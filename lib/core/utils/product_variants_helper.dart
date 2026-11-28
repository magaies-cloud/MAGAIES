import 'package:flutter/material.dart';

class ProductColorPreset {
  final String nameAr;
  final String nameEn;
  final Color color;

  const ProductColorPreset({
    required this.nameAr,
    required this.nameEn,
    required this.color,
  });
}

class ProductVariantsHelper {
  static const String _colorsPrefix = '__colors__:';
  static const String _sizesPrefix = '__sizes__:';
  static const String _descPrefix = '__desc__:';

  static const List<ProductColorPreset> presetColors = [
    ProductColorPreset(nameAr: 'أبيض', nameEn: 'White', color: Color(0xFFFFFFFF)),
    ProductColorPreset(nameAr: 'أسود', nameEn: 'Black', color: Color(0xFF1E293B)),
    ProductColorPreset(nameAr: 'رمادي', nameEn: 'Grey', color: Color(0xFF64748B)),
    ProductColorPreset(nameAr: 'بيج / سكري', nameEn: 'Beige / Cream', color: Color(0xFFF3E5AB)),
    ProductColorPreset(nameAr: 'بني', nameEn: 'Brown', color: Color(0xFF78350F)),
    ProductColorPreset(nameAr: 'أحمر / قرميدي', nameEn: 'Red / Brick', color: Color(0xFFDC2626)),
    ProductColorPreset(nameAr: 'أزرق', nameEn: 'Blue', color: Color(0xFF2563EB)),
    ProductColorPreset(nameAr: 'أخضر', nameEn: 'Green', color: Color(0xFF16A34A)),
    ProductColorPreset(nameAr: 'أصفر / ذهبي', nameEn: 'Yellow / Gold', color: Color(0xFFEAB308)),
    ProductColorPreset(nameAr: 'برتقالي', nameEn: 'Orange', color: Color(0xFFEA580C)),
    ProductColorPreset(nameAr: 'فضي / معدني', nameEn: 'Silver / Metallic', color: Color(0xFF94A3B8)),
    ProductColorPreset(nameAr: 'رخامي', nameEn: 'Marble', color: Color(0xFFCBD5E1)),
  ];

  static const List<String> presetSizes = [
    // أقطار وسماكات الحديد والأنابيب والكوابل
    '6 مم',
    '8 مم',
    '10 مم',
    '12 مم',
    '14 مم',
    '16 مم',
    '20 مم',
    '25 مم',
    '32 مم',
    // مقاسات بالبوصة (سباكة وتوصيلات)
    '1/2 بوصة',
    '3/4 بوصة',
    '1 بوصة',
    '2 بوصة',
    '3 بوصة',
    '4 بوصة',
    // أبعاد السيراميك والبلاط والألواح
    '30×30 سم',
    '40×40 سم',
    '60×60 سم',
    '60×120 سم',
    '120×240 سم',
    // أحجام وأوزان العبوات
    '1 لتر',
    '4 لتر (جالون)',
    '18 لتر (برميل)',
    '25 كجم',
    '50 كجم',
    // مقاسات عامة
    'صغير (S)',
    'متوسط (M)',
    'كبير (L)',
    'كبير جداً (XL)',
  ];

  static Color getColorForName(String name) {
    final clean = name.trim().toLowerCase();
    for (final preset in presetColors) {
      if (preset.nameAr.toLowerCase() == clean ||
          preset.nameEn.toLowerCase() == clean ||
          clean.contains(preset.nameAr.split('/').first.trim())) {
        return preset.color;
      }
    }
    return const Color(0xFF3B82F6);
  }

  static bool isMetadataEntry(String value) {
    final v = value.trim();
    return v.startsWith(_colorsPrefix) ||
        v.startsWith(_sizesPrefix) ||
        v.startsWith(_descPrefix);
  }

  static String? extractMainImageUrl(dynamic rawImages, [dynamic fallbackImageUrl]) {
    if (rawImages is List) {
      for (final item in rawImages) {
        final str = item?.toString().trim() ?? '';
        if (str.isNotEmpty && !isMetadataEntry(str)) {
          return str;
        }
      }
    }
    final fb = fallbackImageUrl?.toString().trim() ?? '';
    if (fb.isNotEmpty && !isMetadataEntry(fb)) {
      return fb;
    }
    return null;
  }

