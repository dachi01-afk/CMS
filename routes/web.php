<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DataMedisPasienController;
use App\Http\Controllers\Admin\JadwalKunjunganController;
use App\Http\Controllers\Admin\KategoriLayananController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\LayananController;
use App\Http\Controllers\Admin\ManagementPerawatController;
use App\Http\Controllers\Admin\ManajemenPenggunaController;
use App\Http\Controllers\Admin\OrderLayananController;
use App\Http\Controllers\Admin\PasienHariIniController as AdminPasienHariIniController;
use App\Http\Controllers\Admin\PengaturanKlinikController;
use App\Http\Controllers\Admin\PoliController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Api\APIWebController;
use App\Http\Controllers\Apoteker\Obat\PenjualanObatController;
use App\Http\Controllers\ApproveDiskonPenjualanObatController;
use App\Http\Controllers\Dokter\AuthController;
use App\Http\Controllers\Dokter\DokterController as DokterDokterController;
use App\Http\Controllers\Farmasi\BahanHabisPakaiController;
use App\Http\Controllers\Farmasi\BrandFarmasiController;
use App\Http\Controllers\Farmasi\CetakResepObatController;
use App\Http\Controllers\Farmasi\DepotController;
use App\Http\Controllers\Farmasi\FarmasiController;
use App\Http\Controllers\Farmasi\HutangBahanHabisPakaiController;
use App\Http\Controllers\Farmasi\JenisObatController;
use App\Http\Controllers\Farmasi\KadaluarsaBHPController;
use App\Http\Controllers\Farmasi\KadaluarsaObatController;
use App\Http\Controllers\Farmasi\KategoriObatController;
use App\Http\Controllers\Farmasi\ObatController;
use App\Http\Controllers\Farmasi\OrderObatController;
use App\Http\Controllers\Farmasi\PengambilanObatController as FarmasiPengambilanObatController;
use App\Http\Controllers\Farmasi\PenggunaanBHPController;
use App\Http\Controllers\Farmasi\PenggunaanObatController;
use App\Http\Controllers\Farmasi\PesananDanStokMasuk;
use App\Http\Controllers\Farmasi\RestockBahanHabisPakaiController;
use App\Http\Controllers\Farmasi\RestockObatController;
use App\Http\Controllers\Farmasi\ReturnBahanHabisPakaiController;
use App\Http\Controllers\Farmasi\ReturnObatController;
use App\Http\Controllers\Farmasi\SatuanObatController;
use App\Http\Controllers\Farmasi\StokMasukBahanHabisPakaiController;
use App\Http\Controllers\Farmasi\StokMasukObatController;
use App\Http\Controllers\Farmasi\StokObatController;
use App\Http\Controllers\Farmasi\SupplierController;
use App\Http\Controllers\Farmasi\TipeDepotController;
use App\Http\Controllers\JenisSpesialisController;
use App\Http\Controllers\Kasir\DiskonApprovalController;
use App\Http\Controllers\Kasir\HutangController;
use App\Http\Controllers\Kasir\KasirController;
use App\Http\Controllers\Kasir\MetodePembayaranController;
use App\Http\Controllers\Kasir\PiutangBahanHabisPakaiController;
use App\Http\Controllers\Kasir\PiutangObatController;
use App\Http\Controllers\Kasir\RiwayatTransaksiController;
use App\Http\Controllers\Kasir\TransaksiInsightController as KasirTransaksiInsightController;
use App\Http\Controllers\Kasir\TransaksiLayananController;
use App\Http\Controllers\Kasir\TransaksiObatController;
use App\Http\Controllers\Management\AdminController;
use App\Http\Controllers\Management\DokterController;
use App\Http\Controllers\Management\JadwalDokterController;
use App\Http\Controllers\Management\PasienController;
use App\Http\Controllers\Management\UserController;
use App\Http\Controllers\PemakaianBahanHabisPakaiController;
use App\Http\Controllers\Perawat\KunjunganController;
use App\Http\Controllers\Perawat\OrderLabController;
use App\Http\Controllers\Perawat\OrderRadiologiController;
use App\Http\Controllers\Perawat\PerawatController;
use App\Http\Controllers\Perawat\RiwayatPemeriksaanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\SuperAdmin\ApproveDiskonOrderLayananController;
use App\Http\Controllers\SuperAdmin\ApproveDiskonOrderLayananManagerController;
use App\Http\Controllers\SuperAdmin\ApproveDiskonPenjualanObatManagerController;
use App\Http\Controllers\SuperAdmin\DiskonApprovalManagerController;
use App\Http\Controllers\SuperAdmin\PasienHariIniController;
use App\Http\Controllers\SuperAdmin\PasienInsightController;
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use App\Http\Controllers\SuperAdmin\TransaksiInsightController;
use App\Http\Controllers\Testing\TestingChartController;
use App\Http\Controllers\Testing\TestingController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Rest of your web routes remain the same...
Route::get('/')->middleware('checkAuth');

Route::middleware('auth')->post('/heartbeat', function () {
    User::where('id', Auth::id())->update([
        'terakhir_login' => now(),
    ]);

    return response()->json([
        'success' => true,
    ]);
});

Route::get('/testing', [TestingController::class, 'testing'])->name('testing');
Route::post('/testing-create-kunjungan', [TestingController::class, 'testingCreateKunjungan'])->name('testing.create.kunjungan');
Route::post('/testing-ubah-status-kunjungan', [TestingController::class, 'ubahStatusKunjungan'])->name('testing.ubah.status.kunjungan');
Route::post('/testing-batalkan-status-kunjungan', [TestingController::class, 'batalkanStatusKunjungan'])->name('testing.batalkan.status.kunjungan');
Route::post('/testing-update-status-resep-obat', [TestingController::class, 'batalkanStatusKunjungan'])->name('testing.update.status.resep.obat');
Route::get('/checkout', [TestingController::class, 'checkout'])->name('checkout');
Route::post('/midtrans/notification', [TestingController::class, 'notificationHandler']);
Route::get('/sebelum/checkout', [TestingController::class, 'sebelumCheckout']);
Route::get('/contoh-detail-emr', [TestingController::class, 'contohDetailEMR']);

Route::prefix('/testing-chart')->group(function () {
    Route::get('/', [TestingChartController::class, 'index'])->name('testing.chart.index');
    Route::get('/keuangan', [TestingChartController::class, 'chartKeuangan'])->name('testing.chart.keuangan');
    Route::get('/kunjungan', [TestingChartController::class, 'chartKunjungan'])->name('testing.chart.kunjungan');
});

Route::prefix('/testing-tom-select')->group(function () {
    Route::get('/', [TestingController::class, 'testingTomSelect'])->name('testing.tom.select.index');
    Route::get('/data-obat', [TestingController::class, 'dataObat'])->name('testing.tom.select.data.obat');
});

Route::prefix('/testing-qr-code')->group(function () {
    // Route::get('/', [QrCodeController::class, 'generate'])->name('testing.qr.code.index');
    Route::get('/qr/generate/all', [QrCodeController::class, 'generateAll'])->name('qr.generate.all');
    Route::get('/qr/generate/{id}', [QrCodeController::class, 'generate'])->name('qr.generate');
    Route::get('/pasien/{id}/qr-view', [QrCodeController::class, 'showPasien'])->name('qr.show');

    // Route::get('/emr/generate-no-rm', [EMRController::class, 'generateAll'])->name('emr.generate');

    Route::get('/generate-no-emr', [PasienController::class, 'generateNoEmrPasien']);

    Route::get('latihan', [TestingController::class, 'contoh']);
});

Route::get('/contoh-kuitansi', function () {
    return view('kuitansi');
});

// API KHUSUS UNTUK AURELIO
// SIAPA YANG GANGGU PECAH KEPALANYA
// BY AURELIO
Route::get('/getDataDokter', [APIWebController::class, 'getDataDokter']);
Route::get('/getDataTestimoni', [APIWebController::class, 'getDataTestimoni']);
// END API KHUSUS AURELIO

// //////// ROLE DOKTER ///////////
Route::middleware('guest')->group(function () {
    Route::prefix('dokter')->group(function () {
        Route::get('/login', [AuthController::class, 'login'])->name('dokter.login');
        Route::post('/register', [AuthController::class, 'register'])->name('dokter.register');
        Route::post('/proses-login', [AuthController::class, 'prosesLogin'])->name('dokter.proses.login');
    });
});

Route::middleware('auth')->group(function () {
    Route::middleware(['role:Dokter'])->group(function () {
        Route::prefix('dokter')->group(function () {
            Route::get('/dashboard', [DokterDokterController::class, 'index'])->name('dokter.dashboard');
            Route::get('/logout-dokter', [DokterDokterController::class, 'logoutDokter'])->name('logout.dokter');
            Route::get('/kunjungan', [DokterDokterController::class, 'kunjungan'])->name('dokter.kunjungan');
        });
    });
});

