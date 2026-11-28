import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

final merchantCouponsProvider = StateNotifierProvider<MerchantCouponsNotifier, AsyncValue<Map<String, dynamic>>>((ref) {
  return MerchantCouponsNotifier();
});

class MerchantCouponsNotifier extends StateNotifier<AsyncValue<Map<String, dynamic>>> {
  MerchantCouponsNotifier() : super(const AsyncValue.loading()) {
    _loadCoupons();
  }

  final _supabase = Supabase.instance.client;

  Future<void> _loadCoupons() async {
    state = const AsyncValue.loading();
    try {
      final merchantId = _supabase.auth.currentUser?.id;
      if (merchantId == null) throw Exception('User not logged in');

      // Fetch gamification_settings (id=1)
      final settingsResponse = await _supabase
          .from('gamification_settings')
          .select('enable_coupons')
          .eq('id', 1)
          .maybeSingle();
          
      final enableCoupons = settingsResponse?['enable_coupons'] ?? false;

      // Fetch coupons and normalize column aliases for UI compatibility
      final couponsResponse = await _supabase
          .from('coupons')
          .select()
          .eq('merchant_id', merchantId)
          .order('created_at', ascending: false);

      final normalizedCoupons = (couponsResponse as List).map((raw) {
        final c = Map<String, dynamic>.from(raw as Map);
        c['type'] ??= c['discount_type'] ?? 'percentage';
        c['value'] ??= c['discount_value'] ?? 0;
        c['min_order_amount'] ??= c['min_order_value'] ?? 0;
        c['expiry_date'] ??= c['expires_at'];
        return c;
      }).toList();

      state = AsyncValue.data({
        'enableCoupons': enableCoupons,
        'coupons': normalizedCoupons,
      });
    } catch (e, st) {
      state = AsyncValue.error(e, st);
    }
  }

  Future<void> createCoupon(
    String code, 
    String type, 
    double value, 
    double minOrder, 
    int limit, 
    DateTime expiry,
  ) async {
    try {
      final merchantId = _supabase.auth.currentUser?.id;
      if (merchantId == null) throw Exception('User not logged in');

      await _supabase.from('coupons').insert({
        'merchant_id': merchantId,
        'code': code.trim(),
        'discount_type': type,
        'discount_value': value,
        'min_order_value': minOrder,
        'usage_limit': limit,
        'expires_at': expiry.toIso8601String(),
        'is_active': true,
      });
      await _loadCoupons(); // Refresh list after insertion
    } catch (e) {
      rethrow;
    }
  }

  Future<void> toggleCoupon(String id, bool isActive) async {
    try {
      await _supabase.from('coupons').update({
        'is_active': isActive,
      }).eq('id', id);
      
      // Update local state instead of refetching everything to make it faster
      state.whenData((data) {
        final List<dynamic> coupons = List.from(data['coupons']);
        final index = coupons.indexWhere((c) => c['id'].toString() == id);
        if (index != -1) {
          coupons[index] = {
            ...coupons[index],
            'is_active': isActive,
          };
          state = AsyncValue.data({
            'enableCoupons': data['enableCoupons'],
            'coupons': coupons,
          });
        }
      });
    } catch (e) {
      rethrow;
    }
  }
}
