import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:intl/intl.dart' hide TextDirection;

final buyerOrdersProvider = FutureProvider.autoDispose<List<Map<String, dynamic>>>((ref) async {
  final user = ref.watch(authNotifierProvider).valueOrNull;
  if (user == null) return [];

  final ordersRes = await Supabase.instance.client
      .from('orders')
      .select('*, order_items(*)')
      .eq('buyer_id', user.id)
      .order('created_at', ascending: false);

  return List<Map<String, dynamic>>.from(ordersRes);
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
      body: ordersAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, stack) => Center(child: Text('Error: $err')),
        data: (orders) {
          if (orders.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.shopping_bag_outlined, size: 64, color: Colors.grey.shade400),
                  const SizedBox(height: 16),
                  Text(isRtl ? 'لم تقم بأي طلبات بعد' : 'You have no orders yet',
                      style: TextStyle(fontSize: 16, color: Colors.grey.shade600)),
                ],
              ),
            );
          }

          return ListView.builder(
            padding: const EdgeInsets.symmetric(vertical: 8),
            itemCount: orders.length,
            itemBuilder: (context, index) {
              final order = orders[index];
              return _BuyerOrderCard(order: order, isRtl: isRtl);
            },
          );
        },
      ),
    );
  }
}

class _BuyerOrderCard extends StatelessWidget {
  final Map<String, dynamic> order;
  final bool isRtl;
  const _BuyerOrderCard({required this.order, required this.isRtl});

  @override
  Widget build(BuildContext context) {
    final idStr = order['id']?.toString() ?? '';
    final orderId = idStr.length >= 8 ? idStr.substring(0, 8) : idStr;
    final date = order['created_at'] != null
        ? DateFormat('yyyy-MM-dd HH:mm').format(DateTime.parse(order['created_at']).toLocal())
        : '-';
    final totalAmount = double.tryParse(order['total_amount']?.toString() ?? '0') ?? 0;
    final paidAmount = double.tryParse(order['paid_amount']?.toString() ?? '0') ?? 0;
    final transportFee = double.tryParse(order['transport_fee']?.toString() ?? '0') ?? 0;
    final remaining = totalAmount - paidAmount;
    final status = order['status'] ?? 'pending';
    final paymentStatus = order['payment_status'] ?? 'unpaid';
    final paymentMethod = order['payment_method'] ?? '';
    final items = order['order_items'] as List<dynamic>? ?? [];

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
                  children: [
                    Text('${isRtl ? 'طلب' : 'Order'} #$orderId',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.blue.shade900)),
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
                    Text('$date', style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
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
                        ...items.map((item) => Padding(
                              padding: const EdgeInsets.only(bottom: 4),
                              child: Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      '${item['product_name'] ?? isRtl ? 'منتج' : 'Product'} × ${item['quantity']}',
                                      style: const TextStyle(fontSize: 13),
                                    ),
                                  ),
                                  Text('${item['price_at_time'] ?? 0} ${isRtl ? 'ج.س' : 'SDG'}',
                                      style: TextStyle(fontSize: 13, color: Colors.grey.shade700)),
                                ],
                              ),
                            )),
                      if (transportFee > 0) ...[
                        const Divider(height: 12),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(isRtl ? 'رسوم الترحيل' : 'Transport Fee',
                                style: TextStyle(fontSize: 13, color: Colors.orange.shade700)),
                            Text('${NumberFormat('#,##0').format(transportFee)} ${isRtl ? 'ج.س' : 'SDG'}',
                                style: TextStyle(fontSize: 13, color: Colors.orange.shade700)),
                          ],
                        ),
                      ],
                      const Divider(height: 16),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(isRtl ? 'الإجمالي' : 'Total',
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                          Text('${NumberFormat('#,##0').format(totalAmount)} ${isRtl ? 'ج.س' : 'SDG'}',
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(isRtl ? 'المدفوع' : 'Paid',
                              style: TextStyle(fontSize: 13, color: Colors.green.shade700)),
                          Text('${NumberFormat('#,##0').format(paidAmount)} ${isRtl ? 'ج.س' : 'SDG'}',
                              style: TextStyle(fontSize: 13, color: Colors.green.shade700)),
                        ],
                      ),
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
                      // Payment status
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.symmetric(vertical: 6),
                        decoration: BoxDecoration(
                          color: _getPaymentColor(paymentStatus).withOpacity(0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          _getPaymentText(paymentStatus),
                          textAlign: TextAlign.center,
                          style: TextStyle(
                            color: _getPaymentColor(paymentStatus),
                            fontWeight: FontWeight.bold,
                            fontSize: 13,
                          ),
                        ),
                      ),
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
    switch (paymentStatus) {
      case 'paid': return Colors.green.shade700;
      case 'partial': return Colors.orange.shade700;
      default: return Colors.red.shade700;
    }
  }

  String _getPaymentText(String paymentStatus) {
    switch (paymentStatus) {
      case 'paid': return isRtl ? '✅ دفع بالكامل' : '✅ Fully Paid';
      case 'partial': return isRtl ? '⚠️ دفع جزئي' : '⚠️ Partially Paid';
      default: return isRtl ? '❌ لم يتم الدفع بعد' : '❌ Unpaid';
    }
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
                        setState(() => isSubmitting = true);
                        try {
                          final supabase = Supabase.instance.client;
                          await supabase.from('product_reviews').insert({
                            'product_id': item['product_id'],
                            'buyer_id': supabase.auth.currentUser!.id,
                            'rating': selectedRating,
                            'comment': commentController.text.trim(),
                          });
                          if (ctx.mounted) {
                            Navigator.pop(ctx);
                            ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                              content: Text(isRtl ? 'شكرًا لتقييمك!' : 'Thank you for your review!'),
                              backgroundColor: Colors.green,
                            ));
                          }
                        } catch (e) {
                          setState(() => isSubmitting = false);
                          if (ctx.mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                              content: Text(isRtl ? 'لقد قمت بتقييم هذا المنتج مسبقاً.' : 'You have already reviewed this product.'),
                              backgroundColor: Colors.red,
                            ));
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
