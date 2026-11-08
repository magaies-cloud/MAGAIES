import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/employee/application/employee_orders_provider.dart';
import 'package:magaiees_marketplace/features/employee/presentation/screens/employee_dashboard_screen.dart';
import 'package:magaiees_marketplace/features/employee/presentation/screens/employee_orders_screen.dart';
import 'package:magaiees_marketplace/features/admin/presentation/screens/admin_products_screen.dart';
import 'package:magaiees_marketplace/features/admin/presentation/screens/admin_categories_screen.dart';
import 'package:magaiees_marketplace/features/admin/subscribers/presentation/screens/subscribers_management_screen.dart';
import 'package:magaiees_marketplace/features/admin/presentation/screens/admin_ads_screen.dart';
import 'package:magaiees_marketplace/features/chat/presentation/screens/chat_rooms_screen.dart';
import 'package:magaiees_marketplace/features/admin/digital_broker/presentation/screens/quote_requests_management_screen.dart';
import 'package:magaiees_marketplace/core/utils/app_notification.dart';
import 'package:go_router/go_router.dart';

class EmployeeShell extends ConsumerStatefulWidget {
  const EmployeeShell({super.key});

  @override
  ConsumerState<EmployeeShell> createState() => _EmployeeShellState();
}

class _EmployeeShellState extends ConsumerState<EmployeeShell> {
  @override
  Widget build(BuildContext context) {
    final rawIndex = ref.watch(employeeShellIndexProvider);
    final user = ref.watch(authNotifierProvider).valueOrNull;
    final perms = user?.permissions ?? {};
    final employeeState = ref.watch(employeeOrdersProvider);
    final pendingCount = employeeState.pendingOrders.length;

    // Listen for new pending orders to show top notification banner
    ref.listen<EmployeeState>(employeeOrdersProvider, (prev, next) {
      if (next.pendingOrders.isNotEmpty) {
        final prevIds = prev?.pendingOrders.map((e) => e.id).toSet() ?? {};
        for (var order in next.pendingOrders) {
          if (!prevIds.contains(order.id)) {
            AppNotification.showNewOrder(
              context,
              orderId: order.orderNumber,
              message: 'طلب حصري جديد متاح (#${order.orderNumber}): ${order.buyerName.isNotEmpty ? order.buyerName : 'مشتري مقاييس'}',
              onTap: () {
                ref.read(employeeShellIndexProvider.notifier).state = 0; // Go to Radar
              },
            );
            break;
          }
        }
      }
    });

    // Determine available pages based on permissions
    final List<Widget> pages = [];
    final List<NavigationDestination> destinations = [];

    // 1. Radar & Orders (Prioritized)
    if (perms['can_view_radar'] == true) {
      pages.add(const EmployeeDashboardScreen());
      destinations.add(NavigationDestination(
        icon: pendingCount > 0
            ? Badge.count(count: pendingCount, child: const Icon(Icons.explore_outlined))
            : const Icon(Icons.explore_outlined),
        selectedIcon: pendingCount > 0
            ? Badge.count(count: pendingCount, child: const Icon(Icons.explore))
            : const Icon(Icons.explore),
        label: 'الرادار',
      ));
      
      pages.add(const EmployeeOrdersScreen());
      destinations.add(NavigationDestination(
        icon: pendingCount > 0
            ? Badge.count(count: pendingCount, child: const Icon(Icons.list_alt_outlined))
            : const Icon(Icons.list_alt_outlined),
        selectedIcon: pendingCount > 0
            ? Badge.count(count: pendingCount, child: const Icon(Icons.list_alt))
            : const Icon(Icons.list_alt),
        label: 'الطلبات',
      ));
    }

    // 2. Products
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
    
    if (perms['can_manage_digital_broker'] == true) {
      pages.add(const QuoteRequestsManagementScreen());
      destinations.add(const NavigationDestination(icon: Icon(Icons.handshake_outlined), selectedIcon: Icon(Icons.handshake), label: 'الوسيط'));
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

    final currentIndex = (rawIndex >= 0 && rawIndex < pages.length) ? rawIndex : 0;

    // Use a vertical navigation rail for tablets/desktop, and bottom bar for mobile
    final isDesktop = MediaQuery.of(context).size.width > 800;

    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        body: Row(
          children: [
            if (isDesktop)
              NavigationRail(
                selectedIndex: currentIndex,
                onDestinationSelected: (idx) => ref.read(employeeShellIndexProvider.notifier).state = idx,
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
              child: pages[currentIndex],
            ),
          ],
        ),
        bottomNavigationBar: isDesktop ? null : Builder(
          builder: (ctx) {
            final int maxMainTabs = 4;
            final int mainTabsCount = destinations.length > maxMainTabs ? maxMainTabs : destinations.length;
            
            final List<NavigationDestination> bottomDestinations = destinations.take(mainTabsCount).toList();
            bottomDestinations.add(const NavigationDestination(icon: Icon(Icons.menu), label: 'المزيد'));

            return NavigationBar(
              selectedIndex: currentIndex < mainTabsCount ? currentIndex : mainTabsCount,
              onDestinationSelected: (idx) {
                if (idx == mainTabsCount) {
                  _showMoreMenu(context, destinations, mainTabsCount, currentIndex);
                } else {
                  ref.read(employeeShellIndexProvider.notifier).state = idx;
                }
              },
              destinations: bottomDestinations,
            );
          }
        ),
      ),
    );
  }

  void _showMoreMenu(BuildContext context, List<NavigationDestination> allDestinations, int mainTabsCount, int currentIndex) {
    showModalBottomSheet(
      context: context,
      backgroundColor: const Color(0xFF0F172A),
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) {
        return Directionality(
          textDirection: TextDirection.rtl,
          child: SafeArea(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const SizedBox(height: 8),
                Container(width: 40, height: 4, decoration: BoxDecoration(color: Colors.grey.shade600, borderRadius: BorderRadius.circular(2))),
                const SizedBox(height: 16),
                
                if (allDestinations.length > mainTabsCount)
                  ...List.generate(allDestinations.length - mainTabsCount, (i) {
                    final actualIndex = mainTabsCount + i;
                    final d = allDestinations[actualIndex];
                    final isSelected = currentIndex == actualIndex;
                    return ListTile(
                      leading: isSelected ? d.selectedIcon : d.icon,
                      iconColor: isSelected ? Colors.orange : Colors.grey.shade400,
                      title: Text(d.label, style: TextStyle(color: isSelected ? Colors.orange : Colors.white, fontWeight: isSelected ? FontWeight.bold : FontWeight.normal)),
                      onTap: () {
                        Navigator.pop(ctx);
                        ref.read(employeeShellIndexProvider.notifier).state = actualIndex;
                      },
                    );
                  }),
                  
                if (allDestinations.length > mainTabsCount)
                  const Divider(color: Colors.white24),
                  
                ListTile(
                  leading: const Icon(Icons.logout, color: Colors.redAccent),
                  title: const Text('تسجيل الخروج', style: TextStyle(color: Colors.redAccent, fontWeight: FontWeight.bold)),
                  onTap: () {
                    Navigator.pop(ctx);
                    ref.read(authNotifierProvider.notifier).logout();
                    context.go('/');
                  },
                ),
                const SizedBox(height: 16),
              ],
            ),
          ),
        );
      },
    );
  }
}
