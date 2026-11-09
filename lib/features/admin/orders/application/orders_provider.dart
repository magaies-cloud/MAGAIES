import 'dart:async';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/core/network/supabase_safe_query.dart';
import 'package:magaiees_marketplace/core/utils/order_number_formatter.dart';

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

class PaymentRecord {
  final String id;
  final String orderId;
  final double amount;
  final String method;
  final DateTime createdAt;

  PaymentRecord({required this.id, required this.orderId, required this.amount, required this.method, required this.createdAt});

  factory PaymentRecord.fromJson(Map<String, dynamic> json) {
    return PaymentRecord(
      id: json['id']?.toString() ?? '',
      orderId: json['order_id']?.toString() ?? '',
      amount: (json['amount'] as num?)?.toDouble() ?? 0.0,
      method: json['method']?.toString() ?? '',
      createdAt: json['created_at'] != null ? DateTime.parse(json['created_at'].toString()) : DateTime.now(),
    );
  }
}

class OrderModel {
  final String id;
  final String? orderNumber;
  final String buyerId;
  final String storeId;
  final double totalAmount;
  final double paidAmount;
  final String paymentStatus; // unpaid, partial, paid
  final String status;
  final DateTime createdAt;
  final String? shippingAddress;
  final String? paymentMethod;
  final String? storeName;
  final String? storeOwnerId;
  final bool isStoreAdvertiser;
  final String? buyerName;
  final String? buyerPhone;
  final String? buyerEmail;
  final String? notes;
  final double transportFee;
  final double offloadingFee;
  final double? commissionAmount;
  final double? commissionRateApplied;
  final String? receiptUrl;
  final List<OrderItemModel> items;
  
  double get actualPaidAmount => (paidAmount > 0) ? paidAmount : (paymentStatus == 'paid' ? totalAmount : 0.0);
  double get remainingAmount => (totalAmount - actualPaidAmount) > 0 ? (totalAmount - actualPaidAmount) : 0.0;
  bool get isFullyPaid => actualPaidAmount >= totalAmount && totalAmount > 0;
  bool get isOverpaid => actualPaidAmount > totalAmount;
  double get overpaidAmount => actualPaidAmount > totalAmount ? actualPaidAmount - totalAmount : 0.0;
  bool get isExclusive => storeId.isEmpty || storeName == 'توريد مقاييس المباشر' || (notes != null && notes!.contains('حصري'));

  String? get staffAssignedName {
    if (notes == null) return null;
    final match = RegExp(r'\[staff:\s*([^\|]+)\s*\|\s*([^\]]+)\]').firstMatch(notes!);
    return match?.group(2)?.trim();
  }

  String? get staffAssignedId {
    if (notes == null) return null;
    final match = RegExp(r'\[staff:\s*([^\|]+)\s*\|\s*([^\]]+)\]').firstMatch(notes!);
    return match?.group(1)?.trim();
  }

  String get cleanNotes {
    if (notes == null) return '';
    var text = notes!;
    text = text.replaceAll(RegExp(r'\[staff:\s*[^\|]+\s*\|\s*[^\]]+\]'), '');
    text = text.replaceAll(RegExp(r'(?:approved_|rejected_)?receipt_url:\S+'), '');
    return text.trim();
  }

  String get formattedOrderNumber => OrderNumberFormatter.format(
    id: id,
    orderNumber: orderNumber,
    createdAt: createdAt,
  );
  
  OrderModel({
    required this.id,
    this.orderNumber,
    required this.buyerId,
    required this.storeId,
    required this.totalAmount,
    this.paidAmount = 0.0,
    this.paymentStatus = 'unpaid',
    required this.status,
    required this.createdAt,
    this.shippingAddress,
    this.paymentMethod,
    this.storeName,
    this.storeOwnerId,
    this.isStoreAdvertiser = false,
    this.buyerName,
    this.buyerPhone,
    this.buyerEmail,
    this.notes,
    this.transportFee = 0.0,
    this.offloadingFee = 0.0,
    this.commissionAmount,
    this.commissionRateApplied,
    this.receiptUrl,
    this.items = const [],
  });

