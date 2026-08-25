import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' as intl;
import 'package:magaiees_marketplace/features/admin/finance/application/admin_finance_provider.dart';

class AdminFinanceScreen extends ConsumerStatefulWidget {
  const AdminFinanceScreen({Key? key}) : super(key: key);

  @override
  ConsumerState<AdminFinanceScreen> createState() => _AdminFinanceScreenState();
}

class _AdminFinanceScreenState extends ConsumerState<AdminFinanceScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final _currencyFormat = intl.NumberFormat.currency(symbol: 'SDG ', decimalDigits: 2);

  // Corporate Fintech Palette
  final Color _primaryDark = const Color(0xFF0F172A);
  final Color _primaryAccent = const Color(0xFF1E40AF);
  final Color _surfaceColor = Colors.white;
  final Color _backgroundLight = const Color(0xFFF1F5F9);

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final financeState = ref.watch(adminFinanceProvider);
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    return Scaffold(
      backgroundColor: _backgroundLight,
      appBar: AppBar(
        title: Text(
          isRtl ? 'المالية والمدفوعات' : 'Finance & Payouts',
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
        centerTitle: true,
        backgroundColor: _primaryDark,
        foregroundColor: Colors.white,
        elevation: 0,
        bottom: TabBar(
          controller: _tabController,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          indicatorColor: _primaryAccent,
          indicatorWeight: 4,
          tabs: [
            Tab(text: isRtl ? 'الطلبات المعلقة' : 'Pending Payouts'),
            Tab(text: isRtl ? 'سجل المدفوعات' : 'Payout History'),
          ],
        ),
      ),
      body: financeState.when(
        loading: () => Center(child: CircularProgressIndicator(color: _primaryAccent)),
        error: (err, st) => Center(child: Text(isRtl ? 'حدث خطأ: $err' : 'Error: $err')),
        data: (data) {
          final totalSales = data['totalSales'] as double? ?? 0.0;
          final totalCommission = data['totalPlatformCommission'] as double;
          final pending = data['pendingPayoutRequests'] as List<Map<String, dynamic>>;
          final completed = data['completedPayouts'] as List<Map<String, dynamic>>;

          double outstandingBalances = 0.0;
          for (var req in pending) {
            outstandingBalances += (num.tryParse(req['amount']?.toString() ?? '0') ?? 0.0).toDouble();
          }

          return Column(
            children: [
              _buildDashboardHeader(totalSales, totalCommission, outstandingBalances, isRtl),
              Expanded(
                child: TabBarView(
                  controller: _tabController,
                  children: [
                    _buildPendingTable(pending, isRtl),
                    _buildHistoryTable(completed, isRtl),
                  ],
                ),
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildDashboardHeader(double totalRevenue, double magaieesShare, double outstandingBalances, bool isRtl) {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: _primaryDark,
        borderRadius: const BorderRadius.only(
          bottomLeft: Radius.circular(32),
          bottomRight: Radius.circular(32),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 10,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            isRtl ? 'نظرة عامة على الإيرادات' : 'Revenue Overview',
            style: const TextStyle(
              color: Colors.white70,
              fontSize: 16,
              fontWeight: FontWeight.w600,
              letterSpacing: 0.5,
            ),
          ),
          const SizedBox(height: 16),
          LayoutBuilder(
            builder: (context, constraints) {
              if (constraints.maxWidth > 600) {
                return Row(
                  children: [
                    Expanded(child: _buildStatCard(isRtl ? 'إجمالي الإيرادات' : 'Total Revenue', totalRevenue, Icons.trending_up, Colors.blueAccent, isRtl)),
                    const SizedBox(width: 16),
                    Expanded(child: _buildStatCard(isRtl ? 'أرباح مقاييس' : 'Magaiees Share', magaieesShare, Icons.account_balance_wallet, Colors.greenAccent, isRtl)),
                    const SizedBox(width: 16),
                    Expanded(child: _buildStatCard(isRtl ? 'الأرصدة المستحقة' : 'Outstanding Balances', outstandingBalances, Icons.pending_actions, Colors.orangeAccent, isRtl)),
                  ],
                );
              }
              return Column(
                children: [
                  Row(
                    children: [
                      Expanded(child: _buildStatCard(isRtl ? 'إجمالي الإيرادات' : 'Total Revenue', totalRevenue, Icons.trending_up, Colors.blueAccent, isRtl)),
                      const SizedBox(width: 12),
                      Expanded(child: _buildStatCard(isRtl ? 'أرباح مقاييس' : 'Magaiees Share', magaieesShare, Icons.account_balance_wallet, Colors.greenAccent, isRtl)),
                    ],
                  ),
                  const SizedBox(height: 12),
                  _buildStatCard(isRtl ? 'الأرصدة المستحقة' : 'Outstanding Balances', outstandingBalances, Icons.pending_actions, Colors.orangeAccent, isRtl),
                ],
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildStatCard(String title, double amount, IconData icon, Color iconColor, bool isRtl) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.05),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white.withOpacity(0.1)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: iconColor.withOpacity(0.2),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(icon, color: iconColor, size: 20),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  title,
                  style: const TextStyle(
                    color: Colors.white70,
                    fontSize: 13,
                    fontWeight: FontWeight.w500,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Text(
            _currencyFormat.format(amount),
            style: const TextStyle(
              color: Colors.white,
              fontSize: 20,
              fontWeight: FontWeight.bold,
              letterSpacing: 0.5,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  Widget _buildPendingTable(List<Map<String, dynamic>> pending, bool isRtl) {
    if (pending.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.check_circle_outline, size: 64, color: Colors.grey.shade400),
            const SizedBox(height: 16),
            Text(
              isRtl ? 'لا توجد طلبات معلقة' : 'No pending requests',
              style: TextStyle(fontSize: 16, color: Colors.grey.shade600, fontWeight: FontWeight.w500),
            ),
          ],
        ),
      );
    }
    return _buildResponsiveList(pending, isRtl, true);
  }

  Widget _buildHistoryTable(List<Map<String, dynamic>> completed, bool isRtl) {
    if (completed.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.history, size: 64, color: Colors.grey.shade400),
            const SizedBox(height: 16),
            Text(
              isRtl ? 'لا يوجد سجل للمدفوعات' : 'No payout history',
              style: TextStyle(fontSize: 16, color: Colors.grey.shade600, fontWeight: FontWeight.w500),
            ),
          ],
        ),
      );
    }
    return _buildResponsiveList(completed, isRtl, false);
  }

  Widget _buildResponsiveList(List<Map<String, dynamic>> items, bool isRtl, bool isPending) {
    return LayoutBuilder(
      builder: (context, constraints) {
        if (constraints.maxWidth > 800) {
          return SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: Card(
              elevation: 2,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              color: _surfaceColor,
              clipBehavior: Clip.antiAlias,
              child: SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: DataTable(
                  headingRowColor: MaterialStateProperty.all(Colors.grey.shade50),
                  dataRowMaxHeight: 70,
                  columns: [
                    DataColumn(label: Text(isRtl ? 'التاجر' : 'Merchant', style: const TextStyle(fontWeight: FontWeight.bold))),
                    DataColumn(label: Text(isRtl ? 'التواصل' : 'Contact', style: const TextStyle(fontWeight: FontWeight.bold))),
                    DataColumn(label: Text(isRtl ? 'معلومات البنك' : 'Bank Info', style: const TextStyle(fontWeight: FontWeight.bold))),
                    DataColumn(label: Text(isRtl ? 'المبلغ' : 'Amount', style: const TextStyle(fontWeight: FontWeight.bold))),
                    if (isPending) DataColumn(label: Text(isRtl ? 'الإجراء' : 'Action', style: const TextStyle(fontWeight: FontWeight.bold)))
                    else DataColumn(label: Text(isRtl ? 'الحالة' : 'Status', style: const TextStyle(fontWeight: FontWeight.bold))),
                  ],
                  rows: items.map((req) {
                    final amount = (num.tryParse(req['amount']?.toString() ?? '0') ?? 0).toDouble();
                    String merchantName = 'ID: ${req['merchant_id']}';
                    String phone = '-';
                    if (req['users'] != null) {
                      final u = req['users'];
                      merchantName = u['store_name'] ?? u['name'] ?? merchantName;
                      phone = u['phone'] ?? phone;
                    }
                    final bankInfo = '${req['bank_name'] ?? 'N/A'}\n${req['account_number'] ?? 'N/A'}';
                    
                    return DataRow(
                      cells: [
                        DataCell(Text(merchantName, style: const TextStyle(fontWeight: FontWeight.w600))),
                        DataCell(Text(phone, style: TextStyle(color: Colors.grey.shade700))),
                        DataCell(Text(bankInfo, style: const TextStyle(height: 1.3))),
                        DataCell(Text(
                          _currencyFormat.format(amount),
                          style: const TextStyle(fontWeight: FontWeight.bold),
                        )),
                        if (isPending)
                          DataCell(Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              ElevatedButton(
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFF10B981),
                                  foregroundColor: Colors.white,
                                  elevation: 0,
                                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                                ),
                                onPressed: () => _confirmAction(context, req['id'], true, isRtl),
                                child: Text(isRtl ? 'مكتمل' : 'Approve'),
                              ),
                              const SizedBox(width: 8),
                              OutlinedButton(
                                style: OutlinedButton.styleFrom(
                                  foregroundColor: const Color(0xFFEF4444),
                                  side: const BorderSide(color: Color(0xFFEF4444)),
                                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                                ),
                                onPressed: () => _confirmAction(context, req['id'], false, isRtl),
                                child: Text(isRtl ? 'رفض' : 'Reject'),
                              ),
                            ],
                          ))
                        else
                          DataCell(_buildStatusBadge(req['status'], isRtl)),
                      ],
                    );
                  }).toList(),
                ),
              ),
            ),
          );
        } else {
          // Mobile View
          return ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: items.length,
            itemBuilder: (context, index) {
              final req = items[index];
              return _buildMobileCard(req, isRtl, isPending);
            },
          );
        }
      },
    );
  }

  Widget _buildStatusBadge(String status, bool isRtl) {
    Color bgColor;
    Color textColor;
    String text;

    if (status == 'approved') {
      bgColor = const Color(0xFFD1FAE5);
      textColor = const Color(0xFF065F46);
      text = isRtl ? 'تم التحويل' : 'Approved';
    } else if (status == 'rejected') {
      bgColor = const Color(0xFFFEE2E2);
      textColor = const Color(0xFF991B1B);
      text = isRtl ? 'مرفوض' : 'Rejected';
    } else {
      bgColor = const Color(0xFFFEF3C7);
      textColor = const Color(0xFF92400E);
      text = isRtl ? 'معلق' : 'Pending';
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        text,
        style: TextStyle(
          color: textColor,
          fontSize: 12,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }

  Widget _buildMobileCard(Map<String, dynamic> req, bool isRtl, bool isPending) {
    final amount = (num.tryParse(req['amount']?.toString() ?? '0') ?? 0).toDouble();
    String merchantName = 'ID: ${req['merchant_id']}';
    String phone = '';
    if (req['users'] != null) {
      final u = req['users'];
      merchantName = u['store_name'] ?? u['name'] ?? merchantName;
      phone = u['phone'] ?? '';
    }

    return Card(
      elevation: 2,
      shadowColor: Colors.black12,
      margin: const EdgeInsets.only(bottom: 16),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      color: _surfaceColor,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    merchantName,
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 16,
                      color: _primaryDark,
                    ),
                  ),
                ),
                if (!isPending) _buildStatusBadge(req['status'], isRtl),
              ],
            ),
            if (phone.isNotEmpty) ...[
              const SizedBox(height: 4),
              Row(
                children: [
                  Icon(Icons.phone, size: 14, color: Colors.grey.shade500),
                  const SizedBox(width: 4),
                  Text(phone, style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                ],
              ),
            ],
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.grey.shade50,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.grey.shade200),
              ),
              child: Column(
                children: [
                  _buildInfoRow(isRtl ? 'البنك' : 'Bank', req['bank_name'] ?? 'N/A'),
                  const Divider(height: 16),
                  _buildInfoRow(isRtl ? 'اسم الحساب' : 'Account Name', req['account_name'] ?? 'N/A'),
                  const Divider(height: 16),
                  _buildInfoRow(isRtl ? 'رقم الحساب' : 'Account No', req['account_number'] ?? 'N/A'),
                ],
              ),
            ),
            const SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  isRtl ? 'إجمالي المبلغ' : 'Total Amount',
                  style: TextStyle(color: Colors.grey.shade600, fontWeight: FontWeight.w500),
                ),
                Text(
                  _currencyFormat.format(amount),
                  style: TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 18,
                    color: _primaryAccent,
                  ),
                ),
              ],
            ),
            if (isPending) ...[
              const SizedBox(height: 20),
              Row(
                children: [
                  Expanded(
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF10B981),
                        foregroundColor: Colors.white,
                        elevation: 0,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      onPressed: () => _confirmAction(context, req['id'], true, isRtl),
                      child: Text(
                        isRtl ? 'تأكيد التحويل' : 'Approve Transfer',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: OutlinedButton(
                      style: OutlinedButton.styleFrom(
                        foregroundColor: const Color(0xFFEF4444),
                        side: const BorderSide(color: Color(0xFFEF4444)),
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      onPressed: () => _confirmAction(context, req['id'], false, isRtl),
                      child: Text(
                        isRtl ? 'رفض' : 'Reject',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
        Text(value, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
      ],
    );
  }

  Future<void> _confirmAction(BuildContext context, int id, bool isApprove, bool isRtl) async {
    final title = isApprove ? (isRtl ? 'تأكيد التحويل' : 'Confirm Transfer') : (isRtl ? 'تأكيد الرفض' : 'Confirm Reject');
    final content = isApprove 
        ? (isRtl ? 'هل قمت بالفعل بتحويل المبلغ إلى حساب التاجر المذكور؟ سيتم خصم هذا المبلغ من رصيده.' : 'Have you actually transferred the amount to the merchant\'s bank account? This will mark it as paid.') 
        : (isRtl ? 'هل أنت متأكد من رفض طلب الدفع هذا؟' : 'Are you sure you want to reject this payout request?');

    final res = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(title, style: TextStyle(color: isApprove ? const Color(0xFF065F46) : const Color(0xFF991B1B))),
        content: Text(content),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: Text(isRtl ? 'إلغاء' : 'Cancel', style: TextStyle(color: Colors.grey.shade700)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: isApprove ? const Color(0xFF10B981) : const Color(0xFFEF4444),
              elevation: 0,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: Text(isRtl ? 'تأكيد' : 'Confirm', style: const TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );

    if (res == true) {
      if (!mounted) return;
      try {
        if (isApprove) {
          await ref.read(adminFinanceProvider.notifier).approvePayout(id);
        } else {
          await ref.read(adminFinanceProvider.notifier).rejectPayout(id);
        }
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(isRtl ? 'تم تحديث الحالة بنجاح' : 'Status updated successfully'),
            backgroundColor: const Color(0xFF10B981),
            behavior: SnackBarBehavior.floating,
          ),
        );
      } catch (e) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(isRtl ? 'حدث خطأ: $e' : 'Error: $e'),
            backgroundColor: const Color(0xFFEF4444),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }
}
