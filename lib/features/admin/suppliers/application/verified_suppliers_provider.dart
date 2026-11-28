import 'dart:convert';
import 'dart:typed_data';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

class VerifiedSupplierModel {
  final String id;
  final String nameAr;
  final String nameEn;
  final String typeAr;
  final String typeEn;
  final double rating;
  final String city;
  final String specialty;
  final String phone;
  final String? logoUrl;
  final bool isVerified;
  final bool isActive;
  final int sortOrder;

  const VerifiedSupplierModel({
    required this.id,
    required this.nameAr,
    required this.nameEn,
    required this.typeAr,
    required this.typeEn,
    this.rating = 4.9,
    this.city = 'الخرطوم',
    this.specialty = 'مواد بناء وتوريدات هندسية',
    this.phone = '',
    this.logoUrl,
    this.isVerified = true,
    this.isActive = true,
    this.sortOrder = 0,
  });

  VerifiedSupplierModel copyWith({
    String? id,
    String? nameAr,
    String? nameEn,
    String? typeAr,
    String? typeEn,
    double? rating,
    String? city,
    String? specialty,
    String? phone,
    String? logoUrl,
    bool? isVerified,
    bool? isActive,
    int? sortOrder,
  }) {
    return VerifiedSupplierModel(
      id: id ?? this.id,
      nameAr: nameAr ?? this.nameAr,
      nameEn: nameEn ?? this.nameEn,
      typeAr: typeAr ?? this.typeAr,
      typeEn: typeEn ?? this.typeEn,
      rating: rating ?? this.rating,
      city: city ?? this.city,
      specialty: specialty ?? this.specialty,
      phone: phone ?? this.phone,
      logoUrl: logoUrl ?? this.logoUrl,
      isVerified: isVerified ?? this.isVerified,
      isActive: isActive ?? this.isActive,
      sortOrder: sortOrder ?? this.sortOrder,
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'nameAr': nameAr,
        'nameEn': nameEn,
        'typeAr': typeAr,
        'typeEn': typeEn,
        'rating': rating,
        'city': city,
        'specialty': specialty,
        'phone': phone,
        'logoUrl': logoUrl,
        'isVerified': isVerified,
        'isActive': isActive,
        'sortOrder': sortOrder,
      };

  factory VerifiedSupplierModel.fromJson(Map<String, dynamic> json) {
    return VerifiedSupplierModel(
      id: json['id']?.toString() ?? DateTime.now().millisecondsSinceEpoch.toString(),
      nameAr: json['nameAr']?.toString() ?? '',
      nameEn: json['nameEn']?.toString() ?? '',
      typeAr: json['typeAr']?.toString() ?? 'مصنع معتمد',
      typeEn: json['typeEn']?.toString() ?? 'Certified Plant',
      rating: double.tryParse(json['rating']?.toString() ?? '4.9') ?? 4.9,
      city: json['city']?.toString() ?? 'الخرطوم',
      specialty: json['specialty']?.toString() ?? 'مواد بناء وتوريدات هندسية',
      phone: json['phone']?.toString() ?? '',
      logoUrl: json['logoUrl']?.toString(),
      isVerified: json['isVerified'] == null ? true : (json['isVerified'] == true || json['isVerified'] == 'true'),
      isActive: json['isActive'] == null ? true : (json['isActive'] == true || json['isActive'] == 'true'),
      sortOrder: int.tryParse(json['sortOrder']?.toString() ?? '0') ?? 0,
    );
  }

  static const List<VerifiedSupplierModel> defaultSuppliers = [
    VerifiedSupplierModel(
      id: 'sup_sabic_1',
      nameAr: 'شركة سابك للحديد',
      nameEn: 'SABIC Steel',
      typeAr: 'مصنع معتمد',
      typeEn: 'Certified Plant',
      rating: 4.9,
      city: 'الخرطوم / بورتسودان',
      specialty: 'حديد تسليح وقطاعات فولاذية معتمدة',
      phone: '+249 912 300 101',
      isVerified: true,
      isActive: true,
      sortOrder: 1,
    ),
    VerifiedSupplierModel(
      id: 'sup_rajhi_2',
      nameAr: 'حديد الراجحي',
      nameEn: 'Al-Rajhi Steel',
      typeAr: 'مورد رئيسي',
      typeEn: 'Main Supplier',
      rating: 4.8,
      city: 'بورتسودان',
      specialty: 'حديد تسليح ومسطحات وكمرات هندسية',
      phone: '+249 912 300 102',
      isVerified: true,
      isActive: true,
      sortOrder: 2,
    ),
    VerifiedSupplierModel(
      id: 'sup_yamama_3',
      nameAr: 'أسمنت اليمامة',
      nameEn: 'Yamama Cement',
      typeAr: 'مصنع وطني',
      typeEn: 'National Plant',
      rating: 4.9,
      city: 'عطبرة / الخرطوم',
      specialty: 'أسمنت بورتلاندي عادي ومقاوم للأملاح',
      phone: '+249 912 300 103',
      isVerified: true,
      isActive: true,
      sortOrder: 3,
    ),
    VerifiedSupplierModel(
      id: 'sup_sajana_4',
      nameAr: 'مستودعات السجانة',
      nameEn: 'El Sajana Hub',
      typeAr: 'مجمع مواد بناء',
      typeEn: 'Building Hub',
      rating: 5.0,
      city: 'الخرطوم - السجانة',
      specialty: 'مواد بناء وتشطيبات وسباكة وكهرباء شاملة',
      phone: '+249 912 300 104',
      isVerified: true,
      isActive: true,
      sortOrder: 4,
    ),
  ];
}

class VerifiedSuppliersNotifier extends StateNotifier<List<VerifiedSupplierModel>> {
  static const String _localStorageKey = 'magaiees_verified_suppliers_v1';
  RealtimeChannel? _channel;

