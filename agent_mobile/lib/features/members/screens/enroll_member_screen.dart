import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/api/dio_client.dart';
import '../../../core/models/tontine.dart';
import '../../../core/widgets/widgets.dart';

class EnrollMemberScreen extends StatefulWidget {
  final int memberId;
  final String memberName;

  const EnrollMemberScreen({
    super.key,
    required this.memberId,
    required this.memberName,
  });

  @override
  State<EnrollMemberScreen> createState() => _EnrollMemberScreenState();
}

class _EnrollMemberScreenState extends State<EnrollMemberScreen> {
  final DioClient _dioClient = DioClient();
  final _formKey = GlobalKey<FormState>();
  final _amountController = TextEditingController();

  List<Tontine> _tontines = [];
  Tontine? _selectedTontine;
  bool _isLoading = true;
  bool _isSubmitting = false;
  String? _error;
  String? _tontineError;
  String? _amountError;

  @override
  void initState() {
    super.initState();
    _loadTontines();
  }

  @override
  void dispose() {
    _amountController.dispose();
    super.dispose();
  }

  Future<void> _loadTontines() async {
    try {
      final response = await _dioClient.dio.get('/tontines');
      print('Response data: ${response.data}');
      print('Response type: ${response.data.runtimeType}');

      final responseData = response.data;
      List tontinesData;

      if (responseData is Map && responseData.containsKey('data')) {
        final data = responseData['data'];
        if (data is List) {
          tontinesData = data;
        } else if (data is Map) {
          tontinesData = data.values.toList();
        } else {
          throw Exception('Format de réponse invalide');
        }
      } else if (responseData is List) {
        tontinesData = responseData;
      } else {
        throw Exception('Format de réponse invalide');
      }

      setState(() {
        _tontines = tontinesData
            .map((json) => Tontine.fromJson(json as Map<String, dynamic>))
            .toList();
        _isLoading = false;
      });
    } catch (e, stackTrace) {
      print('Error: $e');
      print('Stack trace: $stackTrace');
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _submit() async {
    setState(() {
      _tontineError =
          _selectedTontine == null ? 'Veuillez sélectionner une tontine' : null;
      if (_amountController.text.trim().isEmpty) {
        _amountError = 'Veuillez entrer un montant';
      } else {
        final amount = double.tryParse(_amountController.text.trim());
        if (amount == null) {
          _amountError = 'Montant invalide';
        } else if (_selectedTontine != null &&
            amount < _selectedTontine!.minimumAmount) {
          _amountError =
              'Montant minimum: ${_formatAmount(_selectedTontine!.minimumAmount)}';
        } else {
          _amountError = null;
        }
      }
    });

    if (_tontineError == null && _amountError == null) {
      setState(() {
        _isSubmitting = true;
      });

      try {
        await _dioClient.dio.post(
          '/members/${widget.memberId}/enroll',
          data: {
            'tontine_id': _selectedTontine!.id,
            'chosen_amount': double.parse(_amountController.text.trim()),
          },
        );

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Membre inscrit avec succès !'),
              backgroundColor: AppColors.success,
              behavior: SnackBarBehavior.floating,
              margin: EdgeInsets.fromLTRB(16, 40, 16, 600),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.all(Radius.circular(12)),
              ),
              elevation: 4,
              duration: Duration(seconds: 2),
            ),
          );
          await Future.delayed(const Duration(milliseconds: 500));
          if (mounted) {
            context.pop(true);
          }
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(e.toString()),
              backgroundColor: AppColors.danger,
              behavior: SnackBarBehavior.floating,
              margin: EdgeInsets.fromLTRB(16, 40, 16, 600),
              shape: const RoundedRectangleBorder(
                borderRadius: BorderRadius.all(Radius.circular(12)),
              ),
              elevation: 4,
            ),
          );
        }
      } finally {
        if (mounted) {
          setState(() {
            _isSubmitting = false;
          });
        }
      }
    }
  }

  String _formatAmount(double amount) {
    return '${NumberFormat('#,##0', 'fr_FR').format(amount)} FCFA';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.gray50,
      appBar: const TontiAppBar(
        title: 'Inscrire à une tontine',
      ),
      body: SafeArea(
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? _buildError()
                : _buildForm(),
      ),
    );
  }

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppSpacing.xl),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const AppFeatureIcon(
              icon: Icons.error_outline,
              size: 80,
              iconColor: AppColors.danger,
              backgroundColor: AppColors.danger,
            ),
            const SizedBox(height: AppSpacing.lg),
            Text(
              _error!,
              textAlign: TextAlign.center,
              style: AppTextStyles.bodyMedium,
            ),
            const SizedBox(height: AppSpacing.xl),
            AppButton(
              text: 'Réessayer',
              onPressed: _loadTontines,
              icon: Icons.refresh,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildForm() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            AppCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Membre : ${widget.memberName}',
                    style: AppTextStyles.h3,
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.xl),
            Container(
              padding: const EdgeInsets.symmetric(
                horizontal: AppSpacing.md,
              ),
              decoration: BoxDecoration(
                color: AppColors.white,
                borderRadius: BorderRadius.circular(AppBorderRadius.lg),
                border: Border.all(
                  color: _tontineError != null
                      ? AppColors.danger
                      : AppColors.gray200,
                  width: _tontineError != null ? 2 : 1,
                ),
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButtonFormField<Tontine>(
                  value: _selectedTontine,
                  decoration: const InputDecoration(
                    border: InputBorder.none,
                    hintText: 'Sélectionner une tontine',
                    prefixIcon: Icon(Icons.group),
                  ),
                  items: _tontines.map((tontine) {
                    return DropdownMenuItem<Tontine>(
                      value: tontine,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            tontine.name,
                            style: const TextStyle(fontWeight: FontWeight.w600),
                          ),
                          Text(
                            '${tontine.frequencyLabel ?? tontine.frequency} • Min: ${_formatAmount(tontine.minimumAmount)}',
                            style: AppTextStyles.bodySmall,
                          ),
                        ],
                      ),
                    );
                  }).toList(),
                  onChanged: (value) {
                    setState(() {
                      _selectedTontine = value;
                      if (_tontineError != null) {
                        _tontineError = null;
                      }
                      if (value != null) {
                        _amountController.text = value.minimumAmount.toString();
                      }
                    });
                  },
                ),
              ),
            ),
            if (_tontineError != null) ...[
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
                      _tontineError!,
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
              label: 'Montant choisi',
              controller: _amountController,
              hintText: 'Entrez le montant',
              keyboardType: const TextInputType.numberWithOptions(decimal: true),
              errorText: _amountError,
              enabled: _selectedTontine != null,
            ),
            if (_selectedTontine != null)
              Padding(
                padding: const EdgeInsets.only(top: AppSpacing.sm),
                child: Text(
                  'Minimum: ${_formatAmount(_selectedTontine!.minimumAmount)}',
                  style: AppTextStyles.bodySmall,
                ),
              ),
            const SizedBox(height: AppSpacing.xl),
            AppButton(
              text: 'Inscrire le membre',
              isLoading: _isSubmitting,
              onPressed: _submit,
              icon: Icons.person_add,
            ),
          ],
        ),
      ),
    );
  }
}
