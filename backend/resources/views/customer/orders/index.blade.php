@extends('layouts.panel')

@section('content')
    <div class="section">
        <div class="section-title">Your Order History</div>
        
        @if($orders->isEmpty())
            <div class="card text-center p-4">
                <p class="text-muted">No orders yet. Start booking your favorite events!</p>
                <a href="{{ route('customer.events.index') }}" class="btn btn-primary mt-3" style="display: inline-block;">Browse Events</a>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $o)
                        <tr>
                            <td><strong>#{{ $o->id }}</strong></td>
                            <td>{{ optional($o->created_at)->format('M d, Y H:i') ?? '-' }}</td>
                            <td style="font-weight: 600; color: #00FF88;">Rp {{ number_format($o->total_price) }}</td>
                            <td>
                                <span class="badge badge-{{ 
                                    $o->status == 'completed' ? 'success' : 
                                    ($o->status == 'pending' ? 'warning' : 'danger') 
                                }}">
                                    {{ ucfirst($o->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('customer.orders.show', ['order' => $o->id]) }}" class="btn btn-secondary" style="padding: 8px 16px; font-size: 0.9rem;">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
