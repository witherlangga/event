import 'dart:async';
import 'dart:convert';
import 'package:audioplayers/audioplayers.dart';
import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../utils/media_url.dart';

class AlbumDetailScreen extends StatefulWidget {
  final int albumId;
  final Map<String, dynamic>? initialData;

  const AlbumDetailScreen({super.key, required this.albumId, this.initialData});

  @override
  State<AlbumDetailScreen> createState() => _AlbumDetailScreenState();
}

class _AlbumDetailScreenState extends State<AlbumDetailScreen> {
  late Future<Map<String, dynamic>> _future;
  int? _playingIndex;
  final AudioPlayer _audioPlayer = AudioPlayer();
  PlayerState _playerState = PlayerState.stopped;
  Duration _position = Duration.zero;
  Duration _duration = Duration.zero;

  @override
  void initState() {
    super.initState();
    _future = _load();
    _audioPlayer.onPlayerStateChanged.listen((state) {
      setState(() => _playerState = state);
    });
    _audioPlayer.onPositionChanged.listen((position) {
      setState(() => _position = position);
    });
    _audioPlayer.onDurationChanged.listen((duration) {
      setState(() => _duration = duration);
    });
  }

  @override
  void dispose() {
    _audioPlayer.dispose();
    super.dispose();
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
          final coverPath = album['cover_path']?.toString();
          final coverUrl = MediaUrl.resolve(coverPath);

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              Text(album['title']?.toString() ?? 'Album', style: Theme.of(context).textTheme.headlineSmall),
              const SizedBox(height: 12),
              if (coverUrl != null)
                ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: Image.network(
                    coverUrl,
                    height: 180,
                    width: double.infinity,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => Container(
                      height: 180,
                      color: Theme.of(context).colorScheme.primary,
                      child: const Center(child: Icon(Icons.album, size: 48, color: Colors.white54)),
                    ),
                  ),
                ),
              const SizedBox(height: 12),
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
                ...songs.asMap().entries.map((entry) {
                  final i = entry.key;
                  final s = entry.value;
                  final audioPath = s['streaming_url']?.toString() ?? s['file_path']?.toString() ?? s['audio_url']?.toString() ?? s['url']?.toString() ?? '';

                  return ListTile(
                    leading: IconButton(
                      iconSize: 36,
                      icon: Icon(
                        _playingIndex == i && _playerState == PlayerState.playing
                            ? Icons.pause_circle_filled
                            : Icons.play_circle_fill,
                        color: Theme.of(context).colorScheme.secondary,
                      ),
                      onPressed: () async {
                        if (audioPath.isEmpty) {
                          if (mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Audio tidak tersedia')));
                          }
                          return;
                        }

                        final resolved = MediaUrl.resolve(audioPath) ?? audioPath;
                        final messenger = ScaffoldMessenger.of(context);
                        try {
                          if (_playingIndex == i && _playerState == PlayerState.playing) {
                            await _audioPlayer.pause();
                          } else {
                            if (_playingIndex != i) {
                              await _audioPlayer.stop();
                              _position = Duration.zero;
                              _duration = Duration.zero;
                            }
                            _playingIndex = i;
                            await _audioPlayer.play(UrlSource(resolved));
                          }
                        } catch (e) {
                          if (mounted) {
                            messenger.showSnackBar(SnackBar(content: Text('Gagal memutar audio: $e')));
                          }
                        }
                      },
                    ),
                    title: Text(s['title']?.toString() ?? 'Lagu'),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(s['artist']?.toString() ?? ''),
                        if (_playingIndex == i && _duration > Duration.zero)
                          Text(
                            '${_formatDuration(_position.inSeconds)} / ${_formatDuration(_duration.inSeconds)}',
                            style: const TextStyle(fontSize: 12, color: Colors.white60),
                          ),
                      ],
                    ),
                    trailing: Text(_formatDuration(s['duration_seconds'])),
                  );
                }),
            ],
          );
        },
      ),
    );
  }
}
