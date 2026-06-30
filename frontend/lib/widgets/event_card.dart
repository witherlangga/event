import 'package:flutter/material.dart';
import '../screens/event_detail_screen.dart';

class EventCard extends StatelessWidget {
  final Map<String, dynamic> event;
  final double? distanceKm;
  const EventCard({super.key, required this.event, this.distanceKm});

  @override
  Widget build(BuildContext context) {
    final id = event['id'] is int ? event['id'] as int : int.tryParse(event['id'].toString()) ?? 0;
    final title = event['title'] ?? event['name'] ?? 'Konser';
    final subtitle = event['short_description'] ?? event['location_name'] ?? '';
    final cover = event['cover_path'];

    return GestureDetector(
      onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => EventDetailScreen(eventId: id))),
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        child: Material(
          borderRadius: BorderRadius.circular(12),
          elevation: 6,
          color: Theme.of(context).cardColor,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (cover != null && cover.toString().isNotEmpty)
                Hero(
                  tag: 'event-cover-$id',
                  child: ClipRRect(
                    borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
                    child: Stack(
                      children: [
                        Image.network(cover.toString(), width: double.infinity, height: 180, fit: BoxFit.cover),
                        Positioned.fill(
                          child: DecoratedBox(
                            decoration: BoxDecoration(
                              gradient: LinearGradient(colors: [Colors.transparent, const Color.fromRGBO(0, 0, 0, 0.45)], begin: Alignment.topCenter, end: Alignment.bottomCenter),
                            ),
                          ),
                        ),
                        Positioned(
                          left: 12,
                          bottom: 12,
                          right: 12,
                          child: Row(
                            children: [
                              Expanded(child: Text(title.toString(), style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w700))),
                              if (distanceKm != null)
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
                                  decoration: BoxDecoration(color: Theme.of(context).colorScheme.secondary, borderRadius: BorderRadius.circular(8)),
                                  child: Text('${distanceKm!.toStringAsFixed(1)} km', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600)),
                                ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                )
              else
                Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Row(
                    children: [
                      Expanded(child: Text(title.toString(), style: Theme.of(context).textTheme.titleLarge)),
                      if (distanceKm != null) Text('${distanceKm!.toStringAsFixed(1)} km', style: const TextStyle(color: Colors.black54)),
                    ],
                  ),
                ),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 12.0, vertical: 12),
                child: Text(subtitle.toString(), style: Theme.of(context).textTheme.bodySmall),
              ),
              const SizedBox(height: 8),
            ],
          ),
        ),
      ),
    );
  }
}
