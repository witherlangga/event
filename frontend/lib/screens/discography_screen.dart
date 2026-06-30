import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'album_detail_screen.dart';

class DiscographyScreen extends StatefulWidget {
  final bool embedded;

  const DiscographyScreen({super.key, this.embedded = false});

  @override
  State<DiscographyScreen> createState() => _DiscographyScreenState();
}

class _DiscographyScreenState extends State<DiscographyScreen> {
  late Future<List<Map<String, dynamic>>> _future;

  @override
  void initState() {
    super.initState();
    _future = ApiService.instance.getAlbums();
  }

  Widget _buildBody() {
    return FutureBuilder<List<Map<String, dynamic>>>(
      future: _future,
      builder: (context, snap) {
        if (snap.connectionState != ConnectionState.done) {
          return const Center(child: CircularProgressIndicator());
        }
        if (snap.hasError) return Center(child: Text('Error: ${snap.error}'));
        final albums = snap.data ?? [];
        if (albums.isEmpty) return const Center(child: Text('Belum ada album.'));

        return ListView.builder(
          padding: const EdgeInsets.all(16),
          itemCount: albums.length,
          itemBuilder: (context, i) {
            final album = albums[i];
            final id = album['id'] is int ? album['id'] as int : int.tryParse('${album['id']}') ?? 0;
            final songs = album['songs'] is List ? (album['songs'] as List).length : 0;

            return Card(
              margin: const EdgeInsets.only(bottom: 12),
              child: ListTile(
                leading: const Icon(Icons.album, size: 36),
                title: Text(album['title']?.toString() ?? 'Album'),
                subtitle: Text('${album['released_at'] ?? 'TBA'} • $songs lagu'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => AlbumDetailScreen(albumId: id, initialData: album)),
                ),
              ),
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    if (widget.embedded) return _buildBody();
    return Scaffold(appBar: AppBar(title: const Text('Discography')), body: _buildBody());
  }
}
