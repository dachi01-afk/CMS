<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RawatJalanController;
use App\Http\Controllers\Admin\RegistrasiController;
use App\Http\Controllers\Admin\EMRController;
use App\Http\Controllers\Admin\ApotekController;
use App\Http\Controllers\Admin\OfficeController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\KasirController;
use Illuminate\Support\Facades\Route;


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

Route::middleware('auth')->group(function () {
    Route::get('/profile',              [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',            [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',           [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/',                                     [DashboardController::class, 'index'])->name('index');
        Route::get('/chart-kunjungan',                      [DashboardController::class, 'getChartKunjungan'])->name('chart.kunjungan');
        Route::get('/getdashboardmetrics',                  [DashboardController::class, 'getDashboardMetrics'])->name('getdashboardmetrics');
        Route::get('/getdataantricepat',                  [DashboardController::class, 'getDataAntriCepat'])->name('getdataantricepat');
    });

    Route::prefix('rawat_jalan')->name('rawat_jalan.')->group(function () {
        Route::get('/',                 [RawatJalanController::class, 'index'])->name('index');
    });

    Route::prefix('registrasi')->name('registrasi.')->group(function () {
        Route::get('/',                 [RegistrasiController::class, 'index'])->name('index');
    });

    Route::prefix('emr')->name('emr.')->group(function () {
        Route::get('/',                 [EMRController::class, 'index'])->name('index');
    });

    Route::prefix('apotek')->name('apotek.')->group(function () {
        Route::get('/',                 [ApotekController::class, 'index'])->name('index');
    });

    Route::prefix('kasir')->name('kasir.')->group(function () {
        Route::get('/',                 [KasirController::class, 'index'])->name('index');
    });

    Route::prefix('office')->name('office.')->group(function () {
        Route::get('/',                 [OfficeController::class, 'index'])->name('index');
    });

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/',                 [SettingsController::class, 'index'])->name('index');
    });
});

require __DIR__ . '/auth.php';
