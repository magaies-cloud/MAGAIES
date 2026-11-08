import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';
import 'package:magaiees_marketplace/core/enums/app_mode.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/mode_switch/application/mode_provider.dart';
import 'package:magaiees_marketplace/routing/route_names.dart';
import 'package:magaiees_marketplace/routing/router_notifier.dart';

// Screens
import 'package:magaiees_marketplace/features/auth/presentation/screens/login_screen.dart';
import 'package:magaiees_marketplace/features/auth/presentation/screens/register_screen.dart';
import 'package:magaiees_marketplace/routing/buyer_shell.dart';
import 'package:magaiees_marketplace/routing/merchant_shell.dart';

// Placeholder screens
import 'package:magaiees_marketplace/features/buyer/home/presentation/screens/buyer_home_screen.dart';
import 'package:magaiees_marketplace/features/buyer/cart/presentation/screens/cart_screen.dart';
import 'package:magaiees_marketplace/features/admin/presentation/screens/admin_shell.dart';
import 'package:magaiees_marketplace/features/buyer/orders/presentation/screens/buyer_orders_screen.dart';
import 'package:magaiees_marketplace/features/buyer/profile/presentation/screens/buyer_profile_screen.dart';
import 'package:magaiees_marketplace/features/buyer/product_detail/presentation/screens/product_detail_screen.dart';
import 'package:magaiees_marketplace/features/buyer/category_products/presentation/screens/category_products_screen.dart';
import 'package:magaiees_marketplace/core/enums/user_role.dart';
import 'package:magaiees_marketplace/features/buyer/services/presentation/screens/service_providers_screen.dart';
import 'package:magaiees_marketplace/features/buyer/help_center/presentation/screens/help_center_screen.dart';
import 'package:magaiees_marketplace/features/buyer/checkout/presentation/screens/checkout_screen.dart';
import 'package:magaiees_marketplace/features/buyer/digital_broker/presentation/screens/quote_request_screen.dart';
import 'package:magaiees_marketplace/features/merchant/dashboard/presentation/screens/merchant_dashboard_screen.dart';
import 'package:magaiees_marketplace/features/merchant/inventory/presentation/screens/merchant_inventory_screen.dart';
import 'package:magaiees_marketplace/features/merchant/add_product/presentation/screens/add_product_screen.dart';
import 'package:magaiees_marketplace/features/merchant/orders/presentation/screens/merchant_orders_screen.dart';
import 'package:magaiees_marketplace/features/merchant/store_management/presentation/screens/store_settings_screen.dart';
import 'package:magaiees_marketplace/features/employee/presentation/screens/employee_shell.dart';
import 'package:magaiees_marketplace/features/chat/presentation/screens/chat_rooms_screen.dart';
import 'package:magaiees_marketplace/features/chat/presentation/screens/chat_messages_screen.dart';
import 'package:magaiees_marketplace/features/admin/subscribers/domain/models/subscriber_model.dart';
import 'package:magaiees_marketplace/features/admin/subscribers/presentation/widgets/subscriber_detail_sheet.dart';
import 'package:magaiees_marketplace/features/auth/domain/models/user_model.dart';
import 'package:magaiees_marketplace/features/merchant/team/presentation/screens/merchant_team_screen.dart';
import 'package:magaiees_marketplace/features/merchant/finance_report/presentation/screens/merchant_financial_report_screen.dart';
part 'app_router.g.dart';

/// Global navigator keys for shell routes.
final rootNavigatorKey = GlobalKey<NavigatorState>(debugLabel: 'root');

