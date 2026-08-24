import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:intl/intl.dart' hide TextDirection;

// Store ID provider
final _merchantStoreIdProvider = FutureProvider<String?>((ref) async {
  final user = ref.watch(authNotifierProvider).valueOrNull;
  if (user == null) return null;
  final storeRes = await Supabase.instance.client
      .from('stores').select('id').eq('owner_id', user.id).maybeSingle();
  return storeRes?['id']?.toString();
});

// Real-time orders provider using Stream
final merchantOrdersProvider = StreamProvider.autoDispose<List<Map<String, dynamic>>>((ref) {
  final storeIdAsync = ref.watch(_merchantStoreIdProvider);
  final storeId = storeIdAsync.valueOrNull;

  if (storeId == null) return Stream.value([]);

  // Create a stream controller
  final controller = StreamController<List<Map<String, dynamic>>>();

  // Function to fetch all orders with items and payments
  Future<void> fetchOrders() async {
    try {
      final ordersRes = await Supabase.instance.client
          .from('orders')
          .select('*, order_items(*)')
          .eq('store_id', storeId)
          .order('created_at', ascending: false);

      final orders = List<Map<String, dynamic>>.from(ordersRes);
      for (int i = 0; i < orders.length; i++) {
        try {
          final payments = await Supabase.instance.client
              .from('payments').select('*')
              .eq('order_id', orders[i]['id'].toString())
              .order('created_at', ascending: true);
          orders[i] = {...orders[i], 'payments': payments};
        } catch (_) {
          orders[i] = {...orders[i], 'payments': []};
        }
      }
      if (!controller.isClosed) controller.add(orders);
    } catch (e) {
      if (!controller.isClosed) controller.addError(e);
    }
  }

  // Initial fetch
  fetchOrders();

  // Subscribe to realtime changes on orders table
  final channel = Supabase.instance.client
      .channel('merchant_orders_$storeId')
      .onPostgresChanges(
        event: PostgresChangeEvent.all,
        schema: 'public',
        table: 'orders',
        filter: PostgresChangeFilter(type: PostgresChangeFilterType.eq, column: 'store_id', value: storeId),
        callback: (payload) => fetchOrders(),
      )
      .onPostgresChanges(
        event: PostgresChangeEvent.all,
        schema: 'public',
        table: 'payments',
        callback: (payload) => fetchOrders(),
      )
      .subscribe();

  ref.onDispose(() {
    channel.unsubscribe();
    controller.close();
  });

  return controller.stream;
});

// Filter state providers
final _statusFilterProvider = StateProvider.autoDispose<String?>((ref) => null);
final _paymentFilterProvider = StateProvider.autoDispose<String?>((ref) => null);

class MerchantOrdersScreen extends ConsumerWidget {
  const MerchantOrdersScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final ordersAsync = ref.watch(merchantOrdersProvider);
    final statusFilter = ref.watch(_statusFilterProvider);
    final paymentFilter = ref.watch(_paymentFilterProvider);

    int count = 0;
    if (ordersAsync.hasValue && ordersAsync.value != null) {
      final filteredOrders = ordersAsync.value!.where((o) {
        final matchStatus = statusFilter == null || o['status'] == statusFilter;
        bool matchPayment = true;
        if (paymentFilter == 'paid') {
          matchPayment = o['payment_status'] == 'paid';
        } else if (paymentFilter == 'debt') {
          final total = (num.tryParse(o['total_amount']?.toString() ?? '0') ?? 0.0).toDouble();
          final paid = (num.tryParse(o['paid_amount']?.toString() ?? '0') ?? 0.0).toDouble();
          matchPayment = (total - paid) > 0;
        }
        return matchStatus && matchPayment;
      }).toList();
      count = filteredOrders.length;
    }

