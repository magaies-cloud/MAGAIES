import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:intl/intl.dart';
import 'package:magaiees_marketplace/core/utils/order_number_formatter.dart';

final adminFinanceProvider = StateNotifierProvider<AdminFinanceNotifier, AsyncValue<Map<String, dynamic>>>((ref) {
  return AdminFinanceNotifier();
});

class AdminFinanceNotifier extends StateNotifier<AsyncValue<Map<String, dynamic>>> {
  AdminFinanceNotifier() : super(const AsyncValue.loading()) {
    loadFinanceData();
  }

  Future<void> loadFinanceData() async {
    try {
      final supabase = Supabase.instance.client;

      // Parallel execution of all 7 queries simultaneously via Future.wait
      final results = await Future.wait([
        supabase.from('merchant_payout_requests').select('*').order('created_at', ascending: false).limit(10000).catchError((_) => []),
        supabase.from('stores').select('id, name, name_ar, owner_id, custom_commission_percentage, has_custom_commission').limit(10000).catchError((_) => []),
        supabase.from('orders').select('id, order_number, total_amount, paid_amount, status, payment_status, created_at, store_id, payment_method, notes, transport_fee, offloading_fee').order('created_at', ascending: false).limit(10000).catchError((_) => []),
        supabase.from('payments').select('*').limit(10000).catchError((_) => []),
        supabase.from('gamification_settings').select('enable_commission, commission_percentage').eq('id', 1).maybeSingle().catchError((_) => null),
        supabase.from('ads').select('id, title, company, price, created_at').limit(10000).catchError((_) => []),
        supabase.from('users').select('id, name, permissions').limit(10000).catchError((_) => []),
      ]);

      final fallbackPayouts = results[0] as List<dynamic>;
      final storesRes = results[1] as List<dynamic>;
      final ordersRes = results[2] as List<dynamic>;
      final paymentsRes = results[3] as List<dynamic>;
      final settingsRes = results[4] as Map<String, dynamic>?;
      final adsRes = results[5] as List<dynamic>;

      final usersRes = results[6] as List<dynamic>;

      // Map user permissions (commissions) to store owners
      Map<String, double> userCommissionMap = {};
      for (var u in usersRes) {
        final uId = u['id']?.toString() ?? '';
        final perms = u['permissions'] as Map<String, dynamic>?;
        if (perms != null) {
          if (perms['custom_commission_percentage'] != null) {
            final rate = (num.tryParse(perms['custom_commission_percentage'].toString()) ?? 0.0).toDouble();
            if (rate > 0) userCommissionMap[uId] = rate;
          }
          if (perms['category_commissions'] is Map && (perms['category_commissions'] as Map).isNotEmpty) {
            final catComms = perms['category_commissions'] as Map;
            // Take highest or default category commission if general rate not set
            for (var val in catComms.values) {
              final r = (num.tryParse(val.toString()) ?? 0.0).toDouble();
              if (r > 0) {
                userCommissionMap.putIfAbsent(uId, () => r);
              }
            }
          }
        }
      }

      // Process Stores lookup map
      Map<String, String> storeNames = {};
      Map<String, String> storeOwnerToStoreId = {};
      Map<String, double> storeCustomCommission = {};
      for (var s in storesRes) {
        final id = s['id']?.toString() ?? '';
        final nameAr = s['name_ar']?.toString();
        final name = s['name']?.toString() ?? '';
        storeNames[id] = (nameAr != null && nameAr.isNotEmpty) ? nameAr : name;
        final ownerId = s['owner_id']?.toString() ?? '';
        if (ownerId.isNotEmpty) {
          storeOwnerToStoreId[ownerId] = id;
        }
        
        // Priority 1: Store custom commission in stores table
        final hasCustom = s['has_custom_commission'] == true;
        if (hasCustom && s['custom_commission_percentage'] != null) {
          storeCustomCommission[id] = (num.tryParse(s['custom_commission_percentage'].toString()) ?? 0.0).toDouble();
        } else if (ownerId.isNotEmpty && userCommissionMap.containsKey(ownerId)) {
          // Priority 2: User permissions custom commission
          storeCustomCommission[id] = userCommissionMap[ownerId]!;
        }
      }

      // Process Order Payments map
      Map<String, List<Map<String, dynamic>>> orderPaymentsMap = {};
      for (var p in paymentsRes) {
        final ordId = p['order_id']?.toString() ?? '';
        if (ordId.isNotEmpty) {
          orderPaymentsMap.putIfAbsent(ordId, () => []).add(Map<String, dynamic>.from(p));
        }
      }

      double totalSales = 0;
      for (var o in ordersRes) {
        final status = o['status']?.toString().toLowerCase() ?? 'pending';
        final isCancelled = status == 'cancelled' || status == 'ملغي' || status == 'ملغى';
        if (!isCancelled) {
          totalSales += (num.tryParse(o['total_amount']?.toString() ?? '0') ?? 0.0).toDouble();
        }
      }

      bool enableCommission = false;
      double commissionPercent = 0.0;
      if (settingsRes != null) {
        enableCommission = settingsRes['enable_commission'] == true;
        commissionPercent = (num.tryParse(settingsRes['commission_percentage']?.toString() ?? '0') ?? 0.0).toDouble();
      }

      double totalPlatformCommission = 0.0;

      // --- 5. Process Payouts ---
      List<Map<String, dynamic>> pendingPayoutRequests = [];
      List<Map<String, dynamic>> completedPayouts = [];
      double totalApprovedPayouts = 0.0;

      for (var p in fallbackPayouts) {
        final pMap = Map<String, dynamic>.from(p);
        // Resolve store name from merchant_id -> stores.owner_id
        final merchantId = pMap['merchant_id']?.toString() ?? '';
        final storeId = storeOwnerToStoreId[merchantId] ?? '';
        pMap['_store_name'] = storeNames[storeId] ?? 'متجر غير معروف';
        pMap['_store_id'] = storeId;

        if (pMap['status'] == 'pending') {
          pendingPayoutRequests.add(pMap);
        } else if (pMap['status'] == 'approved') {
          completedPayouts.add(pMap);
          totalApprovedPayouts += (num.tryParse(pMap['amount']?.toString() ?? '0') ?? 0.0).toDouble();
        } else if (pMap['status'] == 'rejected') {
          completedPayouts.add(pMap);
        }
      }

      // --- 6. Process Ads Revenue (Already loaded in parallel) ---

      double totalAdsRevenue = 0.0;
      for (var a in adsRes) {
        final adPrice = (num.tryParse(a['price']?.toString() ?? '0') ?? 0.0).toDouble();
        if (adPrice > 0) totalAdsRevenue += adPrice;
      }

      // ====================================
      // Build Full Ledger (كشف الحساب)
      // ====================================
      List<Map<String, dynamic>> ledger = [];
      double totalCollected = 0.0;
      double totalReceivables = 0.0;

      // 1. Add Orders (Income & Receivables) — GAAP Financial Accounting Standard
      for (var o in ordersRes) {
        final status = o['status']?.toString().toLowerCase() ?? 'pending';
        final isCancelled = status == 'cancelled' || status == 'ملغي' || status == 'ملغى';
        if (isCancelled) continue; // Skip cancelled orders completely from revenue/receivables

        final totalAmount = (num.tryParse(o['total_amount']?.toString() ?? '0') ?? 0.0).toDouble();
        final paidAmount = (num.tryParse(o['paid_amount']?.toString() ?? '0') ?? 0.0).toDouble();
        final payStatus = o['payment_status']?.toString() ?? 'unpaid';
        final isDelivered = status == 'delivered' || status == 'completed' || status == 'مكتمل' || status == 'تم التسليم';

        // Determine actually collected funds vs receivables
        final amount = (paidAmount > 0) ? paidAmount : ((payStatus == 'paid' || isDelivered) ? totalAmount : 0.0);
        if (amount <= 0 && !isDelivered && payStatus != 'paid') {
          // Track strictly as receivable
          totalReceivables += totalAmount;
          continue;
        }
        final storeId = o['store_id']?.toString() ?? '';
        final isPlatformExclusive = storeId.isEmpty || storeId == 'magaiees';

        double appliedRate = 0.0;
        double commission = 0.0;
        double merchantShare = 0.0;

        if (isPlatformExclusive) {
          // Direct platform exclusive sale — 100% platform revenue
          commission = amount;
          merchantShare = 0.0;
          appliedRate = 100.0;
        } else {
          // Marketplace Merchant Sale
          if (storeCustomCommission.containsKey(storeId)) {
            appliedRate = storeCustomCommission[storeId]!;
          } else if (enableCommission) {
            appliedRate = commissionPercent;
          }
          commission = (amount * appliedRate) / 100;
          merchantShare = amount - commission;
        }

        totalCollected += amount;
        totalPlatformCommission += commission;
        
        // Track receivables (what is left to be paid on non-cancelled orders)
        final remaining = totalAmount - amount;
        if (remaining > 0) totalReceivables += remaining;

        final storeName = storeNames[storeId] ?? 'متجر غير معروف';
        final orderId = o['id']?.toString() ?? '';
        final shortId = OrderNumberFormatter.formatFromMap(o);
        final payMethod = o['payment_method']?.toString() ?? 'تحويل بنكي';
        
        final paymentsList = orderPaymentsMap[orderId] ?? [];
        String refNumber = '';
        String payBank = '';
        if (paymentsList.isNotEmpty) {
          final lastPay = paymentsList.last;
          refNumber = lastPay['reference_number']?.toString() ?? '';
          payBank = lastPay['bank_name']?.toString() ?? '';
        }

        // Payment status label
        String payLabel = '';
        if (payStatus == 'paid') {
          payLabel = '✓ مدفوع';
        } else if (payStatus == 'partial') {
          payLabel = '◐ جزئي';
        } else {
          payLabel = '✗ غير مدفوع';
        }
        
        final notes = o['notes']?.toString();
        String? staffName;
        if (notes != null) {
          final match = RegExp(r'\[staff:\s*([^\|]+)\s*\|\s*([^\]]+)\]').firstMatch(notes);
          staffName = match?.group(2)?.trim();
        }
        final transportFee = (num.tryParse(o['transport_fee']?.toString() ?? '0') ?? 0.0).toDouble();
        final offloadingFee = (num.tryParse(o['offloading_fee']?.toString() ?? '0') ?? 0.0).toDouble();

        ledger.add({
          'id': 'ORD-$shortId',
          'type': 'order',
          'source': 'طلبات',
          'category': 'income',
          'store_name': storeName,
          'store_id': storeId,
          'title': 'مبيعات طلب #$shortId ($payLabel)',
          'description': storeName,
          'amount': amount,
          'total_order_amount': totalAmount,
          'commission': commission,
          'commission_rate': appliedRate,
          'order_number': o['order_number']?.toString() ?? shortId,
          'merchant_share': merchantShare,
          'payment_status': payStatus,
          'reference_number': refNumber,
          'bank_name': payBank.isNotEmpty ? payBank : payMethod,
          'created_at': o['created_at'],
          'staff_name': staffName,
          'transport_fee': transportFee,
          'offloading_fee': offloadingFee,
        });
      }

      // 2. Add Ads (Income) — platform revenue only
      for (var a in adsRes) {
        final price = (num.tryParse(a['price']?.toString() ?? '0') ?? 0.0).toDouble();
        if (price <= 0) continue; // تخطي الإعلانات المجانية
        totalCollected += price;

        final rawAdId = a['id']?.toString() ?? '';
        final shortAdId = rawAdId.length >= 8 ? rawAdId.substring(0, 8) : rawAdId;
        ledger.add({
          'id': 'AD-$shortAdId',
          'type': 'ad',
          'source': 'إعلانات',
          'category': 'income',
          'store_name': a['company'] ?? 'مقاييس',
          'store_id': '',
          'title': 'إيراد إعلان: ${a['title'] ?? 'بدون عنوان'}',
          'description': a['company'] ?? '',
          'amount': price,
          'commission': price, // 100% is platform
          'merchant_share': 0.0,
          'bank_name': 'إيراد مباشر',
          'created_at': a['created_at'],
        });
      }

      // 3. Add Approved Payouts (Expense)
      for (var p in completedPayouts) {
        if (p['status'] == 'approved') {
          final amount = (num.tryParse(p['amount']?.toString() ?? '0') ?? 0.0).toDouble();
          final storeName = p['_store_name'] ?? 'متجر غير معروف';
          final storeId = p['_store_id'] ?? '';
          final payId = p['id']?.toString() ?? '';
          final shortId = payId.length > 8 ? payId.substring(0, 8) : payId;
          final bankName = p['bank_name']?.toString() ?? '-';

          ledger.add({
            'id': 'PAY-$shortId',
            'type': 'payout',
            'source': 'تحويلات',
            'category': 'expense',
            'store_name': storeName,
            'store_id': storeId,
            'title': 'تحويل مستحقات إلى $storeName',
            'description': storeName,
            'amount': amount,
            'commission': 0.0,
            'merchant_share': 0.0,
            'bank_name': bankName,
            'created_at': p['created_at'],
          });
        }
      }

      // Sort ledger chronologically (oldest first for running balance calc)
      ledger.sort((a, b) {
        final dateA = DateTime.tryParse(a['created_at']?.toString() ?? '') ?? DateTime.fromMillisecondsSinceEpoch(0);
        final dateB = DateTime.tryParse(b['created_at']?.toString() ?? '') ?? DateTime.fromMillisecondsSinceEpoch(0);
        return dateA.compareTo(dateB);
      });

      // Calculate running balance
      double runningBalance = 0.0;
      for (var entry in ledger) {
        if (entry['category'] == 'income') {
          runningBalance += (entry['amount'] as num).toDouble();
        } else {
          runningBalance -= (entry['amount'] as num).toDouble();
        }
        entry['running_balance'] = runningBalance;
      }

      // Keep chronological order (oldest first) so running balance accumulates naturally

      // Collect unique store names for filter dropdown
      final Set<String> merchantNames = {};
      for (var entry in ledger) {
        final name = entry['store_name']?.toString() ?? '';
        if (name.isNotEmpty && name != 'مقاييس') {
          merchantNames.add(name);
        }
      }

      // Per-merchant/source summary (الأرصدة العامة)
      Map<String, Map<String, double>> merchantSummary = {};
      for (var entry in ledger) {
        final name = entry['store_name']?.toString() ?? 'أخرى';
        if (name.isEmpty) continue;
        merchantSummary.putIfAbsent(name, () => {'income': 0.0, 'expense': 0.0, 'commission': 0.0, 'merchant_share': 0.0});
        if (entry['category'] == 'income') {
          merchantSummary[name]!['income'] = (merchantSummary[name]!['income'] ?? 0) + (entry['amount'] as num).toDouble();
          merchantSummary[name]!['commission'] = (merchantSummary[name]!['commission'] ?? 0) + ((entry['commission'] as num?)?.toDouble() ?? 0.0);
          merchantSummary[name]!['merchant_share'] = (merchantSummary[name]!['merchant_share'] ?? 0) + ((entry['merchant_share'] as num?)?.toDouble() ?? 0.0);
        } else if (entry['category'] == 'expense') {
          merchantSummary[name]!['expense'] = (merchantSummary[name]!['expense'] ?? 0) + (entry['amount'] as num).toDouble();
        }
      }

      // Summary values
      double magaieesNetProfit = totalPlatformCommission + totalAdsRevenue;
      double bankBalance = totalCollected - totalApprovedPayouts;
      double totalMerchantShare = 0.0;
      for (var entry in ledger) {
        if (entry['type'] == 'order') {
          totalMerchantShare += (entry['merchant_share'] as num?)?.toDouble() ?? 0.0;
        }
      }

      // Generate monthly revenue chart data
      Map<String, double> monthlyIncomeMap = {};
      Map<String, double> monthlyExpenseMap = {};
      final now = DateTime.now();
      for (int i = 5; i >= 0; i--) {
        final month = DateTime(now.year, now.month - i, 1);
        final key = DateFormat('yyyy-MM').format(month);
        monthlyIncomeMap[key] = 0.0;
        monthlyExpenseMap[key] = 0.0;
      }

      for (var t in ledger) {
        final dateStr = t['created_at']?.toString() ?? '';
        final date = DateTime.tryParse(dateStr);
        if (date != null) {
          final key = DateFormat('yyyy-MM').format(date);
          if (t['category'] == 'income') {
            if (monthlyIncomeMap.containsKey(key)) {
              monthlyIncomeMap[key] = (monthlyIncomeMap[key] ?? 0) + (t['amount'] as num).toDouble();
            }
          } else {
            if (monthlyExpenseMap.containsKey(key)) {
              monthlyExpenseMap[key] = (monthlyExpenseMap[key] ?? 0) + (t['amount'] as num).toDouble();
            }
          }
        }
      }

      List<Map<String, dynamic>> chartData = [];
      monthlyIncomeMap.forEach((key, income) {
        chartData.add({
          'month': key,
          'income': income,
          'expense': monthlyExpenseMap[key] ?? 0.0,
        });
      });
      chartData.sort((a, b) => a['month'].compareTo(b['month']));

      state = AsyncValue.data({
        'totalCollected': totalCollected,
        'totalApprovedPayouts': totalApprovedPayouts,
        'bankBalance': bankBalance,
        'magaieesNetProfit': magaieesNetProfit,
        'totalAdsRevenue': totalAdsRevenue,
        'totalOrdersRevenue': totalSales,
        'totalPlatformCommission': totalPlatformCommission,
        'totalMerchantShare': totalMerchantShare,
        'enableCommission': enableCommission,
        'commissionPercent': commissionPercent,
        'totalReceivables': totalReceivables,
        'pendingPayoutRequests': pendingPayoutRequests,
        'ledger': ledger,
        'chartData': chartData,
        'merchantNames': merchantNames.toList()..sort(),
        'merchantSummary': merchantSummary,
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
