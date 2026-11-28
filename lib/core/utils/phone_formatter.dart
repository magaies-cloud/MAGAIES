import 'package:flutter/material.dart';

/// Utility to normalize and display phone numbers consistently across RTL/LTR interfaces
/// so the country dialing code (e.g. +249) always appears first on the left,
/// followed by neatly grouped subscriber digits (e.g. +249 912 300 101).
class PhoneFormatter {
  const PhoneFormatter._();

  static const List<String> _threeDigitCodes = [
    '249', // Sudan
    '966', // Saudi Arabia
    '971', // UAE
    '968', // Oman
    '974', // Qatar
    '965', // Kuwait
    '973', // Bahrain
    '962', // Jordan
    '963', // Syria
    '964', // Iraq
    '961', // Lebanon
    '967', // Yemen
    '970', // Palestine
    '218', // Libya
    '212', // Morocco
    '213', // Algeria
    '216', // Tunisia
    '251', // Ethiopia
    '254', // Kenya
    '256', // Uganda
    '235', // Chad
    '291', // Eritrea
    '211', // South Sudan
  ];

  static const List<String> _twoDigitCodes = [
    '20', // Egypt
    '44', // UK
    '49', // Germany
    '33', // France
    '90', // Turkey
    '91', // India
    '86', // China
  ];

  /// Formats [raw] into `+CCC XXX XXX XXX` (country code first, then grouped local number).
  static String format(String? raw) {
    if (raw == null) return '';
    final trimmed = raw.trim();
    if (trimmed.isEmpty) return '';

    // Convert Arabic-Indic digits if present
    final normalized = _normalizeDigits(trimmed);

    // Strip everything except digits and '+'
    String cleaned = normalized.replaceAll(RegExp(r'[^\d+]'), '');
    if (cleaned.isEmpty) return trimmed;

    // Normalize leading 00 -> +
    if (cleaned.startsWith('00')) {
      cleaned = '+${cleaned.substring(2)}';
    } else if (!cleaned.startsWith('+')) {
      // Convert local Sudanese mobile 09xxxxxxxx or 01xxxxxxxx (10 digits) -> +249 9xx xxx xxx
      if (cleaned.length == 10 &&
          (cleaned.startsWith('09') || cleaned.startsWith('01'))) {
        cleaned = '+249${cleaned.substring(1)}';
      } else if (cleaned.length == 9 &&
          (cleaned.startsWith('9') || cleaned.startsWith('1'))) {
        cleaned = '+249$cleaned';
      } else if (cleaned.length >= 11 && cleaned.startsWith('249')) {
        cleaned = '+$cleaned';
      }
    }

    if (!cleaned.startsWith('+')) {
      // Local or custom short number: group cleanly by 3s/4s
      return _groupDigits(cleaned);
    }

    final digitsAfterPlus = cleaned.substring(1).replaceAll('+', '');
    if (digitsAfterPlus.isEmpty) return trimmed;

    String countryCode = '';
    String localNumber = digitsAfterPlus;

    if (digitsAfterPlus.startsWith('1') && digitsAfterPlus.length == 11) {
      countryCode = '1';
      localNumber = digitsAfterPlus.substring(1);
    } else {
      for (final c3 in _threeDigitCodes) {
        if (digitsAfterPlus.startsWith(c3) && digitsAfterPlus.length > c3.length + 4) {
          countryCode = c3;
          localNumber = digitsAfterPlus.substring(c3.length);
          break;
        }
      }
      if (countryCode.isEmpty) {
        for (final c2 in _twoDigitCodes) {
          if (digitsAfterPlus.startsWith(c2) && digitsAfterPlus.length > c2.length + 5) {
            countryCode = c2;
            localNumber = digitsAfterPlus.substring(c2.length);
            break;
          }
        }
      }
      if (countryCode.isEmpty && digitsAfterPlus.length > 9) {
        countryCode = digitsAfterPlus.substring(0, 3);
        localNumber = digitsAfterPlus.substring(3);
      }
    }

    // Strip trunk '0' after country code if someone typed +24909123...
    if (countryCode == '249' && localNumber.length == 10 && localNumber.startsWith('0')) {
      localNumber = localNumber.substring(1);
    }

    if (countryCode.isEmpty) {
      return '+${_groupDigits(localNumber)}';
    }
    return '+$countryCode ${_groupDigits(localNumber)}';
  }

  static String _groupDigits(String digits) {
    if (digits.length <= 4) return digits;
    if (digits.length == 9) {
      return '${digits.substring(0, 3)} ${digits.substring(3, 6)} ${digits.substring(6)}';
    }
    if (digits.length == 10) {
      return '${digits.substring(0, 3)} ${digits.substring(3, 6)} ${digits.substring(6)}';
    }
    if (digits.length == 8) {
      return '${digits.substring(0, 4)} ${digits.substring(4)}';
    }
    final buf = StringBuffer();
    for (int i = 0; i < digits.length; i++) {
      if (i > 0 && i % 3 == 0) buf.write(' ');
      buf.write(digits[i]);
    }
    return buf.toString();
  }

  static String _normalizeDigits(String input) {
    const arabicIndic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    const easternIndic = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    var out = input;
    for (int i = 0; i < 10; i++) {
      out = out.replaceAll(arabicIndic[i], '$i').replaceAll(easternIndic[i], '$i');
    }
    return out;
  }

  /// Renders a phone number strictly Left-to-Right (`+249 912 300 101`) even inside RTL rows/cards.
  static Widget text(
    String? rawPhone, {
    TextStyle? style,
    int maxLines = 1,
    TextOverflow overflow = TextOverflow.ellipsis,
  }) {
    final formatted = format(rawPhone);
    return Directionality(
      textDirection: TextDirection.ltr,
      child: Text(
        formatted,
        textDirection: TextDirection.ltr,
        maxLines: maxLines,
        overflow: overflow,
        style: style,
      ),
    );
  }
}
