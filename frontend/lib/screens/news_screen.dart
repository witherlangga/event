import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'news_detail_screen.dart';

class NewsScreen extends StatefulWidget {
  const NewsScreen({super.key});

  @override
  State<NewsScreen> createState() => _NewsScreenState();
}

class _NewsScreenState extends State<NewsScreen> {
  late Future<List<Map<String, dynamic>>> _future;

  @override
  void initState() {
    super.initState();
    _future = ApiService.instance.getNews();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Berita')),
      body: FutureBuilder<List<Map<String, dynamic>>>(
        future: _future,
        builder: (context, snap) {
          if (snap.connectionState != ConnectionState.done) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snap.hasError) return Center(child: Text('Error: ${snap.error}'));
          final posts = snap.data ?? [];
          if (posts.isEmpty) return const Center(child: Text('Belum ada berita.'));

          return ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: posts.length,
            itemBuilder: (context, i) {
              final post = posts[i];
              final id = post['id'] is int ? post['id'] as int : int.tryParse('${post['id']}') ?? 0;

              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                child: ListTile(
                  leading: const Icon(Icons.newspaper),
                  title: Text(post['title']?.toString() ?? 'Berita'),
                  subtitle: Text(post['excerpt']?.toString() ?? post['published_at']?.toString() ?? ''),
                  isThreeLine: true,
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => NewsDetailScreen(newsId: id)),
                  ),
                ),
              );
            },
          );
        },
      ),
    );
  }
}
