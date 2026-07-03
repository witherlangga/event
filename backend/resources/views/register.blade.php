<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - Neon Horizon</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #1A1A2E;
            --surface: #16213E;
            --accent: #E94560;
            --border: #2A2A45;
            --text: #F5F5F5;
            --text-secondary: #B0B0C0;
        }
        html, body { width: 100%; height: 100%; font-family: 'Inter', system-ui, sans-serif; }
        body { background: linear-gradient(135deg, var(--primary), var(--surface)); display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { width: 100%; max-width: 420px; background: rgba(20, 24, 48, 0.96); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 36px 32px; box-shadow: 0 24px 90px rgba(0,0,0,0.35); }
        .card h1 { color: var(--text); font-size: 2rem; margin-bottom: 0.5rem; }
        .card p { color: var(--text-secondary); margin-bottom: 1.75rem; }
        .input-group { margin-bottom: 18px; }
        label { display: block; color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 8px; }
        input { width: 100%; border: 1px solid rgba(255,255,255,0.08); background: rgba(20,24,48,0.9); color: var(--text); border-radius: 12px; padding: 14px 16px; font-size: 0.95rem; }
        input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 4px rgba(233,69,96,0.1); }
        .error { color: #FF6B6B; margin-top: 8px; font-size: 0.9rem; }
        .actions { display: grid; gap: 14px; margin-top: 1rem; }
        button { width: 100%; border: none; border-radius: 12px; padding: 14px 0; background: linear-gradient(135deg, var(--accent), #ff6b85); color: white; font-weight: 700; cursor: pointer; }
        .secondary-link { display: block; text-align: center; color: var(--text-secondary); text-decoration: none; margin-top: 10px; font-size: 0.95rem; }
        .alert { background: rgba(229,69,96,0.12); border: 1px solid rgba(229,69,96,0.18); color: #ffb3c1; border-radius: 12px; padding: 14px 16px; margin-bottom: 18px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Buat Akun</h1>
        <p>Daftar sekarang untuk pesan tiket, lihat order, dan akses fitur fan.</p>

        @if ($errors->any())
            <div class="alert">
                <ul style="margin:0; padding-left: 1.2rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.submit') }}">
            @csrf
            <div class="input-group">
                <label for="name">Nama Lengkap</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>
            </div>
            <div class="input-group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
            </div>
            <div class="input-group">
                <label for="password_confirmation">Konfirmasi Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required>
            </div>

            <div class="actions">
                <button type="submit">Daftar</button>
                <a href="{{ route('login') }}" class="secondary-link">Sudah punya akun? Masuk</a>
            </div>
        </form>
    </div>
</body>
</html>
