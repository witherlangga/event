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
                Wrap(
                  spacing: 12,
                  runSpacing: 10,
                  children: [
                    ElevatedButton.icon(
                      onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const EventListScreen(embedded: true))),
                      icon: const Icon(Icons.confirmation_num_outlined),
                      label: const Text('Dapatkan Tiket'),
                    ),
                    OutlinedButton.icon(
                      onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const NewsScreen())),
                      icon: const Icon(Icons.newspaper_outlined),
                      label: const Text('Baca Berita'),
                    ),
                  ],
                ),
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
                        onPressed: () {},
                        style: ElevatedButton.styleFrom(backgroundColor: AppTheme.secondary),
                        child: const Text('Book Now'),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('Berita Terbaru', style: Theme.of(context).textTheme.titleMedium),
                TextButton(onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const NewsScreen())), child: const Text('Lihat semua')),
              ],
            ),
          ),
          if (_news.isEmpty)
            const Padding(padding: EdgeInsets.symmetric(horizontal: 16), child: Card(child: Padding(padding: EdgeInsets.all(16), child: Text('Belum ada berita.'))))
          else
            SizedBox(
              height: 190,
              child: ListView.separated(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                scrollDirection: Axis.horizontal,
                itemCount: _news.length,
                separatorBuilder: (_, __) => const SizedBox(width: 12),
                itemBuilder: (context, index) {
                  final n = _news[index];
                  return SizedBox(
                    width: 260,
                    child: Card(
                      elevation: 0,
                      color: AppTheme.card,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                      child: InkWell(
                        borderRadius: BorderRadius.circular(16),
                        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const NewsScreen())),
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                decoration: BoxDecoration(color: AppTheme.accent.withValues(alpha: 0.16), borderRadius: BorderRadius.circular(999)),
                                child: Text('News', style: Theme.of(context).textTheme.bodySmall?.copyWith(color: AppTheme.accent)),
                              ),
                              const SizedBox(height: 10),
                              Text(n['title']?.toString() ?? 'Berita', maxLines: 2, overflow: TextOverflow.ellipsis, style: Theme.of(context).textTheme.titleMedium),
                              const SizedBox(height: 8),
                              Text(n['excerpt']?.toString() ?? '', maxLines: 3, overflow: TextOverflow.ellipsis, style: Theme.of(context).textTheme.bodySmall),
                            ],
                          ),
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),
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
