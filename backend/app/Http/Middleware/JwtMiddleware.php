<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\JwtBlacklist;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization');
        if (! $header || ! preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return response()->json(['message' => 'Token not provided'], 401);
        }

        $token = $matches[1];

        try {
            $secret = config('jwt.secret') ?? env('JWT_SECRET', 'change-me');
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Token invalid: ' . $e->getMessage()], 401);
        }

        if (empty($decoded->sub)) {
            return response()->json(['message' => 'Token missing subject'], 401);
        }

        // Check blacklist
        if (! empty($decoded->jti)) {
            $exists = JwtBlacklist::where('jti', $decoded->jti)
                ->where(function ($q) use ($decoded) {
                    if (! empty($decoded->exp)) {
                        $q->where('expires_at', '>=', date('Y-m-d H:i:s', $decoded->exp));
                    }
                })->exists();

            if ($exists) {
                return response()->json(['message' => 'Token has been revoked'], 401);
            }
        }

        $user = User::find($decoded->sub);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 401);
        }

        // Authenticate the user for this request
        Auth::setUser($user);
        $request->setUserResolver(function () use ($user) { return $user; });

        return $next($request);
    }
}
