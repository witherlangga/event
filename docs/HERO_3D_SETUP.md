# Hero 3D Experience - Setup & Integration Guide

## Overview
Sistem Hero 3D menghadirkan pengalaman visual yang immersive dengan efek 3D Canvas animation, particle effects, dan interactive UI elements yang responsif. Implementasi ini tersedia untuk web (Laravel) dan mobile (Flutter).

## Web Implementation (Laravel)

### File Structure
```
backend/resources/
├── css/
│   └── hero-3d.css        # Styling untuk hero 3D
├── js/
│   └── hero-3d.js         # Canvas animation & particle effects
└── views/
    └── local_web.blade.php # Hero section view
```

### Features
- **3D Canvas Background**: Waveform animation dengan particle system
- **Particle Effects**: Interactive particles yang bergerak mengikuti mouse
- **Responsive Design**: Optimal pada desktop, tablet, dan mobile
- **Glow Effects**: Dynamic shadows dan lighting effects
- **Wave Animation**: Multi-layer wave dengan berbagai frekuensi

### Setup Checklist
1. ✅ CSS file sudah di-link di `resources/views/layouts/panel.blade.php`
2. ✅ JavaScript file sudah di-link di `resources/views/layouts/panel.blade.php`
3. File akan di-serve otomatis oleh Laravel asset system
4. No additional packages diperlukan

### Usage
Web hero akan automatically load di halaman home (`/`). Untuk mengakses:
```bash
cd backend
php artisan serve
# Kunjungi http://localhost:8000
```

### Customization
Untuk mengubah warna atau efek, edit `resources/css/hero-3d.css` dan `resources/js/hero-3d.js`:

**Colors (di hero-3d.js)**:
```javascript
getRandomGlowColor() {
    const colors = ['#64c8ff', '#ff6b9d', '#00ffff', '#00ff88'];
    return colors[Math.floor(Math.random() * colors.length)];
}
```

**Wave Properties**:
```javascript
this.waves = [
    { amplitude: 80, frequency: 0.005, phase: 0, y: this.height * 0.4, color: '#64c8ff', thickness: 2 },
    // ... customize di sini
];
```

## Mobile Implementation (Flutter)

### File Structure
```
frontend/lib/screens/
├── hero_screen_3d.dart    # Main hero screen dengan 3D effects
└── main.dart              # Updated dengan hero route
```

### Features
- **3D Canvas Painter**: Custom painter dengan wave animation
- **Particle System**: Dynamic particles dengan physics simulation
- **Interactive 3D Card**: Card dengan 3D transform on hover
- **Responsive Layout**: Desktop, tablet, dan mobile layouts
- **Glow & Shimmer Effects**: Dynamic lighting dan shimmering
- **Feature Cards**: Grid layout dengan blur backdrop

### Setup Checklist
1. ✅ File `hero_screen_3d.dart` sudah dibuat
2. ✅ Route `/hero-3d` sudah ditambahkan di `main.dart`
3. ✅ Import Hero3DScreen sudah ditambahkan
4. No additional dependencies diperlukan (menggunakan Flutter built-in)

### Usage
**Navigate ke Hero 3D Screen**:
```dart
// Dari screen manapun
Navigator.pushNamed(context, '/hero-3d');

// Atau dengan direct navigation
Navigator.push(
  context,
  MaterialPageRoute(builder: (_) => const Hero3DScreen()),
);
```

### Testing
```bash
cd frontend
flutter run
# Klik untuk navigate ke /hero-3d
```

### Customization
**Color Scheme (di hero_screen_3d.dart)**:
```dart
Color _getRandomGlowColor() {
    final colors = [
      const Color(0xFF64C8FF),  // Cyan
      const Color(0xFFFF6B9D),  // Pink
      const Color(0xFF00FFFF),  // Light Cyan
      const Color(0xFF00FF88),  // Green
    ];
    return colors[_random.nextInt(colors.length)];
}
```

**Animation Duration**:
```dart
_animationController = AnimationController(
    duration: const Duration(milliseconds: 3000),  // Change this
    vsync: this,
)..repeat(reverse: false);
```

## API Integration

### Backend Events Data
Halaman hero dapat menampilkan data events dari database Laravel:

```dart
// Di Hero3DScreen, tambahkan parameter
const Hero3DScreen({
    super.key,
    this.events,  // List<Map<String, dynamic>>
});

// Gunakan data di card
Text('${widget.events?[0]['title'] ?? 'Live in Jakarta'}')
```

### Fetch Events dari API
```dart
// Di Hero3DScreen state
Future<void> _loadEvents() async {
    try {
        final response = await http.get(
            Uri.parse('${ApiService.baseUrl}/api/events'),
        );
        final events = jsonDecode(response.body)['events'];
        setState(() {
            _events = events;
        });
    } catch (e) {
        debugPrint('Error loading events: $e');
    }
}
```

### Web - Fetch Events
Events sudah difetch otomatis di Laravel route dan dikirim ke view:
```php
Route::get('/', function () {
    $events = Event::where('is_active', true)
        ->orderBy('starts_at')
        ->take(4)
        ->get();
    return view('local_web', compact('events'));
});
```

## Performance Optimization

### Web
- Canvas animation berjalan di 60fps
- Particle count dioptimalkan berdasarkan viewport size
- GPU acceleration otomatis

### Mobile
- CustomPaint di-rebuild setiap frame
- Particle count dibatasi ke 150 untuk performa
- AnimationController optimized dengan TickerProviderStateMixin

## Troubleshooting

### Web Issues
1. **Efek tidak muncul**: Clear browser cache dan reload
2. **Performance lag**: Kurangi particle count di `createParticles()`
3. **Responsive issue**: Check media queries di `hero-3d.css`

### Mobile Issues
1. **Crash di render**: Update flutter dengan `flutter upgrade`
2. **Performance lag**: Reduce particle count dari 150
3. **Colors tidak benar**: Check color hex codes di `_getRandomGlowColor()`

## Browser & Device Support

### Web
- Chrome/Edge: ✅ Full support
- Firefox: ✅ Full support
- Safari: ✅ Full support (iOS 14+)
- Mobile browsers: ✅ Responsive layout

### Mobile
- iOS: ✅ Full support (12.0+)
- Android: ✅ Full support (5.1+)
- Tablets: ✅ Optimized layout

## Future Enhancements

1. **WebGL Version**: Upgrade web ke WebGL untuk performa lebih baik
2. **3D Model Showcase**: Tambahkan 3D models dengan Three.js
3. **Spatial Audio**: Sync audio dengan visual effects
4. **Real-time Collaboration**: Multi-user interactive experience
5. **Analytics Integration**: Track user interactions dengan 3D elements

## Documentation References

- [Canvas API Documentation](https://developer.mozilla.org/en-US/docs/Web/API/Canvas_API)
- [Flutter CustomPaint](https://api.flutter.dev/flutter/rendering/CustomPaint-class.html)
- [CSS 3D Transforms](https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Transforms)
- [RequestAnimationFrame](https://developer.mozilla.org/en-US/docs/Web/API/window/requestAnimationFrame)

## Support & Contact

Untuk pertanyaan atau issue, silakan buat issue di repository atau hubungi tim development.
