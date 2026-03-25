<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectsWebController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Protected routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/health', [DashboardController::class, 'health'])->name('health');
    
    // Projects management
    Route::get('/projects', [ProjectsWebController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectsWebController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectsWebController::class, 'store'])->name('projects.store');
    Route::get('/projects/{slug}', [ProjectsWebController::class, 'show'])->name('projects.show');
    Route::get('/projects/{slug}/edit', [ProjectsWebController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{slug}', [ProjectsWebController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{slug}', [ProjectsWebController::class, 'destroy'])->name('projects.destroy');
    Route::get('/projects/{slug}/stats', [ProjectsWebController::class, 'stats'])->name('projects.stats');
});

