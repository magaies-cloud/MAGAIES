import 'dart:async';
import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:go_router/go_router.dart';
import 'package:magaiees_marketplace/core/services/call_kit_service.dart';
import 'package:magaiees_marketplace/routing/app_router.dart';
import 'package:magaiees_marketplace/routing/route_names.dart';

@pragma('vm:entry-point')
void notificationTapBackground(NotificationResponse notificationResponse) {
  debugPrint('Notification tapped in background: ${notificationResponse.payload}');
}

/// Service that manages local notifications on Android / iOS devices.
/// Displays heads-up banner notifications, custom Magaiees sound and vibration.
class LocalNotificationService {
  LocalNotificationService._();
  static final LocalNotificationService instance = LocalNotificationService._();

  final FlutterLocalNotificationsPlugin _notificationsPlugin =
      FlutterLocalNotificationsPlugin();

  static const String orderChannelId = 'magaiees_orders_channel';
  static const String orderChannelName = 'طلبات السيطرة الفورية';
  static const String orderChannelDesc =
      'إشعارات وتنبيهات فورية بالصوت والاهتزاز عند وصول طلبات جديدة للمتجر';

  static const String generalChannelId = 'magaiees_general_channel';
  static const String generalChannelName = 'تنبيهات منصة السيطرة';
  static const String generalChannelDesc = 'إشعارات النظام العامة والتحديثات';

  static const String chatChannelId = 'magaiees_chat_channel';
  static const String chatChannelName = 'رسائل المحادثات';
  static const String chatChannelDesc = 'إشعار فوري عند وصول رسالة جديدة في المحادثات';

  static const String ongoingCallChannelId = 'magaiees_ongoing_call_channel';
  static const String ongoingCallChannelName = 'المكالمات الصوتية النشطة';
  static const String ongoingCallChannelDesc =
      'إشعار مستمر في شريط الإشعارات أثناء إجراء مكالمة صوتية عبر السيطرة';
  static const int ongoingCallNotificationId = 999001;

  bool _initialized = false;

  /// Initializes the plugin and channels. Pass [requestPermission] = false from
  /// background isolates where permission dialogs cannot be shown.
  Future<void> init({bool requestPermission = true}) async {
    if (_initialized) return;

    const androidSettings = AndroidInitializationSettings('@mipmap/ic_launcher');
    const iosSettings = DarwinInitializationSettings(
      requestAlertPermission: true,
      requestBadgePermission: true,
      requestSoundPermission: true,
    );

    const initSettings = InitializationSettings(
      android: androidSettings,
      iOS: iosSettings,
    );

    await _notificationsPlugin.initialize(
      initSettings,
      onDidReceiveNotificationResponse: _onNotificationTapped,
      onDidReceiveBackgroundNotificationResponse: notificationTapBackground,
    );

    final androidPlugin = _notificationsPlugin
        .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>();

    if (androidPlugin != null) {
      try {
        final orderChannel = AndroidNotificationChannel(
          orderChannelId,
          orderChannelName,
          description: orderChannelDesc,
          importance: Importance.max,
          playSound: true,
          sound: const RawResourceAndroidNotificationSound('magaiees_notify'),
          enableVibration: true,
          vibrationPattern: Int64List.fromList([0, 500, 250, 500, 250, 700]),
        );

        final generalChannel = AndroidNotificationChannel(
          generalChannelId,
          generalChannelName,
          description: generalChannelDesc,
          importance: Importance.high,
          playSound: true,
          sound: const RawResourceAndroidNotificationSound('magaiees_notify'),
          enableVibration: true,
        );

        await androidPlugin.createNotificationChannel(orderChannel);
        await androidPlugin.createNotificationChannel(generalChannel);
        await androidPlugin.createNotificationChannel(const AndroidNotificationChannel(
          chatChannelId,
          chatChannelName,
          description: chatChannelDesc,
          importance: Importance.high,
          playSound: true,
          sound: RawResourceAndroidNotificationSound('magaiees_notify'),
          enableVibration: true,
        ));
        await androidPlugin.createNotificationChannel(const AndroidNotificationChannel(
          ongoingCallChannelId,
          ongoingCallChannelName,
          description: ongoingCallChannelDesc,
          importance: Importance.max,
          playSound: false,
          enableVibration: false,
          showBadge: true,
        ));
      } catch (e) {
        debugPrint('Custom notification channel sound fallback: $e');
        try {
          await androidPlugin.createNotificationChannel(const AndroidNotificationChannel(
            orderChannelId,
            orderChannelName,
            description: orderChannelDesc,
            importance: Importance.max,
            playSound: true,
            enableVibration: true,
          ));
          await androidPlugin.createNotificationChannel(const AndroidNotificationChannel(
            generalChannelId,
            generalChannelName,
            description: generalChannelDesc,
            importance: Importance.high,
            playSound: true,
            enableVibration: true,
          ));
          await androidPlugin.createNotificationChannel(const AndroidNotificationChannel(
            chatChannelId,
            chatChannelName,
            description: chatChannelDesc,
            importance: Importance.high,
            playSound: true,
            enableVibration: true,
          ));
          await androidPlugin.createNotificationChannel(const AndroidNotificationChannel(
            ongoingCallChannelId,
            ongoingCallChannelName,
            description: ongoingCallChannelDesc,
            importance: Importance.max,
            playSound: false,
            enableVibration: false,
            showBadge: true,
          ));
        } catch (_) {}
      }

      if (requestPermission) {
        unawaited(
          androidPlugin
              .requestNotificationsPermission()
              .timeout(const Duration(seconds: 10), onTimeout: () => false)
              .catchError((_) => false),
        );
      }
    }

    _initialized = true;

    try {
      final launch = await _notificationsPlugin.getNotificationAppLaunchDetails();
      if (launch?.didNotificationLaunchApp == true) {
        final payload = launch!.notificationResponse?.payload;
        Future.delayed(const Duration(seconds: 2), () => _navigateToTarget(payload));
      }
    } catch (_) {}
  }

