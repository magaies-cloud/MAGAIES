import 'dart:async';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

class NotificationsNotifier extends StateNotifier<AsyncValue<List<Map<String, dynamic>>>> {
  NotificationsNotifier() : super(const AsyncValue.loading()) {
    _init();
  }

  RealtimeChannel? _channel;
  Timer? _pollTimer;

  void _init() {
    final user = Supabase.instance.client.auth.currentUser;
    if (user == null) {
      state = const AsyncValue.data([]);
      return;
    }

    _fetchNotifications(user.id);

    try {
      _channel = Supabase.instance.client
          .channel('user_notifs_${user.id}_${DateTime.now().millisecondsSinceEpoch}')
          .onPostgresChanges(
            event: PostgresChangeEvent.all,
            schema: 'public',
            table: 'notifications',
            filter: PostgresChangeFilter(
              type: PostgresChangeFilterType.eq,
              column: 'user_id',
              value: user.id,
            ),
            callback: (_) {
              _fetchNotifications(user.id);
            },
          )
          .subscribe();
    } catch (_) {}

    _pollTimer = Timer.periodic(const Duration(seconds: 15), (_) {
      _fetchNotifications(user.id);
    });
  }

  Future<void> _fetchNotifications(String userId) async {
    if (!mounted) return;
    try {
      final data = await Supabase.instance.client
          .from('notifications')
          .select()
          .eq('user_id', userId)
          .order('created_at', ascending: false)
          .timeout(const Duration(seconds: 10));
      if (mounted) {
        state = AsyncValue.data(List<Map<String, dynamic>>.from(data as List));
      }
    } catch (_) {
      if (mounted && !state.hasValue) {
        state = const AsyncValue.data([]);
      }
    }
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    if (_channel != null) {
      try {
        _channel!.unsubscribe();
        Supabase.instance.client.removeChannel(_channel!);
      } catch (_) {}
    }
    super.dispose();
  }

  Future<void> markAsRead(String notificationId) async {
    try {
      await Supabase.instance.client
          .from('notifications')
          .update({'is_read': true})
          .eq('id', notificationId);
      final user = Supabase.instance.client.auth.currentUser;
      if (user != null) {
        unawaited(_fetchNotifications(user.id));
      }
    } catch (_) {}
  }

  Future<void> markAllAsRead() async {
    final user = Supabase.instance.client.auth.currentUser;
    if (user == null) return;

    try {
      await Supabase.instance.client
          .from('notifications')
          .update({'is_read': true})
          .eq('user_id', user.id)
          .eq('is_read', false);
      unawaited(_fetchNotifications(user.id));
    } catch (_) {}
  }
}

final notificationsProvider = StateNotifierProvider<NotificationsNotifier, AsyncValue<List<Map<String, dynamic>>>>((ref) {
  return NotificationsNotifier();
});

final unreadNotificationsCountProvider = Provider<int>((ref) {
  final notificationsState = ref.watch(notificationsProvider);

  return notificationsState.maybeWhen(
    data: (notifications) {
      return notifications.where((n) => n['is_read'] != true).length;
    },
    orElse: () => 0,
  );
});
