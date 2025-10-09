<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\APIController;

// 🔓 PUBLIC ROUTES (tidak butuh login)
Route::post('/login', [APIController::class, 'login'])->name('api.login');
Route::post('/logout', [APIController::class, 'logout'])->name('api.logout');
Route::post('/register', [APIController::class, 'register'])->name('api.register');

// 🌐 Testimoni bisa diakses publik (tidak perlu login untuk lihat testimoni)
Route::get('/getDataTestimoni', [APIController::class, 'getDataTestimoni'])->name('get.data.testimoni');

// 🔒 PROTECTED ROUTES (butuh autentikasi dengan token)
Route::middleware('auth:sanctum')->group(function () {
    // Profile routes
    Route::get('/pasien/profile', [APIController::class, 'getProfile']);
    Route::put('/pasien/update', [APIController::class, 'updateProfile']);
    
    // Jadwal Dokter routes
    Route::get('/getJadwalDokter', [APIController::class, 'getJadwalDokter'])->name('getJadwalDokter');

    // Kunjungan routes
    Route::post('/kunjungan/create', [APIController::class, 'bookingDokter']);
    Route::put('/kunjungan/ubah-status', [APIController::class, 'ubahStatusKunjungan']);
    Route::put('/kunjungan/batalkan', [APIController::class, 'batalkanStatusKunjungan']);
    Route::get('/kunjungan/riwayat/{pasien_id}', [APIController::class, 'getRiwayatKunjungan']);

    // Data routes (butuh auth)
    Route::get('/getDataJadwalDokter', [APIController::class, 'getDataJadwalDokter'])->name('get.data.jadwal.dokter');
    Route::get('/getDataKunjungan', [APIController::class, 'getDataKunjungan'])->name('get.data.kunjungan');
    Route::get('/getDataDokter', [APIController::class, 'getDataDokter'])->name('get.data.dokter');
    Route::get('/getDataSpesialisasiDokter', [APIController::class, 'getDataSpesialisasiDokter'])->name('get.data.spesialisasi.dokter');
    Route::get('/getDataDokterSpesialisasi', [APIController::class, 'getDataDokterSpesialisasi'])->name('get.data.dokter.spesialisasi');
    
    // Testimoni create (butuh login untuk buat testimoni)
    Route::post('/create-data-testimoni', [APIController::class, 'createDataTestimoni'])->name('create.data.testimoni');
});