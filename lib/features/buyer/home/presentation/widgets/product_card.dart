import 'package:cached_network_image/cached_network_image.dart';
import 'package:magaiees_marketplace/core/utils/image_optimizer.dart';
import 'package:magaiees_marketplace/core/utils/unit_formatter.dart';
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

    final unitName = UnitFormatter.format(product.unitAr, product.unit, isRtl: isRtl);
    final currencyUnit = isRtl ? 'ج.س / $unitName' : 'SDG / $unitName';
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
            // Image Area (Standard 4:3)
            AspectRatio(
              aspectRatio: 4 / 3,
              child: Stack(
                fit: StackFit.expand,
                children: [
                  Container(
                    color: Colors.white,
                    child: product.imageUrl != null && product.imageUrl!.isNotEmpty
                        ? CachedNetworkImage(
                            imageUrl: ImageOptimizer.optimize(product.imageUrl!, width: 400),
                            fit: BoxFit.cover,
                            memCacheWidth: 400,
                            memCacheHeight: 400,
                            placeholder: (context, url) => Container(
                              color: Colors.white,
                              child: Center(
                                child: SizedBox(
                                  width: 24,
                                  height: 24,
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2,
                                    valueColor: AlwaysStoppedAnimation<Color>(Colors.blue.shade300),
                                  ),
                                ),
                              ),
                            ),
                            errorWidget: (context, url, error) => const Center(
                              child: Icon(
                                Icons.inventory_2_outlined,
                                size: 44,
                                color: Color(0xFFCBD5E1),
                              ),
                            ),
                          )
                        : const Center(
                            child: Icon(
                              Icons.inventory_2_outlined,
                              size: 44,
                              color: Color(0xFFCBD5E1),
                            ),
                          ),
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
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                        decoration: BoxDecoration(
                          color: const Color(0xFF1E40AF),
                          borderRadius: BorderRadius.circular(6),
                          boxShadow: const [
                            BoxShadow(color: Colors.black26, blurRadius: 4, offset: Offset(0, 1)),
                          ],
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.verified_rounded, size: 11, color: Colors.amber),
                            const SizedBox(width: 3),
                            Text(
                              isRtl ? 'حصري مقاييس' : 'Magaiees Exclusive',
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 9.5,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                      ),
                    )
                  else if (displayStore.isNotEmpty && displayStore != 'Magaiees' && displayStore != 'مقاييس')
                    Positioned(
                      top: 6,
                      right: isRtl ? null : 6,
                      left: isRtl ? 6 : null,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                        decoration: BoxDecoration(
                          color: const Color(0xFF0F172A).withValues(alpha: 0.75),
                          borderRadius: BorderRadius.circular(6),
                          boxShadow: const [
                            BoxShadow(color: Colors.black26, blurRadius: 4, offset: Offset(0, 1)),
                          ],
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.storefront_rounded, size: 11, color: Color(0xFF38BDF8)),
                            const SizedBox(width: 3),
                            Text(
                              displayStore,
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 9.5,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
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
                          backgroundColor: Colors.white.withValues(alpha: 0.9),
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
            // Details Area (Compact, cohesive, no oversized gaps)
            Expanded(
              child: Padding(
                padding: const EdgeInsets.all(8.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          displayName,
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 13,
                            height: 1.25,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '$priceStr $currencyUnit',
                          style: TextStyle(
                            fontWeight: FontWeight.w900,
                            fontSize: 14,
                            color: Theme.of(context).primaryColor,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            Icon(
                              product.isMagaieesExclusive ? Icons.verified_rounded : Icons.storefront_outlined,
                              size: 13,
                              color: product.isMagaieesExclusive ? const Color(0xFF2563EB) : const Color(0xFF64748B),
                            ),
                            const SizedBox(width: 4),
                            Expanded(
                              child: Text(
                                product.isMagaieesExclusive
                                    ? (isRtl ? 'حصري ومباشر عبر مقاييس' : 'Magaiees Exclusive')
                                    : '${isRtl ? "متجر:" : "Store:"} $displayStore',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: product.isMagaieesExclusive ? FontWeight.bold : FontWeight.w500,
                                  color: product.isMagaieesExclusive ? const Color(0xFF1D4ED8) : const Color(0xFF64748B),
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Container(
                              width: 7,
                              height: 7,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                color: product.inStock ? const Color(0xFF16A34A) : const Color(0xFFDC2626),
                              ),
                            ),
                            const SizedBox(width: 4),
                            Text(
                              stockLabel,
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                                color: product.inStock ? const Color(0xFF15803D) : const Color(0xFFDC2626),
                              ),
                            ),
                          ],
                        ),
                        Material(
                          color: Theme.of(context).primaryColor.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(6),
                          child: InkWell(
                            onTap: onAddToCart,
                            borderRadius: BorderRadius.circular(6),
                            child: Padding(
                              padding: const EdgeInsets.all(5),
                              child: Icon(
                                Icons.add_shopping_cart_rounded,
                                size: 16,
                                color: Theme.of(context).primaryColor,
                              ),
                            ),
                          ),
                        ),
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
