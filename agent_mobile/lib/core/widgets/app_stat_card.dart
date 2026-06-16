import 'package:flutter/material.dart';
import '../constants/app_constants.dart';
import 'app_card.dart';

enum StatCardVariant {
  primary,
  success,
  warning,
  danger,
  info,
}

class AppStatCard extends StatelessWidget {
  final String title;
  final String value;
  final IconData icon;
  final StatCardVariant variant;
  final String? subtitle;
  final VoidCallback? onTap;
  final IconData? trailingIcon;

  const AppStatCard({
    super.key,
    required this.title,
    required this.value,
    required this.icon,
    this.variant = StatCardVariant.primary,
    this.subtitle,
    this.onTap,
    this.trailingIcon,
  });

  @override
  Widget build(BuildContext context) {
    final colors = _getVariantColors();

    return AppCard(
      onTap: onTap,
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                width: 64,
                height: 64,
                decoration: BoxDecoration(
                  color: colors.background,
                  borderRadius: BorderRadius.circular(AppBorderRadius.lg),
                  border: Border.all(
                    color: colors.icon.withValues(alpha: 0.2),
                    width: 2,
                  ),
                ),
                child: Icon(
                  icon,
                  color: colors.icon,
                  size: 32,
                ),
              ),
              if (trailingIcon != null)
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: colors.background,
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    trailingIcon,
                    color: colors.icon,
                    size: 24,
                  ),
                ),
            ],
          ),
          const SizedBox(height: AppSpacing.md),
          Text(
            title,
            style: AppTextStyles.bodyLarge.copyWith(
              color: AppColors.gray500,
            ),
          ),
          const SizedBox(height: AppSpacing.xs),
          Text(
            value,
            style: AppTextStyles.h2.copyWith(
              color: AppColors.gray900,
            ),
          ),
        ],
      ),
    );
  }

  ({Color icon, Color background}) _getVariantColors() {
    switch (variant) {
      case StatCardVariant.primary:
        return (
          icon: AppColors.primary,
          background: AppColors.primary100,
        );
      case StatCardVariant.success:
        return (
          icon: AppColors.success,
          background: AppColors.success.withValues(alpha: 0.1),
        );
      case StatCardVariant.warning:
        return (
          icon: AppColors.warning,
          background: AppColors.warning.withValues(alpha: 0.1),
        );
      case StatCardVariant.danger:
        return (
          icon: AppColors.danger,
          background: AppColors.danger.withValues(alpha: 0.1),
        );
      case StatCardVariant.info:
        return (
          icon: AppColors.info,
          background: AppColors.info.withValues(alpha: 0.1),
        );
    }
  }
}
