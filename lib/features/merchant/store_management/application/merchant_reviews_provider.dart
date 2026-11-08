import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

class MerchantReviewsNotifier
    extends StateNotifier<AsyncValue<List<Map<String, dynamic>>>> {
  MerchantReviewsNotifier() : super(const AsyncValue.loading()) {
    loadReviews();
  }

  Future<void> loadReviews() async {
    state = const AsyncValue.loading();
    try {
      final supabase = Supabase.instance.client;
      final merchantId = supabase.auth.currentUser?.id;
      
      if (merchantId == null) {
        state = const AsyncValue.data([]);
        return;
      }

      final storeRes = await supabase
          .from('stores')
          .select('id')
          .eq('owner_id', merchantId)
          .limit(1)
          .maybeSingle();
          
      if (storeRes == null) {
        state = const AsyncValue.data([]);
        return; // No store found for this merchant
      }

      final storeId = storeRes['id'];
      final res = await supabase
          .from('product_reviews')
          .select('*, products!inner(name, name_ar, store_id), users!buyer_id(name, name_ar)')
          .eq('products.store_id', storeId)
          .order('created_at', ascending: false);

      state = AsyncValue.data(List<Map<String, dynamic>>.from(res));
    } catch (e, st) {
      state = AsyncValue.error(e, st);
    }
  }
}

final merchantReviewsProvider = StateNotifierProvider<MerchantReviewsNotifier,
    AsyncValue<List<Map<String, dynamic>>>>((ref) {
  return MerchantReviewsNotifier();
});
