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
use App\Models\Kunjungan;
use App\Models\EMR;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Mail\OtpMail;
// ... import lainnya
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

            $responseData['account']['pasien_id'] = $pasien->id;
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

    // Profile - FIXED: Return consistent field names
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
                // FIXED: Return nama_lengkap untuk konsistensi dengan Flutter
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
                $profileData['spesialis'] = $dokter->spesialis ?? 'Umum';
                $profileData['foto_profile'] = $dokter->foto_profile ?? $dokter->foto ?? null;
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
            'message' => 'OTP berhasil diverifikasi. Username Anda adalah: ' . $user->username, // Tampilkan username di sini
            'data' => [
                'username' => $user->username
            ]
        ]);
    }

    public function updateUsername(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'username' => 'required|string|max:255|unique:user,username'
        ]);

        $cachedOtp = Cache::get("forgot_username_otp_{$request->email}");

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
            'username' => $request->username
        ]);

        Cache::forget("forgot_username_otp_{$request->email}");

        return response()->json([
            'status' => 'success',
            'message' => 'Username berhasil diperbarui.',
            'data' => [
                'username' => $user->username
            ]
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
        return response()->json([
            'status' => 'success',
            'message' => 'Booking schedule feature will be implemented'
        ]);
    }

    public function getAllEmrPasien($id)
    {
        return response()->json([
            'status' => 'success',
            'data' => []
        ]);
    }

    public function getEmrPasien($kunjunganId)
    {
        return response()->json([
            'status' => 'success',
            'data' => []
        ]);
    }

    public function getDokterDashboardStats(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => []
        ]);
    }

    public function getTodayPatients(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => []
        ]);
    }

    public function updatePatientStatus(Request $request, $kunjunganId)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Status updated'
        ]);
    }

    public function submitExamination(Request $request, $kunjunganId)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Examination submitted'
        ]);
    }

    public function getObatList(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => []
        ]);
    }

    public function createPrescription(Request $request, $kunjunganId)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Prescription created'
        ]);
    }

    public function getPrescriptions(Request $request, $kunjunganId)
    {
        return response()->json([
            'status' => 'success',
            'data' => []
        ]);
    }

    public function getPatientHistory(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => []
        ]);
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
