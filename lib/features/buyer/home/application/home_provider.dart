import 'package:flutter/foundation.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/sub_category_model.dart';
import 'dart:async';
import 'package:riverpod_annotation/riverpod_annotation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart' show StateProvider;
import 'package:magaiees_marketplace/features/buyer/home/domain/models/category_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/product_card_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/market_price_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/repositories/api_home_repository.dart';
import 'package:magaiees_marketplace/core/network/api_client.dart';
import 'package:magaiees_marketplace/core/network/supabase_safe_query.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/core/providers/global_settings_provider.dart';
import 'package:magaiees_marketplace/core/utils/unit_formatter.dart';

part 'home_provider.g.dart';

final apiClientProvider = Provider((ref) => ApiClient());
final apiHomeRepositoryProvider = Provider((ref) => ApiHomeRepository(ref.read(apiClientProvider)));

@Riverpod(keepAlive: true)
class Categories extends _$Categories {
  final _supabase = Supabase.instance.client;

  void swapCategoriesLocally(String id1, String id2) {
    if (state.valueOrNull == null) return;
    final list = List<CategoryModel>.from(state.valueOrNull!);
    final idx1 = list.indexWhere((c) => c.id == id1);
    final idx2 = list.indexWhere((c) => c.id == id2);
    if (idx1 == -1 || idx2 == -1) return;
    final temp = list[idx1];
    list[idx1] = list[idx2];
    list[idx2] = temp;
    state = AsyncData(list);
  }
  
  void swapSubCategoriesLocally(String parentId, String sub1, String sub2) {
    if (state.valueOrNull == null) return;
    final list = List<CategoryModel>.from(state.valueOrNull!);
    final parentIdx = list.indexWhere((c) => c.id == parentId);
    if (parentIdx == -1) return;
    
    final parent = list[parentIdx];
    final subs = List<SubCategoryModel>.from(parent.subCategories);
    final idx1 = subs.indexWhere((s) => s.id == sub1);
    final idx2 = subs.indexWhere((s) => s.id == sub2);
    if (idx1 == -1 || idx2 == -1) return;
    
    final temp = subs[idx1];
    subs[idx1] = subs[idx2];
    subs[idx2] = temp;
    
    list[parentIdx] = parent.copyWith(subCategories: subs);
    state = AsyncData(list);
  }

  void toggleCategoryVisibilityLocally(String categoryId, bool isVisible) {
    if (state.valueOrNull == null) return;
    final list = state.valueOrNull!.map((cat) {
      if (cat.id == categoryId) {
        final updatedSubs = cat.subCategories.map((sub) {
          return sub.copyWith(isVisible: isVisible ? sub.isVisible : false);
        }).toList();
        return cat.copyWith(isVisible: isVisible, subCategories: updatedSubs);
      }
      return cat;
    }).toList();
    state = AsyncData(list);
  }

  void toggleSubCategoryVisibilityLocally(String parentId, String subId, bool isVisible) {
    if (state.valueOrNull == null) return;
    final list = state.valueOrNull!.map((cat) {
      if (cat.id == parentId) {
        final updatedSubs = cat.subCategories.map((sub) {
          if (sub.id == subId) {
            return sub.copyWith(isVisible: isVisible);
          }
          return sub;
        }).toList();
        return cat.copyWith(subCategories: updatedSubs);
      }
      return cat;
    }).toList();
    state = AsyncData(list);
  }

  @override
  FutureOr<List<CategoryModel>> build() async {
    final channel = _supabase
        .channel('public:categories')
        .onPostgresChanges(
          event: PostgresChangeEvent.all,
          schema: 'public',
          table: 'categories',
          callback: (payload) {
            // ref.invalidateSelf(); // Disabled to prevent flickering on reorder
          },
        )
        .subscribe();

    ref.onDispose(() {
      channel.unsubscribe();
    });

    final globalSettings = ref.watch(globalSettingsProvider);
    final rawCats = await ref.read(apiHomeRepositoryProvider).getCategories();

    final hiddenCats = globalSettings.hiddenCategoryIds;
    final hiddenSubs = globalSettings.hiddenSubCategoryIds;

    return rawCats.map((cat) {
      final isCatVisible = !hiddenCats.contains(cat.id);
      final updatedSubs = cat.subCategories.map((sub) {
        final isSubVisible = !hiddenSubs.contains(sub.id) && isCatVisible;
        return sub.copyWith(isVisible: isSubVisible);
      }).toList();

      return cat.copyWith(
        isVisible: isCatVisible,
        subCategories: updatedSubs,
      );
    }).toList();
  }
}

@Riverpod(keepAlive: true)
class Products extends _$Products {
  final _supabase = Supabase.instance.client;

