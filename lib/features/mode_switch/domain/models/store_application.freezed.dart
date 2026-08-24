// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'store_application.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$StoreApplication {

 String get id; String get userId; String get storeName; String get storeNameAr; String get businessLicenseNumber; String? get businessLicenseImageUrl; String get city; String? get address; String? get description; String? get descriptionAr; String get status;// pending, approved, rejected
 DateTime? get submittedAt; DateTime? get reviewedAt; String? get rejectionReason;
/// Create a copy of StoreApplication
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$StoreApplicationCopyWith<StoreApplication> get copyWith => _$StoreApplicationCopyWithImpl<StoreApplication>(this as StoreApplication, _$identity);

  /// Serializes this StoreApplication to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is StoreApplication&&(identical(other.id, id) || other.id == id)&&(identical(other.userId, userId) || other.userId == userId)&&(identical(other.storeName, storeName) || other.storeName == storeName)&&(identical(other.storeNameAr, storeNameAr) || other.storeNameAr == storeNameAr)&&(identical(other.businessLicenseNumber, businessLicenseNumber) || other.businessLicenseNumber == businessLicenseNumber)&&(identical(other.businessLicenseImageUrl, businessLicenseImageUrl) || other.businessLicenseImageUrl == businessLicenseImageUrl)&&(identical(other.city, city) || other.city == city)&&(identical(other.address, address) || other.address == address)&&(identical(other.description, description) || other.description == description)&&(identical(other.descriptionAr, descriptionAr) || other.descriptionAr == descriptionAr)&&(identical(other.status, status) || other.status == status)&&(identical(other.submittedAt, submittedAt) || other.submittedAt == submittedAt)&&(identical(other.reviewedAt, reviewedAt) || other.reviewedAt == reviewedAt)&&(identical(other.rejectionReason, rejectionReason) || other.rejectionReason == rejectionReason));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,userId,storeName,storeNameAr,businessLicenseNumber,businessLicenseImageUrl,city,address,description,descriptionAr,status,submittedAt,reviewedAt,rejectionReason);

@override
String toString() {
  return 'StoreApplication(id: $id, userId: $userId, storeName: $storeName, storeNameAr: $storeNameAr, businessLicenseNumber: $businessLicenseNumber, businessLicenseImageUrl: $businessLicenseImageUrl, city: $city, address: $address, description: $description, descriptionAr: $descriptionAr, status: $status, submittedAt: $submittedAt, reviewedAt: $reviewedAt, rejectionReason: $rejectionReason)';
}


}

