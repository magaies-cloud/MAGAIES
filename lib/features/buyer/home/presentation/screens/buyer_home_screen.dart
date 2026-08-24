import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/features/buyer/cart/application/cart_provider.dart';
import 'package:magaiees_marketplace/features/buyer/home/presentation/widgets/price_ticker_bar.dart';
import 'package:magaiees_marketplace/features/buyer/home/presentation/widgets/search_bar_widget.dart';
import 'package:magaiees_marketplace/features/buyer/home/presentation/widgets/category_grid_widget.dart';
import 'package:magaiees_marketplace/features/buyer/home/presentation/widgets/featured_deals_widget.dart';
import 'package:magaiees_marketplace/features/buyer/home/presentation/widgets/featured_suppliers_widget.dart';
import 'package:magaiees_marketplace/features/buyer/home/presentation/widgets/material_estimator_banner.dart';
import 'package:magaiees_marketplace/features/buyer/home/presentation/widgets/product_card.dart';
import 'package:magaiees_marketplace/features/buyer/home/presentation/widgets/hero_banner_carousel.dart';
import 'package:magaiees_marketplace/features/locale/presentation/widgets/language_switcher.dart';
import 'package:go_router/go_router.dart';

class BuyerHomeScreen extends ConsumerWidget {
  const BuyerHomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final marketPricesAsync = ref.watch(marketPricesProvider);
    final categoriesAsync = ref.watch(categoriesProvider);
    final filteredProductsAsync = ref.watch(filteredProductsProvider);

    return Scaffold(
      appBar: AppBar(
        title: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Image.asset(
              'assets/images/logo_final.png',
              height: 28,
              errorBuilder: (context, error, stackTrace) => const SizedBox(width: 28, height: 28),
              frameBuilder: (context, child, frame, wasSynchronouslyLoaded) {
                if (wasSynchronouslyLoaded || frame != null) return child;
                return const SizedBox(width: 28, height: 28);
              },
            ),
            const SizedBox(width: 8),
            Text(
              isRtl ? 'منصة مقاييس' : 'Magaiees Platform',
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
          ],
        ),
        actions: const [
          LanguageSwitcher(),
        ],
      ),
      body: CustomScrollView(
        slivers: [
          // 1. Premium Advertising Carousel (تحت الشعار مباشرة)
          const SliverToBoxAdapter(
            child: Padding(
              padding: EdgeInsets.only(top: 12.0, bottom: 8.0),
              child: HeroBannerCarousel(),
            ),
          ),

          // 2. Price Ticker (شريط الأسعار)
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.symmetric(vertical: 6.0),
              child: switch (marketPricesAsync) {
                AsyncData(:final value) => PriceTickerBar(prices: value),
                _ => const SizedBox(height: 40, child: Center(child: CircularProgressIndicator())),
              },
            ),
          ),
          
          // 3. Search Bar (شريط البحث)
          const SliverToBoxAdapter(
            child: Padding(
              padding: EdgeInsets.only(top: 6.0, bottom: 8.0),
              child: SearchBarWidget(),
            ),
          ),

          // 4. Categories Section Header
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.only(left: 16.0, right: 16.0, top: 12.0, bottom: 12.0),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    isRtl ? 'أقسام مواد البناء والمعدات' : 'Categories & Materials',
                    style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  Text(
                    isRtl ? 'اختر القسم' : 'Select Category',
                    style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                  ),
                ],
              ),
            ),
          ),

          // Categories Grid (10 Interactive Categories with Icons & Direct Navigation)
          SliverToBoxAdapter(
            child: switch (categoriesAsync) {
              AsyncData(:final value) => CategoryGridWidget(categories: value),
              AsyncError(:final error) => Center(child: Text(isRtl ? 'خطأ في التحميل: $error' : 'Error: $error')),
              _ => const SizedBox(height: 120, child: Center(child: CircularProgressIndicator())),
            },
          ),

          // 5. Featured Deals Section (عروض وصفقات اليوم)
          const SliverToBoxAdapter(
            child: Padding(
              padding: EdgeInsets.only(top: 16.0),
              child: FeaturedDealsWidget(),
            ),
          ),

          // 6. Material Estimator Banner (حاسبة كميات مواد البناء)
          const SliverToBoxAdapter(
            child: MaterialEstimatorBanner(),
          ),

          // 7. Verified Suppliers & Manufacturers (المصانع والموردين المعتمدين)
          const SliverToBoxAdapter(
            child: FeaturedSuppliersWidget(),
          ),

          // 8. Products Feed Header
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.only(left: 16.0, right: 16.0, top: 20.0, bottom: 8.0),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    isRtl ? 'أحدث المنتجات والمواد المعروضة' : 'Latest Products & Materials',
                    style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  switch (filteredProductsAsync) {
                    AsyncData(:final value) => Text(
                        isRtl ? '${value.length} منتج' : '${value.length} products',
                        style: const TextStyle(color: Colors.grey),
                      ),
                    _ => const SizedBox.shrink(),
                  }
                ],
              ),
            ),
          ),

          // Products Grid
          switch (filteredProductsAsync) {
            AsyncData(:final value) => value.isEmpty
                ? SliverToBoxAdapter(
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 32.0, vertical: 48.0),
                      child: Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.search_off_rounded, size: 64, color: Colors.grey.shade400),
                            const SizedBox(height: 16),
                            Text(
                              isRtl ? 'لا توجد منتجات مطابقة للبحث' : 'No matching products found',
                              style: TextStyle(color: Colors.grey.shade600, fontSize: 16, fontWeight: FontWeight.w500),
                              textAlign: TextAlign.center,
                            ),
                          ],
                        ),
                      ),
                    ),
                  )
                : SliverPadding(
                    padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
                    sliver: SliverGrid(
                      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                        crossAxisCount: 2,
                        childAspectRatio: 0.7,
                        crossAxisSpacing: 12,
                        mainAxisSpacing: 12,
                      ),
                      delegate: SliverChildBuilderDelegate(
                        (context, index) {
                          final product = value[index];
                          return ProductCard(
                            product: product,
                            onTap: () {
                              context.push('/buyer/home/product/${product.id}');
                            },
                            onAddToCart: () {
                              ref.read(cartNotifierProvider.notifier).addProduct(product, 1);
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(isRtl ? 'تمت الإضافة إلى السلة' : 'Added to cart'),
                                  duration: const Duration(seconds: 1),
                                  behavior: SnackBarBehavior.floating,
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                ),
                              );
                            },
                          );
                        },
                        childCount: value.length,
                      ),
                    ),
                  ),
            AsyncError(:final error) => SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.all(32.0),
                  child: Center(
                    child: Column(
                      children: [
                        Icon(Icons.error_outline, size: 48, color: Colors.red.shade300),
                        const SizedBox(height: 12),
                        Text(isRtl ? 'حدث خطأ في تحميل المنتجات' : 'Error loading products', style: TextStyle(color: Colors.grey.shade700)),
                        const SizedBox(height: 16),
                        ElevatedButton.icon(
                          onPressed: () {
                            ref.invalidate(productsProvider);
                          },
                          icon: const Icon(Icons.refresh),
                          label: Text(isRtl ? 'إعادة المحاولة' : 'Retry'),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            _ => const SliverToBoxAdapter(
                child: SizedBox(
                  height: 200,
                  child: Center(
                    child: CircularProgressIndicator(strokeWidth: 3),
                  ),
                ),
              ),
          },

          const SliverToBoxAdapter(
            child: SizedBox(height: 24),
          ),
        ],
      ),
    );
  }
}