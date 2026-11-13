import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/core/network/supabase_safe_query.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/merchant/orders/presentation/screens/merchant_orders_screen.dart';
import 'package:magaiees_marketplace/features/merchant/dashboard/application/merchant_wallet_provider.dart';
import 'package:magaiees_marketplace/features/merchant/finance_report/domain/models/merchant_financial_statement_model.dart';

enum FinanceDateFilter {
  today,
  thisWeek,
  thisMonth,
  last3Months,
  allTime,
  custom,
}

/// Filter providers
final financeDateFilterProvider = StateProvider.autoDispose<FinanceDateFilter>((ref) => FinanceDateFilter.thisMonth);
final financeCustomDateRangeProvider = StateProvider.autoDispose<DateTimeRange?>((ref) => null);
final financeStaffFilterProvider = StateProvider.autoDispose<String?>((ref) => null); // null = all staff

/// Model representing a staff member choice in dropdowns
class StaffFilterOption {
  final String id;
  final String name;
  final String jobTitle;
  const StaffFilterOption({required this.id, required this.name, required this.jobTitle});
}

/// Provider to list all staff members belonging to this store plus the Owner
final storeStaffOptionsProvider = FutureProvider.autoDispose<List<StaffFilterOption>>((ref) async {
  final user = ref.watch(authNotifierProvider).valueOrNull;
  if (user == null) return [];

  final List<StaffFilterOption> options = [
    StaffFilterOption(
      id: 'owner',
      name: user.nameAr?.isNotEmpty == true ? user.nameAr! : user.name,
      jobTitle: 'المالك (مبيعات مباشرة)',
    ),
  ];

  try {
    String? storeId;
    if (user.storeId != null) {
      storeId = user.storeId;
    } else {
      final storeRes = await safeSupabaseQuery(
        () => Supabase.instance.client.from('stores').select('id').eq('owner_id', user.id).limit(1).maybeSingle(),
        fallback: () => null,
      );
      storeId = storeRes?['id']?.toString();
    }

    if (storeId != null) {
      final usersRes = await safeSupabaseQuery(
        () => Supabase.instance.client.from('users').select().eq('role', 'employee'),
        fallback: () => <dynamic>[],
      );

      for (final raw in usersRes) {
        if (raw is Map<String, dynamic>) {
          final perms = raw['permissions'] is Map ? raw['permissions'] as Map : {};
          final staffStoreId = perms['store_id']?.toString() ?? raw['store_id']?.toString();
          if (staffStoreId == storeId) {
            final name = (raw['name_ar'] ?? raw['name'] ?? '').toString();
            final job = (perms['job_title'] ?? 'موظف').toString();
            options.add(StaffFilterOption(
              id: raw['id'].toString(),
              name: name,
              jobTitle: job,
            ));
          }
        }
      }
    }
  } catch (e) {
    debugPrint('Error loading staff filter options: $e');
  }

  return options;
});

