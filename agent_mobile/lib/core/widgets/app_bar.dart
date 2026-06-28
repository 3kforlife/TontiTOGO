import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../constants/app_constants.dart';

class TontiAppBar extends StatelessWidget implements PreferredSizeWidget {
  final String title;
  final Widget? action;
  final bool showBackButton;

  const TontiAppBar({
    super.key,
    required this.title,
    this.action,
    this.showBackButton = true,
  });

  @override
  Widget build(BuildContext context) {
    return AppBar(
      backgroundColor: AppColors.white,
      elevation: 0,
      scrolledUnderElevation: 0,
      leading: showBackButton
          ? Padding(
              padding: const EdgeInsets.only(left: AppSpacing.md),
              child: IconButton(
                icon: Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: AppColors.gray50,
                    borderRadius: BorderRadius.circular(AppBorderRadius.lg),
                    border: Border.all(color: AppColors.gray200.withValues(alpha: 0.5), width: 0.5),
                  ),
                  child: const Icon(
                    Icons.arrow_back_ios_new,
                    color: AppColors.gray700,
                    size: 18,
                  ),
                ),
                onPressed: () => context.pop(),
              ),
            )
          : null,
      centerTitle: true,
      title: Text(
        title,
        style: AppTextStyles.h3.copyWith(
          color: AppColors.gray900,
          fontSize: 18,
        ),
      ),
      actions: action != null
          ? [
              Padding(
                padding: const EdgeInsets.only(right: AppSpacing.md),
                child: action,
              ),
            ]
          : null,
    );
  }

  @override
  Size get preferredSize => const Size.fromHeight(64);
}
