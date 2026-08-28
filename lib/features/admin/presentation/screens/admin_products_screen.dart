import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/product_card_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/category_model.dart';
import 'package:magaiees_marketplace/features/admin/pricing/application/pricing_provider.dart';

const Color _kDarkNavy = Color(0xFF0F172A);
const Color _kRoyalBlue = Color(0xFF1E40AF);
const Color _kEmerald = Color(0xFF059669);
const Color _kAmber = Color(0xFFF59E0B);

class AdminProductsScreen extends ConsumerStatefulWidget {
  final String? initialSearchQuery;
  const AdminProductsScreen({super.key, this.initialSearchQuery});

  @override
  ConsumerState<AdminProductsScreen> createState() => _AdminProductsScreenState();
}

class _AdminProductsScreenState extends ConsumerState<AdminProductsScreen> {
  int _selectedSegment = 0; // 0: Exclusive, 1: Merchants, 2: All

  // USD is the BASE price. SDG = USD × rate
  late double _usdRate;
  late final TextEditingController _usdRateCtrl;
  final _percentCtrl = TextEditingController(text: '0');
  final _searchCtrl = TextEditingController();
  String _searchQuery = '';


  String? _selectedCategoryId;

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
  String _fmtUsd(double v) => v.toStringAsFixed(2);

  List<ProductCardModel> _filter(List<ProductCardModel> all) {
    Iterable<ProductCardModel> s = all;
    if (_selectedSegment == 0) s = s.where(_isExclusive);
    if (_selectedSegment == 1) s = s.where((p) => !_isExclusive(p));
    if (_selectedCategoryId != null) s = s.where((p) => p.categoryId == _selectedCategoryId);
    if (_searchQuery.isNotEmpty) {
      final q = _searchQuery.toLowerCase();
      s = s.where((p) =>
          p.nameAr.contains(q) ||
          p.name.toLowerCase().contains(q) ||
          p.categoryNameAr.contains(q) ||
          (p.subCategoryNameAr ?? '').contains(q));
    }
    return s.toList();
  }

