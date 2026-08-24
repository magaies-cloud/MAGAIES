import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/core/enums/supported_locale.dart';
import 'package:magaiees_marketplace/features/locale/application/locale_provider.dart';

/// A compact language toggle button for the app bar.
///
/// Shows the current language and switches to the other
/// on tap (Arabic ↔ English).
class LanguageSwitcher extends ConsumerWidget {
  const LanguageSwitcher({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final currentLocale = ref.watch(currentSupportedLocaleProvider);

    return TextButton.icon(
      onPressed: () {
        ref.read(localeNotifierProvider.notifier).toggleLocale();
      },
      icon: const Icon(Icons.language, size: 20),
      label: Text(
        // Show the OTHER language name as the button label
        // (i.e., what you'll switch TO)
        currentLocale == SupportedLocale.ar ? 'EN' : 'عربي',
        style: const TextStyle(fontWeight: FontWeight.bold),
      ),
    );
  }
}
