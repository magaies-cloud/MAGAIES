import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/features/buyer/services/domain/models/service_provider_model.dart';
import 'package:magaiees_marketplace/features/admin/services/application/admin_services_provider.dart';
import 'package:magaiees_marketplace/features/admin/presentation/widgets/admin_error_view.dart';

// --- Modern Theme Colors ---
const Color _kDarkNavy = Color(0xFF0F172A);
const Color _kRoyalBlue = Color(0xFF2563EB);
const Color _kEmerald = Color(0xFF10B981);
const Color _kEmeraldDark = Color(0xFF059669);
const Color _kAmberDark = Color(0xFFD97706);
const Color _kCrimson = Color(0xFFEF4444);
const Color _kPurple = Color(0xFF8B5CF6);
const Color _kBgLight = Color(0xFFF8FAFC);

class AdminServicesScreen extends ConsumerStatefulWidget {
  const AdminServicesScreen({super.key});

  @override
  ConsumerState<AdminServicesScreen> createState() => _AdminServicesScreenState();
}

class _AdminServicesScreenState extends ConsumerState<AdminServicesScreen> {
  int _selectedSegment = 0; // 0: All, 1: Logistics, 2: Engineering, 3: Technicians, 4: Labor, 5: Sponsored
  String _sortBy = 'newest'; // newest, rating_desc, orders_desc, reviews_desc, experience_desc, name_asc
  final _searchCtrl = TextEditingController();
  String _searchQuery = '';
  bool _isGridView = true;

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  String _fmtSdg(double v) => NumberFormat.decimalPattern('ar').format(v);

  String _getCategoryName(String cat, bool isRtl) {
    switch (cat) {
      case 'All':
        return isRtl ? 'الكل' : 'All';
      case 'engineering':
        return isRtl ? 'استشارات وتصميم هندسي' : 'Engineering';
      case 'technicians':
        return isRtl ? 'فنيون ومهنيون' : 'Technicians';
      case 'labor':
        return isRtl ? 'مقاولات وعمالة تنفيذية' : 'Labor & Contracting';
      case 'logistics':
        return isRtl ? 'أساطيل النقل واللوجستيات' : 'Logistics & Fleet';
      default:
        return cat;
    }
  }

  List<ServiceProviderModel> _filterAndSort(
    List<ServiceProviderModel> all,
    AdminServicesInsights? insights,
  ) {
    Iterable<ServiceProviderModel> s = all;

    // Segment filtering
    switch (_selectedSegment) {
      case 1:
        s = s.where((p) => p.categoryId == 'logistics');
        break;
      case 2:
        s = s.where((p) => p.categoryId == 'engineering');
        break;
      case 3:
        s = s.where((p) => p.categoryId == 'technicians');
        break;
      case 4:
        s = s.where((p) => p.categoryId == 'labor');
        break;
      case 5:
        s = s.where((p) => p.rating >= 4.9 || p.categoryId == 'logistics' || p.contractType != 'none');
        break;
      case 6:
        s = s.where((p) => !p.isVerified || p.contractType == 'pending');
        break;
      case 0:
      default:
        break;
    }

    // Text search query
    if (_searchQuery.isNotEmpty) {
      final q = _searchQuery.toLowerCase();
      s = s.where((p) =>
          p.nameAr.contains(q) ||
          p.name.toLowerCase().contains(q) ||
          p.tradeAr.contains(q) ||
          p.trade.toLowerCase().contains(q) ||
          p.locationAr.contains(q) ||
          p.location.toLowerCase().contains(q) ||
          p.phone.contains(q));
    }

    final list = s.toList();

    // Sorting
    switch (_sortBy) {
      case 'rating_desc':
        list.sort((a, b) => b.rating.compareTo(a.rating));
        break;
      case 'orders_desc':
        list.sort((a, b) => b.completedOrdersCount.compareTo(a.completedOrdersCount));
        break;
      case 'reviews_desc':
        list.sort((a, b) => b.reviewsCount.compareTo(a.reviewsCount));
        break;
      case 'experience_desc':
        list.sort((a, b) {
          final expA = int.tryParse(a.experienceYears) ?? 0;
          final expB = int.tryParse(b.experienceYears) ?? 0;
          return expB.compareTo(expA);
        });
        break;
      case 'name_asc':
        list.sort((a, b) => a.nameAr.compareTo(b.nameAr));
        break;
      case 'newest':
      default:
        break;
    }

    return list;
  }

