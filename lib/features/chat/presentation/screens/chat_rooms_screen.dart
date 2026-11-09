import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/chat/application/chat_provider.dart';
import 'package:magaiees_marketplace/features/chat/domain/models/chat_room_model.dart';
import 'package:magaiees_marketplace/features/chat/presentation/screens/call_screen.dart';
import 'package:magaiees_marketplace/core/utils/call_ringtone_service.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/core/utils/name_formatter.dart';

class ChatRoomsScreen extends ConsumerStatefulWidget {
  const ChatRoomsScreen({super.key});

  @override
  ConsumerState<ChatRoomsScreen> createState() => _ChatRoomsScreenState();
}

class _ChatRoomsScreenState extends ConsumerState<ChatRoomsScreen> {
  RealtimeChannel? _userCallChannel;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _setupUserCallListener();
    });
  }

  void _setupUserCallListener() {
    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user == null || _userCallChannel != null) return;

    _userCallChannel = Supabase.instance.client.channel('user_call_${user.id}');
    _userCallChannel?.onBroadcast(
      event: 'webrtc_signal',
      callback: (payload) {
        if (!mounted) return;
        final senderId = payload['sender_id'];
        if (senderId == user.id) return;
        final data = payload['data'] as Map<String, dynamic>?;
        if (data != null && data['type'] == 'join') {
          final roomId = data['room_id']?.toString() ?? '';
          final callerName = data['caller_name']?.toString() ?? 'مستخدم';
          if (roomId.isNotEmpty &&
              !CallRingtoneService.isCallActive &&
              WidgetsBinding.instance.lifecycleState == AppLifecycleState.resumed) {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (context) => IncomingCallScreen(
                  roomId: roomId,
                  callerName: callerName,
                  currentUser: user,
                ),
              ),
            ).then((_) {
              CallRingtoneService.stop();
            });
          }
        }
      },
    ).subscribe();
  }

  @override
  void dispose() {
    if (_userCallChannel != null) {
      try {
        _userCallChannel!.unsubscribe();
        Supabase.instance.client.removeChannel(_userCallChannel!);
      } catch (_) {}
      _userCallChannel = null;
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    // Re-verify call listener if user logs in or changes
    final user = ref.watch(authNotifierProvider).valueOrNull;
    if (user != null && _userCallChannel == null) {
      _setupUserCallListener();
    }
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final roomsAsync = ref.watch(chatRoomsProvider);

    return Scaffold(
      appBar: AppBar(
        title: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.chat_bubble_outline, size: 22),
            const SizedBox(width: 8),
            Text(isRtl ? 'المحادثات' : 'Messages'),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(chatRoomsProvider),
            tooltip: isRtl ? 'تحديث' : 'Refresh',
          ),
        ],
      ),
      body: roomsAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.error_outline, size: 48, color: Colors.red.shade300),
              const SizedBox(height: 12),
              Text('Error: $e', textAlign: TextAlign.center),
              const SizedBox(height: 12),
              FilledButton.icon(
                onPressed: () => ref.invalidate(chatRoomsProvider),
                icon: const Icon(Icons.refresh),
                label: Text(isRtl ? 'إعادة المحاولة' : 'Retry'),
              ),
            ],
          ),
        ),
        data: (rooms) {
          if (rooms.isEmpty) {
            return Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.chat_bubble_outline, size: 64, color: Colors.grey.shade300),
                  const SizedBox(height: 16),
                  Text(
                    isRtl ? 'لا توجد محادثات بعد' : 'No conversations yet',
                    style: TextStyle(
                      fontSize: 18,
                      color: Colors.grey.shade500,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    isRtl
                        ? 'ابدأ محادثة من صفحة المنتج بالضغط على "تواصل مع التاجر"'
                        : 'Start a conversation from a product page by tapping "Chat with Seller"',
                    style: TextStyle(fontSize: 14, color: Colors.grey.shade400),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            );
          }

          return ListView.separated(
            padding: const EdgeInsets.symmetric(vertical: 8),
            itemCount: rooms.length,
            separatorBuilder: (_, _) => const Divider(height: 1, indent: 72),
            itemBuilder: (context, index) {
              final room = rooms[index];
              return _ChatRoomTile(room: room, currentUserId: user?.id ?? '', isRtl: isRtl);
            },
          );
        },
      ),
    );
  }
}

