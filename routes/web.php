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
use App\Http\Controllers\Api\APIWebController;
use App\Http\Controllers\Dokter\DokterController as DokterDokterController;
use App\Http\Controllers\Management\ApotekerController;
use App\Http\Controllers\Management\DokterController;
use App\Http\Controllers\Management\JadwalDokterController;
use App\Http\Controllers\Management\ObatController;
use App\Http\Controllers\Management\PasienController;
use App\Http\Controllers\Testing\TestingController;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// API Routes - Enhanced for better booking functionality
// Route::prefix('api')->withoutMiddleware(['web'])->group(function () {
//     // Public routes (tidak perlu authentication)
//     Route::post('/check-availability', [App\Http\Controllers\Auth\AuthController::class, 'checkAvailability']);
//     Route::post('/register', [App\Http\Controllers\Auth\AuthController::class, 'register']);
//     Route::post('/login', [App\Http\Controllers\Auth\AuthController::class, 'login']);

//     // ADD THESE NEW ROUTES - Place them before the middleware group
//     Route::get('/doctors-with-specialties', [App\Http\Controllers\Auth\AuthController::class, 'getDoctorsWithSpecialties']);
//     Route::get('/specialties', [App\Http\Controllers\Auth\AuthController::class, 'getSpecialties']);
//     Route::get('/doctor-schedules', [App\Http\Controllers\Auth\AuthController::class, 'getDoctorSchedules']);

//     // Forgot Password routes
//     Route::prefix('forgot-password')->group(function () {
//         Route::post('/send-otp', [App\Http\Controllers\Auth\AuthController::class, 'sendOTP']);
//         Route::post('/verify-otp', [App\Http\Controllers\Auth\AuthController::class, 'verifyOTP']);
//         Route::post('/reset', [App\Http\Controllers\Auth\AuthController::class, 'resetPassword']);
//     });

//     // Forgot Username routes 
//     Route::prefix('forgot-username')->group(function () {
//         Route::post('/send-otp', [App\Http\Controllers\Auth\AuthController::class, 'sendUsernameOTP']);
//         Route::post('/verify-otp', [App\Http\Controllers\Auth\AuthController::class, 'verifyUsernameOTP']);
//         Route::post('/update', [App\Http\Controllers\Auth\AuthController::class, 'updateUsername']);
//     });

//     // Protected routes (perlu authentication)
//     Route::middleware('auth:sanctum')->group(function () {
//         Route::post('/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout']);
//         Route::get('/profile', [App\Http\Controllers\Auth\AuthController::class, 'profile']);
//         Route::put('/profile', [App\Http\Controllers\Auth\AuthController::class, 'updateProfile']); 

//         // CHANGED: Booking routes inside auth middleware
//         Route::prefix('booking')->group(function () {
//             // UPDATED: Use AuthController instead of APIController
//             Route::post('/schedule', [App\Http\Controllers\Auth\AuthController::class, 'createKunjungan']);
//             Route::get('/check-availability/{dokterId}/{tanggal}', [App\Http\Controllers\Auth\AuthController::class, 'checkDoctorAvailability']);
//             Route::get('/my-appointments', [App\Http\Controllers\Auth\AuthController::class, 'getMyAppointments']);
//         });

//         // Appointment management routes
//         Route::prefix('appointment')->group(function () {
//             Route::put('/cancel/{kunjunganId}', [App\Http\Controllers\Auth\AuthController::class, 'cancelAppointment']);
//             Route::put('/reschedule/{kunjunganId}', [App\Http\Controllers\Auth\AuthController::class, 'rescheduleAppointment']);
//         });

//         // Routes untuk pasien
//         Route::get('/emr-pasien/{id}', [App\Http\Controllers\Auth\AuthController::class, 'getAllEmrPasien']);
//         Route::get('/kunjungan-detail/{kunjunganId}', [App\Http\Controllers\Auth\AuthController::class, 'getKunjunganDetail']);

//         // Routes untuk dokter
//         Route::prefix('dokter')->group(function () {
//             Route::get('/dashboard-stats', [App\Http\Controllers\Auth\AuthController::class, 'getDokterDashboardStats']);
//             Route::get('/today-patients', [App\Http\Controllers\Auth\AuthController::class, 'getTodayPatients']);
//             Route::put('/patient-status/{kunjunganId}', [App\Http\Controllers\Auth\AuthController::class, 'updatePatientStatus']);
//             Route::post('/submit-examination/{kunjunganId}', [App\Http\Controllers\Auth\AuthController::class, 'submitExamination']);
//             Route::get('/obat-list', [App\Http\Controllers\Auth\AuthController::class, 'getObatList']);
//             Route::post('/create-prescription/{kunjunganId}', [App\Http\Controllers\Auth\AuthController::class, 'createPrescription']);
//             Route::get('/prescriptions/{kunjunganId}', [App\Http\Controllers\Auth\AuthController::class, 'getPrescriptions']);
//             Route::get('/patient-history', [App\Http\Controllers\Auth\AuthController::class, 'getPatientHistory']);
//             Route::get('/schedule', [App\Http\Controllers\Auth\AuthController::class, 'getDokterSchedule']);
//             Route::get('/data-kunjungan-dokter', [APIController::class, 'indexDokter']);
//         });

//         // Testimoni routes
//         Route::prefix('testimoni')->group(function () {
//             Route::post('/store', [App\Http\Controllers\Auth\AuthController::class, 'submitTestimoni']);
//         });
//     });

//     // Public API routes (these don't need authentication)
//     Route::get('/getDataJadwalDokter', [APIController::class, 'getDataJadwalDokter']);
//     Route::get('/getDataTestimoni', [APIController::class, 'getDataTestimoni']);

