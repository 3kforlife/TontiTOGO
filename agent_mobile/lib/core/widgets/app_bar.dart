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
              padding: const EdgeInsets.only(left: AppSpacing.sm),
              child: IconButton(
                icon: Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: AppColors.gray50,
                    borderRadius: BorderRadius.circular(AppBorderRadius.md),
                  ),
                  child: Icon(
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
        style: AppTextStyles.h3,
      ),
      actions: action != null
          ? [
              Padding(
                padding: const EdgeInsets.only(right: AppSpacing.sm),
                child: action,
              ),
            ]
          : null,
    );
  }

  @override
  Size get preferredSize => const Size.fromHeight(64);
}
