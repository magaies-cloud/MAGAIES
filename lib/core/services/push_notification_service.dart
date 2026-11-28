import 'dart:async';
import 'package:flutter/foundation.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/core/services/local_notification_service.dart';
import 'package:magaiees_marketplace/core/services/call_kit_service.dart';
import 'package:magaiees_marketplace/core/utils/notification_sound_service.dart';

/// Tracks which chat room is currently open so we don't notify for it.
class ChatPresence {
  static String? currentRoomId;
}

/// Renders an incoming FCM data message. Shared by the foreground listener and
/// the background isolate handler.
Future<void> _renderPushMessage(RemoteMessage message, {required bool foreground}) async {
  final data = message.data;
  final type = data['type']?.toString() ?? 'general';
  final resolvedTitle = data['title']?.toString() ?? message.notification?.title;
  final resolvedBody = data['body']?.toString() ?? message.notification?.body;

  switch (type) {
    case 'call':
      if (foreground) return;
      await CallKitService.showIncomingCall(
        inviteId: data['invite_id'] ?? '',
        roomId: data['room_id'] ?? '',
        callerName: data['caller_name'] ?? 'مستخدم',
        callerId: data['caller_id'] ?? '',
      );
      return;

    case 'call_cancel':
      await CallKitService.endCall(data['invite_id'] ?? '');
      return;

    case 'chat':
      final roomId = data['room_id'] ?? '';
      if (foreground && ChatPresence.currentRoomId == roomId) return;
      if (foreground) {
        NotificationSoundService.play();
      }
      await LocalNotificationService.instance.showChatNotification(
        roomId: roomId,
        title: resolvedTitle ?? '💬 رسالة جديدة',
        body: resolvedBody ?? '',
      );
      return;

    case 'new_order':
    case 'order_update':
    case 'fees_approved':
    case 'receipt_received':
      if (foreground) {
        NotificationSoundService.play();
      }
      final orderId = data['related_id'] ?? 'new_order';
      await LocalNotificationService.instance.showOrderNotification(
        orderId: orderId,
        title: resolvedTitle ?? '🔔 تحديث طلبية في مقاييس!',
        body: resolvedBody ?? 'يوجد طلب جديد أو تحديث على أحد الطلبات، اضغط للمعاينة.',
        payload: 'order:$orderId',
      );
      return;

    default:
      if (foreground) {
        NotificationSoundService.play();
      }
      await LocalNotificationService.instance.showGeneralNotification(
        title: resolvedTitle ?? 'مقاييس',
        body: resolvedBody ?? '',
        payload: 'notifications:$type',
      );
  }
}

/// Top-level background message handler for FCM.
/// Runs in a separate isolate when the app is in the background or terminated.
@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  try {
    await Firebase.initializeApp();
    // On iOS, if APNs delivered a native alert notification, the OS already
    // displays it in the notification curtain with sound — avoid duplicates.
    if (defaultTargetPlatform == TargetPlatform.iOS &&
        message.notification != null) {
      return;
    }
    await LocalNotificationService.instance.init(requestPermission: false);
    await _renderPushMessage(message, foreground: false);
  } catch (e) {
    debugPrint('Error in firebaseMessagingBackgroundHandler: $e');
  }
}

/// Manages Firebase Cloud Messaging: permissions, token sync and message handling.
class PushNotificationService {
  PushNotificationService._();
  static final PushNotificationService instance = PushNotificationService._();

  bool _initialized = false;

  Future<void> init() async {
    if (_initialized || kIsWeb) return;

    try {
      await CallKitService.init();

      if (Firebase.apps.isEmpty) {
        try {
          await Firebase.initializeApp().timeout(const Duration(seconds: 5));
        } catch (e) {
          debugPrint('Firebase not configured or timed out: $e');
          return;
        }
      }

      FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);

      final messaging = FirebaseMessaging.instance;
      try {
        await messaging
            .requestPermission(
              alert: true,
              badge: true,
              sound: true,
              provisional: false,
            )
            .timeout(const Duration(seconds: 8));
        // Keep foreground alert handled by LocalNotificationService (_renderPushMessage)
        // so foreground banners never duplicate on iOS, while background/terminated
        // alerts display natively via APNs.
        await messaging.setForegroundNotificationPresentationOptions(
          alert: false,
          badge: true,
          sound: false,
        );
      } catch (e) {
        debugPrint('FCM permission configuration notice: $e');
      }

      FirebaseMessaging.onMessage.listen((m) => _renderPushMessage(m, foreground: true));
      FirebaseMessaging.onMessageOpenedApp.listen((m) {
        final type = m.data['type']?.toString() ?? 'general';
        if (type == 'chat') {
          final roomId = m.data['room_id']?.toString() ?? '';
          LocalNotificationService.handleExternalPayload('chat:$roomId');
        } else if (type == 'new_order' ||
            type == 'order_update' ||
            type == 'fees_approved' ||
            type == 'receipt_received') {
          final orderId = m.data['related_id']?.toString() ?? 'new_order';
          LocalNotificationService.handleExternalPayload('order:$orderId');
        } else {
          LocalNotificationService.handleExternalPayload('notifications:$type');
        }
      });

      Supabase.instance.client.auth.onAuthStateChange.listen((state) {
        if (state.event == AuthChangeEvent.signedIn ||
            state.event == AuthChangeEvent.initialSession ||
            state.event == AuthChangeEvent.tokenRefreshed) {
          unawaited(syncTokenWithServer());
        }
      });
      messaging.onTokenRefresh.listen(_saveTokenToSupabase);

      unawaited(syncTokenWithServer());

      _initialized = true;
      debugPrint('PushNotificationService initialized.');
    } catch (e) {
      debugPrint('Error initializing PushNotificationService: $e');
    }
  }

  Future<void> syncTokenWithServer() async {
    try {
      if (Firebase.apps.isEmpty) return;
      final messaging = FirebaseMessaging.instance;

      // On iOS, ensure APNs token is ready before requesting FCM token on cold boot
      if (defaultTargetPlatform == TargetPlatform.iOS) {
        String? apnsToken = await messaging.getAPNSToken();
        for (int attempt = 0; attempt < 4 && apnsToken == null; attempt++) {
          await Future.delayed(const Duration(milliseconds: 700));
          apnsToken = await messaging.getAPNSToken();
        }
      }

      final token = await messaging
          .getToken()
          .timeout(const Duration(seconds: 6));
      if (token != null) await _saveTokenToSupabase(token);
    } catch (e) {
      debugPrint('Error syncing push token: $e');
    }
  }

  Future<void> _saveTokenToSupabase(String token) async {
    final user = Supabase.instance.client.auth.currentUser;
    if (user == null) return;
    try {
      await Supabase.instance.client.from('user_device_tokens').upsert(
        {
          'user_id': user.id,
          'fcm_token': token,
          'device_type': defaultTargetPlatform == TargetPlatform.iOS ? 'ios' : 'android',
          'updated_at': DateTime.now().toUtc().toIso8601String(),
        },
        onConflict: 'user_id,fcm_token',
      );
    } catch (e) {
      debugPrint('Error saving device token: $e');
    }
  }
}
