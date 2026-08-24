/// Unit conversion utilities for construction materials.
///
/// In the rebar industry, pricing can be per "Weight Ton" (1000 kg)
/// or per "Count Ton" (a bundle of specific number of bars).
abstract final class UnitConverter {
  /// Converts weight in kilograms to metric tons.
  static double kgToTon(double kg) => kg / 1000.0;

  /// Converts metric tons to kilograms.
  static double tonToKg(double ton) => ton * 1000.0;

  /// Calculates total weight of rebar bars.
  ///
  /// Uses the formula: weight = (d² / 162) * length * count
  /// where d is diameter in mm and length is in meters.
  static double rebarWeight({
    required double diameterMm,
    required double lengthM,
    required int count,
  }) {
    final weightPerBar = (diameterMm * diameterMm / 162.0) * lengthM;
    return weightPerBar * count;
  }

  /// Calculates rebar bars per weight ton for a given diameter and length.
  static int barsPerWeightTon({
    required double diameterMm,
    required double lengthM,
  }) {
    final weightPerBar = (diameterMm * diameterMm / 162.0) * lengthM;
    if (weightPerBar <= 0) return 0;
    return (1000.0 / weightPerBar).floor();
  }

  /// Calculates total price breakdown.
  ///
  /// Returns a record of (materialCost, transportFee, offloadingFee, total).
  static ({double materialCost, double transportFee, double offloadingFee, double total})
      priceBreakdown({
    required double basePricePerTon,
    required double weightTons,
    required double transportFeePerTon,
    required double offloadingFeePerTon,
  }) {
    final materialCost = basePricePerTon * weightTons;
    final transportFee = transportFeePerTon * weightTons;
    final offloadingFee = offloadingFeePerTon * weightTons;
    final total = materialCost + transportFee + offloadingFee;
    return (
      materialCost: materialCost,
      transportFee: transportFee,
      offloadingFee: offloadingFee,
      total: total,
    );
  }
}
