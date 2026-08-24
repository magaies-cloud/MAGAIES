import 'dart:ui';

/// Supported locales in the application.
enum SupportedLocale {
  en(Locale('en'), 'English', 'الإنجليزية'),
  ar(Locale('ar'), 'العربية', 'Arabic');

  const SupportedLocale(this.locale, this.nativeName, this.otherName);

  /// The [Locale] object for this supported locale.
  final Locale locale;

  /// The name in its own language.
  final String nativeName;

  /// The name in the other language.
  final String otherName;

  /// Whether this locale is RTL.
  bool get isRtl => this == SupportedLocale.ar;

  /// Get SupportedLocale from a Locale, defaults to Arabic.
  static SupportedLocale fromLocale(Locale locale) {
    return SupportedLocale.values.firstWhere(
      (l) => l.locale.languageCode == locale.languageCode,
      orElse: () => SupportedLocale.ar,
    );
  }
}
