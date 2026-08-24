import 'dart:ui';
import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:magaiees_marketplace/core/enums/app_mode.dart';
import 'package:magaiees_marketplace/features/mode_switch/application/mode_provider.dart';
import 'package:magaiees_marketplace/features/merchant/orders/presentation/screens/merchant_orders_screen.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';

import 'package:magaiees_marketplace/features/merchant/inventory/presentation/screens/merchant_inventory_screen.dart';
import 'package:magaiees_marketplace/features/merchant/dashboard/presentation/widgets/merchant_tier_widget.dart';

final merchantDashboardStatsProvider = Provider.autoDispose<AsyncValue<Map<String, dynamic>>>((ref) {
  final ordersAsync = ref.watch(merchantOrdersProvider);
  final inventoryAsync = ref.watch(merchantInventoryProvider);
  
  if (ordersAsync.isLoading || inventoryAsync.isLoading) {
    return const AsyncValue.loading();
  }
  if (ordersAsync.hasError) {
    return AsyncValue.error(ordersAsync.error!, ordersAsync.stackTrace!);
  }
  if (inventoryAsync.hasError) {
    return AsyncValue.error(inventoryAsync.error!, inventoryAsync.stackTrace!);
  }

  final orders = ordersAsync.value ?? [];
  final products = inventoryAsync.value ?? [];

  double totalSales = 0;
  double totalDebts = 0;
  int pendingCount = 0;
  int deliveredCount = 0;
  Set<String> uniqueCustomers = {};

  for (var order in orders) {
    if (order['status'] != 'cancelled') {
      final total = (num.tryParse(order['total_amount']?.toString() ?? '0') ?? 0.0).toDouble();
      final paid = (num.tryParse(order['paid_amount']?.toString() ?? '0') ?? 0.0).toDouble();
      
      totalSales += total;
      if (total - paid > 0) {
        totalDebts += (total - paid);
      }

      final buyerId = order['buyer_id'];
      if (buyerId != null) uniqueCustomers.add(buyerId.toString());
    }
    if (order['status'] == 'pending') {
      pendingCount++;
    }
    if (order['status'] == 'delivered') {
      deliveredCount++;
    }
  }

  return AsyncValue.data({
    'totalSales': totalSales,
    'totalDebts': totalDebts,
    'pendingOrders': pendingCount,
    'deliveredOrders': deliveredCount,
    'activeProducts': products.length,
    'customers': uniqueCustomers.length,
    'recentOrders': orders.take(5).toList(),
    'allOrders': orders,
  });
});

class MerchantDashboardScreen extends ConsumerStatefulWidget {
  const MerchantDashboardScreen({super.key});

  @override
  ConsumerState<MerchantDashboardScreen> createState() => _MerchantDashboardScreenState();
}

class _MerchantDashboardScreenState extends ConsumerState<MerchantDashboardScreen> {
  DateTime? _startDate;
  DateTime? _endDate;
  String _selectedReportStatus = 'all';
  String _reportType = 'overview';
  String? _selectedCustomerId;

  Future<void> _generatePdfReport(BuildContext context, Map<String, dynamic> stats, bool isRtl) async {
    // Show Loading Dialog
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => AlertDialog(
        content: Row(
          children: [
            const CircularProgressIndicator(),
            const SizedBox(width: 16),
            Text(isRtl ? 'جاري إنشاء التقرير...' : 'Generating report...'),
          ],
        ),
      ),
    );

