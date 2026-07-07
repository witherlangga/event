import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';
import '../constants/app_constants.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../theme.dart';
import 'event_list_screen.dart';
import 'discography_screen.dart';
import 'profile_hub_screen.dart';

class MainShellScreen extends ConsumerStatefulWidget {
  const MainShellScreen({super.key});

  @override
  ConsumerState<MainShellScreen> createState() => _MainShellScreenState();
}

class _MainShellScreenState extends ConsumerState<MainShellScreen> {
  int _index = 0;

  final _tabs = const [
    _HomeTab(),
    EventListScreen(embedded: true),
    DiscographyScreen(embedded: true),
    ProfileHubScreen(embedded: true),
  ];

  @override
  Widget build(BuildContext context) {
    final authProv = ref.watch(authNotifierProvider);
    final authNotifier = ref.read(authNotifierProvider.notifier);
    final name = authProv.user?.name ?? 'Guest';

    return Scaffold(
      appBar: AppBar(title: Text(_titles[_index])),
      drawer: Drawer(
        child: ListView(
          padding: EdgeInsets.zero,
          children: [
            UserAccountsDrawerHeader(
              accountName: Text(name),
              accountEmail: Text(authProv.user?.email ?? ''),
              currentAccountPicture: CircleAvatar(
                backgroundColor: AppTheme.accent,
                child: Text(name.isNotEmpty ? name[0].toUpperCase() : 'G'),
              ),
              decoration: const BoxDecoration(color: AppTheme.primary),
            ),
            ListTile(
              leading: const Icon(Icons.home),
              title: const Text('Beranda'),
              onTap: () { setState(() => _index = 0); Navigator.pop(context); },
            ),
            ListTile(
              leading: const Icon(Icons.event),
              title: const Text('Konser'),
              onTap: () { setState(() => _index = 1); Navigator.pop(context); },
            ),
            ListTile(
              leading: const Icon(Icons.album),
              title: const Text('Musik'),
              onTap: () { setState(() => _index = 2); Navigator.pop(context); },
            ),
            ListTile(
              leading: const Icon(Icons.person),
              title: const Text('Profil'),
              onTap: () { setState(() => _index = 3); Navigator.pop(context); },
            ),
            const Divider(),
            ListTile(
              leading: const Icon(Icons.logout),
              title: const Text('Keluar'),
              onTap: () async {
                await authNotifier.logout();
                if (context.mounted) {
                  Navigator.of(context).pushReplacementNamed('/login');
                }
              },
            ),
          ],
        ),
      ),
      body: IndexedStack(index: _index, children: _tabs),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _index,
        onDestinationSelected: (i) => setState(() => _index = i),
        destinations: const [
          NavigationDestination(icon: Icon(Icons.home_outlined), selectedIcon: Icon(Icons.home), label: 'Beranda'),
          NavigationDestination(icon: Icon(Icons.event_outlined), selectedIcon: Icon(Icons.event), label: 'Konser'),
          NavigationDestination(icon: Icon(Icons.album_outlined), selectedIcon: Icon(Icons.album), label: 'Musik'),
          NavigationDestination(icon: Icon(Icons.person_outline), selectedIcon: Icon(Icons.person), label: 'Profil'),
        ],
      ),
    );
  }

  static const _titles = ['Beranda', 'Konser', 'Musik', 'Profil'];
}

class _HomeTab extends StatefulWidget {
  const _HomeTab();

  @override
  State<_HomeTab> createState() => _HomeTabState();
}

class _HomeTabState extends State<_HomeTab> {
  Map<String, dynamic>? _bandProfile;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final profileRes = await ApiService.instance.getBandProfile();
      if (profileRes.statusCode == 200) {
        final profile = ApiService.extractObject(jsonDecode(profileRes.body));
        if (profile != null) setState(() => _bandProfile = profile);
      }
    } catch (_) {}
  }

  Future<void> _openMap() async {
    final mapLink = _bandProfile?['next_show_map_link']?.toString();
    if (mapLink != null && mapLink.isNotEmpty) {
      try {
        if (await canLaunchUrl(Uri.parse(mapLink))) {
          await launchUrl(Uri.parse(mapLink), mode: LaunchMode.externalApplication);
        } else {
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Tidak dapat membuka peta')),
            );
          }
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Error: $e')),
          );
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final bandName = _bandProfile?['name']?.toString() ?? AppConstants.bandName;
    final bio = _bandProfile?['bio']?.toString() ?? 'Aplikasi resmi band untuk konser, musik, dan komunitas fans.';
    final nextTitle = _bandProfile?['next_show_title']?.toString() ?? 'Live in Jakarta';
    final nextDate = _bandProfile?['next_show_date']?.toString() ?? '23 April 2025 • 20:00';
    final nextPrice = _bandProfile?['next_show_price_text']?.toString() ?? 'Mulai Rp 500K';

    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: EdgeInsets.zero,
        children: [
          Container(
            padding: const EdgeInsets.fromLTRB(20, 24, 20, 28),
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [AppTheme.primary, AppTheme.surface],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    CircleAvatar(
                      radius: 24,
                      backgroundColor: AppTheme.accent.withValues(alpha: 0.18),
                      child: const Icon(Icons.music_note, color: AppTheme.accent),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(bandName, style: Theme.of(context).textTheme.titleLarge),
                          const SizedBox(height: 2),
                          Text(AppConstants.tagline, style: Theme.of(context).textTheme.bodySmall?.copyWith(color: AppTheme.gold)),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 18),
                Text(
                  'Sound Beyond the Horizon',
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontSize: 24, fontWeight: FontWeight.w800),
                ),
                const SizedBox(height: 8),
                Text(bio, style: Theme.of(context).textTheme.bodyMedium),
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.06),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: Colors.white.withValues(alpha: 0.08)),
                  ),
                  child: Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Next Show', style: Theme.of(context).textTheme.bodySmall?.copyWith(color: AppTheme.accent)),
                            const SizedBox(height: 6),
                            Text(nextTitle, style: Theme.of(context).textTheme.titleMedium),
                            const SizedBox(height: 4),
                            Text(nextDate, style: Theme.of(context).textTheme.bodySmall),
                            const SizedBox(height: 4),
                            Text(nextPrice, style: Theme.of(context).textTheme.bodySmall?.copyWith(color: AppTheme.gold)),
                          ],
                        ),
                      ),
                      ElevatedButton(
                        onPressed: _openMap,
                        style: ElevatedButton.styleFrom(backgroundColor: AppTheme.secondary),
                        child: const Text('Show Location'),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
            child: Text('Daftar Tiket', style: Theme.of(context).textTheme.titleMedium),
          ),
          const SizedBox(height: 420, child: EventListScreen(embedded: true)),
        ],
      ),
    );
  }
}
