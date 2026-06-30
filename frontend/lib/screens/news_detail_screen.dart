import 'dart:convert';
import 'package:flutter/material.dart';
import '../services/api_service.dart';

class NewsDetailScreen extends StatefulWidget {
  final int newsId;

  const NewsDetailScreen({super.key, required this.newsId});

  @override
  State<NewsDetailScreen> createState() => _NewsDetailScreenState();
}

class _NewsDetailScreenState extends State<NewsDetailScreen> {
  late Future<Map<String, dynamic>> _future;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<Map<String, dynamic>> _load() async {
    final res = await ApiService.instance.getNewsDetail(widget.newsId);
    if (res.statusCode == 200) {
      final obj = ApiService.extractObject(jsonDecode(res.body));
      if (obj != null) return obj;
    }
    throw Exception('Gagal memuat berita');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Detail Berita')),
      body: FutureBuilder<Map<String, dynamic>>(
        future: _future,
        builder: (context, snap) {
          if (snap.connectionState != ConnectionState.done) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snap.hasError) return Center(child: Text('Error: ${snap.error}'));
          final post = snap.data!;

          return SingleChildScrollView(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(post['title']?.toString() ?? 'Berita', style: Theme.of(context).textTheme.headlineSmall),
                const SizedBox(height: 8),
                Text(post['published_at']?.toString() ?? '', style: Theme.of(context).textTheme.bodySmall),
                const SizedBox(height: 16),
                Text(post['body']?.toString() ?? '', style: Theme.of(context).textTheme.bodyMedium),
              ],
            ),
          );
        },
      ),
    );
  }
}
