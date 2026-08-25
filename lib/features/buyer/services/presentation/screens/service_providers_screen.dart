import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter/material.dart';
import 'package:magaiees_marketplace/features/buyer/services/domain/models/service_provider_model.dart';
import 'package:magaiees_marketplace/features/buyer/services/application/services_provider.dart';

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

    var filtered = _allProviders;
    if (categoryFilter != null) {
      filtered = filtered.where((p) => p.categoryId == categoryFilter).toList();
    }

    // Apply sub-trade filter
    if (_selectedSubTrade != 'all') {
      filtered = filtered.where((p) {
        final lower = (p.trade + ' ' + p.tradeAr).toLowerCase();
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
                  color: Colors.black.withOpacity(0.15),
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
                    color: Colors.white.withOpacity(0.2),
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
                        style: TextStyle(color: Colors.white.withOpacity(0.9), fontSize: 11.5),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),

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
                                backgroundColor: _getDomainColor(p.categoryId).withOpacity(0.12),
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
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      SnackBar(content: Text('${isRtl ? 'الاتصال بـ' : 'Calling'} $name (${p.phone})')),
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
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      SnackBar(content: Text('${isRtl ? 'فتح واتساب مع' : 'Opening WhatsApp with'} $name')),
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
        selectedColor: Theme.of(context).colorScheme.primary.withOpacity(0.2),
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
}