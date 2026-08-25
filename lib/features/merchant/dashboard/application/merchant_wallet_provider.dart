import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';

final merchantWalletProvider = StateNotifierProvider.autoDispose<MerchantWalletNotifier, AsyncValue<Map<String, dynamic>>>((ref) {
  return MerchantWalletNotifier(ref);
});

class MerchantWalletNotifier extends StateNotifier<AsyncValue<Map<String, dynamic>>> {
  final Ref ref;
  MerchantWalletNotifier(this.ref) : super(const AsyncValue.loading()) {
    loadWalletData();
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
          
      // 2. Fetch all delivered orders to calculate total sales
      final ordersRes = await Supabase.instance.client
          .from('orders')
          .select('total_amount, paid_amount')
          .eq('merchant_id', user.id)
          .eq('status', 'delivered');

      // 3. Fetch Gamification settings for commission
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

      // Calculate total approved payouts to subtract from net profit
      double totalWithdrawn = 0;
      for (var p in payoutsRes) {
        if (p['status'] == 'approved') {
          totalWithdrawn += (num.tryParse(p['amount']?.toString() ?? '0') ?? 0.0).toDouble();
        }
      }

      double withdrawableBalance = netProfit - totalWithdrawn;
      if (withdrawableBalance < 0) withdrawableBalance = 0;

      state = AsyncValue.data({
        'payouts': List<Map<String, dynamic>>.from(payoutsRes),
        'totalSales': totalSales,
        'commissionAmount': commissionAmount,
        'commissionPercent': commissionPercent,
        'enableCommission': enableCommission,
        'netProfit': netProfit,
        'withdrawableBalance': withdrawableBalance,
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
        final bal = currentState['withdrawableBalance'] as double;
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
      loadWalletData();
    } catch (e) {
      rethrow;
    }
  }
}
