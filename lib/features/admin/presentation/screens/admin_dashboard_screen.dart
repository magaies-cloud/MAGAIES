import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/core/enums/user_role.dart';
import 'package:magaiees_marketplace/features/admin/pricing/application/pricing_provider.dart';
import 'package:magaiees_marketplace/features/admin/subscribers/application/subscribers_provider.dart';
import 'package:magaiees_marketplace/features/admin/subscribers/domain/models/subscriber_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';

class AdminDashboardScreen extends ConsumerStatefulWidget {
  const AdminDashboardScreen({super.key});

  @override
  ConsumerState<AdminDashboardScreen> createState() => _AdminDashboardScreenState();
}

class _AdminDashboardScreenState extends ConsumerState<AdminDashboardScreen> {
  // Recent orders local state for interactive status management
  final List<Map<String, dynamic>> _recentOrders = [
    {
      'id': 'ORD-8821',
      'customer': 'أحمد محمد عثمان',
      'phone': '+249 912 345 678',
      'items': 'حديد تسليح 16 ملم (10 طن)',
      'amount': 32500000.0,
      'status': 'قيد المعالجة',
      'statusEn': 'Processing',
      'color': Colors.orange,
      'date': '2026-08-22 14:30',
    },
    {
      'id': 'ORD-8822',
      'customer': 'سارة خالد المنصور',
      'phone': '+249 923 456 789',
      'items': 'أسمنت عطبرة (200 كيس)',
      'amount': 5700000.0,
      'status': 'مكتمل',
      'statusEn': 'Delivered',
      'color': Colors.green,
      'date': '2026-08-22 11:15',
    },
    {
      'id': 'ORD-8823',
      'customer': 'م. عمر عبدالله',
      'phone': '+249 911 223 344',
      'items': 'مواسير وزنك جملون',
      'amount': 18400000.0,
      'status': 'مشحون',
      'statusEn': 'Shipped',
      'color': Colors.blue,
      'date': '2026-08-21 16:45',
    },
    {
      'id': 'ORD-8824',
      'customer': 'فاطمة علي التاج',
      'phone': '+249 915 678 901',
      'items': 'سيراميك وبورسلين (150 م²)',
      'amount': 6750000.0,
      'status': 'ملغي',
      'statusEn': 'Cancelled',
      'color': Colors.red,
      'date': '2026-08-21 09:20',
    },
    {
      'id': 'ORD-8825',
      'customer': 'محمود حسن الجزولي',
      'phone': '+249 918 765 432',
      'items': 'حديد الأسعد 12 ملم (15 طن)',
      'amount': 48900000.0,
      'status': 'قيد المعالجة',
      'statusEn': 'Processing',
      'color': Colors.orange,
      'date': '2026-08-20 18:00',
    },
  ];

