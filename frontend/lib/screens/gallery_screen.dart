import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import '../services/api_service.dart';
import '../theme.dart';

class GalleryScreen extends StatefulWidget {
  final bool embedded;

  const GalleryScreen({super.key, this.embedded = false});

  @override
  State<GalleryScreen> createState() => _GalleryScreenState();
}

class _GalleryScreenState extends State<GalleryScreen> {
  late Future<List<Map<String, dynamic>>> _future;

  @override
  void initState() {
    super.initState();
    _future = _loadMoments();
  }

  Future<List<Map<String, dynamic>>> _loadMoments() async {
    try {
      final response = await ApiService.instance.getBandProfile();
      if (response.statusCode == 200) {
        final data = ApiService.extractObject(jsonDecode(response.body));
        if (data != null) {
          final moments = data['moments'];
          if (moments is List) {
            return moments.map<Map<String, dynamic>>((item) {
              if (item is String) {
                final raw = item.trim();
                final lower = raw.toLowerCase();

                // If it's already a full URL, keep it.
                if (lower.startsWith('http://') || lower.startsWith('https://')) {
                  return {'image_url': raw};
                }

                // If it already looks like a storage/uploads path, keep as-is (ApiService will resolve it).
                if (lower.startsWith('/storage') || lower.startsWith('storage') || lower.startsWith('/uploads') || lower.startsWith('uploads') || lower.contains('/storage/') || lower.contains('/uploads/')) {
                  return {'image_url': raw};
                }

                // Otherwise assume it's a storage filename and prefix with storage/
                return {'image_url': 'storage/$raw'};
              }
              if (item is Map) {
                final map = Map<String, dynamic>.from(item.cast<String, dynamic>());
                // Normalize possible path fields
                for (final key in ['image_url', 'url', 'path']) {
                  if (map.containsKey(key) && map[key] is String) {
                    final raw = map[key].toString().trim();
                    final lower = raw.toLowerCase();
                    if (lower.startsWith('http://') || lower.startsWith('https://')) {
                      map['image_url'] = raw;
                    } else if (lower.startsWith('/storage') || lower.startsWith('storage') || lower.startsWith('/uploads') || lower.startsWith('uploads') || lower.contains('/storage/') || lower.contains('/uploads/')) {
                      map['image_url'] = raw;
                    } else {
                      map['image_url'] = 'storage/$raw';
                    }
                    break;
                  }
                }
                return map;
              }
              return <String, dynamic>{};
            }).where((item) => item.isNotEmpty).toList();
          }
        }
      }
      return [];
    } catch (e) {
      rethrow;
    }
  }

  Widget _placeholder() {
    return Container(
      color: AppTheme.primary,
      child: const Center(
        child: Icon(Icons.photo, size: 48, color: Colors.white54),
      ),
    );
  }

