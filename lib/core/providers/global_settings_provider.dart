import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

class MeasurementUnit {
  final String id;
  final String nameAr;
  final String nameEn;
  final String symbol;

  const MeasurementUnit({
    required this.id,
    required this.nameAr,
    required this.nameEn,
    this.symbol = '',
  });

  Map<String, dynamic> toJson() => {
    'id': id,
    'nameAr': nameAr,
    'nameEn': nameEn,
    'symbol': symbol,
  };

  factory MeasurementUnit.fromJson(Map<String, dynamic> json) {
    return MeasurementUnit(
      id: json['id']?.toString() ?? '',
      nameAr: json['nameAr']?.toString() ?? json['name_ar']?.toString() ?? '',
      nameEn: json['nameEn']?.toString() ?? json['name_en']?.toString() ?? '',
      symbol: json['symbol']?.toString() ?? '',
    );
  }

  static const List<MeasurementUnit> defaultUnits = [
    MeasurementUnit(id: 'ton', nameAr: 'طن', nameEn: 'Ton', symbol: 'طن'),
    MeasurementUnit(id: 'bag', nameAr: 'كيس (50 كجم)', nameEn: 'Bag (50kg)', symbol: 'كيس'),
    MeasurementUnit(id: 'm2', nameAr: 'متر مربع (م²)', nameEn: 'Square Meter (m²)', symbol: 'م²'),
    MeasurementUnit(id: 'm3', nameAr: 'متر مكعب (م³)', nameEn: 'Cubic Meter (m³)', symbol: 'م³'),
    MeasurementUnit(id: 'piece', nameAr: 'حبة / قطعة', nameEn: 'Piece / Item', symbol: 'حبة'),
    MeasurementUnit(id: 'bar', nameAr: 'عود / سيخ (6م/12م)', nameEn: 'Length / Bar (6m / 12m)', symbol: 'عود'),
    MeasurementUnit(id: 'sheet', nameAr: 'لوح', nameEn: 'Sheet / Panel', symbol: 'لوح'),
    MeasurementUnit(id: 'bucket', nameAr: 'برميل (18 لتر)', nameEn: 'Drum / Bucket (18L)', symbol: 'برميل'),
    MeasurementUnit(id: 'gallon', nameAr: 'جالون (4 لتر)', nameEn: 'Gallon (4L)', symbol: 'جالون'),
    MeasurementUnit(id: 'thousand', nameAr: 'ألف حبة', nameEn: 'Thousand Pcs (1000)', symbol: 'ألف'),
    MeasurementUnit(id: 'truck', nameAr: 'لوري / قلاب', nameEn: 'Truck Load', symbol: 'لوري'),
  ];
}

class GlobalSettings {
  final Map<String, dynamic> platformSettings;
  final double vatPercent;
  final bool isTaxAndCommissionEnabled;

  // ── 1. Exclusive Magaiees Settings (حصري على مقاييس) ───────────────
  final bool exclusiveBankak;
  final String exclusiveBankakAccount;
  final String exclusiveBankakName;
  final String exclusiveBankakIban;

  final bool exclusiveOcash;
  final String exclusiveOcashAccount;
  final String exclusiveOcashName;

  final bool exclusiveCod;
  final double exclusiveMaxCod;

  final bool exclusiveWire;
  final String exclusiveWireBank;
  final String exclusiveWireAccount;
  final String exclusiveWireName;

  final bool exclusiveFawry;
  final double exclusiveMaxFawry;

  final bool exclusiveB2b;
  final bool exclusivePartialCod;
  final double exclusivePartialDepositPercent;

  // ── 2. Marketplace Merchants Settings (تجار السوق والموردين) ─────────
  final bool merchantAllowBankak;
  final bool merchantAllowOcash;
  final bool merchantAllowCod;
  final double merchantMaxCod;
  final bool merchantAllowWire;
  final bool merchantAllowB2b;
  final bool merchantAllowPartialCod;

  // ── Logistics & Platform ──────────────────────────────────────────
  final double baseDeliveryFee;
  final double deliveryPerKm;
  final double freeDeliveryThreshold;
  final bool enableMerchantUpgrade;
  final double platformUsdRate;
  final List<MeasurementUnit> measurementUnits;
  final List<String> hiddenCategoryIds;
  final List<String> hiddenSubCategoryIds;

