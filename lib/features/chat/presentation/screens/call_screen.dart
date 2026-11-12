import 'dart:async';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_webrtc/flutter_webrtc.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:magaiees_marketplace/core/utils/call_ringtone_service.dart';
import 'package:magaiees_marketplace/core/services/local_notification_service.dart';
import 'package:magaiees_marketplace/features/auth/domain/models/user_model.dart';
import 'package:magaiees_marketplace/core/utils/name_formatter.dart';
import 'package:magaiees_marketplace/core/utils/call_permission_helper.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

class CallScreen extends StatefulWidget {
  final String callID;
  final UserModel currentUser;
  final String? callerName; // اسم المتصل (يظهر للطرف الآخر)
  final bool isIncoming; // هل هي مكالمة واردة أم صادرة
  final String? targetUserId; // اختياري: معرف الطرف الآخر لإرسال إشارة مباشرة

  const CallScreen({
    super.key,
    required this.callID,
    required this.currentUser,
    this.callerName,
    this.isIncoming = false,
    this.targetUserId,
  });

  @override
  State<CallScreen> createState() => _CallScreenState();
}

class _CallScreenState extends State<CallScreen> with TickerProviderStateMixin {
  // 🔴 مفتاح التحكم الذكي: 
  // اجعل هذه القيمة true لتفعيل مكالمات الفيديو، أو false للمكالمات الصوتية فقط.
  final bool _enableVideo = false; 

  final _localRenderer = RTCVideoRenderer();
  final _remoteRenderer = RTCVideoRenderer();
  
  RTCPeerConnection? _peerConnection;
  MediaStream? _localStream;
  RealtimeChannel? _signalingChannel;

  // قائمة ICE candidates المعلقة حتى يتم ربط الاتصال
  final List<RTCIceCandidate> _pendingCandidates = [];
  bool _hasRemoteDescription = false;
  bool _isSignalingReady = false;
  final List<Map<String, dynamic>> _signalQueue = [];

  bool _isMicOn = true;
  bool _isSpeakerOn = false;
  bool _isVideoOn = true;
  bool _isConnected = false;
  bool _isCalling = true; // جاري الاتصال (الرنين)
  String _callStatus = 'جاري الاتصال...';
  int _callDuration = 0;
  Timer? _callTimer;

  // أنيميشن الرنين
  late AnimationController _pulseController;
  late Animation<double> _pulseAnimation;

  final _supabase = Supabase.instance.client;

  /// Row in call_invites for outgoing calls (drives the push to the callee).
  String? _inviteId;

  String _callerDisplayName() {
    final u = widget.currentUser;
    final hasStoreName = (u.storeName != null && u.storeName!.isNotEmpty) ||
        (u.storeNameAr != null && u.storeNameAr!.isNotEmpty);
    return u.hasStore && hasStoreName
        ? NameFormatter.formatStore(storeName: u.storeName, storeNameAr: u.storeNameAr)
        : NameFormatter.formatUser(u);
  }

  Future<void> _createCallInvite() async {
    final target = widget.targetUserId;
    if (widget.isIncoming || target == null || target.isEmpty) return;
    try {
      final res = await _supabase.from('call_invites').insert({
        'room_id': widget.callID,
        'caller_id': widget.currentUser.id,
        'callee_id': target,
        'caller_name': _callerDisplayName(),
      }).select('id').single();
      _inviteId = res['id']?.toString();
    } catch (e) {
      debugPrint('Failed to create call invite: $e');
    }
  }

  void _updateCallInvite(String status) {
    final id = _inviteId;
    if (id == null) return;
    _supabase.from('call_invites').update({
      'status': status,
      'updated_at': DateTime.now().toUtc().toIso8601String(),
    }).eq('id', id).then((_) {}, onError: (e) => debugPrint('call invite update failed: $e'));
  }

