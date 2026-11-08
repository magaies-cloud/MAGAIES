import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/buyer/cart/application/cart_provider.dart';

final checkoutMerchantsProvider = FutureProvider.autoDispose<List<Map<String, dynamic>>>((ref) async {
  final cart = ref.watch(cartNotifierProvider);
  if (cart.items.isEmpty) return [];

  final Set<String> merchantIds = {};
  for (var item in cart.items) {
    if (item.product.merchantId.isNotEmpty) {
      merchantIds.add(item.product.merchantId);
    }
  }

  // Filter only valid UUID strings to avoid PostgrestException 22P02
  final validUuidRegex = RegExp(r'^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$', caseSensitive: false);
  final validUuids = merchantIds.where((id) => validUuidRegex.hasMatch(id)).toList();

  if (validUuids.isEmpty) {
    return [];
  }

  try {
    // 1. Fetch stores matching cart item merchant IDs
    final storeRes = await Supabase.instance.client
        .from('stores')
        .select('id, owner_id, name, bank_name, bank_account_name, bank_account_number')
        .inFilter('id', validUuids)
        .timeout(const Duration(seconds: 8));

    final List<Map<String, dynamic>> results = [];
    final Set<String> ownerIds = {};

    for (var s in storeRes) {
      final ownerId = s['owner_id']?.toString();
      if (ownerId != null && validUuidRegex.hasMatch(ownerId)) {
        ownerIds.add(ownerId);
      }
    }

    final Map<String, dynamic> usersByOwnerId = {};
    if (ownerIds.isNotEmpty) {
      final usersRes = await Supabase.instance.client
          .from('users')
          .select('id, name, merchant_tier, direct_payment_enabled, bank_name, bank_account_name, bank_account_number, permissions')
          .inFilter('id', ownerIds.toList())
          .timeout(const Duration(seconds: 8));
      for (var u in usersRes) {
        usersByOwnerId[u['id'].toString()] = u;
      }
    }

    for (var s in storeRes) {
      final ownerId = s['owner_id']?.toString();
      final user = usersByOwnerId[ownerId] ?? {};
      final userPerms = (user['permissions'] is Map) ? user['permissions'] as Map<String, dynamic> : <String, dynamic>{};
      
      results.add({
        'store_id': s['id'],
        'id': ownerId ?? s['id'],
        'name': s['name'] ?? user['name'] ?? '',
        'merchant_tier': user['merchant_tier'] ?? 'bronze',
        'direct_payment_enabled': user['direct_payment_enabled'] == true,
        'bank_name': (s['bank_name'] != null && s['bank_name'].toString().trim().isNotEmpty) ? s['bank_name'] : user['bank_name'],
        'bank_account_name': (s['bank_account_name'] != null && s['bank_account_name'].toString().trim().isNotEmpty) ? s['bank_account_name'] : user['bank_account_name'],
        'bank_account_number': (s['bank_account_number'] != null && s['bank_account_number'].toString().trim().isNotEmpty) ? s['bank_account_number'] : user['bank_account_number'],
        'bankak_account': userPerms['bankak_account'] ?? '',
        'bankak_name': userPerms['bankak_name'] ?? '',
        'ocash_phone': userPerms['ocash_phone'] ?? '',
        'ocash_name': userPerms['ocash_name'] ?? '',
        'accept_cod': userPerms['accept_cod'] ?? true,
        'accept_partial_cod': userPerms['accept_partial_cod'] ?? true,
        'accept_b2b': userPerms['accept_b2b'] ?? true,
        'b2b_approved_buyers': (userPerms['b2b_approved_buyers'] is List)
            ? userPerms['b2b_approved_buyers'] as List
            : [],
      });
    }

    // 2. Also check if any validUuid was a direct user id (fallback)
    final foundStoreIds = results.map((r) => r['store_id']).toSet();
    final remainingUuids = validUuids.where((id) => !foundStoreIds.contains(id)).toList();
    if (remainingUuids.isNotEmpty) {
      final usersDirect = await Supabase.instance.client
          .from('users')
          .select('id, name, merchant_tier, direct_payment_enabled, bank_name, bank_account_name, bank_account_number, permissions')
          .inFilter('id', remainingUuids)
          .timeout(const Duration(seconds: 8));
      for (var u in usersDirect) {
        final userPerms = (u['permissions'] is Map) ? u['permissions'] as Map<String, dynamic> : <String, dynamic>{};
        results.add({
          'store_id': u['id'],
          'id': u['id'],
          'name': u['name'] ?? '',
          'merchant_tier': u['merchant_tier'] ?? 'bronze',
          'direct_payment_enabled': u['direct_payment_enabled'] == true,
          'bank_name': u['bank_name'],
          'bank_account_name': u['bank_account_name'],
          'bank_account_number': u['bank_account_number'],
          'bankak_account': userPerms['bankak_account'] ?? '',
          'bankak_name': userPerms['bankak_name'] ?? '',
          'ocash_phone': userPerms['ocash_phone'] ?? '',
          'ocash_name': userPerms['ocash_name'] ?? '',
          'accept_cod': userPerms['accept_cod'] ?? true,
          'accept_partial_cod': userPerms['accept_partial_cod'] ?? true,
          'accept_b2b': userPerms['accept_b2b'] ?? true,
          'b2b_approved_buyers': (userPerms['b2b_approved_buyers'] is List)
              ? userPerms['b2b_approved_buyers'] as List
              : [],
        });
      }
    }

    return results;
  } catch (e) {
    return [];
  }
});

/// Checks the logged-in buyer's B2B Corporate Credit eligibility
final buyerB2bEligibilityProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final user = Supabase.instance.client.auth.currentUser;
  if (user == null) {
    return {'isEligible': false, 'scope': 'none'};
  }

  try {
    final res = await Supabase.instance.client
        .from('users')
        .select('permissions')
        .eq('id', user.id)
        .maybeSingle()
        .timeout(const Duration(seconds: 8));

    if (res != null && res['permissions'] is Map) {
      final perms = res['permissions'] as Map<String, dynamic>;
      final bool enabled = perms['b2b_credit_enabled'] == true;
      final String scope = perms['b2b_allowed_scope']?.toString() ?? 'all';
      final double limit = double.tryParse(perms['b2b_credit_limit']?.toString() ?? '') ?? 0.0;
      final List allowedMerchants = (perms['b2b_allowed_merchants'] is List)
          ? perms['b2b_allowed_merchants'] as List
          : [];

      return {
        'isEligible': enabled,
        'scope': scope,
        'creditLimit': limit,
        'allowedMerchants': allowedMerchants,
      };
    }
  } catch (_) {}

  return {'isEligible': false, 'scope': 'none'};
});

