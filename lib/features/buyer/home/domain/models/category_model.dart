import 'package:magaiees_marketplace/features/buyer/home/domain/models/sub_category_model.dart';

class CategoryModel {
  final String id;
  final String name;
  final String nameAr;
  final String? iconName;
  final String? imageUrl;
  final int productCount;
  final List<SubCategoryModel> subCategories;
  final bool isExclusiveToMagaiees;
  final bool isVisible;

  const CategoryModel({
    required this.id,
    required this.name,
    required this.nameAr,
    this.iconName,
    this.imageUrl,
    this.productCount = 0,
    this.subCategories = const [],
    this.isExclusiveToMagaiees = false,
    this.isVisible = true,
  });

  factory CategoryModel.fromJson(Map<String, dynamic> json) {
    var rawSubs = json['subCategories'] ?? json['sub_categories'];
    List<SubCategoryModel> subs = [];
    if (rawSubs is List) {
      subs = rawSubs.map((s) => SubCategoryModel.fromJson(s as Map<String, dynamic>)).toList();
    }
    return CategoryModel(
      id: json['id']?.toString() ?? '',
      name: json['name'] ?? '',
      nameAr: json['nameAr'] ?? json['name_ar'] ?? '',
      iconName: json['iconName'] ?? json['icon_name'],
      imageUrl: json['imageUrl'] ?? json['image_url'],
      productCount: (json['productCount'] as num?)?.toInt() ?? (json['product_count'] as num?)?.toInt() ?? 0,
      subCategories: subs,
      isExclusiveToMagaiees: (json['isExclusiveToMagaiees'] ?? json['is_magaiees_exclusive'] ?? json['is_exclusive'] ?? false) == true,
      isVisible: (json['isVisible'] ?? json['is_visible'] ?? json['is_active'] ?? true) == true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'nameAr': nameAr,
      'iconName': iconName,
      'imageUrl': imageUrl,
      'productCount': productCount,
      'subCategories': subCategories.map((s) => s.toJson()).toList(),
      'isExclusiveToMagaiees': isExclusiveToMagaiees,
      'isVisible': isVisible,
    };
  }

  CategoryModel copyWith({
    String? id,
    String? name,
    String? nameAr,
    String? iconName,
    String? imageUrl,
    int? productCount,
    List<SubCategoryModel>? subCategories,
    bool? isExclusiveToMagaiees,
    bool? isVisible,
  }) {
    return CategoryModel(
      id: id ?? this.id,
      name: name ?? this.name,
      nameAr: nameAr ?? this.nameAr,
      iconName: iconName ?? this.iconName,
      imageUrl: imageUrl ?? this.imageUrl,
      productCount: productCount ?? this.productCount,
      subCategories: subCategories ?? this.subCategories,
      isExclusiveToMagaiees: isExclusiveToMagaiees ?? this.isExclusiveToMagaiees,
      isVisible: isVisible ?? this.isVisible,
    );
  }
}
