<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Usage: middleware([RoleMiddleware::class . ':system_admin'])
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user();
        $isApiRequest = $request->expectsJson() || str_starts_with($request->path(), 'api/');

        if (! $user) {
            if ($isApiRequest) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if ($user->role !== $role) {
            if ($isApiRequest) {
                return response()->json(['message' => 'Unauthorized: role '.$role.' required'], 403);
            }

            return redirect()->route('login')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