  static void _onNotificationTapped(NotificationResponse response) {
    final payload = response.payload;
    debugPrint('Local notification tapped with payload: $payload');
    _navigateToTarget(payload);
  }

  static void _navigateToTarget(String? payload) {
    try {
      if (payload != null && payload.startsWith('call:')) {
        // Already in call screen or returning to foreground
        return;
      }
      if (payload != null && payload.startsWith('incoming_call:')) {
        final parts = payload.substring('incoming_call:'.length).split('|');
        if (parts.length >= 4) {
          CallKitService.handleNotificationAccept(
            AcceptedCall(
              inviteId: parts[0],
              roomId: parts[1],
              callerId: parts[2],
              callerName: parts.sublist(3).join('|'),
            ),
          );
          return;
        }
      }
      final navState = rootNavigatorKey.currentState;
      if (navState == null || !navState.mounted) return;
      final router = GoRouter.of(navState.context);
      if (payload != null && payload.startsWith('chat:')) {
        final roomId = payload.substring(5);
        final loc = router.routerDelegate.currentConfiguration.uri.path;
        final base = loc.startsWith('/merchant') ? RouteNames.merchantChat : RouteNames.buyerChat;
        router.go(roomId.isEmpty ? base : '$base/room/$roomId');
      } else {
        router.go(RouteNames.merchantOrders);
      }
    } catch (e) {
      debugPrint('Error navigating after notification tap: $e');
    }
  }

  static final Set<int> _incomingCallNotifIds = <int>{};

