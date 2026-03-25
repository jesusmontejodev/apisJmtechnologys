<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\ProjectsController;
use App\Http\Controllers\Api\ProxyController;
use Illuminate\Support\Facades\Route;

// Health check endpoint  
Route::get('/health', [HealthController::class, 'check'])->name('health');

// Public routes
Route::post('/submit/{project_token}', [ProxyController::class, 'submit'])
    ->name('submit')
    ->middleware('throttle:60,1'); // 60 requests per minute per IP

// Auth routes with stricter rate limiting
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register'])->name('api.register');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('api.login');
});

// Protected routes (require auth via Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/auth/logout', [AuthController::class, 'logout'])->name('logout');

    // Projects routes
    Route::get('/projects', [ProjectsController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectsController::class, 'store'])->name('projects.store');
    Route::get('/projects/{slug}', [ProjectsController::class, 'show'])->name('projects.show');
    Route::put('/projects/{slug}', [ProjectsController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{slug}', [ProjectsController::class, 'destroy'])->name('projects.destroy');
    Route::post('/projects/{slug}/regenerate-token', [ProjectsController::class, 'regenerateToken'])->name('projects.regenerate-token');

    // Submission logs and stats
    Route::get('/projects/{slug}/logs', [ProjectsController::class, 'logs'])->name('projects.logs');
    Route::get('/projects/{slug}/stats', [ProjectsController::class, 'stats'])->name('projects.stats');
});
