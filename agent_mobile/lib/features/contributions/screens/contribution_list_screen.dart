import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:shimmer/shimmer.dart';
import 'package:intl/intl.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/api/dio_client.dart';
import '../../../core/models/contribution.dart';

class ContributionListScreen extends StatefulWidget {
  const ContributionListScreen({super.key});

  @override
  State<ContributionListScreen> createState() => _ContributionListScreenState();
}

class _ContributionListScreenState extends State<ContributionListScreen> {
  final DioClient _dioClient = DioClient();
  List<Contribution> _contributions = [];
  bool _isLoading = true;
  bool _isLoadingMore = false;
  int _currentPage = 1;
  bool _hasMore = true;
  String? _error;
  final _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _loadContributions();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >=
            _scrollController.position.maxScrollExtent * 0.9 &&
        !_isLoading &&
        !_isLoadingMore &&
        _hasMore) {
      _loadMore();
    }
  }

  Future<void> _loadContributions() async {
    setState(() {
      _isLoading = true;
      _error = null;
      _currentPage = 1;
    });

    try {
      final response = await _dioClient.dio.get(
        '/contributions',
        queryParameters: {'page': 1},
      );

      setState(() {
        _contributions = (response.data['data']['data'] as List<dynamic>)
            .map((json) => Contribution.fromJson(json))
            .toList();
        _hasMore = _contributions.length < (response.data['data']['total'] ?? 0);
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

  Future<void> _loadMore() async {
    setState(() {
      _isLoadingMore = true;
    });

    try {
      final nextPage = _currentPage + 1;
      final response = await _dioClient.dio.get(
        '/contributions',
        queryParameters: {'page': nextPage},
      );

      final newContributions = (response.data['data']['data'] as List<dynamic>)
          .map((json) => Contribution.fromJson(json))
          .toList();

      setState(() {
        _contributions.addAll(newContributions);
        _currentPage = nextPage;
        _hasMore = _contributions.length < (response.data['data']['total'] ?? 0);
      });
    } catch (e) {
      // Silently fail
    } finally {
      setState(() {
        _isLoadingMore = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Cotisations'),
      ),
      body: SafeArea(
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
        currentIndex: 2,
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
        ],
      ),
    );
  }

  Widget _buildLoading() {
    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
      itemCount: 8,
      itemBuilder: (context, index) {
        return Shimmer.fromColors(
          baseColor: AppColors.gray200,
          highlightColor: AppColors.gray100,
          child: Card(
            margin: const EdgeInsets.only(bottom: 8),
            child: const ListTile(),
          ),
        );
      },
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
              onPressed: _loadContributions,
              child: const Text('Réessayer'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildContent() {
    if (_contributions.isEmpty) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(48),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.receipt_long_outlined, size: 64, color: AppColors.gray300),
              SizedBox(height: 16),
              Text(
                'Aucune cotisation enregistrée',
                style: TextStyle(color: AppColors.gray500),
              ),
            ],
          ),
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadContributions,
      child: ListView.builder(
        controller: _scrollController,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
        itemCount: _contributions.length + (_isLoadingMore ? 1 : 0),
        itemBuilder: (context, index) {
          if (index == _contributions.length) {
            return const Padding(
              padding: EdgeInsets.symmetric(vertical: 16),
              child: Center(child: CircularProgressIndicator()),
            );
          }

          final contribution = _contributions[index];
          return Card(
            margin: const EdgeInsets.only(bottom: 8),
            child: ListTile(
              leading: CircleAvatar(
                backgroundColor: contribution.settlementStatus == 'pending'
                    ? AppColors.warning.withOpacity(0.1)
                    : AppColors.success.withOpacity(0.1),
                child: Icon(
                  contribution.settlementStatus == 'pending'
                      ? Icons.pending
                      : Icons.check_circle,
                  color: contribution.settlementStatus == 'pending'
                      ? AppColors.warning
                      : AppColors.success,
                ),
              ),
              title: Text(
                contribution.tontineParticipant?.member?.displayName ?? '',
              ),
              subtitle: Text(
                '${contribution.tontineParticipant?.tontine?.name ?? ''} • ${contribution.createdAt}',
              ),
              trailing: Text(
                _formatAmount(contribution.amount),
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  color: AppColors.primary,
                ),
              ),
              onTap: () => context.push('/contributions/${contribution.id}'),
            ),
          );
        },
      ),
    );
  }

  String _formatAmount(double amount) {
    return '${NumberFormat('#,##0', 'fr_FR').format(amount)} FCFA';
  }
}
