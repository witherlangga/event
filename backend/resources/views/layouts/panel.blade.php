<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Neon Horizon — Dev Panel</title>
    <style>
        :root {
            color-scheme: dark;
            --bg: #0F0F1A;
            --surface: #16213E;
            --card: #1E1E32;
            --accent: #E94560;
            --gold: #F5A623;
            --text: #F5F5F5;
            --text-secondary: #B0B0C0;
            --border: rgba(255,255,255,0.08);
            --shadow: 0 24px 80px rgba(0,0,0,0.35);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: radial-gradient(circle at top, rgba(233,69,96,0.08), transparent 28%),
                        radial-gradient(circle at bottom right, rgba(245,166,35,0.06), transparent 32%),
                        var(--bg);
            color: var(--text);
        }

        .site-header {
            position: sticky;
            top: 0;
            z-index: 40;
            background: rgba(8, 10, 22, 0.72);
            border-bottom: 1px solid rgba(100, 200, 255, 0.12);
            backdrop-filter: blur(14px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0.85rem 1.25rem;
        }

        .logo {
            color: var(--text);
            font-weight: 900;
            font-size: 1.05rem;
            text-decoration: none;
            letter-spacing: 0.14em;
            white-space: nowrap;
            transition: color 0.2s ease, text-shadow 0.2s ease;
        }

        .logo:hover {
            color: #fff;
            text-shadow: 0 0 18px rgba(100, 200, 255, 0.45);
        }

        .main-nav ul {
            display: flex;
            gap: 0.35rem;
            list-style: none;
            margin: 0;
            padding: 0;
            align-items: center;
        }

        .nav-link {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.92rem;
            letter-spacing: 0.04em;
            padding: 0.55rem 1rem;
            border-radius: 999px;
            transition: color 0.2s ease, background 0.2s ease;
        }

        .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.06);
        }

        .nav-auth {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            flex-shrink: 0;
        }

        .nav-auth form {
            margin: 0;
        }

        .nav-auth-link {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.92rem;
            padding: 0.55rem 0.85rem;
            border-radius: 999px;
            transition: color 0.2s ease;
        }

        .nav-auth-link:hover {
            color: #fff;
        }

        .nav-auth-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem 1.25rem;
            border-radius: 999px;
            background: linear-gradient(135deg, #ff6b9d, #e94560);
            color: #fff;
            font-weight: 700;
            font-size: 0.88rem;
            letter-spacing: 0.06em;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .nav-auth-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(233, 69, 96, 0.35);
        }

        .nav-auth-btn-outline {
            background: transparent;
            border: 1px solid rgba(100, 200, 255, 0.35);
            color: #64c8ff;
        }

        .nav-auth-btn-outline:hover {
            background: rgba(100, 200, 255, 0.08);
            box-shadow: 0 0 20px rgba(100, 200, 255, 0.2);
        }

        @media (max-width: 720px) {
            .topbar {
                gap: 1rem;
                padding: 0.75rem 1rem;
            }

            .logo {
                font-size: 0.92rem;
                letter-spacing: 0.1em;
            }

            .main-nav ul {
                gap: 0.15rem;
            }

            .nav-link {
                padding: 0.45rem 0.65rem;
                font-size: 0.85rem;
            }

            .nav-auth-link {
                display: none;
            }

            .nav-auth-btn {
                padding: 0.55rem 1rem;
                font-size: 0.82rem;
            }
        }

        /* Banner / stacked event style */
        .stacked-banner {
            width: 100%;
            display: block;
            margin: 1rem 0;
            border-radius: 6px;
            overflow: hidden;
            position: relative;
            min-height: 230px;
            background-size: cover;
            background-position: center;
            box-shadow: 0 18px 60px rgba(0,0,0,0.45);
        }

        .stacked-banner .caption {
            position: absolute;
            left: 12px;
            bottom: 12px;
            background: rgba(0,0,0,0.6);
            color: #fff;
            padding: 10px 14px;
            border-radius: 6px;
            max-width: calc(100% - 28px);
            font-weight: 700;
        }

        .stacked-banner.small { min-height: 140px; }

        form button,
        .button,
        a.button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.95rem 1.6rem;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--accent), #ff6b81);
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease, opacity 0.2s ease, box-shadow 0.2s ease;
            text-decoration: none;
        }

        .button.small {
            padding: 0.75rem 1.2rem;
            font-size: 0.95rem;
        }

        .button.secondary {
            background: rgba(255,255,255,0.08);
            color: var(--text);
            border: 1px solid rgba(255,255,255,0.12);
        }

        form button:hover,
        .button:hover,
        a.button:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(233, 69, 96, 0.3);
        }

        main {
            width: min(1200px, calc(100% - 36px));
            margin: 2rem auto;
            padding-bottom: 2.5rem;
        }

        .hero {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: 1.4fr 1fr;
            align-items: center;
            margin-bottom: 2rem;
        }

        .hero-copy {
            padding: 2rem 2rem 2rem 2rem;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 28px;
            box-shadow: 0 24px 80px rgba(0,0,0,0.25);
        }

        .hero-tag {
            display: inline-flex;
            padding: 0.5rem 0.9rem;
            border-radius: 999px;
            background: rgba(233,69,96,0.18);
            color: #ffb3c1;
            font-size: 0.85rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .hero-copy h1 {
            font-size: clamp(2.5rem, 3vw, 4.25rem);
            line-height: 1.02;
            margin: 0;
            color: #ffffff;
        }

        .hero-copy p {
            max-width: 680px;
            margin: 1.2rem 0 1.5rem;
            font-size: 1rem;
            line-height: 1.8;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
        }

        .hero-image {
            display: flex;
            justify-content: flex-end;
        }

        .hero-card {
            width: 100%;
            max-width: 420px;
            padding: 2rem;
            border-radius: 28px;
            background: linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.03));
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 30px 70px rgba(0,0,0,0.28);
        }

        .hero-card h2,
        .hero-card p {
            margin: 0;
        }

        .hero-card h2 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            color: #fff;
        }

        .hero-card p {
            color: var(--text-secondary);
            margin-bottom: 1.4rem;
        }

        .section-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .section-social {
            grid-template-columns: repeat(3, 1fr);
            max-width: 720px;
            margin-left: auto;
            margin-right: auto;
        }

        .social-logo-card {
            align-items: center;
            justify-content: center;
            min-height: 140px;
            padding: 2rem;
        }

        .social-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 72px;
            height: 72px;
        }

        .social-logo svg {
            width: 100%;
            height: 100%;
        }

        .social-logo-instagram {
            color: #E4405F;
        }

        .social-logo-youtube {
            color: #FF0000;
        }

        .social-logo-tiktok {
            color: #ffffff;
        }

        a.social-logo-card:hover .social-logo-instagram {
            color: #ff6b81;
        }

        a.social-logo-card:hover .social-logo-youtube {
            color: #ff4444;
        }

        a.social-logo-card:hover .social-logo-tiktok {
            color: #64c8ff;
        }

        .section-moments {
            margin-top: 2.5rem;
        }

        .moments-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        .moment-frame {
            margin: 0;
            aspect-ratio: 1 / 1;
            border-radius: 20px;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.12);
            background: rgba(0, 0, 0, 0.35);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.35);
            transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        }

        .moment-frame:hover {
            transform: translateY(-3px);
            border-color: rgba(100, 200, 255, 0.45);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4), 0 0 20px rgba(100, 200, 255, 0.12);
        }

        .moment-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .band-message {
            margin: 2rem 0 0;
            padding: 2rem 2.25rem;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-left: 4px solid var(--accent);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.2);
        }

        .band-message p {
            margin: 0;
            font-size: 1.05rem;
            line-height: 1.85;
            color: var(--text);
            font-style: italic;
        }

        .band-message footer {
            margin-top: 1.25rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            color: #64c8ff;
            font-style: normal;
        }

        .feature-card,
        .event-card,
        .action-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 26px;
            padding: 1.75rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.16);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .feature-card h3,
        .event-card h3,
        .action-card h3 {
            margin: 0;
            font-size: 1.25rem;
            color: #fff;
        }

        .bgm-content-area {
            position: relative;
            width: 100vw;
            margin-left: calc(50% - 50vw);
            margin-top: 2rem;
            padding: 2.5rem max(18px, calc((100vw - min(1200px, calc(100% - 36px))) / 2 + 18px)) 3rem;
            background-image: url("{{ asset('assets/bgmusic.jpg') }}");
            background-size: cover;
            background-position: center top;
            background-repeat: no-repeat;
        }

        .bgm-content-area::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(10, 14, 39, 0.82) 0%, rgba(10, 14, 39, 0.88) 100%);
            z-index: 0;
        }

        .bgm-content-area > * {
            position: relative;
            z-index: 1;
        }

        a.feature-link {
            text-decoration: none;
            color: inherit;
            cursor: pointer;
            transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        }

        a.feature-link:hover {
            transform: translateY(-4px);
            border-color: rgba(100, 200, 255, 0.45);
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.35), 0 0 24px rgba(100, 200, 255, 0.15);
        }

        @media (max-width: 900px) {
            .moments-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 680px) {
            .section-social {
                grid-template-columns: repeat(3, 1fr);
                max-width: 100%;
            }

            .social-logo-card {
                min-height: 110px;
                padding: 1.25rem;
            }

            .social-logo {
                width: 56px;
                height: 56px;
            }

            .band-message {
                padding: 1.5rem 1.25rem;
            }
        }

        .event-meta {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        .events-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
        }

        .event-card.empty {
            align-items: start;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
            margin-bottom: 1rem;
        }

        .link-button {
            color: var(--accent);
            text-decoration: none;
            font-weight: 700;
        }

        .section-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            box-shadow: 0 20px 55px rgba(0,0,0,0.14);
            padding: 1.75rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 960px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .hero-image {
                justify-content: center;
            }
        }

        @media (max-width: 680px) {
            .hero-copy h1 {
                font-size: 2.25rem;
            }

            .hero {
                gap: 1rem;
            }
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: var(--shadow);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .grid {
            display: grid;
            gap: 1rem;
        }

        .grid-3 {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        h2 {
            margin-top: 0;
            font-size: 1.75rem;
            color: #fff;
        }

        p,
        li,
        label,
        table td,
        table th {
            color: var(--text-secondary);
        }

        ul {
            padding-left: 1.25rem;
            margin: 0.75rem 0 0;
        }

        a {
            color: var(--accent);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: #1f2137;
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .alert-success,
        .alert-error {
            border-radius: 18px;
            padding: 1rem 1.15rem;
            margin-bottom: 1rem;
            border: 1px solid var(--border);
        }

        .alert-success {
            background: rgba(42, 161, 152, 0.12);
            color: #b7f2d1;
        }

        .alert-error {
            background: rgba(229, 69, 96, 0.12);
            color: #ffb3c1;
        }
    </style>
    <!-- UI System Styles -->
    <link rel="stylesheet" href="{{ asset('css/ui-system.css') }}">
    <!-- Hero 3D Styles -->
    <link rel="stylesheet" href="{{ asset('css/hero-3d.css') }}">
</head>
<body>
    <header class="site-header">
        <div class="topbar">
            <a href="/" class="logo">NEON HORIZON</a>

            <nav class="main-nav">
                <ul>
                    <li><a href="/" class="nav-link">Home</a></li>
                    <li><a href="#" class="nav-link">Store</a></li>
                    @auth
                        @if (auth()->user()->isSystemAdmin())
                            <li><a href="{{ route('admin.dashboard') }}" class="nav-link">Panel Admin</a></li>
                        @endif
                    @endauth
                </ul>
            </nav>

            <div class="nav-auth">
                @guest
                    <a href="{{ route('login') }}" class="nav-auth-link">Log In</a>
                    <a href="{{ route('register') }}" class="nav-auth-btn">Join</a>
                @else
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nav-auth-btn nav-auth-btn-outline">Logout</button>
                    </form>
                @endguest
            </div>
        </div>
    </header>

    <main>
        @if(session('success')) <div class="alert-success">{{ session('success') }}</div> @endif
        @if(session('error')) <div class="alert-error">{{ session('error') }}</div> @endif
        @yield('content')
    </main>
    <!-- Hero 3D Scripts -->
    <script src="{{ asset('js/hero-3d.js') }}"></script>
</body>
</html>
