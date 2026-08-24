import 'cart_item_model.dart';

class CartModel {
  final List<CartItemModel> items;

  const CartModel({
    this.items = const [],
  });

  CartModel copyWith({
    List<CartItemModel>? items,
  }) {
    return CartModel(
      items: items ?? this.items,
    );
  }

  Map<String, List<CartItemModel>> get itemsByMerchant {
    final map = <String, List<CartItemModel>>{};
    for (var item in items) {
      final merchantId = item.product.merchantId;
      map.putIfAbsent(merchantId, () => []).add(item);
    }
    return map;
  }
}
