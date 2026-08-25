import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

final adminFinanceProvider = StateNotifierProvider.autoDispose<AdminFinanceNotifier, AsyncValue<Map<String, dynamic>>>((ref) {
  return AdminFinanceNotifier();
});

class AdminFinanceNotifier extends StateNotifier<AsyncValue<Map<String, dynamic>>> {
  AdminFinanceNotifier() : super(const AsyncValue.loading()) {
    loadFinanceData();
  }

  Future<void> loadFinanceData() async {
    try {
      final supabase = Supabase.instance.client;

      final fallbackPayouts = await supabase
          .from('merchant_payout_requests')
          .select()
          .order('created_at', ascending: false);

      final ordersRes = await supabase
          .from('orders')
          .select('total_amount')
          .eq('status', 'delivered');

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

        double totalPlatformCommission = 0.0;
        if (enableCommission) {
          totalPlatformCommission = (totalSales * commissionPercent) / 100;
        }

        List<Map<String, dynamic>> pendingPayoutRequests = [];
        List<Map<String, dynamic>> completedPayouts = [];
        double totalApprovedPayouts = 0.0;

        for (var p in fallbackPayouts) {
          if (p['status'] == 'pending') {
            pendingPayoutRequests.add(Map<String, dynamic>.from(p));
          } else if (p['status'] == 'approved') {
            completedPayouts.add(Map<String, dynamic>.from(p));
            totalApprovedPayouts += (num.tryParse(p['amount']?.toString() ?? '0') ?? 0.0).toDouble();
          } else if (p['status'] == 'rejected') {
            completedPayouts.add(Map<String, dynamic>.from(p));
          }
        }

        state = AsyncValue.data({
          'totalSales': totalSales,
          'totalPlatformCommission': totalPlatformCommission,
          'totalApprovedPayouts': totalApprovedPayouts,
          'pendingPayoutRequests': pendingPayoutRequests,
          'completedPayouts': completedPayouts,
        });

    } catch (e, st) {
      state = AsyncValue.error(e, st);
    }
  }

  Future<void> approvePayout(dynamic id) async {
    try {
      await Supabase.instance.client
          .from('merchant_payout_requests')
          .update({'status': 'approved'})
          .eq('id', id);
      await loadFinanceData();
    } catch (e) {
      rethrow;
    }
  }

  Future<void> rejectPayout(dynamic id) async {
    try {
      await Supabase.instance.client
          .from('merchant_payout_requests')
          .update({'status': 'rejected'})
          .eq('id', id);
      await loadFinanceData();
    } catch (e) {
      rethrow;
    }
  }
}
