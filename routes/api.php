<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\APIMobileController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (No Auth)
|--------------------------------------------------------------------------
*/

Route::post('/login', [APIMobileController::class, 'login'])->name('api.login');
Route::post('/register', [APIMobileController::class, 'register'])->name('api.register');
Route::post('/login-dokter', [APIMobileController::class, 'loginDokter'])->name('api.login_dokter');

Route::get('/getDataTestimoni', [APIMobileController::class, 'getDataTestimoni']);
Route::post('/create-data-testimoni', [APIMobileController::class, 'createDataTestimoni']);
Route::get('/getDataSpesialisasiDokter', [APIMobileController::class, 'getDataSpesialisasiDokter']);
Route::get('/getDokterBySpesialisasi/{spesialisasi_id}', [APIMobileController::class, 'getDokterBySpesialisasi']);

/* Throttled public endpoints (OTP/forgot) */
Route::middleware('throttle:6,1')->group(function () {
    Route::post('/forgot-password/send-otp', [APIMobileController::class, 'sendForgotPasswordOTP'])->name('forgot_password.send_otp');
    Route::post('/forgot-password/reset', [APIMobileController::class, 'resetPasswordWithOTP'])->name('forgot_password.reset');

    Route::post('/forgot-username/send-otp', [APIMobileController::class, 'sendForgotUsernameOTP'])->name('forgot_username.send_otp');
    Route::post('/forgot-username/verify-or-change', [APIMobileController::class, 'verifyOrChangeUsernameWithOTP'])->name('forgot_username.verify_or_change');

    // optional deprecated endpoint, tetap disediakan jika masih dipakai app lama
    Route::post('/forgot-username', [APIMobileController::class, 'sendForgotUsername'])->name('forgot_username.deprecated');
});

/* Midtrans callback (public, dipanggil server Midtrans) */
Route::post('/pembayaran/midtrans/callback', [APIMobileController::class, 'midtransCallback'])->name('midtrans.callback');

/* Katalog data publik */
Route::get('/getDataPoli', [APIMobileController::class, 'getPoliDokter'])->name('poli.data');
Route::get('/getPolibyIdDokter/{dokter_id}', [APIMobileController::class, 'getPolibyIdDokter'])->name('poli.by_dokter');
Route::get('/getAllDokter', [APIMobileController::class, 'getAllDokter'])->name('dokter.all');

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (Sanctum Auth)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Auth
    Route::post('/logout', [APIMobileController::class, 'logout'])->name('api.logout');

    /*
    |--------------------------------------------------------------------------
    | PENJUALAN OBAT ROUTES (Pindahkan ke dalam auth:sanctum)
    |--------------------------------------------------------------------------
    */
    Route::prefix('penjualan-obat')->name('penjualan_obat.')->group(function () {
        Route::get('/obat/list', [APIMobileController::class, 'getDaftarObat'])->name('obat.list');
        Route::get('/obat/all', [APIMobileController::class, 'getAllObat'])->name('obat.all');
        Route::post('/store', [APIMobileController::class, 'storePenjualanObat'])->name('store');
        Route::get('/riwayat/{pasienId}', [APIMobileController::class, 'getRiwayatPembelian'])->name('riwayat');
        Route::get('/detail/{kodeTransaksi}', [APIMobileController::class, 'getDetailTransaksi'])->name('detail');
        Route::get('/sales-summary', [APIMobileController::class, 'getSalesSummary'])->name('sales_summary');
        Route::put('/update-stok/{obatId}', [APIMobileController::class, 'updateStokObat'])->name('update_stok');
    });

    // Notifications (polling)
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/notifications/recent', [APIMobileController::class, 'getRecentNotifications']);
        Route::put('/notifications/{id}/read', [APIMobileController::class, 'markNotificationAsRead']);
    });
});