  bool isCategoryVisible(String categoryId) => !hiddenCategoryIds.contains(categoryId);
  bool isSubCategoryVisible(String subCategoryId) => !hiddenSubCategoryIds.contains(subCategoryId);

  // Backward compatibility getters
  bool get isBankakEnabled => exclusiveBankak;
  bool get isOcashEnabled => exclusiveOcash;
  bool get isCashOnDeliveryEnabled => exclusiveCod;
  bool get isDirectBankWireEnabled => exclusiveWire;
  bool get isFawryEnabled => exclusiveFawry;
  bool get isB2bCreditEnabled => exclusiveB2b;
  bool get isDirectMerchantPayEnabled => merchantAllowWire;
  double get maxCodAmount => exclusiveMaxCod;
  double get maxElectronicPayAmount => exclusiveMaxFawry;

  GlobalSettings({
    this.vatPercent = 0.0,
    this.isTaxAndCommissionEnabled = false,
    this.exclusiveBankak = true,
    this.exclusiveBankakAccount = '1984256',
    this.exclusiveBankakName = 'شركة مقاييس للتوريدات الهندسية',
    this.exclusiveBankakIban = 'SD84BOKH0000000001984256',
    this.exclusiveOcash = true,
    this.exclusiveOcashAccount = '0912300105',
    this.exclusiveOcashName = 'محفظة مقاييس - أوكاش',
    this.exclusiveCod = true,
    this.exclusiveMaxCod = 5000000.0,
    this.exclusiveWire = true,
    this.exclusiveWireBank = 'بنك فيصل الإسلامي',
    this.exclusiveWireAccount = '12345678',
    this.exclusiveWireName = 'شركة مقاييس للتوريدات الهندسية',
    this.exclusiveFawry = true,
    this.exclusiveMaxFawry = 10000000.0,
    this.exclusiveB2b = true,
    this.exclusivePartialCod = true,
    this.exclusivePartialDepositPercent = 30.0,
    this.merchantAllowBankak = true,
    this.merchantAllowOcash = true,
    this.merchantAllowCod = true,
    this.merchantMaxCod = 3000000.0,
    this.merchantAllowWire = true,
    this.merchantAllowB2b = true,
    this.merchantAllowPartialCod = true,
    this.baseDeliveryFee = 5000.0,
    this.deliveryPerKm = 350.0,
    this.freeDeliveryThreshold = 2000000.0,
    this.enableMerchantUpgrade = false,
    this.platformUsdRate = 2800.0,
    this.platformSettings = const {},
    this.measurementUnits = MeasurementUnit.defaultUnits,
    this.hiddenCategoryIds = const [],
    this.hiddenSubCategoryIds = const [],
  });

