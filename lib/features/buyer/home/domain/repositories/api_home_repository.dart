import 'package:dio/dio.dart';
import 'package:magaiees_marketplace/core/network/api_client.dart';
import 'package:magaiees_marketplace/core/network/api_endpoints.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/product_card_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/category_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/sub_category_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/market_price_model.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

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

  Future<List<CategoryModel>> getCategories() async {
    try {
      final response = await Supabase.instance.client.from('categories').select('*, subcategories(*)');
      final List<dynamic> data = response;
      return data.map((json) {
        final subs = (json['subcategories'] as List<dynamic>? ?? []).map((sub) {
          return SubCategoryModel(
            id: sub['id'].toString(),
            categoryId: sub['category_id'].toString(),
            name: sub['name'] ?? '',
            nameAr: sub['name_ar'] ?? '',
            productCount: 0,
          );
        }).toList();

        return CategoryModel(
          id: json['id'].toString(),
          name: json['name'] ?? '',
          nameAr: json['name_ar'] ?? '',
          iconName: json['icon_name'] ?? 'category',
          productCount: 0,
          isExclusiveToMagaiees: json['is_magaiees_exclusive'] ?? false,
          subCategories: subs,
        );
      }).toList();
    } catch (e) {
      return [];
    }
  }

  Future<List<MarketPriceModel>> getMarketPrices() async {
    try {
      final response = await Supabase.instance.client.from('market_prices').select();
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
