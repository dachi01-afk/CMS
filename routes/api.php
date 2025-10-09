<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\APIController;

Route::post('/login', [APIController::class, 'login'])->name('api.login');
Route::post('/logout', [APIController::class, 'logout'])->name('api.logout');
Route::post('/register', [APIController::class, 'register'])->name('api.register');

// Protected routes (butuh autentikasi)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/pasien/profile', [APIController::class, 'getProfile']);
    Route::put('/pasien/update', [APIController::class, 'updateProfile']);
    Route::get('/getJadwalDokter', [APIController::class, 'getJadwalDokter'])->name('getJadwalDokter');

    // Kunjungan routes
    Route::post('/kunjungan/create', [APIController::class, 'bookingDokter']);
    Route::put('/kunjungan/ubah-status', [APIController::class, 'ubahStatusKunjungan']);
    Route::put('/kunjungan/batalkan', [APIController::class, 'batalkanStatusKunjungan']);
    Route::get('/kunjungan/riwayat/{pasien_id}', [APIController::class, 'getRiwayatKunjungan']); // 🔥 TAMBAH INI

    Route::get('/getDataJadwalDokter', [APIController::class, 'getDataJadwalDokter']);
    Route::get('/getDataKunjungan', [APIController::class, 'getDataKunjungan']);
    Route::get('/getDataTestimoni', [APIController::class, 'getDataTestimoni']);
    Route::get('/getDataDokter', [APIController::class, 'getDataDokter']);
    Route::get('/getDataSpesialisasiDokter', [APIController::class, 'getDataSpesialisasiDokter']);
    Route::get('/getDataDokterSpesialisasi', [APIController::class, 'getDataDokterSpesialisasi']);
});

Route::post('/create-data-testimoni', [APIController::class, 'createDataTestimomi'])->name('create.data.testimoni');
