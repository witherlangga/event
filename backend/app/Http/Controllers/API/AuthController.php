<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\JwtBlacklist;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    protected function jwtSecret(): string
    {
        return config('jwt.secret') ?? env('JWT_SECRET', 'change-me');
    }

    protected function createToken(User $user): array
    {

        $jti = Str::uuid()->toString();
        $payload = [
            'sub' => $user->id,
            'role' => $user->role,
            'jti' => $jti,
            'iat' => time(),
            'exp' => time() + (config('jwt.ttl') ?? 3600),
        ];

        $jwt = JWT::encode($payload, $this->jwtSecret(), 'HS256');

        return [
            'access_token' => $jwt,
            'jti' => $jti,
            'token_type' => 'bearer',
            'expires_in' => $payload['exp'] - $payload['iat'],
            'user' => $user,
        ];
    }

    public function register(Request $request)
    {
        $roleValues = [
            User::ROLE_CUSTOMER,
            User::ROLE_ORGANIZER,
            User::ROLE_SYSTEM_ADMIN,
        ];

        $v = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => ['nullable', Rule::in($roleValues)],
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        // Determine role (default customer). If organizer requested, require invite code and config enabled.
        $role = $request->input('role', User::ROLE_CUSTOMER);

        // free registration for requested role (validated above)

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
        ]);

        $token = $this->createToken($user);

        return response()->json($token, 201);
    }

    public function login(Request $request)
    {
        $v = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $this->createToken($user);

        return response()->json($token);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $header = $request->header('Authorization');
        if (! $header || ! preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return response()->json(['message' => 'Token not provided'], 401);
        }

        $token = $matches[1];
        try {
            $decoded = JWT::decode($token, new \Firebase\JWT\Key($this->jwtSecret(), 'HS256'));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Token invalid'], 401);
        }

        if (empty($decoded->jti)) {
            return response()->json(['message' => 'Token has no jti, cannot revoke'], 400);
        }

        JwtBlacklist::updateOrCreate(
            ['jti' => $decoded->jti],
            [
                'user_id' => $decoded->sub ?? null,
                'revoked_at' => now(),
                'expires_at' => isset($decoded->exp) ? date('Y-m-d H:i:s', $decoded->exp) : null,
            ]
        );

        return response()->json(['message' => 'Logged out']);
    }
}
