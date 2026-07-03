import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import '../services/api_service.dart';

class Hero3DService {
  /// Fetch events untuk Hero 3D Screen
  static Future<List<Map<String, dynamic>>> fetchHeroEvents({
    int limit = 4,
  }) async {
    try {
      final response = await http.get(
        Uri.parse('${ApiService.instance.baseUrl}/events?limit=$limit&active=true'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      ).timeout(
        const Duration(seconds: 10),
        onTimeout: () => throw Exception('Request timeout'),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final events = List<Map<String, dynamic>>.from(data['events'] ?? data['data'] ?? []);
        
        if (kDebugMode) {
          debugPrint('✅ Loaded ${events.length} hero events');
        }
        
        return events;
      } else {
        throw Exception('Failed to load events: ${response.statusCode}');
      }
    } catch (e) {
      if (kDebugMode) {
        debugPrint('❌ Error loading hero events: $e');
      }
      return [];
    }
  }

  /// Fetch single event dengan detail lengkap
  static Future<Map<String, dynamic>?> fetchEventDetail(int eventId) async {
    try {
      final response = await http.get(
        Uri.parse('${ApiService.instance.baseUrl}/events/$eventId'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      ).timeout(
        const Duration(seconds: 10),
        onTimeout: () => throw Exception('Request timeout'),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data['event'] ?? data['data'];
      } else {
        throw Exception('Failed to load event detail: ${response.statusCode}');
      }
    } catch (e) {
      if (kDebugMode) {
        debugPrint('❌ Error loading event detail: $e');
      }
      return null;
    }
  }

  /// Get featured event untuk hero card
  static Future<Map<String, dynamic>?> getFeaturedEvent() async {
    try {
      final events = await fetchHeroEvents(limit: 1);
      return events.isNotEmpty ? events.first : null;
    } catch (e) {
      if (kDebugMode) {
        debugPrint('❌ Error getting featured event: $e');
      }
      return null;
    }
  }

  /// Format price untuk display
  static String formatPrice(dynamic price) {
    if (price == null) return 'TBA';
    
    final priceValue = double.tryParse(price.toString()) ?? 0;
    
    if (priceValue >= 1000000) {
      return 'Rp ${(priceValue / 1000000).toStringAsFixed(1)}M';
    } else if (priceValue >= 1000) {
      return 'Rp ${(priceValue / 1000).toStringAsFixed(1)}K';
    } else {
      return 'Rp ${priceValue.toStringAsFixed(0)}';
    }
  }

  /// Format date untuk display
  static String formatEventDate(String? dateString) {
    if (dateString == null || dateString.isEmpty) return 'TBA';
    
    try {
      final date = DateTime.parse(dateString);
      final months = [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
      ];
      
      final month = months[date.month - 1];
      final hour = date.hour.toString().padLeft(2, '0');
      final minute = date.minute.toString().padLeft(2, '0');
      
      return '${date.day} $month ${date.year} • $hour:$minute';
    } catch (e) {
      return 'TBA';
    }
  }

  /// Get event image URL
  static String getEventImageUrl(Map<String, dynamic> event) {
    final coverImage = event['cover_image'];
    
    if (coverImage != null && coverImage.isNotEmpty) {
      return '${ApiService.instance.baseUrl.replaceFirst(RegExp(r'/api\z'), '')}/storage/$coverImage';
    }
    
    // Fallback ke placeholder
    return 'https://picsum.photos/1200/600?random=${event['id'] ?? 0}';
  }
}
