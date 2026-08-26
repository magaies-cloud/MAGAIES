import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';

class SubCategoriesScreen extends ConsumerWidget {
  final String categoryId;
  final String? categoryName;

  const SubCategoriesScreen({
    super.key,
    required this.categoryId,
    this.categoryName,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final categoriesAsync = ref.watch(categoriesProvider);
    final width = MediaQuery.of(context).size.width;
    final crossAxisCount = width > 800 ? 6 : 4;

    return Scaffold(
      backgroundColor: const Color(0xFFF9FAFB),
      appBar: AppBar(
        title: Text(
          categoryName ?? (isRtl ? '??????? ???????' : 'Sub-categories'),
          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
        ),
        centerTitle: true,
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
        elevation: 0,
      ),
      body: categoriesAsync.when(
        data: (categories) {
          final category = categories.firstWhere(
            (c) => c.id == categoryId,
            orElse: () => throw Exception('Category not found'),
          );

          final subs = category.subCategories;

          if (subs.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.category_outlined, size: 64, color: Colors.grey.shade300),
                  const SizedBox(height: 16),
                  Text(
                    isRtl ? '?? ???? ????? ????? ?? ??? ?????' : 'No sub-categories here',
                    style: TextStyle(fontSize: 16, color: Colors.grey.shade500),
                  ),
                ],
              ),
            );
          }

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16.0),
            child: GridView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: crossAxisCount,
                mainAxisSpacing: 16,
                crossAxisSpacing: 10,
                childAspectRatio: 0.72,
              ),
              itemCount: subs.length,
              itemBuilder: (context, index) {
                final sub = subs[index];
                final name = isRtl ? sub.nameAr : sub.name;
                
                return InkWell(
                  onTap: () {
                    context.push('/buyer/home/category/$categoryId/subcategory/${sub.id}?name=${Uri.encodeComponent(name)}');
                  },
                  borderRadius: BorderRadius.circular(22),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        width: 80,
                        height: 80,
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(22),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.04),
                              blurRadius: 10,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: sub.imageUrl != null
                            ? ClipRRect(
                                borderRadius: BorderRadius.circular(22),
                                child: Image.network(
                                  sub.imageUrl!,
                                  fit: BoxFit.cover,
                                  loadingBuilder: (ctx, child, progress) {
                                    if (progress == null) return child;
                                    return const Center(child: SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2)));
                                  },
                                  errorBuilder: (c, e, s) => Icon(Icons.image_not_supported, color: Colors.grey.shade300),
                                ),
                              )
                            : Center(
                                child: Icon(
                                  Icons.category_outlined,
                                  color: const Color(0xFF1E40AF).withOpacity(0.5),
                                  size: 32,
                                ),
                              ),
                      ),
                      const SizedBox(height: 6),
                      Expanded(
                        child: Text(
                          name,
                          textAlign: TextAlign.center,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontSize: 11.5,
                            fontWeight: FontWeight.bold,
                            color: Colors.grey.shade800,
                            height: 1.2,
                          ),
                        ),
                      ),
                    ],
                  ),
                );
              },
            ),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, st) => Center(child: Text('Error: $e')),
      ),
    );
  }
}
