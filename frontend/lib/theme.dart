import 'package:flutter/material.dart';

class AppTheme {
  static const Color primary = Color(0xFF1A1A2E);
  static const Color surface = Color(0xFF16213E);
  static const Color accent = Color(0xFFE94560);
  static const Color gold = Color(0xFFF5A623);
  static const Color background = Color(0xFF0F0F1A);
  static const Color card = Color(0xFF1E1E32);
  static const Color textPrimary = Color(0xFFF5F5F5);
  static const Color textSecondary = Color(0xFFB0B0C0);

  static ThemeData dark() {
    const colorScheme = ColorScheme.dark(
      primary: accent,
      secondary: gold,
      surface: surface,
      onPrimary: Colors.white,
      onSecondary: Colors.black,
      onSurface: textPrimary,
    );

    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.dark,
      colorScheme: colorScheme,
      scaffoldBackgroundColor: background,
      appBarTheme: const AppBarTheme(
        backgroundColor: primary,
        elevation: 0,
        centerTitle: true,
        foregroundColor: textPrimary,
        titleTextStyle: TextStyle(
          fontSize: 20,
          fontWeight: FontWeight.w700,
          color: textPrimary,
          letterSpacing: 0.5,
        ),
      ),
      drawerTheme: const DrawerThemeData(backgroundColor: primary),
      floatingActionButtonTheme: const FloatingActionButtonThemeData(
        backgroundColor: accent,
        foregroundColor: Colors.white,
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: accent,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: card,
        labelStyle: const TextStyle(color: textSecondary),
        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: Color(0xFF2A2A45)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: accent, width: 1.5),
        ),
      ),
      cardColor: card,
      dividerColor: const Color(0xFF2A2A45),
      textTheme: const TextTheme(
        headlineSmall: TextStyle(fontSize: 20, fontWeight: FontWeight.w700, color: textPrimary),
        titleLarge: TextStyle(fontSize: 18, fontWeight: FontWeight.w600, color: textPrimary),
        titleMedium: TextStyle(fontSize: 16, fontWeight: FontWeight.w600, color: textPrimary),
        bodyMedium: TextStyle(fontSize: 14, color: textSecondary),
        bodySmall: TextStyle(fontSize: 13, color: textSecondary),
      ),
      visualDensity: VisualDensity.adaptivePlatformDensity,
    );
  }
}
