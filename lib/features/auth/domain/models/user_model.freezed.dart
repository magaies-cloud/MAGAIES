// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'user_model.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$UserModel {

/// Unique user identifier.
 String get id;/// User's display name (English).
 String get name;/// User's display name (Arabic).
 String? get nameAr;/// Email address.
 String get email;/// Phone number (with country code).
 String get phone;/// User's account role.
 UserRole get role;/// Whether the user has an approved store.
 bool get hasStore;/// The user's store ID, if they have one.
 String? get storeId;/// Store name (English), if applicable.
 String? get storeName;/// Store name (Arabic), if applicable.
 String? get storeNameAr;/// Profile image URL.
 String? get profileImageUrl;/// Authentication token.
 String? get token;/// Refresh token.
 String? get refreshToken;/// Granular permissions (especially for employees).
 Map<String, dynamic> get permissions;
/// Create a copy of UserModel
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$UserModelCopyWith<UserModel> get copyWith => _$UserModelCopyWithImpl<UserModel>(this as UserModel, _$identity);

  /// Serializes this UserModel to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is UserModel&&(identical(other.id, id) || other.id == id)&&(identical(other.name, name) || other.name == name)&&(identical(other.nameAr, nameAr) || other.nameAr == nameAr)&&(identical(other.email, email) || other.email == email)&&(identical(other.phone, phone) || other.phone == phone)&&(identical(other.role, role) || other.role == role)&&(identical(other.hasStore, hasStore) || other.hasStore == hasStore)&&(identical(other.storeId, storeId) || other.storeId == storeId)&&(identical(other.storeName, storeName) || other.storeName == storeName)&&(identical(other.storeNameAr, storeNameAr) || other.storeNameAr == storeNameAr)&&(identical(other.profileImageUrl, profileImageUrl) || other.profileImageUrl == profileImageUrl)&&(identical(other.token, token) || other.token == token)&&(identical(other.refreshToken, refreshToken) || other.refreshToken == refreshToken)&&const DeepCollectionEquality().equals(other.permissions, permissions));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,name,nameAr,email,phone,role,hasStore,storeId,storeName,storeNameAr,profileImageUrl,token,refreshToken,const DeepCollectionEquality().hash(permissions));

@override
String toString() {
  return 'UserModel(id: $id, name: $name, nameAr: $nameAr, email: $email, phone: $phone, role: $role, hasStore: $hasStore, storeId: $storeId, storeName: $storeName, storeNameAr: $storeNameAr, profileImageUrl: $profileImageUrl, token: $token, refreshToken: $refreshToken, permissions: $permissions)';
}


}

/// @nodoc
abstract mixin class $UserModelCopyWith<$Res>  {
  factory $UserModelCopyWith(UserModel value, $Res Function(UserModel) _then) = _$UserModelCopyWithImpl;
@useResult
$Res call({
 String id, String name, String? nameAr, String email, String phone, UserRole role, bool hasStore, String? storeId, String? storeName, String? storeNameAr, String? profileImageUrl, String? token, String? refreshToken, Map<String, dynamic> permissions
});




}
/// @nodoc
class _$UserModelCopyWithImpl<$Res>
    implements $UserModelCopyWith<$Res> {
  _$UserModelCopyWithImpl(this._self, this._then);

  final UserModel _self;
  final $Res Function(UserModel) _then;

/// Create a copy of UserModel
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? name = null,Object? nameAr = freezed,Object? email = null,Object? phone = null,Object? role = null,Object? hasStore = null,Object? storeId = freezed,Object? storeName = freezed,Object? storeNameAr = freezed,Object? profileImageUrl = freezed,Object? token = freezed,Object? refreshToken = freezed,Object? permissions = null,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,nameAr: freezed == nameAr ? _self.nameAr : nameAr // ignore: cast_nullable_to_non_nullable
as String?,email: null == email ? _self.email : email // ignore: cast_nullable_to_non_nullable
as String,phone: null == phone ? _self.phone : phone // ignore: cast_nullable_to_non_nullable
as String,role: null == role ? _self.role : role // ignore: cast_nullable_to_non_nullable
as UserRole,hasStore: null == hasStore ? _self.hasStore : hasStore // ignore: cast_nullable_to_non_nullable
as bool,storeId: freezed == storeId ? _self.storeId : storeId // ignore: cast_nullable_to_non_nullable
as String?,storeName: freezed == storeName ? _self.storeName : storeName // ignore: cast_nullable_to_non_nullable
as String?,storeNameAr: freezed == storeNameAr ? _self.storeNameAr : storeNameAr // ignore: cast_nullable_to_non_nullable
as String?,profileImageUrl: freezed == profileImageUrl ? _self.profileImageUrl : profileImageUrl // ignore: cast_nullable_to_non_nullable
as String?,token: freezed == token ? _self.token : token // ignore: cast_nullable_to_non_nullable
as String?,refreshToken: freezed == refreshToken ? _self.refreshToken : refreshToken // ignore: cast_nullable_to_non_nullable
as String?,permissions: null == permissions ? _self.permissions : permissions // ignore: cast_nullable_to_non_nullable
as Map<String, dynamic>,
  ));
}

}


