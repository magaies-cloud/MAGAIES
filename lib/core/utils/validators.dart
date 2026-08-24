/// Common form validators for the application.
abstract final class Validators {
  /// Validates an email address.
  static String? email(String? value) {
    if (value == null || value.isEmpty) return 'Email is required';
    final regex = RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$');
    if (!regex.hasMatch(value)) return 'Invalid email format';
    return null;
  }

  /// Validates a phone number (supports Saudi/UAE formats).
  static String? phone(String? value) {
    if (value == null || value.isEmpty) return 'Phone number is required';
    final regex = RegExp(r'^\+?[0-9]{9,15}$');
    if (!regex.hasMatch(value)) return 'Invalid phone number';
    return null;
  }

  /// Validates a required field.
  static String? required(String? value, [String fieldName = 'This field']) {
    if (value == null || value.trim().isEmpty) {
      return '$fieldName is required';
    }
    return null;
  }

  /// Validates a positive number.
  static String? positiveNumber(String? value, [String fieldName = 'Value']) {
    if (value == null || value.isEmpty) return '$fieldName is required';
    final number = double.tryParse(value);
    if (number == null || number <= 0) return '$fieldName must be positive';
    return null;
  }

  /// Validates a password (min 8 chars).
  static String? password(String? value) {
    if (value == null || value.isEmpty) return 'Password is required';
    if (value.length < 8) return 'Password must be at least 8 characters';
    return null;
  }
}
