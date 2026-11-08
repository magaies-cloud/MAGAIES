import 'dart:async';
import 'package:image_picker/image_picker.dart';
import 'package:magaiees_marketplace/core/utils/image_picker_helper.dart';
import 'dart:typed_data';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:go_router/go_router.dart';
import 'package:magaiees_marketplace/core/enums/user_role.dart';
import 'package:magaiees_marketplace/core/utils/app_notification.dart';
import 'package:magaiees_marketplace/core/utils/notification_sound_service.dart';
import 'package:magaiees_marketplace/features/chat/application/chat_provider.dart';
import 'package:magaiees_marketplace/core/network/supabase_safe_query.dart';
import 'package:magaiees_marketplace/features/chat/presentation/screens/call_screen.dart';
import 'package:magaiees_marketplace/core/utils/order_number_formatter.dart';
import 'package:magaiees_marketplace/core/utils/data_validator.dart';
import 'package:magaiees_marketplace/core/utils/name_formatter.dart';

final buyerOrdersProvider = StreamProvider.autoDispose<List<Map<String, dynamic>>>((ref) {
  final user = ref.watch(authNotifierProvider).valueOrNull;
  if (user == null) {
    return Stream.value([]);
  }

  final controller = StreamController<List<Map<String, dynamic>>>.broadcast();
  RealtimeChannel? channel;

  // Register onDispose synchronously at provider initialization before any async gaps
  ref.onDispose(() {
    final ch = channel;
    if (ch != null) {
      ch.unsubscribe();
      Supabase.instance.client.removeChannel(ch);
    }
    controller.close();
  });

  Future<void> fetchOrders() async {
    if (controller.isClosed) return;
    try {
      final ordersRes = await safeSupabaseQuery(
        () => Supabase.instance.client
            .from('orders')
            .select('*, order_items(*), stores(id, owner_id, name, name_ar)')
            .eq('buyer_id', user.id)
            .order('created_at', ascending: false),
        fallback: () async {
          return await Supabase.instance.client
              .from('orders')
              .select('*, order_items(*)')
              .eq('buyer_id', user.id)
              .order('created_at', ascending: false);
        },
      );

      if (!controller.isClosed) {
        controller.add(List<Map<String, dynamic>>.from(ordersRes as Iterable));
      }
    } catch (e) {
      if (!controller.isClosed) controller.addError(e);
    }
  }

  fetchOrders();

  channel = Supabase.instance.client
      .channel('buyer_orders_${user.id}_${DateTime.now().millisecondsSinceEpoch}')
      .onPostgresChanges(
        event: PostgresChangeEvent.all,
        schema: 'public',
        table: 'orders',
        callback: (payload) async {
          final newRec = payload.newRecord;
          String? rowBuyerId = newRec['buyer_id']?.toString();
          if (rowBuyerId == null && newRec['id'] != null) {
            try {
              final check = await Supabase.instance.client
                  .from('orders')
                  .select('buyer_id')
                  .eq('id', newRec['id'])
                  .maybeSingle();
              rowBuyerId = check?['buyer_id']?.toString();
            } catch (_) {}
          }
          if (rowBuyerId == user.id || rowBuyerId == null) {
            fetchOrders();
            NotificationSoundService.play();
          }
        },
      )
      .subscribe();

  return controller.stream;
});

class BuyerOrdersScreen extends ConsumerWidget {
  const BuyerOrdersScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final ordersAsync = ref.watch(buyerOrdersProvider);

    return Scaffold(
      appBar: AppBar(
        title: Text(isRtl ? 'طلباتي' : 'My Orders'),
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(buyerOrdersProvider);
        },
        child: ordersAsync.when(
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (err, stack) => SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            child: Center(
              child: Padding(
                padding: const EdgeInsets.all(32),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.cloud_off_rounded, size: 64, color: Colors.grey.shade400),
                    const SizedBox(height: 16),
                    Text(
                      isRtl ? 'تعذر الاتصال بالخادم' : 'Could not connect to server',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.grey.shade700),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      isRtl ? 'تحقق من اتصال الإنترنت وأعد المحاولة' : 'Check your internet connection and try again',
                      style: TextStyle(fontSize: 13, color: Colors.grey.shade500),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 20),
                    FilledButton.icon(
                      onPressed: () => ref.invalidate(buyerOrdersProvider),
                      icon: const Icon(Icons.refresh),
                      label: Text(isRtl ? 'إعادة المحاولة' : 'Retry'),
                      style: FilledButton.styleFrom(
                        backgroundColor: Colors.blue.shade600,
                        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          data: (orders) {
            if (orders.isEmpty) {
              return ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                children: [
                  SizedBox(height: MediaQuery.of(context).size.height * 0.25),
                  Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.shopping_bag_outlined, size: 64, color: Colors.grey.shade400),
                        const SizedBox(height: 16),
                        Text(isRtl ? 'لم تقم بأي طلبات بعد' : 'You have no orders yet',
                            style: TextStyle(fontSize: 16, color: Colors.grey.shade600)),
                      ],
                    ),
                  ),
                ],
              );
            }

            return ListView.builder(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.symmetric(vertical: 8),
              itemCount: orders.length,
              itemBuilder: (context, index) {
                final order = orders[index];
                return _BuyerOrderCard(order: order, isRtl: isRtl);
              },
            );
          },
        ),
      ),
    );
  }
}

class _BuyerOrderCard extends ConsumerWidget {
  final Map<String, dynamic> order;
  final bool isRtl;
  const _BuyerOrderCard({required this.order, required this.isRtl});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final orderId = OrderNumberFormatter.formatFromMap(order);
    final date = order['created_at'] != null
        ? DateFormat('yyyy-MM-dd HH:mm').format(DateTime.parse(order['created_at']).toLocal())
        : '-';
    final totalAmount = double.tryParse(order['total_amount']?.toString() ?? '0') ?? 0;
    final paidAmount = double.tryParse(order['paid_amount']?.toString() ?? '0') ?? 0;
    final transportFee = double.tryParse(order['transport_fee']?.toString() ?? '0') ?? 0;
    final status = order['status'] ?? 'pending';
    final paymentStatus = order['payment_status'] ?? 'unpaid';
    final paymentMethod = order['payment_method'] ?? '';
    final items = order['order_items'] as List<dynamic>? ?? [];
    final notes = order['notes']?.toString() ?? '';
    final shippingAddress = order['shipping_address']?.toString() ?? '';
    final isSelfPickup = notes.contains('استلام وترحيل ذاتي') || shippingAddress.contains('استلام وترحيل ذاتي');
    final isTransportCovered = notes.contains('الترحيل مشمول في طلب آخر');
    final bool feesAwaitingApproval = !isSelfPickup && (order['buyer_approved_fees'] == null || order['buyer_approved_fees'] == false);

    final allReceipts = _parseAllReceipts(notes, fallbackUrl: order['receipt_url']?.toString());
    final underReviewReceipts = allReceipts.where((r) => r['status'] == 'under_review').toList();
    final approvedReceipts = allReceipts.where((r) => r['status'] == 'approved').toList();

    double pendingReceiptAmount = underReviewReceipts.fold(0.0, (sum, r) => sum + (r['amount'] as num).toDouble());
    double approvedReceiptAmount = approvedReceipts.fold(0.0, (sum, r) => sum + (r['amount'] as num).toDouble());

    if (!feesAwaitingApproval && pendingReceiptAmount == 0.0 && paidAmount > 0 && paymentStatus == 'under_review') {
      pendingReceiptAmount = paidAmount;
    }
    final effectivePaid = (!feesAwaitingApproval && paymentStatus == 'under_review')
        ? (pendingReceiptAmount > 0 ? (approvedReceiptAmount + pendingReceiptAmount) : paidAmount)
        : (approvedReceiptAmount > 0 ? approvedReceiptAmount : paidAmount);
    final remaining = (totalAmount - effectivePaid).clamp(0.0, double.infinity);

    final isExclusiveOrder = order['store_id'] == null ||
        notes.contains('حصري') ||
        notes.contains('[طلب حصري مقاييس]');

    final storeData = order['stores'] as Map<String, dynamic>?;
    String storeName = '';
    if (isExclusiveOrder) {
      storeName = isRtl ? 'حصري على مقاييس' : 'Magaiees Exclusive';
    } else if (storeData != null) {
      final nameAr = storeData['name_ar']?.toString().trim();
      final nameEn = storeData['name']?.toString().trim();
      if (isRtl) {
        storeName = (nameAr != null && nameAr.isNotEmpty) ? nameAr : (nameEn ?? '');
      } else {
        storeName = (nameEn != null && nameEn.isNotEmpty) ? nameEn : (nameAr ?? '');
      }
    }
    if (storeName.isEmpty) {
      final matchStore = RegExp(r'\[متجر: ([^\]]+)\]').firstMatch(notes);
      if (matchStore != null) {
        storeName = matchStore.group(1)?.trim() ?? '';
      }
    }
    if (storeName.isEmpty) {
      storeName = isRtl ? 'متجر مقاييس' : 'Magaiees Store';
    }