//     // UPDATED: Use AuthController for specialties to handle new database structure
//     Route::get('/getDataSpesialisasiDokter', [App\Http\Controllers\Auth\AuthController::class, 'getDataSpesialisasiDokter']);

//     Route::get('/getDataPasien', [APIController::class, 'getDataPasien']);
//     Route::get('/getDataKunjunganDokter', [APIController::class, 'getDataKunjunganDokter']);
//     // Route::get('/getDataDokter', [APIController::class, 'getDataDokter']);

// });



// Rest of your web routes remain the same...
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/testing', [TestingController::class, 'testing'])->name('testing');
Route::post('/testing-create-kunjungan', [TestingController::class, 'testingCreateKunjungan'])->name('testing.create.kunjungan');
Route::post('/testing-ubah-status-kunjungan', [TestingController::class, 'ubahStatusKunjungan'])->name('testing.ubah.status.kunjungan');
Route::post('/testing-batalkan-status-kunjungan', [TestingController::class, 'batalkanStatusKunjungan'])->name('testing.batalkan.status.kunjungan');
Route::post('/testing-update-status-resep-obat', [TestingController::class, 'batalkanStatusKunjungan'])->name('testing.update.status.resep.obat');

// // Public web routes for data access
// Route::get('/getDataJadwalDokter', [APIController::class, 'getDataJadwalDokter'])->name('get.data.jadwal.dokter');
// Route::get('/getDataKunjungan', [APIController::class, 'getDataKunjungan'])->name('getee.data.kunjungan');
// Route::get('/getDataTestimoni', [APIController::class, 'getDataTestimoni'])->name('get.data.testimoni');
// Route::get('/getDataDokter', [APIController::class, 'getDataDokter'])->name('get.data.dokter');
// Route::get('/getDataSpesialisasiDokter', [APIController::class, 'getDataSpesialisasiDokter'])->name('get.data.spesialisasi.dokter');
// Route::get('/getDataDokterSpesialisasi', [APIController::class, 'getDataDokterSpesialisasi'])->name('get.data.dokter.spesialisasi');

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

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/chart_kunjungan', [DashboardController::class, 'getChartKunjungan'])->name('chart_kunjungan');
        Route::get('/total_dokter', [DashboardController::class, 'getTotalDokter'])->name('total_dokter');
        Route::get('/total_pasien', [DashboardController::class, 'getTotalPasien'])->name('total_pasien');
        Route::get('/total_apoteker', [DashboardController::class, 'getTotalApoteker'])->name('total_apoteker');
        Route::get('/stok_obat', [DashboardController::class, 'getStokObat'])->name('stok_obat');
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
        Route::put('/update_dokter/{id}', [DokterController::class, 'updateDokter'])->name('update_dokter');
        Route::delete('/delete_dokter/{id}', [DokterController::class, 'deleteDokter'])->name('delete_dokter');

        // crud pasien
        Route::get('/data_pasien', [ManajemenPenggunaController::class, 'dataPasien'])->name('data_pasien');
        Route::get('/get_pasien_by_id/{id}', [PasienController::class, 'getPasienById'])->name('get_pasien_by_id');
        Route::post('/add_pasien', [PasienController::class, 'createPasien'])->name('add_pasien');
        Route::put('/update_pasien/{id}', [PasienController::class, 'updatePasien'])->name('update_pasien');
        Route::delete('/delete_pasien/{id}', [PasienController::class, 'deletePasien'])->name('delete_pasien');

        // crud apoteker
        Route::get('/data_apoteker', [ManajemenPenggunaController::class, 'dataApoteker'])->name('data_apoteker');
        Route::get('/get_apoteker_by_id/{id}', [ApotekerController::class, 'getApotekerById'])->name('get_apoteker_by_id');
        Route::post('/add_apoteker', [ApotekerController::class, 'createApoteker'])->name('add_apoteker');
        Route::put('/update_apoteker/{id}', [ApotekerController::class, 'updateApoteker'])->name('update_apoteker');
        Route::delete('/delete_apoteker/{id}', [ApotekerController::class, 'deleteApoteker'])->name('delete_apoteker');
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
    });

    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::get('/laporan_kunjungan', [LaporanController::class, 'dataKunjungan'])->name('laporan_kunjungan');
        Route::get('/laporan_keuangan', [LaporanController::class, 'dataPembayaran'])->name('laporan_keuangan');
        Route::get('/laporan_transaksi_apoteker', [LaporanController::class, 'dataTransaksiApoteker'])->name('laporan_transaksi_apoteker');
        Route::get('/laporan_administrasi', [LaporanController::class, 'dataAdministrasi'])->name('laporan_administrasi');
    });

    Route::prefix('data_medis_pasien')->name('data_medis_pasien.')->group(function () {
        Route::get('/', [DataMedisPasienController::class, 'index'])->name('index');
        Route::get('/laporan_rekam_medis', [DataMedisPasienController::class, 'dataRekamMedis'])->name('laporan_rekam_medis');
        Route::get('/diagnosa_dan_konsultasi', [DataMedisPasienController::class, 'dataKonsultasi'])->name('diagnosa_dan_konsultasi');
        Route::get('/hasil_lab', [DataMedisPasienController::class, 'dataLab'])->name('hasil_lab');
    });

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
    });
});

Route::get('/login-dokter', [AuthController::class, 'login'])->name('login.dokter');
Route::post('/proses-login-dokter', [AuthController::class, 'prosesLogin'])->name('proses.login.dokter');

require __DIR__ . '/auth.php';
