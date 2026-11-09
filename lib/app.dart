import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/l10n/app_localizations.dart';
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

  @override
  void initState() {
    super.initState();
    _acceptSub = CallKitService.onAccepted.listen(_openAcceptedCall);
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      _setupUserCallListener();
      // App cold-started by tapping "رد" on the native call screen
      final pending = await CallKitService.pendingAcceptedCall();
      if (pending != null) _openAcceptedCall(pending);
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

    return widget.child;
  }
}
