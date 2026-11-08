import 'package:magaiees_marketplace/features/admin/services/presentation/screens/admin_services_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
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
  ];


  @override
  void initState() {
    super.initState();
    _selectedIndex = widget.initialIndex.clamp(0, _pages.length - 1);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(adminTabIndexProvider.notifier).state = _selectedIndex;
    });
  }

  final List<int> _backStack = [];
  final List<int> _forwardStack = [];

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
        });
        ref.read(adminTabIndexProvider.notifier).state = _selectedIndex;
      }
    }
  }

  bool _isSidebarExpanded = false;

  static const double _collapsedWidth = 72;
  static const double _expandedWidth = 260;

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
  ];

  /// Navigate to the tab at [index] using GoRouter deep linking and history tracking.
  void _navigateToTab(int index, {bool fromHistory = false}) {
    if (index == _selectedIndex) return;

    if (!fromHistory) {
      if (_backStack.isEmpty || _backStack.last != _selectedIndex) {
        _backStack.add(_selectedIndex);
      }
      _forwardStack.clear();
    }

    setState(() => _selectedIndex = index);
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
        setState(() => _selectedIndex = next);
      }
    });

    final authUser = ref.watch(authNotifierProvider).valueOrNull;
    final userName = (isRtl && authUser?.nameAr != null && authUser!.nameAr!.isNotEmpty ? authUser.nameAr : authUser?.name) ?? (isRtl ? 'مدير النظام' : 'Admin');
    final userEmail = authUser?.email ?? 'admin@magaiees.com';

    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) {
        if (didPop) return;
        _goBack();
      },
      child: Scaffold(
      body: Row(
        children: [
          // ── Collapsible Hover Sidebar ───────────────────────────────
          MouseRegion(
            onEnter: (_) => setState(() => _isSidebarExpanded = true),
            onExit: (_) => setState(() => _isSidebarExpanded = false),
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              curve: Curves.easeInOut,
              width: _isSidebarExpanded ? _expandedWidth : _collapsedWidth,
              color: const Color(0xFF1E293B),
              clipBehavior: Clip.hardEdge,
              child: Column(
                children: [
                  // Top Header / Logo
                  Container(
                    height: 70,
                    padding: const EdgeInsets.symmetric(horizontal: 8),
                    child: Row(
                      children: [
                        const SizedBox(
                          width: 56,
                          child: Center(
                            child: Icon(
                              Icons.admin_panel_settings,
                              color: Colors.white,
                              size: 30,
                            ),
                          ),
                        ),
                        if (_isSidebarExpanded)
                          Expanded(
                            child: AnimatedOpacity(
                              duration: const Duration(milliseconds: 200),
                              opacity: _isSidebarExpanded ? 1.0 : 0.0,
                              child: Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 8.0),
                                child: Text(
                                  isRtl ? 'لوحة تحكم مقاييس' : 'Magaiees Admin',
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 16,
                                    fontWeight: FontWeight.bold,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                  const Divider(color: Colors.white24, height: 1),
                  const SizedBox(height: 12),

                  // Navigation Items
                  Expanded(
                    child: ListView(
                      padding: const EdgeInsets.symmetric(horizontal: 8),
                      children: [
                        _buildNavItem(0, _indexToIcon[0], isRtl ? 'الرئيسية' : 'Dashboard'),
                        _buildNavItem(1, _indexToIcon[1], isRtl ? 'إدارة المشتركين' : 'Subscribers'),
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

                  // Bottom Area (Admin Info & Logout)
                  const Divider(color: Colors.white24, height: 1),
                  Container(
                    height: 68,
                    padding: const EdgeInsets.symmetric(horizontal: 8),
                    child: Row(
                      children: [
                        const SizedBox(
                          width: 56,
                          child: Center(
                            child: CircleAvatar(
                              radius: 18,
                              backgroundColor: Colors.white24,
                              child: Icon(Icons.person, color: Colors.white, size: 20),
                            ),
                          ),
                        ),
                        if (_isSidebarExpanded)
                          Expanded(
                            child: AnimatedOpacity(
                              duration: const Duration(milliseconds: 200),
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
                                    icon: const Icon(Icons.logout, color: Colors.redAccent, size: 20),
                                    tooltip: isRtl ? 'تسجيل الخروج' : 'Logout',
                                    onPressed: () async {
                                      final confirmed = await showDialog<bool>(
                                        context: context,
                                        builder: (ctx) => AlertDialog(
                                          title: Text(isRtl ? 'تأكيد تسجيل الخروج' : 'Confirm Logout'),
                                          content: Text(isRtl ? 'هل تريد تسجيل الخروج من لوحة التحكم؟' : 'Are you sure you want to log out?'),
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
                                    },
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
          ),

          // ── Main Content Area ──────────────────────────────────────
          Expanded(
            child: Column(
              children: [
                // Breadcrumb / Top Bar
                _buildBreadcrumbBar(isRtl),
                // Page Content
                Expanded(
                  child: widget.child ?? IndexedStack(
                    index: _selectedIndex,
                    children: _pages,
                  ),
                ),
              ],
            ),
          ),
        ],
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
      ),
    );
  }

  Widget _buildNavItem(int index, IconData icon, String label) {
    final isSelected = _selectedIndex == index;

    return Padding(
      padding: const EdgeInsets.only(bottom: 4.0),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(10),
          onTap: () => _navigateToTab(index),
          child: Container(
            height: 48,
            decoration: BoxDecoration(
              color: isSelected ? Colors.blue.withValues(alpha: 0.22) : Colors.transparent,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(
                color: isSelected ? Colors.blue.withValues(alpha: 0.5) : Colors.transparent,
                width: 1,
              ),
            ),
            child: Row(
              children: [
                // Fixed Icon Container - always exact same width & position
                SizedBox(
                  width: 56,
                  height: 48,
                  child: Center(
                    child: Icon(
                      icon,
                      color: isSelected ? Colors.blue.shade300 : Colors.grey.shade400,
                      size: 24,
                    ),
                  ),
                ),
                // Text Label - fades in/out when expanded
                if (_isSidebarExpanded)
                  Expanded(
                    child: AnimatedOpacity(
                      duration: const Duration(milliseconds: 200),
                      opacity: _isSidebarExpanded ? 1.0 : 0.0,
                      child: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 8.0),
                        child: Text(
                          label,
                          style: TextStyle(
                            color: isSelected ? Colors.white : Colors.grey.shade300,
                            fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                            fontSize: 14,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
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
}
