import 'package:riverpod_annotation/riverpod_annotation.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/core/network/supabase_safe_query.dart';

part 'ads_provider.g.dart';

class AdModel {
  final String id;
  final String title;
  final String company;
  final String target;
  final bool isActive;
  final String imageUrl;
  final String startDate;
  final String endDate;
  final double price;

  AdModel({
    required this.id,
    required this.title,
    required this.company,
    required this.target,
    this.isActive = true,
    required this.imageUrl,
    required this.startDate,
    required this.endDate,
    this.price = 0.0,
  });

  AdModel copyWith({
    String? id,
    String? title,
    String? company,
    String? target,
    bool? isActive,
    String? imageUrl,
    String? startDate,
    String? endDate,
    double? price,
  }) {
    return AdModel(
      id: id ?? this.id,
      title: title ?? this.title,
      company: company ?? this.company,
      target: target ?? this.target,
      isActive: isActive ?? this.isActive,
      imageUrl: imageUrl ?? this.imageUrl,
      startDate: startDate ?? this.startDate,
      endDate: endDate ?? this.endDate,
      price: price ?? this.price,
    );
  }

  factory AdModel.fromJson(Map<String, dynamic> json) {
    return AdModel(
      id: json['id']?.toString() ?? '',
      title: json['title'] ?? '',
      company: json['company'] ?? '',
      target: json['target_tag'] ?? '',
      isActive: json['is_active'] ?? true,
      imageUrl: json['image_url'] ?? '',
      startDate: json['start_date'] != null ? json['start_date'].toString().split(' ')[0] : '',
      endDate: json['end_date'] != null ? json['end_date'].toString().split(' ')[0] : '',
      price: double.tryParse(json['price']?.toString() ?? '') ?? 0.0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      if (!id.startsWith('ad_')) 'id': id,
      'title': title,
      'company': company,
      'target_tag': target,
      'is_active': isActive,
      'image_url': imageUrl,
      'start_date': startDate,
      'end_date': endDate,
      'price': price,
    };
  }
}

@Riverpod(keepAlive: true)
class Ads extends _$Ads {
  final _supabase = Supabase.instance.client;

  @override
  FutureOr<List<AdModel>> build() async {
    // Setup Realtime listener for instant ad updates
    final channel = _supabase
        .channel('admin:ads')
        .onPostgresChanges(
          event: PostgresChangeEvent.all,
          schema: 'public',
          table: 'ads',
          callback: (payload) {
            ref.invalidateSelf();
          },
        )
        .subscribe();

    ref.onDispose(() {
      channel.unsubscribe();
    });

    final data = await safeSupabaseQuery(
      () => _supabase.from('ads').select().order('created_at', ascending: false),
      fallback: () => <Map<String, dynamic>>[],
    );
    return (data as List).map((e) => AdModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<void> addAd(AdModel ad) async {
    final previousState = state;
    state = const AsyncLoading();
    try {
      await safeSupabaseQuery(() => _supabase.from('ads').insert(ad.toJson()));
      ref.invalidateSelf();
    } catch (e) {
      state = previousState;
      rethrow;
    }
  }

  Future<void> updateAd(AdModel updatedAd) async {
    final previousState = state;
    try {
      if (state.value != null) {
        state = AsyncData(state.value!.map((ad) => ad.id == updatedAd.id ? updatedAd : ad).toList());
      }
      await safeSupabaseQuery(() => _supabase.from('ads').update(updatedAd.toJson()).eq('id', updatedAd.id));
      ref.invalidateSelf();
    } catch (e) {
      state = previousState;
      rethrow;
    }
  }

  Future<void> toggleStatus(String id) async {
    final previousState = state;
    try {
      bool newStatus = true;
      if (state.value != null) {
        state = AsyncData(state.value!.map((ad) {
          if (ad.id == id) {
            newStatus = !ad.isActive;
            return ad.copyWith(isActive: newStatus);
          }
          return ad;
        }).toList());
      }
      await safeSupabaseQuery(() => _supabase.from('ads').update({'is_active': newStatus}).eq('id', id));
      ref.invalidateSelf();
    } catch (e) {
      state = previousState;
      rethrow;
    }
  }

  Future<void> deleteAd(String id) async {
    final previousState = state;
    try {
      if (state.value != null) {
        state = AsyncData(state.value!.where((ad) => ad.id != id).toList());
      }
      await safeSupabaseQuery(() => _supabase.from('ads').delete().eq('id', id));
      ref.invalidateSelf();
    } catch (e) {
      state = previousState;
      rethrow;
    }
  }
}
