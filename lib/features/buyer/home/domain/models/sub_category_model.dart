class SubCategoryModel {
  final String id;
  final String categoryId;
  final String name;
  final String nameAr;
  final String? iconName;
  final String? imageUrl;
  final int productCount;
  final bool isVisible;

  const SubCategoryModel({
    required this.id,
    required this.categoryId,
    required this.name,
    required this.nameAr,
    this.iconName,
    this.imageUrl,
    this.productCount = 0,
    this.isVisible = true,
  });

  factory SubCategoryModel.fromJson(Map<String, dynamic> json) {
    return SubCategoryModel(
      id: json['id']?.toString() ?? '',
      categoryId: json['categoryId']?.toString() ?? json['category_id']?.toString() ?? '',
      name: json['name'] ?? '',
      nameAr: json['nameAr'] ?? json['name_ar'] ?? '',
      iconName: json['iconName'] ?? json['icon_name'],
      imageUrl: json['imageUrl'] ?? json['image_url'],
      productCount: (json['productCount'] as num?)?.toInt() ?? (json['product_count'] as num?)?.toInt() ?? 0,
      isVisible: (json['isVisible'] ?? json['is_visible'] ?? json['is_active'] ?? true) == true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'categoryId': categoryId,
      'name': name,
      'nameAr': nameAr,
      'iconName': iconName,
      'imageUrl': imageUrl,
      'productCount': productCount,
      'isVisible': isVisible,
    };
  }

  SubCategoryModel copyWith({
    String? id,
    String? categoryId,
    String? name,
    String? nameAr,
    String? iconName,
    String? imageUrl,
    int? productCount,
    bool? isVisible,
  }) {
    return SubCategoryModel(
      id: id ?? this.id,
      categoryId: categoryId ?? this.categoryId,
      name: name ?? this.name,
      nameAr: nameAr ?? this.nameAr,
      iconName: iconName ?? this.iconName,
      imageUrl: imageUrl ?? this.imageUrl,
      productCount: productCount ?? this.productCount,
      isVisible: isVisible ?? this.isVisible,
    );
  }
}
