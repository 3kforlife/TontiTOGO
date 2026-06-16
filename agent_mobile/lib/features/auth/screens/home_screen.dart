import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/widgets/widgets.dart';

class Feature {
  final IconData icon;
  final String title;
  final String description;

  Feature({
    required this.icon,
    required this.title,
    required this.description,
  });
}

final List<Feature> features = [
  Feature(
    icon: Icons.person_add,
    title: 'Enregistrer un membre',
    description: 'Ajoutez rapidement un nouveau membre depuis votre téléphone.',
  ),
  Feature(
    icon: Icons.account_balance,
    title: 'Inscrire à une tontine',
    description: 'Associez les membres aux tontines autorisées par votre organisation.',
  ),
  Feature(
    icon: Icons.savings,
    title: 'Encaisser une cotisation',
    description: 'Enregistrez les paiements en quelques secondes.',
  ),
  Feature(
    icon: Icons.history,
    title: 'Historique des cotisations',
    description: 'Consultez toutes vos opérations déjà effectuées.',
  ),
  Feature(
    icon: Icons.location_on,
    title: 'Traçabilité GPS',
    description: 'Chaque collecte est enregistrée avec sa position géographique.',
  ),
  Feature(
    icon: Icons.sms,
    title: 'SMS automatiques',
    description: 'Les membres reçoivent automatiquement une confirmation après chaque encaissement.',
  ),
];

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen>
    with SingleTickerProviderStateMixin {
  final PageController _pageController = PageController(initialPage: 0);
  int _currentPage = 0;

  @override
  void initState() {
    super.initState();

    // Auto-scroll every 4 seconds
    Future.doWhile(() async {
      await Future.delayed(const Duration(seconds: 4));
      if (mounted) {
        setState(() {
          _currentPage = (_currentPage + 1) % features.length;
        });
        _pageController.animateToPage(
          _currentPage,
          duration: const Duration(milliseconds: 600),
          curve: Curves.easeInOutCubic,
        );
      }
      return mounted;
    });
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.white,
      body: SafeArea(
        child: Stack(
          children: [
            // Scrollable content
            ListView(
              padding: EdgeInsets.only(
                bottom: 120, // Space for fixed button
              ),
              children: [
                // Top: Logo + Title
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.xl),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          // Logo
                          TontiTogoLogo(
                            size: 80,
                          ),
                          const SizedBox(width: AppSpacing.md),
                          // App Name
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.center,
                            children: [
                              RichText(
                                text: TextSpan(
                                  children: [
                                    TextSpan(
                                      text: 'Tonti',
                                      style: AppTextStyles.h1.copyWith(
                                        color: AppColors.gray900,
                                      ),
                                    ),
                                    TextSpan(
                                      text: 'TOGO',
                                      style: AppTextStyles.h1.copyWith(
                                        color: AppColors.primary,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(height: AppSpacing.xs),
                              // Underline
                              Container(
                                width: 40,
                                height: 4,
                                decoration: BoxDecoration(
                                  color: AppColors.primary,
                                  borderRadius:
                                      BorderRadius.circular(AppBorderRadius.sm),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                      const SizedBox(height: AppSpacing.md),
                      // Subtitle
                      Text(
                        'Application officielle des agents collecteurs',
                        textAlign: TextAlign.center,
                        style: AppTextStyles.bodyMedium.copyWith(
                          color: AppColors.gray500,
                        ),
                      ),
                    ],
                  ),
                ),

                // Middle: Background Image
                Container(
                  width: double.infinity,
                  height: 300,
                  child: Image.asset(
                    'assets/images/homescreen.png',
                    fit: BoxFit.cover,
                  ),
                ),

                // Bottom: Carousel, indicators, text
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(AppSpacing.lg),
                  decoration: BoxDecoration(
                    color: AppColors.white,
                    boxShadow: [
                      BoxShadow(
                        color: AppColors.gray900.withValues(alpha: 0.05),
                        blurRadius: 20,
                        offset: const Offset(0, -10),
                      ),
                    ],
                  ),
                  child: Column(
                    children: [
                      // Feature Card Carousel
                      SizedBox(
                        height: 140,
                        child: PageView.builder(
                          controller: _pageController,
                          onPageChanged: (index) {
                            setState(() {
                              _currentPage = index;
                            });
                          },
                          itemCount: features.length,
                          itemBuilder: (context, index) {
                            return _FeatureCard(feature: features[index]);
                          },
                        ),
                      ),
                      const SizedBox(height: AppSpacing.md),
                      // Indicators
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: List.generate(
                          features.length,
                          (index) => AnimatedContainer(
                            duration: const Duration(milliseconds: 400),
                            width: index == _currentPage ? 20 : 8,
                            height: 8,
                            margin:
                                const EdgeInsets.symmetric(horizontal: AppSpacing.xs),
                            decoration: BoxDecoration(
                              color: index == _currentPage
                                  ? AppColors.primary
                                  : AppColors.gray300,
                              borderRadius: BorderRadius.circular(4),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: AppSpacing.xl),
                      // Description
                      Padding(
                        padding:
                            const EdgeInsets.symmetric(horizontal: AppSpacing.sm),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Icon(
                              Icons.verified,
                              color: AppColors.primary,
                              size: 28,
                            ),
                            const SizedBox(width: AppSpacing.md),
                            Expanded(
                              child: Text(
                                'Collectez les cotisations, enregistrez les membres et consultez vos opérations où que vous soyez.',
                                style: AppTextStyles.bodyMedium.copyWith(
                                  color: AppColors.gray600,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),

            // Fixed "Se connecter" button
            Positioned(
              bottom: 0,
              left: 0,
              right: 0,
              child: Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: AppSpacing.lg,
                  vertical: AppSpacing.md,
                ),
                decoration: BoxDecoration(
                  color: AppColors.white,
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.gray900.withValues(alpha: 0.1),
                      blurRadius: 16,
                      offset: const Offset(0, -4),
                    ),
                  ],
                ),
                child: AppButton(
                  text: 'Se connecter',
                  onPressed: () => context.go('/login'),
                  icon: Icons.arrow_forward,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _FeatureCard extends StatelessWidget {
  final Feature feature;
  const _FeatureCard({required this.feature});

  @override
  Widget build(BuildContext context) {
    return AppCard(
      padding: const EdgeInsets.all(AppSpacing.lg),
      margin: const EdgeInsets.symmetric(horizontal: AppSpacing.sm),
      child: Row(
        children: [
          AppFeatureIcon(
            icon: feature.icon,
            size: 72,
            iconColor: AppColors.primary,
            backgroundColor: AppColors.primary100,
          ),
          const SizedBox(width: AppSpacing.md),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  feature.title,
                  style: AppTextStyles.h3,
                ),
                const SizedBox(height: AppSpacing.xs),
                Text(
                  feature.description,
                  style: AppTextStyles.bodyMedium.copyWith(
                    color: AppColors.gray500,
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
          const SizedBox(width: AppSpacing.md),
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: AppColors.primary100,
              shape: BoxShape.circle,
            ),
            child: const Icon(
              Icons.arrow_forward,
              color: AppColors.primary,
              size: 24,
            ),
          ),
        ],
      ),
    );
  }
}