/*
|--------------------------------------------------------------------------
| PASIEN-ONLY ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:Pasien'])->group(function () {
    // Profil Pasien
    Route::get('/pasien/profile', [APIMobileController::class, 'getProfile'])->name('pasien.profile');
    Route::post('/pasien/update', [APIMobileController::class, 'updateProfile'])->name('pasien.update');

    // Data
    Route::get('/getJadwalDokter', [APIMobileController::class, 'getJadwalDokter'])->name('dokter.jadwal');
    Route::get('/getDataJadwalDokter', [APIMobileController::class, 'getDataJadwalDokter'])->name('dokter.jadwal.data');
    Route::get('/getDataKunjungan', [APIMobileController::class, 'getDataKunjungan'])->name('kunjungan.data');
    Route::get('/getDataDokter', [APIMobileController::class, 'getDataDokter'])->name('dokter.data');
    Route::get('/getDataDokterSpesialisasi', [APIMobileController::class, 'getDataDokterSpesialisasi'])->name('dokter.spesialisasi');

    // Kunjungan
    Route::post('/kunjungan/create', [APIMobileController::class, 'bookingDokter'])->name('kunjungan.create');
    Route::put('/kunjungan/{id}/status', [APIMobileController::class, 'ubahStatusKunjungan'])->name('kunjungan.ubah_status');
    Route::post('/kunjungan/batalkan', [APIMobileController::class, 'batalkanStatusKunjungan'])->name('kunjungan.batalkan');

    // Riwayat pasien
    Route::get('/kunjungan/riwayat/{pasien_id}', [APIMobileController::class, 'getRiwayatKunjungan'])->name('kunjungan.riwayat');
    Route::get('/pasien/riwayat-diagnosis/{pasien_id}', [APIMobileController::class, 'getRiwayatDiagnosisPasien'])->name('pasien.riwayat_diagnosis');

    // Pembayaran
    Route::prefix('pembayaran')->name('pembayaran.')->group(function () {
        Route::get('/list/{pasien_id}', [APIMobileController::class, 'getListPembayaran'])->name('list');
        Route::get('/pasien/{pasien_id}', [APIMobileController::class, 'getPembayaranPasien'])->name('pasien');
        Route::get('/detail/{kunjungan_id}', [APIMobileController::class, 'getPembayaranDetail'])->name('detail');
        Route::put('/update-status-obat/{id}', [APIMobileController::class, 'updateStatusObat'])->name('update_status_obat');
        Route::post('/proses', [APIMobileController::class, 'prosesPembayaran'])->name('proses');
        Route::get('/status/{order_id}', [APIMobileController::class, 'checkPaymentStatus'])->name('status');
        Route::get('/get-data-metode-pembayaran', [APIMobileController::class, 'getDataMetodePembayaran']);
    });
});

/*
|--------------------------------------------------------------------------
| DOKTER-ONLY (Sanctum + role:Dokter)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:Dokter'])
    ->prefix('dokter')
    ->name('dokter.')
    ->group(function () {
        Route::get('/get-data-dokter', [APIMobileController::class, 'getDataDokter']);
        Route::post('/update-profile', [APIMobileController::class, 'updateDataDokter'])->name('update_profile');
        Route::get('/get-data-kunjungan-by-id-dokter', [APIMobileController::class, 'getDataKunjunganBerdasarkanIdDokter'])->name('kunjungan_by_dokter');
        Route::get('/get-data-obat', [APIMobileController::class, 'getDataObat'])->name('obat');
        Route::get('/get-layanan', [APIMobileController::class, 'getLayanan'])->name('layanan');
        Route::post('/save-emr', [APIMobileController::class, 'saveEMR'])->name('save_emr');
        Route::get('/riwayat-pasien-diperiksa', [APIMobileController::class, 'getRiwayatPasienDiperiksa'])->name('riwayat_pasien_diperiksa');
        Route::get('/detail-riwayat-pasien/{kunjunganId}', [APIMobileController::class, 'getDetailRiwayatPasien'])->name('detail_riwayat_pasien');
        Route::get('/pasien/riwayat-emr/{pasien_id}', [APIMobileController::class, 'getRiwayatEMRPasien'])->name('riwayat_emr_pasien');        
        Route::get('/get-data-kunjungan/{kunjungan_id}', [APIMobileController::class, 'getDataKunjunganById'])->name('get_data_kunjungan_by_id');
        Route::get('/riwayat-diagnosis/{pasien_id}', [APIMobileController::class, 'getRiwayatDiagnosisDokter'])->name('riwayat_diagnosis_dokter');
        Route::get('/detail-kunjungan/{kunjungan_id}', [APIMobileController::class, 'getDataKunjungan'])->name('get_data_kunjungan');
        Route::get('/perawat', [APIMobileController::class, 'getPerawatByDokter'])->name('perawat.index');
    });
