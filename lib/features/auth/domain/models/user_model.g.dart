// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'user_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_UserModel _$UserModelFromJson(Map<String, dynamic> json) => _UserModel(
  id: json['id'] as String,
  name: json['name'] as String,
  nameAr: json['nameAr'] as String?,
  email: json['email'] as String,
  phone: json['phone'] as String,
  role: $enumDecodeNullable(_$UserRoleEnumMap, json['role']) ?? UserRole.buyer,
  hasStore: json['hasStore'] as bool? ?? false,
  storeId: json['storeId'] as String?,
  storeName: json['storeName'] as String?,
  storeNameAr: json['storeNameAr'] as String?,
  profileImageUrl: json['profileImageUrl'] as String?,
  token: json['token'] as String?,
  refreshToken: json['refreshToken'] as String?,
  permissions: json['permissions'] as Map<String, dynamic>? ?? const {},
  merchantPoints: (json['merchantPoints'] as num?)?.toInt() ?? 0,
  merchantTier: json['merchantTier'] as String? ?? 'beginner',
  directPaymentEnabled: json['directPaymentEnabled'] as bool? ?? false,
  bankName: json['bankName'] as String?,
  bankAccountName: json['bankAccountName'] as String?,
  bankAccountNumber: json['bankAccountNumber'] as String?,
);

Map<String, dynamic> _$UserModelToJson(_UserModel instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'nameAr': instance.nameAr,
      'email': instance.email,
      'phone': instance.phone,
      'role': _$UserRoleEnumMap[instance.role]!,
      'hasStore': instance.hasStore,
      'storeId': instance.storeId,
      'storeName': instance.storeName,
      'storeNameAr': instance.storeNameAr,
      'profileImageUrl': instance.profileImageUrl,
      'token': instance.token,
      'refreshToken': instance.refreshToken,
      'permissions': instance.permissions,
      'merchantPoints': instance.merchantPoints,
      'merchantTier': instance.merchantTier,
      'directPaymentEnabled': instance.directPaymentEnabled,
      'bankName': instance.bankName,
      'bankAccountName': instance.bankAccountName,
      'bankAccountNumber': instance.bankAccountNumber,
    };

const _$UserRoleEnumMap = {
  UserRole.guest: 'guest',
  UserRole.buyer: 'buyer',
  UserRole.merchant: 'merchant',
  UserRole.employee: 'employee',
  UserRole.admin: 'admin',
};
