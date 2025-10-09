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
    Route::get('/getDataJadwalDokter', [APIController::class, 'getDataJadwalDokter'])->name('get.data.jadwal.dokter');
    Route::get('/getDataKunjungan', [APIController::class, 'getDataKunjungan'])->name('getee.data.kunjungan');
    Route::get('/getDataTestimoni', [APIController::class, 'getDataTestimoni'])->name('get.data.testimoni');
    Route::get('/getDataDokter', [APIController::class, 'getDataDokter'])->name('get.data.dokter');
    Route::get('/getDataSpesialisasiDokter', [APIController::class, 'getDataSpesialisasiDokter'])->name('get.data.spesialisasi.dokter');
    Route::get('/getDataDokterSpesialisasi', [APIController::class, 'getDataDokterSpesialisasi'])->name('get.data.dokter.spesialisasi');
});
