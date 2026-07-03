<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Neon Horizon</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }

        :root {
            --primary: #1A1A2E;
            --surface: #16213E;
            --accent: #E94560;
            --gold: #F5A623;
            --background: #0F0F1A;
            --card: #1E1E32;
            --text-primary: #F5F5F5;
            --text-secondary: #B0B0C0;
            --border-color: #2A2A45;
        }

        html, body {
            width: 100%;
            height: 100%;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--background) 0%, var(--primary) 50%, var(--surface) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: var(--card);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            border: 1px solid var(--border-color);
            padding: 40px 32px;
            backdrop-filter: blur(10px);
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-icon {
            font-size: 64px;
            margin-bottom: 16px;
            display: block;
        }

        .login-brand {
            color: var(--text-primary);
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .login-tagline {
            color: var(--text-secondary);
            font-size: 13px;
            margin-bottom: 32px;
        }

        .login-title {
            color: var(--text-primary);
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .form-input-icon {
            position: absolute;
            left: 14px;
            color: var(--text-secondary);
            font-size: 18px;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            background: var(--surface);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 12px 14px 12px 44px;
            color: var(--text-primary);
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 12px rgba(233, 69, 96, 0.3);
            background: var(--surface);
        }

        .form-input::placeholder {
            color: var(--text-secondary);
        }

        .error-message {
            color: #FF6B6B;
            font-size: 12px;
            margin-top: 6px;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .alert-error {
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid rgba(255, 107, 107, 0.3);
            border-radius: 10px;
            padding: 12px 16px;
            color: #FF6B6B;
            font-size: 13px;
            margin-bottom: 20px;
            display: none;
        }

        .alert-error.show {
            display: block;
        }

        .login-button {
            width: 100%;
            background: linear-gradient(135deg, var(--accent) 0%, #C73350 100%);
            border: none;
            border-radius: 10px;
            padding: 14px 20px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            position: relative;
            overflow: hidden;
        }

        .login-button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .login-button:hover::before {
            width: 300px;
            height: 300px;
        }

        .login-button:hover {
            box-shadow: 0 10px 30px rgba(233, 69, 96, 0.4);
            transform: translateY(-2px);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .login-button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .button-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
            z-index: 1;
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .login-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--border-color);
        }

        .login-footer-text {
            color: var(--text-secondary);
            font-size: 13px;
            margin-bottom: 12px;
        }

        .login-footer-link {
            display: inline-block;
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s ease;
            position: relative;
        }

        .login-footer-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent);
            transition: width 0.3s ease;
        }

        .login-footer-link:hover::after {
            width: 100%;
        }

        .dev-access {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--border-color);
            text-align: center;
        }

        .dev-access-text {
            color: var(--text-secondary);
            font-size: 12px;
            margin-bottom: 12px;
        }

        .dev-access-link {
            display: inline-block;
            color: var(--gold);
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            background: rgba(245, 166, 35, 0.1);
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid rgba(245, 166, 35, 0.3);
            transition: all 0.3s ease;
        }

        .dev-access-link:hover {
            background: rgba(245, 166, 35, 0.2);
            border-color: rgba(245, 166, 35, 0.5);
        }

        /* Icons using Unicode */
        .icon-email::before { content: '✉️'; }
        .icon-password::before { content: '🔒'; }
        .icon-user::before { content: '🎵'; }

        @media (max-width: 480px) {
            .login-card {
                padding: 28px 20px;
            }

            .login-header {
                margin-bottom: 32px;
            }

            .login-icon {
                font-size: 48px;
            }

            .login-brand {
                font-size: 20px;
            }

            .login-title {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="login-icon">🎵</div>
                <div class="login-brand">Neon Horizon</div>
                <div class="login-tagline">Premium Event Ticketing Platform</div>
            </div>

            <!-- Title -->
            <div class="login-title">Masuk ke Akun Anda</div>

            <!-- Error Alert -->
            @if ($errors->any())
                <div class="alert-error show">
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- Login Form -->
            <form id="loginForm" method="POST" action="{{ route('login.submit') }}">
                @csrf

                <!-- Email Input -->
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <div class="form-input-wrapper">
                        <span class="form-input-icon">✉️</span>
                        <input 
                            type="email" 
                            id="email"
                            name="email" 
                            class="form-input" 
                            placeholder="masukkan email anda"
                            value="{{ old('email') }}"
                            required
                            autofocus
                        >
                    </div>
                    @error('email')
                        <div class="error-message show">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password Input -->
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="form-input-wrapper">
                        <span class="form-input-icon">🔒</span>
                        <input 
                            type="password" 
                            id="password"
                            name="password" 
                            class="form-input" 
                            placeholder="masukkan password anda"
                            required
                        >
                    </div>
                    @error('password')
                        <div class="error-message show">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                    <input type="checkbox" id="remember" name="remember" style="width: 16px; height: 16px;">
                    <label for="remember" class="form-label" style="margin-bottom: 0;">Ingat saya</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="login-button" id="submitBtn">
                    <div class="button-content">
                        <span id="btnText">Masuk</span>
                    </div>
                </button>
            </form>

            <!-- Footer -->
            <div class="login-footer">
                <div class="login-footer-text">Belum punya akun?</div>
                <a href="/register" class="login-footer-link">Daftar sebagai Fan →</a>
            </div>

            <!-- Dev Access -->
            <div class="dev-access">
                <div class="dev-access-text">🚀 Testing Mode</div>
                <a href="/dev/impersonate" class="dev-access-link">Dev Impersonate →</a>
            </div>
        </div>
    </div>
</body>
</html>
