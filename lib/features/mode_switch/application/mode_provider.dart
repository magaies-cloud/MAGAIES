import 'package:riverpod_annotation/riverpod_annotation.dart';
import 'package:magaiees_marketplace/core/enums/app_mode.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';

part 'mode_provider.g.dart';

/// Manages the current UI mode (Buyer vs Merchant).
///
/// The mode determines which navigation shell and screens
/// the user sees. A user can only switch to [AppMode.merchant]
/// if they have an approved store ([hasStore] is true).
@Riverpod(keepAlive: true)
class ModeNotifier extends _$ModeNotifier {
  @override
  AppMode build() {
    // Default mode is always buyer
    return AppMode.buyer;
  }

  /// Toggle between buyer and merchant modes.
  ///
  /// Returns `true` if the toggle was successful.
  /// Returns `false` if the user doesn't have a store.
  bool toggleMode() {
    if (state == AppMode.buyer) {
      // Check if user has a store before switching to merchant
      final userHasStore = ref.read(hasStoreProvider);
      if (!userHasStore) {
        return false;
      }
      state = AppMode.merchant;
    } else {
      state = AppMode.buyer;
    }
    return true;
  }

  /// Switch to a specific mode.
  ///
  /// Returns `true` if the switch was successful.
  bool switchTo(AppMode mode) {
    if (mode == AppMode.merchant) {
      final userHasStore = ref.read(hasStoreProvider);
      if (!userHasStore) return false;
    }
    state = mode;
    return true;
  }

  /// Reset to buyer mode (e.g., on logout).
  void resetToBuyer() {
    state = AppMode.buyer;
  }
}

/// Whether the user is currently in merchant mode.
@riverpod
bool isMerchantMode(IsMerchantModeRef ref) {
  return ref.watch(modeNotifierProvider) == AppMode.merchant;
}