  GlobalSettings copyWith({
    Map<String, dynamic>? platformSettings,
    double? vatPercent,
    bool? isTaxAndCommissionEnabled,
    bool? exclusiveBankak,
    String? exclusiveBankakAccount,
    String? exclusiveBankakName,
    String? exclusiveBankakIban,
    bool? exclusiveOcash,
    String? exclusiveOcashAccount,
    String? exclusiveOcashName,
    bool? exclusiveCod,
    double? exclusiveMaxCod,
    bool? exclusiveWire,
    String? exclusiveWireBank,
    String? exclusiveWireAccount,
    String? exclusiveWireName,
    bool? exclusiveFawry,
    double? exclusiveMaxFawry,
    bool? exclusiveB2b,
    bool? exclusivePartialCod,
    double? exclusivePartialDepositPercent,
    bool? merchantAllowBankak,
    bool? merchantAllowOcash,
    bool? merchantAllowCod,
    double? merchantMaxCod,
    bool? merchantAllowWire,
    bool? merchantAllowB2b,
    bool? merchantAllowPartialCod,
    double? baseDeliveryFee,
    double? deliveryPerKm,
    double? freeDeliveryThreshold,
    bool? enableMerchantUpgrade,
    double? platformUsdRate,
    List<MeasurementUnit>? measurementUnits,
    List<String>? hiddenCategoryIds,
    List<String>? hiddenSubCategoryIds,
  }) {
    return GlobalSettings(
      platformSettings: platformSettings ?? this.platformSettings,
      vatPercent: vatPercent ?? this.vatPercent,
      isTaxAndCommissionEnabled: isTaxAndCommissionEnabled ?? this.isTaxAndCommissionEnabled,
      exclusiveBankak: exclusiveBankak ?? this.exclusiveBankak,
      exclusiveBankakAccount: exclusiveBankakAccount ?? this.exclusiveBankakAccount,
      exclusiveBankakName: exclusiveBankakName ?? this.exclusiveBankakName,
      exclusiveBankakIban: exclusiveBankakIban ?? this.exclusiveBankakIban,
      exclusiveOcash: exclusiveOcash ?? this.exclusiveOcash,
      exclusiveOcashAccount: exclusiveOcashAccount ?? this.exclusiveOcashAccount,
      exclusiveOcashName: exclusiveOcashName ?? this.exclusiveOcashName,
      exclusiveCod: exclusiveCod ?? this.exclusiveCod,
      exclusiveMaxCod: exclusiveMaxCod ?? this.exclusiveMaxCod,
      exclusiveWire: exclusiveWire ?? this.exclusiveWire,
      exclusiveWireBank: exclusiveWireBank ?? this.exclusiveWireBank,
      exclusiveWireAccount: exclusiveWireAccount ?? this.exclusiveWireAccount,
      exclusiveWireName: exclusiveWireName ?? this.exclusiveWireName,
      exclusiveFawry: exclusiveFawry ?? this.exclusiveFawry,
      exclusiveMaxFawry: exclusiveMaxFawry ?? this.exclusiveMaxFawry,
      exclusiveB2b: exclusiveB2b ?? this.exclusiveB2b,
      exclusivePartialCod: exclusivePartialCod ?? this.exclusivePartialCod,
      exclusivePartialDepositPercent: exclusivePartialDepositPercent ?? this.exclusivePartialDepositPercent,
      merchantAllowBankak: merchantAllowBankak ?? this.merchantAllowBankak,
      merchantAllowOcash: merchantAllowOcash ?? this.merchantAllowOcash,
      merchantAllowCod: merchantAllowCod ?? this.merchantAllowCod,
      merchantMaxCod: merchantMaxCod ?? this.merchantMaxCod,
      merchantAllowWire: merchantAllowWire ?? this.merchantAllowWire,
      merchantAllowB2b: merchantAllowB2b ?? this.merchantAllowB2b,
      merchantAllowPartialCod: merchantAllowPartialCod ?? this.merchantAllowPartialCod,
      baseDeliveryFee: baseDeliveryFee ?? this.baseDeliveryFee,
      deliveryPerKm: deliveryPerKm ?? this.deliveryPerKm,
      freeDeliveryThreshold: freeDeliveryThreshold ?? this.freeDeliveryThreshold,
      enableMerchantUpgrade: enableMerchantUpgrade ?? this.enableMerchantUpgrade,
      platformUsdRate: platformUsdRate ?? this.platformUsdRate,
      measurementUnits: measurementUnits ?? this.measurementUnits,
      hiddenCategoryIds: hiddenCategoryIds ?? this.hiddenCategoryIds,
      hiddenSubCategoryIds: hiddenSubCategoryIds ?? this.hiddenSubCategoryIds,
    );
  }
}

class GlobalSettingsNotifier extends StateNotifier<GlobalSettings> {
  RealtimeChannel? _channel;

  GlobalSettingsNotifier() : super(GlobalSettings()) {
    _loadSettings();
    _subscribeRealtime();
  }

  void _subscribeRealtime() {
    try {
      _channel = Supabase.instance.client
          .channel('public:global_settings_sync')
          .onPostgresChanges(
            event: PostgresChangeEvent.all,
            schema: 'public',
            table: 'users',
            filter: PostgresChangeFilter(
              type: PostgresChangeFilterType.eq,
              column: 'role',
              value: 'admin',
            ),
            callback: (payload) {
              _loadSettings();
            },
          )
          .subscribe();
    } catch (_) {}
  }

  @override
  void dispose() {
    _channel?.unsubscribe();
    super.dispose();
  }

