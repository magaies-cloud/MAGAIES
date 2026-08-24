/// Represents the user's account role.
/// A buyer becomes a merchant after store approval.
enum UserRole {
  guest,
  buyer,
  merchant,
  employee,
  admin;

  /// Whether this role has merchant privileges.
  bool get isMerchant => this == UserRole.merchant;
  
  /// Whether this role is a guest.
  bool get isGuest => this == UserRole.guest;

  /// Whether this role is an admin.
  bool get isAdmin => this == UserRole.admin;
}
