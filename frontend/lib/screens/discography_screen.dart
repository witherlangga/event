import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../theme.dart';
import '../utils/media_url.dart';
import 'album_detail_screen.dart';

class DiscographyScreen extends StatefulWidget {
  final bool embedded;

  const DiscographyScreen({super.key, this.embedded = false});

  @override
  State<DiscographyScreen> createState() => _DiscographyScreenState();
}

class _DiscographyScreenState extends State<DiscographyScreen>
    with SingleTickerProviderStateMixin {
  late Future<List<Map<String, dynamic>>> _future;
  late AnimationController _animationController;

  @override
  void initState() {
    super.initState();
    _future = ApiService.instance.getAlbums();
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 300),
      vsync: this,
    );
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  Widget _buildBody() {
    return FutureBuilder<List<Map<String, dynamic>>>(
      future: _future,
      builder: (context, snap) {
        if (snap.connectionState != ConnectionState.done) {
          return const Center(
            child: CircularProgressIndicator(color: AppTheme.accent),
          );
        }
        if (snap.hasError) return Center(child: Text('Error: ${snap.error}'));
        final albums = snap.data ?? [];
        if (albums.isEmpty) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.music_note, size: 64, color: AppTheme.accent.withValues(alpha: 0.3)),
                const SizedBox(height: 16),
                const Text('Belum ada album.', style: TextStyle(fontSize: 16, color: Colors.white70)),
              ],
            ),
          );
        }

        return ListView.builder(
          padding: const EdgeInsets.all(16),
          itemCount: albums.length,
          itemBuilder: (context, i) {
            final album = albums[i];
            final id = album['id'] is int
                ? album['id'] as int
                : int.tryParse('${album['id']}') ?? 0;
            final songs =
                album['songs'] is List ? (album['songs'] as List).length : 0;
            final coverPath = album['cover_path']?.toString();
            final coverUrl = MediaUrl.resolve(coverPath);
            final releasedAt = album['released_at'] ?? 'TBA';

            return GestureDetector(
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => AlbumDetailScreen(
                    albumId: id,
                    initialData: album,
                  ),
                ),
              ),
              child: Container(
                margin: const EdgeInsets.only(bottom: 16),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: AppTheme.accent.withValues(alpha: 0.2),
                      blurRadius: 12,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Material(
                  borderRadius: BorderRadius.circular(16),
                  color: const Color(0xFF1a2847),
                  child: Row(
                    children: [
                      Container(
                        width: 120,
                        height: 120,
                        decoration: BoxDecoration(
                          borderRadius: const BorderRadius.only(
                            topLeft: Radius.circular(16),
                            bottomLeft: Radius.circular(16),
                          ),
                          image: coverUrl != null
                              ? DecorationImage(
                                  image: NetworkImage(coverUrl),
                                  fit: BoxFit.cover,
                                )
                              : null,
                          color: AppTheme.primary,
                        ),
                        child: coverUrl == null
                            ? Center(
                                child: Icon(
                                  Icons.album,
                                  size: 48,
                                  color: AppTheme.accent.withValues(alpha: 0.5),
                                ),
                              )
                            : null,
                      ),
                      Expanded(
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    album['title']?.toString() ?? 'Album',
                                    style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 16,
                                      fontWeight: FontWeight.w700,
                                    ),
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                  const SizedBox(height: 6),
                                  Row(
                                    children: [
                                      Icon(
                                        Icons.calendar_today,
                                        size: 13,
                                        color: AppTheme.accent,
                                      ),
                                      const SizedBox(width: 4),
                                      Text(
                                        releasedAt.toString(),
                                        style: const TextStyle(
                                          color: Colors.white60,
                                          fontSize: 12,
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 10,
                                  vertical: 6,
                                ),
                                decoration: BoxDecoration(
                                  color:
                                      AppTheme.accent.withValues(alpha: 0.15),
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(
                                    color: AppTheme.accent.withValues(
                                      alpha: 0.3,
                                    ),
                                  ),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    const Icon(
                                      Icons.music_note,
                                      size: 12,
                                      color: AppTheme.accent,
                                    ),
                                    const SizedBox(width: 4),
                                    Text(
                                      '$songs lagu',
                                      style: const TextStyle(
                                        color: AppTheme.accent,
                                        fontSize: 12,
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: Icon(
                          Icons.chevron_right,
                          color: AppTheme.accent.withValues(alpha: 0.6),
                          size: 28,
                        ),
                      ),
                    ],
                  ),
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
    return Scaffold(
      appBar: AppBar(
        title: const Text('Listen Now'),
        elevation: 0,
        backgroundColor: AppTheme.primary,
      ),
      body: _buildBody(),
    );
  }
}

