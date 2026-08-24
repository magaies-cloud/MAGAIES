import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import '../../application/cart_provider.dart';
import '../../domain/models/cart_item_model.dart';

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

    return Scaffold(
      appBar: AppBar(title: Text(isRtl ? 'سلة المشتريات' : 'Cart')),
      body: ListView.builder(
        padding: const EdgeInsets.only(bottom: 140, left: 16, right: 16, top: 16),
        itemCount: itemsByMerchant.length,
        itemBuilder: (context, index) {
          final merchantId = itemsByMerchant.keys.elementAt(index);
          final items = itemsByMerchant[merchantId]!;
          final storeName = isRtl
              ? (items.first.product.storeNameAr.isNotEmpty ? items.first.product.storeNameAr : items.first.product.storeName)
              : (items.first.product.storeName.isNotEmpty ? items.first.product.storeName : items.first.product.storeNameAr);

          return Card(
            margin: const EdgeInsets.only(bottom: 16),
            child: Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.store, size: 18, color: Colors.blue),
                      const SizedBox(width: 8),
                      Text(
                        storeName,
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
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
            const SizedBox(height: 4),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(isRtl ? 'ضريبة القيمة المضافة (15%):' : 'VAT (15%):'),
                Text('${vat.toStringAsFixed(2)} $currency'),
              ],
            ),
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
              color: Colors.grey.shade200,
              borderRadius: BorderRadius.circular(8),
            ),
            child: item.product.imageUrl != null
                ? ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: Image.network(
                      item.product.imageUrl!,
                      fit: BoxFit.cover,
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
                Text(
                  '${item.product.pricePerTon.toStringAsFixed(2)} $currency / ${isRtl ? 'طن' : 'unit'}',
                  style: TextStyle(color: Colors.grey.shade700, fontSize: 13),
                ),
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
                    icon: const Icon(Icons.add_circle_outline),
                    onPressed: () {
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
