@extends('layouts.admin')

@section('admin-content')
    <div class="admin-page-head">
        <div>
            <h1>Order Review & Statistik</h1>
            <p>Semua tiket pembelian muncul di sini, lengkap dengan email pemesan, status, dan opsi review atau hapus oleh admin.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif

    <div class="admin-card" style="margin-bottom: 1.5rem; padding: 1.5rem;">
        <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
            <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 24px; padding: 1.15rem;">
                <p style="margin: 0 0 0.6rem; color: #8892b0; font-size: 0.95rem;">Total Orders</p>
                <h2 style="margin: 0; color: #fff;">{{ number_format($totalOrders) }}</h2>
            </div>
            <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 24px; padding: 1.15rem;">
                <p style="margin: 0 0 0.6rem; color: #8892b0; font-size: 0.95rem;">Total Income</p>
                <h2 style="margin: 0; color: #fff;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h2>
            </div>
            <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 24px; padding: 1.15rem;">
                <p style="margin: 0 0 0.6rem; color: #8892b0; font-size: 0.95rem;">Sold Tickets</p>
                <h2 style="margin: 0; color: #fff;">{{ number_format($totalTickets) }}</h2>
            </div>
            <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 24px; padding: 1.15rem;">
                <p style="margin: 0 0 0.6rem; color: #8892b0; font-size: 0.95rem;">Paid Orders</p>
                <h2 style="margin: 0; color: #fff;">{{ number_format($totalPaidOrders) }}</h2>
            </div>
            <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 24px; padding: 1.15rem;">
                <p style="margin: 0 0 0.6rem; color: #8892b0; font-size: 0.95rem;">Pending Orders</p>
                <h2 style="margin: 0; color: #fff;">{{ number_format($totalPendingOrders) }}</h2>
            </div>
            <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 24px; padding: 1.15rem;">
                <p style="margin: 0 0 0.6rem; color: #8892b0; font-size: 0.95rem;">Unique Buyers</p>
                <h2 style="margin: 0; color: #fff;">{{ number_format($totalBuyers) }}</h2>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Ticket ID</th>
                        <th>Order</th>
                        <th>Email</th>
                        <th>Event</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td><strong>#{{ $ticket->id }}</strong></td>
                            <td>#{{ optional($ticket->order)->id ?? '—' }}</td>
                            <td>{{ optional(optional($ticket->order)->user)->email ?? '—' }}</td>
                            <td>{{ optional(optional($ticket->order)->event)->title ?? '—' }}</td>
                            <td>{{ optional($ticket->ticketType)->name ?? '—' }}</td>
                            <td>
                                <span class="admin-badge {{ optional($ticket->order)->status === 'paid' ? 'admin-badge-success' : (optional($ticket->order)->status === 'pending' ? 'admin-badge-warning' : 'admin-badge-muted') }}">
                                    {{ ucfirst(optional($ticket->order)->status ?? 'unknown') }}
                                </span>
                            </td>
                            <td>{{ optional($ticket->created_at)->format('d M Y') ?? '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.reviews.tickets.delete', ['ticket' => $ticket->id]) }}" onsubmit="return confirm('Delete this ticket permanently?');">
                                    @csrf
                                    <button type="submit" class="admin-btn admin-btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;padding:2rem;color:#8892b0;">No tickets available for review yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1rem; display: flex; justify-content: flex-end;">
            {{ $tickets->links() }}
        </div>
    </div>
@endsection
