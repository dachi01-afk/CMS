<?php

use App\Http\Controllers\Api\APIMobileController;
use App\Http\Controllers\Api\Dokter\ResumeDokterController;
use App\Http\Controllers\Api\LihatPemeriksaanOlehPerawat;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (No Auth)
|--------------------------------------------------------------------------
*/
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API OK dari Laravel',
    ]);
});
Route::post('/login', [APIMobileController::class, 'login'])->name('api.login');
Route::post('/register', [APIMobileController::class, 'register'])->name('api.register');
Route::post('/login-dokter', [APIMobileController::class, 'loginDokter'])->name('api.login_dokter');

Route::get('/getDataTestimoni', [APIMobileController::class, 'getDataTestimoni']);
Route::post('/create-data-testimoni', [APIMobileController::class, 'createDataTestimoni']);
Route::get('/getDataSpesialisasiDokter', [APIMobileController::class, 'getDataSpesialisasiDokter']);
Route::get('/getDokterBySpesialisasi/{spesialisasi_id}', [APIMobileController::class, 'getDokterBySpesialisasi']);

// ✅ JADWAL DOKTER - PUBLIC (untuk kalender sidebar)
Route::get('/getJadwalDokter', [APIMobileController::class, 'getJadwalDokter'])->name('dokter.jadwal.public');

