import 'dart:math';
import 'package:flutter/material.dart';
import 'dart:ui' as ui;
import '../services/hero_3d_service.dart';

class Hero3DScreen extends StatefulWidget {
  final List<Map<String, dynamic>>? events;

  const Hero3DScreen({
    super.key,
    this.events,
  });

  @override
  State<Hero3DScreen> createState() => _Hero3DScreenState();
}

class _Hero3DScreenState extends State<Hero3DScreen>
    with TickerProviderStateMixin {
  late AnimationController _animationController;
  late AnimationController _particleController;
  Offset _mousePosition = Offset.zero;
  final Random _random = Random();
  late List<Particle> particles;
  
  // Events state
  List<Map<String, dynamic>> _events = [];
  Map<String, dynamic>? _featuredEvent;
  bool _isLoadingEvents = true;

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 3000),
      vsync: this,
    )..repeat(reverse: false);

    _particleController = AnimationController(
      duration: const Duration(milliseconds: 100),
      vsync: this,
    )..repeat();

    particles = List.generate(
      150,
      (_) => Particle(
        x: _random.nextDouble() * 1.0,
        y: _random.nextDouble() * 1.0,
        size: _random.nextDouble() * 1.5 + 0.5,
        vx: (_random.nextDouble() - 0.5) * 0.001,
        vy: (_random.nextDouble() - 0.5) * 0.001,
        color: _getRandomGlowColor(),
      ),
    );
    
    _loadEvents();
  }
  
  Future<void> _loadEvents() async {
    try {
      final events = await Hero3DService.fetchHeroEvents();
      final featured = await Hero3DService.getFeaturedEvent();
      
      if (mounted) {
        setState(() {
          _events = events;
          _featuredEvent = featured;
          _isLoadingEvents = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoadingEvents = false);
      }
    }
  }

  Color _getRandomGlowColor() {
    final colors = [
      const Color(0xFF64C8FF),
      const Color(0xFFFF6B9D),
      const Color(0xFF00FFFF),
      const Color(0xFF00FF88),
    ];
    return colors[_random.nextInt(colors.length)];
  }

  @override
  void dispose() {
    _animationController.dispose();
    _particleController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;

    return Scaffold(
      backgroundColor: const Color(0xFF0A0E27),
      body: Stack(
        children: [
          // Background 3D Canvas
          CustomPaint(
            painter: Hero3DPainter(
              animation: _animationController,
              particles: particles,
              mousePosition: _mousePosition,
            ),
            size: Size(size.width, size.height),
          ),

          // Particle Effects Overlay
          AnimatedBuilder(
            animation: _particleController,
            builder: (context, _) => CustomPaint(
              painter: ParticleEffectsPainter(
                particles: particles,
                time: _particleController.value,
              ),
              size: Size(size.width, size.height),
            ),
          ),

          // Hero Content Overlay
          MouseRegion(
            onHover: (event) {
              setState(() {
                _mousePosition = event.localPosition;
              });
            },
            child: SingleChildScrollView(
              child: Column(
                children: [
                  // Main Hero Section
                  Container(
                    height: size.height * 0.85,
                    padding: EdgeInsets.symmetric(
                      horizontal: size.width * 0.05,
                      vertical: size.height * 0.05,
                    ),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      crossAxisAlignment: size.width > 800
                          ? CrossAxisAlignment.start
                          : CrossAxisAlignment.center,
                      children: [
                        // Hero Content
                        Expanded(
                          child: Row(
                            children: [
                              // Left Column - Copy
                              if (size.width > 800)
                                Expanded(
                                  child: _buildHeroCopy(context, size),
                                ),

                              // Right Column - Card
                              if (size.width > 800)
                                Expanded(
                                  child: _buildHeroCard(context, size),
                                )
                              else
                                ...[
                                  SizedBox(
                                    width: double.infinity,
                                    child: _buildHeroCopy(context, size),
                                  ),
                                  const SizedBox(height: 40),
                                  SizedBox(
                                    width: double.infinity,
                                    child: _buildHeroCard(context, size),
                                  ),
                                ],
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),

                  // Features Section
                  _buildFeaturesSection(context, size),
                ],
              ),
            ),
          ),

          // Footer Buttons
          Positioned(
            bottom: 40,
            right: 40,
            child: size.width > 600
                ? _buildFooterButtons(context)
                : Positioned.fill(
                    child: Align(
                      alignment: Alignment.bottomCenter,
                      child: _buildFooterButtons(context),
                    ),
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeroCopy(BuildContext context, Size size) {
    return Padding(
      padding: const EdgeInsets.only(right: 40),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Tag
          ShaderMask(
            shaderCallback: (bounds) {
              return LinearGradient(
                colors: [
                  const Color(0xFF64C8FF),
                  const Color(0xFF64C8FF).withOpacity(0.6),
                ],
              ).createShader(bounds);
            },
            child: Text(
              'NEON HORIZON LIVE',
              style: Theme.of(context).textTheme.labelLarge?.copyWith(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 3,
                    color: Colors.white,
                  ),
            ),
          ),
          const SizedBox(height: 20),

          // Title
          Text(
            'Experience\nThe Next\nLevel',
            style: Theme.of(context).textTheme.displayLarge?.copyWith(
                  fontSize: 56,
                  fontWeight: FontWeight.w900,
                  height: 1.1,
                  color: Colors.white,
                  shadows: [
                    Shadow(
                      color: const Color(0xFF64C8FF).withOpacity(0.3),
                      blurRadius: 30,
                      offset: const Offset(0, 10),
                    ),
                  ],
                ),
          ),
          const SizedBox(height: 30),

          // Description
          Text(
            'Premium live ticketing with immersive 3D experience. Book your unforgettable moments now.',
            style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                  fontSize: 16,
                  color: const Color(0xFFB0B8D4),
                  height: 1.6,
                ),
            maxLines: 3,
          ),
          const SizedBox(height: 40),

          // Buttons
          Wrap(
            spacing: 20,
            children: [
              _buildButton(
                context,
                'Get Tickets',
                const Color(0xFFFF6B9D),
                true,
                onPressed: () {
                  // Navigate to events
                },
              ),
              _buildButton(
                context,
                'Explore As Guest',
                const Color(0xFF64C8FF),
                false,
                onPressed: () {
                  // Navigate to guest mode
                },
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildHeroCard(BuildContext context, Size size) {
    final event = _featuredEvent ?? {
      'title': 'Live in Jakarta',
      'starts_at': '2025-04-23T20:00:00',
      'venue': 'Jakarta Convention Center',
      'price': 500000,
    };

    return Align(
      alignment: Alignment.centerRight,
      child: Hero3DCard(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Badge
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: const Color(0xFFFF6B9D).withOpacity(0.2),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  'NEXT SHOW',
                  style: Theme.of(context).textTheme.labelSmall?.copyWith(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        letterSpacing: 2,
                        color: const Color(0xFFFF6B9D),
                      ),
                ),
              ),
              const SizedBox(height: 20),

              // Event Title
              Text(
                event['title'] ?? 'Live Performance',
                style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                      fontWeight: FontWeight.w900,
                      color: Colors.white,
                    ),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 12),

              // Date
              Text(
                Hero3DService.formatEventDate(event['starts_at']),
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: const Color(0xFF64C8FF),
                      fontWeight: FontWeight.w600,
                    ),
              ),
              const SizedBox(height: 8),

              // Price
              Text(
                'From ${Hero3DService.formatPrice(event['price'] ?? event['min_price'])}',
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: const Color(0xFFB0B8D4),
                    ),
              ),
              const SizedBox(height: 28),

              // Button
              SizedBox(
                width: double.infinity,
                child: _buildButton(
                  context,
                  'Book Now',
                  const Color(0xFFFF6B9D),
                  true,
                  onPressed: () {
                    // Book event
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildFeaturesSection(BuildContext context, Size size) {
    return Container(
      width: double.infinity,
      padding: EdgeInsets.symmetric(
        horizontal: size.width * 0.05,
        vertical: 60,
      ),
      child: Wrap(
        spacing: 30,
        runSpacing: 30,
        alignment: WrapAlignment.center,
        children: [
          _buildFeatureCard(
            context,
            'Premium Web Experience',
            'Full band web portal with event discovery, ticket booking, and order management.',
          ),
          _buildFeatureCard(
            context,
            'Live Event Showcase',
            'Discover concerts, ticket tiers, and event details in a polished layout.',
          ),
          _buildFeatureCard(
            context,
            'Instant Booking',
            'Choose event tickets and complete checkout through our interface.',
          ),
        ],
      ),
    );
  }

  Widget _buildFeatureCard(BuildContext context, String title, String description) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(16),
      child: BackdropFilter(
        filter: ui.ImageFilter.blur(sigmaX: 10, sigmaY: 10),
        child: Container(
          constraints: const BoxConstraints(maxWidth: 300),
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: const Color(0xFF1E2850).withOpacity(0.4),
            border: Border.all(color: const Color(0xFF64C8FF).withOpacity(0.2)),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                title,
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w700,
                      color: Colors.white,
                    ),
              ),
              const SizedBox(height: 12),
              Text(
                description,
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: const Color(0xFFB0B8D4),
                      height: 1.6,
                    ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildButton(
    BuildContext context,
    String label,
    Color color,
    bool filled, {
    required VoidCallback onPressed,
  }) {
    return ElevatedButton(
      onPressed: onPressed,
      style: ElevatedButton.styleFrom(
        backgroundColor: filled ? color : Colors.transparent,
        foregroundColor: Colors.white,
        padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 14),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(50),
          side: !filled
              ? BorderSide(color: color, width: 2)
              : BorderSide.none,
        ),
        elevation: filled ? 8 : 0,
        shadowColor: filled ? color.withOpacity(0.4) : Colors.transparent,
      ),
      child: Text(
        label,
        style: Theme.of(context).textTheme.labelSmall?.copyWith(
              fontWeight: FontWeight.w600,
              letterSpacing: 1,
              fontSize: 13,
              color: filled ? Colors.white : color,
            ),
      ),
    );
  }

  Widget _buildFooterButtons(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      spacing: 20,
      children: [
        _buildSocialButton(context, 'FOLLOW', onPressed: () {}),
        _buildSocialButton(context, 'SUBSCRIBE', onPressed: () {}),
      ],
    );
  }

  Widget _buildSocialButton(
    BuildContext context,
    String label, {
    required VoidCallback onPressed,
  }) {
    return OutlinedButton(
      onPressed: onPressed,
      style: OutlinedButton.styleFrom(
        side: const BorderSide(
          color: Color(0xFF64C8FF),
          width: 2,
        ),
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(30),
        ),
      ),
      child: Text(
        label,
        style: Theme.of(context).textTheme.labelSmall?.copyWith(
              fontWeight: FontWeight.w600,
              letterSpacing: 2,
              fontSize: 11,
              color: const Color(0xFF64C8FF),
            ),
      ),
    );
  }
}

// Hero 3D Card Widget
class Hero3DCard extends StatefulWidget {
  final Widget child;

  const Hero3DCard({
    super.key,
    required this.child,
  });

  @override
  State<Hero3DCard> createState() => _Hero3DCardState();
}

class _Hero3DCardState extends State<Hero3DCard>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  Offset _tapPosition = Offset.zero;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      duration: const Duration(milliseconds: 500),
      vsync: this,
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return MouseRegion(
      onEnter: (_) => _controller.forward(),
      onExit: (_) => _controller.reverse(),
      child: GestureDetector(
        onTapDown: (details) {
          setState(() => _tapPosition = details.localPosition);
        },
        child: AnimatedBuilder(
          animation: _controller,
          builder: (context, child) {
            return Transform(
              alignment: Alignment.center,
              transform: Matrix4.identity()
                ..setEntry(3, 2, 0.001)
                ..rotateX((_tapPosition.dy - 200) * 0.0001 * _controller.value)
                ..rotateY((_tapPosition.dx - 200) * 0.0001 * _controller.value),
              child: Container(
                decoration: BoxDecoration(
                  color: const Color(0xFF1E2850).withOpacity(0.6),
                  border: Border.all(
                    color: Color.lerp(
                          const Color(0xFF64C8FF).withOpacity(0.3),
                          const Color(0xFF64C8FF),
                          _controller.value,
                        ) ??
                        const Color(0xFF64C8FF),
                  ),
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF64C8FF)
                          .withOpacity(0.2 * _controller.value),
                      blurRadius: 20 + (_controller.value * 40),
                      spreadRadius: 0,
                    ),
                  ],
                ),
                child: BackdropFilter(
                  filter: ui.ImageFilter.blur(sigmaX: 10, sigmaY: 10),
                  child: widget.child,
                ),
              ),
            );
          },
        ),
      ),
    );
  }
}

// 3D Canvas Painter
class Hero3DPainter extends CustomPainter {
  final Animation<double> animation;
  final List<Particle> particles;
  final Offset mousePosition;

  Hero3DPainter({
    required this.animation,
    required this.particles,
    required this.mousePosition,
  });

  @override
  void paint(Canvas canvas, Size size) {
    // Draw background gradient
    _drawBackgroundGradient(canvas, size);

    // Draw waves
    _drawWaves(canvas, size);

    // Draw particles
    _drawParticles(canvas, size);
  }

  void _drawBackgroundGradient(Canvas canvas, Size size) {
    final gradient = ui.Gradient.linear(
      Offset(0, 0),
      Offset(size.width, size.height),
      [
        const Color(0xFF0A0E27),
        const Color(0xFF1A2550),
        const Color(0xFF0F1535),
      ],
      [0.0, 0.5, 1.0],
    );

    canvas.drawRect(
      Rect.fromLTWH(0, 0, size.width, size.height),
      Paint()..shader = gradient,
    );

    // Radial glow from mouse
    final radialGradient = ui.Gradient.radial(
      mousePosition,
      400,
      [
        const Color(0xFF64C8FF).withOpacity(0.1),
        const Color(0xFF64C8FF).withOpacity(0),
      ],
      [0.0, 1.0],
    );

    canvas.drawRect(
      Rect.fromLTWH(0, 0, size.width, size.height),
      Paint()..shader = radialGradient,
    );
  }

  void _drawWaves(Canvas canvas, Size size) {
    final waves = [
      {'amplitude': 80.0, 'frequency': 0.005, 'phase': 0.0, 'y': size.height * 0.4, 'color': const Color(0xFF64C8FF)},
      {'amplitude': 60.0, 'frequency': 0.008, 'phase': pi / 4, 'y': size.height * 0.5, 'color': const Color(0xFFFF6B9D)},
      {'amplitude': 40.0, 'frequency': 0.003, 'phase': pi / 2, 'y': size.height * 0.6, 'color': const Color(0xFF00FFFF)},
    ];

    for (var i = 0; i < waves.length; i++) {
      final wave = waves[i];
      final amplitude = (wave['amplitude'] as num).toDouble();
      final frequency = (wave['frequency'] as num).toDouble();
      final phase = (wave['phase'] as num).toDouble();
      final yOffset = (wave['y'] as num).toDouble();
      final color = wave['color'] as Color;

      final paint = Paint()
        ..color = color.withOpacity(0.4 - i * 0.1)
        ..strokeWidth = 2 - i * 0.5
        ..style = PaintingStyle.stroke;

      final path = ui.Path();

      for (int x = 0; x < size.width.toInt(); x += 5) {
        final y = yOffset +
            sin(x * frequency + animation.value * 0.02 + phase) * amplitude +
            sin(x * frequency * 0.5) * (amplitude * 0.3);

        if (x == 0) {
          path.moveTo(x.toDouble(), y);
        } else {
          path.lineTo(x.toDouble(), y);
        }
      }

      canvas.drawPath(path, paint);
    }
  }

  void _drawParticles(Canvas canvas, Size size) {
    for (final particle in particles) {
      final paint = Paint()
        ..color = particle.color.withOpacity(particle.opacity)
        ..style = PaintingStyle.fill;

      final x = particle.x * size.width;
      final y = particle.y * size.height;

      canvas.drawCircle(Offset(x, y), particle.size, paint);

      // Glow effect
      canvas.drawCircle(
        Offset(x, y),
        particle.size * 0.5,
        Paint()
          ..color = particle.color.withOpacity(particle.opacity * 0.5)
          ..style = PaintingStyle.fill,
      );
    }
  }

  @override
  bool shouldRepaint(covariant Hero3DPainter oldDelegate) => true;
}

// Particle Effects Painter
class ParticleEffectsPainter extends CustomPainter {
  final List<Particle> particles;
  final double time;

  ParticleEffectsPainter({
    required this.particles,
    required this.time,
  });

  @override
  void paint(Canvas canvas, Size size) {
    for (final particle in particles) {
      particle.x += particle.vx;
      particle.y += particle.vy;

      if (particle.x < 0) particle.x = 1;
      if (particle.x > 1) particle.x = 0;
      if (particle.y < 0) particle.y = 1;
      if (particle.y > 1) particle.y = 0;
    }
  }

  @override
  bool shouldRepaint(covariant ParticleEffectsPainter oldDelegate) => true;
}

// Particle Class
class Particle {
  double x;
  double y;
  final double size;
  double vx;
  double vy;
  final Color color;
  double opacity = 0.5;

  Particle({
    required this.x,
    required this.y,
    required this.size,
    required this.vx,
    required this.vy,
    required this.color,
  });
}
