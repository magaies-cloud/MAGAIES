import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

class AdminSettingsScreen extends ConsumerStatefulWidget {
  const AdminSettingsScreen({super.key});

  @override
  ConsumerState<AdminSettingsScreen> createState() => _AdminSettingsScreenState();
}

class _AdminSettingsScreenState extends ConsumerState<AdminSettingsScreen> {
  int _selectedIndex = 0;

  static const String _settingsStorageKey = 'magaiees_platform_enterprise_settings';

  // 1. System & Appearance
  final _nameArCtrl = TextEditingController(text: 'منصة مقاييس لمواد البناء');
  final _nameEnCtrl = TextEditingController(text: 'Magaiees Marketplace');
  final _phoneCtrl = TextEditingController(text: '+249 912 300 105');
  final _whatsappCtrl = TextEditingController(text: '+249 912 300 105');
  final _emailCtrl = TextEditingController(text: 'support@magaiees.com');
  final _aboutCtrl = TextEditingController(
    text: 'مقاييس هي منصة رقمية رائدة في السودان تربط بين تجار ومصانع مواد البناء والمقاولين والعملاء لتسهيل عمليات البيع والشراء بأسعار تنافسية وخيارات متعددة.',
  );
  bool _maintenanceMode = false;
  bool _darkModeEnabled = false;

  // 2. App Configs (Finance & Payments)
  final _commissionCtrl = TextEditingController(text: '5.0');
  final _vatCtrl = TextEditingController(text: '17.0');
  final _currencyCtrl = TextEditingController(text: 'ج.س (SDG)');
  bool _autoFxCalculation = true;
  bool _enableBankak = true;
  final _bankakAccountCtrl = TextEditingController(text: '1984256 - بنك الخرطوم (شركة مقاييس)');
  bool _enableFawry = true;
  bool _enableCashOnDelivery = true;

  // 3. Gamification
  bool _enableGamification = true;
  final _pointsPerOrderCtrl = TextEditingController(text: '10');
  final _pointsPerReviewCtrl = TextEditingController(text: '5');
  final _pointsToSdgRatioCtrl = TextEditingController(text: '100'); // 100 points = 1 SDG

  // 4. Admin Account & Security
  final _adminNameCtrl = TextEditingController(text: 'مدير النظام (Admin)');
  final _adminEmailCtrl = TextEditingController(text: 'admin@magaiees.com');
  final _currentPassCtrl = TextEditingController();
  final _newPassCtrl = TextEditingController();
  final _confirmPassCtrl = TextEditingController();
  bool _twoFactorAuth = false;

  bool _isLoading = true;

  // Theme colors
  final Color _darkSlate = const Color(0xFF0F172A);
  final Color _primaryBlue = const Color(0xFF1E40AF);
  final Color _surfaceColor = Colors.white;
  final Color _backgroundColor = const Color(0xFFF8FAFC);

  @override
  void initState() {
    super.initState();
    _loadSettings();
  }

