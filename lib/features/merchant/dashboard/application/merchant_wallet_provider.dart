import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/auth/domain/models/user_model.dart';

final merchantWalletProvider = StateNotifierProvider.autoDispose<MerchantWalletNotifier, AsyncValue<Map<String, dynamic>>>((ref) {
  return MerchantWalletNotifier(ref);
});

class MerchantWalletNotifier extends StateNotifier<AsyncValue<Map<String, dynamic>>> {
  final Ref ref;
  RealtimeChannel? _realtimeChannel;
  String? _subscribedStoreId;

  MerchantWalletNotifier(this.ref) : super(const AsyncValue.loading()) {
    loadWalletData();
  }

  void _setupRealtime({required String userId, required String payoutMerchantId, String? storeId}) {
    if (_realtimeChannel != null && _subscribedStoreId == storeId) return;
    _subscribedStoreId = storeId;
    _realtimeChannel?.unsubscribe();

    var channel = Supabase.instance.client
        .channel('merchant_wallet_sync_${userId}_${storeId ?? "none"}')
        .onPostgresChanges(
          event: PostgresChangeEvent.all,
          schema: 'public',
          table: 'users',
          filter: PostgresChangeFilter(
            type: PostgresChangeFilterType.eq,
            column: 'id',
            value: userId,
          ),
          callback: (_) => loadWalletData(),
        )
        .onPostgresChanges(
          event: PostgresChangeEvent.all,
          schema: 'public',
          table: 'merchant_payout_requests',
          filter: PostgresChangeFilter(
            type: PostgresChangeFilterType.eq,
            column: 'merchant_id',
            value: payoutMerchantId,
          ),
          callback: (_) => loadWalletData(),
        );

    if (storeId != null && storeId.isNotEmpty) {
      channel = channel.onPostgresChanges(
        event: PostgresChangeEvent.all,
        schema: 'public',
        table: 'orders',
        filter: PostgresChangeFilter(
          type: PostgresChangeFilterType.eq,
          column: 'store_id',
          value: storeId,
        ),
        callback: (_) => loadWalletData(),
      );
    }

    _realtimeChannel = channel.subscribe();
  }

  @override
  void dispose() {
    _realtimeChannel?.unsubscribe();
    super.dispose();
  }

