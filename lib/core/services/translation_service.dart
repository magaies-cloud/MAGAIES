import 'dart:async';
import 'package:dio/dio.dart';

class TranslationService {
  static final Dio _dio = Dio(BaseOptions(
    connectTimeout: const Duration(seconds: 4),
    receiveTimeout: const Duration(seconds: 4),
  ));

  // Specialized Arabic -> English Dictionary for Building Materials, Units & Sudanese Trade
  static final Map<String, String> _dictionary = {
    // Structural & Rebar
    'حديد تسليح': 'Reinforcing Steel Rebar',
    'حديد': 'Steel Rebar',
    'سيخ': 'Steel Rods',
    'سلك رباط': 'Binding Wire',
    'كمرات': 'Steel Beams',
    'زوايا حديد': 'Steel Angles',
    'مواسير حديد': 'Iron Pipes',
    'زنك': 'Corrugated Zinc Sheets',
    'زنك امريكي': 'American Zinc Sheets',
    'زنك بلدي': 'Standard Zinc Sheets',
    'صاج': 'Sheet Metal',
    'صاج اسود': 'Black Iron Sheet',
    'صاج مجلفن': 'Galvanized Sheet',
    
    // Cement & Concrete
    'اسمنت بورتلاندي': 'Portland Cement',
    'اسمنت': 'Cement',
    'أسمنت': 'Cement',
    'اسمنت قنا': 'Qena Cement',
    'اسمنت عطبرة': 'Atbara Cement',
    'اسمنت بربر': 'Berber Cement',
    'اسمنت الشمال': 'Al Shamal Cement',
    'خرسانة': 'Concrete',
    'رمل خرسانة': 'Concrete Sand',
    'رمل': 'Sand',
    'رملة': 'Sand',
    'كنكري': 'Gravel Aggregate',
    'حصى': 'Pebbles / Gravel',
    'طوب احمر': 'Red Clay Bricks',
    'طوب': 'Bricks',
    'طوب بلك': 'Concrete Blocks',
    'بلك بركاني': 'Hollow Volcanic Blocks',
    'انترلوك': 'Interlock Paving Blocks',
    'بردورة': 'Curbstone',

    // Finishing & Paints
    'دهان': 'Paint',
    'دهانات': 'Paints',
    'بويات': 'Paints',
    'معجون': 'Wall Putty',
    'جبس': 'Gypsum Plaster',
    'سيراميك': 'Ceramic Tiles',
    'بورسلين': 'Porcelain Tiles',
    'رخام': 'Marble',
    'جرانيت': 'Granite',
    'غراء سيراميك': 'Tile Adhesive',

    // Electrical & Plumbing
    'ادوات كهربائية': 'Electrical Supplies',
    'أدوات كهربائية': 'Electrical Supplies',
    'كابلات': 'Electric Cables',
    'سلك': 'Electric Wire',
    'قواطع': 'Circuit Breakers',
    'لمبات': 'LED Bulbs',
    'انارة': 'Lighting',
    'مواسير بلاستيك': 'PVC Pipes',
    'مواسير حرارية': 'PPR Pipes',
    'ادوات صحية': 'Sanitary Ware',
    'أدوات صحية': 'Sanitary Ware',
    'خلاطات': 'Water Faucets',
    'مغاسل': 'Wash Basins',
    'مرحاض': 'Water Closet Toilet',
    'خزانات مياه': 'Water Tanks',

    // Units and Sizes
    'طن': 'Ton',
    'ملم': 'mm',
    'سم': 'cm',
    'متر': 'Meter',
    'كجم': 'kg',
    'كيلو': 'kg',
    'كيس': 'Bag',
    'شوال': 'Sack / Bag',
    'جوال': 'Bag',
    'لوح': 'Sheet',
    'لتر': 'Liter',
    'كرتونة': 'Carton',
    'حبة': 'Piece',
    'قطعة': 'Piece',
    'مشرشر': 'Deformed',
    'املس': 'Smooth',
    'أملس': 'Smooth',
    'مقاوم للملوحة': 'SRC Salt Resistant',
  };

  /// Translates Arabic text to English automatically
  static Future<String> translateArabicToEnglish(String arabicText) async {
    final cleanText = arabicText.trim();
    if (cleanText.isEmpty) return '';

    // 1. Direct dictionary match
    final lower = cleanText.toLowerCase();
    if (_dictionary.containsKey(lower)) {
      return _dictionary[lower]!;
    }

    // 2. Try Free Google Translate GTX API (Instant & accurate)
    try {
      final url = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=ar&tl=en&dt=t&q=' + Uri.encodeComponent(cleanText);
      final response = await _dio.get(url);
      if (response.statusCode == 200 && response.data is List && (response.data as List).isNotEmpty) {
        final rawTranslations = response.data[0];
        if (rawTranslations is List) {
          String translated = '';
          for (var part in rawTranslations) {
            if (part is List && part.isNotEmpty) {
              translated += part[0].toString();
            }
          }
          if (translated.trim().isNotEmpty) {
            return _capitalizeEachWord(translated.trim());
          }
        }
      }
    } catch (_) {}

    // 3. Fallback: MyMemory Free Translation API
    try {
      final url = 'https://api.mymemory.translated.net/get?q=' + Uri.encodeComponent(cleanText) + '&langpair=ar|en';
      final response = await _dio.get(url);
      if (response.statusCode == 200 && response.data != null) {
        final matches = response.data['responseData'];
        if (matches != null && matches['translatedText'] != null) {
          final translated = matches['translatedText'].toString().trim();
          if (translated.isNotEmpty && !translated.startsWith('MYMEMORY WARNING')) {
            return _capitalizeEachWord(translated);
          }
        }
      }
    } catch (_) {}

    // 4. Fallback: Dictionary word-by-word substitution
    final words = cleanText.split(' ');
    final translatedWords = words.map((w) {
      final sub = _dictionary[w.toLowerCase()];
      return sub ?? w;
    }).toList();

    return _capitalizeEachWord(translatedWords.join(' '));
  }

  static String _capitalizeEachWord(String text) {
    if (text.isEmpty) return text;
    return text.split(' ').map((word) {
      if (word.isEmpty) return word;
      return word[0].toUpperCase() + word.substring(1);
    }).join(' ');
  }
}
