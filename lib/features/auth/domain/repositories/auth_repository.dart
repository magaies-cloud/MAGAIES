import 'package:magaiees_marketplace/features/auth/domain/models/user_model.dart';

/// Abstract contract for authentication operations.
///
/// Implementations can use Firebase Auth, Supabase,
/// or a custom backend API.
abstract class AuthRepository {
  /// Sign in with email and password.
  Future<UserModel> login({
    required String email,
    required String password,
  });

  /// Sign in as a guest user (unregistered).
  Future<UserModel> loginAsGuest();

  /// Register a new user account.
  Future<UserModel> register({
    required String name,
    required String nameAr,
    required String email,
    required String phone,
    required String password,
  });

  /// Sign out the current user.
  Future<void> logout();

  /// Refresh the current user's session/token.
  Future<UserModel> refreshSession();

  /// Get the currently cached user, if any.
  Future<UserModel?> getCachedUser();

  /// Persist user data locally.
  Future<void> cacheUser(UserModel user);

  /// Clear all cached user data.
  Future<void> clearCache();
}