  Future<void> _loadSettings() async {
    try {
      // Read platform settings from admin user's permissions
      Map<String, dynamic>? perms;
      final res = await Supabase.instance.client
          .from('users')
          .select('permissions')
          .eq('role', 'admin')
          .limit(1)
          .maybeSingle();
      if (res != null && res['permissions'] is Map) {
        perms = res['permissions'] as Map<String, dynamic>;
      }

      if (perms != null) {
        final ps = perms['platform_settings'] is Map ? perms['platform_settings'] as Map<String, dynamic> : <String, dynamic>{};
        
        final vatStr = perms['vat']?.toString() ?? ps['vat']?.toString() ?? '0';
        final vatDouble = double.tryParse(vatStr) ?? 0.0;
        
        final isEnabled = perms['is_tax_and_commission_enabled'] ?? ps['isTaxAndCommissionEnabled'] ?? ps['is_tax_and_commission_enabled'] ?? false;
        
        // Exclusive Magaiees Payments
        final excBankak = perms['exclusive_bankak'] ?? ps['exclusiveBankak'] ?? perms['is_bankak_enabled'] ?? ps['enableBankak'] ?? true;
        final excBankakAcc = ps['exclusiveBankakAccount']?.toString() ?? ps['bankakAccount']?.toString() ?? '1984256';
        final excBankakName = ps['exclusiveBankakName']?.toString() ?? ps['bankakName']?.toString() ?? 'شركة مقاييس للتوريدات الهندسية';
        final excBankakIban = ps['exclusiveBankakIban']?.toString() ?? ps['bankakIban']?.toString() ?? 'SD84BOKH0000000001984256';

        final excOcash = perms['exclusive_ocash'] ?? ps['exclusiveOcash'] ?? perms['is_ocash_enabled'] ?? ps['enableOcash'] ?? true;
        final excOcashAcc = ps['exclusiveOcashAccount']?.toString() ?? ps['ocashAccount']?.toString() ?? '0912300105';
        final excOcashName = ps['exclusiveOcashName']?.toString() ?? ps['ocashName']?.toString() ?? 'محفظة مقاييس - أوكاش';

        final excCod = perms['exclusive_cod'] ?? ps['exclusiveCod'] ?? perms['is_cod_enabled'] ?? ps['enableCash'] ?? true;
        final excMaxCod = double.tryParse(ps['exclusiveMaxCod']?.toString() ?? ps['maxCodAmount']?.toString() ?? '5000000') ?? 5000000.0;

        final excWire = perms['exclusive_wire'] ?? ps['exclusiveWire'] ?? perms['is_wire_enabled'] ?? ps['enableDirectWire'] ?? true;
        final excWireBank = ps['exclusiveWireBank']?.toString() ?? ps['wireBankName']?.toString() ?? 'بنك فيصل الإسلامي';
        final excWireAcc = ps['exclusiveWireAccount']?.toString() ?? ps['wireAccount']?.toString() ?? '12345678';
        final excWireName = ps['exclusiveWireName']?.toString() ?? ps['wireAccountName']?.toString() ?? 'شركة مقاييس للتوريدات الهندسية';

        final excFawry = perms['exclusive_fawry'] ?? ps['exclusiveFawry'] ?? perms['enable_fawry'] ?? ps['enableFawry'] ?? true;
        final excMaxFawry = double.tryParse(ps['exclusiveMaxFawry']?.toString() ?? ps['maxElectronicPayAmount']?.toString() ?? '10000000') ?? 10000000.0;

        final excB2b = perms['exclusive_b2b'] ?? ps['exclusiveB2b'] ?? perms['enable_b2b_credit'] ?? ps['enableB2bCredit'] ?? true;
        final excPartialCod = perms['exclusive_partial_cod'] ?? ps['exclusivePartialCod'] ?? true;
        final excPartialDeposit = double.tryParse(ps['exclusivePartialDepositPercent']?.toString() ?? ps['exclusivePartialMinPercent']?.toString() ?? '30') ?? 30.0;

        // Marketplace Merchants Payments (Allowed options for merchants)
        final merchBankak = perms['merchant_allow_bankak'] ?? ps['merchantAllowBankak'] ?? true;
        final merchOcash = perms['merchant_allow_ocash'] ?? ps['merchantAllowOcash'] ?? true;
        final merchCod = perms['merchant_allow_cod'] ?? ps['merchantAllowCod'] ?? true;
        final merchMaxCod = double.tryParse(ps['merchantMaxCod']?.toString() ?? '3000000') ?? 3000000.0;
        final merchWire = perms['merchant_allow_wire'] ?? ps['merchantAllowWire'] ?? true;
        final merchB2b = perms['merchant_allow_b2b'] ?? ps['merchantAllowB2b'] ?? true;
        final merchPartialCod = perms['merchant_allow_partial_cod'] ?? ps['merchantAllowPartialCod'] ?? true;

        // Logistics
        final baseDelivery = double.tryParse(ps['baseDeliveryFee']?.toString() ?? '5000') ?? 5000.0;
        final perKm = double.tryParse(ps['deliveryPerKm']?.toString() ?? '350') ?? 350.0;
        final freeThreshold = double.tryParse(ps['freeDeliveryThreshold']?.toString() ?? '2000000') ?? 2000000.0;
        
        bool enableUpgrade = false;
        if (perms.containsKey('enable_merchant_upgrade')) {
          final val = perms['enable_merchant_upgrade'];
          enableUpgrade = val == true || val == 'true';
        } else if (ps.containsKey('enableMerchantUpgrade')) {
          final val = ps['enableMerchantUpgrade'];
          enableUpgrade = val == true || val == 'true';
        } else if (ps.containsKey('enable_merchant_upgrade')) {
          final val = ps['enable_merchant_upgrade'];
          enableUpgrade = val == true || val == 'true';
        }

        double platformUsd = 2800.0;
        if (perms.containsKey('platform_usd_rate')) {
          platformUsd = double.tryParse(perms['platform_usd_rate']?.toString() ?? '') ?? 2800.0;
        } else if (ps.containsKey('platformUsdRate')) {
          platformUsd = double.tryParse(ps['platformUsdRate']?.toString() ?? '') ?? 2800.0;
        } else if (ps.containsKey('platform_usd_rate')) {
          platformUsd = double.tryParse(ps['platform_usd_rate']?.toString() ?? '') ?? 2800.0;
        }

        List<MeasurementUnit> units = MeasurementUnit.defaultUnits;
        dynamic rawUnits = perms['measurement_units'] ?? ps['measurement_units'];
        if (rawUnits is List && rawUnits.isNotEmpty) {
          units = rawUnits.map((u) {
            if (u is Map<String, dynamic>) {
              return MeasurementUnit.fromJson(u);
            } else if (u is Map) {
              return MeasurementUnit.fromJson(Map<String, dynamic>.from(u));
            }
            return MeasurementUnit(id: u.toString(), nameAr: u.toString(), nameEn: u.toString());
          }).toList();
        }

        List<String> hiddenCats = [];
        dynamic rawHiddenCats = perms['hidden_category_ids'] ?? ps['hidden_category_ids'] ?? ps['hiddenCategoryIds'];
        if (rawHiddenCats is List) {
          hiddenCats = rawHiddenCats.map((e) => e.toString()).toList();
        }

        List<String> hiddenSubs = [];
        dynamic rawHiddenSubs = perms['hidden_subcategory_ids'] ?? ps['hidden_subcategory_ids'] ?? ps['hiddenSubCategoryIds'];
        if (rawHiddenSubs is List) {
          hiddenSubs = rawHiddenSubs.map((e) => e.toString()).toList();
        }

        state = GlobalSettings(
          vatPercent: vatDouble,
          isTaxAndCommissionEnabled: isEnabled == true || isEnabled == 'true',
          exclusiveBankak: excBankak == true || excBankak == 'true',
          exclusiveBankakAccount: excBankakAcc,
          exclusiveBankakName: excBankakName,
          exclusiveBankakIban: excBankakIban,
          exclusiveOcash: excOcash == true || excOcash == 'true',
          exclusiveOcashAccount: excOcashAcc,
          exclusiveOcashName: excOcashName,
          exclusiveCod: excCod == true || excCod == 'true',
          exclusiveMaxCod: excMaxCod,
          exclusiveWire: excWire == true || excWire == 'true',
          exclusiveWireBank: excWireBank,
          exclusiveWireAccount: excWireAcc,
          exclusiveWireName: excWireName,
          exclusiveFawry: excFawry == true || excFawry == 'true',
          exclusiveMaxFawry: excMaxFawry,
          exclusiveB2b: excB2b == true || excB2b == 'true',
          exclusivePartialCod: excPartialCod == true || excPartialCod == 'true',
          exclusivePartialDepositPercent: excPartialDeposit,
          merchantAllowBankak: merchBankak == true || merchBankak == 'true',
          merchantAllowOcash: merchOcash == true || merchOcash == 'true',
          merchantAllowCod: merchCod == true || merchCod == 'true',
          merchantMaxCod: merchMaxCod,
          merchantAllowWire: merchWire == true || merchWire == 'true',
          merchantAllowB2b: merchB2b == true || merchB2b == 'true',
          merchantAllowPartialCod: merchPartialCod == true || merchPartialCod == 'true',
          baseDeliveryFee: baseDelivery,
          deliveryPerKm: perKm,
          freeDeliveryThreshold: freeThreshold,
          enableMerchantUpgrade: enableUpgrade,
          platformUsdRate: platformUsd,
          platformSettings: ps,
          measurementUnits: units,
          hiddenCategoryIds: hiddenCats,
          hiddenSubCategoryIds: hiddenSubs,
        );
      }
    } catch (e) {
      // Ignore errors and keep defaults
    }
  }

