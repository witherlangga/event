@extends('layouts.admin')

@section('admin-content')
    <div class="admin-page-head">
        <div>
            <h1>Event &amp; Tickets</h1>
            <p>Create concerts with Regular and VIP tickets for the Get Tickets page.</p>
        </div>
        <a href="{{ route('admin.events.create') }}" class="admin-btn admin-btn-primary">+ Create Event</a>
    </div>

    @if(session('success'))
        <div class="alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif

    <div class="admin-card">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Location</th>
                        <th>Date &amp; Time</th>
                        <th>Tickets</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                        @php
                            $regular = $event->ticketTypes->firstWhere('name', 'Regular');
                            $vip = $event->ticketTypes->firstWhere('name', 'VIP');
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $event->title }}</strong>
                                @if($event->description)
                                    <div style="color:#8892b0;font-size:0.85rem;margin-top:0.25rem;">{{ Str::limit($event->description, 70) }}</div>
                                @endif
                            </td>
                            <td>{{ $event->location_name ?? '—' }}</td>
                            <td>{{ optional($event->starts_at)->format('d M Y • H:i') ?? '—' }}</td>
                            <td>
                                @if($regular)
                                    <div>Regular: Rp {{ number_format($regular->price, 0, ',', '.') }}</div>
                                @endif
                                @if($vip)
                                    <div>VIP: Rp {{ number_format($vip->price, 0, ',', '.') }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="admin-badge {{ $event->is_active ? 'admin-badge-success' : 'admin-badge-muted' }}">
                                    {{ $event->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="admin-actions">
                                    <a href="{{ route('admin.events.edit', $event) }}" class="admin-btn admin-btn-secondary">Edit</a>
                                    <form method="POST" action="{{ route('admin.events.delete', $event) }}" onsubmit="return confirm('Delete this event and all ticket types?');">
                                        @csrf
                                        <button type="submit" class="admin-btn admin-btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:2rem;color:#8892b0;">No events yet. Create your first concert ticket package.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
