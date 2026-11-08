import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/features/admin/categories/application/admin_categories_provider.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/category_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/sub_category_model.dart';
import 'package:magaiees_marketplace/features/admin/presentation/widgets/admin_error_view.dart';
import 'package:magaiees_marketplace/core/providers/global_settings_provider.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

class AdminCategoriesScreen extends ConsumerStatefulWidget {
  const AdminCategoriesScreen({super.key});

  @override
  ConsumerState<AdminCategoriesScreen> createState() => _AdminCategoriesScreenState();
}

class _AdminCategoriesScreenState extends ConsumerState<AdminCategoriesScreen> {
  String _searchQuery = '';
  int _selectedSegment = 0; // 0: All, 1: Sponsored, 2: Exclusive, 3: Marketplace, 4: Top Selling
  String _selectedSort = 'default';
  bool _isGridView = true;
  CategoryModel? _selectedCategory;

  final Color _primaryBlue = const Color(0xFF1E40AF);
  final Color _darkSlate = const Color(0xFF0F172A);

  void _showSnackBar(BuildContext context, String message, bool isError) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            Icon(isError ? Icons.error_outline : Icons.check_circle_outline, color: Colors.white),
            const SizedBox(width: 8),
            Expanded(child: Text(message, style: const TextStyle(color: Colors.white))),
          ],
        ),
        backgroundColor: isError ? Colors.red.shade700 : Colors.green.shade700,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        margin: const EdgeInsets.all(16),
      ),
    );
  }

  Future<void> _toggleCategoryVisibility(CategoryModel category, bool isRtl) async {
    final nextVisible = !category.isVisible;
    // Optimistic local update in categories provider
    ref.read(categoriesProvider.notifier).toggleCategoryVisibilityLocally(category.id, nextVisible);
    if (_selectedCategory?.id == category.id) {
      setState(() {
        _selectedCategory = _selectedCategory!.copyWith(isVisible: nextVisible);
      });
    }
    await ref.read(globalSettingsProvider.notifier).toggleCategoryVisibility(category.id, nextVisible);
    if (mounted) {
      final msg = nextVisible
          ? (isRtl ? 'تم إظهار قسم "${category.nameAr}" للمشترين بنجاح' : 'Category "${category.name}" is now visible to buyers')
          : (isRtl ? 'تم إخفاء قسم "${category.nameAr}" وكافة فروعه عن المشترين' : 'Category "${category.name}" and all sub-categories are now hidden from buyers');
      _showSnackBar(context, msg, false);
    }
  }

  Future<void> _toggleSubCategoryVisibility(String parentCategoryId, SubCategoryModel subCategory, bool isRtl) async {
    final nextVisible = !subCategory.isVisible;
    // Optimistic local update in categories provider
    ref.read(categoriesProvider.notifier).toggleSubCategoryVisibilityLocally(parentCategoryId, subCategory.id, nextVisible);
    if (_selectedCategory?.id == parentCategoryId) {
      final updatedSubs = _selectedCategory!.subCategories.map((s) {
        if (s.id == subCategory.id) return s.copyWith(isVisible: nextVisible);
        return s;
      }).toList();
      setState(() {
        _selectedCategory = _selectedCategory!.copyWith(subCategories: updatedSubs);
      });
    }
    await ref.read(globalSettingsProvider.notifier).toggleSubCategoryVisibility(subCategory.id, nextVisible);
    if (mounted) {
      final msg = nextVisible
          ? (isRtl ? 'تم إظهار القسم الفرعي "${subCategory.nameAr}" للمشترين' : 'Sub-category "${subCategory.name}" is now visible to buyers')
          : (isRtl ? 'تم إخفاء القسم الفرعي "${subCategory.nameAr}" عن المشترين' : 'Sub-category "${subCategory.name}" is now hidden from buyers');
      _showSnackBar(context, msg, false);
    }
  }

  void _showAddCategoryDialog(BuildContext context, bool isRtl) {
    final nameArController = TextEditingController();
    final nameEnController = TextEditingController();
    bool isExclusive = _selectedSegment == 2;
    Uint8List? selectedImageBytes;
    String? selectedImageExt;
    bool isUploading = false;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              title: Row(
                children: [
                  Icon(Icons.create_new_folder, color: _primaryBlue),
                  const SizedBox(width: 8),
                  Text(isRtl ? 'إضافة قسم رئيسي جديد' : 'Add New Main Category', style: TextStyle(color: _darkSlate, fontWeight: FontWeight.bold)),
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
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        prefixIcon: const Icon(Icons.category),
                      ),
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: nameEnController,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'اسم القسم الرئيسي (بالانجليزية)' : 'Category Name (English)',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        prefixIcon: const Icon(Icons.language),
                      ),
                    ),
                    const SizedBox(height: 16),
                    InkWell(
                      onTap: () async {
                        final picker = ImagePicker();
                        final XFile? image = await picker.pickImage(source: ImageSource.gallery, imageQuality: 60, maxWidth: 800);
                        if (image != null) {
                          final bytes = await image.readAsBytes();
                          final ext = image.name.split('.').last;
                          setDialogState(() {
                            selectedImageBytes = bytes;
                            selectedImageExt = ext;
                          });
                        }
                      },
                      borderRadius: BorderRadius.circular(12),
                      child: Container(
                        height: 120,
                        width: double.infinity,
                        decoration: BoxDecoration(
                          border: Border.all(color: Colors.grey.shade300),
                          borderRadius: BorderRadius.circular(12),
                          color: Colors.grey.shade50,
                        ),
                        child: selectedImageBytes != null
                            ? ClipRRect(
                                borderRadius: BorderRadius.circular(12),
                                child: Image.memory(selectedImageBytes!, fit: BoxFit.contain),
                              )
                            : Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.add_photo_alternate, size: 40, color: Colors.grey.shade400),
                                  const SizedBox(height: 8),
                                  Text(
                                    isRtl ? 'أضف صورة (أيقونة مفرغة PNG)' : 'Add transparent PNG icon',
                                    style: TextStyle(color: Colors.grey.shade700, fontWeight: FontWeight.bold),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    isRtl ? 'المقاس الموصى به: 256x256 بكسل' : 'Recommended: 256x256 px',
                                    style: TextStyle(color: Colors.grey.shade500, fontSize: 11),
                                  ),
                                ],
                              ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Container(
                      decoration: BoxDecoration(
                        border: Border.all(color: Colors.grey.shade200),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: SwitchListTile(
                        title: Text(
                          isRtl ? 'حصر مقاييس' : 'Magaiees Exclusive',
                          style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                        ),
                        subtitle: Text(
                          isRtl ? 'المنتجات التي تباع عبر مقاييس فقط' : 'Hidden from merchant product listing',
                          style: const TextStyle(fontSize: 12),
                        ),
                        value: isExclusive,
                        onChanged: (val) => setDialogState(() => isExclusive = val),
                        activeThumbColor: _primaryBlue,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: isUploading ? null : () => Navigator.pop(context),
                  child: Text(isRtl ? 'إلغاء' : 'Cancel', style: const TextStyle(color: Colors.grey)),
                ),
                FilledButton.icon(
                  style: FilledButton.styleFrom(
                    backgroundColor: _primaryBlue,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  onPressed: isUploading ? null : () async {
                    if (nameArController.text.trim().isEmpty) return;
                    setDialogState(() => isUploading = true);
                    
                    try {
                      final name = nameEnController.text.trim().isNotEmpty ? nameEnController.text.trim() : nameArController.text.trim();
                      final nameAr = nameArController.text.trim();
                      
                      String? uploadedUrl;
                      if (selectedImageBytes != null) {
                        final fileName = '${DateTime.now().millisecondsSinceEpoch}.$selectedImageExt';
                        await Supabase.instance.client.storage
                            .from('category_images')
                            .uploadBinary(fileName, selectedImageBytes!);
                        uploadedUrl = Supabase.instance.client.storage
                            .from('category_images')
                            .getPublicUrl(fileName);
                      }

                      await Supabase.instance.client.from('categories').insert({
                        'name': name,
                        'name_ar': nameAr,
                        'icon_name': 'category',
                        'image_url': uploadedUrl,
                        'is_magaiees_exclusive': isExclusive,
                      });
                      
                      if (context.mounted) {
                        ref.invalidate(categoriesProvider);
                        ref.invalidate(adminCategoriesInsightsProvider);
                        Navigator.pop(context);
                        _showSnackBar(context, isRtl ? 'تم إضافة القسم بنجاح' : 'Added successfully', false);
                      }
                    } catch (e) {
                      setDialogState(() => isUploading = false);
                      if (context.mounted) _showSnackBar(context, 'Error: $e', true);
                    }
                  },
                  icon: isUploading 
                    ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)) 
                    : const Icon(Icons.check),
                  label: Text(isRtl ? 'إضافة' : 'Add'),
                ),
              ],
            );
          },
        );
      },
    );
  }

  void _showEditCategoryDialog(BuildContext context, CategoryModel category, bool isRtl) {
    final nameArController = TextEditingController(text: category.nameAr);
    final nameEnController = TextEditingController(text: category.name);
    bool isExclusive = category.isExclusiveToMagaiees;
    bool isVisible = category.isVisible;
    Uint8List? selectedImageBytes;
    String? selectedImageExt;
    bool isUploading = false;
    String? existingImageUrl = category.imageUrl;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              title: Row(
                children: [
                  Icon(Icons.edit, color: _primaryBlue),
                  const SizedBox(width: 8),
                  Text(isRtl ? 'تعديل القسم الرئيسي' : 'Edit Main Category', style: TextStyle(color: _darkSlate, fontWeight: FontWeight.bold)),
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
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        prefixIcon: const Icon(Icons.category),
                      ),
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: nameEnController,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'اسم القسم الرئيسي (بالانجليزية)' : 'Category Name (English)',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        prefixIcon: const Icon(Icons.language),
                      ),
                    ),
                    const SizedBox(height: 16),
                    InkWell(
                      onTap: () async {
                        final picker = ImagePicker();
                        final XFile? image = await picker.pickImage(source: ImageSource.gallery, imageQuality: 60, maxWidth: 800);
                        if (image != null) {
                          final bytes = await image.readAsBytes();
                          final ext = image.name.split('.').last;
                          setDialogState(() {
                            selectedImageBytes = bytes;
                            selectedImageExt = ext;
                            existingImageUrl = null;
                          });
                        }
                      },
                      borderRadius: BorderRadius.circular(12),
                      child: Container(
                        height: 120,
                        width: double.infinity,
                        decoration: BoxDecoration(
                          border: Border.all(color: Colors.grey.shade300),
                          borderRadius: BorderRadius.circular(12),
                          color: Colors.grey.shade50,
                        ),
                        child: (selectedImageBytes != null || existingImageUrl != null)
                            ? ClipRRect(
                                borderRadius: BorderRadius.circular(12),
                                child: selectedImageBytes != null
                                    ? Image.memory(selectedImageBytes!, fit: BoxFit.contain)
                                    : Image.network(existingImageUrl!, fit: BoxFit.contain, loadingBuilder: (ctx, child, progress) {
                                          if (progress == null) return child;
                                          return const Center(child: SizedBox(width: 24, height: 24, child: CircularProgressIndicator(strokeWidth: 2)));
                                        }),
                              )
                            : Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.add_photo_alternate, size: 40, color: Colors.grey.shade400),
                                  const SizedBox(height: 8),
                                  Text(
                                    isRtl ? 'تغيير الصورة (أيقونة مفرغة PNG)' : 'Change transparent PNG icon',
                                    style: TextStyle(color: Colors.grey.shade700, fontWeight: FontWeight.bold),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    isRtl ? 'المقاس الموصى به: 256x256 بكسل' : 'Recommended: 256x256 px',
                                    style: TextStyle(color: Colors.grey.shade500, fontSize: 11),
                                  ),
                                ],
                              ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Container(
                      decoration: BoxDecoration(
                        border: Border.all(color: Colors.grey.shade200),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: SwitchListTile(
                        title: Text(
                          isRtl ? 'حصر مقاييس' : 'Magaiees Exclusive',
                          style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                        ),
                        subtitle: Text(
                          isRtl ? 'المنتجات التي تباع عبر مقاييس فقط' : 'Hidden from merchant product listing',
                          style: const TextStyle(fontSize: 12),
                        ),
                        value: isExclusive,
                        onChanged: (val) => setDialogState(() => isExclusive = val),
                        activeThumbColor: _primaryBlue,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Container(
                      decoration: BoxDecoration(
                        border: Border.all(
                          color: isVisible ? const Color(0xFF10B981).withValues(alpha: 0.3) : Colors.amber.withValues(alpha: 0.6),
                        ),
                        borderRadius: BorderRadius.circular(12),
                        color: isVisible ? const Color(0xFFF0FDF4) : const Color(0xFFFFFBEB),
                      ),
                      child: SwitchListTile(
                        secondary: Icon(
                          isVisible ? Icons.visibility_rounded : Icons.visibility_off_rounded,
                          color: isVisible ? const Color(0xFF059669) : const Color(0xFFD97706),
                        ),
                        title: Text(
                          isRtl ? 'ظهور القسم في تطبيق المشترين' : 'Visible to Buyers',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.bold,
                            color: isVisible ? const Color(0xFF065F46) : const Color(0xFF92400E),
                          ),
                        ),
                        subtitle: Text(
                          isRtl 
                              ? (isVisible 
                                  ? 'ظاهر للعملاء. عند إيقاف هذا الخيار، سيتم إخفاء هذا القسم الرئيسي وكافة الأقسام الفرعية والماركات التابعة له عن المشترين.'
                                  : '⚠️ مخفي حالياً: لن يظهر هذا القسم وفروعه للمشترين حتى تقوم بإعادة تفعيله.')
                              : (isVisible 
                                  ? 'Visible to buyers. When disabled, this category and all sub-categories inside it will be hidden from buyers.'
                                  : '⚠️ Hidden from buyers: this category and its sub-categories are currently hidden from buyers.'),
                          style: TextStyle(
                            fontSize: 11,
                            color: isVisible ? Colors.grey.shade700 : Colors.amber.shade900,
                          ),
                        ),
                        value: isVisible,
                        onChanged: (val) => setDialogState(() => isVisible = val),
                        activeThumbColor: const Color(0xFF059669),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: isUploading ? null : () => Navigator.pop(context),
                  child: Text(isRtl ? 'إلغاء' : 'Cancel', style: const TextStyle(color: Colors.grey)),
                ),
                FilledButton.icon(
                  style: FilledButton.styleFrom(
                    backgroundColor: _primaryBlue,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  onPressed: isUploading ? null : () async {
                    if (nameArController.text.trim().isEmpty) return;
                    setDialogState(() => isUploading = true);
                    
                    try {
                      final name = nameEnController.text.trim().isNotEmpty ? nameEnController.text.trim() : nameArController.text.trim();
                      final nameAr = nameArController.text.trim();
                      
                      String? uploadedUrl = existingImageUrl;
                      if (selectedImageBytes != null) {
                        final fileName = '${DateTime.now().millisecondsSinceEpoch}.$selectedImageExt';
                        await Supabase.instance.client.storage
                            .from('category_images')
                            .uploadBinary(fileName, selectedImageBytes!);
                        uploadedUrl = Supabase.instance.client.storage
                            .from('category_images')
                            .getPublicUrl(fileName);
                      }

                      await Supabase.instance.client.from('categories').update({
                        'name': name,
                        'name_ar': nameAr,
                        'image_url': uploadedUrl,
                        'is_magaiees_exclusive': isExclusive,
                      }).eq('id', category.id);
                      
                      if (isVisible != category.isVisible) {
                        ref.read(categoriesProvider.notifier).toggleCategoryVisibilityLocally(category.id, isVisible);
                        if (_selectedCategory?.id == category.id) {
                          _selectedCategory = _selectedCategory!.copyWith(isVisible: isVisible);
                        }
                        await ref.read(globalSettingsProvider.notifier).toggleCategoryVisibility(category.id, isVisible);
                      }

                      if (context.mounted) {
                        ref.invalidate(categoriesProvider);
                        ref.invalidate(adminCategoriesInsightsProvider);
                        Navigator.pop(context);
                        _showSnackBar(context, isRtl ? 'تم التعديل بنجاح' : 'Updated successfully', false);
                      }
                    } catch (e) {
                      setDialogState(() => isUploading = false);
                      if (context.mounted) _showSnackBar(context, 'Error: $e', true);
                    }
                  },
                  icon: isUploading 
                    ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)) 
                    : const Icon(Icons.save),
                  label: Text(isRtl ? 'حفظ' : 'Save'),
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
    bool isSaving = false;
    Uint8List? selectedImageBytes;
    String? selectedImageExt;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              title: Row(
                children: [
                  const Icon(Icons.subdirectory_arrow_right, color: Color(0xFF059669)),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      isRtl ? 'إضافة قسم فرعي إلى (${parentCategory.nameAr})' : 'Add Sub-Category to (${parentCategory.name})',
                      style: TextStyle(color: _darkSlate, fontWeight: FontWeight.bold, fontSize: 16),
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
                        labelText: isRtl ? 'اسم القسم الفرعي (بالعربية)' : 'Sub-Category Name (Arabic)',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        prefixIcon: const Icon(Icons.branding_watermark),
                      ),
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: nameEnController,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'اسم القسم الفرعي (بالانجليزية)' : 'Sub-Category Name (English)',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        prefixIcon: const Icon(Icons.language),
                      ),
                    ),
                    const SizedBox(height: 16),
                    InkWell(
                      onTap: () async {
                        final picker = ImagePicker();
                        final XFile? image = await picker.pickImage(source: ImageSource.gallery, imageQuality: 60, maxWidth: 800);
                        if (image != null) {
                          final bytes = await image.readAsBytes();
                          final ext = image.name.split('.').last;
                          setDialogState(() {
                            selectedImageBytes = bytes;
                            selectedImageExt = ext;
                          });
                        }
                      },
                      borderRadius: BorderRadius.circular(12),
                      child: Container(
                        height: 120,
                        width: double.infinity,
                        decoration: BoxDecoration(
                          border: Border.all(color: Colors.grey.shade300),
                          borderRadius: BorderRadius.circular(12),
                          color: Colors.grey.shade50,
                        ),
                        child: selectedImageBytes != null
                            ? ClipRRect(
                                borderRadius: BorderRadius.circular(12),
                                child: Image.memory(selectedImageBytes!, fit: BoxFit.contain),
                              )
                            : Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.add_photo_alternate, size: 40, color: Colors.grey.shade400),
                                  const SizedBox(height: 8),
                                  Text(
                                    isRtl ? 'أضف صورة للقسم الفرعي' : 'Add sub-category image',
                                    style: TextStyle(color: Colors.grey.shade700, fontWeight: FontWeight.bold),
                                  ),
                                ],
                              ),
                      ),
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: isSaving ? null : () => Navigator.pop(context),
                  child: Text(isRtl ? 'إلغاء' : 'Cancel', style: const TextStyle(color: Colors.grey)),
                ),
                FilledButton.icon(
                  style: FilledButton.styleFrom(
                    backgroundColor: const Color(0xFF059669),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  onPressed: isSaving ? null : () async {
                    if (nameArController.text.trim().isEmpty) return;
                    setDialogState(() => isSaving = true);

                    try {
                      final name = nameEnController.text.trim().isNotEmpty ? nameEnController.text.trim() : nameArController.text.trim();
                      final nameAr = nameArController.text.trim();
                      
                      String? uploadedUrl;
                      if (selectedImageBytes != null) {
                        final fileName = 'sub_${DateTime.now().millisecondsSinceEpoch}.$selectedImageExt';
                        await Supabase.instance.client.storage
                            .from('category_images')
                            .uploadBinary(fileName, selectedImageBytes!);
                        uploadedUrl = Supabase.instance.client.storage
                            .from('category_images')
                            .getPublicUrl(fileName);
                      }

                      final insertedList = await Supabase.instance.client.from('subcategories').insert({
                        'category_id': parentCategory.id,
                        'name': name,
                        'name_ar': nameAr,
                        'image_url': uploadedUrl,
                      }).select();
                      
                      List<dynamic> unassigned = [];
                      try {
                        unassigned = await Supabase.instance.client
                            .from('products')
                            .select('id, name, name_ar')
                            .eq('category_id', parentCategory.id)
                            .isFilter('subcategory_id', null);
                      } catch (_) {}

                      if (context.mounted) {
                        ref.invalidate(categoriesProvider);
                        ref.invalidate(adminCategoriesInsightsProvider);
                        Navigator.pop(context);
                        _showSnackBar(context, isRtl ? 'تمت إضافة القسم الفرعي بنجاح' : 'Sub-category added', false);

                        if (unassigned.isNotEmpty && insertedList.isNotEmpty) {
                          final newSubCatId = insertedList.first['id'].toString();
                          _promptToDistributeProducts(
                            context,
                            newSubCatId,
                            nameAr,
                            List<Map<String, dynamic>>.from(unassigned),
                            isRtl,
                          );
                        }
                      }
                    } catch (e) {
                      setDialogState(() => isSaving = false);
                      if (context.mounted) _showSnackBar(context, 'Error: $e', true);
                    }
                  },
                  icon: isSaving 
                    ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)) 
                    : const Icon(Icons.check),
                  label: Text(isRtl ? 'إضافة' : 'Add'),
                ),
              ],
            );
          },
        );
      },
    );
  }

  void _showEditSubCategoryDialog(BuildContext context, SubCategoryModel subCategory, bool isRtl) {
    final nameArController = TextEditingController(text: subCategory.nameAr);
    final nameEnController = TextEditingController(text: subCategory.name);
    bool isSaving = false;
    bool isVisible = subCategory.isVisible;
    Uint8List? selectedImageBytes;
    String? selectedImageExt;
    String? existingImageUrl = subCategory.imageUrl;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              title: Row(
                children: [
                  Icon(Icons.edit, color: _primaryBlue),
                  const SizedBox(width: 8),
                  Text(isRtl ? 'تعديل القسم الفرعي' : 'Edit Sub-Category', style: TextStyle(color: _darkSlate, fontWeight: FontWeight.bold)),
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
                        labelText: isRtl ? 'اسم القسم الفرعي (بالعربية)' : 'Sub-Category Name (Arabic)',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        prefixIcon: const Icon(Icons.branding_watermark),
                      ),
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: nameEnController,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'اسم القسم الفرعي (بالانجليزية)' : 'Sub-Category Name (English)',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        prefixIcon: const Icon(Icons.language),
                      ),
                    ),
                    const SizedBox(height: 16),
                    InkWell(
                      onTap: () async {
                        final picker = ImagePicker();
                        final XFile? image = await picker.pickImage(source: ImageSource.gallery, imageQuality: 60, maxWidth: 800);
                        if (image != null) {
                          final bytes = await image.readAsBytes();
                          final ext = image.name.split('.').last;
                          setDialogState(() {
                            selectedImageBytes = bytes;
                            selectedImageExt = ext;
                            existingImageUrl = null;
                          });
                        }
                      },
                      borderRadius: BorderRadius.circular(12),
                      child: Container(
                        height: 120,
                        width: double.infinity,
                        decoration: BoxDecoration(
                          border: Border.all(color: Colors.grey.shade300),
                          borderRadius: BorderRadius.circular(12),
                          color: Colors.grey.shade50,
                        ),
                        child: (selectedImageBytes != null || (existingImageUrl != null && existingImageUrl!.isNotEmpty))
                            ? ClipRRect(
                                borderRadius: BorderRadius.circular(12),
                                child: selectedImageBytes != null
                                    ? Image.memory(selectedImageBytes!, fit: BoxFit.contain)
                                    : Image.network(existingImageUrl!, fit: BoxFit.contain, loadingBuilder: (ctx, child, progress) {
                                          if (progress == null) return child;
                                          return const Center(child: SizedBox(width: 24, height: 24, child: CircularProgressIndicator(strokeWidth: 2)));
                                        }, errorBuilder: (c, e, s) => const Icon(Icons.broken_image, color: Colors.grey, size: 40)),
                              )
                            : Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.add_photo_alternate, size: 40, color: Colors.grey.shade400),
                                  const SizedBox(height: 8),
                                  Text(
                                    isRtl ? 'تغيير الصورة' : 'Change Image',
                                    style: TextStyle(color: Colors.grey.shade700, fontWeight: FontWeight.bold),
                                  ),
                                ],
                              ),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Container(
                      decoration: BoxDecoration(
                        border: Border.all(
                          color: isVisible ? const Color(0xFF10B981).withValues(alpha: 0.3) : Colors.amber.withValues(alpha: 0.6),
                        ),
                        borderRadius: BorderRadius.circular(12),
                        color: isVisible ? const Color(0xFFF0FDF4) : const Color(0xFFFFFBEB),
                      ),
                      child: SwitchListTile(
                        secondary: Icon(
                          isVisible ? Icons.visibility_rounded : Icons.visibility_off_rounded,
                          color: isVisible ? const Color(0xFF059669) : const Color(0xFFD97706),
                        ),
                        title: Text(
                          isRtl ? 'ظهور القسم الفرعي للمشترين' : 'Visible to Buyers',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.bold,
                            color: isVisible ? const Color(0xFF065F46) : const Color(0xFF92400E),
                          ),
                        ),
                        subtitle: Text(
                          isRtl 
                              ? (isVisible 
                                  ? 'ظاهر للمشترين. يمكنك إخفاؤه مؤقتاً في حال عدم توفر أصناف له دون التأثير على بقية الأقسام.'
                                  : '⚠️ مخفي مؤقتاً: لن يظهر هذا القسم الفرعي للمشترين في شاشة الأقسام أو الفلاتر.')
                              : (isVisible 
                                  ? 'Visible to buyers. You can temporarily hide it if out of stock.'
                                  : '⚠️ Temporarily hidden from buyers in screens and filters.'),
                          style: TextStyle(
                            fontSize: 11,
                            color: isVisible ? Colors.grey.shade700 : Colors.amber.shade900,
                          ),
                        ),
                        value: isVisible,
                        onChanged: (val) => setDialogState(() => isVisible = val),
                        activeThumbColor: const Color(0xFF059669),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: isSaving ? null : () => Navigator.pop(context),
                  child: Text(isRtl ? 'إلغاء' : 'Cancel', style: const TextStyle(color: Colors.grey)),
                ),
                FilledButton.icon(
                  style: FilledButton.styleFrom(
                    backgroundColor: _primaryBlue,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  onPressed: isSaving ? null : () async {
                    if (nameArController.text.trim().isEmpty) return;
                    setDialogState(() => isSaving = true);
                    
                    try {
                      final name = nameEnController.text.trim().isNotEmpty ? nameEnController.text.trim() : nameArController.text.trim();
                      final nameAr = nameArController.text.trim();
                      
                      String? uploadedUrl = existingImageUrl;
                      if (selectedImageBytes != null) {
                        final fileName = 'sub_${DateTime.now().millisecondsSinceEpoch}.$selectedImageExt';
                        await Supabase.instance.client.storage
                            .from('category_images')
                            .uploadBinary(fileName, selectedImageBytes!);
                        uploadedUrl = Supabase.instance.client.storage
                            .from('category_images')
                            .getPublicUrl(fileName);
                      }

                      await Supabase.instance.client.from('subcategories').update({
                        'name': name,
                        'name_ar': nameAr,
                        'image_url': uploadedUrl,
                      }).eq('id', subCategory.id);
                      
                      if (isVisible != subCategory.isVisible) {
                        ref.read(categoriesProvider.notifier).toggleSubCategoryVisibilityLocally(subCategory.categoryId, subCategory.id, isVisible);
                        if (_selectedCategory?.id == subCategory.categoryId) {
                          final updatedSubs = _selectedCategory!.subCategories.map((s) {
                            if (s.id == subCategory.id) return s.copyWith(isVisible: isVisible);
                            return s;
                          }).toList();
                          _selectedCategory = _selectedCategory!.copyWith(subCategories: updatedSubs);
                        }
                        await ref.read(globalSettingsProvider.notifier).toggleSubCategoryVisibility(subCategory.id, isVisible);
                      }

                      if (context.mounted) {
                        ref.invalidate(categoriesProvider);
                        ref.invalidate(adminCategoriesInsightsProvider);
                        Navigator.pop(context);
                        _showSnackBar(context, isRtl ? 'تم التعديل بنجاح' : 'Updated successfully', false);
                      }
                    } catch (e) {
                      setDialogState(() => isSaving = false);
                      if (context.mounted) _showSnackBar(context, 'Error: $e', true);
                    }
                  },
                  icon: isSaving 
                    ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)) 
                    : const Icon(Icons.save),
                  label: Text(isRtl ? 'حفظ' : 'Save'),
                ),
              ],
            );
          },
        );
      },
    );
  }

  Future<void> _confirmDeleteCategory(BuildContext context, CategoryModel category, bool isRtl) async {
    bool isDeleting = false;
    await showDialog(
      context: context,
      barrierDismissible: false,
      builder: (c) => StatefulBuilder(
        builder: (context, setDialogState) {
          return AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            title: Row(
              children: [
                Icon(Icons.warning_amber_rounded, color: Colors.red.shade600),
                const SizedBox(width: 8),
                Text(isRtl ? 'تأكيد الحذف' : 'Confirm Delete', style: TextStyle(color: _darkSlate, fontWeight: FontWeight.bold)),
              ],
            ),
            content: Text(
              isRtl ? 'هل أنت متأكد من حذف هذا القسم بجميع محتوياته؟' : 'Are you sure you want to delete this category?',
              style: const TextStyle(fontSize: 16),
            ),
            actions: [
              TextButton(
                onPressed: isDeleting ? null : () => Navigator.pop(c), 
                child: Text(isRtl ? 'إلغاء' : 'Cancel', style: const TextStyle(color: Colors.grey))
              ),
              FilledButton(
                style: FilledButton.styleFrom(backgroundColor: Colors.red.shade600, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10))),
                onPressed: isDeleting ? null : () async {
                  setDialogState(() => isDeleting = true);
                  try {
                    // Check if products reference this category
                    final productCount = await Supabase.instance.client
                        .from('products')
                        .select('id')
                        .eq('category_id', category.id);
                    if ((productCount as List).isNotEmpty) {
                      setDialogState(() => isDeleting = false);
                      if (context.mounted) {
                        _showSnackBar(
                          context,
                          isRtl 
                              ? 'لا يمكن حذف هذا القسم لوجود ${productCount.length} منتج مرتبط به. الرجاء نقل أو حذف المنتجات أولاً.'
                              : 'Cannot delete: ${productCount.length} product(s) are linked to this category. Move or delete them first.',
                          true,
                        );
                      }
                      return;
                    }
                    await Supabase.instance.client.from('categories').delete().eq('id', category.id);
                    if (context.mounted) {
                      ref.invalidate(categoriesProvider);
                      ref.invalidate(adminCategoriesInsightsProvider);
                      Navigator.pop(c);
                      _showSnackBar(context, isRtl ? 'تم الحذف' : 'Deleted', false);
                    }
                  } catch (e) {
                    setDialogState(() => isDeleting = false);
                    if (context.mounted) _showSnackBar(context, 'Error: $e', true);
                  }
                },
                child: isDeleting 
                    ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : Text(isRtl ? 'حذف' : 'Delete'),
              ),
            ],
          );
        }
      ),
    );
  }

  Future<void> _confirmDeleteSubCategory(BuildContext context, SubCategoryModel sub, bool isRtl) async {
    bool isDeleting = false;
    await showDialog(
      context: context,
      barrierDismissible: false,
      builder: (c) => StatefulBuilder(
        builder: (context, setDialogState) {
          return AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            title: Row(
              children: [
                Icon(Icons.warning_amber_rounded, color: Colors.red.shade600),
                const SizedBox(width: 8),
                Text(isRtl ? 'تأكيد الحذف' : 'Confirm Delete', style: TextStyle(color: _darkSlate, fontWeight: FontWeight.bold)),
              ],
            ),
            content: Text(
              isRtl ? 'هل أنت متأكد من حذف هذه الماركة/القسم الفرعي؟' : 'Are you sure you want to delete this sub-category?',
              style: const TextStyle(fontSize: 16),
            ),
            actions: [
              TextButton(
                onPressed: isDeleting ? null : () => Navigator.pop(c), 
                child: Text(isRtl ? 'إلغاء' : 'Cancel', style: const TextStyle(color: Colors.grey))
              ),
              FilledButton(
                style: FilledButton.styleFrom(backgroundColor: Colors.red.shade600, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10))),
                onPressed: isDeleting ? null : () async {
                  setDialogState(() => isDeleting = true);
                  try {
                    await Supabase.instance.client.from('subcategories').delete().eq('id', sub.id);
                    if (context.mounted) {
                      ref.invalidate(categoriesProvider);
                      ref.invalidate(adminCategoriesInsightsProvider);
                      Navigator.pop(c);
                      _showSnackBar(context, isRtl ? 'تم الحذف' : 'Deleted', false);
                    }
                  } catch (e) {
                    setDialogState(() => isDeleting = false);
                    if (context.mounted) _showSnackBar(context, 'Error: $e', true);
                  }
                },
                child: isDeleting 
                    ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : Text(isRtl ? 'حذف' : 'Delete'),
              ),
            ],
          );
        }
      ),
    );
  }

  Widget _buildFinancialDashboard(AdminCategoriesInsights insights, bool isRtl) {
    final currencyFormatter = NumberFormat('#,##0.0', 'en_US');
    final topNameAr = insights.topSellingCategoryNameAr ?? '';
    final topNameEn = insights.topSellingCategoryNameEn ?? '';
    
    return LayoutBuilder(
      builder: (context, constraints) {
        final isMobile = constraints.maxWidth < 650;
        final isTablet = constraints.maxWidth >= 650 && constraints.maxWidth < 1100;

        return Padding(
          padding: const EdgeInsets.only(bottom: 24.0),
          child: GridView.count(
            crossAxisCount: isMobile ? 1 : (isTablet ? 2 : 5),
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            crossAxisSpacing: 16,
            mainAxisSpacing: 16,
            childAspectRatio: isMobile ? 3.4 : (isTablet ? 2.3 : 1.6),
            children: [
              // Card 1: Total Sales Volume
              _buildKpiCard(
                title: isRtl ? 'حجم مبيعات الأقسام' : 'Category Sales Volume',
                value: '${currencyFormatter.format(insights.totalSalesVolume)} ج.س',
                subtitle: isRtl ? 'إجمالي المبيعات المنفذة' : 'Total order transactions',
                icon: Icons.account_balance_wallet_rounded,
                accentColor: const Color(0xFF059669),
                bgColor: const Color(0xFFECFDF5),
              ),
              // Card 2: Catalog Inventory Valuation
              _buildKpiCard(
                title: isRtl ? 'قيمة المخزون المعروض' : 'Inventory Valuation',
                value: '${currencyFormatter.format(insights.totalInventoryValue)} ج.س',
                subtitle: isRtl ? 'تقييم المنتجات بالكتالوج' : 'Listed catalog products value',
                icon: Icons.inventory_2_rounded,
                accentColor: const Color(0xFF4F46E5),
                bgColor: const Color(0xFFEEF2FF),
              ),
              // Card 3: Sponsored Categories
              _buildKpiCard(
                title: isRtl ? 'أقسام برعاية معلنين' : 'Sponsored Categories',
                value: '${insights.advertiserCategoriesCount} ${isRtl ? "أقسام معلنة" : "Sponsored cats"}',
                subtitle: isRtl ? 'شراكات متاجر وموردي الإعلانات' : 'Active advertiser partners',
                icon: Icons.campaign_rounded,
                accentColor: const Color(0xFFD97706),
                bgColor: const Color(0xFFFFFBEB),
              ),
              // Card 4: Catalog Depth
              _buildKpiCard(
                title: isRtl ? 'هيكلية وتفرع الكتالوج' : 'Catalog Structure',
                value: '${insights.totalCategoriesCount} ${isRtl ? "رئيسي" : "Main"} • ${insights.totalSubcategoriesCount} ${isRtl ? "فرعي" : "Sub"}',
                subtitle: '${insights.totalProductsCount} ${isRtl ? "منتج معروض ونشط" : "active products listed"}',
                icon: Icons.category_rounded,
                accentColor: const Color(0xFF0D9488),
                bgColor: const Color(0xFFF0FDFA),
              ),
              // Card 5: Best Selling Category
              _buildKpiCard(
                title: isRtl ? 'القسم الأكثر طلباً' : 'Top Performing Category',
                value: isRtl 
                    ? (topNameAr.isNotEmpty ? topNameAr : 'لا يوجد مبيعات') 
                    : (topNameEn.isNotEmpty ? topNameEn : 'No sales'),
                subtitle: insights.topSellingCategorySales > 0 
                    ? '${currencyFormatter.format(insights.topSellingCategorySales)} ج.س'
                    : (isRtl ? 'في انتظار أول طلب' : 'Awaiting first order'),
                icon: Icons.local_fire_department_rounded,
                accentColor: const Color(0xFFE11D48),
                bgColor: const Color(0xFFFFF1F2),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildKpiCard({
    required String title,
    required String value,
    required String subtitle,
    required IconData icon,
    required Color accentColor,
    required Color bgColor,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  title,
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: Colors.grey.shade600,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: bgColor,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, color: accentColor, size: 20),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            value,
            style: const TextStyle(
              fontSize: 17,
              fontWeight: FontWeight.w800,
              color: Color(0xFF0F172A),
              letterSpacing: -0.3,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          const SizedBox(height: 4),
          Text(
            subtitle,
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w500,
              color: Colors.grey.shade500,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  Widget _buildSegmentedControl(bool isRtl, AdminCategoriesInsights? insights) {
    final allCount = insights?.totalCategoriesCount ?? 0;
    final advCount = insights?.advertiserCategoriesCount ?? 0;

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 8, offset: const Offset(0, 2)),
        ],
      ),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: SegmentedButton<int>(
          style: SegmentedButton.styleFrom(
            backgroundColor: Colors.white,
            selectedBackgroundColor: const Color(0xFFEFF6FF),
            selectedForegroundColor: _primaryBlue,
            side: BorderSide(color: Colors.grey.shade200),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          ),
          showSelectedIcon: false,
          segments: [
            ButtonSegment<int>(
              value: 0,
              label: Padding(
                padding: const EdgeInsets.symmetric(vertical: 10.0, horizontal: 4.0),
                child: Text(
                  isRtl ? '🌐 عرض الكل ($allCount)' : '🌐 View All ($allCount)',
                  style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                ),
              ),
            ),
            ButtonSegment<int>(
              value: 1,
              label: Padding(
                padding: const EdgeInsets.symmetric(vertical: 10.0, horizontal: 4.0),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.campaign_rounded, size: 16, color: Color(0xFFD97706)),
                    const SizedBox(width: 4),
                    Text(
                      isRtl ? 'رعاية معلنين ($advCount)' : 'Sponsored ($advCount)',
                      style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                    ),
                  ],
                ),
              ),
            ),
            ButtonSegment<int>(
              value: 2,
              label: Padding(
                padding: const EdgeInsets.symmetric(vertical: 10.0, horizontal: 4.0),
                child: Text(
                  isRtl ? '📌 حصر مقاييس' : '📌 Magaiees Exclusive',
                  style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                ),
              ),
            ),
            ButtonSegment<int>(
              value: 3,
              label: Padding(
                padding: const EdgeInsets.symmetric(vertical: 10.0, horizontal: 4.0),
                child: Text(
                  isRtl ? '🏪 سوق الموردين' : '🏪 Marketplace',
                  style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                ),
              ),
            ),
            ButtonSegment<int>(
              value: 4,
              label: Padding(
                padding: const EdgeInsets.symmetric(vertical: 10.0, horizontal: 4.0),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.local_fire_department_rounded, size: 16, color: Colors.deepOrange),
                    const SizedBox(width: 4),
                    Text(
                      isRtl ? 'الأكثر مبيعاً' : 'Top Selling',
                      style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                    ),
                  ],
                ),
              ),
            ),
          ],
          selected: {_selectedSegment},
          onSelectionChanged: (Set<int> newSelection) {
            setState(() => _selectedSegment = newSelection.first);
          },
        ),
      ),
    );
  }

  Widget _buildSearchBar(bool isRtl) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 8, offset: const Offset(0, 2)),
        ],
      ),
      child: TextField(
        onChanged: (v) => setState(() => _searchQuery = v.trim().toLowerCase()),
        decoration: InputDecoration(
          hintText: isRtl ? 'ابحث عن قسم أو ماركة...' : 'Search category or brand...',
          hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
          prefixIcon: Icon(Icons.search, color: Colors.grey.shade500),
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        ),
      ),
    );
  }

  Widget _buildSortDropdown(bool isRtl) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade300),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 8, offset: const Offset(0, 2)),
        ],
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<String>(
          value: _selectedSort,
          icon: const Icon(Icons.sort_rounded, color: Color(0xFF1E40AF), size: 20),
          style: TextStyle(color: _darkSlate, fontWeight: FontWeight.w600, fontSize: 13),
          onChanged: (String? newValue) {
            if (newValue != null) {
              setState(() => _selectedSort = newValue);
            }
          },
          items: [
            DropdownMenuItem(
              value: 'default',
              child: Text(isRtl ? 'الترتيب: الافتراضي' : 'Sort: Default'),
            ),
            DropdownMenuItem(
              value: 'sales_desc',
              child: Text(isRtl ? 'الأعلى مبيعاً (ج.س)' : 'Highest Sales (SDG)'),
            ),
            DropdownMenuItem(
              value: 'products_desc',
              child: Text(isRtl ? 'الأكثر منتجات' : 'Most Products'),
            ),
            DropdownMenuItem(
              value: 'subcats_desc',
              child: Text(isRtl ? 'الأكثر أقساماً فرعية' : 'Most Subcategories'),
            ),
            DropdownMenuItem(
              value: 'alpha',
              child: Text(isRtl ? 'أبجدياً (أ - ي)' : 'Alphabetical'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSubCategoryChip(BuildContext context, SubCategoryModel sub, bool isRtl) {
    return Container(
      decoration: BoxDecoration(
        color: sub.isVisible ? Colors.white : const Color(0xFFFFFBEB),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(
          color: sub.isVisible ? Colors.grey.shade300 : Colors.amber.shade300,
        ),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 4, offset: const Offset(0, 2)),
        ],
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          InkWell(
            borderRadius: isRtl 
              ? const BorderRadius.horizontal(right: Radius.circular(8))
              : const BorderRadius.horizontal(left: Radius.circular(8)),
            onTap: () => _showEditSubCategoryDialog(context, sub, isRtl),
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
              child: Row(
                children: [
                  sub.imageUrl != null && sub.imageUrl!.isNotEmpty
                      ? ClipRRect(
                          borderRadius: BorderRadius.circular(4),
                          child: Image.network(
                            sub.imageUrl!,
                            width: 20,
                            height: 20,
                            fit: BoxFit.cover,
                            errorBuilder: (c, e, s) => Icon(Icons.branding_watermark_outlined, size: 16, color: _primaryBlue.withValues(alpha: 0.7)),
                          ),
                        )
                      : Icon(Icons.branding_watermark_outlined, size: 16, color: _primaryBlue.withValues(alpha: 0.7)),
                  const SizedBox(width: 8),
                  Text(
                    isRtl ? sub.nameAr : sub.name,
                    style: TextStyle(
                      fontWeight: FontWeight.w600, 
                      color: sub.isVisible ? _darkSlate : Colors.amber.shade900, 
                      fontSize: 13,
                    ),
                  ),
                  if (!sub.isVisible) ...[
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                      decoration: BoxDecoration(
                        color: Colors.amber.shade100,
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        isRtl ? 'مخفي' : 'Hidden',
                        style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.bold, color: Colors.amber.shade900),
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),
          Container(width: 1, height: 20, color: Colors.grey.shade200),
          InkWell(
            onTap: () => _toggleSubCategoryVisibility(sub.categoryId, sub, isRtl),
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
              child: Tooltip(
                message: sub.isVisible 
                    ? (isRtl ? 'إخفاء عن المشترين' : 'Hide from buyers') 
                    : (isRtl ? 'إظهار للمشترين' : 'Show to buyers'),
                child: Icon(
                  sub.isVisible ? Icons.visibility_outlined : Icons.visibility_off_rounded,
                  size: 17,
                  color: sub.isVisible ? const Color(0xFF059669) : Colors.amber.shade800,
                ),
              ),
            ),
          ),
          Container(width: 1, height: 20, color: Colors.grey.shade200),
          InkWell(
            borderRadius: isRtl 
              ? const BorderRadius.horizontal(left: Radius.circular(8))
              : const BorderRadius.horizontal(right: Radius.circular(8)),
            onTap: () => _confirmDeleteSubCategory(context, sub, isRtl),
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
              child: Icon(Icons.delete_outline, size: 17, color: Colors.red.shade400),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildViewToggle(bool isRtl) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade300),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          InkWell(
            onTap: () => setState(() => _isGridView = false),
            borderRadius: BorderRadius.horizontal(
              left: isRtl ? Radius.zero : const Radius.circular(12),
              right: isRtl ? const Radius.circular(12) : Radius.zero,
            ),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              decoration: BoxDecoration(
                color: !_isGridView ? Colors.blue.shade50 : Colors.transparent,
                borderRadius: BorderRadius.horizontal(
                  left: isRtl ? Radius.zero : const Radius.circular(12),
                  right: isRtl ? const Radius.circular(12) : Radius.zero,
                ),
              ),
              child: Row(
                children: [
                  Icon(Icons.list_rounded, color: !_isGridView ? Colors.blue.shade700 : Colors.grey.shade600, size: 20),
                  const SizedBox(width: 8),
                  Text(isRtl ? 'صفـوف' : 'List', style: TextStyle(fontWeight: FontWeight.bold, color: !_isGridView ? Colors.blue.shade700 : Colors.grey.shade600)),
                ],
              ),
            ),
          ),
          Container(width: 1, height: 24, color: Colors.grey.shade300),
          InkWell(
            onTap: () => setState(() => _isGridView = true),
            borderRadius: BorderRadius.horizontal(
              left: isRtl ? const Radius.circular(12) : Radius.zero,
              right: isRtl ? Radius.zero : const Radius.circular(12),
            ),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              decoration: BoxDecoration(
                color: _isGridView ? Colors.blue.shade50 : Colors.transparent,
                borderRadius: BorderRadius.horizontal(
                  left: isRtl ? const Radius.circular(12) : Radius.zero,
                  right: isRtl ? Radius.zero : const Radius.circular(12),
                ),
              ),
              child: Row(
                children: [
                  Icon(Icons.grid_view_rounded, color: _isGridView ? Colors.blue.shade700 : Colors.grey.shade600, size: 20),
                  const SizedBox(width: 8),
                  Text(isRtl ? 'أيقونات' : 'Grid', style: TextStyle(fontWeight: FontWeight.bold, color: _isGridView ? Colors.blue.shade700 : Colors.grey.shade600)),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCategoryGridCard(BuildContext context, CategoryModel category, bool isRtl, CategoryAnalytics? analytics) {
    final currencyFormatter = NumberFormat('#,##0', 'en_US');
    final salesVolume = analytics?.salesVolume ?? 0;
    final productsCount = analytics?.productsCount ?? 0;
    final unitsSold = analytics?.unitsSold ?? 0;
    final hasAdvertiser = analytics?.hasAdvertiser ?? false;
    final advertiserStoreNames = analytics?.advertiserStoreNames ?? [];

    return InkWell(
      onTap: () {
        setState(() => _selectedCategory = category);
      },
      borderRadius: BorderRadius.circular(16),
      child: Container(
        decoration: BoxDecoration(
          color: category.isVisible ? Colors.white : const Color(0xFFFFFBEB),
          borderRadius: BorderRadius.circular(16),
          boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 16, offset: const Offset(0, 4))],
          border: Border.all(
            color: hasAdvertiser 
                ? const Color(0xFFF59E0B) 
                : (category.isVisible ? Colors.grey.shade200 : Colors.amber.shade300),
            width: hasAdvertiser ? 1.5 : 1.0,
          ),
        ),
        child: Stack(
          children: [
            Padding(
              padding: const EdgeInsets.all(12.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  // Top Row: Exclusive/Marketplace tag, Visibility badge & Actions
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Wrap(
                        spacing: 4,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: category.isExclusiveToMagaiees ? Colors.purple.shade50 : Colors.blue.shade50,
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              category.isExclusiveToMagaiees
                                  ? (isRtl ? 'حصري' : 'Exclusive')
                                  : (isRtl ? 'سوق' : 'Market'),
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                                color: category.isExclusiveToMagaiees ? Colors.purple.shade700 : Colors.blue.shade700,
                              ),
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                            decoration: BoxDecoration(
                              color: category.isVisible ? const Color(0xFFECFDF5) : Colors.amber.shade100,
                              borderRadius: BorderRadius.circular(6),
                              border: Border.all(
                                color: category.isVisible ? const Color(0xFFA7F3D0) : Colors.amber.shade300,
                                width: 0.8,
                              ),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(
                                  category.isVisible ? Icons.visibility_rounded : Icons.visibility_off_rounded,
                                  size: 10,
                                  color: category.isVisible ? const Color(0xFF059669) : Colors.amber.shade900,
                                ),
                                const SizedBox(width: 2.5),
                                Text(
                                  category.isVisible ? (isRtl ? 'ظاهر' : 'Visible') : (isRtl ? 'مخفي' : 'Hidden'),
                                  style: TextStyle(
                                    fontSize: 9.5,
                                    fontWeight: FontWeight.bold,
                                    color: category.isVisible ? const Color(0xFF047857) : Colors.amber.shade900,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          IconButton(
                            icon: Icon(
                              category.isVisible ? Icons.visibility_outlined : Icons.visibility_off_rounded,
                              size: 18,
                              color: category.isVisible ? const Color(0xFF059669) : Colors.amber.shade800,
                            ),
                            padding: EdgeInsets.zero,
                            constraints: const BoxConstraints(),
                            tooltip: category.isVisible
                                ? (isRtl ? 'إخفاء عن المشترين' : 'Hide from buyers')
                                : (isRtl ? 'إظهار للمشترين' : 'Show to buyers'),
                            onPressed: () => _toggleCategoryVisibility(category, isRtl),
                          ),
                          const SizedBox(width: 4),
                          PopupMenuButton<String>(
                            icon: const Icon(Icons.more_vert, color: Colors.grey, size: 18),
                            padding: EdgeInsets.zero,
                            constraints: const BoxConstraints(),
                            onSelected: (val) {
                              if (val == 'toggle_visibility') _toggleCategoryVisibility(category, isRtl);
                              if (val == 'edit') _showEditCategoryDialog(context, category, isRtl);
                              if (val == 'delete') _confirmDeleteCategory(context, category, isRtl);
                            },
                            itemBuilder: (context) => [
                              PopupMenuItem(
                                value: 'toggle_visibility',
                                child: Row(
                                  children: [
                                    Icon(
                                      category.isVisible ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                                      color: category.isVisible ? Colors.amber.shade800 : const Color(0xFF059669),
                                      size: 18,
                                    ),
                                    const SizedBox(width: 8),
                                    Text(category.isVisible ? (isRtl ? 'إخفاء عن المشترين' : 'Hide from buyers') : (isRtl ? 'إظهار للمشترين' : 'Show to buyers')),
                                  ],
                                ),
                              ),
                              PopupMenuItem(value: 'edit', child: Row(children: [const Icon(Icons.edit_outlined, color: Colors.blue, size: 18), const SizedBox(width: 8), Text(isRtl ? 'تعديل' : 'Edit')])),
                              PopupMenuItem(value: 'delete', child: Row(children: [Icon(Icons.delete_outline, color: Colors.red.shade600, size: 18), const SizedBox(width: 8), Text(isRtl ? 'حذف' : 'Delete')])),
                            ],
                          ),
                        ],
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),

                  // Category Icon/Image
                  Container(
                    width: 68,
                    height: 68,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                        color: hasAdvertiser ? const Color(0xFFFBBF24) : Colors.grey.shade200,
                      ),
                    ),
                    child: category.imageUrl != null && category.imageUrl!.isNotEmpty
                        ? ClipRRect(
                            borderRadius: BorderRadius.circular(14),
                            child: Image.network(
                              category.imageUrl!,
                              fit: BoxFit.cover,
                              loadingBuilder: (ctx, child, progress) {
                                if (progress == null) return child;
                                return const Center(child: SizedBox(width: 24, height: 24, child: CircularProgressIndicator(strokeWidth: 2)));
                              },
                              errorBuilder: (c, e, s) => Icon(Icons.image_not_supported, color: Colors.grey.shade400, size: 28),
                            ),
                          )
                        : const Icon(Icons.category, color: Colors.blue, size: 28),
                  ),
                  const SizedBox(height: 8),

                  // Title
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 4.0),
                    child: Text(
                      isRtl ? category.nameAr : category.name,
                      textAlign: TextAlign.center,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A)),
                    ),
                  ),
                  const SizedBox(height: 4),

                  // Subcategories count & products count
                  Text(
                    '${category.subCategories.length} ${isRtl ? "أقسام" : "subs"} • $productsCount ${isRtl ? "منتج" : "prod"}',
                    style: TextStyle(color: Colors.grey.shade500, fontSize: 11, fontWeight: FontWeight.w500),
                  ),

                  const Spacer(),
                  const Divider(height: 12, thickness: 0.5),

                  // Financial stats: Sales Volume & Units Sold
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            isRtl ? 'المبيعات' : 'Sales',
                            style: TextStyle(fontSize: 10, color: Colors.grey.shade500, fontWeight: FontWeight.w500),
                          ),
                          Text(
                            '${currencyFormatter.format(salesVolume)} ج.س',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w800,
                              color: salesVolume > 0 ? const Color(0xFF059669) : Colors.grey.shade600,
                            ),
                          ),
                        ],
                      ),
                      if (unitsSold > 0)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: const Color(0xFFECFDF5),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            '$unitsSold ${isRtl ? "مباع" : "sold"}',
                            style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF047857)),
                          ),
                        ),
                    ],
                  ),
                ],
              ),
            ),

            // Advertiser Sponsored Badge Overlay
            if (hasAdvertiser)
              Positioned(
                top: 8,
                left: isRtl ? 32 : null,
                right: isRtl ? null : 32,
                child: Tooltip(
                  message: isRtl 
                      ? 'رعاية معلن معتمد:\n${advertiserStoreNames.join("، ")}'
                      : 'Advertiser Sponsored:\n${advertiserStoreNames.join(", ")}',
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEF3C7),
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(color: const Color(0xFFF59E0B), width: 0.8),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.campaign_rounded, size: 12, color: Color(0xFFD97706)),
                        const SizedBox(width: 2),
                        Text(
                          isRtl ? 'رعاية معلن' : 'Sponsored',
                          style: const TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Color(0xFFB45309)),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  void _promptToDistributeProducts(BuildContext context, String newSubCatId, String newSubCatName, List<Map<String, dynamic>> products, bool isRtl) {
    List<String> selectedProductIds = products.map((p) => p['id'].toString()).toList();
    
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDlgState) {
          return AlertDialog(
            title: Text(isRtl ? 'توزيع المنتجات غير المصنفة' : 'Distribute Uncategorized Products', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
            content: SizedBox(
              width: 500,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    isRtl 
                      ? 'لقد قمت بإنشاء قسم فرعي جديد ($newSubCatName). وجدنا منتجات غير مصنفة في القسم الرئيسي. هل تريد نقلها إلى هذا القسم الجديد؟'
                      : 'You created a new subcategory ($newSubCatName). We found unassigned products in the main category. Would you like to move them?',
                  ),
                  const SizedBox(height: 16),
                  Flexible(
                    child: ListView.builder(
                      shrinkWrap: true,
                      itemCount: products.length,
                      itemBuilder: (c, i) {
                        final p = products[i];
                        final pId = p['id'].toString();
                        return CheckboxListTile(
                          value: selectedProductIds.contains(pId),
                          title: Text(isRtl ? (p['name_ar'] ?? p['name']) : p['name']),
                          onChanged: (val) {
                            setDlgState(() {
                              if (val == true) {
                                selectedProductIds.add(pId);
                              } else {
                                selectedProductIds.remove(pId);
                              }
                            });
                          },
                        );
                      },
                    ),
                  ),
                ],
              ),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: Text(isRtl ? 'تخطي' : 'Skip'),
              ),
              FilledButton(
                onPressed: selectedProductIds.isEmpty ? null : () async {
                  try {
                    await Supabase.instance.client
                      .from('products')
                      .update({'subcategory_id': newSubCatId})
                      .inFilter('id', selectedProductIds);
                    if (context.mounted) {
                      Navigator.pop(ctx);
                      _showSnackBar(context, isRtl ? 'تم تحديث المنتجات بنجاح' : 'Products updated', false);
                    }
                  } catch(e) {
                     if (context.mounted) _showSnackBar(context, 'Error: $e', true);
                  }
                },
                child: Text(isRtl ? 'نقل المحدد' : 'Move Selected'),
              ),
            ],
          );
        },
      ),
    );
  }
  
  Widget _buildSubCategoriesDetailView(BuildContext context, CategoryModel category, bool isRtl, CategoryAnalytics? analytics) {
    final currencyFormatter = NumberFormat('#,##0.0', 'en_US');
    final salesVolume = analytics?.salesVolume ?? 0;
    final inventoryValue = analytics?.inventoryValue ?? 0;
    final productsCount = analytics?.productsCount ?? 0;
    final unitsSold = analytics?.unitsSold ?? 0;
    final hasAdvertiser = analytics?.hasAdvertiser ?? false;
    final advertiserStoreNames = analytics?.advertiserStoreNames ?? [];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Header with back button
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 12, offset: const Offset(0, 2))],
            border: Border.all(color: Colors.grey.shade200),
          ),
          child: Row(
            children: [
              IconButton(
                icon: Icon(Icons.arrow_back_rounded, color: _primaryBlue),
                tooltip: isRtl ? 'رجوع' : 'Back',
                onPressed: () => setState(() => _selectedCategory = null),
              ),
              const SizedBox(width: 8),
              // Category image
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.grey.shade200),
                ),
                child: category.imageUrl != null && category.imageUrl!.isNotEmpty
                    ? ClipRRect(
                        borderRadius: BorderRadius.circular(12),
                        child: Image.network(category.imageUrl!, fit: BoxFit.cover,
                            errorBuilder: (c, e, s) => Icon(Icons.category, color: _primaryBlue, size: 24)),
                      )
                    : Icon(Icons.category, color: _primaryBlue, size: 24),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Text(
                          isRtl ? category.nameAr : category.name,
                          style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: _darkSlate),
                        ),
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: category.isVisible ? const Color(0xFFECFDF5) : Colors.amber.shade50,
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(
                              color: category.isVisible ? const Color(0xFFA7F3D0) : Colors.amber.shade300,
                            ),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                category.isVisible ? Icons.visibility_rounded : Icons.visibility_off_rounded,
                                size: 13,
                                color: category.isVisible ? const Color(0xFF059669) : Colors.amber.shade900,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                category.isVisible ? (isRtl ? 'ظاهر للمشترين' : 'Visible to Buyers') : (isRtl ? 'مخفي عن المشترين' : 'Hidden from Buyers'),
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  color: category.isVisible ? const Color(0xFF047857) : Colors.amber.shade900,
                                ),
                              ),
                            ],
                          ),
                        ),
                        if (hasAdvertiser) ...[
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: const Color(0xFFFEF3C7),
                              borderRadius: BorderRadius.circular(6),
                              border: Border.all(color: const Color(0xFFF59E0B)),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(Icons.campaign_rounded, size: 14, color: Color(0xFFD97706)),
                                const SizedBox(width: 4),
                                Text(
                                  isRtl ? 'رعاية معلن' : 'Sponsored',
                                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFFB45309)),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '${category.subCategories.length} ${isRtl ? "أقسام فرعية وماركات" : "sub-categories"} • $productsCount ${isRtl ? "منتج مسجل" : "products"}',
                      style: TextStyle(color: Colors.grey.shade500, fontSize: 13),
                    ),
                  ],
                ),
              ),
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  OutlinedButton.icon(
                    style: OutlinedButton.styleFrom(
                      foregroundColor: category.isVisible ? const Color(0xFF059669) : Colors.amber.shade900,
                      side: BorderSide(
                        color: category.isVisible ? const Color(0xFF059669).withValues(alpha: 0.4) : Colors.amber.shade400,
                      ),
                      backgroundColor: category.isVisible ? const Color(0xFFF0FDF4) : const Color(0xFFFFFBEB),
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                    onPressed: () => _toggleCategoryVisibility(category, isRtl),
                    icon: Icon(category.isVisible ? Icons.visibility_outlined : Icons.visibility_off_rounded, size: 18),
                    label: Text(
                      category.isVisible ? (isRtl ? 'إخفاء القسم' : 'Hide Category') : (isRtl ? 'إظهار القسم' : 'Show Category'),
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                  ),
                  const SizedBox(width: 12),
                  FilledButton.icon(
                    style: FilledButton.styleFrom(
                      backgroundColor: const Color(0xFF059669),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                    onPressed: () => _showAddSubCategoryDialog(context, category, isRtl),
                    icon: const Icon(Icons.add_circle_outline, size: 18),
                    label: Text(isRtl ? 'إضافة قسم فرعي' : 'Add Sub-category', style: const TextStyle(fontWeight: FontWeight.bold)),
                  ),
                  const SizedBox(width: 12),
                  OutlinedButton.icon(
                    style: OutlinedButton.styleFrom(
                      foregroundColor: _primaryBlue,
                      side: BorderSide(color: _primaryBlue.withValues(alpha: 0.3)),
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                    onPressed: () => _showEditCategoryDialog(context, category, isRtl),
                    icon: const Icon(Icons.edit_outlined, size: 18),
                    label: Text(isRtl ? 'تعديل القسم' : 'Edit Category', style: const TextStyle(fontWeight: FontWeight.bold)),
                  ),
                ],
              ),
            ],
          ),
        ),

        // Financial KPIs Row for this category
        const SizedBox(height: 16),
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: Colors.grey.shade200),
            boxShadow: [
              BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 8, offset: const Offset(0, 2)),
            ],
          ),
          child: Row(
            children: [
              Expanded(
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(color: const Color(0xFFECFDF5), borderRadius: BorderRadius.circular(10)),
                      child: const Icon(Icons.account_balance_wallet_rounded, color: Color(0xFF059669), size: 22),
                    ),
                    const SizedBox(width: 12),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(isRtl ? 'حجم المبيعات' : 'Sales Volume', style: TextStyle(fontSize: 12, color: Colors.grey.shade600, fontWeight: FontWeight.w500)),
                        const SizedBox(height: 2),
                        Text('${currencyFormatter.format(salesVolume)} ج.س', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
                      ],
                    ),
                  ],
                ),
              ),
              Container(width: 1, height: 36, color: Colors.grey.shade200),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 12.0),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(color: const Color(0xFFEEF2FF), borderRadius: BorderRadius.circular(10)),
                        child: const Icon(Icons.inventory_2_rounded, color: Color(0xFF4F46E5), size: 22),
                      ),
                      const SizedBox(width: 12),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(isRtl ? 'قيمة المعروض' : 'Inventory Value', style: TextStyle(fontSize: 12, color: Colors.grey.shade600, fontWeight: FontWeight.w500)),
                          const SizedBox(height: 2),
                          Text('${currencyFormatter.format(inventoryValue)} ج.س', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
              Container(width: 1, height: 36, color: Colors.grey.shade200),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 12.0),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(color: const Color(0xFFF0FDFA), borderRadius: BorderRadius.circular(10)),
                        child: const Icon(Icons.shopping_bag_outlined, color: Color(0xFF0D9488), size: 22),
                      ),
                      const SizedBox(width: 12),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(isRtl ? 'المنتجات النشطة' : 'Active Products', style: TextStyle(fontSize: 12, color: Colors.grey.shade600, fontWeight: FontWeight.w500)),
                          const SizedBox(height: 2),
                          Text('$productsCount ${isRtl ? "منتج" : "prods"}', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
              Container(width: 1, height: 36, color: Colors.grey.shade200),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 12.0),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(color: const Color(0xFFFFF1F2), borderRadius: BorderRadius.circular(10)),
                        child: const Icon(Icons.local_shipping_outlined, color: Color(0xFFE11D48), size: 22),
                      ),
                      const SizedBox(width: 12),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(isRtl ? 'القطع المباعة' : 'Units Sold', style: TextStyle(fontSize: 12, color: Colors.grey.shade600, fontWeight: FontWeight.w500)),
                          const SizedBox(height: 2),
                          Text('$unitsSold ${isRtl ? "قطعة" : "units"}', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),

        // Advertiser Partnership Banner if sponsored
        if (hasAdvertiser) ...[
          const SizedBox(height: 16),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: BoxDecoration(
              color: const Color(0xFFFEF3C7),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFF59E0B)),
            ),
            child: Row(
              children: [
                const Icon(Icons.campaign_rounded, color: Color(0xFFD97706), size: 24),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        isRtl ? 'قسم مدعوم بشراكات معلنين وموردين معتمدين' : 'Category Sponsored by Verified Advertisers',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF92400E)),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '${isRtl ? "المتاجر المعلنة في هذا القسم: " : "Active advertiser stores: "}${advertiserStoreNames.join(" • ")}',
                        style: const TextStyle(fontSize: 12, color: Color(0xFFB45309), fontWeight: FontWeight.w500),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],

        const SizedBox(height: 24),
        // Subcategories grid
        Expanded(
          child: category.subCategories.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.inbox_outlined, size: 72, color: Colors.grey.shade300),
                      const SizedBox(height: 16),
                      Text(
                        isRtl ? 'لا توجد أقسام فرعية بعد' : 'No sub-categories yet',
                        style: TextStyle(fontSize: 18, color: Colors.grey.shade500, fontWeight: FontWeight.w600),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        isRtl ? 'اضغط على "إضافة قسم فرعي" للبدء' : 'Click "Add Sub-category" to get started',
                        style: TextStyle(fontSize: 14, color: Colors.grey.shade400),
                      ),
                    ],
                  ),
                )
              : GridView.builder(
                  gridDelegate: const SliverGridDelegateWithMaxCrossAxisExtent(
                    maxCrossAxisExtent: 300,
                    mainAxisSpacing: 16,
                    crossAxisSpacing: 16,
                    childAspectRatio: 1.1,
                  ),
                  itemCount: category.subCategories.length,
                  itemBuilder: (context, index) {
                    final sub = category.subCategories[index];
                    return _buildSubCategoryGridItem(context, sub, isRtl);
                  },
                ),
        ),
      ],
    );
  }

  Widget _buildSubCategoryGridItem(BuildContext context, SubCategoryModel sub, bool isRtl) {
    return Container(
      decoration: BoxDecoration(
        color: sub.isVisible ? Colors.white : const Color(0xFFFFFBEB),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 12, offset: const Offset(0, 4))],
        border: Border.all(
          color: sub.isVisible ? Colors.grey.shade100 : Colors.amber.shade300,
        ),
      ),
      child: InkWell(
        onTap: () => _showEditSubCategoryDialog(context, sub, isRtl),
        borderRadius: BorderRadius.circular(16),
        child: Column(
          children: [
            // Top action bar: Visibility badge, quick toggle & more menu
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 10.0, vertical: 6.0),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: sub.isVisible ? const Color(0xFFECFDF5) : Colors.amber.shade100,
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(
                        color: sub.isVisible ? const Color(0xFFA7F3D0) : Colors.amber.shade300,
                        width: 0.8,
                      ),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          sub.isVisible ? Icons.visibility_rounded : Icons.visibility_off_rounded,
                          size: 11,
                          color: sub.isVisible ? const Color(0xFF059669) : Colors.amber.shade900,
                        ),
                        const SizedBox(width: 3),
                        Text(
                          sub.isVisible ? (isRtl ? 'ظاهر' : 'Visible') : (isRtl ? 'مخفي' : 'Hidden'),
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: sub.isVisible ? const Color(0xFF047857) : Colors.amber.shade900,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      IconButton(
                        icon: Icon(
                          sub.isVisible ? Icons.visibility_outlined : Icons.visibility_off_rounded,
                          color: sub.isVisible ? const Color(0xFF059669) : Colors.amber.shade800,
                          size: 18,
                        ),
                        padding: EdgeInsets.zero,
                        constraints: const BoxConstraints(),
                        tooltip: sub.isVisible
                            ? (isRtl ? 'إخفاء عن المشترين' : 'Hide from buyers')
                            : (isRtl ? 'إظهار للمشترين' : 'Show to buyers'),
                        onPressed: () => _toggleSubCategoryVisibility(sub.categoryId, sub, isRtl),
                      ),
                      const SizedBox(width: 4),
                      PopupMenuButton<String>(
                        icon: const Icon(Icons.more_vert, color: Colors.grey, size: 20),
                        padding: EdgeInsets.zero,
                        constraints: const BoxConstraints(),
                        onSelected: (val) {
                          if (val == 'toggle_visibility') _toggleSubCategoryVisibility(sub.categoryId, sub, isRtl);
                          if (val == 'edit') _showEditSubCategoryDialog(context, sub, isRtl);
                          if (val == 'delete') _confirmDeleteSubCategory(context, sub, isRtl);
                        },
                        itemBuilder: (context) => [
                          PopupMenuItem(
                            value: 'toggle_visibility',
                            child: Row(
                              children: [
                                Icon(
                                  sub.isVisible ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                                  color: sub.isVisible ? Colors.amber.shade800 : const Color(0xFF059669),
                                  size: 18,
                                ),
                                const SizedBox(width: 8),
                                Text(sub.isVisible ? (isRtl ? 'إخفاء عن المشترين' : 'Hide from buyers') : (isRtl ? 'إظهار للمشترين' : 'Show to buyers')),
                              ],
                            ),
                          ),
                          PopupMenuItem(value: 'edit', child: Row(children: [Icon(Icons.edit_outlined, color: _primaryBlue, size: 18), const SizedBox(width: 8), Text(isRtl ? 'تعديل' : 'Edit')])),
                          PopupMenuItem(value: 'delete', child: Row(children: [Icon(Icons.delete_outline, color: Colors.red.shade600, size: 18), const SizedBox(width: 8), Text(isRtl ? 'حذف' : 'Delete')])),
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ),
            // Image
            Expanded(
              child: Container(
                width: 80,
                height: 80,
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.grey.shade200),
                ),
                child: sub.imageUrl != null && sub.imageUrl!.isNotEmpty
                    ? ClipRRect(
                        borderRadius: BorderRadius.circular(12),
                        child: Image.network(sub.imageUrl!, fit: BoxFit.contain,
                            loadingBuilder: (ctx, child, progress) {
                              if (progress == null) return child;
                              return const Center(child: SizedBox(width: 24, height: 24, child: CircularProgressIndicator(strokeWidth: 2)));
                            },
                            errorBuilder: (c, e, s) => Icon(Icons.branding_watermark_outlined, color: Colors.grey.shade400, size: 36)),
                      )
                    : Icon(Icons.branding_watermark_outlined, color: Colors.grey.shade400, size: 36),
              ),
            ),
            const SizedBox(height: 8),
            // Name
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 8.0),
              child: Text(
                isRtl ? sub.nameAr : sub.name,
                textAlign: TextAlign.center,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: _darkSlate),
              ),
            ),
            const SizedBox(height: 8),
          ],
        ),
      ),
    );
  }

  Widget _buildCategoryCard(BuildContext context, CategoryModel category, bool isRtl, CategoryAnalytics? analytics) {
    final currencyFormatter = NumberFormat('#,##0.0', 'en_US');
    final salesVolume = analytics?.salesVolume ?? 0;
    final inventoryValue = analytics?.inventoryValue ?? 0;
    final productsCount = analytics?.productsCount ?? 0;
    final unitsSold = analytics?.unitsSold ?? 0;
    final hasAdvertiser = analytics?.hasAdvertiser ?? false;
    final advertiserStoreNames = analytics?.advertiserStoreNames ?? [];

    return Container(
      decoration: BoxDecoration(
        color: category.isVisible ? Colors.white : const Color(0xFFFFFBEB),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
        border: Border.all(
          color: hasAdvertiser 
              ? const Color(0xFFF59E0B) 
              : (category.isVisible ? Colors.grey.shade200 : Colors.amber.shade300),
          width: hasAdvertiser ? 1.5 : 1.0,
        ),
      ),
      child: Theme(
        data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
        child: ExpansionTile(
          tilePadding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
          childrenPadding: const EdgeInsets.only(left: 24, right: 24, bottom: 24, top: 0),
          leading: Container(
            width: 56,
            height: 56,
            decoration: BoxDecoration(
              color: const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: hasAdvertiser ? const Color(0xFFFBBF24) : Colors.grey.shade200),
            ),
            child: category.imageUrl != null
                ? ClipRRect(
                    borderRadius: BorderRadius.circular(12),
                    child: Image.network(category.imageUrl!, fit: BoxFit.contain, loadingBuilder: (ctx, child, progress) {
                      if (progress == null) return child;
                      return const Center(child: SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2)));
                    }, errorBuilder: (c, e, s) => Icon(Icons.image_not_supported, color: Colors.grey.shade400)),
                  )
                : Icon(Icons.category, color: _primaryBlue),
          ),
          title: Row(
            children: [
              Expanded(
                child: Row(
                  children: [
                    Text(
                      isRtl ? category.nameAr : category.name,
                      style: TextStyle(fontWeight: FontWeight.w700, fontSize: 18, color: _darkSlate),
                    ),
                    const SizedBox(width: 8),
                    if (hasAdvertiser)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFEF3C7),
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(color: const Color(0xFFF59E0B)),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.campaign_rounded, size: 12, color: Color(0xFFD97706)),
                            const SizedBox(width: 4),
                            Text(
                              isRtl ? 'رعاية معلن' : 'Sponsored',
                              style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFFB45309)),
                            ),
                          ],
                        ),
                      ),
                  ],
                ),
              ),
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  IconButton(
                    icon: Icon(
                      category.isVisible ? Icons.visibility_outlined : Icons.visibility_off_rounded,
                      color: category.isVisible ? const Color(0xFF059669) : Colors.amber.shade800,
                    ),
                    tooltip: category.isVisible
                        ? (isRtl ? 'إخفاء عن المشترين' : 'Hide from buyers')
                        : (isRtl ? 'إظهار للمشترين' : 'Show to buyers'),
                    onPressed: () => _toggleCategoryVisibility(category, isRtl),
                  ),
                  IconButton(
                    icon: const Icon(Icons.edit_outlined),
                    color: _primaryBlue,
                    tooltip: isRtl ? 'تعديل' : 'Edit',
                    onPressed: () => _showEditCategoryDialog(context, category, isRtl),
                  ),
                  IconButton(
                    icon: const Icon(Icons.delete_outline),
                    color: Colors.red.shade600,
                    tooltip: isRtl ? 'حذف' : 'Delete',
                    onPressed: () => _confirmDeleteCategory(context, category, isRtl),
                  ),
                ],
              ),
            ],
          ),
          subtitle: Padding(
            padding: const EdgeInsets.only(top: 6.0),
            child: Wrap(
              spacing: 8,
              runSpacing: 6,
              crossAxisAlignment: WrapCrossAlignment.center,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: category.isExclusiveToMagaiees ? Colors.purple.shade50 : Colors.teal.shade50,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    category.isExclusiveToMagaiees 
                      ? (isRtl ? 'حصري لمقاييس' : 'Exclusive') 
                      : (isRtl ? 'سوق الموردين' : 'Marketplace'),
                    style: TextStyle(
                      fontSize: 11, 
                      fontWeight: FontWeight.w600, 
                      color: category.isExclusiveToMagaiees ? Colors.purple.shade700 : Colors.teal.shade700
                    ),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: category.isVisible ? const Color(0xFFECFDF5) : Colors.amber.shade100,
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(
                      color: category.isVisible ? const Color(0xFFA7F3D0) : Colors.amber.shade300,
                      width: 0.8,
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        category.isVisible ? Icons.visibility_rounded : Icons.visibility_off_rounded,
                        size: 13,
                        color: category.isVisible ? const Color(0xFF059669) : Colors.amber.shade900,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        category.isVisible 
                            ? (isRtl ? 'ظاهر للمشترين' : 'Visible to Buyers') 
                            : (isRtl ? 'مخفي عن المشترين' : 'Hidden from Buyers'),
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          color: category.isVisible ? const Color(0xFF047857) : Colors.amber.shade900,
                        ),
                      ),
                    ],
                  ),
                ),
                Text(
                  '${category.subCategories.length} ${isRtl ? "أقسام فرعية" : "sub-cats"} • $productsCount ${isRtl ? "منتج" : "prods"}',
                  style: TextStyle(color: Colors.grey.shade600, fontSize: 12, fontWeight: FontWeight.w500),
                ),
                Text('•', style: TextStyle(color: Colors.grey.shade400)),
                Text(
                  '${isRtl ? "المبيعات: " : "Sales: "}${currencyFormatter.format(salesVolume)} ج.س',
                  style: TextStyle(
                    color: salesVolume > 0 ? const Color(0xFF059669) : Colors.grey.shade600,
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                if (unitsSold > 0) ...[
                  Text('•', style: TextStyle(color: Colors.grey.shade400)),
                  Text(
                    '$unitsSold ${isRtl ? "قطعة مباعة" : "units sold"}',
                    style: const TextStyle(color: Color(0xFF047857), fontSize: 12, fontWeight: FontWeight.w600),
                  ),
                ],
                if (inventoryValue > 0) ...[
                  Text('•', style: TextStyle(color: Colors.grey.shade400)),
                  Text(
                    '${isRtl ? "المخزون المعروض: " : "Inventory: "}${currencyFormatter.format(inventoryValue)} ج.س',
                    style: TextStyle(color: Colors.grey.shade500, fontSize: 11),
                  ),
                ],
                if (hasAdvertiser) ...[
                  Text('•', style: TextStyle(color: Colors.grey.shade400)),
                  Text(
                    '${isRtl ? "متاجر معلنة: " : "Advertisers: "}${advertiserStoreNames.join("، ")}',
                    style: const TextStyle(color: Color(0xFFB45309), fontSize: 11, fontWeight: FontWeight.bold),
                  ),
                ],
              ],
            ),
          ),
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.grey.shade200),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        isRtl ? 'الأقسام الفرعية والماركات' : 'Sub-categories & Brands',
                        style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: _darkSlate),
                      ),
                      TextButton.icon(
                        onPressed: () => _showAddSubCategoryDialog(context, category, isRtl),
                        icon: const Icon(Icons.add, size: 18),
                        label: Text(isRtl ? 'إضافة قسم فرعي' : 'Add Sub-category'),
                        style: TextButton.styleFrom(
                          foregroundColor: const Color(0xFF059669),
                          backgroundColor: const Color(0xFFECFDF5),
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  if (category.subCategories.isEmpty)
                    Center(
                      child: Padding(
                        padding: const EdgeInsets.all(16.0),
                        child: Text(
                          isRtl ? 'لا توجد أقسام فرعية بعد' : 'No sub-categories yet',
                          style: TextStyle(color: Colors.grey.shade500),
                        ),
                      ),
                    )
                  else
                    Wrap(
                      spacing: 12,
                      runSpacing: 12,
                      children: category.subCategories.map((sub) => _buildSubCategoryChip(context, sub, isRtl)).toList(),
                    ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final categoriesAsync = ref.watch(categoriesProvider);
    final insightsAsync = ref.watch(adminCategoriesInsightsProvider);
    final insights = insightsAsync.asData?.value;

    // Update _selectedCategory with fresh data from provider
    if (_selectedCategory != null) {
      categoriesAsync.whenData((allCategories) {
        final fresh = allCategories.where((c) => c.id == _selectedCategory!.id).toList();
        if (fresh.isNotEmpty && fresh.first != _selectedCategory) {
          WidgetsBinding.instance.addPostFrameCallback((_) {
            if (mounted) setState(() => _selectedCategory = fresh.first);
          });
        }
      });
    }

    return PopScope(
      canPop: _selectedCategory == null,
      onPopInvokedWithResult: (didPop, result) {
        if (!didPop && _selectedCategory != null) {
          setState(() => _selectedCategory = null);
        }
      },
      child: Scaffold(
        backgroundColor: const Color(0xFFF8FAFC),
        body: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 32.0, vertical: 24.0),
          child: _selectedCategory != null
              ? _buildSubCategoriesDetailView(
                  context, 
                  _selectedCategory!, 
                  isRtl, 
                  insights?.categoryAnalytics[_selectedCategory!.id],
                )
              : Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              isRtl ? 'إدارة الأقسام والماركات التجارية' : 'Categories & Brands Management',
                              style: TextStyle(fontSize: 28, fontWeight: FontWeight.w800, color: _darkSlate, letterSpacing: -0.5),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              isRtl ? 'إدارة تصنيفات الكتالوج، متابعة حجم المبيعات والمخزون، ودعم شراكات المعلنين' : 'Manage catalog hierarchy, monitor sales & inventory valuation, and support advertisers',
                              style: TextStyle(color: Colors.grey.shade600, fontSize: 14, fontWeight: FontWeight.w500),
                            ),
                          ],
                        ),
                        FilledButton.icon(
                          style: FilledButton.styleFrom(
                            backgroundColor: _primaryBlue,
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
                            elevation: 2,
                            shadowColor: _primaryBlue.withValues(alpha: 0.4),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                          onPressed: () => _showAddCategoryDialog(context, isRtl),
                          icon: const Icon(Icons.add_circle_outline, size: 20),
                          label: Text(isRtl ? 'إضافة قسم رئيسي' : 'Add Main Category', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                        ),
                      ],
                    ),
                    const SizedBox(height: 24),

                    // Financial & Catalog KPI Dashboard
                    if (insights != null) _buildFinancialDashboard(insights, isRtl),

                    // Filters and Sorting Row
                    LayoutBuilder(
                      builder: (context, constraints) {
                        final isWide = constraints.maxWidth > 1050;
                        return isWide ? Row(
                          children: [
                            Expanded(flex: 3, child: _buildSegmentedControl(isRtl, insights)),
                            const SizedBox(width: 16),
                            Expanded(flex: 2, child: _buildSearchBar(isRtl)),
                            const SizedBox(width: 16),
                            _buildSortDropdown(isRtl),
                            const SizedBox(width: 16),
                            _buildViewToggle(isRtl),
                          ],
                        ) : Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            _buildSegmentedControl(isRtl, insights),
                            const SizedBox(height: 12),
                            Row(
                              children: [
                                Expanded(child: _buildSearchBar(isRtl)),
                                const SizedBox(width: 12),
                                _buildSortDropdown(isRtl),
                                const SizedBox(width: 12),
                                _buildViewToggle(isRtl),
                              ],
                            ),
                          ],
                        );
                      }
                    ),
                    const SizedBox(height: 20),

                    // Category List or Grid View
                    Expanded(
                      child: categoriesAsync.when(
                        data: (allCategories) {
                          List<CategoryModel> segmentedCategories;
                          switch (_selectedSegment) {
                            case 0:
                              segmentedCategories = List.from(allCategories);
                              break;
                            case 1:
                              segmentedCategories = allCategories.where((c) {
                                final a = insights?.categoryAnalytics[c.id];
                                return a?.hasAdvertiser == true;
                              }).toList();
                              break;
                            case 2:
                              segmentedCategories = allCategories.where((c) => c.isExclusiveToMagaiees).toList();
                              break;
                            case 3:
                              segmentedCategories = allCategories.where((c) => !c.isExclusiveToMagaiees).toList();
                              break;
                            case 4:
                              segmentedCategories = allCategories.where((c) {
                                final a = insights?.categoryAnalytics[c.id];
                                return (a?.salesVolume ?? 0) > 0;
                              }).toList();
                              segmentedCategories.sort((a, b) {
                                final sa = insights?.categoryAnalytics[a.id]?.salesVolume ?? 0;
                                final sb = insights?.categoryAnalytics[b.id]?.salesVolume ?? 0;
                                return sb.compareTo(sa);
                              });
                              break;
                            default:
                              segmentedCategories = List.from(allCategories);
                          }

                          var filteredCategories = segmentedCategories.where((cat) {
                            if (_searchQuery.isEmpty) return true;
                            final matchMain = cat.name.toLowerCase().contains(_searchQuery) || cat.nameAr.contains(_searchQuery);
                            final matchSub = cat.subCategories.any((s) => s.name.toLowerCase().contains(_searchQuery) || s.nameAr.contains(_searchQuery));
                            return matchMain || matchSub;
                          }).toList();

                          if (_selectedSort != 'default') {
                            filteredCategories.sort((a, b) {
                              final anA = insights?.categoryAnalytics[a.id];
                              final anB = insights?.categoryAnalytics[b.id];
                              switch (_selectedSort) {
                                case 'sales_desc':
                                  return (anB?.salesVolume ?? 0).compareTo(anA?.salesVolume ?? 0);
                                case 'products_desc':
                                  return (anB?.productsCount ?? 0).compareTo(anA?.productsCount ?? 0);
                                case 'subcats_desc':
                                  return b.subCategories.length.compareTo(a.subCategories.length);
                                case 'alpha':
                                  return (isRtl ? a.nameAr : a.name).compareTo(isRtl ? b.nameAr : b.name);
                                default:
                                  return 0;
                              }
                            });
                          }

                          if (filteredCategories.isEmpty) {
                            return Center(
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.category_outlined, size: 64, color: Colors.grey.shade300),
                                  const SizedBox(height: 16),
                                  Text(isRtl ? 'لا توجد أقسام مطابقة للبحث أو التصفية' : 'No categories match the criteria', style: TextStyle(fontSize: 18, color: Colors.grey.shade500, fontWeight: FontWeight.w600)),
                                ],
                              ),
                            );
                          }

                          return _isGridView
                              ? GridView.builder(
                                  gridDelegate: const SliverGridDelegateWithMaxCrossAxisExtent(
                                    maxCrossAxisExtent: 240,
                                    mainAxisSpacing: 16,
                                    crossAxisSpacing: 16,
                                    childAspectRatio: 0.74,
                                  ),
                                  itemCount: filteredCategories.length,
                                  itemBuilder: (context, index) {
                                    final category = filteredCategories[index];
                                    final analytics = insights?.categoryAnalytics[category.id];
                                    return _buildCategoryGridCard(context, category, isRtl, analytics);
                                  },
                                )
                              : ListView.separated(
                                  itemCount: filteredCategories.length,
                                  separatorBuilder: (context, index) => const SizedBox(height: 16),
                                  itemBuilder: (context, index) {
                                    final category = filteredCategories[index];
                                    final analytics = insights?.categoryAnalytics[category.id];
                                    return _buildCategoryCard(context, category, isRtl, analytics);
                                  },
                                );
                        },
                        loading: () => Center(child: CircularProgressIndicator(color: _primaryBlue)),
                        error: (e, st) => AdminErrorView(
                          error: e,
                          stackTrace: st,
                          onRetry: () {
                            ref.invalidate(categoriesProvider);
                            ref.invalidate(adminCategoriesInsightsProvider);
                          },
                        ),
                      ),
                    ),
                  ],
                ),
        ),
      ),
    );
  }
}
