import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/employee/presentation/screens/employee_dashboard_screen.dart';
import 'package:magaiees_marketplace/features/employee/presentation/screens/employee_orders_screen.dart';
import 'package:magaiees_marketplace/features/admin/presentation/screens/admin_products_screen.dart';
import 'package:magaiees_marketplace/features/admin/presentation/screens/admin_categories_screen.dart';
import 'package:magaiees_marketplace/features/admin/subscribers/presentation/screens/subscribers_management_screen.dart';
import 'package:magaiees_marketplace/features/admin/presentation/screens/admin_ads_screen.dart';
import 'package:magaiees_marketplace/features/chat/presentation/screens/chat_rooms_screen.dart';
import 'package:go_router/go_router.dart';

class EmployeeShell extends ConsumerStatefulWidget {
  const EmployeeShell({super.key});

  @override
  ConsumerState<EmployeeShell> createState() => _EmployeeShellState();
}

class _EmployeeShellState extends ConsumerState<EmployeeShell> {
  int _currentIndex = 0;

  @override
  Widget build(BuildContext context) {
    final user = ref.watch(authNotifierProvider).valueOrNull;
    final perms = user?.permissions ?? {};

    // Determine available pages based on permissions
    final List<Widget> pages = [];
    final List<NavigationDestination> destinations = [];

    // 1. Radar (Always visible, or checked)
    if (perms['can_view_radar'] == true || perms.isEmpty) {
      pages.add(const EmployeeDashboardScreen());
      destinations.add(const NavigationDestination(icon: Icon(Icons.explore_outlined), selectedIcon: Icon(Icons.explore), label: 'الرادار'));
      
      pages.add(const EmployeeOrdersScreen());
      destinations.add(const NavigationDestination(icon: Icon(Icons.list_alt_outlined), selectedIcon: Icon(Icons.list_alt), label: 'الطلبات'));
    }

    if (perms['can_manage_products'] == true) {
      pages.add(const AdminProductsScreen());
      destinations.add(const NavigationDestination(icon: Icon(Icons.inventory_2_outlined), selectedIcon: Icon(Icons.inventory_2), label: 'الأصناف'));
    }

    if (perms['can_manage_categories'] == true) {
      pages.add(const AdminCategoriesScreen());
      destinations.add(const NavigationDestination(icon: Icon(Icons.category_outlined), selectedIcon: Icon(Icons.category), label: 'الأقسام'));
    }

    if (perms['can_manage_subscribers'] == true) {
      pages.add(const SubscribersManagementScreen());
      destinations.add(const NavigationDestination(icon: Icon(Icons.people_outline), selectedIcon: Icon(Icons.people), label: 'المشتركين'));
    }

    if (perms['can_manage_ads'] == true) {
      pages.add(const AdminAdsScreen());
      destinations.add(const NavigationDestination(icon: Icon(Icons.campaign_outlined), selectedIcon: Icon(Icons.campaign), label: 'الإعلانات'));
    }
    
    if (perms['can_chat_support'] == true) {
      pages.add(const ChatRoomsScreen());
      destinations.add(const NavigationDestination(icon: Icon(Icons.support_agent_outlined), selectedIcon: Icon(Icons.support_agent), label: 'الدعم'));
    }

    // Fallback if no permissions
    if (pages.isEmpty) {
      pages.add(const Scaffold(body: Center(child: Text('لا توجد صلاحيات لعرض هذه الشاشة'))));
      destinations.add(const NavigationDestination(icon: Icon(Icons.block), label: 'لا صلاحيات'));
    }

    if (_currentIndex >= pages.length) _currentIndex = 0;

    // Use a vertical navigation rail for tablets/desktop, and bottom bar for mobile
    final isDesktop = MediaQuery.of(context).size.width > 800;

    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        body: Row(
          children: [
            if (isDesktop)
              NavigationRail(
                selectedIndex: _currentIndex,
                onDestinationSelected: (idx) => setState(() => _currentIndex = idx),
                labelType: NavigationRailLabelType.all,
                extended: MediaQuery.of(context).size.width > 1200,
                destinations: destinations.map((d) => NavigationRailDestination(icon: d.icon, selectedIcon: d.selectedIcon, label: Text(d.label))).toList(),
                leading: Padding(
                  padding: const EdgeInsets.only(bottom: 24, top: 16),
                  child: Column(
                    children: [
                      const CircleAvatar(radius: 24, backgroundColor: Colors.orange, child: Icon(Icons.badge, color: Colors.white)),
                      const SizedBox(height: 8),
                      Text('بوابة الموظف', style: TextStyle(color: Colors.grey.shade700, fontWeight: FontWeight.bold)),
                    ],
                  ),
                ),
                trailing: Expanded(
                  child: Align(
                    alignment: Alignment.bottomCenter,
                    child: Padding(
                      padding: const EdgeInsets.only(bottom: 16),
                      child: IconButton(
                        icon: const Icon(Icons.logout, color: Colors.red),
                        tooltip: 'تسجيل الخروج',
                        onPressed: () {
                          ref.read(authNotifierProvider.notifier).logout();
                          context.go('/');
                        },
                      ),
                    ),
                  ),
                ),
              ),
            if (isDesktop) const VerticalDivider(thickness: 1, width: 1),
            Expanded(
              child: pages[_currentIndex],
            ),
          ],
        ),
        bottomNavigationBar: isDesktop ? null : NavigationBar(
          selectedIndex: _currentIndex,
          onDestinationSelected: (idx) => setState(() => _currentIndex = idx),
          destinations: destinations,
        ),
      ),
    );
  }
}
