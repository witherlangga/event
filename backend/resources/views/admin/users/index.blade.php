@extends('organizer.layout')

@section('content')
    <h2>Admin: User Management</h2>

    @if(session('success')) <div style="color:green">{{ session('success') }}</div> @endif

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Active</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $u)
                <tr>
                    <td>{{ $u->id }}</td>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td>{{ $u->role }}</td>
                    <td>{{ $u->is_active ? 'yes' : 'no' }}</td>
                    <td>{{ $u->created_at }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.users.update', ['user' => $u->id]) }}" style="display:inline">
                            @csrf
                            <select name="role">
                                <option value="customer" {{ $u->role === 'customer' ? 'selected' : '' }}>customer</option>
                                <option value="organizer" {{ $u->role === 'organizer' ? 'selected' : '' }}>organizer</option>
                                <option value="system_admin" {{ $u->role === 'system_admin' ? 'selected' : '' }}>system_admin</option>
                            </select>
                            <label>
                                <input type="checkbox" name="is_active" value="1" {{ $u->is_active ? 'checked' : '' }}> active
                            </label>
                            <button type="submit">Save</button>
                        </form>
                        <form method="POST" action="{{ route('admin.users.delete', ['user' => $u->id]) }}" style="display:inline" onsubmit="return confirm('Soft-delete user?');">
                            @csrf
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection
