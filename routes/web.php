<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\PoliController;
use App\Http\Controllers\Api\APIWebController;
use App\Http\Controllers\Dokter\AuthController;
use App\Http\Controllers\Kasir\KasirController;
use App\Http\Controllers\Farmasi\ObatController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\LayananController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\JenisSpesialisController;
use App\Http\Controllers\Management\EMRController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Farmasi\FarmasiController;
use App\Http\Controllers\Management\UserController;
use App\Http\Controllers\Perawat\PerawatController;
use App\Http\Controllers\Testing\TestingController;
use App\Http\Controllers\Admin\PembayaranController;
use App\Http\Controllers\Farmasi\OrderObatController;
use App\Http\Controllers\Management\DokterController;
use App\Http\Controllers\Management\PasienController;
use App\Http\Controllers\Perawat\KunjunganController;
use App\Http\Controllers\Admin\OrderLayananController;
use App\Http\Controllers\Kasir\TransaksiObatController;
use App\Http\Controllers\Management\ApotekerController;
use App\Http\Controllers\Testing\TestingChartController;
use App\Http\Controllers\Admin\DataMedisPasienController;
use App\Http\Controllers\Admin\JadwalKunjunganController;
use App\Http\Controllers\Admin\KategoriLayananController;
use App\Http\Controllers\Admin\PengambilanObatController;
use App\Http\Controllers\Testing\TestingQRCodeController;
use App\Http\Controllers\Admin\PengaturanKlinikController;
use App\Http\Controllers\Kasir\MetodePembayaranController;
use App\Http\Controllers\Kasir\RiwayatTransaksiController;
use App\Http\Controllers\Kasir\TransaksiLayananController;
use App\Http\Controllers\Admin\ManajemenPenggunaController;
use App\Http\Controllers\Management\JadwalDokterController;
use App\Http\Controllers\Admin\AturJadwalKunjunganController;
use App\Http\Controllers\Apoteker\Obat\PenjualanObatController;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use App\Http\Controllers\Dokter\DokterController as DokterDokterController;
use App\Http\Controllers\Admin\DataMedisPasienController as AdminDataMedisPasienController;
use App\Http\Controllers\Farmasi\PengambilanObatController as FarmasiPengambilanObatController;

// Rest of your web routes remain the same...
Route::get('/')->middleware('checkAuth');

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

