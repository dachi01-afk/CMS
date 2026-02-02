<?php

use App\Http\Controllers\Api\APIMobileController;
use App\Http\Controllers\Api\Dokter\ResumeDokterController;
use App\Http\Controllers\Api\LihatPemeriksaanOlehPerawat;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (No Auth Required)
|--------------------------------------------------------------------------
*/

// Test endpoint
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API OK dari Laravel',
        'timestamp' => now()->toISOString(),
    ]);
});

// Authentication routes
Route::post('/login', [APIMobileController::class, 'login'])->name('api.login');
Route::post('/register', [APIMobileController::class, 'register'])->name('api.register');
Route::post('/login-dokter', [APIMobileController::class, 'loginDokter'])->name('api.login_dokter');

// Forgot password/username (with throttling)
Route::middleware('throttle:6,1')->group(function () {
    Route::post('/forgot-password/send-otp', [APIMobileController::class, 'sendForgotPasswordOTP'])->name('forgot_password.send_otp');
    Route::post('/forgot-password/reset', [APIMobileController::class, 'resetPasswordWithOTP'])->name('forgot_password.reset');
    Route::post('/forgot-username/send-otp', [APIMobileController::class, 'sendForgotUsernameOTP'])->name('forgot_username.send_otp');
    Route::post('/forgot-username/verify-or-change', [APIMobileController::class, 'verifyOrChangeUsernameWithOTP'])->name('forgot_username.verify_or_change');
});

// Public data routes
Route::get('/getDataSpesialisasiDokter', [APIMobileController::class, 'getDataSpesialisasiDokter']);
Route::get('/getDokterBySpesialisasi/{spesialisasi_id}', [APIMobileController::class, 'getDokterBySpesialisasi']);
Route::get('/getJadwalDokter', [APIMobileController::class, 'getJadwalDokter'])->name('dokter.jadwal.public');
Route::get('/getDataPoli', [APIMobileController::class, 'getPoliDokter'])->name('poli.data');
Route::get('/getPolibyIdDokter/{dokter_id}', [APIMobileController::class, 'getPolibyIdDokter'])->name('poli.by_dokter');
Route::get('/getAllDokter', [APIMobileController::class, 'getAllDokter'])->name('dokter.all');
Route::get('/pembayaran/get-data-metode-pembayaran', [APIMobileController::class, 'getDataMetodePembayaran']);

// Public layanan routes
Route::get('/layanan-publik', [APIMobileController::class, 'getLayananPublik'])->name('layanan.publik');
Route::get('/layanan-publik/{id}', [APIMobileController::class, 'getDetailLayananPublik'])->name('layanan.publik.detail');
Route::get('/kategori-layanan-publik', [APIMobileController::class, 'getKategoriLayananPublik'])->name('kategori_layanan.publik');

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // Logout
    Route::post('/logout', [APIMobileController::class, 'logout'])->name('api.logout');

    // Penjualan obat
    Route::prefix('penjualan-obat')->name('penjualan_obat.')->group(function () {
        Route::get('/obat/list', [APIMobileController::class, 'getDaftarObat'])->name('obat.list');
        Route::get('/obat/all', [APIMobileController::class, 'getAllObat'])->name('obat.all');
        Route::post('/store', [APIMobileController::class, 'storePenjualanObat'])->name('store');
        Route::get('/riwayat/{pasienId}', [APIMobileController::class, 'getRiwayatPembelian'])->name('riwayat');
        Route::get('/detail/{kodeTransaksi}', [APIMobileController::class, 'getDetailTransaksi'])->name('detail');
        Route::get('/sales-summary', [APIMobileController::class, 'getSalesSummary'])->name('sales_summary');
        Route::put('/update-stok/{obatId}', [APIMobileController::class, 'updateStokObat'])->name('update_stok');
    });
});

