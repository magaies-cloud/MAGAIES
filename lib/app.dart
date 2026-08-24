import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/l10n/app_localizations.dart';
import 'package:magaiees_marketplace/core/theme/app_theme.dart';
import 'package:magaiees_marketplace/core/theme/theme_provider.dart';
import 'package:magaiees_marketplace/features/locale/application/locale_provider.dart';
import 'package:magaiees_marketplace/features/mode_switch/application/mode_provider.dart';
import 'package:magaiees_marketplace/routing/app_router.dart';

/// Root application widget.
///
/// Configures:
/// - Routing via [GoRouter]
/// - Localization (Arabic + English with runtime switching)
/// - Theme (different themes for buyer vs merchant mode, and Light / Dark support)
class MagaieesApp extends ConsumerStatefulWidget {
  const MagaieesApp({super.key});

  @override
  ConsumerState<MagaieesApp> createState() => _MagaieesAppState();
}

class _MagaieesAppState extends ConsumerState<MagaieesApp> {
  bool _logoPrecached = false;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (!_logoPrecached) {
      _logoPrecached = true;
      // Precache the logo so it's decoded and ready in memory
      // before LoginScreen tries to render it.
      precacheImage(
        const AssetImage('assets/images/logo_final.png'),
        context,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final router = ref.watch(goRouterProvider);
    final locale = ref.watch(localeNotifierProvider);
    final isMerchant = ref.watch(isMerchantModeProvider);
    final themeMode = ref.watch(themeModeProvider);

    return MaterialApp.router(
      // Routing
      routerConfig: router,

      // App Info
      title: 'Magaiees Marketplace',
      debugShowCheckedModeBanner: false,

      // Theme — switch between buyer and merchant themes
      theme: isMerchant ? AppTheme.merchantTheme : AppTheme.lightTheme,
      darkTheme: AppTheme.darkTheme,
      themeMode: themeMode,

      // Localization
      locale: locale,
      localizationsDelegates: AppLocalizations.localizationsDelegates,
      supportedLocales: AppLocalizations.supportedLocales,
    );
  }
}
