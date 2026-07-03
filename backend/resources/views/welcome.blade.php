<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Neon Horizon - Event Ticketing Platform</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    
    <link rel="stylesheet" href="{{ asset('css/ui-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/hero-3d.css') }}">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { width: 100%; height: 100%; }
        
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(10, 14, 39, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(100, 200, 255, 0.1);
            padding: 16px 60px;
        }
        
        .navbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #FFFFFF 0%, #64C8FF 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .navbar-links {
            display: flex;
            gap: 40px;
            align-items: center;
        }
        
        .navbar-link {
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .navbar-link:hover {
            color: var(--secondary);
        }
        
        .navbar-buttons {
            display: flex;
            gap: 12px;
        }
        
        .hero-wrapper {
            margin-top: 80px;
        }
        
        .features-section {
            background: linear-gradient(135deg, rgba(10, 14, 39, 0.5) 0%, rgba(30, 40, 80, 0.5) 100%);
            padding: 80px 60px;
        }
        
        .features-title {
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 60px;
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 80px;
        }
        
        .feature-card {
            text-align: center;
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 16px;
        }
        
        .feature-card h3 {
            color: var(--text-primary);
            margin-bottom: 12px;
        }
        
        .feature-card p {
            color: var(--text-secondary);
        }
        
        .cta-section {
            text-align: center;
            padding: 60px;
            background: linear-gradient(135deg, rgba(255, 107, 157, 0.1) 0%, rgba(100, 200, 255, 0.1) 100%);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            margin: 60px 0;
        }
        
        .cta-section h2 {
            margin-bottom: 24px;
        }
        
        .footer {
            background: rgba(10, 14, 39, 0.8);
            border-top: 1px solid var(--border-color);
            padding: 60px;
            text-align: center;
            color: var(--text-secondary);
        }
        
        @media (max-width: 1200px) {
            .navbar {
                padding: 16px 40px;
            }
            
            .feature-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .navbar {
                padding: 12px 20px;
            }
            
            .navbar-links {
                gap: 20px;
            }
            
            .features-section {
                padding: 40px 20px;
            }
            
            .feature-grid {
                grid-template-columns: 1fr;
            }
            
            .features-title {
                font-size: 1.75rem;
            }
            
            .cta-section {
                margin: 40px 0;
                padding: 40px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-brand">✨ Neon Horizon</div>
            <div class="navbar-links">
                <a href="#features" class="navbar-link">Features</a>
                <a href="#cta" class="navbar-link">Get Started</a>
                
                <div class="navbar-buttons">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-secondary">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-secondary">Login</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-wrapper">
        @include('local_web')
    </div>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <h2 class="features-title">Why Choose Neon Horizon?</h2>
        
        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon">🎫</div>
                <h3>Easy Booking</h3>
                <p>Book your favorite events in just a few clicks with our intuitive interface.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔐</div>
                <h3>Secure Transactions</h3>
                <p>Your data is protected with enterprise-grade security and encryption.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📱</div>
                <h3>Multi-Platform</h3>
                <p>Access your tickets on web and mobile with our native applications.</p>
            </div>
        </div>

        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h3>Real-time Updates</h3>
                <p>Get instant notifications about events and ticket availability.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎯</div>
                <h3>Smart Filtering</h3>
                <p>Find events by category, date, location, and price range.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💰</div>
                <h3>Great Prices</h3>
                <p>Competitive pricing with exclusive deals and early-bird discounts.</p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <div class="section">
        <div class="cta-section" id="cta">
            <h2>Ready to Experience the Next Level?</h2>
            <p style="margin-bottom: 30px; color: var(--text-secondary); font-size: 1.1rem;">
                Join thousands of event enthusiasts and discover amazing experiences.
            </p>
            
            @auth
                <a href="{{ url('/dashboard') }}" class="btn btn-primary" style="display: inline-block; padding: 14px 40px; font-size: 1.1rem;">
                    → Go to Dashboard
                </a>
            @else
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <a href="{{ route('register') }}" class="btn btn-primary" style="display: inline-block; padding: 14px 40px; font-size: 1.1rem;">
                        → Create Account
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-secondary" style="display: inline-block; padding: 14px 40px; font-size: 1.1rem;">
                        ← Already have account?
                    </a>
                </div>
            @endauth
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2025 Neon Horizon Events. All rights reserved.</p>
        <p style="font-size: 0.9rem; margin-top: 12px;">Building amazing experiences one event at a time ✨</p>
    </footer>

    <script src="{{ asset('js/hero-3d.js') }}"></script>
</body>
</html>
