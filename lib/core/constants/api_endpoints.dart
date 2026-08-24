/// API endpoint constants.
/// TODO: Replace with actual backend URLs.
abstract final class ApiEndpoints {
  static const String baseUrl = 'https://api.magaiees.com/v1';

  // Auth
  static const String login = '/auth/login';
  static const String register = '/auth/register';
  static const String refreshToken = '/auth/refresh';

  // Products
  static const String products = '/products';
  static const String categories = '/categories';
  static const String priceIndex = '/market/price-index';

  // Stores
  static const String stores = '/stores';
  static const String storeApplication = '/stores/apply';

  // Orders
  static const String orders = '/orders';
  static const String checkout = '/orders/checkout';

  // Digital Broker
  static const String quoteRequests = '/broker/quotes';
  static const String quoteBids = '/broker/bids';
}