  Future<void> showIncomingCallAlertNotification({
    required String inviteId,
    required String roomId,
    required String callerName,
    required String callerId,
  }) async {
    try {
      if (!_initialized) await init(requestPermission: false);
      final int notifId = (inviteId.hashCode.abs() % 50000) + 800000;
      _incomingCallNotifIds.add(notifId);

      final title = '📞 مكالمة صوتية واردة من $callerName';
      const body = 'انقر للرد وفتح المكالمة الصوتية عبر السيطرة';

      final androidDetails = AndroidNotificationDetails(
        orderChannelId,
        orderChannelName,
        channelDescription: orderChannelDesc,
        importance: Importance.max,
        priority: Priority.max,
        playSound: true,
        sound: const RawResourceAndroidNotificationSound('magaiees_notify'),
        enableVibration: true,
        vibrationPattern: Int64List.fromList([0, 600, 300, 600, 300, 800]),
        styleInformation: BigTextStyleInformation(
          body,
          contentTitle: title,
          summaryText: 'مكالمة واردة',
        ),
        icon: '@mipmap/ic_launcher',
        color: const Color(0xFF16A34A),
        category: AndroidNotificationCategory.call,
        ticker: title,
      );

      const iosDetails = DarwinNotificationDetails(
        presentAlert: true,
        presentBadge: true,
        presentSound: true,
      );

      await _notificationsPlugin.show(
        notifId,
        title,
        body,
        NotificationDetails(android: androidDetails, iOS: iosDetails),
        payload: 'incoming_call:$inviteId|$roomId|$callerId|$callerName',
      );
    } catch (e) {
      debugPrint('Error showing incoming call alert notification: $e');
    }
  }

  Future<void> cancelIncomingCallAlertNotification(String inviteId) async {
    try {
      final int notifId = (inviteId.hashCode.abs() % 50000) + 800000;
      _incomingCallNotifIds.remove(notifId);
      await _notificationsPlugin.cancel(notifId);
    } catch (_) {}
  }

  Future<void> cancelAllIncomingCallAlerts() async {
    try {
      for (final id in _incomingCallNotifIds.toList()) {
        await _notificationsPlugin.cancel(id);
      }
      _incomingCallNotifIds.clear();
    } catch (_) {}
  }

  /// Displays a persistent ongoing notification in the Android notification shade
  /// while a voice call is active (for Foreground Service / VoIP compliance).
  Future<void> showOngoingCallNotification({
    required String peerName,
    String? statusText,
  }) async {
    try {
      if (!_initialized) {
        await init(requestPermission: true);
      } else {
        final androidPlugin = _notificationsPlugin
            .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>();
        if (androidPlugin != null) {
          final enabled = await androidPlugin.areNotificationsEnabled() ?? false;
          if (!enabled) {
            await androidPlugin
                .requestNotificationsPermission()
                .timeout(const Duration(seconds: 5), onTimeout: () => false)
                .catchError((_) => false);
          }
        }
      }

      final cleanPeer = peerName.trim().isNotEmpty ? peerName.trim() : 'مستخدم السيطرة';
      final title = '📞 مكالمة صوتية جارية - السيطرة ($cleanPeer)';
      final body = statusText ??
          'مكالمة صوتية نشطة عبر الإنترنت (VoIP) • الميكروفون يعمل في الخلفية';

      final androidDetails = AndroidNotificationDetails(
        ongoingCallChannelId,
        ongoingCallChannelName,
        channelDescription: ongoingCallChannelDesc,
        importance: Importance.max,
        priority: Priority.max,
        ongoing: true,
        autoCancel: false,
        usesChronometer: true,
        when: DateTime.now().millisecondsSinceEpoch,
        showWhen: true,
        playSound: false,
        enableVibration: false,
        category: AndroidNotificationCategory.call,
        visibility: NotificationVisibility.public,
        icon: '@mipmap/ic_launcher',
        color: const Color(0xFF16A34A),
        colorized: true,
        styleInformation: BigTextStyleInformation(
          body,
          contentTitle: title,
          summaryText: 'مكالمة صوتية نشطة',
        ),
        ticker: 'مكالمة صوتية جارية عبر السيطرة',
      );

      const iosDetails = DarwinNotificationDetails(
        presentAlert: true,
        presentBadge: false,
        presentSound: false,
      );

      await _notificationsPlugin.show(
        ongoingCallNotificationId,
        title,
        body,
        NotificationDetails(android: androidDetails, iOS: iosDetails),
        payload: 'call:ongoing',
      );
    } catch (e) {
      debugPrint('Error showing ongoing call notification: $e');
    }
  }

