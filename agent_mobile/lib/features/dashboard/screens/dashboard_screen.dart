import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/api/dio_client.dart';
import '../../../core/models/contribution.dart';
import 'package:shimmer/shimmer.dart';
import 'package:intl/intl.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final DioClient _dioClient = DioClient();
  bool _isLoading = true;
  Map<String, dynamic>? _stats;
  List<Map<String, dynamic>>? _recentContributions;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadDashboard();
  }

  Future<void> _loadDashboard() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final response = await _dioClient.dio.get('/dashboard');
      setState(() {
        _stats = response.data['data']['stats'];
        _recentContributions = List<Map<String, dynamic>>.from(
          response.data['data']['recent_contributions'] ?? [],
        );
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
    final authProvider = Provider.of<AuthProvider>(context);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Tableau de bord'),
      ),
      body: RefreshIndicator(
        onRefresh: _loadDashboard,
        child: _isLoading
            ? _buildLoading()
            : _error != null
                ? _buildError()
                : _buildContent(),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push('/contributions/add'),
        icon: const Icon(Icons.add),
        label: const Text('Encaisser'),
      ),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: 0,
        onTap: (index) {
          switch (index) {
            case 0:
              context.go('/');
              break;
            case 1:
              context.go('/members/search');
              break;
            case 2:
              context.go('/contributions');
              break;
            case 3:
              context.go('/settings');
              break;
          }
        },
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.dashboard),
            label: 'Accueil',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.people),
            label: 'Membres',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.receipt_long),
            label: 'Cotisations',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.settings),
            label: 'Paramètres',
          ),
        ],
      ),
    );
  }

  Widget _buildLoading() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Shimmer.fromColors(
            baseColor: AppColors.gray200,
            highlightColor: AppColors.gray100,
            child: GridView.count(
              shrinkWrap: true,
              crossAxisCount: 2,
              mainAxisSpacing: 16,
              crossAxisSpacing: 16,
              children: List.generate(4, (_) => const Card()),
            ),
          ),
          const SizedBox(height: 24),
          Shimmer.fromColors(
            baseColor: AppColors.gray200,
            highlightColor: AppColors.gray100,
            child: Column(
              children: List.generate(5, (_) => const Card()),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
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
              onPressed: _loadDashboard,
              child: const Text('Réessayer'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildContent() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          _buildStatsGrid(),
          const SizedBox(height: 24),
          _buildRecentContributions(),
        ],
      ),
    );
  }

  Widget _buildStatsGrid() {
    final items = [
      {
        'label': 'Aujourd\'hui',
        'value': _formatAmount(_stats?['today_amount']),
        'color': AppColors.primary,
        'icon': Icons.calendar_today,
      },
      {
        'label': 'Cette semaine',
        'value': _formatAmount(_stats?['week_amount']),
        'color': AppColors.secondary,
        'icon': Icons.calendar_view_week,
      },
      {
        'label': 'Ce mois',
        'value': _formatAmount(_stats?['month_amount']),
        'color': AppColors.accent,
        'icon': Icons.calendar_month,
      },
      {
        'label': 'En attente',
        'value': _formatAmount(_stats?['pending_amount']),
        'color': AppColors.warning,
        'icon': Icons.pending,
      },
    ];

    return GridView.count(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisCount: 2,
      mainAxisSpacing: 16,
      crossAxisSpacing: 16,
      children: items.map((item) => _buildStatCard(item)).toList(),
    );
  }

  Widget _buildStatCard(Map<String, dynamic> item) {
    return Card(
      elevation: 0,
      color: (item['color'] as Color).withOpacity(0.1),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(
              item['icon'] as IconData,
              size: 32,
              color: item['color'] as Color,
            ),
            const SizedBox(height: 8),
            Text(
              item['value'] as String,
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: AppColors.gray800,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              item['label'] as String,
              style: const TextStyle(
                fontSize: 12,
                color: AppColors.gray500,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRecentContributions() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Dernières cotisations',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: AppColors.gray800,
          ),
        ),
        const SizedBox(height: 16),
        if (_recentContributions!.isEmpty)
          const Center(
            child: Padding(
              padding: EdgeInsets.all(48),
              child: Column(
                children: [
                  Icon(Icons.receipt_long_outlined,
                      size: 64, color: AppColors.gray300),
                  SizedBox(height: 16),
                  Text(
                    'Aucune cotisation enregistrée',
                    style: TextStyle(color: AppColors.gray500),
                  ),
                ],
              ),
            ),
          )
        else
          ..._recentContributions!.map((contribution) {
            return Card(
              margin: const EdgeInsets.only(bottom: 8),
              child: ListTile(
                leading: CircleAvatar(
                  backgroundColor: AppColors.primaryLight,
                  child: Text(
                    (contribution['member'] ?? '').substring(0, 1).toUpperCase(),
                    style: const TextStyle(
                      color: AppColors.primary,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                title: Text(contribution['member'] ?? ''),
                subtitle: Text(contribution['tontine'] ?? ''),
                trailing: Text(
                  _formatAmount(contribution['amount']),
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    color: AppColors.primary,
                  ),
                ),
              ),
            );
          }).toList(),
      ],
    );
  }

  String _formatAmount(dynamic amount) {
    try {
      final value = double.tryParse(amount.toString()) ?? 0;
      return '${NumberFormat('#,##0', 'fr_FR').format(value)} FCFA';
    } catch (e) {
      return '0 FCFA';
    }
  }
}
