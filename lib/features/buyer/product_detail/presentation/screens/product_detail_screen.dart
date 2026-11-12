import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:magaiees_marketplace/features/buyer/home/application/home_provider.dart';
import 'package:magaiees_marketplace/features/buyer/cart/application/cart_provider.dart';
import 'package:magaiees_marketplace/features/chat/application/chat_provider.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/core/utils/app_notification.dart';
import 'package:magaiees_marketplace/core/utils/unit_formatter.dart';
import 'package:magaiees_marketplace/core/enums/user_role.dart';
import 'package:magaiees_marketplace/core/providers/ugc_moderation_provider.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/product_card_model.dart';
import 'package:intl/intl.dart' hide TextDirection;

class ProductDetailScreen extends ConsumerStatefulWidget {
  final String productId;
  const ProductDetailScreen({super.key, required this.productId});

  @override
  ConsumerState<ProductDetailScreen> createState() => _ProductDetailScreenState();
}

class _ProductDetailScreenState extends ConsumerState<ProductDetailScreen> {
  int quantity = 1;
  bool _isConnectingChat = false;

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final productsAsync = ref.watch(productsProvider);

    return Scaffold(
      appBar: AppBar(
        title: Text(isRtl ? 'تفاصيل المنتج' : 'Product Details'),
        actions: [
          PopupMenuButton<String>(
            icon: const Icon(Icons.more_vert),
            tooltip: isRtl ? 'خيارات الإبلاغ والحظر' : 'Report & Block Options',
            onSelected: (value) {
              final currentProducts = productsAsync.valueOrNull ?? const <ProductCardModel>[];
              final idx = currentProducts.indexWhere((p) => p.id == widget.productId);
              final targetProduct = idx >= 0 ? currentProducts[idx] : null;
              if (value == 'report') {
                _showReportDialog(context, isRtl, targetProduct);
              } else if (value == 'block') {
                _showBlockSellerDialog(context, isRtl, targetProduct);
              }
            },
            itemBuilder: (ctx) => [
              PopupMenuItem(
                value: 'report',
                child: Row(
                  children: [
                    const Icon(Icons.flag_outlined, color: Colors.orange, size: 20),
                    const SizedBox(width: 8),
                    Text(isRtl ? 'الإبلاغ عن هذا المنتج' : 'Report Product'),
                  ],
                ),
              ),
              PopupMenuItem(
                value: 'block',
                child: Row(
                  children: [
                    const Icon(Icons.block, color: Colors.red, size: 20),
                    const SizedBox(width: 8),
                    Text(isRtl ? 'حظر هذا البائع' : 'Block Seller'),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
      body: productsAsync.when(
        data: (products) {
          final productIndex = products.indexWhere(
            (p) => p.id == widget.productId,
          );
          
          if (productIndex < 0) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.error_outline, size: 64, color: Colors.grey),
                  const SizedBox(height: 16),
                  Text(isRtl ? 'المنتج غير موجود' : 'Product not found'),
                ],
              ),
            );
          }
          
          final product = products[productIndex];
          
          return SingleChildScrollView(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    constraints: const BoxConstraints(maxWidth: 420),
                    child: AspectRatio(
                      aspectRatio: 4 / 3,
                      child: Container(
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: Colors.grey.shade200, width: 1.5),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.05),
                              blurRadius: 16,
                              offset: const Offset(0, 6),
                            ),
                          ],
                        ),
                        clipBehavior: Clip.antiAlias,
                        child: product.imageUrl != null && product.imageUrl!.isNotEmpty
                            ? CachedNetworkImage(
                                imageUrl: product.imageUrl!,
                                fit: BoxFit.cover,
                                memCacheWidth: 800,
                                placeholder: (context, url) => Container(
                                  color: Colors.white,
                                  child: const Center(
                                    child: SizedBox(
                                      width: 32,
                                      height: 32,
                                      child: CircularProgressIndicator(strokeWidth: 2.5),
                                    ),
                                  ),
                                ),
                                errorWidget: (context, url, error) => const Icon(
                                  Icons.broken_image_outlined,
                                  size: 80,
                                  color: Colors.grey,
                                ),
                              )
                            : const Icon(Icons.image_outlined, size: 100, color: Colors.grey),
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 24),
                Text(
                  isRtl ? product.nameAr : product.name,
                  style: Theme.of(context).textTheme.headlineMedium?.copyWith(fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 8),
                if (product.isMagaieesExclusive)
                  Container(
                    margin: const EdgeInsets.symmetric(vertical: 6),
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFBFDBFE)),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.verified_rounded, color: Color(0xFF1D4ED8), size: 22),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                isRtl ? '⭐ صنف حصري ومباشر عبر شركة السيطرة' : '🌟 Exclusive Magaiees Product',
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: Color(0xFF1E40AF)),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                isRtl
                                    ? 'مملوك ومضمون رسمياً من شركة السيطرة مع ضمان جودة واستلام كامل.'
                                    : 'Directly verified and guaranteed by Magaiees official escrow.',
                                style: TextStyle(fontSize: 11, color: Colors.blue.shade900),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  )
                else
                  Container(
                    margin: const EdgeInsets.symmetric(vertical: 6),
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFAF5FF),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFE9D5FF)),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.storefront_rounded, color: Color(0xFF7C3AED), size: 22),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      '${isRtl ? "🏪 متجر التاجر المعتمد:" : "🏪 Verified Merchant Store:"} ${isRtl ? product.storeNameAr : product.storeName}',
                                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: Color(0xFF581C87)),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 3),
                              Text(
                                isRtl
                                    ? 'منتج معروض بواسطة تاجر معتمد وموثق في سوق السيطرة.'
                                    : 'Offered by a verified merchant on Magaiees marketplace.',
                                style: TextStyle(fontSize: 11, color: Colors.purple.shade900),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    Icon(Icons.category, color: Theme.of(context).colorScheme.primary),
                    const SizedBox(width: 8),
                    Text(
                      '${isRtl ? 'الفئة:' : 'Category:'} ${isRtl ? product.categoryNameAr : product.categoryName}',
                      style: Theme.of(context).textTheme.bodyLarge,
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Theme.of(context).colorScheme.primaryContainer,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text(
                          isRtl
                              ? '${NumberFormat('#,##0').format(product.pricePerTon)} ج.س / ${UnitFormatter.format(product.unitAr, product.unit, isRtl: true)}'
                              : '${NumberFormat('#,##0').format(product.pricePerTon)} SDG / ${UnitFormatter.format(product.unitAr, product.unit, isRtl: false)}',
                          style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                                color: Theme.of(context).colorScheme.onPrimaryContainer,
                                fontWeight: FontWeight.bold,
                              ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
                // Stock status indicator badge
                Builder(
                  builder: (context) {
                    final bool isOutOfStock = !product.inStock || product.stockQuantity <= 0;
                    final int availableStock = product.stockQuantity;
                    return Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      decoration: BoxDecoration(
                        color: isOutOfStock ? const Color(0xFFFEF2F2) : const Color(0xFFF0FDF4),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(
                          color: isOutOfStock ? const Color(0xFFFECACA) : const Color(0xFFBBF7D0),
                        ),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            isOutOfStock ? Icons.cancel_outlined : Icons.check_circle_outline_rounded,
                            size: 18,
                            color: isOutOfStock ? const Color(0xFFDC2626) : const Color(0xFF16A34A),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              isOutOfStock
                                  ? (isRtl ? 'نفد من المخزون حالياً' : 'Currently Out of Stock')
                                  : (isRtl
                                      ? 'متوفر في المخزون: $availableStock ${UnitFormatter.format(product.unitAr, product.unit, isRtl: true)}'
                                      : 'Available in Stock: $availableStock ${UnitFormatter.format(product.unitAr, product.unit, isRtl: false)}'),
                              style: TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 13,
                                color: isOutOfStock ? const Color(0xFFDC2626) : const Color(0xFF15803D),
                              ),
                            ),
                          ),
                        ],
                      ),
                    );
                  },
                ),
                const SizedBox(height: 16),
                Row(
                  children: [
                    Text(isRtl ? 'الكمية:' : 'Quantity:', style: Theme.of(context).textTheme.titleLarge),
                    const Spacer(),
                    Container(
                      decoration: BoxDecoration(
                        border: Border.all(color: Colors.grey.shade300),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Row(
                        children: [
                          IconButton(
                            onPressed: () {
                              if (quantity > 1) {
                                setState(() => quantity--);
                              }
                            },
                            icon: const Icon(Icons.remove),
                          ),
                          Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 16.0),
                            child: Text('$quantity', style: Theme.of(context).textTheme.titleLarge),
                          ),
                          IconButton(
                            onPressed: (!product.inStock || product.stockQuantity <= 0)
                                ? null
                                : () {
                                    if (quantity >= product.stockQuantity) {
                                      AppNotification.showInfo(
                                        context,
                                        isRtl
                                            ? 'الكمية المحددة هي أقصى كمية متوفرة في المخزون (${product.stockQuantity})'
                                            : 'Selected quantity is the maximum available stock (${product.stockQuantity})',
                                      );
                                      return;
                                    }
                                    setState(() => quantity++);
                                  },
                            icon: Icon(
                              Icons.add,
                              color: (!product.inStock || product.stockQuantity <= 0 || quantity >= product.stockQuantity)
                                  ? Colors.grey.shade400
                                  : null,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 32),
                Builder(
                  builder: (context) {
                    final bool isOutOfStock = !product.inStock || product.stockQuantity <= 0;
                    return SizedBox(
                      width: double.infinity,
                      child: FilledButton.icon(
                        style: isOutOfStock
                            ? FilledButton.styleFrom(
                                backgroundColor: Colors.grey.shade400,
                                foregroundColor: Colors.white,
                              )
                            : null,
                        onPressed: isOutOfStock
                            ? null
                            : () {
                                if (quantity > product.stockQuantity) {
                                  AppNotification.showError(
                                    context,
                                    isRtl
                                        ? 'الكمية المطلوبة تتجاوز المخزون المتوفر (${product.stockQuantity})'
                                        : 'Quantity exceeds available stock (${product.stockQuantity})',
                                  );
                                  return;
                                }
                                ref.read(cartNotifierProvider.notifier).addProduct(product, quantity.toDouble());
                                AppNotification.showSuccess(
                                  context,
                                  isRtl ? 'تمت الإضافة إلى السلة' : 'Added to cart',
                                  icon: Icons.shopping_bag_outlined,
                                );
                              },
                        icon: Icon(isOutOfStock ? Icons.remove_shopping_cart : Icons.shopping_cart),
                        label: Padding(
                          padding: const EdgeInsets.all(16.0),
                          child: Text(
                            isOutOfStock
                                ? (isRtl ? 'نفد من المخزون' : 'Out of Stock')
                                : (isRtl ? 'أضف إلى السلة' : 'Add to Cart'),
                            style: const TextStyle(fontSize: 18),
                          ),
                        ),
                      ),
                    );
                  },
                ),
                const SizedBox(height: 12),
                // Chat with Seller button
                SizedBox(
                  width: double.infinity,
                  height: 52,
                  child: OutlinedButton.icon(
                    onPressed: _isConnectingChat
                        ? null
                        : () async {
                            final user = ref.read(authNotifierProvider).valueOrNull;
                            if (user == null || user.role == UserRole.guest || user.id == 'guest_user') {
                              showDialog(
                                context: context,
                                builder: (ctx) => AlertDialog(
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                                  icon: const Icon(Icons.chat_bubble_outline_rounded, size: 48, color: Color(0xFF1E40AF)),
                                  title: Text(isRtl ? 'تسجيل الدخول مطلوب' : 'Login Required', style: const TextStyle(fontWeight: FontWeight.bold)),
                                  content: Text(
                                    isRtl
                                        ? 'يرجى تسجيل الدخول أو إنشاء حساب لبدء محادثة مباشرة مع التاجر.'
                                        : 'Please log in or create an account to chat directly with the seller.',
                                    textAlign: TextAlign.center,
                                  ),
                                  actions: [
                                    TextButton(
                                      onPressed: () => Navigator.pop(ctx),
                                      child: Text(isRtl ? 'إلغاء' : 'Cancel'),
                                    ),
                                    FilledButton(
                                      style: FilledButton.styleFrom(backgroundColor: const Color(0xFF1E40AF)),
                                      onPressed: () {
                                        Navigator.pop(ctx);
                                        ref.read(authNotifierProvider.notifier).logout();
                                        context.go('/login');
                                      },
                                      child: Text(isRtl ? 'تسجيل الدخول' : 'Log In'),
                                    ),
                                  ],
                                ),
                              );
                              return;
                            }

                            setState(() => _isConnectingChat = true);

                            try {
                              // Find the merchant (store owner) for this product
                              String? merchantId;
                              String? storeId;
                              final validUuidRegex = RegExp(r'^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$', caseSensitive: false);

                              if (validUuidRegex.hasMatch(product.merchantId)) {
                                // 1. Try finding store by store_id
                                var storeRes = await Supabase.instance.client
                                    .from('stores')
                                    .select('id, owner_id')
                                    .eq('id', product.merchantId)
                                    .maybeSingle()
                                    .timeout(const Duration(seconds: 5));

                                // 2. Try finding store by owner_id
                                storeRes ??= await Supabase.instance.client
                                    .from('stores')
                                    .select('id, owner_id')
                                    .eq('owner_id', product.merchantId)
                                    .maybeSingle()
                                    .timeout(const Duration(seconds: 5));

                                if (storeRes != null) {
                                  merchantId = storeRes['owner_id']?.toString();
                                  storeId = storeRes['id']?.toString();
                                } else {
                                  // Check if merchantId directly matches a registered user
                                  final userCheck = await Supabase.instance.client
                                      .from('users')
                                      .select('id')
                                      .eq('id', product.merchantId)
                                      .maybeSingle()
                                      .timeout(const Duration(seconds: 5));
                                  if (userCheck != null) {
                                    merchantId = userCheck['id']?.toString();
                                  }
                                }
                              }

                              if (merchantId == null && validUuidRegex.hasMatch(product.id)) {
                                // Try finding by product's store_id directly
                                final prodRes = await Supabase.instance.client
                                    .from('products')
                                    .select('store_id')
                                    .eq('id', product.id)
                                    .maybeSingle()
                                    .timeout(const Duration(seconds: 5));
                                if (prodRes != null && prodRes['store_id'] != null && validUuidRegex.hasMatch(prodRes['store_id'].toString())) {
                                  storeId = prodRes['store_id'].toString();
                                  final ownerRes = await Supabase.instance.client
                                      .from('stores')
                                      .select('owner_id')
                                      .eq('id', storeId)
                                      .maybeSingle()
                                      .timeout(const Duration(seconds: 5));
                                  merchantId = ownerRes?['owner_id']?.toString();
                                }
                              }

                              // Fallback to platform admin if exclusive or merchant not found
                              if (merchantId == null && (product.isMagaieesExclusive || product.merchantId == 'magaiees' || storeId == null)) {
                                try {
                                  final adminRes = await Supabase.instance.client
                                      .from('users')
                                      .select('id')
                                      .eq('role', 'admin')
                                      .limit(1)
                                      .maybeSingle()
                                      .timeout(const Duration(seconds: 5));
                                  merchantId = adminRes?['id']?.toString();
                                } catch (_) {}
                              }

                              if (merchantId == null) {
                                if (mounted) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    const SnackBar(content: Text('لا يمكن التواصل مع هذا التاجر حالياً')),
                                  );
                                }
                                return;
                              }

                              if (merchantId == user.id) {
                                if (context.mounted) {
                                  showDialog(
                                    context: context,
                                    builder: (ctx) => AlertDialog(
                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                                      icon: const Icon(Icons.info_outline_rounded, size: 48, color: Color(0xFFD97706)),
                                      title: Text(isRtl ? 'تنبيه' : 'Notice', style: const TextStyle(fontWeight: FontWeight.bold)),
                                      content: Text(
                                        isRtl
                                            ? 'أنت صاحب هذا المتجر والمنتج، لا يمكنك بدء محادثة مع نفسك.'
                                            : 'You own this store and product, you cannot start a chat with yourself.',
                                        textAlign: TextAlign.center,
                                      ),
                                      actions: [
                                        FilledButton(
                                          style: FilledButton.styleFrom(backgroundColor: const Color(0xFFD97706)),
                                          onPressed: () => Navigator.pop(ctx),
                                          child: Text(isRtl ? 'حسناً' : 'OK'),
                                        ),
                                      ],
                                    ),
                                  );
                                }
                                return;
                              }

                              // Create or get existing chat room with timeout
                              final roomId = await ChatService.getOrCreateRoom(
                                buyerId: user.id,
                                merchantId: merchantId,
                                storeId: storeId,
                              ).timeout(const Duration(seconds: 12));

                              if (context.mounted) {
                                await context.push('/buyer/chat/room/$roomId');
                              }
                            } catch (e) {
                              debugPrint('Error initiating chat with seller: $e');
                              if (context.mounted) {
                                AppNotification.showError(
                                  context,
                                  isRtl ? 'حدث خطأ أثناء فتح المحادثة: $e' : 'Error opening chat: $e',
                                );
                              }
                            } finally {
                              if (mounted) {
                                setState(() => _isConnectingChat = false);
                              }
                            }
                          },
                    icon: _isConnectingChat
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2.5,
                              valueColor: AlwaysStoppedAnimation<Color>(Color(0xFF1E40AF)),
                            ),
                          )
                        : const Icon(Icons.chat_outlined, size: 22),
                    label: Text(
                      _isConnectingChat
                          ? (isRtl ? 'جاري فتح المحادثة...' : 'Connecting...')
                          : (isRtl ? 'تواصل مع التاجر' : 'Chat with Seller'),
                      style: const TextStyle(fontSize: 16.5, fontWeight: FontWeight.bold),
                    ),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: const Color(0xFF1E40AF),
                      side: const BorderSide(color: Color(0xFF1E40AF), width: 1.8),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                ),
              ],
            ),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, stack) => Center(child: Text(isRtl ? 'خطأ في تحميل المنتج' : 'Error loading product')),
      ),
    );
  }

  void _showReportDialog(BuildContext context, bool isRtl, ProductCardModel? product) {
    final reasonController = TextEditingController();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Row(
          children: [
            const Icon(Icons.flag_outlined, color: Colors.orange),
            const SizedBox(width: 8),
            Expanded(
              child: Text(isRtl ? 'الإبلاغ عن محتوى مخالف' : 'Report Content'),
            ),
          ],
        ),
        content: TextField(
          controller: reasonController,
          maxLines: 3,
          decoration: InputDecoration(
            labelText: isRtl ? 'سبب البلاغ' : 'Reason for reporting',
            hintText: isRtl ? 'مثال: معلومات غير دقيقة أو محتوى غير لائق' : 'e.g. Inaccurate info or inappropriate content',
            border: const OutlineInputBorder(),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: Text(isRtl ? 'إلغاء' : 'Cancel'),
          ),
          FilledButton(
            onPressed: () async {
              final reason = reasonController.text.trim().isEmpty
                  ? (isRtl ? 'مراجعة المنتج' : 'Product review')
                  : reasonController.text.trim();
              Navigator.pop(ctx);
              await ref.read(blockedEntitiesProvider.notifier).submitReport(
                    targetType: 'product',
                    targetId: product?.id ?? widget.productId,
                    reason: reason,
                  );
              if (context.mounted) {
                AppNotification.showSuccess(
                  context,
                  isRtl ? 'تم استلام البلاغ وسيتم مراجعته خلال 24 ساعة' : 'Report submitted and will be reviewed within 24 hours',
                );
              }
            },
            child: Text(isRtl ? 'إرسال البلاغ' : 'Submit Report'),
          ),
        ],
      ),
    );
  }

  void _showBlockSellerDialog(BuildContext context, bool isRtl, ProductCardModel? product) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Row(
          children: [
            const Icon(Icons.block, color: Colors.red),
            const SizedBox(width: 8),
            Expanded(
              child: Text(isRtl ? 'حظر البائع' : 'Block Seller'),
            ),
          ],
        ),
        content: Text(
          isRtl
              ? 'هل أنت متأكد من حظر هذا البائع؟ لن تظهر لك منتجاته أو رسائله بعد الآن.'
              : 'Are you sure you want to block this seller? You will no longer see their products or messages.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: Text(isRtl ? 'إلغاء' : 'Cancel'),
          ),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: Colors.red),
            onPressed: () async {
              Navigator.pop(ctx);
              final targetId = (product != null && product.merchantId.isNotEmpty)
                  ? product.merchantId
                  : widget.productId;
              await ref.read(blockedEntitiesProvider.notifier).blockEntity(
                    targetId: targetId,
                    secondaryKey: product?.storeName,
                    reason: 'Buyer blocked seller from product detail',
                  );
              if (context.mounted) {
                AppNotification.showSuccess(
                  context,
                  isRtl ? 'تم حظر البائع وإخفاء عروضه فوراً' : 'Seller has been blocked and hidden',
                );
                if (context.canPop()) context.pop();
              }
            },
            child: Text(isRtl ? 'تأكيد الحظر' : 'Block'),
          ),
        ],
      ),
    );
  }
}
