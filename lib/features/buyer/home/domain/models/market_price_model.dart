import 'package:freezed_annotation/freezed_annotation.dart';

part 'market_price_model.freezed.dart';
part 'market_price_model.g.dart';

@freezed
abstract class MarketPriceModel with _$MarketPriceModel {
  const MarketPriceModel._();

  const factory MarketPriceModel({
    required String materialName,
    required String materialNameAr,
    required double currentPrice,
    required double previousPrice,
    required String unit,
    required String unitAr,
    DateTime? lastUpdated,
  }) = _MarketPriceModel;

  factory MarketPriceModel.fromJson(Map<String, dynamic> json) =>
      _$MarketPriceModelFromJson(json);

  double get changePercent => previousPrice > 0 ? ((currentPrice - previousPrice) / previousPrice) * 100 : 0;
  bool get isUp => currentPrice >= previousPrice;
}
