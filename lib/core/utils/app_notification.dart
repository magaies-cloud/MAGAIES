import 'dart:async';
import 'package:flutter/material.dart';
import 'package:magaiees_marketplace/core/utils/order_number_formatter.dart';

/// Centralized executive top notification banner system.
/// Displays animated luxury cards at the TOP of the screen,
/// replacing frail snackbars with modern, responsive banners.
class AppNotification {
  static OverlayEntry? _activeEntry;
  static Timer? _dismissTimer;

  /// Show a general top notification banner.
  static void show(
    BuildContext context,
    String message, {
    String? title,
    String? orderId,
    bool isError = false,
    bool isSuccess = false,
    IconData? icon,
    List<Color>? gradientColors,
    Color? glowColor,
    String? actionLabel,
    VoidCallback? onAction,
    Duration duration = const Duration(milliseconds: 3200),
  }) {
    _dismissCurrent();

    final overlayState = Overlay.maybeOf(context, rootOverlay: true) ?? Overlay.maybeOf(context);
    if (overlayState == null) return;

    final key = GlobalKey<_TopNotificationWidgetState>();

    _activeEntry = OverlayEntry(
      builder: (context) => _TopNotificationWidget(
        key: key,
        title: title,
        message: message,
        orderId: orderId,
        isError: isError,
        isSuccess: isSuccess,
        icon: icon,
        gradientColors: gradientColors,
        glowColor: glowColor,
        actionLabel: actionLabel,
        onAction: onAction,
        onDismissed: () {
          _dismissCurrent();
        },
      ),
    );

    overlayState.insert(_activeEntry!);

    _dismissTimer = Timer(duration, () {
      key.currentState?.dismiss();
    });
  }

  /// Show a rich card banner with custom title, message, and gradient
  static void showBanner(
    BuildContext context, {
    required String title,
    required String message,
    String? orderId,
    IconData? icon,
    List<Color>? gradientColors,
    Color? glowColor,
    String? actionLabel,
    VoidCallback? onAction,
    Duration duration = const Duration(milliseconds: 4500),
  }) {
    show(
      context,
      message,
      title: title,
      orderId: orderId,
      icon: icon,
      gradientColors: gradientColors,
      glowColor: glowColor,
      actionLabel: actionLabel,
      onAction: onAction,
      duration: duration,
    );
  }

