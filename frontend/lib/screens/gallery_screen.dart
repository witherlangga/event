import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../utils/media_url.dart';

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
    _future = ApiService.instance.getGallery();
  }

  Widget _buildBody() {
    return FutureBuilder<List<Map<String, dynamic>>>(
      future: _future,
      builder: (context, snap) {
        if (snap.connectionState != ConnectionState.done) {
          return const Center(child: CircularProgressIndicator());
        }
        if (snap.hasError) return Center(child: Text('Error: ${snap.error}'));
        final items = snap.data ?? [];
        if (items.isEmpty) return const Center(child: Text('Galeri masih kosong.'));

        return GridView.builder(
          padding: const EdgeInsets.all(12),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            crossAxisSpacing: 12,
            mainAxisSpacing: 12,
            childAspectRatio: 0.85,
          ),
          itemCount: items.length,
          itemBuilder: (context, i) {
            final item = items[i];
            final url = MediaUrl.resolve(item['image_path']?.toString());

            return Card(
              clipBehavior: Clip.antiAlias,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Expanded(
                    child: url != null
                        ? Image.network(url, fit: BoxFit.cover, errorBuilder: (_, __, ___) => _placeholder())
                        : _placeholder(),
                  ),
                  Padding(
                    padding: const EdgeInsets.all(8),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(item['title']?.toString() ?? 'Foto', style: const TextStyle(fontWeight: FontWeight.w600)),
                        if (item['caption'] != null)
                          Text(item['caption'].toString(), style: Theme.of(context).textTheme.bodySmall, maxLines: 2, overflow: TextOverflow.ellipsis),
                      ],
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Widget _placeholder() {
    return Container(
      color: const Color(0xFF2A2A45),
      child: const Center(child: Icon(Icons.photo, size: 48, color: Colors.white54)),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (widget.embedded) return _buildBody();
    return Scaffold(appBar: AppBar(title: const Text('Galeri')), body: _buildBody());
  }
}
