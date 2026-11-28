import 'dart:convert';
import 'package:magaiees_marketplace/core/network/api_client.dart';
import 'package:magaiees_marketplace/core/network/api_endpoints.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/product_card_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/category_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/sub_category_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/market_price_model.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/core/network/supabase_safe_query.dart';

class ApiHomeRepository {
  final ApiClient _apiClient;

  ApiHomeRepository(this._apiClient);

  Future<List<ProductCardModel>> getProducts() async {
    return [];
  }

  Future<void> addProduct(Map<String, dynamic> data) async {
    try {
      await _apiClient.dio.post(
        ApiEndpoints.baseUrl + ApiEndpoints.addProduct,
        data: data,
      );
    } catch (e) {
      // In local mode or mock mode
    }
  }

  List<CategoryModel> parseCategoriesJson(List<dynamic> data) {
    return data.map((json) {
      final subs = (json['subcategories'] as List<dynamic>? ?? []).map((sub) {
        return SubCategoryModel(
          id: sub['id'].toString(),
          categoryId: sub['category_id'].toString(),
          name: sub['name'] ?? '',
          nameAr: sub['name_ar'] ?? '',
          imageUrl: sub['image_url'],
          productCount: 0,
          isVisible: (sub['is_visible'] ?? sub['is_active'] ?? sub['isVisible']) != false,
        );
      }).toList();

      return CategoryModel(
        id: json['id'].toString(),
        name: json['name'] ?? '',
        nameAr: json['name_ar'] ?? '',
        iconName: json['icon_name'] ?? 'category',
        imageUrl: json['image_url'],
        productCount: 0,
        isExclusiveToMagaiees: json['is_magaiees_exclusive'] ?? false,
        isVisible: (json['is_visible'] ?? json['is_active'] ?? json['isVisible']) != false,
        subCategories: subs,
      );
    }).toList();
  }

  Future<List<CategoryModel>> getCachedCategories() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final cachedRaw = prefs.getString('cached_categories_v1');
      if (cachedRaw != null && cachedRaw.isNotEmpty) {
        final decoded = jsonDecode(cachedRaw) as List<dynamic>;
        return parseCategoriesJson(decoded);
      }
    } catch (_) {}
    return [];
  }

  Future<List<CategoryModel>> getCategories() async {
    try {
      final response = await safeSupabaseQuery(
        () => Supabase.instance.client
            .from('categories')
            .select('*, subcategories(*)')
            .order('created_at', ascending: false),
        fallback: () => <dynamic>[],
      );
      final List<dynamic> data = response;
      if (data.isNotEmpty) {
        try {
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString('cached_categories_v1', jsonEncode(data));
        } catch (_) {}
        return parseCategoriesJson(data);
      }
      return await getCachedCategories();
    } catch (e) {
      return await getCachedCategories();
    }
  }

  Future<List<MarketPriceModel>> getMarketPrices() async {
    try {
      final response = await safeSupabaseQuery(
        () => Supabase.instance.client.from('market_prices').select(),
        fallback: () => <dynamic>[],
      );
      final List<dynamic> data = response;
      return data.map((json) {
        return MarketPriceModel(
          materialName: json['material_name'] ?? '',
          materialNameAr: json['material_name_ar'] ?? '',
          currentPrice: (json['current_price'] as num?)?.toDouble() ?? 0.0,
          previousPrice: (json['previous_price'] as num?)?.toDouble() ?? 0.0,
          unit: json['unit'] ?? '',
          unitAr: json['unit_ar'] ?? '',
        );
      }).toList();
    } catch (e) {
      return [];
    }
  }
}
