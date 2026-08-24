/// Base exception class for application errors.
sealed class AppException implements Exception {
  const AppException(this.message, [this.code]);

  final String message;
  final String? code;

  @override
  String toString() => 'AppException($code): $message';
}

/// Network-related exceptions.
class NetworkException extends AppException {
  const NetworkException([super.message = 'Network error occurred', super.code]);
}

/// Authentication exceptions.
class AuthException extends AppException {
  const AuthException([super.message = 'Authentication failed', super.code]);
}

/// Server exceptions with HTTP status.
class ServerException extends AppException {
  const ServerException(super.message, [super.code, this.statusCode]);
  final int? statusCode;
}

/// Local storage exceptions.
class CacheException extends AppException {
  const CacheException([super.message = 'Cache error occurred', super.code]);
}

/// Validation exceptions.
class ValidationException extends AppException {
  const ValidationException(super.message, [super.code, this.fieldErrors]);
  final Map<String, String>? fieldErrors;
}
