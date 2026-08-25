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
  final _formKey = GlobalKey<FormState>();

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
          final payouts = walletData['payouts'] as List<Map<String, dynamic>>;
          final totalSales = walletData['totalSales'] as double;
          final enableCommission = walletData['enableCommission'] as bool;
          final commissionAmount = walletData['commissionAmount'] as double;
          final commissionPercent = walletData['commissionPercent'] as double;
          final netProfit = walletData['netProfit'] as double;
          final withdrawableBalance = walletData['withdrawableBalance'] as double;

          double totalPending = 0;
          for (var p in payouts) {
            if (p['status'] == 'pending') totalPending += (p['amount'] as num).toDouble();
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
                      boxShadow: [BoxShadow(color: Colors.blue.withOpacity(0.3), blurRadius: 10, offset: const Offset(0, 5))],
                    ),
                    child: Column(
                      children: [
                        Text(
                          isRtl ? 'الرصيد القابل للسحب' : 'Withdrawable Balance',
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
                              '${isRtl ? 'قيد المراجعة:' : 'Pending:'} ${NumberFormat.decimalPattern().format(totalPending)}',
                              style: const TextStyle(color: Colors.white, fontSize: 12),
                            ),
                          )
                        ]
                      ],
                    ),
                  ),
                ),
              ),

              if (enableCommission)
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
                            _buildSummaryRow(isRtl ? 'إجمالي المبيعات' : 'Total Sales', totalSales, isRtl),
                            const Divider(),
                            _buildSummaryRow(isRtl ? 'عمولة المنصة ($commissionPercent%)' : 'Commission ($commissionPercent%)', commissionAmount, isRtl, isRed: true),
                            const Divider(),
                            _buildSummaryRow(isRtl ? 'صافي الأرباح' : 'Net Profit', netProfit, isRtl, isBold: true),
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
                    onPressed: () => _showWithdrawDialog(context, isRtl, withdrawableBalance),
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
                      final amount = NumberFormat.decimalPattern().format(p['amount']);
                      final date = DateFormat('yyyy-MM-dd HH:mm').format(DateTime.parse(p['created_at']).toLocal());
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
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(isRtl ? 'طلب سحب' : 'Request Payout'),
        content: Form(
          key: _formKey,
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  '${isRtl ? 'الرصيد المتاح:' : 'Available Balance:'} ${NumberFormat.decimalPattern().format(maxBalance)}',
                  style: const TextStyle(color: Colors.green, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 16),
                TextFormField(
                  controller: _amountController,
                  keyboardType: TextInputType.number,
                  decoration: InputDecoration(labelText: isRtl ? 'المبلغ (ج.س)' : 'Amount (SDG)', border: const OutlineInputBorder()),
                  validator: (v) {
                    if (v == null || v.isEmpty) return isRtl ? 'مطلوب' : 'Required';
                    final amt = double.tryParse(v) ?? 0;
                    if (amt <= 0) return isRtl ? 'مبلغ غير صالح' : 'Invalid amount';
                    if (amt > maxBalance) return isRtl ? 'تجاوزت الرصيد المتاح' : 'Exceeds available balance';
                    return null;
                  },
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _bankController,
                  decoration: InputDecoration(labelText: isRtl ? 'اسم البنك / بنكك' : 'Bank Name', border: const OutlineInputBorder()),
                  validator: (v) => v!.isEmpty ? (isRtl ? 'مطلوب' : 'Required') : null,
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _nameController,
                  decoration: InputDecoration(labelText: isRtl ? 'اسم صاحب الحساب' : 'Account Holder Name', border: const OutlineInputBorder()),
                  validator: (v) => v!.isEmpty ? (isRtl ? 'مطلوب' : 'Required') : null,
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _accountController,
                  keyboardType: TextInputType.number,
                  decoration: InputDecoration(labelText: isRtl ? 'رقم الحساب' : 'Account Number', border: const OutlineInputBorder()),
                  validator: (v) => v!.isEmpty ? (isRtl ? 'مطلوب' : 'Required') : null,
                ),
              ],
            ),
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: Text(isRtl ? 'إلغاء' : 'Cancel')),
          ElevatedButton(
            onPressed: () async {
              if (_formKey.currentState!.validate()) {
                final amt = double.tryParse(_amountController.text) ?? 0;
                
                Navigator.pop(ctx);
                showDialog(context: context, barrierDismissible: false, builder: (_) => const Center(child: CircularProgressIndicator()));
                
                try {
                  await ref.read(merchantWalletProvider.notifier).requestPayout(
                    amt,
                    _bankController.text,
                    _nameController.text,
                    _accountController.text,
                  );
                  if (context.mounted) Navigator.pop(context); // pop loading
                  if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(isRtl ? 'تم إرسال الطلب للإدارة' : 'Request sent to admin')));
                  _amountController.clear();
                  _bankController.clear();
                  _nameController.clear();
                  _accountController.clear();
                } catch (e) {
                  if (context.mounted) Navigator.pop(context);
                  if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                }
              }
            },
            child: Text(isRtl ? 'تأكيد الطلب' : 'Confirm'),
          )
        ],
      ),
    );
  }
}
