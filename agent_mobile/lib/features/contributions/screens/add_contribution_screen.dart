import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:geolocator/geolocator.dart';
import 'package:dio/dio.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/api/dio_client.dart';
import '../../../core/models/member.dart';
import '../../../core/models/tontine.dart';
import '../../../core/widgets/widgets.dart';
import 'package:shimmer/shimmer.dart';
import 'package:intl/intl.dart';

class AddContributionScreen extends StatefulWidget {
  const AddContributionScreen({super.key});

  @override
  State<AddContributionScreen> createState() => _AddContributionScreenState();
}

class _AddContributionScreenState extends State<AddContributionScreen> {
  final DioClient _dioClient = DioClient();
  final _formKey = GlobalKey<FormState>();
  final _searchController = TextEditingController();
  final _amountController = TextEditingController();

  Member? _selectedMember;
  Participant? _selectedParticipation;
  List<Member> _members = [];
  List<Participant> _participations = [];
  bool _isSearching = false;
  bool _isLoading = false;
  bool _isSubmitting = false;
  String? _error;
  bool _hasSearched = false;

  @override
  void initState() {
    super.initState();
    _searchController.addListener(_onSearchChanged);
  }

  @override
  void dispose() {
    _searchController.dispose();
    _amountController.dispose();
    super.dispose();
  }

  void _onSearchChanged() {
    if (_searchController.text.trim().length >= 2) {
      _searchMembers();
    } else {
      setState(() {
        _members = [];
        _hasSearched = false;
      });
    }
  }

  Future<void> _searchMembers() async {
    setState(() {
      _isSearching = true;
      _hasSearched = true;
      _error = null;
    });

    try {
      final response = await _dioClient.dio.get(
        '/members/search',
        queryParameters: {'query': _searchController.text.trim()},
      );

      setState(() {
        _members = (response.data['data'] as List<dynamic>)
            .map((json) => Member.fromJson(json))
            .toList();
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
      });
    } finally {
      setState(() {
        _isSearching = false;
      });
    }
  }

