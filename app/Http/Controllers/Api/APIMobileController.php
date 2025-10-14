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
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;
use App\Models\Poli;

class APIMobileController extends Controller
{

    public function __construct()
    {
        // Konfigurasi Midtrans SANDBOX
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = false; // SANDBOX MODE
        Config::$isSanitized = true;
        Config::$is3ds = true;

        Log::info('Midtrans Sandbox Configuration Loaded');
    }


    /** LOGIN */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string|min:6',
            ]);

            $user = User::where('username', $request->username)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Username atau password salah',
                ], 401);
            }

            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ]);
        } catch (\Exception $e) {
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
        try {
            $request->validate([
                'username' => 'required|string|unique:user,username',
                'email' => 'required|email|unique:user,email',
                'password' => 'required|string|min:6',
            ]);

            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role' => 'Pasien',
            ]);

            Pasien::create([
                'user_id' => $user->id,
                'nama_pasien' => null,
                'alamat' => null,
                'tanggal_lahir' => null,
                'jenis_kelamin' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil',
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('Register error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
            ], 500);
        }
    }

    public function getProfile(Request $request)
    {
        try {
            $user = $request->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                ], 404);
            }

            $pasien = $user->pasien;

            if (! $pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pasien tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $pasien->id,
                    'nama_pasien' => $pasien->nama_pasien,
                    'alamat' => $pasien->alamat,
                    'tanggal_lahir' => $pasien->tanggal_lahir,
                    'jenis_kelamin' => $pasien->jenis_kelamin,
                    'foto_pasien' => $pasien->foto_pasien,
                    'created_at' => $user->created_at,
                    'email' => $user->email,
                    'username' => $user->username,
                    'role' => $user->role,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Get profile error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
            ], 500);
        }
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

        if ($daysUntilTarget === 0) {
            $daysUntilTarget = 7;
        }

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

    public function ubahStatusKunjungan(Request $request)
    {
        try {
            $dataKunjungan = Kunjungan::findOrFail($request->id);

            if ($dataKunjungan->status === 'Pending') {
                $dataKunjungan->update([
                    'status' => 'Waiting',
                ]);

                return response()->json([
                    'success' => true,
                    'status' => 200,
                    'Data Kunjungan' => $dataKunjungan,
                    'message' => 'Berhasil Merubah Status Kunjungan Dari Pending Menjadi Waiting',
                ]);
            } elseif ($dataKunjungan->status === 'Waiting') {
                $dataKunjungan->update([
                    'status' => 'Engaged',
                ]);

                return response()->json([
                    'success' => true,
                    'status' => 200,
                    'Data Kunjungan' => $dataKunjungan,
                    'message' => 'Berhasil Merubah Status Kunjungan Dari Waiting Menjadi Engaged',
                ]);
            }

            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Error',
                'Data Kunjungan' => $dataKunjungan,
            ]);
        } catch (\Exception $e) {
            Log::error('Error ubah status kunjungan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
            ], 500);
        }
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

    public function bookingDokter(Request $request)
    {
        try {
            Log::info('🔥 bookingDokter called with data: ', $request->all());

            $request->validate([
                'pasien_id' => ['required', 'exists:pasien,id'],
                'poli_id' => ['required', 'exists:poli,id'], // GANTI dari dokter_id ke poli_id
                'tanggal_kunjungan' => ['required', 'date'],
                'keluhan_awal' => ['required', 'string'],
            ]);

            $tanggalKunjungan = $request->tanggal_kunjungan;
            $poliId = $request->poli_id; // GANTI dari dokter_id
            $pasienId = $request->pasien_id;

            Log::info("🎯 Processing booking for pasien_id: $pasienId, poli_id: $poliId, tanggal: $tanggalKunjungan");

            // GANTI logika pengecekan existing booking
            $existingBooking = Kunjungan::where('pasien_id', $pasienId)
                ->where('poli_id', $poliId) // GANTI ke poli_id
                ->where('tanggal_kunjungan', $tanggalKunjungan)
                ->whereIn('status', ['Pending', 'Confirmed', 'Waiting', 'Engaged'])
                ->first();

            if ($existingBooking) {
                Log::info("❌ Duplicate booking found for pasien_id: $pasienId, poli_id: $poliId, tanggal: $tanggalKunjungan");

                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah memiliki jadwal dengan poli ini pada tanggal yang sama. Silakan pilih tanggal lain.',
                ], 422);
            }

            $result = DB::transaction(function () use ($tanggalKunjungan, $poliId, $pasienId, $request) {
                // GANTI query untuk mencari kunjungan terakhir berdasarkan poli
                $lastKunjungan = Kunjungan::where('tanggal_kunjungan', $tanggalKunjungan)
                    ->where('poli_id', $poliId) // GANTI ke poli_id
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

                // GANTI data yang akan dibuat
                $kunjungan = new Kunjungan;
                $kunjungan->pasien_id = $pasienId;
                $kunjungan->poli_id = $poliId; // GANTI ke poli_id
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

            return response()->json([
                'success' => true,
                'message' => 'Kunjungan berhasil dibuat',
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

    public function getRiwayatKunjungan($pasienId)
    {
        try {
            Log::info('=== getRiwayatKunjungan START ===', ['pasien_id' => $pasienId]);

            $pasien = Pasien::find($pasienId);
            if (!$pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pasien tidak ditemukan',
                ], 404);
            }

            // CRITICAL FIX: Query paling basic tanpa relasi apapun dulu
            $riwayat = DB::table('kunjungan')
                ->where('pasien_id', $pasienId)
                ->orderBy('tanggal_kunjungan', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Raw query result count: ' . $riwayat->count());

            if ($riwayat->isEmpty()) {
                Log::info('No kunjungan found for pasien: ' . $pasienId);

                $pasienInfo = [
                    'id' => $pasien->id,
                    'nama_pasien' => $pasien->nama_pasien,
                    'alamat' => $pasien->alamat,
                    'tanggal_lahir' => $pasien->tanggal_lahir,
                    'umur' => $this->calculateAge($pasien->tanggal_lahir),
                    'jenis_kelamin' => $pasien->jenis_kelamin,
                    'foto_pasien' => $pasien->foto_pasien,
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'Riwayat kunjungan berhasil diambil',
                    'pasien_info' => $pasienInfo,
                    'data' => [],
                    'total_kunjungan' => 0,
                ], 200);
            }

            // Process each kunjungan individually using foreach instead of map
            $riwayatWithDetails = [];

            foreach ($riwayat as $index => $kunjunganRow) {
                try {
                    Log::info("Processing kunjungan {$index}:", [
                        'id' => $kunjunganRow->id ?? 'missing',
                        'type' => gettype($kunjunganRow),
                    ]);

                    // SAFETY CHECK: Ensure we have an ID
                    if (!isset($kunjunganRow->id) || empty($kunjunganRow->id)) {
                        Log::warning("Skipping kunjungan {$index} - missing ID");
                        continue;
                    }

                    // Convert stdClass to array for easier handling
                    $kunjunganData = (array) $kunjunganRow;

                    // Initialize default values
                    $dokterData = [
                        'id' => null,
                        'nama_dokter' => 'Tidak ada',
                        'no_hp' => 'Tidak ada',
                        'pengalaman' => 'Tidak ada',
                        'foto_dokter' => null,
                        'spesialisasi' => 'Umum',
                    ];

                    $layananData = [
                        'nama_layanan' => 'Konsultasi Umum',
                        'harga_layanan' => 150000,
                    ];

                    $biayaKonsultasi = 150000;

                    // Get dokter data safely
                    try {
                        $dokter = null;

                        // Check if has poli_id (new schema)
                        if (!empty($kunjunganData['poli_id'])) {
                            Log::info("Getting dokter via poli_id: {$kunjunganData['poli_id']}");

                            $poliResult = DB::table('poli')
                                ->where('id', $kunjunganData['poli_id'])
                                ->first();

                            if ($poliResult) {
                                $dokterResult = DB::table('dokter')
                                    ->where('poli_id', $poliResult->id)
                                    ->first();

                                if ($dokterResult) {
                                    // FIX: Gunakan nama poli, bukan spesialisasi
                                    $dokterData = [
                                        'id' => $dokterResult->id,
                                        'nama_dokter' => $dokterResult->nama_dokter ?? 'Tidak ada',
                                        'no_hp' => $dokterResult->no_hp ?? 'Tidak ada',
                                        'pengalaman' => $dokterResult->pengalaman ?? 'Tidak ada',
                                        'foto_dokter' => $dokterResult->foto_dokter,
                                        'spesialisasi' => $poliResult->nama_poli ?? 'Umum', // GUNAKAN NAMA POLI
                                    ];

                                    // Get layanan for this poli
                                    $layananResult = DB::table('layanan')
                                        ->where('poli_id', $poliResult->id)
                                        ->first();

                                    if ($layananResult) {
                                        $biayaKonsultasi = (float) $layananResult->harga_layanan;
                                        $layananData = [
                                            'nama_layanan' => $layananResult->nama_layanan,
                                            'harga_layanan' => $biayaKonsultasi,
                                        ];
                                    }
                                }
                            }
                        }
                        // Check if has dokter_id (old schema)
                        elseif (!empty($kunjunganData['dokter_id'])) {
                            Log::info("Getting dokter via dokter_id: {$kunjunganData['dokter_id']}");

                            $dokterResult = DB::table('dokter')
                                ->where('id', $kunjunganData['dokter_id'])
                                ->first();

                            if ($dokterResult) {
                                // FIX: Ambil nama poli untuk skema lama juga
                                $poliResult = DB::table('poli')
                                    ->where('id', $dokterResult->poli_id ?? 0)
                                    ->first();

                                $dokterData = [
                                    'id' => $dokterResult->id,
                                    'nama_dokter' => $dokterResult->nama_dokter ?? 'Tidak ada',
                                    'no_hp' => $dokterResult->no_hp ?? 'Tidak ada',
                                    'pengalaman' => $dokterResult->pengalaman ?? 'Tidak ada',
                                    'foto_dokter' => $dokterResult->foto_dokter,
                                    'spesialisasi' => $poliResult->nama_poli ?? 'Umum', // GUNAKAN NAMA POLI
                                ];
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error("Error getting dokter data: " . $e->getMessage());
                        // Keep default dokter data
                    }

                    // Get EMR data safely
                    $emrData = null;
                    $resepObat = [];
                    $totalObat = 0;

                    try {
                        $emrResult = DB::table('emr')
                            ->where('kunjungan_id', $kunjunganData['id'])
                            ->first();

                        if ($emrResult) {
                            $emrData = [
                                'id' => $emrResult->id,
                                'keluhan_utama' => $emrResult->keluhan_utama,
                                'riwayat_penyakit_dahulu' => $emrResult->riwayat_penyakit_dahulu,
                                'riwayat_penyakit_keluarga' => $emrResult->riwayat_penyakit_keluarga,
                                'tanda_vital' => [
                                    'tekanan_darah' => $emrResult->tekanan_darah,
                                    'suhu_tubuh' => $emrResult->suhu_tubuh,
                                    'nadi' => $emrResult->nadi,
                                    'pernapasan' => $emrResult->pernapasan,
                                    'saturasi_oksigen' => $emrResult->saturasi_oksigen,
                                ],
                                'diagnosis' => $emrResult->diagnosis,
                                'tanggal_pemeriksaan' => $emrResult->created_at,
                            ];

                            // Get resep data if exists
                            $resepResult = DB::table('resep')
                                ->where('kunjungan_id', $kunjunganData['id'])
                                ->first();

                            if ($resepResult) {
                                $resepObatResults = DB::table('resep_obat')
                                    ->join('obat', 'resep_obat.obat_id', '=', 'obat.id')
                                    ->where('resep_obat.resep_id', $resepResult->id)
                                    ->select(
                                        'obat.*',
                                        'resep_obat.jumlah',
                                        'resep_obat.dosis as resep_dosis',
                                        'resep_obat.keterangan',
                                        'resep_obat.status'
                                    )
                                    ->get();

                                foreach ($resepObatResults as $obat) {
                                    $jumlah = $obat->jumlah ?? 1;
                                    $hargaObat = $obat->total_harga ?? 0;
                                    $subtotal = $hargaObat * $jumlah;
                                    $totalObat += $subtotal;

                                    $resepObat[] = [
                                        'id' => $obat->id,
                                        'nama_obat' => $obat->nama_obat,
                                        'dosis' => $obat->resep_dosis ?? $obat->dosis,
                                        'jumlah' => $jumlah,
                                        'harga_per_item' => $hargaObat,
                                        'subtotal' => $subtotal,
                                        'keterangan' => $obat->keterangan ?? 'Sesuai anjuran dokter',
                                        'status' => $obat->status ?? 'Belum Diambil',
                                    ];
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error("Error getting EMR data: " . $e->getMessage());
                    }

                    // Get pembayaran data safely - HANYA JIKA EMR SUDAH ADA
                    $pembayaranData = null;

                    // PENTING: Hanya tampilkan data pembayaran jika EMR sudah disave
                    if ($emrData !== null) {
                        $totalTagihan = $biayaKonsultasi + $totalObat;
                        $pembayaranData = [
                            'biaya_konsultasi' => $biayaKonsultasi,
                            'total_obat' => $totalObat,
                            'total_tagihan' => $totalTagihan,
                            'status' => 'Belum Ada Pembayaran',
                        ];

                        try {
                            $pembayaranResult = DB::table('pembayaran')
                                ->join('emr', 'pembayaran.emr_id', '=', 'emr.id')
                                ->where('emr.kunjungan_id', $kunjunganData['id'])
                                ->select('pembayaran.*')
                                ->first();

                            if ($pembayaranResult) {
                                $pembayaranData = [
                                    'id' => $pembayaranResult->id,
                                    'biaya_konsultasi' => $biayaKonsultasi,
                                    'total_obat' => $totalObat,
                                    'total_tagihan' => $pembayaranResult->total_tagihan ?? $totalTagihan,
                                    'uang_yang_diterima' => $pembayaranResult->uang_yang_diterima,
                                    'kembalian' => $pembayaranResult->kembalian,
                                    'metode_pembayaran' => $pembayaranResult->metode_pembayaran,
                                    'tanggal_pembayaran' => $pembayaranResult->tanggal_pembayaran,
                                    'status' => $pembayaranResult->status,
                                ];
                            }
                        } catch (\Exception $e) {
                            Log::error("Error getting pembayaran data: " . $e->getMessage());
                        }
                    }

                    // Add to results array
                    $riwayatWithDetails[] = [
                        'id' => (int) $kunjunganData['id'],
                        'tanggal_kunjungan' => $kunjunganData['tanggal_kunjungan'] ?? null,
                        'no_antrian' => $kunjunganData['no_antrian'] ?? null,
                        'keluhan_awal' => $kunjunganData['keluhan_awal'] ?? null,
                        'status' => $kunjunganData['status'] ?? null,
                        'created_at' => $kunjunganData['created_at'] ?? null,
                        'updated_at' => $kunjunganData['updated_at'] ?? null,
                        'dokter' => $dokterData,
                        'layanan' => $layananData,
                        'emr' => $emrData,
                        'resep_obat' => $resepObat,
                        'pembayaran' => $pembayaranData,
                    ];

                    Log::info("Successfully processed kunjungan {$index}");
                } catch (\Exception $e) {
                    Log::error("Error processing kunjungan {$index}: " . $e->getMessage());
                    Log::error("Stack trace: " . $e->getTraceAsString());
                    // Skip this record and continue
                    continue;
                }
            }

            $pasienInfo = [
                'id' => $pasien->id,
                'nama_pasien' => $pasien->nama_pasien,
                'alamat' => $pasien->alamat,
                'tanggal_lahir' => $pasien->tanggal_lahir,
                'umur' => $this->calculateAge($pasien->tanggal_lahir),
                'jenis_kelamin' => $pasien->jenis_kelamin,
                'foto_pasien' => $pasien->foto_pasien,
            ];

            Log::info('Successfully processed riwayat, total records: ' . count($riwayatWithDetails));

            return response()->json([
                'success' => true,
                'message' => 'Riwayat kunjungan berhasil diambil',
                'pasien_info' => $pasienInfo,
                'data' => $riwayatWithDetails,
                'total_kunjungan' => count($riwayatWithDetails),
            ], 200);
        } catch (\Exception $e) {
            Log::error('CRITICAL ERROR in getRiwayatKunjungan: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat kunjungan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getDataDokter()
    {
        try {
            $login = Auth::user()->id;

            $dataDokter = Dokter::with('user')->where('user_id', $login)->get();

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
            $request->validate([
                'username' => ['required'],
                'password' => ['required'],
            ]);

            $user = User::where('username', $request->username)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Username atau password salah',
                ], 401);
            }

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
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ]);
        } catch (\Exception $e) {
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

                // 🔥 AMBIL SEMUA DIAGNOSIS SEBELUMNYA DARI EMR PASIEN INI
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
                        $tanggal = \Carbon\Carbon::parse($emrLama->created_at)->format('d/m/Y');
                        $riwayatList[] = "- {$emrLama->diagnosis} ({$tanggal})";
                    }
                    $riwayatDiagnosisFormatted = implode("\n", $riwayatList);
                } else {
                    $riwayatDiagnosisFormatted = "Tidak ada riwayat penyakit sebelumnya";
                }

                Log::info('🩺 Riwayat diagnosis pasien:', [
                    'pasien_id' => $kunjungan->pasien_id,
                    'jumlah_riwayat' => $riwayatDiagnosisPasien->count(),
                    'riwayat_formatted' => $riwayatDiagnosisFormatted,
                ]);

                // Create EMR record dengan riwayat diagnosis otomatis
                $emr = EMR::create([
                    'kunjungan_id' => $kunjungan->id,
                    'resep_id' => $resepId,
                    'keluhan_utama' => $request->keluhan_utama,
                    'riwayat_penyakit_dahulu' => $riwayatDiagnosisFormatted, // 🔥 AUTO-FILL
                    'riwayat_penyakit_keluarga' => $request->riwayat_penyakit_keluarga,
                    'tekanan_darah' => $request->tekanan_darah,
                    'suhu_tubuh' => $request->suhu_tubuh,
                    'nadi' => $request->nadi,
                    'pernapasan' => $request->pernapasan,
                    'saturasi_oksigen' => $request->saturasi_oksigen,
                    'diagnosis' => $request->diagnosis, // 🔥 DIAGNOSIS BARU
                ]);

                Log::info('✅ EMR created with auto-filled riwayat:', [
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

                // Create pembayaran record
                $pembayaran = Pembayaran::create([
                    'emr_id' => $emr->id,
                    'total_tagihan' => $totalTagihan,
                    'uang_yang_diterima' => 0,
                    'kembalian' => 0,
                    'metode_pembayaran' => 'Cash',
                    'tanggal_pembayaran' => now(),
                    'status' => 'Belum Bayar',
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
                'message' => 'EMR berhasil disimpan dengan riwayat diagnosis otomatis.',
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
            $poli = \App\Models\Poli::find($poli_id);
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
            $user_id = Auth::user()->id;

            $dokter = Dokter::with('user')->where('user_id', $user_id)->firstOrFail();

            if (! $dokter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dokter tidak ditemukan',
                ], 404);
            }

            $riwayatPasien = Kunjungan::with([
                'pasien' => function ($query) {
                    $query->select('id', 'nama_pasien', 'alamat', 'tanggal_lahir', 'jenis_kelamin', 'foto_pasien');
                },
                'emr' => function ($query) {
                    $query->select('id', 'kunjungan_id', 'keluhan_utama', 'diagnosis', 'created_at');
                },
                'resep.obat' => function ($query) {
                    $query->select('obat.id', 'obat.nama_obat', 'obat.dosis');
                },
            ])
                ->where('dokter_id', $dokter->id)
                ->whereIn('status', ['Succeed', 'Canceled'])
                ->orderBy('updated_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'status' => 200,
                'data' => $riwayatPasien,
                'total_pasien' => $riwayatPasien->count(),
                'dokter_info' => [
                    'id' => $dokter->id,
                    'nama_dokter' => $dokter->nama_dokter,
                    'user_id' => $user_id,
                ],
                'message' => 'Berhasil mengambil riwayat pasien yang diperiksa',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error getting riwayat pasien diperiksa: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat pasien: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getDetailRiwayatPasien($kunjunganId)
    {
        try {
            $user_id = Auth::user()->id;
            $dokter = Dokter::where('user_id', $user_id)->firstOrFail();

            $detailRiwayat = Kunjungan::with([
                'pasien',
                'emr',
                'resep.obat' => function ($query) {
                    $query->select('obat.id', 'obat.nama_obat', 'obat.dosis')
                        ->withPivot('jumlah', 'dosis', 'keterangan');
                },
            ])
                ->where('id', $kunjunganId)
                ->where('dokter_id', $dokter->id)
                ->whereIn('status', ['Succeed', 'Canceled'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'status' => 200,
                'data' => $detailRiwayat,
                'message' => 'Berhasil mengambil detail riwayat pasien',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error getting detail riwayat pasien: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail riwayat: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function sendForgotPasswordOTP(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:user,email',
            ]);

            $email = $request->email;
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

                Log::info("Forgot password OTP sent to: $email, OTP: $otp");

                return response()->json([
                    'success' => true,
                    'message' => 'Kode OTP telah dikirim ke email Anda',
                    'data' => [
                        'email' => $email,
                        'expires_in' => 5,
                    ],
                ], 200);
            } catch (\Exception $e) {
                Log::error('Failed to send forgot password email: ' . $e->getMessage());

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim email. Silakan coba lagi.',
                ], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in sendForgotPasswordOTP: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function resetPasswordWithOTP(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:user,email',
                'otp' => 'required|string|size:6',
                'new_password' => 'required|string|min:6|confirmed',
            ]);

            $email = $request->email;
            $otp = $request->otp;
            $newPassword = $request->new_password;

            $cacheKey = 'forgot_password_otp_' . $email;
            $storedOTP = Cache::get($cacheKey);

            if (! $storedOTP) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode OTP sudah kedaluwarsa. Silakan minta kode baru.',
                ], 400);
            }

            if ($storedOTP !== $otp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode OTP tidak valid.',
                ], 400);
            }

            $user = User::where('email', $email)->first();
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in resetPasswordWithOTP: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function sendForgotUsername(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:user,email',
            ]);

            $email = $request->email;
            $user = User::where('email', $email)->first();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email tidak ditemukan dalam sistem',
                ], 404);
            }

            try {
                Mail::send('emails.username_notification', [
                    'username' => $user->username,
                    'email' => $email,
                    'user_role' => $user->role,
                ], function ($message) use ($email) {
                    $message->to($email)
                        ->subject('Username Akun Anda - Royal Clinic');
                });

                Log::info("Username sent to email: $email, username: {$user->username}");

                return response()->json([
                    'success' => true,
                    'message' => 'Username telah dikirim ke email Anda',
                    'data' => [
                        'email' => $email,
                        'sent_at' => now()->toISOString(),
                    ],
                ], 200);
            } catch (\Exception $e) {
                Log::error('Failed to send username email: ' . $e->getMessage());

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim email. Silakan coba lagi.',
                ], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in sendForgotUsername: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ], 500);
        }
    }

    // 🔥 METHOD PEMBAYARAN - FIXED
    public function getPembayaranPasien($pasienId)
    {
        try {
            $pasien = Pasien::find($pasienId);
            if (!$pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pasien tidak ditemukan',
                ], 404);
            }

            $kunjunganPayment = Kunjungan::with([
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
                'emr.pembayaran',
                'emr.resep.obat' => function ($query) {
                    $query->select('obat.id', 'obat.nama_obat', 'obat.dosis', 'obat.total_harga')
                        ->withPivot('jumlah', 'dosis', 'keterangan', 'status');
                },
                // FIXED: Load layanan through belongsToMany relationship
                'layanan', // Ini akan load layanan melalui relasi many-to-many
            ])
                ->where('pasien_id', $pasienId)
                ->where('status', 'Payment')
                ->orderBy('tanggal_kunjungan', 'desc')
                ->first();

            if (!$kunjunganPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada pembayaran yang menunggu',
                ], 404);
            }

            Log::info('getPembayaranPasien - Data ditemukan:', [
                'kunjungan_id' => $kunjunganPayment->id,
                'emr_id' => $kunjunganPayment->emr->id ?? null,
                'pembayaran_id' => $kunjunganPayment->emr->pembayaran->id ?? null,
                'layanan_count' => $kunjunganPayment->layanan->count(),
            ]);

            $responseData = [
                'kunjungan_id' => $kunjunganPayment->id,
                'pasien' => [
                    'nama_pasien' => $kunjunganPayment->pasien->nama_pasien ?? 'Tidak ada',
                    'umur' => $this->calculateAge($kunjunganPayment->pasien->tanggal_lahir ?? null),
                    'jenis_kelamin' => $kunjunganPayment->pasien->jenis_kelamin ?? 'Tidak ada',
                    'foto_pasien' => $kunjunganPayment->pasien->foto_pasien,
                ],
                'poli' => [
                    'nama_poli' => $kunjunganPayment->poli->nama_poli ?? 'Umum',
                ],
                'tanggal_kunjungan' => $kunjunganPayment->tanggal_kunjungan,
                'no_antrian' => $kunjunganPayment->no_antrian,
                'diagnosis' => $kunjunganPayment->emr->diagnosis ?? 'Tidak ada diagnosis',
                'layanan' => [],
                'resep_obat' => [],
                'total_layanan' => 0,
                'total_obat' => 0,
                'total_tagihan' => 0,
                'status_pembayaran' => $kunjunganPayment->emr->pembayaran->status ?? 'Belum Bayar',
                'pembayaran_id' => $kunjunganPayment->emr->pembayaran->id ?? null,
            ];

            // FIXED: Process layanan using belongsToMany relationship
            $totalLayanan = 0;
            if ($kunjunganPayment->layanan && $kunjunganPayment->layanan->isNotEmpty()) {
                foreach ($kunjunganPayment->layanan as $layanan) {
                    // Get jumlah from pivot table (kunjungan_layanan)
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

                    Log::info('Processing layanan item:', [
                        'layanan_id' => $layanan->id,
                        'nama' => $layanan->nama_layanan,
                        'harga' => $hargaLayanan,
                        'jumlah' => $jumlah,
                        'subtotal' => $subtotal,
                    ]);
                }
            } else {
                Log::warning('No layanan found for kunjungan_id: ' . $kunjunganPayment->id);
            }

            // Process resep obat
            $totalObat = 0;
            if ($kunjunganPayment->emr && $kunjunganPayment->emr->resep) {
                foreach ($kunjunganPayment->emr->resep->obat as $obat) {
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

            Log::info('Response getPembayaranPasien with layanan:', [
                'kunjungan_id' => $responseData['kunjungan_id'],
                'pembayaran_id' => $responseData['pembayaran_id'],
                'layanan_items' => count($responseData['layanan']),
                'total_layanan' => $responseData['total_layanan'],
                'total_obat' => $responseData['total_obat'],
                'total_tagihan' => $responseData['total_tagihan'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil diambil',
                'data' => $responseData,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error getting pembayaran pasien with layanan: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

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

    public function updateStatusObat(Request $request)
    {
        try {
            $request->validate([
                'resep_id' => 'required|exists:resep,id',
                'obat_id' => 'required|exists:obat,id',
                'status' => 'required|in:Belum Diambil,Sudah Diambil',
            ]);

            $resep = Resep::with(['emr.pembayaran', 'emr.kunjungan'])->findOrFail($request->resep_id);

            if (!$resep->emr || !$resep->emr->pembayaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pembayaran tidak ditemukan',
                ], 404);
            }

            if ($resep->emr->pembayaran->status !== 'Sudah Bayar') {
                return response()->json([
                    'success' => false,
                    'message' => 'Obat hanya bisa diambil setelah pembayaran selesai',
                ], 400);
            }

            DB::table('resep_obat')
                ->where('resep_id', $request->resep_id)
                ->where('obat_id', $request->obat_id)
                ->update([
                    'status' => $request->status,
                    'updated_at' => now(),
                ]);

            $belumDiambil = DB::table('resep_obat')
                ->where('resep_id', $request->resep_id)
                ->where('status', 'Belum Diambil')
                ->count();

            if ($belumDiambil === 0 && $request->status === 'Sudah Diambil') {
                $resep->emr->kunjungan->update([
                    'status' => 'Succeed',
                ]);

                Log::info("Semua obat sudah diambil. Status kunjungan diubah menjadi Succeed", [
                    'kunjungan_id' => $resep->emr->kunjungan->id,
                    'resep_id' => $request->resep_id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Status obat berhasil diupdate',
                'all_taken' => $belumDiambil === 0,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating status obat: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status obat: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function prosesPembayaran(Request $request)
    {
        try {
            $request->validate([
                'metode_pembayaran' => 'required|in:Cash,Transfer',
                'pembayaran_id' => 'nullable|exists:pembayaran,id',
                'kunjungan_id' => 'nullable|exists:kunjungan,id',
            ]);

            Log::info('🔥 PROSES PEMBAYARAN - Request Data:', [
                'pembayaran_id' => $request->pembayaran_id,
                'kunjungan_id' => $request->kunjungan_id,
                'metode_pembayaran' => $request->metode_pembayaran,
                'all_request' => $request->all(),
            ]);

            $pembayaran = null;

            if ($request->filled('pembayaran_id')) {
                $pembayaran = Pembayaran::with(['emr.kunjungan'])->find($request->pembayaran_id);
                Log::info('🔍 Mencari berdasarkan pembayaran_id: ' . $request->pembayaran_id);

                if ($pembayaran) {
                    Log::info('✅ Pembayaran ditemukan:', [
                        'id' => $pembayaran->id,
                        'status' => $pembayaran->status,
                        'total_tagihan' => $pembayaran->total_tagihan,
                    ]);
                } else {
                    Log::warning('❌ Pembayaran TIDAK DITEMUKAN dengan ID: ' . $request->pembayaran_id);
                }
            }

            if (!$pembayaran && $request->filled('kunjungan_id')) {
                Log::info('🔍 Fallback: Mencari berdasarkan kunjungan_id: ' . $request->kunjungan_id);

                $pembayaran = Pembayaran::whereHas('emr', function ($query) use ($request) {
                    $query->where('kunjungan_id', $request->kunjungan_id);
                })->with(['emr.kunjungan'])->first();

                if ($pembayaran) {
                    Log::info('✅ Pembayaran ditemukan via kunjungan_id:', [
                        'pembayaran_id' => $pembayaran->id,
                        'status' => $pembayaran->status,
                    ]);
                } else {
                    Log::warning('❌ Pembayaran TIDAK DITEMUKAN dengan kunjungan_id: ' . $request->kunjungan_id);
                }
            }

            if (!$pembayaran) {
                Log::error('❌ GAGAL: Pembayaran tidak ditemukan dengan parameter yang diberikan');
                return response()->json([
                    'success' => false,
                    'message' => 'Data pembayaran tidak ditemukan untuk kunjungan ini.',
                    'debug' => [
                        'pembayaran_id' => $request->pembayaran_id,
                        'kunjungan_id' => $request->kunjungan_id,
                    ],
                ], 404);
            }

            if ($pembayaran->status === 'Sudah Bayar') {
                Log::warning('⚠️ PEMBAYARAN SUDAH LUNAS sebelumnya', [
                    'pembayaran_id' => $pembayaran->id,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran sudah dilakukan sebelumnya.',
                ], 400);
            }

            DB::transaction(function () use ($pembayaran, $request) {
                Log::info('💰 MEMULAI TRANSAKSI PEMBAYARAN:', [
                    'pembayaran_id' => $pembayaran->id,
                    'metode' => $request->metode_pembayaran,
                    'total_tagihan' => $pembayaran->total_tagihan,
                ]);

                $updateResult = $pembayaran->update([
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'uang_yang_diterima' => $pembayaran->total_tagihan,
                    'kembalian' => 0,
                    'tanggal_pembayaran' => now(),
                    'status' => 'Sudah Bayar',
                ]);

                Log::info('📝 Update pembayaran result: ' . ($updateResult ? 'SUCCESS' : 'FAILED'));

                if ($pembayaran->emr && $pembayaran->emr->kunjungan) {
                    $kunjunganUpdateResult = $pembayaran->emr->kunjungan->update([
                        'status' => 'Succeed'
                    ]);

                    Log::info('📝 Update kunjungan result: ' . ($kunjunganUpdateResult ? 'SUCCESS' : 'FAILED'), [
                        'kunjungan_id' => $pembayaran->emr->kunjungan->id,
                        'new_status' => 'Succeed',
                    ]);
                } else {
                    Log::error('❌ EMR atau Kunjungan tidak ditemukan!');
                }
            });

            $pembayaran->refresh();
            Log::info('✅ PEMBAYARAN SELESAI - Status akhir:', [
                'pembayaran_id' => $pembayaran->id,
                'status' => $pembayaran->status,
                'metode_pembayaran' => $pembayaran->metode_pembayaran,
                'tanggal_pembayaran' => $pembayaran->tanggal_pembayaran,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diproses dan status kunjungan diubah menjadi Succeed.',
                'data' => $pembayaran->fresh(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('❌ ERROR PROSES PEMBAYARAN: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage(),
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

    public function checkout(Request $request)
    {
        try {
            // Konfigurasi Midtrans
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = true;
            Config::$is3ds = true;

            // Data transaksi (bisa ambil dari DB)
            $params = [
                'transaction_details' => [
                    'order_id' => rand(),
                    'gross_amount' => 150000, // nominal transaksi
                ],
                'customer_details' => [
                    'first_name' => 'Budi',
                    'email' => 'budi@example.com',
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            // Kirim ke view
            return view('payment', compact('snapToken'));
        } catch (\Exception $e) {
            Log::error('Checkout error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
            ], 500);
        }
    }

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

    public function createMidtransTransaction(Request $request)
    {
        try {
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = true;
            Config::$is3ds = true;

            $request->validate([
                'pembayaran_id' => 'nullable|exists:pembayaran,id',
                'kunjungan_id' => 'nullable|exists:kunjungan,id',
            ]);

            Log::info('🔥 Creating Midtrans transaction:', [
                'pembayaran_id' => $request->pembayaran_id,
                'kunjungan_id' => $request->kunjungan_id,
            ]);

            // Cari pembayaran
            $pembayaran = null;
            if ($request->filled('pembayaran_id')) {
                $pembayaran = Pembayaran::with([
                    'emr.kunjungan.pasien.user',
                    'emr.kunjungan.layanan', // FIXED: Load layanan
                    'emr.resep.obat'
                ])->find($request->pembayaran_id);
            } elseif ($request->filled('kunjungan_id')) {
                $pembayaran = Pembayaran::whereHas('emr', function ($query) use ($request) {
                    $query->where('kunjungan_id', $request->kunjungan_id);
                })->with([
                    'emr.kunjungan.pasien.user',
                    'emr.kunjungan.layanan', // FIXED: Load layanan
                    'emr.resep.obat'
                ])->first();
            }

            if (!$pembayaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pembayaran tidak ditemukan',
                ], 404);
            }

            if ($pembayaran->status === 'Sudah Bayar') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran sudah selesai',
                ], 400);
            }

            $pasien = $pembayaran->emr->kunjungan->pasien;
            $kunjungan = $pembayaran->emr->kunjungan;

            // Generate order_id
            $orderId = 'KLINIK-' . $pembayaran->id . '-' . time();

            // FIXED: Buat item details dari layanan (bukan hardcode konsultasi)
            $itemDetails = [];

            // Tambahkan layanan dari kunjungan
            if ($kunjungan->layanan && $kunjungan->layanan->isNotEmpty()) {
                foreach ($kunjungan->layanan as $layanan) {
                    $jumlah = (int) $layanan->pivot->jumlah;
                    $hargaLayanan = (int) $layanan->harga_layanan;

                    $itemDetails[] = [
                        'id' => 'layanan-' . $layanan->id,
                        'price' => $hargaLayanan,
                        'quantity' => $jumlah,
                        'name' => $layanan->nama_layanan,
                    ];

                    Log::info('Added layanan to Midtrans items:', [
                        'id' => 'layanan-' . $layanan->id,
                        'name' => $layanan->nama_layanan,
                        'price' => $hargaLayanan,
                        'quantity' => $jumlah,
                        'subtotal' => $hargaLayanan * $jumlah,
                    ]);
                }
            } else {
                // Fallback: Jika tidak ada layanan, gunakan default konsultasi
                $itemDetails[] = [
                    'id' => 'konsultasi-' . $pembayaran->id,
                    'price' => 150000,
                    'quantity' => 1,
                    'name' => 'Konsultasi Dokter',
                ];

                Log::warning('No layanan found, using default consultation fee');
            }

            // Tambahkan obat jika ada
            if ($pembayaran->emr && $pembayaran->emr->resep && $pembayaran->emr->resep->obat) {
                foreach ($pembayaran->emr->resep->obat as $obat) {
                    $jumlah = (int) $obat->pivot->jumlah;
                    $hargaObat = (int) $obat->total_harga;

                    $itemDetails[] = [
                        'id' => 'obat-' . $obat->id,
                        'price' => $hargaObat,
                        'quantity' => $jumlah,
                        'name' => $obat->nama_obat,
                    ];

                    Log::info('Added obat to Midtrans items:', [
                        'id' => 'obat-' . $obat->id,
                        'name' => $obat->nama_obat,
                        'price' => $hargaObat,
                        'quantity' => $jumlah,
                        'subtotal' => $hargaObat * $jumlah,
                    ]);
                }
            }

            // Hitung total untuk validasi
            $calculatedTotal = 0;
            foreach ($itemDetails as $item) {
                $calculatedTotal += ($item['price'] * $item['quantity']);
            }

            Log::info('Midtrans transaction totals:', [
                'calculated_total' => $calculatedTotal,
                'pembayaran_total_tagihan' => $pembayaran->total_tagihan,
                'item_details_count' => count($itemDetails),
            ]);

            // Parameter Midtrans
            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $pembayaran->total_tagihan, // Gunakan dari database
                ],
                'customer_details' => [
                    'first_name' => $pasien->nama_pasien ?? 'Pasien',
                    'last_name' => 'Klinik',
                    'email' => $pasien->user->email ?? 'pasien@klinik.com',
                    'phone' => '08123456789',
                ],
                'item_details' => $itemDetails,
            ];

            Log::info('📋 Midtrans params:', $params);

            // Generate Snap Token
            $snapToken = Snap::getSnapToken($params);

            // Update pembayaran
            $pembayaran->update([
                'metode_pembayaran' => 'Midtrans',
            ]);

            // Simpan order_id dalam cache
            Cache::put('midtrans_order_' . $orderId, $pembayaran->id, now()->addHours(24));

            Log::info('✅ Midtrans token generated successfully:', [
                'order_id' => $orderId,
                'pembayaran_id' => $pembayaran->id,
                'snap_token' => substr($snapToken, 0, 20) . '...',
                'gross_amount' => $pembayaran->total_tagihan,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token Midtrans berhasil dibuat',
                'data' => [
                    'snap_token' => $snapToken,
                    'order_id' => $orderId,
                    'amount' => $pembayaran->total_tagihan,
                    'client_key' => config('midtrans.client_key'),
                    'is_sandbox' => !config('midtrans.is_production'),
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Validation error: ', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Error creating Midtrans transaction: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat transaksi Midtrans: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function midtransCallback(Request $request)
    {
        try {
            Log::info('🔔 Midtrans Sandbox callback received:', $request->all());

            // Menggunakan Midtrans Notification SDK
            $notification = new Notification();

            $transactionStatus = $notification->transaction_status;
            $orderId = $notification->order_id;
            $fraudStatus = isset($notification->fraud_status) ? $notification->fraud_status : 'accept';

            Log::info('Processing callback:', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
            ]);

            // Cari pembayaran berdasarkan order_id dari cache atau parsing
            $pembayaranId = Cache::get('midtrans_order_' . $orderId);

            if (!$pembayaranId) {
                // Fallback: parse order_id untuk mendapatkan pembayaran_id
                if (preg_match('/KLINIK-(\d+)-\d+/', $orderId, $matches)) {
                    $pembayaranId = $matches[1];
                    Log::info('📋 Pembayaran ID parsed from order_id: ' . $pembayaranId);
                }
            }

            if (!$pembayaranId) {
                Log::error('❌ Cannot find pembayaran for order_id: ' . $orderId);
                return response()->json(['status' => 'error', 'message' => 'Pembayaran not found'], 404);
            }

            $pembayaran = Pembayaran::with(['emr.kunjungan', 'emr.resep.obat'])->find($pembayaranId);

            if (!$pembayaran) {
                Log::error('❌ Pembayaran not found in DB: ' . $pembayaranId);
                return response()->json(['status' => 'error', 'message' => 'Pembayaran record not found'], 404);
            }

            Log::info('✅ Found pembayaran:', [
                'pembayaran_id' => $pembayaran->id,
                'current_status' => $pembayaran->status,
                'emr_id' => $pembayaran->emr_id,
                'kunjungan_id' => $pembayaran->emr->kunjungan->id ?? null,
            ]);

            DB::transaction(function () use ($pembayaran, $transactionStatus, $fraudStatus, $orderId) {
                if ($transactionStatus == 'capture') {
                    if ($fraudStatus == 'challenge') {
                        Log::info('⚠️ Payment challenge - waiting verification');
                        $this->updatePembayaranStatus($pembayaran, 'Pending', $orderId);
                    } else if ($fraudStatus == 'accept') {
                        Log::info('✅ Payment capture accepted');
                        $this->completeMidtransPayment($pembayaran, $orderId);
                    }
                } else if ($transactionStatus == 'settlement') {
                    Log::info('✅ Payment settlement - completing payment');
                    $this->completeMidtransPayment($pembayaran, $orderId);
                } else if ($transactionStatus == 'pending') {
                    Log::info('⏳ Payment pending');
                    $this->updatePembayaranStatus($pembayaran, 'Belum Bayar', $orderId);
                } else if (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                    Log::error('❌ Payment failed: ' . $transactionStatus);
                    $this->updatePembayaranStatus($pembayaran, 'Belum Bayar', $orderId);
                }
            });

            Log::info('✅ Callback processed successfully');
            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('❌ Midtrans callback error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function completeMidtransPayment($pembayaran, $orderId)
    {
        Log::info('💰 Completing Midtrans payment:', [
            'pembayaran_id' => $pembayaran->id,
            'order_id' => $orderId,
            'before_status' => $pembayaran->status,
        ]);

        // Update pembayaran
        $updateResult = $pembayaran->update([
            'status' => 'Sudah Bayar',
            'metode_pembayaran' => 'Midtrans',
            'tanggal_pembayaran' => now(),
            'uang_yang_diterima' => $pembayaran->total_tagihan,
            'kembalian' => 0,
        ]);

        Log::info('📝 Pembayaran update result: ' . ($updateResult ? 'SUCCESS' : 'FAILED'));

        // Update kunjungan status
        if ($pembayaran->emr && $pembayaran->emr->kunjungan) {
            $kunjunganUpdateResult = $pembayaran->emr->kunjungan->update([
                'status' => 'Succeed'
            ]);

            Log::info('📝 Kunjungan update result: ' . ($kunjunganUpdateResult ? 'SUCCESS' : 'FAILED'), [
                'kunjungan_id' => $pembayaran->emr->kunjungan->id,
                'new_status' => 'Succeed',
            ]);
        }

        // Update status resep obat otomatis ke "Sudah Diambil"
        if ($pembayaran->emr && $pembayaran->emr->resep) {
            DB::table('resep_obat')
                ->where('resep_id', $pembayaran->emr->resep->id)
                ->update([
                    'status' => 'Sudah Diambil',
                    'updated_at' => now(),
                ]);

            Log::info('💊 Resep obat updated to Sudah Diambil');
        }

        // Hapus dari cache
        Cache::forget('midtrans_order_' . $orderId);

        Log::info('✅ Payment completion successful', [
            'pembayaran_id' => $pembayaran->id,
            'status' => 'Sudah Bayar',
            'kunjungan_status' => 'Succeed',
        ]);
    }

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

    public function checkPaymentStatus(Request $request, $orderId)
    {
        try {
            // Cari pembayaran berdasarkan order_id
            $pembayaranId = Cache::get('midtrans_order_' . $orderId);

            if (!$pembayaranId && preg_match('/KLINIK-(\d+)-\d+/', $orderId, $matches)) {
                $pembayaranId = $matches[1];
            }

            if (!$pembayaranId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan',
                ], 404);
            }

            $pembayaran = Pembayaran::find($pembayaranId);

            if (!$pembayaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $orderId,
                    'status' => $pembayaran->status,
                    'amount' => $pembayaran->total_tagihan,
                    'payment_method' => $pembayaran->metode_pembayaran,
                    'paid_at' => $pembayaran->tanggal_pembayaran,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking payment status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengecek status: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function forceUpdatePaymentStatus(Request $request)
    {
        try {
            $request->validate([
                'pembayaran_id' => 'required|exists:pembayaran,id',
                'metode_pembayaran' => 'required|in:Cash,Midtrans',
            ]);

            Log::info('🚨 Force update payment status requested:', [
                'pembayaran_id' => $request->pembayaran_id,
                'metode_pembayaran' => $request->metode_pembayaran,
            ]);

            $pembayaran = Pembayaran::with(['emr.kunjungan', 'emr.resep.obat'])->find($request->pembayaran_id);

            if (!$pembayaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pembayaran tidak ditemukan',
                ], 404);
            }

            if ($pembayaran->status === 'Sudah Bayar') {
                Log::info('⚠️ Payment already paid:', [
                    'pembayaran_id' => $pembayaran->id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Pembayaran sudah selesai sebelumnya',
                    'data' => $pembayaran,
                ]);
            }

            // Force update pembayaran
            DB::transaction(function () use ($pembayaran, $request) {
                Log::info('💪 Force updating payment:', [
                    'pembayaran_id' => $pembayaran->id,
                    'metode' => $request->metode_pembayaran,
                    'total_tagihan' => $pembayaran->total_tagihan,
                ]);

                $updateResult = $pembayaran->update([
                    'status' => 'Sudah Bayar',
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'tanggal_pembayaran' => now(),
                    'uang_yang_diterima' => $pembayaran->total_tagihan,
                    'kembalian' => 0,
                    'catatan' => 'Force update - pembayaran dikonfirmasi manual',
                ]);

                Log::info('📝 Force update payment result: ' . ($updateResult ? 'SUCCESS' : 'FAILED'));

                // Update kunjungan status
                if ($pembayaran->emr && $pembayaran->emr->kunjungan) {
                    $kunjunganUpdateResult = $pembayaran->emr->kunjungan->update([
                        'status' => 'Succeed'
                    ]);

                    Log::info('📝 Force update kunjungan result: ' . ($kunjunganUpdateResult ? 'SUCCESS' : 'FAILED'), [
                        'kunjungan_id' => $pembayaran->emr->kunjungan->id,
                        'new_status' => 'Succeed',
                    ]);
                }

                // Update status resep obat otomatis ke "Sudah Diambil" jika Midtrans
                if ($request->metode_pembayaran === 'Midtrans' && $pembayaran->emr && $pembayaran->emr->resep) {
                    DB::table('resep_obat')
                        ->where('resep_id', $pembayaran->emr->resep->id)
                        ->update([
                            'status' => 'Sudah Diambil',
                            'updated_at' => now(),
                        ]);

                    Log::info('💊 Force update resep obat to Sudah Diambil');
                }
            });

            $pembayaran->refresh();

            Log::info('✅ Force update completed successfully:', [
                'pembayaran_id' => $pembayaran->id,
                'status' => $pembayaran->status,
                'metode_pembayaran' => $pembayaran->metode_pembayaran,
                'tanggal_pembayaran' => $pembayaran->tanggal_pembayaran,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status pembayaran berhasil diperbarui',
                'data' => $pembayaran,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Force update validation error: ', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Force update error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function simulateMidtransCallback(Request $request)
    {
        try {
            // HANYA untuk SANDBOX mode
            if (config('midtrans.is_production')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Endpoint hanya tersedia di sandbox mode',
                ], 403);
            }

            $request->validate([
                'order_id' => 'required|string',
                'transaction_status' => 'required|in:settlement,capture,pending,deny,cancel,expire',
            ]);

            $orderId = $request->order_id;
            $transactionStatus = $request->transaction_status;

            Log::info('🧪 Simulating Midtrans callback:', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
            ]);

            // Cari pembayaran berdasarkan order_id
            $pembayaranId = Cache::get('midtrans_order_' . $orderId);

            if (!$pembayaranId && preg_match('/KLINIK-(\d+)-\d+/', $orderId, $matches)) {
                $pembayaranId = $matches[1];
            }

            if (!$pembayaranId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order ID tidak ditemukan',
                ], 404);
            }

            $pembayaran = Pembayaran::with(['emr.kunjungan', 'emr.resep.obat'])->find($pembayaranId);

            if (!$pembayaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran tidak ditemukan',
                ], 404);
            }

            // Simulasi proses callback
            if (in_array($transactionStatus, ['settlement', 'capture'])) {
                $this->completeMidtransPayment($pembayaran, $orderId);

                return response()->json([
                    'success' => true,
                    'message' => 'Callback berhasil disimulasi - pembayaran selesai',
                    'data' => $pembayaran->fresh(),
                ]);
            } else {
                $this->updatePembayaranStatus($pembayaran, 'Belum Bayar', $orderId);

                return response()->json([
                    'success' => true,
                    'message' => 'Callback berhasil disimulasi - status: ' . $transactionStatus,
                    'data' => $pembayaran->fresh(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('❌ Simulate callback error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mensimulasi callback: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cek apakah pembayaran sudah expired dan perlu dibatalkan
     */
    public function checkExpiredPayments()
    {
        try {
            // Cari pembayaran yang belum dibayar dan sudah lebih dari 30 menit
            $expiredPayments = Pembayaran::where('status', 'Belum Bayar')
                ->where('metode_pembayaran', 'Midtrans')
                ->where('created_at', '<', now()->subMinutes(30))
                ->with(['emr.kunjungan'])
                ->get();

            Log::info('🕐 Checking expired payments, found: ' . $expiredPayments->count());

            $expiredCount = 0;
            foreach ($expiredPayments as $pembayaran) {
                // Update status kunjungan kembali ke Payment jika diperlukan
                if ($pembayaran->emr && $pembayaran->emr->kunjungan && $pembayaran->emr->kunjungan->status === 'Succeed') {
                    $pembayaran->emr->kunjungan->update(['status' => 'Payment']);
                    Log::info('⏰ Reset kunjungan status for expired payment: ' . $pembayaran->id);
                    $expiredCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Ditemukan {$expiredCount} pembayaran yang expired",
                'expired_count' => $expiredCount,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Check expired payments error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengecek expired payments: ' . $e->getMessage(),
            ], 500);
        }
    }

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
                                'jam_awal' => $item->jam_awal,
                                'jam_selesai' => $item->jam_selesai
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
}
