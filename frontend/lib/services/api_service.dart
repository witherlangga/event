
import 'dart:convert';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;

class ApiService {
  ApiService._();
  static final ApiService instance = ApiService._();

  // Update baseUrl if needed
  // Use localhost for desktop/web; emulator devices may need 10.0.2.2
  // API is served under /api prefix on the backend.
  final String baseUrl = 'http://localhost:8000/api';
  final FlutterSecureStorage _storage = const FlutterSecureStorage();

  Future<Map<String, String>> _defaultHeaders() async {
    final token = await _storage.read(key: 'jwt');
    final headers = <String, String>{'Content-Type': 'application/json'};
    if (token != null && token.isNotEmpty) {
      headers['Authorization'] = 'Bearer $token';
    }
    return headers;
  }

  Future<http.Response> post(String path, {Map<String, dynamic>? body, Map<String, String>? headers}) async {
    final uri = Uri.parse('$baseUrl$path');
    final h = await _defaultHeaders();
    if (headers != null) h.addAll(headers);
    return http.post(uri, headers: h, body: jsonEncode(body ?? {}));
  }

  Future<http.Response> get(String path, {Map<String, String>? headers}) async {
    final uri = Uri.parse('$baseUrl$path');
    final h = await _defaultHeaders();
    if (headers != null) h.addAll(headers);
    return http.get(uri, headers: h);
  }

  Future<List<Map<String, dynamic>>> _getJsonList(http.Response res) async {
    if (res.statusCode == 200) {
      final data = jsonDecode(res.body);
      if (data is List) return List<Map<String, dynamic>>.from(data.cast<Map<String, dynamic>>());
      if (data is Map && data['data'] is List) return List<Map<String, dynamic>>.from(data['data'].cast<Map<String, dynamic>>());
    }
    throw Exception('Failed to load: ${res.statusCode}');
  }

  // Convenience helpers
  Future<http.Response> getEvents() => get('/events');
  Future<http.Response> getEvent(int id) => get('/events/$id');
  Future<http.Response> postPurchase(int eventId, Map<String, dynamic> body) => post('/events/$eventId/purchase', body: body);

  Future<http.Response> getTicketQr(int ticketId) async {
    final uri = Uri.parse('$baseUrl/tickets/$ticketId/qr');
    final h = await _defaultHeaders();
    // Expect binary image
    return http.get(uri, headers: h);
  }

  // Convenience: get events near location (radius km defaults to 10)
  Future<List<Map<String, dynamic>>> getEventsWithLocation(double lat, double lng, {double radiusKm = 10}) async {
    final uri = Uri.parse('$baseUrl/events?lat=$lat&lng=$lng&radius_km=$radiusKm');
    final h = await _defaultHeaders();
    final res = await http.get(uri, headers: h);
    return _getJsonList(res);
  }
}

