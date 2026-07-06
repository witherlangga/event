@extends('layouts.panel')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/tickets.css') }}">

    <section class="ticket-page">
        <div class="ticket-page-head">
            <h1>Get Tickets</h1>
            <p>Choose your concert and secure Regular or VIP access.</p>
        </div>

        @if(session('error'))
            <div class="alert-error" style="max-width:1200px;margin:0 auto 1rem;padding:0 1.25rem;">{{ session('error') }}</div>
        @endif

        @if($events->isEmpty())
            <div class="ticket-empty">
                <h2 style="color:#fff;margin-top:0;">No tickets available yet</h2>
                <p>New shows will appear here once published from the admin panel.</p>
            </div>
        @else
            @if($events->count() > 1)
                <div class="ticket-scroll-hint">← Scroll sideways to view more concerts →</div>
            @endif

            <div class="ticket-carousel" tabindex="0">
                @foreach($events as $event)
                    @php
                        $img = null;
                        if (!empty($event->cover_path)) {
                            try { $img = \Illuminate\Support\Facades\Storage::url($event->cover_path); } catch (\Throwable $ex) { $img = null; }
                        }
                        $regular = $event->ticketTypes->firstWhere('name', 'Regular');
                        $vip = $event->ticketTypes->firstWhere('name', 'VIP');
                    @endphp

                    <article class="ticket-slide">
                        <div
                            class="ticket-slide-bg {{ $img ? '' : 'ticket-slide-bg--fallback' }}"
                            @if($img) style="background-image: url('{{ $img }}');" @endif
                        ></div>
                        <div class="ticket-slide-overlay"></div>

                        <div class="ticket-slide-content">
                            <span class="ticket-slide-badge">Live Concert</span>

                            <h2 class="ticket-slide-title">{{ $event->title }}</h2>

                            <div class="ticket-slide-meta">
                                @if($event->location_name)
                                    <span>{{ $event->location_name }}</span>
                                @endif
                                <span>{{ optional($event->starts_at)->format('d M Y • H:i') ?? 'Date TBA' }}</span>
                            </div>

                            @if($event->description)
                                <p class="ticket-slide-desc">{{ Str::limit($event->description, 180) }}</p>
                            @endif

                            <div class="ticket-buy-row">
                                <div class="ticket-buy-card">
                                    <h3>Regular</h3>
                                    <p class="ticket-buy-price">
                                        @if($regular)
                                            Rp {{ number_format($regular->price, 0, ',', '.') }}
                                        @else
                                            —
                                        @endif
                                    </p>
                                    @if($regular)
                                        @auth
                                            @if(auth()->user()->isCustomer())
                                                <form method="POST" action="{{ route('customer.events.purchase', $event) }}">
                                                    @csrf
                                                    <input type="hidden" name="ticket_type_id" value="{{ $regular->id }}">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" class="ticket-buy-btn ticket-buy-btn--regular">Buy Regular</button>
                                                </form>
                                            @else
                                                <a href="{{ route('login') }}" class="ticket-buy-btn ticket-buy-btn--regular">Buy Regular</a>
                                            @endif
                                        @else
                                            <a href="{{ route('login') }}" class="ticket-buy-btn ticket-buy-btn--regular">Buy Regular</a>
                                        @endauth
                                    @else
                                        <button type="button" class="ticket-buy-btn ticket-buy-btn--regular ticket-buy-btn--disabled" disabled>Unavailable</button>
                                    @endif
                                </div>

                                <div class="ticket-buy-card">
                                    <h3>VIP</h3>
                                    <p class="ticket-buy-price">
                                        @if($vip)
                                            Rp {{ number_format($vip->price, 0, ',', '.') }}
                                        @else
                                            —
                                        @endif
                                    </p>
                                    @if($vip)
                                        @auth
                                            @if(auth()->user()->isCustomer())
                                                <form method="POST" action="{{ route('customer.events.purchase', $event) }}">
                                                    @csrf
                                                    <input type="hidden" name="ticket_type_id" value="{{ $vip->id }}">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" class="ticket-buy-btn ticket-buy-btn--vip">Buy VIP</button>
                                                </form>
                                            @else
                                                <a href="{{ route('login') }}" class="ticket-buy-btn ticket-buy-btn--vip">Buy VIP</a>
                                            @endif
                                        @else
                                            <a href="{{ route('login') }}" class="ticket-buy-btn ticket-buy-btn--vip">Buy VIP</a>
                                        @endauth
                                    @else
                                        <button type="button" class="ticket-buy-btn ticket-buy-btn--vip ticket-buy-btn--disabled" disabled>Unavailable</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
