<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;

// Home page with tasks
Route::get('/', [TaskController::class, 'index'])->name('home');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard will show user's tasks
Route::get('/profile', [AuthController::class, 'profile'])->middleware('auth')->name('profile');
Route::get('/dashboard', [TaskController::class, 'dashboard'])->middleware('auth')->name('dashboard');


// Task management routes
Route::middleware('auth')->group(function () {
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::post('/tasks/{task}/accept', [TaskController::class, 'takeTask'])->middleware('auth');
    Route::post('/tasks/{task}/complete', [TaskController::class, 'completeTask'])->middleware('auth');
    Route::post('/tasks/{task}/cancel', [TaskController::class, 'cancelTask'])->middleware('auth');
});

