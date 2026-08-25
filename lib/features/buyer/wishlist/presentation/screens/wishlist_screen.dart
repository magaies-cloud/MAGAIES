import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/product_card_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/presentation/widgets/product_card.dart';
import 'package:magaiees_marketplace/features/buyer/wishlist/application/wishlist_provider.dart';

final wishlistProductsProvider = FutureProvider.autoDispose<List<ProductCardModel>>((ref) async {
  final ids = ref.watch(wishlistProvider).valueOrNull ?? {};
  if (ids.isEmpty) return [];

  final res = await Supabase.instance.client
      .from('products')
      .select('*, stores(name, name_ar)')
      .inFilter('id', ids.toList());

  return List<dynamic>.from(res).map((json) {
    final store = json['stores'] as Map<String, dynamic>?;
    final storeName = store?['name'] ?? 'Magaiees';
    final storeNameAr = store?['name_ar'] ?? 'مقاييس';

    return ProductCardModel(
      id: json['id'] ?? '',
      categoryId: json['category_id'] ?? '',
      categoryName: 'General',
      categoryNameAr: 'عام',
      name: json['name'] ?? '',
      nameAr: json['name_ar'] ?? '',
      imageUrl: (json['images'] != null && (json['images'] as List).isNotEmpty)
          ? json['images'][0]
          : 'https://picsum.photos/seed/${json['id']}/400/400',
      pricePerTon: (json['price_sdg'] as num?)?.toDouble() ?? 0.0,
      priceInUsd: (json['price_usd'] as num?)?.toDouble(),
      unit: 'ton',
      unitAr: 'طن',
      inStock: (json['is_active'] ?? true) && (((json['stock'] as num?) ?? 0) > 0),
      merchantId: json['store_id'] ?? 'magaiees',
      storeName: storeName,
      storeNameAr: storeNameAr,
      isMagaieesExclusive: json['is_magaiees_exclusive'] ?? false,
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
                childAspectRatio: 0.65,
                crossAxisSpacing: 16,
                mainAxisSpacing: 16,
              ),
              itemCount: products.length,
              itemBuilder: (context, index) {
                final product = products[index];
                return Stack(
                  children: [
                    Positioned.fill(
                      child: ProductCard(
                        product: product,
                        onTap: () {
                          // Add navigation logic if needed
                        },
                        onAddToCart: () {
                          // Add to cart logic if needed
                        },
                      ),
                    ),
                    Positioned(
                      top: 4,
                      left: 4,
                      child: CircleAvatar(
                        backgroundColor: Colors.white.withOpacity(0.9),
                        radius: 18,
                        child: IconButton(
                          padding: EdgeInsets.zero,
                          icon: const Icon(
                            Icons.favorite,
                            color: Colors.red,
                            size: 20,
                          ),
                          onPressed: () {
                            ref.read(wishlistProvider.notifier).toggleWishlist(product.id);
                          },
                        ),
                      ),
                    ),
                  ],
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
