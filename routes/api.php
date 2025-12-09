<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\Api\AttendanceApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/ping', function () {
    return response()->json(
        ['data' => 'pong'], 
        200, 
        ['Content-Type' => 'application/json']
        );
    });

    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:60,1');

    Route::middleware(['auth:sanctum', 'throttle:300,1'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        Route::get('events', [EventApiController::class, 'index']);
        Route::get('events/{event}', [EventApiController::class, 'show']);

        Route::get('events/{event}/attendance', [AttendanceApiController::class, 'index']);
        Route::post('events/{event}/attendance', [AttendanceApiController::class, 'store']);
        Route::get('events/{event}/stats', [AttendanceApiController::class, 'stats']);
    });
});
