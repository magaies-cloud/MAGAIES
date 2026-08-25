class ServiceProviderModel {
  final String id;
  final String name;
  final String nameAr;
  final String trade;
  final String tradeAr;
  final String categoryId;
  final double rating;
  final int reviewsCount;
  final String experienceYears;
  final String priceEstimate;
  final String phone;
  final String location;
  final String locationAr;
  final bool isVerified;
  final String? avatarUrl;
  final String contractType;
  final double contractValue;
  final int completedOrdersCount;

  const ServiceProviderModel({
    required this.id,
    required this.name,
    required this.nameAr,
    required this.trade,
    required this.tradeAr,
    required this.categoryId,
    required this.rating,
    required this.reviewsCount,
    required this.experienceYears,
    required this.priceEstimate,
    required this.phone,
    required this.location,
    required this.locationAr,
    this.isVerified = true,
    this.avatarUrl,
    this.contractType = 'none',
    this.contractValue = 0.0,
    this.completedOrdersCount = 0,
  });

  factory ServiceProviderModel.fromJson(Map<String, dynamic> json) {
    return ServiceProviderModel(
      id: json['id']?.toString() ?? '',
      name: json['name'] ?? '',
      nameAr: json['name_ar'] ?? json['nameAr'] ?? '',
      trade: json['trade'] ?? '',
      tradeAr: json['trade_ar'] ?? json['tradeAr'] ?? '',
      categoryId: json['category_id'] ?? json['categoryId'] ?? '',
      rating: (json['rating'] != null) ? (num.tryParse(json['rating'].toString())?.toDouble() ?? 5.0) : 5.0,
      reviewsCount: (json['reviews_count'] != null || json['reviewsCount'] != null) 
          ? (num.tryParse((json['reviews_count'] ?? json['reviewsCount']).toString())?.toInt() ?? 0) : 0,
      experienceYears: json['experience_years'] ?? json['experienceYears'] ?? '',
      priceEstimate: json['price_estimate'] ?? json['priceEstimate'] ?? '',
      phone: json['phone'] ?? '',
      location: json['location'] ?? '',
      locationAr: json['location_ar'] ?? json['locationAr'] ?? '',
      isVerified: (json['is_verified'] ?? json['isVerified'] ?? true) == true,
      avatarUrl: json['avatar_url'] ?? json['avatarUrl'],
      contractType: json['contract_type'] ?? 'none',
      contractValue: (json['contract_value'] != null) ? (num.tryParse(json['contract_value'].toString())?.toDouble() ?? 0.0) : 0.0,
      completedOrdersCount: (json['completed_orders_count'] != null) ? (num.tryParse(json['completed_orders_count'].toString())?.toInt() ?? 0) : 0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'name_ar': nameAr,
      'trade': trade,
      'trade_ar': tradeAr,
      'category_id': categoryId,
      'rating': rating,
      'reviews_count': reviewsCount,
      'experience_years': experienceYears,
      'price_estimate': priceEstimate,
      'phone': phone,
      'location': location,
      'location_ar': locationAr,
      'is_verified': isVerified,
      'avatar_url': avatarUrl,
      'contract_type': contractType,
      'contract_value': contractValue,
      'completed_orders_count': completedOrdersCount,
    };
  }

  ServiceProviderModel copyWith({
    String? id,
    String? name,
    String? nameAr,
    String? trade,
    String? tradeAr,
    String? categoryId,
    double? rating,
    int? reviewsCount,
    String? experienceYears,
    String? priceEstimate,
    String? phone,
    String? location,
    String? locationAr,
    bool? isVerified,
    String? avatarUrl,
    String? contractType,
    double? contractValue,
    int? completedOrdersCount,
  }) {
    return ServiceProviderModel(
      id: id ?? this.id,
      name: name ?? this.name,
      nameAr: nameAr ?? this.nameAr,
      trade: trade ?? this.trade,
      tradeAr: tradeAr ?? this.tradeAr,
      categoryId: categoryId ?? this.categoryId,
      rating: rating ?? this.rating,
      reviewsCount: reviewsCount ?? this.reviewsCount,
      experienceYears: experienceYears ?? this.experienceYears,
      priceEstimate: priceEstimate ?? this.priceEstimate,
      phone: phone ?? this.phone,
      location: location ?? this.location,
      locationAr: locationAr ?? this.locationAr,
      isVerified: isVerified ?? this.isVerified,
      avatarUrl: avatarUrl ?? this.avatarUrl,
      contractType: contractType ?? this.contractType,
      contractValue: contractValue ?? this.contractValue,
      completedOrdersCount: completedOrdersCount ?? this.completedOrdersCount,
    );
  }
}

