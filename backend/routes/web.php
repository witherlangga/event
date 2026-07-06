<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\CustomerPanelController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\MusicController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\Admin\MusicController as AdminMusicController;
use App\Models\BandProfile;
use App\Models\User;

Route::get('/', function () {
    $profile = BandProfile::firstOrCreate(['name' => 'Neon Horizon']);
    $socialLinks = $profile->social_links ?? [];
    $socialLinks = array_merge([
        'instagram' => 'https://instagram.com/neonhorizon',
        'youtube' => 'https://youtube.com/@neonhorizon',
        'tiktok' => 'https://tiktok.com/@neonhorizon',
        'spotify' => 'https://open.spotify.com/artist/neonhorizon',
    ], $socialLinks);

    return view('local_web', compact('socialLinks', 'profile'));
})->name('home');
Route::get('tickets', [CustomerPanelController::class, 'tickets'])->name('tickets');
Route::get('music', [MusicController::class, 'index'])->name('music');

// Music API endpoints untuk support mobile & frontend
Route::get('api/music', [MusicController::class, 'getSongs'])->name('api.music.list');
Route::get('api/music/{song}', [MusicController::class, 'getSong'])->name('api.music.show');

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

    return redirect()->route('tickets');
})->name('dev.impersonate.post');

Route::post('dev/logout-impersonate', function () {
    session()->forget('impersonate_user_id');
    \Illuminate\Support\Facades\Auth::logout();

    return redirect()->route('dev.impersonate');
})->name('dev.logout-impersonate');

Route::middleware([\App\Http\Middleware\Impersonate::class, 'auth'])->group(function () {
    Route::get('profile', [ProfileController::class, 'show'])->name('profile');
    Route::post('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

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
    Route::get('admin', function () {
        return redirect()->route('admin.events');
    })->name('admin.dashboard');

    Route::get('admin/events', [\App\Http\Controllers\Web\Admin\EventController::class, 'index'])->name('admin.events');
    Route::get('admin/events/create', [\App\Http\Controllers\Web\Admin\EventController::class, 'create'])->name('admin.events.create');
    Route::post('admin/events', [\App\Http\Controllers\Web\Admin\EventController::class, 'store'])->name('admin.events.store');
    Route::get('admin/events/{event}/edit', [\App\Http\Controllers\Web\Admin\EventController::class, 'edit'])->name('admin.events.edit');
    Route::put('admin/events/{event}', [\App\Http\Controllers\Web\Admin\EventController::class, 'update'])->name('admin.events.update');
    Route::post('admin/events/{event}/delete', [\App\Http\Controllers\Web\Admin\EventController::class, 'destroy'])->name('admin.events.delete');

    Route::get('admin/users', [\App\Http\Controllers\Web\Admin\UserController::class, 'index'])->name('admin.users');
    Route::post('admin/users/{user}', [\App\Http\Controllers\Web\Admin\UserController::class, 'update'])->name('admin.users.update');
    Route::post('admin/users/{user}/delete', [\App\Http\Controllers\Web\Admin\UserController::class, 'delete'])->name('admin.users.delete');
    Route::get('admin/reviews', [\App\Http\Controllers\Web\Admin\OrderReviewController::class, 'index'])->name('admin.reviews');
    Route::post('admin/reviews/tickets/{ticket}/delete', [\App\Http\Controllers\Web\Admin\OrderReviewController::class, 'deleteTicket'])->name('admin.reviews.tickets.delete');
    
    // Band profile / social links settings
    Route::get('admin/settings/band-profile', [\App\Http\Controllers\Web\Admin\BandProfileController::class, 'edit'])->name('admin.settings.band_profile');
    Route::post('admin/settings/band-profile', [\App\Http\Controllers\Web\Admin\BandProfileController::class, 'update'])->name('admin.settings.band_profile.update');
    Route::get('admin/settings/moments', [\App\Http\Controllers\Web\Admin\BandProfileController::class, 'editMoments'])->name('admin.settings.moments');
    Route::post('admin/settings/moments', [\App\Http\Controllers\Web\Admin\BandProfileController::class, 'updateMoments'])->name('admin.settings.moments.update');
    Route::get('admin/settings/music', [AdminMusicController::class, 'index'])->name('admin.settings.music');
    Route::post('admin/settings/music', [AdminMusicController::class, 'store'])->name('admin.settings.music.store');
    Route::post('admin/settings/music/{song}/delete', [AdminMusicController::class, 'destroy'])->name('admin.settings.music.delete');
    Route::post('admin/settings/music/{song}/toggle', [AdminMusicController::class, 'toggleActive'])->name('admin.settings.music.toggle');
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
