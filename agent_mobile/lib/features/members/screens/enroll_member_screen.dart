import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/api/dio_client.dart';
import '../../../core/models/tontine.dart';

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

      // Vérifions la structure de la réponse
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
    if (!_formKey.currentState!.validate() || _selectedTontine == null) {
      return;
    }

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
          ),
        );
        context.pop(true);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(e.toString()),
            backgroundColor: AppColors.danger,
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

  String _formatAmount(double amount) {
    return '${NumberFormat('#,##0', 'fr_FR').format(amount)} FCFA';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Inscrire à une tontine'),
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
        padding: const EdgeInsets.all(48),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, size: 64, color: AppColors.danger),
            const SizedBox(height: 16),
            Text(
              _error!,
              textAlign: TextAlign.center,
              style: const TextStyle(color: AppColors.gray600),
            ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: _loadTontines,
              child: const Text('Réessayer'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildForm() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Membre : ${widget.memberName}',
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),
            DropdownButtonFormField<Tontine>(
              value: _selectedTontine,
              decoration: InputDecoration(
                labelText: 'Sélectionner une tontine',
                filled: true,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
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
                        style: TextStyle(
                          fontSize: 12,
                          color: AppColors.gray500,
                        ),
                      ),
                    ],
                  ),
                );
              }).toList(),
              onChanged: (value) {
                setState(() {
                  _selectedTontine = value;
                  if (value != null) {
                    _amountController.text = value.minimumAmount.toString();
                  }
                });
              },
              validator: (value) {
                if (value == null) {
                  return 'Veuillez sélectionner une tontine';
                }
                return null;
              },
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _amountController,
              decoration: InputDecoration(
                labelText: 'Montant choisi',
                suffixText: 'FCFA',
                filled: true,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                helperText: _selectedTontine != null
                    ? 'Minimum: ${_formatAmount(_selectedTontine!.minimumAmount)}'
                    : null,
              ),
              keyboardType: const TextInputType.numberWithOptions(decimal: true),
              enabled: _selectedTontine != null,
              validator: (value) {
                if (value == null || value.trim().isEmpty) {
                  return 'Veuillez entrer un montant';
                }
                final amount = double.tryParse(value.trim());
                if (amount == null) {
                  return 'Montant invalide';
                }
                if (_selectedTontine != null &&
                    amount < _selectedTontine!.minimumAmount) {
                  return 'Montant minimum: ${_formatAmount(_selectedTontine!.minimumAmount)}';
                }
                return null;
              },
            ),
            const SizedBox(height: 32),
            ElevatedButton(
              onPressed: _isSubmitting ? null : _submit,
              child: _isSubmitting
                  ? const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        valueColor: AlwaysStoppedAnimation<Color>(
                          Colors.white,
                        ),
                      ),
                    )
                  : const Text('Inscrire le membre'),
            ),
          ],
        ),
      ),
    );
  }
}