Route::prefix('/testing-qr-code')->group(function () {
    // Route::get('/', [QrCodeController::class, 'generate'])->name('testing.qr.code.index');
    Route::get('/qr/generate/all', [QrCodeController::class, 'generateAll'])->name('qr.generate.all');
    Route::get('/qr/generate/{id}', [QrCodeController::class, 'generate'])->name('qr.generate');
    Route::get('/pasien/{id}/qr-view', [QrCodeController::class, 'showPasien'])->name('qr.show');

    // Route::get('/emr/generate-no-rm', [EMRController::class, 'generateAll'])->name('emr.generate');

    Route::get('/generate-no-emr', [PasienController::class, 'generateNoEmrPasien']);
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



////////// ROLE DOKTER ///////////
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

Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/chart_kunjungan', [DashboardController::class, 'getChartKunjungan'])->name('chart_kunjungan');
        Route::get('/total_dokter', [DashboardController::class, 'getTotalDokter'])->name('total_dokter');
        Route::get('/total_pasien', [DashboardController::class, 'getTotalPasien'])->name('total_pasien');
        Route::get('/total_farmasi', [DashboardController::class, 'getTotalFarmasi'])->name('total_farmasi');
        Route::get('/stok_obat', [DashboardController::class, 'getStokObat'])->name('stok_obat');
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

        // crud dokter
        Route::get('/data_dokter', [ManajemenPenggunaController::class, 'dataDokter'])->name('data_dokter');
        Route::get('/get_dokter_by_id/{id}', [DokterController::class, 'getDokterById'])->name('get_dokter_by_id');
        Route::post('/add_dokter', [DokterController::class, 'createDokter'])->name('add_dokter');
        Route::post('/update_dokter/', [DokterController::class, 'updateDokter'])->name('update_dokter');
        Route::delete('/delete_dokter/{id}', [DokterController::class, 'deleteDokter'])->name('delete_dokter');

        // crud farmasi
        Route::get('/data_farmasi', [ManajemenPenggunaController::class, 'dataFarmasi'])->name('data_farmasi');
        Route::post('/add_farmasi', [FarmasiController::class, 'createFarmasi'])->name('add_farmasi');
        Route::get('/get_farmasi_by_id/{id}', [FarmasiController::class, 'getFarmasiById'])->name('get_farmasi_by_id');
        Route::put('/update_farmasi/{id}', [FarmasiController::class, 'updateFarmasi'])->name('update_farmasi');
        Route::delete('/delete_farmasi/{id}', [FarmasiController::class, 'deleteFarmasi'])->name('delete_farmasi');

        // crud perawat
        Route::get('/data_perawat', [ManajemenPenggunaController::class, 'dataPerawat'])->name('data_perawat');
        Route::post('/add_perawat', [PerawatController::class, 'createPerawat'])->name('add_perawat');
        Route::get('/get_perawat_by_id/{id}', [PerawatController::class, 'getPerawatById'])->name('get_perawat_by_id');
        Route::get('/list_dokter', [PerawatController::class, 'listDokter']);
        Route::get('/dokter/{dokterId}/polis', [PerawatController::class, 'listPoliByDokter']);
        Route::put('/update_perawat/{id}', [PerawatController::class, 'updatePerawat'])->name('update_perawat');
        Route::delete('/delete_perawat/{id}', [PerawatController::class, 'deletePerawat'])->name('delete_perawat');

        // crud kasir
        Route::get('/data_kasir', [ManajemenPenggunaController::class, 'dataKasir'])->name('data_kasir');
        Route::post('/add_kasir', [KasirController::class, 'createKasir'])->name('add_kasir');
        Route::get('/get_kasir_by_id/{id}', [KasirController::class, 'getKasirById'])->name('get_kasir_by_id');
        Route::put('/update_kasir/{id}', [KasirController::class, 'updateKasir'])->name('update_kasir');
        Route::delete('/delete_kasir/{id}', [KasirController::class, 'deleteKasir'])->name('delete_kasir');

        // crud pasien
        Route::get('/data_pasien', [ManajemenPenggunaController::class, 'dataPasien'])->name('data_pasien');
        Route::get('/get_pasien_by_id/{id}', [PasienController::class, 'getPasienById'])->name('get_pasien_by_id');
        Route::post('/add_pasien', [PasienController::class, 'createPasien'])->name('add_pasien');
        Route::put('/update_pasien/{id}', [PasienController::class, 'updatePasien'])->name('update_pasien');
        Route::delete('/delete_pasien/{id}', [PasienController::class, 'deletePasien'])->name('delete_pasien');
        Route::delete('/search', [PasienController::class, 'search'])->name('pasien');
        Route::get('/cetak-stiker-pasien/{noEMR}', [PasienController::class, 'cetakStiker'])->name('cetak.stiker.pasien');
        Route::get('/show-detail-data-pasien/{noEMR}', [PasienController::class, 'showPasien'])->name('show.detail.pasien');
    });

    Route::prefix('pengaturan_klinik')->name('pengaturan_klinik.')->group(function () {
        Route::get('/', [PengaturanKlinikController::class, 'index'])->name('index');

        // crud obat
        Route::get('/data_obat', [PengaturanKlinikController::class, 'dataObat'])->name('data_obat');
        Route::post('/add_obat', [ObatController::class, 'createObat'])->name('add_obat');
        Route::get('/get_obat_by_id/{id}', [ObatController::class, 'getObatById'])->name('get_obat_by_id');
        Route::put('/update_obat/{id}', [ObatController::class, 'updateObat'])->name('update_obat');
        Route::delete('/delete_obat/{id}', [ObatController::class, 'deleteObat'])->name('delete_obat');

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

    // Route::prefix('pengambilan_obat')->group(function () {
    //     Route::get('/', [PengambilanObatController::class, 'index'])->name('pengambilan.obat');
    //     Route::get('/get-data', [PengambilanObatController::class, 'getDataResepObat'])->name('get.data.resep.obat');
    //     Route::post('/update-status-resep-obat', [PengambilanObatController::class, 'updateStatusResepObat'])->name('update.status.resep.obat');
    // });

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
    });

    Route::prefix('jadwal_kunjungan')->name('jadwal_kunjungan.')->group(function () {
        Route::get('/',         [JadwalKunjunganController::class, 'index'])->name('index');
        Route::post('/create',         [JadwalKunjunganController::class, 'store'])->name('create');
        Route::get('/search', [JadwalKunjunganController::class, 'search'])->name('pasien');

        Route::get('/waiting', [JadwalKunjunganController::class, 'waiting'])->name('waiting');
        Route::post('/updateKunjungan/{id}', [JadwalKunjunganController::class, 'updateDataKunjungan'])->name('update.kunjungan');
        Route::post('/update-status/{id}', [JadwalKunjunganController::class, 'updateStatus'])->name('update_status');

        Route::get('/masa-depan', [JadwalKunjunganController::class, 'masaDepan'])->name('masa.depan');

        Route::get('/get-data-KYAD/{id}', [JadwalKunjunganController::class, 'getDataKYAD']);

        Route::post('/batalkan-kunjungan/{id}', [JadwalKunjunganController::class, 'batalkanKunjungan']);
    });

    // Menu Order Layanan
    Route::prefix('/order-layanan')->group(function () {
        Route::get('/', [OrderLayananController::class, 'index'])->name('order.layanan.index');
        Route::get('/get-data-order-layanan', [OrderLayananController::class, 'getDataOrderLayanan'])->name('order.layanan.get.data.order.layanan');
        Route::get('/get-data-pasien', [OrderLayananController::class, 'searchPasien'])->name('order.layanan.get.data.pasien');
        Route::get('/get-data-jadwal-dokter-hari-ini', [OrderLayananController::class, 'getJadwalDokterHariIni'])->name('order.layanan.get.data.jadwal.dokter.hari.ini');
        Route::post('/create-data-order-layanan', [OrderLayananController::class, 'createDataOrderLayanan'])->name('order.layanan.create.data.order.layanan');
        Route::get('/get-data-order-layanan/{id}', [OrderLayananController::class, 'getDataOrderLayananById'])->name('order.layanan.get.data.order.layanan.by.id');
        Route::post('/update-data-order-layanan', [OrderLayananController::class, 'updateDataOrderLayanan'])->name('order.layanan.update.data.order.layanan');
        Route::post('/delete-data-order-layanan/{id}', [OrderLayananController::class, 'deleteDataOrderLayanan'])->name('order.layanan.delete.data.order.layanan');
    });
});

