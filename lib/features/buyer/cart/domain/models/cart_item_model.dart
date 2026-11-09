import '../../../home/domain/models/product_card_model.dart';

class CartItemModel {
  final ProductCardModel product;
  final double quantity;

  const CartItemModel({
    required this.product,
    required this.quantity,
  });

  CartItemModel copyWith({
    ProductCardModel? product,
    double? quantity,
  }) {
    return CartItemModel(
      product: product ?? this.product,
      quantity: quantity ?? this.quantity,
    );
  }
}
