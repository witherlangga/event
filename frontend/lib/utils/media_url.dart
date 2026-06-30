class MediaUrl {
  MediaUrl._();

  static const String storageBase = 'http://localhost:8000/storage';

  static String? resolve(String? path) {
    if (path == null || path.isEmpty) return null;
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    return '$storageBase/$path';
  }
}
