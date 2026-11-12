import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:magaiees_marketplace/core/utils/image_optimizer.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/category_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/sub_category_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/presentation/widgets/product_card.dart';
import 'package:magaiees_marketplace/features/buyer/cart/application/cart_provider.dart';

class CategoryProductsScreen extends ConsumerStatefulWidget {
  final String categoryId;
  final String? subCategoryId;
  final String? categoryName;

  const CategoryProductsScreen({
    super.key,
    required this.categoryId,
    this.subCategoryId,
    this.categoryName,
  });

  @override
  ConsumerState<CategoryProductsScreen> createState() => _CategoryProductsScreenState();
}

class _CategoryProductsScreenState extends ConsumerState<CategoryProductsScreen> {
  String? _selectedSubCategoryId;

  @override
  void initState() {
    super.initState();
    _selectedSubCategoryId = widget.subCategoryId;
  }

  @override
  void didUpdateWidget(covariant CategoryProductsScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.subCategoryId != widget.subCategoryId) {
      setState(() {
        _selectedSubCategoryId = widget.subCategoryId;
      });
    }
  }

  String _searchQuery = '';
  String _sortBy = 'all'; // 'all', 'price_asc', 'price_desc', 'in_stock'
  int _exclusivityFilter = 0; // 0: all, 1: exclusive only, 2: merchant stores only

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final categoriesAsync = ref.watch(categoriesProvider);
    final allProductsAsync = ref.watch(productsProvider);

    // Find current category
    final List<CategoryModel> categoryList = categoriesAsync.valueOrNull ?? const <CategoryModel>[];
    final targetCatId = widget.categoryId.trim().toLowerCase();
    CategoryModel? category;
    for (final c in categoryList) {
      if (c.id.trim().toLowerCase() == targetCatId) {
        category = c;
        break;
      }
    }

    final appBarTitle = category != null
        ? (isRtl ? category.nameAr : category.name)
        : (isRtl ? 'القسم' : 'Category');
    
    final bannerTitle = widget.categoryName ?? appBarTitle;

    final List<SubCategoryModel> subCategories = (category?.subCategories ?? const <SubCategoryModel>[])
        .where((SubCategoryModel s) => s.isVisible)
        .toList();
    
    final targetSubId = _selectedSubCategoryId?.trim().toLowerCase();
    SubCategoryModel? subCategory;
    if (targetSubId != null && targetSubId.isNotEmpty) {
      for (final s in subCategories) {
        if (s.id.trim().toLowerCase() == targetSubId) {
          subCategory = s;
          break;
        }
      }
    }
    
    final bannerImageUrl = subCategory?.imageUrl ?? category?.imageUrl;

    final allProducts = allProductsAsync.valueOrNull ?? [];
    final normalizedCatId = widget.categoryId.trim().toLowerCase();
    final totalCatCount = allProducts
        .where((p) => p.categoryId.trim().toLowerCase() == normalizedCatId)
        .length;

    return Scaffold(
      appBar: AppBar(
        title: Text(appBarTitle, style: const TextStyle(fontWeight: FontWeight.bold)),
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
                    width: 56,
                    height: 56,
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      boxShadow: [
                        BoxShadow(color: Colors.black.withValues(alpha: 0.1), blurRadius: 4, offset: const Offset(0, 2)),
                      ],
                    ),
                    child: bannerImageUrl != null && bannerImageUrl.isNotEmpty
                        ? ClipRRect(
                            borderRadius: BorderRadius.circular(12),
                            child: Image.network(
                              ImageOptimizer.optimize(bannerImageUrl, width: 150),
                              fit: BoxFit.cover,
                              errorBuilder: (c, e, s) => Icon(_getCategoryIcon(widget.categoryId), size: 28, color: Theme.of(context).primaryColor),
                            ),
                          )
                        : Icon(_getCategoryIcon(widget.categoryId), size: 28, color: Theme.of(context).primaryColor),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                bannerTitle,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 19,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                            if (category?.isExclusiveToMagaiees == true)
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: Colors.amber.shade400,
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    const Icon(Icons.verified_rounded, size: 12, color: Color(0xFF1E3A8A)),
                                    const SizedBox(width: 4),
                                    Text(
                                      isRtl ? 'حصري مقاييس' : 'Exclusive',
                                      style: const TextStyle(
                                        color: Color(0xFF1E3A8A),
                                        fontSize: 10.5,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          isRtl
                              ? (category?.isExclusiveToMagaiees == true 
                                  ? 'أصناف ومقاسات معتمدة ومباشرة من مقاييس ($totalCatCount صنف)'
                                  : 'تصفح الأصناف والأسعار المعتمدة ($totalCatCount صنف معروض)')
                              : 'Browse certified items and prices ($totalCatCount items)',
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

          // Sub-Categories / Brands Selector (Visual Cards & Filter Chips)
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
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: [
                              Icon(Icons.tune, size: 16, color: Theme.of(context).primaryColor),
                              const SizedBox(width: 6),
                              Text(
                                isRtl ? 'الفروع والأقسام الفرعية والماركات:' : 'Branches & Sub-categories:',
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                              ),
                            ],
                          ),
                          if (_selectedSubCategoryId != null)
                            TextButton(
                              onPressed: () => setState(() => _selectedSubCategoryId = null),
                              style: TextButton.styleFrom(
                                padding: EdgeInsets.zero,
                                minimumSize: Size.zero,
                                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                              ),
                              child: Text(
                                isRtl ? 'عرض كافة الفروع' : 'Show All Branches',
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                  color: Theme.of(context).primaryColor,
                                ),
                              ),
                            ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 8),

                    // Visual Horizontal Cards for Branches / Subcategories
                    SizedBox(
                      height: 98,
                      child: ListView.builder(
                        scrollDirection: Axis.horizontal,
                        padding: const EdgeInsets.symmetric(horizontal: 16.0),
                        itemCount: subCategories.length + 1,
                        itemBuilder: (context, index) {
                          if (index == 0) {
                            final isAllSelected = _selectedSubCategoryId == null;
                            return Padding(
                              padding: const EdgeInsets.only(left: 10.0),
                              child: InkWell(
                                onTap: () => setState(() => _selectedSubCategoryId = null),
                                borderRadius: BorderRadius.circular(14),
                                child: Container(
                                  width: 82,
                                  padding: const EdgeInsets.all(6),
                                  decoration: BoxDecoration(
                                    color: isAllSelected
                                        ? Theme.of(context).primaryColor
                                        : Colors.white,
                                    borderRadius: BorderRadius.circular(14),
                                    border: Border.all(
                                      color: isAllSelected
                                          ? Theme.of(context).primaryColor
                                          : Colors.grey.shade300,
                                      width: isAllSelected ? 1.8 : 1.0,
                                    ),
                                    boxShadow: [
                                      BoxShadow(
                                        color: isAllSelected
                                            ? Theme.of(context).primaryColor.withValues(alpha: 0.25)
                                            : Colors.black.withValues(alpha: 0.04),
                                        blurRadius: 6,
                                        offset: const Offset(0, 2),
                                      ),
                                    ],
                                  ),
                                  child: Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Icon(
                                        Icons.all_inclusive_rounded,
                                        size: 26,
                                        color: isAllSelected ? Colors.white : Theme.of(context).primaryColor,
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                        isRtl ? 'كافة الفروع' : 'All Branches',
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                        style: TextStyle(
                                          fontSize: 11,
                                          fontWeight: FontWeight.bold,
                                          color: isAllSelected ? Colors.white : Colors.black87,
                                        ),
                                      ),
                                      Text(
                                        '($totalCatCount)',
                                        style: TextStyle(
                                          fontSize: 10,
                                          fontWeight: FontWeight.w600,
                                          color: isAllSelected ? Colors.white70 : Colors.grey.shade600,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            );
                          }

                          final sub = subCategories[index - 1];
                          final isSelected = _selectedSubCategoryId?.trim().toLowerCase() == sub.id.trim().toLowerCase();
                          final subTitle = isRtl ? sub.nameAr : sub.name;
                          final subCount = allProducts.where((p) =>
                            p.subCategoryId != null && p.subCategoryId!.trim().toLowerCase() == sub.id.trim().toLowerCase()
                          ).length;
                          final img = (sub.imageUrl != null && sub.imageUrl!.isNotEmpty)
                              ? sub.imageUrl
                              : (category?.imageUrl != null && category!.imageUrl!.isNotEmpty ? category.imageUrl : null);

                          return Padding(
                            padding: const EdgeInsets.only(left: 10.0),
                            child: InkWell(
                              onTap: () {
                                setState(() {
                                  _selectedSubCategoryId = isSelected ? null : sub.id;
                                });
                              },
                              borderRadius: BorderRadius.circular(14),
                              child: Container(
                                width: 84,
                                padding: const EdgeInsets.all(6),
                                decoration: BoxDecoration(
                                  color: isSelected
                                      ? Theme.of(context).primaryColor.withValues(alpha: 0.08)
                                      : Colors.white,
                                  borderRadius: BorderRadius.circular(14),
                                  border: Border.all(
                                    color: isSelected
                                        ? Theme.of(context).primaryColor
                                        : Colors.grey.shade200,
                                    width: isSelected ? 2.0 : 1.0,
                                  ),
                                  boxShadow: [
                                    BoxShadow(
                                      color: isSelected
                                          ? Theme.of(context).primaryColor.withValues(alpha: 0.15)
                                          : Colors.black.withValues(alpha: 0.04),
                                      blurRadius: 6,
                                      offset: const Offset(0, 2),
                                    ),
                                  ],
                                ),
                                child: Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Container(
                                      width: 38,
                                      height: 38,
                                      decoration: BoxDecoration(
                                        borderRadius: BorderRadius.circular(10),
                                        color: Colors.grey.shade100,
                                      ),
                                      child: img != null && img.isNotEmpty
                                          ? ClipRRect(
                                              borderRadius: BorderRadius.circular(10),
                                              child: Image.network(
                                                ImageOptimizer.optimize(img, width: 100),
                                                fit: BoxFit.cover,
                                                errorBuilder: (c, e, s) => Icon(
                                                  Icons.category_rounded,
                                                  size: 20,
                                                  color: Theme.of(context).primaryColor,
                                                ),
                                              ),
                                            )
                                          : Icon(
                                              Icons.category_rounded,
                                              size: 20,
                                              color: Theme.of(context).primaryColor,
                                            ),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      subTitle,
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                      textAlign: TextAlign.center,
                                      style: TextStyle(
                                        fontSize: 10.5,
                                        fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
                                        color: isSelected ? Theme.of(context).primaryColor : const Color(0xFF1E293B),
                                      ),
                                    ),
                                    Text(
                                      '($subCount)',
                                      style: TextStyle(
                                        fontSize: 9.5,
                                        fontWeight: FontWeight.w600,
                                        color: isSelected ? Theme.of(context).primaryColor : Colors.grey.shade600,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          );
                        },
                      ),
                    ),
                    const SizedBox(height: 8),

                    // Quick Filter Chips Bar
                    SizedBox(
                      height: 38,
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
                                label: Text(isRtl ? 'الكل ($totalCatCount)' : 'All ($totalCatCount)'),
                                selected: isAllSelected,
                                onSelected: (_) {
                                  setState(() => _selectedSubCategoryId = null);
                                },
                                selectedColor: Theme.of(context).primaryColor,
                                labelStyle: TextStyle(
                                  color: isAllSelected ? Colors.white : Colors.black87,
                                  fontWeight: isAllSelected ? FontWeight.bold : FontWeight.normal,
                                  fontSize: 12,
                                ),
                                showCheckmark: false,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                              ),
                            );
                          }

                          final sub = subCategories[index - 1];
                          final isSelected = _selectedSubCategoryId?.trim().toLowerCase() == sub.id.trim().toLowerCase();
                          final subTitle = isRtl ? sub.nameAr : sub.name;
                          final subCount = allProducts.where((p) =>
                            p.subCategoryId != null && p.subCategoryId!.trim().toLowerCase() == sub.id.trim().toLowerCase()
                          ).length;

                          return Padding(
                            padding: const EdgeInsets.only(left: 8.0),
                            child: FilterChip(
                              label: Text('$subTitle ($subCount)'),
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
                                fontSize: 12,
                              ),
                              showCheckmark: false,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
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
                      hintText: isRtl ? 'البحث في الأصناف والمقاسات داخل هذا القسم...' : 'Search items & specs...',
                      prefixIcon: const Icon(Icons.search),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                      isDense: true,
                    ),
                  ),
                  const SizedBox(height: 8),
                  // Exclusivity Filter Segment
                  Container(
                    margin: const EdgeInsets.only(bottom: 8),
                    padding: const EdgeInsets.all(3),
                    decoration: BoxDecoration(
                      color: Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.grey.shade300),
                    ),
                    child: Row(
                      children: [
                        Expanded(
                          child: _buildSegmentButton(
                            title: isRtl ? 'الكل' : 'All',
                            icon: Icons.all_inclusive_rounded,
                            isSelected: _exclusivityFilter == 0,
                            onTap: () => setState(() => _exclusivityFilter = 0),
                            activeColor: Theme.of(context).primaryColor,
                          ),
                        ),
                        Expanded(
                          child: _buildSegmentButton(
                            title: isRtl ? 'حصري مقاييس' : 'Exclusive',
                            icon: Icons.verified_rounded,
                            isSelected: _exclusivityFilter == 1,
                            onTap: () => setState(() => _exclusivityFilter = 1),
                            activeColor: const Color(0xFF1E40AF),
                          ),
                        ),
                        Expanded(
                          child: _buildSegmentButton(
                            title: isRtl ? 'متاجر التجار' : 'Merchants',
                            icon: Icons.storefront_rounded,
                            isSelected: _exclusivityFilter == 2,
                            onTap: () => setState(() => _exclusivityFilter = 2),
                            activeColor: const Color(0xFF0F172A),
                          ),
                        ),
                      ],
                    ),
                  ),
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
              final normalizedCatId = widget.categoryId.trim().toLowerCase();
              final normalizedSelectedSubId = _selectedSubCategoryId?.trim().toLowerCase();

              // 1. Filter by category & subcategory (branch)
              var list = products.where((p) {
                final pCatId = p.categoryId.trim().toLowerCase();
                final pSubId = p.subCategoryId?.trim().toLowerCase();

                if (normalizedSelectedSubId != null && normalizedSelectedSubId.isNotEmpty) {
                  // When a branch / subcategory is chosen, match by subcategory id
                  return pSubId == normalizedSelectedSubId;
                } else {
                  // When no branch is chosen, match all items in this category
                  return pCatId == normalizedCatId;
                }
              }).toList();

              // 2. Filter by search query
              if (_searchQuery.isNotEmpty) {
                list = list.where((p) {
                  final nEn = p.name.toLowerCase();
                  final nAr = p.nameAr.toLowerCase();
                  final subAr = (p.subCategoryNameAr ?? '').toLowerCase();
                  return nEn.contains(_searchQuery) || nAr.contains(_searchQuery) || subAr.contains(_searchQuery);
                }).toList();
              }

              // 3. Filter by Exclusivity (0: All, 1: Magaiees Exclusive only, 2: Merchant stores only)
              if (_exclusivityFilter == 1) {
                list = list.where((p) => p.isMagaieesExclusive).toList();
              } else if (_exclusivityFilter == 2) {
                list = list.where((p) => !p.isMagaieesExclusive).toList();
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
                final bool hasSubFilter = normalizedSelectedSubId != null && normalizedSelectedSubId.isNotEmpty;
                return SliverFillRemaining(
                  hasScrollBody: false,
                  child: Center(
                    child: Padding(
                      padding: const EdgeInsets.all(24.0),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.inventory_2_outlined, size: 64, color: Colors.grey.shade400),
                          const SizedBox(height: 16),
                          Text(
                            hasSubFilter
                                ? (isRtl ? 'لا توجد أصناف معروضة في هذا الفرع حالياً' : 'No items found in this branch')
                                : (_searchQuery.isNotEmpty
                                    ? (isRtl ? 'لا توجد نتائج بحث مطابقة' : 'No matching search results')
                                    : (isRtl ? 'لا توجد أصناف في هذا القسم حالياً' : 'No items found in this category')),
                            textAlign: TextAlign.center,
                            style: const TextStyle(fontSize: 15, color: Colors.grey, fontWeight: FontWeight.w600),
                          ),
                          if (hasSubFilter) ...[
                            const SizedBox(height: 14),
                            ElevatedButton.icon(
                              onPressed: () => setState(() => _selectedSubCategoryId = null),
                              icon: const Icon(Icons.all_inclusive_rounded, size: 18),
                              label: Text(isRtl ? 'عرض كافة أصناف القسم' : 'Show All Items in Category'),
                              style: ElevatedButton.styleFrom(
                                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              ),
                            ),
                          ],
                        ],
                      ),
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
                    childAspectRatio: 0.63,
                  ),
                  delegate: SliverChildBuilderDelegate(
                    (context, index) {
                      final product = list[index];
                      return ProductCard(
                        product: product,
                        onTap: () => context.push('/buyer/home/product/${product.id}'),
                        onAddToCart: () {
                          ref.read(cartNotifierProvider.notifier).addProduct(product, 1);
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(isRtl ? 'تمت الإضافة إلى السلة' : 'Added to cart'),
                            ),
                          );
                        },
                      );
                    },
                    childCount: list.length,
                  ),
                ),
              );
            },
            loading: () => const SliverFillRemaining(
              hasScrollBody: false,
              child: Center(child: CircularProgressIndicator()),
            ),
            error: (err, _) => SliverFillRemaining(
              hasScrollBody: false,
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

  Widget _buildSegmentButton({
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