  void _showOrderDetails(Map<String, dynamic> order, bool isRtl) {
    final currencyFmt = NumberFormat.decimalPattern('ar');
    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDlgState) {
          return Directionality(
            textDirection: isRtl ? TextDirection.rtl : TextDirection.ltr,
            child: AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              title: Row(
                children: [
                  const Icon(Icons.receipt_long, color: Color(0xFF1E40AF)),
                  const SizedBox(width: 8),
                  Text('${isRtl ? "تفاصيل الطلب" : "Order Details"} #${order['id']}'),
                ],
              ),
              content: SizedBox(
                width: 480,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildDetailRow(isRtl ? 'العميل' : 'Customer', order['customer']),
                    _buildDetailRow(isRtl ? 'الهاتف' : 'Phone', order['phone']),
                    _buildDetailRow(isRtl ? 'المنتجات المطلوبة' : 'Items', order['items']),
                    _buildDetailRow(
                      isRtl ? 'إجمالي الفاتورة' : 'Total Amount',
                      '${currencyFmt.format(order['amount'])} ${isRtl ? "ج.س" : "SDG"}',
                      isBold: true,
                    ),
                    _buildDetailRow(isRtl ? 'تاريخ الطلب' : 'Date', order['date']),
                    const SizedBox(height: 16),
                    const Divider(),
                    const SizedBox(height: 8),
                    Text(
                      isRtl ? 'تحديث حالة الطلب:' : 'Update Status:',
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 8),
                    Wrap(
                      spacing: 8,
                      children: [
                        _buildStatusOption(order, 'قيد المعالجة', 'Processing', Colors.orange, setDlgState),
                        _buildStatusOption(order, 'مشحون', 'Shipped', Colors.blue, setDlgState),
                        _buildStatusOption(order, 'مكتمل', 'Delivered', Colors.green, setDlgState),
                        _buildStatusOption(order, 'ملغي', 'Cancelled', Colors.red, setDlgState),
                      ],
                    ),
                  ],
                ),
              ),
              actions: [
                FilledButton(
                  onPressed: () => Navigator.pop(ctx),
                  child: Text(isRtl ? 'إغلاق' : 'Close'),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildDetailRow(String label, String value, {bool isBold = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 130,
            child: Text(
              '$label:',
              style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: TextStyle(
                fontWeight: isBold ? FontWeight.bold : FontWeight.w600,
                fontSize: isBold ? 15 : 13,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusOption(
    Map<String, dynamic> order,
    String statusAr,
    String statusEn,
    Color color,
    StateSetter setDlgState,
  ) {
    final isSelected = order['status'] == statusAr;
    return ChoiceChip(
      label: Text(statusAr),
      selected: isSelected,
      selectedColor: color.withValues(alpha: 0.2),
      labelStyle: TextStyle(
        color: isSelected ? color : Colors.grey.shade700,
        fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
      ),
      onSelected: (_) {
        setState(() {
          order['status'] = statusAr;
          order['statusEn'] = statusEn;
          order['color'] = color;
        });
        setDlgState(() {});
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final currencyFmt = NumberFormat.decimalPattern('ar');

    final allSubscribersAsync = ref.watch(subscribersProvider);
    final allSubscribers = allSubscribersAsync.valueOrNull ?? [];
    final totalUsers = allSubscribers.length;
    final activeMerchants = allSubscribers.where((s) => s.role == UserRole.merchant && s.status == SubscriberStatus.active).length;
    final pendingMerchants = allSubscribers.where((s) => s.status == SubscriberStatus.pendingVerification).toList();

    final productsAsync = ref.watch(productsProvider);
    final allProducts = productsAsync.valueOrNull ?? [];
    final exclusiveProducts = allProducts.where((p) => p.isMagaieesExclusive || const {'1', '2', '6'}.contains(p.categoryId)).length;

    final pricingState = ref.watch(pricingProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Top Header with FX Indicator
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      isRtl ? 'لوحة التحكم الرئيسية' : 'Dashboard Overview',
                      style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                            fontWeight: FontWeight.bold,
                            color: const Color(0xFF0F172A),
                          ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      isRtl ? 'متابعة حية للمبيعات، المشتركين، والتسعير بالدولار' : 'Live overview of sales, subscribers & FX pricing',
                      style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                    ),
                  ],
                ),
                // Live FX pill badge
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  decoration: BoxDecoration(
                    color: const Color(0xFF1E40AF).withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFF1E40AF).withValues(alpha: 0.3)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.currency_exchange, color: Color(0xFF1E40AF), size: 18),
                      const SizedBox(width: 8),
                      Text(
                        '1 USD = ${currencyFmt.format(pricingState.usdExchangeRate)} SDG',
                        style: const TextStyle(
                          color: Color(0xFF1E40AF),
                          fontWeight: FontWeight.bold,
                          fontSize: 13,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 24),

            // Row 1 - Stats Cards
            LayoutBuilder(
              builder: (context, constraints) {
                final double cardWidth = (constraints.maxWidth - (16 * 3)) / 4;
                return Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    _buildStatCard(
                      context,
                      title: isRtl ? 'إجمالي المشتركين' : 'Total Subscribers',
                      value: '$totalUsers',
                      subtitle: isRtl ? 'مشترين وتجار' : 'Buyers & Merchants',
                      icon: Icons.people,
                      color: Colors.blue,
                      width: cardWidth,
                    ),
                    _buildStatCard(
                      context,
                      title: isRtl ? 'التجار المعتمدين' : 'Active Merchants',
                      value: '$activeMerchants',
                      subtitle: isRtl ? 'متاجر مفعلة بالسوق' : 'Verified Stores',
                      icon: Icons.storefront,
                      color: Colors.green,
                      width: cardWidth,
                    ),
                    _buildStatCard(
                      context,
                      title: isRtl ? 'أصناف توريد مقاييس' : 'Magaiees Direct Supply',
                      value: '$exclusiveProducts',
                      subtitle: isRtl ? 'حديد، أسمنت، مواسير' : 'Exclusive items',
                      icon: Icons.verified,
                      color: const Color(0xFF1E40AF),
                      width: cardWidth,
                    ),
                    _buildStatCard(
                      context,
                      title: isRtl ? 'إجمالي المبيعات' : 'Total Sales Volume',
                      value: '${currencyFmt.format(111450000)} ج.س',
                      subtitle: isRtl ? 'هذا الشهر' : 'This Month',
                      icon: Icons.payments,
                      color: Colors.amber.shade800,
                      width: cardWidth,
                    ),
                  ],
                );
              },
            ),
            const SizedBox(height: 24),

            // Row 2 - Charts
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(child: _buildRevenueChart(context, isRtl)),
                const SizedBox(width: 24),
                Expanded(child: _buildNewUsersChart(context, isRtl)),
              ],
            ),
            const SizedBox(height: 24),

            // Row 3 - Recent Activity & Pending Approvals
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  flex: 2,
                  child: _buildLatestOrders(context, isRtl),
                ),
                const SizedBox(width: 24),
                Expanded(
                  flex: 1,
                  child: _buildPendingApprovals(context, isRtl, pendingMerchants),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard(
    BuildContext context, {
    required String title,
    required String value,
    required String subtitle,
    required IconData icon,
    required Color color,
    required double width,
  }) {
    return Container(
      width: width,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                title,
                style: TextStyle(
                  color: Colors.grey.shade700,
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                ),
              ),
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(icon, color: color, size: 20),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            value,
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.bold,
              color: const Color(0xFF0F172A),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            subtitle,
            style: TextStyle(color: Colors.grey.shade500, fontSize: 11),
          ),
        ],
      ),
    );
  }

  Widget _buildRevenueChart(BuildContext context, bool isRtl) {
    final months = isRtl
        ? ['مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس']
        : ['Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'];
    final values = [0.45, 0.60, 0.55, 0.85, 0.72, 1.0];
    final amounts = ['48M', '62M', '55M', '92M', '78M', '111M'];

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                isRtl ? '📈 الإيرادات الشهرية (ج.س)' : '📈 Monthly Revenue (SDG)',
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.green.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Text(
                  '+28.4% ↗',
                  style: TextStyle(color: Colors.green, fontWeight: FontWeight.bold, fontSize: 12),
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),
          SizedBox(
            height: 180,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              crossAxisAlignment: CrossAxisAlignment.end,
              children: List.generate(6, (index) {
                return Column(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    Text(
                      amounts[index],
                      style: TextStyle(fontSize: 10, color: Colors.grey.shade600, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 6),
                    Container(
                      width: 36,
                      height: 120 * values[index],
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          begin: Alignment.bottomCenter,
                          end: Alignment.topCenter,
                          colors: [
                            const Color(0xFF1E40AF),
                            index == 5 ? const Color(0xFF3B82F6) : const Color(0xFF60A5FA),
                          ],
                        ),
                        borderRadius: const BorderRadius.vertical(top: Radius.circular(6)),
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(months[index], style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w500)),
                  ],
                );
              }),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildNewUsersChart(BuildContext context, bool isRtl) {
    final days = isRtl
        ? ['السبت', 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة']
        : ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
    final values = [0.35, 0.55, 0.42, 0.78, 0.65, 0.95, 0.82];
    final counts = [12, 18, 14, 26, 22, 34, 28];

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                isRtl ? '👥 المشتركين الجدد هذا الأسبوع' : '👥 New Users This Week',
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
              ),
              Text(
                '154 ${isRtl ? "مستخدم جديد" : "Total"}',
                style: TextStyle(color: Colors.blue.shade700, fontWeight: FontWeight.bold, fontSize: 12),
              ),
            ],
          ),
          const SizedBox(height: 18),
          SizedBox(
            height: 186,
            child: Column(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: List.generate(7, (index) {
                return Row(
                  children: [
                    SizedBox(
                      width: 55,
                      child: Text(days[index], style: const TextStyle(fontSize: 11, color: Colors.grey)),
                    ),
                    Expanded(
                      child: Stack(
                        children: [
                          Container(
                            height: 14,
                            decoration: BoxDecoration(
                              color: Colors.grey.shade100,
                              borderRadius: BorderRadius.circular(7),
                            ),
                          ),
                          FractionallySizedBox(
                            alignment: isRtl ? Alignment.centerRight : Alignment.centerLeft,
                            widthFactor: values[index],
                            child: Container(
                              height: 14,
                              decoration: BoxDecoration(
                                color: const Color(0xFF059669),
                                borderRadius: BorderRadius.circular(7),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 10),
                    SizedBox(
                      width: 24,
                      child: Text(
                        '${counts[index]}',
                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ],
                );
              }),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLatestOrders(BuildContext context, bool isRtl) {
    final currencyFmt = NumberFormat.decimalPattern('ar');

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                isRtl ? '📦 أحدث طلبات التوريد والشراء' : '📦 Latest Orders',
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
              ),
              Text(
                isRtl ? 'اضغط على الطلب للتفاصيل' : 'Click order for details',
                style: TextStyle(color: Colors.grey.shade500, fontSize: 12),
              ),
            ],
          ),
          const SizedBox(height: 14),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: DataTable(
              headingRowColor: WidgetStatePropertyAll(Colors.grey.shade50),
              headingTextStyle: TextStyle(fontWeight: FontWeight.bold, color: Colors.grey.shade800, fontSize: 12),
              columnSpacing: 16,
              horizontalMargin: 8,
              columns: [
                DataColumn(label: Text(isRtl ? 'رقم الطلب' : 'Order ID')),
                DataColumn(label: Text(isRtl ? 'العميل' : 'Customer')),
                DataColumn(label: Text(isRtl ? 'المبلغ (ج.س)' : 'Amount')),
                DataColumn(label: Text(isRtl ? 'الحالة' : 'Status')),
                DataColumn(label: Text(isRtl ? 'التاريخ' : 'Date')),
                DataColumn(label: Text(isRtl ? 'إجراء' : 'Action')),
              ],
              rows: _recentOrders.map((order) {
                return DataRow(
                  cells: [
                    DataCell(Text(order['id'], style: const TextStyle(fontWeight: FontWeight.bold))),
                    DataCell(Text(order['customer'])),
                    DataCell(Text(currencyFmt.format(order['amount']))),
                    DataCell(
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: (order['color'] as Color).withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: (order['color'] as Color).withValues(alpha: 0.4)),
                        ),
                        child: Text(
                          isRtl ? order['status'] : order['statusEn'],
                          style: TextStyle(color: order['color'] as Color, fontSize: 11, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ),
                    DataCell(Text(order['date'], style: TextStyle(color: Colors.grey.shade600, fontSize: 11))),
                    DataCell(
                      IconButton(
                        icon: const Icon(Icons.visibility_outlined, size: 18, color: Color(0xFF1E40AF)),
                        tooltip: isRtl ? 'عرض وتحديث الطلب' : 'View Details',
                        onPressed: () => _showOrderDetails(order, isRtl),
                      ),
                    ),
                  ],
                );
              }).toList(),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPendingApprovals(BuildContext context, bool isRtl, List<SubscriberModel> pendingList) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                isRtl ? '⏳ طلبات اعتماد التجار' : '⏳ Pending Merchants',
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: Colors.amber.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  '${pendingList.length} ${isRtl ? "معلق" : "Pending"}',
                  style: TextStyle(color: Colors.amber.shade900, fontSize: 11, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          if (pendingList.isEmpty)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 24.0),
              child: Center(
                child: Column(
                  children: [
                    const Icon(Icons.check_circle_outline, color: Colors.green, size: 40),
                    const SizedBox(height: 8),
                    Text(
                      isRtl ? 'تمت مراجعة جميع طلبات التجار' : 'All merchant requests verified',
                      style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                    ),
                  ],
                ),
              ),
            )
          else
            ...pendingList.map((subscriber) {
              return Padding(
                padding: const EdgeInsets.symmetric(vertical: 8.0),
                child: Row(
                  children: [
                    CircleAvatar(
                      backgroundColor: Colors.blue.withValues(alpha: 0.1),
                      child: const Icon(Icons.storefront, color: Color(0xFF1E40AF), size: 20),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            isRtl ? subscriber.nameAr : subscriber.name,
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                          ),
                          Text(
                            subscriber.email,
                            style: TextStyle(color: Colors.grey.shade600, fontSize: 11),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                    Row(
                      children: [
                        IconButton(
                          icon: const Icon(Icons.check_circle, color: Colors.green, size: 22),
                          tooltip: isRtl ? 'اعتماد التاجر' : 'Approve',
                          onPressed: () {
                            ref.read(subscribersProvider.notifier).approveMerchant(subscriber.id);
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text('تم اعتماد التاجر "${subscriber.nameAr}" بنجاح وتفعيل متجره بالسوق'),
                                backgroundColor: Colors.green,
                              ),
                            );
                          },
                        ),
                        IconButton(
                          icon: const Icon(Icons.cancel, color: Colors.red, size: 22),
                          tooltip: isRtl ? 'رفض الطلب' : 'Reject',
                          onPressed: () {
                            ref.read(subscribersProvider.notifier).toggleSuspension(subscriber.id);
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text('تم رفض طلب التاجر "${subscriber.nameAr}"'),
                                backgroundColor: Colors.red.shade700,
                              ),
                            );
                          },
                        ),
                      ],
                    ),
                  ],
                ),
              );
            }),
        ],
      ),
    );
  }
}
