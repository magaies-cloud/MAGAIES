import 'dart:async';
import 'package:flutter/foundation.dart';
import 'package:magaiees_marketplace/core/services/local_notification_service.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

/// An incoming call the user accepted from the notification.
class AcceptedCall {
  final String inviteId;
  final String roomId;
  final String callerName;
  final String callerId;
  const AcceptedCall({
    required this.inviteId,
    required this.roomId,
    required this.callerName,
    required this.callerId,
  });
}

/// Incoming call notification manager backed by [LocalNotificationService]
/// (compliant with Google Play E-Commerce / Marketplace policies without
/// restricted FOREGROUND_SERVICE_PHONE_CALL or USE_FULL_SCREEN_INTENT).
class CallKitService {
  CallKitService._();

  static const int ringSeconds = 45;

  static final StreamController<AcceptedCall> _accepted =
      StreamController<AcceptedCall>.broadcast();

  static AcceptedCall? _pendingCall;

  /// Emits when the user taps the incoming call notification.
  static Stream<AcceptedCall> get onAccepted => _accepted.stream;

  static Future<void> init() async {
    if (kIsWeb) return;
  }

  /// Called by [LocalNotificationService] when the user taps an incoming call notification.
  static void handleNotificationAccept(AcceptedCall call) {
    _pendingCall = call;
    _updateInvite(call.inviteId, 'accepted');
    _accepted.add(call);
  }

  /// If the app was launched by tapping an incoming call notification while closed.
  static Future<AcceptedCall?> pendingAcceptedCall() async {
    final call = _pendingCall;
    _pendingCall = null;
    return call;
  }

  static Future<void> showIncomingCall({
    required String inviteId,
    required String roomId,
    required String callerName,
    required String callerId,
  }) async {
    if (inviteId.isEmpty || roomId.isEmpty || kIsWeb) return;
    await LocalNotificationService.instance.showIncomingCallAlertNotification(
      inviteId: inviteId,
      roomId: roomId,
      callerName: callerName,
      callerId: callerId,
    );
  }

  static Future<void> endCall(String inviteId) async {
    if (inviteId.isEmpty || kIsWeb) return;
    await LocalNotificationService.instance.cancelIncomingCallAlertNotification(inviteId);
  }

  static Future<void> endAll() async {
    if (kIsWeb) return;
    _pendingCall = null;
    await LocalNotificationService.instance.cancelAllIncomingCallAlerts();
  }

  static Future<void> _updateInvite(String inviteId, String status) async {
    if (inviteId.isEmpty) return;
    try {
      await Supabase.instance.client
          .from('call_invites')
          .update({
            'status': status,
            'updated_at': DateTime.now().toUtc().toIso8601String(),
          })
          .eq('id', inviteId)
          .eq('status', 'ringing');
    } catch (e) {
      debugPrint('Failed to update call invite: $e');
    }
  }
}
