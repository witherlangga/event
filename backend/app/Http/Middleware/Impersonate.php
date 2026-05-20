<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class Impersonate
{
    public function handle($request, Closure $next)
    {
        $id = session('impersonate_user_id');
        if ($id) {
            if (!Auth::check() || Auth::id() !== $id) {
                $user = User::find($id);
                if ($user) {
                    Auth::loginUsingId($id);
                }
            }
        }

        return $next($request);
    }
}
