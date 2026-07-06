@extends('layouts.panel')

@section('content')
    @php
        $socialLinks = $socialLinks ?? [
            'instagram' => 'https://instagram.com/neonhorizon',
            'youtube' => 'https://youtube.com/@neonhorizon',
            'tiktok' => 'https://tiktok.com/@neonhorizon',
            'spotify' => 'https://open.spotify.com/artist/neonhorizon',
        ];
    @endphp
    <section class="hero-3d">
        <!-- 3D Canvas Background -->
        <canvas id="canvas3d"></canvas>
        
        <!-- Particle Effects Layer -->
        <div id="particles-container" class="particles-layer"></div>
        
        <!-- Hero Content Overlay -->
        <div class="hero-overlay">
            <div class="hero-content">
                <div class="hero-copy">
                    <span class="hero-tag">NEON HORIZON LIVE</span>
                    <h1 class="hero-title">Sound Beyond the Horizon</h1>
                    <p class="hero-description">Neon Horizon is an alternative rock band established in 2018, dedicated to delivering powerful live performances and authentic music experiences for our community of listeners and concert-goers.</p>
                    <div class="hero-actions">
                        <a class="button primary" href="{{ route('tickets') }}">Get Tickets</a>
                        <a class="button secondary" href="{{ route('music') }}">Listen Now</a>
                    </div>
                </div>
                
                <div class="hero-card-3d">
                    <div class="card-inner">
                        <span class="card-badge">NEXT SHOW</span>
                        <h2>{{ optional($profile)->next_show_title ?? 'Live in Jakarta' }}</h2>
                        <p class="card-date">{{ optional($profile)->next_show_date ?? '23 April 2025 • 20:00' }}</p>
                        <p class="card-price">{{ optional($profile)->next_show_price_text ?? 'From Rp 500K' }}</p>
                        @if(!empty(optional($profile)->next_show_map_link))
                            <a class="button small" href="{{ $profile->next_show_map_link }}" target="_blank" rel="noopener noreferrer">Open Map</a>
                        @else
                            <a class="button small" href="{{ route('tickets') }}">Book Now</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="bgm-content-area">
    <section class="section-grid section-social">
        <a href="{{ $socialLinks['instagram'] ?? 'https://instagram.com/neonhorizon' }}" class="feature-card feature-link social-logo-card" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
            <span class="social-logo social-logo-instagram" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
            </span>
        </a>
        <a href="{{ $socialLinks['youtube'] ?? 'https://youtube.com/@neonhorizon' }}" class="feature-card feature-link social-logo-card" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
            <span class="social-logo social-logo-youtube" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
            </span>
        </a>
        <a href="{{ $socialLinks['tiktok'] ?? 'https://tiktok.com/@neonhorizon' }}" class="feature-card feature-link social-logo-card" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
            <span class="social-logo social-logo-tiktok" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 2.16 2.49 3.66 1.35.56-.4.92-1.05 1.04-1.75.11-.63.09-1.27.1-1.91.01-3.78-.01-7.56.02-11.34z"/></svg>
            </span>
        </a>
    </section>

    <section class="section-moments">
        <div class="section-header">
            <div>
                <span class="badge">Moments</span>
                <h2>Behind The Stage</h2>
            </div>
        </div>

        <div class="moments-grid">
            @php
                $moments = $profile->moments ?? [];
                if (empty($moments)) {
                    $moments = [asset('assets/01.jpg'), asset('assets/02.jpeg'), asset('assets/03.jpeg'), asset('assets/04.jpg')];
                }
            @endphp

            @foreach($moments as $m)
                <figure class="moment-frame">
                    <img src="{{ $m }}" alt="Neon Horizon live moment" loading="lazy" style="width:100%; height:100%; object-fit:cover;">
                </figure>
            @endforeach
        </div>

        <blockquote class="band-message">
            <p>{{ $profile->band_message ?? 'Terima kasih sudah menjadi bagian dari perjalanan Neon Horizon. Setiap tiket yang kalian pesan, setiap sorak di konser, dan setiap momen yang kita bagi bersama — itulah energi yang membuat kami terus bermain di atas panggung. Sampai jumpa di show berikutnya. Stay loud, stay neon.' }}</p>
            <footer>— Neon Horizon</footer>
        </blockquote>
    </section>
    </div>
@endsection