  Widget _authImage(String imageUrl, {BoxFit fit = BoxFit.cover}) {
    return FutureBuilder<http.Response>(
      future: ApiService.instance.get(imageUrl),
      builder: (context, snap) {
        if (snap.connectionState != ConnectionState.done) {
          return const Center(
            child: CircularProgressIndicator(color: AppTheme.accent),
          );
        }
        if (snap.hasError) return _placeholder();
        final res = snap.data;
        if (res == null) return _placeholder();

        final contentType = (res.headers['content-type'] ?? '').toLowerCase();
        if (res.statusCode == 200 && contentType.startsWith('image')) {
          return Image.memory(
            res.bodyBytes,
            fit: fit,
            gaplessPlayback: true,
          );
        }

        // Log non-image responses for debugging (status, content-type)
        try {
          debugPrint('Gallery image fetch: $imageUrl -> ${res.statusCode} (${contentType})');
        } catch (_) {}

        // Try to parse JSON response that may include {"url": "..."}
        try {
          final decoded = jsonDecode(utf8.decode(res.bodyBytes));
          if (decoded is Map<String, dynamic> && decoded['url'] is String) {
            return Image.network(
              decoded['url'],
              fit: fit,
              errorBuilder: (_, __, ___) => _placeholder(),
            );
          }
        } catch (_) {}

        return _placeholder();
      },
    );
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
        final items = snap.data ?? [];
        if (items.isEmpty) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  Icons.photo_library,
                  size: 64,
                  color: AppTheme.accent.withValues(alpha: 0.3),
                ),
                const SizedBox(height: 16),
                const Text(
                  'Galeri masih kosong.',
                  style: TextStyle(fontSize: 16, color: Colors.white70),
                ),
              ],
            ),
          );
        }

        return GridView.builder(
          padding: const EdgeInsets.all(12),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            crossAxisSpacing: 12,
            mainAxisSpacing: 12,
            childAspectRatio: 0.9,
          ),
          itemCount: items.length,
          itemBuilder: (context, i) {
            final item = items[i];
            final imageUrl = item['image_url']?.toString() ??
                item['url']?.toString() ??
                '';
            final title = item['title']?.toString() ?? 'Foto';
            final caption = item['caption']?.toString() ?? '';

            return GestureDetector(
              onTap: () {
                if (imageUrl.isNotEmpty) {
                  _showFullScreenImage(imageUrl, title);
                }
              },
              child: Container(
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: AppTheme.accent.withValues(alpha: 0.2),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(16),
                  child: Material(
                    color: AppTheme.primary,
                    child: Stack(
                      fit: StackFit.expand,
                      children: [
                        if (imageUrl.isNotEmpty)
                          _authImage(imageUrl, fit: BoxFit.cover)
                        else
                          _placeholder(),
                        Positioned.fill(
                          child: DecoratedBox(
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                colors: [
                                  Colors.transparent,
                                  AppTheme.primary.withValues(alpha: 0.8),
                                ],
                                begin: Alignment.topCenter,
                                end: Alignment.bottomCenter,
                              ),
                            ),
                          ),
                        ),
                        Positioned(
                          bottom: 0,
                          left: 0,
                          right: 0,
                          child: Padding(
                            padding: const EdgeInsets.all(12),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                if (title.isNotEmpty)
                                  Text(
                                    title,
                                    style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 14,
                                      fontWeight: FontWeight.w700,
                                    ),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                if (caption.isNotEmpty) ...[
                                  const SizedBox(height: 4),
                                  Text(
                                    caption,
                                    style: const TextStyle(
                                      color: Colors.white70,
                                      fontSize: 11,
                                    ),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ],
                              ],
                            ),
                          ),
                        ),
                        if (imageUrl.isNotEmpty)
                          Positioned(
                            top: 8,
                            right: 8,
                            child: Container(
                              padding: const EdgeInsets.all(6),
                              decoration: BoxDecoration(
                                color: AppTheme.accent,
                                borderRadius: BorderRadius.circular(12),
                                boxShadow: [
                                  BoxShadow(
                                    color: AppTheme.accent.withValues(alpha: 0.4),
                                    blurRadius: 6,
                                  ),
                                ],
                              ),
                              child: const Icon(
                                Icons.open_in_full,
                                color: Colors.white,
                                size: 16,
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                ),
              ),
            );
          },
        );
      },
    );
  }

  void _showFullScreenImage(String imageUrl, String title) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => Scaffold(
          appBar: AppBar(
            title: Text(title),
            backgroundColor: AppTheme.primary,
          ),
          body: Container(
            color: AppTheme.primary,
            child: Center(
              child: InteractiveViewer(
                child: _authImage(imageUrl, fit: BoxFit.contain),
              ),
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (widget.embedded) return _buildBody();
    return Scaffold(
      appBar: AppBar(
        title: const Text('Behind The Stage'),
        elevation: 0,
        backgroundColor: AppTheme.primary,
      ),
      body: _buildBody(),
    );
  }
}

