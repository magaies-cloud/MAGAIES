import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

final wishlistProvider = StateNotifierProvider<WishlistNotifier, AsyncValue<Set<String>>>((ref) {
  return WishlistNotifier();
});

class WishlistNotifier extends StateNotifier<AsyncValue<Set<String>>> {
  WishlistNotifier() : super(const AsyncValue.loading()) {
    loadWishlist();
  }

  final _supabase = Supabase.instance.client;

  Future<void> loadWishlist() async {
    final user = _supabase.auth.currentUser;
    if (user == null) {
      state = const AsyncValue.data({});
      return;
    }

    try {
      state = const AsyncValue.loading();
      final response = await _supabase
          .from('wishlists')
          .select('product_id')
          .eq('user_id', user.id);

      final productIds = (response as List<dynamic>)
          .map((item) => item['product_id'] as String)
          .toSet();

      state = AsyncValue.data(productIds);
    } catch (e, st) {
      state = AsyncValue.error(e, st);
    }
  }

  Future<void> toggleWishlist(String productId) async {
    final user = _supabase.auth.currentUser;
    if (user == null) return;

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
    } catch (e, st) {
      // Revert on error
      state = AsyncValue.data(currentSet);
      state = AsyncValue.error(e, st);
    }
  }
}
