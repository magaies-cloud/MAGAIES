import 'dart:async';
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
