@extends('layouts.panel')

@section('content')
    <div class="section">
        @php
            $img = null;
            if (!empty($event->cover_image)) {
                try { $img = \Illuminate\Support\Facades\Storage::url($event->cover_image); } catch (\Throwable $ex) { $img = null; }
            }
        @endphp

        <!-- Header Section -->
        <div class="card mb-4" style="overflow: hidden;">
            @if($img)
                <div style="width: 100%; height: 300px; background: url('{{ $img }}') center/cover no-repeat; margin: -24px -24px 24px -24px;"></div>
            @else
                <div style="width: 100%; height: 300px; background: linear-gradient(135deg, #FF6B9D, #64C8FF); margin: -24px -24px 24px -24px;"></div>
            @endif
            
            <div>
                <div style="display: flex; gap: 12px; margin-bottom: 16px;">
                    @if($event->venue)
                        <span class="badge badge-primary">{{ $event->venue }}</span>
                    @endif
                    <span class="badge badge-secondary">{{ optional($event->starts_at)->format('M d, Y H:i') ?? 'TBA' }}</span>
                </div>
                
                <h1 style="margin: 0 0 16px 0;">{{ $event->title }}</h1>
                <p>{{ $event->description }}</p>
            </div>
        </div>

        <!-- Ticket Selection -->
        <div class="card">
            <div class="card-header">
                <h2 style="margin: 0;">Select Your Tickets</h2>
            </div>
            <div class="card-body">
                @if($ticketTypes->isEmpty())
                    <div class="alert alert-info">No tickets available at the moment.</div>
                @else
                    <div class="grid grid-2" style="margin: 20px 0;">
                        @foreach($ticketTypes as $t)
                            <div class="card p-4" style="border: 2px solid transparent; transition: all 0.3s;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px;">
                                    <div>
                                        <h3 style="margin: 0 0 8px 0; color: white;">{{ $t->name }}</h3>
                                        <p style="margin: 0;">{{ Str::limit($t->description ?? '', 100) }}</p>
                                    </div>
                                </div>
                                
                                <div style="background: rgba(100, 200, 255, 0.1); padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; text-align: center;">
                                    <p style="margin: 0; color: #64C8FF; font-size: 1.5rem; font-weight: 700;">
                                        Rp {{ number_format($t->price) }}
                                    </p>
                                </div>
                                
                                <form method="POST" action="{{ route('customer.events.purchase', ['event' => $event->id]) }}" class="flex flex-col gap-2">
                                    @csrf
                                    <input type="hidden" name="ticket_type_id" value="{{ $t->id }}">
                                    <div>
                                        <label style="display: block; margin-bottom: 8px; color: var(--text-secondary); font-size: 0.9rem;">Quantity</label>
                                        <input type="number" name="quantity" value="1" min="1" max="10" style="width: 100%;">
                                    </div>
                                    <button type="submit" class="btn btn-primary" style="margin-top: 12px; width: 100%;">
                                        Get Tickets
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
