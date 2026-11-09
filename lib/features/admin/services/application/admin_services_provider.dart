import 'dart:typed_data';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/buyer/services/domain/models/service_provider_model.dart';
import 'package:magaiees_marketplace/core/network/supabase_safe_query.dart';

/// State of admin services
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

/// Aggregated accounting, operational, and advertiser insights for services
class AdminServicesInsights {
  final int totalProvidersCount;
  final int engineeringCount;
  final int logisticsCount;
  final int techniciansCount;
  final int laborCount;
  final int verifiedCount;
  final int totalReviewsCount;
  final double averageRating;
  final double totalLogisticsFees;
  final int logisticsOrdersCount;
  final double totalContractValue;
  final int sponsoredCount;
  final int pendingApprovalCount;
  final ServiceProviderModel? topRatedProvider;

  const AdminServicesInsights({
    this.totalProvidersCount = 0,
    this.engineeringCount = 0,
    this.logisticsCount = 0,
    this.techniciansCount = 0,
    this.laborCount = 0,
    this.verifiedCount = 0,
    this.totalReviewsCount = 0,
    this.averageRating = 0.0,
    this.totalLogisticsFees = 0.0,
    this.logisticsOrdersCount = 0,
    this.totalContractValue = 0.0,
    this.sponsoredCount = 0,
    this.pendingApprovalCount = 0,
    this.topRatedProvider,
  });
}

class AdminServicesNotifier extends StateNotifier<AdminServicesState> {
  AdminServicesNotifier() : super(AdminServicesState()) {
    loadProviders();
  }

  final _supabase = Supabase.instance.client;

  Future<void> loadProviders() async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final response = await safeSupabaseQuery(
        () => _supabase.from('service_providers').select().order('created_at', ascending: false),
        fallback: () => <dynamic>[],
      );
      final List<dynamic> rawList = response;
      final providers = rawList.map((json) => ServiceProviderModel.fromJson(json as Map<String, dynamic>)).toList();
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
      
      if (providerJson['id'] == null || providerJson['id'].toString().isEmpty) {
        providerJson.remove('id');
      }
      
      await safeSupabaseQuery(() => _supabase.from('service_providers').insert(providerJson));
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
      
      await safeSupabaseQuery(() => _supabase.from('service_providers').update(providerJson).eq('id', id));
      await loadProviders();
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      rethrow;
    }
  }

  Future<void> approveProvider(String id, {String contractType = 'none', double contractValue = 0.0}) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      await safeSupabaseQuery(() => _supabase.from('service_providers').update({
        'is_verified': true,
        'contract_type': contractType,
        'contract_value': contractValue,
      }).eq('id', id));
      await loadProviders();
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      rethrow;
    }
  }

  Future<void> deleteProvider(dynamic id) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      await safeSupabaseQuery(() => _supabase.from('service_providers').delete().eq('id', id));
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

/// Riverpod provider computing full logistics and service accounting metrics
final adminServicesInsightsProvider = FutureProvider.autoDispose<AdminServicesInsights>((ref) async {
  final servicesState = ref.watch(adminServicesProvider);
  final providers = servicesState.providers;
  final client = Supabase.instance.client;

  final ordersRes = await safeSupabaseQuery(
    () => client.from('orders').select('id, transport_fee, offloading_fee, status'),
    fallback: () => <dynamic>[],
  );

  final List<dynamic> ordersRaw = ordersRes;
  double totalTransport = 0.0;
  double totalOffloading = 0.0;
  int logisticsOrdersCount = 0;

  for (var o in ordersRaw) {
    final t = (o['transport_fee'] as num?)?.toDouble() ?? 0.0;
    final of = (o['offloading_fee'] as num?)?.toDouble() ?? 0.0;
    if (t > 0 || of > 0) logisticsOrdersCount++;
    totalTransport += t;
    totalOffloading += of;
  }

  int eng = 0;
  int log = 0;
  int tech = 0;
  int lab = 0;
  int verified = 0;
  int reviews = 0;
  double sumRating = 0.0;
  double totalContracts = 0.0;
  int sponsored = 0;
  int pendingApproval = 0;

  ServiceProviderModel? topRated;
  double maxRating = 0.0;

  for (var p in providers) {
    switch (p.categoryId) {
      case 'engineering':
        eng++;
        break;
      case 'logistics':
        log++;
        break;
      case 'technicians':
        tech++;
        break;
      case 'labor':
        lab++;
        break;
    }
    if (p.isVerified) {
      verified++;
    } else {
      pendingApproval++;
    }
    if (p.contractType == 'pending' && p.isVerified) {
      pendingApproval++;
    }
    reviews += p.reviewsCount;
    sumRating += p.rating;
    totalContracts += p.contractValue;

    if (p.rating >= 4.9 || p.categoryId == 'logistics' || p.contractType != 'none') {
      sponsored++;
    }

    if (p.rating > maxRating) {
      maxRating = p.rating;
      topRated = p;
    }
  }

  return AdminServicesInsights(
    totalProvidersCount: providers.length,
    engineeringCount: eng,
    logisticsCount: log,
    techniciansCount: tech,
    laborCount: lab,
    verifiedCount: verified,
    totalReviewsCount: reviews,
    averageRating: providers.isNotEmpty ? (sumRating / providers.length) : 0.0,
    totalLogisticsFees: totalTransport + totalOffloading,
    logisticsOrdersCount: logisticsOrdersCount,
    totalContractValue: totalContracts,
    sponsoredCount: sponsored,
    pendingApprovalCount: pendingApproval,
    topRatedProvider: topRated,
  );
});
