import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/widgets/widgets.dart';

class OtpScreen extends StatefulWidget {
  const OtpScreen({super.key});

  @override
  State<OtpScreen> createState() => _OtpScreenState();
}

class _OtpScreenState extends State<OtpScreen> {
  final _formKey = GlobalKey<FormState>();
  final _otpController = TextEditingController();
  String? _otpError;

  @override
  void dispose() {
    _otpController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    setState(() {
      if (_otpController.text.trim().isEmpty) {
        _otpError = 'Veuillez entrer le code OTP';
      } else if (_otpController.text.length != 6) {
        _otpError = 'Le code doit comporter 6 chiffres';
      } else {
        _otpError = null;
      }
    });

    if (_otpError == null) {
      context.go('/forgot-password/reset', extra: _otpController.text.trim());
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);

    return Scaffold(
      backgroundColor: AppColors.gray50,
      appBar: const TontiAppBar(
        title: 'Vérification du code',
      ),
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(AppSpacing.lg),
            child: AppCard(
              padding: const EdgeInsets.all(AppSpacing.xl),
              child: Form(
                key: _formKey,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const Center(
                      child: Column(
                        children: [
                          AppFeatureIcon(
                            icon: Icons.sms,
                            size: 80,
                          ),
                          SizedBox(height: AppSpacing.lg),
                          Text(
                            'Vérification du code',
                            style: AppTextStyles.h2,
                          ),
                          SizedBox(height: AppSpacing.sm),
                          Text(
                            'Entrez le code OTP reçu par SMS',
                            style: AppTextStyles.bodyMedium,
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: AppSpacing.xl),
                    AppTextField(
                      label: 'Code OTP (6 chiffres)',
                      controller: _otpController,
                      hintText: '000000',
                      keyboardType: TextInputType.number,
                      maxLength: 6,
                      prefixIcon: Icons.pin,
                      errorText: _otpError,
                    ),
                    if (authProvider.errorMessage != null) ...[
                      const SizedBox(height: AppSpacing.md),
                      Container(
                        padding: const EdgeInsets.all(AppSpacing.md),
                        decoration: BoxDecoration(
                          color: AppColors.danger.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(AppBorderRadius.lg),
                          border: Border.all(
                            color: AppColors.danger.withValues(alpha: 0.3),
                          ),
                        ),
                        child: Row(
                          children: [
                            const Icon(
                              Icons.error_outline,
                              color: AppColors.danger,
                              size: 20,
                            ),
                            const SizedBox(width: AppSpacing.sm),
                            Expanded(
                              child: Text(
                                authProvider.errorMessage!,
                                style: AppTextStyles.bodyMedium.copyWith(
                                  color: AppColors.danger,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                    const SizedBox(height: AppSpacing.lg),
                    AppButton(
                      text: 'Vérifier',
                      isLoading: authProvider.isLoading,
                      onPressed: _submit,
                      icon: Icons.check,
                    ),
                    const SizedBox(height: AppSpacing.md),
                    Align(
                      alignment: Alignment.center,
                      child: AppButton(
                        text: 'Réessayer avec un autre numéro',
                        type: AppButtonType.text,
                        onPressed: () => context.go('/forgot-password'),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
