<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\CustomerPanelController;
use App\Http\Controllers\Web\AuthController;
use App\Models\Event;
use App\Models\User;

Route::get('/', function () {
    $events = Event::where('is_active', true)
        ->orderBy('starts_at')
        ->take(4)
        ->get();
    return view('local_web', compact('events'));
});
// Authentication routes
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.submit');
Route::get('register', [AuthController::class, 'showRegister'])->name('register');
Route::post('register', [AuthController::class, 'register'])->name('register.submit');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');
// Dev impersonation (testing web UI)
Route::get('dev/impersonate', function () {
    $users = User::orderBy('name')->get();

    return view('dev.impersonate', compact('users'));
})->name('dev.impersonate');

Route::post('dev/impersonate', function () {
    $userId = request('user_id');
    session(['impersonate_user_id' => $userId]);
    \Illuminate\Support\Facades\Auth::loginUsingId($userId);

    return redirect()->route('customer.events');
})->name('dev.impersonate.post');

Route::post('dev/logout-impersonate', function () {
    session()->forget('impersonate_user_id');
    \Illuminate\Support\Facades\Auth::logout();

    return redirect()->route('dev.impersonate');
})->name('dev.logout-impersonate');

Route::middleware([\App\Http\Middleware\Impersonate::class, 'auth', \App\Http\Middleware\RoleMiddleware::class.':customer'])->group(function () {
    Route::get('customer/events', [CustomerPanelController::class, 'index'])->name('customer.events');
    Route::get('customer/events/{event}', [CustomerPanelController::class, 'show'])->name('customer.events.show');
    Route::post('customer/events/{event}/purchase', [CustomerPanelController::class, 'purchase'])->name('customer.events.purchase');
    Route::get('customer/mockpay/{order}', [CustomerPanelController::class, 'mockPaymentShow'])->name('customer.mockpay.show');
    Route::post('customer/mockpay/{order}/complete', [CustomerPanelController::class, 'mockPaymentComplete'])->name('customer.mockpay.complete');

    Route::get('customer/orders', [CustomerPanelController::class, 'orders'])->name('customer.orders');
    Route::get('customer/orders/{order}', [CustomerPanelController::class, 'orderShow'])->name('customer.orders.show');
    Route::post('customer/orders/{order}/request-refund', [CustomerPanelController::class, 'requestRefund'])->name('customer.orders.request_refund');
});

Route::middleware([\App\Http\Middleware\Impersonate::class, 'auth', \App\Http\Middleware\RoleMiddleware::class.':system_admin'])->group(function () {
    Route::get('admin/users', [\App\Http\Controllers\Web\Admin\UserController::class, 'index'])->name('admin.users');
    Route::post('admin/users/{user}', [\App\Http\Controllers\Web\Admin\UserController::class, 'update'])->name('admin.users.update');
    Route::post('admin/users/{user}/delete', [\App\Http\Controllers\Web\Admin\UserController::class, 'delete'])->name('admin.users.delete');
});

Route::get('dev/token', function () {
    $user = User::where('email', 'customer@example.com')->first();
    if (! $user) {
        return response()->json(['error' => 'no user'], 404);
    }
    $jti = \Illuminate\Support\Str::uuid()->toString();
    $payload = [
        'sub' => $user->id,
        'role' => $user->role,
        'jti' => $jti,
        'iat' => time(),
        'exp' => time() + (config('jwt.ttl') ?? env('JWT_TTL', 3600)),
    ];
    $jwt = \Firebase\JWT\JWT::encode($payload, config('jwt.secret') ?? env('JWT_SECRET'), 'HS256');

    return response()->json(['access_token' => $jwt, 'jti' => $jti, 'user' => $user]);
});
