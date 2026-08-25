import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/features/buyer/cart/application/cart_provider.dart';
import 'package:magaiees_marketplace/features/chat/application/chat_provider.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

class ProductDetailScreen extends ConsumerStatefulWidget {
  final String productId;
  const ProductDetailScreen({super.key, required this.productId});

  @override
  ConsumerState<ProductDetailScreen> createState() => _ProductDetailScreenState();
}

class _ProductDetailScreenState extends ConsumerState<ProductDetailScreen> {
  int quantity = 1;

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final productsAsync = ref.watch(productsProvider);

    return Scaffold(
      appBar: AppBar(
        title: Text(isRtl ? 'تفاصيل المنتج' : 'Product Details'),
      ),
      body: productsAsync.when(
        data: (products) {
          final productIndex = products.indexWhere(
            (p) => p.id == widget.productId,
          );
          
          if (productIndex < 0) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.error_outline, size: 64, color: Colors.grey),
                  const SizedBox(height: 16),
                  Text(isRtl ? 'المنتج غير موجود' : 'Product not found'),
                ],
              ),
            );
          }
          
          final product = products[productIndex];
          
          return SingleChildScrollView(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    height: 200,
                    width: 200,
                    decoration: BoxDecoration(
                      color: Colors.grey.shade200,
                      borderRadius: BorderRadius.circular(16),
                      image: product.imageUrl != null
                          ? DecorationImage(
                        image: NetworkImage(
                                product.imageUrl!.contains('unsplash')
                                    ? 'https://picsum.photos/seed/${product.id}/600/600'
                                    : product.imageUrl!,
                              ),
                              fit: BoxFit.cover,
                            )
                          : null,
                    ),
                    child: product.imageUrl == null
                        ? const Icon(Icons.image, size: 100, color: Colors.grey)
                        : null,
                  ),
                ),
                const SizedBox(height: 24),
                Text(
                  isRtl ? product.nameAr : product.name,
                  style: Theme.of(context).textTheme.headlineMedium?.copyWith(fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Icon(Icons.store, color: Theme.of(context).colorScheme.primary),
                    const SizedBox(width: 8),
                    Text(
                      '${isRtl ? 'المتجر:' : 'Store:'} ${isRtl ? product.storeNameAr : product.storeName}',
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    Icon(Icons.category, color: Theme.of(context).colorScheme.primary),
                    const SizedBox(width: 8),
                    Text(
                      '${isRtl ? 'الفئة:' : 'Category:'} ${isRtl ? product.categoryNameAr : product.categoryName}',
                      style: Theme.of(context).textTheme.bodyLarge,
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Theme.of(context).colorScheme.primaryContainer,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    isRtl
                        ? '${product.pricePerTon} ج.س / طن'
                        : '${product.pricePerTon} SDG / Ton',
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                          color: Theme.of(context).colorScheme.onPrimaryContainer,
                          fontWeight: FontWeight.bold,
                        ),
                  ),
                ),
                const SizedBox(height: 24),
                Row(
                  children: [
                    Text(isRtl ? 'الكمية:' : 'Quantity:', style: Theme.of(context).textTheme.titleLarge),
                    const Spacer(),
                    Container(
                      decoration: BoxDecoration(
                        border: Border.all(color: Colors.grey.shade300),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Row(
                        children: [
                          IconButton(
                            onPressed: () {
                              if (quantity > 1) {
                                setState(() => quantity--);
                              }
                            },
                            icon: const Icon(Icons.remove),
                          ),
                          Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 16.0),
                            child: Text('$quantity', style: Theme.of(context).textTheme.titleLarge),
                          ),
                          IconButton(
                            onPressed: () {
                              setState(() => quantity++);
                            },
                            icon: const Icon(Icons.add),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 32),
                SizedBox(
                  width: double.infinity,
                  child: FilledButton.icon(
                    onPressed: () {
                      ref.read(cartNotifierProvider.notifier).addProduct(product, quantity.toDouble());
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text(isRtl ? 'تمت الإضافة إلى السلة' : 'Added to cart'),
                          behavior: SnackBarBehavior.floating,
                        ),
                      );
                    },
                    icon: const Icon(Icons.shopping_cart),
                    label: Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: Text(isRtl ? 'أضف إلى السلة' : 'Add to Cart', style: const TextStyle(fontSize: 18)),
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                // Chat with Seller button
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    onPressed: () async {
                      final user = ref.read(authNotifierProvider).valueOrNull;
                      if (user == null) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(isRtl ? 'يرجى تسجيل الدخول أولاً' : 'Please login first'),
                          ),
                        );
                        return;
                      }

                      // Find the merchant (store owner) for this product
                      String? merchantId;
                      String? storeId;
                      try {
                        final storeRes = await Supabase.instance.client
                            .from('stores')
                            .select('id, owner_id')
                            .eq('id', product.merchantId)
                            .maybeSingle();
                        
                        if (storeRes != null) {
                          merchantId = storeRes['owner_id']?.toString();
                          storeId = storeRes['id']?.toString();
                        } else {
                          // Try finding by product's store_id directly
                          final prodRes = await Supabase.instance.client
                              .from('products')
                              .select('store_id')
                              .eq('id', product.id)
                              .maybeSingle();
                          if (prodRes != null && prodRes['store_id'] != null) {
                            storeId = prodRes['store_id'].toString();
                            final ownerRes = await Supabase.instance.client
                                .from('stores')
                                .select('owner_id')
                                .eq('id', storeId!)
                                .maybeSingle();
                            merchantId = ownerRes?['owner_id']?.toString();
                          }
                        }
                      } catch (_) {}

                      if (merchantId == null) {
                        if (context.mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(isRtl ? 'لا يمكن العثور على التاجر' : 'Could not find seller'),
                            ),
                          );
                        }
                        return;
                      }

                      if (merchantId == user.id) {
                        if (context.mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(isRtl ? 'هذا منتجك الخاص!' : 'This is your own product!'),
                            ),
                          );
                        }
                        return;
                      }

                      // Create or get existing chat room
                      final roomId = await ChatService.getOrCreateRoom(
                        buyerId: user.id,
                        merchantId: merchantId,
                        storeId: storeId,
                      );

                      if (context.mounted) {
                        context.go('/buyer/chat/room/$roomId');
                      }
                    },
                    icon: const Icon(Icons.chat_outlined),
                    label: Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: Text(
                        isRtl ? 'تواصل مع التاجر' : 'Chat with Seller',
                        style: const TextStyle(fontSize: 18),
                      ),
                    ),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: const Color(0xFF1E40AF),
                      side: const BorderSide(color: Color(0xFF1E40AF), width: 1.5),
                    ),
                  ),
                ),
              ],
            ),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, stack) => Center(child: Text(isRtl ? 'خطأ في تحميل المنتج' : 'Error loading product')),
      ),
    );
  }
}