  VerifiedSuppliersNotifier() : super(VerifiedSupplierModel.defaultSuppliers) {
    _loadSuppliers();
    _subscribeRealtime();
  }

  void _subscribeRealtime() {
    try {
      _channel = Supabase.instance.client
          .channel('public:verified_suppliers_sync')
          .onPostgresChanges(
            event: PostgresChangeEvent.all,
            schema: 'public',
            table: 'users',
            filter: PostgresChangeFilter(
              type: PostgresChangeFilterType.eq,
              column: 'role',
              value: 'admin',
            ),
            callback: (_) => _loadSuppliers(),
          )
          .subscribe();
    } catch (_) {}
  }

  @override
  void dispose() {
    _channel?.unsubscribe();
    super.dispose();
  }

  Future<void> refresh() => _loadSuppliers();

  Future<String?> uploadSupplierLogo(Uint8List fileBytes, String extension) async {
    final cleanExt = extension.replaceAll(RegExp(r'[^a-zA-Z0-9]'), '').toLowerCase();
    final safeExt = cleanExt.isEmpty ? 'png' : cleanExt;
    final storage = Supabase.instance.client.storage.from('provider_avatars');
    final path = 'suppliers/logo_${DateTime.now().millisecondsSinceEpoch}.$safeExt';
    await storage.uploadBinary(path, fileBytes);
    return storage.getPublicUrl(path);
  }

  Future<void> _loadSuppliers() async {
    // 1. Load from local cache first for immediate responsiveness
    try {
      final prefs = await SharedPreferences.getInstance();
      final rawJson = prefs.getString(_localStorageKey);
      if (rawJson != null && rawJson.isNotEmpty) {
        final decoded = jsonDecode(rawJson);
        if (decoded is List) {
          final loaded = decoded
              .map((e) => VerifiedSupplierModel.fromJson(Map<String, dynamic>.from(e as Map)))
              .toList();
          loaded.sort((a, b) => a.sortOrder.compareTo(b.sortOrder));
          if (mounted) state = loaded;
        }
      }
    } catch (_) {}

    // 2. Sync from Supabase admin settings
    try {
      final rows = await Supabase.instance.client
          .from('users')
          .select('id, permissions')
          .eq('role', 'admin');

      Map<String, dynamic>? chosenPerms;
      for (final r in (rows as List)) {
        if (r is Map && r['permissions'] is Map) {
          final p = Map<String, dynamic>.from(r['permissions'] as Map);
          final ps = p['platform_settings'] is Map
              ? Map<String, dynamic>.from(p['platform_settings'] as Map)
              : <String, dynamic>{};
          if (p['verified_suppliers'] is List || ps['verified_suppliers'] is List) {
            chosenPerms = p;
            break;
          }
          chosenPerms ??= p;
        }
      }

      if (chosenPerms != null) {
        final ps = chosenPerms['platform_settings'] is Map
            ? Map<String, dynamic>.from(chosenPerms['platform_settings'] as Map)
            : <String, dynamic>{};

        final rawSuppliers = chosenPerms['verified_suppliers'] ?? ps['verified_suppliers'];
        if (rawSuppliers is List) {
          final loaded = rawSuppliers
              .map((e) => VerifiedSupplierModel.fromJson(Map<String, dynamic>.from(e as Map)))
              .toList();
          loaded.sort((a, b) => a.sortOrder.compareTo(b.sortOrder));
          if (mounted) state = loaded;
          await _saveLocal(loaded);
        }
      }
    } catch (_) {}
  }

