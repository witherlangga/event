@extends('organizer.layout')

@section('content')
    <h2>Ticket Types for {{ $event->title ?? $event->name }}</h2>
    <p><a href="{{ route('organizer.tickets.create', ['eventId' => $event->id]) }}">Create Ticket Type</a></p>

    @if($tickets->isEmpty())
        <p>No ticket types yet.</p>
    @else
        <table>
            <thead><tr><th>Name</th><th>Price</th><th>Quota</th><th>Sold</th><th>Actions</th></tr></thead>
            <tbody>
            @foreach($tickets as $t)
                <tr>
                    <td>{{ $t->name }}</td>
                    <td>{{ $t->price }}</td>
                    <td>{{ $t->quota }}</td>
                    <td>{{ $t->sold }}</td>
                    <td>
                        <a href="{{ route('organizer.tickets.edit', ['eventId' => $event->id, 'ticketId' => $t->id]) }}">Edit</a>
                        <form method="POST" action="{{ route('organizer.tickets.delete', ['eventId' => $event->id, 'ticketId' => $t->id]) }}" style="display:inline">@csrf @method('DELETE')<button type="submit">Delete</button></form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

@endsection
