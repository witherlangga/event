import 'package:flutter/material.dart';
import 'dart:convert';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';
import '../models/user.dart';

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
  String _role = 'customer';
  
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
      'role': _role,
    };

    final res = await ApiService.instance.post('/auth/register', body: body);
    setState(() { _loading = false; });
    if (res.statusCode == 201 || res.statusCode == 200) {
      try {
        final parsed = jsonDecode(res.body) as Map<String, dynamic>;
        final token = parsed['access_token'] as String? ?? parsed['token'] as String?;
        final userJson = parsed['user'] as Map<String, dynamic>?;
        if (token != null) {
          final auth = ref.read(authNotifierProvider.notifier);
          final user = userJson != null ? UserModel.fromJson(userJson) : null;
          await auth.setAuth(token, user);
          Navigator.of(context).pushReplacementNamed('/home');
          return;
        }
      } catch (_) {}
      Navigator.of(context).pop();
    } else {
      String message = 'Register failed: ${res.statusCode}';
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
                      const SizedBox(height: 6),
                      const FlutterLogo(size: 64),
                      const SizedBox(height: 12),
                      Text('Create account', style: Theme.of(context).textTheme.titleLarge),
                      const SizedBox(height: 12),
                      TextFormField(
                        decoration: InputDecoration(
                          labelText: 'Full name',
                          prefixIcon: const Icon(Icons.person_outline),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                        onSaved: (v) => _name = v ?? '',
                        validator: (v) => v != null && v.isNotEmpty ? null : 'Enter name',
                      ),
                      const SizedBox(height: 10),
                      TextFormField(
                        decoration: InputDecoration(
                          labelText: 'Email',
                          prefixIcon: const Icon(Icons.email_outlined),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                        keyboardType: TextInputType.emailAddress,
                        onSaved: (v) => _email = v ?? '',
                        validator: (v) => v != null && v.contains('@') ? null : 'Enter a valid email',
                      ),
                      const SizedBox(height: 10),
                      TextFormField(
                        decoration: InputDecoration(
                          labelText: 'Password',
                          prefixIcon: const Icon(Icons.lock_outline),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                        obscureText: true,
                        onSaved: (v) => _password = v ?? '',
                        validator: (v) => v != null && v.length >= 6 ? null : 'Password too short',
                      ),
                      const SizedBox(height: 10),
                      DropdownButtonFormField<String>(
                        initialValue: _role,
                        decoration: InputDecoration(
                          labelText: 'Register as',
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                        items: const [
                          DropdownMenuItem(value: 'customer', child: Text('Customer')),
                          DropdownMenuItem(value: 'organizer', child: Text('Organizer')),
                        ],
                        onChanged: (v) => setState(() { _role = v ?? 'customer'; }),
                        onSaved: (v) => _role = v ?? 'customer',
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
                          style: ElevatedButton.styleFrom(
                              padding: const EdgeInsets.symmetric(vertical: 14),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10))
                          ),
                          child: _loading ? const SizedBox(height: 18, width: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)) : const Text('Register'),
                        ),
                      ),
                      const SizedBox(height: 8),
                      TextButton(onPressed: () => Navigator.of(context).pop(), child: const Text('Back to login')),
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
