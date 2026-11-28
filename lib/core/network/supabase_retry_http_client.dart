import 'dart:async';
import 'package:flutter/foundation.dart';
// ignore: depend_on_referenced_packages
import 'package:http/http.dart' as http;

/// A resilient HTTP client for Supabase that automatically recovers from
/// stale pooled keep-alive sockets on Android/iOS (e.g. `ClientException: Bad file descriptor`,
/// `Connection closed before full header was received`, `Connection reset by peer`,
/// `Software caused connection abort`).
///
/// When a socket error occurs, it flushes the underlying `http.Client` connection pool
/// by recreating the inner client and transparently retries the request.
class SupabaseRetryHttpClient extends http.BaseClient {
  http.Client _inner = http.Client();
  final int maxRetries;

  SupabaseRetryHttpClient({this.maxRetries = 3});

  static bool isTransientSocketError(Object error) {
    final msg = error.toString().toLowerCase();
    return msg.contains('bad file descriptor') ||
        msg.contains('connection closed') ||
        msg.contains('connection reset') ||
        msg.contains('connection abort') ||
        msg.contains('broken pipe') ||
        msg.contains('socketexception') ||
        msg.contains('handshakeexception') ||
        msg.contains('clientexception') ||
        msg.contains('semaphore timeout');
  }

  void _resetInnerClient() {
    final old = _inner;
    _inner = http.Client();
    try {
      old.close();
    } catch (_) {}
  }

  http.Request _cloneRequest(http.Request original, {bool disableKeepAlive = false}) {
    final copy = http.Request(original.method, original.url)
      ..followRedirects = original.followRedirects
      ..maxRedirects = original.maxRedirects
      ..persistentConnection = !disableKeepAlive && original.persistentConnection
      ..headers.addAll(original.headers)
      ..bodyBytes = original.bodyBytes;
    if (disableKeepAlive) {
      copy.headers['Connection'] = 'close';
    }
    return copy;
  }

  @override
  Future<http.StreamedResponse> send(http.BaseRequest request) async {
    // Only standard http.Request (used by PostgREST, GoTrue, Functions) can be safely cloned and retried.
    if (request is! http.Request) {
      try {
        return await _inner.send(request);
      } catch (e) {
        if (isTransientSocketError(e)) {
          _resetInnerClient();
        }
        rethrow;
      }
    }

    int attempt = 0;
    while (true) {
      attempt++;
      try {
        final reqCopy = _cloneRequest(
          request,
          disableKeepAlive: attempt > 1,
        );
        return await _inner.send(reqCopy);
      } catch (e) {
        if (isTransientSocketError(e) && attempt <= maxRetries) {
          debugPrint(
            '[SupabaseRetryHttpClient] Transient socket error on attempt $attempt/$maxRetries '
            '(${request.method} ${request.url}): $e. Resetting connection pool and retrying...',
          );
          _resetInnerClient();
          await Future.delayed(Duration(milliseconds: 250 * attempt));
          continue;
        }
        rethrow;
      }
    }
  }

  @override
  void close() {
    try {
      _inner.close();
    } catch (_) {}
    super.close();
  }
}
