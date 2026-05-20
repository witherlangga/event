@extends('organizer.layout')

@section('content')
    <h2>Pilih User untuk Testing</h2>
    <form method="POST" action="{{ route('organizer.impersonate') }}">
        @csrf
        <label for="user_id">User</label>
        <select name="user_id" id="user_id">
            @foreach($users as $u)
                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }}) - {{ $u->role }}</option>
            @endforeach
        </select>
        <button type="submit">Impersonate</button>
    </form>

    <p>Setelah memilih user, Anda akan diarahkan ke dashboard organizer.</p>
@endsection
