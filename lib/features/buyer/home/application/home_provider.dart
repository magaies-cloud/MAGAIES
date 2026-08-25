import 'dart:async';
import 'package:riverpod_annotation/riverpod_annotation.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/category_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/product_card_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/market_price_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/repositories/api_home_repository.dart';
import 'package:magaiees_marketplace/core/network/api_client.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

part 'home_provider.g.dart';

final apiClientProvider = Provider((ref) => ApiClient());
final apiHomeRepositoryProvider = Provider((ref) => ApiHomeRepository(ref.read(apiClientProvider)));

@Riverpod(keepAlive: true)
class Categories extends _$Categories {
  final _supabase = Supabase.instance.client;

  @override
  FutureOr<List<CategoryModel>> build() async {
    final channel = _supabase
        .channel('public:categories')
        .onPostgresChanges(
          event: PostgresChangeEvent.all,
          schema: 'public',
          table: 'categories',
          callback: (payload) {
            ref.invalidateSelf();
          },
        )
        .subscribe();

    ref.onDispose(() {
      channel.unsubscribe();
    });

    return ref.read(apiHomeRepositoryProvider).getCategories();
  }
}

@Riverpod(keepAlive: true)
class Products extends _$Products {
  final _supabase = Supabase.instance.client;

  @override
  FutureOr<List<ProductCardModel>> build() async {
    // Setup Realtime listener for instant price updates
    final channel = _supabase
        .channel('public:products')
        .onPostgresChanges(
          event: PostgresChangeEvent.all,
          schema: 'public',
          table: 'products',
          callback: (payload) {
            ref.invalidateSelf();
          },
        )
        .subscribe();

    ref.onDispose(() {
      channel.unsubscribe();
    });

    final response = await _supabase.from('products').select().order('created_at', ascending: false);
    
    // Fallback: If DB is empty, return empty list instead of mock for testing/showcase
    if (response.isEmpty) {
      return [];
    }

    final List<dynamic> data = response;
    return data.map((json) {
      return ProductCardModel(
        id: json['id'] ?? '',
        categoryId: json['category_id'] ?? '',
        categoryName: 'General',
        categoryNameAr: 'عام',
        name: json['name'] ?? '',
        nameAr: json['name_ar'] ?? '',
        imageUrl: (json['images'] != null && (json['images'] as List).isNotEmpty) ? json['images'][0] : (json['image_url'] ?? ''),
        pricePerTon: (json['price_sdg'] as num?)?.toDouble() ?? 0.0,
        priceInUsd: (json['price_usd'] as num?)?.toDouble(),
        unit: json['unit'] ?? 'ton',
        unitAr: json['unit_ar'] ?? 'طن',
        inStock: (json['is_active'] ?? true) && (((json['stock'] as num?) ?? 0) > 0),
        merchantId: json['store_id'] ?? 'magaiees',
        storeName: 'Magaiees',
        storeNameAr: 'مقاييس',
        isMagaieesExclusive: json['is_magaiees_exclusive'] ?? false,
      );
    }).toList();
  }

  Future<void> addProduct(ProductCardModel newProduct) async {
    final previousState = state;
    state = const AsyncLoading();
    try {
      final insertData = {
        'category_id': newProduct.categoryId,
        'name': newProduct.name,
        'name_ar': newProduct.nameAr,
        'price_sdg': newProduct.pricePerTon,
        'price_usd': newProduct.priceInUsd,
        'stock': newProduct.inStock ? 100 : 0, // ProductCardModel does not have a stock field; default to 100 if in stock
        'images': [newProduct.imageUrl ?? ''],
        'is_magaiees_exclusive': newProduct.isMagaieesExclusive,
      };
      await _supabase.from('products').insert(insertData);
      ref.invalidateSelf();
    } catch (e) {
      state = previousState;
      rethrow;
    }
  }

  Future<void> updateProduct(ProductCardModel updatedProduct) async {
    final previousState = state;
    try {
      if (state.valueOrNull != null) {
        state = AsyncData(state.value!.map((p) => p.id == updatedProduct.id ? updatedProduct : p).toList());
      }
      final updateData = {
        'name': updatedProduct.name,
        'name_ar': updatedProduct.nameAr,
        'price_sdg': updatedProduct.pricePerTon,
        'price_usd': updatedProduct.priceInUsd,
        'stock': updatedProduct.inStock ? 100 : 0,
        'is_magaiees_exclusive': updatedProduct.isMagaieesExclusive,
      };
      // Only update if it's a real UUID, mock IDs (prod_...) will fail
      if (!updatedProduct.id.startsWith('prod_')) {
        await _supabase.from('products').update(updateData).eq('id', updatedProduct.id);
      }
      ref.invalidateSelf();
    } catch (e) {
      state = previousState;
      rethrow;
    }
  }

  Future<void> deleteProduct(String id) async {
    final previousState = state;
    try {
      if (state.valueOrNull != null) {
        state = AsyncData(state.value!.where((p) => p.id != id).toList());
      }
      if (!id.startsWith('prod_')) {
        await _supabase.from('products').delete().eq('id', id);
      }
      ref.invalidateSelf();
    } catch (e) {
      state = previousState;
      rethrow;
    }
  }

  void applyGlobalPricing(double usdRate, {double? percentOffset}) {
    if (state.valueOrNull != null) {
      final list = state.value!.map((p) {
        double currentUsd = p.priceInUsd ?? (p.pricePerTon / usdRate);
        if (percentOffset != null) {
          currentUsd = currentUsd * (1 + percentOffset / 100);
        }
        return p.copyWith(
          priceInUsd: currentUsd,
          pricePerTon: currentUsd * usdRate,
        );
      }).toList();
      state = AsyncData(list);
    }
  }
}

@Riverpod(keepAlive: true)
class MarketPrices extends _$MarketPrices {
  @override
  FutureOr<List<MarketPriceModel>> build() {
    return ref.read(apiHomeRepositoryProvider).getMarketPrices();
  }
}

@riverpod
class SelectedCategory extends _$SelectedCategory {
  @override
  String? build() => null;
  
  void setCategory(String? categoryId) {
    state = categoryId;
  }
}

@riverpod
class SearchQuery extends _$SearchQuery {
  @override
  String build() => '';

  void setQuery(String query) {
    state = query;
  }
}

@riverpod
Future<List<ProductCardModel>> filteredProducts(FilteredProductsRef ref) async {
  final products = await ref.watch(productsProvider.future);
  final selectedCategory = ref.watch(selectedCategoryProvider);
  final searchQuery = ref.watch(searchQueryProvider).toLowerCase();

  return products.where((product) {
    final matchesCategory = selectedCategory == null || product.categoryId == selectedCategory;
    final matchesSearch = searchQuery.isEmpty || 
        product.name.toLowerCase().contains(searchQuery) ||
        product.nameAr.contains(searchQuery);
    return matchesCategory && matchesSearch && product.inStock;
  }).toList();
}
