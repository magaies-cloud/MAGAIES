import 'dart:async';
import 'dart:typed_data';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/core/utils/image_picker_helper.dart';
import 'package:go_router/go_router.dart';
import 'package:magaiees_marketplace/core/providers/global_settings_provider.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/auth/domain/models/user_model.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';
import 'package:magaiees_marketplace/core/network/supabase_safe_query.dart';
import 'package:magaiees_marketplace/core/widgets/product_image_preview_box.dart';
import 'package:magaiees_marketplace/core/constants/app_colors.dart';

// Provider for Store Info (to get USD rate with real-time sync across inventory and settings)
final merchantStoreInfoProvider = StreamProvider.autoDispose<Map<String, dynamic>?>((ref) {
  final user = ref.watch(authNotifierProvider).valueOrNull;
  if (user == null) {
    return Stream.value(null);
  }

  final controller = StreamController<Map<String, dynamic>?>.broadcast();
  RealtimeChannel? storeChannel;
  RealtimeChannel? userChannel;

  ref.onDispose(() {
    storeChannel?.unsubscribe();
    userChannel?.unsubscribe();
    controller.close();
  });

  Future<void> fetch() async {
    if (controller.isClosed) return;
    try {
      final storeRes = await safeSupabaseQuery(
        () {
          if (user.isStoreStaff && user.storeId != null) {
            return Supabase.instance.client
                .from('stores')
                .select('*')
                .eq('id', user.storeId!)
                .limit(1)
                .maybeSingle();
          }
          return Supabase.instance.client
              .from('stores')
              .select('*')
              .eq('owner_id', user.id)
              .limit(1)
              .maybeSingle();
        },
        fallback: () => null,
      );

      if (storeRes == null) {
        if (!controller.isClosed) controller.add(null);
        return;
      }

      final userRes = await safeSupabaseQuery(
        () => Supabase.instance.client
            .from('users')
            .select('permissions')
            .eq('id', user.id)
            .maybeSingle(),
        fallback: () => null,
      );

      final res = Map<String, dynamic>.from(storeRes);

      if (userRes != null && userRes['permissions'] is Map) {
        final perms = Map<String, dynamic>.from(userRes['permissions'] as Map);
        if (perms['usd_rate_source'] != null) {
          res['usd_rate_source'] = perms['usd_rate_source'];
        }
        if (perms['store_usd_rate'] != null) {
          res['usd_exchange_rate'] = perms['store_usd_rate'];
        }
      }

      if (!controller.isClosed) controller.add(res);
    } catch (e) {
      if (!controller.isClosed) controller.addError(e);
    }
  }

  fetch();

  final timestamp = DateTime.now().millisecondsSinceEpoch;
  storeChannel = Supabase.instance.client
      .channel('merchant_store_info_${user.id}_$timestamp')
      .onPostgresChanges(
        event: PostgresChangeEvent.all,
        schema: 'public',
        table: 'stores',
        filter: PostgresChangeFilter(
          type: PostgresChangeFilterType.eq,
          column: 'owner_id',
          value: user.id,
        ),
        callback: (_) => fetch(),
      )
      .subscribe();

  userChannel = Supabase.instance.client
      .channel('merchant_user_perms_${user.id}_$timestamp')
      .onPostgresChanges(
        event: PostgresChangeEvent.all,
        schema: 'public',
        table: 'users',
        filter: PostgresChangeFilter(
          type: PostgresChangeFilterType.eq,
          column: 'id',
          value: user.id,
        ),
        callback: (_) => fetch(),
      )
      .subscribe();

  return controller.stream;
});


