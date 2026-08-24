import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/category_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/sub_category_model.dart';

class AdminCategoriesScreen extends ConsumerStatefulWidget {
  const AdminCategoriesScreen({super.key});

  @override
  ConsumerState<AdminCategoriesScreen> createState() => _AdminCategoriesScreenState();
}

class _AdminCategoriesScreenState extends ConsumerState<AdminCategoriesScreen> {
  String _searchQuery = '';
  int _selectedSegment = 0; // 0 = Magaiees Exclusive, 1 = Merchant Marketplace, 2 = All
  List<CategoryModel>? _localCategories;

  void _showAddCategoryDialog(BuildContext context, bool isRtl) {
    final nameArController = TextEditingController();
    final nameEnController = TextEditingController();
    bool isExclusive = _selectedSegment == 0;

    showDialog(
      context: context,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              title: Row(
                children: [
                  const Icon(Icons.create_new_folder, color: Color(0xFF1E40AF)),
                  const SizedBox(width: 8),
                  Text(isRtl ? 'إضافة قسم رئيسي جديد' : 'Add New Main Category'),
                ],
              ),
              content: SizedBox(
                width: 450,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    TextField(
                      controller: nameArController,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'اسم القسم الرئيسي (بالعربية)' : 'Category Name (Arabic)',
                        border: const OutlineInputBorder(),
                        prefixIcon: const Icon(Icons.category),
                      ),
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: nameEnController,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'اسم القسم الرئيسي (بالإنجليزية)' : 'Category Name (English)',
                        border: const OutlineInputBorder(),
                        prefixIcon: const Icon(Icons.language),
                      ),
                    ),
                    const SizedBox(height: 16),
                    SwitchListTile(
                      title: Text(
                        isRtl ? 'حصري لشركة مقاييس (توريد مباشر)' : 'Magaiees Exclusive (Direct Supply)',
                        style: const TextStyle(fontSize: 14),
                      ),
                      subtitle: Text(
                        isRtl ? 'لا يظهر للتجار في إضافة المنتجات' : 'Hidden from merchant product listing',
                        style: const TextStyle(fontSize: 12),
                      ),
                      value: isExclusive,
                      onChanged: (val) => setDialogState(() => isExclusive = val),
                      activeColor: const Color(0xFF1E40AF),
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: Text(isRtl ? 'إلغاء' : 'Cancel'),
                ),
                FilledButton.icon(
                  onPressed: () {
                    if (nameArController.text.trim().isNotEmpty) {
                      final newCat = CategoryModel(
                        id: 'cat_${DateTime.now().millisecondsSinceEpoch}',
                        name: nameEnController.text.trim().isNotEmpty ? nameEnController.text.trim() : nameArController.text.trim(),
                        nameAr: nameArController.text.trim(),
                        iconName: 'category',
                        productCount: 0,
                        subCategories: [],
                        isExclusiveToMagaiees: isExclusive,
                      );
                      setState(() {
                        _localCategories = [...(_localCategories ?? []), newCat];
                      });
                      Navigator.pop(context);
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text(isRtl ? 'تمت إضافة القسم الرئيسي بنجاح' : 'Category added successfully'),
                          backgroundColor: Colors.green,
                        ),
                      );
                    }
                  },
                  icon: const Icon(Icons.check),
                  label: Text(isRtl ? 'حفظ القسم' : 'Save Category'),
                ),
              ],
            );
          },
        );
      },
    );
  }

  void _showAddSubCategoryDialog(BuildContext context, CategoryModel parentCategory, bool isRtl) {
    final nameArController = TextEditingController();
    final nameEnController = TextEditingController();

    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: Row(
            children: [
              const Icon(Icons.subdirectory_arrow_right, color: Color(0xFF059669)),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  isRtl ? 'إضافة قسم فرعي / ماركة إلى (${parentCategory.nameAr})' : 'Add Sub-Category to (${parentCategory.name})',
                  style: const TextStyle(fontSize: 16),
                ),
              ),
            ],
          ),
          content: SizedBox(
            width: 450,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: nameArController,
                  decoration: InputDecoration(
                    labelText: isRtl ? 'اسم القسم الفرعي / الماركة (بالعربية)' : 'Sub-Category Name (Arabic)',
                    border: const OutlineInputBorder(),
                    prefixIcon: const Icon(Icons.branding_watermark),
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: nameEnController,
                  decoration: InputDecoration(
                    labelText: isRtl ? 'اسم القسم الفرعي (بالإنجليزية)' : 'Sub-Category Name (English)',
                    border: const OutlineInputBorder(),
                    prefixIcon: const Icon(Icons.language),
                  ),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: Text(isRtl ? 'إلغاء' : 'Cancel'),
            ),
            FilledButton.icon(
              onPressed: () {
                if (nameArController.text.trim().isNotEmpty) {
                  final newSub = SubCategoryModel(
                    id: 'sub_${DateTime.now().millisecondsSinceEpoch}',
                    categoryId: parentCategory.id,
                    name: nameEnController.text.trim().isNotEmpty ? nameEnController.text.trim() : nameArController.text.trim(),
                    nameAr: nameArController.text.trim(),
                    productCount: 0,
                  );
                  setState(() {
                    _localCategories = (_localCategories ?? []).map((cat) {
                      if (cat.id == parentCategory.id) {
                        return cat.copyWith(subCategories: [...cat.subCategories, newSub]);
                      }
                      return cat;
                    }).toList();
                  });
                  Navigator.pop(context);
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(isRtl ? 'تمت إضافة القسم الفرعي بنجاح' : 'Sub-category added'),
                      backgroundColor: Colors.green,
                    ),
                  );
                }
              },
              icon: const Icon(Icons.check),
              label: Text(isRtl ? 'حفظ القسم الفرعي' : 'Save Sub-Category'),
            ),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final categoriesAsync = ref.watch(categoriesProvider);

    return categoriesAsync.when(
      data: (loadedCategories) {
        _localCategories ??= List.from(loadedCategories);
        final allCategories = _localCategories!;

        // Filter by segment
        List<CategoryModel> segmentedCategories;
        switch (_selectedSegment) {
          case 0:
            segmentedCategories = allCategories.where((c) => c.isExclusiveToMagaiees).toList();
            break;
          case 1:
            segmentedCategories = allCategories.where((c) => !c.isExclusiveToMagaiees).toList();
            break;
          default:
            segmentedCategories = allCategories;
        }

        // Filter by search
        final filteredCategories = segmentedCategories.where((cat) {
          if (_searchQuery.isEmpty) return true;
          final matchMain = cat.name.toLowerCase().contains(_searchQuery) || cat.nameAr.contains(_searchQuery);
          final matchSub = cat.subCategories.any((s) => s.name.toLowerCase().contains(_searchQuery) || s.nameAr.contains(_searchQuery));
          return matchMain || matchSub;
        }).toList();

        // Stats for the current segment
        final segMainCount = segmentedCategories.length;
        final segSubCount = segmentedCategories.fold<int>(0, (sum, c) => sum + c.subCategories.length);
        final segProductCount = segmentedCategories.fold<int>(0, (sum, c) => sum + c.productCount);

        return Scaffold(
          backgroundColor: const Color(0xFFF8FAFC),
          body: Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Top Header
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            isRtl ? 'إدارة هيكلية الأقسام والماركات' : 'Categories & Brands Hierarchy',
                            style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                                  fontWeight: FontWeight.bold,
                                  color: const Color(0xFF0F172A),
                                ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            isRtl
                                ? 'إدارة الأقسام الرئيسية، الأقسام الفرعية (الماركات والأنواع)، وتوزيع الأصناف'
                                : 'Manage Main Categories, Sub-categories, Brands, and Product Lines',
                            style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                          ),
                        ],
                      ),
                    ),
                    FilledButton.icon(
                      style: FilledButton.styleFrom(
                        backgroundColor: const Color(0xFF1E40AF),
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      onPressed: () => _showAddCategoryDialog(context, isRtl),
                      icon: const Icon(Icons.add_circle_outline),
                      label: Text(isRtl ? 'إضافة قسم رئيسي' : 'Add Main Category'),
                    ),
                  ],
                ),
                const SizedBox(height: 20),

                // Segmented Tabs
                SizedBox(
                  width: double.infinity,
                  child: SegmentedButton<int>(
                    segments: [
                      ButtonSegment<int>(
                        value: 0,
                        label: Text(isRtl ? '🌟 حصرياً لشركة مقاييس (توريد مباشر)' : '🌟 Magaiees Exclusive Supply', style: const TextStyle(fontSize: 13)),
                        icon: const Icon(Icons.verified, size: 18),
                      ),
                      ButtonSegment<int>(
                        value: 1,
                        label: Text(isRtl ? '🏪 سوق التجار والموردين' : '🏪 Merchant Marketplace', style: const TextStyle(fontSize: 13)),
                        icon: const Icon(Icons.storefront, size: 18),
                      ),
                      ButtonSegment<int>(
                        value: 2,
                        label: Text(isRtl ? '🌐 عرض الكل' : '🌐 View All', style: const TextStyle(fontSize: 13)),
                        icon: const Icon(Icons.all_inclusive, size: 18),
                      ),
                    ],
                    selected: {_selectedSegment},
                    onSelectionChanged: (selection) {
                      setState(() => _selectedSegment = selection.first);
                    },
                    style: SegmentedButton.styleFrom(
                      selectedBackgroundColor: _selectedSegment == 0
                          ? const Color(0xFF1E40AF).withValues(alpha: 0.15)
                          : _selectedSegment == 1
                              ? const Color(0xFF059669).withValues(alpha: 0.15)
                              : Colors.grey.withValues(alpha: 0.15),
                      selectedForegroundColor: _selectedSegment == 0
                          ? const Color(0xFF1E40AF)
                          : _selectedSegment == 1
                              ? const Color(0xFF059669)
                              : Colors.grey.shade800,
                    ),
                  ),
                ),
                const SizedBox(height: 16),

                // Stats Row
                Row(
                  children: [
                    _buildStatCard(
                      title: isRtl ? 'الأقسام الرئيسية' : 'Main Categories',
                      value: segMainCount.toString(),
                      icon: Icons.category,
                      color: const Color(0xFF3B82F6),
                    ),
                    const SizedBox(width: 16),
                    _buildStatCard(
                      title: isRtl ? 'الأقسام الفرعية والماركات' : 'Sub-categories / Brands',
                      value: segSubCount.toString(),
                      icon: Icons.branding_watermark,
                      color: const Color(0xFF10B981),
                    ),
                    const SizedBox(width: 16),
                    _buildStatCard(
                      title: isRtl ? 'إجمالي الأصناف والمنتجات' : 'Total Active Items',
                      value: segProductCount.toString(),
                      icon: Icons.inventory_2,
                      color: const Color(0xFFF59E0B),
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                // Search
                TextField(
                  onChanged: (val) => setState(() => _searchQuery = val.trim().toLowerCase()),
                  decoration: InputDecoration(
                    hintText: isRtl ? 'البحث عن قسم رئيسي أو قسم فرعي (ماركة)...' : 'Search category or brand...',
                    prefixIcon: const Icon(Icons.search),
                    filled: true,
                    fillColor: Colors.white,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide(color: Colors.grey.shade300),
                    ),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  ),
                ),
                const SizedBox(height: 16),

                // Categories Tree
                Expanded(
                  child: filteredCategories.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.folder_off_outlined, size: 64, color: Colors.grey.shade400),
                              const SizedBox(height: 16),
                              Text(
                                isRtl ? 'لا توجد أقسام في هذا التبويب' : 'No categories in this segment',
                                style: const TextStyle(fontSize: 15, color: Colors.grey),
                              ),
                            ],
                          ),
                        )
                      : ListView.builder(
                          itemCount: filteredCategories.length,
                          itemBuilder: (context, index) {
                            final cat = filteredCategories[index];
                            return Card(
                              margin: const EdgeInsets.only(bottom: 14),
                              elevation: 1,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(14),
                                side: BorderSide(
                                  color: cat.isExclusiveToMagaiees ? const Color(0xFF1E40AF).withValues(alpha: 0.3) : Colors.grey.shade200,
                                ),
                              ),
                              child: Theme(
                                data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
                                child: ExpansionTile(
                                  initiallyExpanded: index == 0,
                                  leading: Container(
                                    padding: const EdgeInsets.all(10),
                                    decoration: BoxDecoration(
                                      color: cat.isExclusiveToMagaiees ? const Color(0xFFEFF6FF) : const Color(0xFFF0FDF4),
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                    child: Icon(
                                      _getCategoryIcon(cat.id),
                                      color: cat.isExclusiveToMagaiees ? const Color(0xFF1E40AF) : const Color(0xFF059669),
                                      size: 26,
                                    ),
                                  ),
                                  title: Row(
                                    children: [
                                      Text(
                                        isRtl ? cat.nameAr : cat.name,
                                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                                      ),
                                      const SizedBox(width: 8),
                                      if (cat.isExclusiveToMagaiees)
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                          decoration: BoxDecoration(
                                            color: const Color(0xFF1E40AF),
                                            borderRadius: BorderRadius.circular(12),
                                          ),
                                          child: Text(
                                            isRtl ? '⭐ حصري مقاييس' : '⭐ Magaiees',
                                            style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                                          ),
                                        ),
                                      if (!cat.isExclusiveToMagaiees)
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                          decoration: BoxDecoration(
                                            color: const Color(0xFF059669),
                                            borderRadius: BorderRadius.circular(12),
                                          ),
                                          child: Text(
                                            isRtl ? '🏪 سوق التجار' : '🏪 Marketplace',
                                            style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                                          ),
                                        ),
                                    ],
                                  ),
                                  subtitle: Text(
                                    isRtl
                                        ? '${cat.subCategories.length} أقسام فرعية وماركات • ${cat.productCount} صنف'
                                        : '${cat.subCategories.length} sub-categories • ${cat.productCount} items',
                                    style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                                  ),
                                  trailing: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      OutlinedButton.icon(
                                        style: OutlinedButton.styleFrom(
                                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                          side: const BorderSide(color: Color(0xFF059669)),
                                        ),
                                        onPressed: () => _showAddSubCategoryDialog(context, cat, isRtl),
                                        icon: const Icon(Icons.add, size: 16, color: Color(0xFF059669)),
                                        label: Text(
                                          isRtl ? 'إضافة ماركة' : 'Add Brand',
                                          style: const TextStyle(color: Color(0xFF059669), fontSize: 12),
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      const Icon(Icons.expand_more),
                                    ],
                                  ),
                                  children: [
                                    Container(
                                      width: double.infinity,
                                      padding: const EdgeInsets.all(16),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFF8FAFC),
                                        borderRadius: const BorderRadius.only(
                                          bottomLeft: Radius.circular(14),
                                          bottomRight: Radius.circular(14),
                                        ),
                                        border: Border(top: BorderSide(color: Colors.grey.shade200)),
                                      ),
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Row(
                                            children: [
                                              const Icon(Icons.account_tree_outlined, size: 16, color: Color(0xFF475569)),
                                              const SizedBox(width: 6),
                                              Text(
                                                isRtl ? 'الأقسام الفرعية والماركات:' : 'Sub-categories & Brands:',
                                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF475569)),
                                              ),
                                            ],
                                          ),
                                          const SizedBox(height: 12),
                                          if (cat.subCategories.isEmpty)
                                            Text(
                                              isRtl ? 'لا توجد أقسام فرعية مضافة بعد.' : 'No sub-categories yet.',
                                              style: TextStyle(color: Colors.grey.shade500, fontSize: 13),
                                            )
                                          else
                                            Wrap(
                                              spacing: 10,
                                              runSpacing: 10,
                                              children: cat.subCategories.map((sub) {
                                                return Container(
                                                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                                                  decoration: BoxDecoration(
                                                    color: Colors.white,
                                                    borderRadius: BorderRadius.circular(20),
                                                    border: Border.all(color: Colors.grey.shade300),
                                                    boxShadow: [
                                                      BoxShadow(
                                                        color: Colors.black.withValues(alpha: 0.03),
                                                        blurRadius: 4,
                                                        offset: const Offset(0, 2),
                                                      ),
                                                    ],
                                                  ),
                                                  child: Row(
                                                    mainAxisSize: MainAxisSize.min,
                                                    children: [
                                                      Icon(
                                                        Icons.check_circle_outline,
                                                        size: 16,
                                                        color: cat.isExclusiveToMagaiees ? const Color(0xFF1E40AF) : const Color(0xFF059669),
                                                      ),
                                                      const SizedBox(width: 6),
                                                      Text(
                                                        isRtl ? sub.nameAr : sub.name,
                                                        style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: Color(0xFF1E293B)),
                                                      ),
                                                      const SizedBox(width: 8),
                                                      Container(
                                                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                                        decoration: BoxDecoration(
                                                          color: const Color(0xFFEFF6FF),
                                                          borderRadius: BorderRadius.circular(10),
                                                        ),
                                                        child: Text(
                                                          '${sub.productCount} ${isRtl ? 'صنف' : 'items'}',
                                                          style: const TextStyle(fontSize: 11, color: Color(0xFF1D4ED8), fontWeight: FontWeight.bold),
                                                        ),
                                                      ),
                                                    ],
                                                  ),
                                                );
                                              }).toList(),
                                            ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
                ),
              ],
            ),
          ),
        );
      },
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (err, _) => Center(child: Text(isRtl ? 'خطأ في تحميل الأقسام' : 'Error loading categories')),
    );
  }

  Widget _buildStatCard({
    required String title,
    required String value,
    required IconData icon,
    required Color color,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: Colors.grey.shade200),
          boxShadow: [
            BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 6, offset: const Offset(0, 3)),
          ],
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(icon, color: color, size: 26),
            ),
            const SizedBox(width: 14),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(value, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900, color: Color(0xFF0F172A))),
                Text(title, style: TextStyle(fontSize: 12, color: Colors.grey.shade600, fontWeight: FontWeight.w500)),
              ],
            ),
          ],
        ),
      ),
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
