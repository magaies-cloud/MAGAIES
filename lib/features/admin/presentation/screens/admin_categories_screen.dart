import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/category_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/sub_category_model.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

class AdminCategoriesScreen extends ConsumerStatefulWidget {
  const AdminCategoriesScreen({super.key});

  @override
  ConsumerState<AdminCategoriesScreen> createState() => _AdminCategoriesScreenState();
}

class _AdminCategoriesScreenState extends ConsumerState<AdminCategoriesScreen> {
  String _searchQuery = '';
  int _selectedSegment = 0;

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

  void _showAddCategoryDialog(BuildContext context, bool isRtl) {
    final nameArController = TextEditingController();
    final nameEnController = TextEditingController();
    bool isExclusive = _selectedSegment == 0;
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
                        activeColor: _primaryBlue,
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
                        activeColor: _primaryBlue,
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
                      
                      if (context.mounted) {
                        ref.invalidate(categoriesProvider);
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

                      await Supabase.instance.client.from('subcategories').insert({
                        'category_id': parentCategory.id,
                        'name': name,
                        'name_ar': nameAr,
                      });
                      
                      if (context.mounted) {
                        ref.invalidate(categoriesProvider);
                        Navigator.pop(context);
                        _showSnackBar(context, isRtl ? 'تمت إضافة القسم الفرعي بنجاح' : 'Sub-category added', false);
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

                      await Supabase.instance.client.from('subcategories').update({
                        'name': name,
                        'name_ar': nameAr,
                      }).eq('id', subCategory.id);
                      
                      if (context.mounted) {
                        ref.invalidate(categoriesProvider);
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

  Widget _buildSegmentedControl(bool isRtl) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(color: Colors.black.withOpacity(0.03), blurRadius: 8, offset: const Offset(0, 2)),
        ],
      ),
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
              padding: const EdgeInsets.symmetric(vertical: 12.0),
              child: Text(isRtl ? '📌 حصرياً لمقاييس' : '📌 Magaiees Exclusive', style: const TextStyle(fontWeight: FontWeight.w600)),
            ),
          ),
          ButtonSegment<int>(
            value: 1,
            label: Padding(
              padding: const EdgeInsets.symmetric(vertical: 12.0),
              child: Text(isRtl ? '🏪 سوق الموردين' : '🏪 Merchant Marketplace', style: const TextStyle(fontWeight: FontWeight.w600)),
            ),
          ),
          ButtonSegment<int>(
            value: 2,
            label: Padding(
              padding: const EdgeInsets.symmetric(vertical: 12.0),
              child: Text(isRtl ? '🌐 عرض الكل' : '🌐 View All', style: const TextStyle(fontWeight: FontWeight.w600)),
            ),
          ),
        ],
        selected: {_selectedSegment},
        onSelectionChanged: (Set<int> newSelection) {
          setState(() => _selectedSegment = newSelection.first);
        },
      ),
    );
  }

  Widget _buildSearchBar(bool isRtl) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(color: Colors.black.withOpacity(0.03), blurRadius: 8, offset: const Offset(0, 2)),
        ],
      ),
      child: TextField(
        onChanged: (v) => setState(() => _searchQuery = v.trim().toLowerCase()),
        decoration: InputDecoration(
          hintText: isRtl ? 'ابحث عن قسم...' : 'Search categories...',
          hintStyle: TextStyle(color: Colors.grey.shade400),
          prefixIcon: Icon(Icons.search, color: Colors.grey.shade500),
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        ),
      ),
    );
  }

  Widget _buildSubCategoryChip(BuildContext context, SubCategoryModel sub, bool isRtl) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: Colors.grey.shade300),
        boxShadow: [
          BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 4, offset: const Offset(0, 2)),
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
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
              child: Row(
                children: [
                  Icon(Icons.branding_watermark_outlined, size: 16, color: _primaryBlue.withOpacity(0.7)),
                  const SizedBox(width: 8),
                  Text(
                    isRtl ? sub.nameAr : sub.name,
                    style: TextStyle(fontWeight: FontWeight.w600, color: _darkSlate, fontSize: 14),
                  ),
                ],
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
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
              child: Icon(Icons.delete_outline, size: 18, color: Colors.red.shade400),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCategoryCard(BuildContext context, CategoryModel category, bool isRtl) {
    return Container(
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
              border: Border.all(color: Colors.grey.shade200),
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
                child: Text(
                  isRtl ? category.nameAr : category.name,
                  style: TextStyle(fontWeight: FontWeight.w700, fontSize: 18, color: _darkSlate),
                ),
              ),
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
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
            padding: const EdgeInsets.only(top: 4.0),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: category.isExclusiveToMagaiees ? Colors.purple.shade50 : Colors.teal.shade50,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    category.isExclusiveToMagaiees 
                      ? (isRtl ? 'حصري' : 'Exclusive') 
                      : (isRtl ? 'عام' : 'Public'),
                    style: TextStyle(
                      fontSize: 12, 
                      fontWeight: FontWeight.w600, 
                      color: category.isExclusiveToMagaiees ? Colors.purple.shade700 : Colors.teal.shade700
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Text(
                  isRtl ? '${category.subCategories.length} أقسام فرعية' : '${category.subCategories.length} sub-categories',
                  style: TextStyle(color: Colors.grey.shade500, fontSize: 13, fontWeight: FontWeight.w500),
                ),
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

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 32.0, vertical: 24.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      isRtl ? 'إدارة الأقسام والماركات' : 'Categories Management',
                      style: TextStyle(fontSize: 28, fontWeight: FontWeight.w800, color: _darkSlate, letterSpacing: -0.5),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      isRtl ? 'إضافة أقسام وتصنيفات جديدة وتحديد أين تظهر' : 'Add categories and control where they appear',
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
                    shadowColor: _primaryBlue.withOpacity(0.4),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: () => _showAddCategoryDialog(context, isRtl),
                  icon: const Icon(Icons.add_circle_outline, size: 20),
                  label: Text(isRtl ? 'إضافة قسم رئيسي' : 'Add Main Category', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                ),
              ],
            ),
            const SizedBox(height: 32),

            LayoutBuilder(
              builder: (context, constraints) {
                final isWide = constraints.maxWidth > 800;
                return isWide ? Row(
                  children: [
                    Expanded(flex: 2, child: _buildSegmentedControl(isRtl)),
                    const SizedBox(width: 24),
                    Expanded(flex: 1, child: _buildSearchBar(isRtl)),
                  ],
                ) : Column(
                  children: [
                    _buildSegmentedControl(isRtl),
                    const SizedBox(height: 16),
                    _buildSearchBar(isRtl),
                  ],
                );
              }
            ),
            const SizedBox(height: 24),

            Expanded(
              child: categoriesAsync.when(
                data: (allCategories) {
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

                  final filteredCategories = segmentedCategories.where((cat) {
                    if (_searchQuery.isEmpty) return true;
                    final matchMain = cat.name.toLowerCase().contains(_searchQuery) || cat.nameAr.contains(_searchQuery);
                    final matchSub = cat.subCategories.any((s) => s.name.toLowerCase().contains(_searchQuery) || s.nameAr.contains(_searchQuery));
                    return matchMain || matchSub;
                  }).toList();

                  if (filteredCategories.isEmpty) {
                    return Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.category_outlined, size: 64, color: Colors.grey.shade300),
                          const SizedBox(height: 16),
                          Text(isRtl ? 'لا توجد أقسام هنا' : 'No categories found', style: TextStyle(fontSize: 18, color: Colors.grey.shade500, fontWeight: FontWeight.w600)),
                        ],
                      ),
                    );
                  }

                  return ListView.separated(
                    itemCount: filteredCategories.length,
                    separatorBuilder: (context, index) => const SizedBox(height: 16),
                    itemBuilder: (context, index) {
                      final category = filteredCategories[index];
                      return _buildCategoryCard(context, category, isRtl);
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
