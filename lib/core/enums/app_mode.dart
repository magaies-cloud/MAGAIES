/// The current UI mode the user is viewing.
/// A merchant can browse in buyer mode.
enum AppMode {
  buyer,
  merchant;

  bool get isBuyer => this == AppMode.buyer;
  bool get isMerchant => this == AppMode.merchant;
}
