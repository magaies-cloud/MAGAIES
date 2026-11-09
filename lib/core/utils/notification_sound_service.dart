import 'package:audioplayers/audioplayers.dart';
import 'package:flutter/foundation.dart';

/// Centralized notification sound service for Magaiees app.
/// Plays the custom Magaiees notification tone at key order lifecycle events.
class NotificationSoundService {
  static AudioPlayer? _player;
  static bool _isPlaying = false;

  /// Play the Magaiees notification sound.
  static Future<void> play() async {
    if (_isPlaying) return;
    _isPlaying = true;
    try {
      if (_player == null) {
        _player = AudioPlayer();
        if (!kIsWeb) {
          await _player!.setPlayerMode(PlayerMode.lowLatency);
        }
      }
      await _player!.stop();
      await _player!.play(AssetSource('audio/magaiees_notify.mp3'), volume: 1.0);
    } catch (e) {
      // Gracefully handle browser autoplay policy restriction
      debugPrint('Notification sound note: $e');
    } finally {
      Future.delayed(const Duration(milliseconds: 1500), () {
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