/// Provides the [GoRouter] instance configured with auth/mode redirects.
@Riverpod(keepAlive: true)
GoRouter goRouter(GoRouterRef ref) {
  final notifier = ref.watch(routerNotifierProvider);

  return GoRouter(
    navigatorKey: rootNavigatorKey,
    initialLocation: RouteNames.buyerHome,
    debugLogDiagnostics: true,
    refreshListenable: notifier,
    redirect: (context, state) => _redirect(ref, state),
    routes: [
      // === Auth Routes (outside shell) ===
      GoRoute(
        path: RouteNames.login,
        builder: (context, state) => const LoginScreen(),
      ),
      GoRoute(
        path: RouteNames.register,
        builder: (context, state) => const RegisterScreen(),
      ),

      // === Buyer Shell (Bottom Navigation) ===
      StatefulShellRoute.indexedStack(
        builder: (context, state, navigationShell) {
          return BuyerShell(navigationShell: navigationShell);
        },
        branches: [
          // Tab 1: Home
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: RouteNames.buyerHome,
                builder: (context, state) => const BuyerHomeScreen(),
                routes: [
                  // Nested: Product Detail
                  GoRoute(
                    path: 'product/:id',
                    builder: (context, state) => ProductDetailScreen(
                      productId: state.pathParameters['id']!,
                    ),
                  ),
                  // Nested: Sub Categories & Category Products
                  GoRoute(
                    path: 'category/:id',
                    builder: (context, state) => CategoryProductsScreen(
                      categoryId: state.pathParameters['id']!,
                      subCategoryId: state.uri.queryParameters['subId'],
                      categoryName: state.uri.queryParameters['name'],
                    ),
                    routes: [
                      GoRoute(
                        path: 'products',
                        builder: (context, state) => CategoryProductsScreen(
                          categoryId: state.pathParameters['id']!,
                          subCategoryId: state.uri.queryParameters['subId'],
                          categoryName: state.uri.queryParameters['name'],
                        ),
                      ),
                    ],
                  ),
                  // Nested: Service Providers
                  GoRoute(
                    path: 'services',
                    builder: (context, state) => const ServiceProvidersScreen(),
                  ),
                ],
              ),
            ],
          ),
          // Tab 2: Cart
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: RouteNames.buyerCart,
                builder: (context, state) => const CartScreen(),
                routes: [
                  GoRoute(
                    path: 'checkout',
                    builder: (context, state) => const CheckoutScreen(),
                  ),
                ],
              ),
            ],
          ),
          // Tab 3: Orders
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: RouteNames.buyerOrders,
                builder: (context, state) => const BuyerOrdersScreen(),
              ),
            ],
          ),
          // Tab 4: Chat
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: RouteNames.buyerChat,
                builder: (context, state) => const ChatRoomsScreen(),
                routes: [
                  GoRoute(
                    path: 'room/:roomId',
                    name: 'buyerChatRoom',
                    builder: (context, state) => ChatMessagesScreen(
                      roomId: state.pathParameters['roomId']!,
                    ),
                  ),
                ],
              ),
            ],
          ),
          // Tab 5: Profile
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: RouteNames.buyerProfile,
                builder: (context, state) => const BuyerProfileScreen(),
                routes: [
                  GoRoute(
                    path: 'help',
                    builder: (context, state) => const HelpCenterScreen(),
                  ),
                ],
              ),
            ],
          ),
        ],
      ),

      // === Digital Broker (full screen, above buyer shell) ===
      GoRoute(
        path: RouteNames.buyerDigitalBroker,
        parentNavigatorKey: rootNavigatorKey,
        builder: (context, state) => const QuoteRequestScreen(),
      ),

      // === Merchant Shell (Bottom Navigation, Guarded) ===
      StatefulShellRoute.indexedStack(
        builder: (context, state, navigationShell) {
          return MerchantShell(navigationShell: navigationShell);
        },
        branches: [
          // Tab 1: Dashboard
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: RouteNames.merchantDashboard,
                builder: (context, state) => const MerchantDashboardScreen(),
              ),
            ],
          ),
          // Tab 2: Inventory
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: RouteNames.merchantInventory,
                builder: (context, state) => const MerchantInventoryScreen(),
              ),
            ],
          ),
          // Tab 3: Add Product
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: RouteNames.merchantAddProduct,
                builder: (context, state) => const AddProductScreen(),
              ),
            ],
          ),
          // Tab 4: Merchant Orders
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: RouteNames.merchantOrders,
                builder: (context, state) => const MerchantOrdersScreen(),
              ),
            ],
          ),
          // Tab 5: Store Settings
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: RouteNames.merchantStoreSettings,
                builder: (context, state) => const StoreSettingsScreen(),
              ),
            ],
          ),
          // Tab 6: Chat
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: RouteNames.merchantChat,
                builder: (context, state) => const ChatRoomsScreen(),
                routes: [
                  GoRoute(
                    path: 'room/:roomId',
                    name: 'merchantChatRoom',
                    builder: (context, state) => ChatMessagesScreen(
                      roomId: state.pathParameters['roomId']!,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ],
      ),

      // === Merchant Team Screen (Full Screen) ===
      GoRoute(
        path: RouteNames.merchantTeam,
        parentNavigatorKey: rootNavigatorKey,
        builder: (context, state) => const MerchantTeamScreen(),
      ),

      // === Merchant Financial Report Screen (Full Screen) ===
      GoRoute(
        path: RouteNames.merchantFinancialReport,
        parentNavigatorKey: rootNavigatorKey,
        builder: (context, state) => const MerchantFinancialReportScreen(),
      ),

      // === Admin Panel Routes ===
      GoRoute(
        path: RouteNames.adminDashboard,
        parentNavigatorKey: rootNavigatorKey,
        builder: (context, state) => const AdminShell(initialIndex: 0),
      ),
      GoRoute(
        path: RouteNames.adminSubscribers,
        parentNavigatorKey: rootNavigatorKey,
        builder: (context, state) => const AdminShell(initialIndex: 1),
      ),
      GoRoute(
        path: RouteNames.adminSubscriberProfile,
        parentNavigatorKey: rootNavigatorKey,
        builder: (context, state) {
          final extra = state.extra;
          if (extra is SubscriberModel) {
            return Scaffold(
              appBar: AppBar(title: const Text('الملف التعريفي للمشترك')),
              body: SubscriberDetailSheet(subscriber: extra, isEmbedded: true),
            );
          }
          return const AdminShell(initialIndex: 1);
        },
      ),
      GoRoute(
        path: RouteNames.adminAds,
        parentNavigatorKey: rootNavigatorKey,
        builder: (context, state) => const AdminShell(initialIndex: 2),
      ),
      GoRoute(
        path: RouteNames.adminOrders,
        parentNavigatorKey: rootNavigatorKey,
        builder: (context, state) => const AdminShell(initialIndex: 3),
      ),
      GoRoute(
        path: RouteNames.adminCategories,
        parentNavigatorKey: rootNavigatorKey,
        builder: (context, state) => const AdminShell(initialIndex: 4),
      ),
      GoRoute(
        path: RouteNames.adminProducts,
        parentNavigatorKey: rootNavigatorKey,
        builder: (context, state) => const AdminShell(initialIndex: 5),
      ),
      GoRoute(
        path: RouteNames.adminServices,
        parentNavigatorKey: rootNavigatorKey,
        builder: (context, state) => const AdminShell(initialIndex: 6),
      ),
      GoRoute(
        path: RouteNames.adminFinance,
        parentNavigatorKey: rootNavigatorKey,
        builder: (context, state) => const AdminShell(initialIndex: 7),
      ),
      GoRoute(
        path: RouteNames.adminSettings,
        parentNavigatorKey: rootNavigatorKey,
        builder: (context, state) => const AdminShell(initialIndex: 8),
      ),
      GoRoute(
        path: RouteNames.adminDigitalBroker,
        parentNavigatorKey: rootNavigatorKey,
        builder: (context, state) => const AdminShell(initialIndex: 9),
      ),

      // === Employee Shell ===
      GoRoute(
        path: RouteNames.employeeDashboard,
        parentNavigatorKey: rootNavigatorKey,
        builder: (context, state) => const EmployeeShell(),
      ),
    ],
    errorBuilder: (context, state) {
      final user = ref.read(authNotifierProvider).valueOrNull;
      if (user != null && user.role.isAdmin) {
        return const AdminShell(initialIndex: 0);
      }
      return const BuyerHomeScreen();
    },
  );
}

