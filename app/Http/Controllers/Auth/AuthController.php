<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Pasien;
use App\Models\Dokter;
use App\Models\Testimoni;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Mail\OtpMail;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AuthController extends Controller
{
    // Method untuk check availability username/email
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'type' => 'required|in:username,email',
            'value' => 'required|string',
        ]);

        $exists = User::where($request->type, $request->value)->exists();

        return response()->json([
            'status' => 'success',
            'available' => !$exists,
            'message' => $exists
                ? ucfirst($request->type) . ' sudah digunakan'
                : ucfirst($request->type) . ' tersedia'
        ]);
    }

    // Logika untuk Register - FIXED
    public function register(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:user'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:user'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Always set role as Pasien for mobile app registration
        $role = 'Pasien';

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
        ]);

        $responseData = [
            'account' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ];

        // FIXED: Buat record pasien dengan nama_pasien default dari username
        if ($role === 'Pasien') {
            $pasien = Pasien::create([
                'user_id' => $user->id,
                'nama_pasien' => $request->username, // FIXED: Set default name
                // Kolom lain dibiarkan null untuk diisi nanti
            ]);

            $responseData['account']['pasien_id'] = $pasien->id_pasien ?? $pasien->id;
        }

        $token = $user->createToken('authToken')->plainTextToken;
        $responseData['token'] = $token;

        return response()->json([
            'status' => 'success',
            'message' => 'Akun berhasil didaftarkan.',
            'data' => $responseData
        ], 201);
    }

    // Logika untuk Login dengan pesan error yang spesifik
    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Cek apakah username ada
        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username tidak ditemukan. Silakan periksa kembali atau daftar akun baru.',
                'error_type' => 'username_not_found'
            ], 401);
        }

        // Cek apakah password benar
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Password yang Anda masukkan salah. Silakan coba lagi.',
                'error_type' => 'wrong_password'
            ], 401);
        }

        // Login berhasil
        $user->tokens()->delete();
        $token = $user->createToken('authToken')->plainTextToken;

        $responseData = [
            'account' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token,
        ];

        if ($user->role === 'Pasien') {
            $pasien = Pasien::where('user_id', $user->id)->first();

            if (!$pasien) {
                // Try alternative approach - find by email
                $pasien = Pasien::where('email', $user->email)->first();

                if (!$pasien) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Data pasien tidak ditemukan.',
                        'error_type' => 'patient_data_missing'
                    ], 404);
                }
            }

            $responseData['account']['pasien_id'] = $pasien->id_pasien ?? $pasien->id;
        }

        if ($user->role === 'Dokter') {
            $dokter = Dokter::where('user_id', $user->id)->first();

            if (!$dokter) {
                // Try alternative approach - find by email
                $dokter = Dokter::where('email', $user->email)->first();
            }

            if ($dokter) {
                $responseData['account']['dokter_id'] = $dokter->id_dokter ?? $dokter->id;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil.',
            'data' => $responseData
        ], 200);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil.'
        ]);
    }

    // Profile - FIXED: Return consistent field names for Doctor
    public function profile(Request $request)
    {
        $user = $request->user();

        $profileData = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
        ];

        if ($user->role === 'Pasien') {
            $pasien = Pasien::where('user_id', $user->id)->first();

            if (!$pasien) {
                $pasien = Pasien::where('email', $user->email)->first();
            }

            if ($pasien) {
                $profileData['pasien_id'] = $pasien->id_pasien ?? $pasien->id;
                $profileData['nomor_rm'] = $pasien->nomor_rm ?? '';
                $profileData['nama_lengkap'] = $pasien->nama_pasien ?? '';
                $profileData['alamat'] = $pasien->alamat ?? '';
                $profileData['tanggal_lahir'] = $pasien->tanggal_lahir ?? '';
                $profileData['jenis_kelamin'] = $pasien->jenis_kelamin ?? '';
            }
        }

        if ($user->role === 'Dokter') {
            $dokter = Dokter::where('user_id', $user->id)->first();

            if (!$dokter) {
                $dokter = Dokter::where('email', $user->email)->first();
            }

            if ($dokter) {
                $profileData['dokter_id'] = $dokter->id_dokter ?? $dokter->id;
                $profileData['nama_lengkap'] = $dokter->nama_dokter ?? '';
                $profileData['gelar_depan'] = 'Dr.'; // Default
                $profileData['gelar_belakang'] = ''; // Empty by default
                $profileData['spesialis'] = $dokter->spesialis ?? 'Umum';
                $profileData['subspesialis'] = $dokter->subspesialis ?? '';
                $profileData['foto_profile'] = $dokter->foto_profile ?? $dokter->foto ?? null;
                $profileData['no_tlp'] = $dokter->no_hp ?? '';
                $profileData['alamat'] = $dokter->alamat ?? '';
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $profileData
        ]);
    }

    // UpdateProfile - FIXED: Single method only
    public function updateProfile(Request $request)
    {
        // FIXED: Validate with proper field name
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'alamat' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
        ]);

        $user = $request->user();

        Log::info('Update Profile Request', [
            'user_id' => $user->id,
            'request_data' => $request->all()
        ]);

        if ($user->role === 'Pasien') {
            $pasien = Pasien::where('user_id', $user->id)->first();

            if (!$pasien) {
                Log::error('Pasien not found', ['user_id' => $user->id]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data pasien tidak ditemukan.'
                ], 404);
            }

            // FIXED: Map nama_lengkap to nama_pasien untuk database
            $updateData = [
                'nama_pasien' => $request->nama_lengkap,
                'alamat' => $request->alamat,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
            ];

            $pasien->update($updateData);

            Log::info('Profile Updated Successfully', [
                'pasien_id' => $pasien->id,
                'updated_data' => $updateData
            ]);

            // Refresh data
            $pasien->refresh();

            // Return updated profile
            return response()->json([
                'status' => 'success',
                'message' => 'Profil berhasil diperbarui',
                'data' => [
                    'nama_lengkap' => $pasien->nama_pasien,
                    'alamat' => $pasien->alamat,
                    'tanggal_lahir' => $pasien->tanggal_lahir,
                    'jenis_kelamin' => $pasien->jenis_kelamin,
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Hanya pasien yang dapat mengupdate profil melalui endpoint ini.',
        ], 403);
    }

    // ========== FORGOT PASSWORD METHODS ==========

    public function sendOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:user,email'
        ]);

    $otp = sprintf('%06d', mt_rand(0, 999999));
    
    // Simpan OTP ke cache selama 10 menit
    Cache::put("forgot_password_otp_{$request->email}", $otp, 600);

    try {
        // Kirim email OTP pakai Blade template yang sama
        Mail::send('emails.otp_notification', [
            'otp' => $otp,
            'type' => 'Lupa Password',
            'expiration_minutes' => 10
        ], function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('Kode OTP untuk Lupa Password');
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Kode OTP telah dikirim ke email Anda. Silakan cek kotak masuk atau folder spam.'
        ]);
    } catch (\Exception $e) {
        Log::error('Gagal Kirim OTP Lupa Password', ['error' => $e->getMessage()]);
        
        // Jangan hapus OTP agar bisa diverifikasi nanti
        return response()->json([
            'status' => 'error',
            'message' => 'OTP berhasil dibuat tapi gagal dikirim via email. Silakan coba lagi nanti.',
            // 'debug_otp' => $otp // aktifkan ini hanya untuk pengujian
        ], 500);
    }
}


    public function verifyOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6'
        ]);

        $cachedOtp = Cache::get("forgot_password_otp_{$request->email}");

        if (!$cachedOtp || $cachedOtp !== $request->otp) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP tidak valid atau sudah kedaluwarsa.'
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'OTP berhasil diverifikasi.'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $cachedOtp = Cache::get("forgot_password_otp_{$request->email}");

        if (!$cachedOtp || $cachedOtp !== $request->otp) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP tidak valid atau sudah kedaluwarsa.'
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email tidak ditemukan.'
            ], 404);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Clear OTP cache
        Cache::forget("forgot_password_otp_{$request->email}");

        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil direset.'
        ]);
    }

    // ========== FORGOT USERNAME METHODS ==========
    
