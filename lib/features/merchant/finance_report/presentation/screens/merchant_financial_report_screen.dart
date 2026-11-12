import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';
import 'package:magaiees_marketplace/core/constants/app_colors.dart';
import 'package:magaiees_marketplace/core/utils/order_number_formatter.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/merchant/orders/presentation/screens/merchant_orders_screen.dart';
import 'package:magaiees_marketplace/features/merchant/dashboard/application/merchant_wallet_provider.dart';
import 'package:magaiees_marketplace/features/merchant/finance_report/application/merchant_finance_report_provider.dart';
import 'package:magaiees_marketplace/features/merchant/finance_report/domain/models/merchant_financial_statement_model.dart';

/// Screen presenting the Comprehensive Financial Position Statement
/// including Inflow (الوارد), Outflow/Deductions (المنصرف), Net Cumulative (التراكمي),
/// Platform Commission deductions, and Staff Sales Productivity Attribution.
class MerchantFinancialReportScreen extends ConsumerStatefulWidget {
  const MerchantFinancialReportScreen({super.key});

  @override
  ConsumerState<MerchantFinancialReportScreen> createState() => _MerchantFinancialReportScreenState();
}

class _MerchantFinancialReportScreenState extends ConsumerState<MerchantFinancialReportScreen> {
  final NumberFormat _currencyFmt = NumberFormat('#,##0.##', 'en_US');

  String _formatAmount(double value) => _currencyFmt.format(value);

