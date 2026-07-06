@extends('layouts.auth')

@section('title', 'Join')

@section('content')
    <span class="auth-tag">New Member</span>
    <h1 class="auth-title">Join Neon Horizon</h1>
    <p class="auth-subtitle">Create your fan account to book tickets, view order history, and never miss the next live show.</p>

    @if ($errors->any())
        <div class="auth-alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="auth-form" method="POST" action="{{ route('register.submit') }}">
        @csrf

        <div class="auth-field">
            <label for="name">Full Name</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                placeholder="Your full name"
                required
                autofocus
            >
            @error('name')
                <div class="auth-field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="auth-field">
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="you@example.com"
                required
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
                placeholder="Create a password"
                required
            >
            @error('password')
                <div class="auth-field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password_confirmation">Confirm Password</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                placeholder="Repeat your password"
                required
            >
        </div>

        <button type="submit" class="auth-submit">Create Account</button>
    </form>

    <div class="auth-footer">
        <p>Already have an account?</p>
        <a href="{{ route('login') }}">Log in to your account</a>
    </div>
@endsection
