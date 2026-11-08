class ProductCardModel {
  final String id;
  final String name;
  final String nameAr;
  final String categoryId;
  final String categoryName;
  final String categoryNameAr;
  final String? subCategoryId;
  final String? subCategoryName;
  final String? subCategoryNameAr;
  final double pricePerTon;
  final double? priceInUsd;
  final String? unit;
  final String? unitAr;
  final String? imageUrl;
  final String merchantId;
  final String storeName;
  final String storeNameAr;
  final double transportFeePerTon;
  final double offloadingFeePerTon;
  final String currency;
  final bool inStock;
  final int stockQuantity;
  final bool isMagaieesExclusive;
  final double? storeUsdRate;
  final DateTime? lastUpdated;

  const ProductCardModel({
    required this.id,
    required this.name,
    required this.nameAr,
    required this.categoryId,
    required this.categoryName,
    required this.categoryNameAr,
    this.subCategoryId,
    this.subCategoryName,
    this.subCategoryNameAr,
    required this.pricePerTon,
    this.priceInUsd,
    this.unit = 'ton',
    this.unitAr = 'طن',
    this.imageUrl,
    required this.merchantId,
    required this.storeName,
    required this.storeNameAr,
    this.transportFeePerTon = 0,
    this.offloadingFeePerTon = 0,
    this.currency = 'SDG',
    this.inStock = true,
    this.stockQuantity = 100,
    this.isMagaieesExclusive = false,
    this.storeUsdRate,
    this.lastUpdated,
  });

  factory ProductCardModel.fromJson(Map<String, dynamic> json) {
    final int qty = (json['stock'] as num?)?.toInt() ?? 
                    (json['stockQuantity'] as num?)?.toInt() ?? 
                    (json['stock_quantity'] as num?)?.toInt() ?? 
                    100;
    final bool active = (json['is_active'] ?? json['inStock'] ?? json['in_stock'] ?? true) == true || json['inStock'] == 1;
    final bool available = active && qty > 0;
    return ProductCardModel(
      id: json['id']?.toString() ?? '',
      name: json['name'] ?? '',
      nameAr: json['nameAr'] ?? json['name_ar'] ?? '',
      categoryId: json['categoryId']?.toString() ?? json['category_id']?.toString() ?? '1',
      categoryName: json['categoryName'] ?? json['category_name'] ?? '',
      categoryNameAr: json['categoryNameAr'] ?? json['category_name_ar'] ?? '',
      subCategoryId: json['subCategoryId']?.toString() ?? json['sub_category_id']?.toString() ?? json['subcategory_id']?.toString(),
      subCategoryName: json['subCategoryName'] ?? json['sub_category_name'],
      subCategoryNameAr: json['subCategoryNameAr'] ?? json['sub_category_name_ar'],
      pricePerTon: double.tryParse(json['pricePerTon']?.toString() ?? json['price']?.toString() ?? '0') ?? 0.0,
      priceInUsd: json['priceInUsd'] != null ? double.tryParse(json['priceInUsd'].toString()) : null,
      unit: json['unit'],
      unitAr: json['unitAr'] ?? json['unit_ar'] ?? 'طن',
      imageUrl: json['imageUrl'] ?? json['image_url'],
      merchantId: json['merchantId']?.toString() ?? json['merchant_id']?.toString() ?? '1',
      storeName: json['storeName'] ?? json['store_name'] ?? '',
      storeNameAr: json['storeNameAr'] ?? json['store_name_ar'] ?? '',
      transportFeePerTon: double.tryParse(json['transportFeePerTon']?.toString() ?? '0') ?? 0.0,
      offloadingFeePerTon: double.tryParse(json['offloadingFeePerTon']?.toString() ?? '0') ?? 0.0,
      currency: json['currency'] ?? 'SDG',
      inStock: available,
      stockQuantity: qty,
      isMagaieesExclusive: (json['isMagaieesExclusive'] ?? json['is_magaiees_exclusive'] ?? false) == true,
      storeUsdRate: (json['storeUsdRate'] as num?)?.toDouble() ??
          (json['store_usd_rate'] as num?)?.toDouble() ??
          (json['usd_exchange_rate'] as num?)?.toDouble(),
      lastUpdated: json['lastUpdated'] != null ? DateTime.tryParse(json['lastUpdated']) : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'nameAr': nameAr,
      'categoryId': categoryId,
      'categoryName': categoryName,
      'categoryNameAr': categoryNameAr,
      'subCategoryId': subCategoryId,
      'subCategoryName': subCategoryName,
      'subCategoryNameAr': subCategoryNameAr,
      'pricePerTon': pricePerTon,
      'priceInUsd': priceInUsd,
      'unit': unit,
      'unitAr': unitAr,
      'imageUrl': imageUrl,
      'merchantId': merchantId,
      'storeName': storeName,
      'storeNameAr': storeNameAr,
      'transportFeePerTon': transportFeePerTon,
      'offloadingFeePerTon': offloadingFeePerTon,
      'currency': currency,
      'inStock': inStock,
      'stock': stockQuantity,
      'isMagaieesExclusive': isMagaieesExclusive,
      'storeUsdRate': storeUsdRate,
      'lastUpdated': lastUpdated?.toIso8601String(),
    };
  }

  ProductCardModel copyWith({
    String? id,
    String? name,
    String? nameAr,
    String? categoryId,
    String? categoryName,
    String? categoryNameAr,
    String? subCategoryId,
    String? subCategoryName,
    String? subCategoryNameAr,
    double? pricePerTon,
    double? priceInUsd,
    String? unit,
    String? unitAr,
    String? imageUrl,
    String? merchantId,
    String? storeName,
    String? storeNameAr,
    double? transportFeePerTon,
    double? offloadingFeePerTon,
    String? currency,
    bool? inStock,
    int? stockQuantity,
    bool? isMagaieesExclusive,
    double? storeUsdRate,
    DateTime? lastUpdated,
  }) {
    return ProductCardModel(
      id: id ?? this.id,
      name: name ?? this.name,
      nameAr: nameAr ?? this.nameAr,
      categoryId: categoryId ?? this.categoryId,
      categoryName: categoryName ?? this.categoryName,
      categoryNameAr: categoryNameAr ?? this.categoryNameAr,
      subCategoryId: subCategoryId ?? this.subCategoryId,
      subCategoryName: subCategoryName ?? this.subCategoryName,
      subCategoryNameAr: subCategoryNameAr ?? this.subCategoryNameAr,
      pricePerTon: pricePerTon ?? this.pricePerTon,
      priceInUsd: priceInUsd ?? this.priceInUsd,
      unit: unit ?? this.unit,
      unitAr: unitAr ?? this.unitAr,
      imageUrl: imageUrl ?? this.imageUrl,
      merchantId: merchantId ?? this.merchantId,
      storeName: storeName ?? this.storeName,
      storeNameAr: storeNameAr ?? this.storeNameAr,
      transportFeePerTon: transportFeePerTon ?? this.transportFeePerTon,
      offloadingFeePerTon: offloadingFeePerTon ?? this.offloadingFeePerTon,
      currency: currency ?? this.currency,
      inStock: inStock ?? this.inStock,
      stockQuantity: stockQuantity ?? this.stockQuantity,
      isMagaieesExclusive: isMagaieesExclusive ?? this.isMagaieesExclusive,
      storeUsdRate: storeUsdRate ?? this.storeUsdRate,
      lastUpdated: lastUpdated ?? this.lastUpdated,
    );
  }
}