  Future<void> toggleCategoryVisibility(String categoryId, bool isVisible) async {
    final updatedHidden = List<String>.from(state.hiddenCategoryIds);
    if (isVisible) {
      updatedHidden.remove(categoryId);
    } else {
      if (!updatedHidden.contains(categoryId)) {
        updatedHidden.add(categoryId);
      }
    }
    state = state.copyWith(hiddenCategoryIds: updatedHidden);
    await _saveHiddenLists(updatedHidden, state.hiddenSubCategoryIds);
  }

  Future<void> toggleSubCategoryVisibility(String subCategoryId, bool isVisible) async {
    final updatedHidden = List<String>.from(state.hiddenSubCategoryIds);
    if (isVisible) {
      updatedHidden.remove(subCategoryId);
    } else {
      if (!updatedHidden.contains(subCategoryId)) {
        updatedHidden.add(subCategoryId);
      }
    }
    state = state.copyWith(hiddenSubCategoryIds: updatedHidden);
    await _saveHiddenLists(state.hiddenCategoryIds, updatedHidden);
  }

  Future<void> _saveHiddenLists(List<String> hiddenCats, List<String> hiddenSubs) async {
    try {
      final res = await Supabase.instance.client
          .from('users')
          .select('id, permissions')
          .eq('role', 'admin')
          .limit(1)
          .maybeSingle();

      final perms = (res != null && res['permissions'] is Map)
          ? Map<String, dynamic>.from(res['permissions'] as Map)
          : <String, dynamic>{};

      perms['hidden_category_ids'] = hiddenCats;
      perms['hidden_subcategory_ids'] = hiddenSubs;

      final ps = perms['platform_settings'] is Map
          ? Map<String, dynamic>.from(perms['platform_settings'] as Map)
          : <String, dynamic>{};
      ps['hidden_category_ids'] = hiddenCats;
      ps['hidden_subcategory_ids'] = hiddenSubs;
      perms['platform_settings'] = ps;

      await Supabase.instance.client.from('users').update({
        'permissions': perms,
      }).eq('role', 'admin');

      state = state.copyWith(
        platformSettings: ps,
        hiddenCategoryIds: hiddenCats,
        hiddenSubCategoryIds: hiddenSubs,
      );
    } catch (_) {
      // Optimistic state update is already set
    }
  }

