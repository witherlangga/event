<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (! $user || ! method_exists($user, 'isSystemAdmin') || ! $user->isSystemAdmin()) {
            return redirect()->route('organizer.login')->with('error', 'Unauthorized');
        }

        $users = User::orderByDesc('created_at')->get();
        return view('admin.users.index', compact('users'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (! $user || ! method_exists($user, 'isSystemAdmin') || ! $user->isSystemAdmin()) {
            return redirect()->route('organizer.login')->with('error', 'Unauthorized');
        }

        $v = $request->validate([
            'role' => ['required'],
            'is_active' => ['nullable','in:0,1'],
        ]);

        $u = User::findOrFail($id);
        $u->role = $v['role'];
        $u->is_active = isset($v['is_active']) ? (bool) $v['is_active'] : $u->is_active;
        $u->save();

        return redirect()->route('admin.users')->with('success', 'User updated');
    }

    public function delete(Request $request, $id)
    {
        $user = Auth::user();
        if (! $user || ! method_exists($user, 'isSystemAdmin') || ! $user->isSystemAdmin()) {
            return redirect()->route('organizer.login')->with('error', 'Unauthorized');
        }

        $u = User::findOrFail($id);
        $u->delete();
        logger()->info('Admin web soft-deleted user', ['admin_id' => $user->id, 'user_id' => $u->id]);

        return redirect()->route('admin.users')->with('success', 'User soft-deleted');
    }
}
