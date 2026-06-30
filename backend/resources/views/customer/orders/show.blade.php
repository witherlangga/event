@extends('layouts.panel')

@section('content')
    <h2>Order #{{ $order->id }}</h2>
    <p>Total: {{ $order->total_price }} - Status: {{ $order->status }}</p>

    <h3>Tickets</h3>
    <form method="POST" action="{{ route('customer.orders.request_refund', ['order' => $order->id]) }}">
        @csrf
        @if($tickets->isEmpty())
            <p>No tickets found.</p>
        @else
            <ul>
                @foreach($tickets as $t)
                    <li style="cursor:pointer;" onclick="(function(e){ e = e || window.event; var tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : ''; if(tag==='input' || tag==='a' || tag==='button') return; var cb = this.querySelector('input[type=checkbox]'); if(cb && !cb.disabled){ cb.checked = !cb.checked; cb.dispatchEvent(new Event('change')); } }).call(this,event)">
                        <span style="cursor: default;">
                            <input type="checkbox" name="ticket_ids[]" value="{{ $t->id }}" {{ $t->used ? 'disabled' : '' }} onclick="event.stopPropagation();" style="width:18px;height:18px;position:relative;z-index:2;accent-color:#f59e0b;vertical-align:middle;">
                            <span style="margin-left:8px;">Ticket code: {{ $t->code }} - Used: {{ $t->used ? 'yes' : 'no' }}</span>
                        </span>
                        - <a href="{{ $t->signed_download }}" onclick="event.stopPropagation();">Download QR</a>
                    </li>
                @endforeach
            </ul>
        @endif

        <h3>Request Refund</h3>
        <p>Pilih tiket yang akan direfund (yang sudah digunakan tidak bisa direfund).</p>
        <label>Reason</label>
        <textarea name="reason"></textarea>
        <div><button type="submit">Request Refund</button></div>
        <div id="refund-debug" style="margin-top:8px;color:#9ae6b4;font-weight:600"></div>
    </form>

        <h3>Refund History</h3>
        @if(isset($order->refunds) && $order->refunds->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tickets</th>
                        <th>Amount</th>
                        <th>Requested By</th>
                        <th>Status</th>
                        <th>Processed At</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->refunds as $rf)
                        <tr>
                            <td>{{ $rf->id }}</td>
                            <td>{{ is_array($rf->ticket_ids) ? implode(',', $rf->ticket_ids) : $rf->ticket_ids }}</td>
                            <td>{{ $rf->amount }}</td>
                            <td>{{ $rf->requester->name ?? $rf->requested_by }}</td>
                            <td>{{ $rf->status }}</td>
                            <td>{{ $rf->processed_at ? $rf->processed_at->toDateTimeString() : '-' }}</td>
                            <td>{{ $rf->reason }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No refund requests for this order.</p>
        @endif

    <script>
        function updateRefundDebug(){
            const inputs = Array.from(document.querySelectorAll('input[name="ticket_ids[]"]'));
            const selected = inputs.filter(i=>i.checked).map(i=>i.value);
            document.getElementById('refund-debug').textContent = 'Selected tickets: ' + selected.length + (selected.length ? ' (IDs: ' + selected.join(', ') + ')' : '');
        }
        document.querySelectorAll('input[name="ticket_ids[]"]').forEach(i=>{
            i.addEventListener('change', updateRefundDebug);
        });
        // init
        updateRefundDebug();
    </script>

@endsection
