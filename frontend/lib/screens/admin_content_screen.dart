import 'dart:convert';
import 'package:flutter/material.dart';
import '../services/api_service.dart';

class AdminContentScreen extends StatefulWidget {
  const AdminContentScreen({super.key});

  @override
  State<AdminContentScreen> createState() => _AdminContentScreenState();
}

class _AdminContentScreenState extends State<AdminContentScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Kelola Konten'),
        bottom: TabBar(
          controller: _tabController,
          isScrollable: true,
          tabs: const [
            Tab(text: 'Anggota'),
            Tab(text: 'Album'),
            Tab(text: 'Berita'),
            Tab(text: 'Galeri'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: const [
          _AdminMembersTab(),
          _AdminAlbumsTab(),
          _AdminNewsTab(),
          _AdminGalleryTab(),
        ],
      ),
    );
  }
}

class _AdminMembersTab extends StatefulWidget {
  const _AdminMembersTab();

  @override
  State<_AdminMembersTab> createState() => _AdminMembersTabState();
}

class _AdminMembersTabState extends State<_AdminMembersTab> {
  late Future<List<Map<String, dynamic>>> _future;

  @override
  void initState() {
    super.initState();
    _refresh();
  }

  void _refresh() => setState(() => _future = ApiService.instance.fetchList('/admin/members'));

  Future<void> _addMember() async {
    final nameCtrl = TextEditingController();
    final roleCtrl = TextEditingController();
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Tambah Anggota'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(controller: nameCtrl, decoration: const InputDecoration(labelText: 'Nama')),
            TextField(controller: roleCtrl, decoration: const InputDecoration(labelText: 'Peran')),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Batal')),
          TextButton(onPressed: () => Navigator.pop(context, true), child: const Text('Simpan')),
        ],
      ),
    );
    if (ok == true && nameCtrl.text.isNotEmpty) {
      await ApiService.instance.post('/admin/members', body: {
        'name': nameCtrl.text,
        'role': roleCtrl.text,
        'is_active': true,
      });
      _refresh();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      floatingActionButton: FloatingActionButton(onPressed: _addMember, child: const Icon(Icons.add)),
      body: FutureBuilder<List<Map<String, dynamic>>>(
        future: _future,
        builder: (context, snap) {
          if (!snap.hasData) return const Center(child: CircularProgressIndicator());
          final items = snap.data!;
          return ListView.builder(
            itemCount: items.length,
            itemBuilder: (_, i) {
              final m = items[i];
              return ListTile(
                title: Text(m['name']?.toString() ?? ''),
                subtitle: Text(m['role']?.toString() ?? ''),
                trailing: IconButton(
                  icon: const Icon(Icons.delete_outline),
                  onPressed: () async {
                    await ApiService.instance.delete('/admin/members/${m['id']}');
                    _refresh();
                  },
                ),
              );
            },
          );
        },
      ),
    );
  }
}

class _AdminAlbumsTab extends StatefulWidget {
  const _AdminAlbumsTab();

  @override
  State<_AdminAlbumsTab> createState() => _AdminAlbumsTabState();
}

class _AdminAlbumsTabState extends State<_AdminAlbumsTab> {
  late Future<List<Map<String, dynamic>>> _future;

  @override
  void initState() {
    super.initState();
    _refresh();
  }

  void _refresh() => setState(() => _future = ApiService.instance.fetchList('/admin/albums'));

  Future<void> _addAlbum() async {
    final titleCtrl = TextEditingController();
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Tambah Album'),
        content: TextField(controller: titleCtrl, decoration: const InputDecoration(labelText: 'Judul Album')),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Batal')),
          TextButton(onPressed: () => Navigator.pop(context, true), child: const Text('Simpan')),
        ],
      ),
    );
    if (ok == true && titleCtrl.text.isNotEmpty) {
      final res = await ApiService.instance.post('/admin/albums', body: {'title': titleCtrl.text, 'is_active': true});
      if (res.statusCode == 200 || res.statusCode == 201) {
        final data = jsonDecode(res.body);
        final album = ApiService.extractObject(data);
        if (album != null && album['id'] != null) {
          await ApiService.instance.post('/admin/albums/${album['id']}/songs', body: {
            'title': 'Track 1',
            'track_number': 1,
            'is_active': true,
          });
        }
      }
      _refresh();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      floatingActionButton: FloatingActionButton(onPressed: _addAlbum, child: const Icon(Icons.add)),
      body: FutureBuilder<List<Map<String, dynamic>>>(
        future: _future,
        builder: (context, snap) {
          if (!snap.hasData) return const Center(child: CircularProgressIndicator());
          final albums = snap.data!;
          return ListView.builder(
            itemCount: albums.length,
            itemBuilder: (_, i) {
              final a = albums[i];
              final songCount = a['songs'] is List ? (a['songs'] as List).length : 0;
              return ListTile(
                title: Text(a['title']?.toString() ?? ''),
                subtitle: Text('$songCount lagu'),
                trailing: IconButton(
                  icon: const Icon(Icons.delete_outline),
                  onPressed: () async {
                    await ApiService.instance.delete('/admin/albums/${a['id']}');
                    _refresh();
                  },
                ),
              );
            },
          );
        },
      ),
    );
  }
}

