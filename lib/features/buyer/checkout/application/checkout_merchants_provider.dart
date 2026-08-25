import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/buyer/cart/application/cart_provider.dart';

final checkoutMerchantsProvider = FutureProvider.autoDispose<List<Map<String, dynamic>>>((ref) async {
  final cart = ref.watch(cartNotifierProvider);
  if (cart.items.isEmpty) return [];

  final Set<String> merchantIds = {};
  for (var item in cart.items) {
    merchantIds.add(item.product.merchantId);
  }

  final response = await Supabase.instance.client
      .from('users')
      .select('id, name, merchant_tier, direct_payment_enabled, bank_name, bank_account_name, bank_account_number')
      .inFilter('id', merchantIds.toList());

  return List<Map<String, dynamic>>.from(response);
});
