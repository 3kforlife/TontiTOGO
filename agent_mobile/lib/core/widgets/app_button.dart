import 'package:flutter/material.dart';
import '../constants/app_constants.dart';

enum AppButtonType {
  primary,
  secondary,
  danger,
  text,
}

class AppButton extends StatefulWidget {
  final String text;
  final AppButtonType type;
  final VoidCallback? onPressed;
  final IconData? icon;
  final bool isLoading;
  final bool isDisabled;
  final double? height;
  final double? width;

  const AppButton({
    super.key,
    required this.text,
    this.type = AppButtonType.primary,
    required this.onPressed,
    this.icon,
    this.isLoading = false,
    this.isDisabled = false,
    this.height,
    this.width,
  });

  @override
  State<AppButton> createState() => _AppButtonState();
}

class _AppButtonState extends State<AppButton> {
  bool _isPressed = false;

  @override
  Widget build(BuildContext context) {
    final isActuallyDisabled = widget.isDisabled || widget.isLoading;

    return GestureDetector(
      onTapDown: (_) {
        if (!isActuallyDisabled) {
          setState(() => _isPressed = true);
        }
      },
      onTapUp: (_) {
        if (!isActuallyDisabled) {
          setState(() => _isPressed = false);
          widget.onPressed?.call();
        }
      },
      onTapCancel: () {
        if (!isActuallyDisabled) {
          setState(() => _isPressed = false);
        }
      },
      child: AnimatedContainer(
        duration: AppDurations.fast,
        curve: AppCurves.standard,
        transform: Matrix4.translationValues(
          0,
          _isPressed ? 2 : 0,
          0,
        ),
        height: widget.height ?? 54,
        width: widget.width ?? double.infinity,
        decoration: _buildDecoration(isActuallyDisabled),
        padding: const EdgeInsets.symmetric(
          horizontal: AppSpacing.lg,
        ),
        child: _buildContent(isActuallyDisabled),
      ),
    );
  }

  BoxDecoration _buildDecoration(bool isDisabled) {
    switch (widget.type) {
      case AppButtonType.primary:
        return BoxDecoration(
          gradient: isDisabled
              ? LinearGradient(
                  colors: [
                    AppColors.gray300,
                    AppColors.gray400,
                  ],
                )
              : LinearGradient(
                  colors: [
                    AppColors.primary,
                    AppColors.primaryDark,
                  ],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
          borderRadius: BorderRadius.circular(AppBorderRadius.lg),
          boxShadow: isDisabled
              ? []
              : [
                  BoxShadow(
                    color: AppColors.primary.withValues(alpha: 0.3),
                    blurRadius: _isPressed ? 8 : 16,
                    offset: Offset(0, _isPressed ? 2 : 7),
                  ),
                ],
        );
      case AppButtonType.secondary:
        return BoxDecoration(
          color: isDisabled ? AppColors.gray100 : AppColors.white,
          border: Border.all(
            color: isDisabled ? AppColors.gray200 : AppColors.gray200,
          ),
          borderRadius: BorderRadius.circular(AppBorderRadius.lg),
          boxShadow: isDisabled
              ? []
              : _isPressed
                  ? AppShadows.sm
                  : AppShadows.md,
        );
      case AppButtonType.danger:
        return BoxDecoration(
          color: isDisabled ? AppColors.gray300 : AppColors.danger,
          borderRadius: BorderRadius.circular(AppBorderRadius.lg),
          boxShadow: isDisabled
              ? []
              : [
                  BoxShadow(
                    color: AppColors.danger.withValues(alpha: 0.2),
                    blurRadius: _isPressed ? 8 : 20,
                    spreadRadius: _isPressed ? 0 : 2,
                    offset: Offset(0, _isPressed ? 2 : 8),
                  ),
                ],
        );
      case AppButtonType.text:
        return const BoxDecoration();
    }
  }

  Widget _buildContent(bool isDisabled) {
    final textColor = _getTextColor(isDisabled);

    return Center(
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (widget.isLoading)
            SizedBox(
              width: 20,
              height: 20,
              child: CircularProgressIndicator(
                color: textColor,
                strokeWidth: 2,
              ),
            )
          else ...[
            if (widget.icon != null) ...[
              Icon(
                widget.icon,
                color: textColor,
                size: 20,
              ),
              const SizedBox(width: AppSpacing.sm),
            ],
            Text(
              widget.text,
              style: AppTextStyles.button.copyWith(color: textColor),
            ),
          ],
        ],
      ),
    );
  }

  Color _getTextColor(bool isDisabled) {
    switch (widget.type) {
      case AppButtonType.primary:
        return isDisabled ? AppColors.gray500 : AppColors.white;
      case AppButtonType.secondary:
        return isDisabled ? AppColors.gray400 : AppColors.gray700;
      case AppButtonType.danger:
        return isDisabled ? AppColors.gray500 : AppColors.white;
      case AppButtonType.text:
        return isDisabled ? AppColors.gray400 : AppColors.primary;
    }
  }
}