    try {
      final allOrders = stats['allOrders'] as List<Map<String, dynamic>>? ?? [];
      final doc = pw.Document();
      
      // Load fonts
      pw.Font font;
      pw.Font boldFont;
      try {
        font = await PdfGoogleFonts.cairoRegular();
        boldFont = await PdfGoogleFonts.cairoBold();
      } catch (e) {
        font = pw.Font.helvetica();
        boldFont = pw.Font.helveticaBold();
      }

      final filteredOrders = allOrders.where((o) {
        final date = DateTime.parse(o['created_at']).toLocal();
        if (_startDate != null && date.isBefore(_startDate!)) return false;
        if (_endDate != null && date.isAfter(_endDate!.add(const Duration(days: 1)))) return false;
        
        if (_reportType == 'customer' && _selectedCustomerId != null) {
          if (o['buyer_id'] != _selectedCustomerId) return false;
        }

        if (_selectedReportStatus == 'delivered') {
          if (o['status'] != 'delivered') return false;
        } else if (_selectedReportStatus == 'not_delivered') {
          if (o['status'] == 'delivered' || o['status'] == 'cancelled') return false;
        } else if (_selectedReportStatus == 'debts') {
          final total = (num.tryParse(o['total_amount']?.toString() ?? '0') ?? 0.0).toDouble();
          final paid = (num.tryParse(o['paid_amount']?.toString() ?? '0') ?? 0.0).toDouble();
          if (total - paid <= 0 || o['status'] == 'cancelled') return false;
        }
        return true;
      }).toList();

      double totalReportSales = 0;
      double totalReportPaid = 0;
      double totalReportDebt = 0;

      final tableData = <List<String>>[];
      for (var o in filteredOrders) {
        final total = (num.tryParse(o['total_amount']?.toString() ?? '0') ?? 0.0).toDouble();
        final paid = (num.tryParse(o['paid_amount']?.toString() ?? '0') ?? 0.0).toDouble();
        final remaining = total - paid;
        
        totalReportSales += total;
        totalReportPaid += paid;
        totalReportDebt += remaining > 0 ? remaining : 0;
        
        final dateStr = DateFormat('yyyy-MM-dd').format(DateTime.parse(o['created_at']).toLocal());
        final orderId = (o['id']?.toString() ?? '').substring(0, 8);
        
        tableData.add(
          isRtl 
          ? [NumberFormat('#,##0').format(remaining), NumberFormat('#,##0').format(paid), NumberFormat('#,##0').format(total), dateStr, orderId]
          : [orderId, dateStr, NumberFormat('#,##0').format(total), NumberFormat('#,##0').format(paid), NumberFormat('#,##0').format(remaining)]
        );
      }

      doc.addPage(
        pw.MultiPage(
          pageFormat: PdfPageFormat.a4,
          build: (pw.Context context) {
            return [
              pw.Header(
                level: 0,
                child: pw.Text(
                  isRtl 
                    ? (_reportType == 'overview' ? 'تقرير النظرة العامة' : 'كشف حساب عميل')
                    : (_reportType == 'overview' ? 'General Overview Report' : 'Customer Account Statement'), 
                  style: pw.TextStyle(font: boldFont, fontSize: 24, fontWeight: pw.FontWeight.bold, color: PdfColors.blue900),
                  textDirection: isRtl ? pw.TextDirection.rtl : pw.TextDirection.ltr,
                ),
              ),
              pw.Text(
                '${isRtl ? 'تاريخ التقرير:' : 'Generated:'} ${DateFormat('yyyy-MM-dd HH:mm').format(DateTime.now())}',
                style: pw.TextStyle(font: font, fontSize: 12, color: PdfColors.grey700),
                textDirection: isRtl ? pw.TextDirection.rtl : pw.TextDirection.ltr,
              ),
              pw.SizedBox(height: 20),
              
              if (_reportType == 'overview') ...[
                pw.Text(isRtl ? 'إحصائيات عامة' : 'General Statistics', style: pw.TextStyle(font: boldFont, fontSize: 18), textDirection: isRtl ? pw.TextDirection.rtl : pw.TextDirection.ltr),
                pw.SizedBox(height: 10),
                pw.Row(
                  mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
                  children: [
                    _pdfMiniBox(isRtl ? 'إجمالي الإيرادات' : 'Total Revenue', stats['totalRevenue']?.toDouble() ?? 0.0, true, font, boldFont, isRtl, PdfColors.green),
                    _pdfMiniBox(isRtl ? 'البضاعة (الأصناف)' : 'Active Products', (stats['activeProducts'] ?? 0).toDouble(), false, font, boldFont, isRtl, PdfColors.blue),
                    _pdfMiniBox(isRtl ? 'طلبات مكتملة' : 'Delivered Orders', (stats['deliveredOrders'] ?? 0).toDouble(), false, font, boldFont, isRtl, PdfColors.teal),
                    _pdfMiniBox(isRtl ? 'إجمالي العملاء' : 'Customers', (stats['customers'] ?? 0).toDouble(), false, font, boldFont, isRtl, PdfColors.purple),
                  ]
                ),
                pw.SizedBox(height: 20),
              ],

              pw.Text(isRtl ? 'ملخص المبالغ' : 'Amounts Summary', style: pw.TextStyle(font: boldFont, fontSize: 18), textDirection: isRtl ? pw.TextDirection.rtl : pw.TextDirection.ltr),
              pw.SizedBox(height: 10),
              pw.Row(
                mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
                children: [
                  _pdfSummaryBox('Total Sales', totalReportSales, font, boldFont, isRtl, PdfColors.blue800),
                  _pdfSummaryBox('Total Paid', totalReportPaid, font, boldFont, isRtl, PdfColors.green800),
                  _pdfSummaryBox('Total Debt', totalReportDebt, font, boldFont, isRtl, PdfColors.red800),
                ]
              ),
              pw.SizedBox(height: 20),
              
              pw.Text(isRtl ? 'تفاصيل الطلبات' : 'Orders Details', style: pw.TextStyle(font: boldFont, fontSize: 18), textDirection: isRtl ? pw.TextDirection.rtl : pw.TextDirection.ltr),
              pw.SizedBox(height: 10),
              pw.TableHelper.fromTextArray(
                context: context,
                border: pw.TableBorder.all(color: PdfColors.grey400, width: 0.5),
                headerStyle: pw.TextStyle(font: boldFont, color: PdfColors.white),
                headerDecoration: const pw.BoxDecoration(color: PdfColors.blueGrey800),
                cellStyle: pw.TextStyle(font: font),
                cellAlignment: pw.Alignment.center,
                headers: isRtl 
                    ? ['المتبقي', 'المدفوع', 'الإجمالي', 'التاريخ', 'رقم الطلب']
                    : ['Order ID', 'Date', 'Total', 'Paid', 'Remaining'],
                data: tableData,
              ),
            ];
          },
        ),
      );

      if (context.mounted) {
        Navigator.of(context, rootNavigator: true).pop(); // Close loading dialog
      }

      await Printing.layoutPdf(
        onLayout: (PdfPageFormat format) async => doc.save(),
        name: 'Magaiees_Report_${DateFormat('yyyyMMdd').format(DateTime.now())}.pdf',
      );
    } catch (e) {
      if (context.mounted) {
        Navigator.of(context, rootNavigator: true).pop(); // Close loading
      }
      if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error generating PDF: $e')));
    }
  }

  pw.Widget _pdfMiniBox(String title, double value, bool isCurrency, pw.Font font, pw.Font boldFont, bool isRtl, PdfColor color) {
    return pw.Container(
      width: 110,
      padding: const pw.EdgeInsets.all(8),
      decoration: pw.BoxDecoration(
        color: color.shade(50),
        border: pw.Border.all(color: color, width: 1),
        borderRadius: const pw.BorderRadius.all(pw.Radius.circular(6)),
      ),
      child: pw.Column(
        children: [
          pw.Text(title, style: pw.TextStyle(font: font, fontSize: 10, color: color), textDirection: isRtl ? pw.TextDirection.rtl : pw.TextDirection.ltr, textAlign: pw.TextAlign.center),
          pw.SizedBox(height: 4),
          pw.Text(isCurrency ? NumberFormat('#,##0').format(value) : value.toInt().toString(), style: pw.TextStyle(font: boldFont, fontSize: 14, color: color)),
        ]
      )
    );
  }

  pw.Widget _pdfSummaryBox(String titleEn, double value, pw.Font font, pw.Font boldFont, bool isRtl, PdfColor color) {
    String title = titleEn;
    if (isRtl) {
      if (titleEn == 'Total Sales') title = 'إجمالي المباع';
      if (titleEn == 'Total Paid') title = 'المدفوع';
      if (titleEn == 'Total Debt') title = 'المتبقي (الديون)';
    }
    return pw.Container(
      width: 150,
      padding: const pw.EdgeInsets.all(12),
      decoration: pw.BoxDecoration(
        color: color.shade(100),
        border: pw.Border.all(color: color, width: 1),
        borderRadius: const pw.BorderRadius.all(pw.Radius.circular(8)),
      ),
      child: pw.Column(
        children: [
          pw.Text(title, style: pw.TextStyle(font: font, fontSize: 14, color: color), textDirection: isRtl ? pw.TextDirection.rtl : pw.TextDirection.ltr),
          pw.SizedBox(height: 8),
          pw.Text(NumberFormat('#,##0').format(value), style: pw.TextStyle(font: boldFont, fontSize: 18, color: color)),
        ]
      )
    );
  }

  Future<void> _showReportDialog(BuildContext context, Map<String, dynamic> stats, bool isRtl) async {
    final allOrders = stats['allOrders'] as List<Map<String, dynamic>>? ?? [];
    
    // Show Loading
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => AlertDialog(
        content: Row(
          children: [
            const CircularProgressIndicator(),
            const SizedBox(width: 16),
            Text(isRtl ? 'جاري تحضير البيانات...' : 'Preparing data...'),
          ],
        ),
      ),
    );

    // Extract unique customers
    final Map<String, String> customers = {};
    for (var o in allOrders) {
      if (o['buyer_id'] != null) {
        customers[o['buyer_id']] = o['buyer_id']; // fallback
      }
    }

    try {
      if (customers.isNotEmpty) {
        final buyerIds = customers.keys.toList();
        final usersRes = await Supabase.instance.client
            .from('users')
            .select('id, name')
            .inFilter('id', buyerIds);
        
        for (var u in (usersRes as List)) {
          customers[u['id'].toString()] = u['name']?.toString() ?? 'Unknown';
        }
      }
    } catch (_) {}

    if (context.mounted) {
      Navigator.of(context, rootNavigator: true).pop(); // Close loading dialog
    }

    if (!context.mounted) return;
    
    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setState) {
          return AlertDialog(
            title: Text(isRtl ? 'استخراج كشف حساب / تقرير' : 'Generate Report'),
            content: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Report Type
                  DropdownButtonFormField<String>(
                    value: _reportType,
                    decoration: InputDecoration(labelText: isRtl ? 'نوع التقرير' : 'Report Type', border: const OutlineInputBorder()),
                    items: [
                      DropdownMenuItem(value: 'overview', child: Text(isRtl ? 'نظرة عامة (إحصائيات المتجر)' : 'General Overview')),
                      if (customers.isNotEmpty)
                        DropdownMenuItem(value: 'customer', child: Text(isRtl ? 'كشف حساب عميل معين' : 'Customer Account Statement')),
                    ],
                    onChanged: (val) {
                      if (val != null) setState(() {
                        _reportType = val;
                        if (val == 'overview') _selectedCustomerId = null;
                      });
                    },
                  ),
                  const SizedBox(height: 16),
                  
                  if (_reportType == 'customer' && customers.isNotEmpty) ...[
                    DropdownButtonFormField<String>(
                      value: _selectedCustomerId,
                      hint: Text(isRtl ? 'اختر العميل' : 'Select Customer'),
                      decoration: const InputDecoration(border: OutlineInputBorder()),
                      items: customers.entries.map((e) => DropdownMenuItem(value: e.key, child: Text(e.value))).toList(),
                      onChanged: (val) {
                        setState(() => _selectedCustomerId = val);
                      },
                    ),
                    const SizedBox(height: 16),
                  ],

                  ListTile(
                    title: Text(isRtl ? 'من تاريخ' : 'Start Date'),
                    subtitle: Text(_startDate == null ? (isRtl ? 'لم يحدد' : 'Not set') : DateFormat('yyyy-MM-dd').format(_startDate!)),
                    trailing: const Icon(Icons.calendar_today),
                    onTap: () async {
                      final date = await showDatePicker(
                        context: context, initialDate: _startDate ?? DateTime.now(),
                        firstDate: DateTime(2020), lastDate: DateTime.now(),
                      );
                      if (date != null) setState(() => _startDate = date);
                    },
                  ),
                  ListTile(
                    title: Text(isRtl ? 'إلى تاريخ' : 'End Date'),
                    subtitle: Text(_endDate == null ? (isRtl ? 'لم يحدد' : 'Not set') : DateFormat('yyyy-MM-dd').format(_endDate!)),
                    trailing: const Icon(Icons.calendar_today),
                    onTap: () async {
                      final date = await showDatePicker(
                        context: context, initialDate: _endDate ?? DateTime.now(),
                        firstDate: DateTime(2020), lastDate: DateTime.now(),
                      );
                      if (date != null) setState(() => _endDate = date);
                    },
                  ),
                  const SizedBox(height: 16),
                  DropdownButtonFormField<String>(
                    value: _selectedReportStatus,
                    decoration: InputDecoration(labelText: isRtl ? 'نوع الطلبات' : 'Order Status', border: const OutlineInputBorder()),
                    items: [
                      DropdownMenuItem(value: 'all', child: Text(isRtl ? 'جميع الطلبات' : 'All Orders')),
                      DropdownMenuItem(value: 'delivered', child: Text(isRtl ? 'الطلبات المستلمة (تم التسليم)' : 'Delivered Orders')),
                      DropdownMenuItem(value: 'not_delivered', child: Text(isRtl ? 'الطلبات قيد التنفيذ (غير مستلمة)' : 'Pending/Processing Orders')),
                      DropdownMenuItem(value: 'debts', child: Text(isRtl ? 'ديون مستحقة (عليها متبقي)' : 'Outstanding Debts')),
                    ],
                    onChanged: (val) {
                      if (val != null) setState(() => _selectedReportStatus = val);
                    },
                  ),
                ],
              ),
            ),
            actions: [
              TextButton(onPressed: () => Navigator.pop(context), child: Text(isRtl ? 'إلغاء' : 'Cancel')),
              FilledButton.icon(
                icon: const Icon(Icons.picture_as_pdf),
                label: Text(isRtl ? 'تصدير PDF' : 'Export PDF'),
                style: FilledButton.styleFrom(backgroundColor: Colors.red.shade700),
                onPressed: () {
                  if (_reportType == 'customer' && _selectedCustomerId == null) {
                    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(isRtl ? 'الرجاء اختيار العميل أولاً' : 'Please select a customer')));
                    return;
                  }
                  Navigator.pop(context);
                  _generatePdfReport(context, stats, isRtl);
                },
              ),
            ],
          );
        }
      )
    );
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final statsAsync = ref.watch(merchantDashboardStatsProvider);

    return Scaffold(
      appBar: AppBar(
        title: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Image.asset(
              'assets/images/logo_final.png', 
              height: 28,
              errorBuilder: (context, error, stackTrace) => const SizedBox.shrink(),
            ),
            const SizedBox(width: 8),
            Text(isRtl ? 'لوحة التحكم' : 'Dashboard'),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.swap_horiz),
            tooltip: isRtl ? 'التبديل للمشتري' : 'Switch to Buyer Mode',
            onPressed: () {
              ref.read(modeNotifierProvider.notifier).switchTo(AppMode.buyer);
              context.go('/buyer/home');
            },
          ),
        ],
      ),
      body: statsAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, s) => Center(child: Text('Error: $e')),
        data: (stats) {
          final totalSales = NumberFormat.decimalPattern().format(stats['totalSales']);
          final totalDebts = NumberFormat.decimalPattern().format(stats['totalDebts']);
          final recentOrders = stats['recentOrders'] as List<Map<String, dynamic>>;
          final allOrders = stats['allOrders'] as List<Map<String, dynamic>>;

          return Builder(
            builder: (context) {
              final user = ref.watch(authNotifierProvider).valueOrNull;
              
              return CustomScrollView(
                slivers: [
                  if (user != null)
                    SliverToBoxAdapter(
                      child: MerchantTierWidget(user: user),
                    ),
                    
                  SliverPadding(
                    padding: const EdgeInsets.all(16.0),
                    sliver: SliverToBoxAdapter(
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            isRtl ? 'نظرة عامة ماليّة' : 'Financial Overview',
                            style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
                          ),
                          IconButton(
                            icon: const Icon(Icons.picture_as_pdf, color: Colors.red),
                            tooltip: isRtl ? 'استخراج كشف حساب' : 'Statement of Account',
                            onPressed: () => _showReportDialog(context, stats, isRtl),
                          ),
                        ],
                      ),
                    ),
                  ),
              
              // Top Financial Summary
              SliverPadding(
                padding: const EdgeInsets.symmetric(horizontal: 16.0),
                sliver: SliverToBoxAdapter(
                  child: Row(
                    children: [
                      Expanded(
                        child: _buildMetricCard(
                          context,
                          title: isRtl ? 'إجمالي المبيعات' : 'Total Sales',
                          value: '$totalSales ${isRtl ? 'ج.س' : 'SDG'}',
                          icon: Icons.account_balance_wallet,
                          color: Colors.green,
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: _buildMetricCard(
                          context,
                          title: isRtl ? 'الديون والمتبقي' : 'Outstanding Debts',
                          value: '$totalDebts ${isRtl ? 'ج.س' : 'SDG'}',
                          icon: Icons.money_off,
                          color: Colors.red,
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              SliverPadding(
                padding: const EdgeInsets.only(left: 16.0, right: 16.0, top: 24.0, bottom: 8.0),
                sliver: SliverToBoxAdapter(
                  child: Text(
                    isRtl ? 'إحصائيات المتجر' : 'Store Metrics',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
                  ),
                ),
              ),

              // Store Metrics Grid
              SliverPadding(
                padding: const EdgeInsets.symmetric(horizontal: 16.0),
                sliver: SliverGrid.count(
                  crossAxisCount: 2,
                  crossAxisSpacing: 16,
                  mainAxisSpacing: 16,
                  childAspectRatio: 1.8,
                  children: [
                    _buildMetricCard(
                      context,
                      title: isRtl ? 'الطلبات المعلقة' : 'Pending Orders',
                      value: stats['pendingOrders']?.toString() ?? '0',
                      icon: Icons.pending_actions,
                      color: Colors.orange,
                    ),
                    _buildMetricCard(
                      context,
                      title: isRtl ? 'تم التسليم' : 'Delivered',
                      value: stats['deliveredOrders']?.toString() ?? '0',
                      icon: Icons.check_circle_outline,
                      color: Colors.teal,
                    ),
                    _buildMetricCard(
                      context,
                      title: isRtl ? 'قيد النشاط' : 'Active Products',
                      value: stats['activeProducts']?.toString() ?? '0',
                      icon: Icons.inventory_2,
                      color: Colors.blue,
                    ),
                    _buildMetricCard(
                      context,
                      title: isRtl ? 'العملاء' : 'Customers',
                      value: stats['customers']?.toString() ?? '0',
                      icon: Icons.people,
                      color: Colors.purple,
                    ),
                  ],
                ),
              ),

              SliverPadding(
                padding: const EdgeInsets.only(left: 16.0, right: 16.0, top: 32.0, bottom: 16.0),
                sliver: SliverToBoxAdapter(
                  child: Text(
                    isRtl ? 'أحدث الطلبات' : 'Recent Orders',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
                  ),
                ),
              ),

              SliverPadding(
                padding: const EdgeInsets.symmetric(horizontal: 16.0),
                sliver: SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (context, index) {
                      final order = recentOrders[index];
                      final idStr = order['id']?.toString() ?? '';
                      final orderId = idStr.length >= 8 ? idStr.substring(0, 8) : idStr;
                      final totalAmount = NumberFormat.decimalPattern().format(double.tryParse(order['total_amount']?.toString() ?? '0') ?? 0);
                      
                      return Card(
                        margin: const EdgeInsets.only(bottom: 8.0),
                        elevation: 0,
                        shape: RoundedRectangleBorder(
                          side: BorderSide(color: Colors.grey.shade200),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: ListTile(
                          leading: CircleAvatar(
                            backgroundColor: Colors.blue.shade50,
                            child: Icon(Icons.shopping_bag, color: Colors.blue.shade700),
                          ),
                          title: Text('${isRtl ? 'طلب' : 'Order'} #$orderId', style: const TextStyle(fontWeight: FontWeight.bold)),
                          subtitle: Text('$totalAmount ${isRtl ? 'ج.س' : 'SDG'}', style: TextStyle(color: Colors.green.shade700, fontWeight: FontWeight.w600)),
                          trailing: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                            decoration: BoxDecoration(
                              color: _getStatusColor(order['status']).withOpacity(0.1),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              _getStatusLabel(order['status'], isRtl),
                              style: TextStyle(
                                color: _getStatusColor(order['status']).shade800,
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                      );
                    },
                    childCount: recentOrders.length,
                  ),
                ),
              ),
              const SliverToBoxAdapter(child: SizedBox(height: 32)),
            ],
          );
          },
        ); // closes Builder
        }, // closes data: (stats) {
      ), // closes when
    ); // closes Scaffold
  }

  MaterialColor _getStatusColor(String? status) {
    if (status == 'pending') return Colors.orange;
    if (status == 'processing') return Colors.blue;
    if (status == 'shipped') return Colors.indigo;
    if (status == 'delivered') return Colors.green;
    return Colors.red;
  }

  String _getStatusLabel(String? status, bool isRtl) {
    if (isRtl) {
      if (status == 'pending') return 'قيد الانتظار';
      if (status == 'processing') return 'قيد المعالجة';
      if (status == 'shipped') return 'تم الشحن';
      if (status == 'delivered') return 'تم التسليم';
      return 'ملغي';
    } else {
      return status?.toUpperCase() ?? 'PENDING';
    }
  }

  Widget _buildMetricCard(
    BuildContext context, {
    required String title,
    required String value,
    required IconData icon,
    required MaterialColor color,
  }) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(16),
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 10.0, sigmaY: 10.0),
        child: Container(
          padding: const EdgeInsets.all(16.0),
          decoration: BoxDecoration(
            color: color.shade50.withValues(alpha: 0.6),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: color.shade200.withValues(alpha: 0.8), width: 1.5),
            boxShadow: [
              BoxShadow(
                color: color.withValues(alpha: 0.05),
                blurRadius: 10,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      title,
                      style: TextStyle(
                        color: color.shade800,
                        fontSize: 13,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: color.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(icon, color: color.shade700, size: 20),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              FittedBox(
                fit: BoxFit.scaleDown,
                alignment: Directionality.of(context) == TextDirection.rtl ? Alignment.centerRight : Alignment.centerLeft,
                child: Text(
                  value,
                  style: TextStyle(
                    color: color.shade900,
                    fontSize: 22,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
