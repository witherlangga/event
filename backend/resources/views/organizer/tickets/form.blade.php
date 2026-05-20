@extends('organizer.layout')

@section('content')
    <h2>{{ $ticket->exists ? 'Edit Ticket' : 'Create Ticket' }} for {{ $event->title ?? '' }}</h2>

    <form method="POST" action="{{ $ticket->exists ? route('organizer.tickets.update', ['eventId' => $event->id, 'ticketId' => $ticket->id]) : route('organizer.tickets.store', ['eventId' => $event->id]) }}">
        @csrf
        @if($ticket->exists) @method('PUT') @endif

        <label>Name</label>
        <input name="name" value="{{ old('name', $ticket->name) }}" required>

        <label>Description</label>
        <textarea name="description">{{ old('description', $ticket->description) }}</textarea>

        <label>Price</label>
        <input type="number" step="0.01" name="price" value="{{ old('price', $ticket->price) }}" required>

        <label>Quota</label>
        <input type="number" name="quota" value="{{ old('quota', $ticket->quota) ?? 0 }}" required>

        <button type="submit">{{ $ticket->exists ? 'Update' : 'Create' }}</button>
    </form>

@endsection
