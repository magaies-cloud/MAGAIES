// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'mode_provider.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

String _$isMerchantModeHash() => r'f5f779453f99ea16bb5e0dd95d913ee4f419f8e5';

/// Whether the user is currently in merchant mode.
///
/// Copied from [isMerchantMode].
@ProviderFor(isMerchantMode)
final isMerchantModeProvider = AutoDisposeProvider<bool>.internal(
  isMerchantMode,
  name: r'isMerchantModeProvider',
  debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
      ? null
      : _$isMerchantModeHash,
  dependencies: null,
  allTransitiveDependencies: null,
);

@Deprecated('Will be removed in 3.0. Use Ref instead')
// ignore: unused_element
typedef IsMerchantModeRef = AutoDisposeProviderRef<bool>;
String _$modeNotifierHash() => r'3509f961333d7422eff36a012cd0fa4fbe0a18b4';

/// Manages the current UI mode (Buyer vs Merchant).
///
/// The mode determines which navigation shell and screens
/// the user sees. A user can only switch to [AppMode.merchant]
/// if they have an approved store ([hasStore] is true).
///
/// Copied from [ModeNotifier].
@ProviderFor(ModeNotifier)
final modeNotifierProvider = NotifierProvider<ModeNotifier, AppMode>.internal(
  ModeNotifier.new,
  name: r'modeNotifierProvider',
  debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
      ? null
      : _$modeNotifierHash,
  dependencies: null,
  allTransitiveDependencies: null,
);

typedef _$ModeNotifier = Notifier<AppMode>;
// ignore_for_file: type=lint
// ignore_for_file: subtype_of_sealed_class, invalid_use_of_internal_member, invalid_use_of_visible_for_testing_member, deprecated_member_use_from_same_package