  factory OrderModel.fromJson(Map<String, dynamic> json, {Set<String>? advertiserKeywords}) {
    final buyerData = json['buyer'] ?? json['users'];
    String? buyerName;
    String? buyerPhone;
    String? buyerEmail;
    if (buyerData is Map<String, dynamic>) {
      buyerName = buyerData['name']?.toString();
      buyerPhone = buyerData['phone']?.toString();
      buyerEmail = buyerData['email']?.toString();
    }

    final storeData = json['stores'];
    String? storeName = json['store_name']?.toString();
    String? storeOwnerId;
    if (storeData is Map<String, dynamic>) {
      storeName = storeData['name_ar']?.toString() ?? storeData['name']?.toString() ?? storeName;
      storeOwnerId = storeData['owner_id']?.toString();
    }

    bool isStoreAdv = false;
    if (advertiserKeywords != null && advertiserKeywords.isNotEmpty) {
      final sLower = (storeName ?? '').trim().toLowerCase();
      final oLower = (storeOwnerId ?? '').trim().toLowerCase();
      for (var kw in advertiserKeywords) {
        if (kw.isNotEmpty && (sLower.contains(kw) || oLower == kw)) {
          isStoreAdv = true;
          break;
        }
      }
    }

    List<OrderItemModel> items = [];
    if (json['order_items'] is List) {
      items = (json['order_items'] as List)
          .map((e) => OrderItemModel.fromJson(e as Map<String, dynamic>))
          .toList();
    }

    return OrderModel(
      id: json['id']?.toString() ?? '',
      orderNumber: json['order_number']?.toString(),
      buyerId: json['buyer_id']?.toString() ?? '',
      storeId: json['store_id']?.toString() ?? '',
      totalAmount: (json['total_amount'] as num?)?.toDouble() ?? 0.0,
      paidAmount: (json['paid_amount'] as num?)?.toDouble() ?? 0.0,
      paymentStatus: json['payment_status']?.toString() ?? 'unpaid',
      status: json['status']?.toString() ?? 'pending',
      createdAt: json['created_at'] != null ? DateTime.parse(json['created_at']) : DateTime.now(),
      shippingAddress: json['shipping_address']?.toString() ?? json['address']?.toString(),
      paymentMethod: json['payment_method']?.toString() ?? 'تحويل بنكي',
      storeName: storeName ?? (json['store_id'] != null && json['store_id'].toString().isNotEmpty ? 'متجر ${json['store_id']}' : 'توريد مقاييس المباشر'),
      storeOwnerId: storeOwnerId,
      isStoreAdvertiser: isStoreAdv,
      buyerName: buyerName,
      buyerPhone: buyerPhone,
      buyerEmail: buyerEmail,
      notes: json['notes']?.toString(),
      transportFee: (json['transport_fee'] as num?)?.toDouble() ?? 0.0,
      offloadingFee: (json['offloading_fee'] as num?)?.toDouble() ?? 0.0,
      commissionAmount: (json['commission_amount'] as num?)?.toDouble(),
      commissionRateApplied: (json['commission_rate_applied'] as num?)?.toDouble(),
      receiptUrl: json['receipt_url']?.toString(),
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
      final results = await Future.wait([
        safeSupabaseQuery(() => _supabase
            .from('orders')
            .select('*, users!orders_buyer_id_fkey(name, phone, email), stores(id, name, name_ar, owner_id), order_items(*)')
            .order('created_at', ascending: false)),
        safeSupabaseQuery(() => _supabase.from('ads').select('company, title, is_active').eq('is_active', true),
            fallback: () => <Map<String, dynamic>>[]),
      ]);
      final ordersList = (results[0] as List);
      final adsList = (results[1] as List);

      final Set<String> advertiserKeywords = {};
      for (var a in adsList) {
        final c = a['company']?.toString().trim().toLowerCase() ?? '';
        final t = a['title']?.toString().trim().toLowerCase() ?? '';
        if (c.isNotEmpty) advertiserKeywords.add(c);
        if (t.isNotEmpty) advertiserKeywords.add(t);
      }

      final orders = ordersList
          .map((e) => OrderModel.fromJson(e as Map<String, dynamic>, advertiserKeywords: advertiserKeywords))
          .toList();
      state = AsyncValue.data(orders);
    } catch (e) {
      try {
        final fallback = await safeSupabaseQuery(
          () => _supabase.from('orders').select().order('created_at', ascending: false),
          fallback: () => <Map<String, dynamic>>[],
        );
        state = AsyncValue.data((fallback as List).map((e) => OrderModel.fromJson(e as Map<String, dynamic>)).toList());
      } catch (e2, st2) {
        state = AsyncValue.error(e2, st2);
      }
    }
  }