/* Throttled public endpoints (OTP/forgot) */
Route::middleware('throttle:6,1')->group(function () {
    Route::post('/forgot-password/send-otp', [APIMobileController::class, 'sendForgotPasswordOTP'])->name('forgot_password.send_otp');
    Route::post('/forgot-password/reset', [APIMobileController::class, 'resetPasswordWithOTP'])->name('forgot_password.reset');

    Route::post('/forgot-username/send-otp', [APIMobileController::class, 'sendForgotUsernameOTP'])->name('forgot_username.send_otp');
    Route::post('/forgot-username/verify-or-change', [APIMobileController::class, 'verifyOrChangeUsernameWithOTP'])->name('forgot_username.verify_or_change');

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
    Route::post('/logout', [APIMobileController::class, 'logout'])->name('api.logout');

    Route::prefix('penjualan-obat')->name('penjualan_obat.')->group(function () {
        Route::get('/obat/list', [APIMobileController::class, 'getDaftarObat'])->name('obat.list');
        Route::get('/obat/all', [APIMobileController::class, 'getAllObat'])->name('obat.all');
        Route::post('/store', [APIMobileController::class, 'storePenjualanObat'])->name('store');
        Route::get('/riwayat/{pasienId}', [APIMobileController::class, 'getRiwayatPembelian'])->name('riwayat');
        Route::get('/detail/{kodeTransaksi}', [APIMobileController::class, 'getDetailTransaksi'])->name('detail');
        Route::get('/sales-summary', [APIMobileController::class, 'getSalesSummary'])->name('sales_summary');
        Route::put('/update-stok/{obatId}', [APIMobileController::class, 'updateStokObat'])->name('update_stok');
    });

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
Route::middleware(['auth:sanctum', 'role:Pasien'])->prefix('pasien')->name('pasien.')->group(function () {

    // ========================================
    // PROFIL & AKUN
    // ========================================
    Route::get('/profile', [APIMobileController::class, 'getProfile'])->name('profile');
    Route::post('/update', [APIMobileController::class, 'updateProfile'])->name('update');

    // ========================================
    // VITAL SIGN & EMR
    // ========================================
    Route::get('/vital-terbaru', [APIMobileController::class, 'getLatestVitalPasien'])->name('vital_terbaru');
    Route::get('/vital-history', [APIMobileController::class, 'getVitalHistoryPasien'])->name('vital_history');
    Route::get('/riwayat-diagnosis/{pasien_id}', [APIMobileController::class, 'getRiwayatDiagnosisPasien'])->name('riwayat_diagnosis');

    // ========================================
    // DOKTER & JADWAL
    // ========================================
    Route::get('/data-dokter', [APIMobileController::class, 'getDataDokter'])->name('dokter.data');
    Route::get('/dokter-by-poli-jadwal', [APIMobileController::class, 'getDokterByPoliJadwal'])->name('dokter.by_poli_jadwal');

    // ========================================
    // KUNJUNGAN (OLD SYSTEM)
    // ========================================
    Route::prefix('kunjungan')->name('kunjungan.')->group(function () {
        Route::post('/create', [APIMobileController::class, 'bookingDokter'])->name('create');
        Route::put('/{id}/status', [APIMobileController::class, 'ubahStatusKunjungan'])->name('ubah_status');
        Route::post('/batalkan', [APIMobileController::class, 'batalkanStatusKunjungan'])->name('batalkan');
        Route::get('/riwayat/{pasien_id}', [APIMobileController::class, 'getRiwayatKunjungan'])->name('riwayat');
    });

    // ========================================
    // ORDER LAYANAN (NEW SYSTEM) ✅
    // ========================================
    Route::prefix('order-layanan')->name('order_layanan.')->group(function () {
        // Create order baru
        Route::post('/', [APIMobileController::class, 'orderLayananPasienMobile'])->name('create');

        // Riwayat order
        Route::get('/riwayat', [APIMobileController::class, 'getRiwayatOrderLayanan'])->name('riwayat');

        // Detail order by ID
        Route::get('/{orderId}', [APIMobileController::class, 'getDetailOrderLayananPasien'])->name('detail');

        // Batalkan order
        Route::put('/{orderId}/batalkan', [APIMobileController::class, 'batalkanOrderLayanan'])->name('batalkan');
    });

    // ========================================
    // LAYANAN & KATEGORI
    // ========================================
    Route::get('/layanan', [APIMobileController::class, 'getLayananPasien'])->name('layanan.list');
    Route::get('/layanan/{id}', [APIMobileController::class, 'getDetailLayananPasien'])->name('layanan.detail');
    Route::get('/kategori-layanan', [APIMobileController::class, 'getKategoriLayanan'])->name('kategori_layanan');

    // Riwayat pembelian layanan (legacy)
    Route::get('/riwayat-pembelian-layanan', [APIMobileController::class, 'getRiwayatPembelianLayanan'])->name('riwayat_pembelian_layanan');

    // ========================================
    // PEMBAYARAN
    // ========================================
    Route::prefix('pembayaran')->name('pembayaran.')->group(function () {
        // List pembayaran pasien
        Route::get('/list/{pasien_id}', [APIMobileController::class, 'getListPembayaran'])->name('list');

        // Pembayaran by pasien
        Route::get('/pasien/{pasien_id}', [APIMobileController::class, 'getPembayaranPasien'])->name('pasien');

        // Detail pembayaran by kunjungan
        Route::get('/detail/{kunjungan_id}', [APIMobileController::class, 'getPembayaranDetail'])->name('detail');

        // Update status obat
        Route::put('/update-status-obat/{id}', [APIMobileController::class, 'updateStatusObat'])->name('update_status_obat');

        // Proses pembayaran
        Route::post('/proses', [APIMobileController::class, 'prosesPembayaran'])->name('proses');

        // Cek status pembayaran
        Route::get('/status/{order_id}', [APIMobileController::class, 'checkPaymentStatus'])->name('status');

        // Metode pembayaran
        Route::get('/metode-pembayaran', [APIMobileController::class, 'getDataMetodePembayaran'])->name('metode');
    });

    // ========================================
    // RESUME DOKTER
    // ========================================
    Route::prefix('resume-dokter')->name('resume_dokter.')->group(function () {
        // List resume
        Route::get('/', [APIMobileController::class, 'getResumeDokterPasien'])->name('list');

        // Detail resume by ID
        Route::get('/{id}', [APIMobileController::class, 'getDetailResumeDokterPasien'])->name('detail');
    });
});

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES - Yang Bisa Diakses Tanpa Auth
|--------------------------------------------------------------------------
*/

// Katalog Poli & Dokter
Route::get('/getDataPoli', [APIMobileController::class, 'getPoliDokter'])->name('poli.data');
Route::get('/getPolibyIdDokter/{dokter_id}', [APIMobileController::class, 'getPolibyIdDokter'])->name('poli.by_dokter');
Route::get('/getAllDokter', [APIMobileController::class, 'getAllDokter'])->name('dokter.all');

// Jadwal dokter (public - untuk sidebar kalender)
Route::get('/getJadwalDokter', [APIMobileController::class, 'getJadwalDokter'])->name('dokter.jadwal.public');

// Spesialisasi & Testimoni
Route::get('/getDataSpesialisasiDokter', [APIMobileController::class, 'getDataSpesialisasiDokter']);
Route::get('/getDokterBySpesialisasi/{spesialisasi_id}', [APIMobileController::class, 'getDokterBySpesialisasi']);
Route::get('/getDataTestimoni', [APIMobileController::class, 'getDataTestimoni']);
Route::post('/create-data-testimoni', [APIMobileController::class, 'createDataTestimoni']);

// Metode pembayaran (public)
Route::get('/pembayaran/get-data-metode-pembayaran', [APIMobileController::class, 'getDataMetodePembayaran']);

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

Route::post('/login', [APIMobileController::class, 'login'])->name('api.login');
Route::post('/register', [APIMobileController::class, 'register'])->name('api.register');
Route::post('/login-dokter', [APIMobileController::class, 'loginDokter'])->name('api.login_dokter');
Route::post('/logout', [APIMobileController::class, 'logout'])->middleware('auth:sanctum')->name('api.logout');

// Forgot Password/Username (throttled)
Route::middleware('throttle:6,1')->group(function () {
    Route::post('/forgot-password/send-otp', [APIMobileController::class, 'sendForgotPasswordOTP'])->name('forgot_password.send_otp');
    Route::post('/forgot-password/reset', [APIMobileController::class, 'resetPasswordWithOTP'])->name('forgot_password.reset');
    Route::post('/forgot-username/send-otp', [APIMobileController::class, 'sendForgotUsernameOTP'])->name('forgot_username.send_otp');
    Route::post('/forgot-username/verify-or-change', [APIMobileController::class, 'verifyOrChangeUsernameWithOTP'])->name('forgot_username.verify_or_change');
});

/*
|--------------------------------------------------------------------------
| PENJUALAN OBAT (Authenticated)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->prefix('penjualan-obat')->name('penjualan_obat.')->group(function () {
    Route::get('/obat/list', [APIMobileController::class, 'getDaftarObat'])->name('obat.list');
    Route::get('/obat/all', [APIMobileController::class, 'getAllObat'])->name('obat.all');
    Route::post('/store', [APIMobileController::class, 'storePenjualanObat'])->name('store');
    Route::get('/riwayat/{pasienId}', [APIMobileController::class, 'getRiwayatPembelian'])->name('riwayat');
    Route::get('/detail/{kodeTransaksi}', [APIMobileController::class, 'getDetailTransaksi'])->name('detail');
    Route::get('/sales-summary', [APIMobileController::class, 'getSalesSummary'])->name('sales_summary');
    Route::put('/update-stok/{obatId}', [APIMobileController::class, 'updateStokObat'])->name('update_stok');
});

/*
|--------------------------------------------------------------------------
| NOTIFICATIONS (Authenticated)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/notifications/recent', [APIMobileController::class, 'getRecentNotifications']);
    Route::put('/notifications/{id}/read', [APIMobileController::class, 'markNotificationAsRead']);
});

/*
|--------------------------------------------------------------------------
| MIDTRANS CALLBACK (Public - Called by Midtrans Server)
|--------------------------------------------------------------------------
*/

Route::post('/pembayaran/midtrans/callback', [APIMobileController::class, 'midtransCallback'])->name('midtrans.callback');

/*
|--------------------------------------------------------------------------
| TEST ENDPOINT
|--------------------------------------------------------------------------
*/

Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API OK dari Laravel',
        'timestamp' => now()->toISOString(),
    ]);
});
Route::get(
    '/pembayaran/get-data-metode-pembayaran',
    [APIMobileController::class, 'getDataMetodePembayaran']
);

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

        Route::get('/layanan-order', [APIMobileController::class, 'getLayananOrderDokter']);
        Route::get('/detail-order-layanan/{kunjunganId}', [APIMobileController::class, 'getDetailOrderLayanan']);

        Route::get('/pasien/riwayat-emr/{pasien_id}', [APIMobileController::class, 'getRiwayatEMRPasien'])->name('riwayat_emr_pasien');
        Route::get('/get-data-kunjungan/{kunjungan_id}', [APIMobileController::class, 'getDataKunjunganById'])->name('get_data_kunjungan_by_id');
        Route::get('/perawat', [APIMobileController::class, 'getPerawatByDokter'])->name('perawat.index');
        Route::get('/emr/{emr_id}', [APIMobileController::class, 'getEmrById']);
        Route::post('/save-emr-layanan', [APIMobileController::class, 'saveEMRLayanan']);
        Route::get('/emr/{emrId}/resume', [ResumeDokterController::class, 'show']);
        Route::post('/emr/{emrId}/resume', [ResumeDokterController::class, 'store']);
        Route::post('/emr/{emrId}/resume/finalize', [ResumeDokterController::class, 'finalize']);
        Route::put('/edit-emr/{emr_id}', [APIMobileController::class, 'editEMR'])->name('edit_emr');

    });

Route::middleware(['auth:sanctum', 'role:Perawat'])
    ->prefix('perawat')
    ->group(function () {
        Route::get('/kunjungan-tugas', [LihatPemeriksaanOlehPerawat::class, 'getKunjunganTugasPerawat']);
        Route::get('/kunjungan-sudah-vital', [LihatPemeriksaanOlehPerawat::class, 'getKunjunganSudahDiisiVitalPerawat']);
    });
