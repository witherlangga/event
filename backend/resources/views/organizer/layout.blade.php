<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Organizer Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simpledotcss@1.0.0/simple.min.css">
</head>
<body>
    <header>
        <h1>Organizer Test Panel</h1>
        @if(session('impersonate_user_id'))
            <p>Acting as: {{ \Illuminate\Support\Facades\Auth::user()->name ?? 'Unknown' }} ({{ \Illuminate\Support\Facades\Auth::user()->role ?? '' }})</p>
            <form method="POST" action="{{ route('organizer.logout') }}">@csrf <button type="submit">Stop Impersonation</button></form>
        @else
            <p><a href="{{ route('organizer.login') }}">Login (impersonate)</a></p>
        @endif
    </header>

    <main>
        @if(session('success')) <div style="color:green">{{ session('success') }}</div> @endif
        @if(session('error')) <div style="color:red">{{ session('error') }}</div> @endif
        @yield('content')
    </main>

</body>
</html>
