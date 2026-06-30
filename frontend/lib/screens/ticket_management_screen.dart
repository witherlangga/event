import 'dart:convert';
import 'dart:typed_data';
import 'package:flutter/material.dart';
import '../services/api_service.dart';

class TicketManagementScreen extends StatefulWidget {
  const TicketManagementScreen({super.key});

  @override
  State<TicketManagementScreen> createState() => _TicketManagementScreenState();
}

class _TicketManagementScreenState extends State<TicketManagementScreen> {
  late Future<List<Map<String, dynamic>>> _ticketsFuture;
  final Map<int, Uint8List?> _qrCache = {};
  final Map<int, bool> _loadingQr = {};

  @override
  void initState() {
    super.initState();
    _ticketsFuture = _loadTickets();
  }

  Future<List<Map<String, dynamic>>> _loadTickets() async {
    final res = await ApiService.instance.getOrders();
    if (res.statusCode != 200) {
      throw Exception('Failed to load tickets: ${res.statusCode}');
    }
    final data = jsonDecode(res.body);
    final orders = data is List
        ? List<Map<String, dynamic>>.from(data.cast<Map<String, dynamic>>())
        : data is Map && data['data'] is List
            ? List<Map<String, dynamic>>.from(data['data'].cast<Map<String, dynamic>>())
            : <Map<String, dynamic>>[];

    final List<Map<String, dynamic>> tickets = [];
    for (final order in orders) {
      final orderTickets = order['tickets'];
      if (orderTickets is List) {
        for (final rawTicket in orderTickets) {
          if (rawTicket is Map<String, dynamic>) {
            tickets.add({
              'order_id': order['id'],
              'order_status': order['status'],
              'order_total': order['total_price'],
              ...rawTicket,
            });
          }
        }
      }
    }

    return tickets;
  }

  Future<void> _loadQr(int ticketId) async {
    setState(() { _loadingQr[ticketId] = true; });
    final res = await ApiService.instance.getTicketQr(ticketId);
    if (res.statusCode == 200) {
      setState(() { _qrCache[ticketId] = res.bodyBytes; });
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Failed to load QR: ${res.statusCode}')));
    }
    setState(() { _loadingQr[ticketId] = false; });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Tiket Saya')),
      body: FutureBuilder<List<Map<String, dynamic>>>(
        future: _ticketsFuture,
        builder: (context, snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snap.hasError) {
            return Center(child: Text('Error: ${snap.error}'));
          }
          final tickets = snap.data ?? [];
          if (tickets.isEmpty) {
            return const Center(child: Text('Belum ada tiket'));
          }
          return ListView.builder(
            padding: const EdgeInsets.all(12),
            itemCount: tickets.length,
            itemBuilder: (context, index) {
              final ticket = tickets[index];
              final ticketId = ticket['id'] is int ? ticket['id'] as int : int.tryParse(ticket['id']?.toString() ?? '') ?? 0;
              final qrBytes = _qrCache[ticketId];
              return Card(
                margin: const EdgeInsets.symmetric(vertical: 8),
                child: Padding(
                  padding: const EdgeInsets.all(12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Ticket #${ticket['id'] ?? ''}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                      const SizedBox(height: 6),
                      Text('Order: #${ticket['order_id'] ?? '-'} • Status: ${ticket['order_status'] ?? '-'}'),
                      const SizedBox(height: 6),
                      Text('Code: ${ticket['code'] ?? '-'}'),
                      const SizedBox(height: 10),
                      if (qrBytes != null)
                        Image.memory(qrBytes, height: 180, fit: BoxFit.contain)
                      else
                        ElevatedButton(
                          onPressed: _loadingQr[ticketId] == true ? null : () => _loadQr(ticketId),
                          child: _loadingQr[ticketId] == true ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)) : const Text('View QR'),
                        ),
                    ],
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
