// Data Validator for Magaiees Marketplace
// Enforces strict data integrity, preventing fake, mock, spam, or dummy data entry.

class DataValidator {
  DataValidator._();

  // Known dummy / test keywords (both Arabic & English)
  static const Set<String> _forbiddenKeywords = {
    'test',
    'tst',
    'testing',
    'demo',
    'dummy',
    'fake',
    'sample',
    'mock',
    'asdf',
    'asdfg',
    'asdfgh',
    'qwerty',
    'zxcv',
    'zxcvb',
    'foo',
    'bar',
    'baz',
    'hzbsb',
    'bbb',
    'aaa',
    'hhh',
    'jjj',
    'تست',
    'تجربة',
    'تجربه',
    'وهمي',
    'وهميه',
    'مؤقت',
    'فيك',
    'شسيش',
    'بلبل',
    'سشيش',
    'اايي',
    'هههه',
  };

  // Check if a string is spam, keyboard smash, or repeated chars
  static bool isSpamOrFake(String? input) {
    if (input == null) return false;
    final trimmed = input.trim().toLowerCase();
    if (trimmed.isEmpty) return false;

    // Check exact forbidden keywords
    if (_forbiddenKeywords.contains(trimmed)) return true;

    // Check if trimmed contains forbidden test word isolated by spaces
    final words = trimmed.split(RegExp(r'\s+'));
    for (final w in words) {
      if (_forbiddenKeywords.contains(w) && words.length <= 2) {
        return true;
      }
    }

    // Repeated single character (e.g., 'aaaa', '1111', 'تتتت', '...', '---')
    if (trimmed.length >= 2 && RegExp(r'^(.)\1+$').hasMatch(trimmed)) {
      return true;
    }

    // Low character entropy: 4+ chars long but only 1 or 2 distinct characters (e.g. 'ababab', 'hahaha', '121212')
    if (trimmed.length >= 4) {
      final distinctChars = trimmed.replaceAll(RegExp(r'\s'), '').runes.toSet();
      if (distinctChars.length <= 1) return true;
      if (trimmed.length >= 6 && distinctChars.length <= 2) {
        // e.g. "ababab", "zxzzxx"
        return true;
      }
    }

    return false;
  }

  // General Name Validator (Personal name, merchant name, company name)
  static String? validateName(
    String? value, {
    String fieldName = 'الاسم',
    bool isRtl = true,
    int minLength = 3,
  }) {
    if (value == null || value.trim().isEmpty) {
      return isRtl ? 'يرجى إدخال $fieldName' : 'Please enter $fieldName';
    }
    final trimmed = value.trim();
    if (trimmed.length < minLength) {
      return isRtl
          ? 'يجب أن يتكون $fieldName من $minLength أحرف على الأقل'
          : '$fieldName must be at least $minLength characters';
    }
    if (isSpamOrFake(trimmed)) {
      return isRtl
          ? 'يرجى إدخال $fieldName حقيقي وصحيح وتجنب النصوص العشوائية'
          : 'Please enter a valid $fieldName without test or random characters';
    }
    // Must contain letters (not pure numbers or pure symbols)
    if (!RegExp(r'[\p{L}]', unicode: true).hasMatch(trimmed)) {
      return isRtl
          ? 'يجب أن يحتوي $fieldName على أحرف هجائية واضحة'
          : '$fieldName must contain valid letters';
    }
    return null;
  }

  // Product Name Validator
  static String? validateProductTitle(String? value, {bool isRtl = true}) {
    if (value == null || value.trim().isEmpty) {
      return isRtl ? 'اسم الصنف أو المنتج مطلوب' : 'Product name is required';
    }
    final trimmed = value.trim();
    if (trimmed.length < 3) {
      return isRtl
          ? 'يجب أن يكون اسم الصنف 3 أحرف على الأقل'
          : 'Product name must be at least 3 characters';
    }
    if (isSpamOrFake(trimmed)) {
      return isRtl
          ? 'اسم الصنف غير صالح. يرجى كتابة اسم حقيقي للمادة الإنشائية'
          : 'Invalid product name. Please specify real construction material';
    }
    if (!RegExp(r'[\p{L}]', unicode: true).hasMatch(trimmed)) {
      return isRtl
          ? 'يجب أن يحتوي اسم الصنف على أحرف وليس أرقاماً ورموزاً فقط'
          : 'Product name must contain letters';
    }
    return null;
  }

