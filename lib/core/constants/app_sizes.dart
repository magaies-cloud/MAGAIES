import 'package:flutter/material.dart';

/// Consistent spacing and sizing values.
abstract final class AppSizes {
  // Padding / Margin
  static const double p4 = 4.0;
  static const double p8 = 8.0;
  static const double p12 = 12.0;
  static const double p16 = 16.0;
  static const double p20 = 20.0;
  static const double p24 = 24.0;
  static const double p32 = 32.0;
  static const double p48 = 48.0;
  static const double p64 = 64.0;

  // Border Radius
  static const double radiusSm = 8.0;
  static const double radiusMd = 12.0;
  static const double radiusLg = 16.0;
  static const double radiusXl = 24.0;

  // Icon sizes
  static const double iconSm = 16.0;
  static const double iconMd = 24.0;
  static const double iconLg = 32.0;

  // SizedBox gap helpers
  static const SizedBox gapH4 = SizedBox(height: 4);
  static const SizedBox gapH8 = SizedBox(height: 8);
  static const SizedBox gapH12 = SizedBox(height: 12);
  static const SizedBox gapH16 = SizedBox(height: 16);
  static const SizedBox gapH24 = SizedBox(height: 24);
  static const SizedBox gapH32 = SizedBox(height: 32);
  static const SizedBox gapW4 = SizedBox(width: 4);
  static const SizedBox gapW8 = SizedBox(width: 8);
  static const SizedBox gapW12 = SizedBox(width: 12);
  static const SizedBox gapW16 = SizedBox(width: 16);
}
