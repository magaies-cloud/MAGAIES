import 'dart:ui';
import 'package:riverpod_annotation/riverpod_annotation.dart';
import 'package:magaiees_marketplace/core/enums/supported_locale.dart';
import 'package:shared_preferences/shared_preferences.dart';

part 'locale_provider.g.dart';

/// Key used to persist the selected locale.
const _localeKey = 'selected_locale';

/// Provides the [SharedPreferences] instance.
///
/// Must be overridden in the ProviderScope during bootstrap.
@Riverpod(keepAlive: true)
SharedPreferences sharedPreferences(SharedPreferencesRef ref) {
  // This will be overridden during bootstrap with the actual instance.
  throw UnimplementedError(
    'SharedPreferences must be initialized before use. '
    'Override this provider in the ProviderScope.',
  );
}

/// Manages the application locale (Arabic / English).
///
/// The selected locale is persisted to [SharedPreferences]
/// so it survives app restarts. Default locale is Arabic (ar).
@Riverpod(keepAlive: true)
class LocaleNotifier extends _$LocaleNotifier {
  @override
  Locale build() {
    // Load saved locale from shared preferences
    final prefs = ref.watch(sharedPreferencesProvider);
    final savedLocaleCode = prefs.getString(_localeKey);

    if (savedLocaleCode != null) {
      return SupportedLocale.fromLocale(Locale(savedLocaleCode)).locale;
    }

    // Default to Arabic
    return SupportedLocale.ar.locale;
  }

  /// Switch the app locale.
  Future<void> switchLocale(SupportedLocale locale) async {
    state = locale.locale;
    // Persist the selection
    final prefs = ref.read(sharedPreferencesProvider);
    await prefs.setString(_localeKey, locale.locale.languageCode);
  }

  /// Toggle between Arabic and English.
  Future<void> toggleLocale() async {
    final current = SupportedLocale.fromLocale(state);
    final next = current == SupportedLocale.ar
        ? SupportedLocale.en
        : SupportedLocale.ar;
    await switchLocale(next);
  }
}

/// The current [SupportedLocale] enum value.
@riverpod
SupportedLocale currentSupportedLocale(CurrentSupportedLocaleRef ref) {
  final locale = ref.watch(localeNotifierProvider);
  return SupportedLocale.fromLocale(locale);
}

/// Whether the current locale is RTL.
@riverpod
bool isRtl(IsRtlRef ref) {
  final locale = ref.watch(currentSupportedLocaleProvider);
  return locale.isRtl;
}
