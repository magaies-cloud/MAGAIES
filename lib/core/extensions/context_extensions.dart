import 'package:flutter/material.dart';
import 'package:magaiees_marketplace/l10n/app_localizations.dart';

/// Convenience extensions on [BuildContext].
extension ContextExtensions on BuildContext {
  /// Access localized strings.
  AppLocalizations get l10n => AppLocalizations.of(this)!;

  /// Access the current theme.
  ThemeData get theme => Theme.of(this);

  /// Access the current color scheme.
  ColorScheme get colorScheme => Theme.of(this).colorScheme;

  /// Access the current text theme.
  TextTheme get textTheme => Theme.of(this).textTheme;

  /// Get the current text direction (LTR or RTL).
  TextDirection get textDirection => Directionality.of(this);

  /// Whether the current locale is RTL.
  bool get isRtl => Directionality.of(this) == TextDirection.rtl;

  /// Screen size.
  Size get screenSize => MediaQuery.sizeOf(this);

  /// Screen width.
  double get screenWidth => MediaQuery.sizeOf(this).width;

  /// Screen height.
  double get screenHeight => MediaQuery.sizeOf(this).height;

  /// Show a snackbar.
  void showSnackBar(String message, {bool isError = false}) {
    ScaffoldMessenger.of(this).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? colorScheme.error : null,
      ),
    );
  }
}
