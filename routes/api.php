<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\APIMobileController;

Route::post('/login', [APIMobileController::class, 'login'])->name('api.login');
Route::post('/logout', [APIMobileController::class, 'logout'])->name('api.logout');
Route::post('/register', [APIMobileController::class, 'register'])->name('api.register');

// 🌐 Testimoni bisa diakses publik (tidak perlu login untuk lihat testimoni)
Route::get('/getDataTestimoni', [APIMobileController::class, 'getDataTestimoni']);

// 🔒 PROTECTED ROUTES (butuh autentikasi dengan token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/pasien/profile', [APIMobileController::class, 'getProfile']);
    Route::put('/pasien/update', [APIMobileController::class, 'updateProfile']);
    Route::get('/getJadwalDokter', [APIMobileController::class, 'getJadwalDokter'])->name('getJadwalDokter');

    // Kunjungan routes
    Route::post('/kunjungan/create', [APIMobileController::class, 'bookingDokter']);
    // Route::put('/kunjungan/ubah-status', [APIMobileController::class, 'ubahStatusKunjungan']);
    // Route::post('/kunjungan/batalkan', [APIMobileController::class, 'batalkanStatusKunjungan']);
    Route::get('/kunjungan/riwayat/{pasien_id}', [APIMobileController::class, 'getRiwayatKunjungan']); // 🔥 TAMBAH INI

    Route::get('/getDataJadwalDokter', [APIMobileController::class, 'getDataJadwalDokter']);
    Route::get('/getDataKunjungan', [APIMobileController::class, 'getDataKunjungan']);
    Route::get('/getDataTestimoni', [APIMobileController::class, 'getDataTestimoni']);
    Route::get('/getDataDokter', [APIMobileController::class, 'getDataDokter']);
    Route::get('/getDataSpesialisasiDokter', [APIMobileController::class, 'getDataSpesialisasiDokter']);
    Route::get('/getDataDokterSpesialisasi', [APIMobileController::class, 'getDataDokterSpesialisasi']);
});

Route::post('/create-data-testimoni', [APIMobileController::class, 'createDataTestimomi']);
Route::post('/kunjungan/batalkan', [APIMobileController::class, 'batalkanStatusKunjungan']);
Route::put('/kunjungan/ubah-status', [APIMobileController::class, 'ubahStatusKunjungan']);
