import 'package:freezed_annotation/freezed_annotation.dart';

part 'store_application.freezed.dart';
part 'store_application.g.dart';

/// Represents a merchant store application.
///
/// When a buyer wants to become a merchant, they submit
/// a store application for review.
@freezed
abstract class StoreApplication with _$StoreApplication {
  const factory StoreApplication({
    required String id,
    required String userId,
    required String storeName,
    required String storeNameAr,
    required String businessLicenseNumber,
    String? businessLicenseImageUrl,
    required String city,
    String? address,
    String? description,
    String? descriptionAr,
    @Default('pending') String status, // pending, approved, rejected
    DateTime? submittedAt,
    DateTime? reviewedAt,
    String? rejectionReason,
  }) = _StoreApplication;

  factory StoreApplication.fromJson(Map<String, dynamic> json) =>
      _$StoreApplicationFromJson(json);
}
