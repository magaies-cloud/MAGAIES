import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/core/extensions/context_extensions.dart';
import 'package:magaiees_marketplace/core/utils/app_notification.dart';
import 'package:magaiees_marketplace/core/utils/notification_sound_service.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/core/constants/app_colors.dart';
import 'package:magaiees_marketplace/features/buyer/cart/application/cart_provider.dart';
import 'package:magaiees_marketplace/features/buyer/orders/presentation/screens/buyer_orders_screen.dart';
import 'package:magaiees_marketplace/features/chat/application/chat_provider.dart';

/// Shell scaffold for the Buyer mode with bottom navigation and
/// global real-time orders listener for instant alert tones and banners.
class BuyerShell extends ConsumerStatefulWidget {
  const BuyerShell({
    required this.navigationShell,
    super.key,
  });

  final StatefulNavigationShell navigationShell;

  @override
  ConsumerState<BuyerShell> createState() => _BuyerShellState();
}

class _BuyerShellState extends ConsumerState<BuyerShell> {
  RealtimeChannel? _buyerOrdersChannel;

  @override
  void initState() {
    super.initState();
    _initBuyerOrdersListener();
  }

  Future<void> _initBuyerOrdersListener() async {
    final user = Supabase.instance.client.auth.currentUser;
    if (user == null) return;

    try {
      await _buyerOrdersChannel?.unsubscribe();

      final channelName = 'buyer_global_orders_${user.id}_${DateTime.now().millisecondsSinceEpoch}';
      _buyerOrdersChannel = Supabase.instance.client
          .channel(channelName)
          .onPostgresChanges(
            event: PostgresChangeEvent.all,
            schema: 'public',
            table: 'orders',
            callback: (payload) async {
              final newRec = payload.newRecord;
              String? rowBuyerId = newRec['buyer_id']?.toString();

              if (rowBuyerId == null && newRec['id'] != null) {
                try {
                  final lookup = await Supabase.instance.client
                      .from('orders')
                      .select('buyer_id')
                      .eq('id', newRec['id'])
                      .maybeSingle();
                  rowBuyerId = lookup?['buyer_id']?.toString();
                } catch (_) {}
              }

              if (!mounted) return;

              if (rowBuyerId == user.id) {
                // Play notification tone
                NotificationSoundService.play();

                // Invalidate buyer orders provider so UI refreshes
                ref.invalidate(buyerOrdersProvider);

                if (mounted) {
                  final isRtl = Directionality.of(context) == TextDirection.rtl;
                  final buyerApproved = newRec['buyer_approved_fees'];
                  final transportFee = newRec['transport_fee'];
                  final status = newRec['status']?.toString();
                  final orderId = newRec['id']?.toString();

                  final notes = newRec['notes']?.toString() ?? '';
                  final paymentStatus = newRec['payment_status']?.toString();
                  final isRejected = paymentStatus == 'unpaid' && notes.contains('rejection_reason:');

                  if (isRejected) {
                    final rawReason = notes.split('rejection_reason:').last.split('\n').first.trim();
                    final reason = rawReason.isNotEmpty ? rawReason : (isRtl ? 'الإشعار غير واضح أو البيانات غير مطابقة' : 'Invalid or unverified receipt');
                    AppNotification.showRejected(
                      context,
                      orderId: orderId,
                      reason: reason,
                      onTap: () => widget.navigationShell.goBranch(2),
                    );
                  } else if (status == 'processing') {
                    AppNotification.showProcessing(
                      context,
                      orderId: orderId,
                      onTap: () => widget.navigationShell.goBranch(2),
                    );
                  } else if (status == 'shipped') {
                    AppNotification.showShipped(
                      context,
                      orderId: orderId,
                      message: isRtl ? 'طلبك الآن في طريق إليك مع شركة الشحن!' : 'Your order is on the way!',
                      onTap: () => widget.navigationShell.goBranch(2),
                    );
                  } else if (status == 'delivered') {
                    AppNotification.showDelivered(
                      context,
                      orderId: orderId,
                      message: isRtl ? 'تم تسليم الطلب بنجاح! شكراً لتعاملك معنا.' : 'Order delivered successfully! Thank you.',
                      onTap: () => widget.navigationShell.goBranch(2),
                    );
                  } else if (status == 'cancelled') {
                    AppNotification.showCancelled(
                      context,
                      orderId: orderId,
                      onTap: () => widget.navigationShell.goBranch(2),
                    );
                  } else if (buyerApproved == false && transportFee != null) {
                    AppNotification.showFeesSet(
                      context,
                      orderId: orderId,
                      onTap: () => widget.navigationShell.goBranch(2),
                    );
                  } else if (paymentStatus == 'paid' || paymentStatus == 'partial') {
                    AppNotification.showPaymentApproved(
                      context,
                      orderId: orderId,
                      message: isRtl
                          ? (paymentStatus == 'paid' ? 'تم اعتماد الدفع بالكامل بنجاح!' : 'تم اعتماد دفعة مالية جزئية للطلب.')
                          : (paymentStatus == 'paid' ? 'Full payment verified and approved!' : 'Partial payment verified and approved.'),
                      onTap: () => widget.navigationShell.goBranch(2),
                    );
                  } else {
                    AppNotification.showBanner(
                      context,
                      title: isRtl ? '📦 تحديث بخصوص طلبك' : '📦 Order Update',
                      message: isRtl ? 'تم تحديث حالة طلبك، اضغط للمراجعة والتفاصيل.' : 'Your order has been updated.',
                      orderId: orderId,
                      actionLabel: isRtl ? 'عرض' : 'View',
                      onAction: () => widget.navigationShell.goBranch(2),
                    );
                  }
                }
              }
            },
          )
          .subscribe();
    } catch (e) {
      debugPrint('Error setting up buyer global orders listener: $e');
    }
  }

