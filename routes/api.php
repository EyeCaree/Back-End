<?php

use App\Http\Controllers\FotoMataController;
use App\Http\Controllers\KlasifikasiController;
use App\Http\Controllers\PredictionController;
use Illuminate\Support\Facades\Route;

Route::post('/upload', [FotoMataController::class, 'uploadFoto']);
Route::get('/klasifikasi/{id}', [KlasifikasiController::class, 'getHasil']);
Route::post('/predict', [PredictionController::class, 'predict']);
