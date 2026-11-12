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
import 'package:magaiees_marketplace/features/notifications/application/notifications_provider.dart';
import 'package:magaiees_marketplace/features/notifications/presentation/screens/notifications_screen.dart';
import 'package:magaiees_marketplace/features/buyer/wishlist/presentation/screens/wishlist_screen.dart';
import 'package:go_router/go_router.dart';
import 'package:magaiees_marketplace/core/utils/app_notification.dart';

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
            Flexible(
              child: Text(
                isRtl ? 'منصة السيطرة' : 'Magaiees Platform',
                style: const TextStyle(fontWeight: FontWeight.bold),
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
        actions: [
          Consumer(
            builder: (context, ref, child) {
              final unreadCount = ref.watch(unreadNotificationsCountProvider);
              return Stack(
                alignment: Alignment.center,
                children: [
                  IconButton(
                    icon: const Icon(Icons.notifications_outlined),
                    onPressed: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const NotificationsScreen()),
                      );
                    },
                  ),
                  if (unreadCount > 0)
                    Positioned(
                      right: 8,
                      top: 8,
                      child: Container(
                        padding: const EdgeInsets.all(4),
                        decoration: const BoxDecoration(
                          color: Colors.red,
                          shape: BoxShape.circle,
                        ),
                        child: Text(
                          unreadCount.toString(),
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ),
                ],
              );
            },
          ),
          Consumer(
            builder: (context, ref, child) {
              return IconButton(
                icon: const Icon(Icons.favorite_border),
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const WishlistScreen()),
                  );
                },
              );
            },
          ),
          const LanguageSwitcher(),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(productsProvider);
          ref.invalidate(categoriesProvider);
          ref.invalidate(marketPricesProvider);
        },
        child: CustomScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
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
                  Expanded(
                    child: Text(
                      isRtl ? 'أقسام مواد البناء والمعدات' : 'Categories & Materials',
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  const SizedBox(width: 8),
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

          // 8. Products Feed Header & Exclusivity Segment
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.only(left: 16.0, right: 16.0, top: 20.0, bottom: 8.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text(
                          isRtl ? 'أحدث المنتجات والمواد المعروضة' : 'Latest Products & Materials',
                          style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      const SizedBox(width: 8),
                      switch (filteredProductsAsync) {
                        AsyncData(:final value) => Text(
                            isRtl ? '${value.length} منتج' : '${value.length} products',
                            style: const TextStyle(color: Colors.grey),
                          ),
                        _ => const SizedBox.shrink(),
                      }
                    ],
                  ),
                  const SizedBox(height: 10),
                  Consumer(
                    builder: (context, ref, _) {
                      final currentExclusivity = ref.watch(exclusivityFilterProvider);
                      return Container(
                        padding: const EdgeInsets.all(3),
                        decoration: BoxDecoration(
                          color: Colors.grey.shade100,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: Colors.grey.shade300),
                        ),
                        child: Row(
                          children: [
                            Expanded(
                              child: _buildHomeSegmentButton(
                                title: isRtl ? 'الكل' : 'All',
                                icon: Icons.all_inclusive_rounded,
                                isSelected: currentExclusivity == 0,
                                onTap: () => ref.read(exclusivityFilterProvider.notifier).state = 0,
                                activeColor: Theme.of(context).primaryColor,
                              ),
                            ),
                            Expanded(
                              child: _buildHomeSegmentButton(
                                title: isRtl ? '⭐ حصري السيطرة' : '⭐ Exclusive',
                                icon: Icons.verified_rounded,
                                isSelected: currentExclusivity == 1,
                                onTap: () => ref.read(exclusivityFilterProvider.notifier).state = 1,
                                activeColor: const Color(0xFF1E40AF),
                              ),
                            ),
                            Expanded(
                              child: _buildHomeSegmentButton(
                                title: isRtl ? '🏪 متاجر التجار' : '🏪 Merchants',
                                icon: Icons.storefront_rounded,
                                isSelected: currentExclusivity == 2,
                                onTap: () => ref.read(exclusivityFilterProvider.notifier).state = 2,
                                activeColor: const Color(0xFF0F172A),
                              ),
                            ),
                          ],
                        ),
                      );
                    },
                  ),
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
                        childAspectRatio: 0.63,
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
                              AppNotification.showSuccess(
                                context,
                                isRtl ? 'تمت الإضافة إلى السلة' : 'Added to cart',
                                icon: Icons.shopping_bag_outlined,
                              );
                            },
                          );
                        },
                        childCount: value.length,
                      ),
                    ),
                  ),
            AsyncError() => SliverToBoxAdapter(
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
      ),
    );
  }

  Widget _buildHomeSegmentButton({
    required String title,
    required IconData icon,
    required bool isSelected,
    required VoidCallback onTap,
    required Color activeColor,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(9),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(vertical: 7),
        decoration: BoxDecoration(
          color: isSelected ? activeColor : Colors.transparent,
          borderRadius: BorderRadius.circular(9),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: activeColor.withValues(alpha: 0.3),
                    blurRadius: 4,
                    offset: const Offset(0, 2),
                  ),
                ]
              : null,
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              icon,
              size: 14,
              color: isSelected ? Colors.white : Colors.grey.shade700,
            ),
            const SizedBox(width: 4),
            Flexible(
              child: Text(
                title,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  color: isSelected ? Colors.white : Colors.grey.shade800,
                  fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                  fontSize: 12,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}