Route::middleware(['auth'])->group(function () {
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::put('/profile', [SettingsController::class, 'updateProfile'])->name('profile.update');
        Route::put('/password', [SettingsController::class, 'updatePassword'])->name('password.update');
    });
});

Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/dashboard/stats', [DashboardController::class, 'getDashboardStats'])->name('admin.dashboard.stats');
        Route::get('/chart_kunjungan', [DashboardController::class, 'getChartKunjungan'])->name('chart_kunjungan');
        Route::get('/total_dokter', [DashboardController::class, 'getTotalDokter'])->name('total_dokter');
        Route::get('/total_pasien', [DashboardController::class, 'getTotalPasien'])->name('total_pasien');
        Route::get('/total_farmasi', [DashboardController::class, 'getTotalFarmasi'])->name('total_farmasi');
        Route::get('/stok_obat', [DashboardController::class, 'getStokObat'])->name('stok_obat');
        Route::patch('/antrian/{kunjungan}/proses', [DashboardController::class, 'proses'])->name('admin.antrian.proses');
        Route::patch('/antrian/{kunjungan}/batalkan', [DashboardController::class, 'batalkan'])->name('admin.antrian.batalkan');
        Route::get('/antrian/data', [DashboardController::class, 'getDataAntrean'])->name('antrian.data');

        Route::get('/pasien-hari-ini', [AdminPasienHariIniController::class, 'index'])->name('pasien.hari.ini');
        Route::get('/detail-pasien-hari-ini/{no_emr}', [AdminPasienHariIniController::class, 'detail'])->name('detail.pasien.hari.ini');
    });

    Route::prefix('jenis-spesialis')->group(function () {
        Route::get('/', [JenisSpesialisController::class, 'index'])->name('jenis.spesialis.index');
        Route::get('/data-jenis-spesialis', [JenisSpesialisController::class, 'dataJenisSpesialisDokter'])->name('get.data.jenis.spesialis.dokter');
        Route::post('/create-data-jenis-spesialis', [JenisSpesialisController::class, 'createJenisSpesialisDokter'])->name('create.data.jenis.spesialis.dokter');
        Route::get('/get-data-jenis-spesialis/{id}', [JenisSpesialisController::class, 'getDataJenisSPesialisById'])->name('get.data.jenis.spesialis.dokter.by.id');
        Route::post('/update-data-jenis-spesialis', [JenisSpesialisController::class, 'updateJenisSpesialisDokter'])->name('update.data.jenis.spesialis.dokter');
        Route::post('/delete-data-jenis-spesialis/{id}', [JenisSpesialisController::class, 'deleteJenisSpesialisDokter'])->name('delete.data.jenis.spesialis.dokter');
    });

    Route::prefix('poli')->group(function () {
        Route::get('/', [PoliController::class, 'index'])->name('poli.index');
        Route::get('/get-data-poli', [PoliController::class, 'getDataPoli'])->name('poli.get.data');
        Route::post('/create-data-poli', [PoliController::class, 'createDataPoli'])->name('poli.create.data');
        Route::get('/get-data-poli-by-id/{id}', [PoliController::class, 'getDataPoliById'])->name('poli.get.data.by.id');
        Route::post('/update-data-poli', [PoliController::class, 'updateDataPoli'])->name('poli.update.data');
        Route::post('/delete-data-poli', [PoliController::class, 'deleteDataPoli'])->name('poli.delete.data');
    });

    Route::prefix('kategori_layanan')->group(function () {
        Route::get('/', [KategoriLayananController::class, 'index'])->name('kategori.layanan.index');
        Route::get('/get-data-kategori-layanan', [KategoriLayananController::class, 'getDataKategoriLayanan'])->name('kategori.layanan.get.data.kategori.layanan');
        Route::post('/create-data-kategori-layanan', [KategoriLayananController::class, 'createDataKategoriLayanan'])->name('kategori.layanan.create.data.kategori.layanan');
        Route::get('/get-data-kategori-layanan-by-id/{id}', [KategoriLayananController::class, 'getDataKategoriLayananById'])->name('kategori.layanan.get.data.kategori.layanan.by.id');
        Route::post('/update-data-kategori-layanan', [KategoriLayananController::class, 'updateDataKategoriLayanan'])->name('kategori.layanan.update.data.kategori.layanan');
        Route::post('/delete-data-kategori-layanan', [KategoriLayananController::class, 'deleteDataKategoriLayanan'])->name('kategori.layanan.delete.data.kategori.layanan');
    });

    Route::prefix('layanan')->group(function () {
        Route::get('/', [LayananController::class, 'index'])->name('layanan.index');
        Route::get('/get-data-layanan', [LayananController::class, 'getDataLayanan'])->name('layanan.get.data');
        Route::post('/create-data-layanan', [LayananController::class, 'createDataLayanan'])->name('layanan.create.data');
        Route::get('/get-data-layanan-by-id/{id}', [LayananController::class, 'getDataLayananById'])->name('layanan.get.data.by.id');
        Route::get('/get-data-kategori-layanan', [LayananController::class, 'getDataKategoriLayanan'])->name('layanan.get.data.kategori.layanan');
        Route::get('/get-data-poli', [KategoriLayananController::class, 'getDataPoli'])->name('kategori.layanan.get.data.poli');
        Route::post('/update-data-layanan', [LayananController::class, 'updateDataLayanan'])->name('layanan.update.data');
        Route::post('/delete-data-layanan', [LayananController::class, 'deleteDataLayanan'])->name('layanan.delete.data');
    });

    Route::prefix('manajemen_pengguna')->name('manajemen_pengguna.')->group(function () {
        Route::get('/', [ManajemenPenggunaController::class, 'index'])->name('index');

        // crud user
        Route::get('/data_user', [ManajemenPenggunaController::class, 'dataUser'])->name('data_user');
        Route::post('/add_user', [UserController::class, 'createUser'])->name('add_user');
        Route::get('/get_user_by_id/{id}', [UserController::class, 'getUserById'])->name('get_user_by_id');
        Route::put('/update_user/{id}', [UserController::class, 'updateUser'])->name('update_user');
        Route::delete('/delete_user/{id}', [UserController::class, 'deleteUser'])->name('delete_user');

        Route::get('/data_admin', [ManajemenPenggunaController::class, 'dataAdmin'])->name('data_admin');
        Route::post('/add_admin', [AdminController::class, 'createAdmin'])->name('add_admin');
        Route::get('/get_admin_by_id/{id}', [AdminController::class, 'getAdminById'])->name('get_admin_by_id');
        Route::put('/update_admin/{id}', [AdminController::class, 'updateAdmin'])->name('update_admin');
        Route::delete('/delete_admin/{id}', [AdminController::class, 'deleteAdmin'])->name('delete_admin');

        // crud dokter
        Route::get('/data_dokter', [ManajemenPenggunaController::class, 'dataDokter'])->name('data_dokter');
        Route::get('/get_dokter_by_id/{id}', [DokterController::class, 'getDokterById'])->name('get_dokter_by_id');
        Route::post('/add_dokter', [DokterController::class, 'createDokter'])->name('add_dokter');
        Route::post('/update_dokter/', [DokterController::class, 'updateDokter'])->name('update_dokter');

        // crud farmasi
        Route::get('/data_farmasi', [ManajemenPenggunaController::class, 'dataFarmasi'])->name('data_farmasi');
        Route::post('/add_farmasi', [FarmasiController::class, 'createFarmasi'])->name('add_farmasi');
        Route::get('/get_farmasi_by_id/{id}', [FarmasiController::class, 'getFarmasiById'])->name('get_farmasi_by_id');
        Route::put('/update_farmasi/{id}', [FarmasiController::class, 'updateFarmasi'])->name('update_farmasi');

        // crud kasir
        Route::get('/data_kasir', [ManajemenPenggunaController::class, 'dataKasir'])->name('data_kasir');
        Route::post('/add_kasir', [KasirController::class, 'createKasir'])->name('add_kasir');
        Route::get('/get_kasir_by_id/{id}', [KasirController::class, 'getKasirById'])->name('get_kasir_by_id');
        Route::put('/update_kasir/{id}', [KasirController::class, 'updateKasir'])->name('update_kasir');

        // crud pasien
        Route::get('/data_pasien', [ManajemenPenggunaController::class, 'dataPasien'])->name('data_pasien');
        Route::get('/get_pasien_by_id/{id}', [PasienController::class, 'getPasienById'])->name('get_pasien_by_id');
        Route::post('/add_pasien', [PasienController::class, 'createPasien'])->name('add_pasien');
        Route::put('/update_pasien/{id}', [PasienController::class, 'updatePasien'])->name('update_pasien');
        Route::delete('/search', [PasienController::class, 'search'])->name('pasien');
        Route::get('/cetak-stiker-pasien/{noEMR}', [PasienController::class, 'cetakStiker'])->name('cetak.stiker.pasien');
        Route::get('/show-detail-data-pasien/{noEMR}', [PasienController::class, 'showPasien'])->name('show.detail.pasien');

        // export excel
        Route::get('/pasien/export/excel', [PasienController::class, 'exportExcel'])
            ->name('export_pasien_excel');

    });

    Route::prefix('management-pengguna')->group(function () {
        Route::get('/', [ManajemenPenggunaController::class, 'index'])->name('management.pengguna.index');
        Route::get('/get-data-poli', [ManajemenPenggunaController::class, 'getDataPoli'])->name('get.data.poli');

        // Perawat
        Route::prefix('perawat')->group(function () {
            Route::get('/', [ManagementPerawatController::class, 'index'])->name('perawat');
            Route::get('/get-data', [ManagementPerawatController::class, 'getDataPerawat'])->name('get.data.perawat');
            Route::get('/get-data-detail/{slug}', [ManagementPerawatController::class, 'getDataDetailPerawat'])->name('get.data.detail.perawat');
            Route::get('/get-data-dokter-poli/{slug}', [ManagementPerawatController::class, 'getDataDokterPoli'])->name('get.data.dokter.poli');

            Route::post('/update-data-perawat/{slug}', [ManagementPerawatController::class, 'updateDataPerawat'])->name('update.data.perawat');
        });
    });

    Route::prefix('pengaturan_klinik')->name('pengaturan_klinik.')->group(function () {
        Route::get('/', [PengaturanKlinikController::class, 'index'])->name('index');

        // jadwal dokter
        Route::get('/jadwal_dokter', [PengaturanKlinikController::class, 'dataJadwalDokter'])->name('jadwal_dokter');
        Route::post('/add_jadwal_dokter', [JadwalDokterController::class, 'createJadwalDokter'])->name('add_jadwal_dokter');
        Route::get('/get_jadwal_dokter_by_id/{id}', [JadwalDokterController::class, 'getJadwalDokterById'])->name('get_jadwal_dokter_by_id');
        Route::put('/update_jadwal_dokter/{id}', [JadwalDokterController::class, 'updateJadwalDokter'])->name('update_jadwal_dokter');
        Route::delete('/delete_jadwal_dokter/{id}', [JadwalDokterController::class, 'deleteJadwalDokter'])->name('delete_jadwal_dokter');
        Route::get('/search', [JadwalDokterController::class, 'search'])->name('dokter');
        Route::get('/search-poli-by-dokter/{id}', [PengaturanKlinikController::class, 'searchDataPoliByIdDokter'])->name('search.poli.by.id.dokter');
    });

    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::get('/laporan_kunjungan', [LaporanController::class, 'dataKunjungan'])->name('laporan_kunjungan');
        Route::get('/laporan-kunjungan-export', [LaporanController::class, 'exportKunjungan'])->name('export');
        Route::get('/laporan_keuangan', [LaporanController::class, 'dataPembayaran'])->name('laporan_keuangan');
        Route::get('/laporan_transaksi_apoteker', [LaporanController::class, 'dataTransaksiApoteker'])->name('laporan_transaksi_apoteker');
        Route::get('/laporan_administrasi', [LaporanController::class, 'dataAdministrasi'])->name('laporan_administrasi');
        Route::get('/export-laporan-keuangan', [LaporanController::class, 'exportKeuangan'])->name('export.laporan.keuangan');
    });

    Route::prefix('data_medis_pasien')->name('data_medis_pasien.')->group(function () {
        Route::get('/', [DataMedisPasienController::class, 'index'])->name('index');
        Route::get('/laporan_rekam_medis', [DataMedisPasienController::class, 'dataRekamMedis'])->name('laporan_rekam_medis');
        Route::get('/diagnosa_dan_konsultasi', [DataMedisPasienController::class, 'dataKonsultasi'])->name('diagnosa_dan_konsultasi');
        Route::get('/hasil_lab', [DataMedisPasienController::class, 'dataLab'])->name('hasil_lab');
        Route::get('/data_emr', [DataMedisPasienController::class, 'getDataEMR'])->name('data_emr');
        Route::get('/get-data-emr-by-id/{id}', [DataMedisPasienController::class, 'getDataEMRById'])->name('get.data.emr.by.id');
        Route::get('/detail-emr/{no_emr}', [DataMedisPasienController::class, 'detailEMR'])->name('detail_emr');
        Route::get('/detail-emr/pasien/{noEMR}', [DataMedisPasienController::class, 'detailEMRPasien'])->name('detail.emr.pasien');
    });

    Route::prefix('jadwal_kunjungan')->name('jadwal_kunjungan.')->group(function () {
        Route::get('/', [JadwalKunjunganController::class, 'index'])->name('index');
        Route::post('/create', [JadwalKunjunganController::class, 'store'])->name('create');
        Route::get('/search', [JadwalKunjunganController::class, 'search'])->name('pasien');

        Route::get('/listDokter', [JadwalKunjunganController::class, 'listDokter'])->name('list.dokter');
        Route::get('/listPoliByDokter/{dokterId}/poli', [JadwalKunjunganController::class, 'listPoliByDokter'])->name('list.dokter.poli');

        Route::get('/waiting', [JadwalKunjunganController::class, 'waiting'])->name('waiting');
        Route::post('/updateKunjungan/{id}', [JadwalKunjunganController::class, 'updateDataKunjungan'])->name('update.kunjungan');
        Route::post('/update-status/{id}', [JadwalKunjunganController::class, 'updateStatusKunjunganToEngaged'])->name('update_status');

        Route::get('/masa-depan', [JadwalKunjunganController::class, 'getDataKunjunganYangAkanDatang'])->name('masa.depan');

        Route::get('/get-data-KYAD/{id}', [JadwalKunjunganController::class, 'getDataKYAD']);

        Route::post('/batalkan-kunjungan/{id}', [JadwalKunjunganController::class, 'batalkanKunjungan']);
    });

    // Menu Order Layanan
    Route::prefix('/order-layanan')->group(function () {
        Route::get('/', [OrderLayananController::class, 'index'])->name('order.layanan.index');
        Route::get('/get-data-order-layanan', [OrderLayananController::class, 'getDataOrderLayanan'])->name('order.layanan.get.data.order.layanan');

        Route::get('/get-data-detail/{kodeTransaksi}', [OrderLayananController::class, 'getDetailOrderLayanan'])->name('get.data.detail.order.layanan');

        Route::get('/get-data-pasien', [OrderLayananController::class, 'searchPasien'])->name('order.layanan.get.data.pasien');
        Route::get('/get-data-poli', [OrderLayananController::class, 'getDataPoli'])->name('order.layanan.get.data.poli');
        Route::get('/get-data-jadwal-dokter-hari-ini', [OrderLayananController::class, 'getJadwalDokterHariIni'])->name('order.layanan.get.data.jadwal.dokter.hari.ini');
        Route::post('/create-data-order-layanan', [OrderLayananController::class, 'createDataOrderLayanan'])->name('order.layanan.create.data.order.layanan');
        Route::get('/get-data-order-layanan/{kodeTransaksi}', [OrderLayananController::class, 'getDataOrderLayananById'])->name('order.layanan.get.data.order.layanan.by.id');
        Route::post('/update-data-order-layanan', [OrderLayananController::class, 'updateDataOrderLayanan'])->name('order.layanan.update.data.order.layanan');
        Route::post('/delete-data-order-layanan/{kodeTransaksi}', [OrderLayananController::class, 'deleteDataOrderLayanan'])->name('order.layanan.delete.data.order.layanan');
    });
});