    return Scaffold(
      appBar: AppBar(
        title: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(isRtl ? 'الطلبات' : 'Orders'),
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(color: Colors.blue.shade100, borderRadius: BorderRadius.circular(12)),
              child: Text(
                count.toString(), 
                style: TextStyle(color: Colors.blue.shade900, fontSize: 14, fontWeight: FontWeight.bold)
              ),
            ),
          ]
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(merchantOrdersProvider),
          ),
        ],
      ),
      body: Column(
        children: [
          // Smart Filters Header
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: BoxDecoration(
              color: Colors.white,
              boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 4, offset: const Offset(0, 2))],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Icon(Icons.filter_list, size: 18, color: Colors.blue),
                    const SizedBox(width: 8),
                    Text(isRtl ? 'حالة الطلب:' : 'Order Status:', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                  ],
                ),
                const SizedBox(height: 8),
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      _buildFilterChip(context, ref, _statusFilterProvider, null, isRtl ? 'الكل' : 'All'),
                      _buildFilterChip(context, ref, _statusFilterProvider, 'pending', isRtl ? 'قيد الانتظار' : 'Pending', color: Colors.orange),
                      _buildFilterChip(context, ref, _statusFilterProvider, 'processing', isRtl ? 'قيد المعالجة' : 'Processing', color: Colors.blue),
                      _buildFilterChip(context, ref, _statusFilterProvider, 'shipped', isRtl ? 'تم الشحن' : 'Shipped', color: Colors.indigo),
                      _buildFilterChip(context, ref, _statusFilterProvider, 'delivered', isRtl ? 'تم التسليم' : 'Delivered', color: Colors.green),
                      _buildFilterChip(context, ref, _statusFilterProvider, 'cancelled', isRtl ? 'ملغي' : 'Cancelled', color: Colors.red),
                    ],
                  ),
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    const Icon(Icons.payments_outlined, size: 18, color: Colors.blue),
                    const SizedBox(width: 8),
                    Text(isRtl ? 'حالة الدفع:' : 'Payment Status:', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                  ],
                ),
                const SizedBox(height: 8),
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      _buildFilterChip(context, ref, _paymentFilterProvider, null, isRtl ? 'الكل' : 'All'),
                      _buildFilterChip(context, ref, _paymentFilterProvider, 'paid', isRtl ? 'مدفوع بالكامل' : 'Fully Paid', color: Colors.green),
                      _buildFilterChip(context, ref, _paymentFilterProvider, 'debt', isRtl ? 'عليه متبقي' : 'Has Debt', color: Colors.red),
                    ],
                  ),
                ),
              ],
            ),
          ),
          
          // Orders List
          Expanded(
            child: ordersAsync.when(
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (err, stack) => Center(child: Text('Error: $err')),
              data: (orders) {
                // Apply filters
                final filteredOrders = orders.where((o) {
                  final matchStatus = statusFilter == null || o['status'] == statusFilter;
                  bool matchPayment = true;
                  if (paymentFilter == 'paid') {
                    matchPayment = o['payment_status'] == 'paid';
                  } else if (paymentFilter == 'debt') {
                    final total = double.tryParse(o['total_amount']?.toString() ?? '0') ?? 0;
                    final paid = double.tryParse(o['paid_amount']?.toString() ?? '0') ?? 0;
                    matchPayment = (total - paid) > 0;
                  }
                  return matchStatus && matchPayment;
                }).toList();

                if (filteredOrders.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.receipt_long_outlined, size: 64, color: Colors.grey.shade400),
                        const SizedBox(height: 16),
                        Text(isRtl ? 'لا توجد طلبات تطابق الفلتر' : 'No orders match filters',
                            style: TextStyle(fontSize: 16, color: Colors.grey.shade600)),
                      ],
                    ),
                  );
                }
                return ListView.builder(
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  itemCount: filteredOrders.length,
                  itemBuilder: (context, index) =>
                      _OrderCard(order: filteredOrders[index], ref: ref, isRtl: isRtl),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(BuildContext context, WidgetRef ref, AutoDisposeStateProvider<String?> provider, String? value, String label, {MaterialColor? color}) {
    final selectedValue = ref.watch(provider);
    final isSelected = selectedValue == value;
    final themeColor = color ?? Colors.grey;

    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: FilterChip(
        label: Text(label, style: TextStyle(fontSize: 12, color: isSelected ? Colors.white : Colors.black87)),
        selected: isSelected,
        showCheckmark: false,
        backgroundColor: Colors.grey.shade100,
        selectedColor: themeColor.shade600,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(20),
          side: BorderSide(color: isSelected ? themeColor.shade600 : Colors.grey.shade300),
        ),
        onSelected: (bool selected) {
          ref.read(provider.notifier).state = value;
        },
      ),
    );
  }
}

