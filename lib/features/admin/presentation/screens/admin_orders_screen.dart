import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/features/admin/orders/application/orders_provider.dart';
import 'package:magaiees_marketplace/features/admin/presentation/widgets/admin_error_view.dart';

class AdminOrdersScreen extends ConsumerStatefulWidget {
  const AdminOrdersScreen({super.key});

  @override
  ConsumerState<AdminOrdersScreen> createState() => _AdminOrdersScreenState();
}

class _AdminOrdersScreenState extends ConsumerState<AdminOrdersScreen> with SingleTickerProviderStateMixin {
  bool get isDark => Theme.of(context).brightness == Brightness.dark;
  String _selectedFilter = 'all';
  String _searchQuery = '';
  final TextEditingController _searchCtrl = TextEditingController();
  bool _isTableView = true;

  late AnimationController _animController;
  late Animation<double> _fadeAnimation;

  @override
  void initState() {
    super.initState();
    _animController = AnimationController(vsync: this, duration: const Duration(milliseconds: 800));
    _fadeAnimation = CurvedAnimation(parent: _animController, curve: Curves.easeOut);
    _animController.forward();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    _animController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final ordersAsync = ref.watch(adminOrdersProvider);
    final insightsAsync = ref.watch(adminOrdersInsightsProvider);

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
      body: SafeArea(
        child: FadeTransition(
          opacity: _fadeAnimation,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _buildHeader(isRtl),
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(24.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Reports Dashboard
                      _buildReportsDashboard(insightsAsync, isRtl),
                      const SizedBox(height: 32),
                      
                      // Orders Section Header
                      Text(
                        isRtl ? 'سجل الطلبات وإدارتها' : 'Orders Management & Log',
                        style: TextStyle(
                          fontSize: 22,
                          fontWeight: FontWeight.bold,
                          color: isDark ? Colors.white : Colors.black87,
                        ),
                      ),
                      const SizedBox(height: 16),
                      
                      // Filters & Search
                      _buildFilters(isRtl, ordersAsync.valueOrNull ?? []),
                      const SizedBox(height: 24),
                      
                      // Orders List
                      _buildOrdersList(ordersAsync, isRtl),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader(bool isRtl) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: Colors.blue.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.inventory_2_rounded, color: Colors.blue, size: 28),
              ),
              const SizedBox(width: 16),
              Text(
                isRtl ? 'إدارة الطلبات والتقارير' : 'Orders & Reports',
                style: TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  color: isDark ? Colors.white : const Color(0xFF0F172A),
                ),
              ),
            ],
          ),
          IconButton(
            onPressed: () => ref.read(adminOrdersProvider.notifier).loadOrders(),
            icon: const Icon(Icons.refresh),
            tooltip: isRtl ? 'تحديث البيانات' : 'Refresh Data',
          ),
        ],
      ),
    );
  }

  Widget _buildReportsDashboard(AsyncValue<Map<String, dynamic>> insightsAsync, bool isRtl) {
    final currencyFmt = NumberFormat('#,##0.##', 'en');
    return insightsAsync.when(
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (e, st) => const SizedBox(),
      data: (insights) {
        if (insights.isEmpty) return const SizedBox();

        final totalSalesVolume = (insights['totalSalesVolume'] as num?)?.toDouble() ?? 0.0;
        final totalPaid = (insights['totalPaid'] as num?)?.toDouble() ?? 0.0;
        final totalReceivables = (insights['totalReceivables'] as num?)?.toDouble() ?? 0.0;
        final totalProfit = (insights['totalProfit'] as num?)?.toDouble() ?? 0.0;
        final totalOrders = (insights['totalOrders'] as num?)?.toInt() ?? 0;
        final advertiserOrdersCount = (insights['advertiserOrdersCount'] as num?)?.toInt() ?? 0;
        final advertiserOrdersVolume = (insights['advertiserOrdersVolume'] as num?)?.toDouble() ?? 0.0;
        final collectionRate = (insights['collectionRate'] as num?)?.toDouble() ?? 0.0;
        final unpaidCount = (insights['unpaidCount'] as num?)?.toInt() ?? 0;
        final partialCount = (insights['partialCount'] as num?)?.toInt() ?? 0;
        final topBuyer = insights['topBuyer'] as Map<String, dynamic>?;

        return LayoutBuilder(
          builder: (context, constraints) {
            final isDesktop = constraints.maxWidth > 1024;
            final isTablet = constraints.maxWidth > 650 && constraints.maxWidth <= 1024;

            final cardSales = _buildInsightCard(
              title: isRtl ? 'حجم المبيعات الكلي' : 'Gross Sales Volume',
              value: '${currencyFmt.format(totalSalesVolume)} ج.س',
              subtitle: isRtl ? 'إجمالي $totalOrders طلب عبر المنصة' : 'Total $totalOrders orders on platform',
              icon: Icons.point_of_sale_rounded,
              color: const Color(0xFF2563EB),
              isRtl: isRtl,
            );

            final cardCollected = _buildInsightCard(
              title: isRtl ? 'المحصل الفعلي (نقدياً)' : 'Collected Revenue',
              value: '${currencyFmt.format(totalPaid)} ج.س',
              subtitle: isRtl ? 'نسبة التحصيل: ${collectionRate.toStringAsFixed(1)}%' : 'Collection Rate: ${collectionRate.toStringAsFixed(1)}%',
              icon: Icons.check_circle_rounded,
              color: const Color(0xFF059669),
              isRtl: isRtl,
            );

            final cardReceivables = _buildInsightCard(
              title: isRtl ? 'متبقيات للتحصيل' : 'Outstanding Receivables',
              value: '${currencyFmt.format(totalReceivables)} ج.س',
              subtitle: isRtl ? 'ذمم ${unpaidCount + partialCount} طلب غير مكتمل السداد' : 'From ${unpaidCount + partialCount} unpaid orders',
              icon: Icons.pending_actions_rounded,
              color: const Color(0xFFD97706),
              isRtl: isRtl,
            );

            final cardAdvertisers = _buildInsightCard(
              title: isRtl ? 'طلبات المتاجر المعلنة' : 'Advertiser Store Orders',
              value: '$advertiserOrdersCount طلب',
              subtitle: isRtl ? 'بقيمة ${currencyFmt.format(advertiserOrdersVolume)} ج.س' : 'Value: ${currencyFmt.format(advertiserOrdersVolume)} SDG',
              icon: Icons.campaign_rounded,
              color: const Color(0xFF7C3AED),
              isRtl: isRtl,
            );

            final cardProfit = _buildInsightCard(
              title: isRtl ? 'أرباح وعمولات المنصة' : 'Platform Profit',
              value: '${currencyFmt.format(totalProfit)} ج.س',
              subtitle: isRtl ? 'صافي عمولات المبيعات' : 'Net sales commission',
              icon: Icons.trending_up_rounded,
              color: const Color(0xFF0D9488),
              isRtl: isRtl,
            );

            final cardTopBuyer = _buildTopBuyerCard(topBuyer, isRtl, currencyFmt);

            if (isDesktop) {
              return Column(
                children: [
                  Row(
                    children: [
                      Expanded(child: cardSales),
                      const SizedBox(width: 12),
                      Expanded(child: cardCollected),
                      const SizedBox(width: 12),
                      Expanded(child: cardReceivables),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(child: cardAdvertisers),
                      const SizedBox(width: 12),
                      Expanded(child: cardProfit),
                      const SizedBox(width: 12),
                      Expanded(child: cardTopBuyer),
                    ],
                  ),
                ],
              );
            } else if (isTablet) {
              return Column(
                children: [
                  Row(
                    children: [
                      Expanded(child: cardSales),
                      const SizedBox(width: 12),
                      Expanded(child: cardCollected),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(child: cardReceivables),
                      const SizedBox(width: 12),
                      Expanded(child: cardAdvertisers),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(child: cardProfit),
                      const SizedBox(width: 12),
                      Expanded(child: cardTopBuyer),
                    ],
                  ),
                ],
              );
            } else {
              return Column(
                children: [
                  cardSales,
                  const SizedBox(height: 10),
                  cardCollected,
                  const SizedBox(height: 10),
                  cardReceivables,
                  const SizedBox(height: 10),
                  cardAdvertisers,
                  const SizedBox(height: 10),
                  cardProfit,
                  const SizedBox(height: 10),
                  cardTopBuyer,
                ],
              );
            }
          },
        );
      },
    );
  }

  Widget _buildInsightCard({
    required String title,
    required String value,
    required String subtitle,
    required IconData icon,
    required Color color,
    required bool isRtl,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: color.withValues(alpha: 0.25), width: 1.2),
        boxShadow: [
          BoxShadow(
            color: color.withValues(alpha: 0.05),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(icon, color: color, size: 18),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  title,
                  style: TextStyle(
                    color: isDark ? Colors.white70 : const Color(0xFF64748B),
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          SizedBox(
            height: 24,
            child: FittedBox(
              fit: BoxFit.scaleDown,
              alignment: isRtl ? Alignment.centerRight : Alignment.centerLeft,
              child: Text(
                value,
                style: TextStyle(
                  color: isDark ? Colors.white : const Color(0xFF0F172A),
                  fontSize: 17,
                  fontWeight: FontWeight.w900,
                  letterSpacing: -0.3,
                ),
              ),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            subtitle,
            style: TextStyle(
              color: isDark ? Colors.white54 : const Color(0xFF94A3B8),
              fontSize: 11,
              fontWeight: FontWeight.w600,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  Widget _buildTopBuyerCard(Map<String, dynamic>? topBuyer, bool isRtl, NumberFormat currencyFmt) {
    final spentNum = (topBuyer?['totalSpent'] as num?)?.toDouble() ?? 0.0;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [Colors.purple.shade700, Colors.deepPurple.shade900],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(14),
        boxShadow: [
          BoxShadow(
            color: Colors.purple.withValues(alpha: 0.2),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.18),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(Icons.workspace_premium, color: Colors.amber, size: 18),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  isRtl ? 'أفضل عميل بالمنصة' : 'Top Buyer',
                  style: const TextStyle(color: Colors.white70, fontSize: 12, fontWeight: FontWeight.w700),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          if (topBuyer == null)
            Text(isRtl ? 'لا توجد بيانات' : 'No data', style: const TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.bold))
          else ...[
            SizedBox(
              height: 24,
              child: FittedBox(
                fit: BoxFit.scaleDown,
                alignment: isRtl ? Alignment.centerRight : Alignment.centerLeft,
                child: Text(
                  topBuyer['name'] ?? (isRtl ? 'غير معروف' : 'Unknown'),
                  style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w900),
                ),
              ),
            ),
            const SizedBox(height: 4),
            Text(
              '${isRtl ? "أنفق:" : "Spent:"} ${currencyFmt.format(spentNum)} ج.س',
              style: const TextStyle(color: Colors.amberAccent, fontSize: 11, fontWeight: FontWeight.w800),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ]
        ],
      ),
    );
  }

  Widget _buildFilters(bool isRtl, List<OrderModel> orders) {
    final allCount = orders.length;
    final exclusiveCount = orders.where((o) => o.isExclusive).length;
    final financialReviewCount = orders.where((o) =>
      o.paymentStatus == 'under_review' ||
      (o.receiptUrl != null && o.receiptUrl!.trim().isNotEmpty && !o.isFullyPaid) ||
      (o.status.toLowerCase() == 'pending' && !o.isFullyPaid)
    ).length;
    final advertiserCount = orders.where((o) => o.isStoreAdvertiser).length;

    final processingCount = orders.where((o) => o.status.toLowerCase() == 'processing').length;
    final shippedCount = orders.where((o) => o.status.toLowerCase() == 'shipped').length;
    final deliveredCount = orders.where((o) => o.status.toLowerCase() == 'delivered' || o.status.toLowerCase() == 'completed').length;
    final cancelledCount = orders.where((o) => o.status.toLowerCase() == 'cancelled').length;
    final paidCount = orders.where((o) => o.isFullyPaid).length;
    final unpaidCount = orders.where((o) => !o.isFullyPaid).length;

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1),
          width: 1.2,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Row 1: Search Field + Primary High-Priority Filters (Proximity Grouping)
          LayoutBuilder(
            builder: (context, constraints) {
              final isWide = constraints.maxWidth > 950;
              final searchWidget = SizedBox(
                width: isWide ? 310 : double.infinity,
                height: 42,
                child: TextField(
                  controller: _searchCtrl,
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w800,
                    color: isDark ? Colors.white : const Color(0xFF0F172A),
                  ),
                  decoration: InputDecoration(
                    hintText: isRtl ? 'ابحث بالطلب، المتجر، العميل...' : 'Search order #, store, buyer...',
                    hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                    prefixIcon: const Icon(Icons.search_rounded, color: Color(0xFF2563EB), size: 19),
                    suffixIcon: _searchCtrl.text.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.close_rounded, size: 16),
                            onPressed: () {
                              _searchCtrl.clear();
                              setState(() => _searchQuery = '');
                            },
                          )
                        : null,
                    filled: true,
                    fillColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 0),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: BorderSide(color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1), width: 1.2),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: BorderSide(color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1), width: 1.2),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: const BorderSide(color: Color(0xFF2563EB), width: 1.5),
                    ),
                  ),
                  onChanged: (val) => setState(() => _searchQuery = val),
                ),
              );

              final primaryTabs = [
                {
                  'key': 'all',
                  'label': isRtl ? 'الكل' : 'All',
                  'count': allCount,
                  'icon': Icons.apps_rounded,
                  'color': const Color(0xFF2563EB),
                },
                {
                  'key': 'exclusive',
                  'label': isRtl ? '💎 طلبات مقاييس' : '💎 Magaiees Orders',
                  'count': exclusiveCount,
                  'icon': Icons.verified_rounded,
                  'color': const Color(0xFF0F2E4A),
                },
                {
                  'key': 'financial_review',
                  'label': isRtl ? '📑 قيد المراجعة المالية' : '📑 Financial Review',
                  'count': financialReviewCount,
                  'icon': Icons.pending_actions_rounded,
                  'color': const Color(0xFFD97706),
                  'isAlert': financialReviewCount > 0,
                },
                {
                  'key': 'advertisers',
                  'label': isRtl ? '📢 طلبات المعلنين' : '📢 Advertisers',
                  'count': advertiserCount,
                  'icon': Icons.campaign_rounded,
                  'color': const Color(0xFF7C3AED),
                },
              ];

              final primaryTabsWidget = Wrap(
                spacing: 8,
                runSpacing: 8,
                crossAxisAlignment: WrapCrossAlignment.center,
                children: primaryTabs.map((tab) {
                  final key = tab['key'] as String;
                  final label = tab['label'] as String;
                  final count = tab['count'] as int;
                  final icon = tab['icon'] as IconData;
                  final color = tab['color'] as Color;
                  final isSelected = _selectedFilter == key;
                  final isAlert = (tab['isAlert'] as bool?) ?? false;

                  return InkWell(
                    onTap: () => setState(() => _selectedFilter = key),
                    borderRadius: BorderRadius.circular(10),
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 180),
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
                      decoration: BoxDecoration(
                        color: isSelected
                            ? color
                            : (isAlert
                                ? (isDark ? const Color(0xFF78350F).withValues(alpha: 0.3) : const Color(0xFFFEF3C7))
                                : (isDark ? const Color(0xFF0F172A) : const Color(0xFFF1F5F9))),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(
                          color: isSelected
                              ? color
                              : (isAlert
                                  ? const Color(0xFFF59E0B)
                                  : (isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1))),
                          width: isSelected || isAlert ? 1.4 : 1.0,
                        ),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            icon,
                            size: 15,
                            color: isSelected
                                ? Colors.white
                                : (isAlert ? const Color(0xFFD97706) : (isDark ? Colors.white70 : const Color(0xFF334155))),
                          ),
                          const SizedBox(width: 6),
                          Text(
                            label,
                            style: TextStyle(
                              color: isSelected
                                  ? Colors.white
                                  : (isAlert ? (isDark ? Colors.amber.shade200 : const Color(0xFF92400E)) : (isDark ? Colors.white : const Color(0xFF0F172A))),
                              fontSize: 12,
                              fontWeight: isSelected ? FontWeight.w900 : FontWeight.w700,
                            ),
                          ),
                          const SizedBox(width: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: isSelected
                                  ? Colors.white.withValues(alpha: 0.25)
                                  : (isAlert ? const Color(0xFFFDE68A) : (isDark ? Colors.white12 : const Color(0xFFE2E8F0))),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              '$count',
                              style: TextStyle(
                                color: isSelected
                                    ? Colors.white
                                    : (isAlert ? const Color(0xFF78350F) : (isDark ? Colors.white70 : const Color(0xFF475569))),
                                fontSize: 11,
                                fontWeight: FontWeight.w900,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                }).toList(),
              );

              if (isWide) {
                return Row(
                  children: [
                    searchWidget,
                    const SizedBox(width: 12),
                    Expanded(child: primaryTabsWidget),
                    if (_selectedFilter != 'all' || _searchQuery.isNotEmpty) ...[
                      const SizedBox(width: 8),
                      TextButton.icon(
                        style: TextButton.styleFrom(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                          foregroundColor: const Color(0xFFEF4444),
                        ),
                        onPressed: () {
                          _searchCtrl.clear();
                          setState(() {
                            _searchQuery = '';
                            _selectedFilter = 'all';
                          });
                        },
                        icon: const Icon(Icons.filter_alt_off_rounded, size: 16),
                        label: Text(
                          isRtl ? 'إعادة ضبط' : 'Reset',
                          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ],
                  ],
                );
              } else {
                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    searchWidget,
                    const SizedBox(height: 10),
                    primaryTabsWidget,
                    if (_selectedFilter != 'all' || _searchQuery.isNotEmpty) ...[
                      const SizedBox(height: 8),
                      Align(
                        alignment: isRtl ? Alignment.centerLeft : Alignment.centerRight,
                        child: TextButton.icon(
                          style: TextButton.styleFrom(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            foregroundColor: const Color(0xFFEF4444),
                          ),
                          onPressed: () {
                            _searchCtrl.clear();
                            setState(() {
                              _searchQuery = '';
                              _selectedFilter = 'all';
                            });
                          },
                          icon: const Icon(Icons.filter_alt_off_rounded, size: 15),
                          label: Text(
                            isRtl ? 'إعادة ضبط الفلاتر' : 'Reset Filters',
                            style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold),
                          ),
                        ),
                      ),
                    ],
                  ],
                );
              }
            },
          ),
          const SizedBox(height: 12),
          Divider(height: 1, color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
          const SizedBox(height: 10),

          // Row 2: Secondary Status Filters (Payment & Operational Status)
          Wrap(
            spacing: 8,
            runSpacing: 8,
            crossAxisAlignment: WrapCrossAlignment.center,
            children: [
              Text(
                isRtl ? 'السداد:' : 'Payment:',
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                  color: isDark ? Colors.white54 : const Color(0xFF64748B),
                ),
              ),
              _buildSecondaryFilterChip(
                key: 'paid',
                label: isRtl ? '✓ مسدد بالكامل' : '✓ Paid',
                count: paidCount,
                activeColor: const Color(0xFF059669),
              ),
              _buildSecondaryFilterChip(
                key: 'unpaid',
                label: isRtl ? '⏳ غير مسدد / متبقي' : '⏳ Unpaid',
                count: unpaidCount,
                activeColor: const Color(0xFFD97706),
              ),

              Container(
                width: 1,
                height: 16,
                color: isDark ? const Color(0xFF475569) : const Color(0xFFCBD5E1),
                margin: const EdgeInsets.symmetric(horizontal: 4),
              ),

              Text(
                isRtl ? 'المعالجة:' : 'Status:',
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                  color: isDark ? Colors.white54 : const Color(0xFF64748B),
                ),
              ),
              _buildSecondaryFilterChip(
                key: 'processing',
                label: isRtl ? 'قيد التجهيز' : 'Processing',
                count: processingCount,
                activeColor: const Color(0xFF9333EA),
              ),
              _buildSecondaryFilterChip(
                key: 'shipped',
                label: isRtl ? 'تم الشحن' : 'Shipped',
                count: shippedCount,
                activeColor: Colors.blue,
              ),
              _buildSecondaryFilterChip(
                key: 'delivered',
                label: isRtl ? 'مكتمل' : 'Delivered',
                count: deliveredCount,
                activeColor: Colors.green,
              ),
              _buildSecondaryFilterChip(
                key: 'cancelled',
                label: isRtl ? 'ملغي' : 'Cancelled',
                count: cancelledCount,
                activeColor: Colors.red,
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSecondaryFilterChip({
    required String key,
    required String label,
    required int count,
    required Color activeColor,
  }) {
    final isSelected = _selectedFilter == key;
    return InkWell(
      onTap: () => setState(() => _selectedFilter = key),
      borderRadius: BorderRadius.circular(8),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
        decoration: BoxDecoration(
          color: isSelected
              ? activeColor
              : (isDark ? const Color(0xFF0F172A) : Colors.white),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(
            color: isSelected
                ? activeColor
                : (isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1)),
            width: 1.0,
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              label,
              style: TextStyle(
                color: isSelected
                    ? Colors.white
                    : (isDark ? Colors.white70 : const Color(0xFF334155)),
                fontSize: 11,
                fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
              ),
            ),
            const SizedBox(width: 5),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
              decoration: BoxDecoration(
                color: isSelected
                    ? Colors.white.withValues(alpha: 0.25)
                    : (isDark ? Colors.white10 : const Color(0xFFE2E8F0)),
                borderRadius: BorderRadius.circular(6),
              ),
              child: Text(
                '$count',
                style: TextStyle(
                  color: isSelected
                      ? Colors.white
                      : (isDark ? Colors.white60 : const Color(0xFF64748B)),
                  fontSize: 10,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildOrdersList(AsyncValue<List<OrderModel>> ordersAsync, bool isRtl) {
    return ordersAsync.when(
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (e, st) => AdminErrorView(
        error: e,
        stackTrace: st,
        onRetry: () => ref.read(adminOrdersProvider.notifier).loadOrders(),
      ),
      data: (orders) {
        var filtered = orders.where((o) {
          bool matchFilter = true;
          final status = o.status.toLowerCase();
          switch (_selectedFilter) {
            case 'all':
              matchFilter = true;
              break;
            case 'exclusive':
              matchFilter = o.isExclusive;
              break;
            case 'financial_review':
              matchFilter = o.paymentStatus == 'under_review' ||
                  (o.receiptUrl != null && o.receiptUrl!.trim().isNotEmpty && !o.isFullyPaid) ||
                  (status == 'pending' && !o.isFullyPaid);
              break;
            case 'advertisers':
              matchFilter = o.isStoreAdvertiser;
              break;
            case 'paid':
              matchFilter = o.isFullyPaid;
              break;
            case 'unpaid':
              matchFilter = !o.isFullyPaid;
              break;
            case 'delivered':
              matchFilter = status == 'delivered' || status == 'completed';
              break;
            default:
              matchFilter = status == _selectedFilter;
          }

          final q = _searchQuery.toLowerCase().trim();
          final matchSearch = q.isEmpty ||
              o.id.toLowerCase().contains(q) ||
              o.formattedOrderNumber.toLowerCase().contains(q) ||
              (o.buyerName?.toLowerCase().contains(q) ?? false) ||
              (o.buyerPhone?.toLowerCase().contains(q) ?? false) ||
              (o.storeName?.toLowerCase().contains(q) ?? false);

          return matchFilter && matchSearch;
        }).toList();

        if (filtered.isEmpty) {
          return Center(
            child: Padding(
              padding: const EdgeInsets.all(40.0),
              child: Column(
                children: [
                  Icon(Icons.inbox_rounded, size: 80, color: Colors.grey.shade400),
                  const SizedBox(height: 16),
                  Text(
                    isRtl ? 'لا توجد طلبات مطابقة للبحث أو الفلتر المختار' : 'No matching orders found',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w900,
                      color: isDark ? Colors.white70 : const Color(0xFF0F172A),
                    ),
                  ),
                ],
              ),
            ),
          );
        }

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.only(bottom: 14),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(6),
                        decoration: BoxDecoration(
                          color: const Color(0xFF2563EB).withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Icon(Icons.table_chart_rounded, size: 16, color: Color(0xFF2563EB)),
                      ),
                      const SizedBox(width: 8),
                      Text(
                        isRtl
                            ? 'عرض ${filtered.length} من أصل ${orders.length} طلب'
                            : 'Showing ${filtered.length} of ${orders.length} orders',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                          color: isDark ? Colors.white70 : const Color(0xFF334155),
                        ),
                      ),
                    ],
                  ),
                  // View mode toggle: Table View (default) vs Cards View
                  Container(
                    padding: const EdgeInsets.all(3),
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0xFF1E293B) : const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1)),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        InkWell(
                          onTap: () => setState(() => _isTableView = true),
                          borderRadius: BorderRadius.circular(7),
                          child: AnimatedContainer(
                            duration: const Duration(milliseconds: 150),
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                            decoration: BoxDecoration(
                              color: _isTableView
                                  ? const Color(0xFF2563EB)
                                  : Colors.transparent,
                              borderRadius: BorderRadius.circular(7),
                            ),
                            child: Row(
                              children: [
                                Icon(Icons.table_rows_rounded, size: 15, color: _isTableView ? Colors.white : Colors.grey.shade600),
                                const SizedBox(width: 5),
                                Text(
                                  isRtl ? 'عرض جدول موحد' : 'Table View',
                                  style: TextStyle(
                                    fontSize: 11.5,
                                    fontWeight: FontWeight.bold,
                                    color: _isTableView ? Colors.white : (isDark ? Colors.white60 : Colors.grey.shade700),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(width: 2),
                        InkWell(
                          onTap: () => setState(() => _isTableView = false),
                          borderRadius: BorderRadius.circular(7),
                          child: AnimatedContainer(
                            duration: const Duration(milliseconds: 150),
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                            decoration: BoxDecoration(
                              color: !_isTableView
                                  ? const Color(0xFF2563EB)
                                  : Colors.transparent,
                              borderRadius: BorderRadius.circular(7),
                            ),
                            child: Row(
                              children: [
                                Icon(Icons.grid_view_rounded, size: 15, color: !_isTableView ? Colors.white : Colors.grey.shade600),
                                const SizedBox(width: 5),
                                Text(
                                  isRtl ? 'بطاقات' : 'Cards',
                                  style: TextStyle(
                                    fontSize: 11.5,
                                    fontWeight: FontWeight.bold,
                                    color: !_isTableView ? Colors.white : (isDark ? Colors.white60 : Colors.grey.shade700),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            if (_isTableView)
              _buildOrdersTable(filtered, isRtl)
            else
              ListView.separated(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: filtered.length,
                separatorBuilder: (ctx, i) => const SizedBox(height: 12),
                itemBuilder: (context, index) {
                  final order = filtered[index];
                  return TweenAnimationBuilder<double>(
                    tween: Tween(begin: 0.0, end: 1.0),
                    duration: Duration(milliseconds: 300 + (index * 50).clamp(0, 500)),
                    curve: Curves.easeOutCubic,
                    builder: (context, value, child) {
                      return Transform.translate(
                        offset: Offset(0, 20 * (1 - value)),
                        child: Opacity(opacity: value, child: child),
                      );
                    },
                    child: _buildOrderCard(order, isRtl),
                  );
                },
              ),
          ],
        );
      },
    );
  }

  Widget _buildOrdersTable(List<OrderModel> orders, bool isRtl) {
    final currencyFmt = NumberFormat('#,##0.00', 'en');
    final dateFmt = DateFormat('yyyy-MM-dd HH:mm');

    return Container(
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1),
          width: 1.2,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: LayoutBuilder(
        builder: (context, constraints) {
          const double minTableWidth = 1200.0;
          final tableWidth = constraints.maxWidth > minTableWidth ? constraints.maxWidth : minTableWidth;

          return SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: SizedBox(
              width: tableWidth,
              child: Column(
                children: [
                  // --- Table Header ---
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF1F5F9),
                      border: Border(
                        bottom: BorderSide(
                          color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1),
                          width: 1.2,
                        ),
                      ),
                    ),
                    child: Row(
                      children: [
                        // 1. التاريخ والزمن
                        SizedBox(
                          width: 140,
                          child: _buildTableHeaderCell(isRtl ? 'التاريخ والزمن' : 'Date & Time', Icons.schedule_rounded),
                        ),
                        const SizedBox(width: 12),
                        // 2. رقم الفاتورة
                        SizedBox(
                          width: 140,
                          child: _buildTableHeaderCell(isRtl ? 'رقم الفاتورة' : 'Invoice #', Icons.tag_rounded),
                        ),
                        const SizedBox(width: 12),
                        // 3. المتجر
                        Expanded(
                          flex: 3,
                          child: _buildTableHeaderCell(isRtl ? 'المتجر' : 'Store', Icons.storefront_rounded),
                        ),
                        const SizedBox(width: 12),
                        // 4. المشتري
                        Expanded(
                          flex: 3,
                          child: _buildTableHeaderCell(isRtl ? 'المشتري' : 'Buyer', Icons.person_outline_rounded),
                        ),
                        const SizedBox(width: 12),
                        // 5. الصنف
                        Expanded(
                          flex: 4,
                          child: _buildTableHeaderCell(isRtl ? 'الصنف / الأصناف' : 'Item(s)', Icons.inventory_2_outlined),
                        ),
                        const SizedBox(width: 12),
                        // 6. الحالة
                        SizedBox(
                          width: 125,
                          child: _buildTableHeaderCell(isRtl ? 'حالة الطلب' : 'Status', Icons.flag_outlined),
                        ),
                        const SizedBox(width: 12),
                        // 7. حالة السداد
                        SizedBox(
                          width: 145,
                          child: _buildTableHeaderCell(isRtl ? 'حالة السداد' : 'Payment', Icons.payments_outlined),
                        ),
                        const SizedBox(width: 12),
                        // 8. المبلغ الإجمالي
                        SizedBox(
                          width: 140,
                          child: _buildTableHeaderCell(isRtl ? 'المبلغ الإجمالي' : 'Total Amount', Icons.monetization_on_outlined, alignEnd: true),
                        ),
                        const SizedBox(width: 8),
                        // 9. الإجراءات
                        const SizedBox(
                          width: 50,
                          child: Center(
                            child: Icon(Icons.more_horiz_rounded, size: 20, color: Colors.grey),
                          ),
                        ),
                      ],
                    ),
                  ),

                  // --- Table Rows ---
                  ListView.separated(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: orders.length,
                    separatorBuilder: (context, index) => Divider(
                      height: 1,
                      thickness: 1,
                      color: isDark ? const Color(0xFF334155).withValues(alpha: 0.5) : const Color(0xFFE2E8F0),
                    ),
                    itemBuilder: (context, index) {
                      final order = orders[index];
                      final isEven = index % 2 == 0;
                      final rowBg = isEven
                          ? (isDark ? const Color(0xFF1E293B) : Colors.white)
                          : (isDark ? const Color(0xFF162032) : const Color(0xFFFAFCFF));

                      return _buildTableRow(order, rowBg, isRtl, currencyFmt, dateFmt);
                    },
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildTableHeaderCell(String label, IconData icon, {bool alignEnd = false}) {
    return Row(
      mainAxisAlignment: alignEnd ? MainAxisAlignment.end : MainAxisAlignment.start,
      children: [
        Icon(icon, size: 14, color: isDark ? Colors.white60 : const Color(0xFF64748B)),
        const SizedBox(width: 6),
        Flexible(
          child: Text(
            label,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.bold,
              color: isDark ? Colors.white70 : const Color(0xFF334155),
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
    );
  }

  Widget _buildTableRow(
    OrderModel order,
    Color rowBg,
    bool isRtl,
    NumberFormat currencyFmt,
    DateFormat dateFmt,
  ) {
    final statusColor = _getStatusColor(order.status);
    final statusArabic = _getStatusArabic(order.status);
    final hasReceipt = (order.receiptUrl != null && order.receiptUrl!.isNotEmpty) ||
        (order.notes != null && order.notes!.contains('receipt_url:'));

    // Determine items text / widget
    final Widget itemsWidget;
    if (order.items.isEmpty) {
      itemsWidget = Text(
        isRtl ? 'منتجات عامة' : 'General items',
        style: TextStyle(
          color: isDark ? Colors.white54 : const Color(0xFF64748B),
          fontSize: 12,
          fontStyle: FontStyle.italic,
        ),
        overflow: TextOverflow.ellipsis,
      );
    } else if (order.items.length == 1) {
      final item = order.items.first;
      final qStr = item.quantity > 1 ? ' (×${item.quantity})' : '';
      itemsWidget = Tooltip(
        message: '${item.productName}$qStr',
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.inventory_2_outlined, size: 14, color: isDark ? Colors.white60 : const Color(0xFF64748B)),
            const SizedBox(width: 6),
            Flexible(
              child: Text(
                '${item.productName}$qStr',
                style: TextStyle(
                  color: isDark ? Colors.white : const Color(0xFF1E293B),
                  fontWeight: FontWeight.w700,
                  fontSize: 12.5,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
      );
    } else {
      final itemsTooltip = order.items.map((it) => '• ${it.productName} (×${it.quantity})').join('\n');
      itemsWidget = Tooltip(
        message: itemsTooltip,
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
          decoration: BoxDecoration(
            color: const Color(0xFF8B5CF6).withValues(alpha: 0.12),
            borderRadius: BorderRadius.circular(8),
            border: Border.all(color: const Color(0xFF8B5CF6).withValues(alpha: 0.35)),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.layers_rounded, size: 14, color: Color(0xFF7C3AED)),
              const SizedBox(width: 5),
              Flexible(
                child: Text(
                  isRtl ? 'أكثر من صنف (${order.items.length} أصناف)' : 'Multiple items (${order.items.length})',
                  style: const TextStyle(
                    color: Color(0xFF7C3AED),
                    fontSize: 11.5,
                    fontWeight: FontWeight.w900,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
        ),
      );
    }

    return Material(
      color: rowBg,
      child: InkWell(
        onTap: () => _showOrderDetailsModal(order, isRtl),
        hoverColor: isDark ? const Color(0xFF2D3748) : const Color(0xFFEDF2F7),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          child: Row(
            children: [
              // 1. التاريخ مع الزمن
              SizedBox(
                width: 140,
                child: Row(
                  children: [
                    Icon(Icons.calendar_today_rounded, size: 13, color: isDark ? Colors.white38 : const Color(0xFF94A3B8)),
                    const SizedBox(width: 6),
                    Expanded(
                      child: Text(
                        dateFmt.format(order.createdAt),
                        style: TextStyle(
                          color: isDark ? Colors.white70 : const Color(0xFF475569),
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          fontFamily: 'monospace',
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 12),

              // 2. رقم الفاتورة
              SizedBox(
                width: 140,
                child: Row(
                  children: [
                    Flexible(
                      child: Text(
                        '#${order.formattedOrderNumber}',
                        style: const TextStyle(
                          color: Color(0xFF2563EB),
                          fontWeight: FontWeight.w900,
                          fontSize: 13,
                          fontFamily: 'monospace',
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    if (order.isExclusive) ...[
                      const SizedBox(width: 4),
                      const Tooltip(
                        message: 'توريد مقاييس المباشر',
                        child: Icon(Icons.verified_rounded, size: 14, color: Color(0xFFD4AF37)),
                      ),
                    ],
                    if (order.isStoreAdvertiser) ...[
                      const SizedBox(width: 4),
                      const Tooltip(
                        message: 'متجر معلن رسمي',
                        child: Icon(Icons.campaign_rounded, size: 14, color: Color(0xFF8B5CF6)),
                      ),
                    ],
                  ],
                ),
              ),
              const SizedBox(width: 12),

              // 3. المتجر
              Expanded(
                flex: 3,
                child: Tooltip(
                  message: '${order.storeName ?? (isRtl ? "توريد مقاييس المباشر" : "Magaiees Direct Supply")}${order.staffAssignedName != null ? " (${isRtl ? "الموظف" : "Staff"}: ${order.staffAssignedName})" : ""}',
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Row(
                        children: [
                          Icon(Icons.storefront_rounded, size: 15, color: const Color(0xFF2563EB).withValues(alpha: 0.8)),
                          const SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              order.storeName ?? (isRtl ? 'توريد مقاييس المباشر' : 'Magaiees Direct Supply'),
                              style: TextStyle(
                                color: isDark ? Colors.white : const Color(0xFF0F172A),
                                fontWeight: FontWeight.w700,
                                fontSize: 12.5,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                      if (order.staffAssignedName != null) ...[
                        const SizedBox(height: 2),
                        Row(
                          children: [
                            const Icon(Icons.badge_outlined, size: 11, color: Color(0xFF64748B)),
                            const SizedBox(width: 4),
                            Expanded(
                              child: Text(
                                '${isRtl ? "الموظف" : "Staff"}: ${order.staffAssignedName}',
                                style: TextStyle(
                                  color: isDark ? Colors.white60 : const Color(0xFF64748B),
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.w600,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 12),

              // 4. المشتري
              Expanded(
                flex: 3,
                child: Tooltip(
                  message: '${order.buyerName ?? (isRtl ? "غير مسجل" : "Unregistered")}${order.buyerPhone != null ? " (${order.buyerPhone})" : ""}',
                  child: Row(
                    children: [
                      Icon(Icons.person_outline_rounded, size: 15, color: isDark ? Colors.white54 : const Color(0xFF64748B)),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          order.buyerName ?? (isRtl ? 'غير مسجل' : 'Unregistered'),
                          style: TextStyle(
                            color: isDark ? Colors.white : const Color(0xFF1E293B),
                            fontWeight: FontWeight.w600,
                            fontSize: 12.5,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 12),

              // 5. الصنف (إذا صنف واحد أو أكثر من صنف)
              Expanded(
                flex: 4,
                child: itemsWidget,
              ),
              const SizedBox(width: 12),

              // 6. حالة الطلب
              SizedBox(
                width: 125,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: statusColor.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: statusColor.withValues(alpha: 0.6), width: 1),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(
                        width: 6,
                        height: 6,
                        decoration: BoxDecoration(color: statusColor, shape: BoxShape.circle),
                      ),
                      const SizedBox(width: 6),
                      Flexible(
                        child: Text(
                          isRtl ? statusArabic : order.status,
                          style: TextStyle(
                            color: statusColor,
                            fontWeight: FontWeight.w900,
                            fontSize: 11,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 12),

              // 7. حالة السداد
              SizedBox(
                width: 145,
                child: _buildPaymentBadge(order, isRtl, currencyFmt, hasReceipt),
              ),
              const SizedBox(width: 12),

              // 8. المبلغ الإجمالي
              SizedBox(
                width: 140,
                child: Text(
                  '${currencyFmt.format(order.totalAmount)} ج.س',
                  textAlign: TextAlign.end,
                  style: TextStyle(
                    color: isDark ? const Color(0xFF60A5FA) : const Color(0xFF1E40AF),
                    fontWeight: FontWeight.w900,
                    fontSize: 13.5,
                    fontFamily: 'monospace',
                  ),
                ),
              ),
              const SizedBox(width: 8),

              // 9. الإجراءات (عرض التفاصيل)
              SizedBox(
                width: 50,
                child: Center(
                  child: IconButton(
                    icon: const Icon(Icons.open_in_new_rounded, size: 18, color: Color(0xFF2563EB)),
                    tooltip: isRtl ? 'عرض تفاصيل الفاتورة' : 'View invoice details',
                    onPressed: () => _showOrderDetailsModal(order, isRtl),
                    splashRadius: 18,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildPaymentBadge(OrderModel order, bool isRtl, NumberFormat currencyFmt, bool hasReceipt) {
    final bool isUnderReview = order.paymentStatus == 'under_review' ||
        (order.receiptUrl != null && order.receiptUrl!.trim().isNotEmpty && !order.isFullyPaid);

    Color bg;
    Color border;
    Color text;
    String label;
    IconData icon;

    if (order.isFullyPaid) {
      bg = const Color(0xFFECFDF5);
      border = const Color(0xFF047857);
      text = const Color(0xFF047857);
      label = isRtl ? '✓ مدفوع بالكامل' : '✓ Fully Paid';
      icon = Icons.check_circle_rounded;
    } else if (isUnderReview) {
      bg = const Color(0xFFFEF3C7);
      border = const Color(0xFFD97706);
      text = const Color(0xFFB45309);
      label = isRtl ? '⏳ قيد التدقيق' : '⏳ Review';
      icon = Icons.pending_actions_rounded;
    } else if (order.actualPaidAmount > 0) {
      bg = const Color(0xFFFEF3C7);
      border = const Color(0xFFD97706);
      text = const Color(0xFFB45309);
      label = isRtl ? '◐ مسدد: ${currencyFmt.format(order.actualPaidAmount)}' : '◐ Paid: ${currencyFmt.format(order.actualPaidAmount)}';
      icon = Icons.incomplete_circle_rounded;
    } else {
      bg = const Color(0xFFFEF2F2);
      border = const Color(0xFFB91C1C);
      text = const Color(0xFFB91C1C);
      label = isRtl ? '✗ غير مدفوع' : '✗ Unpaid';
      icon = Icons.cancel_outlined;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
      decoration: BoxDecoration(
        color: isDark ? bg.withValues(alpha: 0.15) : bg,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: border.withValues(alpha: 0.5), width: 1),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, size: 12, color: text),
          const SizedBox(width: 4),
          Flexible(
            child: Text(
              label,
              style: TextStyle(
                color: text,
                fontSize: 10.5,
                fontWeight: FontWeight.w900,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
          if (hasReceipt) ...[
            const SizedBox(width: 4),
            Tooltip(
              message: isRtl ? 'يوجد إشعار تحويل مرفق' : 'Bank receipt attached',
              child: Icon(Icons.attach_file_rounded, size: 12, color: text),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildOrderCard(OrderModel order, bool isRtl) {
    final statusColor = _getStatusColor(order.status);
    final statusArabic = _getStatusArabic(order.status);
    final dateFmt = DateFormat('yyyy-MM-dd HH:mm');
    final currencyFmt = NumberFormat('#,##0.00', 'en');
    final hasReceipt = (order.receiptUrl != null && order.receiptUrl!.isNotEmpty) ||
        (order.notes != null && order.notes!.contains('receipt_url:'));

    return InkWell(
      onTap: () => _showOrderDetailsModal(order, isRtl),
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: isDark ? const Color(0xFF1E293B) : Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: order.isStoreAdvertiser
                ? const Color(0xFF8B5CF6).withValues(alpha: 0.5)
                : const Color(0xFFCBD5E1),
            width: order.isStoreAdvertiser ? 1.5 : 1.2,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.04),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: statusColor.withValues(alpha: 0.12),
                shape: BoxShape.circle,
                border: Border.all(color: statusColor.withValues(alpha: 0.4)),
              ),
              child: Icon(Icons.receipt_long, color: statusColor, size: 28),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Text(
                        '#${order.formattedOrderNumber}',
                        style: TextStyle(
                          fontWeight: FontWeight.w900,
                          fontSize: 16,
                          color: isDark ? Colors.white : const Color(0xFF0F172A),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: statusColor.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: statusColor, width: 1),
                        ),
                        child: Text(
                          isRtl ? statusArabic : order.status,
                          style: TextStyle(color: statusColor, fontWeight: FontWeight.w900, fontSize: 12),
                        ),
                      ),
                      if (order.isStoreAdvertiser) ...[
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [Color(0xFF8B5CF6), Color(0xFF6D28D9)],
                            ),
                            borderRadius: BorderRadius.circular(20),
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xFF8B5CF6).withValues(alpha: 0.3),
                                blurRadius: 4,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.campaign, color: Colors.white, size: 13),
                              const SizedBox(width: 4),
                              Text(
                                isRtl ? 'متجر معلن' : 'Advertiser',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 11,
                                  fontWeight: FontWeight.w900,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                      if (order.isExclusive) ...[
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [Color(0xFF0F2E4A), Color(0xFF1E40AF)],
                            ),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: const Color(0xFFD4AF37), width: 1),
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xFFD4AF37).withValues(alpha: 0.25),
                                blurRadius: 4,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.verified, color: Color(0xFFD4AF37), size: 13),
                              const SizedBox(width: 4),
                              Text(
                                isRtl ? 'توريد مقاييس المباشر' : 'Magaiees Direct Supply',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 11,
                                  fontWeight: FontWeight.w900,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                      if (order.paymentStatus == 'under_review' || (order.receiptUrl != null && order.receiptUrl!.isNotEmpty && !order.isFullyPaid)) ...[
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: isDark ? const Color(0xFF78350F).withValues(alpha: 0.35) : const Color(0xFFFEF3C7),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: const Color(0xFFF59E0B), width: 1),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.pending_actions_rounded, color: Color(0xFFD97706), size: 13),
                              const SizedBox(width: 4),
                              Text(
                                isRtl ? 'قيد التدقيق المالي' : 'Financial Review',
                                style: TextStyle(
                                  color: isDark ? Colors.amber.shade200 : const Color(0xFF92400E),
                                  fontSize: 11,
                                  fontWeight: FontWeight.w900,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    '${isRtl ? "المشتري:" : "Buyer:"} ${order.buyerName ?? (isRtl ? "غير معروف" : "Unknown")}',
                    style: TextStyle(
                      color: isDark ? Colors.white70 : const Color(0xFF0F172A),
                      fontWeight: FontWeight.bold,
                      fontSize: 13.5,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      const Icon(Icons.storefront_rounded, size: 15, color: Color(0xFF2563EB)),
                      const SizedBox(width: 4),
                      Text(
                        '${isRtl ? "المتجر:" : "Store:"} ${order.storeName ?? (isRtl ? "تطبيق مقاييس" : "Magaiees Platform")}',
                        style: TextStyle(
                          color: isDark ? Colors.white60 : const Color(0xFF334155),
                          fontSize: 12.5,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      if (order.staffAssignedName != null) ...[
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9),
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: isDark ? const Color(0xFF475569) : const Color(0xFFCBD5E1)),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.badge_outlined, size: 12, color: isDark ? Colors.white70 : const Color(0xFF475569)),
                              const SizedBox(width: 3),
                              Text(
                                '${isRtl ? "الموظف" : "Staff"}: ${order.staffAssignedName}',
                                style: TextStyle(
                                  color: isDark ? Colors.white70 : const Color(0xFF334155),
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                      if (hasReceipt) ...[
                        const SizedBox(width: 12),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: Colors.blue.shade50,
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: Colors.blue.shade200),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.attach_file, size: 12, color: Colors.blue.shade800),
                              const SizedBox(width: 2),
                              Text(
                                isRtl ? 'إشعار تحويل' : 'Receipt',
                                style: TextStyle(color: Colors.blue.shade900, fontSize: 10.5, fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ],
                  ),
                ],
              ),
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                  '${currencyFmt.format(order.totalAmount)} ج.س',
                  style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 17, color: Color(0xFF1E40AF)),
                ),
                const SizedBox(height: 4),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: order.isFullyPaid
                        ? const Color(0xFFECFDF5)
                        : (order.actualPaidAmount > 0 ? const Color(0xFFFEF3C7) : const Color(0xFFFEF2F2)),
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(
                      color: order.isFullyPaid
                          ? const Color(0xFF047857)
                          : (order.actualPaidAmount > 0 ? const Color(0xFFD97706) : const Color(0xFFB91C1C)),
                    ),
                  ),
                  child: Text(
                    order.isFullyPaid
                        ? (isRtl ? '✓ مدفوع بالكامل' : '✓ Paid')
                        : (order.actualPaidAmount > 0
                            ? (isRtl ? '◐ مسدد: ${currencyFmt.format(order.actualPaidAmount)}' : '◐ Paid: ${currencyFmt.format(order.actualPaidAmount)}')
                            : (isRtl ? '✗ غير مدفوع' : '✗ Unpaid')),
                    style: TextStyle(
                      color: order.isFullyPaid
                          ? const Color(0xFF047857)
                          : (order.actualPaidAmount > 0 ? const Color(0xFFB45309) : const Color(0xFFB91C1C)),
                      fontSize: 11,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  dateFmt.format(order.createdAt),
                  style: TextStyle(
                    color: isDark ? Colors.white60 : const Color(0xFF64748B),
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'pending': return Colors.orange;
      case 'processing': return const Color(0xFF7C3AED);
      case 'shipped': return Colors.blue;
      case 'completed':
      case 'delivered': return Colors.green;
      case 'cancelled': return Colors.red;
      default: return Colors.grey;
    }
  }

  String _getStatusArabic(String status) {
    switch (status.toLowerCase()) {
      case 'pending': return 'قيد المراجعة';
      case 'processing': return 'قيد التجهيز';
      case 'shipped': return 'تم الشحن';
      case 'completed':
      case 'delivered': return 'مكتمل';
      case 'cancelled': return 'ملغي';
      default: return status;
    }
  }

  void _showOrderDetailsModal(OrderModel order, bool isRtl) {
    final currencyFmt = NumberFormat('#,##0.00', 'en');
    final dateFormat = DateFormat('yyyy-MM-dd HH:mm');

    final List<String> receiptUrls = [];
    if (order.receiptUrl != null && order.receiptUrl!.trim().isNotEmpty && order.receiptUrl!.startsWith('http')) {
      receiptUrls.add(order.receiptUrl!.trim());
    }
    if (order.notes != null && order.notes!.contains('receipt_url:')) {
      final parts = order.notes!.split(RegExp(r'(?:approved_|rejected_)?receipt_url:'));
      for (int i = 1; i < parts.length; i++) {
        final u = parts[i].split(' ')[0].split('|')[0].trim().split('\n')[0].trim();
        if (u.isNotEmpty && u.startsWith('http') && !receiptUrls.contains(u)) {
          receiptUrls.add(u);
        }
      }
    }

    showDialog(
      context: context,
      builder: (ctx) => Directionality(
        textDirection: isRtl ? TextDirection.rtl : TextDirection.ltr,
        child: Dialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
          child: Container(
            width: 620,
            constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * 0.9),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF1E293B) : Colors.white,
              borderRadius: BorderRadius.circular(24),
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Modal Header
                Container(
                  padding: const EdgeInsets.all(24),
                  decoration: BoxDecoration(
                    color: Colors.blue.withValues(alpha: 0.05),
                    borderRadius: const BorderRadius.only(topLeft: Radius.circular(24), topRight: Radius.circular(24)),
                    border: Border(bottom: BorderSide(color: Colors.grey.withValues(alpha: 0.2))),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.receipt_long, color: Colors.blue, size: 32),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              '${isRtl ? "تفاصيل الطلب" : "Order Details"} #${order.formattedOrderNumber}',
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 20),
                            ),
                            const SizedBox(height: 4),
                            Text(dateFormat.format(order.createdAt), style: TextStyle(color: Colors.grey.shade600)),
                          ],
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.close),
                        onPressed: () => Navigator.pop(ctx),
                      )
                    ],
                  ),
                ),
                // Modal Body
                Flexible(
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Advertiser store banner if applicable
                        if (order.isStoreAdvertiser) ...[
                          Container(
                            margin: const EdgeInsets.only(bottom: 20),
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                colors: [
                                  const Color(0xFF8B5CF6).withValues(alpha: 0.12),
                                  const Color(0xFF6D28D9).withValues(alpha: 0.05)
                                ],
                              ),
                              borderRadius: BorderRadius.circular(14),
                              border: Border.all(color: const Color(0xFF8B5CF6).withValues(alpha: 0.4)),
                            ),
                            child: Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(8),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFF7C3AED),
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: const Icon(Icons.campaign_rounded, color: Colors.white, size: 22),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        isRtl ? 'طلب لمتجر معلن نشط 📢' : 'Active Advertiser Store Order 📢',
                                        style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF5B21B6), fontSize: 13.5),
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        isRtl
                                            ? 'هذا المتجر (${order.storeName ?? "المتجر"}) يمتلك حملات إعلانية نشطة على منصة مقاييس وتطبق عليه مزايا وتسهيلات الشركاء.'
                                            : 'This store (${order.storeName ?? "Store"}) has active ad campaigns on Magaiees platform.',
                                        style: const TextStyle(color: Color(0xFF4C1D95), fontSize: 11.5, fontWeight: FontWeight.bold),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],

                        // Customer & Store Info Cards
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Expanded(
                              child: _buildInfoSection(
                                isRtl ? 'بيانات المشتري' : 'Buyer Info',
                                Icons.person,
                                [
                                  {'label': isRtl ? 'الاسم' : 'Name', 'value': order.buyerName ?? (isRtl ? 'غير معروف' : 'Unknown')},
                                  {'label': isRtl ? 'الهاتف' : 'Phone', 'value': order.buyerPhone ?? '-'},
                                  {'label': isRtl ? 'البريد' : 'Email', 'value': order.buyerEmail ?? '-'},
                                ],
                                isRtl,
                              ),
                            ),
                            const SizedBox(width: 16),
                            Expanded(
                              child: _buildInfoSection(
                                isRtl ? 'تفاصيل التوصيل والمتجر' : 'Delivery & Store',
                                Icons.local_shipping,
                                [
                                  {'label': isRtl ? 'المتجر' : 'Store', 'value': order.storeName ?? (isRtl ? 'تطبيق مقاييس' : 'Magaiees')},
                                  if (order.staffAssignedName != null)
                                    {'label': isRtl ? 'الموظف المسؤول' : 'Handled by', 'value': order.staffAssignedName!},
                                  {'label': isRtl ? 'العنوان' : 'Address', 'value': order.shippingAddress ?? '-'},
                                  {'label': isRtl ? 'طريقة الدفع' : 'Payment', 'value': order.paymentMethod ?? '-'},
                                ],
                                isRtl,
                              ),
                            ),
                          ],
                        ),
                        if (order.cleanNotes.isNotEmpty) ...[
                          const SizedBox(height: 16),
                          Container(
                            width: double.infinity,
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: isDark ? const Color(0xFF1E293B) : const Color(0xFFF8FAFC),
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: const Color(0xFFE2E8F0)),
                            ),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Icon(Icons.sticky_note_2_outlined, size: 18, color: Color(0xFF64748B)),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        isRtl ? 'ملاحظات الطلب:' : 'Order Notes:',
                                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Color(0xFF64748B)),
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                        order.cleanNotes,
                                        style: TextStyle(
                                          fontSize: 13,
                                          color: isDark ? Colors.white70 : const Color(0xFF334155),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                        const SizedBox(height: 24),
                        // Items Table
                        Text(
                          isRtl ? 'قائمة المنتجات المشتراة' : 'Purchased Items',
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
                        ),
                        const SizedBox(height: 12),
                        Container(
                          decoration: BoxDecoration(
                            border: Border.all(color: Colors.grey.withValues(alpha: 0.2)),
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: Column(
                            children: [
                              // Table Header
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                                decoration: const BoxDecoration(
                                  color: Color(0xFF0F172A),
                                  borderRadius: BorderRadius.only(topLeft: Radius.circular(16), topRight: Radius.circular(16)),
                                ),
                                child: Row(
                                  children: [
                                    Expanded(flex: 3, child: Text(isRtl ? 'المنتج' : 'Product', style: const TextStyle(fontWeight: FontWeight.w900, color: Colors.white, fontSize: 13))),
                                    Expanded(child: Text(isRtl ? 'الكمية' : 'Qty', textAlign: TextAlign.center, style: const TextStyle(fontWeight: FontWeight.w900, color: Colors.white, fontSize: 13))),
                                    Expanded(flex: 2, child: Text(isRtl ? 'السعر' : 'Price', textAlign: TextAlign.end, style: const TextStyle(fontWeight: FontWeight.w900, color: Colors.white, fontSize: 13))),
                                  ],
                                ),
                              ),
                              // Table Rows
                              if (order.items.isEmpty)
                                Padding(
                                  padding: const EdgeInsets.all(16.0),
                                  child: Text(
                                    isRtl ? 'لا توجد منتجات مسجلة' : 'No items found',
                                    style: const TextStyle(color: Color(0xFF0F172A), fontWeight: FontWeight.bold),
                                  ),
                                ),
                              ...order.items.asMap().entries.map((entry) {
                                final item = entry.value;
                                final isLast = entry.key == order.items.length - 1;
                                return Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                                  decoration: BoxDecoration(
                                    color: entry.key.isOdd ? const Color(0xFFF8FAFC) : Colors.white,
                                    border: isLast ? null : const Border(bottom: BorderSide(color: Color(0xFFCBD5E1))),
                                  ),
                                  child: Row(
                                    children: [
                                      Expanded(
                                        flex: 3,
                                        child: Text(
                                          item.productName,
                                          style: const TextStyle(color: Color(0xFF0F172A), fontWeight: FontWeight.w900, fontSize: 13.5),
                                        ),
                                      ),
                                      Expanded(
                                        child: Text(
                                          'x${item.quantity}',
                                          textAlign: TextAlign.center,
                                          style: const TextStyle(color: Color(0xFF1E40AF), fontWeight: FontWeight.w900, fontSize: 13),
                                        ),
                                      ),
                                      Expanded(
                                        flex: 2,
                                        child: Text(
                                          '${currencyFmt.format(item.priceSdg)} ج.س',
                                          textAlign: TextAlign.end,
                                          style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF0F172A), fontSize: 13.5),
                                        ),
                                      ),
                                    ],
                                  ),
                                );
                              }),
                            ],
                          ),
                        ),
                        const SizedBox(height: 24),
                        // Total Financial Breakdown
                        Container(
                          padding: const EdgeInsets.all(18),
                          decoration: BoxDecoration(
                            color: const Color(0xFFEFF6FF),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: const Color(0xFF3B82F6), width: 1.3),
                          ),
                          child: Column(
                            children: [
                              if (order.transportFee > 0 || order.offloadingFee > 0) ...[
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(isRtl ? 'قيمة المنتجات:' : 'Items Subtotal:', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: Color(0xFF334155))),
                                    Text('${currencyFmt.format((order.totalAmount - order.transportFee - order.offloadingFee).clamp(0.0, double.infinity))} ج.س', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A))),
                                  ],
                                ),
                                if (order.transportFee > 0) ...[
                                  const SizedBox(height: 6),
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(isRtl ? 'أجور النقل والترحيل:' : 'Transport Fee:', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: Color(0xFF334155))),
                                      Text('${currencyFmt.format(order.transportFee)} ج.س', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A))),
                                    ],
                                  ),
                                ],
                                if (order.offloadingFee > 0) ...[
                                  const SizedBox(height: 6),
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(isRtl ? 'أجور العتالة والتفريغ:' : 'Offloading Fee:', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: Color(0xFF334155))),
                                      Text('${currencyFmt.format(order.offloadingFee)} ج.س', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A))),
                                    ],
                                  ),
                                ],
                                const Padding(padding: EdgeInsets.symmetric(vertical: 8), child: Divider()),
                              ],
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    isRtl ? 'الإجمالي الكلي للطلب:' : 'Total Amount:',
                                    style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 17, color: Color(0xFF0F172A)),
                                  ),
                                  Text(
                                    '${currencyFmt.format(order.totalAmount)} ج.س',
                                    style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 22, color: Color(0xFF1E40AF)),
                                  ),
                                ],
                              ),
                              if (order.commissionAmount != null && order.commissionAmount! > 0) ...[
                                const SizedBox(height: 6),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(isRtl ? 'عمولة المنصة المقررة:' : 'Platform Commission:', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF059669))),
                                    Text('${currencyFmt.format(order.commissionAmount!)} ج.س (${order.commissionRateApplied ?? 0}%)', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF059669))),
                                  ],
                                ),
                                const SizedBox(height: 6),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(isRtl ? 'صافي مستحق المتجر:' : 'Store Net Share:', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                                    Text('${currencyFmt.format(order.totalAmount - (order.commissionAmount ?? 0.0))} ج.س', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                                  ],
                                ),
                              ],
                            ],
                          ),
                        ),
                        const SizedBox(height: 16),
                        // === PAYMENT STATUS SECTION ===
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: order.isFullyPaid ? const Color(0xFFECFDF5) : const Color(0xFFFEF3C7),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(
                              color: order.isFullyPaid ? const Color(0xFF047857) : const Color(0xFFD97706),
                              width: 1.3,
                            ),
                          ),
                          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                            Row(children: [
                              Icon(
                                order.isFullyPaid ? Icons.check_circle : (order.actualPaidAmount > 0 ? Icons.timelapse : Icons.warning),
                                color: order.isFullyPaid ? const Color(0xFF047857) : const Color(0xFFD97706), size: 24,
                              ),
                              const SizedBox(width: 8),
                              Text(
                                isRtl ? 'حالة السداد والدفع' : 'Payment Status',
                                style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 16, color: Color(0xFF0F172A)),
                              ),
                              const Spacer(),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                  color: order.isFullyPaid ? const Color(0xFF047857) : (order.actualPaidAmount > 0 ? const Color(0xFFD97706) : const Color(0xFFDC2626)),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  order.isFullyPaid ? (isRtl ? 'مدفوع بالكامل' : 'Paid') : (order.actualPaidAmount > 0 ? (isRtl ? 'مدفوع جزئياً' : 'Partial') : (isRtl ? 'غير مدفوع' : 'Unpaid')),
                                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 12),
                                ),
                              ),
                            ]),
                            const SizedBox(height: 12),
                            Row(children: [
                              Expanded(child: _paymentInfoTile(isRtl ? 'إجمالي الطلب' : 'Order Total', '${currencyFmt.format(order.totalAmount)} ج.س', const Color(0xFF1E40AF))),
                              const SizedBox(width: 12),
                              Expanded(child: _paymentInfoTile(isRtl ? 'المبلغ المدفوع' : 'Paid', '${currencyFmt.format(order.actualPaidAmount)} ج.س', const Color(0xFF047857))),
                              const SizedBox(width: 12),
                              Expanded(child: _paymentInfoTile(isRtl ? 'المتبقي' : 'Remaining', '${currencyFmt.format(order.remainingAmount)} ج.س',
                                  order.remainingAmount > 0 ? const Color(0xFFDC2626) : const Color(0xFF047857))),
                            ]),
                            const SizedBox(height: 12),
                            Row(children: [
                              if (!order.isFullyPaid)
                                Expanded(child: ElevatedButton.icon(
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: const Color(0xFF047857), foregroundColor: Colors.white,
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                    padding: const EdgeInsets.symmetric(vertical: 12),
                                  ),
                                  onPressed: () => _showPaymentDialog(ctx, order, isRtl),
                                  icon: const Icon(Icons.payment, size: 18),
                                  label: Text(isRtl ? 'تسجيل دفعة' : 'Record Payment', style: const TextStyle(fontWeight: FontWeight.w900)),
                                )),
                              if (!order.isFullyPaid) const SizedBox(width: 12),
                              if (order.isOverpaid)
                                Expanded(child: ElevatedButton.icon(
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: const Color(0xFFD97706), foregroundColor: Colors.white,
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                    padding: const EdgeInsets.symmetric(vertical: 12),
                                  ),
                                  onPressed: () => _showRefundDialog(ctx, order, isRtl),
                                  icon: const Icon(Icons.undo, size: 18),
                                  label: Text(isRtl ? 'إرجاع تبقية' : 'Refund Change', style: const TextStyle(fontWeight: FontWeight.w900)),
                                )),
                              Expanded(child: OutlinedButton.icon(
                                style: OutlinedButton.styleFrom(
                                  foregroundColor: const Color(0xFF0F172A),
                                  side: const BorderSide(color: Color(0xFFCBD5E1), width: 1.3),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                  padding: const EdgeInsets.symmetric(vertical: 12),
                                ),
                                onPressed: () => _showPaymentHistory(ctx, order, isRtl),
                                icon: const Icon(Icons.history, size: 18, color: Color(0xFF2563EB)),
                                label: Text(isRtl ? 'سجل المدفوعات' : 'Payment Log', style: const TextStyle(fontWeight: FontWeight.w900)),
                              )),
                            ]),
                            if (receiptUrls.isNotEmpty) ...[
                              const SizedBox(height: 12),
                              SizedBox(
                                width: double.infinity,
                                child: FilledButton.icon(
                                  style: FilledButton.styleFrom(
                                    backgroundColor: Colors.blue.shade50,
                                    foregroundColor: Colors.blue.shade900,
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                    padding: const EdgeInsets.symmetric(vertical: 12),
                                  ),
                                  onPressed: () {
                                    showDialog(
                                      context: ctx,
                                      builder: (dCtx) => AlertDialog(
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                                        title: Text(
                                          isRtl ? 'إشعارات التحويل المرفوعة (${receiptUrls.length})' : 'Uploaded Receipts (${receiptUrls.length})',
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                                        ),
                                        content: SizedBox(
                                          width: 450,
                                          child: SingleChildScrollView(
                                            child: Column(
                                              mainAxisSize: MainAxisSize.min,
                                              children: [
                                                for (int rIdx = 0; rIdx < receiptUrls.length; rIdx++) ...[
                                                  if (rIdx > 0) const Divider(height: 24),
                                                  Text(isRtl ? 'إشعار #${rIdx + 1}' : 'Receipt #${rIdx + 1}', style: const TextStyle(fontWeight: FontWeight.bold)),
                                                  const SizedBox(height: 8),
                                                  ClipRRect(
                                                    borderRadius: BorderRadius.circular(8),
                                                    child: Image.network(
                                                      receiptUrls[rIdx],
                                                      fit: BoxFit.contain,
                                                      errorBuilder: (c, e, s) => const Icon(Icons.broken_image, size: 60),
                                                    ),
                                                  ),
                                                ],
                                              ],
                                            ),
                                          ),
                                        ),
                                        actions: [TextButton(onPressed: () => Navigator.pop(dCtx), child: Text(isRtl ? 'إغلاق' : 'Close'))],
                                      ),
                                    );
                                  },
                                  icon: const Icon(Icons.receipt_long, size: 20),
                                  label: Text(
                                    isRtl ? 'عرض إشعارات التحويل البنكي (${receiptUrls.length})' : 'View Uploaded Receipts (${receiptUrls.length})',
                                    style: const TextStyle(fontWeight: FontWeight.bold),
                                  ),
                                ),
                              ),
                            ],
                          ]),
                        ),
                        const SizedBox(height: 24),
                        // Change Status
                        Text(
                          isRtl ? 'تحديث حالة الطلب' : 'Update Status',
                          style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 16, color: Color(0xFF0F172A)),
                        ),
                        const SizedBox(height: 12),
                        Wrap(
                          spacing: 12,
                          runSpacing: 12,
                          children: [
                            _buildStatusBtn(ctx, order, 'pending', 'قيد المراجعة', Colors.orange, isRtl),
                            _buildStatusBtn(ctx, order, 'processing', 'قيد التجهيز', const Color(0xFF7C3AED), isRtl),
                            _buildStatusBtn(ctx, order, 'shipped', 'تم الشحن', Colors.blue, isRtl),
                            _buildStatusBtn(ctx, order, 'delivered', 'مكتمل', Colors.green, isRtl),
                            _buildStatusBtn(ctx, order, 'cancelled', 'ملغي', Colors.red, isRtl),
                          ],
                        )
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildInfoSection(String title, IconData icon, List<Map<String, String>> data, bool isRtl) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFCBD5E1), width: 1.2),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 20, color: const Color(0xFF2563EB)),
              const SizedBox(width: 8),
              Text(
                title,
                style: TextStyle(
                  fontWeight: FontWeight.w900,
                  fontSize: 15,
                  color: isDark ? Colors.white : const Color(0xFF0F172A),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          ...data.map((item) => Padding(
            padding: const EdgeInsets.only(bottom: 8.0),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                SizedBox(
                  width: 75,
                  child: Text(
                    '${item['label']!}:',
                    style: TextStyle(
                      color: isDark ? Colors.white70 : const Color(0xFF475569),
                      fontSize: 13,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                ),
                Expanded(
                  child: Text(
                    item['value']!,
                    style: TextStyle(
                      fontWeight: FontWeight.w900,
                      fontSize: 13.5,
                      color: isDark ? Colors.white : const Color(0xFF0F172A),
                    ),
                  ),
                ),
              ],
            ),
          )),
        ],
      ),
    );
  }

  Widget _buildStatusBtn(BuildContext ctx, OrderModel order, String statusKey, String statusAr, Color color, bool isRtl) {
    final isSelected = order.status.toLowerCase() == statusKey || (statusKey == 'delivered' && order.status == 'completed');
    return ElevatedButton(
      style: ElevatedButton.styleFrom(
        backgroundColor: isSelected ? color : (isDark ? const Color(0xFF1E293B) : Colors.white),
        foregroundColor: isSelected ? Colors.white : color,
        elevation: isSelected ? 2 : 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
          side: BorderSide(color: isSelected ? color : color.withValues(alpha: 0.5)),
        ),
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      ),
      onPressed: isSelected ? null : () async {
        try {
          await ref.read(adminOrdersProvider.notifier).updateOrderStatus(order.id, statusKey);
          if (ctx.mounted) Navigator.pop(ctx);
        } catch (e) {
          if (ctx.mounted) {
            ScaffoldMessenger.of(ctx).showSnackBar(
              SnackBar(content: Text('Error updating status: $e'), backgroundColor: Colors.red),
            );
          }
        }
      },
      child: Text(isRtl ? statusAr : statusKey.toUpperCase(), style: const TextStyle(fontWeight: FontWeight.w900)),
    );
  }

  Widget _paymentInfoTile(String label, String value, Color color) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: color.withValues(alpha: 0.3), width: 1.2),
      ),
      child: Column(children: [
        Text(label, style: const TextStyle(color: Color(0xFF0F172A), fontSize: 11.5, fontWeight: FontWeight.w900)),
        const SizedBox(height: 4),
        Text(value, style: TextStyle(color: color, fontWeight: FontWeight.w900, fontSize: 14)),
      ]),
    );
  }

  void _showPaymentDialog(BuildContext ctx, OrderModel order, bool isRtl) {
    final amountCtrl = TextEditingController(text: order.remainingAmount > 0 ? order.remainingAmount.toStringAsFixed(2) : '');
    String? method = (order.paymentMethod != null && order.paymentMethod!.isNotEmpty) ? order.paymentMethod : null;

    showDialog(context: ctx, builder: (dCtx) => StatefulBuilder(builder: (bCtx, setDState) {
      return AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(children: [
          const Icon(Icons.payment, color: Colors.green),
          const SizedBox(width: 10),
          Text(isRtl ? 'تسجيل دفعة' : 'Record Payment'),
        ]),
        content: Column(mainAxisSize: MainAxisSize.min, children: [
          Text(isRtl ? 'المتبقي: ${order.remainingAmount.toStringAsFixed(2)} ج.س' : 'Remaining: ${order.remainingAmount.toStringAsFixed(2)} ج.س',
              style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.red)),
          const SizedBox(height: 16),
          TextField(
            controller: amountCtrl,
            keyboardType: TextInputType.number,
            decoration: InputDecoration(
              labelText: isRtl ? 'المبلغ المدفوع (ج.س) *' : 'Amount (SDG) *',
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              prefixIcon: const Icon(Icons.monetization_on),
            ),
          ),
          const SizedBox(height: 12),
          DropdownButtonFormField<String>(
            initialValue: method,
            hint: Text(isRtl ? 'اختر طريقة الدفع *' : 'Select Payment Method *'),
            decoration: InputDecoration(
              labelText: isRtl ? 'طريقة الدفع *' : 'Payment Method *',
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              prefixIcon: const Icon(Icons.account_balance),
            ),
            items: ['تحويل بنكي', 'بنكك (Bankak)', 'أو كاش (O-Cash)', 'نقداً', 'أخرى']
                .map((m) => DropdownMenuItem(value: m, child: Text(m))).toList(),
            onChanged: (v) => setDState(() => method = v),
          ),
        ]),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dCtx), child: Text(isRtl ? 'إلغاء' : 'Cancel')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: Colors.green, foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10))),
            onPressed: () async {
              final amount = double.tryParse(amountCtrl.text) ?? 0;
              if (amount <= 0) {
                ScaffoldMessenger.of(ctx).showSnackBar(
                  SnackBar(content: Text(isRtl ? 'الرجاء إدخال مبلغ صحيح' : 'Please enter a valid amount'), backgroundColor: Colors.orange),
                );
                return;
              }
              if (method == null || method!.isEmpty) {
                ScaffoldMessenger.of(ctx).showSnackBar(
                  SnackBar(content: Text(isRtl ? 'يجب تحديد طريقة الدفع أولاً' : 'Please select payment method'), backgroundColor: Colors.red),
                );
                return;
              }
              Navigator.pop(dCtx);
              try {
                await ref.read(adminOrdersProvider.notifier).recordPayment(order.id, amount, method!);
                if (ctx.mounted) {
                  Navigator.pop(ctx); // close detail modal too
                  ScaffoldMessenger.of(ctx).showSnackBar(
                    SnackBar(content: Text(isRtl ? 'تم تسجيل الدفعة بنجاح' : 'Payment recorded successfully'), backgroundColor: Colors.green),
                  );
                }
              } catch (e) {
                if (ctx.mounted) {
                  ScaffoldMessenger.of(ctx).showSnackBar(
                    SnackBar(content: Text('Error recording payment: $e'), backgroundColor: Colors.red),
                  );
                }
              }
            },
            child: Text(isRtl ? 'تأكيد الدفعة' : 'Confirm'),
          ),
        ],
      );
    }));
  }

  void _showRefundDialog(BuildContext ctx, OrderModel order, bool isRtl) {
    final amountCtrl = TextEditingController(text: order.overpaidAmount.toStringAsFixed(2));

    showDialog(context: ctx, builder: (dCtx) => AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      title: Row(children: [
        const Icon(Icons.undo, color: Colors.orange),
        const SizedBox(width: 10),
        Text(isRtl ? 'إرجاع تبقية' : 'Refund Change'),
      ]),
      content: Column(mainAxisSize: MainAxisSize.min, children: [
        Text(isRtl ? 'المبلغ الزائد: ${order.overpaidAmount.toStringAsFixed(2)} ج.س' : 'Overpaid: ${order.overpaidAmount.toStringAsFixed(2)} ج.س',
            style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.orange)),
        const SizedBox(height: 16),
        TextField(
          controller: amountCtrl,
          keyboardType: TextInputType.number,
          decoration: InputDecoration(
            labelText: isRtl ? 'مبلغ الإرجاع (ج.س)' : 'Refund Amount',
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
          ),
        ),
      ]),
      actions: [
        TextButton(onPressed: () => Navigator.pop(dCtx), child: Text(isRtl ? 'إلغاء' : 'Cancel')),
        ElevatedButton(
          style: ElevatedButton.styleFrom(backgroundColor: Colors.orange, foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10))),
          onPressed: () async {
            final amount = double.tryParse(amountCtrl.text) ?? 0;
            if (amount <= 0) return;
            Navigator.pop(dCtx);
            await ref.read(adminOrdersProvider.notifier).processRefund(order.id, amount, order.paymentMethod ?? '');
            if (ctx.mounted) Navigator.pop(ctx);
          },
          child: Text(isRtl ? 'تأكيد الإرجاع' : 'Confirm Refund'),
        ),
      ],
    ));
  }

  void _showPaymentHistory(BuildContext ctx, OrderModel order, bool isRtl) async {
    final payments = await ref.read(adminOrdersProvider.notifier).getPaymentHistory(order.id);
    if (!ctx.mounted) return;
    final currencyFmt = NumberFormat('#,##0.00', 'ar');

    showDialog(context: ctx, builder: (dCtx) => AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      title: Row(children: [
        const Icon(Icons.history, color: Colors.blueGrey),
        const SizedBox(width: 10),
        Text(isRtl ? 'سجل الدفعات' : 'Payment History'),
      ]),
      content: SizedBox(
        width: 400,
        child: payments.isEmpty
            ? Padding(padding: const EdgeInsets.all(20),
                child: Text(isRtl ? 'لا توجد دفعات مسجلة' : 'No payments recorded', textAlign: TextAlign.center))
            : Column(mainAxisSize: MainAxisSize.min, children: [
                ...payments.map((p) => Container(
                  margin: const EdgeInsets.only(bottom: 8),
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: p.amount >= 0 ? Colors.green.withValues(alpha: 0.05) : Colors.orange.withValues(alpha: 0.05),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: (p.amount >= 0 ? Colors.green : Colors.orange).withValues(alpha: 0.2)),
                  ),
                  child: Row(children: [
                    Icon(p.amount >= 0 ? Icons.arrow_downward : Icons.arrow_upward,
                        color: p.amount >= 0 ? Colors.green : Colors.orange, size: 20),
                    const SizedBox(width: 10),
                    Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Text(p.method, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                      Text(DateFormat('yyyy/MM/dd HH:mm').format(p.createdAt),
                          style: TextStyle(color: Colors.grey.shade500, fontSize: 11)),
                    ])),
                    Text('${currencyFmt.format(p.amount.abs())} ج.س',
                        style: TextStyle(color: p.amount >= 0 ? Colors.green : Colors.orange, fontWeight: FontWeight.bold, fontSize: 14)),
                  ]),
                )),
                const Divider(),
                Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                  Text(isRtl ? 'إجمالي المدفوع:' : 'Total Paid:', style: const TextStyle(fontWeight: FontWeight.bold)),
                  Text('${currencyFmt.format(order.paidAmount)} ج.س', style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.green)),
                ]),
              ]),
      ),
      actions: [
        TextButton(onPressed: () => Navigator.pop(dCtx), child: Text(isRtl ? 'إغلاق' : 'Close')),
      ],
    ));
  }
}
