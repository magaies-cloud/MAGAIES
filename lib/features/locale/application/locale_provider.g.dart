// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'locale_provider.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

String _$sharedPreferencesHash() => r'7c866814d54d8f9fb05ee9b30c57e1c2cb904f49';

/// Provides the [SharedPreferences] instance.
///
/// Must be overridden in the ProviderScope during bootstrap.
///
/// Copied from [sharedPreferences].
@ProviderFor(sharedPreferences)
final sharedPreferencesProvider = Provider<SharedPreferences>.internal(
  sharedPreferences,
  name: r'sharedPreferencesProvider',
  debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
      ? null
      : _$sharedPreferencesHash,
  dependencies: null,
  allTransitiveDependencies: null,
);

@Deprecated('Will be removed in 3.0. Use Ref instead')
// ignore: unused_element
typedef SharedPreferencesRef = ProviderRef<SharedPreferences>;
String _$currentSupportedLocaleHash() =>
    r'8ed886d098f24f16920d7c5da8e1a24d06298cec';

/// The current [SupportedLocale] enum value.
///
/// Copied from [currentSupportedLocale].
@ProviderFor(currentSupportedLocale)
final currentSupportedLocaleProvider =
    AutoDisposeProvider<SupportedLocale>.internal(
      currentSupportedLocale,
      name: r'currentSupportedLocaleProvider',
      debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
          ? null
          : _$currentSupportedLocaleHash,
      dependencies: null,
      allTransitiveDependencies: null,
    );

@Deprecated('Will be removed in 3.0. Use Ref instead')
// ignore: unused_element
typedef CurrentSupportedLocaleRef = AutoDisposeProviderRef<SupportedLocale>;
String _$isRtlHash() => r'56b68fd7737062432de54a857d306e0c4683b82d';

/// Whether the current locale is RTL.
///
/// Copied from [isRtl].
@ProviderFor(isRtl)
final isRtlProvider = AutoDisposeProvider<bool>.internal(
  isRtl,
  name: r'isRtlProvider',
  debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
      ? null
      : _$isRtlHash,
  dependencies: null,
  allTransitiveDependencies: null,
);

@Deprecated('Will be removed in 3.0. Use Ref instead')
// ignore: unused_element
typedef IsRtlRef = AutoDisposeProviderRef<bool>;
String _$localeNotifierHash() => r'72f1f0bc5c966b4c4409e1679101ab576e7d8330';

/// Manages the application locale (Arabic / English).
///
/// The selected locale is persisted to [SharedPreferences]
/// so it survives app restarts. Default locale is Arabic (ar).
///
/// Copied from [LocaleNotifier].
@ProviderFor(LocaleNotifier)
final localeNotifierProvider =
    NotifierProvider<LocaleNotifier, Locale>.internal(
      LocaleNotifier.new,
      name: r'localeNotifierProvider',
      debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
          ? null
          : _$localeNotifierHash,
      dependencies: null,
      allTransitiveDependencies: null,
    );

typedef _$LocaleNotifier = Notifier<Locale>;
// ignore_for_file: type=lint
// ignore_for_file: subtype_of_sealed_class, invalid_use_of_internal_member, invalid_use_of_visible_for_testing_member, deprecated_member_use_from_same_package