    return Card(
      elevation: 2,
      shadowColor: Colors.black12,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(color: _getStatusColor(status).withOpacity(0.2), width: 1.5),
      ),
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Column(
        children: [
          // Header with order tracking
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: _getStatusColor(status).withOpacity(0.05),
              borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
            ),
            child: Column(
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    Text('${isRtl ? 'طلب' : 'Order'} #$orderId',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14.5, color: Colors.blue.shade900)),
                    Expanded(
                      child: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 8.0),
                        child: Center(
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(color: Colors.blueGrey.shade200, width: 0.8),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.04),
                                  blurRadius: 4,
                                  offset: const Offset(0, 1),
                                ),
                              ],
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(
                                  isExclusiveOrder ? Icons.verified_rounded : Icons.storefront_rounded,
                                  size: 14,
                                  color: isExclusiveOrder ? const Color(0xFF2563EB) : Colors.blueGrey.shade700,
                                ),
                                const SizedBox(width: 5),
                                Flexible(
                                  child: Text(
                                    storeName,
                                    style: TextStyle(
                                      fontWeight: FontWeight.w700,
                                      fontSize: 12,
                                      color: isExclusiveOrder ? const Color(0xFF1D4ED8) : Colors.blueGrey.shade800,
                                    ),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: _getStatusColor(status).withOpacity(0.1),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: _getStatusColor(status).withOpacity(0.3)),
                      ),
                      child: Text(
                        _getStatusText(status),
                        style: TextStyle(color: _getStatusColor(status), fontSize: 12, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                // Order Tracking Timeline
                _OrderTimeline(status: status, isRtl: isRtl),
              ],
            ),
          ),

          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Date & Payment Method
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(date, style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                    if (paymentMethod.isNotEmpty)
                      Row(
                        children: [
                          Icon(Icons.payment, size: 14, color: Colors.grey.shade500),
                          const SizedBox(width: 4),
                          Text(paymentMethod, style: TextStyle(color: Colors.grey.shade600, fontSize: 12)),
                        ],
                      ),
                  ],
                ),

                const SizedBox(height: 12),

                // Invoice / Financial Summary
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.blue.shade50.withOpacity(0.5),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.blue.shade100),
                  ),
                  child: Column(
                    children: [
                      Row(
                        children: [
                          Icon(Icons.receipt_long, size: 16, color: Colors.blue.shade700),
                          const SizedBox(width: 6),
                          Text(isRtl ? 'الفاتورة' : 'Invoice',
                              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Colors.blue.shade700)),
                        ],
                      ),
                      const SizedBox(height: 8),
                      // Items
                      if (items.isNotEmpty)
                        ...items.map((item) {
                          final pName = item['product_name']?.toString() ?? (isRtl ? 'منتج' : 'Product');
                          final qty = num.tryParse(item['quantity']?.toString() ?? '1') ?? 1;
                          double uPrice = double.tryParse((item['price_at_time'] ?? item['price_sdg'] ?? 0).toString()) ?? 0.0;
                          if (uPrice == 0 && items.length == 1 && totalAmount > 0) {
                            uPrice = (totalAmount - transportFee) / qty;
                          }
                          final lineTotal = uPrice * qty;
                          final label = qty > 1 
                              ? '$pName ($qty × ${NumberFormat('#,##0').format(uPrice)})'
                              : '$pName (1 × ${NumberFormat('#,##0').format(uPrice)})';
                          return Padding(
                            padding: const EdgeInsets.only(bottom: 4),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Expanded(
                                  child: Text(
                                    label,
                                    style: TextStyle(fontSize: 13, color: Colors.grey.shade900, fontWeight: FontWeight.bold),
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                                Text(
                                  '${NumberFormat('#,##0').format(lineTotal > 0 ? lineTotal : uPrice)} ${isRtl ? 'ج.س' : 'SDG'}',
                                  style: TextStyle(fontSize: 14, color: Colors.grey.shade900, fontWeight: FontWeight.bold),
                                ),
                              ],
                            ),
                          );
                        }),
                      if (isSelfPickup) ...[
                        const Divider(height: 12),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Row(
                              children: [
                                const Icon(Icons.warehouse_outlined, size: 16, color: Color(0xFF0F766E)),
                                const SizedBox(width: 6),
                                Text(
                                  isRtl ? 'رسوم الترحيل والتنزيل (استلام ذاتي)' : 'Transport & Offloading (Self-Pickup)',
                                  style: const TextStyle(fontSize: 13, color: Color(0xFF0F766E), fontWeight: FontWeight.bold),
                                ),
                              ],
                            ),
                            Text(
                              '0.00 ${isRtl ? 'ج.س' : 'SDG'}',
                              style: const TextStyle(fontSize: 14, color: Color(0xFF0F766E), fontWeight: FontWeight.bold),
                            ),
                          ],
                        ),
                      ] else if (transportFee > 0 || (order['offloading_fee'] != null && order['offloading_fee'] > 0)) ...[
                        const Divider(height: 12),
                        if (transportFee > 0)
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(isRtl ? 'رسوم الترحيل' : 'Transport Fee',
                                style: TextStyle(fontSize: 13, color: Colors.orange.shade700)),
                            Text('${NumberFormat('#,##0').format(transportFee)} ${isRtl ? 'ج.س' : 'SDG'}',
                                style: TextStyle(fontSize: 13, color: Colors.orange.shade700)),
                          ],
                        ),
                        if (order['offloading_fee'] != null && order['offloading_fee'] > 0) ...[
                          const SizedBox(height: 4),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(isRtl ? 'رسوم التنزيل' : 'Offloading Fee',
                                  style: TextStyle(fontSize: 13, color: Colors.orange.shade700)),
                              Text('${NumberFormat('#,##0').format(order['offloading_fee'])} ${isRtl ? 'ج.س' : 'SDG'}',
                                  style: TextStyle(fontSize: 13, color: Colors.orange.shade700)),
                            ],
                          ),
                        ]
                      ],
                      if (items.length > 1 || (!isSelfPickup && (transportFee > 0 || (order['offloading_fee'] != null && order['offloading_fee'] > 0)))) ...[
                        const Divider(height: 16),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(isRtl ? 'إجمالي الطلب' : 'Total',
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                            Text('${NumberFormat('#,##0').format(totalAmount)} ${isRtl ? 'ج.س' : 'SDG'}',
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                          ],
                        ),
                      ],
                      if (paymentMethod == 'partial_cod') ...[
                      const SizedBox(height: 8),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(isRtl ? 'الدفعة المقدمة (عربون)' : 'Upfront Deposit',
                              style: TextStyle(fontSize: 13, color: Colors.blue.shade700)),
                          Text('${NumberFormat('#,##0').format(order['upfront_payment'] ?? 0)} ${isRtl ? 'ج.س' : 'SDG'}',
                              style: TextStyle(fontSize: 13, color: Colors.blue.shade700)),
                        ],
                      ),
                      ],
                      if (paymentStatus != 'under_review') ...[
                        const SizedBox(height: 4),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(isRtl ? 'المدفوع' : 'Paid',
                                style: TextStyle(fontSize: 13, color: Colors.green.shade700)),
                            Text('${NumberFormat('#,##0').format(paidAmount)} ${isRtl ? 'ج.س' : 'SDG'}',
                                style: TextStyle(fontSize: 13, color: Colors.green.shade700)),
                          ],
                        ),
                      ],
                      if (paymentStatus == 'under_review' && pendingReceiptAmount > 0) ...[
                        const SizedBox(height: 4),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              isRtl ? 'المبلغ في الإشعارات:' : 'Amount in Receipts:',
                              style: const TextStyle(fontSize: 13, color: Color(0xFFD97706), fontWeight: FontWeight.bold),
                            ),
                            Text(
                              '${NumberFormat('#,##0').format(pendingReceiptAmount)} ${isRtl ? 'ج.س' : 'SDG'} (${underReviewReceipts.length > 1 ? '${underReviewReceipts.length} ${isRtl ? "إشعارات" : "receipts"} - ' : ''}${isRtl ? "قيد المراجعة" : "Under Review"})',
                              style: const TextStyle(fontSize: 13, color: Color(0xFFD97706), fontWeight: FontWeight.bold),
                            ),
                          ],
                        ),
                      ],
                      if (remaining > 0) ...[
                        const SizedBox(height: 4),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(isRtl ? 'المتبقي' : 'Remaining',
                                style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.red.shade700)),
                            Text('${NumberFormat('#,##0').format(remaining)} ${isRtl ? 'ج.س' : 'SDG'}',
                                style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.red.shade700)),
                          ],
                        ),
                      ],
                      const SizedBox(height: 8),
                      // Payment status badge
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.symmetric(vertical: 7, horizontal: 8),
                        decoration: BoxDecoration(
                          color: _getPaymentColor(paymentStatus).withOpacity(0.1),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(
                            color: _getPaymentColor(paymentStatus).withOpacity(0.25),
                            width: 1,
                          ),
                        ),
                        child: Text(
                          _getPaymentText(paymentStatus),
                          textAlign: TextAlign.center,
                          style: TextStyle(
                            color: _getPaymentColor(paymentStatus),
                            fontWeight: FontWeight.bold,
                            fontSize: 12.5,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      const SizedBox(height: 8),
                      // Direct Communication Actions (Chat & In-App Call)
                      Row(
                        children: [
                          Expanded(
                            child: _BuyerChatWithMerchantButton(
                              order: order,
                              isRtl: isRtl,
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: _BuyerCallWithMerchantButton(
                              order: order,
                              isRtl: isRtl,
                            ),
                          ),
                        ],
                      ),
                      
                      // Transport Covered by Another Order Banner
                      if (isTransportCovered && !isSelfPickup) ...[
                        const SizedBox(height: 12),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF0FDF4),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: const Color(0xFF86EFAC)),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.local_shipping_rounded, color: Color(0xFF16A34A), size: 20),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Text(
                                  isRtl
                                      ? '✅ الترحيل مشمول في طلب آخر من نفس الشحنة — رسوم الترحيل = 0'
                                      : '✅ Transport covered by another order in the same shipment — Fee = 0',
                                  style: const TextStyle(fontSize: 12, color: Color(0xFF166534), fontWeight: FontWeight.w600),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],

                      // Fee Approval Logic
                      if (!isSelfPickup && !isTransportCovered && order['buyer_approved_fees'] == null && status != 'cancelled') ...[
                        const SizedBox(height: 12),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: Colors.blue.shade50,
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: Colors.blue.shade200),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                isRtl
                                    ? (isExclusiveOrder ? 'بانتظار تحديد رسوم التوصيل من إدارة مقاييس' : 'بانتظار إضافة رسوم التوصيل')
                                    : (isExclusiveOrder ? 'Waiting for Magaiees to set shipping fees' : 'Waiting for shipping fees'),
                                style: TextStyle(fontWeight: FontWeight.bold, color: Colors.blue.shade900),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                isRtl 
                                  ? (isExclusiveOrder
                                      ? 'تم إرسال طلبك الحصري بنجاح. بانتظار قيام فريق مقاييس بتحديد رسوم الترحيل والتنزيل بناءً على عنوانك. ستصلك رسالة تنبيه للموافقة عليها.'
                                      : 'تم إرسال الطلب بنجاح. بانتظار قيام التاجر بتحديد رسوم الترحيل والتنزيل بناءً على عنوانك. ستصلك رسالة تنبيه للموافقة.')
                                  : (isExclusiveOrder
                                      ? 'Exclusive order sent successfully. Waiting for Magaiees team to set transport fees based on your address.'
                                      : 'Order sent successfully. Waiting for merchant to set transport and offloading fees based on your address. You will be notified to approve.'),
                                style: TextStyle(fontSize: 12, color: Colors.blue.shade800),
                              ),
                            ],
                          ),
                        ),
                      ] else if (!isSelfPickup && order['buyer_approved_fees'] == false && status != 'cancelled') ...[
                        const SizedBox(height: 12),
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: Colors.orange.shade50,
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: Colors.orange.shade200),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                isRtl
                                    ? (isExclusiveOrder ? 'قام فريق مقاييس بإضافة رسوم التوصيل' : 'قام التاجر بإضافة رسوم التوصيل')
                                    : (isExclusiveOrder ? 'Magaiees team has added shipping fees' : 'Merchant has added shipping fees'),
                                style: TextStyle(fontWeight: FontWeight.bold, color: Colors.orange.shade900),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                isRtl ? 'الرجاء مراجعة التكلفة الإجمالية أعلاه والموافقة عليها للاستمرار.' : 'Please review the new total above and approve to continue.',
                                style: TextStyle(fontSize: 12, color: Colors.orange.shade800),
                              ),
                              const SizedBox(height: 12),
                              Row(
                                children: [
                                  Expanded(
                                    child: OutlinedButton(
                                      onPressed: () => _rejectFees(context, ref, order),
                                      style: OutlinedButton.styleFrom(foregroundColor: Colors.red),
                                      child: Text(isRtl ? 'رفض وإلغاء' : 'Reject'),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: FilledButton(
                                      onPressed: () => _approveFees(context, ref, order),
                                      style: FilledButton.styleFrom(backgroundColor: Colors.green),
                                      child: Text(isRtl ? 'موافق' : 'Approve'),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ] else if (status == 'cancelled') ...[
                        const SizedBox(height: 12),
                        Container(
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFEF2F2),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: const Color(0xFFFECACA), width: 1.2),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  const Icon(Icons.cancel_outlined, color: Color(0xFFDC2626), size: 20),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      isRtl ? 'تم إلغاء هذا الطلب من قبلك' : 'This order has been cancelled by you',
                                      style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF991B1B), fontSize: 13.5),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 6),
                              Text(
                                isRtl
                                    ? 'قمت بإلغاء الطلبية من التاجر. هل تود التراجع والضغط على موافق لتعديل الحالة عند التاجر للموافقة واستئناف تجهيز الطلب؟'
                                    : 'You cancelled this order. Would you like to backtrack and approve so the status changes to approved on the merchant side?',
                                style: const TextStyle(color: Color(0xFFB91C1C), fontSize: 12.5, height: 1.35),
                              ),
                              const SizedBox(height: 12),
                              SizedBox(
                                width: double.infinity,
                                child: FilledButton.icon(
                                  style: FilledButton.styleFrom(
                                    backgroundColor: const Color(0xFF059669),
                                    padding: const EdgeInsets.symmetric(vertical: 11),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                    elevation: 1,
                                  ),
                                  onPressed: () => _approveFees(context, ref, order),
                                  icon: const Icon(Icons.check_circle_outline, size: 18),
                                  label: Text(
                                    isRtl ? 'تراجع والضغط على موافق (تفعيل الطلب)' : 'Backtrack & Approve Order',
                                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5),
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],

                      // Show green "Fees Approved" badge for non-self-pickup
                      if (!isSelfPickup && !isTransportCovered && order['buyer_approved_fees'] == true && status != 'cancelled') ...[
                        const SizedBox(height: 12),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 12),
                          decoration: BoxDecoration(
                            color: const Color(0xFFECFDF5),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: const Color(0xFFA7F3D0)),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.check_circle_rounded, color: Color(0xFF059669), size: 18),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  isRtl ? 'تمت الموافقة على رسوم التوصيل' : 'Shipping fees approved',
                                  style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold, color: Color(0xFF065F46)),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],

                      // Rejection reason banner (if previous receipt was rejected)
                      if (order['notes']?.toString().contains('rejection_reason:') == true && paymentStatus == 'unpaid' && status != 'cancelled') ...[
                        Builder(
                          builder: (context) {
                            final notesStr = order['notes']!.toString();
                            final rawReason = notesStr.split('rejection_reason:').last.split('\n')[0].trim();
                            final rejectionReason = rawReason.isNotEmpty ? rawReason : (isRtl ? 'الإشعار غير واضح أو البيانات غير مطابقة' : 'Unverified receipt');

                            String? rejectedUrl;
                            if (notesStr.contains('rejected_receipt_url:')) {
                              rejectedUrl = notesStr.split('rejected_receipt_url:').last.split('|')[0].trim().split('\n')[0].trim();
                            }

                            return Container(
                              width: double.infinity,
                              margin: const EdgeInsets.only(top: 12),
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: const Color(0xFFFEF2F2),
                                border: Border.all(color: const Color(0xFFFCA5A5), width: 1.5),
                                borderRadius: BorderRadius.circular(12),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.red.withOpacity(0.06),
                                    blurRadius: 8,
                                    offset: const Offset(0, 3),
                                  ),
                                ],
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.all(6),
                                        decoration: BoxDecoration(
                                          color: Colors.red.shade100,
                                          shape: BoxShape.circle,
                                        ),
                                        child: const Icon(Icons.cancel_rounded, color: Color(0xFFDC2626), size: 20),
                                      ),
                                      const SizedBox(width: 10),
                                      Expanded(
                                        child: Text(
                                          isRtl
                                              ? (isExclusiveOrder ? 'تم رفض إشعار التحويل من قِبل إدارة مقاييس!' : 'تم رفض إشعار التحويل من قِبل المتجر!')
                                              : 'Payment Receipt Was Rejected!',
                                          style: const TextStyle(
                                            color: Color(0xFF991B1B),
                                            fontWeight: FontWeight.bold,
                                            fontSize: 14,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 10),
                                  Container(
                                    width: double.infinity,
                                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                                    decoration: BoxDecoration(
                                      color: Colors.white,
                                      borderRadius: BorderRadius.circular(8),
                                      border: Border.all(color: const Color(0xFFFECACA)),
                                    ),
                                    child: Row(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        const Icon(Icons.info_outline_rounded, color: Color(0xFFDC2626), size: 18),
                                        const SizedBox(width: 8),
                                        Expanded(
                                          child: RichText(
                                            text: TextSpan(
                                              style: const TextStyle(fontSize: 13, color: Color(0xFF1F2937), fontFamily: 'Cairo'),
                                              children: [
                                                TextSpan(
                                                  text: isRtl ? 'سبب الرفض: ' : 'Reason: ',
                                                  style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF991B1B)),
                                                ),
                                                TextSpan(
                                                  text: rejectionReason,
                                                  style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFFDC2626)),
                                                ),
                                              ],
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  const SizedBox(height: 8),
                                  Text(
                                    isRtl
                                        ? (isExclusiveOrder
                                            ? 'يرجى مراجعة سبب الرفض وتصحيح التحويل ثم إعادة إرفاق إشعار جديد وصحيح حتى تقوم إدارة مقاييس بتأكيد طلبك.'
                                            : 'يرجى مراجعة سبب الرفض وتصحيح التحويل ثم إعادة إرفاق إشعار جديد وصحيح حتى يقوم التاجر بتأكيد طلبك.')
                                        : 'Please check the rejection reason and re-upload a valid receipt so your order can be confirmed.',
                                    style: TextStyle(fontSize: 11.5, color: Colors.red.shade800, height: 1.3),
                                  ),
                                  if (rejectedUrl != null && rejectedUrl.isNotEmpty && rejectedUrl.startsWith('http')) ...[
                                    const SizedBox(height: 10),
                                    InkWell(
                                      onTap: () {
                                        showDialog(
                                          context: context,
                                          builder: (dCtx) => Dialog(
                                            backgroundColor: Colors.transparent,
                                            insetPadding: const EdgeInsets.all(16),
                                            child: Stack(
                                              alignment: Alignment.center,
                                              children: [
                                                InteractiveViewer(
                                                  child: ClipRRect(
                                                    borderRadius: BorderRadius.circular(12),
                                                    child: Image.network(
                                                      rejectedUrl!,
                                                      fit: BoxFit.contain,
                                                    ),
                                                  ),
                                                ),
                                                Positioned(
                                                  top: 10,
                                                  right: 10,
                                                  child: IconButton(
                                                    style: IconButton.styleFrom(backgroundColor: Colors.black54),
                                                    icon: const Icon(Icons.close, color: Colors.white),
                                                    onPressed: () => Navigator.pop(dCtx),
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ),
                                        );
                                      },
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Icon(Icons.visibility_outlined, size: 16, color: Colors.red.shade700),
                                          const SizedBox(width: 4),
                                          Text(
                                            isRtl ? 'معاينة الإشعار المرفوض' : 'View Rejected Receipt',
                                            style: TextStyle(
                                              fontSize: 12,
                                              fontWeight: FontWeight.bold,
                                              color: Colors.red.shade700,
                                              decoration: TextDecoration.underline,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ],
                              ),
                            );
                          },
                        ),
                      ],

                      if (order['payment_method'] == 'cod' && status != 'cancelled') ...[
                        const SizedBox(height: 10),
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: const Color(0xFFECFDF5),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: const Color(0xFFA7F3D0)),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.local_shipping_rounded, color: Color(0xFF059669), size: 20),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  isRtl
                                      ? 'الدفع نقداً عند الاستلام (COD): سيتم سداد المبلغ المطلوب لمندوب التوصيل عند استلام الشحنة وتفريغها في الموقع.'
                                      : 'Cash on Delivery (COD): Payment will be handed to the delivery agent upon materials arrival and offloading.',
                                  style: const TextStyle(fontSize: 12, color: Color(0xFF065F46), fontWeight: FontWeight.w600, height: 1.4),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],

                      // Multi-receipts indicator banner if receipts exist
                      if (allReceipts.isNotEmpty && status != 'cancelled') ...[
                        const SizedBox(height: 8),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF0FDFA),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: const Color(0xFF99F6E4)),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.receipt_long_rounded, color: Color(0xFF0F766E), size: 18),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  isRtl
                                      ? 'تم إرفاق ${allReceipts.length} إشعار تحويل (المسدد: ${NumberFormat('#,##0').format(effectivePaid)} من ${NumberFormat('#,##0').format(totalAmount)} ج.س)'
                                      : '${allReceipts.length} receipts attached (${NumberFormat('#,##0').format(effectivePaid)} / ${NumberFormat('#,##0').format(totalAmount)} SDG)',
                                  style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: Color(0xFF0F766E)),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],

                      // Action buttons: Cancel & Upload/View Receipts (active whenever not cancelled and not COD)
                      if (status != 'cancelled' && order['payment_method'] != 'cod') ...[
                        const SizedBox(height: 10),
                        Row(
                          children: [
                            // Cancel Order Button (Active while pending)
                            if (status == 'pending') ...[
                              Expanded(
                                flex: 1,
                                child: OutlinedButton.icon(
                                  style: OutlinedButton.styleFrom(
                                    foregroundColor: Colors.red.shade700,
                                    side: BorderSide(color: Colors.red.shade300),
                                    padding: const EdgeInsets.symmetric(vertical: 10),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                  ),
                                  icon: const Icon(Icons.cancel_outlined, size: 16),
                                  label: Text(
                                    isRtl ? 'إلغاء' : 'Cancel',
                                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                                  ),
                                  onPressed: () => _cancelOrder(context, ref, order),
                                ),
                              ),
                              const SizedBox(width: 8),
                            ],
                            // Upload / View Receipt Button (Active and enabled whenever remaining > 0 or paymentStatus != 'paid', or to view approved receipts)
                            Expanded(
                              flex: status == 'pending' ? 2 : 1,
                              child: FilledButton.icon(
                                style: FilledButton.styleFrom(
                                  backgroundColor: (paymentStatus == 'unpaid' && order['notes']?.toString().contains('rejection_reason:') == true)
                                      ? const Color(0xFFDC2626)
                                      : (remaining > 0 && effectivePaid > 0)
                                          ? const Color(0xFFD97706)
                                          : (remaining == 0 && paymentStatus == 'paid')
                                              ? const Color(0xFF059669)
                                              : const Color(0xFF0F766E),
                                  padding: const EdgeInsets.symmetric(vertical: 10),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                ),
                                icon: Icon(
                                  (remaining == 0 && paymentStatus == 'paid')
                                      ? Icons.check_circle_outline_rounded
                                      : (paymentStatus == 'under_review'
                                          ? Icons.receipt_long_rounded
                                          : (order['notes']?.toString().contains('rejection_reason:') == true ? Icons.refresh_rounded : Icons.upload_file_rounded)),
                                  size: 18,
                                ),
                                label: Text(
                                  isRtl
                                      ? (remaining == 0 && paymentStatus == 'paid'
                                          ? (allReceipts.isNotEmpty ? 'عرض الإشعارات المعتمدة (${allReceipts.length}) ✅' : 'تم السداد بالكامل ✅')
                                          : (paymentStatus == 'under_review'
                                              ? (allReceipts.isNotEmpty ? 'عرض وإضافة إشعارات (${allReceipts.length})' : 'إرفاق إشعار التحويل')
                                              : (order['notes']?.toString().contains('rejection_reason:') == true
                                                  ? (allReceipts.isNotEmpty ? 'إعادة إرفاق إشعار جديد (${allReceipts.length})' : 'إعادة إرفاق إشعار جديد')
                                                  : (remaining > 0 && effectivePaid > 0
                                                      ? 'إرفاق إشعار لباقي المبلغ (${NumberFormat('#,##0').format(remaining)} ج.س)'
                                                      : (remaining > 0
                                                          ? 'إرفاق إشعار التحويل (${NumberFormat('#,##0').format(remaining)} ج.س)'
                                                          : 'إرفاق إشعار التحويل')))))
                                      : (remaining == 0 && paymentStatus == 'paid'
                                          ? 'View Approved Receipts ✅'
                                          : (paymentStatus == 'under_review'
                                              ? (allReceipts.isNotEmpty ? 'View & Add Receipts (${allReceipts.length})' : 'Upload Receipt')
                                              : (order['notes']?.toString().contains('rejection_reason:') == true
                                                  ? 'Re-upload Valid Receipt'
                                                  : (remaining > 0 && effectivePaid > 0
                                                      ? 'Attach Remaining Receipt (${NumberFormat('#,##0').format(remaining)} SDG)'
                                                      : 'Upload Receipt')))),
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                                ),
                                onPressed: () {
                                  double defAmount = remaining > 0 ? remaining : totalAmount;
                                  if (paidAmount == 0 && order['upfront_payment'] != null && order['upfront_payment'] > 0) {
                                    defAmount = double.tryParse(order['upfront_payment']?.toString() ?? '') ?? defAmount;
                                  }
                                  _showUploadReceiptDialog(context, ref, order, isRtl, defAmount);
                                },
                              ),
                            ),
                          ],
                        ),
                      ],
                    ],
                  ),
                ),
                if (status == 'delivered' && items.isNotEmpty) ...[
                  const SizedBox(height: 12),
                  SizedBox(
                    width: double.infinity,
                    child: OutlinedButton.icon(
                      icon: const Icon(Icons.star_outline, color: Colors.amber),
                      label: Text(isRtl ? 'إضافة تقييم' : 'Leave a Review', style: const TextStyle(color: Colors.amber)),
                      style: OutlinedButton.styleFrom(
                        side: const BorderSide(color: Colors.amber),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                      onPressed: () {
                        _showReviewDialog(context, items.first, isRtl);
                      },
                    ),
                  ),
                ]
              ],
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _approveFees(BuildContext context, WidgetRef ref, Map<String, dynamic> order) async {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    try {
      String cleanNotes = (order['notes']?.toString() ?? '')
          .replaceAll('\nBuyer rejected shipping fees.', '')
          .replaceAll('Buyer rejected shipping fees.', '')
          .replaceAll('\nتم إلغاء الطلب بواسطة المشتري.', '')
          .replaceAll('تم إلغاء الطلب بواسطة المشتري.', '')
          .trim();
      cleanNotes += '\n✅ وافق المشتري على رسوم التوصيل (تم التراجع والموافقة)';

      await Supabase.instance.client.from('orders').update({
        'status': 'pending', // Revert status from cancelled back to pending
        'buyer_approved_fees': true,
        'notes': cleanNotes,
        if (order['buyer_id'] != null) 'buyer_id': order['buyer_id'],
        if (order['store_id'] != null) 'store_id': order['store_id'],
      }).eq('id', order['id']);

      // Smart group coordination: auto-set transport=0 for other orders in same group
      final notes = order['notes']?.toString() ?? '';
      final groupMatch = RegExp(r'\[طلب مجمّع: (GRP-[^|]+)').firstMatch(notes);
      final gId = groupMatch?.group(1)?.trim();
      if (gId != null && gId.isNotEmpty) {
        try {
          // Find other orders with same group_id
          final otherOrders = await Supabase.instance.client
              .from('orders')
              .select('id, notes, transport_fee, buyer_approved_fees, store_id')
              .eq('buyer_id', order['buyer_id'])
              .neq('id', order['id'])
              .not('status', 'eq', 'cancelled');
          final grouped = otherOrders.where((o) {
            final oNotes = o['notes']?.toString() ?? '';
            return oNotes.contains(gId);
          }).toList();
          // Auto-set transport=0 for grouped orders that haven't been approved yet
          for (var gOrder in grouped) {
            if (gOrder['buyer_approved_fees'] != true) {
              await Supabase.instance.client.from('orders').update({
                'transport_fee': 0,
                'offloading_fee': 0,
                'buyer_approved_fees': true,
                'notes': (gOrder['notes'] ?? '') + '\n✅ الترحيل مشمول في طلب آخر #${OrderNumberFormatter.formatFromMap(order)}',
                if (gOrder['store_id'] != null) 'store_id': gOrder['store_id'],
              }).eq('id', gOrder['id']);
            }
          }
        } catch (groupErr) {
          debugPrint('Group transport sync error: $groupErr');
        }
      }

      ref.invalidate(buyerOrdersProvider);
      NotificationSoundService.play();
      if (context.mounted) {
        AppNotification.showSuccess(
          context,
          isRtl ? 'تمت الموافقة وتحديث الحالة عند التاجر بنجاح ✓' : 'Approved and updated on merchant side successfully ✓',
        );
      }
    } catch (e) {
      if (context.mounted) {
        AppNotification.showError(
          context,
          e.toString(),
        );
      }
    }
  }

  Future<void> _rejectFees(BuildContext context, WidgetRef ref, Map<String, dynamic> order) async {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    try {
      await Supabase.instance.client.from('orders').update({
        'status': 'cancelled',
        'buyer_approved_fees': false,
        'notes': (order['notes'] ?? '') + '\nBuyer rejected shipping fees.',
        if (order['buyer_id'] != null) 'buyer_id': order['buyer_id'],
        if (order['store_id'] != null) 'store_id': order['store_id'],
      }).eq('id', order['id']);
      ref.invalidate(buyerOrdersProvider);
      if (context.mounted) {
        AppNotification.showInfo(
          context,
          isRtl ? 'تم إلغاء الطلب بنجاح، ويمكنك التراجع في أي وقت.' : 'Order cancelled. You can backtrack anytime.',
        );
      }
    } catch (e) {
      if (context.mounted) {
        AppNotification.showError(
          context,
          e.toString(),
        );
      }
    }
  }

  Future<void> _cancelOrder(BuildContext context, WidgetRef ref, Map<String, dynamic> order) async {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: Colors.red.shade600, size: 28),
            const SizedBox(width: 8),
            Text(isRtl ? 'تأكيد الإلغاء' : 'Confirm Cancellation',
                style: TextStyle(fontWeight: FontWeight.bold, color: Colors.red.shade800)),
          ],
        ),
        content: Text(
          isRtl ? 'هل أنت متأكد من إلغاء هذا الطلب؟ لا يمكن التراجع عن هذا الإجراء.' : 'Are you sure you want to cancel this order? This action cannot be undone.',
          style: const TextStyle(fontSize: 14),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: Text(isRtl ? 'لا، تراجع' : 'No, Go Back'),
          ),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: Colors.red.shade600),
            onPressed: () => Navigator.pop(ctx, true),
            child: Text(isRtl ? 'نعم، إلغاء الطلب' : 'Yes, Cancel Order'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    try {
      await Supabase.instance.client.from('orders').update({
        'status': 'cancelled',
        'notes': (order['notes'] ?? '') + '\nتم إلغاء الطلب بواسطة المشتري.',
        if (order['store_id'] != null) 'store_id': order['store_id'],
      }).eq('id', order['id']);
      ref.invalidate(buyerOrdersProvider);
      if (context.mounted) {
        AppNotification.showInfo(
          context,
          isRtl ? 'تم إلغاء الطلب بنجاح' : 'Order cancelled successfully',
        );
      }
    } catch (e) {
      if (context.mounted) {
        AppNotification.showError(context, e.toString());
      }
    }
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'pending': return Colors.orange.shade700;
      case 'processing': return Colors.blue.shade700;
      case 'shipped': return Colors.indigo.shade700;
      case 'delivered': return Colors.green.shade700;
      case 'cancelled': return Colors.red.shade700;
      default: return Colors.grey.shade700;
    }
  }

  String _getStatusText(String status) {
    switch (status) {
      case 'pending': return isRtl ? 'قيد الانتظار' : 'Pending';
      case 'processing': return isRtl ? 'مقبول - قيد التجهيز' : 'Accepted';
      case 'shipped': return isRtl ? 'تم الشحن' : 'Shipped';
      case 'delivered': return isRtl ? 'تم التسليم' : 'Delivered';
      case 'cancelled': return isRtl ? 'ملغي' : 'Cancelled';
      default: return status;
    }
  }

  Color _getPaymentColor(String paymentStatus) {
    if (paymentStatus == 'unpaid' && order['notes']?.toString().contains('rejection_reason:') == true) {
      return const Color(0xFFDC2626);
    }
    switch (paymentStatus) {
      case 'under_review': return Colors.blue.shade700;
      case 'paid': return Colors.green.shade700;
      case 'partial': return Colors.orange.shade700;
      default: return Colors.red.shade700;
    }
  }

  String _getPaymentText(String paymentStatus) {
    if (paymentStatus == 'unpaid' && order['notes']?.toString().contains('rejection_reason:') == true) {
      return isRtl ? '❌ إشعار مرفوض' : '❌ Receipt Rejected';
    }
    switch (paymentStatus) {
      case 'under_review': return isRtl ? '⏳ قيد المراجعة' : '⏳ Under Review';
      case 'paid': return isRtl ? '✅ دفع مكتمل' : '✅ Fully Paid';
      case 'partial': return isRtl ? '⚠️ دفع جزئي' : '⚠️ Partially Paid';
      default: return isRtl ? '❌ لم يتم الدفع بعد' : '❌ Unpaid';
    }
  }


  static List<Map<String, dynamic>> _parseAllReceipts(String notes, {String? fallbackUrl}) {
    final List<Map<String, dynamic>> list = [];
    final regExp = RegExp(r'((?:approved_|rejected_)?receipt_url):\s*([^\s|]+)([^;\n]*)');
    final matches = regExp.allMatches(notes);

    for (final m in matches) {
      final tag = m.group(1) ?? 'receipt_url';
      final url = (m.group(2) ?? '').trim();
      final rest = (m.group(3) ?? '').trim();
      final fullMatch = m.group(0) ?? '';

      String status = 'under_review';
      if (tag == 'approved_receipt_url') {
        status = 'approved';
      } else if (tag == 'rejected_receipt_url') {
        status = 'rejected';
      }

      String refNum = '';
      String bank = 'بنكك (بنك الخرطوم)';
      double amount = 0.0;

      if (rest.contains('ref:')) {
        refNum = rest.split('ref:')[1].split('|')[0].trim().split('\n')[0].trim();
      }
      if (rest.contains('bank:')) {
        bank = rest.split('bank:')[1].split('|')[0].trim().split('\n')[0].trim();
      }
      if (rest.contains('amount:')) {
        final amtStr = rest.split('amount:')[1].split('|')[0].trim().split('\n')[0].trim();
        amount = double.tryParse(amtStr) ?? 0.0;
      }

      if (url.isNotEmpty && url.startsWith('http')) {
        list.add({
          'tag': tag,
          'url': url,
          'ref': refNum,
          'bank': bank,
          'amount': amount,
          'status': status,
          'rawMatch': fullMatch,
        });
      }
    }

    if (list.isEmpty && fallbackUrl != null && fallbackUrl.isNotEmpty && fallbackUrl.startsWith('http')) {
      list.add({
        'tag': 'receipt_url',
        'url': fallbackUrl,
        'ref': '',
        'bank': 'بنكك (بنك الخرطوم)',
        'amount': 0.0,
        'status': 'under_review',
        'rawMatch': 'receipt_url:$fallbackUrl',
      });
    }

    return list;
  }

  void _showReceiptImagePreview(BuildContext context, String imageUrl) {
    showDialog(
      context: context,
      builder: (context) => Dialog(
        backgroundColor: Colors.transparent,
        insetPadding: const EdgeInsets.all(10),
        child: Stack(
          alignment: Alignment.center,
          children: [
            InteractiveViewer(
              minScale: 0.5,
              maxScale: 4.0,
              child: ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: Image.network(
                  imageUrl,
                  fit: BoxFit.contain,
                  errorBuilder: (c, e, s) => Container(
                    color: Colors.white,
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: const [
                        Icon(Icons.broken_image_rounded, size: 64, color: Colors.grey),
                        SizedBox(height: 8),
                        Text('تعذر تحميل صورة الإشعار', style: TextStyle(color: Colors.grey)),
                      ],
                    ),
                  ),
                ),
              ),
            ),
            Positioned(
              top: 10,
              right: 10,
              child: IconButton(
                style: IconButton.styleFrom(backgroundColor: Colors.black54),
                icon: const Icon(Icons.close, color: Colors.white, size: 24),
                onPressed: () => Navigator.pop(context),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showEditReceiptDialog({
    required BuildContext context,
    required BuildContext parentContext,
    required WidgetRef ref,
    required dynamic order,
    required Map<String, dynamic> receipt,
    required bool isRtl,
    required double totalAmount,
    required Function(String updatedNotes, List<Map<String, dynamic>> updatedReceipts) onReceiptUpdated,
  }) {
    if (receipt['status'] == 'approved') {
      AppNotification.showError(context, isRtl ? 'لا يمكن تعديل الإشعار بعد اعتماده من التاجر' : 'Cannot edit an approved receipt');
      return;
    }

    final String initialRef = receipt['ref']?.toString() ?? '';
    final double initialAmt = (receipt['amount'] as num?)?.toDouble() ?? 0.0;
    String initialBank = receipt['bank']?.toString() ?? 'بنكك (بنك الخرطوم)';
    final String currentImgUrl = receipt['url']?.toString() ?? '';

    final allowedBanks = [
      'بنكك (بنك الخرطوم)',
      'أوكاش (بنك أمدرمان الوطني)',
      'تحويل بنكي مباشر',
      'إيداع نقدي',
    ];
    if (!allowedBanks.contains(initialBank)) {
      allowedBanks.add(initialBank);
    }

    final editRefController = TextEditingController(text: initialRef);
    final editAmountController = TextEditingController(
      text: initialAmt > 0 ? (initialAmt % 1 == 0 ? initialAmt.toInt().toString() : initialAmt.toString()) : '',
    );

    Uint8List? editImageBytes;
    String? editImageExt;
    bool isSaving = false;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (dCtx) {
        return StatefulBuilder(
          builder: (dCtx, setDialogState) {
            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              titlePadding: const EdgeInsets.fromLTRB(20, 18, 20, 10),
              contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
              actionsPadding: const EdgeInsets.fromLTRB(20, 10, 20, 16),
              title: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: const Color(0xFF0F766E).withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(Icons.edit_note_rounded, color: Color(0xFF0F766E), size: 22),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      isRtl ? 'تعديل بيانات إشعار التحويل' : 'Edit Receipt Details',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, size: 20),
                    onPressed: isSaving ? null : () => Navigator.pop(dCtx),
                  ),
                ],
              ),
              content: SizedBox(
                width: 420,
                child: SingleChildScrollView(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Image preview & change button
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                        ),
                        child: Row(
                          children: [
                            ClipRRect(
                              borderRadius: BorderRadius.circular(8),
                              child: editImageBytes != null
                                  ? Image.memory(editImageBytes!, width: 60, height: 60, fit: BoxFit.cover)
                                  : (currentImgUrl.isNotEmpty
                                      ? Image.network(
                                          currentImgUrl,
                                          width: 60,
                                          height: 60,
                                          fit: BoxFit.cover,
                                          errorBuilder: (c, e, s) => Container(
                                            width: 60,
                                            height: 60,
                                            color: Colors.grey.shade200,
                                            child: const Icon(Icons.broken_image, size: 24, color: Colors.grey),
                                          ),
                                        )
                                      : Container(
                                          width: 60,
                                          height: 60,
                                          color: Colors.grey.shade200,
                                          child: const Icon(Icons.image, size: 24, color: Colors.grey),
                                        )),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    editImageBytes != null
                                        ? (isRtl ? 'تم اختيار صورة جديدة' : 'New image selected')
                                        : (isRtl ? 'صورة الإشعار الحالية' : 'Current Receipt Image'),
                                    style: TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 12.5,
                                      color: editImageBytes != null ? const Color(0xFF16A34A) : Colors.blueGrey.shade800,
                                    ),
                                  ),
                                  const SizedBox(height: 6),
                                  InkWell(
                                    onTap: isSaving
                                        ? null
                                        : () async {
                                            try {
                                              final picked = await ImagePickerHelper.pickImage(
                                                context,
                                                imageQuality: 70,
                                                maxWidth: 1000,
                                              );
                                              if (picked != null) {
                                                final bytes = await picked.readAsBytes();
                                                setDialogState(() {
                                                  editImageBytes = bytes;
                                                  editImageExt = picked.name.split('.').last;
                                                });
                                              }
                                            } catch (e) {
                                              debugPrint('Edit picker error: $e');
                                            }
                                          },
                                    child: Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        const Icon(Icons.photo_camera_rounded, size: 14, color: Color(0xFF0F766E)),
                                        const SizedBox(width: 4),
                                        Text(
                                          isRtl ? 'تغيير / تصوير صورة الإشعار' : 'Change / Capture Image',
                                          style: const TextStyle(
                                            fontSize: 12,
                                            color: Color(0xFF0F766E),
                                            fontWeight: FontWeight.bold,
                                            decoration: TextDecoration.underline,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 14),

                      // Bank selector
                      DropdownButtonFormField<String>(
                        decoration: InputDecoration(
                          labelText: isRtl ? 'البنك أو وسيلة الدفع' : 'Bank / Method',
                          border: const OutlineInputBorder(),
                          prefixIcon: const Icon(Icons.account_balance),
                          isDense: true,
                        ),
                        initialValue: initialBank,
                        items: allowedBanks.map((b) => DropdownMenuItem(value: b, child: Text(b, style: const TextStyle(fontSize: 13)))).toList(),
                        onChanged: isSaving ? null : (v) => setDialogState(() => initialBank = v ?? initialBank),
                      ),
                      const SizedBox(height: 12),

                      // Reference Number
                      TextFormField(
                        controller: editRefController,
                        enabled: !isSaving,
                        decoration: InputDecoration(
                          labelText: isRtl ? 'رقم الإشعار / المعاملة (المرجع)' : 'Transaction Reference #',
                          hintText: isRtl ? 'مثلاً: 12847920' : 'e.g. 12847920',
                          border: const OutlineInputBorder(),
                          prefixIcon: const Icon(Icons.tag_rounded),
                          isDense: true,
                        ),
                      ),
                      const SizedBox(height: 12),

                      // Amount
                      TextFormField(
                        controller: editAmountController,
                        enabled: !isSaving,
                        keyboardType: const TextInputType.numberWithOptions(decimal: true),
                        decoration: InputDecoration(
                          labelText: isRtl ? 'المبلغ المحول في هذا الإشعار (ج.س)' : 'Amount in this receipt (SDG)',
                          border: const OutlineInputBorder(),
                          prefixIcon: const Icon(Icons.money),
                          isDense: true,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              actions: [
                TextButton(
                  onPressed: isSaving ? null : () => Navigator.pop(dCtx),
                  child: Text(isRtl ? 'إلغاء' : 'Cancel'),
                ),
                FilledButton.icon(
                  style: FilledButton.styleFrom(
                    backgroundColor: const Color(0xFF0F766E),
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                  onPressed: isSaving
                      ? null
                      : () async {
                          final double newAmt = double.tryParse(editAmountController.text.trim()) ?? 0.0;
                          if (newAmt <= 0) {
                            AppNotification.showError(
                              context,
                              isRtl ? 'يرجى إدخال مبلغ صحيح أكبر من الصفر' : 'Please enter a valid amount',
                            );
                            return;
                          }

                          setDialogState(() => isSaving = true);
                          try {
                            String finalUrl = currentImgUrl;
                            final orderId = order['id']?.toString() ?? '';

                            if (editImageBytes != null && editImageExt != null) {
                              final fName = 'receipt_${orderId}_${DateTime.now().millisecondsSinceEpoch}.$editImageExt';
                              await Supabase.instance.client.storage
                                  .from('products_images')
                                  .uploadBinary(fName, editImageBytes!);
                              finalUrl = Supabase.instance.client.storage
                                  .from('products_images')
                                  .getPublicUrl(fName);
                            }

                            final newRef = editRefController.text.trim();
                            final newReceiptLine = 'receipt_url:$finalUrl | ref:$newRef | bank:$initialBank | amount:$newAmt';

                            final rawMatch = receipt['rawMatch']?.toString() ?? '';
                            String currentNotes = order['notes']?.toString() ?? '';
                            String updatedNotes;
                            if (rawMatch.isNotEmpty && currentNotes.contains(rawMatch)) {
                              updatedNotes = currentNotes.replaceFirst(rawMatch, newReceiptLine).replaceAll('\n\n', '\n').trim();
                            } else {
                              updatedNotes = '$currentNotes\n$newReceiptLine'.trim();
                            }

                            final updatedReceipts = _parseAllReceipts(updatedNotes);
                            final double newTotalPaid = updatedReceipts
                                .where((item) => item['status'] != 'rejected')
                                .fold(0.0, (s, item) => s + (item['amount'] as num).toDouble());

                            String newPaymentStatus = 'unpaid';
                            if (updatedReceipts.any((item) => item['status'] == 'under_review')) {
                              newPaymentStatus = 'under_review';
                            } else if (newTotalPaid >= totalAmount && totalAmount > 0) {
                              newPaymentStatus = 'paid';
                            } else if (newTotalPaid > 0) {
                              newPaymentStatus = 'partial';
                            }

                            await Supabase.instance.client.from('orders').update({
                              'notes': updatedNotes,
                              'paid_amount': newTotalPaid.round(),
                              'payment_status': newPaymentStatus,
                              'receipt_url': finalUrl,
                              if (order['store_id'] != null) 'store_id': order['store_id'],
                            }).eq('id', orderId);

                            order['notes'] = updatedNotes;
                            order['paid_amount'] = newTotalPaid.round();
                            order['payment_status'] = newPaymentStatus;
                            order['receipt_url'] = finalUrl;

                            onReceiptUpdated(updatedNotes, updatedReceipts);
                            ref.invalidate(buyerOrdersProvider);

                            if (dCtx.mounted) {
                              Navigator.pop(dCtx);
                            }
                            if (parentContext.mounted) {
                              AppNotification.showSuccess(
                                parentContext,
                                isRtl ? '✅ تم حفظ تعديلات الإشعار بنجاح!' : 'Receipt updated successfully!',
                              );
                            }
                          } catch (e) {
                            if (context.mounted) {
                              AppNotification.showError(context, 'Error: $e');
                            }
                          } finally {
                            if (dCtx.mounted) setDialogState(() => isSaving = false);
                          }
                        },
                  icon: isSaving
                      ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                      : const Icon(Icons.check, size: 18),
                  label: Text(
                    isSaving ? (isRtl ? 'جاري الحفظ...' : 'Saving...') : (isRtl ? 'حفظ التعديلات' : 'Save Changes'),
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                  ),
                ),
              ],
            );
          },
        );
      },
    );
  }

  void _showUploadReceiptDialog(BuildContext context, WidgetRef ref, dynamic order, bool isRtl, double defaultAmount) {
    final bool isExclusiveOrder = order['store_id'] == null ||
        order['notes']?.toString().contains('حصري') == true ||
        order['notes']?.toString().contains('[طلب حصري مقاييس]') == true;
    Uint8List? newReceiptBytes;
    String? newReceiptExt;
    String currentNotes = order['notes']?.toString() ?? '';
    List<Map<String, dynamic>> currentReceipts = _parseAllReceipts(currentNotes, fallbackUrl: order['receipt_url']?.toString());

    final double totalAmount = double.tryParse(order['total_amount']?.toString() ?? '0') ?? 0.0;
    double sumUploaded = currentReceipts.where((r) => r['status'] != 'rejected').fold(0.0, (s, r) => s + (r['amount'] as num).toDouble());
    double remainingBalance = (totalAmount - sumUploaded).clamp(0.0, double.infinity);

    final refController = TextEditingController();
    final amountController = TextEditingController(
      text: remainingBalance > 0
          ? remainingBalance.toStringAsFixed(0)
          : (defaultAmount > 0 ? defaultAmount.toStringAsFixed(0) : ''),
    );
    String selectedBank = 'بنكك (بنك الخرطوم)';
    bool isUploading = false;
    bool isDeleting = false;
    bool isAddingAnother = false;
    bool isFreshDataLoaded = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (ctx, setSheetState) {
            if (!isFreshDataLoaded) {
              isFreshDataLoaded = true;
              final orderId = order['id']?.toString() ?? '';
              if (orderId.isNotEmpty) {
                Future.microtask(() async {
                  try {
                    final fresh = await Supabase.instance.client
                        .from('orders')
                        .select('notes, receipt_url, paid_amount, payment_status')
                        .eq('id', orderId)
                        .maybeSingle();
                    if (fresh != null && ctx.mounted) {
                      final freshNotes = fresh['notes']?.toString() ?? '';
                      final freshReceipts = _parseAllReceipts(freshNotes, fallbackUrl: fresh['receipt_url']?.toString());
                      setSheetState(() {
                        currentNotes = freshNotes;
                        currentReceipts = freshReceipts;
                        order['notes'] = freshNotes;
                        order['paid_amount'] = fresh['paid_amount'] ?? order['paid_amount'];
                        order['payment_status'] = fresh['payment_status'] ?? order['payment_status'];
                        order['receipt_url'] = fresh['receipt_url'] ?? order['receipt_url'];
                      });
                    }
                  } catch (e) {
                    debugPrint('Error fetching fresh order receipts: $e');
                  }
                });
              }
            }

            sumUploaded = currentReceipts.where((r) => r['status'] != 'rejected').fold(0.0, (s, r) => s + (r['amount'] as num).toDouble());
            remainingBalance = (totalAmount - sumUploaded).clamp(0.0, double.infinity);
            final double progress = totalAmount > 0 ? (sumUploaded / totalAmount).clamp(0.0, 1.0) : 0.0;

            return Container(
              constraints: BoxConstraints(maxHeight: MediaQuery.of(ctx).size.height * 0.9),
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
                left: 18,
                right: 18,
                top: 18,
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Title Bar
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(6),
                              decoration: BoxDecoration(
                                color: const Color(0xFF0F766E).withOpacity(0.1),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: const Icon(Icons.receipt_long_rounded, color: Color(0xFF0F766E), size: 22),
                            ),
                            const SizedBox(width: 10),
                            Text(
                              isRtl ? 'إدارة وإرفاق إشعارات التحويل' : 'Payment Receipts Management',
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                            ),
                          ],
                        ),
                        IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(ctx)),
                      ],
                    ),
                    const Divider(height: 20),

                    // Financial Summary Card
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: Column(
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(isRtl ? 'إجمالي الطلب' : 'Order Total', style: TextStyle(fontSize: 11, color: Colors.blueGrey.shade600)),
                                  const SizedBox(height: 2),
                                  Text(
                                    '${NumberFormat('#,##0').format(totalAmount)} ${isRtl ? 'ج.س' : 'SDG'}',
                                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: Color(0xFF0F172A)),
                                  ),
                                ],
                              ),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.center,
                                children: [
                                  Text(isRtl ? 'المسدد بالإشعارات' : 'Paid in Receipts', style: TextStyle(fontSize: 11, color: Colors.blueGrey.shade600)),
                                  const SizedBox(height: 2),
                                  Text(
                                    '${NumberFormat('#,##0').format(sumUploaded)} ${isRtl ? 'ج.س' : 'SDG'}',
                                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: sumUploaded >= totalAmount ? Colors.green.shade700 : const Color(0xFF0F766E)),
                                  ),
                                ],
                              ),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: [
                                  Text(isRtl ? 'المتبقي للسداد' : 'Remaining', style: TextStyle(fontSize: 11, color: Colors.blueGrey.shade600)),
                                  const SizedBox(height: 2),
                                  Text(
                                    '${NumberFormat('#,##0').format(remainingBalance)} ${isRtl ? 'ج.س' : 'SDG'}',
                                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: remainingBalance == 0 ? Colors.green.shade700 : Colors.orange.shade800),
                                  ),
                                ],
                              ),
                            ],
                          ),
                          const SizedBox(height: 10),
                          ClipRRect(
                            borderRadius: BorderRadius.circular(6),
                            child: LinearProgressIndicator(
                              value: progress,
                              minHeight: 6,
                              backgroundColor: Colors.grey.shade200,
                              valueColor: AlwaysStoppedAnimation<Color>(remainingBalance == 0 ? Colors.green.shade600 : const Color(0xFF0F766E)),
                            ),
                          ),
                          if (remainingBalance > 0) ...[
                            const SizedBox(height: 10),
                            Container(
                              width: double.infinity,
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
                              decoration: BoxDecoration(
                                color: const Color(0xFFFFFBEB),
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(color: const Color(0xFFFDE68A)),
                              ),
                              child: Row(
                                children: [
                                  const Icon(Icons.info_outline_rounded, color: Color(0xFFD97706), size: 16),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      isRtl
                                          ? 'المبلغ المتبقي المطلوب سداده: ${NumberFormat('#,##0').format(remainingBalance)} ج.س — يرجى إرفاق إشعار التحويل لباقي المبلغ أدناه.'
                                          : 'Remaining balance to pay: ${NumberFormat('#,##0').format(remainingBalance)} SDG — please attach receipt below.',
                                      style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: Color(0xFF92400E)),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Section 1: Uploaded Receipts List
                    if (currentReceipts.isNotEmpty) ...[
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            isRtl ? '📋 الإشعارات المرفوعة مسبقاً (${currentReceipts.length})' : 'Uploaded Receipts (${currentReceipts.length})',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A)),
                          ),
                          Text(
                            isRtl ? 'انقر على الصورة للتكبير' : 'Tap image to zoom',
                            style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),

                      ...List.generate(currentReceipts.length, (idx) {
                        final r = currentReceipts[idx];
                        final rStatus = r['status']?.toString() ?? 'under_review';
                        final rUrl = r['url']?.toString() ?? '';
                        final rBank = r['bank']?.toString() ?? '';
                        final rRef = r['ref']?.toString() ?? '';
                        final rAmt = (r['amount'] as num?)?.toDouble() ?? 0.0;

                        Color statusBg;
                        Color statusFg;
                        String statusLabel;
                        if (rStatus == 'approved') {
                          statusBg = const Color(0xFFECFDF5);
                          statusFg = const Color(0xFF059669);
                          statusLabel = isRtl ? 'معتمد ✅' : 'Approved ✅';
                        } else if (rStatus == 'rejected') {
                          statusBg = const Color(0xFFFEF2F2);
                          statusFg = const Color(0xFFDC2626);
                          statusLabel = isRtl ? 'مرفوض ❌' : 'Rejected ❌';
                        } else {
                          statusBg = const Color(0xFFFFFBEB);
                          statusFg = const Color(0xFFD97706);
                          statusLabel = isRtl ? 'قيد المراجعة ⏳' : 'Under Review ⏳';
                        }

                        return Container(
                          margin: const EdgeInsets.only(bottom: 10),
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: Colors.blueGrey.shade100),
                            boxShadow: [
                              BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 4, offset: const Offset(0, 2)),
                            ],
                          ),
                          child: Row(
                            children: [
                              // Receipt thumbnail with zoom icon overlay
                              InkWell(
                                onTap: () => _showReceiptImagePreview(ctx, rUrl),
                                borderRadius: BorderRadius.circular(8),
                                child: Stack(
                                  children: [
                                    ClipRRect(
                                      borderRadius: BorderRadius.circular(8),
                                      child: Image.network(
                                        rUrl,
                                        width: 58,
                                        height: 58,
                                        fit: BoxFit.cover,
                                        errorBuilder: (c, e, s) => Container(
                                          width: 58,
                                          height: 58,
                                          color: Colors.grey.shade100,
                                          child: const Icon(Icons.broken_image, size: 24, color: Colors.grey),
                                        ),
                                      ),
                                    ),
                                    Positioned(
                                      bottom: 2,
                                      right: 2,
                                      child: Container(
                                        padding: const EdgeInsets.all(2),
                                        decoration: BoxDecoration(color: Colors.black54, borderRadius: BorderRadius.circular(4)),
                                        child: const Icon(Icons.zoom_in, color: Colors.white, size: 14),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(width: 12),
                              // Receipt Details
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          isRtl ? 'إشعار تحويل #${idx + 1}' : 'Receipt #${idx + 1}',
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F766E)),
                                        ),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                          decoration: BoxDecoration(color: statusBg, borderRadius: BorderRadius.circular(6)),
                                          child: Text(statusLabel, style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: statusFg)),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 3),
                                    Text(
                                      rBank.isNotEmpty ? rBank : (isRtl ? 'بنك غير محدد' : 'Bank Transfer'),
                                      style: TextStyle(fontSize: 11.5, color: Colors.blueGrey.shade700),
                                    ),
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          rRef.isNotEmpty ? '${isRtl ? "المرجع:" : "Ref:"} $rRef' : (isRtl ? 'بدون رقم مرجع' : 'No Ref'),
                                          style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                                        ),
                                        Text(
                                          '${NumberFormat('#,##0').format(rAmt)} ${isRtl ? 'ج.س' : 'SDG'}',
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A)),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                              // Action Buttons: Edit and Delete (only if not approved)
                              if (rStatus != 'approved') ...[
                                const SizedBox(width: 4),
                                // Edit Button
                                IconButton(
                                  icon: const Icon(Icons.edit_note_rounded, color: Color(0xFF0F766E), size: 22),
                                  tooltip: isRtl ? 'تعديل الإشعار والمبلغ' : 'Edit Receipt & Amount',
                                  onPressed: (isDeleting || isUploading || isAddingAnother)
                                      ? null
                                      : () => _showEditReceiptDialog(
                                          context: ctx,
                                          parentContext: context,
                                          ref: ref,
                                          order: order,
                                          receipt: r,
                                          isRtl: isRtl,
                                          totalAmount: totalAmount,
                                          onReceiptUpdated: (updatedNotes, updatedReceipts) {
                                            setSheetState(() {
                                              currentNotes = updatedNotes;
                                              currentReceipts = updatedReceipts;
                                              final newSum = updatedReceipts
                                                  .where((item) => item['status'] != 'rejected')
                                                  .fold(0.0, (s, item) => s + (item['amount'] as num).toDouble());
                                              final newRem = (totalAmount - newSum).clamp(0.0, double.infinity);
                                              amountController.text = newRem > 0 ? newRem.toStringAsFixed(0) : '';
                                            });
                                          },
                                        ),
                                ),
                                // Delete Button
                                IconButton(
                                  icon: const Icon(Icons.delete_outline_rounded, color: Colors.redAccent, size: 20),
                                  tooltip: isRtl ? 'حذف الإشعار' : 'Delete Receipt',
                                  onPressed: isDeleting
                                      ? null
                                      : () async {
                                          final bool? confirm = await showDialog<bool>(
                                            context: ctx,
                                            builder: (dCtx) => AlertDialog(
                                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                                              title: Row(
                                                children: [
                                                  const Icon(Icons.delete_forever_rounded, color: Colors.red),
                                                  const SizedBox(width: 8),
                                                  Text(isRtl ? 'تأكيد حذف الإشعار' : 'Delete Receipt'),
                                                ],
                                              ),
                                              content: Text(
                                                isRtl
                                                    ? 'هل أنت متأكد من رغبتك في حذف إشعار التحويل هذا من الطلب؟'
                                                    : 'Are you sure you want to delete this payment receipt?',
                                              ),
                                              actions: [
                                                TextButton(onPressed: () => Navigator.pop(dCtx, false), child: Text(isRtl ? 'إلغاء' : 'Cancel')),
                                                FilledButton(
                                                  style: FilledButton.styleFrom(backgroundColor: Colors.red),
                                                  onPressed: () => Navigator.pop(dCtx, true),
                                                  child: Text(isRtl ? 'نعم، حذف' : 'Delete'),
                                                ),
                                              ],
                                            ),
                                          );

                                          if (confirm == true) {
                                            setSheetState(() => isDeleting = true);
                                            try {
                                              final raw = r['rawMatch']?.toString() ?? '';
                                              String updatedNotes = currentNotes.replaceFirst(raw, '').replaceAll('\n\n', '\n').trim();
                                              final remainingReceipts = _parseAllReceipts(updatedNotes);
                                              final double newTotalPaid = remainingReceipts
                                                  .where((item) => item['status'] != 'rejected')
                                                  .fold(0.0, (s, item) => s + (item['amount'] as num).toDouble());

                                              String newPaymentStatus = 'unpaid';
                                              if (remainingReceipts.any((item) => item['status'] == 'under_review')) {
                                                newPaymentStatus = 'under_review';
                                              } else if (newTotalPaid >= totalAmount && totalAmount > 0) {
                                                newPaymentStatus = 'paid';
                                              } else if (newTotalPaid > 0) {
                                                newPaymentStatus = 'partial';
                                              }

                                              final orderId = order['id']?.toString() ?? '';
                                              await Supabase.instance.client.from('orders').update({
                                                'notes': updatedNotes,
                                                'paid_amount': newTotalPaid.round(),
                                                'payment_status': newPaymentStatus,
                                                'receipt_url': remainingReceipts.isNotEmpty ? remainingReceipts.last['url'] : null,
                                                if (order['store_id'] != null) 'store_id': order['store_id'],
                                              }).eq('id', orderId);

                                              order['notes'] = updatedNotes;
                                              order['paid_amount'] = newTotalPaid.round();
                                              order['payment_status'] = newPaymentStatus;

                                              setSheetState(() {
                                                currentNotes = updatedNotes;
                                                currentReceipts = remainingReceipts;
                                                final newRem = (totalAmount - newTotalPaid).clamp(0.0, double.infinity);
                                                amountController.text = newRem > 0 ? newRem.toStringAsFixed(0) : '';
                                              });

                                              ref.invalidate(buyerOrdersProvider);
                                              if (context.mounted) {
                                                AppNotification.showSuccess(context, isRtl ? 'تم حذف الإشعار بنجاح' : 'Receipt removed');
                                              }
                                            } catch (e) {
                                              if (context.mounted) {
                                                AppNotification.showError(context, 'Error: $e');
                                              }
                                            } finally {
                                              setSheetState(() => isDeleting = false);
                                            }
                                          }
                                        },
                                ),
                              ],
                            ],
                          ),
                        );
                      }),
                      const SizedBox(height: 8),
                      const Divider(),
                    ],

                    // Section 2: Attach New / Additional Receipt
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        const Icon(Icons.add_circle_outline_rounded, color: Color(0xFF0F766E), size: 20),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            remainingBalance > 0 && currentReceipts.isNotEmpty
                                ? (isRtl ? 'إرفاق إشعار تحويل لباقي المبلغ (${NumberFormat('#,##0').format(remainingBalance)} ج.س)' : 'Attach Receipt for Remaining Balance')
                                : (currentReceipts.isNotEmpty
                                    ? (isRtl ? 'إرفاق إشعار تحويل إضافي / جديد' : 'Attach Additional / New Receipt')
                                    : (isRtl ? 'إرفاق إشعار التحويل الجديد' : 'Attach New Payment Receipt')),
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14.5, color: Color(0xFF0F766E)),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    // New Image Upload Picker Box
                    InkWell(
                      onTap: () async {
                        try {
                          final picked = await ImagePickerHelper.pickImage(context, imageQuality: 70, maxWidth: 1000);
                          if (picked != null) {
                            final bytes = await picked.readAsBytes();
                            setSheetState(() {
                              newReceiptBytes = bytes;
                              newReceiptExt = picked.name.split('.').last;
                            });
                          }
                        } catch (e) {
                          debugPrint('Picker error: $e');
                        }
                      },
                      borderRadius: BorderRadius.circular(12),
                      child: Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: newReceiptBytes != null ? const Color(0xFFF0FDF4) : const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: newReceiptBytes != null ? const Color(0xFF16A34A) : const Color(0xFFCBD5E1),
                            width: newReceiptBytes != null ? 1.8 : 1.2,
                          ),
                        ),
                        child: newReceiptBytes != null
                            ? Row(
                                children: [
                                  ClipRRect(
                                    borderRadius: BorderRadius.circular(8),
                                    child: Image.memory(newReceiptBytes!, width: 70, height: 70, fit: BoxFit.cover),
                                  ),
                                  const SizedBox(width: 14),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          isRtl ? '✅ تم اختيار صورة الإشعار بنجاح' : 'Receipt Image Selected',
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF16A34A)),
                                        ),
                                        const SizedBox(height: 4),
                                        Text(
                                          isRtl ? 'انقر لتغيير الصورة أو اختر إشعاراً آخر' : 'Tap to change image',
                                          style: TextStyle(fontSize: 11, color: Colors.blueGrey.shade600),
                                        ),
                                        const SizedBox(height: 6),
                                        Row(
                                          children: [
                                            InkWell(
                                              onTap: () => setSheetState(() {
                                                newReceiptBytes = null;
                                                newReceiptExt = null;
                                              }),
                                              child: Text(
                                                isRtl ? 'إلغاء الصورة' : 'Remove',
                                                style: const TextStyle(fontSize: 11.5, color: Colors.red, fontWeight: FontWeight.bold),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),
                                  const Icon(Icons.check_circle_rounded, color: Color(0xFF16A34A), size: 24),
                                ],
                              )
                            : Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  const Icon(Icons.add_a_photo_rounded, size: 36, color: Color(0xFF0F766E)),
                                  const SizedBox(height: 8),
                                  Text(
                                    isRtl ? 'تصوير الإشعار بالكاميرا أو اختياره من المعرض' : 'Capture receipt with camera or select from gallery',
                                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F766E)),
                                    textAlign: TextAlign.center,
                                  ),
                                  const SizedBox(height: 10),
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      ElevatedButton.icon(
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: const Color(0xFF0F766E),
                                          foregroundColor: Colors.white,
                                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                        ),
                                        onPressed: () async {
                                          try {
                                            final picked = await ImagePickerHelper.pickImage(
                                              context,
                                              source: ImageSource.camera,
                                              imageQuality: 70,
                                              maxWidth: 1000,
                                            );
                                            if (picked != null) {
                                              final bytes = await picked.readAsBytes();
                                              setSheetState(() {
                                                newReceiptBytes = bytes;
                                                newReceiptExt = picked.name.split('.').last;
                                              });
                                            }
                                          } catch (e) {
                                            debugPrint('Camera picker error: $e');
                                          }
                                        },
                                        icon: const Icon(Icons.photo_camera_rounded, size: 16),
                                        label: Text(
                                          isRtl ? 'الكاميرا' : 'Camera',
                                          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                                        ),
                                      ),
                                      const SizedBox(width: 10),
                                      OutlinedButton.icon(
                                        style: OutlinedButton.styleFrom(
                                          foregroundColor: const Color(0xFF0F766E),
                                          side: const BorderSide(color: Color(0xFF0F766E)),
                                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                        ),
                                        onPressed: () async {
                                          try {
                                            final picked = await ImagePickerHelper.pickImage(
                                              context,
                                              source: ImageSource.gallery,
                                              imageQuality: 70,
                                              maxWidth: 1000,
                                            );
                                            if (picked != null) {
                                              final bytes = await picked.readAsBytes();
                                              setSheetState(() {
                                                newReceiptBytes = bytes;
                                                newReceiptExt = picked.name.split('.').last;
                                              });
                                            }
                                          } catch (e) {
                                            debugPrint('Gallery picker error: $e');
                                          }
                                        },
                                        icon: const Icon(Icons.photo_library_rounded, size: 16),
                                        label: Text(
                                          isRtl ? 'المعرض' : 'Gallery',
                                          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                      ),
                    ),

                    const SizedBox(height: 14),

                    // Bank Selector
                    DropdownButtonFormField<String>(
                      decoration: InputDecoration(
                        labelText: isRtl ? 'البنك أو وسيلة الدفع' : 'Bank / Method',
                        border: const OutlineInputBorder(),
                        prefixIcon: const Icon(Icons.account_balance),
                      ),
                      initialValue: selectedBank,
                      items: [
                        'بنكك (بنك الخرطوم)',
                        'أوكاش (بنك أمدرمان الوطني)',
                        'تحويل بنكي مباشر',
                        'إيداع نقدي',
                      ].map((b) => DropdownMenuItem(value: b, child: Text(b))).toList(),
                      onChanged: (v) => setSheetState(() => selectedBank = v ?? selectedBank),
                    ),

                    const SizedBox(height: 12),

                    // Reference Number
                    TextFormField(
                      controller: refController,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'رقم الإشعار / المعاملة (المرجع)' : 'Transaction Reference #',
                        hintText: isRtl ? 'مثلاً: 12847920' : 'e.g. 12847920',
                        border: const OutlineInputBorder(),
                        prefixIcon: const Icon(Icons.tag_rounded),
                      ),
                    ),

                    const SizedBox(height: 12),

                    // Amount
                    TextFormField(
                      controller: amountController,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true),
                      decoration: InputDecoration(
                        labelText: isRtl ? 'المبلغ المحول في هذا الإشعار (ج.س)' : 'Amount in this receipt (SDG)',
                        helperText: remainingBalance > 0
                            ? (isRtl ? 'المتبقي المطلوب: ${NumberFormat('#,##0').format(remainingBalance)} ج.س' : 'Remaining: ${NumberFormat('#,##0').format(remainingBalance)} SDG')
                            : null,
                        border: const OutlineInputBorder(),
                        prefixIcon: const Icon(Icons.money),
                      ),
                    ),

                    const SizedBox(height: 18),

                    // Action Buttons: إضافة إشعار آخر + إرسال الإشعار للتاجر
                    Row(
                      children: [
                        // Button 1: إضافة إشعار آخر
                        Expanded(
                          child: OutlinedButton.icon(
                            style: OutlinedButton.styleFrom(
                              foregroundColor: const Color(0xFF0F766E),
                              side: const BorderSide(color: Color(0xFF0F766E), width: 1.5),
                              padding: const EdgeInsets.symmetric(vertical: 13),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                            ),
                            onPressed: (isUploading || isAddingAnother)
                                ? null
                                : () async {
                                    if (newReceiptBytes == null || newReceiptExt == null) {
                                      AppNotification.showError(
                                        context,
                                        isRtl ? 'يرجى اختيار صورة الإشعار الذي تود إضافته أولاً' : 'Please select a receipt screenshot first',
                                      );
                                      return;
                                    }

                                    final paidAmt = double.tryParse(amountController.text) ?? 0.0;
                                    if (paidAmt <= 0) {
                                      AppNotification.showError(
                                        context,
                                        isRtl ? 'يرجى كتابة مبلغ تحويل صحيح أكبر من الصفر' : 'Please enter a valid transfer amount',
                                      );
                                      return;
                                    }

                                    setSheetState(() => isAddingAnother = true);
                                    try {
                                      final orderId = order['id']?.toString() ?? '';
                                      final fName = 'receipt_${orderId}_${DateTime.now().millisecondsSinceEpoch}.$newReceiptExt';
                                      await Supabase.instance.client.storage
                                          .from('products_images')
                                          .uploadBinary(fName, newReceiptBytes!);
                                      final uploadedUrl = Supabase.instance.client.storage
                                          .from('products_images')
                                          .getPublicUrl(fName);

                                      final refNum = refController.text.trim();
                                      final newReceiptLine = 'receipt_url:$uploadedUrl | ref:$refNum | bank:$selectedBank | amount:$paidAmt';

                                      // Clean old rejection reason if any since new receipt is being attached
                                      String baseNotes = currentNotes;
                                      if (baseNotes.contains('rejection_reason:')) {
                                        baseNotes = baseNotes.split('rejection_reason:')[0].trim();
                                      }

                                      final updatedNotes = '$baseNotes\n$newReceiptLine'.trim();
                                      final allUpdatedReceipts = _parseAllReceipts(updatedNotes);
                                      final double totalPaid = allUpdatedReceipts
                                          .where((item) => item['status'] != 'rejected')
                                          .fold(0.0, (s, item) => s + (item['amount'] as num).toDouble());

                                      await Supabase.instance.client.from('orders').update({
                                        'notes': updatedNotes,
                                        'payment_status': 'under_review',
                                        'receipt_url': uploadedUrl,
                                        'paid_amount': totalPaid.round(),
                                        if (order['store_id'] != null) 'store_id': order['store_id'],
                                      }).eq('id', orderId);

                                      order['notes'] = updatedNotes;
                                      order['paid_amount'] = totalPaid.round();
                                      order['payment_status'] = 'under_review';
                                      order['receipt_url'] = uploadedUrl;

                                      final double newRem = (totalAmount - totalPaid).clamp(0.0, double.infinity);

                                      setSheetState(() {
                                        currentNotes = updatedNotes;
                                        currentReceipts = allUpdatedReceipts;
                                        newReceiptBytes = null;
                                        newReceiptExt = null;
                                        refController.clear();
                                        amountController.text = newRem > 0 ? newRem.toStringAsFixed(0) : '';
                                      });

                                      ref.invalidate(buyerOrdersProvider);
                                      if (context.mounted) {
                                        AppNotification.showSuccess(
                                          context,
                                          isRtl
                                              ? '✅ تمت إضافة الإشعار بنجاح! يمكنك الآن اختيار إشعار آخر، أو الضغط على "إرسال الإشعار للتاجر".'
                                              : 'Receipt added! You can add another receipt or send to merchant.',
                                        );
                                      }
                                    } catch (e) {
                                      if (context.mounted) {
                                        AppNotification.showError(
                                          context,
                                          'Error: $e',
                                        );
                                      }
                                    } finally {
                                      setSheetState(() => isAddingAnother = false);
                                    }
                                  },
                            icon: isAddingAnother
                                ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF0F766E)))
                                : const Icon(Icons.add_photo_alternate_outlined, size: 18),
                            label: Text(
                              isAddingAnother
                                  ? (isRtl ? 'جاري الإضافة...' : 'Adding...')
                                  : (isRtl ? 'إضافة إشعار آخر' : 'Add Another'),
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        // Button 2: إرسال الإشعار للتاجر
                        Expanded(
                          child: FilledButton.icon(
                            style: FilledButton.styleFrom(
                              backgroundColor: const Color(0xFF0F766E),
                              padding: const EdgeInsets.symmetric(vertical: 13),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                            ),
                            onPressed: (isUploading || isAddingAnother)
                                ? null
                                : () async {
                                    // If user selected an image in the picker right now
                                    if (newReceiptBytes != null && newReceiptExt != null) {
                                      final paidAmt = double.tryParse(amountController.text) ?? 0.0;
                                      if (paidAmt <= 0) {
                                        AppNotification.showError(
                                          context,
                                          isRtl ? 'يرجى كتابة مبلغ تحويل صحيح أكبر من الصفر' : 'Please enter a valid transfer amount',
                                        );
                                        return;
                                      }

                                      setSheetState(() => isUploading = true);
                                      try {
                                        final orderId = order['id']?.toString() ?? '';
                                        final fName = 'receipt_${orderId}_${DateTime.now().millisecondsSinceEpoch}.$newReceiptExt';
                                        await Supabase.instance.client.storage
                                            .from('products_images')
                                            .uploadBinary(fName, newReceiptBytes!);
                                        final uploadedUrl = Supabase.instance.client.storage
                                            .from('products_images')
                                            .getPublicUrl(fName);

                                        final refNum = refController.text.trim();
                                        final newReceiptLine = 'receipt_url:$uploadedUrl | ref:$refNum | bank:$selectedBank | amount:$paidAmt';

                                        String baseNotes = currentNotes;
                                        if (baseNotes.contains('rejection_reason:')) {
                                          baseNotes = baseNotes.split('rejection_reason:')[0].trim();
                                        }

                                        final updatedNotes = '$baseNotes\n$newReceiptLine'.trim();
                                        final allUpdatedReceipts = _parseAllReceipts(updatedNotes);
                                        final double totalPaid = allUpdatedReceipts
                                            .where((item) => item['status'] != 'rejected')
                                            .fold(0.0, (s, item) => s + (item['amount'] as num).toDouble());

                                        await Supabase.instance.client.from('orders').update({
                                          'notes': updatedNotes,
                                          'payment_status': 'under_review',
                                          'receipt_url': uploadedUrl,
                                          'paid_amount': totalPaid.round(),
                                          if (order['store_id'] != null) 'store_id': order['store_id'],
                                        }).eq('id', orderId);

                                        order['notes'] = updatedNotes;
                                        order['paid_amount'] = totalPaid.round();
                                        order['payment_status'] = 'under_review';
                                        order['receipt_url'] = uploadedUrl;

                                        if (context.mounted) {
                                          ref.invalidate(buyerOrdersProvider);
                                          Navigator.pop(ctx);
                                          AppNotification.showSuccess(
                                            context,
                                            isRtl
                                                ? (isExclusiveOrder
                                                    ? '✅ تم إرسال الإشعار لإدارة مقاييس بنجاح! سيتم مراجعته وتأكيده فوراً.'
                                                    : '✅ تم إرسال الإشعار للتاجر بنجاح! سيتم مراجعته وتأكيده من قبل التاجر.')
                                                : 'Receipt submitted successfully! Will review and verify.',
                                          );
                                        }
                                      } catch (e) {
                                        if (context.mounted) {
                                          AppNotification.showError(
                                            context,
                                            'Error: $e',
                                          );
                                        }
                                      } finally {
                                        setSheetState(() => isUploading = false);
                                      }
                                    } else if (currentReceipts.isNotEmpty) {
                                      if (remainingBalance > 0) {
                                        AppNotification.showError(
                                          context,
                                          isRtl
                                              ? 'يرجى اختيار صورة إشعار التحويل لسداد المبلغ المتبقي (${NumberFormat('#,##0').format(remainingBalance)} ج.س) أولاً.'
                                              : 'Please select a receipt screenshot for the remaining balance first.',
                                        );
                                        return;
                                      }
                                      // User already uploaded receipts and no balance remains, finalize submit
                                      setSheetState(() => isUploading = true);
                                      try {
                                        final orderId = order['id']?.toString() ?? '';
                                        await Supabase.instance.client.from('orders').update({
                                          'payment_status': 'under_review',
                                          if (order['store_id'] != null) 'store_id': order['store_id'],
                                        }).eq('id', orderId);

                                        order['payment_status'] = 'under_review';

                                        if (context.mounted) {
                                          ref.invalidate(buyerOrdersProvider);
                                          Navigator.pop(ctx);
                                          AppNotification.showSuccess(
                                            context,
                                            isRtl
                                                ? (isExclusiveOrder
                                                    ? '✅ تم إرسال كافة الإشعارات (${currentReceipts.length}) لإدارة مقاييس بنجاح!'
                                                    : '✅ تم إرسال كافة الإشعارات (${currentReceipts.length}) للتاجر بنجاح!')
                                                : 'All receipts submitted successfully!',
                                          );
                                        }
                                      } catch (e) {
                                        if (context.mounted) {
                                          AppNotification.showError(context, 'Error: $e');
                                        }
                                      } finally {
                                        setSheetState(() => isUploading = false);
                                      }
                                    } else {
                                      AppNotification.showError(
                                        context,
                                        isRtl ? 'يرجى اختيار صورة إشعار التحويل أولاً' : 'Please select a receipt screenshot first',
                                      );
                                    }
                                  },
                            icon: isUploading
                                ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                                : const Icon(Icons.send_rounded, size: 18),
                            label: Text(
                              isUploading
                                  ? (isRtl ? 'جاري الإرسال...' : 'Sending...')
                                  : (newReceiptBytes == null && currentReceipts.isNotEmpty && remainingBalance == 0
                                      ? (isRtl ? 'إغلاق (تم السداد بالكامل)' : 'Close (Fully Paid)')
                                      : (isExclusiveOrder
                                          ? (isRtl ? 'إرسال الإشعار لإدارة مقاييس' : 'Send to Magaiees')
                                          : (isRtl ? 'إرسال الإشعار للتاجر' : 'Send to Merchant'))),
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  void _showReviewDialog(BuildContext context, dynamic item, bool isRtl) {
    int selectedRating = 5;
    final commentController = TextEditingController();
    bool isSubmitting = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (ctx, setState) {
            return Padding(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(ctx).viewInsets.bottom,
                left: 24, right: 24, top: 24,
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(isRtl ? 'تقييم المنتج' : 'Rate Product',
                    style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  Text(item['product_name'] ?? '', style: TextStyle(color: Colors.grey.shade600)),
                  const SizedBox(height: 24),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: List.generate(5, (index) {
                      return IconButton(
                        iconSize: 40,
                        icon: Icon(
                          index < selectedRating ? Icons.star : Icons.star_border,
                          color: Colors.amber,
                        ),
                        onPressed: () {
                          setState(() => selectedRating = index + 1);
                        },
                      );
                    }),
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: commentController,
                    maxLines: 3,
                    decoration: InputDecoration(
                      hintText: isRtl ? 'اكتب تعليقك (اختياري)...' : 'Write a comment (optional)...',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                  const SizedBox(height: 24),
                  SizedBox(
                    width: double.infinity,
                    height: 50,
                    child: FilledButton(
                      onPressed: isSubmitting ? null : () async {
                        final comment = commentController.text.trim();
                        if (comment.isNotEmpty && DataValidator.isSpamOrFake(comment)) {
                          AppNotification.showError(
                            context,
                            isRtl ? 'يرجى كتابة تعليق حقيقي ومفيد' : 'Please write a genuine review comment',
                          );
                          return;
                        }
                        setState(() => isSubmitting = true);
                        try {
                          final supabase = Supabase.instance.client;
                          await supabase.from('product_reviews').insert({
                            'product_id': item['product_id'],
                            'buyer_id': supabase.auth.currentUser!.id,
                            'rating': selectedRating,
                            'comment': comment,
                          });
                          if (ctx.mounted) {
                            Navigator.pop(ctx);
                            AppNotification.showSuccess(
                              context,
                              isRtl ? 'شكرًا لتقييمك!' : 'Thank you for your review!',
                            );
                          }
                        } catch (e) {
                          setState(() => isSubmitting = false);
                          if (ctx.mounted) {
                            AppNotification.showError(
                              context,
                              isRtl ? 'لقد قمت بتقييم هذا المنتج مسبقاً.' : 'You have already reviewed this product.',
                            );
                          }
                        }
                      },
                      child: isSubmitting 
                        ? const CircularProgressIndicator(color: Colors.white)
                        : Text(isRtl ? 'إرسال التقييم' : 'Submit Review'),
                    ),
                  ),
                  const SizedBox(height: 24),
                ],
              ),
            );
          }
        );
      }
    );
  }
}

// Order Timeline Widget
class _OrderTimeline extends StatelessWidget {
  final String status;
  final bool isRtl;
  const _OrderTimeline({required this.status, required this.isRtl});

  @override
  Widget build(BuildContext context) {
    final steps = [
      {'key': 'pending', 'ar': 'تم الطلب', 'en': 'Ordered', 'icon': Icons.receipt_long},
      {'key': 'processing', 'ar': 'قيد التجهيز', 'en': 'Processing', 'icon': Icons.settings},
      {'key': 'shipped', 'ar': 'تم الشحن', 'en': 'Shipped', 'icon': Icons.local_shipping},
      {'key': 'delivered', 'ar': 'تم التسليم', 'en': 'Delivered', 'icon': Icons.check_circle},
    ];

    if (status == 'cancelled') {
      return Container(
        padding: const EdgeInsets.symmetric(vertical: 8),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.cancel, color: Colors.red.shade700, size: 20),
            const SizedBox(width: 6),
            Text(isRtl ? 'تم إلغاء هذا الطلب' : 'This order has been cancelled',
                style: TextStyle(color: Colors.red.shade700, fontWeight: FontWeight.bold)),
          ],
        ),
      );
    }

    final statusOrder = ['pending', 'processing', 'shipped', 'delivered'];
    final currentIndex = statusOrder.indexOf(status);

    return Padding(
      padding: const EdgeInsets.only(top: 8),
      child: Row(
        children: List.generate(steps.length * 2 - 1, (i) {
          if (i.isOdd) {
            // Connector line
            final stepIndex = i ~/ 2;
            final isCompleted = stepIndex < currentIndex;
            return Expanded(
              child: Container(
                height: 3,
                color: isCompleted ? Colors.green.shade400 : Colors.grey.shade300,
              ),
            );
          }

          final stepIndex = i ~/ 2;
          final step = steps[stepIndex];
          final isCompleted = stepIndex <= currentIndex;
          final isCurrent = stepIndex == currentIndex;

          return Column(
            children: [
              Container(
                width: isCurrent ? 32 : 24,
                height: isCurrent ? 32 : 24,
                decoration: BoxDecoration(
                  color: isCompleted ? Colors.green.shade400 : Colors.grey.shade300,
                  shape: BoxShape.circle,
                  border: isCurrent ? Border.all(color: Colors.green.shade700, width: 2) : null,
                ),
                child: Icon(
                  step['icon'] as IconData,
                  size: isCurrent ? 16 : 12,
                  color: Colors.white,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                isRtl ? step['ar'] as String : step['en'] as String,
                style: TextStyle(
                  fontSize: 9,
                  fontWeight: isCurrent ? FontWeight.bold : FontWeight.normal,
                  color: isCompleted ? Colors.green.shade700 : Colors.grey.shade500,
                ),
              ),
            ],
          );
        }),
      ),
    );
  }
}

