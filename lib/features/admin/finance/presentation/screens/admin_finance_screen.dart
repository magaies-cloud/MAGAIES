import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:go_router/go_router.dart';
import 'package:magaiees_marketplace/routing/route_names.dart';
import '../../application/admin_finance_provider.dart';
import '../../application/banks_provider.dart';
import 'package:magaiees_marketplace/features/admin/presentation/widgets/admin_error_view.dart';

class AdminFinanceScreen extends ConsumerStatefulWidget {
  const AdminFinanceScreen({Key? key}) : super(key: key);

  @override
  ConsumerState<AdminFinanceScreen> createState() => _AdminFinanceScreenState();
}

class _AdminFinanceScreenState extends ConsumerState<AdminFinanceScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  static const String _currency = 'ج.س';

  // Filters
  String _ledgerFilter = 'all';
  String _merchantFilter = '';
  final TextEditingController _merchantSearchCtrl = TextEditingController();
  String _bankFilter = '';
  final TextEditingController _bankSearchCtrl = TextEditingController();
  bool _sortNewestFirst = true;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    _merchantSearchCtrl.dispose();
    _bankSearchCtrl.dispose();
    super.dispose();
  }

  String _fmt(double v) {
    final f = NumberFormat('#,##0.00', 'ar');
    return '${f.format(v)} $_currency';
  }

  String _normalizeBankName(String bank, bool isRtl) {
    if (bank.isEmpty || bank == '-') {
      return isRtl ? 'غير محدد' : 'Unspecified';
    }
    final lower = bank.toLowerCase().trim();
    if (lower.contains('bankak') || lower.contains('خرطوم') || lower.contains('بنكك') || lower == 'bb' || lower == 'gg') {
      return isRtl ? 'بنك الخرطوم (بنكك)' : 'Bank of Khartoum (Bankak)';
    } else if (lower.contains('ocash') || lower.contains('أمدرمان') || lower.contains('امدرمان') || lower.contains('اوكاش') || lower.contains('أوكاش')) {
      return isRtl ? 'بنك أمدرمان (أوكاش)' : 'Omdurman Bank (O-Cash)';
    } else if (lower.contains('fawry') || lower.contains('فيصل') || lower.contains('فوري')) {
      return isRtl ? 'بنك فيصل (فوري)' : 'Faisal Bank (Fawry)';
    } else if (lower.contains('cash') || lower.contains('نقد') || lower.contains('نقدا')) {
      return isRtl ? 'نقداً (استلام باليد)' : 'Cash (In-person)';
    } else if (lower.contains('نيلين')) {
      return 'بنك النيلين';
    } else if (lower.contains('أهلي') || lower.contains('اهلي')) {
      return 'البنك الأهلي السوداني';
    } else if (lower.contains('جزيرة')) {
      return 'بنك الجزيرة السوداني الأردني';
    } else if (lower.contains('إيراد مباشر') || lower.contains('direct')) {
      return isRtl ? 'إيراد مباشر (حساب المنصة)' : 'Direct Revenue';
    }
    return bank.trim();
  }

  @override
  Widget build(BuildContext context) {
    final financeState = ref.watch(adminFinanceProvider);
    final banksState = ref.watch(banksProvider);
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: financeState.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, st) => AdminErrorView(
          error: err,
          stackTrace: st,
          onRetry: () => ref.refresh(adminFinanceProvider),
        ),
        data: (data) {
          final totalCollected = (data['totalCollected'] as num?)?.toDouble() ?? 0.0;
          final totalPaid = (data['totalApprovedPayouts'] as num?)?.toDouble() ?? 0.0;
          final bankBalance = (data['bankBalance'] as num?)?.toDouble() ?? 0.0;
          final netProfit = (data['magaieesNetProfit'] as num?)?.toDouble() ?? 0.0;
          final adsRev = (data['totalAdsRevenue'] as num?)?.toDouble() ?? 0.0;
          final ordersRev = (data['totalOrdersRevenue'] as num?)?.toDouble() ?? 0.0;
          final totalCommission = (data['totalPlatformCommission'] as num?)?.toDouble() ?? 0.0;
          final receivables = (data['totalReceivables'] as num?)?.toDouble() ?? 0.0;
          final pending = data['pendingPayoutRequests'] as List<Map<String, dynamic>>? ?? [];
          final ledger = data['ledger'] as List<Map<String, dynamic>>? ?? [];
          final chartData = data['chartData'] as List<Map<String, dynamic>>? ?? [];
          final merchantNames = data['merchantNames'] as List<String>? ?? [];
          final merchantSummary = data['merchantSummary'] as Map<String, Map<String, double>>? ?? {};
          final platformBanks = banksState.valueOrNull ?? [];

          final totalMerchantShare = (data['totalMerchantShare'] as num?)?.toDouble() ?? (ordersRev - totalCommission);
          final totalMerchantLiabilities = (totalMerchantShare - totalPaid).clamp(0.0, double.infinity);

          return NestedScrollView(
            headerSliverBuilder: (context, innerBoxIsScrolled) {
              return [
                SliverToBoxAdapter(
                  child: _buildHeader(
                    collected: totalCollected,
                    paid: totalPaid,
                    bankBalance: bankBalance,
                    netProfit: netProfit,
                    ads: adsRev,
                    orders: ordersRev,
                    commission: totalCommission,
                    merchantLiabilities: totalMerchantLiabilities,
                    receivables: receivables,
                    ledger: ledger,
                    platformBanks: platformBanks,
                    isRtl: isRtl,
                  ),
                ),
                SliverPersistentHeader(
                  pinned: true,
                  delegate: _SliverAppBarDelegate(
                    minHeight: 65.0,
                    maxHeight: 65.0,
                    child: Container(
                      color: const Color(0xFFF8FAFC),
                      alignment: Alignment.center,
                      child: _buildTabBar(isRtl, pending.length),
                    ),
                  ),
                ),
              ];
            },
            body: TabBarView(
              controller: _tabController,
              children: [
                _buildOverviewTab(chartData, ledger, ordersRev, totalCommission, totalMerchantLiabilities, platformBanks, isRtl),
                _buildLedgerTab(ledger, merchantNames, merchantSummary, platformBanks, isRtl),
                _buildBalancesTab(merchantSummary, ledger, isRtl),
                _buildPendingTab(pending, isRtl),
              ],
            ),
          );
        },
      ),
    );
  }

  // ═══════════════════════════════════════════
  // HEADER — PROFESSIONAL SAAS STYLE (Light Theme)
  // ═══════════════════════════════════════════
  Widget _buildHeader({
    required double collected,
    required double paid,
    required double bankBalance,
    required double netProfit,
    required double ads,
    required double orders,
    required double commission,
    required double merchantLiabilities,
    required double receivables,
    required List<Map<String, dynamic>> ledger,
    required List<PlatformBank> platformBanks,
    required bool isRtl,
  }) {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: Colors.white,
        border: const Border(bottom: BorderSide(color: Color(0xFFE2E8F0), width: 1.5)),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 10, offset: const Offset(0, 4))
        ],
      ),
      padding: const EdgeInsets.fromLTRB(28, 24, 28, 24),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        // Top Action Bar
        Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: const Color(0xFFDBEAFE)),
                ),
                child: const Icon(Icons.account_balance_rounded, color: Color(0xFF2563EB), size: 28),
              ),
              const SizedBox(width: 16),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    isRtl ? 'المالية والحسابات العامة' : 'Finance & Accounts Center',
                    style: const TextStyle(color: Color(0xFF0F172A), fontSize: 24, fontWeight: FontWeight.w900, letterSpacing: -0.5),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    isRtl ? 'متابعة السيولة النقدية، مستحقات التجار، وأرباح المنصة' : 'Track platform liquidity, merchant funds & revenue',
                    style: const TextStyle(color: Color(0xFF64748B), fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                ],
              ),
            ],
          ),
          Wrap(
            spacing: 12,
            children: [
              // Bank Statement Generator
              FilledButton.icon(
                style: FilledButton.styleFrom(
                  backgroundColor: Colors.white,
                  foregroundColor: const Color(0xFF047857),
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                    side: const BorderSide(color: Color(0xFF047857), width: 1.5),
                  ),
                  elevation: 0,
                ),
                icon: const Icon(Icons.receipt_long_rounded, size: 18),
                label: Text(
                  isRtl ? '📄 كشف حساب بنكي' : 'Bank Statement',
                  style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13),
                ),
                onPressed: () => _openBankSelectorDialog(context, ledger, platformBanks, isRtl),
              ),

              // Manage Banks
              FilledButton.icon(
                style: FilledButton.styleFrom(
                  backgroundColor: Colors.white,
                  foregroundColor: const Color(0xFF2563EB),
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                    side: const BorderSide(color: Color(0xFF2563EB), width: 1.5),
                  ),
                  elevation: 0,
                ),
                icon: const Icon(Icons.account_balance, size: 18),
                label: Text(
                  isRtl ? 'إدارة البنوك والمحافظ' : 'Manage Banks',
                  style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13),
                ),
                onPressed: () => _showBankManagementDialog(context, isRtl),
              ),

              // Commission
              FilledButton.icon(
                style: FilledButton.styleFrom(
                  backgroundColor: Colors.white,
                  foregroundColor: const Color(0xFFD97706),
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                    side: const BorderSide(color: Color(0xFFD97706), width: 1.5),
                  ),
                  elevation: 0,
                ),
                icon: const Icon(Icons.percent_rounded, size: 18),
                label: Text(
                  isRtl ? 'إعدادات العمولة' : 'Commission',
                  style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13),
                ),
                onPressed: () => _showCommissionSettingsDialog(context, isRtl),
              ),

              // Refresh
              IconButton(
                style: IconButton.styleFrom(
                  backgroundColor: const Color(0xFFF1F5F9),
                  foregroundColor: const Color(0xFF475569),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  padding: const EdgeInsets.all(12),
                ),
                icon: const Icon(Icons.refresh, size: 20),
                onPressed: () => ref.refresh(adminFinanceProvider),
                tooltip: isRtl ? 'تحديث البيانات' : 'Refresh',
              ),
            ],
          ),
        ]),
        const SizedBox(height: 28),

        // Main 4 Executive KPI Cards
        LayoutBuilder(builder: (ctx, c) {
          final isWide = c.maxWidth > 950;
          final cards = [
            _executiveCard(
              title: isRtl ? 'السيولة المتوفرة في البنوك' : 'Total Bank Liquidity',
              value: bankBalance,
              subtitle: isRtl ? 'إجمالي المحصل في الحسابات' : 'Total in platform accounts',
              icon: Icons.account_balance_wallet_rounded,
              color: const Color(0xFF0284C7),
              bgColor: const Color(0xFFF0F9FF),
              isRtl: isRtl,
            ),
            _executiveCard(
              title: isRtl ? 'مستحقات وأرصدة التجار' : 'Merchant Funds (Payable)',
              value: merchantLiabilities,
              subtitle: isRtl ? 'أموال للتجار بانتظار السحب' : 'Earnings pending withdrawal',
              icon: Icons.storefront_rounded,
              color: const Color(0xFFD97706),
              bgColor: const Color(0xFFFFFBEB),
              isRtl: isRtl,
            ),
            _executiveCard(
              title: isRtl ? 'أرباح وعمولات المنصة' : 'Platform Net Revenue',
              value: netProfit,
              subtitle: isRtl ? 'العمولات (${_fmt(commission)}) + الإعلانات' : 'Commissions + Ads profit',
              icon: Icons.verified_rounded,
              color: const Color(0xFF059669),
              bgColor: const Color(0xFFECFDF5),
              isRtl: isRtl,
            ),
            _executiveCard(
              title: isRtl ? 'إجمالي المسحوبات المحولة' : 'Total Payouts Completed',
              value: paid,
              subtitle: isRtl ? 'مبالغ محولة لحسابات التجار' : 'Successfully transferred',
              icon: Icons.outbox_rounded,
              color: const Color(0xFFDC2626),
              bgColor: const Color(0xFFFEF2F2),
              isRtl: isRtl,
            ),
          ];

          if (isWide) {
            return Row(
              children: cards.map((card) => Expanded(child: Padding(padding: const EdgeInsets.symmetric(horizontal: 6), child: card))).toList(),
            );
          }
          return Wrap(
            spacing: 12,
            runSpacing: 12,
            children: cards.map((card) => SizedBox(width: (c.maxWidth - 12) / 2, child: card)).toList(),
          );
        }),
      ]),
    );
  }

  Widget _executiveCard({
    required String title,
    required double value,
    required String subtitle,
    required IconData icon,
    required Color color,
    required Color bgColor,
    required bool isRtl,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: color.withValues(alpha: 0.25), width: 1.2),
        boxShadow: [
          BoxShadow(color: color.withValues(alpha: 0.04), blurRadius: 8, offset: const Offset(0, 3)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              Container(
                padding: const EdgeInsets.all(7),
                decoration: BoxDecoration(color: bgColor, borderRadius: BorderRadius.circular(8)),
                child: Icon(icon, color: color, size: 18),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  title,
                  style: const TextStyle(color: Color(0xFF64748B), fontSize: 12, fontWeight: FontWeight.w700),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          SizedBox(
            height: 26,
            child: FittedBox(
              fit: BoxFit.scaleDown,
              alignment: isRtl ? Alignment.centerRight : Alignment.centerLeft,
              child: Text(
                _fmt(value),
                style: const TextStyle(
                  color: Color(0xFF0F172A),
                  fontSize: 20,
                  fontWeight: FontWeight.w900,
                  letterSpacing: -0.5,
                ),
              ),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            subtitle,
            style: const TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold),
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  // ═══════════════════════════════════════════
  // TAB BAR — CLEAN PROFESSIONAL
  // ═══════════════════════════════════════════
  Widget _buildTabBar(bool isRtl, int pendingCount) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 28, vertical: 8),
      decoration: BoxDecoration(
        color: const Color(0xFFF1F5F9),
        borderRadius: BorderRadius.circular(12),
      ),
      padding: const EdgeInsets.all(4),
      child: TabBar(
        controller: _tabController,
        labelColor: const Color(0xFF0F172A),
        unselectedLabelColor: const Color(0xFF64748B),
        indicator: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(8),
          boxShadow: [
            BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 4, offset: const Offset(0, 2))
          ],
        ),
        dividerColor: Colors.transparent,
        labelStyle: const TextStyle(fontWeight: FontWeight.w900, fontSize: 14),
        unselectedLabelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5),
        tabs: [
          Tab(
            height: 44,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.analytics_rounded, size: 18),
                const SizedBox(width: 8),
                Text(isRtl ? 'المؤشرات والسيولة' : 'Overview'),
              ],
            ),
          ),
          Tab(
            height: 44,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.receipt_long_rounded, size: 18),
                const SizedBox(width: 8),
                Text(isRtl ? 'كشف الحساب التفصيلي' : 'Ledger'),
              ],
            ),
          ),
          Tab(
            height: 44,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.people_alt_rounded, size: 18),
                const SizedBox(width: 8),
                Text(isRtl ? 'أرصدة التجار' : 'Merchants'),
              ],
            ),
          ),
          Tab(
            height: 44,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.outbox_rounded, size: 18),
                const SizedBox(width: 8),
                Text(isRtl ? 'طلبات السحب' : 'Payouts'),
                if (pendingCount > 0) ...[
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(color: const Color(0xFFDC2626), borderRadius: BorderRadius.circular(12)),
                    child: Text('$pendingCount', style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w900)),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ═══════════════════════════════════════════
  // TAB 1 — DASHBOARD & LIQUIDITY
  // ═══════════════════════════════════════════
  Widget _buildOverviewTab(
    List<Map<String, dynamic>> chartData,
    List<Map<String, dynamic>> ledger,
    double totalSales,
    double totalCommission,
    double merchantLiabilities,
    List<PlatformBank> platformBanks,
    bool isRtl,
  ) {
    final Map<String, Map<String, double>> bankStats = {};
    for (var item in ledger) {
      String rawBank = item['bank_name']?.toString().trim() ?? '';
      if (rawBank.isEmpty || rawBank == '-') {
        rawBank = item['method']?.toString().trim() ?? (isRtl ? 'غير محدد' : 'Unspecified');
      }
      final bankName = _normalizeBankName(rawBank, isRtl);

      bankStats.putIfAbsent(bankName, () => {'in': 0.0, 'out': 0.0, 'count': 0.0});
      final amt = (item['amount'] as num).toDouble();
      if (item['category'] == 'income') {
        bankStats[bankName]!['in'] = bankStats[bankName]!['in']! + amt;
      } else {
        bankStats[bankName]!['out'] = bankStats[bankName]!['out']! + amt;
      }
      bankStats[bankName]!['count'] = bankStats[bankName]!['count']! + 1.0;
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 24),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        // Bank Balances Section Header
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: const Color(0xFF2563EB).withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.account_balance_rounded, color: Color(0xFF2563EB), size: 24),
                ),
                const SizedBox(width: 12),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      isRtl ? 'الأرصدة المصرفية المتوفرة بالبنوك' : 'Bank Accounts Liquidity',
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      isRtl ? 'متابعة حركة الأرصدة المتوفرة والمحصلة في كل حساب' : 'Monitor cash flow and net balance per bank',
                      style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold, color: Color(0xFF64748B)),
                    ),
                  ],
                ),
              ],
            ),
          ],
        ),
        const SizedBox(height: 20),

        // Modern Banking Cards Grid
        LayoutBuilder(
          builder: (ctx, constraints) {
            final isWide = constraints.maxWidth > 750;
            return Wrap(
              spacing: 16,
              runSpacing: 16,
              children: bankStats.entries.map((entry) {
                final bName = entry.key;
                final bIn = entry.value['in'] ?? 0.0;
                final bOut = entry.value['out'] ?? 0.0;
                final bNet = bIn - bOut;
                final bCount = entry.value['count']?.toInt() ?? 0;

                PlatformBank? matchedBank;
                for (var pb in platformBanks) {
                  if (pb.nameAr == bName || pb.nameEn == bName || bName.contains(pb.nameAr) || pb.nameAr.contains(bName)) {
                    matchedBank = pb;
                    break;
                  }
                }

                return Container(
                  width: isWide ? (constraints.maxWidth - 32) / 3 : constraints.maxWidth,
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFFE2E8F0), width: 1.5),
                    boxShadow: [
                      BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 8, offset: const Offset(0, 2)),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: [
                              const CircleAvatar(
                                radius: 14,
                                backgroundColor: Color(0xFFF1F5F9),
                                child: Icon(Icons.account_balance, color: Color(0xFF475569), size: 16),
                              ),
                              const SizedBox(width: 10),
                              ConstrainedBox(
                                constraints: BoxConstraints(maxWidth: isWide ? 180 : 220),
                                child: Text(
                                  bName,
                                  style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 15, color: Color(0xFF0F172A)),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF8FAFC),
                              borderRadius: BorderRadius.circular(6),
                              border: Border.all(color: const Color(0xFFE2E8F0)),
                            ),
                            child: Text('$bCount ' + (isRtl ? "عملية" : "tx"), style: const TextStyle(fontSize: 11, color: Color(0xFF475569), fontWeight: FontWeight.w900)),
                          ),
                        ],
                      ),
                      if (matchedBank != null && matchedBank.accountNumber != null && matchedBank.accountNumber!.isNotEmpty) ...[
                        const SizedBox(height: 8),
                        Text(
                          '${isRtl ? "رقم الحساب" : "A/C"}: ${matchedBank.accountNumber}',
                          style: const TextStyle(color: Color(0xFF64748B), fontSize: 12, fontWeight: FontWeight.bold),
                        ),
                      ],
                      const SizedBox(height: 20),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(isRtl ? 'المحصّل (وارد):' : 'Collected (In):', style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w900, color: Color(0xFF64748B))),
                          Text(_fmt(bIn), style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: Color(0xFF059669))),
                        ],
                      ),
                      const SizedBox(height: 10),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(isRtl ? 'المحوّل (صادر):' : 'Paid Out (Out):', style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w900, color: Color(0xFF64748B))),
                          Text(_fmt(bOut), style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: Color(0xFFDC2626))),
                        ],
                      ),
                      const Padding(
                        padding: EdgeInsets.symmetric(vertical: 14),
                        child: Divider(height: 1, color: Color(0xFFE2E8F0)),
                      ),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            isRtl ? 'الرصيد المتوفر:' : 'Available Bal:',
                            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
                          ),
                          Text(
                            _fmt(bNet),
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w900,
                              color: bNet >= 0 ? const Color(0xFF0F172A) : const Color(0xFFDC2626),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 20),
                      Row(
                        children: [
                          Expanded(
                            child: OutlinedButton.icon(
                              style: OutlinedButton.styleFrom(
                                foregroundColor: const Color(0xFF0F172A),
                                side: const BorderSide(color: Color(0xFFCBD5E1), width: 1.5),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                padding: const EdgeInsets.symmetric(vertical: 12),
                              ),
                              onPressed: () {
                                _showBankStatementDialog(context, bName, matchedBank, ledger, isRtl);
                              },
                              icon: const Icon(Icons.receipt_long_outlined, size: 16),
                              label: Text(isRtl ? 'كشف الحساب' : 'Statement', style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w900)),
                            ),
                          ),
                          const SizedBox(width: 10),
                          FilledButton.icon(
                            style: FilledButton.styleFrom(
                              backgroundColor: const Color(0xFFF8FAFC),
                              foregroundColor: const Color(0xFF0F172A),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                            ),
                            onPressed: () {
                              setState(() {
                                _bankFilter = bName;
                                _tabController.animateTo(1);
                              });
                            },
                            icon: const Icon(Icons.filter_alt_outlined, size: 16),
                            label: const Text(''),
                          ),
                        ],
                      ),
                    ],
                  ),
                );
              }).toList(),
            );
          },
        ),

        const SizedBox(height: 40),

        // 6-Month Income vs Expense Chart
        Row(
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(color: const Color(0xFF10B981).withValues(alpha: 0.1), borderRadius: BorderRadius.circular(10)),
              child: const Icon(Icons.bar_chart_rounded, color: Color(0xFF059669), size: 24),
            ),
            const SizedBox(width: 12),
            Text(
              isRtl ? 'حركة التدفقات النقدية (آخر 6 أشهر)' : 'Cash Flow Trends (Last 6 Months)',
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
            ),
          ],
        ),
        const SizedBox(height: 20),
        Container(
          height: 320,
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFE2E8F0), width: 1.5),
            boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 10, offset: const Offset(0, 4))],
          ),
          child: _buildBarChart(chartData, isRtl),
        ),
        const SizedBox(height: 16),
        Row(mainAxisAlignment: MainAxisAlignment.center, children: [
          _legendDot(const Color(0xFF059669), isRtl ? 'مقبوضات (وارد)' : 'Income'),
          const SizedBox(width: 24),
          _legendDot(const Color(0xFFEF4444), isRtl ? 'مسحوبات (صادر)' : 'Expenses'),
        ]),
        const SizedBox(height: 40),
      ]),
    );
  }

  Widget _legendDot(Color c, String label) {
    return Row(children: [
      Container(width: 12, height: 12, decoration: BoxDecoration(color: c, borderRadius: BorderRadius.circular(3))),
      const SizedBox(width: 8),
      Text(label, style: const TextStyle(color: Color(0xFF475569), fontSize: 13, fontWeight: FontWeight.w900)),
    ]);
  }

  Widget _buildBarChart(List<Map<String, dynamic>> chartData, bool isRtl) {
    if (chartData.isEmpty) return const Center(child: Text('لا توجد بيانات', style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF64748B))));
    double maxY = 0;
    for (var d in chartData) {
      final i = (d['income'] as num).toDouble();
      final e = (d['expense'] as num).toDouble();
      if (i > maxY) maxY = i;
      if (e > maxY) maxY = e;
    }
    if (maxY == 0) maxY = 100;
    maxY *= 1.2;

    return BarChart(BarChartData(
      alignment: BarChartAlignment.spaceAround,
      maxY: maxY,
      barGroups: chartData.asMap().entries.map((e) {
        return BarChartGroupData(x: e.key, barRods: [
          BarChartRodData(toY: (e.value['income'] as num).toDouble(), color: const Color(0xFF059669), width: 16, borderRadius: BorderRadius.circular(4)),
          BarChartRodData(toY: (e.value['expense'] as num).toDouble(), color: const Color(0xFFEF4444), width: 16, borderRadius: BorderRadius.circular(4)),
        ]);
      }).toList(),
      titlesData: FlTitlesData(
        rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
        topTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
        bottomTitles: AxisTitles(
          sideTitles: SideTitles(
            showTitles: true,
            reservedSize: 32,
            getTitlesWidget: (value, meta) {
              final i = value.toInt();
              if (i >= 0 && i < chartData.length) {
                final p = chartData[i]['month'].toString().split('-');
                return SideTitleWidget(meta: meta, child: Text(p.length == 2 ? '${p[1]}/${p[0].substring(2)}' : '', style: const TextStyle(color: Color(0xFF475569), fontSize: 12, fontWeight: FontWeight.w900)));
              }
              return const Text('');
            },
          ),
        ),
        leftTitles: AxisTitles(
          sideTitles: SideTitles(
            showTitles: true,
            reservedSize: 65,
            interval: maxY / 4,
            getTitlesWidget: (v, _) {
              if (v >= 1000000) return Text('${(v / 1000000).toStringAsFixed(1)}M', style: const TextStyle(color: Color(0xFF475569), fontSize: 11, fontWeight: FontWeight.w900));
              if (v >= 1000) return Text('${(v / 1000).toStringAsFixed(0)}K', style: const TextStyle(color: Color(0xFF475569), fontSize: 11, fontWeight: FontWeight.w900));
              return Text(v.toInt().toString(), style: const TextStyle(color: Color(0xFF475569), fontSize: 11, fontWeight: FontWeight.w900));
            },
          ),
        ),
      ),
      borderData: FlBorderData(show: true, border: const Border(bottom: BorderSide(color: Color(0xFFE2E8F0)), left: BorderSide(color: Color(0xFFE2E8F0)))),
      gridData: FlGridData(
        show: true,
        drawVerticalLine: false,
        getDrawingHorizontalLine: (value) => FlLine(color: const Color(0xFFF1F5F9), strokeWidth: 1.5),
      ),
    ));
  }

  // ═══════════════════════════════════════════
  // TAB 2 — DETAILED LEDGER (Stripe-like)
  // ═══════════════════════════════════════════
  Widget _buildLedgerTab(
    List<Map<String, dynamic>> ledger,
    List<String> merchantNames,
    Map<String, Map<String, double>> merchantSummary,
    List<PlatformBank> platformBanks,
    bool isRtl,
  ) {
    final Set<String> availableBanks = {};
    for (var b in platformBanks) {
      if (b.nameAr.isNotEmpty) availableBanks.add(b.nameAr);
    }
    for (var item in ledger) {
      String rawBank = item['bank_name']?.toString().trim() ?? '';
      if (rawBank.isNotEmpty && rawBank != '-') {
        availableBanks.add(_normalizeBankName(rawBank, isRtl));
      }
    }

    final filtered = ledger.where((item) {
      if (_ledgerFilter != 'all') {
        if (_ledgerFilter == 'income' && item['category'] != 'income') return false;
        if (_ledgerFilter == 'expense' && item['category'] != 'expense') return false;
        if (_ledgerFilter == 'orders' && item['type'] != 'order') return false;
        if (_ledgerFilter == 'ads' && item['type'] != 'ad') return false;
      }
      if (_merchantFilter.isNotEmpty) {
        final name = item['store_name']?.toString().toLowerCase() ?? '';
        if (!name.contains(_merchantFilter.toLowerCase())) return false;
      }
      if (_bankFilter.isNotEmpty) {
        final bank = item['bank_name']?.toString().toLowerCase() ?? '';
        final norm = _normalizeBankName(bank, isRtl).toLowerCase();
        final q = _bankFilter.toLowerCase();
        if (!bank.contains(q) && !norm.contains(q)) return false;
      }
      return true;
    }).toList();

    double fIn = 0, fOut = 0;
    for (var i in filtered) {
      if (i['category'] == 'income') {
        fIn += (i['amount'] as num).toDouble();
      } else {
        fOut += (i['amount'] as num).toDouble();
      }
    }

    Map<String, double>? selectedMerchantStats;
    if (_merchantFilter.isNotEmpty) {
      for (var entry in merchantSummary.entries) {
        if (entry.key.toLowerCase().contains(_merchantFilter.toLowerCase())) {
          selectedMerchantStats = entry.value;
          break;
        }
      }
    }

    final inputDecoration = InputDecoration(
      filled: true,
      fillColor: Colors.white,
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
      enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
      focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: Color(0xFF2563EB), width: 1.5)),
    );

    return Column(children: [
      // Filters Header
      Container(
        margin: const EdgeInsets.fromLTRB(28, 16, 28, 16),
        child: Column(
          children: [
            Row(
              children: [
                Expanded(
                  flex: 3,
                  child: DropdownButtonFormField<String>(
                    value: _ledgerFilter,
                    decoration: inputDecoration.copyWith(
                      labelText: isRtl ? 'نوع الحركة / القيد' : 'Transaction Type',
                      labelStyle: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF475569), fontSize: 13),
                    ),
                    icon: const Icon(Icons.expand_more_rounded, color: Color(0xFF475569)),
                    dropdownColor: Colors.white,
                    isExpanded: true,
                    items: [
                      DropdownMenuItem(value: 'all', child: Text(isRtl ? '📋 كافة العمليات والقيود' : 'All Transactions', style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF0F172A), fontSize: 13))),
                      DropdownMenuItem(value: 'income', child: Text(isRtl ? '🟢 مقبوضات المبيعات (دائنة / وارد)' : 'Income (Credit)', style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF059669), fontSize: 13))),
                      DropdownMenuItem(value: 'expense', child: Text(isRtl ? '🔴 مسحوبات وتحويلات (مدينة / صادر)' : 'Payouts (Debit)', style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFFDC2626), fontSize: 13))),
                      DropdownMenuItem(value: 'orders', child: Text(isRtl ? '📦 طلبات المبيعات المسلمة' : 'Delivered Orders', style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF2563EB), fontSize: 13))),
                      DropdownMenuItem(value: 'ads', child: Text(isRtl ? '📢 إيرادات الإعلانات' : 'Ads Revenue', style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFFD97706), fontSize: 13))),
                    ],
                    onChanged: (val) {
                      if (val != null) setState(() => _ledgerFilter = val);
                    },
                  ),
                ),
                const SizedBox(width: 12),

                Expanded(
                  flex: 3,
                  child: DropdownButtonFormField<String>(
                    value: (_bankFilter.isEmpty || !availableBanks.contains(_bankFilter)) ? '' : _bankFilter,
                    decoration: inputDecoration.copyWith(
                      labelText: isRtl ? 'تصفية حسب الحساب البنكي' : 'Filter by Bank',
                      labelStyle: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF475569), fontSize: 13),
                      suffixIcon: _bankFilter.isNotEmpty
                          ? IconButton(
                              icon: const Icon(Icons.clear, size: 18, color: Color(0xFF64748B)),
                              onPressed: () => setState(() => _bankFilter = ''),
                            )
                          : null,
                    ),
                    icon: const Icon(Icons.expand_more_rounded, color: Color(0xFF475569)),
                    dropdownColor: Colors.white,
                    isExpanded: true,
                    items: [
                      DropdownMenuItem(value: '', child: Text(isRtl ? '🏦 كافة البنوك والمحافظ' : 'All Banks & Wallets', style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF0F172A), fontSize: 13))),
                      ...availableBanks.map((b) => DropdownMenuItem(
                            value: b,
                            child: Text(b, style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF0F172A), fontSize: 13), overflow: TextOverflow.ellipsis),
                          )),
                    ],
                    onChanged: (val) {
                      setState(() => _bankFilter = val ?? '');
                    },
                  ),
                ),
                const SizedBox(width: 12),

                Expanded(
                  flex: 4,
                  child: Autocomplete<String>(
                    optionsBuilder: (textEditingValue) {
                      if (textEditingValue.text.isEmpty) return const Iterable<String>.empty();
                      return merchantNames.where((n) => n.toLowerCase().contains(textEditingValue.text.toLowerCase()));
                    },
                    onSelected: (selection) {
                      setState(() {
                        _merchantFilter = selection;
                        _merchantSearchCtrl.text = selection;
                      });
                    },
                    fieldViewBuilder: (ctx, ctrl, focusNode, onSubmitted) {
                      return TextField(
                        controller: ctrl,
                        focusNode: focusNode,
                        style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF0F172A), fontSize: 13),
                        decoration: inputDecoration.copyWith(
                          labelText: isRtl ? 'بحث وعزل حساب تاجر معين...' : 'Search Store / Merchant...',
                          labelStyle: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF475569), fontSize: 13),
                          prefixIcon: const Icon(Icons.search, color: Color(0xFF94A3B8), size: 20),
                          suffixIcon: _merchantFilter.isNotEmpty
                              ? IconButton(
                                  icon: const Icon(Icons.clear, size: 18, color: Color(0xFF64748B)),
                                  onPressed: () {
                                    ctrl.clear();
                                    setState(() => _merchantFilter = '');
                                  },
                                )
                              : null,
                        ),
                        onChanged: (v) => setState(() => _merchantFilter = v.trim()),
                      );
                    },
                  ),
                ),
                const SizedBox(width: 12),

                Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: const Color(0xFFCBD5E1)),
                  ),
                  child: IconButton(
                    padding: const EdgeInsets.all(12),
                    icon: Icon(_sortNewestFirst ? Icons.arrow_downward_rounded : Icons.arrow_upward_rounded, color: const Color(0xFF0F172A), size: 20),
                    tooltip: _sortNewestFirst ? (isRtl ? 'الأحدث أولاً' : 'Newest First') : (isRtl ? 'الأقدم أولاً' : 'Oldest First'),
                    onPressed: () => setState(() => _sortNewestFirst = !_sortNewestFirst),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Minimalist Summary Stats
            Row(
              children: [
                Expanded(child: _miniStatCard(isRtl ? 'إجمالي المقبوض (وارد)' : 'Total In', _fmt(fIn), const Color(0xFF059669))),
                const SizedBox(width: 12),
                Expanded(child: _miniStatCard(isRtl ? 'إجمالي المحول (صادر)' : 'Total Out', _fmt(fOut), const Color(0xFFDC2626))),
                const SizedBox(width: 12),
                Expanded(child: _miniStatCard(isRtl ? 'الرصيد الصافي المفلتر' : 'Net Balance', _fmt(fIn - fOut), fIn >= fOut ? const Color(0xFF0F172A) : const Color(0xFFDC2626))),
                const SizedBox(width: 12),
                Expanded(child: _miniStatCard(isRtl ? 'إجمالي القيود' : 'Total Records', '${filtered.length}', const Color(0xFF475569))),
              ],
            ),
          ],
        ),
      ),

      // Active Statement Banners
      if (_bankFilter.isNotEmpty)
        Container(
          margin: const EdgeInsets.symmetric(horizontal: 28, vertical: 4),
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
          decoration: BoxDecoration(
            color: const Color(0xFFF0F9FF),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFBAE6FD)),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  const Icon(Icons.account_balance, color: Color(0xFF0284C7), size: 24),
                  const SizedBox(width: 12),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        '${isRtl ? "كشف حساب مصرفي مفلتر:" : "Filtered Bank Statement:"} $_bankFilter',
                        style: const TextStyle(color: Color(0xFF0F172A), fontWeight: FontWeight.w900, fontSize: 14),
                      ),
                      Text(
                        '${isRtl ? "الرصيد الصافي للبنك:" : "Net Bank Balance:"} ${_fmt(fIn - fOut)} (${filtered.length} ${isRtl ? "عملية" : "tx"})',
                        style: const TextStyle(color: Color(0xFF0369A1), fontSize: 12.5, fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                ],
              ),
              FilledButton.icon(
                style: FilledButton.styleFrom(
                  backgroundColor: Colors.white,
                  foregroundColor: const Color(0xFF0284C7),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8), side: const BorderSide(color: Color(0xFF0284C7))),
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  elevation: 0,
                ),
                icon: const Icon(Icons.fullscreen, size: 16),
                label: Text(
                  isRtl ? 'معاينة كشف الحساب المصرفي الرسمي' : 'Open Official Statement',
                  style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 12),
                ),
                onPressed: () {
                  _showBankStatementDialog(context, _bankFilter, null, ledger, isRtl);
                },
              ),
            ],
          ),
        ),

      if (_merchantFilter.isNotEmpty && selectedMerchantStats != null)
        Container(
          margin: const EdgeInsets.symmetric(horizontal: 28, vertical: 4),
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
          decoration: BoxDecoration(
            color: const Color(0xFFFFFBEB),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFFDE68A)),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  const Icon(Icons.storefront_rounded, color: Color(0xFFD97706), size: 24),
                  const SizedBox(width: 12),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        '${isRtl ? "كشف حساب التاجر:" : "Merchant Statement:"} $_merchantFilter',
                        style: const TextStyle(color: Color(0xFF0F172A), fontWeight: FontWeight.w900, fontSize: 14),
                      ),
                      Text(
                        '${isRtl ? "المبيعات" : "Sales"}: ${_fmt(selectedMerchantStats["income"] ?? 0)} | ' +
                        '${isRtl ? "العمولة" : "Comm"}: ${_fmt(selectedMerchantStats["commission"] ?? 0)} | ' +
                        '${isRtl ? "المستحق" : "Due"}: ${_fmt(selectedMerchantStats["merchant_share"] ?? 0)} | ' +
                        '${isRtl ? "المتبقي للسحب" : "Available"}: ${_fmt(((selectedMerchantStats["merchant_share"] as num?)?.toDouble() ?? 0.0) - ((selectedMerchantStats["expense"] as num?)?.toDouble() ?? 0.0))}',
                        style: const TextStyle(color: Color(0xFFB45309), fontSize: 12.5, fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                ],
              ),
            ],
          ),
        ),

      // UNIFIED SPACIOUS FINANCIAL LEDGER TABLE (Professional Data Table)
      Expanded(
        child: Container(
          margin: const EdgeInsets.fromLTRB(28, 12, 28, 24),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFE2E8F0), width: 1.5),
            boxShadow: [
              BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 6, offset: const Offset(0, 2)),
            ],
          ),
          child: filtered.isEmpty
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.receipt_long_outlined, size: 48, color: Colors.grey.shade300),
                      const SizedBox(height: 12),
                      Text(
                        isRtl ? 'لا توجد عمليات تطابق البحث' : 'No transactions match filter',
                        style: const TextStyle(color: Color(0xFF64748B), fontSize: 15, fontWeight: FontWeight.w900),
                      ),
                    ],
                  ),
                )
              : Builder(builder: (_) {
                  double runBal = 0;
                  final withBalance = filtered.map((t) {
                    if (t['category'] == 'income') {
                      runBal += (t['amount'] as num).toDouble();
                    } else {
                      runBal -= (t['amount'] as num).toDouble();
                    }
                    return {...t, 'running_balance': runBal};
                  }).toList();

                  final displayList = _sortNewestFirst ? withBalance.reversed.toList() : withBalance;

                  return LayoutBuilder(
                    builder: (context, constraints) {
                      final double availWidth = constraints.maxWidth;
                      final double minRequiredWidth = 1350.0;
                      final double tableWidth = availWidth > minRequiredWidth ? availWidth : minRequiredWidth;
                      final double extra = tableWidth - minRequiredWidth;

                      final double colDate = 135.0;
                      final double colDesc = 270.0 + (extra * 0.30);
                      final double colStore = 135.0 + (extra * 0.10);
                      final double colBank = 135.0 + (extra * 0.10);
                      final double colComm = 110.0 + (extra * 0.05);
                      final double colMerch = 135.0 + (extra * 0.10);
                      final double colCredit = 135.0 + (extra * 0.10);
                      final double colDebit = 135.0 + (extra * 0.10);
                      final double colBal = 160.0 + (extra * 0.15);

                      return Scrollbar(
                        thumbVisibility: true,
                        trackVisibility: true,
                        child: SingleChildScrollView(
                          scrollDirection: Axis.horizontal,
                          child: SizedBox(
                            width: tableWidth,
                            child: Column(
                              children: [
                                // Clean SaaS Table Header
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                                  decoration: const BoxDecoration(
                                    color: Color(0xFFF8FAFC),
                                    borderRadius: BorderRadius.vertical(top: Radius.circular(10)),
                                    border: Border(bottom: BorderSide(color: Color(0xFFE2E8F0), width: 1.5)),
                                  ),
                                  child: Row(
                                    children: [
                                      SizedBox(width: colDate, child: Text(isRtl ? 'التاريخ' : 'Date', style: const TextStyle(color: Color(0xFF475569), fontWeight: FontWeight.w900, fontSize: 12))),
                                      SizedBox(width: colDesc, child: Text(isRtl ? 'البيان والمرجع' : 'Description & Ref', style: const TextStyle(color: Color(0xFF475569), fontWeight: FontWeight.w900, fontSize: 12))),
                                      SizedBox(width: colStore, child: Text(isRtl ? 'التاجر' : 'Store', style: const TextStyle(color: Color(0xFF475569), fontWeight: FontWeight.w900, fontSize: 12))),
                                      SizedBox(width: colBank, child: Text(isRtl ? 'البنك / الطريقة' : 'Bank / Method', style: const TextStyle(color: Color(0xFF475569), fontWeight: FontWeight.w900, fontSize: 12))),
                                      SizedBox(width: colComm, child: Text(isRtl ? 'عمولة المنصة' : 'Platform Comm', style: const TextStyle(color: Color(0xFF475569), fontWeight: FontWeight.w900, fontSize: 12), textAlign: TextAlign.end)),
                                      SizedBox(width: colMerch, child: Text(isRtl ? 'مستحق التاجر' : 'Merchant Share', style: const TextStyle(color: Color(0xFF475569), fontWeight: FontWeight.w900, fontSize: 12), textAlign: TextAlign.end)),
                                      SizedBox(width: colCredit, child: Text(isRtl ? 'مقبوض (دائن)' : 'Credit (In)', style: const TextStyle(color: Color(0xFF475569), fontWeight: FontWeight.w900, fontSize: 12), textAlign: TextAlign.end)),
                                      SizedBox(width: colDebit, child: Text(isRtl ? 'محول (مدين)' : 'Debit (Out)', style: const TextStyle(color: Color(0xFF475569), fontWeight: FontWeight.w900, fontSize: 12), textAlign: TextAlign.end)),
                                      SizedBox(width: colBal, child: Text(isRtl ? 'الرصيد التراكمي' : 'Running Balance', style: const TextStyle(color: Color(0xFF475569), fontWeight: FontWeight.w900, fontSize: 12), textAlign: TextAlign.end)),
                                    ],
                                  ),
                                ),

                                // Table Rows
                                Expanded(
                                  child: ListView.separated(
                                    itemCount: displayList.length,
                                    separatorBuilder: (_, __) => const Divider(height: 1, color: Color(0xFFE2E8F0), thickness: 1),
                                    itemBuilder: (context, i) {
                                      final item = displayList[i];
                                      final isIncome = item['category'] == 'income';
                                      final amt = (item['amount'] as num).toDouble();
                                      final bal = (item['running_balance'] as num).toDouble();
                                      final dt = DateTime.tryParse(item['created_at']?.toString() ?? '');
                                      final dateStr = dt != null ? DateFormat('yyyy/MM/dd HH:mm').format(dt) : '-';

                                      final isOrder = item['type'] == 'order';
                                      final isAd = item['type'] == 'ad';

                                      final commAmount = (item['commission'] as num?)?.toDouble() ?? 0.0;
                                      final merchShare = (item['merchant_share'] as num?)?.toDouble() ?? 0.0;
                                      final rawBank = item['bank_name']?.toString() ?? '-';
                                      final bankName = _normalizeBankName(rawBank, isRtl);
                                      final refNum = item['reference_number']?.toString() ?? '';
                                      final orderNum = item['order_number']?.toString() ?? '';

                                      return Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                                        color: Colors.white,
                                        child: Row(
                                          children: [
                                            SizedBox(
                                              width: colDate,
                                              child: Text(
                                                dateStr,
                                                style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w900, color: Color(0xFF475569)),
                                                maxLines: 1,
                                                softWrap: false,
                                              ),
                                            ),

                                            SizedBox(
                                              width: colDesc,
                                              child: Row(
                                                children: [
                                                  Container(
                                                    padding: const EdgeInsets.all(6),
                                                    decoration: BoxDecoration(
                                                      color: isIncome ? const Color(0xFFF0FDF4) : const Color(0xFFFEF2F2),
                                                      borderRadius: BorderRadius.circular(8),
                                                    ),
                                                    child: Icon(
                                                      isOrder ? Icons.shopping_bag_outlined : (isAd ? Icons.campaign_outlined : Icons.outbox_rounded),
                                                      size: 16,
                                                      color: isIncome ? (isAd ? const Color(0xFFD97706) : const Color(0xFF059669)) : const Color(0xFFDC2626),
                                                    ),
                                                  ),
                                                  const SizedBox(width: 10),
                                                  Expanded(
                                                    child: Column(
                                                      crossAxisAlignment: CrossAxisAlignment.start,
                                                      mainAxisAlignment: MainAxisAlignment.center,
                                                      children: [
                                                        Text(
                                                          item['title'] ?? item['description'] ?? '',
                                                          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
                                                          maxLines: 1,
                                                          overflow: TextOverflow.ellipsis,
                                                        ),
                                                        if (orderNum.isNotEmpty || refNum.isNotEmpty)
                                                          Text(
                                                            (orderNum.isNotEmpty ? 'ORD-$orderNum ' : '') + (refNum.isNotEmpty ? 'Ref: $refNum' : ''),
                                                            style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontWeight: FontWeight.bold),
                                                            maxLines: 1,
                                                            overflow: TextOverflow.ellipsis,
                                                          ),
                                                      ],
                                                    ),
                                                  ),
                                                ],
                                              ),
                                            ),

                                            SizedBox(
                                              width: colStore,
                                              child: Text(
                                                item['store_name'] ?? '-',
                                                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
                                                maxLines: 1,
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            ),

                                            SizedBox(
                                              width: colBank,
                                              child: Text(
                                                bankName,
                                                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF475569)),
                                                maxLines: 1,
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            ),

                                            SizedBox(
                                              width: colComm,
                                              child: Text(
                                                commAmount > 0 ? _fmt(commAmount) : '-',
                                                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w900, color: Color(0xFF64748B)),
                                                textAlign: TextAlign.end,
                                                maxLines: 1,
                                                softWrap: false,
                                              ),
                                            ),

                                            SizedBox(
                                              width: colMerch,
                                              child: Text(
                                                merchShare > 0 ? _fmt(merchShare) : '-',
                                                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w900, color: Color(0xFF64748B)),
                                                textAlign: TextAlign.end,
                                                maxLines: 1,
                                                softWrap: false,
                                              ),
                                            ),

                                            SizedBox(
                                              width: colCredit,
                                              child: Text(
                                                isIncome ? _fmt(amt) : '-',
                                                style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w900, color: isIncome ? const Color(0xFF059669) : const Color(0xFFCBD5E1)),
                                                textAlign: TextAlign.end,
                                                maxLines: 1,
                                                softWrap: false,
                                              ),
                                            ),

                                            SizedBox(
                                              width: colDebit,
                                              child: Text(
                                                !isIncome ? _fmt(amt) : '-',
                                                style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w900, color: !isIncome ? const Color(0xFFDC2626) : const Color(0xFFCBD5E1)),
                                                textAlign: TextAlign.end,
                                                maxLines: 1,
                                                softWrap: false,
                                              ),
                                            ),

                                            SizedBox(
                                              width: colBal,
                                              child: Text(
                                                _fmt(bal),
                                                style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
                                                textAlign: TextAlign.end,
                                                maxLines: 1,
                                                softWrap: false,
                                              ),
                                            ),
                                          ],
                                        ),
                                      );
                                    },
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      );
                    },
                  );
                }),
        ),
      ),
    ]);
  }

  Widget _miniStatCard(String label, String val, Color c) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.01), blurRadius: 4, offset: const Offset(0, 2))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: const TextStyle(fontSize: 11.5, color: Color(0xFF64748B), fontWeight: FontWeight.w900)),
          const SizedBox(height: 4),
          Text(val, style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900, color: c, letterSpacing: -0.5)),
        ],
      ),
    );
  }

  // ═══════════════════════════════════════════
  // TAB 3 — MERCHANT BALANCES
  // ═══════════════════════════════════════════
  Widget _buildBalancesTab(
    Map<String, Map<String, double>> summary,
    List<Map<String, dynamic>> ledger,
    bool isRtl,
  ) {
    double totalGross = 0;
    double totalComm = 0;
    double totalShare = 0;
    double totalPaid = 0;
    double totalNetDue = 0;

    for (var val in summary.values) {
      totalGross += val['income'] ?? 0;
      totalComm += val['commission'] ?? 0;
      totalShare += val['merchant_share'] ?? 0;
      totalPaid += val['expense'] ?? 0;
      totalNetDue += (val['merchant_share'] ?? 0) - (val['expense'] ?? 0);
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.all(28),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFE2E8F0), width: 1.5),
            boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 10, offset: const Offset(0, 4))],
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _balanceSummaryTile(isRtl ? 'إجمالي مبيعات المتاجر' : 'Gross Sales', totalGross, const Color(0xFF0F172A)),
              _balanceSummaryTile(isRtl ? 'عمولات المنصة' : 'Platform Comm.', totalComm, const Color(0xFF059669)),
              _balanceSummaryTile(isRtl ? 'المستحق للتجار' : 'Merchant Share', totalShare, const Color(0xFF475569)),
              _balanceSummaryTile(isRtl ? 'المبالغ المسحوبة' : 'Total Withdrawn', totalPaid, const Color(0xFFDC2626)),
              _balanceSummaryTile(isRtl ? 'المتبقي للسحب' : 'Net Due Balance', totalNetDue, const Color(0xFF2563EB), isHighlight: true),
            ],
          ),
        ),
        const SizedBox(height: 24),

        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFE2E8F0), width: 1.5),
            boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 10, offset: const Offset(0, 4))],
          ),
          child: Column(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                decoration: const BoxDecoration(
                  color: Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.vertical(top: Radius.circular(14)),
                  border: Border(bottom: BorderSide(color: Color(0xFFE2E8F0), width: 1.5)),
                ),
                child: Row(
                  children: [
                    Expanded(flex: 3, child: Text(isRtl ? 'اسم المتجر / التاجر' : 'Store Name', style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF475569), fontSize: 13))),
                    Expanded(flex: 2, child: Text(isRtl ? 'إجمالي المبيعات' : 'Gross Sales', textAlign: TextAlign.end, style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF475569), fontSize: 13))),
                    Expanded(flex: 2, child: Text(isRtl ? 'عمولة المنصة' : 'Platform Comm.', textAlign: TextAlign.end, style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF475569), fontSize: 13))),
                    Expanded(flex: 2, child: Text(isRtl ? 'مستحق التاجر' : 'Merchant Share', textAlign: TextAlign.end, style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF475569), fontSize: 13))),
                    Expanded(flex: 2, child: Text(isRtl ? 'المسحوبات' : 'Paid Out', textAlign: TextAlign.end, style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF475569), fontSize: 13))),
                    Expanded(flex: 2, child: Text(isRtl ? 'صافي المتبقي' : 'Net Due', textAlign: TextAlign.end, style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF0F172A), fontSize: 13))),
                    const SizedBox(width: 80),
                  ],
                ),
              ),

              if (summary.isEmpty)
                Padding(
                  padding: const EdgeInsets.all(32),
                  child: Center(child: Text(isRtl ? 'لا توجد بيانات أرصدة' : 'No balance data', style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF64748B)))),
                )
              else
                ...summary.entries.map((e) {
                  final s = e.value;
                  final storeName = e.key;
                  final gross = s['income'] ?? 0;
                  final comm = s['commission'] ?? 0;
                  final share = s['merchant_share'] ?? 0;
                  final paid = s['expense'] ?? 0;
                  final net = (s['merchant_share'] ?? 0) - (s['expense'] ?? 0);

                  return Container(
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                    decoration: const BoxDecoration(
                      border: Border(bottom: BorderSide(color: Color(0xFFE2E8F0), width: 1)),
                    ),
                    child: Row(
                      children: [
                        Expanded(
                          flex: 3,
                          child: Text(
                            storeName,
                            style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13.5, color: Color(0xFF0F172A)),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        Expanded(
                          flex: 2,
                          child: Text(_fmt(gross), textAlign: TextAlign.end, style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF475569), fontSize: 13)),
                        ),
                        Expanded(
                          flex: 2,
                          child: Text(_fmt(comm), textAlign: TextAlign.end, style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF475569), fontSize: 13)),
                        ),
                        Expanded(
                          flex: 2,
                          child: Text(_fmt(share), textAlign: TextAlign.end, style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF475569), fontSize: 13)),
                        ),
                        Expanded(
                          flex: 2,
                          child: Text(_fmt(paid), textAlign: TextAlign.end, style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFFDC2626), fontSize: 13)),
                        ),
                        Expanded(
                          flex: 2,
                          child: Text(
                            _fmt(net),
                            textAlign: TextAlign.end,
                            style: TextStyle(
                              fontWeight: FontWeight.w900,
                              fontSize: 14,
                              color: net > 0 ? const Color(0xFF0F172A) : const Color(0xFF94A3B8),
                            ),
                          ),
                        ),
                        const SizedBox(width: 16),
                        SizedBox(
                          width: 64,
                          child: OutlinedButton(
                            style: OutlinedButton.styleFrom(
                              foregroundColor: const Color(0xFF0F172A),
                              side: const BorderSide(color: Color(0xFFCBD5E1)),
                              padding: const EdgeInsets.symmetric(horizontal: 0, vertical: 6),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                            ),
                            onPressed: () {
                              _showMerchantStatementDialog(context, storeName, s, ledger, isRtl);
                            },
                            child: Text(isRtl ? 'كشف' : 'View', style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w900)),
                          ),
                        ),
                      ],
                    ),
                  );
                }).toList(),
            ],
          ),
        ),
      ]),
    );
  }

  Widget _balanceSummaryTile(String label, double val, Color c, {bool isHighlight = false}) {
    return Column(
      children: [
        Text(label, style: const TextStyle(color: Color(0xFF64748B), fontSize: 12.5, fontWeight: FontWeight.w900)),
        const SizedBox(height: 6),
        Text(
          _fmt(val),
          style: TextStyle(
            color: c,
            fontSize: isHighlight ? 18 : 16,
            fontWeight: FontWeight.w900,
            letterSpacing: -0.5,
          ),
        ),
      ],
    );
  }

  // ═══════════════════════════════════════════
  // TAB 4 — PENDING PAYOUTS
  // ═══════════════════════════════════════════
  Widget _buildPendingTab(List<Map<String, dynamic>> pending, bool isRtl) {
    if (pending.isEmpty) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(color: const Color(0xFFF1F5F9), borderRadius: BorderRadius.circular(24)),
              child: const Icon(Icons.check_circle_outline, color: Color(0xFF64748B), size: 48),
            ),
            const SizedBox(height: 20),
            Text(
              isRtl ? 'لا توجد طلبات سحب معلقة حالياً' : 'No pending payouts',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(28),
      itemCount: pending.length,
      itemBuilder: (ctx, i) {
        final req = pending[i];
        final id = req['id']?.toString() ?? '';
        final storeName = req['_store_name'] ?? 'متجر';
        final amt = (num.tryParse(req['amount']?.toString() ?? '0') ?? 0.0).toDouble();
        final bankName = req['bank_name'] ?? 'تحويل بنكي';
        final accNum = req['account_number'] ?? '-';
        final accName = req['account_name'] ?? '-';
        final dt = DateTime.tryParse(req['created_at']?.toString() ?? '');
        final dateStr = dt != null ? DateFormat('yyyy/MM/dd HH:mm').format(dt) : '-';

        return Container(
          margin: const EdgeInsets.only(bottom: 16),
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFE2E8F0), width: 1.5),
            boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 8, offset: const Offset(0, 2))],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      const CircleAvatar(
                        backgroundColor: Color(0xFFFFFBEB),
                        child: Icon(Icons.storefront_rounded, color: Color(0xFFD97706), size: 20),
                      ),
                      const SizedBox(width: 12),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(storeName, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 16, color: Color(0xFF0F172A))),
                          Text(dateStr, style: const TextStyle(color: Color(0xFF64748B), fontSize: 12, fontWeight: FontWeight.bold)),
                        ],
                      ),
                    ],
                  ),
                  Text(
                    _fmt(amt),
                    style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 18, color: Color(0xFF0F172A), letterSpacing: -0.5),
                  ),
                ],
              ),
              const Divider(height: 32, color: Color(0xFFE2E8F0)),
              Row(
                children: [
                  Expanded(
                    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Text(isRtl ? 'البنك / الطريقة' : 'Bank / Method', style: const TextStyle(color: Color(0xFF64748B), fontSize: 12, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 4),
                      Text(bankName, style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF0F172A), fontSize: 13.5)),
                    ]),
                  ),
                  Expanded(
                    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Text(isRtl ? 'رقم الحساب / الآيبان' : 'Account Number', style: const TextStyle(color: Color(0xFF64748B), fontSize: 12, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 4),
                      Text(accNum, style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF0F172A), fontSize: 13.5)),
                    ]),
                  ),
                  Expanded(
                    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Text(isRtl ? 'صاحب الحساب' : 'Account Holder', style: const TextStyle(color: Color(0xFF64748B), fontSize: 12, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 4),
                      Text(accName, style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF0F172A), fontSize: 13.5)),
                    ]),
                  ),
                ],
              ),
              const SizedBox(height: 24),
              Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  OutlinedButton.icon(
                    style: OutlinedButton.styleFrom(
                      foregroundColor: const Color(0xFFDC2626),
                      side: const BorderSide(color: Color(0xFFFCA5A5)),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    ),
                    icon: const Icon(Icons.close, size: 16),
                    label: Text(isRtl ? 'رفض الطلب' : 'Reject', style: const TextStyle(fontWeight: FontWeight.w900)),
                    onPressed: () => _handlePayoutAction(id, 'rejected'),
                  ),
                  const SizedBox(width: 12),
                  FilledButton.icon(
                    style: FilledButton.styleFrom(
                      backgroundColor: const Color(0xFF0F172A),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                    ),
                    icon: const Icon(Icons.check, size: 16),
                    label: Text(isRtl ? 'موافقة وتأكيد التحويل' : 'Approve & Confirm', style: const TextStyle(fontWeight: FontWeight.w900)),
                    onPressed: () => _handlePayoutAction(id, 'approved'),
                  ),
                ],
              ),
            ],
          ),
        );
      },
    );
  }

  Future<void> _handlePayoutAction(String id, String status) async {
    try {
      await Supabase.instance.client.from('merchant_payout_requests').update({'status': status}).eq('id', id);
      ref.invalidate(adminFinanceProvider);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(status == 'approved' ? 'تمت الموافقة على الطلب بنجاح' : 'تم رفض الطلب'),
            backgroundColor: status == 'approved' ? const Color(0xFF047857) : const Color(0xFFDC2626),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('خطأ: $e'), backgroundColor: Colors.red),
        );
      }
    }
  }

  // ═════════════════════════════════════════════════════════════════
  // BANK STATEMENT MODALS
  // ═════════════════════════════════════════════════════════════════
  void _openBankSelectorDialog(BuildContext context, List<Map<String, dynamic>> ledger, List<PlatformBank> platformBanks, bool isRtl) {
    final Set<String> allBankNames = {};
    for (var b in platformBanks) {
      if (b.nameAr.isNotEmpty) allBankNames.add(b.nameAr);
    }
    for (var item in ledger) {
      String rawBank = item['bank_name']?.toString().trim() ?? '';
      if (rawBank.isNotEmpty && rawBank != '-') {
        allBankNames.add(_normalizeBankName(rawBank, isRtl));
      }
    }

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(isRtl ? 'اختر البنك لاستخراج كشف الحساب' : 'Select Bank for Statement', style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 16, color: Color(0xFF0F172A))),
        content: SizedBox(
          width: 400,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: allBankNames.map((bName) {
              PlatformBank? matchedBank;
              for (var pb in platformBanks) {
                if (pb.nameAr == bName || pb.nameEn == bName || bName.contains(pb.nameAr)) {
                  matchedBank = pb;
                  break;
                }
              }

              return ListTile(
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                leading: const Icon(Icons.account_balance, color: Color(0xFF475569), size: 20),
                title: Text(bName, style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF0F172A))),
                subtitle: matchedBank != null && matchedBank.accountNumber != null
                    ? Text('${isRtl ? "رقم الحساب" : "A/C"}: ${matchedBank.accountNumber}', style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)))
                    : null,
                trailing: const Icon(Icons.chevron_left_rounded, color: Color(0xFF94A3B8)),
                onTap: () {
                  Navigator.pop(ctx);
                  _showBankStatementDialog(context, bName, matchedBank, ledger, isRtl);
                },
              );
            }).toList(),
          ),
        ),
      ),
    );
  }

  void _showBankStatementDialog(BuildContext context, String bankName, PlatformBank? platformBank, List<Map<String, dynamic>> allLedger, bool isRtl) {
    final bankTxList = allLedger.where((item) {
      final b = item['bank_name']?.toString() ?? '';
      final norm = _normalizeBankName(b, isRtl);
      return b.contains(bankName) || norm.contains(bankName) || bankName.contains(norm);
    }).toList();

    bankTxList.sort((a, b) {
      final dateA = DateTime.tryParse(a['created_at']?.toString() ?? '') ?? DateTime.fromMillisecondsSinceEpoch(0);
      final dateB = DateTime.tryParse(b['created_at']?.toString() ?? '') ?? DateTime.fromMillisecondsSinceEpoch(0);
      return dateA.compareTo(dateB);
    });

    double totalIn = 0.0;
    double totalOut = 0.0;
    double runningBankBal = 0.0;

    final List<Map<String, dynamic>> bankJournal = [];
    for (var tx in bankTxList) {
      final amt = (tx['amount'] as num).toDouble();
      final isInc = tx['category'] == 'income';
      if (isInc) {
        totalIn += amt;
        runningBankBal += amt;
      } else {
        totalOut += amt;
        runningBankBal -= amt;
      }
      bankJournal.add({...tx, 'bank_running_bal': runningBankBal});
    }

    final displayJournal = bankJournal.reversed.toList();

    showDialog(
      context: context,
      builder: (ctx) => Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        insetPadding: const EdgeInsets.symmetric(horizontal: 24, vertical: 24),
        child: Container(
          width: 1100,
          height: 750,
          padding: const EdgeInsets.all(28),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Statement Header
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(color: const Color(0xFFF1F5F9), borderRadius: BorderRadius.circular(12)),
                        child: const Icon(Icons.account_balance_rounded, color: Color(0xFF0F172A), size: 28),
                      ),
                      const SizedBox(width: 16),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            '${isRtl ? "كشف حساب مصرفي رسمي:" : "Official Bank Statement:"} $bankName',
                            style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900, color: Color(0xFF0F172A), letterSpacing: -0.5),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            platformBank != null && platformBank.accountNumber != null
                                ? '${isRtl ? "رقم الحساب" : "A/C"}: ${platformBank.accountNumber} | ${isRtl ? "صاحب الحساب" : "Holder"}: ${platformBank.accountName ?? "-"}'
                                : '${isRtl ? "سجل العمليات المصرفية والسيولة النقدية" : "Bank Transactions & Cash Flow Statement"}',
                            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF64748B)),
                          ),
                        ],
                      ),
                    ],
                  ),
                  Row(
                    children: [
                      FilledButton.icon(
                        style: FilledButton.styleFrom(
                          backgroundColor: Colors.white,
                          foregroundColor: const Color(0xFF0F172A),
                          side: const BorderSide(color: Color(0xFFCBD5E1), width: 1.5),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                          elevation: 0,
                        ),
                        icon: const Icon(Icons.print, size: 18),
                        label: Text(isRtl ? 'طباعة الكشف' : 'Print Statement', style: const TextStyle(fontWeight: FontWeight.w900)),
                        onPressed: () {
                          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('جاهز للطباعة وتصدير كشف الحساب المصرفي')));
                        },
                      ),
                      const SizedBox(width: 12),
                      IconButton(
                        icon: const Icon(Icons.close, color: Color(0xFF64748B), size: 24),
                        onPressed: () => Navigator.pop(ctx),
                      ),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 24),

              // Summary Cards
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  border: Border.all(color: const Color(0xFFE2E8F0), width: 1.5),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                    _statementStat(isRtl ? 'إجمالي المقبوضات' : 'Total Inflows', _fmt(totalIn), const Color(0xFF059669)),
                    _statementStat(isRtl ? 'إجمالي المسحوبات' : 'Total Outflows', _fmt(totalOut), const Color(0xFFDC2626)),
                    _statementStat(isRtl ? 'الرصيد الختامي الصافي' : 'Closing Net Balance', _fmt(totalIn - totalOut), const Color(0xFF0F172A), isBig: true),
                    _statementStat(isRtl ? 'إجمالي عدد العمليات' : 'Transaction Count', '${bankJournal.length}', const Color(0xFF475569)),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              // Transactions Table
              Expanded(
                child: Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFE2E8F0), width: 1.5),
                  ),
                  child: displayJournal.isEmpty
                      ? Center(
                          child: Text(
                            isRtl ? 'لا توجد حركات مسجلة لهذا الحساب المصرفي' : 'No transactions recorded for this bank',
                            style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF64748B), fontSize: 14),
                          ),
                        )
                      : SingleChildScrollView(
                          scrollDirection: Axis.horizontal,
                          child: SizedBox(
                            width: 1000,
                            child: Column(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                                  decoration: const BoxDecoration(
                                    color: Color(0xFFF8FAFC),
                                    borderRadius: BorderRadius.vertical(top: Radius.circular(10)),
                                    border: Border(bottom: BorderSide(color: Color(0xFFE2E8F0), width: 1.5)),
                                  ),
                                  child: Row(
                                    children: [
                                      SizedBox(width: 130, child: Text(isRtl ? 'التاريخ' : 'Date', style: const TextStyle(color: Color(0xFF475569), fontWeight: FontWeight.w900, fontSize: 12))),
                                      Expanded(flex: 3, child: Text(isRtl ? 'البيان والمرجع' : 'Statement & Description', style: const TextStyle(color: Color(0xFF475569), fontWeight: FontWeight.w900, fontSize: 12))),
                                      Expanded(flex: 2, child: Text(isRtl ? 'التاجر / العميل' : 'Counterparty', style: const TextStyle(color: Color(0xFF475569), fontWeight: FontWeight.w900, fontSize: 12))),
                                      SizedBox(width: 130, child: Text(isRtl ? 'مقبوض (دائن)' : 'Credit (In)', textAlign: TextAlign.end, style: const TextStyle(color: Color(0xFF475569), fontWeight: FontWeight.w900, fontSize: 12))),
                                      SizedBox(width: 130, child: Text(isRtl ? 'محول (مدين)' : 'Debit (Out)', textAlign: TextAlign.end, style: const TextStyle(color: Color(0xFF475569), fontWeight: FontWeight.w900, fontSize: 12))),
                                      SizedBox(width: 140, child: Text(isRtl ? 'رصيد البنك' : 'Bank Balance', textAlign: TextAlign.end, style: const TextStyle(color: Color(0xFF475569), fontWeight: FontWeight.w900, fontSize: 12))),
                                    ],
                                  ),
                                ),
                                Expanded(
                                  child: ListView.separated(
                                    itemCount: displayJournal.length,
                                    separatorBuilder: (_, __) => const Divider(height: 1, color: Color(0xFFE2E8F0)),
                                    itemBuilder: (c, idx) {
                                      final tx = displayJournal[idx];
                                      final isInc = tx['category'] == 'income';
                                      final amt = (tx['amount'] as num).toDouble();
                                      final bBal = (tx['bank_running_bal'] as num).toDouble();
                                      final dt = DateTime.tryParse(tx['created_at']?.toString() ?? '');
                                      final dtStr = dt != null ? DateFormat('yyyy/MM/dd HH:mm').format(dt) : '-';

                                      return Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                                        color: Colors.white,
                                        child: Row(
                                          children: [
                                            SizedBox(width: 130, child: Text(dtStr, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 12.5, color: Color(0xFF475569)))),
                                            Expanded(flex: 3, child: Text(tx['title'] ?? tx['description'] ?? '-', style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13, color: Color(0xFF0F172A)), overflow: TextOverflow.ellipsis)),
                                            Expanded(flex: 2, child: Text(tx['store_name'] ?? '-', style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13, color: Color(0xFF0F172A)), overflow: TextOverflow.ellipsis)),
                                            SizedBox(width: 130, child: Text(isInc ? _fmt(amt) : '-', textAlign: TextAlign.end, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13, color: Color(0xFF059669)))),
                                            SizedBox(width: 130, child: Text(!isInc ? _fmt(amt) : '-', textAlign: TextAlign.end, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13, color: Color(0xFFDC2626)))),
                                            SizedBox(
                                              width: 140,
                                              child: Text(
                                                _fmt(bBal),
                                                textAlign: TextAlign.end,
                                                style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13.5, color: Color(0xFF0F172A)),
                                              ),
                                            ),
                                          ],
                                        ),
                                      );
                                    },
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _statementStat(String label, String val, Color c, {bool isBig = false}) {
    return Column(
      children: [
        Text(label, style: const TextStyle(color: Color(0xFF64748B), fontSize: 12, fontWeight: FontWeight.bold)),
        const SizedBox(height: 6),
        Text(
          val,
          style: TextStyle(color: c, fontSize: isBig ? 20 : 16, fontWeight: FontWeight.w900, letterSpacing: -0.5),
        ),
      ],
    );
  }

  void _showMerchantStatementDialog(BuildContext context, String merchantName, Map<String, double>? stats, List<Map<String, dynamic>> allLedger, bool isRtl) {
    final mTxList = allLedger.where((item) {
      final sName = item['store_name']?.toString() ?? '';
      return sName.toLowerCase().contains(merchantName.toLowerCase());
    }).toList();

    showDialog(
      context: context,
      builder: (ctx) => Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        child: Container(
          width: 900,
          height: 650,
          padding: const EdgeInsets.all(28),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(color: const Color(0xFFF1F5F9), borderRadius: BorderRadius.circular(12)),
                        child: const Icon(Icons.storefront_rounded, color: Color(0xFF0F172A), size: 28),
                      ),
                      const SizedBox(width: 16),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            '${isRtl ? "كشف حساب التاجر:" : "Merchant Statement:"} $merchantName',
                            style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900, color: Color(0xFF0F172A), letterSpacing: -0.5),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            isRtl ? 'تفاصيل المبيعات، العمولات، والمسحوبات' : 'Sales, commissions and payouts breakdown',
                            style: const TextStyle(color: Color(0xFF64748B), fontSize: 13, fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                    ],
                  ),
                  Row(
                    children: [
                      OutlinedButton.icon(
                        style: OutlinedButton.styleFrom(
                          foregroundColor: const Color(0xFF1E40AF),
                          side: const BorderSide(color: Color(0xFFBFDBFE)),
                          backgroundColor: const Color(0xFFEFF6FF),
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                        ),
                        onPressed: () {
                          Navigator.pop(ctx);
                          context.push(RouteNames.merchantFinancialReport);
                        },
                        icon: const Icon(Icons.analytics_outlined, size: 16),
                        label: Text(
                          isRtl ? 'التقرير الشامل والطباعة' : 'Full Report & Print',
                          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                        ),
                      ),
                      const SizedBox(width: 8),
                      IconButton(icon: const Icon(Icons.close, color: Color(0xFF64748B)), onPressed: () => Navigator.pop(ctx)),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 24),
              if (stats != null)
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF8FAFC),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFFE2E8F0), width: 1.5),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      _statementStat(isRtl ? 'المبيعات' : 'Sales', _fmt(stats['income'] ?? 0), const Color(0xFF0F172A)),
                      _statementStat(isRtl ? 'عمولة المنصة' : 'Comm', _fmt(stats['commission'] ?? 0), const Color(0xFF059669)),
                      _statementStat(isRtl ? 'المسحوبات' : 'Paid', _fmt(stats['expense'] ?? 0), const Color(0xFFDC2626)),
                      _statementStat(isRtl ? 'المتبقي للسحب' : 'Available', _fmt((stats['merchant_share'] ?? 0) - (stats['expense'] ?? 0)), const Color(0xFF2563EB), isBig: true),
                    ],
                  ),
                ),
              const SizedBox(height: 24),
              Expanded(
                child: ListView.separated(
                  itemCount: mTxList.length,
                  separatorBuilder: (_, __) => const Divider(height: 1, color: Color(0xFFE2E8F0)),
                  itemBuilder: (c, idx) {
                    final item = mTxList[idx];
                    final isInc = item['category'] == 'income';
                    final amt = (item['amount'] as num).toDouble();
                    final dt = DateTime.tryParse(item['created_at']?.toString() ?? '');
                    final dtStr = dt != null ? DateFormat('yyyy/MM/dd HH:mm').format(dt) : '-';
                    final staffName = item['staff_name'] as String?;
                    final transFee = (item['transport_fee'] as num?)?.toDouble() ?? 0.0;
                    final offloadFee = (item['offloading_fee'] as num?)?.toDouble() ?? 0.0;
                    final comm = (item['commission'] as num?)?.toDouble() ?? 0.0;

                    return ListTile(
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      leading: CircleAvatar(
                        backgroundColor: isInc ? const Color(0xFFF0FDF4) : const Color(0xFFFEF2F2),
                        child: Icon(isInc ? Icons.arrow_downward : Icons.arrow_upward, color: isInc ? const Color(0xFF059669) : const Color(0xFFDC2626), size: 18),
                      ),
                      title: Row(
                        children: [
                          Expanded(
                            child: Text(
                              item['title'] ?? item['description'] ?? '-',
                              style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF0F172A), fontSize: 13.5),
                            ),
                          ),
                          if (staffName != null && staffName.isNotEmpty) ...[
                            const SizedBox(width: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: const Color(0xFFF1F5F9),
                                borderRadius: BorderRadius.circular(6),
                                border: Border.all(color: const Color(0xFFCBD5E1)),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(Icons.badge_outlined, size: 11, color: Color(0xFF475569)),
                                  const SizedBox(width: 4),
                                  Text(
                                    '${isRtl ? "الموظف" : "Staff"}: $staffName',
                                    style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: Color(0xFF334155)),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ],
                      ),
                      subtitle: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const SizedBox(height: 2),
                          Text('$dtStr | ${item['bank_name'] ?? "-"}', style: const TextStyle(color: Color(0xFF64748B), fontSize: 12, fontWeight: FontWeight.bold)),
                          if (transFee > 0 || offloadFee > 0 || (isInc && comm > 0)) ...[
                            const SizedBox(height: 2),
                            Wrap(
                              spacing: 8,
                              children: [
                                if (transFee > 0)
                                  Text('${isRtl ? "ترحيل" : "Transport"}: ${_fmt(transFee)}', style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontWeight: FontWeight.w600)),
                                if (offloadFee > 0)
                                  Text('${isRtl ? "عتالة" : "Offload"}: ${_fmt(offloadFee)}', style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontWeight: FontWeight.w600)),
                                if (isInc && comm > 0)
                                  Text('${isRtl ? "عمولة المنصة" : "Platform Comm"}: ${_fmt(comm)}', style: const TextStyle(fontSize: 11, color: Color(0xFF059669), fontWeight: FontWeight.w600)),
                              ],
                            ),
                          ],
                        ],
                      ),
                      trailing: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text(
                            (isInc ? '+' : '-') + _fmt(amt),
                            style: TextStyle(fontWeight: FontWeight.w900, fontSize: 14, color: isInc ? const Color(0xFF059669) : const Color(0xFFDC2626)),
                          ),
                          if (isInc && item['merchant_share'] != null)
                            Text(
                              '${isRtl ? "صافي المتجر: " : "Net: "}${_fmt((item['merchant_share'] as num).toDouble())}',
                              style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700, color: Color(0xFF64748B)),
                            ),
                        ],
                      ),
                    );
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ═════════════════════════════════════════════════════════════════
  // OTHER UNCHANGED MODALS...
  // ═════════════════════════════════════════════════════════════════

  void _showCommissionSettingsDialog(BuildContext context, bool isRtl) {
    showDialog(
      context: context,
      builder: (ctx) => _CommissionAuditReportDialog(isRtl: isRtl, parentContext: context),
    );
  }

  // ═════════════════════════════════════════════════════════════════
  // BANK ACCOUNTS MANAGER DIALOG
  // ═════════════════════════════════════════════════════════════════
  void _showBankManagementDialog(BuildContext context, bool isRtl) {
    showDialog(
      context: context,
      builder: (ctx) => Consumer(
        builder: (context, ref, child) {
          final banksState = ref.watch(banksProvider);

          return AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            title: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    const Icon(Icons.account_balance, color: Color(0xFF2563EB), size: 24),
                    const SizedBox(width: 10),
                    Text(
                      isRtl ? 'إدارة البنوك والمحافظ' : 'Manage Platform Banks',
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
                    ),
                  ],
                ),
                FilledButton.icon(
                  style: FilledButton.styleFrom(
                    backgroundColor: const Color(0xFF2563EB),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  icon: const Icon(Icons.add, size: 16),
                  label: Text(isRtl ? 'إضافة بنك' : 'Add Bank', style: const TextStyle(fontWeight: FontWeight.w900)),
                  onPressed: () => _openAddEditBankDialog(context, null, isRtl),
                ),
              ],
            ),
            content: SizedBox(
              width: 600,
              height: 400,
              child: banksState.when(
                loading: () => const Center(child: CircularProgressIndicator()),
                error: (e, st) => AdminErrorView(
                  error: e,
                  stackTrace: st,
                  onRetry: () => ref.read(banksProvider.notifier).loadBanks(),
                ),
                data: (banks) {
                  if (banks.isEmpty) {
                    return Center(child: Text(isRtl ? 'لا توجد بنوك مسجلة' : 'No banks added', style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF0F172A))));
                  }
                  return ListView.separated(
                    itemCount: banks.length,
                    separatorBuilder: (_, __) => const Divider(height: 1, color: Color(0xFFCBD5E1)),
                    itemBuilder: (c, idx) {
                      final b = banks[idx];
                      return ListTile(
                        title: Text(b.nameAr, style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF0F172A))),
                        subtitle: Text(
                          '${b.nameEn} | ${isRtl ? "رقم الحساب" : "A/C"}: ${b.accountNumber ?? "-"}',
                          style: const TextStyle(color: Color(0xFF475569), fontSize: 12, fontWeight: FontWeight.bold),
                        ),
                        trailing: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Switch(
                              value: b.isActive,
                              activeColor: const Color(0xFF047857),
                              onChanged: (v) {
                                ref.read(banksProvider.notifier).toggleBankActive(b.id, v);
                              },
                            ),
                            IconButton(
                              icon: const Icon(Icons.edit, color: Color(0xFF2563EB), size: 20),
                              onPressed: () => _openAddEditBankDialog(context, b, isRtl),
                            ),
                          ],
                        ),
                      );
                    },
                  );
                },
              ),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: Text(isRtl ? 'إغلاق' : 'Close', style: const TextStyle(fontWeight: FontWeight.w900)),
              ),
            ],
          );
        },
      ),
    );
  }

  void _openAddEditBankDialog(BuildContext context, PlatformBank? bank, bool isRtl) {
    final nameArCtrl = TextEditingController(text: bank?.nameAr ?? '');
    final nameEnCtrl = TextEditingController(text: bank?.nameEn ?? '');
    final accNumCtrl = TextEditingController(text: bank?.accountNumber ?? '');
    final accNameCtrl = TextEditingController(text: bank?.accountName ?? '');
    final codeCtrl = TextEditingController(text: bank?.code ?? '');
    bool isActive = bank?.isActive ?? true;

    showDialog(
      context: context,
      builder: (dialogCtx) => StatefulBuilder(
        builder: (ctx, setSt) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: Text(
            bank == null ? (isRtl ? 'إضافة بنك جديد' : 'Add Bank') : (isRtl ? 'تعديل بيانات البنك' : 'Edit Bank'),
            style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
          ),
          content: SingleChildScrollView(
            child: SizedBox(
              width: 450,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  TextField(controller: nameArCtrl, decoration: InputDecoration(labelText: isRtl ? 'اسم البنك (بالعربي)' : 'Bank Name (Arabic)', border: const OutlineInputBorder())),
                  const SizedBox(height: 10),
                  TextField(controller: nameEnCtrl, decoration: InputDecoration(labelText: isRtl ? 'اسم البنك (بالإنجليزي)' : 'Bank Name (English)', border: const OutlineInputBorder())),
                  const SizedBox(height: 10),
                  TextField(controller: accNumCtrl, decoration: InputDecoration(labelText: isRtl ? 'رقم الحساب أو الآيبان' : 'Account / IBAN Number', border: const OutlineInputBorder())),
                  const SizedBox(height: 10),
                  TextField(controller: accNameCtrl, decoration: InputDecoration(labelText: isRtl ? 'اسم صاحب الحساب' : 'Account Holder Name', border: const OutlineInputBorder())),
                  const SizedBox(height: 10),
                  TextField(controller: codeCtrl, decoration: InputDecoration(labelText: isRtl ? 'رمز البنك (Bank Code)' : 'Bank Code', border: const OutlineInputBorder())),
                ],
              ),
            ),
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(dialogCtx), child: Text(isRtl ? 'إلغاء' : 'Cancel', style: const TextStyle(fontWeight: FontWeight.w900))),
            FilledButton(
              style: FilledButton.styleFrom(backgroundColor: const Color(0xFF2563EB)),
              onPressed: () async {
                if (nameArCtrl.text.trim().isEmpty) return;
                Navigator.pop(dialogCtx);
                final nameEn = nameEnCtrl.text.trim().isNotEmpty ? nameEnCtrl.text.trim() : nameArCtrl.text.trim();

                if (bank == null) {
                  await ref.read(banksProvider.notifier).addBank(
                    nameAr: nameArCtrl.text.trim(),
                    nameEn: nameEn,
                    code: codeCtrl.text.trim().isNotEmpty ? codeCtrl.text.trim() : null,
                    accountNumber: accNumCtrl.text.trim().isNotEmpty ? accNumCtrl.text.trim() : null,
                    accountName: accNameCtrl.text.trim().isNotEmpty ? accNameCtrl.text.trim() : null,
                  );
                } else {
                  await ref.read(banksProvider.notifier).updateBank(
                    id: bank.id,
                    nameAr: nameArCtrl.text.trim(),
                    nameEn: nameEn,
                    code: codeCtrl.text.trim().isNotEmpty ? codeCtrl.text.trim() : null,
                    accountNumber: accNumCtrl.text.trim().isNotEmpty ? accNumCtrl.text.trim() : null,
                    accountName: accNameCtrl.text.trim().isNotEmpty ? accNameCtrl.text.trim() : null,
                    isActive: isActive,
                  );
                }
              },
              child: Text(isRtl ? 'حفظ' : 'Save', style: const TextStyle(fontWeight: FontWeight.w900)),
            ),
          ],
        ),
      ),
    );
  }
}

