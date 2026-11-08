import 'cart_item_model.dart';

class CartModel {
  final List<CartItemModel> items;
  final String? appliedCouponCode;
  final double discountAmount;

  const CartModel({
    this.items = const [],
    this.appliedCouponCode,
    this.discountAmount = 0.0,
  });

  CartModel copyWith({
    List<CartItemModel>? items,
    String? appliedCouponCode,
    double? discountAmount,
  }) {
    return CartModel(
      items: items ?? this.items,
      appliedCouponCode: appliedCouponCode ?? this.appliedCouponCode,
      discountAmount: discountAmount ?? this.discountAmount,
    );
  }

  Map<String, List<CartItemModel>> get itemsByMerchant {
    final map = <String, List<CartItemModel>>{};
    for (var item in items) {
      final merchantId = item.product.isMagaieesExclusive ? 'magaiees' : item.product.merchantId;
      map.putIfAbsent(merchantId, () => []).add(item);
    }
    return map;
  }
}
