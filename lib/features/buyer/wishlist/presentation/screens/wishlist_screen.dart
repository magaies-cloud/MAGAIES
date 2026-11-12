import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/buyer/cart/application/cart_provider.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/product_card_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/presentation/widgets/product_card.dart';
import 'package:magaiees_marketplace/features/buyer/wishlist/application/wishlist_provider.dart';

import 'package:magaiees_marketplace/core/providers/global_settings_provider.dart';
import 'package:magaiees_marketplace/core/utils/app_notification.dart';
import 'package:magaiees_marketplace/core/utils/unit_formatter.dart';

final wishlistProductsProvider = FutureProvider.autoDispose<List<ProductCardModel>>((ref) async {
  final ids = ref.watch(wishlistProvider).valueOrNull ?? {};
  if (ids.isEmpty) return [];

  final res = await Supabase.instance.client
      .from('products')
      .select('*, stores(name, name_ar, usd_exchange_rate)')
      .inFilter('id', ids.toList());

  final platformRate = ref.read(globalSettingsProvider).platformUsdRate;

  return List<dynamic>.from(res).map((rawJson) {
    final json = rawJson is Map ? Map<String, dynamic>.from(rawJson) : <String, dynamic>{};
    final store = json['stores'] is Map ? Map<String, dynamic>.from(json['stores'] as Map) : null;
    final storeName = store?['name'] ?? 'Magaiees';
    final storeNameAr = store?['name_ar'] ?? 'مقاييس';
    final storeRate = (store?['usd_exchange_rate'] as num?)?.toDouble();
    final bool isExclusive = json['is_magaiees_exclusive'] ?? false;
    final double effectiveRate = (!isExclusive && storeRate != null && storeRate > 0)
        ? storeRate
        : platformRate;

    double sdg = (json['price_sdg'] as num?)?.toDouble() ?? 0.0;
    final double? rawUsd = (json['price_usd'] as num?)?.toDouble();
    if (sdg <= 0 && rawUsd != null && rawUsd > 0 && effectiveRate > 0) {
      sdg = rawUsd * effectiveRate;
    }
    final double calculatedUsd;
    if (sdg > 0 && effectiveRate > 0) {
      calculatedUsd = sdg / effectiveRate;
    } else if (rawUsd != null && rawUsd > 0) {
      calculatedUsd = rawUsd;
    } else {
      calculatedUsd = 0.0;
    }

    final String cleanUnit = UnitFormatter.clean(json['unit']?.toString() ?? 'ton');
    final String cleanUnitAr = UnitFormatter.clean(json['unit_ar']?.toString() ?? json['unit']?.toString() ?? 'طن');

    return ProductCardModel(
      id: json['id'] ?? '',
      categoryId: json['category_id'] ?? '',
      categoryName: 'General',
      categoryNameAr: 'عام',
      name: json['name'] ?? '',
      nameAr: json['name_ar'] ?? '',
      imageUrl: (json['images'] != null && (json['images'] as List).isNotEmpty)
          ? json['images'][0]
          : (json['image_url'] != null ? json['image_url'].toString() : null),
      pricePerTon: sdg,
      priceInUsd: calculatedUsd,
      unit: cleanUnit.isNotEmpty ? cleanUnit : 'ton',
      unitAr: cleanUnitAr.isNotEmpty ? cleanUnitAr : 'طن',
      inStock: (json['is_active'] ?? true) && (((json['stock'] as num?) ?? 0) > 0),
      merchantId: json['store_id'] ?? 'magaiees',
      storeName: storeName,
      storeNameAr: storeNameAr,
      isMagaieesExclusive: isExclusive,
      storeUsdRate: effectiveRate,
    );
  }).toList();
});

class WishlistScreen extends ConsumerWidget {
  const WishlistScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final wishlistAsync = ref.watch(wishlistProductsProvider);

    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        appBar: AppBar(
          title: const Text(
            'المفضلة',
            style: TextStyle(fontWeight: FontWeight.bold),
          ),
          centerTitle: true,
        ),
        body: wishlistAsync.when(
          data: (products) {
            if (products.isEmpty) {
              return Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      Icons.favorite_border,
                      size: 80,
                      color: Colors.grey.shade400,
                    ),
                    const SizedBox(height: 16),
                    Text(
                      'لا توجد منتجات في المفضلة',
                      style: TextStyle(
                        fontSize: 18,
                        color: Colors.grey.shade600,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              );
            }

            return GridView.builder(
              padding: const EdgeInsets.all(16),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2,
                childAspectRatio: 0.63,
                crossAxisSpacing: 12,
                mainAxisSpacing: 12,
              ),
              itemCount: products.length,
              itemBuilder: (context, index) {
                final product = products[index];
                return ProductCard(
                  product: product,
                  onTap: () => context.push('/buyer/home/product/${product.id}'),
                  onAddToCart: () {
                    ref.read(cartNotifierProvider.notifier).addProduct(product, 1);
                    AppNotification.showSuccess(
                      context,
                      'تمت الإضافة إلى السلة',
                      icon: Icons.shopping_bag_outlined,
                    );
                  },
                );
              },
            );
          },
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (err, st) => Center(
            child: Text('حدث خطأ: $err'),
          ),
        ),
      ),
    );
  }
}