/*
|--------------------------------------------------------------------------
| PASIEN-ONLY ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:Pasien'])->prefix('pasien')->name('pasien.')->group(function () {
    // Profile
    Route::get('/profile', [APIMobileController::class, 'getProfile'])->name('profile');
    Route::post('/update', [APIMobileController::class, 'updateProfile'])->name('update');

    // Vital signs & EMR
    Route::get('/vital-terbaru', [APIMobileController::class, 'getLatestVitalPasien'])->name('vital_terbaru');
    Route::get('/vital-history', [APIMobileController::class, 'getVitalHistoryPasien'])->name('vital_history');
    Route::get('/riwayat-diagnosis/{pasien_id}', [APIMobileController::class, 'getRiwayatDiagnosisPasien'])->name('riwayat_diagnosis');
    Route::get('/hasil-lab/kunjungan/{kunjungan_id}', [APIMobileController::class, 'getHasilLabByKunjungan'])->name('hasil_lab.by_kunjungan');

    Route::prefix('notifikasi')->name('notifikasi.')->group(function () {
        Route::get('/', [APIMobileController::class, 'getNotifikasiPasien'])->name('list');
        Route::patch('/{id}/read', [APIMobileController::class, 'markNotifikasiAsRead'])->name('mark_read');
    });
    // Dokter & jadwal
    Route::get('/data-dokter', [APIMobileController::class, 'getDataDokter'])->name('dokter.data');
    Route::get('/dokter-by-poli-jadwal', [APIMobileController::class, 'getDokterByPoliJadwal'])->name('dokter.by_poli_jadwal');
    Route::get('/jadwal-dokter', [APIMobileController::class, 'getJadwalDokterByDokterPoliTanggal'])->name('dokter.jadwal_by_dokter_poli_tanggal');
    /*
    |--------------------------------------------------------------------------
    | ORDER LAB (PASIEN)
    |--------------------------------------------------------------------------
    */
    Route::prefix('order-lab')->name('order_lab.')->group(function () {
        // list order lab milik pasien login
        Route::get('/', [APIMobileController::class, 'pasienListOrderLab'])->name('list');

        // detail 1 order lab + detail pemeriksaan + hasil
        Route::get('/{orderLabId}', [APIMobileController::class, 'pasienDetailOrderLab'])->name('detail');
    });

    // Kunjungan (legacy)
    Route::prefix('kunjungan')->name('kunjungan.')->group(function () {
        Route::post('/create', [APIMobileController::class, 'bookingDokter'])->name('create');
        Route::put('/{id}/status', [APIMobileController::class, 'ubahStatusKunjungan'])->name('ubah_status');
        Route::post('/batalkan', [APIMobileController::class, 'batalkanStatusKunjungan'])->name('batalkan');
        Route::get('/riwayat/{pasien_id}', [APIMobileController::class, 'getRiwayatKunjungan'])->name('riwayat');
    });

    // Order layanan (new system)
    Route::prefix('order-layanan')->name('order_layanan.')->group(function () {
        Route::post('/', [APIMobileController::class, 'orderLayananPasienMobile'])->name('create');
        Route::get('/riwayat', [APIMobileController::class, 'getRiwayatOrderLayanan'])->name('riwayat');
        Route::get('/{orderId}', [APIMobileController::class, 'getDetailOrderLayananPasien'])->name('detail');
        Route::put('/{orderId}/batalkan', [APIMobileController::class, 'batalkanOrderLayanan'])->name('batalkan');
    });

    // Layanan & kategori
    Route::get('/layanan', [APIMobileController::class, 'getLayananPasien'])->name('layanan.list');
    Route::get('/layanan/{id}', [APIMobileController::class, 'getDetailLayananPasien'])->name('layanan.detail');
    Route::get('/kategori-layanan', [APIMobileController::class, 'getKategoriLayanan'])->name('kategori_layanan');
    Route::get('/riwayat-pembelian-layanan', [APIMobileController::class, 'getRiwayatPembelianLayanan'])->name('riwayat_pembelian_layanan');
    Route::get('/poli', [APIMobileController::class, 'getDataPoli']);

    // Pembayaran
    Route::prefix('pembayaran')->name('pembayaran.')->group(function () {
        Route::get('/list/{pasien_id}', [APIMobileController::class, 'getListPembayaran'])->name('list');
        Route::get('/pasien/{pasien_id}', [APIMobileController::class, 'getPembayaranPasien'])->name('pasien');
        Route::get('/detail/{kunjungan_id}', [APIMobileController::class, 'getPembayaranDetail'])->name('detail');
        Route::put('/update-status-obat/{id}', [APIMobileController::class, 'updateStatusObat'])->name('update_status_obat');
        Route::post('/proses', [APIMobileController::class, 'prosesPembayaran'])->name('proses');
        Route::get('/status/{order_id}', [APIMobileController::class, 'checkPaymentStatus'])->name('status');
        Route::get('/metode-pembayaran', [APIMobileController::class, 'getDataMetodePembayaran'])->name('metode');
    });

    // Resume dokter
    Route::prefix('resume-dokter')->name('resume_dokter.')->group(function () {
        Route::get('/', [APIMobileController::class, 'getResumeDokterPasien'])->name('list');
        Route::get('/{id}', [APIMobileController::class, 'getDetailResumeDokterPasien'])->name('detail');
    });
    Route::prefix('testimoni')->name('testimoni.')->group(function () {
        // list testimoni (bisa semua / bisa juga kamu pakai buat halaman Testimoni)
        Route::get('/', [APIMobileController::class, 'pasienListTestimoni'])->name('list');

        // detail 1 testimoni
        Route::get('/{id}', [APIMobileController::class, 'pasienDetailTestimoni'])->name('detail');

        // pasien buat testimoni
        Route::post('/', [APIMobileController::class, 'pasienCreateTestimoni'])->name('create');

        // pasien update testimoni miliknya
        Route::post('/{id}', [APIMobileController::class, 'pasienUpdateTestimoni'])->name('update');
        // (pakai POST biar gampang dari Flutter, kalau mau REST bener: PUT)

        // pasien hapus testimoni miliknya
        Route::delete('/{id}', [APIMobileController::class, 'pasienDeleteTestimoni'])->name('delete');
    });
});

