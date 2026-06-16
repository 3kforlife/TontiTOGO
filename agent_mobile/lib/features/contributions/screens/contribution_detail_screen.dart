import 'package:flutter/material.dart';
import 'package:shimmer/shimmer.dart';
import 'package:intl/intl.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/api/dio_client.dart';
import '../../../core/models/contribution.dart';

class ContributionDetailScreen extends StatefulWidget {
  final int contributionId;

  const ContributionDetailScreen({super.key, required this.contributionId});

  @override
  State<ContributionDetailScreen> createState() => _ContributionDetailScreenState();
}

class _ContributionDetailScreenState extends State<ContributionDetailScreen> {
  final DioClient _dioClient = DioClient();
  Contribution? _contribution;
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadContribution();
  }

  Future<void> _loadContribution() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final response = await _dioClient.dio.get(
        '/contributions/${widget.contributionId}',
      );

      setState(() {
        _contribution = Contribution.fromJson(response.data['data']);
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Détails de la cotisation'),
      ),
      body: SafeArea(
        child: _isLoading
            ? _buildLoading()
            : _error != null
                ? _buildError()
                : _buildContent(),
      ),
    );
  }

  Widget _buildLoading() {
    return ListView(
      padding: const EdgeInsets.all(24),
      children: [
        Shimmer.fromColors(
          baseColor: AppColors.gray200,
          highlightColor: AppColors.gray100,
          child: Card(
            child: Container(
              height: 400,
              width: double.infinity,
            ),
          ),
        ),
      ],
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
              onPressed: _loadContribution,
              child: const Text('Réessayer'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildContent() {
    return ListView(
      padding: const EdgeInsets.all(24),
      children: [
        Card(
          color: _contribution!.settlementStatus == 'pending'
              ? AppColors.warning.withOpacity(0.1)
              : AppColors.success.withOpacity(0.1),
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              children: [
                Icon(
                  _contribution!.settlementStatus == 'pending'
                      ? Icons.pending
                      : Icons.check_circle,
                  size: 64,
                  color: _contribution!.settlementStatus == 'pending'
                      ? AppColors.warning
                      : AppColors.success,
                ),
                const SizedBox(height: 16),
                Text(
                  _formatAmount(_contribution!.amount),
                  style: const TextStyle(
                    fontSize: 32,
                    fontWeight: FontWeight.bold,
                    color: AppColors.gray900,
                  ),
                ),
                const SizedBox(height: 8),
                Chip(
                  label: Text(
                    _contribution!.settlementStatus == 'pending'
                        ? 'En attente'
                        : 'Réglée',
                  ),
                  backgroundColor: _contribution!.settlementStatus == 'pending'
                      ? AppColors.warning.withOpacity(0.2)
                      : AppColors.success.withOpacity(0.2),
                  labelStyle: TextStyle(
                    color: _contribution!.settlementStatus == 'pending'
                        ? AppColors.warning
                        : AppColors.success,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 24),
        Card(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Informations',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: AppColors.gray800,
                  ),
                ),
                const SizedBox(height: 16),
                _buildInfoRow(
                  icon: Icons.receipt,
                  label: 'Référence',
                  value: _contribution!.reference,
                ),
                const SizedBox(height: 12),
                _buildInfoRow(
                  icon: Icons.person,
                  label: 'Membre',
                  value:
                      _contribution!.displayMember?.displayName ?? '',
                ),
                const SizedBox(height: 12),
                _buildInfoRow(
                  icon: Icons.wallet,
                  label: 'Tontine',
                  value:
                      _contribution!.displayTontine?.name ?? '',
                ),
                const SizedBox(height: 12),
                _buildInfoRow(
                  icon: Icons.calendar_today,
                  label: 'Date',
                  value: _contribution!.createdAt ?? '',
                ),
                if (_contribution!.latitude != null &&
                    _contribution!.longitude != null) ...[
                  const SizedBox(height: 12),
                  _buildInfoRow(
                    icon: Icons.location_on,
                    label: 'Localisation',
                    value:
                        '${_contribution!.latitude!.toStringAsFixed(4)}, ${_contribution!.longitude!.toStringAsFixed(4)}',
                  ),
                ],
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildInfoRow({
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Row(
      children: [
        Icon(icon, size: 20, color: AppColors.gray500),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: const TextStyle(
                  fontSize: 12,
                  color: AppColors.gray500,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                value,
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                  color: AppColors.gray800,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  String _formatAmount(double amount) {
    return '${NumberFormat('#,##0', 'fr_FR').format(amount)} FCFA';
  }
}
