<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FotoMataController;
use App\Http\Controllers\KlasifikasiController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PredictionController;

// ✅ Rute publik
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/users', [UsersController::class, 'index']);
Route::post('/users', [UsersController::class, 'store']);

Route::get('/foto-mata', [FotoMataController::class, 'index']);
Route::post('/foto-mata', [FotoMataController::class, 'store']);
Route::delete('/foto-mata/{id}', [FotoMataController::class, 'destroy']);

Route::get('/klasifikasi', [KlasifikasiController::class, 'index']);
Route::post('/klasifikasi', [KlasifikasiController::class, 'store']);

Route::post('/predict', [PredictionController::class, 'predict']);

// ✅ Rute yang butuh token (Harus pakai middleware auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});