class _OrderCard extends StatelessWidget {
  final Map<String, dynamic> order;
  final WidgetRef ref;
  final bool isRtl;
  const _OrderCard({required this.order, required this.ref, required this.isRtl});

  @override
  Widget build(BuildContext context) {
    final idStr = order['id']?.toString() ?? '';
    final orderId = idStr.length >= 8 ? idStr.substring(0, 8) : idStr;
    final date = order['created_at'] != null
        ? DateFormat('yyyy-MM-dd').format(DateTime.parse(order['created_at']))
        : '';
    final totalAmount = double.tryParse(order['total_amount']?.toString() ?? '0') ?? 0;
    final paidAmount = double.tryParse(order['paid_amount']?.toString() ?? '0') ?? 0;
    final transportFee = double.tryParse(order['transport_fee']?.toString() ?? '0') ?? 0;
    final remaining = totalAmount - paidAmount;
    final status = order['status'] ?? 'pending';
    final paymentStatus = order['payment_status'] ?? 'unpaid';
    final items = order['order_items'] as List<dynamic>? ?? [];
    final payments = order['payments'] as List<dynamic>? ?? [];

    return Card(
      elevation: 2,
      shadowColor: Colors.black12,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(color: _statusColor(status).withOpacity(0.2), width: 1.5),
      ),
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Column(
        children: [
          // Header
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: _statusColor(status).withOpacity(0.05),
              borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('${isRtl ? 'طلب' : 'Order'} #$orderId',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.blue.shade900)),
                _buildStatusBadge(status),
              ],
            ),
          ),

          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Date
                Text('${isRtl ? 'التاريخ' : 'Date'}: $date',
                    style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                const SizedBox(height: 12),

                // Financial Summary
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade50,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.grey.shade200),
                  ),
                  child: Column(
                    children: [
                      _finRow(isRtl ? 'إجمالي الطلب' : 'Total', totalAmount, bold: true),
                      if (transportFee > 0) ...[
                        const SizedBox(height: 4),
                        _finRow(isRtl ? 'رسوم الترحيل' : 'Transport', transportFee, color: Colors.orange.shade700),
                      ],
                      const Divider(height: 16),
                      _finRow(isRtl ? 'المدفوع' : 'Paid', paidAmount, color: Colors.green.shade700),
                      const SizedBox(height: 4),
                      _finRow(isRtl ? 'المتبقي' : 'Remaining', remaining,
                          color: remaining > 0 ? Colors.red.shade700 : Colors.green.shade700, bold: true),
                      const SizedBox(height: 8),
                      _buildPaymentBadge(paymentStatus),
                    ],
                  ),
                ),

                // Items
                const SizedBox(height: 12),
                Text(isRtl ? 'المنتجات:' : 'Items:', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                const SizedBox(height: 6),
                if (items.isEmpty)
                  Text(isRtl ? 'لا توجد تفاصيل' : 'No details', style: TextStyle(color: Colors.grey.shade500, fontSize: 12))
                else
                  ...items.map((item) => Padding(
                        padding: const EdgeInsets.only(bottom: 3),
                        child: Row(
                          children: [
                            Icon(Icons.inventory_2_outlined, size: 14, color: Colors.grey.shade500),
                            const SizedBox(width: 6),
                            Expanded(child: Text('${item['product_name'] ?? ''} x${item['quantity']}',
                                style: TextStyle(fontSize: 13, color: Colors.grey.shade800))),
                            Text('${item['price_at_time']} ${isRtl ? 'ج.س' : 'SDG'}',
                                style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                          ],
                        ),
                      )),

                // Payment History
                if (payments.isNotEmpty) ...[
                  const SizedBox(height: 12),
                  Text(isRtl ? 'سجل المدفوعات:' : 'Payments:', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                  const SizedBox(height: 6),
                  ...payments.map((p) {
                    final pDate = p['created_at'] != null
                        ? DateFormat('MM-dd HH:mm').format(DateTime.parse(p['created_at']).toLocal())
                        : '';
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 3),
                      child: Row(
                        children: [
                          Icon(Icons.payments_outlined, size: 14, color: Colors.green.shade600),
                          const SizedBox(width: 6),
                          Expanded(child: Text('${p['amount']} ${isRtl ? 'ج.س' : 'SDG'} - ${p['method'] ?? ''}',
                              style: TextStyle(fontSize: 12, color: Colors.green.shade700))),
                          Text(pDate, style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
                        ],
                      ),
                    );
                  }),
                ],

                // Actions
                const SizedBox(height: 16),
                _buildActions(context, status, remaining, totalAmount, paidAmount),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusBadge(String status) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: _statusColor(status).withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: _statusColor(status).withOpacity(0.3)),
      ),
      child: Text(_statusText(status), style: TextStyle(color: _statusColor(status), fontSize: 12, fontWeight: FontWeight.bold)),
    );
  }

  Widget _buildPaymentBadge(String ps) {
    Color c; String t; IconData ic;
    switch (ps) {
      case 'paid': c = Colors.green.shade700; t = isRtl ? 'مدفوع بالكامل ✓' : 'Fully Paid ✓'; ic = Icons.check_circle; break;
      case 'partial': c = Colors.orange.shade700; t = isRtl ? 'مدفوع جزئياً' : 'Partially Paid'; ic = Icons.timelapse; break;
      default: c = Colors.red.shade700; t = isRtl ? 'لم يتم الدفع' : 'Unpaid'; ic = Icons.cancel;
    }
    return Row(mainAxisAlignment: MainAxisAlignment.center, children: [
      Icon(ic, size: 16, color: c), const SizedBox(width: 4),
      Text(t, style: TextStyle(color: c, fontSize: 13, fontWeight: FontWeight.bold)),
    ]);
  }

  Widget _finRow(String label, double value, {Color? color, bool bold = false}) {
    return Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
      Text(label, style: TextStyle(fontSize: 13, color: color ?? Colors.grey.shade700, fontWeight: bold ? FontWeight.bold : FontWeight.normal)),
      Text('${NumberFormat('#,##0').format(value)} ${isRtl ? 'ج.س' : 'SDG'}',
          style: TextStyle(fontSize: 14, color: color ?? Colors.grey.shade900, fontWeight: bold ? FontWeight.bold : FontWeight.normal)),
    ]);
  }

  Widget _buildActions(BuildContext context, String status, double remaining, double total, double paid) {
    if (status == 'cancelled') return const SizedBox.shrink();

    return Column(children: [
      // Payment button
      if (remaining > 0)
        SizedBox(
          width: double.infinity,
          child: OutlinedButton.icon(
            onPressed: () => _showPaymentDialog(context, remaining, total, paid),
            icon: const Icon(Icons.payments_outlined),
            label: Text(isRtl ? 'تأكيد استلام دفعة' : 'Record Payment'),
            style: OutlinedButton.styleFrom(
              foregroundColor: Colors.green.shade700,
              side: BorderSide(color: Colors.green.shade300),
            ),
          ),
        ),
      if (remaining > 0 && status != 'delivered') const SizedBox(height: 8),

      // Status buttons
      if (status != 'delivered')
      Row(children: [
        if (status == 'pending') ...[
          Expanded(
            child: TextButton(
              onPressed: () => _confirmAction(context, 'cancelled', remaining,
                  isRtl ? 'تم رفض الطلب' : 'Order declined'),
              child: Text(isRtl ? 'رفض' : 'Decline', style: TextStyle(color: Colors.red.shade700)),
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            flex: 2,
            child: FilledButton.icon(
              style: FilledButton.styleFrom(backgroundColor: Colors.green.shade600),
              onPressed: () => _confirmAction(context, 'processing', remaining,
                  isRtl ? 'تم قبول الطلب!' : 'Order accepted!'),
              icon: const Icon(Icons.check, size: 18),
              label: Text(isRtl ? 'قبول الطلب' : 'Accept'),
            ),
          ),
        ],
        if (status == 'processing')
          Expanded(
            child: FilledButton.icon(
              style: FilledButton.styleFrom(backgroundColor: Colors.indigo.shade600),
              onPressed: () => _confirmAction(context, 'shipped', remaining,
                  isRtl ? 'تم تأكيد الشحن!' : 'Marked as shipped!'),
              icon: const Icon(Icons.local_shipping, size: 18),
              label: Text(isRtl ? 'تم الشحن' : 'Mark Shipped'),
            ),
          ),
        if (status == 'shipped')
          Expanded(
            child: FilledButton.icon(
              style: FilledButton.styleFrom(backgroundColor: Colors.green.shade700),
              onPressed: () => _confirmAction(context, 'delivered', remaining,
                  isRtl ? 'تم تأكيد التسليم!' : 'Delivered!'),
              icon: const Icon(Icons.check_circle, size: 18),
              label: Text(isRtl ? 'تأكيد التسليم' : 'Confirm Delivery'),
            ),
          ),
      ]),
    ]);
  }

  void _confirmAction(BuildContext context, String newStatus, double remaining, String successMsg) {
    // Build warning message based on payment status
    String? warningMsg;
    if (remaining > 0) {
      final formatted = NumberFormat('#,##0').format(remaining);
      if (newStatus == 'processing') {
        warningMsg = isRtl
            ? '⚠️ تنبيه: المشتري لم يسدد المبلغ بعد!\nالمتبقي: $formatted ج.س\n\nهل تريد قبول الطلب رغم ذلك؟'
            : '⚠️ Warning: Buyer has NOT paid yet!\nRemaining: $formatted SDG\n\nAccept anyway?';
      } else if (newStatus == 'shipped') {
        warningMsg = isRtl
            ? '⚠️ تحذير: المشتري لم يسدد كامل المبلغ!\nالمتبقي: $formatted ج.س\n\nهل تريد شحن الطلب رغم وجود مبلغ متبقي؟'
            : '⚠️ Warning: Buyer has outstanding balance!\nRemaining: $formatted SDG\n\nShip anyway?';
      } else if (newStatus == 'delivered') {
        warningMsg = isRtl
            ? '🚨 تحذير مهم: المشتري لديه مبلغ متبقي!\nالمتبقي: $formatted ج.س\n\nتأكيد التسليم يعني أن البضاعة وصلت.\nهل أنت متأكد من تأكيد التسليم؟'
            : '🚨 Important: Buyer has unpaid balance!\nRemaining: $formatted SDG\n\nConfirming delivery means goods arrived.\nAre you sure?';
      } else if (newStatus == 'cancelled') {
        warningMsg = isRtl
            ? 'هل أنت متأكد من رفض هذا الطلب؟'
            : 'Are you sure you want to decline this order?';
      }
    } else if (newStatus == 'cancelled') {
      warningMsg = isRtl
          ? 'هل أنت متأكد من رفض هذا الطلب؟'
          : 'Are you sure you want to decline this order?';
    }

    if (warningMsg != null) {
      showDialog(
        context: context,
        builder: (dialogContext) => AlertDialog(
          title: Icon(
            remaining > 0 ? Icons.warning_amber_rounded : Icons.help_outline,
            size: 48,
            color: remaining > 0 ? Colors.orange.shade700 : Colors.blue.shade700,
          ),
          content: Text(warningMsg!, textAlign: TextAlign.center, style: const TextStyle(fontSize: 15)),
          actionsAlignment: MainAxisAlignment.center,
          actions: [
            TextButton(
              onPressed: () => Navigator.of(dialogContext).pop(),
              child: Text(isRtl ? 'إلغاء' : 'Cancel'),
            ),
            FilledButton(
              style: FilledButton.styleFrom(
                backgroundColor: newStatus == 'cancelled' ? Colors.red.shade600 : Colors.green.shade600,
              ),
              onPressed: () {
                Navigator.of(dialogContext).pop();
                _doUpdateStatus(context, newStatus, successMsg);
              },
              child: Text(isRtl ? 'نعم، متأكد' : 'Yes, Proceed'),
            ),
          ],
        ),
      );
    } else {
      _doUpdateStatus(context, newStatus, successMsg);
    }
  }

  Future<void> _doUpdateStatus(BuildContext context, String newStatus, String successMsg) async {
    try {
      await Supabase.instance.client
          .from('orders')
          .update({'status': newStatus})
          .eq('id', order['id'].toString());
      ref.invalidate(merchantOrdersProvider);
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(successMsg), backgroundColor: Colors.green),
        );
      }
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
        );
      }
    }
  }

  void _showPaymentDialog(BuildContext context, double remaining, double total, double paid) {
    final amountController = TextEditingController();
    String selectedMethod = 'cash';

    showDialog(
      context: context,
      builder: (dialogContext) => StatefulBuilder(
        builder: (ctx, setDialogState) => AlertDialog(
          title: Text(isRtl ? 'تأكيد استلام دفعة' : 'Confirm Payment', textAlign: TextAlign.center),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(isRtl ? 'المتبقي: ${NumberFormat('#,##0').format(remaining)} ج.س' : 'Remaining: ${NumberFormat('#,##0').format(remaining)} SDG',
                  style: TextStyle(color: Colors.red.shade700, fontWeight: FontWeight.bold, fontSize: 16)),
              const SizedBox(height: 16),
              TextField(
                controller: amountController,
                keyboardType: TextInputType.number,
                decoration: InputDecoration(
                  labelText: isRtl ? 'المبلغ المستلم' : 'Amount',
                  suffixText: isRtl ? 'ج.س' : 'SDG',
                  border: const OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                value: selectedMethod,
                decoration: InputDecoration(
                  labelText: isRtl ? 'طريقة الدفع' : 'Method',
                  border: const OutlineInputBorder(),
                ),
                items: [
                  DropdownMenuItem(value: 'cash', child: Text(isRtl ? 'نقداً' : 'Cash')),
                  DropdownMenuItem(value: 'ocash', child: Text(isRtl ? 'أوكاش' : 'O-Cash')),
                  DropdownMenuItem(value: 'bankak', child: Text(isRtl ? 'بنكك' : 'Bankak')),
                  DropdownMenuItem(value: 'bank_transfer', child: Text(isRtl ? 'تحويل بنكي' : 'Bank Transfer')),
                ],
                onChanged: (v) => setDialogState(() => selectedMethod = v ?? 'cash'),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(dialogContext).pop(),
              child: Text(isRtl ? 'إلغاء' : 'Cancel'),
            ),
            FilledButton(
              onPressed: () async {
                final amount = double.tryParse(amountController.text) ?? 0;
                if (amount <= 0) return;

                try {
                  await Supabase.instance.client.from('payments').insert({
                    'order_id': order['id'].toString(),
                    'amount': amount,
                    'method': selectedMethod,
                  });

                  final newPaid = paid + amount;
                  final newPaymentStatus = newPaid >= total ? 'paid' : 'partial';

                  await Supabase.instance.client.from('orders').update({
                    'paid_amount': newPaid,
                    'payment_status': newPaymentStatus,
                  }).eq('id', order['id'].toString());

                  ref.invalidate(merchantOrdersProvider);
                  if (dialogContext.mounted) Navigator.of(dialogContext).pop();
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text(isRtl ? 'تم تسجيل الدفعة!' : 'Payment recorded!'), backgroundColor: Colors.green),
                    );
                  }
                } catch (e) {
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
                    );
                  }
                }
              },
              child: Text(isRtl ? 'تأكيد' : 'Confirm'),
            ),
          ],
        ),
      ),
    );
  }

  Color _statusColor(String s) {
    switch (s) {
      case 'pending': return Colors.orange.shade700;
      case 'processing': return Colors.blue.shade700;
      case 'shipped': return Colors.indigo.shade700;
      case 'delivered': return Colors.green.shade700;
      case 'cancelled': return Colors.red.shade700;
      default: return Colors.grey.shade700;
    }
  }

  String _statusText(String s) {
    switch (s) {
      case 'pending': return isRtl ? 'قيد الانتظار' : 'Pending';
      case 'processing': return isRtl ? 'مقبول - قيد المعالجة' : 'Accepted';
      case 'shipped': return isRtl ? 'تم الشحن' : 'Shipped';
      case 'delivered': return isRtl ? 'تم التسليم' : 'Delivered';
      case 'cancelled': return isRtl ? 'ملغي' : 'Cancelled';
      default: return s;
    }
  }
}
