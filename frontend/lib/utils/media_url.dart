import 'package:flutter/foundation.dart';

class MediaUrl {
  MediaUrl._();

  static String get storageBase {
    if (kIsWeb) {
      final uri = Uri.base;
      final host = uri.host.isNotEmpty ? uri.host : 'localhost';
      return '${uri.scheme}://$host:8000/storage';
    }
    if (defaultTargetPlatform == TargetPlatform.android) {
      return 'http://10.0.2.2:8000/storage';
    }
    return 'http://127.0.0.1:8000/storage';
  }

  static String? resolve(String? path) {
    if (path == null || path.isEmpty) return null;
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    final normalized = path.startsWith('/') ? path.substring(1) : path;
    if (normalized.startsWith('storage/')) {
      return '$storageBase/${normalized.substring('storage/'.length)}';
    }
    return '$storageBase/$normalized';
  }
}
