import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/core/constants/app_colors.dart';
import 'package:magaiees_marketplace/core/enums/user_role.dart';
import 'package:magaiees_marketplace/core/theme/app_theme.dart';
import 'package:magaiees_marketplace/core/extensions/context_extensions.dart';
import 'package:magaiees_marketplace/core/utils/app_notification.dart';
import 'package:magaiees_marketplace/core/utils/notification_sound_service.dart';
import 'package:magaiees_marketplace/features/admin/subscribers/application/subscribers_provider.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/auth/domain/models/user_model.dart';
import 'package:magaiees_marketplace/features/chat/application/chat_provider.dart';
import 'package:magaiees_marketplace/features/merchant/orders/presentation/screens/merchant_orders_screen.dart';
import 'package:magaiees_marketplace/features/merchant/presentation/widgets/usd_rate_history_dialog.dart';
import 'package:magaiees_marketplace/core/services/local_notification_service.dart';
import 'package:magaiees_marketplace/core/services/push_notification_service.dart';
import 'package:magaiees_marketplace/routing/route_names.dart';

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

  /// Tracks the last visited branch before entering AddProduct (branch 2)
  /// so back navigation returns to the exact previous tab.
  static int lastNonAddBranchIndex = 0;

  @override
  ConsumerState<MerchantShell> createState() => _MerchantShellState();
}

class _MerchantTabItem {
  final int branchIndex;
  final IconData icon;
  final IconData selectedIcon;
  final String label;
  final int badge;
  const _MerchantTabItem({
    required this.branchIndex,
    required this.icon,
    required this.selectedIcon,
    required this.label,
    this.badge = 0,
  });
}

class _MerchantShellState extends ConsumerState<MerchantShell> {
  RealtimeChannel? _ordersChannel;
  Set<String> _myStoreIds = {};
  final Map<String, DateTime> _recentAlertTimestamps = {};
  String _merchantUsdRateSource = 'platform';
  bool _merchantUsdRateAlertsEnabled = true;
  bool _usdRateListenerReady = false;
  Timer? _usdInitTimer;
  double? _edgeSwipeStartX;
  double _edgeSwipeDeltaX = 0.0;

  bool _shouldTriggerAlert(String key) {
    final now = DateTime.now();
    final prev = _recentAlertTimestamps[key];
    if (prev != null && now.difference(prev).inSeconds < 5) {
      return false;
    }
    _recentAlertTimestamps[key] = now;
    return true;
  }

  @override
  void initState() {
    super.initState();
    _initMerchantGlobalOrdersListener();
    _usdInitTimer = Timer(const Duration(milliseconds: 2500), () {
      if (mounted) {
        _usdRateListenerReady = true;
      }
    });
  }

  Future<void> _refreshMerchantUsdRateSource(String userId) async {
    bool? localAlertsPref;
    try {
      final prefs = await SharedPreferences.getInstance();
      localAlertsPref = prefs.getBool('usd_rate_alerts_enabled');
      if (localAlertsPref != null) {
        _merchantUsdRateAlertsEnabled = localAlertsPref;
      }
    } catch (_) {}

    try {
      final userRes = await Supabase.instance.client
          .from('users')
          .select('permissions')
          .eq('id', userId)
          .maybeSingle();
      if (userRes != null && userRes['permissions'] is Map) {
        final perms = userRes['permissions'] as Map;
        final src = perms['usd_rate_source']?.toString();
        if (src != null && src.isNotEmpty) {
          _merchantUsdRateSource = src;
        } else {
          _merchantUsdRateSource = 'platform';
        }
        final rawAlerts = perms['usd_rate_alerts_enabled'];
        if (rawAlerts is bool) {
          _merchantUsdRateAlertsEnabled = rawAlerts;
        } else if (rawAlerts != null) {
          _merchantUsdRateAlertsEnabled =
              rawAlerts.toString().toLowerCase() != 'false';
        } else if (localAlertsPref != null) {
          _merchantUsdRateAlertsEnabled = localAlertsPref;
        } else {
          _merchantUsdRateAlertsEnabled = true;
        }
      }
    } catch (_) {}
  }

