@extends('layouts.panel')

@section('content')
    <section class="hero">
        <div class="hero-copy">
            <span class="hero-tag">ONLINE TICKET</span>
            <h1>Neon Horizon Live</h1>
            <p>Experience the pulse of the band with premium web ticketing, exclusive concerts, and live events delivered directly through your browser.</p>
            <div class="hero-actions">
                <a class="button" href="{{ route('customer.events') }}">Browse Events</a>
                <a class="button secondary" href="{{ route('dev.impersonate') }}">Start as Guest</a>
            </div>
        </div>
        <div class="hero-image">
            <div class="hero-card">
                <span class="card-label">Upcoming Show</span>
                <h2>Live in Jakarta</h2>
                <p>23 April 2025 • 20:00</p>
                <a class="button small" href="{{ route('customer.events') }}">Book Now</a>
            </div>
        </div>
    </section>

    <section class="section-grid">
        <div class="feature-card">
            <h3>Premium Web Experience</h3>
            <p>Full band web portal with event discovery, ticket booking, and order management optimized for modern audiences.</p>
        </div>
        <div class="feature-card">
            <h3>Live Event Showcase</h3>
            <p>Discover concerts, ticket tiers, and event details in a polished music brand layout.</p>
        </div>
        <div class="feature-card">
            <h3>Instant Booking</h3>
            <p>Choose event tickets and complete checkout through the local Laravel interface.</p>
        </div>
    </section>

    <section class="section-events">
        <div class="section-header">
            <div>
                <span class="badge">Featured</span>
                <h2>Featured Concerts</h2>
            </div>
            <a class="link-button" href="{{ route('customer.events') }}">View all events</a>
        </div>

        <div class="events-grid">
            @forelse($events as $event)
                @php
                    $img = null;
                    if (!empty($event->cover_image)) {
                        try { $img = \Illuminate\Support\Facades\Storage::url($event->cover_image); } catch (\Throwable $e) { $img = null; }
                    }
                @endphp

                <a href="{{ route('customer.events.show', ['event' => $event->id]) }}" class="stacked-banner {{ $loop->index == 0 ? '' : 'small' }}" style="background-image: url('{{ $img ?? 'https://picsum.photos/1200/600?random=' . $event->id }}')">
                    <div class="caption">{{ $event->title }} — {{ optional($event->starts_at)->format('d M Y H:i') }} @if($event->venue) • {{ $event->venue }} @endif</div>
                </a>

            @empty
                <div class="event-card empty">
                    <h3>No active concerts found</h3>
                    <p>Check back later when new live shows are available.</p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="section-actions">
        <div class="action-card">
            <h3>Order History</h3>
            <p>Track your completed and pending tickets with a modern customer dashboard.</p>
            <a class="button small" href="{{ route('customer.orders') }}">My Orders</a>
        </div>
        <div class="action-card">
            <h3>Guest Access</h3>
            <p>Use the guest login flow to quickly test web ordering without full account setup.</p>
            <a class="button small" href="{{ route('dev.impersonate') }}">Guest Login</a>
        </div>
    </section>
@endsection
