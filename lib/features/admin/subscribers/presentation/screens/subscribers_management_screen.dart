import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/core/enums/user_role.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/category_model.dart';
import 'package:magaiees_marketplace/routing/route_names.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import '../../application/subscribers_provider.dart';
import '../../domain/models/subscriber_model.dart';
import '../widgets/subscriber_detail_sheet.dart';
import '../../../presentation/providers/settings_tab_provider.dart';
import 'package:magaiees_marketplace/features/admin/presentation/widgets/admin_error_view.dart';
import 'package:magaiees_marketplace/features/admin/application/ads_provider.dart';
import 'package:magaiees_marketplace/core/utils/phone_formatter.dart';

class SubscribersManagementScreen extends ConsumerStatefulWidget {
  const SubscribersManagementScreen({super.key});

  @override
  ConsumerState<SubscribersManagementScreen> createState() => _SubscribersManagementScreenState();
}

class _SubscribersManagementScreenState extends ConsumerState<SubscribersManagementScreen> {
  final TextEditingController _searchController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  UiServerFilter _selectedFilter = UiServerFilter.all;
  String _sortBy = 'newest'; // 'newest', 'highest_volume', 'most_orders', 'name_asc'
  bool _showColorLegend = true;
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
    setState(() {});
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

  List<SubscriberModel> _applyLocalSort(List<SubscriberModel> list, bool isRtl) {
    final sorted = List<SubscriberModel>.from(list);
    switch (_sortBy) {
      case 'highest_volume':
        sorted.sort((a, b) => b.totalSpent.compareTo(a.totalSpent));
        break;
      case 'most_orders':
        sorted.sort((a, b) => b.totalOrders.compareTo(a.totalOrders));
        break;
      case 'name_asc':
        sorted.sort((a, b) {
          final nameA = isRtl ? (a.nameAr.isNotEmpty ? a.nameAr : a.name) : a.name;
          final nameB = isRtl ? (b.nameAr.isNotEmpty ? b.nameAr : b.name) : b.name;
          return nameA.toLowerCase().compareTo(nameB.toLowerCase());
        });
        break;
      case 'newest':
      default:
        sorted.sort((a, b) => b.joinedAt.compareTo(a.joinedAt));
        break;
    }
    return sorted;
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
      final latestSub = allSubscribers.cast<SubscriberModel?>().firstWhere(
        (s) => s?.id == _selectedSubscriber!.id,
        orElse: () => null,
      ) ?? _selectedSubscriber!;

      final roleColor = _getRoleColor(latestSub.role);
      final statusColor = _getStatusColor(latestSub.status);

      return PopScope(
        canPop: false,
        onPopInvokedWithResult: (didPop, _) {
          if (!didPop) setState(() => _selectedSubscriber = null);
        },
        child: Scaffold(
          backgroundColor: const Color(0xFFF1F5F9),
          body: Stack(
            children: [
              Column(
                children: [
                  // Enhanced Color-Coded Top Navigation Bar for Detail View
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      border: Border(
                        top: BorderSide(color: roleColor, width: 4),
                        bottom: const BorderSide(color: Color(0xFFE2E8F0)),
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: roleColor.withValues(alpha: 0.06),
                          blurRadius: 12,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Wrap(
                      alignment: WrapAlignment.spaceBetween,
                      crossAxisAlignment: WrapCrossAlignment.center,
                      spacing: 12,
                      runSpacing: 10,
                      children: [
                        Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            FilledButton.tonalIcon(
                              onPressed: () => setState(() => _selectedSubscriber = null),
                              icon: Icon(isRtl ? Icons.arrow_forward_rounded : Icons.arrow_back_rounded, size: 18),
                              label: Text(
                                isRtl ? 'العودة لقائمة المشتركين' : 'Back to List',
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5),
                              ),
                              style: FilledButton.styleFrom(
                                backgroundColor: const Color(0xFFEFF6FF),
                                foregroundColor: const Color(0xFF1E40AF),
                                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              ),
                            ),
                            const SizedBox(width: 14),
                            Container(
                              padding: const EdgeInsets.all(10),
                              decoration: BoxDecoration(
                                color: roleColor.withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(color: roleColor.withValues(alpha: 0.3)),
                              ),
                              child: Icon(_getRoleIcon(latestSub.role), color: roleColor, size: 22),
                            ),
                            const SizedBox(width: 12),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Text(
                                      isRtl ? (latestSub.nameAr.isNotEmpty ? latestSub.nameAr : latestSub.name) : latestSub.name,
                                      style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
                                    ),
                                    const SizedBox(width: 8),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: roleColor.withValues(alpha: 0.1),
                                        borderRadius: BorderRadius.circular(6),
                                        border: Border.all(color: roleColor.withValues(alpha: 0.3)),
                                      ),
                                      child: Text(
                                        _getRoleName(latestSub.role, isRtl),
                                        style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: roleColor),
                                      ),
                                    ),
                                    const SizedBox(width: 6),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: statusColor.withValues(alpha: 0.1),
                                        borderRadius: BorderRadius.circular(6),
                                        border: Border.all(color: statusColor.withValues(alpha: 0.3)),
                                      ),
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Container(
                                            width: 6,
                                            height: 6,
                                            decoration: BoxDecoration(color: statusColor, shape: BoxShape.circle),
                                          ),
                                          const SizedBox(width: 4),
                                          Text(
                                            _getStatusName(latestSub.status, isRtl),
                                            style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: statusColor),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 3),
                                Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Text(
                                      latestSub.membershipNumber.isNotEmpty ? latestSub.membershipNumber : latestSub.id.substring(0, 8),
                                      style: TextStyle(fontSize: 12, color: Colors.grey.shade600, fontWeight: FontWeight.w600),
                                    ),
                                    Text(' • ', style: TextStyle(fontSize: 12, color: Colors.grey.shade500)),
                                    if (latestSub.email.isNotEmpty)
                                      Flexible(
                                        child: Text(
                                          latestSub.email,
                                          style: TextStyle(fontSize: 12, color: Colors.grey.shade600, fontWeight: FontWeight.w500),
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      )
                                    else
                                      PhoneFormatter.text(
                                        latestSub.phone,
                                        style: TextStyle(fontSize: 12, color: Colors.grey.shade700, fontWeight: FontWeight.w700, fontFamily: 'monospace'),
                                      ),
                                  ],
                                ),
                              ],
                            ),
                          ],
                        ),
                        // Quick Action Chips in Detail Header
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: [
                            if (latestSub.status == SubscriberStatus.pendingVerification)
                              FilledButton.icon(
                                onPressed: () => _runAsync(() => ref.read(subscribersProvider.notifier).approveMerchant(latestSub.id)),
                                icon: const Icon(Icons.verified_rounded, size: 16),
                                label: Text(isRtl ? 'اعتماد الحساب فوراً' : 'Approve Now', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                                style: FilledButton.styleFrom(
                                  backgroundColor: const Color(0xFF059669),
                                  foregroundColor: Colors.white,
                                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                ),
                              ),
                            FilledButton.icon(
                              onPressed: () => _showEditSubscriberBasicAndStoreDialog(latestSub),
                              icon: const Icon(Icons.edit_note_rounded, size: 17),
                              label: Text(
                                isRtl ? 'تعديل البيانات والمتجر والهاتف' : 'Edit Profile & Store',
                                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                              ),
                              style: FilledButton.styleFrom(
                                backgroundColor: const Color(0xFF1E40AF),
                                foregroundColor: Colors.white,
                                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              ),
                            ),
                            OutlinedButton.icon(
                              onPressed: () => _showChangeRoleDialog(latestSub),
                              icon: const Icon(Icons.manage_accounts_rounded, size: 16),
                              label: Text(isRtl ? 'تغيير الدور' : 'Change Role', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                              style: OutlinedButton.styleFrom(
                                foregroundColor: const Color(0xFF1E40AF),
                                side: const BorderSide(color: Color(0xFF93C5FD)),
                                backgroundColor: const Color(0xFFEFF6FF),
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              ),
                            ),
                            if (latestSub.role == UserRole.merchant)
                              OutlinedButton.icon(
                                onPressed: () => _showCategoryPermissionsDialog(latestSub),
                                icon: const Icon(Icons.admin_panel_settings_outlined, size: 16),
                                label: Text(isRtl ? 'الأقسام والعمولات' : 'Categories & Rates', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                                style: OutlinedButton.styleFrom(
                                  foregroundColor: const Color(0xFF059669),
                                  side: const BorderSide(color: Color(0xFF6EE7B7)),
                                  backgroundColor: const Color(0xFFECFDF5),
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                ),
                              ),
                          ],
                        ),
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
                      onOpenEditBasicInfo: () => _showEditSubscriberBasicAndStoreDialog(latestSub),
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

    // Global pool for KPI Metrics calculation (never collapses to 0 when filtering)
    final snapshot = ref.read(subscribersProvider.notifier).allUsersSnapshot;
    final globalPool = snapshot.isNotEmpty ? snapshot : allSubscribers;

    final totalMerchants = globalPool.where((s) => s.role == UserRole.merchant).length;
    final totalBuyers = globalPool.where((s) => s.role == UserRole.buyer).length;
    final totalEmployees = globalPool.where((s) => s.role == UserRole.employee).length;
    final totalActive = globalPool.where((s) => s.status == SubscriberStatus.active).length;
    final totalSuspended = globalPool.where((s) => s.status == SubscriberStatus.suspended).length;
    final totalPending = globalPool.where((s) => s.status == SubscriberStatus.pendingVerification).length;
    final totalRejected = globalPool.where((s) => s.status == SubscriberStatus.rejectedVerification).length;
    final totalAdvertisers = globalPool.where((s) => s.isAdvertiser || s.totalAdsCount > 0).length;
    final totalCommercialVolume = globalPool.fold<double>(0.0, (sum, s) => sum + s.totalSpent);

    final isMobileScreen = MediaQuery.of(context).size.width < 700;

    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
      body: Stack(
        children: [
          SingleChildScrollView(
            controller: _scrollController,
            padding: EdgeInsets.symmetric(
              horizontal: isMobileScreen ? 12.0 : 26.0,
              vertical: isMobileScreen ? 14.0 : 22.0,
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // 1. Organized Executive Top Header Card with Color-Coded Controls
                _buildHeader(
                  context,
                  isRtl,
                  globalPool,
                  totalActive: totalActive,
                  totalPending: totalPending,
                ),
                const SizedBox(height: 20),

                // 2. Interactive Semantic KPI Cards Grid (Click to Filter)
                _buildKpiSummaryBar(
                  isRtl: isRtl,
                  totalSubscribers: globalPool.length,
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
                const SizedBox(height: 20),

                // 3. Search, Sort & Color-Coded Filter Hub + Visual Color Legend
                _buildFilterAndSearchBar(
                  isRtl: isRtl,
                  subscribers: allSubscribers,
                  merchantsCount: totalMerchants,
                  buyersCount: totalBuyers,
                  employeesCount: totalEmployees,
                  activeCount: totalActive,
                  suspendedCount: totalSuspended,
                  pendingCount: totalPending,
                  rejectedCount: totalRejected,
                  advertisersCount: totalAdvertisers > 0 ? totalAdvertisers : activeAdsCount,
                ),
                const SizedBox(height: 20),

                // 4. High-Readability Color-Coded Subscribers Master Table
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
  // 1. EXECUTIVE HEADER CARD WITH UNIFIED FULL-WIDTH CONTROL DECK
  // ===========================================================================
  Widget _buildHeader(
    BuildContext context,
    bool isRtl,
    List<SubscriberModel> subscribers, {
    required int totalActive,
    required int totalPending,
  }) {
    final currentUsdRate = ref.watch(platformUsdRateSettingProvider);
    final isUpgradeEnabled = ref.watch(merchantUpgradeSettingProvider);

    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFCBD5E1)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.05),
            blurRadius: 18,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Top Multi-Color Semantic Accent Bar
          Container(
            height: 5,
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [
                  Color(0xFF1E40AF),
                  Color(0xFF6D28D9),
                  Color(0xFF059669),
                  Color(0xFFD97706),
                ],
              ),
            ),
          ),

          // Tier 1: Executive Identity + Primary Action Buttons
          Padding(
            padding: const EdgeInsets.fromLTRB(22, 18, 22, 18),
            child: LayoutBuilder(
              builder: (context, constraints) {
                final isCompact = constraints.maxWidth < 780;
                final identitySection = Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(13),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [Color(0xFF0F172A), Color(0xFF1E40AF)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.circular(15),
                        boxShadow: [
                          BoxShadow(
                            color: const Color(0xFF1E40AF).withValues(alpha: 0.24),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: const Icon(Icons.groups_3_rounded, color: Colors.white, size: 26),
                    ),
                    const SizedBox(width: 14),
                    Flexible(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Wrap(
                            crossAxisAlignment: WrapCrossAlignment.center,
                            spacing: 8,
                            runSpacing: 6,
                            children: [
                              Text(
                                isRtl ? 'إدارة المشتركين وقاعدة العملاء' : 'Subscribers & User Directory',
                                style: TextStyle(
                                  fontSize: isCompact ? 18.5 : 22,
                                  fontWeight: FontWeight.w900,
                                  color: const Color(0xFF0F172A),
                                  letterSpacing: -0.3,
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3.5),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFECFDF5),
                                  borderRadius: BorderRadius.circular(20),
                                  border: Border.all(color: const Color(0xFF6EE7B7)),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Container(
                                      width: 7,
                                      height: 7,
                                      decoration: const BoxDecoration(
                                        color: Color(0xFF059669),
                                        shape: BoxShape.circle,
                                      ),
                                    ),
                                    const SizedBox(width: 6),
                                    Text(
                                      isRtl ? '$totalActive حساب نشط' : '$totalActive Active',
                                      style: const TextStyle(
                                        fontSize: 11.5,
                                        fontWeight: FontWeight.w800,
                                        color: Color(0xFF065F46),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              if (totalPending > 0)
                                InkWell(
                                  onTap: () => _onFilterChanged(UiServerFilter.pending),
                                  borderRadius: BorderRadius.circular(20),
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3.5),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFFFFBEB),
                                      borderRadius: BorderRadius.circular(20),
                                      border: Border.all(color: const Color(0xFFFCD34D)),
                                    ),
                                    child: Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        const Icon(Icons.hourglass_top_rounded, size: 13, color: Color(0xFFD97706)),
                                        const SizedBox(width: 4),
                                        Text(
                                          isRtl ? '$totalPending بانتظار الاعتماد' : '$totalPending Pending',
                                          style: const TextStyle(
                                            fontSize: 11.5,
                                            fontWeight: FontWeight.w800,
                                            color: Color(0xFF92400E),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text(
                            isRtl
                                ? 'منظومة متكاملة لإدارة التجار والمشترين والموظفين، التسعير المرجعي، والرسائل الجماعية الموجهة'
                                : 'Unified command center for merchants, buyers, staff governance, USD pricing & targeted broadcasts',
                            style: const TextStyle(
                              color: Color(0xFF64748B),
                              fontSize: 12.5,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                );

                final primaryActions = Wrap(
                  spacing: 10,
                  runSpacing: 8,
                  children: [
                    FilledButton.icon(
                      style: FilledButton.styleFrom(
                        backgroundColor: const Color(0xFF1E40AF),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 13),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        elevation: 1,
                      ),
                      icon: const Icon(Icons.person_add_alt_1_rounded, size: 18),
                      label: Text(
                        isRtl ? 'إضافة مشترك جديد' : 'Add Subscriber',
                        style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                      ),
                      onPressed: () => _showAddSubscriberDialog(context, isRtl),
                    ),
                    FilledButton.icon(
                      style: FilledButton.styleFrom(
                        backgroundColor: const Color(0xFF6D28D9),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 13),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        elevation: 1,
                      ),
                      icon: const Icon(Icons.campaign_rounded, size: 19),
                      label: Text(
                        isRtl ? 'رسالة جماعية للمشتركين' : 'Bulk Broadcast',
                        style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                      ),
                      onPressed: () => _showBulkBroadcastDialog(context, isRtl),
                    ),
                  ],
                );

                if (isCompact) {
                  return Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      identitySection,
                      const SizedBox(height: 14),
                      primaryActions,
                    ],
                  );
                }

                return Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(child: identitySection),
                    const SizedBox(width: 16),
                    primaryActions,
                  ],
                );
              },
            ),
          ),

          // Tier 2: Unified Full-Width Operational Control Strip (Evenly Aligned Tiles)
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
            decoration: const BoxDecoration(
              color: Color(0xFFF8FAFC),
              border: Border(top: BorderSide(color: Color(0xFFE2E8F0))),
            ),
            child: LayoutBuilder(
              builder: (context, constraints) {
                final int cols = constraints.maxWidth > 960
                    ? 4
                    : (constraints.maxWidth > 520 ? 2 : 1);
                const double gap = 10.0;
                final double tileWidth =
                    (constraints.maxWidth - (gap * (cols - 1))) / cols;

                return Wrap(
                  spacing: gap,
                  runSpacing: gap,
                  children: [
                    // Tile 1: Official Platform USD Rate
                    SizedBox(
                      width: tileWidth,
                      height: 48,
                      child: InkWell(
                        borderRadius: BorderRadius.circular(12),
                        onTap: () => _showSubscriberUsdRateDialog(context, isRtl),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: const Color(0xFF6EE7B7), width: 1.3),
                          ),
                          child: Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(6),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFECFDF5),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: const Icon(Icons.currency_exchange_rounded, size: 17, color: Color(0xFF059669)),
                              ),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  isRtl ? 'سعر الدولار المعتمد' : 'Platform USD Rate',
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: const Color(0xFF059669),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  '${NumberFormat('#,##0').format(currentUsdRate)} ${isRtl ? "ج.س" : "SDG"}',
                                  style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w900, color: Colors.white),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),

                    // Tile 2: Points & Commissions Rules
                    SizedBox(
                      width: tileWidth,
                      height: 48,
                      child: InkWell(
                        borderRadius: BorderRadius.circular(12),
                        onTap: () => _showGamificationSettingsDialog(context, isRtl),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: const Color(0xFFFCD34D), width: 1.3),
                          ),
                          child: Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(6),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFFFFBEB),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: const Icon(Icons.stars_rounded, size: 17, color: Color(0xFFD97706)),
                              ),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  isRtl ? 'قواعد النقاط والعمولات' : 'Points & Commissions',
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
                                ),
                              ),
                              const Icon(Icons.tune_rounded, size: 16, color: Color(0xFFB45309)),
                            ],
                          ),
                        ),
                      ),
                    ),

                    // Tile 3: Merchant Upgrade Gate Switch
                    SizedBox(
                      width: tileWidth,
                      height: 48,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: isUpgradeEnabled ? const Color(0xFF6EE7B7) : const Color(0xFFFECDD3),
                            width: 1.3,
                          ),
                        ),
                        child: Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(6),
                              decoration: BoxDecoration(
                                color: isUpgradeEnabled ? const Color(0xFFECFDF5) : const Color(0xFFFFF1F2),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Icon(
                                isUpgradeEnabled ? Icons.storefront_rounded : Icons.lock_person_rounded,
                                size: 17,
                                color: isUpgradeEnabled ? const Color(0xFF059669) : const Color(0xFFE11D48),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                isUpgradeEnabled
                                    ? (isRtl ? 'باب الترقية: مفتوح' : 'Upgrade: Open')
                                    : (isRtl ? 'باب الترقية: مغلق' : 'Upgrade: Closed'),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w800,
                                  color: isUpgradeEnabled ? const Color(0xFF065F46) : const Color(0xFF9F1239),
                                ),
                              ),
                            ),
                            Transform.scale(
                              scale: 0.82,
                              child: Switch(
                                value: isUpgradeEnabled,
                                activeThumbColor: const Color(0xFF059669),
                                activeTrackColor: const Color(0xFFA7F3D0),
                                materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                onChanged: (val) {
                                  ref.read(merchantUpgradeSettingProvider.notifier).toggleSetting(val);
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(
                                      content: Text(
                                        val
                                            ? (isRtl ? '✅ تم تفعيل وإظهار طلب الترقية كتاجر في تطبيق المشتري' : 'Merchant upgrade option is now visible in Buyer App')
                                            : (isRtl ? '🔒 تم إيقاف وإخفاء طلب الترقية كتاجر من تطبيق المشتري' : 'Merchant upgrade option is now hidden in Buyer App'),
                                      ),
                                      backgroundColor: val ? const Color(0xFF059669) : const Color(0xFFE11D48),
                                      duration: const Duration(seconds: 2),
                                      behavior: SnackBarBehavior.floating,
                                    ),
                                  );
                                },
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),

                    // Tile 4: Sync & Refresh Directory
                    SizedBox(
                      width: tileWidth,
                      height: 48,
                      child: InkWell(
                        borderRadius: BorderRadius.circular(12),
                        onTap: () => ref.read(subscribersProvider.notifier).loadSubscribers(reset: true),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: const Color(0xFFCBD5E1), width: 1.3),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              const Icon(Icons.sync_rounded, size: 18, color: Color(0xFF1E40AF)),
                              const SizedBox(width: 8),
                              Text(
                                isRtl ? 'تحديث ومزامنة السجل' : 'Refresh Directory',
                                style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800, color: Color(0xFF1E293B)),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ],
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  // ===========================================================================
  // 2. INTERACTIVE COLOR-CODED KPI SUMMARY GRID (CLICK TO FILTER)
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
        const double spacing = 12.0;
        final int crossAxisCount = constraints.maxWidth > 1100
            ? 4
            : (constraints.maxWidth > 340 ? 2 : 1);
        final double cardWidth =
            (constraints.maxWidth - (spacing * (crossAxisCount - 1))) / crossAxisCount;

        final formattedVolume = NumberFormat.compact(locale: isRtl ? 'ar' : 'en').format(totalCommercialVolume);

        return Wrap(
          spacing: spacing,
          runSpacing: spacing,
          children: [
            _buildKpiCard(
              title: isRtl ? 'إجمالي المشتركين' : 'Total Subscribers',
              value: '$totalSubscribers',
              subtitle: isRtl ? 'كافة الحسابات المسجلة' : 'All registered accounts',
              badgeText: isRtl ? 'الكل' : 'All',
              icon: Icons.groups_rounded,
              accentColor: const Color(0xFF7E22CE), // Royal Purple
              softBgColor: const Color(0xFFFAF5FF),
              borderColor: const Color(0xFFD8B4FE),
              width: cardWidth,
              isSelected: _selectedFilter == UiServerFilter.all,
              onTap: () => _onFilterChanged(UiServerFilter.all),
            ),
            _buildKpiCard(
              title: isRtl ? 'التجار المعتمدون' : 'Verified Merchants',
              value: '$totalMerchants',
              subtitle: isRtl ? 'أصحاب المتاجر والتوريد' : 'Active store owners',
              badgeText: totalSubscribers > 0
                  ? '${((totalMerchants / totalSubscribers) * 100).round()}%'
                  : '0%',
              icon: Icons.storefront_rounded,
              accentColor: const Color(0xFF059669), // Emerald Green
              softBgColor: const Color(0xFFECFDF5),
              borderColor: const Color(0xFF6EE7B7),
              width: cardWidth,
              isSelected: _selectedFilter == UiServerFilter.merchants,
              onTap: () => _onFilterChanged(
                _selectedFilter == UiServerFilter.merchants ? UiServerFilter.all : UiServerFilter.merchants,
              ),
            ),
            _buildKpiCard(
              title: isRtl ? 'المشترون والعملاء' : 'Buyers & Customers',
              value: '$totalBuyers',
              subtitle: isRtl ? 'حسابات الشراء المباشر والشركات' : 'Direct & corporate buyers',
              badgeText: totalSubscribers > 0
                  ? '${((totalBuyers / totalSubscribers) * 100).round()}%'
                  : '0%',
              icon: Icons.shopping_bag_rounded,
              accentColor: const Color(0xFF2563EB), // Royal Blue
              softBgColor: const Color(0xFFEFF6FF),
              borderColor: const Color(0xFF93C5FD),
              width: cardWidth,
              isSelected: _selectedFilter == UiServerFilter.buyers,
              onTap: () => _onFilterChanged(
                _selectedFilter == UiServerFilter.buyers ? UiServerFilter.all : UiServerFilter.buyers,
              ),
            ),
            _buildKpiCard(
              title: isRtl ? 'المعلنون والحملات' : 'Advertisers & Ads',
              value: '$totalAdvertisers',
              subtitle: isRtl ? '$activeAdsCount حملة إعلانية نشطة حالياً' : '$activeAdsCount active campaigns',
              badgeText: isRtl ? 'تسويق' : 'Ads',
              icon: Icons.campaign_rounded,
              accentColor: const Color(0xFFC026D3), // Fuchsia / Magenta
              softBgColor: const Color(0xFFFDF4FF),
              borderColor: const Color(0xFFF0ABFC),
              width: cardWidth,
              isSelected: _selectedFilter == UiServerFilter.advertisers,
              onTap: () => _onFilterChanged(
                _selectedFilter == UiServerFilter.advertisers ? UiServerFilter.all : UiServerFilter.advertisers,
              ),
            ),
            _buildKpiCard(
              title: isRtl ? 'حجم المعاملات المالية' : 'Financial Volume',
              value: '$formattedVolume ج.س',
              subtitle: isRtl ? 'إجمالي المبيعات والمشتريات الحية' : 'Total live sales & purchases',
              badgeText: isRtl ? 'ترتيب بالأعلى ↕' : 'Sort Top ↕',
              icon: Icons.account_balance_wallet_rounded,
              accentColor: const Color(0xFF047857), // Deep Emerald
              softBgColor: const Color(0xFFF0FDF4),
              borderColor: const Color(0xFF86EFAC),
              width: cardWidth,
              isSelected: _sortBy == 'highest_volume',
              onTap: () {
                setState(() {
                  _sortBy = _sortBy == 'highest_volume' ? 'newest' : 'highest_volume';
                });
              },
            ),
            _buildKpiCard(
              title: isRtl ? 'طاقم العمل والموظفون' : 'Platform Staff',
              value: '$totalEmployees',
              subtitle: isRtl ? 'موظفو الإدارة والمتابعة' : 'Operations & support team',
              badgeText: isRtl ? 'فريق العمل' : 'Staff',
              icon: Icons.badge_rounded,
              accentColor: const Color(0xFFD97706), // Amber Gold
              softBgColor: const Color(0xFFFFFBEB),
              borderColor: const Color(0xFFFCD34D),
              width: cardWidth,
              isSelected: _selectedFilter == UiServerFilter.employees,
              onTap: () => _onFilterChanged(
                _selectedFilter == UiServerFilter.employees ? UiServerFilter.all : UiServerFilter.employees,
              ),
            ),
            _buildKpiCard(
              title: isRtl ? 'الحسابات النشطة' : 'Active Accounts',
              value: '$totalActive',
              subtitle: isRtl ? 'حسابات موثقة وفعّالة بالمنصة' : 'Verified & active users',
              badgeText: totalSubscribers > 0
                  ? '${((totalActive / totalSubscribers) * 100).round()}%'
                  : '0%',
              icon: Icons.verified_user_rounded,
              accentColor: const Color(0xFF0891B2), // Cyan / Teal
              softBgColor: const Color(0xFFECFEFF),
              borderColor: const Color(0xFF67E8F9),
              width: cardWidth,
              isSelected: _selectedFilter == UiServerFilter.active,
              onTap: () => _onFilterChanged(
                _selectedFilter == UiServerFilter.active ? UiServerFilter.all : UiServerFilter.active,
              ),
            ),
            _buildKpiCard(
              title: isRtl ? 'الموقوفون وقيد المراجعة' : 'Pending & Suspended',
              value: '${totalSuspended + totalPending}',
              subtitle: isRtl
                  ? '$totalPending قيد المراجعة • $totalSuspended موقوف'
                  : '$totalPending Pending • $totalSuspended Suspended',
              badgeText: (totalSuspended + totalPending) > 0
                  ? (isRtl ? 'يتطلب إجراء' : 'Action Needed')
                  : (isRtl ? 'مستقر' : 'Clear'),
              icon: Icons.warning_amber_rounded,
              accentColor: const Color(0xFFE11D48), // Rose Red
              softBgColor: const Color(0xFFFFF1F2),
              borderColor: const Color(0xFFFECDD3),
              width: cardWidth,
              isSelected: _selectedFilter == UiServerFilter.pending || _selectedFilter == UiServerFilter.suspended,
              onTap: () => _onFilterChanged(
                totalPending > 0 ? UiServerFilter.pending : UiServerFilter.suspended,
              ),
            ),
          ],
        );
      },
    );
  }

  Widget _buildKpiCard({
    required String title,
    required String value,
    required String subtitle,
    required String badgeText,
    required IconData icon,
    required Color accentColor,
    required Color softBgColor,
    required Color borderColor,
    required double width,
    required bool isSelected,
    required VoidCallback onTap,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          width: width,
          decoration: BoxDecoration(
            color: isSelected ? softBgColor : Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: isSelected ? accentColor : const Color(0xFFE2E8F0),
              width: isSelected ? 2.0 : 1.2,
            ),
            boxShadow: [
              BoxShadow(
                color: isSelected
                    ? accentColor.withValues(alpha: 0.14)
                    : const Color(0xFF0F172A).withValues(alpha: 0.03),
                blurRadius: isSelected ? 14 : 8,
                offset: const Offset(0, 3),
              ),
            ],
          ),
          clipBehavior: Clip.antiAlias,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Colored Top Indicator Strip
              Container(
                height: 4,
                width: double.infinity,
                color: accentColor,
              ),
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 14, 16, 14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Container(
                          padding: const EdgeInsets.all(9),
                          decoration: BoxDecoration(
                            color: softBgColor,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: borderColor),
                          ),
                          child: Icon(icon, color: accentColor, size: 20),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: isSelected ? accentColor : softBgColor,
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: isSelected ? accentColor : borderColor),
                          ),
                          child: Text(
                            badgeText,
                            style: TextStyle(
                              fontSize: 10.5,
                              fontWeight: FontWeight.w800,
                              color: isSelected ? Colors.white : accentColor,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Text(
                      value,
                      style: const TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.w900,
                        color: Color(0xFF0F172A),
                        letterSpacing: -0.5,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 3),
                    Text(
                      title,
                      style: TextStyle(
                        color: accentColor,
                        fontSize: 12.5,
                        fontWeight: FontWeight.w800,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 2),
                    Text(
                      subtitle,
                      style: const TextStyle(
                        color: Color(0xFF64748B),
                        fontSize: 11,
                        fontWeight: FontWeight.w500,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ===========================================================================
  // 3. FULL-WIDTH SEARCH, SORT & SEGMENTED CATEGORY TRACK
  // ===========================================================================
  Widget _buildFilterAndSearchBar({
    required bool isRtl,
    required List<SubscriberModel> subscribers,
    required int merchantsCount,
    required int buyersCount,
    required int employeesCount,
    required int activeCount,
    required int suspendedCount,
    required int pendingCount,
    required int rejectedCount,
    required int advertisersCount,
  }) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFCBD5E1)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.035),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: LayoutBuilder(
        builder: (context, constraints) {
          final isWide = constraints.maxWidth >= 920;

          final searchField = TextField(
            controller: _searchController,
            onChanged: _onSearchChanged,
            style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w600, color: Color(0xFF0F172A)),
            decoration: InputDecoration(
              hintText: isRtl
                  ? 'ابحث بالاسم، رقم العضوية، البريد، الهاتف، أو اسم المتجر...'
                  : 'Search by Name, Membership ID, Email, Phone, or Store...',
              hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 13, fontWeight: FontWeight.normal),
              prefixIcon: const Icon(Icons.search_rounded, color: Color(0xFF1E40AF), size: 20),
              suffixIcon: _searchController.text.isNotEmpty
                  ? IconButton(
                      icon: const Icon(Icons.clear_rounded, size: 18, color: Color(0xFF64748B)),
                      onPressed: () {
                        _searchController.clear();
                        _onSearchChanged('');
                      },
                    )
                  : null,
              filled: true,
              fillColor: const Color(0xFFF8FAFC),
              contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 13),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFF1E40AF), width: 1.6),
              ),
            ),
          );

          final sortPill = Container(
            height: 46,
            padding: const EdgeInsets.symmetric(horizontal: 12),
            decoration: BoxDecoration(
              color: const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFCBD5E1)),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.sort_rounded, size: 18, color: Color(0xFF1E40AF)),
                const SizedBox(width: 6),
                Text(
                  isRtl ? 'ترتيب:' : 'Sort:',
                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Color(0xFF475569)),
                ),
                const SizedBox(width: 6),
                DropdownButtonHideUnderline(
                  child: DropdownButton<String>(
                    value: _sortBy,
                    isDense: true,
                    style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
                    items: [
                      DropdownMenuItem(
                        value: 'newest',
                        child: Text(isRtl ? 'الأحدث انضماماً' : 'Newest First'),
                      ),
                      DropdownMenuItem(
                        value: 'highest_volume',
                        child: Text(isRtl ? 'الأعلى تعاملاً مالياً' : 'Highest Financial Volume'),
                      ),
                      DropdownMenuItem(
                        value: 'most_orders',
                        child: Text(isRtl ? 'الأكثر طلبات' : 'Most Orders'),
                      ),
                      DropdownMenuItem(
                        value: 'name_asc',
                        child: Text(isRtl ? 'الاسم (أ - ي)' : 'Name (A - Z)'),
                      ),
                    ],
                    onChanged: (val) {
                      if (val != null) setState(() => _sortBy = val);
                    },
                  ),
                ),
              ],
            ),
          );

          final legendBtn = InkWell(
            borderRadius: BorderRadius.circular(12),
            onTap: () => setState(() => _showColorLegend = !_showColorLegend),
            child: Container(
              height: 46,
              padding: const EdgeInsets.symmetric(horizontal: 12),
              decoration: BoxDecoration(
                color: _showColorLegend ? const Color(0xFFFAF5FF) : const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: _showColorLegend ? const Color(0xFFD8B4FE) : const Color(0xFFCBD5E1),
                ),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    Icons.palette_outlined,
                    size: 16,
                    color: _showColorLegend ? const Color(0xFF7E22CE) : const Color(0xFF64748B),
                  ),
                  const SizedBox(width: 6),
                  Text(
                    isRtl ? 'دليل الألوان' : 'Color Guide',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w800,
                      color: _showColorLegend ? const Color(0xFF7E22CE) : const Color(0xFF475569),
                    ),
                  ),
                ],
              ),
            ),
          );

          final countBadge = Container(
            height: 46,
            padding: const EdgeInsets.symmetric(horizontal: 14),
            decoration: BoxDecoration(
              color: const Color(0xFFEFF6FF),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFBFDBFE)),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.fact_check_outlined, size: 16, color: Color(0xFF1E40AF)),
                const SizedBox(width: 6),
                Text(
                  '${subscribers.length} ${isRtl ? "مشترك معروض" : "Shown"}',
                  style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 12, color: Color(0xFF1E40AF)),
                ),
              ],
            ),
          );

          final roleButtons = <Widget>[
            _buildSegmentFilterButton(
              filter: UiServerFilter.all,
              label: isRtl ? 'كل المشتركين' : 'All Users',
              icon: Icons.groups_rounded,
              semanticColor: const Color(0xFF1E40AF),
              badgeCount: subscribers.length,
              isRtl: isRtl,
              expandContent: isWide,
            ),
            _buildSegmentFilterButton(
              filter: UiServerFilter.merchants,
              label: isRtl ? 'التجار المعتمدون' : 'Merchants',
              icon: Icons.storefront_rounded,
              semanticColor: const Color(0xFF059669),
              badgeCount: merchantsCount > 0 ? merchantsCount : null,
              isRtl: isRtl,
              expandContent: isWide,
            ),
            _buildSegmentFilterButton(
              filter: UiServerFilter.buyers,
              label: isRtl ? 'المشترون والعملاء' : 'Buyers',
              icon: Icons.shopping_bag_rounded,
              semanticColor: const Color(0xFF2563EB),
              badgeCount: buyersCount > 0 ? buyersCount : null,
              isRtl: isRtl,
              expandContent: isWide,
            ),
            _buildSegmentFilterButton(
              filter: UiServerFilter.advertisers,
              label: isRtl ? 'المعلنون والحملات' : 'Advertisers',
              icon: Icons.campaign_rounded,
              semanticColor: const Color(0xFFC026D3),
              badgeCount: advertisersCount > 0 ? advertisersCount : null,
              isRtl: isRtl,
              expandContent: isWide,
            ),
            _buildSegmentFilterButton(
              filter: UiServerFilter.employees,
              label: isRtl ? 'الموظفون' : 'Staff',
              icon: Icons.badge_rounded,
              semanticColor: const Color(0xFFD97706),
              badgeCount: employeesCount > 0 ? employeesCount : null,
              isRtl: isRtl,
              expandContent: isWide,
            ),
          ];

          final statusButtons = <Widget>[
            _buildSegmentFilterButton(
              filter: UiServerFilter.active,
              label: isRtl ? 'الحسابات النشطة' : 'Active',
              icon: Icons.check_circle_outline_rounded,
              semanticColor: const Color(0xFF0891B2),
              badgeCount: activeCount > 0 ? activeCount : null,
              isRtl: isRtl,
              expandContent: isWide,
            ),
            _buildSegmentFilterButton(
              filter: UiServerFilter.pending,
              label: isRtl ? 'قيد الاعتماد' : 'Pending',
              icon: Icons.hourglass_top_rounded,
              semanticColor: const Color(0xFFD97706),
              badgeCount: pendingCount > 0 ? pendingCount : null,
              isRtl: isRtl,
              expandContent: isWide,
            ),
            _buildSegmentFilterButton(
              filter: UiServerFilter.rejected,
              label: isRtl ? 'الطلبات المرفوضة' : 'Rejected',
              icon: Icons.cancel_outlined,
              semanticColor: const Color(0xFFE11D48),
              badgeCount: rejectedCount > 0 ? rejectedCount : null,
              isRtl: isRtl,
              expandContent: isWide,
            ),
            _buildSegmentFilterButton(
              filter: UiServerFilter.suspended,
              label: isRtl ? 'الحسابات الموقوفة' : 'Suspended',
              icon: Icons.block_rounded,
              semanticColor: const Color(0xFFDC2626),
              badgeCount: suspendedCount > 0 ? suspendedCount : null,
              isRtl: isRtl,
              expandContent: isWide,
            ),
            InkWell(
              borderRadius: BorderRadius.circular(12),
              onTap: () {
                ref.read(settingsTabIndexProvider.notifier).state = 6;
                context.go(RouteNames.adminSettings);
              },
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                decoration: BoxDecoration(
                  color: const Color(0xFFFFFBEB),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFFCD34D), width: 1.2),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  mainAxisSize: isWide ? MainAxisSize.max : MainAxisSize.min,
                  children: [
                    const Icon(Icons.admin_panel_settings_rounded, size: 16, color: Color(0xFFB45309)),
                    const SizedBox(width: 6),
                    Flexible(
                      child: Text(
                        isRtl ? 'صلاحيات الموظفين' : 'Staff Permissions',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          color: Color(0xFF92400E),
                          fontWeight: FontWeight.w800,
                          fontSize: 12,
                        ),
                      ),
                    ),
                    const SizedBox(width: 4),
                    const Icon(Icons.open_in_new_rounded, size: 12, color: Color(0xFFD97706)),
                  ],
                ),
              ),
            ),
          ];

          return Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Row 1: Full-Width Search & Quick Sort Controls
              if (isWide)
                Row(
                  children: [
                    Expanded(child: searchField),
                    const SizedBox(width: 10),
                    sortPill,
                    const SizedBox(width: 10),
                    legendBtn,
                    const SizedBox(width: 10),
                    countBadge,
                  ],
                )
              else
                Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    searchField,
                    const SizedBox(height: 10),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: [sortPill, legendBtn, countBadge],
                    ),
                  ],
                ),

              const SizedBox(height: 14),
              const Divider(height: 1, color: Color(0xFFF1F5F9)),
              const SizedBox(height: 14),

              // Row 2: Full-Width Role Categories Track ("تصنيف الفئات بالمسار كاملاً")
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: isWide
                    ? Row(
                        children: [
                          for (int i = 0; i < roleButtons.length; i++) ...[
                            if (i > 0) const SizedBox(width: 8),
                            Expanded(child: roleButtons[i]),
                          ],
                        ],
                      )
                    : SingleChildScrollView(
                        scrollDirection: Axis.horizontal,
                        child: Row(
                          children: [
                            for (int i = 0; i < roleButtons.length; i++) ...[
                              if (i > 0) const SizedBox(width: 8),
                              roleButtons[i],
                            ],
                          ],
                        ),
                      ),
              ),
              const SizedBox(height: 10),

              // Row 3: Full-Width Account Status & Governance Track ("حالة الحساب بالمسار كاملاً")
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: isWide
                    ? Row(
                        children: [
                          for (int i = 0; i < statusButtons.length; i++) ...[
                            if (i > 0) const SizedBox(width: 8),
                            Expanded(child: statusButtons[i]),
                          ],
                        ],
                      )
                    : SingleChildScrollView(
                        scrollDirection: Axis.horizontal,
                        child: Row(
                          children: [
                            for (int i = 0; i < statusButtons.length; i++) ...[
                              if (i > 0) const SizedBox(width: 8),
                              statusButtons[i],
                            ],
                          ],
                        ),
                      ),
              ),

              // Collapsible Illustrative Color Legend Bar
              if (_showColorLegend) ...[
                const SizedBox(height: 12),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF8FAFC),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  child: Wrap(
                    spacing: 18,
                    runSpacing: 8,
                    crossAxisAlignment: WrapCrossAlignment.center,
                    children: [
                      Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.info_outline_rounded, size: 15, color: Color(0xFF475569)),
                          const SizedBox(width: 6),
                          Text(
                            isRtl ? 'دليل الألوان البصري:' : 'Visual Color Legend:',
                            style: const TextStyle(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w800,
                              color: Color(0xFF334155),
                            ),
                          ),
                        ],
                      ),
                      _buildLegendItem(
                        color: const Color(0xFF059669),
                        label: isRtl ? 'أخضر زمردي: تاجر معتمد / مبيعات / نشط' : 'Emerald: Merchant / Sales / Active',
                      ),
                      _buildLegendItem(
                        color: const Color(0xFF2563EB),
                        label: isRtl ? 'أزرق ملكي: مشتري / طلبات شراء / موثق' : 'Blue: Buyer / Orders / Verified',
                      ),
                      _buildLegendItem(
                        color: const Color(0xFFC026D3),
                        label: isRtl ? 'أرجواني: معلن / حملات تسويقية' : 'Fuchsia: Advertiser / Campaigns',
                      ),
                      _buildLegendItem(
                        color: const Color(0xFF7E22CE),
                        label: isRtl ? 'بنفسجي: تسهيلات شركات B2B / إدارة' : 'Purple: B2B Credit / Admin',
                      ),
                      _buildLegendItem(
                        color: const Color(0xFFD97706),
                        label: isRtl ? 'كهرماني ذهبي: موظف / قيد المراجعة / نقاط' : 'Amber: Staff / Pending / Points',
                      ),
                      _buildLegendItem(
                        color: const Color(0xFFE11D48),
                        label: isRtl ? 'أحمر قرمزي: حساب موقوف / مرفوض' : 'Crimson: Suspended / Rejected',
                      ),
                    ],
                  ),
                ),
              ],
            ],
          );
        },
      ),
    );
  }

  Widget _buildLegendItem({required Color color, required String label}) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 10,
          height: 10,
          decoration: BoxDecoration(
            color: color,
            borderRadius: BorderRadius.circular(3),
            boxShadow: [
              BoxShadow(
                color: color.withValues(alpha: 0.3),
                blurRadius: 4,
              ),
            ],
          ),
        ),
        const SizedBox(width: 6),
        Text(
          label,
          style: const TextStyle(
            fontSize: 11.5,
            fontWeight: FontWeight.w600,
            color: Color(0xFF475569),
          ),
        ),
      ],
    );
  }

  Widget _buildSegmentFilterButton({
    required UiServerFilter filter,
    required String label,
    required IconData icon,
    required Color semanticColor,
    required bool isRtl,
    int? badgeCount,
    bool expandContent = false,
  }) {
    final isSelected = _selectedFilter == filter;
    return InkWell(
      borderRadius: BorderRadius.circular(11),
      onTap: () => _onFilterChanged(filter),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 9),
        decoration: BoxDecoration(
          color: isSelected ? semanticColor : Colors.white,
          borderRadius: BorderRadius.circular(11),
          border: Border.all(
            color: isSelected ? semanticColor : semanticColor.withValues(alpha: 0.28),
            width: isSelected ? 1.5 : 1.0,
          ),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: semanticColor.withValues(alpha: 0.25),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  ),
                ]
              : null,
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          mainAxisSize: expandContent ? MainAxisSize.max : MainAxisSize.min,
          children: [
            Icon(
              icon,
              size: 16,
              color: isSelected ? Colors.white : semanticColor,
            ),
            const SizedBox(width: 6),
            Flexible(
              child: Text(
                label,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  color: isSelected ? Colors.white : const Color(0xFF1E293B),
                  fontWeight: FontWeight.w800,
                  fontSize: 12.5,
                ),
              ),
            ),
            if (badgeCount != null) ...[
              const SizedBox(width: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6.5, vertical: 1.5),
                decoration: BoxDecoration(
                  color: isSelected ? Colors.white : semanticColor.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  '$badgeCount',
                  style: TextStyle(
                    color: isSelected ? semanticColor : semanticColor,
                    fontSize: 10.5,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  // ===========================================================================
  // 4. HIGH-READABILITY COLOR-CODED SUBSCRIBERS MASTER TABLE
  // ===========================================================================
  Widget _buildSubscribersTableCard(
    BuildContext context,
    bool isRtl,
    AsyncValue<List<SubscriberModel>> asyncSubscribers,
  ) {
    final notifier = ref.watch(subscribersProvider.notifier);
    final categoriesAsync = ref.watch(categoriesProvider);
    final allCategories = categoriesAsync.valueOrNull ?? <CategoryModel>[];

    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: const Color(0xFFCBD5E1)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.04),
            blurRadius: 20,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Table Card Header Banner
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 18),
            decoration: const BoxDecoration(
              color: Color(0xFFF8FAFC),
              border: Border(bottom: BorderSide(color: Color(0xFFE2E8F0))),
            ),
            child: Wrap(
              alignment: WrapAlignment.spaceBetween,
              crossAxisAlignment: WrapCrossAlignment.center,
              spacing: 16,
              runSpacing: 10,
              children: [
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(9),
                      decoration: BoxDecoration(
                        color: const Color(0xFF1E40AF).withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: const Color(0xFFBFDBFE)),
                      ),
                      child: const Icon(Icons.table_chart_rounded, color: Color(0xFF1E40AF), size: 20),
                    ),
                    const SizedBox(width: 12),
                    Flexible(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            isRtl ? 'السجل التفصيلي للمشتركين والمتاجر' : 'Subscribers & Merchants Master Ledger',
                            style: const TextStyle(
                              fontSize: 17,
                              fontWeight: FontWeight.w900,
                              color: Color(0xFF0F172A),
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            isRtl
                                ? 'انقر على أي صف لعرض الملف الكامل للمشترك وإدارة صلاحياته وحساباته المالية'
                                : 'Click any row to open full subscriber profile, permissions, and financial settings',
                            style: const TextStyle(color: Color(0xFF64748B), fontSize: 12, fontWeight: FontWeight.w500),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                // Active Filter Indicator Chip
                if (_selectedFilter != UiServerFilter.all)
                  InkWell(
                    onTap: () => _onFilterChanged(UiServerFilter.all),
                    borderRadius: BorderRadius.circular(10),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFEF2F2),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: const Color(0xFFFECACA)),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.filter_alt_off_rounded, size: 15, color: Color(0xFFDC2626)),
                          const SizedBox(width: 6),
                          Text(
                            isRtl ? 'إلغاء الفلتر وعرض الكل' : 'Clear Filter & Show All',
                            style: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w800,
                              color: Color(0xFFDC2626),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
              ],
            ),
          ),

          // Table Content
          asyncSubscribers.when(
            loading: () => const Padding(
              padding: EdgeInsets.symmetric(vertical: 60.0),
              child: Center(
                child: CircularProgressIndicator(color: Color(0xFF1E40AF)),
              ),
            ),
            error: (err, stack) => Padding(
              padding: const EdgeInsets.all(24.0),
              child: AdminErrorView(
                error: err,
                stackTrace: stack,
                onRetry: () => ref.read(subscribersProvider.notifier).loadSubscribers(reset: true),
              ),
            ),
            data: (rawSubs) {
              final subs = _applyLocalSort(rawSubs, isRtl);

              if (subs.isEmpty) {
                return Padding(
                  padding: const EdgeInsets.symmetric(vertical: 64),
                  child: Center(
                    child: Column(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(20),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF1F5F9),
                            shape: BoxShape.circle,
                            border: Border.all(color: const Color(0xFFE2E8F0)),
                          ),
                          child: const Icon(Icons.person_off_rounded, size: 48, color: Color(0xFF94A3B8)),
                        ),
                        const SizedBox(height: 16),
                        Text(
                          isRtl ? 'لا يوجد مشتركون مطابقون للبحث أو الفلتر المحدد' : 'No subscribers found matching your filter',
                          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: Color(0xFF334155)),
                        ),
                        const SizedBox(height: 8),
                        if (_selectedFilter != UiServerFilter.all || _searchController.text.isNotEmpty)
                          OutlinedButton.icon(
                            onPressed: () {
                              _searchController.clear();
                              _onSearchChanged('');
                              _onFilterChanged(UiServerFilter.all);
                            },
                            icon: const Icon(Icons.refresh_rounded, size: 16),
                            label: Text(isRtl ? 'إعادة ضبط البحث والفلاتر' : 'Reset Filters'),
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
                          tableConstraints.maxWidth > 1240 ? tableConstraints.maxWidth : 1240;

                      return SingleChildScrollView(
                        scrollDirection: Axis.horizontal,
                        child: ConstrainedBox(
                          constraints: BoxConstraints(minWidth: tableWidth),
                          child: DataTable(
                            showCheckboxColumn: false,
                            headingRowHeight: 54,
                            headingRowColor: const WidgetStatePropertyAll(Color(0xFFF1F5F9)),
                            headingTextStyle: const TextStyle(
                              fontWeight: FontWeight.w900,
                              color: Color(0xFF0F172A),
                              fontSize: 12.5,
                            ),
                            dataRowMinHeight: 74,
                            dataRowMaxHeight: 90,
                            columnSpacing: 18,
                            horizontalMargin: 20,
                            dividerThickness: 1,
                            columns: [
                              DataColumn(
                                label: Text(isRtl ? '#' : '#', style: const TextStyle(color: Color(0xFF64748B))),
                              ),
                              DataColumn(
                                label: _buildColumnHeader(
                                  Icons.person_rounded,
                                  isRtl ? 'المشترك والعضوية' : 'Subscriber & ID',
                                  const Color(0xFF1E40AF),
                                ),
                              ),
                              DataColumn(
                                label: _buildColumnHeader(
                                  Icons.contact_phone_rounded,
                                  isRtl ? 'بيانات التواصل' : 'Contact Info',
                                  const Color(0xFF0284C7),
                                ),
                              ),
                              DataColumn(
                                label: _buildColumnHeader(
                                  Icons.badge_rounded,
                                  isRtl ? 'نوع الحساب والرتبة' : 'Role & Tier',
                                  const Color(0xFF7E22CE),
                                ),
                              ),
                              DataColumn(
                                label: _buildColumnHeader(
                                  Icons.verified_user_rounded,
                                  isRtl ? 'حالة الحساب' : 'Account Status',
                                  const Color(0xFF059669),
                                ),
                              ),
                              DataColumn(
                                label: _buildColumnHeader(
                                  Icons.storefront_rounded,
                                  isRtl ? 'المتجر والأقسام المصرحة' : 'Store & Authorized Categories',
                                  const Color(0xFF047857),
                                ),
                              ),
                              DataColumn(
                                label: _buildColumnHeader(
                                  Icons.shopping_bag_rounded,
                                  isRtl ? 'الطلبات' : 'Orders',
                                  const Color(0xFF2563EB),
                                ),
                              ),
                              DataColumn(
                                label: _buildColumnHeader(
                                  Icons.payments_rounded,
                                  isRtl ? 'الحجم المالي والإنفاق' : 'Financial Volume',
                                  const Color(0xFF059669),
                                ),
                              ),
                              DataColumn(
                                label: _buildColumnHeader(
                                  Icons.calendar_today_rounded,
                                  isRtl ? 'الانضمام' : 'Joined',
                                  const Color(0xFFD97706),
                                ),
                              ),
                              DataColumn(
                                label: _buildColumnHeader(
                                  Icons.tune_rounded,
                                  isRtl ? 'الإجراءات السريعة' : 'Quick Actions',
                                  const Color(0xFF334155),
                                ),
                              ),
                            ],
                            rows: List.generate(subs.length, (index) {
                              final sub = subs[index];
                              final statusColor = _getStatusColor(sub.status);
                              final statusText = _getStatusName(sub.status, isRtl);
                              final roleColor = _getRoleColor(sub.role);
                              final roleName = _getRoleName(sub.role, isRtl);
                              final displayName = isRtl ? (sub.nameAr.isNotEmpty ? sub.nameAr : sub.name) : sub.name;
                              final isB2bEnabled = sub.permissions['b2b_credit_enabled'] == true;
                              final hasAds = sub.isAdvertiser || sub.totalAdsCount > 0;
                              final secondaryPhone = sub.permissions['secondary_phone']?.toString().trim() ?? '';

                              // Semantic Row Background Color for Immediate Visual Readability
                              Color rowBgColor;
                              if (sub.status == SubscriberStatus.suspended) {
                                rowBgColor = const Color(0xFFFFF1F2).withValues(alpha: 0.55);
                              } else if (sub.status == SubscriberStatus.pendingVerification) {
                                rowBgColor = const Color(0xFFFFFBEB).withValues(alpha: 0.65);
                              } else if (sub.status == SubscriberStatus.rejectedVerification) {
                                rowBgColor = const Color(0xFFFEF2F2).withValues(alpha: 0.45);
                              } else {
                                rowBgColor = index.isEven ? Colors.white : const Color(0xFFF8FAFC);
                              }

                              return DataRow(
                                color: WidgetStatePropertyAll(rowBgColor),
                                onSelectChanged: (_) => _showDetailSheet(context, sub),
                                cells: [
                                  // 0. Serial Row Index
                                  DataCell(
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFF1F5F9),
                                        borderRadius: BorderRadius.circular(6),
                                        border: Border.all(color: const Color(0xFFE2E8F0)),
                                      ),
                                      child: Text(
                                        '${index + 1}',
                                        style: const TextStyle(
                                          fontWeight: FontWeight.w800,
                                          fontSize: 11.5,
                                          color: Color(0xFF475569),
                                        ),
                                      ),
                                    ),
                                  ),

                                  // 1. Subscriber Avatar, Name, Membership ID & Special Badges
                                  DataCell(
                                    Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Stack(
                                          clipBehavior: Clip.none,
                                          children: [
                                            Container(
                                              height: 42,
                                              width: 42,
                                              decoration: BoxDecoration(
                                                gradient: LinearGradient(
                                                  colors: [roleColor, roleColor.withValues(alpha: 0.75)],
                                                  begin: Alignment.topLeft,
                                                  end: Alignment.bottomRight,
                                                ),
                                                borderRadius: BorderRadius.circular(12),
                                                boxShadow: [
                                                  BoxShadow(
                                                    color: roleColor.withValues(alpha: 0.22),
                                                    blurRadius: 6,
                                                    offset: const Offset(0, 2),
                                                  ),
                                                ],
                                              ),
                                              child: Center(
                                                child: Text(
                                                  displayName.trim().isNotEmpty ? displayName.trim()[0].toUpperCase() : 'U',
                                                  style: const TextStyle(
                                                    color: Colors.white,
                                                    fontWeight: FontWeight.w900,
                                                    fontSize: 16,
                                                  ),
                                                ),
                                              ),
                                            ),
                                            if (sub.isVerified)
                                              Positioned(
                                                bottom: -3,
                                                right: -3,
                                                child: Container(
                                                  padding: const EdgeInsets.all(1.5),
                                                  decoration: const BoxDecoration(
                                                    color: Colors.white,
                                                    shape: BoxShape.circle,
                                                  ),
                                                  child: const Icon(
                                                    Icons.verified_rounded,
                                                    size: 14,
                                                    color: Color(0xFF2563EB),
                                                  ),
                                                ),
                                              ),
                                          ],
                                        ),
                                        const SizedBox(width: 12),
                                        Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            Row(
                                              mainAxisSize: MainAxisSize.min,
                                              children: [
                                                ConstrainedBox(
                                                  constraints: const BoxConstraints(maxWidth: 165),
                                                  child: Text(
                                                    displayName,
                                                    style: const TextStyle(
                                                      fontWeight: FontWeight.w800,
                                                      color: Color(0xFF0F172A),
                                                      fontSize: 13.5,
                                                    ),
                                                    maxLines: 1,
                                                    overflow: TextOverflow.ellipsis,
                                                  ),
                                                ),
                                                if (hasAds) ...[
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
                                                          style: const TextStyle(
                                                            fontSize: 10,
                                                            fontWeight: FontWeight.w800,
                                                            color: Color(0xFFC026D3),
                                                          ),
                                                        ),
                                                      ],
                                                    ),
                                                  ),
                                                ],
                                              ],
                                            ),
                                            const SizedBox(height: 4),
                                            Row(
                                              mainAxisSize: MainAxisSize.min,
                                              children: [
                                                Container(
                                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
                                                  decoration: BoxDecoration(
                                                    color: const Color(0xFFF1F5F9),
                                                    borderRadius: BorderRadius.circular(4),
                                                    border: Border.all(color: const Color(0xFFCBD5E1)),
                                                  ),
                                                  child: Text(
                                                    sub.membershipNumber.isNotEmpty
                                                        ? sub.membershipNumber
                                                        : 'ID:${sub.id.length > 6 ? sub.id.substring(0, 6).toUpperCase() : sub.id}',
                                                    style: const TextStyle(
                                                      color: Color(0xFF475569),
                                                      fontSize: 10.5,
                                                      fontWeight: FontWeight.w700,
                                                      fontFamily: 'monospace',
                                                    ),
                                                  ),
                                                ),
                                                if (isB2bEnabled) ...[
                                                  const SizedBox(width: 5),
                                                  Container(
                                                    padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                                                    decoration: BoxDecoration(
                                                      color: const Color(0xFFFAF5FF),
                                                      borderRadius: BorderRadius.circular(4),
                                                      border: Border.all(color: const Color(0xFFD8B4FE)),
                                                    ),
                                                    child: const Text(
                                                      'B2B',
                                                      style: TextStyle(
                                                        color: Color(0xFF7E22CE),
                                                        fontSize: 9.5,
                                                        fontWeight: FontWeight.w900,
                                                      ),
                                                    ),
                                                  ),
                                                ],
                                              ],
                                            ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),

                                  // 2. Contact Info (Email, Primary Phone & Secondary Phone)
                                  DataCell(
                                    Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            const Icon(Icons.email_outlined, size: 13, color: Color(0xFF2563EB)),
                                            const SizedBox(width: 5),
                                            ConstrainedBox(
                                              constraints: const BoxConstraints(maxWidth: 165),
                                              child: Text(
                                                sub.email.isNotEmpty ? sub.email : '-',
                                                style: const TextStyle(
                                                  color: Color(0xFF1E293B),
                                                  fontSize: 11.5,
                                                  fontWeight: FontWeight.w600,
                                                ),
                                                maxLines: 1,
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 3),
                                        Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            const Icon(Icons.phone_iphone_rounded, size: 13, color: Color(0xFF059669)),
                                            const SizedBox(width: 5),
                                            if (sub.phone.isNotEmpty)
                                              PhoneFormatter.text(
                                                sub.phone,
                                                style: const TextStyle(
                                                  color: Color(0xFF0F172A),
                                                  fontSize: 11.5,
                                                  fontWeight: FontWeight.w800,
                                                  fontFamily: 'monospace',
                                                ),
                                              )
                                            else
                                              Text(
                                                isRtl ? 'بدون هاتف' : 'No phone',
                                                style: const TextStyle(
                                                  color: Color(0xFF94A3B8),
                                                  fontSize: 11.5,
                                                  fontWeight: FontWeight.w700,
                                                ),
                                              ),
                                          ],
                                        ),
                                        if (secondaryPhone.isNotEmpty) ...[
                                          const SizedBox(height: 2),
                                          Row(
                                            mainAxisSize: MainAxisSize.min,
                                            children: [
                                              const Icon(Icons.phone_callback_rounded, size: 11.5, color: Color(0xFF7E22CE)),
                                              const SizedBox(width: 4),
                                              PhoneFormatter.text(
                                                secondaryPhone,
                                                style: const TextStyle(
                                                  color: Color(0xFF6B21A8),
                                                  fontSize: 10.5,
                                                  fontWeight: FontWeight.w700,
                                                  fontFamily: 'monospace',
                                                ),
                                              ),
                                            ],
                                          ),
                                        ],
                                      ],
                                    ),
                                  ),

                                  // 3. Role & Tier / Points
                                  DataCell(
                                    Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                          decoration: BoxDecoration(
                                            color: roleColor.withValues(alpha: 0.12),
                                            borderRadius: BorderRadius.circular(8),
                                            border: Border.all(color: roleColor.withValues(alpha: 0.35)),
                                          ),
                                          child: Row(
                                            mainAxisSize: MainAxisSize.min,
                                            children: [
                                              Icon(_getRoleIcon(sub.role), size: 13.5, color: roleColor),
                                              const SizedBox(width: 5),
                                              Text(
                                                roleName,
                                                style: TextStyle(
                                                  color: roleColor,
                                                  fontSize: 11.5,
                                                  fontWeight: FontWeight.w800,
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                        if (sub.role == UserRole.merchant) ...[
                                          const SizedBox(height: 4),
                                          Row(
                                            mainAxisSize: MainAxisSize.min,
                                            children: [
                                              const Icon(Icons.stars_rounded, size: 12, color: Color(0xFFD97706)),
                                              const SizedBox(width: 3),
                                              Text(
                                                '${sub.merchantPoints} ${isRtl ? "نقطة" : "pts"} • ${_formatTierName(sub.merchantTier, isRtl)}',
                                                style: const TextStyle(
                                                  fontSize: 10.5,
                                                  fontWeight: FontWeight.w700,
                                                  color: Color(0xFFB45309),
                                                ),
                                              ),
                                            ],
                                          ),
                                        ],
                                      ],
                                    ),
                                  ),

                                  // 4. Status & Verification
                                  DataCell(
                                    Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                          decoration: BoxDecoration(
                                            color: statusColor.withValues(alpha: 0.12),
                                            borderRadius: BorderRadius.circular(20),
                                            border: Border.all(color: statusColor.withValues(alpha: 0.35)),
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
                                                style: TextStyle(
                                                  color: statusColor,
                                                  fontSize: 11.5,
                                                  fontWeight: FontWeight.w800,
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                        const SizedBox(height: 3),
                                        Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            Icon(
                                              sub.isVerified ? Icons.verified_rounded : Icons.gpp_maybe_outlined,
                                              size: 11.5,
                                              color: sub.isVerified ? const Color(0xFF2563EB) : const Color(0xFFD97706),
                                            ),
                                            const SizedBox(width: 3),
                                            Text(
                                              sub.isVerified
                                                  ? (isRtl ? 'حساب موثق' : 'Verified')
                                                  : (isRtl ? 'غير موثق' : 'Unverified'),
                                              style: TextStyle(
                                                fontSize: 10,
                                                fontWeight: FontWeight.w700,
                                                color: sub.isVerified ? const Color(0xFF1E40AF) : const Color(0xFFB45309),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),

                                  // 5. Store / Entity & Configured Categories Preview
                                  DataCell(
                                    Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        InkWell(
                                          onTap: () => _showEditSubscriberBasicAndStoreDialog(sub),
                                          borderRadius: BorderRadius.circular(8),
                                          child: Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                            decoration: BoxDecoration(
                                              color: sub.role == UserRole.merchant
                                                  ? const Color(0xFFECFDF5)
                                                  : const Color(0xFFF8FAFC),
                                              borderRadius: BorderRadius.circular(8),
                                              border: Border.all(
                                                color: sub.role == UserRole.merchant
                                                    ? const Color(0xFFA7F3D0)
                                                    : const Color(0xFFE2E8F0),
                                              ),
                                            ),
                                            child: Row(
                                              mainAxisSize: MainAxisSize.min,
                                              children: [
                                                Icon(
                                                  sub.role == UserRole.merchant
                                                      ? Icons.storefront_rounded
                                                      : (sub.role == UserRole.employee ? Icons.support_agent_rounded : Icons.person_outline_rounded),
                                                  size: 13.5,
                                                  color: sub.role == UserRole.merchant
                                                      ? const Color(0xFF059669)
                                                      : const Color(0xFF64748B),
                                                ),
                                                const SizedBox(width: 5),
                                                ConstrainedBox(
                                                  constraints: const BoxConstraints(maxWidth: 150),
                                                  child: Text(
                                                    sub.storeName != null && sub.storeName!.isNotEmpty
                                                        ? (isRtl && sub.storeNameAr != null && sub.storeNameAr!.isNotEmpty
                                                            ? sub.storeNameAr!
                                                            : sub.storeName!)
                                                        : (sub.role == UserRole.merchant
                                                            ? (isRtl ? 'متجر معتمد' : 'Verified Store')
                                                            : (sub.role == UserRole.employee
                                                                ? (isRtl ? 'إدارة المنصة' : 'Platform Team')
                                                                : (isRtl ? 'مشتري مباشر' : 'Direct Buyer'))),
                                                    style: TextStyle(
                                                      fontWeight: FontWeight.w800,
                                                      fontSize: 11.5,
                                                      color: sub.role == UserRole.merchant
                                                          ? const Color(0xFF065F46)
                                                          : const Color(0xFF475569),
                                                    ),
                                                    maxLines: 1,
                                                    overflow: TextOverflow.ellipsis,
                                                  ),
                                                ),
                                                if (sub.role == UserRole.merchant) ...[
                                                  const SizedBox(width: 4),
                                                  const Icon(Icons.edit_outlined, size: 11, color: Color(0xFF059669)),
                                                ],
                                              ],
                                            ),
                                          ),
                                        ),
                                        if (sub.role == UserRole.merchant) ...[
                                          const SizedBox(height: 4),
                                          _buildInlineMerchantCategoriesPreview(sub, allCategories, isRtl),
                                        ],
                                      ],
                                    ),
                                  ),

                                  // 6. Orders Count
                                  DataCell(
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                      decoration: BoxDecoration(
                                        color: sub.totalOrders > 0 ? const Color(0xFFEFF6FF) : const Color(0xFFF8FAFC),
                                        borderRadius: BorderRadius.circular(8),
                                        border: Border.all(
                                          color: sub.totalOrders > 0 ? const Color(0xFFBFDBFE) : const Color(0xFFE2E8F0),
                                        ),
                                      ),
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Icon(
                                            Icons.shopping_bag_outlined,
                                            size: 13.5,
                                            color: sub.totalOrders > 0 ? const Color(0xFF1E40AF) : const Color(0xFF94A3B8),
                                          ),
                                          const SizedBox(width: 4),
                                          Text(
                                            '${sub.totalOrders} ${isRtl ? "طلب" : "Orders"}',
                                            style: TextStyle(
                                              fontWeight: FontWeight.w800,
                                              fontSize: 11.5,
                                              color: sub.totalOrders > 0 ? const Color(0xFF1E40AF) : const Color(0xFF64748B),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),

                                  // 7. Financial Volume & Ad Spend (Color-Coded by Role)
                                  DataCell(
                                    Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                          decoration: BoxDecoration(
                                            color: sub.totalSpent > 0
                                                ? (sub.role == UserRole.merchant
                                                    ? const Color(0xFFECFDF5)
                                                    : const Color(0xFFEFF6FF))
                                                : Colors.transparent,
                                            borderRadius: BorderRadius.circular(6),
                                          ),
                                          child: Text(
                                            '${NumberFormat('#,##0.##', isRtl ? 'ar' : 'en').format(sub.totalSpent)} ج.س',
                                            style: TextStyle(
                                              fontWeight: FontWeight.w900,
                                              fontSize: 12.5,
                                              color: sub.totalSpent > 0
                                                  ? (sub.role == UserRole.merchant
                                                      ? const Color(0xFF047857)
                                                      : const Color(0xFF1E40AF))
                                                  : const Color(0xFF64748B),
                                            ),
                                          ),
                                        ),
                                        const SizedBox(height: 2),
                                        if (sub.totalAdSpend > 0)
                                          Text(
                                            isRtl
                                                ? 'إعلانات: ${NumberFormat('#,##0.##', 'ar').format(sub.totalAdSpend)} ج.س'
                                                : 'Ads: ${NumberFormat('#,##0.##', 'en').format(sub.totalAdSpend)} SDG',
                                            style: const TextStyle(
                                              fontSize: 10,
                                              color: Color(0xFFC026D3),
                                              fontWeight: FontWeight.w800,
                                            ),
                                          )
                                        else
                                          Padding(
                                            padding: const EdgeInsets.symmetric(horizontal: 4),
                                            child: Text(
                                              sub.role == UserRole.merchant
                                                  ? (isRtl ? 'إجمالي المبيعات' : 'Total Sales')
                                                  : (isRtl ? 'إجمالي المشتريات' : 'Total Purchases'),
                                              style: const TextStyle(
                                                color: Color(0xFF64748B),
                                                fontSize: 10,
                                                fontWeight: FontWeight.w600,
                                              ),
                                            ),
                                          ),
                                      ],
                                    ),
                                  ),

                                  // 8. Joined Date & Account Age
                                  DataCell(
                                    Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Text(
                                          DateFormat('yyyy-MM-dd').format(sub.joinedAt),
                                          style: const TextStyle(
                                            fontWeight: FontWeight.w700,
                                            color: Color(0xFF334155),
                                            fontSize: 12,
                                          ),
                                        ),
                                        const SizedBox(height: 2),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                                          decoration: BoxDecoration(
                                            color: const Color(0xFFFFFBEB),
                                            borderRadius: BorderRadius.circular(4),
                                          ),
                                          child: Text(
                                            isRtl
                                                ? 'منذ ${DateTime.now().difference(sub.joinedAt).inDays} يوم'
                                                : '${DateTime.now().difference(sub.joinedAt).inDays}d ago',
                                            style: const TextStyle(
                                              color: Color(0xFFB45309),
                                              fontSize: 10,
                                              fontWeight: FontWeight.w700,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),

                                  // 9. Quick Actions Bar
                                  DataCell(
                                    Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        if (sub.status == SubscriberStatus.pendingVerification) ...[
                                          Tooltip(
                                            message: isRtl ? 'اعتماد الحساب وترقيته فوراً' : 'Approve Now',
                                            child: IconButton.filled(
                                              onPressed: () => _runAsync(() => ref.read(subscribersProvider.notifier).approveMerchant(sub.id)),
                                              icon: const Icon(Icons.check_rounded, size: 16),
                                              style: IconButton.styleFrom(
                                                backgroundColor: const Color(0xFF059669),
                                                foregroundColor: Colors.white,
                                                minimumSize: const Size(32, 32),
                                                padding: EdgeInsets.zero,
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                              ),
                                            ),
                                          ),
                                          const SizedBox(width: 5),
                                        ],
                                        Tooltip(
                                          message: isRtl ? 'تعديل الاسم واسم المتجر وأرقام الهاتف' : 'Edit Profile, Store & Phone',
                                          child: IconButton.outlined(
                                            onPressed: () => _showEditSubscriberBasicAndStoreDialog(sub),
                                            icon: const Icon(Icons.edit_note_rounded, size: 17, color: Color(0xFF1E40AF)),
                                            style: IconButton.styleFrom(
                                              backgroundColor: const Color(0xFFF8FAFC),
                                              side: const BorderSide(color: Color(0xFFCBD5E1)),
                                              minimumSize: const Size(34, 34),
                                              padding: EdgeInsets.zero,
                                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                            ),
                                          ),
                                        ),
                                        const SizedBox(width: 5),
                                        FilledButton.tonalIcon(
                                          icon: const Icon(Icons.visibility_rounded, size: 15),
                                          label: Text(
                                            isRtl ? 'التفاصيل' : 'Details',
                                            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800),
                                          ),
                                          style: FilledButton.styleFrom(
                                            backgroundColor: const Color(0xFFEFF6FF),
                                            foregroundColor: const Color(0xFF1E40AF),
                                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                            shape: RoundedRectangleBorder(
                                              borderRadius: BorderRadius.circular(8),
                                              side: const BorderSide(color: Color(0xFFBFDBFE)),
                                            ),
                                          ),
                                          onPressed: () => _showDetailSheet(context, sub),
                                        ),
                                        const SizedBox(width: 4),
                                        PopupMenuButton<String>(
                                          icon: const Icon(Icons.more_vert_rounded, size: 20, color: Color(0xFF475569)),
                                          tooltip: isRtl ? 'خيارات سريعة' : 'More Actions',
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                                          itemBuilder: (ctx) => [
                                            PopupMenuItem(
                                              value: 'profile',
                                              child: Row(
                                                children: [
                                                  const Icon(Icons.badge_outlined, size: 18, color: Color(0xFF1E40AF)),
                                                  const SizedBox(width: 8),
                                                  Text(isRtl ? 'فتح الملف الكامل' : 'Full Profile', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                                                ],
                                              ),
                                            ),
                                            PopupMenuItem(
                                              value: 'edit_info',
                                              child: Row(
                                                children: [
                                                  const Icon(Icons.edit_note_rounded, size: 19, color: Color(0xFF0284C7)),
                                                  const SizedBox(width: 8),
                                                  Text(
                                                    isRtl ? 'تعديل البيانات واسم المتجر والهاتف' : 'Edit Profile, Store & Phone',
                                                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700),
                                                  ),
                                                ],
                                              ),
                                            ),
                                            PopupMenuItem(
                                              value: 'role',
                                              child: Row(
                                                children: [
                                                  const Icon(Icons.manage_accounts_rounded, size: 18, color: Color(0xFF7E22CE)),
                                                  const SizedBox(width: 8),
                                                  Text(isRtl ? 'تغيير الدور / الصلاحية' : 'Change Role', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
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
                                                    Text(isRtl ? 'الأقسام والعمولات' : 'Categories & Commissions', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                                                  ],
                                                ),
                                              ),
                                            if (hasAds)
                                              PopupMenuItem(
                                                value: 'ads',
                                                child: Row(
                                                  children: [
                                                    const Icon(Icons.campaign_rounded, size: 18, color: Color(0xFFC026D3)),
                                                    const SizedBox(width: 8),
                                                    Text(
                                                      isRtl ? 'الحملات الإعلانية (${sub.totalAdsCount})' : 'Ad Campaigns (${sub.totalAdsCount})',
                                                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
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
                                                        : (isRtl ? 'إيقاف الحساب مؤقتاً' : 'Suspend'),
                                                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
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
                                                  Text(
                                                    isRtl ? 'حذف الحساب' : 'Delete',
                                                    style: const TextStyle(fontSize: 13, color: Color(0xFFDC2626), fontWeight: FontWeight.w700),
                                                  ),
                                                ],
                                              ),
                                            ),
                                          ],
                                          onSelected: (val) {
                                            switch (val) {
                                              case 'profile':
                                                _showDetailSheet(context, sub);
                                                break;
                                              case 'edit_info':
                                                _showEditSubscriberBasicAndStoreDialog(sub);
                                                break;
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
                            }),
                          ),
                        ),
                      );
                    },
                  ),
                  if (notifier.isLoadingMore) ...[
                    const SizedBox(height: 16),
                    const Center(
                      child: Padding(
                        padding: EdgeInsets.all(12.0),
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

  /// Builds an inline preview of the Merchant's authorized categories and custom commission right in the table row
  Widget _buildInlineMerchantCategoriesPreview(
    SubscriberModel sub,
    List<CategoryModel> allCategories,
    bool isRtl,
  ) {
    final rawAllowed = sub.permissions['allowed_categories'];
    bool allowAll = false;
    Set<String> allowedIds = {};
    if (rawAllowed == 'all' || rawAllowed == null) {
      allowAll = true;
    } else if (rawAllowed is List) {
      allowedIds = rawAllowed.map((e) => e.toString()).toSet();
      if (allowedIds.isEmpty) allowAll = true;
    }

    final bool hasCustomStoreComm = sub.permissions['has_custom_commission'] == true;
    final dynamic customStoreCommPct = sub.permissions['custom_commission_percentage'];
    final rawComms = sub.permissions['category_commissions'];
    final int customCatCommsCount = rawComms is Map ? rawComms.length : 0;

    // Resolve names of authorized categories
    final List<String> resolvedCatNames = [];
    if (!allowAll && allCategories.isNotEmpty) {
      for (final cat in allCategories) {
        final isMain = allowedIds.contains(cat.id);
        final hasSub = cat.subCategories.any((s) => allowedIds.contains(s.id));
        if (isMain || hasSub) {
          resolvedCatNames.add(isRtl ? (cat.nameAr.isNotEmpty ? cat.nameAr : cat.name) : cat.name);
        }
      }
    }

    return InkWell(
      onTap: () => _showCategoryPermissionsDialog(sub),
      borderRadius: BorderRadius.circular(6),
      child: Wrap(
        spacing: 4,
        runSpacing: 3,
        crossAxisAlignment: WrapCrossAlignment.center,
        children: [
          if (allowAll)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
              decoration: BoxDecoration(
                color: const Color(0xFFECFEFF),
                borderRadius: BorderRadius.circular(5),
                border: Border.all(color: const Color(0xFFA5F3FC)),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.account_tree_rounded, size: 10.5, color: Color(0xFF0891B2)),
                  const SizedBox(width: 3),
                  Text(
                    isRtl ? 'كل الأقسام' : 'All Categories',
                    style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.w800, color: Color(0xFF0E7490)),
                  ),
                ],
              ),
            )
          else if (resolvedCatNames.isNotEmpty) ...[
            ...resolvedCatNames.take(2).map((name) => Container(
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                  decoration: BoxDecoration(
                    color: const Color(0xFFEFF6FF),
                    borderRadius: BorderRadius.circular(5),
                    border: Border.all(color: const Color(0xFFBFDBFE)),
                  ),
                  child: Text(
                    name,
                    style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.w800, color: Color(0xFF1E40AF)),
                  ),
                )),
            if (resolvedCatNames.length > 2)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                decoration: BoxDecoration(
                  color: const Color(0xFF1E40AF),
                  borderRadius: BorderRadius.circular(5),
                ),
                child: Text(
                  '+${resolvedCatNames.length - 2}',
                  style: const TextStyle(fontSize: 9, fontWeight: FontWeight.w900, color: Colors.white),
                ),
              ),
          ] else
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
              decoration: BoxDecoration(
                color: const Color(0xFFEFF6FF),
                borderRadius: BorderRadius.circular(5),
                border: Border.all(color: const Color(0xFFBFDBFE)),
              ),
              child: Text(
                isRtl ? '${allowedIds.length} قسم مخصص' : '${allowedIds.length} Custom Cats',
                style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.w800, color: Color(0xFF1E40AF)),
              ),
            ),

          if (hasCustomStoreComm && customStoreCommPct != null)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
              decoration: BoxDecoration(
                color: const Color(0xFFFEF3C7),
                borderRadius: BorderRadius.circular(5),
                border: Border.all(color: const Color(0xFFF59E0B)),
              ),
              child: Text(
                isRtl ? 'عمولة $customStoreCommPct%' : '$customStoreCommPct%',
                style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.w900, color: Color(0xFFB45309)),
              ),
            )
          else if (customCatCommsCount > 0)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
              decoration: BoxDecoration(
                color: const Color(0xFFFEF3C7),
                borderRadius: BorderRadius.circular(5),
                border: Border.all(color: const Color(0xFFF59E0B)),
              ),
              child: Text(
                isRtl ? '$customCatCommsCount عمولة قسم' : '$customCatCommsCount Rates',
                style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.w900, color: Color(0xFFB45309)),
              ),
            ),

          if (sub.directPaymentEnabled)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
              decoration: BoxDecoration(
                color: const Color(0xFFECFDF5),
                borderRadius: BorderRadius.circular(5),
                border: Border.all(color: const Color(0xFF6EE7B7)),
              ),
              child: Text(
                isRtl ? 'دفع مباشر' : 'Direct Pay',
                style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.w800, color: Color(0xFF059669)),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildColumnHeader(IconData icon, String title, Color color) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 15, color: color),
        const SizedBox(width: 6),
        Text(
          title,
          style: const TextStyle(
            fontWeight: FontWeight.w900,
            color: Color(0xFF0F172A),
            fontSize: 12.5,
          ),
        ),
      ],
    );
  }

  String _formatTierName(String tier, bool isRtl) {
    switch (tier.toLowerCase()) {
      case 'gold':
      case 'golden':
        return isRtl ? 'ذهبي' : 'Gold';
      case 'silver':
        return isRtl ? 'فضي' : 'Silver';
      case 'bronze':
        return isRtl ? 'برونزي' : 'Bronze';
      case 'platinum':
      case 'vip':
        return isRtl ? 'بلاتيني' : 'Platinum';
      default:
        return isRtl ? 'مبتدئ' : 'Starter';
    }
  }

  // ===========================================================================
  // HELPER DIALOGS & METHODS
  // ===========================================================================

  void _showDetailSheet(BuildContext context, SubscriberModel subscriber) {
    setState(() => _selectedSubscriber = subscriber);
  }

  /// Comprehensive Dialog to Edit Subscriber's Basic Info, Store Name (Ar/En), Phone Numbers & Tier
  void _showEditSubscriberBasicAndStoreDialog(SubscriberModel subscriber) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    final nameArCtrl = TextEditingController(text: subscriber.nameAr.isNotEmpty ? subscriber.nameAr : subscriber.name);
    final nameEnCtrl = TextEditingController(text: subscriber.name);
    final emailCtrl = TextEditingController(text: subscriber.email);
    final phoneCtrl = TextEditingController(text: PhoneFormatter.format(subscriber.phone));
    final secondaryPhoneCtrl = TextEditingController(
      text: PhoneFormatter.format(subscriber.permissions['secondary_phone']?.toString() ?? ''),
    );
    final addressCtrl = TextEditingController(
      text: subscriber.permissions['address']?.toString() ?? '',
    );
    final membershipCtrl = TextEditingController(text: subscriber.membershipNumber);
    final storeNameArCtrl = TextEditingController(
      text: subscriber.storeNameAr ?? subscriber.storeName ?? '',
    );
    final storeNameEnCtrl = TextEditingController(
      text: subscriber.storeName ?? subscriber.storeNameAr ?? '',
    );
    final docTypeCtrl = TextEditingController(text: subscriber.documentType ?? '');

    bool isVerified = subscriber.isVerified;
    String selectedTier = ['beginner', 'bronze', 'silver', 'gold', 'platinum']
            .contains(subscriber.merchantTier.toLowerCase())
        ? subscriber.merchantTier.toLowerCase()
        : 'beginner';

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (dlgCtx, setDlgState) {
          return AlertDialog(
            backgroundColor: Colors.white,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
            titlePadding: const EdgeInsets.fromLTRB(24, 22, 24, 14),
            contentPadding: const EdgeInsets.symmetric(horizontal: 24, vertical: 8),
            title: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: const Color(0xFFEFF6FF),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFBFDBFE)),
                  ),
                  child: const Icon(Icons.edit_note_rounded, color: Color(0xFF1E40AF), size: 24),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        isRtl ? 'تعديل البيانات الأساسية واسم المتجر والهاتف' : 'Edit Subscriber, Store & Contact Info',
                        style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 17, color: Color(0xFF0F172A)),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        isRtl
                            ? 'يتم تحديث بيانات المشترك ومتجره فورياً في قاعدة البيانات والسوق'
                            : 'Updates subscriber profile and store record across the marketplace',
                        style: const TextStyle(fontSize: 12, color: Color(0xFF64748B), fontWeight: FontWeight.w500),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            content: SizedBox(
              width: 560,
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Section 1: Basic Identity
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.person_rounded, size: 17, color: Color(0xFF1E40AF)),
                              const SizedBox(width: 6),
                              Text(
                                isRtl ? 'البيانات الشخصية ورقم العضوية' : 'Personal Identity & Membership',
                                style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: Color(0xFF1E40AF)),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          Row(
                            children: [
                              Expanded(
                                child: TextField(
                                  controller: nameArCtrl,
                                  decoration: InputDecoration(
                                    labelText: isRtl ? 'الاسم بالعربية *' : 'Arabic Name *',
                                    prefixIcon: const Icon(Icons.badge_outlined, size: 18),
                                    filled: true,
                                    fillColor: Colors.white,
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                    isDense: true,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: TextField(
                                  controller: nameEnCtrl,
                                  decoration: InputDecoration(
                                    labelText: isRtl ? 'الاسم بالإنجليزية' : 'English Name',
                                    prefixIcon: const Icon(Icons.person_outline, size: 18),
                                    filled: true,
                                    fillColor: Colors.white,
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                    isDense: true,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 10),
                          Row(
                            children: [
                              Expanded(
                                child: TextField(
                                  controller: membershipCtrl,
                                  decoration: InputDecoration(
                                    labelText: isRtl ? 'رقم العضوية المرجعي' : 'Membership No.',
                                    prefixIcon: const Icon(Icons.card_membership_rounded, size: 18),
                                    filled: true,
                                    fillColor: Colors.white,
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                    isDense: true,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: TextField(
                                  controller: docTypeCtrl,
                                  decoration: InputDecoration(
                                    labelText: isRtl ? 'نوع الوثيقة (سجل تجاري/هوية)' : 'Document Type',
                                    prefixIcon: const Icon(Icons.description_outlined, size: 18),
                                    filled: true,
                                    fillColor: Colors.white,
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                    isDense: true,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Section 2: Phone Numbers & Contact Details
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF0FDF4),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: const Color(0xFFBBF7D0)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.contact_phone_rounded, size: 17, color: Color(0xFF059669)),
                              const SizedBox(width: 6),
                              Text(
                                isRtl ? 'أرقام الهاتف وبيانات الاتصال والعنوان' : 'Phone Numbers, Email & Address',
                                style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: Color(0xFF065F46)),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          Row(
                            children: [
                              Expanded(
                                child: TextField(
                                  controller: phoneCtrl,
                                  keyboardType: TextInputType.phone,
                                  textDirection: TextDirection.ltr,
                                  decoration: InputDecoration(
                                    labelText: isRtl ? 'رقم الهاتف الأساسي *' : 'Primary Phone *',
                                    prefixIcon: const Icon(Icons.phone_iphone_rounded, size: 18, color: Color(0xFF059669)),
                                    filled: true,
                                    fillColor: Colors.white,
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                    isDense: true,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: TextField(
                                  controller: secondaryPhoneCtrl,
                                  keyboardType: TextInputType.phone,
                                  textDirection: TextDirection.ltr,
                                  decoration: InputDecoration(
                                    labelText: isRtl ? 'رقم هاتف إضافي / واتساب' : 'Secondary / WhatsApp Phone',
                                    prefixIcon: const Icon(Icons.phone_callback_rounded, size: 18, color: Color(0xFF7E22CE)),
                                    filled: true,
                                    fillColor: Colors.white,
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                    isDense: true,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 10),
                          Row(
                            children: [
                              Expanded(
                                child: TextField(
                                  controller: emailCtrl,
                                  keyboardType: TextInputType.emailAddress,
                                  textDirection: TextDirection.ltr,
                                  decoration: InputDecoration(
                                    labelText: isRtl ? 'البريد الإلكتروني' : 'Email Address',
                                    prefixIcon: const Icon(Icons.email_outlined, size: 18, color: Color(0xFF2563EB)),
                                    filled: true,
                                    fillColor: Colors.white,
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                    isDense: true,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: TextField(
                                  controller: addressCtrl,
                                  decoration: InputDecoration(
                                    labelText: isRtl ? 'المدينة / العنوان' : 'City / Address',
                                    prefixIcon: const Icon(Icons.location_on_outlined, size: 18, color: Color(0xFFD97706)),
                                    filled: true,
                                    fillColor: Colors.white,
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                    isDense: true,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Section 3: Store Name (Ar/En), Merchant Tier & Verification Status
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFFFBEB),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: const Color(0xFFFDE68A)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.storefront_rounded, size: 17, color: Color(0xFFD97706)),
                              const SizedBox(width: 6),
                              Text(
                                isRtl ? 'اسم المتجر والرتبة والتوثيق' : 'Store Name, Tier & Verification',
                                style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: Color(0xFF92400E)),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          Row(
                            children: [
                              Expanded(
                                child: TextField(
                                  controller: storeNameArCtrl,
                                  decoration: InputDecoration(
                                    labelText: isRtl ? 'اسم المتجر (بالعربية)' : 'Store Name (Arabic)',
                                    prefixIcon: const Icon(Icons.store_rounded, size: 18, color: Color(0xFF059669)),
                                    filled: true,
                                    fillColor: Colors.white,
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                    isDense: true,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: TextField(
                                  controller: storeNameEnCtrl,
                                  decoration: InputDecoration(
                                    labelText: isRtl ? 'اسم المتجر (بالإنجليزية)' : 'Store Name (English)',
                                    prefixIcon: const Icon(Icons.store_mall_directory_outlined, size: 18, color: Color(0xFF047857)),
                                    filled: true,
                                    fillColor: Colors.white,
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                    isDense: true,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 10),
                          DropdownButtonFormField<String>(
                            initialValue: selectedTier,
                            decoration: InputDecoration(
                              labelText: isRtl ? 'رتبة التاجر (Merchant Tier)' : 'Merchant Tier',
                              prefixIcon: const Icon(Icons.military_tech_rounded, size: 18, color: Color(0xFFD97706)),
                              filled: true,
                              fillColor: Colors.white,
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                              isDense: true,
                            ),
                            items: [
                              DropdownMenuItem(value: 'beginner', child: Text(isRtl ? 'مبتدئ (Starter)' : 'Starter')),
                              DropdownMenuItem(value: 'bronze', child: Text(isRtl ? 'برونزي (Bronze)' : 'Bronze')),
                              DropdownMenuItem(value: 'silver', child: Text(isRtl ? 'فضي (Silver)' : 'Silver')),
                              DropdownMenuItem(value: 'gold', child: Text(isRtl ? 'ذهبي (Gold)' : 'Gold')),
                              DropdownMenuItem(value: 'platinum', child: Text(isRtl ? 'بلاتينيوم (Platinum)' : 'Platinum')),
                            ],
                            onChanged: (v) {
                              if (v != null) setDlgState(() => selectedTier = v);
                            },
                          ),
                          const SizedBox(height: 8),
                          SwitchListTile(
                            title: Text(
                              isRtl ? 'توثيق الحساب رسمياً (Verified Badge)' : 'Official Account Verification',
                              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: Color(0xFF0F172A)),
                            ),
                            subtitle: Text(
                              isVerified
                                  ? (isRtl ? 'الحساب موثق وتظهر له شارة التوثيق الزرقاء' : 'Verified badge is active')
                                  : (isRtl ? 'الحساب غير موثق (قيد المراجعة)' : 'Account is unverified'),
                              style: const TextStyle(fontSize: 11.5, color: Color(0xFF64748B)),
                            ),
                            value: isVerified,
                            activeTrackColor: const Color(0xFF2563EB),
                            contentPadding: EdgeInsets.zero,
                            onChanged: (val) => setDlgState(() => isVerified = val),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
            actionsPadding: const EdgeInsets.fromLTRB(24, 8, 24, 20),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(dlgCtx),
                child: Text(isRtl ? 'إلغاء' : 'Cancel', style: const TextStyle(color: Color(0xFF64748B), fontWeight: FontWeight.w700)),
              ),
              FilledButton.icon(
                icon: const Icon(Icons.save_rounded, size: 18),
                label: Text(isRtl ? 'حفظ التعديلات' : 'Save Changes', style: const TextStyle(fontWeight: FontWeight.w800)),
                style: FilledButton.styleFrom(
                  backgroundColor: const Color(0xFF1E40AF),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                onPressed: () async {
                  if (nameArCtrl.text.trim().isEmpty && nameEnCtrl.text.trim().isEmpty) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text(isRtl ? 'يرجى إدخال اسم المشترك' : 'Subscriber name is required')),
                    );
                    return;
                  }
                  Navigator.pop(dlgCtx);
                  await _runAsync(() async {
                    try {
                      await ref.read(subscribersProvider.notifier).updateSubscriberBasicAndStoreInfo(
                            userId: subscriber.id,
                            name: nameEnCtrl.text.trim().isNotEmpty ? nameEnCtrl.text.trim() : nameArCtrl.text.trim(),
                            nameAr: nameArCtrl.text.trim().isNotEmpty ? nameArCtrl.text.trim() : nameEnCtrl.text.trim(),
                            email: emailCtrl.text.trim(),
                            phone: phoneCtrl.text.trim(),
                            secondaryPhone: secondaryPhoneCtrl.text.trim(),
                            cityOrAddress: addressCtrl.text.trim(),
                            membershipNumber: membershipCtrl.text.trim(),
                            storeName: storeNameEnCtrl.text.trim(),
                            storeNameAr: storeNameArCtrl.text.trim(),
                            isVerified: isVerified,
                            merchantTier: selectedTier,
                            documentType: docTypeCtrl.text.trim(),
                          );
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(
                              isRtl
                                  ? '✅ تم تحديث البيانات الأساسية واسم المتجر وأرقام الهاتف بنجاح'
                                  : 'Profile, store name & phone numbers updated successfully',
                            ),
                            backgroundColor: const Color(0xFF059669),
                            behavior: SnackBarBehavior.floating,
                          ),
                        );
                      }
                    } catch (e) {
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text('Error: $e'),
                            backgroundColor: const Color(0xFFDC2626),
                            behavior: SnackBarBehavior.floating,
                          ),
                        );
                      }
                    }
                  });
                },
              ),
            ],
          );
        },
      ),
    );
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
                      textDirection: TextDirection.ltr,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'رقم الهاتف' : 'Phone Number',
                        hintText: '+249 912 300 101',
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
    final currentPerms = subscriber.permissions['allowed_categories'] ??
        subscriber.permissions['requested_categories'];
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
                                                                        final allSubsSelected = cat.subCategories.isNotEmpty &&
                                                                            cat.subCategories.every((s) => selectedIds.contains(s.id));
                                                                        if (allSubsSelected) {
                                                                          selectedIds.add(cat.id);
                                                                          for (final s in cat.subCategories) {
                                                                            selectedIds.remove(s.id);
                                                                          }
                                                                        }
                                                                      } else {
                                                                        if (isMainSelected) {
                                                                          selectedIds.remove(cat.id);
                                                                          for (final s in cat.subCategories) {
                                                                            if (s.id != sub.id) {
                                                                              selectedIds.add(s.id);
                                                                            }
                                                                          }
                                                                        } else {
                                                                          selectedIds.remove(sub.id);
                                                                        }
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
              if (_selectedSubscriber?.id == subscriber.id) {
                setState(() => _selectedSubscriber = null);
              }
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

  void _showSubscriberUsdRateDialog(BuildContext context, bool isRtl) {
    final oldRate = ref.read(platformUsdRateSettingProvider);
    final rateCtrl = TextEditingController(text: oldRate.toStringAsFixed(0));

    showDialog(
      context: context,
      builder: (ctx) => Directionality(
        textDirection: isRtl ? TextDirection.rtl : TextDirection.ltr,
        child: AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
          title: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: const Color(0xFFECFDF5),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.currency_exchange_rounded, color: Color(0xFF059669), size: 24),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  isRtl ? 'ضبط سعر صرف الدولار المعتمد' : 'Platform USD Exchange Rate',
                  style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w900),
                ),
              ),
            ],
          ),
          content: SizedBox(
            width: 440,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF0FDF4),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFF86EFAC)),
                  ),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Icon(Icons.notifications_active_rounded, color: Color(0xFF059669), size: 19),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          isRtl
                              ? 'عند تغيير السعر، يظهر إشعار فوري أعلى الشاشة للتجار المرتبطين بسعر المنصة فقط (ولا يظهر للمشترين نهائياً).'
                              : 'Updating notifies synced Merchants ONLY at the top of their screen (Buyers are never notified).',
                          style: const TextStyle(fontSize: 12.5, color: Color(0xFF065F46), fontWeight: FontWeight.w600, height: 1.45),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: rateCtrl,
                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  autofocus: true,
                  decoration: InputDecoration(
                    labelText: isRtl ? 'سعر الدولار الجديد (ج.س)' : 'New USD Rate (SDG)',
                    helperText: isRtl
                        ? 'السعر الحالي: ${NumberFormat('#,##0').format(oldRate)} ج.س'
                        : 'Current rate: ${NumberFormat('#,##0').format(oldRate)} SDG',
                    suffixText: isRtl ? 'ج.س' : 'SDG',
                    prefixIcon: const Icon(Icons.attach_money_rounded, color: Color(0xFF059669)),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: Text(isRtl ? 'إلغاء' : 'Cancel'),
            ),
            FilledButton.icon(
              style: FilledButton.styleFrom(
                backgroundColor: const Color(0xFF059669),
                padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              icon: const Icon(Icons.check_circle_rounded, size: 18),
              label: Text(isRtl ? 'حفظ وإشعار التجار' : 'Save & Notify Merchants'),
              onPressed: () async {
                final newRate = double.tryParse(rateCtrl.text.trim());
                if (newRate != null && newRate > 0) {
                  Navigator.pop(ctx);
                  await ref.read(platformUsdRateSettingProvider.notifier).updateRate(newRate);
                  if (mounted) {
                    ScaffoldMessenger.of(this.context).showSnackBar(
                      SnackBar(
                        content: Text(
                          isRtl
                              ? '✅ تم تغيير سعر الدولار من ${NumberFormat('#,##0').format(oldRate)} إلى ${NumberFormat('#,##0').format(newRate)} ج.س وإشعار التجار المرتبطين'
                              : 'USD rate updated from ${NumberFormat('#,##0').format(oldRate)} to ${NumberFormat('#,##0').format(newRate)} SDG',
                        ),
                        backgroundColor: const Color(0xFF059669),
                        behavior: SnackBarBehavior.floating,
                      ),
                    );
                  }
                }
              },
            ),
          ],
        ),
      ),
    );
  }

  void _showBulkBroadcastDialog(BuildContext context, bool isRtl) {
    final snapshot = ref.read(subscribersProvider.notifier).allUsersSnapshot;
    final currentFiltered = ref.read(subscribersProvider).valueOrNull ?? [];
    final pool = (snapshot.isNotEmpty ? snapshot : currentFiltered)
        .where((s) => s.role != UserRole.admin && s.role != UserRole.guest)
        .toList();

    String selectedSegment = 'approved_merchants';
    String selectedType = 'admin_broadcast_info';
    bool isSending = false;

    final titleCtrl = TextEditingController(
      text: isRtl ? '📢 تعميم إداري من منصة مقاييس' : '📢 Official Notice from Magaiees',
    );
    final bodyCtrl = TextEditingController();

    List<SubscriberModel> resolveTargets(String segment) {
      switch (segment) {
        case 'approved_merchants':
          return pool.where((s) => s.role == UserRole.merchant && s.status == SubscriberStatus.active).toList();
        case 'buyers_active':
          return pool.where((s) => s.role == UserRole.buyer && s.status == SubscriberStatus.active).toList();
        case 'suspended':
          return pool.where((s) => s.status == SubscriberStatus.suspended).toList();
        case 'pending_merchants':
          return pool.where((s) => s.status == SubscriberStatus.pendingVerification).toList();
        case 'all_merchants':
          return pool.where((s) => s.role == UserRole.merchant).toList();
        case 'advertisers':
          return pool.where((s) => s.isAdvertiser || s.totalAdsCount > 0).toList();
        case 'current_filter':
          return currentFiltered.where((s) => s.role != UserRole.admin && s.role != UserRole.guest).toList();
        case 'all_active':
        default:
          return pool.where((s) => s.status == SubscriberStatus.active).toList();
      }
    }

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) {
          final targets = resolveTargets(selectedSegment);
          final segments = <Map<String, dynamic>>[
            {
              'id': 'approved_merchants',
              'label': isRtl ? 'التجار المعتمدون' : 'Approved Merchants',
              'icon': Icons.verified_rounded,
              'color': const Color(0xFF059669),
              'count': resolveTargets('approved_merchants').length,
            },
            {
              'id': 'buyers_active',
              'label': isRtl ? 'المشترون النشطون' : 'Active Buyers',
              'icon': Icons.shopping_bag_rounded,
              'color': const Color(0xFF1E40AF),
              'count': resolveTargets('buyers_active').length,
            },
            {
              'id': 'suspended',
              'label': isRtl ? 'الحسابات الموقوفة' : 'Suspended Accounts',
              'icon': Icons.block_rounded,
              'color': const Color(0xFFDC2626),
              'count': resolveTargets('suspended').length,
            },
            {
              'id': 'pending_merchants',
              'label': isRtl ? 'التجار قيد التوثيق' : 'Pending Merchants',
              'icon': Icons.hourglass_top_rounded,
              'color': const Color(0xFFD97706),
              'count': resolveTargets('pending_merchants').length,
            },
            {
              'id': 'all_merchants',
              'label': isRtl ? 'جميع التجار' : 'All Merchants',
              'icon': Icons.storefront_rounded,
              'color': const Color(0xFF0D9488),
              'count': resolveTargets('all_merchants').length,
            },
            {
              'id': 'advertisers',
              'label': isRtl ? 'المعلنون' : 'Advertisers',
              'icon': Icons.campaign_rounded,
              'color': const Color(0xFF9333EA),
              'count': resolveTargets('advertisers').length,
            },
            {
              'id': 'current_filter',
              'label': isRtl ? 'نتائج الفلتر الحالي' : 'Current Table Filter',
              'icon': Icons.filter_alt_rounded,
              'color': const Color(0xFF475569),
              'count': resolveTargets('current_filter').length,
            },
            {
              'id': 'all_active',
              'label': isRtl ? 'جميع المشتركين النشطين' : 'All Active Users',
              'icon': Icons.groups_rounded,
              'color': const Color(0xFF4F46E5),
              'count': resolveTargets('all_active').length,
            },
          ];

          final isWarn = selectedType == 'admin_broadcast_warning';
          final isPromo = selectedType == 'admin_broadcast_promo';
          final previewColors = isWarn
              ? const [Color(0xFF9A3412), Color(0xFFEA580C)]
              : (isPromo
                  ? const [Color(0xFF4C1D95), Color(0xFF7C3AED)]
                  : const [Color(0xFF1E3A8A), Color(0xFF2563EB)]);

          return Directionality(
            textDirection: isRtl ? TextDirection.rtl : TextDirection.ltr,
            child: Dialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
              insetPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 24),
              child: Container(
                width: 680,
                constraints: const BoxConstraints(maxHeight: 760),
                padding: const EdgeInsets.all(24),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Header
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(colors: [Color(0xFF6D28D9), Color(0xFF1E40AF)]),
                            borderRadius: BorderRadius.circular(14),
                          ),
                          child: const Icon(Icons.campaign_rounded, color: Colors.white, size: 26),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                isRtl ? 'نظام الرسائل والتنبيهات الجماعية الموجهة' : 'Targeted Bulk Broadcast Center',
                                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
                              ),
                              const SizedBox(height: 3),
                              Text(
                                isRtl
                                    ? 'إرسال إشعار فوري أعلى الشاشة وداخل مركز التنبيهات لفئة محددة من المشتركين'
                                    : 'Send instant top-screen and in-app notifications to specific subscriber segments',
                                style: const TextStyle(fontSize: 12.5, color: Color(0xFF64748B)),
                              ),
                            ],
                          ),
                        ),
                        IconButton(
                          onPressed: isSending ? null : () => Navigator.pop(ctx),
                          icon: const Icon(Icons.close_rounded),
                        ),
                      ],
                    ),
                    const Divider(height: 24),

                    Flexible(
                      child: SingleChildScrollView(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // 1. Segment Picker
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  isRtl ? '1. اختر الفئة المستهدفة:' : '1. Select Target Segment:',
                                  style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5, color: Color(0xFF0F172A)),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFF3E8FF),
                                    borderRadius: BorderRadius.circular(20),
                                    border: Border.all(color: const Color(0xFFD8B4FE)),
                                  ),
                                  child: Text(
                                    isRtl ? 'عدد المستلمين: ${targets.length} حساب' : 'Recipients: ${targets.length}',
                                    style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w900, color: Color(0xFF6B21A8)),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 10),
                            Wrap(
                              spacing: 8,
                              runSpacing: 8,
                              children: segments.map((seg) {
                                final id = seg['id'] as String;
                                final isSelected = selectedSegment == id;
                                final color = seg['color'] as Color;
                                return InkWell(
                                  borderRadius: BorderRadius.circular(12),
                                  onTap: () {
                                    setDialogState(() {
                                      selectedSegment = id;
                                      if (id == 'suspended') {
                                        selectedType = 'admin_broadcast_warning';
                                        titleCtrl.text = isRtl ? '⚠️ تنبيه إداري بخصوص حالة حسابك' : '⚠️ Account Status Notice';
                                      } else if (id == 'approved_merchants' || id == 'all_merchants') {
                                        selectedType = 'admin_broadcast_info';
                                        titleCtrl.text = isRtl ? '🏪 تعميم هام لتجار منصة مقاييس' : '🏪 Important Merchant Notice';
                                      } else if (id == 'buyers_active') {
                                        selectedType = 'admin_broadcast_promo';
                                        titleCtrl.text = isRtl ? '✨ جديد منصة مقاييس لعملائنا الكرام' : '✨ New on Magaiees Marketplace';
                                      }
                                    });
                                  },
                                  child: AnimatedContainer(
                                    duration: const Duration(milliseconds: 160),
                                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 9),
                                    decoration: BoxDecoration(
                                      color: isSelected ? color : color.withValues(alpha: 0.06),
                                      borderRadius: BorderRadius.circular(12),
                                      border: Border.all(
                                        color: isSelected ? color : color.withValues(alpha: 0.3),
                                        width: isSelected ? 1.6 : 1.0,
                                      ),
                                    ),
                                    child: Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Icon(seg['icon'] as IconData, size: 16, color: isSelected ? Colors.white : color),
                                        const SizedBox(width: 6),
                                        Text(
                                          seg['label'] as String,
                                          style: TextStyle(
                                            fontSize: 12.5,
                                            fontWeight: FontWeight.w800,
                                            color: isSelected ? Colors.white : const Color(0xFF1E293B),
                                          ),
                                        ),
                                        const SizedBox(width: 6),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
                                          decoration: BoxDecoration(
                                            color: isSelected ? Colors.white.withValues(alpha: 0.22) : color.withValues(alpha: 0.14),
                                            borderRadius: BorderRadius.circular(10),
                                          ),
                                          child: Text(
                                            '${seg['count']}',
                                            style: TextStyle(
                                              fontSize: 11,
                                              fontWeight: FontWeight.w900,
                                              color: isSelected ? Colors.white : color,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                );
                              }).toList(),
                            ),
                            const SizedBox(height: 18),

                            // 2. Notification Tone / Style
                            Text(
                              isRtl ? '2. مظهر وأولوية الإشعار:' : '2. Notification Style:',
                              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5, color: Color(0xFF0F172A)),
                            ),
                            const SizedBox(height: 8),
                            Wrap(
                              spacing: 8,
                              runSpacing: 8,
                              children: [
                                ChoiceChip(
                                  selected: selectedType == 'admin_broadcast_info',
                                  label: Text(isRtl ? '🔔 تعميم رسمي (أزرق)' : '🔔 Official Info'),
                                  onSelected: (_) => setDialogState(() => selectedType = 'admin_broadcast_info'),
                                ),
                                ChoiceChip(
                                  selected: selectedType == 'admin_broadcast_promo',
                                  label: Text(isRtl ? '✨ عروض وتحديثات (بنفسجي)' : '✨ Promo / Update'),
                                  onSelected: (_) => setDialogState(() => selectedType = 'admin_broadcast_promo'),
                                ),
                                ChoiceChip(
                                  selected: selectedType == 'admin_broadcast_warning',
                                  label: Text(isRtl ? '⚠️ تنبيه هام / موقوفين (برتقالي)' : '⚠️ Urgent / Warning'),
                                  onSelected: (_) => setDialogState(() => selectedType = 'admin_broadcast_warning'),
                                ),
                              ],
                            ),
                            const SizedBox(height: 14),

                            // Quick Ready Templates
                            Wrap(
                              spacing: 8,
                              runSpacing: 6,
                              crossAxisAlignment: WrapCrossAlignment.center,
                              children: [
                                Text(
                                  isRtl ? 'قوالب سريعة:' : 'Quick Templates:',
                                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Color(0xFF64748B)),
                                ),
                                ActionChip(
                                  label: Text(isRtl ? 'تحديث أسعار ومخزون التجار' : 'Merchant Inventory Update', style: const TextStyle(fontSize: 11.5)),
                                  onPressed: () => setDialogState(() {
                                    titleCtrl.text = '🏪 تعميم لتجار منصة مقاييس المعتمدين';
                                    bodyCtrl.text = 'نرجو من السادة التجار مراجعة وتحديث توفر المخزون والأسعار لضمان سرعة اعتماد طلبات العملاء.';
                                  }),
                                ),
                                ActionChip(
                                  label: Text(isRtl ? 'رسالة للحسابات الموقوفة' : 'Suspended Account Notice', style: const TextStyle(fontSize: 11.5)),
                                  onPressed: () => setDialogState(() {
                                    selectedSegment = 'suspended';
                                    selectedType = 'admin_broadcast_warning';
                                    titleCtrl.text = '⚠️ تنبيه إداري بخصوص تفعيل حسابك';
                                    bodyCtrl.text = 'يرجى التواصل مع إدارة منصة مقاييس لاستكمال متطلبات مراجعة الحساب وإعادة تفعيله.';
                                  }),
                                ),
                                ActionChip(
                                  label: Text(isRtl ? 'تنبيه عروض للمشترين' : 'Buyers Offer Notice', style: const TextStyle(fontSize: 11.5)),
                                  onPressed: () => setDialogState(() {
                                    selectedSegment = 'buyers_active';
                                    selectedType = 'admin_broadcast_promo';
                                    titleCtrl.text = '✨ عروض وتوريدات جديدة على مقاييس';
                                    bodyCtrl.text = 'تصفح أحدث عروض مواد البناء والحديد والأسمنت المعتمدة بأفضل الأسعار المباشرة عبر منصة مقاييس.';
                                  }),
                                ),
                              ],
                            ),
                            const SizedBox(height: 14),

                            // 3. Title & Body Inputs
                            TextField(
                              controller: titleCtrl,
                              onChanged: (_) => setDialogState(() {}),
                              decoration: InputDecoration(
                                labelText: isRtl ? 'عنوان الرسالة الجماعية' : 'Broadcast Title',
                                prefixIcon: const Icon(Icons.title_rounded),
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                            ),
                            const SizedBox(height: 12),
                            TextField(
                              controller: bodyCtrl,
                              maxLines: 3,
                              onChanged: (_) => setDialogState(() {}),
                              decoration: InputDecoration(
                                labelText: isRtl ? 'نص الرسالة والتفاصيل' : 'Broadcast Message Body',
                                hintText: isRtl ? 'اكتب نص الرسالة التي ستظهر للمشتركين المستهدفين...' : 'Write your message here...',
                                prefixIcon: const Icon(Icons.chat_bubble_outline_rounded),
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                            ),
                            const SizedBox(height: 16),

                            // Live Top-Screen Preview Card
                            Text(
                              isRtl ? 'معاينة شكل الظهور أعلى شاشة التطبيق:' : 'Top-of-Screen Banner Preview:',
                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Color(0xFF64748B)),
                            ),
                            const SizedBox(height: 6),
                            Container(
                              width: double.infinity,
                              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                              decoration: BoxDecoration(
                                gradient: LinearGradient(colors: previewColors),
                                borderRadius: BorderRadius.circular(14),
                                boxShadow: [
                                  BoxShadow(
                                    color: previewColors.last.withValues(alpha: 0.25),
                                    blurRadius: 10,
                                    offset: const Offset(0, 4),
                                  ),
                                ],
                              ),
                              child: Row(
                                children: [
                                  Icon(
                                    isWarn ? Icons.warning_amber_rounded : (isPromo ? Icons.auto_awesome_rounded : Icons.campaign_rounded),
                                    color: Colors.white,
                                    size: 24,
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          titleCtrl.text.trim().isEmpty
                                              ? (isRtl ? 'عنوان الرسالة' : 'Message Title')
                                              : titleCtrl.text.trim(),
                                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 13.5),
                                        ),
                                        const SizedBox(height: 3),
                                        Text(
                                          bodyCtrl.text.trim().isEmpty
                                              ? (isRtl ? 'سيظهر نص رسالتك الجماعية هنا فور الإرسال...' : 'Your broadcast message will appear here...')
                                              : bodyCtrl.text.trim(),
                                          style: TextStyle(color: Colors.white.withValues(alpha: 0.92), fontSize: 12, height: 1.35),
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
                    ),
                    const SizedBox(height: 18),

                    // Footer Actions
                    Row(
                      mainAxisAlignment: MainAxisAlignment.end,
                      children: [
                        TextButton(
                          onPressed: isSending ? null : () => Navigator.pop(ctx),
                          child: Text(isRtl ? 'إلغاء' : 'Cancel'),
                        ),
                        const SizedBox(width: 10),
                        FilledButton.icon(
                          style: FilledButton.styleFrom(
                            backgroundColor: const Color(0xFF6D28D9),
                            padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 13),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                          icon: isSending
                              ? const SizedBox(
                                  width: 16,
                                  height: 16,
                                  child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                                )
                              : const Icon(Icons.send_rounded, size: 18),
                          label: Text(
                            isRtl
                                ? 'إرسال الآن (${targets.length} مشترك)'
                                : 'Send Now (${targets.length})',
                            style: const TextStyle(fontWeight: FontWeight.w800),
                          ),
                          onPressed: (isSending || targets.isEmpty)
                              ? null
                              : () async {
                                  if (titleCtrl.text.trim().isEmpty || bodyCtrl.text.trim().isEmpty) {
                                    ScaffoldMessenger.of(this.context).showSnackBar(
                                      SnackBar(
                                        content: Text(isRtl ? 'يرجى كتابة عنوان ونص الرسالة الجماعية أولاً' : 'Please enter title and message body'),
                                        backgroundColor: Colors.red,
                                        behavior: SnackBarBehavior.floating,
                                      ),
                                    );
                                    return;
                                  }
                                  setDialogState(() => isSending = true);
                                  try {
                                    final sentCount = await ref.read(subscribersProvider.notifier).sendBulkBroadcast(
                                          targetUserIds: targets.map((s) => s.id).toList(),
                                          title: titleCtrl.text.trim(),
                                          body: bodyCtrl.text.trim(),
                                          type: selectedType,
                                        );
                                    if (ctx.mounted) Navigator.pop(ctx);
                                    if (mounted) {
                                      ScaffoldMessenger.of(this.context).showSnackBar(
                                        SnackBar(
                                          content: Text(
                                            isRtl
                                                ? '✅ تم إرسال الرسالة الجماعية بنجاح إلى $sentCount مشترك!'
                                                : '✅ Broadcast sent successfully to $sentCount subscribers!',
                                          ),
                                          backgroundColor: const Color(0xFF059669),
                                          behavior: SnackBarBehavior.floating,
                                        ),
                                      );
                                    }
                                  } catch (e) {
                                    setDialogState(() => isSending = false);
                                    if (mounted) {
                                      ScaffoldMessenger.of(this.context).showSnackBar(
                                        SnackBar(
                                          content: Text(isRtl ? 'حدث خطأ أثناء الإرسال: $e' : 'Failed to send broadcast: $e'),
                                          backgroundColor: Colors.red,
                                          behavior: SnackBarBehavior.floating,
                                        ),
                                      );
                                    }
                                  }
                                },
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}


