@extends('layouts.panel')

@section('content')
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-brand">Admin Panel</div>
            <h2 class="admin-sidebar-title">Settings</h2>

            <nav class="admin-nav">
                <a href="{{ route('admin.events') }}" class="admin-nav-link {{ request()->routeIs('admin.events*') ? 'active' : '' }}">
                    Event &amp; Tickets
                </a>
                <a href="{{ route('admin.users') }}" class="admin-nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    User Management
                </a>
            </nav>

            <div class="admin-sidebar-footer">
                <a href="{{ url('/') }}">← Back to Homepage</a>
            </div>
        </aside>

        <div class="admin-content">
            @yield('admin-content')
        </div>
    </div>

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <style>
        main {
            width: min(1400px, calc(100% - 36px));
        }
    </style>
@endsection
