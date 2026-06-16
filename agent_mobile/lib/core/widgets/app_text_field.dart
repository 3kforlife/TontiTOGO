import 'package:flutter/material.dart';
import '../constants/app_constants.dart';

class AppTextField extends StatefulWidget {
  final String label;
  final TextEditingController? controller;
  final String? hintText;
  final TextInputType? keyboardType;
  final bool obscureText;
  final bool? enabled;
  final int? maxLines;
  final int? maxLength;
  final IconData? prefixIcon;
  final IconData? suffixIcon;
  final VoidCallback? suffixIconOnTap;
  final String? errorText;
  final Function(String)? onChanged;
  final Function(String)? onSubmitted;
  final TextInputAction? textInputAction;
  final bool readOnly;
  final String? initialValue;
  final EdgeInsetsGeometry? padding;
  final String? suffixText;
  final String? Function(String?)? validator;

  const AppTextField({
    super.key,
    required this.label,
    this.controller,
    this.hintText,
    this.keyboardType,
    this.obscureText = false,
    this.enabled = true,
    this.maxLines = 1,
    this.maxLength,
    this.prefixIcon,
    this.suffixIcon,
    this.suffixIconOnTap,
    this.errorText,
    this.onChanged,
    this.onSubmitted,
    this.textInputAction,
    this.readOnly = false,
    this.initialValue,
    this.padding,
    this.suffixText,
    this.validator,
  });

  @override
  State<AppTextField> createState() => _AppTextFieldState();
}

class _AppTextFieldState extends State<AppTextField> {
  bool _isObscured = false;
  bool _isFocused = false;
  final FocusNode _focusNode = FocusNode();

  @override
  void initState() {
    super.initState();
    _isObscured = widget.obscureText;
    _focusNode.addListener(() {
      setState(() => _isFocused = _focusNode.hasFocus);
    });
  }

  @override
  void dispose() {
    _focusNode.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: widget.padding ?? EdgeInsets.zero,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            widget.label,
            style: AppTextStyles.bodyLarge.copyWith(
              color: AppColors.gray700,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: AppSpacing.sm),
          Container(
            decoration: BoxDecoration(
              color: widget.enabled ?? true ? AppColors.white : AppColors.gray100,
              borderRadius: BorderRadius.circular(AppBorderRadius.lg),
              border: Border.all(
                color: widget.errorText != null
                    ? AppColors.danger
                    : _isFocused
                        ? AppColors.primary
                        : AppColors.gray200,
                width: _isFocused || widget.errorText != null ? 2 : 1,
              ),
              boxShadow: _isFocused
                  ? [
                      BoxShadow(
                        color: AppColors.primary.withValues(alpha: 0.1),
                        blurRadius: 8,
                        offset: const Offset(0, 0),
                      ),
                    ]
                  : null,
            ),
            child: TextFormField(
              focusNode: _focusNode,
              controller: widget.controller,
              initialValue: widget.initialValue,
              keyboardType: widget.keyboardType,
              obscureText: _isObscured,
              enabled: widget.enabled,
              maxLines: widget.maxLines,
              maxLength: widget.maxLength,
              onChanged: widget.onChanged,
              onFieldSubmitted: widget.onSubmitted,
              textInputAction: widget.textInputAction,
              readOnly: widget.readOnly,
              style: AppTextStyles.bodyLarge.copyWith(
                color: widget.enabled ?? true ? AppColors.gray800 : AppColors.gray500,
              ),
              decoration: InputDecoration(
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: AppSpacing.lg,
                  vertical: AppSpacing.md,
                ),
                hintText: widget.hintText,
                hintStyle: AppTextStyles.bodyLarge.copyWith(
                  color: AppColors.gray400,
                ),
                prefixIcon: widget.prefixIcon != null
                    ? Icon(
                        widget.prefixIcon,
                        color: _isFocused ? AppColors.primary : AppColors.gray400,
                        size: 20,
                      )
                    : null,
                suffixIcon: widget.obscureText
                    ? _buildObscureIcon()
                    : widget.suffixIcon != null
                        ? _buildSuffixIcon()
                        : null,
                border: InputBorder.none,
                errorBorder: InputBorder.none,
                focusedBorder: InputBorder.none,
                enabledBorder: InputBorder.none,
                counterText: '',
              ),
            ),
          ),
          if (widget.errorText != null) ...[
            const SizedBox(height: AppSpacing.xs),
            Row(
              children: [
                Icon(
                  Icons.error_outline,
                  color: AppColors.danger,
                  size: 16,
                ),
                const SizedBox(width: AppSpacing.xs),
                Expanded(
                  child: Text(
                    widget.errorText!,
                    style: AppTextStyles.bodySmall.copyWith(
                      color: AppColors.danger,
                      fontWeight: FontWeight.w500,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  Widget? _buildObscureIcon() {
    return InkWell(
      onTap: () {
        setState(() => _isObscured = !_isObscured);
      },
      child: Icon(
        _isObscured ? Icons.visibility_off : Icons.visibility,
        color: _isFocused ? AppColors.primary : AppColors.gray400,
        size: 20,
      ),
    );
  }

  Widget? _buildSuffixIcon() {
    return InkWell(
      onTap: widget.suffixIconOnTap,
      child: Icon(
        widget.suffixIcon,
        color: _isFocused ? AppColors.primary : AppColors.gray400,
        size: 20,
      ),
    );
  }
}
