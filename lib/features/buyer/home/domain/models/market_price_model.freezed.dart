// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'market_price_model.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$MarketPriceModel {

 String get materialName; String get materialNameAr; double get currentPrice; double get previousPrice; String get unit; String get unitAr; DateTime? get lastUpdated;
/// Create a copy of MarketPriceModel
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$MarketPriceModelCopyWith<MarketPriceModel> get copyWith => _$MarketPriceModelCopyWithImpl<MarketPriceModel>(this as MarketPriceModel, _$identity);

  /// Serializes this MarketPriceModel to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is MarketPriceModel&&(identical(other.materialName, materialName) || other.materialName == materialName)&&(identical(other.materialNameAr, materialNameAr) || other.materialNameAr == materialNameAr)&&(identical(other.currentPrice, currentPrice) || other.currentPrice == currentPrice)&&(identical(other.previousPrice, previousPrice) || other.previousPrice == previousPrice)&&(identical(other.unit, unit) || other.unit == unit)&&(identical(other.unitAr, unitAr) || other.unitAr == unitAr)&&(identical(other.lastUpdated, lastUpdated) || other.lastUpdated == lastUpdated));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,materialName,materialNameAr,currentPrice,previousPrice,unit,unitAr,lastUpdated);

@override
String toString() {
  return 'MarketPriceModel(materialName: $materialName, materialNameAr: $materialNameAr, currentPrice: $currentPrice, previousPrice: $previousPrice, unit: $unit, unitAr: $unitAr, lastUpdated: $lastUpdated)';
}


}

/// @nodoc
abstract mixin class $MarketPriceModelCopyWith<$Res>  {
  factory $MarketPriceModelCopyWith(MarketPriceModel value, $Res Function(MarketPriceModel) _then) = _$MarketPriceModelCopyWithImpl;
@useResult
$Res call({
 String materialName, String materialNameAr, double currentPrice, double previousPrice, String unit, String unitAr, DateTime? lastUpdated
});




}
/// @nodoc
class _$MarketPriceModelCopyWithImpl<$Res>
    implements $MarketPriceModelCopyWith<$Res> {
  _$MarketPriceModelCopyWithImpl(this._self, this._then);

  final MarketPriceModel _self;
  final $Res Function(MarketPriceModel) _then;

/// Create a copy of MarketPriceModel
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? materialName = null,Object? materialNameAr = null,Object? currentPrice = null,Object? previousPrice = null,Object? unit = null,Object? unitAr = null,Object? lastUpdated = freezed,}) {
  return _then(_self.copyWith(
materialName: null == materialName ? _self.materialName : materialName // ignore: cast_nullable_to_non_nullable
as String,materialNameAr: null == materialNameAr ? _self.materialNameAr : materialNameAr // ignore: cast_nullable_to_non_nullable
as String,currentPrice: null == currentPrice ? _self.currentPrice : currentPrice // ignore: cast_nullable_to_non_nullable
as double,previousPrice: null == previousPrice ? _self.previousPrice : previousPrice // ignore: cast_nullable_to_non_nullable
as double,unit: null == unit ? _self.unit : unit // ignore: cast_nullable_to_non_nullable
as String,unitAr: null == unitAr ? _self.unitAr : unitAr // ignore: cast_nullable_to_non_nullable
as String,lastUpdated: freezed == lastUpdated ? _self.lastUpdated : lastUpdated // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

}


/// Adds pattern-matching-related methods to [MarketPriceModel].
extension MarketPriceModelPatterns on MarketPriceModel {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _MarketPriceModel value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _MarketPriceModel() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _MarketPriceModel value)  $default,){
final _that = this;
switch (_that) {
case _MarketPriceModel():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _MarketPriceModel value)?  $default,){
final _that = this;
switch (_that) {
case _MarketPriceModel() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String materialName,  String materialNameAr,  double currentPrice,  double previousPrice,  String unit,  String unitAr,  DateTime? lastUpdated)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _MarketPriceModel() when $default != null:
return $default(_that.materialName,_that.materialNameAr,_that.currentPrice,_that.previousPrice,_that.unit,_that.unitAr,_that.lastUpdated);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String materialName,  String materialNameAr,  double currentPrice,  double previousPrice,  String unit,  String unitAr,  DateTime? lastUpdated)  $default,) {final _that = this;
switch (_that) {
case _MarketPriceModel():
return $default(_that.materialName,_that.materialNameAr,_that.currentPrice,_that.previousPrice,_that.unit,_that.unitAr,_that.lastUpdated);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String materialName,  String materialNameAr,  double currentPrice,  double previousPrice,  String unit,  String unitAr,  DateTime? lastUpdated)?  $default,) {final _that = this;
switch (_that) {
case _MarketPriceModel() when $default != null:
return $default(_that.materialName,_that.materialNameAr,_that.currentPrice,_that.previousPrice,_that.unit,_that.unitAr,_that.lastUpdated);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _MarketPriceModel extends MarketPriceModel {
  const _MarketPriceModel({required this.materialName, required this.materialNameAr, required this.currentPrice, required this.previousPrice, required this.unit, required this.unitAr, this.lastUpdated}): super._();
  factory _MarketPriceModel.fromJson(Map<String, dynamic> json) => _$MarketPriceModelFromJson(json);

@override final  String materialName;
@override final  String materialNameAr;
@override final  double currentPrice;
@override final  double previousPrice;
@override final  String unit;
@override final  String unitAr;
@override final  DateTime? lastUpdated;

/// Create a copy of MarketPriceModel
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$MarketPriceModelCopyWith<_MarketPriceModel> get copyWith => __$MarketPriceModelCopyWithImpl<_MarketPriceModel>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$MarketPriceModelToJson(this, );
}

@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _MarketPriceModel&&(identical(other.materialName, materialName) || other.materialName == materialName)&&(identical(other.materialNameAr, materialNameAr) || other.materialNameAr == materialNameAr)&&(identical(other.currentPrice, currentPrice) || other.currentPrice == currentPrice)&&(identical(other.previousPrice, previousPrice) || other.previousPrice == previousPrice)&&(identical(other.unit, unit) || other.unit == unit)&&(identical(other.unitAr, unitAr) || other.unitAr == unitAr)&&(identical(other.lastUpdated, lastUpdated) || other.lastUpdated == lastUpdated));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode => Object.hash(runtimeType,materialName,materialNameAr,currentPrice,previousPrice,unit,unitAr,lastUpdated);

@override
String toString() {
  return 'MarketPriceModel(materialName: $materialName, materialNameAr: $materialNameAr, currentPrice: $currentPrice, previousPrice: $previousPrice, unit: $unit, unitAr: $unitAr, lastUpdated: $lastUpdated)';
}


}

