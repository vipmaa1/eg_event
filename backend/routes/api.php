<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\CheckInController;
use App\Http\Controllers\Api\OrganizerController;
use App\Http\Controllers\Api\VenueController;
use App\Http\Controllers\Api\PromoCodeController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\UserEventActionController;
use App\Http\Controllers\Api\PayoutController;
use App\Http\Controllers\Api\OrderController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::put('/profile', [ProfileController::class, 'update']);
    Route::get('/profile/orders', [ProfileController::class, 'orders']);
    Route::get('/profile/tickets', [ProfileController::class, 'tickets']);

    Route::get('/my-events', [EventController::class, 'myEvents']);
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{event}', [EventController::class, 'update']);
    Route::post('/events/{event}/publish', [EventController::class, 'publish']);
    Route::post('/events/{event}/cancel', [EventController::class, 'cancel']);

    Route::get('/organizers/mine', [OrganizerController::class, 'dashboard']);
    Route::post('/organizers', [OrganizerController::class, 'store']);
    Route::put('/organizers/{organizer}', [OrganizerController::class, 'update']);

    Route::post('/venues', [VenueController::class, 'store']);
    Route::put('/venues/{venue}', [VenueController::class, 'update']);

    Route::post('/tickets/purchase', [TicketController::class, 'purchase']);
    Route::post('/orders/{order}/confirm-payment', [TicketController::class, 'confirmPayment']);
    Route::get('/tickets/{ticket}/qr', [TicketController::class, 'getQrCode']);

    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);

    Route::post('/check-in/scan', [CheckInController::class, 'scan']);
    Route::get('/events/{event}/check-in-stats', [CheckInController::class, 'eventStats']);

    Route::apiResource('promo-codes', PromoCodeController::class)->except(['show', 'update']);
    Route::post('/promo-codes/validate', [PromoCodeController::class, 'checkValidity']);

    Route::post('/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    Route::post('/organizers/{organizer}/follow', [FollowController::class, 'follow']);
    Route::delete('/organizers/{organizer}/unfollow', [FollowController::class, 'unfollow']);
    Route::get('/my-follows', [FollowController::class, 'myFollows']);

    Route::post('/event-actions', [UserEventActionController::class, 'store']);
    Route::delete('/events/{event}/actions/{actionType}', [UserEventActionController::class, 'destroy']);
    Route::get('/my-actions', [UserEventActionController::class, 'myActions']);

    Route::apiResource('payouts', PayoutController::class)->only(['index', 'store']);

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Api\AdminController::class, 'dashboard']);
        Route::get('/events', [App\Http\Controllers\Api\AdminController::class, 'listEvents']);
        Route::get('/events/pending', [App\Http\Controllers\Api\AdminController::class, 'listPendingEvents']);
        Route::post('/events/{event}/approve', [App\Http\Controllers\Api\AdminController::class, 'approveEvent']);
        Route::post('/events/{event}/reject', [App\Http\Controllers\Api\AdminController::class, 'rejectEvent']);
        Route::delete('/events/{event}', [App\Http\Controllers\Api\AdminController::class, 'deleteEvent']);
        Route::get('/users', [App\Http\Controllers\Api\AdminController::class, 'listUsers']);
        Route::post('/users/{user}/toggle-status', [App\Http\Controllers\Api\AdminController::class, 'toggleUserStatus']);
        Route::get('/organizers', [App\Http\Controllers\Api\AdminController::class, 'listOrganizers']);
        Route::post('/organizers/{organizer}/verify', [App\Http\Controllers\Api\AdminController::class, 'verifyOrganizer']);
        Route::get('/venues', [App\Http\Controllers\Api\AdminController::class, 'listVenues']);
        Route::post('/venues/{venue}/toggle-status', [App\Http\Controllers\Api\AdminController::class, 'toggleVenueStatus']);
        Route::get('/settings', [App\Http\Controllers\Api\AdminController::class, 'getSettings']);
        Route::post('/settings', [App\Http\Controllers\Api\AdminController::class, 'updateSetting']);
    });
});

Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{event}', [EventController::class, 'show']);
Route::get('/organizers', [OrganizerController::class, 'index']);
Route::get('/organizers/{organizer}', [OrganizerController::class, 'show']);
Route::get('/venues', [VenueController::class, 'index']);
Route::get('/venues/{venue}', [VenueController::class, 'show']);
Route::get('/events/{event}/comments', [CommentController::class, 'index']);
Route::get('/tickets/qr/{code}', [TicketController::class, 'showQr']);
