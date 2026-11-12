import 'package:magaiees_marketplace/features/admin/finance/application/banks_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/features/merchant/dashboard/application/merchant_wallet_provider.dart';
import 'package:intl/intl.dart' hide TextDirection;

class MerchantWalletScreen extends ConsumerStatefulWidget {
  const MerchantWalletScreen({super.key});

  @override
  ConsumerState<MerchantWalletScreen> createState() => _MerchantWalletScreenState();
}

class _MerchantWalletScreenState extends ConsumerState<MerchantWalletScreen> {
  final _amountController = TextEditingController();
  final _bankController = TextEditingController();
  final _nameController = TextEditingController();
  final _accountController = TextEditingController();

  @override
  void dispose() {
    _amountController.dispose();
    _bankController.dispose();
    _nameController.dispose();
    _accountController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final walletAsync = ref.watch(merchantWalletProvider);

    return Scaffold(
      appBar: AppBar(
        title: Text(isRtl ? 'المحفظة والسحوبات' : 'Wallet & Payouts'),
      ),
      body: walletAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, st) => Center(child: Text('Error: $e')),
        data: (walletData) {
          final payouts = (walletData['payouts'] is List)
              ? List<Map<String, dynamic>>.from(walletData['payouts'] as List)
              : <Map<String, dynamic>>[];
          final totalSales = (walletData['totalSales'] as num?)?.toDouble() ?? 0.0;
          final allActiveSales = (walletData['allActiveSales'] as num?)?.toDouble() ?? totalSales;
          final totalDebts = (walletData['totalDebts'] as num?)?.toDouble() ?? 0.0;
          final totalWithdrawn = (walletData['totalWithdrawn'] as num?)?.toDouble() ?? 0.0;
          final enableCommission = walletData['enableCommission'] == true;
          final commissionAmount = (walletData['commissionAmount'] as num?)?.toDouble() ?? 0.0;
          final commissionPercent = (walletData['commissionPercent'] as num?)?.toDouble() ?? 0.0;
          final netProfit = (walletData['netProfit'] as num?)?.toDouble() ?? 0.0;
          final withdrawableBalance = (walletData['withdrawableBalance'] as num?)?.toDouble() ?? 0.0;

          double totalPending = 0;
          for (var p in payouts) {
            if (p['status'] == 'pending') {
              totalPending += (num.tryParse(p['amount']?.toString() ?? '0') ?? 0.0).toDouble();
            }
          }

          return CustomScrollView(
            slivers: [
              SliverPadding(
                padding: const EdgeInsets.all(16),
                sliver: SliverToBoxAdapter(
                  child: Container(
                    padding: const EdgeInsets.all(24),
                    decoration: BoxDecoration(
                      gradient: LinearGradient(colors: [Colors.blue.shade900, Colors.blue.shade600]),
                      borderRadius: BorderRadius.circular(24),
                      boxShadow: [BoxShadow(color: Colors.blue.withValues(alpha: 0.3), blurRadius: 10, offset: const Offset(0, 5))],
                    ),
                    child: Column(
                      children: [
                        Text(
                          isRtl ? 'الرصيد القابل للسحب (المحفظة)' : 'Withdrawable Wallet Balance',
                          style: const TextStyle(color: Colors.white70, fontSize: 16),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          '${NumberFormat.decimalPattern().format(withdrawableBalance)} ${isRtl ? 'ج.س' : 'SDG'}',
                          style: const TextStyle(color: Colors.white, fontSize: 32, fontWeight: FontWeight.bold),
                        ),
                        if (totalPending > 0) ...[
                          const SizedBox(height: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                            decoration: BoxDecoration(color: Colors.white24, borderRadius: BorderRadius.circular(12)),
                            child: Text(
                              '${isRtl ? 'سحب قيد المراجعة:' : 'Pending Payout:'} ${NumberFormat.decimalPattern().format(totalPending)} ${isRtl ? 'ج.س' : 'SDG'}',
                              style: const TextStyle(color: Colors.white, fontSize: 12),
                            ),
                          )
                        ]
                      ],
                    ),
                  ),
                ),
              ),

              SliverPadding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                sliver: SliverToBoxAdapter(
                  child: Card(
                    color: Colors.blue.shade50,
                    elevation: 0,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    child: Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: Column(
                        children: [
                          _buildSummaryRow(isRtl ? 'إجمالي المبيعات النشطة' : 'Total Active Sales', allActiveSales, isRtl),
                          const Divider(),
                          _buildSummaryRow(isRtl ? 'المحصّل من الطلبات المستلمة' : 'Collected (Delivered Orders)', totalSales, isRtl),
                          const Divider(),
                          _buildSummaryRow(isRtl ? 'الديون المتبقية على العملاء' : 'Remaining Customer Debts', totalDebts, isRtl, isRed: totalDebts > 0),
                          if (enableCommission) ...[
                            const Divider(),
                            _buildSummaryRow(isRtl ? 'عمولة المنصة ($commissionPercent%)' : 'Commission ($commissionPercent%)', commissionAmount, isRtl, isRed: true),
                            const Divider(),
                            _buildSummaryRow(isRtl ? 'صافي أرباح المستلم' : 'Net Delivered Profit', netProfit, isRtl),
                          ],
                          if (totalWithdrawn > 0) ...[
                            const Divider(),
                            _buildSummaryRow(isRtl ? 'المسحوب / قيد السحب' : 'Withdrawn / Pending', totalWithdrawn, isRtl, isRed: true),
                          ],
                          const Divider(),
                          _buildSummaryRow(isRtl ? 'الرصيد المتاح للسحب' : 'Available Wallet Balance', withdrawableBalance, isRtl, isBold: true),
                        ],
                      ),
                    ),
                  ),
                ),
              ),

              SliverPadding(
                padding: const EdgeInsets.all(16),
                sliver: SliverToBoxAdapter(
                  child: ElevatedButton.icon(
                    onPressed: () {
                      final bName = walletData['bankName'] as String? ?? '';
                      final bAccName = walletData['bankAccountName'] as String? ?? '';
                      final bAccNum = walletData['bankAccountNumber'] as String? ?? '';
                      if (_bankController.text.isEmpty && bName.isNotEmpty) _bankController.text = bName;
                      if (_nameController.text.isEmpty && bAccName.isNotEmpty) _nameController.text = bAccName;
                      if (_accountController.text.isEmpty && bAccNum.isNotEmpty) _accountController.text = bAccNum;
                      _showWithdrawDialog(context, isRtl, withdrawableBalance);
                    },
                    icon: const Icon(Icons.account_balance_wallet),
                    label: Text(isRtl ? 'طلب سحب رصيد' : 'Request Payout'),
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      backgroundColor: Colors.blue.shade800,
                      foregroundColor: Colors.white,
                    ),
                  ),
                ),
              ),
              SliverPadding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                sliver: SliverToBoxAdapter(
                  child: Text(
                    isRtl ? 'سجل السحوبات' : 'Payout History',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
                  ),
                ),
              ),
              if (payouts.isEmpty)
                SliverToBoxAdapter(
                  child: Center(
                    child: Padding(
                      padding: const EdgeInsets.all(32),
                      child: Text(isRtl ? 'لا توجد حركات مالية' : 'No payouts yet', style: const TextStyle(color: Colors.grey)),
                    ),
                  ),
                )
              else
                SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (context, index) {
                      final p = payouts[index];
                      final amount = NumberFormat.decimalPattern().format(num.tryParse(p['amount']?.toString() ?? '0') ?? 0);
                      final parsedDate = DateTime.tryParse(p['created_at']?.toString() ?? '')?.toLocal();
                      final date = parsedDate != null ? DateFormat('yyyy-MM-dd HH:mm').format(parsedDate) : (p['created_at']?.toString() ?? '-');
                      final status = p['status'];

                      Color sColor = Colors.orange;
                      String sText = isRtl ? 'قيد المراجعة' : 'Pending';
                      if (status == 'approved') {
                        sColor = Colors.green;
                        sText = isRtl ? 'مكتمل' : 'Approved';
                      } else if (status == 'rejected') {
                        sColor = Colors.red;
                        sText = isRtl ? 'مرفوض' : 'Rejected';
                      }

                      return Card(
                        margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                        child: ListTile(
                          leading: CircleAvatar(
                            backgroundColor: sColor.withOpacity(0.1),
                            child: Icon(Icons.monetization_on, color: sColor),
                          ),
                          title: Text('$amount ${isRtl ? 'ج.س' : 'SDG'}', style: const TextStyle(fontWeight: FontWeight.bold)),
                          subtitle: Text('${p['bank_name']} - $date'),
                          trailing: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(color: sColor.withOpacity(0.1), borderRadius: BorderRadius.circular(8)),
                            child: Text(sText, style: TextStyle(color: sColor, fontWeight: FontWeight.bold, fontSize: 12)),
                          ),
                        ),
                      );
                    },
                    childCount: payouts.length,
                  ),
                ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildSummaryRow(String title, double amount, bool isRtl, {bool isRed = false, bool isBold = false}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(title, style: TextStyle(fontWeight: isBold ? FontWeight.bold : FontWeight.normal)),
        Text(
          '${isRed ? "-" : ""}${NumberFormat.decimalPattern().format(amount)} ${isRtl ? 'ج.س' : 'SDG'}',
          style: TextStyle(
            fontWeight: isBold ? FontWeight.bold : FontWeight.normal,
            color: isRed ? Colors.red : (isBold ? Colors.green : Colors.black87),
          ),
        ),
      ],
    );
  }

  void _showWithdrawDialog(BuildContext context, bool isRtl, double maxBalance) {
    if (maxBalance <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(isRtl ? 'عفواً، لا يوجد رصيد متاح للسحب حالياً' : 'No available balance to withdraw'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    final formKey = GlobalKey<FormState>();
    final amountCtrl = TextEditingController();
    bool isSubmitting = false;

    // Read saved bank details
    final walletState = ref.read(merchantWalletProvider).valueOrNull;
    final savedBankName = walletState?['bankName']?.toString() ?? '';
    final savedAccountName = walletState?['bankAccountName']?.toString() ?? '';
    final savedAccountNumber = walletState?['bankAccountNumber']?.toString() ?? '';
    final hasVerifiedBank = savedBankName.isNotEmpty && savedAccountNumber.isNotEmpty;

    final dbBanks = ref
            .read(banksProvider)
            .valueOrNull
            ?.where((b) => b.isActive && b.nameAr.trim().isNotEmpty)
            .map((b) => b.nameAr.trim())
            .toSet()
            .toList() ??
        [];
    final sudaneseBanks = dbBanks.isNotEmpty ? dbBanks : [
      'بنك الخرطوم (بنكك)',
      'بنك أمدرمان الوطني (أوكاش)',
      'بنك فيصل الإسلامي (فوري)',
      'بنك النيلين',
      'البنك الأهلي السوداني',
    ];

    String selectedBank = hasVerifiedBank ? savedBankName : sudaneseBanks.first;
    final nameCtrl = TextEditingController(text: savedAccountName);
    final accountCtrl = TextEditingController(text: savedAccountNumber);

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (dialogCtx) => StatefulBuilder(
        builder: (stCtx, setModalState) {
          return AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            title: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(color: Colors.blue.shade50, borderRadius: BorderRadius.circular(10)),
                  child: const Icon(Icons.account_balance_wallet_rounded, color: Colors.blue, size: 24),
                ),
                const SizedBox(width: 12),
                Text(isRtl ? 'طلب سحب رصيد' : 'Request Payout', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
              ],
            ),
            content: SizedBox(
              width: 440,
              child: Form(
                key: formKey,
                child: SingleChildScrollView(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Available Balance Header Box
                      Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: Colors.green.shade50,
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: Colors.green.shade200),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(isRtl ? 'الرصيد المتاح للسحب:' : 'Available Balance:', style: TextStyle(color: Colors.green.shade900, fontWeight: FontWeight.w600, fontSize: 13)),
                            Text('${NumberFormat.decimalPattern().format(maxBalance)} ${isRtl ? 'ج.س' : 'SDG'}', style: TextStyle(color: Colors.green.shade900, fontWeight: FontWeight.bold, fontSize: 16)),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),

                      // Amount Input
                      TextFormField(
                        controller: amountCtrl,
                        keyboardType: const TextInputType.numberWithOptions(decimal: true),
                        decoration: InputDecoration(
                          labelText: isRtl ? 'المبلغ المطلوب سحبه (ج.س) *' : 'Amount to withdraw (SDG) *',
                          hintText: isRtl ? 'الحد الأقصى: ${NumberFormat.decimalPattern().format(maxBalance)}' : 'Max: ${NumberFormat.decimalPattern().format(maxBalance)}',
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                          prefixIcon: const Icon(Icons.monetization_on_outlined),
                        ),
                        validator: (v) {
                          if (v == null || v.trim().isEmpty) return isRtl ? 'الرجاء إدخال المبلغ' : 'Please enter amount';
                          final amt = double.tryParse(v.trim()) ?? 0;
                          if (amt <= 0) return isRtl ? 'يجب إدخال مبلغ أكبر من صفر' : 'Amount must be greater than zero';
                          if (amt > maxBalance) return isRtl ? 'المبلغ يتجاوز رصيدك المتاح (${NumberFormat.decimalPattern().format(maxBalance)} ج.س)' : 'Exceeds available balance';
                          return null;
                        },
                      ),
                      const SizedBox(height: 8),

                      // Quick Percentages Chips
                      Row(
                        children: [
                          _buildPresetChip('25%', maxBalance * 0.25, amountCtrl),
                          const SizedBox(width: 8),
                          _buildPresetChip('50%', maxBalance * 0.50, amountCtrl),
                          const SizedBox(width: 8),
                          _buildPresetChip('75%', maxBalance * 0.75, amountCtrl),
                          const SizedBox(width: 8),
                          _buildPresetChip(isRtl ? 'كامل الرصيد' : '100%', maxBalance, amountCtrl, isFull: true),
                        ],
                      ),
                      const SizedBox(height: 20),

                      // Bank Account Details Box
                      if (hasVerifiedBank) ...[
                        Container(
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: Colors.grey.shade50,
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: Colors.grey.shade300),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  const Icon(Icons.verified_user_rounded, color: Colors.blue, size: 18),
                                  const SizedBox(width: 8),
                                  Text(isRtl ? 'الحساب البنكي المعتمد للتحويل' : 'Registered Bank Account', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.blue)),
                                ],
                              ),
                              const Divider(height: 16),
                              _buildBankInfoRow(isRtl ? 'البنك' : 'Bank', savedBankName),
                              _buildBankInfoRow(isRtl ? 'صاحب الحساب' : 'Holder', savedAccountName),
                              _buildBankInfoRow(isRtl ? 'رقم الحساب' : 'Account #', savedAccountNumber),
                              const SizedBox(height: 4),
                              Text(
                                isRtl ? '🔒 لتعديل الحساب البنكي يرجى مراجعة إعدادات المتجر أو إدارة ساس.' : '🔒 To modify bank details, visit Store Settings or contact SaaS Support.',
                                style: TextStyle(fontSize: 10, color: Colors.grey.shade600),
                              ),
                            ],
                          ),
                        ),
                      ] else ...[
                        Text(isRtl ? 'بيانات الحساب البنكي للتحويل' : 'Bank Account Info', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                        const SizedBox(height: 10),
                        DropdownButtonFormField<String>(
                          value: sudaneseBanks.contains(selectedBank) ? selectedBank : sudaneseBanks.first,
                          decoration: InputDecoration(
                            labelText: isRtl ? 'اسم البنك *' : 'Bank Name *',
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                            prefixIcon: const Icon(Icons.account_balance),
                          ),
                          items: sudaneseBanks.map((b) => DropdownMenuItem(value: b, child: Text(b, style: const TextStyle(fontSize: 13)))).toList(),
                          onChanged: (val) => setModalState(() => selectedBank = val ?? selectedBank),
                        ),
                        const SizedBox(height: 12),
                        TextFormField(
                          controller: nameCtrl,
                          decoration: InputDecoration(
                            labelText: isRtl ? 'اسم صاحب الحساب رباعي *' : 'Account Holder Full Name *',
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                            prefixIcon: const Icon(Icons.person),
                          ),
                          validator: (v) => (v == null || v.trim().isEmpty) ? (isRtl ? 'مطلوب' : 'Required') : null,
                        ),
                        const SizedBox(height: 12),
                        TextFormField(
                          controller: accountCtrl,
                          keyboardType: TextInputType.number,
                          decoration: InputDecoration(
                            labelText: isRtl ? 'رقم الحساب أو رقم المحفظة *' : 'Account / Wallet Number *',
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                            prefixIcon: const Icon(Icons.numbers),
                          ),
                          validator: (v) => (v == null || v.trim().isEmpty) ? (isRtl ? 'مطلوب' : 'Required') : null,
                        ),
                      ],
                    ],
                  ),
                ),
              ),
            ),
            actions: [
              TextButton(
                onPressed: isSubmitting ? null : () => Navigator.pop(dialogCtx),
                child: Text(isRtl ? 'إلغاء' : 'Cancel'),
              ),
              ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.blue.shade800,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                ),
                onPressed: isSubmitting ? null : () async {
                  if (!formKey.currentState!.validate()) return;
                  
                  final amt = double.tryParse(amountCtrl.text.trim()) ?? 0;
                  final bName = hasVerifiedBank ? savedBankName : selectedBank;
                  final aName = hasVerifiedBank ? savedAccountName : nameCtrl.text.trim();
                  final aNum = hasVerifiedBank ? savedAccountNumber : accountCtrl.text.trim();

                  setModalState(() => isSubmitting = true);

                  try {
                    await ref.read(merchantWalletProvider.notifier).requestPayout(amt, bName, aName, aNum);
                    
                    if (dialogCtx.mounted) {
                      Navigator.pop(dialogCtx);
                    }
                    if (context.mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text(isRtl ? 'تم إرسال طلب السحب بنجاح إلى الإدارة للمراجعة' : 'Payout request submitted successfully'),
                          backgroundColor: Colors.green,
                        ),
                      );
                    }
                  } catch (e) {
                    if (dialogCtx.mounted) {
                      setModalState(() => isSubmitting = false);
                    }
                    if (context.mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
                      );
                    }
                  }
                },
                child: isSubmitting
                    ? Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)),
                          const SizedBox(width: 8),
                          Text(isRtl ? 'جاري الإرسال...' : 'Submitting...', style: const TextStyle(fontSize: 13, color: Colors.white)),
                        ],
                      )
                    : Text(isRtl ? 'تأكيد السحب' : 'Confirm Payout'),
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildPresetChip(String label, double amount, TextEditingController ctrl, {bool isFull = false}) {
    return Expanded(
      child: InkWell(
        onTap: () => ctrl.text = amount.toStringAsFixed(0),
        borderRadius: BorderRadius.circular(8),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 6),
          decoration: BoxDecoration(
            color: isFull ? Colors.blue.shade100 : Colors.grey.shade100,
            borderRadius: BorderRadius.circular(8),
            border: Border.all(color: isFull ? Colors.blue.shade400 : Colors.grey.shade300),
          ),
          child: Text(
            label,
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: isFull ? Colors.blue.shade900 : Colors.grey.shade800),
          ),
        ),
      ),
    );
  }

  Widget _buildBankInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(color: Colors.grey.shade600, fontSize: 12)),
          Text(value.isNotEmpty ? value : '-', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
        ],
      ),
    );
  }
}
