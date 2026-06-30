import 'dart:convert';
import 'package:flutter/material.dart';
import '../services/api_service.dart';

class AdminTicketTypesScreen extends StatefulWidget {
  final Map<String, dynamic> event;

  const AdminTicketTypesScreen({super.key, required this.event});

  @override
  State<AdminTicketTypesScreen> createState() => _AdminTicketTypesScreenState();
}

class _AdminTicketTypesScreenState extends State<AdminTicketTypesScreen> {
  late Future<List<Map<String, dynamic>>> _ticketTypesFuture;

  @override
  void initState() {
    super.initState();
    _loadTicketTypes();
  }

  void _loadTicketTypes() {
    final eventId = widget.event['id'] is int
        ? widget.event['id'] as int
        : int.tryParse(widget.event['id']?.toString() ?? '0') ?? 0;
    setState(() {
      _ticketTypesFuture = ApiService.instance.fetchList('/admin/concerts/$eventId/tickets');
    });
  }

  Future<void> _showTicketTypeForm([Map<String, dynamic>? ticketType]) async {
    final nameCtrl = TextEditingController(text: ticketType?['name']?.toString() ?? '');
    final descCtrl = TextEditingController(text: ticketType?['description']?.toString() ?? '');
    final priceCtrl = TextEditingController(text: ticketType != null ? ticketType['price']?.toString() ?? '' : '0');
    final quotaCtrl = TextEditingController(text: ticketType != null ? ticketType['quota']?.toString() ?? '' : '0');
    bool isActive = ticketType?['is_active'] == true || ticketType?['is_active'] == 1;

    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(ticketType == null ? 'Tambah Tipe Tiket' : 'Edit Tipe Tiket'),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(controller: nameCtrl, decoration: const InputDecoration(labelText: 'Nama')),
              const SizedBox(height: 12),
              TextField(controller: descCtrl, decoration: const InputDecoration(labelText: 'Deskripsi')),
              const SizedBox(height: 12),
              TextField(
                controller: priceCtrl,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                decoration: const InputDecoration(labelText: 'Harga (Rp)'),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: quotaCtrl,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(labelText: 'Kuota'),
              ),
              const SizedBox(height: 12),
              SwitchListTile(
                contentPadding: EdgeInsets.zero,
                title: const Text('Aktif'),
                value: isActive,
                onChanged: (value) => setState(() => isActive = value),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.of(context).pop(false), child: const Text('Batal')),
          TextButton(onPressed: () => Navigator.of(context).pop(true), child: const Text('Simpan')),
        ],
      ),
    );

    if (confirmed != true) return;

    final eventId = widget.event['id'] is int
        ? widget.event['id'] as int
        : int.tryParse(widget.event['id']?.toString() ?? '0') ?? 0;
    final body = {
      'name': nameCtrl.text,
      'description': descCtrl.text,
      'price': double.tryParse(priceCtrl.text) ?? 0,
      'quota': int.tryParse(quotaCtrl.text) ?? 0,
      'is_active': isActive,
    };

    final res = ticketType == null
        ? await ApiService.instance.post('/admin/concerts/$eventId/tickets', body: body)
        : await ApiService.instance.put('/admin/concerts/$eventId/tickets/${ticketType['id']}', body: body);

    if (res.statusCode == 200 || res.statusCode == 201) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(ticketType == null ? 'Tipe tiket berhasil ditambahkan.' : 'Tipe tiket berhasil diperbarui.')),
      );
      _loadTicketTypes();
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Gagal menyimpan tipe tiket: ${res.statusCode}')),
    );
  }

  Future<void> _deleteTicketType(Map<String, dynamic> ticketType) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Hapus Tipe Tiket'),
        content: const Text('Apakah Anda yakin ingin menghapus tipe tiket ini?'),
        actions: [
          TextButton(onPressed: () => Navigator.of(context).pop(false), child: const Text('Batal')),
          TextButton(onPressed: () => Navigator.of(context).pop(true), child: const Text('Hapus')),
        ],
      ),
    );

    if (confirmed != true) return;

    final eventId = widget.event['id'] is int
        ? widget.event['id'] as int
        : int.tryParse(widget.event['id']?.toString() ?? '0') ?? 0;
    final ticketId = ticketType['id'] is int ? ticketType['id'] as int : int.tryParse(ticketType['id']?.toString() ?? '0') ?? 0;
    final res = await ApiService.instance.delete('/admin/concerts/$eventId/tickets/$ticketId');

    if (res.statusCode == 200) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Tipe tiket berhasil dihapus.')));
      _loadTicketTypes();
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Gagal menghapus tipe tiket: ${res.statusCode}')),
    );
  }

  @override
  Widget build(BuildContext context) {
    final eventTitle = widget.event['title']?.toString() ?? 'Konser';
    return Scaffold(
      appBar: AppBar(title: Text('Tiket: $eventTitle')),
      floatingActionButton: FloatingActionButton(
        onPressed: () => _showTicketTypeForm(),
        child: const Icon(Icons.add),
        tooltip: 'Tambah Tipe Tiket',
      ),
      body: FutureBuilder<List<Map<String, dynamic>>>(
        future: _ticketTypesFuture,
        builder: (context, snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snap.hasError) {
            return Center(child: Text('Error: ${snap.error}'));
          }
          final ticketTypes = snap.data ?? [];
          if (ticketTypes.isEmpty) {
            return const Center(child: Text('Belum ada tipe tiket. Tambahkan tipe tiket untuk konser ini.'));
          }
          return ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: ticketTypes.length,
            itemBuilder: (context, index) {
              final ticketType = ticketTypes[index];
              return Card(
                margin: const EdgeInsets.symmetric(vertical: 8),
                child: ListTile(
                  title: Text(ticketType['name']?.toString() ?? 'Tipe Tiket'),
                  subtitle: Text('Harga: Rp ${ticketType['price'] ?? 0} • Kuota: ${ticketType['quota'] ?? 0} • Terjual: ${ticketType['sold'] ?? 0}'),
                  trailing: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      IconButton(
                        icon: const Icon(Icons.edit),
                        tooltip: 'Edit',
                        onPressed: () => _showTicketTypeForm(ticketType),
                      ),
                      IconButton(
                        icon: const Icon(Icons.delete_outline, color: Colors.redAccent),
                        tooltip: 'Hapus',
                        onPressed: () => _deleteTicketType(ticketType),
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