  Future<void> updateOrderStatus(String id, String status) async {
    try {
      await safeSupabaseQuery(() => _supabase.from('orders').update({'status': status}).eq('id', id));
      await loadOrders();
    } catch (e) {
      rethrow;
    }
  }

  /// Record a payment for an order. Updates paid_amount and payment_status.
  Future<void> recordPayment(
    String orderId, 
    double amount, 
    String method, {
    String? referenceNumber,
    String? bankName,
    String? recipientAccount,
    String? destination,
  }) async {
    try {
      // 1. Insert into payments table
      await safeSupabaseQuery(() => _supabase.from('payments').insert({
        'order_id': orderId,
        'amount': amount,
        'method': method,
        if (referenceNumber != null && referenceNumber.isNotEmpty) 'reference_number': referenceNumber,
        if (bankName != null && bankName.isNotEmpty) 'bank_name': bankName,
        if (recipientAccount != null && recipientAccount.isNotEmpty) 'recipient_account': recipientAccount,
        if (destination != null && destination.isNotEmpty) 'payment_destination': destination,
        'note': (referenceNumber != null && referenceNumber.isNotEmpty) ? 'إشعار: $referenceNumber' : null,
      }));

      // 2. Get current order to calculate new paid_amount
      final orderRes = await safeSupabaseQuery(() => _supabase
          .from('orders')
          .select('total_amount, paid_amount')
          .eq('id', orderId)
          .maybeSingle());

      if (orderRes == null) {
        await loadOrders();
        return;
      }

      final totalAmount = (num.tryParse(orderRes['total_amount']?.toString() ?? '0') ?? 0).toDouble();
      final currentPaid = (num.tryParse(orderRes['paid_amount']?.toString() ?? '0') ?? 0).toDouble();
      final newPaid = currentPaid + amount;

      String newStatus = 'unpaid';
      if (newPaid >= totalAmount) {
        newStatus = 'paid';
      } else if (newPaid > 0) {
        newStatus = 'partial';
      }

      // 3. Update order
      await safeSupabaseQuery(() => _supabase.from('orders').update({
        'paid_amount': newPaid,
        'payment_status': newStatus,
      }).eq('id', orderId));

      await loadOrders();
    } catch (e) {
      rethrow;
    }
  }

  /// Process a refund/change for overpaid amount
  Future<void> processRefund(String orderId, double refundAmount, String method) async {
    try {
      // Insert negative payment record
      await safeSupabaseQuery(() => _supabase.from('payments').insert({
        'order_id': orderId,
        'amount': -refundAmount,
        'method': 'إرجاع: $method',
      }));

      // Update paid_amount
      final orderRes = await safeSupabaseQuery(() => _supabase
          .from('orders')
          .select('total_amount, paid_amount')
          .eq('id', orderId)
          .maybeSingle());

      if (orderRes == null) {
        await loadOrders();
        return;
      }

      final totalAmount = (num.tryParse(orderRes['total_amount']?.toString() ?? '0') ?? 0).toDouble();
      final currentPaid = (num.tryParse(orderRes['paid_amount']?.toString() ?? '0') ?? 0).toDouble();
      final newPaid = currentPaid - refundAmount;

      String newStatus = 'unpaid';
      if (newPaid >= totalAmount) {
        newStatus = 'paid';
      } else if (newPaid > 0) {
        newStatus = 'partial';
      }

      await _supabase.from('orders').update({
        'paid_amount': newPaid,
        'payment_status': newStatus,
      }).eq('id', orderId);

      await loadOrders();
    } catch (e) {
      rethrow;
    }
  }

