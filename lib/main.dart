import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/bootstrap.dart';
import 'package:magaiees_marketplace/app.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Global Crash & Grey-Screen Shield for Production Release Builds
  FlutterError.onError = (FlutterErrorDetails details) {
    FlutterError.presentError(details);
    debugPrint('FlutterError caught: ${details.exceptionAsString()}');
  };

  PlatformDispatcher.instance.onError = (Object error, StackTrace stack) {
    debugPrint('Uncaught async error: $error\n$stack');
    return true;
  };

  ErrorWidget.builder = (FlutterErrorDetails details) {
    if (!kReleaseMode) {
      return ErrorWidget(details.exception);
    }
    return Material(
      color: Colors.transparent,
      child: Center(
        child: Container(
          margin: const EdgeInsets.all(20),
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 18),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFE2E8F0)),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.05),
                blurRadius: 12,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: const Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.refresh_rounded, size: 36, color: Color(0xFF1E40AF)),
              SizedBox(height: 10),
              Text(
                'تعذر عرض هذا الجزء مؤقتاً',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF0F172A),
                ),
              ),
              SizedBox(height: 4),
              Text(
                'يرجى الرجوع للخلف أو التحديث للمتابعة',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 12,
                  color: Color(0xFF64748B),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  };

  // Edge-to-Edge display support for Android 15 & modern mobile OS
  try {
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.edgeToEdge);
    SystemChrome.setSystemUIOverlayStyle(
      const SystemUiOverlayStyle(
        statusBarColor: Colors.transparent,
        statusBarIconBrightness: Brightness.dark,
        statusBarBrightness: Brightness.light,
        systemNavigationBarColor: Colors.transparent,
        systemNavigationBarDividerColor: Colors.transparent,
        systemNavigationBarIconBrightness: Brightness.dark,
      ),
    );
  } catch (e) {
    debugPrint('SystemChrome configuration warning: $e');
  }

  // Initialize Supabase with safety guard so startup never hangs
  try {
    await Supabase.initialize(
      url: 'https://ktxiqgajgvbbrbumtufe.supabase.co',
      anonKey: 'sb_publishable_n5bTkwOUGdVCEmApubP86Q_NoH0EsxD',
    ).timeout(const Duration(seconds: 8));
  } catch (e) {
    debugPrint('Supabase initialization warning: $e');
  }

  // Initialize core services and get provider overrides
  final overrides = await bootstrap();

  runApp(
    ProviderScope(
      overrides: overrides,
      child: const MagaieesApp(),
    ),
  );
}