  Future<void> _pickCustomDateRange(BuildContext context) async {
    final now = DateTime.now();
    final picked = await showDateRangePicker(
      context: context,
      firstDate: DateTime(2023),
      lastDate: DateTime(now.year + 1),
      initialDateRange: ref.read(financeCustomDateRangeProvider) ??
          DateTimeRange(
            start: DateTime(now.year, now.month, 1),
            end: now,
          ),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: AppColors.merchantAccent,
              onPrimary: Colors.white,
            ),
          ),
          child: child!,
        );
      },
    );

    if (picked != null) {
      ref.read(financeCustomDateRangeProvider.notifier).state = picked;
      ref.read(financeDateFilterProvider.notifier).state = FinanceDateFilter.custom;
    }
  }

  Future<void> _exportPdfReport({
    required MerchantFinancialStatement statement,
    required bool isRtl,
    required String periodLabel,
    required String? staffNameFilter,
  }) async {
    final user = ref.read(authNotifierProvider).valueOrNull;
    final storeName = user?.storeNameAr ?? user?.storeName ?? (isRtl ? 'متجري' : 'My Store');

    final font = await PdfGoogleFonts.cairoRegular();
    final boldFont = await PdfGoogleFonts.cairoBold();
    final pdf = pw.Document();

    pdf.addPage(
      pw.MultiPage(
        pageFormat: PdfPageFormat.a4,
        margin: const pw.EdgeInsets.all(24),
        theme: pw.ThemeData.withFont(base: font, bold: boldFont),
        textDirection: isRtl ? pw.TextDirection.rtl : pw.TextDirection.ltr,
        build: (pw.Context context) {
          return [
            // Header
            pw.Row(
              mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
              children: [
                pw.Column(
                  crossAxisAlignment: pw.CrossAxisAlignment.start,
                  children: [
                    pw.Text(
                      isRtl ? 'كشف حساب الموقف المالي الشامل' : 'Comprehensive Financial Statement',
                      style: pw.TextStyle(font: boldFont, fontSize: 20, color: PdfColors.blue900),
                    ),
                    pw.SizedBox(height: 4),
                    pw.Text(
                      '${isRtl ? 'المتجر:' : 'Store:'} $storeName',
                      style: pw.TextStyle(font: boldFont, fontSize: 13, color: PdfColors.grey800),
                    ),
                    pw.Text(
                      '${isRtl ? 'الفترة الزمنية:' : 'Period:'} $periodLabel',
                      style: pw.TextStyle(font: font, fontSize: 11, color: PdfColors.grey700),
                    ),
                    if (staffNameFilter != null)
                      pw.Text(
                        '${isRtl ? 'تصفية الموظف:' : 'Staff Filter:'} $staffNameFilter',
                        style: pw.TextStyle(font: boldFont, fontSize: 11, color: PdfColors.teal800),
                      ),
                  ],
                ),
                pw.Column(
                  crossAxisAlignment: pw.CrossAxisAlignment.end,
                  children: [
                    pw.Text(
                      'MAGAIEES MARKETPLACE',
                      style: pw.TextStyle(font: boldFont, fontSize: 14, color: PdfColors.blue900),
                    ),
                    pw.SizedBox(height: 4),
                    pw.Text(
                      '${isRtl ? 'تاريخ الاستخراج:' : 'Date:'} ${DateFormat('yyyy-MM-dd HH:mm').format(DateTime.now())}',
                      style: pw.TextStyle(font: font, fontSize: 10, color: PdfColors.grey600),
                    ),
                  ],
                ),
              ],
            ),
            pw.Divider(height: 20, thickness: 1.5, color: PdfColors.blue900),
            pw.SizedBox(height: 10),

            // Financial Summary Boxes (الوارد، المنصرف، والتراكمي)
            pw.Row(
              mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
              children: [
                _buildPdfSummaryCard(
                  title: isRtl ? '🟢 إجمالي الوارد (الإيرادات)' : '🟢 Total Inflow',
                  amount: '${_formatAmount(statement.totalInflow)} ج.س',
                  subtext: isRtl
                      ? 'بضاعة: ${_formatAmount(statement.goodsTotal)} | ترحيل: ${_formatAmount(statement.transportTotal)}'
                      : 'Goods: ${_formatAmount(statement.goodsTotal)} | Transport: ${_formatAmount(statement.transportTotal)}',
                  color: PdfColors.green700,
                  boldFont: boldFont,
                  font: font,
                ),
                pw.SizedBox(width: 10),
                _buildPdfSummaryCard(
                  title: isRtl ? '🔴 إجمالي المنصرف والاستقطاعات' : '🔴 Total Outflow',
                  amount: '${_formatAmount(statement.totalOutflow)} ج.س',
                  subtext: statement.hasCommissionEnabled
                      ? (isRtl
                          ? 'عمولة مقاييس (${statement.commissionRate}%): ${_formatAmount(statement.platformCommissionTotal)}'
                          : 'Commission (${statement.commissionRate}%): ${_formatAmount(statement.platformCommissionTotal)}')
                      : (isRtl ? 'سحوبات أرباح: ${_formatAmount(statement.payoutsTotal)}' : 'Payouts: ${_formatAmount(statement.payoutsTotal)}'),
                  color: PdfColors.red700,
                  boldFont: boldFont,
                  font: font,
                ),
                pw.SizedBox(width: 10),
                _buildPdfSummaryCard(
                  title: isRtl ? '🔵 الصافي التراكمي' : '🔵 Net Cumulative',
                  amount: '${_formatAmount(statement.netCumulative)} ج.س',
                  subtext: isRtl
                      ? 'المحصل: ${_formatAmount(statement.collectedPaid)} | معلق: ${_formatAmount(statement.unpaidDebts)}'
                      : 'Collected: ${_formatAmount(statement.collectedPaid)} | Debts: ${_formatAmount(statement.unpaidDebts)}',
                  color: PdfColors.blue800,
                  boldFont: boldFont,
                  font: font,
                ),
              ],
            ),
            pw.SizedBox(height: 20),

            // Staff Breakdown Table
            if (statement.staffBreakdown.isNotEmpty) ...[
              pw.Text(
                isRtl ? '📊 جدول إنتاجية وتوزيع مبيعات الموظفين' : '📊 Staff Sales & Productivity Breakdown',
                style: pw.TextStyle(font: boldFont, fontSize: 13, color: PdfColors.blue900),
              ),
              pw.SizedBox(height: 8),
              pw.TableHelper.fromTextArray(
                context: context,
                border: pw.TableBorder.all(color: PdfColors.grey300, width: 0.5),
                headerStyle: pw.TextStyle(font: boldFont, fontSize: 9.5, color: PdfColors.white),
                headerDecoration: const pw.BoxDecoration(color: PdfColors.blue800),
                cellStyle: pw.TextStyle(font: font, fontSize: 8.5),
                cellAlignment: pw.Alignment.center,
                headers: [
                  isRtl ? 'الموظف / المسؤول' : 'Staff Member',
                  isRtl ? 'المسمى الوظيفي' : 'Role',
                  isRtl ? 'عدد الطلبات' : 'Orders',
                  isRtl ? 'إجمالي المبيعات' : 'Total Sales',
                  if (statement.hasCommissionEnabled) isRtl ? 'عمولة المنصة' : 'Commission',
                  isRtl ? 'الصافي للمتجر' : 'Net Sales',
                  isRtl ? 'نسبة المساهمة' : 'Share %',
                ],
                data: statement.staffBreakdown.map((s) {
                  return [
                    s.staffName,
                    s.jobTitle,
                    s.ordersCount.toString(),
                    '${_formatAmount(s.salesTotal)} ج.س',
                    if (statement.hasCommissionEnabled) '${_formatAmount(s.platformCommission)} ج.س',
                    '${_formatAmount(s.netSales)} ج.س',
                    '${s.contributionPercent.toStringAsFixed(1)}%',
                  ];
                }).toList(),
              ),
              pw.SizedBox(height: 20),
            ],

            // Orders Table
            pw.Text(
              isRtl ? '🧾 كشف الفواتير والعمليات التفصيلية' : '🧾 Detailed Orders Ledger',
              style: pw.TextStyle(font: boldFont, fontSize: 13, color: PdfColors.blue900),
            ),
            pw.SizedBox(height: 8),
            pw.TableHelper.fromTextArray(
              context: context,
              border: pw.TableBorder.all(color: PdfColors.grey300, width: 0.5),
              headerStyle: pw.TextStyle(font: boldFont, fontSize: 9, color: PdfColors.white),
              headerDecoration: const pw.BoxDecoration(color: PdfColors.grey800),
              cellStyle: pw.TextStyle(font: font, fontSize: 8),
              cellAlignment: pw.Alignment.center,
              headers: [
                isRtl ? 'رقم الطلب' : 'Order #',
                isRtl ? 'التاريخ' : 'Date',
                isRtl ? 'المسؤول' : 'Staff',
                isRtl ? 'الوارد (الإجمالي)' : 'Total Inflow',
                isRtl ? 'الترحيل' : 'Transport',
                if (statement.hasCommissionEnabled) isRtl ? 'خصم مقاييس' : 'Commission',
                isRtl ? 'الصافي' : 'Net',
                isRtl ? 'الحالة' : 'Status',
              ],
              data: statement.orders.map((o) {
                final total = (num.tryParse(o['total_amount']?.toString() ?? '0') ?? 0.0).toDouble();
                final transport = (num.tryParse(o['transport_fee']?.toString() ?? '0') ?? 0.0).toDouble();
                final comm = statement.hasCommissionEnabled ? (total * statement.commissionRate / 100) : 0.0;
                final net = total - comm;

                String dateStr = '-';
                if (o['created_at'] != null) {
                  final dt = DateTime.tryParse(o['created_at'].toString())?.toLocal();
                  if (dt != null) dateStr = DateFormat('MM-dd HH:mm').format(dt);
                }

                final notes = (o['notes'] ?? '').toString();
                final match = RegExp(r'\[staff:\s*([^\|]+)\s*\|\s*([^\]]+)\]').firstMatch(notes);
                final staffName = match?.group(2)?.trim() ?? (isRtl ? 'المالك' : 'Owner');

                return [
                  OrderNumberFormatter.formatFromMap(o),
                  dateStr,
                  staffName,
                  '${_formatAmount(total)} ج.س',
                  transport > 0 ? '${_formatAmount(transport)} ج.س' : '-',
                  if (statement.hasCommissionEnabled) '-${_formatAmount(comm)} ج.س',
                  '${_formatAmount(net)} ج.س',
                  o['status']?.toString() ?? '',
                ];
              }).toList(),
            ),
          ];
        },
      ),
    );

    await Printing.layoutPdf(
      onLayout: (PdfPageFormat format) async => pdf.save(),
      name: 'Magaiees_Financial_Statement_${DateFormat('yyyyMMdd').format(DateTime.now())}.pdf',
    );
  }

  pw.Widget _buildPdfSummaryCard({
    required String title,
    required String amount,
    required String subtext,
    required PdfColor color,
    required pw.Font boldFont,
    required pw.Font font,
  }) {
    return pw.Expanded(
      child: pw.Container(
        padding: const pw.EdgeInsets.all(10),
        decoration: pw.BoxDecoration(
          border: pw.Border.all(color: color, width: 1.5),
          borderRadius: const pw.BorderRadius.all(pw.Radius.circular(8)),
        ),
        child: pw.Column(
          crossAxisAlignment: pw.CrossAxisAlignment.start,
          children: [
            pw.Text(title, style: pw.TextStyle(font: boldFont, fontSize: 10, color: color)),
            pw.SizedBox(height: 4),
            pw.Text(amount, style: pw.TextStyle(font: boldFont, fontSize: 13, color: color)),
            pw.SizedBox(height: 2),
            pw.Text(subtext, style: pw.TextStyle(font: font, fontSize: 7.5, color: PdfColors.grey700)),
          ],
        ),
      ),
    );
  }

  String _getDateFilterLabel(FinanceDateFilter filter, DateTimeRange? customRange, bool isRtl) {
    switch (filter) {
      case FinanceDateFilter.today:
        return isRtl ? 'اليوم' : 'Today';
      case FinanceDateFilter.thisWeek:
        return isRtl ? 'هذا الأسبوع' : 'This Week';
      case FinanceDateFilter.thisMonth:
        return isRtl ? 'هذا الشهر' : 'This Month';
      case FinanceDateFilter.last3Months:
        return isRtl ? 'آخر 3 أشهر' : 'Last 3 Months';
      case FinanceDateFilter.allTime:
        return isRtl ? 'كل الفترات' : 'All Time';
      case FinanceDateFilter.custom:
        if (customRange != null) {
          final fmt = DateFormat('yyyy-MM-dd');
          return '${fmt.format(customRange.start)} → ${fmt.format(customRange.end)}';
        }
        return isRtl ? 'فترة مخصصة' : 'Custom Range';
    }
  }

  Widget _buildFilterChips(bool isRtl) {
    final currentFilter = ref.watch(financeDateFilterProvider);
    final customRange = ref.watch(financeCustomDateRangeProvider);

    final filters = [
      FinanceDateFilter.thisMonth,
      FinanceDateFilter.thisWeek,
      FinanceDateFilter.today,
      FinanceDateFilter.last3Months,
      FinanceDateFilter.allTime,
      FinanceDateFilter.custom,
    ];

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Row(
        children: filters.map((f) {
          final isSelected = currentFilter == f;
          final label = _getDateFilterLabel(f, customRange, isRtl);

          return Padding(
            padding: const EdgeInsets.only(right: 8),
            child: FilterChip(
              selected: isSelected,
              label: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (f == FinanceDateFilter.custom) ...[
                    const Icon(Icons.date_range, size: 14),
                    const SizedBox(width: 4),
                  ],
                  Text(label),
                ],
              ),
              selectedColor: AppColors.merchantAccent.withValues(alpha: 0.18),
              checkmarkColor: AppColors.merchantAccent,
              labelStyle: TextStyle(
                color: isSelected ? AppColors.merchantAccent : Colors.black87,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                fontSize: 12.5,
              ),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
                side: BorderSide(
                  color: isSelected ? AppColors.merchantAccent : Colors.grey.shade300,
                ),
              ),
              onSelected: (val) {
                if (f == FinanceDateFilter.custom) {
                  _pickCustomDateRange(context);
                } else {
                  ref.read(financeDateFilterProvider.notifier).state = f;
                }
              },
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildStaffFilterBar(bool isRtl) {
    final staffOptionsAsync = ref.watch(storeStaffOptionsProvider);
    final selectedStaffId = ref.watch(financeStaffFilterProvider);

    return staffOptionsAsync.when(
      data: (options) {
        return Container(
          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.grey.shade300),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.03),
                blurRadius: 4,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Row(
            children: [
              const Icon(Icons.groups_rounded, color: AppColors.merchantAccent, size: 20),
              const SizedBox(width: 10),
              Text(
                isRtl ? 'عرض مبيعات:' : 'Staff Filter:',
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<String?>(
                    value: options.any((o) => o.id == selectedStaffId) ? selectedStaffId : null,
                    isExpanded: true,
                    icon: const Icon(Icons.arrow_drop_down, color: AppColors.merchantAccent),
                    items: [
                      DropdownMenuItem<String?>(
                        value: null,
                        child: Text(
                          isRtl ? '🌐 جميع الموظفين (الموقف المالي الكلي)' : '🌐 All Staff (Total Store)',
                          style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.merchantAccent),
                        ),
                      ),
                      ...options.map((opt) {
                        return DropdownMenuItem<String?>(
                          value: opt.id,
                          child: Text('${opt.name} (${opt.jobTitle})'),
                        );
                      }),
                    ],
                    onChanged: (val) {
                      ref.read(financeStaffFilterProvider.notifier).state = val;
                    },
                  ),
                ),
              ),
              if (selectedStaffId != null)
                IconButton(
                  tooltip: isRtl ? 'إلغاء التصفية' : 'Clear filter',
                  icon: const Icon(Icons.clear, size: 18, color: Colors.grey),
                  onPressed: () {
                    ref.read(financeStaffFilterProvider.notifier).state = null;
                  },
                ),
            ],
          ),
        );
      },
      loading: () => const SizedBox.shrink(),
      error: (_, __) => const SizedBox.shrink(),
    );
  }

  Widget _buildTopPillarsCard(MerchantFinancialStatement statement, bool isRtl) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Column(
        children: [
          // 1. Inflow Card
          Container(
            padding: const EdgeInsets.all(16),
            margin: const EdgeInsets.only(bottom: 12),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF065F46), Color(0xFF047857)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF047857).withValues(alpha: 0.3),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
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
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.arrow_downward_rounded, color: Colors.white, size: 20),
                        ),
                        const SizedBox(width: 10),
                        Text(
                          isRtl ? '🟢 إجمالي الوارد (الإيرادات)' : '🟢 Total Inflow',
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16),
                        ),
                      ],
                    ),
                    Text(
                      '${_formatAmount(statement.totalInflow)} ${isRtl ? 'ج.س' : 'SDG'}',
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 20),
                    ),
                  ],
                ),
                const Divider(color: Colors.white24, height: 20),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                    _buildSubMetric(
                      isRtl ? 'قيمة البضاعة' : 'Goods Value',
                      '${_formatAmount(statement.goodsTotal)} ${isRtl ? 'ج.س' : 'SDG'}',
                      Colors.white70,
                    ),
                    _buildSubMetric(
                      isRtl ? 'رسوم الترحيل' : 'Transport',
                      '${_formatAmount(statement.transportTotal)} ${isRtl ? 'ج.س' : 'SDG'}',
                      Colors.white70,
                    ),
                    if (statement.offloadingTotal > 0)
                      _buildSubMetric(
                        isRtl ? 'رسوم العتالة' : 'Offloading',
                        '${_formatAmount(statement.offloadingTotal)} ${isRtl ? 'ج.س' : 'SDG'}',
                        Colors.white70,
                      ),
                  ],
                ),
              ],
            ),
          ),

          // 2. Outflow Card
          Container(
            padding: const EdgeInsets.all(16),
            margin: const EdgeInsets.only(bottom: 12),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF991B1B), Color(0xFFB91C1C)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFFB91C1C).withValues(alpha: 0.3),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
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
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.arrow_upward_rounded, color: Colors.white, size: 20),
                        ),
                        const SizedBox(width: 10),
                        Text(
                          isRtl ? '🔴 المنصرف والاستقطاعات' : '🔴 Total Outflow',
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16),
                        ),
                      ],
                    ),
                    Text(
                      '${_formatAmount(statement.totalOutflow)} ${isRtl ? 'ج.س' : 'SDG'}',
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 20),
                    ),
                  ],
                ),
                const Divider(color: Colors.white24, height: 20),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                    if (statement.hasCommissionEnabled)
                      _buildSubMetric(
                        isRtl ? 'عمولة مقاييس (${statement.commissionRate}%)' : 'Commission (${statement.commissionRate}%)',
                        '${_formatAmount(statement.platformCommissionTotal)} ${isRtl ? 'ج.س' : 'SDG'}',
                        Colors.white70,
                      )
                    else
                      _buildSubMetric(
                        isRtl ? 'عمولة المنصة' : 'Commission',
                        isRtl ? 'معفاة (0%)' : 'Exempt (0%)',
                        Colors.white70,
                      ),
                    _buildSubMetric(
                      isRtl ? 'السحوبات المنفذة' : 'Payouts',
                      '${_formatAmount(statement.payoutsTotal)} ${isRtl ? 'ج.س' : 'SDG'}',
                      Colors.white70,
                    ),
                  ],
                ),
              ],
            ),
          ),

          // 3. Net Balance Card
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF1E3A8A), Color(0xFF2563EB)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF2563EB).withValues(alpha: 0.3),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
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
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.account_balance_wallet_rounded, color: Colors.white, size: 20),
                        ),
                        const SizedBox(width: 10),
                        Text(
                          isRtl ? '🔵 الصافي التراكمي' : '🔵 Net Cumulative',
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16),
                        ),
                      ],
                    ),
                    Text(
                      '${_formatAmount(statement.netCumulative)} ${isRtl ? 'ج.س' : 'SDG'}',
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 22),
                    ),
                  ],
                ),
                const Divider(color: Colors.white24, height: 20),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                    _buildSubMetric(
                      isRtl ? 'المحصل نقداً / بنكياً' : 'Collected Cash',
                      '${_formatAmount(statement.collectedPaid)} ${isRtl ? 'ج.س' : 'SDG'}',
                      Colors.white70,
                    ),
                    _buildSubMetric(
                      isRtl ? 'ديون معلقة على المشترين' : 'Pending Debts',
                      '${_formatAmount(statement.unpaidDebts)} ${isRtl ? 'ج.س' : 'SDG'}',
                      statement.unpaidDebts > 0 ? Colors.amberAccent : Colors.white70,
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSubMetric(String label, String value, Color color) {
    return Column(
      children: [
        Text(value, style: TextStyle(color: color, fontWeight: FontWeight.bold, fontSize: 13)),
        const SizedBox(height: 2),
        Text(label, style: const TextStyle(color: Colors.white60, fontSize: 10.5)),
      ],
    );
  }

  Widget _buildStaffBreakdownSection(MerchantFinancialStatement statement, bool isRtl) {
    if (statement.staffBreakdown.isEmpty) return const SizedBox.shrink();

    final selectedStaffId = ref.watch(financeStaffFilterProvider);

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 6,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: AppColors.merchantAccent.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.leaderboard_rounded, color: AppColors.merchantAccent, size: 20),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      isRtl ? 'جدول إنتاجية وتوزيع مبيعات الموظفين' : 'Staff Sales & Productivity',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                    ),
                    Text(
                      isRtl
                          ? 'حجم المبيعات ونسبة المساهمة لكل موظف خلال الفترة المحددة'
                          : 'Sales performance and contribution share per staff member',
                      style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const Divider(height: 24),
          ...statement.staffBreakdown.map((s) {
            final isSelected = selectedStaffId == s.staffId;

            return Container(
              margin: const EdgeInsets.only(bottom: 12),
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: isSelected ? Colors.blue.shade50 : Colors.grey.shade50,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: isSelected ? Colors.blue.shade400 : Colors.grey.shade200,
                  width: isSelected ? 1.5 : 1,
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      CircleAvatar(
                        radius: 18,
                        backgroundColor: AppColors.merchantAccent.withValues(alpha: 0.15),
                        child: Text(
                          s.staffName.isNotEmpty ? s.staffName[0].toUpperCase() : 'S',
                          style: const TextStyle(
                            color: AppColors.merchantAccent,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Text(
                                  s.staffName,
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5),
                                ),
                                const SizedBox(width: 6),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: Colors.blue.shade100,
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: Text(
                                    s.jobTitle,
                                    style: TextStyle(fontSize: 10, color: Colors.blue.shade900, fontWeight: FontWeight.w600),
                                  ),
                                ),
                              ],
                            ),
                            Text(
                              '${s.ordersCount} ${isRtl ? 'طلب مكتمل' : 'orders completed'}',
                              style: TextStyle(fontSize: 11, color: Colors.grey.shade700),
                            ),
                          ],
                        ),
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text(
                            '${_formatAmount(s.salesTotal)} ${isRtl ? 'ج.س' : 'SDG'}',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF047857)),
                          ),
                          Text(
                            '${isRtl ? 'المساهمة:' : 'Share:'} ${s.contributionPercent.toStringAsFixed(1)}%',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Colors.blue),
                          ),
                        ],
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),

                  // Progress bar
                  ClipRRect(
                    borderRadius: BorderRadius.circular(4),
                    child: LinearProgressIndicator(
                      value: (s.contributionPercent / 100).clamp(0.0, 1.0),
                      minHeight: 6,
                      backgroundColor: Colors.grey.shade200,
                      valueColor: AlwaysStoppedAnimation<Color>(
                        s.staffId == 'owner' ? Colors.blue.shade700 : AppColors.merchantAccent,
                      ),
                    ),
                  ),

                  // Action: Filter by this staff
                  const SizedBox(height: 8),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      InkWell(
                        onTap: () {
                          if (isSelected) {
                            ref.read(financeStaffFilterProvider.notifier).state = null;
                          } else {
                            ref.read(financeStaffFilterProvider.notifier).state = s.staffId;
                          }
                        },
                        child: Row(
                          children: [
                            Icon(
                              isSelected ? Icons.check_circle : Icons.filter_alt_outlined,
                              size: 14,
                              color: isSelected ? Colors.blue.shade800 : AppColors.merchantAccent,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              isSelected
                                  ? (isRtl ? 'معروض حالياً (إلغاء التصفية)' : 'Viewing (Clear)')
                                  : (isRtl ? 'عرض فواتير هذا الموظف فقط' : 'Filter by this staff'),
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                color: isSelected ? Colors.blue.shade800 : AppColors.merchantAccent,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            );
          }),
        ],
      ),
    );
  }

  Widget _buildOrdersLedger(MerchantFinancialStatement statement, bool isRtl) {
    if (statement.orders.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(32.0),
          child: Column(
            children: [
              Icon(Icons.receipt_long_outlined, size: 48, color: Colors.grey.shade400),
              const SizedBox(height: 12),
              Text(
                isRtl ? 'لا توجد طلبات أو عمليات في هذه الفترة' : 'No orders found for this period',
                style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
              ),
            ],
          ),
        ),
      );
    }

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 6,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                isRtl ? '🧾 كشف الفواتير والعمليات التفصيلية' : '🧾 Orders Ledger',
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  '${statement.orders.length} ${isRtl ? 'فاتورة' : 'orders'}',
                  style: TextStyle(fontSize: 11, color: Colors.grey.shade800, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
          const Divider(height: 20),

          ...statement.orders.map((o) {
            final total = (num.tryParse(o['total_amount']?.toString() ?? '0') ?? 0.0).toDouble();
            final transport = (num.tryParse(o['transport_fee']?.toString() ?? '0') ?? 0.0).toDouble();
            final offloading = (num.tryParse(o['offloading_fee']?.toString() ?? '0') ?? 0.0).toDouble();
            final comm = statement.hasCommissionEnabled ? (total * statement.commissionRate / 100) : 0.0;
            final net = total - comm;

            String dateStr = '-';
            if (o['created_at'] != null) {
              final dt = DateTime.tryParse(o['created_at'].toString())?.toLocal();
              if (dt != null) dateStr = DateFormat('yyyy-MM-dd HH:mm').format(dt);
            }

            final notes = (o['notes'] ?? '').toString();
            final match = RegExp(r'\[staff:\s*([^\|]+)\s*\|\s*([^\]]+)\]').firstMatch(notes);
            final staffName = match?.group(2)?.trim() ?? (isRtl ? 'المالك (مبيعات مباشرة)' : 'Owner (Direct)');

            return Container(
              margin: const EdgeInsets.only(bottom: 12),
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.grey.shade50,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.grey.shade200),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        OrderNumberFormatter.formatFromMap(o),
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF1E3A8A)),
                      ),
                      Text(
                        dateStr,
                        style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),

                  // Staff attribution chip
                  Row(
                    children: [
                      const Icon(Icons.person_outline, size: 14, color: AppColors.merchantAccent),
                      const SizedBox(width: 4),
                      Text(
                        '${isRtl ? 'المسؤول:' : 'Staff:'} $staffName',
                        style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w600, color: Colors.black87),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),

                  // Numbers breakdown
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: Colors.grey.shade200),
                    ),
                    child: Column(
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(isRtl ? 'إجمالي الطلب (الوارد):' : 'Gross Total (Inflow):', style: const TextStyle(fontSize: 11.5)),
                            Text('${_formatAmount(total)} ج.س', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                          ],
                        ),
                        if (transport > 0) ...[
                          const SizedBox(height: 2),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(isRtl ? 'رسوم الترحيل:' : 'Transport:', style: TextStyle(fontSize: 11, color: Colors.grey.shade700)),
                              Text('+${_formatAmount(transport)} ج.س', style: TextStyle(fontSize: 11, color: Colors.grey.shade800)),
                            ],
                          ),
                        ],
                        if (offloading > 0) ...[
                          const SizedBox(height: 2),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(isRtl ? 'رسوم العتالة:' : 'Offloading:', style: TextStyle(fontSize: 11, color: Colors.grey.shade700)),
                              Text('+${_formatAmount(offloading)} ج.س', style: TextStyle(fontSize: 11, color: Colors.grey.shade800)),
                            ],
                          ),
                        ],
                        if (statement.hasCommissionEnabled) ...[
                          const SizedBox(height: 2),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                '${isRtl ? 'خصم عمولة مقاييس' : 'Commission'} (${statement.commissionRate}%):',
                                style: TextStyle(fontSize: 11, color: Colors.red.shade800),
                              ),
                              Text(
                                '-${_formatAmount(comm)} ج.س',
                                style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.red.shade800),
                              ),
                            ],
                          ),
                        ],
                        const Divider(height: 10),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              isRtl ? 'الصافي المستحق للمتجر:' : 'Net Merchant Share:',
                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF047857)),
                            ),
                            Text(
                              '${_formatAmount(net)} ج.س',
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF047857)),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            );
          }),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final statementAsync = ref.watch(merchantFinancialReportProvider);
    final dateFilter = ref.watch(financeDateFilterProvider);
    final customRange = ref.watch(financeCustomDateRangeProvider);
    final selectedStaffId = ref.watch(financeStaffFilterProvider);
    final staffOptions = ref.watch(storeStaffOptionsProvider).valueOrNull ?? [];

    String? staffNameFilter;
    if (selectedStaffId != null) {
      if (selectedStaffId == 'owner') {
        staffNameFilter = isRtl ? 'المالك (مبيعات مباشرة)' : 'Owner (Direct)';
      } else {
        staffNameFilter = staffOptions.firstWhere((s) => s.id == selectedStaffId, orElse: () => StaffFilterOption(id: '', name: selectedStaffId, jobTitle: '')).name;
      }
    }

    final periodLabel = _getDateFilterLabel(dateFilter, customRange, isRtl);

    return Scaffold(
      appBar: AppBar(
        title: Text(isRtl ? 'كشف الحساب والموقف المالي' : 'Financial Statement & Ledger'),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        elevation: 1,
        actions: [
          statementAsync.when(
            data: (statement) => IconButton(
              tooltip: isRtl ? 'تصدير وطباعة كشف حساب PDF' : 'Export & Print PDF',
              icon: const Icon(Icons.picture_as_pdf_rounded, color: Colors.red),
              onPressed: () => _exportPdfReport(
                statement: statement,
                isRtl: isRtl,
                periodLabel: periodLabel,
                staffNameFilter: staffNameFilter,
              ),
            ),
            loading: () => const SizedBox.shrink(),
            error: (_, __) => const SizedBox.shrink(),
          ),
          IconButton(
            tooltip: isRtl ? 'تحديث' : 'Refresh',
            icon: const Icon(Icons.refresh),
            onPressed: () {
              ref.invalidate(merchantOrdersProvider);
              ref.invalidate(merchantWalletProvider);
            },
          ),
        ],
      ),
      body: statementAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, st) => Center(
          child: Padding(
            padding: const EdgeInsets.all(24.0),
            child: Text('Error: $e', style: const TextStyle(color: Colors.red)),
          ),
        ),
        data: (statement) {
          return RefreshIndicator(
            onRefresh: () async {
              ref.invalidate(merchantOrdersProvider);
              ref.invalidate(merchantWalletProvider);
            },
            child: ListView(
              padding: const EdgeInsets.only(bottom: 40),
              children: [
                const SizedBox(height: 8),

                // 1. Time Filter Chips
                _buildFilterChips(isRtl),

                // 2. Staff Filter Selector Dropdown
                _buildStaffFilterBar(isRtl),

                // 3. Top 3 Financial Pillars Cards (Inflow, Outflow, Net)
                _buildTopPillarsCard(statement, isRtl),

                // 4. Staff Sales Breakdown
                _buildStaffBreakdownSection(statement, isRtl),

                // 5. Orders Ledger
                _buildOrdersLedger(statement, isRtl),
              ],
            ),
          );
        },
      ),
    );
  }
}
