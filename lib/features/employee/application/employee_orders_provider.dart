import 'dart:async';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/core/utils/notification_sound_service.dart';
import 'package:magaiees_marketplace/core/network/supabase_safe_query.dart';
import 'package:magaiees_marketplace/core/utils/order_number_formatter.dart';
import 'package:magaiees_marketplace/core/services/local_notification_service.dart';

part 'employee_orders_provider.g.dart';

class OrderModel {
  final String id;
  final String customerId;
  final String buyerName;
  final String buyerPhone;
  final double totalSdg;
  final double paidAmount;
  final String status;
  final String paymentStatus;
  final String address;
  final String paymentMethod;
  final DateTime createdAt;
  final String notes;
  final String? orderNumberRaw;
  final double? transportFee;
  final double? offloadingFee;
  final bool? buyerApprovedFees;
  final String? receiptUrl;
  final List<Map<String, dynamic>> orderItems;
  final List<Map<String, dynamic>> payments;

  OrderModel({
    required this.id,
    required this.customerId,
    this.buyerName = '',
    this.buyerPhone = '',
    required this.totalSdg,
    required this.paidAmount,
    required this.status,
    required this.paymentStatus,
    required this.address,
    required this.paymentMethod,
    required this.createdAt,
    this.notes = '',
    this.orderNumberRaw,
    this.transportFee,
    this.offloadingFee,
    this.buyerApprovedFees,
    this.receiptUrl,
    this.orderItems = const [],
    this.payments = const [],
  });

  String get orderNumber => OrderNumberFormatter.format(
    id: id,
    orderNumber: orderNumberRaw,
    createdAt: createdAt,
  );

  factory OrderModel.fromJson(Map<String, dynamic> json) {
    final buyer = json['buyer'] as Map<String, dynamic>?;
    final buyerName = buyer?['name']?.toString() ?? json['buyer_name']?.toString() ?? '';
    final buyerPhone = buyer?['phone']?.toString() ?? json['buyer_phone']?.toString() ?? '';

    final rawItems = json['order_items'] as List<dynamic>? ?? [];
    final itemsList = rawItems.map((e) => Map<String, dynamic>.from(e as Map)).toList();

    final rawPayments = json['payments'] as List<dynamic>? ?? [];
    final paymentsList = rawPayments.map((e) => Map<String, dynamic>.from(e as Map)).toList();

    return OrderModel(
      id: json['id']?.toString() ?? '',
      customerId: json['buyer_id']?.toString() ?? 'unknown',
      buyerName: buyerName,
      buyerPhone: buyerPhone,
      totalSdg: (json['total_amount'] as num?)?.toDouble() ?? 0.0,
      paidAmount: (json['paid_amount'] as num?)?.toDouble() ?? 0.0,
      status: json['status']?.toString() ?? 'pending',
      paymentStatus: json['payment_status']?.toString() ?? 'unpaid',
      address: json['shipping_address']?.toString() ?? 'مسقط، الخوير',
      paymentMethod: json['payment_method']?.toString() ?? 'bankak',
      createdAt: json['created_at'] != null ? DateTime.tryParse(json['created_at'].toString()) ?? DateTime.now() : DateTime.now(),
      notes: json['notes']?.toString() ?? '',
      orderNumberRaw: json['order_number']?.toString(),
      transportFee: (json['transport_fee'] as num?)?.toDouble(),
      offloadingFee: (json['offloading_fee'] as num?)?.toDouble(),
      buyerApprovedFees: json['buyer_approved_fees'] as bool?,
      receiptUrl: json['receipt_url']?.toString(),
      orderItems: itemsList,
      payments: paymentsList,
    );
  }
}

final employeeShellIndexProvider = StateProvider<int>((ref) => 0);
final employeeOrdersTabProvider = StateProvider<int>((ref) => 0);

class EmployeeState {
  final bool isAvailable;
  final bool isLoading;
  final List<OrderModel> pendingOrders; // incoming requests
  final List<OrderModel> myOrders; // accepted by me

  EmployeeState({
    this.isAvailable = true,
    this.isLoading = false,
    this.pendingOrders = const [],
    this.myOrders = const [],
  });

