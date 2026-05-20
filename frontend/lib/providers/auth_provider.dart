import 'dart:convert';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../services/api_service.dart';
import '../models/user.dart';

final FlutterSecureStorage _storage = const FlutterSecureStorage();

class AuthState {
  final String? token;
  final UserModel? user;

  AuthState({this.token, this.user});

  bool get isAuthenticated => token != null && token!.isNotEmpty;
}

class AuthNotifier extends StateNotifier<AuthState> {
  AuthNotifier(): super(AuthState());

  Future<void> loadFromStorage() async {
    final t = await _storage.read(key: 'jwt');
    if (t != null) {
      // set token and try to fetch current user
      state = AuthState(token: t, user: null);
      try {
        final res = await ApiService.instance.get('/auth/me');
        if (res.statusCode == 200) {
          final map = jsonDecode(res.body) as Map<String, dynamic>;
          final userJson = map['user'] as Map<String, dynamic>? ?? map;
          final user = userJson != null ? UserModel.fromJson(userJson) : null;
          state = AuthState(token: t, user: user);
        }
      } catch (_) {
        // ignore, keep token only
      }
    }
  }

  Future<void> setAuth(String token, UserModel? user) async {
    await _storage.write(key: 'jwt', value: token);
    state = AuthState(token: token, user: user);
  }

  Future<String?> login(String email, String password) async {
    final res = await ApiService.instance.post('/auth/login', body: {'email': email, 'password': password});
    if (res.statusCode == 200) {
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      final token = data['access_token'] as String? ?? data['token'] as String?;
      final userJson = data['user'] as Map<String, dynamic>?;
      if (token != null) {
        await _storage.write(key: 'jwt', value: token);
        final user = userJson != null ? UserModel.fromJson(userJson) : null;
        state = AuthState(token: token, user: user);
        return null;
      }
      return 'Token not returned';
    }
    return 'Login failed: ${res.statusCode} ${res.body}';
  }

  Future<void> logout() async {
    await _storage.delete(key: 'jwt');
    state = AuthState();
  }
}

final authNotifierProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  final n = AuthNotifier();
  n.loadFromStorage();
  return n;
});