/// @nodoc
abstract mixin class _$MarketPriceModelCopyWith<$Res> implements $MarketPriceModelCopyWith<$Res> {
  factory _$MarketPriceModelCopyWith(_MarketPriceModel value, $Res Function(_MarketPriceModel) _then) = __$MarketPriceModelCopyWithImpl;
@override @useResult
$Res call({
 String materialName, String materialNameAr, double currentPrice, double previousPrice, String unit, String unitAr, DateTime? lastUpdated
});




}
/// @nodoc
class __$MarketPriceModelCopyWithImpl<$Res>
    implements _$MarketPriceModelCopyWith<$Res> {
  __$MarketPriceModelCopyWithImpl(this._self, this._then);

  final _MarketPriceModel _self;
  final $Res Function(_MarketPriceModel) _then;

/// Create a copy of MarketPriceModel
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? materialName = null,Object? materialNameAr = null,Object? currentPrice = null,Object? previousPrice = null,Object? unit = null,Object? unitAr = null,Object? lastUpdated = freezed,}) {
  return _then(_MarketPriceModel(
materialName: null == materialName ? _self.materialName : materialName // ignore: cast_nullable_to_non_nullable
as String,materialNameAr: null == materialNameAr ? _self.materialNameAr : materialNameAr // ignore: cast_nullable_to_non_nullable
as String,currentPrice: null == currentPrice ? _self.currentPrice : currentPrice // ignore: cast_nullable_to_non_nullable
as double,previousPrice: null == previousPrice ? _self.previousPrice : previousPrice // ignore: cast_nullable_to_non_nullable
as double,unit: null == unit ? _self.unit : unit // ignore: cast_nullable_to_non_nullable
as String,unitAr: null == unitAr ? _self.unitAr : unitAr // ignore: cast_nullable_to_non_nullable
as String,lastUpdated: freezed == lastUpdated ? _self.lastUpdated : lastUpdated // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}


}

// dart format on
