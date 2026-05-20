<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\Web\OrganizerPanelController;

// Simple organizer testing UI (impersonation-based)
Route::get('organizer/login', [OrganizerPanelController::class, 'showLoginForm'])->name('organizer.login');
Route::post('organizer/impersonate', [OrganizerPanelController::class, 'impersonate'])->name('organizer.impersonate');

Route::middleware([\App\Http\Middleware\Impersonate::class])->group(function () {
    Route::get('organizer/dashboard', [OrganizerPanelController::class, 'dashboard'])->name('organizer.dashboard');
    Route::get('organizer/refunds', [OrganizerPanelController::class, 'refunds'])->name('organizer.refunds');
    Route::post('organizer/refunds/{id}/approve', [OrganizerPanelController::class, 'approveRefund'])->name('organizer.refunds.approve');
    Route::post('organizer/refunds/{id}/reject', [OrganizerPanelController::class, 'rejectRefund'])->name('organizer.refunds.reject');

    // Event CRUD
    Route::get('organizer/events/create', [OrganizerPanelController::class, 'createEvent'])->name('organizer.events.create');
    // use a distinct POST path to avoid collision with API POST route
    Route::post('organizer/events/store', [OrganizerPanelController::class, 'storeEvent'])->name('organizer.events.store');
    Route::get('organizer/events/{id}/edit', [OrganizerPanelController::class, 'editEvent'])->name('organizer.events.edit');
    Route::put('organizer/events/{id}', [OrganizerPanelController::class, 'updateEvent'])->name('organizer.events.update');
    Route::delete('organizer/events/{id}', [OrganizerPanelController::class, 'deleteEvent'])->name('organizer.events.delete');

    // TicketType CRUD
    Route::get('organizer/events/{eventId}/tickets', [OrganizerPanelController::class, 'ticketIndex'])->name('organizer.tickets');
    Route::get('organizer/events/{eventId}/tickets/create', [OrganizerPanelController::class, 'ticketCreate'])->name('organizer.tickets.create');
    Route::post('organizer/events/{eventId}/tickets', [OrganizerPanelController::class, 'ticketStore'])->name('organizer.tickets.store');
    Route::get('organizer/events/{eventId}/tickets/{ticketId}/edit', [OrganizerPanelController::class, 'ticketEdit'])->name('organizer.tickets.edit');
    Route::put('organizer/events/{eventId}/tickets/{ticketId}', [OrganizerPanelController::class, 'ticketUpdate'])->name('organizer.tickets.update');
    Route::delete('organizer/events/{eventId}/tickets/{ticketId}', [OrganizerPanelController::class, 'ticketDelete'])->name('organizer.tickets.delete');

    Route::post('organizer/logout-impersonate', function () {
        session()->forget('impersonate_user_id');
        \Illuminate\Support\Facades\Auth::logout();
        return redirect()->route('organizer.login');
    })->name('organizer.logout');

    // Admin web UI for user management (impersonation-based; require admin account)
    Route::get('admin/users', [\App\Http\Controllers\Web\Admin\UserController::class, 'index'])->name('admin.users');
    Route::post('admin/users/{user}', [\App\Http\Controllers\Web\Admin\UserController::class, 'update'])->name('admin.users.update');
    Route::post('admin/users/{user}/delete', [\App\Http\Controllers\Web\Admin\UserController::class, 'delete'])->name('admin.users.delete');
});

use App\Http\Controllers\Web\CustomerPanelController;

// Customer test UI (reuse impersonation)
Route::middleware([\App\Http\Middleware\Impersonate::class])->group(function () {
    Route::get('customer/events', [CustomerPanelController::class, 'index'])->name('customer.events');
    Route::get('customer/events/{event}', [CustomerPanelController::class, 'show'])->name('customer.events.show');
    Route::post('customer/events/{event}/purchase', [CustomerPanelController::class, 'purchase'])->name('customer.events.purchase');
    Route::get('customer/mockpay/{order}', [CustomerPanelController::class, 'mockPaymentShow'])->name('customer.mockpay.show');
    Route::post('customer/mockpay/{order}/complete', [CustomerPanelController::class, 'mockPaymentComplete'])->name('customer.mockpay.complete');

    Route::get('customer/orders', [CustomerPanelController::class, 'orders'])->name('customer.orders');
    Route::get('customer/orders/{order}', [CustomerPanelController::class, 'orderShow'])->name('customer.orders.show');
    Route::post('customer/orders/{order}/request-refund', [CustomerPanelController::class, 'requestRefund'])->name('customer.orders.request_refund');
});

// NOTE: API routes are loaded by the framework via bootstrap/app.php

// Development helper: return a JWT for the seeded customer (no CSRF, dev only)
Route::get('dev/token', function () {
    $user = \App\Models\User::where('email', 'customer@example.com')->first();
    if (! $user) return response()->json(['error' => 'no user'], 404);
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
