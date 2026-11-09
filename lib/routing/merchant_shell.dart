import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/core/constants/app_colors.dart';
import 'package:magaiees_marketplace/core/extensions/context_extensions.dart';
import 'package:magaiees_marketplace/core/utils/app_notification.dart';
import 'package:magaiees_marketplace/core/utils/notification_sound_service.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/auth/domain/models/user_model.dart';
import 'package:magaiees_marketplace/features/chat/application/chat_provider.dart';
import 'package:magaiees_marketplace/features/merchant/orders/presentation/screens/merchant_orders_screen.dart';
import 'package:magaiees_marketplace/core/services/local_notification_service.dart';
import 'package:magaiees_marketplace/core/services/push_notification_service.dart';

/// Shell scaffold for the Merchant mode with bottom navigation.
///
/// Has a distinct visual style (blue accent) to differentiate
/// from the buyer storefront, and hosts a global real-time listener
/// for incoming orders, receipts, and buyer approvals.
class MerchantShell extends ConsumerStatefulWidget {
  const MerchantShell({
    required this.navigationShell,
    super.key,
  });

  final StatefulNavigationShell navigationShell;

  @override
  ConsumerState<MerchantShell> createState() => _MerchantShellState();
}

class _MerchantTabItem {
  final int branchIndex;
  final NavigationDestination destination;
  const _MerchantTabItem({required this.branchIndex, required this.destination});
}

class _MerchantShellState extends ConsumerState<MerchantShell> {
  RealtimeChannel? _ordersChannel;
  Set<String> _myStoreIds = {};

  @override
  void initState() {
    super.initState();
    _initMerchantGlobalOrdersListener();
  }

  Future<void> _initMerchantGlobalOrdersListener() async {
    final user = Supabase.instance.client.auth.currentUser;
    if (user == null) return;

    try {
      final currentUserModel = ref.read(authNotifierProvider).valueOrNull;
      Set<String> ids = {};

      if (currentUserModel != null && currentUserModel.isStoreStaff && currentUserModel.storeId != null) {
        ids = {currentUserModel.storeId!};
      } else {
        final storesRes = await Supabase.instance.client
            .from('stores')
            .select('id')
            .eq('owner_id', user.id);

        ids = (storesRes as List)
            .map((s) => s['id']?.toString())
            .whereType<String>()
            .toSet();
      }

      _myStoreIds = ids;
      if (_myStoreIds.isEmpty) return;

      // Sync push notification token
      PushNotificationService.instance.syncTokenWithServer();

      await _ordersChannel?.unsubscribe();

      final channelName = 'merchant_global_orders_${user.id}_${DateTime.now().millisecondsSinceEpoch}';
      _ordersChannel = Supabase.instance.client
          .channel(channelName)
          .onPostgresChanges(
            event: PostgresChangeEvent.all,
            schema: 'public',
            table: 'orders',
            callback: (payload) async {
              final newRec = payload.newRecord;
              String? rowStoreId = newRec['store_id']?.toString();

              // Fallback check if store_id not included in WAL update payload
              if (rowStoreId == null && newRec['id'] != null) {
                try {
                  final lookup = await Supabase.instance.client
                      .from('orders')
                      .select('store_id')
                      .eq('id', newRec['id'])
                      .maybeSingle();
                  rowStoreId = lookup?['store_id']?.toString();
                } catch (_) {}
              }

              // Only trigger if the order belongs to one of this merchant's stores
              if (rowStoreId != null && _myStoreIds.contains(rowStoreId)) {
                // 1. Play Magaiees custom notification sound
                NotificationSoundService.play();

                // 2. Invalidate merchant orders provider so UI updates in real time
                ref.invalidate(merchantOrdersProvider);

                final isRtl = mounted ? (Directionality.of(context) == TextDirection.rtl) : true;
                final eventType = payload.eventType;
                final paymentStatus = newRec['payment_status']?.toString();
                final buyerApprovedFees = newRec['buyer_approved_fees'];
                final orderId = newRec['id']?.toString();

                // 3. Post instant Android Heads-Up notification in system tray (vibrates and sounds even when minimized)
                if (eventType == PostgresChangeEvent.insert) {
                  LocalNotificationService.instance.showOrderNotification(
                    orderId: orderId ?? 'new_order',
                    title: isRtl ? '🔔 وصلك طلب جديد وارد!' : '🔔 New Order Received!',
                    body: isRtl
                        ? 'قام أحد المشترين بإرسال طلبية جديدة لمتجرك، اضغط للمعاينة والرد.'
                        : 'A buyer placed a new order for your store. Tap to review.',
                    payload: orderId,
                  );
                }

                // 4. Show in-app executive notification banner
                if (mounted) {
                  if (eventType == PostgresChangeEvent.insert) {
                    AppNotification.showNewOrder(
                      context,
                      orderId: orderId,
                      onTap: () => widget.navigationShell.goBranch(3),
                    );
                  } else if (paymentStatus == 'under_review') {
                    AppNotification.showReceiptReceived(
                      context,
                      orderId: orderId,
                      onTap: () => widget.navigationShell.goBranch(3),
                    );
                  } else if (buyerApprovedFees == true) {
                    AppNotification.showFeesApproved(
                      context,
                      orderId: orderId,
                      onTap: () => widget.navigationShell.goBranch(3),
                    );
                  } else {
                    AppNotification.showBanner(
                      context,
                      title: isRtl ? '📦 تحديث على أحد الطلبات' : '📦 Order Updated',
                      message: isRtl ? 'تم تحديث تفاصيل الطلب، اضغط للمراجعة.' : 'Order details or status have been updated.',
                      orderId: orderId,
                      actionLabel: isRtl ? 'عرض' : 'View',
                      onAction: () => widget.navigationShell.goBranch(3),
                    );
                  }
                }
              }
            },
          )
          .onPostgresChanges(
            event: PostgresChangeEvent.insert,
            schema: 'public',
            table: 'notifications',
            filter: PostgresChangeFilter(
              type: PostgresChangeFilterType.eq,
              column: 'user_id',
              value: user.id,
            ),
            callback: (payload) {
              final newRec = payload.newRecord;
              final type = newRec['type']?.toString();
              if (type == 'new_order') {
                NotificationSoundService.play();
                ref.invalidate(merchantOrdersProvider);
                final title = newRec['title']?.toString() ?? '🔔 طلب جديد وارد للمتجر!';
                final body = newRec['body']?.toString() ?? 'قام المشتري بإرسال طلبية جديدة، اضغط للمعاينة.';
                final relatedId = newRec['related_id']?.toString();
                LocalNotificationService.instance.showOrderNotification(
                  orderId: relatedId ?? 'new_order',
                  title: title,
                  body: body,
                  payload: relatedId,
                );
              }
            },
          )
          .subscribe();
    } catch (e) {
      debugPrint('Error setting up merchant global orders listener: $e');
    }
  }

