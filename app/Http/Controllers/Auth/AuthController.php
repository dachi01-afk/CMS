<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\EMR;
use App\Models\JadwalDokter;
use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\Testimoni;
use App\Models\User;
use App\Models\Apoteker;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

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
            'available' => ! $exists,
            'message' => $exists
                ? ucfirst($request->type) . ' sudah digunakan'
                : ucfirst($request->type) . ' tersedia',
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

            $responseData['account']['pasien_id'] = $pasien->id; // ✅ Gunakan id_pasien
        }

        $token = $user->createToken('authToken')->plainTextToken;
        $responseData['token'] = $token;

        return response()->json([
            'status' => 'success',
            'message' => 'Akun berhasil didaftarkan.',
            'data' => $responseData,
        ], 201);
    }

    // Logika untuk Login dengan pesan error yang spesifik - FIXED
    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Cek apakah username ada
        $login = $request->login;

        $user = User::where('username', $login)
            ->orWhere('email', $login)
            ->first();

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username tidak ditemukan. Silakan periksa kembali atau daftar akun baru.',
                'error_type' => 'username_not_found',
            ], 401);
        }

        // Cek apakah password benar
        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Password yang Anda masukkan salah. Silakan coba lagi.',
                'error_type' => 'wrong_password',
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

            if (! $pasien) {
                // Try alternative approach - find by email
                $pasien = Pasien::where('email', $user->email)->first();

                if (! $pasien) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Data pasien tidak ditemukan.',
                        'error_type' => 'patient_data_missing',
                    ], 404);
                }
            }

            $responseData['account']['pasien_id'] = $pasien->id; // ✅ Gunakan id_pasien
        }

        if ($user->role === 'Dokter') {
            $dokter = Dokter::where('user_id', $user->id)->first();

            if (! $dokter) {
                // Try alternative approach - find by email
                $dokter = Dokter::where('email', $user->email)->first();
            }

            if ($dokter) {
                $responseData['account']['dokter_id'] = $dokter->id_dokter ?? $dokter->id;
            }
        }

        if ($user->role === 'Apoteker') {
            $apoteker = Apoteker::where('user_id', $user->id)->first();

            if (! $apoteker) {
                // Try alternative approach - find by email
                $apoteker = Apoteker::where('email', $user->email)->first();
            }

            if ($apoteker) {
                $responseData['account']['apoteker_id'] = $apoteker->user_id ?? $apoteker->id;
            }
        }

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login gagal. Periksa kembali username/email dan password.',
                'error_type' => 'invalid_credentials',
            ], 401);
        }
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil.',
        ]);
    }

    // Profile - FIXED
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

            if (! $pasien) {
                $pasien = Pasien::where('email', $user->email)->first();
            }

            if ($pasien) {
                $profileData['pasien_id'] = $pasien->id; // ✅ Gunakan id_pasien
                $profileData['nomor_rm'] = $pasien->nomor_rm ?? '';
                $profileData['nama_lengkap'] = $pasien->nama_pasien ?? '';
                $profileData['alamat'] = $pasien->alamat ?? '';
                $profileData['tanggal_lahir'] = $pasien->tanggal_lahir ?? '';
                $profileData['jenis_kelamin'] = $pasien->jenis_kelamin ?? '';
            }
        }

        if ($user->role === 'Dokter') {
            $dokter = Dokter::where('user_id', $user->id)->first();

            if (! $dokter) {
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
            'data' => $profileData,
        ]);
    }

    public function getDataSpesialisasiDokter()
    {
        try {
            // Get specialties from jenis_spesialis table
            $dataSpesialis = DB::table('jenis_spesialis')
                ->select('id', 'nama_spesialis as spesialisasi')
                ->orderBy('nama_spesialis')
                ->get();

            return response()->json([
                'status' => 'success',
                'Data Spesialis Dokter' => $dataSpesialis,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching specialties: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch specialties: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getDoctorsWithSpecialties()
    {
        try {
            $doctors = DB::table('dokter')
                ->leftJoin('jenis_spesialis', 'dokter.jenis_spesialis_id', '=', 'jenis_spesialis.id')
                ->select(
                    'dokter.id',
                    'dokter.nama_dokter',
                    'dokter.foto',
                    'dokter.deskripsi_dokter',
                    'dokter.pengalaman',
                    'dokter.jenis_spesialis_id',
                    'jenis_spesialis.nama_spesialis',
                    'jenis_spesialis.id as specialty_id'
                )
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $doctors,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching doctors with specialties: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch doctors: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getSpecialties()
    {
        try {
            $specialties = DB::table('jenis_spesialis')
                ->select('id', 'nama_spesialis')
                ->orderBy('nama_spesialis')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $specialties,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching specialties: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch specialties: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function getDoctorSchedules()
    {
        try {
            $schedules = DB::table('jadwal_dokter')
                ->join('dokter', 'jadwal_dokter.dokter_id', '=', 'dokter.id')
                ->leftJoin('jenis_spesialis', 'dokter.jenis_spesialis_id', '=', 'jenis_spesialis.id')
                ->select(
                    'jadwal_dokter.*',
                    'dokter.nama_dokter',
                    'jenis_spesialis.nama_spesialis'
                )
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $schedules
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching doctor schedules: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch doctor schedules: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createKunjungan(Request $request)
    {
        // Log incoming request for debugging
        Log::info('=== CREATE KUNJUNGAN AUTH CONTROLLER DEBUG ===', [
            'request_data' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        $request->validate([
            'pasien_id' => 'required|integer',
            'dokter_id' => 'required|integer',
            'tanggal_kunjungan' => 'required|date',
            'keluhan_awal' => 'required|string',
        ]);

        try {
            $user = $request->user();

            // Verify user is authenticated
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'status' => 'error',
                    'message' => 'User tidak terautentikasi'
                ], 401);
            }

            // Verify pasien exists and belongs to user
            $pasien = Pasien::find($request->pasien_id);
            if (!$pasien) {
                return response()->json([
                    'success' => false,
                    'status' => 'error',
                    'message' => 'Data pasien tidak ditemukan'
                ], 404);
            }

            // For additional security, verify pasien belongs to authenticated user
            if ($user->role === 'Pasien') {
                $userPasien = Pasien::where('user_id', $user->id)->first();
                if (!$userPasien) {
                    $userPasien = Pasien::where('email', $user->email)->first();
                }

                if (!$userPasien || $userPasien->id !== $request->pasien_id) {
                    return response()->json([
                        'success' => false,
                        'status' => 'error',
                        'message' => 'Akses ditolak. Pasien tidak sesuai dengan user yang login.'
                    ], 403);
                }
            }

            // Verify dokter exists
            $dokter = Dokter::find($request->dokter_id);
            if (!$dokter) {
                return response()->json([
                    'success' => false,
                    'status' => 'error',
                    'message' => 'Data dokter tidak ditemukan'
                ], 404);
            }

            // Create kunjungan
            $kunjungan = Kunjungan::create([
                'pasien_id' => $request->pasien_id,
                'dokter_id' => $request->dokter_id,
                'tanggal_kunjungan' => $request->tanggal_kunjungan,
                'keluhan_awal' => $request->keluhan_awal,
                'status' => 'menunggu',
            ]);

            Log::info('Kunjungan created successfully', [
                'kunjungan_id' => $kunjungan->id,
                'pasien_id' => $request->pasien_id,
                'dokter_id' => $request->dokter_id
            ]);

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => 'Kunjungan berhasil dibuat',
                'data' => [
                    'kunjungan_id' => $kunjungan->id,
                    'pasien_id' => $kunjungan->pasien_id,
                    'dokter_id' => $kunjungan->dokter_id,
                    'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                    'keluhan_awal' => $kunjungan->keluhan_awal,
                    'status' => $kunjungan->status,
                    'dokter_nama' => $dokter->nama_dokter,
                    'pasien_nama' => $pasien->nama_pasien,
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating kunjungan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Gagal membuat kunjungan: ' . $e->getMessage()
            ], 500);
        }
    }


    // UpdateProfile - FIXED
    public function updateProfile(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'alamat' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
        ]);

        $user = $request->user();

        Log::info('Update Profile Request', [
            'user_id' => $user->id,
            'request_data' => $request->all(),
        ]);

        if ($user->role === 'Pasien') {
            $pasien = Pasien::where('user_id', $user->id)->first();

            if (! $pasien) {
                Log::error('Pasien not found', ['user_id' => $user->id]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Data pasien tidak ditemukan.',
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
                'pasien_id' => $pasien->id_pasien, // ✅ Gunakan id_pasien
                'updated_data' => $updateData,
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
                ],
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
            'email' => 'required|email|exists:user,email',
        ]);

        $otp = sprintf('%06d', mt_rand(0, 999999));

        // Simpan OTP ke cache selama 10 menit
        Cache::put("forgot_password_otp_{$request->email}", $otp, 600);

        try {
            // Kirim email OTP pakai Blade template yang sama
            Mail::send('emails.otp_notification', [
                'otp' => $otp,
                'type' => 'Lupa Password',
                'expiration_minutes' => 10,
            ], function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Kode OTP untuk Lupa Password');
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Kode OTP telah dikirim ke email Anda. Silakan cek kotak masuk atau folder spam.',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal Kirim OTP Lupa Password', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'OTP berhasil dibuat tapi gagal dikirim via email. Silakan coba lagi nanti.',
            ], 500);
        }
    }

    public function verifyOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $cachedOtp = Cache::get("forgot_password_otp_{$request->email}");

        if (! $cachedOtp || $cachedOtp !== $request->otp) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP tidak valid atau sudah kedaluwarsa.',
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'OTP berhasil diverifikasi.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $cachedOtp = Cache::get("forgot_password_otp_{$request->email}");

        if (! $cachedOtp || $cachedOtp !== $request->otp) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP tidak valid atau sudah kedaluwarsa.',
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email tidak ditemukan.',
            ], 404);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Clear OTP cache
        Cache::forget("forgot_password_otp_{$request->email}");

        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil direset.',
        ]);
    }

    // ========== FORGOT USERNAME METHODS ==========

    public function sendUsernameOTP(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:user,email']);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email tidak ditemukan.',
            ], 404);
        }

        $otp = sprintf('%06d', mt_rand(0, 999999));
        Cache::put("forgot_username_otp_{$request->email}", $otp, 300);

        try {
            Mail::send('emails.otp_notification', [
                'otp' => $otp,
                'type' => 'Lupa Username',
                'expiration_minutes' => 5,
            ], function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Kode Verifikasi untuk Lupa Username');
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Kode OTP telah dikirim ke email Anda. Silakan cek kotak masuk.',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal Kirim OTP Username', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'OTP berhasil dibuat, namun gagal dikirim via email. Silakan coba verifikasi.',
            ], 500);
        }
    }

    public function verifyUsernameOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $cachedOtp = Cache::get("forgot_username_otp_{$request->email}");

        if (! $cachedOtp || $cachedOtp !== $request->otp) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP tidak valid atau sudah kedaluwarsa.',
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'OTP berhasil diverifikasi. Username Anda adalah: ' . $user->username,
            'data' => [
                'username' => $user->username,
            ],
        ]);
    }

    public function updateUsername(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('user', 'username')->ignore(User::where('email', $request->email)->value('id')),
            ],
        ]);

        $cachedOtp = Cache::get("forgot_username_otp_{$request->email}");

        if (! $cachedOtp || $cachedOtp !== $request->otp) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP belum diverifikasi atau sudah kedaluwarsa.',
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email tidak ditemukan.',
            ], 404);
        }

        $user->username = $request->username;
        $user->save();

        Cache::forget("forgot_username_otp_{$request->email}");

        return response()->json([
            'status' => 'success',
            'message' => 'Username berhasil diperbarui.',
            'data' => ['username' => $user->username],
        ]);
    }

    // ========== TESTIMONI METHODS ==========

    public function submitTestimoni(Request $request)
    {
        $request->validate([
            'nama_testimoni' => 'required|string|max:255',
            'umur' => 'required|string|max:10',
            'pekerjaan' => 'required|string|max:255',
            'isi_testimoni' => 'required|string',
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
            'data' => $testimoni,
        ], 201);
    }

    // ========== BOOKING SCHEDULE METHOD - FIXED ==========

    public function bookSchedule(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|integer',
            'day' => 'required|string',
            'time' => 'required|string',
            'keluhan' => 'required|string',
        ]);

        Log::info('=== BOOKING SCHEDULE START ===', [
            'request_data' => $request->all(),
        ]);

        $user = $request->user();
        if (! $user) {
            Log::error('User is null');

            return response()->json([
                'status' => 'error',
                'message' => 'User tidak valid',
            ], 401);
        }

        Log::info('User found:', ['user_id' => $user->id, 'email' => $user->email]);

        $pasien = Pasien::where('user_id', $user->id)->first();
        if (! $pasien) {
            $pasien = Pasien::where('email', $user->email)->first();
        }

        if (! $pasien) {
            Log::error('Pasien not found, creating new one');
            try {
                $pasien = Pasien::create([
                    'user_id' => $user->id,
                    'nama_pasien' => $user->username,
                    'email' => $user->email,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create pasien: ' . $e->getMessage());

                return response()->json([
                    'status' => 'error',
                    'message' => 'Data pasien tidak ditemukan dan gagal dibuat',
                ], 404);
            }
        }

        Log::info('Pasien found:', ['pasien_id' => $pasien->id]);

        $dokter = Dokter::find($request->doctor_id);
        if (! $dokter) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dokter tidak ditemukan',
            ], 404);
        }

        $tanggalKunjungan = $this->getNextDateForDay($request->day);

        try {
            $kunjungan = Kunjungan::create([
                'dokter_id' => $request->doctor_id,
                'pasien_id' => $pasien->id,
                'tanggal_kunjungan' => $tanggalKunjungan,
                'keluhan_awal' => $request->keluhan,
                'status' => 'menunggu',
            ]);

            Log::info('Kunjungan created successfully:', ['kunjungan_id' => $kunjungan->id]);

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
                        'status' => $kunjungan->status,
                        'dokter_nama' => $dokter->nama_dokter ?? 'Unknown',
                        'jadwal' => $request->day . ', ' . $request->time,
                    ],
                    'emr' => [
                        'pasien_id' => $kunjungan->pasien_id,
                        'kunjungan_id' => $kunjungan->id,
                    ],
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating kunjungan: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memesan jadwal: ' . $e->getMessage(),
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
            'Minggu' => 0,
        ];

        $targetDay = $dayMapping[$dayName] ?? 1;
        $today = Carbon::now();

        if ($today->dayOfWeek == $targetDay) {
            return $today->addWeek()->format('Y-m-d H:i:s');
        }

        $daysToAdd = ($targetDay - $today->dayOfWeek + 7) % 7;
        if ($daysToAdd == 0) {
            $daysToAdd = 7;
        }

        return $today->addDays($daysToAdd)->format('Y-m-d H:i:s');
    }

    // ========== EMR METHODS ==========

    public function getAllEmrPasien($id)
    {
        try {
            Log::info('getAllEmrPasien called', ['pasien_id' => $id]);

            $kunjunganList = Kunjungan::with(['dokter'])
                ->where('pasien_id', $id)
                ->orderBy('tanggal_kunjungan', 'desc')
                ->get();

            Log::info('Found kunjungan', ['count' => $kunjunganList->count()]);

            if ($kunjunganList->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Belum ada data kunjungan',
                    'data' => [],
                ]);
            }

            $emrData = $kunjunganList->map(function ($kunjungan) {
                $data = [
                    'id' => $kunjungan->id,
                    'kunjungan_id' => $kunjungan->id,
                    'pasien_id' => $kunjungan->pasien_id,
                    'dokter_id' => $kunjungan->dokter_id,
                    'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                    'keluhan_awal' => $kunjungan->keluhan_awal ?? '-',
                    'keluhan' => $kunjungan->keluhan_awal ?? '-',
                    'status' => $kunjungan->status ?? 'menunggu',
                    'nama_dokter' => $kunjungan->dokter->nama_dokter ?? '-',
                    'dokter_nama' => $kunjungan->dokter->nama_dokter ?? '-',
                    'spesialisasi' => $kunjungan->dokter->spesialis ?? '-',
                    'nama_rs_perujuk' => $kunjungan->nama_rs_perujuk,
                    'nama_dokter_perujuk' => $kunjungan->nama_dokter_perujuk,
                ];

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

                $resepObat = [];
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
                    Log::info('Resep table not found or error', ['error' => $e->getMessage()]);
                }

                $data['resep_obat'] = $resepObat;

                return $data;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data EMR berhasil diambil',
                'data' => $emrData->values(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching EMR data', [
                'pasien_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data EMR: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    public function getKunjunganDetail(Request $request, $kunjunganId)
    {
        try {
            $kunjungan = Kunjungan::with(['dokter', 'pasien'])->findOrFail($kunjunganId);

            $user = $request->user();
            if ($user->role === 'Pasien') {
                $pasien = Pasien::where('user_id', $user->id)->first();
                if (! $pasien) {
                    $pasien = Pasien::where('email', $user->email)->first();
                }

                if (! $pasien || $kunjungan->pasien_id !== $pasien->id_pasien) { // ✅ Gunakan id_pasien
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Akses ditolak',
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
                'resep_obat' => [],
            ];

            $emr = EMR::where('kunjungan_id', $kunjungan->id)->first();
            if ($emr) {
                $data['emr'] = [
                    'riwayat_penyakit' => $emr->riwayat_penyakit,
                    'alergi' => $emr->alergi,
                    'hasil_periksa' => $emr->hasil_periksa,
                ];
            }

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
                Log::info('Resep table not available', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'status' => 'success',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching kunjungan detail', [
                'kunjungan_id' => $kunjunganId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Data kunjungan tidak ditemukan',
            ], 404);
        }
    }

    // ========== APPOINTMENT MANAGEMENT - FIXED ==========

    public function checkDoctorAvailability(Request $request, $dokterId, $tanggal)
    {
        return response()->json([
            'status' => 'success',
            'available' => true,
            'message' => 'Dokter tersedia',
        ]);
    }

    public function getMyAppointments(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->role !== 'Pasien') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak. Hanya pasien yang dapat melihat appointment.',
                ], 403);
            }

            $pasien = Pasien::where('user_id', $user->id)->first();
            if (! $pasien) {
                $pasien = Pasien::where('email', $user->email)->first();
            }

            if (! $pasien) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data pasien tidak ditemukan.',
                ], 404);
            }

            $appointments = Kunjungan::with(['dokter'])
                ->where('pasien_id', $pasien->id_pasien) // ✅ Gunakan id_pasien
                ->orderBy('tanggal_kunjungan', 'desc')
                ->get()
                ->map(function ($kunjungan) {
                    return [
                        'id' => $kunjungan->id,
                        'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                        'keluhan_awal' => $kunjungan->keluhan_awal ?? '-',
                        'status' => $kunjungan->status ?? 'menunggu',
                        'dokter_nama' => $kunjungan->dokter->nama_dokter ?? '-',
                        'spesialisasi' => $kunjungan->dokter->spesialis ?? '-',
                        'can_cancel' => in_array($kunjungan->status ?? 'menunggu', ['menunggu']),
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $appointments,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching appointments', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data appointment',
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
                    'message' => 'Akses ditolak.',
                ], 403);
            }

            $pasien = Pasien::where('user_id', $user->id)->first();
            if (! $pasien) {
                $pasien = Pasien::where('email', $user->email)->first();
            }

            if (! $pasien) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data pasien tidak ditemukan.',
                ], 404);
            }

            $kunjungan = Kunjungan::where('id', $kunjunganId)
                ->where('pasien_id', $pasien->id_pasien) // ✅ Gunakan id_pasien
                ->first();

            if (! $kunjungan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data kunjungan tidak ditemukan.',
                ], 404);
            }

            if (! in_array($kunjungan->status ?? 'menunggu', ['menunggu'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kunjungan tidak dapat dibatalkan. Status saat ini: ' . ($kunjungan->status ?? 'menunggu'),
                ], 400);
            }

            $kunjungan->update(['status' => 'dibatalkan']);

            return response()->json([
                'status' => 'success',
                'message' => 'Kunjungan berhasil dibatalkan.',
                'data' => [
                    'id' => $kunjungan->id,
                    'status' => $kunjungan->status,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error cancelling appointment', [
                'error' => $e->getMessage(),
                'kunjungan_id' => $kunjunganId,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membatalkan kunjungan',
            ], 500);
        }
    }

    public function rescheduleAppointment(Request $request, $kunjunganId)
    {
        $request->validate([
            'tanggal_kunjungan_baru' => 'required|date|after:today',
            'alasan' => 'nullable|string|max:500',
        ]);

        try {
            $user = $request->user();

            if ($user->role !== 'Pasien') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak.',
                ], 403);
            }

            $pasien = Pasien::where('user_id', $user->id)->first();
            if (! $pasien) {
                $pasien = Pasien::where('email', $user->email)->first();
            }

            if (! $pasien) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data pasien tidak ditemukan.',
                ], 404);
            }

            $kunjungan = Kunjungan::where('id', $kunjunganId)
                ->where('pasien_id', $pasien->id_pasien) // ✅ Gunakan id_pasien
                ->first();

            if (! $kunjungan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data kunjungan tidak ditemukan.',
                ], 404);
            }

            if (! in_array($kunjungan->status ?? 'menunggu', ['menunggu'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jadwal kunjungan tidak dapat diubah. Status saat ini: ' . ($kunjungan->status ?? 'menunggu'),
                ], 400);
            }

            $tanggalLama = $kunjungan->tanggal_kunjungan;

            $kunjungan->update([
                'tanggal_kunjungan' => $request->tanggal_kunjungan_baru,
                'status' => 'menunggu',
            ]);

            Log::info('Appointment rescheduled', [
                'kunjungan_id' => $kunjungan->id,
                'pasien_id' => $pasien->id_pasien, // ✅ Gunakan id_pasien
                'tanggal_lama' => $tanggalLama,
                'tanggal_baru' => $request->tanggal_kunjungan_baru,
                'alasan' => $request->alasan,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Jadwal kunjungan berhasil diubah.',
                'data' => [
                    'id' => $kunjungan->id,
                    'tanggal_kunjungan_lama' => $tanggalLama,
                    'tanggal_kunjungan_baru' => $kunjungan->tanggal_kunjungan,
                    'status' => $kunjungan->status,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error rescheduling appointment', [
                'error' => $e->getMessage(),
                'kunjungan_id' => $kunjunganId,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengubah jadwal kunjungan',
            ], 500);
        }
    }

    // ========== DOCTOR SPECIFIC METHODS ==========

    public function getDokterDashboardStats(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->role !== 'Dokter') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak. Hanya dokter yang dapat mengakses endpoint ini.',
                ], 403);
            }

            $dokter = Dokter::where('user_id', $user->id)->first();
            if (! $dokter) {
                $dokter = Dokter::where('email', $user->email)->first();
            }

            if (! $dokter) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data dokter tidak ditemukan.',
                ], 404);
            }

            $dokterId = $dokter->id_dokter ?? $dokter->id;

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
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching doctor dashboard stats', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil statistik dashboard',
            ], 500);
        }
    }

    public function getTodayPatients(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->role !== 'Dokter') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak. Hanya dokter yang dapat mengakses endpoint ini.',
                ], 403);
            }

            $dokter = Dokter::where('user_id', $user->id)->first();
            if (! $dokter) {
                $dokter = Dokter::where('email', $user->email)->first();
            }

            if (! $dokter) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data dokter tidak ditemukan.',
                ], 404);
            }

            $dokterId = $dokter->id_dokter ?? $dokter->id;
            $today = Carbon::today();

            $todayPatients = Kunjungan::with(['pasien', 'dokter'])
                ->where('dokter_id', $dokterId)
                ->whereDate('tanggal_kunjungan', $today)
                ->orderBy('tanggal_kunjungan', 'asc')
                ->get()
                ->map(function ($kunjungan, $index) {
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
                'data' => $todayPatients,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching today patients', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data pasien hari ini',
            ], 500);
        }
    }

    public function updatePatientStatus(Request $request, $kunjunganId)
    {
        $request->validate([
            'status' => 'required|string|in:Pending,Engaged,Completed,Succeed',
        ]);

        try {
            $user = $request->user();

            if ($user->role !== 'Dokter') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak.',
                ], 403);
            }

            $dokter = Dokter::where('user_id', $user->id)->first();
            if (! $dokter) {
                $dokter = Dokter::where('email', $user->email)->first();
            }

            if (! $dokter) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data dokter tidak ditemukan.',
                ], 404);
            }

            $dokterId = $dokter->id_dokter ?? $dokter->id;

            $kunjungan = Kunjungan::where('id', $kunjunganId)
                ->where('dokter_id', $dokterId)
                ->first();

            if (! $kunjungan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data kunjungan tidak ditemukan atau bukan milik Anda.',
                ], 404);
            }

            $kunjungan->update(['status' => $request->status]);

            Log::info('Patient status updated', [
                'kunjungan_id' => $kunjunganId,
                'dokter_id' => $dokterId,
                'new_status' => $request->status,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Status pasien berhasil diperbarui',
                'data' => [
                    'kunjungan_id' => $kunjungan->id,
                    'status' => $kunjungan->status,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating patient status', [
                'error' => $e->getMessage(),
                'kunjungan_id' => $kunjunganId,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui status pasien',
            ], 500);
        }
    }

    public function submitExamination(Request $request, $kunjunganId)
    {
        $request->validate([
            'prosedur' => 'required|string',
            'informasi_kondisi' => 'required|string',
        ]);

        try {
            $user = $request->user();

            if ($user->role !== 'Dokter') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak.',
                ], 403);
            }

            $dokter = Dokter::where('user_id', $user->id)->first();
            if (! $dokter) {
                $dokter = Dokter::where('email', $user->email)->first();
            }

            if (! $dokter) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data dokter tidak ditemukan.',
                ], 404);
            }

            $dokterId = $dokter->id_dokter ?? $dokter->id;

            $kunjungan = Kunjungan::where('id', $kunjunganId)
                ->where('dokter_id', $dokterId)
                ->first();

            if (! $kunjungan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data kunjungan tidak ditemukan.',
                ], 404);
            }

            $kunjungan->update(['status' => 'Succeed']);

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
                'emr_id' => $emr->id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pemeriksaan berhasil disimpan',
                'data' => [
                    'kunjungan_id' => $kunjungan->id,
                    'status' => $kunjungan->status,
                    'emr_id' => $emr->id,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error submitting examination', [
                'error' => $e->getMessage(),
                'kunjungan_id' => $kunjunganId,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan pemeriksaan',
            ], 500);
        }
    }

    public function getObatList(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->role !== 'Dokter') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak.',
                ], 403);
            }

            $obatList = DB::table('obat')
                ->select('id as id_obat', 'nama_obat', 'jenis', 'dosis', 'stok', 'satuan')
                ->where('stok', '>', 0)
                ->orderBy('nama_obat')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $obatList,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching obat list', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data obat',
            ], 500);
        }
    }

    public function createPrescription(Request $request, $kunjunganId)
    {
        $request->validate([
            'resep' => 'required|array',
            'resep.*.obat_id' => 'required|integer',
            'resep.*.jumlah_obat' => 'required|integer|min:1',
            'resep.*.aturan_pakai' => 'required|string',
        ]);

        try {
            $user = $request->user();

            if ($user->role !== 'Dokter') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak.',
                ], 403);
            }

            $dokter = Dokter::where('user_id', $user->id)->first();
            if (! $dokter) {
                $dokter = Dokter::where('email', $user->email)->first();
            }

            if (! $dokter) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data dokter tidak ditemukan.',
                ], 404);
            }

            $dokterId = $dokter->id_dokter ?? $dokter->id;

            $kunjungan = Kunjungan::where('id', $kunjunganId)
                ->where('dokter_id', $dokterId)
                ->first();

            if (! $kunjungan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data kunjungan tidak ditemukan.',
                ], 404);
            }

            foreach ($request->resep as $resepItem) {
                $obat = DB::table('obat')->where('id', $resepItem['obat_id'])->first();

                if (! $obat) {
                    continue;
                }

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

                DB::table('obat')
                    ->where('id', $resepItem['obat_id'])
                    ->decrement('stok', $resepItem['jumlah_obat']);
            }

            Log::info('Prescription created', [
                'kunjungan_id' => $kunjunganId,
                'dokter_id' => $dokterId,
                'resep_count' => count($request->resep),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Resep obat berhasil dibuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating prescription', [
                'error' => $e->getMessage(),
                'kunjungan_id' => $kunjunganId,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat resep obat',
            ], 500);
        }
    }

    public function getPrescriptions(Request $request, $kunjunganId)
    {
        try {
            $user = $request->user();

            if ($user->role !== 'Dokter') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak.',
                ], 403);
            }

            $prescriptions = DB::table('resep')
                ->where('kunjungan_id', $kunjunganId)
                ->get()
                ->map(function ($resep) {
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
                'data' => $prescriptions,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching prescriptions', [
                'error' => $e->getMessage(),
                'kunjungan_id' => $kunjunganId,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data resep',
            ], 500);
        }
    }

    public function getPatientHistory(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->role !== 'Dokter') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak.',
                ], 403);
            }

            $dokter = Dokter::where('user_id', $user->id)->first();
            if (! $dokter) {
                $dokter = Dokter::where('email', $user->email)->first();
            }

            if (! $dokter) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data dokter tidak ditemukan.',
                ], 404);
            }

            $dokterId = $dokter->id_dokter ?? $dokter->id;
            $search = $request->get('search', '');

            $query = Kunjungan::with(['pasien'])
                ->where('dokter_id', $dokterId)
                ->whereDate('tanggal_kunjungan', '<', Carbon::today());

            if (! empty($search)) {
                $query->whereHas('pasien', function ($q) use ($search) {
                    $q->where('nama_pasien', 'like', "%{$search}%")
                        ->orWhere('nomor_rm', 'like', "%{$search}%");
                });
            }
            $kunjunganList = $query->orderBy('tanggal_kunjungan', 'desc')->get();

            // Group by date
            $groupedHistory = [];
            foreach ($kunjunganList as $kunjungan) {
                $date = Carbon::parse($kunjungan->tanggal_kunjungan)->format('Y-m-d');

                if (! isset($groupedHistory[$date])) {
                    $groupedHistory[$date] = [
                        'tanggal' => $date,
                        'jumlah' => 0,
                        'pasien' => [],
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
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching patient history', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil riwayat pasien',
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
                    'message' => 'Akses ditolak.',
                ], 403);
            }

            // Cari data dokter
            $dokter = Dokter::where('user_id', $user->id)->first();
            if (! $dokter) {
                $dokter = Dokter::where('email', $user->email)->first();
            }

            if (! $dokter) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data dokter tidak ditemukan.',
                ], 404);
            }

            $dokterId = $dokter->id_dokter ?? $dokter->id;

            // Ambil jadwal dokter
            $schedules = JadwalDokter::where('dokter_id', $dokterId)
                ->get()
                ->map(function ($jadwal) {
                    return [
                        'id' => $jadwal->id,
                        'hari' => trim($jadwal->hari, '"\''), // Clean quotes
                        'jam_mulai' => $jadwal->jam_mulai ?? $jadwal->jam_awal,
                        'jam_selesai' => $jadwal->jam_selesai,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $schedules,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching doctor schedule', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil jadwal dokter',
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
            'data' => $responseData,
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
            'message' => 'Data EMR Berhasil Ditambahkan',
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
                'message' => 'Berhasil mengubah status kunjungan menjadi Succeed',
            ]);
        }
    }
}
