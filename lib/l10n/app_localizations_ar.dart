// ignore: unused_import
import 'package:intl/intl.dart' as intl;
import 'app_localizations.dart';

// ignore_for_file: type=lint

/// The translations for Arabic (`ar`).
class AppLocalizationsAr extends AppLocalizations {
  AppLocalizationsAr([String locale = 'ar']) : super(locale);

  @override
  String get appTitle => 'منصة السيطرة';

  @override
  String get login => 'تسجيل الدخول';

  @override
  String get register => 'إنشاء حساب';

  @override
  String get logout => 'تسجيل الخروج';

  @override
  String get email => 'البريد الإلكتروني';

  @override
  String get password => 'كلمة المرور';

  @override
  String get phone => 'رقم الهاتف';

  @override
  String get fullName => 'الاسم الكامل';

  @override
  String get fullNameAr => 'الاسم الكامل (بالعربية)';

  @override
  String get buyerMode => 'وضع المشتري';

  @override
  String get merchantMode => 'وضع التاجر';

  @override
  String get switchToMerchant => 'التبديل لوضع التاجر';

  @override
  String get switchToBuyer => 'التبديل لوضع المشتري';

  @override
  String get noStoreAccess => 'تحتاج متجر معتمد للوصول لوضع التاجر.';

  @override
  String get createStore => 'إنشاء متجر';

  @override
  String get home => 'الرئيسية';

  @override
  String get cart => 'السلة';

  @override
  String get orders => 'الطلبات';

  @override
  String get profile => 'الملف الشخصي';

  @override
  String get settings => 'الإعدادات';

  @override
  String get dashboard => 'لوحة التحكم';

  @override
  String get inventory => 'المخزون';

  @override
  String get addProduct => 'إضافة منتج';

  @override
  String get storeSettings => 'إعدادات';

  @override
  String get categories => 'الأقسام';

  @override
  String get products => 'المنتجات';

  @override
  String soldBy(String storeName) {
    return 'يُباع بواسطة: $storeName';
  }

  @override
  String get pricePerTon => 'السعر للطن';

  @override
  String get weightTon => 'طن وزني';

  @override
  String get countTon => 'طن عددي';

  @override
  String get basePrice => 'السعر الأساسي';

  @override
  String get transportFee => 'رسوم النقل';

  @override
  String get offloadingFee => 'رسوم التنزيل';

  @override
  String get totalPrice => 'السعر الإجمالي';

  @override
  String get materialSubtotal => 'إجمالي المواد';

  @override
  String get shippingFee => 'رسوم الشحن';

  @override
  String get laborFee => 'رسوم العمالة / التنزيل';

  @override
  String get checkout => 'إتمام الشراء';

  @override
  String get siteLocation => 'موقع البناء';

  @override
  String get pinLocation => 'حدد موقع البناء على الخريطة';

  @override
  String get siteAccessibility => 'إمكانية الوصول للموقع';

  @override
  String get narrowRoad => 'طريق ضيق';

  @override
  String get bigTrucksAllowed => 'يُسمح بالشاحنات الكبيرة';

  @override
  String get mediumAccess => 'وصول متوسط (بيك أب)';

  @override
  String get digitalBroker => 'السمسار الرقمي';

  @override
  String get requestQuote => 'طلب عرض سعر جملة';

  @override
  String get quoteSent => 'تم إرسال طلب عرض السعر للتجار المعنيين.';

  @override
  String itemCount(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count عنصر',
      many: '$count عنصراً',
      few: '$count عناصر',
      two: 'عنصران',
      one: 'عنصر واحد',
      zero: 'لا توجد عناصر',
    );
    return '$_temp0';
  }

  @override
  String welcomeBack(String name) {
    return 'مرحباً بعودتك، $name!';
  }

  @override
  String get livePrices => 'أسعار السوق الحية';

  @override
  String get viewAll => 'عرض الكل';

  @override
  String get search => 'البحث عن منتجات...';

  @override
  String get noResults => 'لا توجد نتائج';

  @override
  String get retry => 'إعادة المحاولة';

  @override
  String get error => 'حدث خطأ ما';

  @override
  String get loading => 'جاري التحميل...';

  @override
  String get save => 'حفظ';

  @override
  String get cancel => 'إلغاء';

  @override
  String get confirm => 'تأكيد';

  @override
  String get delete => 'حذف';

  @override
  String get edit => 'تعديل';

  @override
  String get close => 'إغلاق';
}
