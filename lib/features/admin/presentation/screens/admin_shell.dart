import 'package:magaiees_marketplace/features/admin/services/presentation/screens/admin_services_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:magaiees_marketplace/core/theme/app_theme.dart';
import 'package:magaiees_marketplace/features/admin/presentation/screens/admin_dashboard_screen.dart';
import 'package:magaiees_marketplace/features/admin/presentation/screens/admin_ads_screen.dart';
import 'package:magaiees_marketplace/features/admin/presentation/screens/admin_orders_screen.dart';
import 'package:magaiees_marketplace/features/admin/presentation/screens/admin_categories_screen.dart';
import 'package:magaiees_marketplace/features/admin/presentation/screens/admin_products_screen.dart';
import 'package:magaiees_marketplace/features/admin/presentation/screens/admin_settings_screen.dart';
import 'package:magaiees_marketplace/features/admin/finance/presentation/screens/admin_finance_screen.dart';
import 'package:magaiees_marketplace/features/admin/subscribers/presentation/screens/subscribers_management_screen.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/admin/digital_broker/presentation/screens/quote_requests_management_screen.dart';
import 'package:magaiees_marketplace/features/admin/suppliers/presentation/screens/admin_verified_suppliers_screen.dart';
import 'package:magaiees_marketplace/routing/route_names.dart';

final adminTabIndexProvider = StateProvider<int>((ref) => 0);

class AdminShell extends ConsumerStatefulWidget {
  final Widget? child;
  final int initialIndex;
  const AdminShell({super.key, this.child, this.initialIndex = 0});

  @override
  ConsumerState<AdminShell> createState() => _AdminShellState();
}

class _AdminShellState extends ConsumerState<AdminShell> {
  late int _selectedIndex;

  // ── Index ↔ Route mapping ──────────────────────────────────────────
  static const List<String> _indexToRoute = [
    RouteNames.adminDashboard,    // 0
    RouteNames.adminSubscribers,  // 1
    RouteNames.adminAds,          // 2
    RouteNames.adminOrders,       // 3
    RouteNames.adminCategories,   // 4
    RouteNames.adminProducts,     // 5
    RouteNames.adminServices,     // 6
    RouteNames.adminFinance,      // 7
    RouteNames.adminSettings,     // 8
    RouteNames.adminDigitalBroker,// 9
    RouteNames.adminSuppliers,    // 10
  ];

  /// Labels for breadcrumb (Arabic / English pairs)
  static const List<List<String>> _indexToLabel = [
    ['الرئيسية', 'Dashboard'],
    ['إدارة المشتركين', 'Subscribers'],
    ['إدارة الإعلانات', 'Ads'],
    ['إدارة الطلبات', 'Orders'],
    ['إدارة الأقسام', 'Categories'],
    ['إدارة المنتجات', 'Products'],
    ['مقدمو الخدمات', 'Service Providers'],
    ['المالية والحسابات', 'Finance & Accounting'],
    ['الإعدادات', 'Settings'],
    ['الوسيط الرقمي', 'Digital Broker'],
    ['المصانع والموردون (موثق)', 'Verified Suppliers'],
  ];

  static const List<IconData> _indexToIcon = [
    Icons.dashboard_outlined,
    Icons.people_outline,
    Icons.campaign_outlined,
    Icons.shopping_cart_outlined,
    Icons.category_outlined,
    Icons.inventory_2_outlined,
    Icons.engineering_outlined,
    Icons.account_balance_wallet_outlined,
    Icons.settings_outlined,
    Icons.handshake_outlined,
    Icons.verified_outlined,
  ];