  @override
  void dispose() {
    _buyerOrdersChannel?.unsubscribe();
    if (_buyerOrdersChannel != null) {
      Supabase.instance.client.removeChannel(_buyerOrdersChannel!);
      _buyerOrdersChannel = null;
    }
    super.dispose();
  }

  void _onTap(int index) {
    widget.navigationShell.goBranch(
      index,
      initialLocation: index == 1 || index == widget.navigationShell.currentIndex,
    );
  }

  @override
  Widget build(BuildContext context) {
    ref.listen(authNotifierProvider, (prev, next) {
      if (next.valueOrNull != null) {
        _initBuyerOrdersListener();
      } else {
        _buyerOrdersChannel?.unsubscribe();
        if (_buyerOrdersChannel != null) {
          Supabase.instance.client.removeChannel(_buyerOrdersChannel!);
          _buyerOrdersChannel = null;
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
    final cartItemCount = ref.watch(cartNotifierProvider).items.length;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final activeColor = isDark ? AppColors.primaryLight : AppColors.primary;
    final inactiveColor = isDark ? const Color(0xFF94A3B8) : AppColors.textSecondary;

    final navItems = <({IconData icon, IconData selectedIcon, String label, int badge})>[
      (
        icon: Icons.home_outlined,
        selectedIcon: Icons.home_rounded,
        label: context.isRtl ? 'الرئيسية' : 'Home',
        badge: 0,
      ),
      (
        icon: Icons.shopping_cart_outlined,
        selectedIcon: Icons.shopping_cart_rounded,
        label: context.isRtl ? 'السلة' : 'Cart',
        badge: cartItemCount,
      ),
      (
        icon: Icons.receipt_long_outlined,
        selectedIcon: Icons.receipt_long_rounded,
        label: context.isRtl ? 'الطلبات' : 'Orders',
        badge: 0,
      ),
      (
        icon: Icons.chat_bubble_outline_rounded,
        selectedIcon: Icons.chat_bubble_rounded,
        label: context.isRtl ? 'المحادثات' : 'Chat',
        badge: unreadCount,
      ),
      (
        icon: Icons.person_outline_rounded,
        selectedIcon: Icons.person_rounded,
        label: context.isRtl ? 'حسابي' : 'Account',
        badge: 0,
      ),
    ];

    return PopScope(
      canPop: widget.navigationShell.currentIndex == 0,
      onPopInvokedWithResult: (didPop, result) {
        if (!didPop && widget.navigationShell.currentIndex != 0) {
          _onTap(0);
        }
      },
      child: Scaffold(
        body: widget.navigationShell,
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
                height: 52,
                child: Row(
                  children: List.generate(navItems.length, (index) {
                    final item = navItems[index];
                    final isSelected = widget.navigationShell.currentIndex == index;
                    final iconWidget = Icon(
                      isSelected ? item.selectedIcon : item.icon,
                      size: isSelected ? 22 : 21,
                      color: isSelected ? activeColor : inactiveColor,
                    );
                    return Expanded(
                      child: InkWell(
                        onTap: () => _onTap(index),
                        borderRadius: BorderRadius.circular(12),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            AnimatedContainer(
                              duration: const Duration(milliseconds: 180),
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 3),
                              decoration: BoxDecoration(
                                color: isSelected
                                    ? activeColor.withValues(alpha: 0.12)
                                    : Colors.transparent,
                                borderRadius: BorderRadius.circular(14),
                              ),
                              child: item.badge > 0
                                  ? Badge(
                                      label: Text(
                                        item.badge > 99 ? '99+' : item.badge.toString(),
                                        style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.bold),
                                      ),
                                      child: iconWidget,
                                    )
                                  : iconWidget,
                            ),
                            const SizedBox(height: 2),
                            Text(
                              item.label,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: TextStyle(
                                fontSize: 10.5,
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
    );
  }
}
