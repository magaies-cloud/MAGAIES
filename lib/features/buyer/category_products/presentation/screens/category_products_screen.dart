import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/sub_category_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/presentation/widgets/product_card.dart';

class CategoryProductsScreen extends ConsumerStatefulWidget {
  final String categoryId;
  final String? categoryName;

  const CategoryProductsScreen({
    super.key,
    required this.categoryId,
    this.categoryName,
  });

  @override
  ConsumerState<CategoryProductsScreen> createState() => _CategoryProductsScreenState();
}

class _CategoryProductsScreenState extends ConsumerState<CategoryProductsScreen> {
  String? _selectedSubCategoryId;
  String _searchQuery = '';
  String _sortBy = 'all'; // 'all', 'price_asc', 'price_desc', 'in_stock'

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final categoriesAsync = ref.watch(categoriesProvider);
    final allProductsAsync = ref.watch(productsProvider);

    // Find current category
    final categoryList = categoriesAsync.valueOrNull ?? [];
    final category = categoryList.cast<dynamic>().firstWhere(
      (c) => c.id == widget.categoryId,
      orElse: () => null,
    );

    final title = category != null
        ? (isRtl ? category.nameAr : category.name)
        : (widget.categoryName ?? (isRtl ? 'القسم' : 'Category'));

    final subCategories = category?.subCategories ?? <SubCategoryModel>[];

