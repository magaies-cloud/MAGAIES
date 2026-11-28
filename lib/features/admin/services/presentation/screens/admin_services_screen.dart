import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/core/utils/phone_formatter.dart';
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
const Color _kTealDeep = Color(0xFF0F766E);
const Color _kTealLight = Color(0xFF0D9488);
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
      case 'contracting':
        return isRtl
            ? 'شركات المقاولات والتشطيبات والهياكل الخرسانية'
            : 'Contracting, Concrete & Finishing';
      case 'engineering':
        return isRtl ? 'استشارات وتصميم هندسي' : 'Engineering';
      case 'technicians':
        return isRtl ? 'فنيون ومهنيون' : 'Technicians';
      case 'labor':
        return isRtl ? 'عمالة وحرفيون تنفيذيون' : 'Construction Labor';
      case 'logistics':
        return isRtl ? 'شركات الشحن وأساطيل اللوجستيات' : 'Logistics & Shipping Fleet';
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
      case 7:
        s = s.where((p) => p.categoryId == 'contracting');
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
        s = s.where((p) =>
            p.rating >= 4.9 ||
            p.categoryId == 'logistics' ||
            p.categoryId == 'contracting' ||
            p.contractType != 'none');
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
                        _buildDetailRow(
                          'رقم الاتصال المباشر:',
                          provider.phone.isNotEmpty ? PhoneFormatter.format(provider.phone) : 'غير مسجل',
                          forceLtr: provider.phone.isNotEmpty,
                        ),
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

  Widget _buildDetailRow(String label, String value, {bool forceLtr = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: TextStyle(fontSize: 12, color: Colors.grey.shade700, fontWeight: FontWeight.w600)),
          const SizedBox(width: 8),
          Expanded(
            child: Align(
              alignment: AlignmentDirectional.centerStart,
              child: Text(
                value,
                textDirection: forceLtr ? TextDirection.ltr : null,
                style: TextStyle(
                  fontSize: 12.5,
                  fontWeight: FontWeight.bold,
                  color: _kDarkNavy,
                  fontFamily: forceLtr ? 'monospace' : null,
                ),
              ),
            ),
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
    required Color secondaryColor,
    required String badge,
    required double width,
  }) {
    return Container(
      width: width,
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [color, secondaryColor],
          begin: AlignmentDirectional.topStart,
          end: AlignmentDirectional.bottomEnd,
        ),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.white.withValues(alpha: 0.22), width: 1),
        boxShadow: [
          BoxShadow(
            color: color.withValues(alpha: 0.22),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Container(
            width: 38,
            height: 38,
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.18),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: Colors.white.withValues(alpha: 0.28)),
            ),
            child: Icon(icon, size: 20, color: Colors.white),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        label,
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.white.withValues(alpha: 0.92),
                          fontWeight: FontWeight.w700,
                          height: 1.15,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                      decoration: BoxDecoration(
                        color: Colors.black.withValues(alpha: 0.22),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        badge,
                        style: const TextStyle(
                          fontSize: 9.5,
                          fontWeight: FontWeight.w800,
                          color: Colors.white,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 5),
                FittedBox(
                  fit: BoxFit.scaleDown,
                  alignment: AlignmentDirectional.centerStart,
                  child: Text(
                    value,
                    style: const TextStyle(
                      fontWeight: FontWeight.w900,
                      fontSize: 17,
                      color: Colors.white,
                      letterSpacing: -0.2,
                      height: 1.1,
                    ),
                    maxLines: 1,
                  ),
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
    final bool isContracting = p.categoryId == 'contracting';
    final bool isSponsored =
        p.rating >= 4.9 || isLogistics || isContracting || p.contractType != 'none';

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
                    : (isContracting
                        ? [_kTealDeep, _kTealLight]
                        : (isSponsored
                            ? [const Color(0xFF1E3A8A), _kRoyalBlue]
                            : [const Color(0xFF334155), const Color(0xFF64748B)])),
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
                            _getCategoryIcon(p.categoryId),
                            size: 26,
                            color: isContracting ? _kTealDeep : _kRoyalBlue,
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
                        PhoneFormatter.text(
                          p.phone,
                          style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: Colors.grey.shade700, fontFamily: 'monospace'),
                        ),
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
    return Expanded(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 2),
        child: Column(
          children: [
            FittedBox(
              fit: BoxFit.scaleDown,
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(icon, size: 13.5, color: color),
                  const SizedBox(width: 3),
                  Text(value, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: _kDarkNavy)),
                ],
              ),
            ),
            const SizedBox(height: 2),
            Text(label, style: TextStyle(fontSize: 9.5, color: Colors.grey.shade600), maxLines: 1, overflow: TextOverflow.ellipsis),
          ],
        ),
      ),
    );
  }

  Color _getCategoryColor(String categoryId) {
    switch (categoryId) {
      case 'contracting':
        return _kTealDeep;
      case 'logistics':
        return _kAmberDark;
      case 'engineering':
        return _kPurple;
      case 'technicians':
        return _kRoyalBlue;
      case 'labor':
        return _kEmeraldDark;
      default:
        return _kRoyalBlue;
    }
  }

  IconData _getCategoryIcon(String categoryId) {
    switch (categoryId) {
      case 'contracting':
        return Icons.domain_rounded;
      case 'logistics':
        return Icons.local_shipping_rounded;
      case 'engineering':
        return Icons.architecture_rounded;
      case 'technicians':
        return Icons.handyman_rounded;
      case 'labor':
        return Icons.construction_rounded;
      default:
        return Icons.engineering_rounded;
    }
  }

  Widget _buildDedicatedSectorsHub(
    BuildContext context,
    AdminServicesInsights? insights,
    bool isRtl,
    bool isMobile,
  ) {
    final contractingCard = Container(
      padding: EdgeInsets.all(isMobile ? 16 : 20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF0F292E), Color(0xFF0F766E)],
          begin: Alignment.centerRight,
          end: Alignment.centerLeft,
        ),
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: _kTealDeep.withValues(alpha: 0.22),
            blurRadius: 12,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(11),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.16),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.domain_rounded, color: Colors.white, size: 26),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'قطاع شركات المقاولات والتشطيبات والخرسانة',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 15.5,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      'إدارة الشركات المنفذة للهياكل الخرسانية المسلحة، المباني، والتشطيبات المتكاملة',
                      style: TextStyle(
                        color: Colors.white.withValues(alpha: 0.85),
                        fontSize: 11.5,
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                decoration: BoxDecoration(
                  color: Colors.black.withValues(alpha: 0.24),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  '${insights?.contractingCount ?? 0} شركة',
                  style: const TextStyle(
                    color: Color(0xFF5EEAD4),
                    fontWeight: FontWeight.w900,
                    fontSize: 12.5,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Wrap(
            spacing: 6,
            runSpacing: 6,
            children: [
              _buildSectorMiniTag('🏗️ هياكل خرسانية مسلحة'),
              _buildSectorMiniTag('🎨 تشطيبات تسليم مفتاح'),
              _buildSectorMiniTag('🏢 مقاولات عامة وهناجر'),
            ],
          ),
          const SizedBox(height: 14),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              FilledButton.icon(
                style: FilledButton.styleFrom(
                  backgroundColor: Colors.white,
                  foregroundColor: _kTealDeep,
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                onPressed: () => _showProviderDialog(
                  context: context,
                  isRtl: isRtl,
                  initialCategory: 'contracting',
                ),
                icon: const Icon(Icons.add_business_rounded, size: 17),
                label: const Text(
                  'تسجيل شركة مقاولات +',
                  style: TextStyle(fontWeight: FontWeight.w900, fontSize: 12.5),
                ),
              ),
              OutlinedButton.icon(
                style: OutlinedButton.styleFrom(
                  foregroundColor: Colors.white,
                  side: BorderSide(color: Colors.white.withValues(alpha: 0.35)),
                  padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 10),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                onPressed: () => setState(() => _selectedSegment = 7),
                icon: const Icon(Icons.filter_list_rounded, size: 17),
                label: Text(
                  _selectedSegment == 7 ? 'معروض الآن ✓' : 'عرض شركات المقاولات',
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                ),
              ),
            ],
          ),
        ],
      ),
    );

    final logisticsCard = Container(
      padding: EdgeInsets.all(isMobile ? 16 : 20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF0F172A), Color(0xFF1E3A8A)],
          begin: Alignment.centerRight,
          end: Alignment.centerLeft,
        ),
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: _kRoyalBlue.withValues(alpha: 0.2),
            blurRadius: 12,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(11),
                decoration: BoxDecoration(
                  color: Colors.amber.withValues(alpha: 0.18),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.local_shipping_rounded, color: Color(0xFFFBBF24), size: 26),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'قطاع شركات الشحن والخدمات اللوجستية',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 15.5,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      'إدارة شركات الشحن البري، التريلات المسطحة، القلابات، الدفارات وفرق التعتيل',
                      style: TextStyle(
                        color: Colors.white.withValues(alpha: 0.85),
                        fontSize: 11.5,
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                decoration: BoxDecoration(
                  color: Colors.black.withValues(alpha: 0.24),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  '${insights?.logisticsCount ?? 0} أسطول',
                  style: const TextStyle(
                    color: Color(0xFFFCD34D),
                    fontWeight: FontWeight.w900,
                    fontSize: 12.5,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Wrap(
            spacing: 6,
            runSpacing: 6,
            children: [
              _buildSectorMiniTag('🚛 مسطحات وتريلات شحن'),
              _buildSectorMiniTag('🚜 قلابات رمل وخرسانة'),
              _buildSectorMiniTag('🚚 دفارات وتعتيل موقعي'),
            ],
          ),
          const SizedBox(height: 14),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              FilledButton.icon(
                style: FilledButton.styleFrom(
                  backgroundColor: const Color(0xFFF59E0B),
                  foregroundColor: const Color(0xFF0F172A),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                onPressed: () => _showProviderDialog(
                  context: context,
                  isRtl: isRtl,
                  initialCategory: 'logistics',
                ),
                icon: const Icon(Icons.local_shipping_rounded, size: 17),
                label: const Text(
                  'تسجيل شركة شحن / أسطول +',
                  style: TextStyle(fontWeight: FontWeight.w900, fontSize: 12.5),
                ),
              ),
              OutlinedButton.icon(
                style: OutlinedButton.styleFrom(
                  foregroundColor: Colors.white,
                  side: BorderSide(color: Colors.white.withValues(alpha: 0.35)),
                  padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 10),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                onPressed: () => setState(() => _selectedSegment = 1),
                icon: const Icon(Icons.filter_list_rounded, size: 17),
                label: Text(
                  _selectedSegment == 1 ? 'معروض الآن ✓' : 'عرض شركات اللوجستيات',
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                ),
              ),
            ],
          ),
        ],
      ),
    );

    return LayoutBuilder(
      builder: (context, constraints) {
        if (constraints.maxWidth < 920) {
          return Column(
            children: [
              contractingCard,
              const SizedBox(height: 12),
              logisticsCard,
            ],
          );
        }
        return Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(child: contractingCard),
            const SizedBox(width: 14),
            Expanded(child: logisticsCard),
          ],
        );
      },
    );
  }

  Widget _buildSectorMiniTag(String text) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.13),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: Colors.white.withValues(alpha: 0.18)),
      ),
      child: Text(
        text,
        style: const TextStyle(
          color: Colors.white,
          fontSize: 11,
          fontWeight: FontWeight.w700,
        ),
      ),
    );
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

                          // --- 2. Live Accounting & Fleet KPI Grid (6 Distinct Compact Cards) ---
                          LayoutBuilder(
                            builder: (context, constraints) {
                              const double spacing = 10.0;
                              final int cols = constraints.maxWidth > 1050
                                  ? 3
                                  : (constraints.maxWidth > 620 ? 2 : 1);
                              final double cardW =
                                  (constraints.maxWidth - (spacing * (cols - 1))) / cols;

                              return Wrap(
                                spacing: spacing,
                                runSpacing: spacing,
                                children: [
                                  _buildStatCard(
                                    width: cardW,
                                    icon: Icons.monetization_on_rounded,
                                    label: 'عوائد النقل واللوجستيات المعتمدة',
                                    value: insights != null ? '${_fmtSdg(insights.totalLogisticsFees)} ج.س' : '0 ج.س',
                                    color: const Color(0xFF059669),
                                    secondaryColor: const Color(0xFF047857),
                                    badge: insights != null ? '${insights.logisticsOrdersCount} عملية توريد' : 'لوجستيات',
                                  ),
                                  _buildStatCard(
                                    width: cardW,
                                    icon: Icons.groups_rounded,
                                    label: 'إجمالي مقدمي الخدمات',
                                    value: '${state.providers.length} مزود وشركة',
                                    color: const Color(0xFF2563EB),
                                    secondaryColor: const Color(0xFF1E40AF),
                                    badge: '${insights?.verifiedCount ?? state.providers.where((p) => p.isVerified).length} موثق ✓',
                                  ),
                                  _buildStatCard(
                                    width: cardW,
                                    icon: Icons.domain_rounded,
                                    label: 'شركات المقاولات والهياكل الخرسانية',
                                    value: '${insights?.contractingCount ?? 0} شركة معتمدة',
                                    color: _kTealDeep,
                                    secondaryColor: const Color(0xFF115E59),
                                    badge: 'خرسانة وتشطيبات',
                                  ),
                                  _buildStatCard(
                                    width: cardW,
                                    icon: Icons.local_shipping_rounded,
                                    label: 'شركات الشحن وأساطيل اللوجستيات',
                                    value: '${insights?.logisticsCount ?? 0} أساطيل معتمدة',
                                    color: const Color(0xFFD97706),
                                    secondaryColor: const Color(0xFFB45309),
                                    badge: 'تريلات وقلابات',
                                  ),
                                  _buildStatCard(
                                    width: cardW,
                                    icon: Icons.architecture_rounded,
                                    label: 'المكاتب والاستشارات الهندسية',
                                    value: '${insights?.engineeringCount ?? 0} مكاتب معتمدة',
                                    color: const Color(0xFF7C3AED),
                                    secondaryColor: const Color(0xFF5B21B6),
                                    badge: 'تصميم وإشراف',
                                  ),
                                  _buildStatCard(
                                    width: cardW,
                                    icon: Icons.pending_actions_rounded,
                                    label: 'طلبات الانضمام المعلقة',
                                    value: '${insights?.pendingApprovalCount ?? 0} طلب انضمام',
                                    color: const Color(0xFFE11D48),
                                    secondaryColor: const Color(0xFF9F1239),
                                    badge: (insights?.pendingApprovalCount ?? 0) > 0 ? 'تحتاج مراجعة' : 'مكتمل',
                                  ),
                                ],
                              );
                            },
                          ),
                          const SizedBox(height: 18),

                          // --- 2B. Dedicated Corporate Contracting & Logistics Hub ---
                          _buildDedicatedSectorsHub(context, insights, isRtl, isMobile),
                          const SizedBox(height: 20),

                          // --- 3. Segment Navigation Tabs ---
                          SingleChildScrollView(
                            scrollDirection: Axis.horizontal,
                            child: Row(
                              children: [
                                _buildSegmentTab(0, '🌐 كل المزودين والشركات', Icons.all_inclusive_rounded, _kDarkNavy, state.providers.length),
                                const SizedBox(width: 12),
                                _buildSegmentTab(7, '🏢 شركات المقاولات والخرسانة والتشطيبات', Icons.domain_rounded, _kTealDeep, insights?.contractingCount ?? 0),
                                const SizedBox(width: 12),
                                _buildSegmentTab(1, '🚚 شركات الشحن واللوجستيات', Icons.local_shipping_rounded, _kAmberDark, insights?.logisticsCount ?? 0),
                                const SizedBox(width: 12),
                                _buildSegmentTab(6, '⏳ طلبات الانضمام المعلقة', Icons.pending_actions_rounded, const Color(0xFFD97706), insights?.pendingApprovalCount ?? 0),
                                const SizedBox(width: 12),
                                _buildSegmentTab(2, '🏗️ الهندسة والتصميم', Icons.architecture_rounded, _kRoyalBlue, insights?.engineeringCount ?? 0),
                                const SizedBox(width: 12),
                                _buildSegmentTab(3, '⚡ الفنيون والمهنيون', Icons.handyman_rounded, _kPurple, insights?.techniciansCount ?? 0),
                                const SizedBox(width: 12),
                                _buildSegmentTab(4, '🔨 العمالة والحرفيون', Icons.construction_rounded, _kEmerald, insights?.laborCount ?? 0),
                                const SizedBox(width: 12),
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
                        padding: EdgeInsets.fromLTRB(isMobile ? 12 : 20, 0, isMobile ? 12 : 20, 24),
                        sliver: SliverToBoxAdapter(
                          child: LayoutBuilder(
                            builder: (context, tableConstraints) {
                              final tableBox = Container(
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
                                        final isSponsored = p.rating >= 4.9 || p.categoryId == 'logistics' || p.categoryId == 'contracting' || p.contractType != 'none';

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
                                                              _getCategoryIcon(p.categoryId),
                                                              size: 18,
                                                              color: _getCategoryColor(p.categoryId),
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
                                                  PhoneFormatter.text(
                                                    p.phone,
                                                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Colors.grey.shade700, fontFamily: 'monospace'),
                                                  ),
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
                          );
                          if (tableConstraints.maxWidth < 940) {
                            return SingleChildScrollView(
                              scrollDirection: Axis.horizontal,
                              child: SizedBox(width: 960, child: tableBox),
                            );
                          }
                          return tableBox;
                        },
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
    String initialCategory = 'logistics',
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
    final phoneController = TextEditingController(
      text: provider != null && provider.phone.trim().isNotEmpty
          ? PhoneFormatter.format(provider.phone)
          : '',
    );
    final locationController = TextEditingController(text: provider?.location ?? '');
    final locationArController = TextEditingController(text: provider?.locationAr ?? '');
    final contractValueController = TextEditingController(text: provider != null ? provider.contractValue.toString() : '0.0');
    final completedOrdersController = TextEditingController(text: provider != null ? provider.completedOrdersCount.toString() : '0');

    String selectedCategory = provider?.categoryId ?? initialCategory;
    if (!['contracting', 'engineering', 'technicians', 'labor', 'logistics'].contains(selectedCategory)) {
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
                                                hintText: selectedCategory == 'contracting'
                                                    ? 'مثال: تنفيذ هياكل خرسانية مسلحة وتشطيبات تسليم مفتاح'
                                                    : 'مثال: قلابات 20 متر لنقل الرمل والحجر',
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
                                          isExpanded: true,
                                          initialValue: selectedCategory,
                                          decoration: InputDecoration(
                                            labelText: 'القطاع والتصنيف الرئيسي *',
                                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                            filled: true,
                                            fillColor: Colors.white,
                                            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                                          ),
                                          items: [
                                            DropdownMenuItem(value: 'contracting', child: Text(_getCategoryName('contracting', true), overflow: TextOverflow.ellipsis)),
                                            DropdownMenuItem(value: 'logistics', child: Text(_getCategoryName('logistics', true), overflow: TextOverflow.ellipsis)),
                                            DropdownMenuItem(value: 'engineering', child: Text(_getCategoryName('engineering', true), overflow: TextOverflow.ellipsis)),
                                            DropdownMenuItem(value: 'technicians', child: Text(_getCategoryName('technicians', true), overflow: TextOverflow.ellipsis)),
                                            DropdownMenuItem(value: 'labor', child: Text(_getCategoryName('labor', true), overflow: TextOverflow.ellipsis)),
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
                                                hintText: '+249 912 300 000',
                                                keyboardType: TextInputType.phone,
                                                forceLtr: true,
                                              ),
                                            ),
                                            const SizedBox(width: 12),
                                            Expanded(
                                              child: _buildTextField(
                                                controller: locationArController,
                                                label: 'نطاق التغطية والموقع',
                                                hintText: 'مثال: الخرطوم، بحري، بورتسودان، عطبرة',
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
                                          phone: PhoneFormatter.format(phoneController.text),
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
    bool forceLtr = false,
  }) {
    return TextFormField(
      controller: controller,
      textDirection: forceLtr ? TextDirection.ltr : null,
      decoration: InputDecoration(
        labelText: label,
        hintText: hintText,
        hintTextDirection: forceLtr ? TextDirection.ltr : null,
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
