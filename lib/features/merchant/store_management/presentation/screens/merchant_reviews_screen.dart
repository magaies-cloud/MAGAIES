import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/features/merchant/store_management/application/merchant_reviews_provider.dart';

class MerchantReviewsScreen extends ConsumerWidget {
  const MerchantReviewsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final reviewsState = ref.watch(merchantReviewsProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('التقييمات والآراء'),
        centerTitle: true,
      ),
      body: Directionality(
        textDirection: TextDirection.rtl,
        child: reviewsState.when(
          data: (reviews) {
            if (reviews.isEmpty) {
              return const Center(
                child: Text(
                  'لا توجد تقييمات حتى الآن',
                  style: TextStyle(fontSize: 18),
                ),
              );
            }

            double totalRating = 0;
            for (var review in reviews) {
              totalRating += (review['rating'] as num?)?.toDouble() ?? 0;
            }
            final averageRating = totalRating / reviews.length;

            return RefreshIndicator(
              onRefresh: () =>
                  ref.read(merchantReviewsProvider.notifier).loadReviews(),
              child: CustomScrollView(
                slivers: [
                  SliverToBoxAdapter(
                    child: _buildHeader(reviews.length, averageRating),
                  ),
                  SliverList(
                    delegate: SliverChildBuilderDelegate(
                      (context, index) {
                        final review = reviews[index];
                        return _buildReviewCard(review);
                      },
                      childCount: reviews.length,
                    ),
                  ),
                ],
              ),
            );
          },
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (error, stack) => Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text('حدث خطأ: $error'),
                const SizedBox(height: 16),
                ElevatedButton(
                  onPressed: () =>
                      ref.read(merchantReviewsProvider.notifier).loadReviews(),
                  child: const Text('إعادة المحاولة'),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildHeader(int totalReviews, double averageRating) {
    return Card(
      margin: const EdgeInsets.all(16.0),
      elevation: 4.0,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          children: [
            const Text(
              'متوسط التقييمات',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  averageRating.toStringAsFixed(1),
                  style: const TextStyle(
                      fontSize: 48, fontWeight: FontWeight.bold),
                ),
                const SizedBox(width: 16),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildStars(averageRating.round()),
                    const SizedBox(height: 4),
                    Text(
                      '$totalReviews تقييم',
                      style: const TextStyle(color: Colors.grey, fontSize: 16),
                    ),
                  ],
                )
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildReviewCard(Map<String, dynamic> review) {
    final product = review['products'] as Map<String, dynamic>?;
    final productName =
        product?['name_ar'] ?? product?['name'] ?? 'منتج غير معروف';
    final user = review['users'] as Map<String, dynamic>?;
    final buyerName = user?['name_ar'] ?? user?['name'] ?? 'مشتري غير معروف';
    final rating = (review['rating'] as num?)?.toInt() ?? 0;
    final comment = review['comment'] as String?;
    final dateStr = review['created_at'] as String?;
    DateTime? date;
    if (dateStr != null) {
      date = DateTime.tryParse(dateStr);
    }

    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
      elevation: 2.0,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    buyerName,
                    style: const TextStyle(
                        fontWeight: FontWeight.bold, fontSize: 16),
                  ),
                ),
                if (date != null)
                  Text(
                    '${date.year}/${date.month.toString().padLeft(2, '0')}/${date.day.toString().padLeft(2, '0')}',
                    style: const TextStyle(color: Colors.grey, fontSize: 12),
                  ),
              ],
            ),
            const SizedBox(height: 4),
            Text(
              productName,
              style: const TextStyle(color: Colors.grey, fontSize: 14),
            ),
            const SizedBox(height: 8),
            _buildStars(rating),
            if (comment != null && comment.trim().isNotEmpty) ...[
              const SizedBox(height: 12),
              Text(
                comment.trim(),
                style: const TextStyle(fontSize: 15, height: 1.4),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildStars(int rating) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: List.generate(5, (index) {
        return Icon(
          Icons.star,
          size: 22,
          color: index < rating ? Colors.amber : Colors.grey.shade300,
        );
      }),
    );
  }
}
