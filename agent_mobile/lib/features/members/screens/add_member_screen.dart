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
              content: Text(response.data['message'] ?? 'Membre créé avec succès'),
              backgroundColor: AppColors.success,
              behavior: SnackBarBehavior.floating,
              margin: const EdgeInsets.fromLTRB(16, 40, 16, 600),
              shape: const RoundedRectangleBorder(
                borderRadius: BorderRadius.all(Radius.circular(12)),
              ),
              elevation: 4,
              duration: const Duration(seconds: 2),
            ),
          );
          await Future.delayed(const Duration(milliseconds: 500));
          if (mounted) {
            context.pop();
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
              AppTextField(
                label: 'Numéro de carnet',
                controller: _notebookNumberController,
                hintText: 'Entrez le numéro de carnet',
                prefixIcon: Icons.book,
                errorText: _notebookNumberError,
                padding: const EdgeInsets.only(bottom: AppSpacing.md),
              ),
              AppTextField(
                label: 'Prénom',
                controller: _firstNameController,
                hintText: 'Entrez le prénom',
                prefixIcon: Icons.person_outline,
                errorText: _firstNameError,
                padding: const EdgeInsets.only(bottom: AppSpacing.md),
              ),
              AppTextField(
                label: 'Nom',
                controller: _lastNameController,
                hintText: 'Entrez le nom',
                prefixIcon: Icons.person,
                errorText: _lastNameError,
                padding: const EdgeInsets.only(bottom: AppSpacing.md),
              ),
              AppTextField(
                label: 'Téléphone',
                controller: _phoneController,
                hintText: 'Entrez le numéro de téléphone',
                keyboardType: TextInputType.phone,
                prefixIcon: Icons.phone,
                errorText: _phoneError,
                padding: const EdgeInsets.only(bottom: AppSpacing.md),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: AppSpacing.md,
                ),
                decoration: BoxDecoration(
                  color: AppColors.white,
                  borderRadius: BorderRadius.circular(AppBorderRadius.lg),
                  border: Border.all(
                    color: _genderError != null
                        ? AppColors.danger
                        : AppColors.gray200,
                    width: _genderError != null ? 2 : 1,
                  ),
                ),
                child: DropdownButtonHideUnderline(
                  child: DropdownButtonFormField<String>(
                    value: _selectedGender,
                    decoration: const InputDecoration(
                      border: InputBorder.none,
                      hintText: 'Sélectionnez le genre',
                      prefixIcon: Icon(Icons.wc),
                    ),
                    items: const [
                      DropdownMenuItem(value: 'M', child: Text('Homme')),
                      DropdownMenuItem(value: 'F', child: Text('Femme')),
                    ],
                    onChanged: (value) {
                      setState(() {
                        _selectedGender = value;
                        if (_genderError != null) {
                          _genderError = null;
                        }
                      });
                    },
                  ),
                ),
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
              const SizedBox(height: AppSpacing.md),
              AppTextField(
                label: 'Adresse',
                controller: _addressController,
                hintText: 'Entrez l\'adresse',
                prefixIcon: Icons.location_on,
                maxLines: 3,
              ),
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
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
              const SizedBox(height: AppSpacing.lg),
              AppButton(
                text: 'Enregistrer',
                isLoading: _isLoading,
                onPressed: _submit,
                icon: Icons.save,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
