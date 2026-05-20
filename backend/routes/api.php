<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware([\App\Http\Middleware\JwtMiddleware::class])->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

// Public events for customers
Route::get('events', [\App\Http\Controllers\API\EventController::class, 'index']);
Route::get('events/{event}', [\App\Http\Controllers\API\EventController::class, 'show']);

// Organizer routes
Route::prefix('organizer')->group(function () {
    Route::middleware([\App\Http\Middleware\JwtMiddleware::class, \App\Http\Middleware\RoleMiddleware::class.':organizer'])->group(function () {
        Route::get('events', [\App\Http\Controllers\API\Organizer\EventController::class, 'index']);
        Route::post('events', [\App\Http\Controllers\API\Organizer\EventController::class, 'store']);
        Route::get('events/{event}', [\App\Http\Controllers\API\Organizer\EventController::class, 'show']);
        Route::put('events/{event}', [\App\Http\Controllers\API\Organizer\EventController::class, 'update']);
        Route::delete('events/{event}', [\App\Http\Controllers\API\Organizer\EventController::class, 'destroy']);
        Route::post('events/{event}/cover', [\App\Http\Controllers\API\Organizer\EventController::class, 'uploadCover']);
        Route::post('events/{event}/gallery', [\App\Http\Controllers\API\Organizer\EventController::class, 'uploadGallery']);
        Route::delete('events/{event}/gallery/{image}', [\App\Http\Controllers\API\Organizer\EventController::class, 'deleteGallery']);
        // Ticket types management per event
        Route::get('events/{event}/tickets', [\App\Http\Controllers\API\Organizer\TicketTypeController::class, 'index']);
        Route::post('events/{event}/tickets', [\App\Http\Controllers\API\Organizer\TicketTypeController::class, 'store']);
        Route::get('events/{event}/tickets/{ticket}', [\App\Http\Controllers\API\Organizer\TicketTypeController::class, 'show']);
        Route::put('events/{event}/tickets/{ticket}', [\App\Http\Controllers\API\Organizer\TicketTypeController::class, 'update']);
        Route::delete('events/{event}/tickets/{ticket}', [\App\Http\Controllers\API\Organizer\TicketTypeController::class, 'destroy']);
    });
});

// Purchase endpoint for customers
Route::post('events/{event}/purchase', [\App\Http\Controllers\API\PurchaseController::class, 'purchase'])
    ->middleware([\App\Http\Middleware\JwtMiddleware::class, \App\Http\Middleware\RoleMiddleware::class.':customer']);

// Ticket QR serve (authenticated)
Route::get('tickets/{ticket}/qr', [\App\Http\Controllers\API\TicketController::class, 'qr'])
    ->middleware([\App\Http\Middleware\JwtMiddleware::class]);

// Signed download route for QR (no JWT required; link must be signed)
Route::get('tickets/{ticket}/qr/download', [\App\Http\Controllers\API\TicketController::class, 'download'])
    ->name('tickets.qr.download')
    ->middleware(['signed']);

// Orders management
Route::middleware([\App\Http\Middleware\JwtMiddleware::class])->group(function () {
    Route::get('orders', [\App\Http\Controllers\API\OrderController::class, 'index']);
    Route::get('orders/{order}', [\App\Http\Controllers\API\OrderController::class, 'show']);
    Route::post('orders/{order}/refund', [\App\Http\Controllers\API\OrderController::class, 'refund']);
    Route::post('orders/{order}/cancel', [\App\Http\Controllers\API\OrderController::class, 'cancel']);
    Route::post('orders/{order}/request-refund', [\App\Http\Controllers\API\OrderController::class, 'requestRefund']);
    Route::post('refunds/{refund}/approve', [\App\Http\Controllers\API\OrderController::class, 'approveRefund']);
    Route::post('refunds/{refund}/reject', [\App\Http\Controllers\API\OrderController::class, 'rejectRefund']);
});

// Admin API (user management) - use api/admin prefix to avoid colliding with web routes
Route::prefix('api/admin')->middleware([\App\Http\Middleware\JwtMiddleware::class, \App\Http\Middleware\RoleMiddleware::class . ':system_admin'])->group(function () {
    Route::get('users', [\App\Http\Controllers\API\Admin\UserController::class, 'index']);
    Route::put('users/{user}', [\App\Http\Controllers\API\Admin\UserController::class, 'update']);
    Route::delete('users/{user}', [\App\Http\Controllers\API\Admin\UserController::class, 'destroy']);
});
