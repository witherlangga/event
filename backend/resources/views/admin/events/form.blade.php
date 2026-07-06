@extends('layouts.admin')

@section('admin-content')
    @php
        $regular = $event?->ticketTypes->firstWhere('name', 'Regular');
        $vip = $event?->ticketTypes->firstWhere('name', 'VIP');
        $isEdit = (bool) $event;
    @endphp

    <div class="admin-page-head">
        <div>
            <h1>{{ $isEdit ? 'Edit Event & Tickets' : 'Create Event & Tickets' }}</h1>
            <p>Set concert details, schedule, location, and Regular/VIP pricing.</p>
        </div>
        <a href="{{ route('admin.events') }}" class="admin-btn admin-btn-secondary">← Back to List</a>
    </div>

    @if ($errors->any())
        <div class="alert-error" style="margin-bottom: 1rem;">
            <ul style="margin:0;padding-left:1.1rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="admin-card">
        <form
            class="admin-form"
            method="POST"
            action="{{ $isEdit ? route('admin.events.update', $event) : route('admin.events.store') }}"
            enctype="multipart/form-data"
        >
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <h3 class="admin-section-label">Event Information</h3>
            <div class="admin-form-grid">
                <div class="admin-field">
                    <label for="title">Event Title</label>
                    <input id="title" type="text" name="title" value="{{ old('title', $event->title ?? '') }}" required>
                </div>
                <div class="admin-field">
                    <label for="location_name">Venue / Location</label>
                    <input id="location_name" type="text" name="location_name" value="{{ old('location_name', $event->location_name ?? '') }}" required>
                </div>
            </div>

            <div class="admin-field">
                <label for="location_address">Full Address</label>
                <input id="location_address" type="text" name="location_address" value="{{ old('location_address', $event->location_address ?? '') }}">
            </div>

            <div class="admin-field">
                <label for="description">Description</label>
                <textarea id="description" name="description">{{ old('description', $event->description ?? '') }}</textarea>
            </div>

            <h3 class="admin-section-label">Schedule</h3>
            <div class="admin-form-grid-3">
                <div class="admin-field">
                    <label for="event_date">Date</label>
                    <input
                        id="event_date"
                        type="date"
                        name="event_date"
                        value="{{ old('event_date', optional($event?->starts_at)->format('Y-m-d')) }}"
                        required
                    >
                </div>
                <div class="admin-field">
                    <label for="event_time">Start Time</label>
                    <input
                        id="event_time"
                        type="time"
                        name="event_time"
                        value="{{ old('event_time', optional($event?->starts_at)->format('H:i')) }}"
                        required
                    >
                </div>
                <div class="admin-field">
                    <label for="ends_time">End Time</label>
                    <input
                        id="ends_time"
                        type="time"
                        name="ends_time"
                        value="{{ old('ends_time', optional($event?->ends_at)->format('H:i')) }}"
                    >
                </div>
            </div>

            <div class="admin-form-grid">
                <div class="admin-field">
                    <label for="capacity">Venue Capacity</label>
                    <input id="capacity" type="number" min="1" name="capacity" value="{{ old('capacity', $event->capacity ?? '') }}">
                </div>
                <div class="admin-field">
                    <label for="cover">Cover Image</label>
                    <input id="cover" type="file" name="cover" accept="image/*">
                </div>
            </div>

            <div class="admin-field">
                <label style="display:flex;align-items:center;gap:0.55rem;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $event->is_active ?? true) ? 'checked' : '' }}>
                    Publish on Get Tickets page
                </label>
            </div>

            <h3 class="admin-section-label">Ticket Types</h3>
            <div class="admin-form-grid">
                <div class="admin-ticket-box">
                    <h4 style="margin:0 0 0.75rem;color:#fff;">Regular Ticket</h4>
                    <div class="admin-form-grid">
                        <div class="admin-field">
                            <label for="regular_price">Price (Rp)</label>
                            <input id="regular_price" type="number" min="0" step="1000" name="regular_price" value="{{ old('regular_price', $regular->price ?? 150000) }}" required>
                        </div>
                        <div class="admin-field">
                            <label for="regular_quota">Quota</label>
                            <input id="regular_quota" type="number" min="1" name="regular_quota" value="{{ old('regular_quota', $regular->quota ?? 500) }}" required>
                        </div>
                    </div>
                </div>

                <div class="admin-ticket-box">
                    <h4 style="margin:0 0 0.75rem;color:#fff;">VIP Ticket</h4>
                    <div class="admin-form-grid">
                        <div class="admin-field">
                            <label for="vip_price">Price (Rp)</label>
                            <input id="vip_price" type="number" min="0" step="1000" name="vip_price" value="{{ old('vip_price', $vip->price ?? 350000) }}" required>
                        </div>
                        <div class="admin-field">
                            <label for="vip_quota">Quota</label>
                            <input id="vip_quota" type="number" min="1" name="vip_quota" value="{{ old('vip_quota', $vip->quota ?? 50) }}" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-actions">
                <button type="submit" class="admin-btn admin-btn-primary">
                    {{ $isEdit ? 'Save Changes' : 'Create Event & Tickets' }}
                </button>
                <a href="{{ route('admin.events') }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
