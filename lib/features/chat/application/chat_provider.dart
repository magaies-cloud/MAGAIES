import 'dart:async';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/chat/domain/models/chat_room_model.dart';
import 'package:magaiees_marketplace/features/chat/domain/models/chat_message_model.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/core/providers/ugc_moderation_provider.dart';
import 'package:magaiees_marketplace/core/utils/notification_sound_service.dart';
import 'package:magaiees_marketplace/core/utils/name_formatter.dart';

import 'package:magaiees_marketplace/features/auth/domain/models/user_model.dart';

final _supabase = Supabase.instance.client;

/// Notifier that manages chat rooms state with Supabase Realtime synchronization
class ChatRoomsNotifier extends StateNotifier<AsyncValue<List<ChatRoomModel>>> {
  ChatRoomsNotifier(this._userId, {this.storeId, this.blockedEntities = const {}}) : super(const AsyncValue.loading()) {
    if (_userId == null || _userId.isEmpty || _userId == 'guest_user') {
      state = const AsyncValue.data([]);
    } else {
      _fetchRooms();
      _setupRealtimeListener();
    }
  }

  final String? _userId;
  final String? storeId;
  final Set<String> blockedEntities;
  final _supabase = Supabase.instance.client;
  RealtimeChannel? _channel;
  final Set<String> _myRoomIds = {};

  bool _belongsToCurrentUser(Map<String, dynamic> roomRecord) {
    if (_userId == null || _userId.isEmpty) return false;
    final bId = roomRecord['buyer_id']?.toString();
    final mId = roomRecord['merchant_id']?.toString();
    final sId = roomRecord['store_id']?.toString();
    if (bId == _userId || mId == _userId) return true;
    if (storeId != null && storeId!.isNotEmpty && sId == storeId) return true;
    return false;
  }

  void _setupRealtimeListener() {
    if (_userId == null) return;

    _channel = _supabase
        .channel('public:chat_rooms_and_messages:$_userId')
        .onPostgresChanges(
          event: PostgresChangeEvent.all,
          schema: 'public',
          table: 'chat_rooms',
          callback: (payload) {
            final rec = payload.newRecord.isNotEmpty
                ? payload.newRecord
                : payload.oldRecord;
            if (rec.isEmpty ||
                _belongsToCurrentUser(rec) ||
                _myRoomIds.contains(rec['id']?.toString())) {
              _fetchRooms();
            }
          },
        )
        .onPostgresChanges(
          event: PostgresChangeEvent.all,
          schema: 'public',
          table: 'chat_messages',
          callback: (payload) async {
            final newRec = payload.newRecord;
            final oldRec = payload.oldRecord;
            final roomId =
                (newRec['room_id'] ?? oldRec['room_id'])?.toString();
            if (roomId == null || roomId.isEmpty) return;

            // Strict isolation: verify this room belongs to the current user/store
            bool isMyRoom = _myRoomIds.contains(roomId);
            if (!isMyRoom) {
              try {
                final roomCheck = await _supabase
                    .from('chat_rooms')
                    .select('id, buyer_id, merchant_id, store_id')
                    .eq('id', roomId)
                    .maybeSingle();
                if (roomCheck != null && _belongsToCurrentUser(roomCheck)) {
                  isMyRoom = true;
                  _myRoomIds.add(roomId);
                }
              } catch (_) {}
            }

            // Ignore messages belonging to other merchants or other customers
            if (!isMyRoom) return;

            if (payload.eventType == PostgresChangeEvent.insert) {
              final senderId = newRec['sender_id']?.toString();
              if (senderId != null && senderId != _userId) {
                NotificationSoundService.play();
              }
            }
            _fetchRooms();
          },
        )
        .subscribe();
  }

