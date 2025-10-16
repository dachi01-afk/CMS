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

// 🔥 MIDTRANS CALLBACK - PUBLIC (dipanggil langsung oleh Midtrans server tanpa auth)
Route::post('/pembayaran/midtrans/callback', [APIMobileController::class, 'midtransCallback']);

// 🔒 PROTECTED ROUTES (butuh autentikasi dengan token)
Route::middleware(['auth:sanctum', 'role:Pasien'])->group(function () {
    Route::get('/pasien/profile', [APIMobileController::class, 'getProfile']);
    Route::post('/pasien/update', [APIMobileController::class, 'updateProfile']);
    Route::get('/getJadwalDokter', [APIMobileController::class, 'getJadwalDokter'])->name('getJadwalDokter');

    // Kunjungan routes
    Route::post('/kunjungan/create', [APIMobileController::class, 'bookingDokter']);
    Route::put('/kunjungan/ubah-status', [APIMobileController::class, 'ubahStatusKunjungan']);
    Route::post('/kunjungan/batalkan', [APIMobileController::class, 'batalkanStatusKunjungan']);

    Route::get('/getDataJadwalDokter', [APIMobileController::class, 'getDataJadwalDokter']);
    Route::get('/getDataKunjungan', [APIMobileController::class, 'getDataKunjungan']);
    Route::get('/getDataDokter', [APIMobileController::class, 'getDataDokter']);
    Route::get('/getDataDokterSpesialisasi', [APIMobileController::class, 'getDataDokterSpesialisasi']);
});

// Route Untuk User Yang Login Sebagai Dokter 
// Route Untuk User Yang Login Sebagai Dokter 
Route::middleware(['auth:sanctum', 'role:Dokter'])->group(function () {
    Route::prefix('dokter')->group(function () {
        Route::get('/get-data-dokter', [APIMobileController::class, 'getDataDokter']);
        Route::post('/update-profile', [APIMobileController::class, 'updateDataDokter']);
        Route::get('/get-data-kunjungan-by-id-dokter', [APIMobileController::class, 'getDataKunjunganBerdasarkanIdDokter']);
        Route::get('/get-data-obat', [APIMobileController::class, 'getDataObat']);
        
        // ADD THIS LINE:
        Route::get('/get-layanan/{poli_id}', [APIMobileController::class, 'getLayananByPoli']);
        
        Route::post('/save-emr', [APIMobileController::class, 'saveEMR']);         
        Route::get('/riwayat-pasien-diperiksa', [APIMobileController::class, 'getRiwayatPasienDiperiksa']);
        Route::get('/detail-riwayat-pasien/{kunjunganId}', [APIMobileController::class, 'getDetailRiwayatPasien']);

    });
});

// 🔥 PEMBAYARAN ROUTES - CORRECTED AND ENHANCED
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('pembayaran')->group(function () {
        // Route yang sudah ada
        Route::get('/pasien/{pasien_id}', [APIMobileController::class, 'getPembayaranPasien']);
        
        // 🔥 TAMBAHKAN ROUTE INI untuk detail individual
        Route::get('/detail/{kunjungan_id}', [APIMobileController::class, 'getDetailPembayaran']);
        
        // Route lainnya...
        Route::post('/update-status-obat', [APIMobileController::class, 'updateStatusObat']);
        Route::post('/proses', [APIMobileController::class, 'prosesPembayaran']);
        Route::post('/midtrans/create', [APIMobileController::class, 'createMidtransTransaction']);
        Route::get('/status/{order_id}', [APIMobileController::class, 'checkPaymentStatus']);
        Route::post('/force-update', [APIMobileController::class, 'forceUpdatePaymentStatus']);
    });
});

Route::get('/getDataPoli', [APIMobileController::class, 'getPoliDokter']);
Route::get('/getPolibyIdDokter/{dokter_id}', [APIMobileController::class, 'getPolibyIdDokter']);
Route::get('/getAllDokter', [APIMobileController::class, 'getAllDokter']);
Route::get('/kunjungan/riwayat/{pasien_id}', [APIMobileController::class, 'getRiwayatKunjungan']);
Route::get('/pasien/riwayat-diagnosis/{pasien_id}', [APIMobileController::class, 'getRiwayatDiagnosisPasien']);