  // Email Validator
  static String? validateEmail(String? value, {bool isRtl = true}) {
    if (value == null || value.trim().isEmpty) {
      return isRtl ? 'البريد الإلكتروني مطلوب' : 'Email address is required';
    }
    final trimmed = value.trim().toLowerCase();
    
    // Strict email format
    final emailRegex = RegExp(r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$');
    if (!emailRegex.hasMatch(trimmed)) {
      return isRtl ? 'صيغة البريد الإلكتروني غير صحيحة' : 'Invalid email format';
    }

    // Reject single-letter usernames or test patterns (e.g. a@a.com, test@test.com)
    final parts = trimmed.split('@');
    final localPart = parts[0];
    final domainPart = parts[1];

    if (localPart.length < 2) {
      return isRtl
          ? 'اسم المستخدم في البريد الإلكتروني قصير جداً'
          : 'Email username is too short';
    }

    if (isSpamOrFake(localPart) || domainPart.startsWith('test') || domainPart.startsWith('fake') || domainPart.startsWith('temp')) {
      return isRtl
          ? 'يرجى استخدام بريد إلكتروني حقيقي ومفعل'
          : 'Please enter a valid, active email address';
    }

    return null;
  }

  // Phone Validator
  static String? validatePhone(String? value, {bool isRtl = true}) {
    if (value == null || value.trim().isEmpty) {
      return isRtl ? 'رقم الهاتف مطلوب' : 'Phone number is required';
    }
    // Extract only digits
    final digits = value.replaceAll(RegExp(r'\D'), '');

    // Allow Sudanese numbers (without +249: 9 or 10 digits; with 249: 12 digits) or international (9-15 digits)
    if (digits.length < 9 || digits.length > 15) {
      return isRtl
          ? 'رقم الهاتف غير مكتمل (9 أرقام على الأقل)'
          : 'Phone number must be at least 9 digits';
    }

    // Check repeated digits like 000000000, 111111111, 888888888
    if (RegExp(r'^(.)\1+$').hasMatch(digits)) {
      return isRtl ? 'رقم الهاتف غير صالح' : 'Invalid phone number';
    }

    return null;
  }

  // Price Validator
  static String? validatePrice(
    String? value, {
    bool isRtl = true,
    double min = 1.0,
    String fieldName = 'السعر',
  }) {
    if (value == null || value.trim().isEmpty) {
      return isRtl ? '$fieldName مطلوب' : '$fieldName is required';
    }
    final parsed = double.tryParse(value.trim());
    if (parsed == null || parsed.isNaN || parsed.isInfinite) {
      return isRtl ? 'يرجى إدخال قيمة رقمية صحيحة لـ $fieldName' : 'Please enter a valid number';
    }
    if (parsed < min) {
      return isRtl
          ? 'يجب أن يكون $fieldName أكبر من ${min.toStringAsFixed(0)}'
          : '$fieldName must be greater than ${min.toStringAsFixed(0)}';
    }
    return null;
  }

  // Stock Quantity Validator
  static String? validateStock(String? value, {bool isRtl = true}) {
    if (value == null || value.trim().isEmpty) {
      return isRtl ? 'كمية المخزون مطلوبة' : 'Stock quantity is required';
    }
    final parsed = int.tryParse(value.trim());
    if (parsed == null) {
      return isRtl ? 'يرجى إدخال رقم صحيح للكمية' : 'Please enter a valid integer';
    }
    if (parsed < 0) {
      return isRtl ? 'لا يمكن أن تكون الكمية سالبة' : 'Stock cannot be negative';
    }
    return null;
  }

  // Address Validator
  static String? validateAddress(String? value, {bool isRtl = true}) {
    if (value == null || value.trim().isEmpty) {
      return isRtl ? 'العنوان وتفاصيل الموقع مطلوبة' : 'Address details are required';
    }
    final trimmed = value.trim();
    if (trimmed.length < 5) {
      return isRtl
          ? 'يرجى إدخال عنوان واضح ومفصل (5 أحرف على الأقل)'
          : 'Please enter a detailed address (min 5 characters)';
    }
    if (isSpamOrFake(trimmed)) {
      return isRtl
          ? 'يرجى إدخال عنوان وموقع حقيقي وصحيح'
          : 'Please enter a valid real delivery location';
    }
    return null;
  }

  // Ad Banner Validator
  static String? validateAdTitle(String? value, {bool isRtl = true}) {
    if (value == null || value.trim().isEmpty) {
      return isRtl ? 'عنوان الإعلان مطلوب' : 'Ad title is required';
    }
    final trimmed = value.trim();
    if (trimmed.length < 4) {
      return isRtl
          ? 'يجب أن يكون عنوان الإعلان 4 أحرف على الأقل'
          : 'Ad title must be at least 4 characters';
    }
    if (isSpamOrFake(trimmed)) {
      return isRtl
          ? 'عنوان الإعلان غير صالح. يرجى إدخال عنوان ترويجي حقيقي'
          : 'Invalid ad title. Please enter a genuine headline';
    }
    return null;
  }
}
