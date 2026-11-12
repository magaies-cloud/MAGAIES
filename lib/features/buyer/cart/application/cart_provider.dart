import 'package:magaiees_marketplace/core/providers/global_settings_provider.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';
import '../domain/models/cart_item_model.dart';
import '../domain/models/cart_model.dart';
import 'package:magaiees_marketplace/features/buyer/home/domain/models/product_card_model.dart';

part 'cart_provider.g.dart';

@Riverpod(keepAlive: true)
class CartNotifier extends _$CartNotifier {
  @override
  CartModel build() {
    return const CartModel();
  }

  void addProduct(ProductCardModel product, double quantity) {
    final existingIndex = state.items.indexWhere((i) => i.product.id == product.id);
    if (existingIndex >= 0) {
      final existingItem = state.items[existingIndex];
      final newItems = List<CartItemModel>.from(state.items);
      newItems[existingIndex] = existingItem.copyWith(quantity: existingItem.quantity + quantity);
      state = state.copyWith(items: newItems);
    } else {
      state = state.copyWith(
        items: [...state.items, CartItemModel(product: product, quantity: quantity)],
      );
    }
  }

  void updateQuantity(String productId, double quantity) {
    if (quantity <= 0) {
      removeItem(productId);
      return;
    }
    final existingIndex = state.items.indexWhere((i) => i.product.id == productId);
    if (existingIndex >= 0) {
      final newItems = List<CartItemModel>.from(state.items);
      newItems[existingIndex] = newItems[existingIndex].copyWith(quantity: quantity);
      state = state.copyWith(items: newItems);
    }
  }

  void removeItem(String productId) {
    state = state.copyWith(
      items: state.items.where((i) => i.product.id != productId).toList(),
    );
  }

  void clearCart() {
    state = const CartModel();
  }

  void applyCoupon(String code, double amount) {
    state = state.copyWith(appliedCouponCode: code, discountAmount: amount);
  }

  void removeCoupon() {
    state = state.copyWith(clearCoupon: true);
  }
}

@Riverpod(keepAlive: true)
double cartSubtotal(CartSubtotalRef ref) {
  final cart = ref.watch(cartNotifierProvider);
  return cart.items.fold(0, (sum, item) => sum + (item.product.pricePerTon * item.quantity));
}

@Riverpod(keepAlive: true)
double cartTransportTotal(CartTransportTotalRef ref) {
  // Transport fees are now determined by the merchant after order placement
  return 0.0;
}

@Riverpod(keepAlive: true)
double cartOffloadingTotal(CartOffloadingTotalRef ref) {
  // Offloading fees are now determined by the merchant after order placement
  return 0.0;
}

@Riverpod(keepAlive: true)
double cartGrandTotal(CartGrandTotalRef ref) {
  final cart = ref.watch(cartNotifierProvider);
  final subtotal = ref.watch(cartSubtotalProvider);
  // Transport and offloading are 0 at checkout
  final settings = ref.watch(globalSettingsProvider);
  double totalWithVat = subtotal;
  if (settings.isTaxAndCommissionEnabled) {
      totalWithVat += totalWithVat * (settings.vatPercent / 100.0);
  }
  return totalWithVat - cart.discountAmount;
}

@Riverpod(keepAlive: true)
double cartVatTotal(CartVatTotalRef ref) {
  final subtotal = ref.watch(cartSubtotalProvider);
  final settings = ref.watch(globalSettingsProvider);
  if (!settings.isTaxAndCommissionEnabled) return 0.0;
  return subtotal * (settings.vatPercent / 100.0); 
}

@Riverpod(keepAlive: true)
int cartItemsCount(CartItemsCountRef ref) {
  final cart = ref.watch(cartNotifierProvider);
  return cart.items.length;
}
