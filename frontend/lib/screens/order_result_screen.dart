import 'dart:typed_data';
import 'package:flutter/material.dart';
import '../services/api_service.dart';

class OrderResultScreen extends StatefulWidget {
  final List<Map<String, dynamic>> tickets;
  const OrderResultScreen({super.key, required this.tickets});

  @override
  State<OrderResultScreen> createState() => _OrderResultScreenState();
}

class _OrderResultScreenState extends State<OrderResultScreen> {
  final Map<int, Uint8List?> _qrCache = {};
  final Map<int, bool> _loading = {};

  Future<void> _loadQr(int ticketId) async {
    setState(() { _loading[ticketId] = true; });
    final res = await ApiService.instance.getTicketQr(ticketId);
    if (res.statusCode == 200) {
      setState(() { _qrCache[ticketId] = res.bodyBytes; });
    }
    setState(() { _loading[ticketId] = false; });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Order Result')),
      body: ListView(
        padding: const EdgeInsets.all(12),
        children: [
          const Text('Tickets', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
          const SizedBox(height: 8),
          for (final t in widget.tickets)
            Card(
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Ticket ${t['id'] ?? t['code'] ?? ''}'),
                    const SizedBox(height: 8),
                    if (_qrCache[t['id']] != null)
                      Image.memory(_qrCache[t['id']]!, height: 200)
                    else
                      ElevatedButton(
                        onPressed: _loading[t['id']] == true ? null : () => _loadQr(t['id'] as int),
                        child: _loading[t['id']] == true ? const SizedBox(width:16,height:16,child:CircularProgressIndicator(strokeWidth:2)) : const Text('View QR'),
                      ),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }
}
