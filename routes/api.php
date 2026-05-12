<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TimeLogController;
use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes - TimeBee
|--------------------------------------------------------------------------
|
| Public routes  → لا تحتاج token
| Protected routes → تحتاج JWT token في الـ header
|   Authorization: Bearer {token}
|
*/

// ===========================
// Public Routes (بدون auth)
// ===========================
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// ===========================
// Protected Routes (محتاج JWT)
// ===========================
Route::middleware('auth:api')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
    });

    // Projects CRUD
    Route::apiResource('projects', ProjectController::class);

    // Time Logs
    Route::prefix('time-logs')->group(function () {
        Route::get('/',           [TimeLogController::class, 'index']);
        Route::post('/start',     [TimeLogController::class, 'start']);
        Route::patch('/{id}/stop',[TimeLogController::class, 'stop']);
        Route::get('/{id}',       [TimeLogController::class, 'show']);
        Route::delete('/{id}',    [TimeLogController::class, 'destroy']);
    });

    // AI Endpoints
    Route::prefix('ai')->group(function () {
        Route::get('/productivity/{userId}', [AiController::class, 'productivityReport']);
        Route::get('/team-summary',          [AiController::class, 'teamSummary']);
    });

    // User Management (admin only)
    Route::prefix('users')->group(function () {
        Route::get('/',                    [UserController::class, 'index']);
        Route::post('/',                   [UserController::class, 'store']);
        Route::get('/{id}',                [UserController::class, 'show']);
        Route::put('/{id}',                [UserController::class, 'update']);
        Route::patch('/{id}/toggle-status',[UserController::class, 'toggleStatus']);
    });
});
