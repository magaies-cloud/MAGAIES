import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';

final merchantWalletProvider = StateNotifierProvider.autoDispose<MerchantWalletNotifier, AsyncValue<Map<String, dynamic>>>((ref) {
  return MerchantWalletNotifier(ref);
});

class MerchantWalletNotifier extends StateNotifier<AsyncValue<Map<String, dynamic>>> {
  final Ref ref;
  RealtimeChannel? _realtimeChannel;

  MerchantWalletNotifier(this.ref) : super(const AsyncValue.loading()) {
    loadWalletData();
    _setupRealtime();
  }

  void _setupRealtime() {
    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user == null) return;
    _realtimeChannel?.unsubscribe();
    _realtimeChannel = Supabase.instance.client
        .channel('merchant_wallet_user_${user.id}')
        .onPostgresChanges(
          event: PostgresChangeEvent.all,
          schema: 'public',
          table: 'users',
          filter: PostgresChangeFilter(
            type: PostgresChangeFilterType.eq,
            column: 'id',
            value: user.id,
          ),
          callback: (payload) => loadWalletData(),
        )
        .subscribe();
  }

  @override
  void dispose() {
    _realtimeChannel?.unsubscribe();
    super.dispose();
  }

  Future<void> loadWalletData() async {
    try {
      final user = ref.read(authNotifierProvider).valueOrNull;
      if (user == null) return;
      
      // 1. Fetch Payouts
      final payoutsRes = await Supabase.instance.client
          .from('merchant_payout_requests')
          .select()
          .eq('merchant_id', user.id)
          .order('created_at', ascending: false);
          
      // 2. Fetch merchant store
      final storeRes = await Supabase.instance.client
          .from('stores')
          .select('id, bank_name, bank_account_name, bank_account_number')
          .eq('owner_id', user.id)
          .limit(1)
          .maybeSingle();
      final storeId = storeRes != null ? storeRes['id']?.toString() : null;

      // Also check users table for bank info fallback
      final userRes = await Supabase.instance.client
          .from('users')
          .select('bank_name, bank_account_name, bank_account_number')
          .eq('id', user.id)
          .maybeSingle();

      String bankName = storeRes?['bank_name']?.toString() ?? '';
      if (bankName.isEmpty) bankName = userRes?['bank_name']?.toString() ?? '';

      String bankAccountName = storeRes?['bank_account_name']?.toString() ?? '';
      if (bankAccountName.isEmpty) bankAccountName = userRes?['bank_account_name']?.toString() ?? '';

      String bankAccountNumber = storeRes?['bank_account_number']?.toString() ?? '';
      if (bankAccountNumber.isEmpty) bankAccountNumber = userRes?['bank_account_number']?.toString() ?? '';

      // 3. Fetch all delivered orders for this store to calculate total sales
      List<dynamic> ordersRes = [];
      if (storeId != null) {
        ordersRes = await Supabase.instance.client
            .from('orders')
            .select('total_amount, paid_amount')
            .eq('store_id', storeId)
            .eq('status', 'delivered');
      }

      // 4. Fetch Gamification settings for commission
      final settingsRes = await Supabase.instance.client
          .from('gamification_settings')
          .select('enable_commission, commission_percentage')
          .eq('id', 1)
          .maybeSingle();

      double totalSales = 0;
      for (var o in ordersRes) {
        totalSales += (num.tryParse(o['total_amount']?.toString() ?? '0') ?? 0.0).toDouble();
      }

      bool enableCommission = false;
      double commissionPercent = 0.0;
      if (settingsRes != null) {
        enableCommission = settingsRes['enable_commission'] == true;
        commissionPercent = (num.tryParse(settingsRes['commission_percentage']?.toString() ?? '0') ?? 0.0).toDouble();
      }

      double commissionAmount = 0.0;
      if (enableCommission) {
        commissionAmount = (totalSales * commissionPercent) / 100;
      }

      double netProfit = totalSales - commissionAmount;

      // Calculate total approved and pending payouts to subtract from net profit
      double totalWithdrawn = 0;
      for (var p in payoutsRes) {
        if (p['status'] == 'approved' || p['status'] == 'pending') {
          totalWithdrawn += (num.tryParse(p['amount']?.toString() ?? '0') ?? 0.0).toDouble();
        }
      }

      double withdrawableBalance = (netProfit - totalWithdrawn).clamp(0.0, double.infinity);

      state = AsyncValue.data({
        'payouts': List<Map<String, dynamic>>.from(payoutsRes),
        'totalSales': totalSales,
        'commissionAmount': commissionAmount,
        'commissionPercent': commissionPercent,
        'enableCommission': enableCommission,
        'netProfit': netProfit,
        'withdrawableBalance': withdrawableBalance,
        'bankName': bankName,
        'bankAccountName': bankAccountName,
        'bankAccountNumber': bankAccountNumber,
      });
    } catch (e, st) {
      state = AsyncValue.error(e, st);
    }
  }

  Future<void> requestPayout(double amount, String bankName, String accountName, String accountNumber) async {
    try {
      final user = ref.read(authNotifierProvider).valueOrNull;
      if (user == null) return;

      // Check balance
      final currentState = state.value;
      if (currentState != null) {
        final bal = (currentState['withdrawableBalance'] as num?)?.toDouble() ?? 0.0;
        if (amount > bal) {
          throw Exception('Requested amount exceeds withdrawable balance.');
        }
      }

      await Supabase.instance.client.from('merchant_payout_requests').insert({
        'merchant_id': user.id,
        'amount': amount,
        'bank_name': bankName,
        'account_name': accountName,
        'account_number': accountNumber,
        'status': 'pending'
      });

      // Also persist bank details to stores & users
      try {
        await Supabase.instance.client.from('stores').update({
          'bank_name': bankName,
          'bank_account_name': accountName,
          'bank_account_number': accountNumber,
        }).eq('owner_id', user.id);
      } catch (_) {}

      try {
        await Supabase.instance.client.from('users').update({
          'bank_name': bankName,
          'bank_account_name': accountName,
          'bank_account_number': accountNumber,
        }).eq('id', user.id);
      } catch (_) {}

      await loadWalletData();
    } catch (e) {
      rethrow;
    }
  }
}