  @override
  void dispose() {
    _nameArCtrl.dispose();
    _nameEnCtrl.dispose();
    _phoneCtrl.dispose();
    _whatsappCtrl.dispose();
    _emailCtrl.dispose();
    _aboutCtrl.dispose();
    _commissionCtrl.dispose();
    _vatCtrl.dispose();
    _currencyCtrl.dispose();
    _bankakAccountCtrl.dispose();
    _pointsPerOrderCtrl.dispose();
    _pointsPerReviewCtrl.dispose();
    _pointsToSdgRatioCtrl.dispose();
    _adminNameCtrl.dispose();
    _adminEmailCtrl.dispose();
    _currentPassCtrl.dispose();
    _newPassCtrl.dispose();
    _confirmPassCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadSettings() async {
    try {
      // Try loading from Supabase first
      final supabase = Supabase.instance.client;
      final res = await supabase
          .from('gamification_settings')
          .select()
          .eq('id', 1)
          .maybeSingle();

      if (res != null) {
        setState(() {
          _nameArCtrl.text = res['platform_name_ar'] ?? _nameArCtrl.text;
          _nameEnCtrl.text = res['platform_name_en'] ?? _nameEnCtrl.text;
          _phoneCtrl.text = res['phone'] ?? _phoneCtrl.text;
          _whatsappCtrl.text = res['whatsapp'] ?? _whatsappCtrl.text;
          _emailCtrl.text = res['support_email'] ?? _emailCtrl.text;
          _aboutCtrl.text = res['about'] ?? _aboutCtrl.text;
          _maintenanceMode = res['maintenance_mode'] ?? _maintenanceMode;

          _commissionCtrl.text = (res['commission_percentage'] ?? _commissionCtrl.text).toString();
          _vatCtrl.text = (res['vat_percentage'] ?? _vatCtrl.text).toString();
          _autoFxCalculation = res['auto_fx_calculation'] ?? _autoFxCalculation;
          _enableBankak = res['enable_bankak'] ?? _enableBankak;
          _bankakAccountCtrl.text = res['bankak_account'] ?? _bankakAccountCtrl.text;
          _enableFawry = res['enable_fawry'] ?? _enableFawry;
          _enableCashOnDelivery = res['enable_cash_on_delivery'] ?? _enableCashOnDelivery;

          _enableGamification = res['enable_gamification'] ?? _enableGamification;
          _pointsPerOrderCtrl.text = (res['points_per_order'] ?? _pointsPerOrderCtrl.text).toString();
          _pointsPerReviewCtrl.text = (res['points_per_review'] ?? _pointsPerReviewCtrl.text).toString();
          _pointsToSdgRatioCtrl.text = (res['points_to_sdg_ratio'] ?? _pointsToSdgRatioCtrl.text).toString();
        });
      } else {
        // Fallback to SharedPreferences (legacy)
        await _loadFromLocalPrefs();
      }
    } catch (_) {
      // If Supabase fails (e.g. columns don't exist yet), fallback to local
      await _loadFromLocalPrefs();
    }
    setState(() => _isLoading = false);
  }

  Future<void> _loadFromLocalPrefs() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final savedStr = prefs.getString(_settingsStorageKey);
      if (savedStr != null && savedStr.isNotEmpty) {
        final data = jsonDecode(savedStr) as Map<String, dynamic>;
        _nameArCtrl.text = data['nameAr'] ?? _nameArCtrl.text;
        _nameEnCtrl.text = data['nameEn'] ?? _nameEnCtrl.text;
        _phoneCtrl.text = data['phone'] ?? _phoneCtrl.text;
        _whatsappCtrl.text = data['whatsapp'] ?? _whatsappCtrl.text;
        _emailCtrl.text = data['email'] ?? _emailCtrl.text;
        _aboutCtrl.text = data['about'] ?? _aboutCtrl.text;
        _maintenanceMode = data['maintenanceMode'] ?? _maintenanceMode;
        _darkModeEnabled = data['darkModeEnabled'] ?? _darkModeEnabled;

        _commissionCtrl.text = data['commission'] ?? _commissionCtrl.text;
        _vatCtrl.text = data['vat'] ?? _vatCtrl.text;
        _autoFxCalculation = data['autoFx'] ?? _autoFxCalculation;
        _enableBankak = data['enableBankak'] ?? _enableBankak;
        _bankakAccountCtrl.text = data['bankakAccount'] ?? _bankakAccountCtrl.text;
        _enableFawry = data['enableFawry'] ?? _enableFawry;
        _enableCashOnDelivery = data['enableCash'] ?? _enableCashOnDelivery;

        _enableGamification = data['enableGamification'] ?? _enableGamification;
        _pointsPerOrderCtrl.text = data['pointsPerOrder'] ?? _pointsPerOrderCtrl.text;
        _pointsPerReviewCtrl.text = data['pointsPerReview'] ?? _pointsPerReviewCtrl.text;
        _pointsToSdgRatioCtrl.text = data['pointsToSdgRatio'] ?? _pointsToSdgRatioCtrl.text;

        _adminNameCtrl.text = data['adminName'] ?? _adminNameCtrl.text;
        _adminEmailCtrl.text = data['adminEmail'] ?? _adminEmailCtrl.text;
        _twoFactorAuth = data['twoFactorAuth'] ?? _twoFactorAuth;
      }
    } catch (_) {}
  }

  Future<void> _saveSettings() async {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    
    try {
      // Save to Supabase (primary storage)
      final supabase = Supabase.instance.client;
      await supabase.from('gamification_settings').upsert({
        'id': 1,
        'platform_name_ar': _nameArCtrl.text.trim(),
        'platform_name_en': _nameEnCtrl.text.trim(),
        'phone': _phoneCtrl.text.trim(),
        'whatsapp': _whatsappCtrl.text.trim(),
        'support_email': _emailCtrl.text.trim(),
        'about': _aboutCtrl.text.trim(),
        'maintenance_mode': _maintenanceMode,
        'commission_percentage': double.tryParse(_commissionCtrl.text.trim()) ?? 5.0,
        'vat_percentage': double.tryParse(_vatCtrl.text.trim()) ?? 17.0,
        'auto_fx_calculation': _autoFxCalculation,
        'enable_bankak': _enableBankak,
        'bankak_account': _bankakAccountCtrl.text.trim(),
        'enable_fawry': _enableFawry,
        'enable_cash_on_delivery': _enableCashOnDelivery,
        'enable_gamification': _enableGamification,
        'points_per_order': int.tryParse(_pointsPerOrderCtrl.text.trim()) ?? 10,
        'points_per_review': int.tryParse(_pointsPerReviewCtrl.text.trim()) ?? 5,
        'points_to_sdg_ratio': int.tryParse(_pointsToSdgRatioCtrl.text.trim()) ?? 100,
      });

      // Also save locally as cache
      final prefs = await SharedPreferences.getInstance();
      final data = {
        'nameAr': _nameArCtrl.text.trim(),
        'nameEn': _nameEnCtrl.text.trim(),
        'phone': _phoneCtrl.text.trim(),
        'whatsapp': _whatsappCtrl.text.trim(),
        'email': _emailCtrl.text.trim(),
        'about': _aboutCtrl.text.trim(),
        'maintenanceMode': _maintenanceMode,
        'darkModeEnabled': _darkModeEnabled,
        'commission': _commissionCtrl.text.trim(),
        'vat': _vatCtrl.text.trim(),
        'autoFx': _autoFxCalculation,
        'enableBankak': _enableBankak,
        'bankakAccount': _bankakAccountCtrl.text.trim(),
        'enableFawry': _enableFawry,
        'enableCash': _enableCashOnDelivery,
        'enableGamification': _enableGamification,
        'pointsPerOrder': _pointsPerOrderCtrl.text.trim(),
        'pointsPerReview': _pointsPerReviewCtrl.text.trim(),
        'pointsToSdgRatio': _pointsToSdgRatioCtrl.text.trim(),
        'adminName': _adminNameCtrl.text.trim(),
        'adminEmail': _adminEmailCtrl.text.trim(),
        'twoFactorAuth': _twoFactorAuth,
      };
      await prefs.setString(_settingsStorageKey, jsonEncode(data));

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(isRtl ? '✅ تم حفظ كافة إعدادات المنصة بنجاح' : '✅ All platform settings saved successfully'),
            backgroundColor: _primaryBlue,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('خطأ في حفظ الإعدادات: $e'),
            backgroundColor: Colors.red,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  Widget _buildTextField(TextEditingController controller, String label, IconData icon, {bool isNumber = false, int maxLines = 1, bool isPassword = false, String? suffixText}) {
    return TextField(
      controller: controller,
      keyboardType: isNumber ? const TextInputType.numberWithOptions(decimal: true) : TextInputType.text,
      maxLines: maxLines,
      obscureText: isPassword,
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon, color: _primaryBlue.withOpacity(0.7)),
        suffixText: suffixText,
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: BorderSide(color: Colors.grey.shade300),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: BorderSide(color: Colors.grey.shade300),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: BorderSide(color: _primaryBlue, width: 1.5),
        ),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      ),
    );
  }

  Widget _buildSwitchTile(String title, String subtitle, bool value, ValueChanged<bool> onChanged, {IconData? icon}) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: SwitchListTile(
        title: Row(
          children: [
            if (icon != null) ...[
              Icon(icon, color: _darkSlate, size: 20),
              const SizedBox(width: 8),
            ],
            Expanded(child: Text(title, style: TextStyle(fontWeight: FontWeight.bold, color: _darkSlate))),
          ],
        ),
        subtitle: Padding(
          padding: EdgeInsets.only(top: 4, right: icon != null ? 28.0 : 0),
          child: Text(subtitle, style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
        ),
        value: value,
        onChanged: onChanged,
        activeColor: _primaryBlue,
        activeTrackColor: _primaryBlue.withOpacity(0.3),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      ),
    );
  }

  Widget _buildSectionHeader(String title, String subtitle, IconData icon) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 24.0),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: _primaryBlue.withOpacity(0.1),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: _primaryBlue, size: 28),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: _darkSlate)),
                const SizedBox(height: 4),
                Text(subtitle, style: TextStyle(color: Colors.grey.shade600)),
              ],
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    if (_isLoading) {
      return Scaffold(
        backgroundColor: _backgroundColor,
        body: Center(child: CircularProgressIndicator(color: _primaryBlue)),
      );
    }

    final railDestinations = [
      NavigationRailDestination(
        icon: const Icon(Icons.display_settings_outlined),
        selectedIcon: const Icon(Icons.display_settings),
        label: Text(isRtl ? 'النظام والمظهر' : 'System & Appearance'),
      ),
      NavigationRailDestination(
        icon: const Icon(Icons.account_tree_outlined),
        selectedIcon: const Icon(Icons.account_tree),
        label: Text(isRtl ? 'إعدادات التطبيق' : 'App Configs'),
      ),
      NavigationRailDestination(
        icon: const Icon(Icons.military_tech_outlined),
        selectedIcon: const Icon(Icons.military_tech),
        label: Text(isRtl ? 'التحفيز والولاء' : 'Gamification'),
      ),
      NavigationRailDestination(
        icon: const Icon(Icons.admin_panel_settings_outlined),
        selectedIcon: const Icon(Icons.admin_panel_settings),
        label: Text(isRtl ? 'الإدارة والأمان' : 'Admin & Security'),
      ),
    ];

    return Scaffold(
      backgroundColor: _backgroundColor,
      appBar: AppBar(
        backgroundColor: _surfaceColor,
        elevation: 0,
        scrolledUnderElevation: 0,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              isRtl ? 'إعدادات المنصة والنظام' : 'Platform & System Settings',
              style: TextStyle(fontWeight: FontWeight.bold, color: _darkSlate, fontSize: 20),
            ),
            Text(
              isRtl ? 'إدارة شاملة لجميع خصائص منصة مقاييس' : 'Comprehensive management of Magaiees platform features',
              style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
            ),
          ],
        ),
        actions: [
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
            child: FilledButton.icon(
              style: FilledButton.styleFrom(
                backgroundColor: _primaryBlue,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 24),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              ),
              onPressed: _saveSettings,
              icon: const Icon(Icons.save_rounded, size: 20),
              label: Text(
                isRtl ? 'حفظ كافة التغييرات' : 'Save All Changes',
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
              ),
            ),
          ),
        ],
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(1),
          child: Container(color: Colors.grey.shade200, height: 1),
        ),
      ),
      body: Row(
        children: [
          // Navigation Rail
          NavigationRail(
            selectedIndex: _selectedIndex,
            onDestinationSelected: (int index) {
              setState(() {
                _selectedIndex = index;
              });
            },
            labelType: NavigationRailLabelType.all,
            backgroundColor: _surfaceColor,
            selectedIconTheme: IconThemeData(color: _primaryBlue, size: 28),
            unselectedIconTheme: IconThemeData(color: Colors.grey.shade500, size: 24),
            selectedLabelTextStyle: TextStyle(color: _primaryBlue, fontWeight: FontWeight.bold, fontSize: 13),
            unselectedLabelTextStyle: TextStyle(color: Colors.grey.shade600, fontSize: 12),
            indicatorColor: _primaryBlue.withOpacity(0.1),
            useIndicator: true,
            minExtendedWidth: 200,
            destinations: railDestinations,
          ),
          VerticalDivider(thickness: 1, width: 1, color: Colors.grey.shade200),

          // Main Content Area
          Expanded(
            child: Container(
              color: _backgroundColor,
              child: IndexedStack(
                index: _selectedIndex,
                children: [
                  _buildSystemAppearanceTab(isRtl),
                  _buildAppConfigsTab(isRtl),
                  _buildGamificationTab(isRtl),
                  _buildAdminSecurityTab(isRtl),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  // 1. System & Appearance
  Widget _buildSystemAppearanceTab(bool isRtl) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(32),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildSectionHeader(
            isRtl ? 'النظام والمظهر' : 'System & Appearance',
            isRtl ? 'إدارة الهوية البصرية ومعلومات المنصة الأساسية' : 'Manage brand identity and core platform information',
            Icons.display_settings
          ),

          Row(
            children: [
              Expanded(child: _buildTextField(_nameArCtrl, isRtl ? 'اسم المنصة (بالعربية)' : 'Platform Name (AR)', Icons.business)),
              const SizedBox(width: 20),
              Expanded(child: _buildTextField(_nameEnCtrl, isRtl ? 'اسم المنصة (بالإنجليزية)' : 'Platform Name (EN)', Icons.language)),
            ],
          ),
          const SizedBox(height: 20),
          Row(
            children: [
              Expanded(child: _buildTextField(_phoneCtrl, isRtl ? 'رقم هاتف الدعم الفني' : 'Support Phone', Icons.phone)),
              const SizedBox(width: 20),
              Expanded(child: _buildTextField(_whatsappCtrl, isRtl ? 'رقم واتساب خدمة العملاء' : 'WhatsApp Number', Icons.chat)),
              const SizedBox(width: 20),
              Expanded(child: _buildTextField(_emailCtrl, isRtl ? 'البريد الإلكتروني للدعم' : 'Support Email', Icons.email)),
            ],
          ),
          const SizedBox(height: 20),
          _buildTextField(_aboutCtrl, isRtl ? 'نبذة تعريفية عن منصة مقاييس' : 'About Magaiees Platform', Icons.info_outline, maxLines: 4),

          const SizedBox(height: 32),
          Text(isRtl ? 'تفضيلات النظام' : 'System Preferences', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: _darkSlate)),
          const SizedBox(height: 16),

          _buildSwitchTile(
            isRtl ? 'وضع الصيانة (Maintenance Mode)' : 'Maintenance Mode',
            isRtl ? 'إيقاف وصول المشترين مؤقتاً أثناء التحديثات البرمجية الكبرى' : 'Temporarily disable buyer access during major updates',
            _maintenanceMode,
            (val) => setState(() => _maintenanceMode = val),
            icon: Icons.construction,
          ),

          _buildSwitchTile(
            isRtl ? 'تفعيل المظهر الداكن الافتراضي' : 'Enable Default Dark Mode',
            isRtl ? 'تعيين المظهر الداكن كخيار افتراضي لجميع المستخدمين الجدد' : 'Set Dark Mode as the default theme for all new users',
            _darkModeEnabled,
            (val) => setState(() => _darkModeEnabled = val),
            icon: Icons.dark_mode,
          ),
        ],
      ),
    );
  }

  // 2. App Configs (Finance & Payments)
  Widget _buildAppConfigsTab(bool isRtl) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(32),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildSectionHeader(
            isRtl ? 'إعدادات التطبيق والمالية' : 'App & Finance Configs',
            isRtl ? 'التحكم في العمولات، الضرائب، وبوابات الدفع الإلكتروني' : 'Control commissions, taxes, and payment gateways',
            Icons.account_tree
          ),

          Text(isRtl ? 'السياسات المالية' : 'Financial Policies', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: _darkSlate)),
          const SizedBox(height: 16),

          Row(
            children: [
              Expanded(child: _buildTextField(_commissionCtrl, isRtl ? 'نسبة عمولة المنصة (%)' : 'Platform Commission (%)', Icons.percent, isNumber: true, suffixText: '%')),
              const SizedBox(width: 20),
              Expanded(child: _buildTextField(_vatCtrl, isRtl ? 'ضريبة القيمة المضافة (VAT %)' : 'VAT (%)', Icons.receipt_long, isNumber: true, suffixText: '%')),
              const SizedBox(width: 20),
              Expanded(child: _buildTextField(_currencyCtrl, isRtl ? 'العملة الأساسية' : 'Base Currency', Icons.monetization_on)),
            ],
          ),
          const SizedBox(height: 16),

          _buildSwitchTile(
            isRtl ? 'التحويل التلقائي للأسعار بالدولار (USD/SDG)' : 'Auto USD/SDG Recalculation',
            isRtl ? 'تحديث أسعار الأصناف بالجنيه السوداني فورياً عند تغير سعر الصرف' : 'Instantly update SDG prices when exchange rates fluctuate',
            _autoFxCalculation,
            (val) => setState(() => _autoFxCalculation = val),
            icon: Icons.currency_exchange,
          ),

          const SizedBox(height: 32),
          Text(isRtl ? 'بوابات وطرق الدفع' : 'Payment Gateways & Methods', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: _darkSlate)),
          const SizedBox(height: 16),

          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.grey.shade200),
            ),
            child: Column(
              children: [
                _buildSwitchTile(
                  isRtl ? 'تطبيق بنكك - بنك الخرطوم' : 'Bankak App - Bank of Khartoum',
                  isRtl ? 'تمكين التحويل المباشر عبر تطبيق بنكك' : 'Enable direct transfers via Bankak app',
                  _enableBankak,
                  (val) => setState(() => _enableBankak = val),
                  icon: Icons.account_balance,
                ),
                if (_enableBankak) ...[
                  const SizedBox(height: 16),
                  _buildTextField(_bankakAccountCtrl, isRtl ? 'بيانات حساب بنكك لاستقبال التحويلات' : 'Bankak Account Details', Icons.numbers),
                ],
              ],
            ),
          ),
          const SizedBox(height: 12),

          _buildSwitchTile(
            isRtl ? 'تطبيق فوري / المحافظ الإلكترونية' : 'Fawry App / E-Wallets',
            isRtl ? 'قبول الدفع عبر محفظة فوري وبطاقات الصراف الآلي' : 'Accept payments via Fawry wallet and ATM cards',
            _enableFawry,
            (val) => setState(() => _enableFawry = val),
            icon: Icons.phone_android,
          ),

          _buildSwitchTile(
            isRtl ? 'الدفع نقداً عند الاستلام (Cash on Delivery)' : 'Cash on Delivery (COD)',
            isRtl ? 'الدفع المباشر عند استلام شحنة مواد البناء في الموقع' : 'Direct payment upon delivery of materials to the site',
            _enableCashOnDelivery,
            (val) => setState(() => _enableCashOnDelivery = val),
            icon: Icons.local_shipping,
          ),
        ],
      ),
    );
  }

  // 3. Gamification
  Widget _buildGamificationTab(bool isRtl) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(32),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildSectionHeader(
            isRtl ? 'نظام التحفيز والولاء' : 'Gamification & Loyalty',
            isRtl ? 'إدارة نقاط المكافآت، مستويات المستخدمين وبرامج الولاء' : 'Manage reward points, user tiers, and loyalty programs',
            Icons.military_tech
          ),

          _buildSwitchTile(
            isRtl ? 'تفعيل نظام النقاط والولاء' : 'Enable Loyalty Points System',
            isRtl ? 'السماح للعملاء بكسب النقاط واستبدالها برصيد أو خصومات' : 'Allow customers to earn points and redeem them for balance or discounts',
            _enableGamification,
            (val) => setState(() => _enableGamification = val),
            icon: Icons.star_rounded,
          ),

          if (_enableGamification) ...[
            const SizedBox(height: 24),
            Text(isRtl ? 'قواعد اكتساب النقاط' : 'Points Earning Rules', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: _darkSlate)),
            const SizedBox(height: 16),

            Row(
              children: [
                Expanded(child: _buildTextField(_pointsPerOrderCtrl, isRtl ? 'النقاط لكل طلب مكتمل' : 'Points per Completed Order', Icons.shopping_bag, isNumber: true)),
                const SizedBox(width: 20),
                Expanded(child: _buildTextField(_pointsPerReviewCtrl, isRtl ? 'النقاط لكل تقييم ومراجعة' : 'Points per Review', Icons.rate_review, isNumber: true)),
                const SizedBox(width: 20),
                Expanded(child: _buildTextField(_pointsToSdgRatioCtrl, isRtl ? 'معادل النقاط للجنيه (كم نقطة = 1 جنيه)' : 'Points to SDG Ratio', Icons.currency_exchange, isNumber: true)),
              ],
            ),

            const SizedBox(height: 32),
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: _primaryBlue.withOpacity(0.05),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: _primaryBlue.withOpacity(0.2)),
              ),
              child: Row(
                children: [
                  Icon(Icons.info_outline, color: _primaryBlue, size: 32),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          isRtl ? 'معلومات حول نظام الولاء' : 'About Loyalty System',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: _primaryBlue),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          isRtl
                          ? 'نظام مقاييس للولاء يشجع المقاولين والعملاء على الاستمرار في الشراء والتقييم. سيتم تحويل النقاط المكتسبة إلى رصيد في المحفظة تلقائياً بناءً على "معادل النقاط للجنيه".'
                          : 'Magaiees loyalty system encourages contractors and clients to keep buying and reviewing. Earned points will be converted to wallet balance automatically based on the Points Ratio.',
                          style: TextStyle(color: Colors.grey.shade700, height: 1.5),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ]
        ],
      ),
    );
  }

  // 4. Admin Account & Security
  Widget _buildAdminSecurityTab(bool isRtl) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(32),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildSectionHeader(
            isRtl ? 'الإدارة والأمان' : 'Admin & Security',
            isRtl ? 'إدارة حساب المسؤول، كلمات المرور، وإعدادات الحماية' : 'Manage admin account, passwords, and security settings',
            Icons.admin_panel_settings
          ),

          Text(isRtl ? 'بيانات الحساب الأساسية' : 'Basic Account Details', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: _darkSlate)),
          const SizedBox(height: 16),

          Row(
            children: [
              Expanded(child: _buildTextField(_adminNameCtrl, isRtl ? 'اسم مدير النظام' : 'Admin Name', Icons.badge)),
              const SizedBox(width: 20),
              Expanded(child: _buildTextField(_adminEmailCtrl, isRtl ? 'البريد الإلكتروني للإدارة' : 'Admin Email', Icons.email)),
            ],
          ),
          const SizedBox(height: 24),

          _buildSwitchTile(
            isRtl ? 'المصادقة الثنائية (2FA)' : 'Two-Factor Authentication (2FA)',
            isRtl ? 'طلب رمز تأكيد إضافي عند تسجيل الدخول لحساب الإدارة' : 'Require an additional verification code when logging in',
            _twoFactorAuth,
            (val) => setState(() => _twoFactorAuth = val),
            icon: Icons.security,
          ),

          const SizedBox(height: 32),
          Text(isRtl ? 'تغيير كلمة المرور' : 'Change Password', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: _darkSlate)),
          const SizedBox(height: 16),

          Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: Colors.grey.shade200),
            ),
            child: Column(
              children: [
                _buildTextField(_currentPassCtrl, isRtl ? 'كلمة المرور الحالية' : 'Current Password', Icons.lock_outline, isPassword: true),
                const SizedBox(height: 16),
                Row(
                  children: [
                    Expanded(child: _buildTextField(_newPassCtrl, isRtl ? 'كلمة المرور الجديدة' : 'New Password', Icons.lock, isPassword: true)),
                    const SizedBox(width: 20),
                    Expanded(child: _buildTextField(_confirmPassCtrl, isRtl ? 'تأكيد كلمة المرور الجديدة' : 'Confirm New Password', Icons.check_circle_outline, isPassword: true)),
                  ],
                ),
                const SizedBox(height: 24),
                Align(
                  alignment: isRtl ? Alignment.centerLeft : Alignment.centerRight,
                  child: FilledButton.icon(
                    style: FilledButton.styleFrom(
                      backgroundColor: _darkSlate,
                      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                    onPressed: () async {
                      if (_newPassCtrl.text.isEmpty || _newPassCtrl.text != _confirmPassCtrl.text) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(isRtl ? 'الرجاء التأكد من تطابق كلمة المرور الجديدة' : 'Please ensure new passwords match'),
                            backgroundColor: Colors.red,
                            behavior: SnackBarBehavior.floating,
                          ),
                        );
                        return;
                      }
                      if (_newPassCtrl.text.length < 6) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(isRtl ? 'كلمة المرور يجب أن تكون 6 أحرف على الأقل' : 'Password must be at least 6 characters'),
                            backgroundColor: Colors.red,
                            behavior: SnackBarBehavior.floating,
                          ),
                        );
                        return;
                      }
                      try {
                        await Supabase.instance.client.auth.updateUser(
                          UserAttributes(password: _newPassCtrl.text),
                        );
                        _currentPassCtrl.clear();
                        _newPassCtrl.clear();
                        _confirmPassCtrl.clear();
                        if (mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(isRtl ? '🔐 تم تحديث كلمة مرور مدير النظام بنجاح' : '🔐 Admin password updated successfully'),
                              backgroundColor: const Color(0xFF059669),
                              behavior: SnackBarBehavior.floating,
                            ),
                          );
                        }
                      } catch (e) {
                        if (mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(isRtl ? 'فشل تحديث كلمة المرور: $e' : 'Failed to update password: $e'),
                              backgroundColor: Colors.red,
                              behavior: SnackBarBehavior.floating,
                            ),
                          );
                        }
                      }
                    },
                    icon: const Icon(Icons.password, size: 20),
                    label: Text(
                      isRtl ? 'تحديث كلمة المرور' : 'Update Password',
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
