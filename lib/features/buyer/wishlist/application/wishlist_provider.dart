import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

final wishlistProvider = StateNotifierProvider<WishlistNotifier, AsyncValue<Set<String>>>((ref) {
  return WishlistNotifier();
});

class WishlistNotifier extends StateNotifier<AsyncValue<Set<String>>> {
  WishlistNotifier() : super(const AsyncValue.loading()) {
    loadWishlist();
  }

  static const _guestPrefsKey = 'guest_wishlist_ids_v1';
  final _supabase = Supabase.instance.client;

  Future<void> loadWishlist() async {
    final user = _supabase.auth.currentUser;
    if (user == null) {
      final prefs = await SharedPreferences.getInstance();
      final saved = prefs.getStringList(_guestPrefsKey) ?? const <String>[];
      state = AsyncValue.data(saved.toSet());
      return;
    }

    try {
      state = const AsyncValue.loading();
      final response = await _supabase
          .from('wishlists')
          .select('product_id')
          .eq('user_id', user.id);

      final productIds = (response as List<dynamic>)
          .map((item) => item['product_id']?.toString() ?? '')
          .where((id) => id.isNotEmpty)
          .toSet();

      state = AsyncValue.data(productIds);
    } catch (e, st) {
      state = AsyncValue.error(e, st);
    }
  }

  Future<void> toggleWishlist(String productId) async {
    final currentSet = state.valueOrNull ?? {};
    final isPresent = currentSet.contains(productId);

    // Optimistic update
    final updatedSet = Set<String>.from(currentSet);
    if (isPresent) {
      updatedSet.remove(productId);
    } else {
      updatedSet.add(productId);
    }
    state = AsyncValue.data(updatedSet);

    final user = _supabase.auth.currentUser;
    if (user == null) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setStringList(_guestPrefsKey, updatedSet.toList());
      return;
    }

    try {
      if (isPresent) {
        await _supabase
            .from('wishlists')
            .delete()
            .eq('user_id', user.id)
            .eq('product_id', productId);
      } else {
        await _supabase.from('wishlists').insert({
          'user_id': user.id,
          'product_id': productId,
        });
      }
    } catch (_) {
      // Revert on error without breaking UI into error state
      state = AsyncValue.data(currentSet);
    }
  }
}
