import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:magaiees_marketplace/core/enums/user_role.dart';

part 'user_model.freezed.dart';
part 'user_model.g.dart';

/// Core user model representing an authenticated user.
///
/// The [hasStore] flag determines whether the user can toggle
/// into Merchant mode. A user starts as a buyer and applies
/// to create a store.
@freezed
abstract class UserModel with _$UserModel {
  const factory UserModel({
    /// Unique user identifier.
    required String id,

    /// User's display name (English).
    required String name,

    /// User's display name (Arabic).
    String? nameAr,

    /// Email address.
    required String email,

    /// Phone number (with country code).
    required String phone,

    /// User's account role.
    @Default(UserRole.buyer) UserRole role,

    /// Whether the user has an approved store.
    @Default(false) bool hasStore,

    /// The user's store ID, if they have one.
    String? storeId,

    /// Store name (English), if applicable.
    String? storeName,

    /// Store name (Arabic), if applicable.
    String? storeNameAr,

    /// Profile image URL.
    String? profileImageUrl,

    /// Authentication token.
    String? token,

    /// Refresh token.
    String? refreshToken,

    /// Granular permissions (especially for employees).
    @Default({}) Map<String, dynamic> permissions,
  }) = _UserModel;

  factory UserModel.fromJson(Map<String, dynamic> json) =>
      _$UserModelFromJson(json);
}
