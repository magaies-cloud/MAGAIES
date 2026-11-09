import 'dart:typed_data';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:magaiees_marketplace/features/buyer/services/domain/models/service_provider_model.dart';
import 'package:magaiees_marketplace/features/buyer/services/application/services_provider.dart';
import 'package:magaiees_marketplace/core/utils/app_notification.dart';

class ServiceProvidersScreen extends ConsumerStatefulWidget {
  const ServiceProvidersScreen({super.key});

  @override
  ConsumerState<ServiceProvidersScreen> createState() => _ServiceProvidersScreenState();
}

class _ServiceProvidersScreenState extends ConsumerState<ServiceProvidersScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  String _selectedSubTrade = 'all';
  String _searchQuery = '';

  List<ServiceProviderModel> _allProviders = [];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _tabController.addListener(() {
      if (_tabController.indexIsChanging) return;
      setState(() {
        _selectedSubTrade = 'all';
      });
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final servicesAsync = ref.watch(servicesProvider);
    _allProviders = servicesAsync.valueOrNull ?? [];

    final isRtl = Directionality.of(context) == TextDirection.rtl;

    return Scaffold(
      appBar: AppBar(
        title: Text(isRtl ? 'دليل الخدمات والمقاولات والنقل' : 'Services & Fleet Logistics'),
        centerTitle: true,
        bottom: TabBar(
          controller: _tabController,
          isScrollable: true,
          labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
          tabs: [
            Tab(text: isRtl ? 'جميع الخدمات' : 'All Services'),
            Tab(
              icon: const Icon(Icons.architecture, size: 18),
              text: isRtl ? 'الاستشارات الهندسية' : 'Engineering',
            ),
            Tab(
              icon: const Icon(Icons.handyman, size: 18),
              text: isRtl ? 'العمالة الفنية' : 'Skilled Labor',
            ),
            Tab(
              icon: const Icon(Icons.local_shipping, size: 18),
              text: isRtl ? 'أسطول النقل والتعتيل' : 'Transport & Logistics',
            ),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildProvidersList(context, null), // All
          _buildProvidersList(context, 'engineering'),
          _buildProvidersList(context, 'labor'),
          _buildProvidersList(context, 'logistics'),
        ],
      ),
    );
  }

  Widget _buildProvidersList(BuildContext context, String? categoryFilter) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    // Only show approved and verified providers to the public
    var filtered = _allProviders.where((p) => p.isVerified).toList();
    if (categoryFilter != null) {
      filtered = filtered.where((p) => p.categoryId == categoryFilter).toList();
    }

    // Apply sub-trade filter
    if (_selectedSubTrade != 'all') {
      filtered = filtered.where((p) {
        final lower = '${p.trade} ${p.tradeAr}'.toLowerCase();
        return lower.contains(_selectedSubTrade.toLowerCase());
      }).toList();
    }

    // Apply search query
    if (_searchQuery.isNotEmpty) {
      filtered = filtered.where((p) {
        final query = _searchQuery.toLowerCase();
        return p.name.toLowerCase().contains(query) ||
               p.nameAr.toLowerCase().contains(query) ||
               p.tradeAr.toLowerCase().contains(query) ||
               p.locationAr.toLowerCase().contains(query);
      }).toList();
    }

    return CustomScrollView(
      slivers: [
        // Domain Banner
        SliverToBoxAdapter(
          child: Container(
            margin: const EdgeInsets.all(16.0),
            padding: const EdgeInsets.all(18.0),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: _getBannerGradient(categoryFilter),
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.15),
                  blurRadius: 8,
                  offset: const Offset(0, 3),
                ),
              ],
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.2),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    _getBannerIcon(categoryFilter),
                    size: 32,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _getBannerTitle(categoryFilter, isRtl),
                        style: const TextStyle(color: Colors.white, fontSize: 17, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 3),
                      Text(
                        _getBannerSubtitle(categoryFilter, isRtl),
                        style: TextStyle(color: Colors.white.withValues(alpha: 0.9), fontSize: 11.5),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),

        // Call-to-action for Join as Provider
        SliverToBoxAdapter(
          child: Container(
            margin: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 4.0),
            padding: const EdgeInsets.all(14.0),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: const Color(0xFFE2E8F0), width: 1.2),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.03),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: const Color(0xFFEFF6FF),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.engineering_rounded, color: Color(0xFF2563EB), size: 24),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        isRtl ? 'مهندس، فني، أو صاحب شاحنة نقل؟' : 'Engineer, Technician or Fleet Owner?',
                        style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13.5, color: Color(0xFF0F172A)),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        isRtl ? 'انضم لشبكة مقاييس المعتمدة لتصلك طلبات المشروعات والمواد' : 'Join Magaiees certified network for direct customer orders',
                        style: const TextStyle(color: Color(0xFF64748B), fontSize: 11.5),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                FilledButton.icon(
                  style: FilledButton.styleFrom(
                    backgroundColor: const Color(0xFF2563EB),
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  onPressed: () => _showJoinApplicationDialog(context, isRtl),
                  icon: const Icon(Icons.add_circle_outline, size: 16),
                  label: Text(
                    isRtl ? 'طلب انضمام' : 'Apply',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                  ),
                ),
              ],
            ),
          ),
        ),
        const SliverToBoxAdapter(child: SizedBox(height: 10)),

        // Subcategory Chips (Especially for Logistics / Fleet sub-classification)
        if (categoryFilter == 'logistics')
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.only(bottom: 12.0),
              child: SizedBox(
                height: 42,
                child: ListView(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.symmetric(horizontal: 12.0),
                  children: [
                    _buildSubChip('all', isRtl ? 'الكل' : 'All Fleet', isRtl),
                    _buildSubChip('الدفارات', isRtl ? '🚚 الدفارات (حمولات متوسطة وبوهيات)' : '🚚 Dyna (Medium Cargo)', isRtl),
                    _buildSubChip('مسطحة', isRtl ? '🚛 الجرارات والمسطحات (للسيخ والمواسير 12م)' : '🚛 Flatbed (Rebar & Long Cargo)', isRtl),
                    _buildSubChip('القلابات', isRtl ? '🚜 القلابات (للرمل والخرسانة والحصى)' : '🚜 Dump Trucks (Sand & Bulk)', isRtl),
                    _buildSubChip('تعتيل', isRtl ? '👷‍♂️ عمالة التعتيل والتنزيل والتشوين' : '👷‍♂️ Offloading Crews', isRtl),
                  ],
                ),
              ),
            ),
          )
        else if (categoryFilter == 'labor')
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.only(bottom: 12.0),
              child: SizedBox(
                height: 42,
                child: ListView(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.symmetric(horizontal: 12.0),
                  children: [
                    _buildSubChip('all', isRtl ? 'الكل' : 'All Trades', isRtl),
                    _buildSubChip('حداد', isRtl ? 'حدادين مسلح' : 'Steel Fixers', isRtl),
                    _buildSubChip('نجار', isRtl ? 'نجارين مسلح' : 'Carpenters', isRtl),
                    _buildSubChip('بناء', isRtl ? 'بنائين ومليسين' : 'Masons', isRtl),
                    _buildSubChip('سباك', isRtl ? 'سباكين' : 'Plumbers', isRtl),
                    _buildSubChip('كهرباء', isRtl ? 'كهربائيين' : 'Electricians', isRtl),
                    _buildSubChip('دهان', isRtl ? 'دهانين' : 'Painters', isRtl),
                  ],
                ),
              ),
            ),
          )
        else if (categoryFilter == 'engineering')
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.only(bottom: 12.0),
              child: SizedBox(
                height: 42,
                child: ListView(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.symmetric(horizontal: 12.0),
                  children: [
                    _buildSubChip('all', isRtl ? 'الكل' : 'All Engineering', isRtl),
                    _buildSubChip('إنشائي', isRtl ? 'مهندسين إنشائيين وإشراف صب' : 'Structural Engineers', isRtl),
                    _buildSubChip('معماري', isRtl ? 'تصميم معماري ومساحة' : 'Architects & Surveyors', isRtl),
                    _buildSubChip('كميات', isRtl ? 'حساب كميات (BOQ)' : 'Quantity Surveyors', isRtl),
                  ],
                ),
              ),
            ),
          ),

        // Search Field
        SliverToBoxAdapter(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16.0),
            child: TextField(
              onChanged: (val) => setState(() => _searchQuery = val.trim()),
              decoration: InputDecoration(
                hintText: isRtl ? 'ابحث باسم الشاحنة، المهندس، الحرفي، أو المنطقة...' : 'Search by truck, engineer, trade, or area...',
                prefixIcon: const Icon(Icons.search),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              ),
            ),
          ),
        ),

        const SliverToBoxAdapter(child: SizedBox(height: 16)),

        // Providers Cards
        if (filtered.isEmpty)
          SliverFillRemaining(
            hasScrollBody: false,
            child: Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.local_shipping_outlined, size: 64, color: Colors.grey.shade400),
                  const SizedBox(height: 16),
                  Text(
                    isRtl ? 'لا توجد مركبات أو خدمات مطابقة للبحث' : 'No fleet or services found',
                    style: const TextStyle(fontSize: 16, color: Colors.grey),
                  ),
                ],
              ),
            ),
          )
        else
          SliverPadding(
            padding: const EdgeInsets.symmetric(horizontal: 16.0),
            sliver: SliverList(
              delegate: SliverChildBuilderDelegate(
                (context, index) {
                  final p = filtered[index];
                  final name = isRtl ? p.nameAr : p.name;
                  final trade = isRtl ? p.tradeAr : p.trade;
                  final location = isRtl ? p.locationAr : p.location;

                  return Card(
                    margin: const EdgeInsets.only(bottom: 14.0),
                    elevation: 2,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    child: Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              CircleAvatar(
                                radius: 26,
                                backgroundColor: _getDomainColor(p.categoryId).withValues(alpha: 0.12),
                                child: Icon(
                                  _getTradeIcon(p.categoryId, trade),
                                  color: _getDomainColor(p.categoryId),
                                  size: 28,
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
                                            name,
                                            style: const TextStyle(fontSize: 15.5, fontWeight: FontWeight.bold),
                                          ),
                                        ),
                                        if (p.isVerified) ...[
                                          const SizedBox(width: 4),
                                          const Icon(Icons.verified, color: Colors.blue, size: 18),
                                        ],
                                      ],
                                    ),
                                    const SizedBox(height: 3),
                                    Text(
                                      trade,
                                      style: TextStyle(
                                        color: _getDomainColor(p.categoryId),
                                        fontSize: 12.5,
                                        fontWeight: FontWeight.w600,
                                        height: 1.3,
                                      ),
                                    ),
                                    const SizedBox(height: 6),
                                    Row(
                                      children: [
                                        const Icon(Icons.location_on_outlined, size: 14, color: Colors.grey),
                                        const SizedBox(width: 2),
                                        Expanded(
                                          child: Text(
                                            location,
                                            style: const TextStyle(fontSize: 11.5, color: Colors.grey),
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ),
                                        const Icon(Icons.star, size: 15, color: Colors.amber),
                                        Text(
                                          ' ${p.rating} (${p.reviewsCount})',
                                          style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),

                          const Divider(height: 20),

                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      isRtl ? 'التسعيرة وحمولة الرد' : 'Rate & Payload',
                                      style: const TextStyle(fontSize: 11, color: Colors.grey),
                                    ),
                                    Text(
                                      p.priceEstimate,
                                      style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold, color: Colors.green),
                                    ),
                                  ],
                                ),
                              ),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: [
                                  Text(
                                    isRtl ? 'الخبرة الميدانية' : 'Experience',
                                    style: const TextStyle(fontSize: 11, color: Colors.grey),
                                  ),
                                  Text(
                                    '${p.experienceYears} ${isRtl ? 'سنوات' : 'years'}',
                                    style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold),
                                  ),
                                ],
                              ),
                            ],
                          ),

                          const SizedBox(height: 14),

                          Row(
                            children: [
                              Expanded(
                                child: OutlinedButton.icon(
                                  onPressed: () {
                                    AppNotification.showInfo(
                                      context,
                                      '${isRtl ? 'الاتصال بـ' : 'Calling'} $name (${p.phone})',
                                      icon: Icons.call,
                                    );
                                  },
                                  icon: const Icon(Icons.call, size: 18),
                                  label: Text(isRtl ? 'اتصال مباشر' : 'Call'),
                                ),
                              ),
                              const SizedBox(width: 8),
                              Expanded(
                                child: FilledButton.icon(
                                  style: FilledButton.styleFrom(backgroundColor: const Color(0xFF25D366)),
                                  onPressed: () {
                                    AppNotification.showInfo(
                                      context,
                                      '${isRtl ? 'فتح واتساب مع' : 'Opening WhatsApp with'} $name',
                                      icon: Icons.chat_bubble_outline,
                                    );
                                  },
                                  icon: const Icon(Icons.chat_bubble_outline, size: 18),
                                  label: Text(isRtl ? 'طلب عبر واتساب' : 'WhatsApp'),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  );
                },
                childCount: filtered.length,
              ),
            ),
          ),

        const SliverToBoxAdapter(child: SizedBox(height: 32)),
      ],
    );
  }

  Widget _buildSubChip(String key, String label, bool isRtl) {
    final isSelected = _selectedSubTrade == key;
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 4.0),
      child: FilterChip(
        selected: isSelected,
        label: Text(label, style: TextStyle(fontSize: 12, fontWeight: isSelected ? FontWeight.bold : FontWeight.normal)),
        selectedColor: Theme.of(context).colorScheme.primary.withValues(alpha: 0.2),
        checkmarkColor: Theme.of(context).colorScheme.primary,
        onSelected: (_) {
          setState(() => _selectedSubTrade = key);
        },
      ),
    );
  }

  List<Color> _getBannerGradient(String? category) {
    switch (category) {
      case 'engineering':
        return [const Color(0xFF1E3A8A), const Color(0xFF0284C7)];
      case 'labor':
        return [const Color(0xFF92400E), const Color(0xFFD97706)];
      case 'logistics':
        return [const Color(0xFF065F46), const Color(0xFF059669)];
      default:
        return [const Color(0xFF0F172A), const Color(0xFF1E3A8A)];
    }
  }

  IconData _getBannerIcon(String? category) {
    switch (category) {
      case 'engineering': return Icons.architecture;
      case 'labor': return Icons.handyman;
      case 'logistics': return Icons.local_shipping;
      default: return Icons.engineering;
    }
  }

  String _getBannerTitle(String? category, bool isRtl) {
    switch (category) {
      case 'engineering':
        return isRtl ? 'الاستشارات الهندسية والإشراف' : 'Engineering Consulting & Supervision';
      case 'labor':
        return isRtl ? 'العمالة الفنية والحرفية المتخصصة' : 'Skilled Tradesmen & Construction Labor';
      case 'logistics':
        return isRtl ? 'أسطول النقل الثقيل، الدفارات، والتعتيل' : 'Heavy Truck Fleets, Dyna & Offloading';
      default:
        return isRtl ? 'دليل مقاولي وأساطيل منصة مقاييس' : 'Magaiees Services & Fleet Directory';
    }
  }

  String _getBannerSubtitle(String? category, bool isRtl) {
    switch (category) {
      case 'engineering':
        return isRtl ? 'مهندسون إنشائيون، معماريون، ومساحين لاستلام الصب والمخططات' : 'Structural, architectural & surveying certified engineers';
      case 'labor':
        return isRtl ? 'حدادين، نجارين، بنائين، سباكين، وكهربائيين معتمدين' : 'Certified steel fixers, carpenters, masons, plumbers & electricians';
      case 'logistics':
        return isRtl ? 'الدفارات للتشطيبات • المسطحات لسيخ 12م • القلابات للمواد السائبة • فرق التعتيل' : 'Dyna for finishes • Flatbed for 12m rebar • Dump trucks for bulk • Offloading crews';
      default:
        return isRtl ? 'تواصل مباشرة مع أصحاب الشاحنات، المهندسين، والحرفيين' : 'Direct contact with truck fleets, engineers & skilled masons';
    }
  }

  Color _getDomainColor(String category) {
    switch (category) {
      case 'engineering': return const Color(0xFF1E40AF);
      case 'labor': return const Color(0xFFD97706);
      case 'logistics': return const Color(0xFF059669);
      default: return const Color(0xFF3B82F6);
    }
  }

  IconData _getTradeIcon(String category, String trade) {
    if (category == 'logistics') {
      if (trade.contains('الدفارات') || trade.contains('Dyna')) return Icons.local_shipping;
      if (trade.contains('مسطحة') || trade.contains('Flatbed')) return Icons.rv_hookup;
      if (trade.contains('القلابات') || trade.contains('Dump')) return Icons.agriculture;
      if (trade.contains('تعتيل')) return Icons.groups_2;
      return Icons.local_shipping;
    }
    if (category == 'engineering') return Icons.architecture;
    return Icons.handyman;
  }

  Future<void> _showJoinApplicationDialog(BuildContext context, bool isRtl) async {
    final formKey = GlobalKey<FormState>();
    final nameArCtrl = TextEditingController();
    final tradeArCtrl = TextEditingController();
    final phoneCtrl = TextEditingController();
    final locationArCtrl = TextEditingController();
    final experienceCtrl = TextEditingController(text: '3');
    final priceCtrl = TextEditingController();

    String selectedCategory = 'technicians';
    Uint8List? avatarBytes;
    String? avatarExtension;
    bool isSubmitting = false;

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
                insetPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
                clipBehavior: Clip.antiAlias,
                child: Container(
                  width: 650,
                  constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * 0.88),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      // Header
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 18),
                        decoration: const BoxDecoration(
                          gradient: LinearGradient(
                            colors: [Color(0xFF0F2E4A), Color(0xFF1E40AF)],
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
                              child: const Icon(Icons.badge_rounded, color: Colors.white, size: 24),
                            ),
                            const SizedBox(width: 14),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    isRtl ? 'طلب انضمام مقدم خدمة / مهندس / أسطول نقل' : 'Join as Certified Service Provider',
                                    style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 16, color: Colors.white),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    isRtl ? 'سجل بياناتك وسيتم مراجعتها واعتمادها من إدارة المنصة' : 'Submit your details for verification and approval by Magaiees Admin',
                                    style: TextStyle(color: Colors.white.withValues(alpha: 0.85), fontSize: 11.5),
                                  ),
                                ],
                              ),
                            ),
                            IconButton(
                              icon: const Icon(Icons.close, color: Colors.white),
                              onPressed: isSubmitting ? null : () => Navigator.pop(dialogCtx),
                            ),
                          ],
                        ),
                      ),

                      // Body Form
                      Expanded(
                        child: SingleChildScrollView(
                          padding: const EdgeInsets.all(22),
                          child: Form(
                            key: formKey,
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                // Photo Picker
                                Center(
                                  child: GestureDetector(
                                    onTap: () async {
                                      final picker = ImagePicker();
                                      final picked = await picker.pickImage(source: ImageSource.gallery, imageQuality: 60, maxWidth: 600);
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
                                          radius: 42,
                                          backgroundColor: const Color(0xFFEFF6FF),
                                          backgroundImage: avatarBytes != null ? MemoryImage(avatarBytes!) : null,
                                          child: avatarBytes == null
                                              ? const Icon(Icons.add_a_photo_outlined, size: 32, color: Color(0xFF2563EB))
                                              : null,
                                        ),
                                        Positioned(
                                          bottom: 0,
                                          right: 0,
                                          child: Container(
                                            padding: const EdgeInsets.all(4),
                                            decoration: const BoxDecoration(color: Colors.white, shape: BoxShape.circle),
                                            child: Container(
                                              padding: const EdgeInsets.all(5),
                                              decoration: const BoxDecoration(color: Color(0xFF2563EB), shape: BoxShape.circle),
                                              child: const Icon(Icons.camera_alt, size: 13, color: Colors.white),
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                                const SizedBox(height: 6),
                                Center(
                                  child: Text(
                                    isRtl ? 'صورة شخصية / رخصة / شعار الشاحنة (اختياري)' : 'Photo / License / Vehicle photo (Optional)',
                                    style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontWeight: FontWeight.bold),
                                  ),
                                ),
                                const SizedBox(height: 18),

                                // Full Name
                                TextFormField(
                                  controller: nameArCtrl,
                                  decoration: InputDecoration(
                                    labelText: isRtl ? 'الاسم الكامل أو اسم المنشأة / الأسطول *' : 'Full Name or Business Name *',
                                    hintText: isRtl ? 'مثال: م. مصطفى كمال أو أسطول الوفاق للنقل' : 'e.g. Eng. Kamal or Al-Wifaq Fleet',
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                  ),
                                  validator: (v) => (v == null || v.trim().isEmpty) ? (isRtl ? 'يرجى إدخال الاسم' : 'Please enter name') : null,
                                ),
                                const SizedBox(height: 14),

                                // Category Dropdown
                                DropdownButtonFormField<String>(
                                  initialValue: selectedCategory,
                                  decoration: InputDecoration(
                                    labelText: isRtl ? 'القطاع والتصنيف الرئيسي *' : 'Primary Sector *',
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                  ),
                                  items: [
                                    DropdownMenuItem(
                                      value: 'technicians',
                                      child: Text(isRtl ? '⚡ فنيون ومهنيون (كهربائي / سباك / نجار / حداد)' : 'Electrician / Plumber / Tradesman'),
                                    ),
                                    DropdownMenuItem(
                                      value: 'engineering',
                                      child: Text(isRtl ? '🏗️ استشارات وتصميم هندسي (إنشائي / معماري / مساح)' : 'Engineering & Architectural'),
                                    ),
                                    DropdownMenuItem(
                                      value: 'logistics',
                                      child: Text(isRtl ? '🚚 أساطيل النقل واللوجستيات (دفار / قلاب / تريلا)' : 'Trucking, Dyna & Dump Logistics'),
                                    ),
                                    DropdownMenuItem(
                                      value: 'labor',
                                      child: Text(isRtl ? '🔨 مقاولات وعمالة تنفيذية (بناء / مليس / تعتيل)' : 'Construction Labor & Offloading'),
                                    ),
                                  ],
                                  onChanged: (val) {
                                    if (val != null) setStateDialog(() => selectedCategory = val);
                                  },
                                ),
                                const SizedBox(height: 14),

                                // Specific Trade
                                TextFormField(
                                  controller: tradeArCtrl,
                                  decoration: InputDecoration(
                                    labelText: isRtl ? 'التخصص الدقيق وطبيعة العمل *' : 'Exact Trade / Specialty *',
                                    hintText: isRtl ? 'مثال: فني تمديدات كهربائية ثلاثية الطور أو قلاب 20 متر رمل' : 'e.g. 3-Phase Electrician or 20m Dump Truck',
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                  ),
                                  validator: (v) => (v == null || v.trim().isEmpty) ? (isRtl ? 'يرجى إدخال التخصص الدقيق' : 'Please enter specialty') : null,
                                ),
                                const SizedBox(height: 14),

                                // Phone & Location
                                Row(
                                  children: [
                                    Expanded(
                                      child: TextFormField(
                                        controller: phoneCtrl,
                                        keyboardType: TextInputType.phone,
                                        decoration: InputDecoration(
                                          labelText: isRtl ? 'رقم الهاتف المباشر (واتساب/اتصال) *' : 'Direct Phone *',
                                          hintText: '09xxxxxxxxx',
                                          prefixIcon: const Icon(Icons.phone_rounded, size: 18),
                                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                          contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                        ),
                                        validator: (v) => (v == null || v.trim().length < 8) ? (isRtl ? 'رقم هاتف غير صالح' : 'Invalid phone') : null,
                                      ),
                                    ),
                                    const SizedBox(width: 12),
                                    Expanded(
                                      child: TextFormField(
                                        controller: locationArCtrl,
                                        decoration: InputDecoration(
                                          labelText: isRtl ? 'المدينة ونطاق التغطية *' : 'Coverage City / Area *',
                                          hintText: isRtl ? 'مثال: السجانة والخرطوم وبحري' : 'e.g. Khartoum & Bahri',
                                          prefixIcon: const Icon(Icons.location_on_outlined, size: 18),
                                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                          contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                        ),
                                        validator: (v) => (v == null || v.trim().isEmpty) ? (isRtl ? 'يرجى إدخال النطاق الجغرافي' : 'Please enter location') : null,
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 14),

                                // Experience & Price
                                Row(
                                  children: [
                                    Expanded(
                                      child: TextFormField(
                                        controller: experienceCtrl,
                                        keyboardType: TextInputType.number,
                                        decoration: InputDecoration(
                                          labelText: isRtl ? 'سنوات الخبرة الميدانية *' : 'Years of Experience *',
                                          hintText: '5',
                                          suffixText: isRtl ? 'سنوات' : 'yrs',
                                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                          contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                        ),
                                        validator: (v) => (v == null || v.trim().isEmpty) ? (isRtl ? 'يرجى إدخال سنوات الخبرة' : 'Enter years') : null,
                                      ),
                                    ),
                                    const SizedBox(width: 12),
                                    Expanded(
                                      child: TextFormField(
                                        controller: priceCtrl,
                                        decoration: InputDecoration(
                                          labelText: isRtl ? 'التسعيرة التقديرية للخدمة *' : 'Estimated Pricing *',
                                          hintText: isRtl ? 'مثال: حسب الكشف أو 40,000 ج.س' : 'e.g. Per inspection / 40,000 SDG',
                                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                          contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                        ),
                                        validator: (v) => (v == null || v.trim().isEmpty) ? (isRtl ? 'يرجى إدخال التسعيرة التقديرية' : 'Enter price estimate') : null,
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),

                      // Footer Actions
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 14),
                        decoration: const BoxDecoration(
                          color: Color(0xFFF8FAFC),
                          border: Border(top: BorderSide(color: Color(0xFFE2E8F0))),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.end,
                          children: [
                            TextButton(
                              onPressed: isSubmitting ? null : () => Navigator.pop(dialogCtx),
                              child: Text(isRtl ? 'إلغاء' : 'Cancel'),
                            ),
                            const SizedBox(width: 12),
                            FilledButton.icon(
                              style: FilledButton.styleFrom(
                                backgroundColor: const Color(0xFF2563EB),
                                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              ),
                              onPressed: isSubmitting
                                  ? null
                                  : () async {
                                      if (formKey.currentState!.validate()) {
                                        setStateDialog(() => isSubmitting = true);
                                        try {
                                          await ref.read(servicesProvider.notifier).submitProviderApplication(
                                                nameAr: nameArCtrl.text.trim(),
                                                tradeAr: tradeArCtrl.text.trim(),
                                                categoryId: selectedCategory,
                                                phone: phoneCtrl.text.trim(),
                                                locationAr: locationArCtrl.text.trim(),
                                                experienceYears: experienceCtrl.text.trim(),
                                                priceEstimate: priceCtrl.text.trim(),
                                                avatarBytes: avatarBytes,
                                                avatarExtension: avatarExtension,
                                              );

                                          if (dialogCtx.mounted) Navigator.pop(dialogCtx);

                                          if (context.mounted) {
                                            showDialog(
                                              context: context,
                                              builder: (succCtx) => Directionality(
                                                textDirection: TextDirection.rtl,
                                                child: AlertDialog(
                                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                                                  title: const Row(
                                                    children: [
                                                      Icon(Icons.check_circle_rounded, color: Color(0xFF059669), size: 28),
                                                      SizedBox(width: 10),
                                                      Text('تم استلام طلب انضمامك بنجاح!', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16)),
                                                    ],
                                                  ),
                                                  content: const Text(
                                                    'شكراً لاهتمامك بالانضمام إلى شبكة مزودي مقاييس المعتمدين.\n\nسيقوم فريق إدارة التطبيق بمراجعة بياناتك والتحقق من رقم الهاتف، وسيتم اعتماد وتفعيل ملفك الشخصي في الدليل الرسمي فور اكتمال التدقيق.',
                                                    style: TextStyle(fontSize: 13, height: 1.5, color: Color(0xFF334155)),
                                                  ),
                                                  actions: [
                                                    FilledButton(
                                                      style: FilledButton.styleFrom(backgroundColor: const Color(0xFF059669)),
                                                      onPressed: () => Navigator.pop(succCtx),
                                                      child: const Text('حسناً، شكراً لكم'),
                                                    ),
                                                  ],
                                                ),
                                              ),
                                            );
                                          }
                                        } catch (err) {
                                          setStateDialog(() => isSubmitting = false);
                                          if (context.mounted) {
                                            ScaffoldMessenger.of(context).showSnackBar(
                                              SnackBar(content: Text('تعذر إرسال الطلب: $err'), backgroundColor: Colors.red),
                                            );
                                          }
                                        }
                                      }
                                    },
                              icon: isSubmitting
                                  ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                                  : const Icon(Icons.send_rounded, size: 17),
                              label: Text(
                                isSubmitting ? (isRtl ? 'جارٍ الإرسال...' : 'Sending...') : (isRtl ? 'إرسال طلب الانضمام' : 'Submit Application'),
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
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
}