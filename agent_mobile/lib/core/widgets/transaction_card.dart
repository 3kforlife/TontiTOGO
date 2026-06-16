import 'package:flutter/material.dart';
import '../constants/app_constants.dart';

enum TransactionType {
  deposit,
  withdrawal,
  transfer,
}

class TransactionCard extends StatelessWidget {
  final String title;
  final String subtitle;
  final String amount;
  final String date;
  final TransactionType type;
  final VoidCallback? onTap;

  const TransactionCard({
    super.key,
    required this.title,
    required this.subtitle,
    required this.amount,
    required this.date,
    required this.type,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final colors = _getTypeColors();

    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(AppBorderRadius.lg),
      child: Padding(
        padding: const EdgeInsets.symmetric(
          horizontal: AppSpacing.sm,
          vertical: AppSpacing.sm,
        ),
        child: Row(
          children: [
            Container(
              width: 48,
              height: 48,
              decoration: BoxDecoration(
                color: colors.background,
                borderRadius: BorderRadius.circular(AppBorderRadius.lg),
              ),
              child: Icon(
                _getIcon(),
                color: colors.icon,
                size: 24,
              ),
            ),
            const SizedBox(width: AppSpacing.md),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: AppTextStyles.bodyLarge.copyWith(
                      color: AppColors.gray900,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: AppSpacing.xs),
                  Text(
                    subtitle,
                    style: AppTextStyles.bodyMedium.copyWith(
                      color: AppColors.gray500,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(width: AppSpacing.md),
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                  amount,
                  style: AppTextStyles.h3.copyWith(
                    color: colors.amount,
                  ),
                ),
                const SizedBox(height: AppSpacing.xs),
                Text(
                  date,
                  style: AppTextStyles.bodySmall.copyWith(
                    color: AppColors.gray500,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  ({Color icon, Color background, Color amount}) _getTypeColors() {
    switch (type) {
      case TransactionType.deposit:
        return (
          icon: AppColors.success,
          background: AppColors.success.withValues(alpha: 0.1),
          amount: AppColors.success,
        );
      case TransactionType.withdrawal:
        return (
          icon: AppColors.danger,
          background: AppColors.danger.withValues(alpha: 0.1),
          amount: AppColors.danger,
        );
      case TransactionType.transfer:
        return (
          icon: AppColors.info,
          background: AppColors.info.withValues(alpha: 0.1),
          amount: AppColors.info,
        );
    }
  }

  IconData _getIcon() {
    switch (type) {
      case TransactionType.deposit:
        return Icons.arrow_downward;
      case TransactionType.withdrawal:
        return Icons.arrow_upward;
      case TransactionType.transfer:
        return Icons.swap_horiz;
    }
  }
}