class _ChatRoomTile extends StatelessWidget {
  final ChatRoomModel room;
  final String currentUserId;
  final bool isRtl;

  const _ChatRoomTile({required this.room, required this.currentUserId, required this.isRtl});

  @override
  Widget build(BuildContext context) {
    // If the current user is the merchant, show the buyer's name.
    // If the current user is the buyer, show the store's name.
    final bool isMerchant = room.merchantId == currentUserId;
    final String displayName;
    if (isMerchant) {
      final raw = room.otherUserName ?? '';
      final cleaned = NameFormatter.cleanPrefix(raw);
      displayName = cleaned.isNotEmpty ? cleaned : (isRtl ? 'عميل' : 'Customer');
    } else {
      final rawStore = (room.storeName != null && room.storeName!.isNotEmpty)
          ? room.storeName!
          : (room.otherUserName ?? '');
      final cleaned = NameFormatter.cleanPrefix(rawStore);
      displayName = cleaned.isNotEmpty ? cleaned : (isRtl ? 'متجر' : 'Store');
    }
    final timeStr = _formatTime(room.lastMessageAt);
    final hasUnread = room.unreadCount > 0;

    return ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
      leading: CircleAvatar(
        radius: 24,
        backgroundColor: hasUnread ? const Color(0xFF1E40AF) : Colors.grey.shade200,
        child: Text(
          displayName.isNotEmpty ? displayName[0].toUpperCase() : '?',
          style: TextStyle(
            color: hasUnread ? Colors.white : Colors.grey.shade600,
            fontWeight: FontWeight.bold,
            fontSize: 18,
          ),
        ),
      ),
      title: Row(
        children: [
          Expanded(
            child: Text(
              displayName,
              style: TextStyle(
                fontWeight: hasUnread ? FontWeight.w800 : FontWeight.w600,
                fontSize: 15,
              ),
              overflow: TextOverflow.ellipsis,
            ),
          ),
          Text(
            timeStr,
            style: TextStyle(
              fontSize: 12,
              color: hasUnread ? const Color(0xFF1E40AF) : Colors.grey.shade400,
              fontWeight: hasUnread ? FontWeight.bold : FontWeight.normal,
            ),
          ),
        ],
      ),
      subtitle: Row(
        children: [
          Expanded(
            child: Text(
              room.lastMessage ?? (isRtl ? 'محادثة جديدة' : 'New conversation'),
              style: TextStyle(
                color: hasUnread ? Colors.black87 : Colors.grey.shade500,
                fontWeight: hasUnread ? FontWeight.w600 : FontWeight.normal,
                fontSize: 13,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
          if (hasUnread)
            Container(
              margin: const EdgeInsets.only(left: 8),
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
              decoration: BoxDecoration(
                color: const Color(0xFF1E40AF),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(
                room.unreadCount.toString(),
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
        ],
      ),
      onTap: () {
        // Navigate to chat room using relative path
        final currentPath = GoRouterState.of(context).matchedLocation;
        context.go('$currentPath/room/${room.id}');
      },
    );
  }

  String _formatTime(DateTime? dt) {
    if (dt == null) return '';
    final now = DateTime.now();
    final local = dt.toLocal();
    final diff = now.difference(local);

    if (diff.inMinutes < 1) return isRtl ? 'الآن' : 'Now';
    if (diff.inMinutes < 60) return '${diff.inMinutes}m';
    if (diff.inHours < 24) return DateFormat.Hm().format(local);
    if (diff.inDays < 7) return DateFormat.E().format(local);
    return DateFormat.MMMd().format(local);
  }
}