  Future<void> updateMeasurementUnits(List<MeasurementUnit> units) async {
    try {
      final res = await Supabase.instance.client
          .from('users')
          .select('permissions')
          .eq('role', 'admin')
          .limit(1)
          .maybeSingle();

      final perms = (res != null && res['permissions'] is Map)
          ? Map<String, dynamic>.from(res['permissions'] as Map)
          : <String, dynamic>{};

      final unitsJson = units.map((u) => u.toJson()).toList();
      perms['measurement_units'] = unitsJson;

      final ps = perms['platform_settings'] is Map
          ? Map<String, dynamic>.from(perms['platform_settings'] as Map)
          : <String, dynamic>{};
      ps['measurement_units'] = unitsJson;
      perms['platform_settings'] = ps;

      await Supabase.instance.client.from('users').update({
        'permissions': perms,
      }).eq('role', 'admin');

      state = state.copyWith(
        platformSettings: ps,
        measurementUnits: units,
      );
    } catch (e) {
      // State updated locally even if offline
      state = state.copyWith(
        measurementUnits: units,
      );
    }
  }

  void refresh() {
    _loadSettings();
  }
}

final globalSettingsProvider = StateNotifierProvider<GlobalSettingsNotifier, GlobalSettings>((ref) {
  return GlobalSettingsNotifier();
});
