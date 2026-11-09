import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/core/network/supabase_safe_query.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/category_model.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

class CategoryAnalytics {
  final String categoryId;
  final int productsCount;
  final double inventoryValue;
  final double salesVolume;
  final int unitsSold;
  final bool isAdvertiserSponsored;
  final List<String> advertiserStoreNames;

  bool get hasAdvertiser => isAdvertiserSponsored;

  const CategoryAnalytics({
    required this.categoryId,
    this.productsCount = 0,
    this.inventoryValue = 0.0,
    this.salesVolume = 0.0,
    this.unitsSold = 0,
    this.isAdvertiserSponsored = false,
    this.advertiserStoreNames = const [],
  });
}

class AdminCategoriesInsights {
  final double totalSalesVolume;
  final double totalInventoryValue;
  final int totalProductsCount;
  final int totalCategoriesCount;
  final int totalSubcategoriesCount;
  final int advertiserCategoriesCount;
  final String? topSellingCategoryNameAr;
  final String? topSellingCategoryNameEn;
  final double topSellingCategorySales;
  final int topSellingCategoryUnits;
  final Map<String, CategoryAnalytics> categoryAnalytics;

  const AdminCategoriesInsights({
    this.totalSalesVolume = 0.0,
    this.totalInventoryValue = 0.0,
    this.totalProductsCount = 0,
    this.totalCategoriesCount = 0,
    this.totalSubcategoriesCount = 0,
    this.advertiserCategoriesCount = 0,
    this.topSellingCategoryNameAr,
    this.topSellingCategoryNameEn,
    this.topSellingCategorySales = 0.0,
    this.topSellingCategoryUnits = 0,
    this.categoryAnalytics = const {},
  });
}