class _AdminNewsTab extends StatefulWidget {
  const _AdminNewsTab();

  @override
  State<_AdminNewsTab> createState() => _AdminNewsTabState();
}

class _AdminNewsTabState extends State<_AdminNewsTab> {
  late Future<List<Map<String, dynamic>>> _future;

  @override
  void initState() {
    super.initState();
    _refresh();
  }

  void _refresh() => setState(() => _future = ApiService.instance.fetchList('/admin/news'));

  Future<void> _addNews() async {
    final titleCtrl = TextEditingController();
    final bodyCtrl = TextEditingController();
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Tambah Berita'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(controller: titleCtrl, decoration: const InputDecoration(labelText: 'Judul')),
            TextField(controller: bodyCtrl, decoration: const InputDecoration(labelText: 'Isi'), maxLines: 3),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Batal')),
          TextButton(onPressed: () => Navigator.pop(context, true), child: const Text('Publikasi')),
        ],
      ),
    );
    if (ok == true && titleCtrl.text.isNotEmpty) {
      await ApiService.instance.post('/admin/news', body: {
        'title': titleCtrl.text,
        'body': bodyCtrl.text.isEmpty ? titleCtrl.text : bodyCtrl.text,
        'is_published': true,
        'published_at': DateTime.now().toIso8601String(),
      });
      _refresh();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      floatingActionButton: FloatingActionButton(onPressed: _addNews, child: const Icon(Icons.add)),
      body: FutureBuilder<List<Map<String, dynamic>>>(
        future: _future,
        builder: (context, snap) {
          if (!snap.hasData) return const Center(child: CircularProgressIndicator());
          final posts = snap.data!;
          return ListView.builder(
            itemCount: posts.length,
            itemBuilder: (_, i) {
              final p = posts[i];
              return ListTile(
                title: Text(p['title']?.toString() ?? ''),
                subtitle: Text(p['published_at']?.toString() ?? 'Draft'),
                trailing: IconButton(
                  icon: const Icon(Icons.delete_outline),
                  onPressed: () async {
                    await ApiService.instance.delete('/admin/news/${p['id']}');
                    _refresh();
                  },
                ),
              );
            },
          );
        },
      ),
    );
  }
}

class _AdminGalleryTab extends StatefulWidget {
  const _AdminGalleryTab();

  @override
  State<_AdminGalleryTab> createState() => _AdminGalleryTabState();
}

class _AdminGalleryTabState extends State<_AdminGalleryTab> {
  late Future<List<Map<String, dynamic>>> _future;

  @override
  void initState() {
    super.initState();
    _refresh();
  }

  void _refresh() => setState(() => _future = ApiService.instance.fetchList('/admin/gallery'));

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<List<Map<String, dynamic>>>(
      future: _future,
      builder: (context, snap) {
        if (!snap.hasData) return const Center(child: CircularProgressIndicator());
        final items = snap.data!;
        if (items.isEmpty) {
          return const Center(child: Text('Upload galeri via API admin (multipart) atau tambahkan dari backend.'));
        }
        return ListView.builder(
          itemCount: items.length,
          itemBuilder: (_, i) {
            final g = items[i];
            return ListTile(
              title: Text(g['title']?.toString() ?? 'Foto'),
              subtitle: Text(g['caption']?.toString() ?? ''),
              trailing: IconButton(
                icon: const Icon(Icons.delete_outline),
                onPressed: () async {
                  await ApiService.instance.delete('/admin/gallery/${g['id']}');
                  _refresh();
                },
              ),
            );
          },
        );
      },
    );
  }
}
