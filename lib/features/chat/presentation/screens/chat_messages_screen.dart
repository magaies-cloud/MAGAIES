import 'package:go_router/go_router.dart';
import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/features/chat/application/chat_provider.dart';
import 'package:magaiees_marketplace/features/chat/domain/models/chat_message_model.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:audioplayers/audioplayers.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ChatMessagesScreen extends ConsumerStatefulWidget {
  final String roomId;
  const ChatMessagesScreen({super.key, required this.roomId});

  @override
  ConsumerState<ChatMessagesScreen> createState() => _ChatMessagesScreenState();
}

class _ChatMessagesScreenState extends ConsumerState<ChatMessagesScreen> {
  final _messageController = TextEditingController();
  final _scrollController = ScrollController();
  bool _isSending = false;
  
  Timer? _presenceTimer;
  String? _otherUserName;
  DateTime? _lastSeen;
  bool _isOnline = false;

  StreamSubscription? _presenceSubscription;
  final AudioPlayer _audioPlayer = AudioPlayer();
  int _lastMessageCount = 0;
  bool _soundsEnabled = true;

  @override
  void initState() {
    super.initState();
    SharedPreferences.getInstance().then((prefs) {
      _soundsEnabled = prefs.getBool('chat_sounds') ?? true;
    });

    WidgetsBinding.instance.addPostFrameCallback((_) {
      _updatePresence();
      _setupPresenceStream();
      
      _presenceTimer = Timer.periodic(const Duration(seconds: 15), (_) {
        _updatePresence();
        // Force a re-evaluation of _isOnline locally even if no stream event comes
        if (_lastSeen != null && mounted) {
          setState(() {
            _isOnline = DateTime.now().toUtc().difference(_lastSeen!).inMinutes < 2;
          });
        }
      });
    });
  }
  