/// Adds pattern-matching-related methods to [UserModel].
extension UserModelPatterns on UserModel {
/// A variant of `map` that fallback to returning `orElse`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _UserModel value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _UserModel() when $default != null:
return $default(_that);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// Callbacks receives the raw object, upcasted.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case final Subclass2 value:
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _UserModel value)  $default,){
final _that = this;
switch (_that) {
case _UserModel():
return $default(_that);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `map` that fallback to returning `null`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _UserModel value)?  $default,){
final _that = this;
switch (_that) {
case _UserModel() when $default != null:
return $default(_that);case _:
  return null;

}
}
/// A variant of `when` that fallback to an `orElse` callback.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String name,  String? nameAr,  String email,  String phone,  UserRole role,  bool hasStore,  String? storeId,  String? storeName,  String? storeNameAr,  String? profileImageUrl,  String? token,  String? refreshToken,  Map<String, dynamic> permissions)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _UserModel() when $default != null:
return $default(_that.id,_that.name,_that.nameAr,_that.email,_that.phone,_that.role,_that.hasStore,_that.storeId,_that.storeName,_that.storeNameAr,_that.profileImageUrl,_that.token,_that.refreshToken,_that.permissions);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// As opposed to `map`, this offers destructuring.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case Subclass2(:final field2):
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String name,  String? nameAr,  String email,  String phone,  UserRole role,  bool hasStore,  String? storeId,  String? storeName,  String? storeNameAr,  String? profileImageUrl,  String? token,  String? refreshToken,  Map<String, dynamic> permissions)  $default,) {final _that = this;
switch (_that) {
case _UserModel():
return $default(_that.id,_that.name,_that.nameAr,_that.email,_that.phone,_that.role,_that.hasStore,_that.storeId,_that.storeName,_that.storeNameAr,_that.profileImageUrl,_that.token,_that.refreshToken,_that.permissions);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `when` that fallback to returning `null`
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String name,  String? nameAr,  String email,  String phone,  UserRole role,  bool hasStore,  String? storeId,  String? storeName,  String? storeNameAr,  String? profileImageUrl,  String? token,  String? refreshToken,  Map<String, dynamic> permissions)?  $default,) {final _that = this;
switch (_that) {
case _UserModel() when $default != null:
return $default(_that.id,_that.name,_that.nameAr,_that.email,_that.phone,_that.role,_that.hasStore,_that.storeId,_that.storeName,_that.storeNameAr,_that.profileImageUrl,_that.token,_that.refreshToken,_that.permissions);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _UserModel implements UserModel {
  const _UserModel({required this.id, required this.name, this.nameAr, required this.email, required this.phone, this.role = UserRole.buyer, this.hasStore = false, this.storeId, this.storeName, this.storeNameAr, this.profileImageUrl, this.token, this.refreshToken, final  Map<String, dynamic> permissions = const {}}): _permissions = permissions;
  factory _UserModel.fromJson(Map<String, dynamic> json) => _$UserModelFromJson(json);

/// Unique user identifier.
@override final  String id;
/// User's display name (English).
@override final  String name;
/// User's display name (Arabic).
@override final  String? nameAr;
/// Email address.
@override final  String email;
/// Phone number (with country code).
@override final  String phone;
/// User's account role.
@override@JsonKey() final  UserRole role;
/// Whether the user has an approved store.
@override@JsonKey() final  bool hasStore;
/// The user's store ID, if they have one.
@override final  String? storeId;
/// Store name (English), if applicable.
@override final  String? storeName;
/// Store name (Arabic), if applicable.
@override final  String? storeNameAr;
/// Profile image URL.
@override final  String? profileImageUrl;
/// Authentication token.
@override final  String? token;
/// Refresh token.
@override final  String? refreshToken;
/// Granular permissions (especially for employees).
 final  Map<String, dynamic> _permissions;
/// Granular permissions (especially for employees).
@override@JsonKey() Map<String, dynamic> get permissions {
  if (_permissions is EqualUnmodifiableMapView) return _permissions;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableMapView(_permissions);
}


/// Create a copy of UserModel
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$UserModelCopyWith<_UserModel> get copyWith => __$UserModelCopyWithImpl<_UserModel>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$UserModelToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _UserModel&&(identical(other.id, id) || other.id == id)&&(identical(other.name, name) || other.name == name)&&(identical(other.nameAr, nameAr) || other.nameAr == nameAr)&&(identical(other.email, email) || other.email == email)&&(identical(other.phone, phone) || other.phone == phone)&&(identical(other.role, role) || other.role == role)&&(identical(other.hasStore, hasStore) || other.hasStore == hasStore)&&(identical(other.storeId, storeId) || other.storeId == storeId)&&(identical(other.storeName, storeName) || other.storeName == storeName)&&(identical(other.storeNameAr, storeNameAr) || other.storeNameAr == storeNameAr)&&(identical(other.profileImageUrl, profileImageUrl) || other.profileImageUrl == profileImageUrl)&&(identical(other.token, token) || other.token == token)&&(identical(other.refreshToken, refreshToken) || other.refreshToken == refreshToken)&&const DeepCollectionEquality().equals(other._permissions, _permissions));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,name,nameAr,email,phone,role,hasStore,storeId,storeName,storeNameAr,profileImageUrl,token,refreshToken,const DeepCollectionEquality().hash(_permissions));

@override
String toString() {
  return 'UserModel(id: $id, name: $name, nameAr: $nameAr, email: $email, phone: $phone, role: $role, hasStore: $hasStore, storeId: $storeId, storeName: $storeName, storeNameAr: $storeNameAr, profileImageUrl: $profileImageUrl, token: $token, refreshToken: $refreshToken, permissions: $permissions)';
}


}

