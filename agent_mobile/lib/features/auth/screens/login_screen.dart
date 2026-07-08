import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/widgets/widgets.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  String? _phoneError;
  String? _passwordError;

  @override
  void dispose() {
    _phoneController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    setState(() {
      _phoneError = _phoneController.text.trim().isEmpty
          ? 'Veuillez entrer votre numéro de téléphone'
          : null;
      _passwordError = _passwordController.text.trim().isEmpty
          ? 'Veuillez entrer votre mot de passe'
          : null;
    });

    if (_phoneError == null && _passwordError == null) {
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      final success = await authProvider.login(
        phone: _phoneController.text.trim(),
        password: _passwordController.text.trim(),
      );

      if (success && mounted) {
        if (authProvider.mustChangePassword) {
          context.go('/change-password');
        } else {
          context.go('/');
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);

    return Scaffold(
      backgroundColor: AppColors.gray50,
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
                          // TontiTOGO Logo
                          TontiTogoLogo(),
                          SizedBox(height: AppSpacing.lg),
                          Text(
                            'Connexion',
                            style: AppTextStyles.h2,
                          ),
                          SizedBox(height: AppSpacing.sm),
                          Text(
                            'Connectez-vous pour continuer',
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
                      hintText: 'votre numéro de téléphone',
                      keyboardType: TextInputType.phone,
                      prefixIcon: Icons.phone,
                      errorText: _phoneError,
                      padding: const EdgeInsets.only(bottom: AppSpacing.md),
                    ),
                    AppTextField(
                      label: 'Mot de passe',
                      controller: _passwordController,
                      hintText: 'votre mot de passe',
                      obscureText: true,
                      prefixIcon: Icons.lock,
                      errorText: _passwordError,
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
                    const SizedBox(height: AppSpacing.sm),
                    Align(
                      alignment: Alignment.centerRight,
                      child: AppButton(
                        text: 'Mot de passe oublié ?',
                        type: AppButtonType.text,
                        onPressed: () => context.go('/forgot-password'),
                      ),
                    ),
                    const SizedBox(height: AppSpacing.lg),
                    AppButton(
                      text: 'Se connecter',
                      isLoading: authProvider.isLoading,
                      onPressed: _submit,
                      icon: Icons.arrow_forward,
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


