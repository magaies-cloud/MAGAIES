import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/locale/application/locale_provider.dart';

const _blockedEntitiesKey = 'ugc_blocked_entity_ids';

/// Manages User-Generated Content (UGC) moderation:
/// - Persists blocked seller/store/user IDs locally and syncs reports/blocks to Supabase
/// - Immediately filters blocked sellers' products and blocked chat participants across the UI
///   in full compliance with Apple App Store Guideline 1.2 & Google Play UGC policy.
class BlockedEntitiesNotifier extends StateNotifier<Set<String>> {
  BlockedEntitiesNotifier(this._prefs) : super(_loadInitial(_prefs));

  final SharedPreferences _prefs;

  static Set<String> _loadInitial(SharedPreferences prefs) {
    final list = prefs.getStringList(_blockedEntitiesKey) ?? const <String>[];
    return list.map((e) => e.trim().toLowerCase()).where((e) => e.isNotEmpty).toSet();
  }

  bool isBlocked(String? idOrName) {
    if (idOrName == null || idOrName.trim().isEmpty) return false;
    return state.contains(idOrName.trim().toLowerCase());
  }

  Future<void> blockEntity({
    required String targetId,
    String? secondaryKey,
    String? reason,
  }) async {
    final normalized = targetId.trim().toLowerCase();
    final updated = <String>{...state, if (normalized.isNotEmpty) normalized};
    if (secondaryKey != null && secondaryKey.trim().isNotEmpty) {
      updated.add(secondaryKey.trim().toLowerCase());
    }
    state = updated;
    await _prefs.setStringList(_blockedEntitiesKey, updated.toList());

    // Best-effort log to Supabase user permissions/metadata or notifications for admin moderation
    try {
      final client = Supabase.instance.client;
      final uid = client.auth.currentUser?.id;
      if (uid != null) {
        await client.from('notifications').insert({
          'user_id': uid,
          'title': 'UGC Block Action',
          'body': 'Blocked entity: $targetId ${reason != null ? "($reason)" : ""}',
          'type': 'moderation_block',
          'is_read': true,
        });
      }
    } catch (e) {
      debugPrint('Moderation block sync note: $e');
    }
  }

  Future<void> unblockEntity(String targetId) async {
    final normalized = targetId.trim().toLowerCase();
    final updated = <String>{...state}..remove(normalized);
    state = updated;
    await _prefs.setStringList(_blockedEntitiesKey, updated.toList());
  }

  Future<void> submitReport({
    required String targetType,
    required String targetId,
    required String reason,
  }) async {
    try {
      final client = Supabase.instance.client;
      final uid = client.auth.currentUser?.id;
      await client.from('notifications').insert({
        if (uid != null) 'user_id': uid,
        'title': 'بلاغ محتوى ($targetType)',
        'body': 'المعرف: $targetId — السبب: $reason',
        'type': 'moderation_report',
        'is_read': false,
      });
    } catch (e) {
      debugPrint('Moderation report sync note: $e');
    }
  }
}

final blockedEntitiesProvider =
    StateNotifierProvider<BlockedEntitiesNotifier, Set<String>>((ref) {
  final prefs = ref.watch(sharedPreferencesProvider);
  return BlockedEntitiesNotifier(prefs);
});