  Future<void> _updatePresence() async {
    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user != null) {
      ChatService.markAsRead(roomId: widget.roomId, currentUserId: user.id);
      ChatService.updateLastSeen(user.id);
    }
  }

  Future<void> _setupPresenceStream() async {
    try {
      final user = ref.read(authNotifierProvider).valueOrNull;
      if (user == null) return;
      
      final roomRes = await Supabase.instance.client
          .from('chat_rooms')
          .select()
          .eq('id', widget.roomId)
          .maybeSingle();
      if (roomRes == null) return;
      
      final otherId = roomRes['buyer_id'] == user.id ? roomRes['merchant_id'] : roomRes['buyer_id'];
      
      _presenceSubscription = Supabase.instance.client
          .from('users')
          .stream(primaryKey: ['id'])
          .eq('id', otherId)
          .listen((data) {
        if (data.isNotEmpty && mounted) {
          final userRes = data.first;
          setState(() {
            _otherUserName = userRes['name'];
            if (userRes['last_seen'] != null) {
              _lastSeen = DateTime.tryParse(userRes['last_seen'].toString());
              if (_lastSeen != null) {
                _isOnline = DateTime.now().toUtc().difference(_lastSeen!).inMinutes < 2;
              }
            }
          });
        }
      });
    } catch (_) {}
  }

  @override
  void dispose() {
    _presenceSubscription?.cancel();
    _presenceTimer?.cancel();
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _scrollToBottom() {
    if (_scrollController.hasClients) {
      Future.delayed(const Duration(milliseconds: 100), () {
        if (_scrollController.hasClients) {
          _scrollController.animateTo(
            _scrollController.position.maxScrollExtent,
            duration: const Duration(milliseconds: 300),
            curve: Curves.easeOut,
          );
        }
      });
    }
  }

  Future<void> _sendMessage() async {
    final text = _messageController.text.trim();
    if (text.isEmpty || _isSending) return;

    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user == null) return;

    setState(() => _isSending = true);
    _messageController.clear();

    try {
      await ChatService.sendMessage(
        roomId: widget.roomId,
        senderId: user.id,
        content: text,
      );
      _scrollToBottom();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')),
        );
        _messageController.text = text; // Restore the text on failure
      }
    } finally {
      if (mounted) {
        setState(() => _isSending = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final user = ref.watch(authNotifierProvider).valueOrNull;
    final messagesAsync = ref.watch(chatMessagesProvider(widget.roomId));

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(_otherUserName ?? (isRtl ? 'المحادثة' : 'Conversation'), style: const TextStyle(fontSize: 16)),
            if (_otherUserName != null)
              Row(
                children: [
                  Container(
                    width: 8, height: 8,
                    decoration: BoxDecoration(
                      color: _isOnline ? Colors.green : Colors.grey,
                      shape: BoxShape.circle,
                    ),
                  ),
                  const SizedBox(width: 4),
                  Text(
                    _isOnline 
                      ? (isRtl ? 'متصل الآن' : 'Online') 
                      : (isRtl ? 'آخر ظهور: ${_lastSeen != null ? DateFormat('HH:mm').format(_lastSeen!.toLocal()) : ''}' : 'Last seen: ${_lastSeen != null ? DateFormat('HH:mm').format(_lastSeen!.toLocal()) : ''}'),
                    style: const TextStyle(fontSize: 12, fontWeight: FontWeight.normal),
                  ),
                ],
              ),
          ],
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      body: Column(
        children: [
          // Messages list
          Expanded(
            child: messagesAsync.when(
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (e, _) => Center(child: Text('Error: $e')),
              data: (messages) {
                // Play sound and auto-scroll when new messages arrive
                if (messages.length > _lastMessageCount) {
                  final newMsg = messages.last;
                  final isMine = user != null && newMsg.isMine(user.id);
                  if (!isMine && _soundsEnabled && _lastMessageCount > 0) {
                    _audioPlayer.play(AssetSource('audio/magaiees_notify.ogg'));
                  }
                  _lastMessageCount = messages.length;
                  WidgetsBinding.instance.addPostFrameCallback((_) => _scrollToBottom());
                }

                if (messages.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.waving_hand, size: 48, color: Colors.amber.shade300),
                        const SizedBox(height: 12),
                        Text(
                          isRtl ? 'ابدأ المحادثة الآن!' : 'Start the conversation!',
                          style: TextStyle(fontSize: 16, color: Colors.grey.shade500),
                        ),
                      ],
                    ),
                  );
                }

                return ListView.builder(
                  controller: _scrollController,
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  itemCount: messages.length,
                  itemBuilder: (context, index) {
                    final msg = messages[index];
                    final isMine = user != null && msg.isMine(user.id);
                    final showDate = index == 0 ||
                        _isDifferentDay(messages[index - 1].createdAt, msg.createdAt);

                    return Column(
                      children: [
                        if (showDate)
                          _DateSeparator(date: msg.createdAt, isRtl: isRtl),
                        _MessageBubble(message: msg, isMine: isMine, isRtl: isRtl),
                      ],
                    );
                  },
                );
              },
            ),
          ),

          // Input bar
          Container(
            decoration: BoxDecoration(
              color: Colors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.05),
                  blurRadius: 10,
                  offset: const Offset(0, -2),
                ),
              ],
            ),
            padding: EdgeInsets.only(
              left: 12,
              right: 8,
              top: 8,
              bottom: MediaQuery.of(context).padding.bottom + 8,
            ),
            child: Row(
              children: [
                Expanded(
                  child: Container(
                    decoration: BoxDecoration(
                      color: Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(24),
                    ),
                    child: TextField(
                      controller: _messageController,
                      textInputAction: TextInputAction.send,
                      onSubmitted: (_) => _sendMessage(),
                      maxLines: 4,
                      minLines: 1,
                      decoration: InputDecoration(
                        hintText: isRtl ? 'اكتب رسالتك...' : 'Type a message...',
                        border: InputBorder.none,
                        contentPadding: const EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 10,
                        ),
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Container(
                  decoration: const BoxDecoration(
                    color: Color(0xFF1E40AF),
                    shape: BoxShape.circle,
                  ),
                  child: IconButton(
                    onPressed: _isSending ? null : _sendMessage,
                    icon: _isSending
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.white,
                            ),
                          )
                        : Icon(
                            isRtl ? Icons.send : Icons.send,
                            color: Colors.white,
                            size: 20,
                          ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  bool _isDifferentDay(DateTime? a, DateTime? b) {
    if (a == null || b == null) return true;
    return a.year != b.year || a.month != b.month || a.day != b.day;
  }
}

class _DateSeparator extends StatelessWidget {
  final DateTime? date;
  final bool isRtl;

  const _DateSeparator({required this.date, required this.isRtl});

  @override
  Widget build(BuildContext context) {
    String text;
    if (date == null) {
      text = '';
    } else {
      final now = DateTime.now();
      final local = date!.toLocal();
      final diff = now.difference(local);
      if (diff.inDays == 0) {
        text = isRtl ? 'اليوم' : 'Today';
      } else if (diff.inDays == 1) {
        text = isRtl ? 'أمس' : 'Yesterday';
      } else {
        text = DateFormat.yMMMd().format(local);
      }
    }

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 12),
      child: Center(
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
          decoration: BoxDecoration(
            color: Colors.grey.shade200,
            borderRadius: BorderRadius.circular(12),
          ),
          child: Text(
            text,
            style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
          ),
        ),
      ),
    );
  }
}

class _MessageBubble extends StatelessWidget {
  final ChatMessageModel message;
  final bool isMine;
  final bool isRtl;

  const _MessageBubble({
    required this.message,
    required this.isMine,
    required this.isRtl,
  });

  @override
  Widget build(BuildContext context) {
    final timeStr = message.createdAt != null
        ? DateFormat.Hm().format(message.createdAt!.toLocal())
        : '';

    return Align(
      alignment: isMine ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: EdgeInsets.only(
          top: 4,
          bottom: 4,
          left: isMine ? 48 : 0,
          right: isMine ? 0 : 48,
        ),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        decoration: BoxDecoration(
          color: isMine ? const Color(0xFF1E40AF) : Colors.grey.shade100,
          borderRadius: BorderRadius.only(
            topLeft: const Radius.circular(16),
            topRight: const Radius.circular(16),
            bottomLeft: isMine ? const Radius.circular(16) : const Radius.circular(4),
            bottomRight: isMine ? const Radius.circular(4) : const Radius.circular(16),
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.04),
              blurRadius: 4,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment:
              isMine ? CrossAxisAlignment.end : CrossAxisAlignment.start,
          children: [
            Text(
              message.content,
              style: TextStyle(
                color: isMine ? Colors.white : Colors.black87,
                fontSize: 15,
              ),
            ),
            const SizedBox(height: 4),
            Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  timeStr,
                  style: TextStyle(
                    color: isMine ? Colors.white70 : Colors.grey.shade400,
                    fontSize: 11,
                  ),
                ),
                if (isMine) ...[
                  const SizedBox(width: 4),
                  Icon(
                    message.isRead ? Icons.done_all : Icons.done,
                    size: 14,
                    color: message.isRead ? Colors.lightBlueAccent : Colors.white70,
                  ),
                ],
              ],
            ),
          ],
        ),
      ),
    );
  }
}