class _SliverAppBarDelegate extends SliverPersistentHeaderDelegate {
  _SliverAppBarDelegate({required this.minHeight, required this.maxHeight, required this.child});
  final double minHeight;
  final double maxHeight;
  final Widget child;

  @override
  double get minExtent => minHeight;
  @override
  double get maxExtent => maxHeight;
  @override
  Widget build(BuildContext context, double shrinkOffset, bool overlapsContent) => SizedBox.expand(child: child);
  @override
  bool shouldRebuild(_SliverAppBarDelegate oldDelegate) =>
      maxHeight != oldDelegate.maxHeight || minHeight != oldDelegate.minHeight || child != oldDelegate.child;
}

// ═════════════════════════════════════════════════════════════════
// COMPREHENSIVE COMMISSION & CATEGORY AUDIT REPORT DIALOG
// ═════════════════════════════════════════════════════════════════
class _CommissionAuditReportDialog extends StatefulWidget {
  final bool isRtl;
  final BuildContext parentContext;

  const _CommissionAuditReportDialog({
    Key? key,
    required this.isRtl,
    required this.parentContext,
  }) : super(key: key);

  @override
  State<_CommissionAuditReportDialog> createState() => _CommissionAuditReportDialogState();
}

class _CommissionAuditReportDialogState extends State<_CommissionAuditReportDialog> {
  bool _isLoading = true;
  String _error = '';
  String _searchQuery = '';