/// Comprehensive Financial Statement Provider
final merchantFinancialReportProvider = Provider.autoDispose<AsyncValue<MerchantFinancialStatement>>((ref) {
  final ordersAsync = ref.watch(merchantOrdersProvider);
  final walletAsync = ref.watch(merchantWalletProvider);
  final dateFilter = ref.watch(financeDateFilterProvider);
  final customDateRange = ref.watch(financeCustomDateRangeProvider);
  final selectedStaffId = ref.watch(financeStaffFilterProvider);
  final staffOptionsAsync = ref.watch(storeStaffOptionsProvider);

  if (ordersAsync.isLoading || walletAsync.isLoading) {
    return const AsyncValue.loading();
  }

  if (ordersAsync.hasError) {
    return AsyncValue.error(ordersAsync.error!, ordersAsync.stackTrace ?? StackTrace.current);
  }

  final allOrders = ordersAsync.value ?? [];
  final walletData = walletAsync.valueOrNull ?? {};

  // Commission configuration
  final bool hasCommissionEnabled = walletData['enableCommission'] == true;
  final double commissionRate = (walletData['commissionPercent'] as num?)?.toDouble() ?? 0.0;

  // Payouts
  final payouts = (walletData['payouts'] is List)
      ? List<Map<String, dynamic>>.from(walletData['payouts'] as List)
      : <Map<String, dynamic>>[];

  // Determine Date Window
  final now = DateTime.now();
  DateTime? rangeStart;
  DateTime? rangeEnd;

  switch (dateFilter) {
    case FinanceDateFilter.today:
      rangeStart = DateTime(now.year, now.month, now.day);
      rangeEnd = DateTime(now.year, now.month, now.day, 23, 59, 59);
      break;
    case FinanceDateFilter.thisWeek:
      final startOfWeek = now.subtract(Duration(days: now.weekday % 7));
      rangeStart = DateTime(startOfWeek.year, startOfWeek.month, startOfWeek.day);
      rangeEnd = DateTime(now.year, now.month, now.day, 23, 59, 59);
      break;
    case FinanceDateFilter.thisMonth:
      rangeStart = DateTime(now.year, now.month, 1);
      rangeEnd = DateTime(now.year, now.month, now.day, 23, 59, 59);
      break;
    case FinanceDateFilter.last3Months:
      rangeStart = now.subtract(const Duration(days: 90));
      rangeEnd = DateTime(now.year, now.month, now.day, 23, 59, 59);
      break;
    case FinanceDateFilter.custom:
      if (customDateRange != null) {
        rangeStart = DateTime(customDateRange.start.year, customDateRange.start.month, customDateRange.start.day);
        rangeEnd = DateTime(customDateRange.end.year, customDateRange.end.month, customDateRange.end.day, 23, 59, 59);
      }
      break;
    case FinanceDateFilter.allTime:
      rangeStart = null;
      rangeEnd = null;
      break;
  }

  // 1. Filter Orders by Date & Status
  final List<Map<String, dynamic>> dateFilteredOrders = [];
  for (final o in allOrders) {
    if (o['status'] == 'cancelled') continue;

    if (rangeStart != null || rangeEnd != null) {
      final createdAtStr = o['created_at']?.toString();
      if (createdAtStr != null) {
        final dt = DateTime.tryParse(createdAtStr)?.toLocal();
        if (dt != null) {
          if (rangeStart != null && dt.isBefore(rangeStart)) continue;
          if (rangeEnd != null && dt.isAfter(rangeEnd)) continue;
        }
      }
    }
    dateFilteredOrders.add(o);
  }

  // 2. Extract Staff Attribution helper
  Map<String, String> extractStaffInfo(Map<String, dynamic> order) {
    final notes = (order['notes'] ?? '').toString();
    // Pattern: [staff: <staff_id> | <staff_name>]
    final match = RegExp(r'\[staff:\s*([^\|]+)\s*\|\s*([^\]]+)\]').firstMatch(notes);
    if (match != null) {
      return {
        'id': match.group(1)?.trim() ?? 'owner',
        'name': match.group(2)?.trim() ?? 'المالك (مبيعات مباشرة)',
      };
    }
    return {
      'id': 'owner',
      'name': 'المالك (مبيعات مباشرة)',
    };
  }

  // 3. Aggregate Staff Metrics across dateFilteredOrders
  final Map<String, _StaffAccumulator> staffMap = {};

  // Pre-populate with known staff options if available
  final availableStaff = staffOptionsAsync.valueOrNull ?? [];
  for (final opt in availableStaff) {
    staffMap[opt.id] = _StaffAccumulator(
      staffId: opt.id,
      staffName: opt.name,
      jobTitle: opt.jobTitle,
    );
  }
  if (!staffMap.containsKey('owner')) {
    staffMap['owner'] = _StaffAccumulator(
      staffId: 'owner',
      staffName: 'المالك (مبيعات مباشرة)',
      jobTitle: 'مدير المتجر',
    );
  }

  double grandTotalSalesAcrossAllStaff = 0.0;

  for (final o in dateFilteredOrders) {
    final staffInfo = extractStaffInfo(o);
    final sId = staffInfo['id']!;
    final sName = staffInfo['name']!;

    if (!staffMap.containsKey(sId)) {
      staffMap[sId] = _StaffAccumulator(
        staffId: sId,
        staffName: sName,
        jobTitle: 'موظف مبيعات',
      );
    }

    final total = (num.tryParse(o['total_amount']?.toString() ?? '0') ?? 0.0).toDouble();
    final transport = (num.tryParse(o['transport_fee']?.toString() ?? '0') ?? 0.0).toDouble();
    final comm = hasCommissionEnabled ? (total * commissionRate / 100) : 0.0;

    staffMap[sId]!.ordersCount++;
    staffMap[sId]!.salesTotal += total;
    staffMap[sId]!.transportTotal += transport;
    staffMap[sId]!.platformCommission += comm;

    grandTotalSalesAcrossAllStaff += total;
  }

  // 4. Calculate contribution percentages and staff breakdown list
  final List<StaffSalesMetric> staffBreakdown = staffMap.values.map((acc) {
    final netSales = acc.salesTotal - acc.platformCommission;
    final percent = grandTotalSalesAcrossAllStaff > 0
        ? (acc.salesTotal / grandTotalSalesAcrossAllStaff) * 100
        : 0.0;
    return StaffSalesMetric(
      staffId: acc.staffId,
      staffName: acc.staffName,
      jobTitle: acc.jobTitle,
      ordersCount: acc.ordersCount,
      salesTotal: acc.salesTotal,
      transportTotal: acc.transportTotal,
      platformCommission: acc.platformCommission,
      netSales: netSales,
      contributionPercent: percent,
    );
  }).toList();

  // Sort: highest sales first
  staffBreakdown.sort((a, b) => b.salesTotal.compareTo(a.salesTotal));

  // 5. Filter orders by selectedStaffId (if filtered by a specific staff member)
  final List<Map<String, dynamic>> finalOrders = [];
  for (final o in dateFilteredOrders) {
    if (selectedStaffId != null) {
      final staffInfo = extractStaffInfo(o);
      if (staffInfo['id'] != selectedStaffId) continue;
    }
    finalOrders.add(o);
  }

  // 6. Calculate Financial Inflow, Outflow, and Totals on finalOrders
  double goodsTotal = 0.0;
  double transportTotal = 0.0;
  double offloadingTotal = 0.0;
  double totalInflow = 0.0;
  double collectedPaid = 0.0;
  double platformCommissionTotal = 0.0;

  for (final o in finalOrders) {
    final total = (num.tryParse(o['total_amount']?.toString() ?? '0') ?? 0.0).toDouble();
    double paid = (num.tryParse(o['paid_amount']?.toString() ?? '0') ?? 0.0).toDouble();
    if (o['payment_status'] == 'paid' && paid <= 0 && total > 0) {
      paid = total;
    }
    final effectivePaid = paid.clamp(0.0, total);
    final transport = (num.tryParse(o['transport_fee']?.toString() ?? '0') ?? 0.0).toDouble();
    final offloading = (num.tryParse(o['offloading_fee']?.toString() ?? '0') ?? 0.0).toDouble();
    final goods = (total - transport - offloading).clamp(0.0, double.infinity);
    final comm = hasCommissionEnabled ? (total * commissionRate / 100) : 0.0;

    totalInflow += total;
    goodsTotal += goods;
    transportTotal += transport;
    offloadingTotal += offloading;
    collectedPaid += effectivePaid;
    platformCommissionTotal += comm;
  }

  // Payouts calculation (applies when viewing store total; for individual staff, payouts are 0)
  double payoutsTotal = 0.0;
  if (selectedStaffId == null || selectedStaffId == 'owner') {
    for (final p in payouts) {
      if (p['status'] == 'approved' || p['status'] == 'pending') {
        final amount = (num.tryParse(p['amount']?.toString() ?? '0') ?? 0.0).toDouble();
        if (rangeStart != null || rangeEnd != null) {
          final dtStr = p['created_at']?.toString();
          if (dtStr != null) {
            final dt = DateTime.tryParse(dtStr)?.toLocal();
            if (dt != null) {
              if (rangeStart != null && dt.isBefore(rangeStart)) continue;
              if (rangeEnd != null && dt.isAfter(rangeEnd)) continue;
            }
          }
        }
        payoutsTotal += amount;
      }
    }
  }

  final double totalOutflow = platformCommissionTotal + payoutsTotal;
  final double netCumulative = totalInflow - totalOutflow;
  final double unpaidDebts = (totalInflow - collectedPaid).clamp(0.0, double.infinity);

  return AsyncValue.data(
    MerchantFinancialStatement(
      totalInflow: totalInflow,
      goodsTotal: goodsTotal,
      transportTotal: transportTotal,
      offloadingTotal: offloadingTotal,
      totalOutflow: totalOutflow,
      platformCommissionTotal: platformCommissionTotal,
      commissionRate: commissionRate,
      hasCommissionEnabled: hasCommissionEnabled,
      payoutsTotal: payoutsTotal,
      netCumulative: netCumulative,
      collectedPaid: collectedPaid,
      unpaidDebts: unpaidDebts,
      totalOrdersCount: finalOrders.length,
      staffBreakdown: staffBreakdown,
      orders: finalOrders,
    ),
  );
});

class _StaffAccumulator {
  final String staffId;
  final String staffName;
  final String jobTitle;
  int ordersCount = 0;
  double salesTotal = 0.0;
  double transportTotal = 0.0;
  double platformCommission = 0.0;

  _StaffAccumulator({
    required this.staffId,
    required this.staffName,
    required this.jobTitle,
  });
}