Route::middleware(['auth', 'role:Farmasi'])->group(function () {

    Route::prefix('farmasi')->group(function () {
        Route::get('/dashboard', [FarmasiController::class, 'index'])->name('farmasi.dashboard');
        Route::get('/dashboard/summary', [FarmasiController::class, 'dashboardSummary'])->name('farmasi.dashboard.summary');
        Route::get('/dashboard/stok-kritis', [FarmasiController::class, 'dashboardStokKritis'])->name('farmasi.dashboard.stok.kritis');
        Route::get('/dashboard/transaksi-terbaru', [FarmasiController::class, 'dashboardTransaksiTerbaru'])->name('farmasi.dashboard.transaksi.terbaru');
        Route::get('/chart/penjualan-obat', [FarmasiController::class, 'chartPenjualanObat'])->name('chart.penjualan');

        Route::get('/stok-obat', [StokObatController::class, 'stokObatPage'])->name('farmasi.stok.obat.index');
        Route::get('/stok-obat/data', [StokObatController::class, 'stokObatData'])->name('farmasi.stok-obat.data');
        Route::get('/stok-obat/{id}/detail', [StokObatController::class, 'stokObatDetail'])->name('farmasi.stok-obat.detail');

        Route::get('/penjualan-obat', [PenjualanObatController::class, 'penjualanObatPage'])->name('farmasi.penjualan.obat.index');
        Route::get('/penjualan-obat/hari-ini', [PenjualanObatController::class, 'penjualanObatHariIniPage'])->name('farmasi.penjualan.obat.hari.ini.index');
        Route::get('/get-data-penjualan-obat/hari-ini', [PenjualanObatController::class, 'penjualanObatHariIni'])->name('farmasi.penjualan.obat.hari.ini.data');
        Route::get('/penjualan-obat/data', [PenjualanObatController::class, 'penjualanObatData'])->name('farmasi.penjualan-obat.data');
        Route::get('/penjualan-obat/{id}/detail', [PenjualanObatController::class, 'showDetailPenjualanObat'])->name('farmasi.penjualan.obat.detail');

        // Route Kategori Obat
        Route::prefix('kategori-obat')->group(function () {
            Route::get('/', [KategoriObatController::class, 'index'])->name('kategori.obat.index');
            Route::get('/get-data-kategori-obat', [KategoriObatController::class, 'getDataKategoriObat'])->name('kategori.obat.get.data.kategori.obat');
            Route::post('/create-data-kategori-obat', [KategoriObatController::class, 'createDataKategoriObat'])->name('kategori.obat.create.data.kategori.obat');
            Route::get('/get-data-kategori-obat-by-id/{id}', [KategoriObatController::class, 'getDataKategoriObatById'])->name('kategori.obat.get.data.kategori.obat.by.id');
            Route::post('/update-data-kategori-obat', [KategoriObatController::class, 'updateDataKategoriObat'])->name('kategori.obat.update.data.kategori.obat');
            Route::post('/delete-data-kategori-obat', [KategoriObatController::class, 'deleteDataKategoriObat'])->name('kategori.obat.delete.data.kategori.obat');
        });

        Route::prefix('obat')->group(function () {
            Route::get('/', [ObatController::class, 'index'])->name('obat.index');
            Route::get('/get-data-obat', [ObatController::class, 'getDataObat'])->name('obat.get.data.obat');
            Route::get('/get-data-kategori-obat', [ObatController::class, 'getDataKategoriObat'])->name('obat.get.data.kategori.obat');
            Route::post('/create-data-obat', [ObatController::class, 'createObat'])->name('obat.create');
            Route::get('/get-data-obat-by/{id}', [ObatController::class, 'getObatById'])->name('obat.get.data.by.id');
            Route::post('/update-data-obat/{id}', [ObatController::class, 'updateObat'])->name('obat.update');
            Route::delete('/delete-data-obat/{id}', [ObatController::class, 'deleteObat'])->name('obat.delete');
            Route::get('/export-data-obat', [ObatController::class, 'export'])->name('export.data.obat');
            Route::post('/import-data-obat', [ObatController::class, 'importExcel'])->name('import.data.obat');
            Route::get('/print-data-obat', [ObatController::class, 'printPDF'])->name('print.data.obat');
        });

        Route::prefix('order-obat')->group(function () {
            Route::get('/get-data-penjualan-obat', [OrderObatController::class, 'getDataPenjualanObat'])->name('obat.penjualan.obat');
            Route::get('/search-data-pasien', [OrderObatController::class, 'search'])->name('obat.search.data.pasien');
            Route::get('/search-data-obat', [OrderObatController::class, 'searchObat'])->name('obat.search.data.obat');
            Route::get('/resep-aktif', [OrderObatController::class, 'ajaxResepAktif'])->name('resep.aktif');

            Route::post('/pesan-obat', [OrderObatController::class, 'pesanObat'])->name('obat.pesan.obat');
            Route::get('/order/{id}', [OrderObatController::class, 'show']);
            Route::get('/get-data-detail-order-obat/{kodeTransaksi}', [OrderObatController::class, 'getDataDetailOrderObat'])->name('get.data.detail.order.obat');
            Route::put('/order/{id}', [OrderObatController::class, 'update']);
            Route::delete('/order/{id}', [OrderObatController::class, 'destroy']);
        });

        // Route Penggunaan Obat
        Route::prefix('penggunaan-obat')->group(function () {
            Route::get('/', [PenggunaanObatController::class, 'index'])->name('penggunaan.obat');
            Route::get('/get-data-penggunaan-obat', [PenggunaanObatController::class, 'getDataPenggunaanObat'])->name('get.data.penggunaan.obat');
            Route::get('/export-data-penggunaan-obat', [PenggunaanObatController::class, 'export'])->name('export.data.penggunaan.obat');
            Route::get('/print-data-penggunaan-obat', [PenggunaanObatController::class, 'print'])->name('print.data.penggunaan.obat');
        });

        // Route Kadaluarsa Obat
        Route::prefix('kadaluarsa-obat')->group(function () {
            Route::get('/', [KadaluarsaObatController::class, 'index'])->name('kadaluarsa.obat');
            Route::get('/get-data-warning-kadaluarsa-obat', [KadaluarsaObatController::class, 'getWarningKadaluarsa'])->name('get.data.warning.kadaluarsa.obat');
            Route::get('/get-data-kadaluarsa-obat', [KadaluarsaObatController::class, 'getDataKadaluarsaObat'])->name('get.data.kadaluarsa.obat');
        });

        // Route Bahan Habis Pakai
        Route::prefix('bahan-habis-pakai')->group(function () {
            Route::get('/', [BahanHabisPakaiController::class, 'index'])->name('bahan.habis.pakai');
            Route::get('/get-data-bhp', [BahanHabisPakaiController::class, 'getDataBahanHabisPakai'])->name('get.data.bahan.habis.pakai');
            Route::post('/create-data-bhp', [BahanHabisPakaiController::class, 'createDataBahanHabisPakai'])->name('create.data.bahan.habis.pakai');
            Route::get('/get-data-bhp-by-id/{id}', [BahanHabisPakaiController::class, 'getDataBahanHabisPakaiById'])->name('get.data.bahan.habis.pakai.by.id');
            Route::post('/update-data-bhp/{id}', [BahanHabisPakaiController::class, 'updateDataBahanHabisPakai'])->name('update.data.bahan.habis.pakai');
            Route::post('/delete-data-bhp/{id}', [BahanHabisPakaiController::class, 'deleteDataBahanHabisPakai'])->name('delete.data.bahan.habis.pakai');
            Route::get('/export-excel-data-bhp', [BahanHabisPakaiController::class, 'exportExcelBhp'])->name('export.excel.data.bahan.habis.pakai');
            Route::get('/print-pdf-data-bhp', [BahanHabisPakaiController::class, 'printPdfBhp'])->name('print.pdf.data.bahan.habis.pakai');
            Route::post('/import-excel-data-bhp', [BahanHabisPakaiController::class, 'importExcelBhp'])->name('import.excel.data.bahan.habis.pakai');
        });

        Route::prefix('pemakaian-bhp')->group(function () {
            Route::get('get-data-bhp', [PemakaianBahanHabisPakaiController::class, 'getDataPemakaianBHP'])->name('get.data.bhp.pemakaian.bhp');
            Route::post('store-data-pemakaian-bhp', [PemakaianBahanHabisPakaiController::class, 'storeDataPemakaianBHP'])->name('store.data.pemakaian.bhp');
            Route::get('get-data-depot', [PemakaianBahanHabisPakaiController::class, 'getDataDepot'])->name('get.data.depot.pemakaian.bhp');
        });

        // Route Penggunaan BHP
        Route::prefix('penggunaan-bhp')->group(function () {
            Route::get('/', [PenggunaanBHPController::class, 'index'])->name('penggunaan.bhp');
            Route::get('/get-data-penggunaan-bhp', [PenggunaanBHPController::class, 'getDataPenggunaanBHP'])->name('get.data.penggunaan.bhp');
            Route::get('/export-data-penggunaan-bhp', [PenggunaanBHPController::class, 'exportExcel'])->name('export.data.penggunaan.bhp');
            Route::get('/print-pdf-data-penggunaan-bhp', [PenggunaanBHPController::class, 'printPdf'])->name('print.pdf.data.penggunaan.bhp');
        });

        // Route Kadaluarsa BHP
        Route::prefix('kadaluarsa-bhp')->group(function () {
            Route::get('/', [KadaluarsaBHPController::class, 'index'])->name('kadaluarsa.bhp');
            Route::get('/get-data-warning-kadaluarsa-bhp', [KadaluarsaBHPController::class, 'getWarningKadaluarsa'])->name('get.data.warning.kadaluarsa.bhp');
            Route::get('/get-data-kadaluarsa-bhp', [KadaluarsaBHPController::class, 'getDataKadaluarsaBHP'])->name('get.data.kadaluarsa.bhp');
        });

        // Route Cetak Resep Obat
        Route::prefix('cetak-resep-obat')->group(function () {
            Route::get('/', [CetakResepObatController::class, 'index'])->name('cetak.resep.obat');
            Route::get('/search-data-pasien', [CetakResepObatController::class, 'searchDataPasien'])->name('cetak.resep.obat.search.data.pasien');
            Route::get('/search-data-dokter', [CetakResepObatController::class, 'searchDataDokter'])->name('cetak.resep.obat.search.data.dokter');
            Route::get('/search-data-obat', [CetakResepObatController::class, 'searchDataObat'])->name('cetak.resep.obat.search.data.obat');
            Route::post('/print-preview', [CetakResepObatController::class, 'printPreview'])->name('cetak.resep.obat.print.preview');
        });

        Route::prefix('restock-obat')->group(function () {
            Route::get('/', [RestockObatController::class, 'index'])->name('farmasi.restock.obat');
            Route::get('/get-data-restock-obat', [RestockObatController::class, 'getDataRestockObat'])->name('farmasi.get.data.restock.obat');
            Route::get('/get-data-batch-obat-by-obat-id/{obatId}', [RestockObatController::class, 'getDataBatchObatByObatId'])->name('farmasi.get.data.batch.obat.by.obat.id');
            Route::post('/create-data-restock-obat', [RestockObatController::class, 'createDataRestockObat'])->name('farmasi.create.data.restock.obat');
            Route::get('/get-data-detail-restock-obat/{id}', [RestockObatController::class, 'getDetailRestockObat'])->name('farmasi.get.data.restock.obat.by.id');
            Route::post('/cancel/{noFaktur}', [RestockObatController::class, 'cancelRestockObat'])->name('farmasi.cancel.restock.obat');
        });

        Route::prefix('riwayat-restock-obat')->group(function () {
            Route::get('/get-data-riwayat-restock-obat', [RestockObatController::class, 'getDataRiwayatRestockObat'])->name('farmasi.get.data.riwayat.restock.obat');
        });

        Route::prefix('stok-masuk-obat')->group(function () {
            Route::get('/', [StokMasukObatController::class, 'index'])->name('farmasi.stok.masuk.obat');
            Route::get('/get-data-stok-masuk-obat', [StokMasukObatController::class, 'getDataStokMasukObat'])->name('farmasi.get.data.stok.masuk.obat');
            Route::get('/get-data-detail-stok-masuk-obat/{noFaktur}', [StokMasukObatController::class, 'getDataDetailStokMasukObat'])->name('farmasi.get.data.detail.stok.masuk.obat');
            Route::post('/konfirmasi/{id}', [StokMasukObatController::class, 'konfirmasiStokMasukObat'])->name('farmasi.konfirmasi.stok.masuk.obat');
        });

        Route::prefix('riwayat-stok-masuk-obat')->group(function () {
            Route::get('/get-data-riwayat-stok-masuk-obat', [StokMasukObatController::class, 'getDataRiwayatStokMasukobat'])->name('farmasi.get.data.riwayat.stok.masuk.obat');
        });

        Route::prefix('restock-bahan-habis-pakai')->group(function () {
            Route::get('/', [RestockBahanHabisPakaiController::class, 'index'])->name('farmasi.restock.bahan-habis-pakai');
            Route::get('/get-data-restock-bahan-habis-pakai', [RestockBahanHabisPakaiController::class, 'getDataRestockBahanHabisPakai'])->name('farmasi.get.data.restock.bahan.habis.pakai');
            Route::get('/get-data-batch-bahan-habis-pakai-by-id/{bhpId}', [RestockBahanHabisPakaiController::class, 'getDataBatchBahanHabisPakaiById'])->name('farmasi.get.data.batch.bahan.habis.pakai.by.id');
            Route::post('/create-data-restock-bahan-habis-pakai', [RestockBahanHabisPakaiController::class, 'createDataRestockRestockBahanHabisPakai'])->name('farmasi.create.data.restock.bahan.habis.pakai');
            Route::get('/get-data-detail-restock-bahan-habis-pakai/{id}', [RestockBahanHabisPakaiController::class, 'getDetailRestockBahanHabisPakai'])->name('farmasi.get.data.restock.bahan.habis.pakai.by.id');
            Route::post('/cancel/{noFaktur}', [RestockBahanHabisPakaiController::class, 'cancelRestockBahanHabisPakai'])->name('farmasi.cancel.restock.bahan.habis.pakai');
        });

        Route::prefix('riwayat-restock-bahan-habis-pakai')->group(function () {
            Route::get('/get-data-riwayat-restock-bahan-habis-pakai', [RestockBahanHabisPakaiController::class, 'getDataRiwayatRestockBahanHabisPakai'])->name('farmasi.get.data.riwayat.restock.bahan.habis.pakai');
        });

        Route::prefix('stok-masuk-bahan-habis-pakai')->group(function () {
            Route::get('/', [StokMasukBahanHabisPakaiController::class, 'index'])->name('farmasi.stok.masuk.bahan.habis.pakai');
            Route::get('/get-data-stok-masuk-bahan-habis-pakai', [StokMasukBahanHabisPakaiController::class, 'getDataStokMasukBahanHabisPakai'])->name('farmasi.get.data.stok.masuk.bahan.habis.pakai');
            Route::get('/get-data-detail-stok-masuk-bahan-habis-pakai/{noFaktur}', [StokMasukBahanHabisPakaiController::class, 'getDataDetailStokMasukBahanHabisPakai'])->name('farmasi.get.data.detail.stok.masuk.bahan.habis.pakai');
            Route::post('/konfirmasi/{id}', [StokMasukBahanHabisPakaiController::class, 'konfirmasiStokMasukBahanHabisPakai'])->name('farmasi.konfirmasi.stok.masuk.bahan.habis.pakai');
        });

        Route::prefix('riwayat-stok-masuk-bahan-habis-pakai')->group(function () {
            Route::get('/get-data-riwayat-stok-masuk-bahan-habis-pakai', [StokMasukBahanHabisPakaiController::class, 'getDataRiwayatStokMasukBahanHabisPakai'])->name('farmasi.get.data.riwayat.stok.masuk.bahan.habis.pakai');
        });

        // Return Obat
        Route::prefix('/return-obat')->group(function () {
            Route::get('/', [ReturnObatController::class, 'index'])->name('farmasi.return.obat');
            Route::get('/get-data-return-obat', [ReturnObatController::class, 'getDataReturnObat'])->name('farmasi.get.data.return.obat');
            Route::get('/get-data-return-obat-by-no-return/{noReturn}', [ReturnObatController::class, 'getDataReturnObatByNoReturn'])->name('farmasi.get.data.return.obat.by.no.return');
            Route::get('/get-data-obat', [ReturnObatController::class, 'getDataObat'])->name('farmasi.get.data.obat');
            Route::get('/get-data-batch-by-obat-id/{obatId}', [ReturnObatController::class, 'getDataBatchByObatId'])->name('farmasi.get.data.batch.by.obat.id');
            Route::get('/get-stok-batch-obat-depot/{batchObatId}/{depotId}', [ReturnObatController::class, 'getStokBatchObatDepot'])->name('farmasi.get.stok.batch.obat.depot');
            Route::post('/create-data-return-obat', [ReturnObatController::class, 'createDataReturnObat'])->name('farmasi.create.data.return.obat');
            Route::post('/konfirmasi-return-obat/{kodeReturn}', [ReturnObatController::class, 'konfirmasiReturnObat'])->name('farmasi.konfirmasi.return.obat');

            Route::get('/get-data-supplier', [ReturnObatController::class, 'getDataSupplier'])->name('farmasi.get.data.supplier');
            Route::get('/get-depot-by-supplier', [ReturnObatController::class, 'getDepotBySupplier'])->name('farmasi.get.depot.by.supplier');
            Route::get('/get-obat-by-supplier-depot', [ReturnObatController::class, 'getObatBySupplierDepot'])->name('farmasi.get.obat.by.supplier.depot');
        });

        Route::prefix('/riwayat-return-obat')->group(function () {
            Route::get('/get-data', [ReturnObatController::class, 'getDataRiwayatReturnObat'])->name('get.data.riwayat.return.obat');
            Route::get('/get-data-summary', [ReturnObatController::class, 'getSummaryRiwayatReturnObat'])->name('get.data.summary.riwayat.return.obat');
            Route::get('/get-data-detail-riwayat-return-obat/{kodeReturn}', [ReturnObatController::class, 'getDataDetailRiwayatReturnObat'])->name('get.data.detail.riwayat.return.obat');
        });

        // Return Bahan Habis Pakai
        Route::prefix('return-bahan-habis-pakai')->group(function () {
            Route::get('/', [ReturnBahanHabisPakaiController::class, 'index'])->name('farmasi.return.bahan.habis.pakai');
            Route::get('/get-data-return-bahan-habis-pakai', [ReturnBahanHabisPakaiController::class, 'getDataReturnBhp'])->name('farmasi.get.data.return.bahan.habis.pakai');
            Route::get('/get-data-bahan-habis-pakai', [ReturnBahanHabisPakaiController::class, 'getDataBhp'])->name('farmasi.get.data.bahan.habis.pakai');
            Route::get('/get-data-batch-by-bahan-habis-pakai-id/{bhpId}', [ReturnBahanHabisPakaiController::class, 'getDataBatchByBhpId'])->name('farmasi.get.data.batch.by.bhp.id');
            Route::get('/get-stok-batch-bhp-depot/{batchBhpId}/{depotId}', [ReturnBahanHabisPakaiController::class, 'getStokBatchBhpDepot'])->name('farmasi.get.stok.batch.bhp.depot');
            Route::get('/get-data-return-bhp-by-kode-return/{noReturn}', [ReturnBahanHabisPakaiController::class, 'getDataReturnBhpByKodeReturn'])->name('farmasi.get.data.return.bhp.by.kode.return');
            Route::post('/create-data-return-bahan-habis-pakai', [ReturnBahanHabisPakaiController::class, 'createDataReturnBhp'])->name('farmasi.create.data.return.bahan.habis.pakai');
        });

        // Route Depot
        Route::prefix('depot')->group(function () {
            Route::get('/', [DepotController::class, 'index'])->name('depot.index');
            Route::get('/get-data-depot', [DepotController::class, 'dataTables'])->name('get.data.depot.dataTables');
            Route::get('/get-data-obat-by-depot/{id}', [DepotController::class, 'getDataObatByDepotId'])->name('get.data.obat.by.depot.id');
            Route::get('/get-data-repair-obat-by-depot/{id}', [DepotController::class, 'getDataRepairStokObatByDepotId'])->name('get.data.repair.obat.by.depot.id');
            Route::post('/repair-stok-obat', [DepotController::class, 'repairStokObat'])->name('repair.stok.obat');
            Route::get('/get-data-repair-bhp-by-depot/{id}', [DepotController::class, 'getDataRepairStokBHPByDepotId'])->name('get.data.repair.bhp.by.depot.id');
            Route::post('/repair-stok-bhp', [DepotController::class, 'repairStokBHP'])->name('repair.stok.bhp');
        });

        Route::prefix('pesanan-dan-stok-masuk')->group(function () {
            Route::get('/', [PesananDanStokMasuk::class, 'index'])->name('pesanan.dan.stok.masuk.index');
            Route::get('/get-data-pesanan-dan-stok-masuk', [PesananDanStokMasuk::class, 'getData'])->name('pesanan.dan.stok.masuk.get.data');
        });

        // Route Brand Farmasi
        Route::get('/get-data-brand-farmasi', [BrandFarmasiController::class, 'getDataBrandFarmasi'])->name('get.data.brand.farmasi');
        Route::post('/create-data-brand-farmasi', [BrandFarmasiController::class, 'createDataBrandFarmasi'])->name('create.data.brand.farmasi');
        Route::post('/delete-data-brand-farmasi', [BrandFarmasiController::class, 'deleteDataBrandFarmasi'])->name('delete.data.brand.farmasi');

        // Route Jenis Obat
        Route::get('/get-data-jenis-obat', [JenisObatController::class, 'getDataJenisObat'])->name('get.data.jenis.obat');
        Route::post('/create-data-jenis-obat', [JenisObatController::class, 'createDataJenisObat'])->name('create.data.jenis.obat');
        Route::post('/delete-data-jenis-obat', [JenisObatController::class, 'deleteDataJenisObat'])->name('delete.data.jenis.obat');

        // Route Satuan Obat
        Route::get('/get-data-satuan-obat', [SatuanObatController::class, 'getDataSatuanObat'])->name('get.data.satuan.obat');
        Route::post('/create-data-satuan-obat', [SatuanObatController::class, 'createDataSatuanObat'])->name('create.data.satuan.obat');
        Route::post('/delete-data-satuan-obat', [SatuanObatController::class, 'deleteDataSatuanObat'])->name('delete.data.satuan.obat');

        // Route Tipe Depot
        Route::get('/get-data-tipe-depot', [TipeDepotController::class, 'getDataTipeDepot'])->name('get.data.tipe.depot');
        Route::post('/create-data-tipe-depot', [TipeDepotController::class, 'createDataTipeDepot'])->name('create.data.tipe.depot');
        Route::post('/delete-data-tipe-depot', [TipeDepotController::class, 'deleteDataTipeDepot'])->name('delete.data.tipe.depot');

        // Route Depot
        Route::get('/get-data-depot', [DepotController::class, 'getDataDepot'])->name('get.data.depot');
        Route::post('/create-data-depot', [DepotController::class, 'createDataDepot'])->name('create.data.depot');
        Route::post('/delete-data-depot', [DepotController::class, 'deleteDataDepot'])->name('delete.data.depot');

        // Route Supplier
        Route::get('/get-data-supplier', [SupplierController::class, 'getDataSupplier'])->name('get.data.supplier');
        Route::get('/get-data-supplier-by-id/{id}', [SupplierController::class, 'showDataSupplier'])->name('get.data.supplier.by.id');
        Route::post('/create-data-supplier', [SupplierController::class, 'createDataSupplier'])->name('create.data.supplier');
        Route::post('/delete-data-supplier', [SupplierController::class, 'deleteDataSupplier'])->name('delete.data.supplier');
        Route::post('/update-data-supplier', [SupplierController::class, 'updateDataSupplier'])->name('update.data.supplier');

        Route::prefix('pengambilan-obat')->group(function () {
            Route::get('/', [FarmasiPengambilanObatController::class, 'index'])->name('pengambilan.obat');
            Route::get('/get-data', [FarmasiPengambilanObatController::class, 'getDataResepObat'])->name('get.data.resep.obat');
            Route::get('/get-data-pasien', [FarmasiPengambilanObatController::class, 'searchPasien'])->name('pengambilan.obat.get.data.pasien');
            Route::get('/get-data-dokter', [FarmasiPengambilanObatController::class, 'searchDokter'])->name('pengambilan.obat.get.data.dokter');
            Route::get('/get-data-obat', [FarmasiPengambilanObatController::class, 'searchObat'])->name('pengambilan.obat.get.data.obat');
            Route::get('/get-data-depot-by-obat-id', [FarmasiPengambilanObatController::class, 'getDataDepotByObat'])->name('pengambilan.obat.get.data.depot.by.obat.id');
            Route::post('/create-data-resep-obat', [FarmasiPengambilanObatController::class, 'createDataResepObat'])->name('pengambilan.obat.create.data.resep.obat');
            Route::post('/update-status-resep', [FarmasiPengambilanObatController::class, 'updateStatusResep'])->name('update.status.resep');
            Route::get('/cetak-stiker-obat/{id}', [FarmasiPengambilanObatController::class, 'cetakStikerObat']);
            Route::get('/get-data-resep-obat-id/{id}', [FarmasiPengambilanObatController::class, 'getDataResepObatById'])->name('pengambilan.obat.get.data.resep.obat.by.id');
            Route::post('/update-data-resep-obat/{id}', [FarmasiPengambilanObatController::class, 'updateResepObat'])->name('pengambilan.obat.update.data.resep.obat');

            Route::get('get-data-resep-obat-detail/{id}', [FarmasiPengambilanObatController::class, 'getDataResepObatDetail'])->name('pengambilan.obat.get.data.resep.obat.detail');

            // Route Antrian Hari Ini Yang Sudah Selesai
            Route::get('/get-data-resep-obat-selesai', [FarmasiPengambilanObatController::class, 'getDataResepObatYangSudahSelesai'])->name('pengambilan.obat.get.data.resep.obat.selesai');
        });
    });
});

