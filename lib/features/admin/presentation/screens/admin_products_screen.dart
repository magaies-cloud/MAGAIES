import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/product_card_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/category_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/sub_category_model.dart';
import 'package:magaiees_marketplace/features/admin/pricing/application/pricing_provider.dart';
import 'package:magaiees_marketplace/features/admin/presentation/widgets/admin_error_view.dart';
import 'package:magaiees_marketplace/features/admin/products/application/admin_products_provider.dart';
import 'package:magaiees_marketplace/core/utils/unit_formatter.dart';

// --- Theme Colors ---
const Color _kDarkNavy = Color(0xFF0F172A);
const Color _kRoyalBlue = Color(0xFF2563EB);
const Color _kEmerald = Color(0xFF10B981);
const Color _kEmeraldDark = Color(0xFF059669);
const Color _kAmberDark = Color(0xFFD97706);
const Color _kCrimson = Color(0xFFEF4444);
const Color _kPurple = Color(0xFF8B5CF6);
const Color _kBgLight = Color(0xFFF8FAFC);

class _StoreFilterOption {
  final String id;
  final String name;
  final int count;
  const _StoreFilterOption({required this.id, required this.name, required this.count});
}

class AdminProductsScreen extends ConsumerStatefulWidget {
  final String? initialSearchQuery;
  const AdminProductsScreen({super.key, this.initialSearchQuery});

  @override
  ConsumerState<AdminProductsScreen> createState() => _AdminProductsScreenState();
}

class _AdminProductsScreenState extends ConsumerState<AdminProductsScreen> {
  int _selectedSegment = 0; // 0: All, 1: Exclusive, 2: Merchants/Advertisers, 3: Top Selling
  String _sortBy = 'newest'; // newest, sales_desc, units_desc, valuation_desc, price_asc, price_desc, in_stock, name_asc

  late double _usdRate;
  late final TextEditingController _usdRateCtrl;
  final _percentCtrl = TextEditingController(text: '0');
  final _searchCtrl = TextEditingController();
  String _searchQuery = '';
  bool _isGridView = true;
  String? _selectedCategoryId;
  String? _selectedSubCategoryId;
  String? _selectedStoreId; // Filter by specific partner/advertiser store

  @override
  void initState() {
    super.initState();
    _searchQuery = widget.initialSearchQuery ?? '';
    if (widget.initialSearchQuery != null) {
      _searchCtrl.text = widget.initialSearchQuery!;
    }
    final initialRate = ref.read(pricingProvider).usdExchangeRate;
    _usdRate = initialRate;
    _usdRateCtrl = TextEditingController(text: initialRate.toStringAsFixed(0));
  }

  @override
  void dispose() {
    _usdRateCtrl.dispose();
    _percentCtrl.dispose();
    _searchCtrl.dispose();
    super.dispose();
  }

  bool _isExclusive(ProductCardModel p) => p.isMagaieesExclusive;
  String _fmtSdg(double v) => NumberFormat.decimalPattern('ar').format(v);

  List<ProductCardModel> _filterAndSort(
    List<ProductCardModel> all,
    AdminProductsInsights? insights,
  ) {
    Iterable<ProductCardModel> s = all;

    // Segment Filter: 0 = All, 1 = Exclusive, 2 = Merchants/Advertisers, 3 = Top Selling
    if (_selectedSegment == 1) {
      s = s.where(_isExclusive);
    } else if (_selectedSegment == 2) {
      s = s.where((p) => !_isExclusive(p));
    } else if (_selectedSegment == 3) {
      s = s.where((p) {
        final a = insights?.forProduct(p.id);
        return (a?.salesVolume ?? 0) > 0;
      });
    }

    // Specific Store / Advertiser Filter
    if (_selectedStoreId != null) {
      s = s.where((p) {
        final key = p.merchantId.trim().isNotEmpty
            ? p.merchantId.trim()
            : (p.storeNameAr.trim().isNotEmpty ? p.storeNameAr.trim() : 'other');
        return key == _selectedStoreId ||
            p.merchantId == _selectedStoreId ||
            p.storeNameAr == _selectedStoreId ||
            p.storeName == _selectedStoreId;
      });
    }

    // Category & Subcategory Filter
    if (_selectedCategoryId != null) {
      s = s.where((p) => p.categoryId == _selectedCategoryId);
    }
    if (_selectedSubCategoryId != null) {
      if (_selectedSubCategoryId == '__has_sub__') {
        s = s.where((p) => p.subCategoryId != null && p.subCategoryId!.trim().isNotEmpty);
      } else {
        s = s.where((p) => p.subCategoryId == _selectedSubCategoryId);
      }
    }

    // Search Query (by product name, category, subcategory, brand, store/advertiser or ID)
    if (_searchQuery.isNotEmpty) {
      final q = _searchQuery.toLowerCase();
      s = s.where((p) =>
          p.nameAr.toLowerCase().contains(q) ||
          p.name.toLowerCase().contains(q) ||
          p.categoryNameAr.toLowerCase().contains(q) ||
          p.categoryName.toLowerCase().contains(q) ||
          (p.subCategoryNameAr ?? '').toLowerCase().contains(q) ||
          (p.subCategoryName ?? '').toLowerCase().contains(q) ||
          p.storeNameAr.toLowerCase().contains(q) ||
          p.storeName.toLowerCase().contains(q) ||
          p.id.toLowerCase().contains(q));
    }

    final list = s.toList();

    // Sorting
    switch (_sortBy) {
      case 'sales_desc':
        list.sort((a, b) {
          final sA = insights?.forProduct(a.id).salesVolume ?? 0;
          final sB = insights?.forProduct(b.id).salesVolume ?? 0;
          return sB.compareTo(sA);
        });
        break;
      case 'units_desc':
        list.sort((a, b) {
          final uA = insights?.forProduct(a.id).unitsSold ?? 0;
          final uB = insights?.forProduct(b.id).unitsSold ?? 0;
          return uB.compareTo(uA);
        });
        break;
      case 'valuation_desc':
        list.sort((a, b) {
          final vA = a.pricePerTon * a.stockQuantity;
          final vB = b.pricePerTon * b.stockQuantity;
          return vB.compareTo(vA);
        });
        break;
      case 'price_asc':
        list.sort((a, b) => a.pricePerTon.compareTo(b.pricePerTon));
        break;
      case 'price_desc':
        list.sort((a, b) => b.pricePerTon.compareTo(a.pricePerTon));
        break;
      case 'in_stock':
        list.sort((a, b) => (b.inStock ? 1 : 0).compareTo(a.inStock ? 1 : 0));
        break;
      case 'name_asc':
        list.sort((a, b) => a.nameAr.compareTo(b.nameAr));
        break;
      case 'newest':
      default:
        break;
    }

    return list;
  }