public function sendUsernameOTP(Request $request)
{
    $request->validate(['email' => 'required|email|exists:user,email']); // Tambah validasi exists:user,email
    
    $user = User::where('email', $request->email)->first();
    
    // User pasti ada karena sudah divalidasi 'exists', tapi tetap cek untuk pesan spesifik
    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'Email tidak ditemukan.',
        ], 404);
    }

    // 1. Generate OTP (asumsi 6 digit)
    $otp = sprintf('%06d', mt_rand(0, 999999));
    
    // 2. Simpan OTP ke cache selama 5 menit (300 detik)
    Cache::put("forgot_username_otp_{$request->email}", $otp, 300); // FIX: Menambah Cache::put

    // 3. Kirim Email menggunakan Blade Template
    try {
        Mail::send('emails.otp_notification', [
            'otp' => $otp,
            'type' => 'Lupa Username', 
            'expiration_minutes' => 5
        ], function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('Kode Verifikasi untuk Lupa Username');
        });

        // 4. Berikan Response Sukses
        return response()->json([
            'status' => 'success',
            'message' => 'Kode OTP telah dikirim ke email Anda. Silakan cek kotak masuk.',
            // Hapus 'debug_otp' di production
            // 'debug_otp' => $otp
        ]);
    } catch (\Exception $e) {
        Log::error('Gagal Kirim OTP Username', ['error' => $e->getMessage()]);
        
        // JANGAN MENGHAPUS CACHE AGAR BISA TETAP DIVERIFIKASI JIKA SERVER EMAIL BERMASALAH
        // Tapi berikan pesan yang jelas ke pengguna
        return response()->json([
            'status' => 'error',
            'message' => 'OTP berhasil dibuat, namun gagal dikirim via email. Silakan coba verifikasi.',
            // Jika Anda ingin mengizinkan pengguna melanjutkan tanpa email (untuk development)
            // 'debug_otp' => $otp 
        ], 500); 
    }
}

   public function verifyUsernameOTP(Request $request)
{
    // ... (Validasi)

    $cachedOtp = Cache::get("forgot_username_otp_{$request->email}");
    
    if (!$cachedOtp || $cachedOtp !== $request->otp) {
        // ... (Error response)
    }
    
    // Opsional: Hapus OTP setelah diverifikasi agar tidak bisa dipakai untuk update username lain (security)
    // Walaupun 'updateUsername' juga melakukan ini, ini bagus sebagai layer pencegahan
    // Cache::forget("forgot_username_otp_{$request->email}"); // PENTING: Jangan hapus dulu, karena updateUsername masih memerlukannya untuk final check.

    $user = User::where('email', $request->email)->first();

    return response()->json([
        'status' => 'success',
        'message' => 'OTP berhasil diverifikasi. Username Anda adalah: '.$user->username, // Tampilkan username di sini
        'data' => [
            'username' => $user->username
        ]
    ]);
}

    public function updateUsername(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('user', 'username')->ignore(User::where('email', $request->email)->value('id')),
            ],
        ]);

        $cachedOtp = Cache::get("forgot_username_otp_{$request->email}");
        
        if (!$cachedOtp || $cachedOtp !== $request->otp) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email tidak ditemukan.'
            ], 404);
        }

        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP belum diverifikasi atau sudah kedaluwarsa.'
            ], 400);
        }

        $user->username = $newUsername;
        $user->save();

        Cache::forget("forgot_username_otp_{$email}");
        Cache::forget("forgot_username_verified_{$email}");

        return response()->json([
            'status' => 'success',
            'message' => 'Username berhasil diperbarui.',
            'data' => ['username' => $user->username]
        ]);
    }

    // ========== TESTIMONI METHODS ==========

    public function submitTestimoni(Request $request)
    {
        $request->validate([
            'nama_testimoni' => 'required|string|max:255',
            'umur' => 'required|string|max:10',
            'pekerjaan' => 'required|string|max:255',
            'isi_testimoni' => 'required|string'
        ]);

        $testimoni = Testimoni::create([
            'nama_testimoni' => $request->nama_testimoni,
            'umur' => $request->umur,
            'pekerjaan' => $request->pekerjaan,
            'isi_testimoni' => $request->isi_testimoni,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Testimoni berhasil disimpan.',
            'data' => $testimoni
        ], 201);
    }

    // ========== PLACEHOLDER METHODS FOR FUTURE IMPLEMENTATION ==========
    
    public function bookSchedule(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|integer|exists:dokter,id',
            'day' => 'required|string',
            'time' => 'required|string',
            'keluhan' => 'required|string|max:1000',
            'nama_rs_perujuk' => 'nullable|string|max:255',
            'nama_dokter_perujuk' => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        
        // Cari data pasien berdasarkan user
        $pasien = Pasien::where('user_id', $user->id)->first();
        
        if (!$pasien) {
            // Fallback: cari berdasarkan email
            $pasien = Pasien::where('email', $user->email)->first();
            
            if (!$pasien) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data pasien tidak ditemukan. Silakan lengkapi profil Anda terlebih dahulu.'
                ], 404);
            }
        }

        // Validasi dokter exists
        $dokter = Dokter::find($request->doctor_id);
        if (!$dokter) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dokter tidak ditemukan.'
            ], 404);
        }

        // FIXED - Handle quoted day names in database
        // Clean the day name by removing quotes if they exist
        $cleanDay = trim($request->day, '"\''); // Remove quotes
        
        $jadwalDokter = JadwalDokter::where('dokter_id', $request->doctor_id)
            ->where(function($query) use ($cleanDay) {
                $query->where('hari', $cleanDay)                    // Try without quotes
                      ->orWhere('hari', '"' . $cleanDay . '"')      // Try with double quotes  
                      ->orWhere('hari', "'" . $cleanDay . "'");     // Try with single quotes
            })
            ->first();

        if (!$jadwalDokter) {
            // Log untuk debugging - lihat data apa yang dikirim vs yang ada di DB
            $availableSchedules = JadwalDokter::where('dokter_id', $request->doctor_id)->get();
            
            Log::info('Booking Failed - Schedule not found', [
                'requested' => [
                    'doctor_id' => $request->doctor_id,
                    'day' => $request->day,
                    'time' => $request->time
                ],
                'available_schedules' => $availableSchedules->toArray(),
                'doctor_info' => $dokter->toArray()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Dokter tidak praktik pada hari ' . $request->day,
                'debug' => [
                    'doctor_id' => $request->doctor_id,
                    'day_requested' => $request->day,
                    'available_days' => $availableSchedules->pluck('hari')->toArray()
                ]
            ], 400);
        }

        // Tentukan tanggal kunjungan berdasarkan hari yang dipilih
        $tanggalKunjungan = $this->getNextDateForDay($request->day);

        try {
            // Buat record kunjungan baru
            $kunjungan = Kunjungan::create([
                'dokter_id' => $request->doctor_id,
                'pasien_id' => $pasien->id_pasien ?? $pasien->id,
                'tanggal_kunjungan' => $tanggalKunjungan,
                'keluhan_awal' => $request->keluhan,
            ]);

            // Log sukses
            Log::info('Booking Success', [
                'kunjungan_id' => $kunjungan->id,
                'dokter_id' => $kunjungan->dokter_id,
                'pasien_id' => $kunjungan->pasien_id,
                'tanggal' => $kunjungan->tanggal_kunjungan
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Jadwal berhasil dipesan.',
                'data' => [
                    'kunjungan' => [
                        'id' => $kunjungan->id,
                        'dokter_id' => $kunjungan->dokter_id,
                        'pasien_id' => $kunjungan->pasien_id,
                        'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                        'keluhan_awal' => $kunjungan->keluhan_awal,
                        'dokter_nama' => $dokter->nama_dokter,
                        'jadwal' => $request->day . ', ' . $request->time,
                    ],
                    'emr' => [
                        'pasien_id' => $kunjungan->pasien_id,
                        'kunjungan_id' => $kunjungan->id,
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating kunjungan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
                'pasien' => $pasien->toArray()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memesan jadwal. Silakan coba lagi.',
                'debug' => $e->getMessage()
            ], 500);
        }
    }

    private function getNextDateForDay($dayName)
    {
        $dayMapping = [
            'Senin' => 1,
            'Selasa' => 2,
            'Rabu' => 3,
            'Kamis' => 4,
            'Jumat' => 5,
            'Sabtu' => 6,
            'Minggu' => 0
        ];

        $targetDay = $dayMapping[$dayName] ?? 1;
        $today = Carbon::now();
        
        // If today is the target day but time has passed, go to next week
        if ($today->dayOfWeek == $targetDay) {
            return $today->addWeek()->format('Y-m-d H:i:s');
        }
        
        // Find next occurrence of the target day
        $daysToAdd = ($targetDay - $today->dayOfWeek + 7) % 7;
        if ($daysToAdd == 0) {
            $daysToAdd = 7; // If today is the day, go to next week
        }
        
        return $today->addDays($daysToAdd)->format('Y-m-d H:i:s');
    }

    // ========== EMR METHODS - MAIN IMPLEMENTATION ==========

    public function getAllEmrPasien($id)
    {
        try {
            Log::info('getAllEmrPasien called', ['pasien_id' => $id]);

            // Query untuk mengambil semua kunjungan pasien dengan relasi
            $kunjunganList = Kunjungan::with(['dokter'])
                ->where('pasien_id', $id)
                ->orderBy('tanggal_kunjungan', 'desc')
                ->get();

            Log::info('Found kunjungan', ['count' => $kunjunganList->count()]);

            if ($kunjunganList->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Belum ada data kunjungan',
                    'data' => []
                ]);
            }

            $emrData = $kunjunganList->map(function($kunjungan) {
                // Data dasar kunjungan
                $data = [
                    'id' => $kunjungan->id,
                    'kunjungan_id' => $kunjungan->id,
                    'pasien_id' => $kunjungan->pasien_id,
                    'dokter_id' => $kunjungan->dokter_id,
                    'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                    'keluhan_awal' => $kunjungan->keluhan_awal ?? '-',
                    'keluhan' => $kunjungan->keluhan_awal ?? '-', // alias untuk Flutter
                    'status' => $kunjungan->status ?? 'menunggu',
                    
                    // Data Dokter
                    'nama_dokter' => $kunjungan->dokter->nama_dokter ?? '-',
                    'dokter_nama' => $kunjungan->dokter->nama_dokter ?? '-', // alias
                    'spesialisasi' => $kunjungan->dokter->spesialis ?? '-',
                    
                    // Data Rujukan
                    'nama_rs_perujuk' => $kunjungan->nama_rs_perujuk,
                    'nama_dokter_perujuk' => $kunjungan->nama_dokter_perujuk,
                ];

                // Ambil data EMR untuk kunjungan ini
                $emr = EMR::where('kunjungan_id', $kunjungan->id)->first();
                if ($emr) {
                    $data['prosedur'] = $emr->hasil_periksa ?? null;
                    $data['informasi_kondisi'] = $emr->riwayat_penyakit ?? null;
                    $data['alergi'] = $emr->alergi ?? null;
                } else {
                    $data['prosedur'] = null;
                    $data['informasi_kondisi'] = null;
                    $data['alergi'] = null;
                }

                // Ambil data resep obat
                $resepObat = [];
                
                // Cek apakah tabel resep ada
                try {
                    $resepList = DB::table('resep')
                        ->where('kunjungan_id', $kunjungan->id)
                        ->get();

                    foreach ($resepList as $resep) {
                        $resepObat[] = [
                            'id' => $resep->id ?? null,
                            'nama_obat' => $resep->nama_obat ?? '-',
                            'dosis' => $resep->dosis ?? '-',
                            'aturan_pakai' => $resep->aturan_pakai ?? '-',
                            'jumlah_obat' => $resep->jumlah_obat ?? $resep->jumlah ?? '-',
                        ];
                    }
                } catch (\Exception $e) {
                    // Tabel resep mungkin belum ada, skip saja
                    Log::info('Resep table not found or error', ['error' => $e->getMessage()]);
                }

                $data['resep_obat'] = $resepObat;

                return $data;
            });

            Log::info('EMR data processed', ['count' => $emrData->count()]);

            return response()->json([
                'status' => 'success',
                'message' => 'Data EMR berhasil diambil',
                'data' => $emrData->values() // Ensure array format
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching EMR data', [
                'pasien_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data EMR: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function getKunjunganDetail(Request $request, $kunjunganId)
    {
        try {
            $kunjungan = Kunjungan::with(['dokter', 'pasien'])
                ->findOrFail($kunjunganId);

            // Pastikan user hanya bisa akses data kunjungannya sendiri
            $user = $request->user();
            if ($user->role === 'Pasien') {
                $pasien = Pasien::where('user_id', $user->id)->first();
                if (!$pasien) {
                    $pasien = Pasien::where('email', $user->email)->first();
                }
                
                if (!$pasien || $kunjungan->pasien_id !== ($pasien->id_pasien ?? $pasien->id)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Akses ditolak'
                    ], 403);
                }
            }

            $data = [
                'id' => $kunjungan->id,
                'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                'keluhan_awal' => $kunjungan->keluhan_awal,
                'status' => $kunjungan->status ?? 'menunggu',
                'dokter' => [
                    'nama_dokter' => $kunjungan->dokter->nama_dokter ?? '-',
                    'spesialisasi' => $kunjungan->dokter->spesialis ?? '-',
                ],
                'emr' => null,
                'resep_obat' => []
            ];

            // Ambil data EMR jika ada
            $emr = EMR::where('kunjungan_id', $kunjungan->id)->first();
            if ($emr) {
                $data['emr'] = [
                    'riwayat_penyakit' => $emr->riwayat_penyakit,
                    'alergi' => $emr->alergi,
                    'hasil_periksa' => $emr->hasil_periksa,
                ];
            }

            // Ambil data resep jika ada
            try {
                $resepList = DB::table('resep')
                    ->where('kunjungan_id', $kunjungan->id)
                    ->get();

                foreach ($resepList as $resep) {
                    $data['resep_obat'][] = [
                        'nama_obat' => $resep->nama_obat ?? '-',
                        'dosis' => $resep->dosis ?? '-',
                        'aturan_pakai' => $resep->aturan_pakai ?? '-',
                        'jumlah_obat' => $resep->jumlah_obat ?? $resep->jumlah ?? '-',
                    ];
                }
            } catch (\Exception $e) {
                // Resep table tidak ada, skip
                Log::info('Resep table not available', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching kunjungan detail', [
                'kunjungan_id' => $kunjunganId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Data kunjungan tidak ditemukan'
            ], 404);
        }
    }

    // ========== APPOINTMENT MANAGEMENT ==========
    
    public function checkDoctorAvailability(Request $request, $dokterId, $tanggal)
    {
        return response()->json([
            'status' => 'success',
            'available' => true,
            'message' => 'Dokter tersedia'
        ]);
    }

    public function getMyAppointments(Request $request)
    {
        try {
            $user = $request->user();
            
            if ($user->role !== 'Pasien') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak. Hanya pasien yang dapat melihat appointment.'
                ], 403);
            }

            // Cari data pasien
            $pasien = Pasien::where('user_id', $user->id)->first();
            if (!$pasien) {
                $pasien = Pasien::where('email', $user->email)->first();
            }
            
            if (!$pasien) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data pasien tidak ditemukan.'
                ], 404);
            }

            // Ambil semua kunjungan pasien
            $appointments = Kunjungan::with(['dokter'])
                ->where('pasien_id', $pasien->id_pasien ?? $pasien->id)
                ->orderBy('tanggal_kunjungan', 'desc')
                ->get()
                ->map(function($kunjungan) {
                    return [
                        'id' => $kunjungan->id,
                        'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                        'keluhan_awal' => $kunjungan->keluhan_awal ?? '-',
                        'status' => $kunjungan->status ?? 'menunggu',
                        'dokter_nama' => $kunjungan->dokter->nama_dokter ?? '-',
                        'spesialisasi' => $kunjungan->dokter->spesialis ?? '-',
                        'can_cancel' => in_array($kunjungan->status ?? 'menunggu', ['menunggu']), // Hanya bisa cancel jika masih menunggu
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $appointments
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching appointments', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data appointment'
            ], 500);
        }
    }

    public function cancelAppointment(Request $request, $kunjunganId)
    {
        try {
            $user = $request->user();
            
            if ($user->role !== 'Pasien') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak.'
                ], 403);
            }

            // Cari data pasien
            $pasien = Pasien::where('user_id', $user->id)->first();
            if (!$pasien) {
                $pasien = Pasien::where('email', $user->email)->first();
            }
            
            if (!$pasien) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data pasien tidak ditemukan.'
                ], 404);
            }

            // Cari kunjungan
            $kunjungan = Kunjungan::where('id', $kunjunganId)
                ->where('pasien_id', $pasien->id_pasien ?? $pasien->id)
                ->first();

            if (!$kunjungan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data kunjungan tidak ditemukan.'
                ], 404);
            }

            // Cek apakah masih bisa dibatalkan
            if (!in_array($kunjungan->status ?? 'menunggu', ['menunggu'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kunjungan tidak dapat dibatalkan. Status saat ini: ' . ($kunjungan->status ?? 'menunggu')
                ], 400);
            }

            // Update status ke dibatalkan
            $kunjungan->update(['status' => 'dibatalkan']);

            return response()->json([
                'status' => 'success',
                'message' => 'Kunjungan berhasil dibatalkan.',
                'data' => [
                    'id' => $kunjungan->id,
                    'status' => $kunjungan->status
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error cancelling appointment', [
                'error' => $e->getMessage(),
                'kunjungan_id' => $kunjunganId
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membatalkan kunjungan'
            ], 500);
        }
    }

    public function rescheduleAppointment(Request $request, $kunjunganId)
    {
        $request->validate([
            'tanggal_kunjungan_baru' => 'required|date|after:today',
            'alasan' => 'nullable|string|max:500'
        ]);

        try {
            $user = $request->user();
            
            if ($user->role !== 'Pasien') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak.'
                ], 403);
            }

            // Cari data pasien
            $pasien = Pasien::where('user_id', $user->id)->first();
            if (!$pasien) {
                $pasien = Pasien::where('email', $user->email)->first();
            }
            
            if (!$pasien) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data pasien tidak ditemukan.'
                ], 404);
            }

            // Cari kunjungan
            $kunjungan = Kunjungan::where('id', $kunjunganId)
                ->where('pasien_id', $pasien->id_pasien ?? $pasien->id)
                ->first();

            if (!$kunjungan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data kunjungan tidak ditemukan.'
                ], 404);
            }

            // Cek apakah masih bisa diubah jadwalnya
            if (!in_array($kunjungan->status ?? 'menunggu', ['menunggu'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jadwal kunjungan tidak dapat diubah. Status saat ini: ' . ($kunjungan->status ?? 'menunggu')
                ], 400);
            }

            // Simpan tanggal lama untuk log
            $tanggalLama = $kunjungan->tanggal_kunjungan;

            // Update tanggal kunjungan
            $kunjungan->update([
                'tanggal_kunjungan' => $request->tanggal_kunjungan_baru,
                'status' => 'menunggu' // Reset status ke menunggu
            ]);

            // Log perubahan jadwal
            Log::info('Appointment rescheduled', [
                'kunjungan_id' => $kunjungan->id,
                'pasien_id' => $pasien->id,
                'tanggal_lama' => $tanggalLama,
                'tanggal_baru' => $request->tanggal_kunjungan_baru,
                'alasan' => $request->alasan
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Jadwal kunjungan berhasil diubah.',
                'data' => [
                    'id' => $kunjungan->id,
                    'tanggal_kunjungan_lama' => $tanggalLama,
                    'tanggal_kunjungan_baru' => $kunjungan->tanggal_kunjungan,
                    'status' => $kunjungan->status
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error rescheduling appointment', [
                'error' => $e->getMessage(),
                'kunjungan_id' => $kunjunganId
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengubah jadwal kunjungan'
            ], 500);
        }
    }

    // ========== DOCTOR SPECIFIC METHODS ==========

    // Get dashboard stats for doctor
    public function getDokterDashboardStats(Request $request)
    {
        try {
            $user = $request->user();
            
            if ($user->role !== 'Dokter') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak. Hanya dokter yang dapat mengakses endpoint ini.'
                ], 403);
            }

            // Cari data dokter
            $dokter = Dokter::where('user_id', $user->id)->first();
            if (!$dokter) {
                $dokter = Dokter::where('email', $user->email)->first();
            }
            
            if (!$dokter) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data dokter tidak ditemukan.'
                ], 404);
            }

            $dokterId = $dokter->id_dokter ?? $dokter->id;

            // Hitung statistik
            $today = Carbon::today();
            $thisWeek = Carbon::now()->startOfWeek();
            $thisMonth = Carbon::now()->startOfMonth();

            $stats = [
                'pasien_hari_ini' => Kunjungan::where('dokter_id', $dokterId)
                    ->whereDate('tanggal_kunjungan', $today)
                    ->count(),
                
                'pasien_minggu_ini' => Kunjungan::where('dokter_id', $dokterId)
                    ->whereBetween('tanggal_kunjungan', [$thisWeek, Carbon::now()])
                    ->count(),
                
                'pasien_bulan_ini' => Kunjungan::where('dokter_id', $dokterId)
                    ->whereBetween('tanggal_kunjungan', [$thisMonth, Carbon::now()])
                    ->count(),
                
                'total_pasien' => Kunjungan::where('dokter_id', $dokterId)->count(),
                
                'waktu_praktik_berikutnya' => $this->getNextPracticeTime($dokterId),
            ];

            return response()->json([
                'status' => 'success',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching doctor dashboard stats', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil statistik dashboard'
            ], 500);
        }
    }

    // Get today's patients for doctor
    public function getTodayPatients(Request $request)
    {
        try {
            $user = $request->user();
            
            if ($user->role !== 'Dokter') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak. Hanya dokter yang dapat mengakses endpoint ini.'
                ], 403);
            }

            // Cari data dokter
            $dokter = Dokter::where('user_id', $user->id)->first();
            if (!$dokter) {
                $dokter = Dokter::where('email', $user->email)->first();
            }
            
            if (!$dokter) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data dokter tidak ditemukan.'
                ], 404);
            }

            $dokterId = $dokter->id_dokter ?? $dokter->id;
            $today = Carbon::today();

            // Ambil pasien hari ini dengan data lengkap
            $todayPatients = Kunjungan::with(['pasien', 'dokter'])
                ->where('dokter_id', $dokterId)
                ->whereDate('tanggal_kunjungan', $today)
                ->orderBy('tanggal_kunjungan', 'asc')
                ->get()
                ->map(function($kunjungan, $index) {
                    $noAntrian = 'A' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
                    $waktu = Carbon::parse($kunjungan->tanggal_kunjungan)->format('H:i');
                    
                    return [
                        'id_kunjungan' => $kunjungan->id,
                        'id_pasien' => $kunjungan->pasien_id,
                        'nama_pasien' => $kunjungan->pasien->nama_pasien ?? 'Pasien',
                        'nomor_rm' => $kunjungan->pasien->nomor_rm ?? '-',
                        'nomor_ktp' => $kunjungan->pasien->nomor_ktp ?? '-',
                        'tanggal_lahir' => $kunjungan->pasien->tanggal_lahir ?? '-',
                        'jenis_kelamin' => $kunjungan->pasien->jenis_kelamin ?? '-',
                        'no_tlp' => $kunjungan->pasien->no_tlp ?? '-',
                        'alamat' => $kunjungan->pasien->alamat ?? '-',
                        'keluhan' => $kunjungan->keluhan_awal ?? '-',
                        'status' => $kunjungan->status ?? 'Pending',
                        'waktu' => $waktu,
                        'no_antrian' => $noAntrian,
                        'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $todayPatients
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching today patients', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data pasien hari ini'
            ], 500);
        }
    }

    // Update patient status
    public function updatePatientStatus(Request $request, $kunjunganId)
    {
        $request->validate([
            'status' => 'required|string|in:Pending,Engaged,Completed,Succeed'
        ]);

        try {
            $user = $request->user();
            
            if ($user->role !== 'Dokter') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak.'
                ], 403);
            }

            // Cari data dokter
            $dokter = Dokter::where('user_id', $user->id)->first();
            if (!$dokter) {
                $dokter = Dokter::where('email', $user->email)->first();
            }
            
            if (!$dokter) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data dokter tidak ditemukan.'
                ], 404);
            }

            $dokterId = $dokter->id_dokter ?? $dokter->id;

            // Cari kunjungan dan pastikan milik dokter ini
            $kunjungan = Kunjungan::where('id', $kunjunganId)
                ->where('dokter_id', $dokterId)
                ->first();

            if (!$kunjungan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data kunjungan tidak ditemukan atau bukan milik Anda.'
                ], 404);
            }

            // Update status
            $kunjungan->update(['status' => $request->status]);

            Log::info('Patient status updated', [
                'kunjungan_id' => $kunjunganId,
                'dokter_id' => $dokterId,
                'new_status' => $request->status
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Status pasien berhasil diperbarui',
                'data' => [
                    'kunjungan_id' => $kunjungan->id,
                    'status' => $kunjungan->status
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating patient status', [
                'error' => $e->getMessage(),
                'kunjungan_id' => $kunjunganId
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui status pasien'
            ], 500);
        }
    }

    // Submit examination
    public function submitExamination(Request $request, $kunjunganId)
    {
        $request->validate([
            'prosedur' => 'required|string',
            'informasi_kondisi' => 'required|string'
        ]);

        try {
            $user = $request->user();
            
            if ($user->role !== 'Dokter') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak.'
                ], 403);
            }

            // Cari data dokter
            $dokter = Dokter::where('user_id', $user->id)->first();
            if (!$dokter) {
                $dokter = Dokter::where('email', $user->email)->first();
            }
            
            if (!$dokter) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data dokter tidak ditemukan.'
                ], 404);
            }

            $dokterId = $dokter->id_dokter ?? $dokter->id;

            // Cari kunjungan
            $kunjungan = Kunjungan::where('id', $kunjunganId)
                ->where('dokter_id', $dokterId)
                ->first();

            if (!$kunjungan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data kunjungan tidak ditemukan.'
                ], 404);
            }

            // Update kunjungan status ke Completed/Succeed
            $kunjungan->update(['status' => 'Succeed']);

            // Buat atau update EMR
            $emr = EMR::updateOrCreate(
                ['kunjungan_id' => $kunjunganId],
                [
                    'pasien_id' => $kunjungan->pasien_id,
                    'dokter_id' => $dokterId,
                    'hasil_periksa' => $request->prosedur,
                    'riwayat_penyakit' => $request->informasi_kondisi,
                    'tanggal_periksa' => now(),
                ]
            );

            Log::info('Examination submitted', [
                'kunjungan_id' => $kunjunganId,
                'dokter_id' => $dokterId,
                'emr_id' => $emr->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pemeriksaan berhasil disimpan',
                'data' => [
                    'kunjungan_id' => $kunjungan->id,
                    'status' => $kunjungan->status,
                    'emr_id' => $emr->id
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error submitting examination', [
                'error' => $e->getMessage(),
                'kunjungan_id' => $kunjunganId
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan pemeriksaan'
            ], 500);
        }
    }

    // Get list obat
    public function getObatList(Request $request)
    {
        try {
            $user = $request->user();
            
            if ($user->role !== 'Dokter') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak.'
                ], 403);
            }

            // Ambil semua obat yang tersedia
            $obatList = DB::table('obat')
                ->select('id as id_obat', 'nama_obat', 'jenis', 'dosis', 'stok', 'satuan')
                ->where('stok', '>', 0)
                ->orderBy('nama_obat')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $obatList
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching obat list', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data obat'
            ], 500);
        }
    }

    // Create prescription
    public function createPrescription(Request $request, $kunjunganId)
    {
        $request->validate([
            'resep' => 'required|array',
            'resep.*.obat_id' => 'required|integer',
            'resep.*.jumlah_obat' => 'required|integer|min:1',
            'resep.*.aturan_pakai' => 'required|string'
        ]);

        try {
            $user = $request->user();
            
            if ($user->role !== 'Dokter') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak.'
                ], 403);
            }

            // Cari data dokter
            $dokter = Dokter::where('user_id', $user->id)->first();
            if (!$dokter) {
                $dokter = Dokter::where('email', $user->email)->first();
            }

            if (!$dokter) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data dokter tidak ditemukan.'
                ], 404);
            }

            $dokterId = $dokter->id_dokter ?? $dokter->id;

            // Cari kunjungan
            $kunjungan = Kunjungan::where('id', $kunjunganId)
                ->where('dokter_id', $dokterId)
                ->first();

            if (!$kunjungan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data kunjungan tidak ditemukan.'
                ], 404);
            }

            // Create resep records
            foreach ($request->resep as $resepItem) {
                // Ambil data obat
                $obat = DB::table('obat')->where('id', $resepItem['obat_id'])->first();
                
                if (!$obat) {
                    continue;
                }

                // Insert ke tabel resep
                DB::table('resep')->insert([
                    'kunjungan_id' => $kunjunganId,
                    'obat_id' => $resepItem['obat_id'],
                    'nama_obat' => $obat->nama_obat,
                    'dosis' => $obat->dosis,
                    'jumlah_obat' => $resepItem['jumlah_obat'],
                    'aturan_pakai' => $resepItem['aturan_pakai'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update stok obat
                DB::table('obat')
                    ->where('id', $resepItem['obat_id'])
                    ->decrement('stok', $resepItem['jumlah_obat']);
            }

            Log::info('Prescription created', [
                'kunjungan_id' => $kunjunganId,
                'dokter_id' => $dokterId,
                'resep_count' => count($request->resep)
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Resep obat berhasil dibuat'
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating prescription', [
                'error' => $e->getMessage(),
                'kunjungan_id' => $kunjunganId
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat resep obat'
            ], 500);
        }
    }

    // Get prescriptions
    public function getPrescriptions(Request $request, $kunjunganId)
    {
        try {
            $user = $request->user();
            
            if ($user->role !== 'Dokter') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak.'
                ], 403);
            }

            // Ambil data resep untuk kunjungan ini
            $prescriptions = DB::table('resep')
                ->where('kunjungan_id', $kunjunganId)
                ->get()
                ->map(function($resep) {
                    return [
                        'id' => $resep->id,
                        'nama_obat' => $resep->nama_obat,
                        'dosis' => $resep->dosis,
                        'jumlah_obat' => $resep->jumlah_obat,
                        'aturan_pakai' => $resep->aturan_pakai,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $prescriptions
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching prescriptions', [
                'error' => $e->getMessage(),
                'kunjungan_id' => $kunjunganId
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data resep'
            ], 500);
        }
    }

    // Get patient history
    public function getPatientHistory(Request $request)
    {
        try {
            $user = $request->user();
            
            if ($user->role !== 'Dokter') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak.'
                ], 403);
            }

            // Cari data dokter
            $dokter = Dokter::where('user_id', $user->id)->first();
            if (!$dokter) {
                $dokter = Dokter::where('email', $user->email)->first();
            }
            
            if (!$dokter) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data dokter tidak ditemukan.'
                ], 404);
            }

            $dokterId = $dokter->id_dokter ?? $dokter->id;
            $search = $request->get('search', '');

            // Ambil riwayat pasien yang pernah diperiksa dokter ini
            $query = Kunjungan::with(['pasien'])
                ->where('dokter_id', $dokterId)
                ->whereDate('tanggal_kunjungan', '<', Carbon::today());

            if (!empty($search)) {
                $query->whereHas('pasien', function($q) use ($search) {
                    $q->where('nama_pasien', 'like', "%{$search}%")
                      ->orWhere('nomor_rm', 'like', "%{$search}%");
                });
            }

            $kunjunganList = $query->orderBy('tanggal_kunjungan', 'desc')->get();

            // Group by date
            $groupedHistory = [];
            foreach ($kunjunganList as $kunjungan) {
                $date = Carbon::parse($kunjungan->tanggal_kunjungan)->format('Y-m-d');
                
                if (!isset($groupedHistory[$date])) {
                    $groupedHistory[$date] = [
                        'tanggal' => $date,
                        'jumlah' => 0,
                        'pasien' => []
                    ];
                }

                $groupedHistory[$date]['jumlah']++;
                $groupedHistory[$date]['pasien'][] = [
                    'id_kunjungan' => $kunjungan->id,
                    'nama_pasien' => $kunjungan->pasien->nama_pasien ?? 'Pasien',
                    'nomor_rm' => $kunjungan->pasien->nomor_rm ?? '-',
                    'keluhan' => $kunjungan->keluhan_awal ?? '-',
                    'status' => $kunjungan->status ?? 'Completed',
                    'waktu' => Carbon::parse($kunjungan->tanggal_kunjungan)->format('H:i'),
                    'no_antrian' => 'A001', // Generate based on order
                    'tanggal_kunjungan' => $date,
                    'prosedur' => '-', // Will be filled from EMR if needed
                ];
            }

            $result = array_values($groupedHistory);

            return response()->json([
                'status' => 'success',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching patient history', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil riwayat pasien'
            ], 500);
        }
    }

    // Get doctor's schedule
    public function getDokterSchedule(Request $request)
    {
        try {
            $user = $request->user();
            
            if ($user->role !== 'Dokter') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak.'
                ], 403);
            }

            // Cari data dokter
            $dokter = Dokter::where('user_id', $user->id)->first();
            if (!$dokter) {
                $dokter = Dokter::where('email', $user->email)->first();
            }
            
            if (!$dokter) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data dokter tidak ditemukan.'
                ], 404);
            }

            $dokterId = $dokter->id_dokter ?? $dokter->id;

            // Ambil jadwal dokter
            $schedules = JadwalDokter::where('dokter_id', $dokterId)
                ->get()
                ->map(function($jadwal) {
                    return [
                        'id' => $jadwal->id,
                        'hari' => trim($jadwal->hari, '"\''), // Clean quotes
                        'jam_mulai' => $jadwal->jam_mulai ?? $jadwal->jam_awal,
                        'jam_selesai' => $jadwal->jam_selesai,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $schedules
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching doctor schedule', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil jadwal dokter'
            ], 500);
        }
    }

    // Helper method untuk mendapatkan waktu praktik berikutnya
    private function getNextPracticeTime($dokterId)
    {
        try {
            $jadwal = JadwalDokter::where('dokter_id', $dokterId)->first();
            
            if ($jadwal) {
                $jamMulai = $jadwal->jam_mulai ?? $jadwal->jam_awal ?? '08:00';
                $jamSelesai = $jadwal->jam_selesai ?? '17:00';
                return $jamMulai . ' - ' . $jamSelesai;
            }
            
            return '08:00 - 17:00'; // Default
        } catch (\Exception $e) {
            return '08:00 - 17:00'; // Fallback
        }
    }

    // ==== ROLE DOKTER  =====

    public function registerDokter(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:user'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:user'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'Dokter',
        ]);

        $responseData = [
            'account' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ];

        // FIXED: Buat record pasien dengan nama_pasien default dari username
        if ($user->role === 'Dokter') {
            $dokter = Dokter::create([
                'user_id' => $user->id,
                'nama_dokter' => $request->username, // FIXED: Set default name
                // Kolom lain dibiarkan null untuk diisi nanti
            ]);

            $responseData['account']['dokter_id'] = $dokter->id;
        }

        $token = $user->createToken('authToken')->plainTextToken;
        $responseData['token'] = $token;

        return response()->json([
            'status' => 'success',
            'message' => 'Akun berhasil didaftarkan.',
            'data' => $responseData
        ], 201);
    }

    public function createDataEMR(Request $request)
    {
        $request->validate([
            'kunjungan_id' => ['required'],
            'riwayat_penyakit' => ['required'],
            'algergi' => ['required'],
            'hasil_periksa' => ['required'],
        ]);

        $dataKunjungan = Kunjungan::findOrFail($request->kunjungan_id);

        $dataEMR = EMR::create([
            'kunjungan_id' => $request->kunjungan_id,
            'riwayar_penyakit' => $request->riwayat_penyakit,
            'alergi' => $request->alergi,
            'hasil_periksa' => $request->hasil_periksa,
        ]);

        return response()->json([
            'success' => true,
            'status' => 200,
            'Data EMR' => $dataEMR,
            'message' => 'Data EMR Berhasil Ditambahkan'
        ]);
    }

    public function ubahStatusKunjungan(Request $request)
    {
        $dataKunjungan = Kunjungan::findOrFail($request->id);

        if ($dataKunjungan->status === 'Engaged') {
            $dataKunjungan->update([
                'status' => 'Succeed',
            ]);

            return response()->json([
                'success' => true,
                'Data Kunjungan' => $dataKunjungan,
                'message' => 'Berhasil mengubah status kunjungan menjadi Succeed'
            ]);
        }
    }
}