Route::middleware(['auth', 'role:Kasir'])->group(function () {
    Route::prefix('kasir')->group(function () {
        // Menu Dashboard
        Route::get('/dasboard', [KasirController::class, 'dashboard'])->name('kasir.dashboard');
        Route::get('/chart-transaksi', [KasirController::class, 'chartTransaksi'])->name('kasir.chart.transaksi');

        Route::get('/get-data-pemasukan', [KasirController::class, 'chartKeuangan'])->name('kasir.chart.keuangan');
        Route::get('/get-data-transaksi-hari-ini', [KasirController::class, 'totalTransaksiHariIni'])->name('kasir.get.data.pemasukan.hari.ini');
        Route::get('/get-data-total-keseluruhan-transaksi', [KasirController::class, 'totalKeseluruhanTransaksi'])->name('kasir.get.data.total.keseluruhan.transaksi');
        Route::get('/get-data-transaksi-obat-hari-ini', [KasirController::class, 'totalTransaksiObatHariIni'])->name('kasir.get.data.transaksi.obat.hari.ini');
        Route::get('/get-data-total-keseluruhan-transaksi-obat', [KasirController::class, 'totalKeseluruhanTransaksiObat'])->name('kasir.get.data.total.keseluruhan.transaksi.obat');

        Route::get('/pembayaran', [KasirController::class, 'index'])->name('kasir.pembayaran');

        Route::get('/insight-pembayaran', [KasirTransaksiInsightController::class, 'pembayaran'])->name('kasir.insight.pembayaran');

        Route::get('/insight/obat', [KasirTransaksiInsightController::class, 'obat'])
            ->name('kasir.insight.obat');

        Route::get('/insight/obat/{id}/detail', [KasirTransaksiInsightController::class, 'showDetailPenjualanObat'])->name('kasir.insight.detail.penjualan.obat');

        Route::get('/insight/layanan', [KasirTransaksiInsightController::class, 'layanan'])
            ->name('kasir.insight.layanan');

        Route::get('/insight/pembayaran/chart', [KasirTransaksiInsightController::class, 'chartPembayaran'])
            ->name('kasir.insight.pembayaran.chart');

        Route::get('/insight/obat/chart', [KasirTransaksiInsightController::class, 'chartObat'])
            ->name('kasir.insight.obat.chart');

        Route::get('/insight/layanan/chart', [KasirTransaksiInsightController::class, 'chartLayanan'])
            ->name('kasir.insight.layanan.chart');

        Route::get('/get-data-pembayaran', [KasirController::class, 'getDataPembayaran'])->name('get.data.pembayaran');
        Route::get('/transaksi/{kode_transaksi}', [KasirController::class, 'transaksi'])->name('kasir.transaksi');
        Route::get('/kwitansi/{kodeTransaksi}', [KasirController::class, 'showKwitansi'])->name('show.kwitansi');

        Route::post('/pembayaran/{pembayaran}/diskon/request', [DiskonApprovalController::class, 'requestApproval'])->name('kasir.pembayaran.diskon.request');
        Route::get('/pembayaran/{pembayaran}/diskon/status', [DiskonApprovalController::class, 'status'])->name('kasir.pembayaran.diskon.status');

        Route::post('/pembayaran-cash', [KasirController::class, 'transaksiCash'])->name('kasir.pembayaran.cash');
        Route::post('/pembayaran-transfer', [KasirController::class, 'transaksiTransfer'])->name('kasir.pembayaran.transfer');

        // CRUD Metode Pembayaran
        Route::get('/metode-pembayaran', [MetodePembayaranController::class, 'index'])->name('kasir.metode.pembayaran');
        Route::get('/get-data-metode-pembayaran', [MetodePembayaranController::class, 'getDataMetodePembayaran'])->name('get.data.metode.pembayaran');
        Route::post('/create-metode-pembayaran', [MetodePembayaranController::class, 'createData'])->name('kasir.crate.data.metode.pembayaran');
        Route::post('/update-metode-pembayaran', [MetodePembayaranController::class, 'updateData'])->name('kasir.update.data.metode.pembayaran');
        Route::post('/delete-metode-pembayaran/{id}', [MetodePembayaranController::class, 'deleteData'])->name('kasir.delete.data.metode.pembayaran');
        Route::get('/get-data-metode-pembayaran/{id}', [MetodePembayaranController::class, 'getDataMetodePembayaranById'])->name('get.data.metode.pembayaran.by.id');

        // Transaksi Obat
        Route::get('/get-data-transaksi-obat', [TransaksiObatController::class, 'getDataTransaksiObat'])->name('get.data.transaksi.obat');
        Route::get('/transaksi-obat/{kodeTransaksi}', [TransaksiObatController::class, 'transaksiObat'])->name('kasir.transaksi.obat');

        Route::post('/transaksi-obat/{penjualanObat}/diskon/request', [ApproveDiskonPenjualanObatController::class, 'requestApproval'])->name('kasir.transaksi.obat.diskon.request');
        Route::get('/transaksi-obat/{penjualanObat}/diskon/status', [ApproveDiskonPenjualanObatController::class, 'status'])->name('kasir.transaksi.obat.diskon.status');

        Route::post('/pembayaran-cash-transaksi-obat', [TransaksiObatController::class, 'transaksiCash'])->name('kasir.transaksi.obat.cash');
        Route::post('/pembayaran-transfer-transaksi-obat', [TransaksiObatController::class, 'transaksiTransfer'])->name('kasir.transaksi.obat.transfer');

        // Route Untuk Riwayat Transaksi Obat
        Route::prefix('riwayat-transaksi-obat')->group(function () {
            Route::get('/get-data', [TransaksiObatController::class, 'getDataRiwayatTransaksiObat'])->name('get.data.riwayat.transaksi.obat');
            Route::get('/kwitansi-transaksi-obat/{kodeTransaksi}', [TransaksiObatController::class, 'kwitansiTransaksiObat'])->name('get.show.kwitansi.transaksi.obat');
        });

        // Riwayat Transaksi
        Route::get('/riwayat-transaksi', [RiwayatTransaksiController::class, 'index'])->name('kasir.riwayat.transaksi');
        Route::get('/get-data-riwayat-pembayaran', [KasirController::class, 'getDataRiwayatPembayaran'])->name('get.data.riwayat.pembayaran');

        // Transaksi Layanan
        Route::prefix('transaksi-layanan')->group(function () {
            Route::get('/get-data', [TransaksiLayananController::class, 'getDataTransaksiLayanan'])->name('kasir.get.data.transaksi.layanan');
            Route::get('/detail-orderan/{kodeTransaksi}', [TransaksiLayananController::class, 'detailTransaksiLayanan'])->name('kasir.detail.orderan.transaksi.layanan');
            Route::post('/pembayaran/cash', [TransaksiLayananController::class, 'pembayaranLayananCash'])->name('kasir.pembayaran.cash.layanan');
            Route::post('/pembayaran/transfer', [TransaksiLayananController::class, 'pembayaranLayananTransfer'])->name('kasir.pembayaran.transfer.layanan');
            Route::post('/pembayaran/{orderLayanan}/diskon/request', [ApproveDiskonOrderLayananController::class, 'requestApproval'])->name('kasir.request.diskon.order.layanan');
            Route::get('/pembayaran/{orderLayanan}/diskon/status', [ApproveDiskonOrderLayananController::class, 'status'])->name('kasir.status.diskon.order.layanan');
        });

        Route::get('/transaksi-layanan/{kodeTransaksi}/proses', [TransaksiLayananController::class, 'prosesPembayaranLayanan'])->name('kasir.proses.pembayaran.layanan');

        Route::prefix('riwayat-transaksi-layanan')->group(function () {
            Route::get('/get-data', [TransaksiLayananController::class, 'getDataRiwayatTransaksiLayanan'])->name('kasir.get.data.riwaya.transaksi.layanan');
            Route::get('/kwitansi-transaksi-layanan/{kodeTransaksi}', [TransaksiLayananController::class, 'kwitansiTransaksiLayanan'])->name('kasir.show.kwitansi.transaksi.layanan');
        });

        // Route::get('/riwayat-transaksi-layanan', [TransaksiLayananController::class, 'getDataRiwayatTransaksiLayanan'])->name('kasir.get.data.riwayat.transaksi.layanan');

        // Route::get('/show-detail-transaksi-layanan/{kodeTransaksi}', [TransaksiLayananCofntroller::class, 'showDetailTransaksiLayanan'])->name('kasir.show.detail.transaksi.layanan');

        // Route::get('/proses-pembayaran-layanan/{kodeTransaksi}', [TransaksiLayananController::class, 'prosesPembayaranLayanan'])->name('kasir.proses.pembayaran.layanan');

        // Route::post('/layanan-pembayaran-cash', [TransaksiLayananController::class, 'pembayaranLayananCash'])->name('kasir.layanan.pembayaran.cash');
        // Route::post('/layanan-pembayaran-transfer', [TransaksiLayananController::class, 'pembayaranLayananTransfer'])->name('kasir.layanan.pembayaran.transfer');

        // Riwayat Transaksi Layanan
        // Route::get('/get-data-riwayat-transaksi-layanan', [TransaksiLayananController::class, 'getDataRiwayatTransaksiLayanan'])->name('kasir.get.data.riwayat.transaksi.layanan');
        // Route::get('/kwitansi-transaksi-layanan/{kodeTransaksi}', [TransaksiLayananController::class, 'kwitansiTransaksiLayanan'])->name('kasir.show.kwitansi.transaksi.layanan');

        // Delete Transaksi
        Route::delete('/pembayaran/{id}', [KasirController::class, 'deletePembayaran'])->name('kasir.pembayaran.delete');

        Route::prefix('/hutang-obat')->group(function () {
            Route::get('/', [HutangController::class, 'index'])->name('kasir.hutang');
            Route::get('/get-data-hutang-obat', [HutangController::class, 'getDataHutangObat'])->name('kasir.get.data.hutang.obat');
            Route::get('/get-data-detail-hutang-obat/{noFaktur}', [HutangController::class, 'getDataDetailHutangObat'])->name('kasir.get.data.detail.hutang.obat');
            Route::get('/pembayaran/{noFaktur}', [HutangController::class, 'halamanPembayaranHutangObat'])->name('kasir.pembayaran.hutang.obat');
            Route::post('/pembayaran-cash/{noFaktur}', [HutangController::class, 'transaksiCash'])->name('kasir.pembayaran.cash.hutang.obat');
            Route::post('/pembayaran-transfer/{noFaktur}', [HutangController::class, 'transaksiTransfer'])->name('kasir.pembayaran.transfer.hutang.obat');
        });

        Route::prefix('/riwayat-hutang')->group(function () {
            Route::get('/get-data-riwayat-hutang', [HutangController::class, 'getDataRiwayatHutang'])->name('kasir.get.data.riwayat.hutang');
            Route::get('/get-data-detail-riwayat-hutang/{noFaktur}', [HutangController::class, 'getDataDetailRiwayatHutang'])->name('kasir.get.data.detail.riwayat.hutang');
        });

        Route::prefix('/hutang-bahan-habis-pakai')->group(function () {
            Route::get('/', [HutangBahanHabisPakaiController::class, 'index'])->name('kasir.hutang.bahan.habis.pakai');
            Route::get('/get-data-hutang-bahan-habis-pakai', [HutangBahanHabisPakaiController::class, 'getDataHutangBahanHabisPakai'])->name('kasir.get.data.hutang.bahan.habis.pakai');
            Route::get('/get-data-detail-hutang-bahan-habis-pakai/{noFaktur}', [HutangBahanHabisPakaiController::class, 'getDataDetailHutangBahanHabisPakai'])->name('kasir.get.data.detail.hutang.bahan.habis.pakai');
            Route::get('/pembayaran/{noFaktur}', [HutangBahanHabisPakaiController::class, 'halamanPembayaranHutangBahanHabisPakai'])->name('kasir.pembayaran.hutang.bahan.habis.pakai');
            Route::post('/pembayaran-cash/{noFaktur}', [HutangBahanHabisPakaiController::class, 'transaksiCash'])->name('kasir.pembayaran.cash.hutang.bahan.habis.pakai');
            Route::post('/pembayaran-transfer/{noFaktur}', [HutangBahanHabisPakaiController::class, 'transaksiTransfer'])->name('kasir.pembayaran.transfer.hutang.bahan.habis.pakai');
        });

        Route::prefix('/riwayat-hutang-bahan-habis-pakai')->group(function () {
            Route::get('/get-data-riwayat-hutang-bahan-habis-pakai', [HutangBahanHabisPakaiController::class, 'getDataRiwayatHutangBahanHabisPakai'])->name('kasir.get.data.riwayat.hutang.bahan.habis.pakai');
            Route::get('/get-data-detail-riwayat-hutang-bahan-habis-pakai/{noFaktur}', [HutangBahanHabisPakaiController::class, 'getDataDetailRiwayatHutangBahanHabisPakai'])->name('kasir.get.data.detail.riwayat.hutang.bahan.habis.pakai');
        });

        // Piutang Obat
        Route::prefix('/piutang-obat')->group(function () {
            Route::get('/', [PiutangObatController::class, 'index'])->name('kasir.piutang.obat');
            Route::get('/get-data-piutang-obat', [PiutangObatController::class, 'getDataPiutangObat'])->name('kasir.get.data.piutang.obat');
            Route::get('/get-data-detail-piutang-obat/{noReferensi}', [PiutangObatController::class, 'getDetailPiutangObat'])->name('kasir.get.data.detail.piutang.obat');
        });

        // Piutang Bahan Habis Pakai
        Route::prefix('/piutang-bahan-habis-pakai')->group(function () {
            Route::get('/', [PiutangBahanHabisPakaiController::class, 'index'])->name('kasir.piutang.bahan.habis.pakai');
            Route::get('/get-data-piutang-bahan-habis-pakai', [PiutangBahanHabisPakaiController::class, 'getDataPiutangBahanHabisPakai'])->name('kasir.get.data.piutang.bahan.habis.pakai');
            Route::get('/get-data-detail-piutang-bahan-habis-pakai/{id}', [PiutangBahanHabisPakaiController::class, 'getDetailPiutangBahanHabisPakai'])->name('kasir.get.data.deatail.piutang.bahan.habis.pakai');
        });
    });
});

