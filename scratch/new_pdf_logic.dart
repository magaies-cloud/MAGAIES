import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';

// Just a dummy to hold the new methods.
class Dummy {
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
      builder: (_) => const AlertDialog(
        content: Row(
          children: [
            CircularProgressIndicator(),
            SizedBox(width: 16),
            Text('جاري إنشاء التقرير...'),
          ],
        ),
      ),
    );

    try {
      final allOrders = stats['allOrders'] as List<Map<String, dynamic>>? ?? [];
      final doc = pw.Document();
      
      // Load fonts carefully, catch errors if offline
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

              pw.Text(isRtl ? 'ملخص المبالغ المفلترة' : 'Filtered Amounts Summary', style: pw.TextStyle(font: boldFont, fontSize: 18), textDirection: isRtl ? pw.TextDirection.rtl : pw.TextDirection.ltr),
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

      if (context.mounted) Navigator.pop(context); // Close loading

      await Printing.layoutPdf(
        onLayout: (PdfPageFormat format) async => doc.save(),
        name: 'Magaiees_Report_${DateFormat('yyyyMMdd').format(DateTime.now())}.pdf',
      );
    } catch (e) {
      if (context.mounted) Navigator.pop(context); // Close loading
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

  void _showReportDialog(BuildContext context, Map<String, dynamic> stats, bool isRtl) {
    final allOrders = stats['allOrders'] as List<Map<String, dynamic>>? ?? [];
    
    // Extract unique customers
    final Map<String, String> customers = {};
    for (var o in allOrders) {
      if (o['buyer_id'] != null && o['buyer_name'] != null) {
        customers[o['buyer_id']] = o['buyer_name'];
      }
    }

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
                  
                  if (_reportType == 'customer') ...[
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
}