// Provider for Products (Realtime listener for merchant as well)
final merchantInventoryProvider = StreamProvider.autoDispose<List<Map<String, dynamic>>>((ref) {
  final user = ref.watch(authNotifierProvider).valueOrNull;
  if (user == null) return Stream.value([]);

  final controller = StreamController<List<Map<String, dynamic>>>.broadcast();
  RealtimeChannel? channel;

  // Register onDispose synchronously at provider initialization before any async gaps
  ref.onDispose(() {
    final ch = channel;
    if (ch != null) {
      ch.unsubscribe();
      Supabase.instance.client.removeChannel(ch);
    }
    controller.close();
  });

  String? storeId;

  Future<void> fetch() async {
    if (controller.isClosed) return;
    try {
      if (storeId == null) {
        if (user.isStoreStaff && user.storeId != null) {
          storeId = user.storeId;
        } else {
          final s = await safeSupabaseQuery(
            () => Supabase.instance.client.from('stores').select('id').eq('owner_id', user.id).limit(1).maybeSingle(),
            fallback: () => null,
          );
          if (s == null) {
            if (!controller.isClosed) controller.add([]);
            return;
          }
          storeId = s['id']?.toString();
        }
      }
      if (storeId == null) {
        if (!controller.isClosed) controller.add([]);
        return;
      }
      
      final res = await safeSupabaseQuery(
        () => Supabase.instance.client.from('products').select('*').eq('store_id', storeId!).order('created_at', ascending: false),
        fallback: () => <dynamic>[],
      );
      if (!controller.isClosed) controller.add(List<Map<String, dynamic>>.from(res as Iterable));
    } catch (e) {
      if (!controller.isClosed) controller.addError(e);
    }
  }

  fetch();

  channel = Supabase.instance.client
      .channel('merchant_inventory_${user.id}_${DateTime.now().millisecondsSinceEpoch}')
      .onPostgresChanges(
        event: PostgresChangeEvent.all,
        schema: 'public',
        table: 'products',
        callback: (payload) => fetch(),
      )
      .subscribe();

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

  Future<void> _showExchangeRatePolicyDialog({
    required String storeId,
    required String currentSource,
    required double currentRate,
    required double platformRate,
    required bool isRtl,
  }) async {
    String selectedSource = currentSource;
    final rateCtrl = TextEditingController(
      text: (currentRate > 0 ? currentRate : platformRate).toStringAsFixed(0),
    );

    final updated = await showDialog<bool>(
      context: context,
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              title: Row(
                children: [
                  const Icon(Icons.currency_exchange, color: Color(0xFF1E40AF)),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      isRtl ? 'سياسة تسعير العملات وسعر الصرف' : 'Currency & Exchange Rate Policy',
                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                    ),
                  ),
                ],
              ),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      isRtl
                          ? 'اختر طريقة تسعير منتجات متجرك. يتم توحيد السعر فوراً في إعدادات المتجر وكافة المنتجات:'
                          : 'Choose pricing policy. It will be unified immediately across store settings and all products:',
                      style: TextStyle(fontSize: 12, color: Colors.grey.shade700),
                    ),
                    const SizedBox(height: 16),
                    // Option 1: Platform Rate
                    InkWell(
                      onTap: () => setDialogState(() => selectedSource = 'platform'),
                      borderRadius: BorderRadius.circular(12),
                      child: Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: selectedSource == 'platform' ? const Color(0xFFF0FDF4) : Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: selectedSource == 'platform' ? const Color(0xFF16A34A) : Colors.grey.shade300,
                            width: selectedSource == 'platform' ? 1.8 : 1.0,
                          ),
                        ),
                        child: Row(
                          children: [
                            Radio<String>(
                              value: 'platform',
                              groupValue: selectedSource,
                              activeColor: const Color(0xFF16A34A),
                              onChanged: (v) {
                                if (v != null) setDialogState(() => selectedSource = v);
                              },
                            ),
                            const SizedBox(width: 6),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      Text(
                                        isRtl ? 'سعر منصة السيطرة المعتمد 🌐' : 'Platform USD Rate 🌐',
                                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                      ),
                                      const SizedBox(width: 6),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                        decoration: BoxDecoration(
                                          color: const Color(0xFFDCFCE7),
                                          borderRadius: BorderRadius.circular(4),
                                        ),
                                        child: Text(
                                          isRtl ? 'تلقائي' : 'Auto',
                                          style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF15803D)),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    '1\$ = ${NumberFormat('#,##0').format(platformRate)} ${isRtl ? 'ج.س' : 'SDG'}',
                                    style: TextStyle(fontSize: 12, color: Colors.green.shade800, fontWeight: FontWeight.w600),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 12),
                    // Option 2: Custom Rate
                    InkWell(
                      onTap: () => setDialogState(() => selectedSource = 'custom'),
                      borderRadius: BorderRadius.circular(12),
                      child: Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: selectedSource == 'custom' ? const Color(0xFFEFF6FF) : Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: selectedSource == 'custom' ? const Color(0xFF2563EB) : Colors.grey.shade300,
                            width: selectedSource == 'custom' ? 1.8 : 1.0,
                          ),
                        ),
                        child: Column(
                          children: [
                            Row(
                              children: [
                                Radio<String>(
                                  value: 'custom',
                                  groupValue: selectedSource,
                                  activeColor: const Color(0xFF2563EB),
                                  onChanged: (v) {
                                    if (v != null) setDialogState(() => selectedSource = v);
                                  },
                                ),
                                const SizedBox(width: 6),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        isRtl ? 'سعر مخصص لمتجرك ⚙️' : 'Custom Store Rate ⚙️',
                                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                        isRtl ? 'حدد سعر الصرف المعتمد لمتجرك بشكل مستقل' : 'Set custom rate independently',
                                        style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                            if (selectedSource == 'custom') ...[
                              const SizedBox(height: 10),
                              TextField(
                                controller: rateCtrl,
                                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                decoration: InputDecoration(
                                  labelText: isRtl ? 'سعر الدولار المخصص (ج.س)' : 'Custom USD Rate (SDG)',
                                  prefixIcon: const Icon(Icons.tune_rounded, color: Color(0xFF2563EB)),
                                  suffixText: isRtl ? 'ج.س' : 'SDG',
                                  border: const OutlineInputBorder(),
                                  isDense: true,
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(ctx, false),
                  child: Text(isRtl ? 'إلغاء' : 'Cancel'),
                ),
                FilledButton.icon(
                  onPressed: () {
                    if (selectedSource == 'custom') {
                      final val = double.tryParse(rateCtrl.text.trim());
                      if (val == null || val <= 0) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(isRtl ? 'يرجى إدخال سعر صرف صحيح' : 'Please enter valid exchange rate'),
                            backgroundColor: Colors.red,
                          ),
                        );
                        return;
                      }
                    }
                    Navigator.pop(ctx, true);
                  },
                  icon: const Icon(Icons.check, size: 18),
                  label: Text(isRtl ? 'حفظ وتوحيد السعر' : 'Save & Sync'),
                ),
              ],
            );
          },
        );
      },
    );

    if (updated == true && mounted) {
      final double targetRate = selectedSource == 'platform'
          ? platformRate
          : (double.tryParse(rateCtrl.text.trim()) ?? platformRate);

      await _applyExchangeRateUpdate(
        storeId: storeId,
        source: selectedSource,
        effectiveRate: targetRate,
        isRtl: isRtl,
      );
    }
  }

  Future<void> _applyExchangeRateUpdate({
    required String storeId,
    required String source,
    required double effectiveRate,
    required bool isRtl,
  }) async {
    try {
      final user = ref.read(authNotifierProvider).valueOrNull;
      if (user != null) {
        final userRow = await Supabase.instance.client
            .from('users')
            .select('permissions')
            .eq('id', user.id)
            .maybeSingle();
        final perms = (userRow != null && userRow['permissions'] is Map)
            ? Map<String, dynamic>.from(userRow['permissions'] as Map)
            : <String, dynamic>{};
        perms['usd_rate_source'] = source;
        perms['store_usd_rate'] = effectiveRate;
        await Supabase.instance.client
            .from('users')
            .update({'permissions': perms})
            .eq('id', user.id);
      }

      await Supabase.instance.client
          .from('stores')
          .update({'usd_exchange_rate': effectiveRate})
          .eq('id', storeId);

      // Recalculate product prices based on effective USD rate
      final products = await Supabase.instance.client
          .from('products')
          .select('id, price_usd, price_sdg')
          .eq('store_id', storeId);

      for (var p in products) {
        double usd = double.tryParse(p['price_usd']?.toString() ?? '0') ?? 0;
        double sdg = double.tryParse(p['price_sdg']?.toString() ?? '0') ?? 0;

        if (usd == 0 && sdg > 0 && effectiveRate > 0) {
          usd = sdg / effectiveRate;
        }

        if (usd > 0) {
          final newSdg = usd * effectiveRate;
          await Supabase.instance.client.from('products').update({
            'price_sdg': newSdg,
            'price_usd': usd,
          }).eq('id', p['id']).eq('store_id', storeId);
        }
      }

      ref.invalidate(merchantStoreInfoProvider);
      ref.invalidate(merchantInventoryProvider);
      try { ref.invalidate(productsProvider); } catch (_) {}

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              source == 'platform'
                  ? (isRtl
                      ? 'تمت مزامنة السعر بنجاح مع سعر المنصة المعتمد (${NumberFormat('#,##0').format(effectiveRate)} ج.س) وتوحيده في إعدادات المتجر!'
                      : 'Synced with platform rate (${NumberFormat('#,##0').format(effectiveRate)} SDG) and unified in Store Settings!')
                  : (isRtl
                      ? 'تم تحديث السعر المخصص لمتجرك (${NumberFormat('#,##0').format(effectiveRate)} ج.س) وتوحيده في إعدادات المتجر وكافة المنتجات بنجاح!'
                      : 'Custom store rate (${NumberFormat('#,##0').format(effectiveRate)} SDG) updated and unified across Store Settings and products!'),
            ),
            backgroundColor: Colors.green,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
        );
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
          }).eq('id', p['id']).eq('store_id', storeId);
        }
        
        try { ref.invalidate(productsProvider); } catch (_) {}
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

    final nameArCtrl = TextEditingController(text: product['name_ar']?.toString() ?? product['name']?.toString() ?? '');
    final nameEnCtrl = TextEditingController(text: product['name']?.toString() ?? '');
    final descCtrl = TextEditingController(text: product['description']?.toString() ?? '');
    String selectedUnit = product['unit']?.toString() ?? 'ton';
    final usdCtrl = TextEditingController(text: initialUsd == 0 ? '' : initialUsd.toStringAsFixed(2));
    final sdgCtrl = TextEditingController(text: initialSdg == 0 ? '' : initialSdg.toStringAsFixed(0));
    final stockCtrl = TextEditingController(text: product['stock']?.toString() ?? '0');

    Uint8List? newImageBytes;
    String? newImageExt;
    bool isUploading = false;
    final rawImages = product['images'];
    String? currentImageUrl = (rawImages is List && rawImages.isNotEmpty)
        ? rawImages.first?.toString()
        : null;
    final editImageBoxController = ProductImagePreviewBoxController();
    bool hasPreloaded = false;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (dialogContext) => StatefulBuilder(
        builder: (ctx, setState) {
          if (!hasPreloaded && currentImageUrl != null && newImageBytes == null) {
            hasPreloaded = true;
            Dio().get<List<int>>(
              currentImageUrl!,
              options: Options(responseType: ResponseType.bytes),
            ).then((res) {
              if (res.data != null && ctx.mounted) {
                setState(() {
                  newImageBytes = Uint8List.fromList(res.data!);
                  newImageExt = 'png';
                });
              }
            }).catchError((e) {
              debugPrint('Preload image bytes error: $e');
            });
          }

          return AlertDialog(
            title: Text(isRtl ? 'تعديل المنتج' : 'Edit Product'),
            content: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  ProductImagePreviewBox(
                    controller: editImageBoxController,
                    imageBytes: newImageBytes,
                    imageUrl: currentImageUrl,
                    height: 240,
                    label: isRtl ? 'صورة المنتج' : 'Product Image',
                    onCropConfirmed: (croppedBytes) {
                      setState(() {
                        newImageBytes = croppedBytes;
                        newImageExt = 'png';
                      });
                    },
                    onPickImage: () async {
                      try {
                        final pickedFile = await ImagePickerHelper.pickImage(
                          context,
                          imageQuality: 75,
                          maxWidth: 1200,
                        );
                        if (pickedFile != null) {
                          final bytes = await pickedFile.readAsBytes();
                          final ext = pickedFile.name.split('.').last;
                          if (ctx.mounted) {
                            setState(() {
                              newImageBytes = bytes;
                              newImageExt = ext;
                            });
                          }
                        }
                      } catch (e) {
                        if (context.mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(content: Text(isRtl ? 'فشل اختيار الصورة: $e' : 'Failed to pick image')),
                          );
                        }
                      }
                    },
                    onRemoveImage: (newImageBytes != null || currentImageUrl != null)
                        ? () {
                            setState(() {
                              newImageBytes = null;
                              newImageExt = null;
                              currentImageUrl = null;
                            });
                          }
                        : null,
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
                    controller: nameArCtrl,
                    decoration: InputDecoration(
                      labelText: isRtl ? 'اسم المنتج (بالعربي)' : 'Product Name (Arabic)',
                      prefixIcon: const Icon(Icons.label_outline),
                      border: const OutlineInputBorder(),
                    ),
                  ),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<String>(
                    value: ['ton', 'sack', 'meter', 'piece', 'truck', 'kg', 'box', 'liter', 'barrel'].contains(selectedUnit)
                        ? selectedUnit
                        : 'ton',
                    decoration: InputDecoration(
                      labelText: isRtl ? 'وحدة القياس' : 'Unit of Measurement',
                      prefixIcon: const Icon(Icons.straighten),
                      border: const OutlineInputBorder(),
                    ),
                    items: [
                      DropdownMenuItem(value: 'ton', child: Text(isRtl ? 'طن (Ton)' : 'Ton')),
                      DropdownMenuItem(value: 'sack', child: Text(isRtl ? 'جوال / شوال (Sack)' : 'Sack')),
                      DropdownMenuItem(value: 'meter', child: Text(isRtl ? 'متر (Meter)' : 'Meter')),
                      DropdownMenuItem(value: 'piece', child: Text(isRtl ? 'قطعة / حبة (Piece)' : 'Piece')),
                      DropdownMenuItem(value: 'truck', child: Text(isRtl ? 'نقلة / قلاب / لوري (Truck)' : 'Truck')),
                      DropdownMenuItem(value: 'kg', child: Text(isRtl ? 'كيلو (Kg)' : 'Kg')),
                      DropdownMenuItem(value: 'box', child: Text(isRtl ? 'كرتونة (Box)' : 'Box')),
                      DropdownMenuItem(value: 'liter', child: Text(isRtl ? 'لتر (Liter)' : 'Liter')),
                      DropdownMenuItem(value: 'barrel', child: Text(isRtl ? 'برميل (Barrel)' : 'Barrel')),
                    ],
                    onChanged: (val) {
                      if (val != null) setState(() => selectedUnit = val);
                    },
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: descCtrl,
                    maxLines: 2,
                    decoration: InputDecoration(
                      labelText: isRtl ? 'وصف المنتج' : 'Description',
                      prefixIcon: const Icon(Icons.description_outlined),
                      border: const OutlineInputBorder(),
                    ),
                  ),
                  const SizedBox(height: 12),
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
                          await Supabase.instance.client.from('products').update({'is_active': !isActive}).eq('id', product['id']).eq('store_id', product['store_id']);
                          try { ref.invalidate(productsProvider); } catch (_) {}
                          if (dialogContext.mounted) Navigator.pop(dialogContext);
                          if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(isRtl ? (isActive ? 'تم إخفاء المنتج' : 'تم إظهار المنتج') : (isActive ? 'Product hidden' : 'Product shown'))));
                        } catch (e) {
                          if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                          if (ctx.mounted) setState(() => isUploading = false);
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
                        if (ctx.mounted) setState(() => isUploading = true);
                        try {
                          await Supabase.instance.client.from('products').delete().eq('id', product['id']).eq('store_id', product['store_id']);
                          try { ref.invalidate(productsProvider); } catch (_) {}
                          if (dialogContext.mounted) Navigator.pop(dialogContext);
                          if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(isRtl ? 'تم الحذف بنجاح' : 'Deleted successfully')));
                        } catch (e) {
                          if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                          if (ctx.mounted) setState(() => isUploading = false);
                        }
                      }
                    },
                    child: Text(isRtl ? 'حذف' : 'Delete', style: const TextStyle(color: Colors.red))
                  ),
                  const SizedBox(width: 8),
                    FilledButton(
                    style: FilledButton.styleFrom(
                      backgroundColor: AppColors.merchantAccent,
                      disabledBackgroundColor: AppColors.merchantAccent.withValues(alpha: 0.6),
                      foregroundColor: Colors.white,
                      disabledForegroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                    ),
                    onPressed: isUploading ? null : () async {
                      setState(() => isUploading = true);
                      try {
                        String? finalImageUrl;
                        if (newImageBytes != null) {
                          // New image was picked — capture framed version
                          Uint8List uploadBytes = newImageBytes!;
                          String ext = newImageExt ?? 'png';

                          try {
                            final framed = await editImageBoxController.captureFramedImage();
                            if (framed != null && framed.isNotEmpty) {
                              uploadBytes = framed;
                              ext = 'png';
                            }
                          } catch (e) {
                            debugPrint('Framed capture error in edit: $e');
                          }

                          final fileName = '${DateTime.now().millisecondsSinceEpoch}.$ext';
                          await Supabase.instance.client.storage
                              .from('products_images')
                              .uploadBinary(
                                fileName,
                                uploadBytes,
                                fileOptions: FileOptions(contentType: 'image/$ext'),
                              );
                          finalImageUrl = Supabase.instance.client.storage
                              .from('products_images')
                              .getPublicUrl(fileName);
                        }

                        final existingImages = (product['images'] is List)
                            ? (product['images'] as List)
                            : const [];

                        final updateData = <String, dynamic>{
                          'name_ar': nameArCtrl.text.trim().isNotEmpty ? nameArCtrl.text.trim() : product['name_ar'],
                          if (nameEnCtrl.text.trim().isNotEmpty) 'name': nameEnCtrl.text.trim(),
                          if (product.containsKey('description') && descCtrl.text.trim().isNotEmpty) 'description': descCtrl.text.trim(),
                          'unit': selectedUnit,
                          'price_usd': double.tryParse(usdCtrl.text) ?? 0,
                          'price_sdg': double.tryParse(sdgCtrl.text) ?? 0,
                          'stock': int.tryParse(stockCtrl.text) ?? 0,
                        };

                        if (finalImageUrl != null) {
                          updateData['images'] = [finalImageUrl, ...existingImages.skip(1)];
                        } else if (currentImageUrl == null && newImageBytes == null) {
                          updateData['images'] = [];
                        }

                        await Supabase.instance.client.from('products').update(updateData).eq('id', product['id']).eq('store_id', product['store_id']);
                        try { ref.invalidate(productsProvider); } catch (_) {}
                        try { ref.invalidate(merchantInventoryProvider); } catch (_) {}
                        
                        if (dialogContext.mounted) Navigator.pop(dialogContext);
                        if (context.mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(isRtl ? 'تم تحديث بيانات وصورة المنتج بنجاح!' : 'Product updated successfully!'),
                              backgroundColor: Colors.green.shade700,
                            ),
                          );
                        }
                      } catch (e) {
                        if (context.mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(isRtl ? 'فشل تحديث المنتج: $e' : 'Error updating product: $e'),
                              backgroundColor: Colors.red.shade700,
                            ),
                          );
                        }
                      } finally {
                        if (ctx.mounted && dialogContext.mounted) {
                          setState(() => isUploading = false);
                        }
                      }
                    }, 
                    child: isUploading
                        ? const SizedBox(
                            width: 18,
                            height: 18,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                            ),
                          )
                        : Text(
                            isRtl ? 'حفظ' : 'Save',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                          ),
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

    final store = storeAsync.valueOrNull;
    final globalSettings = ref.watch(globalSettingsProvider);
    final platformRate = globalSettings.platformUsdRate;
    final rateSource = store?['usd_rate_source'] ?? 'platform';
    final isPlatformSource = rateSource == 'platform';
    final effectiveExchangeRate = isPlatformSource ? platformRate : (double.tryParse(store?['usd_exchange_rate']?.toString() ?? '') ?? platformRate);

    return Scaffold(
      appBar: AppBar(
        title: Text(title),
        actions: [
          IconButton(
            icon: const Icon(Icons.picture_as_pdf_outlined),
            tooltip: isRtl ? 'تصدير الكتالوج (PDF)' : 'Export Catalog (PDF)',
            onPressed: () {
              final prods = productsAsync.valueOrNull ?? [];
              if (prods.isEmpty) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(isRtl ? 'لا توجد منتجات لتصديرها' : 'No products to export'),
                  ),
                );
                return;
              }
              _generateAndPrintPDF(prods, effectiveExchangeRate, isRtl);
            },
          ),
        ],
      ),
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
          final globalSettings = ref.watch(globalSettingsProvider);
          final platformRate = globalSettings.platformUsdRate;
          final rateSource = store['usd_rate_source'] ?? 'platform';
          final isPlatformSource = rateSource == 'platform';
          final effectiveExchangeRate = isPlatformSource ? platformRate : (double.tryParse(store['usd_exchange_rate']?.toString() ?? '') ?? platformRate);

          return Column(
            children: [
              // Smart Pricing Header
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: isPlatformSource ? const Color(0xFF0F2E4A) : Colors.blue.shade900,
                  boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.1), blurRadius: 4, offset: const Offset(0, 2))],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      crossAxisAlignment: WrapCrossAlignment.center,
                      children: [
                        Row(
                          children: [
                            Text(isRtl ? 'نظام التسعير الذكي' : 'Smart Pricing System', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16)),
                            const SizedBox(width: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(
                                color: isPlatformSource ? const Color(0xFF16A34A).withOpacity(0.3) : const Color(0xFF2563EB).withOpacity(0.3),
                                borderRadius: BorderRadius.circular(6),
                                border: Border.all(
                                  color: isPlatformSource ? const Color(0xFF22C55E) : const Color(0xFF60A5FA),
                                  width: 0.8,
                                ),
                              ),
                              child: Text(
                                isPlatformSource ? (isRtl ? 'سعر منصة السيطرة المعتمد 🌐' : 'Platform Rate 🌐') : (isRtl ? 'سعر مخصص لمتجرك ⚙️' : 'Custom Store Rate ⚙️'),
                                style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                              ),
                            ),
                          ),
                          child: Text(
                            isPlatformSource ? (isRtl ? 'سعر منصة مقاييس المعتمد 🌐' : 'Platform Rate 🌐') : (isRtl ? 'سعر مخصص لمتجرك ⚙️' : 'Custom Store Rate ⚙️'),
                            style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(4)),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.visibility, color: Colors.white, size: 14),
                              const SizedBox(width: 4),
                              Text(activeCount.toString(), style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold)),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(color: Colors.red.withValues(alpha: 0.8), borderRadius: BorderRadius.circular(4)),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.visibility_off, color: Colors.white, size: 14),
                              const SizedBox(width: 4),
                              Text(hiddenCount.toString(), style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold)),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: InkWell(
                            onTap: () => _showExchangeRatePolicyDialog(
                              storeId: storeId,
                              currentSource: rateSource,
                              currentRate: effectiveExchangeRate,
                              platformRate: platformRate,
                              isRtl: isRtl,
                            ),
                            borderRadius: BorderRadius.circular(8),
                            child: Container(
                              padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
                              decoration: BoxDecoration(
                                color: isPlatformSource ? Colors.green.shade900.withOpacity(0.4) : Colors.white.withOpacity(0.1),
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(color: isPlatformSource ? Colors.green.shade400 : Colors.white24),
                              ),
                              child: Column(
                                children: [
                                  Text(
                                    isPlatformSource
                                        ? (isRtl ? 'سعر الدولار المعتمد (منصة السيطرة)' : 'Platform USD Rate')
                                        : (isRtl ? 'سعر الدولار المخصص لمتجرك' : 'Custom Store USD Rate'),
                                    style: TextStyle(color: Colors.blue.shade100, fontSize: 11),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    '${NumberFormat('#,##0').format(effectiveExchangeRate)} ${isRtl ? 'ج.س' : 'SDG'}',
                                    style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    isRtl ? 'تعديل أو توحيد السعر ⚙️' : 'Edit or Sync Rate ⚙️',
                                    style: TextStyle(color: isPlatformSource ? Colors.greenAccent : Colors.amber, fontSize: 12, fontWeight: FontWeight.bold),
                                  ),
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
                        if (usd == 0 && sdg > 0 && effectiveExchangeRate > 0) usd = sdg / effectiveExchangeRate;

                        return Card(
                          margin: const EdgeInsets.only(bottom: 12),
                          elevation: 2,
                          child: InkWell(
                            onTap: () => _showEditDialog(product, effectiveExchangeRate, isRtl),
                            borderRadius: BorderRadius.circular(12),
                            child: Padding(
                              padding: const EdgeInsets.all(12),
                              child: Row(
                                children: [
                                  Container(
                                    width: 60, height: 60,
                                    decoration: BoxDecoration(
                                      color: Colors.white,
                                      border: Border.all(color: Colors.grey.shade200),
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: imageUrl != null
                                        ? ClipRRect(
                                            borderRadius: BorderRadius.circular(7),
                                            child: Image.network(
                                              imageUrl,
                                              fit: BoxFit.contain,
                                              errorBuilder: (ctx, err, stack) => const Icon(Icons.inventory_2_outlined, size: 36, color: Colors.grey),
                                            ),
                                          )
                                        : const Icon(Icons.inventory_2_outlined, color: Colors.grey),
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
                                    onPressed: () => _showEditDialog(product, effectiveExchangeRate, isRtl),
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