Route::middleware(['auth', 'role:Perawat'])->group(function () {
    Route::prefix('perawat')->group(function () {
        Route::get('/', [PerawatController::class, 'index'])->name('perawat.dashboard');
        Route::get('/chart', [PerawatController::class, 'chartDashboard'])->name('perawat.chart');

        Route::get('/kunjungan', [KunjunganController::class, 'index'])->name('perawat.kunjungan');
        Route::get('/getDataKunjunganHariIni', [KunjunganController::class, 'getDataKunjunganHariIni'])->name('perawat.get.data.kunjungan.hari.ini');
        Route::post('/updateStatusKunjunganKeEngaged/{id}', [KunjunganController::class, 'updateStatusKunjunganKeEngaged'])->name('perawat.update.status.kunjungan.ke.engaged');
        Route::get('/getDataKunjunganDenganStatusEngaged', [KunjunganController::class, 'getDataKunjunganDenganStatusEngaged'])->name('perawat.get.data.kunjungan.dengan.status.engaged');
        Route::get('/get-data-detail-kunjungan-engaged/{id}', [KunjunganController::class, 'detailKunjunganEngaged'])->name('get.data.detail.kunjungan.engaged');
        Route::get('/form-pengisian-vital-sign/{id}', [KunjunganController::class, 'formPengisianVitalSign'])->name('perawat.form.pengisian.vital.sign');
        Route::post('/submitDataVitalSignPasien/{id}', [KunjunganController::class, 'submitDataVitalSignPasien'])->name('perawat.submit.data.vital.sign.pasien');

        Route::prefix('order-lab')->group(function () {
            Route::get('/get-data-order-lab', [OrderLabController::class, 'getDataHasilLab'])->name('get.data.order.lab');
            Route::get('/input-hasil/{id}', [OrderLabController::class, 'inputHasil'])->name('input.hasil.order.lab');
            Route::post('/simpan-hasil-lab', [OrderLabController::class, 'simpanHasil'])->name('simpan.hasil.order.lab');
        });

        Route::prefix('order-radiologi')->group(function () {
            Route::get('/get-data-order-radiologi', [OrderRadiologiController::class, 'getDataOrderRadiologi'])->name('get.data.order.radiologi');
            Route::get('/input-hasil/{id}', [OrderRadiologiController::class, 'inputHasil'])->name('input.hasil.order.radiologi');
            Route::post('/simpan-hasil', [OrderRadiologiController::class, 'simpanHasil'])->name('simpan-hasil');
        });

        Route::prefix('riwayat-pemeriksaan')->name('riwayat-pemeriksaan.')->group(function () {
            // halaman utama (tab UI)
            Route::get('/', [RiwayatPemeriksaanController::class, 'index'])->name('index');

            Route::get('/radiologi/data', [RiwayatPemeriksaanController::class, 'dataRadiologi'])->name('radiologi.data');
            Route::get('/radiologi/{order}', [RiwayatPemeriksaanController::class, 'showRadiologi'])->name('radiologi.show');

            Route::get('/lab/data', [RiwayatPemeriksaanController::class, 'dataLab'])->name('lab.data');
            Route::get('/lab/{order}', [RiwayatPemeriksaanController::class, 'showLab'])->name('lab.show');

            Route::get('/emr/data', [RiwayatPemeriksaanController::class, 'dataEmr'])->name('emr.data');
            Route::get('/emr/{emr}', [RiwayatPemeriksaanController::class, 'showEmr'])->name('emr.show');
        });
    });
});

