@extends('layouts.panel')

@section('content')
    <div class="section">
        <!-- Order Header -->
        <div class="card mb-4">
            <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                <div>
                    <h2 style="margin: 0;">E-Ticket Details</h2>
                    <p style="margin: 8px 0 0 0; color: var(--text-muted);">Review your order, download each QR code, and lihat ulang ticket kapan saja.</p>
                </div>
                <span class="badge badge-{{ $order->status == 'completed' ? 'success' : ($order->status == 'pending' ? 'warning' : 'danger') }}" style="white-space: nowrap;">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="grid grid-3" style="gap: 20px;">
                    <div>
                        <p style="margin: 0; color: var(--text-muted); font-size: 0.9rem;">Order Date</p>
                        <p style="margin: 8px 0 0 0; color: white; font-weight: 600;">{{ optional($order->created_at)->format('M d, Y H:i') ?? '-' }}</p>
                    </div>
                    <div>
                        <p style="margin: 0; color: var(--text-muted); font-size: 0.9rem;">Total Amount</p>
                        <p style="margin: 8px 0 0 0; color: #00FF88; font-weight: 700; font-size: 1.25rem;">Rp {{ number_format($order->total_price) }}</p>
                    </div>
                    <div>
                        <p style="margin: 0; color: var(--text-muted); font-size: 0.9rem;">Tickets Count</p>
                        <p style="margin: 8px 0 0 0; color: white; font-weight: 600;">{{ $order->items->sum('quantity') ?? 0 }} Ticket(s)</p>
                    </div>
                </div>
                <a href="{{ route('customer.orders') }}" class="btn btn-secondary" style="margin-top: 20px; display: inline-block;">Review Ticket History</a>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h2 style="margin: 0;">Customer Information</h2>
            </div>
            <div class="card-body" style="display: grid; gap: 12px;">
                <div style="display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                    <div>
                        <p style="margin: 0; color: var(--text-muted); font-size: 0.85rem;">Nama Pemesan</p>
                        <p style="margin: 8px 0 0 0; color: white; font-weight: 600;">{{ $order->user->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p style="margin: 0; color: var(--text-muted); font-size: 0.85rem;">Email Pemesan</p>
                        <p style="margin: 8px 0 0 0; color: white; font-weight: 600;">{{ $order->user->email ?? '-' }}</p>
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                    <div>
                        <p style="margin: 0; color: var(--text-muted); font-size: 0.85rem;">Event</p>
                        <p style="margin: 8px 0 0 0; color: white; font-weight: 600;">{{ $order->event->title ?? '-' }}</p>
                    </div>
                    <div>
                        <p style="margin: 0; color: var(--text-muted); font-size: 0.85rem;">Tanggal & Waktu</p>
                        <p style="margin: 8px 0 0 0; color: white; font-weight: 600;">{{ optional($order->event->starts_at)->format('d M Y • H:i') ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tickets Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h2 style="margin: 0;">Ticket List</h2>
            </div>
            <div class="card-body">
                @if($tickets->isEmpty())
                    <div class="alert alert-info">No tickets found for this order.</div>
                @else
                    <div class="grid grid-2" style="gap: 16px;">
                        @foreach($tickets as $t)
                            <div class="card p-3" style="border: 2px solid rgba(100, 200, 255, 0.2); position: relative;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                                    <div>
                                        <h4 style="margin: 0 0 8px 0; color: white;">E-Ticket {{ $loop->iteration }}</h4>
                                        <p style="margin: 0; font-size: 0.9rem;">Kode Tiket: <span style="font-weight: 600; color: #64C8FF;">{{ $t->code }}</span></p>
                                    </div>
                                    @if($t->used)
                                        <span class="badge badge-success">Used</span>
                                    @else
                                        <span class="badge badge-secondary">Unused</span>
                                    @endif
                                </div>

                                <div style="display: grid; gap: 8px; margin-bottom: 16px;">
                                    <div style="display: flex; justify-content: space-between; gap: 8px;">
                                        <span style="color: var(--text-muted);">Ticket Type</span>
                                        <span style="color: white; font-weight: 600;">{{ $t->ticketType->name ?? '-' }}</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; gap: 8px;">
                                        <span style="color: var(--text-muted);">Acara</span>
                                        <span style="color: white; font-weight: 600;">{{ $order->event->title ?? '-' }}</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; gap: 8px;">
                                        <span style="color: var(--text-muted);">Nama Pemesan</span>
                                        <span style="color: white; font-weight: 600;">{{ $order->user->name ?? '-' }}</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; gap: 8px;">
                                        <span style="color: var(--text-muted);">Email</span>
                                        <span style="color: white; font-weight: 600;">{{ $order->user->email ?? '-' }}</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; gap: 8px;">
                                        <span style="color: var(--text-muted);">Status</span>
                                        <span style="color: white; font-weight: 600;">{{ $t->used ? 'Digunakan' : 'Belum digunakan' }}</span>
                                    </div>
                                </div>

                                <div style="background: rgba(100, 200, 255, 0.1); padding: 16px; border-radius: 8px; text-align: center; margin: 12px 0;">
                                    <a href="{{ $t->signed_download }}" class="btn btn-secondary" style="display: inline-block;">
                                        📥 Download QR Code
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Refund Request Section -->
        @if(!$tickets->isEmpty())
            <div class="card mb-4">
                <div class="card-header">
                    <h2 style="margin: 0;">Request Refund</h2>
                </div>
                <form method="POST" action="{{ route('customer.orders.request_refund', ['order' => $order->id]) }}" class="card-body">
                    @csrf
                    
                    <p style="color: var(--text-secondary); margin-bottom: 20px;">
                        Select the tickets you'd like to refund. Tickets marked as "Used" cannot be refunded.
                    </p>
                    
                    <div class="mb-3" style="margin-bottom: 20px;">
                        <label style="display: block; color: var(--text-secondary); font-weight: 600; margin-bottom: 12px;">Select Tickets</label>
                        <div class="grid grid-2" style="gap: 12px;">
                            @foreach($tickets as $t)
                                <label style="display: flex; align-items: center; padding: 12px; background: rgba(100, 200, 255, 0.05); border-radius: 8px; border: 1px solid var(--border-color); @if($t->used) cursor: not-allowed; opacity: 0.5; @else cursor: pointer; @endif transition: all 0.2s;">
                                    <input 
                                        type="checkbox" 
                                        name="ticket_ids[]" 
                                        value="{{ $t->id }}" 
                                        {{ $t->used ? 'disabled' : '' }} 
                                        style="width: 18px; height: 18px; cursor: pointer; margin-right: 12px; accent-color: #FF6B9D;">
                                    <span style="flex: 1;">
                                        <span style="color: white; font-weight: 600;">Ticket {{ $loop->iteration }}</span>
                                        <span style="display: block; font-size: 0.85rem; color: var(--text-muted);">{{ $t->code }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="mb-3" style="margin-bottom: 20px;">
                        <label style="display: block; color: var(--text-secondary); font-weight: 600; margin-bottom: 8px;">Refund Reason</label>
                        <textarea name="reason" placeholder="Please explain why you're requesting a refund..." style="width: 100%; min-height: 100px;"></textarea>
                    </div>
                    
                    <div id="refund-debug" style="padding: 12px; background: rgba(0, 255, 136, 0.1); border-radius: 8px; margin-bottom: 20px; color: #00FF88; font-weight: 600; display: none;"></div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Refund Request</button>
                </form>
            </div>
        @endif

        <!-- Refund History -->
        @if(isset($order->refunds) && $order->refunds->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h2 style="margin: 0;">Refund History</h2>
                </div>
                <div class="card-body">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tickets</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Requested At</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->refunds as $rf)
                                <tr>
                                    <td><strong>#{{ $rf->id }}</strong></td>
                                    <td>{{ is_array($rf->ticket_ids) ? implode(', ', $rf->ticket_ids) : $rf->ticket_ids }}</td>
                                    <td style="color: #00FF88; font-weight: 600;">Rp {{ number_format($rf->amount) }}</td>
                                    <td>
                                        <span class="badge badge-{{ 
                                            $rf->status == 'approved' ? 'success' : 
                                            ($rf->status == 'pending' ? 'warning' : 'danger') 
                                        }}">
                                            {{ ucfirst($rf->status) }}
                                        </span>
                                    </td>
                                    <td>{{ optional($rf->created_at)->format('M d, Y H:i') ?? '-' }}</td>
                                    <td>{{ $rf->reason }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            @if(!$tickets->isEmpty())
                <p style="text-align: center; color: var(--text-muted);">No refund requests for this order yet.</p>
            @endif
        @endif
    </div>

    <script>
        function updateRefundDebug(){
            const inputs = Array.from(document.querySelectorAll('input[name="ticket_ids[]"]:not(:disabled)'));
            const selected = inputs.filter(i=>i.checked).map(i=>i.value);
            const debug = document.getElementById('refund-debug');
            if(selected.length > 0) {
                debug.textContent = '✓ Selected ' + selected.length + ' ticket(s) for refund';
                debug.style.display = 'block';
            } else {
                debug.style.display = 'none';
            }
        }
        document.querySelectorAll('input[name="ticket_ids[]"]').forEach(i=>{
            i.addEventListener('change', updateRefundDebug);
        });
        updateRefundDebug();
    </script>
@endsection