  Future<void> _fetchRooms() async {
    if (_userId == null) {
      if (mounted) {
        state = const AsyncValue.data([]);
      }
      return;
    }

    try {
      // Fetch rooms where user is buyer OR merchant OR matches staff store_id
      final orFilter = (storeId != null && storeId!.isNotEmpty)
          ? 'buyer_id.eq.$_userId,merchant_id.eq.$_userId,store_id.eq.$storeId'
          : 'buyer_id.eq.$_userId,merchant_id.eq.$_userId';

      final response = await _supabase
          .from('chat_rooms')
          .select()
          .or(orFilter)
          .order('last_message_at', ascending: false);

      final List<dynamic> data = response;
      final rooms = <ChatRoomModel>[];

      for (final json in data) {
        var room = ChatRoomModel.fromJson(json as Map<String, dynamic>);

        // Determine who the "other" user is
        final otherUserId = room.buyerId == _userId ? room.merchantId : room.buyerId;
        if (blockedEntities.contains(otherUserId.trim().toLowerCase()) ||
            (room.storeId != null && blockedEntities.contains(room.storeId!.trim().toLowerCase()))) {
          continue;
        }

        // Get other user's name
        String? otherName;
        try {
          final userRes = await _supabase
              .from('users')
              .select('name, name_ar')
              .eq('id', otherUserId)
              .maybeSingle();
          final formatted = NameFormatter.formatName(
            name: userRes?['name']?.toString(),
            nameAr: userRes?['name_ar']?.toString(),
          );
          if (formatted.isNotEmpty) {
            otherName = formatted;
          }
        } catch (_) {}

        // Get store name if available
        String? storeName;
        if (room.storeId != null) {
          try {
            final storeRes = await _supabase
                .from('stores')
                .select('name, name_ar')
                .eq('id', room.storeId!)
                .maybeSingle();
            final formatted = NameFormatter.formatName(
              name: storeRes?['name']?.toString(),
              nameAr: storeRes?['name_ar']?.toString(),
            );
            if (formatted.isNotEmpty) {
              storeName = formatted;
            }
          } catch (_) {}
        }

        // Count unread messages
        int unread = 0;
        try {
          final unreadRes = await _supabase
              .from('chat_messages')
              .select('id')
              .eq('room_id', room.id)
              .neq('sender_id', _userId)
              .eq('is_read', false);
          unread = (unreadRes as List).length;
        } catch (_) {}

        rooms.add(room.copyWith(
          otherUserName: otherName ?? 'مستخدم',
          storeName: storeName,
          unreadCount: unread,
        ));
      }

      _myRoomIds
        ..clear()
        ..addAll(rooms.map((r) => r.id));

      if (mounted) {
        state = AsyncValue.data(rooms);
      }
    } catch (e, st) {
      // Preserve existing loaded rooms on temporary network hiccups
      if (mounted && !state.hasValue) {
        state = AsyncValue.error(e, st);
      }
    }
  }

  /// Manually refresh rooms if needed
  Future<void> refresh() async {
    await _fetchRooms();
  }

  @override
  void dispose() {
    _channel?.unsubscribe();
    super.dispose();
  }
}

/// Provider that fetches and streams all chat rooms for the current user (buyer or merchant)
final chatRoomsProvider = StateNotifierProvider.autoDispose<ChatRoomsNotifier, AsyncValue<List<ChatRoomModel>>>((ref) {
  final user = ref.watch(authNotifierProvider).valueOrNull;
  final blocked = ref.watch(blockedEntitiesProvider);
  final isGuestOrAnon = user == null || user.role.isGuest || user.id == 'guest_user';
  final staffStoreId = (!isGuestOrAnon && user.isStoreStaff) ? user.storeId : null;
  return ChatRoomsNotifier(
    isGuestOrAnon ? null : user.id,
    storeId: staffStoreId,
    blockedEntities: blocked,
  );
});

final Map<String, List<ChatMessageModel>> _roomMessagesCache = {};
final Map<String, Future<void> Function()> _roomRefreshCallbacks = {};

/// Provider that streams messages for a specific chat room in real-time
/// Uses resilient HTTP REST + safe Realtime channel + fallback polling so WebSocket drops
/// (RealtimeSubscribeException / SocketException) never crash or wipe the chat UI.
final chatMessagesProvider = StreamProvider.autoDispose.family<List<ChatMessageModel>, String>((ref, roomId) {
  final controller = StreamController<List<ChatMessageModel>>();
  bool isDisposed = false;
  bool isFetching = false;

  // Emit cached messages immediately if available
  if (_roomMessagesCache.containsKey(roomId)) {
    controller.add(_roomMessagesCache[roomId]!);
  }

  Future<void> fetchMessages() async {
    if (isDisposed || isFetching) return;
    isFetching = true;
    try {
      final data = await _supabase
          .from('chat_messages')
          .select()
          .eq('room_id', roomId)
          .order('created_at', ascending: true)
          .timeout(const Duration(seconds: 10));

      final list = (data as List)
          .map((json) => ChatMessageModel.fromJson(json as Map<String, dynamic>))
          .toList();

      _roomMessagesCache[roomId] = list;
      if (!isDisposed && !controller.isClosed) {
        controller.add(list);
      }
    } catch (_) {
      // On temporary offline/DNS failure, keep cached messages or emit empty list on first load
      // so RealtimeSubscribeException never breaks the screen
      if (!isDisposed && !controller.isClosed && _roomMessagesCache.containsKey(roomId)) {
        controller.add(_roomMessagesCache[roomId]!);
      } else if (!isDisposed && !controller.isClosed) {
        controller.add(const <ChatMessageModel>[]);
      }
    } finally {
      isFetching = false;
    }
  }

  _roomRefreshCallbacks[roomId] = fetchMessages;
  fetchMessages();

  RealtimeChannel? channel;
  try {
    channel = _supabase
        .channel('room_msgs_${roomId}_${DateTime.now().millisecondsSinceEpoch}')
        .onPostgresChanges(
          event: PostgresChangeEvent.all,
          schema: 'public',
          table: 'chat_messages',
          filter: PostgresChangeFilter(
            type: PostgresChangeFilterType.eq,
            column: 'room_id',
            value: roomId,
          ),
          callback: (_) {
            fetchMessages();
          },
        )
        .subscribe();
  } catch (_) {}

  final pollTimer = Timer.periodic(const Duration(seconds: 3), (_) {
    fetchMessages();
  });

  ref.onDispose(() {
    isDisposed = true;
    _roomRefreshCallbacks.remove(roomId);
    pollTimer.cancel();
    if (channel != null) {
      try {
        channel.unsubscribe();
        _supabase.removeChannel(channel);
      } catch (_) {}
    }
    controller.close();
  });

  return controller.stream;
});

