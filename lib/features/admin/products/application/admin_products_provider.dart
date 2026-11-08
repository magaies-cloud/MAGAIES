import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/core/network/supabase_safe_query.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/product_card_model.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

/// Performance and accounting analytics for an individual product
class ProductAnalytics {
  final String productId;
  final double salesVolume;
  final int unitsSold;
  final int orderCount;
  final double inventoryValue;
  final bool isAdvertiserSponsored;
  final String advertiserStoreName;
  final String storeId;
  final double storeUsdRate;
  final String? storeAddress;
  final String? storeBank;

  const ProductAnalytics({
    required this.productId,
    this.salesVolume = 0.0,
    this.unitsSold = 0,
    this.orderCount = 0,
    this.inventoryValue = 0.0,
    this.isAdvertiserSponsored = false,
    this.advertiserStoreName = '',
    this.storeId = '',
    this.storeUsdRate = 0.0,
    this.storeAddress,
    this.storeBank,
  });
}

/// Information about a partner merchant / advertiser store
class StoreAdvertiserInfo {
  final String storeId;
  final String storeName;
  final String storeNameAr;
  final int productsCount;
  final double totalSalesVolume;
  final double totalInventoryValue;
  final bool isAdvertiser;
  final double usdExchangeRate;
  final String? address;
  final String? bankName;

  const StoreAdvertiserInfo({
    required this.storeId,
    required this.storeName,
    required this.storeNameAr,
    this.productsCount = 0,
    this.totalSalesVolume = 0.0,
    this.totalInventoryValue = 0.0,
    this.isAdvertiser = false,
    this.usdExchangeRate = 0.0,
    this.address,
    this.bankName,
  });
}

/// Global catalog accounting insights and financial figures
class AdminProductsInsights {
  final double totalSalesVolume;
  final double totalInventoryValue;
  final int totalStockUnits;
  final int totalUnitsSold;
  final int totalProductsCount;
  final int exclusiveProductsCount;
  final int advertiserProductsCount;
  final int inStockCount;
  final int outOfStockCount;
  final String? topSellingProductNameAr;
  final double topSellingProductSales;
  final int topSellingProductUnits;
  final Map<String, ProductAnalytics> productAnalyticsMap;
  final List<StoreAdvertiserInfo> partnerStores;

  const AdminProductsInsights({
    this.totalSalesVolume = 0.0,
    this.totalInventoryValue = 0.0,
    this.totalStockUnits = 0,
    this.totalUnitsSold = 0,
    this.totalProductsCount = 0,
    this.exclusiveProductsCount = 0,
    this.advertiserProductsCount = 0,
    this.inStockCount = 0,
    this.outOfStockCount = 0,
    this.topSellingProductNameAr,
    this.topSellingProductSales = 0.0,
    this.topSellingProductUnits = 0,
    this.productAnalyticsMap = const {},
    this.partnerStores = const [],
  });

  ProductAnalytics forProduct(String productId) {
    return productAnalyticsMap[productId] ??
        ProductAnalytics(productId: productId);
  }
}

