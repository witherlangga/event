@extends('layouts.panel')

@section('content')
    <div class="section">
        <div class="card" style="max-width:900px; margin:auto;">
            <div class="card-header">
                <h2 style="margin:0;">Daftar Music</h2>
                <p style="margin:8px 0 0 0; color:var(--text-muted);">Dengarkan track terbaru yang bisa diputar langsung di browser.</p>
            </div>
            <div class="card-body" style="display:grid; gap:24px;">
                @if($songs->isEmpty())
                    <div class="alert alert-info">Saat ini belum ada lagu yang tersedia.</div>
                @else
                    @foreach($songs as $song)
                        <div style="background: rgba(255,255,255,0.04); padding:18px; border-radius:16px; border:1px solid rgba(255,255,255,0.08);">
                            <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:12px; align-items:center;">
                                <div>
                                    <h3 style="margin:0 0 6px 0;">{{ $song->track_number }}. {{ $song->title }}</h3>
                                    @if($song->duration_seconds)
                                        <p style="margin:0; color:var(--text-muted); font-size:0.95rem;">Durasi {{ gmdate('i:s', $song->duration_seconds) }}</p>
                                    @endif
                                </div>
                            </div>
                            @if($song->streaming_url)
                                <audio controls style="width:100%; margin-top:16px;">
                                    <source src="{{ asset('storage/' . $song->streaming_url) }}" type="audio/mpeg">
                                    Browser Anda tidak mendukung audio player.
                                </audio>
                            @else
                                <div class="alert alert-warning">Link audio tidak tersedia untuk lagu ini.</div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
@endsection
