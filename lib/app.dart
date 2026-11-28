import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/l10n/app_localizations.dart';
import 'package:magaiees_marketplace/core/providers/global_settings_provider.dart';
import 'package:magaiees_marketplace/core/theme/app_theme.dart';
import 'package:magaiees_marketplace/core/theme/theme_provider.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/chat/presentation/screens/call_screen.dart';
import 'package:magaiees_marketplace/core/utils/call_ringtone_service.dart';
import 'package:magaiees_marketplace/features/locale/application/locale_provider.dart';
import 'package:magaiees_marketplace/features/mode_switch/application/mode_provider.dart';
import 'package:magaiees_marketplace/routing/app_router.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/core/utils/name_formatter.dart';
import 'package:magaiees_marketplace/core/services/call_kit_service.dart';
import 'package:url_launcher/url_launcher.dart';
import 'dart:async';

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

      // Responsive & Accessibility Font Scaling Guard
      builder: (context, child) {
        final mediaQuery = MediaQuery.of(context);
        // Smart clamp: allows comfortable scaling up to 1.15x for users who need larger fonts,
        // while preventing layout destruction and text collisions from extreme scaling (e.g. 1.5x - 2.0x).
        return MediaQuery(
          data: mediaQuery.copyWith(
            textScaler: mediaQuery.textScaler.clamp(
              minScaleFactor: 0.85,
              maxScaleFactor: 1.15,
            ),
          ),
          child: GlobalCallListenerWrapper(child: child ?? const SizedBox.shrink()),
        );
      },
    );
  }
}

/// Global wrapper that listens to incoming calls across all screens of the app
class GlobalCallListenerWrapper extends ConsumerStatefulWidget {
  final Widget child;
  const GlobalCallListenerWrapper({super.key, required this.child});

  @override
  ConsumerState<GlobalCallListenerWrapper> createState() => _GlobalCallListenerWrapperState();
}

class _GlobalCallListenerWrapperState extends ConsumerState<GlobalCallListenerWrapper> {
  RealtimeChannel? _userCallChannel;
  StreamSubscription<AcceptedCall>? _acceptSub;
  String? _dismissedUpdateVersion;
  bool _isUpdateDialogShowing = false;

