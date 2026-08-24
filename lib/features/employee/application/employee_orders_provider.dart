import 'package:riverpod_annotation/riverpod_annotation.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';

part 'employee_orders_provider.g.dart';

class OrderModel {
  final String id;
  final String customerId;
  final double totalSdg;
  final String status;
  final String address;
  final DateTime createdAt;

  OrderModel({
    required this.id,
    required this.customerId,
    required this.totalSdg,
    required this.status,
    required this.address,
    required this.createdAt,
  });

  factory OrderModel.fromJson(Map<String, dynamic> json) {
    return OrderModel(
      id: json['id'],
      customerId: json['buyer_id'] ?? 'unknown',
      totalSdg: (json['total_amount'] as num).toDouble(),
      status: json['status'],
      address: json['shipping_address'] ?? 'الخرطوم', // fallback
      createdAt: DateTime.parse(json['created_at']),
    );
  }
}

class EmployeeState {
  final bool isAvailable;
  final List<OrderModel> pendingOrders; // incoming requests
  final List<OrderModel> myOrders; // accepted by me

  EmployeeState({
    this.isAvailable = false,
    this.pendingOrders = const [],
    this.myOrders = const [],
  });

  EmployeeState copyWith({
    bool? isAvailable,
    List<OrderModel>? pendingOrders,
    List<OrderModel>? myOrders,
  }) {
    return EmployeeState(
      isAvailable: isAvailable ?? this.isAvailable,
      pendingOrders: pendingOrders ?? this.pendingOrders,
      myOrders: myOrders ?? this.myOrders,
    );
  }
}

@Riverpod(keepAlive: true)
class EmployeeOrders extends _$EmployeeOrders {
  final _supabase = Supabase.instance.client;
  RealtimeChannel? _ordersChannel;

  @override
  EmployeeState build() {
    // We start offline by default
    return EmployeeState();
  }

  void toggleAvailability(bool available) {
    state = state.copyWith(isAvailable: available);
    if (available) {
      _listenToNewOrders();
      _fetchMyOrders();
    } else {
      _ordersChannel?.unsubscribe();
      _ordersChannel = null;
      state = state.copyWith(pendingOrders: []);
    }
  }

  void _listenToNewOrders() {
    _ordersChannel?.unsubscribe();
    _ordersChannel = _supabase.channel('public:orders');
    
    _ordersChannel!.onPostgresChanges(
      event: PostgresChangeEvent.insert,
      schema: 'public',
      table: 'orders',
      filter: PostgresChangeFilter(
        type: PostgresChangeFilterType.eq,
        column: 'status',
        value: 'pending',
      ),
      callback: (payload) {
        if (!state.isAvailable) return;
        final newOrder = OrderModel.fromJson(payload.newRecord);
        state = state.copyWith(
          pendingOrders: [newOrder, ...state.pendingOrders],
        );
      },
    ).subscribe();
  }

  Future<void> _fetchMyOrders() async {
    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user == null) return;

    try {
      final data = await _supabase.from('employee_order_assignments')
          .select('orders(*)')
          .eq('employee_id', user.id);
      
      final List<OrderModel> orders = data
          .map((row) => OrderModel.fromJson(row['orders']))
          .toList();
          
      state = state.copyWith(myOrders: orders);
    } catch (e) {
      print('Error fetching my orders: $e');
    }
  }

  Future<bool> acceptOrder(String orderId) async {
    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user == null) return false;

    try {
      // 1. Attempt to insert into employee_order_assignments
      // If someone else already accepted it, the UNIQUE constraint on order_id will throw an error
      await _supabase.from('employee_order_assignments').insert({
        'order_id': orderId,
        'employee_id': user.id,
      });

      // 2. Update order status to processing
      await _supabase.from('orders').update({'status': 'processing'}).eq('id', orderId);

      // Remove from pending, refresh my orders
      state = state.copyWith(
        pendingOrders: state.pendingOrders.where((o) => o.id != orderId).toList(),
      );
      await _fetchMyOrders();
      return true; // Success! I got the order
    } catch (e) {
      // It means someone else claimed it first, or it failed
      print('Failed to accept order: $e');
      
      // Remove from pending because it's no longer available
      state = state.copyWith(
        pendingOrders: state.pendingOrders.where((o) => o.id != orderId).toList(),
      );
      return false;
    }
  }

  void rejectOrder(String orderId) {
    state = state.copyWith(
      pendingOrders: state.pendingOrders.where((o) => o.id != orderId).toList(),
    );
  }
}
