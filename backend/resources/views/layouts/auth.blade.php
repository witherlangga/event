<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Account') — Neon Horizon</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body class="auth-page">
    <div class="auth-backdrop" aria-hidden="true"></div>
    <div class="auth-glow auth-glow--left" aria-hidden="true"></div>
    <div class="auth-glow auth-glow--right" aria-hidden="true"></div>

    <header class="site-header">
        <div class="topbar">
            <a href="/" class="logo">NEON HORIZON</a>

            <nav class="main-nav">
                <ul>
                    <li><a href="/" class="nav-link">Home</a></li>
                    <li><a href="#" class="nav-link">Store</a></li>
                </ul>
            </nav>

            <div class="nav-auth">
                @if (request()->routeIs('login'))
                    <a href="{{ route('register') }}" class="nav-auth-btn">Join</a>
                @else
                    <a href="{{ route('login') }}" class="nav-auth-link">Log In</a>
                @endif
            </div>
        </div>
    </header>

    <main class="auth-main">
        <div class="auth-card">
            @yield('content')
        </div>
    </main>
</body>
</html>
