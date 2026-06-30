import 'dart:convert';
import 'package:flutter/material.dart';
import '../constants/app_constants.dart';
import '../services/api_service.dart';
import 'members_screen.dart';

class BandProfileScreen extends StatefulWidget {
  const BandProfileScreen({super.key});

  @override
  State<BandProfileScreen> createState() => _BandProfileScreenState();
}

class _BandProfileScreenState extends State<BandProfileScreen> {
  late Future<Map<String, dynamic>?> _profileFuture;
  late Future<List<Map<String, dynamic>>> _membersFuture;

  @override
  void initState() {
    super.initState();
    _profileFuture = _loadProfile();
    _membersFuture = ApiService.instance.getBandMembers();
  }

  Future<Map<String, dynamic>?> _loadProfile() async {
    final res = await ApiService.instance.get('/band/profile');
    if (res.statusCode == 200) {
      return ApiService.extractObject(jsonDecode(res.body));
    }
    return null;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Profil Band')),
      body: FutureBuilder<Map<String, dynamic>?>(
        future: _profileFuture,
        builder: (context, snap) {
          if (snap.connectionState != ConnectionState.done) {
            return const Center(child: CircularProgressIndicator());
          }
          final profile = snap.data;
          if (profile == null) {
            return Center(child: Text('Profil ${AppConstants.bandName} belum tersedia.'));
          }

          final social = profile['social_links'];
          final links = social is Map ? Map<String, dynamic>.from(social.cast<String, dynamic>()) : <String, dynamic>{};

          return SingleChildScrollView(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: CircleAvatar(
                    radius: 48,
                    backgroundColor: Theme.of(context).colorScheme.primary,
                    child: Text(
                      (profile['name']?.toString() ?? AppConstants.bandName)[0].toUpperCase(),
                      style: const TextStyle(fontSize: 36, fontWeight: FontWeight.bold, color: Colors.white),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Center(
                  child: Text(profile['name']?.toString() ?? AppConstants.bandName, style: Theme.of(context).textTheme.headlineSmall, textAlign: TextAlign.center),
                ),
                if (profile['genre'] != null) ...[
                  const SizedBox(height: 4),
                  Center(child: Text(profile['genre'].toString(), style: Theme.of(context).textTheme.bodySmall)),
                ],
                if (profile['formed_year'] != null) ...[
                  const SizedBox(height: 4),
                  Center(child: Text('Dibentuk ${profile['formed_year']}', style: Theme.of(context).textTheme.bodySmall)),
                ],
                const SizedBox(height: 24),
                Text('Tentang', style: Theme.of(context).textTheme.titleMedium),
                const SizedBox(height: 8),
                Text(profile['bio']?.toString() ?? '', style: Theme.of(context).textTheme.bodyMedium),
                const SizedBox(height: 24),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text('Anggota', style: Theme.of(context).textTheme.titleMedium),
                    TextButton(onPressed: () {
                      Navigator.push(context, MaterialPageRoute(builder: (_) => const MembersScreen()));
                    }, child: const Text('Lihat semua')),
                  ],
                ),
                FutureBuilder<List<Map<String, dynamic>>>(
                  future: _membersFuture,
                  builder: (context, memberSnap) {
                    if (!memberSnap.hasData) return const SizedBox.shrink();
                    final members = memberSnap.data!.take(4).toList();
                    return Column(
                      children: members.map((m) => ListTile(
                        contentPadding: EdgeInsets.zero,
                        leading: CircleAvatar(child: Text((m['name']?.toString() ?? '?')[0])),
                        title: Text(m['name']?.toString() ?? ''),
                        subtitle: Text(m['role']?.toString() ?? ''),
                      )).toList(),
                    );
                  },
                ),
                if (links.isNotEmpty) ...[
                  const SizedBox(height: 24),
                  Text('Media Sosial', style: Theme.of(context).textTheme.titleMedium),
                  const SizedBox(height: 8),
                  ...links.entries.map((e) => ListTile(
                        contentPadding: EdgeInsets.zero,
                        leading: const Icon(Icons.link),
                        title: Text(e.key),
                        subtitle: Text(e.value.toString()),
                      )),
                ],
              ],
            ),
          );
        },
      ),
    );
  }
}