  void _onUpdateRate() {
    final rate = double.tryParse(_usdRateCtrl.text);
    if (rate == null || rate <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('الرجاء إدخال سعر صرف صحيح'), backgroundColor: Colors.red),
      );
      return;
    }
    setState(() => _usdRate = rate);
    
    // Update locally and globally
    ref.read(pricingProvider.notifier).updateUsdRate(rate);
    ref.read(productsProvider.notifier).applyGlobalPricing(rate);

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('✅ تم تحديث سعر الصرف: 1 USD = ${_fmtSdg(rate)} SDG — تم تطبيق التأثير المحاسبي فوراً!'),
        backgroundColor: _kRoyalBlue,
      ),
    );
  }

  void _onApplyPercent() {
    final pct = double.tryParse(_percentCtrl.text);
    if (pct == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('الرجاء إدخال نسبة صحيحة'), backgroundColor: Colors.red),
      );
      return;
    }
    
    // Apply globally via provider
    ref.read(productsProvider.notifier).applyGlobalPricing(_usdRate, percentOffset: pct);

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('✅ تم تطبيق ${pct >= 0 ? "زيادة" : "خفض"} بنسبة ${pct.abs()}% بنجاح.'),
        backgroundColor: _kEmerald,
      ),
    );
  }

  void _toggleStock(ProductCardModel product) {
    final newStatus = !product.inStock;
    // Update globally
    ref.read(productsProvider.notifier).updateProduct(product.copyWith(inStock: newStatus));

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('تم تغيير حالة الصنف "${product.nameAr}" إلى (${newStatus ? "متوفر بالمخزون 🟢" : "نفد من المخزون 🔴"})'),
        backgroundColor: newStatus ? _kEmerald : Colors.red.shade700,
        duration: const Duration(seconds: 3),
      ),
    );
  }

  void _deleteProduct(ProductCardModel product, bool isRtl) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(isRtl ? 'تأكيد حذف الصنف' : 'Delete Product', style: const TextStyle(fontWeight: FontWeight.bold)),
        content: Text(isRtl
            ? 'هل أنت متأكد من حذف الصنف "${product.nameAr}" من قائمة الأسعار نهائياً؟'
            : 'Are you sure you want to delete "${product.name}"?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: Text(isRtl ? 'إلغاء' : 'Cancel', style: TextStyle(color: Colors.grey.shade600))),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: Colors.red.shade600, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8))),
            onPressed: () {
              ref.read(productsProvider.notifier).deleteProduct(product.id);
              Navigator.pop(ctx);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('تم حذف الصنف "${product.nameAr}" نهائياً.'),
                  backgroundColor: Colors.red.shade600,
                ),
              );
            },
            child: Text(isRtl ? 'حذف' : 'Delete'),
          ),
        ],
      ),
    );
  }

  void _showEditPriceDialog(ProductCardModel product) {
    final currentUsd = product.priceInUsd ?? (product.pricePerTon / _usdRate);
    final currentSdg = product.pricePerTon;

    final usdCtrl = TextEditingController(text: currentUsd.toStringAsFixed(2));
    final sdgCtrl = TextEditingController(text: currentSdg.toStringAsFixed(0));
    
    bool isUpdating = false;

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDlgState) {
          return Directionality(
            textDirection: TextDirection.rtl,
            child: AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              title: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(color: _kRoyalBlue.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(10)),
                    child: const Icon(Icons.edit_note, color: _kRoyalBlue),
                  ),
                  const SizedBox(width: 12),
                  const Expanded(child: Text('تعديل سعر الصنف', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18))),
                ],
              ),
              content: SizedBox(
                width: 420,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(color: Colors.grey.shade50, borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.grey.shade200)),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            product.nameAr.isNotEmpty ? product.nameAr : product.name,
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            '${product.categoryNameAr} · ${product.subCategoryNameAr ?? product.subCategoryName ?? ""}',
                            style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 24),
                    // USD Price Input
                    TextField(
                      controller: usdCtrl,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true),
                      onChanged: (val) {
                        if (isUpdating) return;
                        isUpdating = true;
                        final parsed = double.tryParse(val) ?? 0.0;
                        sdgCtrl.text = (parsed * _usdRate).toStringAsFixed(0);
                        isUpdating = false;
                        setDlgState(() {});
                      },
                      decoration: InputDecoration(
                        labelText: 'السعر بالدولار (USD)',
                        labelStyle: const TextStyle(fontWeight: FontWeight.w500),
                        prefixIcon: const Icon(Icons.attach_money, color: _kRoyalBlue),
                        suffixText: 'USD',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                        filled: true,
                        fillColor: _kRoyalBlue.withValues(alpha: 0.05),
                      ),
                    ),
                    const SizedBox(height: 16),
                    // SDG Price Input
                    TextField(
                      controller: sdgCtrl,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true),
                      onChanged: (val) {
                        if (isUpdating) return;
                        isUpdating = true;
                        final parsed = double.tryParse(val) ?? 0.0;
                        usdCtrl.text = (parsed / _usdRate).toStringAsFixed(2);
                        isUpdating = false;
                        setDlgState(() {});
                      },
                      decoration: InputDecoration(
                        labelText: 'السعر المحلي (SDG)',
                        labelStyle: const TextStyle(fontWeight: FontWeight.w500),
                        prefixIcon: const Icon(Icons.money, color: _kEmerald),
                        suffixText: 'SDG',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                        filled: true,
                        fillColor: _kEmerald.withValues(alpha: 0.05),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Center(
                      child: Text(
                        'سعر الصرف الحالي: 1 USD = ${_fmtSdg(_usdRate)} SDG',
                        style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey.shade600),
                      ),
                    ),
                  ],
                ),
              ),
              actionsPadding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(ctx), 
                  child: Text('إلغاء', style: TextStyle(color: Colors.grey.shade600, fontWeight: FontWeight.bold))
                ),
                FilledButton.icon(
                  style: FilledButton.styleFrom(
                    backgroundColor: _kRoyalBlue,
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10))
                  ),
                  onPressed: () {
                    final newUsd = double.tryParse(usdCtrl.text);
                    final newSdg = double.tryParse(sdgCtrl.text);
                    if (newUsd == null || newUsd <= 0 || newSdg == null || newSdg <= 0) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('الرجاء إدخال أسعار صحيحة'), backgroundColor: Colors.red),
                      );
                      return;
                    }
                    
                    ref.read(productsProvider.notifier).updateProduct(
                      product.copyWith(
                        priceInUsd: newUsd,
                        pricePerTon: newSdg,
                      )
                    );

                    Navigator.pop(ctx);
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text('✅ تم تحديث "${product.nameAr}" بنجاح.'),
                        backgroundColor: _kEmerald,
                      ),
                    );
                  },
                  icon: const Icon(Icons.save, size: 20),
                  label: const Text('حفظ ونشر السعر', style: TextStyle(fontWeight: FontWeight.bold)),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  void _showAddItemDialog(List<CategoryModel> categories) {
    final nameArCtrl = TextEditingController();
    final nameEnCtrl = TextEditingController();
    final usdPriceCtrl = TextEditingController();
    final brandCtrl = TextEditingController();
    String selectedCatId = categories.isNotEmpty ? categories.first.id : '1';
    String selectedUnit = 'طن';
    final initialCat = categories.isNotEmpty ? categories.first : null;
    bool isExclusive = _selectedSegment == 0 || (initialCat?.isExclusiveToMagaiees ?? false);

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDlgState) {
          final selectedCat = categories.firstWhere(
            (c) => c.id == selectedCatId,
            orElse: () => categories.first,
          );

          return Directionality(
            textDirection: TextDirection.rtl,
            child: AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              title: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(color: _kEmerald.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(10)),
                    child: const Icon(Icons.add_circle, color: _kEmerald),
                  ),
                  const SizedBox(width: 12),
                  const Text('إضافة صنف جديد', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
                ],
              ),
              content: SizedBox(
                width: 500,
                child: SingleChildScrollView(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      DropdownButtonFormField<String>(
                        decoration: InputDecoration(
                          labelText: 'القسم الرئيسي',
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                          prefixIcon: const Icon(Icons.category, color: _kDarkNavy),
                        ),
                        value: selectedCatId,
                        items: categories.map((cat) {
                          return DropdownMenuItem(
                            value: cat.id,
                            child: Text(cat.nameAr),
                          );
                        }).toList(),
                        onChanged: (val) {
                          if (val != null) {
                            setDlgState(() {
                              selectedCatId = val;
                              _selectedSubCatId = null;
                              final cat = categories.firstWhere((c) => c.id == val, orElse: () => selectedCat);
                              isExclusive = _selectedSegment == 0 || cat.isExclusiveToMagaiees;
                            });
                          }
                        },
                      ),
                      const SizedBox(height: 16),
                      Builder(
                        builder: (ctx) {
                          final cat = categories.firstWhere((c) => c.id == selectedCatId, orElse: () => categories.first);
                          if (cat.subCategories.isEmpty) {
                            return const SizedBox.shrink();
                          }
                          return Padding(
                            padding: const EdgeInsets.only(bottom: 16.0),
                            child: DropdownButtonFormField<String>(
                              value: _selectedSubCatId,
                              decoration: InputDecoration(
                                labelText: 'القسم الفرعي',
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                                prefixIcon: const Icon(Icons.subdirectory_arrow_right, color: _kDarkNavy),
                              ),
                              items: cat.subCategories.map((sub) {
                                return DropdownMenuItem(
                                  value: sub.id.toString(),
                                  child: Text(sub.nameAr),
                                );
                              }).toList(),
                              onChanged: (val) {
                                setDlgState(() {
                                  _selectedSubCatId = val;
                                });
                              },
                            ),
                          );
                        }
                      ),
                      TextField(
                      const SizedBox(height: 16),
                      TextField(
                        controller: nameArCtrl,
                        decoration: InputDecoration(
                          labelText: 'اسم ومواصفات الصنف (بالعربية)',
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                          prefixIcon: const Icon(Icons.edit_note, color: _kDarkNavy),
                        ),
                      ),
                      const SizedBox(height: 16),
                      TextField(
                        controller: nameEnCtrl,
                        decoration: InputDecoration(
                          labelText: 'اسم الصنف (بالإنجليزية)',
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                          prefixIcon: const Icon(Icons.language, color: _kDarkNavy),
                        ),
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            flex: 3,
                            child: TextField(
                              controller: usdPriceCtrl,
                              keyboardType: const TextInputType.numberWithOptions(decimal: true),
                              decoration: InputDecoration(
                                labelText: 'السعر (USD)',
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                                prefixIcon: const Icon(Icons.attach_money, color: _kDarkNavy),
                                suffixText: 'USD',
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            flex: 2,
                            child: DropdownButtonFormField<String>(
                              decoration: InputDecoration(
                                labelText: 'الوحدة',
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                              value: selectedUnit,
                              items: const [
                                DropdownMenuItem(value: 'طن', child: Text('طن')),
                                DropdownMenuItem(value: 'كيس', child: Text('كيس')),
                                DropdownMenuItem(value: 'م²', child: Text('م²')),
                                DropdownMenuItem(value: 'لوح', child: Text('لوح')),
                                DropdownMenuItem(value: 'عود', child: Text('عود')),
                                DropdownMenuItem(value: 'حبة', child: Text('حبة')),
                              ],
                              onChanged: (val) {
                                if (val != null) setDlgState(() => selectedUnit = val);
                              },
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      Container(
                        decoration: BoxDecoration(
                          color: isExclusive ? _kRoyalBlue.withValues(alpha: 0.05) : Colors.grey.shade50,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: isExclusive ? _kRoyalBlue.withValues(alpha: 0.3) : Colors.grey.shade200)
                        ),
                        child: SwitchListTile(
                          title: const Text('توريد حصري لشركة مقاييس', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
                          subtitle: const Text('تمييز الصنف بشارة مقاييس الرسمية', style: TextStyle(fontSize: 12)),
                          value: isExclusive,
                          onChanged: (val) => setDlgState(() => isExclusive = val),
                          activeColor: _kRoyalBlue,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              actionsPadding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
              actions: [
                TextButton(onPressed: () => Navigator.pop(ctx), child: Text('إلغاء', style: TextStyle(color: Colors.grey.shade600, fontWeight: FontWeight.bold))),
                FilledButton.icon(
                  style: FilledButton.styleFrom(
                    backgroundColor: _kEmerald,
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10))
                  ),
                  onPressed: () {
                    final nameAr = nameArCtrl.text.trim();
                    final usdPrice = double.tryParse(usdPriceCtrl.text) ?? 100.0;
                    if (nameAr.isEmpty) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('الرجاء إدخال اسم الصنف'), backgroundColor: Colors.red),
                      );
                      return;
                    }

                    final selectedSubCat = _selectedSubCatId != null 
                        ? selectedCat.subCategories.cast<SubCategoryModel?>().firstWhere((s) => s?.id.toString() == _selectedSubCatId, orElse: () => null)
                        : null;

                    final newProduct = ProductCardModel(
                      id: 'custom_prod_${DateTime.now().millisecondsSinceEpoch}',
                      name: nameEnCtrl.text.trim().isNotEmpty ? nameEnCtrl.text.trim() : nameAr,
                      nameAr: nameAr,
                      categoryId: selectedCat.id,
                      categoryName: selectedCat.name,
                      categoryNameAr: selectedCat.nameAr,
                      subCategoryId: _selectedSubCatId,
                      subCategoryName: selectedSubCat?.name,
                      subCategoryNameAr: selectedSubCat?.nameAr,
                      pricePerTon: usdPrice * _usdRate,
                      priceInUsd: usdPrice,
                      unitAr: 'ج.س/$selectedUnit',
                      unit: 'SDG/$selectedUnit',
                      merchantId: isExclusive ? 'm_magaiees' : 'm_custom',
                      storeName: isExclusive ? 'Magaiees Direct Supply' : 'المتجر المعتمد',
                      storeNameAr: isExclusive ? 'شركة مقاييس - توريد مباشر' : 'المتجر المعتمد',
                      imageUrl: '',
                      inStock: true,
                      isMagaieesExclusive: isExclusive,
                    );

                    // Update globally
                    ref.read(productsProvider.notifier).addProduct(newProduct);

                    Navigator.pop(ctx);
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text('✅ تمت إضافة الصنف "$nameAr" ونشره فوراً في السوق.'),
                        backgroundColor: _kEmerald,
                      ),
                    );
                  },
                  icon: const Icon(Icons.check, size: 20),
                  label: const Text('نشر الصنف الآن', style: TextStyle(fontWeight: FontWeight.bold)),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildProfessionalTab(int index, String title, IconData icon, Color activeColor) {
    final isSelected = _selectedSegment == index;
    return Expanded(
      child: InkWell(
        onTap: () => setState(() {
          _selectedSegment = index;
          _selectedCategoryId = null;
        }),
        borderRadius: BorderRadius.circular(12),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 250),
          padding: const EdgeInsets.symmetric(vertical: 14),
          decoration: BoxDecoration(
            color: isSelected ? activeColor : Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: isSelected ? activeColor : Colors.grey.shade300,
              width: 1.5,
            ),
            boxShadow: isSelected
                ? [
                    BoxShadow(
                      color: activeColor.withValues(alpha: 0.25),
                      blurRadius: 12,
                      offset: const Offset(0, 4),
                    )
                  ]
                : [],
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                icon,
                size: 20,
                color: isSelected ? Colors.white : Colors.grey.shade600,
              ),
              const SizedBox(width: 8),
              Text(
                title,
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.bold,
                  color: isSelected ? Colors.white : Colors.grey.shade700,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildProfessionalCategoryChip(String? id, String label) {
    final isSelected = _selectedCategoryId == id;
    return InkWell(
      onTap: () => setState(() => _selectedCategoryId = isSelected ? null : id),
      borderRadius: BorderRadius.circular(24),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
        decoration: BoxDecoration(
          color: isSelected ? _kDarkNavy : Colors.white,
          borderRadius: BorderRadius.circular(24),
          border: Border.all(color: isSelected ? _kDarkNavy : Colors.grey.shade300, width: 1.5),
          boxShadow: isSelected ? [BoxShadow(color: _kDarkNavy.withValues(alpha: 0.2), blurRadius: 8, offset: const Offset(0, 2))] : [],
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 13,
            fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
            color: isSelected ? Colors.white : Colors.grey.shade600,
          ),
        ),
      ),
    );
  }

  Widget _buildDashboardControl({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    required String buttonLabel,
    required VoidCallback onPressed,
    required Color color,
    String? suffixText,
  }) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: color.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, color: color, size: 20),
        ),
        const SizedBox(width: 12),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(label, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _kDarkNavy)),
            const SizedBox(height: 4),
            SizedBox(
              width: 120,
              height: 36,
              child: TextField(
                controller: controller,
                keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: true),
                decoration: InputDecoration(
                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 0),
                  filled: true,
                  fillColor: Colors.white,
                  suffixText: suffixText,
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: BorderSide(color: Colors.grey.shade300)),
                  enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: BorderSide(color: Colors.grey.shade300)),
                  focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: BorderSide(color: color)),
                ),
                style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        const SizedBox(width: 12),
        FilledButton(
          style: FilledButton.styleFrom(
            backgroundColor: color, 
            padding: const EdgeInsets.symmetric(horizontal: 16),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            elevation: 0,
          ),
          onPressed: onPressed,
          child: Text(buttonLabel, style: const TextStyle(fontWeight: FontWeight.bold)),
        ),
      ],
    );
  }

  Widget _stat(IconData icon, String label, String value, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        decoration: BoxDecoration(
          color: Colors.white, 
          borderRadius: BorderRadius.circular(16), 
          border: Border.all(color: Colors.grey.shade200),
          boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 10, offset: const Offset(0, 4))]
        ),
        child: Row(children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(12)
            ),
            child: Icon(icon, size: 24, color: color),
          ),
          const SizedBox(width: 16),
          Flexible(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(value, style: TextStyle(fontWeight: FontWeight.w900, fontSize: 22, color: _kDarkNavy)),
            const SizedBox(height: 2),
            Text(label, style: TextStyle(fontSize: 13, color: Colors.grey.shade600, fontWeight: FontWeight.w500), overflow: TextOverflow.ellipsis),
          ])),
        ]),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
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

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: productsAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('خطأ: $e')),
        data: (allProducts) {
          final allCategories = categoriesAsync.valueOrNull ?? [];
          final filtered = _filter(allProducts);

          final inStock = filtered.where((p) => p.inStock).length;
          final totalCount = allProducts.length;

          return CustomScrollView(
            slivers: [
              SliverPadding(
                padding: const EdgeInsets.all(24),
                sliver: SliverList(
                  delegate: SliverChildListDelegate([
                    // Header
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('إدارة الأصناف والتسعير المحاسبي', style: TextStyle(fontSize: 28, fontWeight: FontWeight.w900, color: _kDarkNavy)),
                              const SizedBox(height: 8),
                              Text('تحكم مركزي بالأسعار، المخزون، وسعر الصرف. أي تعديل هنا ينعكس فورياً في تطبيق العملاء.', style: TextStyle(color: Colors.grey.shade600, fontSize: 14, fontWeight: FontWeight.w500)),
                            ],
                          ),
                        ),
                        FilledButton.icon(
                          style: FilledButton.styleFrom(
                            backgroundColor: _kEmerald, 
                            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            elevation: 2,
                            shadowColor: _kEmerald.withValues(alpha: 0.5)
                          ),
                          onPressed: () => _showAddItemDialog(allCategories),
                          icon: const Icon(Icons.add, size: 22),
                          label: const Text('إضافة صنف للسوق', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                        ),
                      ],
                    ),
                    const SizedBox(height: 24),
                    
                    // Stats Row
                    Row(
                      children: [
                        _stat(Icons.inventory_2, 'الأصناف الحالية', '${filtered.length}', _kRoyalBlue),
                        const SizedBox(width: 16),
                        _stat(Icons.check_circle, 'متوفر بالمخزون', '$inStock', _kEmerald),
                        const SizedBox(width: 16),
                        _stat(Icons.cancel, 'نفد من المخزون', '${filtered.length - inStock}', Colors.red.shade600),
                        const SizedBox(width: 16),
                        _stat(Icons.dataset, 'إجمالي الكتالوج', '$totalCount', _kAmber),
                      ],
                    ),
                    const SizedBox(height: 24),

                    // FX & Pricing Control Bar
                    Container(
                      padding: const EdgeInsets.all(24),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: Colors.grey.shade200),
                        boxShadow: [
                          BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 24, offset: const Offset(0, 8))
                        ]
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.analytics, color: _kDarkNavy),
                              const SizedBox(width: 8),
                              const Text('لوحة التحكم المالي والتسعير (FX Trading Desk)', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: _kDarkNavy)),
                              const Spacer(),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                                decoration: BoxDecoration(
                                  color: _kDarkNavy.withValues(alpha: 0.05),
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Row(
                                  children: [
                                    Container(
                                      width: 8, height: 8, 
                                      decoration: const BoxDecoration(color: _kEmerald, shape: BoxShape.circle)
                                    ),
                                    const SizedBox(width: 8),
                                    Text('السعر المعتمد: 1 USD = ${_fmtSdg(_usdRate)} SDG', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: _kDarkNavy)),
                                  ],
                                ),
                              ),
                            ],
                          ),
                          const Padding(
                            padding: EdgeInsets.symmetric(vertical: 16),
                            child: Divider(height: 1),
                          ),
                          Wrap(
                            spacing: 32,
                            runSpacing: 16,
                            children: [
                              _buildDashboardControl(
                                controller: _usdRateCtrl,
                                label: 'تحديث سعر الصرف الأساسي',
                                icon: Icons.currency_exchange,
                                buttonLabel: 'تحديث السعر',
                                color: _kRoyalBlue,
                                onPressed: _onUpdateRate,
                                suffixText: 'SDG',
                              ),
                              _buildDashboardControl(
                                controller: _percentCtrl,
                                label: 'تعديل أسعار القائمة بنسبة مئوية',
                                icon: Icons.percent,
                                buttonLabel: 'تطبيق النسبة',
                                color: _kAmber,
                                onPressed: _onApplyPercent,
                                suffixText: '%',
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Professional Tabs Row
                    Row(
                      children: [
                        _buildProfessionalTab(0, '🌟 حصرياً لمقاييس', Icons.verified, _kRoyalBlue),
                        const SizedBox(width: 16),
                        _buildProfessionalTab(1, '🏪 سوق التجار', Icons.storefront, _kDarkNavy),
                        const SizedBox(width: 16),
                        _buildProfessionalTab(2, '🌐 كل الأصناف', Icons.all_inclusive, Colors.grey.shade600),
                      ],
                    ),
                    const SizedBox(height: 24),

                    // Search & Filters
                    Row(
                      children: [
                        Expanded(
                          flex: 2,
                          child: TextField(
                            controller: _searchCtrl,
                            onChanged: (v) => setState(() => _searchQuery = v.trim()),
                            decoration: InputDecoration(
                              hintText: 'ابحث بالاسم، القسم، أو الماركة...',
                              hintStyle: TextStyle(color: Colors.grey.shade400),
                              prefixIcon: const Icon(Icons.search, color: _kDarkNavy),
                              suffixIcon: _searchQuery.isNotEmpty 
                                ? IconButton(icon: const Icon(Icons.clear, size: 20), onPressed: () { _searchCtrl.clear(); setState(() => _searchQuery = ''); }) 
                                : null,
                              filled: true, 
                              fillColor: Colors.white,
                              contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide(color: Colors.grey.shade200)),
                              enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide(color: Colors.grey.shade200)),
                              focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: _kDarkNavy, width: 2)),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    // Categories Wrap
                    Wrap(
                      spacing: 10,
                      runSpacing: 10,
                      children: [
                        _buildProfessionalCategoryChip(null, 'الكل'),
                        ...allCategories.map((cat) => _buildProfessionalCategoryChip(cat.id, cat.nameAr)),
                      ],
                    ),
                    const SizedBox(height: 24),
                  ]),
                ),
              ),

              // Data Table as Custom Sliver List
              if (filtered.isEmpty)
                SliverFillRemaining(
                  hasScrollBody: false,
                  child: Center(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.inventory_2_outlined, size: 80, color: Colors.grey.shade300),
                        const SizedBox(height: 16),
                        Text('لا توجد أصناف مطابقة', style: TextStyle(color: Colors.grey.shade500, fontSize: 18, fontWeight: FontWeight.bold)),
                      ]
                    ),
                  )
                )
              else
                SliverPadding(
                  padding: const EdgeInsets.only(left: 24, right: 24, bottom: 48),
                  sliver: SliverToBoxAdapter(
                    child: Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: Colors.grey.shade200),
                        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 16, offset: const Offset(0, 8))],
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            // Header Row
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
                              decoration: const BoxDecoration(
                                color: _kDarkNavy,
                              ),
                              child: Row(
                                children: [
                                  const SizedBox(width: 40, child: Text('#', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold))),
                                  const Expanded(flex: 3, child: Text('الصنف والمواصفات', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold))),
                                  const Expanded(flex: 2, child: Text('القسم والماركة', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold))),
                                  const Expanded(flex: 2, child: Text('السعر (USD)', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold))),
                                  const Expanded(flex: 2, child: Text('السعر المحلي (SDG)', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold))),
                                  const SizedBox(width: 120, child: Text('حالة المخزون', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold))),
                                  const SizedBox(width: 100, child: Text('الإجراءات', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold), textAlign: TextAlign.center)),
                                ],
                              ),
                            ),
                            // Rows
                            ListView.separated(
                              shrinkWrap: true,
                              physics: const NeverScrollableScrollPhysics(),
                              itemCount: filtered.length,
                              separatorBuilder: (context, index) => Divider(height: 1, color: Colors.grey.shade100),
                              itemBuilder: (context, i) {
                                final p = filtered[i];
                                final usdPrice = p.priceInUsd ?? (p.pricePerTon / _usdRate);
                                final sdgPrice = p.pricePerTon;

                                return Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
                                  color: i.isEven ? Colors.white : Colors.grey.shade50,
                                  child: Row(
                                    children: [
                                      SizedBox(
                                        width: 40, 
                                        child: Text('${i + 1}', style: TextStyle(color: Colors.grey.shade500, fontWeight: FontWeight.bold))
                                      ),
                                      Expanded(
                                        flex: 3, 
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(p.nameAr.isNotEmpty ? p.nameAr : p.name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: _kDarkNavy)),
                                            if (p.isMagaieesExclusive)
                                              Container(
                                                margin: const EdgeInsets.only(top: 4),
                                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                                decoration: BoxDecoration(color: _kRoyalBlue.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(4)),
                                                child: const Text('توريد مقاييس', style: TextStyle(fontSize: 10, color: _kRoyalBlue, fontWeight: FontWeight.bold)),
                                              )
                                          ],
                                        )
                                      ),
                                      Expanded(
                                        flex: 2, 
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(p.categoryNameAr.isNotEmpty ? p.categoryNameAr : p.categoryName, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: _kDarkNavy)),
                                            const SizedBox(height: 2),
                                            Text(p.subCategoryNameAr ?? p.subCategoryName ?? '—', style: TextStyle(color: Colors.grey.shade500, fontSize: 12)),
                                          ],
                                        )
                                      ),
                                      Expanded(
                                        flex: 2, 
                                        child: Text('\$ ${_fmtUsd(usdPrice)}', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.blueGrey.shade600, fontSize: 14))
                                      ),
                                      Expanded(
                                        flex: 2, 
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(_fmtSdg(sdgPrice), style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 15, color: _kEmerald)),
                                            Text(p.unitAr ?? p.unit ?? 'طن', style: TextStyle(fontSize: 12, color: Colors.grey.shade500)),
                                          ],
                                        )
                                      ),
                                      SizedBox(
                                        width: 120, 
                                        child: InkWell(
                                          onTap: () => _toggleStock(p),
                                          borderRadius: BorderRadius.circular(20),
                                          child: Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                            decoration: BoxDecoration(
                                              color: p.inStock ? _kEmerald.withValues(alpha: 0.1) : Colors.red.withValues(alpha: 0.1),
                                              borderRadius: BorderRadius.circular(20),
                                              border: Border.all(color: p.inStock ? _kEmerald.withValues(alpha: 0.3) : Colors.red.withValues(alpha: 0.3)),
                                            ),
                                            child: Row(
                                              mainAxisSize: MainAxisSize.min,
                                              children: [
                                                Container(width: 8, height: 8, decoration: BoxDecoration(shape: BoxShape.circle, color: p.inStock ? _kEmerald : Colors.red)),
                                                const SizedBox(width: 8),
                                                Text(
                                                  p.inStock ? 'متوفر' : 'نفد',
                                                  style: TextStyle(
                                                    fontSize: 12,
                                                    fontWeight: FontWeight.bold,
                                                    color: p.inStock ? _kEmerald : Colors.red.shade700,
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ),
                                        ),
                                      ),
                                      SizedBox(
                                        width: 100, 
                                        child: Row(
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            IconButton(
                                              icon: const Icon(Icons.edit_note, color: _kRoyalBlue, size: 22),
                                              tooltip: 'تعديل السعر',
                                              onPressed: () => _showEditPriceDialog(p),
                                              splashRadius: 20,
                                            ),
                                            IconButton(
                                              icon: Icon(Icons.delete_outline, color: Colors.red.shade400, size: 22),
                                              tooltip: 'حذف الصنف',
                                              onPressed: () => _deleteProduct(p, true),
                                              splashRadius: 20,
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
                ),
            ],
          );
        },
      ),
    );
  }
}
