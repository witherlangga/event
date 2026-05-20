<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::select('id','name','email','role','is_active','created_at')->orderByDesc('created_at')->get();
        return response()->json($users);
    }

    public function update(Request $request, $id)
    {
        $v = $request->validate([
            'role' => ['required', Rule::in(['system_admin','organizer','customer'])],
            'is_active' => ['required','boolean'],
        ]);

        $user = User::findOrFail($id);
        $user->role = $v['role'];
        $user->is_active = (bool) $v['is_active'];
        $user->save();

        // simple audit log to laravel log
        logger()->info('Admin updated user', ['admin_id' => optional($request->user())->id, 'user_id' => $user->id, 'role' => $user->role, 'is_active' => $user->is_active]);

        return response()->json(['message' => 'User updated', 'user' => $user]);
    }

    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        logger()->info('Admin soft-deleted user', ['admin_id' => optional($request->user())->id, 'user_id' => $user->id]);
        return response()->json(['message' => 'User soft-deleted']);
    }
}
