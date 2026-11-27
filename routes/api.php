<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// Publik (tanpa token)
Route::post('/register', [AuthController::class, 'daftar']);
Route::post('/login', [AuthController::class, 'masuk']);

// Perlu token Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'saya']);
    Route::post('/logout', [AuthController::class, 'keluar']);
});