import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../../features/auth/screens/home_screen.dart';
import '../../features/auth/screens/login_screen.dart';
import '../../features/auth/screens/change_password_screen.dart';
import '../../features/auth/screens/forgot_password_screen.dart';
import '../../features/auth/screens/otp_screen.dart';
import '../../features/auth/screens/reset_password_screen.dart';
import '../../features/dashboard/screens/dashboard_screen.dart';
import '../../features/members/screens/member_search_screen.dart';
import '../../features/members/screens/add_member_screen.dart';
import '../../features/members/screens/member_detail_screen.dart';
import '../../features/members/screens/enroll_member_screen.dart';
import '../../features/contributions/screens/contribution_list_screen.dart';
import '../../features/contributions/screens/add_contribution_screen.dart';
import '../../features/contributions/screens/contribution_detail_screen.dart';
import '../../features/settings/screens/settings_screen.dart';

final router = GoRouter(
  initialLocation: '/',
  redirect: (context, state) {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final isLoggedIn = authProvider.isAuthenticated;
    final mustChangePassword = authProvider.mustChangePassword;

    final onHome = state.matchedLocation == '/';
    final loggingIn = state.matchedLocation == '/login';
    final changingPassword = state.matchedLocation == '/change-password';
    final onDashboard = state.matchedLocation == '/dashboard';
    final onForgotPassword = state.matchedLocation.startsWith('/forgot-password');

    if (!isLoggedIn) {
      return (onHome || loggingIn || onForgotPassword) ? null : '/';
    }

    if (mustChangePassword && !changingPassword) {
      return '/change-password';
    }

    if (!mustChangePassword && (onHome || loggingIn)) {
      return '/dashboard';
    }

    return null;
  },
  routes: [
    GoRoute(
      path: '/',
      builder: (context, state) => const HomeScreen(),
    ),
    GoRoute(
      path: '/login',
      builder: (context, state) => const LoginScreen(),
    ),
    GoRoute(
      path: '/change-password',
      builder: (context, state) => const ChangePasswordScreen(),
    ),
    GoRoute(
      path: '/forgot-password',
      builder: (context, state) => const ForgotPasswordScreen(),
    ),
    GoRoute(
      path: '/forgot-password/otp',
      builder: (context, state) => const OtpScreen(),
    ),
    GoRoute(
      path: '/forgot-password/reset',
      builder: (context, state) => const ResetPasswordScreen(),
    ),
    GoRoute(
      path: '/dashboard',
      builder: (context, state) => const DashboardScreen(),
    ),
    GoRoute(
      path: '/members/search',
      builder: (context, state) => const MemberSearchScreen(),
    ),
    GoRoute(
      path: '/members/add',
      builder: (context, state) => const AddMemberScreen(),
    ),
    GoRoute(
      path: '/members/:id',
      builder: (context, state) => MemberDetailScreen(
        memberId: int.parse(state.pathParameters['id']!),
      ),
    ),
    GoRoute(
      path: '/members/:id/enroll',
      builder: (context, state) {
        final memberId = int.parse(state.pathParameters['id']!);
        final memberName = state.uri.queryParameters['name'] ?? 'Membre';
        return EnrollMemberScreen(
          memberId: memberId,
          memberName: memberName,
        );
      },
    ),
    GoRoute(
      path: '/contributions',
      builder: (context, state) => const ContributionListScreen(),
    ),
    GoRoute(
      path: '/contributions/add',
      builder: (context, state) => const AddContributionScreen(),
    ),
    GoRoute(
      path: '/contributions/:id',
      builder: (context, state) => ContributionDetailScreen(
        contributionId: int.parse(state.pathParameters['id']!),
      ),
    ),
    GoRoute(
      path: '/settings',
      builder: (context, state) => const SettingsScreen(),
    ),
  ],
);
