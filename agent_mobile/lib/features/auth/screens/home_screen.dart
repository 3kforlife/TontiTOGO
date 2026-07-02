import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/widgets/widgets.dart';

class Feature {
  final IconData icon;
  final String title;
  final String description;

  Feature({required this.icon, required this.title, required this.description});
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
    description:
        'Associez les membres aux tontines autorisées par votre organisation.',
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
    description:
        'Chaque collecte est enregistrée avec sa position géographique.',
  ),
  Feature(
    icon: Icons.sms,
    title: 'SMS automatiques',
    description:
        'Les membres reçoivent automatiquement une confirmation après chaque encaissement.',
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
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Top: Logo + Title
              Padding(
                padding: const EdgeInsets.symmetric(
                  horizontal: AppSpacing.lg,
                  vertical: AppSpacing.md,   // xl → md (réduit l'espace en haut)
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        // Logo
                        TontiTogoLogo(size: 80),
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
                                borderRadius: BorderRadius.circular(
                                  AppBorderRadius.sm,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                    const SizedBox(height: AppSpacing.lg),
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

              const SizedBox(height: AppSpacing.xs),  // lg → xs (espace avant le carrousel)
              SizedBox(
                height: 85,
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

              // Carousel indicators
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  ...List.generate(
                    features.length,
                    (index) => AnimatedContainer(
                      duration: const Duration(milliseconds: 400),
                      width: index == _currentPage ? 20 : 8,
                      height: 8,
                      margin: const EdgeInsets.symmetric(
                        horizontal: AppSpacing.xs,
                      ),
                      decoration: BoxDecoration(
                        color:
                            index == _currentPage
                                ? AppColors.primary
                                : AppColors.gray300,
                        borderRadius: BorderRadius.circular(4),
                      ),
                    ),
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Text(
                    '${_currentPage + 1} / ${features.length}',
                    style: AppTextStyles.bodySmall.copyWith(
                      color: AppColors.primary,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ],
              ),

              const SizedBox(height: AppSpacing.sm),  // md → sm

              // Background Image — hauteur réduite pour laisser le bouton visible
              Container(
                width: double.infinity,
                height: 240,   // 280 → 180
                decoration: const BoxDecoration(
                  image: DecorationImage(
                    image: AssetImage('assets/images/homescreen.png'),
                    fit: BoxFit.cover,
                  ),
                ),
              ),

              const SizedBox(height: AppSpacing.xl),  // xl → md

              // Login Button
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
                child: AppButton(
                  text: 'Se connecter',
                  onPressed: () => context.go('/login'),
                  icon: Icons.login,
                  height: 52,   // 56 → 52
                ),
              ),

              const SizedBox(height: AppSpacing.sm),  // sm → xs

              Text(
                'Accédez à votre espace agent',
                textAlign: TextAlign.center,
                style: AppTextStyles.bodyMedium.copyWith(
                  color: AppColors.gray500,
                ),
              ),

              const SizedBox(height: AppSpacing.md),  // xl → md
            ],
          ),
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
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.sm),
      child: AppCard(
        padding: const EdgeInsets.all(AppSpacing.sm),
        child: Row(
          children: [
            AppFeatureIcon(
              icon: feature.icon,
              size: 40,
              iconColor: AppColors.primary,
              backgroundColor: AppColors.primary100,
            ),
            const SizedBox(width: AppSpacing.sm),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    feature.title,
                    style: AppTextStyles.bodyLarge.copyWith(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 2),
                  Text(
                    feature.description,
                    style: AppTextStyles.bodySmall.copyWith(
                      fontSize: 10,
                      color: AppColors.gray500,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
            ),
            const SizedBox(width: AppSpacing.xs),
            Icon(Icons.arrow_forward_ios, color: AppColors.primary, size: 14),
          ],
        ),
      ),
    );
  }
}
