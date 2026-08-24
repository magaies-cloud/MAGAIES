import 'dart:async';
import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import 'package:go_router/go_router.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/product_card_model.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';

// Provider for Store Info (to get USD rate)
final merchantStoreInfoProvider = FutureProvider.autoDispose<Map<String, dynamic>?>((ref) async {
  final user = ref.watch(authNotifierProvider).valueOrNull;
  if (user == null) return null;

  final storeRes = await Supabase.instance.client
      .from('stores')
      .select('*')
      .eq('owner_id', user.id)
      .maybeSingle();
      
  return storeRes;
});

// Provider for Products (Realtime listener for merchant as well)
final merchantInventoryProvider = StreamProvider.autoDispose<List<Map<String, dynamic>>>((ref) {
  final user = ref.watch(authNotifierProvider).valueOrNull;
  if (user == null) return Stream.value([]);

  final controller = StreamController<List<Map<String, dynamic>>>();
  String? storeId;

  Future<void> fetch() async {
    if (storeId == null) {
      final s = await Supabase.instance.client.from('stores').select('id').eq('owner_id', user.id).maybeSingle();
      if (s == null) return;
      storeId = s['id']?.toString();
    }
    if (storeId == null) return;
    
    final res = await Supabase.instance.client.from('products').select('*').eq('store_id', storeId!).order('created_at', ascending: false);
    if (!controller.isClosed) controller.add(List<Map<String, dynamic>>.from(res));
  }

  fetch();

  final channel = Supabase.instance.client
      .channel('merchant_inventory')
      .onPostgresChanges(
        event: PostgresChangeEvent.all,
        schema: 'public',
        table: 'products',
        callback: (payload) => fetch(),
      )
      .subscribe();

  ref.onDispose(() {
    channel.unsubscribe();
    controller.close();
  });

  return controller.stream;
});

class MerchantInventoryScreen extends ConsumerStatefulWidget {
  const MerchantInventoryScreen({super.key});

  @override
  ConsumerState<MerchantInventoryScreen> createState() => _MerchantInventoryScreenState();
}

class _MerchantInventoryScreenState extends ConsumerState<MerchantInventoryScreen> {

  Future<void> _generateAndPrintPDF(List<Map<String, dynamic>> products, double exchangeRate, bool isRtl) async {
    final doc = pw.Document();
    
    // Load font for Arabic support (using default for now, printing handles basic text)
    // Note: For perfect Arabic in PDF, a custom TTF font is usually needed, but this is a solid start.
    final font = await PdfGoogleFonts.cairoRegular();

    doc.addPage(
      pw.Page(
        pageFormat: PdfPageFormat.a4,
        build: (pw.Context context) {
          return pw.Column(
            crossAxisAlignment: pw.CrossAxisAlignment.start,
            children: [
              pw.Header(
                level: 0,
                child: pw.Text(
                  isRtl ? 'قائمة الأسعار والمخزون' : 'Price & Inventory List', 
                  style: pw.TextStyle(font: font, fontSize: 24, fontWeight: pw.FontWeight.bold),
                  textDirection: isRtl ? pw.TextDirection.rtl : pw.TextDirection.ltr,
                ),
              ),
              pw.Text(
                '${isRtl ? 'سعر الصرف المعتمد:' : 'Exchange Rate:'} 1\$ = ${NumberFormat('#,##0').format(exchangeRate)} SDG',
                style: pw.TextStyle(font: font, fontSize: 14),
                textDirection: isRtl ? pw.TextDirection.rtl : pw.TextDirection.ltr,
              ),
              pw.SizedBox(height: 20),
              pw.TableHelper.fromTextArray(
                context: context,
                border: pw.TableBorder.all(color: PdfColors.grey400, width: 0.5),
                headerStyle: pw.TextStyle(font: font, fontWeight: pw.FontWeight.bold, color: PdfColors.white),
                headerDecoration: const pw.BoxDecoration(color: PdfColors.blueGrey800),
                cellStyle: pw.TextStyle(font: font),
                cellAlignment: pw.Alignment.center,
                headers: isRtl 
                    ? ['الكمية', 'السعر (\$)', 'السعر (ج.س)', 'اسم الصنف']
                    : ['Product Name', 'Price (SDG)', 'Price (USD)', 'Stock'],
                data: products.map((p) {
                  final name = isRtl ? (p['name_ar'] ?? p['name']) : (p['name'] ?? p['name_ar']);
                  final sdg = NumberFormat('#,##0').format(double.tryParse(p['price_sdg']?.toString() ?? '0') ?? 0);
                  final usd = double.tryParse(p['price_usd']?.toString() ?? '0') ?? 0;
                  final stock = p['stock']?.toString() ?? '0';
                  
                  return isRtl 
                      ? [stock, '\$${usd.toStringAsFixed(2)}', sdg, name]
                      : [name, sdg, '\$${usd.toStringAsFixed(2)}', stock];
                }).toList(),
              ),
              pw.SizedBox(height: 20),
              pw.Text(
                '${isRtl ? 'تاريخ الإصدار:' : 'Generated on:'} ${DateFormat('yyyy-MM-dd HH:mm').format(DateTime.now())}',
                style: pw.TextStyle(font: font, fontSize: 10, color: PdfColors.grey700),
                textDirection: isRtl ? pw.TextDirection.rtl : pw.TextDirection.ltr,
              ),
            ],
          );
        },
      ),
    );

    await Printing.layoutPdf(
      onLayout: (PdfPageFormat format) async => doc.save(),
      name: 'Magaiees_PriceList_${DateFormat('yyyyMMdd').format(DateTime.now())}.pdf',
    );
  }