  static List<String> buildImagesArray({
    String? mainImageUrl,
    required List<String> colors,
    required List<String> sizes,
    String? description,
  }) {
    final result = <String>[];
    if (mainImageUrl != null &&
        mainImageUrl.trim().isNotEmpty &&
        !isMetadataEntry(mainImageUrl)) {
      result.add(mainImageUrl.trim());
    }
    final cleanColors = colors.map((e) => e.trim()).where((e) => e.isNotEmpty).toList();
    if (cleanColors.isNotEmpty) {
      result.add('$_colorsPrefix${cleanColors.join("|")}');
    }
    final cleanSizes = sizes.map((e) => e.trim()).where((e) => e.isNotEmpty).toList();
    if (cleanSizes.isNotEmpty) {
      result.add('$_sizesPrefix${cleanSizes.join("|")}');
    }
    final cleanDesc = description?.trim() ?? '';
    if (cleanDesc.isNotEmpty) {
      result.add('$_descPrefix$cleanDesc');
    }
    return result;
  }

  static String? extractDescription(Map<String, dynamic> json) {
    final direct = json['description']?.toString().trim() ?? '';
    if (direct.isNotEmpty) return direct;

    final rawImages = json['images'];
    if (rawImages is List) {
      for (final item in rawImages) {
        final str = item?.toString().trim() ?? '';
        if (str.startsWith(_descPrefix)) {
          final payload = str.substring(_descPrefix.length).trim();
          if (payload.isNotEmpty) return payload;
        }
      }
    }
    return null;
  }

  static List<String> extractColors(Map<String, dynamic> json) {
    // 1. Direct list field
    final direct = json['availableColors'] ?? json['available_colors'] ?? json['colors'];
    if (direct is List && direct.isNotEmpty) {
      return direct.map((e) => e.toString().trim()).where((e) => e.isNotEmpty).toList();
    }

    // 2. Encoded in images array
    final rawImages = json['images'];
    if (rawImages is List) {
      for (final item in rawImages) {
        final str = item?.toString().trim() ?? '';
        if (str.startsWith(_colorsPrefix)) {
          final payload = str.substring(_colorsPrefix.length);
          return payload
              .split('|')
              .map((e) => e.trim())
              .where((e) => e.isNotEmpty)
              .toList();
        }
      }
    }

    // 3. Fallback from description text if present
    final desc = json['description']?.toString() ?? '';
    for (final line in desc.split('\n')) {
      if (line.trim().startsWith('الألوان المتوفرة:')) {
        final raw = line.replaceFirst('الألوان المتوفرة:', '').trim();
        return raw.split('،').expand((e) => e.split(',')).map((e) => e.trim()).where((e) => e.isNotEmpty).toList();
      }
    }

    return const [];
  }

  static List<String> extractSizes(Map<String, dynamic> json) {
    // 1. Direct list field
    final direct = json['availableSizes'] ?? json['available_sizes'] ?? json['sizes'];
    if (direct is List && direct.isNotEmpty) {
      return direct.map((e) => e.toString().trim()).where((e) => e.isNotEmpty).toList();
    }

    // 2. Encoded in images array
    final rawImages = json['images'];
    if (rawImages is List) {
      for (final item in rawImages) {
        final str = item?.toString().trim() ?? '';
        if (str.startsWith(_sizesPrefix)) {
          final payload = str.substring(_sizesPrefix.length);
          return payload
              .split('|')
              .map((e) => e.trim())
              .where((e) => e.isNotEmpty)
              .toList();
        }
      }
    }

    // 3. Fallback from description text if present
    final desc = json['description']?.toString() ?? '';
    for (final line in desc.split('\n')) {
      if (line.trim().startsWith('المقاسات المتوفرة:')) {
        final raw = line.replaceFirst('المقاسات المتوفرة:', '').trim();
        return raw.split('،').expand((e) => e.split(',')).map((e) => e.trim()).where((e) => e.isNotEmpty).toList();
      }
    }

    return const [];
  }
}