final adminCategoriesInsightsProvider = FutureProvider.autoDispose<AdminCategoriesInsights>((ref) async {
  final categoriesAsync = ref.watch(categoriesProvider);
  final List<CategoryModel> categories = categoriesAsync.valueOrNull ?? [];
  final client = Supabase.instance.client;

  // Query products, order_items, stores, and ads concurrently
  final results = await Future.wait([
    safeSupabaseQuery(() => client.from('products').select('id, category_id, subcategory_id, price_sdg, store_id, is_active'), fallback: () => <dynamic>[]),
    safeSupabaseQuery(() => client.from('order_items').select('id, product_id, quantity, price_sdg'), fallback: () => <dynamic>[]),
    safeSupabaseQuery(() => client.from('stores').select('id, name, name_ar, owner_id'), fallback: () => <dynamic>[]),
    safeSupabaseQuery(() => client.from('ads').select('id, title, company, is_active'), fallback: () => <dynamic>[]),
  ]);

  final List<dynamic> productsRaw = results[0] as List<dynamic>? ?? [];
  final List<dynamic> orderItemsRaw = results[1] as List<dynamic>? ?? [];
  final List<dynamic> storesRaw = results[2] as List<dynamic>? ?? [];
  final List<dynamic> adsRaw = results[3] as List<dynamic>? ?? [];

  // Identify active advertiser stores
  final activeAds = adsRaw.where((a) => a['is_active'] == true).toList();
  final Set<String> advertiserStoreIds = {};
  final Map<String, String> storeNamesMap = {};

  for (var s in storesRaw) {
    final sId = s['id']?.toString() ?? '';
    final sName = s['name']?.toString() ?? s['name_ar']?.toString() ?? '';
    if (sId.isNotEmpty) {
      storeNamesMap[sId] = sName.isNotEmpty ? sName : 'متجر';
      // Cross reference with ads company name or active campaigns
      final hasActiveCampaign = activeAds.any((a) {
        final comp = a['company']?.toString().toLowerCase().trim() ?? '';
        return comp.isNotEmpty && sName.toLowerCase().trim().contains(comp);
      });
      if (hasActiveCampaign || activeAds.isNotEmpty) {
        advertiserStoreIds.add(sId);
      }
    }
  }

  // Pre-populate category stats map
  final Map<String, _CatAccumulator> accumulators = {};
  int totalSubcategoriesCount = 0;
  for (var cat in categories) {
    accumulators[cat.id] = _CatAccumulator(cat.id);
    totalSubcategoriesCount += cat.subCategories.length;
  }

  // Map products
  final Map<String, String> prodToCat = {};
  double totalInventory = 0.0;

  for (var p in productsRaw) {
    final pId = p['id']?.toString() ?? '';
    final catId = p['category_id']?.toString() ?? '';
    final price = (num.tryParse(p['price_sdg']?.toString() ?? '0') ?? 0.0).toDouble();
    final storeId = p['store_id']?.toString() ?? '';

    totalInventory += price;
    if (pId.isNotEmpty && catId.isNotEmpty) {
      prodToCat[pId] = catId;
      var acc = accumulators[catId];
      if (acc == null) {
        acc = _CatAccumulator(catId);
        accumulators[catId] = acc;
      }
      acc.productsCount++;
      acc.inventoryValue += price;

      if (advertiserStoreIds.contains(storeId)) {
        acc.isAdvertiserSponsored = true;
        final sName = storeNamesMap[storeId] ?? 'متجر معلن';
        if (!acc.advertiserStores.contains(sName)) {
          acc.advertiserStores.add(sName);
        }
      }
    }
  }

  // Aggregate order items sales volume & units sold
  double totalSales = 0.0;
  for (var oi in orderItemsRaw) {
    final prodId = oi['product_id']?.toString() ?? '';
    final catId = prodToCat[prodId];
    if (catId != null && accumulators.containsKey(catId)) {
      final qty = (num.tryParse(oi['quantity']?.toString() ?? '1') ?? 1).toInt();
      final price = (num.tryParse(oi['price_sdg']?.toString() ?? '0') ?? 0.0).toDouble();
      final itemTotal = price * qty;

      accumulators[catId]!.unitsSold += qty;
      accumulators[catId]!.salesVolume += itemTotal;
      totalSales += itemTotal;
    }
  }

  // Determine top selling category
  String? topCatNameAr;
  String? topCatNameEn;
  double topSales = 0.0;
  int topUnits = 0;
  int advertiserCategoriesCount = 0;

  final Map<String, CategoryAnalytics> analyticsMap = {};

  accumulators.forEach((catId, acc) {
    if (acc.isAdvertiserSponsored) {
      advertiserCategoriesCount++;
    }

    if (acc.salesVolume > topSales) {
      topSales = acc.salesVolume;
      topUnits = acc.unitsSold;
      final matchCat = categories.where((c) => c.id == catId).toList();
      if (matchCat.isNotEmpty) {
        topCatNameAr = matchCat.first.nameAr;
        topCatNameEn = matchCat.first.name;
      }
    }

    analyticsMap[catId] = CategoryAnalytics(
      categoryId: catId,
      productsCount: acc.productsCount,
      inventoryValue: acc.inventoryValue,
      salesVolume: acc.salesVolume,
      unitsSold: acc.unitsSold,
      isAdvertiserSponsored: acc.isAdvertiserSponsored,
      advertiserStoreNames: acc.advertiserStores,
    );
  });

  return AdminCategoriesInsights(
    totalSalesVolume: totalSales,
    totalInventoryValue: totalInventory,
    totalProductsCount: productsRaw.length,
    totalCategoriesCount: categories.length,
    totalSubcategoriesCount: totalSubcategoriesCount,
    advertiserCategoriesCount: advertiserCategoriesCount,
    topSellingCategoryNameAr: topCatNameAr,
    topSellingCategoryNameEn: topCatNameEn,
    topSellingCategorySales: topSales,
    topSellingCategoryUnits: topUnits,
    categoryAnalytics: analyticsMap,
  );
});

class _CatAccumulator {
  final String categoryId;
  int productsCount = 0;
  double inventoryValue = 0.0;
  double salesVolume = 0.0;
  int unitsSold = 0;
  bool isAdvertiserSponsored = false;
  List<String> advertiserStores = [];

  _CatAccumulator(this.categoryId);
}
