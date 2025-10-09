<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\APIMobileController;

// API Untuk Login Pasien
Route::post('/login', [APIMobileController::class, 'login'])->name('api.login');
Route::post('/logout', [APIMobileController::class, 'logout'])->name('api.logout');
Route::post('/register', [APIMobileController::class, 'register'])->name('api.register');

// API Untuk Login Dokter
Route::post('/login-dokter', [APIMobileController::class, 'loginDokter']);


// 🌐 Testimoni bisa diakses publik (tidak perlu login untuk lihat testimoni)
Route::get('/getDataTestimoni', [APIMobileController::class, 'getDataTestimoni']); // PINDAHKAN KE SINI
Route::post('/create-data-testimoni', [APIMobileController::class, 'createDataTestimoni']); // HAPUS SPASI

// 🔒 PROTECTED ROUTES (butuh autentikasi dengan token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/pasien/profile', [APIMobileController::class, 'getProfile']);
    Route::put('/pasien/update', [APIMobileController::class, 'updateProfile']);
    Route::get('/getJadwalDokter', [APIMobileController::class, 'getJadwalDokter'])->name('getJadwalDokter');

    // Kunjungan routes
    Route::post('/kunjungan/create', [APIMobileController::class, 'bookingDokter']);
    Route::put('/kunjungan/ubah-status', [APIMobileController::class, 'ubahStatusKunjungan']);
    Route::post('/kunjungan/batalkan', [APIMobileController::class, 'batalkanStatusKunjungan']);
    Route::get('/kunjungan/riwayat/{pasien_id}', [APIMobileController::class, 'getRiwayatKunjungan']);

    Route::get('/getDataJadwalDokter', [APIMobileController::class, 'getDataJadwalDokter']);
    Route::get('/getDataKunjungan', [APIMobileController::class, 'getDataKunjungan']);
    Route::get('/getDataDokter', [APIMobileController::class, 'getDataDokter']);
    Route::get('/getDataSpesialisasiDokter', [APIMobileController::class, 'getDataSpesialisasiDokter']);
    Route::get('/getDataDokterSpesialisasi', [APIMobileController::class, 'getDataDokterSpesialisasi']);
});

// Route Untuk User Yang Login Sebagai Dokter 
Route::middleware(['auth:sanctum', 'role:Dokter'])->group(function () {
    Route::get('/get-data-dokter', [APIMobileController::class, 'getDataDokter']);
});

Route::post('/create-data-testimoni', [APIMobileController::class, 'createDataTestimomi']);

