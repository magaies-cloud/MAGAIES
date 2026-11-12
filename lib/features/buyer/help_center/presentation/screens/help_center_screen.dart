import 'package:flutter/material.dart';
import 'package:magaiees_marketplace/core/utils/app_notification.dart';

class HelpCenterScreen extends StatefulWidget {
  const HelpCenterScreen({super.key});

  @override
  State<HelpCenterScreen> createState() => _HelpCenterScreenState();
}

class _HelpCenterScreenState extends State<HelpCenterScreen> {
  String _searchQuery = '';
  String _selectedCategory = 'all';

  final List<Map<String, dynamic>> _helpSections = [
    {
      'id': 'account',
      'titleAr': 'الحسابات والتسجيل',
      'titleEn': 'Accounts & Registration',
      'icon': Icons.account_circle_outlined,
      'color': Color(0xFF2563EB),
      'items': [
        {
          'qAr': 'كيف أنشئ حساب مشتري أو حساب تاجر؟',
          'qEn': 'How to create a buyer or merchant account?',
          'aAr': '• للتسجيل كمشتري: يمكنك التسجيل بريدك الإلكتروني وكلمة المرور فوراً من شاشة التسجيل.\n• للترقية كتاجر/مورد: من صفحة (الملف الشخصي > ترقية حسابك إلى تاجر)، أدخل اسم المتجر ورقم السجل التجاري ورقم الهاتف لتبدأ البيع فوراً.',
          'aEn': '• Buyer: Register with your email and password from the registration screen.\n• Merchant: From (Profile > Upgrade to Merchant), enter your store name and CR number to start selling.',
        },
        {
          'qAr': 'كيف يتم توثيق الحسابات التجارية والمصانع؟',
          'qEn': 'How are business accounts and factories verified?',
          'aAr': '• يتم منح شارة التوثيق الزرقاء (Verified ✔️) للمصانع والتجار بعد مراجعة السجل التجاري وموقع المستودع الميداني لضمان موثوقية التوريد لجميع المشترين.',
          'aEn': '• The verified badge is granted after validating the Commercial Register and physical warehouse location.',
        },
      ],
    },
    {
      'id': 'ads',
      'titleAr': 'إدارة الإعلانات والترويج المدفوع',
      'titleEn': 'Ads Management & Paid Promotion',
      'icon': Icons.campaign_outlined,
      'color': Color(0xFFEA580C),
      'items': [
        {
          'qAr': 'ما هي ميزات وفوائد الإعلانات المدفوعة في منصة السيطرة؟',
          'qEn': 'What are the benefits of paid ads on Magaiees?',
          'aAr': '1. الظهور في البانرات الرئيسية (Hero Banners) في أعلى الشاشة الرئيسية أمام آلاف المقاولين يومياً.\n2. وضع شارة (ممول / Sponsored) على منتجاتك لتظهر في مقدمة نتائج البحث وقوائم التصنيفات.\n3. مضاعفة مبيعاتك وطلبات عروض الأسعار بكميات الجملة بنسبة تزيد عن 300%.\n4. استهداف جغرافي دقيق للمقاولين وأصحاب المشاريع في منطقتك.',
          'aEn': '1. Prominent placement in top Hero Banners seen by thousands of contractors.\n2. Sponsored tag on products to rank first in search and categories.\n3. Boost bulk quote requests and sales by over 300%.\n4. Precise geographic targeting for contractors in your area.',
        },
        {
          'qAr': 'كيف أطلق حملة إعلانية ممولة لمنتجاتي أو مصنعي؟',
          'qEn': 'How to launch a sponsored ad campaign?',
          'aAr': '• من لوحة تحكم التاجر، اختر (إدارة الإعلانات والحملات)، حدد المنتج أو البانر المطلوب، وحدد مدة الحملة والميزانية، وسيتم تفعيلها فوراً بعد مراجعة الفريق.',
          'aEn': '• From the Merchant Dashboard, select (Ads Management), pick the product or banner, set the duration and budget, and launch.',
        },
      ],
    },
    {
      'id': 'payments',
      'titleAr': 'الدفع والشراء والعمليات المالية',
      'titleEn': 'Payments, Orders & Billing',
      'icon': Icons.payment_outlined,
      'color': Color(0xFF059669),
      'items': [
        {
          'qAr': 'ما هي طرق الدفع المعتمدة في المنصة؟',
          'qEn': 'What are the accepted payment methods?',
          'aAr': '• التطبيقات المصرفية المعتمدة والتحويل الفوري.\n• البطاقات المصرفية وبطاقات مدى / فيزا وماستركارد.\n• التحويل البنكي المباشر لحسابات المنصة مع إرفاق الإشعار.\n• الدفع الآجل وتسهيلات الشركات (B2B Credit) للمقاولين المعتمدين.',
          'aEn': '• Approved Mobile Banking Apps & Instant Transfers.\n• Bank Debit/Credit Cards.\n• Direct Wire Transfer with receipt upload.\n• B2B Corporate Credit Facilities for certified contractors.',
        },
        {
          'qAr': 'كيف تتم متابعة الأرصدة والحركات والفواتير الضريبية؟',
          'qEn': 'How to track balances, transactions and VAT invoices?',
          'aAr': '• يمكنك متابعة حالة جميع طلبياتك من تبويب (الطلبات).\n• تصدر المنصة فاتورة ضريبية رسمية بصيغة PDF لكل طلبية تتضمن تفاصيل كميات الحديد، الأسمنت، ورسوم النقل والتفريغ ورقم السجل التجاري.',
          'aEn': '• Track orders from the (Orders) tab.\n• Automated official VAT invoices (PDF) are generated for every order with tonnage and transportation breakdown.',
        },
      ],
    },
    {
      'id': 'security',
      'titleAr': 'الأمان والحماية وضمان المعاملات',
      'titleEn': 'Security, Protection & Trust',
      'icon': Icons.security_outlined,
      'color': Color(0xFF7C3AED),
      'items': [
        {
          'qAr': 'كيف تضمن المنصة حماية أموال المشتري وجودة المواد؟',
          'qEn': 'How does Magaiees protect buyer funds and material quality?',
          'aAr': '• نظام حجز المبالغ (Escrow Payment): لا يتم تحويل قيمة البضاعة للتاجر إلا بعد استلام المشتري للحديد أو الأسمنت في الموقع والتأكد من مطابقته للمواصفات.\n• فحص الجودة والمطابقة: نلزم جميع الموردين بتقديم شهادات الجودة والمطابقة المعتمدة للمواد.',
          'aEn': '• Escrow Payment: Funds are only released to the supplier after the buyer inspects and receives the materials on site.\n• Quality Compliance: Suppliers are required to provide certified material compliance certificates.',
        },
        {
          'qAr': 'سياسة حماية الخصوصية وبيانات المشاريع',
          'qEn': 'Privacy policy and project data protection',
          'aAr': '• تشفير كامل من النهاية إلى النهاية لكافة البيانات المالية وبيانات المواقع والمخططات الهندسية مع عدم مشاركتها مع أي جهات خارجية.',
          'aEn': '• End-to-end encryption for all financial records, job site locations, and architectural plans.',
        },
      ],
    },
  ];

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    return Scaffold(
      appBar: AppBar(
        title: Text(isRtl ? 'مركز المساعدة والدعم' : 'Help & Knowledge Center'),
        centerTitle: true,
      ),
      body: ListView(
        padding: const EdgeInsets.all(16.0),
        children: [
          // Hero Banner
          Container(
            padding: const EdgeInsets.all(20.0),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF1E40AF), Color(0xFF3B82F6)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(18),
              boxShadow: [
                BoxShadow(
                  color: Colors.blue.withOpacity(0.25),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.2),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.help_center, color: Colors.white, size: 28),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        isRtl ? 'كيف يمكننا مساعدتك اليوم؟' : 'How can we help you today?',
                        style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Text(
                  isRtl
                      ? 'دليلك الشامل للاستفادة القصوى من منصة السيطرة: الحسابات، الإعلانات، الدفع، وحماية المشتريات.'
                      : 'Your complete guide to Magaiees: Accounts, Ads, Payments, and Buyer Protection.',
                  style: TextStyle(color: Colors.white.withOpacity(0.9), fontSize: 12.5, height: 1.4),
                ),
                const SizedBox(height: 16),
                // Search Input
                TextField(
                  onChanged: (val) => setState(() => _searchQuery = val.trim().toLowerCase()),
                  style: const TextStyle(color: Colors.black87),
                  decoration: InputDecoration(
                    hintText: isRtl ? 'ابحث عن إجابة أو سؤال...' : 'Search for questions or topics...',
                    hintStyle: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                    prefixIcon: const Icon(Icons.search, color: Colors.blue),
                    filled: true,
                    fillColor: Colors.white,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none,
                    ),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 24),

          // Pro Tips for Users & Merchants Card
          Container(
            padding: const EdgeInsets.all(16.0),
            decoration: BoxDecoration(
              color: Theme.of(context).colorScheme.primaryContainer.withOpacity(0.3),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: Theme.of(context).colorScheme.primary.withOpacity(0.3)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Icon(Icons.lightbulb, color: Colors.amber, size: 22),
                    const SizedBox(width: 8),
                    Text(
                      isRtl ? 'نصائح احترافية لتحقيق أعلى استفادة' : 'Pro Tips for Maximum Benefit',
                      style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                _buildTipItem(
                  isRtl
                      ? '📈 تابع شريط الأسعار الحي لحظة بلحظة لاغتنام فرص انخفاض أسعار حديد التسليح والأسمنت.'
                      : '📈 Track live price ticker to buy rebar and cement at the lowest market dips.',
                ),
                _buildTipItem(
                  isRtl
                      ? '📐 استخدم حاسبة كميات البناء وطلب التسعيرة المباشرة (الوسيط الرقمي) للكميات الكبيرة لتوفير حتى 15%.'
                      : '📐 Use Material Estimator and Digital Broker for bulk orders to save up to 15%.',
                ),
                _buildTipItem(
                  isRtl
                      ? '📢 للشركات والتجار: تفعيل الإعلانات الممولة يرفع نسبة التواصل مع مقاولي المشاريع الكبرى إلى 4 أضعاف.'
                      : '📢 For Suppliers: Sponsored ads increase direct contractor leads by 4x.',
                ),
              ],
            ),
          ),

          const SizedBox(height: 24),

          // Categories Filter Selector
          Text(
            isRtl ? 'أقسام مركز المساعدة' : 'Help Topics',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),

          Row(
            children: [
              _buildTopicChip('all', isRtl ? 'جميع الأقسام' : 'All Topics', Icons.apps, isRtl),
              const SizedBox(width: 8),
              _buildTopicChip('ads', isRtl ? 'الإعلانات والترويج' : 'Ads & Promo', Icons.campaign, isRtl),
              const SizedBox(width: 8),
              _buildTopicChip('payments', isRtl ? 'الدفع والشراء' : 'Payments', Icons.payment, isRtl),
            ],
          ),

          const SizedBox(height: 16),

          // Accordion Sections
          ..._buildFilteredSections(isRtl),

          const SizedBox(height: 24),

          // Direct Support Footer Card
          Card(
            color: Theme.of(context).colorScheme.surfaceContainerHighest,
            child: Padding(
              padding: const EdgeInsets.all(18.0),
              child: Column(
                children: [
                  const Icon(Icons.support_agent, size: 40, color: Colors.blue),
                  const SizedBox(height: 8),
                  Text(
                    isRtl ? 'لم تجد الإجابة التي تبحث عنها؟' : 'Still need help?',
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    isRtl
                        ? 'فريق الدعم الفني جاهز لمساعدتك على مدار الساعة'
                        : 'Our technical support team is available 24/7',
                    style: const TextStyle(fontSize: 12, color: Colors.grey),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: () {
                            AppNotification.showInfo(
                              context,
                              isRtl ? 'الاتصال بالرقم الموحد: 8001234567' : 'Calling 8001234567',
                              icon: Icons.call,
                            );
                          },
                          icon: const Icon(Icons.call, size: 18),
                          label: Text(isRtl ? 'اتصال هاتفي' : 'Call Support'),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: FilledButton.icon(
                          style: FilledButton.styleFrom(backgroundColor: const Color(0xFF25D366)),
                          onPressed: () {
                            AppNotification.showInfo(
                              context,
                              isRtl ? 'فتح واتساب الدعم الفني...' : 'Opening WhatsApp Support...',
                              icon: Icons.chat,
                            );
                          },
                          icon: const Icon(Icons.chat, size: 18),
                          label: Text(isRtl ? 'محادثة واتساب' : 'WhatsApp'),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),

          const SizedBox(height: 24),
        ],
      ),
    );
  }

  Widget _buildTipItem(String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('• ', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
          Expanded(
            child: Text(text, style: const TextStyle(fontSize: 12.5, height: 1.35)),
          ),
        ],
      ),
    );
  }

  Widget _buildTopicChip(String id, String label, IconData icon, bool isRtl) {
    final isSelected = _selectedCategory == id;
    return Expanded(
      child: InkWell(
        onTap: () => setState(() => _selectedCategory = id),
        borderRadius: BorderRadius.circular(10),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
          decoration: BoxDecoration(
            color: isSelected
                ? Theme.of(context).colorScheme.primary
                : Theme.of(context).colorScheme.surfaceContainerHighest,
            borderRadius: BorderRadius.circular(10),
          ),
          child: Column(
            children: [
              Icon(
                icon,
                size: 20,
                color: isSelected ? Colors.white : Theme.of(context).colorScheme.onSurfaceVariant,
              ),
              const SizedBox(height: 4),
              Text(
                label,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                  color: isSelected ? Colors.white : Theme.of(context).colorScheme.onSurfaceVariant,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  List<Widget> _buildFilteredSections(bool isRtl) {
    var sections = _helpSections;
    if (_selectedCategory != 'all') {
      sections = sections.where((s) => s['id'] == _selectedCategory).toList();
    }

    List<Widget> widgets = [];

    for (var section in sections) {
      final items = section['items'] as List<Map<String, String>>;
      final title = isRtl ? section['titleAr'] as String : section['titleEn'] as String;
      final icon = section['icon'] as IconData;
      final color = section['color'] as Color;

      // Filter by search
      var filteredItems = items;
      if (_searchQuery.isNotEmpty) {
        filteredItems = items.where((item) {
          final q = (item['qAr']! + ' ' + item['qEn']! + ' ' + item['aAr']! + ' ' + item['aEn']!).toLowerCase();
          return q.contains(_searchQuery);
        }).toList();
      }

      if (filteredItems.isEmpty) continue;

      widgets.add(
        Padding(
          padding: const EdgeInsets.only(top: 14.0, bottom: 6.0),
          child: Row(
            children: [
              Icon(icon, color: color, size: 20),
              const SizedBox(width: 8),
              Text(
                title,
                style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
              ),
            ],
          ),
        ),
      );

      for (var item in filteredItems) {
        final q = isRtl ? item['qAr']! : item['qEn']!;
        final a = isRtl ? item['aAr']! : item['aEn']!;

        widgets.add(
          Card(
            margin: const EdgeInsets.only(bottom: 8.0),
            elevation: 1,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            child: ExpansionTile(
              leading: Icon(Icons.help_outline, color: color, size: 20),
              title: Text(
                q,
                style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w600),
              ),
              children: [
                Padding(
                  padding: const EdgeInsets.only(left: 16.0, right: 16.0, bottom: 16.0, top: 4.0),
                  child: Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Theme.of(context).colorScheme.surfaceContainerHighest.withOpacity(0.5),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      a,
                      style: const TextStyle(fontSize: 13, height: 1.5),
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      }
    }

    if (widgets.isEmpty) {
      widgets.add(
        Padding(
          padding: const EdgeInsets.all(32.0),
          child: Center(
            child: Text(
              isRtl ? 'لا توجد نتائج مطابقة لبحثك في مركز المساعدة' : 'No matching help topics found',
              style: const TextStyle(color: Colors.grey),
            ),
          ),
        ),
      );
    }

    return widgets;
  }
}