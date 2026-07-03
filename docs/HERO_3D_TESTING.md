# Hero 3D Implementation - Testing & Deployment Guide

## Quick Start

### Web Hero 3D

**Requirements:**
- PHP 8.0+
- Laravel 9+
- Modern browser (Chrome, Firefox, Safari, Edge)

**Run Web Demo:**
```bash
cd backend
php artisan serve
# Open browser: http://localhost:8000
```

Expected Output:
- Full-screen hero section dengan 3D Canvas background
- Animated waves dengan multiple layers
- Particle system yang bergerak
- Interactive mouse tracking
- Responsive buttons "Get Tickets" dan "Explore As Guest"

### Mobile Hero 3D

**Requirements:**
- Flutter 3.0+
- Dart 2.18+
- iOS 12.0+ atau Android 5.1+

**Run Mobile Demo:**
```bash
cd frontend
flutter pub get
flutter run
# Navigate ke /hero-3d route
```

Expected Output:
- Full-screen hero dengan 3D Canvas painter
- Animated particles dengan physics
- 3D card yang respond ke mouse hover
- Responsive layout (mobile, tablet, desktop)
- Feature cards section
- Social buttons (Follow/Subscribe)

## Testing Checklist

### Web Testing

- [ ] **Visual Rendering**
  - [ ] Canvas background animates smoothly
  - [ ] Waves move dengan proper frequency
  - [ ] Particles glow dengan benar
  - [ ] Colors match design (cyan #64c8ff, pink #ff6b9d)

- [ ] **Interactivity**
  - [ ] Mouse movement creates radial glow effect
  - [ ] Hover effects on buttons work
  - [ ] Buttons change color on hover
  - [ ] Follow/Subscribe buttons respond to click

- [ ] **Performance**
  - [ ] No lag pada 60fps
  - [ ] CPU usage reasonable (<50%)
  - [ ] GPU acceleration active
  - [ ] Smooth animations throughout

- [ ] **Responsiveness**
  - [ ] Desktop layout (> 1200px) shows 2-column
  - [ ] Tablet layout (768-1199px) adapts properly
  - [ ] Mobile layout (< 768px) shows single-column
  - [ ] All text readable pada semua ukuran

- [ ] **Browser Compatibility**
  - [ ] Chrome/Edge: Full support ✓
  - [ ] Firefox: Full support ✓
  - [ ] Safari: Full support ✓
  - [ ] Mobile browsers: Works ✓

### Mobile Testing

- [ ] **Visual Rendering**
  - [ ] Canvas painter renders correctly
  - [ ] Particles animate smoothly
  - [ ] Waves display dengan proper spacing
  - [ ] Glows dan shadows render correctly

- [ ] **3D Effects**
  - [ ] Hero card shows 3D transform on hover (desktop)
  - [ ] Shimmer effect pada card background
  - [ ] Particle system moves independently
  - [ ] Colors consistent dengan design

- [ ] **Interactivity**
  - [ ] Buttons respond ke tap/click
  - [ ] Cards respond ke hover (desktop)
  - [ ] Text readable all screen sizes
  - [ ] Scrolling smooth without jank

- [ ] **API Integration**
  - [ ] Events load from backend
  - [ ] Featured event displays correctly
  - [ ] Price formats properly (Rp)
  - [ ] Date formats correctly (DD Mon YYYY • HH:mm)

- [ ] **Performance**
  - [ ] No frame drops during animation
  - [ ] Memory usage reasonable
  - [ ] No memory leaks on navigation
  - [ ] Smooth scrolling dalam features section

- [ ] **Layout Responsiveness**
  - [ ] Desktop (> 800px): 2-column layout
  - [ ] Mobile (< 800px): 1-column layout
  - [ ] Tablets: Proper adaptation
  - [ ] Footer buttons position correctly

## API Testing

### Verify Backend Events Endpoint

```bash
# Test web events endpoint
curl -X GET "http://localhost:8000/api/events?limit=4&active=true" \
  -H "Accept: application/json"

# Expected Response:
{
  "events": [
    {
      "id": 1,
      "title": "Event Name",
      "starts_at": "2025-04-23T20:00:00",
      "venue": "Jakarta",
      "price": 500000,
      "cover_image": "path/to/image.jpg"
    }
  ]
}
```

### Verify Flutter API Call

```dart
// Di flutter command line
flutter logs

// Harus melihat:
// ✅ Loaded 4 hero events
// Event titles dan dates di console
```

## Common Issues & Solutions

### Web Issues

**Issue: Canvas tidak menampilkan**
```
Solution: Check browser DevTools console untuk JS errors
- Ensure hero-3d.js loaded (F12 > Network tab)
- Check console untuk undefined variables
```

**Issue: Particles melambat**
```
Solution: Reduce particle count
// Di hero-3d.js, line ~47
const count = Math.min(100, Math.floor(...)); // Reduce dari 150 ke 100
```

**Issue: Colors berbeda dari design**
```
Solution: Update color hex codes
// Di hero-3d.js, updatekan getRandomGlowColor()
// Di hero-3d.css, updatekan color values
```

### Mobile Issues

**Issue: Crash saat rendering**
```
Solution: Update Flutter
flutter upgrade
flutter clean
flutter pub get
flutter run
```

**Issue: Poor performance**
```
Solution: Reduce particle count
// Di hero_screen_3d.dart line ~37
particles = List.generate(100, (_) => ...); // Change 150 to 100
```

**Issue: Events tidak load**
```
Solution: Check API connectivity
// Make sure backend is running
// Check Hero3DService.dart untuk error logs
// Use flutter logs untuk debug
```

**Issue: Image tidak load**
```
Solution: Verify image URLs
- Check backend storage path
- Verify image exists di public/storage
- Check browser DevTools untuk 404 errors
```

## Performance Metrics

### Target Metrics
- **Web**: 60 FPS, <50MB memory
- **Mobile**: 60 FPS, <150MB memory
- **API Response**: <200ms per request
- **Load Time**: <2s untuk hero section

### Profiling Tools

**Web:**
```bash
# Open DevTools
F12 -> Performance tab
1. Record
2. Interact with hero
3. Stop recording
4. Analyze frame time (target: 16.67ms per frame for 60fps)
```

**Mobile:**
```bash
# Use Flutter DevTools
flutter pub global activate devtools
devtools

# Open in browser, connect app
# Performance tab -> record timeline
# Look for jank (frames > 16.67ms)
```

## Deployment Checklist

### Web Deployment

- [ ] Build optimized assets
  ```bash
  cd backend
  npm run build  # If using build tools
  php artisan optimize
  ```

- [ ] Update cache busting
  ```bash
  php artisan view:clear
  php artisan cache:clear
  ```

- [ ] Compress assets
  ```bash
  # Ensure JS/CSS minified
  gzip -k resources/js/hero-3d.js
  gzip -k resources/css/hero-3d.css
  ```

- [ ] Test production build
  ```bash
  php artisan serve --env=production
  ```

### Mobile Deployment

- [ ] Build APK/AAB (Android)
  ```bash
  flutter build apk --release
  # or
  flutter build appbundle --release
  ```

- [ ] Build iOS
  ```bash
  flutter build ios --release
  ```

- [ ] Run tests
  ```bash
  flutter test
  ```

- [ ] Check performance
  ```bash
  flutter analyze
  ```

## Monitoring

### Web Monitoring

```javascript
// Add to hero-3d.js to track performance
console.time('hero-3d-init');
// ... initialization code
console.timeEnd('hero-3d-init');

// Track FPS
let lastTime = performance.now();
let frameCount = 0;
function monitorFPS() {
  const now = performance.now();
  if (now - lastTime >= 1000) {
    console.log(`FPS: ${frameCount}`);
    frameCount = 0;
    lastTime = now;
  }
  frameCount++;
  requestAnimationFrame(monitorFPS);
}
monitorFPS();
```

### Mobile Monitoring

```dart
// Add telemetry untuk track performance
import 'dart:developer' as developer;

void logPerformance(String name, Duration duration) {
  developer.Timeline.instantSync(name, arguments: {'duration': duration.inMilliseconds});
  debugPrint('$name took ${duration.inMilliseconds}ms');
}
```

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2025-07-03 | Initial release dengan Canvas 3D, Particles, API integration |

## Support

Untuk support atau pertanyaan:
1. Check logs: `flutter logs` atau DevTools Console
2. Review troubleshooting section di atas
3. Check GitHub issues atau project documentation
4. Contact development team

## Next Steps

- [ ] Deploy ke production
- [ ] Monitor performance metrics
- [ ] Gather user feedback
- [ ] Plan enhancements (WebGL, 3D models, real-time)
