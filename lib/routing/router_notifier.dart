import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/mode_switch/application/mode_provider.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'router_notifier.g.dart';

/// A [ChangeNotifier] that bridges Riverpod state to [GoRouter.refreshListenable].
///
/// Listens to both auth state and mode changes, notifying the router
/// to re-evaluate its redirect logic whenever either changes.
@riverpod
Raw<RouterNotifier> routerNotifier(RouterNotifierRef ref) {
  return RouterNotifier(ref);
}

class RouterNotifier extends ChangeNotifier {
  RouterNotifier(this._ref) {
    // Watch auth state changes
    _ref.listen(authNotifierProvider, (_, __) {
      notifyListeners();
    });

    // Watch mode changes
    _ref.listen(modeNotifierProvider, (_, __) {
      notifyListeners();
    });
  }

  final Ref _ref;
}