Route::middleware(['auth', 'role:Farmasi'])->group(function () {

    Route::prefix('farmasi')->group(function () {
        Route::get('/dashboard', [FarmasiController::class, 'index'])->name('farmasi.dashboard');

        Route::get('/chart/penjualan-obat', [FarmasiController::class, 'chartPenjualanObat'])->name('chart.penjualan');
        Route::get('/get-jumlah-stok-obat', [FarmasiController::class, 'getTotalObat'])->name('get.jumlah.stok.obat');
        Route::get('/get-jumlah-penjualan-obat-hari-ini', [FarmasiController::class, 'getJumlahPenjualanObatHariIni'])->name('get.jumlah.penjualan.obat.hari.ini');
        Route::get('/get-jumlah-keseluruhan-penjualan-obat', [FarmasiController::class, 'getJumlahKeseluruhanTransaksiObat'])->name('get.jumlah.keseluruhan.penjualan.obat');

        Route::prefix('obat')->group(function () {
            Route::get('/', [ObatController::class, 'index'])->name('obat.index');
            Route::get('/get-data-obat', [ObatController::class, 'getDataObat'])->name('obat.get.data.obat');
            Route::post('/create-data-obat', [ObatController::class, 'createObat'])->name('obat.create');
            Route::get('/get-data-obat-by/{id}', [ObatController::class, 'getObatById'])->name('obat.get.data.by.id');
            Route::post('update-data-obat/{id}', [ObatController::class, 'updateObat'])->name('obat.update');
            Route::delete('/delete-data-obat/{id}', [ObatController::class, 'deleteObat'])->name("obat.delete");
            // Route::get('jual-obat', [ApotekerController::class, 'index'])->name('obat.jual.obat');

            Route::get('/get-data-penjualan-obat', [OrderObatController::class, 'getDataPenjualanObat'])->name('obat.penjualan.obat');
            Route::get('/search-data-pasien', [OrderObatController::class, 'search'])->name('obat.search.data.pasien');
            Route::get('/search-data-obat', [OrderObatController::class, 'searchObat'])->name('obat.search.data.obat');
            Route::post('/pesan-obat', [OrderObatController::class, 'pesanObat'])->name('obat.pesan.obat');
            Route::get('/resep-aktif', [OrderObatController::class, 'ajaxResepAktif'])->name('resep.aktif');
        });

        Route::prefix('pengambilan-obat')->group(function () {
            Route::get('/', [FarmasiPengambilanObatController::class, 'index'])->name('pengambilan.obat');
            Route::get('/get-data', [FarmasiPengambilanObatController::class, 'getDataResepObat'])->name('get.data.resep.obat');
            Route::post('/update-status-resep-obat', [FarmasiPengambilanObatController::class, 'updateStatusResepObat'])->name('update.status.resep.obat');
            Route::get('/cetak-stiker-obat/{id}', [FarmasiPengambilanObatController::class, 'cetakStikerObat']);
        });
    });
});

