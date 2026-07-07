import 'dart:convert';
import 'package:flutter/material.dart';
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
                return {'image_url': item};
              }
              if (item is Map) {
                return Map<String, dynamic>.from(item.cast<String, dynamic>());
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
                          Image.network(
                            imageUrl,
                            fit: BoxFit.cover,
                            errorBuilder: (_, __, ___) => _placeholder(),
                            loadingBuilder: (context, child, progress) {
                              if (progress == null) return child;
                              return Center(
                                child: CircularProgressIndicator(
                                  value: progress.expectedTotalBytes != null
                                      ? progress.cumulativeBytesLoaded /
                                          progress.expectedTotalBytes!
                                      : null,
                                  color: AppTheme.accent,
                                ),
                              );
                            },
                          )
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
                child: Image.network(imageUrl),
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