  @override
  void initState() {
    super.initState();
    _selectedIndex = widget.initialIndex.clamp(0, _pages.length - 1);
    _visitedTabs.add(_selectedIndex);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(adminTabIndexProvider.notifier).state = _selectedIndex;
    });
  }

  final List<int> _backStack = [];
  final List<int> _forwardStack = [];
  final Set<int> _visitedTabs = {};

  @override
  void didUpdateWidget(covariant AdminShell oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.initialIndex != oldWidget.initialIndex) {
      final target = widget.initialIndex.clamp(0, _pages.length - 1);
      if (target != _selectedIndex) {
        if (_backStack.isEmpty || _backStack.last != _selectedIndex) {
          _backStack.add(_selectedIndex);
        }
        setState(() {
          _selectedIndex = target;
          _visitedTabs.add(target);
        });
        ref.read(adminTabIndexProvider.notifier).state = _selectedIndex;
      }
    }
  }

  bool _isSidebarOpen = false;
  bool _isSidebarPinned = false;
  int? _hoveredNavIndex;

  bool get _isSidebarExpanded => _isSidebarOpen || _isSidebarPinned;

  void _toggleSidebar() {
    setState(() {
      if (_isSidebarExpanded) {
        _isSidebarOpen = false;
        _isSidebarPinned = false;
      } else {
        _isSidebarOpen = true;
      }
    });
  }

  Future<void> _confirmAndLogout(BuildContext context, bool isRtl) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(isRtl ? 'تأكيد تسجيل الخروج' : 'Confirm Logout'),
        content: Text(
          isRtl
              ? 'هل تريد تسجيل الخروج من لوحة التحكم؟'
              : 'Are you sure you want to log out?',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: Text(isRtl ? 'إلغاء' : 'Cancel'),
          ),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: Colors.redAccent),
            onPressed: () => Navigator.pop(ctx, true),
            child: Text(isRtl ? 'تسجيل الخروج' : 'Logout'),
          ),
        ],
      ),
    );
    if (confirmed == true) {
      await ref.read(authNotifierProvider.notifier).logout();
      if (context.mounted) {
        context.go(RouteNames.login);
      }
    }
  }

  Widget _buildLazyPagesStack() {
    return IndexedStack(
      index: _selectedIndex,
      children: List.generate(_pages.length, (i) {
        return _visitedTabs.contains(i) ? _pages[i] : const SizedBox.shrink();
      }),
    );
  }

  Widget _buildCrispHamburgerIcon({required Color color, bool isOpen = false}) {
    return SizedBox(
      width: 20,
      height: 15,
      child: Column(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          AnimatedContainer(
            duration: const Duration(milliseconds: 220),
            curve: Curves.easeOutCubic,
            width: 20,
            height: 2.3,
            decoration: BoxDecoration(
              color: color,
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          AnimatedContainer(
            duration: const Duration(milliseconds: 220),
            curve: Curves.easeOutCubic,
            width: isOpen ? 13 : 20,
            height: 2.3,
            decoration: BoxDecoration(
              color: color,
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          AnimatedContainer(
            duration: const Duration(milliseconds: 220),
            curve: Curves.easeOutCubic,
            width: isOpen ? 16 : 20,
            height: 2.3,
            decoration: BoxDecoration(
              color: color,
              borderRadius: BorderRadius.circular(2),
            ),
          ),
        ],
      ),
    );
  }

  static const double _collapsedWidth = 72;
  static const double _expandedWidth = 296;

  final List<Widget> _pages = const [
    AdminDashboardScreen(),
    SubscribersManagementScreen(),
    AdminAdsScreen(),
    AdminOrdersScreen(),
    AdminCategoriesScreen(),
    AdminProductsScreen(),
    AdminServicesScreen(),
    AdminFinanceScreen(),
    AdminSettingsScreen(),
    QuoteRequestsManagementScreen(),
    AdminVerifiedSuppliersScreen(),
  ];

  /// Navigate to the tab at [index] using GoRouter deep linking and history tracking.
  void _navigateToTab(int index, {bool fromHistory = false}) {
    if (index == _selectedIndex) {
      if (_isSidebarOpen && !_isSidebarPinned) {
        setState(() => _isSidebarOpen = false);
      }
      return;
    }

    if (!fromHistory) {
      if (_backStack.isEmpty || _backStack.last != _selectedIndex) {
        _backStack.add(_selectedIndex);
      }
      _forwardStack.clear();
    }

    setState(() {
      _selectedIndex = index;
      _visitedTabs.add(index);
      if (!_isSidebarPinned) {
        _isSidebarOpen = false;
      }
    });
    ref.read(adminTabIndexProvider.notifier).state = index;

    final targetRoute = _indexToRoute[index];
    context.go(targetRoute);
  }

  /// Go back to the preceding screen that the user was on.
  void _goBack() {
    if (_backStack.isNotEmpty) {
      final prevIndex = _backStack.removeLast();
      if (_forwardStack.isEmpty || _forwardStack.last != _selectedIndex) {
        _forwardStack.add(_selectedIndex);
      }
      _navigateToTab(prevIndex, fromHistory: true);
    } else if (_selectedIndex != 0) {
      if (_forwardStack.isEmpty || _forwardStack.last != _selectedIndex) {
        _forwardStack.add(_selectedIndex);
      }
      _navigateToTab(0, fromHistory: true);
    } else if (context.canPop()) {
      context.pop();
    } else {
      context.go('/buyer/home');
    }
  }

  /// Go forward to the next screen if user had navigated back.
  void _goForward() {
    if (_forwardStack.isNotEmpty) {
      final nextIndex = _forwardStack.removeLast();
      if (_backStack.isEmpty || _backStack.last != _selectedIndex) {
        _backStack.add(_selectedIndex);
      }
      _navigateToTab(nextIndex, fromHistory: true);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    ref.listen<int>(adminTabIndexProvider, (prev, next) {
      if (next != _selectedIndex) {
        if (_backStack.isEmpty || _backStack.last != _selectedIndex) {
          _backStack.add(_selectedIndex);
        }
        setState(() {
          _selectedIndex = next;
          _visitedTabs.add(next);
        });
      }
    });

    final authUser = ref.watch(authNotifierProvider).valueOrNull;
    final userName = (isRtl && authUser?.nameAr != null && authUser!.nameAr!.isNotEmpty ? authUser.nameAr : authUser?.name) ?? (isRtl ? 'مدير النظام' : 'Admin');
    final userEmail = authUser?.email ?? 'admin@magaiees.com';
    final screenWidth = MediaQuery.of(context).size.width;
    final isMobile = screenWidth < 768;
    final safeIndex = _selectedIndex.clamp(0, _indexToLabel.length - 1);
    final currentLabel = isRtl ? _indexToLabel[safeIndex][0] : _indexToLabel[safeIndex][1];
    final canGoBack = _backStack.isNotEmpty || _selectedIndex != 0;

    return Theme(
      data: AppTheme.lightTheme,
      child: PopScope(
        canPop: false,
        onPopInvokedWithResult: (didPop, result) {
          if (didPop) return;
          _goBack();
        },
        child: isMobile
            ? Scaffold(
                backgroundColor: const Color(0xFFF8FAFC),
                appBar: AppBar(
                  backgroundColor: const Color(0xFF0F172A),
                  foregroundColor: Colors.white,
                  elevation: 2,
                  leadingWidth: 96,
                  titleSpacing: 4,
                  leading: Builder(
                    builder: (drawerCtx) => Padding(
                      padding: const EdgeInsetsDirectional.only(start: 8, top: 8, bottom: 8),
                      child: InkWell(
                        borderRadius: BorderRadius.circular(10),
                        onTap: () => Scaffold.of(drawerCtx).openDrawer(),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10),
                          decoration: BoxDecoration(
                            color: const Color(0xFF1E3A8A),
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: const Color(0xFF60A5FA).withValues(alpha: 0.6)),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              _buildCrispHamburgerIcon(color: Colors.white),
                              const SizedBox(width: 6),
                              Text(
                                isRtl ? 'القائمة' : 'Menu',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 11.5,
                                  fontWeight: FontWeight.w800,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),
                  title: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(6),
                        decoration: BoxDecoration(
                          color: Colors.blue.withValues(alpha: 0.22),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Icon(_indexToIcon[safeIndex], size: 18, color: Colors.blue.shade300),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          currentLabel,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 15.5,
                            fontWeight: FontWeight.w800,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  actions: [
                    if (canGoBack)
                      IconButton(
                        icon: Icon(
                          isRtl ? Icons.arrow_forward_rounded : Icons.arrow_back_rounded,
                          size: 20,
                          color: Colors.white,
                        ),
                        tooltip: isRtl ? 'رجوع' : 'Back',
                        onPressed: _goBack,
                      ),
                    IconButton(
                      icon: const Icon(Icons.storefront_rounded, size: 20, color: Color(0xFF93C5FD)),
                      tooltip: isRtl ? 'عرض التطبيق' : 'Marketplace App',
                      onPressed: () => context.go('/buyer/home'),
                    ),
                  ],
                ),
                drawer: _buildMobileAdminDrawer(context, isRtl, userName, userEmail),
                body: SafeArea(
                  top: false,
                  child: widget.child ?? _buildLazyPagesStack(),
                ),
                bottomNavigationBar: _buildMobileBottomBar(context, isRtl),
              )
            : Scaffold(
                backgroundColor: const Color(0xFFF8FAFC),
                body: Stack(
                  children: [
                    // ── Main Content Area (Fixed rail offset = zero relayout jank) ──
                    AnimatedPadding(
                      duration: const Duration(milliseconds: 240),
                      curve: Curves.easeOutCubic,
                      padding: EdgeInsetsDirectional.only(
                        start: _isSidebarPinned ? _expandedWidth : _collapsedWidth,
                      ),
                      child: Column(
                        children: [
                          _buildBreadcrumbBar(isRtl),
                          Expanded(
                            child: widget.child ?? _buildLazyPagesStack(),
                          ),
                        ],
                      ),
                    ),

                    // ── Soft Dismissible Scrim When Overlay Sidebar Is Open ──
                    Positioned.fill(
                      child: IgnorePointer(
                        ignoring: !(_isSidebarOpen && !_isSidebarPinned),
                        child: GestureDetector(
                          behavior: HitTestBehavior.opaque,
                          onTap: () => setState(() => _isSidebarOpen = false),
                          child: AnimatedOpacity(
                            duration: const Duration(milliseconds: 220),
                            curve: Curves.easeOutCubic,
                            opacity: (_isSidebarOpen && !_isSidebarPinned) ? 1.0 : 0.0,
                            child: const ColoredBox(color: Color(0x2E0F172A)),
                          ),
                        ),
                      ),
                    ),

                    // ── Smooth 60fps Sliding Sidebar Rail / Drawer ──
                    PositionedDirectional(
                      top: 0,
                      bottom: 0,
                      start: 0,
                      child: RepaintBoundary(
                        child: AnimatedContainer(
                          duration: const Duration(milliseconds: 240),
                          curve: Curves.easeOutCubic,
                          width: _isSidebarExpanded ? _expandedWidth : _collapsedWidth,
                          clipBehavior: Clip.hardEdge,
                          decoration: BoxDecoration(
                            color: const Color(0xFF0F172A),
                            border: BorderDirectional(
                              end: BorderSide(
                                color: Colors.white.withValues(alpha: 0.08),
                                width: 1,
                              ),
                            ),
                            boxShadow: _isSidebarExpanded && !_isSidebarPinned
                                ? [
                                    BoxShadow(
                                      color: Colors.black.withValues(alpha: 0.32),
                                      blurRadius: 28,
                                      offset: Offset(isRtl ? -8 : 8, 0),
                                    ),
                                  ]
                                : const [],
                          ),
                          child: SafeArea(
                            right: false,
                            left: false,
                            child: Column(
                              children: [
                                // Top Header / Crisp Toggle Button
                                Material(
                                  color: Colors.transparent,
                                  child: SizedBox(
                                    height: 64,
                                    child: SingleChildScrollView(
                                      scrollDirection: Axis.horizontal,
                                      physics: const NeverScrollableScrollPhysics(),
                                      child: SizedBox(
                                        width: _expandedWidth,
                                        child: Padding(
                                          padding: const EdgeInsets.symmetric(horizontal: 8),
                                          child: Row(
                                            children: [
                                              SizedBox(
                                                width: 56,
                                                child: Center(
                                                  child: Tooltip(
                                                    textStyle: const TextStyle(
                                                      color: Colors.white,
                                                      fontSize: 15.5,
                                                      fontWeight: FontWeight.w800,
                                                    ),
                                                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 9),
                                                    decoration: BoxDecoration(
                                                      color: const Color(0xFF0F172A),
                                                      borderRadius: BorderRadius.circular(10),
                                                      border: Border.all(color: const Color(0xFF3B82F6), width: 1.2),
                                                    ),
                                                    message: _isSidebarExpanded
                                                        ? (isRtl ? 'إغلاق القائمة الجانبية' : 'Close Sidebar')
                                                        : (isRtl ? 'فتح القائمة الجانبية' : 'Open Sidebar'),
                                                    child: InkWell(
                                                      borderRadius: BorderRadius.circular(11),
                                                      onTap: _toggleSidebar,
                                                      child: AnimatedContainer(
                                                        duration: const Duration(milliseconds: 200),
                                                        width: 42,
                                                        height: 40,
                                                        decoration: BoxDecoration(
                                                          color: _isSidebarExpanded
                                                              ? const Color(0xFF2563EB)
                                                              : Colors.white.withValues(alpha: 0.10),
                                                          borderRadius: BorderRadius.circular(11),
                                                          border: Border.all(
                                                            color: _isSidebarExpanded
                                                                ? const Color(0xFF60A5FA)
                                                                : Colors.white.withValues(alpha: 0.24),
                                                          ),
                                                        ),
                                                        child: Center(
                                                          child: _buildCrispHamburgerIcon(
                                                            color: Colors.white,
                                                            isOpen: _isSidebarExpanded,
                                                          ),
                                                        ),
                                                      ),
                                                    ),
                                                  ),
                                                ),
                                              ),
                                              Expanded(
                                                child: AnimatedOpacity(
                                                  duration: const Duration(milliseconds: 180),
                                                  opacity: _isSidebarExpanded ? 1.0 : 0.0,
                                                  child: Padding(
                                                    padding: const EdgeInsetsDirectional.only(start: 6, end: 4),
                                                    child: Row(
                                                      children: [
                                                        Expanded(
                                                          child: GestureDetector(
                                                            onTap: _toggleSidebar,
                                                            child: Text(
                                                              isRtl ? 'لوحة تحكم مقاييس' : 'Magaiees Admin',
                                                              style: const TextStyle(
                                                                color: Colors.white,
                                                                fontSize: 17,
                                                                fontWeight: FontWeight.w900,
                                                              ),
                                                              maxLines: 1,
                                                              overflow: TextOverflow.ellipsis,
                                                            ),
                                                          ),
                                                        ),
                                                        Tooltip(
                                                          message: _isSidebarPinned
                                                              ? (isRtl ? 'إلغاء تثبيت القائمة' : 'Unpin Sidebar')
                                                              : (isRtl ? 'تثبيت القائمة مفتوحة' : 'Pin Sidebar Open'),
                                                          child: IconButton(
                                                            visualDensity: VisualDensity.compact,
                                                            iconSize: 18,
                                                            onPressed: () => setState(() {
                                                              _isSidebarPinned = !_isSidebarPinned;
                                                              _isSidebarOpen = _isSidebarPinned;
                                                            }),
                                                            icon: Icon(
                                                              _isSidebarPinned
                                                                  ? Icons.push_pin_rounded
                                                                  : Icons.push_pin_outlined,
                                                              color: _isSidebarPinned
                                                                  ? const Color(0xFF60A5FA)
                                                                  : Colors.white54,
                                                            ),
                                                          ),
                                                        ),
                                                      ],
                                                    ),
                                                  ),
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ),
                                    ),
                                  ),
                                ),
                                Divider(color: Colors.white.withValues(alpha: 0.12), height: 1),
                                const SizedBox(height: 10),

                                // Navigation Items
                                Expanded(
                                  child: ListView(
                                    padding: const EdgeInsets.symmetric(horizontal: 8),
                                    children: [
                                      _buildNavItem(0, _indexToIcon[0], isRtl ? 'الرئيسية' : 'Dashboard'),
                                      _buildNavItem(1, _indexToIcon[1], isRtl ? 'إدارة المشتركين' : 'Subscribers'),
                                      _buildNavItem(10, _indexToIcon[10], isRtl ? 'المصانع والموردون (موثق)' : 'Verified Suppliers'),
                                      _buildNavItem(2, _indexToIcon[2], isRtl ? 'إدارة الإعلانات' : 'Ads'),
                                      _buildNavItem(3, _indexToIcon[3], isRtl ? 'إدارة الطلبات' : 'Orders'),
                                      _buildNavItem(4, _indexToIcon[4], isRtl ? 'إدارة الأقسام' : 'Categories'),
                                      _buildNavItem(5, _indexToIcon[5], isRtl ? 'إدارة المنتجات' : 'Products'),
                                      _buildNavItem(6, _indexToIcon[6], isRtl ? 'مقدمو الخدمات' : 'Service Providers'),
                                      _buildNavItem(7, _indexToIcon[7], isRtl ? 'المالية والحسابات' : 'Finance & Accounting'),
                                      _buildNavItem(9, _indexToIcon[9], isRtl ? 'الوسيط الرقمي (التسعير)' : 'Digital Broker'),
                                      Padding(
                                        padding: const EdgeInsets.symmetric(vertical: 8.0),
                                        child: Divider(color: Colors.white.withValues(alpha: 0.12), height: 1),
                                      ),
                                      _buildNavItem(8, _indexToIcon[8], isRtl ? 'الإعدادات' : 'Settings'),
                                    ],
                                  ),
                                ),

                                // Bottom Area (Admin Info & Logout — zero overflow in both rail & expanded states)
                                Divider(color: Colors.white.withValues(alpha: 0.12), height: 1),
                                SizedBox(
                                  height: 66,
                                  child: SingleChildScrollView(
                                    scrollDirection: Axis.horizontal,
                                    physics: const NeverScrollableScrollPhysics(),
                                    child: SizedBox(
                                      width: _expandedWidth,
                                      child: Padding(
                                        padding: const EdgeInsets.symmetric(horizontal: 8),
                                        child: Row(
                                          children: [
                                            SizedBox(
                                              width: 56,
                                              child: Center(
                                                child: Tooltip(
                                                  message: _isSidebarExpanded
                                                      ? userName
                                                      : (isRtl ? '$userName • اضغط لتسجيل الخروج' : '$userName • Tap to Logout'),
                                                  child: InkWell(
                                                    borderRadius: BorderRadius.circular(20),
                                                    onTap: _isSidebarExpanded
                                                        ? null
                                                        : () => _confirmAndLogout(context, isRtl),
                                                    child: CircleAvatar(
                                                      radius: 18,
                                                      backgroundColor: Colors.white.withValues(alpha: 0.14),
                                                      child: Icon(
                                                        _isSidebarExpanded
                                                            ? Icons.person_rounded
                                                            : Icons.logout_rounded,
                                                        color: _isSidebarExpanded
                                                            ? Colors.white
                                                            : Colors.redAccent.shade100,
                                                        size: 19,
                                                      ),
                                                    ),
                                                  ),
                                                ),
                                              ),
                                            ),
                                            Expanded(
                                              child: AnimatedOpacity(
                                                duration: const Duration(milliseconds: 180),
                                                opacity: _isSidebarExpanded ? 1.0 : 0.0,
                                                child: Row(
                                                  children: [
                                                    Expanded(
                                                      child: Padding(
                                                        padding: const EdgeInsets.symmetric(horizontal: 8.0),
                                                        child: Column(
                                                          crossAxisAlignment: CrossAxisAlignment.start,
                                                          mainAxisAlignment: MainAxisAlignment.center,
                                                          children: [
                                                            Text(
                                                              userName,
                                                              style: const TextStyle(
                                                                color: Colors.white,
                                                                fontWeight: FontWeight.bold,
                                                                fontSize: 13,
                                                              ),
                                                              maxLines: 1,
                                                              overflow: TextOverflow.ellipsis,
                                                            ),
                                                            Text(
                                                              userEmail,
                                                              style: TextStyle(
                                                                color: Colors.grey.shade400,
                                                                fontSize: 11,
                                                              ),
                                                              maxLines: 1,
                                                              overflow: TextOverflow.ellipsis,
                                                            ),
                                                          ],
                                                        ),
                                                      ),
                                                    ),
                                                    IconButton(
                                                      icon: const Icon(Icons.logout_rounded, color: Colors.redAccent, size: 20),
                                                      tooltip: isRtl ? 'تسجيل الخروج' : 'Logout',
                                                      onPressed: () => _confirmAndLogout(context, isRtl),
                                                    ),
                                                  ],
                                                ),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
      ),
    );
  }

  /// Builds a slim breadcrumb bar at the top of the content area showing
  /// current location with Back and Forward navigation buttons
  Widget _buildBreadcrumbBar(bool isRtl) {
    final safeIndex = _selectedIndex.clamp(0, _indexToLabel.length - 1);
    final currentLabel = isRtl ? _indexToLabel[safeIndex][0] : _indexToLabel[safeIndex][1];
    final homeLabel = isRtl ? 'لوحة التحكم' : 'Admin Panel';
    final isDashboard = safeIndex == 0;

    final canGoBack = _backStack.isNotEmpty || _selectedIndex != 0;
    final canGoForward = _forwardStack.isNotEmpty;

    // Previous screen name for tooltip
    String previousScreenName = '';
    if (_backStack.isNotEmpty) {
      final prevIdx = _backStack.last.clamp(0, _indexToLabel.length - 1);
      previousScreenName = isRtl ? _indexToLabel[prevIdx][0] : _indexToLabel[prevIdx][1];
    } else if (_selectedIndex != 0) {
      previousScreenName = isRtl ? 'لوحة التحكم الرئيسية' : 'Dashboard';
    }

    // Next screen name for tooltip
    String nextScreenName = '';
    if (_forwardStack.isNotEmpty) {
      final nextIdx = _forwardStack.last.clamp(0, _indexToLabel.length - 1);
      nextScreenName = isRtl ? _indexToLabel[nextIdx][0] : _indexToLabel[nextIdx][1];
    }

    return Container(
      height: 48,
      padding: const EdgeInsets.symmetric(horizontal: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border(
          bottom: BorderSide(color: Colors.grey.shade200, width: 1.2),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          // ── Explicit Sliding Sidebar Toggle Button ──────────────────
          Tooltip(
            message: _isSidebarExpanded
                ? (isRtl ? 'إغلاق القائمة الجانبية' : 'Close Sidebar')
                : (isRtl ? 'فتح القائمة الجانبية' : 'Open Sidebar'),
            child: InkWell(
              onTap: _toggleSidebar,
              borderRadius: BorderRadius.circular(9),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
                decoration: BoxDecoration(
                  color: _isSidebarExpanded
                      ? const Color(0xFF1E40AF)
                      : const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(9),
                  border: Border.all(
                    color: _isSidebarExpanded
                        ? const Color(0xFF1E40AF)
                        : const Color(0xFFBFDBFE),
                  ),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    _buildCrispHamburgerIcon(
                      color: _isSidebarExpanded ? Colors.white : const Color(0xFF1E40AF),
                      isOpen: _isSidebarExpanded,
                    ),
                    const SizedBox(width: 7),
                    Text(
                      isRtl ? 'القائمة' : 'Menu',
                      style: TextStyle(
                        color: _isSidebarExpanded ? Colors.white : const Color(0xFF1E40AF),
                        fontSize: 12,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          const SizedBox(width: 10),

          // ── Back & Forward History Controls ─────────────────────────
          Container(
            padding: const EdgeInsets.all(3),
            decoration: BoxDecoration(
              color: const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                // زر الرجوع (Back Button)
                Tooltip(
                  message: canGoBack
                      ? (isRtl ? 'الرجوع إلى: $previousScreenName' : 'Back to: $previousScreenName')
                      : (isRtl ? 'لا توجد شاشة سابقة' : 'No previous screen'),
                  child: InkWell(
                    onTap: canGoBack ? _goBack : null,
                    borderRadius: BorderRadius.circular(7),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      decoration: BoxDecoration(
                        color: canGoBack ? const Color(0xFF1E40AF).withValues(alpha: 0.08) : Colors.transparent,
                        borderRadius: BorderRadius.circular(7),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            isRtl ? Icons.arrow_forward_rounded : Icons.arrow_back_rounded,
                            size: 16,
                            color: canGoBack ? const Color(0xFF1E40AF) : Colors.grey.shade400,
                          ),
                          const SizedBox(width: 6),
                          Text(
                            isRtl ? 'رجوع' : 'Back',
                            style: TextStyle(
                              color: canGoBack ? const Color(0xFF1E40AF) : Colors.grey.shade400,
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 2),
                // زر التقدم للأمام (Forward Button)
                Tooltip(
                  message: canGoForward
                      ? (isRtl ? 'التقدم إلى: $nextScreenName' : 'Forward to: $nextScreenName')
                      : (isRtl ? 'لا توجد شاشة تالية' : 'No forward screen'),
                  child: InkWell(
                    onTap: canGoForward ? _goForward : null,
                    borderRadius: BorderRadius.circular(7),
                    child: Container(
                      padding: const EdgeInsets.all(6),
                      child: Icon(
                        isRtl ? Icons.arrow_back_rounded : Icons.arrow_forward_rounded,
                        size: 16,
                        color: canGoForward ? const Color(0xFF1E40AF) : Colors.grey.shade400,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(width: 14),

          // Divider
          Container(
            height: 20,
            width: 1,
            color: const Color(0xFFE2E8F0),
          ),

          const SizedBox(width: 14),

          // Breadcrumb Trail
          Expanded(
            child: Row(
              children: [
                Icon(Icons.dashboard_rounded, size: 16, color: Colors.blue.shade700),
                const SizedBox(width: 6),
                InkWell(
                  onTap: isDashboard ? null : () => _navigateToTab(0),
                  borderRadius: BorderRadius.circular(4),
                  child: Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                    child: Text(
                      homeLabel,
                      style: TextStyle(
                        color: isDashboard ? const Color(0xFF0F172A) : Colors.blue.shade700,
                        fontSize: 13,
                        fontWeight: isDashboard ? FontWeight.w800 : FontWeight.w600,
                      ),
                    ),
                  ),
                ),
                if (!isDashboard) ...[
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 6),
                    child: Icon(
                      isRtl ? Icons.chevron_left_rounded : Icons.chevron_right_rounded,
                      size: 18,
                      color: Colors.grey.shade400,
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: const Color(0xFF1E40AF).withValues(alpha: 0.06),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(_indexToIcon[safeIndex], size: 15, color: const Color(0xFF1E40AF)),
                        const SizedBox(width: 6),
                        Text(
                          currentLabel,
                          style: const TextStyle(
                            color: Color(0xFF1E40AF),
                            fontSize: 12.5,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ],
            ),
          ),

          // Return to App Button
          TextButton.icon(
            onPressed: () => context.go('/buyer/home'),
            icon: const Icon(Icons.storefront_rounded, size: 16, color: Color(0xFF1E40AF)),
            label: Text(
              isRtl ? 'عرض التطبيق' : 'Marketplace App',
              style: const TextStyle(
                color: Color(0xFF1E40AF),
                fontWeight: FontWeight.bold,
                fontSize: 12,
              ),
            ),
            style: TextButton.styleFrom(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              backgroundColor: const Color(0xFFEFF6FF),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
          ),
          if (MediaQuery.of(context).size.width > 1020) ...[
            const SizedBox(width: 10),
            // Current route path indicator
            Text(
              _indexToRoute[safeIndex],
              style: TextStyle(
                color: Colors.grey.shade400,
                fontSize: 11,
                fontFamily: 'monospace',
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildMobileBottomBar(BuildContext context, bool isRtl) {
    final quickTabs = <Map<String, dynamic>>[
      {'idx': 0, 'icon': Icons.dashboard_rounded, 'label': isRtl ? 'الرئيسية' : 'Home'},
      {'idx': 1, 'icon': Icons.people_alt_rounded, 'label': isRtl ? 'المشتركون' : 'Users'},
      {'idx': 3, 'icon': Icons.shopping_cart_rounded, 'label': isRtl ? 'الطلبات' : 'Orders'},
      {'idx': 5, 'icon': Icons.inventory_2_rounded, 'label': isRtl ? 'المنتجات' : 'Products'},
      {'idx': 8, 'icon': Icons.settings_rounded, 'label': isRtl ? 'الإعدادات' : 'Settings'},
    ];

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border(top: BorderSide(color: Colors.grey.shade200)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, -2),
          ),
        ],
      ),
      child: SafeArea(
        top: false,
        child: SizedBox(
          height: 60,
          child: Row(
            children: quickTabs.map((item) {
              final tabIdx = item['idx'] as int;
              final selected = _selectedIndex == tabIdx;
              final color = selected ? const Color(0xFF1E40AF) : const Color(0xFF64748B);
              return Expanded(
                child: InkWell(
                  onTap: () => _navigateToTab(tabIdx),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(item['icon'] as IconData, size: 22, color: color),
                      const SizedBox(height: 3),
                      Text(
                        item['label'] as String,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          fontSize: 10.5,
                          fontWeight: selected ? FontWeight.w900 : FontWeight.w600,
                          color: color,
                        ),
                      ),
                    ],
                  ),
                ),
              );
            }).toList(),
          ),
        ),
      ),
    );
  }

  Widget _buildMobileAdminDrawer(
    BuildContext context,
    bool isRtl,
    String userName,
    String userEmail,
  ) {
    final navItems = <Map<String, dynamic>>[
      {'idx': 0, 'label': isRtl ? 'الرئيسية' : 'Dashboard'},
      {'idx': 1, 'label': isRtl ? 'إدارة المشتركين' : 'Subscribers'},
      {'idx': 10, 'label': isRtl ? 'المصانع والموردون (موثق)' : 'Verified Suppliers'},
      {'idx': 2, 'label': isRtl ? 'إدارة الإعلانات' : 'Ads'},
      {'idx': 3, 'label': isRtl ? 'إدارة الطلبات' : 'Orders'},
      {'idx': 4, 'label': isRtl ? 'إدارة الأقسام' : 'Categories'},
      {'idx': 5, 'label': isRtl ? 'إدارة المنتجات' : 'Products'},
      {'idx': 6, 'label': isRtl ? 'مقدمو الخدمات واللوجستيات' : 'Service Providers'},
      {'idx': 7, 'label': isRtl ? 'المالية والحسابات' : 'Finance & Accounting'},
      {'idx': 9, 'label': isRtl ? 'الوسيط الرقمي (التسعير)' : 'Digital Broker'},
      {'idx': 8, 'label': isRtl ? 'الإعدادات العامة' : 'Settings'},
    ];

    return Drawer(
      backgroundColor: const Color(0xFF0F172A),
      child: SafeArea(
        child: Column(
          children: [
            // Drawer Header
            Container(
              padding: const EdgeInsets.fromLTRB(16, 18, 16, 16),
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [Color(0xFF0F172A), Color(0xFF1E3A8A)],
                  begin: Alignment.topRight,
                  end: Alignment.bottomLeft,
                ),
              ),
              child: Row(
                children: [
                  const CircleAvatar(
                    radius: 22,
                    backgroundColor: Colors.white24,
                    child: Icon(Icons.admin_panel_settings_rounded, color: Colors.white, size: 24),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          isRtl ? 'لوحة تحكم مقاييس' : 'Magaiees Admin',
                          style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w900),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          '$userName • $userEmail',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(color: Colors.blue.shade200, fontSize: 11.5),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const Divider(color: Colors.white12, height: 1),

            // Drawer Navigation List
            Expanded(
              child: ListView.builder(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                itemCount: navItems.length,
                itemBuilder: (ctx, i) {
                  final item = navItems[i];
                  final idx = item['idx'] as int;
                  final selected = _selectedIndex == idx;
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 4),
                    child: ListTile(
                      dense: true,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      tileColor: selected ? Colors.blue.withValues(alpha: 0.22) : Colors.transparent,
                      leading: Icon(
                        _indexToIcon[idx],
                        color: selected ? Colors.blue.shade300 : Colors.grey.shade400,
                        size: 22,
                      ),
                      title: Text(
                        item['label'] as String,
                        style: TextStyle(
                          color: selected ? Colors.white : Colors.grey.shade200,
                          fontWeight: selected ? FontWeight.w800 : FontWeight.w600,
                          fontSize: 15.5,
                        ),
                      ),
                      onTap: () {
                        Navigator.pop(ctx);
                        _navigateToTab(idx);
                      },
                    ),
                  );
                },
              ),
            ),

            const Divider(color: Colors.white12, height: 1),
            Padding(
              padding: const EdgeInsets.all(12),
              child: Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      style: OutlinedButton.styleFrom(
                        foregroundColor: Colors.white,
                        side: const BorderSide(color: Colors.white24),
                        padding: const EdgeInsets.symmetric(vertical: 10),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      onPressed: () {
                        Navigator.pop(context);
                        context.go('/buyer/home');
                      },
                      icon: const Icon(Icons.storefront_rounded, size: 17),
                      label: Text(isRtl ? 'واجهة المتجر' : 'Storefront', style: const TextStyle(fontSize: 12.5)),
                    ),
                  ),
                  const SizedBox(width: 8),
                  IconButton(
                    style: IconButton.styleFrom(
                      backgroundColor: Colors.redAccent.withValues(alpha: 0.16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                    icon: const Icon(Icons.logout_rounded, color: Colors.redAccent, size: 20),
                    tooltip: isRtl ? 'تسجيل الخروج' : 'Logout',
                    onPressed: () async {
                      Navigator.pop(context);
                      await ref.read(authNotifierProvider.notifier).logout();
                      if (context.mounted) {
                        context.go(RouteNames.login);
                      }
                    },
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildNavItem(int index, IconData icon, String label) {
    final isSelected = _selectedIndex == index;
    final isHovered = _hoveredNavIndex == index;

    final itemTile = Padding(
      padding: const EdgeInsets.only(bottom: 5.0),
      child: MouseRegion(
        onEnter: (_) => setState(() => _hoveredNavIndex = index),
        onExit: (_) {
          if (_hoveredNavIndex == index) {
            setState(() => _hoveredNavIndex = null);
          }
        },
        child: Material(
          color: Colors.transparent,
          child: InkWell(
            borderRadius: BorderRadius.circular(11),
            onTap: () => _navigateToTab(index),
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 180),
              height: 50,
              decoration: BoxDecoration(
                color: isSelected
                    ? const Color(0xFF2563EB).withValues(alpha: 0.28)
                    : (isHovered
                        ? Colors.white.withValues(alpha: 0.08)
                        : Colors.transparent),
                borderRadius: BorderRadius.circular(11),
                border: Border.all(
                  color: isSelected
                      ? const Color(0xFF60A5FA).withValues(alpha: 0.6)
                      : (isHovered
                          ? Colors.white.withValues(alpha: 0.18)
                          : Colors.transparent),
                  width: 1,
                ),
              ),
              child: SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                physics: const NeverScrollableScrollPhysics(),
                child: SizedBox(
                  width: _expandedWidth - 18,
                  child: Row(
                    children: [
                      // Fixed Icon Container - always exact same width & position in the 72px rail
                      SizedBox(
                        width: 54,
                        height: 50,
                        child: Center(
                          child: Icon(
                            icon,
                            color: isSelected
                                ? const Color(0xFF93C5FD)
                                : (isHovered ? Colors.white : Colors.grey.shade300),
                            size: isHovered || isSelected ? 24 : 22.5,
                          ),
                        ),
                      ),
                      // Text Label - larger & bolder, scales up smoothly on hover
                      Expanded(
                        child: AnimatedOpacity(
                          duration: const Duration(milliseconds: 180),
                          opacity: _isSidebarExpanded ? 1.0 : 0.0,
                          child: Padding(
                            padding: const EdgeInsetsDirectional.only(start: 6, end: 10),
                            child: AnimatedDefaultTextStyle(
                              duration: const Duration(milliseconds: 160),
                              curve: Curves.easeOutCubic,
                              style: TextStyle(
                                fontFamily: Theme.of(context).textTheme.bodyMedium?.fontFamily,
                                color: (isSelected || isHovered)
                                    ? Colors.white
                                    : Colors.grey.shade200,
                                fontWeight: (isSelected || isHovered)
                                    ? FontWeight.w800
                                    : FontWeight.w600,
                                fontSize: (isSelected || isHovered) ? 16.5 : 15.5,
                              ),
                              child: Text(
                                label,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );

    if (_isSidebarExpanded) return itemTile;
    return Tooltip(
      message: label,
      waitDuration: const Duration(milliseconds: 120),
      preferBelow: false,
      verticalOffset: 14,
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 9),
      textStyle: const TextStyle(
        color: Colors.white,
        fontSize: 16,
        fontWeight: FontWeight.w800,
      ),
      decoration: BoxDecoration(
        color: const Color(0xFF0F172A),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFF3B82F6), width: 1.2),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.3),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: itemTile,
    );
  }
}
