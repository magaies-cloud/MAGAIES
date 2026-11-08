import 'dart:async';
import 'package:flutter/foundation.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:magaiees_marketplace/features/locale/application/locale_provider.dart';
import 'package:magaiees_marketplace/core/services/local_notification_service.dart';
import 'package:magaiees_marketplace/core/services/push_notification_service.dart';

/// Initializes core services before the app starts.
///
/// Returns a list of [Override]s to inject into the [ProviderScope].
Future<List<Override>> bootstrap() async {
  // Initialize SharedPreferences (local, fast)
  final sharedPreferences = await SharedPreferences.getInstance();

  // Schedule notification & push services AFTER the first UI frame renders
  // so they never block runApp() or cause splash-screen freezes on Honor/MagicOS/Android 15.
  WidgetsBinding.instance.addPostFrameCallback((_) {
    unawaited(_initBackgroundServices());
  });

  return [
    sharedPreferencesProvider.overrideWithValue(sharedPreferences),
  ];
}

Future<void> _initBackgroundServices() async {
  // Small delay to ensure Activity & Flutter surface are completely settled
  await Future.delayed(const Duration(milliseconds: 600));

  try {
    await LocalNotificationService.instance
        .init()
        .timeout(const Duration(seconds: 8));
  } catch (e) {
    debugPrint('LocalNotificationService initialization error: $e');
  }

  try {
    await PushNotificationService.instance
        .init()
        .timeout(const Duration(seconds: 10));
  } catch (e) {
    debugPrint('PushNotificationService initialization error: $e');
  }
}
