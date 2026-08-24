// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'store_application.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_StoreApplication _$StoreApplicationFromJson(Map<String, dynamic> json) =>
    _StoreApplication(
      id: json['id'] as String,
      userId: json['userId'] as String,
      storeName: json['storeName'] as String,
      storeNameAr: json['storeNameAr'] as String,
      businessLicenseNumber: json['businessLicenseNumber'] as String,
      businessLicenseImageUrl: json['businessLicenseImageUrl'] as String?,
      city: json['city'] as String,
      address: json['address'] as String?,
      description: json['description'] as String?,
      descriptionAr: json['descriptionAr'] as String?,
      status: json['status'] as String? ?? 'pending',
      submittedAt: json['submittedAt'] == null
          ? null
          : DateTime.parse(json['submittedAt'] as String),
      reviewedAt: json['reviewedAt'] == null
          ? null
          : DateTime.parse(json['reviewedAt'] as String),
      rejectionReason: json['rejectionReason'] as String?,
    );

Map<String, dynamic> _$StoreApplicationToJson(_StoreApplication instance) =>
    <String, dynamic>{
      'id': instance.id,
      'userId': instance.userId,
      'storeName': instance.storeName,
      'storeNameAr': instance.storeNameAr,
      'businessLicenseNumber': instance.businessLicenseNumber,
      'businessLicenseImageUrl': instance.businessLicenseImageUrl,
      'city': instance.city,
      'address': instance.address,
      'description': instance.description,
      'descriptionAr': instance.descriptionAr,
      'status': instance.status,
      'submittedAt': instance.submittedAt?.toIso8601String(),
      'reviewedAt': instance.reviewedAt?.toIso8601String(),
      'rejectionReason': instance.rejectionReason,
    };
