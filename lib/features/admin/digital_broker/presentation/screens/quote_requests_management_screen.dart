// ignore_for_file: deprecated_member_use
import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:url_launcher/url_launcher.dart';
import 'dart:convert';
import 'dart:math';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/admin/digital_broker/application/admin_digital_broker_provider.dart';
import 'package:magaiees_marketplace/features/admin/presentation/widgets/admin_error_view.dart';
import 'package:magaiees_marketplace/core/utils/web_download_helper.dart' as web_helper;

class QuoteRequestsManagementScreen extends ConsumerStatefulWidget {
  const QuoteRequestsManagementScreen({super.key});

  @override
  ConsumerState<QuoteRequestsManagementScreen> createState() =>
      _QuoteRequestsManagementScreenState();
}

class _QuoteRequestsManagementScreenState
    extends ConsumerState<QuoteRequestsManagementScreen> {
  static const String _currency = 'ج.س';

  String _statusFilter = 'all'; // all, pending, contacted, closed
  String _supplierFilter = 'all'; // all or supplier name
  String _sortBy = 'newest'; // newest, oldest, delivery, quantity, deal_desc
  final TextEditingController _searchCtrl = TextEditingController();
  bool _isTableView = false;

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  String _fmt(double v) {
    final f = NumberFormat('#,##0', 'ar');
    return '${f.format(v)} $_currency';
  }

  Future<void> _exportCsv(List<QuoteRequestModel> list) async {
    if (list.isEmpty) return;

    final rows = <List<String>>[
      [
        'رقم التذكرة',
        'تاريخ الإنشاء',
        'المشتري',
        'رقم الهاتف',
        'المواد المطلوبة',
        'الكمية',
        'موعد التسليم',
        'الحالة',
        'المورد المعلن المعتمد',
        'قيمة الصفقة التقديرية (ج.س)',
        'عمولة الوساطة (ج.س)',
      ]
    ];

    for (var r in list) {
      final dateStr = r.createdAt != null
          ? DateFormat('yyyy-MM-dd HH:mm').format(r.createdAt!)
          : '-';

      String statusAr = 'قيد الانتظار';
      if (r.status == 'contacted') {
        statusAr = 'تم التواصل والتسعير';
      } else if (r.status == 'closed') {
        statusAr = 'مكتمل ومورد';
      } else if (r.status == 'cancelled') {
        statusAr = 'ملغي';
      }

      rows.add([
        r.ticketNumber,
        dateStr,
        r.buyerName,
        r.buyerPhone,
        r.materialType.replaceAll('\n', ' ').replaceAll(',', '،'),
        r.quantity.replaceAll('\n', ' ').replaceAll(',', '،'),
        r.deliveryDate ?? '-',
        statusAr,
        r.assignedSupplier ?? 'غير محدد',
        r.dealValue > 0 ? r.dealValue.toStringAsFixed(0) : '0',
        r.commissionValue > 0 ? r.commissionValue.toStringAsFixed(0) : '0',
      ]);
    }

    final csvBody = rows.map((row) => row.map((f) => '"${f.replaceAll('"', '""')}"').join(',')).join('\r\n');
    final csvContent = '\uFEFF$csvBody';

    final bytes = utf8.encode(csvContent);
    final fileName = 'magaiees_b2b_quote_requests_${DateTime.now().millisecondsSinceEpoch}.csv';

    if (kIsWeb) {
      // Web: use browser download via conditional import
      web_helper.downloadCsvBytes(bytes, fileName);
    } else {
      // Mobile (Android/iOS): generate a simple PDF table and share/print it
      final pdf = pw.Document();
      pdf.addPage(
        pw.MultiPage(
          pageFormat: PdfPageFormat.a4.landscape,
          textDirection: pw.TextDirection.rtl,
          build: (context) => [
            pw.Header(level: 0, text: 'كشف طلبات الوسيط الرقمي - منصة مقاييس', textStyle: pw.TextStyle(fontSize: 14, fontWeight: pw.FontWeight.bold)),
            pw.Table.fromTextArray(
              headers: rows.first,
              data: rows.skip(1).toList(),
              headerStyle: pw.TextStyle(fontSize: 8, fontWeight: pw.FontWeight.bold),
              cellStyle: const pw.TextStyle(fontSize: 7),
              cellAlignment: pw.Alignment.centerRight,
              headerAlignment: pw.Alignment.centerRight,
            ),
          ],
        ),
      );
      await Printing.sharePdf(bytes: await pdf.save(), filename: fileName.replaceAll('.csv', '.pdf'));
    }

    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('تم تصدير كشف طلبات الوسيط الرقمي بنجاح'),
        backgroundColor: Color(0xFF059669),
      ),
    );
  }

  Future<void> _makeCall(String phone) async {
    final clean = phone.replaceAll(RegExp(r'[^0-9+]'), '');
    final uri = Uri.parse('tel:$clean');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  Future<void> _sendWhatsApp({required String phone, required String message}) async {
    String clean = phone.replaceAll(RegExp(r'[^0-9]'), '');
    if (clean.startsWith('0') && clean.length == 10) {
      // Local Sudanese phone e.g. 0912345678 -> 249912345678
      clean = '249${clean.substring(1)}';
    } else if (clean.length == 9 && (clean.startsWith('9') || clean.startsWith('1'))) {
      clean = '249$clean';
    }
    final encoded = Uri.encodeComponent(message);
    final url = clean.isNotEmpty
        ? 'https://wa.me/$clean?text=$encoded'
        : 'https://wa.me/?text=$encoded';
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final asyncInsights = ref.watch(adminDigitalBrokerProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: asyncInsights.when(
        loading: () => const Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              CircularProgressIndicator(),
              SizedBox(height: 16),
              Text(
                'جارٍ جلب وتدقيق طلبات الوسيط الرقمي وشبكة الموردين...',
                style: TextStyle(color: Color(0xFF64748B), fontWeight: FontWeight.bold),
              ),
            ],
          ),
        ),
        error: (e, st) => AdminErrorView(
          error: e,
          stackTrace: st,
          onRetry: () => ref.refresh(adminDigitalBrokerProvider),
        ),
        data: (insights) {
          // Filter requests
          var list = insights.requests.where((r) {
            // Status filter
            if (_statusFilter == 'pending' && r.status != 'pending') return false;
            if (_statusFilter == 'contacted' && r.status != 'contacted') return false;
            if (_statusFilter == 'closed' && r.status != 'closed') return false;

            // Supplier filter
            if (_supplierFilter != 'all' && r.assignedSupplier != _supplierFilter) {
              return false;
            }

            // Search filter
            final q = _searchCtrl.text.trim().toLowerCase();
            if (q.isNotEmpty) {
              final inTicket = r.ticketNumber.toLowerCase().contains(q);
              final inMaterial = r.materialType.toLowerCase().contains(q);
              final inBuyer = r.buyerName.toLowerCase().contains(q);
              final inPhone = r.buyerPhone.toLowerCase().contains(q);
              final inNotes = r.notes.toLowerCase().contains(q);
              final inSupplier = (r.assignedSupplier ?? '').toLowerCase().contains(q);
              if (!inTicket && !inMaterial && !inBuyer && !inPhone && !inNotes && !inSupplier) {
                return false;
              }
            }
            return true;
          }).toList();

          // Sort
          if (_sortBy == 'newest') {
            list.sort((a, b) => (b.createdAt ?? DateTime(2000)).compareTo(a.createdAt ?? DateTime(2000)));
          } else if (_sortBy == 'oldest') {
            list.sort((a, b) => (a.createdAt ?? DateTime(2000)).compareTo(b.createdAt ?? DateTime(2000)));
          } else if (_sortBy == 'deal_desc') {
            list.sort((a, b) => b.dealValue.compareTo(a.dealValue));
          } else if (_sortBy == 'delivery') {
            list.sort((a, b) => (a.deliveryDate ?? '').compareTo(b.deliveryDate ?? ''));
          } else if (_sortBy == 'quantity') {
            list.sort((a, b) => b.quantity.compareTo(a.quantity));
          }

          return SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildHeader(insights, list),
                const SizedBox(height: 20),
                _buildKpiDashboard(insights),
                const SizedBox(height: 24),
                _buildFilterControls(insights),
                const SizedBox(height: 20),
                if (list.isEmpty)
                  _buildEmptyState()
                else if (_isTableView)
                  _buildTableView(list, insights.suppliers, isRtl)
                else
                  _buildGridView(list, insights.suppliers, isRtl),
              ],
            ),
          );
        },
      ),
    );
  }

  // ═══════════════════════════════════════════
  // 1. HEADER (Fully Responsive with Create Button)
  // ═══════════════════════════════════════════
  Widget _buildHeader(AdminDigitalBrokerInsights insights, List<QuoteRequestModel> filteredList) {
    return Container(
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: LayoutBuilder(
        builder: (context, constraints) {
          final isCompact = constraints.maxWidth < 950;
          return Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFF6366F1).withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: const Icon(Icons.hub_rounded, color: Color(0xFF6366F1), size: 28),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Wrap(
                          spacing: 10,
                          runSpacing: 6,
                          crossAxisAlignment: WrapCrossAlignment.center,
                          children: [
                            const Text(
                              'إدارة الوسيط الرقمي وتوريدات المشاريع (B2B RFQ)',
                              style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: const Color(0xFFEEF2FF),
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(color: const Color(0xFFC7D2FE)),
                              ),
                              child: const Text('دعم الموردين والمعلنين', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF4338CA))),
                            ),
                          ],
                        ),
                        const SizedBox(height: 6),
                        const Text(
                          'استقبال وإدارة طلبات عروض الأسعار والتوريدات الكبرى ومطابقتها وتوجيهها للمعلنين والموردين المعتمدين',
                          style: TextStyle(fontSize: 13, color: Color(0xFF64748B)),
                        ),
                      ],
                    ),
                  ),
                  if (!isCompact) ...[
                    const SizedBox(width: 12),
                    OutlinedButton.icon(
                      onPressed: () => _exportCsv(filteredList),
                      icon: const Icon(Icons.download_rounded, size: 18),
                      label: const Text('تصدير كـ CSV', style: TextStyle(fontWeight: FontWeight.bold)),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: const Color(0xFF0F172A),
                        side: const BorderSide(color: Color(0xFFCBD5E1)),
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                    const SizedBox(width: 8),
                    ElevatedButton.icon(
                      onPressed: () => _showCreateRfqDialog(insights.suppliers),
                      icon: const Icon(Icons.add_circle_outline_rounded, size: 18),
                      label: const Text('إضافة طلب وساطة +', style: TextStyle(fontWeight: FontWeight.bold)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF059669),
                        foregroundColor: Colors.white,
                        elevation: 0,
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                    const SizedBox(width: 8),
                    ElevatedButton.icon(
                      onPressed: () => ref.refresh(adminDigitalBrokerProvider),
                      icon: const Icon(Icons.refresh_rounded, size: 18),
                      label: const Text('تحديث فوري', style: TextStyle(fontWeight: FontWeight.bold)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF6366F1),
                        foregroundColor: Colors.white,
                        elevation: 0,
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                  ],
                ],
              ),
              if (isCompact) ...[
                const SizedBox(height: 16),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    OutlinedButton.icon(
                      onPressed: () => _exportCsv(filteredList),
                      icon: const Icon(Icons.download_rounded, size: 18),
                      label: const Text('تصدير CSV', style: TextStyle(fontWeight: FontWeight.bold)),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: const Color(0xFF0F172A),
                        side: const BorderSide(color: Color(0xFFCBD5E1)),
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                    ElevatedButton.icon(
                      onPressed: () => _showCreateRfqDialog(insights.suppliers),
                      icon: const Icon(Icons.add_circle_outline_rounded, size: 18),
                      label: const Text('إضافة طلب وساطة +', style: TextStyle(fontWeight: FontWeight.bold)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF059669),
                        foregroundColor: Colors.white,
                        elevation: 0,
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                    ElevatedButton.icon(
                      onPressed: () => ref.refresh(adminDigitalBrokerProvider),
                      icon: const Icon(Icons.refresh_rounded, size: 18),
                      label: const Text('تحديث فوري', style: TextStyle(fontWeight: FontWeight.bold)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF6366F1),
                        foregroundColor: Colors.white,
                        elevation: 0,
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                  ],
                ),
              ],
            ],
          );
        },
      ),
    );
  }

  // ═══════════════════════════════════════════
  // 2. 5-KPI DASHBOARD (Accounting & Operation Focus)
  // ═══════════════════════════════════════════
  Widget _buildKpiDashboard(AdminDigitalBrokerInsights insights) {
    return LayoutBuilder(builder: (context, constraints) {
      final w = constraints.maxWidth;
      int cols = 5;
      if (w < 850) {
        cols = 2;
      } else if (w < 1200) {
        cols = 3;
      }

      final cards = [
        _buildKpiCard(
          title: 'حجم صفقات التوريد (GMV)',
          value: _fmt(insights.totalEstimatedDealVolume),
          subtitle: 'إجمالي قيمة توريدات B2B المعتمدة',
          icon: Icons.monetization_on_rounded,
          color: const Color(0xFF059669),
          badge: 'صفقات التوريد',
        ),
        _buildKpiCard(
          title: 'عمولات الوساطة لمقاييس',
          value: _fmt(insights.totalBrokerageCommissions),
          subtitle: 'أرباح المنصة بنسبة 2.0% من الصفقات',
          icon: Icons.account_balance_wallet_rounded,
          color: const Color(0xFF6366F1),
          badge: 'إيراد وساطة',
        ),
        _buildKpiCard(
          title: 'طلبات التوريد النشطة',
          value: '${insights.pendingRequests + insights.contactedRequests} طلبات',
          subtitle: '${insights.pendingRequests} قيد المطابقة + ${insights.contactedRequests} قيد التسعير',
          icon: Icons.pending_actions_rounded,
          color: const Color(0xFFF59E0B),
          badge: 'عروض نشطة',
        ),
        _buildKpiCard(
          title: 'توريدات مكتملة وموردة',
          value: '${insights.closedRequests} صفقات',
          subtitle: 'تم التوريد والتسليم للموقع بنجاح',
          icon: Icons.verified_rounded,
          color: const Color(0xFF10B981),
          badge: 'موردة بنجاح',
        ),
        _buildKpiCard(
          title: 'شبكة المعلنين والموردين',
          value: '${insights.accreditedSuppliersCount} شريكاً',
          subtitle: 'متاجر، أساطيل، ومكاتب استشارات',
          icon: Icons.storefront_rounded,
          color: const Color(0xFF8B5CF6),
          badge: 'جاهزون للتوريد',
        ),
      ];

      return Wrap(
        spacing: 14,
        runSpacing: 14,
        children: cards.map((c) {
          final itemWidth = (w - (cols - 1) * 14) / cols;
          return SizedBox(width: itemWidth.clamp(200.0, double.infinity), child: c);
        }).toList(),
      );
    });
  }

  Widget _buildKpiCard({
    required String title,
    required String value,
    required String subtitle,
    required IconData icon,
    required Color color,
    required String badge,
  }) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, color: color, size: 22),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.08),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(badge, style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.bold)),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Text(value, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
          const SizedBox(height: 4),
          Text(title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF475569))),
          const SizedBox(height: 2),
          Text(subtitle, style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8)), maxLines: 1, overflow: TextOverflow.ellipsis),
        ],
      ),
    );
  }

  // ═══════════════════════════════════════════
  // 3. FILTER CONTROLS, SUPPLIER CHIPS & TABS
  // ═══════════════════════════════════════════
  Widget _buildFilterControls(AdminDigitalBrokerInsights insights) {
    // Unique assigned suppliers for filter dropdown
    final suppliersSet = <String>{};
    for (var r in insights.requests) {
      if (r.assignedSupplier != null && r.assignedSupplier!.isNotEmpty) {
        suppliersSet.add(r.assignedSupplier!);
      }
    }

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        children: [
          Row(
            children: [
              // Status Filter Tabs
              Expanded(
                child: SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      _buildTabChip('all', 'الكل (${insights.totalRequests})'),
                      const SizedBox(width: 8),
                      _buildTabChip('pending', 'قيد الانتظار (${insights.pendingRequests})'),
                      const SizedBox(width: 8),
                      _buildTabChip('contacted', 'تم التواصل (${insights.contactedRequests})'),
                      const SizedBox(width: 8),
                      _buildTabChip('closed', 'مكتمل ومورد (${insights.closedRequests})'),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 16),
              // View Switcher (Cards vs Table)
              Container(
                decoration: BoxDecoration(
                  color: const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(10),
                ),
                padding: const EdgeInsets.all(3),
                child: Row(
                  children: [
                    InkWell(
                      onTap: () => setState(() => _isTableView = false),
                      borderRadius: BorderRadius.circular(8),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                        decoration: BoxDecoration(
                          color: !_isTableView ? Colors.white : Colors.transparent,
                          borderRadius: BorderRadius.circular(8),
                          boxShadow: !_isTableView
                              ? [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 4)]
                              : null,
                        ),
                        child: Row(
                          children: [
                            Icon(Icons.grid_view_rounded, size: 16, color: !_isTableView ? const Color(0xFF0F172A) : const Color(0xFF64748B)),
                            const SizedBox(width: 6),
                            Text('بطاقات', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: !_isTableView ? const Color(0xFF0F172A) : const Color(0xFF64748B))),
                          ],
                        ),
                      ),
                    ),
                    InkWell(
                      onTap: () => setState(() => _isTableView = true),
                      borderRadius: BorderRadius.circular(8),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                        decoration: BoxDecoration(
                          color: _isTableView ? Colors.white : Colors.transparent,
                          borderRadius: BorderRadius.circular(8),
                          boxShadow: _isTableView
                              ? [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 4)]
                              : null,
                        ),
                        child: Row(
                          children: [
                            Icon(Icons.table_chart_rounded, size: 16, color: _isTableView ? const Color(0xFF0F172A) : const Color(0xFF64748B)),
                            const SizedBox(width: 6),
                            Text('سجل محاسبي', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: _isTableView ? const Color(0xFF0F172A) : const Color(0xFF64748B))),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              // Search Input
              Expanded(
                flex: 3,
                child: TextField(
                  controller: _searchCtrl,
                  onChanged: (_) => setState(() {}),
                  decoration: InputDecoration(
                    hintText: 'بحث برقم التذكرة، الصنف المطلوب، اسم المشتري، رقم الهاتف، أو المورد المعلن...',
                    prefixIcon: const Icon(Icons.search_rounded, size: 20, color: Color(0xFF94A3B8)),
                    suffixIcon: _searchCtrl.text.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear, size: 18),
                            onPressed: () {
                              _searchCtrl.clear();
                              setState(() {});
                            },
                          )
                        : null,
                    filled: true,
                    fillColor: const Color(0xFFF8FAFC),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              // Filter by Supplier/Advertiser ("دعم المعلنين")
              Expanded(
                flex: 2,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFAF5FF),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: const Color(0xFFE9D5FF)),
                  ),
                  child: DropdownButtonHideUnderline(
                    child: DropdownButton<String>(
                      value: _supplierFilter,
                      icon: const Icon(Icons.campaign_rounded, size: 20, color: Color(0xFF9333EA)),
                      isExpanded: true,
                      items: [
                        const DropdownMenuItem(value: 'all', child: Text('جميع الموردين والمعلنين (الكل)')),
                        ...suppliersSet.map((s) => DropdownMenuItem(
                          value: s,
                          child: Text('📢 $s', maxLines: 1, overflow: TextOverflow.ellipsis),
                        )),
                      ],
                      onChanged: (val) {
                        if (val != null) setState(() => _supplierFilter = val);
                      },
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              // Sort dropdown
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<String>(
                    value: _sortBy,
                    icon: const Icon(Icons.sort_rounded, size: 20, color: Color(0xFF64748B)),
                    items: const [
                      DropdownMenuItem(value: 'newest', child: Text('الأحدث أولاً ⏱️')),
                      DropdownMenuItem(value: 'deal_desc', child: Text('الأعلى قيمة صفقة 💰')),
                      DropdownMenuItem(value: 'delivery', child: Text('الأقرب تسليماً 📅')),
                      DropdownMenuItem(value: 'quantity', child: Text('الأكبر كمية 📦')),
                      DropdownMenuItem(value: 'oldest', child: Text('الأقدم أولاً ⏳')),
                    ],
                    onChanged: (val) {
                      if (val != null) setState(() => _sortBy = val);
                    },
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildTabChip(String key, String label) {
    final isSelected = _statusFilter == key;
    return InkWell(
      onTap: () => setState(() => _statusFilter = key),
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF0F172A) : const Color(0xFFF1F5F9),
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.bold,
            color: isSelected ? Colors.white : const Color(0xFF475569),
          ),
        ),
      ),
    );
  }

  // ═══════════════════════════════════════════
  // 4. GRID VIEW (Rich Interactive Cards)
  // ═══════════════════════════════════════════
  Widget _buildGridView(
    List<QuoteRequestModel> list,
    List<DigitalBrokerSupplier> suppliers,
    bool isRtl,
  ) {
    return LayoutBuilder(builder: (context, constraints) {
      final w = constraints.maxWidth;
      int crossAxisCount = 1;
      if (w > 1250) {
        crossAxisCount = 3;
      } else if (w > 780) {
        crossAxisCount = 2;
      }

      return GridView.builder(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: crossAxisCount,
          crossAxisSpacing: 16,
          mainAxisSpacing: 16,
          mainAxisExtent: 350,
        ),
        itemCount: list.length,
        itemBuilder: (context, index) {
          final req = list[index];
          return _buildQuoteCard(req, suppliers, isRtl);
        },
      );
    });
  }

  Widget _buildQuoteCard(
    QuoteRequestModel req,
    List<DigitalBrokerSupplier> suppliers,
    bool isRtl,
  ) {
    Color statusColor = const Color(0xFFF59E0B);
    String statusText = 'قيد الانتظار';
    if (req.status == 'contacted') {
      statusColor = const Color(0xFF0EA5E9);
      statusText = 'تم التواصل والتسعير';
    } else if (req.status == 'closed') {
      statusColor = const Color(0xFF10B981);
      statusText = 'مكتمل ومورد';
    } else if (req.status == 'cancelled') {
      statusColor = const Color(0xFFEF4444);
      statusText = 'ملغي';
    }

    final dateStr = req.createdAt != null
        ? DateFormat('yyyy/MM/dd HH:mm').format(req.createdAt!)
        : '-';

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      padding: const EdgeInsets.all(18),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Card Header: Ticket & Status Badge
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: const Color(0xFFCBD5E1)),
                    ),
                    child: Text(
                      '#${req.ticketNumber}',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF334155)),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Text(dateStr, style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8))),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  statusText,
                  style: TextStyle(color: statusColor, fontSize: 11, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
          const Divider(height: 20, color: Color(0xFFF1F5F9)),

          // Material Requested & Quantity
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.construction_rounded, color: Color(0xFF2563EB), size: 20),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      req.materialType,
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF0F172A)),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'الكمية: ${req.quantity}',
                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF475569)),
                    ),
                  ],
                ),
              ),
              if (req.deliveryDate != null && req.deliveryDate!.isNotEmpty)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF8FAFC),
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.calendar_month_rounded, size: 13, color: Color(0xFF64748B)),
                      const SizedBox(width: 4),
                      Text(req.deliveryDate!, style: const TextStyle(fontSize: 11, color: Color(0xFF64748B))),
                    ],
                  ),
                ),
            ],
          ),
          const SizedBox(height: 12),

          // Buyer Details
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Row(
              children: [
                const Icon(Icons.person_outline_rounded, size: 16, color: Color(0xFF64748B)),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    req.buyerName,
                    style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                Text(
                  req.buyerPhone,
                  style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                ),
                const SizedBox(width: 8),
                InkWell(
                  onTap: () => _makeCall(req.buyerPhone),
                  child: const Padding(
                    padding: EdgeInsets.all(4),
                    child: Icon(Icons.phone_in_talk_rounded, size: 16, color: Color(0xFF059669)),
                  ),
                ),
                InkWell(
                  onTap: () => _sendWhatsApp(
                    phone: req.buyerPhone,
                    message: 'السلام عليكم ورحمة الله، بخصوص طلب التوريد رقم #${req.ticketNumber} (${req.materialType}) المقدم عبر منصة مقاييس...',
                  ),
                  child: const Padding(
                    padding: EdgeInsets.all(4),
                    child: Icon(Icons.chat_bubble_outline_rounded, size: 16, color: Color(0xFF2563EB)),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 10),

          // Assigned Advertiser / Supplier Badge ("ممتاز في دعم المعلنين")
          if (req.assignedSupplier != null && req.assignedSupplier!.isNotEmpty)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: const Color(0xFFFAF5FF),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: const Color(0xFFE9D5FF)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.campaign_rounded, size: 16, color: Color(0xFF9333EA)),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      'المورد المعلن: ${req.assignedSupplier}',
                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF7E22CE)),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFF9333EA).withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: const Text('شريك معتمد', style: TextStyle(fontSize: 9, color: Color(0xFF9333EA), fontWeight: FontWeight.bold)),
                  ),
                ],
              ),
            )
          else
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: const Color(0xFFFFFBEB),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: const Color(0xFFFEF3C7)),
              ),
              child: const Row(
                children: [
                  Icon(Icons.storefront_outlined, size: 15, color: Color(0xFFD97706)),
                  SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      'لم يتم توجيه الطلب إلى معلن أو مورد بعد',
                      style: TextStyle(fontSize: 11, color: Color(0xFFB45309)),
                    ),
                  ),
                ],
              ),
            ),

          const Spacer(),

          // Deal Economics & Actions
          Container(
            padding: const EdgeInsets.only(top: 8),
            decoration: const BoxDecoration(
              border: Border(top: BorderSide(color: Color(0xFFF1F5F9))),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (req.dealValue > 0) ...[
                      Text(
                        _fmt(req.dealValue),
                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                      ),
                      Text(
                        'عمولة مقاييس: ${_fmt(req.commissionValue)}',
                        style: const TextStyle(fontSize: 10, color: Color(0xFF059669), fontWeight: FontWeight.bold),
                      ),
                    ] else ...[
                      const Text(
                        'قيد التسعير والمطابقة',
                        style: TextStyle(fontSize: 11, color: Color(0xFF94A3B8), fontWeight: FontWeight.w600),
                      ),
                    ],
                  ],
                ),
                Row(
                  children: [
                    IconButton(
                      tooltip: 'عرض أمر التوريد والطباعة 📄',
                      icon: const Icon(Icons.print_outlined, size: 18, color: Color(0xFF475569)),
                      onPressed: () => _showPrintableVoucherDialog(req),
                    ),
                    IconButton(
                      tooltip: 'مشاركة ومطابقة عبر واتساب 💬',
                      icon: const Icon(Icons.share_rounded, size: 18, color: Color(0xFF059669)),
                      onPressed: () => _shareWithSupplier(req, suppliers),
                    ),
                    ElevatedButton.icon(
                      onPressed: () => _showManageDealDialog(req, suppliers),
                      icon: const Icon(Icons.tune_rounded, size: 15),
                      label: const Text('إدارة الصفقة', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF0F172A),
                        foregroundColor: Colors.white,
                        elevation: 0,
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
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

  // ═══════════════════════════════════════════
  // 5. TABLE LEDGER VIEW
  // ═══════════════════════════════════════════
  Widget _buildTableView(
    List<QuoteRequestModel> list,
    List<DigitalBrokerSupplier> suppliers,
    bool isRtl,
  ) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: DataTable(
            headingRowColor: WidgetStateProperty.all(const Color(0xFFF8FAFC)),
            horizontalMargin: 20,
            columnSpacing: 24,
            columns: const [
              DataColumn(label: Text('رقم التذكرة', style: TextStyle(fontWeight: FontWeight.bold))),
              DataColumn(label: Text('المشتري / العميل', style: TextStyle(fontWeight: FontWeight.bold))),
              DataColumn(label: Text('المواد المطلوبة', style: TextStyle(fontWeight: FontWeight.bold))),
              DataColumn(label: Text('الكمية', style: TextStyle(fontWeight: FontWeight.bold))),
              DataColumn(label: Text('تاريخ التسليم', style: TextStyle(fontWeight: FontWeight.bold))),
              DataColumn(label: Text('المورد المعلن المعتمد', style: TextStyle(fontWeight: FontWeight.bold))),
              DataColumn(label: Text('قيمة الصفقة (ج.س)', style: TextStyle(fontWeight: FontWeight.bold))),
              DataColumn(label: Text('الحالة', style: TextStyle(fontWeight: FontWeight.bold))),
              DataColumn(label: Text('الإجراءات', style: TextStyle(fontWeight: FontWeight.bold))),
            ],
            rows: list.map((req) {
              Color statusColor = const Color(0xFFF59E0B);
              String statusText = 'قيد الانتظار';
              if (req.status == 'contacted') {
                statusColor = const Color(0xFF0EA5E9);
                statusText = 'تم التواصل';
              } else if (req.status == 'closed') {
                statusColor = const Color(0xFF10B981);
                statusText = 'مكتمل';
              } else if (req.status == 'cancelled') {
                statusColor = const Color(0xFFEF4444);
                statusText = 'ملغي';
              }

              return DataRow(cells: [
                DataCell(
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text('#${req.ticketNumber}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                  ),
                ),
                DataCell(
                  Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(req.buyerName, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                      Text(req.buyerPhone, style: const TextStyle(color: Color(0xFF64748B), fontSize: 11)),
                    ],
                  ),
                ),
                DataCell(
                  SizedBox(
                    width: 160,
                    child: Text(req.materialType, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w600)),
                  ),
                ),
                DataCell(Text(req.quantity, style: const TextStyle(fontWeight: FontWeight.bold))),
                DataCell(Text(req.deliveryDate ?? '-')),
                DataCell(
                  req.assignedSupplier != null && req.assignedSupplier!.isNotEmpty
                      ? Chip(
                          label: Text(req.assignedSupplier!, style: const TextStyle(fontSize: 11, color: Color(0xFF7E22CE), fontWeight: FontWeight.bold)),
                          backgroundColor: const Color(0xFFFAF5FF),
                          side: const BorderSide(color: Color(0xFFE9D5FF)),
                          padding: EdgeInsets.zero,
                        )
                      : const Text('غير محدد', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 12)),
                ),
                DataCell(
                  Text(
                    req.dealValue > 0 ? _fmt(req.dealValue) : 'قيد التسعير',
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      color: req.dealValue > 0 ? const Color(0xFF0F172A) : const Color(0xFF94A3B8),
                    ),
                  ),
                ),
                DataCell(
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: statusColor.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(statusText, style: TextStyle(color: statusColor, fontSize: 11, fontWeight: FontWeight.bold)),
                  ),
                ),
                DataCell(
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      IconButton(
                        icon: const Icon(Icons.print_outlined, size: 18, color: Color(0xFF475569)),
                        tooltip: 'أمر التوريد والطباعة',
                        onPressed: () => _showPrintableVoucherDialog(req),
                      ),
                      IconButton(
                        icon: const Icon(Icons.tune_rounded, size: 18, color: Color(0xFF2563EB)),
                        tooltip: 'إدارة وتوجيه الصفقة',
                        onPressed: () => _showManageDealDialog(req, suppliers),
                      ),
                      IconButton(
                        icon: const Icon(Icons.share_rounded, size: 18, color: Color(0xFF059669)),
                        tooltip: 'مشاركة مع المعلن',
                        onPressed: () => _shareWithSupplier(req, suppliers),
                      ),
                      IconButton(
                        icon: const Icon(Icons.delete_outline_rounded, size: 18, color: Color(0xFFEF4444)),
                        tooltip: 'حذف الطلب',
                        onPressed: () => _confirmDelete(req),
                      ),
                    ],
                  ),
                ),
              ]);
            }).toList(),
          ),
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Container(
      padding: const EdgeInsets.all(40),
      alignment: Alignment.center,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: const Column(
        children: [
          Icon(Icons.search_off_rounded, size: 48, color: Color(0xFF94A3B8)),
          SizedBox(height: 12),
          Text(
            'لا توجد طلبات تطابق معايير البحث أو التصفية الحالية',
            style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF475569)),
          ),
          SizedBox(height: 4),
          Text(
            'يمكنك تغيير حالة الفلتر أو محو نص البحث لإظهار كافة التذاكر',
            style: TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
          ),
        ],
      ),
    );
  }

  // ═══════════════════════════════════════════
  // 6. MANAGE & MATCH DEAL DIALOG
  // ═══════════════════════════════════════════
  void _showManageDealDialog(
    QuoteRequestModel req,
    List<DigitalBrokerSupplier> suppliers,
  ) {
    final validStatuses = ['pending', 'contacted', 'closed', 'cancelled'];
    String currentStatus = validStatuses.contains(req.status.toLowerCase()) ? req.status.toLowerCase() : 'pending';

    final dealValueCtrl = TextEditingController(
      text: req.dealValue > 0 ? req.dealValue.toStringAsFixed(0) : '',
    );
    final commissionPercentCtrl = TextEditingController(
      text: req.dealValue > 0 && req.commissionValue > 0
          ? ((req.commissionValue / req.dealValue) * 100).toStringAsFixed(1)
          : '2.0',
    );
    String selectedSupplier = req.assignedSupplier ?? '';
    final notesCtrl = TextEditingController();

    final hasSupplierInList = suppliers.any((s) => s.name == selectedSupplier);

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDialogState) {
          final dVal = double.tryParse(dealValueCtrl.text.replaceAll(',', '')) ?? 0.0;
          final cPct = double.tryParse(commissionPercentCtrl.text) ?? 2.0;
          final cAmt = (dVal * cPct) / 100.0;
          final netSupplier = dVal - cAmt;

          return AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            titlePadding: EdgeInsets.zero,
            title: Container(
              padding: const EdgeInsets.all(20),
              decoration: const BoxDecoration(
                color: Color(0xFF0F172A),
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(16),
                  topRight: Radius.circular(16),
                ),
              ),
              child: Row(
                children: [
                  const Icon(Icons.hub_rounded, color: Color(0xFF818CF8), size: 24),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'إدارة صفقة الوسيط الرقمي #${req.ticketNumber}',
                          style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                        Text(
                          'توجيه التوريد ومطابقة المعلنين والاتفاق المالي',
                          style: TextStyle(color: Colors.white.withValues(alpha: 0.7), fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, color: Colors.white70),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
            ),
            content: SizedBox(
              width: 580,
              child: SingleChildScrollView(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Customer & Contact Summary Card
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(10),
                            decoration: BoxDecoration(
                              color: const Color(0xFFEFF6FF),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: const Icon(Icons.person_rounded, color: Color(0xFF2563EB)),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  req.buyerName,
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  req.buyerPhone,
                                  style: const TextStyle(color: Color(0xFF64748B), fontSize: 12),
                                ),
                              ],
                            ),
                          ),
                          OutlinedButton.icon(
                            onPressed: () => _makeCall(req.buyerPhone),
                            icon: const Icon(Icons.phone, size: 14),
                            label: const Text('اتصال', style: TextStyle(fontSize: 11)),
                            style: OutlinedButton.styleFrom(
                              foregroundColor: const Color(0xFF059669),
                              side: const BorderSide(color: Color(0xFF10B981)),
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                            ),
                          ),
                          const SizedBox(width: 6),
                          ElevatedButton.icon(
                            onPressed: () => _sendWhatsApp(
                              phone: req.buyerPhone,
                              message: 'السلام عليكم ورحمة الله، بخصوص تذكرة التوريد #${req.ticketNumber} الخاصة بـ (${req.materialType})...',
                            ),
                            icon: const Icon(Icons.chat, size: 14),
                            label: const Text('واتساب', style: TextStyle(fontSize: 11)),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF2563EB),
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                              elevation: 0,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Material & Items Breakdown
                    const Text('المواد والكميات المطلوبة من العميل:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF334155))),
                    const SizedBox(height: 6),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF1F5F9),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            '📌 الصنف الرئيسي: ${req.materialType}',
                            style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                          ),
                          const SizedBox(height: 4),
                          Text('📦 الكمية الكلية: ${req.quantity}', style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF475569))),
                          if (req.deliveryDate != null && req.deliveryDate!.isNotEmpty) ...[
                            const SizedBox(height: 4),
                            Text('📅 موعد التسليم: ${req.deliveryDate}', style: const TextStyle(color: Color(0xFF64748B))),
                          ],
                          if (req.itemsBreakdown.isNotEmpty) ...[
                            const Divider(height: 14),
                            ...req.itemsBreakdown.map((it) => Padding(
                              padding: const EdgeInsets.only(bottom: 2),
                              child: Text('• $it', style: const TextStyle(fontSize: 12, color: Color(0xFF334155))),
                            )),
                          ],
                        ],
                      ),
                    ),
                    const SizedBox(height: 18),

                    // Section: Advertiser / Supplier Routing ("دعم المعلنين")
                    Row(
                      children: [
                        const Icon(Icons.campaign_rounded, size: 18, color: Color(0xFF9333EA)),
                        const SizedBox(width: 6),
                        const Text(
                          'توجيه الصفقة إلى معلن / مورد معتمد:',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF7E22CE)),
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<String>(
                      initialValue: selectedSupplier.isNotEmpty ? selectedSupplier : '',
                      decoration: InputDecoration(
                        hintText: 'اختر متجراً معلناً أو شركة أسطول أو مكتباً هندسياً...',
                        filled: true,
                        fillColor: const Color(0xFFFAF5FF),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: Color(0xFFE9D5FF))),
                        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: Color(0xFFE9D5FF))),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                      items: [
                        const DropdownMenuItem(value: '', child: Text('— بدون تعيين حالياً —')),
                        if (selectedSupplier.isNotEmpty && !hasSupplierInList)
                          DropdownMenuItem(
                            value: selectedSupplier,
                            child: Text('$selectedSupplier (مورد مسجل)'),
                          ),
                        ...suppliers.map((s) => DropdownMenuItem(
                          value: s.name,
                          child: Row(
                            children: [
                              Icon(
                                s.type == 'fleet' ? Icons.local_shipping_rounded : (s.type == 'engineering' ? Icons.architecture_rounded : Icons.store_rounded),
                                size: 16,
                                color: const Color(0xFF9333EA),
                              ),
                              const SizedBox(width: 8),
                              Text('${s.name} (${s.location})', style: const TextStyle(fontSize: 13)),
                            ],
                          ),
                        )),
                      ],
                      onChanged: (val) {
                        setDialogState(() => selectedSupplier = val ?? '');
                      },
                    ),
                    const SizedBox(height: 18),

                    // Section: Financial Deal Economics
                    const Row(
                      children: [
                        Icon(Icons.monetization_on_rounded, size: 18, color: Color(0xFF059669)),
                        SizedBox(width: 6),
                        Text(
                          'الحسابات والوساطة المالية (Deal Economics):',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF065F46)),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Expanded(
                          flex: 3,
                          child: TextFormField(
                            controller: dealValueCtrl,
                            keyboardType: TextInputType.number,
                            onChanged: (_) => setDialogState(() {}),
                            decoration: InputDecoration(
                              labelText: 'قيمة الصفقة الإجمالية (ج.س)',
                              filled: true,
                              fillColor: const Color(0xFFF0FDF4),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: Color(0xFFA7F3D0))),
                              enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: Color(0xFFA7F3D0))),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          flex: 2,
                          child: TextFormField(
                            controller: commissionPercentCtrl,
                            keyboardType: TextInputType.number,
                            onChanged: (_) => setDialogState(() {}),
                            decoration: InputDecoration(
                              labelText: 'عمولة مقاييس (%)',
                              filled: true,
                              fillColor: const Color(0xFFF0FDF4),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: Color(0xFFA7F3D0))),
                              enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: Color(0xFFA7F3D0))),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),

                    // Financial Calculations Summary
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFECFDF5),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: const Color(0xFFA7F3D0)),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceAround,
                        children: [
                          Column(
                            children: [
                              const Text('عمولة مقاييس', style: TextStyle(fontSize: 11, color: Color(0xFF047857))),
                              const SizedBox(height: 2),
                              Text(_fmt(cAmt), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF065F46))),
                            ],
                          ),
                          Container(width: 1, height: 28, color: const Color(0xFFA7F3D0)),
                          Column(
                            children: [
                              const Text('صافي نصيب المورد المعلن', style: TextStyle(fontSize: 11, color: Color(0xFF047857))),
                              const SizedBox(height: 2),
                              Text(_fmt(netSupplier), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF065F46))),
                            ],
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 18),

                    // Update Status
                    const Text('حالة طلب الوساطة والتوريد:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF334155))),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<String>(
                      initialValue: currentStatus,
                      decoration: InputDecoration(
                        filled: true,
                        fillColor: const Color(0xFFF8FAFC),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                      items: const [
                        DropdownMenuItem(value: 'pending', child: Text('قيد الانتظار والمطابقة ⏳')),
                        DropdownMenuItem(value: 'contacted', child: Text('تم التواصل وتوجيه العرض 📞')),
                        DropdownMenuItem(value: 'closed', child: Text('مكتمل ومورد بنجاح ✅')),
                        DropdownMenuItem(value: 'cancelled', child: Text('ملغي من قبل العميل ❌')),
                      ],
                      onChanged: (val) {
                        if (val != null) setDialogState(() => currentStatus = val);
                      },
                    ),
                    const SizedBox(height: 14),

                    // Additional Broker Notes
                    TextFormField(
                      controller: notesCtrl,
                      maxLines: 2,
                      decoration: InputDecoration(
                        labelText: 'ملاحظات وسيط مقاييس الإدارية (اختياري)',
                        hintText: 'أي شروط دفع، موقع التفريغ، أو تفاصيل المورد...',
                        filled: true,
                        fillColor: const Color(0xFFF8FAFC),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                        contentPadding: const EdgeInsets.all(12),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            actionsPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: const Text('إلغاء', style: TextStyle(color: Color(0xFF64748B))),
              ),
              OutlinedButton.icon(
                onPressed: () {
                  _shareWithSupplier(req, suppliers, customSupplier: selectedSupplier);
                },
                icon: const Icon(Icons.share_rounded, size: 16),
                label: const Text('مشاركة للمعلن واتساب', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                style: OutlinedButton.styleFrom(
                  foregroundColor: const Color(0xFF2563EB),
                  side: const BorderSide(color: Color(0xFF93C5FD)),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                ),
              ),
              ElevatedButton.icon(
                onPressed: () async {
                  final messenger = ScaffoldMessenger.of(context);
                  Navigator.pop(ctx);
                  await ref.read(adminDigitalBrokerProvider.notifier).saveDealDetails(
                    id: req.id,
                    status: currentStatus,
                    dealValue: dVal,
                    commissionPercent: cPct,
                    assignedSupplier: selectedSupplier,
                    additionalNotes: notesCtrl.text.trim(),
                  );
                  messenger.showSnackBar(
                    const SnackBar(
                      content: Text('تم حفظ وتحديث بيانات صفقة الوسيط الرقمي بنجاح'),
                      backgroundColor: Color(0xFF059669),
                    ),
                  );
                },
                icon: const Icon(Icons.check_rounded, size: 16),
                label: const Text('حفظ وتحديث الصفقة', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF0F172A),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                ),
              ),
            ],
          );
        },
      ),
    );
  }

  // ═══════════════════════════════════════════
  // 7. CREATE NEW RFQ MODAL (+ إضافة طلب وساطة)
  // ═══════════════════════════════════════════
  void _showCreateRfqDialog(List<DigitalBrokerSupplier> suppliers) {
    final buyerNameCtrl = TextEditingController();
    final buyerPhoneCtrl = TextEditingController();
    final materialCtrl = TextEditingController();
    final quantityCtrl = TextEditingController();
    final deliveryDateCtrl = TextEditingController();
    final dealValueCtrl = TextEditingController();
    final commissionPercentCtrl = TextEditingController(text: '2.0');
    final notesCtrl = TextEditingController();
    String selectedSupplier = '';

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDialogState) {
          final dVal = double.tryParse(dealValueCtrl.text.replaceAll(',', '')) ?? 0.0;
          final cPct = double.tryParse(commissionPercentCtrl.text) ?? 2.0;
          final cAmt = (dVal * cPct) / 100.0;

          return AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            titlePadding: EdgeInsets.zero,
            title: Container(
              padding: const EdgeInsets.all(20),
              decoration: const BoxDecoration(
                color: Color(0xFF059669),
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(16),
                  topRight: Radius.circular(16),
                ),
              ),
              child: Row(
                children: [
                  const Icon(Icons.add_business_rounded, color: Colors.white, size: 24),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'إضافة طلب وساطة وتوريد جديد (New RFQ)',
                          style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                        Text(
                          'تسجيل طلب وارد من مقاول أو شركة ومطابقته فورياً مع المعلنين',
                          style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, color: Colors.white70),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
            ),
            content: SizedBox(
              width: 580,
              child: SingleChildScrollView(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Client Info
                    const Text('بيانات المشتري أو المقاول:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF334155))),
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        Expanded(
                          child: TextFormField(
                            controller: buyerNameCtrl,
                            decoration: InputDecoration(
                              labelText: 'اسم العميل / الشركة *',
                              hintText: 'شركة المقرن للإنشاءات',
                              filled: true,
                              fillColor: const Color(0xFFF8FAFC),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: TextFormField(
                            controller: buyerPhoneCtrl,
                            decoration: InputDecoration(
                              labelText: 'رقم الهاتف *',
                              hintText: '0912345678',
                              filled: true,
                              fillColor: const Color(0xFFF8FAFC),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // Material & Quantity
                    const Text('المواد والكميات المطلوبة:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF334155))),
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        Expanded(
                          flex: 3,
                          child: TextFormField(
                            controller: materialCtrl,
                            decoration: InputDecoration(
                              labelText: 'الصنف ونوع المواد *',
                              hintText: 'حديد تسليح 12 مم، أسمنت، بلك، رمل...',
                              filled: true,
                              fillColor: const Color(0xFFF8FAFC),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          flex: 2,
                          child: TextFormField(
                            controller: quantityCtrl,
                            decoration: InputDecoration(
                              labelText: 'الكمية المطلوبة *',
                              hintText: '50 طن / 1000 جوال',
                              filled: true,
                              fillColor: const Color(0xFFF8FAFC),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // Delivery Date & Location
                    TextFormField(
                      controller: deliveryDateCtrl,
                      decoration: InputDecoration(
                        labelText: 'موعد التسليم المتوقع وموقع المشروع',
                        hintText: '2026-09-30 - مسقط - الخوير',
                        filled: true,
                        fillColor: const Color(0xFFF8FAFC),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Advertiser / Supplier Assignment
                    const Text('توجيه الطلب للمورد المعلن الشريك:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF7E22CE))),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<String>(
                      initialValue: selectedSupplier.isNotEmpty ? selectedSupplier : '',
                      decoration: InputDecoration(
                        filled: true,
                        fillColor: const Color(0xFFFAF5FF),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: Color(0xFFE9D5FF))),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      ),
                      items: [
                        const DropdownMenuItem(value: '', child: Text('— بدون تعيين حالياً —')),
                        ...suppliers.map((s) => DropdownMenuItem(
                          value: s.name,
                          child: Text('${s.name} (${s.location})', maxLines: 1, overflow: TextOverflow.ellipsis),
                        )),
                      ],
                      onChanged: (val) {
                        setDialogState(() => selectedSupplier = val ?? '');
                      },
                    ),
                    const SizedBox(height: 14),

                    // Economics
                    Row(
                      children: [
                        Expanded(
                          flex: 3,
                          child: TextFormField(
                            controller: dealValueCtrl,
                            keyboardType: TextInputType.number,
                            onChanged: (_) => setDialogState(() {}),
                            decoration: InputDecoration(
                              labelText: 'قيمة الصفقة التقديرية (ج.س)',
                              hintText: '10,000,000',
                              filled: true,
                              fillColor: const Color(0xFFF0FDF4),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          flex: 2,
                          child: TextFormField(
                            controller: commissionPercentCtrl,
                            keyboardType: TextInputType.number,
                            onChanged: (_) => setDialogState(() {}),
                            decoration: InputDecoration(
                              labelText: 'العمولة (%)',
                              hintText: '2.0',
                              filled: true,
                              fillColor: const Color(0xFFF0FDF4),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                            ),
                          ),
                        ),
                      ],
                    ),
                    if (dVal > 0) ...[
                      const SizedBox(height: 8),
                      Text(
                        'عمولة مقاييس المحسوبة: ${_fmt(cAmt)}',
                        style: const TextStyle(fontSize: 12, color: Color(0xFF047857), fontWeight: FontWeight.bold),
                      ),
                    ],
                    const SizedBox(height: 14),

                    // Notes
                    TextFormField(
                      controller: notesCtrl,
                      maxLines: 2,
                      decoration: InputDecoration(
                        labelText: 'ملاحظات إضافية',
                        hintText: 'أي تفاصيل خاصة بالتفريغ أو شروط الدفع...',
                        filled: true,
                        fillColor: const Color(0xFFF8FAFC),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        contentPadding: const EdgeInsets.all(12),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            actionsPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: const Text('إلغاء', style: TextStyle(color: Color(0xFF64748B))),
              ),
              ElevatedButton.icon(
                onPressed: () async {
                  final mat = materialCtrl.text.trim();
                  final qty = quantityCtrl.text.trim();
                  if (mat.isEmpty || qty.isEmpty) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('الرجاء إدخال الصنف والكمية المطلوبة')),
                    );
                    return;
                  }

                  final messenger = ScaffoldMessenger.of(context);
                  Navigator.pop(ctx);

                  // Generate Ticket Number
                  final now = DateTime.now();
                  final yy = now.year.toString().substring(2);
                  final mm = now.month.toString().padLeft(2, '0');
                  final rand = (1000 + Random().nextInt(9000)).toString();
                  final ticketNumber = 'T$yy$mm$rand';

                  final fDeal = dVal > 0 ? dVal.toStringAsFixed(0) : '0';
                  final fComm = cAmt > 0 ? cAmt.toStringAsFixed(0) : '0';

                  final buyerName = buyerNameCtrl.text.trim();
                  final buyerPhone = buyerPhoneCtrl.text.trim();
                  final userExtra = notesCtrl.text.trim();

                  final structuredNotes = '''رقم الطلب (مرجع): $ticketNumber

العميل المشتري: $buyerName
رقم الاتصال: $buyerPhone
${userExtra.isNotEmpty ? 'ملاحظات: $userExtra\n' : ''}
-- تفاصيل الأصناف --
صنف 1: $mat (الكمية: $qty)

-- بيانات الوساطة المالية --
قيمة الصفقة التقديرية: $fDeal
عمولة الوسيط الرقمي: $fComm ($cPct%)
المورد المعلن المعتمد: ${selectedSupplier.isNotEmpty ? selectedSupplier : 'غير محدد'}''';

                  try {
                    await Supabase.instance.client.from('quote_requests').insert({
                      'material_type': mat,
                      'quantity': qty,
                      'delivery_date': deliveryDateCtrl.text.trim(),
                      'notes': structuredNotes,
                      'status': 'pending',
                    });

                    ref.invalidate(adminDigitalBrokerProvider);

                    messenger.showSnackBar(
                      SnackBar(
                        content: Text('تم تسجيل طلب الوسيط الرقمي بنجاح برقم #$ticketNumber'),
                        backgroundColor: const Color(0xFF059669),
                      ),
                    );
                  } catch (e) {
                    messenger.showSnackBar(
                      SnackBar(content: Text('حدث خطأ أثناء الحفظ: $e'), backgroundColor: Colors.red),
                    );
                  }
                },
                icon: const Icon(Icons.check_circle_outline_rounded, size: 16),
                label: const Text('تسجيل طلب التوريد', style: TextStyle(fontWeight: FontWeight.bold)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF059669),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                ),
              ),
            ],
          );
        },
      ),
    );
  }

  // ═══════════════════════════════════════════
  // 8. PRINTABLE RFQ VOUCHER MODAL
  // ═══════════════════════════════════════════
  void _showPrintableVoucherDialog(QuoteRequestModel req) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        titlePadding: EdgeInsets.zero,
        title: Container(
          padding: const EdgeInsets.all(16),
          decoration: const BoxDecoration(
            color: Color(0xFF0F172A),
            borderRadius: BorderRadius.only(topLeft: Radius.circular(16), topRight: Radius.circular(16)),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Row(
                children: [
                  Icon(Icons.receipt_long_rounded, color: Colors.white, size: 22),
                  SizedBox(width: 8),
                  Text('أمر تسعير وتوريد رسمي (B2B RFQ)', style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
                ],
              ),
              IconButton(icon: const Icon(Icons.close, color: Colors.white70), onPressed: () => Navigator.pop(ctx)),
            ],
          ),
        ),
        content: SizedBox(
          width: 500,
          child: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Column(
                    children: [
                      const Text('منصة مقاييس لمواد البناء والمقاولات', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Color(0xFF0F172A))),
                      const Text('إدارة الوسيط الرقمي وتوريدات المشاريع الكبرى', style: TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                      const SizedBox(height: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                        decoration: BoxDecoration(color: const Color(0xFFEFF6FF), borderRadius: BorderRadius.circular(6), border: Border.all(color: const Color(0xFFBFDBFE))),
                        child: Text('رقم التذكرة: #${req.ticketNumber}', style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF1D4ED8))),
                      ),
                    ],
                  ),
                ),
                const Divider(height: 24),
                _buildVoucherRow('اسم المشتري / الجهة:', req.buyerName),
                _buildVoucherRow('رقم هاتف الاتصال:', req.buyerPhone),
                _buildVoucherRow('موعد التسليم بالموقع:', req.deliveryDate ?? 'فوري عند التعاقد'),
                _buildVoucherRow('المورد المعلن المعتمد:', req.assignedSupplier ?? 'قيد التعيين والمطابقة'),
                _buildVoucherRow('حالة الطلب الحالية:', req.status == 'closed' ? 'مكتمل ومورد ✅' : (req.status == 'contacted' ? 'تم التواصل والتسعير 📞' : 'قيد الانتظار والمطابقة ⏳')),
                const Divider(height: 24),
                const Text('الأصناف والكميات المطلوب توريدها:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF334155))),
                const SizedBox(height: 6),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(color: const Color(0xFFF8FAFC), borderRadius: BorderRadius.circular(8), border: Border.all(color: const Color(0xFFE2E8F0))),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('• المادة الأساسية: ${req.materialType}', style: const TextStyle(fontWeight: FontWeight.bold)),
                      Text('• الكمية المطلوبة: ${req.quantity}', style: const TextStyle(fontWeight: FontWeight.bold)),
                      if (req.itemsBreakdown.isNotEmpty) ...[
                        const SizedBox(height: 6),
                        ...req.itemsBreakdown.map((e) => Text('  $e', style: const TextStyle(fontSize: 12, color: Color(0xFF475569)))),
                      ],
                    ],
                  ),
                ),
                const SizedBox(height: 14),
                if (req.dealValue > 0) ...[
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(color: const Color(0xFFECFDF5), borderRadius: BorderRadius.circular(8), border: Border.all(color: const Color(0xFFA7F3D0))),
                    child: Column(
                      children: [
                        _buildVoucherRow('القيمة التقديرية الإجمالية:', _fmt(req.dealValue)),
                        _buildVoucherRow('عمولة وساطة مقاييس (2%):', _fmt(req.commissionValue)),
                        _buildVoucherRow('صافي استحقاق المورد المعلن:', _fmt(req.dealValue - req.commissionValue)),
                      ],
                    ),
                  ),
                ],
              ],
            ),
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('إغلاق')),
          ElevatedButton.icon(
            onPressed: () {
              if (kIsWeb) {
                web_helper.triggerBrowserPrint();
              } else {
                // On mobile, use Printing package for native print dialog
                Printing.layoutPdf(onLayout: (format) async {
                  final pdf = pw.Document();
                  pdf.addPage(pw.Page(
                    pageFormat: PdfPageFormat.a4,
                    textDirection: pw.TextDirection.rtl,
                    build: (context) => pw.Center(
                      child: pw.Text('سند أمر وساطة توريد - منصة مقاييس\nتذكرة رقم: #${req.ticketNumber}\nالمادة: ${req.materialType}\nالكمية: ${req.quantity}',
                        textDirection: pw.TextDirection.rtl,
                        style: pw.TextStyle(fontSize: 14, fontWeight: pw.FontWeight.bold),
                      ),
                    ),
                  ));
                  return pdf.save();
                });
              }
            },
            icon: const Icon(Icons.print_rounded, size: 16),
            label: const Text('طباعة الأمر 🖨️', style: TextStyle(fontWeight: FontWeight.bold)),
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF0F172A), foregroundColor: Colors.white),
          ),
        ],
      ),
    );
  }

  Widget _buildVoucherRow(String title, String val) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(title, style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
          Text(val, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
        ],
      ),
    );
  }

  void _shareWithSupplier(
    QuoteRequestModel req,
    List<DigitalBrokerSupplier> suppliers, {
    String? customSupplier,
  }) {
    final supplierName = customSupplier ?? req.assignedSupplier ?? 'المورد المعتمد';
    
    String supplierPhone = '';
    for (var s in suppliers) {
      if (s.name == supplierName) {
        supplierPhone = s.phone;
        break;
      }
    }

    final msg = '''السلام عليكم ورحمة الله وبركاته،
منصة مقاييس - الوسيط الرقمي لمواد البناء والتوريدات
إلى الشريك المعلن: $supplierName

📌 تذكرة توريد جديدة برقم: #${req.ticketNumber}
📦 المواد المطلوبة: ${req.materialType}
📊 الكمية المطلوبة: ${req.quantity}
📅 موعد التسليم المتوقع: ${req.deliveryDate ?? 'فوري بالموقع'}

الرجاء مراجعة الكميات وتأكيد الجاهزية وعرض السعر المعتمد للتوريد المباشر.
مع خالص التحية والتقدير،
إدارة الوسيط الرقمي - مقاييس''';

    _sendWhatsApp(phone: supplierPhone, message: msg);
  }

  void _confirmDelete(QuoteRequestModel req) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('حذف طلب الوسيط الرقمي'),
        content: Text('هل أنت متأكد من حذف التذكرة رقم #${req.ticketNumber} الخاصة بـ (${req.materialType})؟'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('إلغاء')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFEF4444), foregroundColor: Colors.white),
            onPressed: () async {
              Navigator.pop(ctx);
              await ref.read(adminDigitalBrokerProvider.notifier).deleteRequest(req.id);
            },
            child: const Text('حذف'),
          ),
        ],
      ),
    );
  }
}
