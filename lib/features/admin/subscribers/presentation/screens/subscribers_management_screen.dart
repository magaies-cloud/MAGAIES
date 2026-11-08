import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/core/enums/user_role.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/routing/route_names.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import '../../application/subscribers_provider.dart';
import '../../domain/models/subscriber_model.dart';
import '../widgets/subscriber_detail_sheet.dart';
import '../../../presentation/providers/settings_tab_provider.dart';
import 'package:magaiees_marketplace/features/admin/presentation/widgets/admin_error_view.dart';
import 'package:magaiees_marketplace/features/admin/application/ads_provider.dart';

class SubscribersManagementScreen extends ConsumerStatefulWidget {
  const SubscribersManagementScreen({super.key});

  @override
  ConsumerState<SubscribersManagementScreen> createState() => _SubscribersManagementScreenState();
}

class _SubscribersManagementScreenState extends ConsumerState<SubscribersManagementScreen> {
  final TextEditingController _searchController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  UiServerFilter _selectedFilter = UiServerFilter.all;
  bool _isLoading = false;
  Timer? _debounce;
  SubscriberModel? _selectedSubscriber;

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _searchController.dispose();
    _scrollController.dispose();
    _debounce?.cancel();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >= _scrollController.position.maxScrollExtent - 200) {
      final notifier = ref.read(subscribersProvider.notifier);
      if (notifier.hasMore && !notifier.isLoadingMore) {
        notifier.loadMore();
      }
    }
  }

  void _onSearchChanged(String value) {
    _debounce?.cancel();
    _debounce = Timer(const Duration(milliseconds: 400), () {
      ref.read(subscriberQueryProvider.notifier).state =
          ref.read(subscriberQueryProvider).copyWith(searchQuery: value.trim());
    });
  }

  void _onFilterChanged(UiServerFilter filter) {
    setState(() => _selectedFilter = filter);
    ref.read(subscriberQueryProvider.notifier).state =
        ref.read(subscriberQueryProvider).copyWith(filter: filter);
  }

  Future<void> _runAsync(Future<void> Function() action) async {
    setState(() => _isLoading = true);
    try {
      await action();
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final asyncSubscribers = ref.watch(subscribersProvider);
    final allSubscribers = asyncSubscribers.valueOrNull ?? [];

    // If a subscriber is selected, show full-screen detail
    if (_selectedSubscriber != null) {
      // Get the latest version from provider
      final latestSub = allSubscribers.cast<SubscriberModel?>().firstWhere(
        (s) => s?.id == _selectedSubscriber!.id,
        orElse: () => null,
      ) ?? _selectedSubscriber!;

      return PopScope(
        canPop: false,
        onPopInvokedWithResult: (didPop, _) {
          if (!didPop) setState(() => _selectedSubscriber = null);
        },
        child: Scaffold(
          backgroundColor: const Color(0xFFF8FAFC),
          body: Stack(
            children: [
              Column(
                children: [
                  // Top navigation bar
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                    decoration: const BoxDecoration(
                      color: Colors.white,
                      border: Border(bottom: BorderSide(color: Color(0xFFE2E8F0))),
                    ),
                    child: Row(
                      children: [
                        IconButton.filledTonal(
                          onPressed: () => setState(() => _selectedSubscriber = null),
                          icon: Icon(isRtl ? Icons.arrow_forward_rounded : Icons.arrow_back_rounded, size: 20),
                          style: IconButton.styleFrom(
                            backgroundColor: const Color(0xFFEFF6FF),
                            foregroundColor: const Color(0xFF1E40AF),
                          ),
                          tooltip: isRtl ? 'العودة لقائمة المشتركين' : 'Back to Subscribers',
                        ),
                        const SizedBox(width: 12),
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: _getRoleColor(latestSub.role).withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Icon(_getRoleIcon(latestSub.role), color: _getRoleColor(latestSub.role), size: 20),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                isRtl ? (latestSub.nameAr.isNotEmpty ? latestSub.nameAr : latestSub.name) : latestSub.name,
                                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              Text(
                                '${_getRoleName(latestSub.role, isRtl)} • ${latestSub.email}',
                                style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ],
                          ),
                        ),
                        // Quick action chips
                        OutlinedButton.icon(
                          onPressed: () => _showChangeRoleDialog(latestSub),
                          icon: const Icon(Icons.manage_accounts_rounded, size: 16),
                          label: Text(isRtl ? 'تغيير الدور' : 'Change Role', style: const TextStyle(fontSize: 12)),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: const Color(0xFF1E40AF),
                            side: const BorderSide(color: Color(0xFFBFDBFE)),
                            backgroundColor: const Color(0xFFEFF6FF),
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                        ),
                        if (latestSub.role == UserRole.merchant) ...[
                          const SizedBox(width: 8),
                          OutlinedButton.icon(
                            onPressed: () => _showCategoryPermissionsDialog(latestSub),
                            icon: const Icon(Icons.admin_panel_settings_outlined, size: 16),
                            label: Text(isRtl ? 'الأقسام والعمولات' : 'Categories', style: const TextStyle(fontSize: 12)),
                            style: OutlinedButton.styleFrom(
                              foregroundColor: const Color(0xFF059669),
                              side: const BorderSide(color: Color(0xFFA7F3D0)),
                              backgroundColor: const Color(0xFFECFDF5),
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                  // Full-screen detail content
                  Expanded(
                    child: SubscriberDetailSheet(
                      subscriber: latestSub,
                      isEmbedded: true,
                      onOpenCategoryPermissions: () => _showCategoryPermissionsDialog(latestSub),
                      onOpenChangeRole: () => _showChangeRoleDialog(latestSub),
                      onClose: () => setState(() => _selectedSubscriber = null),
                    ),
                  ),
                ],
              ),
              if (_isLoading)
                Container(
                  color: Colors.black.withValues(alpha: 0.15),
                  child: const Center(
                    child: CircularProgressIndicator(color: Color(0xFF1E40AF)),
                  ),
                ),
            ],
          ),
        ),
      );
    }

    final adsAsync = ref.watch(adsProvider);
    final allAds = adsAsync.valueOrNull ?? [];
    final activeAdsCount = allAds.where((a) => a.isActive).length;

    // KPI Metrics calculation
    final totalMerchants = allSubscribers.where((s) => s.role == UserRole.merchant).length;
    final totalBuyers = allSubscribers.where((s) => s.role == UserRole.buyer).length;
    final totalEmployees = allSubscribers.where((s) => s.role == UserRole.employee).length;
    final totalActive = allSubscribers.where((s) => s.status == SubscriberStatus.active).length;
    final totalSuspended = allSubscribers.where((s) => s.status == SubscriberStatus.suspended).length;
    final totalPending = allSubscribers.where((s) => s.status == SubscriberStatus.pendingVerification).length;
    final totalRejected = allSubscribers.where((s) => s.status == SubscriberStatus.rejectedVerification).length;
    final totalAdvertisers = allSubscribers.where((s) => s.isAdvertiser || s.totalAdsCount > 0).length;
    final totalCommercialVolume = allSubscribers.fold<double>(0.0, (sum, s) => sum + s.totalSpent);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: Stack(
        children: [
          SingleChildScrollView(
            controller: _scrollController,
            padding: const EdgeInsets.symmetric(horizontal: 32.0, vertical: 28.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // 1. Executive Top Header with Actions
                _buildHeader(context, isRtl, allSubscribers),
                const SizedBox(height: 24),

                // 2. Modern Compact KPI Cards Bar
                _buildKpiSummaryBar(
                  isRtl: isRtl,
                  totalSubscribers: allSubscribers.length,
                  totalMerchants: totalMerchants,
                  totalBuyers: totalBuyers,
                  totalEmployees: totalEmployees,
                  totalActive: totalActive,
                  totalSuspended: totalSuspended,
                  totalPending: totalPending,
                  totalAdvertisers: totalAdvertisers,
                  activeAdsCount: activeAdsCount,
                  totalCommercialVolume: totalCommercialVolume,
                ),
                const SizedBox(height: 28),

                // 3. Search & Filter Bar with Professional Segmented Controls
                _buildFilterAndSearchBar(
                  isRtl,
                  allSubscribers,
                  totalPending,
                  totalRejected,
                  totalAdvertisers > 0 ? totalAdvertisers : activeAdsCount,
                ),
                const SizedBox(height: 24),

                // 4. Inline Enterprise Subscribers Master Table (100% Span, One Line per User)
                _buildSubscribersTableCard(context, isRtl, asyncSubscribers),
                const SizedBox(height: 40),
              ],
            ),
          ),
          if (_isLoading)
            Container(
              color: Colors.black.withValues(alpha: 0.15),
              child: const Center(
                child: CircularProgressIndicator(color: Color(0xFF1E40AF)),
              ),
            ),
        ],
      ),
    );
  }

  // ===========================================================================
  // 1. EXECUTIVE HEADER
  // ===========================================================================
  Widget _buildHeader(BuildContext context, bool isRtl, List<SubscriberModel> subscribers) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Row(
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF7E22CE), Color(0xFFA855F7)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(14),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF7E22CE).withValues(alpha: 0.25),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: const Icon(Icons.people_alt_rounded, color: Colors.white, size: 24),
            ),
            const SizedBox(width: 14),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  isRtl ? 'إدارة المشتركين وقاعدة العملاء' : 'Subscribers & User Management',
                  style: const TextStyle(
                    fontSize: 26,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF0F172A),
                    letterSpacing: -0.5,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  isRtl
                      ? 'متابعة شاملة لبيانات التجار، المشترين، الموظفين، وحالات النشاط والطلبات'
                      : 'Comprehensive management of merchants, buyers, staff accounts, roles & activity',
                  style: TextStyle(color: Colors.grey.shade600, fontSize: 13, fontWeight: FontWeight.w500),
                ),
              ],
            ),
          ],
        ),
        Row(
          children: [
            // Add New Subscriber Button
            FilledButton.icon(
              style: FilledButton.styleFrom(
                backgroundColor: const Color(0xFF1E40AF),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                elevation: 2,
              ),
              icon: const Icon(Icons.person_add_alt_1_rounded, size: 18),
              label: Text(
                isRtl ? 'إضافة مشترك جديد' : 'Add New User',
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
              ),
              onPressed: () => _showAddSubscriberDialog(context, isRtl),
            ),
            const SizedBox(width: 10),

            // Platform Gamification Rules
            FilledButton.tonalIcon(
              style: FilledButton.styleFrom(
                backgroundColor: const Color(0xFFFEF3C7),
                foregroundColor: const Color(0xFFD97706),
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              icon: const Icon(Icons.stars_rounded, size: 18),
              label: Text(
                isRtl ? 'إعدادات المنصة ونقاط التجار' : 'Platform Rules',
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
              ),
              onPressed: () => _showGamificationSettingsDialog(context, isRtl),
            ),
            const SizedBox(width: 10),

            // Quick Toggle for Merchant Upgrade in Buyer App
            Builder(
              builder: (ctx) {
                final isUpgradeEnabled = ref.watch(merchantUpgradeSettingProvider);
                return Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                  decoration: BoxDecoration(
                    color: isUpgradeEnabled ? const Color(0xFFEFF6FF) : const Color(0xFFFEF2F2),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: isUpgradeEnabled ? const Color(0xFFBFDBFE) : const Color(0xFFFECACA),
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        Icons.storefront_rounded,
                        size: 18,
                        color: isUpgradeEnabled ? const Color(0xFF1D4ED8) : const Color(0xFFDC2626),
                      ),
                      const SizedBox(width: 8),
                      Text(
                        isRtl ? 'الترقية كتاجر:' : 'Merchant Upgrade:',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                          color: isUpgradeEnabled ? const Color(0xFF1E40AF) : const Color(0xFF991B1B),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Switch(
                        value: isUpgradeEnabled,
                        activeThumbColor: const Color(0xFF2563EB),
                        materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        onChanged: (val) {
                          ref.read(merchantUpgradeSettingProvider.notifier).toggleSetting(val);
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(
                                val
                                    ? (isRtl ? 'تم تفعيل وإظهار طلب الترقية كتاجر في تطبيق المشتري' : 'Merchant upgrade option is now visible in Buyer App')
                                    : (isRtl ? 'تم إيقاف وإخفاء طلب الترقية كتاجر من تطبيق المشتري' : 'Merchant upgrade option is now hidden in Buyer App'),
                              ),
                              backgroundColor: val ? const Color(0xFF10B981) : Colors.red,
                              duration: const Duration(seconds: 2),
                              behavior: SnackBarBehavior.floating,
                            ),
                          );
                        },
                      ),
                    ],
                  ),
                );
              },
            ),
            const SizedBox(width: 10),

            // Refresh Button
            IconButton.filledTonal(
              icon: const Icon(Icons.refresh_rounded, size: 20),
              tooltip: isRtl ? 'تحديث فوري' : 'Refresh Data',
              style: IconButton.styleFrom(
                backgroundColor: Colors.white,
                foregroundColor: const Color(0xFF1E40AF),
                padding: const EdgeInsets.all(12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14),
                  side: const BorderSide(color: Color(0xFFE2E8F0)),
                ),
              ),
              onPressed: () => ref.read(subscribersProvider.notifier).loadSubscribers(reset: true),
            ),
          ],
        ),
      ],
    );
  }

  // ===========================================================================
  // 2. COMPACT KPI SUMMARY BAR
  // ===========================================================================
  Widget _buildKpiSummaryBar({
    required bool isRtl,
    required int totalSubscribers,
    required int totalMerchants,
    required int totalBuyers,
    required int totalEmployees,
    required int totalActive,
    required int totalSuspended,
    required int totalPending,
    required int totalAdvertisers,
    required int activeAdsCount,
    required double totalCommercialVolume,
  }) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final double spacing = 14.0;
        final int crossAxisCount = constraints.maxWidth > 1100
            ? 4
            : (constraints.maxWidth > 650 ? 2 : 1);
        final double cardWidth =
            (constraints.maxWidth - (spacing * (crossAxisCount - 1))) / crossAxisCount;

        final formattedVolume = NumberFormat.compact(locale: isRtl ? 'ar' : 'en').format(totalCommercialVolume);

        return Wrap(
          spacing: spacing,
          runSpacing: spacing,
          children: [
            _buildKpiCard(
              title: isRtl ? 'إجمالي المشتركين' : 'Total Users',
              value: '$totalSubscribers',
              icon: Icons.groups_rounded,
              bgColor: const Color(0xFFFAF5FF),
              borderColor: const Color(0xFFE9D5FF),
              iconColor: const Color(0xFF9333EA),
              width: cardWidth,
            ),
            _buildKpiCard(
              title: isRtl ? 'التجار المعتمدين' : 'Merchants',
              value: '$totalMerchants',
              icon: Icons.storefront_rounded,
              bgColor: const Color(0xFFF0FDF4),
              borderColor: const Color(0xFFBBF7D0),
              iconColor: const Color(0xFF059669),
              width: cardWidth,
            ),
            _buildKpiCard(
              title: isRtl ? 'المشترون والعملاء' : 'Buyers',
              value: '$totalBuyers',
              icon: Icons.shopping_bag_rounded,
              bgColor: const Color(0xFFEFF6FF),
              borderColor: const Color(0xFFBFDBFE),
              iconColor: const Color(0xFF2563EB),
              width: cardWidth,
            ),
            // Advertisers & Campaigns KPI Card
            _buildKpiCard(
              title: isRtl ? 'المعلنون والإعلانات' : 'Advertisers & Ads',
              value: isRtl ? '$totalAdvertisers معلن ($activeAdsCount نشط)' : '$totalAdvertisers Adv ($activeAdsCount Ads)',
              icon: Icons.campaign_rounded,
              bgColor: const Color(0xFFFDF4FF),
              borderColor: const Color(0xFFF0ABFC),
              iconColor: const Color(0xFFC026D3),
              width: cardWidth,
            ),
            // Financial Volume KPI Card
            _buildKpiCard(
              title: isRtl ? 'حجم المعاملات المالية' : 'Financial Volume',
              value: '$formattedVolume ج.س',
              icon: Icons.account_balance_wallet_rounded,
              bgColor: const Color(0xFFECFDF5),
              borderColor: const Color(0xFFA7F3D0),
              iconColor: const Color(0xFF047857),
              width: cardWidth,
            ),
            _buildKpiCard(
              title: isRtl ? 'طاقم العمل والموظفين' : 'Staff',
              value: '$totalEmployees',
              icon: Icons.badge_rounded,
              bgColor: const Color(0xFFFFFBEB),
              borderColor: const Color(0xFFFDE68A),
              iconColor: const Color(0xFFD97706),
              width: cardWidth,
            ),
            _buildKpiCard(
              title: isRtl ? 'الحسابات النشطة' : 'Active Accounts',
              value: '$totalActive',
              icon: Icons.check_circle_rounded,
              bgColor: const Color(0xFFECFEFF),
              borderColor: const Color(0xFFA5F3FC),
              iconColor: const Color(0xFF0891B2),
              width: cardWidth,
            ),
            _buildKpiCard(
              title: isRtl ? 'الموقوفين والمعلقين' : 'Suspended / Pending',
              value: '${totalSuspended + totalPending}',
              icon: Icons.warning_amber_rounded,
              bgColor: const Color(0xFFFFF1F2),
              borderColor: const Color(0xFFFECDD3),
              iconColor: const Color(0xFFE11D48),
              width: cardWidth,
            ),
          ],
        );
      },
    );
  }

  Widget _buildKpiCard({
    required String title,
    required String value,
    required IconData icon,
    required Color bgColor,
    required Color borderColor,
    required Color iconColor,
    required double width,
  }) {
    return Container(
      width: width,
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: borderColor, width: 1.2),
        boxShadow: [
          BoxShadow(
            color: iconColor.withValues(alpha: 0.04),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: iconColor,
              borderRadius: BorderRadius.circular(10),
              boxShadow: [
                BoxShadow(
                  color: iconColor.withValues(alpha: 0.25),
                  blurRadius: 6,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Icon(icon, color: Colors.white, size: 18),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    color: Colors.grey.shade700,
                    fontSize: 11.5,
                    fontWeight: FontWeight.w600,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w900,
                    color: Color(0xFF0F172A),
                    letterSpacing: -0.5,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ===========================================================================
  // 3. SEARCH AND PROFESSIONAL FILTER BAR
  // ===========================================================================
  Widget _buildFilterAndSearchBar(
    bool isRtl,
    List<SubscriberModel> subscribers,
    int pendingCount,
    int rejectedCount, [
    int advertisersCount = 0,
  ]) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.03),
            blurRadius: 14,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              // Search input
              Expanded(
                flex: 3,
                child: TextField(
                  controller: _searchController,
                  onChanged: _onSearchChanged,
                  decoration: InputDecoration(
                    hintText: isRtl
                        ? 'ابحث بالاسم، البريد الإلكتروني، رقم الهاتف أو اسم المتجر...'
                        : 'Search by Name, Email, Phone, or Store Name...',
                    hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
                    prefixIcon: const Icon(Icons.search_rounded, color: Color(0xFF1E40AF), size: 20),
                    suffixIcon: _searchController.text.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear, size: 18),
                            onPressed: () {
                              _searchController.clear();
                              _onSearchChanged('');
                              setState(() {});
                            },
                          )
                        : null,
                    filled: true,
                    fillColor: const Color(0xFFF8FAFC),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 14),

              // Total matching counter badge
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                decoration: BoxDecoration(
                  color: const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.person_search_rounded, size: 16, color: Color(0xFF64748B)),
                    const SizedBox(width: 6),
                    Text(
                      '${subscribers.length} ${isRtl ? "مستخدم" : "Users"}',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Color(0xFF334155)),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),

          // Professional Segment Buttons
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _buildSegmentFilterButton(
                  filter: UiServerFilter.all,
                  label: isRtl ? 'كل المشتركين' : 'All Users',
                  icon: Icons.groups_rounded,
                  isRtl: isRtl,
                ),
                const SizedBox(width: 8),
                _buildSegmentFilterButton(
                  filter: UiServerFilter.merchants,
                  label: isRtl ? 'التجار المعتمدين' : 'Merchants',
                  icon: Icons.storefront_rounded,
                  isRtl: isRtl,
                ),
                const SizedBox(width: 8),
                _buildSegmentFilterButton(
                  filter: UiServerFilter.buyers,
                  label: isRtl ? 'المشترين والعملاء' : 'Buyers',
                  icon: Icons.shopping_bag_rounded,
                  isRtl: isRtl,
                ),
                const SizedBox(width: 8),
                // Advertisers Filter Button
                _buildSegmentFilterButton(
                  filter: UiServerFilter.advertisers,
                  label: isRtl ? 'المعلنون وأصحاب الحملات' : 'Advertisers',
                  icon: Icons.campaign_rounded,
                  badgeCount: advertisersCount > 0 ? advertisersCount : null,
                  isRtl: isRtl,
                ),
                const SizedBox(width: 8),
                // Navigate to Settings → Employees tab
                InkWell(
                  borderRadius: BorderRadius.circular(12),
                  onTap: () {
                    ref.read(settingsTabIndexProvider.notifier).state = 6;
                    context.go(RouteNames.adminSettings);
                  },
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFFFBEB),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFFDE68A), width: 1.2),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.badge_rounded, size: 16, color: Color(0xFFD97706)),
                        const SizedBox(width: 6),
                        Text(
                          isRtl ? 'فريق العمل والموظفين' : 'Staff & Employees',
                          style: const TextStyle(
                            color: Color(0xFF92400E),
                            fontWeight: FontWeight.bold,
                            fontSize: 12.5,
                          ),
                        ),
                        const SizedBox(width: 6),
                        const Icon(Icons.arrow_forward_ios_rounded, size: 12, color: Color(0xFFD97706)),
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                _buildSegmentFilterButton(
                  filter: UiServerFilter.pending,
                  label: isRtl ? 'قيد الاعتماد' : 'Pending Verification',
                  icon: Icons.hourglass_top_rounded,
                  badgeCount: pendingCount > 0 ? pendingCount : null,
                  isRtl: isRtl,
                ),
                const SizedBox(width: 8),
                _buildSegmentFilterButton(
                  filter: UiServerFilter.rejected,
                  label: isRtl ? 'الطلبات المرفوضة' : 'Rejected Requests',
                  icon: Icons.cancel_outlined,
                  badgeCount: rejectedCount > 0 ? rejectedCount : null,
                  isRtl: isRtl,
                ),
                const SizedBox(width: 8),
                _buildSegmentFilterButton(
                  filter: UiServerFilter.active,
                  label: isRtl ? 'الحسابات النشطة' : 'Active Only',
                  icon: Icons.check_circle_outline_rounded,
                  isRtl: isRtl,
                ),
                const SizedBox(width: 8),
                _buildSegmentFilterButton(
                  filter: UiServerFilter.suspended,
                  label: isRtl ? 'الموقوفين' : 'Suspended',
                  icon: Icons.block_rounded,
                  isRtl: isRtl,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSegmentFilterButton({
    required UiServerFilter filter,
    required String label,
    required IconData icon,
    required bool isRtl,
    int? badgeCount,
  }) {
    final isSelected = _selectedFilter == filter;
    return InkWell(
      borderRadius: BorderRadius.circular(12),
      onTap: () => _onFilterChanged(filter),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF1E40AF) : const Color(0xFFF8FAFC),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isSelected ? const Color(0xFF1E40AF) : const Color(0xFFE2E8F0),
            width: 1.2,
          ),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: const Color(0xFF1E40AF).withValues(alpha: 0.25),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  ),
                ]
              : null,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              size: 16,
              color: isSelected ? Colors.white : const Color(0xFF64748B),
            ),
            const SizedBox(width: 6),
            Text(
              label,
              style: TextStyle(
                color: isSelected ? Colors.white : const Color(0xFF334155),
                fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
                fontSize: 12.5,
              ),
            ),
            if (badgeCount != null) ...[
              const SizedBox(width: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: isSelected ? Colors.white : const Color(0xFFD97706),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  '$badgeCount',
                  style: TextStyle(
                    color: isSelected ? const Color(0xFF1E40AF) : Colors.white,
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  /// Styled button that navigates to Settings → Employees & Staff tab (index 6)

  // ===========================================================================
  // 4. INLINE ENTERPRISE SUBSCRIBERS MASTER TABLE (100% WIDTH, ONE LINE)
  // ===========================================================================
  Widget _buildSubscribersTableCard(
    BuildContext context,
    bool isRtl,
    AsyncValue<List<SubscriberModel>> asyncSubscribers,
  ) {
    final notifier = ref.watch(subscribersProvider.notifier);

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.04),
            blurRadius: 20,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Table Title Header
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFF7E22CE).withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.people_outline_rounded, color: Color(0xFF7E22CE), size: 20),
                  ),
                  const SizedBox(width: 10),
                  Text(
                    isRtl ? 'سجل بيانات المشتركين والمتاجر الحية' : 'Subscribers & Merchant Directory Ledger',
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF0F172A),
                    ),
                  ),
                ],
              ),
              Text(
                isRtl ? 'جميع التفاصيل والحالات معروضة في سطر واحد شامل' : 'All details & status in a single row',
                style: TextStyle(color: Colors.grey.shade500, fontSize: 12, fontWeight: FontWeight.w500),
              ),
            ],
          ),
          const SizedBox(height: 20),

          // Table Content
          asyncSubscribers.when(
            loading: () => const Padding(
              padding: EdgeInsets.symmetric(vertical: 60.0),
              child: Center(
                child: CircularProgressIndicator(color: Color(0xFF1E40AF)),
              ),
            ),
            error: (err, stack) => AdminErrorView(
              error: err,
              stackTrace: stack,
              onRetry: () => ref.read(subscribersProvider.notifier).loadSubscribers(reset: true),
            ),
            data: (subs) {
              if (subs.isEmpty) {
                return Padding(
                  padding: const EdgeInsets.symmetric(vertical: 60),
                  child: Center(
                    child: Column(
                      children: [
                        Icon(Icons.person_off_rounded, size: 64, color: Colors.grey.shade300),
                        const SizedBox(height: 14),
                        Text(
                          isRtl ? 'لا يوجد مشتركين مطابقين للبحث أو الفلتر' : 'No subscribers found matching query',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600, color: Colors.grey.shade500),
                        ),
                      ],
                    ),
                  ),
                );
              }

              return Column(
                children: [
                  LayoutBuilder(
                    builder: (context, tableConstraints) {
                      final double tableWidth =
                          tableConstraints.maxWidth > 1180 ? tableConstraints.maxWidth : 1180;

                      return SingleChildScrollView(
                        scrollDirection: Axis.horizontal,
                        child: ConstrainedBox(
                          constraints: BoxConstraints(minWidth: tableWidth),
                          child: DataTable(
                            headingRowColor: const WidgetStatePropertyAll(Color(0xFFF8FAFC)),
                            headingTextStyle: const TextStyle(
                              fontWeight: FontWeight.w800,
                              color: Color(0xFF1E293B),
                              fontSize: 13,
                            ),
                            dataRowMaxHeight: 74,
                            columnSpacing: 22,
                            horizontalMargin: 16,
                            dividerThickness: 1,
                            columns: [
                              DataColumn(label: Text(isRtl ? 'المشترك والاسم' : 'Subscriber Name')),
                              DataColumn(label: Text(isRtl ? 'البريد الإلكتروني والهاتف' : 'Contact & Phone')),
                              DataColumn(label: Text(isRtl ? 'نوع الحساب والرتبة' : 'Role & Tier')),
                              DataColumn(label: Text(isRtl ? 'حالة الحساب والنشاط' : 'Status & Activity')),
                              DataColumn(label: Text(isRtl ? 'المتجر وسوق التوريد' : 'Store / Entity')),
                              DataColumn(label: Text(isRtl ? 'حجم الطلبات' : 'Orders Count')),
                              DataColumn(label: Text(isRtl ? 'حجم المعاملات المالية' : 'Financial Volume')),
                              DataColumn(label: Text(isRtl ? 'تاريخ الانضمام' : 'Joined Date')),
                              DataColumn(label: Text(isRtl ? 'الإجراءات السريعة' : 'Inline Actions')),
                            ],
                            rows: subs.map((sub) {
                              final statusColor = _getStatusColor(sub.status);
                              final statusText = _getStatusName(sub.status, isRtl);
                              final roleColor = _getRoleColor(sub.role);
                              final roleName = _getRoleName(sub.role, isRtl);
                              final displayName = isRtl ? (sub.nameAr.isNotEmpty ? sub.nameAr : sub.name) : sub.name;

                              return DataRow(
                                cells: [
                                  // 1. Subscriber Avatar & Name
                                  DataCell(
                                    Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Container(
                                          height: 38,
                                          width: 38,
                                          decoration: BoxDecoration(
                                            gradient: LinearGradient(
                                              colors: [roleColor, roleColor.withValues(alpha: 0.7)],
                                              begin: Alignment.topLeft,
                                              end: Alignment.bottomRight,
                                            ),
                                            shape: BoxShape.circle,
                                            boxShadow: [
                                              BoxShadow(
                                                color: roleColor.withValues(alpha: 0.25),
                                                blurRadius: 6,
                                                offset: const Offset(0, 2),
                                              ),
                                            ],
                                          ),
                                          child: Center(
                                            child: Text(
                                              displayName.isNotEmpty ? displayName[0].toUpperCase() : 'U',
                                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 15),
                                            ),
                                          ),
                                        ),
                                        const SizedBox(width: 12),
                                        Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            Row(
                                              mainAxisSize: MainAxisSize.min,
                                              children: [
                                                Text(
                                                  displayName,
                                                  style: const TextStyle(
                                                    fontWeight: FontWeight.bold,
                                                    color: Color(0xFF0F172A),
                                                    fontSize: 13.5,
                                                  ),
                                                ),
                                                if (sub.isAdvertiser || sub.totalAdsCount > 0) ...[
                                                  const SizedBox(width: 6),
                                                  Container(
                                                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                                    decoration: BoxDecoration(
                                                      color: const Color(0xFFFDF4FF),
                                                      borderRadius: BorderRadius.circular(6),
                                                      border: Border.all(color: const Color(0xFFF0ABFC)),
                                                    ),
                                                    child: Row(
                                                      mainAxisSize: MainAxisSize.min,
                                                      children: [
                                                        const Icon(Icons.campaign_rounded, size: 11, color: Color(0xFFC026D3)),
                                                        const SizedBox(width: 3),
                                                        Text(
                                                          isRtl ? 'معلن (${sub.totalAdsCount})' : 'Ad (${sub.totalAdsCount})',
                                                          style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFFC026D3)),
                                                        ),
                                                      ],
                                                    ),
                                                  ),
                                                ],
                                              ],
                                            ),
                                            Text(
                                              'ID: ${sub.id.length > 8 ? sub.id.substring(0, 8) : sub.id}',
                                              style: TextStyle(color: Colors.grey.shade400, fontSize: 11, fontFamily: 'monospace'),
                                            ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),

                                  // 2. Email & Phone
                                  DataCell(
                                    Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            const Icon(Icons.email_outlined, size: 13, color: Color(0xFF64748B)),
                                            const SizedBox(width: 4),
                                            Text(
                                              sub.email.isNotEmpty ? sub.email : '-',
                                              style: const TextStyle(color: Color(0xFF334155), fontSize: 12, fontWeight: FontWeight.w500),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 2),
                                        Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            const Icon(Icons.phone_outlined, size: 13, color: Color(0xFF64748B)),
                                            const SizedBox(width: 4),
                                            Text(
                                              sub.phone.isNotEmpty ? sub.phone : '-',
                                              style: TextStyle(color: Colors.grey.shade500, fontSize: 11),
                                            ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),

                                  // 3. Role & Tier
                                  DataCell(
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                      decoration: BoxDecoration(
                                        color: roleColor.withValues(alpha: 0.1),
                                        borderRadius: BorderRadius.circular(10),
                                        border: Border.all(color: roleColor.withValues(alpha: 0.25)),
                                      ),
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Icon(_getRoleIcon(sub.role), size: 14, color: roleColor),
                                          const SizedBox(width: 5),
                                          Text(
                                            roleName,
                                            style: TextStyle(color: roleColor, fontSize: 12, fontWeight: FontWeight.bold),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),

                                  // 4. Status & Activity (Active / Suspended / Pending)
                                  DataCell(
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                      decoration: BoxDecoration(
                                        color: statusColor.withValues(alpha: 0.1),
                                        borderRadius: BorderRadius.circular(12),
                                        border: Border.all(color: statusColor.withValues(alpha: 0.25)),
                                      ),
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Container(
                                            width: 7,
                                            height: 7,
                                            decoration: BoxDecoration(color: statusColor, shape: BoxShape.circle),
                                          ),
                                          const SizedBox(width: 6),
                                          Text(
                                            statusText,
                                            style: TextStyle(color: statusColor, fontSize: 12, fontWeight: FontWeight.bold),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),

                                  // 5. Store / Entity
                                  DataCell(
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFF8FAFC),
                                        borderRadius: BorderRadius.circular(8),
                                        border: Border.all(color: const Color(0xFFE2E8F0)),
                                      ),
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Icon(
                                            sub.role == UserRole.merchant ? Icons.storefront_rounded : Icons.person_outline,
                                            size: 14,
                                            color: sub.role == UserRole.merchant ? const Color(0xFF059669) : Colors.grey.shade600,
                                          ),
                                          const SizedBox(width: 5),
                                          ConstrainedBox(
                                            constraints: const BoxConstraints(maxWidth: 140),
                                            child: Text(
                                              sub.storeName != null && sub.storeName!.isNotEmpty
                                                  ? sub.storeName!
                                                  : (sub.role == UserRole.merchant
                                                      ? (isRtl ? 'متجر معتمد' : 'Verified Store')
                                                      : (isRtl ? 'مشتري مباشر' : 'Direct Buyer')),
                                              style: TextStyle(
                                                fontWeight: FontWeight.w600,
                                                fontSize: 11.5,
                                                color: sub.role == UserRole.merchant ? const Color(0xFF065F46) : const Color(0xFF475569),
                                              ),
                                              maxLines: 1,
                                              overflow: TextOverflow.ellipsis,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),

                                  // 6. Orders Count
                                  DataCell(
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFEFF6FF),
                                        borderRadius: BorderRadius.circular(8),
                                        border: Border.all(color: const Color(0xFFBFDBFE)),
                                      ),
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          const Icon(Icons.shopping_bag_outlined, size: 14, color: Color(0xFF1E40AF)),
                                          const SizedBox(width: 4),
                                          Text(
                                            '${sub.totalOrders} ${isRtl ? "طلب" : "Orders"}',
                                            style: const TextStyle(
                                              fontWeight: FontWeight.w800,
                                              fontSize: 12,
                                              color: Color(0xFF1E40AF),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),

                                  // 7. Financial Volume & Ad Spend
                                  DataCell(
                                    Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Text(
                                          '${NumberFormat('#,##0.##', isRtl ? 'ar' : 'en').format(sub.totalSpent)} ج.س',
                                          style: const TextStyle(
                                            fontWeight: FontWeight.w800,
                                            fontSize: 12.5,
                                            color: Color(0xFF0F172A),
                                          ),
                                        ),
                                        if (sub.totalAdSpend > 0)
                                          Text(
                                            isRtl
                                                ? 'إعلانات: ${NumberFormat('#,##0.##', 'ar').format(sub.totalAdSpend)} ج.س'
                                                : 'Ads: ${NumberFormat('#,##0.##', 'en').format(sub.totalAdSpend)} SDG',
                                            style: const TextStyle(fontSize: 10, color: Color(0xFFC026D3), fontWeight: FontWeight.bold),
                                          )
                                        else
                                          Text(
                                            sub.role == UserRole.merchant
                                                ? (isRtl ? 'إجمالي مبيعات' : 'Total Sales')
                                                : (isRtl ? 'إجمالي مشتريات' : 'Total Purchases'),
                                            style: TextStyle(color: Colors.grey.shade500, fontSize: 10.5),
                                          ),
                                      ],
                                    ),
                                  ),

                                  // 8. Joined Date
                                  DataCell(
                                    Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Text(
                                          DateFormat('yyyy-MM-dd').format(sub.joinedAt),
                                          style: const TextStyle(fontWeight: FontWeight.w600, color: Color(0xFF475569), fontSize: 12),
                                        ),
                                        Text(
                                          '${DateTime.now().difference(sub.joinedAt).inDays} ${isRtl ? "يوم" : "days"}',
                                          style: TextStyle(color: Colors.grey.shade400, fontSize: 10.5, fontWeight: FontWeight.w500),
                                        ),
                                      ],
                                    ),
                                  ),

                                  // 9. Inline Actions with PopupMenu
                                  DataCell(
                                    Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        ElevatedButton.icon(
                                          icon: const Icon(Icons.settings_suggest_rounded, size: 16),
                                          label: Text(isRtl ? 'إدارة' : 'Manage', style: const TextStyle(fontSize: 12)),
                                          style: ElevatedButton.styleFrom(
                                            backgroundColor: const Color(0xFF1E40AF),
                                            foregroundColor: Colors.white,
                                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                            elevation: 0,
                                          ),
                                          onPressed: () => _showDetailSheet(context, sub),
                                        ),
                                        const SizedBox(width: 4),
                                        PopupMenuButton<String>(
                                          icon: const Icon(Icons.more_vert_rounded, size: 20, color: Color(0xFF64748B)),
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                          itemBuilder: (ctx) => [
                                            PopupMenuItem(
                                              value: 'role',
                                              child: Row(
                                                children: [
                                                  const Icon(Icons.manage_accounts_rounded, size: 18, color: Color(0xFF1E40AF)),
                                                  const SizedBox(width: 8),
                                                  Text(isRtl ? 'تغيير الدور' : 'Change Role', style: const TextStyle(fontSize: 13)),
                                                ],
                                              ),
                                            ),
                                            if (sub.role == UserRole.merchant)
                                              PopupMenuItem(
                                                value: 'categories',
                                                child: Row(
                                                  children: [
                                                    const Icon(Icons.admin_panel_settings_outlined, size: 18, color: Color(0xFF059669)),
                                                    const SizedBox(width: 8),
                                                    Text(isRtl ? 'الأقسام والعمولات' : 'Categories & Commissions', style: const TextStyle(fontSize: 13)),
                                                  ],
                                                ),
                                              ),
                                            if (sub.isAdvertiser || sub.totalAdsCount > 0)
                                              PopupMenuItem(
                                                value: 'ads',
                                                child: Row(
                                                  children: [
                                                    const Icon(Icons.campaign_rounded, size: 18, color: Color(0xFFC026D3)),
                                                    const SizedBox(width: 8),
                                                    Text(
                                                      isRtl ? 'الحملات الإعلانية (${sub.totalAdsCount})' : 'Ad Campaigns (${sub.totalAdsCount})',
                                                      style: const TextStyle(fontSize: 13),
                                                    ),
                                                  ],
                                                ),
                                              ),
                                            const PopupMenuDivider(),
                                            PopupMenuItem(
                                              value: 'suspend',
                                              child: Row(
                                                children: [
                                                  Icon(
                                                    sub.status == SubscriberStatus.suspended ? Icons.play_arrow_rounded : Icons.pause_rounded,
                                                    size: 18,
                                                    color: sub.status == SubscriberStatus.suspended ? const Color(0xFF059669) : const Color(0xFFD97706),
                                                  ),
                                                  const SizedBox(width: 8),
                                                  Text(
                                                    sub.status == SubscriberStatus.suspended
                                                        ? (isRtl ? 'تنشيط الحساب' : 'Activate')
                                                        : (isRtl ? 'إيقاف الحساب' : 'Suspend'),
                                                    style: const TextStyle(fontSize: 13),
                                                  ),
                                                ],
                                              ),
                                            ),
                                            PopupMenuItem(
                                              value: 'delete',
                                              child: Row(
                                                children: [
                                                  const Icon(Icons.delete_outline_rounded, size: 18, color: Color(0xFFDC2626)),
                                                  const SizedBox(width: 8),
                                                  Text(isRtl ? 'حذف الحساب' : 'Delete', style: const TextStyle(fontSize: 13, color: Color(0xFFDC2626))),
                                                ],
                                              ),
                                            ),
                                          ],
                                          onSelected: (val) {
                                            switch (val) {
                                              case 'role':
                                                _showChangeRoleDialog(sub);
                                                break;
                                              case 'categories':
                                                _showCategoryPermissionsDialog(sub);
                                                break;
                                              case 'ads':
                                                context.go(RouteNames.adminAds);
                                                break;
                                              case 'suspend':
                                                _runAsync(() => ref.read(subscribersProvider.notifier).toggleSuspension(sub.id));
                                                break;
                                              case 'delete':
                                                _showDeleteConfirm(sub);
                                                break;
                                            }
                                          },
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              );
                            }).toList(),
                          ),
                        ),
                      );
                    },
                  ),
                  if (notifier.isLoadingMore) ...[
                    const SizedBox(height: 16),
                    const Center(
                      child: Padding(
                        padding: EdgeInsets.all(8.0),
                        child: CircularProgressIndicator(color: Color(0xFF1E40AF), strokeWidth: 2.5),
                      ),
                    ),
                  ],
                ],
              );
            },
          ),
        ],
      ),
    );
  }

  // ===========================================================================
  // HELPER DIALOGS & METHODS
  // ===========================================================================



  void _showDetailSheet(BuildContext context, SubscriberModel subscriber) {
    setState(() => _selectedSubscriber = subscriber);
  }

  void _showAddSubscriberDialog(BuildContext context, bool isRtl) {
    final nameCtrl = TextEditingController();
    final nameArCtrl = TextEditingController();
    final emailCtrl = TextEditingController();
    final phoneCtrl = TextEditingController();
    final storeNameCtrl = TextEditingController();
    UserRole selectedRole = UserRole.buyer;

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (dlgCtx, setDlgState) {
          return AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            title: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: const Color(0xFF1E40AF).withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.person_add_alt_1_rounded, color: Color(0xFF1E40AF), size: 22),
                ),
                const SizedBox(width: 10),
                Text(
                  isRtl ? 'إضافة مشترك أو تاجر جديد' : 'Add New Subscriber',
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: Color(0xFF0F172A)),
                ),
              ],
            ),
            content: SizedBox(
              width: 480,
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    TextField(
                      controller: nameCtrl,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'الاسم بالإنجليزية (Name En)' : 'English Name',
                        prefixIcon: const Icon(Icons.person_outline, size: 20),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        isDense: true,
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: nameArCtrl,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'الاسم بالعربية (Name Ar)' : 'Arabic Name',
                        prefixIcon: const Icon(Icons.badge_outlined, size: 20),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        isDense: true,
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: emailCtrl,
                      keyboardType: TextInputType.emailAddress,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'البريد الإلكتروني' : 'Email Address',
                        prefixIcon: const Icon(Icons.email_outlined, size: 20),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        isDense: true,
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: phoneCtrl,
                      keyboardType: TextInputType.phone,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'رقم الهاتف' : 'Phone Number',
                        prefixIcon: const Icon(Icons.phone_outlined, size: 20),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        isDense: true,
                      ),
                    ),
                    const SizedBox(height: 14),
                    DropdownButtonFormField<UserRole>(
                      initialValue: selectedRole,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'نوع الحساب والرتبة' : 'Role',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        isDense: true,
                      ),
                      items: UserRole.values.map((role) {
                        return DropdownMenuItem(
                          value: role,
                          child: Text(_getRoleName(role, isRtl)),
                        );
                      }).toList(),
                      onChanged: (v) {
                        if (v != null) setDlgState(() => selectedRole = v);
                      },
                    ),
                    if (selectedRole == UserRole.merchant) ...[
                      const SizedBox(height: 12),
                      TextField(
                        controller: storeNameCtrl,
                        decoration: InputDecoration(
                          labelText: isRtl ? 'اسم المتجر أو الكيان التجاري' : 'Store Name',
                          prefixIcon: const Icon(Icons.storefront_rounded, size: 20),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                          isDense: true,
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(dlgCtx),
                child: Text(isRtl ? 'إلغاء' : 'Cancel', style: TextStyle(color: Colors.grey.shade600)),
              ),
              FilledButton(
                style: FilledButton.styleFrom(
                  backgroundColor: const Color(0xFF1E40AF),
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                onPressed: () async {
                  if (emailCtrl.text.trim().isEmpty || nameCtrl.text.trim().isEmpty) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text(isRtl ? 'يرجى إدخال الاسم والبريد الإلكتروني' : 'Name & Email required')),
                    );
                    return;
                  }
                  Navigator.pop(dlgCtx);
                  await _runAsync(() async {
                    try {
                      await ref.read(subscribersProvider.notifier).createSubscriber(
                        name: nameCtrl.text.trim(),
                        nameAr: nameArCtrl.text.trim(),
                        email: emailCtrl.text.trim(),
                        phone: phoneCtrl.text.trim(),
                        role: selectedRole,
                        storeName: storeNameCtrl.text.trim(),
                      );
                      if (context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(isRtl ? '✅ تم إنشاء المشترك بنجاح' : 'Subscriber created successfully'),
                            backgroundColor: const Color(0xFF059669),
                            behavior: SnackBarBehavior.floating,
                          ),
                        );
                      }
                    } catch (e) {
                      if (context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text('Error: $e'),
                            backgroundColor: const Color(0xFFEF4444),
                            behavior: SnackBarBehavior.floating,
                          ),
                        );
                      }
                    }
                  });
                },
                child: Text(isRtl ? 'إضافة المشترك' : 'Create User'),
              ),
            ],
          );
        },
      ),
    );
  }

    void _showCategoryPermissionsDialog(SubscriberModel subscriber) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    List<String> allowed = [];
    bool allowAll = false;
    final currentPerms = subscriber.permissions['allowed_categories'];
    if (currentPerms is List) {
      allowed = currentPerms.map((e) => e.toString()).toList();
    } else if (currentPerms == 'all' || currentPerms == null) {
      allowAll = true;
    }

    Set<String> selectedIds = allowed.toSet();

    Map<String, double> categoryCommissions = {};
    final rawComms = subscriber.permissions['category_commissions'];
    if (rawComms is Map) {
      rawComms.forEach((k, v) {
        final d = (num.tryParse(v.toString()) ?? 0.0).toDouble();
        categoryCommissions[k.toString()] = d;
      });
    }

    bool directPayment = subscriber.directPaymentEnabled;
    bool autoApprove = subscriber.permissions['auto_approve_products'] == true;
    bool canAddExclusive = subscriber.permissions['can_add_exclusive'] == true;

    bool hasCustomCommission = subscriber.permissions['has_custom_commission'] == true;
    final customCommRate = subscriber.permissions['custom_commission_percentage'];
    final customCommCtrl = TextEditingController(
      text: customCommRate != null ? customCommRate.toString() : '',
    );

    showDialog(
      context: context,
      builder: (ctx) => Consumer(
        builder: (dialogCtx, ref, _) {
          final categoriesAsync = ref.watch(categoriesProvider);
          final cachedCategories = categoriesAsync.valueOrNull ?? [];
          final isLoadingCats = categoriesAsync.isLoading && cachedCategories.isEmpty;

          if (allowAll && cachedCategories.isNotEmpty && selectedIds.isEmpty) {
            for (var c in cachedCategories) {
              selectedIds.add(c.id);
            }
          }

          return StatefulBuilder(
            builder: (innerCtx, setDlgState) {
              return AlertDialog(
                backgroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                titlePadding: const EdgeInsets.fromLTRB(24, 24, 24, 16),
                contentPadding: const EdgeInsets.symmetric(horizontal: 24),
                title: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: const Color(0xFF059669).withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.admin_panel_settings_rounded, color: Color(0xFF059669), size: 24),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            isRtl ? 'صلاحيات الأقسام وعمولات الأصناف' : 'Categories & Custom Commissions',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: Color(0xFF0F172A)),
                          ),
                          Text(
                            '${isRtl ? subscriber.nameAr : subscriber.name} • ${subscriber.storeName ?? (isRtl ? "متجر معتمد" : "Verified Store")}',
                            style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                content: SizedBox(
                  width: 620,
                  child: SingleChildScrollView(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Header & Global Select/Deselect
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF1F5F9),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: const Color(0xFFE2E8F0)),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Row(
                                children: [
                                  const Icon(Icons.account_tree_rounded, size: 18, color: Color(0xFF1E40AF)),
                                  const SizedBox(width: 8),
                                  Text(
                                    isRtl ? 'شجرة الأقسام وعمولات الأصناف' : 'Categories & Rates Tree',
                                    style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                                  ),
                                ],
                              ),
                              Row(
                                children: [
                                  TextButton.icon(
                                    onPressed: () {
                                      setDlgState(() {
                                        for (var c in cachedCategories) {
                                          selectedIds.add(c.id);
                                        }
                                      });
                                    },
                                    icon: const Icon(Icons.select_all_rounded, size: 16),
                                    label: Text(isRtl ? 'تحديد الكل' : 'Select All', style: const TextStyle(fontSize: 12)),
                                    style: TextButton.styleFrom(
                                      foregroundColor: const Color(0xFF059669),
                                      visualDensity: VisualDensity.compact,
                                    ),
                                  ),
                                  const SizedBox(width: 4),
                                  TextButton.icon(
                                    onPressed: () {
                                      setDlgState(() {
                                        selectedIds.clear();
                                      });
                                    },
                                    icon: const Icon(Icons.deselect_rounded, size: 16),
                                    label: Text(isRtl ? 'إلغاء التحديد' : 'Clear All', style: const TextStyle(fontSize: 12)),
                                    style: TextButton.styleFrom(
                                      foregroundColor: Colors.red.shade700,
                                      visualDensity: VisualDensity.compact,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 12),

                        // Categories and Subcategories Tree
                        ConstrainedBox(
                          constraints: const BoxConstraints(maxHeight: 400),
                          child: isLoadingCats
                              ? const Padding(
                                  padding: EdgeInsets.all(32.0),
                                  child: Center(child: CircularProgressIndicator()),
                                )
                              : (cachedCategories.isEmpty
                                  ? Padding(
                                      padding: const EdgeInsets.all(24.0),
                                      child: Center(
                                        child: Text(
                                          isRtl ? 'لا توجد أقسام مسجلة بالسوق حالياً' : 'No categories available',
                                          style: TextStyle(color: Colors.grey.shade600),
                                        ),
                                      ),
                                    )
                                  : Scrollbar(
                                      thumbVisibility: true,
                                      child: ListView.separated(
                                        shrinkWrap: true,
                                        padding: const EdgeInsets.symmetric(vertical: 4),
                                        itemCount: cachedCategories.length,
                                        separatorBuilder: (context, index) => const SizedBox(height: 10),
                                        itemBuilder: (context, index) {
                                          final cat = cachedCategories[index];
                                          final catName = isRtl ? cat.nameAr : cat.name;
                                          final isMainSelected = selectedIds.contains(cat.id);
                                          final catRate = categoryCommissions[cat.id];

                                          int selectedSubCount = 0;
                                          for (var sub in cat.subCategories) {
                                            if (selectedIds.contains(sub.id)) selectedSubCount++;
                                          }

                                          return Container(
                                            decoration: BoxDecoration(
                                              color: isMainSelected ? const Color(0xFFF0FDF4) : Colors.white,
                                              borderRadius: BorderRadius.circular(14),
                                              border: Border.all(
                                                color: isMainSelected ? const Color(0xFF86EFAC) : const Color(0xFFE2E8F0),
                                                width: isMainSelected ? 1.5 : 1.0,
                                              ),
                                              boxShadow: [
                                                BoxShadow(
                                                  color: Colors.black.withValues(alpha: 0.02),
                                                  blurRadius: 4,
                                                  offset: const Offset(0, 2),
                                                ),
                                              ],
                                            ),
                                            child: Theme(
                                              data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
                                              child: ExpansionTile(
                                                initiallyExpanded: true,
                                                tilePadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                                                leading: Checkbox(
                                                  value: isMainSelected,
                                                  activeColor: const Color(0xFF059669),
                                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                                                  onChanged: (val) {
                                                    setDlgState(() {
                                                      if (val == true) {
                                                        selectedIds.add(cat.id);
                                                        for (var sub in cat.subCategories) {
                                                          selectedIds.remove(sub.id);
                                                        }
                                                      } else {
                                                        selectedIds.remove(cat.id);
                                                      }
                                                    });
                                                  },
                                                ),
                                                title: Row(
                                                  children: [
                                                    Expanded(
                                                      child: Text(
                                                        catName,
                                                        style: TextStyle(
                                                          fontWeight: FontWeight.bold,
                                                          fontSize: 14,
                                                          color: isMainSelected ? const Color(0xFF065F46) : const Color(0xFF1E293B),
                                                        ),
                                                      ),
                                                    ),
                                                    // Golden Commission Badge for Category
                                                    InkWell(
                                                      onTap: () async {
                                                        final ctrl = TextEditingController(text: catRate != null ? catRate.toString() : '');
                                                        final res = await showDialog<double?>(
                                                          context: context,
                                                          builder: (dCtx) => AlertDialog(
                                                            title: Row(
                                                              children: [
                                                                const Icon(Icons.percent_rounded, color: Color(0xFFD97706)),
                                                                const SizedBox(width: 8),
                                                                Text(isRtl ? 'عمولة قسم: $catName' : 'Commission: $catName'),
                                                              ],
                                                            ),
                                                            content: Column(
                                                              mainAxisSize: MainAxisSize.min,
                                                              children: [
                                                                TextField(
                                                                  controller: ctrl,
                                                                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                                                  decoration: InputDecoration(
                                                                    labelText: isRtl ? 'نسبة العمولة لهذا القسم (%)' : 'Commission %',
                                                                    hintText: 'مثلاً: 2.5 أو 1 أو 5',
                                                                    border: const OutlineInputBorder(),
                                                                    prefixIcon: const Icon(Icons.percent, color: Color(0xFFD97706)),
                                                                  ),
                                                                ),
                                                                const SizedBox(height: 12),
                                                                Wrap(
                                                                  spacing: 6,
                                                                  children: [0.0, 1.0, 2.5, 5.0, 7.5, 10.0].map((p) => ActionChip(
                                                                    label: Text('$p%'),
                                                                    onPressed: () => ctrl.text = p.toString(),
                                                                  )).toList(),
                                                                ),
                                                              ],
                                                            ),
                                                            actions: [
                                                              TextButton(
                                                                onPressed: () => Navigator.pop(dCtx, -1.0),
                                                                child: Text(isRtl ? 'إزالة المخصص' : 'Reset', style: TextStyle(color: Colors.red.shade600)),
                                                              ),
                                                              TextButton(onPressed: () => Navigator.pop(dCtx, null), child: Text(isRtl ? 'إلغاء' : 'Cancel')),
                                                              FilledButton(
                                                                onPressed: () {
                                                                  final val = double.tryParse(ctrl.text.trim());
                                                                  Navigator.pop(dCtx, val);
                                                                },
                                                                child: Text(isRtl ? 'حفظ النسبة' : 'Save'),
                                                              ),
                                                            ],
                                                          ),
                                                        );
                                                        if (res != null) {
                                                          setDlgState(() {
                                                            if (res < 0) {
                                                                categoryCommissions.remove(cat.id);
                                                            } else {
                                                              categoryCommissions[cat.id] = res;
                                                            }
                                                          });
                                                        }
                                                      },
                                                      borderRadius: BorderRadius.circular(10),
                                                      child: Container(
                                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                                        decoration: BoxDecoration(
                                                          color: catRate != null ? const Color(0xFFFEF3C7) : const Color(0xFFF1F5F9),
                                                          borderRadius: BorderRadius.circular(10),
                                                          border: Border.all(
                                                            color: catRate != null ? const Color(0xFFF59E0B) : const Color(0xFFCBD5E1),
                                                            width: catRate != null ? 1.5 : 1.0,
                                                          ),
                                                        ),
                                                        child: Row(
                                                          mainAxisSize: MainAxisSize.min,
                                                          children: [
                                                            Icon(
                                                              Icons.loyalty_rounded,
                                                              size: 13,
                                                              color: catRate != null ? const Color(0xFFB45309) : const Color(0xFF64748B),
                                                            ),
                                                            const SizedBox(width: 4),
                                                            Text(
                                                              catRate != null ? (isRtl ? 'عمولة: $catRate%' : '$catRate% Comm') : (isRtl ? 'تحديد عمولة القسم' : 'Set Rate'),
                                                              style: TextStyle(
                                                                fontSize: 11,
                                                                fontWeight: FontWeight.bold,
                                                                color: catRate != null ? const Color(0xFFB45309) : const Color(0xFF475569),
                                                              ),
                                                            ),
                                                            const SizedBox(width: 4),
                                                            Icon(Icons.edit, size: 11, color: catRate != null ? const Color(0xFFB45309) : const Color(0xFF94A3B8)),
                                                          ],
                                                        ),
                                                      ),
                                                    ),
                                                  ],
                                                ),
                                                subtitle: Text(
                                                  isMainSelected
                                                      ? (isRtl ? '✓ مصرح بكافة الأصناف الفرعية (${cat.subCategories.length} صنف)' : 'All subcategories authorized')
                                                      : (selectedSubCount > 0
                                                          ? (isRtl ? 'مصرح بـ $selectedSubCount أصناف فرعية' : '$selectedSubCount subcategories authorized')
                                                          : (isRtl ? 'انقر لتحديد أصناف فرعية معينة' : 'Click to select subcategories')),
                                                  style: TextStyle(
                                                    fontSize: 11,
                                                    color: isMainSelected ? const Color(0xFF059669) : const Color(0xFF64748B),
                                                    fontWeight: isMainSelected ? FontWeight.w600 : FontWeight.normal,
                                                  ),
                                                ),
                                                children: [
                                                  if (cat.subCategories.isNotEmpty) ...[
                                                    const Divider(height: 1),
                                                    Container(
                                                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                                                      color: const Color(0xFFF8FAFC),
                                                      child: Column(
                                                        children: cat.subCategories.map((sub) {
                                                          final subName = isRtl ? sub.nameAr : sub.name;
                                                          final isSubSelected = isMainSelected || selectedIds.contains(sub.id);
                                                          final subRate = categoryCommissions[sub.id];

                                                          return Container(
                                                            margin: const EdgeInsets.only(bottom: 6),
                                                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                                            decoration: BoxDecoration(
                                                              color: isSubSelected ? Colors.white : const Color(0xFFF1F5F9),
                                                              borderRadius: BorderRadius.circular(10),
                                                              border: Border.all(
                                                                color: isSubSelected ? const Color(0xFF93C5FD) : const Color(0xFFE2E8F0),
                                                              ),
                                                            ),
                                                            child: Row(
                                                              children: [
                                                                Checkbox(
                                                                  value: isSubSelected,
                                                                  activeColor: const Color(0xFF2563EB),
                                                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                                                                  onChanged: (val) {
                                                                    setDlgState(() {
                                                                      if (val == true) {
                                                                        selectedIds.add(sub.id);
                                                                      } else {
                                                                        selectedIds.remove(sub.id);
                                                                        selectedIds.remove(cat.id);
                                                                      }
                                                                    });
                                                                  },
                                                                ),
                                                                Expanded(
                                                                  child: Text(
                                                                    subName,
                                                                    style: TextStyle(
                                                                      fontSize: 12,
                                                                      fontWeight: isSubSelected ? FontWeight.bold : FontWeight.w500,
                                                                      color: isSubSelected ? const Color(0xFF1E3A8A) : const Color(0xFF64748B),
                                                                    ),
                                                                  ),
                                                                ),
                                                                // Subcategory Gold Commission % Chip
                                                                InkWell(
                                                                  onTap: () async {
                                                                    final ctrl = TextEditingController(text: subRate != null ? subRate.toString() : '');
                                                                    final res = await showDialog<double?>(
                                                                      context: context,
                                                                      builder: (dCtx) => AlertDialog(
                                                                        title: Row(
                                                                          children: [
                                                                            const Icon(Icons.percent_rounded, color: Color(0xFFD97706)),
                                                                            const SizedBox(width: 8),
                                                                            Text(isRtl ? 'عمولة الصنف: $subName' : 'Commission: $subName'),
                                                                          ],
                                                                        ),
                                                                        content: Column(
                                                                          mainAxisSize: MainAxisSize.min,
                                                                          children: [
                                                                            TextField(
                                                                              controller: ctrl,
                                                                              keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                                                              decoration: InputDecoration(
                                                                                labelText: isRtl ? 'نسبة العمولة لهذا الصنف الفرعي (%)' : 'Commission %',
                                                                                hintText: 'مثلاً: 1 أو 2.5 أو 5',
                                                                                border: const OutlineInputBorder(),
                                                                                prefixIcon: const Icon(Icons.percent, color: Color(0xFFD97706)),
                                                                              ),
                                                                            ),
                                                                            const SizedBox(height: 12),
                                                                            Wrap(
                                                                              spacing: 6,
                                                                              children: [0.0, 1.0, 2.0, 3.0, 5.0, 7.5].map((p) => ActionChip(
                                                                                label: Text('$p%'),
                                                                                onPressed: () => ctrl.text = p.toString(),
                                                                              )).toList(),
                                                                            ),
                                                                          ],
                                                                        ),
                                                                        actions: [
                                                                          TextButton(
                                                                            onPressed: () => Navigator.pop(dCtx, -1.0),
                                                                            child: Text(isRtl ? 'إزالة' : 'Reset', style: TextStyle(color: Colors.red.shade600)),
                                                                          ),
                                                                          TextButton(onPressed: () => Navigator.pop(dCtx, null), child: Text(isRtl ? 'إلغاء' : 'Cancel')),
                                                                          FilledButton(
                                                                            onPressed: () {
                                                                              final val = double.tryParse(ctrl.text.trim());
                                                                              Navigator.pop(dCtx, val);
                                                                            },
                                                                            child: Text(isRtl ? 'حفظ النسبة' : 'Save'),
                                                                          ),
                                                                        ],
                                                                      ),
                                                                    );
                                                                    if (res != null) {
                                                                      setDlgState(() {
                                                                        if (res < 0) {
                                                                          categoryCommissions.remove(sub.id);
                                                                        } else {
                                                                          categoryCommissions[sub.id] = res;
                                                                          selectedIds.add(sub.id);
                                                                        }
                                                                      });
                                                                    }
                                                                  },
                                                                  borderRadius: BorderRadius.circular(8),
                                                                  child: Container(
                                                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                                                    decoration: BoxDecoration(
                                                                      color: subRate != null ? const Color(0xFFFEF3C7) : Colors.white,
                                                                      borderRadius: BorderRadius.circular(8),
                                                                      border: Border.all(
                                                                        color: subRate != null ? const Color(0xFFF59E0B) : const Color(0xFFCBD5E1),
                                                                        width: subRate != null ? 1.5 : 1.0,
                                                                      ),
                                                                    ),
                                                                    child: Row(
                                                                      mainAxisSize: MainAxisSize.min,
                                                                      children: [
                                                                        Text(
                                                                          subRate != null ? (isRtl ? '🎯 عمولة: $subRate%' : '🎯 $subRate%') : (isRtl ? '+ عمولة الصنف' : '+ Rate'),
                                                                          style: TextStyle(
                                                                            fontSize: 10.5,
                                                                            fontWeight: FontWeight.bold,
                                                                            color: subRate != null ? const Color(0xFFB45309) : const Color(0xFF2563EB),
                                                                          ),
                                                                        ),
                                                                        const SizedBox(width: 3),
                                                                        Icon(Icons.edit, size: 10, color: subRate != null ? const Color(0xFFB45309) : const Color(0xFF94A3B8)),
                                                                      ],
                                                                    ),
                                                                  ),
                                                                ),
                                                              ],
                                                            ),
                                                          );
                                                        }).toList(),
                                                      ),
                                                    ),
                                                  ]
                                                ],
                                              ),
                                            ),
                                          );
                                        },
                                      ),
                                    )),
                        ),
                        const SizedBox(height: 16),

                        // Section 2: Store Overall Commission & Publishing
                        Row(
                          children: [
                            const Icon(Icons.payments_rounded, size: 18, color: Color(0xFFD97706)),
                            const SizedBox(width: 10),
                            Text(
                              isRtl ? 'صلاحيات النشر وعمولة المتجر العامة' : 'Publishing & Store Commission',
                              style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Container(
                          decoration: BoxDecoration(
                            color: const Color(0xFFF8FAFC),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: const Color(0xFFE2E8F0)),
                          ),
                          child: Column(
                            children: [
                              SwitchListTile(
                                title: Text(
                                  isRtl ? 'تفعيل استلام الدفع المباشر للتاجر' : 'Enable Direct Payment to Merchant',
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                ),
                                subtitle: Text(
                                  isRtl ? 'تحويل مدفوعات الطلبات مباشرة لحساب التاجر البنكي' : 'Payments go directly to merchant',
                                  style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                                ),
                                value: directPayment,
                                activeTrackColor: const Color(0xFFD97706),
                                onChanged: (val) => setDlgState(() => directPayment = val),
                              ),
                              const Divider(height: 1),
                              SwitchListTile(
                                title: Text(
                                  isRtl ? 'النشر التلقائي للمنتجات بدون مراجعة' : 'Auto-approve Merchant Products',
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                ),
                                subtitle: Text(
                                  isRtl ? 'تظهر منتجات التاجر بالسوق فور إضافتها بدون انتظار موافقة' : 'Products live immediately',
                                  style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                                ),
                                value: autoApprove,
                                activeTrackColor: const Color(0xFF1E40AF),
                                onChanged: (val) => setDlgState(() => autoApprove = val),
                              ),
                              const Divider(height: 1),
                              SwitchListTile(
                                title: Text(
                                  isRtl ? 'تخصيص نسبة عمولة عامة لهذا التاجر' : 'Custom Global Commission for Store',
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFFD97706)),
                                ),
                                subtitle: Text(
                                  isRtl
                                      ? 'تطبق كنسبة عامة لمنتجات هذا المتجر التي ليس لها عمولة صنف مخصصة'
                                      : 'Applies to items without a specific category rate',
                                  style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                                ),
                                value: hasCustomCommission,
                                activeTrackColor: const Color(0xFFD97706),
                                onChanged: (val) => setDlgState(() => hasCustomCommission = val),
                              ),
                              if (hasCustomCommission) ...[
                                Padding(
                                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
                                  child: Row(
                                    children: [
                                      Expanded(
                                        child: TextField(
                                          controller: customCommCtrl,
                                          keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                          decoration: InputDecoration(
                                            labelText: isRtl ? 'نسبة العمولة العامة للمتجر (%) *' : 'Store Commission (%) *',
                                            hintText: isRtl ? 'مثلاً: 2.5 أو 0' : 'e.g. 2.5 or 0',
                                            border: const OutlineInputBorder(),
                                            prefixIcon: const Icon(Icons.percent, color: Color(0xFFD97706)),
                                            isDense: true,
                                          ),
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      Wrap(
                                        spacing: 4,
                                        children: [0.0, 2.5, 5.0, 7.5, 10.0].map((p) => ActionChip(
                                          label: Text('$p%'),
                                          onPressed: () => setDlgState(() => customCommCtrl.text = p.toString()),
                                        )).toList(),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                actionsPadding: const EdgeInsets.fromLTRB(24, 0, 24, 24),
                actions: [
                  TextButton(
                    onPressed: () => Navigator.pop(dialogCtx),
                    child: Text(isRtl ? 'إلغاء' : 'Cancel', style: TextStyle(color: Colors.grey.shade700)),
                  ),
                  FilledButton(
                    onPressed: () async {
                      final notifier = ref.read(subscribersProvider.notifier);
                      Navigator.pop(dialogCtx);
                      await _runAsync(() async {
                        try {
                          Map<String, dynamic> newPerms = Map<String, dynamic>.from(subscriber.permissions);
                          final isAllCatsChecked = cachedCategories.isNotEmpty && cachedCategories.every((c) => selectedIds.contains(c.id));

                          if (isAllCatsChecked && selectedIds.length == cachedCategories.length) {
                            newPerms['allowed_categories'] = 'all';
                          } else {
                            newPerms['allowed_categories'] = selectedIds.toList();
                          }
                          newPerms['category_commissions'] = categoryCommissions;
                          newPerms['auto_approve_products'] = autoApprove;
                          newPerms['can_add_exclusive'] = canAddExclusive;
                          final customPct = hasCustomCommission ? (double.tryParse(customCommCtrl.text.trim()) ?? 0.0) : null;
                          newPerms['has_custom_commission'] = hasCustomCommission;
                          newPerms['custom_commission_percentage'] = customPct;

                          await notifier.updatePermissions(subscriber.id, newPerms);
                          await notifier.toggleDirectPayment(subscriber.id, directPayment);
                          await Supabase.instance.client
                              .from('stores')
                              .update({
                                'custom_commission_percentage': customPct,
                                'has_custom_commission': hasCustomCommission,
                              })
                              .eq('owner_id', subscriber.id);

                          if (mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(isRtl ? 'تم حفظ الصلاحيات وعمولات الأصناف بنجاح' : 'Permissions & Category rates updated successfully'),
                                backgroundColor: Colors.teal.shade600,
                              ),
                            );
                          }
                        } catch (e) {
                          if (mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text('Error: $e'),
                                backgroundColor: Colors.red,
                              ),
                            );
                          }
                        }
                      });
                    },
                    style: FilledButton.styleFrom(
                      backgroundColor: const Color(0xFF1E40AF),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                    ),
                    child: Text(isRtl ? 'حفظ الصلاحيات والعمولات' : 'Save Permissions & Rates'),
                  ),
                ],
              );
            },
          );
        },
      ),
    );
  }

    void _showChangeRoleDialog(SubscriberModel subscriber) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    showDialog(
      context: context,
      builder: (dlgContext) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: Text(isRtl ? 'تغيير صلاحية ورتبة المستخدم' : 'Change User Role', style: const TextStyle(color: Color(0xFF0F172A), fontWeight: FontWeight.bold)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: UserRole.values
                .where((r) => r != UserRole.admin && r != UserRole.guest)
                .map((role) {
              return ListTile(
                leading: Icon(_getRoleIcon(role), color: _getRoleColor(role)),
                title: Text(_getRoleName(role, isRtl), style: const TextStyle(fontWeight: FontWeight.w600)),
                trailing: subscriber.role == role ? const Icon(Icons.check_circle, color: Color(0xFF1E40AF)) : null,
                onTap: () async {
                  Navigator.pop(dlgContext);
                  await _runAsync(() => ref.read(subscribersProvider.notifier).changeRole(subscriber.id, role));
                  if (!mounted) return;
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(isRtl ? 'تم تغيير الصلاحية بنجاح' : 'Role updated successfully'),
                      backgroundColor: Colors.teal.shade600,
                      behavior: SnackBarBehavior.floating,
                    ),
                  );
                  // Auto-redirect to employees tab when role changed to employee
                  if (role == UserRole.employee) {
                    setState(() => _selectedSubscriber = null);
                    ref.read(settingsTabIndexProvider.notifier).state = 6;
                    context.go(RouteNames.adminSettings);
                  }
                },
              );
            }).toList(),
          ),
        );
      },
    );
  }

  void _showDeleteConfirm(SubscriberModel subscriber) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: Colors.red.shade600),
            const SizedBox(width: 8),
            Text(isRtl ? 'تأكيد الحذف' : 'Confirm Deletion', style: const TextStyle(color: Color(0xFF0F172A))),
          ],
        ),
        content: Text(
          isRtl
              ? 'هل أنت متأكد من حذف هذا المستخدم؟ سيتم إلغاء تفعيل حسابه بأمان.'
              : 'Are you sure you want to remove this user account?',
          style: const TextStyle(fontSize: 15),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(isRtl ? 'إلغاء' : 'Cancel', style: TextStyle(color: Colors.grey.shade700)),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(context);
              await _runAsync(() => ref.read(subscribersProvider.notifier).removeSubscriber(subscriber.id));
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red.shade600,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            child: Text(isRtl ? 'حذف' : 'Delete'),
          ),
        ],
      ),
    );
  }

  Future<void> _showGamificationSettingsDialog(BuildContext context, bool isRtl) async {
    final pointsPerOrderCtrl = TextEditingController();
    final salesThresholdCtrl = TextEditingController();
    final pointsPerSalesCtrl = TextEditingController();
    final penaltyCtrl = TextEditingController();
    final commissionCtrl = TextEditingController();
    bool enableCommission = false;
    bool enableCoupons = true;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => const Center(child: CircularProgressIndicator(color: Color(0xFF1E40AF))),
    );

    try {
      final res = await Supabase.instance.client.from('gamification_settings').select().eq('id', 1).maybeSingle();
      if (res != null) {
        pointsPerOrderCtrl.text = res['points_per_order']?.toString() ?? '10';
        salesThresholdCtrl.text = res['sales_threshold_sdg']?.toString() ?? '50000';
        pointsPerSalesCtrl.text = res['points_per_sales_threshold']?.toString() ?? '1';
        penaltyCtrl.text = res['penalty_cancel_order']?.toString() ?? '20';
        commissionCtrl.text = res['commission_percentage']?.toString() ?? '0.0';
        enableCommission = res['enable_commission'] == true;
        enableCoupons = res['enable_coupons'] ?? true;
      }
    } catch (_) {
      pointsPerOrderCtrl.text = '10';
      salesThresholdCtrl.text = '50000';
      pointsPerSalesCtrl.text = '1';
      penaltyCtrl.text = '20';
      commissionCtrl.text = '0.0';
    }

    if (context.mounted) Navigator.pop(context);
    if (!context.mounted) return;

    await showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setState) {
          return AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            title: Row(
              children: [
                const Icon(Icons.stars_rounded, color: Colors.amber, size: 28),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    isRtl ? 'إعدادات المنصة والتجار' : 'Platform Settings',
                    style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                  ),
                ),
              ],
            ),
            content: SizedBox(
              width: double.maxFinite,
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildSettingsHeader(isRtl ? 'الكوبونات والعروض' : 'Coupons & Offers', Icons.local_offer_outlined, Colors.purple),
                    SwitchListTile(
                      title: Text(isRtl ? 'تفعيل نظام الكوبونات' : 'Enable Coupons System', style: const TextStyle(fontWeight: FontWeight.w600)),
                      subtitle: Text(
                        isRtl ? 'السماح بإنشاء أكواد خصم' : 'Allow promo codes creation',
                        style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                      ),
                      value: enableCoupons,
                      activeTrackColor: const Color(0xFF1E40AF),
                      onChanged: (v) => setState(() => enableCoupons = v),
                      contentPadding: EdgeInsets.zero,
                    ),
                    const Divider(height: 24),

                    _buildSettingsHeader(isRtl ? 'العمولات' : 'Commissions', Icons.percent_rounded, Colors.blue),
                    SwitchListTile(
                      title: Text(isRtl ? 'تفعيل نظام العمولات' : 'Enable Commission System', style: const TextStyle(fontWeight: FontWeight.w600)),
                      value: enableCommission,
                      activeTrackColor: const Color(0xFF1E40AF),
                      onChanged: (v) => setState(() => enableCommission = v),
                      contentPadding: EdgeInsets.zero,
                    ),
                    if (enableCommission) ...[
                      const SizedBox(height: 12),
                      _buildSettingsTextField(
                        controller: commissionCtrl,
                        label: isRtl ? 'نسبة العمولة %' : 'Commission %',
                        icon: Icons.pie_chart_outline,
                        isNumber: true,
                      ),
                    ],
                    const Divider(height: 32),

                    _buildSettingsHeader(isRtl ? 'نقاط التدرج (Gamification)' : 'Gamification Rules', Icons.emoji_events_outlined, Colors.amber.shade700),
                    const SizedBox(height: 12),
                    _buildSettingsTextField(
                      controller: pointsPerOrderCtrl,
                      label: isRtl ? 'النقاط لاكتمال الطلب' : 'Points per order',
                      icon: Icons.check_circle_outline,
                      isNumber: true,
                    ),
                    const SizedBox(height: 12),
                    _buildSettingsTextField(
                      controller: salesThresholdCtrl,
                      label: isRtl ? 'حد المبيعات (ج.س)' : 'Sales Threshold (SDG)',
                      icon: Icons.monetization_on_outlined,
                      isNumber: true,
                    ),
                    const SizedBox(height: 12),
                    _buildSettingsTextField(
                      controller: pointsPerSalesCtrl,
                      label: isRtl ? 'النقاط لكل حد مبيعات' : 'Points per threshold',
                      icon: Icons.star_outline,
                      isNumber: true,
                    ),
                    const SizedBox(height: 12),
                    _buildSettingsTextField(
                      controller: penaltyCtrl,
                      label: isRtl ? 'خصم لإلغاء الطلب' : 'Cancel penalty',
                      icon: Icons.cancel_outlined,
                      isNumber: true,
                    ),
                  ],
                ),
              ),
            ),
            actionsPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: Text(isRtl ? 'إلغاء' : 'Cancel', style: TextStyle(color: Colors.grey.shade700)),
              ),
              ElevatedButton(
                onPressed: () async {
                  Navigator.pop(ctx);
                  await _runAsync(() async {
                    try {
                      await Supabase.instance.client.from('gamification_settings').upsert({
                        'id': 1,
                        'points_per_order': int.tryParse(pointsPerOrderCtrl.text) ?? 10,
                        'sales_threshold_sdg': int.tryParse(salesThresholdCtrl.text) ?? 50000,
                        'points_per_sales_threshold': int.tryParse(pointsPerSalesCtrl.text) ?? 1,
                        'penalty_cancel_order': int.tryParse(penaltyCtrl.text) ?? 20,
                        'enable_commission': enableCommission,
                        'commission_percentage': double.tryParse(commissionCtrl.text) ?? 0.0,
                        'enable_coupons': enableCoupons,
                      });
                      if (context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(isRtl ? 'تم الحفظ بنجاح' : 'Saved successfully'),
                            backgroundColor: Colors.teal.shade600,
                          ),
                        );
                      }
                    } catch (e) {
                      if (context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text('Error: $e'),
                            backgroundColor: Colors.red,
                          ),
                        );
                      }
                    }
                  });
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF1E40AF),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                ),
                child: Text(isRtl ? 'حفظ التعديلات' : 'Save Changes'),
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildSettingsHeader(String title, IconData icon, Color color) {
    return Row(
      children: [
        Icon(icon, color: color, size: 20),
        const SizedBox(width: 8),
        Text(title, style: TextStyle(color: color, fontWeight: FontWeight.bold, fontSize: 15)),
      ],
    );
  }

  Widget _buildSettingsTextField({required TextEditingController controller, required String label, required IconData icon, bool isNumber = false}) {
    return TextField(
      controller: controller,
      keyboardType: isNumber ? const TextInputType.numberWithOptions(decimal: true) : TextInputType.text,
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon, color: Colors.grey.shade500, size: 20),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide(color: Colors.grey.shade300)),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide(color: Colors.grey.shade300)),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: Color(0xFF1E40AF))),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        isDense: true,
      ),
    );
  }

  IconData _getRoleIcon(UserRole role) {
    switch (role) {
      case UserRole.admin: return Icons.admin_panel_settings_rounded;
      case UserRole.merchant: return Icons.storefront_rounded;
      case UserRole.buyer: return Icons.shopping_bag_rounded;
      case UserRole.guest: return Icons.person_outline_rounded;
      case UserRole.employee: return Icons.badge_rounded;
    }
  }

  String _getRoleName(UserRole role, bool isRtl) {
    switch (role) {
      case UserRole.admin: return isRtl ? 'مدير نظام' : 'Admin';
      case UserRole.merchant: return isRtl ? 'تاجر معتمد' : 'Merchant';
      case UserRole.buyer: return isRtl ? 'مشتري' : 'Buyer';
      case UserRole.guest: return isRtl ? 'زائر' : 'Guest';
      case UserRole.employee: return isRtl ? 'موظف منصة' : 'Employee';
    }
  }

  Color _getRoleColor(UserRole role) {
    switch (role) {
      case UserRole.admin: return const Color(0xFF7E22CE);
      case UserRole.merchant: return const Color(0xFF059669);
      case UserRole.buyer: return const Color(0xFF1E40AF);
      case UserRole.guest: return const Color(0xFF64748B);
      case UserRole.employee: return const Color(0xFFD97706);
    }
  }

  String _getStatusName(SubscriberStatus status, bool isRtl) {
    switch (status) {
      case SubscriberStatus.active: return isRtl ? 'نشط' : 'Active';
      case SubscriberStatus.suspended: return isRtl ? 'موقوف' : 'Suspended';
      case SubscriberStatus.pendingVerification: return isRtl ? 'قيد المراجعة' : 'Pending';
      case SubscriberStatus.rejectedVerification: return isRtl ? 'طلب مرفوض' : 'Rejected';
    }
  }

  Color _getStatusColor(SubscriberStatus status) {
    switch (status) {
      case SubscriberStatus.active: return const Color(0xFF10B981);
      case SubscriberStatus.suspended: return const Color(0xFFEF4444);
      case SubscriberStatus.pendingVerification: return const Color(0xFFF59E0B);
      case SubscriberStatus.rejectedVerification: return const Color(0xFFE11D48);
    }
  }
}