/// @nodoc
abstract mixin class _$UserModelCopyWith<$Res> implements $UserModelCopyWith<$Res> {
  factory _$UserModelCopyWith(_UserModel value, $Res Function(_UserModel) _then) = __$UserModelCopyWithImpl;
@override @useResult
$Res call({
 String id, String name, String? nameAr, String email, String phone, UserRole role, bool hasStore, String? storeId, String? storeName, String? storeNameAr, String? profileImageUrl, String? token, String? refreshToken, Map<String, dynamic> permissions
});




}
/// @nodoc
class __$UserModelCopyWithImpl<$Res>
    implements _$UserModelCopyWith<$Res> {
  __$UserModelCopyWithImpl(this._self, this._then);

  final _UserModel _self;
  final $Res Function(_UserModel) _then;

/// Create a copy of UserModel
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? name = null,Object? nameAr = freezed,Object? email = null,Object? phone = null,Object? role = null,Object? hasStore = null,Object? storeId = freezed,Object? storeName = freezed,Object? storeNameAr = freezed,Object? profileImageUrl = freezed,Object? token = freezed,Object? refreshToken = freezed,Object? permissions = null,}) {
  return _then(_UserModel(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,nameAr: freezed == nameAr ? _self.nameAr : nameAr // ignore: cast_nullable_to_non_nullable
as String?,email: null == email ? _self.email : email // ignore: cast_nullable_to_non_nullable
as String,phone: null == phone ? _self.phone : phone // ignore: cast_nullable_to_non_nullable
as String,role: null == role ? _self.role : role // ignore: cast_nullable_to_non_nullable
as UserRole,hasStore: null == hasStore ? _self.hasStore : hasStore // ignore: cast_nullable_to_non_nullable
as bool,storeId: freezed == storeId ? _self.storeId : storeId // ignore: cast_nullable_to_non_nullable
as String?,storeName: freezed == storeName ? _self.storeName : storeName // ignore: cast_nullable_to_non_nullable
as String?,storeNameAr: freezed == storeNameAr ? _self.storeNameAr : storeNameAr // ignore: cast_nullable_to_non_nullable
as String?,profileImageUrl: freezed == profileImageUrl ? _self.profileImageUrl : profileImageUrl // ignore: cast_nullable_to_non_nullable
as String?,token: freezed == token ? _self.token : token // ignore: cast_nullable_to_non_nullable
as String?,refreshToken: freezed == refreshToken ? _self.refreshToken : refreshToken // ignore: cast_nullable_to_non_nullable
as String?,permissions: null == permissions ? _self._permissions : permissions // ignore: cast_nullable_to_non_nullable
as Map<String, dynamic>,
  ));
}


}

// dart format on
