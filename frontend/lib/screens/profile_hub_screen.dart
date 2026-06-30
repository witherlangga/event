import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../providers/auth_provider.dart';
import '../constants/app_constants.dart';
import '../theme.dart';
import 'band_profile_screen.dart';
import 'members_screen.dart';
import 'news_screen.dart';
import 'order_history_screen.dart';
import 'ticket_management_screen.dart';
import 'admin_dashboard_screen.dart';
import 'admin_content_screen.dart';

class ProfileHubScreen extends ConsumerWidget {
  final bool embedded;

  const ProfileHubScreen({super.key, this.embedded = false});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final auth = ref.watch(authNotifierProvider);
    final isAdmin = auth.user?.role == 'system_admin';
    final name = auth.user?.name ?? 'Guest';

    final body = ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Card(
          child: ListTile(
            leading: CircleAvatar(
              backgroundColor: AppTheme.accent,
              child: Text(name.isNotEmpty ? name[0].toUpperCase() : 'F'),
            ),
            title: Text(name),
            subtitle: Text('${AppConstants.fanLabel} • ${auth.user?.email ?? ''}'),
          ),
        ),
        const SizedBox(height: 8),
        _tile(context, Icons.info_outline, 'Profil Band', () {
          Navigator.push(context, MaterialPageRoute(builder: (_) => const BandProfileScreen()));
        }),
        _tile(context, Icons.group, 'Anggota Band', () {
          Navigator.push(context, MaterialPageRoute(builder: (_) => const MembersScreen()));
        }),
        _tile(context, Icons.newspaper, 'Berita', () {
          Navigator.push(context, MaterialPageRoute(builder: (_) => const NewsScreen()));
        }),
        _tile(context, Icons.confirmation_num, 'Tiket Saya', () {
          Navigator.push(context, MaterialPageRoute(builder: (_) => const TicketManagementScreen()));
        }),
        _tile(context, Icons.history, 'Riwayat Pesanan', () {
          Navigator.push(context, MaterialPageRoute(builder: (_) => const OrderHistoryScreen()));
        }),
        if (isAdmin) ...[
          const Divider(),
          Text('Administrasi', style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 8),
          _tile(context, Icons.admin_panel_settings, 'Panel Konser', () {
            Navigator.push(context, MaterialPageRoute(builder: (_) => const AdminDashboardScreen()));
          }),
          _tile(context, Icons.dashboard_customize, 'Kelola Konten Band', () {
            Navigator.push(context, MaterialPageRoute(builder: (_) => const AdminContentScreen()));
          }),
        ],
      ],
    );

    if (embedded) return body;
    return Scaffold(appBar: AppBar(title: const Text('Profil')), body: body);
  }

  Widget _tile(BuildContext context, IconData icon, String title, VoidCallback onTap) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(leading: Icon(icon), title: Text(title), trailing: const Icon(Icons.chevron_right), onTap: onTap),
    );
  }
}
