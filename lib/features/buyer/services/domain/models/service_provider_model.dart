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
  });
}