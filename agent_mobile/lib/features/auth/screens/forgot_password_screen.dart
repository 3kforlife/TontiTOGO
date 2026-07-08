import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/widgets/widgets.dart';

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _phoneController = TextEditingController();
  String? _phoneError;

  @override
  void dispose() {
    _phoneController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    setState(() {
      _phoneError = _phoneController.text.trim().isEmpty
          ? 'Veuillez entrer votre numéro de téléphone'
          : null;
    });

    if (_phoneError == null) {
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      final success = await authProvider.requestOtp(
        phone: _phoneController.text.trim(),
      );

      if (success && mounted) {
        context.go('/forgot-password/otp');
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);

    return Scaffold(
      backgroundColor: AppColors.gray50,
      appBar: const TontiAppBar(
        title: 'Mot de passe oublié',
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
                            icon: Icons.lock_reset,
                            size: 80,
                          ),
                          SizedBox(height: AppSpacing.lg),
                          Text(
                            'Mot de passe oublié',
                            style: AppTextStyles.h2,
                          ),
                          SizedBox(height: AppSpacing.sm),
                          Text(
                            'Entrez votre numéro de téléphone pour recevoir un code de réinitialisation',
                            textAlign: TextAlign.center,
                            style: AppTextStyles.bodyMedium,
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: AppSpacing.xl),
                    AppTextField(
                      label: 'Numéro de téléphone',
                      controller: _phoneController,
                      hintText: 'numéro de téléphone',
                      keyboardType: TextInputType.phone,
                      prefixIcon: Icons.phone,
                      errorText: _phoneError,
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
                      text: 'Envoyer le code',
                      isLoading: authProvider.isLoading,
                      onPressed: _submit,
                      icon: Icons.send,
                    ),
                    const SizedBox(height: AppSpacing.md),
                    Align(
                      alignment: Alignment.center,
                      child: AppButton(
                        text: 'Retour à la connexion',
                        type: AppButtonType.text,
                        onPressed: () => context.go('/login'),
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
