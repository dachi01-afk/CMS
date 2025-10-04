<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DataMedisPasienController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\ManajemenPenggunaController;
use App\Http\Controllers\Admin\PengaturanKlinikController;

// testing
// Route::get('testing', function () {
//     return view('mycomponents.layout');
// });

Route::get('/', function () {
    return redirect()->route('login');
});

// Route::get('/dashboard', function () {
//     return view('admin.dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware([])->group(function () {
    Route::get('/profile',              [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',            [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',           [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/',                                     [DashboardController::class, 'index'])->name('index');
        Route::get('/chart-kunjungan',                      [DashboardController::class, 'getChartKunjungan'])->name('chart.kunjungan');
        Route::get('/getdashboardmetrics',                  [DashboardController::class, 'getDashboardMetrics'])->name('getdashboardmetrics');
        Route::get('/getdataantricepat',                    [DashboardController::class, 'getDataAntriCepat'])->name('getdataantricepat');
    });

    Route::prefix('manajemen_pengguna')->name('manajemen_pengguna.')->group(function () {
        Route::get('/',                 [ManajemenPenggunaController::class, 'index'])->name('index');
    });

    Route::prefix('pengaturan_klinik')->name('pengaturan_klinik.')->group(function () {
        Route::get('/',                 [PengaturanKlinikController::class, 'index'])->name('index');
    });

    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/',                 [LaporanController::class, 'index'])->name('index');
    });

    Route::prefix('data_medis_pasien')->name('data_medis_pasien.')->group(function () {
        Route::get('/',                 [DataMedisPasienController::class, 'index'])->name('index');
    });


    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/',                 [SettingsController::class, 'index'])->name('index');
    });
});


require __DIR__ . '/auth.php';
