import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:magaiees_marketplace/features/locale/application/locale_provider.dart';

/// Initializes core services before the app starts.
///
/// Returns a list of [Override]s to inject into the [ProviderScope].
/// This is where you initialize:
/// - SharedPreferences
/// - SQLite database
/// - Firebase (when added)
/// - Any other async initialization
Future<List<Override>> bootstrap() async {
  // Initialize SharedPreferences
  final sharedPreferences = await SharedPreferences.getInstance();

  // TODO: Initialize SQLite database
  // final database = await AppDatabase.instance;

  // TODO: Initialize Firebase
  // await Firebase.initializeApp();

  return [
    // Override the SharedPreferences provider with the actual instance
    sharedPreferencesProvider.overrideWithValue(sharedPreferences),
  ];
}
