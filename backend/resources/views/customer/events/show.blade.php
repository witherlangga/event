@extends('organizer.layout')

@section('content')
    <h2>{{ $event->title }}</h2>
    <p>{{ $event->description }}</p>

    <h3>Ticket Types</h3>
    @if($ticketTypes->isEmpty())
        <p>No tickets available.</p>
    @else
        <ul>
            @foreach($ticketTypes as $t)
                <li>
                    <strong>{{ $t->name }}</strong> - {{ $t->price }}
                    <form method="POST" action="{{ route('customer.events.purchase', ['event' => $event->id]) }}">
                        @csrf
                        <input type="hidden" name="ticket_type_id" value="{{ $t->id }}">
                        <label>Quantity <input type="number" name="quantity" value="1" min="1"></label>
                        <button type="submit">Buy</button>
                    </form>
                </li>
            @endforeach
        </ul>
    @endif

@endsection