class _BuyerChatWithMerchantButton extends ConsumerStatefulWidget {
  final Map<String, dynamic> order;
  final bool isRtl;

  const _BuyerChatWithMerchantButton({
    required this.order,
    required this.isRtl,
  });

  @override
  ConsumerState<_BuyerChatWithMerchantButton> createState() => _BuyerChatWithMerchantButtonState();
}

class _BuyerChatWithMerchantButtonState extends ConsumerState<_BuyerChatWithMerchantButton> {
  bool _isLoading = false;

  Future<void> _handleChat() async {
    if (_isLoading) return;

    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user == null || user.role == UserRole.guest || user.id == 'guest_user') {
      if (mounted) {
        showDialog(
          context: context,
          builder: (ctx) => AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            title: Row(
              children: [
                const Icon(Icons.lock_outline_rounded, color: Color(0xFF1E3A8A)),
                const SizedBox(width: 8),
                Text(widget.isRtl ? 'تسجيل الدخول مطلوب' : 'Login Required'),
              ],
            ),
            content: Text(
              widget.isRtl
                  ? 'يرجى تسجيل الدخول أو إنشاء حساب لبدء المحادثة مع التاجر.'
                  : 'Please log in or create an account to start chatting with the seller.',
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: Text(widget.isRtl ? 'إلغاء' : 'Cancel'),
              ),
              FilledButton(
                onPressed: () {
                  Navigator.pop(ctx);
                  context.push('/login');
                },
                child: Text(widget.isRtl ? 'تسجيل الدخول' : 'Log In'),
              ),
            ],
          ),
        );
      }
      return;
    }

    setState(() => _isLoading = true);

    try {
      final storeData = widget.order['stores'] as Map<String, dynamic>?;
      String? merchantId = storeData?['owner_id']?.toString();
      final storeId = widget.order['store_id']?.toString();

      if (merchantId == null && storeId != null && storeId.isNotEmpty) {
        try {
          final sRes = await Supabase.instance.client
              .from('stores')
              .select('owner_id')
              .eq('id', storeId)
              .limit(1)
              .maybeSingle()
              .timeout(const Duration(seconds: 5));
          merchantId = sRes?['owner_id']?.toString();
        } catch (_) {}
      }

      // If still null (e.g. Magaiees Exclusive order): check assigned employee or fallback to admin
      if (merchantId == null) {
        try {
          final assignRes = await Supabase.instance.client
              .from('employee_order_assignments')
              .select('employee_id')
              .eq('order_id', widget.order['id'])
              .limit(1)
              .maybeSingle()
              .timeout(const Duration(seconds: 4));
          merchantId = assignRes?['employee_id']?.toString();
        } catch (_) {}

        if (merchantId == null) {
          try {
            final adminRes = await Supabase.instance.client
                .from('users')
                .select('id')
                .eq('role', 'admin')
                .limit(1)
                .maybeSingle()
                .timeout(const Duration(seconds: 4));
            merchantId = adminRes?['id']?.toString();
          } catch (_) {}
        }
      }

      if (merchantId == null) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('لا يمكن التواصل مع هذا التاجر حالياً')),
          );
        }
        return;
      }

      if (merchantId == user.id) {
        if (mounted) {
          AppNotification.showInfo(
            context,
            widget.isRtl
                ? 'أنت صاحب هذا المتجر، لا يمكنك بدء محادثة مع نفسك'
                : 'You are the store owner, cannot chat with yourself',
          );
        }
        return;
      }

      final roomId = await ChatService.getOrCreateRoom(
        buyerId: user.id,
        merchantId: merchantId,
        storeId: storeId,
      ).timeout(const Duration(seconds: 10));

      if (mounted) {
        await context.push('/buyer/chat/room/$roomId');
      }
    } catch (e) {
      debugPrint('Error opening chat with merchant: $e');
      if (mounted) {
        AppNotification.showError(
          context,
          widget.isRtl
              ? 'تعذر فتح المحادثة، يرجى المحاولة مرة أخرى'
              : 'Could not open chat, please try again',
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final bool isExclusive = widget.order['store_id'] == null ||
        widget.order['notes']?.toString().contains('حصري') == true ||
        widget.order['notes']?.toString().contains('[طلب حصري مقاييس]') == true;
    return OutlinedButton.icon(
      onPressed: _isLoading ? null : _handleChat,
      icon: _isLoading
          ? const SizedBox(
              width: 14,
              height: 14,
              child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF0F766E)),
            )
          : const Icon(Icons.chat_bubble_outline_rounded, size: 16),
      label: Text(
        _isLoading
            ? (widget.isRtl ? 'جاري الفتح...' : 'Opening...')
            : (widget.isRtl
                ? (isExclusive ? 'تواصل مع مقاييس' : 'تواصل مع التاجر')
                : (isExclusive ? 'Chat Magaiees' : 'Chat Seller')),
        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
        overflow: TextOverflow.ellipsis,
      ),
      style: OutlinedButton.styleFrom(
        foregroundColor: const Color(0xFF0F766E),
        side: const BorderSide(color: Color(0xFF5EEAD4)),
        backgroundColor: const Color(0xFFF0FDFA),
        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 7),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      ),
    );
  }
}

