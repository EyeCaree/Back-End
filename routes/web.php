<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FotoMataController;
use App\Http\Controllers\KlasifikasiController;
use App\Http\Controllers\RekomendasiController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\AuthController;


Route::get('/', function () {
    return view('welcome');
});
    Route::get('/users', [UsersController::class, 'index']);
    Route::post('/users', [UsersController::class, 'store']);

    Route::get('/foto-mata', [FotoMataController::class, 'index']);
    Route::post('/foto-mata', [FotoMataController::class, 'store']);
    Route::delete('/foto-mata/{id}', [FotoMataController::class, 'destroy']);

    Route::get('/klasifikasi', [KlasifikasiController::class, 'index']);
    Route::post('/klasifikasi', [KlasifikasiController::class, 'store']);

    Route::get('/rekomendasi', [RekomendasiController::class, 'index']);
    Route::post('/rekomendasi', [RekomendasiController::class, 'store']);



//auth


    // Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout', [AuthController::class, 'logout']);
// });