  @override
  void initState() {
    super.initState();
    _acceptSub = CallKitService.onAccepted.listen(_openAcceptedCall);
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      _setupUserCallListener();
      _checkAndShowUpdateDialog(ref.read(globalSettingsProvider));
      // App cold-started by tapping "رد" on the native call screen
      final pending = await CallKitService.pendingAcceptedCall();
      if (pending != null) _openAcceptedCall(pending);
    });
  }

  void _checkAndShowUpdateDialog(GlobalSettings settings) {
    if (kIsWeb || !settings.isUpdateAvailable || _isUpdateDialogShowing) return;
    final isOptional = settings.optionalUpdatesOnly;
    if (isOptional && _dismissedUpdateVersion == settings.latestAppVersion) return;

    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted || _isUpdateDialogShowing) return;
      final navContext = rootNavigatorKey.currentContext;
      if (navContext == null) return;

      _isUpdateDialogShowing = true;
      final isAr = ref.read(localeNotifierProvider).languageCode == 'ar';
      final storeUrl = defaultTargetPlatform == TargetPlatform.iOS
          ? settings.appStoreUrl
          : settings.playStoreUrl;

      showDialog<void>(
        context: navContext,
        barrierDismissible: isOptional,
        builder: (ctx) => PopScope(
          canPop: isOptional,
          child: AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            title: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: const Color(0xFF059669).withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.system_update_rounded, color: Color(0xFF059669), size: 24),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    isAr ? 'تحديث جديد متاح' : 'New Update Available',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 17),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: const Color(0xFF1E40AF).withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    'v${settings.latestAppVersion}',
                    style: const TextStyle(
                      color: Color(0xFF1E40AF),
                      fontWeight: FontWeight.bold,
                      fontSize: 12,
                    ),
                  ),
                ),
              ],
            ),
            content: Text(
              isAr
                  ? settings.updateMessageAr
                  : 'A new version of Magaiees Marketplace (v${settings.latestAppVersion}) is now available on the store with performance improvements and new features.',
              style: const TextStyle(fontSize: 14, height: 1.5),
            ),
            actions: [
              if (isOptional)
                TextButton(
                  onPressed: () {
                    _dismissedUpdateVersion = settings.latestAppVersion;
                    Navigator.of(ctx).pop();
                  },
                  child: Text(
                    isAr ? 'لاحقاً (المتابعة)' : 'Later',
                    style: const TextStyle(fontWeight: FontWeight.w600),
                  ),
                ),
              FilledButton.icon(
                style: FilledButton.styleFrom(
                  backgroundColor: const Color(0xFF059669),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
                ),
                onPressed: () async {
                  final uri = Uri.tryParse(storeUrl);
                  if (uri != null) {
                    await launchUrl(uri, mode: LaunchMode.externalApplication);
                  }
                  if (isOptional && ctx.mounted) {
                    _dismissedUpdateVersion = settings.latestAppVersion;
                    Navigator.of(ctx).pop();
                  }
                },
                icon: const Icon(Icons.open_in_new_rounded, size: 18),
                label: Text(
                  isAr ? 'تحديث الآن' : 'Update Now',
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
        ),
      ).whenComplete(() {
        _isUpdateDialogShowing = false;
      });
    });
  }

  /// Opens the in-app call screen for a call accepted from the native UI.
  Future<void> _openAcceptedCall(AcceptedCall call) async {
    // Wait briefly for auth to restore after a cold start
    for (var i = 0; i < 20 && ref.read(authNotifierProvider).valueOrNull == null; i++) {
      await Future.delayed(const Duration(milliseconds: 250));
    }
    final user = ref.read(authNotifierProvider).valueOrNull;
    final nav = rootNavigatorKey.currentState;
    if (user == null || nav == null || CallRingtoneService.isCallActive) return;
    await CallKitService.endAll(); // hand over from the native UI to our call screen
    nav.push(
      MaterialPageRoute(
        builder: (_) => CallScreen(
          callID: call.roomId,
          currentUser: user,
          callerName: call.callerName,
          isIncoming: true,
        ),
      ),
    );
  }

  void _setupUserCallListener() {
    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user == null || _userCallChannel != null) return;

    _userCallChannel = Supabase.instance.client.channel('user_call_${user.id}');
    _userCallChannel?.onBroadcast(
      event: 'webrtc_signal',
      callback: (payload) {
        final senderId = payload['sender_id'];
        if (senderId == user.id) return;
        final data = payload['data'] as Map<String, dynamic>?;
        if (data != null && data['type'] == 'join') {
          final roomId = data['room_id']?.toString() ?? '';
          final rawCaller = data['caller_name']?.toString() ?? '';
          final callerName = NameFormatter.cleanPrefix(rawCaller).isNotEmpty
              ? NameFormatter.cleanPrefix(rawCaller)
              : 'مستخدم';
          if (roomId.isNotEmpty && !CallRingtoneService.isCallActive) {
            // When the app is in the background the native call screen (via push) rings instead.
            if (WidgetsBinding.instance.lifecycleState != AppLifecycleState.resumed) return;
            final navContext = rootNavigatorKey.currentContext;
            if (navContext != null) {
              Navigator.push(
                navContext,
                MaterialPageRoute(
                  builder: (ctx) => IncomingCallScreen(
                    roomId: roomId,
                    callerName: callerName,
                    currentUser: user,
                  ),
                ),
              ).then((_) {
                CallRingtoneService.stop();
              });
            }
          }
        }
      },
    ).subscribe();
  }

  @override
  void dispose() {
    _acceptSub?.cancel();
    _cleanupChannel();
    super.dispose();
  }

  void _cleanupChannel() {
    if (_userCallChannel != null) {
      try {
        _userCallChannel!.unsubscribe();
        Supabase.instance.client.removeChannel(_userCallChannel!);
      } catch (_) {}
      _userCallChannel = null;
    }
  }

  @override
  Widget build(BuildContext context) {
    ref.listen(authNotifierProvider, (prev, next) {
      final user = next.valueOrNull;
      if (user != null) {
        _cleanupChannel();
        _setupUserCallListener();
      } else {
        _cleanupChannel();
      }
    });

    ref.listen(globalSettingsProvider, (prev, next) {
      if (next.isUpdateAvailable) {
        _checkAndShowUpdateDialog(next);
      }
    });

    return widget.child;
  }
}
