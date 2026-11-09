import 'dart:async';
import 'dart:typed_data';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/buyer/services/domain/models/service_provider_model.dart';

class ServicesNotifier extends StateNotifier<AsyncValue<List<ServiceProviderModel>>> {
  final _supabase = Supabase.instance.client;
  RealtimeChannel? _channel;

  ServicesNotifier() : super(const AsyncValue.loading()) {
    _loadProviders();
    _subscribeToChanges();
  }

  Future<void> _loadProviders() async {
    try {
      final res = await _supabase.from('service_providers').select().order('created_at', ascending: false);
      final List<dynamic> data = res;
      final providers = data.map((json) => ServiceProviderModel.fromJson(json)).toList();
      state = AsyncValue.data(providers);
    } catch (e, stack) {
      state = AsyncValue.error(e, stack);
    }
  }

  Future<void> submitProviderApplication({
    required String nameAr,
    required String tradeAr,
    required String categoryId,
    required String phone,
    required String locationAr,
    required String experienceYears,
    required String priceEstimate,
    Uint8List? avatarBytes,
    String? avatarExtension,
  }) async {
    String? avatarUrl;
    if (avatarBytes != null && avatarExtension != null) {
      try {
        final storage = _supabase.storage.from('provider_avatars');
        final path = 'avatars/applicant_${DateTime.now().millisecondsSinceEpoch}.$avatarExtension';
        await storage.uploadBinary(path, avatarBytes);
        avatarUrl = storage.getPublicUrl(path);
      } catch (_) {
        // Continue if avatar upload fails
      }
    }

    final newRecord = {
      'name': nameAr,
      'name_ar': nameAr,
      'trade': tradeAr,
      'trade_ar': tradeAr,
      'category_id': categoryId,
      'phone': phone,
      'location': locationAr,
      'location_ar': locationAr,
      'experience_years': experienceYears,
      'price_estimate': priceEstimate,
      'is_verified': false,
      'avatar_url': avatarUrl,
      'contract_type': 'pending',
      'contract_value': 0.0,
      'rating': 5.0,
      'reviews_count': 0,
      'completed_orders_count': 0,
    };

    await _supabase.from('service_providers').insert(newRecord);
    await _loadProviders();
  }

  void _subscribeToChanges() {
    _channel = _supabase
        .channel('public:service_providers')
        .onPostgresChanges(
          event: PostgresChangeEvent.all,
          schema: 'public',
          table: 'service_providers',
          callback: (payload) {
            _loadProviders();
          },
        )
        .subscribe();
  }

  @override
  void dispose() {
    _channel?.unsubscribe();
    super.dispose();
  }
}

final servicesProvider = StateNotifierProvider<ServicesNotifier, AsyncValue<List<ServiceProviderModel>>>((ref) {
  return ServicesNotifier();
});