Route::middleware(['auth', 'superAdmin'])->prefix('super-admin')->group(function () {

    // delete data dokter
    Route::delete('/delete_dokter/{id}', [DokterController::class, 'deleteDokter'])->name('delete_dokter');

    // delete data pasien
    Route::delete('/delete_pasien/{id}', [PasienController::class, 'deletePasien'])->name('delete_pasien');

    // delete data farmasi
    Route::delete('/delete_farmasi/{id}', [FarmasiController::class, 'deleteFarmasi'])->name('delete_farmasi');

    // delete data perawat
    Route::post('/delete_perawat/{slug}', [ManagementPerawatController::class, 'deleteDataPerawat'])->name('delete.data.perawat');

    // delete data kasir
    Route::delete('/delete_kasir/{id}', [KasirController::class, 'deleteKasir'])->name('delete_kasir');

    Route::get('/diskon-approval', [DiskonApprovalManagerController::class, 'index'])->name('super.admin.diskon.index');
    Route::get('/diskon-approval/get-data-belum-approve', [DiskonApprovalManagerController::class, 'getDataBelumApprove']);
    Route::get('/diskon-approval/get-data-sudah-approve', [DiskonApprovalManagerController::class, 'getDataSudahApprove'])->name('super.admin.data.sudah.approve');

    Route::get('/diskon-approval/{approval}/detail-items', [DiskonApprovalManagerController::class, 'getDetailItems'])->name('super.admin.diskon.detail_items');
    Route::get('/diskon-approval/{approval}/detail-items-sudah-approve', [DiskonApprovalManagerController::class, 'getDetailSudahApprove'])->name('super.admin.diskon.detail_items.sudah.approve');

    Route::post('/diskon-approval/{approval}/approve', [DiskonApprovalManagerController::class, 'approve'])->name('super.admin.diskon.approve');
    Route::post('/diskon-approval/{approval}/reject', [DiskonApprovalManagerController::class, 'reject'])->name('super.admin.diskon.reject');

    // Approve Diskon Penjualan Obat
    Route::get('/approve-diskon-penjualan-obat', [ApproveDiskonPenjualanObatManagerController::class, 'index'])->name('super.admin.approve.diskon.penjualan.obat');
    Route::get('/approve-diskon-penjualan-obat/get-data-belum-approve', [ApproveDiskonPenjualanObatManagerController::class, 'getDataBelumApprove']);
    Route::get('/approve-diskon-penjualan-obat/get-data-sudah-approve', [ApproveDiskonPenjualanObatManagerController::class, 'getDataSudahApprove'])->name('super.admin.data.approve-diskon-penjualan-obat');

    Route::get('/approve-diskon-penjualan-obat/{approval}/detail-items', [ApproveDiskonPenjualanObatManagerController::class, 'getDetailItems'])->name('super.admin.diskon.penjualan.obat.detail_items');
    Route::get('/approve-diskon-penjualan-obat/{approval}/detail-items-sudah-approve', [ApproveDiskonPenjualanObatManagerController::class, 'getDetailSudahApprove'])->name('super.admin.diskon.penjualan.obat.detail_items.sudah.approve');

    Route::post('/approve-diskon-penjualan-obat/{approval}/approve', [ApproveDiskonPenjualanObatManagerController::class, 'approve'])->name('super.admin.approve.diskon.penjualan.obat.approve');
    Route::post('/approve-diskon-penjualan-obat/{approval}/reject', [ApproveDiskonPenjualanObatManagerController::class, 'reject'])->name('super.admin.reject.diskon.penjualan.obat');

    Route::get('index', [SuperAdminController::class, 'dashboard'])->name('super.admin.index');
    Route::get('/dashboard/chart-kunjungan', [SuperAdminController::class, 'chartKunjungan'])->name('super.admin.chart.kunjungan');
    Route::get('/dashboard/report-kunjungan/pdf', [SuperAdminController::class, 'reportKunjunganPdf'])->name('super.admin.report.kunjungan.pdf');
    Route::get('/dashboard/report-kunjungan/excel', [SuperAdminController::class, 'reportKunjunganExcel'])->name('super.admin.report.kunjungan.excel');

    Route::get('/pasien/insight', [PasienInsightController::class, 'index'])->name('super.admin.pasien.insight.index');
    Route::get('/pasien/insight/{id}', [PasienInsightController::class, 'show'])->name('super.admin.pasien.insight.show');
    Route::get('/pasien-hari-ini', [PasienHariIniController::class, 'index'])->name('super.admin.pasien.hari.ini.index');
    Route::get('/detail-pasien-hari-ini/{no_emr}', [PasienHariIniController::class, 'detail'])->name('super.admin.detail.pasien.hari.ini');

    Route::get('/transaksi/insight', [TransaksiInsightController::class, 'index'])->name('super.admin.transaksi.insight.index');
    Route::get('/transaksi/insight/{id}', [TransaksiInsightController::class, 'show'])->name('super.admin.transaksi.insight.show');

    Route::prefix('approve-diskon-order-layanan')->group(function () {
        Route::get('/', [ApproveDiskonOrderLayananManagerController::class, 'index'])->name('super.admin.approve.diskon.order.layanan.index');
        Route::get('/get-data-belum-approve', [ApproveDiskonOrderLayananManagerController::class, 'getDataBelumApprove'])->name('super.admin.get.data.diskon.order.layanan.belum.approve');
        Route::get('/get-data-sudah-approve', [ApproveDiskonOrderLayananManagerController::class, 'getDataSudahApprove'])->name('super.admin.get.data.diskon.order.layanan.sudah.approve');
        Route::get('/get-data-detail/{approval}', [ApproveDiskonOrderLayananManagerController::class, 'getDetailDiskonOrderLayanan'])->name('super.admin.get.data.detail.diskon.order.layanan.belum.approve');

        Route::post('/approve/{approval}', [ApproveDiskonOrderLayananManagerController::class, 'approve'])->name('super.admin.approve.diskon.order.layanan');
        Route::post('/reject/{approval}', [ApproveDiskonOrderLayananManagerController::class, 'reject'])->name('super.admin.reject.diskon.order.layanan');
    });
});

Route::get('/login-dokter', [AuthController::class, 'login'])->name('login.dokter');
Route::post('/proses-login-dokter', [AuthController::class, 'prosesLogin'])->name('proses.login.dokter');

Route::get('/is-global', [LayananController::class, 'isGlobal'])->name('layanan.is.global');

require __DIR__.'/auth.php';
