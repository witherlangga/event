import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../providers/auth_provider.dart';
import 'event_list_screen.dart';
import '../theme.dart';

class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authProv = ref.read(authNotifierProvider);
    final authNotifier = ref.read(authNotifierProvider.notifier);
    final name = authProv.user?.name ?? 'Guest';

    return Scaffold(
      appBar: AppBar(title: const Text('Event Tickets')),
      drawer: Drawer(
        child: ListView(
          padding: EdgeInsets.zero,
          children: [
            UserAccountsDrawerHeader(
              accountName: Text(name),
              accountEmail: Text(authProv.user?.email ?? ''),
              currentAccountPicture: CircleAvatar(child: Text(name.isNotEmpty ? name[0].toUpperCase() : 'G')),
              decoration: const BoxDecoration(color: AppTheme.primary),
            ),
            ListTile(
              leading: const Icon(Icons.event),
              title: const Text('Browse Events'),
              onTap: () { Navigator.of(context).push(MaterialPageRoute(builder: (_) => const EventListScreen())); },
            ),
            const Divider(),
            ListTile(
              leading: const Icon(Icons.logout),
              title: const Text('Logout'),
              onTap: () async { await authNotifier.logout(); Navigator.of(context).pushReplacementNamed('/login'); },
            ),
          ],
        ),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Card(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              elevation: 6,
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Welcome,', style: Theme.of(context).textTheme.bodySmall),
                          const SizedBox(height: 6),
                          Text(name, style: Theme.of(context).textTheme.titleLarge),
                          const SizedBox(height: 8),
                          Text('Discover events near you', style: Theme.of(context).textTheme.bodyMedium),
                        ],
                      ),
                    ),
                    const SizedBox(width: 12),
                    ElevatedButton.icon(
                      onPressed: () { Navigator.of(context).push(MaterialPageRoute(builder: (_) => const EventListScreen())); },
                      icon: const Icon(Icons.search),
                      label: const Text('Browse'),
                      style: ElevatedButton.styleFrom(shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10))),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            Expanded(child: const EventListScreen()),
          ],
        ),
      ),
    );
  }
}

// Event browsing handled by EventListScreen
