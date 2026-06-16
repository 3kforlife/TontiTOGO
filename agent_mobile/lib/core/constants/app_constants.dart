import 'package:flutter/material.dart';

class AppConstants {
  static const String apiBaseUrl = 'http://localhost:8000';
  static const String tokenKey = 'auth_token';
  static const String userKey = 'auth_user';

  static const int defaultPageSize = 20;
}

class AppColors {
  // Primary
  static const primary = Color(0xFF10B981);
  static const primaryDark = Color(0xFF059669);
  static const primaryLight = Color(0xFFD1FAE5);
  static const primary50 = Color(0xFFECFDF5);
  static const primary100 = Color(0xFFD1FAE5);
  static const primary200 = Color(0xFFA7F3D0);
  static const primary300 = Color(0xFF6EE7B7);
  static const primary400 = Color(0xFF34D399);
  static const primary500 = Color(0xFF10B981);
  static const primary600 = Color(0xFF059669);
  static const primary700 = Color(0xFF047857);
  static const primary800 = Color(0xFF065F46);
  static const primary900 = Color(0xFF064E3B);

  // Secondary
  static const secondary = Color(0xFF3B82F6);

  // Accent
  static const accent = Color(0xFFF59E0B);

  // Status
  static const success = Color(0xFF22C55E);
  static const danger = Color(0xFFEF4444);
  static const warning = Color(0xFFF59E0B);
  static const info = Color(0xFF3B82F6);

  // Neutral
  static const white = Color(0xFFFFFFFF);
  static const black = Color(0xFF000000);
  static const gray50 = Color(0xFFF9FAFB);
  static const gray100 = Color(0xFFF3F4F6);
  static const gray200 = Color(0xFFE5E7EB);
  static const gray300 = Color(0xFFD1D5DB);
  static const gray400 = Color(0xFF9CA3AF);
  static const gray500 = Color(0xFF6B7280);
  static const gray600 = Color(0xFF4B5563);
  static const gray700 = Color(0xFF374151);
  static const gray800 = Color(0xFF1F2937);
  static const gray900 = Color(0xFF111827);
}

class AppSpacing {
  static const double xs = 4.0;
  static const double sm = 8.0;
  static const double md = 16.0;
  static const double lg = 24.0;
  static const double xl = 32.0;
  static const double xxl = 48.0;
}

class AppBorderRadius {
  static const double sm = 8.0;
  static const double md = 12.0;
  static const double lg = 16.0;
  static const double xl = 20.0;
  static const double xxl = 24.0;
}

class AppShadows {
  static const List<BoxShadow> sm = [
    BoxShadow(
      color: Color(0x0F000000),
      blurRadius: 4,
      offset: Offset(0, 2),
    ),
  ];

  static const List<BoxShadow> md = [
    BoxShadow(
      color: Color(0x1A000000),
      blurRadius: 8,
      offset: Offset(0, 4),
    ),
  ];

  static const List<BoxShadow> lg = [
    BoxShadow(
      color: Color(0x14000000),
      blurRadius: 16,
      offset: Offset(0, 8),
    ),
  ];

  static const List<BoxShadow> primary = [
    BoxShadow(
      color: Color(0x3D10B981),
      blurRadius: 20,
      offset: Offset(0, 8),
    ),
  ];
}

class AppTextStyles {
  static const TextStyle h1 = TextStyle(
    fontSize: 32,
    fontWeight: FontWeight.w800,
    height: 1.2,
    color: AppColors.gray900,
  );

  static const TextStyle h2 = TextStyle(
    fontSize: 24,
    fontWeight: FontWeight.w800,
    height: 1.2,
    color: AppColors.gray900,
  );

  static const TextStyle h3 = TextStyle(
    fontSize: 20,
    fontWeight: FontWeight.w700,
    height: 1.3,
    color: AppColors.gray900,
  );

  static const TextStyle bodyLarge = TextStyle(
    fontSize: 16,
    fontWeight: FontWeight.w500,
    height: 1.5,
    color: AppColors.gray700,
  );

  static const TextStyle bodyMedium = TextStyle(
    fontSize: 14,
    fontWeight: FontWeight.w400,
    height: 1.5,
    color: AppColors.gray600,
  );

  static const TextStyle bodySmall = TextStyle(
    fontSize: 12,
    fontWeight: FontWeight.w400,
    height: 1.5,
    color: AppColors.gray500,
  );

  static const TextStyle button = TextStyle(
    fontSize: 16,
    fontWeight: FontWeight.w700,
    height: 1.3,
    letterSpacing: -0.3,
    color: AppColors.white,
  );

  static const TextStyle caption = TextStyle(
    fontSize: 12,
    fontWeight: FontWeight.w500,
    height: 1.3,
    color: AppColors.gray500,
    letterSpacing: 0.2,
  );
}

class AppDurations {
  static const Duration fast = Duration(milliseconds: 150);
  static const Duration medium = Duration(milliseconds: 300);
  static const Duration slow = Duration(milliseconds: 500);
  static const Duration xSlow = Duration(milliseconds: 800);
}

class AppCurves {
  static const Curve standard = Curves.easeInOutCubic;
  static const Curve decelerate = Curves.decelerate;
  static const Curve accelerate = Curves.easeIn;
  static const Curve bounce = Curves.elasticOut;
}
