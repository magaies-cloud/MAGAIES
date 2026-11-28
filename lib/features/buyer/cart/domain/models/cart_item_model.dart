import '../../../home/domain/models/product_card_model.dart';

class CartItemModel {
  final ProductCardModel product;
  final double quantity;
  final String? selectedColor;
  final String? selectedSize;

  const CartItemModel({
    required this.product,
    required this.quantity,
    this.selectedColor,
    this.selectedSize,
  });

  CartItemModel copyWith({
    ProductCardModel? product,
    double? quantity,
    String? selectedColor,
    String? selectedSize,
  }) {
    return CartItemModel(
      product: product ?? this.product,
      quantity: quantity ?? this.quantity,
      selectedColor: selectedColor ?? this.selectedColor,
      selectedSize: selectedSize ?? this.selectedSize,
    );
  }
}
