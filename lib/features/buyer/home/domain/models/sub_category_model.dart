class SubCategoryModel {
  final String id;
  final String categoryId;
  final String name;
  final String nameAr;
  final String? iconName;
  final String? imageUrl;
  final int productCount;

  const SubCategoryModel({
    required this.id,
    required this.categoryId,
    required this.name,
    required this.nameAr,
    this.iconName,
    this.imageUrl,
    this.productCount = 0,
  });

  factory SubCategoryModel.fromJson(Map<String, dynamic> json) {
    return SubCategoryModel(
      id: json['id']?.toString() ?? '',
      categoryId: json['categoryId']?.toString() ?? json['category_id']?.toString() ?? '',
      name: json['name'] ?? '',
      nameAr: json['nameAr'] ?? json['name_ar'] ?? '',
      iconName: json['iconName'] ?? json['icon_name'],
      imageUrl: json['imageUrl'] ?? json['image_url'],
      productCount: (json['productCount'] ?? json['product_count'] ?? 0) as int,
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
    };
  }
}
