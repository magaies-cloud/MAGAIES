/// Named route path constants used throughout the app.
///
/// Organized by mode (auth, buyer, merchant).
abstract final class RouteNames {
  // === Auth Routes ===
  static const String login = '/login';
  static const String register = '/register';

  // === Buyer Routes ===
  static const String buyerHome = '/buyer/home';
  static const String buyerCart = '/buyer/cart';
  static const String buyerOrders = '/buyer/orders';
  static const String buyerProfile = '/buyer/profile';
  static const String buyerProductDetail = '/buyer/home/product/:id';
  static const String buyerCheckout = '/buyer/cart/checkout';
  static const String buyerDigitalBroker = '/buyer/digital-broker';
  static const String buyerChat = '/buyer/chat';

  // === Merchant Routes ===
  static const String merchantDashboard = '/merchant/dashboard';
  static const String merchantInventory = '/merchant/inventory';
  static const String merchantAddProduct = '/merchant/add-product';
  static const String merchantOrders = '/merchant/orders';
  static const String merchantStoreSettings = '/merchant/store-settings';
  static const String merchantTeam = '/merchant/team';
  static const String merchantFinancialReport = '/merchant/financial-report';
  static const String merchantChat = '/merchant/chat';

  // === Admin Routes ===
  static const String adminSubscribers = '/admin/subscribers';
  static const String adminSubscriberProfile = '/admin/subscribers/profile'; // Extra object is passed
  static const String adminDashboard = '/admin/dashboard';
  static const String adminAds = '/admin/ads';
  static const String adminOrders = '/admin/orders';
  static const String adminCategories = '/admin/categories';
  static const String adminProducts = '/admin/products';
  static const String adminServices = '/admin/services';
  static const String adminFinance = '/admin/finance';
  static const String adminSettings = '/admin/settings';
  static const String adminDigitalBroker = '/admin/digital-broker';

  // === Employee Routes ===
  static const String employeeDashboard = '/employee/dashboard';

  /// Helper to build product detail path.
  static String productDetail(String id) => '/buyer/home/product/$id';
}
