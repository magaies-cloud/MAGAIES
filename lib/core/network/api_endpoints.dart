class ApiEndpoints {
  // للبيئة المحلية (XAMPP)
  static const String baseUrl = 'http://localhost/magaiees_api/api/v1';
  // للإنتاج (Live Server)
  // static const String baseUrl = 'https://APP.elsajana.com/api/v1';

  // Auth
  static const String login = '/auth/login';
  static const String register = '/auth/register';

  // Products
  static const String products = '/products.php';
  static const String addProduct = '/add_product.php';
  static const String categories = '/categories';

  // Cart & Orders
  static const String cart = '/cart';
  static const String checkout = '/checkout';
}
