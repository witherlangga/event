import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'providers/auth_provider.dart';
import 'screens/login_screen.dart';
import 'screens/main_shell_screen.dart';
import 'screens/register_screen.dart';
import 'screens/event_list_screen.dart';
import 'screens/order_history_screen.dart';
import 'screens/admin_dashboard_screen.dart';
import 'screens/admin_content_screen.dart';
import 'screens/ticket_management_screen.dart';
import 'screens/band_profile_screen.dart';
import 'screens/news_screen.dart';
import 'screens/members_screen.dart';
import 'screens/discography_screen.dart';
import 'screens/gallery_screen.dart';
import 'screens/hero_screen_3d.dart';
import 'constants/app_constants.dart';
import 'theme.dart';

void main() {
  runApp(const ProviderScope(child: MyApp()));
}

class MyApp extends ConsumerWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authNotifierProvider);

    return MaterialApp(
      title: AppConstants.appTitle,
      debugShowCheckedModeBanner: false,
      theme: AppTheme.dark(),
      home: authState.isAuthenticated ? const MainShellScreen() : const LoginScreen(),
      routes: {
        '/home': (_) => const MainShellScreen(),
        '/login': (_) => const LoginScreen(),
        '/register': (_) => const RegisterScreen(),
        '/events': (_) => const EventListScreen(),
        '/orders': (_) => const OrderHistoryScreen(),
        '/admin-dashboard': (_) => const AdminDashboardScreen(),
        '/admin-content': (_) => const AdminContentScreen(),
        '/tickets': (_) => const TicketManagementScreen(),
        '/band-profile': (_) => const BandProfileScreen(),
        '/news': (_) => const NewsScreen(),
        '/members': (_) => const MembersScreen(),
        '/discography': (_) => const DiscographyScreen(),
        '/gallery': (_) => const GalleryScreen(),
        '/hero-3d': (_) => const Hero3DScreen(),
      },
    );
  }
}