  Future<void> _initMerchantGlobalOrdersListener() async {
    final user = Supabase.instance.client.auth.currentUser;
    if (user == null) return;

    try {
      await _refreshMerchantUsdRateSource(user.id);
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

      // Sync push notification token regardless of store list state
      PushNotificationService.instance.syncTokenWithServer();

      // Catch up on any unread USD rate change or broadcast that arrived while app was closed
      unawaited(_checkUnreadOfflineAlerts(user.id));

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

              if (!mounted) return;

              // Only trigger if the order belongs to one of this merchant's stores
              if (rowStoreId != null && _myStoreIds.contains(rowStoreId)) {
                final eventType = payload.eventType;
                final paymentStatus = newRec['payment_status']?.toString();
                final buyerApprovedFees = newRec['buyer_approved_fees'];
                final orderId = newRec['id']?.toString() ?? 'order';
                final dedupKey = '${eventType.name}_${orderId}_${paymentStatus}_$buyerApprovedFees';
                if (!_shouldTriggerAlert(dedupKey)) return;

                // 1. Play Magaiees custom notification sound
                NotificationSoundService.play();

                // 2. Invalidate merchant orders provider so UI updates in real time
                ref.invalidate(merchantOrdersProvider);

                final isRtl = mounted ? (Directionality.of(context) == TextDirection.rtl) : true;

                // 3. Post instant Android/iOS Heads-Up notification in system tray
                if (eventType == PostgresChangeEvent.insert) {
                  LocalNotificationService.instance.showOrderNotification(
                    orderId: orderId,
                    title: isRtl ? '🔔 وصلك طلب جديد وارد!' : '🔔 New Order Received!',
                    body: isRtl
                        ? 'قام أحد المشترين بإرسال طلبية جديدة لمتجرك، اضغط للمعاينة والرد.'
                        : 'A buyer placed a new order for your store. Tap to review.',
                    payload: 'merchant_order:$orderId',
                  );
                } else if (paymentStatus == 'under_review') {
                  LocalNotificationService.instance.showOrderNotification(
                    orderId: '${orderId}_receipt',
                    title: isRtl ? '🧾 تم استلام إشعار دفع جديد!' : '🧾 Payment Receipt Received!',
                    body: isRtl
                        ? 'قام المشتري برفع إشعار التحويل البنكي للطلب، اضغط للمراجعة والاعتماد.'
                        : 'Buyer uploaded a payment receipt. Tap to review and approve.',
                    payload: 'merchant_order:$orderId',
                  );
                } else if (buyerApprovedFees == true) {
                  LocalNotificationService.instance.showOrderNotification(
                    orderId: '${orderId}_fees',
                    title: isRtl ? '✅ وافق المشتري على رسوم الترحيل!' : '✅ Buyer Approved Shipping Fees!',
                    body: isRtl
                        ? 'اعتمد المشتري رسوم الترحيل والتنزيل للطلب، يمكنك متابعة التجهيز الآن.'
                        : 'Buyer approved transport fees for the order.',
                    payload: 'merchant_order:$orderId',
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
            callback: (payload) async {
              final newRec = payload.newRecord;
              final type = newRec['type']?.toString() ?? 'general';
              final relatedId = newRec['related_id']?.toString() ?? 'notif_${DateTime.now().millisecondsSinceEpoch}';
              final dedupKey = 'insert_${relatedId}_null_null';
              if (type == 'new_order' && !_shouldTriggerAlert(dedupKey)) {
                return;
              }

              final currentAuthUser = ref.read(authNotifierProvider).valueOrNull;
              if (type == 'usd_rate_changed') {
                // STRICT MERCHANT-ONLY GUARD: Only Merchants synced with platform USD rate who enabled alerts
                if (currentAuthUser?.role != UserRole.merchant) return;
                await _refreshMerchantUsdRateSource(user.id);
                if (_merchantUsdRateSource != 'platform' || !_merchantUsdRateAlertsEnabled) return;

                final bodyText = newRec['body_ar']?.toString() ??
                    newRec['body']?.toString() ??
                    'تم تحديث سعر صرف الدولار المعتمد في المنصة.';
                if (!_shouldTriggerAlert('usd_rate_banner_$bodyText')) return;

                final titleText = newRec['title_ar']?.toString() ??
                    newRec['title']?.toString() ??
                    '💱 تحديث سعر صرف الدولار المعتمد';

                NotificationSoundService.play();
                LocalNotificationService.instance.showOrderNotification(
                  orderId: 'usd_rate_${DateTime.now().millisecondsSinceEpoch}',
                  title: titleText,
                  body: bodyText,
                  payload: 'merchant_usd_rate',
                );

                if (mounted) {
                  final isRtl = Directionality.of(context) == TextDirection.rtl;
                  AppNotification.showBanner(
                    context,
                    title: titleText,
                    message: bodyText,
                    icon: Icons.currency_exchange_rounded,
                    gradientColors: const [Color(0xFF065F46), Color(0xFF059669)],
                    glowColor: const Color(0xFF10B981),
                    actionLabel: isRtl ? 'سجل الدولار 📅' : 'Rate History',
                    onAction: () => UsdRateHistoryDialog.show(context),
                    duration: const Duration(milliseconds: 6500),
                  );
                }
                return;
              }

              NotificationSoundService.play();
              ref.invalidate(merchantOrdersProvider);

              final title = newRec['title_ar']?.toString() ??
                  newRec['title']?.toString() ??
                  '🔔 إشعار جديد في مقاييس!';
              final body = newRec['body_ar']?.toString() ??
                  newRec['body']?.toString() ??
                  'يوجد تحديث جديد في متجرك، اضغط للمعاينة.';

              LocalNotificationService.instance.showOrderNotification(
                orderId: relatedId,
                title: title,
                body: body,
                payload: 'merchant_order:$relatedId',
              );

              if (mounted) {
                final isRtl = Directionality.of(context) == TextDirection.rtl;
                if (type == 'new_order') {
                  AppNotification.showNewOrder(
                    context,
                    orderId: relatedId,
                    onTap: () => widget.navigationShell.goBranch(3),
                  );
                } else if (type.startsWith('admin_broadcast')) {
                  final isWarning = type == 'admin_broadcast_warning';
                  final isPromo = type == 'admin_broadcast_promo';
                  AppNotification.showBanner(
                    context,
                    title: title,
                    message: body,
                    icon: isWarning
                        ? Icons.warning_amber_rounded
                        : (isPromo ? Icons.auto_awesome_rounded : Icons.campaign_rounded),
                    gradientColors: isWarning
                        ? const [Color(0xFF9A3412), Color(0xFFEA580C)]
                        : (isPromo
                            ? const [Color(0xFF4C1D95), Color(0xFF7C3AED)]
                            : const [Color(0xFF1E3A8A), Color(0xFF2563EB)]),
                    glowColor: isWarning
                        ? const Color(0xFFF97316)
                        : (isPromo ? const Color(0xFF8B5CF6) : const Color(0xFF3B82F6)),
                    duration: const Duration(milliseconds: 6500),
                  );
                } else {
                  AppNotification.showBanner(
                    context,
                    title: title,
                    message: body,
                    orderId: newRec['related_id']?.toString(),
                    actionLabel: isRtl ? 'عرض' : 'View',
                    onAction: () => widget.navigationShell.goBranch(3),
                  );
                }
              }
            },
          )
          .subscribe();
    } catch (e) {
      debugPrint('Error setting up merchant global orders listener: $e');
    }
  }

  Future<void> _checkUnreadOfflineAlerts(String userId) async {
    await Future.delayed(const Duration(milliseconds: 1400));
    if (!mounted) return;
    try {
      final currentAuthUser = ref.read(authNotifierProvider).valueOrNull;
      if (currentAuthUser?.role != UserRole.merchant) return;

      final unread = await Supabase.instance.client
          .from('notifications')
          .select('id, title, body, type')
          .eq('user_id', userId)
          .eq('is_read', false)
          .order('created_at', ascending: false)
          .limit(5);

      for (final row in (unread as List)) {
        final type = row['type']?.toString() ?? '';
        final id = row['id']?.toString() ?? '';
        final title = row['title']?.toString() ?? '';
        final body = row['body']?.toString() ?? '';

        if (type == 'usd_rate_changed' && _merchantUsdRateSource == 'platform') {
          await Supabase.instance.client.from('notifications').update({'is_read': true}).eq('id', id);
          if (!_merchantUsdRateAlertsEnabled) continue;
          if (!mounted || !_shouldTriggerAlert('usd_rate_banner_$body')) return;
          final isRtl = Directionality.of(context) == TextDirection.rtl;
          NotificationSoundService.play();
          AppNotification.showBanner(
            context,
            title: title,
            message: body,
            icon: Icons.currency_exchange_rounded,
            gradientColors: const [Color(0xFF065F46), Color(0xFF059669)],
            glowColor: const Color(0xFF10B981),
            actionLabel: isRtl ? 'سجل الدولار 📈' : 'Rate History',
            onAction: () => UsdRateHistoryDialog.show(context),
            duration: const Duration(milliseconds: 6500),
          );
          return;
        } else if (type.startsWith('admin_broadcast')) {
          await Supabase.instance.client.from('notifications').update({'is_read': true}).eq('id', id);
          if (!mounted || !_shouldTriggerAlert('broadcast_banner_$id')) return;
          NotificationSoundService.play();
          AppNotification.showBanner(
            context,
            title: title,
            message: body,
            icon: Icons.campaign_rounded,
            gradientColors: const [Color(0xFF1E3A8A), Color(0xFF2563EB)],
            glowColor: const Color(0xFF3B82F6),
            duration: const Duration(milliseconds: 6500),
          );
          return;
        }
      }
    } catch (_) {}
  }

  @override
  void dispose() {
    _usdInitTimer?.cancel();
    _ordersChannel?.unsubscribe();
    if (_ordersChannel != null) {
      Supabase.instance.client.removeChannel(_ordersChannel!);
      _ordersChannel = null;
    }
    super.dispose();
  }

  void _onTap(int index) {
    FocusManager.instance.primaryFocus?.unfocus();
    if (widget.navigationShell.currentIndex != 2) {
      MerchantShell.lastNonAddBranchIndex = widget.navigationShell.currentIndex;
    }
    widget.navigationShell.goBranch(
      index,
      initialLocation: index == widget.navigationShell.currentIndex,
    );
  }

  @override
  Widget build(BuildContext context) {
    ref.listen<double>(platformUsdRateSettingProvider, (prev, next) async {
      if (!_usdRateListenerReady) return;
      if (prev == null || prev <= 0 || next <= 0 || (next - prev).abs() < 0.01) return;

      final currentAuthUser = ref.read(authNotifierProvider).valueOrNull;
      // STRICT MERCHANT-ONLY GUARD: Never show to Buyers
      if (currentAuthUser == null || currentAuthUser.role != UserRole.merchant) return;

      await _refreshMerchantUsdRateSource(currentAuthUser.id);
      if (_merchantUsdRateSource != 'platform' ||
          !_merchantUsdRateAlertsEnabled ||
          !mounted ||
          !context.mounted) {
        return;
      }

      final isRtl = Directionality.of(context) == TextDirection.rtl;
      final fmtOld = NumberFormat('#,##0.##').format(prev);
      final fmtNew = NumberFormat('#,##0.##').format(next);
      final arMsg = 'تم تغيير سعر صرف الدولار في المنصة من $fmtOld ج.س إلى $fmtNew ج.س';
      final enMsg = 'Platform USD exchange rate changed from $fmtOld SDG to $fmtNew SDG';

      if (!_shouldTriggerAlert('usd_rate_banner_$arMsg')) return;

      NotificationSoundService.play();
      AppNotification.showBanner(
        context,
        title: isRtl ? '💱 تحديث سعر صرف الدولار المعتمد' : '💱 Platform USD Rate Updated',
        message: isRtl ? arMsg : enMsg,
        icon: Icons.currency_exchange_rounded,
        gradientColors: const [Color(0xFF065F46), Color(0xFF059669)],
        glowColor: const Color(0xFF10B981),
        actionLabel: isRtl ? 'إعدادات الصرف' : 'Rate Settings',
        onAction: () => context.push(RouteNames.merchantStoreSettings),
        duration: const Duration(milliseconds: 6500),
      );
    });

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
          icon: Icons.dashboard_outlined,
          selectedIcon: Icons.dashboard_rounded,
          label: context.l10n.dashboard,
        ),
        _MerchantTabItem(
          branchIndex: 1,
          icon: Icons.inventory_2_outlined,
          selectedIcon: Icons.inventory_2_rounded,
          label: context.l10n.inventory,
        ),
        _MerchantTabItem(
          branchIndex: 2,
          icon: Icons.add_box_outlined,
          selectedIcon: Icons.add_box_rounded,
          label: context.l10n.addProduct,
        ),
        _MerchantTabItem(
          branchIndex: 3,
          icon: Icons.receipt_long_outlined,
          selectedIcon: Icons.receipt_long_rounded,
          label: context.l10n.orders,
        ),
        _MerchantTabItem(
          branchIndex: 4,
          icon: Icons.settings_outlined,
          selectedIcon: Icons.settings_rounded,
          label: context.l10n.storeSettings,
        ),
        _MerchantTabItem(
          branchIndex: 5,
          icon: Icons.chat_outlined,
          selectedIcon: Icons.chat_rounded,
          label: isRtl ? 'المحادثات' : 'Chat',
          badge: unreadCount,
        ),
      ]);
    } else {
      // Store Staff: filtered by granular RBAC permissions
      if (currentUser != null && currentUser.canManageStoreOrders) {
        visibleTabs.add(
          _MerchantTabItem(
            branchIndex: 3,
            icon: Icons.receipt_long_outlined,
            selectedIcon: Icons.receipt_long_rounded,
            label: context.l10n.orders,
          ),
        );
      }

      if (currentUser != null && currentUser.canManageStoreProducts) {
        visibleTabs.add(
          _MerchantTabItem(
            branchIndex: 1,
            icon: Icons.inventory_2_outlined,
            selectedIcon: Icons.inventory_2_rounded,
            label: context.l10n.inventory,
          ),
        );
        visibleTabs.add(
          _MerchantTabItem(
            branchIndex: 2,
            icon: Icons.add_box_outlined,
            selectedIcon: Icons.add_box_rounded,
            label: context.l10n.addProduct,
          ),
        );
      }

      if (currentUser != null && currentUser.canChatStoreCustomers) {
        visibleTabs.add(
          _MerchantTabItem(
            branchIndex: 5,
            icon: Icons.chat_outlined,
            selectedIcon: Icons.chat_rounded,
            label: isRtl ? 'المحادثات' : 'Chat',
            badge: unreadCount,
          ),
        );
      }
    }

    // Safety fallback: if no tabs matched, fallback to Orders branch (3)
    if (visibleTabs.isEmpty) {
      visibleTabs.add(
        _MerchantTabItem(
          branchIndex: 3,
          icon: Icons.receipt_long_outlined,
          selectedIcon: Icons.receipt_long_rounded,
          label: context.l10n.orders,
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

    if (widget.navigationShell.currentIndex != 2) {
      MerchantShell.lastNonAddBranchIndex = widget.navigationShell.currentIndex;
    }

    final isDark = Theme.of(context).brightness == Brightness.dark;
    final activeColor = AppColors.merchantAccent;
    final inactiveColor = isDark ? const Color(0xFF94A3B8) : AppColors.textSecondary;

    void handleShellBack() {
      FocusManager.instance.primaryFocus?.unfocus();
      if (isStaff && selectedBarIndex != 0) {
        final fallback = permittedBranchIndices.contains(MerchantShell.lastNonAddBranchIndex) &&
                MerchantShell.lastNonAddBranchIndex != widget.navigationShell.currentIndex
            ? MerchantShell.lastNonAddBranchIndex
            : visibleTabs.first.branchIndex;
        widget.navigationShell.goBranch(fallback);
      } else if (!isStaff && widget.navigationShell.currentIndex != 0) {
        final fallback = (widget.navigationShell.currentIndex == 2 &&
                MerchantShell.lastNonAddBranchIndex != 2)
            ? MerchantShell.lastNonAddBranchIndex
            : 0;
        _onTap(fallback);
      }
    }

    return PopScope(
      canPop: isStaff ? selectedBarIndex == 0 : widget.navigationShell.currentIndex == 0,
      onPopInvokedWithResult: (didPop, result) {
        if (!didPop) {
          handleShellBack();
        }
      },
      child: Theme(
        data: AppTheme.merchantTheme,
        child: Scaffold(
          appBar: isStaff
              ? PreferredSize(
                  preferredSize: const Size.fromHeight(38),
                  child: Container(
                    color: AppColors.merchantAccent.withValues(alpha: 0.08),
                    child: SafeArea(
                      bottom: false,
                      child: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
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
                    ),
                  ),
                )
              : null,
          body: Listener(
            onPointerDown: (event) {
              final screenW = MediaQuery.of(context).size.width;
              final x = event.position.dx;
              // Detect swipe starting near either screen edge (right edge for RTL or left edge for LTR/iOS)
              if (x <= 44 || x >= screenW - 44) {
                _edgeSwipeStartX = x;
                _edgeSwipeDeltaX = 0.0;
              } else {
                _edgeSwipeStartX = null;
                _edgeSwipeDeltaX = 0.0;
              }
            },
            onPointerMove: (event) {
              if (_edgeSwipeStartX == null) return;
              _edgeSwipeDeltaX = event.position.dx - _edgeSwipeStartX!;
            },
            onPointerUp: (_) {
              if (_edgeSwipeStartX != null) {
                final screenW = MediaQuery.of(context).size.width;
                final startedRight = _edgeSwipeStartX! >= screenW - 44;
                final startedLeft = _edgeSwipeStartX! <= 44;
                final swipedFromRightEdge = startedRight && _edgeSwipeDeltaX < -65;
                final swipedFromLeftEdge = startedLeft && _edgeSwipeDeltaX > 65;

                if (swipedFromRightEdge || swipedFromLeftEdge) {
                  final canGoBackInShell = isStaff
                      ? selectedBarIndex != 0
                      : widget.navigationShell.currentIndex != 0;
                  if (canGoBackInShell && !Navigator.of(context).canPop()) {
                    handleShellBack();
                  }
                }
              }
              _edgeSwipeStartX = null;
              _edgeSwipeDeltaX = 0.0;
            },
            child: widget.navigationShell,
          ),
          bottomNavigationBar: Container(
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF0F172A) : Colors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: isDark ? 0.25 : 0.05),
                  blurRadius: 12,
                  offset: const Offset(0, -2),
                ),
              ],
              border: Border(
                top: BorderSide(
                  color: isDark ? const Color(0xFF1E293B) : const Color(0xFFE2E8F0),
                  width: 0.8,
                ),
              ),
            ),
            child: SafeArea(
              top: false,
              child: MediaQuery(
                data: MediaQuery.of(context).copyWith(
                  textScaler: MediaQuery.of(context).textScaler.clamp(
                    minScaleFactor: 0.85,
                    maxScaleFactor: 1.0,
                  ),
                ),
                child: SizedBox(
                  height: 50,
                  child: Row(
                    children: List.generate(visibleTabs.length, (barIndex) {
                      final tab = visibleTabs[barIndex];
                      final isSelected = selectedBarIndex == barIndex;
                      final iconWidget = Icon(
                        isSelected ? tab.selectedIcon : tab.icon,
                        size: isSelected ? 21 : 20,
                        color: isSelected ? activeColor : inactiveColor,
                      );
                      return Expanded(
                        child: InkWell(
                          onTap: () {
                            FocusManager.instance.primaryFocus?.unfocus();
                            final targetBranch = tab.branchIndex;
                            if (widget.navigationShell.currentIndex != 2) {
                              MerchantShell.lastNonAddBranchIndex =
                                  widget.navigationShell.currentIndex;
                            }
                            widget.navigationShell.goBranch(
                              targetBranch,
                              initialLocation: targetBranch == widget.navigationShell.currentIndex,
                            );
                          },
                          borderRadius: BorderRadius.circular(10),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              AnimatedContainer(
                                duration: const Duration(milliseconds: 180),
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 2),
                                decoration: BoxDecoration(
                                  color: isSelected
                                      ? activeColor.withValues(alpha: 0.12)
                                      : Colors.transparent,
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: tab.badge > 0
                                    ? Badge(
                                        label: Text(
                                          tab.badge > 99 ? '99+' : tab.badge.toString(),
                                          style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.bold),
                                        ),
                                        child: iconWidget,
                                      )
                                    : iconWidget,
                              ),
                              const SizedBox(height: 2),
                              Text(
                                tab.label,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: TextStyle(
                                  fontSize: 10,
                                  height: 1.1,
                                  fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
                                  color: isSelected ? activeColor : inactiveColor,
                                ),
                              ),
                            ],
                          ),
                        ),
                      );
                    }),
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
