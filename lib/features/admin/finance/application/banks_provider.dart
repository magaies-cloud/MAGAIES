import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/core/network/supabase_safe_query.dart';

class PlatformBank {
  final String id;
  final String nameAr;
  final String nameEn;
  final String? code;
  final String? accountNumber;
  final String? accountName;
  final bool isActive;
  final int displayOrder;

  PlatformBank({
    required this.id,
    required this.nameAr,
    required this.nameEn,
    this.code,
    this.accountNumber,
    this.accountName,
    this.isActive = true,
    this.displayOrder = 0,
  });

  factory PlatformBank.fromMap(Map<String, dynamic> map) {
    return PlatformBank(
      id: map['id']?.toString() ?? '',
      nameAr: map['name_ar']?.toString() ?? '',
      nameEn: map['name_en']?.toString() ?? '',
      code: map['code']?.toString(),
      accountNumber: map['account_number']?.toString(),
      accountName: map['account_name']?.toString(),
      isActive: map['is_active'] as bool? ?? true,
      displayOrder: (map['display_order'] as num?)?.toInt() ?? int.tryParse(map['display_order']?.toString() ?? '') ?? 0,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'name_ar': nameAr,
      'name_en': nameEn,
      'code': code,
      'account_number': accountNumber,
      'account_name': accountName,
      'is_active': isActive,
      'display_order': displayOrder,
    };
  }

  PlatformBank copyWith({
    String? id,
    String? nameAr,
    String? nameEn,
    String? code,
    String? accountNumber,
    String? accountName,
    bool? isActive,
    int? displayOrder,
  }) {
    return PlatformBank(
      id: id ?? this.id,
      nameAr: nameAr ?? this.nameAr,
      nameEn: nameEn ?? this.nameEn,
      code: code ?? this.code,
      accountNumber: accountNumber ?? this.accountNumber,
      accountName: accountName ?? this.accountName,
      isActive: isActive ?? this.isActive,
      displayOrder: displayOrder ?? this.displayOrder,
    );
  }
}

class BanksNotifier extends StateNotifier<AsyncValue<List<PlatformBank>>> {
  BanksNotifier() : super(const AsyncValue.loading()) {
    loadBanks();
  }

  final _supabase = Supabase.instance.client;

  Future<void> loadBanks() async {
    try {
      state = const AsyncValue.loading();
      final res = await safeSupabaseQuery(
        () => _supabase
            .from('platform_banks')
            .select()
            .order('display_order', ascending: true),
        fallback: () => <Map<String, dynamic>>[],
      );
      
      final List<PlatformBank> list = (res as List)
          .map((m) => PlatformBank.fromMap(m as Map<String, dynamic>))
          .toList();

      state = AsyncValue.data(list);
    } catch (e, st) {
      state = AsyncValue.error(e, st);
    }
  }

  Future<void> addBank({
    required String nameAr,
    required String nameEn,
    String? code,
    String? accountNumber,
    String? accountName,
  }) async {
    final current = state.valueOrNull ?? [];
    final nextOrder = current.isEmpty ? 1 : (current.map((b) => b.displayOrder).reduce((a, b) => a > b ? a : b) + 1);

    final newBankMap = {
      'name_ar': nameAr,
      'name_en': nameEn,
      'code': code,
      'account_number': accountNumber,
      'account_name': accountName,
      'is_active': true,
      'display_order': nextOrder,
    };

    final res = await safeSupabaseQuery(() => _supabase.from('platform_banks').insert(newBankMap).select().single());
    final newBank = PlatformBank.fromMap(res);
    state = AsyncValue.data([...current, newBank]);
  }

  Future<void> updateBank({
    required String id,
    required String nameAr,
    required String nameEn,
    String? code,
    String? accountNumber,
    String? accountName,
    required bool isActive,
  }) async {
    final updateMap = {
      'name_ar': nameAr,
      'name_en': nameEn,
      'code': code,
      'account_number': accountNumber,
      'account_name': accountName,
      'is_active': isActive,
    };

    await safeSupabaseQuery(() => _supabase.from('platform_banks').update(updateMap).eq('id', id));

    final current = state.valueOrNull ?? [];
    state = AsyncValue.data(current.map((b) {
      if (b.id == id) {
        return b.copyWith(
          nameAr: nameAr,
          nameEn: nameEn,
          code: code,
          accountNumber: accountNumber,
          accountName: accountName,
          isActive: isActive,
        );
      }
      return b;
    }).toList());
  }

  Future<void> deleteBank(String id) async {
    await safeSupabaseQuery(() => _supabase.from('platform_banks').delete().eq('id', id));
    final current = state.valueOrNull ?? [];
    state = AsyncValue.data(current.where((b) => b.id != id).toList());
  }

  Future<void> toggleBankActive(String id, bool isActive) async {
    await safeSupabaseQuery(() => _supabase.from('platform_banks').update({'is_active': isActive}).eq('id', id));
    final current = state.valueOrNull ?? [];
    state = AsyncValue.data(current.map((b) => b.id == id ? b.copyWith(isActive: isActive) : b).toList());
  }
}

final banksProvider = StateNotifierProvider<BanksNotifier, AsyncValue<List<PlatformBank>>>((ref) {
  return BanksNotifier();
});
