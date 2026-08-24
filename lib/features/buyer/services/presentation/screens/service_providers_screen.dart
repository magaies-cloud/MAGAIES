import 'package:flutter/material.dart';
import 'package:magaiees_marketplace/features/buyer/services/domain/models/service_provider_model.dart';

class ServiceProvidersScreen extends StatefulWidget {
  const ServiceProvidersScreen({super.key});

  @override
  State<ServiceProvidersScreen> createState() => _ServiceProvidersScreenState();
}

class _ServiceProvidersScreenState extends State<ServiceProvidersScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  String _selectedSubTrade = 'all';
  String _searchQuery = '';

  final List<ServiceProviderModel> _allProviders = const [
    // === 1. الاستشارات الهندسية (Engineering Consulting) ===
    ServiceProviderModel(
      id: 'eng1',
      name: 'Eng. Khalid Al-Mansour (Structural)',
      nameAr: 'م. خالد المنصور - استشارات إنشائية وإشراف صب',
      trade: 'Senior Structural Engineer',
      tradeAr: 'مهندس إنشائي معتمد - مراجعة مخططات واستلام صب',
      categoryId: 'engineering',
      rating: 4.95,
      reviewsCount: 64,
      experienceYears: '16',
      priceEstimate: '50,000 ج.س / زيارة إشراف واستلام',
      phone: '+249 91 223 3445',
      location: 'Khartoum - Central',
      locationAr: 'الخرطوم وأم درمان وبحري',
      isVerified: true,
    ),
    ServiceProviderModel(
      id: 'eng2',
      name: 'Dar Al-Binaa Architectural Design',
      nameAr: 'مكتب دار البناء للتصميم المعماري والمساحة',
      trade: 'Architectural & Surveying Office',
      tradeAr: 'تصميم معماري، رخص بناء، ورفع مساحي دقيق',
      categoryId: 'engineering',
      rating: 4.88,
      reviewsCount: 48,
      experienceYears: '12',
      priceEstimate: 'حسب مساحة المخطط / 800 ج.س للمتر',
      phone: '+249 92 334 4556',
      location: 'Khartoum - Riyadh',
      locationAr: 'الخرطوم - شارع الستين والرياض',
      isVerified: true,
    ),
    ServiceProviderModel(
      id: 'eng3',
      name: 'Eng. Yasser Quantity Surveying',
      nameAr: 'م. ياسر لحساب كميات المشاريع وجداول الكميات (BOQ)',
      trade: 'Quantity Surveyor (QS)',
      tradeAr: 'حساب كميات الحديد والخرسانة وتدقيق تكاليف المقاولين',
      categoryId: 'engineering',
      rating: 4.9,
      reviewsCount: 37,
      experienceYears: '10',
      priceEstimate: '35,000 ج.س / جدول كميات كامل للمبنى',
      phone: '+249 90 445 5667',
      location: 'Bahri',
      locationAr: 'بحري وكافوري',
      isVerified: true,
    ),

    // === 2. العمالة الفنية والحرفية (Technical & Skilled Labor) ===
    ServiceProviderModel(
      id: 'sp1',
      name: 'Othman Steel Fixing Team',
      nameAr: 'معلم عثمان وفريقه للحدادة المسلحة',
      trade: 'Steel Fixer / Blacksmith',
      tradeAr: 'حداد مسلح وقص وتثبيت حديد طبقا للمخطط',
      categoryId: 'labor',
      rating: 4.9,
      reviewsCount: 38,
      experienceYears: '12',
      priceEstimate: '15,000 ج.س / طن مصنعية',
      phone: '+249 91 122 3344',
      location: 'Khartoum - El Sajana',
      locationAr: 'الخرطوم - السجانة والمقرن',
      isVerified: true,
    ),
    ServiceProviderModel(
      id: 'sp2',
      name: 'Master Carpenter Adam',
      nameAr: 'الأسطى آدم لأعمال النجارة المسلحة والصب',
      trade: 'Formwork Carpenter',
      tradeAr: 'نجار مسلح وقوالب خرسانية وشدات أعمدة وسقف',
      categoryId: 'labor',
      rating: 4.8,
      reviewsCount: 29,
      experienceYears: '15',
      priceEstimate: '2,500 ج.س / متر مربع نجارة',
      phone: '+249 92 445 6677',
      location: 'Omdurman - Industrial Area',
      locationAr: 'أم درمان - المنطقة الصناعية',
      isVerified: true,
    ),
    ServiceProviderModel(
      id: 'sp3',
      name: 'Eng. Tariq Plumbing Services',
      nameAr: 'مؤسسة طارق للسباكة وتمديدات الصرف',
      trade: 'Master Plumber',
      tradeAr: 'سباكة وتمديدات صحية متطورة وشبكات تغذية',
      categoryId: 'labor',
      rating: 4.9,
      reviewsCount: 52,
      experienceYears: '9',
      priceEstimate: 'يومية 20,000 ج.س / أو مقطوعية للمبنى',
      phone: '+249 90 778 8990',
      location: 'Bahri - Kafouri',
      locationAr: 'بحري - كافوري وشمبات',
      isVerified: true,
    ),
    ServiceProviderModel(
      id: 'sp4',
      name: 'Al-Nour Electrical Contracting',
      nameAr: 'ورشة النور للتمديدات الكهربائية والإنارة',
      trade: 'Master Electrician',
      tradeAr: 'كهرباء مباني وتأسيس لوحات قواطع رئيسية',
      categoryId: 'labor',
      rating: 4.7,
      reviewsCount: 41,
      experienceYears: '11',
      priceEstimate: '1,800 ج.س / نقطة كهرباء',
      phone: '+249 91 334 5566',
      location: 'Khartoum - Riyadh',
      locationAr: 'الخرطوم - المعمورة والرياض',
      isVerified: true,
    ),
    ServiceProviderModel(
      id: 'sp5',
      name: 'Ibrahim Decor & Painting',
      nameAr: 'فريق إبراهيم للدهانات والديكورات الإنشائية',
      trade: 'Professional Painter',
      tradeAr: 'دهانات داخلية وخارجية وبوهيات',
      categoryId: 'labor',
      rating: 4.8,
      reviewsCount: 34,
      experienceYears: '8',
      priceEstimate: '1,200 ج.س / متر مربع',
      phone: '+249 92 889 0011',
      location: 'Khartoum',
      locationAr: 'الخرطوم وبحري',
      isVerified: true,
    ),
    ServiceProviderModel(
      id: 'sp6',
      name: 'Al-Baraka Masonry & Plastering',
      nameAr: 'مقاولات البركة للبناء واللياسة',
      trade: 'Master Mason',
      tradeAr: 'بناء طوب وبلوك مصمت ولياسة خشنة وناعمة',
      categoryId: 'labor',
      rating: 4.9,
      reviewsCount: 46,
      experienceYears: '14',
      priceEstimate: '3,000 ج.س / متر مربع لياسة',
      phone: '+249 91 556 7788',
      location: 'Omdurman',
      locationAr: 'أم درمان - الثورات',
      isVerified: true,
    ),

    // === 3. الخدمات اللوجستية وأسطول النقل والتعتيل ===
    // 3.1 الدفارات (Dyna / Medium Trucks)
    ServiceProviderModel(
      id: 'log_dyna1',
      name: 'Al-Fursan Dyna Fleet (Medium Cargo)',
      nameAr: 'أسطول دفارات الفرسان للنقل المتوسط والتشطيبات',
      trade: 'Dyna Truck Fleet (الدفارات)',
      tradeAr: 'الدفارات: حمولات متوسطة مخصصة لطلبيات التشطيبات، البوهيات، العدد، وتجهيزات السباكة والكهرباء',
      categoryId: 'logistics',
      rating: 4.89,
      reviewsCount: 76,
      experienceYears: '9',
      priceEstimate: '18,000 إلى 25,000 ج.س / الرد داخل ولاية الخرطوم',
      phone: '+249 91 776 5544',
      location: 'Khartoum - El Sajana',
      locationAr: 'سوق السجانة ولكافة مناطق الخرطوم',
      isVerified: true,
    ),
    ServiceProviderModel(
      id: 'log_dyna2',
      name: 'Express Dyna Delivery (Finishes & Paints)',
      nameAr: 'دفارات الصادق للتوصيل السريع للمشاريع',
      trade: 'Dyna Truck Fleet (الدفارات)',
      tradeAr: 'الدفارات: نقل مباشر للبوهيات، العوازل، الكراتين، وأطقم الحمامات والمستلزمات',
      categoryId: 'logistics',
      rating: 4.82,
      reviewsCount: 43,
      experienceYears: '6',
      priceEstimate: '20,000 ج.س / المشوار السريع',
      phone: '+249 92 665 4433',
      location: 'Bahri & Omdurman',
      locationAr: 'بحري وأم درمان وشرق النيل',
      isVerified: true,
    ),

    // 3.2 الجرارات والشاحنات المسطحة (Flatbed Trucks for Rebar & Pipes)
    ServiceProviderModel(
      id: 'log_flatbed1',
      name: 'Al-Nile Flatbed & Trailer Fleet (Rebar 12m)',
      nameAr: 'شركة النيل للجرارات والشاحنات المسطحة (Flatbed)',
      trade: 'Flatbed Trucks (جرارات وشاحنات مسطحة)',
      tradeAr: 'الجرارات والمسطحات: متخصصة في نقل أسياخ حديد التسليح (12 متر)، المواسير الطويلة، كمرات الصلب، والهياكل الحديدية الضخمة',
      categoryId: 'logistics',
      rating: 4.96,
      reviewsCount: 110,
      experienceYears: '18',
      priceEstimate: '75,000 ج.س / رد تريلا مسطحة 30 طن',
      phone: '+249 91 888 7766',
      location: 'Khartoum - Industrial Zone',
      locationAr: 'المناطق الصناعية، مصانع الحديد، والمشاريع الكبرى',
      isVerified: true,
    ),
    ServiceProviderModel(
      id: 'log_flatbed2',
      name: 'Al-Amana Heavy Flatbed Trailers',
      nameAr: 'أسطول الأمانة للجرارات والسطحات الطويلة',
      trade: 'Flatbed Trucks (جرارات وشاحنات مسطحة)',
      tradeAr: 'الجرارات والمسطحات: شاحنات مسطحة لنقل السيخ والمواسير الطويلة وكمرات الجمالونات',
      categoryId: 'logistics',
      rating: 4.88,
      reviewsCount: 58,
      experienceYears: '14',
      priceEstimate: 'حسب الحمولة والمسافة (65,000 إلى 90,000 ج.س)',
      phone: '+249 90 998 8112',
      location: 'Khartoum State',
      locationAr: 'الخرطوم وكافة الولايات المجاورة',
      isVerified: true,
    ),

    // 3.3 القلابات (Dump Trucks for Sand, Gravel & Ready-mix)
    ServiceProviderModel(
      id: 'log_dump1',
      name: 'Al-Muqran Dump Trucks (Sand & Gravel)',
      nameAr: 'مؤسسة المقرن للقلابات ونقل المواد السائبة',
      trade: 'Dump Trucks (القلابات)',
      tradeAr: 'القلابات: توريد وتفريغ مباشر للمواد السائبة (رمل خشن، رمل ناعم، سن، حجر، كركار، وخرسانة) قلابات 18 و 24 متر مكعب',
      categoryId: 'logistics',
      rating: 4.92,
      reviewsCount: 94,
      experienceYears: '12',
      priceEstimate: 'حسب نوع المادة والمسافة (يبدأ من 45,000 ج.س للقلاب)',
      phone: '+249 91 332 2110',
      location: 'Khartoum Quarries',
      locationAr: 'المرخيات، قري، حطاب، وكافة مواقع الصب',
      isVerified: true,
    ),
    ServiceProviderModel(
      id: 'log_dump2',
      name: 'Sahara Heavy Dump Trucks Fleet',
      nameAr: 'أسطول الصحراء للقلابات الكبيرة',
      trade: 'Dump Trucks (القلابات)',
      tradeAr: 'القلابات: توريد يومي ومستمر لرمل البناء، الحصى، والدفان لمواقع الإنشاءات الكبرى',
      categoryId: 'logistics',
      rating: 4.85,
      reviewsCount: 67,
      experienceYears: '10',
      priceEstimate: '50,000 ج.س / قلاب 20 متر مكعب',
      phone: '+249 92 443 3221',
      location: 'Omdurman & Bahri',
      locationAr: 'أم درمان وبحري وشرق النيل',
      isVerified: true,
    ),

    // 3.4 فرق التعتيل والتنزيل والتشوين
    ServiceProviderModel(
      id: 'log_crew1',
      name: 'El Sajana Heavy Offloading & Hoisting Crew',
      nameAr: 'فرقة السجانة المركزية لتعتيل وتنزيل الشحنات',
      trade: 'Offloading & Hoisting Labor (تعتيل وتشوين)',
      tradeAr: 'عمالة تعتيل قوية لتفريغ وتنزيل تريلات الحديد والأسمنت وتشوين المواد للأدوار العليا والمستودعات بأمان',
      categoryId: 'logistics',
      rating: 4.95,
      reviewsCount: 82,
      experienceYears: '10',
      priceEstimate: '500 ج.س / كيس أسمنت تشوين - 3,500 ج.س / طن حديد',
      phone: '+249 91 998 8776',
      location: 'Khartoum - El Sajana Market',
      locationAr: 'سوق السجانة ولكافة مواقع الخرطوم',
      isVerified: true,
    ),
  ];

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