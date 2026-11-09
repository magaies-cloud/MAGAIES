// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'auth_provider.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

String _$authRepositoryHash() => r'ef11f659225833e699ba021ad1bb222bc938c36d';

/// Provides the [AuthRepository] implementation.
///
/// Copied from [authRepository].
@ProviderFor(authRepository)
final authRepositoryProvider = Provider<AuthRepository>.internal(
  authRepository,
  name: r'authRepositoryProvider',
  debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
      ? null
      : _$authRepositoryHash,
  dependencies: null,
  allTransitiveDependencies: null,
);

@Deprecated('Will be removed in 3.0. Use Ref instead')
// ignore: unused_element
typedef AuthRepositoryRef = ProviderRef<AuthRepository>;
String _$isAuthenticatedHash() => r'b6a54a55a6f89d0aa4b31dc0d4ca74fa63ddbb5b';

/// Whether the user is currently authenticated.
///
/// Copied from [isAuthenticated].
@ProviderFor(isAuthenticated)
final isAuthenticatedProvider = AutoDisposeProvider<bool>.internal(
  isAuthenticated,
  name: r'isAuthenticatedProvider',
  debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
      ? null
      : _$isAuthenticatedHash,
  dependencies: null,
  allTransitiveDependencies: null,
);

@Deprecated('Will be removed in 3.0. Use Ref instead')
// ignore: unused_element
typedef IsAuthenticatedRef = AutoDisposeProviderRef<bool>;
String _$hasStoreHash() => r'd9550ff3f23b68e5f2d0d841b8f586eb121e819f';

/// Whether the authenticated user has an approved store.
///
/// Copied from [hasStore].
@ProviderFor(hasStore)
final hasStoreProvider = AutoDisposeProvider<bool>.internal(
  hasStore,
  name: r'hasStoreProvider',
  debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
      ? null
      : _$hasStoreHash,
  dependencies: null,
  allTransitiveDependencies: null,
);

@Deprecated('Will be removed in 3.0. Use Ref instead')
// ignore: unused_element
typedef HasStoreRef = AutoDisposeProviderRef<bool>;
String _$currentUserNameHash() => r'45381339ff6d32089c8a58c3d60e58fd4e8f17cf';

/// The current user's display name (locale-aware).
///
/// Copied from [currentUserName].
@ProviderFor(currentUserName)
final currentUserNameProvider = AutoDisposeProvider<String>.internal(
  currentUserName,
  name: r'currentUserNameProvider',
  debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
      ? null
      : _$currentUserNameHash,
  dependencies: null,
  allTransitiveDependencies: null,
);

@Deprecated('Will be removed in 3.0. Use Ref instead')
// ignore: unused_element
typedef CurrentUserNameRef = AutoDisposeProviderRef<String>;
String _$authNotifierHash() => r'ae238a753c6bdfa331fbf33b5cc53d83217b314c';

/// Manages the authentication state of the application.
///
/// Holds the current [UserModel] or `null` if unauthenticated.
/// All auth operations (login, register, logout) go through this notifier.
///
/// Copied from [AuthNotifier].
@ProviderFor(AuthNotifier)
final authNotifierProvider =
    AsyncNotifierProvider<AuthNotifier, UserModel?>.internal(
      AuthNotifier.new,
      name: r'authNotifierProvider',
      debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
          ? null
          : _$authNotifierHash,
      dependencies: null,
      allTransitiveDependencies: null,
    );

typedef _$AuthNotifier = AsyncNotifier<UserModel?>;
// ignore_for_file: type=lint
// ignore_for_file: subtype_of_sealed_class, invalid_use_of_internal_member, invalid_use_of_visible_for_testing_member, deprecated_member_use_from_same_package
