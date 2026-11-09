import 'package:magaiees_marketplace/features/auth/domain/models/user_model.dart';

/// Utility class for formatting user, merchant, and store names.
///
/// Rule requested by user:
/// - If the name was written in English, it appears in English.
/// - If the name was written in Arabic, it appears in Arabic.
/// - Never force an English name to appear in Arabic or vice versa.
/// - Never prepend artificial role prefixes like "التاجر" or "المشتري".
/// - If genuine dual-language names exist (both distinct Arabic and English),
///   pick according to the active locale [isRtl].
class NameFormatter {
  static final RegExp _arabicRegex = RegExp(r'[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF]');
  static final RegExp _latinRegex = RegExp(r'[a-zA-Z]');
  static final RegExp _rolePrefixRegex = RegExp(
    r'^(التاجر|المشتري|الزبون|العميل)\s*[:\-–]?\s*',
    caseSensitive: false,
  );

  /// Strips artificial role prefixes like "التاجر ", "المشتري ", "متجر "
  static String cleanPrefix(String? text) {
    if (text == null) return '';
    var trimmed = text.trim();
    while (_rolePrefixRegex.hasMatch(trimmed)) {
      trimmed = trimmed.replaceFirst(_rolePrefixRegex, '').trim();
    }
    return trimmed;
  }

  /// Checks if a string contains any Arabic letters
  static bool hasArabic(String text) => _arabicRegex.hasMatch(text);

  /// Checks if a string contains any Latin / English letters
  static bool hasLatin(String text) => _latinRegex.hasMatch(text);

  /// Formats name preserving the language it was originally written in:
  /// - If written in English -> returns English name
  /// - If written in Arabic -> returns Arabic name
  /// - If both distinct languages were provided -> returns based on [isRtl]
  static String formatName({
    String? name,
    String? nameAr,
    bool isRtl = true,
    String fallback = '',
  }) {
    final cleanEn = cleanPrefix(name);
    final cleanAr = cleanPrefix(nameAr);

    if (cleanEn.isEmpty && cleanAr.isEmpty) {
      return fallback;
    }

    if (cleanEn.isNotEmpty && cleanAr.isEmpty) {
      return cleanEn;
    }

    if (cleanAr.isNotEmpty && cleanEn.isEmpty) {
      return cleanAr;
    }

    // Both are present
    if (cleanEn.toLowerCase() == cleanAr.toLowerCase()) {
      return cleanEn;
    }

    final enHasLatin = hasLatin(cleanEn);
    final enHasArabic = hasArabic(cleanEn);
    final arHasLatin = hasLatin(cleanAr);
    final arHasArabic = hasArabic(cleanAr);

    // If English field is purely Latin
    if (enHasLatin && !enHasArabic) {
      // If English field is a short initial/name (e.g. 'd', 'w'), it was written in English:
      if (cleanEn.length <= 2) {
        return cleanEn;
      }

      // If cleanAr has genuine Arabic words (and not Latin)
      if (arHasArabic && !arHasLatin) {
        return isRtl ? cleanAr : cleanEn;
      }

      // Default to the original English text
      return cleanEn;
    }

    // If Arabic field is purely Arabic and English field is also Arabic
    if (arHasArabic && !arHasLatin && enHasArabic && !enHasLatin) {
      return cleanAr;
    }

    // If English field has Arabic and Arabic field has no Arabic
    if (enHasArabic && !enHasLatin && !arHasArabic) {
      return cleanEn;
    }

    // Genuine bilingual entries (both distinct scripts present)
    if (arHasArabic && enHasLatin) {
      return isRtl ? cleanAr : cleanEn;
    }

    // General fallback respecting UI direction
    if (isRtl) {
      return cleanAr.isNotEmpty ? cleanAr : cleanEn;
    } else {
      return cleanEn.isNotEmpty ? cleanEn : cleanAr;
    }
  }

  /// Formats a [UserModel]'s display name
  static String formatUser(
    UserModel? user, {
    bool isRtl = true,
    String fallback = '',
  }) {
    if (user == null) return fallback;
    return formatName(
      name: user.name,
      nameAr: user.nameAr,
      isRtl: isRtl,
      fallback: fallback,
    );
  }

  /// Formats a store's display name
  static String formatStore({
    String? storeName,
    String? storeNameAr,
    bool isRtl = true,
    String fallback = '',
  }) {
    return formatName(
      name: storeName,
      nameAr: storeNameAr,
      isRtl: isRtl,
      fallback: fallback,
    );
  }
}
