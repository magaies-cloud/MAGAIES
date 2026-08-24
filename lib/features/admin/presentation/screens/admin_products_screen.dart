import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/product_card_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/category_model.dart';
import 'package:magaiees_marketplace/features/admin/pricing/application/pricing_provider.dart';

const Color _kBlue = Color(0xFF1E40AF);
const Color _kGreen = Color(0xFF059669);
const Color _kAmber = Color(0xFFF59E0B);
const Set<String> _kExclusiveIds = {'1', '2', '6'};

class AdminProductsScreen extends ConsumerStatefulWidget {
  const AdminProductsScreen({super.key});

  @override
  ConsumerState<AdminProductsScreen> createState() => _AdminProductsScreenState();
}

class _AdminProductsScreenState extends ConsumerState<AdminProductsScreen> {
  int _selectedSegment = 0; // 0: Exclusive, 1: Merchants, 2: All

  // USD is the BASE price. SDG = USD × rate
  double _usdRate = 6000.0;

  // Pricing tool panel visibility
  bool _showRateTool = false;
  bool _showPercentTool = false;

  final _usdRateCtrl = TextEditingController(text: '6000');
  final _percentCtrl = TextEditingController(text: '0');
  final _searchCtrl = TextEditingController();
  String _searchQuery = '';
  String? _selectedCategoryId;

  @override
  void dispose() {
    _usdRateCtrl.dispose();
    _percentCtrl.dispose();
    _searchCtrl.dispose();
    super.dispose();
  }