/// Riverpod provider computing full catalog financial metrics and advertiser linkage
final adminProductsInsightsProvider =
    FutureProvider.autoDispose<AdminProductsInsights>((ref) async {
  final productsAsync = ref.watch(productsProvider);
  final List<ProductCardModel> allProducts = productsAsync.valueOrNull ?? [];
  final client = Supabase.instance.client;

  // Concurrently fetch order_items, stores, and ads
  final results = await Future.wait([
    safeSupabaseQuery(
      () => client.from('order_items').select('id, product_id, quantity, price_sdg, order_id'),
      fallback: () => <dynamic>[],
    ),
    safeSupabaseQuery(
      () => client.from('stores').select('*'),
      fallback: () => <dynamic>[],
    ),
    safeSupabaseQuery(
      () => client.from('ads').select('id, title, company, is_active, price'),
      fallback: () => <dynamic>[],
    ),
  ]);

  final List<dynamic> orderItemsRaw = results[0] as List<dynamic>? ?? [];
  final List<dynamic> storesRaw = results[1] as List<dynamic>? ?? [];
  final List<dynamic> adsRaw = results[2] as List<dynamic>? ?? [];

  // 1. Process Stores & Advertisers
  final activeAds = adsRaw.where((a) => a['is_active'] == true).toList();
  final Map<String, Map<String, dynamic>> storeMap = {};
  for (var s in storesRaw) {
    final sId = s['id']?.toString() ?? '';
    if (sId.isNotEmpty) {
      storeMap[sId] = Map<String, dynamic>.from(s as Map);
    }
  }

  // 2. Aggregate Sales per Product from order_items
  final Map<String, _ProductSalesAccumulator> salesMap = {};
  double totalSalesVolume = 0.0;
  int totalUnitsSold = 0;

  for (var item in orderItemsRaw) {
    final pId = item['product_id']?.toString() ?? '';
    if (pId.isEmpty) continue;

    final qty = (item['quantity'] as num?)?.toInt() ?? 0;
    final price = (item['price_sdg'] as num?)?.toDouble() ?? 0.0;
    final amount = qty * price;

    totalSalesVolume += amount;
    totalUnitsSold += qty;

    final acc = salesMap.putIfAbsent(pId, () => _ProductSalesAccumulator());
    acc.unitsSold += qty;
    acc.salesVolume += amount;
    acc.orderCount += 1;
  }

  // 3. Compute Product Level Analytics and Totals
  double totalInventoryValue = 0.0;
  int totalStockUnits = 0;
  int inStockCount = 0;
  int outOfStockCount = 0;
  int exclusiveCount = 0;
  int advertiserCount = 0;

  final Map<String, ProductAnalytics> analyticsMap = {};
  final Map<String, _StoreAccumulator> storeAccumulators = {};

  String? topProductName;
  double topProductSales = 0.0;
  int topProductUnits = 0;

  for (var product in allProducts) {
    final sales = salesMap[product.id];
    final salesVol = sales?.salesVolume ?? 0.0;
    final units = sales?.unitsSold ?? 0;
    final orders = sales?.orderCount ?? 0;

    final stock = product.stockQuantity;
    final price = product.pricePerTon;
    final invValue = price * stock;

    totalInventoryValue += invValue;
    totalStockUnits += stock;

    if (product.inStock && stock > 0) {
      inStockCount++;
    } else {
      outOfStockCount++;
    }

    final isExclusive = product.isMagaieesExclusive;
    if (isExclusive) {
      exclusiveCount++;
    } else {
      advertiserCount++;
    }

    // Top selling check
    if (salesVol > topProductSales) {
      topProductSales = salesVol;
      topProductName = product.nameAr.isNotEmpty ? product.nameAr : product.name;
      topProductUnits = units;
    }

    // Match store details
    final sId = product.merchantId;
    final sData = storeMap[sId];
    final String storeName = sData?['name_ar']?.toString().isNotEmpty == true
        ? sData!['name_ar'].toString()
        : (sData?['name']?.toString().isNotEmpty == true
            ? sData!['name'].toString()
            : product.storeNameAr);
    final double storeRate = (sData?['usd_exchange_rate'] as num?)?.toDouble() ??
        product.storeUsdRate ??
        0.0;
    final String? storeAddr = sData?['address']?.toString();
    final String? storeBank = sData?['bank_name']?.toString();

    // Check advertiser linkage
    final isAdv = !isExclusive &&
        (activeAds.isNotEmpty ||
            (sData != null && sData['status'] != null) ||
            sId != 'magaiees');

    analyticsMap[product.id] = ProductAnalytics(
      productId: product.id,
      salesVolume: salesVol,
      unitsSold: units,
      orderCount: orders,
      inventoryValue: invValue,
      isAdvertiserSponsored: isAdv,
      advertiserStoreName: isExclusive ? 'شركة مقاييس - توريد مباشر' : storeName,
      storeId: sId,
      storeUsdRate: storeRate,
      storeAddress: storeAddr,
      storeBank: storeBank,
    );

    // Accumulate for store summary
    if (!isExclusive && sId.isNotEmpty && sId != 'magaiees') {
      final sAcc = storeAccumulators.putIfAbsent(
        sId,
        () => _StoreAccumulator(
          storeId: sId,
          storeName: sData?['name']?.toString() ?? product.storeName,
          storeNameAr: storeName,
          rate: storeRate,
          address: storeAddr,
          bank: storeBank,
          isAdvertiser: isAdv,
        ),
      );
      sAcc.productsCount++;
      sAcc.totalSales += salesVol;
      sAcc.totalInventory += invValue;
    }
  }

  final partnerStoresList = storeAccumulators.values.map((s) {
    return StoreAdvertiserInfo(
      storeId: s.storeId,
      storeName: s.storeName,
      storeNameAr: s.storeNameAr,
      productsCount: s.productsCount,
      totalSalesVolume: s.totalSales,
      totalInventoryValue: s.totalInventory,
      isAdvertiser: s.isAdvertiser,
      usdExchangeRate: s.rate,
      address: s.address,
      bankName: s.bank,
    );
  }).toList()
    ..sort((a, b) => b.totalSalesVolume.compareTo(a.totalSalesVolume));

  return AdminProductsInsights(
    totalSalesVolume: totalSalesVolume,
    totalInventoryValue: totalInventoryValue,
    totalStockUnits: totalStockUnits,
    totalUnitsSold: totalUnitsSold,
    totalProductsCount: allProducts.length,
    exclusiveProductsCount: exclusiveCount,
    advertiserProductsCount: advertiserCount,
    inStockCount: inStockCount,
    outOfStockCount: outOfStockCount,
    topSellingProductNameAr: topProductName,
    topSellingProductSales: topProductSales,
    topSellingProductUnits: topProductUnits,
    productAnalyticsMap: analyticsMap,
    partnerStores: partnerStoresList,
  );
});

class _ProductSalesAccumulator {
  int unitsSold = 0;
  double salesVolume = 0.0;
  int orderCount = 0;
}

class _StoreAccumulator {
  final String storeId;
  final String storeName;
  final String storeNameAr;
  final double rate;
  final String? address;
  final String? bank;
  final bool isAdvertiser;
  int productsCount = 0;
  double totalSales = 0.0;
  double totalInventory = 0.0;

  _StoreAccumulator({
    required this.storeId,
    required this.storeName,
    required this.storeNameAr,
    required this.rate,
    this.address,
    this.bank,
    required this.isAdvertiser,
  });
}
