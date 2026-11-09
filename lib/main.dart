import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/bootstrap.dart';
import 'package:magaiees_marketplace/app.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Edge-to-Edge display support for Android 15 & modern mobile OS
  try {
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.edgeToEdge);
    SystemChrome.setSystemUIOverlayStyle(
      const SystemUiOverlayStyle(
        statusBarColor: Colors.transparent,
        systemNavigationBarColor: Colors.transparent,
        systemNavigationBarDividerColor: Colors.transparent,
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