  Future<void> _updateGlobalRate(String storeId, double currentRate, bool isRtl) async {
    final rateCtrl = TextEditingController(text: currentRate.toString());
    final result = await showDialog<double?>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(isRtl ? 'تحديث سعر صرف الدولار' : 'Update USD Exchange Rate'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              isRtl 
                ? 'تحديث سعر الصرف سيقوم بإعادة حساب أسعار جميع المنتجات بالجنيه (ج.س) بناءً على سعرها بالدولار.' 
                : 'Updating the exchange rate will recalculate all SDG prices based on their USD prices.',
              style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: rateCtrl,
              decoration: InputDecoration(
                labelText: isRtl ? 'سعر الدولار الجديد (ج.س)' : 'New USD Rate (SDG)',
                prefixIcon: const Icon(Icons.currency_exchange),
                border: const OutlineInputBorder(),
              ),
              keyboardType: TextInputType.number,
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: Text(isRtl ? 'إلغاء' : 'Cancel')),
          FilledButton(
            onPressed: () => Navigator.pop(context, double.tryParse(rateCtrl.text)),
            child: Text(isRtl ? 'تحديث الكل' : 'Update All'),
          ),
        ],
      ),
    );

    if (result != null && result > 0 && mounted) {
      try {
        await Supabase.instance.client.from('stores').update({'usd_exchange_rate': result}).eq('id', storeId);
        
        final products = await Supabase.instance.client.from('products').select('id, price_usd, price_sdg').eq('store_id', storeId);
        for (var p in products) {
          double usd = double.tryParse(p['price_usd']?.toString() ?? '0') ?? 0;
          double sdg = double.tryParse(p['price_sdg']?.toString() ?? '0') ?? 0;
          
          if (usd == 0 && sdg > 0) {
            usd = sdg / currentRate;
          }
          
          if (usd > 0) {
            final newSdg = usd * result;
            await Supabase.instance.client.from('products').update({
              'price_sdg': newSdg,
              'price_usd': usd,
            }).eq('id', p['id']);
          }
        }

        ref.invalidate(merchantStoreInfoProvider);
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(isRtl ? 'تم تحديث أسعار جميع المنتجات بنجاح!' : 'All product prices updated successfully!'), backgroundColor: Colors.green));
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red));
        }
      }
    }
  }

  Future<void> _applyBulkMarkup(String storeId, bool isRtl) async {
    final pctCtrl = TextEditingController();
    final result = await showDialog<double?>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(isRtl ? 'زيادة الأسعار (نسبة مئوية)' : 'Increase Prices (%)'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              isRtl 
                ? 'أدخل النسبة المئوية لزيادة أسعار جميع المنتجات (دولار وجنيه).' 
                : 'Enter percentage to increase all product prices (USD & SDG).',
              style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: pctCtrl,
              decoration: InputDecoration(
                labelText: isRtl ? 'نسبة الزيادة (%)' : 'Increase (%)',
                prefixIcon: const Icon(Icons.percent),
                border: const OutlineInputBorder(),
              ),
              keyboardType: TextInputType.number,
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: Text(isRtl ? 'إلغاء' : 'Cancel')),
          FilledButton(
            onPressed: () => Navigator.pop(context, double.tryParse(pctCtrl.text)),
            style: FilledButton.styleFrom(backgroundColor: Colors.orange.shade700),
            child: Text(isRtl ? 'تطبيق' : 'Apply'),
          ),
        ],
      ),
    );

    if (result != null && result > 0 && mounted) {
      try {
        final products = await Supabase.instance.client.from('products').select('id, price_usd, price_sdg').eq('store_id', storeId);
        final multiplier = 1 + (result / 100);
        
        for (var p in products) {
          final usd = double.tryParse(p['price_usd']?.toString() ?? '0') ?? 0;
          final sdg = double.tryParse(p['price_sdg']?.toString() ?? '0') ?? 0;
          
          await Supabase.instance.client.from('products').update({
            'price_usd': usd * multiplier,
            'price_sdg': sdg * multiplier,
          }).eq('id', p['id']);
        }
        
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(isRtl ? 'تم تطبيق الزيادة على جميع المنتجات!' : 'Markup applied to all products!'), backgroundColor: Colors.green));
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red));
        }
      }
    }
  }

  void _showEditDialog(Map<String, dynamic> product, double exchangeRate, bool isRtl) {
    double initialUsd = double.tryParse(product['price_usd']?.toString() ?? '0') ?? 0;
    double initialSdg = double.tryParse(product['price_sdg']?.toString() ?? '0') ?? 0;
    
    if (initialUsd == 0 && initialSdg > 0 && exchangeRate > 0) {
      initialUsd = initialSdg / exchangeRate;
    }

    final usdCtrl = TextEditingController(text: initialUsd == 0 ? '' : initialUsd.toStringAsFixed(2));
    final sdgCtrl = TextEditingController(text: initialSdg == 0 ? '' : initialSdg.toStringAsFixed(0));
    final stockCtrl = TextEditingController(text: product['stock']?.toString() ?? '0');

    Uint8List? newImageBytes;
    String? newImageExt;
    bool isUploading = false;
    String? currentImageUrl = (product['images'] != null && (product['images'] as List).isNotEmpty) ? product['images'][0] : null;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (dialogContext) => StatefulBuilder(
        builder: (ctx, setState) {
          return AlertDialog(
            title: Text(isRtl ? 'تعديل المنتج' : 'Edit Product'),
            content: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  InkWell(
                    onTap: () async {
                      try {
                        final picker = ImagePicker();
                        final pickedFile = await picker.pickImage(source: ImageSource.gallery);
                        if (pickedFile != null) {
                          final bytes = await pickedFile.readAsBytes();
                          final ext = pickedFile.name.split('.').last;
                          setState(() {
                            newImageBytes = bytes;
                            newImageExt = ext;
                          });
                        }
                      } catch (e) {
                        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(isRtl ? 'فشل اختيار الصورة: $e' : 'Failed to pick image')));
                      }
                    },
                    child: Container(
                      width: double.infinity,
                      height: 140,
                      decoration: BoxDecoration(
                        color: Colors.grey.shade100,
                        border: Border.all(color: Colors.grey.shade300),
                        borderRadius: BorderRadius.circular(8),
                        image: newImageBytes != null 
                            ? DecorationImage(image: MemoryImage(newImageBytes!), fit: BoxFit.cover)
                            : (currentImageUrl != null ? DecorationImage(image: NetworkImage(currentImageUrl), fit: BoxFit.cover) : null),
                      ),
                      child: (newImageBytes == null && currentImageUrl == null)
                          ? Icon(Icons.add_photo_alternate, size: 40, color: Colors.grey.shade400)
                          : Align(
                              alignment: Alignment.bottomRight,
                              child: Container(
                                padding: const EdgeInsets.all(4),
                                margin: const EdgeInsets.all(4),
                                decoration: BoxDecoration(color: Colors.black54, borderRadius: BorderRadius.circular(4)),
                                child: const Icon(Icons.edit, color: Colors.white, size: 16),
                              ),
                            ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(color: Colors.blue.shade50, borderRadius: BorderRadius.circular(8)),
                    child: Row(
                      children: [
                        const Icon(Icons.info_outline, size: 16, color: Colors.blue),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            isRtl ? 'سعر الصرف: 1\$ = ${NumberFormat('#,##0').format(exchangeRate)} ج.س' : 'Rate: 1\$ = ${NumberFormat('#,##0').format(exchangeRate)} SDG',
                            style: TextStyle(fontSize: 12, color: Colors.blue.shade900),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: usdCtrl,
                    decoration: InputDecoration(
                      labelText: isRtl ? 'السعر بالدولار (\$)' : 'Price (USD)',
                      prefixIcon: const Icon(Icons.attach_money),
                      border: const OutlineInputBorder(),
                    ),
                    keyboardType: TextInputType.number,
                    onChanged: (val) {
                      final usd = double.tryParse(val) ?? 0;
                      sdgCtrl.text = (usd * exchangeRate).toStringAsFixed(0);
                    },
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: sdgCtrl,
                    decoration: InputDecoration(
                      labelText: isRtl ? 'السعر بالجنيه (ج.س)' : 'Price (SDG)',
                      prefixIcon: const Icon(Icons.money),
                      border: const OutlineInputBorder(),
                    ),
                    keyboardType: TextInputType.number,
                    onChanged: (val) {
                      final sdg = double.tryParse(val) ?? 0;
                      if (exchangeRate > 0) {
                        usdCtrl.text = (sdg / exchangeRate).toStringAsFixed(2);
                      }
                    },
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: stockCtrl,
                    decoration: InputDecoration(
                      labelText: isRtl ? 'الكمية المتوفرة' : 'Stock Quantity',
                      prefixIcon: const Icon(Icons.inventory),
                      border: const OutlineInputBorder(),
                    ),
                    keyboardType: TextInputType.number,
                  ),
                ],
              ),
            ),
            actionsAlignment: MainAxisAlignment.spaceBetween,
            actions: [
              TextButton(onPressed: isUploading ? null : () => Navigator.pop(dialogContext), child: Text(isRtl ? 'إلغاء' : 'Cancel')),
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  TextButton(
                    onPressed: isUploading ? null : () async {
                      final bool isActive = product['is_active'] ?? true;
                      
                      final confirm = await showDialog<bool>(
                        context: context,
                        builder: (ctx) => AlertDialog(
                          title: Text(isRtl ? (isActive ? 'إخفاء المنتج' : 'إظهار المنتج') : (isActive ? 'Hide Product' : 'Show Product')),
                          content: Text(isRtl 
                            ? (isActive ? 'هل أنت متأكد أنك تريد إخفاء هذا المنتج من المتجر؟' : 'هل أنت متأكد أنك تريد إظهار هذا المنتج مرة أخرى؟') 
                            : (isActive ? 'Are you sure you want to hide this product?' : 'Are you sure you want to show this product?')),
                          actions: [
                            TextButton(onPressed: () => Navigator.pop(ctx, false), child: Text(isRtl ? 'إلغاء' : 'Cancel')),
                            FilledButton(onPressed: () => Navigator.pop(ctx, true), child: Text(isRtl ? 'تأكيد' : 'Confirm')),
                          ],
                        )
                      );
                      
                      if (confirm == true) {
                        setState(() => isUploading = true);
                        try {
                          await Supabase.instance.client.from('products').update({'is_active': !isActive}).eq('id', product['id']);
                          if (dialogContext.mounted) Navigator.pop(dialogContext);
                          if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(isRtl ? (isActive ? 'تم إخفاء المنتج' : 'تم إظهار المنتج') : (isActive ? 'Product hidden' : 'Product shown'))));
                        } catch (e) {
                          if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                          setState(() => isUploading = false);
                        }
                      }
                    },
                    child: Text(isRtl ? ((product['is_active'] ?? true) ? 'إخفاء' : 'إظهار') : ((product['is_active'] ?? true) ? 'Hide' : 'Show'), style: const TextStyle(color: Colors.orange))
                  ),
                  TextButton(
                    onPressed: isUploading ? null : () async {
                      final confirm = await showDialog<bool>(
                        context: context,
                        builder: (ctx) => AlertDialog(
                          title: Text(isRtl ? 'حذف المنتج' : 'Delete Product'),
                          content: Text(isRtl ? 'هل أنت متأكد أنك تريد حذف هذا المنتج نهائياً؟' : 'Are you sure you want to delete this product permanently?'),
                          actions: [
                            TextButton(onPressed: () => Navigator.pop(ctx, false), child: Text(isRtl ? 'إلغاء' : 'Cancel')),
                            FilledButton(style: FilledButton.styleFrom(backgroundColor: Colors.red), onPressed: () => Navigator.pop(ctx, true), child: Text(isRtl ? 'حذف' : 'Delete')),
                          ],
                        )
                      );
                      
                      if (confirm == true) {
                        setState(() => isUploading = true);
                        try {
                          await Supabase.instance.client.from('products').delete().eq('id', product['id']);
                          if (dialogContext.mounted) Navigator.pop(dialogContext);
                          if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(isRtl ? 'تم الحذف بنجاح' : 'Deleted successfully')));
                        } catch (e) {
                          if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                          setState(() => isUploading = false);
                        }
                      }
                    },
                    child: Text(isRtl ? 'حذف' : 'Delete', style: const TextStyle(color: Colors.red))
                  ),
                  const SizedBox(width: 8),
                  FilledButton(
                    onPressed: isUploading ? null : () async {
                      setState(() => isUploading = true);
                      try {
                        String? finalImageUrl = currentImageUrl;
                        if (newImageBytes != null && newImageExt != null) {
                          final fileName = '${DateTime.now().millisecondsSinceEpoch}.$newImageExt';
                          await Supabase.instance.client.storage
                              .from('products_images')
                              .uploadBinary(fileName, newImageBytes!);
                          finalImageUrl = Supabase.instance.client.storage
                              .from('products_images')
                              .getPublicUrl(fileName);
                        }

                        await Supabase.instance.client.from('products').update({
                          'price_usd': double.tryParse(usdCtrl.text) ?? 0,
                          'price_sdg': double.tryParse(sdgCtrl.text) ?? 0,
                          'stock': int.tryParse(stockCtrl.text) ?? 0,
                          if (finalImageUrl != null) 'images': [finalImageUrl],
                        }).eq('id', product['id']);
                        
                        if (dialogContext.mounted) Navigator.pop(dialogContext);
                        if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(isRtl ? 'تم التعديل' : 'Updated')));
                      } catch (e) {
                        if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                        setState(() => isUploading = false);
                      }
                    }, 
                    child: isUploading ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)) : Text(isRtl ? 'حفظ' : 'Save')
                  ),
                ]
              )
            ],
          );
        }
      )
    );
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final title = isRtl ? 'المخزون والأسعار' : 'Inventory & Pricing';
    final storeAsync = ref.watch(merchantStoreInfoProvider);
    final productsAsync = ref.watch(merchantInventoryProvider);

    int activeCount = 0;
    int hiddenCount = 0;
    if (productsAsync.hasValue && productsAsync.value != null) {
      for (final p in productsAsync.value!) {
        if (p['is_active'] == false) hiddenCount++;
        else activeCount++;
      }
    }

    return Scaffold(
      appBar: AppBar(title: Text(title)),
      floatingActionButton: FloatingActionButton(
        onPressed: () => context.go('/merchant/add-product'),
        child: const Icon(Icons.add),
        tooltip: isRtl ? 'إضافة منتج' : 'Add Product',
      ),
      body: storeAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, _) => Center(child: Text('Error: $err')),
        data: (store) {
          if (store == null) return const Center(child: Text('Store not found'));
          final storeId = store['id'].toString();
          final exchangeRate = double.tryParse(store['usd_exchange_rate']?.toString() ?? '1000') ?? 1000;

          return Column(
            children: [
              // Smart Pricing Header
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.blue.shade900,
                  boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.1), blurRadius: 4, offset: const Offset(0, 2))],
                ),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            Text(isRtl ? 'نظام التسعير الذكي' : 'Smart Pricing System', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16)),
                            const SizedBox(width: 12),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(color: Colors.white.withOpacity(0.2), borderRadius: BorderRadius.circular(4)),
                              child: Row(
                                children: [
                                  const Icon(Icons.visibility, color: Colors.white, size: 14),
                                  const SizedBox(width: 4),
                                  Text(activeCount.toString(), style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold)),
                                ],
                              ),
                            ),
                            const SizedBox(width: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(color: Colors.red.withOpacity(0.8), borderRadius: BorderRadius.circular(4)),
                              child: Row(
                                children: [
                                  const Icon(Icons.visibility_off, color: Colors.white, size: 14),
                                  const SizedBox(width: 4),
                                  Text(hiddenCount.toString(), style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold)),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: InkWell(
                            onTap: () => _updateGlobalRate(storeId, exchangeRate, isRtl),
                            borderRadius: BorderRadius.circular(8),
                            child: Container(
                              padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
                              decoration: BoxDecoration(color: Colors.white.withOpacity(0.1), borderRadius: BorderRadius.circular(8)),
                              child: Column(
                                children: [
                                  Text(isRtl ? 'سعر الدولار المعتمد' : 'USD Exchange Rate', style: TextStyle(color: Colors.blue.shade100, fontSize: 12)),
                                  const SizedBox(height: 4),
                                  Text('${NumberFormat('#,##0').format(exchangeRate)} ${isRtl ? 'ج.س' : 'SDG'}', style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                                  const SizedBox(height: 4),
                                  Text(isRtl ? 'تحديث الكل 🔄' : 'Update All 🔄', style: const TextStyle(color: Colors.amber, fontSize: 12)),
                                ],
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: InkWell(
                            onTap: () => _applyBulkMarkup(storeId, isRtl),
                            borderRadius: BorderRadius.circular(8),
                            child: Container(
                              padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
                              decoration: BoxDecoration(color: Colors.white.withOpacity(0.1), borderRadius: BorderRadius.circular(8)),
                              child: Column(
                                children: [
                                  Text(isRtl ? 'زيادة أسعار جميع المنتجات' : 'Increase All Prices', style: TextStyle(color: Colors.blue.shade100, fontSize: 12)),
                                  const SizedBox(height: 4),
                                  const Icon(Icons.trending_up, color: Colors.white, size: 24),
                                  const SizedBox(height: 4),
                                  Text(isRtl ? 'نسبة مئوية %' : 'Percentage %', style: const TextStyle(color: Colors.amber, fontSize: 12)),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),

              // Products List
              Expanded(
                child: productsAsync.when(
                  loading: () => const Center(child: CircularProgressIndicator()),
                  error: (err, _) => Center(child: Text('Error: $err')),
                  data: (products) {
                    if (products.isEmpty) {
                      return Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.inventory_2_outlined, size: 64, color: Colors.grey.shade400),
                            const SizedBox(height: 16),
                            Text(isRtl ? 'لا توجد منتجات' : 'No products', style: const TextStyle(fontSize: 16, color: Colors.grey)),
                          ],
                        ),
                      );
                    }

                    return ListView.builder(
                      padding: const EdgeInsets.all(12),
                      itemCount: products.length,
                      itemBuilder: (context, index) {
                        final product = products[index];
                        final name = isRtl ? (product['name_ar'] ?? product['name']) : (product['name'] ?? product['name_ar']);
                        final stock = product['stock'] ?? 0;
                        final inStock = stock > 0;
                        final stockLabel = inStock ? (isRtl ? 'متوفر: $stock' : 'In Stock: $stock') : (isRtl ? 'نفد' : 'Out of Stock');
                        final imageUrls = product['images'] as List<dynamic>? ?? [];
                        final imageUrl = imageUrls.isNotEmpty ? imageUrls.first.toString() : null;

                        final sdg = double.tryParse(product['price_sdg']?.toString() ?? '0') ?? 0;
                        double usd = double.tryParse(product['price_usd']?.toString() ?? '0') ?? 0;
                        
                        // Fallback display if USD isn't set yet in DB
                        if (usd == 0 && sdg > 0 && exchangeRate > 0) usd = sdg / exchangeRate;

                        return Card(
                          margin: const EdgeInsets.only(bottom: 12),
                          elevation: 2,
                          child: InkWell(
                            onTap: () => _showEditDialog(product, exchangeRate, isRtl),
                            borderRadius: BorderRadius.circular(12),
                            child: Padding(
                              padding: const EdgeInsets.all(12),
                              child: Row(
                                children: [
                                  Container(
                                    width: 60, height: 60,
                                    decoration: BoxDecoration(color: Colors.grey.shade200, borderRadius: BorderRadius.circular(8)),
                                    child: imageUrl != null
                                        ? ClipRRect(borderRadius: BorderRadius.circular(8), child: Image.network(imageUrl, fit: BoxFit.cover))
                                        : const Icon(Icons.inventory_2_outlined),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(name.toString(), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                                        const SizedBox(height: 4),
                                        Row(
                                          children: [
                                            Text('${NumberFormat('#,##0').format(sdg)} ${isRtl ? 'ج.س' : 'SDG'}', style: TextStyle(color: Colors.green.shade700, fontWeight: FontWeight.bold)),
                                            const SizedBox(width: 8),
                                            Text('(\$${usd.toStringAsFixed(2)})', style: TextStyle(color: Colors.grey.shade600, fontSize: 12)),
                                          ],
                                        ),
                                        const SizedBox(height: 4),
                                        (product['is_active'] == false)
                                          ? Row(
                                              children: [
                                                const Icon(Icons.visibility_off, size: 14, color: Colors.red),
                                                const SizedBox(width: 4),
                                                Text(isRtl ? 'تم إخفاؤه' : 'Hidden', style: const TextStyle(color: Colors.red, fontSize: 12, fontWeight: FontWeight.bold)),
                                              ],
                                            )
                                          : Text(stockLabel, style: TextStyle(color: inStock ? Colors.blue.shade700 : Colors.red, fontSize: 12)),
                                      ],
                                    ),
                                  ),
                                  IconButton(
                                    icon: const Icon(Icons.edit, color: Colors.blue),
                                    onPressed: () => _showEditDialog(product, exchangeRate, isRtl),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        );
                      },
                    );
                  },
                ),
              ),
            ],
          );
        }
      ),
    );
  }
}
