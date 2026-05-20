@extends('organizer.layout')

@section('content')
    <h2>Dashboard</h2>
    @php $me = \Illuminate\Support\Facades\Auth::user(); @endphp

    @if($me && $me->role === 'organizer')
        <h3>Events (organizer)</h3>
        <p><a href="{{ route('organizer.events.create') }}">Buat Event Baru</a></p>
        @if($events->isEmpty())
            <p>Tidak ada event. Buat event lewat form di atas atau via API.</p>
        @else
            <ul>
                @foreach($events as $e)
                    <li>
                        <strong>{{ $e->title ?? $e->name ?? 'Event' }}</strong>
                        <div>{{ $e->description ?? '' }}</div>
                        <div>
                            <a href="{{ route('organizer.events.edit', ['id' => $e->id]) }}">Edit</a>
                            |
                            <a href="{{ route('organizer.tickets', ['eventId' => $e->id]) }}">Manage Tickets</a>
                            |
                            <a href="{{ route('organizer.refunds') }}">Lihat refund untuk event ini</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <p><a href="{{ route('organizer.refunds') }}">Lihat semua refund request</a></p>

    @elseif($me && $me->role === 'customer')
        <h3>Customer</h3>
        <p><a href="{{ route('customer.events') }}">Lihat Event (Customer)</a></p>
        <p><a href="{{ route('customer.orders') }}">Riwayat Pesanan</a></p>

    @else
        <h3>Events</h3>
        <p>Silakan pilih user untuk impersonate atau hubungi admin untuk akses.</p>
    @endif
@endsection