Route::middleware(['auth', 'role:Kasir'])->group(function () {
    Route::prefix('kasir')->group(function () {
        // Menu Dashboard
        Route::get('/dasboard', [KasirController::class, 'dashboard'])->name('kasir.dashboard');
        Route::get('/get-data-pemasukan', [KasirController::class, 'chartKeuangan'])->name('kasir.chart.keuangan');
        Route::get('/get-data-transaksi-hari-ini', [KasirController::class, 'totalTransaksiHariIni'])->name('kasir.get.data.pemasukan.hari.ini');
        Route::get('/get-data-total-keseluruhan-transaksi', [KasirController::class, 'totalKeseluruhanTransaksi'])->name('kasir.get.data.total.keseluruhan.transaksi');
        Route::get('/get-data-transaksi-obat-hari-ini', [KasirController::class, 'totalTransaksiObatHariIni'])->name('kasir.get.data.transaksi.obat.hari.ini');
        Route::get('/get-data-total-keseluruhan-transaksi-obat', [KasirController::class, 'totalKeseluruhanTransaksiObat'])->name('kasir.get.data.total.keseluruhan.transaksi.obat');


        Route::get('/pembayaran', [KasirController::class, 'index'])->name('kasir.pembayaran');
        Route::get('/get-data-pembayaran', [KasirController::class, 'getDataPembayaran'])->name('get.data.pembayaran');
        Route::get('/transaksi/{kode_transaksi}', [KasirController::class, 'transaksi'])->name('kasir.transaksi');
        Route::get('/kwitansi/{kodeTransaksi}', [KasirController::class, 'showKwitansi'])->name('show.kwitansi');

        Route::post('/pembayaran-cash', [KasirController::class, 'transaksiCash'])->name('kasir.pembayaran.cash');
        Route::post('/pembayaran-transfer', [KasirController::class, 'transaksiTransfer'])->name('kasir.pembayaran.transfer');

        // CRUD Metode Pembayaran 
        Route::get('/metode-pembayaran', [MetodePembayaranController::class, 'index'])->name('kasir.metode.pembayaran');
        Route::get('/get-data-metode-pembayaran', [MetodePembayaranController::class, 'getDataMetodePembayaran'])->name('get.data.metode.pembayaran');
        Route::post('/create-metode-pembayaran', [MetodePembayaranController::class, 'createData'])->name('kasir.crate.data.metode.pembayaran');
        Route::post('/update-metode-pembayaran', [MetodePembayaranController::class, 'updateData'])->name('kasir.update.data.metode.pembayaran');
        Route::post('/delete-metode-pembayaran/{id}', [MetodePembayaranController::class, 'deleteData'])->name('kasir.delete.data.metode.pembayaran');
        Route::get('/get-data-metode-pembayaran/{id}', [MetodePembayaranController::class, 'getDataMetodePembayaranById'])->name('get.data.metode.pembayaran.by.id');


        Route::get('/get-data-transaksi-obat', [TransaksiObatController::class, 'getDataTransaksiObat'])->name('get.data.transaksi.obat');
        Route::get('/transaksi-obat/{kodeTransaksi}', [TransaksiObatController::class, 'transaksiObat'])->name('kasir.transaksi.obat');

        Route::post('/pembayaran-cash-transaksi-obat', [TransaksiObatController::class, 'transaksiCash'])->name('kasir.transaksi.obat.cash');
        Route::post('/pembayaran-transfer-transaksi-obat', [TransaksiObatController::class, 'transaksiTransfer'])->name('kasir.transaksi.obat.transfer');

        // Route Untuk Riwayat Transaksi Obat
        Route::get('/get-data-riwayat-transaksi-obat', [PenjualanObatController::class, 'getDataRiwayatTransaksiObat'])->name('get.data.riwayat.transaksi.obat');

        // Riwayat Transaksi 
        Route::get('/riwayat-transaksi', [RiwayatTransaksiController::class, 'index'])->name('kasir.riwayat.transaksi');
        Route::get('/get-data-riwayat-pembayaran', [KasirController::class, 'getDataRiwayatPembayaran'])->name('get.data.riwayat.pembayaran');

        // Transaksi Layanan 
        Route::get('/get-data-transaksi-layanan', [TransaksiLayananController::class, 'getDataTransaksiLayanan'])->name('kasir.get.data.transaksi.layanan');
        Route::get('/show-detail-transaksi-layanan/{kodeTransaksi}', [TransaksiLayananController::class, 'showDetailTransaksiLayanan'])->name('kasir.show.detail.transaksi.layanan');
        Route::get('/proses-pembayaran-layanan/{kodeTransaksi}', [TransaksiLayananController::class, 'prosesPembayaranLayanan'])->name('kasir.proses.pembayaran.layanan');
        Route::post('/proses-pembayaran-layanan', [TransaksiLayananController::class, 'submitPembayaranLayanan'])->name('kasir.submit.pembayaran.layanan');
        Route::post('/layanan-pembayaran-cash', [TransaksiLayananController::class, 'pembayaranLayananCash'])->name('kasir.layanan.pembayaran.cash');
        Route::post('/layanan-pembayaran-transfer', [TransaksiLayananController::class, 'pembayaranLayananTransfer'])->name('kasir.layanan.pembayaran.transfer');

        // Riwayat Transaksi Layanan 
        Route::get('/get-data-riwayat-transaksi-layanan', [TransaksiLayananController::class, 'getDataRiwayatTransaksiLayanan'])->name('kasir.get.data.riwayat.transaksi.layanan');
        Route::get('/kwitansi-transaksi-layanan/{kodeTransaksi}', [TransaksiLayananController::class, 'kwitansiTransaksiLayanan'])->name('kasir.show.kwitansi.transaksi.layanan');
    });
});

