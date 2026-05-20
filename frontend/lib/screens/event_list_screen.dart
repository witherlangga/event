import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';
import 'organizer_add_event_screen.dart';
import '../widgets/event_card.dart';
import '../widgets/event_card_skeleton.dart';

class EventListScreen extends StatefulWidget {
  const EventListScreen({super.key});

  @override
  State<EventListScreen> createState() => _EventListScreenState();
}

class _EventListScreenState extends State<EventListScreen> {
  late Future<List<Map<String, dynamic>>> _eventsFuture;

  @override
  void initState() {
    super.initState();
    _eventsFuture = _loadEvents();
  }

  Future<List<Map<String, dynamic>>> _loadEvents() async {
    final res = await ApiService.instance.getEvents();
    if (res.statusCode == 200) {
      final data = jsonDecode(res.body);
      if (data is List) {
        return List<Map<String, dynamic>>.from(data.cast<Map<String, dynamic>>());
      }
      if (data is Map && data['data'] is List) {
        return List<Map<String, dynamic>>.from(data['data'].cast<Map<String, dynamic>>());
      }
    }
    throw Exception('Failed to load events: ${res.statusCode}');
  }

  @override
  Widget build(BuildContext context) {
    return Consumer(builder: (context, ref, _) {
      final auth = ref.watch(authNotifierProvider);
      final isOrganizer = auth.user?.role == 'organizer';

      return Scaffold(
        appBar: AppBar(title: const Text('Events'), actions: [
          IconButton(
            icon: const Icon(Icons.my_location),
            onPressed: () async {
              final serviceEnabled = await Geolocator.isLocationServiceEnabled();
              if (!serviceEnabled) {
                showDialog(context: context, builder: (_) => AlertDialog(title: const Text('Location disabled'), content: const Text('Please enable location services'), actions: [TextButton(onPressed: () => Navigator.of(context).pop(), child: const Text('OK'))]));
                return;
              }
              var permission = await Geolocator.checkPermission();
              if (permission == LocationPermission.denied) permission = await Geolocator.requestPermission();
              if (permission == LocationPermission.denied || permission == LocationPermission.deniedForever) {
                showDialog(context: context, builder: (_) => AlertDialog(title: const Text('Permission denied'), content: const Text('Location permission is required to search nearby events'), actions: [TextButton(onPressed: () => Navigator.of(context).pop(), child: const Text('OK'))]));
                return;
              }
              final pos = await Geolocator.getCurrentPosition(desiredAccuracy: LocationAccuracy.best);
              final lat = pos.latitude;
              final lng = pos.longitude;
              setState(() { _eventsFuture = ApiService.instance.getEventsWithLocation(lat, lng); });
            },
          ),
          IconButton(
            icon: const Icon(Icons.search),
            onPressed: () async {
              // prompt for lat,lng input
              final result = await showDialog<Map<String, double>?>(context: context, builder: (_) {
                final latController = TextEditingController();
                final lngController = TextEditingController();
                return AlertDialog(
                  title: const Text('Find near (km)'),
                  content: Column(mainAxisSize: MainAxisSize.min, children: [
                    TextField(controller: latController, decoration: const InputDecoration(labelText: 'Latitude')),
                    TextField(controller: lngController, decoration: const InputDecoration(labelText: 'Longitude')),
                    const SizedBox(height: 8),
                    const Text('Search radius: 10 km (fixed)'),
                  ]),
                  actions: [
                    TextButton(onPressed: () => Navigator.of(context).pop(null), child: const Text('Cancel')),
                    TextButton(onPressed: () {
                      final lat = double.tryParse(latController.text);
                      final lng = double.tryParse(lngController.text);
                      if (lat == null || lng == null) return;
                      Navigator.of(context).pop({'lat': lat, 'lng': lng});
                    }, child: const Text('Search')),
                  ],
                );
              });

              if (result != null) {
                final _searchLat = result['lat'];
                final _searchLng = result['lng'];
                setState(() { _eventsFuture = ApiService.instance.getEventsWithLocation(_searchLat!, _searchLng!); });
              }
            },
          ),
        ]),
        floatingActionButton: isOrganizer
            ? FloatingActionButton(
                child: const Icon(Icons.add),
                onPressed: () async {
                  await Navigator.of(context).push(MaterialPageRoute(builder: (_) => const OrganizerAddEventScreen()));
                  // after returning, refresh list
                  setState(() { _eventsFuture = _loadEvents(); });
                },
              )
            : null,
        body: FutureBuilder<List<Map<String, dynamic>>>(
          future: _eventsFuture,
          builder: (context, snap) {
            if (snap.connectionState == ConnectionState.waiting) {
              return ListView.builder(
                padding: const EdgeInsets.only(top: 12, bottom: 12),
                itemCount: 6,
                itemBuilder: (context, i) => const EventCardSkeleton(),
              );
            }
            if (snap.hasError) return Center(child: Text('Error: ${snap.error}'));
            final events = snap.data ?? [];
            if (events.isEmpty) return const Center(child: Text('No events found'));
            return ListView.builder(
              padding: const EdgeInsets.only(top: 12, bottom: 12),
              itemCount: events.length,
              itemBuilder: (context, i) {
                final e = events[i];
                final distance = e['distance_km'] != null ? (double.tryParse(e['distance_km'].toString()) ?? 0.0) : null;
                return EventCard(event: e, distanceKm: distance);
              },
            );
          },
        ),
      );
    });
  }
}
