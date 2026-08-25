import 'package:flutter/material.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/product_card_model.dart';
import 'package:magaiees_marketplace/features/buyer/wishlist/application/wishlist_provider.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' hide TextDirection;

class ProductCard extends StatelessWidget {
  final ProductCardModel product;
  final VoidCallback? onTap;
  final VoidCallback? onAddToCart;
  final bool isSponsored;

  const ProductCard({
    super.key,
    required this.product,
    this.onTap,
    this.onAddToCart,
    this.isSponsored = false,
  });

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final format = NumberFormat.decimalPattern();
    final priceStr = format.format(product.pricePerTon);

    final displayName = isRtl
        ? (product.nameAr.isNotEmpty ? product.nameAr : product.name)
        : (product.name.isNotEmpty ? product.name : product.nameAr);

    final displayStore = isRtl
        ? (product.storeNameAr.isNotEmpty ? product.storeNameAr : product.storeName)
        : (product.storeName.isNotEmpty ? product.storeName : product.storeNameAr);

    final currencyUnit = isRtl ? 'ج.س/طن' : 'SDG/ton';
    final sellerLabel = isRtl ? 'البائع:' : 'Seller:';
    final stockLabel = product.inStock 
        ? (isRtl ? 'متوفر' : 'In Stock') 
        : (isRtl ? 'غير متوفر' : 'Out of Stock');
    final sponsoredLabel = isRtl ? 'ممول' : 'Sponsored';

    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Image Placeholder
            Expanded(
              flex: 3,
              child: Stack(
                fit: StackFit.expand,
                children: [
                  Container(
                    color: Colors.grey.shade200,
                    child: product.imageUrl != null
                        ? Image.network(
                            product.imageUrl!.contains('unsplash') ? 'https://picsum.photos/seed/${product.id}/600/600' : product.imageUrl!,
                            fit: BoxFit.cover,
                            errorBuilder: (context, error, stackTrace) => const Icon(
                              Icons.inventory_2_outlined,
                              size: 48,
                              color: Colors.grey,
                            ),
                          )
                        : const Icon(Icons.inventory_2_outlined, size: 48, color: Colors.grey),
                  ),
                  if (isSponsored)
                    Positioned(
                      top: 6,
                      left: isRtl ? null : 6,
                      right: isRtl ? 6 : null,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: Colors.amber.shade700,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          sponsoredLabel,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ),
                  if (product.isMagaieesExclusive)
                    Positioned(
                      top: 6,
                      right: isRtl ? null : 6,
                      left: isRtl ? 6 : null,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: const Color(0xFF1E40AF),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          isRtl ? '⭐ مقاييس' : '🌟 Magaiees',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ),
                  Consumer(
                    builder: (context, ref, child) {
                      final wishlist = ref.watch(wishlistProvider).valueOrNull ?? {};
                      final isSaved = wishlist.contains(product.id);
                      return Positioned(
                        bottom: 4,
                        left: isRtl ? 4 : null,
                        right: isRtl ? null : 4,
                        child: CircleAvatar(
                          backgroundColor: Colors.white.withOpacity(0.9),
                          radius: 16,
                          child: IconButton(
                            padding: EdgeInsets.zero,
                            iconSize: 18,
                            icon: Icon(
                              isSaved ? Icons.favorite : Icons.favorite_border,
                              color: isSaved ? Colors.red : Colors.grey.shade600,
                            ),
                            onPressed: () {
                              ref.read(wishlistProvider.notifier).toggleWishlist(product.id);
                            },
                          ),
                        ),
                      );
                    },
                  ),
                ],
              ),
            ),
            // Details
            Expanded(
              flex: 4,
              child: Padding(
                padding: const EdgeInsets.all(8.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      displayName,
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '$priceStr $currencyUnit',
                      style: TextStyle(
                        fontWeight: FontWeight.w900,
                        fontSize: 15,
                        color: Theme.of(context).primaryColor,
                      ),
                    ),
                    if (product.priceInUsd != null && product.priceInUsd! > 0)
                      Text(
                        '\$${product.priceInUsd!.toStringAsFixed(2)} / ${isRtl ? 'طن' : 'ton'}',
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey.shade700,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    const SizedBox(height: 4),
                    Text(
                      '$sellerLabel $displayStore',
                      style: const TextStyle(fontSize: 12, color: Colors.grey),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const Spacer(),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            Container(
                              width: 8,
                              height: 8,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                color: product.inStock ? Colors.green : Colors.red,
                              ),
                            ),
                            const SizedBox(width: 4),
                            Text(
                              stockLabel,
                              style: const TextStyle(fontSize: 12),
                            ),
                          ],
                        ),
                        IconButton(
                          icon: const Icon(Icons.add_shopping_cart, size: 20),
                          onPressed: onAddToCart,
                          padding: EdgeInsets.zero,
                          constraints: const BoxConstraints(),
                          color: Theme.of(context).primaryColor,
                        )
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