Route::middleware(['auth', 'role:Perawat'])->group(function () {
    Route::prefix('perawat')->group(function () {
        Route::get('/dashboard', [PerawatController::class, 'dashboard'])->name('perawat.dashboard');

        Route::get('/kunjungan', [KunjunganController::class, 'index'])->name('perawat.kunjungan');
        Route::get('/getDataKunjunganHariIni', [KunjunganController::class, 'getDataKunjunganHariIni'])->name('perawat.get.data.kunjungan.hari.ini');
        Route::post('/updateStatusKunjunganKeEngaged/{id}', [KunjunganController::class, 'updateStatusKunjunganKeEngaged'])->name('perawat.update.status.kunjungan.ke.engaged');
        Route::get('/getDataKunjunganDenganStatusEngaged', [KunjunganController::class, 'getDataKunjunganDenganStatusEngaged'])->name('perawat.get.data.kunjungan.dengan.status.engaged');
        Route::get('/form-pengisian-vital-sign/{id}', [KunjunganController::class, 'formPengisianVitalSign'])->name('perawat.form.pengisian.vital.sign');
        Route::post('/submitDataVitalSignPasien/{id}', [KunjunganController::class, 'submitDataVitalSignPasien'])->name('perawat.submit.data.vital.sign.pasien');
    });
});

Route::get('/halo', function () {
    $nama = 'David Sebastian';
    return view('testing', compact('nama'));
});

Route::get('/login-dokter', [AuthController::class, 'login'])->name('login.dokter');
Route::post('/proses-login-dokter', [AuthController::class, 'prosesLogin'])->name('proses.login.dokter');

require __DIR__ . '/auth.php';
