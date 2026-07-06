@extends('layouts.auth')

@section('title', 'Log In')

@section('content')
    <span class="auth-tag">Fan Access</span>
    <h1 class="auth-title">Welcome Back</h1>
    <p class="auth-subtitle">Sign in to manage your tickets, track orders, and stay connected with Neon Horizon.</p>

    @if ($errors->any())
        <div class="auth-alert">{{ $errors->first() }}</div>
    @endif

    <form class="auth-form" method="POST" action="{{ route('login.submit') }}">
        @csrf

        <div class="auth-field">
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="you@example.com"
                required
                autofocus
            >
            @error('email')
                <div class="auth-field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                required
            >
            @error('password')
                <div class="auth-field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="auth-remember">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Remember me</label>
        </div>

        <button type="submit" class="auth-submit">Log In</button>
    </form>

    <div class="auth-footer">
        <p>Don't have an account yet?</p>
        <a href="{{ route('register') }}">Join the Neon Horizon community</a>
    </div>

    <div class="auth-dev">
        <a href="{{ route('dev.impersonate') }}">Dev Impersonate</a>
    </div>
@endsection
