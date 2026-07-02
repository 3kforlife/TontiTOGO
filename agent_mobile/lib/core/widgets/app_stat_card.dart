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
      padding: const EdgeInsets.all(AppSpacing.sm),   // lg → sm
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              // Icône principale réduite
              Container(
                width: 40,          // 64 → 40
                height: 40,         // 64 → 40
                decoration: BoxDecoration(
                  color: colors.background,
                  borderRadius: BorderRadius.circular(AppBorderRadius.md),
                  border: Border.all(
                    color: colors.icon.withValues(alpha: 0.2),
                    width: 1.5,
                  ),
                ),
                child: Icon(
                  icon,
                  color: colors.icon,
                  size: 20,          // 32 → 20
                ),
              ),
              if (trailingIcon != null)
                Container(
                  width: 28,         // 44 → 28
                  height: 28,        // 44 → 28
                  decoration: BoxDecoration(
                    color: colors.background,
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    trailingIcon,
                    color: colors.icon,
                    size: 14,         // 24 → 14
                  ),
                ),
            ],
          ),
          const SizedBox(height: AppSpacing.xs),   // md → xs
          Text(
            title,
            style: AppTextStyles.bodySmall.copyWith(  // bodySmall → bodyMedium
              color: AppColors.gray500,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          const SizedBox(height: 2),
          Flexible(
            child: Text(
              value,
              style: AppTextStyles.h3.copyWith(
                color: AppColors.gray900,
                fontSize: 18,                           // 13 → 15
              ),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
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
