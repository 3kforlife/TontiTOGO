import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/widgets/widgets.dart';

class ChangePasswordScreen extends StatefulWidget {
  const ChangePasswordScreen({super.key});

  @override
  State<ChangePasswordScreen> createState() => _ChangePasswordScreenState();
}

class _ChangePasswordScreenState extends State<ChangePasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  String? _passwordError;
  String? _confirmPasswordError;

  @override
  void dispose() {
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    setState(() {
      if (_passwordController.text.trim().isEmpty) {
        _passwordError = 'Veuillez entrer un mot de passe';
      } else if (_passwordController.text.trim().length < 8) {
        _passwordError = 'Le mot de passe doit contenir au moins 8 caractères';
      } else if (!RegExp(r'(?=.*[a-z])(?=.*[A-Z])').hasMatch(_passwordController.text.trim())) {
        _passwordError = 'Le mot de passe doit contenir au moins une lettre majuscule et une lettre minuscule';
      } else if (!RegExp(r'(?=.*\d)').hasMatch(_passwordController.text.trim())) {
        _passwordError = 'Le mot de passe doit contenir au moins un chiffre';
      } else if (!RegExp(r'(?=.*[@$!%*#?&_\-+=^~])').hasMatch(_passwordController.text.trim())) {
        _passwordError = 'Le mot de passe doit contenir au moins un caractère spécial (@ \$ ! % * # ? & _ - + = ^ ~)';
      } else {
        _passwordError = null;
      }

      if (_confirmPasswordController.text.trim().isEmpty) {
        _confirmPasswordError = 'Veuillez confirmer votre mot de passe';
      } else if (_confirmPasswordController.text.trim() != _passwordController.text.trim()) {
        _confirmPasswordError = 'Les mots de passe ne correspondent pas';
      } else {
        _confirmPasswordError = null;
      }
    });

    if (_passwordError == null && _confirmPasswordError == null) {
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      final success = await authProvider.changePassword(
        password: _passwordController.text.trim(),
        passwordConfirmation: _confirmPasswordController.text.trim(),
      );

      if (success && mounted) {
        context.go('/dashboard');
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);

    return Scaffold(
      backgroundColor: AppColors.gray50,
      appBar: const TontiAppBar(
        title: 'Changer de mot de passe',
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppSpacing.lg),
          child: Form(
            key: _formKey,
            child: Column(
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
                        'Changement obligatoire',
                        textAlign: TextAlign.center,
                        style: AppTextStyles.h2,
                      ),
                      SizedBox(height: AppSpacing.sm),
                      Text(
                        'Veuillez choisir un nouveau mot de passe pour sécuriser votre compte.',
                        textAlign: TextAlign.center,
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
                  label: 'Confirmer le mot de passe',
                  controller: _confirmPasswordController,
                  hintText: 'Confirmez votre nouveau mot de passe',
                  obscureText: true,
                  prefixIcon: Icons.lock_outline,
                  errorText: _confirmPasswordError,
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
                  text: 'Changer le mot de passe',
                  isLoading: authProvider.isLoading,
                  onPressed: _submit,
                  icon: Icons.check,
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