  void _onUpdateRate() {
    final rate = double.tryParse(_usdRateCtrl.text);
    if (rate == null || rate <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('الرجاء إدخال سعر صرف صحيح وموجب'), backgroundColor: Colors.red),
      );
      return;
    }
    setState(() => _usdRate = rate);

    ref.read(pricingProvider.notifier).updateUsdRate(rate);
    ref.read(productsProvider.notifier).applyGlobalPricing(rate);

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.check_circle, color: Colors.white),
            const SizedBox(width: 10),
            Text('تم تحديث سعر الصرف المركزي: 1 USD = ${_fmtSdg(rate)} SDG — تم ترحيل الأسعار فوراً!', style: const TextStyle(fontWeight: FontWeight.bold)),
          ],
        ),
        backgroundColor: _kRoyalBlue,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  void _onApplyPercent() {
    final pct = double.tryParse(_percentCtrl.text);
    if (pct == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('الرجاء إدخال نسبة مئوية صحيحة'), backgroundColor: Colors.red),
      );
      return;
    }

    ref.read(productsProvider.notifier).applyGlobalPricing(_usdRate, percentOffset: pct);

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.trending_up_rounded, color: Colors.white),
            const SizedBox(width: 10),
            Text('تم تطبيق تعديل ${pct >= 0 ? "زيادة" : "خفض"} بنسبة ${pct.abs()}% على كافة الأصناف المعروضة.'),
          ],
        ),
        backgroundColor: _kEmerald,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  void _toggleStock(ProductCardModel product) {
    final newStatus = !product.inStock;
    final newQty = newStatus ? (product.stockQuantity > 0 ? product.stockQuantity : 100) : 0;
    ref.read(productsProvider.notifier).updateProduct(product.copyWith(inStock: newStatus, stockQuantity: newQty));

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            Icon(newStatus ? Icons.check_circle : Icons.remove_circle, color: Colors.white, size: 20),
            const SizedBox(width: 10),
            Text('تم تحديث حالة "${product.nameAr}" إلى (${newStatus ? "متوفر بالمخزون: $newQty ${product.unitAr ?? ''} 🟢" : "نفد من المخزون 🔴"})'),
          ],
        ),
        backgroundColor: newStatus ? _kEmerald : _kCrimson,
        duration: const Duration(seconds: 2),
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  void _exportCatalog(List<ProductCardModel> products) {
    final buffer = StringBuffer();
    buffer.writeln('ID,Name_Ar,Name_En,Category,Subcategory,Price_USD,Price_SDG,Stock_Quantity,Unit,In_Stock,Exclusive_To_Magaiees');
    for (final p in products) {
      buffer.writeln('"${p.id}","${p.nameAr}","${p.name}","${p.categoryNameAr}","${p.subCategoryNameAr ?? ''}",${p.priceInUsd ?? 0},${p.pricePerTon},${p.stockQuantity},"${p.unitAr ?? ''}",${p.inStock},${p.isMagaieesExclusive}');
    }

    showDialog(
      context: context,
      builder: (ctx) => Directionality(
        textDirection: TextDirection.rtl,
        child: AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: const Row(
            children: [
              Icon(Icons.download_rounded, color: _kRoyalBlue),
              SizedBox(width: 10),
              Text('تصدير كشف الأسعار والكتالوج (CSV)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('تم تجهيز بيانات ${products.length} صنف للتصدير والمطابقة المحاسبية:', style: const TextStyle(fontSize: 13, color: _kDarkNavy)),
              const SizedBox(height: 12),
              Container(
                constraints: const BoxConstraints(maxHeight: 180),
                width: double.maxFinite,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: Colors.grey.shade300),
                ),
                child: SingleChildScrollView(
                  child: Text(
                    buffer.toString(),
                    style: const TextStyle(fontSize: 11, fontFamily: 'monospace'),
                  ),
                ),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('إغلاق'),
            ),
            FilledButton.icon(
              style: FilledButton.styleFrom(backgroundColor: _kRoyalBlue),
              onPressed: () {
                Navigator.pop(ctx);
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('تم استخراج وتجهيز بيانات الكتالوج بنجاح!'), backgroundColor: _kEmerald),
                );
              },
              icon: const Icon(Icons.check_rounded, size: 18),
              label: const Text('تم'),
            ),
          ],
        ),
      ),
    );
  }

  void _deleteProduct(ProductCardModel product, bool isRtl) {
    showDialog(
      context: context,
      builder: (ctx) => Directionality(
        textDirection: isRtl ? TextDirection.rtl : TextDirection.ltr,
        child: AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
          title: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(color: _kCrimson.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(10)),
                child: const Icon(Icons.delete_forever_rounded, color: _kCrimson, size: 24),
              ),
              const SizedBox(width: 12),
              Text(isRtl ? 'حذف الصنف نهائياً' : 'Delete Product', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
            ],
          ),
          content: Text(
            isRtl
                ? 'هل أنت متأكد من حذف الصنف "${product.nameAr}" من الكتالوج وقاعدة البيانات نهائياً؟ لا يمكن التراجع عن هذا الإجراء.'
                : 'Are you sure you want to permanently delete "${product.name}"? This action cannot be undone.',
            style: const TextStyle(fontSize: 14, color: _kDarkNavy),
          ),
          actionsPadding: const EdgeInsets.all(16),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: Text(isRtl ? 'إلغاء' : 'Cancel', style: TextStyle(color: Colors.grey.shade600, fontWeight: FontWeight.bold)),
            ),
            FilledButton(
              style: FilledButton.styleFrom(backgroundColor: _kCrimson, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10))),
              onPressed: () {
                ref.read(productsProvider.notifier).deleteProduct(product.id);
                Navigator.pop(ctx);
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text('تم حذف الصنف "${product.nameAr}" بنجاح.'),
                    backgroundColor: _kCrimson,
                  ),
                );
              },
              child: Text(isRtl ? 'تأكيد الحذف' : 'Confirm Delete', style: const TextStyle(fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }

  void _showProductFormDialog({
    ProductCardModel? existingProduct,
    required List<CategoryModel> categories,
  }) {
    final isEditing = existingProduct != null;

    final nameArCtrl = TextEditingController(text: existingProduct?.nameAr ?? '');
    final nameEnCtrl = TextEditingController(text: existingProduct?.name ?? '');

    final double initialUsd = existingProduct?.priceInUsd ??
        (existingProduct != null ? (existingProduct.pricePerTon / _usdRate) : 100.0);
    final double initialSdg = existingProduct?.pricePerTon ?? (initialUsd * _usdRate);

    final usdPriceCtrl = TextEditingController(text: initialUsd > 0 ? initialUsd.toStringAsFixed(2) : '');
    final sdgPriceCtrl = TextEditingController(text: initialSdg > 0 ? initialSdg.toStringAsFixed(0) : '');
    final stockQtyCtrl = TextEditingController(text: existingProduct != null ? existingProduct.stockQuantity.toString() : '100');

    String selectedCatId = existingProduct?.categoryId ?? (categories.isNotEmpty ? categories.first.id : '1');
    String? selectedSubCatId = existingProduct?.subCategoryId;
    String selectedUnit = existingProduct?.unitAr?.replaceAll('ج.س/', '') ?? 'طن';
    bool inStock = existingProduct?.inStock ?? true;
    bool isExclusive = existingProduct?.isMagaieesExclusive ?? (_selectedSegment == 1);

    Uint8List? selectedImageBytes;
    String? selectedImageExt;
    String currentImageUrl = existingProduct?.imageUrl ?? '';
    final imageUrlCtrl = TextEditingController(text: currentImageUrl);

    bool isSyncingPrice = false;
    bool isSaving = false;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => StatefulBuilder(
        builder: (dialogCtx, setDlgState) {
          final selectedCat = categories.firstWhere(
            (c) => c.id == selectedCatId,
            orElse: () => categories.isNotEmpty ? categories.first : CategoryModel(id: '1', name: 'General', nameAr: 'عام', iconName: 'category', subCategories: []),
          );

          return Directionality(
            textDirection: TextDirection.rtl,
            child: Dialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
              insetPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
              clipBehavior: Clip.antiAlias,
              child: Container(
                width: 740,
                constraints: BoxConstraints(
                  maxHeight: MediaQuery.of(context).size.height * 0.92,
                ),
                decoration: const BoxDecoration(
                  color: Colors.white,
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    // --- 1. Header with Gradient Accent ---
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 18),
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          colors: [
                            isEditing ? const Color(0xFF1E3A8A) : const Color(0xFF065F46),
                            isEditing ? _kRoyalBlue : _kEmerald,
                          ],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                      ),
                      child: Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(10),
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.2),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Icon(
                              isEditing ? Icons.edit_note_rounded : Icons.add_box_rounded,
                              color: Colors.white,
                              size: 26,
                            ),
                          ),
                          const SizedBox(width: 14),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  isEditing ? 'تعديل بيانات ومواصفات الصنف' : 'إضافة صنف جديد لمنظومة السيطرة',
                                  style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 18, color: Colors.white),
                                ),
                                const SizedBox(height: 3),
                                Text(
                                  isEditing
                                      ? 'قم بتحديث الأسعار والمخزون والمواصفات وسيتم التحديث اللحظي لجميع المشترين'
                                      : 'أدخل تفاصيل الصنف والتسعير والمخزون لربطه فوراً مع السوق والتطبيق',
                                  style: TextStyle(color: Colors.white.withValues(alpha: 0.85), fontSize: 12),
                                ),
                              ],
                            ),
                          ),
                          IconButton(
                            icon: const Icon(Icons.close, color: Colors.white, size: 22),
                            onPressed: isSaving ? null : () => Navigator.pop(dialogCtx),
                            tooltip: 'إغلاق',
                          ),
                        ],
                      ),
                    ),

                    // --- 2. Scrollable Body ---
                    Expanded(
                      child: SingleChildScrollView(
                        padding: const EdgeInsets.all(24),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Categories Selection Card
                            Container(
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                color: const Color(0xFFF8FAFC),
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(color: Colors.grey.shade200),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Row(
                                    children: [
                                      Icon(Icons.category_rounded, size: 18, color: _kRoyalBlue),
                                      SizedBox(width: 8),
                                      Text(
                                        'التصنيف والأقسام *',
                                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: _kDarkNavy),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 12),
                                  Row(
                                    children: [
                                      Expanded(
                                        child: DropdownButtonFormField<String>(
                                          decoration: InputDecoration(
                                            labelText: 'القسم الرئيسي *',
                                            filled: true,
                                            fillColor: Colors.white,
                                            isDense: true,
                                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade300)),
                                            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                          ),
                                          initialValue: categories.any((c) => c.id == selectedCatId) ? selectedCatId : (categories.isNotEmpty ? categories.first.id : null),
                                          items: categories.map((cat) {
                                            return DropdownMenuItem(
                                              value: cat.id,
                                              child: Text(cat.nameAr, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                                            );
                                          }).toList(),
                                          onChanged: isSaving ? null : (val) {
                                            if (val != null) {
                                              setDlgState(() {
                                                selectedCatId = val;
                                                selectedSubCatId = null;
                                              });
                                            }
                                          },
                                        ),
                                      ),
                                      const SizedBox(width: 12),
                                      Expanded(
                                        child: DropdownButtonFormField<String?>(
                                          decoration: InputDecoration(
                                            labelText: 'القسم الفرعي / الماركة',
                                            filled: true,
                                            fillColor: Colors.white,
                                            isDense: true,
                                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade300)),
                                            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                          ),
                                          initialValue: selectedCat.subCategories.any((s) => s.id.toString() == selectedSubCatId) ? selectedSubCatId : null,
                                          items: [
                                            const DropdownMenuItem<String?>(
                                              value: null,
                                              child: Text('بدون قسم فرعي (عام)', style: TextStyle(fontSize: 13, color: Colors.grey)),
                                            ),
                                            ...selectedCat.subCategories.map((sub) {
                                              return DropdownMenuItem<String?>(
                                                value: sub.id.toString(),
                                                child: Text(sub.nameAr, style: const TextStyle(fontSize: 13)),
                                              );
                                            }),
                                          ],
                                          onChanged: isSaving ? null : (val) {
                                            setDlgState(() {
                                              selectedSubCatId = val;
                                            });
                                          },
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(height: 16),

                            // Product Names (Arabic & English)
                            TextField(
                              controller: nameArCtrl,
                              decoration: InputDecoration(
                                labelText: 'اسم ومواصفات الصنف (بالعربية) *',
                                hintText: 'مثال: أسمنت بورتلاندي عادي 50 كجم عالي المقاومة',
                                isDense: true,
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                                prefixIcon: const Icon(Icons.title_rounded, color: _kRoyalBlue, size: 22),
                                filled: true,
                                fillColor: Colors.white,
                                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                              ),
                            ),
                            const SizedBox(height: 12),
                            TextField(
                              controller: nameEnCtrl,
                              decoration: InputDecoration(
                                labelText: 'اسم ومواصفات الصنف (بالإنجليزية)',
                                hintText: 'e.g. Portland Cement Type 1 - 50kg Heavy Duty',
                                isDense: true,
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                                prefixIcon: const Icon(Icons.language_rounded, color: _kDarkNavy, size: 22),
                                filled: true,
                                fillColor: Colors.white,
                                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                              ),
                            ),
                            const SizedBox(height: 16),

                            // Synchronized Dual-Currency Pricing
                            Container(
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                gradient: const LinearGradient(
                                  colors: [
                                    Color(0xFFEFF6FF),
                                    Color(0xFFF0FDF4),
                                  ],
                                  begin: Alignment.topLeft,
                                  end: Alignment.bottomRight,
                                ),
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(color: const Color(0xFFBFDBFE)),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      const Icon(Icons.currency_exchange_rounded, size: 18, color: _kRoyalBlue),
                                      const SizedBox(width: 8),
                                      const Text(
                                        'التسعير المتزامن والتحويل الفوري (USD ↔ SDG)',
                                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: _kDarkNavy),
                                      ),
                                      const Spacer(),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: Colors.white,
                                          borderRadius: BorderRadius.circular(20),
                                          border: Border.all(color: const Color(0xFF93C5FD)),
                                          boxShadow: [
                                            BoxShadow(color: _kRoyalBlue.withValues(alpha: 0.08), blurRadius: 6),
                                          ],
                                        ),
                                        child: Text(
                                          '1 USD = ${_fmtSdg(_usdRate)} SDG',
                                          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: _kRoyalBlue),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 14),
                                  Row(
                                    children: [
                                      // USD Price
                                      Expanded(
                                        child: TextField(
                                          controller: usdPriceCtrl,
                                          keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                          onChanged: (val) {
                                            if (isSyncingPrice) return;
                                            isSyncingPrice = true;
                                            final parsed = double.tryParse(val) ?? 0.0;
                                            sdgPriceCtrl.text = (parsed * _usdRate).toStringAsFixed(0);
                                            isSyncingPrice = false;
                                            setDlgState(() {});
                                          },
                                          decoration: InputDecoration(
                                            labelText: 'السعر بالدولار (USD) *',
                                            filled: true,
                                            fillColor: Colors.white,
                                            isDense: true,
                                            prefixIcon: const Icon(Icons.attach_money_rounded, color: _kRoyalBlue, size: 20),
                                            suffixText: 'USD',
                                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                                          ),
                                        ),
                                      ),
                                      const SizedBox(width: 10),
                                      // SDG Price
                                      Expanded(
                                        child: TextField(
                                          controller: sdgPriceCtrl,
                                          keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                          onChanged: (val) {
                                            if (isSyncingPrice) return;
                                            isSyncingPrice = true;
                                            final parsed = double.tryParse(val) ?? 0.0;
                                            usdPriceCtrl.text = (parsed / _usdRate).toStringAsFixed(2);
                                            isSyncingPrice = false;
                                            setDlgState(() {});
                                          },
                                          decoration: InputDecoration(
                                            labelText: 'السعر بالجنيه (SDG) *',
                                            filled: true,
                                            fillColor: Colors.white,
                                            isDense: true,
                                            prefixIcon: const Icon(Icons.payments_rounded, color: _kEmerald, size: 20),
                                            suffixText: 'SDG',
                                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                                          ),
                                        ),
                                      ),
                                      const SizedBox(width: 10),
                                      // Unit Dropdown
                                      SizedBox(
                                        width: 110,
                                        child: DropdownButtonFormField<String>(
                                          decoration: InputDecoration(
                                            labelText: 'الوحدة',
                                            filled: true,
                                            fillColor: Colors.white,
                                            isDense: true,
                                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                            contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 12),
                                          ),
                                          initialValue: selectedUnit,
                                          items: const [
                                            DropdownMenuItem(value: 'طن', child: Text('طن')),
                                            DropdownMenuItem(value: 'كيس', child: Text('كيس')),
                                            DropdownMenuItem(value: 'م²', child: Text('م²')),
                                            DropdownMenuItem(value: 'متر طولي', child: Text('متر')),
                                            DropdownMenuItem(value: 'لوح', child: Text('لوح')),
                                            DropdownMenuItem(value: 'عود', child: Text('عود')),
                                            DropdownMenuItem(value: 'حبة', child: Text('حبة')),
                                            DropdownMenuItem(value: 'لتر', child: Text('لتر')),
                                            DropdownMenuItem(value: 'برميل', child: Text('برميل')),
                                            DropdownMenuItem(value: 'حمولة', child: Text('حمولة')),
                                            DropdownMenuItem(value: 'درباس', child: Text('درباس')),
                                          ],
                                          onChanged: isSaving ? null : (val) {
                                            if (val != null) setDlgState(() => selectedUnit = val);
                                          },
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(height: 16),

                            // --- Section: Inventory & Stock Quantity Control ---
                            Container(
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                color: const Color(0xFFF8FAFC),
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(color: Colors.grey.shade200),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      const Icon(Icons.inventory_2_rounded, size: 18, color: _kRoyalBlue),
                                      const SizedBox(width: 8),
                                      const Text('إدارة المخزون والكمية المتوفرة *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: _kDarkNavy)),
                                      const Spacer(),
                                      Text('الرصيد الفعلي للصنف في المستودع', style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
                                    ],
                                  ),
                                  const SizedBox(height: 12),
                                  Row(
                                    children: [
                                      Expanded(
                                        child: TextField(
                                          controller: stockQtyCtrl,
                                          keyboardType: TextInputType.number,
                                          onChanged: (val) {
                                            final qty = int.tryParse(val) ?? 0;
                                            setDlgState(() {
                                              inStock = qty > 0;
                                            });
                                          },
                                          decoration: InputDecoration(
                                            labelText: 'الكمية المتوفرة بالمخزن *',
                                            hintText: 'مثال: 500',
                                            filled: true,
                                            fillColor: Colors.white,
                                            isDense: true,
                                            prefixIcon: const Icon(Icons.warehouse_rounded, color: _kRoyalBlue, size: 20),
                                            suffixText: selectedUnit,
                                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                                          ),
                                        ),
                                      ),
                                      const SizedBox(width: 10),
                                      // Quick Preset Increment Buttons
                                      Wrap(
                                        spacing: 6,
                                        children: [10, 50, 100, 500].map((inc) {
                                          return ActionChip(
                                            label: Text('+$inc', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: _kRoyalBlue)),
                                            backgroundColor: const Color(0xFFEFF6FF),
                                            padding: const EdgeInsets.symmetric(horizontal: 4),
                                            onPressed: isSaving ? null : () {
                                              final current = int.tryParse(stockQtyCtrl.text) ?? 0;
                                              final updated = current + inc;
                                              stockQtyCtrl.text = updated.toString();
                                              setDlgState(() => inStock = updated > 0);
                                            },
                                          );
                                        }).toList(),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(height: 16),

                            // Image Upload Container
                            Container(
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                color: const Color(0xFFF8FAFC),
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(color: Colors.grey.shade200),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Row(
                                    children: [
                                      Icon(Icons.image_rounded, size: 18, color: _kRoyalBlue),
                                      SizedBox(width: 8),
                                      Text('صورة الصنف', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: _kDarkNavy)),
                                    ],
                                  ),
                                  const SizedBox(height: 12),
                                  Row(
                                    children: [
                                      Container(
                                        width: 84,
                                        height: 84,
                                        decoration: BoxDecoration(
                                          color: Colors.white,
                                          borderRadius: BorderRadius.circular(12),
                                          border: Border.all(color: Colors.grey.shade300),
                                          boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 6)],
                                        ),
                                        child: ClipRRect(
                                          borderRadius: BorderRadius.circular(12),
                                          child: selectedImageBytes != null
                                              ? Image.memory(selectedImageBytes!, fit: BoxFit.cover)
                                              : (currentImageUrl.isNotEmpty
                                                  ? Image.network(
                                                      currentImageUrl,
                                                      fit: BoxFit.cover,
                                                      errorBuilder: (context, error, stackTrace) => const Center(child: Icon(Icons.broken_image, color: Colors.grey)),
                                                    )
                                                  : const Center(child: Icon(Icons.add_photo_alternate_rounded, size: 36, color: Colors.grey))),
                                        ),
                                      ),
                                      const SizedBox(width: 16),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            FilledButton.tonalIcon(
                                              style: FilledButton.styleFrom(
                                                backgroundColor: const Color(0xFFEFF6FF),
                                                foregroundColor: _kRoyalBlue,
                                                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                              ),
                                              icon: const Icon(Icons.upload_file_rounded, size: 18),
                                              label: Text(
                                                selectedImageBytes != null || currentImageUrl.isNotEmpty ? 'تغيير صورة الصنف' : 'رفع صورة من جهازك',
                                                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                                              ),
                                              onPressed: isSaving
                                                  ? null
                                                  : () async {
                                                      final picker = ImagePicker();
                                                      final XFile? image = await picker.pickImage(
                                                        source: ImageSource.gallery,
                                                        imageQuality: 80,
                                                        maxWidth: 900,
                                                      );
                                                      if (image != null) {
                                                        final bytes = await image.readAsBytes();
                                                        final ext = image.name.split('.').last;
                                                        setDlgState(() {
                                                          selectedImageBytes = bytes;
                                                          selectedImageExt = ext;
                                                        });
                                                      }
                                                    },
                                            ),
                                            const SizedBox(height: 8),
                                            TextField(
                                              controller: imageUrlCtrl,
                                              onChanged: (val) {
                                                setDlgState(() {
                                                  currentImageUrl = val.trim();
                                                  selectedImageBytes = null;
                                                });
                                              },
                                              decoration: InputDecoration(
                                                hintText: 'أو أدخل رابط صورة مباشر (URL)...',
                                                hintStyle: TextStyle(fontSize: 11, color: Colors.grey.shade400),
                                                filled: true,
                                                fillColor: Colors.white,
                                                isDense: true,
                                                contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide(color: Colors.grey.shade300)),
                                              ),
                                              style: const TextStyle(fontSize: 12),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(height: 16),

                            // Switches Row: Exclusive Magaiees & Stock Availability
                            Row(
                              children: [
                                Expanded(
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                    decoration: BoxDecoration(
                                      color: isExclusive ? _kRoyalBlue.withValues(alpha: 0.06) : Colors.white,
                                      borderRadius: BorderRadius.circular(14),
                                      border: Border.all(color: isExclusive ? _kRoyalBlue.withValues(alpha: 0.4) : Colors.grey.shade300),
                                    ),
                                    child: SwitchListTile(
                                      contentPadding: EdgeInsets.zero,
                                      title: const Text('توريد حصري للسيطرة 🛡️', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                                      subtitle: const Text('شارة التوريد الرسمي المعتمد', style: TextStyle(fontSize: 11, color: Colors.grey)),
                                      value: isExclusive,
                                      activeThumbColor: _kRoyalBlue,
                                      onChanged: isSaving ? null : (val) => setDlgState(() => isExclusive = val),
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                    decoration: BoxDecoration(
                                      color: inStock ? _kEmerald.withValues(alpha: 0.06) : Colors.white,
                                      borderRadius: BorderRadius.circular(14),
                                      border: Border.all(color: inStock ? _kEmerald.withValues(alpha: 0.4) : Colors.grey.shade300),
                                    ),
                                    child: SwitchListTile(
                                      contentPadding: EdgeInsets.zero,
                                      title: Text(inStock ? 'متوفر بالمخزون 🟢' : 'نفد من المخزون 🔴', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: inStock ? _kEmeraldDark : _kCrimson)),
                                      subtitle: const Text('إمكانية الشراء الفوري من التطبيق', style: TextStyle(fontSize: 11, color: Colors.grey)),
                                      value: inStock,
                                      activeThumbColor: _kEmerald,
                                      onChanged: isSaving
                                          ? null
                                          : (val) => setDlgState(() {
                                                inStock = val;
                                                if (!val) {
                                                  stockQtyCtrl.text = '0';
                                                } else if (stockQtyCtrl.text == '0' || stockQtyCtrl.text.isEmpty) {
                                                  stockQtyCtrl.text = '100';
                                                }
                                              }),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ),

                    // --- 3. Action Footer ---
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        border: Border(top: BorderSide(color: Colors.grey.shade200)),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.end,
                        children: [
                          TextButton(
                            onPressed: isSaving ? null : () => Navigator.pop(dialogCtx),
                            style: TextButton.styleFrom(
                              foregroundColor: Colors.grey.shade600,
                              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                            ),
                            child: const Text('إلغاء التراجع', style: TextStyle(fontWeight: FontWeight.bold)),
                          ),
                          const SizedBox(width: 12),
                          FilledButton.icon(
                            style: FilledButton.styleFrom(
                              backgroundColor: isEditing ? _kRoyalBlue : _kEmerald,
                              padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 14),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              elevation: 2,
                            ),
                            onPressed: isSaving
                                ? null
                                : () async {
                                    final nameAr = nameArCtrl.text.trim();
                                    final nameEn = nameEnCtrl.text.trim();
                                    final usdPrice = double.tryParse(usdPriceCtrl.text) ?? 0.0;
                                    final sdgPrice = double.tryParse(sdgPriceCtrl.text) ?? 0.0;
                                    final stockQty = int.tryParse(stockQtyCtrl.text) ?? (inStock ? 100 : 0);

                                    if (nameAr.isEmpty) {
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        const SnackBar(content: Text('الرجاء إدخال اسم ومواصفات الصنف بالعربية'), backgroundColor: Colors.red),
                                      );
                                      return;
                                    }
                                    if (usdPrice <= 0 && sdgPrice <= 0) {
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        const SnackBar(content: Text('الرجاء إدخال سعر الصنف بالدولار أو الجنيه'), backgroundColor: Colors.red),
                                      );
                                      return;
                                    }

                                    setDlgState(() => isSaving = true);
                                    final messenger = ScaffoldMessenger.of(context);

                                    try {
                                      String? finalImageUrl = currentImageUrl.isNotEmpty ? currentImageUrl : null;

                                      if (selectedImageBytes != null) {
                                        final fileName = 'prod_${DateTime.now().millisecondsSinceEpoch}.${selectedImageExt ?? "jpg"}';
                                        await Supabase.instance.client.storage
                                            .from('category_images')
                                            .uploadBinary(fileName, selectedImageBytes!);
                                        finalImageUrl = Supabase.instance.client.storage
                                            .from('category_images')
                                            .getPublicUrl(fileName);
                                      }

                                      final selectedSubCat = selectedSubCatId != null
                                          ? selectedCat.subCategories.cast<SubCategoryModel?>().firstWhere(
                                                (s) => s?.id.toString() == selectedSubCatId,
                                                orElse: () => null,
                                              )
                                          : null;

                                      final productToSave = ProductCardModel(
                                        id: existingProduct?.id ?? 'custom_prod_${DateTime.now().millisecondsSinceEpoch}',
                                        name: nameEn.isNotEmpty ? nameEn : nameAr,
                                        nameAr: nameAr,
                                        categoryId: selectedCat.id,
                                        categoryName: selectedCat.name,
                                        categoryNameAr: selectedCat.nameAr,
                                        subCategoryId: selectedSubCatId,
                                        subCategoryName: selectedSubCat?.name,
                                        subCategoryNameAr: selectedSubCat?.nameAr,
                                        pricePerTon: sdgPrice > 0 ? sdgPrice : (usdPrice * _usdRate),
                                        priceInUsd: usdPrice > 0 ? usdPrice : (sdgPrice / _usdRate),
                                        unitAr: selectedUnit,
                                        unit: selectedUnit,
                                        stockQuantity: stockQty,
                                        merchantId: isExclusive ? 'm_magaiees' : (existingProduct?.merchantId ?? 'm_custom'),
                                        storeName: isExclusive ? 'Magaiees Direct Supply' : (existingProduct?.storeName ?? 'المتجر المعتمد'),
                                        storeNameAr: isExclusive ? 'شركة السيطرة - توريد مباشر' : (existingProduct?.storeNameAr ?? 'المتجر المعتمد'),
                                        imageUrl: finalImageUrl,
                                        inStock: stockQty > 0 && inStock,
                                        isMagaieesExclusive: isExclusive,
                                      );

                                      if (isEditing) {
                                        await ref.read(productsProvider.notifier).updateProduct(productToSave);
                                      } else {
                                        await ref.read(productsProvider.notifier).addProduct(productToSave);
                                      }

                                      ref.invalidate(productsProvider);

                                      if (dialogCtx.mounted) {
                                        Navigator.pop(dialogCtx);
                                      }

                                      messenger.showSnackBar(
                                        SnackBar(
                                          content: Row(
                                            children: [
                                              const Icon(Icons.check_circle, color: Colors.white),
                                              const SizedBox(width: 10),
                                              Text(isEditing
                                                  ? 'تم تحديث بيانات ومخزون الصنف "$nameAr" بنجاح.'
                                                  : 'تمت إضافة ونشر الصنف "$nameAr" برصيد ($stockQty $selectedUnit) فوراً.'),
                                            ],
                                          ),
                                          backgroundColor: _kEmerald,
                                          behavior: SnackBarBehavior.floating,
                                        ),
                                      );
                                    } catch (err) {
                                      setDlgState(() => isSaving = false);
                                      messenger.showSnackBar(
                                        SnackBar(
                                          content: Text('حدث خطأ أثناء الحفظ: $err'),
                                          backgroundColor: _kCrimson,
                                        ),
                                      );
                                    }
                                  },
                            icon: isSaving
                                ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                                : const Icon(Icons.check_circle_rounded, size: 20),
                            label: Text(
                              isSaving
                                  ? 'جاري النشر والحفظ...'
                                  : (isEditing ? 'حفظ التعديلات' : 'نشر الصنف الآن في السوق'),
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }


  void _showProductFinancialDialog({
    required BuildContext context,
    required ProductCardModel product,
    required ProductAnalytics analytics,
    required AdminProductsInsights insights,
  }) {
    final double salesShare = insights.totalSalesVolume > 0
        ? (analytics.salesVolume / insights.totalSalesVolume * 100)
        : 0.0;

    showDialog(
      context: context,
      builder: (ctx) => Directionality(
        textDirection: TextDirection.rtl,
        child: Dialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          insetPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
          child: Container(
            width: 620,
            constraints: const BoxConstraints(maxHeight: 700),
            padding: const EdgeInsets.all(24),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Dialog Header
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(colors: [_kRoyalBlue, _kDarkNavy]),
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: const Icon(Icons.analytics_rounded, color: Colors.white, size: 26),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              product.nameAr,
                              style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 18, color: _kDarkNavy),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              'كشف الأداء المالي والمحاسبي والرعاية الإعلانية للصنف',
                              style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                            ),
                          ],
                        ),
                      ),
                      IconButton(
                        onPressed: () => Navigator.pop(ctx),
                        icon: const Icon(Icons.close_rounded),
                      ),
                    ],
                  ),
                  const SizedBox(height: 20),

                  // 4 Financial Metrics Grid
                  Row(
                    children: [
                      _buildMiniFinancialCard(
                        title: 'إجمالي المبيعات المحققة',
                        value: '${_fmtSdg(analytics.salesVolume)} ج.س',
                        subtitle: 'من حركة الطلبات المعتمدة',
                        color: _kEmerald,
                        icon: Icons.monetization_on_rounded,
                      ),
                      const SizedBox(width: 12),
                      _buildMiniFinancialCard(
                        title: 'الكميات المباعة للعملاء',
                        value: '${analytics.unitsSold} ${product.unitAr ?? "وحدة"}',
                        subtitle: '${analytics.orderCount} طلبات شراء',
                        color: _kRoyalBlue,
                        icon: Icons.shopping_cart_checkout_rounded,
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      _buildMiniFinancialCard(
                        title: 'تقييم المخزون المتبقي',
                        value: '${_fmtSdg(analytics.inventoryValue)} ج.س',
                        subtitle: '${product.stockQuantity} ${product.unitAr ?? "وحدة"} بالمستودع',
                        color: _kAmberDark,
                        icon: Icons.warehouse_rounded,
                      ),
                      const SizedBox(width: 12),
                      _buildMiniFinancialCard(
                        title: 'حصة الصنف من مبيعات السوق',
                        value: '${salesShare.toStringAsFixed(2)}%',
                        subtitle: 'من إجمالي إيرادات المنصة',
                        color: _kPurple,
                        icon: Icons.pie_chart_rounded,
                      ),
                    ],
                  ),
                  const SizedBox(height: 20),

                  // Advertiser / Merchant Store Card
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: product.isMagaieesExclusive
                          ? _kRoyalBlue.withValues(alpha: 0.05)
                          : _kPurple.withValues(alpha: 0.05),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(
                        color: product.isMagaieesExclusive
                            ? _kRoyalBlue.withValues(alpha: 0.2)
                            : _kPurple.withValues(alpha: 0.2),
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Icon(
                              product.isMagaieesExclusive
                                  ? Icons.verified_rounded
                                  : Icons.campaign_rounded,
                              color: product.isMagaieesExclusive ? _kRoyalBlue : _kPurple,
                              size: 20,
                            ),
                            const SizedBox(width: 8),
                            Text(
                              product.isMagaieesExclusive
                                  ? 'شراكة التوريد الحصري المباشر'
                                  : 'المتجر الراعي والرعاية الإعلانية',
                              style: TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 14,
                                color: product.isMagaieesExclusive ? _kRoyalBlue : _kPurple,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),
                        _buildDetailLine('اسم الجهة الموردة / الراعية:', analytics.advertiserStoreName),
                        if (!product.isMagaieesExclusive) ...[
                          _buildDetailLine(
                            'سعر صرف المتجر المعتمد:',
                            analytics.storeUsdRate > 0
                                ? '1 USD = ${_fmtSdg(analytics.storeUsdRate)} SDG'
                                : 'مربوط بسعر الصرف المركزي',
                          ),
                          if (analytics.storeAddress != null && analytics.storeAddress!.isNotEmpty)
                            _buildDetailLine('الموقع والمستودع:', analytics.storeAddress!),
                          if (analytics.storeBank != null && analytics.storeBank!.isNotEmpty)
                            _buildDetailLine('المعلومات البنكية المعتمدة:', analytics.storeBank!),
                          _buildDetailLine('حالة الشراكة الإعلانية:', '📢 معلن معتمد ونشط بالمنصة'),
                        ] else ...[
                          _buildDetailLine('طبيعة الصنف:', 'صنف توريد مباشر معتمد حصرياً للسيطرة'),
                          _buildDetailLine('الضمان:', 'مشمول بضمان الجودة والتوريد الرسمي'),
                        ],
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),

                  // Actions
                  Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      FilledButton(
                        style: FilledButton.styleFrom(
                          backgroundColor: _kDarkNavy,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                        ),
                        onPressed: () => Navigator.pop(ctx),
                        child: const Text('إغلاق الكشف', style: TextStyle(fontWeight: FontWeight.bold)),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildDetailLine(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: TextStyle(fontSize: 12, color: Colors.grey.shade700, fontWeight: FontWeight.w600)),
          const SizedBox(width: 8),
          Expanded(
            child: Text(value, style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold, color: _kDarkNavy)),
          ),
        ],
      ),
    );
  }

  Widget _buildMiniFinancialCard({
    required String title,
    required String value,
    required String subtitle,
    required Color color,
    required IconData icon,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: Colors.grey.shade200),
          boxShadow: [
            BoxShadow(color: color.withValues(alpha: 0.05), blurRadius: 10, offset: const Offset(0, 3)),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(title, style: TextStyle(fontSize: 11, color: Colors.grey.shade600, fontWeight: FontWeight.w600)),
                Icon(icon, color: color, size: 18),
              ],
            ),
            const SizedBox(height: 6),
            Text(value, style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900, color: color)),
            const SizedBox(height: 2),
            Text(subtitle, style: TextStyle(fontSize: 10.5, color: Colors.grey.shade500)),
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard({
    required IconData icon,
    required String label,
    required String value,
    required Color color,
    required String badge,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: Colors.grey.shade200),
          boxShadow: [
            BoxShadow(
              color: color.withValues(alpha: 0.06),
              blurRadius: 16,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [color, color.withValues(alpha: 0.8)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(14),
                boxShadow: [
                  BoxShadow(
                    color: color.withValues(alpha: 0.3),
                    blurRadius: 10,
                    offset: const Offset(0, 3),
                  ),
                ],
              ),
              child: Icon(icon, size: 26, color: Colors.white),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        value,
                        style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 24, color: _kDarkNavy, height: 1.1),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: color.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          badge,
                          style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: color),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    label,
                    style: TextStyle(fontSize: 12.5, color: Colors.grey.shade600, fontWeight: FontWeight.w600),
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _resetAllFilters() {
    setState(() {
      _selectedSegment = 0;
      _selectedCategoryId = null;
      _selectedSubCategoryId = null;
      _selectedStoreId = null;
      _searchQuery = '';
      _searchCtrl.clear();
      _sortBy = 'newest';
    });
  }

  List<_StoreFilterOption> _getStoreOptions(List<ProductCardModel> allProducts, AdminProductsInsights? insights) {
    final Map<String, _StoreFilterOption> map = {};
    for (final p in allProducts) {
      final key = p.merchantId.trim().isNotEmpty
          ? p.merchantId.trim()
          : (p.storeNameAr.trim().isNotEmpty ? p.storeNameAr.trim() : 'other');
      final name = p.storeNameAr.trim().isNotEmpty
          ? p.storeNameAr.trim()
          : (p.storeName.trim().isNotEmpty ? p.storeName.trim() : (p.isMagaieesExclusive ? 'توريد السيطرة المباشر' : 'متجر شريك'));
      if (map.containsKey(key)) {
        map[key] = _StoreFilterOption(id: key, name: name, count: map[key]!.count + 1);
      } else {
        map[key] = _StoreFilterOption(id: key, name: name, count: 1);
      }
    }
    if (insights != null) {
      for (final ps in insights.partnerStores) {
        if (!map.containsKey(ps.storeId)) {
          map[ps.storeId] = _StoreFilterOption(id: ps.storeId, name: ps.storeNameAr, count: ps.productsCount);
        }
      }
    }
    final list = map.values.toList();
    list.sort((a, b) => b.count.compareTo(a.count));
    return list;
  }

  String _getSubCategoryName(List<CategoryModel> categories, String subId) {
    for (final c in categories) {
      for (final s in c.subCategories) {
        if (s.id == subId) return s.nameAr;
      }
    }
    return subId;
  }

  String _getStoreName(List<_StoreFilterOption> stores, String storeId) {
    for (final s in stores) {
      if (s.id == storeId) return s.name;
    }
    return storeId;
  }

  String _getSortLabel(String sortBy) {
    switch (sortBy) {
      case 'sales_desc':
        return 'الأعلى مبيعاً';
      case 'units_desc':
        return 'الأكثر طلباً';
      case 'valuation_desc':
        return 'الأعلى تقييماً بالمخزن';
      case 'price_asc':
        return 'الأقل سعراً';
      case 'price_desc':
        return 'الأعلى سعراً';
      case 'in_stock':
        return 'المتوفر أولاً';
      case 'name_asc':
        return 'أبجدياً (أ-ي)';
      case 'newest':
      default:
        return 'الأحدث إضافة';
    }
  }

  Widget _buildDropdownCountBadge(int count, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Text(
        count.toString(),
        style: TextStyle(
          fontSize: 10.5,
          fontWeight: FontWeight.bold,
          color: color,
        ),
      ),
    );
  }

  Widget _buildFilterDropdownCard<T>({
    required String label,
    required IconData icon,
    required Color accentColor,
    required T? value,
    required List<DropdownMenuItem<T>> items,
    required ValueChanged<T?> onChanged,
  }) {
    final bool isHighlighted = value != null && value != 'newest';
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: isHighlighted ? accentColor : const Color(0xFFE2E8F0),
          width: isHighlighted ? 1.5 : 1.0,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            children: [
              Icon(icon, size: 14, color: isHighlighted ? accentColor : Colors.grey.shade600),
              const SizedBox(width: 5),
              Expanded(
                child: Text(
                  label,
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: isHighlighted ? accentColor : Colors.grey.shade600,
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          DropdownButtonHideUnderline(
            child: DropdownButton<T>(
              value: value,
              isExpanded: true,
              isDense: true,
              icon: Icon(Icons.keyboard_arrow_down_rounded, size: 18, color: isHighlighted ? accentColor : Colors.grey.shade500),
              items: items,
              onChanged: onChanged,
              style: const TextStyle(
                fontSize: 12.5,
                fontWeight: FontWeight.w700,
                color: _kDarkNavy,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActiveTag({
    required String label,
    required Color color,
    required VoidCallback onDelete,
  }) {
    return Container(
      margin: const EdgeInsets.only(left: 6),
      padding: const EdgeInsets.only(right: 10, left: 4, top: 4, bottom: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            label,
            style: TextStyle(
              fontSize: 11.5,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
          const SizedBox(width: 4),
          InkWell(
            onTap: onDelete,
            borderRadius: BorderRadius.circular(12),
            child: Padding(
              padding: const EdgeInsets.all(2),
              child: Icon(Icons.close_rounded, size: 14, color: color),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSegmentTab(int index, String title, IconData icon, Color activeColor, int count) {
    final isSelected = _selectedSegment == index;
    return InkWell(
      onTap: () => setState(() {
        _selectedSegment = index;
      }),
      borderRadius: BorderRadius.circular(14),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(vertical: 9, horizontal: 14),
        decoration: BoxDecoration(
          color: isSelected ? activeColor : Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: isSelected ? activeColor : Colors.grey.shade300,
            width: isSelected ? 1.5 : 1.0,
          ),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: activeColor.withValues(alpha: 0.25),
                    blurRadius: 10,
                    offset: const Offset(0, 3),
                  )
                ]
              : [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.02),
                    blurRadius: 4,
                    offset: const Offset(0, 1),
                  )
                ],
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              icon,
              size: 17,
              color: isSelected ? Colors.white : activeColor,
            ),
            const SizedBox(width: 7),
            Text(
              title,
              style: TextStyle(
                fontSize: 13,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
                color: isSelected ? Colors.white : _kDarkNavy,
              ),
            ),
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
              decoration: BoxDecoration(
                color: isSelected ? Colors.white.withValues(alpha: 0.25) : activeColor.withValues(alpha: 0.08),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(
                count.toString(),
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w900,
                  color: isSelected ? Colors.white : activeColor,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildProductGridCard({
    required ProductCardModel p,
    required bool isRtl,
    required List<CategoryModel> categories,
    required ProductAnalytics analytics,
    required AdminProductsInsights insights,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // 1. Image & Badges
          AspectRatio(
            aspectRatio: 4 / 3,
            child: Stack(
              fit: StackFit.expand,
              children: [
                p.imageUrl != null && p.imageUrl!.isNotEmpty
                    ? Image.network(
                        p.imageUrl!,
                        fit: BoxFit.cover,
                        errorBuilder: (context, error, stackTrace) => Container(
                          color: const Color(0xFFF1F5F9),
                          child: const Center(child: Icon(Icons.broken_image, color: Colors.grey, size: 36)),
                        ),
                      )
                    : Container(
                        color: const Color(0xFFF8FAFC),
                        child: Center(
                          child: Icon(Icons.inventory_2_rounded, size: 44, color: _kRoyalBlue.withValues(alpha: 0.3)),
                        ),
                      ),
                // Magaiees Exclusive vs Advertiser Partner Badge
                if (p.isMagaieesExclusive)
                  Positioned(
                    top: 10,
                    right: 10,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(colors: [_kRoyalBlue, Color(0xFF1E3A8A)]),
                        borderRadius: BorderRadius.circular(20),
                        boxShadow: [
                          BoxShadow(color: _kRoyalBlue.withValues(alpha: 0.3), blurRadius: 6),
                        ],
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.verified_rounded, color: Colors.white, size: 13),
                          SizedBox(width: 4),
                          Text('حصري للسيطرة', style: TextStyle(color: Colors.white, fontSize: 10.5, fontWeight: FontWeight.bold)),
                        ],
                      ),
                    ),
                  )
                else
                  Positioned(
                    top: 10,
                    right: 10,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(colors: [_kPurple, Color(0xFF6D28D9)]),
                        borderRadius: BorderRadius.circular(20),
                        boxShadow: [
                          BoxShadow(color: _kPurple.withValues(alpha: 0.3), blurRadius: 6),
                        ],
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.campaign_rounded, color: Colors.white, size: 13),
                          const SizedBox(width: 4),
                          Text(
                            analytics.advertiserStoreName.isNotEmpty
                                ? 'معلن: ${analytics.advertiserStoreName}'
                                : 'سوق الشركاء',
                            style: const TextStyle(color: Colors.white, fontSize: 10.5, fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                    ),
                  ),
                // Stock Quantity Badge
                Positioned(
                  bottom: 8,
                  left: 8,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: p.inStock ? _kEmeraldDark.withValues(alpha: 0.95) : _kCrimson.withValues(alpha: 0.95),
                      borderRadius: BorderRadius.circular(12),
                      boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.2), blurRadius: 4)],
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(p.inStock ? Icons.warehouse_rounded : Icons.cancel_rounded, color: Colors.white, size: 12),
                        const SizedBox(width: 4),
                        Text(
                          p.inStock ? '${p.stockQuantity} ${p.unitAr ?? ""}' : 'نفد من المخزون',
                          style: const TextStyle(color: Colors.white, fontSize: 10.5, fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),

          // 2. Info, Financial Tag & Dual Price
          Expanded(
            child: Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Category & Advertiser line
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: _kRoyalBlue.withValues(alpha: 0.08),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          p.categoryNameAr.isNotEmpty ? p.categoryNameAr : p.categoryName,
                          style: const TextStyle(color: _kRoyalBlue, fontSize: 11, fontWeight: FontWeight.bold),
                        ),
                      ),
                      if (p.subCategoryNameAr != null && p.subCategoryNameAr!.isNotEmpty) ...[
                        const SizedBox(width: 4),
                        Text('•', style: TextStyle(color: Colors.grey.shade400, fontSize: 12)),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            p.subCategoryNameAr!,
                            style: TextStyle(color: Colors.grey.shade600, fontSize: 11, fontWeight: FontWeight.w500),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ],
                  ),
                  const SizedBox(height: 6),

                  // Item Name
                  Text(
                    p.nameAr,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _kDarkNavy, height: 1.25),
                  ),
                  const SizedBox(height: 6),

                  // Accounting Sales & Valuation mini bar
                  Row(
                    children: [
                      if (analytics.salesVolume > 0)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: _kEmerald.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.trending_up_rounded, size: 12, color: _kEmeraldDark),
                              const SizedBox(width: 3),
                              Text(
                                'مبيعات: ${_fmtSdg(analytics.salesVolume)} ج.س (${analytics.unitsSold})',
                                style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: _kEmeraldDark),
                              ),
                            ],
                          ),
                        )
                      else
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: Colors.grey.shade100,
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            'المخزون: ${_fmtSdg(analytics.inventoryValue)} ج.س',
                            style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.grey.shade700),
                          ),
                        ),
                    ],
                  ),
                  const Spacer(),

                  // Local Currency Price Box (SDG)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF0FDF4),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFBBF7D0)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.payments_rounded, size: 16, color: _kEmeraldDark),
                        const SizedBox(width: 6),
                        Text(
                          '${_fmtSdg(p.pricePerTon)} ج.س',
                          style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13.5, color: _kEmeraldDark),
                        ),
                        if (p.unitAr != null && p.unitAr!.isNotEmpty) ...[
                          Text(
                            ' / ${UnitFormatter.format(p.unitAr, p.unit, isRtl: true)}',
                            style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: Colors.grey.shade700),
                          ),
                        ],
                      ],
                    ),
                  ),
                  const SizedBox(height: 8),

                  // Bottom Controls
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      InkWell(
                        onTap: () => _toggleStock(p),
                        borderRadius: BorderRadius.circular(10),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: p.inStock ? _kEmerald.withValues(alpha: 0.1) : _kCrimson.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(p.inStock ? Icons.toggle_on_rounded : Icons.toggle_off_rounded, color: p.inStock ? _kEmerald : _kCrimson, size: 20),
                              const SizedBox(width: 4),
                              Text(p.inStock ? 'رصيد: ${p.stockQuantity}' : 'معطل', style: TextStyle(color: p.inStock ? _kEmeraldDark : _kCrimson, fontSize: 11, fontWeight: FontWeight.bold)),
                            ],
                          ),
                        ),
                      ),
                      Row(
                        children: [
                          IconButton(
                            icon: const Icon(Icons.analytics_outlined, size: 20, color: _kPurple),
                            tooltip: 'كشف الأداء المالي والرعاية',
                            onPressed: () => _showProductFinancialDialog(
                              context: context,
                              product: p,
                              analytics: analytics,
                              insights: insights,
                            ),
                            padding: EdgeInsets.zero,
                            constraints: const BoxConstraints(),
                            splashRadius: 18,
                          ),
                          const SizedBox(width: 8),
                          IconButton(
                            icon: const Icon(Icons.edit_note_rounded, size: 22, color: _kRoyalBlue),
                            tooltip: 'تعديل بيانات ومخزون الصنف',
                            onPressed: () => _showProductFormDialog(categories: categories, existingProduct: p),
                            padding: EdgeInsets.zero,
                            constraints: const BoxConstraints(),
                            splashRadius: 18,
                          ),
                          const SizedBox(width: 8),
                          IconButton(
                            icon: const Icon(Icons.delete_outline_rounded, size: 20, color: _kCrimson),
                            tooltip: 'حذف الصنف',
                            onPressed: () => _deleteProduct(p, isRtl),
                            padding: EdgeInsets.zero,
                            constraints: const BoxConstraints(),
                            splashRadius: 18,
                          ),
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    ref.listen<PricingState>(pricingProvider, (prev, next) {
      if (prev?.usdExchangeRate != next.usdExchangeRate) {
        _usdRateCtrl.text = next.usdExchangeRate.toStringAsFixed(0);
        setState(() {
          _usdRate = next.usdExchangeRate;
        });
      }
    });

    final productsAsync = ref.watch(productsProvider);
    final categoriesAsync = ref.watch(categoriesProvider);
    final insightsAsync = ref.watch(adminProductsInsightsProvider);
    final insights = insightsAsync.valueOrNull;

    return Scaffold(
      backgroundColor: _kBgLight,
      body: productsAsync.when(
        loading: () => const Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              CircularProgressIndicator(color: _kRoyalBlue, strokeWidth: 3),
              SizedBox(height: 16),
              Text('جاري مزامنة كتالوج الأصناف والمخزون والبيانات المحاسبية...', style: TextStyle(color: _kDarkNavy, fontWeight: FontWeight.bold)),
            ],
          ),
        ),
        error: (e, st) => AdminErrorView(
          error: e,
          stackTrace: st,
          onRetry: () {
            ref.invalidate(productsProvider);
            ref.invalidate(adminProductsInsightsProvider);
          },
        ),
        data: (allProducts) {
          final allCategories = categoriesAsync.valueOrNull ?? [];
          final storeOptions = _getStoreOptions(allProducts, insights);
          final filtered = _filterAndSort(allProducts, insights);

          final exclusiveCount = insights?.exclusiveProductsCount ?? allProducts.where(_isExclusive).length;
          final merchantsCount = insights?.advertiserProductsCount ?? allProducts.where((p) => !_isExclusive(p)).length;
          final topSellingCount = allProducts.where((p) => (insights?.forProduct(p.id).salesVolume ?? 0) > 0).length;

          final isMobile = MediaQuery.of(context).size.width < 800;

          return CustomScrollView(
            slivers: [
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(20, 20, 20, 16),
                sliver: SliverList(
                  delegate: SliverChildListDelegate([
                    // --- 1. Top Screen Header ---
                    if (isMobile)
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  gradient: const LinearGradient(colors: [_kDarkNavy, _kRoyalBlue]),
                                  borderRadius: BorderRadius.circular(16),
                                  boxShadow: [
                                    BoxShadow(color: _kRoyalBlue.withValues(alpha: 0.25), blurRadius: 12, offset: const Offset(0, 4)),
                                  ],
                                ),
                                child: const Icon(Icons.inventory_2_rounded, color: Colors.white, size: 28),
                              ),
                              const SizedBox(width: 16),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text('إدارة الأصناف والترحيل المحاسبي', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900, color: _kDarkNavy)),
                                    const SizedBox(height: 4),
                                    Text('تحكم فوري بكافة الأصناف، المخزون، ودعم المعلنين.', style: TextStyle(color: Colors.grey.shade600, fontSize: 13, fontWeight: FontWeight.w500)),
                                  ],
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 16),
                          Wrap(
                            spacing: 12,
                            runSpacing: 12,
                            children: [
                              OutlinedButton.icon(
                                style: OutlinedButton.styleFrom(
                                  foregroundColor: _kDarkNavy,
                                  side: BorderSide(color: Colors.grey.shade300, width: 1.5),
                                  padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                                onPressed: () => _exportCatalog(filtered),
                                icon: const Icon(Icons.file_download_outlined, size: 20),
                                label: const Text('تصدير كشف CSV', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                              ),
                              FilledButton.icon(
                                style: FilledButton.styleFrom(
                                  backgroundColor: _kEmerald,
                                  padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 15),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                  elevation: 3,
                                  shadowColor: _kEmerald.withValues(alpha: 0.4),
                                ),
                                onPressed: () => _showProductFormDialog(categories: allCategories),
                                icon: const Icon(Icons.add_circle_outline_rounded, size: 22),
                                label: const Text('إضافة صنف جديد', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 15)),
                              ),
                            ],
                          ),
                        ],
                      )
                    else
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(colors: [_kDarkNavy, _kRoyalBlue]),
                              borderRadius: BorderRadius.circular(16),
                              boxShadow: [
                                BoxShadow(color: _kRoyalBlue.withValues(alpha: 0.25), blurRadius: 12, offset: const Offset(0, 4)),
                              ],
                            ),
                            child: const Icon(Icons.inventory_2_rounded, color: Colors.white, size: 28),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text('إدارة الأصناف والترحيل المحاسبي', style: TextStyle(fontSize: 26, fontWeight: FontWeight.w900, color: _kDarkNavy)),
                                const SizedBox(height: 4),
                                Text('تحكم شامل بكتالوج السوق، تقييم المخزون المالي، مبيعات الأصناف، ودعم كبار المعلنين والموردين.', style: TextStyle(color: Colors.grey.shade600, fontSize: 13, fontWeight: FontWeight.w500)),
                              ],
                            ),
                          ),
                          OutlinedButton.icon(
                            style: OutlinedButton.styleFrom(
                              foregroundColor: _kDarkNavy,
                              side: BorderSide(color: Colors.grey.shade300, width: 1.5),
                              padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                            onPressed: () => _exportCatalog(filtered),
                            icon: const Icon(Icons.file_download_outlined, size: 20),
                            label: const Text('تصدير كشف CSV', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                          ),
                          const SizedBox(width: 12),
                          FilledButton.icon(
                            style: FilledButton.styleFrom(
                              backgroundColor: _kEmerald,
                              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 15),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              elevation: 3,
                              shadowColor: _kEmerald.withValues(alpha: 0.4),
                            ),
                            onPressed: () => _showProductFormDialog(categories: allCategories),
                            icon: const Icon(Icons.add_circle_outline_rounded, size: 22),
                            label: const Text('إضافة صنف جديد', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 15)),
                          ),
                        ],
                      ),
                    const SizedBox(height: 20),

                    // --- 2. Live Accounting & Advertiser Metrics Row (5 KPIs) ---
                    SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: Row(
                        children: [
                          _buildStatCard(
                            icon: Icons.monetization_on_rounded,
                            label: 'إجمالي مبيعات الأصناف (GMV)',
                            value: insights != null ? '${_fmtSdg(insights.totalSalesVolume)} ج.س' : '...',
                            color: _kEmerald,
                            badge: 'مبيعات حقيقية',
                          ),
                          const SizedBox(width: 12),
                          _buildStatCard(
                            icon: Icons.warehouse_rounded,
                            label: 'تقييم المخزون المعروض',
                            value: insights != null ? '${_fmtSdg(insights.totalInventoryValue)} ج.س' : '...',
                            color: _kRoyalBlue,
                            badge: insights != null ? '${insights.totalStockUnits} وحدة' : 'المخزون',
                          ),
                          const SizedBox(width: 12),
                          _buildStatCard(
                            icon: Icons.shopping_cart_checkout_rounded,
                            label: 'الوحدات المباعة للعملاء',
                            value: insights != null ? '${insights.totalUnitsSold} قطعة' : '...',
                            color: _kAmberDark,
                            badge: 'حركة التوريد',
                          ),
                          const SizedBox(width: 12),
                          _buildStatCard(
                            icon: Icons.military_tech_rounded,
                            label: 'الصنف الأكثر إيراداً',
                            value: insights?.topSellingProductNameAr ?? 'لا يوجد',
                            color: _kPurple,
                            badge: insights != null ? '${_fmtSdg(insights.topSellingProductSales)} ج.س' : 'الأعلى',
                          ),
                          const SizedBox(width: 12),
                          _buildStatCard(
                            icon: Icons.campaign_rounded,
                            label: 'أصناف المعلنين والشركاء',
                            value: '$merchantsCount صنف',
                            color: _kCrimson,
                            badge: 'سوق الشركاء',
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),

                    // --- 3. FX Trading Desk & Pricing Bar ---
                    Container(
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [Colors.white, Color(0xFFF8FAFC)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.circular(18),
                        border: Border.all(color: Colors.grey.shade200),
                        boxShadow: [
                          BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 16, offset: const Offset(0, 6)),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          if (isMobile)
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.all(8),
                                      decoration: BoxDecoration(
                                        color: _kRoyalBlue.withValues(alpha: 0.1),
                                        borderRadius: BorderRadius.circular(10),
                                      ),
                                      child: const Icon(Icons.currency_exchange_rounded, color: _kRoyalBlue, size: 22),
                                    ),
                                    const SizedBox(width: 12),
                                    const Expanded(child: Text('الترحيل المحاسبي وأسعار الصرف المركزية (FX Core)', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900, color: _kDarkNavy))),
                                  ],
                                ),
                                const SizedBox(height: 12),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                                  decoration: BoxDecoration(
                                    color: _kRoyalBlue.withValues(alpha: 0.08),
                                    borderRadius: BorderRadius.circular(20),
                                    border: Border.all(color: _kRoyalBlue.withValues(alpha: 0.2)),
                                  ),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Container(
                                        width: 8,
                                        height: 8,
                                        decoration: const BoxDecoration(color: _kEmerald, shape: BoxShape.circle),
                                      ),
                                      const SizedBox(width: 8),
                                      Text('السعر الأساسي المعتمد: 1 USD = ${_fmtSdg(_usdRate)} SDG', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w900, color: _kRoyalBlue)),
                                    ],
                                  ),
                                ),
                              ],
                            )
                          else
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(8),
                                  decoration: BoxDecoration(
                                    color: _kRoyalBlue.withValues(alpha: 0.1),
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: const Icon(Icons.currency_exchange_rounded, color: _kRoyalBlue, size: 22),
                                ),
                                const SizedBox(width: 12),
                                const Text('منظومة الترحيل المحاسبي وأسعار الصرف المركزية (FX Core Desk)', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: _kDarkNavy)),
                                const Spacer(),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                                  decoration: BoxDecoration(
                                    color: _kRoyalBlue.withValues(alpha: 0.08),
                                    borderRadius: BorderRadius.circular(20),
                                    border: Border.all(color: _kRoyalBlue.withValues(alpha: 0.2)),
                                  ),
                                  child: Row(
                                    children: [
                                      Container(
                                        width: 8,
                                        height: 8,
                                        decoration: const BoxDecoration(color: _kEmerald, shape: BoxShape.circle),
                                      ),
                                      const SizedBox(width: 8),
                                      Text('السعر الأساسي المعتمد: 1 USD = ${_fmtSdg(_usdRate)} SDG', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w900, color: _kRoyalBlue)),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          const Padding(
                            padding: EdgeInsets.symmetric(vertical: 14),
                            child: Divider(height: 1),
                          ),
                          if (isMobile)
                            Column(
                              children: [
                                Row(
                                  children: [
                                    Expanded(
                                      child: TextField(
                                        controller: _usdRateCtrl,
                                        keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                        decoration: InputDecoration(
                                          labelText: 'سعر صرف الدولار (SDG / USD)',
                                          hintText: 'مثال: 3000',
                                          isDense: true,
                                          filled: true,
                                          fillColor: Colors.white,
                                          prefixIcon: const Icon(Icons.attach_money_rounded, color: _kRoyalBlue, size: 20),
                                          suffixText: 'SDG',
                                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide(color: Colors.grey.shade300)),
                                          contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 10),
                                    FilledButton.icon(
                                      style: FilledButton.styleFrom(
                                        backgroundColor: _kRoyalBlue,
                                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                      ),
                                      onPressed: _onUpdateRate,
                                      icon: const Icon(Icons.sync_rounded, size: 18),
                                      label: const Text('تحديث', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 16),
                                Row(
                                  children: [
                                    Expanded(
                                      child: TextField(
                                        controller: _percentCtrl,
                                        keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: true),
                                        decoration: InputDecoration(
                                          labelText: 'تعديل نسبي على أسعار القائمة (٪)',
                                          hintText: 'مثال: +5 أو -3',
                                          isDense: true,
                                          filled: true,
                                          fillColor: Colors.white,
                                          prefixIcon: const Icon(Icons.percent_rounded, color: _kAmberDark, size: 20),
                                          suffixText: '%',
                                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide(color: Colors.grey.shade300)),
                                          contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 10),
                                    FilledButton.icon(
                                      style: FilledButton.styleFrom(
                                        backgroundColor: _kAmberDark,
                                        padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                      ),
                                      onPressed: _onApplyPercent,
                                      icon: const Icon(Icons.trending_up_rounded, size: 18),
                                      label: const Text('تطبيق النسبة المئوية', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                                    ),
                                  ],
                                ),
                              ],
                            )
                          else
                            Row(
                              children: [
                                // Exchange Rate Controller
                                Expanded(
                                  flex: 3,
                                  child: Row(
                                    children: [
                                      Expanded(
                                        child: TextField(
                                          controller: _usdRateCtrl,
                                          keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                          decoration: InputDecoration(
                                            labelText: 'سعر صرف الدولار (SDG / USD)',
                                            hintText: 'مثال: 3000',
                                            isDense: true,
                                            filled: true,
                                            fillColor: Colors.white,
                                            prefixIcon: const Icon(Icons.attach_money_rounded, color: _kRoyalBlue, size: 20),
                                            suffixText: 'SDG',
                                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide(color: Colors.grey.shade300)),
                                            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                                          ),
                                        ),
                                      ),
                                      const SizedBox(width: 10),
                                      FilledButton.icon(
                                        style: FilledButton.styleFrom(
                                          backgroundColor: _kRoyalBlue,
                                          padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                        ),
                                        onPressed: _onUpdateRate,
                                        icon: const Icon(Icons.sync_rounded, size: 18),
                                        label: const Text('تحديث الصرف والترحيل', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                                      ),
                                    ],
                                  ),
                                ),
                                const SizedBox(width: 24),
                                // Percentage Adjustment Controller
                                Expanded(
                                  flex: 3,
                                  child: Row(
                                    children: [
                                      Expanded(
                                        child: TextField(
                                          controller: _percentCtrl,
                                          keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: true),
                                          decoration: InputDecoration(
                                            labelText: 'تعديل نسبي على أسعار القائمة (٪)',
                                            hintText: 'مثال: +5 أو -3',
                                            isDense: true,
                                            filled: true,
                                            fillColor: Colors.white,
                                            prefixIcon: const Icon(Icons.percent_rounded, color: _kAmberDark, size: 20),
                                            suffixText: '%',
                                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide(color: Colors.grey.shade300)),
                                            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                                          ),
                                        ),
                                      ),
                                      const SizedBox(width: 10),
                                      FilledButton.icon(
                                        style: FilledButton.styleFrom(
                                          backgroundColor: _kAmberDark,
                                          padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                        ),
                                        onPressed: _onApplyPercent,
                                        icon: const Icon(Icons.trending_up_rounded, size: 18),
                                        label: const Text('تطبيق', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),

                    // --- 4. Smart Catalog Filter Hub ---
                    Container(
                      padding: const EdgeInsets.all(18),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.04),
                            blurRadius: 18,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          // Hub Header: Title + Results Badge
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(8),
                                decoration: BoxDecoration(
                                  color: _kRoyalBlue.withValues(alpha: 0.1),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: const Icon(Icons.tune_rounded, color: _kRoyalBlue, size: 20),
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text(
                                      'مركز الفلترة والتصفية الذكية للأصناف',
                                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: _kDarkNavy),
                                    ),
                                    const SizedBox(height: 2),
                                    Text(
                                      'تصفية تفاعلية حسب المصدر، الأقسام الرئيسية والفرعية، المتاجر والمعلنين، والبحث بالاسم',
                                      style: TextStyle(fontSize: 11.5, color: Colors.grey.shade600, fontWeight: FontWeight.w500),
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ],
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFF1F5F9),
                                  borderRadius: BorderRadius.circular(10),
                                  border: Border.all(color: const Color(0xFFE2E8F0)),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Container(
                                      width: 8,
                                      height: 8,
                                      decoration: BoxDecoration(
                                        color: filtered.isNotEmpty ? _kEmerald : _kCrimson,
                                        shape: BoxShape.circle,
                                      ),
                                    ),
                                    const SizedBox(width: 6),
                                    Text(
                                      '${filtered.length} من ${allProducts.length} صنف',
                                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: _kDarkNavy),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 16),

                          // Tier 1: Source & Segment Tabs (Pills)
                          SingleChildScrollView(
                            scrollDirection: Axis.horizontal,
                            child: Row(
                              children: [
                                _buildSegmentTab(0, '🌐 كل الأصناف', Icons.all_inclusive_rounded, _kDarkNavy, allProducts.length),
                                const SizedBox(width: 10),
                                _buildSegmentTab(1, '💎 توريد السيطرة', Icons.verified_rounded, _kRoyalBlue, exclusiveCount),
                                const SizedBox(width: 10),
                                _buildSegmentTab(2, '📢 أصناف المعلنين', Icons.campaign_rounded, _kPurple, merchantsCount),
                                const SizedBox(width: 10),
                                _buildSegmentTab(3, '🔥 الأكثر مبيعاً', Icons.military_tech_rounded, _kEmerald, topSellingCount),
                              ],
                            ),
                          ),
                          const SizedBox(height: 14),

                          // Tier 2: 4 Interactive Filter Dropdowns
                          () {
                            // Subcategories list based on selected category
                            final List<SubCategoryModel> availableSubCats;
                            if (_selectedCategoryId != null) {
                              final cat = allCategories.firstWhere(
                                (c) => c.id == _selectedCategoryId,
                                orElse: () => const CategoryModel(id: '', name: '', nameAr: ''),
                              );
                              availableSubCats = cat.subCategories;
                            } else {
                              availableSubCats = allCategories.expand((c) => c.subCategories).toList();
                            }

                            // Subcategories products count
                            final subOnlyCount = allProducts.where((p) => p.subCategoryId != null && p.subCategoryId!.trim().isNotEmpty).length;

                            final catDropdown = _buildFilterDropdownCard<String?>(
                              label: 'القسم الرئيسي',
                              icon: Icons.category_rounded,
                              accentColor: _kRoyalBlue,
                              value: _selectedCategoryId,
                              items: [
                                DropdownMenuItem<String?>(
                                  value: null,
                                  child: Row(
                                    children: [
                                      const Expanded(child: Text('كافة الأقسام الرئيسية', style: TextStyle(fontWeight: FontWeight.bold), overflow: TextOverflow.ellipsis)),
                                      _buildDropdownCountBadge(allProducts.length, _kRoyalBlue),
                                    ],
                                  ),
                                ),
                                ...allCategories.map((c) {
                                  final cnt = allProducts.where((p) => p.categoryId == c.id).length;
                                  return DropdownMenuItem<String?>(
                                    value: c.id,
                                    child: Row(
                                      children: [
                                        Expanded(child: Text(c.nameAr, overflow: TextOverflow.ellipsis)),
                                        _buildDropdownCountBadge(cnt, _kRoyalBlue),
                                      ],
                                    ),
                                  );
                                }),
                              ],
                              onChanged: (val) {
                                setState(() {
                                  _selectedCategoryId = val;
                                  if (_selectedSubCategoryId != null && _selectedSubCategoryId != '__has_sub__') {
                                    if (val != null) {
                                      final curCat = allCategories.firstWhere(
                                        (c) => c.id == val,
                                        orElse: () => const CategoryModel(id: '', name: '', nameAr: ''),
                                      );
                                      if (!curCat.subCategories.any((s) => s.id == _selectedSubCategoryId)) {
                                        _selectedSubCategoryId = null;
                                      }
                                    }
                                  }
                                });
                              },
                            );

                            final subCatDropdown = _buildFilterDropdownCard<String?>(
                              label: 'القسم الفرعي والماركات',
                              icon: Icons.account_tree_rounded,
                              accentColor: _kAmberDark,
                              value: _selectedSubCategoryId,
                              items: [
                                DropdownMenuItem<String?>(
                                  value: null,
                                  child: Row(
                                    children: [
                                      const Expanded(child: Text('كافة الفروع والتصنيفات', style: TextStyle(fontWeight: FontWeight.bold), overflow: TextOverflow.ellipsis)),
                                      _buildDropdownCountBadge(allProducts.length, _kAmberDark),
                                    ],
                                  ),
                                ),
                                DropdownMenuItem<String?>(
                                  value: '__has_sub__',
                                  child: Row(
                                    children: [
                                      const Expanded(child: Text('🏷️ الأقسام الفرعية فقط', style: TextStyle(fontWeight: FontWeight.bold, color: _kAmberDark), overflow: TextOverflow.ellipsis)),
                                      _buildDropdownCountBadge(subOnlyCount, _kAmberDark),
                                    ],
                                  ),
                                ),
                                ...availableSubCats.map((s) {
                                  final cnt = allProducts.where((p) => p.subCategoryId == s.id).length;
                                  final parentName = _selectedCategoryId == null
                                      ? ' (${allCategories.firstWhere((c) => c.id == s.categoryId, orElse: () => const CategoryModel(id: '', name: '', nameAr: '')).nameAr})'
                                      : '';
                                  return DropdownMenuItem<String?>(
                                    value: s.id,
                                    child: Row(
                                      children: [
                                        Expanded(child: Text('${s.nameAr}$parentName', overflow: TextOverflow.ellipsis)),
                                        _buildDropdownCountBadge(cnt, _kAmberDark),
                                      ],
                                    ),
                                  );
                                }),
                              ],
                              onChanged: (val) {
                                setState(() {
                                  _selectedSubCategoryId = val;
                                  if (val != null && val != '__has_sub__' && _selectedCategoryId == null) {
                                    for (final c in allCategories) {
                                      if (c.subCategories.any((s) => s.id == val)) {
                                        _selectedCategoryId = c.id;
                                        break;
                                      }
                                    }
                                  }
                                });
                              },
                            );

                            final storeDropdown = _buildFilterDropdownCard<String?>(
                              label: 'المعلن / المتجر',
                              icon: Icons.storefront_rounded,
                              accentColor: _kPurple,
                              value: _selectedStoreId,
                              items: [
                                DropdownMenuItem<String?>(
                                  value: null,
                                  child: Row(
                                    children: [
                                      const Expanded(child: Text('كافة المتاجر والمعلنين', style: TextStyle(fontWeight: FontWeight.bold), overflow: TextOverflow.ellipsis)),
                                      _buildDropdownCountBadge(allProducts.length, _kPurple),
                                    ],
                                  ),
                                ),
                                ...storeOptions.map((st) {
                                  return DropdownMenuItem<String?>(
                                    value: st.id,
                                    child: Row(
                                      children: [
                                        Expanded(child: Text('📢 ${st.name}', overflow: TextOverflow.ellipsis)),
                                        _buildDropdownCountBadge(st.count, _kPurple),
                                      ],
                                    ),
                                  );
                                }),
                              ],
                              onChanged: (val) => setState(() => _selectedStoreId = val),
                            );

                            final sortDropdown = _buildFilterDropdownCard<String>(
                              label: 'الترتيب والفرز',
                              icon: Icons.sort_rounded,
                              accentColor: _kDarkNavy,
                              value: _sortBy,
                              items: const [
                                DropdownMenuItem(value: 'newest', child: Text('الأحدث إضافة', style: TextStyle(fontWeight: FontWeight.bold))),
                                DropdownMenuItem(value: 'sales_desc', child: Text('الأعلى مبيعاً (ج.س)')),
                                DropdownMenuItem(value: 'units_desc', child: Text('الأكثر طلباً (وحدات)')),
                                DropdownMenuItem(value: 'valuation_desc', child: Text('الأعلى قيمة مخزون')),
                                DropdownMenuItem(value: 'price_asc', child: Text('الأقل سعراً')),
                                DropdownMenuItem(value: 'price_desc', child: Text('الأعلى سعراً')),
                                DropdownMenuItem(value: 'in_stock', child: Text('المتوفر أولاً')),
                                DropdownMenuItem(value: 'name_asc', child: Text('أبجدياً (أ-ي)')),
                              ],
                              onChanged: (val) {
                                if (val != null) setState(() => _sortBy = val);
                              },
                            );

                            if (isMobile) {
                              return Column(
                                children: [
                                  Row(
                                    children: [
                                      Expanded(child: catDropdown),
                                      const SizedBox(width: 8),
                                      Expanded(child: subCatDropdown),
                                    ],
                                  ),
                                  const SizedBox(height: 8),
                                  Row(
                                    children: [
                                      Expanded(child: storeDropdown),
                                      const SizedBox(width: 8),
                                      Expanded(child: sortDropdown),
                                    ],
                                  ),
                                ],
                              );
                            } else {
                              return Row(
                                children: [
                                  Expanded(child: catDropdown),
                                  const SizedBox(width: 10),
                                  Expanded(child: subCatDropdown),
                                  const SizedBox(width: 10),
                                  Expanded(child: storeDropdown),
                                  const SizedBox(width: 10),
                                  Expanded(child: sortDropdown),
                                ],
                              );
                            }
                          }(),
                          const SizedBox(height: 14),

                          // Tier 3: Search Bar & View Mode Switcher
                          Row(
                            children: [
                              Expanded(
                                child: TextField(
                                  controller: _searchCtrl,
                                  onChanged: (v) => setState(() => _searchQuery = v.trim()),
                                  decoration: InputDecoration(
                                    hintText: 'ابحث باسم الصنف، الكود، القسم، الماركة، أو المتجر المعلن...',
                                    hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
                                    prefixIcon: const Icon(Icons.search_rounded, color: _kRoyalBlue, size: 22),
                                    suffixIcon: _searchQuery.isNotEmpty
                                        ? IconButton(
                                            icon: const Icon(Icons.clear_rounded, size: 18),
                                            onPressed: () {
                                              _searchCtrl.clear();
                                              setState(() => _searchQuery = '');
                                            },
                                          )
                                        : null,
                                    filled: true,
                                    fillColor: const Color(0xFFF8FAFC),
                                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                                    enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                                    focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: _kRoyalBlue, width: 1.5)),
                                  ),
                                ),
                              ),
                              const SizedBox(width: 12),
                              // View Switcher (Grid vs Table)
                              Container(
                                decoration: BoxDecoration(
                                  color: const Color(0xFFF1F5F9),
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(color: const Color(0xFFE2E8F0)),
                                ),
                                child: Row(
                                  children: [
                                    IconButton(
                                      icon: Icon(Icons.grid_view_rounded, color: _isGridView ? _kRoyalBlue : Colors.grey.shade500, size: 20),
                                      tooltip: 'عرض بطاقات شبكية',
                                      onPressed: () => setState(() => _isGridView = true),
                                    ),
                                    IconButton(
                                      icon: Icon(Icons.table_rows_rounded, color: !_isGridView ? _kRoyalBlue : Colors.grey.shade500, size: 20),
                                      tooltip: 'عرض جدول صفوف',
                                      onPressed: () => setState(() => _isGridView = false),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),

                          // Tier 4: Active Filters Bar & Reset All
                          () {
                            final hasFilters = _selectedSegment != 0 ||
                                _selectedCategoryId != null ||
                                _selectedSubCategoryId != null ||
                                _selectedStoreId != null ||
                                _searchQuery.isNotEmpty ||
                                _sortBy != 'newest';

                            if (!hasFilters) return const SizedBox.shrink();

                            return Padding(
                              padding: const EdgeInsets.only(top: 14),
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFF8FAFC),
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(color: _kRoyalBlue.withValues(alpha: 0.2)),
                                ),
                                child: Row(
                                  children: [
                                    const Icon(Icons.filter_alt_rounded, size: 16, color: _kRoyalBlue),
                                    const SizedBox(width: 6),
                                    const Text('الفلاتر النشطة:', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: _kDarkNavy)),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: SingleChildScrollView(
                                        scrollDirection: Axis.horizontal,
                                        child: Row(
                                          children: [
                                            if (_selectedSegment != 0)
                                              _buildActiveTag(
                                                label: _selectedSegment == 1
                                                    ? 'المصدر: توريد السيطرة'
                                                    : (_selectedSegment == 2 ? 'المصدر: أصناف المعلنين' : 'المصدر: الأكثر مبيعاً'),
                                                color: _selectedSegment == 1 ? _kRoyalBlue : (_selectedSegment == 2 ? _kPurple : _kEmerald),
                                                onDelete: () => setState(() => _selectedSegment = 0),
                                              ),
                                            if (_selectedCategoryId != null) ...[
                                              () {
                                                final cat = allCategories.firstWhere(
                                                  (c) => c.id == _selectedCategoryId,
                                                  orElse: () => const CategoryModel(id: '', name: '', nameAr: ''),
                                                );
                                                return _buildActiveTag(
                                                  label: 'القسم: ${cat.nameAr}',
                                                  color: _kDarkNavy,
                                                  onDelete: () => setState(() {
                                                    _selectedCategoryId = null;
                                                    _selectedSubCategoryId = null;
                                                  }),
                                                );
                                              }(),
                                            ],
                                            if (_selectedSubCategoryId != null) ...[
                                              _buildActiveTag(
                                                label: _selectedSubCategoryId == '__has_sub__'
                                                    ? 'الأقسام الفرعية فقط'
                                                    : 'الفرع: ${_getSubCategoryName(allCategories, _selectedSubCategoryId!)}',
                                                color: _kAmberDark,
                                                onDelete: () => setState(() => _selectedSubCategoryId = null),
                                              ),
                                            ],
                                            if (_selectedStoreId != null) ...[
                                              _buildActiveTag(
                                                label: 'المعلن: ${_getStoreName(storeOptions, _selectedStoreId!)}',
                                                color: _kPurple,
                                                onDelete: () => setState(() => _selectedStoreId = null),
                                              ),
                                            ],
                                            if (_searchQuery.isNotEmpty) ...[
                                              _buildActiveTag(
                                                label: 'بحث: "$_searchQuery"',
                                                color: _kRoyalBlue,
                                                onDelete: () {
                                                  _searchCtrl.clear();
                                                  setState(() => _searchQuery = '');
                                                },
                                              ),
                                            ],
                                            if (_sortBy != 'newest') ...[
                                              _buildActiveTag(
                                                label: 'ترتيب: ${_getSortLabel(_sortBy)}',
                                                color: Colors.grey.shade700,
                                                onDelete: () => setState(() => _sortBy = 'newest'),
                                              ),
                                            ],
                                          ],
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    InkWell(
                                      onTap: _resetAllFilters,
                                      borderRadius: BorderRadius.circular(8),
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                        decoration: BoxDecoration(
                                          color: _kCrimson.withValues(alpha: 0.1),
                                          borderRadius: BorderRadius.circular(8),
                                          border: Border.all(color: _kCrimson.withValues(alpha: 0.3)),
                                        ),
                                        child: const Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            Icon(Icons.refresh_rounded, size: 14, color: _kCrimson),
                                            SizedBox(width: 4),
                                            Text(
                                              'إعادة ضبط الكل',
                                              style: TextStyle(fontSize: 11, fontWeight: FontWeight.w900, color: _kCrimson),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          }(),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                  ]),
                ),
              ),

              // --- 8. Product List: Grid View vs Table View ---
              if (filtered.isEmpty)
                SliverFillRemaining(
                  hasScrollBody: false,
                  child: Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Container(
                          padding: const EdgeInsets.all(20),
                          decoration: BoxDecoration(
                            color: _kRoyalBlue.withValues(alpha: 0.08),
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(Icons.search_off_rounded, size: 56, color: _kRoyalBlue),
                        ),
                        const SizedBox(height: 16),
                        const Text('لا توجد أصناف مطابقة للبحث أو التصفية المختارة', style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: _kDarkNavy)),
                        const SizedBox(height: 6),
                        Text('جرّب تغيير كلمات البحث أو مسح الفلاتر المختارة', style: TextStyle(fontSize: 13, color: Colors.grey.shade600)),
                        const SizedBox(height: 16),
                        FilledButton.icon(
                          onPressed: () => _showProductFormDialog(categories: allCategories),
                          icon: const Icon(Icons.add_circle_outline_rounded, size: 20),
                          label: const Text('إضافة صنف جديد الآن', style: TextStyle(fontWeight: FontWeight.bold)),
                          style: FilledButton.styleFrom(
                            backgroundColor: _kEmerald,
                            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                        ),
                      ],
                    ),
                  ),
                )
              else if (_isGridView)
                SliverPadding(
                  padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
                  sliver: SliverGrid(
                    gridDelegate: const SliverGridDelegateWithMaxCrossAxisExtent(
                      maxCrossAxisExtent: 310,
                      mainAxisSpacing: 16,
                      crossAxisSpacing: 16,
                      childAspectRatio: 0.65,
                    ),
                    delegate: SliverChildBuilderDelegate(
                      (context, index) {
                        final p = filtered[index];
                        final a = insights != null
                            ? insights.forProduct(p.id)
                            : ProductAnalytics(productId: p.id);
                        return _buildProductGridCard(
                          p: p,
                          isRtl: isRtl,
                          categories: allCategories,
                          analytics: a,
                          insights: insights ?? const AdminProductsInsights(),
                        );
                      },
                      childCount: filtered.length,
                    ),
                  ),
                )
              else
                SliverPadding(
                  padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
                  sliver: SliverToBoxAdapter(
                    child: Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(18),
                        border: Border.all(color: Colors.grey.shade200),
                        boxShadow: [
                          BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 12, offset: const Offset(0, 4)),
                        ],
                      ),
                      clipBehavior: Clip.antiAlias,
                      child: Column(
                        children: [
                          // Table Header
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF8FAFC),
                              border: Border(bottom: BorderSide(color: Colors.grey.shade200)),
                            ),
                            child: const Row(
                              children: [
                                Expanded(flex: 3, child: Text('الصنف والمتجر المعلن', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _kDarkNavy))),
                                Expanded(flex: 2, child: Text('القسم', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _kDarkNavy))),
                                Expanded(flex: 2, child: Text('السعر (USD / SDG)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _kDarkNavy))),
                                Expanded(flex: 2, child: Text('المخزون والتقييم', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _kDarkNavy))),
                                Expanded(flex: 2, child: Text('المبيعات التراكمية', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _kDarkNavy))),
                                Expanded(flex: 1, child: Text('الحالة', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _kDarkNavy))),
                                SizedBox(width: 120, child: Center(child: Text('الإجراءات', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _kDarkNavy)))),
                              ],
                            ),
                          ),
                          // Table Rows
                          ListView.separated(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            itemCount: filtered.length,
                            separatorBuilder: (context, index) => Divider(height: 1, color: Colors.grey.shade100),
                            itemBuilder: (context, index) {
                              final p = filtered[index];
                              final a = insights != null
                                  ? insights.forProduct(p.id)
                                  : ProductAnalytics(productId: p.id);
                              return Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
                                child: Row(
                                  children: [
                                    // Product Info & Store Badge
                                    Expanded(
                                      flex: 3,
                                      child: Row(
                                        children: [
                                          ClipRRect(
                                            borderRadius: BorderRadius.circular(10),
                                            child: Container(
                                              width: 48,
                                              height: 48,
                                              color: const Color(0xFFF1F5F9),
                                              child: p.imageUrl != null && p.imageUrl!.isNotEmpty
                                                  ? Image.network(p.imageUrl!, fit: BoxFit.cover, errorBuilder: (context, error, stackTrace) => const Icon(Icons.broken_image, size: 22, color: Colors.grey))
                                                  : const Icon(Icons.inventory_2_rounded, size: 22, color: Colors.grey),
                                            ),
                                          ),
                                          const SizedBox(width: 14),
                                          Expanded(
                                            child: Column(
                                              crossAxisAlignment: CrossAxisAlignment.start,
                                              children: [
                                                Text(p.nameAr, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: _kDarkNavy)),
                                                const SizedBox(height: 2),
                                                Container(
                                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                                                  decoration: BoxDecoration(
                                                    color: p.isMagaieesExclusive
                                                        ? _kRoyalBlue.withValues(alpha: 0.08)
                                                        : _kPurple.withValues(alpha: 0.08),
                                                    borderRadius: BorderRadius.circular(6),
                                                  ),
                                                  child: Text(
                                                    p.isMagaieesExclusive
                                                        ? '🌟 توريد السيطرة الحصري'
                                                        : ('📢 ${a.advertiserStoreName}'),
                                                    style: TextStyle(
                                                      fontSize: 10.5,
                                                      fontWeight: FontWeight.bold,
                                                      color: p.isMagaieesExclusive ? _kRoyalBlue : _kPurple,
                                                    ),
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                    // Category
                                    Expanded(
                                      flex: 2,
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(p.categoryNameAr.isNotEmpty ? p.categoryNameAr : p.categoryName, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: _kDarkNavy)),
                                          if (p.subCategoryNameAr != null && p.subCategoryNameAr!.isNotEmpty)
                                            Text(p.subCategoryNameAr!, style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
                                        ],
                                      ),
                                    ),
                                    // Price USD & SDG
                                    // Price Local Currency Only (SDG)
                                    Expanded(
                                      flex: 2,
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text('${_fmtSdg(p.pricePerTon)} ج.س', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w900, color: _kEmeraldDark)),
                                          if (p.unitAr != null && p.unitAr!.isNotEmpty)
                                            Text('/ ${UnitFormatter.format(p.unitAr, p.unit, isRtl: true)}', style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                                        ],
                                      ),
                                    ),
                                    // Stock Quantity & Inventory Valuation
                                    Expanded(
                                      flex: 2,
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                            decoration: BoxDecoration(
                                              color: p.inStock ? _kEmerald.withValues(alpha: 0.08) : _kCrimson.withValues(alpha: 0.08),
                                              borderRadius: BorderRadius.circular(8),
                                            ),
                                            child: Text(
                                              p.inStock ? '${p.stockQuantity} ${p.unitAr ?? ""}' : 'نفد الرصيد',
                                              style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: p.inStock ? _kEmeraldDark : _kCrimson),
                                            ),
                                          ),
                                          const SizedBox(height: 2),
                                          Text(
                                            'تقييم: ${_fmtSdg(a.inventoryValue)} ج.س',
                                            style: TextStyle(fontSize: 10.5, color: Colors.grey.shade600, fontWeight: FontWeight.w600),
                                          ),
                                        ],
                                      ),
                                    ),
                                    // Cumulative Sales & Units Sold
                                    Expanded(
                                      flex: 2,
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            a.salesVolume > 0 ? '${_fmtSdg(a.salesVolume)} ج.س' : 'لا مبيعات',
                                            style: TextStyle(
                                              fontSize: 13,
                                              fontWeight: FontWeight.w900,
                                              color: a.salesVolume > 0 ? _kEmeraldDark : Colors.grey.shade500,
                                            ),
                                          ),
                                          if (a.unitsSold > 0)
                                            Text(
                                              '${a.unitsSold} قطعة مباعة',
                                              style: TextStyle(fontSize: 10.5, color: Colors.grey.shade600, fontWeight: FontWeight.w500),
                                            ),
                                        ],
                                      ),
                                    ),
                                    // Stock Toggle
                                    Expanded(
                                      flex: 1,
                                      child: InkWell(
                                        onTap: () => _toggleStock(p),
                                        borderRadius: BorderRadius.circular(20),
                                        child: Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                          decoration: BoxDecoration(
                                            color: p.inStock ? _kEmerald.withValues(alpha: 0.1) : _kCrimson.withValues(alpha: 0.1),
                                            borderRadius: BorderRadius.circular(20),
                                          ),
                                          child: Row(
                                            mainAxisSize: MainAxisSize.min,
                                            children: [
                                              Container(width: 6, height: 6, decoration: BoxDecoration(shape: BoxShape.circle, color: p.inStock ? _kEmerald : _kCrimson)),
                                              const SizedBox(width: 6),
                                              Text(p.inStock ? 'متوفر' : 'معطل', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: p.inStock ? _kEmeraldDark : _kCrimson)),
                                            ],
                                          ),
                                        ),
                                      ),
                                    ),
                                    // Actions (Financial, Edit, Delete)
                                    SizedBox(
                                      width: 120,
                                      child: Row(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          IconButton(
                                            icon: const Icon(Icons.analytics_outlined, color: _kPurple, size: 20),
                                            tooltip: 'التقرير المالي للصنف',
                                            onPressed: () => _showProductFinancialDialog(
                                              context: context,
                                              product: p,
                                              analytics: a,
                                              insights: insights ?? const AdminProductsInsights(),
                                            ),
                                            splashRadius: 18,
                                          ),
                                          IconButton(
                                            icon: const Icon(Icons.edit_note_rounded, color: _kRoyalBlue, size: 22),
                                            tooltip: 'تعديل الصنف والمخزون',
                                            onPressed: () => _showProductFormDialog(categories: allCategories, existingProduct: p),
                                            splashRadius: 18,
                                          ),
                                          IconButton(
                                            icon: const Icon(Icons.delete_outline_rounded, color: _kCrimson, size: 20),
                                            tooltip: 'حذف الصنف',
                                            onPressed: () => _deleteProduct(p, isRtl),
                                            splashRadius: 18,
                                          ),
                                        ],
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
                ),
            ],
          );
        },
      ),
    );
  }
}

