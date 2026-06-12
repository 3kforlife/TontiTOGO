import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:geolocator/geolocator.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:dio/dio.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/api/dio_client.dart';
import '../../../core/models/member.dart';
import '../../../core/models/tontine.dart';
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
        Fluttertoast.showToast(
          msg: 'Veuillez sélectionner une tontine',
          backgroundColor: AppColors.warning,
          textColor: Colors.white,
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
          Fluttertoast.showToast(
            msg: response.data['message'] ?? 'Cotisation enregistrée avec succès',
            toastLength: Toast.LENGTH_LONG,
            gravity: ToastGravity.BOTTOM,
            backgroundColor: AppColors.success,
            textColor: Colors.white,
          );
          context.pop();
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

        Fluttertoast.showToast(
          msg: errorMessage,
          backgroundColor: AppColors.danger,
          textColor: Colors.white,
        );
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
      appBar: AppBar(
        title: const Text('Encaisser une cotisation'),
      ),
      body: SafeArea(
        child: Form(
          key: _formKey,
          child: ListView(
            padding: const EdgeInsets.all(24),
            children: [
              // Member Search
              const Text(
                'Membre',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                  color: AppColors.gray800,
                ),
              ),
              const SizedBox(height: 8),
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
                  padding:
                      WidgetStateProperty.all(const EdgeInsets.symmetric(horizontal: 16)),
                ),
                const SizedBox(height: 16),
                if (_isSearching) _buildSearchLoading(),
                if (_hasSearched && !_isSearching && _members.isEmpty)
                  _buildSearchEmpty(),
                if (_hasSearched && !_isSearching && _members.isNotEmpty)
                  ..._members.map((member) {
                    return Card(
                      margin: const EdgeInsets.only(bottom: 8),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: AppColors.primaryLight,
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
                Card(
                  color: AppColors.primaryLight,
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
                const SizedBox(height: 24),

                // Tontine Selection
                const Text(
                  'Tontine',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: AppColors.gray800,
                  ),
                ),
                const SizedBox(height: 8),
                if (_isLoading) _buildParticipationsLoading(),
                if (!_isLoading && _participations.isEmpty)
                  const Center(
                    child: Padding(
                      padding: EdgeInsets.all(48),
                      child: Column(
                        children: [
                          Icon(Icons.wallet_outlined,
                              size: 64, color: AppColors.gray300),
                          SizedBox(height: 16),
                          Text(
                            'Aucune tontine active',
                            style: TextStyle(color: AppColors.gray500),
                          ),
                        ],
                      ),
                    ),
                  ),
                if (!_isLoading && _participations.isNotEmpty)
                  ..._participations.map((participation) {
                    final isSelected =
                        _selectedParticipation?.participantId == participation.participantId;
                    return Card(
                      margin: const EdgeInsets.only(bottom: 8),
                      color: isSelected
                          ? AppColors.primaryLight.withOpacity(0.5)
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
                              backgroundColor: AppColors.primaryLight,
                              labelStyle: const TextStyle(
                                color: AppColors.primary,
                                fontSize: 12,
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
                const SizedBox(height: 24),

                // Amount
                TextFormField(
                  controller: _amountController,
                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  decoration: const InputDecoration(
                    labelText: 'Montant',
                    prefixIcon: Icon(Icons.money),
                    suffixText: 'FCFA',
                  ),
                  validator: (value) {
                    if (value == null || value.trim().isEmpty) {
                      return 'Veuillez entrer le montant';
                    }
                    final amount = double.tryParse(value.trim());
                    if (amount == null || amount <= 0) {
                      return 'Montant invalide';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 24),

                // Submit Button
                ElevatedButton(
                  onPressed: _isSubmitting ? null : _submit,
                  child: _isSubmitting
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            valueColor: AlwaysStoppedAnimation<Color>(
                              AppColors.white,
                            ),
                          ),
                        )
                      : const Text(
                          'Enregistrer la cotisation',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
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