  void _exportCsv(List<ServiceProviderModel> providers) {
    final buffer = StringBuffer();
    buffer.writeln('ID,Name_Ar,Name_En,Category,Trade_Ar,Price_Estimate,Rating,Reviews,Completed_Orders,Contract_Type,Contract_Value,Phone,Location');
    for (final p in providers) {
      buffer.writeln('"${p.id}","${p.nameAr}","${p.name}","${p.categoryId}","${p.tradeAr}","${p.priceEstimate}",${p.rating},${p.reviewsCount},${p.completedOrdersCount},"${p.contractType}",${p.contractValue},"${p.phone}","${p.locationAr}"');
    }

    showDialog(
      context: context,
      builder: (ctx) => Directionality(
        textDirection: TextDirection.rtl,
        child: AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: const Row(
            children: [
              Icon(Icons.download_rounded, color: _kRoyalBlue),
              SizedBox(width: 10),
              Text('تصدير كشف مقدمي الخدمات وعقودهم (CSV)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('تم تجهيز بيانات ${providers.length} مقدم خدمة وشركة لوجستية للمطابقة المحاسبية:', style: const TextStyle(fontSize: 13, color: _kDarkNavy)),
              const SizedBox(height: 12),
              Container(
                constraints: const BoxConstraints(maxHeight: 180),
                width: double.maxFinite,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: Colors.grey.shade300),
                ),
                child: SingleChildScrollView(
                  child: Text(
                    buffer.toString(),
                    style: const TextStyle(fontSize: 11, fontFamily: 'monospace'),
                  ),
                ),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('إغلاق'),
            ),
            FilledButton.icon(
              style: FilledButton.styleFrom(backgroundColor: _kRoyalBlue),
              onPressed: () {
                Navigator.pop(ctx);
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('تم استخراج كشف مقدمي الخدمات بنجاح!'), backgroundColor: _kEmerald),
                );
              },
              icon: const Icon(Icons.check_rounded, size: 18),
              label: const Text('تم'),
            ),
          ],
        ),
      ),
    );
  }

  void _showFinancialContractDialog({
    required BuildContext context,
    required ServiceProviderModel provider,
    required bool isRtl,
  }) {
    showDialog(
      context: context,
      builder: (ctx) => Directionality(
        textDirection: TextDirection.rtl,
        child: Dialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          insetPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
          child: Container(
            width: 620,
            constraints: const BoxConstraints(maxHeight: 700),
            padding: const EdgeInsets.all(24),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Dialog Header
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(colors: [_kRoyalBlue, _kDarkNavy]),
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: const Icon(Icons.receipt_long_rounded, color: Colors.white, size: 26),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              provider.nameAr,
                              style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 18, color: _kDarkNavy),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              'كشف التعاقد المحاسبي وشروط الرعاية الإعلانية والعمولة',
                              style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                            ),
                          ],
                        ),
                      ),
                      IconButton(
                        onPressed: () => Navigator.pop(ctx),
                        icon: const Icon(Icons.close_rounded),
                      ),
                    ],
                  ),
                  const SizedBox(height: 20),

                  // 4 Financial / Operational Metric Cards
                  Row(
                    children: [
                      _buildMiniMetricCard(
                        title: 'نوع التعاقد والعمولة',
                        value: provider.contractType == 'percentage'
                            ? '${provider.contractValue}% عمولة'
                            : (provider.contractType == 'subscription'
                                ? '${_fmtSdg(provider.contractValue)} ج.س شهرياً'
                                : 'بدون عمولة / مباشر'),
                        subtitle: 'نظام التحصيل المالي',
                        color: _kEmerald,
                        icon: Icons.monetization_on_rounded,
                      ),
                      const SizedBox(width: 12),
                      _buildMiniMetricCard(
                        title: 'العمليات والطلبات المنفذة',
                        value: '${provider.completedOrdersCount} عملية',
                        subtitle: 'عبر منصة مقاييس',
                        color: _kRoyalBlue,
                        icon: Icons.task_alt_rounded,
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      _buildMiniMetricCard(
                        title: 'السعر التقديري المعتمد',
                        value: provider.priceEstimate.isNotEmpty ? provider.priceEstimate : 'حسب المشروع',
                        subtitle: 'تسعيرة الخدمة الرسمية',
                        color: _kAmberDark,
                        icon: Icons.price_change_rounded,
                      ),
                      const SizedBox(width: 12),
                      _buildMiniMetricCard(
                        title: 'مستوى التقييم والمراجعات',
                        value: '⭐ ${provider.rating.toStringAsFixed(1)} / 5',
                        subtitle: 'من ${provider.reviewsCount} تقييم عملاء',
                        color: _kPurple,
                        icon: Icons.star_rounded,
                      ),
                    ],
                  ),
                  const SizedBox(height: 20),

                  // Contract & Advertiser Card
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: _kPurple.withValues(alpha: 0.05),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: _kPurple.withValues(alpha: 0.2)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Row(
                          children: [
                            Icon(Icons.verified_user_rounded, color: _kPurple, size: 20),
                            SizedBox(width: 8),
                            Text(
                              'بيانات التوثيق والشراكة الإعلانية',
                              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: _kPurple),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),
                        _buildDetailRow('التخصص والمهنة:', provider.tradeAr.isNotEmpty ? provider.tradeAr : provider.trade),
                        _buildDetailRow('القطاع:', _getCategoryName(provider.categoryId, true)),
                        _buildDetailRow('رقم الاتصال المباشر:', provider.phone.isNotEmpty ? provider.phone : 'غير مسجل'),
                        _buildDetailRow('نطاق التغطية والموقع:', provider.locationAr.isNotEmpty ? provider.locationAr : provider.location),
                        _buildDetailRow('سنوات الخبرة المعتمدة:', '${provider.experienceYears} سنوات'),
                        _buildDetailRow(
                          'حالة التوثيق:',
                          provider.isVerified ? '✅ مزود موثق وشريك معتمد بمنصة مقاييس' : '⏳ قيد المراجعة والتدقيق',
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),

                  // Actions
                  Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      FilledButton(
                        style: FilledButton.styleFrom(
                          backgroundColor: _kDarkNavy,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                        ),
                        onPressed: () => Navigator.pop(ctx),
                        child: const Text('إغلاق الكشف', style: TextStyle(fontWeight: FontWeight.bold)),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: TextStyle(fontSize: 12, color: Colors.grey.shade700, fontWeight: FontWeight.w600)),
          const SizedBox(width: 8),
          Expanded(
            child: Text(value, style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold, color: _kDarkNavy)),
          ),
        ],
      ),
    );
  }

  Widget _buildMiniMetricCard({
    required String title,
    required String value,
    required String subtitle,
    required Color color,
    required IconData icon,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: Colors.grey.shade200),
          boxShadow: [
            BoxShadow(color: color.withValues(alpha: 0.05), blurRadius: 10, offset: const Offset(0, 3)),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(title, style: TextStyle(fontSize: 11, color: Colors.grey.shade600, fontWeight: FontWeight.w600)),
                Icon(icon, color: color, size: 18),
              ],
            ),
            const SizedBox(height: 6),
            Text(value, style: TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: color), maxLines: 1, overflow: TextOverflow.ellipsis),
            const SizedBox(height: 2),
            Text(subtitle, style: TextStyle(fontSize: 10.5, color: Colors.grey.shade500)),
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard({
    required IconData icon,
    required String label,
    required String value,
    required Color color,
    required String badge,
  }) {
    return Container(
      width: 250,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [
          BoxShadow(
            color: color.withValues(alpha: 0.06),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [color, color.withValues(alpha: 0.8)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(14),
              boxShadow: [
                BoxShadow(
                  color: color.withValues(alpha: 0.3),
                  blurRadius: 10,
                  offset: const Offset(0, 3),
                ),
              ],
            ),
            child: Icon(icon, size: 26, color: Colors.white),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      value,
                      style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 22, color: _kDarkNavy, height: 1.1),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: color.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        badge,
                        style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: color),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Text(
                  label,
                  style: TextStyle(fontSize: 12, color: Colors.grey.shade600, fontWeight: FontWeight.w600),
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSegmentTab(int index, String title, IconData icon, Color activeColor, int count) {
    final isSelected = _selectedSegment == index;
    return InkWell(
      onTap: () => setState(() => _selectedSegment = index),
        borderRadius: BorderRadius.circular(16),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 250),
          padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
          decoration: BoxDecoration(
            color: isSelected ? activeColor : Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: isSelected ? activeColor : Colors.grey.shade300,
              width: 1.5,
            ),
            boxShadow: isSelected
                ? [
                    BoxShadow(
                      color: activeColor.withValues(alpha: 0.28),
                      blurRadius: 14,
                      offset: const Offset(0, 4),
                    )
                  ]
                : [],
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                icon,
                size: 20,
                color: isSelected ? Colors.white : Colors.grey.shade600,
              ),
              const SizedBox(width: 8),
              Text(
                title,
                style: TextStyle(
                  fontSize: 13.5,
                  fontWeight: FontWeight.bold,
                  color: isSelected ? Colors.white : Colors.grey.shade700,
                ),
              ),
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: isSelected ? Colors.white.withValues(alpha: 0.25) : Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  count.toString(),
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w900,
                    color: isSelected ? Colors.white : Colors.grey.shade700,
                  ),
                ),
              ),
            ],
          ),
        ),
      );
  }

  Widget _buildProviderGridCard(
    BuildContext context,
    ServiceProviderModel p,
    bool isRtl,
    AdminServicesInsights? insights,
  ) {
    final bool isPending = !p.isVerified || p.contractType == 'pending';
    final bool isLogistics = p.categoryId == 'logistics';
    final bool isSponsored = p.rating >= 4.9 || isLogistics || p.contractType != 'none';

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: isPending ? const Color(0xFFF59E0B) : Colors.grey.shade200, width: isPending ? 1.5 : 1.0),
        boxShadow: [
          BoxShadow(
            color: isPending ? const Color(0xFFF59E0B).withValues(alpha: 0.1) : Colors.black.withValues(alpha: 0.03),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // 1. Top Bar & Avatar Stack
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: isPending
                    ? [const Color(0xFFB45309), const Color(0xFFD97706)]
                    : (isSponsored
                        ? [const Color(0xFF1E3A8A), _kRoyalBlue]
                        : [const Color(0xFF334155), const Color(0xFF64748B)]),
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
            ),
            child: Row(
              children: [
                CircleAvatar(
                  radius: 28,
                  backgroundColor: Colors.white,
                  child: CircleAvatar(
                    radius: 26,
                    backgroundColor: const Color(0xFFF1F5F9),
                    backgroundImage: p.avatarUrl != null && p.avatarUrl!.isNotEmpty
                        ? NetworkImage(p.avatarUrl!)
                        : null,
                    child: p.avatarUrl == null || p.avatarUrl!.isEmpty
                        ? Icon(
                            isLogistics ? Icons.local_shipping_rounded : Icons.engineering_rounded,
                            size: 26,
                            color: _kRoyalBlue,
                          )
                        : null,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              p.nameAr,
                              style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 15, color: Colors.white),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          if (p.isVerified)
                            const Icon(Icons.verified_rounded, color: Colors.amberAccent, size: 18),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Text(
                          isPending
                              ? '⏳ طلب انضمام معلق'
                              : (isSponsored ? '📢 شريك معلن رسمي وموثق' : 'مزود معتمد'),
                          style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // 2. Body Details
          Expanded(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Trade & Category Tag
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(6),
                        decoration: BoxDecoration(
                          color: _getCategoryColor(p.categoryId).withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Icon(_getCategoryIcon(p.categoryId), size: 16, color: _getCategoryColor(p.categoryId)),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          p.tradeAr,
                          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: _kDarkNavy),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      _buildContractBadge(p.contractType, p.contractValue),
                    ],
                  ),
                  const SizedBox(height: 12),

                  // Location & Phone
                  Row(
                    children: [
                      const Icon(Icons.location_on_outlined, size: 15, color: Colors.grey),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          p.locationAr.isNotEmpty ? p.locationAr : p.location,
                          style: TextStyle(fontSize: 12, color: Colors.grey.shade700),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      if (p.phone.isNotEmpty) ...[
                        const SizedBox(width: 8),
                        const Icon(Icons.phone_outlined, size: 14, color: Colors.grey),
                        const SizedBox(width: 4),
                        Text(p.phone, style: TextStyle(fontSize: 11.5, color: Colors.grey.shade600)),
                      ],
                    ],
                  ),
                  const SizedBox(height: 14),

                  // 3 Operational Quick Metrics
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceAround,
                      children: [
                        _buildGridMetric(
                          icon: Icons.star_rounded,
                          color: Colors.amber,
                          value: '${p.rating.toStringAsFixed(1)} (${p.reviewsCount})',
                          label: 'التقييم',
                        ),
                        Container(width: 1, height: 24, color: Colors.grey.shade300),
                        _buildGridMetric(
                          icon: Icons.work_history_rounded,
                          color: _kRoyalBlue,
                          value: '${p.experienceYears} سنوات',
                          label: 'الخبرة',
                        ),
                        Container(width: 1, height: 24, color: Colors.grey.shade300),
                        _buildGridMetric(
                          icon: Icons.task_alt_rounded,
                          color: _kEmerald,
                          value: '${p.completedOrdersCount} عملية',
                          label: 'المنجز',
                        ),
                      ],
                    ),
                  ),
                  const Spacer(),

                  // Pricing Bar
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'التسعيرة التقديرية:',
                        style: TextStyle(fontSize: 12, color: Colors.grey, fontWeight: FontWeight.w600),
                      ),
                      Text(
                        p.priceEstimate.isNotEmpty ? p.priceEstimate : 'حسب المشروع',
                        style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold, color: _kEmeraldDark),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  const Divider(height: 1),
                  const SizedBox(height: 8),

                  // Bottom Action Buttons
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      if (isPending)
                        FilledButton.icon(
                          style: FilledButton.styleFrom(
                            backgroundColor: _kEmerald,
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          ),
                          onPressed: () => _showApproveDialog(context, p, isRtl),
                          icon: const Icon(Icons.check_circle_rounded, size: 15),
                          label: const Text('اعتماد وتفعيل', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                        )
                      else
                        TextButton.icon(
                          onPressed: () => _showFinancialContractDialog(context: context, provider: p, isRtl: isRtl),
                          icon: const Icon(Icons.receipt_long_rounded, size: 16, color: _kPurple),
                          label: const Text('كشف التعاقد', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: _kPurple)),
                          style: TextButton.styleFrom(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            backgroundColor: _kPurple.withValues(alpha: 0.08),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          ),
                        ),
                      Row(
                        children: [
                          IconButton(
                            icon: const Icon(Icons.edit_note_rounded, size: 22, color: _kRoyalBlue),
                            tooltip: 'تعديل بيانات المزود والتعاقد',
                            onPressed: () => _showProviderDialog(context: context, provider: p, isRtl: isRtl),
                            splashRadius: 18,
                            padding: EdgeInsets.zero,
                            constraints: const BoxConstraints(),
                          ),
                          const SizedBox(width: 10),
                          IconButton(
                            icon: const Icon(Icons.delete_outline_rounded, size: 20, color: _kCrimson),
                            tooltip: isPending ? 'رفض الطلب' : 'حذف المزود',
                            onPressed: () => _confirmDelete(context, p, isRtl),
                            splashRadius: 18,
                            padding: EdgeInsets.zero,
                            constraints: const BoxConstraints(),
                          ),
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildGridMetric({required IconData icon, required Color color, required String value, required String label}) {
    return Column(
      children: [
        Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 14, color: color),
            const SizedBox(width: 4),
            Text(value, style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: _kDarkNavy)),
          ],
        ),
        const SizedBox(height: 2),
        Text(label, style: TextStyle(fontSize: 10, color: Colors.grey.shade600)),
      ],
    );
  }

  Color _getCategoryColor(String categoryId) {
    switch (categoryId) {
      case 'logistics':
        return _kAmberDark;
      case 'engineering':
        return _kPurple;
      default:
        return _kRoyalBlue;
    }
  }

  IconData _getCategoryIcon(String categoryId) {
    switch (categoryId) {
      case 'logistics':
        return Icons.local_shipping_rounded;
      case 'engineering':
        return Icons.architecture_rounded;
      default:
        return Icons.engineering_rounded;
    }
  }

  Widget _buildContractBadge(String type, double value) {
    String label = 'بدون عمولة';
    Color color = Colors.grey;
    if (type == 'pending') {
      label = '⏳ بانتظار الاعتماد';
      color = const Color(0xFFD97706);
    } else if (type == 'percentage') {
      label = '${value > 0 ? value.toStringAsFixed(0) : "5"}% عمولة';
      color = _kEmeraldDark;
    } else if (type == 'subscription') {
      label = 'اشتراك شهري';
      color = _kPurple;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Text(
        label,
        style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: color),
      ),
    );
  }

  void _showApproveDialog(BuildContext context, ServiceProviderModel p, bool isRtl) {
    String contractType = p.contractType == 'pending' ? 'none' : p.contractType;
    final valueCtrl = TextEditingController(text: p.contractValue > 0 ? p.contractValue.toString() : '');

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSt) => Directionality(
          textDirection: TextDirection.rtl,
          child: AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            title: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(color: _kEmerald.withValues(alpha: 0.15), shape: BoxShape.circle),
                  child: const Icon(Icons.verified_user_rounded, color: _kEmerald, size: 24),
                ),
                const SizedBox(width: 10),
                const Expanded(
                  child: Text(
                    'اعتماد وتفعيل مقدم الخدمة',
                    style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16),
                  ),
                ),
              ],
            ),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'أنت على وشك اعتماد ملف "${p.nameAr}" (${p.tradeAr}) ونشره في الدليل الرسمي للجمهور.',
                  style: const TextStyle(fontSize: 13, color: _kDarkNavy),
                ),
                const SizedBox(height: 16),
                DropdownButtonFormField<String>(
                  initialValue: contractType,
                  decoration: InputDecoration(
                    labelText: 'نوع التعاقد والعمولة',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  ),
                  items: const [
                    DropdownMenuItem(value: 'none', child: Text('بدون عمولة / مباشر')),
                    DropdownMenuItem(value: 'percentage', child: Text('نسبة مئوية من العمليات (%)')),
                    DropdownMenuItem(value: 'subscription', child: Text('اشتراك شهري ثابت (ج.س)')),
                  ],
                  onChanged: (val) {
                    if (val != null) setSt(() => contractType = val);
                  },
                ),
                if (contractType != 'none') ...[
                  const SizedBox(height: 12),
                  TextField(
                    controller: valueCtrl,
                    keyboardType: const TextInputType.numberWithOptions(decimal: true),
                    decoration: InputDecoration(
                      labelText: contractType == 'percentage' ? 'نسبة المنصة (%)' : 'قيمة الاشتراك الشهري (ج.س)',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    ),
                  ),
                ],
              ],
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: const Text('إلغاء'),
              ),
              FilledButton.icon(
                style: FilledButton.styleFrom(
                  backgroundColor: _kEmerald,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                onPressed: () async {
                  Navigator.pop(ctx);
                  final val = double.tryParse(valueCtrl.text.trim()) ?? 0.0;
                  try {
                    await ref.read(adminServicesProvider.notifier).approveProvider(
                          p.id,
                          contractType: contractType,
                          contractValue: val,
                        );
                    ref.invalidate(adminServicesInsightsProvider);
                    if (context.mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text('تم اعتماد وتفعيل "${p.nameAr}" بنجاح في الدليل المباشر!'),
                          backgroundColor: _kEmerald,
                        ),
                      );
                    }
                  } catch (e) {
                    if (context.mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(content: Text('فشل اعتماد المزود: $e'), backgroundColor: Colors.red),
                      );
                    }
                  }
                },
                icon: const Icon(Icons.check_circle_rounded, size: 16),
                label: const Text('تأكيد الاعتماد والنشر', style: TextStyle(fontWeight: FontWeight.bold)),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(adminServicesProvider);
    final insightsAsync = ref.watch(adminServicesInsightsProvider);
    final insights = insightsAsync.valueOrNull;
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    final filteredProviders = _filterAndSort(state.providers, insights);
    final isMobile = MediaQuery.of(context).size.width < 800;

    return Scaffold(
      backgroundColor: _kBgLight,
      body: state.isLoading && state.providers.isEmpty
          ? const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  CircularProgressIndicator(color: _kRoyalBlue, strokeWidth: 3),
                  SizedBox(height: 16),
                  Text('جاري تحميل بيانات مقدمي الخدمات واللوجستيات...', style: TextStyle(color: _kDarkNavy, fontWeight: FontWeight.bold)),
                ],
              ),
            )
          : state.providers.isEmpty && state.error != null
              ? AdminErrorView(
                  error: state.error,
                  onRetry: () {
                    ref.invalidate(adminServicesProvider);
                    ref.invalidate(adminServicesInsightsProvider);
                  },
                )
              : CustomScrollView(
                  slivers: [
                    SliverPadding(
                      padding: const EdgeInsets.fromLTRB(20, 20, 20, 16),
                      sliver: SliverList(
                        delegate: SliverChildListDelegate([
                          // --- 1. Top Screen Header ---
                          if (isMobile)
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.all(12),
                                      decoration: BoxDecoration(
                                        gradient: const LinearGradient(colors: [_kDarkNavy, _kRoyalBlue]),
                                        borderRadius: BorderRadius.circular(16),
                                        boxShadow: [
                                          BoxShadow(color: _kRoyalBlue.withValues(alpha: 0.25), blurRadius: 12, offset: const Offset(0, 4)),
                                        ],
                                      ),
                                      child: const Icon(Icons.handshake_rounded, color: Colors.white, size: 28),
                                    ),
                                    const SizedBox(width: 16),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          const Text('إدارة مقدمي الخدمات واللوجستيات', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900, color: _kDarkNavy)),
                                          const SizedBox(height: 4),
                                          Text('إدارة أساطيل النقل، المقاولين، والاستشارات وعقود الرعاية.', style: TextStyle(color: Colors.grey.shade600, fontSize: 13, fontWeight: FontWeight.w500)),
                                        ],
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 16),
                                Wrap(
                                  spacing: 12,
                                  runSpacing: 12,
                                  children: [
                                    OutlinedButton.icon(
                                      style: OutlinedButton.styleFrom(
                                        foregroundColor: _kDarkNavy,
                                        side: BorderSide(color: Colors.grey.shade300, width: 1.5),
                                        padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                      ),
                                      onPressed: () => _exportCsv(filteredProviders),
                                      icon: const Icon(Icons.file_download_outlined, size: 20),
                                      label: const Text('تصدير كشف CSV', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                                    ),
                                    FilledButton.icon(
                                      style: FilledButton.styleFrom(
                                        backgroundColor: _kEmerald,
                                        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 15),
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                        elevation: 3,
                                        shadowColor: _kEmerald.withValues(alpha: 0.4),
                                      ),
                                      onPressed: () => _showProviderDialog(context: context, isRtl: isRtl),
                                      icon: const Icon(Icons.add_circle_outline_rounded, size: 22),
                                      label: const Text('إضافة مقدم خدمة', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 15)),
                                    ),
                                  ],
                                ),
                              ],
                            )
                          else
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    gradient: const LinearGradient(colors: [_kDarkNavy, _kRoyalBlue]),
                                    borderRadius: BorderRadius.circular(16),
                                    boxShadow: [
                                      BoxShadow(color: _kRoyalBlue.withValues(alpha: 0.25), blurRadius: 12, offset: const Offset(0, 4)),
                                    ],
                                  ),
                                  child: const Icon(Icons.handshake_rounded, color: Colors.white, size: 28),
                                ),
                                const SizedBox(width: 16),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      const Text('إدارة مقدمي الخدمات واللوجستيات (Services & Fleet Core)', style: TextStyle(fontSize: 26, fontWeight: FontWeight.w900, color: _kDarkNavy)),
                                      const SizedBox(height: 4),
                                      Text('التحكم بأسطول النقل الثقيل، المقاولين، المكاتب الهندسية، وعقود الرعاية الإعلانية والعمولات.', style: TextStyle(color: Colors.grey.shade600, fontSize: 13, fontWeight: FontWeight.w500)),
                                    ],
                                  ),
                                ),
                                OutlinedButton.icon(
                                  style: OutlinedButton.styleFrom(
                                    foregroundColor: _kDarkNavy,
                                    side: BorderSide(color: Colors.grey.shade300, width: 1.5),
                                    padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                  ),
                                  onPressed: () => _exportCsv(filteredProviders),
                                  icon: const Icon(Icons.file_download_outlined, size: 20),
                                  label: const Text('تصدير كشف CSV', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                                ),
                                const SizedBox(width: 12),
                                FilledButton.icon(
                                  style: FilledButton.styleFrom(
                                    backgroundColor: _kEmerald,
                                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 15),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                    elevation: 3,
                                    shadowColor: _kEmerald.withValues(alpha: 0.4),
                                  ),
                                  onPressed: () => _showProviderDialog(context: context, isRtl: isRtl),
                                  icon: const Icon(Icons.add_circle_outline_rounded, size: 22),
                                  label: const Text('إضافة مقدم خدمة جديد', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 15)),
                                ),
                              ],
                            ),
                          const SizedBox(height: 20),

                          // --- 2. Live Accounting & Fleet KPI Row (5 Cards) ---
                          SingleChildScrollView(
                            scrollDirection: Axis.horizontal,
                            child: Row(
                              children: [
                                _buildStatCard(
                                  icon: Icons.monetization_on_rounded,
                                  label: 'عوائد النقل واللوجستيات المعتمدة',
                                  value: insights != null ? ('${_fmtSdg(insights.totalLogisticsFees)} ج.س') : '...',
                                  color: _kEmerald,
                                  badge: insights != null ? ('${insights.logisticsOrdersCount} عملية توريد') : 'لوجستيات',
                                ),
                                const SizedBox(width: 12),
                                _buildStatCard(
                                  icon: Icons.groups_rounded,
                                  label: 'إجمالي مقدمي الخدمات',
                                  value: '${state.providers.length} مزود وشركة',
                                  color: _kRoyalBlue,
                                  badge: '100% موثق',
                                ),
                                const SizedBox(width: 12),
                                _buildStatCard(
                                  icon: Icons.local_shipping_rounded,
                                  label: 'أساطيل النقل واللوجستيات',
                                  value: insights != null ? ('${insights.logisticsCount} أساطيل معتمدة') : '...',
                                  color: _kAmberDark,
                                  badge: 'تريلات وقلابات',
                                ),
                                const SizedBox(width: 12),
                                _buildStatCard(
                                  icon: Icons.architecture_rounded,
                                  label: 'المكاتب والاستشارات الهندسية',
                                  value: insights != null ? ('${insights.engineeringCount} مكاتب معتمدة') : '...',
                                  color: _kPurple,
                                  badge: 'تصميم وإشراف',
                                ),
                                const SizedBox(width: 12),
                                _buildStatCard(
                                  icon: Icons.campaign_rounded,
                                  label: 'مقدمو الخدمات المعلنون',
                                  value: insights != null ? ('${insights.sponsoredCount} شريك معلن') : '...',
                                  color: _kCrimson,
                                  badge: 'رعاية مميزة',
                                ),
                                const SizedBox(width: 12),
                                _buildStatCard(
                                  icon: Icons.pending_actions_rounded,
                                  label: 'طلبات الانضمام المعلقة',
                                  value: '${insights?.pendingApprovalCount ?? 0} طلب',
                                  color: const Color(0xFFD97706),
                                  badge: (insights?.pendingApprovalCount ?? 0) > 0 ? 'تحتاج مراجعة' : 'مكتمل',
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 20),

                          // --- 3. Segment Navigation Tabs ---
                          SingleChildScrollView(
                            scrollDirection: Axis.horizontal,
                            child: Row(
                              children: [
                                _buildSegmentTab(0, '🌐 كل المزودين والشركات', Icons.all_inclusive_rounded, _kDarkNavy, state.providers.length),
                                const SizedBox(width: 14),
                                _buildSegmentTab(6, '⏳ طلبات الانضمام المعلقة', Icons.pending_actions_rounded, const Color(0xFFD97706), insights?.pendingApprovalCount ?? 0),
                                const SizedBox(width: 14),
                                _buildSegmentTab(1, '🚚 أساطيل النقل واللوجستيات', Icons.local_shipping_rounded, _kAmberDark, insights?.logisticsCount ?? 0),
                                const SizedBox(width: 14),
                                _buildSegmentTab(2, '🏗️ الهندسة والتصميم', Icons.architecture_rounded, _kRoyalBlue, insights?.engineeringCount ?? 0),
                                const SizedBox(width: 14),
                                _buildSegmentTab(3, '⚡ الفنيون والمهنيون', Icons.handyman_rounded, _kPurple, insights?.techniciansCount ?? 0),
                                const SizedBox(width: 14),
                                _buildSegmentTab(4, '🔨 المقاولات والعمالة', Icons.construction_rounded, _kEmerald, insights?.laborCount ?? 0),
                                const SizedBox(width: 14),
                                _buildSegmentTab(5, '📢 شركاء برعاية إعلانية', Icons.campaign_rounded, _kCrimson, insights?.sponsoredCount ?? 0),
                              ],
                            ),
                          ),
                          const SizedBox(height: 18),

                          // --- 4. Search, Filter & Sort Bar ---
                          if (isMobile)
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              children: [
                                TextField(
                                  controller: _searchCtrl,
                                  onChanged: (v) => setState(() => _searchQuery = v.trim()),
                                  decoration: InputDecoration(
                                    hintText: 'ابحث عن مزود، تخصص، موقع، أو هاتف...',
                                    hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
                                    prefixIcon: const Icon(Icons.search_rounded, color: _kRoyalBlue),
                                    suffixIcon: _searchQuery.isNotEmpty
                                        ? IconButton(
                                            icon: const Icon(Icons.clear_rounded, size: 18),
                                            onPressed: () {
                                              _searchCtrl.clear();
                                              setState(() => _searchQuery = '');
                                            },
                                          )
                                        : null,
                                    filled: true,
                                    fillColor: Colors.white,
                                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade200)),
                                    enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade200)),
                                    focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: _kRoyalBlue, width: 1.5)),
                                  ),
                                ),
                                const SizedBox(height: 12),
                                Row(
                                  children: [
                                    Expanded(
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 12),
                                        decoration: BoxDecoration(
                                          color: Colors.white,
                                          borderRadius: BorderRadius.circular(12),
                                          border: Border.all(color: Colors.grey.shade200),
                                        ),
                                        child: DropdownButtonHideUnderline(
                                          child: DropdownButton<String>(
                                            isExpanded: true,
                                            value: _sortBy,
                                            icon: const Icon(Icons.sort_rounded, color: _kRoyalBlue, size: 20),
                                            items: const [
                                              DropdownMenuItem(value: 'newest', child: Text('الأحدث انضماماً', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold))),
                                              DropdownMenuItem(value: 'rating_desc', child: Text('الأعلى تقييماً (⭐)', style: TextStyle(fontSize: 13))),
                                              DropdownMenuItem(value: 'orders_desc', child: Text('الأكثر إنجازاً للعمليات', style: TextStyle(fontSize: 13))),
                                              DropdownMenuItem(value: 'reviews_desc', child: Text('الأكثر مراجعات', style: TextStyle(fontSize: 13))),
                                              DropdownMenuItem(value: 'experience_desc', child: Text('الأكثر خبرة (سنوات)', style: TextStyle(fontSize: 13))),
                                              DropdownMenuItem(value: 'name_asc', child: Text('أبجدياً (أ-ي)', style: TextStyle(fontSize: 13))),
                                            ],
                                            onChanged: (val) {
                                              if (val != null) setState(() => _sortBy = val);
                                            },
                                          ),
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 12),
                                    Container(
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFF1F5F9),
                                        borderRadius: BorderRadius.circular(12),
                                        border: Border.all(color: Colors.grey.shade200),
                                      ),
                                      child: Row(
                                        children: [
                                          IconButton(
                                            icon: Icon(Icons.grid_view_rounded, color: _isGridView ? _kRoyalBlue : Colors.grey.shade500, size: 20),
                                            tooltip: 'عرض بطاقات شبكية',
                                            onPressed: () => setState(() => _isGridView = true),
                                          ),
                                          IconButton(
                                            icon: Icon(Icons.table_rows_rounded, color: !_isGridView ? _kRoyalBlue : Colors.grey.shade500, size: 20),
                                            tooltip: 'عرض جدول صفوف',
                                            onPressed: () => setState(() => _isGridView = false),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            )
                          else
                            Row(
                              children: [
                                Expanded(
                                  flex: 3,
                                  child: TextField(
                                    controller: _searchCtrl,
                                    onChanged: (v) => setState(() => _searchQuery = v.trim()),
                                    decoration: InputDecoration(
                                      hintText: 'ابحث عن مزود، تخصص، موقع جغرافي، أو هاتف الاتصال...',
                                      hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
                                      prefixIcon: const Icon(Icons.search_rounded, color: _kRoyalBlue),
                                      suffixIcon: _searchQuery.isNotEmpty
                                          ? IconButton(
                                              icon: const Icon(Icons.clear_rounded, size: 18),
                                              onPressed: () {
                                                _searchCtrl.clear();
                                                setState(() => _searchQuery = '');
                                              },
                                            )
                                          : null,
                                      filled: true,
                                      fillColor: Colors.white,
                                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade200)),
                                      enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade200)),
                                      focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: _kRoyalBlue, width: 1.5)),
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 12),
                                  decoration: BoxDecoration(
                                    color: Colors.white,
                                    borderRadius: BorderRadius.circular(12),
                                    border: Border.all(color: Colors.grey.shade200),
                                  ),
                                  child: DropdownButtonHideUnderline(
                                    child: DropdownButton<String>(
                                      value: _sortBy,
                                      icon: const Icon(Icons.sort_rounded, color: _kRoyalBlue, size: 20),
                                      items: const [
                                        DropdownMenuItem(value: 'newest', child: Text('الأحدث انضماماً', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold))),
                                        DropdownMenuItem(value: 'rating_desc', child: Text('الأعلى تقييماً (⭐)', style: TextStyle(fontSize: 13))),
                                        DropdownMenuItem(value: 'orders_desc', child: Text('الأكثر إنجازاً للعمليات', style: TextStyle(fontSize: 13))),
                                        DropdownMenuItem(value: 'reviews_desc', child: Text('الأكثر مراجعات', style: TextStyle(fontSize: 13))),
                                        DropdownMenuItem(value: 'experience_desc', child: Text('الأكثر خبرة (سنوات)', style: TextStyle(fontSize: 13))),
                                        DropdownMenuItem(value: 'name_asc', child: Text('أبجدياً (أ-ي)', style: TextStyle(fontSize: 13))),
                                      ],
                                      onChanged: (val) {
                                        if (val != null) setState(() => _sortBy = val);
                                      },
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Container(
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFF1F5F9),
                                    borderRadius: BorderRadius.circular(12),
                                    border: Border.all(color: Colors.grey.shade200),
                                  ),
                                  child: Row(
                                    children: [
                                      IconButton(
                                        icon: Icon(Icons.grid_view_rounded, color: _isGridView ? _kRoyalBlue : Colors.grey.shade500, size: 20),
                                        tooltip: 'عرض بطاقات شبكية',
                                        onPressed: () => setState(() => _isGridView = true),
                                      ),
                                      IconButton(
                                        icon: Icon(Icons.table_rows_rounded, color: !_isGridView ? _kRoyalBlue : Colors.grey.shade500, size: 20),
                                        tooltip: 'عرض جدول صفوف',
                                        onPressed: () => setState(() => _isGridView = false),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          const SizedBox(height: 16),
                        ]),
                      ),
                    ),

                    // --- 5. Provider Content: Grid View vs Table View ---
                    if (filteredProviders.isEmpty)
                      SliverFillRemaining(
                        hasScrollBody: false,
                        child: Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Container(
                                padding: const EdgeInsets.all(20),
                                decoration: BoxDecoration(
                                  color: _kRoyalBlue.withValues(alpha: 0.08),
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(Icons.search_off_rounded, size: 56, color: _kRoyalBlue),
                              ),
                              const SizedBox(height: 16),
                              const Text('لا يوجد مقدمو خدمات مطابقون للبحث أو الفلتر المختار', style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: _kDarkNavy)),
                              const SizedBox(height: 6),
                              Text('جرّب تعديل كلمات البحث أو مسح الفلتر', style: TextStyle(fontSize: 13, color: Colors.grey.shade600)),
                              const SizedBox(height: 16),
                              FilledButton.icon(
                                onPressed: () => _showProviderDialog(context: context, isRtl: isRtl),
                                icon: const Icon(Icons.add_circle_outline_rounded, size: 20),
                                label: const Text('إضافة مقدم خدمة جديد', style: TextStyle(fontWeight: FontWeight.bold)),
                                style: FilledButton.styleFrom(
                                  backgroundColor: _kEmerald,
                                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                ),
                              ),
                            ],
                          ),
                        ),
                      )
                    else if (_isGridView)
                      SliverPadding(
                        padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
                        sliver: SliverGrid(
                          gridDelegate: const SliverGridDelegateWithMaxCrossAxisExtent(
                            maxCrossAxisExtent: 360,
                            mainAxisSpacing: 16,
                            crossAxisSpacing: 16,
                            childAspectRatio: 0.82,
                          ),
                          delegate: SliverChildBuilderDelegate(
                            (context, index) => _buildProviderGridCard(context, filteredProviders[index], isRtl, insights),
                            childCount: filteredProviders.length,
                          ),
                        ),
                      )
                    else
                      SliverPadding(
                        padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
                        sliver: SliverToBoxAdapter(
                          child: Container(
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(18),
                              border: Border.all(color: Colors.grey.shade200),
                              boxShadow: [
                                BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 12, offset: const Offset(0, 4)),
                              ],
                            ),
                            clipBehavior: Clip.antiAlias,
                            child: Column(
                              children: [
                                // Table Header
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFF8FAFC),
                                    border: Border(bottom: BorderSide(color: Colors.grey.shade200)),
                                  ),
                                  child: const Row(
                                    children: [
                                      Expanded(flex: 3, child: Text('مقدم الخدمة والجهة الراعية', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _kDarkNavy))),
                                      Expanded(flex: 2, child: Text('القطاع والتخصص', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _kDarkNavy))),
                                      Expanded(flex: 2, child: Text('السعر التقديري', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _kDarkNavy))),
                                      Expanded(flex: 2, child: Text('التقييم والمراجعات', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _kDarkNavy))),
                                      Expanded(flex: 2, child: Text('نوع التعاقد', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _kDarkNavy))),
                                      Expanded(flex: 2, child: Text('الموقع والهاتف', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _kDarkNavy))),
                                      SizedBox(width: 150, child: Center(child: Text('الإجراءات', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _kDarkNavy)))),
                                    ],
                                  ),
                                ),
                                // Table Rows
                                ListView.separated(
                                  shrinkWrap: true,
                                  physics: const NeverScrollableScrollPhysics(),
                                  itemCount: filteredProviders.length,
                                  separatorBuilder: (context, index) => Divider(height: 1, color: Colors.grey.shade100),
                                  itemBuilder: (context, index) {
                                    final p = filteredProviders[index];
                                    final isPending = !p.isVerified || p.contractType == 'pending';
                                    final isSponsored = p.rating >= 4.9 || p.categoryId == 'logistics' || p.contractType != 'none';

                                    return Padding(
                                      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
                                      child: Row(
                                        children: [
                                          // Provider Info
                                          Expanded(
                                            flex: 3,
                                            child: Row(
                                              children: [
                                                CircleAvatar(
                                                  radius: 20,
                                                  backgroundColor: const Color(0xFFF1F5F9),
                                                  backgroundImage: p.avatarUrl != null && p.avatarUrl!.isNotEmpty
                                                      ? NetworkImage(p.avatarUrl!)
                                                      : null,
                                                  child: p.avatarUrl == null || p.avatarUrl!.isEmpty
                                                      ? Icon(
                                                          p.categoryId == 'logistics' ? Icons.local_shipping_rounded : Icons.person_rounded,
                                                          size: 18,
                                                          color: _kRoyalBlue,
                                                        )
                                                      : null,
                                                ),
                                                const SizedBox(width: 12),
                                                Expanded(
                                                  child: Column(
                                                    crossAxisAlignment: CrossAxisAlignment.start,
                                                    children: [
                                                      Text(p.nameAr, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: _kDarkNavy)),
                                                      const SizedBox(height: 2),
                                                      Container(
                                                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                                                        decoration: BoxDecoration(
                                                          color: isPending
                                                              ? Colors.amber.shade100
                                                              : (isSponsored ? _kPurple.withValues(alpha: 0.08) : _kRoyalBlue.withValues(alpha: 0.08)),
                                                          borderRadius: BorderRadius.circular(6),
                                                        ),
                                                        child: Text(
                                                          isPending
                                                              ? '⏳ بانتظار الاعتماد'
                                                              : (isSponsored ? '📢 شريك معلن رسمي' : 'مزود معتمد'),
                                                          style: TextStyle(
                                                            fontSize: 10,
                                                            fontWeight: FontWeight.bold,
                                                            color: isPending
                                                                ? const Color(0xFFB45309)
                                                                : (isSponsored ? _kPurple : _kRoyalBlue),
                                                          ),
                                                        ),
                                                      ),
                                                    ],
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ),
                                          // Category & Trade
                                          Expanded(
                                            flex: 2,
                                            child: Column(
                                              crossAxisAlignment: CrossAxisAlignment.start,
                                              children: [
                                                Text(_getCategoryName(p.categoryId, true), style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold, color: _kDarkNavy)),
                                                if (p.tradeAr.isNotEmpty)
                                                  Text(p.tradeAr, style: TextStyle(fontSize: 11, color: Colors.grey.shade600), maxLines: 1, overflow: TextOverflow.ellipsis),
                                              ],
                                            ),
                                          ),
                                          // Price Estimate
                                          Expanded(
                                            flex: 2,
                                            child: Text(
                                              p.priceEstimate.isNotEmpty ? p.priceEstimate : 'حسب المشروع',
                                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: _kEmeraldDark),
                                              maxLines: 2,
                                              overflow: TextOverflow.ellipsis,
                                            ),
                                          ),
                                          // Rating & Reviews
                                          Expanded(
                                            flex: 2,
                                            child: Row(
                                              children: [
                                                const Icon(Icons.star_rounded, size: 16, color: Colors.amber),
                                                const SizedBox(width: 4),
                                                Text(p.rating.toStringAsFixed(1), style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: _kDarkNavy)),
                                                Text(' (${p.reviewsCount})', style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
                                              ],
                                            ),
                                          ),
                                          // Contract Badge
                                          Expanded(
                                            flex: 2,
                                            child: _buildContractBadge(p.contractType, p.contractValue),
                                          ),
                                          // Location & Phone
                                          Expanded(
                                            flex: 2,
                                            child: Column(
                                              crossAxisAlignment: CrossAxisAlignment.start,
                                              children: [
                                                Text(p.locationAr.isNotEmpty ? p.locationAr : p.location, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: _kDarkNavy)),
                                                if (p.phone.isNotEmpty)
                                                  Text(p.phone, style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                                              ],
                                            ),
                                          ),
                                          // Actions
                                          SizedBox(
                                            width: 150,
                                            child: Row(
                                              mainAxisAlignment: MainAxisAlignment.center,
                                              children: [
                                                if (isPending)
                                                  IconButton(
                                                    icon: const Icon(Icons.verified_rounded, color: _kEmerald, size: 22),
                                                    tooltip: 'اعتماد وتفعيل التعاقد',
                                                    onPressed: () => _showApproveDialog(context, p, isRtl),
                                                    splashRadius: 18,
                                                  ),
                                                IconButton(
                                                  icon: const Icon(Icons.receipt_long_rounded, color: _kPurple, size: 20),
                                                  tooltip: 'كشف التعاقد والحساب',
                                                  onPressed: () => _showFinancialContractDialog(context: context, provider: p, isRtl: isRtl),
                                                  splashRadius: 18,
                                                ),
                                                IconButton(
                                                  icon: const Icon(Icons.edit_note_rounded, color: _kRoyalBlue, size: 22),
                                                  tooltip: 'تعديل المزود والتعاقد',
                                                  onPressed: () => _showProviderDialog(context: context, provider: p, isRtl: isRtl),
                                                  splashRadius: 18,
                                                ),
                                                IconButton(
                                                  icon: const Icon(Icons.delete_outline_rounded, color: _kCrimson, size: 20),
                                                  tooltip: 'حذف المزود',
                                                  onPressed: () => _confirmDelete(context, p, isRtl),
                                                  splashRadius: 18,
                                                ),
                                              ],
                                            ),
                                          ),
                                        ],
                                      ),
                                    );
                                  },
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
    );
  }

  Future<void> _showProviderDialog({
    required BuildContext context,
    ServiceProviderModel? provider,
    required bool isRtl,
  }) async {
    final isEditing = provider != null;

    final nameController = TextEditingController(text: provider?.name ?? '');
    final nameArController = TextEditingController(text: provider?.nameAr ?? '');
    final tradeController = TextEditingController(text: provider?.trade ?? '');
    final tradeArController = TextEditingController(text: provider?.tradeAr ?? '');
    final ratingController = TextEditingController(text: provider != null ? provider.rating.toStringAsFixed(1) : '5.0');
    final reviewsController = TextEditingController(text: provider != null ? provider.reviewsCount.toString() : '10');
    final experienceController = TextEditingController(text: provider?.experienceYears ?? '5');
    final priceController = TextEditingController(text: provider?.priceEstimate ?? 'حسب نوع المشروع');
    final phoneController = TextEditingController(text: provider?.phone ?? '');
    final locationController = TextEditingController(text: provider?.location ?? '');
    final locationArController = TextEditingController(text: provider?.locationAr ?? '');
    final contractValueController = TextEditingController(text: provider != null ? provider.contractValue.toString() : '0.0');
    final completedOrdersController = TextEditingController(text: provider != null ? provider.completedOrdersCount.toString() : '0');

    String selectedCategory = provider?.categoryId ?? 'logistics';
    if (!['engineering', 'technicians', 'labor', 'logistics'].contains(selectedCategory)) {
      selectedCategory = 'logistics';
    }

    String selectedContractType = provider?.contractType ?? 'none';
    if (!['none', 'percentage', 'subscription'].contains(selectedContractType)) {
      selectedContractType = 'none';
    }

    Uint8List? avatarBytes;
    String? avatarExtension;
    final String? existingAvatarUrl = provider?.avatarUrl;
    final formKey = GlobalKey<FormState>();
    bool isSaving = false;

    await showDialog(
      context: context,
      barrierDismissible: false,
      builder: (dialogCtx) {
        return StatefulBuilder(
          builder: (dialogCtx, setStateDialog) {
            return Directionality(
              textDirection: TextDirection.rtl,
              child: Dialog(
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
                insetPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
                clipBehavior: Clip.antiAlias,
                child: Container(
                  width: 720,
                  constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * 0.9),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      // Header
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 18),
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            colors: isEditing
                                ? [const Color(0xFF1E3A8A), _kRoyalBlue]
                                : [const Color(0xFF065F46), _kEmerald],
                          ),
                        ),
                        child: Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(10),
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.2),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Icon(
                                isEditing ? Icons.edit_note_rounded : Icons.add_circle_outline_rounded,
                                color: Colors.white,
                                size: 26,
                              ),
                            ),
                            const SizedBox(width: 14),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    isEditing ? 'تعديل بيانات مقدم الخدمة وشروط التعاقد' : 'إضافة مقدم خدمة أو أسطول نقل جديد',
                                    style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 18, color: Colors.white),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    isEditing
                                        ? 'تحديث بيانات الاعتماد، التسعيرة التقديرية، والعمولات المحاسبية'
                                        : 'تسجيل مقاول، مهندس، أو أسطول نقل جديد للربط المباشر مع السوق',
                                    style: TextStyle(color: Colors.white.withValues(alpha: 0.85), fontSize: 12),
                                  ),
                                ],
                              ),
                            ),
                            IconButton(
                              icon: const Icon(Icons.close, color: Colors.white),
                              onPressed: isSaving ? null : () => Navigator.pop(dialogCtx),
                            ),
                          ],
                        ),
                      ),

                      // Form Body
                      Expanded(
                        child: SingleChildScrollView(
                          padding: const EdgeInsets.all(24),
                          child: Form(
                            key: formKey,
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                // Avatar Picker
                                Center(
                                  child: GestureDetector(
                                    onTap: () async {
                                      final picker = ImagePicker();
                                      final picked = await picker.pickImage(source: ImageSource.gallery, imageQuality: 60, maxWidth: 800);
                                      if (picked != null) {
                                        final bytes = await picked.readAsBytes();
                                        final ext = picked.name.split('.').last;
                                        setStateDialog(() {
                                          avatarBytes = bytes;
                                          avatarExtension = ext;
                                        });
                                      }
                                    },
                                    child: Stack(
                                      children: [
                                        CircleAvatar(
                                          radius: 46,
                                          backgroundColor: Colors.grey.shade200,
                                          backgroundImage: avatarBytes != null
                                              ? MemoryImage(avatarBytes!)
                                              : (existingAvatarUrl != null && existingAvatarUrl.isNotEmpty
                                                  ? NetworkImage(existingAvatarUrl)
                                                  : null) as ImageProvider?,
                                          child: avatarBytes == null && (existingAvatarUrl == null || existingAvatarUrl.isEmpty)
                                              ? Icon(Icons.add_a_photo, size: 36, color: Colors.grey.shade400)
                                              : null,
                                        ),
                                        Positioned(
                                          bottom: 0,
                                          right: 0,
                                          child: Container(
                                            padding: const EdgeInsets.all(4),
                                            decoration: const BoxDecoration(color: Colors.white, shape: BoxShape.circle),
                                            child: Container(
                                              padding: const EdgeInsets.all(6),
                                              decoration: const BoxDecoration(color: _kRoyalBlue, shape: BoxShape.circle),
                                              child: const Icon(Icons.camera_alt, size: 14, color: Colors.white),
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                                const SizedBox(height: 20),

                                // Basic Info Section
                                _buildSectionTitle('البيانات الأساسية والهوية المهنية', Icons.person_outline_rounded),
                                Card(
                                  elevation: 0,
                                  color: const Color(0xFFF8FAFC),
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    side: BorderSide(color: Colors.grey.shade200),
                                  ),
                                  child: Padding(
                                    padding: const EdgeInsets.all(16),
                                    child: Column(
                                      children: [
                                        Row(
                                          children: [
                                            Expanded(
                                              child: _buildTextField(
                                                controller: nameArController,
                                                label: 'اسم المزود / الشركة (بالعربية) *',
                                                validator: (v) => (v == null || v.trim().isEmpty) ? 'الرجاء إدخال الاسم بالعربية' : null,
                                              ),
                                            ),
                                            const SizedBox(width: 12),
                                            Expanded(
                                              child: _buildTextField(
                                                controller: nameController,
                                                label: 'الاسم (بالإنجليزية)',
                                              ),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 12),
                                        Row(
                                          children: [
                                            Expanded(
                                              child: _buildTextField(
                                                controller: tradeArController,
                                                label: 'المهنة / التخصص الدقيق (بالعربية)',
                                                hintText: 'مثال: قلابات 20 متر لنقل الرمل والحجر',
                                              ),
                                            ),
                                            const SizedBox(width: 12),
                                            Expanded(
                                              child: _buildTextField(
                                                controller: tradeController,
                                                label: 'التخصص (بالإنجليزية)',
                                              ),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 12),
                                        DropdownButtonFormField<String>(
                                          initialValue: selectedCategory,
                                          decoration: InputDecoration(
                                            labelText: 'القطاع والتصنيف الرئيسي *',
                                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                            filled: true,
                                            fillColor: Colors.white,
                                            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                                          ),
                                          items: [
                                            DropdownMenuItem(value: 'logistics', child: Text(_getCategoryName('logistics', true))),
                                            DropdownMenuItem(value: 'engineering', child: Text(_getCategoryName('engineering', true))),
                                            DropdownMenuItem(value: 'technicians', child: Text(_getCategoryName('technicians', true))),
                                            DropdownMenuItem(value: 'labor', child: Text(_getCategoryName('labor', true))),
                                          ],
                                          onChanged: (v) {
                                            if (v != null) setStateDialog(() => selectedCategory = v);
                                          },
                                        ),
                                        const SizedBox(height: 12),
                                        Row(
                                          children: [
                                            Expanded(
                                              child: _buildTextField(
                                                controller: phoneController,
                                                label: 'رقم الهاتف المباشر',
                                                keyboardType: TextInputType.phone,
                                              ),
                                            ),
                                            const SizedBox(width: 12),
                                            Expanded(
                                              child: _buildTextField(
                                                controller: locationArController,
                                                label: 'نطاق التغطية والموقع',
                                                hintText: 'مثال: السجانة والخرطوم وبحري',
                                              ),
                                            ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                                const SizedBox(height: 20),

                                // Finance & Pricing Section
                                _buildSectionTitle('التسعيرة والعقود المحاسبية', Icons.attach_money_rounded),
                                Card(
                                  elevation: 0,
                                  color: const Color(0xFFF8FAFC),
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    side: BorderSide(color: Colors.grey.shade200),
                                  ),
                                  child: Padding(
                                    padding: const EdgeInsets.all(16),
                                    child: Column(
                                      children: [
                                        _buildTextField(
                                          controller: priceController,
                                          label: 'التسعيرة التقديرية (الوصف والسعر) *',
                                          hintText: 'مثال: 50,000 ج.س / قلاب 20 متر أو حسب المشروع',
                                        ),
                                        const SizedBox(height: 12),
                                        Row(
                                          children: [
                                            Expanded(
                                              child: DropdownButtonFormField<String>(
                                                initialValue: selectedContractType,
                                                decoration: InputDecoration(
                                                  labelText: 'نوع التعاقد والعمولة',
                                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                                  filled: true,
                                                  fillColor: Colors.white,
                                                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                                                ),
                                                items: const [
                                                  DropdownMenuItem(value: 'none', child: Text('بدون عمولة / مباشر')),
                                                  DropdownMenuItem(value: 'percentage', child: Text('نسبة مئوية من العمليات (%)')),
                                                  DropdownMenuItem(value: 'subscription', child: Text('اشتراك شهري ثابت (SDG)')),
                                                ],
                                                onChanged: (v) {
                                                  if (v != null) setStateDialog(() => selectedContractType = v);
                                                },
                                              ),
                                            ),
                                            const SizedBox(width: 12),
                                            Expanded(
                                              child: _buildTextField(
                                                controller: contractValueController,
                                                label: selectedContractType == 'percentage'
                                                    ? 'نسبة العمولة (%)'
                                                    : 'قيمة الاشتراك (ج.س)',
                                                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                                const SizedBox(height: 20),

                                // Experience & Rating Section
                                _buildSectionTitle('التقييم والخبرة والاعتماد', Icons.verified_rounded),
                                Card(
                                  elevation: 0,
                                  color: const Color(0xFFF8FAFC),
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    side: BorderSide(color: Colors.grey.shade200),
                                  ),
                                  child: Padding(
                                    padding: const EdgeInsets.all(16),
                                    child: Column(
                                      children: [
                                        Row(
                                          children: [
                                            Expanded(
                                              child: _buildTextField(
                                                controller: experienceController,
                                                label: 'سنوات الخبرة المعتمدة',
                                              ),
                                            ),
                                            const SizedBox(width: 12),
                                            Expanded(
                                              child: _buildTextField(
                                                controller: completedOrdersController,
                                                label: 'العمليات والطلبات المنجزة',
                                                keyboardType: TextInputType.number,
                                              ),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 12),
                                        Row(
                                          children: [
                                            Expanded(
                                              child: _buildTextField(
                                                controller: ratingController,
                                                label: 'التقييم (من 0 إلى 5)',
                                                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                              ),
                                            ),
                                            const SizedBox(width: 12),
                                            Expanded(
                                              child: _buildTextField(
                                                controller: reviewsController,
                                                label: 'عدد المراجعات',
                                                keyboardType: TextInputType.number,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),

                      // Footer Actions
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          border: Border(top: BorderSide(color: Colors.grey.shade200)),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.end,
                          children: [
                            TextButton(
                              onPressed: isSaving ? null : () => Navigator.pop(dialogCtx),
                              child: const Text('إلغاء التراجع', style: TextStyle(fontWeight: FontWeight.bold)),
                            ),
                            const SizedBox(width: 12),
                            FilledButton.icon(
                              style: FilledButton.styleFrom(
                                backgroundColor: isEditing ? _kRoyalBlue : _kEmerald,
                                padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 14),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                              onPressed: isSaving
                                  ? null
                                  : () async {
                                      if (formKey.currentState!.validate()) {
                                        setStateDialog(() => isSaving = true);
                                        final messenger = ScaffoldMessenger.of(context);

                                        final nameAr = nameArController.text.trim();
                                        final nameEn = nameController.text.trim().isNotEmpty
                                            ? nameController.text.trim()
                                            : nameAr;
                                        final tradeAr = tradeArController.text.trim();
                                        final tradeEn = tradeController.text.trim().isNotEmpty
                                            ? tradeController.text.trim()
                                            : tradeAr;

                                        final newModel = ServiceProviderModel(
                                          id: provider?.id ?? '',
                                          name: nameEn,
                                          nameAr: nameAr,
                                          trade: tradeEn,
                                          tradeAr: tradeAr,
                                          categoryId: selectedCategory,
                                          phone: phoneController.text.trim(),
                                          location: locationController.text.trim(),
                                          locationAr: locationArController.text.trim(),
                                          experienceYears: experienceController.text.trim(),
                                          priceEstimate: priceController.text.trim(),
                                          rating: double.tryParse(ratingController.text) ?? 5.0,
                                          reviewsCount: int.tryParse(reviewsController.text) ?? 0,
                                          contractType: selectedContractType,
                                          contractValue: double.tryParse(contractValueController.text) ?? 0.0,
                                          completedOrdersCount: int.tryParse(completedOrdersController.text) ?? 0,
                                          isVerified: true,
                                          avatarUrl: existingAvatarUrl,
                                        );

                                        try {
                                          if (provider == null) {
                                            await ref.read(adminServicesProvider.notifier).addProvider(
                                                  newModel,
                                                  avatarBytes: avatarBytes,
                                                  avatarExtension: avatarExtension,
                                                );
                                          } else {
                                            await ref.read(adminServicesProvider.notifier).updateProvider(
                                                  newModel,
                                                  avatarBytes: avatarBytes,
                                                  avatarExtension: avatarExtension,
                                                );
                                          }

                                          ref.invalidate(adminServicesInsightsProvider);

                                          if (dialogCtx.mounted) Navigator.pop(dialogCtx);
                                          messenger.showSnackBar(
                                            SnackBar(
                                              content: Text(isEditing
                                                  ? 'تم تحديث بيانات مقدم الخدمة "$nameAr" بنجاح.'
                                                  : 'تم تسجيل ونشر مقدم الخدمة "$nameAr" في النظام فوراً.'),
                                              backgroundColor: _kEmerald,
                                            ),
                                          );
                                        } catch (e) {
                                          setStateDialog(() => isSaving = false);
                                          messenger.showSnackBar(
                                            SnackBar(content: Text('حدث خطأ أثناء الحفظ: $e'), backgroundColor: _kCrimson),
                                          );
                                        }
                                      }
                                    },
                              icon: isSaving
                                  ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                                  : const Icon(Icons.check_circle_rounded, size: 20),
                              label: Text(
                                isSaving
                                    ? 'جاري الحفظ...'
                                    : (isEditing ? 'حفظ التعديلات' : 'نشر واعتماد المزود'),
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildSectionTitle(String title, IconData icon) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8.0, top: 4.0),
      child: Row(
        children: [
          Icon(icon, color: _kRoyalBlue, size: 18),
          const SizedBox(width: 6),
          Text(
            title,
            style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: _kDarkNavy),
          ),
        ],
      ),
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String label,
    String? hintText,
    TextInputType? keyboardType,
    String? Function(String?)? validator,
  }) {
    return TextFormField(
      controller: controller,
      decoration: InputDecoration(
        labelText: label,
        hintText: hintText,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
        filled: true,
        fillColor: Colors.white,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      ),
      keyboardType: keyboardType,
      validator: validator,
    );
  }

  void _confirmDelete(BuildContext context, ServiceProviderModel provider, bool isRtl) {
    showDialog(
      context: context,
      builder: (dialogCtx) => Directionality(
        textDirection: TextDirection.rtl,
        child: AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
          title: const Row(
            children: [
              Icon(Icons.delete_forever_rounded, color: _kCrimson, size: 28),
              SizedBox(width: 10),
              Text('تأكيد حذف مقدم الخدمة', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
            ],
          ),
          content: Text(
            'هل أنت متأكد من حذف مقدم الخدمة "${provider.nameAr}" من قاعدة البيانات نهائياً؟ لا يمكن التراجع عن هذا الإجراء.',
            style: const TextStyle(fontSize: 14, color: _kDarkNavy),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(dialogCtx),
              child: const Text('إلغاء', style: TextStyle(fontWeight: FontWeight.bold)),
            ),
            FilledButton(
              style: FilledButton.styleFrom(backgroundColor: _kCrimson),
              onPressed: () async {
                Navigator.pop(dialogCtx);
                try {
                  await ref.read(adminServicesProvider.notifier).deleteProvider(provider.id);
                  ref.invalidate(adminServicesInsightsProvider);
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('تم حذف المزود "${provider.nameAr}" بنجاح.'), backgroundColor: _kCrimson),
                    );
                  }
                } catch (e) {
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('خطأ أثناء الحذف: $e'), backgroundColor: _kCrimson),
                    );
                  }
                }
              },
              child: const Text('تأكيد الحذف', style: TextStyle(fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }
}