/// Service class with static methods for chat operations
class ChatService {
  /// Send a message to a chat room
  static Future<void> sendMessage({
    required String roomId,
    required String senderId,
    required String content,
    String type = 'text',
  }) async {
    // Insert the message
    await _supabase.from('chat_messages').insert({
      'room_id': roomId,
      'sender_id': senderId,
      'content': content,
      'type': type,
    });

    // Update the room's last message
    await _supabase.from('chat_rooms').update({
      'last_message': type == 'image' ? '📷 صورة / Image' : content,
      'last_message_at': DateTime.now().toUtc().toIso8601String(),
    }).eq('id', roomId);

    // Immediately trigger message stream refresh for this room
    final refreshFn = _roomRefreshCallbacks[roomId];
    if (refreshFn != null) {
      unawaited(refreshFn());
    }
  }

  /// Get or create a chat room between buyer and merchant
  static Future<String> getOrCreateRoom({
    required String buyerId,
    required String merchantId,
    String? storeId,
  }) async {
    // 1. Check direct direction: buyer_id = buyerId AND merchant_id = merchantId
    try {
      final existing = await _supabase
          .from('chat_rooms')
          .select('id')
          .eq('buyer_id', buyerId)
          .eq('merchant_id', merchantId)
          .limit(1)
          .maybeSingle();

      if (existing != null) {
        return existing['id'].toString();
      }
    } catch (_) {}

    // 2. Check reverse direction: buyer_id = merchantId AND merchant_id = buyerId
    try {
      final existingReverse = await _supabase
          .from('chat_rooms')
          .select('id')
          .eq('buyer_id', merchantId)
          .eq('merchant_id', buyerId)
          .limit(1)
          .maybeSingle();

      if (existingReverse != null) {
        return existingReverse['id'].toString();
      }
    } catch (_) {}

    // 3. Create new room
    try {
      final newRoom = await _supabase
          .from('chat_rooms')
          .insert({
            'buyer_id': buyerId,
            'merchant_id': merchantId,
            if (storeId != null && storeId.isNotEmpty) 'store_id': storeId,
          })
          .select('id')
          .single();

      return newRoom['id'].toString();
    } catch (insertErr) {
      // If constraint violation occurred (e.g. room exists or created concurrently), fetch again
      try {
        final r1 = await _supabase
            .from('chat_rooms')
            .select('id')
            .eq('buyer_id', buyerId)
            .eq('merchant_id', merchantId)
            .limit(1)
            .maybeSingle();
        if (r1 != null) return r1['id'].toString();

        final r2 = await _supabase
            .from('chat_rooms')
            .select('id')
            .eq('buyer_id', merchantId)
            .eq('merchant_id', buyerId)
            .limit(1)
            .maybeSingle();
        if (r2 != null) return r2['id'].toString();
      } catch (_) {}
      rethrow;
    }
  }

  static Future<void> updateLastSeen(String userId) async {
    try {
      await _supabase
          .from('users')
          .update({'last_seen': DateTime.now().toUtc().toIso8601String()})
          .eq('id', userId);
    } catch (_) {}
  }

  /// Mark all messages in a room as read for the current user
  static Future<void> markAsRead({
    required String roomId,
    required String currentUserId,
  }) async {
    try {
      await _supabase
          .from('chat_messages')
          .update({'is_read': true})
          .eq('room_id', roomId)
          .neq('sender_id', currentUserId)
          .eq('is_read', false);
    } catch (_) {}
  }
}

/// Provider that tracks total unread messages for the user across all chat rooms
final unreadMessagesCountProvider = Provider.autoDispose<AsyncValue<int>>((ref) {
  final roomsAsync = ref.watch(chatRoomsProvider);
  return roomsAsync.whenData(
    (rooms) => rooms.fold<int>(0, (sum, r) => sum + r.unreadCount),
  );
});