    return Scaffold(
      appBar: AppBar(
        title: Text(title, style: const TextStyle(fontWeight: FontWeight.bold)),
        centerTitle: true,
      ),
      body: CustomScrollView(
        slivers: [
          // Category Banner Header
          SliverToBoxAdapter(
            child: Container(
              margin: const EdgeInsets.fromLTRB(16, 12, 16, 8),
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    Theme.of(context).colorScheme.primary,
                    const Color(0xFF1E3A8A),
                  ],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(16),
                boxShadow: [
                  BoxShadow(
                    color: Theme.of(context).colorScheme.primary.withValues(alpha: 0.25),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.2),
                      shape: BoxShape.circle,
                    ),
                    child: Icon(
                      _getCategoryIcon(widget.categoryId),
                      size: 32,
                      color: Colors.white,
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          title,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 19,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          isRtl
                              ? 'اختر النوع أو الماركة وتصفح الأصناف والأسعار المعتمدة'
                              : 'Select brand/type to browse certified items and prices',
                          style: TextStyle(
                            color: Colors.white.withValues(alpha: 0.9),
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),

          // Sub-Categories / Brands Selector
          if (subCategories.isNotEmpty)
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.symmetric(vertical: 8.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 4.0),
                      child: Row(
                        children: [
                          Icon(Icons.tune, size: 16, color: Theme.of(context).primaryColor),
                          const SizedBox(width: 6),
                          Text(
                            isRtl ? 'الأقسام الفرعية والماركات:' : 'Sub-categories & Brands:',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 6),
                    SizedBox(
                      height: 44,
                      child: ListView.builder(
                        scrollDirection: Axis.horizontal,
                        padding: const EdgeInsets.symmetric(horizontal: 16.0),
                        itemCount: subCategories.length + 1,
                        itemBuilder: (context, index) {
                          if (index == 0) {
                            final isAllSelected = _selectedSubCategoryId == null;
                            return Padding(
                              padding: const EdgeInsets.only(left: 8.0),
                              child: FilterChip(
                                label: Text(isRtl ? 'الكل (${category?.productCount ?? 0})' : 'All'),
                                selected: isAllSelected,
                                onSelected: (_) {
                                  setState(() => _selectedSubCategoryId = null);
                                },
                                selectedColor: Theme.of(context).primaryColor,
                                labelStyle: TextStyle(
                                  color: isAllSelected ? Colors.white : Colors.black87,
                                  fontWeight: isAllSelected ? FontWeight.bold : FontWeight.normal,
                                  fontSize: 13,
                                ),
                                showCheckmark: false,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                              ),
                            );
                          }

                          final sub = subCategories[index - 1];
                          final isSelected = _selectedSubCategoryId == sub.id;
                          final subTitle = isRtl ? sub.nameAr : sub.name;

                          return Padding(
                            padding: const EdgeInsets.only(left: 8.0),
                            child: FilterChip(
                              label: Text(subTitle),
                              selected: isSelected,
                              onSelected: (_) {
                                setState(() {
                                  _selectedSubCategoryId = isSelected ? null : sub.id;
                                });
                              },
                              selectedColor: Theme.of(context).primaryColor,
                              labelStyle: TextStyle(
                                color: isSelected ? Colors.white : Colors.black87,
                                fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                                fontSize: 13,
                              ),
                              showCheckmark: false,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                            ),
                          );
                        },
                      ),
                    ),
                  ],
                ),
              ),
            ),

          // Search & Filter Bar
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 12),
              child: Column(
                children: [
                  TextField(
                    onChanged: (val) => setState(() => _searchQuery = val.trim().toLowerCase()),
                    decoration: InputDecoration(
                      hintText: isRtl ? 'البحث في الأصناف والمقاسات...' : 'Search items & specs...',
                      prefixIcon: const Icon(Icons.search),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                      isDense: true,
                    ),
                  ),
                  const SizedBox(height: 10),
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      children: [
                        _buildSortChip(
                          label: isRtl ? 'جميع الأصناف' : 'All Items',
                          selected: _sortBy == 'all',
                          onSelected: () => setState(() => _sortBy = 'all'),
                        ),
                        const SizedBox(width: 8),
                        _buildSortChip(
                          label: isRtl ? 'الأقل سعراً' : 'Lowest Price',
                          selected: _sortBy == 'price_asc',
                          onSelected: () => setState(() => _sortBy = 'price_asc'),
                        ),
                        const SizedBox(width: 8),
                        _buildSortChip(
                          label: isRtl ? 'الأعلى سعراً' : 'Highest Price',
                          selected: _sortBy == 'price_desc',
                          onSelected: () => setState(() => _sortBy = 'price_desc'),
                        ),
                        const SizedBox(width: 8),
                        _buildSortChip(
                          label: isRtl ? 'المتوفر في المخزون' : 'In Stock Only',
                          selected: _sortBy == 'in_stock',
                          onSelected: () => setState(() => _sortBy = 'in_stock'),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),

          // Products / Items Grid
          allProductsAsync.when(
            data: (products) {
              // 1. Filter by category
              var list = products.where((p) => p.categoryId == widget.categoryId).toList();

              // 2. Filter by subcategory if selected
              if (_selectedSubCategoryId != null) {
                list = list.where((p) => p.subCategoryId == _selectedSubCategoryId).toList();
              }

              // 3. Filter by search query
              if (_searchQuery.isNotEmpty) {
                list = list.where((p) {
                  final nEn = p.name.toLowerCase();
                  final nAr = p.nameAr.toLowerCase();
                  final subAr = (p.subCategoryNameAr ?? '').toLowerCase();
                  return nEn.contains(_searchQuery) || nAr.contains(_searchQuery) || subAr.contains(_searchQuery);
                }).toList();
              }

              // 4. Sort
              if (_sortBy == 'price_asc') {
                list.sort((a, b) => a.pricePerTon.compareTo(b.pricePerTon));
              } else if (_sortBy == 'price_desc') {
                list.sort((a, b) => b.pricePerTon.compareTo(a.pricePerTon));
              } else if (_sortBy == 'in_stock') {
                list = list.where((p) => p.inStock).toList();
              }

              if (list.isEmpty) {
                return SliverFillRemaining(
                  hasScrollBody: false,
                  child: Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.inventory_2_outlined, size: 64, color: Colors.grey.shade400),
                        const SizedBox(height: 16),
                        Text(
                          isRtl ? 'لا توجد أصناف مطابقة لهذا التصنيف حالياً' : 'No items found for this subcategory',
                          style: const TextStyle(fontSize: 15, color: Colors.grey),
                        ),
                      ],
                    ),
                  ),
                );
              }

              return SliverPadding(
                padding: const EdgeInsets.symmetric(horizontal: 16.0),
                sliver: SliverGrid(
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    mainAxisSpacing: 12,
                    crossAxisSpacing: 12,
                    childAspectRatio: 0.72,
                  ),
                  delegate: SliverChildBuilderDelegate(
                    (context, index) {
                      final product = list[index];
                      return ProductCard(
                        product: product,
                        onTap: () => context.push('/buyer/home/product/${product.id}'),
                      );
                    },
                    childCount: list.length,
                  ),
                ),
              );
            },
            loading: () => const SliverFillRemaining(
              child: Center(child: CircularProgressIndicator()),
            ),
            error: (err, _) => SliverFillRemaining(
              child: Center(child: Text(isRtl ? 'خطأ في جلب الأصناف' : 'Error loading items')),
            ),
          ),

          const SliverToBoxAdapter(child: SizedBox(height: 24)),
        ],
      ),
    );
  }

  Widget _buildSortChip({
    required String label,
    required bool selected,
    required VoidCallback onSelected,
  }) {
    return ChoiceChip(
      label: Text(label, style: TextStyle(fontSize: 12, fontWeight: selected ? FontWeight.bold : FontWeight.normal)),
      selected: selected,
      onSelected: (_) => onSelected(),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
    );
  }

  IconData _getCategoryIcon(String categoryId) {
    switch (categoryId) {
      case '1': return Icons.construction;
      case '2': return Icons.foundation;
      case '3': return Icons.grid_on;
      case '4': return Icons.window;
      case '5': return Icons.format_paint;
      case '6': return Icons.plumbing;
      case '7': return Icons.brush;
      case '8': return Icons.bolt;
      case '9': return Icons.terrain;
      default: return Icons.category;
    }
  }
}
