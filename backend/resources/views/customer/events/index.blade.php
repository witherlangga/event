@extends('layouts.panel')

@section('content')
    <div class="section">
        <div class="section-title">Upcoming Events</div>
        
        @if($events->isEmpty())
            <div class="card text-center p-4">
                <p class="text-muted">No active events at the moment. Check back soon!</p>
            </div>
        @else
            <div class="grid grid-3">
                @foreach($events as $e)
                    <a href="{{ route('customer.events.show', ['event' => $e->id]) }}" style="text-decoration: none;">
                        <div class="card" style="height: 100%; display: flex; flex-direction: column;">
                            @php
                                $img = null;
                                if (!empty($e->cover_image)) {
                                    try { $img = \Illuminate\Support\Facades\Storage::url($e->cover_image); } catch (\Throwable $ex) { $img = null; }
                                }
                            @endphp
                            
                            @if($img)
                                <div style="width: 100%; height: 180px; background: url('{{ $img }}') center/cover no-repeat; border-radius: 12px; margin-bottom: 16px;"></div>
                            @else
                                <div style="width: 100%; height: 180px; background: linear-gradient(135deg, #FF6B9D, #64C8FF); border-radius: 12px; margin-bottom: 16px;"></div>
                            @endif
                            
                            <h3 style="color: white; margin: 0 0 8px 0;">{{ $e->title }}</h3>
                            
                            <p style="flex: 1;">{{ Str::limit($e->description, 100) }}</p>
                            
                            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 12px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid rgba(100, 200, 255, 0.2);">
                                    <span class="badge badge-secondary">{{ optional($e->starts_at)->format('M d, Y') ?? 'TBA' }}</span>
                                    <span style="color: #00FF88; font-weight: 600;">View →</span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection
