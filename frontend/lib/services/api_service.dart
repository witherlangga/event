
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;

class ApiService {
  ApiService._();
  static final ApiService instance = ApiService._();

  String get baseUrl {
    if (kIsWeb) {
      final uri = Uri.base;
      final scheme = uri.scheme.isNotEmpty && uri.scheme != 'file' ? uri.scheme : 'http';
      final host = uri.host.isNotEmpty ? uri.host : 'localhost';
      return '$scheme://$host:8000/api';
    }

    return 'http://localhost:8000/api';
  }

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

  Future<http.Response> put(String path, {Map<String, dynamic>? body, Map<String, String>? headers}) async {
    final uri = Uri.parse('$baseUrl$path');
    final h = await _defaultHeaders();
    if (headers != null) h.addAll(headers);
    return http.put(uri, headers: h, body: jsonEncode(body ?? {}));
  }

  Future<http.Response> delete(String path) async {
    final uri = Uri.parse('$baseUrl$path');
    final h = await _defaultHeaders();
    return http.delete(uri, headers: h);
  }

  Future<http.Response> get(String path, {Map<String, String>? headers}) async {
    final uri = path.startsWith('http://') || path.startsWith('https://')
        ? Uri.parse(path)
        : Uri.parse('$baseUrl$path');
    final h = await _defaultHeaders();
    if (headers != null) h.addAll(headers);
    return http.get(uri, headers: h);
  }

  static List<Map<String, dynamic>> extractList(dynamic data) {
    if (data is List) {
      return List<Map<String, dynamic>>.from(data.cast<Map<String, dynamic>>());
    }
    if (data is Map && data['data'] is List) {
      return List<Map<String, dynamic>>.from((data['data'] as List).cast<Map<String, dynamic>>());
    }
    return [];
  }

  static Map<String, dynamic>? extractObject(dynamic data) {
    if (data is Map<String, dynamic>) {
      if (data['data'] is Map) {
        return Map<String, dynamic>.from(data['data'] as Map);
      }
      return data;
    }
    return null;
  }

  Future<List<Map<String, dynamic>>> fetchList(String path) async {
    final res = await get(path);
    if (res.statusCode == 200) {
      return extractList(jsonDecode(res.body));
    }
    throw Exception('Gagal memuat data: ${res.statusCode}');
  }

  Future<http.Response> getEvents({String? query}) {
    final path = '/events${query != null && query.isNotEmpty ? '?$query' : ''}';
    return get(path);
  }

  Future<http.Response> getEvent(int id) => get('/events/$id');
  Future<http.Response> getBandProfile() => get('/band/profile');
  Future<List<Map<String, dynamic>>> getBandMembers() => fetchList('/band/members');
  Future<List<Map<String, dynamic>>> getAlbums() => fetchList('/band/albums');
  Future<http.Response> getAlbum(int id) => get('/band/albums/$id');
  Future<List<Map<String, dynamic>>> getGallery() => fetchList('/band/gallery');
  Future<List<Map<String, dynamic>>> getNews() => fetchList('/band/news');
  Future<http.Response> getNewsDetail(int id) => get('/band/news/$id');

  Future<http.Response> getOrders({Map<String, String>? params}) {
    final queryString = params != null && params.isNotEmpty
        ? '?${params.entries.map((e) => '${Uri.encodeComponent(e.key)}=${Uri.encodeComponent(e.value)}').join('&')}'
        : '';
    return get('/orders$queryString');
  }

  Future<http.Response> getOrder(int id) => get('/orders/$id');
  Future<http.Response> getAdminConcerts() => get('/admin/concerts');
  Future<http.Response> postPurchase(int eventId, Map<String, dynamic> body) => post('/events/$eventId/purchase', body: body);

  Future<http.Response> initializePayment(int orderId, String paymentMethod, {String? bankCode}) async {
    return post('/orders/$orderId/payment/init', body: {
      'payment_method': paymentMethod,
      if (bankCode != null) 'bank': bankCode,
    });
  }

  Future<http.Response> generatePaymentQris(int orderId) async {
    final uri = Uri.parse('$baseUrl/orders/$orderId/payment/qris');
    final h = await _defaultHeaders();
    return http.post(uri, headers: h);
  }

  Future<http.Response> getPaymentStatus(int orderId) async {
    final uri = Uri.parse('$baseUrl/orders/$orderId/payment/status');
    final h = await _defaultHeaders();
    return http.get(uri, headers: h);
  }

  Future<http.Response> confirmPayment(int orderId) async {
    final uri = Uri.parse('$baseUrl/orders/$orderId/payment/confirm');
    final h = await _defaultHeaders();
    return http.post(uri, headers: h);
  }

  Future<http.Response> getTicketQr(int ticketId) async {
    final uri = Uri.parse('$baseUrl/tickets/$ticketId/qr');
    final h = await _defaultHeaders();
    final res = await http.get(uri, headers: h);

    if (res.statusCode == 200) {
      try {
        final data = jsonDecode(res.body);
        if (data is Map<String, dynamic> && data['url'] is String) {
          return http.get(Uri.parse(data['url']));
        }
      } catch (_) {}
    }

    return res;
  }

  Future<List<Map<String, dynamic>>> getEventsWithLocation(double lat, double lng, {double radiusKm = 10}) async {
    final uri = Uri.parse('$baseUrl/events?lat=$lat&lng=$lng&radius_km=$radiusKm');
    final h = await _defaultHeaders();
    final res = await http.get(uri, headers: h);
    if (res.statusCode == 200) {
      return extractList(jsonDecode(res.body));
    }
    throw Exception('Gagal memuat konser: ${res.statusCode}');
  }
}
