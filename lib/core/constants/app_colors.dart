import 'package:flutter/material.dart';

/// Magaiees Marketplace brand colors.
abstract final class AppColors {
  // Primary palette - modern, trust-inspiring
  static const Color primary = Color(0xFF1E40AF); // Deep Royal Blue (Tailwind Blue 800)
  static const Color primaryLight = Color(0xFF3B82F6); // Vibrant Blue (Tailwind Blue 500)
  static const Color primaryDark = Color(0xFF1E3A8A); // Navy (Tailwind Blue 900)

  // Secondary palette - action elements
  static const Color secondary = Color(0xFFF97316); // Vibrant Orange (Tailwind Orange 500)
  static const Color secondaryLight = Color(0xFFFDBA74);

  // Surface colors - ultra clean look
  static const Color surface = Color(0xFFF8FAFC); // Very light grey/blue for scaffold
  static const Color surfaceDark = Color(0xFF0F172A); // Slate 900 for dark mode
  static const Color card = Colors.white;

  // Status colors
  static const Color success = Color(0xFF16A34A); // Clean green
  static const Color warning = Color(0xFFEAB308); // Clean yellow
  static const Color error = Color(0xFFDC2626); // Clean red
  static const Color info = Color(0xFF0284C7);

  // Text colors
  static const Color textPrimary = Color(0xFF0F172A); // Slate 900
  static const Color textSecondary = Color(0xFF64748B); // Slate 500
  static const Color textOnPrimary = Colors.white;

  // Merchant mode accent
  static const Color merchantAccent = Color(0xFF6D28D9); // Vibrant Purple (Tailwind Purple 700) for contrast
}