  EmployeeState copyWith({
    bool? isAvailable,
    bool? isLoading,
    List<OrderModel>? pendingOrders,
    List<OrderModel>? myOrders,
  }) {
    return EmployeeState(
      isAvailable: isAvailable ?? this.isAvailable,
      isLoading: isLoading ?? this.isLoading,
      pendingOrders: pendingOrders ?? this.pendingOrders,
      myOrders: myOrders ?? this.myOrders,
    );
  }
}

@Riverpod(keepAlive: true)
class EmployeeOrders extends _$EmployeeOrders {
  final _supabase = Supabase.instance.client;
  RealtimeChannel? _ordersChannel;
  Timer? _heartbeatTimer;

  @override
  EmployeeState build() {
    ref.onDispose(() {
      _stopHeartbeat();
      _unsubscribeChannel();
    });

    // Start active by default and fetch live data
    Future.microtask(() {
      _startHeartbeat();
      _listenToNewOrders();
      refreshAllOrders();
    });
    return EmployeeState(isAvailable: true);
  }

  void _startHeartbeat() {
    _heartbeatTimer?.cancel();
    // Heartbeat silently checks every 10s for incoming pending orders as a rock-solid safety net
    _heartbeatTimer = Timer.periodic(const Duration(seconds: 10), (_) {
      if (state.isAvailable) {
        _fetchPendingOrders(silent: true);
      }
    });
  }

  void _stopHeartbeat() {
    _heartbeatTimer?.cancel();
    _heartbeatTimer = null;
  }

  Future<void> refreshAllOrders() async {
    state = state.copyWith(isLoading: true);
    await _fetchPendingOrders();
    await _fetchMyOrders();
    state = state.copyWith(isLoading: false);
  }

  void toggleAvailability(bool available) {
    state = state.copyWith(isAvailable: available);
    if (available) {
      _startHeartbeat();
      _listenToNewOrders();
      refreshAllOrders();
    } else {
      _stopHeartbeat();
      _unsubscribeChannel();
    }
  }

  Future<void> _unsubscribeChannel() async {
    try {
      if (_ordersChannel != null) {
        await _ordersChannel?.unsubscribe();
        _supabase.removeChannel(_ordersChannel!);
        _ordersChannel = null;
      }
    } catch (_) {}
  }

  Future<void> _listenToNewOrders() async {
    await _unsubscribeChannel();

    final channelName = 'employee_orders_radar_${DateTime.now().millisecondsSinceEpoch}';
    _ordersChannel = _supabase.channel(channelName);
    
    _ordersChannel!.onPostgresChanges(
      event: PostgresChangeEvent.all,
      schema: 'public',
      table: 'orders',
      callback: (payload) {
        if (!state.isAvailable) return;
        final newRec = payload.newRecord;
        final eventType = payload.eventType;

        final isNewPending = eventType == PostgresChangeEvent.insert &&
            (newRec['status'] == 'pending' || newRec['status'] == null);

        if (isNewPending) {
          playOrderNotificationSound();
          final orderId = newRec['id']?.toString() ?? 'new_order';
          LocalNotificationService.instance.showOrderNotification(
            orderId: orderId,
            title: '🔔 طلبية جديدة في رادار مقاييس!',
            body: 'وصل طلب جديد في انتظار القبول والتنفيذ، اضغط لمعاينة الطلب.',
            payload: orderId,
          );
        }

        // Instantly refresh all orders on any order change
        refreshAllOrders();
      },
    ).subscribe((status, error) {
      debugPrint('Employee orders radar channel status: $status, error: $error');
      if (status == RealtimeSubscribeStatus.timedOut ||
          status == RealtimeSubscribeStatus.channelError) {
        Future.delayed(const Duration(seconds: 3), () {
          if (state.isAvailable) _listenToNewOrders();
        });
      }
    });
  }

  Future<void> playOrderNotificationSound() async {
    try {
      await NotificationSoundService.play();
    } catch (_) {}
  }

