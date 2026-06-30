import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../constants/app_constants.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../theme.dart';
import 'event_list_screen.dart';
import 'discography_screen.dart';
import 'gallery_screen.dart';
import 'profile_hub_screen.dart';
import 'news_screen.dart';

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
    GalleryScreen(embedded: true),
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
              leading: const Icon(Icons.music_note),
              title: const Text('Konser'),
              onTap: () { setState(() => _index = 1); Navigator.pop(context); },
            ),
            ListTile(
              leading: const Icon(Icons.album),
              title: const Text('Musik'),
              onTap: () { setState(() => _index = 2); Navigator.pop(context); },
            ),
            ListTile(
              leading: const Icon(Icons.photo_library),
              title: const Text('Galeri'),
              onTap: () { setState(() => _index = 3); Navigator.pop(context); },
            ),
            ListTile(
              leading: const Icon(Icons.newspaper),
              title: const Text('Berita'),
              onTap: () {
                Navigator.pop(context);
                Navigator.push(context, MaterialPageRoute(builder: (_) => const NewsScreen()));
              },
            ),
            ListTile(
              leading: const Icon(Icons.person),
              title: const Text('Profil'),
              onTap: () { setState(() => _index = 4); Navigator.pop(context); },
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
          NavigationDestination(icon: Icon(Icons.music_note_outlined), selectedIcon: Icon(Icons.music_note), label: 'Konser'),
          NavigationDestination(icon: Icon(Icons.album_outlined), selectedIcon: Icon(Icons.album), label: 'Musik'),
          NavigationDestination(icon: Icon(Icons.photo_library_outlined), selectedIcon: Icon(Icons.photo_library), label: 'Galeri'),
          NavigationDestination(icon: Icon(Icons.person_outline), selectedIcon: Icon(Icons.person), label: 'Profil'),
        ],
      ),
    );
  }

  static const _titles = ['Beranda', 'Konser', 'Musik', 'Galeri', 'Profil'];
}

class _HomeTab extends StatefulWidget {
  const _HomeTab();

  @override
  State<_HomeTab> createState() => _HomeTabState();
}

class _HomeTabState extends State<_HomeTab> {
  Map<String, dynamic>? _bandProfile;
  List<Map<String, dynamic>> _news = [];

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
      final news = await ApiService.instance.getNews();
      if (mounted) setState(() => _news = news.take(3).toList());
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    final bandName = _bandProfile?['name']?.toString() ?? AppConstants.bandName;

    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: EdgeInsets.zero,
        children: [
          Container(
            padding: const EdgeInsets.all(24),
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
                Text(bandName, style: Theme.of(context).textTheme.headlineSmall),
                const SizedBox(height: 4),
                Text(AppConstants.tagline, style: Theme.of(context).textTheme.bodySmall?.copyWith(color: AppTheme.gold)),
                const SizedBox(height: 12),
                Text(
                  _bandProfile?['bio']?.toString() ?? 'Aplikasi resmi band untuk konser, musik, dan komunitas fans.',
                  style: Theme.of(context).textTheme.bodyMedium,
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
            child: Text('Berita Terbaru', style: Theme.of(context).textTheme.titleMedium),
          ),
          if (_news.isEmpty)
            const Padding(padding: EdgeInsets.all(16), child: Text('Belum ada berita.'))
          else
            ..._news.map((n) => ListTile(
                  leading: const Icon(Icons.newspaper),
                  title: Text(n['title']?.toString() ?? 'Berita'),
                  subtitle: Text(n['excerpt']?.toString() ?? ''),
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const NewsScreen())),
                )),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
            child: Text('Konser Mendatang', style: Theme.of(context).textTheme.titleMedium),
          ),
          const SizedBox(height: 420, child: EventListScreen(embedded: true)),
        ],
      ),
    );
  }
}