  /// Cancels the ongoing voice call notification when the call ends.
  Future<void> cancelOngoingCallNotification() async {
    try {
      await _notificationsPlugin.cancel(ongoingCallNotificationId);
    } catch (e) {
      debugPrint('Error cancelling ongoing call notification: $e');
    }
  }

  Future<void> showChatNotification({
    required String roomId,
    required String title,
    required String body,
  }) async {
    try {
      if (!_initialized) await init(requestPermission: false);
      final androidDetails = AndroidNotificationDetails(
        chatChannelId,
        chatChannelName,
        channelDescription: chatChannelDesc,
        importance: Importance.high,
        priority: Priority.high,
        playSound: true,
        sound: const RawResourceAndroidNotificationSound('magaiees_notify'),
        styleInformation: BigTextStyleInformation(body, contentTitle: title),
        icon: '@mipmap/ic_launcher',
        color: const Color(0xFF003366),
        category: AndroidNotificationCategory.message,
        groupKey: 'chat_$roomId',
      );
      const iosDetails = DarwinNotificationDetails(
        presentAlert: true,
        presentBadge: true,
        presentSound: true,
      );

      await _notificationsPlugin.show(
        roomId.hashCode.abs() % 100000 + 200000,
        title,
        body,
        NotificationDetails(android: androidDetails, iOS: iosDetails),
        payload: 'chat:$roomId',
      );
    } catch (e) {
      debugPrint('Error showing chat notification: $e');
    }
  }

  Future<void> showOrderNotification({
    required String orderId,
    required String title,
    required String body,
    String? payload,
  }) async {
    try {
      if (!_initialized) {
        await init();
      }

      final int notifId = (orderId.hashCode.abs()) % 100000;

      final androidDetails = AndroidNotificationDetails(
        orderChannelId,
        orderChannelName,
        channelDescription: orderChannelDesc,
        importance: Importance.max,
        priority: Priority.high,
        playSound: true,
        sound: const RawResourceAndroidNotificationSound('magaiees_notify'),
        enableVibration: true,
        vibrationPattern: Int64List.fromList([0, 500, 250, 500, 250, 700]),
        styleInformation: BigTextStyleInformation(
          body,
          contentTitle: title,
          summaryText: 'طلبية جديدة',
        ),
        icon: '@mipmap/ic_launcher',
        color: const Color(0xFF003366),
        category: AndroidNotificationCategory.reminder,
        ticker: 'طلب جديد وارد',
      );

      const iosDetails = DarwinNotificationDetails(
        presentAlert: true,
        presentBadge: true,
        presentSound: true,
        sound: 'magaiees_notify.wav',
      );

      final notificationDetails = NotificationDetails(
        android: androidDetails,
        iOS: iosDetails,
      );

      await _notificationsPlugin.show(
        notifId,
        title,
        body,
        notificationDetails,
        payload: payload ?? orderId,
      );
    } catch (e) {
      debugPrint('Error showing local order notification: $e');
    }
  }

  Future<void> showGeneralNotification({
    required String title,
    required String body,
    String? payload,
  }) async {
    try {
      if (!_initialized) {
        await init();
      }

      final int notifId = DateTime.now().millisecondsSinceEpoch % 100000;

      final androidDetails = AndroidNotificationDetails(
        generalChannelId,
        generalChannelName,
        channelDescription: generalChannelDesc,
        importance: Importance.high,
        priority: Priority.high,
        playSound: true,
        sound: const RawResourceAndroidNotificationSound('magaiees_notify'),
        enableVibration: true,
        styleInformation: BigTextStyleInformation(
          body,
          contentTitle: title,
        ),
        icon: '@mipmap/ic_launcher',
        color: const Color(0xFF003366),
      );

      const iosDetails = DarwinNotificationDetails(
        presentAlert: true,
        presentBadge: true,
        presentSound: true,
        sound: 'magaiees_notify.wav',
      );

      final notificationDetails = NotificationDetails(
        android: androidDetails,
        iOS: iosDetails,
      );

      await _notificationsPlugin.show(
        notifId,
        title,
        body,
        notificationDetails,
        payload: payload,
      );
    } catch (e) {
      debugPrint('Error showing general notification: $e');
    }
  }
}
