import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/core/enums/user_role.dart';
import 'package:magaiees_marketplace/features/admin/pricing/application/pricing_provider.dart';
import 'package:magaiees_marketplace/features/admin/subscribers/application/subscribers_provider.dart';
import 'package:magaiees_marketplace/features/admin/subscribers/domain/models/subscriber_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/features/admin/orders/application/orders_provider.dart';
import 'package:magaiees_marketplace/features/admin/presentation/screens/admin_shell.dart';

class AdminDashboardScreen extends ConsumerStatefulWidget {
  const AdminDashboardScreen({super.key});

  @override
  ConsumerState<AdminDashboardScreen> createState() => _AdminDashboardScreenState();
}

class _AdminDashboardScreenState extends ConsumerState<AdminDashboardScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(adminOrdersProvider.notifier).loadOrders();
    });
  }

  Color _getStatusColor(String status) {
    final s = status.toLowerCase();
    if (s == 'delivered' || s == 'completed' || s == 'مكتمل') return const Color(0xFF10B981);
    if (s == 'cancelled' || s == 'ملغي') return const Color(0xFFEF4444);
    if (s == 'shipped' || s == 'مشحون') return const Color(0xFF3B82F6);
    return const Color(0xFFF59E0B);
  }

  String _getStatusText(String status, bool isRtl) {
    final s = status.toLowerCase();
    if (s == 'delivered' || s == 'completed' || s == 'مكتمل') return isRtl ? 'مكتمل' : 'Delivered';
    if (s == 'cancelled' || s == 'ملغي') return isRtl ? 'ملغي' : 'Cancelled';
    if (s == 'shipped' || s == 'مشحون') return isRtl ? 'مشحون' : 'Shipped';
    return isRtl ? 'قيد المعالجة' : 'Processing';
  }

  void _showOrderDetails(BuildContext context, WidgetRef ref, OrderModel order, bool isRtl) {
    final currencyFmt = NumberFormat.decimalPattern('ar');
    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDlgState) {
          return Directionality(
            textDirection: isRtl ? TextDirection.rtl : TextDirection.ltr,
            child: AlertDialog(
              backgroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              title: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFF1E40AF).withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.receipt_long, color: Color(0xFF1E40AF)),
                  ),
                  const SizedBox(width: 12),
                  Text(
                    '${isRtl ? "تفاصيل الطلب" : "Order Details"} #${order.id.length > 8 ? order.id.substring(0, 8) : order.id}',
                    style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                  ),
                ],
              ),
              content: SizedBox(
                width: 480,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildDetailRow(isRtl ? 'العميل' : 'Customer', order.buyerId),
                    _buildDetailRow(
                      isRtl ? 'إجمالي الفاتورة' : 'Total Amount',
                      '${currencyFmt.format(order.totalAmount)} ${isRtl ? "ج.س" : "SDG"}',
                      isBold: true,
                    ),
                    _buildDetailRow(isRtl ? 'تاريخ الطلب' : 'Date', DateFormat('yyyy-MM-dd HH:mm').format(order.createdAt)),
                    const SizedBox(height: 24),
                    const Divider(color: Color(0xFFE2E8F0)),
                    const SizedBox(height: 16),
                    Text(
                      isRtl ? 'تحديث حالة الطلب:' : 'Update Status:',
                      style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                    ),
                    const SizedBox(height: 12),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: [
                        _buildStatusOption(ctx, ref, order, 'pending', isRtl ? 'قيد المعالجة' : 'Pending', const Color(0xFFF59E0B), setDlgState),
                        _buildStatusOption(ctx, ref, order, 'shipped', isRtl ? 'مشحون' : 'Shipped', const Color(0xFF3B82F6), setDlgState),
                        _buildStatusOption(ctx, ref, order, 'delivered', isRtl ? 'مكتمل' : 'Delivered', const Color(0xFF10B981), setDlgState),
                        _buildStatusOption(ctx, ref, order, 'cancelled', isRtl ? 'ملغي' : 'Cancelled', const Color(0xFFEF4444), setDlgState),
                      ],
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(ctx),
                  child: Text(isRtl ? 'إغلاق' : 'Close', style: const TextStyle(color: Color(0xFF64748B))),
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
      padding: const EdgeInsets.symmetric(vertical: 6.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 140,
            child: Text(
              '$label:',
              style: const TextStyle(color: Color(0xFF64748B), fontSize: 14, fontWeight: FontWeight.w500),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: TextStyle(
                fontWeight: isBold ? FontWeight.bold : FontWeight.w600,
                color: isBold ? const Color(0xFF0F172A) : const Color(0xFF334155),
                fontSize: isBold ? 16 : 14,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusOption(
    BuildContext ctx,
    WidgetRef ref,
    OrderModel order,
    String statusKey,
    String label,
    Color color,
    StateSetter setDlgState,
  ) {
    final isSelected = order.status.toLowerCase() == statusKey.toLowerCase();
    return InkWell(
      onTap: () {
        if (!isSelected) {
          ref.read(adminOrdersProvider.notifier).updateOrderStatus(order.id, statusKey);
          Navigator.pop(ctx);
          ScaffoldMessenger.of(ctx).showSnackBar(
            SnackBar(
              content: Text('تم تحديث حالة الطلب إلى $label'),
              backgroundColor: color,
              behavior: SnackBarBehavior.floating,
            ),
          );
        }
      },
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? color.withValues(alpha: 0.1) : Colors.transparent,
          border: Border.all(color: isSelected ? color : const Color(0xFFCBD5E1)),
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: isSelected ? color : const Color(0xFF475569),
            fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final currencyFmt = NumberFormat.decimalPattern('ar');

    final allSubscribersAsync = ref.watch(subscribersProvider);
    final allSubscribers = allSubscribersAsync.valueOrNull ?? [];
    
    final productsAsync = ref.watch(productsProvider);
    final allProducts = productsAsync.valueOrNull ?? [];
    final totalAdvertisedProducts = allProducts.length;
    final pendingMerchants = allSubscribers.where((s) => s.status == SubscriberStatus.pendingVerification).toList();

    final pricingState = ref.watch(pricingProvider);
    
    final ordersAsync = ref.watch(adminOrdersProvider);
    final allOrders = ordersAsync.valueOrNull ?? [];
    
    final completedOrders = allOrders.where((o) => o.status == 'مكتمل' || o.status.toLowerCase() == 'completed' || o.status.toLowerCase() == 'delivered').toList();
    final cancelledOrders = allOrders.where((o) => o.status == 'ملغي' || o.status.toLowerCase() == 'cancelled').toList();
    final inProgressOrders = allOrders.length - completedOrders.length - cancelledOrders.length;
    final validOrdersForSales = completedOrders;
    
    double totalSales = 0.0;
    for (var o in validOrdersForSales) {
      totalSales += o.totalAmount;
    }

    final now = DateTime.now();
    double currentMonthRevenue = 0.0;
    for (var o in validOrdersForSales.where((o) => o.createdAt.year == now.year && o.createdAt.month == now.month)) {
      currentMonthRevenue += o.totalAmount;
    }

    final thirtyDaysAgo = now.subtract(const Duration(days: 30));
    final sixtyDaysAgo = now.subtract(const Duration(days: 60));
    final newSubscribersCount = allSubscribers.where((s) => s.joinedAt.isAfter(thirtyDaysAgo)).length;
    final previousSubscribersCount = allSubscribers.where((s) => s.joinedAt.isAfter(sixtyDaysAgo) && s.joinedAt.isBefore(thirtyDaysAgo)).length;
    double subscriberGrowth = 0.0;
    if (previousSubscribersCount > 0) {
      subscriberGrowth = ((newSubscribersCount - previousSubscribersCount) / previousSubscribersCount) * 100;
    } else if (newSubscribersCount > 0) {
      subscriberGrowth = 100.0;
    }
    
    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(32.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      isRtl ? 'لوحة التحكم الرئيسية' : 'Dashboard Overview',
                      style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                            fontWeight: FontWeight.w800,
                            color: const Color(0xFF0F172A),
                            letterSpacing: -0.5,
                          ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      isRtl ? 'متابعة حية للمبيعات، المشتركين، والتسعير بالدولار' : 'Live overview of sales, subscribers & FX pricing',
                      style: const TextStyle(color: Color(0xFF64748B), fontSize: 14, fontWeight: FontWeight.w500),
                    ),
                  ],
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFF0F172A).withValues(alpha: 0.03),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(6),
                        decoration: BoxDecoration(
                          color: const Color(0xFF1E40AF).withValues(alpha: 0.1),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(Icons.currency_exchange, color: Color(0xFF1E40AF), size: 16),
                      ),
                      const SizedBox(width: 12),
                      Text(
                        '1 USD = ${currencyFmt.format(pricingState.usdExchangeRate)} SDG',
                        style: const TextStyle(
                          color: Color(0xFF0F172A),
                          fontWeight: FontWeight.bold,
                          fontSize: 14,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 32),

            LayoutBuilder(
              builder: (context, constraints) {
                final double spacing = 16.0;
                final int crossAxisCount = constraints.maxWidth > 1400 ? 4 : (constraints.maxWidth > 900 ? 4 : 2);
                final double cardWidth = (constraints.maxWidth - (spacing * (crossAxisCount - 1))) / crossAxisCount;

                return Wrap(
                  spacing: spacing,
                  runSpacing: spacing,
                  children: [
                    _buildStatCard(
                      context,
                      title: isRtl ? 'إيرادات عامة' : 'Total Revenue',
                      value: '${currencyFmt.format(totalSales)}',
                      subtitle: isRtl ? 'إجمالي الدخل' : 'Overall Income',
                      icon: Icons.account_balance_wallet,
                      color: const Color(0xFF10B981),
                      width: cardWidth,
                    ),
                    _buildStatCard(
                      context,
                      title: isRtl ? 'إيرادات الشهر' : 'Monthly Revenue',
                      value: '${currencyFmt.format(currentMonthRevenue)}',
                      subtitle: isRtl ? 'الوارد هذا الشهر' : 'This Month Income',
                      icon: Icons.trending_up,
                      color: const Color(0xFF059669),
                      width: cardWidth,
                    ),
                    _buildStatCard(
                      context,
                      title: isRtl ? 'إجمالي الطلبات' : 'Total Orders',
                      value: '${allOrders.length}',
                      subtitle: isRtl ? 'جميع الطلبات' : 'All orders',
                      icon: Icons.shopping_cart,
                      color: const Color(0xFF8B5CF6),
                      width: cardWidth,
                    ),
                    _buildStatCard(
                      context,
                      title: isRtl ? 'طلبات منفذة' : 'Completed Orders',
                      value: '${completedOrders.length}',
                      subtitle: isRtl ? 'تم التسليم' : 'Executed / Delivered',
                      icon: Icons.check_circle,
                      color: const Color(0xFF3B82F6),
                      width: cardWidth,
                    ),
                    _buildStatCard(
                      context,
                      title: isRtl ? 'قيد التنفيذ' : 'Orders In Progress',
                      value: '$inProgressOrders',
                      subtitle: isRtl ? 'جاري المعالجة' : 'Pending / Shipped',
                      icon: Icons.local_shipping,
                      color: const Color(0xFFF59E0B),
                      width: cardWidth,
                    ),
                    _buildStatCard(
                      context,
                      title: isRtl ? 'مشتركين جدد' : 'New Subscribers',
                      value: '$newSubscribersCount',
                      subtitle: isRtl ? 'آخر 30 يوم' : 'Last 30 days',
                      icon: Icons.person_add,
                      color: const Color(0xFFEC4899),
                      width: cardWidth,
                    ),
                    _buildStatCard(
                      context,
                      title: isRtl ? 'نمو المشتركين' : 'Subscriber Growth',
                      value: '${subscriberGrowth > 0 ? "+" : ""}${subscriberGrowth.toStringAsFixed(1)}%',
                      subtitle: isRtl ? 'مقارنة بالشهر السابق' : 'vs last month',
                      icon: Icons.insights,
                      color: subscriberGrowth >= 0 ? const Color(0xFF10B981) : const Color(0xFFEF4444),
                      width: cardWidth,
                    ),
                    _buildStatCard(
                      context,
                      title: isRtl ? 'المنتجات المعلنة' : 'Advertised Products',
                      value: '$totalAdvertisedProducts',
                      subtitle: isRtl ? 'إجمالي المنتجات' : 'Total Products',
                      icon: Icons.inventory,
                      color: const Color(0xFF1E40AF),
                      width: cardWidth,
                    ),
                  ],
                );
              },
            ),
            const SizedBox(height: 32),

            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(child: _buildRevenueChart(context, isRtl, validOrdersForSales)),
                const SizedBox(width: 24),
                Expanded(child: _buildNewUsersChart(context, isRtl, allSubscribers)),
              ],
            ),
            const SizedBox(height: 32),

            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  flex: 2,
                  child: _buildLatestOrders(context, isRtl, allOrders),
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
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.04),
            blurRadius: 16,
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
                style: const TextStyle(
                  color: Color(0xFF64748B),
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                ),
              ),
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: color, size: 22),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Directionality(
            textDirection: TextDirection.ltr,
            child: Text(
              value,
              style: const TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.w800,
                color: Color(0xFF0F172A),
                letterSpacing: -0.5,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              textAlign: Directionality.of(context) == TextDirection.rtl ? TextAlign.right : TextAlign.left,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            subtitle,
            style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12, fontWeight: FontWeight.w500),
          ),
        ],
      ),
    );
  }

  Widget _buildRevenueChart(BuildContext context, bool isRtl, List<OrderModel> validOrders) {
    final now = DateTime.now();
    List<String> months = [];
    List<double> monthlyRevenue = [];
    List<String> monthlyRevenueFormatted = [];
    
    double totalRevenue = 0;
    
    for (int i = 5; i >= 0; i--) {
      final monthDate = DateTime(now.year, now.month - i, 1);
      final monthFormat = DateFormat('MMM');
      final arMonths = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
      final arMonth = arMonths[monthDate.month - 1];
      
      months.add(isRtl ? arMonth : monthFormat.format(monthDate));
      
      double monthTotal = 0.0;
      for (var o in validOrders) {
        if (o.createdAt.year == monthDate.year && o.createdAt.month == monthDate.month) {
          monthTotal += o.totalAmount;
        }
      }
      monthlyRevenue.add(monthTotal);
      totalRevenue += monthTotal;
    }
    
    final maxMonthlyRev = monthlyRevenue.isEmpty ? 0.0 : monthlyRevenue.reduce((a, b) => a > b ? a : b);
    List<double> normalizedRev = monthlyRevenue.map((v) => maxMonthlyRev == 0 ? 0.0 : v / maxMonthlyRev).toList();
    
    for (var v in monthlyRevenue) {
      if (v >= 1000000) {
        monthlyRevenueFormatted.add('${(v / 1000000).toStringAsFixed(1)}M');
      } else if (v >= 1000) {
        monthlyRevenueFormatted.add('${(v / 1000).toStringAsFixed(1)}K');
      } else {
        monthlyRevenueFormatted.add(v.toStringAsFixed(0));
      }
    }

    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.04),
            blurRadius: 16,
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
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    isRtl ? 'الإيرادات الشهرية' : 'Monthly Revenue',
                    style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 18, color: Color(0xFF0F172A)),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    isRtl ? 'آخر 6 أشهر' : 'Last 6 months',
                    style: const TextStyle(color: Color(0xFF64748B), fontSize: 13),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: const Color(0xFF10B981).withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.trending_up, color: Color(0xFF10B981), size: 16),
                    const SizedBox(width: 6),
                    Text(
                      '${totalRevenue > 0 ? (isRtl ? "نشط" : "Active") : (isRtl ? "في الانتظار" : "Pending")}',
                      style: const TextStyle(color: Color(0xFF10B981), fontWeight: FontWeight.bold, fontSize: 13),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 32),
          SizedBox(
            height: 200,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              crossAxisAlignment: CrossAxisAlignment.end,
              children: List.generate(6, (index) {
                return Column(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    Text(
                      monthlyRevenueFormatted[index],
                      style: const TextStyle(fontSize: 12, color: Color(0xFF64748B), fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 8),
                    Container(
                      width: 40,
                      height: 140 * (normalizedRev[index] == 0 ? 0.05 : normalizedRev[index]),
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          begin: Alignment.bottomCenter,
                          end: Alignment.topCenter,
                          colors: [
                            const Color(0xFF1E40AF),
                            index == 5 ? const Color(0xFF3B82F6) : const Color(0xFF60A5FA),
                          ],
                        ),
                        borderRadius: BorderRadius.circular(6),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Text(months[index], style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF334155))),
                  ],
                );
              }),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildNewUsersChart(BuildContext context, bool isRtl, List<SubscriberModel> allSubscribers) {
    final now = DateTime.now();
    List<String> days = [];
    List<int> counts = [];
    
    int totalThisWeek = 0;
    
    for (int i = 6; i >= 0; i--) {
      final dayDate = now.subtract(Duration(days: i));
      final dayFormat = DateFormat('E');
      final arDays = {'Mon': 'الإثنين', 'Tue': 'الثلاثاء', 'Wed': 'الأربعاء', 'Thu': 'الخميس', 'Fri': 'الجمعة', 'Sat': 'السبت', 'Sun': 'الأحد'};
      
      final dayEn = dayFormat.format(dayDate);
      days.add(isRtl ? (arDays[dayEn] ?? dayEn) : dayEn);
      
      int count = 0;
      for (var s in allSubscribers) {
        if (s.joinedAt.year == dayDate.year && s.joinedAt.month == dayDate.month && s.joinedAt.day == dayDate.day) {
          count++;
        }
      }
      counts.add(count);
      totalThisWeek += count;
    }
    
    final maxDailySub = counts.isEmpty ? 0 : counts.reduce((a, b) => a > b ? a : b);
    List<double> normalizedSub = counts.map((v) => maxDailySub == 0 ? 0.0 : v / maxDailySub).toList();

    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.04),
            blurRadius: 16,
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
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    isRtl ? 'المشتركين الجدد' : 'New Subscribers',
                    style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 18, color: Color(0xFF0F172A)),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    isRtl ? 'آخر 7 أيام' : 'Last 7 days',
                    style: const TextStyle(color: Color(0xFF64748B), fontSize: 13),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: const Color(0xFF3B82F6).withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  '$totalThisWeek ${isRtl ? "مستخدم جديد" : "Total"}',
                  style: const TextStyle(color: Color(0xFF2563EB), fontWeight: FontWeight.bold, fontSize: 13),
                ),
              ),
            ],
          ),
          const SizedBox(height: 32),
          SizedBox(
            height: 200,
            child: Column(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: List.generate(7, (index) {
                return Row(
                  children: [
                    SizedBox(
                      width: 60,
                      child: Text(days[index], style: const TextStyle(fontSize: 13, color: Color(0xFF475569), fontWeight: FontWeight.w500)),
                    ),
                    Expanded(
                      child: Stack(
                        children: [
                          Container(
                            height: 12,
                            decoration: BoxDecoration(
                              color: const Color(0xFFF1F5F9),
                              borderRadius: BorderRadius.circular(6),
                            ),
                          ),
                          FractionallySizedBox(
                            alignment: isRtl ? Alignment.centerRight : Alignment.centerLeft,
                            widthFactor: normalizedSub[index] == 0 ? 0.02 : normalizedSub[index],
                            child: Container(
                              height: 12,
                              decoration: BoxDecoration(
                                color: const Color(0xFF8B5CF6),
                                borderRadius: BorderRadius.circular(6),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 16),
                    SizedBox(
                      width: 24,
                      child: Text(
                        '${counts[index]}',
                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                        textAlign: TextAlign.center,
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

  Widget _buildLatestOrders(BuildContext context, bool isRtl, List<OrderModel> orders) {
    final currencyFmt = NumberFormat.decimalPattern('ar');
    final recentOrders = orders.take(10).toList();

    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.04),
            blurRadius: 16,
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
                isRtl ? 'أحدث طلبات التوريد والشراء' : 'Recent Orders',
                style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 18, color: Color(0xFF0F172A)),
              ),
              TextButton.icon(
                onPressed: () {
                  ref.read(adminTabIndexProvider.notifier).state = 3;
                },
                icon: const Icon(Icons.arrow_forward, size: 16),
                label: Text(isRtl ? 'عرض الكل' : 'View All'),
                style: TextButton.styleFrom(foregroundColor: const Color(0xFF1E40AF)),
              )
            ],
          ),
          const SizedBox(height: 20),
          recentOrders.isEmpty
              ? Padding(
                  padding: const EdgeInsets.symmetric(vertical: 40),
                  child: Center(
                    child: Text(
                      isRtl ? 'لا توجد طلبات حديثة' : 'No recent orders',
                      style: const TextStyle(color: Color(0xFF94A3B8)),
                    ),
                  ),
                )
              : SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: DataTable(
                    headingRowColor: WidgetStatePropertyAll(const Color(0xFFF8FAFC)),
                    headingTextStyle: const TextStyle(fontWeight: FontWeight.w600, color: Color(0xFF475569), fontSize: 13),
                    dataRowMaxHeight: 64,
                    columnSpacing: 32,
                    horizontalMargin: 16,
                    dividerThickness: 1,
                    columns: [
                      DataColumn(label: Text(isRtl ? 'رقم الطلب' : 'Order ID')),
                      DataColumn(label: Text(isRtl ? 'العميل' : 'Customer ID')),
                      DataColumn(label: Text(isRtl ? 'المبلغ (ج.س)' : 'Amount')),
                      DataColumn(label: Text(isRtl ? 'الحالة' : 'Status')),
                      DataColumn(label: Text(isRtl ? 'التاريخ' : 'Date')),
                      DataColumn(label: Text(isRtl ? 'إجراء' : 'Action')),
                    ],
                    rows: recentOrders.map((order) {
                      final statusColor = _getStatusColor(order.status);
                      final statusText = _getStatusText(order.status, isRtl);
                      
                      return DataRow(
                        cells: [
                          DataCell(Text(
                            order.id.length > 8 ? order.id.substring(0, 8) : order.id,
                            style: const TextStyle(fontWeight: FontWeight.w600, color: Color(0xFF0F172A)),
                          )),
                          DataCell(Text(
                            order.buyerName ?? (order.buyerId.length > 8 ? order.buyerId.substring(0, 8) : order.buyerId),
                            style: const TextStyle(color: Color(0xFF475569)),
                          )),
                          DataCell(Text(
                            currencyFmt.format(order.totalAmount),
                            style: const TextStyle(fontWeight: FontWeight.w600, color: Color(0xFF0F172A)),
                          )),
                          DataCell(
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                              decoration: BoxDecoration(
                                color: statusColor.withValues(alpha: 0.1),
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(color: statusColor.withValues(alpha: 0.2)),
                              ),
                              child: Text(
                                statusText,
                                style: TextStyle(color: statusColor, fontSize: 12, fontWeight: FontWeight.bold),
                              ),
                            ),
                          ),
                          DataCell(Text(
                            DateFormat('yyyy-MM-dd').format(order.createdAt),
                            style: const TextStyle(color: Color(0xFF64748B), fontSize: 13),
                          )),
                          DataCell(
                            IconButton(
                              icon: const Icon(Icons.edit_note, size: 22, color: Color(0xFF3B82F6)),
                              tooltip: isRtl ? 'تحديث وتفاصيل الطلب' : 'Update Order',
                              onPressed: () => _showOrderDetails(context, ref, order, isRtl),
                              splashRadius: 20,
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
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.04),
            blurRadius: 16,
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
                isRtl ? 'طلبات اعتماد التجار' : 'Pending Merchants',
                style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 18, color: Color(0xFF0F172A)),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: const Color(0xFFF59E0B).withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  '${pendingList.length} ${isRtl ? "معلق" : "Pending"}',
                  style: const TextStyle(color: Color(0xFFD97706), fontSize: 12, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),
          if (pendingList.isEmpty)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 40.0),
              child: Center(
                child: Column(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: const Color(0xFF10B981).withValues(alpha: 0.1),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.check_circle, color: Color(0xFF10B981), size: 32),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      isRtl ? 'تمت مراجعة جميع الطلبات' : 'All requests verified',
                      style: const TextStyle(color: Color(0xFF64748B), fontSize: 14, fontWeight: FontWeight.w500),
                    ),
                  ],
                ),
              ),
            )
          else
            ...pendingList.map((subscriber) {
              return Container(
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  border: Border.all(color: const Color(0xFFF1F5F9)),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: const Color(0xFF3B82F6).withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.storefront, color: Color(0xFF2563EB), size: 20),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            isRtl ? subscriber.nameAr : subscriber.name,
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A)),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            subscriber.email,
                            style: const TextStyle(color: Color(0xFF64748B), fontSize: 12),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                    Row(
                      children: [
                        IconButton(
                          icon: const Icon(Icons.check_circle, color: Color(0xFF10B981), size: 24),
                          tooltip: isRtl ? 'اعتماد التاجر' : 'Approve',
                          onPressed: () {
                            ref.read(subscribersProvider.notifier).approveMerchant(subscriber.id);
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text('تم اعتماد التاجر "${subscriber.nameAr}" بنجاح وتفعيل متجره بالسوق'),
                                backgroundColor: const Color(0xFF10B981),
                                behavior: SnackBarBehavior.floating,
                              ),
                            );
                          },
                        ),
                        IconButton(
                          icon: const Icon(Icons.cancel, color: Color(0xFFEF4444), size: 24),
                          tooltip: isRtl ? 'رفض الطلب' : 'Reject',
                          onPressed: () {
                            ref.read(subscribersProvider.notifier).toggleSuspension(subscriber.id);
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text('تم رفض طلب التاجر "${subscriber.nameAr}"'),
                                backgroundColor: const Color(0xFFEF4444),
                                behavior: SnackBarBehavior.floating,
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
