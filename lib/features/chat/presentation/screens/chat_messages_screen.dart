import 'package:go_router/go_router.dart';
import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/features/chat/application/chat_provider.dart';
import 'package:magaiees_marketplace/features/chat/presentation/screens/call_screen.dart';
import 'package:magaiees_marketplace/features/chat/domain/models/chat_message_model.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:audioplayers/audioplayers.dart';
import 'package:magaiees_marketplace/core/utils/image_picker_helper.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:magaiees_marketplace/core/utils/notification_sound_service.dart';
import 'package:magaiees_marketplace/core/utils/call_ringtone_service.dart';
import 'package:magaiees_marketplace/core/utils/call_permission_helper.dart';
import 'package:magaiees_marketplace/core/utils/name_formatter.dart';
import 'package:magaiees_marketplace/core/providers/ugc_moderation_provider.dart';
import 'package:magaiees_marketplace/core/services/push_notification_service.dart';

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
  String? _otherUserId;
  String? _otherUserName;
  DateTime? _lastSeen;
  bool _isOnline = false;

  StreamSubscription? _presenceSubscription;
  RealtimeChannel? _callSignalChannel;
  final AudioPlayer _audioPlayer = AudioPlayer();
  int _lastMessageCount = 0;
  bool _soundsEnabled = true;

  @override
  void initState() {
    super.initState();
    ChatPresence.currentRoomId = widget.roomId;
    SharedPreferences.getInstance().then((prefs) {
      _soundsEnabled = prefs.getBool('chat_sounds') ?? true;
    });

    WidgetsBinding.instance.addPostFrameCallback((_) {
      _updatePresence();
      _setupPresenceStream();
      _setupCallListener();
      
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
      if (!mounted) return;
      if (roomRes == null) return;
      
      final isBuyer = roomRes['buyer_id'] == user.id;
      final otherId = isBuyer ? roomRes['merchant_id'] : roomRes['buyer_id'];
      _otherUserId = otherId?.toString();

      // If current user is buyer and room has store_id, try to fetch store name
      if (isBuyer && roomRes['store_id'] != null) {
        try {
          final storeRes = await Supabase.instance.client
              .from('stores')
              .select('name, name_ar')
              .eq('id', roomRes['store_id'])
              .maybeSingle();
          if (storeRes != null && mounted) {
            final sName = NameFormatter.formatName(
              name: storeRes['name']?.toString(),
              nameAr: storeRes['name_ar']?.toString(),
            );
            if (sName.isNotEmpty) {
              setState(() {
                _otherUserName = sName;
              });
            }
          }
        } catch (_) {}
      }
      
      _presenceSubscription = Supabase.instance.client
          .from('users')
          .stream(primaryKey: ['id'])
          .eq('id', otherId)
          .listen((data) {
        if (data.isNotEmpty && mounted) {
          // Find the exact user because stream might return multiple rows if eq() is ignored by the SDK
          final userRes = data.firstWhere(
            (u) => u['id'] == otherId,
            orElse: () => <String, dynamic>{},
          );
          
          if (userRes.isNotEmpty) {
            setState(() {
              final formattedUserName = NameFormatter.formatName(
                name: userRes['name']?.toString(),
                nameAr: userRes['name_ar']?.toString(),
              );
              // Only overwrite if it wasn't already set to a store name, or if user is merchant
              if (!isBuyer || _otherUserName == null || _otherUserName!.isEmpty) {
                if (formattedUserName.isNotEmpty) {
                  _otherUserName = formattedUserName;
                }
              }
              if (userRes['last_seen'] != null) {
                _lastSeen = DateTime.tryParse(userRes['last_seen'].toString());
                if (_lastSeen != null) {
                  _isOnline = DateTime.now().toUtc().difference(_lastSeen!).inMinutes < 2;
                }
              }
            });
          }
        }
      });
    } catch (_) {}
  }

  void _setupCallListener() {
    // تنظيف القناة القديمة إذا وجدت
    if (_callSignalChannel != null) {
      try {
        _callSignalChannel!.unsubscribe();
        Supabase.instance.client.removeChannel(_callSignalChannel!);
      } catch (_) {}
      _callSignalChannel = null;
    }

    // يجب أن يكون اسم القناة متطابقاً مع اسم القناة في CallScreen
    _callSignalChannel = Supabase.instance.client.channel('call_${widget.roomId}');
    _callSignalChannel?.onBroadcast(event: 'webrtc_signal', callback: (payload) {
      if (!mounted) return;
      final senderId = payload['sender_id'];
      final currentUser = ref.read(authNotifierProvider).valueOrNull;
      if (currentUser == null || senderId == currentUser.id) return;
      
      final data = payload['data'];
      if (data['type'] == 'join') {
        if (CallRingtoneService.isCallActive) return;
        // In the background the native call screen (push) rings instead
        if (WidgetsBinding.instance.lifecycleState != AppLifecycleState.resumed) return;
        // استخراج اسم المتصل
        final rawCaller = data['caller_name']?.toString() ?? _otherUserName ?? '';
        final callerName = NameFormatter.cleanPrefix(rawCaller).isNotEmpty
            ? NameFormatter.cleanPrefix(rawCaller)
            : 'مستخدم';
        
        // إزالة القناة الحالية أولاً حتى لا تتعارض مع قناة CallScreen
        try {
          _callSignalChannel?.unsubscribe();
          Supabase.instance.client.removeChannel(_callSignalChannel!);
        } catch (_) {}
        _callSignalChannel = null;
        
        // فتح شاشة الاتصال الوارد الاحترافية
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => IncomingCallScreen(
              roomId: widget.roomId,
              callerName: callerName,
              currentUser: currentUser,
            ),
          ),
        ).then((_) {
          CallRingtoneService.stop();
          // بعد العودة من شاشة المكالمة، أعد تفعيل المستمع لاستقبال مكالمات جديدة
          if (mounted) {
            Future.delayed(const Duration(seconds: 1), () {
              if (mounted) _setupCallListener();
            });
          }
        });
      }
    }).subscribe();
  }

  @override
  void dispose() {
    if (ChatPresence.currentRoomId == widget.roomId) ChatPresence.currentRoomId = null;
    CallRingtoneService.stop();
    if (_callSignalChannel != null) {
      try {
        _callSignalChannel!.unsubscribe();
        Supabase.instance.client.removeChannel(_callSignalChannel!);
      } catch (_) {}
    }
    _presenceSubscription?.cancel();
    _presenceTimer?.cancel();
    _messageController.dispose();
    _scrollController.dispose();
    _audioPlayer.dispose();
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


  Future<void> _pickAndSendImage() async {
    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user == null) return;

    final pickedFile = await ImagePickerHelper.pickImage(context, imageQuality: 70);
    if (pickedFile == null) return;

    setState(() => _isSending = true);
    try {
      final bytes = await pickedFile.readAsBytes();
      final ext = pickedFile.name.split('.').last;
      final fName = 'chat_${DateTime.now().millisecondsSinceEpoch}.$ext';
      
      await Supabase.instance.client.storage
          .from('products_images')
          .uploadBinary(fName, bytes);
          
      final url = Supabase.instance.client.storage
          .from('products_images')
          .getPublicUrl(fName);

      await ChatService.sendMessage(
        roomId: widget.roomId,
        senderId: user.id,
        content: url,
        type: 'image',
      );
      
      _scrollToBottom();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
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
            Text(
              NameFormatter.cleanPrefix(_otherUserName).isNotEmpty
                  ? NameFormatter.cleanPrefix(_otherUserName)
                  : (isRtl ? 'المحادثة' : 'Conversation'),
              style: const TextStyle(fontSize: 16),
            ),
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
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go('/buyer/chat');
            }
          },
        ),

                actions: [
          IconButton(
            icon: const Icon(Icons.call), // Internet VoIP call
            onPressed: () async {
              final authState = ref.read(authNotifierProvider);
              final currentUser = authState.valueOrNull;
              if (currentUser == null) return;

              // Ensure microphone permission using international-grade UI
              final hasPermission = await CallPermissionHelper.requestMicrophonePermission(context);
              if (!hasPermission) return;

              if (!context.mounted) return;

              // إزالة مستمع المكالمات قبل فتح شاشة الاتصال لتجنب التعارض
              try {
                _callSignalChannel?.unsubscribe();
                Supabase.instance.client.removeChannel(_callSignalChannel!);
              } catch (_) {}
              _callSignalChannel = null;

              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => CallScreen(
                    callID: widget.roomId,
                    currentUser: currentUser,
                    callerName: _otherUserName,
                    targetUserId: _otherUserId,
                  ),
                ),
              ).then((_) {
                CallRingtoneService.stop();
                // بعد العودة من المكالمة، أعد تفعيل المستمع
                if (mounted) {
                  Future.delayed(const Duration(seconds: 1), () {
                    if (mounted) _setupCallListener();
                  });
                }
              });
            },
          ),
          PopupMenuButton<String>(
            icon: const Icon(Icons.more_vert),
            tooltip: isRtl ? 'الإبلاغ والحظر' : 'Report & Block',
            onSelected: (val) {
              if (val == 'report') {
                showDialog(
                  context: context,
                  builder: (ctx) => AlertDialog(
                    title: Text(isRtl ? 'الإبلاغ عن مستخدم أو محتوى' : 'Report User / Content'),
                    content: Text(
                      isRtl
                          ? 'هل ترغب في الإبلاغ عن محتوى غير لائق في هذه المحادثة؟ سيقوم فريق الإشراف بمراجعتها فوراً.'
                          : 'Do you want to report inappropriate content in this chat? Our moderation team will review it promptly.',
                    ),
                    actions: [
                      TextButton(
                        onPressed: () => Navigator.pop(ctx),
                        child: Text(isRtl ? 'إلغاء' : 'Cancel'),
                      ),
                      FilledButton(
                        onPressed: () async {
                          Navigator.pop(ctx);
                          await ref.read(blockedEntitiesProvider.notifier).submitReport(
                                targetType: 'chat_room',
                                targetId: _otherUserId ?? widget.roomId,
                                reason: 'Inappropriate chat content reported by user',
                              );
                          if (context.mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(isRtl ? 'تم إرسال البلاغ لفريق المراجعة بنجاح' : 'Report submitted to moderation team'),
                              ),
                            );
                          }
                        },
                        child: Text(isRtl ? 'إرسال البلاغ' : 'Report'),
                      ),
                    ],
                  ),
                );
              } else if (val == 'block') {
                showDialog(
                  context: context,
                  builder: (ctx) => AlertDialog(
                    title: Text(isRtl ? 'حظر المستخدم' : 'Block User'),
                    content: Text(
                      isRtl
                          ? 'هل أنت متأكد من حظر هذا المستخدم وإيقاف المحادثة معه؟'
                          : 'Are you sure you want to block this user and stop receiving messages from them?',
                    ),
                    actions: [
                      TextButton(
                        onPressed: () => Navigator.pop(ctx),
                        child: Text(isRtl ? 'إلغاء' : 'Cancel'),
                      ),
                      FilledButton(
                        style: FilledButton.styleFrom(backgroundColor: Colors.red),
                        onPressed: () async {
                          Navigator.pop(ctx);
                          await ref.read(blockedEntitiesProvider.notifier).blockEntity(
                                targetId: _otherUserId ?? widget.roomId,
                                secondaryKey: _otherUserName,
                                reason: 'Blocked from chat room ${widget.roomId}',
                              );
                          if (context.mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(isRtl ? 'تم حظر المستخدم بنجاح' : 'User blocked successfully'),
                              ),
                            );
                            if (context.canPop()) {
                              context.pop();
                            } else {
                              context.go('/buyer/chat');
                            }
                          }
                        },
                        child: Text(isRtl ? 'حظر' : 'Block'),
                      ),
                    ],
                  ),
                );
              }
            },
            itemBuilder: (ctx) => [
              PopupMenuItem(
                value: 'report',
                child: Row(
                  children: [
                    const Icon(Icons.flag_outlined, color: Colors.orange, size: 20),
                    const SizedBox(width: 8),
                    Text(isRtl ? 'الإبلاغ عن محتوى مخالف' : 'Report Content'),
                  ],
                ),
              ),
              PopupMenuItem(
                value: 'block',
                child: Row(
                  children: [
                    const Icon(Icons.block, color: Colors.red, size: 20),
                    const SizedBox(width: 8),
                    Text(isRtl ? 'حظر هذا المستخدم' : 'Block User'),
                  ],
                ),
              ),
            ],
          ),
        ],
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
                    NotificationSoundService.play();
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
                IconButton(
                  onPressed: _isSending ? null : _pickAndSendImage,
                  icon: const Icon(Icons.image, color: Colors.blue),
                ),
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
            if (message.type == 'image')
              GestureDetector(
                onTap: () {
                  showDialog(
                    context: context,
                    builder: (context) => Dialog(
                      child: Stack(
                        children: [
                          InteractiveViewer(
                            child: Image.network(message.content, fit: BoxFit.contain),
                          ),
                          Positioned(
                            top: 8,
                            right: 8,
                            child: IconButton(
                              icon: const Icon(Icons.close, color: Colors.white, shadows: [Shadow(blurRadius: 2, color: Colors.black)]),
                              onPressed: () => Navigator.pop(context),
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                },
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: Image.network(
                    message.content,
                    width: 200,
                    height: 200,
                    fit: BoxFit.cover,
                    loadingBuilder: (context, child, progress) {
                      if (progress == null) return child;
                      return const SizedBox(
                        width: 200, height: 200,
                        child: Center(child: CircularProgressIndicator()),
                      );
                    },
                    errorBuilder: (context, error, stackTrace) => const SizedBox(
                      width: 200, height: 200,
                      child: Center(child: Icon(Icons.broken_image, size: 50)),
                    ),
                  ),
                ),
              )
            else
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