  bool _enableCommission = false;
  double _globalCommissionRate = 0.0;

  List<Map<String, dynamic>> _stores = [];
  Map<String, Map<String, dynamic>> _userPermissions = {};
  Map<String, String> _categoryNames = {};
  Map<String, String> _subcategoryNames = {};

  @override
  void initState() {
    super.initState();
    _loadAllCommissionData();
  }

  Future<void> _loadAllCommissionData() async {
    setState(() {
      _isLoading = true;
      _error = '';
    });

    try {
      final supabase = Supabase.instance.client;

      final settingsRes = await supabase
          .from('gamification_settings')
          .select('enable_commission, commission_percentage')
          .eq('id', 1)
          .maybeSingle();

      if (settingsRes != null) {
        _enableCommission = settingsRes['enable_commission'] == true;
        _globalCommissionRate = (num.tryParse(settingsRes['commission_percentage']?.toString() ?? '0') ?? 0.0).toDouble();
      }

      final catRes = await supabase.from('categories').select('*, subcategories(*)').order('name_ar');
      Map<String, String> catNames = {};
      Map<String, String> subNames = {};

      for (var c in catRes) {
        final cId = c['id']?.toString() ?? '';
        final cName = (c['name_ar'] != null && c['name_ar'].toString().isNotEmpty) ? c['name_ar'].toString() : (c['name']?.toString() ?? '');
        catNames[cId] = cName;

        if (c['subcategories'] is List) {
          for (var s in c['subcategories']) {
            final sId = s['id']?.toString() ?? '';
            final sName = (s['name_ar'] != null && s['name_ar'].toString().isNotEmpty) ? s['name_ar'].toString() : (s['name']?.toString() ?? '');
            subNames[sId] = sName;
          }
        }
      }

      final storesRes = await supabase
          .from('stores')
          .select('id, name, name_ar, owner_id, custom_commission_percentage, has_custom_commission, created_at')
          .order('created_at', ascending: false);

      final usersRes = await supabase.from('users').select('id, name, phone, permissions');
      Map<String, Map<String, dynamic>> userPerms = {};
      for (var u in usersRes) {
        final uId = u['id']?.toString() ?? '';
        userPerms[uId] = {
          'name': u['name']?.toString() ?? 'تاجر',
          'phone': u['phone']?.toString() ?? '',
          'permissions': u['permissions'] is Map ? Map<String, dynamic>.from(u['permissions']) : <String, dynamic>{},
        };
      }

      if (mounted) {
        setState(() {
          _categoryNames = catNames;
          _subcategoryNames = subNames;
          _stores = List<Map<String, dynamic>>.from(storesRes);
          _userPermissions = userPerms;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = e.toString();
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = widget.isRtl;

    return Directionality(
      textDirection: isRtl ? TextDirection.rtl : TextDirection.ltr,
      child: Dialog(
        backgroundColor: Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        insetPadding: const EdgeInsets.symmetric(horizontal: 24, vertical: 24),
        child: Container(
          width: 950,
          height: 720,
          padding: const EdgeInsets.all(24),
          child: _isLoading
              ? const Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      CircularProgressIndicator(),
                      SizedBox(height: 16),
                      Text('جاري تحميل كشف وتقرير العمولات والتصنيفات...', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.blueGrey)),
                    ],
                  ),
                )
              : _error.isNotEmpty
                  ? Center(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.error_outline, color: Colors.red, size: 48),
                          const SizedBox(height: 12),
                          Text('حدث خطأ أثناء تحميل البيانات: $_error', style: const TextStyle(color: Colors.red)),
                          const SizedBox(height: 12),
                          ElevatedButton.icon(
                            icon: const Icon(Icons.refresh),
                            label: const Text('إعادة المحاولة'),
                            onPressed: _loadAllCommissionData,
                          ),
                        ],
                      ),
                    )
                  : _buildReportContent(isRtl),
        ),
      ),
    );
  }

  Widget _buildReportContent(bool isRtl) {
    final filteredStores = _stores.where((store) {
      final sName = (store['name_ar'] ?? store['name'] ?? '').toString().toLowerCase();
      final ownerId = store['owner_id']?.toString() ?? '';
      final ownerData = _userPermissions[ownerId];
      final ownerName = (ownerData?['name'] ?? '').toString().toLowerCase();
      final q = _searchQuery.toLowerCase();
      return sName.contains(q) || ownerName.contains(q);
    }).toList();

    final customCount = _stores.where((s) => s['has_custom_commission'] == true).length;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFD97706).withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: const Icon(Icons.analytics_rounded, color: Color(0xFFD97706), size: 28),
                ),
                const SizedBox(width: 14),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      isRtl ? 'كشف وتقرير هيكلية العمولات والتصنيفات الشامل' : 'Comprehensive Merchant Commission & Category Audit Report',
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      isRtl
                          ? 'استعراض دقيق لجميع التجار والفروع والأقسام ونسب العمولات المخصصة والعامة'
                          : 'Overview of all merchants, branches, allowed categories, and custom rates',
                      style: const TextStyle(fontSize: 12, color: Color(0xFF64748B), fontWeight: FontWeight.bold),
                    ),
                  ],
                ),
              ],
            ),
            IconButton(
              icon: const Icon(Icons.close, color: Color(0xFF64748B)),
              onPressed: () => Navigator.pop(context),
            ),
          ],
        ),
        const SizedBox(height: 16),

        Container(
          padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
            ),
            borderRadius: BorderRadius.circular(16),
            boxShadow: [
              BoxShadow(color: Colors.black.withValues(alpha: 0.06), blurRadius: 10, offset: const Offset(0, 4)),
            ],
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF59E0B).withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.percent_rounded, color: Color(0xFFFBBF24), size: 20),
                  ),
                  const SizedBox(width: 10),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        isRtl ? 'النسبة العامة الافتراضية للمنصة' : 'Default Global Commission',
                        style: const TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold),
                      ),
                      Text(
                        _enableCommission ? '$_globalCommissionRate% (مفعلة تلقائياً)' : 'نظام العمولات العام معطل',
                        style: TextStyle(
                          color: _enableCommission ? const Color(0xFFFDE68A) : Colors.redAccent,
                          fontWeight: FontWeight.w900,
                          fontSize: 15,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
              _statHeaderItem(
                label: isRtl ? 'إجمالي المتاجر' : 'Total Stores',
                val: '${_stores.length}',
                color: const Color(0xFF93C5FD),
              ),
              _statHeaderItem(
                label: isRtl ? 'بعمولات مخصصة' : 'Custom Rates',
                val: '$customCount',
                color: const Color(0xFFFDE68A),
              ),
              FilledButton.icon(
                style: FilledButton.styleFrom(
                  backgroundColor: const Color(0xFFD97706),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                icon: const Icon(Icons.tune_rounded, size: 16),
                label: Text(isRtl ? 'تعديل النسبة العامة' : 'Edit Global', style: const TextStyle(fontWeight: FontWeight.w900)),
                onPressed: _showEditGlobalCommissionDialog,
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        Row(
          children: [
            Expanded(
              child: TextField(
                onChanged: (val) => setState(() => _searchQuery = val),
                style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF0F172A), fontSize: 13.5),
                decoration: InputDecoration(
                  hintText: isRtl ? 'ابحث باسم المتجر، الفرع، أو التاجر...' : 'Search by store name or owner...',
                  prefixIcon: const Icon(Icons.search, color: Color(0xFF64748B)),
                  filled: true,
                  fillColor: const Color(0xFFF8FAFC),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                  enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                ),
              ),
            ),
            const SizedBox(width: 12),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
              decoration: BoxDecoration(
                color: const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFCBD5E1)),
              ),
              child: Text(
                '${filteredStores.length} ' + (isRtl ? 'تاجر ومتجر' : 'stores'),
                style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF0F172A), fontSize: 12),
              ),
            ),
          ],
        ),
        const SizedBox(height: 14),

        Expanded(
          child: filteredStores.isEmpty
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.storefront_outlined, size: 48, color: Colors.grey.shade300),
                      const SizedBox(height: 10),
                      Text(
                        isRtl ? 'لا توجد متاجر تطابق البحث' : 'No stores match search',
                        style: const TextStyle(color: Color(0xFF0F172A), fontWeight: FontWeight.w900),
                      ),
                    ],
                  ),
                )
              : ListView.separated(
                  itemCount: filteredStores.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 12),
                  itemBuilder: (context, i) {
                    final store = filteredStores[i];
                    final sId = store['id']?.toString() ?? '';
                    final sName = (store['name_ar'] != null && store['name_ar'].toString().isNotEmpty)
                        ? store['name_ar'].toString()
                        : (store['name']?.toString() ?? 'متجر بدون اسم');
                    final ownerId = store['owner_id']?.toString() ?? '';
                    final ownerData = _userPermissions[ownerId];
                    final ownerName = ownerData?['name'] ?? 'مالك المتجر';
                    final ownerPhone = ownerData?['phone'] ?? '';
                    final permissions = ownerData?['permissions'] ?? {};

                    final hasCustomCommission = store['has_custom_commission'] == true;
                    final customRate = store['custom_commission_percentage'];

                    final allowedCats = permissions['allowed_categories'];
                    final bool isAllCategories = allowedCats == null || allowedCats == 'all';
                    final List<dynamic> allowedCatList = (allowedCats is List) ? allowedCats : [];

                    final categoryCommissions = (permissions['category_commissions'] is Map)
                        ? Map<String, dynamic>.from(permissions['category_commissions'])
                        : <String, dynamic>{};

                    return Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(
                          color: hasCustomCommission || categoryCommissions.isNotEmpty
                              ? const Color(0xFFF59E0B)
                              : const Color(0xFFCBD5E1),
                          width: hasCustomCommission || categoryCommissions.isNotEmpty ? 1.5 : 1,
                        ),
                        boxShadow: [
                          BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 6, offset: const Offset(0, 2)),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Row(
                                children: [
                                  CircleAvatar(
                                    radius: 18,
                                    backgroundColor: hasCustomCommission
                                        ? const Color(0xFFFEF3C7)
                                        : const Color(0xFFEFF6FF),
                                    child: Icon(
                                      Icons.storefront_rounded,
                                      size: 18,
                                      color: hasCustomCommission
                                          ? const Color(0xFFD97706)
                                          : const Color(0xFF2563EB),
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Row(
                                        children: [
                                          Text(
                                            sName,
                                            style: const TextStyle(
                                              fontSize: 14.5,
                                              fontWeight: FontWeight.w900,
                                              color: Color(0xFF0F172A),
                                            ),
                                          ),
                                          const SizedBox(width: 8),
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                            decoration: BoxDecoration(
                                              color: const Color(0xFFF1F5F9),
                                              borderRadius: BorderRadius.circular(6),
                                            ),
                                            child: Text(
                                              '#${sId.length > 6 ? sId.substring(0, 6) : sId}',
                                              style: const TextStyle(fontSize: 10, color: Color(0xFF0F172A), fontWeight: FontWeight.w900),
                                            ),
                                          ),
                                        ],
                                      ),
                                      Text(
                                        'التاجر: $ownerName' + (ownerPhone.isNotEmpty ? ' ($ownerPhone)' : ''),
                                        style: const TextStyle(fontSize: 11.5, color: Color(0xFF475569), fontWeight: FontWeight.bold),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                              Row(
                                children: [
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                    decoration: BoxDecoration(
                                      color: hasCustomCommission ? const Color(0xFFFEF3C7) : const Color(0xFFF8FAFC),
                                      borderRadius: BorderRadius.circular(10),
                                      border: Border.all(
                                        color: hasCustomCommission ? const Color(0xFFF59E0B) : const Color(0xFFCBD5E1),
                                      ),
                                    ),
                                    child: Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Icon(
                                          Icons.label_important_rounded,
                                          size: 14,
                                          color: hasCustomCommission ? const Color(0xFFB45309) : const Color(0xFF475569),
                                        ),
                                        const SizedBox(width: 4),
                                        Text(
                                          hasCustomCommission
                                              ? (isRtl ? 'عمولة المتجر: $customRate%' : 'Store Rate: $customRate%')
                                              : (isRtl ? 'يطبق الافتراضي ($_globalCommissionRate%)' : 'Uses Global ($_globalCommissionRate%)'),
                                          style: TextStyle(
                                            fontSize: 11.5,
                                            fontWeight: FontWeight.w900,
                                            color: hasCustomCommission ? const Color(0xFFB45309) : const Color(0xFF0F172A),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  OutlinedButton.icon(
                                    style: OutlinedButton.styleFrom(
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                      visualDensity: VisualDensity.compact,
                                      side: const BorderSide(color: Color(0xFF2563EB)),
                                    ),
                                    icon: const Icon(Icons.edit, size: 14, color: Color(0xFF2563EB)),
                                    label: Text(
                                      isRtl ? 'تعديل الصلاحيات والعمولة' : 'Edit Rate',
                                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w900, color: Color(0xFF2563EB)),
                                    ),
                                    onPressed: () => _openStoreCommissionEditor(store, ownerData),
                                  ),
                                ],
                              ),
                            ],
                          ),
                          const Divider(height: 18, color: Color(0xFFCBD5E1)),
                          Wrap(
                            spacing: 6,
                            runSpacing: 6,
                            crossAxisAlignment: WrapCrossAlignment.center,
                            children: [
                              Text(
                                isRtl ? 'الأقسام والعمولات المخصصة:' : 'Categories & Rates:',
                                style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
                              ),
                              if (isAllCategories)
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFDCFCE7),
                                    borderRadius: BorderRadius.circular(6),
                                    border: Border.all(color: const Color(0xFF86EFAC)),
                                  ),
                                  child: const Text(
                                    '✓ مصرح بكافة أقسام السوق',
                                    style: TextStyle(fontSize: 11, color: Color(0xFF166534), fontWeight: FontWeight.w900),
                                  ),
                                )
                              else ...[
                                ...allowedCatList.map((catId) {
                                  final cName = _categoryNames[catId.toString()] ?? _subcategoryNames[catId.toString()] ?? 'صنف';
                                  final hasSpecificRate = categoryCommissions.containsKey(catId.toString());
                                  final specificRate = categoryCommissions[catId.toString()];

                                  return Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                    decoration: BoxDecoration(
                                      color: hasSpecificRate ? const Color(0xFFFEF3C7) : const Color(0xFFF1F5F9),
                                      borderRadius: BorderRadius.circular(6),
                                      border: Border.all(
                                        color: hasSpecificRate ? const Color(0xFFF59E0B) : const Color(0xFFCBD5E1),
                                      ),
                                    ),
                                    child: Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Text(
                                          cName,
                                          style: TextStyle(
                                            fontSize: 11,
                                            fontWeight: FontWeight.w900,
                                            color: hasSpecificRate ? const Color(0xFF92400E) : const Color(0xFF0F172A),
                                          ),
                                        ),
                                        if (hasSpecificRate) ...[
                                          const SizedBox(width: 4),
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                                            decoration: BoxDecoration(
                                              color: const Color(0xFFF59E0B),
                                              borderRadius: BorderRadius.circular(4),
                                            ),
                                            child: Text(
                                              '$specificRate%',
                                              style: const TextStyle(fontSize: 9.5, color: Colors.white, fontWeight: FontWeight.w900),
                                            ),
                                          ),
                                        ],
                                      ],
                                    ),
                                  );
                                }),
                              ],
                              if (categoryCommissions.isNotEmpty && isAllCategories) ...[
                                ...categoryCommissions.entries.map((entry) {
                                  final cName = _categoryNames[entry.key] ?? _subcategoryNames[entry.key] ?? 'صنف';
                                  return Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFFEF3C7),
                                      borderRadius: BorderRadius.circular(6),
                                      border: Border.all(color: const Color(0xFFF59E0B)),
                                    ),
                                    child: Text(
                                      '🎯 $cName: ${entry.value}%',
                                      style: const TextStyle(fontSize: 11, color: Color(0xFF92400E), fontWeight: FontWeight.w900),
                                    ),
                                  );
                                }),
                              ],
                            ],
                          ),
                        ],
                      ),
                    );
                  },
                ),
        ),
      ],
    );
  }

  Widget _statHeaderItem({required String label, required String val, required Color color}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.center,
      children: [
        Text(label, style: const TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold)),
        const SizedBox(height: 2),
        Text(val, style: TextStyle(color: color, fontWeight: FontWeight.w900, fontSize: 16)),
      ],
    );
  }

  void _showEditGlobalCommissionDialog() {
    final ctrl = TextEditingController(text: _globalCommissionRate.toStringAsFixed(1));
    bool enabled = _enableCommission;

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx2, setSt) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: Text(widget.isRtl ? 'تعديل النسبة العامة للمنصة' : 'Edit Global Platform Commission', style: const TextStyle(fontWeight: FontWeight.w900)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              SwitchListTile(
                title: Text(widget.isRtl ? 'تفعيل نظام العمولة' : 'Enable Commission', style: const TextStyle(fontWeight: FontWeight.w900)),
                value: enabled,
                activeThumbColor: const Color(0xFFD97706),
                onChanged: (v) => setSt(() => enabled = v),
              ),
              if (enabled) ...[
                const SizedBox(height: 10),
                TextField(
                  controller: ctrl,
                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  decoration: InputDecoration(
                    labelText: widget.isRtl ? 'النسبة العامة (%)' : 'Rate (%)',
                    border: const OutlineInputBorder(),
                    prefixIcon: const Icon(Icons.percent),
                  ),
                ),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 4,
                  children: [1.0, 2.5, 5.0, 7.5, 10.0].map((p) => ActionChip(
                    label: Text('$p%'),
                    onPressed: () => setSt(() => ctrl.text = p.toString()),
                  )).toList(),
                ),
              ],
            ],
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx), child: Text(widget.isRtl ? 'إلغاء' : 'Cancel', style: const TextStyle(fontWeight: FontWeight.w900))),
            FilledButton(
              style: FilledButton.styleFrom(backgroundColor: const Color(0xFFD97706)),
              onPressed: () async {
                final newRate = enabled ? (double.tryParse(ctrl.text.trim()) ?? 0.0) : 0.0;
                Navigator.pop(ctx);
                await Supabase.instance.client.from('gamification_settings').update({
                  'enable_commission': enabled,
                  'commission_percentage': newRate,
                }).eq('id', 1);

                _loadAllCommissionData();
              },
              child: Text(widget.isRtl ? 'حفظ' : 'Save', style: const TextStyle(fontWeight: FontWeight.w900)),
            ),
          ],
        ),
      ),
    );
  }

  void _openStoreCommissionEditor(Map<String, dynamic> store, Map<String, dynamic>? ownerData) async {
    final sName = (store['name_ar'] ?? store['name'] ?? 'متجر').toString();
    final hasCustom = store['has_custom_commission'] == true;
    final rate = store['custom_commission_percentage'];
    final sId = store['id']?.toString() ?? '';

    final ctrl = TextEditingController(text: rate != null ? rate.toString() : '');
    bool editHasCustom = hasCustom;

    final saved = await showDialog<bool>(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx2, setSt) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: Text(widget.isRtl ? 'تخصيص عمولة: $sName' : 'Set Custom Commission: $sName', style: const TextStyle(fontWeight: FontWeight.w900)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              SwitchListTile(
                title: Text(widget.isRtl ? 'تفعيل عمولة مخصصة لهذا المتجر' : 'Custom Commission', style: const TextStyle(fontWeight: FontWeight.w900)),
                subtitle: Text(
                  widget.isRtl ? 'تجاوز النسبة العامة للمنصة ($_globalCommissionRate%)' : 'Overrides platform default rate',
                  style: const TextStyle(fontSize: 11, color: Color(0xFF475569), fontWeight: FontWeight.bold),
                ),
                value: editHasCustom,
                activeThumbColor: const Color(0xFFD97706),
                onChanged: (v) => setSt(() => editHasCustom = v),
              ),
              if (editHasCustom) ...[
                const SizedBox(height: 10),
                TextField(
                  controller: ctrl,
                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  decoration: InputDecoration(
                    labelText: widget.isRtl ? 'نسبة العمولة الخاصة (%)' : 'Rate (%)',
                    border: const OutlineInputBorder(),
                    prefixIcon: const Icon(Icons.percent),
                  ),
                ),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 4,
                  children: [1.0, 2.0, 2.5, 3.0, 5.0, 7.0, 10.0].map((p) => ActionChip(
                    label: Text('$p%'),
                    onPressed: () => setSt(() => ctrl.text = p.toString()),
                  )).toList(),
                ),
              ],
            ],
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx, false), child: Text(widget.isRtl ? 'إلغاء' : 'Cancel', style: const TextStyle(fontWeight: FontWeight.w900))),
            FilledButton(
              style: FilledButton.styleFrom(backgroundColor: const Color(0xFFD97706)),
              onPressed: () async {
                final newRate = editHasCustom ? (double.tryParse(ctrl.text.trim()) ?? 0.0) : null;
                Navigator.pop(ctx, true);
                await Supabase.instance.client.from('stores').update({
                  'has_custom_commission': editHasCustom,
                  'custom_commission_percentage': newRate,
                }).eq('id', sId);
              },
              child: Text(widget.isRtl ? 'حفظ' : 'Save', style: const TextStyle(fontWeight: FontWeight.w900)),
            ),
          ],
        ),
      ),
    );

    if (saved == true) {
      _loadAllCommissionData();
    }
  }
}
