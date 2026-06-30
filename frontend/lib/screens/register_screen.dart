import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'dart:convert';
import '../services/api_service.dart';
import '../constants/app_constants.dart';
import '../theme.dart';

class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key});

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  String _name = '';
  String _email = '';
  String _password = '';
  bool _loading = false;
  String? _error;

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    _formKey.currentState!.save();
    setState(() { _loading = true; _error = null; });

    final body = {
      'name': _name,
      'email': _email,
      'password': _password,
      'password_confirmation': _password,
    };

    final res = await ApiService.instance.post('/auth/register', body: body);
    setState(() { _loading = false; });
    if (res.statusCode == 201 || res.statusCode == 200) {
      if (mounted) Navigator.of(context).pushReplacementNamed('/login');
      return;
    } else {
      String message = 'Registrasi gagal: ${res.statusCode}';
      try {
        final parsed = jsonDecode(res.body) as Map<String, dynamic>;
        if (parsed.containsKey('errors')) {
          final errors = parsed['errors'] as Map<String, dynamic>;
          message = errors.values.map((v) => (v is List ? v.join(', ') : v.toString())).join(' ');
        } else if (parsed.containsKey('message')) {
          message = parsed['message'].toString();
        }
      } catch (_) {}
      setState(() { _error = message; });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 24),
            child: Card(
              elevation: 8,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              child: Padding(
                padding: const EdgeInsets.all(20.0),
                child: Form(
                  key: _formKey,
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.person_add, size: 56, color: AppTheme.accent),
                      const SizedBox(height: 12),
                      Text('Daftar ${AppConstants.fanLabel}', style: Theme.of(context).textTheme.titleLarge),
                      const SizedBox(height: 4),
                      Text('Bergabung dengan komunitas ${AppConstants.bandName}', style: Theme.of(context).textTheme.bodySmall),
                      const SizedBox(height: 16),
                      TextFormField(
                        decoration: const InputDecoration(
                          labelText: 'Nama lengkap',
                          prefixIcon: Icon(Icons.person_outline),
                        ),
                        onSaved: (v) => _name = v ?? '',
                        validator: (v) => v != null && v.isNotEmpty ? null : 'Masukkan nama',
                      ),
                      const SizedBox(height: 10),
                      TextFormField(
                        decoration: const InputDecoration(
                          labelText: 'Email',
                          prefixIcon: Icon(Icons.email_outlined),
                        ),
                        keyboardType: TextInputType.emailAddress,
                        onSaved: (v) => _email = v ?? '',
                        validator: (v) => v != null && v.contains('@') ? null : 'Masukkan email valid',
                      ),
                      const SizedBox(height: 10),
                      TextFormField(
                        decoration: const InputDecoration(
                          labelText: 'Password',
                          prefixIcon: Icon(Icons.lock_outline),
                        ),
                        obscureText: true,
                        onSaved: (v) => _password = v ?? '',
                        validator: (v) => v != null && v.length >= 6 ? null : 'Password minimal 6 karakter',
                      ),
                      const SizedBox(height: 14),
                      if (_error != null) ...[
                        Text(_error!, style: const TextStyle(color: Colors.red)),
                        const SizedBox(height: 10),
                      ],
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: _loading ? null : _submit,
                          style: ElevatedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 14)),
                          child: _loading
                              ? const SizedBox(height: 18, width: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                              : const Text('Daftar'),
                        ),
                      ),
                      const SizedBox(height: 8),
                      TextButton(onPressed: () => Navigator.of(context).pop(), child: const Text('Kembali ke login')),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
