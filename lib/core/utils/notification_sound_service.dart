import 'package:audioplayers/audioplayers.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';
import 'package:magaiees_marketplace/core/utils/call_ringtone_service.dart';

/// Centralized notification sound service for Magaiees app.
/// Plays the custom Magaiees notification tone at key order, approval, and chat lifecycle events.
class NotificationSoundService {
  static AudioPlayer? _player;
  static bool _isPlaying = false;

  static Future<AudioPlayer> _getConfiguredPlayer() async {
    if (_player == null) {
      _player = AudioPlayer();
      await _player!.setReleaseMode(ReleaseMode.stop);
      if (!kIsWeb) {
        await _player!.setPlayerMode(PlayerMode.mediaPlayer);
        try {
          await _player!.setAudioContext(
            AudioContext(
              iOS: AudioContextIOS(
                category: AVAudioSessionCategory.playback,
                options: const {
                  AVAudioSessionOptions.mixWithOthers,
                },
              ),
              android: const AudioContextAndroid(
                isSpeakerphoneOn: true,
                stayAwake: false,
                contentType: AndroidContentType.sonification,
                usageType: AndroidUsageType.notificationEvent,
                audioFocus: AndroidAudioFocus.gainTransientMayDuck,
              ),
            ),
          );
        } catch (e) {
          debugPrint('Notification AudioContext note: $e');
        }
      }
    }
    return _player!;
  }

  /// Play the Magaiees notification sound with haptic vibration on mobile.
  static Future<void> play() async {
    if (CallRingtoneService.isCallActive) return;
    if (_isPlaying) return;
    _isPlaying = true;

    try {
      if (!kIsWeb) {
        try {
          HapticFeedback.mediumImpact();
        } catch (_) {}
      }

      final player = await _getConfiguredPlayer();
      await player.stop();
      await player.setVolume(1.0);
      try {
        await player.play(AssetSource('audio/magaiees_notify.wav'), volume: 1.0);
      } catch (_) {
        await player.play(AssetSource('audio/magaiees_notify.mp3'), volume: 1.0);
      }
    } catch (e) {
      debugPrint('Notification sound note: $e');
    } finally {
      Future.delayed(const Duration(milliseconds: 1200), () {
        _isPlaying = false;
      });
    }
  }

  /// Dispose the audio player (call on app shutdown if needed).
  static void dispose() {
    _player?.dispose();
    _player = null;
  }
}
