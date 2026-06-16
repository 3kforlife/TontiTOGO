import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:shimmer/shimmer.dart';
import 'package:intl/intl.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/api/dio_client.dart';
import '../../../core/models/contribution.dart';
import '../../../core/widgets/widgets.dart';

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
      backgroundColor: AppColors.white,
      appBar: const TontiAppBar(
        title: 'Cotisations',
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
        backgroundColor: AppColors.primary,
        elevation: 4,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppBorderRadius.xl),
        ),
        icon: const Icon(
          Icons.add,
          color: AppColors.white,
          size: 32,
        ),
        label: Text(
          'Encaisser',
          style: AppTextStyles.bodyLarge.copyWith(
            color: AppColors.white,
            fontWeight: FontWeight.w700,
          ),
        ),
      ),

      bottomNavigationBar: BottomNavigationBar(
        type: BottomNavigationBarType.fixed,
        currentIndex: 2,
        backgroundColor: AppColors.white,
        elevation: 8,
        selectedItemColor: AppColors.primary,
        unselectedItemColor: AppColors.gray400,
        selectedLabelStyle: const TextStyle(
          fontWeight: FontWeight.w700,
        ),
        onTap: (index) {
          switch (index) {
            case 0:
              context.go('/dashboard');
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
            icon: Icon(Icons.grid_view_outlined),
            activeIcon: Icon(Icons.grid_view),
            label: 'Tableau de bord',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.people_outlined),
            activeIcon: Icon(Icons.people),
            label: 'Membres',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.receipt_long_outlined),
            activeIcon: Icon(Icons.receipt_long),
            label: 'Cotisations',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.settings_outlined),
            activeIcon: Icon(Icons.settings),
            label: 'Paramètres',
          ),
        ],
      ),
    );
  }

  Widget _buildLoading() {
    return ListView.builder(
      padding: const EdgeInsets.symmetric(
        horizontal: AppSpacing.lg,
        vertical: AppSpacing.xl,
      ),
      itemCount: 8,
      itemBuilder: (context, index) {
        return Shimmer.fromColors(
          baseColor: AppColors.gray200,
          highlightColor: AppColors.gray100,
          child: Container(
            margin: const EdgeInsets.only(bottom: AppSpacing.sm),
            height: 70,
            decoration: BoxDecoration(
              color: AppColors.white,
              borderRadius: BorderRadius.circular(AppBorderRadius.lg),
            ),
          ),
        );
      },
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
              onPressed: _loadContributions,
              icon: Icons.refresh,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildContent() {
    if (_contributions.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(AppSpacing.xl),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.receipt_long_outlined,
                size: 80,
                color: AppColors.gray300,
              ),
              const SizedBox(height: AppSpacing.lg),
              Text(
                'Aucune cotisation enregistrée',
                style: AppTextStyles.bodyMedium,
              ),
            ],
          ),
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadContributions,
      color: AppColors.primary,
      child: AppCard(
        padding: const EdgeInsets.symmetric(vertical: AppSpacing.sm),
        child: ListView.builder(
          controller: _scrollController,
          padding: const EdgeInsets.symmetric(
            horizontal: AppSpacing.sm,
          ),
          itemCount: _contributions.length + (_isLoadingMore ? 1 : 0),
          itemBuilder: (context, index) {
            if (index == _contributions.length) {
              return const Padding(
                padding: EdgeInsets.symmetric(vertical: AppSpacing.md),
                child: Center(child: CircularProgressIndicator()),
              );
            }

            final contribution = _contributions[index];
            return TransactionCard(
              title: contribution.displayMember?.displayName ?? '',
              subtitle:
                  '${contribution.displayTontine?.name ?? ''} • ${contribution.createdAt}',
              amount: _formatAmount(contribution.amount),
              date: '',
              type: contribution.settlementStatus == 'pending'
                  ? TransactionType.transfer
                  : TransactionType.deposit,
              onTap: () => context.push('/contributions/${contribution.id}'),
            );
          },
        ),
      ),
    );
  }

  String _formatAmount(double amount) {
    return '${NumberFormat('#,##0', 'fr_FR').format(amount)} FCFA';
  }
}
