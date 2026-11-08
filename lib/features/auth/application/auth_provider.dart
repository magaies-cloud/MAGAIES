import 'dart:async';
import 'package:riverpod_annotation/riverpod_annotation.dart';
import 'package:magaiees_marketplace/features/auth/domain/models/user_model.dart';
import 'package:magaiees_marketplace/features/auth/domain/repositories/auth_repository.dart';
import 'package:magaiees_marketplace/features/auth/data/supabase_auth_repository.dart';
import 'package:magaiees_marketplace/features/locale/application/locale_provider.dart';

part 'auth_provider.g.dart';

/// Provides the [AuthRepository] implementation.
@Riverpod(keepAlive: true)
AuthRepository authRepository(AuthRepositoryRef ref) {
  final prefs = ref.watch(sharedPreferencesProvider);
  return SupabaseAuthRepository(prefs);
}

/// Manages the authentication state of the application.
///
/// Holds the current [UserModel] or `null` if unauthenticated.
/// All auth operations (login, register, logout) go through this notifier.
@Riverpod(keepAlive: true)
class AuthNotifier extends _$AuthNotifier {
  late AuthRepository _repository;

  @override
  FutureOr<UserModel?> build() async {
    _repository = ref.watch(authRepositoryProvider);
    // Try to restore cached session on app startup
    return _repository.getCachedUser();
  }

  /// Sign in with email and password.
  Future<void> login({
    required String email,
    required String password,
  }) async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(() async {
      final user = await _repository.login(
        email: email,
        password: password,
      );
      await _repository.cacheUser(user);
      return user;
    });
  }

  /// Sign in as a guest (unregistered).
  Future<void> loginAsGuest() async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(() async {
      final user = await _repository.loginAsGuest();
      await _repository.cacheUser(user);
      return user;
    });
  }

  /// Register a new user account.
  Future<void> register({
    required String name,
    required String nameAr,
    required String email,
    required String phone,
    required String password,
  }) async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(() async {
      final user = await _repository.register(
        name: name,
        nameAr: nameAr,
        email: email,
        phone: phone,
        password: password,
      );
      await _repository.cacheUser(user);
      return user;
    });
  }

  /// Sign out and clear all cached data.
  Future<void> logout() async {
    await _repository.logout();
    await _repository.clearCache();
    state = const AsyncData(null);
  }

  /// Refresh the current session without losing state on transient errors.
  Future<void> refreshSession() async {
    final previousUser = state.valueOrNull;
    try {
      final updatedUser = await _repository.refreshSession();
      await _repository.cacheUser(updatedUser);
      state = AsyncData(updatedUser);
    } catch (_) {
      if (previousUser != null) {
        state = AsyncData(previousUser);
      }
    }
  }
}

/// Whether the user is currently authenticated.
@riverpod
bool isAuthenticated(IsAuthenticatedRef ref) {
  final authState = ref.watch(authNotifierProvider);
  return authState.valueOrNull != null;
}

/// Whether the authenticated user has an approved store.
@riverpod
bool hasStore(HasStoreRef ref) {
  final user = ref.watch(authNotifierProvider).valueOrNull;
  return user?.hasStore ?? false;
}

/// The current user's display name (locale-aware).
@riverpod
String currentUserName(CurrentUserNameRef ref) {
  final user = ref.watch(authNotifierProvider).valueOrNull;
  if (user == null) return '';
  final isArabic = ref.watch(isArabicProvider);
  if (isArabic && user.nameAr != null && user.nameAr!.trim().isNotEmpty) {
    return user.nameAr!;
  }
  return user.name;
}