  @override
  void initState() {
    super.initState();
    CallRingtoneService.isCallActive = true;
    final peerDisplay = NameFormatter.cleanPrefix(widget.callerName ?? '');
    unawaited(
      LocalNotificationService.instance.showOngoingCallNotification(
        peerName: peerDisplay.isNotEmpty ? peerDisplay : 'مستخدم السيطرة',
        statusText: 'مكالمة صوتية نشطة عبر الإنترنت (VoIP) • الميكروفون يعمل في الخلفية',
      ),
    );
    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1500),
    )..repeat(reverse: true);
    _pulseAnimation = Tween<double>(begin: 1.0, end: 1.15).animate(
      CurvedAnimation(parent: _pulseController, curve: Curves.easeInOut),
    );
    _initRenderers();
  }

  @override
  void dispose() {
    CallRingtoneService.isCallActive = false;
    CallRingtoneService.stop();
    unawaited(LocalNotificationService.instance.cancelOngoingCallNotification());
    _callTimer?.cancel();
    _pulseController.dispose();
    _cleanupCall();
    super.dispose();
  }

  Future<void> _cleanupCall() async {
    CallRingtoneService.stop();
    unawaited(LocalNotificationService.instance.cancelOngoingCallNotification());
    // Stops the native ringing on the callee if they haven't answered yet
    _updateCallInvite(_isConnected ? 'accepted' : 'ended');
    if (_isSignalingReady) {
      _sendSignal({'type': 'hangup'});
    }
    _isSignalingReady = false;
    _signalQueue.clear();
    
    if (_localStream != null) {
      for (var track in _localStream!.getTracks()) {
        track.stop();
      }
      await _localStream?.dispose();
    }
    
    await _peerConnection?.close();
    await _peerConnection?.dispose();
    
    // دائماً تنظيف الـ renderers (الـ remoteRenderer ضروري حتى للصوت على الويب)
    await _localRenderer.dispose();
    await _remoteRenderer.dispose();
    
    if (_signalingChannel != null) {
      // فقط إلغاء الاشتراك بدون حذف القناة نهائياً
      // حتى لا يتأثر مستمع المكالمات في شاشة المحادثة
      _signalingChannel!.unsubscribe();
    }
  }

  void _startCallTimer() {
    _callTimer = Timer.periodic(const Duration(seconds: 1), (_) {
      if (mounted) {
        setState(() => _callDuration++);
      }
    });
  }

  String _formatDuration(int seconds) {
    final m = (seconds ~/ 60).toString().padLeft(2, '0');
    final s = (seconds % 60).toString().padLeft(2, '0');
    return '$m:$s';
  }

  Future<void> _initRenderers() async {
    // دائماً نهيئ الـ remoteRenderer لأن الويب يحتاجه لتشغيل الصوت
    await _remoteRenderer.initialize();
    if (_enableVideo) {
      await _localRenderer.initialize();
    }
    await _startCallProcess();
  }

  Future<void> _startCallProcess() async {
    try {
      // 1. Request microphone permission using world-class international UI
      final hasPermission = await CallPermissionHelper.requestMicrophonePermission(context);
      if (!hasPermission) {
        if (mounted) Navigator.pop(context);
        return;
      }

      final peerDisplay = NameFormatter.cleanPrefix(widget.callerName ?? '');
      unawaited(
        LocalNotificationService.instance.showOngoingCallNotification(
          peerName: peerDisplay.isNotEmpty ? peerDisplay : 'مستخدم السيطرة',
          statusText: 'مكالمة صوتية نشطة عبر الإنترنت (VoIP) • الميكروفون يعمل في الخلفية',
        ),
      );

      final mediaConstraints = <String, dynamic>{
        'audio': {
          'echoCancellation': true,
          'noiseSuppression': true,
          'autoGainControl': true,
        },
        'video': _enableVideo ? {
          'mandatory': {
            'minWidth': '640',
            'minHeight': '480',
            'minFrameRate': '30',
          },
          'facingMode': 'user',
          'optional': [],
        } : false,
      };

      try {
        _localStream = await navigator.mediaDevices.getUserMedia(mediaConstraints);
      } catch (firstErr) {
        debugPrint('Failed with advanced constraints ($firstErr), retrying with basic audio...');
        // Fallback to basic universal audio constraint without advanced map options
        final basicConstraints = <String, dynamic>{
          'audio': true,
          'video': _enableVideo,
        };
        _localStream = await navigator.mediaDevices.getUserMedia(basicConstraints);
      }
      
      if (_enableVideo) {
        _localRenderer.srcObject = _localStream;
      }
      if (mounted) setState(() {});

      await _setupPeerConnection();
      _setupSignaling();

      if (!widget.isIncoming) {
        CallRingtoneService.startOutgoingTone();
        _createCallInvite();
      }
    } catch (e) {
      debugPrint('Error accessing microphone: $e');
      if (mounted) {
        // دائماً عرض نافذة الإذن الاحترافية بدلاً من رسائل الأخطاء التقنية المزعجة
        _showPermissionDialog();
      }
    }
  }

  void _showPermissionDialog({bool isPermanentlyDenied = false}) {
    final bool isInsecureHttpWeb = kIsWeb &&
        Uri.base.scheme == 'http' &&
        Uri.base.host != 'localhost' &&
        Uri.base.host != '127.0.0.1';

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.transparent,
        contentPadding: const EdgeInsets.fromLTRB(24, 20, 24, 20),
        titlePadding: const EdgeInsets.fromLTRB(24, 24, 24, 0),
        title: Column(
          children: [
            Container(
              width: 72,
              height: 72,
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF1E3A8A), Color(0xFF3B82F6)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                shape: BoxShape.circle,
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF3B82F6).withValues(alpha: 0.3),
                    blurRadius: 16,
                    offset: const Offset(0, 6),
                  ),
                ],
              ),
              child: const Icon(Icons.mic_rounded, size: 36, color: Colors.white),
            ),
            const SizedBox(height: 18),
            const Text(
              'إذن استخدام الميكروفون',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontWeight: FontWeight.bold,
                fontSize: 18,
                color: Color(0xFF0F172A),
              ),
            ),
            const SizedBox(height: 4),
            Text(
              'مطلوب لتفعيل الاتصال الصوتي المباشر',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 13,
                color: Colors.grey.shade600,
              ),
            ),
          ],
        ),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 8),
              // بطاقة الإفصاح البارز المتوافقة مع سياسات Google Play
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Icon(Icons.info_outline_rounded, size: 20, color: Color(0xFF2563EB)),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            'يتطلب تطبيق السيطرة الوصول إلى الميكروفون لتمكينك من التحدث مع التاجر في مكالمات صوتية مباشرة لمناقشة المنتجات والأسعار.',
                            style: TextStyle(
                              fontSize: 12.5,
                              height: 1.45,
                              color: Colors.grey.shade800,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 10),
                      child: Divider(height: 1, color: Color(0xFFE2E8F0)),
                    ),
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Icon(Icons.verified_user_outlined, size: 20, color: Color(0xFF059669)),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            '🔒 الخصوصية والأمان: المكالمات مشفرة بالكامل بين الطرفين، ولا يتم تسجيل أو تخزين أو مشاركة أي محادثة صوتية.',
                            style: TextStyle(
                              fontSize: 12,
                              height: 1.4,
                              color: Colors.grey.shade700,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 14),
              if (isInsecureHttpWeb) ...[
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFEF2F2),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFFECACA)),
                  ),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Icon(Icons.gpp_maybe_rounded, size: 20, color: Color(0xFFDC2626)),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          'متصفحات الموبايل (سفاري/كروم) تمنع تشغيل الميكروفون عبر روابط الـ HTTP غير المشفرة لحماية الخصوصية. لتجربة الاتصال، يرجى فتح التطبيق كبرنامج مثبت (APK) أو تفعيل HTTPS.',
                          style: TextStyle(
                            fontSize: 11.5,
                            height: 1.4,
                            color: Colors.red.shade900,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 14),
              ],
              // إرشادات سريعة في حال تم حظر الإذن سابقاً
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEF3C7).withValues(alpha: 0.5),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFFDE68A)),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(Icons.settings_suggest_rounded, size: 20, color: Color(0xFFD97706)),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        'في حال رفض الإذن سابقاً: يرجى التوجه إلى إعدادات الهاتف > التطبيقات > السيطرة > الأذونات > تفعيل الميكروفون.',
                        style: TextStyle(
                          fontSize: 11.5,
                          height: 1.4,
                          color: Colors.amber.shade900,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        actionsPadding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
        actions: [
          Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  style: OutlinedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 13),
                    side: BorderSide(color: Colors.grey.shade300),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  onPressed: () {
                    Navigator.pop(ctx);
                    Navigator.pop(context);
                  },
                  child: Text(
                    'ليس الآن',
                    style: TextStyle(color: Colors.grey.shade700, fontWeight: FontWeight.bold),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                flex: 2,
                child: ElevatedButton.icon(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF1E3A8A),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 13),
                    elevation: 0,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  onPressed: () async {
                    Navigator.pop(ctx);
                    if (kIsWeb) {
                      _startCallProcess();
                      return;
                    }
                    if (isPermanentlyDenied) {
                      await openAppSettings();
                    } else {
                      final req = await Permission.microphone.request();
                      if (req.isGranted) {
                        _startCallProcess();
                      } else if (mounted) {
                        _showPermissionDialog(isPermanentlyDenied: req.isPermanentlyDenied);
                      }
                    }
                  },
                  icon: Icon(
                    isPermanentlyDenied ? Icons.settings_rounded : Icons.check_circle_outline_rounded,
                    size: 18,
                  ),
                  label: Text(
                    isPermanentlyDenied ? 'فتح إعدادات الهاتف' : 'منح الإذن والمتابعة',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Future<void> _setupPeerConnection() async {
    final configuration = <String, dynamic>{
      'iceServers': [
        {'urls': 'stun:stun.l.google.com:19302'},
        {'urls': 'stun:stun1.l.google.com:19302'},
        {'urls': 'stun:stun2.l.google.com:19302'},
        {'urls': 'stun:stun3.l.google.com:19302'},
        {'urls': 'stun:stun4.l.google.com:19302'},
      ],
      'sdpSemantics': 'unified-plan',
    };

    _peerConnection = await createPeerConnection(configuration);

    // مراقبة حالة ICE Connection
    _peerConnection?.onIceConnectionState = (state) {
      if (!mounted) return;
      switch (state) {
        case RTCIceConnectionState.RTCIceConnectionStateConnected:
        case RTCIceConnectionState.RTCIceConnectionStateCompleted:
          CallRingtoneService.stop();
          setState(() {
            _isConnected = true;
            _isCalling = false;
            _callStatus = 'متصل';
          });
          _pulseController.stop();
          _startCallTimer();
          _updateCallInvite('accepted');
          break;
        case RTCIceConnectionState.RTCIceConnectionStateDisconnected:
          CallRingtoneService.stop();
          setState(() => _callStatus = 'انقطع الاتصال...');
          break;
        case RTCIceConnectionState.RTCIceConnectionStateFailed:
          CallRingtoneService.stop();
          setState(() => _callStatus = 'فشل الاتصال');
          Future.delayed(const Duration(seconds: 2), () {
            if (mounted) Navigator.pop(context);
          });
          break;
        case RTCIceConnectionState.RTCIceConnectionStateClosed:
          CallRingtoneService.stop();
          if (mounted) Navigator.pop(context);
          break;
        default:
          break;
      }
    };

    _peerConnection?.onIceCandidate = (candidate) {
      _sendSignal({
        'type': 'candidate',
        'candidate': candidate.candidate,
        'sdpMid': candidate.sdpMid,
        'sdpMLineIndex': candidate.sdpMLineIndex,
      });
    };

    _peerConnection?.onTrack = (event) {
      if (event.streams.isNotEmpty) {
        // دائماً نربط الـ stream بالـ renderer (ضروري لتشغيل الصوت على الويب)
        _remoteRenderer.srcObject = event.streams[0];
      }
      if (event.track.kind == 'audio') {
        CallRingtoneService.stop();
        if (mounted) {
          setState(() {
            _isConnected = true;
            _isCalling = false;
            _callStatus = 'متصل';
          });
        }
      }
    };

    // Add local tracks
    _localStream?.getTracks().forEach((track) {
      _peerConnection?.addTrack(track, _localStream!);
    });
  }

  void _setupSignaling() {
    _signalingChannel = _supabase.channel('call_${widget.callID}');

    _signalingChannel?.onBroadcast(event: 'webrtc_signal', callback: (payload) {
      if (!mounted) return;
      final senderId = payload['sender_id'];
      if (senderId == widget.currentUser.id) return;

      final data = payload['data'] as Map<String, dynamic>;
      _handleSignal(data);
    });

    _signalingChannel?.subscribe((status, [error]) {
      if (!mounted) return;
      if (status == RealtimeSubscribeStatus.subscribed) {
        _isSignalingReady = true;
        // إرسال إشارة الانضمام مع اسم المتصل عبر الويب سوكت
        final callerDisplayName = widget.currentUser.hasStore &&
                ((widget.currentUser.storeName != null && widget.currentUser.storeName!.isNotEmpty) ||
                 (widget.currentUser.storeNameAr != null && widget.currentUser.storeNameAr!.isNotEmpty))
            ? NameFormatter.formatStore(
                storeName: widget.currentUser.storeName,
                storeNameAr: widget.currentUser.storeNameAr,
              )
            : NameFormatter.formatUser(widget.currentUser);

        try {
          _signalingChannel?.sendBroadcastMessage(
            event: 'webrtc_signal',
            payload: {
              'sender_id': widget.currentUser.id,
              'data': {
                'type': 'join',
                'caller_name': callerDisplayName,
                'room_id': widget.callID,
              },
            },
          );

          // إرسال إشعار مباشر لقناة المستخدم المستهدف إن وجدت
          if (widget.targetUserId != null && widget.targetUserId!.isNotEmpty) {
            final targetChannel = _supabase.channel('user_call_${widget.targetUserId}');
            targetChannel.subscribe((st, [err]) {
              if (st == RealtimeSubscribeStatus.subscribed) {
                try {
                  targetChannel.sendBroadcastMessage(
                    event: 'webrtc_signal',
                    payload: {
                      'sender_id': widget.currentUser.id,
                      'data': {
                        'type': 'join',
                        'caller_name': callerDisplayName,
                        'room_id': widget.callID,
                      },
                    },
                  );
                } catch (_) {}
                Future.delayed(const Duration(milliseconds: 600), () {
                  try {
                    targetChannel.unsubscribe();
                    _supabase.removeChannel(targetChannel);
                  } catch (_) {}
                });
              }
            });
          }
        } catch (_) {}
        // إرسال كافة الإشارات المؤجلة
        _flushSignalQueue();
      } else if (status == RealtimeSubscribeStatus.closed ||
                 status == RealtimeSubscribeStatus.channelError) {
        _isSignalingReady = false;
      }
    });
  }

  Future<void> _handleSignal(Map<String, dynamic> data) async {
    final type = data['type'];

    if (type == 'join') {
      // الطرف الآخر انضم - أنشئ العرض
      _createOffer();
    } else if (type == 'offer') {
      final sdp = data['sdp'];
      await _peerConnection?.setRemoteDescription(
        RTCSessionDescription(sdp, type),
      );
      _hasRemoteDescription = true;
      // أضف جميع ICE candidates المعلقة
      for (final c in _pendingCandidates) {
        await _peerConnection?.addCandidate(c);
      }
      _pendingCandidates.clear();
      _createAnswer();
    } else if (type == 'answer') {
      CallRingtoneService.stop();
      final sdp = data['sdp'];
      await _peerConnection?.setRemoteDescription(
        RTCSessionDescription(sdp, type),
      );
      _hasRemoteDescription = true;
      // أضف جميع ICE candidates المعلقة
      for (final c in _pendingCandidates) {
        await _peerConnection?.addCandidate(c);
      }
      _pendingCandidates.clear();
      if (mounted) setState(() { _callStatus = 'جاري ربط الاتصال...'; });
    } else if (type == 'candidate') {
      final candidate = RTCIceCandidate(
        data['candidate'],
        data['sdpMid'],
        data['sdpMLineIndex'],
      );
      // إذا لم يتم تعيين Remote Description بعد، أضف إلى قائمة الانتظار
      if (_hasRemoteDescription) {
        await _peerConnection?.addCandidate(candidate);
      } else {
        _pendingCandidates.add(candidate);
      }
    } else if (type == 'hangup') {
      CallRingtoneService.stop();
      if (mounted) Navigator.pop(context);
    }
  }

  Future<void> _createOffer() async {
    final offer = await _peerConnection?.createOffer({
      'offerToReceiveAudio': true,
      'offerToReceiveVideo': _enableVideo,
    });
    if (offer == null) return;
    await _peerConnection?.setLocalDescription(offer);
    _sendSignal({'type': 'offer', 'sdp': offer.sdp});
  }

  Future<void> _createAnswer() async {
    final answer = await _peerConnection?.createAnswer();
    if (answer == null) return;
    await _peerConnection?.setLocalDescription(answer);
    _sendSignal({'type': 'answer', 'sdp': answer.sdp});
  }

  void _sendSignal(Map<String, dynamic> data) {
    if (!_isSignalingReady || _signalingChannel == null) {
      _signalQueue.add(data);
      return;
    }
    try {
      _signalingChannel?.sendBroadcastMessage(
        event: 'webrtc_signal',
        payload: {
          'sender_id': widget.currentUser.id,
          'data': data,
        },
      );
    } catch (e) {
      debugPrint('Error sending signal: $e');
    }
  }

  void _flushSignalQueue() {
    if (!_isSignalingReady || _signalingChannel == null) return;
    final signals = List<Map<String, dynamic>>.from(_signalQueue);
    _signalQueue.clear();
    for (final signal in signals) {
      try {
        _signalingChannel?.sendBroadcastMessage(
          event: 'webrtc_signal',
          payload: {
            'sender_id': widget.currentUser.id,
            'data': signal,
          },
        );
      } catch (e) {
        debugPrint('Error flushing signal: $e');
      }
    }
  }

  void _toggleMic() {
    if (_localStream != null) {
      final audioTracks = _localStream!.getAudioTracks();
      if (audioTracks.isNotEmpty) {
        audioTracks[0].enabled = !audioTracks[0].enabled;
        setState(() => _isMicOn = audioTracks[0].enabled);
      }
    }
  }

  Future<void> _toggleSpeaker() async {
    final nextState = !_isSpeakerOn;
    setState(() => _isSpeakerOn = nextState);
    if (!kIsWeb) {
      try {
        await Helper.setSpeakerphoneOn(nextState);
      } catch (e) {
        debugPrint('Error toggling speakerphone: $e');
      }
    }
  }

  void _toggleVideo() {
    if (_localStream != null && _enableVideo) {
      final videoTracks = _localStream!.getVideoTracks();
      if (videoTracks.isNotEmpty) {
        videoTracks[0].enabled = !videoTracks[0].enabled;
        setState(() => _isVideoOn = videoTracks[0].enabled);
      }
    }
  }
  
  void _switchCamera() {
    if (_localStream != null && _enableVideo) {
      final videoTracks = _localStream!.getVideoTracks();
      if (videoTracks.isNotEmpty) {
        Helper.switchCamera(videoTracks[0]);
      }
    }
  }

  void _hangup() {
    if (mounted) Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final rawName = widget.callerName ?? '';
    final cleaned = NameFormatter.cleanPrefix(rawName);
    final displayName = cleaned.isNotEmpty ? cleaned : (isRtl ? 'مستخدم' : 'User');

    return Scaffold(
      backgroundColor: const Color(0xFF1a1a2e),
      body: SafeArea(
        child: Stack(
          children: [
            // ==========================================
            // الخلفية المتدرجة
            // ==========================================
            Container(
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Color(0xFF1a1a2e),
                    Color(0xFF16213e),
                    Color(0xFF0f3460),
                  ],
                ),
              ),
            ),

            // ==========================================
            // شاشة الفيديو (إذا كان مفعلاً)
            // ==========================================
            if (_isConnected && _enableVideo) ...[
              Positioned.fill(
                child: RTCVideoView(
                  _remoteRenderer,
                  objectFit: RTCVideoViewObjectFit.RTCVideoViewObjectFitCover,
                ),
              ),
              Positioned(
                top: 20,
                right: 20,
                child: Container(
                  width: 100,
                  height: 150,
                  decoration: BoxDecoration(
                    color: Colors.black54,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.white30, width: 1),
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(12),
                    child: RTCVideoView(
                      _localRenderer,
                      mirror: true,
                      objectFit: RTCVideoViewObjectFit.RTCVideoViewObjectFitCover,
                    ),
                  ),
                ),
              ),
            ],

            // ==========================================
            // محتوى الشاشة الرئيسي (صوتية)
            // ==========================================
            if (!_enableVideo || !_isConnected)
              Positioned.fill(
                child: Column(
                  children: [
                    const Spacer(flex: 2),
                    
                    // أيقونة المستخدم مع أنيميشن الرنين
                    ScaleTransition(
                      scale: _isCalling ? _pulseAnimation : const AlwaysStoppedAnimation(1.0),
                      child: Container(
                        width: 120,
                        height: 120,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: _isConnected 
                            ? const Color(0xFF00c853).withValues(alpha: 0.2)
                            : Colors.white.withValues(alpha: 0.1),
                          border: Border.all(
                            color: _isConnected 
                              ? const Color(0xFF00c853).withValues(alpha: 0.5)
                              : Colors.white24,
                            width: 3,
                          ),
                        ),
                        child: Icon(
                          _isConnected ? Icons.person : Icons.phone_in_talk,
                          size: 60,
                          color: _isConnected ? const Color(0xFF00c853) : Colors.white60,
                        ),
                      ),
                    ),

                    const SizedBox(height: 24),

                    // اسم المتصل
                    Text(
                      displayName,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 28,
                        fontWeight: FontWeight.bold,
                        letterSpacing: 0.5,
                      ),
                    ),

                    const SizedBox(height: 8),

                    // حالة الاتصال أو المدة
                    Text(
                      _isConnected 
                        ? _formatDuration(_callDuration) 
                        : _callStatus,
                      style: TextStyle(
                        color: _isConnected 
                          ? const Color(0xFF00c853) 
                          : Colors.white60,
                        fontSize: 16,
                        fontWeight: FontWeight.w500,
                      ),
                    ),

                    // وصف نوع المكالمة
                    if (_isCalling) ...[
                      const SizedBox(height: 4),
                      Text(
                        isRtl ? 'مكالمة صوتية' : 'Voice Call',
                        style: TextStyle(
                          color: Colors.white.withValues(alpha: 0.4),
                          fontSize: 13,
                        ),
                      ),
                    ],

                    const Spacer(flex: 3),
                  ],
                ),
              ),

            // ==========================================
            // أزرار التحكم السفلية
            // ==========================================
            Positioned(
              bottom: 50,
              left: 0,
              right: 0,
              child: Column(
                children: [
                  // صف الأزرار الثانوية
                  if (_isConnected)
                    Padding(
                      padding: const EdgeInsets.only(bottom: 30),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          // المايكروفون
                          _buildControlButton(
                            icon: _isMicOn ? Icons.mic : Icons.mic_off,
                            label: _isMicOn 
                              ? (isRtl ? 'كتم' : 'Mute') 
                              : (isRtl ? 'تشغيل' : 'Unmute'),
                            isActive: !_isMicOn,
                            onPressed: _toggleMic,
                          ),
                          const SizedBox(width: 40),
                          
                          // مكبر الصوت
                          _buildControlButton(
                            icon: _isSpeakerOn ? Icons.volume_up_rounded : Icons.volume_down_rounded,
                            label: isRtl ? 'مكبر' : 'Speaker',
                            isActive: _isSpeakerOn,
                            onPressed: _toggleSpeaker,
                          ),

                          if (_enableVideo) ...[
                            const SizedBox(width: 40),
                            _buildControlButton(
                              icon: _isVideoOn ? Icons.videocam : Icons.videocam_off,
                              label: isRtl ? 'كاميرا' : 'Camera',
                              isActive: !_isVideoOn,
                              onPressed: _toggleVideo,
                            ),
                            const SizedBox(width: 40),
                            _buildControlButton(
                              icon: Icons.switch_camera,
                              label: isRtl ? 'قلب' : 'Flip',
                              isActive: false,
                              onPressed: _switchCamera,
                            ),
                          ],
                        ],
                      ),
                    ),
                  
                  // زر إنهاء المكالمة الكبير
                  GestureDetector(
                    onTap: _hangup,
                    child: Container(
                      width: 70,
                      height: 70,
                      decoration: const BoxDecoration(
                        color: Colors.red,
                        shape: BoxShape.circle,
                        boxShadow: [
                          BoxShadow(
                            color: Colors.red,
                            blurRadius: 20,
                            spreadRadius: 2,
                          ),
                        ],
                      ),
                      child: const Icon(Icons.call_end, color: Colors.white, size: 34),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildControlButton({
    required IconData icon,
    required String label,
    required bool isActive,
    required VoidCallback onPressed,
  }) {
    return GestureDetector(
      onTap: onPressed,
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 55,
            height: 55,
            decoration: BoxDecoration(
              color: isActive ? Colors.white : Colors.white.withValues(alpha: 0.15),
              shape: BoxShape.circle,
            ),
            child: Icon(
              icon,
              color: isActive ? Colors.black : Colors.white,
              size: 26,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            label,
            style: TextStyle(
              color: Colors.white.withValues(alpha: 0.7),
              fontSize: 11,
            ),
          ),
        ],
      ),
    );
  }
}

// ==========================================
// شاشة الاتصال الوارد الاحترافية
// ==========================================
class IncomingCallScreen extends StatefulWidget {
  final String roomId;
  final String callerName;
  final UserModel currentUser;

  const IncomingCallScreen({
    super.key,
    required this.roomId,
    required this.callerName,
    required this.currentUser,
  });

  @override
  State<IncomingCallScreen> createState() => _IncomingCallScreenState();
}

class _IncomingCallScreenState extends State<IncomingCallScreen> with TickerProviderStateMixin {
  late AnimationController _pulseController;
  late Animation<double> _pulseAnimation;
  late AnimationController _slideController;
  late Animation<Offset> _slideAnimation;
  RealtimeChannel? _incomingChannel;
  Timer? _timeoutTimer;
  bool _isActionTaken = false;

  @override
  void initState() {
    super.initState();
    CallRingtoneService.isCallActive = true;
    // 🔔 تشغيل نغمة الرنين المميزة والاهتزاز فور وصول المكالمة
    CallRingtoneService.startIncomingRingtone();

    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1200),
    )..repeat(reverse: true);
    _pulseAnimation = Tween<double>(begin: 1.0, end: 1.2).animate(
      CurvedAnimation(parent: _pulseController, curve: Curves.easeInOut),
    );

    _slideController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 600),
    );
    _slideAnimation = Tween<Offset>(
      begin: const Offset(0, 1),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _slideController, curve: Curves.easeOutCubic));
    _slideController.forward();

    // الاستماع لإشارة الإلغاء إذا أنهى المتصل المكالمة قبل الرد
    _listenForHangup();

    // إيقاف الرنين وإغلاق الشاشة تلقائياً بعد 35 ثانية إذا لم يتم الرد
    _timeoutTimer = Timer(const Duration(seconds: 35), () {
      if (mounted && !_isActionTaken) {
        _isActionTaken = true;
        CallRingtoneService.stop();
        Navigator.pop(context);
      }
    });
  }

  void _listenForHangup() {
    try {
      _incomingChannel = Supabase.instance.client.channel('call_${widget.roomId}');
      _incomingChannel?.onBroadcast(
        event: 'webrtc_signal',
        callback: (payload) {
          if (!mounted || _isActionTaken) return;
          final senderId = payload['sender_id'];
          if (senderId == widget.currentUser.id) return;
          final data = payload['data'] as Map<String, dynamic>?;
          if (data != null && data['type'] == 'hangup') {
            _isActionTaken = true;
            CallRingtoneService.stop();
            if (mounted) Navigator.pop(context);
          }
        },
      ).subscribe();
    } catch (_) {}
  }

  void _cleanupIncomingChannel() {
    _timeoutTimer?.cancel();
    _timeoutTimer = null;
    if (_incomingChannel != null) {
      try {
        _incomingChannel!.unsubscribe();
        Supabase.instance.client.removeChannel(_incomingChannel!);
      } catch (_) {}
      _incomingChannel = null;
    }
  }

  @override
  void dispose() {
    CallRingtoneService.isCallActive = false;
    _isActionTaken = true;
    _cleanupIncomingChannel();
    CallRingtoneService.stop();
    _pulseController.dispose();
    _slideController.dispose();
    super.dispose();
  }

  void _acceptCall() async {
    if (_isActionTaken) return;

    final hasPermission = await CallPermissionHelper.requestMicrophonePermission(context);
    if (!hasPermission) return;

    if (!mounted) return;

    _isActionTaken = true;
    CallRingtoneService.stop();
    _cleanupIncomingChannel();

    Navigator.pop(context); // Close incoming call screen
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => CallScreen(
          callID: widget.roomId,
          currentUser: widget.currentUser,
          callerName: widget.callerName,
          isIncoming: true,
        ),
      ),
    );
  }

  void _rejectCall() {
    if (_isActionTaken) return;
    _isActionTaken = true;
    CallRingtoneService.stop();
    _cleanupIncomingChannel();

    // Send reject signal safely over WebSocket channel
    final channel = Supabase.instance.client.channel('call_${widget.roomId}');
    channel.subscribe((status, [error]) {
      if (status == RealtimeSubscribeStatus.subscribed) {
        try {
          channel.sendBroadcastMessage(
            event: 'webrtc_signal',
            payload: {
              'sender_id': widget.currentUser.id,
              'data': {'type': 'hangup'},
            },
          );
        } catch (_) {}
        Future.delayed(const Duration(milliseconds: 300), () {
          try {
            channel.unsubscribe();
            Supabase.instance.client.removeChannel(channel);
          } catch (_) {}
        });
      }
    });
    Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    
    return Scaffold(
      backgroundColor: const Color(0xFF1a1a2e),
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [
              Color(0xFF0F172A),
              Color(0xFF1E293B),
              Color(0xFF0F172A),
            ],
          ),
        ),
        child: SafeArea(
          child: Column(
            children: [
              const Spacer(flex: 2),

              // شارة "مكالمة واردة جديدة" مع تأثير الرنين
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.ring_volume_rounded, color: Color(0xFF38BDF8), size: 18),
                    const SizedBox(width: 8),
                    Text(
                      isRtl ? 'مكالمة واردة جديدة...' : 'Incoming Call...',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 14,
                        fontWeight: FontWeight.w600,
                        letterSpacing: 0.5,
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 36),

              // صورة أو أيقونة المتصل مع نبضات الرنين الدائرية
              ScaleTransition(
                scale: _pulseAnimation,
                child: Container(
                  width: 140,
                  height: 140,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: const Color(0xFF1E3A8A).withValues(alpha: 0.35),
                    border: Border.all(color: const Color(0xFF38BDF8).withValues(alpha: 0.6), width: 3),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFF38BDF8).withValues(alpha: 0.35),
                        blurRadius: 36,
                        spreadRadius: 8,
                      ),
                    ],
                  ),
                  child: const Center(
                    child: Icon(
                      Icons.person_rounded,
                      size: 76,
                      color: Colors.white,
                    ),
                  ),
                ),
              ),

              const SizedBox(height: 28),

              // اسم المتصل
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 24),
                child: Text(
                  NameFormatter.cleanPrefix(widget.callerName).isNotEmpty
                      ? NameFormatter.cleanPrefix(widget.callerName)
                      : (isRtl ? 'مستخدم' : 'User'),
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 28,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),

              const SizedBox(height: 10),

              // تسمية نوع المكالمة
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.graphic_eq_rounded, color: Color(0xFF4ADE80), size: 18),
                  const SizedBox(width: 6),
                  Text(
                    isRtl ? 'مكالمة صوتية عالية الدقة' : 'HD Voice Call',
                    style: TextStyle(
                      color: Colors.white.withValues(alpha: 0.75),
                      fontSize: 14,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ],
              ),

              const Spacer(flex: 3),

              // أزرار الرد والرفض
              SlideTransition(
                position: _slideAnimation,
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 50),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      // زر الرفض
                      Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          GestureDetector(
                            onTap: _rejectCall,
                            child: Container(
                              width: 72,
                              height: 72,
                              decoration: BoxDecoration(
                                gradient: const LinearGradient(
                                  colors: [Color(0xFFEF4444), Color(0xFFDC2626)],
                                  begin: Alignment.topLeft,
                                  end: Alignment.bottomRight,
                                ),
                                shape: BoxShape.circle,
                                boxShadow: [
                                  BoxShadow(
                                    color: const Color(0xFFEF4444).withValues(alpha: 0.5),
                                    blurRadius: 18,
                                    offset: const Offset(0, 6),
                                  ),
                                ],
                              ),
                              child: const Icon(Icons.call_end_rounded, color: Colors.white, size: 34),
                            ),
                          ),
                          const SizedBox(height: 12),
                          Text(
                            isRtl ? 'رفض' : 'Decline',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 15,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),

                      // زر الرد
                      Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          GestureDetector(
                            onTap: _acceptCall,
                            child: Container(
                              width: 72,
                              height: 72,
                              decoration: BoxDecoration(
                                gradient: const LinearGradient(
                                  colors: [Color(0xFF22C55E), Color(0xFF16A34A)],
                                  begin: Alignment.topLeft,
                                  end: Alignment.bottomRight,
                                ),
                                shape: BoxShape.circle,
                                boxShadow: [
                                  BoxShadow(
                                    color: const Color(0xFF22C55E).withValues(alpha: 0.5),
                                    blurRadius: 18,
                                    offset: const Offset(0, 6),
                                  ),
                                ],
                              ),
                              child: const Icon(Icons.call_rounded, color: Colors.white, size: 34),
                            ),
                          ),
                          const SizedBox(height: 12),
                          Text(
                            isRtl ? 'رد' : 'Accept',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 15,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 50),
            ],
          ),
        ),
      ),
    );
  }
}
