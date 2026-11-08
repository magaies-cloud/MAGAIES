import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import '../../application/cart_provider.dart';
import '../../domain/models/cart_item_model.dart';
import 'package:magaiees_marketplace/core/providers/global_settings_provider.dart';
import 'package:magaiees_marketplace/core/utils/app_notification.dart';
import 'package:magaiees_marketplace/core/utils/unit_formatter.dart';
import 'package:intl/intl.dart' hide TextDirection;

class CartScreen extends ConsumerWidget {
  const CartScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final cart = ref.watch(cartNotifierProvider);
    final subtotal = ref.watch(cartSubtotalProvider);
    final vat = ref.watch(cartVatTotalProvider);
    final grandTotal = ref.watch(cartGrandTotalProvider);
    final currency = isRtl ? 'ج.س' : 'SDG';
    final settings = ref.watch(globalSettingsProvider);

    if (cart.items.isEmpty) {
      return Scaffold(
        appBar: AppBar(title: Text(isRtl ? 'سلة المشتريات' : 'Cart')),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.shopping_cart_outlined, size: 80, color: Colors.grey.shade300),
              const SizedBox(height: 24),
              Text(
                isRtl ? 'سلة المشتريات فارغة' : 'Your cart is empty',
                style: TextStyle(fontSize: 20, color: Colors.grey.shade600, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 32),
              FilledButton.icon(
                onPressed: () => context.go('/buyer/home'),
                icon: const Icon(Icons.shopping_bag_outlined),
                label: Text(isRtl ? 'ابدأ التسوق' : 'Start Shopping'),
                style: FilledButton.styleFrom(
                  padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                ),
              ),
            ],
          ),
        ),
      );
    }

    final itemsByMerchant = cart.itemsByMerchant;
    final bool hasMultipleMerchants = itemsByMerchant.length > 1;

    return Scaffold(
      appBar: AppBar(title: Text(isRtl ? 'سلة المشتريات' : 'Cart')),
      body: Column(
        children: [
          // Multi-merchant warning banner
          if (hasMultipleMerchants)
            Container(
              margin: const EdgeInsets.fromLTRB(16, 12, 16, 0),
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFFFF7ED),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFFBBF24), width: 1.2),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(Icons.info_outline_rounded, color: Color(0xFFD97706), size: 22),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          isRtl
                              ? 'سلتك تحتوي منتجات من ${itemsByMerchant.length} متاجر مختلفة'
                              : 'Your cart has items from ${itemsByMerchant.length} different stores',
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF92400E)),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          isRtl
                              ? 'سيتم إنشاء طلب منفصل لكل متجر عند إتمام الشراء. يمكنك التنسيق لتوحيد الشحن إن أمكن.'
                              : 'A separate order will be created for each store at checkout. You can coordinate combined shipping if possible.',
                          style: const TextStyle(fontSize: 11.5, color: Color(0xFFB45309)),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          Expanded(
            child: ListView.builder(
        padding: const EdgeInsets.only(bottom: 140, left: 16, right: 16, top: 16),
        itemCount: itemsByMerchant.length,
        itemBuilder: (context, index) {
          final merchantId = itemsByMerchant.keys.elementAt(index);
          final items = itemsByMerchant[merchantId]!;
          final isExclusive = items.first.product.isMagaieesExclusive || merchantId == 'magaiees';
          final storeName = isExclusive
              ? (isRtl ? 'حصري على مقاييس' : 'Exclusive to Magaiees')
              : (isRtl
                  ? (items.first.product.storeNameAr.isNotEmpty ? items.first.product.storeNameAr : items.first.product.storeName)
                  : (items.first.product.storeName.isNotEmpty ? items.first.product.storeName : items.first.product.storeNameAr));

          return Card(
            margin: const EdgeInsets.only(bottom: 16),
            child: Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Icon(
                        isExclusive ? Icons.verified_rounded : Icons.storefront_outlined,
                        size: 20,
                        color: isExclusive ? const Color(0xFF2563EB) : Colors.blue,
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          storeName,
                          style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                fontWeight: FontWeight.bold,
                                color: isExclusive ? const Color(0xFF1D4ED8) : null,
                              ),
                        ),
                      ),
                      if (isExclusive)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: const Color(0xFFDBEAFE),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            isRtl ? '⭐ صنف حصري' : '⭐ Exclusive',
                            style: const TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF1D4ED8),
                            ),
                          ),
                        ),
                    ],
                  ),
                  const Divider(),
                  ...items.map((item) => _CartItemTile(item: item)),
                ],
              ),
            ),
          );
        },
      ),
          ),
        ],
      ),
      bottomSheet: SafeArea(
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Theme.of(context).colorScheme.surface,
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.05),
                blurRadius: 10,
                offset: const Offset(0, -5),
              ),
            ],
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(isRtl ? 'المجموع الفرعي:' : 'Subtotal:'),
                Text('${subtotal.toStringAsFixed(2)} $currency'),
              ],
            ),
            if (settings.isTaxAndCommissionEnabled) ...[
              const SizedBox(height: 4),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(isRtl ? 'ضريبة القيمة المضافة (${settings.vatPercent}%):' : 'VAT (${settings.vatPercent}%):'),
                  Text('${vat.toStringAsFixed(2)} $currency'),
                ],
              ),
            ],
            const Divider(),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(isRtl ? 'المجموع الكلي:' : 'Grand Total:', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                Text('${grandTotal.toStringAsFixed(2)} $currency', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.blue)),
              ],
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {
                  final user = ref.read(authNotifierProvider).valueOrNull;
                  if (user?.role.isGuest ?? true) {
                    // Guest cannot checkout - prompt login
                    showDialog(
                      context: context,
                      builder: (ctx) => AlertDialog(
                        icon: const Icon(Icons.login, size: 48, color: Colors.blue),
                        title: Text(isRtl ? 'تسجيل الدخول مطلوب' : 'Login Required'),
                        content: Text(
                          isRtl
                              ? 'يرجى تسجيل الدخول أو إنشاء حساب لإتمام عملية الشراء.'
                              : 'Please log in or create an account to complete your purchase.',
                        ),
                        actions: [
                          TextButton(
                            onPressed: () => Navigator.pop(ctx),
                            child: Text(isRtl ? 'إلغاء' : 'Cancel'),
                          ),
                          FilledButton(
                            onPressed: () {
                              Navigator.pop(ctx);
                              ref.read(authNotifierProvider.notifier).logout();
                            },
                            child: Text(isRtl ? 'تسجيل الدخول' : 'Log In'),
                          ),
                        ],
                      ),
                    );
                    return;
                  }
                  final outOfStockItem = cart.items.cast<CartItemModel?>().firstWhere(
                    (i) => i != null && (!i.product.inStock || i.product.stockQuantity <= 0),
                    orElse: () => null,
                  );
                  if (outOfStockItem != null) {
                    final pName = isRtl
                        ? (outOfStockItem.product.nameAr.isNotEmpty ? outOfStockItem.product.nameAr : outOfStockItem.product.name)
                        : (outOfStockItem.product.name.isNotEmpty ? outOfStockItem.product.name : outOfStockItem.product.nameAr);
                    AppNotification.showError(
                      context,
                      isRtl
                          ? 'الصنف ($pName) نفد من المخزون حالياً. يرجى حذفه من السلة للمتابعة.'
                          : 'Item ($pName) is out of stock. Please remove it from your cart to proceed.',
                    );
                    return;
                  }
                  final overStockItem = cart.items.cast<CartItemModel?>().firstWhere(
                    (i) => i != null && i.quantity > i.product.stockQuantity,
                    orElse: () => null,
                  );
                  if (overStockItem != null) {
                    final pName = isRtl
                        ? (overStockItem.product.nameAr.isNotEmpty ? overStockItem.product.nameAr : overStockItem.product.name)
                        : (overStockItem.product.name.isNotEmpty ? overStockItem.product.name : overStockItem.product.nameAr);
                    AppNotification.showError(
                      context,
                      isRtl
                          ? 'الكمية المطلوبة من ($pName) تتجاوز المخزون المتوفر (${overStockItem.product.stockQuantity}). يرجى تقليل الكمية للمتابعة.'
                          : 'Quantity for ($pName) exceeds available stock (${overStockItem.product.stockQuantity}). Please decrease it to proceed.',
                    );
                    return;
                  }
                  context.push('/buyer/cart/checkout');
                },
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: Text(isRtl ? 'متابعة إلى الدفع' : 'Proceed to Checkout', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
              ),
            ),
          ],
        ),
      )),
    );
  }
}

