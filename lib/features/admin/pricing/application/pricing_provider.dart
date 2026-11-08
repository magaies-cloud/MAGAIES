import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

/// State representation for admin pricing and currency exchange rate.
class PricingState {
  final double usdExchangeRate;
  final double lastAppliedPercentage;

  const PricingState({
    this.usdExchangeRate = 6000.0,
    this.lastAppliedPercentage = 0.0,
  });

  PricingState copyWith({
    double? usdExchangeRate,
    double? lastAppliedPercentage,
  }) {
    return PricingState(
      usdExchangeRate: usdExchangeRate ?? this.usdExchangeRate,
      lastAppliedPercentage:
          lastAppliedPercentage ?? this.lastAppliedPercentage,
    );
  }

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is PricingState &&
        other.usdExchangeRate == usdExchangeRate &&
        other.lastAppliedPercentage == lastAppliedPercentage;
  }

  @override
  int get hashCode => Object.hash(usdExchangeRate, lastAppliedPercentage);

  @override
  String toString() =>
      'PricingState(usdExchangeRate: $usdExchangeRate, lastAppliedPercentage: $lastAppliedPercentage)';
}

/// State notifier to manage USD exchange rate and percentage pricing changes.
class PricingNotifier extends StateNotifier<PricingState> {
  PricingNotifier() : super(const PricingState()) {
    _loadFromDb();
  }

  final _supabase = Supabase.instance.client;

  Future<void> _loadFromDb() async {
    try {
      final res = await _supabase
          .from('users')
          .select('permissions')
          .eq('role', 'admin')
          .limit(1)
          .maybeSingle();
      if (res != null && res['permissions'] != null && res['permissions']['usd_exchange_rate'] != null) {
        state = state.copyWith(
          usdExchangeRate: double.tryParse(res['permissions']['usd_exchange_rate']?.toString() ?? '') ?? 2800.0,
        );
      }
    } catch (_) {
      // Use default rate if table/column doesn't exist
    }
  }

  /// Updates the current USD to SDG exchange rate.
  void updateUsdRate(double newRate) {
    state = state.copyWith(usdExchangeRate: newRate);
    _saveToDb();
  }

  /// Alias for updateUsdRate
  void updateRate(double newRate) => updateUsdRate(newRate);

  Future<void> _saveToDb() async {
    try {
      final res = await _supabase.from('users').select('permissions').eq('role', 'admin').limit(1).maybeSingle();
      if (res != null) {
        final perms = (res['permissions'] as Map<String, dynamic>?) ?? {};
        perms['usd_exchange_rate'] = state.usdExchangeRate;
        await _supabase.from('users').update({'permissions': perms}).eq('role', 'admin');
      }
    } catch (_) {
      // Silently ignore
    }
  }

  /// Records and applies a percentage change to pricing.
  void applyPercentageIncrease(double percent) {
    state = state.copyWith(lastAppliedPercentage: percent);
  }

  /// Calculates SDG price given a price in USD.
  double calculateSdgFromUsd(double usdPrice) {
    return usdPrice * state.usdExchangeRate;
  }

  /// Resets exchange rate and percentage adjustments back to defaults.
  void reset() {
    state = const PricingState();
  }
}

/// Provider for managing admin pricing state across Magaiees app.
final pricingProvider =
    StateNotifierProvider<PricingNotifier, PricingState>((ref) {
  return PricingNotifier();
});
