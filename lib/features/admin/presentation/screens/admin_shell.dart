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
import 'package:magaiees_marketplace/routing/route_names.dart';

final adminTabIndexProvider = StateProvider<int>((ref) => 0);

class AdminShell extends ConsumerStatefulWidget {
  const AdminShell({super.key});

  @override
  ConsumerState<AdminShell> createState() => _AdminShellState();
}

class _AdminShellState extends ConsumerState<AdminShell> {
  int _selectedIndex = 0;
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
  ];

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    ref.listen<int>(adminTabIndexProvider, (prev, next) {
      if (next != _selectedIndex) {
        setState(() => _selectedIndex = next);
      }
    });

    final authUser = ref.watch(authNotifierProvider).valueOrNull;
    final userName = (isRtl && authUser?.nameAr != null && authUser!.nameAr!.isNotEmpty ? authUser.nameAr : authUser?.name) ?? (isRtl ? 'مدير النظام' : 'Admin');
    final userEmail = authUser?.email ?? 'admin@magaiees.com';

    return Scaffold(
      body: Row(
        children: [
          // Collapsible Hover Sidebar
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
                          _buildNavItem(0, Icons.dashboard_outlined, isRtl ? 'الرئيسية' : 'Dashboard'),
                          _buildNavItem(1, Icons.people_outline, isRtl ? 'إدارة المشتركين' : 'Subscribers'),
                          _buildNavItem(2, Icons.campaign_outlined, isRtl ? 'إدارة الإعلانات' : 'Ads'),
                          _buildNavItem(3, Icons.shopping_cart_outlined, isRtl ? 'إدارة الطلبات' : 'Orders'),
                          _buildNavItem(4, Icons.category_outlined, isRtl ? 'إدارة الأقسام' : 'Categories'),
                          _buildNavItem(5, Icons.price_change_outlined, isRtl ? 'الأسعار والتسعير' : 'Pricing'),
                          _buildNavItem(6, Icons.engineering, isRtl ? 'مقدمو الخدمات' : 'Service Providers'),
                          _buildNavItem(7, Icons.account_balance_wallet_outlined, isRtl ? 'المالية والحسابات' : 'Finance & Accounting'),
                          Padding(
                            padding: const EdgeInsets.symmetric(vertical: 8.0),
                            child: Divider(color: Colors.white.withValues(alpha: 0.12), height: 1),
                          ),
                          _buildNavItem(8, Icons.settings_outlined, isRtl ? 'الإعدادات' : 'Settings'),
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
                                      await ref.read(authNotifierProvider.notifier).logout();
                                      if (context.mounted) {
                                        context.go(RouteNames.login);
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

          // Main Content Area
          Expanded(
            child: IndexedStack(
              index: _selectedIndex,
              children: _pages,
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
          onTap: () {
            setState(() => _selectedIndex = index);
            ref.read(adminTabIndexProvider.notifier).state = index;
          },
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