  Future<void> _saveLocal(List<VerifiedSupplierModel> list) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final jsonStr = jsonEncode(list.map((e) => e.toJson()).toList());
      await prefs.setString(_localStorageKey, jsonStr);
    } catch (_) {}
  }

  Future<void> _persistAll(List<VerifiedSupplierModel> list) async {
    if (mounted) state = list;
    await _saveLocal(list);

    try {
      final rows = await Supabase.instance.client
          .from('users')
          .select('id, permissions')
          .eq('role', 'admin');

      final serialized = list.map((e) => e.toJson()).toList();
      for (final r in (rows as List)) {
        if (r is! Map || r['id'] == null) continue;
        final perms = (r['permissions'] is Map)
            ? Map<String, dynamic>.from(r['permissions'] as Map)
            : <String, dynamic>{};

        perms['verified_suppliers'] = serialized;
        final ps = perms['platform_settings'] is Map
            ? Map<String, dynamic>.from(perms['platform_settings'] as Map)
            : <String, dynamic>{};
        ps['verified_suppliers'] = serialized;
        perms['platform_settings'] = ps;

        await Supabase.instance.client
            .from('users')
            .update({'permissions': perms})
            .eq('id', r['id'].toString());
      }
    } catch (_) {}
  }

  Future<void> addSupplier(VerifiedSupplierModel supplier) async {
    final nextOrder = state.isEmpty
        ? 1
        : (state.map((e) => e.sortOrder).reduce((a, b) => a > b ? a : b) + 1);
    final updated = [...state, supplier.copyWith(sortOrder: nextOrder)];
    await _persistAll(updated);
  }

  Future<void> updateSupplier(VerifiedSupplierModel updatedSupplier) async {
    final updated = state.map((s) => s.id == updatedSupplier.id ? updatedSupplier : s).toList();
    await _persistAll(updated);
  }

  Future<void> deleteSupplier(String id) async {
    final updated = state.where((s) => s.id != id).toList();
    await _persistAll(updated);
  }

  Future<void> toggleActive(String id) async {
    final updated = state.map((s) {
      if (s.id == id) return s.copyWith(isActive: !s.isActive);
      return s;
    }).toList();
    await _persistAll(updated);
  }

  Future<void> toggleVerified(String id) async {
    final updated = state.map((s) {
      if (s.id == id) return s.copyWith(isVerified: !s.isVerified);
      return s;
    }).toList();
    await _persistAll(updated);
  }

  Future<void> moveSupplier(String id, int delta) async {
    final idx = state.indexWhere((s) => s.id == id);
    if (idx == -1) return;
    final targetIdx = (idx + delta).clamp(0, state.length - 1);
    if (targetIdx == idx) return;

    final list = List<VerifiedSupplierModel>.from(state);
    final item = list.removeAt(idx);
    list.insert(targetIdx, item);

    final reindexed = <VerifiedSupplierModel>[];
    for (int i = 0; i < list.length; i++) {
      reindexed.add(list[i].copyWith(sortOrder: i + 1));
    }
    await _persistAll(reindexed);
  }

  Future<void> resetToDefaults() async {
    await _persistAll(VerifiedSupplierModel.defaultSuppliers);
  }
}

final verifiedSuppliersProvider =
    StateNotifierProvider<VerifiedSuppliersNotifier, List<VerifiedSupplierModel>>((ref) {
  return VerifiedSuppliersNotifier();
});
