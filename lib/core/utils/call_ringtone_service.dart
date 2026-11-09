import 'dart:async';
import 'package:audioplayers/audioplayers.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';

/// Service dedicated to managing phone call ringtones (incoming & outgoing)
class CallRingtoneService {
  static AudioPlayer? _player;
  static bool isCallActive = false;
  static bool _isPlayingIncoming = false;
  static bool _isPlayingOutgoing = false;
  static Timer? _vibrateTimer;

  static Future<AudioPlayer> _getPlayer() async {
    if (_player == null) {
      _player = AudioPlayer();
      await _player!.setReleaseMode(ReleaseMode.loop);
      if (!kIsWeb) {
        await _player!.setPlayerMode(PlayerMode.mediaPlayer);
      }
    }
    return _player!;
  }

  /// Start playing the distinctive incoming call ringtone continuously
  static Future<void> startIncomingRingtone() async {
    if (_isPlayingIncoming) return;
    _isPlayingIncoming = true;
    _isPlayingOutgoing = false;

    try {
      final player = await _getPlayer();
      await player.stop();
      await player.setReleaseMode(ReleaseMode.loop);
      await player.setVolume(1.0);
      await player.play(AssetSource('audio/magaiees_call_ringtone.wav'));

      // Continuous rhythmic vibration on mobile devices
      _startVibrationLoop();
    } catch (e) {
      debugPrint('Incoming ringtone play note: $e');
    }
  }

  /// Start playing the outgoing dial/ringback tone for caller
  static Future<void> startOutgoingTone() async {
    if (_isPlayingOutgoing) return;
    _isPlayingOutgoing = true;
    _isPlayingIncoming = false;

    try {
      final player = await _getPlayer();
      await player.stop();
      await player.setReleaseMode(ReleaseMode.loop);
      await player.setVolume(0.85);
      await player.play(AssetSource('audio/magaiees_call_outgoing.wav'));
    } catch (e) {
      debugPrint('Outgoing tone play note: $e');
    }
  }

  /// Stop all ringing and vibrations immediately
  static Future<void> stop() async {
    _isPlayingIncoming = false;
    _isPlayingOutgoing = false;
    _vibrateTimer?.cancel();
    _vibrateTimer = null;

    try {
      if (_player != null) {
        await _player!.stop();
      }
    } catch (e) {
      debugPrint('Ringtone stop note: $e');
    }
  }

  static void _startVibrationLoop() {
    _vibrateTimer?.cancel();
    if (kIsWeb) return;

    try {
      HapticFeedback.vibrate();
      _vibrateTimer = Timer.periodic(const Duration(milliseconds: 1400), (_) {
        if (!_isPlayingIncoming) {
          _vibrateTimer?.cancel();
          return;
        }
        try {
          HapticFeedback.vibrate();
        } catch (_) {}
      });
    } catch (_) {}
  }

  /// Dispose the player when app closes
  static void dispose() {
    stop();
    _player?.dispose();
    _player = null;
  }
}
