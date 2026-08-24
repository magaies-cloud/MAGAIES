// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'router_notifier.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

String _$routerNotifierHash() => r'239df8ad1a0b6cf7b0759cb502e89cb7f10d3f83';

/// A [ChangeNotifier] that bridges Riverpod state to [GoRouter.refreshListenable].
///
/// Listens to both auth state and mode changes, notifying the router
/// to re-evaluate its redirect logic whenever either changes.
///
/// Copied from [routerNotifier].
@ProviderFor(routerNotifier)
final routerNotifierProvider =
    AutoDisposeProvider<Raw<RouterNotifier>>.internal(
      routerNotifier,
      name: r'routerNotifierProvider',
      debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
          ? null
          : _$routerNotifierHash,
      dependencies: null,
      allTransitiveDependencies: null,
    );

@Deprecated('Will be removed in 3.0. Use Ref instead')
// ignore: unused_element
typedef RouterNotifierRef = AutoDisposeProviderRef<Raw<RouterNotifier>>;
// ignore_for_file: type=lint
// ignore_for_file: subtype_of_sealed_class, invalid_use_of_internal_member, invalid_use_of_visible_for_testing_member, deprecated_member_use_from_same_package
