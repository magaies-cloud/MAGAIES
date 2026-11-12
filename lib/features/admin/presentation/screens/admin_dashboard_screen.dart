import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:go_router/go_router.dart';
import 'package:magaiees_marketplace/routing/route_names.dart';
import 'package:magaiees_marketplace/features/admin/subscribers/application/subscribers_provider.dart';
import 'package:magaiees_marketplace/features/admin/subscribers/domain/models/subscriber_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/features/admin/orders/application/orders_provider.dart';
import 'package:magaiees_marketplace/features/admin/application/ads_provider.dart';
import 'package:magaiees_marketplace/features/admin/finance/application/admin_finance_provider.dart';

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
      ref.read(adminFinanceProvider.notifier).loadFinanceData();
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
    final currencyFmt = NumberFormat.decimalPattern(isRtl ? 'ar' : 'en');
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
                    '${isRtl ? "تفاصيل حركة التوريد والشراء" : "Order Details"} #${order.formattedOrderNumber}',
                    style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0F172A), fontSize: 16),
                  ),
                ],
              ),
              content: SizedBox(
                width: 520,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildDetailRow(isRtl ? 'العميل / المشتري' : 'Customer', order.buyerName ?? order.buyerId),
                    if (order.buyerPhone != null && order.buyerPhone!.isNotEmpty)
                      _buildDetailRow(isRtl ? 'هاتف العميل' : 'Phone', order.buyerPhone!),
                    _buildDetailRow(isRtl ? 'المتجر / المورد' : 'Store / Supplier', order.storeName ?? (isRtl ? 'متجر معتمد' : 'Verified Store')),
                    if (order.staffAssignedName != null)
                      _buildDetailRow(isRtl ? 'الموظف المسؤول' : 'Handled by', order.staffAssignedName!),
                    if (order.transportFee > 0)
                      _buildDetailRow(isRtl ? 'أجور النقل والترحيل' : 'Transport Fee', '${currencyFmt.format(order.transportFee)} ${isRtl ? "ج.س" : "SDG"}'),
                    if (order.offloadingFee > 0)
                      _buildDetailRow(isRtl ? 'أجور العتالة والتفريغ' : 'Offloading Fee', '${currencyFmt.format(order.offloadingFee)} ${isRtl ? "ج.س" : "SDG"}'),
                    _buildDetailRow(
                      isRtl ? 'إجمالي قيمة التوريد' : 'Total Amount',
                      '${currencyFmt.format(order.totalAmount)} ${isRtl ? "ج.س" : "SDG"}',
                      isBold: true,
                    ),
                    if (order.commissionAmount != null && order.commissionAmount! > 0)
                      _buildDetailRow(
                        isRtl ? 'عمولة المنصة' : 'Platform Commission',
                        '${currencyFmt.format(order.commissionAmount!)} ${isRtl ? "ج.س" : "SDG"} (${order.commissionRateApplied ?? 0}%)',
                      ),
                    _buildDetailRow(isRtl ? 'طريقة الدفع' : 'Payment Method', order.paymentMethod ?? (isRtl ? 'تحويل بنكي' : 'Bank Transfer')),
                    _buildDetailRow(isRtl ? 'تاريخ الطلب' : 'Date', DateFormat('yyyy-MM-dd HH:mm').format(order.createdAt.toLocal())),
                    const SizedBox(height: 20),
                    const Divider(color: Color(0xFFE2E8F0)),
                    const SizedBox(height: 12),
                    Text(
                      isRtl ? 'تحديث حالة حركة التوريد:' : 'Update Order Status:',
                      style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0F172A), fontSize: 13),
                    ),
                    const SizedBox(height: 10),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: [
                        _buildStatusOption(context, ctx, ref, order, 'pending', isRtl ? 'قيد المعالجة' : 'Pending', const Color(0xFFF59E0B), setDlgState),
                        _buildStatusOption(context, ctx, ref, order, 'shipped', isRtl ? 'مشحون' : 'Shipped', const Color(0xFF3B82F6), setDlgState),
                        _buildStatusOption(context, ctx, ref, order, 'delivered', isRtl ? 'مكتمل ومسلم' : 'Delivered', const Color(0xFF10B981), setDlgState),
                        _buildStatusOption(context, ctx, ref, order, 'cancelled', isRtl ? 'ملغي' : 'Cancelled', const Color(0xFFEF4444), setDlgState),
                      ],
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
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
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(color: Color(0xFF64748B), fontSize: 13)),
          Text(
            value,
            style: TextStyle(
              fontWeight: isBold ? FontWeight.bold : FontWeight.w600,
              color: isBold ? const Color(0xFF0F172A) : const Color(0xFF334155),
              fontSize: isBold ? 15 : 13,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusOption(
    BuildContext context,
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
      onTap: () async {
        if (!isSelected) {
          try {
            await ref.read(adminOrdersProvider.notifier).updateOrderStatus(order.id, statusKey);
            if (context.mounted) {
              Navigator.pop(ctx);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('تم تحديث حالة الطلب إلى $label'),
                  backgroundColor: color,
                  behavior: SnackBarBehavior.floating,
                ),
              );
            }
          } catch (e) {
            if (context.mounted) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('فشل التحديث: $e'),
                  backgroundColor: Colors.red,
                  behavior: SnackBarBehavior.floating,
                ),
              );
            }
          }
        }
      },
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? color.withValues(alpha: 0.12) : Colors.transparent,
          border: Border.all(color: isSelected ? color : const Color(0xFFCBD5E1)),
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: isSelected ? color : const Color(0xFF475569),
            fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
            fontSize: 12,
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final currencyFmt = NumberFormat.decimalPattern(isRtl ? 'ar' : 'en');

    final allSubscribersAsync = ref.watch(subscribersProvider);
    final allSubscribers = allSubscribersAsync.valueOrNull ?? [];
    
    final productsAsync = ref.watch(productsProvider);
    final allProducts = productsAsync.valueOrNull ?? [];
    final totalAdvertisedProducts = allProducts.length;

    final ordersAsync = ref.watch(adminOrdersProvider);
    final allOrders = ordersAsync.valueOrNull ?? [];
    
    final completedOrders = allOrders.where((o) => o.status == 'مكتمل' || o.status == 'تم التسليم' || o.status.toLowerCase() == 'completed' || o.status.toLowerCase() == 'delivered').toList();
    final cancelledOrders = allOrders.where((o) => o.status == 'ملغي' || o.status == 'ملغى' || o.status.toLowerCase() == 'cancelled').toList();
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

    // ════════════════════════════════════════════════════════════════
    // ADVERTISER PAYMENTS & ACCOUNTING INTEGRATION (دفعات المعلنين)
    // ════════════════════════════════════════════════════════════════
    final adsAsync = ref.watch(adsProvider);
    final allAds = adsAsync.valueOrNull ?? [];
    final activeAds = allAds.where((a) => a.isActive).toList();

    double totalAdsRevenue = 0.0;
    double currentMonthAdsRevenue = 0.0;
    for (var a in allAds) {
      if (a.price > 0) {
        totalAdsRevenue += a.price;
        final startDt = DateTime.tryParse(a.startDate);
        if (startDt != null && startDt.year == now.year && startDt.month == now.month) {
          currentMonthAdsRevenue += a.price;
        }
      }
    }

    final financeAsync = ref.watch(adminFinanceProvider);
    final financeData = financeAsync.valueOrNull;
    final totalPlatformCommission = (financeData?['totalPlatformCommission'] as num?)?.toDouble() ?? 0.0;
    final magaieesNetProfit = (financeData?['magaieesNetProfit'] as num?)?.toDouble() ?? (totalPlatformCommission + totalAdsRevenue);

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
      backgroundColor: const Color(0xFFF8FAFC),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 28.0, vertical: 24.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Top Bar
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
                            fontSize: 24,
                            letterSpacing: -0.5,
                          ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      isRtl ? 'متابعة حية للمبيعات، دفعات المعلنين، المشتركين، والتسعير' : 'Live overview of sales, advertiser payments, subscribers & FX',
                      style: const TextStyle(color: Color(0xFF64748B), fontSize: 13, fontWeight: FontWeight.w500),
                    ),
                  ],
                ),
                Wrap(
                  spacing: 10,
                  runSpacing: 8,
                  crossAxisAlignment: WrapCrossAlignment.center,
                  children: [
                    // Official Platform USD Exchange Rate Interactive Controller
                    Builder(
                      builder: (ctx) {
                        final platformUsd = ref.watch(platformUsdRateSettingProvider);
                        return InkWell(
                          onTap: () => _showUpdatePlatformUsdDialog(context, isRtl, platformUsd),
                          borderRadius: BorderRadius.circular(14),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF0FDF4),
                              borderRadius: BorderRadius.circular(14),
                              border: Border.all(color: const Color(0xFFBBF7D0), width: 1.2),
                              boxShadow: [
                                BoxShadow(
                                  color: const Color(0xFF16A34A).withValues(alpha: 0.08),
                                  blurRadius: 10,
                                  offset: const Offset(0, 3),
                                ),
                              ],
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(6),
                                  decoration: const BoxDecoration(
                                    color: Color(0xFFDCFCE7),
                                    shape: BoxShape.circle,
                                  ),
                                  child: const Icon(Icons.currency_exchange_rounded, color: Color(0xFF16A34A), size: 16),
                                ),
                                const SizedBox(width: 8),
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Text(
                                      isRtl ? 'سعر الدولار المعتمد' : 'Platform USD',
                                      style: const TextStyle(
                                        color: Color(0xFF166534),
                                        fontSize: 10,
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                    Text(
                                      '1 USD = ${NumberFormat('#,##0').format(platformUsd)} ${isRtl ? "ج.س" : "SDG"}',
                                      style: const TextStyle(
                                        color: Color(0xFF15803D),
                                        fontWeight: FontWeight.w900,
                                        fontSize: 13,
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),

                    // Quick Shortcut: Subscribers Management (إدارة المشتركين)
                    InkWell(
                      onTap: () => context.go(RouteNames.adminSubscribers),
                      borderRadius: BorderRadius.circular(14),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                        decoration: BoxDecoration(
                          color: const Color(0xFFEFF6FF),
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: const Color(0xFFBFDBFE), width: 1.2),
                          boxShadow: [
                            BoxShadow(
                              color: const Color(0xFF2563EB).withValues(alpha: 0.08),
                              blurRadius: 10,
                              offset: const Offset(0, 3),
                            ),
                          ],
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Container(
                              padding: const EdgeInsets.all(6),
                              decoration: const BoxDecoration(
                                color: Color(0xFFDBEAFE),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.people_alt_rounded, color: Color(0xFF2563EB), size: 16),
                            ),
                            const SizedBox(width: 8),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text(
                                  isRtl ? 'إدارة المشتركين' : 'Subscribers',
                                  style: const TextStyle(
                                    color: Color(0xFF1E40AF),
                                    fontSize: 10,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                                Text(
                                  '${allSubscribers.length} ${isRtl ? "مشترك" : "Users"}',
                                  style: const TextStyle(
                                    color: Color(0xFF1D4ED8),
                                    fontWeight: FontWeight.w900,
                                    fontSize: 13,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ),

                    // Quick Shortcut: Ads & Campaigns
                    InkWell(
                      onTap: () => context.go(RouteNames.adminAds),
                      borderRadius: BorderRadius.circular(14),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFFF7ED),
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: const Color(0xFFFED7AA), width: 1.2),
                          boxShadow: [
                            BoxShadow(
                              color: const Color(0xFFEA580C).withValues(alpha: 0.08),
                              blurRadius: 10,
                              offset: const Offset(0, 3),
                            ),
                          ],
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Container(
                              padding: const EdgeInsets.all(6),
                              decoration: const BoxDecoration(
                                color: Color(0xFFFFEDD5),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.campaign_rounded, color: Color(0xFFEA580C), size: 16),
                            ),
                            const SizedBox(width: 8),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text(
                                  isRtl ? 'حملات المعلنين' : 'Advertiser Ads',
                                  style: const TextStyle(
                                    color: Color(0xFF9A3412),
                                    fontSize: 10,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                                Text(
                                  '${activeAds.length} ${isRtl ? "حملة نشطة" : "Active"}',
                                  style: const TextStyle(
                                    color: Color(0xFFC2410C),
                                    fontWeight: FontWeight.w900,
                                    fontSize: 13,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ),

                    // Quick Shortcut: Finance & Accounting
                    InkWell(
                      onTap: () => context.go(RouteNames.adminFinance),
                      borderRadius: BorderRadius.circular(14),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFAF5FF),
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: const Color(0xFFE9D5FF), width: 1.2),
                          boxShadow: [
                            BoxShadow(
                              color: const Color(0xFF7C3AED).withValues(alpha: 0.08),
                              blurRadius: 10,
                              offset: const Offset(0, 3),
                            ),
                          ],
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Container(
                              padding: const EdgeInsets.all(6),
                              decoration: const BoxDecoration(
                                color: Color(0xFFF3E8FF),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.account_balance_rounded, color: Color(0xFF7C3AED), size: 16),
                            ),
                            const SizedBox(width: 8),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text(
                                  isRtl ? 'أرباح السيطرة' : 'Net Profit',
                                  style: const TextStyle(
                                    color: Color(0xFF581C87),
                                    fontSize: 10,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                                Text(
                                  '${currencyFmt.format(magaieesNetProfit)} ${isRtl ? "ج.س" : "SDG"}',
                                  style: const TextStyle(
                                    color: Color(0xFF6B21A8),
                                    fontWeight: FontWeight.w900,
                                    fontSize: 13,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
            if (ordersAsync.hasError || allSubscribersAsync.hasError || productsAsync.hasError || adsAsync.hasError || financeAsync.hasError) ...[
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEF3C7),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFF59E0B)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.sync_problem_rounded, color: Color(0xFFD97706), size: 22),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        isRtl
                            ? 'تعذر تحميل بعض البيانات الحية مؤقتاً بسبب مزامنة توقيت الخادم أو اتصال الشبكة.'
                            : 'Some live metrics failed to load due to server clock sync or network.',
                        style: const TextStyle(color: Color(0xFF92400E), fontSize: 13, fontWeight: FontWeight.w600),
                      ),
                    ),
                    TextButton.icon(
                      onPressed: () {
                        ref.invalidate(adminOrdersProvider);
                        ref.invalidate(subscribersProvider);
                        ref.invalidate(productsProvider);
                        ref.invalidate(adsProvider);
                        ref.invalidate(adminFinanceProvider);
                      },
                      icon: const Icon(Icons.refresh_rounded, size: 18, color: Color(0xFF92400E)),
                      label: Text(
                        isRtl ? 'إعادة المحاولة' : 'Retry',
                        style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF92400E)),
                      ),
                    ),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 20),

            // ════════════════════════════════════════════════════════════════
            // 8 KPI STAT CARDS (FINANCIAL ACCOUNTING + ADVERTISERS + OPERATIONS)
            // ════════════════════════════════════════════════════════════════
            LayoutBuilder(
              builder: (context, constraints) {
                final double spacing = 12.0;
                final int crossAxisCount = constraints.maxWidth > 1100 ? 4 : (constraints.maxWidth > 650 ? 2 : 1);
                final double cardWidth = (constraints.maxWidth - (spacing * (crossAxisCount - 1))) / crossAxisCount;

                return Column(
                  children: [
                    // Row 1: Core Financial & Accounting Metrics (GMV, Inflow, Ads, Net Profit)
                    Wrap(
                      spacing: spacing,
                      runSpacing: spacing,
                      children: [
                        _buildCompactStatCard(
                          title: isRtl ? 'مبيعات السوق الكلية' : 'Marketplace GMV',
                          value: '${currencyFmt.format(totalSales)} ${isRtl ? 'ج.س' : 'SDG'}',
                          subtitle: isRtl ? 'إجمالي التوريدات المنفذة' : 'Delivered Sales',
                          icon: Icons.account_balance_wallet_rounded,
                          color: const Color(0xFF059669),
                          width: cardWidth,
                          onTap: () => context.go(RouteNames.adminFinance),
                        ),
                        _buildCompactStatCard(
                          title: isRtl ? 'إيرادات الشهر المجمعة' : 'Monthly Inflow',
                          value: '${currencyFmt.format(currentMonthRevenue + currentMonthAdsRevenue)} ${isRtl ? 'ج.س' : 'SDG'}',
                          subtitle: isRtl ? 'مبيعات + إعلانات' : 'Sales & Ads',
                          icon: Icons.trending_up_rounded,
                          color: const Color(0xFF0D9488),
                          width: cardWidth,
                          onTap: () => context.go(RouteNames.adminFinance),
                        ),
                        _buildCompactStatCard(
                          title: isRtl ? 'دفعات المعلنين' : 'Advertiser Payments',
                          value: '${currencyFmt.format(totalAdsRevenue)} ${isRtl ? 'ج.س' : 'SDG'}',
                          subtitle: isRtl ? '${activeAds.length} حملة نشطة' : '${activeAds.length} Active Ads',
                          icon: Icons.campaign_rounded,
                          color: const Color(0xFFEA580C),
                          width: cardWidth,
                          onTap: () => context.go(RouteNames.adminAds),
                        ),
                        _buildCompactStatCard(
                          title: isRtl ? 'صافي أرباح المنصة' : 'Net Platform Profit',
                          value: '${currencyFmt.format(magaieesNetProfit)} ${isRtl ? 'ج.س' : 'SDG'}',
                          subtitle: isRtl ? 'عمولات + إعلانات' : 'Commission & Ads',
                          icon: Icons.monetization_on_rounded,
                          color: const Color(0xFF7C3AED),
                          width: cardWidth,
                          onTap: () => context.go(RouteNames.adminFinance),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    // Row 2: Operations, Logistics & Growth Metrics
                    Wrap(
                      spacing: spacing,
                      runSpacing: spacing,
                      children: [
                        _buildCompactStatCard(
                          title: isRtl ? 'إجمالي الطلبات' : 'Total Orders',
                          value: '${allOrders.length}',
                          subtitle: isRtl ? '${completedOrders.length} مكتمل' : '${completedOrders.length} Delivered',
                          icon: Icons.shopping_bag_rounded,
                          color: const Color(0xFF2563EB),
                          width: cardWidth,
                          onTap: () => context.go(RouteNames.adminOrders),
                        ),
                        _buildCompactStatCard(
                          title: isRtl ? 'قيد التنفيذ' : 'Orders In Progress',
                          value: '$inProgressOrders',
                          subtitle: isRtl ? 'جاري المعالجة' : 'In Transit',
                          icon: Icons.local_shipping_rounded,
                          color: const Color(0xFFD97706),
                          width: cardWidth,
                          onTap: () => context.go(RouteNames.adminOrders),
                        ),
                        _buildCompactStatCard(
                          title: isRtl ? 'مشتركين جدد' : 'New Subscribers',
                          value: '$newSubscribersCount',
                          subtitle: isRtl ? '${subscriberGrowth >= 0 ? "+" : ""}${subscriberGrowth.toStringAsFixed(1)}% نمو' : '${subscriberGrowth >= 0 ? "+" : ""}${subscriberGrowth.toStringAsFixed(1)}% growth',
                          icon: Icons.person_add_rounded,
                          color: const Color(0xFFDB2777),
                          width: cardWidth,
                          onTap: () => context.go(RouteNames.adminSubscribers),
                        ),
                        _buildCompactStatCard(
                          title: isRtl ? 'المنتجات المعروضة' : 'Listed Products',
                          value: '$totalAdvertisedProducts',
                          subtitle: isRtl ? 'إجمالي بالسوق' : 'Marketplace items',
                          icon: Icons.inventory_2_rounded,
                          color: const Color(0xFF0284C7),
                          width: cardWidth,
                          onTap: () => context.go(RouteNames.adminProducts),
                        ),
                      ],
                    ),
                  ],
                );
              },
            ),
            const SizedBox(height: 24),

            // ════════════════════════════════════════════════════════════════
            // COMBINED HIGH-PERFORMANCE ANALYTICS (REVENUE + GROWTH)
            // ════════════════════════════════════════════════════════════════
            _buildMergedAnalyticsCard(context, isRtl, validOrdersForSales, allSubscribers, allAds),
            const SizedBox(height: 24),

            // ════════════════════════════════════════════════════════════════
            // ADVERTISER PAYMENTS & CAMPAIGNS (دفعات وحملات المعلنين)
            // ════════════════════════════════════════════════════════════════
            _buildAdvertiserCampaignsCard(context, isRtl, allAds),
            const SizedBox(height: 24),

            // ════════════════════════════════════════════════════════════════
            // FULL-WIDTH RECENT SUPPLY & PURCHASE ORDERS TABLE
            // ════════════════════════════════════════════════════════════════
            _buildFullWidthLatestOrders(context, isRtl, allOrders),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  // ════════════════════════════════════════════════════════════════
  // COMPACT TRANSLUCENT STAT CARD (HEIGHT -50%)
  // ════════════════════════════════════════════════════════════════
  Widget _buildCompactStatCard({
    required String title,
    required String value,
    required String subtitle,
    required IconData icon,
    required Color color,
    required double width,
    VoidCallback? onTap,
  }) {
    final cardWidget = Container(
      width: width,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.06),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: color.withValues(alpha: 0.25)),
        boxShadow: [
          BoxShadow(
            color: color.withValues(alpha: 0.04),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(9),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.14),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: color, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Flexible(
                      child: Text(
                        title,
                        style: TextStyle(
                          color: color.withValues(alpha: 0.9),
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    Flexible(
                      child: Text(
                        subtitle,
                        style: TextStyle(
                          color: Colors.grey.shade600,
                          fontSize: 10,
                          fontWeight: FontWeight.w500,
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w900,
                    color: Color(0xFF0F172A),
                    letterSpacing: -0.3,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
          if (onTap != null) ...[
            const SizedBox(width: 4),
            Icon(Icons.arrow_forward_ios, size: 11, color: color.withValues(alpha: 0.45)),
          ],
        ],
      ),
    );

    if (onTap != null) {
      return Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(16),
          child: cardWidget,
        ),
      );
    }
    return cardWidget;
  }

  // ════════════════════════════════════════════════════════════════
  // MERGED ANALYTICS (REVENUE + NEW SUBSCRIBERS + ADS PAYMENTS)
  // ════════════════════════════════════════════════════════════════
  Widget _buildMergedAnalyticsCard(
    BuildContext context,
    bool isRtl,
    List<OrderModel> validOrders,
    List<SubscriberModel> allSubscribers,
    List<AdModel> allAds,
  ) {
    final now = DateTime.now();
    List<String> months = [];
    List<double> monthlyRevenue = [];
    List<int> monthlyNewUsers = [];
    
    double totalRevenue6M = 0;
    int totalNewUsers6M = 0;
    
    for (int i = 5; i >= 0; i--) {
      final monthDate = DateTime(now.year, now.month - i, 1);
      final arMonths = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
      final arMonth = arMonths[monthDate.month - 1];
      months.add(isRtl ? arMonth : DateFormat('MMM').format(monthDate));
      
      double mTotal = 0.0;
      for (var o in validOrders) {
        if (o.createdAt.year == monthDate.year && o.createdAt.month == monthDate.month) {
          mTotal += o.totalAmount;
        }
      }
      for (var a in allAds) {
        if (a.price > 0) {
          final dt = DateTime.tryParse(a.startDate);
          if (dt != null && dt.year == monthDate.year && dt.month == monthDate.month) {
            mTotal += a.price;
          }
        }
      }
      monthlyRevenue.add(mTotal);
      totalRevenue6M += mTotal;

      int uCount = 0;
      for (var s in allSubscribers) {
        if (s.joinedAt.year == monthDate.year && s.joinedAt.month == monthDate.month) {
          uCount++;
        }
      }
      monthlyNewUsers.add(uCount);
      totalNewUsers6M += uCount;
    }
    
    final maxMonthlyRev = monthlyRevenue.isEmpty ? 0.0 : monthlyRevenue.reduce((a, b) => a > b ? a : b);
    final maxMonthlyUsers = monthlyNewUsers.isEmpty ? 0 : monthlyNewUsers.reduce((a, b) => a > b ? a : b);

    return Container(
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.03),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Merged Card Header
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(color: const Color(0xFF1E40AF).withValues(alpha: 0.1), borderRadius: BorderRadius.circular(10)),
                    child: const Icon(Icons.insights_rounded, color: Color(0xFF1E40AF), size: 22),
                  ),
                  const SizedBox(width: 10),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        isRtl ? 'تحليلات الإيرادات ونمو المشتركين المجمعة' : 'Combined Revenue & Growth Analytics',
                        style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: Color(0xFF0F172A)),
                      ),
                      Text(
                        isRtl
                            ? 'إجمالي 6 أشهر: ${NumberFormat.compact(locale: "ar").format(totalRevenue6M)} ج.س • $totalNewUsers6M مشترك جديد'
                            : '6M Total: ${NumberFormat.compact().format(totalRevenue6M)} SDG • $totalNewUsers6M new subscribers',
                        style: const TextStyle(color: Color(0xFF64748B), fontSize: 12),
                      ),
                    ],
                  ),
                ],
              ),
              Row(
                children: [
                  _chartLegendPill(const Color(0xFF2563EB), isRtl ? 'المبيعات ودفعات المعلنين' : 'Revenue & Ads'),
                  const SizedBox(width: 10),
                  _chartLegendPill(const Color(0xFF10B981), isRtl ? 'مشتركين جدد' : 'Subscribers'),
                ],
              ),
            ],
          ),
          const Divider(height: 28),

          // 6-Month Dual Chart Visualization
          SizedBox(
            height: 180,
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.end,
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: List.generate(6, (idx) {
                final rev = monthlyRevenue[idx];
                final users = monthlyNewUsers[idx];
                
                final double revHeight = maxMonthlyRev > 0 ? (rev / maxMonthlyRev) * 110 : 6.0;
                final double userHeight = maxMonthlyUsers > 0 ? (users / maxMonthlyUsers) * 110 : 6.0;

                String revLabel = '';
                if (rev >= 1000000) {
                  revLabel = '${(rev / 1000000).toStringAsFixed(1)}M';
                } else if (rev >= 1000) {
                  revLabel = '${(rev / 1000).toStringAsFixed(0)}K';
                } else {
                  revLabel = rev.toInt().toString();
                }

                return Column(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    Text(revLabel, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF2563EB))),
                    const SizedBox(height: 4),
                    Row(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        // Revenue Bar
                        Container(
                          width: 22,
                          height: revHeight.clamp(6.0, 120.0),
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [Color(0xFF1E40AF), Color(0xFF3B82F6)],
                              begin: Alignment.bottomCenter,
                              end: Alignment.topCenter,
                            ),
                            borderRadius: BorderRadius.circular(6),
                          ),
                        ),
                        const SizedBox(width: 4),
                        // Subscribers Bar
                        Container(
                          width: 16,
                          height: userHeight.clamp(6.0, 120.0),
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [Color(0xFF059669), Color(0xFF34D399)],
                              begin: Alignment.bottomCenter,
                              end: Alignment.topCenter,
                            ),
                            borderRadius: BorderRadius.circular(5),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Text(months[idx], style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF334155))),
                    Text('$users ${isRtl ? "مشترك" : "users"}', style: TextStyle(fontSize: 9, color: Colors.grey.shade500)),
                  ],
                );
              }),
            ),
          ),
        ],
      ),
    );
  }

  Widget _chartLegendPill(Color c, String label) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: c.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: c.withValues(alpha: 0.2)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(width: 8, height: 8, decoration: BoxDecoration(color: c, shape: BoxShape.circle)),
          const SizedBox(width: 6),
          Text(label, style: TextStyle(color: c, fontSize: 11, fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }

  // ════════════════════════════════════════════════════════════════
  // RECENT ADVERTISER CAMPAIGNS & PAYMENTS TABLE (دفعات وحملات المعلنين)
  // ════════════════════════════════════════════════════════════════
  Widget _buildAdvertiserCampaignsCard(BuildContext context, bool isRtl, List<AdModel> ads) {
    final currencyFmt = NumberFormat.decimalPattern(isRtl ? 'ar' : 'en');
    final recentAds = ads.take(10).toList();
    final activeCount = ads.where((a) => a.isActive).length;
    double totalRevenue = 0.0;
    for (var a in ads) {
      if (a.price > 0) totalRevenue += a.price;
    }

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.03),
            blurRadius: 14,
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
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEA580C).withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.campaign_rounded, color: Color(0xFFEA580C), size: 22),
                  ),
                  const SizedBox(width: 10),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Text(
                            isRtl ? 'حملات ودفعات المعلنين' : 'Advertiser Campaigns & Payments',
                            style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: Color(0xFF0F172A)),
                          ),
                          const SizedBox(width: 10),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                            decoration: BoxDecoration(
                              color: const Color(0xFFEA580C).withValues(alpha: 0.1),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              '$activeCount ${isRtl ? "نشط" : "Active"}',
                              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFFEA580C)),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 2),
                      Text(
                        isRtl
                            ? 'إجمالي إيرادات المعلنين: ${currencyFmt.format(totalRevenue)} ${isRtl ? "ج.س" : "SDG"} • متابعة مباشرة لمدفوعات الحملات والشركات'
                            : 'Total Ad Revenue: ${currencyFmt.format(totalRevenue)} SDG • Live campaign payments and sponsorships',
                        style: const TextStyle(color: Color(0xFF64748B), fontSize: 12),
                      ),
                    ],
                  ),
                ],
              ),
              FilledButton.tonalIcon(
                onPressed: () => context.go(RouteNames.adminAds),
                icon: Icon(isRtl ? Icons.arrow_back : Icons.arrow_forward, size: 16),
                label: Text(isRtl ? 'إدارة كافة الإعلانات' : 'Manage All Ads'),
                style: FilledButton.styleFrom(
                  backgroundColor: const Color(0xFFEA580C).withValues(alpha: 0.08),
                  foregroundColor: const Color(0xFFEA580C),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
            ],
          ),
          const Divider(height: 24),

          recentAds.isEmpty
              ? Padding(
                  padding: const EdgeInsets.all(32),
                  child: Center(
                    child: Column(
                      children: [
                        Icon(Icons.campaign_outlined, size: 40, color: Colors.grey.shade400),
                        const SizedBox(height: 8),
                        Text(
                          isRtl ? 'لا توجد حملات إعلانية مسجلة حتى الآن' : 'No ad campaigns registered yet',
                          style: TextStyle(color: Colors.grey.shade500),
                        ),
                        const SizedBox(height: 12),
                        OutlinedButton.icon(
                          onPressed: () => context.go(RouteNames.adminAds),
                          icon: const Icon(Icons.add_rounded, size: 16),
                          label: Text(isRtl ? 'إطلاق أول حملة إعلانية' : 'Launch First Campaign'),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: const Color(0xFFEA580C),
                            side: const BorderSide(color: Color(0xFFEA580C)),
                          ),
                        ),
                      ],
                    ),
                  ),
                )
              : SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: SizedBox(
                    width: 1050,
                    child: DataTable(
                      headingRowColor: WidgetStateProperty.all(const Color(0xFFF8FAFC)),
                      horizontalMargin: 12,
                      columnSpacing: 16,
                      headingTextStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Color(0xFF475569)),
                      dataRowColor: WidgetStateProperty.resolveWith<Color>((states) => Colors.white),
                      columns: [
                        DataColumn(label: Text(isRtl ? 'اسم المعلن / الشركة' : 'Advertiser / Company')),
                        DataColumn(label: Text(isRtl ? 'عنوان الحملة' : 'Campaign Title')),
                        DataColumn(label: Text(isRtl ? 'المبلغ المدفوع' : 'Paid Amount')),
                        DataColumn(label: Text(isRtl ? 'الوسم الترويجي' : 'Target Tag')),
                        DataColumn(label: Text(isRtl ? 'فترة الحملة' : 'Duration')),
                        DataColumn(label: Text(isRtl ? 'الحالة' : 'Status')),
                        DataColumn(label: Text(isRtl ? 'إجراء' : 'Action')),
                      ],
                      rows: recentAds.map((ad) {
                        final isActive = ad.isActive;
                        final company = ad.company.isNotEmpty ? ad.company : (isRtl ? 'معلن غير محدد' : 'Unknown');
                        final title = ad.title.isNotEmpty ? ad.title : '—';
                        final target = ad.target.isNotEmpty ? ad.target : '—';
                        final dates = '${ad.startDate.isNotEmpty ? ad.startDate : '—'} → ${ad.endDate.isNotEmpty ? ad.endDate : '—'}';

                        return DataRow(
                          cells: [
                            DataCell(
                              Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  CircleAvatar(
                                    radius: 14,
                                    backgroundColor: const Color(0xFFEA580C).withValues(alpha: 0.12),
                                    child: Text(
                                      company.isNotEmpty ? company.characters.first : 'A',
                                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFFEA580C)),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Text(
                                    company,
                                    style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 12, color: Color(0xFF0F172A)),
                                  ),
                                ],
                              ),
                            ),
                            DataCell(
                              Text(
                                title,
                                style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 12),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            DataCell(
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: const Color(0xFF059669).withValues(alpha: 0.08),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  '${currencyFmt.format(ad.price)} ${isRtl ? "ج.س" : "SDG"}',
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Color(0xFF059669)),
                                ),
                              ),
                            ),
                            DataCell(
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(
                                  color: Colors.grey.shade100,
                                  borderRadius: BorderRadius.circular(6),
                                  border: Border.all(color: Colors.grey.shade300),
                                ),
                                child: Text(
                                  target,
                                  style: TextStyle(fontSize: 11, color: Colors.grey.shade700),
                                ),
                              ),
                            ),
                            DataCell(
                              Text(
                                dates,
                                style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                              ),
                            ),
                            DataCell(
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: isActive
                                      ? const Color(0xFF10B981).withValues(alpha: 0.12)
                                      : Colors.grey.shade200,
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Text(
                                  isActive ? (isRtl ? 'نشط 🟢' : 'Active 🟢') : (isRtl ? 'متوقف ⚪' : 'Ended ⚪'),
                                  style: TextStyle(
                                    color: isActive ? const Color(0xFF10B981) : Colors.grey.shade700,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 11,
                                  ),
                                ),
                              ),
                            ),
                            DataCell(
                              IconButton(
                                icon: const Icon(Icons.open_in_new_rounded, size: 18, color: Color(0xFFEA580C)),
                                onPressed: () => context.go(RouteNames.adminAds),
                                tooltip: isRtl ? 'عرض الحملة وتعديلها في الإعلانات' : 'Open in Ads Manager',
                              ),
                            ),
                          ],
                        );
                      }).toList(),
                    ),
                  ),
                ),
        ],
      ),
    );
  }

  // ════════════════════════════════════════════════════════════════
  // FULL-WIDTH RECENT SUPPLY & PURCHASE ORDERS TABLE
  // ════════════════════════════════════════════════════════════════
  Widget _buildFullWidthLatestOrders(BuildContext context, bool isRtl, List<OrderModel> orders) {
    final currencyFmt = NumberFormat.decimalPattern(isRtl ? 'ar' : 'en');
    final recentOrders = orders.take(12).toList();

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.03),
            blurRadius: 14,
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
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(color: const Color(0xFF059669).withValues(alpha: 0.1), borderRadius: BorderRadius.circular(10)),
                    child: const Icon(Icons.shopping_bag_rounded, color: Color(0xFF059669), size: 22),
                  ),
                  const SizedBox(width: 10),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        isRtl ? 'أحدث حركات التوريد والشراء بالسوق' : 'Recent Supply & Purchasing Orders',
                        style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: Color(0xFF0F172A)),
                      ),
                      Text(
                        isRtl ? 'عرض مباشر لكافة الطلبات، التوريدات، الموردين، وحالات الدفع' : 'Live marketplace orders, suppliers, and payment status',
                        style: const TextStyle(color: Color(0xFF64748B), fontSize: 12),
                      ),
                    ],
                  ),
                ],
              ),
              FilledButton.tonalIcon(
                onPressed: () => context.go(RouteNames.adminOrders),
                icon: Icon(isRtl ? Icons.arrow_back : Icons.arrow_forward, size: 16),
                label: Text(isRtl ? 'عرض كافة الطلبات' : 'View All Orders'),
                style: FilledButton.styleFrom(
                  backgroundColor: const Color(0xFF1E40AF).withValues(alpha: 0.08),
                  foregroundColor: const Color(0xFF1E40AF),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
            ],
          ),
          const Divider(height: 24),

          recentOrders.isEmpty
              ? Padding(
                  padding: const EdgeInsets.all(32),
                  child: Center(
                    child: Column(
                      children: [
                        Icon(Icons.shopping_cart_outlined, size: 40, color: Colors.grey.shade400),
                        const SizedBox(height: 8),
                        Text(isRtl ? 'لا توجد طلبات توريد حالياً' : 'No orders found', style: TextStyle(color: Colors.grey.shade500)),
                      ],
                    ),
                  ),
                )
              : SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: SizedBox(
                    width: 1050,
                    child: DataTable(
                      headingRowColor: WidgetStateProperty.all(const Color(0xFFF8FAFC)),
                      horizontalMargin: 12,
                      columnSpacing: 16,
                      headingTextStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Color(0xFF475569)),
                      dataRowColor: WidgetStateProperty.resolveWith<Color>((states) {
                        return Colors.white;
                      }),
                      columns: [
                        DataColumn(label: Text(isRtl ? 'رقم الطلب' : 'Order ID')),
                        DataColumn(label: Text(isRtl ? 'المشتري / العميل' : 'Customer')),
                        DataColumn(label: Text(isRtl ? 'المتجر / المورد' : 'Supplier / Store')),
                        DataColumn(label: Text(isRtl ? 'إجمالي التوريد' : 'Total Amount')),
                        DataColumn(label: Text(isRtl ? 'طريقة الدفع' : 'Payment Method')),
                        DataColumn(label: Text(isRtl ? 'الحالة' : 'Status')),
                        DataColumn(label: Text(isRtl ? 'التاريخ' : 'Date')),
                        DataColumn(label: Text(isRtl ? 'إجراء' : 'Action')),
                      ],
                      rows: recentOrders.map((order) {
                        final shortId = order.formattedOrderNumber;
                        final statusColor = _getStatusColor(order.status);
                        final statusText = _getStatusText(order.status, isRtl);
                        final dateStr = DateFormat('yyyy-MM-dd HH:mm').format(order.createdAt.toLocal());
                        final buyer = order.buyerName ?? order.buyerId;
                        final store = order.storeName ?? (isRtl ? 'متجر معتمد' : 'Verified Store');
                        final method = order.paymentMethod ?? (isRtl ? 'تحويل بنكي' : 'Bank Transfer');

                        return DataRow(
                          cells: [
                            DataCell(
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: const Color(0xFF1E40AF).withValues(alpha: 0.08),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  '#$shortId',
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF1E40AF)),
                                ),
                              ),
                            ),
                            DataCell(
                              Text(buyer, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 12)),
                            ),
                            DataCell(
                              Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(Icons.storefront, size: 14, color: Colors.blueGrey),
                                  const SizedBox(width: 4),
                                  Text(store, style: const TextStyle(fontSize: 12, color: Color(0xFF334155))),
                                ],
                              ),
                            ),
                            DataCell(
                              Text(
                                '${currencyFmt.format(order.totalAmount)} ${isRtl ? "ج.س" : "SDG"}',
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Color(0xFF0F172A)),
                              ),
                            ),
                            DataCell(
                              Text(method, style: TextStyle(fontSize: 11, color: Colors.grey.shade700)),
                            ),
                            DataCell(
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: statusColor.withValues(alpha: 0.12),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Text(
                                  statusText,
                                  style: TextStyle(color: statusColor, fontWeight: FontWeight.bold, fontSize: 11),
                                ),
                              ),
                            ),
                            DataCell(
                              Text(dateStr, style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                            ),
                            DataCell(
                              IconButton(
                                icon: const Icon(Icons.visibility_outlined, size: 18, color: Color(0xFF1E40AF)),
                                onPressed: () => _showOrderDetails(context, ref, order, isRtl),
                                tooltip: isRtl ? 'عرض التفاصيل وتعديل الحالة' : 'View Details',
                              ),
                            ),
                          ],
                        );
                      }).toList(),
                    ),
                  ),
                ),
        ],
      ),
    );
  }

  Future<void> _showUpdatePlatformUsdDialog(BuildContext context, bool isRtl, double currentRate) async {
    final rateCtrl = TextEditingController(text: currentRate.toString());
    await showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            const Icon(Icons.currency_exchange_rounded, color: Color(0xFF16A34A), size: 26),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                isRtl ? 'سعر صرف الدولار المعتمد للمنصة' : 'Official Platform USD Exchange Rate',
                style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              isRtl
                  ? 'هذا السعر هو السعر المرجعي المعتمد لكافة المتاجر والمنتجات المرتبطة بسعر صرف منصة السيطرة. سيتم تطبيق التحديث وتعميمه فوراً.'
                  : 'This is the standard exchange rate for all stores synced with Magaiees Platform. Updates broadcast immediately.',
              style: TextStyle(fontSize: 13, color: Colors.grey.shade600, height: 1.4),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: rateCtrl,
              keyboardType: const TextInputType.numberWithOptions(decimal: true),
              autofocus: true,
              decoration: InputDecoration(
                labelText: isRtl ? 'سعر الدولار المعتمد (ج.س)' : 'Platform USD Rate (SDG)',
                suffixText: isRtl ? 'ج.س' : 'SDG',
                prefixIcon: const Icon(Icons.attach_money_rounded, color: Color(0xFF16A34A)),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: Text(isRtl ? 'إلغاء' : 'Cancel'),
          ),
          FilledButton.icon(
            style: FilledButton.styleFrom(
              backgroundColor: const Color(0xFF16A34A),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            icon: const Icon(Icons.check_circle_rounded, size: 18),
            label: Text(isRtl ? 'حفظ وتحديث للمنصة' : 'Save & Broadcast'),
            onPressed: () async {
              final newRate = double.tryParse(rateCtrl.text.trim());
              if (newRate != null && newRate > 0) {
                Navigator.pop(ctx);
                await ref.read(platformUsdRateSettingProvider.notifier).updateRate(newRate);
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(
                        isRtl
                            ? 'تم تحديث سعر صرف الدولار المعتمد للمنصة إلى: ${NumberFormat('#,##0').format(newRate)} ج.س'
                            : 'Platform USD exchange rate updated to: ${NumberFormat('#,##0').format(newRate)} SDG',
                      ),
                      backgroundColor: const Color(0xFF16A34A),
                      duration: const Duration(seconds: 2),
                      behavior: SnackBarBehavior.floating,
                    ),
                  );
                }
              }
            },
          ),
        ],
      ),
    );
    rateCtrl.dispose();
  }
}
