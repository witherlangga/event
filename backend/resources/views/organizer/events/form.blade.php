@extends('organizer.layout')

@section('content')
    <h2>{{ $event->exists ? 'Edit Event' : 'Buat Event' }}</h2>

    <form method="POST" action="{{ $event->exists ? route('organizer.events.update', ['id' => $event->id]) : route('organizer.events.store') }}" enctype="multipart/form-data">
        @csrf
        @if($event->exists)
            @method('PUT')
        @endif

        <label>Title</label>
        <input name="title" value="{{ old('title', $event->title) }}" required>

        <label>Description</label>
        <textarea name="description">{{ old('description', $event->description) }}</textarea>

        <label>Starts At</label>
        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($event->starts_at)->format('Y-m-d\TH:i') ) }}">

        <label>Ends At</label>
        <input type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($event->ends_at)->format('Y-m-d\TH:i') ) }}">

        <label>Capacity</label>
        <input type="number" name="capacity" value="{{ old('capacity', $event->capacity) }}">

        <label>Cover Image</label>
        @if($event->cover_path)
            <div><img src="{{ asset('storage/' . $event->cover_path) }}" alt="cover" style="max-width:200px"></div>
        @endif
        <input type="file" name="cover">

        <button type="submit">{{ $event->exists ? 'Update' : 'Create' }}</button>
    </form>

    @if($event->exists)
        <form method="POST" action="{{ route('organizer.events.delete', ['id' => $event->id]) }}" onsubmit="return confirm('Hapus event?')">
            @csrf
            @method('DELETE')
            <button type="submit">Delete Event</button>
        </form>
    @endif

@endsection
