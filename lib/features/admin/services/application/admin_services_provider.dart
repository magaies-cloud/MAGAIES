import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/buyer/services/domain/models/service_provider_model.dart';
import 'dart:typed_data';

class AdminServicesState {
  final bool isLoading;
  final List<ServiceProviderModel> providers;
  final String? error;

  AdminServicesState({
    this.isLoading = false,
    this.providers = const [],
    this.error,
  });

  AdminServicesState copyWith({
    bool? isLoading,
    List<ServiceProviderModel>? providers,
    String? error,
  }) {
    return AdminServicesState(
      isLoading: isLoading ?? this.isLoading,
      providers: providers ?? this.providers,
      error: error ?? this.error,
    );
  }
}

class AdminServicesNotifier extends StateNotifier<AdminServicesState> {
  AdminServicesNotifier() : super(AdminServicesState()) {
    loadProviders();
  }

  final _supabase = Supabase.instance.client;

  Future<void> loadProviders() async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final response = await _supabase.from('service_providers').select().order('created_at', ascending: false);
      final providers = (response as List).map((json) => ServiceProviderModel.fromJson(json)).toList();
      state = state.copyWith(isLoading: false, providers: providers);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  Future<String?> uploadAvatar(Uint8List fileBytes, String extension) async {
    try {
      final storage = _supabase.storage.from('provider_avatars');
      final path = 'avatars/avatar_${DateTime.now().millisecondsSinceEpoch}.$extension';
      await storage.uploadBinary(path, fileBytes);
      return storage.getPublicUrl(path);
    } catch (e) {
      throw Exception('Failed to upload image: $e');
    }
  }

  Future<void> addProvider(ServiceProviderModel provider, {Uint8List? avatarBytes, String? avatarExtension}) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final Map<String, dynamic> providerJson = provider.toJson();
      
      if (avatarBytes != null && avatarExtension != null) {
          final avatarUrl = await uploadAvatar(avatarBytes, avatarExtension);
          providerJson['avatar_url'] = avatarUrl;
        }
      
      if (providerJson['id'] == null) {
        providerJson.remove('id');
      }
      
      await _supabase.from('service_providers').insert(providerJson);
      await loadProviders();
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      rethrow;
    }
  }

  Future<void> updateProvider(ServiceProviderModel provider, {Uint8List? avatarBytes, String? avatarExtension}) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final Map<String, dynamic> providerJson = provider.toJson();
      
      if (avatarBytes != null && avatarExtension != null) {
          final avatarUrl = await uploadAvatar(avatarBytes, avatarExtension);
          providerJson['avatar_url'] = avatarUrl;
        }
      
      final id = providerJson['id'];
      if (id == null) throw Exception('Provider ID is missing');
      
      await _supabase.from('service_providers').update(providerJson).eq('id', id);
      await loadProviders();
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      rethrow;
    }
  }

  Future<void> deleteProvider(dynamic id) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      await _supabase.from('service_providers').delete().eq('id', id);
      await loadProviders();
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      rethrow;
    }
  }
}

final adminServicesProvider = StateNotifierProvider<AdminServicesNotifier, AdminServicesState>((ref) {
  return AdminServicesNotifier();
});