  bool _isExclusive(ProductCardModel p) =>
      p.isMagaieesExclusive || _kExclusiveIds.contains(p.categoryId);

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
        content: Text('✅ تم تحديث سعر الصرف: 1 USD = ${_fmtSdg(rate)} SDG — تم تطبيق التأثير المحاسبي فوراً على كافة واجهات المستخدم!'),
        backgroundColor: _kBlue,
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
        content: Text('✅ تم تطبيق ${pct >= 0 ? "زيادة" : "خفض"} بنسبة ${pct.abs()}% بنجاح. التأثير المحاسبي ظهر الآن لدى كافة المستخدمين.'),
        backgroundColor: _kGreen,
      ),
    );
  }

  void _toggleStock(ProductCardModel product) {
    final newStatus = !product.inStock;
    // Update globally
    ref.read(productsProvider.notifier).updateProduct(product.copyWith(inStock: newStatus));

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('تم تغيير حالة الصنف "${product.nameAr}" إلى (${newStatus ? "متوفر بالمخزون 🟢" : "نفد من المخزون 🔴"}) — التحديث فوري لدى العملاء.'),
        backgroundColor: newStatus ? _kGreen : Colors.red.shade700,
        duration: const Duration(seconds: 3),
      ),
    );
  }

  void _deleteProduct(ProductCardModel product, bool isRtl) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(isRtl ? 'تأكيد حذف الصنف' : 'Delete Product'),
        content: Text(isRtl
            ? 'هل أنت متأكد من حذف الصنف "${product.nameAr}" من قائمة الأسعار نهائياً؟'
            : 'Are you sure you want to delete "${product.name}"?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: Text(isRtl ? 'إلغاء' : 'Cancel')),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: Colors.red),
            onPressed: () {
              ref.read(productsProvider.notifier).deleteProduct(product.id);
              Navigator.pop(ctx);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('تم حذف الصنف "${product.nameAr}" نهائياً وتمت إزالته من العرض.'),
                  backgroundColor: Colors.red,
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
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              title: Row(
                children: [
                  const Icon(Icons.edit, color: _kBlue),
                  const SizedBox(width: 8),
                  const Expanded(child: Text('تعديل سعر الصنف')),
                ],
              ),
              content: SizedBox(
                width: 420,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      product.nameAr.isNotEmpty ? product.nameAr : product.name,
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '${product.categoryNameAr} · ${product.subCategoryNameAr ?? product.subCategoryName ?? ""}',
                      style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                    ),
                    const SizedBox(height: 20),
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
                        prefixIcon: const Icon(Icons.attach_money),
                        suffixText: 'USD',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        filled: true,
                        fillColor: const Color(0xFFF0F4FF),
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
                        labelText: 'السعر المحلي بالجنيه (SDG)',
                        prefixIcon: const Icon(Icons.money),
                        suffixText: 'SDG',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        filled: true,
                        fillColor: const Color(0xFFF0FDF4),
                      ),
                    ),
                    const SizedBox(height: 10),
                    Text(
                      'سعر الصرف الحالي: 1 USD = ${_fmtSdg(_usdRate)} SDG',
                      style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('إلغاء')),
                FilledButton.icon(
                  style: FilledButton.styleFrom(backgroundColor: _kBlue),
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
                        backgroundColor: _kGreen,
                      ),
                    );
                  },
                  icon: const Icon(Icons.save),
                  label: const Text('حفظ ونشر السعر'),
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
    bool isExclusive = _selectedSegment == 0 || _kExclusiveIds.contains(selectedCatId);

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
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              title: Row(
                children: [
                  const Icon(Icons.add_circle, color: _kGreen),
                  const SizedBox(width: 8),
                  const Text('إضافة صنف جديد لقائمة الأسعار'),
                ],
              ),
              content: SizedBox(
                width: 500,
                child: SingleChildScrollView(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      DropdownButtonFormField<String>(
                        decoration: const InputDecoration(
                          labelText: 'القسم الرئيسي',
                          border: OutlineInputBorder(),
                          prefixIcon: Icon(Icons.category),
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
                              isExclusive = _kExclusiveIds.contains(val);
                            });
                          }
                        },
                      ),
                      const SizedBox(height: 12),
                      TextField(
                        controller: brandCtrl,
                        decoration: const InputDecoration(
                          labelText: 'الماركة / القسم الفرعي (مثلاً: حديد الأسعد أو أسمنت عطبرة)',
                          border: OutlineInputBorder(),
                          prefixIcon: Icon(Icons.branding_watermark),
                        ),
                      ),
                      const SizedBox(height: 12),
                      TextField(
                        controller: nameArCtrl,
                        decoration: const InputDecoration(
                          labelText: 'اسم ومواصفات الصنف (بالعربية)',
                          border: OutlineInputBorder(),
                          prefixIcon: Icon(Icons.edit_note),
                        ),
                      ),
                      const SizedBox(height: 12),
                      TextField(
                        controller: nameEnCtrl,
                        decoration: const InputDecoration(
                          labelText: 'اسم الصنف (بالإنجليزية)',
                          border: OutlineInputBorder(),
                          prefixIcon: Icon(Icons.language),
                        ),
                      ),
                      const SizedBox(height: 12),
                      Row(
                        children: [
                          Expanded(
                            flex: 3,
                            child: TextField(
                              controller: usdPriceCtrl,
                              keyboardType: const TextInputType.numberWithOptions(decimal: true),
                              decoration: const InputDecoration(
                                labelText: 'السعر بالدولار (USD)',
                                border: OutlineInputBorder(),
                                prefixIcon: Icon(Icons.attach_money),
                                suffixText: 'USD',
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            flex: 2,
                            child: DropdownButtonFormField<String>(
                              decoration: const InputDecoration(
                                labelText: 'الوحدة',
                                border: OutlineInputBorder(),
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
                      const SizedBox(height: 12),
                      SwitchListTile(
                        title: const Text('توريد حصري لشركة مقاييس', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                        subtitle: const Text('تمييز الصنف بشارة مقاييس الرسمية', style: TextStyle(fontSize: 11)),
                        value: isExclusive,
                        onChanged: (val) => setDlgState(() => isExclusive = val),
                        activeColor: _kBlue,
                      ),
                    ],
                  ),
                ),
              ),
              actions: [
                TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('إلغاء')),
                FilledButton.icon(
                  style: FilledButton.styleFrom(backgroundColor: _kGreen),
                  onPressed: () {
                    final nameAr = nameArCtrl.text.trim();
                    final usdPrice = double.tryParse(usdPriceCtrl.text) ?? 100.0;
                    if (nameAr.isEmpty) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('الرجاء إدخال اسم الصنف'), backgroundColor: Colors.red),
                      );
                      return;
                    }

                    final newProduct = ProductCardModel(
                      id: 'custom_prod_${DateTime.now().millisecondsSinceEpoch}',
                      name: nameEnCtrl.text.trim().isNotEmpty ? nameEnCtrl.text.trim() : nameAr,
                      nameAr: nameAr,
                      categoryId: selectedCat.id,
                      categoryName: selectedCat.name,
                      categoryNameAr: selectedCat.nameAr,
                      subCategoryId: 'sub_custom',
                      subCategoryName: brandCtrl.text.trim(),
                      subCategoryNameAr: brandCtrl.text.trim().isNotEmpty ? brandCtrl.text.trim() : 'عام',
                      pricePerTon: usdPrice * _usdRate,
                      priceInUsd: usdPrice,
                      unitAr: 'ج.س/$selectedUnit',
                      unit: 'SDG/$selectedUnit',
                      merchantId: isExclusive ? 'm_magaiees' : 'm_custom',
                      storeName: isExclusive ? 'Magaiees Direct Supply' : 'المتجر المعتمد',
                      storeNameAr: isExclusive ? 'شركة مقاييس - توريد مباشر' : 'المتجر المعتمد',
                      imageUrl: 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?w=600',
                      inStock: true,
                      isMagaieesExclusive: isExclusive,
                    );

                    // Update globally
                    ref.read(productsProvider.notifier).addProduct(newProduct);

                    Navigator.pop(ctx);
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text('✅ تمت إضافة الصنف "$nameAr" ونشره فوراً في السوق.'),
                        backgroundColor: _kGreen,
                      ),
                    );
                  },
                  icon: const Icon(Icons.check),
                  label: const Text('نشر الصنف الآن'),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  // Beautiful Tab Builder
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
          padding: const EdgeInsets.symmetric(vertical: 12),
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
                      color: activeColor.withValues(alpha: 0.3),
                      blurRadius: 8,
                      offset: const Offset(0, 3),
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

  // Beautiful Category Chip Builder
  Widget _buildProfessionalCategoryChip(String? id, String label) {
    final isSelected = _selectedCategoryId == id;
    return InkWell(
      onTap: () => setState(() => _selectedCategoryId = isSelected ? null : id),
      borderRadius: BorderRadius.circular(20),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? _kBlue : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: isSelected ? _kBlue : Colors.grey.shade300, width: 1.5),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 13,
            fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
            color: isSelected ? Colors.white : Colors.grey.shade600,
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
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

          return SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header
                Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('إدارة الأصناف والتسعير المحاسبي', style: Theme.of(context).textTheme.headlineMedium?.copyWith(fontWeight: FontWeight.bold, color: const Color(0xFF0F172A))),
                          const SizedBox(height: 6),
                          Text('تحكم مركزي بالأسعار، المخزون، وسعر الصرف. أي تعديل هنا ينعكس فورياً في تطبيق العملاء.', style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                        ],
                      ),
                    ),
                    FilledButton.icon(
                      style: FilledButton.styleFrom(backgroundColor: _kGreen, padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14)),
                      onPressed: () => _showAddItemDialog(allCategories),
                      icon: const Icon(Icons.add, size: 20),
                      label: const Text('إضافة صنف للسوق', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                    ),
                  ],
                ),
                const SizedBox(height: 24),

                // Professional Tabs Row
                Row(
                  children: [
                    _buildProfessionalTab(0, '🌟 حصرياً لمقاييس (توريد مباشر)', Icons.verified, _kBlue),
                    const SizedBox(width: 12),
                    _buildProfessionalTab(1, '🏪 سوق التجار والموردين', Icons.storefront, Colors.indigo),
                    const SizedBox(width: 12),
                    _buildProfessionalTab(2, '🌐 عرض كل الأصناف', Icons.all_inclusive, Colors.grey.shade800),
                  ],
                ),
                const SizedBox(height: 16),

                // Compact Exchange Rate Tool
                if (_selectedSegment == 0)
                  Container(
                    margin: const EdgeInsets.only(bottom: 16),
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF0F4FF),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: _kBlue.withValues(alpha: 0.2)),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.currency_exchange, color: _kBlue, size: 24),
                        const SizedBox(width: 12),
                        const Text('سعر الصرف:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                        const SizedBox(width: 12),
                        SizedBox(
                          width: 120,
                          height: 40,
                          child: TextField(
                            controller: _usdRateCtrl,
                            keyboardType: const TextInputType.numberWithOptions(decimal: true),
                            decoration: InputDecoration(
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 0),
                              filled: true,
                              fillColor: Colors.white,
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(6)),
                            ),
                            style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                          ),
                        ),
                        const SizedBox(width: 12),
                        FilledButton(
                          style: FilledButton.styleFrom(backgroundColor: _kBlue, padding: const EdgeInsets.symmetric(horizontal: 16)),
                          onPressed: _onUpdateRate,
                          child: const Text('تحديث السعر', style: TextStyle(fontWeight: FontWeight.bold)),
                        ),
                        const Spacer(),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                          decoration: BoxDecoration(
                            color: _kAmber.withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text('سعر الصرف المعتمد حالياً: 1 USD = ${_fmtSdg(_usdRate)} SDG', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.amber.shade900)),
                        ),
                      ],
                    ),
                  ),

                // Search & Category Chips (Categories UNDER search)
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Search Bar
                    TextField(
                      controller: _searchCtrl,
                      onChanged: (v) => setState(() => _searchQuery = v.trim()),
                      decoration: InputDecoration(
                        hintText: 'بحث عن صنف بالاسم، القسم، أو الماركة...',
                        prefixIcon: const Icon(Icons.search),
                        suffixIcon: _searchQuery.isNotEmpty ? IconButton(icon: const Icon(Icons.clear, size: 20), onPressed: () { _searchCtrl.clear(); setState(() => _searchQuery = ''); }) : null,
                        filled: true, fillColor: Colors.white,
                        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade300)),
                      ),
                    ),
                    const SizedBox(height: 16),
                    // Categories as Wrap
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: [
                        _buildProfessionalCategoryChip(null, 'الكل'),
                        ...allCategories.map((cat) => _buildProfessionalCategoryChip(cat.id, cat.nameAr)),
                      ],
                    ),
                  ],
                ),
                
                const SizedBox(height: 24),

                // Stats
                Row(
                  children: [
                    _stat(Icons.inventory_2, 'الأصناف في هذه القائمة', '${filtered.length}', _kBlue),
                    const SizedBox(width: 12),
                    _stat(Icons.check_circle, 'متوفر بالمخزون', '$inStock', _kGreen),
                    const SizedBox(width: 12),
                    _stat(Icons.cancel, 'نفد من المخزون', '${filtered.length - inStock}', Colors.red.shade600),
                    const SizedBox(width: 12),
                    _stat(Icons.dataset, 'إجمالي الكتالوج بالسوق', '$totalCount', _kAmber),
                  ],
                ),
                const SizedBox(height: 16),

                // Data Table
                filtered.isEmpty
                    ? Center(child: Padding(
                        padding: const EdgeInsets.all(48.0),
                        child: Column(mainAxisSize: MainAxisSize.min, children: [
                          Icon(Icons.inventory_2_outlined, size: 64, color: Colors.grey.shade400),
                          const SizedBox(height: 12),
                          Text('لا توجد أصناف مطابقة', style: TextStyle(color: Colors.grey.shade500, fontSize: 16)),
                        ]),
                      ))
                    : Container(
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: Colors.grey.shade200),
                          boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 10, offset: const Offset(0, 4))],
                        ),
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(14),
                          child: SingleChildScrollView(
                            scrollDirection: Axis.horizontal,
                            child: ConstrainedBox(
                              constraints: BoxConstraints(minWidth: MediaQuery.of(context).size.width - 140),
                              child: DataTable(
                                headingRowColor: WidgetStatePropertyAll(Colors.grey.shade50),
                                headingTextStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Colors.black87),
                                dataTextStyle: const TextStyle(fontSize: 14, color: Colors.black87),
                                columnSpacing: 18,
                                horizontalMargin: 16,
                                dataRowMinHeight: 40,
                                dataRowMaxHeight: 52,
                                border: TableBorder(horizontalInside: BorderSide(color: Colors.grey.shade100, width: 1)),
                                columns: const [
                                  DataColumn(label: Text('#')),
                                  DataColumn(label: Text('اسم ومواصفات الصنف')),
                                  DataColumn(label: Text('القسم الرئيسي')),
                                  DataColumn(label: Text('الماركة / الفرعي')),
                                  DataColumn(label: Text('السعر (USD)'), numeric: true),
                                  DataColumn(label: Text('السعر المحلي (SDG)'), numeric: true),
                                  DataColumn(label: Text('الوحدة')),
                                  DataColumn(label: Text('المخزون')),
                                  DataColumn(label: Text('الإجراءات')),
                                ],
                                rows: List.generate(filtered.length, (i) {
                                  final p = filtered[i];
                                  final usdPrice = p.priceInUsd ?? (p.pricePerTon / _usdRate);
                                  final sdgPrice = p.pricePerTon;

                                  return DataRow(
                                    color: WidgetStatePropertyAll(i.isEven ? Colors.white : Colors.grey.shade50),
                                    cells: [
                                      DataCell(Text('${i + 1}')),
                                      DataCell(ConstrainedBox(
                                        constraints: const BoxConstraints(maxWidth: 240),
                                        child: Text(p.nameAr.isNotEmpty ? p.nameAr : p.name, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                                      )),
                                      DataCell(Text(p.categoryNameAr.isNotEmpty ? p.categoryNameAr : p.categoryName, style: TextStyle(color: Colors.grey.shade700, fontSize: 13))),
                                      DataCell(Text(p.subCategoryNameAr ?? p.subCategoryName ?? '—', style: TextStyle(color: Colors.grey.shade600, fontSize: 13))),
                                      DataCell(Text('\$ ${_fmtUsd(usdPrice)}', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.blueGrey.shade700, fontSize: 14))),
                                      DataCell(Text(_fmtSdg(sdgPrice), style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 15, color: _kGreen))),
                                      DataCell(Text(p.unitAr ?? p.unit ?? 'طن', style: const TextStyle(fontSize: 13))),
                                      // Interactive Stock Badge
                                      DataCell(
                                        InkWell(
                                          onTap: () => _toggleStock(p),
                                          borderRadius: BorderRadius.circular(12),
                                          child: Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                            decoration: BoxDecoration(
                                              color: p.inStock ? _kGreen.withValues(alpha: 0.1) : Colors.red.withValues(alpha: 0.1),
                                              borderRadius: BorderRadius.circular(12),
                                              border: Border.all(color: p.inStock ? _kGreen.withValues(alpha: 0.3) : Colors.red.withValues(alpha: 0.3)),
                                            ),
                                            child: Row(
                                              mainAxisSize: MainAxisSize.min,
                                              children: [
                                                Container(width: 8, height: 8, decoration: BoxDecoration(shape: BoxShape.circle, color: p.inStock ? _kGreen : Colors.red)),
                                                const SizedBox(width: 6),
                                                Text(
                                                  p.inStock ? 'متوفر 🟢' : 'نفد 🔴',
                                                  style: TextStyle(
                                                    fontSize: 11,
                                                    fontWeight: FontWeight.bold,
                                                    color: p.inStock ? _kGreen : Colors.red.shade700,
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ),
                                        ),
                                      ),
                                      // Actions
                                      DataCell(
                                        Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            IconButton(
                                              icon: const Icon(Icons.edit_note, color: _kBlue, size: 20),
                                              tooltip: 'تعديل السعر',
                                              onPressed: () => _showEditPriceDialog(p),
                                            ),
                                            IconButton(
                                              icon: Icon(Icons.delete_outline, color: Colors.red.shade400, size: 20),
                                              tooltip: 'حذف الصنف',
                                              onPressed: () => _deleteProduct(p, true),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],
                                  );
                                }),
                              ),
                            ),
                          ),
                        ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _stat(IconData icon, String label, String value, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(color: color.withValues(alpha: 0.08), borderRadius: BorderRadius.circular(10), border: Border.all(color: color.withValues(alpha: 0.2))),
        child: Row(children: [
          Icon(icon, size: 20, color: color),
          const SizedBox(width: 8),
          Flexible(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(value, style: TextStyle(fontWeight: FontWeight.w900, fontSize: 18, color: color)),
            Text(label, style: TextStyle(fontSize: 11, color: Colors.grey.shade700), overflow: TextOverflow.ellipsis),
          ])),
        ]),
      ),
    );
  }
}
