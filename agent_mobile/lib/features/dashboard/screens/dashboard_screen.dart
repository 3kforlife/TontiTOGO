import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import 'package:shimmer/shimmer.dart';
import 'package:intl/intl.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/api/dio_client.dart';
import '../../../core/widgets/widgets.dart';

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
    final user = authProvider.user;

    return Scaffold(
      backgroundColor: AppColors.white,
      body: RefreshIndicator(
        onRefresh: _loadDashboard,
        color: AppColors.primary,
        child: _isLoading
            ? _buildLoading()
            : _error != null
                ? _buildError()
                : _buildContent(user),
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
      floatingActionButtonLocation: FloatingActionButtonLocation.endFloat,
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: const BorderRadius.only(
            topLeft: Radius.circular(24),
            topRight: Radius.circular(24),
          ),
          boxShadow: [
            BoxShadow(
              color: AppColors.gray900.withValues(alpha: 0.08),
              blurRadius: 20,
              offset: const Offset(0, -4),
            ),
          ],
        ),
        child: BottomNavigationBar(
          type: BottomNavigationBarType.fixed,
          currentIndex: 0,
          backgroundColor: Colors.transparent,
          elevation: 0,
          selectedItemColor: AppColors.primary,
          unselectedItemColor: AppColors.gray400,
          selectedLabelStyle: const TextStyle(
            fontWeight: FontWeight.w700,
            fontSize: 12,
          ),
          unselectedLabelStyle: const TextStyle(
            fontWeight: FontWeight.w500,
            fontSize: 11,
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
          items: [
            BottomNavigationBarItem(
              icon: _buildNavIcon(0, Icons.grid_view, Icons.grid_view_outlined),
              label: 'Tableau de bord',
            ),
            BottomNavigationBarItem(
              icon: _buildNavIcon(1, Icons.people, Icons.people_outlined),
              label: 'Membres',
            ),
            BottomNavigationBarItem(
              icon: _buildNavIcon(2, Icons.receipt_long, Icons.receipt_long_outlined),
              label: 'Cotisations',
            ),
            BottomNavigationBarItem(
              icon: _buildNavIcon(3, Icons.settings, Icons.settings_outlined),
              label: 'Paramètres',
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildNavIcon(int index, IconData activeIcon, IconData inactiveIcon) {
    final isActive = index == 0;
    return Container(
      padding: const EdgeInsets.all(8),
      decoration: BoxDecoration(
        color: isActive ? AppColors.primary100 : Colors.transparent,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Icon(
        isActive ? activeIcon : inactiveIcon,
        color: isActive ? AppColors.primary : AppColors.gray400,
      ),
    );
  }

  Widget _buildLoading() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Shimmer.fromColors(
            baseColor: AppColors.gray200,
            highlightColor: AppColors.gray100,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 100,
                  height: 24,
                  decoration: BoxDecoration(
                    color: AppColors.white,
                    borderRadius: BorderRadius.circular(AppBorderRadius.sm),
                  ),
                ),
                const SizedBox(height: AppSpacing.sm),
                Container(
                  width: 200,
                  height: 32,
                  decoration: BoxDecoration(
                    color: AppColors.white,
                    borderRadius: BorderRadius.circular(AppBorderRadius.sm),
                  ),
                ),
                const SizedBox(height: AppSpacing.sm),
                Container(
                  width: 150,
                  height: 20,
                  decoration: BoxDecoration(
                    color: AppColors.white,
                    borderRadius: BorderRadius.circular(AppBorderRadius.sm),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: AppSpacing.xl),
          Shimmer.fromColors(
            baseColor: AppColors.gray200,
            highlightColor: AppColors.gray100,
            child: GridView.count(
              shrinkWrap: true,
              crossAxisCount: 2,
              mainAxisSpacing: AppSpacing.md,
              crossAxisSpacing: AppSpacing.md,
              children: List.generate(
                4,
                (_) => Container(
                  height: 180,
                  decoration: BoxDecoration(
                    color: AppColors.white,
                    borderRadius: BorderRadius.circular(AppBorderRadius.xxl),
                  ),
                ),
              ),
            ),
          ),
          const SizedBox(height: AppSpacing.xl),
          Shimmer.fromColors(
            baseColor: AppColors.gray200,
            highlightColor: AppColors.gray100,
            child: Column(
              children: List.generate(
                5,
                (_) => Container(
                  height: 70,
                  margin: const EdgeInsets.only(bottom: AppSpacing.sm),
                  decoration: BoxDecoration(
                    color: AppColors.white,
                    borderRadius: BorderRadius.circular(AppBorderRadius.lg),
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppSpacing.lg),
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
              onPressed: _loadDashboard,
              icon: Icons.refresh,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildContent(dynamic user) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Header section
          _buildHeader(user),
          const SizedBox(height: AppSpacing.xl),
          // Stats grid
          _buildStatsGrid(),
          const SizedBox(height: AppSpacing.xl),
          // Recent contributions
          _buildRecentContributions(),
          const SizedBox(height: AppSpacing.xl * 2),
        ],
      ),
    );
  }

  Widget _buildRecentContributions() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Dernières cotisations',
          style: AppTextStyles.h3,
        ),
        const SizedBox(height: AppSpacing.md),
        if (_recentContributions!.isEmpty)
          Center(
            child: Padding(
              padding: const EdgeInsets.all(AppSpacing.xl),
              child: Column(
                children: [
                  Icon(
                    Icons.receipt_long_outlined,
                    size: 80,
                    color: AppColors.gray300,
                  ),
                  const SizedBox(height: AppSpacing.md),
                  Text(
                    'Aucune cotisation enregistrée',
                    style: AppTextStyles.bodyMedium,
                  ),
                ],
              ),
            ),
          )
        else
          AppCard(
            padding: const EdgeInsets.symmetric(vertical: AppSpacing.sm),
            child: Column(
              children: _recentContributions!.map((contribution) {
                return TransactionCard(
                  title: contribution['member'] ?? '',
                  subtitle: contribution['tontine'] ?? '',
                  amount: _formatAmount(contribution['amount']),
                  date: contribution['date'] ?? '',
                  type: TransactionType.deposit,
                  onTap: () {},
                );
              }).toList(),
            ),
          ),
      ],
    );
  }

  Widget _buildHeader(dynamic user) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              'Tableau de bord',
              style: AppTextStyles.h3,
            ),
            Container(
              width: 48,
              height: 48,
              decoration: BoxDecoration(
                color: AppColors.white,
                shape: BoxShape.circle,
                boxShadow: [
                  BoxShadow(
                    color: AppColors.gray900.withValues(alpha: 0.1),
                    blurRadius: 12,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: const Icon(
                Icons.account_circle_outlined,
                color: AppColors.primary,
              ),
            ),
          ],
        ),
        const SizedBox(height: AppSpacing.lg),
        Text(
          '👋 Bonjour ${user?.firstName ?? ''}',
          style: AppTextStyles.h2.copyWith(
            color: AppColors.gray900,
          ),
        ),
        const SizedBox(height: AppSpacing.sm),
        Text(
          'Bienvenue dans votre espace de collecte',
          style: AppTextStyles.bodyLarge.copyWith(
            color: AppColors.gray500,
          ),
        ),
        const SizedBox(height: AppSpacing.md),
        if (user?.organization?.name != null)
          Container(
            padding: const EdgeInsets.symmetric(
              horizontal: AppSpacing.md,
              vertical: AppSpacing.sm,
            ),
            decoration: BoxDecoration(
              color: AppColors.primary100,
              borderRadius: BorderRadius.circular(AppBorderRadius.xl),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(
                  Icons.group,
                  color: AppColors.primary,
                  size: 20,
                ),
                const SizedBox(width: AppSpacing.sm),
                Text(
                  user?.organization?.name ?? '',
                  style: AppTextStyles.bodyMedium.copyWith(
                    color: AppColors.primary,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
      ],
    );
  }

  Widget _buildStatsGrid() {
    return GridView.count(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisCount: 2,
      mainAxisSpacing: AppSpacing.md,
      crossAxisSpacing: AppSpacing.md,
      childAspectRatio: 0.95,   // < 1 = plus haut que large, donne plus de place au contenu
      children: [
        AppStatCard(
          title: 'Aujourd\'hui',
          value: _formatAmount(_stats?['today_amount']),
          icon: Icons.calendar_month_outlined,
          variant: StatCardVariant.primary,
          trailingIcon: Icons.trending_up,
        ),
        AppStatCard(
          title: 'En attente',
          value: _formatAmount(_stats?['pending_amount']),
          icon: Icons.more_horiz,
          variant: StatCardVariant.warning,
          trailingIcon: Icons.schedule,
        ),
        AppStatCard(
          title: 'Membres recrutés',
          value: (_stats?['total_members'] ?? 0).toString(),
          icon: Icons.person_add_outlined,
          variant: StatCardVariant.info,
          trailingIcon: Icons.group_outlined,
        ),
        AppStatCard(
          title: 'Inscriptions',
          value: (_stats?['total_enrollments'] ?? 0).toString(),
          icon: Icons.person_add_alt_1,
          variant: StatCardVariant.success,
          trailingIcon: Icons.article_outlined,
        ),
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
