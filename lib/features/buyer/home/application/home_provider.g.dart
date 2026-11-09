// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'home_provider.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

String _$filteredProductsHash() => r'deac3a9033a2df6f378f75d63580ecd6be72143f';

/// See also [filteredProducts].
@ProviderFor(filteredProducts)
final filteredProductsProvider =
    AutoDisposeFutureProvider<List<ProductCardModel>>.internal(
      filteredProducts,
      name: r'filteredProductsProvider',
      debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
          ? null
          : _$filteredProductsHash,
      dependencies: null,
      allTransitiveDependencies: null,
    );

@Deprecated('Will be removed in 3.0. Use Ref instead')
// ignore: unused_element
typedef FilteredProductsRef =
    AutoDisposeFutureProviderRef<List<ProductCardModel>>;
String _$categoriesHash() => r'6a1519e8eb11cc31c7309ba427ac794edad23f97';

/// See also [Categories].
@ProviderFor(Categories)
final categoriesProvider =
    AsyncNotifierProvider<Categories, List<CategoryModel>>.internal(
      Categories.new,
      name: r'categoriesProvider',
      debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
          ? null
          : _$categoriesHash,
      dependencies: null,
      allTransitiveDependencies: null,
    );

typedef _$Categories = AsyncNotifier<List<CategoryModel>>;
String _$productsHash() => r'98167092266fa904c0864c01f1adbe52dffbaa4e';

/// See also [Products].
@ProviderFor(Products)
final productsProvider =
    AsyncNotifierProvider<Products, List<ProductCardModel>>.internal(
      Products.new,
      name: r'productsProvider',
      debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
          ? null
          : _$productsHash,
      dependencies: null,
      allTransitiveDependencies: null,
    );

typedef _$Products = AsyncNotifier<List<ProductCardModel>>;
String _$marketPricesHash() => r'8eb177db707e0f1332337181e7c073d137e00ac4';

/// See also [MarketPrices].
@ProviderFor(MarketPrices)
final marketPricesProvider =
    AsyncNotifierProvider<MarketPrices, List<MarketPriceModel>>.internal(
      MarketPrices.new,
      name: r'marketPricesProvider',
      debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
          ? null
          : _$marketPricesHash,
      dependencies: null,
      allTransitiveDependencies: null,
    );

typedef _$MarketPrices = AsyncNotifier<List<MarketPriceModel>>;
String _$selectedCategoryHash() => r'6c58a81f9c4c5fc645a6020bd46b3850f3e7163c';

/// See also [SelectedCategory].
@ProviderFor(SelectedCategory)
final selectedCategoryProvider =
    AutoDisposeNotifierProvider<SelectedCategory, String?>.internal(
      SelectedCategory.new,
      name: r'selectedCategoryProvider',
      debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
          ? null
          : _$selectedCategoryHash,
      dependencies: null,
      allTransitiveDependencies: null,
    );

typedef _$SelectedCategory = AutoDisposeNotifier<String?>;
String _$searchQueryHash() => r'2c146927785523a0ddf51b23b777a9be4afdc092';

/// See also [SearchQuery].
@ProviderFor(SearchQuery)
final searchQueryProvider =
    AutoDisposeNotifierProvider<SearchQuery, String>.internal(
      SearchQuery.new,
      name: r'searchQueryProvider',
      debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
          ? null
          : _$searchQueryHash,
      dependencies: null,
      allTransitiveDependencies: null,
    );

typedef _$SearchQuery = AutoDisposeNotifier<String>;
// ignore_for_file: type=lint
// ignore_for_file: subtype_of_sealed_class, invalid_use_of_internal_member, invalid_use_of_visible_for_testing_member, deprecated_member_use_from_same_package
