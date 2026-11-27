<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LupaSandiController;

// Publik (tanpa token)
Route::post('/register', [AuthController::class, 'daftar']);
Route::post('/login', [AuthController::class, 'masuk']);
Route::post('/lupa-sandi', [LupaSandiController::class, 'kirimKode'])->middleware('throttle:5,1');
Route::post('/reset-sandi', [LupaSandiController::class, 'reset'])->middleware('throttle:10,1');

// Perlu token Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'saya']);
    Route::post('/logout', [AuthController::class, 'keluar']);
});