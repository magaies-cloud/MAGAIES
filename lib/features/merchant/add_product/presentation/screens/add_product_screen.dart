import 'dart:async';
import 'dart:typed_data';
import 'dart:ui' as ui;
import 'package:flutter/rendering.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:supabase_flutter/supabase_flutter.dart';

import 'package:magaiees_marketplace/core/providers/global_settings_provider.dart';
import 'package:magaiees_marketplace/core/services/translation_service.dart';
import 'package:magaiees_marketplace/core/utils/data_validator.dart';
import 'package:magaiees_marketplace/core/utils/image_picker_helper.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/features/auth/domain/models/user_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/category_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/sub_category_model.dart';
import 'package:magaiees_marketplace/features/merchant/inventory/presentation/screens/merchant_inventory_screen.dart';

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
  final _stockController = TextEditingController(text: '100');
  final _descriptionController = TextEditingController();
  final _customUnitController = TextEditingController();

  Timer? _debounceTimer;
  bool _isTranslating = false;

  String? _selectedCategory;
  String? _selectedSubCategory;
  String? _selectedUnit = 'ton';
  bool _isCustomUnit = false;

  bool _hasExpiryDate = false;
  DateTime? _expiryDate;
  final List<String> _selectedPaymentMethods = [
    'cod',
    'bankak',
    'ocash',
    'bank',
    'b2b',
    'partial_cod',
  ];
  bool _inStock = true;
  Uint8List? _imageBytes;
  String? _imageExt;
  final _previewCropBoundaryKey = GlobalKey();
  final _previewTransformController = TransformationController();

  Future<Uint8List?> _captureCurrentFramedImage() async {
    if (_imageBytes == null) return null;
    try {
      final boundary = _previewCropBoundaryKey.currentContext?.findRenderObject()
          as RenderRepaintBoundary?;
      if (boundary == null) return _imageBytes;

      final ui.Image image =
          await boundary.toImage(pixelRatio: 2.0).timeout(const Duration(seconds: 4));
      final ByteData? byteData =
          await image.toByteData(format: ui.ImageByteFormat.png).timeout(const Duration(seconds: 2));
      if (byteData != null) {
        return byteData.buffer.asUint8List();
      }
    } catch (e) {
      debugPrint('Error capturing framed image: $e');
    }
    return _imageBytes;
  }

  Future<void> _lockAndApplyCrop() async {
    if (_imageBytes == null) return;
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    try {
      final cropped = await _captureCurrentFramedImage();
      if (cropped != null && cropped.isNotEmpty) {
        setState(() {
          _imageBytes = cropped;
          _imageExt = 'png';
          _previewTransformController.value = Matrix4.identity();
        });
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Row(
                children: [
                  const Icon(Icons.check_circle_rounded, color: Colors.white),
                  const SizedBox(width: 8),
                  Text(isRtl
                      ? '✨ تم تثبيت وتأطير مقاس الصورة بنجاح!'
                      : '✨ Image framing and size locked successfully!'),
                ],
              ),
              backgroundColor: const Color(0xFF16A34A),
              duration: const Duration(seconds: 2),
              behavior: SnackBarBehavior.floating,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
          );
        }
      }
    } catch (e) {
      debugPrint('Error locking crop: $e');
    }
  }
  double _exchangeRate = 1000.0;
  double _prevExchangeRate = 0.0;
  String _usdRateSource = 'platform';

  List<String>? _allowedCategoryIds;
  bool _allowAllCategories = true;
  bool _isLoadingPerms = true;
  RealtimeChannel? _permChannel;
  RealtimeChannel? _storeChannel;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _nameArController.addListener(_onFormChanged);
    _nameEnController.addListener(_onFormChanged);
    _priceController.addListener(_onFormChanged);
    _priceUsdController.addListener(_onFormChanged);
    _stockController.addListener(_onFormChanged);
    _descriptionController.addListener(_onFormChanged);

    WidgetsBinding.instance.addPostFrameCallback((_) {
      _fetchStoreRate();
      _fetchMerchantPermissions();
      _listenToPermissionsRealtime();
    });
  }

  void _onFormChanged() {
    if (mounted) setState(() {});
  }

  void _onArabicNameChanged(String value) {
    _debounceTimer?.cancel();
    if (value.trim().isEmpty) {
      _nameEnController.clear();
      return;
    }
    _debounceTimer = Timer(const Duration(milliseconds: 600), () async {
      if (!mounted) return;
      setState(() => _isTranslating = true);
      try {
        final translated =
            await TranslationService.translateArabicToEnglish(value);
        if (mounted && translated.isNotEmpty) {
          _nameEnController.text = translated;
        }
      } catch (_) {
      } finally {
        if (mounted) setState(() => _isTranslating = false);
      }
    });
  }

  void _resetForm() {
    _nameArController.clear();
    _nameEnController.clear();
    _priceController.clear();
    _priceUsdController.clear();
    _customUnitController.clear();
    _descriptionController.clear();
    _stockController.text = '100';
    if (mounted) {
      setState(() {
        _selectedCategory = null;
        _selectedSubCategory = null;
        _selectedUnit = 'ton';
        _isCustomUnit = false;
        _hasExpiryDate = false;
        _expiryDate = null;
        _imageBytes = null;
        _imageExt = null;
        _inStock = true;
        _prevExchangeRate = 0.0;
      });
    }
  }

  Future<void> _fetchMerchantPermissions() async {
    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user == null) return;
    try {
      final res = await Supabase.instance.client
          .from('users')
          .select('permissions')
          .eq('id', user.id)
          .maybeSingle();
      if (res != null && res['permissions'] != null) {
        final perms = res['permissions'] as Map<String, dynamic>;
        final allowed = perms['allowed_categories'];
        if (mounted) {
          setState(() {
            if (allowed is List) {
              _allowedCategoryIds = allowed.map((e) => e.toString()).toList();
              _allowAllCategories = false;
            } else if (allowed == 'all' || allowed == null) {
              _allowAllCategories = true;
              _allowedCategoryIds = null;
            } else {
              _allowAllCategories = true;
            }
            _isLoadingPerms = false;
          });
        }
      } else {
        if (mounted) setState(() => _isLoadingPerms = false);
      }
    } catch (_) {
      if (mounted) setState(() => _isLoadingPerms = false);
    }
  }

  void _listenToPermissionsRealtime() {
    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user == null) return;
    try {
      _permChannel?.unsubscribe();
      _permChannel = Supabase.instance.client
          .channel('public:users_perms_${user.id}')
          .onPostgresChanges(
            event: PostgresChangeEvent.update,
            schema: 'public',
            table: 'users',
            filter: PostgresChangeFilter(
              type: PostgresChangeFilterType.eq,
              column: 'id',
              value: user.id,
            ),
            callback: (payload) {
              _fetchMerchantPermissions();
              _fetchStoreRate();
            },
          )
          .subscribe();

      _storeChannel?.unsubscribe();
      _storeChannel = Supabase.instance.client
          .channel('public:stores_rate_${user.id}')
          .onPostgresChanges(
            event: PostgresChangeEvent.update,
            schema: 'public',
            table: 'stores',
            filter: PostgresChangeFilter(
              type: PostgresChangeFilterType.eq,
              column: 'owner_id',
              value: user.id,
            ),
            callback: (payload) {
              _fetchStoreRate();
            },
          )
          .subscribe();
    } catch (_) {}
  }

  Future<void> _fetchStoreRate() async {
    final user = ref.read(authNotifierProvider).valueOrNull;
    if (user != null) {
      final platformRate = ref.read(globalSettingsProvider).platformUsdRate;
      final userRes = await Supabase.instance.client
          .from('users')
          .select('permissions')
          .eq('id', user.id)
          .maybeSingle();
      final storeRes = await Supabase.instance.client
          .from('stores')
          .select('usd_exchange_rate')
          .eq('owner_id', user.id)
          .limit(1)
          .maybeSingle();

      String source = 'platform';
      double rate = platformRate;

      if (userRes != null && userRes['permissions'] is Map) {
        final perms = userRes['permissions'] as Map<String, dynamic>;
        source = perms['usd_rate_source'] ?? 'platform';
        final customRate = double.tryParse(
                perms['store_usd_rate']?.toString() ?? '') ??
            (storeRes != null && storeRes['usd_exchange_rate'] != null
                ? double.tryParse(storeRes['usd_exchange_rate'].toString())
                : null) ??
            platformRate;
        rate = source == 'platform' ? platformRate : customRate;
      } else if (storeRes != null && storeRes['usd_exchange_rate'] != null) {
        rate = double.tryParse(storeRes['usd_exchange_rate'].toString()) ??
            platformRate;
      }

      if (mounted) {
        setState(() {
          _usdRateSource = source;
          _exchangeRate = rate;
        });
      }
    }
  }

  @override
  void dispose() {
    _debounceTimer?.cancel();
    _permChannel?.unsubscribe();
    _storeChannel?.unsubscribe();
    _nameArController.removeListener(_onFormChanged);
    _nameEnController.removeListener(_onFormChanged);
    _priceController.removeListener(_onFormChanged);
    _priceUsdController.removeListener(_onFormChanged);
    _stockController.removeListener(_onFormChanged);
    _descriptionController.removeListener(_onFormChanged);
    _nameArController.dispose();
    _nameEnController.dispose();
    _priceController.dispose();
    _priceUsdController.dispose();
    _customUnitController.dispose();
    _stockController.dispose();
    _descriptionController.dispose();
    _previewTransformController.dispose();
    super.dispose();
  }

  double get _readinessScore {
    int score = 0;
    if (_nameArController.text.trim().isNotEmpty) score += 20;
    if (_selectedCategory != null) score += 20;
    if (_imageBytes != null) score += 20;
    final priceVal = double.tryParse(_priceController.text) ?? 0;
    final priceUsdVal = double.tryParse(_priceUsdController.text) ?? 0;
    if (priceVal > 0 || priceUsdVal > 0) score += 20;
    if (!_inStock || (int.tryParse(_stockController.text.trim()) ?? 0) > 0) {
      score += 20;
    }
    return score / 100.0;
  }

  Future<void> _saveProduct() async {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    if (!(_formKey.currentState?.validate() ?? false)) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Row(
            children: [
              const Icon(Icons.error_outline_rounded, color: Colors.white),
              const SizedBox(width: 8),
              Text(isRtl
                  ? 'يرجى استكمال الحقول المطلوبة والتأكد من صحتها'
                  : 'Please complete all required fields correctly'),
            ],
          ),
          backgroundColor: const Color(0xFFDC2626),
          behavior: SnackBarBehavior.floating,
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
      );
      return;
    }

    if (_imageBytes == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Row(
            children: [
              const Icon(Icons.add_a_photo_rounded, color: Colors.white),
              const SizedBox(width: 8),
              Text(isRtl
                  ? '⚠️ يرجى رفع صورة واضحة ومناسبة للمنتج قبل الحفظ'
                  : 'Please upload a clear product image before saving'),
            ],
          ),
          backgroundColor: const Color(0xFFD97706),
          behavior: SnackBarBehavior.floating,
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
      );
      return;
    }

    setState(() => _isLoading = true);

    try {
      String? finalImageUrl;
      if (_imageBytes != null) {
        Uint8List uploadBytes = _imageBytes!;
        String ext = _imageExt ?? 'png';

        try {
          final framed = await _captureCurrentFramedImage();
          if (framed != null && framed.isNotEmpty) {
            uploadBytes = framed;
            ext = 'png';
          }
        } catch (e) {
          debugPrint('Framed capture fallback: $e');
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

      final user = ref.read(authNotifierProvider).valueOrNull;
      String? storeId;
      if (user != null) {
        if (user.isStoreStaff && user.storeId != null && user.storeId!.isNotEmpty) {
          storeId = user.storeId;
        } else {
          final storeRes = await Supabase.instance.client
              .from('stores')
              .select('id')
              .eq('owner_id', user.id)
              .limit(1)
              .maybeSingle();
          storeId = storeRes?['id']?.toString();
        }

        if (storeId == null && !user.isStoreStaff) {
          try {
            final newStore = await Supabase.instance.client
                .from('stores')
                .insert({'owner_id': user.id, 'name': user.name})
                .select('id')
                .single();
            storeId = newStore['id']?.toString();
          } catch (_) {}
        }

        if (storeId == null) {
          if (mounted) {
            final isRtlMsg = Directionality.of(context) == TextDirection.rtl;
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(isRtlMsg
                    ? 'لا يوجد متجر مرتبط بحسابك. يرجى التواصل مع الإدارة لإنشاء متجرك أولاً.'
                    : 'No store linked to your account. Please contact admin to set up your store first.'),
                backgroundColor: const Color(0xFFDC2626),
                behavior: SnackBarBehavior.floating,
                duration: const Duration(seconds: 4),
              ),
            );
          }
          setState(() => _isLoading = false);
          return;
        }
      }

      final effectiveUnit =
          _isCustomUnit && _customUnitController.text.trim().isNotEmpty
              ? _customUnitController.text.trim()
              : (_selectedUnit ?? 'ton');

      final availableCategories =
          ref.read(categoriesProvider).valueOrNull ?? [];
      final defaultCategoryId =
          availableCategories.isNotEmpty ? availableCategories.first.id : null;

      final int parsedStock = _inStock
          ? (int.tryParse(_stockController.text.trim()) ?? 100)
          : 0;

      final List<String> descParts = [];
      if (_descriptionController.text.trim().isNotEmpty) {
        descParts.add(_descriptionController.text.trim());
      }
      if (_hasExpiryDate && _expiryDate != null) {
        descParts.add('تاريخ الصلاحية/الضمان: ${DateFormat("yyyy-MM-dd").format(_expiryDate!)}');
      }
      if (_selectedPaymentMethods.isNotEmpty) {
        descParts.add('طرق الدفع المعتمدة: ${_selectedPaymentMethods.join(", ")}');
      }
      final String fullDescription = descParts.join('\n');

      final insertData = <String, dynamic>{
        'store_id': storeId,
        'category_id': _selectedCategory ?? defaultCategoryId,
        'unit': effectiveUnit,
        'name': _nameEnController.text.trim(),
        'name_ar': _nameArController.text.trim(),
        'price_sdg': double.tryParse(_priceController.text) ?? 0.0,
        'price_usd': double.tryParse(_priceUsdController.text) ?? 0.0,
        'stock': parsedStock,
        'images': finalImageUrl != null ? [finalImageUrl] : [],
        'is_magaiees_exclusive': false,
        'is_active': _inStock,
        if (fullDescription.isNotEmpty) 'description': fullDescription,
      };

      if (_selectedSubCategory != null && _selectedSubCategory!.isNotEmpty) {
        insertData['subcategory_id'] = _selectedSubCategory;
      }

      try {
        await Supabase.instance.client.from('products').insert(insertData);
      } on PostgrestException catch (pgErr) {
        if (insertData.containsKey('description') &&
            pgErr.message.toLowerCase().contains('description')) {
          final retryData = Map<String, dynamic>.from(insertData)..remove('description');
          await Supabase.instance.client.from('products').insert(retryData);
        } else {
          rethrow;
        }
      }

      ref.invalidate(productsProvider);
      ref.invalidate(categoriesProvider);
      ref.invalidate(merchantInventoryProvider);

      if (mounted) {
        final isRtlSuccess = Directionality.of(context) == TextDirection.rtl;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                const Icon(Icons.check_circle_rounded, color: Colors.white),
                const SizedBox(width: 8),
                Text(isRtlSuccess
                    ? '🎉 تم إضافة ونشر المنتج بنجاح في المنصة!'
                    : '🎉 Product published successfully to the marketplace!'),
              ],
            ),
            backgroundColor: const Color(0xFF16A34A),
            behavior: SnackBarBehavior.floating,
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          ),
        );
        _resetForm();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('خطأ أثناء الحفظ: $e'),
            backgroundColor: const Color(0xFFDC2626),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  // ════════════════════════════════════════════════════════════════════════════
  // UI WIDGET BUILDERS
  // ════════════════════════════════════════════════════════════════════════════

  Widget _buildSectionCard({
    required Widget header,
    required Widget child,
    EdgeInsetsGeometry padding = const EdgeInsets.all(20),
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0), width: 1.2),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.03),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      padding: padding,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          header,
          const SizedBox(height: 16),
          const Divider(height: 1, color: Color(0xFFF1F5F9)),
          const SizedBox(height: 16),
          child,
        ],
      ),
    );
  }

  Widget _buildSectionHeader({
    required IconData icon,
    required Color iconColor,
    required Color iconBg,
    required String title,
    required String subtitle,
    Widget? trailing,
  }) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(
            color: iconBg,
            borderRadius: BorderRadius.circular(12),
          ),
          child: Icon(icon, color: iconColor, size: 22),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF0F172A),
                ),
              ),
              const SizedBox(height: 2),
              Text(
                subtitle,
                style: const TextStyle(
                  fontSize: 12,
                  color: Color(0xFF64748B),
                ),
              ),
            ],
          ),
        ),
        ?trailing,
      ],
    );
  }

  Widget _buildReadinessBar(bool isRtl) {
    final score = _readinessScore;
    final percent = (score * 100).toInt();

    final hasName = _nameArController.text.trim().isNotEmpty;
    final hasCategory = _selectedCategory != null;
    final hasImage = _imageBytes != null;
    final hasPrice = (double.tryParse(_priceController.text) ?? 0) > 0 ||
        (double.tryParse(_priceUsdController.text) ?? 0) > 0;
    final hasStock =
        !_inStock || (int.tryParse(_stockController.text.trim()) ?? 0) > 0;

    final completedCount = (hasName ? 1 : 0) +
        (hasCategory ? 1 : 0) +
        (hasImage ? 1 : 0) +
        (hasPrice ? 1 : 0) +
        (hasStock ? 1 : 0);

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: score >= 1.0
              ? const Color(0xFF86EFAC)
              : const Color(0xFFE2E8F0),
          width: 1.1,
        ),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.03),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Sleek compact header row
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    isRtl ? 'جاهزية نشر الصنف' : 'Listing Readiness',
                    style: const TextStyle(
                      fontSize: 12.5,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF1E293B),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 7, vertical: 2),
                    decoration: BoxDecoration(
                      color: score >= 1.0
                          ? const Color(0xFFDCFCE7)
                          : const Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(
                        color: score >= 1.0
                            ? const Color(0xFF86EFAC)
                            : const Color(0xFFBFDBFE),
                      ),
                    ),
                    child: Text(
                      '$percent%',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                        color: score >= 1.0
                            ? const Color(0xFF15803D)
                            : const Color(0xFF2563EB),
                      ),
                    ),
                  ),
                ],
              ),
              Text(
                score >= 1.0
                    ? (isRtl ? '✨ مكتمل وجاهز للنشر' : '✨ Ready to publish')
                    : (isRtl
                        ? '$completedCount من 5 مكتملة'
                        : '$completedCount of 5 completed'),
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: score >= 1.0 ? FontWeight.bold : FontWeight.w600,
                  color: score >= 1.0
                      ? const Color(0xFF16A34A)
                      : const Color(0xFF94A3B8),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          // Integrated Segmented Progress Bar: the step names ARE the bar
          ClipRRect(
            borderRadius: BorderRadius.circular(8),
            child: Container(
              height: 28,
              padding: const EdgeInsets.all(2),
              decoration: BoxDecoration(
                color: const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                children: [
                  _buildBarSegment(
                    title: isRtl ? 'اسم الصنف' : 'Item Name',
                    isDone: hasName,
                  ),
                  const SizedBox(width: 3),
                  _buildBarSegment(
                    title: isRtl ? 'التصنيف' : 'Category',
                    isDone: hasCategory,
                  ),
                  const SizedBox(width: 3),
                  _buildBarSegment(
                    title: isRtl ? 'صورة المنتج' : 'Photo',
                    isDone: hasImage,
                  ),
                  const SizedBox(width: 3),
                  _buildBarSegment(
                    title: isRtl ? 'التسعير' : 'Pricing',
                    isDone: hasPrice,
                  ),
                  const SizedBox(width: 3),
                  _buildBarSegment(
                    title: isRtl ? 'المخزون' : 'Stock',
                    isDone: hasStock,
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBarSegment({
    required String title,
    required bool isDone,
  }) {
    return Expanded(
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeInOut,
        height: 24,
        decoration: BoxDecoration(
          color: isDone ? const Color(0xFF16A34A) : Colors.white,
          borderRadius: BorderRadius.circular(6),
          border: Border.all(
            color: isDone ? const Color(0xFF16A34A) : const Color(0xFFE2E8F0),
            width: 1,
          ),
          boxShadow: isDone
              ? [
                  BoxShadow(
                    color: const Color(0xFF16A34A).withValues(alpha: 0.25),
                    blurRadius: 3,
                    offset: const Offset(0, 1),
                  ),
                ]
              : null,
        ),
        alignment: Alignment.center,
        padding: const EdgeInsets.symmetric(horizontal: 2),
        child: FittedBox(
          fit: BoxFit.scaleDown,
          child: Text(
            title,
            maxLines: 1,
            style: TextStyle(
              fontSize: 11,
              fontWeight: isDone ? FontWeight.bold : FontWeight.w600,
              color: isDone ? Colors.white : const Color(0xFF64748B),
              letterSpacing: -0.2,
            ),
          ),
        ),
      ),
    );
  }

  // ════════════════════════════════════════════════════════════════════════════
  // LIVE BUYER PREVIEW CARD (معاينة حية للمشتري)
  // ════════════════════════════════════════════════════════════════════════════
  Widget _buildLivePreviewCard(bool isRtl, List<dynamic> categories) {
    final arName = _nameArController.text.trim();
    final enName = _nameEnController.text.trim();
    final sdgPrice = double.tryParse(_priceController.text) ?? 0.0;
    final usdPrice = double.tryParse(_priceUsdController.text) ?? 0.0;

    final selectedCat = categories.cast<CategoryModel?>().firstWhere(
          (c) => c?.id == _selectedCategory,
          orElse: () => null,
        );
    final categoryName = selectedCat != null
        ? (isRtl ? selectedCat.nameAr : selectedCat.name)
        : (isRtl ? 'تصنيف الصنف' : 'Category');

    final unitStr = _isCustomUnit && _customUnitController.text.trim().isNotEmpty
        ? _customUnitController.text.trim()
        : (_selectedUnit ?? 'ton');

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0), width: 1.2),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.04),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      padding: const EdgeInsets.all(18),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Header row
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(
                  Icons.visibility_rounded,
                  color: Color(0xFF2563EB),
                  size: 20,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      isRtl ? 'معاينة الصنف في المنصة' : 'Marketplace Live Preview',
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF0F172A),
                      ),
                    ),
                    Text(
                      isRtl
                          ? 'شكل البطاقة الفعلي كما سيظهر للمشتري'
                          : 'How buyers see your product in app',
                      style: const TextStyle(
                        fontSize: 11,
                        color: Color(0xFF64748B),
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: const Color(0xFFBFDBFE)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      width: 6,
                      height: 6,
                      decoration: const BoxDecoration(
                        color: Color(0xFF2563EB),
                        shape: BoxShape.circle,
                      ),
                    ),
                    const SizedBox(width: 4),
                    Text(
                      isRtl ? 'مباشر' : 'Live',
                      style: const TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF1D4ED8),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          // Product Card Simulation
          Container(
            decoration: BoxDecoration(
              color: const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // ═══ Interactive Image Area with upload, zoom & pan ═══
                Stack(
                  children: [
                    ClipRRect(
                      borderRadius:
                          const BorderRadius.vertical(top: Radius.circular(13)),
                      child: GestureDetector(
                        onTap: _imageBytes == null
                            ? () => _pickProductImage()
                            : null,
                        child: AspectRatio(
                          aspectRatio: 4 / 3,
                          child: Container(
                            color: Colors.white,
                            child: _imageBytes != null
                              ? RepaintBoundary(
                                  key: _previewCropBoundaryKey,
                                  child: Container(
                                    color: Colors.white,
                                    child: InteractiveViewer(
                                      transformationController:
                                          _previewTransformController,
                                      minScale: 0.5,
                                      maxScale: 5.0,
                                      clipBehavior: Clip.antiAlias,
                                      child: Center(
                                        child: Image.memory(
                                          _imageBytes!,
                                          fit: BoxFit.cover,
                                          width: double.infinity,
                                          height: double.infinity,
                                        ),
                                      ),
                                    ),
                                  ),
                                )
                              : Center(
                                  child: Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.all(14),
                                        decoration: BoxDecoration(
                                          color: const Color(0xFFEFF6FF),
                                          shape: BoxShape.circle,
                                          border: Border.all(
                                            color: const Color(0xFFBFDBFE),
                                            width: 2,
                                            strokeAlign: BorderSide.strokeAlignOutside,
                                          ),
                                        ),
                                        child: const Icon(
                                          Icons.add_a_photo_rounded,
                                          size: 28,
                                          color: Color(0xFF2563EB),
                                        ),
                                      ),
                                      const SizedBox(height: 10),
                                      Text(
                                        isRtl
                                            ? 'انقر لرفع أو تصوير صورة المنتج'
                                            : 'Tap to capture or upload product image',
                                        style: const TextStyle(
                                          fontSize: 13,
                                          fontWeight: FontWeight.w600,
                                          color: Color(0xFF2563EB),
                                        ),
                                      ),
                                      const SizedBox(height: 8),
                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          InkWell(
                                            borderRadius: BorderRadius.circular(8),
                                            onTap: () => _pickProductImage(source: ImageSource.camera),
                                            child: Container(
                                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                              decoration: BoxDecoration(
                                                color: const Color(0xFF2563EB),
                                                borderRadius: BorderRadius.circular(8),
                                              ),
                                              child: Row(
                                                mainAxisSize: MainAxisSize.min,
                                                children: [
                                                  const Icon(Icons.photo_camera_rounded, size: 15, color: Colors.white),
                                                  const SizedBox(width: 5),
                                                  Text(
                                                    isRtl ? 'الكاميرا' : 'Camera',
                                                    style: const TextStyle(
                                                      fontSize: 11.5,
                                                      fontWeight: FontWeight.bold,
                                                      color: Colors.white,
                                                    ),
                                                  ),
                                                ],
                                              ),
                                            ),
                                          ),
                                          const SizedBox(width: 10),
                                          InkWell(
                                            borderRadius: BorderRadius.circular(8),
                                            onTap: () => _pickProductImage(source: ImageSource.gallery),
                                            child: Container(
                                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                              decoration: BoxDecoration(
                                                color: const Color(0xFF16A34A),
                                                borderRadius: BorderRadius.circular(8),
                                              ),
                                              child: Row(
                                                mainAxisSize: MainAxisSize.min,
                                                children: [
                                                  const Icon(Icons.photo_library_rounded, size: 15, color: Colors.white),
                                                  const SizedBox(width: 5),
                                                  Text(
                                                    isRtl ? 'المعرض' : 'Gallery',
                                                    style: const TextStyle(
                                                      fontSize: 11.5,
                                                      fontWeight: FontWeight.bold,
                                                      color: Colors.white,
                                                    ),
                                                  ),
                                                ],
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                      const SizedBox(height: 6),
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                            horizontal: 10, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: const Color(0xFFF1F5F9),
                                          borderRadius:
                                              BorderRadius.circular(6),
                                          border: Border.all(
                                              color: const Color(0xFFE2E8F0)),
                                        ),
                                        child: Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            const Icon(
                                                Icons.aspect_ratio_rounded,
                                                size: 12,
                                                color: Color(0xFF64748B)),
                                            const SizedBox(width: 4),
                                            Text(
                                              isRtl
                                                  ? 'المقاس الموصى به: 1200×900 (4:3) • الصيغ: JPG, PNG, WEBP'
                                                  : 'Recommended: 1200×900 (4:3) • Formats: JPG, PNG, WEBP',
                                              style: const TextStyle(
                                                fontSize: 9.5,
                                                fontWeight: FontWeight.w600,
                                                color: Color(0xFF475569),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],
                                  ),
                          ),
                        ),
                      ),
                    ),
                    ),
                    // In-stock badge
                    Positioned(
                      top: 8,
                      right: isRtl ? 8 : null,
                      left: isRtl ? null : 8,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: _inStock
                              ? const Color(0xFF16A34A)
                              : const Color(0xFFDC2626),
                          borderRadius: BorderRadius.circular(6),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.15),
                              blurRadius: 4,
                            ),
                          ],
                        ),
                        child: Text(
                          _inStock
                              ? (isRtl ? 'متوفر' : 'In Stock')
                              : (isRtl ? 'غير متوفر' : 'Out of Stock'),
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ),
                    // Category Tag
                    Positioned(
                      bottom: 8,
                      left: isRtl ? 8 : null,
                      right: isRtl ? null : 8,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: Colors.black.withValues(alpha: 0.65),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          categoryName,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 10,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                    ),
                    // Image action buttons (change / lock crop / remove) — only when image exists
                    if (_imageBytes != null)
                      Positioned(
                        top: 8,
                        left: isRtl ? null : 8,
                        right: isRtl ? 8 : null,
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            _buildImageActionBtn(
                              icon: Icons.camera_alt_rounded,
                              tooltip: isRtl ? 'تغيير الصورة' : 'Change Photo',
                              onTap: () => _pickProductImage(),
                              color: const Color(0xFF2563EB),
                            ),
                            const SizedBox(width: 6),
                            _buildImageActionBtn(
                              icon: Icons.crop_rounded,
                              tooltip: isRtl
                                  ? 'تثبيت وحفظ مقاس وتأطير الصورة'
                                  : 'Lock & Apply Framing/Crop',
                              onTap: _lockAndApplyCrop,
                              color: const Color(0xFF16A34A),
                              bgColor: const Color(0xFFDCFCE7),
                            ),
                            const SizedBox(width: 6),
                            _buildImageActionBtn(
                              icon: Icons.delete_outline_rounded,
                              tooltip: isRtl ? 'حذف الصورة' : 'Remove Photo',
                              onTap: () => setState(() {
                                _imageBytes = null;
                                _imageExt = null;
                                _previewTransformController.value =
                                    Matrix4.identity();
                              }),
                              color: const Color(0xFFDC2626),
                            ),
                          ],
                        ),
                      ),
                    // Zoom hint badge
                    if (_imageBytes != null)
                      Positioned(
                        bottom: 8,
                        left: isRtl ? 8 : null,
                        right: isRtl ? null : 8,
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.92),
                            borderRadius: BorderRadius.circular(6),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.08),
                                blurRadius: 4,
                              ),
                            ],
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.pinch_rounded,
                                  size: 13, color: Color(0xFF2563EB)),
                              const SizedBox(width: 4),
                              Text(
                                isRtl
                                    ? 'كبّر وحرّك، ثم اضغط زر التثبيت (الأخضر) لحفظ المقاس'
                                    : 'Pinch & drag, then tap green button to lock framing',
                                style: const TextStyle(
                                  fontSize: 9.5,
                                  color: Color(0xFF1E293B),
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                  ],
                ),
                // Details area
                Padding(
                  padding: const EdgeInsets.all(12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        arName.isNotEmpty
                            ? arName
                            : (isRtl
                                ? 'اسم الصنف والمقاس بالعربية'
                                : 'Product Name (Arabic)'),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                          color: arName.isNotEmpty
                              ? const Color(0xFF0F172A)
                              : const Color(0xFF94A3B8),
                        ),
                      ),
                      if (enName.isNotEmpty) ...[
                        const SizedBox(height: 2),
                        Text(
                          enName,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontSize: 11,
                            color: Color(0xFF64748B),
                          ),
                        ),
                      ],
                      const SizedBox(height: 8),
                      // Price row
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Text(
                                      sdgPrice > 0
                                          ? NumberFormat('#,###')
                                              .format(sdgPrice)
                                          : '0',
                                      style: const TextStyle(
                                        fontSize: 16,
                                        fontWeight: FontWeight.w900,
                                        color: Color(0xFF0F172A),
                                      ),
                                    ),
                                    const SizedBox(width: 4),
                                    Text(
                                      isRtl ? 'ج.س' : 'SDG',
                                      style: const TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.bold,
                                        color: Color(0xFF2563EB),
                                      ),
                                    ),
                                    Text(
                                      ' / $unitStr',
                                      style: const TextStyle(
                                        fontSize: 10,
                                        color: Color(0xFF64748B),
                                      ),
                                    ),
                                  ],
                                ),
                                if (usdPrice > 0)
                                  Text(
                                    '≈ \$${usdPrice.toStringAsFixed(2)} USD',
                                    style: const TextStyle(
                                      fontSize: 10,
                                      color: Color(0xFF059669),
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                              ],
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.all(6),
                            decoration: BoxDecoration(
                              color: const Color(0xFF2563EB),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Icon(
                              Icons.add_shopping_cart_rounded,
                              color: Colors.white,
                              size: 16,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildImageActionBtn({
    required IconData icon,
    required String tooltip,
    required VoidCallback onTap,
    required Color color,
    Color? bgColor,
  }) {
    return Material(
      color: bgColor ?? Colors.white.withValues(alpha: 0.92),
      borderRadius: BorderRadius.circular(8),
      elevation: 2,
      child: InkWell(
        borderRadius: BorderRadius.circular(8),
        onTap: onTap,
        child: Tooltip(
          message: tooltip,
          child: Padding(
            padding: const EdgeInsets.all(7),
            child: Icon(icon, size: 18, color: color),
          ),
        ),
      ),
    );
  }

  Future<void> _pickProductImage({ImageSource? source}) async {
    try {
      final pickedFile = await ImagePickerHelper.pickImage(
        context,
        source: source,
        imageQuality: 80,
        maxWidth: 1400,
      );
      if (pickedFile != null) {
        final bytes = await pickedFile.readAsBytes();
        final ext = pickedFile.name.split('.').last;
        if (mounted) {
          setState(() {
            _imageBytes = bytes;
            _imageExt = ext;
          });
        }
      }
    } catch (e) {
      if (!context.mounted) return;
      final isRtlMsg = Directionality.of(context) == TextDirection.rtl;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
              isRtlMsg ? 'فشل اختيار الصورة: $e' : 'Failed to pick image'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  // ════════════════════════════════════════════════════════════════════════════
  // ACTION & PUBLISH CARD
  // ════════════════════════════════════════════════════════════════════════════
  Widget _buildActionCard(bool isRtl) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0), width: 1.2),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.04),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          FilledButton.icon(
            style: FilledButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 16),
              backgroundColor: const Color(0xFF1E40AF),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              elevation: 2,
            ),
            onPressed: _isLoading ? null : _saveProduct,
            icon: _isLoading
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      color: Colors.white,
                    ),
                  )
                : const Icon(Icons.rocket_launch_rounded),
            label: Text(
              _isLoading
                  ? (isRtl ? 'جاري التحقق والنشر...' : 'Publishing...')
                  : (isRtl
                      ? 'حفظ ونشر الصنف في المنصة'
                      : 'Publish Item to Platform'),
              style: const TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
          const SizedBox(height: 10),
          OutlinedButton.icon(
            style: OutlinedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 12),
              side: const BorderSide(color: Color(0xFFCBD5E1)),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
            onPressed: () {
              showDialog(
                context: context,
                builder: (ctx) => AlertDialog(
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14)),
                  title: Text(isRtl ? 'إعادة ضبط النموذج؟' : 'Reset Form?'),
                  content: Text(isRtl
                      ? 'هل أنت متأكد من مسح جميع الحقول والبدء من جديد؟'
                      : 'Are you sure you want to clear all entered fields?'),
                  actions: [
                    TextButton(
                      onPressed: () => Navigator.of(ctx).pop(),
                      child: Text(isRtl ? 'إلغاء' : 'Cancel'),
                    ),
                    FilledButton(
                      style: FilledButton.styleFrom(
                          backgroundColor: const Color(0xFFDC2626)),
                      onPressed: () {
                        Navigator.of(ctx).pop();
                        _resetForm();
                      },
                      child: Text(isRtl ? 'نعم، إفراغ' : 'Yes, Clear'),
                    ),
                  ],
                ),
              );
            },
            icon: const Icon(Icons.refresh_rounded, size: 18, color: Color(0xFF64748B)),
            label: Text(
              isRtl ? 'تفريغ الحقول والبدء من جديد' : 'Clear & Reset',
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: Color(0xFF64748B),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ════════════════════════════════════════════════════════════════════════════
  // PAYMENT METHODS CHIP
  // ════════════════════════════════════════════════════════════════════════════
  Widget _buildPaymentMethodCheck({
    required String key,
    required String title,
    required IconData icon,
    required Color color,
  }) {
    final isSelected = _selectedPaymentMethods.contains(key);
    return InkWell(
      onTap: () {
        setState(() {
          if (isSelected) {
            if (_selectedPaymentMethods.length > 1) {
              _selectedPaymentMethods.remove(key);
            }
          } else {
            _selectedPaymentMethods.add(key);
          }
        });
      },
      borderRadius: BorderRadius.circular(12),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: isSelected ? color.withValues(alpha: 0.08) : const Color(0xFFF8FAFC),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isSelected ? color : const Color(0xFFE2E8F0),
            width: isSelected ? 1.5 : 1,
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              isSelected
                  ? Icons.check_box_rounded
                  : Icons.check_box_outline_blank_rounded,
              size: 18,
              color: isSelected ? color : const Color(0xFF94A3B8),
            ),
            const SizedBox(width: 8),
            Icon(icon, size: 16, color: color),
            const SizedBox(width: 6),
            Text(
              title,
              style: TextStyle(
                fontSize: 12,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                color: isSelected ? color : const Color(0xFF334155),
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
    final allCategories = categoriesAsync.valueOrNull ?? [];

    final categories = allCategories.where((c) {
      if (c.isExclusiveToMagaiees) return false;
      if (_allowAllCategories || _allowedCategoryIds == null) return true;

      final isCatAllowed = _allowedCategoryIds!.contains(c.id.toString());
      final hasAllowedSub = c.subCategories
          .any((s) => _allowedCategoryIds!.contains(s.id.toString()));
      return isCatAllowed || hasAllowedSub;
    }).toList();

    final effectiveSelectedCategory = categories.any((c) => c.id == _selectedCategory)
        ? _selectedCategory
        : (categories.length == 1 ? categories.first.id : null);

    if (_selectedCategory == null && categories.length == 1) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (mounted && _selectedCategory == null) {
          setState(() => _selectedCategory = categories.first.id);
        }
      });
    }

    final currentCat = categories.cast<CategoryModel?>().firstWhere(
          (c) => c?.id == effectiveSelectedCategory,
          orElse: () => null,
        );
    final List<SubCategoryModel> allSubCategories =
        currentCat?.subCategories ?? [];
    final List<SubCategoryModel> subCategories = allSubCategories.where((s) {
      if (_allowAllCategories || _allowedCategoryIds == null) return true;
      if (_allowedCategoryIds!.contains(currentCat?.id.toString())) return true;
      return _allowedCategoryIds!.contains(s.id.toString());
    }).toList();
    final effectiveSelectedSubCategory = subCategories.any((s) => s.id == _selectedSubCategory)
        ? _selectedSubCategory
        : null;

    final globalSettings = ref.watch(globalSettingsProvider);
    final storeInfo = ref.watch(merchantStoreInfoProvider).valueOrNull;

    if (storeInfo != null) {
      final source = storeInfo['usd_rate_source']?.toString() ?? 'platform';
      _usdRateSource = source;
      final customRate = double.tryParse(
              storeInfo['store_usd_rate']?.toString() ?? '') ??
          double.tryParse(storeInfo['usd_exchange_rate']?.toString() ?? '') ??
          globalSettings.platformUsdRate;
      _exchangeRate =
          source == 'platform' ? globalSettings.platformUsdRate : customRate;
    } else if (_usdRateSource == 'platform') {
      _exchangeRate = globalSettings.platformUsdRate;
    }

    if (_prevExchangeRate > 0 && _prevExchangeRate != _exchangeRate) {
      final usdVal = double.tryParse(_priceUsdController.text) ?? 0.0;
      if (usdVal > 0) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (mounted) {
            _priceController.text =
                (usdVal * _exchangeRate).toStringAsFixed(0);
          }
        });
      }
    }
    _prevExchangeRate = _exchangeRate;

    final seenUnitIds = <String>{};
    final List<Map<String, String>> unitOptions = [
      for (final u in globalSettings.measurementUnits)
        if (u.id.isNotEmpty && u.id != 'custom' && seenUnitIds.add(u.id))
          {
            'id': u.id,
            'nameEn': u.nameEn,
            'nameAr': u.nameAr,
            'symbol': u.symbol,
          },
      {
        'id': 'custom',
        'nameEn': '+ Other (Enter custom unit)',
        'nameAr': '+ أخرى (إدخال وحدة مخصصة)'
      },
    ];

    final screenWidth = MediaQuery.of(context).size.width;
    final isDesktop = screenWidth >= 960;

    // Quick Unit list for quick selection chips
    final popularUnits = [
      {'id': 'ton', 'nameAr': 'طن (Ton)', 'nameEn': 'Ton'},
      {'id': 'kg', 'nameAr': 'كجم (Kg)', 'nameEn': 'Kg'},
      {'id': 'meter', 'nameAr': 'متر (Meter)', 'nameEn': 'Meter'},
      {'id': 'piece', 'nameAr': 'حبة / قطعة', 'nameEn': 'Piece'},
      {'id': 'carton', 'nameAr': 'كرتونة', 'nameEn': 'Carton'},
      {'id': 'custom', 'nameAr': '+ أخرى مخصصة', 'nameEn': '+ Custom'},
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // CARDS DEFINITIONS
    // ─────────────────────────────────────────────────────────────────────────

    // Card 1: Category & Classification
    final categoryCard = _buildSectionCard(
      header: _buildSectionHeader(
        icon: Icons.category_rounded,
        iconColor: const Color(0xFF2563EB),
        iconBg: const Color(0xFFEFF6FF),
        title: isRtl ? 'التصنيف والهيكلية' : 'Category & Classification',
        subtitle: isRtl
            ? 'حدد القسم الرئيسي والماركة أو القسم الفرعي لسهولة وصول المشترين'
            : 'Select main category and subcategory / brand for buyer discovery',
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Permissions badge / warning
          if (!_allowAllCategories &&
              categories.isEmpty &&
              !_isLoadingPerms) ...[
            Container(
              margin: const EdgeInsets.only(bottom: 16),
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: const Color(0xFFFFFBEB),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFFDE68A)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.lock_person_rounded,
                      color: Color(0xFFD97706), size: 24),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      isRtl
                          ? '🔒 متجرك بانتظار تحديد الأقسام المصرحة من قبل إدارة المنصة. يرجى التواصل مع الدعم لتفعيلها.'
                          : '🔒 Your store is awaiting authorized category assignment by admin.',
                      style: const TextStyle(
                        color: Color(0xFF92400E),
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ] else if (!_allowAllCategories && categories.isNotEmpty) ...[
            Container(
              margin: const EdgeInsets.only(bottom: 16),
              padding:
                  const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: const Color(0xFFF0FDF4),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFBBF7D0)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.verified_rounded,
                      color: Color(0xFF16A34A), size: 18),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      isRtl
                          ? 'الأقسام المعتمدة والمصرحة لمتجرك (${categories.length} أقسام)'
                          : 'Authorized categories for your store (${categories.length})',
                      style: const TextStyle(
                        color: Color(0xFF166534),
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],

          DropdownButtonFormField<String>(
            decoration: InputDecoration(
              labelText: isRtl ? 'القسم الرئيسي' : 'Main Category',
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide:
                    const BorderSide(color: Color(0xFF2563EB), width: 1.8),
              ),
              filled: true,
              fillColor: const Color(0xFFF8FAFC),
              prefixIcon: const Icon(Icons.category, color: Color(0xFF2563EB)),
            ),
            value: effectiveSelectedCategory,
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
            validator: (value) => value == null
                ? (isRtl ? 'الرجاء اختيار القسم الرئيسي' : 'Required field')
                : null,
          ),
          const SizedBox(height: 16),

          DropdownButtonFormField<String>(
            decoration: InputDecoration(
              labelText: isRtl
                  ? 'القسم الفرعي / الماركة / النوع'
                  : 'Sub-Category / Brand / Line',
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide:
                    const BorderSide(color: Color(0xFF2563EB), width: 1.8),
              ),
              filled: true,
              fillColor: const Color(0xFFF8FAFC),
              prefixIcon: const Icon(Icons.branding_watermark_rounded,
                  color: Color(0xFF2563EB)),
              helperText: _selectedCategory == null
                  ? (isRtl
                      ? 'اختر القسم الرئيسي أولاً لتحميل الماركات والأقسام الفرعية'
                      : 'Select main category first to load brands')
                  : null,
            ),
            value: effectiveSelectedSubCategory,
            items: subCategories.map((sub) {
              return DropdownMenuItem<String>(
                value: sub.id,
                child: Text(isRtl ? sub.nameAr : sub.name),
              );
            }).toList(),
            onChanged: effectiveSelectedCategory == null
                ? null
                : (val) {
                    setState(() {
                      _selectedSubCategory = val;
                    });
                  },
            validator: (value) => subCategories.isNotEmpty && value == null
                ? (isRtl
                    ? 'الرجاء اختيار الماركة أو القسم الفرعي'
                    : 'Required field')
                : null,
          ),
        ],
      ),
    );

    // Card 2: Item Identity & Smart Translation
    final identityCard = _buildSectionCard(
      header: _buildSectionHeader(
        icon: Icons.auto_awesome_rounded,
        iconColor: const Color(0xFF7C3AED),
        iconBg: const Color(0xFFF5F3FF),
        title: isRtl ? 'هوية الصنف والترجمة الذكية' : 'Item Identity & AI Translation',
        subtitle: isRtl
            ? 'اكتب الاسم بالعربية وسيقوم النظام بترجمته فورياً إلى الإنجليزية'
            : 'Type Arabic name and AI will auto-translate to English in real time',
        trailing: Container(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
          decoration: BoxDecoration(
            color: const Color(0xFFF5F3FF),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: const Color(0xFFDDD6FE)),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.bolt_rounded, size: 14, color: Color(0xFF7C3AED)),
              const SizedBox(width: 4),
              Text(
                isRtl ? 'ترجمة فورية' : 'AI Powered',
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF7C3AED),
                ),
              ),
            ],
          ),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          TextFormField(
            controller: _nameArController,
            onChanged: _onArabicNameChanged,
            decoration: InputDecoration(
              labelText: isRtl
                  ? 'اسم الصنف والمقاس (عربي) *'
                  : 'Item Name & Size (Arabic) *',
              hintText: isRtl
                  ? 'مثلاً: حديد تسليح تركي 16 ملم أو أسمنت بورتلاندي 50 كجم'
                  : 'e.g. Rebar 16mm Turkish',
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide:
                    const BorderSide(color: Color(0xFF2563EB), width: 1.8),
              ),
              filled: true,
              fillColor: const Color(0xFFF8FAFC),
              prefixIcon:
                  const Icon(Icons.edit_note_rounded, color: Color(0xFF2563EB)),
            ),
            validator: (value) =>
                DataValidator.validateProductTitle(value, isRtl: isRtl),
          ),
          const SizedBox(height: 14),

          TextFormField(
            controller: _nameEnController,
            decoration: InputDecoration(
              labelText: isRtl
                  ? 'اسم الصنف (إنجليزي - مترجم تلقائياً) *'
                  : 'Item Name (English - Auto-translated) *',
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide:
                    const BorderSide(color: Color(0xFF2563EB), width: 1.8),
              ),
              filled: true,
              fillColor: const Color(0xFFF8FAFC),
              prefixIcon:
                  const Icon(Icons.language_rounded, color: Color(0xFF7C3AED)),
              suffixIcon: _isTranslating
                  ? const Padding(
                      padding: EdgeInsets.all(12),
                      child: SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      ),
                    )
                  : IconButton(
                      icon: const Icon(Icons.translate_rounded,
                          color: Color(0xFF7C3AED)),
                      tooltip: isRtl ? 'ترجمة فورية الآن' : 'Translate now',
                      onPressed: () {
                        if (_nameArController.text.isNotEmpty) {
                          _onArabicNameChanged(_nameArController.text);
                        }
                      },
                    ),
            ),
            validator: (value) =>
                DataValidator.validateProductTitle(value, isRtl: isRtl),
          ),
          const SizedBox(height: 14),

          TextFormField(
            controller: _descriptionController,
            maxLines: 3,
            decoration: InputDecoration(
              labelText: isRtl
                  ? 'وصف الصنف والمواصفات الفنية (اختياري)'
                  : 'Product Description & Technical Specs (Optional)',
              hintText: isRtl
                  ? 'اكتب تفاصيل إضافية عن الجودة، المنشأ، معايير المطابقة الفنية، أو شروط التوريد...'
                  : 'Additional specs, origin, compliance details, delivery conditions...',
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide:
                    const BorderSide(color: Color(0xFF2563EB), width: 1.8),
              ),
              filled: true,
              fillColor: const Color(0xFFF8FAFC),
              prefixIcon: const Icon(Icons.description_outlined,
                  color: Color(0xFF64748B)),
            ),
          ),
        ],
      ),
    );


    // Card 4: Smart Pricing & Currency Converter
    final pricingCard = _buildSectionCard(
      header: _buildSectionHeader(
        icon: Icons.payments_rounded,
        iconColor: const Color(0xFF16A34A),
        iconBg: const Color(0xFFDCFCE7),
        title: isRtl ? 'التسعير الذكي والوحدات' : 'Smart Pricing & Units',
        subtitle: isRtl
            ? 'ربط مباشر وفوري بين الدولار والجنيه السوداني وفق سعر الصرف'
            : 'Real-time dual-currency converter based on current exchange rate',
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Live Exchange Rate Banner
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFFF0F9FF), Color(0xFFE0F2FE)],
              ),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFBAE6FD)),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(6),
                  decoration: const BoxDecoration(
                    color: Color(0xFF0284C7),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.currency_exchange_rounded,
                      color: Colors.white, size: 16),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        isRtl
                            ? 'سعر صرف الدولار الحالي: ${_exchangeRate.toStringAsFixed(0)} ج.س = 1\$'
                            : 'Live Exchange Rate: 1\$ = ${_exchangeRate.toStringAsFixed(0)} SDG',
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 13,
                          color: Color(0xFF0C4A6E),
                        ),
                      ),
                      Text(
                        _usdRateSource == 'platform'
                            ? (isRtl ? 'سعر صرف المنصة المعتمد' : 'Official Platform Rate')
                            : (isRtl ? 'سعر صرف مخصص لمتجرك' : 'Custom Store Rate'),
                        style: const TextStyle(
                          fontSize: 10,
                          color: Color(0xFF0369A1),
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: const Color(0xFF0EA5E9),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        width: 6,
                        height: 6,
                        decoration: const BoxDecoration(
                          color: Colors.white,
                          shape: BoxShape.circle,
                        ),
                      ),
                      const SizedBox(width: 5),
                      Text(
                        isRtl ? 'تحديث فوري' : 'Live Sync',
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 11,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Dual Price Inputs
          Row(
            children: [
              Expanded(
                child: TextFormField(
                  controller: _priceUsdController,
                  keyboardType:
                      const TextInputType.numberWithOptions(decimal: true),
                  decoration: InputDecoration(
                    labelText: isRtl ? 'السعر بالدولار (\$)' : 'Price (USD)',
                    hintText: '0.00',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(
                          color: Color(0xFF2563EB), width: 1.8),
                    ),
                    filled: true,
                    fillColor: const Color(0xFFF8FAFC),
                    prefixIcon: const Icon(Icons.attach_money_rounded,
                        color: Color(0xFF16A34A)),
                  ),
                  onChanged: (val) {
                    final usd = double.tryParse(val) ?? 0.0;
                    _priceController.text =
                        (usd * _exchangeRate).toStringAsFixed(0);
                  },
                  validator: (value) => DataValidator.validatePrice(
                    value,
                    isRtl: isRtl,
                    min: 0.01,
                    fieldName: isRtl ? 'السعر بالدولار' : 'USD Price',
                  ),
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: TextFormField(
                  controller: _priceController,
                  keyboardType:
                      const TextInputType.numberWithOptions(decimal: true),
                  decoration: InputDecoration(
                    labelText: isRtl ? 'السعر (ج.س)' : 'Price (SDG)',
                    hintText: '0',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(
                          color: Color(0xFF2563EB), width: 1.8),
                    ),
                    filled: true,
                    fillColor: const Color(0xFFF8FAFC),
                    prefixIcon: const Icon(Icons.account_balance_wallet_rounded,
                        color: Color(0xFF2563EB)),
                  ),
                  onChanged: (val) {
                    final sdg = double.tryParse(val) ?? 0.0;
                    if (_exchangeRate > 0) {
                      _priceUsdController.text =
                          (sdg / _exchangeRate).toStringAsFixed(2);
                    }
                  },
                  validator: (value) => DataValidator.validatePrice(
                    value,
                    isRtl: isRtl,
                    min: 1.0,
                    fieldName: isRtl ? 'السعر بالجنيه' : 'SDG Price',
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),

          // Unit of Measurement Section
          Text(
            isRtl ? 'وحدة البيع والقياس' : 'Unit of Measurement',
            style: const TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.bold,
              color: Color(0xFF334155),
            ),
          ),
          const SizedBox(height: 8),

          // Quick selection chips
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: popularUnits.map((u) {
              final isSelected = (_selectedUnit == u['id']) ||
                  (_isCustomUnit && u['id'] == 'custom');
              return ChoiceChip(
                selected: isSelected,
                label: Text(isRtl ? u['nameAr']! : u['nameEn']!),
                selectedColor: const Color(0xFF2563EB),
                labelStyle: TextStyle(
                  color: isSelected ? Colors.white : const Color(0xFF0F172A),
                  fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                  fontSize: 12,
                ),
                backgroundColor: const Color(0xFFF1F5F9),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                  side: BorderSide(
                    color: isSelected
                        ? const Color(0xFF2563EB)
                        : const Color(0xFFE2E8F0),
                  ),
                ),
                onSelected: (selected) {
                  setState(() {
                    if (u['id'] == 'custom') {
                      _selectedUnit = 'custom';
                      _isCustomUnit = true;
                    } else {
                      _selectedUnit = u['id'];
                      _isCustomUnit = false;
                    }
                  });
                },
              );
            }).toList(),
          ),

          const SizedBox(height: 12),
          // Complete standard units dropdown
          DropdownButtonFormField<String>(
            decoration: InputDecoration(
              labelText: isRtl
                  ? 'أو اختر من جميع وحدات القياس المتاحة'
                  : 'Or select from all standard units',
              contentPadding:
                  const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
              ),
              filled: true,
              fillColor: const Color(0xFFF8FAFC),
              prefixIcon: const Icon(Icons.straighten_rounded,
                  color: Color(0xFF64748B), size: 18),
            ),
            value: unitOptions.any((u) => u['id'] == _selectedUnit)
                ? _selectedUnit
                : null,
            items: unitOptions.map((unit) {
              return DropdownMenuItem(
                value: unit['id'],
                child: Text(
                  isRtl ? unit['nameAr']! : unit['nameEn']!,
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: unit['id'] == 'custom'
                        ? FontWeight.bold
                        : FontWeight.normal,
                    color: unit['id'] == 'custom'
                        ? const Color(0xFF1E40AF)
                        : null,
                  ),
                ),
              );
            }).toList(),
            onChanged: (val) {
              if (val == null) return;
              setState(() {
                _selectedUnit = val;
                _isCustomUnit = (val == 'custom');
              });
            },
          ),

          if (_isCustomUnit) ...[
            const SizedBox(height: 12),
            TextFormField(
              controller: _customUnitController,
              decoration: InputDecoration(
                labelText: isRtl
                    ? 'اكتب وحدة القياس المخصصة'
                    : 'Enter Custom Measurement Unit',
                hintText: isRtl
                    ? 'مثلاً: بلكة، طرد، أنبوبة، قلاب 18م، لفة 100م'
                    : 'e.g. Bundle, Box, Pallet, Roll',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(
                      color: Color(0xFF2563EB), width: 1.8),
                ),
                filled: true,
                fillColor: const Color(0xFFF8FAFC),
                prefixIcon: const Icon(Icons.straighten_rounded,
                    color: Color(0xFF2563EB)),
              ),
              validator: (val) => _isCustomUnit
                  ? DataValidator.validateName(val,
                      fieldName:
                          isRtl ? 'وحدة القياس المخصصة' : 'Custom unit',
                      isRtl: isRtl,
                      minLength: 2)
                  : null,
            ),
          ],
        ],
      ),
    );

    // Card 5: Inventory & Availability
    final stockCard = _buildSectionCard(
      header: _buildSectionHeader(
        icon: Icons.inventory_2_rounded,
        iconColor: const Color(0xFFD97706),
        iconBg: const Color(0xFFFEF3C7),
        title: isRtl ? 'المخزون والتوفر الفوري' : 'Inventory & Availability',
        subtitle: isRtl
            ? 'حدد ما إذا كان الصنف متاحاً للطلب الفوري والكمية المتوفرة في المستودع'
            : 'Toggle instant availability and specify available quantity in warehouse',
        trailing: Container(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
          decoration: BoxDecoration(
            color: _inStock ? const Color(0xFFDCFCE7) : const Color(0xFFFEE2E2),
            borderRadius: BorderRadius.circular(20),
          ),
          child: Text(
            _inStock
                ? (isRtl ? 'جاهز للطلب' : 'Available')
                : (isRtl ? 'غير متوفر' : 'Unavailable'),
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.bold,
              color: _inStock ? const Color(0xFF166534) : const Color(0xFF991B1B),
            ),
          ),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SwitchListTile(
            contentPadding: EdgeInsets.zero,
            title: Text(
              isRtl ? 'متوفر في المخزون حالياً' : 'In Stock Now',
              style: const TextStyle(
                fontWeight: FontWeight.bold,
                fontSize: 14,
                color: Color(0xFF0F172A),
              ),
            ),
            subtitle: Text(
              isRtl
                  ? 'سيظهر للمشترين بعلامة خضراء وجاهز للشراء والتوريد الفوري'
                  : 'Visible to buyers as ready for instant purchase and delivery',
              style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
            ),
            value: _inStock,
            activeThumbColor: const Color(0xFF16A34A),
            onChanged: (val) {
              setState(() {
                _inStock = val;
              });
            },
          ),
          if (_inStock) ...[
            const SizedBox(height: 12),
            TextFormField(
              controller: _stockController,
              keyboardType: TextInputType.number,
              decoration: InputDecoration(
                labelText: isRtl
                    ? 'الكمية المتوفرة في المستودع'
                    : 'Available Stock Quantity',
                hintText: '100',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(
                      color: Color(0xFF2563EB), width: 1.8),
                ),
                filled: true,
                fillColor: const Color(0xFFF8FAFC),
                prefixIcon: const Icon(Icons.inventory_rounded,
                    color: Color(0xFFD97706)),
                suffixText: _isCustomUnit &&
                        _customUnitController.text.isNotEmpty
                    ? _customUnitController.text
                    : (_selectedUnit ?? ''),
              ),
              validator: (val) {
                if (!_inStock) return null;
                if (val == null || val.trim().isEmpty) {
                  return isRtl
                      ? 'يرجى إدخال الكمية المتوفرة'
                      : 'Please enter stock quantity';
                }
                final num = int.tryParse(val.trim());
                if (num == null || num < 0) {
                  return isRtl
                      ? 'يرجى إدخال رقم صحيح'
                      : 'Valid positive number required';
                }
                return null;
              },
            ),
            const SizedBox(height: 10),
            // Quick quantity helper pills
            Wrap(
              spacing: 6,
              runSpacing: 6,
              crossAxisAlignment: WrapCrossAlignment.center,
              children: [
                Text(
                  isRtl ? 'إضافة سريعة: ' : 'Quick fill: ',
                  style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                ),
                ...[50, 100, 250, 500, 1000].map((qty) {
                  return InkWell(
                    onTap: () {
                      setState(() {
                        _stockController.text = qty.toString();
                      });
                    },
                    borderRadius: BorderRadius.circular(6),
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF1F5F9),
                        borderRadius: BorderRadius.circular(6),
                        border: Border.all(color: const Color(0xFFCBD5E1)),
                      ),
                      child: Text(
                        '$qty',
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF334155),
                        ),
                      ),
                    ),
                  );
                }),
              ],
            ),
          ],
        ],
      ),
    );

    // Card 6: Expiry Date & Warranty (Optional)
    final expiryCard = _buildSectionCard(
      header: _buildSectionHeader(
        icon: Icons.event_available_rounded,
        iconColor: const Color(0xFFE11D48),
        iconBg: const Color(0xFFFFE4E6),
        title: isRtl
            ? 'تاريخ الصلاحية وفترة الضمان (اختياري)'
            : 'Expiry Date & Warranty Period (Optional)',
        subtitle: isRtl
            ? 'مهم لمواد البناء سريعة التلف كالأسمنت والغراء أو ضمان المعدات'
            : 'Useful for perishable materials (cement, adhesives) or equipment warranty',
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SwitchListTile(
            contentPadding: EdgeInsets.zero,
            title: Text(
              isRtl
                  ? 'تحديد تاريخ انتهاء الصلاحية أو الضمان لهذا المنتج'
                  : 'Set Expiry Date or Warranty Period',
              style: const TextStyle(
                fontWeight: FontWeight.bold,
                fontSize: 13,
                color: Color(0xFF0F172A),
              ),
            ),
            subtitle: Text(
              isRtl
                  ? 'مناسب للأسمنت (3 أشهر)، الدهانات (سنة)، والأجهزة الكهربائية (ضمان سنتين)'
                  : 'Recommended for cement (3 mos), paints (1 yr), equipment (2 yrs)',
              style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
            ),
            value: _hasExpiryDate,
            activeThumbColor: const Color(0xFFE11D48),
            onChanged: (val) {
              setState(() {
                _hasExpiryDate = val;
                if (val && _expiryDate == null) {
                  _expiryDate =
                      DateTime.now().add(const Duration(days: 90));
                }
              });
            },
          ),
          if (_hasExpiryDate) ...[
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 14, vertical: 12),
                      side: const BorderSide(color: Color(0xFFE11D48)),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                    icon: const Icon(Icons.calendar_month_rounded,
                        size: 18, color: Color(0xFFE11D48)),
                    label: Text(
                      _expiryDate != null
                          ? (isRtl
                              ? 'تاريخ الصلاحية: ${DateFormat("yyyy-MM-dd").format(_expiryDate!)}'
                              : 'Expiry: ${DateFormat("yyyy-MM-dd").format(_expiryDate!)}')
                          : (isRtl ? 'اختر التاريخ' : 'Select Date'),
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF0F172A),
                        fontSize: 13,
                      ),
                    ),
                    onPressed: () async {
                      final picked = await showDatePicker(
                        context: context,
                        initialDate: _expiryDate ??
                            DateTime.now().add(const Duration(days: 90)),
                        firstDate: DateTime.now(),
                        lastDate: DateTime.now()
                            .add(const Duration(days: 365 * 10)),
                      );
                      if (picked != null) {
                        setState(() => _expiryDate = picked);
                      }
                    },
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Wrap(
              spacing: 6,
              runSpacing: 6,
              children: [
                ActionChip(
                  label: Text(isRtl ? '+3 أشهر (أسمنت)' : '+3 Months'),
                  backgroundColor: const Color(0xFFFFF1F2),
                  labelStyle: const TextStyle(fontSize: 11, color: Color(0xFFBE123C)),
                  onPressed: () => setState(() => _expiryDate =
                      DateTime.now().add(const Duration(days: 90))),
                ),
                ActionChip(
                  label: Text(isRtl ? '+6 أشهر' : '+6 Months'),
                  backgroundColor: const Color(0xFFFFF1F2),
                  labelStyle: const TextStyle(fontSize: 11, color: Color(0xFFBE123C)),
                  onPressed: () => setState(() => _expiryDate =
                      DateTime.now().add(const Duration(days: 180))),
                ),
                ActionChip(
                  label: Text(isRtl ? '+1 سنة (دهانات)' : '+1 Year'),
                  backgroundColor: const Color(0xFFFFF1F2),
                  labelStyle: const TextStyle(fontSize: 11, color: Color(0xFFBE123C)),
                  onPressed: () => setState(() => _expiryDate =
                      DateTime.now().add(const Duration(days: 365))),
                ),
                ActionChip(
                  label: Text(isRtl ? '+2 سنة (ضمان أجهزة)' : '+2 Years'),
                  backgroundColor: const Color(0xFFFFF1F2),
                  labelStyle: const TextStyle(fontSize: 11, color: Color(0xFFBE123C)),
                  onPressed: () => setState(() => _expiryDate =
                      DateTime.now().add(const Duration(days: 730))),
                ),
              ],
            ),
          ],
        ],
      ),
    );

    // Card 7: Accepted Payment Methods
    final paymentCard = _buildSectionCard(
      header: _buildSectionHeader(
        icon: Icons.account_balance_wallet_rounded,
        iconColor: const Color(0xFF4F46E5),
        iconBg: const Color(0xFFEEF2FF),
        title: isRtl
            ? 'طرق الدفع المعتمدة لهذا الصنف'
            : 'Accepted Payment Methods',
        subtitle: isRtl
            ? 'اختر القنوات المالية المتاحة للمشتري لإتمام طلب هذا المنتج'
            : 'Select financial channels accepted for purchasing this item',
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Consumer(
            builder: (context, ref, _) {
              final settings = ref.watch(globalSettingsProvider);
              return Wrap(
                spacing: 10,
                runSpacing: 10,
                children: [
                  if (settings.merchantAllowCod)
                    _buildPaymentMethodCheck(
                      key: 'cod',
                      title: isRtl
                          ? 'الدفع عند الاستلام (COD)'
                          : 'Cash on Delivery',
                      icon: Icons.handshake_rounded,
                      color: const Color(0xFFD97706),
                    ),
                  if (settings.merchantAllowBankak)
                    _buildPaymentMethodCheck(
                      key: 'bankak',
                      title: isRtl
                          ? 'تطبيق بنكك (Bankak)'
                          : 'Bankak App',
                      icon: Icons.account_balance_wallet_rounded,
                      color: const Color(0xFF059669),
                    ),
                  if (settings.merchantAllowOcash)
                    _buildPaymentMethodCheck(
                      key: 'ocash',
                      title: isRtl
                          ? 'تطبيق أوكاش (O-Cash)'
                          : 'O-Cash App',
                      icon: Icons.payments_rounded,
                      color: const Color(0xFF7C3AED),
                    ),
                  if (settings.merchantAllowWire)
                    _buildPaymentMethodCheck(
                      key: 'bank',
                      title: isRtl
                          ? 'تحويل بنكي مباشر'
                          : 'Bank Wire',
                      icon: Icons.account_balance_rounded,
                      color: const Color(0xFF2563EB),
                    ),
                  if (settings.merchantAllowB2b)
                    _buildPaymentMethodCheck(
                      key: 'b2b',
                      title: isRtl
                          ? 'دفع آجل للشركات (B2B)'
                          : 'B2B Credit',
                      icon: Icons.business_rounded,
                      color: const Color(0xFF0D9488),
                    ),
                  if (settings.merchantAllowPartialCod)
                    _buildPaymentMethodCheck(
                      key: 'partial_cod',
                      title: isRtl
                          ? 'دفع جزئي والباقي عند الاستلام'
                          : 'Partial Payment + COD',
                      icon: Icons.pie_chart_outline_rounded,
                      color: const Color(0xFFEA580C),
                    ),
                ],
              );
            },
          ),
        ],
      ),
    );

    // ─────────────────────────────────────────────────────────────────────────
    // SCAFFOLD & RESPONSIVE LAYOUT
    // ─────────────────────────────────────────────────────────────────────────

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Text(
          isRtl ? 'إضافة صنف ومنتج جديد' : 'Add New Item / Product',
          style: const TextStyle(
            fontWeight: FontWeight.bold,
            fontSize: 18,
          ),
        ),
        elevation: 0.5,
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF0F172A),
        actions: [
          TextButton.icon(
            onPressed: _resetForm,
            icon: const Icon(Icons.refresh_rounded, size: 16),
            label: Text(isRtl ? 'إفراغ' : 'Reset'),
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: Form(
        key: _formKey,
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 1200),
              child: isDesktop
                  ? Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Left / Main Column (60%)
                        Expanded(
                          flex: 60,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              _buildReadinessBar(isRtl),
                              const SizedBox(height: 20),
                              categoryCard,
                              const SizedBox(height: 20),
                              identityCard,
                              const SizedBox(height: 20),
                              pricingCard,
                              const SizedBox(height: 20),
                              stockCard,
                              const SizedBox(height: 20),
                              expiryCard,
                              const SizedBox(height: 20),
                              paymentCard,
                            ],
                          ),
                        ),
                        const SizedBox(width: 24),
                        // Right / Sidebar Column (40%)
                        Expanded(
                          flex: 40,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              _buildActionCard(isRtl),
                              const SizedBox(height: 20),
                              _buildLivePreviewCard(isRtl, categories),
                            ],
                          ),
                        ),
                      ],
                    )
                  : Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        _buildReadinessBar(isRtl),
                        const SizedBox(height: 16),
                        categoryCard,
                        const SizedBox(height: 16),
                        identityCard,
                        const SizedBox(height: 16),
                        _buildLivePreviewCard(isRtl, categories),
                        const SizedBox(height: 16),
                        pricingCard,
                        const SizedBox(height: 16),
                        stockCard,
                        const SizedBox(height: 16),
                        expiryCard,
                        const SizedBox(height: 16),
                        paymentCard,
                        const SizedBox(height: 20),
                        _buildActionCard(isRtl),
                      ],
                    ),
            ),
          ),
        ),
      ),
    );
  }
}