/*
|--------------------------------------------------------------------------
| DOKTER-ONLY ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:Dokter'])->prefix('dokter')->name('dokter.')->group(function () {
    // Profile & data dokter
    Route::get('/get-data-dokter', [APIMobileController::class, 'getDataDokter']);
    Route::post('/update-profile', [APIMobileController::class, 'updateDataDokter'])->name('update_profile');

    // Kunjungan & pasien
    Route::get('/get-data-kunjungan-by-id-dokter', [APIMobileController::class, 'getDataKunjunganBerdasarkanIdDokter'])->name('kunjungan_by_dokter');
    Route::get('/riwayat-pasien-diperiksa', [APIMobileController::class, 'getRiwayatPasienDiperiksa'])->name('riwayat_pasien_diperiksa');
    Route::get('/detail-riwayat-pasien/{kunjunganId}', [APIMobileController::class, 'getDetailRiwayatPasien'])->name('detail_riwayat_pasien');
    Route::get('/get-data-kunjungan/{kunjungan_id}', [APIMobileController::class, 'getDataKunjunganById'])->name('get_data_kunjungan_by_id');

    // Data master
    Route::get('/get-data-obat', [APIMobileController::class, 'getDataObat'])->name('obat');
    Route::get('/get-layanan', [APIMobileController::class, 'getLayanan'])->name('layanan');
    Route::get('/perawat', [APIMobileController::class, 'getPerawatByDokter'])->name('perawat.index');

    // EMR management
    Route::post('/save-emr', [APIMobileController::class, 'saveEMR'])->name('save_emr');
    Route::put('/edit-emr/{emr_id}', [APIMobileController::class, 'editEMR'])->name('edit_emr');
    Route::get('/emr/{emr_id}', [APIMobileController::class, 'getEmrById']);
    Route::post('/save-emr-layanan', [APIMobileController::class, 'saveEMRLayanan']);
    Route::get('/pasien/riwayat-emr/{pasien_id}', [APIMobileController::class, 'getRiwayatEMRPasien'])->name('riwayat_emr_pasien');

    // Layanan order
    Route::get('/layanan-order', [APIMobileController::class, 'getLayananOrderDokter']);
    Route::get('/detail-order-layanan/{kunjunganId}', [APIMobileController::class, 'getDetailOrderLayanan']);

    // Resume dokter (IMPORTANT: specific routes BEFORE general routes)
    Route::post('/emr/{emrId}/resume/finalize', [ResumeDokterController::class, 'finalize'])->name('resume.finalize');
    Route::get('/emr/{emrId}/resume', [ResumeDokterController::class, 'show'])->name('resume.show');
    Route::post('/emr/{emrId}/resume', [ResumeDokterController::class, 'store'])->name('resume.store');

    // Lab orders
    Route::put('/order-lab/{orderLabId}/schedule', [APIMobileController::class, 'updateJadwalOrderLab'])->name('order_lab.update_schedule');

    Route::get('/order-lab', [APIMobileController::class, 'dokterListOrderLab'])->name('order_lab.index');
    Route::get('/master-lab', [APIMobileController::class, 'dokterMasterLab'])->name('master_lab.index');
    Route::post('/create-order-lab', [APIMobileController::class, 'dokterCreateOrderLab'])->name('order_lab.create');
    Route::get('/order-lab/{orderLabId}', [APIMobileController::class, 'dokterDetailOrderLab'])->name('order_lab.detail');
    Route::get('/riwayat-lab/pasien/{pasien_id}', [APIMobileController::class, 'dokterRiwayatLabPasien'])->name('riwayat_lab.pasien');
    Route::get('/riwayat-lab/detail/{order_lab_id}', [APIMobileController::class, 'dokterDetailRiwayatLab'])->name('riwayat_lab.detail');
    Route::post('/hasil-lab', [APIMobileController::class, 'dokterCreateHasilLab'])->name('hasil_lab.store');
    Route::prefix('order-radiologi')->name('order_radiologi.')->group(function () {
        // Get master jenis pemeriksaan radiologi
        Route::get('/master', [APIMobileController::class, 'dokterMasterRadiologi']);

        // List order radiologi
        Route::get('/', [APIMobileController::class, 'dokterListOrderRadiologi']);

        // Create order radiologi
        Route::post('/', [APIMobileController::class, 'dokterCreateOrderRadiologi']);
        Route::put('/{id}/schedule', [APIMobileController::class, 'updateJadwalOrderRadiologi'])->name('update_schedule');

        // Detail order radiologi
        Route::get('/{id}', [APIMobileController::class, 'dokterDetailOrderRadiologi']);
    });
});

/*
|--------------------------------------------------------------------------
| PERAWAT-ONLY ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:Perawat'])->prefix('perawat')->group(function () {
    Route::get('/kunjungan-tugas', [LihatPemeriksaanOlehPerawat::class, 'getKunjunganTugasPerawat']);
    Route::get('/kunjungan-sudah-vital', [LihatPemeriksaanOlehPerawat::class, 'getKunjunganSudahDiisiVitalPerawat']);
});
Route::post('/test/kirim-notifikasi-simple', [APIMobileController::class, 'testKirimNotifikasiSimple'])
    ->middleware('auth:sanctum');
