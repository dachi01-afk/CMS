<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Dokter\AuthController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Management\UserController;
use App\Http\Controllers\Admin\DataMedisPasienController;
use App\Http\Controllers\Admin\PengaturanKlinikController;
use App\Http\Controllers\Admin\ManajemenPenggunaController;
use App\Http\Controllers\Management\ApotekerController;
use App\Http\Controllers\Management\DokterController;
use App\Http\Controllers\Management\JadwalDokterController;
use App\Http\Controllers\Management\ObatController;
use App\Http\Controllers\Management\PasienController;

// testing
// Route::get('testing', function () {
//     return view('mycomponents.layout');
// });

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware([])->group(function () {
    Route::get('/profile',              [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',            [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',           [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/',                                     [DashboardController::class, 'index'])->name('index');
        Route::get('/chart-kunjungan',                      [DashboardController::class, 'getChartKunjungan'])->name('chart.kunjungan');
        Route::get('/getdashboardmetrics',                  [DashboardController::class, 'getDashboardMetrics'])->name('getdashboardmetrics');
        Route::get('/getdataantricepat',                    [DashboardController::class, 'getDataAntriCepat'])->name('getdataantricepat');
    });

    Route::prefix('manajemen_pengguna')->name('manajemen_pengguna.')->group(function () {
        Route::get('/',                                     [ManajemenPenggunaController::class, 'index'])->name('index');

        // crud user
        Route::get('/data_user',                            [ManajemenPenggunaController::class, 'dataUser'])->name('data_user');
        Route::post('/add_user',                            [UserController::class, 'createUser'])->name('add_user');
        Route::get('/get_user_by_id/{id}',                  [UserController::class, 'getUserById'])->name('get_user_by_id');
        Route::put('/update_user/{id}',                     [UserController::class, 'updateUser'])->name('update_user');
        Route::delete('/delete_user{id}',                   [UserController::class, 'deleteUser'])->name('delete_user');

        // crud dokter
        Route::get('/data_dokter',                          [ManajemenPenggunaController::class, 'dataDokter'])->name('data_dokter');
        Route::get('/get_dokter_by_id/{id}',                [DokterController::class, 'getDokterById'])->name('get_dokter_by_id');
        Route::post('/add_dokter',                          [DokterController::class, 'createDokter'])->name('add_dokter');
        Route::put('/update_dokter/{id}',                   [DokterController::class, 'updateDokter'])->name('update_dokter');
        Route::delete('/delete_dokter/{id}',                [DokterController::class, 'deleteDokter'])->name('delete_dokter');

        // crud pasien
        Route::get('/data_pasien',                          [ManajemenPenggunaController::class, 'dataPasien'])->name('data_pasien');
        Route::get('/get_pasien_by_id/{id}',                [PasienController::class, 'getPasienById'])->name('get_pasien_by_id');
        Route::post('/add_pasien',                          [PasienController::class, 'createPasien'])->name('add_pasien');
        Route::put('/update_pasien/{id}',                   [PasienController::class, 'updatePasien'])->name('update_pasien');
        Route::delete('/delete_pasien/{id}',                [PasienController::class, 'deletePasien'])->name('delete_pasien');

        // crud apoteker
        Route::get('/data_apoteker',                        [ManajemenPenggunaController::class, 'dataApoteker'])->name('data_apoteker');
        Route::get('/get_apoteker_by_id/{id}',              [ApotekerController::class, 'getApotekerById'])->name('get_apoteker_by_id');
        Route::post('/add_apoteker',                        [ApotekerController::class, 'createApoteker'])->name('add_apoteker');
        Route::put('/update_apoteker/{id}',                 [ApotekerController::class, 'updateApoteker'])->name('update_apoteker');
        Route::delete('/delete_apoteker/{id}',              [ApotekerController::class, 'deleteApoteker'])->name('delete_apoteker');
    });

    Route::prefix('pengaturan_klinik')->name('pengaturan_klinik.')->group(function () {
        Route::get('/',                                     [PengaturanKlinikController::class, 'index'])->name('index');

        // crud obat
        Route::get('/data_obat',                            [PengaturanKlinikController::class, 'dataObat'])->name('data_obat');
        Route::post('/add_obat',                            [ObatController::class, 'createObat'])->name('add_obat');
        Route::get('/get_obat_by_id/{id}',                       [ObatController::class, 'getObatById'])->name('get_obat_by_id');
        Route::put('/update_obat/{id}',                          [ObatController::class, 'updateObat'])->name('update_obat');
        Route::delete('/delete_obat/{id}',                       [ObatController::class, 'deleteObat'])->name('delete_obat');

        // jadwal dokter
        Route::get('/jadwal_dokter',                        [PengaturanKlinikController::class, 'dataJadwalDokter'])->name('jadwal_dokter');
        Route::post('/add_jadwal_dokter',                   [JadwalDokterController::class, 'createJadwalDokter'])->name('add_jadwal_dokter');
        Route::get('/get_jadwal_dokter_by_id/{id}',         [JadwalDokterController::class, 'getJadwalDokterById'])->name('get_jadwal_dokter_by_id');
        Route::put('/update_jadwal_dokter/{id}',                   [JadwalDokterController::class, 'updateJadwalDokter'])->name('update_jadwal_dokter');
        Route::delete('/delete_jadwal_dokter/{id}',                [JadwalDokterController::class, 'deleteJadwalDokter'])->name('delete_jadwal_dokter');
    });

    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/',                                     [LaporanController::class, 'index'])->name('index');

        // laporan kunjungna
        Route::get('/laporan_kunjungan',                    [LaporanController::class, 'dataKunjungan'])->name('laporan_kunjungan');

        // laporan keuangan
        Route::get('/laporan_keuangan',                     [LaporanController::class, 'dataPembayaran'])->name('laporan_keuangan');

        // laporan transaksi apt
        Route::get('/laporan_transaksi_apoteker',           [LaporanController::class, 'dataTransaksiApoteker'])->name('laporan_transaksi_apoteker');

        // laporan administrasi
        Route::get('/laporan_administrasi',                 [LaporanController::class, 'dataAdministrasi'])->name('laporan_administrasi');
    });

    Route::prefix('data_medis_pasien')->name('data_medis_pasien.')->group(function () {
        Route::get('/',                                     [DataMedisPasienController::class, 'index'])->name('index');

        // laporan emr
        Route::get('/laporan_rekam_medis',                  [DataMedisPasienController::class, 'dataRekamMedis'])->name('laporan_rekam_medis');

        // data diagnosa dan konsultasi
        Route::get('/diagnosa_dan_konsultasi',              [DataMedisPasienController::class, 'dataKonsultasi'])->name('data_lab');

        // data hasil lab
        Route::get('/hasil_lab',                            [DataMedisPasienController::class, 'dataLab'])->name('hasil_lab');
    });


    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/',                 [SettingsController::class, 'index'])->name('index');
    });
});

Route::get('/login-dokter', [AuthController::class, 'login'])->name('login.dokter');
Route::post('/proses-login-dokter', [AuthController::class, 'prosesLogin'])->name('proses.login.dokter');


require __DIR__ . '/auth.php';