/// Central redirect logic for the router.
///
/// Handles:
/// 1. Unauthenticated users → login
/// 2. Authenticated users on login page → appropriate home
/// 3. Merchant route access guard (requires hasStore)
/// 4. Mode-based routing
String? _redirect(Ref ref, GoRouterState state) {
  final authState = ref.read(authNotifierProvider);
  final user = authState.valueOrNull;
  final isLoggedIn = user != null;
  final currentMode = ref.read(modeNotifierProvider);
  final currentPath = state.matchedLocation;

  // Auth routes that don't require authentication
  final isAuthRoute = currentPath == RouteNames.login ||
      currentPath == RouteNames.register;

  // If still loading auth state, don't redirect
  if (authState.isLoading) return null;

  // 1. Not logged in → redirect to login (unless already on auth route)
  if (!isLoggedIn) {
    return isAuthRoute ? null : RouteNames.login;
  }

  // 2. Logged in but on auth route → redirect to appropriate dashboard/home
  if (isLoggedIn && isAuthRoute) {
    if (user.role.isAdmin) {
      return RouteNames.adminDashboard;
    }
    if (user.isStoreStaff) {
      return user.canManageStoreOrders ? RouteNames.merchantOrders : RouteNames.merchantInventory;
    }
    if (user.role == UserRole.employee) {
      return RouteNames.employeeDashboard;
    }
    if (user.role.isMerchant || currentMode == AppMode.merchant) {
      return RouteNames.merchantDashboard;
    }
    return RouteNames.buyerHome;
  }

  // 3. Guard admin routes — require admin role
  final isAdminRoute = currentPath.startsWith('/admin');
  if (isAdminRoute && !user.role.isAdmin) {
    return RouteNames.buyerHome;
  }

  // 4. Guard merchant routes — require hasStore or isStoreStaff (admins always allowed for inspection)
  final isMerchantRoute = currentPath.startsWith('/merchant');
  if (isMerchantRoute) {
    if (!user.hasStore && !user.isStoreStaff && !user.role.isAdmin) {
      // Redirect to buyer home if they don't have store access
      return RouteNames.buyerHome;
    }
    // Prevent store staff from accessing owner-only financial, team, dashboard, and store settings routes
    if (user.isStoreStaff) {
      if (currentPath == RouteNames.merchantFinancialReport ||
          currentPath == RouteNames.merchantTeam ||
          currentPath == RouteNames.merchantDashboard ||
          currentPath == RouteNames.merchantStoreSettings) {
        return user.canManageStoreOrders ? RouteNames.merchantOrders : RouteNames.merchantInventory;
      }
    }
  }

  // 5. Guard employee routes — require platform employee or admin role (not store staff)
  final isEmployeeRoute = currentPath.startsWith('/employee');
  if (isEmployeeRoute && ((user.role != UserRole.employee) || user.isStoreStaff) && !user.role.isAdmin) {
    return RouteNames.buyerHome;
  }

  // No redirect needed
  return null;
}
