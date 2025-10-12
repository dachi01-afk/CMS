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

// 🌐 PUBLIC ROUTES (tidak perlu login)
Route::get('/getDataTestimoni', [APIMobileController::class, 'getDataTestimoni']);
Route::post('/create-data-testimoni', [APIMobileController::class, 'createDataTestimoni']);
Route::get('/getDataSpesialisasiDokter', [APIMobileController::class, 'getDataSpesialisasiDokter']);
Route::get('/getDokterBySpesialisasi/{spesialisasi_id}', [APIMobileController::class, 'getDokterBySpesialisasi']);

// 🔥 ROUTES UNTUK FORGOT PASSWORD & USERNAME
Route::post('/forgot-password/send-otp', [APIMobileController::class, 'sendForgotPasswordOTP']);
Route::post('/forgot-password/reset', [APIMobileController::class, 'resetPasswordWithOTP']);
Route::post('/forgot-username', [APIMobileController::class, 'sendForgotUsername']);

// 🔒 PROTECTED ROUTES (butuh autentikasi dengan token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/pasien/profile', [APIMobileController::class, 'getProfile']);
    Route::post('/pasien/update', [APIMobileController::class, 'updateProfile']);
    Route::get('/getJadwalDokter', [APIMobileController::class, 'getJadwalDokter'])->name('getJadwalDokter');

    // Kunjungan routes
    Route::post('/kunjungan/create', [APIMobileController::class, 'bookingDokter']);
    Route::put('/kunjungan/ubah-status', [APIMobileController::class, 'ubahStatusKunjungan']);
    Route::post('/kunjungan/batalkan', [APIMobileController::class, 'batalkanStatusKunjungan']);
    Route::get('/kunjungan/riwayat/{pasien_id}', [APIMobileController::class, 'getRiwayatKunjungan']);

    Route::get('/getDataJadwalDokter', [APIMobileController::class, 'getDataJadwalDokter']);
    Route::get('/getDataKunjungan', [APIMobileController::class, 'getDataKunjungan']);
    Route::get('/getDataDokter', [APIMobileController::class, 'getDataDokter']);
    Route::get('/getDataDokterSpesialisasi', [APIMobileController::class, 'getDataDokterSpesialisasi']);
});

// Route Untuk User Yang Login Sebagai Dokter 
Route::middleware(['auth:sanctum', 'role:Dokter'])->group(function () {
    Route::prefix('dokter')->group(function () {
        Route::get('/get-data-dokter', [APIMobileController::class, 'getDataDokter']);
        Route::post('/update-profile', [APIMobileController::class, 'updateDataDokter']);
        Route::get('/get-data-kunjungan-by-id-dokter', [APIMobileController::class, 'getDataKunjunganBerdasarkanIdDokter']);
        Route::get('/get-data-obat', [APIMobileController::class, 'getDataObat']);
        Route::post('/save-emr', [APIMobileController::class, 'saveEMR']);         
        Route::get('/riwayat-pasien-diperiksa', [APIMobileController::class, 'getRiwayatPasienDiperiksa']);
        Route::get('/detail-riwayat-pasien/{kunjunganId}', [APIMobileController::class, 'getDetailRiwayatPasien']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('pembayaran')->group(function () {
        Route::get('/pasien/{pasien_id}', [APIMobileController::class, 'getPembayaranPasien']);
        Route::post('/update-status-obat', [APIMobileController::class, 'updateStatusObat']);
        Route::post('/proses', [APIMobileController::class, 'prosesPembayaran']);
    });
});