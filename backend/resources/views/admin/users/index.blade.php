@extends('layouts.panel')

@section('content')
    <div class="section">
        <div class="section-title">User Management</div>

        @if(session('success'))
            <div class="alert alert-success">
                <span>✓</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div style="overflow-x: auto;">
                    <table style="min-width: 100%;">
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
                                    <td>
                                        <div style="font-weight: 600; color: white;">{{ $u->name }}</div>
                                    </td>
                                    <td style="color: var(--text-secondary);">{{ $u->email }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.users.update', ['user' => $u->id]) }}" style="display: inline; min-width: 150px;">
                                            @csrf
                                            <select name="role" style="padding: 8px 12px; background: var(--bg-input); border: 1px solid var(--border-color); color: white; border-radius: 6px; font-size: 0.9rem; onchange="this.form.submit();">
                                                <option value="customer" {{ $u->role === 'customer' ? 'selected' : '' }}>👤 Customer</option>
                                                <option value="system_admin" {{ $u->role === 'system_admin' ? 'selected' : '' }}>👨‍💼 Admin</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $u->is_active ? 'success' : 'danger' }}">
                                            {{ $u->is_active ? '🟢 Active' : '🔴 Inactive' }}
                                        </span>
                                    </td>
                                    <td style="color: var(--text-muted); font-size: 0.9rem;">{{ optional($u->created_at)->format('M d, Y') ?? '-' }}</td>
                                    <td>
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <!-- Toggle Active Status -->
                                            <form method="POST" action="{{ route('admin.users.update', ['user' => $u->id]) }}" style="display: inline;">
                                                @csrf
                                                <input type="hidden" name="is_active" value="{{ $u->is_active ? '0' : '1' }}">
                                                <button 
                                                    type="submit" 
                                                    class="btn btn-secondary" 
                                                    style="padding: 6px 12px; font-size: 0.85rem;">
                                                    {{ $u->is_active ? '⏸️ Deactivate' : '▶️ Activate' }}
                                                </button>
                                            </form>

                                            <!-- Delete Button -->
                                            <form method="POST" action="{{ route('admin.users.delete', ['user' => $u->id]) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                @csrf
                                                <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 0.85rem;">
                                                    🗑️ Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px 20px;">
                                        <p style="color: var(--text-muted);">No users found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        select {
            cursor: pointer;
        }
        select:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 10px rgba(100, 200, 255, 0.2);
        }
    </style>
@endsection
