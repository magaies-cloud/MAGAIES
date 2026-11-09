/// Utility for formatting and localizing unit names across Magaiees Marketplace.
class UnitFormatter {
  /// Strips any accidental currency prefix (e.g. "ج.س/", "SDG/", "$/", "USD/", "دولار/")
  static String clean(String raw) {
    var s = raw.trim();
    // Strip leading currency like "ج.س/", "SDG/", "$/", "USD/", "ج.س /", "USD /", etc.
    s = s.replaceAll(RegExp(r'^(ج\.س|SDG|USD|\$|دولار)\s*/\s*', caseSensitive: false), '');
    // Strip leading currency words followed by space
    s = s.replaceAll(RegExp(r'^(ج\.س|SDG|USD|\$|دولار)\s+', caseSensitive: false), '');
    // Strip trailing or leftover slash
    if (s.startsWith('/')) {
      s = s.substring(1).trim();
    }
    return s.trim();
  }

  /// Localizes a unit name given Arabic and English representations.
  /// Standardizes common terms such as piece/pieces, ton, sack, meter, truck, etc.
  static String format(String? unitAr, String? unit, {bool isRtl = true}) {
    if (isRtl) {
      final rawAr = unitAr != null ? clean(unitAr) : '';
      final rawEn = unit != null ? clean(unit) : '';
      final raw = rawAr.isNotEmpty ? rawAr : rawEn;
      final lower = raw.toLowerCase();

      if (lower == 'piece' ||
          lower == 'pieces' ||
          lower == 'pcs' ||
          lower == 'pc' ||
          lower == 'بيس' ||
          lower == 'بيسس' ||
          lower == 'قطعة' ||
          lower.contains('piece') ||
          lower.contains('pcs') ||
          lower.contains('قطعة')) {
        return 'قطعة';
      }
      if (lower == 'حبة' || lower.contains('حبة')) {
        return 'حبة';
      }
      if (lower == 'ton' || lower == 'tons' || lower == 'طن' || lower.contains('ton')) {
        return 'طن';
      }
      if (lower == 'sack' ||
          lower == 'sacks' ||
          lower == 'bag' ||
          lower == 'bags' ||
          lower == 'جوال' ||
          lower == 'شوال' ||
          lower.contains('sack') ||
          lower.contains('bag') ||
          lower.contains('جوال') ||
          lower.contains('شوال')) {
        return 'جوال';
      }
      if (lower == 'كيس' || lower.contains('كيس')) {
        return 'كيس';
      }
      if (lower == 'meter' ||
          lower == 'meters' ||
          lower == 'm' ||
          lower == 'متر' ||
          lower == 'متر طولي' ||
          lower.contains('meter')) {
        return 'متر';
      }
      if (lower == 'm2' || lower == 'م²' || lower == 'متر مربع') {
        return 'م²';
      }
      if (lower == 'm3' || lower == 'م³' || lower == 'متر مكعب') {
        return 'م³';
      }
      if (lower == 'truck' ||
          lower == 'trucks' ||
          lower == 'trip' ||
          lower == 'lorry' ||
          lower == 'نقلة' ||
          lower == 'شحنة' ||
          lower.contains('truck') ||
          lower.contains('trip')) {
        return 'نقلة';
      }
      if (lower == 'حمولة' || lower.contains('حمولة')) {
        return 'حمولة';
      }
      if (lower == 'درباس' || lower.contains('درباس')) {
        return 'درباس';
      }
      if (lower == 'kg' ||
          lower == 'kilo' ||
          lower == 'kilogram' ||
          lower == 'كيلو' ||
          lower.contains('kilo') ||
          lower.contains('kg')) {
        return 'كيلو';
      }
      if (lower == 'carton' ||
          lower == 'box' ||
          lower == 'cartons' ||
          lower == 'boxes' ||
          lower == 'كرتونة' ||
          lower == 'كرتون' ||
          lower.contains('carton') ||
          lower.contains('box')) {
        return 'كرتونة';
      }
      if (lower == 'barrel' || lower == 'برميل' || lower.contains('barrel') || lower.contains('برميل')) {
        return 'برميل';
      }
      if (lower == 'liter' || lower == 'litre' || lower == 'لتر' || lower.contains('liter')) {
        return 'لتر';
      }
      if (lower == 'لوح' || lower == 'sheet' || lower.contains('لوح')) {
        return 'لوح';
      }
      if (lower == 'عود' || lower == 'bar' || lower.contains('عود')) {
        return 'عود';
      }
      if (lower == 'unit' || lower == 'units' || lower == 'وحدة') {
        return 'وحدة';
      }
      if (raw.isNotEmpty) {
        return raw;
      }
      return 'قطعة';
    } else {
      final rawEn = unit != null ? clean(unit) : '';
      final rawAr = unitAr != null ? clean(unitAr) : '';
      final raw = rawEn.isNotEmpty ? rawEn : rawAr;
      final lower = raw.toLowerCase();

      if (lower == 'قطعة' || lower == 'بيس' || lower == 'بيسس' || lower == 'حبة' || lower == 'piece' || lower == 'pcs') return 'Piece';
      if (lower == 'طن' || lower == 'ton') return 'Ton';
      if (lower == 'جوال' || lower == 'شوال' || lower == 'sack') return 'Sack';
      if (lower == 'كيس' || lower == 'bag') return 'Bag';
      if (lower == 'متر' || lower == 'متر طولي' || lower == 'meter') return 'Meter';
      if (lower == 'م²' || lower == 'متر مربع' || lower == 'm2') return 'm²';
      if (lower == 'م³' || lower == 'متر مكعب' || lower == 'm3') return 'm³';
      if (lower == 'نقلة' || lower == 'شحنة' || lower == 'truck') return 'Truck';
      if (lower == 'حمولة') return 'Load';
      if (lower == 'درباس') return 'Derbas';
      if (lower == 'كيلو' || lower == 'kg') return 'Kg';
      if (lower == 'كرتونة' || lower == 'كرتون' || lower == 'box') return 'Carton';
      if (lower == 'برميل' || lower == 'barrel') return 'Barrel';
      if (lower == 'لتر' || lower == 'liter') return 'Liter';
      if (lower == 'لوح' || lower == 'sheet') return 'Sheet';
      if (lower == 'عود' || lower == 'bar') return 'Bar';
      if (lower == 'وحدة' || lower == 'unit') return 'Unit';
      return raw.isNotEmpty ? raw : 'Unit';
    }
  }
}
