<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BandProfileController;
use App\Http\Controllers\API\BandMemberController;
use App\Http\Controllers\API\AlbumController;
use App\Http\Controllers\API\GalleryController;
use App\Http\Controllers\API\NewsController;
use App\Http\Controllers\API\Admin\BandProfileController as AdminBandProfileController;
use App\Http\Controllers\API\Admin\BandMemberController as AdminBandMemberController;
use App\Http\Controllers\API\Admin\AlbumController as AdminAlbumController;
use App\Http\Controllers\API\Admin\SongController as AdminSongController;
use App\Http\Controllers\API\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\API\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\API\Admin\ConcertController;
use App\Http\Controllers\API\Admin\TicketTypeController as AdminTicketTypeController;
use App\Http\Controllers\API\Admin\UserController as AdminUserController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware([\App\Http\Middleware\JwtMiddleware::class])->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

// Konten band (publik)
Route::get('band/profile', [BandProfileController::class, 'show']);
Route::get('band/members', [BandMemberController::class, 'index']);
Route::get('band/albums', [AlbumController::class, 'index']);
Route::get('band/albums/{album}', [AlbumController::class, 'show']);
Route::get('band/gallery', [GalleryController::class, 'index']);
Route::get('band/news', [NewsController::class, 'index']);
Route::get('band/news/{newsPost}', [NewsController::class, 'show']);

// Konser (publik)
Route::get('events', [\App\Http\Controllers\API\EventController::class, 'index']);
Route::get('events/{event}', [\App\Http\Controllers\API\EventController::class, 'show']);

// Admin
Route::prefix('admin')->middleware([
    \App\Http\Middleware\JwtMiddleware::class,
    \App\Http\Middleware\RoleMiddleware::class.':system_admin',
])->group(function () {
    Route::get('users', [AdminUserController::class, 'index']);
    Route::put('users/{user}', [AdminUserController::class, 'update']);
    Route::delete('users/{user}', [AdminUserController::class, 'destroy']);

    Route::get('band/profile', [AdminBandProfileController::class, 'show']);
    Route::put('band/profile', [AdminBandProfileController::class, 'update']);

    Route::get('members', [AdminBandMemberController::class, 'index']);
    Route::post('members', [AdminBandMemberController::class, 'store']);
    Route::put('members/{member}', [AdminBandMemberController::class, 'update']);
    Route::delete('members/{member}', [AdminBandMemberController::class, 'destroy']);

    Route::get('albums', [AdminAlbumController::class, 'index']);
    Route::post('albums', [AdminAlbumController::class, 'store']);
    Route::put('albums/{album}', [AdminAlbumController::class, 'update']);
    Route::delete('albums/{album}', [AdminAlbumController::class, 'destroy']);
    Route::post('albums/{album}/songs', [AdminSongController::class, 'store']);
    Route::put('albums/{album}/songs/{song}', [AdminSongController::class, 'update']);
    Route::delete('albums/{album}/songs/{song}', [AdminSongController::class, 'destroy']);

    Route::get('gallery', [AdminGalleryController::class, 'index']);
    Route::post('gallery', [AdminGalleryController::class, 'store']);
    Route::put('gallery/{galleryItem}', [AdminGalleryController::class, 'update']);
    Route::delete('gallery/{galleryItem}', [AdminGalleryController::class, 'destroy']);

    Route::get('news', [AdminNewsController::class, 'index']);
    Route::post('news', [AdminNewsController::class, 'store']);
    Route::put('news/{newsPost}', [AdminNewsController::class, 'update']);
    Route::delete('news/{newsPost}', [AdminNewsController::class, 'destroy']);

    Route::get('concerts', [ConcertController::class, 'index']);
    Route::post('concerts', [ConcertController::class, 'store']);
    Route::get('concerts/{event}', [ConcertController::class, 'show']);
    Route::put('concerts/{event}', [ConcertController::class, 'update']);
    Route::delete('concerts/{event}', [ConcertController::class, 'destroy']);
    Route::post('concerts/{event}/cover', [ConcertController::class, 'uploadCover']);
    Route::post('concerts/{event}/gallery', [ConcertController::class, 'uploadGallery']);
    Route::delete('concerts/{event}/gallery/{image}', [ConcertController::class, 'deleteGallery']);

    Route::get('concerts/{event}/tickets', [AdminTicketTypeController::class, 'index']);
    Route::post('concerts/{event}/tickets', [AdminTicketTypeController::class, 'store']);
    Route::get('concerts/{event}/tickets/{ticket}', [AdminTicketTypeController::class, 'show']);
    Route::put('concerts/{event}/tickets/{ticket}', [AdminTicketTypeController::class, 'update']);
    Route::delete('concerts/{event}/tickets/{ticket}', [AdminTicketTypeController::class, 'destroy']);
});

Route::post('events/{event}/purchase', [\App\Http\Controllers\API\PurchaseController::class, 'purchase'])
    ->middleware([\App\Http\Middleware\JwtMiddleware::class, \App\Http\Middleware\RoleMiddleware::class.':customer']);

Route::get('tickets/{ticket}/qr', [\App\Http\Controllers\API\TicketController::class, 'qr'])
    ->middleware([\App\Http\Middleware\JwtMiddleware::class]);

Route::get('tickets/{ticket}/qr/download', [\App\Http\Controllers\API\TicketController::class, 'download'])
    ->name('tickets.qr.download')
    ->middleware(['signed']);

Route::middleware([\App\Http\Middleware\JwtMiddleware::class])->group(function () {
    Route::get('orders', [\App\Http\Controllers\API\OrderController::class, 'index']);
    Route::get('orders/{order}', [\App\Http\Controllers\API\OrderController::class, 'show']);
    Route::post('orders/{order}/refund', [\App\Http\Controllers\API\OrderController::class, 'refund']);
    Route::post('orders/{order}/cancel', [\App\Http\Controllers\API\OrderController::class, 'cancel']);
    Route::post('orders/{order}/request-refund', [\App\Http\Controllers\API\OrderController::class, 'requestRefund']);
    Route::post('refunds/{refund}/approve', [\App\Http\Controllers\API\OrderController::class, 'approveRefund']);
    Route::post('refunds/{refund}/reject', [\App\Http\Controllers\API\OrderController::class, 'rejectRefund']);
});
