@extends('layouts.panel')

@section('content')
    <h2>Mock Payment for Order #{{ $order->id }}</h2>
    <p>Total: {{ $order->total_price }}</p>
    <p>This is a simulated payment page for coursework. No real money will be used.</p>

    <form method="POST" action="{{ route('customer.mockpay.complete', ['order' => $order->id]) }}">
        @csrf
        <button type="submit">Simulate Payment (Pay)</button>
    </form>

    <p><a href="{{ route('customer.orders.show', ['order' => $order->id]) }}">Back to Order</a></p>
@endsection
