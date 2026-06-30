import 'dart:convert';
import 'dart:typed_data';
import 'package:flutter/material.dart';
import '../services/api_service.dart';

class OrderHistoryScreen extends StatefulWidget {
  const OrderHistoryScreen({super.key});

  @override
  State<OrderHistoryScreen> createState() => _OrderHistoryScreenState();
}

class _OrderHistoryScreenState extends State<OrderHistoryScreen> {
  late Future<List<Map<String, dynamic>>> _ordersFuture;

  @override
  void initState() {
    super.initState();
    _ordersFuture = _loadOrders();
  }

  Future<List<Map<String, dynamic>>> _loadOrders() async {
    final res = await ApiService.instance.getOrders();
    if (res.statusCode == 200) {
      final data = jsonDecode(res.body);
      if (data is List) return List<Map<String, dynamic>>.from(data.cast<Map<String, dynamic>>());
      if (data is Map && data['data'] is List) return List<Map<String, dynamic>>.from(data['data'].cast<Map<String, dynamic>>());
    }
    throw Exception('Failed to load orders: ${res.statusCode}');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Riwayat Pesanan')),
      body: FutureBuilder<List<Map<String, dynamic>>>(
        future: _ordersFuture,
        builder: (context, snap) {
          if (snap.connectionState == ConnectionState.waiting) return const Center(child: CircularProgressIndicator());
          if (snap.hasError) return Center(child: Text('Error: ${snap.error}'));
          final orders = snap.data ?? [];
          if (orders.isEmpty) return const Center(child: Text('Belum ada pesanan'));
          return ListView.builder(
            padding: const EdgeInsets.all(12),
            itemCount: orders.length,
            itemBuilder: (context, i) {
              final order = orders[i];
              return Card(
                margin: const EdgeInsets.symmetric(vertical: 8),
                child: ListTile(
                  title: Text('Order #${order['id'] ?? ''}'),
                  subtitle: Text('Status: ${order['status'] ?? 'unknown'}\nTotal: Rp ${order['total_price'] ?? 0}'),
                  isThreeLine: true,
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () {
                    final orderId = order['id'] is int ? order['id'] as int : int.tryParse(order['id']?.toString() ?? '0') ?? 0;
                    Navigator.of(context).push(MaterialPageRoute(builder: (_) => OrderDetailScreen(orderId: orderId)));
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

class OrderDetailScreen extends StatefulWidget {
  final int orderId;
  const OrderDetailScreen({super.key, required this.orderId});

  @override
  State<OrderDetailScreen> createState() => _OrderDetailScreenState();
}

class _OrderDetailScreenState extends State<OrderDetailScreen> {
  late Future<Map<String, dynamic>> _orderFuture;
  final Map<int, Uint8List?> _ticketQr = {};
  final Map<int, bool> _loadingQr = {};

  @override
  void initState() {
    super.initState();
    _orderFuture = _loadOrder();
  }

  Future<Map<String, dynamic>> _loadOrder() async {
    final res = await ApiService.instance.getOrder(widget.orderId);
    if (res.statusCode == 200) {
      final data = jsonDecode(res.body);
      if (data is Map<String, dynamic>) return data;
      if (data is Map && data['data'] is Map<String, dynamic>) return Map<String, dynamic>.from(data['data']);
    }
    throw Exception('Failed to load order: ${res.statusCode}');
  }

  Future<void> _loadQr(int ticketId) async {
    setState(() => _loadingQr[ticketId] = true);
    final res = await ApiService.instance.getTicketQr(ticketId);
    if (res.statusCode == 200) {
      setState(() => _ticketQr[ticketId] = res.bodyBytes);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Failed to load QR: ${res.statusCode}')));
    }
    setState(() => _loadingQr[ticketId] = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Order Detail')),
      body: FutureBuilder<Map<String, dynamic>>(
        future: _orderFuture,
        builder: (context, snap) {
          if (snap.connectionState == ConnectionState.waiting) return const Center(child: CircularProgressIndicator());
          if (snap.hasError) return Center(child: Text('Error: ${snap.error}'));
          final order = snap.data!;
          final tickets = order['tickets'] is List ? List<Map<String, dynamic>>.from(order['tickets'].cast<Map<String, dynamic>>()) : <Map<String, dynamic>>[];
          final items = order['items'] is List ? List<Map<String, dynamic>>.from(order['items'].cast<Map<String, dynamic>>()) : <Map<String, dynamic>>[];
          return Padding(
            padding: const EdgeInsets.all(16),
            child: ListView(
              children: [
                Text('Order #${order['id'] ?? ''}', style: Theme.of(context).textTheme.titleLarge),
                const SizedBox(height: 10),
                Text('Status: ${order['status'] ?? '-'}', style: Theme.of(context).textTheme.bodyLarge),
                const SizedBox(height: 8),
                Text('Total: Rp ${order['total_price'] ?? 0}'),
                const SizedBox(height: 16),
                const Text('Tickets', style: TextStyle(fontWeight: FontWeight.bold)),
                const SizedBox(height: 8),
                if (tickets.isEmpty && items.isEmpty)
                  const Text('No ticket data available')
                else ...[
                  for (final ticket in tickets)
                    Card(
                      margin: const EdgeInsets.symmetric(vertical: 6),
                      child: Padding(
                        padding: const EdgeInsets.all(12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Ticket ID: ${ticket['id'] ?? ''}', style: const TextStyle(fontWeight: FontWeight.bold)),
                            const SizedBox(height: 6),
                            Text('Code: ${ticket['code'] ?? '-'}'),
                            const SizedBox(height: 6),
                            if (_ticketQr[ticket['id']] != null)
                              Image.memory(_ticketQr[ticket['id']]!, height: 160)
                            else
                              ElevatedButton(
                                onPressed: _loadingQr[ticket['id']] == true ? null : () => _loadQr(ticket['id'] as int),
                                child: _loadingQr[ticket['id']] == true ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)) : const Text('View QR'),
                              ),
                          ],
                        ),
                      ),
                    ),
                  if (tickets.isEmpty)
                    for (final item in items)
                      Card(
                        margin: const EdgeInsets.symmetric(vertical: 6),
                        child: ListTile(
                          title: Text(item['ticketType']?['name']?.toString() ?? 'Ticket Item'),
                          subtitle: Text('Qty: ${item['quantity'] ?? 0} • Rp ${item['unit_price'] ?? 0}'),
                        ),
                      ),
                ],
              ],
            ),
          );
        },
      ),
    );
  }
}