  @override
  FutureOr<List<ProductCardModel>> build() async {
    // Setup Realtime listener for instant product & price updates
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

    try {
      final response = await safeSupabaseQuery(
        () => _supabase
            .from('products')
            .select('*, stores(id, name, name_ar, usd_exchange_rate)')
            .order('created_at', ascending: false),
        fallback: () => <dynamic>[],
      );
      if (response.isEmpty) return [];
      final List<dynamic> data = response;

      // Enrich with category names from categories repository/provider if available
      List<CategoryModel> cats = [];
      try {
        cats = await ref.read(categoriesProvider.future);
      } catch (_) {}

      final globalSettings = ref.read(globalSettingsProvider);
      final double platformRate = globalSettings.platformUsdRate;

      return data.map((json) {
        final catId = json['category_id']?.toString() ?? '';
        final subCatId = json['subcategory_id']?.toString() ?? json['sub_category_id']?.toString();

        final matchedCat = cats.where((c) => c.id == catId).firstOrNull;
        
        SubCategoryModel? matchedSubCat;
        if (subCatId != null && matchedCat != null) {
          matchedSubCat = matchedCat.subCategories.where((s) => s.id == subCatId).firstOrNull;
        } else if (subCatId != null) {
          for (final c in cats) {
            final s = c.subCategories.where((sub) => sub.id == subCatId).firstOrNull;
            if (s != null) {
              matchedSubCat = s;
              break;
            }
          }
        }

        String img = '';
        if (json['images'] is List && (json['images'] as List).isNotEmpty) {
          img = (json['images'] as List)[0]?.toString() ?? '';
        } else if (json['image_url'] != null && json['image_url'].toString().isNotEmpty) {
          img = json['image_url'].toString();
        }

        final double? usd = (json['price_usd'] as num?)?.toDouble() ?? 
                            (json['price_in_usd'] as num?)?.toDouble();

        final bool isActive = (json['is_active'] ?? true) == true;
        final num stockNum = (json['stock'] as num?) ?? 100;
        final bool inStock = isActive && (stockNum > 0);

        final storeData = (json['stores'] is Map) ? json['stores'] as Map<String, dynamic> : null;
        final String rawStoreName = storeData?['name']?.toString().trim() ?? '';
        final String rawStoreNameAr = storeData?['name_ar']?.toString().trim() ?? rawStoreName;
        final double? storeUsdRate = (storeData?['usd_exchange_rate'] as num?)?.toDouble();

        final String? pStoreId = json['store_id']?.toString();
        final bool hasStore = pStoreId != null && pStoreId.isNotEmpty && pStoreId != 'null';

        // An item is exclusive to Magaiees if:
        // 1. Explicitly marked is_magaiees_exclusive == true in DB, OR
        // 2. The parent category is exclusive to Magaiees (matchedCat?.isExclusiveToMagaiees == true), OR
        // 3. It has no merchant store associated with it (or store_id is 'magaiees').
        final bool explicitExclusive = json['is_magaiees_exclusive'] == true;
        final bool categoryExclusive = matchedCat?.isExclusiveToMagaiees == true;
        final bool noMerchantStore = !hasStore || pStoreId == 'magaiees';

        final bool isExclusive = explicitExclusive || categoryExclusive || noMerchantStore;

        final String finalStoreName;
        final String finalStoreNameAr;
        if (isExclusive) {
          finalStoreName = 'Magaiees Exclusive';
          finalStoreNameAr = 'حصري على مقاييس';
        } else {
          finalStoreName = (hasStore && rawStoreName.isNotEmpty) ? rawStoreName : 'Merchant Store';
          finalStoreNameAr = (hasStore && rawStoreNameAr.isNotEmpty) ? rawStoreNameAr : 'متجر تاجر';
        }

        final String effectiveMerchantId = isExclusive ? 'magaiees' : (hasStore ? pStoreId : 'magaiees');

        // Effective exchange rate according to merchant control or platform official rate
        final double effectiveRate = (!isExclusive && storeUsdRate != null && storeUsdRate > 0)
            ? storeUsdRate
            : platformRate;

        double sdg = (json['price_sdg'] as num?)?.toDouble() ?? 
                     (json['price'] as num?)?.toDouble() ?? 
                     0.0;
        // If local currency price is not provided or zero, but USD is available, convert it to local currency!
        if (sdg <= 0 && usd != null && usd > 0 && effectiveRate > 0) {
          sdg = usd * effectiveRate;
        }

        final double calculatedUsd;
        if (sdg > 0 && effectiveRate > 0) {
          calculatedUsd = sdg / effectiveRate;
        } else if (usd != null && usd > 0) {
          calculatedUsd = usd;
        } else {
          calculatedUsd = 0.0;
        }

        final String cleanUnit = UnitFormatter.clean(json['unit']?.toString() ?? 'ton');
        final String cleanUnitAr = UnitFormatter.clean(json['unit_ar']?.toString() ?? json['unit']?.toString() ?? 'طن');

        return ProductCardModel(
          id: json['id']?.toString() ?? '',
          categoryId: catId,
          categoryName: matchedCat?.name ?? 'General',
          categoryNameAr: matchedCat?.nameAr ?? 'عام',
          subCategoryId: subCatId,
          subCategoryName: matchedSubCat?.name,
          subCategoryNameAr: matchedSubCat?.nameAr,
          name: json['name'] ?? json['name_ar'] ?? '',
          nameAr: json['name_ar'] ?? json['name'] ?? '',
          imageUrl: img,
          pricePerTon: sdg,
          priceInUsd: calculatedUsd,
          unit: cleanUnit.isNotEmpty ? cleanUnit : 'ton',
          unitAr: cleanUnitAr.isNotEmpty ? cleanUnitAr : 'طن',
          inStock: inStock,
          stockQuantity: stockNum.toInt(),
          merchantId: effectiveMerchantId,
          storeName: finalStoreName,
          storeNameAr: finalStoreNameAr,
          isMagaieesExclusive: isExclusive,
          storeUsdRate: effectiveRate,
        );
      }).toList();
    } catch (e) {
      debugPrint('Products Error: $e');
      return []; // Return empty so it doesn't hang the UI
    }
  }

