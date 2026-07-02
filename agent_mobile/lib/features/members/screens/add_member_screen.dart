import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:dio/dio.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/api/dio_client.dart';
import '../../../core/widgets/widgets.dart';

class AddMemberScreen extends StatefulWidget {
  const AddMemberScreen({super.key});

  @override
  State<AddMemberScreen> createState() => _AddMemberScreenState();
}

class _AddMemberScreenState extends State<AddMemberScreen> {
  final DioClient _dioClient = DioClient();
  final _formKey = GlobalKey<FormState>();
  final _notebookNumberController = TextEditingController();
  final _firstNameController = TextEditingController();
  final _lastNameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _addressController = TextEditingController();
  String? _selectedGender;
  bool _isLoading = false;
  String? _errorMessage;
  String? _notebookNumberError;
  String? _firstNameError;
  String? _lastNameError;
  String? _phoneError;
  String? _genderError;

  @override
  void dispose() {
    _notebookNumberController.dispose();
    _firstNameController.dispose();
    _lastNameController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    setState(() {
      _notebookNumberError = _notebookNumberController.text.trim().isEmpty
          ? 'Veuillez entrer le numéro de carnet'
          : null;
      _firstNameError = _firstNameController.text.trim().isEmpty
          ? 'Veuillez entrer le prénom'
          : null;
      _lastNameError = _lastNameController.text.trim().isEmpty
          ? 'Veuillez entrer le nom'
          : null;
      _phoneError = _phoneController.text.trim().isEmpty
          ? 'Veuillez entrer le numéro de téléphone'
          : null;
      _genderError = _selectedGender == null
          ? 'Veuillez sélectionner le genre'
          : null;
    });

    if (_notebookNumberError == null &&
        _firstNameError == null &&
        _lastNameError == null &&
        _phoneError == null &&
        _genderError == null) {
      setState(() {
        _isLoading = true;
        _errorMessage = null;
      });

      try {
        final response = await _dioClient.dio.post(
          '/members',
          data: {
            'notebook_number': _notebookNumberController.text.trim(),
            'firstname': _firstNameController.text.trim(),
            'lastname': _lastNameController.text.trim(),
            'phone': _phoneController.text.trim(),
            'gender': _selectedGender,
            'address': _addressController.text.trim(),
          },
        );

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Row(
                children: [
                  const Icon(Icons.check_circle, color: Colors.white, size: 20),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      response.data['message'] ?? 'Membre créé avec succès',
                      style: const TextStyle(fontWeight: FontWeight.w600),
                    ),
                  ),
                ],
              ),
              backgroundColor: AppColors.success,
              behavior: SnackBarBehavior.floating,
              margin: const EdgeInsets.fromLTRB(16, 0, 16, 16),
              shape: const RoundedRectangleBorder(
                borderRadius: BorderRadius.all(Radius.circular(12)),
              ),
              elevation: 4,
              duration: const Duration(seconds: 2),
            ),
          );
          await Future.delayed(const Duration(milliseconds: 2100));
          if (mounted) {
            context.pop(true);
          }
        }
      } catch (e) {
        setState(() {
          if (e is DioException && e.response != null) {
            final errors = e.response!.data['errors'];
            if (errors != null) {
              _errorMessage = errors.values.first.first;
            } else {
              _errorMessage = e.response!.data['message'] ?? 'Erreur inconnue';
            }
          } else {
            _errorMessage = 'Vérifiez votre connexion internet';
          }
        });
      } finally {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.gray50,
      appBar: const TontiAppBar(
        title: 'Nouveau membre',
      ),
      body: SafeArea(
        child: Form(
          key: _formKey,
          child: ListView(
            padding: const EdgeInsets.all(AppSpacing.lg),
            children: [
              // Info Card
              AppCard(
                gradient: LinearGradient(
                  colors: [AppColors.primary100, AppColors.primaryLight],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                padding: const EdgeInsets.all(AppSpacing.lg),
                margin: const EdgeInsets.only(bottom: AppSpacing.xl),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.3),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: const Icon(
                        Icons.person_add,
                        color: AppColors.primary,
                        size: 28,
                      ),
                    ),
                    const SizedBox(width: AppSpacing.md),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Enregistrement membre',
                            style: AppTextStyles.h3.copyWith(
                              color: AppColors.primary,
                              fontSize: 16,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Remplissez les informations du membre',
                            style: AppTextStyles.bodySmall.copyWith(
                              color: AppColors.primary700,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              // Form Fields
              AppTextField(
                label: 'Numéro de carnet',
                controller: _notebookNumberController,
                hintText: 'Ex: CARNET-001',
                prefixIcon: Icons.badge,
                errorText: _notebookNumberError,
                padding: const EdgeInsets.only(bottom: AppSpacing.md),
              ),
              Row(
                children: [
                  Expanded(
                    child: AppTextField(
                      label: 'Prénom',
                      controller: _firstNameController,
                      hintText: 'Entrez le prénom',
                      prefixIcon: Icons.person_outline,
                      errorText: _firstNameError,
                      padding: const EdgeInsets.only(bottom: AppSpacing.md),
                    ),
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Expanded(
                    child: AppTextField(
                      label: 'Nom',
                      controller: _lastNameController,
                      hintText: 'Entrez le nom',
                      prefixIcon: Icons.person,
                      errorText: _lastNameError,
                      padding: const EdgeInsets.only(bottom: AppSpacing.md),
                    ),
                  ),
                ],
              ),
              AppTextField(
                label: 'Téléphone',
                controller: _phoneController,
                hintText: '228 90 00 00 00',
                keyboardType: TextInputType.phone,
                prefixIcon: Icons.phone_android,
                errorText: _phoneError,
                padding: const EdgeInsets.only(bottom: AppSpacing.md),
              ),

              // Gender Selection Card
              AppCard(
                padding: const EdgeInsets.all(AppSpacing.md),
                margin: const EdgeInsets.only(bottom: AppSpacing.md),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Genre',
                      style: AppTextStyles.bodyLarge.copyWith(
                        color: AppColors.gray700,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: AppSpacing.sm),
                    Row(
                      children: [
                        Expanded(
                          child: _buildGenderOption('M', 'Homme', Icons.male),
                        ),
                        const SizedBox(width: AppSpacing.md),
                        Expanded(
                          child: _buildGenderOption('F', 'Femme', Icons.female),
                        ),
                      ],
                    ),
                    if (_genderError != null) ...[
                      const SizedBox(height: AppSpacing.sm),
                      Row(
                        children: [
                          const Icon(
                            Icons.error_outline,
                            color: AppColors.danger,
                            size: 16,
                          ),
                          const SizedBox(width: AppSpacing.sm),
                          Expanded(
                            child: Text(
                              _genderError!,
                              style: AppTextStyles.bodySmall.copyWith(
                                color: AppColors.danger,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ],
                ),
              ),

              AppTextField(
                label: 'Adresse',
                controller: _addressController,
                hintText: 'Entrez l\'adresse (optionnel)',
                prefixIcon: Icons.location_on_outlined,
                maxLines: 3,
                padding: const EdgeInsets.only(bottom: AppSpacing.md),
              ),

              // Error Message
              if (_errorMessage != null) ...[
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
                          _errorMessage!,
                          style: AppTextStyles.bodyMedium.copyWith(
                            color: AppColors.danger,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],

              const SizedBox(height: AppSpacing.xl),

              // Submit Button
              AppButton(
                text: 'Enregistrer le membre',
                isLoading: _isLoading,
                onPressed: _submit,
                icon: Icons.check_circle,
              ),
              const SizedBox(height: AppSpacing.lg),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildGenderOption(String value, String label, IconData icon) {
    final isSelected = _selectedGender == value;
    return GestureDetector(
      onTap: () {
        setState(() {
          _selectedGender = value;
          if (_genderError != null) {
            _genderError = null;
          }
        });
      },
      child: AnimatedContainer(
        duration: AppDurations.fast,
        curve: AppCurves.standard,
        padding: const EdgeInsets.symmetric(
          horizontal: AppSpacing.sm,   // md → sm pour éviter le débordement droit
          vertical: AppSpacing.md,
        ),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.primary100 : AppColors.white,
          borderRadius: BorderRadius.circular(AppBorderRadius.lg),
          border: Border.all(
            color: isSelected ? AppColors.primary : AppColors.gray200,
            width: isSelected ? 2 : 1,
          ),
          boxShadow: isSelected ? AppShadows.sm : null,
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              icon,
              color: isSelected ? AppColors.primary : AppColors.gray500,
              size: 20,
            ),
            const SizedBox(width: AppSpacing.sm),
            Text(
              label,
              style: AppTextStyles.bodyMedium.copyWith(
                color: isSelected ? AppColors.primary : AppColors.gray700,
                fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