  @override
  void dispose() {
    _ordersChannel?.unsubscribe();
    if (_ordersChannel != null) {
      Supabase.instance.client.removeChannel(_ordersChannel!);
      _ordersChannel = null;
    }
    super.dispose();
  }

  void _onTap(int index) {
    widget.navigationShell.goBranch(
      index,
      initialLocation: index == widget.navigationShell.currentIndex,
    );
  }

  @override
  Widget build(BuildContext context) {
    ref.listen(authNotifierProvider, (prev, next) {
      if (next.valueOrNull != null) {
        _initMerchantGlobalOrdersListener();
      } else {
        _ordersChannel?.unsubscribe();
        if (_ordersChannel != null) {
          Supabase.instance.client.removeChannel(_ordersChannel!);
          _ordersChannel = null;
        }
      }
    });

    ref.listen<AsyncValue<int>>(unreadMessagesCountProvider, (prev, next) {
      final prevCount = prev?.valueOrNull ?? 0;
      final nextCount = next.valueOrNull ?? 0;
      if (nextCount > prevCount) {
        NotificationSoundService.play();
      }
    });

    final unreadCount = ref.watch(unreadMessagesCountProvider).valueOrNull ?? 0;
    final currentUser = ref.watch(authNotifierProvider).valueOrNull;
    final isStaff = currentUser?.isStoreStaff ?? false;
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    final List<_MerchantTabItem> visibleTabs = [];

    if (!isStaff) {
      // Store Owner retains 100% full tabs and privileges
      visibleTabs.addAll([
        _MerchantTabItem(
          branchIndex: 0,
          destination: NavigationDestination(
            icon: const Icon(Icons.dashboard_outlined),
            selectedIcon: const Icon(Icons.dashboard),
            label: context.l10n.dashboard,
          ),
        ),
        _MerchantTabItem(
          branchIndex: 1,
          destination: NavigationDestination(
            icon: const Icon(Icons.inventory_2_outlined),
            selectedIcon: const Icon(Icons.inventory_2),
            label: context.l10n.inventory,
          ),
        ),
        _MerchantTabItem(
          branchIndex: 2,
          destination: NavigationDestination(
            icon: const Icon(Icons.add_box_outlined),
            selectedIcon: const Icon(Icons.add_box),
            label: context.l10n.addProduct,
          ),
        ),
        _MerchantTabItem(
          branchIndex: 3,
          destination: NavigationDestination(
            icon: const Icon(Icons.receipt_long_outlined),
            selectedIcon: const Icon(Icons.receipt_long),
            label: context.l10n.orders,
          ),
        ),
        _MerchantTabItem(
          branchIndex: 4,
          destination: NavigationDestination(
            icon: const Icon(Icons.settings_outlined),
            selectedIcon: const Icon(Icons.settings),
            label: context.l10n.storeSettings,
          ),
        ),
        _MerchantTabItem(
          branchIndex: 5,
          destination: NavigationDestination(
            icon: Badge(
              isLabelVisible: unreadCount > 0,
              label: Text(unreadCount.toString()),
              child: const Icon(Icons.chat_outlined),
            ),
            selectedIcon: Badge(
              isLabelVisible: unreadCount > 0,
              label: Text(unreadCount.toString()),
              child: const Icon(Icons.chat),
            ),
            label: isRtl ? 'المحادثات' : 'Chat',
          ),
        ),
      ]);
    } else {
      // Store Staff: filtered by granular RBAC permissions
      if (currentUser != null && currentUser.canManageStoreOrders) {
        visibleTabs.add(
          _MerchantTabItem(
            branchIndex: 3,
            destination: NavigationDestination(
              icon: const Icon(Icons.receipt_long_outlined),
              selectedIcon: const Icon(Icons.receipt_long),
              label: context.l10n.orders,
            ),
          ),
        );
      }

      if (currentUser != null && currentUser.canManageStoreProducts) {
        visibleTabs.add(
          _MerchantTabItem(
            branchIndex: 1,
            destination: NavigationDestination(
              icon: const Icon(Icons.inventory_2_outlined),
              selectedIcon: const Icon(Icons.inventory_2),
              label: context.l10n.inventory,
            ),
          ),
        );
        visibleTabs.add(
          _MerchantTabItem(
            branchIndex: 2,
            destination: NavigationDestination(
              icon: const Icon(Icons.add_box_outlined),
              selectedIcon: const Icon(Icons.add_box),
              label: context.l10n.addProduct,
            ),
          ),
        );
      }

      if (currentUser != null && currentUser.canChatStoreCustomers) {
        visibleTabs.add(
          _MerchantTabItem(
            branchIndex: 5,
            destination: NavigationDestination(
              icon: Badge(
                isLabelVisible: unreadCount > 0,
                label: Text(unreadCount.toString()),
                child: const Icon(Icons.chat_outlined),
              ),
              selectedIcon: Badge(
                isLabelVisible: unreadCount > 0,
                label: Text(unreadCount.toString()),
                child: const Icon(Icons.chat),
              ),
              label: isRtl ? 'المحادثات' : 'Chat',
            ),
          ),
        );
      }
    }

    // Safety fallback: if no tabs matched, fallback to Orders branch (3)
    if (visibleTabs.isEmpty) {
      visibleTabs.add(
        _MerchantTabItem(
          branchIndex: 3,
          destination: NavigationDestination(
            icon: const Icon(Icons.receipt_long_outlined),
            selectedIcon: const Icon(Icons.receipt_long),
            label: context.l10n.orders,
          ),
        ),
      );
    }

    final permittedBranchIndices = visibleTabs.map((t) => t.branchIndex).toSet();
    if (isStaff && !permittedBranchIndices.contains(widget.navigationShell.currentIndex)) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (mounted) {
          widget.navigationShell.goBranch(visibleTabs.first.branchIndex);
        }
      });
    }

    int selectedBarIndex = visibleTabs.indexWhere((t) => t.branchIndex == widget.navigationShell.currentIndex);
    if (selectedBarIndex < 0) selectedBarIndex = 0;

    return PopScope(
      canPop: isStaff ? selectedBarIndex == 0 : widget.navigationShell.currentIndex == 0,
      onPopInvokedWithResult: (didPop, result) {
        if (!didPop) {
          if (isStaff && selectedBarIndex != 0) {
            widget.navigationShell.goBranch(visibleTabs.first.branchIndex);
          } else if (!isStaff && widget.navigationShell.currentIndex != 0) {
            _onTap(0);
          }
        }
      },
      child: Scaffold(
        appBar: isStaff
            ? PreferredSize(
                preferredSize: const Size.fromHeight(36),
                child: Container(
                  color: AppColors.merchantAccent.withOpacity(0.08),
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  alignment: Alignment.center,
                  child: Row(
                    children: [
                      const Icon(Icons.badge, size: 16, color: AppColors.merchantAccent),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          '${isRtl ? 'الموظف:' : 'Staff:'} ${currentUser?.nameAr ?? currentUser?.name ?? ''} (${currentUser?.staffJobTitle ?? (isRtl ? 'فريق العمل' : 'Staff')}) • ${currentUser?.storeNameAr ?? currentUser?.storeName ?? (isRtl ? 'المتجر' : 'Store')}',
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: AppColors.merchantAccent,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ),
              )
            : null,
        body: widget.navigationShell,
        bottomNavigationBar: MediaQuery(
          data: MediaQuery.of(context).copyWith(
            textScaler: MediaQuery.of(context).textScaler.clamp(
              minScaleFactor: 0.85,
              maxScaleFactor: 1.05,
            ),
          ),
          child: NavigationBar(
            selectedIndex: selectedBarIndex,
            onDestinationSelected: (barIndex) {
              final targetBranch = visibleTabs[barIndex].branchIndex;
              widget.navigationShell.goBranch(
                targetBranch,
                initialLocation: targetBranch == widget.navigationShell.currentIndex,
              );
            },
            indicatorColor: AppColors.merchantAccent.withValues(alpha: 0.15),
            destinations: visibleTabs.map((t) => t.destination).toList(),
          ),
        ),
      ),
    );
  }
}
