// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'market_price_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_MarketPriceModel _$MarketPriceModelFromJson(Map<String, dynamic> json) =>
    _MarketPriceModel(
      materialName: json['materialName'] as String,
      materialNameAr: json['materialNameAr'] as String,
      currentPrice: (json['currentPrice'] as num).toDouble(),
      previousPrice: (json['previousPrice'] as num).toDouble(),
      unit: json['unit'] as String,
      unitAr: json['unitAr'] as String,
      lastUpdated: json['lastUpdated'] == null
          ? null
          : DateTime.parse(json['lastUpdated'] as String),
    );

Map<String, dynamic> _$MarketPriceModelToJson(_MarketPriceModel instance) =>
    <String, dynamic>{
      'materialName': instance.materialName,
      'materialNameAr': instance.materialNameAr,
      'currentPrice': instance.currentPrice,
      'previousPrice': instance.previousPrice,
      'unit': instance.unit,
      'unitAr': instance.unitAr,
      'lastUpdated': instance.lastUpdated?.toIso8601String(),
    };