  /// Specialized top notification for: "تم الشحن" (Shipped)
  static void showShipped(
    BuildContext context, {
    String? orderId,
    String? message,
    VoidCallback? onTap,
    String? actionLabel,
  }) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    show(
      context,
      message ?? (isRtl ? 'طلبك الآن في طريقه إليك مع شركة الشحن!' : 'Your order is on the way!'),
      title: isRtl ? '🚚 تم شحن الطلب بنجاح' : '🚚 Order Shipped',
      orderId: orderId,
      icon: Icons.local_shipping_rounded,
      gradientColors: const [Color(0xFF1E3A8A), Color(0xFF2563EB)],
      glowColor: const Color(0xFF2563EB),
      actionLabel: actionLabel ?? (isRtl ? 'عرض' : 'View'),
      onAction: onTap,
      duration: const Duration(milliseconds: 5000),
    );
  }

  /// Specialized top notification for: "تم التسليم" (Delivered)
  static void showDelivered(
    BuildContext context, {
    String? orderId,
    String? message,
    VoidCallback? onTap,
    String? actionLabel,
  }) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    show(
      context,
      message ?? (isRtl ? 'تم تأكيد استلام الشحنة بنجاح. شكراً لتعاملك معنا!' : 'Order marked as delivered successfully!'),
      title: isRtl ? '✅ تم تسليم الطلب بنجاح' : '✅ Order Delivered',
      orderId: orderId,
      icon: Icons.verified_rounded,
      gradientColors: const [Color(0xFF064E3B), Color(0xFF059669)],
      glowColor: const Color(0xFF059669),
      actionLabel: actionLabel ?? (isRtl ? 'عرض' : 'View'),
      onAction: onTap,
      duration: const Duration(milliseconds: 5500),
    );
  }

  /// Specialized top notification for: "قيد التجهيز" (Processing)
  static void showProcessing(
    BuildContext context, {
    String? orderId,
    String? message,
    VoidCallback? onTap,
    String? actionLabel,
  }) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    show(
      context,
      message ?? (isRtl ? 'تم قبول وتجهيز الطلب من قبل المتجر.' : 'Your order is being processed by the store.'),
      title: isRtl ? '📦 تم قبول وتجهيز الطلب' : '📦 Order Being Processed',
      orderId: orderId,
      icon: Icons.inventory_2_rounded,
      gradientColors: const [Color(0xFF0F766E), Color(0xFF0D9488)],
      glowColor: const Color(0xFF0D9488),
      actionLabel: actionLabel,
      onAction: onTap,
      duration: const Duration(milliseconds: 4500),
    );
  }

  /// Specialized top notification for: "رفض الإشعار" (Receipt Rejected)
  static void showRejected(
    BuildContext context, {
    String? orderId,
    required String reason,
    VoidCallback? onTap,
  }) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    show(
      context,
      isRtl
          ? 'السبب: $reason\nيرجى مراجعة تفاصيل الطلب وإعادة إرسال إشعار صحيح.'
          : 'Reason: $reason\nPlease check order details and re-upload valid receipt.',
      title: isRtl ? '❌ تم رفض إشعار التحويل' : '❌ Payment Receipt Rejected',
      orderId: orderId,
      icon: Icons.cancel_rounded,
      gradientColors: const [Color(0xFF7F1D1D), Color(0xFFDC2626)],
      glowColor: const Color(0xFFDC2626),
      actionLabel: isRtl ? 'تعديل الإشعار' : 'Edit Receipt',
      onAction: onTap,
      duration: const Duration(milliseconds: 8000),
    );
  }

  /// Specialized top notification for: "تحديد رسوم التوصيل" (Fees Set)
  static void showFeesSet(
    BuildContext context, {
    String? orderId,
    String? message,
    VoidCallback? onTap,
  }) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    show(
      context,
      message ?? (isRtl ? 'قام التاجر بتحديد رسوم الترحيل والتنزيل، يرجى الموافقة لإتمام الطلب.' : 'Merchant has specified shipping fees. Please review and approve.'),
      title: isRtl ? '🚚 تم تحديد رسوم التوصيل' : '🚚 Shipping Fees Set',
      orderId: orderId,
      icon: Icons.local_shipping_rounded,
      gradientColors: const [Color(0xFF075985), Color(0xFF0284C7)],
      glowColor: const Color(0xFF0284C7),
      actionLabel: isRtl ? 'مراجعة وتأكيد' : 'Review & Approve',
      onAction: onTap,
      duration: const Duration(milliseconds: 6000),
    );
  }

  /// Specialized top notification for: "موافقة على الرسوم" (Fees Approved)
  static void showFeesApproved(
    BuildContext context, {
    String? orderId,
    String? message,
    VoidCallback? onTap,
  }) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    show(
      context,
      message ?? (isRtl ? 'وافق المشتري على رسوم التوصيل المحددة للطلب.' : 'Buyer approved delivery fees.'),
      title: isRtl ? '✅ موافقة على رسوم التوصيل' : '✅ Fees Approved by Buyer',
      orderId: orderId,
      icon: Icons.thumb_up_alt_rounded,
      gradientColors: const [Color(0xFF065F46), Color(0xFF10B981)],
      glowColor: const Color(0xFF10B981),
      actionLabel: isRtl ? 'عرض' : 'View',
      onAction: onTap,
      duration: const Duration(milliseconds: 5000),
    );
  }

  /// Specialized top notification for: "طلب جديد وارد" (New Order for Merchant)
  static void showNewOrder(
    BuildContext context, {
    String? orderId,
    String? message,
    VoidCallback? onTap,
  }) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    show(
      context,
      message ?? (isRtl ? 'قام المشتري بإتمام طلب جديد، يرجى مراجعته وتحديد رسوم الترحيل إن لزم.' : 'A new order has arrived. Please review and set delivery fees if needed.'),
      title: isRtl ? '🔔 وصلك طلب جديد وارد!' : '🔔 New Incoming Order!',
      orderId: orderId,
      icon: Icons.add_shopping_cart_rounded,
      gradientColors: const [Color(0xFF1E1B4B), Color(0xFF4338CA)],
      glowColor: const Color(0xFF4338CA),
      actionLabel: isRtl ? 'مراجعة الطلب' : 'Review Order',
      onAction: onTap,
      duration: const Duration(milliseconds: 6000),
    );
  }

  /// Specialized top notification for: "إشعار تحويل جديد" (Receipt Received for Merchant)
  static void showReceiptReceived(
    BuildContext context, {
    String? orderId,
    String? message,
    VoidCallback? onTap,
  }) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    show(
      context,
      message ?? (isRtl ? 'قام المشتري برفع إشعار تحويل مالي، يرجى مطابقة المبلغ وتأكيده.' : 'Buyer submitted a payment receipt. Please verify and confirm.'),
      title: isRtl ? '💰 إشعار تحويل جديد للمراجعة!' : '💰 New Receipt Received!',
      orderId: orderId,
      icon: Icons.receipt_long_rounded,
      gradientColors: const [Color(0xFF064E3B), Color(0xFF0F766E)],
      glowColor: const Color(0xFF0F766E),
      actionLabel: isRtl ? 'مراجعة الدفع' : 'Verify Payment',
      onAction: onTap,
      duration: const Duration(milliseconds: 6000),
    );
  }

  /// Specialized top notification for: "تم إلغاء الطلب" (Order Cancelled)
  static void showCancelled(
    BuildContext context, {
    String? orderId,
    String? message,
    VoidCallback? onTap,
    String? actionLabel,
  }) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    show(
      context,
      message ?? (isRtl ? 'تم إلغاء الطلب من قِبل المتجر.' : 'Your order was cancelled by the store.'),
      title: isRtl ? '❌ تم إلغاء الطلب' : '❌ Order Cancelled',
      orderId: orderId,
      icon: Icons.cancel_outlined,
      gradientColors: const [Color(0xFF7F1D1D), Color(0xFFDC2626)],
      glowColor: const Color(0xFFDC2626),
      actionLabel: actionLabel ?? (isRtl ? 'عرض' : 'View'),
      onAction: onTap,
      duration: const Duration(milliseconds: 6000),
    );
  }

  /// Specialized top notification for: "تم تأكيد الدفع" (Payment Approved)
  static void showPaymentApproved(
    BuildContext context, {
    String? orderId,
    String? message,
    VoidCallback? onTap,
    String? actionLabel,
  }) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    show(
      context,
      message ?? (isRtl ? 'تم التحقق من إشعار التحويل واعتماده بنجاح من قِبل المتجر.' : 'Payment receipt verified and approved.'),
      title: isRtl ? '💰 تم اعتماد الدفع بنجاح' : '💰 Payment Approved',
      orderId: orderId,
      icon: Icons.check_circle_outline_rounded,
      gradientColors: const [Color(0xFF065F46), Color(0xFF047857)],
      glowColor: const Color(0xFF059669),
      actionLabel: actionLabel ?? (isRtl ? 'عرض' : 'View'),
      onAction: onTap,
      duration: const Duration(milliseconds: 6000),
    );
  }

  /// Show a success top notification (e.g. added to cart, saved).
  static void showSuccess(BuildContext context, String message, {String? title, IconData? icon, Duration? duration}) {
    show(
      context,
      message,
      title: title,
      isSuccess: true,
      icon: icon ?? Icons.check_circle_rounded,
      gradientColors: const [Color(0xFF065F46), Color(0xFF047857)],
      glowColor: const Color(0xFF059669),
      duration: duration ?? const Duration(milliseconds: 3200),
    );
  }

  /// Show an error top notification.
  static void showError(BuildContext context, String message, {String? title, IconData? icon, Duration? duration}) {
    show(
      context,
      message,
      title: title,
      isError: true,
      icon: icon ?? Icons.error_outline_rounded,
      gradientColors: const [Color(0xFF991B1B), Color(0xFFDC2626)],
      glowColor: const Color(0xFFDC2626),
      duration: duration ?? const Duration(milliseconds: 4000),
    );
  }

  /// Show an informational top notification.
  static void showInfo(BuildContext context, String message, {String? title, IconData? icon, Duration? duration}) {
    show(
      context,
      message,
      title: title,
      icon: icon ?? Icons.info_outline_rounded,
      gradientColors: const [Color(0xFF1E293B), Color(0xFF334155)],
      glowColor: const Color(0xFF334155),
      duration: duration ?? const Duration(milliseconds: 3500),
    );
  }

  static void _dismissCurrent() {
    _dismissTimer?.cancel();
    _dismissTimer = null;
    try {
      _activeEntry?.remove();
    } catch (_) {}
    _activeEntry = null;
  }
}

