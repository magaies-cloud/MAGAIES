import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import 'package:magaiees_marketplace/core/utils/phone_formatter.dart';
import 'package:magaiees_marketplace/features/admin/suppliers/application/verified_suppliers_provider.dart';

class AdminVerifiedSuppliersScreen extends ConsumerStatefulWidget {
  const AdminVerifiedSuppliersScreen({super.key});

  @override
  ConsumerState<AdminVerifiedSuppliersScreen> createState() =>
      _AdminVerifiedSuppliersScreenState();
}

class _AdminVerifiedSuppliersScreenState
    extends ConsumerState<AdminVerifiedSuppliersScreen> {
  String _searchQuery = '';
  String _selectedTypeFilter = 'الكل';

  static const List<Map<String, String>> _supplierTypes = [
    {'ar': 'مصنع معتمد', 'en': 'Certified Plant'},
    {'ar': 'مورد رئيسي', 'en': 'Main Supplier'},
    {'ar': 'مصنع وطني', 'en': 'National Plant'},
    {'ar': 'مجمع مواد بناء', 'en': 'Building Hub'},
    {'ar': 'وكيل حصري', 'en': 'Exclusive Agent'},
    {'ar': 'شركة توريدات هندسية', 'en': 'Engineering Supply Co.'},
    {'ar': 'شركة مقاولات وهياكل خرسانية', 'en': 'Contracting & Concrete Co.'},
    {'ar': 'شركة تشطيبات وديكور هندسي', 'en': 'Turnkey Finishing Co.'},
  ];

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final screenWidth = MediaQuery.of(context).size.width;
    final isMobile = screenWidth < 700;
    final suppliers = ref.watch(verifiedSuppliersProvider);

    final filtered = suppliers.where((s) {
      final matchesSearch = _searchQuery.trim().isEmpty ||
          s.nameAr.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          s.nameEn.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          s.city.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          s.specialty.toLowerCase().contains(_searchQuery.toLowerCase());

      if (!matchesSearch) return false;
      if (_selectedTypeFilter == 'الكل') return true;
      if (_selectedTypeFilter == 'النشط فقط') return s.isActive;
      return s.typeAr == _selectedTypeFilter;
    }).toList();

    final totalCount = suppliers.length;
    final activeCount = suppliers.where((s) => s.isActive).length;
    final verifiedCount = suppliers.where((s) => s.isVerified).length;
    final avgRating = suppliers.isEmpty
        ? 0.0
        : suppliers.map((s) => s.rating).reduce((a, b) => a + b) /
            suppliers.length;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: CustomScrollView(
        slivers: [
          SliverPadding(
            padding: EdgeInsets.all(isMobile ? 12 : 20),
            sliver: SliverList(
              delegate: SliverChildListDelegate([
                // ── 1. Top Executive Header ─────────────────────────────
                _buildHeader(context, isRtl, isMobile),
                const SizedBox(height: 16),

                // ── 2. Compact KPI Cards (40% Height & Distinct Colors) ──
                _buildKpiCardsRow(
                  totalCount: totalCount,
                  activeCount: activeCount,
                  verifiedCount: verifiedCount,
                  avgRating: avgRating,
                ),
                const SizedBox(height: 18),

                // ── 3. Live App Strip Preview ───────────────────────────
                _buildLiveAppPreview(
                  context,
                  suppliers.where((s) => s.isActive).toList(),
                ),
                const SizedBox(height: 18),

                // ── 4. Search & Filter Bar ──────────────────────────────
                _buildSearchAndFilters(),
                const SizedBox(height: 16),

                // ── 5. Suppliers Grid / List ────────────────────────────
                if (filtered.isEmpty)
                  _buildEmptyState(context)
                else
                  LayoutBuilder(
                    builder: (context, constraints) {
                      final crossAxisCount = constraints.maxWidth > 1150
                          ? 3
                          : constraints.maxWidth > 720
                              ? 2
                              : 1;
                      return GridView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: filtered.length,
                        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: crossAxisCount,
                          crossAxisSpacing: 14,
                          mainAxisSpacing: 14,
                          mainAxisExtent: isMobile ? 260 : 244,
                        ),
                        itemBuilder: (context, index) {
                          final supplier = filtered[index];
                          return _buildSupplierAdminCard(
                            context,
                            supplier,
                            index,
                            filtered.length,
                          );
                        },
                      );
                    },
                  ),
                const SizedBox(height: 36),
              ]),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeader(BuildContext context, bool isRtl, bool isMobile) {
    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: isMobile ? 16 : 22,
        vertical: isMobile ? 16 : 18,
      ),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF0F172A), Color(0xFF1E3A8A)],
          begin: Alignment.centerRight,
          end: Alignment.centerLeft,
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF1E3A8A).withValues(alpha: 0.18),
            blurRadius: 14,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(11),
                decoration: BoxDecoration(
                  color: Colors.blue.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.blue.shade300.withValues(alpha: 0.4)),
                ),
                child: const Icon(
                  Icons.verified_rounded,
                  color: Color(0xFF60A5FA),
                  size: 28,
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Wrap(
                      spacing: 8,
                      runSpacing: 6,
                      crossAxisAlignment: WrapCrossAlignment.center,
                      children: [
                        Text(
                          'إدارة المصانع والموردين المعتمدين',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: isMobile ? 16.5 : 19,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: const Color(0xFF2563EB),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: const Text(
                            'موثق ✓',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'تحكم كامل في إضافة وتعديل وحذف وترتيب المصانع والموردين وشركات المقاولات المعتمدة في الواجهة الرئيسية',
                      style: TextStyle(
                        color: Colors.blue.shade100,
                        fontSize: isMobile ? 11.5 : 12.5,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Wrap(
            spacing: 10,
            runSpacing: 10,
            alignment: WrapAlignment.end,
            children: [
              OutlinedButton.icon(
                onPressed: () async {
                  await ref.read(verifiedSuppliersProvider.notifier).refresh();
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('تمت مزامنة قائمة المصانع والموردين مع السحابة'),
                        backgroundColor: Color(0xFF059669),
                      ),
                    );
                  }
                },
                style: OutlinedButton.styleFrom(
                  foregroundColor: Colors.white,
                  side: BorderSide(color: Colors.white.withValues(alpha: 0.3)),
                  padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 11),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
                icon: const Icon(Icons.sync_rounded, size: 18),
                label: const Text(
                  'مزامنة',
                  style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold),
                ),
              ),
              OutlinedButton.icon(
                onPressed: () => _confirmResetDefaults(context),
                style: OutlinedButton.styleFrom(
                  foregroundColor: Colors.white,
                  side: BorderSide(color: Colors.white.withValues(alpha: 0.3)),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
                icon: const Icon(Icons.restore_rounded, size: 18),
                label: const Text(
                  'استعادة الافتراضي',
                  style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold),
                ),
              ),
              FilledButton.icon(
                onPressed: () => _showSupplierDialog(context),
                style: FilledButton.styleFrom(
                  backgroundColor: const Color(0xFF2563EB),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
                icon: const Icon(Icons.add_business_rounded, size: 19),
                label: const Text(
                  'إضافة مصنع / مورد معتمد +',
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildKpiCardsRow({
    required int totalCount,
    required int activeCount,
    required int verifiedCount,
    required double avgRating,
  }) {
    final cards = [
      _KpiItem(
        title: 'إجمالي المصانع والموردين',
        value: '$totalCount',
        subtitle: 'مسجل في القائمة',
        icon: Icons.factory_rounded,
        colors: const [Color(0xFF1E3A8A), Color(0xFF2563EB)],
      ),
      _KpiItem(
        title: 'المعروضون في الواجهة',
        value: '$activeCount',
        subtitle: 'نشط حالياً للمشترين',
        icon: Icons.visibility_rounded,
        colors: const [Color(0xFF065F46), Color(0xFF10B981)],
      ),
      _KpiItem(
        title: 'الحاصلون على شارة موثق',
        value: '$verifiedCount',
        subtitle: 'اعتماد رسمي',
        icon: Icons.verified_rounded,
        colors: const [Color(0xFF0369A1), Color(0xFF0EA5E9)],
      ),
      _KpiItem(
        title: 'متوسط تقييم الاعتماد',
        value: '${avgRating.toStringAsFixed(1)} ⭐',
        subtitle: 'من 5.0 درجات',
        icon: Icons.workspace_premium_rounded,
        colors: const [Color(0xFFB45309), Color(0xFFF59E0B)],
      ),
    ];

    return LayoutBuilder(
      builder: (context, constraints) {
        final cols = constraints.maxWidth > 950
            ? 4
            : constraints.maxWidth > 550
                ? 2
                : 1;
        return GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: cards.length,
          gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: cols,
            crossAxisSpacing: 12,
            mainAxisSpacing: 12,
            mainAxisExtent: 74,
          ),
          itemBuilder: (context, i) {
            final c = cards[i];
            return Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: c.colors,
                  begin: Alignment.topRight,
                  end: Alignment.bottomLeft,
                ),
                borderRadius: BorderRadius.circular(12),
                boxShadow: [
                  BoxShadow(
                    color: c.colors.last.withValues(alpha: 0.22),
                    blurRadius: 8,
                    offset: const Offset(0, 3),
                  ),
                ],
              ),
              child: Row(
                children: [
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.18),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Icon(c.icon, color: Colors.white, size: 21),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(
                          c.title,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            color: Colors.white.withValues(alpha: 0.9),
                            fontSize: 11.5,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Row(
                          children: [
                            Flexible(
                              child: FittedBox(
                                fit: BoxFit.scaleDown,
                                child: Text(
                                  c.value,
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 19,
                                    fontWeight: FontWeight.w900,
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 6,
                                vertical: 2,
                              ),
                              decoration: BoxDecoration(
                                color: Colors.black.withValues(alpha: 0.18),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                c.subtitle,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 10,
                                  fontWeight: FontWeight.w600,
                                ),
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
          },
        );
      },
    );
  }

  Widget _buildLiveAppPreview(
    BuildContext context,
    List<VerifiedSupplierModel> activeSuppliers,
  ) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Wrap(
            alignment: WrapAlignment.spaceBetween,
            crossAxisAlignment: WrapCrossAlignment.center,
            spacing: 10,
            runSpacing: 8,
            children: [
              Wrap(
                spacing: 8,
                runSpacing: 6,
                crossAxisAlignment: WrapCrossAlignment.center,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(color: const Color(0xFFBFDBFE)),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.phone_iphone_rounded, size: 14, color: Color(0xFF1D4ED8)),
                        SizedBox(width: 4),
                        Text(
                          'معاينة حية كما تظهر في واجهة المشتري',
                          style: TextStyle(
                            fontSize: 11.5,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF1D4ED8),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.verified, color: Colors.blue, size: 18),
                      SizedBox(width: 5),
                      Text(
                        'المصانع والموردين المعتمدين',
                        style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                ],
              ),
              const Text(
                'موثق',
                style: TextStyle(
                  color: Colors.blue,
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          if (activeSuppliers.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 12),
              child: Text(
                'لا يوجد موردون مفعلون للعرض حالياً (سيتم إخفاء الشريط تلقائياً من واجهة المشتري).',
                style: TextStyle(color: Colors.grey, fontSize: 12),
              ),
            )
          else
            SizedBox(
              height: 82,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                itemCount: activeSuppliers.length,
                itemBuilder: (context, index) {
                  final s = activeSuppliers[index];
                  return Container(
                    width: 185,
                    margin: const EdgeInsets.only(left: 8, top: 2, bottom: 2),
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Row(
                      children: [
                        CircleAvatar(
                          radius: 18,
                          backgroundColor: const Color(0xFF2563EB).withValues(alpha: 0.12),
                          backgroundImage: (s.logoUrl != null && s.logoUrl!.trim().isNotEmpty)
                              ? NetworkImage(s.logoUrl!.trim())
                              : null,
                          child: (s.logoUrl == null || s.logoUrl!.trim().isEmpty)
                              ? Icon(
                                  _getIconForType(s.typeAr),
                                  size: 18,
                                  color: const Color(0xFF2563EB),
                                )
                              : null,
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      s.nameAr,
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                      style: const TextStyle(
                                        fontSize: 12,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ),
                                  if (s.isVerified)
                                    const Icon(
                                      Icons.verified_rounded,
                                      size: 13,
                                      color: Colors.blue,
                                    ),
                                ],
                              ),
                              const SizedBox(height: 3),
                              Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      s.typeAr,
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                      style: const TextStyle(
                                        fontSize: 10,
                                        color: Colors.grey,
                                      ),
                                    ),
                                  ),
                                  const Icon(Icons.star, size: 12, color: Colors.amber),
                                  Text(
                                    s.rating.toStringAsFixed(1),
                                    style: const TextStyle(
                                      fontSize: 10,
                                      fontWeight: FontWeight.bold,
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
                },
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildSearchAndFilters() {
    final filters = [
      'الكل',
      'النشط فقط',
      ..._supplierTypes.map((t) => t['ar']!),
    ];

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: TextField(
                  onChanged: (val) => setState(() => _searchQuery = val),
                  decoration: InputDecoration(
                    hintText: 'ابحث باسم المصنع أو المورد، المدينة، أو التخصص...',
                    hintStyle: const TextStyle(fontSize: 13),
                    prefixIcon: const Icon(Icons.search_rounded, size: 20),
                    suffixIcon: _searchQuery.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear_rounded, size: 18),
                            onPressed: () => setState(() => _searchQuery = ''),
                          )
                        : null,
                    filled: true,
                    fillColor: const Color(0xFFF8FAFC),
                    contentPadding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 10,
                    ),
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
            ],
          ),
          const SizedBox(height: 10),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: filters.map((f) {
                final isSelected = _selectedTypeFilter == f;
                return Padding(
                  padding: const EdgeInsets.only(left: 8),
                  child: ChoiceChip(
                    label: Text(
                      f,
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                        color: isSelected ? Colors.white : const Color(0xFF334155),
                      ),
                    ),
                    selected: isSelected,
                    selectedColor: const Color(0xFF1E40AF),
                    backgroundColor: const Color(0xFFF1F5F9),
                    onSelected: (_) => setState(() => _selectedTypeFilter = f),
                  ),
                );
              }).toList(),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSupplierAdminCard(
    BuildContext context,
    VerifiedSupplierModel supplier,
    int index,
    int totalFiltered,
  ) {
    final badgeColor = _getColorForType(supplier.typeAr);

    return Container(
      padding: const EdgeInsets.all(15),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: supplier.isActive
              ? const Color(0xFFCBD5E1)
              : Colors.red.shade200,
          width: 1.2,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Top Row: Avatar, Names, Verified Badge, Order & Actions
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 46,
                height: 46,
                decoration: BoxDecoration(
                  color: badgeColor.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(12),
                  image: (supplier.logoUrl != null && supplier.logoUrl!.trim().isNotEmpty)
                      ? DecorationImage(
                          image: NetworkImage(supplier.logoUrl!.trim()),
                          fit: BoxFit.cover,
                        )
                      : null,
                ),
                child: (supplier.logoUrl == null || supplier.logoUrl!.trim().isEmpty)
                    ? Icon(
                        _getIconForType(supplier.typeAr),
                        color: badgeColor,
                        size: 24,
                      )
                    : null,
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Flexible(
                          child: Text(
                            supplier.nameAr,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w900,
                              color: Color(0xFF0F172A),
                            ),
                          ),
                        ),
                        if (supplier.isVerified) ...[
                          const SizedBox(width: 5),
                          const Tooltip(
                            message: 'معتمد وموثق بشارة موثق الزرقاء',
                            child: Icon(
                              Icons.verified_rounded,
                              color: Color(0xFF2563EB),
                              size: 17,
                            ),
                          ),
                        ],
                      ],
                    ),
                    const SizedBox(height: 2),
                    Text(
                      supplier.nameEn.isNotEmpty ? supplier.nameEn : supplier.typeEn,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: 11.5,
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ],
                ),
              ),
              // Reorder buttons
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  IconButton(
                    visualDensity: VisualDensity.compact,
                    tooltip: 'تحريك للأعلى/اليمين',
                    onPressed: index > 0
                        ? () => ref
                            .read(verifiedSuppliersProvider.notifier)
                            .moveSupplier(supplier.id, -1)
                        : null,
                    icon: const Icon(Icons.arrow_upward_rounded, size: 17),
                  ),
                  IconButton(
                    visualDensity: VisualDensity.compact,
                    tooltip: 'تحريك للأسفل/اليسار',
                    onPressed: index < totalFiltered - 1
                        ? () => ref
                            .read(verifiedSuppliersProvider.notifier)
                            .moveSupplier(supplier.id, 1)
                        : null,
                    icon: const Icon(Icons.arrow_downward_rounded, size: 17),
                  ),
                ],
              ),
            ],
          ),

          const SizedBox(height: 10),

          // Type chip + Rating + City
          Wrap(
            spacing: 6,
            runSpacing: 6,
            crossAxisAlignment: WrapCrossAlignment.center,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: badgeColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  supplier.typeAr,
                  style: TextStyle(
                    color: badgeColor,
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                decoration: BoxDecoration(
                  color: Colors.amber.withValues(alpha: 0.14),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.star_rounded, size: 13, color: Colors.amber),
                    const SizedBox(width: 3),
                    Text(
                      supplier.rating.toStringAsFixed(1),
                      style: const TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF92400E),
                      ),
                    ),
                  ],
                ),
              ),
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.location_on_outlined, size: 13, color: Colors.grey.shade600),
                  const SizedBox(width: 2),
                  Text(
                    supplier.city,
                    style: TextStyle(fontSize: 11, color: Colors.grey.shade700),
                  ),
                ],
              ),
              if (supplier.phone.trim().isNotEmpty)
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.phone_outlined, size: 12.5, color: Colors.grey.shade600),
                    const SizedBox(width: 3),
                    PhoneFormatter.text(
                      supplier.phone,
                      style: TextStyle(
                        fontSize: 11.5,
                        fontWeight: FontWeight.w700,
                        color: Colors.grey.shade800,
                        fontFamily: 'monospace',
                      ),
                    ),
                  ],
                ),
            ],
          ),

          const SizedBox(height: 8),

          // Specialty text
          Text(
            supplier.specialty,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              fontSize: 12,
              color: Colors.grey.shade700,
            ),
          ),

          const Spacer(),
          const Divider(height: 14),

          // Bottom Controls: Active Toggle, Verified Toggle, Edit, Delete
          Wrap(
            alignment: WrapAlignment.spaceBetween,
            crossAxisAlignment: WrapCrossAlignment.center,
            spacing: 8,
            runSpacing: 6,
            children: [
              // Switches
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  InkWell(
                    borderRadius: BorderRadius.circular(6),
                    onTap: () => ref
                        .read(verifiedSuppliersProvider.notifier)
                        .toggleActive(supplier.id),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: supplier.isActive
                            ? const Color(0xFFDCFCE7)
                            : const Color(0xFFFEE2E2),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            supplier.isActive
                                ? Icons.visibility_rounded
                                : Icons.visibility_off_rounded,
                            size: 13,
                            color: supplier.isActive
                                ? const Color(0xFF15803D)
                                : const Color(0xFFB91C1C),
                          ),
                          const SizedBox(width: 4),
                          Text(
                            supplier.isActive ? 'ظاهر بالواجهة' : 'مخفي',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              color: supplier.isActive
                                  ? const Color(0xFF15803D)
                                  : const Color(0xFFB91C1C),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 6),
                  InkWell(
                    borderRadius: BorderRadius.circular(6),
                    onTap: () => ref
                        .read(verifiedSuppliersProvider.notifier)
                        .toggleVerified(supplier.id),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: supplier.isVerified
                            ? const Color(0xFFDBEAFE)
                            : const Color(0xFFF1F5F9),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            Icons.verified_rounded,
                            size: 13,
                            color: supplier.isVerified
                                ? const Color(0xFF1D4ED8)
                                : Colors.grey,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            supplier.isVerified ? 'موثق' : 'غير موثق',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              color: supplier.isVerified
                                  ? const Color(0xFF1D4ED8)
                                  : Colors.grey.shade700,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),

              // Edit & Delete buttons
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  TextButton.icon(
                    onPressed: () => _showSupplierDialog(context, existing: supplier),
                    style: TextButton.styleFrom(
                      foregroundColor: const Color(0xFF1E40AF),
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
                      minimumSize: Size.zero,
                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                    ),
                    icon: const Icon(Icons.edit_rounded, size: 15),
                    label: const Text(
                      'تعديل',
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                    ),
                  ),
                  const SizedBox(width: 4),
                  TextButton.icon(
                    onPressed: () => _confirmDelete(context, supplier),
                    style: TextButton.styleFrom(
                      foregroundColor: const Color(0xFFDC2626),
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
                      minimumSize: Size.zero,
                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                    ),
                    icon: const Icon(Icons.delete_outline_rounded, size: 15),
                    label: const Text(
                      'حذف',
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(36),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        children: [
          Icon(Icons.factory_outlined, size: 52, color: Colors.grey.shade400),
          const SizedBox(height: 12),
          const Text(
            'لا توجد مصانع أو موردون مطابقون للبحث',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 6),
          Text(
            'يمكنك إضافة مصنع أو مورد معتمد جديد ليظهر فوراً في الصفحة الرئيسية للتطبيق',
            style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
          ),
          const SizedBox(height: 16),
          FilledButton.icon(
            onPressed: () => _showSupplierDialog(context),
            icon: const Icon(Icons.add_rounded),
            label: const Text('إضافة مصنع أو مورد الآن'),
          ),
        ],
      ),
    );
  }

  Future<void> _showSupplierDialog(
    BuildContext context, {
    VerifiedSupplierModel? existing,
  }) async {
    final isEdit = existing != null;
    final nameArCtrl = TextEditingController(text: existing?.nameAr ?? '');
    final nameEnCtrl = TextEditingController(text: existing?.nameEn ?? '');
    final cityCtrl = TextEditingController(text: existing?.city ?? 'الخرطوم');
    final specialtyCtrl = TextEditingController(
      text: existing?.specialty ?? 'مواد بناء وتوريدات هندسية',
    );
    final phoneCtrl = TextEditingController(
      text: existing != null && existing.phone.trim().isNotEmpty
          ? PhoneFormatter.format(existing.phone)
          : '',
    );
    final logoUrlCtrl = TextEditingController(text: existing?.logoUrl ?? '');
    final ratingCtrl = TextEditingController(
      text: (existing?.rating ?? 4.9).toStringAsFixed(1),
    );

    String selectedTypeAr = existing?.typeAr ?? _supplierTypes.first['ar']!;
    String selectedTypeEn = existing?.typeEn ?? _supplierTypes.first['en']!;
    if (!_supplierTypes.any((t) => t['ar'] == selectedTypeAr)) {
      selectedTypeAr = _supplierTypes.first['ar']!;
      selectedTypeEn = _supplierTypes.first['en']!;
    }

    bool isVerified = existing?.isVerified ?? true;
    bool isActive = existing?.isActive ?? true;
    Uint8List? pickedLogoBytes;
    String? pickedLogoExt;
    bool isSaving = false;

    await showDialog(
      context: context,
      builder: (ctx) {
        return StatefulBuilder(
          builder: (ctx, setDialogState) {
            final currentLogoUrl = logoUrlCtrl.text.trim();
            return AlertDialog(
              insetPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 20),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
              ),
              title: Row(
                children: [
                  Icon(
                    isEdit ? Icons.edit_note_rounded : Icons.add_business_rounded,
                    color: const Color(0xFF1E40AF),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      isEdit
                          ? 'تعديل بيانات المصنع / المورد المعتمد'
                          : 'إضافة مصنع أو مورد معتمد جديد',
                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
                    ),
                  ),
                ],
              ),
              content: SizedBox(
                width: 520,
                child: SingleChildScrollView(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      // Logo Upload / Preview
                      Center(
                        child: GestureDetector(
                          onTap: () async {
                            final picker = ImagePicker();
                            final picked = await picker.pickImage(
                              source: ImageSource.gallery,
                              imageQuality: 70,
                              maxWidth: 512,
                            );
                            if (picked != null) {
                              final bytes = await picked.readAsBytes();
                              final ext = picked.name.split('.').last;
                              setDialogState(() {
                                pickedLogoBytes = bytes;
                                pickedLogoExt = ext;
                              });
                            }
                          },
                          child: Stack(
                            children: [
                              CircleAvatar(
                                radius: 36,
                                backgroundColor: const Color(0xFFEFF6FF),
                                backgroundImage: pickedLogoBytes != null
                                    ? MemoryImage(pickedLogoBytes!)
                                    : (currentLogoUrl.isNotEmpty
                                        ? NetworkImage(currentLogoUrl)
                                        : null) as ImageProvider?,
                                child: pickedLogoBytes == null && currentLogoUrl.isEmpty
                                    ? const Icon(
                                        Icons.add_photo_alternate_outlined,
                                        color: Color(0xFF1E40AF),
                                        size: 28,
                                      )
                                    : null,
                              ),
                              Positioned(
                                bottom: 0,
                                right: 0,
                                child: Container(
                                  padding: const EdgeInsets.all(4),
                                  decoration: const BoxDecoration(
                                    color: Color(0xFF2563EB),
                                    shape: BoxShape.circle,
                                  ),
                                  child: const Icon(Icons.camera_alt_rounded, size: 13, color: Colors.white),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 6),
                      const Text(
                        'شعار المصنع أو الشركة (اختياري - اضغط للرفع)',
                        style: TextStyle(fontSize: 11, color: Colors.blueGrey, fontWeight: FontWeight.w600),
                      ),
                      const SizedBox(height: 14),
                      TextField(
                        controller: nameArCtrl,
                        decoration: const InputDecoration(
                          labelText: 'اسم المصنع أو المورد (بالعربية) *',
                          hintText: 'مثال: شركة سابك للحديد / مصنع أسمنت عطبرة',
                          prefixIcon: Icon(Icons.business_rounded),
                          border: OutlineInputBorder(),
                        ),
                      ),
                      const SizedBox(height: 12),
                      TextField(
                        controller: nameEnCtrl,
                        decoration: const InputDecoration(
                          labelText: 'الاسم بالإنجليزية (اختياري)',
                          hintText: 'e.g. SABIC Steel',
                          prefixIcon: Icon(Icons.language_rounded),
                          border: OutlineInputBorder(),
                        ),
                      ),
                      const SizedBox(height: 12),
                      DropdownButtonFormField<String>(
                        isExpanded: true,
                        initialValue: selectedTypeAr,
                        decoration: const InputDecoration(
                          labelText: 'تصنيف الجهة *',
                          prefixIcon: Icon(Icons.category_rounded),
                          border: OutlineInputBorder(),
                        ),
                        items: _supplierTypes.map((t) {
                          return DropdownMenuItem<String>(
                            value: t['ar']!,
                            child: Text(
                              '${t['ar']} (${t['en']})',
                              overflow: TextOverflow.ellipsis,
                            ),
                          );
                        }).toList(),
                        onChanged: (val) {
                          if (val == null) return;
                          final match = _supplierTypes.firstWhere(
                            (t) => t['ar'] == val,
                            orElse: () => _supplierTypes.first,
                          );
                          setDialogState(() {
                            selectedTypeAr = match['ar']!;
                            selectedTypeEn = match['en']!;
                          });
                        },
                      ),
                      const SizedBox(height: 12),
                      Row(
                        children: [
                          Expanded(
                            flex: 2,
                            child: TextField(
                              controller: cityCtrl,
                              decoration: const InputDecoration(
                                labelText: 'المدينة / المقر',
                                hintText: 'الخرطوم / بورتسودان',
                                prefixIcon: Icon(Icons.location_city_rounded),
                                border: OutlineInputBorder(),
                              ),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            flex: 1,
                            child: TextField(
                              controller: ratingCtrl,
                              keyboardType: const TextInputType.numberWithOptions(
                                decimal: true,
                              ),
                              decoration: const InputDecoration(
                                labelText: 'التقييم (5)',
                                prefixIcon: Icon(Icons.star_rounded, color: Colors.amber),
                                border: OutlineInputBorder(),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      TextField(
                        controller: specialtyCtrl,
                        decoration: const InputDecoration(
                          labelText: 'التخصص / المواد التي يوردها',
                          hintText: 'مثال: حديد تسليح وقطاعات فولاذية معتمدة',
                          prefixIcon: Icon(Icons.inventory_2_outlined),
                          border: OutlineInputBorder(),
                        ),
                      ),
                      const SizedBox(height: 12),
                      TextField(
                        controller: phoneCtrl,
                        textDirection: TextDirection.ltr,
                        keyboardType: TextInputType.phone,
                        decoration: const InputDecoration(
                          labelText: 'رقم التواصل المباشر / واتساب (اختياري)',
                          hintText: '+249 912 300 101',
                          hintTextDirection: TextDirection.ltr,
                          prefixIcon: Icon(Icons.phone_outlined),
                          border: OutlineInputBorder(),
                        ),
                      ),
                      const SizedBox(height: 12),
                      SwitchListTile(
                        contentPadding: EdgeInsets.zero,
                        title: const Text(
                          'تفعيل شارة التوثيق الزرقاء (موثق ✓)',
                          style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.bold),
                        ),
                        subtitle: const Text(
                          'يظهر بجانب اسم المصنع/المورد علامة التوثيق الرسمية',
                          style: TextStyle(fontSize: 11.5),
                        ),
                        value: isVerified,
                        activeThumbColor: const Color(0xFF2563EB),
                        onChanged: (v) => setDialogState(() => isVerified = v),
                      ),
                      SwitchListTile(
                        contentPadding: EdgeInsets.zero,
                        title: const Text(
                          'إظهار في الصفحة الرئيسية للتطبيق',
                          style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.bold),
                        ),
                        subtitle: const Text(
                          'عند إيقافه يتم إخفاؤه مؤقتاً من واجهة المشتري دون حذفه',
                          style: TextStyle(fontSize: 11.5),
                        ),
                        value: isActive,
                        activeThumbColor: const Color(0xFF10B981),
                        onChanged: (v) => setDialogState(() => isActive = v),
                      ),
                    ],
                  ),
                ),
              ),
              actions: [
                TextButton(
                  onPressed: isSaving ? null : () => Navigator.pop(ctx),
                  child: const Text('إلغاء'),
                ),
                FilledButton.icon(
                  onPressed: isSaving
                      ? null
                      : () async {
                          final nameAr = nameArCtrl.text.trim();
                          if (nameAr.isEmpty) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(
                                content: Text('يرجى إدخال اسم المصنع أو المورد بالعربية'),
                                backgroundColor: Colors.redAccent,
                              ),
                            );
                            return;
                          }
                          setDialogState(() => isSaving = true);
                          String? finalLogoUrl = logoUrlCtrl.text.trim().isNotEmpty
                              ? logoUrlCtrl.text.trim()
                              : existing?.logoUrl;
                          if (pickedLogoBytes != null && pickedLogoExt != null) {
                            try {
                              final uploaded = await ref
                                  .read(verifiedSuppliersProvider.notifier)
                                  .uploadSupplierLogo(pickedLogoBytes!, pickedLogoExt!);
                              if (uploaded != null && uploaded.isNotEmpty) {
                                finalLogoUrl = uploaded;
                              }
                            } catch (_) {}
                          }

                          final parsedRating =
                              (double.tryParse(ratingCtrl.text.trim()) ?? 4.9)
                                  .clamp(1.0, 5.0);
                          final model = VerifiedSupplierModel(
                            id: existing?.id ??
                                'sup_${DateTime.now().millisecondsSinceEpoch}',
                            nameAr: nameAr,
                            nameEn: nameEnCtrl.text.trim().isNotEmpty
                                ? nameEnCtrl.text.trim()
                                : nameAr,
                            typeAr: selectedTypeAr,
                            typeEn: selectedTypeEn,
                            rating: parsedRating,
                            city: cityCtrl.text.trim().isNotEmpty
                                ? cityCtrl.text.trim()
                                : 'الخرطوم',
                            specialty: specialtyCtrl.text.trim().isNotEmpty
                                ? specialtyCtrl.text.trim()
                                : 'مواد بناء وتوريدات هندسية',
                            phone: PhoneFormatter.format(phoneCtrl.text),
                            logoUrl: finalLogoUrl,
                            isVerified: isVerified,
                            isActive: isActive,
                            sortOrder: existing?.sortOrder ?? 0,
                          );

                          if (isEdit) {
                            await ref
                                .read(verifiedSuppliersProvider.notifier)
                                .updateSupplier(model);
                          } else {
                            await ref
                                .read(verifiedSuppliersProvider.notifier)
                                .addSupplier(model);
                          }
                          if (ctx.mounted) Navigator.pop(ctx);
                          if (context.mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(
                                  isEdit
                                      ? 'تم تحديث بيانات «$nameAr» بنجاح'
                                      : 'تمت إضافة «$nameAr» إلى قائمة المعتمدين بنجاح',
                                ),
                                backgroundColor: const Color(0xFF059669),
                              ),
                            );
                          }
                        },
                  icon: isSaving
                      ? const SizedBox(
                          width: 16,
                          height: 16,
                          child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                        )
                      : Icon(isEdit ? Icons.save_rounded : Icons.add_rounded),
                  label: Text(isSaving ? 'جارٍ الحفظ...' : (isEdit ? 'حفظ التعديلات' : 'إضافة واعتماد')),
                ),
              ],
            );
          },
        );
      },
    );
  }

  Future<void> _confirmDelete(
    BuildContext context,
    VerifiedSupplierModel supplier,
  ) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('تأكيد الحذف'),
        content: Text(
          'هل أنت متأكد من حذف «${supplier.nameAr}» من قائمة المصانع والموردين المعتمدين؟',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('إلغاء'),
          ),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: const Color(0xFFDC2626)),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('حذف نهائياً'),
          ),
        ],
      ),
    );

    if (confirmed == true) {
      await ref
          .read(verifiedSuppliersProvider.notifier)
          .deleteSupplier(supplier.id);
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('تم حذف «${supplier.nameAr}» بنجاح'),
            backgroundColor: Colors.redAccent,
          ),
        );
      }
    }
  }

  Future<void> _confirmResetDefaults(BuildContext context) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('استعادة القائمة الافتراضية'),
        content: const Text(
          'هل ترغب في استعادة المصانع والموردين الافتراضيين الأربعة (شركة سابك للحديد، حديد الراجحي، أسمنت اليمامة، مستودعات السجانة)؟',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('إلغاء'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('نعم، استعادة'),
          ),
        ],
      ),
    );

    if (confirmed == true) {
      await ref.read(verifiedSuppliersProvider.notifier).resetToDefaults();
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('تمت استعادة القائمة الافتراضية بنجاح'),
            backgroundColor: Color(0xFF1E40AF),
          ),
        );
      }
    }
  }

  IconData _getIconForType(String typeAr) {
    if (typeAr.contains('مصنع')) return Icons.factory_rounded;
    if (typeAr.contains('مجمع')) return Icons.warehouse_rounded;
    if (typeAr.contains('وكيل')) return Icons.workspace_premium_rounded;
    if (typeAr.contains('مقاولات') || typeAr.contains('خرسان')) return Icons.domain_rounded;
    if (typeAr.contains('تشطيبات')) return Icons.format_paint_rounded;
    if (typeAr.contains('هندسية')) return Icons.architecture_rounded;
    return Icons.storefront_rounded;
  }

  Color _getColorForType(String typeAr) {
    if (typeAr.contains('مصنع معتمد')) return const Color(0xFF1D4ED8);
    if (typeAr.contains('مصنع وطني')) return const Color(0xFF047857);
    if (typeAr.contains('مجمع')) return const Color(0xFF7C3AED);
    if (typeAr.contains('وكيل')) return const Color(0xFFB45309);
    if (typeAr.contains('مقاولات') || typeAr.contains('خرسان')) return const Color(0xFF0F766E);
    if (typeAr.contains('تشطيبات')) return const Color(0xFFBE185D);
    return const Color(0xFF0369A1);
  }
}

class _KpiItem {
  final String title;
  final String value;
  final String subtitle;
  final IconData icon;
  final List<Color> colors;

  const _KpiItem({
    required this.title,
    required this.value,
    required this.subtitle,
    required this.icon,
    required this.colors,
  });
}
