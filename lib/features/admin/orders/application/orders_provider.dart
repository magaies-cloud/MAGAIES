import 'dart:async';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

class OrderItemModel {
  final String? id;
  final String? productId;
  final String productName;
  final int quantity;
  final double priceSdg;
  final double priceUsd;

  OrderItemModel({
    this.id,
    this.productId,
    required this.productName,
    required this.quantity,
    required this.priceSdg,
    required this.priceUsd,
  });

  factory OrderItemModel.fromJson(Map<String, dynamic> json) {
    return OrderItemModel(
      id: json['id']?.toString(),
      productId: json['product_id']?.toString(),
      productName: json['product_name']?.toString() ?? '',
      quantity: (json['quantity'] as num?)?.toInt() ?? 1,
      priceSdg: (json['price_sdg'] as num?)?.toDouble() ?? 0.0,
      priceUsd: (json['price_usd'] as num?)?.toDouble() ?? 0.0,
    );
  }
}

class OrderModel {
  final String id;
  final String buyerId;
  final String storeId;
  final double totalAmount;
  final String status;
  final DateTime createdAt;
  final String? shippingAddress;
  // Buyer info from JOIN
  final String? buyerName;
  final String? buyerPhone;
  final String? buyerEmail;
  // Order items
  final List<OrderItemModel> items;
  
  OrderModel({
    required this.id,
    required this.buyerId,
    required this.storeId,
    required this.totalAmount,
    required this.status,
    required this.createdAt,
    this.shippingAddress,
    this.buyerName,
    this.buyerPhone,
    this.buyerEmail,
    this.items = const [],
  });

  factory OrderModel.fromJson(Map<String, dynamic> json) {
    // Parse buyer info from joined data
    final buyerData = json['buyer'] ?? json['users'];
    String? buyerName;
    String? buyerPhone;
    String? buyerEmail;
    if (buyerData is Map<String, dynamic>) {
      buyerName = buyerData['name']?.toString();
      buyerPhone = buyerData['phone']?.toString();
      buyerEmail = buyerData['email']?.toString();
    }

    // Parse order items
    List<OrderItemModel> items = [];
    if (json['order_items'] is List) {
      items = (json['order_items'] as List)
          .map((e) => OrderItemModel.fromJson(e as Map<String, dynamic>))
          .toList();
    }

    return OrderModel(
      id: json['id']?.toString() ?? '',
      buyerId: json['buyer_id']?.toString() ?? '',
      storeId: json['store_id']?.toString() ?? '',
      totalAmount: (json['total_amount'] as num?)?.toDouble() ?? 0.0,
      status: json['status']?.toString() ?? 'pending',
      createdAt: json['created_at'] != null ? DateTime.parse(json['created_at']) : DateTime.now(),
      shippingAddress: json['shipping_address']?.toString(),
      buyerName: buyerName,
      buyerPhone: buyerPhone,
      buyerEmail: buyerEmail,
      items: items,
    );
  }
}

class OrdersNotifier extends StateNotifier<AsyncValue<List<OrderModel>>> {
  OrdersNotifier() : super(const AsyncValue.loading()) {
    loadOrders();
    _setupRealtimeListener();
  }

  final _supabase = Supabase.instance.client;
  RealtimeChannel? _channel;

  void _setupRealtimeListener() {
    _channel = _supabase
        .channel('admin:orders')
        .onPostgresChanges(
          event: PostgresChangeEvent.all,
          schema: 'public',
          table: 'orders',
          callback: (payload) {
            loadOrders();
          },
        )
        .subscribe();
  }

  Future<void> loadOrders() async {
    try {
      // Join with users table for buyer info and order_items for line items
      final response = await _supabase
          .from('orders')
          .select('*, buyer:buyer_id(name, phone, email), order_items(*)')
          .order('created_at', ascending: false);
      final orders = (response as List).map((e) => OrderModel.fromJson(e)).toList();
      state = AsyncValue.data(orders);
    } catch (e, st) {
      // Fallback: try without JOINs in case relations aren't set up
      try {
        final response = await _supabase.from('orders').select().order('created_at', ascending: false);
        final orders = (response as List).map((e) => OrderModel.fromJson(e)).toList();
        state = AsyncValue.data(orders);
      } catch (e2, st2) {
        state = AsyncValue.error(e2, st2);
      }
    }
  }

  Future<void> updateOrderStatus(String id, String status) async {
    try {
      await _supabase.from('orders').update({'status': status}).eq('id', id);
      await loadOrders();
    } catch (e) {
      rethrow;
    }
  }

  @override
  void dispose() {
    _channel?.unsubscribe();
    super.dispose();
  }
}

final adminOrdersProvider = StateNotifierProvider<OrdersNotifier, AsyncValue<List<OrderModel>>>((ref) {
  return OrdersNotifier();
});

