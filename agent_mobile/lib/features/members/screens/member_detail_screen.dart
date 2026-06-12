import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:shimmer/shimmer.dart';
import 'package:intl/intl.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/api/dio_client.dart';
import '../../../core/models/member.dart';
import '../../../core/models/tontine.dart';

class MemberDetailScreen extends StatefulWidget {
  final int memberId;

  const MemberDetailScreen({super.key, required this.memberId});

  @override
  State<MemberDetailScreen> createState() => _MemberDetailScreenState();
}

class _MemberDetailScreenState extends State<MemberDetailScreen> {
  final DioClient _dioClient = DioClient();
  Member? _member;
  List<Participant> _participations = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadMember();
  }

  Future<void> _loadMember() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final response = await _dioClient.dio.get(
        '/members/${widget.memberId}/tontines',
      );

      setState(() {
        _member = Member.fromJson(response.data['data']['member']);
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Détails du membre'),
      ),
      body: SafeArea(
        child: _isLoading
            ? _buildLoading()
            : _error != null
                ? _buildError()
                : _buildContent(),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push(
          '/contributions/add?memberId=${widget.memberId}',
        ),
        icon: const Icon(Icons.payment),
        label: const Text('Encaisser'),
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
          child: const Card(
            child: SizedBox(
              height: 120,
              width: double.infinity,
            ),
          ),
        ),
        const SizedBox(height: 24),
        Shimmer.fromColors(
          baseColor: AppColors.gray200,
          highlightColor: AppColors.gray100,
          child: Column(
            children: List.generate(3, (_) => const Card()),
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
              onPressed: _loadMember,
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
        _buildMemberCard(),
        const SizedBox(height: 24),
        _buildTontinesSection(),
      ],
    );
  }

  Widget _buildMemberCard() {
    return Card(
      elevation: 0,
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Row(
          children: [
            CircleAvatar(
              radius: 32,
              backgroundColor: AppColors.primaryLight,
              child: Text(
                _member!.displayName.substring(0, 1).toUpperCase(),
                style: const TextStyle(
                  fontSize: 28,
                  fontWeight: FontWeight.bold,
                  color: AppColors.primary,
                ),
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    _member!.displayName,
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: AppColors.gray900,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      const Icon(Icons.phone, size: 16, color: AppColors.gray500),
                      const SizedBox(width: 4),
                      Text(
                        _member!.phone,
                        style: const TextStyle(color: AppColors.gray600),
                      ),
                    ],
                  ),
                  if (_member!.memberCode != null) ...[
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        const Icon(Icons.badge, size: 16, color: AppColors.gray500),
                        const SizedBox(width: 4),
                        Text(
                          _member!.memberCode!,
                          style: const TextStyle(color: AppColors.gray600),
                        ),
                      ],
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTontinesSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Tontines actives',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: AppColors.gray800,
          ),
        ),
        const SizedBox(height: 16),
        if (_participations.isEmpty)
          const Center(
            child: Padding(
              padding: EdgeInsets.all(48),
              child: Column(
                children: [
                  Icon(Icons.wallet_outlined, size: 64, color: AppColors.gray300),
                  SizedBox(height: 16),
                  Text(
                    'Aucune tontine active',
                    style: TextStyle(color: AppColors.gray500),
                  ),
                ],
              ),
            ),
          )
        else
          ..._participations.map((participation) {
            return Card(
              margin: const EdgeInsets.only(bottom: 8),
              child: ListTile(
                title: Text(participation.tontine.name),
                subtitle: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Montant choisi: ${_formatAmount(participation.chosenAmount)}',
                    ),
                    const SizedBox(height: 4),
                    Chip(
                      label: Text(participation.tontine.frequencyLabel ?? ''),
                      backgroundColor: AppColors.primaryLight,
                      labelStyle: const TextStyle(
                        color: AppColors.primary,
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
                trailing: ElevatedButton.icon(
                  onPressed: () => context.push(
                    '/contributions/add?memberId=${widget.memberId}&participationId=${participation.participantId}',
                  ),
                  icon: const Icon(Icons.payment),
                  label: const Text('Encaisser'),
                ),
              ),
            );
          }).toList(),
      ],
    );
  }

  String _formatAmount(double amount) {
    return '${NumberFormat('#,##0', 'fr_FR').format(amount)} FCFA';
  }
}
