import 'dart:convert';
import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'order_result_screen.dart';

class EventDetailScreen extends StatefulWidget {
  final int eventId;
  const EventDetailScreen({super.key, required this.eventId});

  @override
  State<EventDetailScreen> createState() => _EventDetailScreenState();
}

class _TicketTypesList extends StatefulWidget {
  final Map<String, dynamic> event;
  const _TicketTypesList({required this.event});

  @override
  State<_TicketTypesList> createState() => _TicketTypesListState();
}

class _TicketTypesListState extends State<_TicketTypesList> {
  Map<int, int> qty = {};
  bool _loading = false;
  String? _error;

  List<Map<String, dynamic>> _typesFrom(Map<String, dynamic> event) {
    final dynamic t1 = event['ticket_types'] ?? event['ticketTypes'];
    final List<Map<String, dynamic>> result = [];
    if (t1 is List) {
      for (final e in t1) {
        if (e is Map) {
          result.add(Map<String, dynamic>.from(e.map((k, v) => MapEntry(k.toString(), v))));
        }
      }
    }
    return result;
  }

  Future<void> _purchase(int eventId, int ticketTypeId) async {
    final q = qty[ticketTypeId] ?? 1;
    setState(() { _loading = true; _error = null; });
    try {
      final res = await ApiService.instance.postPurchase(eventId, {'ticket_type_id': ticketTypeId, 'quantity': q});
      if (res.statusCode == 201 || res.statusCode == 200) {
        final parsed = jsonDecode(res.body);
        final tickets = parsed is Map && parsed['tickets'] is List ? List<Map<String, dynamic>>.from(parsed['tickets'].cast<Map<String, dynamic>>()) : <Map<String, dynamic>>[];
        Navigator.of(context).push(MaterialPageRoute(builder: (_) => OrderResultScreen(tickets: tickets)));
      } else {
        setState(() { _error = 'Purchase failed: ${res.statusCode}'; });
      }
    } catch (e) {
      setState(() { _error = e.toString(); });
    } finally {
      setState(() { _loading = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    final types = _typesFrom(widget.event);
    if (types.isEmpty) return const Text('No ticket types');
    return Column(
      children: [
        if (_error != null) Text(_error!, style: const TextStyle(color: Colors.red)),
        for (final t in types)
          Card(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            elevation: 2,
            child: Padding(
              padding: const EdgeInsets.all(12.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(child: Text(t['name'] ?? 'Ticket', style: const TextStyle(fontWeight: FontWeight.w600))),
                      Text('Rp ${t['price'] ?? '0'}', style: const TextStyle(fontWeight: FontWeight.w700)),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text('Available: ${t['quota'] != null ? (t['quota'] - (t['sold'] ?? 0)) : 'unlimited'}', style: Theme.of(context).textTheme.bodySmall),
                  const SizedBox(height: 10),
                  Row(children: [
                    IconButton(icon: const Icon(Icons.remove_circle_outline), onPressed: () { setState(() { qty[t['id'] as int] = (qty[t['id'] as int] ?? 1) - 1; if (qty[t['id'] as int]! < 1) qty[t['id'] as int] = 1; }); }),
                    Text('${qty[t['id'] as int] ?? 1}', style: const TextStyle(fontSize: 16)),
                    IconButton(icon: const Icon(Icons.add_circle_outline), onPressed: () { setState(() { qty[t['id'] as int] = (qty[t['id'] as int] ?? 1) + 1; }); }),
                    const Spacer(),
                  ]),
                  const SizedBox(height: 8),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: _loading ? null : () {
                        final rawEventId = widget.event['id'];
                        final eventId = rawEventId is int ? rawEventId : (rawEventId != null ? (int.tryParse(rawEventId.toString()) ?? 0) : 0);
                        if (eventId == 0) {
                          setState(() { _error = 'Invalid event ID'; });
                          return;
                        }
                        _purchase(eventId, t['id'] as int);
                      },
                      style: ElevatedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 12), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8))),
                      child: _loading ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)) : const Text('Buy Ticket'),
                    ),
                  ),
                ],
              ),
            ),
          ),
      ],
    );
  }
}

class _EventDetailScreenState extends State<EventDetailScreen> {
  late Future<Map<String, dynamic>> _eventFuture;

  @override
  void initState() {
    super.initState();
    _eventFuture = _loadEvent();
  }

  Future<Map<String, dynamic>> _loadEvent() async {
    final res = await ApiService.instance.getEvent(widget.eventId);
    if (res.statusCode == 200) {
      final data = jsonDecode(res.body);
      if (data is Map<String, dynamic>) return data;
      if (data is Map && data['data'] is Map) return Map<String, dynamic>.from(data['data']);
    }
    throw Exception('Failed to load event: ${res.statusCode}');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Event Detail')),
      body: FutureBuilder<Map<String, dynamic>>(
        future: _eventFuture,
        builder: (context, snap) {
          if (snap.connectionState != ConnectionState.done) return const Center(child: CircularProgressIndicator());
          if (snap.hasError) return Center(child: Text('Error: ${snap.error}'));
          final e = snap.data!;
          final rawId = e['id'];
          final id = rawId is int ? rawId : (rawId != null ? (int.tryParse(rawId.toString()) ?? 0) : 0);
          return Padding(
            padding: const EdgeInsets.all(16.0),
            child: SingleChildScrollView(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (e['cover_path'] != null && e['cover_path'].toString().isNotEmpty)
                    Hero(
                      tag: 'event-cover-$id',
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(12),
                        child: Image.network(e['cover_path'].toString(), width: double.infinity, height: 200, fit: BoxFit.cover),
                      ),
                    ),
                  const SizedBox(height: 12),
                  Card(
                    elevation: 4,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    child: Padding(
                      padding: const EdgeInsets.all(12.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(e['title']?.toString() ?? e['name']?.toString() ?? 'Event', style: Theme.of(context).textTheme.titleLarge),
                          const SizedBox(height: 8),
                          Text(e['short_description']?.toString() ?? '', style: Theme.of(context).textTheme.bodyMedium),
                          const SizedBox(height: 10),
                          Row(children: [
                            const Icon(Icons.calendar_today, size: 16, color: Colors.grey),
                            const SizedBox(width: 6),
                            Text('${e['date'] ?? e['starts_at'] ?? 'TBA'}'),
                            const SizedBox(width: 12),
                            const Icon(Icons.place, size: 16, color: Colors.grey),
                            const SizedBox(width: 6),
                            Expanded(child: Text(e['location_name']?.toString() ?? e['location']?.toString() ?? 'Location')),
                          ])
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text('About', style: Theme.of(context).textTheme.titleMedium),
                  const SizedBox(height: 8),
                  Card(
                    child: Padding(padding: const EdgeInsets.all(12.0), child: Text(e['description']?.toString() ?? '')),
                  ),
                  const SizedBox(height: 16),
                  Text('Ticket Types', style: Theme.of(context).textTheme.titleMedium),
                  const SizedBox(height: 8),
                  _TicketTypesList(event: e),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