class _TopNotificationWidget extends StatefulWidget {
  final String? title;
  final String message;
  final String? orderId;
  final bool isError;
  final bool isSuccess;
  final IconData? icon;
  final List<Color>? gradientColors;
  final Color? glowColor;
  final String? actionLabel;
  final VoidCallback? onAction;
  final VoidCallback onDismissed;

  const _TopNotificationWidget({
    super.key,
    this.title,
    required this.message,
    this.orderId,
    this.isError = false,
    this.isSuccess = false,
    this.icon,
    this.gradientColors,
    this.glowColor,
    this.actionLabel,
    this.onAction,
    required this.onDismissed,
  });

  @override
  State<_TopNotificationWidget> createState() => _TopNotificationWidgetState();
}

class _TopNotificationWidgetState extends State<_TopNotificationWidget>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;
  late final Animation<Offset> _offsetAnimation;
  late final Animation<double> _fadeAnimation;
  late final Animation<double> _scaleAnimation;
  bool _isDismissing = false;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 380),
      reverseDuration: const Duration(milliseconds: 260),
    );

    _offsetAnimation = Tween<Offset>(
      begin: const Offset(0, -1.25),
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _controller,
      curve: Curves.easeOutCubic,
      reverseCurve: Curves.easeInCubic,
    ));

    _fadeAnimation = CurvedAnimation(
      parent: _controller,
      curve: Curves.easeOut,
      reverseCurve: Curves.easeIn,
    );

    _scaleAnimation = Tween<double>(begin: 0.94, end: 1.0).animate(
      CurvedAnimation(parent: _controller, curve: Curves.easeOutBack),
    );

    _controller.forward();
  }

  void dismiss() {
    if (_isDismissing) return;
    _isDismissing = true;
    if (mounted) {
      _controller.reverse().then((_) {
        widget.onDismissed();
      });
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    List<Color> effectiveGradient = widget.gradientColors ?? const [Color(0xFF1E293B), Color(0xFF0F172A)];
    IconData effectiveIcon = widget.icon ?? Icons.notifications_active_rounded;
    Color effectiveGlow = widget.glowColor ?? effectiveGradient.last;

    if (widget.isError) {
      effectiveGradient = const [Color(0xFF991B1B), Color(0xFFDC2626)];
      effectiveIcon = widget.icon ?? Icons.error_outline_rounded;
      effectiveGlow = const Color(0xFFDC2626);
    } else if (widget.isSuccess) {
      effectiveGradient = const [Color(0xFF065F46), Color(0xFF059669)];
      effectiveIcon = widget.icon ?? Icons.check_circle_rounded;
      effectiveGlow = const Color(0xFF059669);
    }

    final hasTitle = widget.title != null && widget.title!.trim().isNotEmpty;
    final hasOrderId = widget.orderId != null && widget.orderId!.trim().isNotEmpty;
    final shortOrderId = hasOrderId
        ? OrderNumberFormatter.format(id: widget.orderId)
        : null;

    return SafeArea(
      top: true,
      bottom: false,
      child: Align(
        alignment: Alignment.topCenter,
        child: SlideTransition(
          position: _offsetAnimation,
          child: FadeTransition(
            opacity: _fadeAnimation,
            child: ScaleTransition(
              scale: _scaleAnimation,
              child: Dismissible(
                key: const Key('top_notification_dismiss'),
                direction: DismissDirection.up,
                onDismissed: (_) => widget.onDismissed(),
                child: Padding(
                  padding: const EdgeInsets.only(top: 8.0, left: 16.0, right: 16.0, bottom: 8.0),
                  child: ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: 540),
                    child: Material(
                      color: Colors.transparent,
                      child: Container(
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            colors: effectiveGradient,
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                          borderRadius: BorderRadius.circular(16),
                          boxShadow: [
                            BoxShadow(
                              color: effectiveGlow.withValues(alpha: 0.38),
                              blurRadius: 22,
                              offset: const Offset(0, 8),
                              spreadRadius: 1,
                            ),
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.25),
                              blurRadius: 8,
                              offset: const Offset(0, 3),
                            ),
                          ],
                          border: Border.all(
                            color: Colors.white.withValues(alpha: 0.22),
                            width: 1.2,
                          ),
                        ),
                        child: InkWell(
                          onTap: () {
                            if (widget.onAction != null) {
                              widget.onAction!();
                              dismiss();
                            } else {
                              dismiss();
                            }
                          },
                          borderRadius: BorderRadius.circular(16),
                          child: Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 13.0),
                            child: Row(
                              crossAxisAlignment: hasTitle ? CrossAxisAlignment.start : CrossAxisAlignment.center,
                              children: [
                                // Glowing circular icon badge
                                Container(
                                  width: 44,
                                  height: 44,
                                  decoration: BoxDecoration(
                                    color: Colors.white.withValues(alpha: 0.18),
                                    shape: BoxShape.circle,
                                    border: Border.all(
                                      color: Colors.white.withValues(alpha: 0.32),
                                      width: 1.5,
                                    ),
                                  ),
                                  child: Icon(
                                    effectiveIcon,
                                    color: Colors.white,
                                    size: 24,
                                  ),
                                ),
                                const SizedBox(width: 13),

                                // Title, Order badge, and Body Message
                                Expanded(
                                  child: Column(
                                    mainAxisSize: MainAxisSize.min,
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      if (hasTitle) ...[
                                        Row(
                                          children: [
                                            Expanded(
                                              child: Text(
                                                widget.title!,
                                                style: const TextStyle(
                                                  color: Colors.white,
                                                  fontSize: 14.5,
                                                  fontWeight: FontWeight.w800,
                                                  letterSpacing: 0.2,
                                                ),
                                                maxLines: 1,
                                                overflow: TextOverflow.ellipsis,
                                                textAlign: isRtl ? TextAlign.right : TextAlign.left,
                                              ),
                                            ),
                                            if (shortOrderId != null) ...[
                                              const SizedBox(width: 6),
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                                decoration: BoxDecoration(
                                                  color: Colors.black.withValues(alpha: 0.3),
                                                  borderRadius: BorderRadius.circular(6),
                                                  border: Border.all(
                                                    color: Colors.white.withValues(alpha: 0.2),
                                                    width: 0.8,
                                                  ),
                                                ),
                                                child: Text(
                                                  '#$shortOrderId',
                                                  style: const TextStyle(
                                                    color: Colors.white70,
                                                    fontSize: 10.5,
                                                    fontWeight: FontWeight.bold,
                                                  ),
                                                ),
                                              ),
                                            ],
                                          ],
                                        ),
                                        const SizedBox(height: 3),
                                      ],
                                      Text(
                                        widget.message,
                                        style: TextStyle(
                                          color: Colors.white.withValues(alpha: hasTitle ? 0.92 : 1.0),
                                          fontSize: hasTitle ? 12.5 : 13.5,
                                          fontWeight: hasTitle ? FontWeight.w500 : FontWeight.w600,
                                          height: 1.35,
                                        ),
                                        maxLines: 3,
                                        overflow: TextOverflow.ellipsis,
                                        textAlign: isRtl ? TextAlign.right : TextAlign.left,
                                      ),
                                    ],
                                  ),
                                ),
                                const SizedBox(width: 8),

                                // Action Button (if provided)
                                if (widget.actionLabel != null && widget.onAction != null) ...[
                                  InkWell(
                                    onTap: () {
                                      widget.onAction!();
                                      dismiss();
                                    },
                                    borderRadius: BorderRadius.circular(8),
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 11, vertical: 6),
                                      decoration: BoxDecoration(
                                        color: Colors.white.withValues(alpha: 0.25),
                                        borderRadius: BorderRadius.circular(8),
                                        border: Border.all(
                                          color: Colors.white.withValues(alpha: 0.4),
                                          width: 1,
                                        ),
                                      ),
                                      child: Text(
                                        widget.actionLabel!,
                                        style: const TextStyle(
                                          color: Colors.white,
                                          fontSize: 11.5,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 4),
                                ],

                                // Close icon button
                                InkWell(
                                  onTap: dismiss,
                                  borderRadius: BorderRadius.circular(16),
                                  child: Padding(
                                    padding: const EdgeInsets.all(4.0),
                                    child: Icon(
                                      Icons.close_rounded,
                                      size: 18,
                                      color: Colors.white.withValues(alpha: 0.7),
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
              ),
            ),
          ),
        ),
      ),
    );
  }
}