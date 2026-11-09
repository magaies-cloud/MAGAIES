import 'package:flutter_test/flutter_test.dart';
import 'package:magaiees_marketplace/core/utils/name_formatter.dart';

void main() {
  group('NameFormatter Tests', () {
    test('English name remains English in Arabic and English UI', () {
      expect(NameFormatter.formatName(name: 'd', nameAr: null, isRtl: true), 'd');
      expect(NameFormatter.formatName(name: 'd', nameAr: null, isRtl: false), 'd');
      expect(NameFormatter.formatName(name: 'w', nameAr: '', isRtl: true), 'w');
      expect(NameFormatter.formatName(name: 'Mega Store', nameAr: null, isRtl: true), 'Mega Store');
      expect(NameFormatter.formatName(name: 'Ahmed', nameAr: 'Ahmed', isRtl: true), 'Ahmed');
    });

    test('Single-letter English name with transliterated Arabic field displays English', () {
      expect(NameFormatter.formatName(name: 'd', nameAr: 'د', isRtl: true), 'd');
      expect(NameFormatter.formatName(name: 'w', nameAr: 'و', isRtl: true), 'w');
    });

    test('Arabic name remains Arabic in Arabic and English UI', () {
      expect(NameFormatter.formatName(name: null, nameAr: 'متجر الأمل', isRtl: true), 'متجر الأمل');
      expect(NameFormatter.formatName(name: null, nameAr: 'متجر الأمل', isRtl: false), 'متجر الأمل');
      expect(NameFormatter.formatName(name: 'متجر الأمل', nameAr: 'متجر الأمل', isRtl: false), 'متجر الأمل');
      expect(NameFormatter.formatName(name: '', nameAr: 'أسامة', isRtl: false), 'أسامة');
    });

    test('Artificial prefixes are cleanly removed', () {
      expect(NameFormatter.cleanPrefix('التاجر د'), 'د');
      expect(NameFormatter.cleanPrefix('المشتري و'), 'و');
      expect(NameFormatter.cleanPrefix('التاجر: أحمد'), 'أحمد');
      expect(NameFormatter.cleanPrefix('العميل خالد'), 'خالد');
      expect(NameFormatter.formatName(name: 'd', nameAr: 'التاجر د', isRtl: true), 'd');
      expect(NameFormatter.formatName(name: 'w', nameAr: 'المشتري و', isRtl: true), 'w');
      expect(NameFormatter.formatName(name: 'd', nameAr: 'متجر د', isRtl: true), 'd');
    });

    test('Genuine bilingual names respect isRtl', () {
      expect(
        NameFormatter.formatName(name: 'Apple Store', nameAr: 'متجر آبل', isRtl: true),
        'متجر آبل',
      );
      expect(
        NameFormatter.formatName(name: 'Apple Store', nameAr: 'متجر آبل', isRtl: false),
        'Apple Store',
      );
    });

    test('Fallback is used when both names are empty', () {
      expect(NameFormatter.formatName(name: null, nameAr: null, fallback: 'مستخدم'), 'مستخدم');
      expect(NameFormatter.formatName(name: '', nameAr: '   ', fallback: 'User'), 'User');
    });
  });
}