/// @nodoc
abstract mixin class $StoreApplicationCopyWith<$Res>  {
  factory $StoreApplicationCopyWith(StoreApplication value, $Res Function(StoreApplication) _then) = _$StoreApplicationCopyWithImpl;
@useResult
$Res call({
 String id, String userId, String storeName, String storeNameAr, String businessLicenseNumber, String? businessLicenseImageUrl, String city, String? address, String? description, String? descriptionAr, String status, DateTime? submittedAt, DateTime? reviewedAt, String? rejectionReason
});




}
/// @nodoc
class _$StoreApplicationCopyWithImpl<$Res>
    implements $StoreApplicationCopyWith<$Res> {
  _$StoreApplicationCopyWithImpl(this._self, this._then);

  final StoreApplication _self;
  final $Res Function(StoreApplication) _then;

/// Create a copy of StoreApplication
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? userId = null,Object? storeName = null,Object? storeNameAr = null,Object? businessLicenseNumber = null,Object? businessLicenseImageUrl = freezed,Object? city = null,Object? address = freezed,Object? description = freezed,Object? descriptionAr = freezed,Object? status = null,Object? submittedAt = freezed,Object? reviewedAt = freezed,Object? rejectionReason = freezed,}) {
  return _then(_self.copyWith(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,userId: null == userId ? _self.userId : userId // ignore: cast_nullable_to_non_nullable
as String,storeName: null == storeName ? _self.storeName : storeName // ignore: cast_nullable_to_non_nullable
as String,storeNameAr: null == storeNameAr ? _self.storeNameAr : storeNameAr // ignore: cast_nullable_to_non_nullable
as String,businessLicenseNumber: null == businessLicenseNumber ? _self.businessLicenseNumber : businessLicenseNumber // ignore: cast_nullable_to_non_nullable
as String,businessLicenseImageUrl: freezed == businessLicenseImageUrl ? _self.businessLicenseImageUrl : businessLicenseImageUrl // ignore: cast_nullable_to_non_nullable
as String?,city: null == city ? _self.city : city // ignore: cast_nullable_to_non_nullable
as String,address: freezed == address ? _self.address : address // ignore: cast_nullable_to_non_nullable
as String?,description: freezed == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String?,descriptionAr: freezed == descriptionAr ? _self.descriptionAr : descriptionAr // ignore: cast_nullable_to_non_nullable
as String?,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,submittedAt: freezed == submittedAt ? _self.submittedAt : submittedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,reviewedAt: freezed == reviewedAt ? _self.reviewedAt : reviewedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,rejectionReason: freezed == rejectionReason ? _self.rejectionReason : rejectionReason // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [StoreApplication].
extension StoreApplicationPatterns on StoreApplication {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _StoreApplication value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _StoreApplication() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _StoreApplication value)  $default,){
final _that = this;
switch (_that) {
case _StoreApplication():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _StoreApplication value)?  $default,){
final _that = this;
switch (_that) {
case _StoreApplication() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String userId,  String storeName,  String storeNameAr,  String businessLicenseNumber,  String? businessLicenseImageUrl,  String city,  String? address,  String? description,  String? descriptionAr,  String status,  DateTime? submittedAt,  DateTime? reviewedAt,  String? rejectionReason)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _StoreApplication() when $default != null:
return $default(_that.id,_that.userId,_that.storeName,_that.storeNameAr,_that.businessLicenseNumber,_that.businessLicenseImageUrl,_that.city,_that.address,_that.description,_that.descriptionAr,_that.status,_that.submittedAt,_that.reviewedAt,_that.rejectionReason);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String userId,  String storeName,  String storeNameAr,  String businessLicenseNumber,  String? businessLicenseImageUrl,  String city,  String? address,  String? description,  String? descriptionAr,  String status,  DateTime? submittedAt,  DateTime? reviewedAt,  String? rejectionReason)  $default,) {final _that = this;
switch (_that) {
case _StoreApplication():
return $default(_that.id,_that.userId,_that.storeName,_that.storeNameAr,_that.businessLicenseNumber,_that.businessLicenseImageUrl,_that.city,_that.address,_that.description,_that.descriptionAr,_that.status,_that.submittedAt,_that.reviewedAt,_that.rejectionReason);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String userId,  String storeName,  String storeNameAr,  String businessLicenseNumber,  String? businessLicenseImageUrl,  String city,  String? address,  String? description,  String? descriptionAr,  String status,  DateTime? submittedAt,  DateTime? reviewedAt,  String? rejectionReason)?  $default,) {final _that = this;
switch (_that) {
case _StoreApplication() when $default != null:
return $default(_that.id,_that.userId,_that.storeName,_that.storeNameAr,_that.businessLicenseNumber,_that.businessLicenseImageUrl,_that.city,_that.address,_that.description,_that.descriptionAr,_that.status,_that.submittedAt,_that.reviewedAt,_that.rejectionReason);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _StoreApplication implements StoreApplication {
  const _StoreApplication({required this.id, required this.userId, required this.storeName, required this.storeNameAr, required this.businessLicenseNumber, this.businessLicenseImageUrl, required this.city, this.address, this.description, this.descriptionAr, this.status = 'pending', this.submittedAt, this.reviewedAt, this.rejectionReason});
  factory _StoreApplication.fromJson(Map<String, dynamic> json) => _$StoreApplicationFromJson(json);

@override final  String id;
@override final  String userId;
@override final  String storeName;
@override final  String storeNameAr;
@override final  String businessLicenseNumber;
@override final  String? businessLicenseImageUrl;
@override final  String city;
@override final  String? address;
@override final  String? description;
@override final  String? descriptionAr;
@override@JsonKey() final  String status;
// pending, approved, rejected
@override final  DateTime? submittedAt;
@override final  DateTime? reviewedAt;
@override final  String? rejectionReason;

/// Create a copy of StoreApplication
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$StoreApplicationCopyWith<_StoreApplication> get copyWith => __$StoreApplicationCopyWithImpl<_StoreApplication>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$StoreApplicationToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _StoreApplication&&(identical(other.id, id) || other.id == id)&&(identical(other.userId, userId) || other.userId == userId)&&(identical(other.storeName, storeName) || other.storeName == storeName)&&(identical(other.storeNameAr, storeNameAr) || other.storeNameAr == storeNameAr)&&(identical(other.businessLicenseNumber, businessLicenseNumber) || other.businessLicenseNumber == businessLicenseNumber)&&(identical(other.businessLicenseImageUrl, businessLicenseImageUrl) || other.businessLicenseImageUrl == businessLicenseImageUrl)&&(identical(other.city, city) || other.city == city)&&(identical(other.address, address) || other.address == address)&&(identical(other.description, description) || other.description == description)&&(identical(other.descriptionAr, descriptionAr) || other.descriptionAr == descriptionAr)&&(identical(other.status, status) || other.status == status)&&(identical(other.submittedAt, submittedAt) || other.submittedAt == submittedAt)&&(identical(other.reviewedAt, reviewedAt) || other.reviewedAt == reviewedAt)&&(identical(other.rejectionReason, rejectionReason) || other.rejectionReason == rejectionReason));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,id,userId,storeName,storeNameAr,businessLicenseNumber,businessLicenseImageUrl,city,address,description,descriptionAr,status,submittedAt,reviewedAt,rejectionReason);

@override
String toString() {
  return 'StoreApplication(id: $id, userId: $userId, storeName: $storeName, storeNameAr: $storeNameAr, businessLicenseNumber: $businessLicenseNumber, businessLicenseImageUrl: $businessLicenseImageUrl, city: $city, address: $address, description: $description, descriptionAr: $descriptionAr, status: $status, submittedAt: $submittedAt, reviewedAt: $reviewedAt, rejectionReason: $rejectionReason)';
}


}

/// @nodoc
abstract mixin class _$StoreApplicationCopyWith<$Res> implements $StoreApplicationCopyWith<$Res> {
  factory _$StoreApplicationCopyWith(_StoreApplication value, $Res Function(_StoreApplication) _then) = __$StoreApplicationCopyWithImpl;
@override @useResult
$Res call({
 String id, String userId, String storeName, String storeNameAr, String businessLicenseNumber, String? businessLicenseImageUrl, String city, String? address, String? description, String? descriptionAr, String status, DateTime? submittedAt, DateTime? reviewedAt, String? rejectionReason
});




}
/// @nodoc
class __$StoreApplicationCopyWithImpl<$Res>
    implements _$StoreApplicationCopyWith<$Res> {
  __$StoreApplicationCopyWithImpl(this._self, this._then);

  final _StoreApplication _self;
  final $Res Function(_StoreApplication) _then;

/// Create a copy of StoreApplication
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? userId = null,Object? storeName = null,Object? storeNameAr = null,Object? businessLicenseNumber = null,Object? businessLicenseImageUrl = freezed,Object? city = null,Object? address = freezed,Object? description = freezed,Object? descriptionAr = freezed,Object? status = null,Object? submittedAt = freezed,Object? reviewedAt = freezed,Object? rejectionReason = freezed,}) {
  return _then(_StoreApplication(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,userId: null == userId ? _self.userId : userId // ignore: cast_nullable_to_non_nullable
as String,storeName: null == storeName ? _self.storeName : storeName // ignore: cast_nullable_to_non_nullable
as String,storeNameAr: null == storeNameAr ? _self.storeNameAr : storeNameAr // ignore: cast_nullable_to_non_nullable
as String,businessLicenseNumber: null == businessLicenseNumber ? _self.businessLicenseNumber : businessLicenseNumber // ignore: cast_nullable_to_non_nullable
as String,businessLicenseImageUrl: freezed == businessLicenseImageUrl ? _self.businessLicenseImageUrl : businessLicenseImageUrl // ignore: cast_nullable_to_non_nullable
as String?,city: null == city ? _self.city : city // ignore: cast_nullable_to_non_nullable
as String,address: freezed == address ? _self.address : address // ignore: cast_nullable_to_non_nullable
as String?,description: freezed == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String?,descriptionAr: freezed == descriptionAr ? _self.descriptionAr : descriptionAr // ignore: cast_nullable_to_non_nullable
as String?,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,submittedAt: freezed == submittedAt ? _self.submittedAt : submittedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,reviewedAt: freezed == reviewedAt ? _self.reviewedAt : reviewedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,rejectionReason: freezed == rejectionReason ? _self.rejectionReason : rejectionReason // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

// dart format on
