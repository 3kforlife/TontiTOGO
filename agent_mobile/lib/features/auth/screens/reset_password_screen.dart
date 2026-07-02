import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/widgets/widgets.dart';

class ResetPasswordScreen extends StatefulWidget {
  const ResetPasswordScreen({super.key});

  @override
  State<ResetPasswordScreen> createState() => _ResetPasswordScreenState();
}

class _ResetPasswordScreenState extends State<ResetPasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _passwordController = TextEditingController();
  final _passwordConfirmController = TextEditingController();
  String? _passwordError;
  String? _passwordConfirmError;

  bool _validatePassword(String value) {
    if (value.isEmpty) return false;
    if (value.length < 8) return false;
    if (!value.contains(RegExp(r'[A-Z]'))) return false;
    if (!value.contains(RegExp(r'[a-z]'))) return false;
    if (!value.contains(RegExp(r'[0-9]'))) return false;
    if (!value.contains(RegExp(r'[!@#$%^&*(),.?":{}|<>]'))) return false;
    return true;
  }

  Future<void> _submit(String otp) async {
    setState(() {
      if (_passwordController.text.trim().isEmpty) {
        _passwordError = 'Veuillez entrer un mot de passe';
      } else if (!_validatePassword(_passwordController.text)) {
        _passwordError =
            'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un symbole';
      } else {
        _passwordError = null;
      }

      if (_passwordConfirmController.text.trim().isEmpty) {
        _passwordConfirmError = 'Veuillez confirmer le mot de passe';
      } else if (_passwordConfirmController.text != _passwordController.text) {
        _passwordConfirmError = 'Les mots de passe ne correspondent pas';
      } else {
        _passwordConfirmError = null;
      }
    });

    if (_passwordError == null && _passwordConfirmError == null) {
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      final success = await authProvider.resetPassword(
        otp: otp,
        password: _passwordController.text.trim(),
        passwordConfirmation: _passwordConfirmController.text.trim(),
      );

      if (success && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Mot de passe réinitialisé avec succès !'),
            backgroundColor: AppColors.success,
            behavior: SnackBarBehavior.floating,
            margin: EdgeInsets.fromLTRB(16, 0, 16, 16),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.all(Radius.circular(12)),
            ),
            elevation: 4,
            duration: Duration(seconds: 2),
          ),
        );
        await Future.delayed(const Duration(milliseconds: 500));
        if (mounted) {
          context.go('/login');
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final otp = GoRouterState.of(context).extra as String?;

    return Scaffold(
      backgroundColor: AppColors.gray50,
      appBar: const TontiAppBar(
        title: 'Nouveau mot de passe',
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
                            icon: Icons.lock_open,
                            size: 80,
                          ),
                          SizedBox(height: AppSpacing.lg),
                          Text(
                            'Nouveau mot de passe',
                            style: AppTextStyles.h2,
                          ),
                          SizedBox(height: AppSpacing.sm),
                          Text(
                            'Créez un nouveau mot de passe',
                            style: AppTextStyles.bodyMedium,
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: AppSpacing.xl),
                    AppTextField(
                      label: 'Nouveau mot de passe',
                      controller: _passwordController,
                      hintText: 'Entrez votre nouveau mot de passe',
                      obscureText: true,
                      prefixIcon: Icons.lock,
                      errorText: _passwordError,
                      padding: const EdgeInsets.only(bottom: AppSpacing.md),
                    ),
                    AppTextField(
                      label: 'Confirmez le mot de passe',
                      controller: _passwordConfirmController,
                      hintText: 'Confirmez votre nouveau mot de passe',
                      obscureText: true,
                      prefixIcon: Icons.lock,
                      errorText: _passwordConfirmError,
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
                      text: 'Réinitialiser',
                      isLoading: authProvider.isLoading,
                      isDisabled: otp == null,
                      onPressed: (otp != null)
                          ? () => _submit(otp!)
                          : null,
                      icon: Icons.refresh,
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
