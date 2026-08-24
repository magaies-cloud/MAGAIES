// ignore: unused_import
import 'package:intl/intl.dart' as intl;
import 'app_localizations.dart';

// ignore_for_file: type=lint

/// The translations for English (`en`).
class AppLocalizationsEn extends AppLocalizations {
  AppLocalizationsEn([String locale = 'en']) : super(locale);

  @override
  String get appTitle => 'Magaiees Marketplace';

  @override
  String get login => 'Login';

  @override
  String get register => 'Register';

  @override
  String get logout => 'Logout';

  @override
  String get email => 'Email';

  @override
  String get password => 'Password';

  @override
  String get phone => 'Phone Number';

  @override
  String get fullName => 'Full Name';

  @override
  String get fullNameAr => 'Full Name (Arabic)';

  @override
  String get buyerMode => 'Buyer Mode';

  @override
  String get merchantMode => 'Merchant Mode';

  @override
  String get switchToMerchant => 'Switch to Merchant';

  @override
  String get switchToBuyer => 'Switch to Buyer';

  @override
  String get noStoreAccess =>
      'You need an approved store to access Merchant mode.';

  @override
  String get createStore => 'Create a Store';

  @override
  String get home => 'Home';

  @override
  String get cart => 'Cart';

  @override
  String get orders => 'Orders';

  @override
  String get profile => 'Profile';

  @override
  String get settings => 'Settings';

  @override
  String get dashboard => 'Dashboard';

  @override
  String get inventory => 'Inventory';

  @override
  String get addProduct => 'Add Product';

  @override
  String get storeSettings => 'Store Settings';

  @override
  String get categories => 'Categories';

  @override
  String get products => 'Products';

  @override
  String soldBy(String storeName) {
    return 'Sold by: $storeName';
  }

  @override
  String get pricePerTon => 'Price per Ton';

  @override
  String get weightTon => 'Weight Ton';

  @override
  String get countTon => 'Count Ton';

  @override
  String get basePrice => 'Base Price';

  @override
  String get transportFee => 'Transport Fee';

  @override
  String get offloadingFee => 'Offloading Fee';

  @override
  String get totalPrice => 'Total Price';

  @override
  String get materialSubtotal => 'Material Subtotal';

  @override
  String get shippingFee => 'Shipping Fee';

  @override
  String get laborFee => 'Labor / Offloading Fee';

  @override
  String get checkout => 'Checkout';

  @override
  String get siteLocation => 'Construction Site Location';

  @override
  String get pinLocation => 'Pin your construction site on the map';

  @override
  String get siteAccessibility => 'Site Accessibility';

  @override
  String get narrowRoad => 'Narrow road';

  @override
  String get bigTrucksAllowed => 'Big trucks allowed';

  @override
  String get mediumAccess => 'Medium access (pickup trucks)';

  @override
  String get digitalBroker => 'Digital Broker';

  @override
  String get requestQuote => 'Request Bulk Quote';

  @override
  String get quoteSent =>
      'Your quote request has been sent to relevant merchants.';

  @override
  String itemCount(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count items',
      one: '1 item',
      zero: 'No items',
    );
    return '$_temp0';
  }

  @override
  String welcomeBack(String name) {
    return 'Welcome back, $name!';
  }

  @override
  String get livePrices => 'Live Market Prices';

  @override
  String get viewAll => 'View All';

  @override
  String get search => 'Search products...';

  @override
  String get noResults => 'No results found';

  @override
  String get retry => 'Retry';

  @override
  String get error => 'Something went wrong';

  @override
  String get loading => 'Loading...';

  @override
  String get save => 'Save';

  @override
  String get cancel => 'Cancel';

  @override
  String get confirm => 'Confirm';

  @override
  String get delete => 'Delete';

  @override
  String get edit => 'Edit';

  @override
  String get close => 'Close';
}
