import 'dart:convert';
import 'package:flutter/material.dart';
import '../services/api_service.dart';

class AlbumDetailScreen extends StatefulWidget {
  final int albumId;
  final Map<String, dynamic>? initialData;

  const AlbumDetailScreen({super.key, required this.albumId, this.initialData});

  @override
  State<AlbumDetailScreen> createState() => _AlbumDetailScreenState();
}

class _AlbumDetailScreenState extends State<AlbumDetailScreen> {
  late Future<Map<String, dynamic>> _future;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<Map<String, dynamic>> _load() async {
    if (widget.initialData != null && widget.initialData!['songs'] is List) {
      return widget.initialData!;
    }
    final res = await ApiService.instance.getAlbum(widget.albumId);
    if (res.statusCode == 200) {
      final obj = ApiService.extractObject(jsonDecode(res.body));
      if (obj != null) return obj;
    }
    throw Exception('Gagal memuat album');
  }

  String _formatDuration(dynamic seconds) {
    final s = int.tryParse('$seconds') ?? 0;
    final m = s ~/ 60;
    final r = s % 60;
    return '${m.toString().padLeft(2, '0')}:${r.toString().padLeft(2, '0')}';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Detail Album')),
      body: FutureBuilder<Map<String, dynamic>>(
        future: _future,
        builder: (context, snap) {
          if (snap.connectionState != ConnectionState.done) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snap.hasError) return Center(child: Text('Error: ${snap.error}'));
          final album = snap.data!;
          final songs = album['songs'] is List
              ? List<Map<String, dynamic>>.from((album['songs'] as List).cast<Map<String, dynamic>>())
              : <Map<String, dynamic>>[];

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              Text(album['title']?.toString() ?? 'Album', style: Theme.of(context).textTheme.headlineSmall),
              const SizedBox(height: 8),
              if (album['released_at'] != null) Text('Rilis: ${album['released_at']}'),
              if (album['description'] != null) ...[
                const SizedBox(height: 12),
                Text(album['description'].toString()),
              ],
              const SizedBox(height: 20),
              Text('Daftar Lagu', style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 8),
              if (songs.isEmpty)
                const Text('Belum ada lagu.')
              else
                ...songs.map((s) => ListTile(
                      leading: Text('${s['track_number'] ?? '-'}'),
                      title: Text(s['title']?.toString() ?? 'Lagu'),
                      trailing: Text(_formatDuration(s['duration_seconds'])),
                    )),
            ],
          );
        },
      ),
    );
  }
}
