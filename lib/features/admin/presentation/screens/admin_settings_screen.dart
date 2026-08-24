import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AdminSettingsScreen extends ConsumerStatefulWidget {
  const AdminSettingsScreen({super.key});

  @override
  ConsumerState<AdminSettingsScreen> createState() => _AdminSettingsScreenState();
}

class _AdminSettingsScreenState extends ConsumerState<AdminSettingsScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;

  static const String _settingsStorageKey = 'magaiees_platform_enterprise_settings';

  // General controllers
  final _nameArCtrl = TextEditingController(text: 'منصة مقاييس لمواد البناء');
  final _nameEnCtrl = TextEditingController(text: 'Magaiees Marketplace');
  final _phoneCtrl = TextEditingController(text: '+249 912 300 105');
  final _whatsappCtrl = TextEditingController(text: '+249 912 300 105');
  final _emailCtrl = TextEditingController(text: 'support@magaiees.com');
  final _aboutCtrl = TextEditingController(
    text: 'مقاييس هي منصة رقمية رائدة في السودان تربط بين تجار ومصانع مواد البناء والمقاولين والعملاء لتسهيل عمليات البيع والشراء بأسعار تنافسية وخيارات متعددة.',
  );
  bool _maintenanceMode = false;

  // Finance controllers
  final _commissionCtrl = TextEditingController(text: '5.0');
  final _vatCtrl = TextEditingController(text: '17.0');
  final _currencyCtrl = TextEditingController(text: 'ج.س (SDG)');
  bool _autoFxCalculation = true;

  // Payment gateways
  bool _enableBankak = true;
  final _bankakAccountCtrl = TextEditingController(text: '1984256 - بنك الخرطوم (شركة مقاييس)');
  bool _enableFawry = true;
  bool _enableCashOnDelivery = true;

  // Security
  final _adminNameCtrl = TextEditingController(text: 'مدير النظام (Admin)');
  final _adminEmailCtrl = TextEditingController(text: 'admin@magaiees.com');
  final _currentPassCtrl = TextEditingController();
  final _newPassCtrl = TextEditingController();
  final _confirmPassCtrl = TextEditingController();

  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _loadSettings();
  }

  @override
  void dispose() {
    _tabController.dispose();
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
    _adminNameCtrl.dispose();
    _adminEmailCtrl.dispose();
    _currentPassCtrl.dispose();
    _newPassCtrl.dispose();
    _confirmPassCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadSettings() async {
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
        _maintenanceMode = data['maintenanceMode'] ?? false;

        _commissionCtrl.text = data['commission'] ?? _commissionCtrl.text;
        _vatCtrl.text = data['vat'] ?? _vatCtrl.text;
        _autoFxCalculation = data['autoFx'] ?? true;

        _enableBankak = data['enableBankak'] ?? true;
        _bankakAccountCtrl.text = data['bankakAccount'] ?? _bankakAccountCtrl.text;
        _enableFawry = data['enableFawry'] ?? true;
        _enableCashOnDelivery = data['enableCash'] ?? true;

        _adminNameCtrl.text = data['adminName'] ?? _adminNameCtrl.text;
        _adminEmailCtrl.text = data['adminEmail'] ?? _adminEmailCtrl.text;
      }
    } catch (_) {}
    setState(() => _isLoading = false);
  }

  Future<void> _saveSettings() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final data = {
        'nameAr': _nameArCtrl.text.trim(),
        'nameEn': _nameEnCtrl.text.trim(),
        'phone': _phoneCtrl.text.trim(),
        'whatsapp': _whatsappCtrl.text.trim(),
        'email': _emailCtrl.text.trim(),
        'about': _aboutCtrl.text.trim(),
        'maintenanceMode': _maintenanceMode,
        'commission': _commissionCtrl.text.trim(),
        'vat': _vatCtrl.text.trim(),
        'autoFx': _autoFxCalculation,
        'enableBankak': _enableBankak,
        'bankakAccount': _bankakAccountCtrl.text.trim(),
        'enableFawry': _enableFawry,
        'enableCash': _enableCashOnDelivery,
        'adminName': _adminNameCtrl.text.trim(),
        'adminEmail': _adminEmailCtrl.text.trim(),
      };
      await prefs.setString(_settingsStorageKey, jsonEncode(data));

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('✅ تم حفظ كافة إعدادات المنصة بنجاح في التخزين الدائم'),
            backgroundColor: Color(0xFF059669),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('خطأ في حفظ الإعدادات: $e'), backgroundColor: Colors.red),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      isRtl ? 'إعدادات المنصة والنظام' : 'Platform & System Settings',
                      style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                            fontWeight: FontWeight.bold,
                            color: const Color(0xFF0F172A),
                          ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      isRtl ? 'إدارة بيانات المنصة، السياسات المالية، بوابات الدفع، والأمان' : 'Manage platform data, finance policies, payments & security',
                      style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                    ),
                  ],
                ),
                FilledButton.icon(
                  style: FilledButton.styleFrom(
                    backgroundColor: const Color(0xFF1E40AF),
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                  ),
                  onPressed: _saveSettings,
                  icon: const Icon(Icons.save, size: 18),
                  label: Text(
                    isRtl ? 'حفظ كافة التغييرات' : 'Save All Changes',
                    style: const TextStyle(fontWeight: FontWeight.bold),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 20),

            // Tab Bar
            Container(
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.grey.shade200),
              ),
              child: TabBar(
                controller: _tabController,
                labelColor: const Color(0xFF1E40AF),
                unselectedLabelColor: Colors.grey.shade600,
                indicatorColor: const Color(0xFF1E40AF),
                indicatorWeight: 3,
                labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                tabs: [
                  Tab(icon: const Icon(Icons.settings, size: 18), text: isRtl ? 'الإعدادات العامة' : 'General'),
                  Tab(icon: const Icon(Icons.monetization_on, size: 18), text: isRtl ? 'المالية والعمولات' : 'Finance'),
                  Tab(icon: const Icon(Icons.account_balance, size: 18), text: isRtl ? 'بوابات الدفع الإلكتروني' : 'Payments'),
                  Tab(icon: const Icon(Icons.security, size: 18), text: isRtl ? 'الأمان وحساب المدير' : 'Security'),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Tab Content
            Expanded(
              child: Container(
                width: double.infinity,
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: Colors.grey.shade200),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.03),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: TabBarView(
                  controller: _tabController,
                  children: [
                    _buildGeneralTab(isRtl),
                    _buildFinanceTab(isRtl),
                    _buildPaymentsTab(isRtl),
                    _buildSecurityTab(isRtl),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // 1. General Settings Tab
  Widget _buildGeneralTab(bool isRtl) {
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _nameArCtrl,
                  decoration: const InputDecoration(
                    labelText: 'اسم المنصة (بالعربية)',
                    border: OutlineInputBorder(),
                    prefixIcon: Icon(Icons.business),
                  ),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: TextField(
                  controller: _nameEnCtrl,
                  decoration: const InputDecoration(
                    labelText: 'اسم المنصة (بالإنجليزية)',
                    border: OutlineInputBorder(),
                    prefixIcon: Icon(Icons.language),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _phoneCtrl,
                  decoration: const InputDecoration(
                    labelText: 'رقم هاتف الدعم الفني',
                    border: OutlineInputBorder(),
                    prefixIcon: Icon(Icons.phone),
                  ),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: TextField(
                  controller: _whatsappCtrl,
                  decoration: const InputDecoration(
                    labelText: 'رقم واتساب خدمة العملاء',
                    border: OutlineInputBorder(),
                    prefixIcon: Icon(Icons.chat),
                  ),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: TextField(
                  controller: _emailCtrl,
                  decoration: const InputDecoration(
                    labelText: 'البريد الإلكتروني للدعم',
                    border: OutlineInputBorder(),
                    prefixIcon: Icon(Icons.email),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          TextField(
            controller: _aboutCtrl,
            maxLines: 3,
            decoration: const InputDecoration(
              labelText: 'نبذة تعريفية عن منصة مقاييس',
              border: OutlineInputBorder(),
              prefixIcon: Icon(Icons.info_outline),
            ),
          ),
          const SizedBox(height: 20),
          const Divider(),
          const SizedBox(height: 10),
          SwitchListTile(
            title: const Text('وضع الصيانة (Maintenance Mode)', style: TextStyle(fontWeight: FontWeight.bold)),
            subtitle: const Text('إيقاف وصول المشترين مؤقتاً أثناء التحديثات البرمجية الكبرى'),
            value: _maintenanceMode,
            onChanged: (val) => setState(() => _maintenanceMode = val),
            activeColor: Colors.orange,
          ),
        ],
      ),
    );
  }

  // 2. Finance Tab
  Widget _buildFinanceTab(bool isRtl) {
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _commissionCtrl,
                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  decoration: const InputDecoration(
                    labelText: 'نسبة عمولة المنصة من المبيعات (%)',
                    border: OutlineInputBorder(),
                    prefixIcon: Icon(Icons.percent),
                    suffixText: '%',
                  ),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: TextField(
                  controller: _vatCtrl,
                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  decoration: const InputDecoration(
                    labelText: 'نسبة ضريبة القيمة المضافة (VAT %)',
                    border: OutlineInputBorder(),
                    prefixIcon: Icon(Icons.receipt_long),
                    suffixText: '%',
                  ),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: TextField(
                  controller: _currencyCtrl,
                  readOnly: true,
                  decoration: const InputDecoration(
                    labelText: 'العملة الأساسية للنظام',
                    border: OutlineInputBorder(),
                    prefixIcon: Icon(Icons.monetization_on),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),
          const Divider(),
          const SizedBox(height: 10),
          SwitchListTile(
            title: const Text('التحويل التلقائي للأسعار بالدولار (USD/SDG Auto-Recalculate)', style: TextStyle(fontWeight: FontWeight.bold)),
            subtitle: const Text('عند تغيير سعر صرف الدولار، تتحدث أسعار جميع الأصناف بالجنيه السوداني فورياً'),
            value: _autoFxCalculation,
            onChanged: (val) => setState(() => _autoFxCalculation = val),
            activeColor: const Color(0xFF1E40AF),
          ),
        ],
      ),
    );
  }

  // 3. Payments Tab
  Widget _buildPaymentsTab(bool isRtl) {
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Bankak Section
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.green.withValues(alpha: 0.05),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.green.withValues(alpha: 0.2)),
            ),
            child: Column(
              children: [
                SwitchListTile(
                  title: const Text('تطبيق بنكك - بنك الخرطوم (Bankak App)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                  subtitle: const Text('تمكين المشترين من الدفع والتحويل المباشر عبر حساب بنكك'),
                  value: _enableBankak,
                  onChanged: (val) => setState(() => _enableBankak = val),
                  activeColor: Colors.green,
                ),
                if (_enableBankak) ...[
                  const SizedBox(height: 12),
                  TextField(
                    controller: _bankakAccountCtrl,
                    decoration: const InputDecoration(
                      labelText: 'بيانات الحساب ورقم بنكك لاستقبال التحويلات',
                      border: OutlineInputBorder(),
                      prefixIcon: Icon(Icons.account_balance),
                    ),
                  ),
                ],
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Fawry Section
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.blue.withValues(alpha: 0.05),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.blue.withValues(alpha: 0.2)),
            ),
            child: SwitchListTile(
              title: const Text('تطبيق فوري (Fawry App) / المحافظ الإلكترونية', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
              subtitle: const Text('قبول الدفع عبر محفظة فوري وبطاقات الصراف الآلي'),
              value: _enableFawry,
              onChanged: (val) => setState(() => _enableFawry = val),
              activeColor: Colors.blue,
            ),
          ),
          const SizedBox(height: 16),

          // Cash on Delivery
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.amber.withValues(alpha: 0.05),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.amber.withValues(alpha: 0.2)),
            ),
            child: SwitchListTile(
              title: const Text('الدفع نقداً عند الاستلام والتنزيل (Cash on Delivery)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
              subtitle: const Text('الدفع المباشر عند استلام شحنة مواد البناء في موقع المشروع'),
              value: _enableCashOnDelivery,
              onChanged: (val) => setState(() => _enableCashOnDelivery = val),
              activeColor: Colors.amber.shade800,
            ),
          ),
        ],
      ),
    );
  }

  // 4. Security Tab
  Widget _buildSecurityTab(bool isRtl) {
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _adminNameCtrl,
                  decoration: const InputDecoration(
                    labelText: 'اسم مدير النظام',
                    border: OutlineInputBorder(),
                    prefixIcon: Icon(Icons.badge),
                  ),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: TextField(
                  controller: _adminEmailCtrl,
                  decoration: const InputDecoration(
                    labelText: 'البريد الإلكتروني لحساب الإدارة',
                    border: OutlineInputBorder(),
                    prefixIcon: Icon(Icons.admin_panel_settings),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),
          Text(
            isRtl ? 'تغيير كلمة مرور الإدارة:' : 'Change Admin Password:',
            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _currentPassCtrl,
                  obscureText: true,
                  decoration: const InputDecoration(
                    labelText: 'كلمة المرور الحالية',
                    border: OutlineInputBorder(),
                    prefixIcon: Icon(Icons.lock_outline),
                  ),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: TextField(
                  controller: _newPassCtrl,
                  obscureText: true,
                  decoration: const InputDecoration(
                    labelText: 'كلمة المرور الجديدة',
                    border: OutlineInputBorder(),
                    prefixIcon: Icon(Icons.lock),
                  ),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: TextField(
                  controller: _confirmPassCtrl,
                  obscureText: true,
                  decoration: const InputDecoration(
                    labelText: 'تأكيد كلمة المرور الجديدة',
                    border: OutlineInputBorder(),
                    prefixIcon: Icon(Icons.check_circle_outline),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Align(
            alignment: isRtl ? Alignment.centerLeft : Alignment.centerRight,
            child: OutlinedButton.icon(
              onPressed: () {
                if (_newPassCtrl.text.isNotEmpty && _newPassCtrl.text == _confirmPassCtrl.text) {
                  _currentPassCtrl.clear();
                  _newPassCtrl.clear();
                  _confirmPassCtrl.clear();
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('🔐 تم تحديث كلمة مرور مدير النظام بنجاح'),
                      backgroundColor: Color(0xFF059669),
                    ),
                  );
                } else {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('الرجاء التأكد من تطابق كلمة المرور الجديدة'),
                      backgroundColor: Colors.red,
                    ),
                  );
                }
              },
              icon: const Icon(Icons.security_update_good, size: 18),
              label: Text(isRtl ? 'تحديث كلمة المرور' : 'Update Password'),
            ),
          ),
        ],
      ),
    );
  }
}
