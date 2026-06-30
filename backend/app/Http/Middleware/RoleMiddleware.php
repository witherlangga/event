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
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($user->role !== $role) {
            return response()->json(['message' => 'Unauthorized: role '.$role.' required'], 403);
        }

        return $next($request);
    }
}