class _BuyerCallWithMerchantButton extends ConsumerStatefulWidget {
  final Map<String, dynamic> order;
  final bool isRtl;

  const _BuyerCallWithMerchantButton({
    required this.order,
    required this.isRtl,
  });

  @override
  ConsumerState<_BuyerCallWithMerchantButton> createState() => _BuyerCallWithMerchantButtonState();
}

class _BuyerCallWithMerchantButtonState extends ConsumerState<_BuyerCallWithMerchantButton> {
  bool _isLoading = false;

  Future<void> _handleCall() async {
    if (_isLoading) return;

    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user == null || user.role == UserRole.guest || user.id == 'guest_user') {
      if (mounted) {
        showDialog(
          context: context,
          builder: (ctx) => AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            icon: const Icon(Icons.phone_in_talk_rounded, size: 48, color: Color(0xFF1E40AF)),
            title: Text(widget.isRtl ? 'تسجيل الدخول مطلوب' : 'Login Required', style: const TextStyle(fontWeight: FontWeight.bold)),
            content: Text(
              widget.isRtl
                  ? 'يرجى تسجيل الدخول أو إنشاء حساب لبدء اتصال مباشر مع التاجر.'
                  : 'Please log in or create an account to start calling the seller.',
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: Text(widget.isRtl ? 'إلغاء' : 'Cancel'),
              ),
              FilledButton(
                style: FilledButton.styleFrom(backgroundColor: const Color(0xFF1E40AF)),
                onPressed: () {
                  Navigator.pop(ctx);
                  ref.read(authNotifierProvider.notifier).logout();
                  context.go('/login');
                },
                child: Text(widget.isRtl ? 'تسجيل الدخول' : 'Log In'),
              ),
            ],
          ),
        );
      }
      return;
    }

    setState(() => _isLoading = true);

    try {
      final storeId = widget.order['store_id']?.toString();
      String? merchantId;
      String? displayStoreName;

      if (storeId != null && storeId.isNotEmpty) {
        try {
          final storeRes = await Supabase.instance.client
              .from('stores')
              .select('owner_id, name, name_ar')
              .eq('id', storeId)
              .maybeSingle()
              .timeout(const Duration(seconds: 4));
          merchantId = storeRes?['owner_id']?.toString();
          displayStoreName = NameFormatter.formatName(
            name: storeRes?['name']?.toString(),
            nameAr: storeRes?['name_ar']?.toString(),
            isRtl: widget.isRtl,
          );
        } catch (_) {}
      }

      if (displayStoreName == null || displayStoreName.isEmpty) {
        displayStoreName = NameFormatter.formatName(
          name: widget.order['stores']?['name']?.toString() ?? widget.order['store_name']?.toString(),
          nameAr: widget.order['stores']?['name_ar']?.toString(),
          isRtl: widget.isRtl,
        );
      }

      if (merchantId == null) {
        try {
          final assignRes = await Supabase.instance.client
              .from('employee_order_assignments')
              .select('employee_id')
              .eq('order_id', widget.order['id'])
              .limit(1)
              .maybeSingle()
              .timeout(const Duration(seconds: 4));
          merchantId = assignRes?['employee_id']?.toString();
        } catch (_) {}

        if (merchantId == null) {
          try {
            final adminRes = await Supabase.instance.client
                .from('users')
                .select('id')
                .eq('role', 'admin')
                .limit(1)
                .maybeSingle()
                .timeout(const Duration(seconds: 4));
            merchantId = adminRes?['id']?.toString();
          } catch (_) {}
        }
      }

      if (merchantId == null) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(widget.isRtl ? 'لا يمكن الاتصال بهذا التاجر حالياً' : 'Cannot call seller right now')),
          );
        }
        return;
      }

      if (merchantId == user.id) {
        if (mounted) {
          AppNotification.showInfo(
            context,
            widget.isRtl ? 'لا يمكنك الاتصال بنفسك' : 'Cannot call yourself',
          );
        }
        return;
      }

      final roomId = await ChatService.getOrCreateRoom(
        buyerId: user.id,
        merchantId: merchantId,
        storeId: storeId,
      ).timeout(const Duration(seconds: 10));

      final callerName = displayStoreName.isNotEmpty
          ? displayStoreName
          : (widget.isRtl ? 'المتجر' : 'Store');

      if (mounted) {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => CallScreen(
              callID: roomId,
              currentUser: user,
              callerName: callerName,
              targetUserId: merchantId,
            ),
          ),
        );
      }
    } catch (e) {
      debugPrint('Error starting call with merchant: $e');
      if (mounted) {
        AppNotification.showError(
          context,
          widget.isRtl
              ? 'تعذر بدء المكالمة، يرجى التأكد من الاتصال بالإنترنت'
              : 'Could not start call, please check connection',
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return OutlinedButton.icon(
      onPressed: _isLoading ? null : _handleCall,
      icon: _isLoading
          ? const SizedBox(
              width: 14,
              height: 14,
              child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF2563EB)),
            )
          : const Icon(Icons.phone_in_talk_rounded, size: 16),
      label: Text(
        _isLoading
            ? (widget.isRtl ? 'جاري الاتصال...' : 'Calling...')
            : (widget.isRtl ? 'اتصال مباشر' : 'In-App Call'),
        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
        overflow: TextOverflow.ellipsis,
      ),
      style: OutlinedButton.styleFrom(
        foregroundColor: const Color(0xFF2563EB),
        side: const BorderSide(color: Color(0xFF93C5FD)),
        backgroundColor: const Color(0xFFEFF6FF),
        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 7),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      ),
    );
  }
}



