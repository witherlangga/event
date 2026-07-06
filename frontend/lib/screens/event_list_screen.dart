import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import '../services/api_service.dart';
import '../widgets/event_card.dart';
import '../widgets/event_card_skeleton.dart';

class EventListScreen extends StatefulWidget {
  final bool embedded;

  const EventListScreen({super.key, this.embedded = false});

  @override
  State<EventListScreen> createState() => _EventListScreenState();
}

class _EventListScreenState extends State<EventListScreen> {
  late Future<List<Map<String, dynamic>>> _eventsFuture;

  String? _filterKeyword;
  DateTime? _filterDateFrom;
  DateTime? _filterDateTo;

  @override
  void initState() {
    super.initState();
    _eventsFuture = _loadEvents();
  }

  Future<List<Map<String, dynamic>>> _loadEvents() async {
    final params = <String, String>{};
    if (_filterKeyword != null && _filterKeyword!.isNotEmpty) {
      params['q'] = _filterKeyword!;
    }
    if (_filterDateFrom != null) {
      params['date_from'] = _filterDateFrom!.toIso8601String().split('T').first;
    }
    if (_filterDateTo != null) {
      params['date_to'] = _filterDateTo!.toIso8601String().split('T').first;
    }
    final query = params.isNotEmpty
        ? params.entries.map((e) => '${Uri.encodeComponent(e.key)}=${Uri.encodeComponent(e.value)}').join('&')
        : null;
    final res = await ApiService.instance.getEvents(query: query);
    if (res.statusCode == 200) {
      final data = jsonDecode(res.body);
      return ApiService.extractList(data);
    }
    throw Exception('Gagal memuat konser: ${res.statusCode}');
  }

  String _formatDate(DateTime? date) {
    if (date == null) return 'Semua';
    return '${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}';
  }

  Future<void> _showFilterDialog() async {
    final keywordController = TextEditingController(text: _filterKeyword);
    DateTime? tempFrom = _filterDateFrom;
    DateTime? tempTo = _filterDateTo;
    final result = await showDialog<bool?>(
      context: context,
      builder: (_) {
        return StatefulBuilder(builder: (context, setDialogState) {
          return AlertDialog(
            title: const Text('Filter Konser'),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: keywordController,
                  decoration: const InputDecoration(labelText: 'Kata kunci'),
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () async {
                          final selected = await showDatePicker(
                            context: context,
                            initialDate: tempFrom ?? DateTime.now(),
                            firstDate: DateTime(2020),
                            lastDate: DateTime(2100),
                          );
                          if (selected != null) setDialogState(() => tempFrom = selected);
                        },
                        child: Text('Dari: ${_formatDate(tempFrom)}'),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () async {
                          final selected = await showDatePicker(
                            context: context,
                            initialDate: tempTo ?? DateTime.now(),
                            firstDate: DateTime(2020),
                            lastDate: DateTime(2100),
                          );
                          if (selected != null) setDialogState(() => tempTo = selected);
                        },
                        child: Text('Sampai: ${_formatDate(tempTo)}'),
                      ),
                    ),
                  ],
                ),
              ],
            ),
            actions: [
              TextButton(onPressed: () => Navigator.of(context).pop(null), child: const Text('Batal')),
              TextButton(
                onPressed: () {
                  _filterKeyword = keywordController.text.trim().isEmpty ? null : keywordController.text.trim();
                  _filterDateFrom = tempFrom;
                  _filterDateTo = tempTo;
                  Navigator.of(context).pop(true);
                },
                child: const Text('Terapkan'),
              ),
            ],
          );
        });
      },
    );
    if (result == true) setState(() => _eventsFuture = _loadEvents());
  }

  Future<void> _searchNearby() async {
    final serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      if (!mounted) return;
      showDialog(
        context: context,
        builder: (_) => AlertDialog(
          title: const Text('Lokasi nonaktif'),
          content: const Text('Aktifkan layanan lokasi untuk mencari konser terdekat.'),
          actions: [TextButton(onPressed: () => Navigator.of(context).pop(), child: const Text('OK'))],
        ),
      );
      return;
    }
    var permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) permission = await Geolocator.requestPermission();
    if (permission == LocationPermission.denied || permission == LocationPermission.deniedForever) {
      if (!mounted) return;
      showDialog(
        context: context,
        builder: (_) => AlertDialog(
          title: const Text('Izin ditolak'),
          content: const Text('Izin lokasi diperlukan untuk mencari konser terdekat.'),
          actions: [TextButton(onPressed: () => Navigator.of(context).pop(), child: const Text('OK'))],
        ),
      );
      return;
    }
    final pos = await Geolocator.getCurrentPosition(desiredAccuracy: LocationAccuracy.best);
    setState(() => _eventsFuture = ApiService.instance.getEventsWithLocation(pos.latitude, pos.longitude));
  }

  Widget _buildBody() {
    return FutureBuilder<List<Map<String, dynamic>>>(
      future: _eventsFuture,
      builder: (context, snap) {
        if (snap.connectionState == ConnectionState.waiting) {
          return ListView.builder(
            padding: const EdgeInsets.only(top: 12, bottom: 12),
            itemCount: widget.embedded ? 3 : 6,
            itemBuilder: (context, i) => const EventCardSkeleton(),
          );
        }
        if (snap.hasError) return Center(child: Text('Error: ${snap.error}'));
        final events = snap.data ?? [];
        final filtersActive = (_filterKeyword != null && _filterKeyword!.isNotEmpty) ||
            _filterDateFrom != null ||
            _filterDateTo != null;

        if (events.isEmpty) {
          return Column(
            children: [
              if (filtersActive) _buildFilterChips(filtersActive),
              const Expanded(child: Center(child: Text('Tidak ada konser ditemukan'))),
            ],
          );
        }

        return Column(
          children: [
            if (filtersActive) _buildFilterChips(filtersActive),
            Expanded(
              child: ListView.builder(
                padding: const EdgeInsets.only(top: 12, bottom: 12),
                itemCount: events.length,
                itemBuilder: (context, i) {
                  final e = events[i];
                  final distance = e['distance_km'] != null
                      ? (double.tryParse(e['distance_km'].toString()) ?? 0.0)
                      : null;
                  return EventCard(event: e, distanceKm: distance);
                },
              ),
            ),
          ],
        );
      },
    );
  }

  Widget _buildFilterChips(bool filtersActive) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 12.0),
      child: Row(
        children: [
          Expanded(
            child: Wrap(
              spacing: 8,
              runSpacing: 4,
              children: [
                if (_filterKeyword != null && _filterKeyword!.isNotEmpty)
                  Chip(label: Text('Kata kunci: $_filterKeyword')),
                if (_filterDateFrom != null) Chip(label: Text('Dari: ${_formatDate(_filterDateFrom)}')),
                if (_filterDateTo != null) Chip(label: Text('Sampai: ${_formatDate(_filterDateTo)}')),
              ],
            ),
          ),
          TextButton(
            onPressed: () {
              setState(() {
                _filterKeyword = null;
                _filterDateFrom = null;
                _filterDateTo = null;
                _eventsFuture = _loadEvents();
              });
            },
            child: const Text('Reset'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (widget.embedded) return _buildBody();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Konser'),
        actions: [
          IconButton(icon: const Icon(Icons.refresh), onPressed: () => setState(() => _eventsFuture = _loadEvents())),
          IconButton(icon: const Icon(Icons.filter_list), onPressed: _showFilterDialog),
          IconButton(icon: const Icon(Icons.my_location), onPressed: _searchNearby),
        ],
      ),
      body: _buildBody(),
    );
  }
}
