@extends('layouts.panel')

@section('content')
    <h2>Your Orders</h2>
    @if($orders->isEmpty())
        <p>No orders yet.</p>
    @else
        <ul>
            @foreach($orders as $o)
                <li>
                    <a href="{{ route('customer.orders.show', ['order' => $o->id]) }}">Order #{{ $o->id }}</a>
                    - {{ $o->total_price }} - {{ $o->status }}
                </li>
            @endforeach
        </ul>
    @endif
@endsection