class _CartItemTile extends ConsumerWidget {
  final CartItemModel item;

  const _CartItemTile({required this.item});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final displayName = isRtl
        ? (item.product.nameAr.isNotEmpty ? item.product.nameAr : item.product.name)
        : (item.product.name.isNotEmpty ? item.product.name : item.product.nameAr);
    final currency = isRtl ? 'ج.س' : 'SDG';

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 60,
            height: 60,
            decoration: BoxDecoration(
              color: Colors.white,
              border: Border.all(color: Colors.grey.shade200),
              borderRadius: BorderRadius.circular(8),
            ),
            child: item.product.imageUrl != null
                ? ClipRRect(
                    borderRadius: BorderRadius.circular(7),
                    child: Image.network(
                      item.product.imageUrl!,
                      fit: BoxFit.contain,
                      errorBuilder: (context, error, stackTrace) => const Icon(Icons.inventory_2_outlined, color: Colors.grey),
                    ),
                  )
                : const Icon(Icons.inventory_2_outlined, color: Colors.grey),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  displayName,
                  style: const TextStyle(fontWeight: FontWeight.w600),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 4),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                  decoration: BoxDecoration(
                    color: item.product.isMagaieesExclusive
                        ? const Color(0xFFEFF6FF)
                        : const Color(0xFFF1F5F9),
                    borderRadius: BorderRadius.circular(4),
                    border: Border.all(
                      color: item.product.isMagaieesExclusive
                          ? const Color(0xFFBFDBFE)
                          : const Color(0xFFE2E8F0),
                      width: 0.5,
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        item.product.isMagaieesExclusive
                            ? Icons.verified_rounded
                            : Icons.storefront_outlined,
                        size: 12,
                        color: item.product.isMagaieesExclusive
                            ? const Color(0xFF2563EB)
                            : const Color(0xFF64748B),
                      ),
                      const SizedBox(width: 4),
                      Flexible(
                        child: Text(
                          item.product.isMagaieesExclusive
                              ? (isRtl ? 'حصري مقاييس' : 'Magaiees Exclusive')
                              : (isRtl
                                  ? (item.product.storeNameAr.isNotEmpty
                                      ? item.product.storeNameAr
                                      : item.product.storeName)
                                  : (item.product.storeName.isNotEmpty
                                      ? item.product.storeName
                                      : item.product.storeNameAr)),
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: item.product.isMagaieesExclusive
                                ? const Color(0xFF1D4ED8)
                                : const Color(0xFF475569),
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  '${NumberFormat('#,##0.00').format(item.product.pricePerTon)} $currency / ${UnitFormatter.format(item.product.unitAr, item.product.unit, isRtl: isRtl)}',
                  style: TextStyle(color: Colors.grey.shade700, fontSize: 12.5),
                ),
                if (!item.product.inStock || item.product.stockQuantity <= 0) ...[
                  const SizedBox(height: 4),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEE2E2),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text(
                      isRtl ? 'نفد من المخزون' : 'Out of stock',
                      style: const TextStyle(fontSize: 10.5, color: Color(0xFFDC2626), fontWeight: FontWeight.bold),
                    ),
                  ),
                ] else if (item.quantity > item.product.stockQuantity) ...[
                  const SizedBox(height: 4),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEF3C7),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text(
                      isRtl
                          ? 'الكمية تتجاوز المتوفر بالمخزون (${item.product.stockQuantity})'
                          : 'Exceeds stock (${item.product.stockQuantity})',
                      style: const TextStyle(fontSize: 10.5, color: Color(0xFFB45309), fontWeight: FontWeight.bold),
                    ),
                  ),
                ],
              ],
            ),
          ),
          Column(
            children: [
              Row(
                children: [
                  IconButton(
                    icon: const Icon(Icons.remove_circle_outline),
                    onPressed: () {
                      ref.read(cartNotifierProvider.notifier).updateQuantity(item.product.id, item.quantity - 1);
                    },
                    padding: EdgeInsets.zero,
                    constraints: const BoxConstraints(),
                  ),
                  const SizedBox(width: 8),
                  Text(item.quantity.toStringAsFixed(0), style: const TextStyle(fontWeight: FontWeight.bold)),
                  const SizedBox(width: 8),
                  IconButton(
                    icon: Icon(
                      Icons.add_circle_outline,
                      color: (!item.product.inStock || item.product.stockQuantity <= 0 || item.quantity >= item.product.stockQuantity)
                          ? Colors.grey.shade400
                          : null,
                    ),
                    onPressed: () {
                      if (!item.product.inStock || item.product.stockQuantity <= 0) {
                        AppNotification.showInfo(
                          context,
                          isRtl ? 'هذا الصنف غير متوفر حالياً' : 'This item is currently out of stock',
                        );
                        return;
                      }
                      if (item.quantity >= item.product.stockQuantity) {
                        AppNotification.showInfo(
                          context,
                          isRtl
                              ? 'الكمية وصلت للحد الأقصى المتوفر بالمخزون (${item.product.stockQuantity})'
                              : 'Maximum available stock reached (${item.product.stockQuantity})',
                        );
                        return;
                      }
                      ref.read(cartNotifierProvider.notifier).updateQuantity(item.product.id, item.quantity + 1);
                    },
                    padding: EdgeInsets.zero,
                    constraints: const BoxConstraints(),
                  ),
                ],
              ),
              TextButton(
                onPressed: () {
                  ref.read(cartNotifierProvider.notifier).removeItem(item.product.id);
                },
                style: TextButton.styleFrom(
                  padding: EdgeInsets.zero,
                  minimumSize: const Size(50, 30),
                  tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  alignment: isRtl ? Alignment.centerLeft : Alignment.centerRight,
                ),
                child: Text(isRtl ? 'حذف' : 'Remove', style: const TextStyle(color: Colors.red, fontSize: 12)),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
