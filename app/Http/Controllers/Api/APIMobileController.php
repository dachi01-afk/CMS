<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\EMR;
use App\Models\Kunjungan;
use App\Models\Obat;
use App\Models\Pasien;
use App\Models\Pembayaran;
use App\Models\Resep;
use App\Models\MetodePembayaran;
use App\Models\Testimoni;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
// use Midtrans\Config;
// use Midtrans\Snap;
// use Midtrans\Notification;
use App\Models\Poli;
use App\Http\Controllers\Api\Concerns\TransformsNotifications;

class APIMobileController extends Controller
{

    // public function __construct()
    // {
    //     // HANYA konfigurasi yang diperlukan untuk server-side
    //     Config::$serverKey = config('midtrans.server_key');
    //     Config::$isProduction = config('midtrans.is_production', false);
    //     Config::$isSanitized = true;
    //     Config::$is3ds = true;

    //     Log::info('Midtrans Configuration:', [
    //         'server_key_prefix' => substr(config('midtrans.server_key'), 0, 10),
    //         'is_production' => config('midtrans.is_production', false),
    //     ]);
    // }


    /** LOGIN */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'password' => 'required|string|min:6',
            ], [
                'username.required' => 'Username tidak boleh kosong.',
                'password.required' => 'Password tidak boleh kosong.',
                'password.min'      => 'Password minimal 6 karakter.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $user = User::where('username', $request->username)->first();

            // Username tidak ditemukan / salah
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Username salah',
                ], 401);
            }

            // Password salah
            if (! Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password salah',
                ], 401);
            }

            // Kredensial ok → buat token
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user'       => $user,
                    'token'      => $token,
                    'token_type' => 'Bearer',
                ],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
            ], 500);
        }
    }


    /** REGISTER */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:3|unique:user,username', // <--
            'email'    => 'required|email|unique:user,email',           // <--
            'password' => 'required|string|min:6',
        ], [
            'username.required' => 'Username tidak boleh kosong.',
            'username.min'      => 'Username minimal 3 karakter.',
            'username.unique'   => 'Username sudah digunakan.',
            'email.required'    => 'Email tidak boleh kosong.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah terdaftar.',
            'password.required' => 'Password tidak boleh kosong.',
            'password.min'      => 'Password minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                $user = User::create([
                    'username' => $request->username,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    'role'     => 'Pasien',
                ]);

                Pasien::create([
                    'user_id'        => $user->id,
                    'nama_pasien'    => null,
                    'alamat'         => null,
                    'tanggal_lahir'  => null,
                    'jenis_kelamin'  => null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Registrasi berhasil',
                    'data'    => $user,
                ], 201);
            });
        } catch (\Throwable $e) {
            Log::error('Register error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
            ], 500);
        }
    }

    /**
     * Get recent notifications for polling
     */
    // public function getRecentNotifications(Request $request)
    // {
    //     $user = $request->user(); // auth:sanctum
    //     if (!$user) {
    //         return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    //     }

    //     $sinceParam = $request->query('since');
    //     $limit      = (int) $request->query('limit', 50);

    //     $since = $sinceParam ? Carbon::parse($sinceParam) : now()->subDay();

    //     $items = Notification::where('user_id', $user->id)
    //         ->where('created_at', '>=', $since)
    //         ->orderBy('created_at', 'desc')
    //         ->limit($limit)
    //         ->get();

    //     return response()->json([
    //         'success' => true,
    //         'data'    => $items,
    //     ]);
    // }

    /**
     * Mark notification as read
     */
    // public function markNotificationAsRead(Request $request, $id)
    // {
    //     $user = $request->user(); // auth:sanctum
    //     if (!$user) {
    //         return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    //     }

    //     $notif = Notification::where('id', $id)
    //         ->where('user_id', $user->id)
    //         ->firstOrFail();

    //     $notif->is_read = true;
    //     $notif->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Notification marked as read',
    //     ]);
    // }

    /**
     * Create notification (internal use)
     */
    // protected function createNotification(int $userId, string $title, string $body, array $data = []): Notification
    // {
    //     return Notification::create([
    //         'user_id' => $userId,
    //         'title'   => $title,
    //         'body'    => $body,
    //         'data'    => $data,
    //         'is_read' => false,
    //     ]);
    // }


    public function getProfile(Request $request)
    {
        $user = $request->user();                       // asumsi sanctum
        $pasien = \App\Models\Pasien::where('user_id', $user->id)->firstOrFail();

        $this->ensureQrCodePasien($pasien);             // ← penting

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $pasien->id,
                'nama_pasien' => $pasien->nama_pasien,
                'alamat' => $pasien->alamat,
                'tanggal_lahir' => $pasien->tanggal_lahir,
                'jenis_kelamin' => $pasien->jenis_kelamin,
                'foto_pasien' => $pasien->foto_pasien,
                'qr_code_pasien' => $pasien->qr_code_pasien,   // ← kirim ke FE
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $pasien = Pasien::where('user_id', $user->id)->first();
            if (! $pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pasien tidak ditemukan',
                ], 404);
            }

            $validated = $request->validate([
                'nama_pasien' => 'required|string|max:255',
                'alamat' => 'nullable|string|max:255',
                'tanggal_lahir' => 'nullable|date',
                'jenis_kelamin' => 'nullable|string|in:Laki-laki,Perempuan',
                'foto_pasien' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $pathFotoPasien = $pasien->foto_pasien;

            if ($request->hasFile('foto_pasien')) {
                if ($pasien->foto_pasien && Storage::disk('public')->exists($pasien->foto_pasien)) {
                    Storage::disk('public')->delete($pasien->foto_pasien);
                }

                $fileFoto = $request->file('foto_pasien');
                $namaFoto = 'pasien_' . $user->id . '_' . time() . '.' . $fileFoto->getClientOriginalExtension();
                $pathFotoPasien = $fileFoto->storeAs('Foto-Pasien', $namaFoto, 'public');
            }

            $pasien->update([
                'nama_pasien' => $validated['nama_pasien'],
                'alamat' => $validated['alamat'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'foto_pasien' => $pathFotoPasien,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => $pasien->fresh(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating profile: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getJadwalDokter(Request $request)
    {
        try {
            $jadwal = \App\Models\JadwalDokter::with(['dokter.jenisSpesialis'])->get();

            $hariMapping = [
                'Senin' => 1,
                'Selasa' => 2,
                'Rabu' => 3,
                'Kamis' => 4,
                'Jumat' => 5,
                'Sabtu' => 6,
                'Minggu' => 0,
            ];

            $tz = config('app.timezone') ?: 'Asia/Jakarta';
            $today = Carbon::now($tz)->startOfDay();

            $jadwalWithDates = $jadwal->map(function ($item) use ($hariMapping, $today) {
                $hari = $item->hari;
                $hariNumber = $hariMapping[$hari] ?? null;

                if ($hariNumber !== null) {
                    $tanggalTerdekat = $this->getNextDateByDay($hariNumber, $today);

                    if ($tanggalTerdekat->lt($today)) {
                        $tanggalTerdekat = $tanggalTerdekat->addWeek();
                    }

                    $item->tanggal_terdekat = $tanggalTerdekat->toDateString();
                    $item->tanggal_terdekat_formatted = $this->formatTanggalIndonesia($tanggalTerdekat);
                    $item->hari_selisih = $this->getDayDifference($tanggalTerdekat);
                } else {
                    $item->tanggal_terdekat = null;
                    $item->tanggal_terdekat_formatted = null;
                    $item->hari_selisih = 999;
                }

                return $item;
            });

            $jadwalSorted = $jadwalWithDates->sortBy('hari_selisih')->values();

            return response()->json([
                'success' => true,
                'data' => $jadwalSorted,
            ]);
        } catch (\Exception $e) {
            log::error('Error getting jadwal dokter: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil jadwal dokter: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function getNextDateByDay(int $dayOfWeek, ?Carbon $from = null): Carbon
    {
        $tz = config('app.timezone') ?: 'Asia/Jakarta';
        $from = $from ? $from->copy()->startOfDay() : Carbon::now($tz)->startOfDay();
        $daysUntilTarget = ($dayOfWeek - $from->dayOfWeek + 7) % 7;

        // HAPUS SEMUA PENGECEKAN JAM - biar Flutter yang handle

        $target = $from->copy()->addDays($daysUntilTarget);
        return $target;
    }

    private function formatTanggalIndonesia($date)
    {
        if (! $date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        $bulanIndonesia = [
            '01' => 'Jan',
            '02' => 'Feb',
            '03' => 'Mar',
            '04' => 'Apr',
            '05' => 'Mei',
            '06' => 'Jun',
            '07' => 'Jul',
            '08' => 'Ags',
            '09' => 'Sep',
            '10' => 'Okt',
            '11' => 'Nov',
            '12' => 'Des',
        ];

        $hari = $date->format('j');
        $bulan = $bulanIndonesia[$date->format('m')];
        $tahun = $date->format('Y');

        return "{$hari} {$bulan} {$tahun}";
    }

    private function getDayDifference($targetDate)
    {
        $tz = config('app.timezone') ?: 'Asia/Jakarta';

        if (! $targetDate instanceof Carbon) {
            $targetDate = Carbon::parse($targetDate);
        }

        $today = Carbon::now($tz)->startOfDay();

        return $today->diffInDays($targetDate);
    }

    public function ubahStatusKunjungan(Request $request, $id)
    {
        $validated = $request->validate([
            'status'     => 'required|string|max:50',
            'no_antrian' => 'nullable|integer|min:1',
        ]);

        $kunjungan = Kunjungan::findOrFail($id);

        $kunjungan->status = $validated['status'];
        if (array_key_exists('no_antrian', $validated)) {
            $kunjungan->no_antrian = $validated['no_antrian'];
        }
        $kunjungan->save();

        // === Kirim notifikasi ke pasien ===
        try {
            $title = 'Status Kunjungan Diperbarui';
            // Kamu bisa sesuaikan wording per status:
            // switch ($kunjungan->status) { case 'Diterima': ... }
            $body  = 'Status kunjungan Anda kini: ' . ($kunjungan->status ?? '-');
            if (!empty($kunjungan->no_antrian)) {
                $body .= ' | No. Antrian: ' . $kunjungan->no_antrian;
            }

            $this->notifyPasienFromKunjungan($kunjungan, $title, $body, [
                'changed_by' => 'admin',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim notif ubahStatusKunjungan: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Status kunjungan berhasil diperbarui',
            'data'    => $kunjungan,
        ]);
    }

    public function getDokterBySpesialisasi($spesialisasiId)
    {
        try {
            $spesialisasi = DB::table('jenis_spesialis')
                ->where('id', $spesialisasiId)
                ->first();

            if (! $spesialisasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Spesialisasi tidak ditemukan',
                ], 404);
            }

            $dokterList = Dokter::with([
                'jenisSpesialis',
                'jadwalDokter' => function ($query) {
                    $query->orderBy('hari');
                },
            ])
                ->where('jenis_spesialis_id', $spesialisasiId)
                ->get();

            $hariMapping = [
                'Senin' => 1,
                'Selasa' => 2,
                'Rabu' => 3,
                'Kamis' => 4,
                'Jumat' => 5,
                'Sabtu' => 6,
                'Minggu' => 0,
            ];

            $dokterWithSchedules = $dokterList->map(function ($dokter) use ($hariMapping) {
                $jadwalWithDates = $dokter->jadwalDokter->map(function ($jadwal) use ($hariMapping) {
                    $hari = $jadwal->hari;
                    $hariNumber = $hariMapping[$hari] ?? null;

                    if ($hariNumber !== null) {
                        $tanggalTerdekat = $this->getNextDateByDay($hariNumber);
                        $jadwal->tanggal_terdekat = $tanggalTerdekat;
                        $jadwal->tanggal_terdekat_formatted = $this->formatTanggalIndonesia($tanggalTerdekat);
                        $jadwal->hari_selisih = $this->getDayDifference($tanggalTerdekat);
                    } else {
                        $jadwal->tanggal_terdekat = null;
                        $jadwal->tanggal_terdekat_formatted = null;
                        $jadwal->hari_selisih = 999;
                    }

                    return $jadwal;
                });

                $dokter->setAttribute('jadwalDokter', $jadwalWithDates->sortBy('hari_selisih')->values());

                return $dokter;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'spesialisasi' => $spesialisasi,
                    'dokter_list' => $dokterWithSchedules,
                    'total_dokter' => $dokterWithSchedules->count(),
                ],
                'message' => "Berhasil mengambil daftar dokter spesialisasi {$spesialisasi->nama_spesialis}",
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error getting dokter by spesialisasi: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dokter: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function batalkanStatusKunjungan(Request $request)
    {
        try {
            Log::info('=== BATALKAN KUNJUNGAN START ===');
            Log::info('Request method: ' . $request->method());
            Log::info('Request data: ', $request->all());

            $request->validate([
                'id' => 'required|integer|exists:kunjungan,id',
            ]);

            $kunjunganId = $request->input('id');
            Log::info('Processing kunjungan ID: ' . $kunjunganId);

            $dataKunjungan = Kunjungan::findOrFail($kunjunganId);
            Log::info('Found kunjungan before update: ', $dataKunjungan->toArray());

            if (! in_array($dataKunjungan->status, ['Pending', 'Confirmed', 'Waiting'])) {
                Log::warning('Cannot cancel kunjungan with status: ' . $dataKunjungan->status);

                return response()->json([
                    'success' => false,
                    'status' => 400,
                    'message' => 'Kunjungan dengan status "' . $dataKunjungan->status . '" tidak dapat dibatalkan',
                    'Data Kunjungan' => $dataKunjungan,
                ], 400);
            }

            $updatedKunjungan = DB::transaction(function () use ($kunjunganId) {
                $affected = DB::table('kunjungan')
                    ->where('id', $kunjunganId)
                    ->update([
                        'status' => 'Canceled',
                        'no_antrian' => null,
                        'updated_at' => now(),
                    ]);

                Log::info('Rows affected by update: ' . $affected);

                if ($affected === 0) {
                    throw new \Exception('Gagal memperbarui data kunjungan');
                }

                return Kunjungan::find($kunjunganId);
            });

            Log::info('Updated kunjungan after transaction: ', $updatedKunjungan->toArray());

            if ($updatedKunjungan->status !== 'Canceled') {
                Log::error('Status update failed - still: ' . $updatedKunjungan->status);

                return response()->json([
                    'success' => false,
                    'status' => 500,
                    'message' => 'Gagal mengubah status kunjungan',
                    'Data Kunjungan' => $updatedKunjungan,
                ], 500);
            }

            return response()->json([
                'success' => true,
                'status' => 200,
                'Data Kunjungan' => $updatedKunjungan,
                'message' => 'Berhasil membatalkan kunjungan. Status diubah menjadi Canceled dan nomor antrian dihapus.',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ', $e->errors());

            return response()->json([
                'success' => false,
                'status' => 422,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Kunjungan not found: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Data kunjungan tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Exception in batalkanStatusKunjungan: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }


    private function isProfileComplete($pasienId)
    {
        $pasien = Pasien::find($pasienId);

        if (!$pasien) {
            return false;
        }

        // Cek field wajib yang harus diisi
        $requiredFields = [
            'nama_pasien',
            'alamat',
            'tanggal_lahir',
            'jenis_kelamin'
        ];

        foreach ($requiredFields as $field) {
            if (empty($pasien->$field)) {
                return false;
            }
        }

        return true;
    }


    public function bookingDokter(Request $request)
    {
        try {
            Log::info('🔥 bookingDokter called with data: ', $request->all());

            $request->validate([
                'pasien_id' => ['required', 'exists:pasien,id'],
                'poli_id' => ['required', 'exists:poli,id'],
                'tanggal_kunjungan' => ['required', 'date'],
                'keluhan_awal' => ['required', 'string'],
            ]);

            $pasienId = $request->pasien_id;

            // VALIDASI PROFIL LENGKAP
            if (!$this->isProfileComplete($pasienId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mohon lengkapi data profil Anda terlebih dahulu sebelum membuat janji',
                    'error_code' => 'PROFILE_INCOMPLETE'
                ], 422);
            }

            $tanggalKunjungan = $request->tanggal_kunjungan;
            $poliId = $request->poli_id;
            $pasienId = $request->pasien_id;

            Log::info("🎯 Processing booking for pasien_id: $pasienId, poli_id: $poliId, tanggal: $tanggalKunjungan");

            // ENHANCED: Cek existing booking dengan status yang tidak boleh duplikasi
            $activeStatuses = ['Pending', 'Confirmed', 'Waiting', 'Engaged'];
            $existingActiveBooking = Kunjungan::where('pasien_id', $pasienId)
                ->where('poli_id', $poliId)
                ->where('tanggal_kunjungan', $tanggalKunjungan)
                ->whereIn('status', $activeStatuses)
                ->first();

            if ($existingActiveBooking) {
                Log::info("❌ Active booking found for pasien_id: $pasienId, poli_id: $poliId, tanggal: $tanggalKunjungan, status: {$existingActiveBooking->status}");

                // Pesan yang lebih spesifik berdasarkan status
                $statusMessages = [
                    'Pending' => 'Anda sudah memiliki janji yang menunggu konfirmasi dengan poli ini pada tanggal yang sama.',
                    'Confirmed' => 'Anda sudah memiliki janji yang telah dikonfirmasi dengan poli ini pada tanggal yang sama.',
                    'Waiting' => 'Anda sudah terdaftar dalam antrian dengan poli ini pada tanggal yang sama.',
                    'Engaged' => 'Anda sedang dalam proses konsultasi dengan poli ini pada tanggal yang sama.'
                ];

                $message = $statusMessages[$existingActiveBooking->status] ??
                    'Anda sudah memiliki jadwal dengan poli ini pada tanggal yang sama.';

                $message .= ' Silakan pilih tanggal lain atau batalkan janji yang sudah ada.';

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error_code' => 'DUPLICATE_ACTIVE_BOOKING',
                    'existing_booking' => [
                        'id' => $existingActiveBooking->id,
                        'status' => $existingActiveBooking->status,
                        'no_antrian' => $existingActiveBooking->no_antrian,
                        'tanggal_kunjungan' => $existingActiveBooking->tanggal_kunjungan
                    ]
                ], 422);
            }

            // OPTIONAL: Cek apakah ada booking dengan status Cancelled atau Success pada hari yang sama
            // Ini untuk memberikan informasi tambahan, tapi tidak menghalangi booking baru
            $previousBookings = Kunjungan::where('pasien_id', $pasienId)
                ->where('poli_id', $poliId)
                ->where('tanggal_kunjungan', $tanggalKunjungan)
                ->whereIn('status', ['Cancelled', 'Success', 'Completed'])
                ->get();

            if ($previousBookings->count() > 0) {
                Log::info("ℹ️ Found {$previousBookings->count()} previous booking(s) with Cancelled/Success status for same date");
            }

            $result = DB::transaction(function () use ($tanggalKunjungan, $poliId, $pasienId, $request) {
                // GANTI query untuk mencari kunjungan terakhir berdasarkan poli
                $lastKunjungan = Kunjungan::where('tanggal_kunjungan', $tanggalKunjungan)
                    ->where('poli_id', $poliId)
                    ->orderByDesc('no_antrian')
                    ->lockForUpdate()
                    ->first();

                Log::info('🔍 Last kunjungan found: ', $lastKunjungan ? $lastKunjungan->toArray() : ['none']);

                if ($lastKunjungan && $lastKunjungan->no_antrian) {
                    $nextNumber = (int) $lastKunjungan->no_antrian + 1;
                    Log::info("📈 Next number calculated from existing: $nextNumber");
                } else {
                    $nextNumber = 1;
                    Log::info("🆕 Starting fresh with number: $nextNumber");
                }

                $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
                Log::info("🎫 Formatted number: $formattedNumber");

                // Create new booking
                $kunjungan = new Kunjungan;
                $kunjungan->pasien_id = $pasienId;
                $kunjungan->poli_id = $poliId;
                $kunjungan->tanggal_kunjungan = $tanggalKunjungan;
                $kunjungan->no_antrian = $formattedNumber;
                $kunjungan->keluhan_awal = $request->keluhan_awal;
                $kunjungan->status = 'Pending';
                $kunjungan->save();

                Log::info('✅ Kunjungan created: ', $kunjungan->toArray());

                return [
                    'kunjungan' => $kunjungan,
                    'no_antrian' => $formattedNumber,
                ];
            });

            $responseMessage = 'Kunjungan berhasil dibuat';

            // Tambahkan informasi jika ada booking sebelumnya yang dibatalkan/selesai
            if (isset($previousBookings) && $previousBookings->count() > 0) {
                $responseMessage .= '. Catatan: Anda pernah memiliki janji dengan poli ini pada tanggal yang sama yang telah selesai/dibatalkan.';
            }

            return response()->json([
                'success' => true,
                'message' => $responseMessage,
                'Data Kunjungan' => $result['kunjungan'],
                'Data No Antrian' => $result['no_antrian'],
            ], 200);
        } catch (\Exception $e) {
            Log::error('❌ Exception in bookingDokter: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat kunjungan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Di APIMobileController.php - Ganti method getRiwayatKunjungan

    private function calculateFinalStatus($kunjungan)
    {
        try {
            // Cek apakah ada pembayaran untuk kunjungan ini
            $emr = EMR::where('kunjungan_id', $kunjungan->id)->first();
            if ($emr) {
                $pembayaran = Pembayaran::where('emr_id', $emr->id)->first();
                if ($pembayaran && strtolower(trim($pembayaran->status)) === 'sudah bayar') {
                    return 'Succeed';
                }
            }

            return $kunjungan->status ?? 'Pending';
        } catch (\Exception $e) {
            Log::warning('Error calculating final status: ' . $e->getMessage());
            return $kunjungan->status ?? 'Pending';
        }
    }
    public function getRiwayatKunjungan($pasien_id)
    {
        try {
            Log::info('=== GET RIWAYAT KUNJUNGAN START ===', [
                'pasien_id' => $pasien_id,
                'timestamp' => now()
            ]);

            if (!$pasien_id || !is_numeric($pasien_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID pasien tidak valid'
                ], 400);
            }

            $pasien = Pasien::find($pasien_id);
            if (!$pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pasien tidak ditemukan'
                ], 404);
            }

            $riwayatKunjungan = Kunjungan::where('pasien_id', $pasien_id)
                ->orderBy('tanggal_kunjungan', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Kunjungan query result', [
                'total_found' => $riwayatKunjungan->count(),
                'pasien_id' => $pasien_id
            ]);

            $formattedData = $riwayatKunjungan->map(function ($kunjungan) {
                $statusFinal = $this->calculateFinalStatus($kunjungan);

                $data = [
                    'id' => $kunjungan->id,
                    'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                    'status' => $kunjungan->status ?? 'Pending',
                    'status_final' => $statusFinal,
                    'created_at' => $kunjungan->created_at,
                    'updated_at' => $kunjungan->updated_at,
                ];

                if (isset($kunjungan->no_antrian)) {
                    $data['no_antrian'] = $kunjungan->no_antrian;
                }
                if (isset($kunjungan->keluhan_awal)) {
                    $data['keluhan_awal'] = $kunjungan->keluhan_awal;
                }

                // Ambil data poli
                try {
                    if ($kunjungan->poli) {
                        $data['poli'] = [
                            'id' => $kunjungan->poli->id,
                            'nama_poli' => $kunjungan->poli->nama_poli,
                        ];
                    }
                } catch (\Exception $e) {
                    $data['poli'] = null;
                    Log::warning('Poli relation error', ['error' => $e->getMessage()]);
                }

                // Ambil data dokter
                try {
                    if (method_exists($kunjungan, 'dokter') && $kunjungan->dokter) {
                        $data['dokter'] = [
                            'id' => $kunjungan->dokter->id,
                            'nama_dokter' => $kunjungan->dokter->nama_dokter,
                            'foto_dokter' => $kunjungan->dokter->foto_dokter ?? null,
                            'spesialisasi' => $kunjungan->dokter->spesialisasi ?? 'Umum',
                            'no_hp' => $kunjungan->dokter->no_hp ?? null,
                            'pengalaman' => $kunjungan->dokter->pengalaman ?? null,
                        ];
                    }
                } catch (\Exception $e) {
                    $data['dokter'] = null;
                    Log::warning('Dokter relation error', ['error' => $e->getMessage()]);
                }

                // Ambil data EMR
                try {
                    $emr = EMR::where('kunjungan_id', $kunjungan->id)->first();
                    if ($emr) {
                        $data['emr'] = [
                            'id' => $emr->id,
                            'diagnosis' => $emr->diagnosis ?? null,
                            'keluhan_utama' => $emr->keluhan_utama ?? null,
                            'riwayat_penyakit_sekarang' => $emr->riwayat_penyakit_sekarang ?? null,
                            'riwayat_penyakit_dahulu' => $emr->riwayat_penyakit_dahulu ?? null,
                            'riwayat_penyakit_keluarga' => $emr->riwayat_penyakit_keluarga ?? null,
                            'tanggal_pemeriksaan' => $emr->tanggal_pemeriksaan ?? $emr->created_at,
                            'created_at' => $emr->created_at,
                            'tanda_vital' => [
                                'tekanan_darah' => $emr->tekanan_darah ?? null,
                                'suhu_tubuh' => $emr->suhu_tubuh ?? null,
                                'nadi' => $emr->nadi ?? null,
                                'pernapasan' => $emr->pernapasan ?? null,
                                'saturasi_oksigen' => $emr->saturasi_oksigen ?? null,
                            ],
                        ];
                    } else {
                        $data['emr'] = null;
                    }
                } catch (\Exception $e) {
                    $data['emr'] = null;
                    Log::warning('EMR relation error', ['error' => $e->getMessage()]);
                }

                // FIXED: Ambil data layanan dengan detail lengkap
                try {
                    $layanan = DB::table('kunjungan_layanan')
                        ->join('layanan', 'kunjungan_layanan.layanan_id', '=', 'layanan.id')
                        ->where('kunjungan_layanan.kunjungan_id', $kunjungan->id)
                        ->select(
                            'kunjungan_layanan.id',
                            'layanan.nama_layanan',
                            'layanan.harga_layanan',
                            'kunjungan_layanan.jumlah',
                            DB::raw('(layanan.harga_layanan * kunjungan_layanan.jumlah) as subtotal')
                        )
                        ->get();

                    $data['layanan'] = $layanan->map(function ($l) {
                        return [
                            'id' => $l->id,
                            'nama_layanan' => $l->nama_layanan,
                            'harga_layanan' => (float) $l->harga_layanan,
                            'jumlah' => (int) $l->jumlah,
                            'subtotal' => (float) $l->subtotal,
                        ];
                    })->toArray();
                } catch (\Exception $e) {
                    $data['layanan'] = [];
                    Log::warning('Layanan relation error', ['error' => $e->getMessage()]);
                }

                // FIXED: Ambil data resep obat dengan harga yang benar
                try {
                    $resep = DB::table('resep_obat')
                        ->join('obat', 'resep_obat.obat_id', '=', 'obat.id')
                        ->join('resep', 'resep_obat.resep_id', '=', 'resep.id')
                        ->where('resep.kunjungan_id', $kunjungan->id)
                        ->select(
                            'resep_obat.id',
                            'obat.nama_obat',
                            'resep_obat.dosis',
                            'resep_obat.jumlah',
                            'obat.total_harga as harga_per_item',
                            DB::raw('(obat.total_harga * resep_obat.jumlah) as subtotal'),
                            'resep_obat.keterangan',
                            'resep_obat.status'
                        )
                        ->get();

                    $data['resep_obat'] = $resep->map(function ($r) {
                        return [
                            'id' => $r->id,
                            'nama_obat' => $r->nama_obat,
                            'dosis' => $r->dosis,
                            'jumlah' => (int) $r->jumlah,
                            'harga_per_item' => (float) $r->harga_per_item,
                            'subtotal' => (float) $r->subtotal,
                            'keterangan' => $r->keterangan ?? null,
                            'status' => $r->status ?? 'Belum Diambil',
                        ];
                    })->toArray();
                } catch (\Exception $e) {
                    $data['resep_obat'] = [];
                    Log::warning('Resep obat relation error', ['error' => $e->getMessage()]);
                }

                // FIXED: Ambil data pembayaran dengan detail yang benar
                try {
                    $emr = EMR::where('kunjungan_id', $kunjungan->id)->first();
                    if ($emr) {
                        $pembayaran = Pembayaran::with('metodePembayaran')->where('emr_id', $emr->id)->first();
                        if ($pembayaran) {
                            // Hitung total layanan dan obat dari data yang sudah diambil
                            $totalLayanan = collect($data['layanan'])->sum('subtotal');
                            $totalObat = collect($data['resep_obat'])->sum('subtotal');

                            $data['pembayaran'] = [
                                'id' => $pembayaran->id,
                                'biaya_konsultasi' => $totalLayanan > 0 ? $totalLayanan : 150000,
                                'total_obat' => $totalObat,
                                'total_tagihan' => $pembayaran->total_tagihan ?? ($totalLayanan + $totalObat),
                                'total_biaya' => $pembayaran->total_biaya ?? ($totalLayanan + $totalObat),
                                'status' => $pembayaran->status ?? 'Belum Bayar',
                                'status_pembayaran' => $pembayaran->status ?? 'Belum Bayar',
                                'kode_transaksi' => $pembayaran->kode_transaksi ?? null,
                                'tanggal_pembayaran' => $pembayaran->tanggal_pembayaran ?? $pembayaran->created_at,
                                'uang_yang_diterima' => $pembayaran->uang_yang_diterima ?? null,
                                'kembalian' => $pembayaran->kembalian ?? null,
                            ];

                            try {
                                if ($pembayaran->metodePembayaran) {
                                    $data['pembayaran']['metode_pembayaran'] = $pembayaran->metodePembayaran->nama_metode;
                                } elseif (isset($pembayaran->metode_pembayaran)) {
                                    $data['pembayaran']['metode_pembayaran'] = $pembayaran->metode_pembayaran;
                                } else {
                                    $data['pembayaran']['metode_pembayaran'] = null;
                                }
                            } catch (\Exception $e) {
                                $data['pembayaran']['metode_pembayaran'] = null;
                            }
                        } else {
                            $data['pembayaran'] = null;
                        }
                    } else {
                        $data['pembayaran'] = null;
                    }
                } catch (\Exception $e) {
                    $data['pembayaran'] = null;
                    Log::warning('Pembayaran relation error', ['error' => $e->getMessage()]);
                }

                Log::info('Kunjungan status mapping', [
                    'kunjungan_id' => $kunjungan->id,
                    'original_status' => $kunjungan->status,
                    'final_status' => $statusFinal,
                    'has_pembayaran' => $data['pembayaran'] !== null,
                    'pembayaran_status' => $data['pembayaran']['status'] ?? 'N/A'
                ]);

                return $data;
            });

            $pasienInfo = [
                'id' => $pasien->id,
                'nama_pasien' => $pasien->nama_pasien,
                'alamat' => $pasien->alamat ?? null,
                'tanggal_lahir' => $pasien->tanggal_lahir ?? null,
                'jenis_kelamin' => $pasien->jenis_kelamin ?? null,
                'no_telepon' => $pasien->no_telepon ?? null,
                'email' => $pasien->email ?? null,
            ];

            Log::info('=== GET RIWAYAT KUNJUNGAN SUCCESS ===', [
                'total_kunjungan' => $formattedData->count(),
                'pasien_id' => $pasien_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Riwayat kunjungan berhasil diambil',
                'data' => $formattedData,
                'pasien_info' => $pasienInfo,
                'total_kunjungan' => $formattedData->count(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('=== GET RIWAYAT KUNJUNGAN ERROR ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'pasien_id' => $pasien_id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }



    public function getDataDokter()
    {
        try {
            $login = Auth::user()->id;

            $dataDokter = Dokter::with(['user', 'poli'])
                ->where('user_id', $login)
                ->get();

            return response()->json([
                'success' => true,
                'status' => 200,
                'Data Dokter' => $dataDokter,
                'message' => 'Berhasil Mengambil Data Dokter',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error getting data dokter: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dokter: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function loginDokter(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $user = User::where('username', $request->username)->first();

            // Username salah
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Username salah',
                ], 401);
            }

            // Password salah
            if (! Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password salah',
                ], 401);
            }

            // Role bukan Dokter
            if ($user->role !== 'Dokter') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun ini bukan akun dokter',
                ], 403);
            }

            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user'       => $user,
                    'token'      => $token,
                    'token_type' => 'Bearer',
                ],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Login dokter error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
            ], 500);
        }
    }

    public function updateDataDokter(Request $request)
    {
        try {
            $login = Auth::user()->id;

            $dataDokter = Dokter::with('user')->where('user_id', $login)->first();

            if (! $dataDokter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dokter tidak ditemukan',
                ], 404);
            }

            $request->validate([
                'nama_dokter' => ['required'],
                'foto_dokter' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
                'deskripsi_dokter' => ['required'],
                'pengalaman' => ['required'],
                'jenis_spesialis_id' => ['required', 'exists:jenis_spesialis,id'],
                'no_hp' => ['required'],
            ]);

            $pathFotoDokter = $dataDokter->foto_dokter;

            if ($request->hasFile('foto_dokter')) {
                $fileFoto = $request->file('foto_dokter');
                $namaFoto = $request->nama_dokter . '_' . time() . '.' . $fileFoto->getClientOriginalExtension();
                $pathFotoDokter = $fileFoto->storeAs('Foto-Dokter', $namaFoto, 'public');
            }

            $dataDokter->update([
                'user_id' => $login,
                'nama_dokter' => $request->nama_dokter,
                'foto_dokter' => $pathFotoDokter,
                'deskripsi_dokter' => $request->deskripsi_dokter,
                'pengalaman' => $request->pengalaman,
                'jenis_spesialis_id' => $request->jenis_spesialis_id,
                'no_hp' => $request->no_hp,
            ]);

            return response()->json([
                'success' => true,
                'status' => 200,
                'Data Dokter' => $dataDokter->fresh(),
                'message' => 'Berhasil Mengupdate Data Dokter',
            ]);
        } catch (\Exception $e) {
            Log::error('Update data dokter error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
            ], 500);
        }
    }

    public function getDataKunjunganBerdasarkanIdDokter()
    {
        try {
            $user_id = Auth::user()->id;

            // Ambil data dokter dengan relasi poli
            $dokter = Dokter::with(['user', 'poli'])->where('user_id', $user_id)->firstOrFail();

            if (!$dokter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dokter tidak ditemukan',
                ], 404);
            }

            Log::info('Dokter Info:', [
                'dokter_id' => $dokter->id,
                'nama_dokter' => $dokter->nama_dokter,
                'poli_id' => $dokter->poli_id,
                'nama_poli' => $dokter->poli->nama_poli ?? null,
            ]);

            // FIX: Cari kunjungan berdasarkan poli_id, bukan dokter_id
            $dataKunjungan = Kunjungan::with(['pasien'])
                ->where('poli_id', $dokter->poli_id) // GANTI dari dokter_id ke poli_id
                ->where('status', 'Engaged')
                ->orderBy('tanggal_kunjungan', 'desc')
                ->orderBy('no_antrian', 'asc')
                ->get();

            Log::info('Kunjungan Query Result:', [
                'poli_id_used' => $dokter->poli_id,
                'total_kunjungan' => $dataKunjungan->count(),
                'kunjungan_ids' => $dataKunjungan->pluck('id')->toArray(),
            ]);

            // Debug: Cek semua kunjungan dengan poli_id ini
            $allKunjunganInPoli = Kunjungan::where('poli_id', $dokter->poli_id)->get();
            Log::info('All kunjungan in this poli:', [
                'total' => $allKunjunganInPoli->count(),
                'statuses' => $allKunjunganInPoli->pluck('status')->toArray(),
            ]);

            return response()->json([
                'success' => true,
                'status' => 200,
                'data' => $dataKunjungan,
                'kunjungan_hari_ini' => $dataKunjungan,
                'dokter_info' => [
                    'id' => $dokter->id,
                    'nama_dokter' => $dokter->nama_dokter,
                    'user_id' => $user_id,
                    'poli_id' => $dokter->poli_id,
                    'nama_poli' => $dokter->poli->nama_poli ?? 'Tidak ada',
                ],
                'debug_info' => [
                    'poli_id_used' => $dokter->poli_id,
                    'total_engaged' => $dataKunjungan->count(),
                    'all_in_poli' => $allKunjunganInPoli->count(),
                ],
                'message' => 'Berhasil mengambil data kunjungan dokter',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error getting kunjungan by dokter ID: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kunjungan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getDataSpesialisasiDokter()
    {
        try {
            $spesialis = DB::table('jenis_spesialis')
                ->select('id', 'nama_spesialis')
                ->orderBy('nama_spesialis', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'status' => 200,
                'data' => $spesialis,
                'message' => 'Berhasil Mengambil Data Spesialisasi Dokter',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error getting spesialisasi dokter: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data spesialisasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getDataObat()
    {
        try {
            $dataObat = Obat::all();

            return response()->json([
                'success' => true,
                'status' => 200,
                'Data Obat' => $dataObat,
                'message' => 'Berhasil Memunculkan Data Obat',
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting data obat: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
            ], 500);
        }
    }

    public function saveEMR(Request $request)
    {
        try {
            $request->validate([
                'kunjungan_id' => 'required|exists:kunjungan,id',
                'keluhan_utama' => 'required|string',
                'riwayat_penyakit_keluarga' => 'nullable|string',
                'tekanan_darah' => 'nullable|string|max:10',
                'suhu_tubuh' => 'nullable|numeric|between:30,45',
                'nadi' => 'nullable|integer|between:40,200',
                'pernapasan' => 'nullable|integer|between:10,60',
                'saturasi_oksigen' => 'nullable|integer|between:70,100',
                'diagnosis' => 'required|string',
                'resep' => 'nullable|array',
                'resep.*.obat_id' => 'required_with:resep|exists:obat,id',
                'resep.*.jumlah' => 'required_with:resep|integer|min:1',
                'resep.*.keterangan' => 'required_with:resep|string',
                'layanan' => 'nullable|array',
                'layanan.*.layanan_id' => 'required_with:layanan|exists:layanan,id',
                'layanan.*.jumlah' => 'required_with:layanan|integer|min:1',
                // REMOVED: metode_pembayaran validation - tidak diperlukan lagi
            ]);

            $user_id = Auth::id();
            $dokter = Dokter::where('user_id', $user_id)->firstOrFail();

            $kunjungan = Kunjungan::where('id', $request->kunjungan_id)
                ->where('poli_id', $dokter->poli_id)
                ->with('pasien')
                ->firstOrFail();

            Log::info('SaveEMR validation:', [
                'kunjungan_id' => $request->kunjungan_id,
                'dokter_id' => $dokter->id,
                'pasien_id' => $kunjungan->pasien_id,
                'diagnosis' => $request->diagnosis,
            ]);

            if ($kunjungan->status !== 'Engaged') {
                return response()->json([
                    'success' => false,
                    'message' => 'Kunjungan harus dalam status Engaged untuk dapat membuat EMR',
                ], 400);
            }

            $result = DB::transaction(function () use ($request, $kunjungan, $dokter) {
                $resepId = null;

                // Create resep if medications are provided
                if (!empty($request->resep)) {
                    $resep = Resep::create([
                        'kunjungan_id' => $kunjungan->id,
                    ]);
                    $resepId = $resep->id;

                    foreach ($request->resep as $obatResep) {
                        $obat = Obat::findOrFail($obatResep['obat_id']);

                        if ($obat->jumlah < $obatResep['jumlah']) {
                            throw new \Exception("Stok obat {$obat->nama_obat} tidak mencukupi. Stok tersedia: {$obat->jumlah}");
                        }

                        $resep->obat()->attach($obat->id, [
                            'jumlah' => $obatResep['jumlah'],
                            'dosis' => $obat->dosis,
                            'keterangan' => $obatResep['keterangan'],
                            'status' => 'Belum Diambil',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // Auto-fill riwayat diagnosis dari EMR sebelumnya
                $riwayatDiagnosisPasien = EMR::whereHas('kunjungan', function ($query) use ($kunjungan) {
                    $query->where('pasien_id', $kunjungan->pasien_id);
                })
                    ->whereNotNull('diagnosis')
                    ->orderBy('created_at', 'desc')
                    ->get(['diagnosis', 'created_at']);

                // Format riwayat diagnosis
                $riwayatDiagnosisFormatted = '';
                if ($riwayatDiagnosisPasien->isNotEmpty()) {
                    $riwayatList = [];
                    foreach ($riwayatDiagnosisPasien as $emrLama) {
                        $tanggal = Carbon::parse($emrLama->created_at)->format('d/m/Y');
                        $riwayatList[] = "- {$emrLama->diagnosis} ({$tanggal})";
                    }
                    $riwayatDiagnosisFormatted = implode("\n", $riwayatList);
                } else {
                    $riwayatDiagnosisFormatted = "Tidak ada riwayat penyakit sebelumnya";
                }

                Log::info('Riwayat diagnosis pasien:', [
                    'pasien_id' => $kunjungan->pasien_id,
                    'jumlah_riwayat' => $riwayatDiagnosisPasien->count(),
                    'riwayat_formatted' => $riwayatDiagnosisFormatted,
                ]);

                // Create EMR record dengan riwayat diagnosis otomatis
                $emr = EMR::create([
                    'kunjungan_id' => $kunjungan->id,
                    'resep_id' => $resepId,
                    'keluhan_utama' => $request->keluhan_utama,
                    'riwayat_penyakit_dahulu' => $riwayatDiagnosisFormatted,
                    'riwayat_penyakit_keluarga' => $request->riwayat_penyakit_keluarga,
                    'tekanan_darah' => $request->tekanan_darah,
                    'suhu_tubuh' => $request->suhu_tubuh,
                    'nadi' => $request->nadi,
                    'pernapasan' => $request->pernapasan,
                    'saturasi_oksigen' => $request->saturasi_oksigen,
                    'diagnosis' => $request->diagnosis,
                ]);

                Log::info('EMR created with auto-filled riwayat:', [
                    'emr_id' => $emr->id,
                    'diagnosis_baru' => $request->diagnosis,
                    'riwayat_count' => $riwayatDiagnosisPasien->count(),
                ]);

                // Handle layanan
                if (!empty($request->layanan)) {
                    foreach ($request->layanan as $layananData) {
                        $layanan = \App\Models\Layanan::findOrFail($layananData['layanan_id']);

                        if ($layanan->poli_id !== $kunjungan->poli_id) {
                            throw new \Exception("Layanan {$layanan->nama_layanan} tidak tersedia untuk poli ini");
                        }

                        \App\Models\KunjunganLayanan::create([
                            'kunjungan_id' => $kunjungan->id,
                            'layanan_id' => $layanan->id,
                            'jumlah' => $layananData['jumlah'],
                        ]);
                    }
                }

                // Update kunjungan status
                $kunjungan->update(['status' => 'Payment']);

                // Calculate total billing
                $totalTagihan = $this->calculateTotalTagihan($kunjungan, $resepId);

                // FIXED: Create pembayaran record - Cash by default dengan metode_pembayaran_id

                $pembayaran = Pembayaran::create([
                    'emr_id' => $emr->id,
                    'total_tagihan' => $totalTagihan,
                    'uang_yang_diterima' => 0,
                    'kembalian' => 0,
                    'kode_transaksi' => strtoupper(uniqid('TRX_')),
                    'metode_pembayaran_id' => null, // SET NULL - diisi kasir nanti
                    'tanggal_pembayaran' => null,
                    'status' => 'Belum Bayar',
                    'catatan' => 'Menunggu pembayaran di kasir',
                ]);

                return [
                    'emr' => $emr,
                    'resep' => $resep ?? null,
                    'kunjungan' => $kunjungan->fresh(),
                    'pembayaran' => $pembayaran,
                    'riwayat_count' => $riwayatDiagnosisPasien->count(),
                    'billing_info' => [
                        'total_tagihan' => $totalTagihan,
                        'layanan_count' => count($request->layanan ?? []),
                        'resep_count' => count($request->resep ?? []),
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'EMR berhasil disimpan. Pasien dapat melakukan pembayaran di kasir.',
                'data' => [
                    'emr' => $result['emr'],
                    'resep' => $result['resep'],
                    'kunjungan' => $result['kunjungan'],
                    'pembayaran' => $result['pembayaran'],
                    'billing_info' => $result['billing_info'],
                    'riwayat_diagnosis_count' => $result['riwayat_count'],
                ],
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('EMR validation error: ', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error saving EMR: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan EMR: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function getRiwayatDiagnosisPasien($pasienId)
    {
        try {
            Log::info('Getting riwayat diagnosis for pasien_id: ' . $pasienId);

            $pasien = Pasien::find($pasienId);
            if (!$pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pasien tidak ditemukan',
                ], 404);
            }

            // Ambil semua diagnosis dari EMR pasien ini
            $riwayatDiagnosisPasien = EMR::whereHas('kunjungan', function ($query) use ($pasienId) {
                $query->where('pasien_id', $pasienId);
            })
                ->whereNotNull('diagnosis')
                ->orderBy('created_at', 'desc')
                ->get(['diagnosis', 'created_at']);

            // Format riwayat diagnosis
            $riwayatFormatted = '';
            if ($riwayatDiagnosisPasien->isNotEmpty()) {
                $riwayatList = [];
                foreach ($riwayatDiagnosisPasien as $emr) {
                    $tanggal = \Carbon\Carbon::parse($emr->created_at)->format('d/m/Y');
                    $riwayatList[] = "- {$emr->diagnosis} ({$tanggal})";
                }
                $riwayatFormatted = implode("\n", $riwayatList);
            } else {
                $riwayatFormatted = "Tidak ada riwayat penyakit sebelumnya";
            }

            Log::info('Riwayat diagnosis formatted:', [
                'pasien_id' => $pasienId,
                'count' => $riwayatDiagnosisPasien->count(),
                'preview' => substr($riwayatFormatted, 0, 100),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Riwayat diagnosis berhasil diambil',
                'riwayat' => $riwayatFormatted,
                'count' => $riwayatDiagnosisPasien->count(),
                'data' => $riwayatDiagnosisPasien,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting riwayat diagnosis: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat diagnosis: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function calculateTotalTagihan($kunjungan, $resepId = null)
    {
        try {
            $totalTagihan = 0;

            // Calculate layanan costs from kunjungan_layanan pivot table
            $kunjunganLayanan = \App\Models\KunjunganLayanan::with('layanan')
                ->where('kunjungan_id', $kunjungan->id)
                ->get();

            foreach ($kunjunganLayanan as $kl) {
                if ($kl->layanan) { // Add null check
                    $subtotal = (float)$kl->layanan->harga_layanan * (int)$kl->jumlah;
                    $totalTagihan += $subtotal;

                    Log::info('Layanan billing calculation:', [
                        'layanan' => $kl->layanan->nama_layanan,
                        'harga_satuan' => $kl->layanan->harga_layanan,
                        'jumlah' => $kl->jumlah,
                        'subtotal' => $subtotal,
                    ]);
                }
            }

            // If no layanan selected, use default consultation fee
            if ($kunjunganLayanan->isEmpty()) {
                $defaultLayanan = \App\Models\Layanan::where('poli_id', $kunjungan->poli_id)->first();
                $biayaKonsultasi = $defaultLayanan ? (float)$defaultLayanan->harga_layanan : 150000.00;
                $totalTagihan += $biayaKonsultasi;

                Log::info('Using default consultation fee:', [
                    'poli_id' => $kunjungan->poli_id,
                    'biaya_konsultasi' => $biayaKonsultasi,
                ]);
            }

            // Add medication costs if resep exists
            if ($resepId) {
                $resep = Resep::with('obat')->find($resepId);
                if ($resep && $resep->obat) {
                    foreach ($resep->obat as $obat) {
                        $jumlah = (int)($obat->pivot->jumlah ?? 1);
                        $hargaObat = (float)($obat->total_harga ?? 0);
                        $subtotalObat = $hargaObat * $jumlah;
                        $totalTagihan += $subtotalObat;

                        Log::info('Medication billing calculation:', [
                            'obat' => $obat->nama_obat,
                            'harga_satuan' => $hargaObat,
                            'jumlah' => $jumlah,
                            'subtotal' => $subtotalObat,
                        ]);
                    }
                }
            }

            // Ensure minimum value
            if ($totalTagihan <= 0) {
                $totalTagihan = 150000.00;
                Log::warning('Total tagihan was 0 or negative, using default consultation fee');
            }

            Log::info('Total billing calculated:', [
                'kunjungan_id' => $kunjungan->id,
                'total_tagihan' => $totalTagihan,
            ]);

            return round($totalTagihan, 2);
        } catch (\Exception $e) {
            Log::error('Error calculating total tagihan: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            // Return default consultation fee as fallback
            return 150000.00;
        }
    }

    public function getLayananByPoli($poli_id)
    {
        try {
            Log::info('Getting layanan for poli_id: ' . $poli_id);

            // Validate poli exists
            $poli = Poli::find($poli_id);
            if (!$poli) {
                return response()->json([
                    'success' => false,
                    'message' => 'Poli tidak ditemukan',
                ], 404);
            }

            // Get layanan for this poli
            $layanan = \App\Models\Layanan::where('poli_id', $poli_id)
                ->select('id', 'nama_layanan', 'harga_layanan')
                ->get();

            if ($layanan->isEmpty()) {
                Log::info('No layanan found for poli_id: ' . $poli_id);

                // Return default consultation service
                $defaultLayanan = [
                    [
                        'id' => null,
                        'nama_layanan' => 'Konsultasi ' . $poli->nama_poli,
                        'harga_layanan' => '150000.00',
                        'is_default' => true
                    ]
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'Menggunakan layanan default',
                    'layanan' => $defaultLayanan,
                    'poli_info' => [
                        'id' => $poli->id,
                        'nama_poli' => $poli->nama_poli
                    ]
                ]);
            }

            Log::info('Found ' . $layanan->count() . ' layanan for poli_id: ' . $poli_id);

            return response()->json([
                'success' => true,
                'message' => 'Layanan berhasil diambil',
                'layanan' => $layanan->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'nama_layanan' => $item->nama_layanan,
                        'harga_layanan' => number_format($item->harga_layanan, 2, '.', ''),
                        'is_default' => false
                    ];
                }),
                'poli_info' => [
                    'id' => $poli->id,
                    'nama_poli' => $poli->nama_poli
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting layanan by poli: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil layanan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getRiwayatPasienDiperiksa()
    {
        try {
            $userId = Auth::user()->id;

            $dokter = Dokter::with('user', 'poli')->where('user_id', $userId)->first();

            if (!$dokter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dokter tidak ditemukan'
                ], 404);
            }

            // ✅ Status SUDAH BENAR: "Succeed" sesuai migration
            $riwayatPasien = Kunjungan::with([
                'pasien',
                'emr',
                'emr.resep.obat',
                'poli'
            ])
                ->where('poli_id', $dokter->poli_id)
                ->whereIn('status', ['Succeed', 'Canceled']) // ✅ SUDAH BENAR
                ->orderBy('updated_at', 'desc')
                ->orderBy('tanggal_kunjungan', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'status' => 200,
                'data' => $riwayatPasien,
                'total_pasien' => $riwayatPasien->count(),
                'dokter_info' => [
                    'id' => $dokter->id,
                    'nama_dokter' => $dokter->nama_dokter,
                    'user_id' => $userId,
                    'poli_id' => $dokter->poli_id,
                    'poli_nama' => $dokter->poli->nama_poli ?? 'Tidak ada',
                ],
                'message' => 'Berhasil mengambil riwayat pasien yang diperiksa',
            ], 200);
        } catch (\Exception $e) {
            Log::error('ERROR in getRiwayatPasienDiperiksa: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat pasien: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getDetailRiwayatPasien($kunjunganId)
    {
        try {
            $user_id = Auth::user()->id;
            Log::info('🔍 Getting detail riwayat for kunjungan_id: ' . $kunjunganId . ' by user_id: ' . $user_id);

            $dokter = Dokter::with(['poli'])->where('user_id', $user_id)->first();

            if (!$dokter) {
                Log::warning('❌ Dokter tidak ditemukan untuk user_id: ' . $user_id);
                return response()->json([
                    'success' => false,
                    'message' => 'Data dokter tidak ditemukan',
                ], 404);
            }

            Log::info('✅ Dokter found:', [
                'dokter_id' => $dokter->id,
                'poli_id' => $dokter->poli_id,
                'poli_nama' => $dokter->poli->nama_poli ?? 'N/A',
            ]);

            // FIXED: Gunakan poli_id untuk konsistensi
            $detailRiwayat = Kunjungan::with([
                'pasien',
                'poli', // Tambahkan relasi poli
                'emr',
                'emr.resep',
                'emr.resep.obat' => function ($query) {
                    $query->select('obat.id', 'obat.nama_obat', 'obat.dosis', 'obat.total_harga')
                        ->withPivot('jumlah', 'dosis', 'keterangan', 'status');
                },
                // Tambahkan relasi layanan jika diperlukan
                'layanan'
            ])
                ->where('id', $kunjunganId)
                ->where('poli_id', $dokter->poli_id) // FIXED: Gunakan poli_id bukan dokter_id
                ->whereIn('status', ['Succeed', 'Canceled'])
                ->first();

            if (!$detailRiwayat) {
                Log::warning('❌ Detail riwayat tidak ditemukan:', [
                    'kunjungan_id' => $kunjunganId,
                    'poli_id' => $dokter->poli_id,
                    'dokter_id' => $dokter->id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Data riwayat tidak ditemukan atau tidak memiliki akses',
                    'debug_info' => [
                        'kunjungan_id' => $kunjunganId,
                        'poli_id_checked' => $dokter->poli_id,
                        'status_filter' => ['Succeed', 'Canceled'],
                    ],
                ], 404);
            }

            Log::info('✅ Detail riwayat found:', [
                'kunjungan_id' => $detailRiwayat->id,
                'pasien_nama' => $detailRiwayat->pasien->nama_pasien ?? 'N/A',
                'status' => $detailRiwayat->status,
                'has_emr' => $detailRiwayat->emr !== null,
                'has_resep' => $detailRiwayat->emr && $detailRiwayat->emr->resep !== null,
                'resep_obat_count' => $detailRiwayat->emr && $detailRiwayat->emr->resep && $detailRiwayat->emr->resep->obat ? $detailRiwayat->emr->resep->obat->count() : 0,
            ]);

            return response()->json([
                'success' => true,
                'status' => 200,
                'data' => $detailRiwayat,
                'dokter_info' => [
                    'id' => $dokter->id,
                    'nama_dokter' => $dokter->nama_dokter,
                    'poli_id' => $dokter->poli_id,
                    'poli_nama' => $dokter->poli->nama_poli ?? 'Tidak ada',
                ],
                'debug_info' => [
                    'query_used' => "poli_id = {$dokter->poli_id}",
                    'kunjungan_id' => $kunjunganId,
                    'has_access' => true,
                ],
                'message' => 'Berhasil mengambil detail riwayat pasien',
            ], 200);
        } catch (\Exception $e) {
            Log::error('❌ CRITICAL ERROR in getDetailRiwayatPasien: ' . $e->getMessage());
            Log::error('📍 File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            Log::error('🔍 Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail riwayat: ' . $e->getMessage(),
                'debug_info' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'kunjungan_id' => $kunjunganId,
                    'user_id' => Auth::id() ?? 'not_authenticated',
                ],
            ], 500);
        }
    }

    public function sendForgotPasswordOTP(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ], [
                'email.required' => 'Email tidak boleh kosong.',
                'email.email'    => 'Format email tidak valid.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $email = $request->email;
            $user  = User::where('email', $email)->first();

            // Email tidak ditemukan
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email tidak ditemukan dalam sistem',
                ], 404);
            }

            $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

            $cacheKey = 'forgot_password_otp_' . $email;
            Cache::put($cacheKey, $otp, now()->addMinutes(5));

            try {
                Mail::send('emails.otp_notification', [
                    'otp' => $otp,
                    'type' => 'Reset Password',
                    'expiration_minutes' => 5,
                ], function ($message) use ($email) {
                    $message->to($email)
                        ->subject('Kode Verifikasi Reset Password - Royal Clinic');
                });

                // Jangan log OTP di production
                Log::info("Forgot password OTP sent to: $email");

                return response()->json([
                    'success' => true,
                    'message' => 'Kode OTP telah dikirim ke email Anda',
                    'data' => [
                        'email' => $email,
                        'expires_in' => 5,
                    ],
                ], 200);
            } catch (\Throwable $e) {
                Log::error('Failed to send forgot password email: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim email. Silakan coba lagi.',
                ], 500);
            }
        } catch (\Throwable $e) {
            Log::error('Error in sendForgotPasswordOTP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
            ], 500);
        }
    }


    public function resetPasswordWithOTP(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email'         => 'required|email',
                'otp'           => 'required|string|size:6',
                'new_password'  => 'required|string|min:6|confirmed',
            ], [
                'email.required'        => 'Email tidak boleh kosong.',
                'email.email'           => 'Format email tidak valid.',
                'otp.required'          => 'Kode OTP wajib diisi.',
                'otp.size'              => 'Kode OTP harus 6 digit.',
                'new_password.required' => 'Password baru wajib diisi.',
                'new_password.min'      => 'Password baru minimal 6 karakter.',
                'new_password.confirmed' => 'Konfirmasi password tidak sama.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $email       = $request->email;
            $otp         = $request->otp;
            $newPassword = $request->new_password;

            $user = User::where('email', $email)->first();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email tidak ditemukan dalam sistem',
                ], 404);
            }

            $cacheKey = 'forgot_password_otp_' . $email;
            $storedOTP = Cache::get($cacheKey);

            if (! $storedOTP) {
                // OTP kedaluwarsa → 410 Gone (lebih tepat daripada 400)
                return response()->json([
                    'success' => false,
                    'message' => 'Kode OTP sudah kedaluwarsa. Silakan minta kode baru.',
                ], 410);
            }

            if ($storedOTP !== $otp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode OTP tidak valid.',
                ], 400);
            }

            $user->update([
                'password' => Hash::make($newPassword),
            ]);

            Cache::forget($cacheKey);
            $user->tokens()->delete();

            Log::info("Password reset successful for email: $email");

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil direset. Silakan login dengan password baru.',
                'data' => [
                    'email' => $email,
                    'reset_at' => now()->toISOString(),
                ],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Error in resetPasswordWithOTP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
            ], 500);
        }
    }


    public function sendForgotUsernameOTP(Request $request)
    {
        try {
            // VALIDASI FORMAT SAJA
            $validator = Validator::make($request->all(), [
                'email' => 'required|email', // <- BUKAN "requirad"
            ], [
                'email.required' => 'Email tidak boleh kosong.',
                'email.email'    => 'Format email tidak valid.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $email = $request->email;

            // CEK EMAIL DI DB
            $user = User::where('email', $email)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email tidak ditemukan dalam sistem',
                ], 404);
            }

            // GENERATE & SIMPAN OTP (5 menit)
            $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $cacheKey = 'forgot_username_otp_' . $email;
            Cache::put($cacheKey, $otp, now()->addMinutes(5));

            // KIRIM EMAIL OTP
            try {
                Mail::send('emails.otp_notification', [
                    'otp' => $otp,
                    'type' => 'Forgot Username',
                    'expiration_minutes' => 5,
                ], function ($message) use ($email) {
                    $message->to($email)->subject('Kode Verifikasi - Lupa Username (Royal Clinic)');
                });

                Log::info("Forgot-username OTP sent to: {$email}, OTP: {$otp}");

                return response()->json([
                    'success' => true,
                    'message' => 'Kode OTP telah dikirim ke email Anda',
                    'data' => [
                        'email' => $email,
                        'expires_in' => 5,
                    ],
                ], 200);
            } catch (\Throwable $e) {
                Log::error('Failed to send forgot-username OTP email: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim email. Silakan coba lagi.',
                ], 500);
            }
        } catch (\Throwable $e) {
            Log::error('Error in sendForgotUsernameOTP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
            ], 500);
        }
    }


    public function verifyOrChangeUsernameWithOTP(Request $request)
    {
        try {
            // new_username optional: hanya diverifikasi kalau diisi
            $validator = Validator::make($request->all(), [
                'email'        => 'required|email',
                'otp'          => 'required|string|size:6',
                'new_username' => 'sometimes|nullable|string|min:3|max:50|regex:/^[A-Za-z0-9_.-]+$/|unique:user,username',
            ], [
                'email.required'         => 'Email tidak boleh kosong.',
                'email.email'            => 'Format email tidak valid.',
                'otp.required'           => 'Kode OTP wajib diisi.',
                'otp.size'               => 'Kode OTP harus 6 digit.',
                'new_username.min'       => 'Username minimal 3 karakter.',
                'new_username.regex'     => 'Username hanya boleh huruf, angka, titik, garis bawah, dan minus.',
                'new_username.unique'    => 'Username sudah dipakai.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $email = trim(strtolower($request->email));
            $otp   = $request->otp;
            $new   = $request->new_username;

            $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email tidak ditemukan dalam sistem',
                ], 404);
            }

            $cacheKey = 'forgot_username_otp_' . $email;
            $storedOTP = Cache::get($cacheKey);

            if (! $storedOTP) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode OTP sudah kedaluwarsa. Silakan minta kode baru.',
                ], 410);
            }

            if ($storedOTP !== $otp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode OTP tidak valid.',
                ], 400);
            }

            // Jika tidak ingin ganti username → cukup kembalikan username saat ini
            if ($new === null || $new === '') {
                return response()->json([
                    'success' => true,
                    'message' => 'Verifikasi OTP berhasil',
                    'data' => [
                        'email'    => $email,
                        'username' => $user->username,
                        'verified_at' => now()->toISOString(),
                    ],
                ], 200);
            }

            // Jika ingin ganti username
            // (opsional) double check unik (selain unique rule)
            if (User::where('username', $new)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Username sudah dipakai.',
                ], 409);
            }

            $user->update(['username' => $new]);

            Cache::forget($cacheKey);
            $user->tokens()->delete(); // optional: force re-login

            return response()->json([
                'success' => true,
                'message' => 'Username berhasil diganti.',
                'data' => [
                    'email'        => $email,
                    'new_username' => $new,
                    'updated_at'   => now()->toISOString(),
                ],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Error in verifyOrChangeUsernameWithOTP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
            ], 500);
        }
    }



    /**
     * Get pembayaran detail by kunjungan ID
     */
    public function getPembayaranDetail($kunjunganId)
    {
        try {
            Log::info('Getting pembayaran detail for kunjungan_id: ' . $kunjunganId);

            $kunjungan = Kunjungan::with([
                'pasien' => function ($query) {
                    $query->select('id', 'nama_pasien', 'alamat', 'tanggal_lahir', 'jenis_kelamin');
                },
                'poli' => function ($query) {
                    $query->select('id', 'nama_poli');
                }
            ])->find($kunjunganId);

            if (!$kunjungan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kunjungan tidak ditemukan'
                ], 404);
            }

            // Cari EMR
            $emr = EMR::where('kunjungan_id', $kunjunganId)->first();

            if (!$emr) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data pembayaran belum tersedia - EMR belum dibuat',
                    'data' => [
                        'id' => null,
                        'total_tagihan' => 0,
                        'status_pembayaran' => 'Belum Bayar',
                        'kode_transaksi' => null,
                        'tanggal_pembayaran' => null,
                        'metode_pembayaran_nama' => null,
                        'bukti_pembayaran' => null, // TAMBAHKAN
                        'pasien' => [
                            'nama_pasien' => $kunjungan->pasien->nama_pasien ?? 'Unknown'
                        ],
                        'poli' => [
                            'nama_poli' => $kunjungan->poli->nama_poli ?? 'Unknown'
                        ],
                        'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                        'no_antrian' => $kunjungan->no_antrian,
                        'keluhan_awal' => $kunjungan->keluhan_awal,
                        'diagnosis' => 'Menunggu pemeriksaan dokter',
                        'resep' => [],
                        'layanan' => [],
                        'is_emr_missing' => true
                    ]
                ]);
            }

            // Jika EMR ada, cari pembayaran
            $pembayaran = Pembayaran::with('metodePembayaran')->where('emr_id', $emr->id)->first();

            if (!$pembayaran) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pembayaran belum dibuat untuk kunjungan ini',
                    'data' => [
                        'id' => null,
                        'total_tagihan' => 0,
                        'status_pembayaran' => 'Belum Bayar',
                        'kode_transaksi' => null,
                        'tanggal_pembayaran' => null,
                        'metode_pembayaran_nama' => null,
                        'bukti_pembayaran' => null, // TAMBAHKAN
                        'pasien' => [
                            'nama_pasien' => $kunjungan->pasien->nama_pasien ?? 'Unknown'
                        ],
                        'poli' => [
                            'nama_poli' => $kunjungan->poli->nama_poli ?? 'Unknown'
                        ],
                        'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                        'no_antrian' => $kunjungan->no_antrian,
                        'keluhan_awal' => $kunjungan->keluhan_awal,
                        'diagnosis' => $emr->diagnosis ?? 'Sedang diproses',
                        'resep' => [],
                        'layanan' => [],
                        'is_payment_missing' => true
                    ]
                ]);
            }

            // Ambil data resep
            $resepList = [];
            if ($emr->resep && $emr->resep->obat) {
                foreach ($emr->resep->obat as $obat) {
                    $resepList[] = [
                        'id' => $obat->id,
                        'jumlah' => $obat->pivot->jumlah ?? 1,
                        'obat' => [
                            'nama_obat' => $obat->nama_obat,
                            'harga_obat' => $obat->total_harga ?? 0
                        ]
                    ];
                }
            }

            // Ambil data layanan dari pivot table
            $layananList = [];
            $kunjunganLayanan = \App\Models\KunjunganLayanan::with('layanan')
                ->where('kunjungan_id', $kunjunganId)
                ->get();

            foreach ($kunjunganLayanan as $kl) {
                if ($kl->layanan) {
                    $layananList[] = [
                        'id' => $kl->layanan->id,
                        'nama_layanan' => $kl->layanan->nama_layanan,
                        'harga_layanan' => $kl->layanan->harga_layanan,
                        'jumlah' => $kl->jumlah ?? 1
                    ];
                }
            }

            $responseData = [
                'id' => $pembayaran->id,
                'emr_id' => $pembayaran->emr_id,
                'total_tagihan' => $pembayaran->total_tagihan,
                'status_pembayaran' => $pembayaran->status,
                'kode_transaksi' => $pembayaran->kode_transaksi,
                'tanggal_pembayaran' => $pembayaran->tanggal_pembayaran,
                'metode_pembayaran_id' => $pembayaran->metode_pembayaran_id,
                'metode_pembayaran_nama' => $pembayaran->metodePembayaran->nama_metode ?? null,
                'bukti_pembayaran' => $pembayaran->bukti_pembayaran, // TAMBAHKAN
                'uang_yang_diterima' => $pembayaran->uang_yang_diterima,
                'kembalian' => $pembayaran->kembalian,
                'catatan' => $pembayaran->catatan,
                'pasien' => [
                    'nama_pasien' => $kunjungan->pasien->nama_pasien ?? 'Unknown'
                ],
                'poli' => [
                    'nama_poli' => $kunjungan->poli->nama_poli ?? 'Unknown'
                ],
                'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                'no_antrian' => $kunjungan->no_antrian,
                'keluhan_awal' => $kunjungan->keluhan_awal,
                'diagnosis' => $emr->diagnosis ?? null,
                'resep' => $resepList,
                'layanan' => $layananList
            ];

            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil diambil',
                'data' => $responseData
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting pembayaran detail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pembayaran by pasien ID
     */
    public function getPembayaranPasien($pasienId)
    {
        try {
            Log::info('Getting pembayaran for pasien_id: ' . $pasienId);

            $kunjungan = Kunjungan::with(['pasien', 'poli'])
                ->where('pasien_id', $pasienId)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$kunjungan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kunjungan tidak ditemukan untuk pasien ini'
                ], 404);
            }

            // ✅ SAFE: Cari EMR, jika tidak ada buat response kosong
            $emr = EMR::where('kunjungan_id', $kunjungan->id)->first();

            if (!$emr) {
                // ✅ Jika EMR tidak ada, return data minimal
                $paymentData = [
                    'id' => null,
                    'total_tagihan' => 0,
                    'status_pembayaran' => 'Belum Bayar',
                    'kode_transaksi' => null,
                    'tanggal_pembayaran' => null,
                    'metode_pembayaran_nama' => null,
                    'pasien' => [
                        'nama_pasien' => $kunjungan->pasien->nama_pasien ?? 'Unknown'
                    ],
                    'poli' => [
                        'nama_poli' => $kunjungan->poli->nama_poli ?? 'Unknown'
                    ],
                    'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                    'no_antrian' => $kunjungan->no_antrian,
                    'diagnosis' => 'Menunggu pemeriksaan dokter',
                    'resep' => [],
                    'layanan' => [],
                    'is_emr_missing' => true
                ];
            } else {
                // ✅ EMR ada, cek pembayaran
                $pembayaran = Pembayaran::with('metodePembayaran')->where('emr_id', $emr->id)->first();

                if (!$pembayaran) {
                    $paymentData = [
                        'id' => null,
                        'total_tagihan' => 0,
                        'status_pembayaran' => 'Belum Bayar',
                        'kode_transaksi' => null,
                        'tanggal_pembayaran' => null,
                        'metode_pembayaran_nama' => null,
                        'pasien' => [
                            'nama_pasien' => $kunjungan->pasien->nama_pasien ?? 'Unknown'
                        ],
                        'poli' => [
                            'nama_poli' => $kunjungan->poli->nama_poli ?? 'Unknown'
                        ],
                        'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                        'no_antrian' => $kunjungan->no_antrian,
                        'diagnosis' => $emr->diagnosis ?? 'Sedang diproses',
                        'resep' => [],
                        'layanan' => [],
                        'is_payment_missing' => true
                    ];
                } else {
                    // ✅ Normal case - ada EMR dan pembayaran
                    $resepList = Resep::with('obat')->where('emr_id', $emr->id)->get();

                    $paymentData = [
                        'id' => $pembayaran->id,
                        'total_tagihan' => $pembayaran->total_tagihan,
                        'status_pembayaran' => $pembayaran->status,
                        'kode_transaksi' => $pembayaran->kode_transaksi,
                        'tanggal_pembayaran' => $pembayaran->tanggal_pembayaran,
                        'metode_pembayaran_nama' => $pembayaran->metodePembayaran->nama_metode ?? null,
                        'pasien' => [
                            'nama_pasien' => $kunjungan->pasien->nama_pasien ?? 'Unknown'
                        ],
                        'poli' => [
                            'nama_poli' => $kunjungan->poli->nama_poli ?? 'Unknown'
                        ],
                        'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                        'no_antrian' => $kunjungan->no_antrian,
                        'diagnosis' => $emr->diagnosis ?? null,
                        'resep' => $resepList->map(function ($resep) {
                            return [
                                'id' => $resep->id,
                                'jumlah' => $resep->jumlah,
                                'obat' => [
                                    'nama_obat' => $resep->obat->nama_obat ?? 'Unknown',
                                    'harga_obat' => $resep->obat->harga_obat ?? 0
                                ]
                            ];
                        })->toArray(),
                        'layanan' => [],
                    ];
                }
            }

            $responseData = [
                'payments' => [$paymentData]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil diambil',
                'data' => $responseData
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting pembayaran by pasien: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    // FIXED: Update method getDetailPembayaran() - ganti metodePembayaranRelation jadi metodePembayaran
    public function getDetailPembayaran($kunjunganId)
    {
        try {
            Log::info('🔍 getDetailPembayaran called for kunjungan_id: ' . $kunjunganId);

            $kunjungan = Kunjungan::with([
                'poli',
                'pasien' => function ($query) {
                    $query->select('id', 'nama_pasien', 'alamat', 'tanggal_lahir', 'jenis_kelamin', 'foto_pasien');
                },
                'emr' => function ($query) {
                    $query->select(
                        'id',
                        'kunjungan_id',
                        'resep_id',
                        'keluhan_utama',
                        'diagnosis',
                        'tekanan_darah',
                        'suhu_tubuh',
                        'nadi',
                        'pernapasan',
                        'saturasi_oksigen'
                    );
                },
                'emr.pembayaran' => function ($query) {
                    $query->select(
                        'id',
                        'emr_id',
                        'total_tagihan',
                        'status',
                        'kode_transaksi',
                        'metode_pembayaran_id', // FIXED: Gunakan metode_pembayaran_id
                        'tanggal_pembayaran',
                        'uang_yang_diterima',
                        'kembalian'
                    );
                },
                'emr.pembayaran.metodePembayaran', // FIXED: Load relasi metode pembayaran
                'emr.resep.obat' => function ($query) {
                    $query->select('obat.id', 'obat.nama_obat', 'obat.dosis', 'obat.total_harga')
                        ->withPivot('jumlah', 'dosis', 'keterangan', 'status');
                },
                'layanan',
            ])
                ->where('id', $kunjunganId)
                ->where('status', 'Payment')
                ->first();

            if (!$kunjungan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kunjungan tidak ditemukan atau sudah dibayar',
                ], 404);
            }

            if (!$kunjungan->emr || !$kunjungan->emr->pembayaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pembayaran tidak tersedia',
                ], 404);
            }

            if ($kunjungan->emr->pembayaran->status !== 'Belum Bayar') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran sudah selesai',
                ], 400);
            }

            $pembayaran = $kunjungan->emr->pembayaran;

            // Build response data untuk detail individual
            $responseData = [
                'kunjungan_id' => $kunjungan->id,
                'pasien' => [
                    'nama_pasien' => $kunjungan->pasien->nama_pasien ?? 'Tidak ada',
                    'umur' => $this->calculateAge($kunjungan->pasien->tanggal_lahir ?? null),
                    'jenis_kelamin' => $kunjungan->pasien->jenis_kelamin ?? 'Tidak ada',
                    'foto_pasien' => $kunjungan->pasien->foto_pasien,
                ],
                'poli' => [
                    'nama_poli' => $kunjungan->poli->nama_poli ?? 'Umum',
                ],
                'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                'no_antrian' => $kunjungan->no_antrian,
                'diagnosis' => $kunjungan->emr->diagnosis ?? 'Tidak ada diagnosis',

                // FIXED: GUNAKAN METODE_PEMBAYARAN_ID DAN RELASI
                'kode_transaksi' => $pembayaran->kode_transaksi ?? null,
                'metode_pembayaran_id' => $pembayaran->metode_pembayaran_id ?? null,
                'metode_pembayaran_nama' => $pembayaran->metodePembayaran->nama_metode ?? 'Cash',

                'layanan' => [],
                'resep_obat' => [],
                'total_layanan' => 0,
                'total_obat' => 0,
                'total_tagihan' => 0,
                'status_pembayaran' => $pembayaran->status ?? 'Belum Bayar',
                'pembayaran_id' => $pembayaran->id ?? null,
            ];

            // Process layanan (existing code sama seperti di atas...)
            $totalLayanan = 0;
            if ($kunjungan->layanan && $kunjungan->layanan->isNotEmpty()) {
                foreach ($kunjungan->layanan as $layanan) {
                    $jumlah = (int) $layanan->pivot->jumlah;
                    $hargaLayanan = (float) $layanan->harga_layanan;
                    $subtotal = $hargaLayanan * $jumlah;
                    $totalLayanan += $subtotal;

                    $responseData['layanan'][] = [
                        'id' => $layanan->id,
                        'nama_layanan' => $layanan->nama_layanan ?? 'Layanan',
                        'harga_layanan' => $hargaLayanan,
                        'jumlah' => $jumlah,
                        'subtotal' => $subtotal,
                    ];
                }
            }

            // Process resep obat (existing code sama seperti di atas...)
            $totalObat = 0;
            if ($kunjungan->emr && $kunjungan->emr->resep) {
                foreach ($kunjungan->emr->resep->obat as $obat) {
                    $jumlah = $obat->pivot->jumlah ?? 1;
                    $hargaObat = $obat->total_harga ?? 0;
                    $subtotal = $hargaObat * $jumlah;
                    $totalObat += $subtotal;

                    $responseData['resep_obat'][] = [
                        'obat' => [
                            'id' => $obat->id,
                            'nama_obat' => $obat->nama_obat,
                            'harga_obat' => $hargaObat,
                        ],
                        'jumlah' => $jumlah,
                        'dosis' => $obat->pivot->dosis ?? $obat->dosis,
                        'keterangan' => $obat->pivot->keterangan ?? 'Sesuai anjuran dokter',
                        'status' => $obat->pivot->status ?? 'Belum Diambil',
                    ];
                }
            }

            $responseData['total_layanan'] = $totalLayanan;
            $responseData['total_obat'] = $totalObat;
            $responseData['total_tagihan'] = $totalLayanan + $totalObat;

            return response()->json([
                'success' => true,
                'message' => 'Detail pembayaran berhasil diambil',
                'data' => $responseData,
            ], 200);
        } catch (\Exception $e) {
            Log::error('❌ Error getting detail pembayaran: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function notifyPasienFromKunjungan(Kunjungan $kunjungan, string $title, string $body, array $extra = []): void
    {
        try {
            // Ambil user_id dari relasi pasien
            $userId = null;

            if ($kunjungan->relationLoaded('pasien')) {
                $userId = optional($kunjungan->pasien)->user_id;
            } else {
                $pasien = Pasien::find($kunjungan->pasien_id);
                $userId = optional($pasien)->user_id;
            }

            if (!$userId) {
                Log::warning('notifyPasienFromKunjungan: user_id pasien tidak ditemukan. kunjungan_id=' . $kunjungan->id);
                return;
            }

            $payload = array_merge([
                'type'           => 'kunjungan_status',
                'kunjungan_id'   => $kunjungan->id,
                'status'         => $kunjungan->status ?? null,
                'nomor_antrian'  => $kunjungan->no_antrian ?? null,
            ], $extra);

            $this->createNotification($userId, $title, $body, $payload);
        } catch (\Throwable $e) {
            Log::warning('notifyPasienFromKunjungan error: ' . $e->getMessage());
        }
    }


    // Tambahkan method ini ke APIMobileController.php

    // public function getDetailPembayaran($kunjunganId)
    // {
    //     try {
    //         Log::info('🔍 getDetailPembayaran called for kunjungan_id: ' . $kunjunganId);

    //         $kunjungan = Kunjungan::with([
    //             'poli',
    //             'pasien' => function ($query) {
    //                 $query->select('id', 'nama_pasien', 'alamat', 'tanggal_lahir', 'jenis_kelamin', 'foto_pasien');
    //             },
    //             'emr' => function ($query) {
    //                 $query->select(
    //                     'id',
    //                     'kunjungan_id',
    //                     'resep_id',
    //                     'keluhan_utama',
    //                     'diagnosis',
    //                     'tekanan_darah',
    //                     'suhu_tubuh',
    //                     'nadi',
    //                     'pernapasan',
    //                     'saturasi_oksigen'
    //                 );
    //             },
    //             'emr.pembayaran' => function ($query) {
    //                 // TAMBAHKAN relasi untuk kode transaksi dan metode pembayaran
    //                 $query->select(
    //                     'id',
    //                     'emr_id',
    //                     'total_tagihan',
    //                     'status',
    //                     'kode_transaksi',
    //                     'metode_pembayaran',
    //                     'metode_pembayaran_id',
    //                     'tanggal_pembayaran',
    //                     'uang_yang_diterima',
    //                     'kembalian'
    //                 );
    //             },
    //             'emr.pembayaran.metodePembayaranRelation', // TAMBAHKAN relasi ke tabel metode_pembayaran
    //             'emr.resep.obat' => function ($query) {
    //                 $query->select('obat.id', 'obat.nama_obat', 'obat.dosis', 'obat.total_harga')
    //                     ->withPivot('jumlah', 'dosis', 'keterangan', 'status');
    //             },
    //             'layanan',
    //         ])
    //             ->where('id', $kunjunganId)
    //             ->where('status', 'Payment')
    //             ->first();

    //         if (!$kunjungan) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Kunjungan tidak ditemukan atau sudah dibayar',
    //             ], 404);
    //         }

    //         if (!$kunjungan->emr || !$kunjungan->emr->pembayaran) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Data pembayaran tidak tersedia',
    //             ], 404);
    //         }

    //         if ($kunjungan->emr->pembayaran->status !== 'Belum Bayar') {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Pembayaran sudah selesai',
    //             ], 400);
    //         }

    //         $pembayaran = $kunjungan->emr->pembayaran;

    //         // Build response data untuk detail individual
    //         $responseData = [
    //             'kunjungan_id' => $kunjungan->id,
    //             'pasien' => [
    //                 'nama_pasien' => $kunjungan->pasien->nama_pasien ?? 'Tidak ada',
    //                 'umur' => $this->calculateAge($kunjungan->pasien->tanggal_lahir ?? null),
    //                 'jenis_kelamin' => $kunjungan->pasien->jenis_kelamin ?? 'Tidak ada',
    //                 'foto_pasien' => $kunjungan->pasien->foto_pasien,
    //             ],
    //             'poli' => [
    //                 'nama_poli' => $kunjungan->poli->nama_poli ?? 'Umum',
    //             ],
    //             'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
    //             'no_antrian' => $kunjungan->no_antrian,
    //             'diagnosis' => $kunjungan->emr->diagnosis ?? 'Tidak ada diagnosis',

    //             // TAMBAHKAN KODE TRANSAKSI DAN METODE PEMBAYARAN
    //             'kode_transaksi' => $pembayaran->kode_transaksi ?? null,
    //             'metode_pembayaran' => $pembayaran->metode_pembayaran ?? 'Cash',
    //             'metode_pembayaran_id' => $pembayaran->metode_pembayaran_id ?? null,
    //             'metode_pembayaran_nama' => $pembayaran->metodePembayaranRelation->nama_metode ?? $pembayaran->metode_pembayaran ?? 'Cash',

    //             'layanan' => [],
    //             'resep_obat' => [],
    //             'total_layanan' => 0,
    //             'total_obat' => 0,
    //             'total_tagihan' => 0,
    //             'status_pembayaran' => $pembayaran->status ?? 'Belum Bayar',
    //             'pembayaran_id' => $pembayaran->id ?? null,
    //         ];

    //         // Process layanan
    //         $totalLayanan = 0;
    //         if ($kunjungan->layanan && $kunjungan->layanan->isNotEmpty()) {
    //             foreach ($kunjungan->layanan as $layanan) {
    //                 $jumlah = (int) $layanan->pivot->jumlah;
    //                 $hargaLayanan = (float) $layanan->harga_layanan;
    //                 $subtotal = $hargaLayanan * $jumlah;
    //                 $totalLayanan += $subtotal;

    //                 $responseData['layanan'][] = [
    //                     'id' => $layanan->id,
    //                     'nama_layanan' => $layanan->nama_layanan ?? 'Layanan',
    //                     'harga_layanan' => $hargaLayanan,
    //                     'jumlah' => $jumlah,
    //                     'subtotal' => $subtotal,
    //                 ];
    //             }
    //         }

    //         // Process resep obat
    //         $totalObat = 0;
    //         if ($kunjungan->emr && $kunjungan->emr->resep) {
    //             foreach ($kunjungan->emr->resep->obat as $obat) {
    //                 $jumlah = $obat->pivot->jumlah ?? 1;
    //                 $hargaObat = $obat->total_harga ?? 0;
    //                 $subtotal = $hargaObat * $jumlah;
    //                 $totalObat += $subtotal;

    //                 $responseData['resep_obat'][] = [
    //                     'obat' => [
    //                         'id' => $obat->id,
    //                         'nama_obat' => $obat->nama_obat,
    //                         'harga_obat' => $hargaObat,
    //                     ],
    //                     'jumlah' => $jumlah,
    //                     'dosis' => $obat->pivot->dosis ?? $obat->dosis,
    //                     'keterangan' => $obat->pivot->keterangan ?? 'Sesuai anjuran dokter',
    //                     'status' => $obat->pivot->status ?? 'Belum Diambil',
    //                 ];
    //             }
    //         }

    //         $responseData['total_layanan'] = $totalLayanan;
    //         $responseData['total_obat'] = $totalObat;
    //         $responseData['total_tagihan'] = $totalLayanan + $totalObat;

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Detail pembayaran berhasil diambil',
    //             'data' => $responseData,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         Log::error('❌ Error getting detail pembayaran: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal mengambil detail pembayaran: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    private function calculateAge($tanggalLahir)
    {
        if (!$tanggalLahir) {
            return 0;
        }

        try {
            $birthDate = Carbon::parse($tanggalLahir);
            $today = Carbon::now();
            return $today->diffInYears($birthDate);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function updateStatusObat(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|max:50',
        ]);

        // Ganti Resep ke model yang kamu pakai
        $resep = Resep::findOrFail($id);
        $resep->status = $validated['status'];
        $resep->save();

        // Cari kunjungan untuk tahu pasiennya
        $kunjungan = null;
        if ($resep->relationLoaded('kunjungan')) {
            $kunjungan = $resep->kunjungan;
        } elseif (!empty($resep->kunjungan_id)) {
            $kunjungan = Kunjungan::find($resep->kunjungan_id);
        }

        if ($kunjungan) {
            try {
                $title = 'Status Resep/Obat Diperbarui';
                $body  = 'Status obat Anda kini: ' . ($resep->status ?? '-');

                $this->notifyPasienFromKunjungan($kunjungan, $title, $body, [
                    'type'       => 'obat_status',
                    'resep_id'   => $resep->id,
                    'new_status' => $resep->status,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim notif updateStatusObat: ' . $e->getMessage());
            }
        } else {
            Log::warning('updateStatusObat: kunjungan tidak ditemukan untuk resep_id=' . $resep->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status obat berhasil diperbarui',
            'data'    => $resep,
        ]);
    }

    public function prosesPembayaran(Request $request)
    {
        try {
            Log::info('=== PROSES PEMBAYARAN START ===', [
                'request_data' => $request->all(),
                'timestamp' => now()
            ]);

            // Validasi input
            $request->validate([
                'pembayaran_id' => 'required|exists:pembayaran,id',
                'uang_yang_diterima' => 'required|numeric|min:0',
                'metode_pembayaran_id' => 'required|exists:metode_pembayaran,id',
                'catatan' => 'nullable|string|max:255',
            ]);

            // Load pembayaran dengan semua relasi
            $pembayaran = Pembayaran::with(['emr.kunjungan', 'emr.resep'])
                ->find($request->pembayaran_id);

            if (!$pembayaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pembayaran tidak ditemukan'
                ], 404);
            }

            // Cek apakah sudah dibayar
            if ($pembayaran->status == 'Sudah Bayar') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran sudah selesai sebelumnya'
                ], 400);
            }

            // Validasi relasi
            if (!$pembayaran->emr || !$pembayaran->emr->kunjungan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data EMR atau kunjungan tidak ditemukan'
                ], 404);
            }

            $kunjunganId = $pembayaran->emr->kunjungan->id;
            $uangDiterima = (float) $request->uang_yang_diterima;
            $totalTagihan = (float) $pembayaran->total_tagihan;

            // Validasi jumlah uang
            if ($uangDiterima < $totalTagihan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Uang yang diterima kurang dari total tagihan',
                    'data' => [
                        'total_tagihan' => $totalTagihan,
                        'uang_diterima' => $uangDiterima,
                        'kekurangan' => $totalTagihan - $uangDiterima,
                    ]
                ], 400);
            }

            $kembalian = $uangDiterima - $totalTagihan;

            // Process payment in transaction
            DB::transaction(function () use ($pembayaran, $kunjunganId, $uangDiterima, $kembalian, $request) {
                // 1. Update pembayaran
                $pembayaran->update([
                    'status' => 'Sudah Bayar',
                    'metode_pembayaran_id' => $request->metode_pembayaran_id,
                    'uang_yang_diterima' => $uangDiterima,
                    'kembalian' => $kembalian,
                    'tanggal_pembayaran' => now(),
                    'catatan' => $request->catatan ?? 'Pembayaran di kasir',
                ]);

                Log::info('Pembayaran updated', ['pembayaran_id' => $pembayaran->id]);

                // 2. Update kunjungan status ke "Succeed" (PENTING - direct DB update)
                $affectedRows = DB::table('kunjungan')
                    ->where('id', $kunjunganId)
                    ->update([
                        'status' => 'Succeed',  // ✅ SESUAI MIGRATION
                        'updated_at' => now(),
                    ]);

                Log::info('Kunjungan updated to Succeed', [
                    'kunjungan_id' => $kunjunganId,
                    'affected_rows' => $affectedRows,
                ]);

                // 3. Update status resep obat
                if ($pembayaran->emr->resep) {
                    DB::table('resep_obat')
                        ->where('resep_id', $pembayaran->emr->resep->id)
                        ->update([
                            'status' => 'Sudah Diambil',
                            'updated_at' => now(),
                        ]);

                    Log::info('Resep obat updated to Sudah Diambil');
                }
            });

            // Refresh data
            $pembayaran->refresh();

            Log::info('=== PROSES PEMBAYARAN SUCCESS ===', [
                'pembayaran_id' => $pembayaran->id,
                'kunjungan_id' => $kunjunganId,
                'total_tagihan' => $totalTagihan,
                'uang_diterima' => $uangDiterima,
                'kembalian' => $kembalian,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diproses',
                'data' => [
                    'pembayaran' => $pembayaran,
                    'total_tagihan' => $totalTagihan,
                    'uang_diterima' => $uangDiterima,
                    'kembalian' => $kembalian,
                    'tanggal_pembayaran' => $pembayaran->tanggal_pembayaran,
                ],
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('=== PROSES PEMBAYARAN ERROR ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }



    public function prosesPembayaranKasir(Request $request)
    {
        try {
            $request->validate([
                'pembayaran_id' => 'required|exists:pembayaran,id',
                'uang_yang_diterima' => 'required|numeric|min:0',
                'metode_pembayaran_id' => 'required|exists:metode_pembayaran,id', // TAMBAHKAN INI
                'catatan' => 'nullable|string|max:255',
            ]);

            Log::info('Processing cash payment at cashier:', [
                'pembayaran_id' => $request->pembayaran_id,
                'uang_diterima' => $request->uang_yang_diterima,
                'metode_pembayaran_id' => $request->metode_pembayaran_id, // LOG INI
            ]);

            $pembayaran = Pembayaran::with(['emr.kunjungan', 'emr.resep.obat'])->find($request->pembayaran_id);

            if (!$pembayaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pembayaran tidak ditemukan',
                ], 404);
            }

            if ($pembayaran->status === 'Sudah Bayar') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran sudah selesai sebelumnya',
                ], 400);
            }

            $uangDiterima = (float) $request->uang_yang_diterima;
            $totalTagihan = (float) $pembayaran->total_tagihan;

            if ($uangDiterima < $totalTagihan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Uang yang diterima kurang dari total tagihan',
                    'data' => [
                        'total_tagihan' => $totalTagihan,
                        'uang_diterima' => $uangDiterima,
                        'kekurangan' => $totalTagihan - $uangDiterima,
                    ],
                ], 400);
            }

            $kembalian = $uangDiterima - $totalTagihan;

            // Process payment
            DB::transaction(function () use ($pembayaran, $uangDiterima, $kembalian, $request) {
                // UPDATE dengan metode pembayaran yang dipilih kasir
                $pembayaran->update([
                    'status' => 'Sudah Bayar',
                    'metode_pembayaran_id' => $request->metode_pembayaran_id, // TAMBAHKAN INI
                    'uang_yang_diterima' => $uangDiterima,
                    'kembalian' => $kembalian,
                    'tanggal_pembayaran' => now(),
                    'catatan' => $request->catatan ?? 'Pembayaran di kasir',
                ]);

                // ... rest of the code remains the same
            });

            $pembayaran->refresh();

            Log::info('Cash payment completed successfully:', [
                'pembayaran_id' => $pembayaran->id,
                'total_tagihan' => $totalTagihan,
                'uang_diterima' => $uangDiterima,
                'kembalian' => $kembalian,
                'metode_pembayaran_id' => $pembayaran->metode_pembayaran_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diproses',
                'data' => [
                    'pembayaran' => $pembayaran,
                    'total_tagihan' => $totalTagihan,
                    'uang_diterima' => $uangDiterima,
                    'kembalian' => $kembalian,
                    'tanggal_pembayaran' => $pembayaran->tanggal_pembayaran,
                    'metode_pembayaran' => $pembayaran->metodePembayaran, // TAMBAHKAN INI
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Cash payment validation error: ', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Cash payment error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    // NEW: Get pending cash payments for cashier
    public function getPendingCashPayments()
    {
        try {
            $pendingPayments = Pembayaran::with([
                'emr.kunjungan.pasien',
                'emr.kunjungan.poli',
                'emr.resep.obat'
            ])
                ->where('status', 'Belum Bayar')
                ->where('metode_pembayaran', 'Cash')
                ->orderBy('created_at', 'asc')
                ->get();

            $formattedPayments = $pendingPayments->map(function ($pembayaran) {
                $kunjungan = $pembayaran->emr->kunjungan;
                $pasien = $kunjungan->pasien;

                return [
                    'pembayaran_id' => $pembayaran->id,
                    'kode_transaksi' => $pembayaran->kode_transaksi,
                    'total_tagihan' => $pembayaran->total_tagihan,
                    'created_at' => $pembayaran->created_at,
                    'pasien' => [
                        'nama_pasien' => $pasien->nama_pasien,
                        'id' => $pasien->id,
                    ],
                    'kunjungan' => [
                        'id' => $kunjungan->id,
                        'no_antrian' => $kunjungan->no_antrian,
                        'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                    ],
                    'poli' => [
                        'nama_poli' => $kunjungan->poli->nama_poli ?? 'Umum',
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran pending berhasil diambil',
                'data' => $formattedPayments,
                'total_pending' => $formattedPayments->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting pending cash payments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pembayaran pending: ' . $e->getMessage(),
            ], 500);
        }
    }
    // midtrans
    public function updateStatusResepObat(Request $request)
    {
        try {
            $request->validate([
                'resep_id' => ['required', 'exists:resep,id'],
                'obat_id' => ['required', 'exists:obat,id'],
                'status' => ['required', 'string'], // contoh: 'belum bayar' / 'sudah bayar'
            ]);

            DB::transaction(function () use ($request) {
                // Ambil resep
                $resep = Resep::findOrFail($request->resep_id);

                // Cek apakah obat ada di dalam resep ini
                $obat = $resep->obat()->where('obat_id', $request->obat_id)->firstOrFail();

                // Update status di tabel pivot resep_obat
                $resep->obat()->updateExistingPivot($request->obat_id, [
                    'status' => $request->status,
                ]);

                // Jika status berubah jadi "sudah bayar", kurangi stok obat
                if ($request->status === 'sudah bayar') {
                    $jumlahObat = $obat->pivot->jumlah;

                    if ($obat->jumlah < $jumlahObat) {
                        throw new \Exception('Stok obat tidak mencukupi.');
                    }

                    $obat->decrement('jumlah', $jumlahObat);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Status resep obat berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status resep obat: ' . $e->getMessage(),
            ], 500);
        }
    }

    // public function checkout(Request $request)
    // {
    //     try {
    //         // Konfigurasi Midtrans
    //         Config::$serverKey = config('midtrans.server_key');
    //         Config::$isProduction = config('midtrans.is_production');
    //         Config::$isSanitized = true;
    //         Config::$is3ds = true;

    //         // Data transaksi (bisa ambil dari DB)
    //         $params = [
    //             'transaction_details' => [
    //                 'order_id' => rand(),
    //                 'gross_amount' => 150000, // nominal transaksi
    //             ],
    //             'customer_details' => [
    //                 'first_name' => 'Budi',
    //                 'email' => 'budi@example.com',
    //             ],
    //         ];

    //         $snapToken = Snap::getSnapToken($params);

    //         // Kirim ke view
    //         return view('payment', compact('snapToken'));
    //     } catch (\Exception $e) {
    //         Log::error('Checkout error: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Terjadi kesalahan sistem',
    //         ], 500);
    //     }
    // }

    public function notificationHandler(Request $request)
    {
        try {
            $notif = new \Midtrans\Notification();
            $transaction = $notif->transaction_status;
            $order_id = $notif->order_id;

            $dataPembayaran = Pembayaran::firstOrFail($request->id);

            if ($transaction == 'settlement') {
                $dataPembayaran->update([
                    'status' => 'Sudah Bayar'
                ]);
            }

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('Notification handler error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }

    // public function createMidtransTransaction(Request $request)
    // {
    //     try {
    //         Config::$serverKey = config('midtrans.server_key');
    //         Config::$isProduction = config('midtrans.is_production');
    //         Config::$isSanitized = true;
    //         Config::$is3ds = true;

    //         $request->validate([
    //             'pembayaran_id' => 'nullable|exists:pembayaran,id',
    //             'kunjungan_id' => 'nullable|exists:kunjungan,id',
    //         ]);

    //         Log::info('🔥 Creating Midtrans transaction:', [
    //             'pembayaran_id' => $request->pembayaran_id,
    //             'kunjungan_id' => $request->kunjungan_id,
    //         ]);

    //         // Cari pembayaran
    //         $pembayaran = null;
    //         if ($request->filled('pembayaran_id')) {
    //             $pembayaran = Pembayaran::with([
    //                 'emr.kunjungan.pasien.user',
    //                 'emr.kunjungan.layanan', // FIXED: Load layanan
    //                 'emr.resep.obat'
    //             ])->find($request->pembayaran_id);
    //         } elseif ($request->filled('kunjungan_id')) {
    //             $pembayaran = Pembayaran::whereHas('emr', function ($query) use ($request) {
    //                 $query->where('kunjungan_id', $request->kunjungan_id);
    //             })->with([
    //                 'emr.kunjungan.pasien.user',
    //                 'emr.kunjungan.layanan', // FIXED: Load layanan
    //                 'emr.resep.obat'
    //             ])->first();
    //         }

    //         if (!$pembayaran) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Data pembayaran tidak ditemukan',
    //             ], 404);
    //         }

    //         if ($pembayaran->status === 'Sudah Bayar') {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Pembayaran sudah selesai',
    //             ], 400);
    //         }

    //         $pasien = $pembayaran->emr->kunjungan->pasien;
    //         $kunjungan = $pembayaran->emr->kunjungan;

    //         // Generate order_id
    //         $orderId = 'KLINIK-' . $pembayaran->id . '-' . time();

    //         // FIXED: Buat item details dari layanan (bukan hardcode konsultasi)
    //         $itemDetails = [];

    //         // Tambahkan layanan dari kunjungan
    //         if ($kunjungan->layanan && $kunjungan->layanan->isNotEmpty()) {
    //             foreach ($kunjungan->layanan as $layanan) {
    //                 $jumlah = (int) $layanan->pivot->jumlah;
    //                 $hargaLayanan = (int) $layanan->harga_layanan;

    //                 $itemDetails[] = [
    //                     'id' => 'layanan-' . $layanan->id,
    //                     'price' => $hargaLayanan,
    //                     'quantity' => $jumlah,
    //                     'name' => $layanan->nama_layanan,
    //                 ];

    //                 Log::info('Added layanan to Midtrans items:', [
    //                     'id' => 'layanan-' . $layanan->id,
    //                     'name' => $layanan->nama_layanan,
    //                     'price' => $hargaLayanan,
    //                     'quantity' => $jumlah,
    //                     'subtotal' => $hargaLayanan * $jumlah,
    //                 ]);
    //             }
    //         } else {
    //             // Fallback: Jika tidak ada layanan, gunakan default konsultasi
    //             $itemDetails[] = [
    //                 'id' => 'konsultasi-' . $pembayaran->id,
    //                 'price' => 150000,
    //                 'quantity' => 1,
    //                 'name' => 'Konsultasi Dokter',
    //             ];

    //             Log::warning('No layanan found, using default consultation fee');
    //         }

    //         // Tambahkan obat jika ada
    //         if ($pembayaran->emr && $pembayaran->emr->resep && $pembayaran->emr->resep->obat) {
    //             foreach ($pembayaran->emr->resep->obat as $obat) {
    //                 $jumlah = (int) $obat->pivot->jumlah;
    //                 $hargaObat = (int) $obat->total_harga;

    //                 $itemDetails[] = [
    //                     'id' => 'obat-' . $obat->id,
    //                     'price' => $hargaObat,
    //                     'quantity' => $jumlah,
    //                     'name' => $obat->nama_obat,
    //                 ];

    //                 Log::info('Added obat to Midtrans items:', [
    //                     'id' => 'obat-' . $obat->id,
    //                     'name' => $obat->nama_obat,
    //                     'price' => $hargaObat,
    //                     'quantity' => $jumlah,
    //                     'subtotal' => $hargaObat * $jumlah,
    //                 ]);
    //             }
    //         }

    //         // Hitung total untuk validasi
    //         $calculatedTotal = 0;
    //         foreach ($itemDetails as $item) {
    //             $calculatedTotal += ($item['price'] * $item['quantity']);
    //         }

    //         Log::info('Midtrans transaction totals:', [
    //             'calculated_total' => $calculatedTotal,
    //             'pembayaran_total_tagihan' => $pembayaran->total_tagihan,
    //             'item_details_count' => count($itemDetails),
    //         ]);

    //         // Parameter Midtrans
    //         $params = [
    //             'transaction_details' => [
    //                 'order_id' => $orderId,
    //                 'gross_amount' => (int) $pembayaran->total_tagihan, // Gunakan dari database
    //             ],
    //             'customer_details' => [
    //                 'first_name' => $pasien->nama_pasien ?? 'Pasien',
    //                 'last_name' => 'Klinik',
    //                 'email' => $pasien->user->email ?? 'pasien@klinik.com',
    //                 'phone' => '08123456789',
    //             ],
    //             'item_details' => $itemDetails,
    //         ];

    //         Log::info('📋 Midtrans params:', $params);

    //         // Generate Snap Token
    //         $snapToken = Snap::getSnapToken($params);

    //         // Update pembayaran
    //         $pembayaran->update([
    //             'metode_pembayaran' => 'Midtrans',
    //         ]);

    //         // Simpan order_id dalam cache
    //         Cache::put('midtrans_order_' . $orderId, $pembayaran->id, now()->addHours(24));

    //         Log::info('✅ Midtrans token generated successfully:', [
    //             'order_id' => $orderId,
    //             'pembayaran_id' => $pembayaran->id,
    //             'snap_token' => substr($snapToken, 0, 20) . '...',
    //             'gross_amount' => $pembayaran->total_tagihan,
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Token Midtrans berhasil dibuat',
    //             'data' => [
    //                 'snap_token' => $snapToken,
    //                 'order_id' => $orderId,
    //                 'amount' => $pembayaran->total_tagihan,
    //                 'client_key' => config('midtrans.client_key'),
    //                 'is_sandbox' => !config('midtrans.is_production'),
    //             ],
    //         ]);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         Log::error('❌ Validation error: ', $e->errors());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation error',
    //             'errors' => $e->errors(),
    //         ], 422);
    //     } catch (\Exception $e) {
    //         Log::error('❌ Error creating Midtrans transaction: ' . $e->getMessage());
    //         Log::error('Stack trace: ' . $e->getTraceAsString());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal membuat transaksi Midtrans: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // public function midtransCallback(Request $request)
    // {
    //     try {
    //         Log::info('🔔 Midtrans Sandbox callback received:', $request->all());

    //         // Menggunakan Midtrans Notification SDK
    //         $notification = new Notification();

    //         $transactionStatus = $notification->transaction_status;
    //         $orderId = $notification->order_id;
    //         $fraudStatus = isset($notification->fraud_status) ? $notification->fraud_status : 'accept';

    //         Log::info('Processing callback:', [
    //             'order_id' => $orderId,
    //             'transaction_status' => $transactionStatus,
    //             'fraud_status' => $fraudStatus,
    //         ]);

    //         // Cari pembayaran berdasarkan order_id dari cache atau parsing
    //         $pembayaranId = Cache::get('midtrans_order_' . $orderId);

    //         if (!$pembayaranId) {
    //             // Fallback: parse order_id untuk mendapatkan pembayaran_id
    //             if (preg_match('/KLINIK-(\d+)-\d+/', $orderId, $matches)) {
    //                 $pembayaranId = $matches[1];
    //                 Log::info('📋 Pembayaran ID parsed from order_id: ' . $pembayaranId);
    //             }
    //         }

    //         if (!$pembayaranId) {
    //             Log::error('❌ Cannot find pembayaran for order_id: ' . $orderId);
    //             return response()->json(['status' => 'error', 'message' => 'Pembayaran not found'], 404);
    //         }

    //         $pembayaran = Pembayaran::with(['emr.kunjungan', 'emr.resep.obat'])->find($pembayaranId);

    //         if (!$pembayaran) {
    //             Log::error('❌ Pembayaran not found in DB: ' . $pembayaranId);
    //             return response()->json(['status' => 'error', 'message' => 'Pembayaran record not found'], 404);
    //         }

    //         Log::info('✅ Found pembayaran:', [
    //             'pembayaran_id' => $pembayaran->id,
    //             'current_status' => $pembayaran->status,
    //             'emr_id' => $pembayaran->emr_id,
    //             'kunjungan_id' => $pembayaran->emr->kunjungan->id ?? null,
    //         ]);

    //         DB::transaction(function () use ($pembayaran, $transactionStatus, $fraudStatus, $orderId) {
    //             if ($transactionStatus == 'capture') {
    //                 if ($fraudStatus == 'challenge') {
    //                     Log::info('⚠️ Payment challenge - waiting verification');
    //                     $this->updatePembayaranStatus($pembayaran, 'Pending', $orderId);
    //                 } else if ($fraudStatus == 'accept') {
    //                     Log::info('✅ Payment capture accepted');
    //                     $this->completeMidtransPayment($pembayaran, $orderId);
    //                 }
    //             } else if ($transactionStatus == 'settlement') {
    //                 Log::info('✅ Payment settlement - completing payment');
    //                 $this->completeMidtransPayment($pembayaran, $orderId);
    //             } else if ($transactionStatus == 'pending') {
    //                 Log::info('⏳ Payment pending');
    //                 $this->updatePembayaranStatus($pembayaran, 'Belum Bayar', $orderId);
    //             } else if (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
    //                 Log::error('❌ Payment failed: ' . $transactionStatus);
    //                 $this->updatePembayaranStatus($pembayaran, 'Belum Bayar', $orderId);
    //             }
    //         });

    //         Log::info('✅ Callback processed successfully');
    //         return response()->json(['status' => 'ok']);
    //     } catch (\Exception $e) {
    //         Log::error('❌ Midtrans callback error: ' . $e->getMessage());
    //         Log::error('Stack trace: ' . $e->getTraceAsString());
    //         return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    //     }
    // }

    // private function completeMidtransPayment($pembayaran, $orderId)
    // {
    //     Log::info('💰 Completing Midtrans payment:', [
    //         'pembayaran_id' => $pembayaran->id,
    //         'order_id' => $orderId,
    //         'before_status' => $pembayaran->status,
    //     ]);

    //     // Update pembayaran
    //     $updateResult = $pembayaran->update([
    //         'status' => 'Sudah Bayar',
    //         'metode_pembayaran' => 'Midtrans',
    //         'tanggal_pembayaran' => now(),
    //         'uang_yang_diterima' => $pembayaran->total_tagihan,
    //         'kembalian' => 0,
    //     ]);

    //     Log::info('📝 Pembayaran update result: ' . ($updateResult ? 'SUCCESS' : 'FAILED'));

    //     // Update kunjungan status
    //     if ($pembayaran->emr && $pembayaran->emr->kunjungan) {
    //         $kunjunganUpdateResult = $pembayaran->emr->kunjungan->update([
    //             'status' => 'Succeed'
    //         ]);

    //         Log::info('📝 Kunjungan update result: ' . ($kunjunganUpdateResult ? 'SUCCESS' : 'FAILED'), [
    //             'kunjungan_id' => $pembayaran->emr->kunjungan->id,
    //             'new_status' => 'Succeed',
    //         ]);
    //     }

    //     // Update status resep obat otomatis ke "Sudah Diambil"
    //     if ($pembayaran->emr && $pembayaran->emr->resep) {
    //         DB::table('resep_obat')
    //             ->where('resep_id', $pembayaran->emr->resep->id)
    //             ->update([
    //                 'status' => 'Sudah Diambil',
    //                 'updated_at' => now(),
    //             ]);

    //         Log::info('💊 Resep obat updated to Sudah Diambil');
    //     }

    //     // Hapus dari cache
    //     Cache::forget('midtrans_order_' . $orderId);

    //     Log::info('✅ Payment completion successful', [
    //         'pembayaran_id' => $pembayaran->id,
    //         'status' => 'Sudah Bayar',
    //         'kunjungan_status' => 'Succeed',
    //     ]);
    // }

    private function updatePembayaranStatus($pembayaran, $status, $orderId)
    {
        Log::info("📝 Updating payment status to: $status", [
            'pembayaran_id' => $pembayaran->id,
            'order_id' => $orderId,
        ]);

        $pembayaran->update([
            'status' => $status,
            'metode_pembayaran' => 'Midtrans',
        ]);

        Log::info("✅ Payment status updated successfully");
    }

    public function checkPaymentStatus($order_id)
    {
        try {
            Log::info('Checking payment status for order_id: ' . $order_id);

            // Cari pembayaran berdasarkan kode_transaksi atau ID
            $pembayaran = Pembayaran::with(['metodePembayaran', 'emr.kunjungan.pasien', 'emr.kunjungan.poli'])
                ->where(function ($query) use ($order_id) {
                    $query->where('kode_transaksi', $order_id)
                        ->orWhere('id', $order_id);
                })
                ->first();

            if (!$pembayaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pembayaran tidak ditemukan'
                ], 404);
            }

            $kunjungan = $pembayaran->emr->kunjungan;

            // Get resep data
            $resepList = Resep::with('obat')->where('emr_id', $pembayaran->emr_id)->get();

            $responseData = [
                'id' => $pembayaran->id,
                'total_tagihan' => $pembayaran->total_tagihan,
                'status_pembayaran' => $pembayaran->status,
                'kode_transaksi' => $pembayaran->kode_transaksi,
                'tanggal_pembayaran' => $pembayaran->tanggal_pembayaran,
                'metode_pembayaran_nama' => $pembayaran->metodePembayaran->nama_metode ?? null,
                'uang_yang_diterima' => $pembayaran->uang_yang_diterima,
                'kembalian' => $pembayaran->kembalian,
                'catatan' => $pembayaran->catatan,
                'pasien' => [
                    'nama_pasien' => $kunjungan->pasien->nama_pasien ?? 'Unknown'
                ],
                'poli' => [
                    'nama_poli' => $kunjungan->poli->nama_poli ?? 'Unknown'
                ],
                'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                'no_antrian' => $kunjungan->no_antrian,
                'diagnosis' => $pembayaran->emr->diagnosis ?? null,
                'resep' => $resepList->map(function ($resep) {
                    return [
                        'id' => $resep->id,
                        'jumlah' => $resep->jumlah,
                        'obat' => [
                            'nama_obat' => $resep->obat->nama_obat ?? 'Unknown',
                            'harga_obat' => $resep->obat->harga_obat ?? 0
                        ]
                    ];
                })->toArray(),
                'layanan' => [], // Bisa ditambahkan jika ada data layanan
            ];

            return response()->json([
                'success' => true,
                'message' => 'Status pembayaran berhasil diambil',
                'data' => $responseData
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking payment status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of all payments for a patient (untuk halaman ListPembayaran)
     */
    public function getListPembayaran($pasienId)
    {
        try {
            Log::info('Getting payment list for pasien_id: ' . $pasienId);

            // Validasi pasien exists
            $pasien = Pasien::find($pasienId);
            if (!$pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pasien tidak ditemukan'
                ], 404);
            }

            // Ambil semua kunjungan dengan status Payment atau Succeed yang memiliki EMR dan pembayaran
            $kunjunganList = Kunjungan::with([
                'pasien' => function ($query) {
                    $query->select('id', 'nama_pasien');
                },
                'poli' => function ($query) {
                    $query->select('id', 'nama_poli');
                },
                'emr' => function ($query) {
                    $query->select('id', 'kunjungan_id', 'diagnosis');
                },
                'emr.pembayaran' => function ($query) {
                    $query->select('id', 'emr_id', 'total_tagihan', 'status', 'kode_transaksi', 'tanggal_pembayaran', 'metode_pembayaran_id');
                },
                'emr.pembayaran.metodePembayaran',
                'emr.resep.obat' => function ($query) {
                    $query->select('obat.id', 'obat.nama_obat', 'obat.total_harga')
                        ->withPivot('jumlah', 'dosis', 'keterangan', 'status');
                }
            ])
                ->where('pasien_id', $pasienId)
                ->whereIn('status', ['Payment', 'Succeed'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Filter hanya yang memiliki EMR dan pembayaran
            $validKunjungan = $kunjunganList->filter(function ($kunjungan) {
                return $kunjungan->emr && $kunjungan->emr->pembayaran;
            });

            $formattedList = $validKunjungan->map(function ($kunjungan) {
                $pembayaran = $kunjungan->emr->pembayaran;

                // TAMBAHAN: Logic status yang konsisten dengan RiwayatKunjungan
                $effectiveStatus = $kunjungan->status;
                $paymentStatus = strtolower(trim($pembayaran->status));

                // Jika pembayaran sudah "Sudah Bayar", ubah status jadi "Succeed"
                if ($paymentStatus === 'sudah bayar') {
                    $effectiveStatus = 'Succeed';
                }

                // Hitung resep
                $resepData = [];
                if ($kunjungan->emr->resep && $kunjungan->emr->resep->obat) {
                    foreach ($kunjungan->emr->resep->obat as $obat) {
                        $resepData[] = [
                            'id' => $obat->id,
                            'jumlah' => $obat->pivot->jumlah ?? 1,
                            'obat' => [
                                'nama_obat' => $obat->nama_obat,
                                'harga_obat' => $obat->total_harga ?? 0
                            ]
                        ];
                    }
                }

                return [
                    'id' => $kunjungan->id,
                    'total_tagihan' => $pembayaran->total_tagihan,
                    'status_pembayaran' => $pembayaran->status, // Status pembayaran asli
                    'status_kunjungan' => $effectiveStatus, // Status kunjungan yang sudah diproses
                    'kode_transaksi' => $pembayaran->kode_transaksi,
                    'tanggal_pembayaran' => $pembayaran->tanggal_pembayaran,
                    'metode_pembayaran_nama' => $pembayaran->metodePembayaran->nama_metode ?? null,
                    'pasien' => [
                        'nama_pasien' => $kunjungan->pasien->nama_pasien ?? 'Unknown'
                    ],
                    'poli' => [
                        'nama_poli' => $kunjungan->poli->nama_poli ?? 'Unknown'
                    ],
                    'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                    'no_antrian' => $kunjungan->no_antrian,
                    'diagnosis' => $kunjungan->emr->diagnosis ?? null,
                    'resep' => $resepData,
                    'layanan' => [],
                    'is_emr_missing' => false,
                    'is_payment_missing' => false,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'message' => 'Daftar pembayaran berhasil diambil',
                'data' => $formattedList
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting payment list: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    // public function forceUpdatePaymentStatus(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'pembayaran_id' => 'required|exists:pembayaran,id',
    //             'metode_pembayaran' => 'required|in:Cash,Midtrans',
    //         ]);

    //         Log::info('🚨 Force update payment status requested:', [
    //             'pembayaran_id' => $request->pembayaran_id,
    //             'metode_pembayaran' => $request->metode_pembayaran,
    //         ]);

    //         $pembayaran = Pembayaran::with(['emr.kunjungan', 'emr.resep.obat'])->find($request->pembayaran_id);

    //         if (!$pembayaran) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Data pembayaran tidak ditemukan',
    //             ], 404);
    //         }

    //         if ($pembayaran->status === 'Sudah Bayar') {
    //             Log::info('⚠️ Payment already paid:', [
    //                 'pembayaran_id' => $pembayaran->id,
    //             ]);

    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Pembayaran sudah selesai sebelumnya',
    //                 'data' => $pembayaran,
    //             ]);
    //         }

    //         // Force update pembayaran
    //         DB::transaction(function () use ($pembayaran, $request) {
    //             Log::info('💪 Force updating payment:', [
    //                 'pembayaran_id' => $pembayaran->id,
    //                 'metode' => $request->metode_pembayaran,
    //                 'total_tagihan' => $pembayaran->total_tagihan,
    //             ]);

    //             $updateResult = $pembayaran->update([
    //                 'status' => 'Sudah Bayar',
    //                 'metode_pembayaran' => $request->metode_pembayaran,
    //                 'tanggal_pembayaran' => now(),
    //                 'uang_yang_diterima' => $pembayaran->total_tagihan,
    //                 'kembalian' => 0,
    //                 'catatan' => 'Force update - pembayaran dikonfirmasi manual',
    //             ]);

    //             Log::info('📝 Force update payment result: ' . ($updateResult ? 'SUCCESS' : 'FAILED'));

    //             // Update kunjungan status
    //             if ($pembayaran->emr && $pembayaran->emr->kunjungan) {
    //                 $kunjunganUpdateResult = $pembayaran->emr->kunjungan->update([
    //                     'status' => 'Succeed'
    //                 ]);

    //                 Log::info('📝 Force update kunjungan result: ' . ($kunjunganUpdateResult ? 'SUCCESS' : 'FAILED'), [
    //                     'kunjungan_id' => $pembayaran->emr->kunjungan->id,
    //                     'new_status' => 'Succeed',
    //                 ]);
    //             }

    //             // Update status resep obat otomatis ke "Sudah Diambil" jika Midtrans
    //             if ($request->metode_pembayaran === 'Midtrans' && $pembayaran->emr && $pembayaran->emr->resep) {
    //                 DB::table('resep_obat')
    //                     ->where('resep_id', $pembayaran->emr->resep->id)
    //                     ->update([
    //                         'status' => 'Sudah Diambil',
    //                         'updated_at' => now(),
    //                     ]);

    //                 Log::info('💊 Force update resep obat to Sudah Diambil');
    //             }
    //         });

    //         $pembayaran->refresh();

    //         Log::info('✅ Force update completed successfully:', [
    //             'pembayaran_id' => $pembayaran->id,
    //             'status' => $pembayaran->status,
    //             'metode_pembayaran' => $pembayaran->metode_pembayaran,
    //             'tanggal_pembayaran' => $pembayaran->tanggal_pembayaran,
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Status pembayaran berhasil diperbarui',
    //             'data' => $pembayaran,
    //         ]);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         Log::error('❌ Force update validation error: ', $e->errors());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation error',
    //             'errors' => $e->errors(),
    //         ], 422);
    //     } catch (\Exception $e) {
    //         Log::error('❌ Force update error: ' . $e->getMessage());
    //         Log::error('Stack trace: ' . $e->getTraceAsString());

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal memperbarui status pembayaran: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }


    // public function simulateMidtransCallback(Request $request)
    // {
    //     try {
    //         // HANYA untuk SANDBOX mode
    //         if (config('midtrans.is_production')) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Endpoint hanya tersedia di sandbox mode',
    //             ], 403);
    //         }

    //         $request->validate([
    //             'order_id' => 'required|string',
    //             'transaction_status' => 'required|in:settlement,capture,pending,deny,cancel,expire',
    //         ]);

    //         $orderId = $request->order_id;
    //         $transactionStatus = $request->transaction_status;

    //         Log::info('🧪 Simulating Midtrans callback:', [
    //             'order_id' => $orderId,
    //             'transaction_status' => $transactionStatus,
    //         ]);

    //         // Cari pembayaran berdasarkan order_id
    //         $pembayaranId = Cache::get('midtrans_order_' . $orderId);

    //         if (!$pembayaranId && preg_match('/KLINIK-(\d+)-\d+/', $orderId, $matches)) {
    //             $pembayaranId = $matches[1];
    //         }

    //         if (!$pembayaranId) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Order ID tidak ditemukan',
    //             ], 404);
    //         }

    //         $pembayaran = Pembayaran::with(['emr.kunjungan', 'emr.resep.obat'])->find($pembayaranId);

    //         if (!$pembayaran) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Pembayaran tidak ditemukan',
    //             ], 404);
    //         }

    //         // Simulasi proses callback
    //         if (in_array($transactionStatus, ['settlement', 'capture'])) {
    //             $this->completeMidtransPayment($pembayaran, $orderId);

    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Callback berhasil disimulasi - pembayaran selesai',
    //                 'data' => $pembayaran->fresh(),
    //             ]);
    //         } else {
    //             $this->updatePembayaranStatus($pembayaran, 'Belum Bayar', $orderId);

    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Callback berhasil disimulasi - status: ' . $transactionStatus,
    //                 'data' => $pembayaran->fresh(),
    //             ]);
    //         }
    //     } catch (\Exception $e) {
    //         Log::error('❌ Simulate callback error: ' . $e->getMessage());

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal mensimulasi callback: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    /**
     * Cek apakah pembayaran sudah expired dan perlu dibatalkan
     */
    // public function checkExpiredPayments()
    // {
    //     try {
    //         // Cari pembayaran yang belum dibayar dan sudah lebih dari 30 menit
    //         $expiredPayments = Pembayaran::where('status', 'Belum Bayar')
    //             ->where('metode_pembayaran', 'Midtrans')
    //             ->where('created_at', '<', now()->subMinutes(30))
    //             ->with(['emr.kunjungan'])
    //             ->get();

    //         Log::info('🕐 Checking expired payments, found: ' . $expiredPayments->count());

    //         $expiredCount = 0;
    //         foreach ($expiredPayments as $pembayaran) {
    //             // Update status kunjungan kembali ke Payment jika diperlukan
    //             if ($pembayaran->emr && $pembayaran->emr->kunjungan && $pembayaran->emr->kunjungan->status === 'Succeed') {
    //                 $pembayaran->emr->kunjungan->update(['status' => 'Payment']);
    //                 Log::info('⏰ Reset kunjungan status for expired payment: ' . $pembayaran->id);
    //                 $expiredCount++;
    //             }
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => "Ditemukan {$expiredCount} pembayaran yang expired",
    //             'expired_count' => $expiredCount,
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('❌ Check expired payments error: ' . $e->getMessage());

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal mengecek expired payments: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // Add these functions to your APIMobileController class

    /**
     * Get all poli data
     */
    public function getPoliDokter()
    {
        try {
            $data = Poli::all(); // ambil semua data poli dari tabel
            return response()->json([
                'success' => true,
                'message' => 'Data poli berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting poli dokter: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get poli data by dokter ID with jadwal
     */
    public function getPolibyIdDokter($dokter_id)
    {
        try {
            // Ambil dokter beserta poli dan jadwalnya
            $dokter = Dokter::with(['poli', 'jadwalDokter'])->find($dokter_id);

            if (!$dokter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokter tidak ditemukan'
                ], 404);
            }

            // Kembalikan data lengkap
            return response()->json([
                'success' => true,
                'message' => 'Data poli dan jadwal untuk dokter berhasil diambil',
                'data' => [
                    'id_dokter' => $dokter->id,
                    'nama_dokter' => $dokter->nama_dokter,
                    'foto_dokter' => $dokter->foto_dokter,
                    'no_hp' => $dokter->no_hp,
                    'poli' => [
                        'id' => $dokter->poli->id,
                        'nama_poli' => $dokter->poli->nama_poli
                    ],
                    'jadwal' => $dokter->jadwalDokter->map(function ($item) {
                        return [
                            'hari' => $item->hari,
                            'jam_awal' => $item->jam_awal,
                            'jam_selesai' => $item->jam_selesai
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting poli by dokter ID: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all dokter with poli and jadwal data
     */
    public function getAllDokter()
    {
        try {
            // Ambil semua dokter dengan relasi poli dan jadwal
            $dokterList = Dokter::with(['poli', 'jadwalDokter'])->get();

            return response()->json([
                'success' => true,
                'message' => 'Data seluruh dokter berhasil diambil',
                'data' => $dokterList->map(function ($dokter) {
                    return [
                        'id_dokter' => $dokter->id,
                        'nama_dokter' => $dokter->nama_dokter,
                        'foto_dokter' => $dokter->foto_dokter,
                        'no_hp' => $dokter->no_hp,
                        'poli' => [
                            'id' => $dokter->poli->id ?? null,
                            'nama_poli' => $dokter->poli->nama_poli ?? '-',
                        ],
                        'jadwal' => $dokter->jadwalDokter->map(function ($item) {
                            return [
                                'hari' => $item->hari,
                                'jam_awal' => $item->jam_awal,      // Pastikan ini ada
                                'jam_selesai' => $item->jam_selesai, // Pastikan ini ada
                            ];
                        }),
                    ];
                })
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting all dokter: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get layanan by poli ID
     */
    public function getLayanan($poli_id)
    {
        try {
            // Ambil semua layanan berdasarkan poli_id
            $layanan = \App\Models\Layanan::where('poli_id', $poli_id)->get();

            // Jika tidak ada layanan ditemukan
            if ($layanan->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ada layanan untuk poli ini.',
                    'data' => []
                ]);
            }

            // Jika ada layanan
            return response()->json([
                'success' => true,
                'message' => 'Data layanan berhasil diambil',
                'data' => $layanan->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'nama_layanan' => $item->nama_layanan,
                        'harga_layanan' => number_format($item->harga_layanan, 2, ',', '.')
                    ];
                })
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting layanan by poli ID: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDataMetodePembayaran()
    {
        try {
            $dataMetodePembayaran = MetodePembayaran::orderBy('nama_metode', 'asc')->get();

            return response()->json([
                'success' => true,
                'status' => 200,
                'message' => 'Berhasil mengambil data metode pembayaran',
                'data' => $dataMetodePembayaran->map(function ($metode) {
                    return [
                        'id' => $metode->id,
                        'nama_metode' => $metode->nama_metode,
                        'icon' => $this->getPaymentMethodIcon($metode->nama_metode),
                        'created_at' => $metode->created_at,
                        'updated_at' => $metode->updated_at,
                    ];
                }),
                'total' => $dataMetodePembayaran->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting metode pembayaran: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data metode pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    // TAMBAHKAN helper method untuk icon pembayaran
    private function getPaymentMethodIcon($metodeName)
    {
        $icons = [
            'Tunai' => '💰',
            'Cash' => '💰',
            'Kartu Debit' => '💳',
            'Kartu Kredit' => '💳',
            'QRIS' => '📱',
            'Transfer Bank' => '🏦',
            'Midtrans' => '💻',
            'E-Wallet' => '📱',
            'OVO' => '🟠',
            'DANA' => '🔵',
            'GoPay' => '🟢',
            'ShopeePay' => '🟠',
        ];

        return $icons[$metodeName] ?? '💳';
    }
    private function ensureQrCodePasien(\App\Models\Pasien $pasien): void
    {
        if (empty($pasien->qr_code_pasien)) {
            $payload = 'PAS-' . strtoupper(uniqid());
            $pasien->qr_code_pasien = $payload;
            $pasien->save();
            Log::info('Auto-set qr_code_pasien', ['pasien_id' => $pasien->id, 'qr' => $payload]);
        }
    }
}
