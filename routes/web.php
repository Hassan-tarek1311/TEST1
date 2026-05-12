<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\WebAuthController;
use App\Http\Controllers\Web\DashboardController;

// ===========================
// Guest routes
// ===========================
Route::middleware('web')->group(function () {
    Route::get('/login',    [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [WebAuthController::class, 'login'])->name('login.post');
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[WebAuthController::class, 'register'])->name('register.post');
    Route::post('/logout',  [WebAuthController::class, 'logout'])->name('logout');

    // ===========================
    // Authenticated web views
    // ===========================
    Route::middleware(\App\Http\Middleware\WebAuthMiddleware::class)->group(function () {
        Route::get('/',          [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::get('/projects', function() { return view('projects.index'); })->name('projects.index');
        Route::get('/time-logs', function() { return view('timelogs.index'); })->name('timelogs.index');
        Route::get('/users',    function() { return view('users.index'); })->name('users.index');
        Route::get('/ai-report',function() { return view('ai.report'); })->name('ai.report');
    });
});