  Future<void> loadWalletData() async {
    try {
      final user = ref.read(authNotifierProvider).valueOrNull;
      if (user == null) {
        if (!mounted) return;
        state = const AsyncValue.data({});
        return;
      }

      // 1. Resolve merchant store (supports both Store Owner and Store Staff)
      Map<String, dynamic>? storeRes;
      if (user.isStoreStaff && user.storeId != null && user.storeId!.isNotEmpty) {
        storeRes = await Supabase.instance.client
            .from('stores')
            .select('id, owner_id, bank_name, bank_account_name, bank_account_number')
            .eq('id', user.storeId!)
            .limit(1)
            .maybeSingle();
      } else {
        storeRes = await Supabase.instance.client
            .from('stores')
            .select('id, owner_id, bank_name, bank_account_name, bank_account_number')
            .eq('owner_id', user.id)
            .limit(1)
            .maybeSingle();
      }

      final storeId = storeRes?['id']?.toString() ?? user.storeId;
      final ownerId = storeRes?['owner_id']?.toString() ?? user.id;

      _setupRealtime(userId: user.id, payoutMerchantId: ownerId, storeId: storeId);

      // 2. Fetch Payouts for the store owner
      final payoutsRes = await Supabase.instance.client
          .from('merchant_payout_requests')
          .select()
          .eq('merchant_id', ownerId)
          .order('created_at', ascending: false);

      // Also check users table for bank info fallback
      final userRes = await Supabase.instance.client
          .from('users')
          .select('bank_name, bank_account_name, bank_account_number')
          .eq('id', ownerId)
          .maybeSingle();

      String bankName = storeRes?['bank_name']?.toString() ?? '';
      if (bankName.isEmpty) bankName = userRes?['bank_name']?.toString() ?? '';

      String bankAccountName = storeRes?['bank_account_name']?.toString() ?? '';
      if (bankAccountName.isEmpty) bankAccountName = userRes?['bank_account_name']?.toString() ?? '';

      String bankAccountNumber = storeRes?['bank_account_number']?.toString() ?? '';
      if (bankAccountNumber.isEmpty) bankAccountNumber = userRes?['bank_account_number']?.toString() ?? '';

      // 3. Fetch all store orders to compute exact Sales, Collected, Debts & Delivered Wallet Balance
      List<dynamic> ordersRes = [];
      if (storeId != null && storeId.isNotEmpty) {
        ordersRes = await Supabase.instance.client
            .from('orders')
            .select('status, payment_status, total_amount, paid_amount')
            .eq('store_id', storeId);
      }

      // 4. Fetch Gamification settings for commission
      final settingsRes = await Supabase.instance.client
          .from('gamification_settings')
          .select('enable_commission, commission_percentage')
          .eq('id', 1)
          .maybeSingle();

      double allActiveSales = 0.0;
      double totalCollected = 0.0;
      double totalDebts = 0.0;
      double deliveredCollectedSales = 0.0;
      double deliveredTotalSales = 0.0;

      for (final o in ordersRes) {
        final status = o['status']?.toString() ?? 'pending';
        if (status == 'cancelled') continue;

        final total = (num.tryParse(o['total_amount']?.toString() ?? '0') ?? 0.0).toDouble();
        double paid = (num.tryParse(o['paid_amount']?.toString() ?? '0') ?? 0.0).toDouble();
        if (o['payment_status'] == 'paid' && paid <= 0 && total > 0) {
          paid = total;
        }
        final effectivePaid = paid.clamp(0.0, total);
        final debt = (total - effectivePaid).clamp(0.0, double.infinity);

        allActiveSales += total;
        totalCollected += effectivePaid;
        totalDebts += debt;

        if (status == 'delivered') {
          deliveredTotalSales += total;
          deliveredCollectedSales += effectivePaid;
        }
      }

      bool enableCommission = false;
      double commissionPercent = 0.0;
      if (settingsRes != null) {
        enableCommission = settingsRes['enable_commission'] == true;
        commissionPercent = (num.tryParse(settingsRes['commission_percentage']?.toString() ?? '0') ?? 0.0).toDouble();
      }

      double commissionAmount = 0.0;
      if (enableCommission) {
        commissionAmount = (deliveredCollectedSales * commissionPercent) / 100;
      }

      final double netProfit = (deliveredCollectedSales - commissionAmount).clamp(0.0, double.infinity);

      // Subtract approved and pending payouts from net collected profit
      double totalWithdrawn = 0.0;
      for (final p in payoutsRes) {
        if (p['status'] == 'approved' || p['status'] == 'pending') {
          totalWithdrawn += (num.tryParse(p['amount']?.toString() ?? '0') ?? 0.0).toDouble();
        }
      }

      final double withdrawableBalance = (netProfit - totalWithdrawn).clamp(0.0, double.infinity);

      if (!mounted) return;
      state = AsyncValue.data({
        'payouts': List<Map<String, dynamic>>.from(payoutsRes),
        'totalSales': deliveredCollectedSales,
        'deliveredTotalSales': deliveredTotalSales,
        'allActiveSales': allActiveSales,
        'totalCollected': totalCollected,
        'totalDebts': totalDebts,
        'commissionAmount': commissionAmount,
        'commissionPercent': commissionPercent,
        'enableCommission': enableCommission,
        'netProfit': netProfit,
        'totalWithdrawn': totalWithdrawn,
        'withdrawableBalance': withdrawableBalance,
        'bankName': bankName,
        'bankAccountName': bankAccountName,
        'bankAccountNumber': bankAccountNumber,
      });
    } catch (e, st) {
      if (!mounted) return;
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
