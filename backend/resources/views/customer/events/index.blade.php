@extends('layouts.panel')

@section('content')
    <h2>Events</h2>
    @if($events->isEmpty())
        <p>No active events.</p>
    @else
        <ul>
            @foreach($events as $e)
                <li>
                    <a href="{{ route('customer.events.show', ['event' => $e->id]) }}"><strong>{{ $e->title }}</strong></a>
                    <div>{{ $e->description }}</div>
                    <div>{{ optional($e->starts_at)->format('Y-m-d H:i') ?? '' }}</div>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