  Future<void> addProduct(ProductCardModel newProduct) async {
    final previousState = state;
    try {
      final insertData = <String, dynamic>{
        'category_id': newProduct.categoryId,
        if (newProduct.subCategoryId != null && newProduct.subCategoryId!.isNotEmpty)
          'subcategory_id': newProduct.subCategoryId,
        'name': newProduct.name,
        'name_ar': newProduct.nameAr,
        'price_sdg': newProduct.pricePerTon,
        'price_usd': newProduct.priceInUsd,
        'stock': newProduct.inStock ? (newProduct.stockQuantity > 0 ? newProduct.stockQuantity : 100) : 0,
        'images': [if (newProduct.imageUrl != null && newProduct.imageUrl!.isNotEmpty) newProduct.imageUrl!],
        'is_magaiees_exclusive': newProduct.isMagaieesExclusive,
        'unit': newProduct.unitAr,
        'is_active': newProduct.inStock,
      };
      
      final res = await _supabase.from('products').insert(insertData).select().maybeSingle();
      final createdProduct = newProduct.copyWith(
        id: (res != null && res['id'] != null) ? res['id'].toString() : newProduct.id,
      );
      
      if (previousState.valueOrNull != null) {
        state = AsyncData([createdProduct, ...previousState.value!]);
      } else {
        state = AsyncData([createdProduct]);
      }
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
      final updateData = <String, dynamic>{
        'category_id': updatedProduct.categoryId,
        'subcategory_id': (updatedProduct.subCategoryId != null && updatedProduct.subCategoryId!.isNotEmpty) ? updatedProduct.subCategoryId : null,
        'name': updatedProduct.name,
        'name_ar': updatedProduct.nameAr,
        'price_sdg': updatedProduct.pricePerTon,
        'price_usd': updatedProduct.priceInUsd,
        'stock': updatedProduct.inStock ? (updatedProduct.stockQuantity > 0 ? updatedProduct.stockQuantity : 100) : 0,
        'images': [if (updatedProduct.imageUrl != null && updatedProduct.imageUrl!.isNotEmpty) updatedProduct.imageUrl!],
        'is_magaiees_exclusive': updatedProduct.isMagaieesExclusive,
        'unit': updatedProduct.unitAr,
        'is_active': updatedProduct.inStock,
      };
      if (!updatedProduct.id.startsWith('prod_') && !updatedProduct.id.startsWith('custom_prod_')) {
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
      if (!id.startsWith('prod_') && !id.startsWith('custom_prod_')) {
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

// 0: All products, 1: Magaiees Exclusive only, 2: Merchant stores only
final exclusivityFilterProvider = StateProvider<int>((ref) => 0);

@riverpod
Future<List<ProductCardModel>> filteredProducts(FilteredProductsRef ref) async {
  final products = await ref.watch(productsProvider.future);
  final selectedCategory = ref.watch(selectedCategoryProvider);
  final searchQuery = ref.watch(searchQueryProvider).toLowerCase();
  final exclusivity = ref.watch(exclusivityFilterProvider);

  return products.where((product) {
    final matchesCategory = selectedCategory == null || product.categoryId == selectedCategory;
    final matchesSearch = searchQuery.isEmpty || 
        product.name.toLowerCase().contains(searchQuery) ||
        product.nameAr.contains(searchQuery);
    final matchesExclusivity = exclusivity == 0 ||
        (exclusivity == 1 && product.isMagaieesExclusive) ||
        (exclusivity == 2 && !product.isMagaieesExclusive);
    return matchesCategory && matchesSearch && matchesExclusivity && product.inStock;
  }).toList();
}

