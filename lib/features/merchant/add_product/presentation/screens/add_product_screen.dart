import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/sub_category_model.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';

class AddProductScreen extends ConsumerStatefulWidget {
  const AddProductScreen({super.key});

  @override
  ConsumerState<AddProductScreen> createState() => _AddProductScreenState();
}

class _AddProductScreenState extends ConsumerState<AddProductScreen> {
  final _formKey = GlobalKey<FormState>();

  final _nameArController = TextEditingController();
  final _nameEnController = TextEditingController();
  final _priceController = TextEditingController();
  final _priceUsdController = TextEditingController();
  final _transportFeeController = TextEditingController();
  final _offloadingFeeController = TextEditingController();

  String? _selectedCategory;
  String? _selectedSubCategory;
  String? _selectedUnit;
  bool _inStock = true;
  Uint8List? _imageBytes;
  String? _imageExt;
  double _exchangeRate = 1000.0;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _fetchStoreRate();
    });
  }

  Future<void> _fetchStoreRate() async {
    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user != null) {
      final storeRes = await Supabase.instance.client
          .from('stores')
          .select('usd_exchange_rate')
          .eq('owner_id', user.id)
          .maybeSingle();
      if (storeRes != null) {
        if (mounted) {
          setState(() {
            _exchangeRate = double.tryParse(storeRes['usd_exchange_rate']?.toString() ?? '1000') ?? 1000.0;
          });
        }
      }
    }
  }

  @override
  void dispose() {
    _nameArController.dispose();
    _nameEnController.dispose();
    _priceController.dispose();
    _priceUsdController.dispose();
    _transportFeeController.dispose();
    _offloadingFeeController.dispose();
    super.dispose();
  }

  bool _isLoading = false;

  Future<void> _saveProduct() async {
    if (_formKey.currentState!.validate()) {
      setState(() => _isLoading = true);
      
      try {
        String? finalImageUrl;
        if (_imageBytes != null && _imageExt != null) {
          final fileName = '${DateTime.now().millisecondsSinceEpoch}.$_imageExt';
          await Supabase.instance.client.storage
              .from('products_images')
              .uploadBinary(fileName, _imageBytes!);
          finalImageUrl = Supabase.instance.client.storage
              .from('products_images')
              .getPublicUrl(fileName);
        }

        final user = ref.read(authNotifierProvider).valueOrNull;
        
        // Get the actual store ID for this merchant
        String? storeId;
        if (user != null) {
          final storeRes = await Supabase.instance.client
              .from('stores')
              .select('id')
              .eq('owner_id', user.id)
              .maybeSingle();
          storeId = storeRes?['id'];
          
          if (storeId == null) {
            final newStore = await Supabase.instance.client
                .from('stores')
                .insert({'owner_id': user.id, 'name': user.name})
                .select()
                .single();
            storeId = newStore['id'];
          }
        }
        
        await Supabase.instance.client.from('products').insert({
          'store_id': storeId,
          'category_id': _selectedCategory ?? '1',
          'subcategory_id': _selectedSubCategory,
          'unit': _selectedUnit,
          'name': _nameEnController.text,
          'name_ar': _nameArController.text,
          'price_sdg': double.tryParse(_priceController.text) ?? 0.0,
          'price_usd': double.tryParse(_priceUsdController.text) ?? 0.0,
          'stock': _inStock ? 100 : 0,
          'images': finalImageUrl != null ? [finalImageUrl] : [],
          'transport_fee': double.tryParse(_transportFeeController.text) ?? 0.0,
          'offloading_fee': double.tryParse(_offloadingFeeController.text) ?? 0.0,
        });

        ref.invalidate(productsProvider);

        if (mounted) {
          final isRtl = Directionality.of(context) == TextDirection.rtl;
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(isRtl ? 'تمت إضافة الصنف والمنتج بنجاح!' : 'Product added successfully!'),
              backgroundColor: Colors.green.shade700,
            ),
          );
          _nameArController.clear();
          _nameEnController.clear();
          _priceController.clear();
          _priceUsdController.clear();
          _transportFeeController.clear();
          _offloadingFeeController.clear();
          setState(() {
            _selectedSubCategory = null;
            _imageBytes = null;
            _imageExt = null;
          });
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Error: $e')),
          );
        }
      } finally {
        if (mounted) {
          setState(() => _isLoading = false);
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final categoriesAsync = ref.watch(categoriesProvider);
    final allCategories = categoriesAsync.valueOrNull ?? [];
    final categories = allCategories.where((c) => !c.isExclusiveToMagaiees).toList();

    final currentCat = categories.cast<dynamic>().firstWhere(
      (c) => c.id == _selectedCategory,
      orElse: () => null,
    );
    final List<SubCategoryModel> subCategories = currentCat?.subCategories ?? [];

    final List<Map<String, String>> unitOptions = [
      {'id': 'ton', 'nameEn': 'Ton', 'nameAr': 'طن'},
      {'id': 'bag', 'nameEn': 'Bag (50kg)', 'nameAr': 'كيس (50 كجم)'},
      {'id': 'm2', 'nameEn': 'Square Meter (m²)', 'nameAr': 'متر مربع (م²)'},
      {'id': 'm3', 'nameEn': 'Cubic Meter (m³)', 'nameAr': 'متر مكعب (م³)'},
      {'id': 'piece', 'nameEn': 'Piece / Item', 'nameAr': 'حبة / قطعة'},
      {'id': 'bar', 'nameEn': 'Length / Bar (6m / 12m)', 'nameAr': 'عود / سيخ'},
      {'id': 'sheet', 'nameEn': 'Sheet / Panel', 'nameAr': 'لوح'},
      {'id': 'bucket', 'nameEn': 'Drum / Bucket (18L)', 'nameAr': 'برميل (18 لتر)'},
      {'id': 'gallon', 'nameEn': 'Gallon (4L)', 'nameAr': 'جالون (4 لتر)'},
      {'id': 'thousand', 'nameEn': 'Thousand Pcs (1000)', 'nameAr': 'ألف حبة'},
      {'id': 'truck', 'nameEn': 'Truck Load', 'nameAr': 'لوري / قلاب'},
    ];

    return Scaffold(
      appBar: AppBar(
        title: Text(isRtl ? 'إضافة صنف ومنتج جديد' : 'Add New Item / Product'),
      ),
      body: Form(
        key: _formKey,
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Text(
                isRtl ? 'التصنيف والهيكلية' : 'Category & Brand Classification',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
              ),
              const SizedBox(height: 16),

              DropdownButtonFormField<String>(
                decoration: InputDecoration(
                  labelText: isRtl ? 'القسم الرئيسي' : 'Main Category',
                  border: const OutlineInputBorder(),
                  prefixIcon: const Icon(Icons.category),
                ),
                value: _selectedCategory,
                items: categories.map((cat) {
                  return DropdownMenuItem<String>(
                    value: cat.id,
                    child: Text(isRtl ? cat.nameAr : cat.name),
                  );
                }).toList(),
                onChanged: (val) {
                  setState(() {
                    _selectedCategory = val;
                    _selectedSubCategory = null; 
                  });
                },
                validator: (value) => value == null ? (isRtl ? 'الرجاء اختيار القسم الرئيسي' : 'Required field') : null,
              ),
              const SizedBox(height: 16),

              DropdownButtonFormField<String>(
                decoration: InputDecoration(
                  labelText: isRtl ? 'القسم الفرعي / الماركة / النوع' : 'Sub-Category / Brand / Line',
                  border: const OutlineInputBorder(),
                  prefixIcon: const Icon(Icons.branding_watermark),
                  helperText: _selectedCategory == null 
                      ? (isRtl ? 'اختر القسم الرئيسي أولاً لتحميل الماركات' : 'Select main category first')
                      : null,
                ),
                value: _selectedSubCategory,
                items: subCategories.map((sub) {
                  return DropdownMenuItem<String>(
                    value: sub.id,
                    child: Text(isRtl ? sub.nameAr : sub.name),
                  );
                }).toList(),
                onChanged: _selectedCategory == null ? null : (val) {
                  setState(() {
                    _selectedSubCategory = val;
                  });
                },
                validator: (value) => subCategories.isNotEmpty && value == null 
                    ? (isRtl ? 'الرجاء اختيار الماركة / القسم الفرعي' : 'Required field') 
                    : null,
              ),
              const SizedBox(height: 24),

              Text(
                isRtl ? 'بيانات الصنف والمواصفات' : 'Item Specifications & Name',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
              ),
              const SizedBox(height: 16),

              TextFormField(
                controller: _nameArController,
                decoration: InputDecoration(
                  labelText: isRtl ? 'اسم الصنف والمقاس (عربي)' : 'Item Name & Size (Arabic)',
                  hintText: isRtl ? 'مثلاً: حديد تسليح 16 ملم مشرشر أو أسمنت بورتلاندي 50 كجم' : 'e.g. Rebar 16mm',
                  border: const OutlineInputBorder(),
                  prefixIcon: const Icon(Icons.edit_note),
                ),
                validator: (value) =>
                    value == null || value.isEmpty ? isRtl ? 'حقل مطلوب' : 'Required field' : null,
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _nameEnController,
                decoration: InputDecoration(
                  labelText: isRtl ? 'اسم الصنف (إنجليزي)' : 'Item Name (English)',
                  border: const OutlineInputBorder(),
                  prefixIcon: const Icon(Icons.language),
                ),
                validator: (value) =>
                    value == null || value.isEmpty ? isRtl ? 'حقل مطلوب' : 'Required field' : null,
              ),
              const SizedBox(height: 24),

              Text(
                isRtl ? 'صور الصنف' : 'Product Images',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
              ),
              const SizedBox(height: 16),
              InkWell(
                onTap: () async {
                  try {
                    final picker = ImagePicker();
                    final pickedFile = await picker.pickImage(source: ImageSource.gallery, imageQuality: 60, maxWidth: 800);
                    
                    if (pickedFile != null) {
                      final bytes = await pickedFile.readAsBytes();
                      final ext = pickedFile.name.split('.').last;
                      setState(() {
                        _imageBytes = bytes;
                        _imageExt = ext;
                      });
                    }
                  } catch (e) {
                    final isRtl = Directionality.of(context) == TextDirection.rtl;
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text(isRtl ? 'فشل اختيار الصورة: $e' : 'Failed to pick image'),
                        backgroundColor: Colors.red,
                      ),
                    );
                  }
                },
                borderRadius: BorderRadius.circular(12),
                child: Container(
                  width: double.infinity,
                  height: 160,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade50,
                    border: Border.all(color: Colors.grey.shade300, width: 2),
                    borderRadius: BorderRadius.circular(12),
                    image: _imageBytes != null 
                        ? DecorationImage(
                            image: MemoryImage(_imageBytes!),
                            fit: BoxFit.cover,
                          ) 
                        : null,
                  ),
                  child: _imageBytes == null ? Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.add_photo_alternate_outlined, size: 48, color: Colors.blue.shade400),
                      const SizedBox(height: 12),
                      Text(
                        isRtl ? 'انقر لرفع صورة المنتج' : 'Click to upload product image',
                        style: TextStyle(
                          color: Colors.grey.shade600,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        isRtl ? 'صيغة PNG أو JPG' : 'PNG or JPG format',
                        style: TextStyle(
                          color: Colors.grey.shade500,
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ) : null,
                ),
              ),
              const SizedBox(height: 24),

              Text(
                isRtl ? 'التسعير الذكي (${_exchangeRate.toStringAsFixed(0)} ج.س للدولار)' : 'Smart Pricing (${_exchangeRate.toStringAsFixed(0)} SDG/\$)',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
              ),
              const SizedBox(height: 16),
              
              DropdownButtonFormField<String>(
                decoration: InputDecoration(
                  labelText: isRtl ? 'وحدة البيع' : 'Sale Unit',
                  border: const OutlineInputBorder(),
                ),
                value: _selectedUnit,
                items: unitOptions.map((unit) {
                  return DropdownMenuItem(
                    value: unit['id'],
                    child: Text(isRtl ? unit['nameAr']! : unit['nameEn']!),
                  );
                }).toList(),
                onChanged: (val) {
                  setState(() {
                    _selectedUnit = val;
                  });
                },
                validator: (value) => value == null ? isRtl ? 'حقل مطلوب' : 'Required field' : null,
              ),
              const SizedBox(height: 16),
              
              Row(
                children: [
                  Expanded(
                    child: TextFormField(
                      controller: _priceUsdController,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true),
                      decoration: InputDecoration(
                        labelText: isRtl ? 'السعر بالدولار (\$)' : 'Price (USD)',
                        border: const OutlineInputBorder(),
                        prefixIcon: const Icon(Icons.attach_money),
                      ),
                      onChanged: (val) {
                        final usd = double.tryParse(val) ?? 0.0;
                        _priceController.text = (usd * _exchangeRate).toStringAsFixed(0);
                      },
                      validator: (value) =>
                          value == null || value.isEmpty ? isRtl ? 'حقل مطلوب' : 'Required field' : null,
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: TextFormField(
                      controller: _priceController,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true),
                      decoration: InputDecoration(
                        labelText: isRtl ? 'السعر (ج.س)' : 'Price (SDG)',
                        border: const OutlineInputBorder(),
                        prefixIcon: const Icon(Icons.money),
                      ),
                      onChanged: (val) {
                        final sdg = double.tryParse(val) ?? 0.0;
                        if (_exchangeRate > 0) {
                          _priceUsdController.text = (sdg / _exchangeRate).toStringAsFixed(2);
                        }
                      },
                      validator: (value) =>
                          value == null || value.isEmpty ? isRtl ? 'حقل مطلوب' : 'Required field' : null,
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 24),
              Text(
                isRtl ? 'تكاليف إضافية (اختياري)' : 'Additional Logistics',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: TextFormField(
                      controller: _transportFeeController,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true),
                      decoration: InputDecoration(
                        labelText: isRtl ? 'رسوم الترحيل' : 'Transport Fee',
                        border: const OutlineInputBorder(),
                      ),
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: TextFormField(
                      controller: _offloadingFeeController,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true),
                      decoration: InputDecoration(
                        labelText: isRtl ? 'رسوم التنزيل/العتالة' : 'Offloading Fee',
                        border: const OutlineInputBorder(),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              SwitchListTile(
                title: Text(isRtl ? 'متوفر في المخزون حالياً' : 'In Stock'),
                subtitle: Text(
                  isRtl
                      ? 'سيظهر للمشترين بعلامة خضراء وجاهز للطلب الفوري'
                      : 'Visible to buyers as ready for instant order',
                ),
                value: _inStock,
                onChanged: (val) {
                  setState(() {
                    _inStock = val;
                  });
                },
              ),
              const SizedBox(height: 24),
              FilledButton.icon(
                style: FilledButton.styleFrom(
                  padding: const EdgeInsets.all(16),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                onPressed: _isLoading ? null : _saveProduct,
                icon: _isLoading
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                      )
                    : const Icon(Icons.save),
                label: Text(
                  _isLoading
                      ? (isRtl ? 'جاري الحفظ...' : 'Saving...')
                      : (isRtl ? 'حفظ ونشر الصنف في المنصة' : 'Publish Item to Platform'),
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