  /// Fetch payment history for an order
  Future<List<PaymentRecord>> getPaymentHistory(String orderId) async {
    try {
      final res = await _supabase
          .from('payments')
          .select()
          .eq('order_id', orderId)
          .order('created_at', ascending: true);
      return (res as List).map((e) => PaymentRecord.fromJson(e)).toList();
    } catch (e) {
      return [];
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

final adminOrdersInsightsProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final ordersState = ref.watch(adminOrdersProvider);
  if (ordersState is! AsyncData) {
    return {};
  }
  final orders = ordersState.value!;
  
  double commissionPercent = 0.0;
  bool enableCommission = false;
  try {
    final settingsRes = await safeSupabaseQuery(() => Supabase.instance.client
        .from('gamification_settings')
        .select('enable_commission, commission_percentage')
        .eq('id', 1)
        .maybeSingle());
    if (settingsRes != null) {
      enableCommission = settingsRes['enable_commission'] == true;
      commissionPercent = (num.tryParse(settingsRes['commission_percentage']?.toString() ?? '0') ?? 0.0).toDouble();
    }
  } catch (_) {}

  double totalSalesVolume = 0.0;
  double totalPaid = 0.0;
  double totalReceivables = 0.0;
  double totalProfit = 0.0;
  int completedCount = 0;
  int processingCount = 0;
  int shippedCount = 0;
  int pendingCount = 0;
  int cancelledCount = 0;
  int paidCount = 0;
  int unpaidCount = 0;
  int partialCount = 0;
  int advertiserOrdersCount = 0;
  double advertiserOrdersVolume = 0.0;
  
  Map<String, double> buyerSpending = {};
  Map<String, Map<String, String>> buyerDetails = {};

  for (var order in orders) {
    final isCancelled = order.status.toLowerCase() == 'cancelled';
    final total = order.totalAmount;
    final paid = order.actualPaidAmount;

    if (!isCancelled) {
      totalSalesVolume += total;
      totalReceivables += order.remainingAmount;
      totalPaid += paid;

      if (order.isFullyPaid) {
        paidCount++;
      } else if (order.actualPaidAmount > 0) {
        partialCount++;
      } else {
        unpaidCount++;
      }

      if (order.commissionAmount != null && order.commissionAmount! > 0) {
        totalProfit += order.commissionAmount!;
      } else if (enableCommission && commissionPercent > 0) {
        totalProfit += (order.actualPaidAmount * commissionPercent) / 100;
      }
    }

    if (order.isStoreAdvertiser) {
      advertiserOrdersCount++;
      if (!isCancelled) advertiserOrdersVolume += total;
    }

    switch (order.status.toLowerCase()) {
      case 'completed':
      case 'delivered':
        completedCount++;
        break;
      case 'processing':
        processingCount++;
        break;
      case 'shipped':
        shippedCount++;
        break;
      case 'cancelled':
        cancelledCount++;
        break;
      case 'pending':
      default:
        pendingCount++;
        break;
    }

    if (order.buyerId.isNotEmpty && !isCancelled) {
      buyerSpending[order.buyerId] = (buyerSpending[order.buyerId] ?? 0.0) + total;
      if (!buyerDetails.containsKey(order.buyerId)) {
        buyerDetails[order.buyerId] = {
          'name': order.buyerName ?? 'غير معروف',
          'phone': order.buyerPhone ?? '',
          'email': order.buyerEmail ?? '',
        };
      }
    }
  }

  String topBuyerId = '';
  double maxSpenderAmt = 0.0;
  buyerSpending.forEach((id, amt) {
    if (amt > maxSpenderAmt) {
      maxSpenderAmt = amt;
      topBuyerId = id;
    }
  });

  Map<String, dynamic>? topBuyer;
  if (topBuyerId.isNotEmpty) {
    topBuyer = {
      'name': buyerDetails[topBuyerId]?['name'],
      'phone': buyerDetails[topBuyerId]?['phone'],
      'email': buyerDetails[topBuyerId]?['email'],
      'totalSpent': maxSpenderAmt,
    };
  }

  final collectionRate = totalSalesVolume > 0 ? (totalPaid / totalSalesVolume) * 100 : 0.0;

  return {
    'totalSalesVolume': totalSalesVolume,
    'totalCollections': totalPaid, // mapped to actual collected cash for compatibility
    'totalPaid': totalPaid,
    'totalReceivables': totalReceivables,
    'totalProfit': totalProfit,
    'totalOrders': orders.length,
    'completedCount': completedCount,
    'processingCount': processingCount,
    'shippedCount': shippedCount,
    'pendingCount': pendingCount,
    'cancelledCount': cancelledCount,
    'paidCount': paidCount,
    'unpaidCount': unpaidCount,
    'partialCount': partialCount,
    'advertiserOrdersCount': advertiserOrdersCount,
    'advertiserOrdersVolume': advertiserOrdersVolume,
    'collectionRate': collectionRate,
    'topBuyer': topBuyer,
  };
});
