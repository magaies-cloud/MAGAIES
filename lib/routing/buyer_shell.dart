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
            color: Colors.white,
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.05),
                blurRadius: 10,
                offset: const Offset(0, -3),
              ),
            ],
            border: Border(
              top: BorderSide(color: Colors.grey.shade200, width: 0.8),
            ),
          ),
          child: MediaQuery(
            data: MediaQuery.of(context).copyWith(
              textScaler: MediaQuery.of(context).textScaler.clamp(
                minScaleFactor: 0.85,
                maxScaleFactor: 1.05,
              ),
            ),
            child: NavigationBarTheme(
              data: NavigationBarThemeData(
                indicatorColor: AppColors.primary.withValues(alpha: 0.12),
                labelTextStyle: WidgetStateProperty.resolveWith<TextStyle>((states) {
                  if (states.contains(WidgetState.selected)) {
                    return const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: AppColors.primary,
                    );
                  }
                  return const TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                    color: AppColors.textSecondary,
                  );
                }),
                iconTheme: WidgetStateProperty.resolveWith<IconThemeData>((states) {
                  if (states.contains(WidgetState.selected)) {
                    return const IconThemeData(
                      size: 25,
                      color: AppColors.primary,
                    );
                  }
                  return const IconThemeData(
                    size: 24,
                    color: AppColors.textSecondary,
                  );
                }),
              ),
              child: NavigationBar(
                selectedIndex: widget.navigationShell.currentIndex,
                onDestinationSelected: _onTap,
                backgroundColor: Colors.white,
                elevation: 0,
                destinations: [
                  NavigationDestination(
                    icon: const Icon(Icons.home_outlined),
                    selectedIcon: const Icon(Icons.home_rounded),
                    label: context.isRtl ? 'الرئيسية' : 'Home',
                  ),
                  NavigationDestination(
                    icon: Badge(
                      isLabelVisible: cartItemCount > 0,
                      label: Text(cartItemCount.toString()),
                      child: const Icon(Icons.shopping_cart_outlined),
                    ),
                    selectedIcon: Badge(
                      isLabelVisible: cartItemCount > 0,
                      label: Text(cartItemCount.toString()),
                      child: const Icon(Icons.shopping_cart_rounded),
                    ),
                    label: context.isRtl ? 'السلة' : 'Cart',
                  ),
                  NavigationDestination(
                    icon: const Icon(Icons.receipt_long_outlined),
                    selectedIcon: const Icon(Icons.receipt_long_rounded),
                    label: context.isRtl ? 'الطلبات' : 'Orders',
                  ),
                  NavigationDestination(
                    icon: Badge(
                      isLabelVisible: unreadCount > 0,
                      label: Text(unreadCount.toString()),
                      child: const Icon(Icons.chat_bubble_outline_rounded),
                    ),
                    selectedIcon: Badge(
                      isLabelVisible: unreadCount > 0,
                      label: Text(unreadCount.toString()),
                      child: const Icon(Icons.chat_bubble_rounded),
                    ),
                    label: context.isRtl ? 'المحادثات' : 'Chat',
                  ),
                  NavigationDestination(
                    icon: const Icon(Icons.person_outline_rounded),
                    selectedIcon: const Icon(Icons.person_rounded),
                    label: context.isRtl ? 'حسابي' : 'My Account',
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
