import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/category_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/sub_category_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/features/admin/presentation/screens/admin_products_screen.dart';

class AdminSubcategoriesScreen extends ConsumerStatefulWidget {
  final CategoryModel category;

  const AdminSubcategoriesScreen({super.key, required this.category});

  @override
  ConsumerState<AdminSubcategoriesScreen> createState() => _AdminSubcategoriesScreenState();
}

class _AdminSubcategoriesScreenState extends ConsumerState<AdminSubcategoriesScreen> {
  String _searchQuery = '';
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
      ),
    );
  }

  void _showAddSubCategoryDialog(BuildContext context, CategoryModel parentCategory, bool isRtl) {
    final nameArController = TextEditingController();
    final nameEnController = TextEditingController();
    Uint8List? selectedImageBytes;
    String? selectedImageExt;
    bool isUploading = false;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (c) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              title: Row(
                children: [
                  Icon(Icons.add_circle, color: _primaryBlue),
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
                        prefixIcon: const Icon(Icons.account_tree),
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
                                    isRtl ? 'أضف صورة (أيقونة مفرغة PNG)' : 'Add transparent PNG icon',
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
                  onPressed: isUploading ? null : () => Navigator.pop(c),
                  child: Text(isRtl ? 'إلغاء' : 'Cancel', style: const TextStyle(color: Colors.grey)),
                ),
                FilledButton.icon(
                  style: FilledButton.styleFrom(
                    backgroundColor: const Color(0xFF059669),
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
                        final fileName = 'sub_${DateTime.now().millisecondsSinceEpoch}.$selectedImageExt';
                        await Supabase.instance.client.storage
                            .from('category_images')
                            .uploadBinary(fileName, selectedImageBytes!);
                        uploadedUrl = Supabase.instance.client.storage
                            .from('category_images')
                            .getPublicUrl(fileName);
                      }

                      await Supabase.instance.client.from('subcategories').insert({
                        'category_id': parentCategory.id,
                        'name': name,
                        'name_ar': nameAr,
                        'image_url': uploadedUrl,
                      });
                      
                      if (context.mounted) {
                        ref.invalidate(categoriesProvider);
                        Navigator.pop(c);
                        _showSnackBar(context, isRtl ? 'تم إضافة القسم الفرعي بنجاح' : 'Added successfully', false);
                      }
                    } catch (e) {
                      setDialogState(() => isUploading = false);
                      if (context.mounted) _showSnackBar(context, 'Error: $e', true);
                    }
                  },
                  icon: isUploading 
                    ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)) 
                    : const Icon(Icons.check),
                  label: Text(isRtl ? 'إضافة قسم فرعي' : 'Add Sub-category'),
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
    Uint8List? selectedImageBytes;
    String? selectedImageExt;
    bool isUploading = false;
    String? existingImageUrl = subCategory.imageUrl;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (c) {
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
                        labelText: isRtl ? 'الاسم (بالعربية)' : 'Name (Arabic)',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        prefixIcon: const Icon(Icons.edit),
                      ),
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: nameEnController,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'الاسم (بالانجليزية)' : 'Name (English)',
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
                                    isRtl ? 'أضف صورة (أيقونة مفرغة PNG)' : 'Add transparent PNG icon',
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
                  onPressed: isUploading ? null : () => Navigator.pop(c),
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
                      
                      if (context.mounted) {
                        ref.invalidate(categoriesProvider);
                        Navigator.pop(c);
                        _showSnackBar(context, isRtl ? 'تم التعديل بنجاح' : 'Updated successfully', false);
                      }
                    } catch (e) {
                      setDialogState(() => isUploading = false);
                      if (context.mounted) _showSnackBar(context, 'Error: $e', true);
                    }
                  },
                  icon: isUploading 
                    ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)) 
                    : const Icon(Icons.check),
                  label: Text(isRtl ? 'حفظ' : 'Save'),
                ),
              ],
            );
          },
        );
      },
    );
  }

  void _confirmDeleteSubCategory(BuildContext context, SubCategoryModel sub, bool isRtl) {
    bool isDeleting = false;
    showDialog(
      context: context,
      builder: (c) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              title: Row(
                children: [
                  const Icon(Icons.warning_amber_rounded, color: Colors.orange, size: 28),
                  const SizedBox(width: 8),
                  Text(isRtl ? 'تأكيد الحذف' : 'Confirm Delete'),
                ],
              ),
              content: Text(
                isRtl 
                  ? 'هل أنت متأكد من حذف القسم الفرعي (${sub.nameAr})؟\nلا يمكن التراجع عن هذا الإجراء.' 
                  : 'Are you sure you want to delete sub-category (${sub.name})?',
              ),
              actions: [
                TextButton(
                  onPressed: isDeleting ? null : () => Navigator.pop(c),
                  child: Text(isRtl ? 'إلغاء' : 'Cancel', style: const TextStyle(color: Colors.grey)),
                ),
                FilledButton.icon(
                  style: FilledButton.styleFrom(backgroundColor: Colors.red),
                  onPressed: isDeleting ? null : () async {
                    setDialogState(() => isDeleting = true);
                    try {
                      final productCount = await Supabase.instance.client
                          .from('products')
                          .select('id')
                          .eq('sub_category_id', sub.id);
                      if (productCount.isNotEmpty) {
                        setDialogState(() => isDeleting = false);
                        if (context.mounted) {
                          Navigator.pop(c);
                          _showSnackBar(context, isRtl ? 'لا يمكن حذف القسم الفرعي لأنه يحتوي على منتجات مرتبطة به.' : 'Cannot delete sub-category because it has linked products.', true);
                        }
                        return;
                      }

                      await Supabase.instance.client.from('subcategories').delete().eq('id', sub.id);
                      if (context.mounted) {
                        ref.invalidate(categoriesProvider);
                        Navigator.pop(c);
                        _showSnackBar(context, isRtl ? 'تم الحذف بنجاح' : 'Deleted successfully', false);
                      }
                    } catch (e) {
                      setDialogState(() => isDeleting = false);
                      if (context.mounted) _showSnackBar(context, 'Error: $e', true);
                    }
                  },
                  icon: isDeleting 
                    ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)) 
                    : const Icon(Icons.delete),
                  label: Text(isRtl ? 'حذف' : 'Delete'),
                ),
              ],
            );
          },
        );
      },
    );
  }

  Widget _buildSubCategoryGridItem(BuildContext context, SubCategoryModel sub, bool isRtl) {
    return InkWell(
      onTap: () {
        Navigator.push(context, MaterialPageRoute(builder: (_) => Scaffold(
          appBar: AppBar(title: Text(isRtl ? sub.nameAr : sub.name)),
          body: AdminProductsScreen(initialSearchQuery: sub.nameAr), 
        )));
      },
      borderRadius: BorderRadius.circular(16),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.04),
              blurRadius: 16,
              offset: const Offset(0, 4),
            ),
          ],
          border: Border.all(color: Colors.grey.shade100),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Expanded(
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.grey.shade50,
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                ),
                child: sub.imageUrl != null
                    ? Image.network(sub.imageUrl!, fit: BoxFit.contain, loadingBuilder: (ctx, child, progress) {
                        if (progress == null) return child;
                        return const Center(child: SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2)));
                      }, errorBuilder: (c, e, s) => Icon(Icons.image_not_supported, color: Colors.grey.shade400))
                    : Icon(Icons.category_outlined, color: _primaryBlue, size: 48),
              ),
            ),
            Container(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  Text(
                    isRtl ? sub.nameAr : sub.name,
                    textAlign: TextAlign.center,
                    style: TextStyle(fontWeight: FontWeight.bold, color: _darkSlate, fontSize: 14),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 8),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      IconButton(
                        icon: const Icon(Icons.edit, size: 18),
                        color: _primaryBlue,
                        onPressed: () => _showEditSubCategoryDialog(context, sub, isRtl),
                        tooltip: isRtl ? 'تعديل' : 'Edit',
                        padding: EdgeInsets.zero,
                        constraints: const BoxConstraints(),
                      ),
                      const SizedBox(width: 16),
                      IconButton(
                        icon: const Icon(Icons.delete, size: 18),
                        color: Colors.red,
                        onPressed: () => _confirmDeleteSubCategory(context, sub, isRtl),
                        tooltip: isRtl ? 'حذف' : 'Delete',
                        padding: EdgeInsets.zero,
                        constraints: const BoxConstraints(),
                      ),
                    ],
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

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: IconThemeData(color: _darkSlate),
        title: Text(
          isRtl ? 'الأقسام الفرعية: ${widget.category.nameAr}' : 'Sub-categories: ${widget.category.name}',
          style: TextStyle(color: _darkSlate, fontWeight: FontWeight.bold, fontSize: 18),
        ),
        actions: [
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16.0),
            child: FilledButton.icon(
              style: FilledButton.styleFrom(
                backgroundColor: const Color(0xFF059669),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              onPressed: () => _showAddSubCategoryDialog(context, widget.category, isRtl),
              icon: const Icon(Icons.add_circle_outline, size: 18),
              label: Text(isRtl ? 'إضافة قسم فرعي' : 'Add Sub-category'),
            ),
          )
        ],
      ),
      body: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 32.0, vertical: 24.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            TextField(
              decoration: InputDecoration(
                hintText: isRtl ? 'ابحث في الأقسام الفرعية...' : 'Search sub-categories...',
                prefixIcon: const Icon(Icons.search),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
              ),
              onChanged: (val) => setState(() => _searchQuery = val.toLowerCase()),
            ),
            const SizedBox(height: 24),
            Expanded(
              child: categoriesAsync.when(
                data: (allCategories) {
                  final currentCat = allCategories.firstWhere((c) => c.id == widget.category.id, orElse: () => widget.category);
                  final filteredSubs = currentCat.subCategories.where((s) {
                    if (_searchQuery.isEmpty) return true;
                    return s.name.toLowerCase().contains(_searchQuery) || s.nameAr.contains(_searchQuery);
                  }).toList();

                  if (filteredSubs.isEmpty) {
                    return Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.category_outlined, size: 64, color: Colors.grey.shade300),
                          const SizedBox(height: 16),
                          Text(isRtl ? 'لا توجد أقسام فرعية مطابقة' : 'No sub-categories found', style: TextStyle(fontSize: 18, color: Colors.grey.shade500, fontWeight: FontWeight.w600)),
                        ],
                      ),
                    );
                  }

                  return GridView.builder(
                    gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: MediaQuery.of(context).size.width > 1200 ? 5 : (MediaQuery.of(context).size.width > 800 ? 4 : 2),
                      crossAxisSpacing: 16,
                      mainAxisSpacing: 16,
                      childAspectRatio: 0.8,
                    ),
                    itemCount: filteredSubs.length,
                    itemBuilder: (context, index) {
                      return _buildSubCategoryGridItem(context, filteredSubs[index], isRtl);
                    },
                  );
                },
                loading: () => Center(child: CircularProgressIndicator(color: _primaryBlue)),
                error: (e, st) => Center(child: Text('Error: $e', style: const TextStyle(color: Colors.red))),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
