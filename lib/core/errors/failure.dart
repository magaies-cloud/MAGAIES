/// Represents a handled failure in the domain layer.
/// Unlike exceptions, failures are expected and handled gracefully.
sealed class Failure {
  const Failure(this.message, [this.code]);

  final String message;
  final String? code;

  @override
  String toString() => 'Failure($code): $message';
}

class ServerFailure extends Failure {
  const ServerFailure([super.message = 'Server error', super.code]);
}

class NetworkFailure extends Failure {
  const NetworkFailure([super.message = 'No internet connection', super.code]);
}

class CacheFailure extends Failure {
  const CacheFailure([super.message = 'Local storage error', super.code]);
}

class AuthFailure extends Failure {
  const AuthFailure([super.message = 'Authentication failed', super.code]);
}

class ValidationFailure extends Failure {
  const ValidationFailure(super.message, [super.code]);
}