  Future<void> _selectMember(Member member) async {
    setState(() {
      _selectedMember = member;
      _selectedParticipation = null;
      _participations = [];
      _isLoading = true;
    });

    try {
      final response = await _dioClient.dio.get(
        '/members/${member.id}/tontines',
      );

      setState(() {
        _participations = (response.data['data']['participations'] as List<dynamic>)
            .map((json) => Participant.fromJson(json))
            .toList();
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _submit() async {
    if (_formKey.currentState!.validate()) {
      if (_selectedParticipation == null) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Veuillez sélectionner une tontine'),
            backgroundColor: AppColors.warning,
            behavior: SnackBarBehavior.floating,
            margin: EdgeInsets.fromLTRB(16, 0, 16, 16),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.all(Radius.circular(12)),
            ),
            elevation: 4,
          ),
        );
        return;
      }

      setState(() {
        _isSubmitting = true;
      });

      try {
        // Get location
        LocationPermission permission = await Geolocator.requestPermission();
        double? latitude;
        double? longitude;

        if (permission == LocationPermission.whileInUse ||
            permission == LocationPermission.always) {
          final position = await Geolocator.getCurrentPosition(
            locationSettings: const LocationSettings(
              accuracy: LocationAccuracy.high,
            ),
          );
          latitude = position.latitude;
          longitude = position.longitude;
        }

        final response = await _dioClient.dio.post(
          '/contributions',
          data: {
            'tontine_participant_id': _selectedParticipation!.participantId,
            'amount': double.parse(_amountController.text.trim()),
            'latitude': latitude,
            'longitude': longitude,
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
                      response.data['message'] ?? 'Cotisation enregistrée avec succès',
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
        String errorMessage = 'Erreur inconnue';
        if (e is DioException && e.response != null) {
          final errors = e.response!.data['errors'];
          if (errors != null) {
            errorMessage = errors.values.first.first;
          } else {
            errorMessage = e.response!.data['message'] ?? 'Erreur inconnue';
          }
        } else {
          errorMessage = 'Vérifiez votre connexion internet';
        }

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(errorMessage),
              backgroundColor: AppColors.danger,
              behavior: SnackBarBehavior.floating,
              margin: const EdgeInsets.fromLTRB(16, 0, 16, 16),
              shape: const RoundedRectangleBorder(
                borderRadius: BorderRadius.all(Radius.circular(12)),
              ),
              elevation: 4,
            ),
          );
        }
      } finally {
        setState(() {
          _isSubmitting = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.white,
      appBar: AppBar(
        title: const Text('Encaisser une cotisation'),
        centerTitle: false,
        elevation: 0,
      ),
      body: SafeArea(
        child: Form(
          key: _formKey,
          child: ListView(
            padding: const EdgeInsets.all(AppSpacing.lg),
            children: [
              // Member Search
              Text(
                'Membre',
                style: AppTextStyles.bodyLarge.copyWith(
                  color: AppColors.gray800,
                  fontWeight: FontWeight.w600,
                ),
              ),
              const SizedBox(height: AppSpacing.sm),
              if (_selectedMember == null) ...[
                SearchBar(
                  controller: _searchController,
                  hintText: 'Rechercher un membre',
                  leading: const Icon(Icons.search),
                  trailing: [
                    if (_searchController.text.isNotEmpty)
                      IconButton(
                        icon: const Icon(Icons.clear),
                        onPressed: () {
                          _searchController.clear();
                        },
                      ),
                  ],
                  elevation: WidgetStateProperty.all(1),
                  padding: WidgetStateProperty.all(
                    const EdgeInsets.symmetric(horizontal: AppSpacing.md),
                  ),
                ),
                const SizedBox(height: AppSpacing.md),
                if (_isSearching) _buildSearchLoading(),
                if (_hasSearched && !_isSearching && _members.isEmpty)
                  _buildSearchEmpty(),
                if (_hasSearched && !_isSearching && _members.isNotEmpty)
                  ..._members.map((member) {
                    return AppCard(
                      margin: const EdgeInsets.only(bottom: AppSpacing.sm),
                      padding: const EdgeInsets.symmetric(
                        vertical: AppSpacing.sm,
                        horizontal: AppSpacing.sm,
                      ),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: AppColors.primary100,
                          child: Text(
                            member.displayName.substring(0, 1).toUpperCase(),
                            style: const TextStyle(
                              color: AppColors.primary,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                        title: Text(member.displayName),
                        subtitle: Text(member.phone),
                        trailing: const Icon(Icons.chevron_right),
                        onTap: () => _selectMember(member),
                      ),
                    );
                  }).toList(),
              ] else ...[
                AppCard(
                  backgroundColor: AppColors.primary100,
                  padding: const EdgeInsets.symmetric(
                    vertical: AppSpacing.sm,
                    horizontal: AppSpacing.sm,
                  ),
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor: AppColors.primary,
                      child: Text(
                        _selectedMember!.displayName.substring(0, 1).toUpperCase(),
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                    title: Text(_selectedMember!.displayName),
                    subtitle: Text(_selectedMember!.phone),
                    trailing: IconButton(
                      icon: const Icon(Icons.close),
                      onPressed: () {
                        setState(() {
                          _selectedMember = null;
                          _selectedParticipation = null;
                          _participations = [];
                        });
                      },
                    ),
                  ),
                ),
                const SizedBox(height: AppSpacing.xl),

                // Tontine Selection
                Text(
                  'Tontine',
                  style: AppTextStyles.bodyLarge.copyWith(
                    color: AppColors.gray800,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: AppSpacing.sm),
                if (_isLoading) _buildParticipationsLoading(),
                if (!_isLoading && _participations.isEmpty)
                  Center(
                    child: Padding(
                      padding: const EdgeInsets.all(AppSpacing.xl),
                      child: Column(
                        children: [
                          const Icon(Icons.wallet_outlined,
                              size: 64, color: AppColors.gray300),
                          const SizedBox(height: AppSpacing.md),
                          Text(
                            'Aucune tontine active',
                            style: AppTextStyles.bodyMedium.copyWith(
                              color: AppColors.gray500,
                            ),
                          ),
                          const SizedBox(height: AppSpacing.xl),
                          AppButton(
                            onPressed: () async {
                              final result = await context.push<bool?>(
                                '/members/${_selectedMember!.id}/enroll?name=${Uri.encodeComponent(_selectedMember!.displayName)}',
                              );
                              if (result == true && mounted) {
                                _selectMember(_selectedMember!);
                              }
                            },
                            text: 'Inscrire à une tontine',
                            icon: Icons.add_card,
                          ),
                        ],
                      ),
                    ),
                  ),
                if (!_isLoading && _participations.isNotEmpty)
                  ..._participations.map((participation) {
                    final isSelected =
                        _selectedParticipation?.participantId == participation.participantId;
                    return AppCard(
                      margin: const EdgeInsets.only(bottom: AppSpacing.sm),
                      padding: const EdgeInsets.symmetric(
                        vertical: AppSpacing.sm,
                        horizontal: AppSpacing.sm,
                      ),
                      backgroundColor: isSelected
                          ? AppColors.primary100
                          : null,
                      child: ListTile(
                        title: Text(participation.tontine.name),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Min: ${_formatAmount(participation.minimumAmount)} • Choisi: ${_formatAmount(participation.chosenAmount)}',
                            ),
                            Chip(
                              label: Text(
                                participation.tontine.frequencyLabel ?? '',
                              ),
                              backgroundColor: AppColors.primary100,
                              labelStyle: AppTextStyles.bodySmall.copyWith(
                                color: AppColors.primary,
                              ),
                            ),
                          ],
                        ),
                        trailing: isSelected
                            ? const Icon(Icons.check_circle, color: AppColors.success)
                            : null,
                        onTap: () {
                          setState(() {
                            _selectedParticipation = participation;
                            _amountController.text =
                                participation.chosenAmount.toStringAsFixed(0);
                          });
                        },
                      ),
                    );
                  }).toList(),
                const SizedBox(height: AppSpacing.xl),

                // Amount
                AppTextField(
                  controller: _amountController,
                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  label: 'Montant',
                  prefixIcon: Icons.money,
                  suffixText: 'FCFA',
                  validator: (value) {
                    if (value == null || value.trim().isEmpty) {
                      return 'Veuillez entrer le montant';
                    }
                    final amount = double.tryParse(value.trim());
                    if (amount == null || amount <= 0) {
                      return 'Montant invalide';
                    }
                    if (_selectedParticipation != null && amount < _selectedParticipation!.chosenAmount) {
                      return 'Montant minimum: ${_formatAmount(_selectedParticipation!.chosenAmount)}';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: AppSpacing.xl),

                // Submit Button
                AppButton(
                  onPressed: _isSubmitting ? null : _submit,
                  text: 'Enregistrer la cotisation',
                  isLoading: _isSubmitting,
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSearchLoading() {
    return Column(
      children: List.generate(3, (_) {
        return Shimmer.fromColors(
          baseColor: AppColors.gray200,
          highlightColor: AppColors.gray100,
          child: Card(
            margin: const EdgeInsets.only(bottom: 8),
            child: const ListTile(),
          ),
        );
      }),
    );
  }

  Widget _buildSearchEmpty() {
    return const Center(
      child: Padding(
        padding: EdgeInsets.all(48),
        child: Column(
          children: [
            Icon(Icons.person_off_outlined, size: 64, color: AppColors.gray300),
            SizedBox(height: 16),
            Text(
              'Aucun membre trouvé',
              style: TextStyle(color: AppColors.gray500),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildParticipationsLoading() {
    return Column(
      children: List.generate(3, (_) {
        return Shimmer.fromColors(
          baseColor: AppColors.gray200,
          highlightColor: AppColors.gray100,
          child: Card(
            margin: const EdgeInsets.only(bottom: 8),
            child: const ListTile(),
          ),
        );
      }),
    );
  }

  String _formatAmount(double amount) {
    return '${NumberFormat('#,##0', 'fr_FR').format(amount)} FCFA';
  }
}
