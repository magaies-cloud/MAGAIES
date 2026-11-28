import 'dart:async';
import 'package:flutter/foundation.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

/// Checks if an error is specifically caused by clock skew between client/auth and PostgreSQL (PGRST303).
bool isClockSkewError(dynamic error) {
  if (error == null) return false;
  final str = error.toString().toLowerCase();
  if (error is PostgrestException) {
    if (error.code == 'PGRST303') return true;
    if (error.message.toLowerCase().contains('issued at future')) return true;
  }
  return str.contains('pgrst303') || str.contains('jwt issued at future') || str.contains('issued at future');
}

/// Checks if an error is due to an expired JWT token (PGRST301).
bool isJwtExpiredError(dynamic error) {
  if (error == null) return false;
  final str = error.toString().toLowerCase();
  if (error is PostgrestException) {
    if (error.code == 'PGRST301') return true;
    if (error.message.toLowerCase().contains('jwt expired')) return true;
  }
  return str.contains('pgrst301') || str.contains('jwt expired');
}

/// Checks if an error is a transient socket/network error (e.g. Bad file descriptor, connection reset).
bool isTransientNetworkError(dynamic error) {
  if (error == null) return false;
  final str = error.toString().toLowerCase();
  return str.contains('bad file descriptor') ||
      str.contains('connection closed') ||
      str.contains('connection reset') ||
      str.contains('connection abort') ||
      str.contains('broken pipe') ||
      str.contains('socketexception') ||
      str.contains('handshakeexception') ||
      str.contains('clientexception') ||
      str.contains('semaphore timeout');
}

/// Executes a Supabase query with automatic retry on clock skew (PGRST303), token expiration (PGRST301),
/// or transient socket errors (Bad file descriptor / connection closed).
Future<T> safeSupabaseQuery<T>(
  Future<T> Function() queryFn, {
  int maxRetries = 4,
  Duration initialDelay = const Duration(milliseconds: 800),
  T Function()? fallback,
}) async {
  int attempt = 0;
  while (true) {
    attempt++;
    try {
      return await queryFn();
    } catch (e) {
      final bool isClockSkew = isClockSkewError(e);
      final bool isExpired = isJwtExpiredError(e);
      final bool isTransientNet = isTransientNetworkError(e);

      if ((isClockSkew || isExpired || isTransientNet) && attempt <= maxRetries) {
        final delayMs = isTransientNet
            ? (350 * attempt)
            : (initialDelay.inMilliseconds * attempt);
        debugPrint(
          '[safeSupabaseQuery] Caught transient error (clockSkew=$isClockSkew, expired=$isExpired, '
          'transientNet=$isTransientNet). Attempt $attempt of $maxRetries. Waiting ${delayMs}ms before retry...',
        );

        // IMPORTANT: Only refresh session when token is EXPIRED (PGRST301).
        // Do NOT refresh on clock skew (PGRST303), because refreshing mints a new token
        // with an even newer 'iat' timestamp that would fail PGRST303 again!
        if (isExpired && !isClockSkew) {
          try {
            final session = Supabase.instance.client.auth.currentSession;
            if (session != null) {
              await Supabase.instance.client.auth.refreshSession();
              debugPrint('[safeSupabaseQuery] Expired auth session refreshed successfully.');
            }
          } catch (refreshErr) {
            debugPrint('[safeSupabaseQuery] Session refresh attempt failed: $refreshErr');
          }
        }

        await Future.delayed(Duration(milliseconds: delayMs));
        continue;
      }

      debugPrint('[safeSupabaseQuery] Query failed permanently on attempt $attempt: $e');
      if (fallback != null) {
        debugPrint('[safeSupabaseQuery] Returning provided fallback.');
        return fallback();
      }
      rethrow;
    }
  }
}
