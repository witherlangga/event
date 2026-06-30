import 'dart:convert';
import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'admin_add_concert_screen.dart';
import 'admin_content_screen.dart';
import 'admin_ticket_types_screen.dart';

class AdminDashboardScreen extends StatefulWidget {
  const AdminDashboardScreen({super.key});

  @override
  State<AdminDashboardScreen> createState() => _AdminDashboardScreenState();
}

class _AdminDashboardScreenState extends State<AdminDashboardScreen> {
  late Future<Map<String, dynamic>> _statsFuture;

  @override
  void initState() {
    super.initState();
    _statsFuture = _loadStats();
  }

  Future<void> _refresh() async {
    setState(() {
      _statsFuture = _loadStats();
    });
  }

  Future<Map<String, dynamic>> _loadStats() async {
    final res = await ApiService.instance.getAdminConcerts();
    if (res.statusCode == 200) {
      final data = jsonDecode(res.body);
      final events = ApiService.extractList(data);
      return {
        'events': events,
        'totalConcerts': events.length,
      };
    }
    throw Exception('Gagal memuat data admin: ${res.statusCode}');
  }

  Future<void> _deleteConcert(int concertId) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Hapus Konser'),
        content: const Text('Apakah Anda yakin ingin menghapus konser ini?'),
        actions: [
          TextButton(onPressed: () => Navigator.of(context).pop(false), child: const Text('Batal')),
          TextButton(onPressed: () => Navigator.of(context).pop(true), child: const Text('Hapus')),
        ],
      ),
    );

    if (confirmed != true) return;

    final res = await ApiService.instance.delete('/admin/concerts/$concertId');
    if (res.statusCode == 200) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Konser berhasil dihapus.')),
      );
      await _refresh();
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Gagal menghapus konser: ${res.statusCode}')),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Panel Admin')),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          await Navigator.of(context).push(
            MaterialPageRoute(builder: (_) => const AdminAddConcertScreen()),
          );
          _refresh();
        },
        child: const Icon(Icons.add),
      ),
      body: FutureBuilder<Map<String, dynamic>>(
        future: _statsFuture,
        builder: (context, snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snap.hasError) {
            return Center(child: Text('Error: ${snap.error}'));
          }
          final stats = snap.data!;
          final events = stats['events'] as List<Map<String, dynamic>>;

          return RefreshIndicator(
            onRefresh: _refresh,
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Ringkasan', style: Theme.of(context).textTheme.titleMedium),
                        const SizedBox(height: 8),
                        Text('Total konser: ${stats['totalConcerts']}'),
                        const SizedBox(height: 12),
                        OutlinedButton.icon(
                          onPressed: () {
                            Navigator.push(context, MaterialPageRoute(builder: (_) => const AdminContentScreen()));
                          },
                          icon: const Icon(Icons.dashboard_customize),
                          label: const Text('Kelola Konten Band'),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Text('Daftar Konser', style: Theme.of(context).textTheme.titleMedium),
                const SizedBox(height: 8),
                if (events.isEmpty)
                  const Padding(
                    padding: EdgeInsets.all(24),
                    child: Center(child: Text('Belum ada konser. Tap + untuk menambah.')),
                  )
                else
                  ...events.map((event) {
                    final id = event['id'];
                    return Card(
                      margin: const EdgeInsets.symmetric(vertical: 6),
                      child: Padding(
                        padding: const EdgeInsets.symmetric(vertical: 4),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            ListTile(
                              leading: const Icon(Icons.music_note),
                              title: Text(event['title']?.toString() ?? 'Konser'),
                              subtitle: Text(event['location_name']?.toString() ?? 'Lokasi belum diisi'),
                              trailing: Icon(
                                (event['is_active'] == true || event['is_active'] == 1)
                                    ? Icons.check_circle
                                    : Icons.pause_circle,
                                color: (event['is_active'] == true || event['is_active'] == 1)
                                    ? Colors.green
                                    : Colors.orange,
                              ),
                            ),
                            Padding(
                              padding: const EdgeInsets.only(right: 8, bottom: 8),
                              child: Align(
                                alignment: Alignment.centerRight,
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    TextButton.icon(
                                      icon: const Icon(Icons.settings, color: Colors.blueAccent),
                                      label: const Text('Atur Tiket', style: TextStyle(color: Colors.blueAccent)),
                                      onPressed: () {
                                        Navigator.of(context).push(
                                          MaterialPageRoute(
                                            builder: (_) => AdminTicketTypesScreen(event: event),
                                          ),
                                        );
                                      },
                                    ),
                                    const SizedBox(width: 8),
                                    TextButton.icon(
                                      icon: const Icon(Icons.delete_outline, color: Colors.redAccent),
                                      label: const Text('Hapus', style: TextStyle(color: Colors.redAccent)),
                                      onPressed: () {
                                        if (id is int) {
                                          _deleteConcert(id);
                                        } else if (id is String) {
                                          final parsedId = int.tryParse(id);
                                          if (parsedId != null) {
                                            _deleteConcert(parsedId);
                                          }
                                        }
                                      },
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    );
                  }),
              ],
            ),
          );
        },
      ),
    );
  }
}