  Future<void> _fetchPendingOrders({bool silent = false}) async {
    try {
      final data = await safeSupabaseQuery(
        () => _supabase
            .from('orders')
            .select('*, order_items(*), buyer:users!buyer_id(id, name, phone)')
            .eq('status', 'pending')
            .or('store_id.is.null,notes.ilike.%حصري%')
            .order('created_at', ascending: false)
            .limit(50),
        fallback: () async {
          return await _supabase
              .from('orders')
              .select('*, order_items(*)')
              .eq('status', 'pending')
              .or('store_id.is.null,notes.ilike.%حصري%')
              .order('created_at', ascending: false)
              .limit(50);
        },
      );

      final List<OrderModel> orders = (data as List)
          .map((row) => OrderModel.fromJson(Map<String, dynamic>.from(row as Map)))
          .toList();

      state = state.copyWith(pendingOrders: orders);
    } catch (e) {
      debugPrint('Error fetching pending orders: $e');
    }
  }

  Future<void> _fetchMyOrders() async {
    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user == null) return;

    try {
      final data = await safeSupabaseQuery(
        () => _supabase.from('employee_order_assignments')
            .select('assigned_at, orders(*, order_items(*), buyer:users!buyer_id(id, name, phone))')
            .eq('employee_id', user.id)
            .order('assigned_at', ascending: false),
        fallback: () async => <dynamic>[],
      );
      
      final List<OrderModel> orders = [];
      for (var row in (data as List)) {
        if (row['orders'] != null) {
          orders.add(OrderModel.fromJson(Map<String, dynamic>.from(row['orders'] as Map)));
        }
      }

      // Fetch payments for my orders
      if (orders.isNotEmpty) {
        final orderIds = orders.map((o) => o.id).toList();
        try {
          final paymentsRes = await safeSupabaseQuery(
            () => _supabase
                .from('payments')
                .select('*')
                .inFilter('order_id', orderIds)
                .order('created_at', ascending: true),
            fallback: () async => <dynamic>[],
          );

          final paymentsByOrderId = <String, List<Map<String, dynamic>>>{};
          for (var p in (paymentsRes as List)) {
            final oId = p['order_id'].toString();
            paymentsByOrderId.putIfAbsent(oId, () => []).add(Map<String, dynamic>.from(p));
          }

          for (int i = 0; i < orders.length; i++) {
            final o = orders[i];
            if (paymentsByOrderId.containsKey(o.id)) {
              orders[i] = OrderModel(
                id: o.id,
                customerId: o.customerId,
                buyerName: o.buyerName,
                buyerPhone: o.buyerPhone,
                totalSdg: o.totalSdg,
                paidAmount: o.paidAmount,
                status: o.status,
                paymentStatus: o.paymentStatus,
                address: o.address,
                paymentMethod: o.paymentMethod,
                createdAt: o.createdAt,
                notes: o.notes,
                orderNumberRaw: o.orderNumberRaw,
                transportFee: o.transportFee,
                offloadingFee: o.offloadingFee,
                buyerApprovedFees: o.buyerApprovedFees,
                receiptUrl: o.receiptUrl,
                orderItems: o.orderItems,
                payments: paymentsByOrderId[o.id] ?? [],
              );
            }
          }
        } catch (_) {}
      }
          
      state = state.copyWith(myOrders: orders);
    } catch (e) {
      debugPrint('Error fetching assignments: $e');
      state = state.copyWith(myOrders: [], isLoading: false);
    }
  }

  Future<void> setDeliveryFees(String orderId, double transportFee, double offloadingFee) async {
    try {
      final orderRes = await _supabase
          .from('orders')
          .select('total_amount, transport_fee, offloading_fee')
          .eq('id', orderId)
          .maybeSingle();

      final double oldTotal = (orderRes?['total_amount'] as num?)?.toDouble() ?? 0.0;
      final double prevTransport = (orderRes?['transport_fee'] as num?)?.toDouble() ?? 0.0;
      final double prevOffloading = (orderRes?['offloading_fee'] as num?)?.toDouble() ?? 0.0;
      final double baseTotal = oldTotal - prevTransport - prevOffloading;
      final double newTotal = baseTotal + transportFee + offloadingFee;

      await _supabase.from('orders').update({
        'transport_fee': transportFee.round(),
        'offloading_fee': offloadingFee.round(),
        'total_amount': newTotal,
        'buyer_approved_fees': false,
      }).eq('id', orderId);

      await refreshAllOrders();
    } catch (e) {
      debugPrint('Error setting delivery fees: $e');
      rethrow;
    }
  }

  Future<void> approveReceiptPayments({
    required String orderId,
    required List<Map<String, dynamic>> receipts,
    required double totalReceiptsAmount,
  }) async {
    try {
      final orderRes = await _supabase
          .from('orders')
          .select('notes, total_amount, paid_amount')
          .eq('id', orderId)
          .maybeSingle();

      final notes = orderRes?['notes']?.toString() ?? '';
      final double total = (orderRes?['total_amount'] as num?)?.toDouble() ?? 0.0;
      final double basePaid = (orderRes?['paid_amount'] as num?)?.toDouble() ?? 0.0;

      // 1. Insert payment records
      if (receipts.isNotEmpty) {
        for (final r in receipts) {
          final rAmt = (r['amount'] as num?)?.toDouble() ?? 0.0;
          await _supabase.from('payments').insert({
            'order_id': orderId,
            'method': r['bank'] ?? 'bankak',
            'amount': (rAmt > 0 ? rAmt : totalReceiptsAmount / receipts.length).round(),
            'note': 'إشعار تحويل - مرجع: ${r['ref'] ?? ''}',
          });
        }
      } else {
        await _supabase.from('payments').insert({
          'order_id': orderId,
          'method': 'bank_transfer',
          'amount': totalReceiptsAmount.round(),
          'note': 'اعتماد إشعار التحويل البنكي',
        });
      }

      // 2. Update order paid amount and payment status
      final newPaid = basePaid + totalReceiptsAmount;
      final newPaymentStatus = newPaid >= total ? 'paid' : 'partial';

      await _supabase.from('orders').update({
        'paid_amount': newPaid.round(),
        'payment_status': newPaymentStatus,
        'notes': notes.replaceAll(RegExp(r'(?<!\w)receipt_url:'), 'approved_receipt_url:'),
      }).eq('id', orderId);

      await refreshAllOrders();
    } catch (e) {
      debugPrint('Error approving receipt payments: $e');
      rethrow;
    }
  }

  Future<void> rejectReceipt({
    required String orderId,
    required String reason,
  }) async {
    try {
      final orderRes = await _supabase
          .from('orders')
          .select('notes, buyer_id')
          .eq('id', orderId)
          .maybeSingle();

      final notes = orderRes?['notes']?.toString() ?? '';
      final buyerId = orderRes?['buyer_id']?.toString();

      final updatedNotes = notes.replaceAll(RegExp(r'(?<!\w)receipt_url:'), 'rejected_receipt_url:');
      final notesWithReason = '$updatedNotes\n[سبب رفض الإشعار: $reason]';

      await _supabase.from('orders').update({
        'payment_status': 'unpaid',
        'notes': notesWithReason,
      }).eq('id', orderId);

      // Send notification to buyer if notifications table exists
      if (buyerId != null) {
        try {
          await _supabase.from('notifications').insert({
            'user_id': buyerId,
            'title': 'تم رفض إشعار التحويل',
            'title_ar': 'تم رفض إشعار التحويل',
            'body': 'تم رفض إشعار التحويل للطلب: $reason. يرجى إرفاق إشعار صحيح.',
            'body_ar': 'تم رفض إشعار التحويل للطلب: $reason. يرجى إرفاق إشعار صحيح.',
            'type': 'order',
          });
        } catch (_) {}
      }

      await refreshAllOrders();
    } catch (e) {
      debugPrint('Error rejecting receipt: $e');
      rethrow;
    }
  }

  Future<bool> acceptOrder(String orderId) async {
    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user == null) return false;
    final empId = user.id;

    try {
      // 1. Insert or update assignment with current timestamp
      try {
        await _supabase.from('employee_order_assignments').upsert({
          'order_id': orderId,
          'employee_id': empId,
          'status': 'claimed',
          'assigned_at': DateTime.now().toUtc().toIso8601String(),
        });
      } catch (e) {
        debugPrint('Upsert error in employee_order_assignments: $e');
        try {
          await _supabase.from('employee_order_assignments').insert({
            'order_id': orderId,
            'employee_id': empId,
            'status': 'claimed',
          });
        } catch (_) {}
      }

      // 2. Update order status to processing
      await _supabase.from('orders').update({'status': 'processing'}).eq('id', orderId);

      await refreshAllOrders();
      return true;
    } catch (e) {
      debugPrint('Failed to accept order: $e');
      await refreshAllOrders();
      return false;
    }
  }

  Future<void> markProcessing(String orderId) async {
    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user != null) {
      try {
        await _supabase.from('employee_order_assignments').upsert({
          'order_id': orderId,
          'employee_id': user.id,
          'status': 'claimed',
          'assigned_at': DateTime.now().toUtc().toIso8601String(),
        });
      } catch (_) {}
    }
    try {
      await _supabase.from('orders').update({
        'status': 'processing',
      }).eq('id', orderId);

      await refreshAllOrders();
    } catch (e) {
      debugPrint('Failed to mark processing: $e');
      rethrow;
    }
  }

  Future<void> markShipped(String orderId) async {
    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user != null) {
      try {
        await _supabase.from('employee_order_assignments').upsert({
          'order_id': orderId,
          'employee_id': user.id,
          'status': 'claimed',
          'assigned_at': DateTime.now().toUtc().toIso8601String(),
        });
      } catch (_) {}
    }
    try {
      await _supabase.from('orders').update({
        'status': 'shipped',
      }).eq('id', orderId);

      await refreshAllOrders();
    } catch (e) {
      debugPrint('Failed to mark shipped: $e');
      rethrow;
    }
  }

  Future<void> updateOrderStatus(String orderId, String newStatus) async {
    switch (newStatus) {
      case 'processing':
        await markProcessing(orderId);
        break;
      case 'shipped':
        await markShipped(orderId);
        break;
      case 'delivered':
        await markDelivered(orderId);
        break;
      default:
        await _supabase.from('orders').update({'status': newStatus}).eq('id', orderId);
        await refreshAllOrders();
    }
  }

  Future<void> addPayment(String orderId, double amount, String method) async {
    try {
      await _supabase.from('payments').insert({
        'order_id': orderId,
        'amount': amount.round(),
        'method': method,
      });

      final orderRes = await _supabase
          .from('orders')
          .select('total_amount, paid_amount')
          .eq('id', orderId)
          .maybeSingle();

      final total = (orderRes?['total_amount'] as num?)?.toDouble() ?? 0.0;
      final currentPaid = (orderRes?['paid_amount'] as num?)?.toDouble() ?? 0.0;
      final newPaid = currentPaid + amount;
      final newPaymentStatus = newPaid >= total ? 'paid' : 'partial';

      await _supabase.from('orders').update({
        'paid_amount': newPaid.round(),
        'payment_status': newPaymentStatus,
      }).eq('id', orderId);

      await refreshAllOrders();
    } catch (e) {
      debugPrint('Failed to add payment: $e');
      rethrow;
    }
  }

  Future<void> markDelivered(String orderId) async {
    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user != null) {
      try {
        await _supabase.from('employee_order_assignments').upsert({
          'order_id': orderId,
          'employee_id': user.id,
          'status': 'completed',
          'assigned_at': DateTime.now().toUtc().toIso8601String(),
        });
      } catch (_) {}
    }
    try {
      await _supabase.from('orders').update({
        'status': 'delivered',
        'payment_status': 'paid',
      }).eq('id', orderId);

      try {
        await _supabase.from('employee_order_assignments').update({
          'status': 'completed',
        }).eq('order_id', orderId);
      } catch (_) {}

      await refreshAllOrders();
    } catch (e) {
      debugPrint('Failed to mark delivered: $e');
      rethrow;
    }
  }

  Future<void> cancelOrder(String orderId, String reason) async {
    try {
      final orderRes = await _supabase.from('orders').select('notes').eq('id', orderId).maybeSingle();
      final currentNotes = orderRes?['notes']?.toString() ?? '';

      await _supabase.from('orders').update({
        'status': 'cancelled',
        'notes': '${currentNotes.isNotEmpty ? '$currentNotes\n' : ''}تم الرفض بواسطة موظف مقاييس: $reason',
      }).eq('id', orderId);

      await refreshAllOrders();
    } catch (e) {
      debugPrint('Failed to cancel order: $e');
      rethrow;
    }
  }

  void rejectOrder(String orderId) {
    state = state.copyWith(
      pendingOrders: state.pendingOrders.where((o) => o.id != orderId).toList(),
    );
  }
}
