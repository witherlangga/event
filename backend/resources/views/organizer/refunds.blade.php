@extends('organizer.layout')

@section('content')
    <h2>Refund Requests</h2>

    @if($refunds->isEmpty())
        <p>Tidak ada refund request untuk event Anda.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Order</th>
                    <th>Amount</th>
                    <th>Tickets</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($refunds as $r)
                    <tr>
                        <td>{{ $r->id }}</td>
                        <td>{{ $r->order_id }}</td>
                        <td>{{ $r->amount }}</td>
                        <td>{{ is_array($r->ticket_ids) ? implode(',', $r->ticket_ids) : $r->ticket_ids }}</td>
                        <td>{{ $r->reason }}</td>
                        <td>{{ $r->status }}</td>
                        <td>
                            @if($r->status === 'requested')
                                <form method="POST" action="{{ route('organizer.refunds.approve', ['id' => $r->id]) }}" style="display:inline">@csrf <button type="submit">Approve</button></form>
                                <form method="POST" action="{{ route('organizer.refunds.reject', ['id' => $r->id]) }}" style="display:inline">@csrf <button type="submit">Reject</button></form>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

@endsection
