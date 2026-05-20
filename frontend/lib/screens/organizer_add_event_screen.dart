import 'dart:convert';
import 'package:flutter/material.dart';
import '../services/api_service.dart';

class OrganizerAddEventScreen extends StatefulWidget {
  const OrganizerAddEventScreen({super.key});

  @override
  State<OrganizerAddEventScreen> createState() => _OrganizerAddEventScreenState();
}

class _OrganizerAddEventScreenState extends State<OrganizerAddEventScreen> {
  final _formKey = GlobalKey<FormState>();
  String _title = '';
  String _description = '';
  String _location = '';
  DateTime? _startsAt;
  DateTime? _endsAt;
  int _capacity = 0;
  bool _loading = false;
  String? _error;

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    _formKey.currentState!.save();
    setState(() { _loading = true; _error = null; });

    final body = {
      'title': _title,
      'description': _description,
      'location_name': _location,
      'starts_at': _startsAt?.toIso8601String(),
      'ends_at': _endsAt?.toIso8601String(),
      'capacity': _capacity,
    };

    final res = await ApiService.instance.post('/organizer/events', body: body);
    setState(() { _loading = false; });
    if (res.statusCode == 201 || res.statusCode == 200) {
      Navigator.of(context).pop(true);
    } else {
      try {
        final parsed = jsonDecode(res.body) as Map<String, dynamic>;
        _error = parsed['message'] ?? (parsed['errors']?.toString());
      } catch (_) {
        _error = 'Failed: ${res.statusCode}';
      }
      setState(() {});
    }
  }

  Future<void> _pickDate(BuildContext ctx, bool isStart) async {
    final now = DateTime.now();
    final d = await showDatePicker(context: ctx, initialDate: now, firstDate: now.subtract(const Duration(days: 365)), lastDate: now.add(const Duration(days: 3650)));
    if (d == null) return;
    final t = await showTimePicker(context: ctx, initialTime: TimeOfDay.now());
    final dt = DateTime(d.year, d.month, d.day, t?.hour ?? 0, t?.minute ?? 0);
    setState(() { if (isStart) _startsAt = dt; else _endsAt = dt; });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Add Event')),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: ListView(
            children: [
              TextFormField(decoration: const InputDecoration(labelText: 'Title'), onSaved: (v) => _title = v ?? '', validator: (v) => v != null && v.isNotEmpty ? null : 'Enter title'),
              TextFormField(decoration: const InputDecoration(labelText: 'Description'), onSaved: (v) => _description = v ?? '', maxLines: 3),
              TextFormField(decoration: const InputDecoration(labelText: 'Location'), onSaved: (v) => _location = v ?? ''),
              const SizedBox(height: 8),
              Row(children: [
                Expanded(child: Text(_startsAt != null ? 'Starts: ${_startsAt!.toLocal()}' : 'Select start')),
                TextButton(onPressed: () => _pickDate(context, true), child: const Text('Pick')),
              ]),
              Row(children: [
                Expanded(child: Text(_endsAt != null ? 'Ends: ${_endsAt!.toLocal()}' : 'Select end')),
                TextButton(onPressed: () => _pickDate(context, false), child: const Text('Pick')),
              ]),
              TextFormField(decoration: const InputDecoration(labelText: 'Capacity'), keyboardType: TextInputType.number, onSaved: (v) => _capacity = int.tryParse(v ?? '0') ?? 0),
              const SizedBox(height: 16),
              if (_error != null) Text(_error!, style: const TextStyle(color: Colors.red)),
              ElevatedButton(onPressed: _loading ? null : _submit, child: _loading ? const CircularProgressIndicator() : const Text('Create Event')),
            ],
          ),
        ),
      ),
    );
  }
}
