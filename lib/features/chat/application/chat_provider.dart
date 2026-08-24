import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/chat/domain/models/chat_room_model.dart';
import 'package:magaiees_marketplace/features/chat/domain/models/chat_message_model.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';

final _supabase = Supabase.instance.client;

/// Provider that fetches all chat rooms for the current user (buyer or merchant)
final chatRoomsProvider = FutureProvider.autoDispose<List<ChatRoomModel>>((ref) async {
  final user = ref.watch(authNotifierProvider).valueOrNull;
  if (user == null) return [];

  final userId = user.id;

  // Fetch rooms where user is buyer OR merchant
  final response = await _supabase
      .from('chat_rooms')
      .select()
      .or('buyer_id.eq.$userId,merchant_id.eq.$userId')
      .order('last_message_at', ascending: false);

  final List<dynamic> data = response;
  final rooms = <ChatRoomModel>[];

  for (final json in data) {
    var room = ChatRoomModel.fromJson(json as Map<String, dynamic>);

    // Determine who the "other" user is
    final otherUserId = room.buyerId == userId ? room.merchantId : room.buyerId;

    // Get other user's name
    String? otherName;
    try {
      final userRes = await _supabase
          .from('users')
          .select('name')
          .eq('id', otherUserId)
          .maybeSingle();
      otherName = userRes?['name']?.toString();
    } catch (_) {}

    // Get store name if available
    String? storeName;
    if (room.storeId != null) {
      try {
        final storeRes = await _supabase
            .from('stores')
            .select('name')
            .eq('id', room.storeId!)
            .maybeSingle();
        storeName = storeRes?['name']?.toString();
      } catch (_) {}
    }

    // Count unread messages
    int unread = 0;
    try {
      final unreadRes = await _supabase
          .from('chat_messages')
          .select('id')
          .eq('room_id', room.id)
          .neq('sender_id', userId)
          .eq('is_read', false);
      unread = (unreadRes as List).length;
    } catch (_) {}

    rooms.add(room.copyWith(
      otherUserName: otherName ?? 'مستخدم',
      storeName: storeName,
      unreadCount: unread,
    ));
  }

  return rooms;
});

/// Provider that streams messages for a specific chat room in real-time
final chatMessagesProvider = StreamProvider.autoDispose.family<List<ChatMessageModel>, String>((ref, roomId) {
  return _supabase
      .from('chat_messages')
      .stream(primaryKey: ['id'])
      .eq('room_id', roomId)
      .order('created_at', ascending: true)
      .map((data) => data.map((json) => ChatMessageModel.fromJson(json)).toList());
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
      'last_message': content,
      'last_message_at': DateTime.now().toUtc().toIso8601String(),
    }).eq('id', roomId);
  }

  /// Get or create a chat room between buyer and merchant
  static Future<String> getOrCreateRoom({
    required String buyerId,
    required String merchantId,
    String? storeId,
  }) async {
    // Check if room already exists
    final existing = await _supabase
        .from('chat_rooms')
        .select('id')
        .eq('buyer_id', buyerId)
        .eq('merchant_id', merchantId)
        .maybeSingle();

    if (existing != null) {
      return existing['id'].toString();
    }

    // Create new room
    final newRoom = await _supabase
        .from('chat_rooms')
        .insert({
          'buyer_id': buyerId,
          'merchant_id': merchantId,
          'store_id': storeId,
        })
        .select('id')
        .single();

    return newRoom['id'].toString();
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
    await _supabase
        .from('chat_messages')
        .update({'is_read': true})
        .eq('room_id', roomId)
        .neq('sender_id', currentUserId)
        .eq('is_read', false);
  }
}

/// Global stream provider for tracking total unread messages for the user.
final unreadMessagesCountProvider = StreamProvider.autoDispose<int>((ref) async* {
  final user = ref.watch(authNotifierProvider).valueOrNull;
  if (user == null) {
    yield 0;
    return;
  }

  // Get room IDs the user is part of
  final roomsResponse = await _supabase
      .from('chat_rooms')
      .select('id')
      .or('buyer_id.eq.${user.id},merchant_id.eq.${user.id}');
      
  final List<String> roomIds = (roomsResponse as List).map((r) => r['id'].toString()).toList();
  if (roomIds.isEmpty) {
    yield 0;
    return;
  }

  // Stream messages in those rooms
  // Using .inFilter works perfectly with .stream() in supabase_flutter
  yield* _supabase
      .from('chat_messages')
      .stream(primaryKey: ['id'])
      .inFilter('room_id', roomIds)
      .map((messages) {
        return messages.where((m) => 
          m['sender_id'].toString() != user.id && 
          m['is_read'] == false
        ).length;
      });
});
