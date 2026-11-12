import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/core/theme/theme_provider.dart';
import 'package:magaiees_marketplace/core/providers/global_settings_provider.dart';
import 'package:magaiees_marketplace/features/admin/subscribers/application/subscribers_provider.dart';
import '../providers/settings_tab_provider.dart';

// ── Palette ──────────────────────────────────────────────────────────
const Color _kNavy = Color(0xFF0F172A);
const Color _kRoyalBlue = Color(0xFF1E40AF);
const Color _kEmerald = Color(0xFF059669);
const Color _kPurple = Color(0xFF7E22CE);
const Color _kAmber = Color(0xFFD97706);
const Color _kRose = Color(0xFFDC2626);
const Color _kLightBg = Color(0xFFF8FAFC);
const Color _kCardBorder = Color(0xFFE2E8F0);

class AdminSettingsScreen extends ConsumerStatefulWidget {
  const AdminSettingsScreen({super.key});

  @override
  ConsumerState<AdminSettingsScreen> createState() => _AdminSettingsScreenState();
}

class _AdminSettingsScreenState extends ConsumerState<AdminSettingsScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  static const String _settingsStorageKey = 'magaiees_platform_enterprise_settings';

  bool _isLoading = true;
  bool _isSaving = false;
  bool _hasUnsavedChanges = false;

  // Diagnostics State
  bool _isCheckingHealth = false;
  int _dbLatencyMs = 45;
  bool _dbOnline = true;
  bool _storageOnline = true;
  bool _realtimeOnline = true;

  // ── 1. System & Identity ──────────────────────────────────────────
  final _nameArCtrl = TextEditingController(text: 'منصة مقاييس لمواد البناء والتوريدات');
  final _nameEnCtrl = TextEditingController(text: 'Magaiees Building Materials Marketplace');
  final _sloganArCtrl = TextEditingController(text: 'سوقك الهندسي المتكامل لتوريد مواد البناء');
  final _sloganEnCtrl = TextEditingController(text: 'Your Integrated Engineering Marketplace');
  final _phoneCtrl = TextEditingController(text: '+968 9905 1925');
  final _whatsappCtrl = TextEditingController(text: '+968 9905 1925');
  final _emailCtrl = TextEditingController(text: 'support@magaiees.com');
  final _websiteCtrl = TextEditingController(text: 'https://magaiees.com');
  final _aboutCtrl = TextEditingController(
    text: 'مقاييس هي منصة رقمية رائدة تربط بين تجار ومصانع مواد البناء والمقاولين والعملاء لتسهيل عمليات البيع والشراء بأسعار تنافسية وخيارات متعددة مع التوصيل الفوري.',
  );
  final _maintenanceMsgCtrl = TextEditingController(
    text: 'النظام تحت الصيانة المجدولة لتطوير الأداء. سنعود للعمل قريباً جداً!',
  );
  bool _maintenanceMode = false;
  bool _darkModeEnabled = false;
  bool _optionalUpdatesOnly = true;

  // Social & Store URLs
  final _playStoreCtrl = TextEditingController(text: 'https://play.google.com/store/apps/details?id=com.magaiees.app');
  final _appStoreCtrl = TextEditingController(text: 'https://apps.apple.com/app/magaiees/id123456789');
  final _facebookCtrl = TextEditingController(text: 'https://facebook.com/magaiees');
  final _twitterCtrl = TextEditingController(text: 'https://x.com/magaiees');

  // ── 2. Finance & Taxes (النسب المالية والضرائب والعملات) ────────────
  final _commissionCtrl = TextEditingController(text: '5.0');
  bool _isTaxAndCommissionEnabled = false;
  final _vatCtrl = TextEditingController(text: '17.0');
  final _taxNumberCtrl = TextEditingController(text: 'SD-VAT-9021482');
  final _currencyCtrl = TextEditingController(text: 'ج.س (SDG)');
  final _platformUsdRateCtrl = TextEditingController();

  // ── 2.A Buyer Payment Settings: Exclusive Magaiees (إعدادات دفع منتجات مقاييس الحصرية) ─────
  bool _exclusiveBankak = true;
  final _exclusiveBankakAccountCtrl = TextEditingController(text: '1984256');
  final _exclusiveBankakNameCtrl = TextEditingController(text: 'شركة مقاييس للتوريدات الهندسية');
  final _exclusiveBankakIbanCtrl = TextEditingController(text: 'SD84BOKH0000000001984256');

  bool _exclusiveOcash = true;
  final _exclusiveOcashAccountCtrl = TextEditingController(text: '0912300105');
  final _exclusiveOcashNameCtrl = TextEditingController(text: 'محفظة مقاييس - أوكاش');

  bool _exclusiveCod = true;
  final _exclusiveMaxCodCtrl = TextEditingController(text: '5000000');

  bool _exclusiveWire = true;
  final _exclusiveWireBankCtrl = TextEditingController(text: 'بنك فيصل الإسلامي');
  final _exclusiveWireAccountCtrl = TextEditingController(text: '12345678');
  final _exclusiveWireNameCtrl = TextEditingController(text: 'شركة مقاييس للتوريدات الهندسية');

  bool _exclusiveFawry = true;
  final _exclusiveMaxFawryCtrl = TextEditingController(text: '10000000');

  bool _exclusiveB2b = true;
  bool _exclusivePartialCod = true;
  final _exclusivePartialMinPercentCtrl = TextEditingController(text: '30');

  // ── 2.B Buyer Payment Settings: Marketplace Merchants (إعدادات دفع تجار السوق والموردين) ─────
  bool _merchantAllowBankak = true;
  bool _merchantAllowOcash = true;
  bool _merchantAllowCod = true;
  final _merchantMaxCodCtrl = TextEditingController(text: '3000000');
  bool _merchantAllowWire = true;
  bool _merchantAllowB2b = true;
  bool _merchantAllowPartialCod = true;

  // Delivery & Logistics Defaults (إعدادات الشحن وتكاليف التوصيل)
  final _baseDeliveryFeeCtrl = TextEditingController(text: '5000');
  final _deliveryPerKmCtrl = TextEditingController(text: '350');
  final _freeDeliveryThresholdCtrl = TextEditingController(text: '2000000');

  // ── 3. Account Upgrades & Registration ────────────────────────────
  bool _enableMerchantUpgrade = true;
  bool _autoApproveVerifiedDocuments = false;
  bool _requireCommercialRegister = true;
  bool _requireTaxCertificate = true;

  // ── 4. Gamification & Loyalty ─────────────────────────────────────
  bool _enableGamification = true;
  final _pointsPerOrderCtrl = TextEditingController(text: '10');
  final _pointsPerReviewCtrl = TextEditingController(text: '5');
  final _pointsToSdgRatioCtrl = TextEditingController(text: '100'); // 100 points = 1 SDG
  final _bronzeTierPointsCtrl = TextEditingController(text: '1000');
  final _silverTierPointsCtrl = TextEditingController(text: '5000');
  final _goldTierPointsCtrl = TextEditingController(text: '15000');

  // ── 5. Admin Security, Sessions & Backup ──────────────────────────
  final _adminNameCtrl = TextEditingController(text: 'مدير النظام (Admin)');
  final _adminEmailCtrl = TextEditingController(text: 'admin@magaiees.com');
  final _sessionTimeoutCtrl = TextEditingController(text: '30'); // 30 minutes
  final _currentPassCtrl = TextEditingController();
  final _newPassCtrl = TextEditingController();
  final _confirmPassCtrl = TextEditingController();
  bool _twoFactorAuth = false;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 7, vsync: this);
    
    // Jump to tab if requested externally
    final requestedTab = ref.read(settingsTabIndexProvider);
    if (requestedTab > 0 && requestedTab < 7) {
      _tabController.index = requestedTab;
      Future.microtask(() => ref.read(settingsTabIndexProvider.notifier).state = 0);
    }
    
    _loadSettings();
    _runHealthCheck();
  }

  @override
  void dispose() {
    _tabController.dispose();
    _nameArCtrl.dispose();
    _nameEnCtrl.dispose();
    _sloganArCtrl.dispose();
    _sloganEnCtrl.dispose();
    _phoneCtrl.dispose();
    _whatsappCtrl.dispose();
    _emailCtrl.dispose();
    _websiteCtrl.dispose();
    _aboutCtrl.dispose();
    _maintenanceMsgCtrl.dispose();
    _playStoreCtrl.dispose();
    _appStoreCtrl.dispose();
    _facebookCtrl.dispose();
    _twitterCtrl.dispose();
    _commissionCtrl.dispose();
    _vatCtrl.dispose();
    _taxNumberCtrl.dispose();
    _currencyCtrl.dispose();
    _platformUsdRateCtrl.dispose();
    _exclusiveBankakAccountCtrl.dispose();
    _exclusiveBankakNameCtrl.dispose();
    _exclusiveBankakIbanCtrl.dispose();
    _exclusiveOcashAccountCtrl.dispose();
    _exclusiveOcashNameCtrl.dispose();
    _exclusiveMaxCodCtrl.dispose();
    _exclusiveWireBankCtrl.dispose();
    _exclusiveWireAccountCtrl.dispose();
    _exclusiveWireNameCtrl.dispose();
    _exclusiveMaxFawryCtrl.dispose();
    _exclusivePartialMinPercentCtrl.dispose();
    _merchantMaxCodCtrl.dispose();
    _baseDeliveryFeeCtrl.dispose();
    _deliveryPerKmCtrl.dispose();
    _freeDeliveryThresholdCtrl.dispose();
    _pointsPerOrderCtrl.dispose();
    _pointsPerReviewCtrl.dispose();
    _pointsToSdgRatioCtrl.dispose();
    _bronzeTierPointsCtrl.dispose();
    _silverTierPointsCtrl.dispose();
    _goldTierPointsCtrl.dispose();
    _adminNameCtrl.dispose();
    _adminEmailCtrl.dispose();
    _sessionTimeoutCtrl.dispose();
    _currentPassCtrl.dispose();
    _newPassCtrl.dispose();
    _confirmPassCtrl.dispose();
    super.dispose();
  }

  void _markChanged() {
    if (!_hasUnsavedChanges) {
      setState(() => _hasUnsavedChanges = true);
    }
  }

  Future<void> _runHealthCheck() async {
    setState(() => _isCheckingHealth = true);
    final stopwatch = Stopwatch()..start();
    try {
      final supabase = Supabase.instance.client;
      await supabase.from('users').select('id').limit(1);
      stopwatch.stop();
      if (mounted) {
        setState(() {
          _dbLatencyMs = stopwatch.elapsedMilliseconds;
          _dbOnline = true;
          _storageOnline = true;
          _realtimeOnline = true;
          _isCheckingHealth = false;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() {
          _dbLatencyMs = 999;
          _dbOnline = false;
          _isCheckingHealth = false;
        });
      }
    }
  }

  Future<void> _loadSettings() async {
    try {
      final supabase = Supabase.instance.client;

      // 1. Load settings from users table where role = 'admin'
      final adminData = await supabase.from('users').select('permissions').eq('role', 'admin').limit(1).maybeSingle();
      if (adminData != null && adminData['permissions'] is Map) {
        final perms = adminData['permissions'] as Map<String, dynamic>;
        if (perms.containsKey('enable_merchant_upgrade')) {
          _enableMerchantUpgrade = perms['enable_merchant_upgrade'] == true;
        }
        if (perms.containsKey('platform_settings') && perms['platform_settings'] is Map) {
          final ps = perms['platform_settings'] as Map<String, dynamic>;
          _populateControllersFromMap(ps);
        }
      }

      // 2. Also load gamification table if exists
      try {
        final res = await supabase.from('gamification_settings').select().eq('id', 1).maybeSingle();
        if (res != null) {
          _pointsPerOrderCtrl.text = (res['points_per_order'] ?? _pointsPerOrderCtrl.text).toString();
          if (res.containsKey('commission_percentage') && res['commission_percentage'] != null) {
            _commissionCtrl.text = res['commission_percentage'].toString();
          }
        }
      } catch (_) {}

      await _loadFromLocalPrefs();
    } catch (_) {
      await _loadFromLocalPrefs();
    }

    if (mounted) {
      setState(() {
        _isLoading = false;
        _darkModeEnabled = ref.read(themeModeProvider) == ThemeMode.dark;
        _hasUnsavedChanges = false;
      });
    }
  }

  void _populateControllersFromMap(Map<String, dynamic> data) {
    _nameArCtrl.text = data['nameAr'] ?? _nameArCtrl.text;
    _nameEnCtrl.text = data['nameEn'] ?? _nameEnCtrl.text;
    _sloganArCtrl.text = data['sloganAr'] ?? _sloganArCtrl.text;
    _sloganEnCtrl.text = data['sloganEn'] ?? _sloganEnCtrl.text;
    _phoneCtrl.text = data['phone'] ?? _phoneCtrl.text;
    _whatsappCtrl.text = data['whatsapp'] ?? _whatsappCtrl.text;
    _emailCtrl.text = data['email'] ?? _emailCtrl.text;
    _websiteCtrl.text = data['website'] ?? _websiteCtrl.text;
    _aboutCtrl.text = data['about'] ?? _aboutCtrl.text;
    _maintenanceMsgCtrl.text = data['maintenanceMsg'] ?? _maintenanceMsgCtrl.text;
    _maintenanceMode = data['maintenanceMode'] ?? _maintenanceMode;
    _playStoreCtrl.text = data['playStore'] ?? _playStoreCtrl.text;
    _appStoreCtrl.text = data['appStore'] ?? _appStoreCtrl.text;
    _facebookCtrl.text = data['facebook'] ?? _facebookCtrl.text;
    _twitterCtrl.text = data['twitter'] ?? _twitterCtrl.text;

    _commissionCtrl.text = data['commission'] ?? _commissionCtrl.text;
    _vatCtrl.text = data['vat'] ?? _vatCtrl.text;
    _isTaxAndCommissionEnabled = data['is_tax_and_commission_enabled'] ?? data['isTaxAndCommissionEnabled'] ?? false;
    _taxNumberCtrl.text = data['taxNumber'] ?? _taxNumberCtrl.text;
    _currencyCtrl.text = data['currency'] ?? _currencyCtrl.text;
    _platformUsdRateCtrl.text = data['platformUsdRate']?.toString() ?? data['platform_usd_rate']?.toString() ?? _platformUsdRateCtrl.text;

    // 2.A Buyer Payment Settings: Exclusive Magaiees
    _exclusiveBankak = data['exclusive_bankak'] ?? data['exclusiveBankak'] ?? data['enableBankak'] ?? data['is_bankak_enabled'] ?? true;
    _exclusiveBankakAccountCtrl.text = data['exclusiveBankakAccount'] ?? data['bankakAccount'] ?? _exclusiveBankakAccountCtrl.text;
    _exclusiveBankakNameCtrl.text = data['exclusiveBankakName'] ?? data['bankakName'] ?? _exclusiveBankakNameCtrl.text;
    _exclusiveBankakIbanCtrl.text = data['exclusiveBankakIban'] ?? data['bankakIban'] ?? _exclusiveBankakIbanCtrl.text;

    _exclusiveOcash = data['exclusive_ocash'] ?? data['exclusiveOcash'] ?? data['enableOcash'] ?? data['is_ocash_enabled'] ?? true;
    _exclusiveOcashAccountCtrl.text = data['exclusiveOcashAccount'] ?? data['ocashAccount'] ?? _exclusiveOcashAccountCtrl.text;
    _exclusiveOcashNameCtrl.text = data['exclusiveOcashName'] ?? data['ocashName'] ?? _exclusiveOcashNameCtrl.text;

    _exclusiveCod = data['exclusive_cod'] ?? data['exclusiveCod'] ?? data['enableCash'] ?? data['is_cod_enabled'] ?? true;
    _exclusiveMaxCodCtrl.text = data['exclusiveMaxCod'] ?? data['maxCodAmount'] ?? _exclusiveMaxCodCtrl.text;

    _exclusiveWire = data['exclusive_wire'] ?? data['exclusiveWire'] ?? data['enableDirectWire'] ?? data['is_wire_enabled'] ?? true;
    _exclusiveWireBankCtrl.text = data['exclusiveWireBank'] ?? data['wireBankName'] ?? _exclusiveWireBankCtrl.text;
    _exclusiveWireAccountCtrl.text = data['exclusiveWireAccount'] ?? data['wireAccount'] ?? _exclusiveWireAccountCtrl.text;
    _exclusiveWireNameCtrl.text = data['exclusiveWireName'] ?? data['wireAccountName'] ?? _exclusiveWireNameCtrl.text;

    _exclusiveFawry = data['exclusive_fawry'] ?? data['exclusiveFawry'] ?? data['enableFawry'] ?? true;
    _exclusiveMaxFawryCtrl.text = data['exclusiveMaxFawry'] ?? data['maxElectronicPayAmount'] ?? _exclusiveMaxFawryCtrl.text;

    _exclusiveB2b = data['exclusive_b2b'] ?? data['exclusiveB2b'] ?? data['enableB2bCredit'] ?? true;
    _exclusivePartialCod = data['exclusive_partial_cod'] ?? data['exclusivePartialCod'] ?? true;
    _exclusivePartialMinPercentCtrl.text = data['exclusivePartialMinPercent']?.toString() ?? data['exclusive_partial_min_percent']?.toString() ?? _exclusivePartialMinPercentCtrl.text;

    // 2.B Buyer Payment Settings: Marketplace Merchants
    _merchantAllowBankak = data['merchant_allow_bankak'] ?? data['merchantAllowBankak'] ?? true;
    _merchantAllowOcash = data['merchant_allow_ocash'] ?? data['merchantAllowOcash'] ?? true;
    _merchantAllowCod = data['merchant_allow_cod'] ?? data['merchantAllowCod'] ?? true;
    _merchantMaxCodCtrl.text = data['merchantMaxCod']?.toString() ?? _merchantMaxCodCtrl.text;
    _merchantAllowWire = data['merchant_allow_wire'] ?? data['merchantAllowWire'] ?? true;
    _merchantAllowB2b = data['merchant_allow_b2b'] ?? data['merchantAllowB2b'] ?? true;
    _merchantAllowPartialCod = data['merchant_allow_partial_cod'] ?? data['merchantAllowPartialCod'] ?? true;

    // Delivery & Logistics Defaults
    _baseDeliveryFeeCtrl.text = data['baseDeliveryFee'] ?? _baseDeliveryFeeCtrl.text;
    _deliveryPerKmCtrl.text = data['deliveryPerKm'] ?? _deliveryPerKmCtrl.text;
    _freeDeliveryThresholdCtrl.text = data['freeDeliveryThreshold'] ?? _freeDeliveryThresholdCtrl.text;

    _enableMerchantUpgrade = data['enableMerchantUpgrade'] ?? _enableMerchantUpgrade;
    _autoApproveVerifiedDocuments = data['autoApproveDocs'] ?? _autoApproveVerifiedDocuments;
    _requireCommercialRegister = data['requireCr'] ?? _requireCommercialRegister;
    _requireTaxCertificate = data['requireTaxCert'] ?? _requireTaxCertificate;

    _enableGamification = data['enableGamification'] ?? _enableGamification;
    _pointsPerOrderCtrl.text = data['pointsPerOrder'] ?? _pointsPerOrderCtrl.text;
    _pointsPerReviewCtrl.text = data['pointsPerReview'] ?? _pointsPerReviewCtrl.text;
    _pointsToSdgRatioCtrl.text = data['pointsToSdgRatio'] ?? _pointsToSdgRatioCtrl.text;
    _bronzeTierPointsCtrl.text = data['bronzePoints'] ?? _bronzeTierPointsCtrl.text;
    _silverTierPointsCtrl.text = data['silverPoints'] ?? _silverTierPointsCtrl.text;
    _goldTierPointsCtrl.text = data['goldPoints'] ?? _goldTierPointsCtrl.text;

    _adminNameCtrl.text = data['adminName'] ?? _adminNameCtrl.text;
    _adminEmailCtrl.text = data['adminEmail'] ?? _adminEmailCtrl.text;
    _sessionTimeoutCtrl.text = data['sessionTimeout'] ?? _sessionTimeoutCtrl.text;
    _twoFactorAuth = data['twoFactorAuth'] ?? _twoFactorAuth;
  }

  Future<void> _loadFromLocalPrefs() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final savedStr = prefs.getString(_settingsStorageKey);
      if (savedStr != null && savedStr.isNotEmpty) {
        final data = jsonDecode(savedStr) as Map<String, dynamic>;
        _populateControllersFromMap(data);
      }
    } catch (_) {}
  }

  Map<String, dynamic> _collectSettingsMap() {
    return {
      'nameAr': _nameArCtrl.text.trim(),
      'nameEn': _nameEnCtrl.text.trim(),
      'sloganAr': _sloganArCtrl.text.trim(),
      'sloganEn': _sloganEnCtrl.text.trim(),
      'phone': _phoneCtrl.text.trim(),
      'whatsapp': _whatsappCtrl.text.trim(),
      'email': _emailCtrl.text.trim(),
      'website': _websiteCtrl.text.trim(),
      'about': _aboutCtrl.text.trim(),
      'maintenanceMsg': _maintenanceMsgCtrl.text.trim(),
      'maintenanceMode': _maintenanceMode,
      'darkModeEnabled': _darkModeEnabled,
      'playStore': _playStoreCtrl.text.trim(),
      'appStore': _appStoreCtrl.text.trim(),
      'facebook': _facebookCtrl.text.trim(),
      'twitter': _twitterCtrl.text.trim(),

      'commission': _commissionCtrl.text.trim(),
      'vat': _vatCtrl.text.trim(),
      'is_tax_and_commission_enabled': _isTaxAndCommissionEnabled,
      'isTaxAndCommissionEnabled': _isTaxAndCommissionEnabled,
      'taxNumber': _taxNumberCtrl.text.trim(),
      'currency': _currencyCtrl.text.trim(),
      'platformUsdRate': _platformUsdRateCtrl.text.trim(),

      // 2.A Buyer Payment Settings: Exclusive Magaiees
      'exclusive_bankak': _exclusiveBankak,
      'exclusiveBankak': _exclusiveBankak,
      'exclusiveBankakAccount': _exclusiveBankakAccountCtrl.text.trim(),
      'exclusiveBankakName': _exclusiveBankakNameCtrl.text.trim(),
      'exclusiveBankakIban': _exclusiveBankakIbanCtrl.text.trim(),

      'exclusive_ocash': _exclusiveOcash,
      'exclusiveOcash': _exclusiveOcash,
      'exclusiveOcashAccount': _exclusiveOcashAccountCtrl.text.trim(),
      'exclusiveOcashName': _exclusiveOcashNameCtrl.text.trim(),

      'exclusive_cod': _exclusiveCod,
      'exclusiveCod': _exclusiveCod,
      'exclusiveMaxCod': _exclusiveMaxCodCtrl.text.trim(),

      'exclusive_wire': _exclusiveWire,
      'exclusiveWire': _exclusiveWire,
      'exclusiveWireBank': _exclusiveWireBankCtrl.text.trim(),
      'exclusiveWireAccount': _exclusiveWireAccountCtrl.text.trim(),
      'exclusiveWireName': _exclusiveWireNameCtrl.text.trim(),

      'exclusive_fawry': _exclusiveFawry,
      'exclusiveFawry': _exclusiveFawry,
      'exclusiveMaxFawry': _exclusiveMaxFawryCtrl.text.trim(),

      'exclusive_b2b': _exclusiveB2b,
      'exclusiveB2b': _exclusiveB2b,
      'exclusive_partial_cod': _exclusivePartialCod,
      'exclusivePartialCod': _exclusivePartialCod,
      'exclusivePartialMinPercent': _exclusivePartialMinPercentCtrl.text.trim(),

      // 2.B Buyer Payment Settings: Marketplace Merchants
      'merchant_allow_bankak': _merchantAllowBankak,
      'merchantAllowBankak': _merchantAllowBankak,
      'merchant_allow_ocash': _merchantAllowOcash,
      'merchantAllowOcash': _merchantAllowOcash,
      'merchant_allow_cod': _merchantAllowCod,
      'merchantAllowCod': _merchantAllowCod,
      'merchantMaxCod': _merchantMaxCodCtrl.text.trim(),
      'merchant_allow_wire': _merchantAllowWire,
      'merchantAllowWire': _merchantAllowWire,
      'merchant_allow_b2b': _merchantAllowB2b,
      'merchantAllowB2b': _merchantAllowB2b,
      'merchant_allow_partial_cod': _merchantAllowPartialCod,
      'merchantAllowPartialCod': _merchantAllowPartialCod,

      // Logistics
      'baseDeliveryFee': _baseDeliveryFeeCtrl.text.trim(),
      'deliveryPerKm': _deliveryPerKmCtrl.text.trim(),
      'freeDeliveryThreshold': _freeDeliveryThresholdCtrl.text.trim(),

      'enableMerchantUpgrade': _enableMerchantUpgrade,
      'autoApproveDocs': _autoApproveVerifiedDocuments,
      'requireCr': _requireCommercialRegister,
      'requireTaxCert': _requireTaxCertificate,

      'enableGamification': _enableGamification,
      'pointsPerOrder': _pointsPerOrderCtrl.text.trim(),
      'pointsPerReview': _pointsPerReviewCtrl.text.trim(),
      'pointsToSdgRatio': _pointsToSdgRatioCtrl.text.trim(),
      'bronzePoints': _bronzeTierPointsCtrl.text.trim(),
      'silverPoints': _silverTierPointsCtrl.text.trim(),
      'goldPoints': _goldTierPointsCtrl.text.trim(),

      'adminName': _adminNameCtrl.text.trim(),
      'adminEmail': _adminEmailCtrl.text.trim(),
      'sessionTimeout': _sessionTimeoutCtrl.text.trim(),
      'twoFactorAuth': _twoFactorAuth,
      'updated_at': DateTime.now().toIso8601String(),
    };
  }

  Future<void> _saveSettings() async {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    setState(() => _isSaving = true);
    try {
      final supabase = Supabase.instance.client;
      final pSettings = _collectSettingsMap();

      // 1. Save to users table for global platform-wide sync
      final currentAdmin = await supabase.from('users').select('permissions').eq('role', 'admin').limit(1).maybeSingle();
      final existingPerms = (currentAdmin != null && currentAdmin['permissions'] is Map)
          ? Map<String, dynamic>.from(currentAdmin['permissions'] as Map)
          : <String, dynamic>{};

      existingPerms['vat'] = _vatCtrl.text.trim();
      existingPerms['is_tax_and_commission_enabled'] = _isTaxAndCommissionEnabled;
      existingPerms['exclusive_bankak'] = _exclusiveBankak;
      existingPerms['exclusive_ocash'] = _exclusiveOcash;
      existingPerms['exclusive_cod'] = _exclusiveCod;
      existingPerms['exclusive_wire'] = _exclusiveWire;
      existingPerms['exclusive_fawry'] = _exclusiveFawry;
      existingPerms['exclusive_b2b'] = _exclusiveB2b;
      existingPerms['exclusive_partial_cod'] = _exclusivePartialCod;
      existingPerms['merchant_allow_bankak'] = _merchantAllowBankak;
      existingPerms['merchant_allow_ocash'] = _merchantAllowOcash;
      existingPerms['merchant_allow_cod'] = _merchantAllowCod;
      existingPerms['merchant_allow_wire'] = _merchantAllowWire;
      existingPerms['merchant_allow_b2b'] = _merchantAllowB2b;
      existingPerms['merchant_allow_partial_cod'] = _merchantAllowPartialCod;
      // Backward compatibility
      existingPerms['is_bankak_enabled'] = _exclusiveBankak;
      existingPerms['is_ocash_enabled'] = _exclusiveOcash;
      existingPerms['is_cod_enabled'] = _exclusiveCod;
      existingPerms['is_wire_enabled'] = _exclusiveWire;
      existingPerms['enable_fawry'] = _exclusiveFawry;
      existingPerms['enable_b2b_credit'] = _exclusiveB2b;
      existingPerms['enable_merchant_upgrade'] = _enableMerchantUpgrade;
      existingPerms['platform_usd_rate'] = double.tryParse(_platformUsdRateCtrl.text.trim()) ?? ref.read(globalSettingsProvider).platformUsdRate;
      existingPerms['platform_settings'] = pSettings;

      await supabase.from('users').update({
        'permissions': existingPerms,
      }).eq('role', 'admin');

      // Invalidate global settings provider so changes take effect across app immediately
      ref.invalidate(globalSettingsProvider);

      // Update provider in-memory
      ref.read(merchantUpgradeSettingProvider.notifier).toggleSetting(_enableMerchantUpgrade);

      // 2. Also save to gamification_settings if available
      try {
        await supabase.from('gamification_settings').upsert({
          'id': 1,
          'points_per_order': int.tryParse(_pointsPerOrderCtrl.text.trim()) ?? 10,
          'commission_percentage': double.tryParse(_commissionCtrl.text.trim()) ?? 5.0,
        });
      } catch (_) {}

      // 3. Save to local preferences
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_settingsStorageKey, jsonEncode(pSettings));

      if (mounted) {
        setState(() {
          _isSaving = false;
          _hasUnsavedChanges = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                const Icon(Icons.check_circle_rounded, color: Colors.white, size: 22),
                const SizedBox(width: 10),
                Text(
                  isRtl ? '✅ تم حفظ كافة إعدادات المنظومة ومزامنتها بنجاح' : '✅ All platform settings saved & synced successfully',
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                ),
              ],
            ),
            backgroundColor: _kEmerald,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isSaving = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('حدث خطأ أثناء حفظ الإعدادات: $e'),
            backgroundColor: _kRose,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  void _exportSettingsJson() {
    final settingsMap = _collectSettingsMap();
    final jsonStr = const JsonEncoder.withIndent('  ').convert(settingsMap);
    Clipboard.setData(ClipboardData(text: jsonStr));

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.download_rounded, color: _kRoyalBlue),
            SizedBox(width: 10),
            Text('تصدير إعدادات المنظومة (JSON)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          ],
        ),
        content: SizedBox(
          width: 550,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(color: const Color(0xFFEFF6FF), borderRadius: BorderRadius.circular(10)),
                child: const Text(
                  '📋 تم نسخ نص الإعدادات الكامل إلى الحافظة (Clipboard). يمكنك حفظه كنسخة احتياطية للرجوع إليها في أي وقت.',
                  style: TextStyle(fontSize: 13, color: _kRoyalBlue, fontWeight: FontWeight.w600),
                ),
              ),
              const SizedBox(height: 14),
              Container(
                height: 200,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFF1E293B),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: SingleChildScrollView(
                  child: Text(
                    jsonStr,
                    style: const TextStyle(fontFamily: 'monospace', fontSize: 11, color: Color(0xFF38BDF8)),
                  ),
                ),
              ),
            ],
          ),
        ),
        actions: [
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: _kRoyalBlue),
            onPressed: () => Navigator.pop(ctx),
            child: const Text('إغلاق'),
          ),
        ],
      ),
    );
  }

  void _showImportJsonDialog() {
    final jsonInputCtrl = TextEditingController();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.upload_rounded, color: _kEmerald),
            SizedBox(width: 10),
            Text('استيراد إعدادات من ملف JSON', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          ],
        ),
        content: SizedBox(
          width: 550,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('ألصق كود JSON الخاص بالإعدادات السابقة هنا لاستعادتها فورياً:'),
              const SizedBox(height: 12),
              TextField(
                controller: jsonInputCtrl,
                maxLines: 8,
                decoration: InputDecoration(
                  hintText: '{\n  "nameAr": "منصة مقاييس...",\n  "commission": "5.0"\n}',
                  hintStyle: const TextStyle(fontFamily: 'monospace', fontSize: 12),
                  filled: true,
                  fillColor: const Color(0xFFF8FAFC),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                ),
                style: const TextStyle(fontFamily: 'monospace', fontSize: 12),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('إلغاء')),
          FilledButton.icon(
            style: FilledButton.styleFrom(backgroundColor: _kEmerald),
            onPressed: () {
              try {
                final parsed = jsonDecode(jsonInputCtrl.text.trim()) as Map<String, dynamic>;
                setState(() {
                  _populateControllersFromMap(parsed);
                  _hasUnsavedChanges = true;
                });
                Navigator.pop(ctx);
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('✅ تم استيراد الإعدادات بنجاح. اضغط حفظ التغييرات لاعتمادها.'), backgroundColor: _kEmerald),
                );
              } catch (e) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text('❌ صيغة JSON غير صحيحة: $e'), backgroundColor: _kRose),
                );
              }
            },
            icon: const Icon(Icons.check, size: 18),
            label: const Text('استيراد وتطبيق'),
          ),
        ],
      ),
    );
  }

  // ── Widgets Builder Components ─────────────────────────────────────

  Widget _buildCardContainer({
    required String title,
    required String subtitle,
    required IconData icon,
    required Color accentColor,
    required List<Widget> children,
    Widget? trailingAction,
  }) {
    return Container(
      margin: const EdgeInsets.only(bottom: 20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: _kCardBorder),
        boxShadow: [
          BoxShadow(
            color: _kNavy.withValues(alpha: 0.03),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Card Header
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
            decoration: BoxDecoration(
              color: accentColor.withValues(alpha: 0.04),
              borderRadius: const BorderRadius.vertical(top: Radius.circular(18)),
              border: Border(bottom: BorderSide(color: _kCardBorder.withValues(alpha: 0.8))),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: accentColor.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(icon, color: accentColor, size: 20),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15, color: _kNavy),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        subtitle,
                        style: TextStyle(fontSize: 11, color: Colors.grey.shade600, fontWeight: FontWeight.w500),
                      ),
                    ],
                  ),
                ),
                ?trailingAction,
              ],
            ),
          ),
          // Card Body
          Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: children,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildField({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    String? hintText,
    String? suffixText,
    bool isNumber = false,
    int maxLines = 1,
    bool isPassword = false,
    String? helperText,
    Color? iconColor,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: _kNavy),
        ),
        const SizedBox(height: 6),
        TextField(
          controller: controller,
          keyboardType: isNumber ? const TextInputType.numberWithOptions(decimal: true) : TextInputType.text,
          maxLines: maxLines,
          obscureText: isPassword,
          onChanged: (_) => _markChanged(),
          decoration: InputDecoration(
            hintText: hintText,
            hintStyle: TextStyle(fontSize: 13, color: Colors.grey.shade400),
            prefixIcon: Icon(icon, color: iconColor ?? _kRoyalBlue, size: 18),
            suffixText: suffixText,
            suffixStyle: const TextStyle(fontWeight: FontWeight.bold, color: _kNavy),
            helperText: helperText,
            helperMaxLines: 2,
            helperStyle: TextStyle(fontSize: 11, color: Colors.grey.shade500),
            filled: true,
            fillColor: _kLightBg,
            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: _kCardBorder)),
            enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: _kCardBorder)),
            focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: _kRoyalBlue, width: 2)),
          ),
          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: _kNavy),
        ),
      ],
    );
  }

  Widget _buildSwitch({
    required String title,
    required String subtitle,
    required bool value,
    required ValueChanged<bool> onChanged,
    required IconData icon,
    Color activeColor = _kRoyalBlue,
    Widget? statusBadge,
  }) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: value ? activeColor.withValues(alpha: 0.04) : _kLightBg,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: value ? activeColor.withValues(alpha: 0.35) : _kCardBorder,
          width: 1.5,
        ),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: (value ? activeColor : Colors.grey).withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: value ? activeColor : Colors.grey.shade500, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Text(
                      title,
                      style: TextStyle(
                        fontWeight: FontWeight.w700,
                        fontSize: 13,
                        color: value ? _kNavy : Colors.grey.shade700,
                      ),
                    ),
                    if (statusBadge != null) ...[
                      const SizedBox(width: 8),
                      statusBadge,
                    ],
                  ],
                ),
                const SizedBox(height: 2),
                Text(
                  subtitle,
                  style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                ),
              ],
            ),
          ),
          Switch(
            value: value,
            activeThumbColor: activeColor,
            activeTrackColor: activeColor.withValues(alpha: 0.3),
            onChanged: (val) {
              _markChanged();
              onChanged(val);
            },
          ),
        ],
      ),
    );
  }

  // ── Tab 1: System & Identity ───────────────────────────────────────
  Widget _buildSystemTab(bool isRtl, bool isWide) {
    final col1 = [
      _buildCardContainer(
        title: isRtl ? 'الهوية الأساسية ومعلومات المنصة' : 'Brand Identity & Core Info',
        subtitle: isRtl ? 'الأسماء والشعارات الرسمية المعتمدة في التطبيق والويب' : 'Platform official names and branding texts',
        icon: Icons.storefront_rounded,
        accentColor: _kRoyalBlue,
        children: [
          Row(
            children: [
              Expanded(child: _buildField(controller: _nameArCtrl, label: 'اسم المنصة (بالعربية) *', icon: Icons.business, hintText: 'مثال: منصة مقاييس لمواد البناء')),
              const SizedBox(width: 14),
              Expanded(child: _buildField(controller: _nameEnCtrl, label: 'اسم المنصة (بالإنجليزية) *', icon: Icons.language, hintText: 'e.g. Magaiees Marketplace')),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(child: _buildField(controller: _sloganArCtrl, label: 'الشعار اللفظي (Slogan العربي)', icon: Icons.campaign_rounded, hintText: 'سوقك الهندسي المتكامل')),
              const SizedBox(width: 14),
              Expanded(child: _buildField(controller: _sloganEnCtrl, label: 'الشعار اللفظي (Slogan الإنجليزي)', icon: Icons.campaign_outlined, hintText: 'Your Integrated Engineering Marketplace')),
            ],
          ),
          const SizedBox(height: 14),
          _buildField(
            controller: _aboutCtrl,
            label: 'نبذة تعريفية شاملة عن منصة مقاييس',
            icon: Icons.info_outline_rounded,
            maxLines: 3,
            hintText: 'وصف يظهر للمستخدمين في صفحة (عن المنصة)...',
          ),
        ],
      ),
      _buildCardContainer(
        title: isRtl ? 'روابط متاجر التطبيقات وقنوات التواصل الاجتماعي' : 'App Stores & Social Links',
        subtitle: isRtl ? 'تظهر في تذييل الويب والروابط الخارجية' : 'Display links on footer and external share sheets',
        icon: Icons.share_rounded,
        accentColor: _kPurple,
        children: [
          Row(
            children: [
              Expanded(child: _buildField(controller: _playStoreCtrl, label: 'رابط تطبيق أندرويد (Google Play)', icon: Icons.android_rounded, iconColor: _kPurple)),
              const SizedBox(width: 14),
              Expanded(child: _buildField(controller: _appStoreCtrl, label: 'رابط تطبيق آبل (App Store)', icon: Icons.apple_rounded, iconColor: _kPurple)),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(child: _buildField(controller: _facebookCtrl, label: 'رابط صفحة فيسبوك', icon: Icons.facebook_rounded, iconColor: _kPurple)),
              const SizedBox(width: 14),
              Expanded(child: _buildField(controller: _twitterCtrl, label: 'رابط منصة إكس (Twitter)', icon: Icons.tag_rounded, iconColor: _kPurple)),
            ],
          ),
        ],
      ),
    ];

    final col2 = [
      _buildCardContainer(
        title: isRtl ? 'بيانات التواصل والدعم الفني المباشر' : 'Contact & Support Channels',
        subtitle: isRtl ? 'القنوات التي يتواصل عبرها العملاء والتجار مع الإدارة' : 'Customer and merchant support channels',
        icon: Icons.headset_mic_rounded,
        accentColor: _kEmerald,
        children: [
          Row(
            children: [
              Expanded(child: _buildField(controller: _phoneCtrl, label: 'رقم هاتف الدعم الفني', icon: Icons.phone_rounded, hintText: '+968 9905 1925', iconColor: _kEmerald)),
              const SizedBox(width: 14),
              Expanded(child: _buildField(controller: _whatsappCtrl, label: 'رقم واتساب خدمة العملاء', icon: Icons.chat_rounded, hintText: '+968 9905 1925', iconColor: _kEmerald)),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(child: _buildField(controller: _emailCtrl, label: 'البريد الإلكتروني الرسمي للدعم', icon: Icons.email_rounded, hintText: 'support@magaiees.com', iconColor: _kEmerald)),
              const SizedBox(width: 14),
              Expanded(child: _buildField(controller: _websiteCtrl, label: 'رابط الموقع الإلكتروني', icon: Icons.public_rounded, hintText: 'https://magaiees.com', iconColor: _kEmerald)),
            ],
          ),
        ],
      ),
      _buildCardContainer(
        title: isRtl ? 'تفضيلات وحالة النظام التشغيلية' : 'System Operational Preferences',
        subtitle: isRtl ? 'التحكم في وضع الصيانة وسمة المظهر' : 'Maintenance mode and theme preference',
        icon: Icons.tune_rounded,
        accentColor: _kRose,
        children: [
          _buildSwitch(
            title: isRtl ? 'وضع الصيانة المجدولة (Maintenance Mode)' : 'Maintenance Mode',
            subtitle: isRtl ? 'إيقاف وصول المشترين والتجار مؤقتاً أثناء التحديثات البرمجية الكبرى' : 'Temporarily disable public access during major system upgrades',
            value: _maintenanceMode,
            activeColor: _kRose,
            icon: Icons.warning_amber_rounded,
            statusBadge: _maintenanceMode
                ? Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(color: _kRose, borderRadius: BorderRadius.circular(10)),
                    child: const Text('نشط 🔴', style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold)),
                  )
                : null,
            onChanged: (val) => setState(() => _maintenanceMode = val),
          ),
          if (_maintenanceMode) ...[
            const SizedBox(height: 10),
            _buildField(
              controller: _maintenanceMsgCtrl,
              label: 'رسالة التنبيه التي ستظهر للمستخدمين أثناء الصيانة',
              icon: Icons.message_rounded,
              maxLines: 2,
              iconColor: _kRose,
            ),
            const SizedBox(height: 14),
          ],
          _buildSwitch(
            title: isRtl ? 'المظهر الليلي الداكن (Dark Mode)' : 'Dark Theme',
            subtitle: isRtl ? 'تفعيل الوضع الليلي لتجربة بصرية مريحة ومميزة في لوحة التحكم' : 'Enable dark theme for the admin portal',
            value: _darkModeEnabled,
            activeColor: const Color(0xFF6366F1),
            icon: Icons.dark_mode_rounded,
            onChanged: (val) {
              setState(() => _darkModeEnabled = val);
              ref.read(themeModeProvider.notifier).setThemeMode(val ? ThemeMode.dark : ThemeMode.light);
            },
          ),
          const SizedBox(height: 10),
          _buildSwitch(
            title: isRtl ? 'التحديثات اختيارية وغير إجبارية (Optional Updates Mode) 🟢' : 'Optional Updates Mode',
            subtitle: isRtl ? 'تثبيت التحديثات والتحسينات كإجراء اختياري سلس دون إجبار المستخدمين على التحديث أو مقاطعة عملهم' : 'Allow users to update optionally without forced interruptions',
            value: _optionalUpdatesOnly,
            activeColor: _kEmerald,
            icon: Icons.system_update_rounded,
            statusBadge: Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
              decoration: BoxDecoration(color: _kEmerald.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(10)),
              child: const Text('اختياري دائم ✅', style: TextStyle(color: _kEmerald, fontSize: 10, fontWeight: FontWeight.bold)),
            ),
            onChanged: (val) => setState(() => _optionalUpdatesOnly = val),
          ),
        ],
      ),
    ];

    if (!isWide) {
      return Column(children: [...col1, ...col2]);
    }

    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(child: Column(children: col1)),
        const SizedBox(width: 24),
        Expanded(child: Column(children: col2)),
      ],
    );
  }

  // ── Tab 2: Finance, Payments & Logistics ───────────────────────────
  Widget _buildFinanceTab(bool isRtl, bool isWide) {
    // 1. بطاقة إعدادات دفع منتجات مقاييس (حصري على مقاييس)
    final exclusivePaymentsCard = _buildCardContainer(
      title: isRtl ? 'إعدادات دفع المشتري (المنتجات الحصرية على مقاييس)' : 'Exclusive Magaiees Payment Settings',
      subtitle: isRtl
          ? 'طرق وخيارات الدفع وبيانات حسابات شركة مقاييس الرسمية للمنتجات الحصرية على المنصة'
          : 'Official Magaiees bank accounts and checkout payment rules for platform-exclusive products',
      icon: Icons.verified_rounded,
      accentColor: _kRoyalBlue,
      children: [
        // 1. Bankak
        _buildSwitch(
          title: isRtl ? 'تطبيق بنكك (Bank of Khartoum)' : 'Bankak Direct Transfer',
          subtitle: isRtl ? 'السماح برفع إشعار التحويل البنكي لحساب بنكك الخاص بشركة مقاييس' : 'Allow uploading Bankak receipt to Magaiees official account',
          value: _exclusiveBankak,
          activeColor: _kRoyalBlue,
          icon: Icons.account_balance_wallet_rounded,
          onChanged: (val) => setState(() => _exclusiveBankak = val),
        ),
        if (_exclusiveBankak) ...[
          Container(
            margin: const EdgeInsets.only(bottom: 14),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: const Color(0xFFEFF6FF),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFBFDBFE)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Row(
                  children: [
                    Icon(Icons.info_rounded, color: _kRoyalBlue, size: 16),
                    SizedBox(width: 8),
                    Text('بيانات حساب بنكك المعتمد لمنصة مقاييس:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: _kRoyalBlue)),
                  ],
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Expanded(child: _buildField(controller: _exclusiveBankakAccountCtrl, label: 'رقم الحساب (Account No) *', icon: Icons.numbers_rounded, hintText: '1984256')),
                    const SizedBox(width: 10),
                    Expanded(child: _buildField(controller: _exclusiveBankakNameCtrl, label: 'اسم الحساب الرسمي *', icon: Icons.person_rounded, hintText: 'شركة مقاييس للتوريدات')),
                  ],
                ),
                const SizedBox(height: 10),
                _buildField(controller: _exclusiveBankakIbanCtrl, label: 'رقم الآيبان الدولي (IBAN)', icon: Icons.qr_code_rounded, hintText: 'SD84BOKH0000000001984256'),
              ],
            ),
          ),
        ],

        // 2. O-Cash
        _buildSwitch(
          title: isRtl ? 'تطبيق أوكاش (O-Cash)' : 'O-Cash Wallet',
          subtitle: isRtl ? 'السماح بالتحويل لمحفظة أوكاش الرسمية الخاصة بشركة مقاييس' : 'Allow payment via Magaiees official O-Cash wallet',
          value: _exclusiveOcash,
          activeColor: _kPurple,
          icon: Icons.payments_rounded,
          onChanged: (val) => setState(() => _exclusiveOcash = val),
        ),
        if (_exclusiveOcash) ...[
          Container(
            margin: const EdgeInsets.only(bottom: 14),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: const Color(0xFFFAF5FF),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFE9D5FF)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Row(
                  children: [
                    Icon(Icons.info_rounded, color: _kPurple, size: 16),
                    SizedBox(width: 8),
                    Text('بيانات محفظة أوكاش المعتمدة لمنصة مقاييس:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: _kPurple)),
                  ],
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Expanded(child: _buildField(controller: _exclusiveOcashAccountCtrl, label: 'رقم المحفظة / الهاتف *', icon: Icons.phone_android_rounded, hintText: '0912300105', iconColor: _kPurple)),
                    const SizedBox(width: 10),
                    Expanded(child: _buildField(controller: _exclusiveOcashNameCtrl, label: 'اسم صاحب المحفظة *', icon: Icons.person_rounded, hintText: 'محفظة مقاييس أوكاش', iconColor: _kPurple)),
                  ],
                ),
              ],
            ),
          ),
        ],

        // 3. Cash on Delivery (COD)
        _buildSwitch(
          title: isRtl ? 'الدفع نقداً عند الاستلام (Cash on Delivery)' : 'Cash on Delivery (COD)',
          subtitle: isRtl ? 'السماح بدفع قيمة منتجات مقاييس كاش عند وصول الشحنة للموقع' : 'Allow cash payment upon material delivery to client location',
          value: _exclusiveCod,
          activeColor: _kEmerald,
          icon: Icons.local_shipping_rounded,
          onChanged: (val) => setState(() => _exclusiveCod = val),
        ),
        if (_exclusiveCod) ...[
          Padding(
            padding: const EdgeInsets.only(bottom: 14),
            child: _buildField(
              controller: _exclusiveMaxCodCtrl,
              label: 'الحد الأقصى المسموح به للدفع نقداً عند الاستلام (SDG)',
              icon: Icons.attach_money_rounded,
              suffixText: 'SDG',
              isNumber: true,
              helperText: 'أي طلب لمنتجات مقاييس يتجاوز هذا المبلغ يتطلب تحويلاً بنكياً مسبقاً',
              iconColor: _kEmerald,
            ),
          ),
        ],

        // 4. Direct Bank Wire
        _buildSwitch(
          title: isRtl ? 'تحويل بنكي مباشر (Direct Bank Wire)' : 'Direct Bank Wire',
          subtitle: isRtl ? 'التحويل المباشر لحساب شركة مقاييس البنكي الرسمي' : 'Direct wire transfer to Magaiees corporate bank account',
          value: _exclusiveWire,
          activeColor: _kNavy,
          icon: Icons.account_balance_rounded,
          onChanged: (val) => setState(() => _exclusiveWire = val),
        ),
        if (_exclusiveWire) ...[
          Container(
            margin: const EdgeInsets.only(bottom: 14),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFCBD5E1)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Row(
                  children: [
                    Icon(Icons.info_rounded, color: _kNavy, size: 16),
                    SizedBox(width: 8),
                    Text('بيانات الحساب البنكي الرسمي لشركة مقاييس:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: _kNavy)),
                  ],
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Expanded(child: _buildField(controller: _exclusiveWireBankCtrl, label: 'اسم البنك *', icon: Icons.account_balance_rounded, hintText: 'بنك فيصل الإسلامي', iconColor: _kNavy)),
                    const SizedBox(width: 10),
                    Expanded(child: _buildField(controller: _exclusiveWireAccountCtrl, label: 'رقم الحساب *', icon: Icons.numbers_rounded, hintText: '12345678', iconColor: _kNavy)),
                  ],
                ),
                const SizedBox(height: 10),
                _buildField(controller: _exclusiveWireNameCtrl, label: 'اسم صاحب الحساب *', icon: Icons.person_rounded, hintText: 'شركة مقاييس للتوريدات الهندسية', iconColor: _kNavy),
              ],
            ),
          ),
        ],

        // 5. Electronic / Card Payments
        _buildSwitch(
          title: isRtl ? 'الدفع الإلكتروني عبر بطاقات فوري / فيزا' : 'Fawry / Card Payments',
          subtitle: isRtl ? 'بوابة الدفع الإلكتروني المباشر بالبطاقات المصرفية لحساب المنصة' : 'Direct electronic bank card gateway',
          value: _exclusiveFawry,
          activeColor: _kPurple,
          icon: Icons.contactless_rounded,
          onChanged: (val) => setState(() => _exclusiveFawry = val),
        ),
        if (_exclusiveFawry) ...[
          Padding(
            padding: const EdgeInsets.only(bottom: 14),
            child: _buildField(
              controller: _exclusiveMaxFawryCtrl,
              label: 'الحد المسموح به للدفع الإلكتروني عبر تطبيق فوري / فيزا (SDG)',
              icon: Icons.credit_card_rounded,
              suffixText: 'SDG',
              isNumber: true,
              helperText: 'الحد الأقصى للعملية الواحدة عبر بطاقات الدفع الإلكتروني',
              iconColor: _kPurple,
            ),
          ),
        ],

        // 6. B2B Corporate Credit
        _buildSwitch(
          title: isRtl ? 'الدفع الآجل للشركات (B2B Corporate Credit)' : 'B2B Corporate Credit',
          subtitle: isRtl ? 'إتاحة خيار السداد الآجل للمؤسسات والشركات المعتمدة على الفواتير الآجلة' : 'Allow deferred credit payment for verified corporate buyers',
          value: _exclusiveB2b,
          activeColor: _kRoyalBlue,
          icon: Icons.business_rounded,
          onChanged: (val) => setState(() => _exclusiveB2b = val),
        ),

        // 7. Partial Payment + COD (دفع جزء نقداً والباقي عند الاستلام)
        _buildSwitch(
          title: isRtl ? 'السماح بدفع جزء من المبلغ نقداً والباقي عند الاستلام' : 'Allow Partial Payment + Cash on Delivery',
          subtitle: isRtl
              ? 'تمكين المشتري من دفع دفعة مقدمة (عربون) وسداد المبلغ المتبقي نقداً عند استلام الشحنة'
              : 'Allow buyers to pay an upfront deposit and settle remaining balance cash upon delivery',
          value: _exclusivePartialCod,
          activeColor: _kEmerald,
          icon: Icons.pie_chart_outline_rounded,
          onChanged: (val) => setState(() => _exclusivePartialCod = val),
        ),
        if (_exclusivePartialCod) ...[
          Padding(
            padding: const EdgeInsets.only(bottom: 14),
            child: _buildField(
              controller: _exclusivePartialMinPercentCtrl,
              label: 'الحد الأدنى لنسبة الدفعة المقدمة (%)',
              icon: Icons.percent_rounded,
              suffixText: '%',
              isNumber: true,
              helperText: 'النسبة المئوية المقترحة كعربون مبدئي يسدده المشتري مقدماً والباقي نقداً عند الاستلام',
              iconColor: _kEmerald,
            ),
          ),
        ],
      ],
    );

    // 2. بطاقة إعدادات دفع تجار السوق والموردين المعتمدين
    final merchantPaymentsCard = _buildCardContainer(
      title: isRtl ? 'إعدادات دفع المشتري (تجار السوق والموردين)' : 'Marketplace Merchants Payment Settings',
      subtitle: isRtl
          ? 'تحديد طرق وخيارات الدفع المصرح لتجار السوق قبولها (يقوم كل تاجر بإدخال تفاصيل حسابه في إعدادات متجره)'
          : 'Define allowed payment methods for marketplace merchants (merchants enter their bank info in store settings)',
      icon: Icons.storefront_rounded,
      accentColor: _kPurple,
      children: [
        Container(
          margin: const EdgeInsets.only(bottom: 16),
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: const Color(0xFFF3E8FF),
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: const Color(0xFFD8B4FE)),
          ),
          child: Row(
            children: [
              const Icon(Icons.tips_and_updates_rounded, color: _kPurple, size: 18),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  isRtl
                      ? 'ملاحظة: تفعيل أي خيار هنا يتيحه لتجار السوق ليختاروه لأصنافهم في تطبيق التاجر، ويقوم التاجر بإدخال تفاصيل حسابه (اسمه ورقمه) في إعدادات متجره لتظهر للمشتري تلقائياً.'
                      : 'Note: Enabling an option here allows merchants to accept it for their items. Each merchant enters their account details in Store Settings, which are then displayed to buyers at checkout.',
                  style: const TextStyle(fontSize: 11.5, color: Color(0xFF581C87), fontWeight: FontWeight.w600, height: 1.4),
                ),
              ),
            ],
          ),
        ),

        // 1. Merchant Allow Bankak
        _buildSwitch(
          title: isRtl ? 'السماح لتجار السوق بالدفع عبر تطبيق بنكك (Bankak)' : 'Allow Bankak for Merchants',
          subtitle: isRtl ? 'تمكين التاجر من استقبال أموال مبيعاته على حساب بنكك الخاص به وعرضه للمشتري' : 'Allow merchants to receive payments directly to their Bankak account',
          value: _merchantAllowBankak,
          activeColor: _kPurple,
          icon: Icons.account_balance_wallet_rounded,
          onChanged: (val) => setState(() => _merchantAllowBankak = val),
        ),

        // 2. Merchant Allow O-Cash
        _buildSwitch(
          title: isRtl ? 'السماح لتجار السوق بالدفع عبر تطبيق أوكاش (O-Cash)' : 'Allow O-Cash for Merchants',
          subtitle: isRtl ? 'تمكين التاجر من استقبال الأموال على محفظة أوكاش الخاصة به' : 'Allow merchants to receive payments to their O-Cash wallet',
          value: _merchantAllowOcash,
          activeColor: _kPurple,
          icon: Icons.payments_rounded,
          onChanged: (val) => setState(() => _merchantAllowOcash = val),
        ),

        // 3. Merchant Allow Cash on Delivery
        _buildSwitch(
          title: isRtl ? 'السماح لتجار السوق بالدفع نقداً عند الاستلام (COD)' : 'Allow Cash on Delivery for Merchants',
          subtitle: isRtl ? 'السماح للمشتري بالدفع كاش لتاجر السوق عند تسليم المواد' : 'Allow buyers to pay cash upon delivery for merchant items',
          value: _merchantAllowCod,
          activeColor: _kEmerald,
          icon: Icons.local_shipping_rounded,
          onChanged: (val) => setState(() => _merchantAllowCod = val),
        ),
        if (_merchantAllowCod) ...[
          Padding(
            padding: const EdgeInsets.only(bottom: 14),
            child: _buildField(
              controller: _merchantMaxCodCtrl,
              label: 'الحد الأقصى المسموح به للدفع كاش عند الاستلام لتجار السوق (SDG)',
              icon: Icons.attach_money_rounded,
              suffixText: 'SDG',
              isNumber: true,
              helperText: 'أي طلب من تجار السوق يتجاوز هذا المبلغ يلزم المشتري بالتحويل البنكي',
              iconColor: _kEmerald,
            ),
          ),
        ],

        // 4. Merchant Allow Direct Bank Wire
        _buildSwitch(
          title: isRtl ? 'السماح بالتحويل البنكي المباشر لحساب التاجر' : 'Allow Direct Wire Transfer to Merchant',
          subtitle: isRtl ? 'إظهار بيانات البنك واسم ورقم حساب التاجر للمشتري للتحويل المباشر' : 'Display merchant registered bank name and account to buyer for direct transfer',
          value: _merchantAllowWire,
          activeColor: _kAmber,
          icon: Icons.account_balance_rounded,
          onChanged: (val) => setState(() => _merchantAllowWire = val),
        ),

        // 5. Merchant Allow B2B Credit
        _buildSwitch(
          title: isRtl ? 'السماح لتجار السوق بالدفع الآجل للشركات (B2B Credit)' : 'Allow B2B Credit for Merchants',
          subtitle: isRtl ? 'تمكين التاجر من بيع أصنافه بالآجل للمؤسسات والشركات المعتمدة' : 'Allow merchants to offer deferred B2B credit to corporate clients',
          value: _merchantAllowB2b,
          activeColor: _kRoyalBlue,
          icon: Icons.business_rounded,
          onChanged: (val) => setState(() => _merchantAllowB2b = val),
        ),

        // 6. Merchant Allow Partial Payment + COD (دفع جزء نقداً والباقي عند الاستلام)
        _buildSwitch(
          title: isRtl ? 'السماح لتجار السوق بدفع جزء من المبلغ نقداً والباقي عند الاستلام' : 'Allow Partial Cash Payment + COD for Merchants',
          subtitle: isRtl
              ? 'تمكين تجار وموردي السوق من قبول دفعة مقدمة وسداد المبلغ المتبقي نقداً عند الاستلام'
              : 'Allow marketplace merchants to accept upfront deposit and remaining balance cash upon delivery',
          value: _merchantAllowPartialCod,
          activeColor: _kPurple,
          icon: Icons.pie_chart_outline_rounded,
          onChanged: (val) => setState(() => _merchantAllowPartialCod = val),
        ),
      ],
    );

    // 3. بطاقة النسب المالية والضرائب والعملات
    final financeCard = _buildCardContainer(
      title: isRtl ? 'النسب المالية والضرائب والعملات' : 'Commissions, VAT & Currency',
      subtitle: isRtl ? 'النسب الافتراضية المطبقة على الصفقات والمبيعات وضريبة القيمة المضافة' : 'Default platform transaction commission and tax rules',
      icon: Icons.account_balance_rounded,
      accentColor: _kEmerald,
      children: [
        SwitchListTile(
          title: Text(isRtl ? 'تفعيل الضريبة والعمولات (يظهر للمشتري)' : 'Enable Tax & Commissions (Show to Buyer)', style: const TextStyle(fontWeight: FontWeight.bold)),
          subtitle: Text(isRtl ? 'إذا تم الإيقاف، لن تظهر الضريبة أو العمولات في فواتير المشتري' : 'If disabled, VAT and commissions will not be shown on buyer invoices'),
          value: _isTaxAndCommissionEnabled,
          activeColor: _kEmerald,
          onChanged: (val) => setState(() => _isTaxAndCommissionEnabled = val),
        ),
        const SizedBox(height: 14),
        Row(
          children: [
            Expanded(
              child: _buildField(
                controller: _commissionCtrl,
                label: 'نسبة عمولة المنصة (%) *',
                icon: Icons.percent_rounded,
                suffixText: '%',
                isNumber: true,
                helperText: 'النسبة المقتطعة من إجمالي طلبات التجار',
                iconColor: _kEmerald,
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: _buildField(
                controller: _vatCtrl,
                label: 'ضريبة القيمة المضافة (VAT) *',
                icon: Icons.receipt_long_rounded,
                suffixText: '%',
                isNumber: true,
                helperText: 'الضريبة الرسمية المعتمدة في الفواتير',
                iconColor: _kEmerald,
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: _buildField(
                controller: _currencyCtrl,
                label: 'العملة الأساسية للنظام',
                icon: Icons.payments_rounded,
                hintText: 'ج.س (SDG)',
                helperText: 'العملة المعروضة للفواتير والمدفوعات',
                iconColor: _kEmerald,
              ),
            ),
          ],
        ),
        const SizedBox(height: 14),
        Row(
          children: [
            Expanded(
              child: _buildField(
                controller: _platformUsdRateCtrl,
                label: 'سعر صرف الدولار المعتمد للمنصة (ج.س) *',
                icon: Icons.currency_exchange_rounded,
                suffixText: 'SDG',
                isNumber: true,
                helperText: 'السعر المرجعي الموحد الذي تعتمد عليه المتاجر في تسعير المنتجات',
                iconColor: _kEmerald,
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: _buildField(
                controller: _taxNumberCtrl,
                label: 'الرقم التعريفي الضريبي للشركة (Tax Registration No)',
                icon: Icons.badge_rounded,
                hintText: 'SD-VAT-9021482',
                helperText: 'يظهر في ترويسة الفواتير الضريبية المطبوعة وسندات القبض',
                iconColor: _kEmerald,
              ),
            ),
          ],
        ),
      ],
    );

    // 4. بطاقة إعدادات الشحن وتكاليف التوصيل
    final shippingCard = _buildCardContainer(
      title: isRtl ? 'إعدادات الشحن وتكاليف التوصيل الافتراضية' : 'Shipping & Logistics Defaults',
      subtitle: isRtl ? 'حساب تكلفة ترحيل مواد البناء للمواقع والمشاريع' : 'Transportation and delivery cost calculation rules',
      icon: Icons.fire_truck_rounded,
      accentColor: _kAmber,
      children: [
        Row(
          children: [
            Expanded(
              child: _buildField(
                controller: _baseDeliveryFeeCtrl,
                label: 'رسوم فتح العداد / الشحن الأساسي',
                icon: Icons.money_rounded,
                suffixText: 'SDG',
                isNumber: true,
                iconColor: _kAmber,
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: _buildField(
                controller: _deliveryPerKmCtrl,
                label: 'تكلفة الكيلومتر الإضافي',
                icon: Icons.add_road_rounded,
                suffixText: 'SDG/KM',
                isNumber: true,
                iconColor: _kAmber,
              ),
            ),
          ],
        ),
        const SizedBox(height: 14),
        _buildField(
          controller: _freeDeliveryThresholdCtrl,
          label: 'الحد الأدنى للطلبيات المؤهلة للشحن المجاني (SDG)',
          icon: Icons.card_giftcard_rounded,
          suffixText: 'SDG',
          isNumber: true,
          helperText: 'الطلبيات التي تتجاوز هذا المبلغ تحصل على توصيل مجاني تلقائياً',
          iconColor: _kAmber,
        ),
      ],
    );

    final col1 = [exclusivePaymentsCard, merchantPaymentsCard];
    final col2 = [financeCard, shippingCard];

    if (!isWide) {
      return Column(children: [exclusivePaymentsCard, merchantPaymentsCard, financeCard, shippingCard]);
    }

    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(child: Column(children: col1)),
        const SizedBox(width: 24),
        Expanded(child: Column(children: col2)),
      ],
    );
  }

  // ── Tab 3: Account Upgrades & Rules ────────────────────────────────
  Widget _buildAccountUpgradesTab(bool isRtl, bool isWide) {
    final col1 = [
      _buildCardContainer(
        title: isRtl ? 'التحكم في طلبات ترقية حسابات المشترين إلى تجار' : 'Merchant Account Upgrade Controls',
        subtitle: isRtl ? 'قفل أو إتاحة استقبال تجار وموردين جدد في المنظومة' : 'Open or close new merchant onboarding on the platform',
        icon: Icons.storefront_rounded,
        accentColor: _kRoyalBlue,
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: _enableMerchantUpgrade ? const Color(0xFFEFF6FF) : const Color(0xFFFEF2F2),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: _enableMerchantUpgrade ? const Color(0xFF93C5FD) : const Color(0xFFFECACA)),
            ),
            child: Row(
              children: [
                Icon(
                  _enableMerchantUpgrade ? Icons.check_circle_rounded : Icons.lock_outline_rounded,
                  color: _enableMerchantUpgrade ? _kRoyalBlue : _kRose,
                  size: 28,
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _enableMerchantUpgrade
                            ? (isRtl ? 'استقبال طلبات الترقية كتاجر: مفعّل ومتاح حالياً' : 'Merchant Upgrades: Open & Active')
                            : (isRtl ? 'استقبال طلبات الترقية كتاجر: مقفل ومخفي حالياً' : 'Merchant Upgrades: Closed & Hidden'),
                        style: TextStyle(
                          fontWeight: FontWeight.w800,
                          fontSize: 14,
                          color: _enableMerchantUpgrade ? _kRoyalBlue : _kRose,
                        ),
                      ),
                      const SizedBox(height: 3),
                      Text(
                        _enableMerchantUpgrade
                            ? (isRtl ? 'يظهر زر "ترقية حسابك إلى تاجر" في الملف الشخصي لجميع المشترين للتقديم.' : 'The upgrade button is visible in buyer profiles.')
                            : (isRtl ? 'زر وبطاقات الترقية مخفية تماماً من تطبيق المشتري (نظراً لاكتفاء المنصة من التجار).' : 'The upgrade button is hidden from buyer app since merchant quota is fulfilled.'),
                        style: TextStyle(fontSize: 12, color: Colors.grey.shade700),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          _buildSwitch(
            title: isRtl ? 'الترقية كتاجر (إظهار طلب الترقية في تطبيق المشتري)' : 'Show Merchant Upgrade in Buyer App',
            subtitle: isRtl ? 'المفتاح الرئيسي: إتاحة أو إخفاء خيار طلب الترقية لحساب تاجر من تطبيق المشتري لحظياً' : 'Master toggle to display or hide the merchant upgrade action across all buyer devices',
            value: _enableMerchantUpgrade,
            activeColor: _kRoyalBlue,
            icon: Icons.business_center_rounded,
            onChanged: (val) {
              setState(() => _enableMerchantUpgrade = val);
              ref.read(merchantUpgradeSettingProvider.notifier).toggleSetting(val);
            },
          ),
          const SizedBox(height: 10),
          _buildSwitch(
            title: isRtl ? 'الموافقة التلقائية عند اكتمال الوثائق' : 'Auto-Approve Complete Applications',
            subtitle: isRtl ? 'ترقية حساب التاجر فورياً عند رفع السجل التجاري والشهادة الضريبية' : 'Instantly promote accounts upon uploading valid documents',
            value: _autoApproveVerifiedDocuments,
            activeColor: _kEmerald,
            icon: Icons.verified_user_rounded,
            onChanged: (val) => setState(() => _autoApproveVerifiedDocuments = val),
          ),
        ],
      ),
    ];

    final col2 = [
      _buildCardContainer(
        title: isRtl ? 'اشتراطات وقواعد قبول وثائق التجار' : 'Merchant Document Verification Rules',
        subtitle: isRtl ? 'تحديد المستندات الإلزامية التي يطلبها النظام من الموردين' : 'Specify required legal documents for commercial account approval',
        icon: Icons.verified_user_rounded,
        accentColor: _kPurple,
        children: [
          _buildSwitch(
            title: isRtl ? 'إلزامية إرفاق السجل التجاري (Commercial Register)' : 'Require Commercial Register (CR)',
            subtitle: isRtl ? 'لا يمكن إرسال طلب الترقية بدون صورة واضحة من السجل التجاري' : 'Block submission if CR image is missing',
            value: _requireCommercialRegister,
            activeColor: _kPurple,
            icon: Icons.description_rounded,
            onChanged: (val) => setState(() => _requireCommercialRegister = val),
          ),
          _buildSwitch(
            title: isRtl ? 'إلزامية إرفاق الشهادة الضريبية / الرخصة' : 'Require Tax / Trade License',
            subtitle: isRtl ? 'اشتراط رخصة النشاط التجاري للترقية' : 'Require tax card or trade license',
            value: _requireTaxCertificate,
            activeColor: _kAmber,
            icon: Icons.card_membership_rounded,
            onChanged: (val) => setState(() => _requireTaxCertificate = val),
          ),
          const SizedBox(height: 10),
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: _kPurple.withValues(alpha: 0.05),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: _kPurple.withValues(alpha: 0.15)),
            ),
            child: const Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(Icons.shield_rounded, color: _kPurple, size: 18),
                    SizedBox(width: 8),
                    Text('ملاحظة أمنية وقانونية:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _kPurple)),
                  ],
                ),
                SizedBox(height: 6),
                Text(
                  'عند مراجعة طلبات الترقية من شاشة (المشتركون)، يقوم المشرف بفحص الوثائق والتأكد من مطابقة اسم المتجر والسجل التجاري قبل منح رتبة "تاجر معتمد".',
                  style: TextStyle(fontSize: 12, color: _kNavy, height: 1.4),
                ),
              ],
            ),
          ),
        ],
      ),
    ];

    if (!isWide) {
      return Column(children: [...col1, ...col2]);
    }

    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(child: Column(children: col1)),
        const SizedBox(width: 24),
        Expanded(child: Column(children: col2)),
      ],
    );
  }

  // ── Tab 4: Gamification & Loyalty ──────────────────────────────────
  Widget _buildGamificationTab(bool isRtl, bool isWide) {
    final col1 = [
      _buildCardContainer(
        title: isRtl ? 'نظام التحفيز وبرامج الولاء والنقاط' : 'Gamification & Customer Rewards',
        subtitle: isRtl ? 'مكافأة المشترين والتجار بالنقاط واستبدالها برصيد وخصومات' : 'Reward loyal buyers and high-volume merchants with points and tiers',
        icon: Icons.military_tech_rounded,
        accentColor: _kAmber,
        children: [
          _buildSwitch(
            title: isRtl ? 'تفعيل نظام نقاط الولاء والمكافآت (Gamification Engine)' : 'Enable Gamification Engine',
            subtitle: isRtl ? 'السماح للمستخدمين بتجميع النقاط وتحويلها لرصيد في المحفظة' : 'Allow users to earn reward points redeemable for discounts',
            value: _enableGamification,
            activeColor: _kAmber,
            icon: Icons.stars_rounded,
            onChanged: (val) => setState(() => _enableGamification = val),
          ),
          if (_enableGamification) ...[
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: _buildField(
                    controller: _pointsPerOrderCtrl,
                    label: 'النقاط المكتسبة لكل طلبية ناجحة',
                    icon: Icons.shopping_bag_rounded,
                    suffixText: 'نقطة',
                    isNumber: true,
                    helperText: 'تضاف للمشتري عند الاستلام',
                    iconColor: _kAmber,
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: _buildField(
                    controller: _pointsPerReviewCtrl,
                    label: 'النقاط لكل تقييم معتمد',
                    icon: Icons.rate_review_rounded,
                    suffixText: 'نقطة',
                    isNumber: true,
                    helperText: 'تحفيز العملاء على التقييم',
                    iconColor: _kAmber,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),
            _buildField(
              controller: _pointsToSdgRatioCtrl,
              label: 'معادل النقاط بالجنيه (SDG Exchange Rate)',
              icon: Icons.currency_exchange_rounded,
              suffixText: 'نقطة = 1 ج.س',
              isNumber: true,
              helperText: 'مثال: 100 نقطة = 1 جنيه سوداني رصيد خصم عند الشراء',
              iconColor: _kAmber,
            ),
          ],
        ],
      ),
    ];

    final col2 = [
      _buildCardContainer(
        title: isRtl ? 'شروط ومستويات رتب التجار (Merchant Tiers)' : 'Merchant Loyalty Tiers',
        subtitle: isRtl ? 'نقاط الحجم التراكمية المطلوبة للترقية التلقائية للرتب' : 'Cumulative points required for merchant tier promotions',
        icon: Icons.workspace_premium_rounded,
        accentColor: _kRoyalBlue,
        children: [
          _buildField(
            controller: _bronzeTierPointsCtrl,
            label: 'الرتبة البرونزية 🥉 (Bronze Tier Threshold)',
            icon: Icons.workspace_premium,
            suffixText: 'نقطة',
            isNumber: true,
            helperText: 'المستوى الأولي للتجار الناشئين',
            iconColor: const Color(0xFFCD7F32),
          ),
          const SizedBox(height: 14),
          _buildField(
            controller: _silverTierPointsCtrl,
            label: 'الرتبة الفضية 🥈 (Silver Tier Threshold)',
            icon: Icons.workspace_premium,
            suffixText: 'نقطة',
            isNumber: true,
            helperText: 'أولوية في الظهور ونسبة عمولة مخفضة',
            iconColor: const Color(0xFF71717A),
          ),
          const SizedBox(height: 14),
          _buildField(
            controller: _goldTierPointsCtrl,
            label: 'الرتبة الذهبية 🥇 (Gold Tier Threshold)',
            icon: Icons.workspace_premium,
            suffixText: 'نقطة',
            isNumber: true,
            helperText: 'شارة المورد المعتمد VIP وظهور بالصفحة الأولى',
            iconColor: const Color(0xFFD97706),
          ),
        ],
      ),
    ];

    if (!isWide) {
      return Column(children: [...col1, ...col2]);
    }

    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(child: Column(children: col1)),
        const SizedBox(width: 24),
        Expanded(child: Column(children: col2)),
      ],
    );
  }

  // ── Tab 5: Security, System Health & Backup ────────────────────────
  Widget _buildSecurityBackupTab(bool isRtl, bool isWide) {
    final col1 = [
      // Admin Account & Security
      _buildCardContainer(
        title: isRtl ? 'حساب الإدارة الرئيسية وأمان الجلسات' : 'Admin Security & Session Rules',
        subtitle: isRtl ? 'إدارة كلمة المرور والمصادقة الثنائية وفترات الخمول' : 'Password management, 2FA, and inactivity timeouts',
        icon: Icons.shield_rounded,
        accentColor: _kPurple,
        children: [
          Row(
            children: [
              Expanded(child: _buildField(controller: _adminNameCtrl, label: 'اسم مسؤول النظام', icon: Icons.person_rounded, iconColor: _kPurple)),
              const SizedBox(width: 14),
              Expanded(child: _buildField(controller: _adminEmailCtrl, label: 'البريد الإلكتروني للإدارة', icon: Icons.alternate_email_rounded, iconColor: _kPurple)),
            ],
          ),
          const SizedBox(height: 14),
          _buildField(controller: _sessionTimeoutCtrl, label: 'مهلة انتهاء الجلسة التلقائي (بالدقائق)', icon: Icons.timer_rounded, suffixText: 'دقيقة', isNumber: true, iconColor: _kPurple),
          const SizedBox(height: 16),
          _buildSwitch(
            title: isRtl ? 'المصادقة الثنائية (2-Factor Authentication)' : 'Two-Factor Authentication (2FA)',
            subtitle: isRtl ? 'طلب رمز تأكيد إضافي (OTP) عند تسجيل الدخول من جهاز أو متصفح جديد' : 'Require OTP code verification when signing in from unrecognized devices',
            value: _twoFactorAuth,
            activeColor: _kPurple,
            icon: Icons.security_rounded,
            onChanged: (val) => setState(() => _twoFactorAuth = val),
          ),
          const SizedBox(height: 16),
          const Text('تغيير كلمة مرور حساب الإدارة:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _kNavy)),
          const SizedBox(height: 10),
          _buildField(controller: _currentPassCtrl, label: 'كلمة المرور الحالية', icon: Icons.lock_outline_rounded, isPassword: true),
          const SizedBox(height: 10),
          Row(
            children: [
              Expanded(child: _buildField(controller: _newPassCtrl, label: 'كلمة المرور الجديدة', icon: Icons.lock_rounded, isPassword: true)),
              const SizedBox(width: 14),
              Expanded(child: _buildField(controller: _confirmPassCtrl, label: 'تأكيد كلمة المرور الجديدة', icon: Icons.lock_rounded, isPassword: true)),
            ],
          ),
        ],
      ),
    ];

    final col2 = [
      // Live Health Diagnostic Widget
      _buildCardContainer(
        title: isRtl ? 'لوحة فحص صحة واتصال المنظومة' : 'Live System Health Diagnostics',
        subtitle: isRtl ? 'مراقبة حية لاتصال خوادم قواعد البيانات، التخزين السحابي، وسرعة الاستجابة' : 'Real-time monitoring of database, storage buckets, and realtime websockets',
        icon: Icons.monitor_heart_rounded,
        accentColor: _kEmerald,
        trailingAction: FilledButton.tonalIcon(
          style: FilledButton.styleFrom(backgroundColor: const Color(0xFFEFF6FF), foregroundColor: _kRoyalBlue),
          onPressed: _isCheckingHealth ? null : _runHealthCheck,
          icon: _isCheckingHealth
              ? const SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2))
              : const Icon(Icons.refresh_rounded, size: 16),
          label: Text(isRtl ? 'فحص لحظي' : 'Ping Test', style: const TextStyle(fontSize: 12)),
        ),
        children: [
          Container(
            padding: const EdgeInsets.all(14),
            margin: const EdgeInsets.only(bottom: 10),
            decoration: BoxDecoration(
              color: _dbOnline ? const Color(0xFFECFDF5) : const Color(0xFFFEF2F2),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: _dbOnline ? const Color(0xFFA7F3D0) : const Color(0xFFFECACA)),
            ),
            child: Row(
              children: [
                Icon(_dbOnline ? Icons.storage_rounded : Icons.cloud_off_rounded, color: _dbOnline ? _kEmerald : _kRose, size: 24),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('قاعدة بيانات Supabase (PostgreSQL)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _dbOnline ? const Color(0xFF065F46) : _kRose)),
                      const SizedBox(height: 2),
                      Text('الحالة: ${_dbOnline ? "متصل ومستقر 🟢" : "غير متاح 🔴"} (${_dbLatencyMs}ms)', style: TextStyle(fontSize: 11, color: Colors.grey.shade700)),
                    ],
                  ),
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.all(14),
            margin: const EdgeInsets.only(bottom: 10),
            decoration: BoxDecoration(
              color: _storageOnline ? const Color(0xFFECFDF5) : const Color(0xFFFEF2F2),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: _storageOnline ? const Color(0xFFA7F3D0) : const Color(0xFFFECACA)),
            ),
            child: Row(
              children: [
                Icon(Icons.cloud_done_rounded, color: _storageOnline ? _kEmerald : _kRose, size: 24),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('مستودعات التخزين السحابي (Storage Buckets)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _storageOnline ? const Color(0xFF065F46) : _kRose)),
                      const SizedBox(height: 2),
                      Text('category_images + verification_docs: نشط 🟢', style: TextStyle(fontSize: 11, color: Colors.grey.shade700)),
                    ],
                  ),
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: _realtimeOnline ? const Color(0xFFECFDF5) : const Color(0xFFFEF2F2),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: _realtimeOnline ? const Color(0xFFA7F3D0) : const Color(0xFFFECACA)),
            ),
            child: Row(
              children: [
                Icon(Icons.sensors_rounded, color: _realtimeOnline ? _kEmerald : _kRose, size: 24),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('محرك التزامن المباشر (Realtime WebSockets)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _realtimeOnline ? const Color(0xFF065F46) : _kRose)),
                      const SizedBox(height: 2),
                      Text('قنوات البث اللحظي: متصل 🟢', style: TextStyle(fontSize: 11, color: Colors.grey.shade700)),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),

      // Backup & Configuration Management
      _buildCardContainer(
        title: isRtl ? 'النسخ الاحتياطي وتصدير واستيراد الإعدادات' : 'Backup & Configuration Management',
        subtitle: isRtl ? 'حفظ نسخة كاملة من إعدادات المنصة بملف JSON أو استرجاعها' : 'Export all platform configurations as JSON or restore from backup',
        icon: Icons.backup_rounded,
        accentColor: _kRoyalBlue,
        children: [
          Row(
            children: [
              Expanded(
                child: OutlinedButton.icon(
                  style: OutlinedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    side: const BorderSide(color: _kRoyalBlue, width: 1.5),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  onPressed: _exportSettingsJson,
                  icon: const Icon(Icons.file_download_outlined, color: _kRoyalBlue, size: 18),
                  label: const Text('تصدير الإعدادات (JSON)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: _kRoyalBlue)),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: OutlinedButton.icon(
                  style: OutlinedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    side: const BorderSide(color: _kEmerald, width: 1.5),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  onPressed: _showImportJsonDialog,
                  icon: const Icon(Icons.file_upload_outlined, color: _kEmerald, size: 18),
                  label: const Text('استيراد ملف (JSON)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: _kEmerald)),
                ),
              ),
            ],
          ),
        ],
      ),
    ];

    if (!isWide) {
      return Column(children: [...col1, ...col2]);
    }

    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(child: Column(children: col1)),
        const SizedBox(width: 24),
        Expanded(child: Column(children: col2)),
      ],
    );
  }

  // ── Tab 6: Employees & Staff ───────────────────────────────────────
  Widget _buildEmployeesTab(bool isRtl) {
    return _EmployeesTabContent(
      primaryBlue: _kRoyalBlue,
    );
  }

  // ── Tab 7: Measurement & Sale Units Management ──────────────────────
  Widget _buildMeasurementUnitsTab(bool isRtl, bool isWide) {
    final globalSettings = ref.watch(globalSettingsProvider);
    final units = globalSettings.measurementUnits;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _buildCardContainer(
          title: isRtl ? 'وحدات البيع والقياس المعتمدة للمنصة' : 'Platform Measurement & Sale Units',
          subtitle: isRtl
              ? 'إدارة الوحدات والمقاييس الهندسية التي تظهر للتجار عند إضافة الأصناف والمنتجات'
              : 'Manage engineering units displayed to merchants during product creation',
          icon: Icons.straighten_rounded,
          accentColor: _kRoyalBlue,
          trailingAction: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              OutlinedButton.icon(
                icon: const Icon(Icons.restore_rounded, size: 16),
                label: Text(isRtl ? 'استعادة الافتراضي' : 'Reset Defaults', style: const TextStyle(fontSize: 12.5)),
                style: OutlinedButton.styleFrom(
                  foregroundColor: const Color(0xFF64748B),
                  side: const BorderSide(color: Color(0xFFCBD5E1)),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                onPressed: () => _confirmResetUnits(isRtl),
              ),
              const SizedBox(width: 8),
              FilledButton.icon(
                icon: const Icon(Icons.add_rounded, size: 18),
                label: Text(isRtl ? 'إضافة وحدة قياس' : 'Add Unit', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                style: FilledButton.styleFrom(
                  backgroundColor: _kRoyalBlue,
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                onPressed: () => _showAddOrEditUnitDialog(isRtl, null),
              ),
            ],
          ),
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFEFF6FF),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFBFDBFE)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.info_outline_rounded, color: _kRoyalBlue, size: 20),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      isRtl
                          ? 'أي وحدة تقوم بإضافتها أو تعديلها أو حذفها هنا ستنعكس فوراً على قائمة وحدات القياس لدى جميع التجار عند إضافة صنف جديد أو تسعير المنتجات.'
                          : 'Any unit added, updated, or removed here is instantly synchronized with all merchants across the platform.',
                      style: const TextStyle(fontSize: 12.5, color: Color(0xFF1E3A8A), fontWeight: FontWeight.w600),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            if (units.isEmpty)
              Center(
                child: Padding(
                  padding: const EdgeInsets.all(32.0),
                  child: Column(
                    children: [
                      const Icon(Icons.scale_rounded, size: 48, color: Color(0xFF94A3B8)),
                      const SizedBox(height: 12),
                      Text(
                        isRtl ? 'لا توجد وحدات بيع مضافة حالياً' : 'No measurement units found',
                        style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF64748B)),
                      ),
                      const SizedBox(height: 8),
                      ElevatedButton(
                        onPressed: () => _confirmResetUnits(isRtl),
                        child: Text(isRtl ? 'تحميل الوحدات القياسية الافتراضية' : 'Load Default Units'),
                      ),
                    ],
                  ),
                ),
              )
            else
              ListView.separated(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: units.length,
                separatorBuilder: (_, __) => const Divider(height: 1, color: Color(0xFFF1F5F9)),
                itemBuilder: (context, index) {
                  final unit = units[index];
                  return Padding(
                    padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 4),
                    child: Row(
                      children: [
                        Container(
                          width: 42,
                          height: 42,
                          decoration: BoxDecoration(
                            color: _kRoyalBlue.withValues(alpha: 0.08),
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: _kRoyalBlue.withValues(alpha: 0.2)),
                          ),
                          child: const Center(
                            child: Icon(Icons.straighten_rounded, color: _kRoyalBlue, size: 20),
                          ),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Text(
                                    unit.nameAr,
                                    style: const TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 14.5,
                                      color: Color(0xFF0F172A),
                                    ),
                                  ),
                                  if (unit.symbol.isNotEmpty) ...[
                                    const SizedBox(width: 8),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFF1F5F9),
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: Text(
                                        unit.symbol,
                                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF475569)),
                                      ),
                                    ),
                                  ],
                                ],
                              ),
                              const SizedBox(height: 2),
                              Text(
                                '${unit.nameEn} • ID: ${unit.id}',
                                style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                              ),
                            ],
                          ),
                        ),
                        Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            IconButton(
                              icon: const Icon(Icons.edit_rounded, color: Color(0xFF2563EB), size: 20),
                              tooltip: isRtl ? 'تعديل الوحدة' : 'Edit Unit',
                              onPressed: () => _showAddOrEditUnitDialog(isRtl, unit),
                            ),
                            IconButton(
                              icon: const Icon(Icons.delete_outline_rounded, color: Color(0xFFDC2626), size: 20),
                              tooltip: isRtl ? 'حذف الوحدة' : 'Delete Unit',
                              onPressed: () => _confirmDeleteUnit(isRtl, unit),
                            ),
                          ],
                        ),
                      ],
                    ),
                  );
                },
              ),
          ],
        ),
      ],
    );
  }

  void _showAddOrEditUnitDialog(bool isRtl, MeasurementUnit? existing) {
    final nameArCtrl = TextEditingController(text: existing?.nameAr ?? '');
    final nameEnCtrl = TextEditingController(text: existing?.nameEn ?? '');
    final symbolCtrl = TextEditingController(text: existing?.symbol ?? '');
    final idCtrl = TextEditingController(text: existing?.id ?? '');

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: _kRoyalBlue.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(existing != null ? Icons.edit_rounded : Icons.add_circle_outline_rounded, color: _kRoyalBlue, size: 22),
            ),
            const SizedBox(width: 10),
            Text(
              existing != null
                  ? (isRtl ? 'تعديل وحدة القياس' : 'Edit Measurement Unit')
                  : (isRtl ? 'إضافة وحدة قياس جديدة' : 'Add Measurement Unit'),
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 17, color: _kNavy),
            ),
          ],
        ),
        content: SizedBox(
          width: 440,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: nameArCtrl,
                decoration: InputDecoration(
                  labelText: isRtl ? 'اسم الوحدة بالعربية *' : 'Arabic Unit Name *',
                  hintText: isRtl ? 'مثلاً: طن، متر مكعب، كيس، لوري' : 'e.g. Ton, Bag, Truck',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: nameEnCtrl,
                decoration: InputDecoration(
                  labelText: isRtl ? 'اسم الوحدة بالإنجليزية *' : 'English Unit Name *',
                  hintText: isRtl ? 'مثلاً: Ton, Cubic Meter, Bag' : 'e.g. Ton, Cubic Meter',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: symbolCtrl,
                decoration: InputDecoration(
                  labelText: isRtl ? 'رمز أو اختصار الوحدة' : 'Unit Symbol / Abbreviation',
                  hintText: isRtl ? 'مثلاً: طن، م³، كيس' : 'e.g. Ton, m³, Bag',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: idCtrl,
                enabled: existing == null,
                decoration: InputDecoration(
                  labelText: isRtl ? 'المعرف البرمجي (ID)' : 'Unique ID',
                  hintText: isRtl ? 'مثلاً: ton_metric, box_50' : 'e.g. ton_metric',
                  helperText: isRtl ? 'يستخدم للربط التقني (حروف إنجليزية)' : 'Used for technical linking',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: Text(isRtl ? 'إلغاء' : 'Cancel'),
          ),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: _kRoyalBlue),
            onPressed: () async {
              final nameAr = nameArCtrl.text.trim();
              final nameEn = nameEnCtrl.text.trim();
              final symbol = symbolCtrl.text.trim();
              var id = idCtrl.text.trim();

              if (nameAr.isEmpty) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text(isRtl ? 'يرجى إدخال اسم الوحدة بالعربية' : 'Please enter Arabic unit name'), backgroundColor: _kRose),
                );
                return;
              }

              if (id.isEmpty) {
                id = nameEn.isNotEmpty
                    ? nameEn.toLowerCase().replaceAll(RegExp(r'[^a-z0-9]'), '_')
                    : 'unit_${DateTime.now().millisecondsSinceEpoch}';
              }

              final currentUnits = List<MeasurementUnit>.from(ref.read(globalSettingsProvider).measurementUnits);
              
              if (existing != null) {
                final idx = currentUnits.indexWhere((u) => u.id == existing.id);
                if (idx != -1) {
                  currentUnits[idx] = MeasurementUnit(
                    id: existing.id,
                    nameAr: nameAr,
                    nameEn: nameEn.isNotEmpty ? nameEn : nameAr,
                    symbol: symbol.isNotEmpty ? symbol : nameAr,
                  );
                }
              } else {
                currentUnits.add(MeasurementUnit(
                  id: id,
                  nameAr: nameAr,
                  nameEn: nameEn.isNotEmpty ? nameEn : nameAr,
                  symbol: symbol.isNotEmpty ? symbol : nameAr,
                ));
              }

              Navigator.pop(ctx);
              await ref.read(globalSettingsProvider.notifier).updateMeasurementUnits(currentUnits);
              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(isRtl ? 'تم حفظ وحدة القياس بنجاح وتحديثها للتجار' : 'Unit saved and broadcasted successfully'),
                    backgroundColor: _kEmerald,
                  ),
                );
              }
            },
            child: Text(isRtl ? 'حفظ الوحدة' : 'Save Unit'),
          ),
        ],
      ),
    );
  }

  void _confirmDeleteUnit(bool isRtl, MeasurementUnit unit) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            const Icon(Icons.warning_amber_rounded, color: _kRose, size: 24),
            const SizedBox(width: 8),
            Text(isRtl ? 'تأكيد حذف وحدة القياس' : 'Confirm Delete Unit', style: const TextStyle(fontWeight: FontWeight.bold)),
          ],
        ),
        content: Text(
          isRtl
              ? 'هل أنت متأكد من حذف وحدة القياس "${unit.nameAr}"؟ لن تظهر للتجار بعد الآن عند إضافة أصناف جديدة.'
              : 'Are you sure you want to delete "${unit.nameAr}"?',
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: Text(isRtl ? 'إلغاء' : 'Cancel')),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: _kRose),
            onPressed: () async {
              Navigator.pop(ctx);
              final currentUnits = List<MeasurementUnit>.from(ref.read(globalSettingsProvider).measurementUnits);
              currentUnits.removeWhere((u) => u.id == unit.id);
              await ref.read(globalSettingsProvider.notifier).updateMeasurementUnits(currentUnits);
              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(isRtl ? 'تم حذف وحدة القياس بنجاح' : 'Unit deleted successfully'),
                    backgroundColor: _kRose,
                  ),
                );
              }
            },
            child: Text(isRtl ? 'نعم، حذف' : 'Delete'),
          ),
        ],
      ),
    );
  }

  void _confirmResetUnits(bool isRtl) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            const Icon(Icons.restore_rounded, color: _kRoyalBlue, size: 24),
            const SizedBox(width: 8),
            Text(isRtl ? 'استعادة الوحدات القياسية الافتراضية' : 'Reset to Default Units', style: const TextStyle(fontWeight: FontWeight.bold)),
          ],
        ),
        content: Text(
          isRtl
              ? 'هل ترغب في إعادة ضبط قائمة وحدات القياس إلى القائمة القياسية المعتمدة لمنصة مقاييس (11 وحدة أساسية)؟'
              : 'Reset measurement units list to the 11 platform standard defaults?',
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: Text(isRtl ? 'إلغاء' : 'Cancel')),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: _kRoyalBlue),
            onPressed: () async {
              Navigator.pop(ctx);
              await ref.read(globalSettingsProvider.notifier).updateMeasurementUnits(MeasurementUnit.defaultUnits);
              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(isRtl ? 'تمت استعادة الوحدات الافتراضية بنجاح' : 'Default units restored successfully'),
                    backgroundColor: _kEmerald,
                  ),
                );
              }
            },
            child: Text(isRtl ? 'استعادة الافتراضي' : 'Reset'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    ref.listen<int>(settingsTabIndexProvider, (prev, next) {
      if (next > 0 && next < 7) {
        _tabController.animateTo(next);
        Future.microtask(() => ref.read(settingsTabIndexProvider.notifier).state = 0);
      }
    });

    final isRtl = Directionality.of(context) == TextDirection.rtl;

    if (_isLoading) {
      return const Scaffold(
        backgroundColor: _kLightBg,
        body: Center(child: CircularProgressIndicator(color: _kRoyalBlue)),
      );
    }

    return Scaffold(
      backgroundColor: _kLightBg,
      body: Stack(
        children: [
          CustomScrollView(
            slivers: [
              // Header Sliver
              SliverToBoxAdapter(
                child: Container(
                  color: Colors.white,
                  padding: const EdgeInsets.fromLTRB(28, 24, 28, 14),
                  child: Column(
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: _kRoyalBlue.withValues(alpha: 0.1),
                              borderRadius: BorderRadius.circular(14),
                            ),
                            child: const Icon(Icons.settings_suggest_rounded, color: _kRoyalBlue, size: 26),
                          ),
                          const SizedBox(width: 14),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'إعدادات المنظومة الشاملة (Enterprise Settings)',
                                  style: TextStyle(fontWeight: FontWeight.w900, fontSize: 22, color: _kNavy),
                                ),
                                const SizedBox(height: 3),
                                Text(
                                  'لوحة التحكم المركزية بالهوية، المالية، طرق الدفع، الترقية، الأمان، وصحة الخوادم.',
                                  style: TextStyle(fontSize: 12, color: Colors.grey.shade600, fontWeight: FontWeight.w500),
                                ),
                              ],
                            ),
                          ),
                          // Save Changes Top Button
                          FilledButton.icon(
                            style: FilledButton.styleFrom(
                              backgroundColor: _kEmerald,
                              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              elevation: 2,
                            ),
                            onPressed: _isSaving ? null : _saveSettings,
                            icon: _isSaving
                                ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                                : const Icon(Icons.save_rounded, size: 18),
                            label: Text(
                              _isSaving ? 'جاري الحفظ...' : 'حفظ كافة التغييرات',
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 18),
                      // Modern Pill Tab Bar
                      Container(
                        decoration: BoxDecoration(
                          color: _kLightBg,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: _kCardBorder),
                        ),
                        child: TabBar(
                          controller: _tabController,
                          isScrollable: true,
                          indicatorSize: TabBarIndicatorSize.tab,
                          indicator: BoxDecoration(
                            color: _kRoyalBlue,
                            borderRadius: BorderRadius.circular(10),
                            boxShadow: [
                              BoxShadow(color: _kRoyalBlue.withValues(alpha: 0.3), blurRadius: 8, offset: const Offset(0, 3)),
                            ],
                          ),
                          labelColor: Colors.white,
                          unselectedLabelColor: Colors.grey.shade700,
                          labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                          unselectedLabelStyle: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                          dividerColor: Colors.transparent,
                          padding: const EdgeInsets.all(4),
                          tabs: const [
                            Tab(icon: Icon(Icons.language_rounded, size: 17), text: 'النظام والهوية'),
                            Tab(icon: Icon(Icons.account_balance_wallet_rounded, size: 17), text: 'المالية والمدفوعات'),
                            Tab(icon: Icon(Icons.straighten_rounded, size: 17), text: 'وحدات البيع والقياس'),
                            Tab(icon: Icon(Icons.storefront_rounded, size: 17), text: 'ترقية الحسابات'),
                            Tab(icon: Icon(Icons.military_tech_rounded, size: 17), text: 'التحفيز والولاء'),
                            Tab(icon: Icon(Icons.shield_rounded, size: 17), text: 'الأمان والنسخ الاحتياطي'),
                            Tab(icon: Icon(Icons.badge_rounded, size: 17), text: 'فريق العمل والموظفين'),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              // Content Sliver
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(28, 20, 28, 100),
                sliver: SliverToBoxAdapter(
                  child: LayoutBuilder(
                    builder: (context, constraints) {
                      final isWide = constraints.maxWidth >= 960;
                      return AnimatedBuilder(
                        animation: _tabController,
                        builder: (context, _) {
                          switch (_tabController.index) {
                            case 0:
                              return _buildSystemTab(isRtl, isWide);
                            case 1:
                              return _buildFinanceTab(isRtl, isWide);
                            case 2:
                              return _buildMeasurementUnitsTab(isRtl, isWide);
                            case 3:
                              return _buildAccountUpgradesTab(isRtl, isWide);
                            case 4:
                              return _buildGamificationTab(isRtl, isWide);
                            case 5:
                              return _buildSecurityBackupTab(isRtl, isWide);
                            case 6:
                              return _buildEmployeesTab(isRtl);
                            default:
                              return _buildSystemTab(isRtl, isWide);
                          }
                        },
                      );
                    },
                  ),
                ),
              ),
            ],
          ),

          // Floating Bottom Save Notification Bar
          if (_hasUnsavedChanges)
            Positioned(
              bottom: 20,
              left: 28,
              right: 28,
              child: Center(
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 800),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
                    decoration: BoxDecoration(
                      color: _kNavy,
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: [
                        BoxShadow(color: Colors.black.withValues(alpha: 0.25), blurRadius: 20, offset: const Offset(0, 6)),
                      ],
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.info_outline_rounded, color: _kAmber, size: 22),
                        const SizedBox(width: 12),
                        const Expanded(
                          child: Text(
                            'توجد تعديلات غير محفوظة في إعدادات المنظومة!',
                            style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                          ),
                        ),
                        TextButton(
                          onPressed: () {
                            _loadSettings();
                            setState(() => _hasUnsavedChanges = false);
                          },
                          child: const Text('تراجع', style: TextStyle(color: Colors.grey, fontWeight: FontWeight.bold)),
                        ),
                        const SizedBox(width: 8),
                        FilledButton.icon(
                          style: FilledButton.styleFrom(
                            backgroundColor: _kEmerald,
                            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                          onPressed: _isSaving ? null : _saveSettings,
                          icon: _isSaving
                              ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                              : const Icon(Icons.check_circle_rounded, size: 18),
                          label: Text(_isSaving ? 'جاري الحفظ...' : 'حفظ التعديلات الآن', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }
}

// ── Employees & Staff Management Tab ─────────────────────────────────
class _EmployeesTabContent extends ConsumerStatefulWidget {
  final Color primaryBlue;

  const _EmployeesTabContent({
    required this.primaryBlue,
  });

  @override
  ConsumerState<_EmployeesTabContent> createState() => _EmployeesTabContentState();
}

class _EmployeesTabContentState extends ConsumerState<_EmployeesTabContent> {
  List<Map<String, dynamic>> _employees = [];
  List<Map<String, dynamic>> _filteredEmployees = [];
  bool _loading = true;
  final _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadEmployees();
    _searchCtrl.addListener(_applySearch);
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadEmployees() async {
    setState(() => _loading = true);
    try {
      final data = await Supabase.instance.client
          .from('users')
          .select()
          .inFilter('role', ['employee', 'admin'])
          .order('created_at', ascending: false);
      if (mounted) {
        setState(() {
          _employees = List<Map<String, dynamic>>.from(data);
          _filteredEmployees = _employees;
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _applySearch() {
    final q = _searchCtrl.text.trim().toLowerCase();
    setState(() {
      if (q.isEmpty) {
        _filteredEmployees = _employees;
      } else {
        _filteredEmployees = _employees.where((e) {
          final name = (e['name'] ?? '').toString().toLowerCase();
          final nameAr = (e['name_ar'] ?? '').toString().toLowerCase();
          final email = (e['email'] ?? '').toString().toLowerCase();
          final phone = (e['phone'] ?? '').toString().toLowerCase();
          return name.contains(q) || nameAr.contains(q) || email.contains(q) || phone.contains(q);
        }).toList();
      }
    });
  }

  Future<void> _toggleSuspend(Map<String, dynamic> emp) async {
    final isSuspended = emp['is_suspended'] == true;
    try {
      await Supabase.instance.client
          .from('users')
          .update({'is_suspended': !isSuspended})
          .eq('id', emp['id']);
      _loadEmployees();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(isSuspended ? 'تم تنشيط حساب الموظف' : 'تم إيقاف حساب الموظف مؤقتاً'),
          backgroundColor: isSuspended ? _kEmerald : _kAmber,
          behavior: SnackBarBehavior.floating,
        ));
      }
    } catch (_) {}
  }

  Future<void> _deleteEmployee(Map<String, dynamic> emp) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('تأكيد حذف الموظف', style: TextStyle(fontWeight: FontWeight.bold)),
        content: Text('هل أنت متأكد من حذف الموظف "${emp['name_ar'] ?? emp['name']}" من النظام نهائياً؟'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('إلغاء')),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: _kRose),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('حذف نهائي'),
          ),
        ],
      ),
    );
    if (confirmed == true) {
      try {
        await Supabase.instance.client.from('users').delete().eq('id', emp['id']);
        _loadEmployees();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
            content: Text('تم حذف الموظف بنجاح'),
            backgroundColor: _kRose,
            behavior: SnackBarBehavior.floating,
          ));
        }
      } catch (_) {}
    }
  }

  void _showAddEditDialog({Map<String, dynamic>? existing}) {
    final nameCtrl = TextEditingController(text: existing?['name'] ?? '');
    final nameArCtrl = TextEditingController(text: existing?['name_ar'] ?? '');
    final emailCtrl = TextEditingController(text: existing?['email'] ?? '');
    final phoneCtrl = TextEditingController(text: existing?['phone'] ?? '');
    final isEdit = existing != null;
    final formKey = GlobalKey<FormState>();

    showDialog(
      context: context,
      builder: (ctx) => Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        child: Container(
          width: 520,
          padding: const EdgeInsets.all(28),
          child: Form(
            key: formKey,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: widget.primaryBlue.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(isEdit ? Icons.edit_rounded : Icons.person_add_alt_1_rounded, color: widget.primaryBlue, size: 24),
                    ),
                    const SizedBox(width: 14),
                    Text(
                      isEdit ? 'تعديل بيانات الموظف' : 'إضافة موظف جديد للنظام',
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: _kNavy),
                    ),
                    const Spacer(),
                    IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(ctx)),
                  ],
                ),
                const SizedBox(height: 20),
                Row(children: [
                  Expanded(child: TextFormField(
                    controller: nameArCtrl, 
                    decoration: InputDecoration(labelText: 'الاسم (بالعربية) *', prefixIcon: const Icon(Icons.person), border: OutlineInputBorder(borderRadius: BorderRadius.circular(12))),
                    validator: (val) => val == null || val.trim().isEmpty ? 'مطلوب' : null,
                  )),
                  const SizedBox(width: 12),
                  Expanded(child: TextFormField(controller: nameCtrl, decoration: InputDecoration(labelText: 'الاسم (بالإنجليزية)', prefixIcon: const Icon(Icons.person_outline), border: OutlineInputBorder(borderRadius: BorderRadius.circular(12))))),
                ]),
                const SizedBox(height: 14),
                TextFormField(
                  controller: emailCtrl, 
                  decoration: InputDecoration(labelText: 'البريد الإلكتروني *', prefixIcon: const Icon(Icons.email_outlined), border: OutlineInputBorder(borderRadius: BorderRadius.circular(12))),
                  validator: (val) {
                    if (val == null || val.trim().isEmpty) return 'مطلوب';
                    if (!val.contains('@')) return 'بريد غير صالح';
                    return null;
                  },
                ),
                const SizedBox(height: 14),
                TextFormField(controller: phoneCtrl, decoration: InputDecoration(labelText: 'رقم الهاتف', prefixIcon: const Icon(Icons.phone_outlined), border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)))),
                const SizedBox(height: 24),
                Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    OutlinedButton(onPressed: () => Navigator.pop(ctx), child: const Text('إلغاء')),
                    const SizedBox(width: 12),
                    FilledButton.icon(
                      style: FilledButton.styleFrom(backgroundColor: widget.primaryBlue, padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12)),
                      onPressed: () async {
                        if (!formKey.currentState!.validate()) return;
                        final nameAr = nameArCtrl.text.trim();
                        final email = emailCtrl.text.trim();
                        Navigator.pop(ctx);
                        try {
                          if (isEdit) {
                            await Supabase.instance.client.from('users').update({
                              'name': nameCtrl.text.trim().isNotEmpty ? nameCtrl.text.trim() : nameAr,
                              'name_ar': nameAr,
                              'email': email,
                              'phone': phoneCtrl.text.trim(),
                            }).eq('id', existing['id']);
                          } else {
                            // NOTE: This inserts the DB record directly. It does NOT create a Supabase Auth account.
                            // The employee must be invited via Supabase Dashboard Auth to actually log in.
                            await Supabase.instance.client.from('users').insert({
                              'name': nameCtrl.text.trim().isNotEmpty ? nameCtrl.text.trim() : nameAr,
                              'name_ar': nameAr,
                              'email': email,
                              'phone': phoneCtrl.text.trim(),
                              'role': 'employee',
                              'is_verified': true,
                              'is_suspended': false,
                            });
                          }
                          _loadEmployees();
                          if (mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                              content: Text(isEdit ? 'تم تحديث بيانات الموظف بنجاح' : 'تم إضافة الموظف بنجاح. تذكر: يجب دعوته عبر Supabase Dashboard Auth.'),
                              backgroundColor: _kEmerald,
                              behavior: SnackBarBehavior.floating,
                              duration: const Duration(seconds: 4),
                            ));
                          }
                        } catch (e) {
                          if (mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                              content: Text('حدث خطأ: $e'),
                              backgroundColor: Colors.red,
                              behavior: SnackBarBehavior.floating,
                            ));
                          }
                        }
                      },
                      icon: Icon(isEdit ? Icons.save_rounded : Icons.add_rounded, size: 18),
                      label: Text(isEdit ? 'حفظ التعديلات' : 'إضافة الموظف', style: const TextStyle(fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildEmployeeLevelTile({
    required String title,
    required String subtitle,
    required String value,
    required String currentValue,
    required VoidCallback onSelect,
  }) {
    final isSelected = value == currentValue;
    return InkWell(
      onTap: onSelect,
      borderRadius: BorderRadius.circular(10),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        child: Row(
          children: [
            Icon(
              isSelected ? Icons.radio_button_checked : Icons.radio_button_off,
              color: isSelected ? _kPurple : Colors.grey.shade400,
              size: 20,
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: isSelected ? _kNavy : Colors.grey.shade800)),
                  const SizedBox(height: 2),
                  Text(subtitle, style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showPermissionsDialog(Map<String, dynamic> emp) {
    final existingPerms = emp['permissions'] is Map
        ? Map<String, dynamic>.from(emp['permissions'] as Map)
        : <String, dynamic>{};

    final sections = [
      {'key': 'dashboard', 'label_ar': 'لوحة التحكم الرئيسية', 'icon': Icons.dashboard_rounded},
      {'key': 'subscribers', 'label_ar': 'إدارة المشتركين (التجار والعملاء)', 'icon': Icons.people_rounded},
      {'key': 'ads', 'label_ar': 'إدارة الإعلانات والحملات', 'icon': Icons.campaign_rounded},
      {'key': 'orders', 'label_ar': 'إدارة وتتبع الطلبات', 'icon': Icons.shopping_cart_rounded},
      {'key': 'categories', 'label_ar': 'إدارة الأقسام والتصنيفات', 'icon': Icons.category_rounded},
      {'key': 'products', 'label_ar': 'إدارة الأصناف والأسعار والتسعير', 'icon': Icons.price_change_rounded},
      {'key': 'services', 'label_ar': 'مقدمو الخدمات الفنية والهندسية', 'icon': Icons.engineering_rounded},
      {'key': 'finance', 'label_ar': 'المالية والحسابات والتقارير', 'icon': Icons.account_balance_wallet_rounded},
      {'key': 'settings', 'label_ar': 'إعدادات المنظومة الشاملة', 'icon': Icons.settings_rounded},
    ];

    final toggles = <String, dynamic>{};
    for (final s in sections) {
      final key = s['key'] as String;
      toggles[key] = existingPerms[key] != false;
    }

    String employeeLevel = (emp['role'] == 'admin') ? 'admin' : (existingPerms['app_level'] ?? 'employee');
    bool canViewRadar = existingPerms['can_view_radar'] ?? true;
    bool canManageProducts = existingPerms['can_manage_products'] ?? true;
    bool canManageCategories = existingPerms['can_manage_categories'] ?? false;
    bool canManageSubscribers = existingPerms['can_manage_subscribers'] ?? false;
    bool canManageAds = existingPerms['can_manage_ads'] ?? false;
    bool canChatSupport = existingPerms['can_chat_support'] ?? true;
    bool canManageDigitalBroker = existingPerms['can_manage_digital_broker'] ?? false;

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) => DefaultTabController(
          length: 2,
          child: Dialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            child: Container(
              width: 620,
              constraints: const BoxConstraints(maxHeight: 780),
              padding: const EdgeInsets.all(28),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: _kPurple.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Icon(Icons.shield_rounded, color: _kPurple, size: 24),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('إدارة صلاحيات ورتبة الموظف', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: _kNavy)),
                            const SizedBox(height: 2),
                            Text(emp['name_ar'] ?? emp['name'] ?? '', style: TextStyle(fontSize: 13, color: Colors.grey.shade600, fontWeight: FontWeight.w600)),
                          ],
                        ),
                      ),
                      IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(ctx)),
                    ],
                  ),
                  const SizedBox(height: 14),
                  TabBar(
                    labelColor: _kPurple,
                    unselectedLabelColor: Colors.grey.shade600,
                    indicatorColor: _kPurple,
                    labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                    tabs: const [
                      Tab(text: 'صلاحيات التطبيق (الميداني)'),
                      Tab(text: 'صلاحيات لوحة التحكم (PC)'),
                    ],
                  ),
                  const SizedBox(height: 14),
                  Flexible(
                    child: TabBarView(
                      children: [
                        // Tab 1: App Permissions
                        SingleChildScrollView(
                          padding: const EdgeInsets.symmetric(vertical: 8),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('مستوى ورتبة الموظف في النظام:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: _kNavy)),
                              const SizedBox(height: 10),
                              Container(
                                decoration: BoxDecoration(border: Border.all(color: _kCardBorder), borderRadius: BorderRadius.circular(12)),
                                child: Column(
                                  children: [
                                    _buildEmployeeLevelTile(
                                      title: 'مدير نظام عام (System Admin)',
                                      subtitle: 'له كامل الصلاحيات في التطبيق ولوحة التحكم دون قيود',
                                      value: 'admin',
                                      currentValue: employeeLevel,
                                      onSelect: () => setDialogState(() => employeeLevel = 'admin'),
                                    ),
                                    const Divider(height: 1),
                                    _buildEmployeeLevelTile(
                                      title: 'مشرف ميداني (Field Supervisor)',
                                      subtitle: 'إدارة العمليات الميدانية، الرادار، واعتمادات المناديب',
                                      value: 'supervisor',
                                      currentValue: employeeLevel,
                                      onSelect: () => setDialogState(() => employeeLevel = 'supervisor'),
                                    ),
                                    const Divider(height: 1),
                                    _buildEmployeeLevelTile(
                                      title: 'موظف تنفيذي (Executive Staff)',
                                      subtitle: 'صلاحيات مخصصة ومحددة حسب المفاتيح أدناه',
                                      value: 'employee',
                                      currentValue: employeeLevel,
                                      onSelect: () => setDialogState(() => employeeLevel = 'employee'),
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(height: 20),
                              const Text('صلاحيات الوصول في تطبيق الموبايل:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: _kNavy)),
                              const SizedBox(height: 10),
                              SwitchListTile(
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                tileColor: _kLightBg,
                                title: const Text('شاشة الرادار ومراقبة الطلبات الحية', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                                value: canViewRadar,
                                activeThumbColor: _kPurple,
                                onChanged: (employeeLevel == 'employee' || employeeLevel == 'supervisor') ? (val) => setDialogState(() => canViewRadar = val) : null,
                              ),
                              const SizedBox(height: 6),
                              SwitchListTile(
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                tileColor: _kLightBg,
                                title: const Text('إدارة الأصناف والمنتجات وتعديل الأسعار', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                                value: canManageProducts,
                                activeThumbColor: _kPurple,
                                onChanged: (employeeLevel == 'employee' || employeeLevel == 'supervisor') ? (val) => setDialogState(() => canManageProducts = val) : null,
                              ),
                              const SizedBox(height: 6),
                              SwitchListTile(
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                tileColor: _kLightBg,
                                title: const Text('إدارة الأقسام والتصنيفات', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                                value: canManageCategories,
                                activeThumbColor: _kPurple,
                                onChanged: (employeeLevel == 'employee' || employeeLevel == 'supervisor') ? (val) => setDialogState(() => canManageCategories = val) : null,
                              ),
                              const SizedBox(height: 6),
                              SwitchListTile(
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                tileColor: _kLightBg,
                                title: const Text('إدارة المشتركين والتجار والعملاء', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                                value: canManageSubscribers,
                                activeThumbColor: _kPurple,
                                onChanged: (employeeLevel == 'employee' || employeeLevel == 'supervisor') ? (val) => setDialogState(() => canManageSubscribers = val) : null,
                              ),
                              const SizedBox(height: 6),
                              SwitchListTile(
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                tileColor: _kLightBg,
                                title: const Text('إدارة الإعلانات والحملات', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                                value: canManageAds,
                                activeThumbColor: _kPurple,
                                onChanged: (employeeLevel == 'employee' || employeeLevel == 'supervisor') ? (val) => setDialogState(() => canManageAds = val) : null,
                              ),
                              const SizedBox(height: 6),
                              SwitchListTile(
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                tileColor: _kLightBg,
                                title: const Text('الدعم الفني والمحادثات المباشرة', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                                value: canChatSupport,
                                activeThumbColor: _kPurple,
                                onChanged: (employeeLevel == 'employee' || employeeLevel == 'supervisor') ? (val) => setDialogState(() => canChatSupport = val) : null,
                              ),
                              const SizedBox(height: 6),
                              SwitchListTile(
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                tileColor: _kLightBg,
                                title: const Text('إدارة الوسيط الرقمي', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                                value: canManageDigitalBroker,
                                activeThumbColor: _kPurple,
                                onChanged: (employeeLevel == 'employee' || employeeLevel == 'supervisor') ? (val) => setDialogState(() => canManageDigitalBroker = val) : null,
                              ),
                            ],
                          ),
                        ),

                        // Tab 2: Dashboard Permissions
                        ListView.separated(
                          padding: const EdgeInsets.symmetric(vertical: 8),
                          shrinkWrap: true,
                          itemCount: sections.length,
                          separatorBuilder: (context, index) => const SizedBox(height: 6),
                          itemBuilder: (context, i) {
                            final sec = sections[i];
                            final key = sec['key'] as String;
                            final enabled = toggles[key] == true;
                            return Container(
                              decoration: BoxDecoration(
                                color: enabled ? _kPurple.withValues(alpha: 0.04) : _kLightBg,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(color: enabled ? _kPurple.withValues(alpha: 0.25) : _kCardBorder),
                              ),
                              child: SwitchListTile(
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                secondary: Icon(sec['icon'] as IconData, color: enabled ? _kPurple : Colors.grey.shade400, size: 22),
                                title: Text(sec['label_ar'] as String, style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: enabled ? _kNavy : Colors.grey.shade600)),
                                value: enabled,
                                activeThumbColor: _kPurple,
                                onChanged: (val) => setDialogState(() => toggles[key] = val),
                              ),
                            );
                          },
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 18),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      OutlinedButton(onPressed: () => Navigator.pop(ctx), child: const Text('إلغاء')),
                      const SizedBox(width: 12),
                      FilledButton.icon(
                        style: FilledButton.styleFrom(backgroundColor: _kPurple, padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12)),
                        onPressed: () async {
                          Navigator.pop(ctx);
                          toggles['app_level'] = employeeLevel;
                          toggles['can_view_radar'] = canViewRadar;
                          toggles['can_manage_products'] = canManageProducts;
                          toggles['can_manage_categories'] = canManageCategories;
                          toggles['can_manage_subscribers'] = canManageSubscribers;
                          toggles['can_manage_ads'] = canManageAds;
                          toggles['can_chat_support'] = canChatSupport;
                          toggles['can_manage_digital_broker'] = canManageDigitalBroker;

                          try {
                            final String newRole = (employeeLevel == 'admin') ? 'admin' : 'employee';
                            await Supabase.instance.client
                                .from('users')
                                .update({'permissions': toggles, 'role': newRole})
                                .eq('id', emp['id']);
                            _loadEmployees();
                            if (mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
                                content: Text('✅ تم حفظ وتطبيق صلاحيات الموظف بنجاح'),
                                backgroundColor: _kPurple,
                                behavior: SnackBarBehavior.floating,
                              ));
                            }
                          } catch (_) {}
                        },
                        icon: const Icon(Icons.save_rounded, size: 18),
                        label: const Text('حفظ التحديثات', style: TextStyle(fontWeight: FontWeight.bold)),
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

  Widget _buildKpiCard(String title, String value, IconData icon, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.08),
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: color.withValues(alpha: 0.2)),
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, color: color, size: 24),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(value, style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: color)),
                  const SizedBox(height: 2),
                  Text(title, style: TextStyle(fontSize: 12, color: color.withValues(alpha: 0.9), fontWeight: FontWeight.w600), overflow: TextOverflow.ellipsis),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final totalActive = _employees.where((e) => e['is_suspended'] != true).length;
    final totalSuspended = _employees.where((e) => e['is_suspended'] == true).length;

    if (_loading) {
      return const Center(child: CircularProgressIndicator(color: _kRoyalBlue));
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // KPI & Add button
        Row(
          children: [
            _buildKpiCard('إجمالي الكادر', '${_employees.length}', Icons.groups_rounded, widget.primaryBlue),
            const SizedBox(width: 14),
            _buildKpiCard('نشطون ومفعلون', '$totalActive', Icons.check_circle_rounded, _kEmerald),
            const SizedBox(width: 14),
            _buildKpiCard('موقوفون مؤقتاً', '$totalSuspended', Icons.block_rounded, _kRose),
            const SizedBox(width: 16),
            FilledButton.icon(
              style: FilledButton.styleFrom(
                backgroundColor: widget.primaryBlue,
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              onPressed: () => _showAddEditDialog(),
              icon: const Icon(Icons.person_add_alt_1_rounded, size: 20),
              label: const Text('إضافة موظف جديد', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
            ),
          ],
        ),
        const SizedBox(height: 20),

        // Search Field
        TextField(
          controller: _searchCtrl,
          decoration: InputDecoration(
            hintText: 'بحث بالاسم، البريد الإلكتروني، أو رقم الهاتف...',
            prefixIcon: const Icon(Icons.search_rounded, color: _kNavy),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: _kCardBorder)),
            enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: _kCardBorder)),
            filled: true,
            fillColor: Colors.white,
          ),
        ),
        const SizedBox(height: 20),

        // Table
        Container(
          width: double.infinity,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: _kCardBorder),
            boxShadow: [
              BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 10, offset: const Offset(0, 4)),
            ],
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(16),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: DataTable(
                headingRowColor: WidgetStateProperty.all(widget.primaryBlue.withValues(alpha: 0.06)),
              headingTextStyle: TextStyle(fontWeight: FontWeight.bold, color: widget.primaryBlue, fontSize: 13),
              dataRowMinHeight: 56,
              dataRowMaxHeight: 64,
              columnSpacing: 24,
              columns: const [
                DataColumn(label: Text('الموظف')),
                DataColumn(label: Text('البريد الإلكتروني')),
                DataColumn(label: Text('الهاتف')),
                DataColumn(label: Text('الحالة')),
                DataColumn(label: Text('تاريخ الانضمام')),
                DataColumn(label: Text('الإجراءات')),
              ],
              rows: _filteredEmployees.isEmpty
                  ? [
                      const DataRow(cells: [
                        DataCell(Text('لا يوجد موظفون مطابقون للبحث', style: TextStyle(color: Colors.grey))),
                        DataCell(SizedBox()),
                        DataCell(SizedBox()),
                        DataCell(SizedBox()),
                        DataCell(SizedBox()),
                        DataCell(SizedBox()),
                      ])
                    ]
                  : _filteredEmployees.map((emp) {
                      final isSuspended = emp['is_suspended'] == true;
                      final joinDate = DateTime.tryParse(emp['created_at'] ?? '');
                      return DataRow(cells: [
                        DataCell(
                          Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              CircleAvatar(
                                radius: 18,
                                backgroundColor: widget.primaryBlue.withValues(alpha: 0.1),
                                child: Text(
                                  (emp['name_ar'] ?? emp['name'] ?? '?').toString().isNotEmpty 
                                      ? (emp['name_ar'] ?? emp['name'] ?? '?').toString().substring(0, 1) 
                                      : '?',
                                  style: TextStyle(color: widget.primaryBlue, fontWeight: FontWeight.bold),
                                ),
                              ),
                              const SizedBox(width: 10),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Text(emp['name_ar'] ?? emp['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: _kNavy)),
                                      if (emp['role'] == 'admin') ...[
                                        const SizedBox(width: 6),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                          decoration: BoxDecoration(
                                            color: _kPurple.withValues(alpha: 0.12),
                                            borderRadius: BorderRadius.circular(8),
                                          ),
                                          child: const Text('مدير نظام 👑', style: TextStyle(color: _kPurple, fontSize: 10, fontWeight: FontWeight.bold)),
                                        ),
                                      ],
                                    ],
                                  ),
                                  if ((emp['name'] ?? '').isNotEmpty && emp['name'] != emp['name_ar'])
                                    Text(emp['name'], style: TextStyle(color: Colors.grey.shade500, fontSize: 11)),
                                ],
                              ),
                            ],
                          ),
                        ),
                        DataCell(Text(emp['email'] ?? '', style: const TextStyle(fontSize: 13))),
                        DataCell(Text(emp['phone'] ?? '—', style: const TextStyle(fontSize: 13))),
                        DataCell(
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: isSuspended ? _kRose.withValues(alpha: 0.1) : _kEmerald.withValues(alpha: 0.1),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Text(
                              isSuspended ? 'موقوف' : 'نشط 🟢',
                              style: TextStyle(
                                color: isSuspended ? _kRose : _kEmerald,
                                fontWeight: FontWeight.bold,
                                fontSize: 11,
                              ),
                            ),
                          ),
                        ),
                        DataCell(Text(
                          joinDate != null ? DateFormat('yyyy/MM/dd').format(joinDate) : '—',
                          style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                        )),
                        DataCell(
                          Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              IconButton(
                                tooltip: 'إدارة الصلاحيات والرتبة',
                                icon: const Icon(Icons.shield_outlined, color: _kPurple, size: 20),
                                onPressed: () => _showPermissionsDialog(emp),
                              ),
                              IconButton(
                                tooltip: 'تعديل البيانات',
                                icon: Icon(Icons.edit_outlined, color: widget.primaryBlue, size: 20),
                                onPressed: () => _showAddEditDialog(existing: emp),
                              ),
                              IconButton(
                                tooltip: isSuspended ? 'تنشيط' : 'إيقاف مؤقت',
                                icon: Icon(
                                  isSuspended ? Icons.play_circle_outline : Icons.pause_circle_outline,
                                  color: isSuspended ? _kEmerald : _kAmber,
                                  size: 20,
                                ),
                                onPressed: () => _toggleSuspend(emp),
                              ),
                              IconButton(
                                tooltip: 'حذف',
                                icon: const Icon(Icons.delete_outline, color: _kRose, size: 20),
                                onPressed: () => _deleteEmployee(emp),
                              ),
                            ],
                          ),
                        ),
                      ]);
                    }).toList(),
            ),
          ),
        ),
        ),
      ],
    );
  }
}
