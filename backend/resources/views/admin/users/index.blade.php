@extends('layouts.admin')

@section('admin-content')
    <div class="admin-page-head">
        <div>
            <h1>User Management</h1>
            <p>Manage fan accounts, roles, and access status.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif

    <div class="admin-card">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td><strong>#{{ $u->id }}</strong></td>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.users.update', ['user' => $u->id]) }}">
                                    @csrf
                                    <select name="role" onchange="this.form.submit()" class="admin-field" style="padding:0.55rem 0.75rem;min-width:140px;">
                                        <option value="customer" {{ $u->role === 'customer' ? 'selected' : '' }}>Customer</option>
                                        <option value="system_admin" {{ $u->role === 'system_admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <span class="admin-badge {{ $u->is_active ? 'admin-badge-success' : 'admin-badge-muted' }}">
                                    {{ $u->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ optional($u->created_at)->format('M d, Y') ?? '—' }}</td>
                            <td>
                                <div class="admin-actions">
                                    <form method="POST" action="{{ route('admin.users.update', ['user' => $u->id]) }}">
                                        @csrf
                                        <input type="hidden" name="is_active" value="{{ $u->is_active ? '0' : '1' }}">
                                        <button type="submit" class="admin-btn admin-btn-secondary">
                                            {{ $u->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.users.delete', ['user' => $u->id]) }}" onsubmit="return confirm('Delete this user?');">
                                        @csrf
                                        <button type="submit" class="admin-btn admin-btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:2rem;color:#8892b0;">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
