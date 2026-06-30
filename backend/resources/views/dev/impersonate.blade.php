@extends('layouts.panel')

@section('content')
    <h2>Dev Impersonate</h2>
    <form method="POST" action="{{ route('dev.impersonate.post') }}">
        @csrf
        <label>Pilih user untuk testing:
            <select name="user_id" required>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }}) — {{ $u->role }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit">Masuk</button>
    </form>
    <p>Setelah memilih user, Anda dapat mengakses halaman customer atau admin.</p>
    <p><a href="{{ route('customer.events') }}">Customer Events</a> | <a href="{{ route('admin.users') }}">Admin Users</a></p>
@endsection
