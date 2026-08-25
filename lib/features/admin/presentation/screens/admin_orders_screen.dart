import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/features/admin/orders/application/orders_provider.dart';

class AdminOrdersScreen extends ConsumerStatefulWidget {
  const AdminOrdersScreen({super.key});

  @override
  ConsumerState<AdminOrdersScreen> createState() => _AdminOrdersScreenState();
}

class _AdminOrdersScreenState extends ConsumerState<AdminOrdersScreen> {
  String _selectedFilter = 'all';
  String _searchQuery = '';
  final TextEditingController _searchCtrl = TextEditingController();

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return Colors.orange;
      case 'shipped':
        return Colors.blue;
      case 'completed':
      case 'delivered':
        return Colors.green;
      case 'cancelled':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  String _getStatusArabic(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return 'قيد المراجعة';
      case 'shipped':
        return 'تم الشحن';
      case 'completed':
      case 'delivered':
        return 'مكتمل';
      case 'cancelled':
        return 'ملغي';
      default:
        return status;
    }
  }

  void _showOrderDetailsModal(OrderModel order, bool isRtl) {
    final currencyFmt = NumberFormat.decimalPattern('ar');
    final dateFormat = DateFormat('yyyy-MM-dd HH:mm');

    showDialog(
      context: context,
      builder: (ctx) => Directionality(
        textDirection: isRtl ? TextDirection.rtl : TextDirection.ltr,
        child: AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: Row(
            children: [
              const Icon(Icons.receipt_long, color: Color(0xFF1E40AF)),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  '${isRtl ? "تفاصيل الطلب" : "Order Details"} #${order.id.length > 8 ? order.id.substring(0, 8) : order.id}',
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: _getStatusColor(order.status).withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  _getStatusArabic(order.status),
                  style: TextStyle(
                    color: _getStatusColor(order.status),
                    fontWeight: FontWeight.bold,
                    fontSize: 12,
                  ),
                ),
              ),
            ],
          ),
          content: SizedBox(
            width: 500,
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.grey.shade50,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.grey.shade200),
                    ),
                    child: Column(
                      children: [
                        _infoRow(Icons.tag, isRtl ? 'معرف الطلب' : 'Order ID', '#${order.id.length > 8 ? order.id.substring(0, 8) : order.id}'),
                        _infoRow(Icons.person, isRtl ? 'العميل' : 'Customer', order.buyerName ?? (isRtl ? 'غير معروف' : 'Unknown')),
                        if (order.buyerPhone != null && order.buyerPhone!.isNotEmpty)
                          _infoRow(Icons.phone, isRtl ? 'الهاتف' : 'Phone', order.buyerPhone!),
                        if (order.buyerEmail != null && order.buyerEmail!.isNotEmpty)
                          _infoRow(Icons.email, isRtl ? 'البريد' : 'Email', order.buyerEmail!),
                        _infoRow(
                            Icons.location_on,
                            isRtl ? 'عنوان التوصيل' : 'Address',
                            order.shippingAddress ?? (isRtl ? 'غير متوفر' : 'N/A')),
                        _infoRow(
                            Icons.calendar_today,
                            isRtl ? 'تاريخ الطلب' : 'Date',
                            dateFormat.format(order.createdAt)),
                      ],
                    ),
                  ),
                  // Order Items section
                  if (order.items.isNotEmpty) ...[
                    const SizedBox(height: 16),
                    Text(
                      isRtl ? 'عناصر الطلب' : 'Order Items',
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: const Color(0xFF0F172A)),
                    ),
                    const SizedBox(height: 8),
                    Container(
                      decoration: BoxDecoration(
                        border: Border.all(color: Colors.grey.shade200),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Column(
                        children: order.items.asMap().entries.map((entry) {
                          final item = entry.value;
                          final isLast = entry.key == order.items.length - 1;
                          return Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                            decoration: BoxDecoration(
                              border: isLast ? null : Border(bottom: BorderSide(color: Colors.grey.shade100)),
                            ),
                            child: Row(
                              children: [
                                Expanded(
                                  flex: 3,
                                  child: Text(item.productName, style: const TextStyle(fontWeight: FontWeight.w500)),
                                ),
                                Expanded(
                                  child: Text('×${item.quantity}', textAlign: TextAlign.center, style: TextStyle(color: Colors.grey.shade600)),
                                ),
                                Expanded(
                                  flex: 2,
                                  child: Directionality(
                                    textDirection: TextDirection.ltr,
                                    child: Text(
                                      '${item.priceSdg.toStringAsFixed(0)} SDG',
                                      textAlign: TextAlign.end,
                                      style: const TextStyle(fontWeight: FontWeight.w600),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          );
                        }).toList(),
                      ),
                    ),
                  ],
                  const SizedBox(height: 20),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                    decoration: BoxDecoration(
                      color: const Color(0xFF1E40AF).withValues(alpha: 0.08),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          isRtl ? 'إجمالي قيمة الطلب:' : 'Total Amount:',
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                        ),
                        Text(
                          '${currencyFmt.format(order.totalAmount)} ${isRtl ? "ج.س" : "SDG"}',
                          style: const TextStyle(
                            fontWeight: FontWeight.w900,
                            fontSize: 18,
                            color: Color(0xFF1E40AF),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 24),
                  Text(
                    isRtl ? 'تحديث حالة الطلب:' : 'Update Order Status:',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                  ),
                  const SizedBox(height: 12),
                  Wrap(
                    spacing: 12,
                    runSpacing: 12,
                    children: [
                      _statusUpdateButton(ctx, order, 'pending', 'قيد المراجعة', Colors.orange),
                      _statusUpdateButton(ctx, order, 'shipped', 'تم الشحن', Colors.blue),
                      _statusUpdateButton(ctx, order, 'completed', 'مكتمل', Colors.green),
                      _statusUpdateButton(ctx, order, 'cancelled', 'ملغي', Colors.red),
                    ],
                  ),
                ],
              ),
            ),
          ),
          actions: [
            FilledButton(
              style: FilledButton.styleFrom(
                backgroundColor: const Color(0xFF0F172A),
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
              ),
              onPressed: () => Navigator.pop(ctx),
              child: Text(isRtl ? 'إغلاق' : 'Close'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _infoRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 18, color: Colors.grey.shade600),
          const SizedBox(width: 10),
          SizedBox(
            width: 120,
            child: Text('$label:', style: TextStyle(color: Colors.grey.shade700, fontSize: 13, fontWeight: FontWeight.w500)),
          ),
          Expanded(
            child: Text(value, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: Color(0xFF0F172A))),
          ),
        ],
      ),
    );
  }

  Widget _statusUpdateButton(BuildContext ctx, OrderModel order, String statusValue, String statusLabel, Color color) {
    final isCurrent = order.status.toLowerCase() == statusValue;
    return InkWell(
      onTap: isCurrent
          ? null
          : () async {
              try {
                Navigator.pop(ctx);
                await ref.read(adminOrdersProvider.notifier).updateOrderStatus(order.id, statusValue);
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text('تم تحديث حالة الطلب إلى $statusLabel'),
                      backgroundColor: Colors.green,
                    ),
                  );
                }
              } catch (e) {
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text('حدث خطأ أثناء التحديث: $e'),
                      backgroundColor: Colors.red,
                    ),
                  );
                }
              }
            },
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        decoration: BoxDecoration(
          color: isCurrent ? color.withValues(alpha: 0.15) : Colors.transparent,
          border: Border.all(color: isCurrent ? color : Colors.grey.shade300),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (isCurrent) ...[
              Icon(Icons.check_circle, size: 16, color: color),
              const SizedBox(width: 6),
            ],
            Text(
              statusLabel,
              style: TextStyle(
                color: isCurrent ? color : Colors.grey.shade800,
                fontWeight: isCurrent ? FontWeight.bold : FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFilterChip(String id, String label) {
    final isSelected = _selectedFilter == id;
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      selectedColor: const Color(0xFF1E40AF).withValues(alpha: 0.15),
      labelStyle: TextStyle(
        color: isSelected ? const Color(0xFF1E40AF) : Colors.grey.shade700,
        fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
      ),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(20),
        side: BorderSide(
          color: isSelected ? const Color(0xFF1E40AF).withValues(alpha: 0.5) : Colors.grey.shade300,
        ),
      ),
      onSelected: (selected) {
        if (selected) {
          setState(() {
            _selectedFilter = id;
          });
        }
      },
    );
  }

  Widget _statPill(IconData icon, String label, String value, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.06),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: color.withValues(alpha: 0.15)),
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, color: color, size: 20),
            ),
            const SizedBox(width: 12),
            Text(label, style: TextStyle(color: Colors.grey.shade800, fontSize: 14, fontWeight: FontWeight.w600)),
            const Spacer(),
            Text(value, style: TextStyle(fontWeight: FontWeight.w900, color: color, fontSize: 18)),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final currencyFmt = NumberFormat.decimalPattern('ar');
    final dateFormat = DateFormat('yyyy-MM-dd');
    final ordersAsync = ref.watch(adminOrdersProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: ordersAsync.when(
        loading: () => const Center(child: CircularProgressIndicator(color: Color(0xFF1E40AF))),
        error: (err, st) => Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.error_outline, color: Colors.red, size: 56),
              const SizedBox(height: 16),
              Text('حدث خطأ: $err', style: const TextStyle(color: Colors.red, fontSize: 16)),
              const SizedBox(height: 24),
              FilledButton.icon(
                style: FilledButton.styleFrom(backgroundColor: const Color(0xFF0F172A)),
                onPressed: () => ref.read(adminOrdersProvider.notifier).loadOrders(),
                icon: const Icon(Icons.refresh),
                label: const Text('إعادة المحاولة'),
              )
            ],
          ),
        ),
        data: (orders) {
          final pendingCount = orders.where((o) => o.status.toLowerCase() == 'pending').length;
          final completedCount = orders.where((o) => o.status.toLowerCase() == 'completed' || o.status.toLowerCase() == 'delivered').length;
          final cancelledCount = orders.where((o) => o.status.toLowerCase() == 'cancelled').length;

          final filtered = orders.where((o) {
            if (_selectedFilter != 'all') {
              if (_selectedFilter == 'completed') {
                if (o.status.toLowerCase() != 'completed' && o.status.toLowerCase() != 'delivered') return false;
              } else if (o.status.toLowerCase() != _selectedFilter) {
                return false;
              }
            }
            if (_searchQuery.isNotEmpty) {
              final q = _searchQuery.toLowerCase();
              if (!o.id.toLowerCase().contains(q)) {
                return false;
              }
            }
            return true;
          }).toList();

          return Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          isRtl ? 'إدارة الطلبات' : 'Orders Management',
                          style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                                fontWeight: FontWeight.bold,
                                color: const Color(0xFF0F172A),
                              ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          isRtl ? 'متابعة تفاصيل الطلبات، الشحن، وتحديث الحالات' : 'Track order details, shipping & status updates',
                          style: TextStyle(color: Colors.grey.shade600, fontSize: 14),
                        ),
                      ],
                    ),
                    FilledButton.icon(
                      style: FilledButton.styleFrom(
                        backgroundColor: const Color(0xFF1E40AF),
                        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      onPressed: () {
                        ref.read(adminOrdersProvider.notifier).loadOrders();
                      },
                      icon: const Icon(Icons.refresh, size: 20),
                      label: Text(
                        isRtl ? 'تحديث البيانات' : 'Refresh Data',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 28),

                // Stats Bar
                Row(
                  children: [
                    _statPill(Icons.all_inbox, isRtl ? 'الكل' : 'All', '${orders.length}', const Color(0xFF0F172A)),
                    const SizedBox(width: 16),
                    _statPill(Icons.pending_actions, isRtl ? 'قيد المراجعة' : 'Pending', '$pendingCount', Colors.orange),
                    const SizedBox(width: 16),
                    _statPill(Icons.check_circle, isRtl ? 'مكتمل' : 'Completed', '$completedCount', Colors.green),
                    const SizedBox(width: 16),
                    _statPill(Icons.cancel, isRtl ? 'ملغي' : 'Cancelled', '$cancelledCount', Colors.red),
                  ],
                ),
                const SizedBox(height: 24),

                // Search & Filter Row
                Row(
                  children: [
                    Expanded(
                      flex: 2,
                      child: TextField(
                        controller: _searchCtrl,
                        onChanged: (v) => setState(() => _searchQuery = v.trim()),
                        decoration: InputDecoration(
                          hintText: isRtl ? 'ابحث برقم الطلب...' : 'Search by order #...',
                          prefixIcon: const Icon(Icons.search, color: Color(0xFF1E40AF)),
                          suffixIcon: _searchQuery.isNotEmpty
                              ? IconButton(
                                  icon: const Icon(Icons.clear, color: Colors.grey),
                                  onPressed: () {
                                    _searchCtrl.clear();
                                    setState(() => _searchQuery = '');
                                  },
                                )
                              : null,
                          filled: true,
                          fillColor: Colors.white,
                          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade300)),
                          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade300)),
                          focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFF1E40AF), width: 2)),
                        ),
                      ),
                    ),
                    const SizedBox(width: 20),
                    Expanded(
                      flex: 3,
                      child: SingleChildScrollView(
                        scrollDirection: Axis.horizontal,
                        child: Row(
                          children: [
                            _buildFilterChip('all', isRtl ? 'الكل' : 'All'),
                            const SizedBox(width: 12),
                            _buildFilterChip('pending', isRtl ? 'قيد المراجعة' : 'Pending'),
                            const SizedBox(width: 12),
                            _buildFilterChip('shipped', isRtl ? 'تم الشحن' : 'Shipped'),
                            const SizedBox(width: 12),
                            _buildFilterChip('completed', isRtl ? 'مكتمل' : 'Completed'),
                            const SizedBox(width: 12),
                            _buildFilterChip('cancelled', isRtl ? 'ملغي' : 'Cancelled'),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 20),

                // Orders Table
                Expanded(
                  child: Container(
                    width: double.infinity,
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: Colors.grey.shade200),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.02),
                          blurRadius: 15,
                          offset: const Offset(0, 5),
                        ),
                      ],
                    ),
                    child: filtered.isEmpty
                        ? Center(
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.inbox, size: 64, color: Colors.grey.shade300),
                                const SizedBox(height: 16),
                                Text(
                                  isRtl ? 'لا توجد طلبات مطابقة للبحث' : 'No matching orders found',
                                  style: TextStyle(color: Colors.grey.shade500, fontSize: 16),
                                ),
                              ],
                            ),
                          )
                        : ClipRRect(
                            borderRadius: BorderRadius.circular(16),
                            child: Scrollbar(
                              child: SingleChildScrollView(
                                scrollDirection: Axis.horizontal,
                                child: ConstrainedBox(
                                  constraints: BoxConstraints(minWidth: MediaQuery.of(context).size.width - 80),
                                  child: DataTable(
                                    headingRowColor: WidgetStatePropertyAll(const Color(0xFFF8FAFC)),
                                    headingTextStyle: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0F172A), fontSize: 14),
                                    columnSpacing: 32,
                                    horizontalMargin: 24,
                                    dataRowMinHeight: 70,
                                    dataRowMaxHeight: 80,
                                    columns: [
                                      DataColumn(label: Text(isRtl ? 'رقم الطلب' : 'Order #')),
                                      DataColumn(label: Text(isRtl ? 'تاريخ الطلب' : 'Date')),
                                      DataColumn(label: Text(isRtl ? 'المبلغ الإجمالي' : 'Total Amount')),
                                      DataColumn(label: Text(isRtl ? 'الحالة' : 'Status')),
                                      DataColumn(label: Text(isRtl ? 'الإجراءات' : 'Actions')),
                                    ],
                                    rows: filtered.map((order) {
                                      final shortId = order.id.length > 8 ? order.id.substring(0, 8) : order.id;
                                      return DataRow(
                                        cells: [
                                          DataCell(Text(
                                            '#$shortId',
                                            style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF1E40AF), fontSize: 14),
                                          )),
                                          DataCell(Text(
                                            dateFormat.format(order.createdAt),
                                            style: TextStyle(color: Colors.grey.shade700, fontWeight: FontWeight.w500),
                                          )),
                                          DataCell(Text(
                                            '${currencyFmt.format(order.totalAmount)} ج.س',
                                            style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0F172A), fontSize: 15),
                                          )),
                                          DataCell(
                                            Container(
                                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                              decoration: BoxDecoration(
                                                color: _getStatusColor(order.status).withValues(alpha: 0.12),
                                                borderRadius: BorderRadius.circular(20),
                                                border: Border.all(color: _getStatusColor(order.status).withValues(alpha: 0.3)),
                                              ),
                                              child: Row(
                                                mainAxisSize: MainAxisSize.min,
                                                children: [
                                                  Icon(Icons.circle, size: 8, color: _getStatusColor(order.status)),
                                                  const SizedBox(width: 8),
                                                  Text(
                                                    _getStatusArabic(order.status),
                                                    style: TextStyle(
                                                      color: _getStatusColor(order.status),
                                                      fontSize: 12,
                                                      fontWeight: FontWeight.bold,
                                                    ),
                                                  ),
                                                ],
                                              ),
                                            ),
                                          ),
                                          DataCell(
                                            IconButton(
                                              icon: const Icon(Icons.remove_red_eye, color: Color(0xFF1E40AF), size: 24),
                                              onPressed: () => _showOrderDetailsModal(order, isRtl),
                                              tooltip: isRtl ? 'عرض التفاصيل وتحديث الحالة' : 'View Details & Update',
                                              style: IconButton.styleFrom(
                                                backgroundColor: const Color(0xFF1E40AF).withValues(alpha: 0.1),
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                              ),
                                            ),
                                          ),
                                        ],
                                      );
                                    }).toList(),
                                  ),
                                ),
                              ),
                            ),
                          ),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
