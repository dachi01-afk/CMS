<?php

namespace App\Http\Controllers\Api;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\DentalExamination;
use App\Models\Dokter;
use App\Models\EMR;
use App\Models\EmrKklp;
use App\Models\EmrKklpAnggotaKeluarga;
use App\Models\EmrKklpCopcPlan;
use App\Models\EmrKklpEkstremitas;
use App\Models\EmrKklpFamilyApgar;
use App\Models\EmrKklpFamilyPlan;
use App\Models\EmrKklpFamilyScreem;
use App\Models\EmrKklpHeteroanamnesis;
use App\Models\EmrKklpHomevisit;
use App\Models\EmrKklpOrangtua;
use App\Models\EmrPengkajianAwalPenyakitDalam;
use App\Models\HasilLab;
use App\Models\JadwalDokter;
use App\Models\KategoriLayanan;
use App\Models\Kunjungan;
use App\Models\KunjunganLayanan;
use App\Models\Layanan;
use App\Models\MetodePembayaran;
use App\Models\Obat;
use App\Models\OrderLab;
use App\Models\OrderLayanan;
use App\Models\OrderLayananDetail;
use App\Models\Pasien;
use App\Models\Pembayaran;
use App\Models\PenjualanLayanan;
use App\Models\PenjualanObat;
use App\Models\Perawat;
use App\Models\Poli;
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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
    /**
     * Save FCM Token dari Flutter
     */
    public function saveFCMToken(Request $request)
    {
        try {
            $request->validate([
                'fcm_token' => 'required|string|max:500',
            ]);

            $user = $request->user();

            $user->update([
                'fcm_token' => $request->fcm_token,
            ]);

            Log::info('✅ FCM Token saved', [
                'user_id' => $user->id,
                'token' => substr($request->fcm_token, 0, 20).'...',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FCM Token berhasil disimpan',
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error saving FCM token: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan FCM token',
            ], 500);
        }
    }

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
                'password.min' => 'Password minimal 6 karakter.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
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
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Login error: '.$e->getMessage());

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
            'username' => 'required|string|min:3|unique:user,username',
            'email' => 'required|email|unique:user,email',
            'password' => 'required|string|min:6',
        ], [
            'username.required' => 'Username tidak boleh kosong.',
            'username.min' => 'Username minimal 3 karakter.',
            'username.unique' => 'Username sudah digunakan.',
            'email.required' => 'Email tidak boleh kosong.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password tidak boleh kosong.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                // ✅ GENERATE NO_EMR DENGAN PREFIX RM (Mobile)
                $lastPasien = Pasien::where('no_emr', 'LIKE', 'RM-%')
                    ->orderBy('id', 'desc')
                    ->first();

                $lastNumber = 0;
                if ($lastPasien && preg_match('/RM-(\d+)/', $lastPasien->no_emr, $matches)) {
                    $lastNumber = (int) $matches[1];
                }

                $nextNumber = $lastNumber + 1;
                $no_emr = 'RM-'.str_pad($nextNumber, 8, '0', STR_PAD_LEFT);

                Log::info('Generating EMR for mobile registration:', [
                    'no_emr' => $no_emr,
                    'last_number' => $lastNumber,
                    'next_number' => $nextNumber,
                ]);

                // Create user
                $user = User::create([
                    'username' => $request->username,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => 'Pasien',
                ]);

                // Create pasien WITH no_emr
                $pasien = Pasien::create([
                    'user_id' => $user->id,
                    'no_emr' => $no_emr,  // ✅ LANGSUNG SET
                    'nama_pasien' => null,
                    'alamat' => null,
                    'tanggal_lahir' => null,
                    'jenis_kelamin' => null,
                ]);

                Log::info('Mobile registration successful:', [
                    'user_id' => $user->id,
                    'pasien_id' => $pasien->id,
                    'no_emr' => $no_emr,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Registrasi berhasil',
                    'data' => [
                        'user' => $user,
                        'pasien' => $pasien,
                        'no_emr' => $no_emr,
                    ],
                ], 201);
            });
        } catch (\Throwable $e) {
            Log::error('Register error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if ($user) {
                // Hapus semua token user
                $user->tokens()->delete();

                Log::info('User logged out successfully', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil',
            ]);

        } catch (\Exception $e) {
            Log::error('Logout error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat logout',
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

    // publik
    public function getPoliDokter()
    {
        try {
            $data = Poli::all(); // ambil semua data poli dari tabel

            return response()->json([
                'success' => true,
                'message' => 'Data poli berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting poli dokter: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getPolibyIdDokter($dokter_id)
    {
        try {
            // Ambil dokter beserta poli dan jadwalnya
            $dokter = Dokter::with(['poli', 'jadwalDokter'])->find($dokter_id);

            if (! $dokter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokter tidak ditemukan',
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
                        'nama_poli' => $dokter->poli->nama_poli,
                    ],
                    'jadwal' => $dokter->jadwalDokter->map(function ($item) {
                        return [
                            'hari' => $item->hari,
                            'jam_awal' => $item->jam_awal,
                            'jam_selesai' => $item->jam_selesai,
                        ];
                    }),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting poli by dokter ID: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getAllDokter()
    {
        try {
            // Ambil semua kombinasi dokter-poli dari pivot table
            $dokterPoliList = DB::table('dokter_poli as dp')
                ->join('dokter as d', 'd.id', '=', 'dp.dokter_id')
                ->join('poli as p', 'p.id', '=', 'dp.poli_id')
                ->leftJoin('jenis_spesialis as js', 'js.id', '=', 'd.jenis_spesialis_id')
                ->select(
                    'd.id as dokter_id',
                    'd.nama_dokter',
                    'd.foto_dokter',
                    'd.no_hp',
                    'dp.id as dokter_poli_id', // ✅ TAMBAHKAN INI - ID UNIK PER KOMBINASI
                    'p.id as poli_id',
                    'p.nama_poli',
                    'js.id as spesialis_id',
                    'js.nama_spesialis'
                )
                ->orderBy('d.nama_dokter')
                ->orderBy('p.nama_poli')
                ->get();

            $data = $dokterPoliList->map(function ($item) {
                // Ambil jadwal spesifik untuk kombinasi dokter + poli ini
                $jadwal = DB::table('jadwal_dokter')
                    ->where('dokter_id', $item->dokter_id)
                    ->where('poli_id', $item->poli_id) // ✅ FILTER BY POLI
                    ->select('id', 'hari', 'jam_awal', 'jam_selesai')
                    ->orderBy('hari')
                    ->get();

                return [
                    'id' => $item->dokter_id,
                    'dokter_poli_id' => $item->dokter_poli_id, // ✅ ID UNIK
                    'nama_dokter' => $item->nama_dokter,
                    'foto_dokter' => $item->foto_dokter,
                    'no_hp' => $item->no_hp,
                    'poli' => [
                        'id' => $item->poli_id,
                        'nama_poli' => $item->nama_poli,
                    ],
                    'spesialis' => $item->spesialis_id ? [
                        'id' => $item->spesialis_id,
                        'nama_spesialis' => $item->nama_spesialis,
                    ] : null,
                    'jadwal' => $jadwal->map(function ($j) {
                        return [
                            'id' => $j->id,
                            'hari' => $j->hari,
                            'jam_awal' => $j->jam_awal,
                            'jam_selesai' => $j->jam_selesai,
                        ];
                    })->values(),
                ];
            })->values();

            return response()->json([
                'success' => true,
                'message' => 'Data seluruh dokter berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error getAllDokter: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
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
            log::error('Error getting jadwal dokter: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil jadwal dokter: '.$e->getMessage(),
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
            Log::error('Error getting spesialisasi dokter: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data spesialisasi: '.$e->getMessage(),
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
            Log::error('Error getting dokter by spesialisasi: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dokter: '.$e->getMessage(),
            ], 500);
        }
    }

    public function dokterMasterRadiologi()
    {
        $rows = DB::table('jenis_pemeriksaan_radiologi')
            ->where('status', 'Active') // hanya yang aktif
            ->select(
                'id',
                'kode_pemeriksaan',
                'nama_pemeriksaan',
                'harga_pemeriksaan_radiologi'
            )
            ->orderBy('nama_pemeriksaan')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'nama' => $r->nama_pemeriksaan, // WAJIB "nama" (konsisten dengan lab)
                    'kode_pemeriksaan' => $r->kode_pemeriksaan,
                    'harga' => $r->harga_pemeriksaan_radiologi,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    /**
     * ✅ DOKTER - Create Order Radiologi (sama seperti order lab)
     * POST /api/dokter/order-radiologi
     *
     * Body:
     * {
     *   "pasien_id": 1,
     *   "kunjungan_id": 10,        // opsional, kalau ada kunjungan
     *   "dokter_id": 5,             // auto dari auth
     *   "tanggal_pemeriksaan": "2024-01-29",
     *   "jam_pemeriksaan": "14:30",
     *   "jenis_pemeriksaan_radiologi_ids": [1, 2, 3]  // array ID pemeriksaan
     * }
     */
    public function dokterCreateOrderRadiologi(Request $request)
    {
        $data = $request->all();

        $user = $request->user();
        $dokter = \App\Models\Dokter::where('user_id', $user->id)->first();
        if (! $dokter) {
            return response()->json([
                'success' => false,
                'message' => 'Dokter tidak ditemukan untuk user ini.',
            ], 404);
        }

        $data['dokter_id'] = $dokter->id;

        if (! empty($data['kunjungan_id'])) {
            $k = DB::table('kunjungan')->where('id', $data['kunjungan_id'])->first();
            if ($k && empty($data['pasien_id'])) {
                $data['pasien_id'] = (int) $k->pasien_id;
            }
        }

        $jenisIds = [];

        if (isset($data['jenis_pemeriksaan_radiologi_ids']) && is_array($data['jenis_pemeriksaan_radiologi_ids'])) {
            foreach ($data['jenis_pemeriksaan_radiologi_ids'] as $v) {
                if (is_numeric($v)) {
                    $jenisIds[] = (int) $v;
                } elseif (is_string($v) && preg_match('/(\d+)/', $v, $m)) {
                    $jenisIds[] = (int) $m[1];
                }
            }
        }

        if (empty($jenisIds) && isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $it) {
                if (is_numeric($it)) {
                    $jenisIds[] = (int) $it;

                    continue;
                }

                if (is_array($it)) {
                    $raw = $it['id']
                        ?? $it['radiologi_test_id']
                        ?? $it['jenis_pemeriksaan_radiologi_id']
                        ?? $it['value']
                        ?? null;

                    if ($raw === null) {
                        continue;
                    }

                    if (is_numeric($raw)) {
                        $jenisIds[] = (int) $raw;
                    } elseif (is_string($raw) && preg_match('/(\d+)/', $raw, $m)) {
                        $jenisIds[] = (int) $m[1];
                    }
                }
            }
        }

        $data['jenis_pemeriksaan_radiologi_ids'] = array_values(array_unique(array_filter($jenisIds, fn ($x) => (int) $x > 0)));

        // ❌ HAPUS AUTO-FILL tanggal & jam

        $v = Validator::make($data, [
            'dokter_id' => 'required|exists:dokter,id',
            'pasien_id' => 'required|exists:pasien,id',
            'jenis_pemeriksaan_radiologi_ids' => 'required|array|min:1',
            'jenis_pemeriksaan_radiologi_ids.*' => 'required|exists:jenis_pemeriksaan_radiologi,id',
            // ✅ TIDAK ADA validasi tanggal_pemeriksaan & jam_pemeriksaan
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $v->errors(),
            ], 422);
        }

        return DB::transaction(function () use ($data) {

            $no = 'RAD-'.date('Ymd').'-'.strtoupper(Str::random(6));

            // ✅ FIX: tanggal_pemeriksaan & jam_pemeriksaan = NULL
            $orderId = DB::table('order_radiologi')->insertGetId([
                'no_order_radiologi' => $no,
                'dokter_id' => (int) $data['dokter_id'],
                'pasien_id' => (int) $data['pasien_id'],
                'tanggal_order' => now()->toDateString(),
                'tanggal_pemeriksaan' => null, // ✅ NULL - ISI NANTI VIA FLUTTER
                'jam_pemeriksaan' => null,     // ✅ NULL - ISI NANTI VIA FLUTTER
                'status' => 'Pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $details = [];
            foreach ($data['jenis_pemeriksaan_radiologi_ids'] as $jpId) {
                $details[] = [
                    'order_radiologi_id' => $orderId,
                    'jenis_pemeriksaan_radiologi_id' => (int) $jpId,
                    'status_pemeriksaan' => 'Pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('order_radiologi_detail')->insert($details);

            $order = DB::table('order_radiologi')->where('id', $orderId)->first();

            $detailRows = DB::table('order_radiologi_detail as d')
                ->join('jenis_pemeriksaan_radiologi as j', 'j.id', '=', 'd.jenis_pemeriksaan_radiologi_id')
                ->where('d.order_radiologi_id', $orderId)
                ->select(
                    'd.id as order_radiologi_detail_id',
                    'd.status_pemeriksaan',
                    'j.id as jenis_id',
                    'j.kode_pemeriksaan',
                    'j.nama_pemeriksaan',
                    'j.deskripsi',
                    'j.harga_pemeriksaan_radiologi'
                )
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Order radiologi berhasil dibuat. Jadwal pemeriksaan akan ditentukan nanti.',
                'data' => [
                    'order' => $order,
                    'details' => $detailRows,
                ],
            ]);
        });
    }

    /**
     * ✅ DOKTER - List order radiologi milik dokter yang login
     * GET /api/dokter/order-radiologi
     */
    public function dokterListOrderRadiologi(Request $request)
    {
        $user = $request->user();

        $dokter = \App\Models\Dokter::where('user_id', $user->id)->first();
        if (! $dokter) {
            return response()->json([
                'success' => false,
                'message' => 'Dokter tidak ditemukan untuk user ini.',
            ], 404);
        }

        $data = DB::table('order_radiologi as o')
            ->where('o.dokter_id', $dokter->id)
            ->leftJoin('pasien as p', 'p.id', '=', 'o.pasien_id')
            ->select(
                'o.id',
                'o.no_order_radiologi',
                'o.tanggal_order',
                'o.tanggal_pemeriksaan',
                'o.jam_pemeriksaan',
                'o.status',
                'p.nama_pasien',
                'p.no_emr'
            )
            ->orderByDesc('o.id')
            ->get()
            ->map(function ($o) {
                // Hitung progress
                $total = DB::table('order_radiologi_detail')
                    ->where('order_radiologi_id', $o->id)
                    ->count();

                $done = DB::table('order_radiologi_detail')
                    ->where('order_radiologi_id', $o->id)
                    ->where('status_pemeriksaan', 'Selesai')
                    ->count();

                return [
                    'id' => $o->id,
                    'no_order_radiologi' => $o->no_order_radiologi,
                    'tanggal_order' => $o->tanggal_order,
                    'tanggal_pemeriksaan' => $o->tanggal_pemeriksaan,
                    'jam_pemeriksaan' => $o->jam_pemeriksaan,
                    'status' => $o->status,
                    'progress' => [
                        'total' => $total,
                        'selesai' => $done,
                    ],
                    'pasien' => [
                        'nama_pasien' => $o->nama_pasien,
                        'no_emr' => $o->no_emr,
                    ],
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * ✅ DOKTER - Detail order radiologi (lihat hasil jika sudah ada)
     * GET /api/dokter/order-radiologi/{id}
     */
    public function dokterDetailOrderRadiologi(Request $request, $orderRadiologiId)
    {
        $user = $request->user();

        $dokter = \App\Models\Dokter::where('user_id', $user->id)->first();
        if (! $dokter) {
            return response()->json([
                'success' => false,
                'message' => 'Dokter tidak ditemukan untuk user ini.',
            ], 404);
        }

        $order = DB::table('order_radiologi as o')
            ->where('o.id', $orderRadiologiId)
            ->where('o.dokter_id', $dokter->id)
            ->leftJoin('pasien as p', 'p.id', '=', 'o.pasien_id')
            ->leftJoin('dokter as d', 'd.id', '=', 'o.dokter_id')
            ->select(
                'o.*',
                'p.nama_pasien',
                'p.no_emr',
                'p.jenis_kelamin',
                'p.tanggal_lahir',
                'd.nama_dokter'
            )
            ->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order radiologi tidak ditemukan / bukan milik dokter ini.',
            ], 404);
        }

        // Ambil detail + hasil (jika ada)
        $details = DB::table('order_radiologi_detail as d')
            ->join('jenis_pemeriksaan_radiologi as j', 'j.id', '=', 'd.jenis_pemeriksaan_radiologi_id')
            ->leftJoin('hasil_radiologi as h', 'h.order_radiologi_detail_id', '=', 'd.id')
            ->leftJoin('perawat as pr', 'pr.id', '=', 'h.perawat_id')
            ->leftJoin('dokter as dr', 'dr.id', '=', 'h.dokter_radiologi_id')
            ->where('d.order_radiologi_id', $orderRadiologiId)
            ->select(
                'd.id as order_radiologi_detail_id',
                'd.status_pemeriksaan',

                'j.id as jenis_id',
                'j.kode_pemeriksaan',
                'j.nama_pemeriksaan',
                'j.deskripsi',
                'j.harga_pemeriksaan_radiologi',

                'h.id as hasil_id',
                'h.foto_hasil_radiologi',
                'h.keterangan as interpretasi',
                'h.tanggal_pemeriksaan',
                'h.jam_pemeriksaan',

                'pr.nama_perawat as radiografer_nama',
                'dr.nama_dokter as dokter_radiologi_nama'
            )
            ->get()
            ->map(function ($d) {
                return [
                    'order_radiologi_detail_id' => $d->order_radiologi_detail_id,
                    'status_pemeriksaan' => $d->status_pemeriksaan,

                    'pemeriksaan' => [
                        'id' => $d->jenis_id,
                        'kode_pemeriksaan' => $d->kode_pemeriksaan,
                        'nama_pemeriksaan' => $d->nama_pemeriksaan,
                        'deskripsi' => $d->deskripsi,
                        'harga' => $d->harga_pemeriksaan_radiologi,
                    ],

                    'hasil' => $d->hasil_id ? [
                        'id' => $d->hasil_id,
                        'foto_url' => $d->foto_hasil_radiologi
                            ? asset('storage/'.$d->foto_hasil_radiologi)
                            : null,
                        'interpretasi' => $d->interpretasi,
                        'tanggal_pemeriksaan' => $d->tanggal_pemeriksaan,
                        'jam_pemeriksaan' => $d->jam_pemeriksaan,
                        'radiografer' => $d->radiografer_nama,
                        'dokter_radiologi' => $d->dokter_radiologi_nama,
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'no_order_radiologi' => $order->no_order_radiologi,
                'tanggal_order' => $order->tanggal_order,
                'tanggal_pemeriksaan' => $order->tanggal_pemeriksaan,
                'jam_pemeriksaan' => $order->jam_pemeriksaan,
                'status' => $order->status,
                'dokter' => [
                    'nama_dokter' => $order->nama_dokter,
                ],
                'pasien' => [
                    'nama_pasien' => $order->nama_pasien,
                    'no_emr' => $order->no_emr,
                    'jenis_kelamin' => $order->jenis_kelamin,
                    'tanggal_lahir' => $order->tanggal_lahir,
                ],
                'detail' => $details,
            ],
        ]);
    }

    public function dokterDetailOrderLab(Request $request, $orderLabId)
    {
        $user = $request->user();

        $dokter = \App\Models\Dokter::where('user_id', $user->id)->first();
        if (! $dokter) {
            return response()->json([
                'success' => false,
                'message' => 'Dokter tidak ditemukan untuk user ini.',
            ], 404);
        }

        $order = OrderLab::with([
            'pasien:id,nama_pasien,no_emr,jenis_kelamin,tanggal_lahir',
            'dokter:id,nama_dokter',
            'orderLabDetail.jenisPemeriksaanLab:id,kode_pemeriksaan,nama_pemeriksaan,nilai_normal,satuan_lab_id',
            'orderLabDetail.jenisPemeriksaanLab.satuanLab:id,nama_satuan',
        ])
            ->where('id', $orderLabId)
            ->where('dokter_id', $dokter->id)
            ->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order lab tidak ditemukan / bukan milik dokter ini.',
            ], 404);
        }

        // ambil hasil lab untuk semua detail
        $detailIds = $order->orderLabDetail->pluck('id')->toArray();
        $hasilMap = HasilLab::whereIn('order_lab_detail_id', $detailIds)
            ->get()
            ->keyBy('order_lab_detail_id');

        $details = $order->orderLabDetail->map(function ($d) use ($hasilMap) {
            $jp = $d->jenisPemeriksaanLab;
            $hasil = $hasilMap->get($d->id);

            return [
                'order_lab_detail_id' => $d->id,
                'status_pemeriksaan' => $d->status_pemeriksaan,

                'pemeriksaan' => $jp ? [
                    'id' => $jp->id,
                    'kode_pemeriksaan' => $jp->kode_pemeriksaan,
                    'nama_pemeriksaan' => $jp->nama_pemeriksaan,
                    'nilai_normal' => $jp->nilai_normal,
                    'satuan' => $jp->satuanLab?->nama_satuan,
                ] : null,

                'hasil' => $hasil ? [
                    'id' => $hasil->id,
                    'nilai_hasil' => $hasil->nilai_hasil,
                    'nilai_rujukan' => $hasil->nilai_rujukan,
                    'keterangan' => $hasil->catatan,
                    'tanggal_pemeriksaan' => $hasil->tanggal_pemeriksaan,
                    'jam_pemeriksaan' => $hasil->jam_pemeriksaan,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'no_order_lab' => $order->no_order_lab,
                'tanggal_order' => $order->tanggal_order,
                'tanggal_pemeriksaan' => $order->tanggal_pemeriksaan,
                'jam_pemeriksaan' => $order->jam_pemeriksaan,
                'status' => $order->status,
                'dokter' => $order->dokter ? [
                    'id' => $order->dokter->id,
                    'nama_dokter' => $order->dokter->nama_dokter,
                ] : null,
                'pasien' => $order->pasien ? [
                    'id' => $order->pasien->id,
                    'nama_pasien' => $order->pasien->nama_pasien,
                    'no_emr' => $order->pasien->no_emr,
                    'jenis_kelamin' => $order->pasien->jenis_kelamin,
                    'tanggal_lahir' => $order->pasien->tanggal_lahir,
                ] : null,
                'detail' => $details,
            ],
        ]);
    }

    public function dokterListOrderLab(Request $request)
    {
        $user = $request->user();

        // ambil dokter berdasarkan user login
        $dokter = \App\Models\Dokter::where('user_id', $user->id)->first();
        if (! $dokter) {
            return response()->json([
                'success' => false,
                'message' => 'Dokter tidak ditemukan untuk user ini.',
            ], 404);
        }

        $data = OrderLab::with([
            'pasien:id,nama_pasien,no_emr',
            'orderLabDetail.jenisPemeriksaanLab:id,kode_pemeriksaan,nama_pemeriksaan,satuan_lab_id',
            'orderLabDetail.jenisPemeriksaanLab.satuanLab:id,nama_satuan',
        ])
            ->where('dokter_id', $dokter->id)
            ->orderByDesc('tanggal_order')
            ->orderByDesc('id')
            ->get()
            ->map(function ($o) {
                $total = $o->orderLabDetail->count();
                $done = $o->orderLabDetail->where('status_pemeriksaan', 'Selesai')->count();

                return [
                    'id' => $o->id,
                    'no_order_lab' => $o->no_order_lab,
                    'tanggal_order' => $o->tanggal_order,
                    'tanggal_pemeriksaan' => $o->tanggal_pemeriksaan,
                    'jam_pemeriksaan' => $o->jam_pemeriksaan,
                    'status' => $o->status,
                    'progress' => [
                        'total' => $total,
                        'selesai' => $done,
                    ],
                    'pasien' => $o->pasien ? [
                        'id' => $o->pasien->id,
                        'nama_pasien' => $o->pasien->nama_pasien,
                        'no_emr' => $o->pasien->no_emr,
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    private function isDokterKKLP($dokter): bool
    {
        $namaSpesialis = strtolower(trim($dokter->jenisSpesialis->nama_spesialis ?? ''));

        if (str_contains($namaSpesialis, 'kedokteran keluarga layanan primer')) {
            return true;
        }

        $poliNames = $dokter->poli->pluck('nama_poli')
            ->map(fn ($item) => strtolower(trim($item)))
            ->toArray();

        foreach ($poliNames as $poli) {
            if (
                str_contains($poli, 'kklp') ||
                str_contains($poli, 'kedokteran keluarga layanan primer')
            ) {
                return true;
            }
        }

        return false;
    }

    private function isDokterPenyakitDalam($dokter): bool
    {
        if (! $dokter) {
            return false;
        }

        $namaSpesialis = strtolower(trim($dokter->jenisSpesialis->nama_spesialis ?? ''));

        if (
            str_contains($namaSpesialis, 'penyakit dalam') ||
            str_contains($namaSpesialis, 'internis')
        ) {
            return true;
        }

        $poliValue = $dokter->poli ?? null;

        if ($poliValue instanceof \Illuminate\Support\Collection) {
            $poliNames = $poliValue
                ->pluck('nama_poli')
                ->filter()
                ->map(fn ($item) => strtolower(trim($item)))
                ->toArray();
        } elseif (is_iterable($poliValue)) {
            $poliNames = collect($poliValue)
                ->pluck('nama_poli')
                ->filter()
                ->map(fn ($item) => strtolower(trim($item)))
                ->toArray();
        } else {
            $singlePoliName = strtolower(trim($poliValue->nama_poli ?? ''));
            $poliNames = $singlePoliName !== '' ? [$singlePoliName] : [];
        }

        foreach ($poliNames as $poli) {
            if (
                str_contains($poli, 'penyakit dalam') ||
                str_contains($poli, 'internis')
            ) {
                return true;
            }
        }

        return false;
    }

    public function getDataDokter()
    {
        try {
            $login = Auth::user()->id;

            $dataDokter = Dokter::with(['user', 'poli', 'jenisSpesialis'])
                ->where('user_id', $login)
                ->get()
                ->map(function ($dokter) {
                    $firstPoli = null;
                    $allPoli = [];

                    if ($dokter->poli && $dokter->poli->isNotEmpty()) {
                        $firstPoli = [
                            'id' => (int) $dokter->poli->first()->id,
                            'nama_poli' => (string) $dokter->poli->first()->nama_poli,
                        ];

                        $allPoli = $dokter->poli->map(function ($poli) {
                            return [
                                'id' => (int) $poli->id,
                                'nama_poli' => (string) $poli->nama_poli,
                            ];
                        })->toArray();
                    }

                    $isKklp = $this->isDokterKKLP($dokter);
                    $isPenyakitDalam = $this->isDokterPenyakitDalam($dokter);

                    $formType = 'general';
                    if ($isKklp) {
                        $formType = 'kklp';
                    } elseif ($isPenyakitDalam) {
                        $formType = 'penyakit_dalam';
                    }

                    return [
                        'id' => (int) $dokter->id,
                        'user_id' => (int) $dokter->user_id,
                        'nama_dokter' => (string) ($dokter->nama_dokter ?? ''),
                        'foto_dokter' => $dokter->foto_dokter,
                        'deskripsi_dokter' => $dokter->deskripsi_dokter,
                        'pengalaman' => $dokter->pengalaman,
                        'jenis_spesialis_id' => $dokter->jenis_spesialis_id ? (int) $dokter->jenis_spesialis_id : null,
                        'no_hp' => $dokter->no_hp,
                        'created_at' => $dokter->created_at ? $dokter->created_at->toISOString() : null,
                        'updated_at' => $dokter->updated_at ? $dokter->updated_at->toISOString() : null,

                        'poli' => $firstPoli,
                        'all_poli' => $allPoli,

                        'jenis_spesialis' => $dokter->jenisSpesialis ? [
                            'id' => (int) $dokter->jenisSpesialis->id,
                            'nama_spesialis' => (string) $dokter->jenisSpesialis->nama_spesialis,
                        ] : null,

                        'is_kklp' => $isKklp,
                        'is_penyakit_dalam' => $isPenyakitDalam,
                        'form_type' => $formType,

                        'user' => $dokter->user ? [
                            'id' => (int) $dokter->user->id,
                            'username' => (string) $dokter->user->username,
                            'email' => (string) $dokter->user->email,
                            'role' => (string) $dokter->user->role,
                        ] : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'status' => 200,
                'Data Dokter' => $dataDokter,
                'message' => 'Berhasil Mengambil Data Dokter',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error getting data dokter: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dokter: '.$e->getMessage(),
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
                $namaFoto = $request->nama_dokter.'_'.time().'.'.$fileFoto->getClientOriginalExtension();
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
            Log::error('Update data dokter error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
            ], 500);
        }
    }

    private function isKunjunganPenyakitDalam($kunjungan): bool
    {
        $namaPoli = strtolower(trim($kunjungan->poli->nama_poli ?? ''));

        return str_contains($namaPoli, 'penyakit dalam') ||
               str_contains($namaPoli, 'internis');
    }

    private function isDokterGigi($dokter): bool
    {
        // Cek berdasarkan jenis_spesialis_id
        // ID 12 = Dokter Gigi Umum, ID 15 = Dokter Gigi Anak
        if (in_array($dokter->jenis_spesialis_id, [12, 15])) {
            return true;
        }

        // Fallback: cek nama spesialis
        $namaSpesialis = strtolower(trim($dokter->jenisSpesialis->nama_spesialis ?? ''));

        if (str_contains($namaSpesialis, 'gigi') || str_contains($namaSpesialis, 'dental')) {
            return true;
        }

        // Cek berdasarkan poli
        $poliNames = $dokter->poli->pluck('nama_poli')
            ->map(fn ($item) => strtolower(trim($item)))
            ->toArray();

        foreach ($poliNames as $poli) {
            if (str_contains($poli, 'gigi') || str_contains($poli, 'dental')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check apakah kunjungan adalah ke poli gigi
     */
    private function isKunjunganGigi($kunjungan): bool
    {
        $namaPoli = strtolower(trim($kunjungan->poli->nama_poli ?? ''));

        // Cek nama poli
        if (str_contains($namaPoli, 'gigi') || str_contains($namaPoli, 'dental')) {
            return true;
        }

        // Cek poli_id (ID 7 = Gigi dan Mulut Dewasa, ID 8 = Gigi dan Mulut Anak)
        if (in_array($kunjungan->poli_id, [7, 8])) {
            return true;
        }

        return false;
    }

    private function mapPengkajianPenyakitDalamForMobile($pd): ?array
    {
        if (! $pd) {
            return null;
        }

        return [
            'id' => $pd->id,
            'emr_id' => $pd->emr_id,
            'kunjungan_id' => $pd->kunjungan_id,
            'pasien_id' => $pd->pasien_id,
            'dokter_id' => $pd->dokter_id,
            'poli_id' => $pd->poli_id,

            'tanggal_pengkajian' => $pd->tanggal_pengkajian,
            'jam_pengkajian' => $pd->jam_pengkajian,
            'no_rm_snapshot' => $pd->no_rm_snapshot,
            'nik_snapshot' => $pd->nik_snapshot,
            'sumber_data' => $pd->sumber_data,
            'sumber_data_lainnya' => $pd->sumber_data_lainnya,
            'alergi' => $pd->alergi,

            'nyeri_ada' => (bool) $pd->nyeri_ada,
            'skala_nyeri' => $pd->skala_nyeri,
            'karakteristik_nyeri' => $pd->karakteristik_nyeri,
            'lokasi_nyeri' => $pd->lokasi_nyeri,
            'durasi_nyeri' => $pd->durasi_nyeri,
            'frekuensi_nyeri' => $pd->frekuensi_nyeri,
            'tren_nyeri' => $pd->tren_nyeri,

            'keluhan_utama' => $pd->keluhan_utama,
            'riwayat_penyakit_sekarang' => $pd->riwayat_penyakit_sekarang,

            'riwayat_keluarga_hipertensi' => (bool) $pd->riwayat_keluarga_hipertensi,
            'riwayat_keluarga_kencing_manis' => (bool) $pd->riwayat_keluarga_kencing_manis,
            'riwayat_keluarga_jantung' => (bool) $pd->riwayat_keluarga_jantung,
            'riwayat_keluarga_asthma' => (bool) $pd->riwayat_keluarga_asthma,
            'riwayat_penyakit_keluarga_lain' => $pd->riwayat_penyakit_keluarga_lain,
            'riwayat_pekerjaan_sosial_ekonomi_psikologi_kebiasaan' => $pd->riwayat_pekerjaan_sosial_ekonomi_psikologi_kebiasaan,

            'keadaan_umum' => $pd->keadaan_umum,
            'status_gizi' => $pd->status_gizi,
            'gcs_e' => $pd->gcs_e,
            'gcs_m' => $pd->gcs_m,
            'gcs_v' => $pd->gcs_v,
            'tindakan_resusitasi' => (bool) $pd->tindakan_resusitasi,

            'berat_badan' => $pd->berat_badan,
            'tinggi_badan' => $pd->tinggi_badan,
            'tensi_sistolik' => $pd->tensi_sistolik,
            'tensi_diastolik' => $pd->tensi_diastolik,
            'suhu_axila' => $pd->suhu_axila,
            'suhu_rectal' => $pd->suhu_rectal,
            'nadi' => $pd->nadi,
            'respirasi' => $pd->respirasi,
            'saturasi_o2' => $pd->saturasi_o2,
            'saturasi_o2_dengan' => $pd->saturasi_o2_dengan,

            'pemeriksaan_kulit' => $pd->pemeriksaan_kulit,
            'pemeriksaan_kepala_dan_leher' => $pd->pemeriksaan_kepala_dan_leher,
            'pemeriksaan_telinga_hidung_mulut' => $pd->pemeriksaan_telinga_hidung_mulut,
            'pemeriksaan_leher' => $pd->pemeriksaan_leher,

            'paru_inspeksi' => $pd->paru_inspeksi,
            'paru_palpasi' => $pd->paru_palpasi,
            'paru_perkusi' => $pd->paru_perkusi,
            'paru_auskultasi' => $pd->paru_auskultasi,

            'jantung_inspeksi' => $pd->jantung_inspeksi,
            'jantung_palpasi' => $pd->jantung_palpasi,
            'jantung_perkusi' => $pd->jantung_perkusi,
            'jantung_auskultasi' => $pd->jantung_auskultasi,

            'pemeriksaan_ekstremitas' => $pd->pemeriksaan_ekstremitas,
            'pemeriksaan_alat_kelamin_dan_rektum' => $pd->pemeriksaan_alat_kelamin_dan_rektum,
            'pemeriksaan_neurologis' => $pd->pemeriksaan_neurologis,

            'diagnosa_kerja' => $pd->diagnosa_kerja,
            'diagnosa_diferensial' => $pd->diagnosa_diferensial,
            'terapi_tindakan' => $pd->terapi_tindakan,
            'rencana_kerja' => $pd->rencana_kerja,

            'boleh_pulang' => (bool) $pd->boleh_pulang,
            'tanggal_pulang' => $pd->tanggal_pulang,
            'jam_keluar' => $pd->jam_keluar,

            'kontrol_poliklinik' => (bool) $pd->kontrol_poliklinik,
            'nama_poli_kontrol' => $pd->nama_poli_kontrol,
            'tanggal_kontrol' => $pd->tanggal_kontrol,

            'dirawat_di_ruang' => $pd->dirawat_di_ruang,
            'kelas_rawat' => $pd->kelas_rawat,

            'tanggal_ttd_dokter' => $pd->tanggal_ttd_dokter,
            'jam_ttd_dokter' => $pd->jam_ttd_dokter,
            'nama_dokter_ttd' => $pd->nama_dokter_ttd,
            'status_form' => $pd->status_form,

            'riwayat' => $pd->riwayat
                ? $pd->riwayat->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'riwayat_penyakit' => $item->riwayat_penyakit,
                        'tahun' => $item->tahun,
                        'riwayat_pengobatan' => $item->riwayat_pengobatan,
                    ];
                })->values()->toArray()
                : [],

            'penunjang' => $pd->penunjang
                ? $pd->penunjang->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'jenis_penunjang' => $item->jenis_penunjang,
                        'jenis_penunjang_lainnya' => $item->jenis_penunjang_lainnya,
                        'hasil_penunjang' => $item->hasil_penunjang,
                        'tanggal_penunjang' => $item->tanggal_penunjang,
                    ];
                })->values()->toArray()
                : [],
        ];
    }

    private function resolveFormType($dokter, $kunjungan, $emr = null, $dental = null): string
    {
        $kklp = $emr?->emrKklp;
        $penyakitDalam = $emr?->pengkajianAwalPenyakitDalam;

        // 1. Prioritas: data form yang benar-benar ada
        if (! is_null($dental)) {
            return 'gigi';
        }

        if (! is_null($penyakitDalam)) {
            return 'penyakit_dalam';
        }

        if (! is_null($kklp)) {
            return 'kklp';
        }

        // 2. Fallback: cek nama poli kunjungan
        $namaPoli = strtolower(trim($kunjungan->poli->nama_poli ?? ''));

        if (str_contains($namaPoli, 'gigi') || str_contains($namaPoli, 'dental')) {
            return 'gigi';
        }

        if ($namaPoli === 'penyakit dalam') {
            return 'penyakit_dalam';
        }

        if ($namaPoli === 'kklp') {
            return 'kklp';
        }

        return 'general';
    }

    public function getDataKunjunganBerdasarkanIdDokter(Request $request)
    {
        try {
            $user = $request->user();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi',
                ], 401);
            }

            $dokter = Dokter::with(['jenisSpesialis', 'poli'])
                ->where('user_id', $user->id)
                ->first();

            if (! $dokter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokter tidak ditemukan untuk user ini',
                ], 404);
            }

            $dataKunjungan = Kunjungan::with([
                'pasien:id,nama_pasien,alamat,tanggal_lahir,jenis_kelamin,no_emr',
                'poli:id,nama_poli',
                'dokter:id,nama_dokter,user_id,jenis_spesialis_id,no_hp,pengalaman',
                'dokter.jenisSpesialis:id,nama_spesialis',
                'emr' => function ($query) {
                    $query->with([
                        'perawat:id,nama_perawat,foto_perawat,no_hp_perawat',
                        'emrKklp',
                        'pengkajianAwalPenyakitDalam.riwayat',
                        'pengkajianAwalPenyakitDalam.penunjang',
                    ]);
                },
            ])
                ->where('dokter_id', $dokter->id)
                ->where('status', 'Engaged')
                ->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('penjualan_layanan as pl')
                        ->whereColumn('pl.kunjungan_id', 'kunjungan.id')
                        ->whereIn('pl.status', ['Belum Bayar', 'Pending']);
                })
                ->orderBy('tanggal_kunjungan', 'desc')
                ->orderBy('no_antrian', 'asc')
                ->get();

            Log::info('=== HASIL QUERY KUNJUNGAN DOKTER ===', [
                'dokter_id' => $dokter->id,
                'total_kunjungan' => $dataKunjungan->count(),
            ]);

            $formatted = $dataKunjungan->map(function ($kunjungan) use ($dokter) {
                $emr = $kunjungan->emr;
                $kklp = $emr?->emrKklp;
                $penyakitDalam = $emr?->pengkajianAwalPenyakitDalam;

                $isKklp = $kklp ? true : $this->isDokterKKLP($dokter);

                $isPenyakitDalam = $penyakitDalam ? true : (
                    $this->isKunjunganPenyakitDalam($kunjungan) ||
                    $this->isDokterPenyakitDalam($dokter)
                );

                $formType = $this->resolveFormType($dokter, $kunjungan, $emr);

                return [
                    'id' => (int) $kunjungan->id,
                    'pasien_id' => (int) $kunjungan->pasien_id,
                    'poli_id' => (int) $kunjungan->poli_id,
                    'order_layanan_id' => $this->resolveOrderLayananIdFromKunjungan($kunjungan),
                    'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                    'no_antrian' => $kunjungan->no_antrian
                        ? (string) $kunjungan->no_antrian
                        : null,
                    'status' => (string) $kunjungan->status,
                    'keluhan_awal' => $kunjungan->keluhan_awal ?? null,
                    'created_at' => optional($kunjungan->created_at)->toISOString(),
                    'updated_at' => optional($kunjungan->updated_at)->toISOString(),

                    'form_type' => $formType,
                    'is_kklp' => $isKklp,
                    'is_penyakit_dalam' => $isPenyakitDalam,

                    'pasien' => $kunjungan->pasien ? [
                        'id' => (int) $kunjungan->pasien->id,
                        'nama_pasien' => (string) ($kunjungan->pasien->nama_pasien ?? 'Tidak ada nama'),
                        'alamat' => $kunjungan->pasien->alamat
                            ? (string) $kunjungan->pasien->alamat
                            : null,
                        'tanggal_lahir' => $kunjungan->pasien->tanggal_lahir,
                        'jenis_kelamin' => $kunjungan->pasien->jenis_kelamin
                            ? (string) $kunjungan->pasien->jenis_kelamin
                            : null,
                        'no_emr' => $kunjungan->pasien->no_emr
                            ? (string) $kunjungan->pasien->no_emr
                            : null,
                    ] : null,

                    'poli' => $kunjungan->poli ? [
                        'id' => (int) $kunjungan->poli->id,
                        'nama_poli' => (string) $kunjungan->poli->nama_poli,
                    ] : null,

                    'dokter' => $kunjungan->dokter ? [
                        'id' => (int) $kunjungan->dokter->id,
                        'nama_dokter' => (string) ($kunjungan->dokter->nama_dokter ?? ''),
                        'no_hp' => $kunjungan->dokter->no_hp,
                        'pengalaman' => $kunjungan->dokter->pengalaman,
                        'spesialis' => $kunjungan->dokter->jenisSpesialis ? [
                            'id' => (int) $kunjungan->dokter->jenisSpesialis->id,
                            'nama_spesialis' => (string) $kunjungan->dokter->jenisSpesialis->nama_spesialis,
                        ] : null,
                        'poli' => $kunjungan->poli ? [
                            'id' => (int) $kunjungan->poli->id,
                            'nama_poli' => (string) $kunjungan->poli->nama_poli,
                        ] : null,
                    ] : null,

                    'emr' => $emr ? [
                        'id' => (int) $emr->id,
                        'dokter_id' => $emr->dokter_id ? (int) $emr->dokter_id : null,
                        'keluhan_utama' => $emr->keluhan_utama,
                        'riwayat_penyakit_sekarang' => $emr->riwayat_penyakit_sekarang ?? null,
                        'riwayat_penyakit_dahulu' => $emr->riwayat_penyakit_dahulu,
                        'riwayat_penyakit_keluarga' => $emr->riwayat_penyakit_keluarga,
                        'tekanan_darah' => $emr->tekanan_darah,
                        'suhu_tubuh' => $emr->suhu_tubuh,
                        'nadi' => $emr->nadi,
                        'pernapasan' => $emr->pernapasan,
                        'saturasi_oksigen' => $emr->saturasi_oksigen,
                        'diagnosis' => $emr->diagnosis,
                        'tanggal_pemeriksaan_perawat' => $emr->created_at,
                        'perawat' => $emr->perawat ? [
                            'id' => (int) $emr->perawat->id,
                            'nama_perawat' => (string) $emr->perawat->nama_perawat,
                            'foto_perawat' => $emr->perawat->foto_perawat,
                            'no_hp_perawat' => $emr->perawat->no_hp_perawat,
                        ] : null,
                    ] : null,

                    'kklp' => $kklp ? [
                        'id' => $kklp->id,
                        'form' => $kklp,
                    ] : null,

                    'pengkajian_awal_penyakit_dalam' => $this->mapPengkajianPenyakitDalamForMobile($penyakitDalam),
                ];
            });

            return response()->json([
                'success' => true,
                'status' => 200,
                'data' => $formatted,
                'kunjungan_hari_ini' => $formatted,
                'dokter_info' => [
                    'id' => (int) $dokter->id,
                    'nama_dokter' => (string) $dokter->nama_dokter,
                    'user_id' => (int) $user->id,
                    'is_kklp' => $this->isDokterKKLP($dokter),
                    'is_penyakit_dalam' => $this->isDokterPenyakitDalam($dokter),
                    'form_type' => 'dynamic_by_kunjungan',
                ],
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Error getDataKunjunganBerdasarkanIdDokter: '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kunjungan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getDataObat()
    {
        try {
            $obat = Obat::with([
                'brandFarmasi:id,nama_brand',
                'kategoriObat:id,nama_kategori_obat',
                'jenisObat:id,nama_jenis_obat',
                'satuanObat:id,nama_satuan_obat',
                'batchObat' => function ($query) {
                    $query->orderBy('tanggal_kadaluarsa_obat', 'asc')
                        ->select('id', 'obat_id', 'nama_batch', 'tanggal_kadaluarsa_obat');
                },
            ])
                ->where('jumlah', '>', 0)
                ->select([
                    'id',
                    'brand_farmasi_id',
                    'kategori_obat_id',
                    'jenis_obat_id',
                    'satuan_obat_id',
                    'kode_obat',
                    'nama_obat',
                    'kandungan_obat',
                    'jumlah',
                    'dosis',
                    'harga_jual_obat',
                ])
                ->orderBy('nama_obat', 'asc')
                ->get()
                ->map(function ($item) {
                    // Ambil batch terdekat
                    $batchTerdekat = $item->batchObat->first();

                    return [
                        'id' => $item->id,
                        'kode_obat' => $item->kode_obat,
                        'nama_obat' => $item->nama_obat,
                        'kandungan_obat' => $item->kandungan_obat,
                        'jumlah' => $item->jumlah,
                        'dosis' => $item->dosis,
                        'harga_jual_obat' => $item->harga_jual_obat,

                        // ✅ DATA DARI BATCH TERDEKAT
                        'nomor_batch_obat' => $batchTerdekat?->nama_batch,
                        'tanggal_kadaluarsa_obat' => $batchTerdekat?->tanggal_kadaluarsa_obat,

                        // ✅ RELASI
                        'brand_farmasi' => $item->brandFarmasi ? [
                            'id' => $item->brandFarmasi->id,
                            'nama_brand' => $item->brandFarmasi->nama_brand,
                        ] : null,

                        'kategori_obat' => $item->kategoriObat ? [
                            'id' => $item->kategoriObat->id,
                            'nama_kategori_obat' => $item->kategoriObat->nama_kategori_obat,
                        ] : null,

                        'jenis_obat' => $item->jenisObat ? [
                            'id' => $item->jenisObat->id,
                            'nama_jenis_obat' => $item->jenisObat->nama_jenis_obat,
                        ] : null,

                        'satuan_obat' => $item->satuanObat ? [
                            'id' => $item->satuanObat->id,
                            'nama_satuan_obat' => $item->satuanObat->nama_satuan_obat,
                        ] : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Data obat berhasil diambil',
                'data' => $obat,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getDataObat: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data obat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getLayanan()
    {
        try {
            $layanan = \App\Models\Layanan::orderBy('nama_layanan', 'asc')->get();

            if ($layanan->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ada layanan tersedia.',
                    'data' => [],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data layanan berhasil diambil',
                'data' => $layanan->map(function ($item) {
                    $hargaRaw = $item->harga_setelah_diskon ?? $item->harga_sebelum_diskon ?? 0;

                    return [
                        'id' => $item->id,
                        'nama_layanan' => $item->nama_layanan,
                        'harga_layanan' => number_format($hargaRaw, 2, ',', '.'),
                        'harga_layanan_raw' => $hargaRaw,
                        'harga_sebelum_diskon' => $item->harga_sebelum_diskon ?? null,
                        'harga_setelah_diskon' => $item->harga_setelah_diskon ?? null,
                        'diskon' => $item->diskon ?? 0,
                        'is_global' => $item->is_global ?? false,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ];
                }),
                'total' => $layanan->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting all layanan: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    private function normalizeKklpDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
                return \Carbon\Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d');
            }

            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function hasMeaningfulValue($value): bool
    {
        if (is_array($value)) {
            foreach ($value as $v) {
                if ($this->hasMeaningfulValue($v)) {
                    return true;
                }
            }

            return false;
        }

        return $value !== null && trim((string) $value) !== '';
    }

    private function normalizeOrangtuaRows(array $rows): array
    {
        return collect($rows)
            ->map(function ($row) {
                return [
                    'hubungan' => $row['hubungan'] ?? null,
                    'nama_lengkap' => $row['nama_lengkap'] ?? null,
                    'tanggal_lahir' => $this->normalizeKklpDate($row['tanggal_lahir'] ?? null),
                    'umur' => $row['umur'] ?? null,
                    'alamat' => $row['alamat'] ?? null,
                    'telepon' => $row['telepon'] ?? null,
                    'pekerjaan' => $row['pekerjaan'] ?? null,
                    'agama' => $row['agama'] ?? null,
                    'pendidikan_terakhir' => $row['pendidikan_terakhir'] ?? null,
                    'suku_bangsa' => $row['suku_bangsa'] ?? null,
                ];
            })
            ->filter(fn ($row) => $this->hasMeaningfulValue($row))
            ->values()
            ->toArray();
    }

    private function normalizeHeteroanamnesisRow(?array $row): ?array
    {
        if (! is_array($row)) {
            return null;
        }

        $result = [
            'nama_lengkap' => $row['nama_lengkap'] ?? null,
            'jenis_kelamin' => $row['jenis_kelamin'] ?? null,
            'tanggal_lahir' => $this->normalizeKklpDate($row['tanggal_lahir'] ?? null),
            'umur' => $row['umur'] ?? null,
            'alamat' => $row['alamat'] ?? null,
            'telepon' => $row['telepon'] ?? null,
            'hubungan_dengan_pasien' => $row['hubungan_dengan_pasien'] ?? null,
        ];

        return $this->hasMeaningfulValue($result) ? $result : null;
    }

    private function normalizeAnggotaKeluargaRows(array $rows): array
    {
        return collect($rows)
            ->map(function ($row) {
                return [
                    'kategori' => $row['kategori'] ?? null,
                    'nama' => $row['nama'] ?? null,
                    'jenis_kelamin' => $row['jenis_kelamin'] ?? null,
                    'tanggal_lahir' => $this->normalizeKklpDate($row['tanggal_lahir'] ?? null),
                    'umur' => $row['umur'] ?? null,
                    'pekerjaan' => $row['pekerjaan'] ?? null,
                    'no_hp' => $row['no_hp'] ?? null,
                    'status_kesehatan' => $row['status_kesehatan'] ?? null,
                    'hubungan' => $row['hubungan'] ?? null,
                ];
            })
            ->filter(fn ($row) => $this->hasMeaningfulValue($row))
            ->values()
            ->toArray();
    }

    private function normalizeHomevisitRows(array $rows): array
    {
        return collect($rows)
            ->map(function ($row) {
                return [
                    'nomor_kunjungan' => $row['nomor_kunjungan'] ?? null,
                    'tanggal' => $this->normalizeKklpDate($row['tanggal'] ?? null),
                    'catatan' => $row['catatan'] ?? null,
                    'kesimpulan' => $row['kesimpulan'] ?? null,
                    'rencana_tindak_lanjut' => $row['rencana_tindak_lanjut'] ?? null,
                ];
            })
            ->filter(fn ($row) => $this->hasMeaningfulValue($row))
            ->values()
            ->toArray();
    }

    private function normalizeFamilyPlanRows(array $rows): array
    {
        return collect($rows)
            ->map(function ($row) {
                return [
                    'nama' => $row['nama'] ?? null,
                    'usia' => $row['usia'] ?? null,
                    'status_kesehatan' => $row['status_kesehatan'] ?? null,
                    'skrining' => $row['skrining'] ?? null,
                    'edukasi_konseling' => $row['edukasi_konseling'] ?? null,
                    'imunisasi' => $row['imunisasi'] ?? null,
                    'catatan' => $row['catatan'] ?? null,
                ];
            })
            ->filter(fn ($row) => $this->hasMeaningfulValue($row))
            ->values()
            ->toArray();
    }

    private function normalizeCopcPlanRows(array $rows): array
    {
        return collect($rows)
            ->map(function ($row) {
                return [
                    'masalah_komunitas' => $row['masalah_komunitas'] ?? null,
                    'rencana_eksplorasi' => $row['rencana_eksplorasi'] ?? null,
                    'rencana_edukasi' => $row['rencana_edukasi'] ?? null,
                    'target' => $row['target'] ?? null,
                ];
            })
            ->filter(fn ($row) => $this->hasMeaningfulValue($row))
            ->values()
            ->toArray();
    }

    private function normalizeEkstremitasRows($ekstremitas): array
    {
        if (! is_array($ekstremitas)) {
            return [];
        }

        // support format array of rows
        $isList = array_keys($ekstremitas) === range(0, count($ekstremitas) - 1);

        if ($isList) {
            return collect($ekstremitas)
                ->map(function ($row) {
                    return [
                        'anggota' => $row['anggota'] ?? null,
                        'akral' => $row['akral'] ?? null,
                        'gerakan' => $row['gerakan'] ?? null,
                        'tonus' => $row['tonus'] ?? null,
                        'trofi' => $row['trofi'] ?? null,
                        'refleks_fisiologis' => $row['refleks_fisiologis'] ?? null,
                        'refleks_patologis' => $row['refleks_patologis'] ?? null,
                        'sensibilitas' => $row['sensibilitas'] ?? null,
                        'meningeal_signs' => $row['meningeal_signs'] ?? null,
                    ];
                })
                ->filter(fn ($row) => $this->hasMeaningfulValue($row))
                ->values()
                ->toArray();
        }

        // support format map tunggal dari flutter lama
        $rows = [
            [
                'anggota' => 'kanan_atas',
                'akral' => $ekstremitas['akral'] ?? null,
                'gerakan' => $ekstremitas['gerakan_ka'] ?? null,
                'tonus' => $ekstremitas['tonus'] ?? null,
                'trofi' => $ekstremitas['trofi'] ?? null,
                'refleks_fisiologis' => $ekstremitas['refleks_fisiologis'] ?? null,
                'refleks_patologis' => $ekstremitas['refleks_patologis'] ?? null,
                'sensibilitas' => $ekstremitas['sensibilitas'] ?? null,
                'meningeal_signs' => $ekstremitas['meningeal_signs'] ?? null,
            ],
            [
                'anggota' => 'kiri_atas',
                'akral' => $ekstremitas['akral'] ?? null,
                'gerakan' => $ekstremitas['gerakan_ki'] ?? null,
                'tonus' => $ekstremitas['tonus'] ?? null,
                'trofi' => $ekstremitas['trofi'] ?? null,
                'refleks_fisiologis' => $ekstremitas['refleks_fisiologis'] ?? null,
                'refleks_patologis' => $ekstremitas['refleks_patologis'] ?? null,
                'sensibilitas' => $ekstremitas['sensibilitas'] ?? null,
                'meningeal_signs' => $ekstremitas['meningeal_signs'] ?? null,
            ],
            [
                'anggota' => 'kanan_bawah',
                'akral' => $ekstremitas['akral'] ?? null,
                'gerakan' => $ekstremitas['gerakan_ba'] ?? null,
                'tonus' => $ekstremitas['tonus'] ?? null,
                'trofi' => $ekstremitas['trofi'] ?? null,
                'refleks_fisiologis' => $ekstremitas['refleks_fisiologis'] ?? null,
                'refleks_patologis' => $ekstremitas['refleks_patologis'] ?? null,
                'sensibilitas' => $ekstremitas['sensibilitas'] ?? null,
                'meningeal_signs' => $ekstremitas['meningeal_signs'] ?? null,
            ],
            [
                'anggota' => 'kiri_bawah',
                'akral' => $ekstremitas['akral'] ?? null,
                'gerakan' => $ekstremitas['gerakan_bi'] ?? null,
                'tonus' => $ekstremitas['tonus'] ?? null,
                'trofi' => $ekstremitas['trofi'] ?? null,
                'refleks_fisiologis' => $ekstremitas['refleks_fisiologis'] ?? null,
                'refleks_patologis' => $ekstremitas['refleks_patologis'] ?? null,
                'sensibilitas' => $ekstremitas['sensibilitas'] ?? null,
                'meningeal_signs' => $ekstremitas['meningeal_signs'] ?? null,
            ],
        ];

        return collect($rows)
            ->filter(fn ($row) => $this->hasMeaningfulValue($row))
            ->values()
            ->toArray();
    }

    private function syncKklpNestedData(EmrKklp $kklp, array $kklpData): void
    {
        // delete old data first
        EmrKklpOrangtua::where('emr_kklp_id', $kklp->id)->delete();
        EmrKklpHeteroanamnesis::where('emr_kklp_id', $kklp->id)->delete();
        EmrKklpFamilyApgar::where('emr_kklp_id', $kklp->id)->delete();
        EmrKklpFamilyScreem::where('emr_kklp_id', $kklp->id)->delete();
        EmrKklpAnggotaKeluarga::where('emr_kklp_id', $kklp->id)->delete();
        EmrKklpHomevisit::where('emr_kklp_id', $kklp->id)->delete();
        EmrKklpFamilyPlan::where('emr_kklp_id', $kklp->id)->delete();
        EmrKklpCopcPlan::where('emr_kklp_id', $kklp->id)->delete();
        EmrKklpEkstremitas::where('emr_kklp_id', $kklp->id)->delete();

        $orangtuaRows = $this->normalizeOrangtuaRows($kklpData['orangtua'] ?? []);
        foreach ($orangtuaRows as $row) {
            EmrKklpOrangtua::create(array_merge($row, [
                'emr_kklp_id' => $kklp->id,
            ]));
        }

        $hetero = $this->normalizeHeteroanamnesisRow($kklpData['heteroanamnesis'] ?? null);
        if ($hetero) {
            EmrKklpHeteroanamnesis::create(array_merge($hetero, [
                'emr_kklp_id' => $kklp->id,
            ]));
        }

        $familyApgar = $kklpData['family_apgar'] ?? null;
        if (is_array($familyApgar) && $this->hasMeaningfulValue($familyApgar)) {
            EmrKklpFamilyApgar::create([
                'emr_kklp_id' => $kklp->id,
                'adaptability' => $familyApgar['adaptability'] ?? null,
                'partnership' => $familyApgar['partnership'] ?? null,
                'growth' => $familyApgar['growth'] ?? null,
                'affection' => $familyApgar['affection'] ?? null,
                'resolve' => $familyApgar['resolve'] ?? null,
                'total_skor' => $familyApgar['total_skor'] ?? null,
                'interpretasi' => $familyApgar['interpretasi'] ?? null,
            ]);
        }

        $familyScreem = $kklpData['family_screem'] ?? null;
        if (is_array($familyScreem) && $this->hasMeaningfulValue($familyScreem)) {
            EmrKklpFamilyScreem::create([
                'emr_kklp_id' => $kklp->id,
                'social_sumber_daya' => $familyScreem['social_sumber_daya'] ?? null,
                'social_patologis' => $familyScreem['social_patologis'] ?? null,
                'cultural_sumber_daya' => $familyScreem['cultural_sumber_daya'] ?? null,
                'cultural_patologis' => $familyScreem['cultural_patologis'] ?? null,
                'religious_sumber_daya' => $familyScreem['religious_sumber_daya'] ?? null,
                'religious_patologis' => $familyScreem['religious_patologis'] ?? null,
                'educational_sumber_daya' => $familyScreem['educational_sumber_daya'] ?? null,
                'educational_patologis' => $familyScreem['educational_patologis'] ?? null,
                'economic_sumber_daya' => $familyScreem['economic_sumber_daya'] ?? null,
                'economic_patologis' => $familyScreem['economic_patologis'] ?? null,
                'medical_sumber_daya' => $familyScreem['medical_sumber_daya'] ?? null,
                'medical_patologis' => $familyScreem['medical_patologis'] ?? null,
            ]);
        }

        $anggotaRows = $this->normalizeAnggotaKeluargaRows($kklpData['anggota_keluarga'] ?? []);
        foreach ($anggotaRows as $row) {
            EmrKklpAnggotaKeluarga::create(array_merge($row, [
                'emr_kklp_id' => $kklp->id,
            ]));
        }

        $homevisitRows = $this->normalizeHomevisitRows($kklpData['homevisit'] ?? []);
        foreach ($homevisitRows as $row) {
            EmrKklpHomevisit::create(array_merge($row, [
                'emr_kklp_id' => $kklp->id,
            ]));
        }

        $familyPlanRows = $this->normalizeFamilyPlanRows($kklpData['family_plan'] ?? []);
        foreach ($familyPlanRows as $row) {
            EmrKklpFamilyPlan::create(array_merge($row, [
                'emr_kklp_id' => $kklp->id,
            ]));
        }

        $copcPlanRows = $this->normalizeCopcPlanRows($kklpData['copc_plan'] ?? []);
        foreach ($copcPlanRows as $row) {
            EmrKklpCopcPlan::create(array_merge($row, [
                'emr_kklp_id' => $kklp->id,
            ]));
        }

        $ekstremitasRows = $this->normalizeEkstremitasRows($kklpData['ekstremitas'] ?? []);
        foreach ($ekstremitasRows as $row) {
            EmrKklpEkstremitas::create(array_merge($row, [
                'emr_kklp_id' => $kklp->id,
            ]));
        }
    }

    public function saveEMR(Request $request)
    {
        Log::info('REQUEST MASUK SAVE EMR', $request->all());
        try {
            $request->validate([
                'kunjungan_id' => 'required|exists:kunjungan,id',
                'diagnosis' => 'required|string',

                'keluhan_utama' => 'nullable|string',
                'riwayat_penyakit_dahulu' => 'nullable|string',
                'riwayat_penyakit_keluarga' => 'nullable|string',
                'tekanan_darah' => 'nullable|string|max:20',
                'suhu_tubuh' => 'nullable|numeric',
                'nadi' => 'nullable|integer',
                'pernapasan' => 'nullable|integer',
                'saturasi_oksigen' => 'nullable|integer',
                'tinggi_badan' => 'nullable|numeric',
                'berat_badan' => 'nullable|numeric',
                'imt' => 'nullable|numeric',

                'resep' => 'nullable|array',
                'resep.*.obat_id' => 'required_with:resep|exists:obat,id',
                'resep.*.jumlah' => 'required_with:resep|integer|min:1',
                'resep.*.keterangan' => 'nullable|string',
                'resep.*.dosis' => 'nullable|numeric',

                'layanan' => 'nullable|array',
                'layanan.*.layanan_id' => 'required_with:layanan|exists:layanan,id',
                'layanan.*.jumlah' => 'required_with:layanan|integer|min:1',

                'lab_tests' => 'nullable|array',
                'lab_tests.*.lab_test_id' => 'required_with:lab_tests|exists:jenis_pemeriksaan_lab,id',
                'lab_tests.*.tanggal_pemeriksaan' => 'nullable|date',
                'lab_tests.*.jam_pemeriksaan' => 'nullable|string',

                'radiologi_tests' => 'nullable|array',
                'radiologi_tests.*.jenis_radiologi_id' => 'required_with:radiologi_tests|exists:jenis_pemeriksaan_radiologi,id',
                'radiologi_tests.*.tanggal_pemeriksaan' => 'nullable|date',
                'radiologi_tests.*.jam_pemeriksaan' => 'nullable|string',

                // ===== KKLP =====
                'is_kklp' => 'nullable|boolean',
                'kklp_data' => 'nullable|array',
                'kklp_data.nama_dokter_form' => 'nullable|string|max:255',
                'kklp_data.nim_dokter' => 'nullable|string|max:255',
                'kklp_data.kasus_ke' => 'nullable|string|max:255',
                'kklp_data.tanggal_kasus' => 'nullable|date',
                'kklp_data.no_kasus' => 'nullable|string|max:255',
                'kklp_data.telepon_pasien' => 'nullable|string|max:255',
                'kklp_data.agama_pasien' => 'nullable|string|max:255',
                'kklp_data.pendidikan_terakhir_pasien' => 'nullable|string|max:255',
                'kklp_data.suku_bangsa_pasien' => 'nullable|string|max:255',
                'kklp_data.tanggal_pemeriksaan' => 'nullable|date',
                'kklp_data.tanggal_homevisit' => 'nullable|date',

                'kklp_data.riwayat_penyakit_sekarang' => 'nullable|string',
                'kklp_data.riwayat_penyakit_dahulu_detail' => 'nullable|string',
                'kklp_data.riwayat_penyakit_keluarga_detail' => 'nullable|string',
                'kklp_data.riwayat_personal_sosial' => 'nullable|string',
                'kklp_data.review_sistem' => 'nullable|string',

                'kklp_data.illness_pikiran' => 'nullable|string',
                'kklp_data.illness_perasaan' => 'nullable|string',
                'kklp_data.illness_efek_fungsi' => 'nullable|string',
                'kklp_data.illness_harapan' => 'nullable|string',
                'kklp_data.illness_kesimpulan' => 'nullable|string',

                'kklp_data.genogram_keterangan' => 'nullable|string',
                'kklp_data.bentuk_keluarga' => 'nullable|string',
                'kklp_data.siklus_kehidupan_keluarga' => 'nullable|string',
                'kklp_data.family_map_keterangan' => 'nullable|string',
                'kklp_data.apgar_score_total' => 'nullable|integer',
                'kklp_data.apgar_kesimpulan' => 'nullable|string',
                'kklp_data.family_life_line_ringkasan' => 'nullable|string',

                'kklp_data.keadaan_umum' => 'nullable|string|max:255',
                'kklp_data.kesadaran' => 'nullable|string|max:255',
                'kklp_data.tekanan_darah' => 'nullable|string|max:255',
                'kklp_data.nadi' => 'nullable|string|max:255',
                'kklp_data.respirasi' => 'nullable|string|max:255',
                'kklp_data.suhu' => 'nullable|string|max:255',
                'kklp_data.tinggi_badan' => 'nullable|string|max:255',
                'kklp_data.berat_badan' => 'nullable|string|max:255',
                'kklp_data.imt' => 'nullable|string|max:255',

                'kklp_data.lingkar_pinggang' => 'nullable|string|max:255',
                'kklp_data.lingkar_panggul' => 'nullable|string|max:255',
                'kklp_data.lingkar_lengan_atas' => 'nullable|string|max:255',
                'kklp_data.status_gizi' => 'nullable|string|max:255',
                'kklp_data.waist_hip_ratio' => 'nullable|string|max:255',

                'kklp_data.pemeriksaan_kulit' => 'nullable|string',
                'kklp_data.pemeriksaan_kelenjar_limfe' => 'nullable|string',
                'kklp_data.pemeriksaan_otot' => 'nullable|string',
                'kklp_data.pemeriksaan_tulang' => 'nullable|string',
                'kklp_data.pemeriksaan_sendi' => 'nullable|string',
                'kklp_data.pemeriksaan_kepala' => 'nullable|string',
                'kklp_data.pemeriksaan_mata' => 'nullable|string',
                'kklp_data.pemeriksaan_hidung' => 'nullable|string',
                'kklp_data.pemeriksaan_telinga' => 'nullable|string',
                'kklp_data.pemeriksaan_mulut_gigi' => 'nullable|string',
                'kklp_data.pemeriksaan_tenggorokan' => 'nullable|string',
                'kklp_data.pemeriksaan_leher' => 'nullable|string',
                'kklp_data.thorax_paru_inspeksi' => 'nullable|string',
                'kklp_data.thorax_paru_palpasi' => 'nullable|string',
                'kklp_data.thorax_paru_perkusi' => 'nullable|string',
                'kklp_data.thorax_paru_auskultasi' => 'nullable|string',
                'kklp_data.thorax_jantung_inspeksi' => 'nullable|string',
                'kklp_data.thorax_jantung_palpasi' => 'nullable|string',
                'kklp_data.thorax_jantung_perkusi' => 'nullable|string',
                'kklp_data.thorax_jantung_auskultasi' => 'nullable|string',
                'kklp_data.abdomen_inspeksi' => 'nullable|string',
                'kklp_data.abdomen_palpasi' => 'nullable|string',
                'kklp_data.abdomen_perkusi' => 'nullable|string',
                'kklp_data.abdomen_auskultasi' => 'nullable|string',
                'kklp_data.anogenital' => 'nullable|string',
                'kklp_data.tambahan_pemeriksaan_khusus' => 'nullable|string',

                'kklp_data.ringkasan_laboratorium' => 'nullable|string',
                'kklp_data.ringkasan_radiologi' => 'nullable|string',
                'kklp_data.ringkasan_penunjang_lain' => 'nullable|string',
                'kklp_data.patogenesis_patofisiologi' => 'nullable|string',
                'kklp_data.diagnosis_klinis_banding' => 'nullable|string',
                'kklp_data.diagnosis_holistik' => 'nullable|string',
                'kklp_data.uraian_diagnosis_holistik' => 'nullable|string',

                'kklp_data.upaya_promotif' => 'nullable|string',
                'kklp_data.upaya_preventif' => 'nullable|string',
                'kklp_data.upaya_kuratif' => 'nullable|string',
                'kklp_data.upaya_rehabilitatif' => 'nullable|string',
                'kklp_data.upaya_paliatif' => 'nullable|string',
                'kklp_data.copc_plan_ringkasan' => 'nullable|string',
                'kklp_data.kesimpulan_phbs' => 'nullable|string',

                'kklp_data.kondisi_rumah' => 'nullable|string',
                'kklp_data.lingkungan_sekitar_rumah' => 'nullable|string',
                'kklp_data.catatan_tambahan_homevisit' => 'nullable|string',

                'kklp_data.nilai_humanisme' => 'nullable|integer',
                'kklp_data.nilai_komunikasi' => 'nullable|integer',
                'kklp_data.nilai_pemeriksaan_fisik' => 'nullable|integer',
                'kklp_data.nilai_penalaran_klinis' => 'nullable|integer',
                'kklp_data.nilai_diagnosis_holistik' => 'nullable|integer',
                'kklp_data.nilai_pengelolaan_komprehensif' => 'nullable|integer',
                'kklp_data.nilai_edukasi_konseling' => 'nullable|integer',
                'kklp_data.nilai_organisasi_efisiensi' => 'nullable|integer',
                'kklp_data.nilai_kompetensi_keseluruhan' => 'nullable|integer',
                'kklp_data.skor_total' => 'nullable|integer',
                'kklp_data.skor_akhir' => 'nullable|numeric',
                'kklp_data.komentar_pembimbing' => 'nullable|string',
                'kklp_data.komentar_dokter_residen' => 'nullable|string',
                'kklp_data.status_form' => 'nullable|in:draft,final',
            ]);

            $userId = Auth::id();
            $dokter = Dokter::where('user_id', $userId)->firstOrFail();

            $kunjungan = Kunjungan::where('id', $request->kunjungan_id)
                ->with(['pasien', 'emr.perawat'])
                ->firstOrFail();

            $emr = $kunjungan->emr;

            if (! $emr) {
                return response()->json([
                    'success' => false,
                    'message' => 'EMR belum dibuat oleh perawat untuk kunjungan ini',
                ], 400);
            }

            $sudahDiperiksaPerawat = (
                ! empty($emr->tekanan_darah) ||
                ! empty($emr->suhu_tubuh) ||
                ! empty($emr->nadi) ||
                ! empty($emr->pernapasan) ||
                ! empty($emr->saturasi_oksigen) ||
                ! empty($emr->keluhan_utama) ||
                ! empty($emr->riwayat_penyakit_dahulu) ||
                ! empty($emr->riwayat_penyakit_keluarga)
            );

            if (! $sudahDiperiksaPerawat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pemeriksaan perawat belum dilakukan untuk kunjungan ini',
                ], 400);
            }

            if ((int) $emr->dokter_id !== (int) $dokter->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'EMR ini bukan untuk dokter yang sedang login',
                ], 403);
            }

            if ($kunjungan->status !== 'Engaged') {
                return response()->json([
                    'success' => false,
                    'message' => 'Kunjungan harus dalam status Engaged untuk dapat melengkapi EMR',
                ], 400);
            }

            $normalizeDate = function ($value) {
                if (empty($value)) {
                    return null;
                }

                try {
                    if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
                        return \Carbon\Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d');
                    }

                    return \Carbon\Carbon::parse($value)->format('Y-m-d');
                } catch (\Throwable $e) {
                    return null;
                }
            };

            $generateNoKasus = function () {
                do {
                    $kode = 'KKLP-'.now()->format('Ymd').'-'.strtoupper(substr(md5(uniqid('', true)), 0, 6));
                } while (\App\Models\EmrKklp::where('no_kasus', $kode)->exists());

                return $kode;
            };

            $result = DB::transaction(function () use (
                $request,
                $kunjungan,
                $dokter,
                $emr

            ) {
                /**
                 * ============================
                 * 1) RESEP + RESEP_OBAT
                 * ============================
                 */
                $resepId = $emr->resep_id;

                if (! empty($request->resep)) {
                    if (! $resepId) {
                        $resepId = DB::table('resep')->insertGetId([
                            'kunjungan_id' => $kunjungan->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        DB::table('resep')->where('id', $resepId)->update([
                            'kunjungan_id' => $kunjungan->id,
                            'updated_at' => now(),
                        ]);
                    }

                    DB::table('resep_obat')->where('resep_id', $resepId)->delete();

                    foreach ($request->resep as $item) {
                        $obat = Obat::findOrFail($item['obat_id']);

                        if (! is_null($obat->jumlah) && $obat->jumlah < (int) $item['jumlah']) {
                            throw new \Exception("Stok obat {$obat->nama_obat} tidak mencukupi. Stok tersedia: {$obat->jumlah}");
                        }

                        DB::table('resep_obat')->insert([
                            'resep_id' => $resepId,
                            'obat_id' => $obat->id,
                            'jumlah' => (int) $item['jumlah'],
                            'dosis' => $item['dosis'] ?? $obat->dosis ?? null,
                            'keterangan' => $item['keterangan'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                /**
                 * ============================
                 * 2) UPDATE EMR OLEH DOKTER
                 * ============================
                 */
                $updateData = [
                    'diagnosis' => $request->diagnosis,
                    'resep_id' => $resepId,
                ];

                $editableFields = [
                    'keluhan_utama',
                    'riwayat_penyakit_dahulu',
                    'riwayat_penyakit_keluarga',
                    'tekanan_darah',
                    'suhu_tubuh',
                    'nadi',
                    'pernapasan',
                    'saturasi_oksigen',
                    'tinggi_badan',
                    'berat_badan',
                    'imt',
                ];

                foreach ($editableFields as $field) {
                    if ($request->has($field)) {
                        $updateData[$field] = $request->input($field);
                    }
                }

                $emr->update($updateData);

                /**
                 * ============================
                 * 3) LAYANAN (kunjungan_layanan)
                 * ============================
                 */
                DB::table('kunjungan_layanan')->where('kunjungan_id', $kunjungan->id)->delete();

                if (! empty($request->layanan)) {
                    foreach ($request->layanan as $layananItem) {
                        DB::table('kunjungan_layanan')->insert([
                            'kunjungan_id' => $kunjungan->id,
                            'layanan_id' => $layananItem['layanan_id'],
                            'jumlah' => (int) $layananItem['jumlah'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                /**
                 * ============================
                 * 4) LAB (order_lab + detail)
                 * ============================
                 */
                $orderLabId = null;

                if (! empty($request->lab_tests)) {
                    $existing = DB::table('order_lab')
                        ->where('kunjungan_id', $kunjungan->id)
                        ->whereIn('status', ['Pending', 'Diproses'])
                        ->orderByDesc('id')
                        ->first();

                    if ($existing) {
                        $orderLabId = $existing->id;

                        DB::table('order_lab_detail')->where('order_lab_id', $orderLabId)->delete();

                        $firstLabTest = $request->lab_tests[0];
                        $tanggalPemeriksaan = $firstLabTest['tanggal_pemeriksaan'] ?? null;
                        $jamPemeriksaan = $firstLabTest['jam_pemeriksaan'] ?? null;

                        DB::table('order_lab')->where('id', $orderLabId)->update([
                            'dokter_id' => $dokter->id,
                            'pasien_id' => $kunjungan->pasien_id,
                            'tanggal_order' => now()->toDateString(),
                            'tanggal_pemeriksaan' => $tanggalPemeriksaan,
                            'jam_pemeriksaan' => $jamPemeriksaan,
                            'status' => 'Pending',
                            'updated_at' => now(),
                        ]);
                    } else {
                        $noOrderLab = 'LAB-'.date('Ymd').'-'.strtoupper(Str::random(6));

                        $firstLabTest = $request->lab_tests[0];
                        $tanggalPemeriksaan = $firstLabTest['tanggal_pemeriksaan'] ?? null;
                        $jamPemeriksaan = $firstLabTest['jam_pemeriksaan'] ?? null;

                        $orderLabId = DB::table('order_lab')->insertGetId([
                            'no_order_lab' => $noOrderLab,
                            'kunjungan_id' => $kunjungan->id,
                            'dokter_id' => $dokter->id,
                            'pasien_id' => $kunjungan->pasien_id,
                            'tanggal_order' => now()->toDateString(),
                            'tanggal_pemeriksaan' => $tanggalPemeriksaan,
                            'jam_pemeriksaan' => $jamPemeriksaan,
                            'status' => 'Pending',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    foreach ($request->lab_tests as $labTest) {
                        DB::table('order_lab_detail')->insert([
                            'order_lab_id' => $orderLabId,
                            'jenis_pemeriksaan_lab_id' => $labTest['lab_test_id'],
                            'status_pemeriksaan' => 'Pending',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                /**
                 * ============================
                 * 5) RADIOLOGI (order_radiologi + detail)
                 * ============================
                 */
                $orderRadiologiId = null;

                if (! empty($request->radiologi_tests)) {
                    $existingRad = DB::table('order_radiologi')
                        ->where('kunjungan_id', $kunjungan->id)
                        ->whereIn('status', ['Pending', 'Diproses'])
                        ->orderByDesc('id')
                        ->first();

                    if ($existingRad) {
                        $orderRadiologiId = $existingRad->id;

                        DB::table('order_radiologi_detail')->where('order_radiologi_id', $orderRadiologiId)->delete();

                        $firstRadiologiTest = $request->radiologi_tests[0];
                        $tanggalPemeriksaan = $firstRadiologiTest['tanggal_pemeriksaan'] ?? null;
                        $jamPemeriksaan = $firstRadiologiTest['jam_pemeriksaan'] ?? null;

                        DB::table('order_radiologi')->where('id', $orderRadiologiId)->update([
                            'dokter_id' => $dokter->id,
                            'pasien_id' => $kunjungan->pasien_id,
                            'tanggal_order' => now()->toDateString(),
                            'tanggal_pemeriksaan' => $tanggalPemeriksaan,
                            'jam_pemeriksaan' => $jamPemeriksaan,
                            'status' => 'Pending',
                            'updated_at' => now(),
                        ]);
                    } else {
                        $noOrderRadiologi = 'RAD-'.date('Ymd').'-'.strtoupper(Str::random(6));

                        $firstRadiologiTest = $request->radiologi_tests[0];
                        $tanggalPemeriksaan = $firstRadiologiTest['tanggal_pemeriksaan'] ?? null;
                        $jamPemeriksaan = $firstRadiologiTest['jam_pemeriksaan'] ?? null;

                        $orderRadiologiId = DB::table('order_radiologi')->insertGetId([
                            'no_order_radiologi' => $noOrderRadiologi,
                            'kunjungan_id' => $kunjungan->id,
                            'dokter_id' => $dokter->id,
                            'pasien_id' => $kunjungan->pasien_id,
                            'tanggal_order' => now()->toDateString(),
                            'tanggal_pemeriksaan' => $tanggalPemeriksaan,
                            'jam_pemeriksaan' => $jamPemeriksaan,
                            'status' => 'Pending',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    foreach ($request->radiologi_tests as $rad) {
                        DB::table('order_radiologi_detail')->insert([
                            'order_radiologi_id' => $orderRadiologiId,
                            'jenis_pemeriksaan_radiologi_id' => $rad['jenis_radiologi_id'],
                            'status_pemeriksaan' => 'Pending',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                /**
                 * ============================
                 * 6) KKLP EXTENSION (emr_kklp)
                 * ============================
                 */
                $kklp = null;

                if ((bool) $request->input('is_kklp') === true && is_array($request->kklp_data)) {
                    $kklpData = $request->kklp_data;

                    $existingKklp = EmrKklp::where('emr_id', $emr->id)->first();

                    $payload = [
                        'emr_id' => $emr->id,
                        'kunjungan_id' => $kunjungan->id,
                        'pasien_id' => $kunjungan->pasien_id,
                        'dokter_id' => $dokter->id,
                        'poli_id' => $kunjungan->poli_id,

                        'nama_dokter_form' => $kklpData['nama_dokter_form'] ?? $dokter->nama_dokter,
                        'nim_dokter' => $kklpData['nim_dokter'] ?? null,
                        'kasus_ke' => $kklpData['kasus_ke'] ?? null,
                        'tanggal_kasus' => $this->normalizeKklpDate($kklpData['tanggal_kasus'] ?? null),
                        'no_kasus' => $kklpData['no_kasus'] ?? ($existingKklp->no_kasus ?? ('KKLP-'.now()->format('Ymd').'-'.strtoupper(substr(md5(uniqid('', true)), 0, 6)))),
                        'telepon_pasien' => $kklpData['telepon_pasien'] ?? null,
                        'agama_pasien' => $kklpData['agama_pasien'] ?? null,
                        'pendidikan_terakhir_pasien' => $kklpData['pendidikan_terakhir_pasien'] ?? null,
                        'suku_bangsa_pasien' => $kklpData['suku_bangsa_pasien'] ?? null,
                        'tanggal_pemeriksaan' => $this->normalizeKklpDate($kklpData['tanggal_pemeriksaan'] ?? null),
                        'tanggal_homevisit' => $this->normalizeKklpDate($kklpData['tanggal_homevisit'] ?? null),

                        'riwayat_penyakit_sekarang' => $kklpData['riwayat_penyakit_sekarang'] ?? null,
                        'riwayat_penyakit_dahulu_detail' => $kklpData['riwayat_penyakit_dahulu_detail'] ?? null,
                        'riwayat_penyakit_keluarga_detail' => $kklpData['riwayat_penyakit_keluarga_detail'] ?? null,
                        'riwayat_personal_sosial' => $kklpData['riwayat_personal_sosial'] ?? null,
                        'review_sistem' => $kklpData['review_sistem'] ?? null,

                        'illness_pikiran' => $kklpData['illness_pikiran'] ?? null,
                        'illness_perasaan' => $kklpData['illness_perasaan'] ?? null,
                        'illness_efek_fungsi' => $kklpData['illness_efek_fungsi'] ?? null,
                        'illness_harapan' => $kklpData['illness_harapan'] ?? null,
                        'illness_kesimpulan' => $kklpData['illness_kesimpulan'] ?? null,

                        'genogram_keterangan' => $kklpData['genogram_keterangan'] ?? null,
                        'bentuk_keluarga' => $kklpData['bentuk_keluarga'] ?? null,
                        'siklus_kehidupan_keluarga' => $kklpData['siklus_kehidupan_keluarga'] ?? null,
                        'family_map_keterangan' => $kklpData['family_map_keterangan'] ?? null,
                        'apgar_score_total' => $kklpData['apgar_score_total'] ?? null,
                        'apgar_kesimpulan' => $kklpData['apgar_kesimpulan'] ?? null,
                        'family_life_line_ringkasan' => $kklpData['family_life_line_ringkasan'] ?? null,

                        'keadaan_umum' => $kklpData['keadaan_umum'] ?? null,
                        'kesadaran' => $kklpData['kesadaran'] ?? null,
                        'tekanan_darah' => $kklpData['tekanan_darah'] ?? $request->tekanan_darah ?? null,
                        'nadi' => $kklpData['nadi'] ?? $request->nadi ?? null,
                        'respirasi' => $kklpData['respirasi'] ?? null,
                        'suhu' => $kklpData['suhu'] ?? null,
                        'tinggi_badan' => $kklpData['tinggi_badan'] ?? $request->tinggi_badan ?? null,
                        'berat_badan' => $kklpData['berat_badan'] ?? $request->berat_badan ?? null,
                        'imt' => $kklpData['imt'] ?? $request->imt ?? null,

                        'lingkar_pinggang' => $kklpData['lingkar_pinggang'] ?? null,
                        'lingkar_panggul' => $kklpData['lingkar_panggul'] ?? null,
                        'lingkar_lengan_atas' => $kklpData['lingkar_lengan_atas'] ?? null,
                        'status_gizi' => $kklpData['status_gizi'] ?? null,
                        'waist_hip_ratio' => $kklpData['waist_hip_ratio'] ?? null,

                        'pemeriksaan_kulit' => $kklpData['pemeriksaan_kulit'] ?? null,
                        'pemeriksaan_kelenjar_limfe' => $kklpData['pemeriksaan_kelenjar_limfe'] ?? null,
                        'pemeriksaan_otot' => $kklpData['pemeriksaan_otot'] ?? null,
                        'pemeriksaan_tulang' => $kklpData['pemeriksaan_tulang'] ?? null,
                        'pemeriksaan_sendi' => $kklpData['pemeriksaan_sendi'] ?? null,
                        'pemeriksaan_kepala' => $kklpData['pemeriksaan_kepala'] ?? null,
                        'pemeriksaan_mata' => $kklpData['pemeriksaan_mata'] ?? null,
                        'pemeriksaan_hidung' => $kklpData['pemeriksaan_hidung'] ?? null,
                        'pemeriksaan_telinga' => $kklpData['pemeriksaan_telinga'] ?? null,
                        'pemeriksaan_mulut_gigi' => $kklpData['pemeriksaan_mulut_gigi'] ?? null,
                        'pemeriksaan_tenggorokan' => $kklpData['pemeriksaan_tenggorokan'] ?? null,
                        'pemeriksaan_leher' => $kklpData['pemeriksaan_leher'] ?? null,
                        'thorax_paru_inspeksi' => $kklpData['thorax_paru_inspeksi'] ?? null,
                        'thorax_paru_palpasi' => $kklpData['thorax_paru_palpasi'] ?? null,
                        'thorax_paru_perkusi' => $kklpData['thorax_paru_perkusi'] ?? null,
                        'thorax_paru_auskultasi' => $kklpData['thorax_paru_auskultasi'] ?? null,
                        'thorax_jantung_inspeksi' => $kklpData['thorax_jantung_inspeksi'] ?? null,
                        'thorax_jantung_palpasi' => $kklpData['thorax_jantung_palpasi'] ?? null,
                        'thorax_jantung_perkusi' => $kklpData['thorax_jantung_perkusi'] ?? null,
                        'thorax_jantung_auskultasi' => $kklpData['thorax_jantung_auskultasi'] ?? null,
                        'abdomen_inspeksi' => $kklpData['abdomen_inspeksi'] ?? null,
                        'abdomen_palpasi' => $kklpData['abdomen_palpasi'] ?? null,
                        'abdomen_perkusi' => $kklpData['abdomen_perkusi'] ?? null,
                        'abdomen_auskultasi' => $kklpData['abdomen_auskultasi'] ?? null,
                        'anogenital' => $kklpData['anogenital'] ?? null,
                        'tambahan_pemeriksaan_khusus' => $kklpData['tambahan_pemeriksaan_khusus'] ?? null,

                        'ringkasan_laboratorium' => $kklpData['ringkasan_laboratorium'] ?? null,
                        'ringkasan_radiologi' => $kklpData['ringkasan_radiologi'] ?? null,
                        'ringkasan_penunjang_lain' => $kklpData['ringkasan_penunjang_lain'] ?? null,
                        'patogenesis_patofisiologi' => $kklpData['patogenesis_patofisiologi'] ?? null,
                        'diagnosis_klinis_banding' => $kklpData['diagnosis_klinis_banding'] ?? null,
                        'diagnosis_holistik' => $kklpData['diagnosis_holistik'] ?? null,
                        'uraian_diagnosis_holistik' => $kklpData['uraian_diagnosis_holistik'] ?? null,

                        'upaya_promotif' => $kklpData['upaya_promotif'] ?? null,
                        'upaya_preventif' => $kklpData['upaya_preventif'] ?? null,
                        'upaya_kuratif' => $kklpData['upaya_kuratif'] ?? null,
                        'upaya_rehabilitatif' => $kklpData['upaya_rehabilitatif'] ?? null,
                        'upaya_paliatif' => $kklpData['upaya_paliatif'] ?? null,
                        'copc_plan_ringkasan' => $kklpData['copc_plan_ringkasan'] ?? null,
                        'kesimpulan_phbs' => $kklpData['kesimpulan_phbs'] ?? null,

                        'kondisi_rumah' => $kklpData['kondisi_rumah'] ?? null,
                        'lingkungan_sekitar_rumah' => $kklpData['lingkungan_sekitar_rumah'] ?? null,
                        'catatan_tambahan_homevisit' => $kklpData['catatan_tambahan_homevisit'] ?? null,

                        'nilai_humanisme' => $kklpData['nilai_humanisme'] ?? null,
                        'nilai_komunikasi' => $kklpData['nilai_komunikasi'] ?? null,
                        'nilai_pemeriksaan_fisik' => $kklpData['nilai_pemeriksaan_fisik'] ?? null,
                        'nilai_penalaran_klinis' => $kklpData['nilai_penalaran_klinis'] ?? null,
                        'nilai_diagnosis_holistik' => $kklpData['nilai_diagnosis_holistik'] ?? null,
                        'nilai_pengelolaan_komprehensif' => $kklpData['nilai_pengelolaan_komprehensif'] ?? null,
                        'nilai_edukasi_konseling' => $kklpData['nilai_edukasi_konseling'] ?? null,
                        'nilai_organisasi_efisiensi' => $kklpData['nilai_organisasi_efisiensi'] ?? null,
                        'nilai_kompetensi_keseluruhan' => $kklpData['nilai_kompetensi_keseluruhan'] ?? null,
                        'skor_total' => $kklpData['skor_total'] ?? null,
                        'skor_akhir' => $kklpData['skor_akhir'] ?? null,
                        'komentar_pembimbing' => $kklpData['komentar_pembimbing'] ?? null,
                        'komentar_dokter_residen' => $kklpData['komentar_dokter_residen'] ?? null,
                        'status_form' => $kklpData['status_form'] ?? 'draft',
                    ];

                    $kklp = EmrKklp::updateOrCreate(
                        ['emr_id' => $emr->id],
                        $payload
                    );

                    $this->syncKklpNestedData($kklp, $kklpData);
                }

                /**
                 * ============================
                 * 7) STATUS KUNJUNGAN -> Payment
                 * ============================
                 */
                $kunjungan->update(['status' => 'Payment']);

                /**
                 * ============================
                 * 8) PEMBAYARAN HEADER + DETAIL
                 * ============================
                 */
                $existingPembayaran = Pembayaran::where('emr_id', $emr->id)->first();

                $pembayaran = Pembayaran::updateOrCreate(
                    ['emr_id' => $emr->id],
                    [
                        'kode_transaksi' => $existingPembayaran?->kode_transaksi ?? strtoupper(uniqid('TRX_')),
                        'tanggal_pembayaran' => null,
                        'status' => 'Belum Bayar',
                        'metode_pembayaran_id' => null,
                        'bukti_pembayaran' => null,
                        'total_tagihan' => 0,
                        'diskon_tipe' => $existingPembayaran?->diskon_tipe,
                        'diskon_nilai' => $existingPembayaran?->diskon_nilai ?? 0,
                        'total_setelah_diskon' => null,
                        'uang_yang_diterima' => 0,
                        'kembalian' => 0,
                        'catatan' => 'Menunggu pembayaran di kasir - EMR dilengkapi dokter',
                    ]
                );

                DB::table('pembayaran_detail')->where('pembayaran_id', $pembayaran->id)->delete();

                $total = 0;
                $totalLayanan = 0;
                $totalObat = 0;

                $insertDetail = function (array $data) use ($pembayaran, &$total) {
                    $row = [
                        'pembayaran_id' => $pembayaran->id,
                        'nama_item' => $data['nama_item'],
                        'qty' => (int) $data['qty'],
                        'harga' => (float) $data['harga'],
                        'subtotal' => (float) $data['subtotal'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $row['layanan_id'] = $data['layanan_id'] ?? null;
                    $row['resep_obat_id'] = $data['resep_obat_id'] ?? null;
                    $row['order_lab_detail_id'] = $data['order_lab_detail_id'] ?? null;
                    $row['order_radiologi_detail_id'] = $data['order_radiologi_detail_id'] ?? null;

                    if (array_key_exists('hasil_lab_id', $data)) {
                        $row['hasil_lab_id'] = $data['hasil_lab_id'] ?: null;
                    }

                    DB::table('pembayaran_detail')->insert($row);
                    $total += (float) $row['subtotal'];
                };

                $layananRows = DB::table('kunjungan_layanan as kl')
                    ->join('layanan as l', 'l.id', '=', 'kl.layanan_id')
                    ->where('kl.kunjungan_id', $kunjungan->id)
                    ->select(
                        'kl.layanan_id',
                        'kl.jumlah',
                        'l.nama_layanan',
                        'l.harga_sebelum_diskon',
                        'l.harga_setelah_diskon'
                    )
                    ->get();

                foreach ($layananRows as $row) {
                    $harga = (float) ($row->harga_setelah_diskon ?? $row->harga_sebelum_diskon ?? 0);
                    $qty = (int) ($row->jumlah ?? 1);
                    $subtotal = $harga * $qty;

                    $insertDetail([
                        'layanan_id' => $row->layanan_id,
                        'nama_item' => 'Layanan: '.$row->nama_layanan,
                        'qty' => $qty,
                        'harga' => $harga,
                        'subtotal' => $subtotal,
                    ]);

                    $totalLayanan += $subtotal;
                }

                if (! empty($resepId)) {
                    $obatRows = DB::table('resep_obat as ro')
                        ->join('obat as o', 'o.id', '=', 'ro.obat_id')
                        ->where('ro.resep_id', $resepId)
                        ->select('ro.id as resep_obat_id', 'ro.jumlah', 'o.nama_obat', 'o.harga_jual_obat')
                        ->get();

                    foreach ($obatRows as $row) {
                        $harga = (float) ($row->harga_jual_obat ?? 0);
                        $qty = (int) ($row->jumlah ?? 1);
                        $subtotal = $harga * $qty;

                        $insertDetail([
                            'resep_obat_id' => $row->resep_obat_id,
                            'nama_item' => 'Obat: '.$row->nama_obat,
                            'qty' => $qty,
                            'harga' => $harga,
                            'subtotal' => $subtotal,
                        ]);

                        $totalObat += $subtotal;
                    }
                }

                if (! empty($orderLabId)) {
                    $labRows = DB::table('order_lab_detail as old')
                        ->join('jenis_pemeriksaan_lab as jpl', 'jpl.id', '=', 'old.jenis_pemeriksaan_lab_id')
                        ->where('old.order_lab_id', $orderLabId)
                        ->select('old.id as order_lab_detail_id', 'jpl.nama_pemeriksaan', 'jpl.harga_pemeriksaan_lab')
                        ->get();

                    foreach ($labRows as $row) {
                        $harga = (float) ($row->harga_pemeriksaan_lab ?? 0);
                        $qty = 1;
                        $subtotal = $harga * $qty;

                        $insertDetail([
                            'order_lab_detail_id' => $row->order_lab_detail_id,
                            'nama_item' => 'Lab: '.$row->nama_pemeriksaan,
                            'qty' => $qty,
                            'harga' => $harga,
                            'subtotal' => $subtotal,
                        ]);
                    }
                }

                if (! empty($orderRadiologiId)) {
                    $radRows = DB::table('order_radiologi_detail as ord')
                        ->join('jenis_pemeriksaan_radiologi as jpr', 'jpr.id', '=', 'ord.jenis_pemeriksaan_radiologi_id')
                        ->where('ord.order_radiologi_id', $orderRadiologiId)
                        ->select(
                            'ord.id as order_radiologi_detail_id',
                            'jpr.nama_pemeriksaan',
                            'jpr.harga_pemeriksaan_radiologi'
                        )
                        ->get();

                    foreach ($radRows as $row) {
                        $harga = (float) ($row->harga_pemeriksaan_radiologi ?? 0);
                        $qty = 1;
                        $subtotal = $harga * $qty;

                        $insertDetail([
                            'order_radiologi_detail_id' => $row->order_radiologi_detail_id,
                            'nama_item' => 'Radiologi: '.$row->nama_pemeriksaan,
                            'qty' => $qty,
                            'harga' => $harga,
                            'subtotal' => $subtotal,
                        ]);
                    }
                }

                $pembayaran->update([
                    'total_tagihan' => $total,
                ]);

                return [
                    'emr' => $emr->fresh(['perawat']),
                    'kunjungan' => $kunjungan->fresh(),
                    'pembayaran' => $pembayaran->fresh(),
                    'billing_info' => [
                        'total' => $total,
                        'detail_count' => DB::table('pembayaran_detail')
                            ->where('pembayaran_id', $pembayaran->id)
                            ->count(),
                    ],
                    'order_lab_id' => $orderLabId ?? null,
                    'order_radiologi_id' => $orderRadiologiId ?? null,
                    'kklp_id' => $kklp?->id,
                    'is_kklp' => $kklp ? true : false,
                ];
            });

            \App\Helpers\NotificationHelper::kirimNotifikasiEMRSelesai($kunjungan, $result);

            return response()->json([
                'success' => true,
                'message' => 'EMR berhasil dilengkapi. Kasir akan input diskon final.',
                'data' => $result,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melengkapi EMR: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getRiwayatPasienDiperiksa()
    {
        try {
            $userId = Auth::id();
            $dokter = Dokter::with(['poli', 'jenisSpesialis'])->where('user_id', $userId)->first();

            if (! $dokter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dokter tidak ditemukan',
                ], 404);
            }

            $kunjunganList = Kunjungan::with([
                'pasien:id,nama_pasien,no_emr',
                'poli:id,nama_poli',
                'dokter:id,nama_dokter',
                'emr.perawat:id,nama_perawat,foto_perawat,no_hp_perawat',
                'emr.emrKklp',
                'emr.pengkajianAwalPenyakitDalam.riwayat',
                'emr.pengkajianAwalPenyakitDalam.penunjang',
            ])
                ->where('dokter_id', $dokter->id)
                ->whereHas('emr')
                ->orderByDesc('tanggal_kunjungan')
                ->orderByDesc('id')
                ->get();

            $riwayatPasien = $kunjunganList->map(function ($kunjungan) use ($dokter) {
                $emr = $kunjungan->emr;
                $kklp = $emr?->emrKklp;
                $penyakitDalam = $emr?->pengkajianAwalPenyakitDalam;

                $isPenyakitDalam = $this->isKunjunganPenyakitDalam($kunjungan);
                $isKklp = $kklp ? true : $this->isDokterKKLP($dokter);

                $formType = 'general';
                if ($penyakitDalam || $isPenyakitDalam) {
                    $formType = 'penyakit_dalam';
                } elseif ($kklp || $isKklp) {
                    $formType = 'kklp';
                }

                return [
                    'id' => (int) $kunjungan->id,
                    'pasien_id' => (int) $kunjungan->pasien_id,
                    'poli_id' => (int) $kunjungan->poli_id,
                    'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                    'no_antrian' => $kunjungan->no_antrian ? (string) $kunjungan->no_antrian : null,
                    'status' => 'draft',
                    'status_kunjungan' => $kunjungan->status,
                    'keluhan_awal' => $kunjungan->keluhan_awal,
                    'created_at' => $kunjungan->created_at,
                    'updated_at' => $kunjungan->updated_at,

                    'form_type' => $formType,
                    'is_kklp' => $kklp ? true : false,
                    'is_penyakit_dalam' => $penyakitDalam ? true : $isPenyakitDalam,

                    'pasien' => [
                        'id' => (int) $kunjungan->pasien->id,
                        'nama_pasien' => $kunjungan->pasien->nama_pasien,
                        'no_emr' => $kunjungan->pasien->no_emr,
                    ],

                    'poli' => [
                        'id' => (int) $kunjungan->poli->id,
                        'nama_poli' => $kunjungan->poli->nama_poli,
                    ],

                    'emr' => [
                        'id' => $emr ? (int) $emr->id : null,
                        'dokter_id' => $emr?->dokter_id ? (int) $emr->dokter_id : null,
                        'diagnosis' => $emr?->diagnosis,
                        'keluhan_utama' => $emr?->keluhan_utama,
                        'riwayat_penyakit_sekarang' => $emr?->riwayat_penyakit_sekarang ?? null,
                        'riwayat_penyakit_dahulu' => $emr?->riwayat_penyakit_dahulu,
                        'riwayat_penyakit_keluarga' => $emr?->riwayat_penyakit_keluarga,
                        'tekanan_darah' => $emr?->tekanan_darah,
                        'suhu_tubuh' => $emr?->suhu_tubuh,
                        'nadi' => $emr?->nadi,
                        'pernapasan' => $emr?->pernapasan,
                        'saturasi_oksigen' => $emr?->saturasi_oksigen,
                        'tanggal_pemeriksaan_perawat' => $emr?->created_at,
                        'perawat' => [
                            'id' => $emr?->perawat?->id ? (int) $emr->perawat->id : null,
                            'nama_perawat' => $emr?->perawat?->nama_perawat,
                            'foto_perawat' => $emr?->perawat?->foto_perawat,
                            'no_hp_perawat' => $emr?->perawat?->no_hp_perawat,
                        ],
                    ],

                    'dokter_pemeriksa' => [
                        'id' => $kunjungan->dokter ? (int) $kunjungan->dokter->id : null,
                        'nama_dokter' => $kunjungan->dokter?->nama_dokter,
                    ],

                    'perawat' => [
                        'id' => $emr?->perawat?->id ? (int) $emr->perawat->id : null,
                        'nama_perawat' => $emr?->perawat?->nama_perawat,
                        'foto_perawat' => $emr?->perawat?->foto_perawat,
                        'no_hp_perawat' => $emr?->perawat?->no_hp_perawat,
                    ],

                    'tanggal_pemeriksaan_perawat' => $emr?->created_at,
                    'finalized_at' => null,
                    'can_edit' => true,
                    'emr_id' => $emr ? (int) $emr->id : null,

                    'kklp' => $kklp ? [
                        'id' => $kklp->id,
                        'form' => $kklp,
                    ] : null,

                    'pengkajian_awal_penyakit_dalam' => $this->mapPengkajianPenyakitDalamForMobile($penyakitDalam),
                ];
            });

            return response()->json([
                'success' => true,
                'status' => 200,
                'data' => $riwayatPasien->values()->toArray(),
                'total_pasien' => $riwayatPasien->count(),
                'dokter_info' => [
                    'id' => $dokter->id,
                    'nama_dokter' => $dokter->nama_dokter,
                    'filtering_method' => 'kunjungan_dokter_with_emr',
                ],
                'message' => 'Berhasil mengambil riwayat pasien',
            ], 200);

        } catch (\Throwable $e) {
            Log::error('ERROR getRiwayatPasienDiperiksa: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function mapDentalForMobile(?DentalExamination $dental): ?array
    {
        if (! $dental) {
            return null;
        }

        return [
            'id' => $dental->id,
            'pasien_id' => $dental->pasien_id,
            'kunjungan_id' => $dental->kunjungan_id,
            'order_layanan_id' => $dental->order_layanan_id,
            'tanggal_kunjungan' => $dental->tanggal_kunjungan,
            'dpjp_nama' => $dental->dpjp_nama,
            'ppjp_nama' => $dental->ppjp_nama,

            'gigi_dewasa_atas' => $dental->gigi_dewasa_atas,
            'gigi_dewasa_bawah' => $dental->gigi_dewasa_bawah,
            'gigi_anak_atas' => $dental->gigi_anak_atas,
            'gigi_anak_bawah' => $dental->gigi_anak_bawah,

            'occlusi' => $dental->occlusi,
            'torus_palatinus' => $dental->torus_palatinus,
            'torus_mandibularis' => $dental->torus_mandibularis,
            'palatum' => $dental->palatum,

            'diastema_ada' => $dental->diastema_ada,
            'diastema_keterangan' => $dental->diastema_keterangan,

            'gigi_anomali_ada' => $dental->gigi_anomali_ada,
            'gigi_anomali_keterangan' => $dental->gigi_anomali_keterangan,

            'lain_lain' => $dental->lain_lain,

            'd_index' => $dental->d_index,
            'm_index' => $dental->m_index,
            'f_index' => $dental->f_index,

            'jumlah_foto' => $dental->jumlah_foto,
            'jenis_foto' => $dental->jenis_foto,
            'jumlah_rontgen' => $dental->jumlah_rontgen,
            'jenis_rontgen' => $dental->jenis_rontgen,

            'diperiksa_oleh' => $dental->diperiksa_oleh,
            'tanggal_pemeriksaan' => $dental->tanggal_pemeriksaan,
            'status' => $dental->status,

            'created_at' => $dental->created_at,
            'updated_at' => $dental->updated_at,
        ];
    }

public function getDetailRiwayatPasienMobile(Request $request, $kunjunganId)
{
    try {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak terautentikasi',
            ], 401);
        }

        $pasien = Pasien::where('user_id', $user->id)->first();

        if (! $pasien) {
            return response()->json([
                'success' => false,
                'message' => 'Data pasien tidak ditemukan',
            ], 404);
        }

        $kunjungan = Kunjungan::with([
            'pasien',
            'poli',
            'dokter.jenisSpesialis',
            'dokter.poli',
            'emr' => function ($query) {
                $query->with([
                    'resep.obat',
                    'perawat',
                    'dokter.jenisSpesialis',

                    // KKLP
                    'emrKklp.orangtua',
                    'emrKklp.heteroanamnesis',
                    'emrKklp.familyApgar',
                    'emrKklp.familyScreem',
                    'emrKklp.anggotaKeluarga',
                    'emrKklp.homevisit',
                    'emrKklp.familyPlan',
                    'emrKklp.copcPlan',
                    'emrKklp.ekstremitas',

                    // Penyakit Dalam
                    'pengkajianAwalPenyakitDalam',
                    'pengkajianAwalPenyakitDalam.riwayat',
                    'pengkajianAwalPenyakitDalam.penunjang',
                ]);
            },
        ])
            ->where('id', $kunjunganId)
            ->where('pasien_id', $pasien->id)
            ->whereHas('emr')
            ->first();

        if (! $kunjungan) {
            return response()->json([
                'success' => false,
                'message' => 'Detail riwayat tidak ditemukan.',
            ], 404);
        }

        $emr = $kunjungan->emr;
        $kklp = $emr?->emrKklp;
        $penyakitDalam = $emr?->pengkajianAwalPenyakitDalam;

        // Ambil data dental langsung dari kunjungan
        $dental = DentalExamination::where('kunjungan_id', $kunjungan->id)->first();

        $formType = $this->resolveFormType($kunjungan->dokter, $kunjungan, $emr, $dental);

        $isKklp = ! is_null($kklp);
        $isPenyakitDalam = ! is_null($penyakitDalam);
        $isDental = ! is_null($dental);

        $responseData = [
            'id' => $kunjungan->id,
            'kunjungan_id' => $kunjungan->id,
            'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
            'no_antrian' => $kunjungan->no_antrian,
            'status' => $kunjungan->status,
            'keluhan_awal' => $kunjungan->keluhan_awal,
            'created_at' => $kunjungan->created_at,
            'updated_at' => $kunjungan->updated_at,

            'form_type' => $formType,
            'is_kklp' => $isKklp,
            'is_penyakit_dalam' => $isPenyakitDalam,
            'is_dental' => $isDental,

            'pasien' => $kunjungan->pasien ? [
                'id' => $kunjungan->pasien->id,
                'nama_pasien' => $kunjungan->pasien->nama_pasien,
                'no_emr' => $kunjungan->pasien->no_emr,
                'alamat' => $kunjungan->pasien->alamat,
                'tanggal_lahir' => $kunjungan->pasien->tanggal_lahir,
                'jenis_kelamin' => $kunjungan->pasien->jenis_kelamin,
                'no_hp' => $kunjungan->pasien->no_hp_pasien ?? null,
                'nik' => $kunjungan->pasien->nik ?? null,
                'pekerjaan' => $kunjungan->pasien->pekerjaan ?? null,
            ] : null,

            'poli' => $kunjungan->poli ? [
                'id' => $kunjungan->poli->id,
                'nama_poli' => $kunjungan->poli->nama_poli,
            ] : null,

            'dokter' => $kunjungan->dokter ? [
                'id' => $kunjungan->dokter->id,
                'nama_dokter' => $kunjungan->dokter->nama_dokter,
                'foto_dokter' => $kunjungan->dokter->foto_dokter,
                'no_hp' => $kunjungan->dokter->no_hp,
                'pengalaman' => $kunjungan->dokter->pengalaman,
                'jenis_spesialis' => $kunjungan->dokter->jenisSpesialis ? [
                    'id' => $kunjungan->dokter->jenisSpesialis->id,
                    'nama_spesialis' => $kunjungan->dokter->jenisSpesialis->nama_spesialis,
                ] : null,
                'poli' => $kunjungan->poli ? [
                    'id' => $kunjungan->poli->id,
                    'nama_poli' => $kunjungan->poli->nama_poli,
                ] : null,
            ] : null,

            'emr' => $emr ? [
                'id' => $emr->id,
                'keluhan_utama' => $emr->keluhan_utama,
                'diagnosis' => $emr->diagnosis,
                'riwayat_penyakit_sekarang' => $emr->riwayat_penyakit_sekarang ?? null,
                'riwayat_penyakit_dahulu' => $emr->riwayat_penyakit_dahulu,
                'riwayat_penyakit_keluarga' => $emr->riwayat_penyakit_keluarga,
                'tekanan_darah' => $emr->tekanan_darah,
                'suhu_tubuh' => $emr->suhu_tubuh,
                'nadi' => $emr->nadi,
                'pernapasan' => $emr->pernapasan,
                'saturasi_oksigen' => $emr->saturasi_oksigen,
                'tinggi_badan' => $emr->tinggi_badan ?? null,
                'berat_badan' => $emr->berat_badan ?? null,
                'imt' => $emr->imt ?? null,
                'tanggal_pemeriksaan' => $emr->tanggal ?? null,
                'waktu_pemeriksaan' => $emr->waktu ?? null,
                'tanda_vital' => [
                    'tekanan_darah' => $emr->tekanan_darah,
                    'suhu_tubuh' => $emr->suhu_tubuh,
                    'nadi' => $emr->nadi,
                    'pernapasan' => $emr->pernapasan,
                    'saturasi_oksigen' => $emr->saturasi_oksigen,
                    'tinggi_badan' => $emr->tinggi_badan ?? null,
                    'berat_badan' => $emr->berat_badan ?? null,
                    'imt' => $emr->imt ?? null,
                ],
                'perawat' => $emr->perawat ? [
                    'id' => $emr->perawat->id,
                    'nama_perawat' => $emr->perawat->nama_perawat,
                    'foto_perawat' => $emr->perawat->foto_perawat,
                    'no_hp_perawat' => $emr->perawat->no_hp_perawat,
                ] : null,
            ] : null,

            // khusus form
            'dental' => $this->mapDentalForMobile($dental),
            'pengkajian_awal_penyakit_dalam' => $this->mapPengkajianPenyakitDalamForMobile($penyakitDalam),
            'kklp' => $kklp ? $kklp->toArray() : null,
        ];

        if ($emr && $emr->resep && $emr->resep->obat) {
            $responseData['resep_obat'] = $emr->resep->obat->map(function ($item) use ($emr) {
                return [
                    'id' => $item->id,
                    'nama_obat' => $item->nama_obat ?? null,
                    'jumlah' => $item->pivot->jumlah ?? null,
                    'dosis' => $item->pivot->dosis ?? null,
                    'keterangan' => $item->pivot->keterangan ?? null,
                    'status' => $emr->resep->status ?? null,
                ];
            })->values()->toArray();
        } else {
            $responseData['resep_obat'] = [];
        }

        $layananRows = DB::table('kunjungan_layanan as kl')
            ->join('layanan as l', 'l.id', '=', 'kl.layanan_id')
            ->where('kl.kunjungan_id', $kunjungan->id)
            ->select(
                'l.id',
                'l.nama_layanan',
                'kl.jumlah',
                DB::raw('COALESCE(l.harga_setelah_diskon, l.harga_sebelum_diskon, 0) as harga_layanan')
            )
            ->get();

        $responseData['layanan'] = $layananRows->map(function ($item) {
            return [
                'id' => $item->id,
                'nama_layanan' => $item->nama_layanan,
                'jumlah' => $item->jumlah,
                'harga_layanan' => $item->harga_layanan,
            ];
        })->values()->toArray();

        return response()->json([
            'success' => true,
            'message' => 'Detail riwayat pasien berhasil diambil',
            'data' => $responseData,
        ], 200);

    } catch (\Throwable $e) {
        Log::error('ERROR getDetailRiwayatPasienMobile: '.$e->getMessage(), [
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengambil detail riwayat pasien: '.$e->getMessage(),
        ], 500);
    }
}

    public function getDetailRiwayatPasien($kunjunganId)
    {
        try {
            $userId = Auth::id();
            $dokter = Dokter::where('user_id', $userId)->first();

            if (! $dokter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dokter tidak ditemukan',
                ], 404);
            }

            $kunjungan = Kunjungan::with([
                'pasien',
                'poli',
                'dokter.jenisSpesialis',
                'emr' => function ($query) {
                    $query->with([
                        'resep.obat',
                        'perawat',
                        'dokter.jenisSpesialis',
                        'emrKklp.orangtua',
                        'emrKklp.heteroanamnesis',
                        'emrKklp.familyApgar',
                        'emrKklp.familyScreem',
                        'emrKklp.anggotaKeluarga',
                        'emrKklp.homevisit',
                        'emrKklp.familyPlan',
                        'emrKklp.copcPlan',
                        'emrKklp.ekstremitas',
                    ]);
                },
            ])
                ->where('id', $kunjunganId)
                ->whereHas('emr')
                ->first();

            if (! $kunjungan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detail riwayat tidak ditemukan.',
                ], 404);
            }

            $emr = $kunjungan->emr;
            $kklp = $emr?->emrKklp;

            $pengkajianPenyakitDalam = null;
            if ($emr) {
                $pengkajianPenyakitDalam = EmrPengkajianAwalPenyakitDalam::with([
                    'dokter',
                    'riwayat',
                    'penunjang',
                ])->where('emr_id', $emr->id)->first();
            }

            // ✅ TAMBAHKAN INI - Fetch Dental Examination
            $dentalExamination = null;
            if ($emr) {
                $dentalExamination = \App\Models\DentalExamination::with(['pasien', 'kunjungan'])
                    ->where('kunjungan_id', $kunjungan->id)
                    ->first();
            }

            $isKklp = $kklp ? true : false;
            $formType = $isKklp ? 'kklp' : 'general';

            $responseData = [
                'id' => $kunjungan->id,
                'kunjungan_id' => $kunjungan->id,
                'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                'no_antrian' => $kunjungan->no_antrian,
                'status' => $kunjungan->status,
                'keluhan_awal' => $kunjungan->keluhan_awal,
                'created_at' => $kunjungan->created_at,
                'updated_at' => $kunjungan->updated_at,

                'form_type' => $formType,
                'is_kklp' => $isKklp,

                'pasien' => $kunjungan->pasien ? [
                    'id' => $kunjungan->pasien->id,
                    'nama_pasien' => $kunjungan->pasien->nama_pasien,
                    'no_emr' => $kunjungan->pasien->no_emr,
                    'alamat' => $kunjungan->pasien->alamat,
                    'tanggal_lahir' => $kunjungan->pasien->tanggal_lahir,
                    'jenis_kelamin' => $kunjungan->pasien->jenis_kelamin,
                    'no_hp' => $kunjungan->pasien->no_hp_pasien ?? null,
                    'nik' => $kunjungan->pasien->nik ?? null,
                    'pekerjaan' => $kunjungan->pasien->pekerjaan ?? null,
                ] : null,

                'poli' => $kunjungan->poli ? [
                    'id' => $kunjungan->poli->id,
                    'nama_poli' => $kunjungan->poli->nama_poli,
                ] : null,

                'dokter' => $kunjungan->dokter ? [
                    'id' => $kunjungan->dokter->id,
                    'nama_dokter' => $kunjungan->dokter->nama_dokter,
                    'foto_dokter' => $kunjungan->dokter->foto_dokter,
                    'no_hp' => $kunjungan->dokter->no_hp,
                    'pengalaman' => $kunjungan->dokter->pengalaman,
                    'jenis_spesialis' => $kunjungan->dokter->jenisSpesialis ? [
                        'id' => $kunjungan->dokter->jenisSpesialis->id,
                        'nama_spesialis' => $kunjungan->dokter->jenisSpesialis->nama_spesialis,
                    ] : null,
                    'poli' => $kunjungan->poli ? [
                        'id' => $kunjungan->poli->id,
                        'nama_poli' => $kunjungan->poli->nama_poli,
                    ] : null,
                ] : null,

                'emr' => $emr ? [
                    'id' => $emr->id,
                    'keluhan_utama' => $emr->keluhan_utama,
                    'diagnosis' => $emr->diagnosis,
                    'riwayat_penyakit_sekarang' => $emr->riwayat_penyakit_sekarang ?? null,
                    'riwayat_penyakit_dahulu' => $emr->riwayat_penyakit_dahulu,
                    'riwayat_penyakit_keluarga' => $emr->riwayat_penyakit_keluarga,
                    'tekanan_darah' => $emr->tekanan_darah,
                    'suhu_tubuh' => $emr->suhu_tubuh,
                    'nadi' => $emr->nadi,
                    'pernapasan' => $emr->pernapasan,
                    'saturasi_oksigen' => $emr->saturasi_oksigen,
                    'tinggi_badan' => $emr->tinggi_badan ?? null,
                    'berat_badan' => $emr->berat_badan ?? null,
                    'imt' => $emr->imt ?? null,
                    'tanggal_pemeriksaan' => $emr->tanggal ?? null,
                    'waktu_pemeriksaan' => $emr->waktu ?? null,

                    'tanda_vital' => [
                        'tekanan_darah' => $emr->tekanan_darah,
                        'suhu_tubuh' => $emr->suhu_tubuh,
                        'nadi' => $emr->nadi,
                        'pernapasan' => $emr->pernapasan,
                        'saturasi_oksigen' => $emr->saturasi_oksigen,
                        'tinggi_badan' => $emr->tinggi_badan ?? null,
                        'berat_badan' => $emr->berat_badan ?? null,
                        'imt' => $emr->imt ?? null,
                    ],

                    'perawat' => $emr->perawat ? [
                        'id' => $emr->perawat->id,
                        'nama_perawat' => $emr->perawat->nama_perawat,
                        'foto_perawat' => $emr->perawat->foto_perawat,
                        'no_hp_perawat' => $emr->perawat->no_hp_perawat,
                    ] : null,
                ] : null,
            ];

            // RESEP OBAT
            if ($emr && $emr->resep && $emr->resep->obat) {
                $responseData['resep_obat'] = $emr->resep->obat->map(function ($item) use ($emr) {
                    return [
                        'id' => $item->id,
                        'nama_obat' => $item->nama_obat ?? null,
                        'jumlah' => $item->pivot->jumlah ?? null,
                        'dosis' => $item->pivot->dosis ?? null,
                        'keterangan' => $item->pivot->keterangan ?? null,
                        'status' => $emr->resep->status ?? null,
                    ];
                })->values()->toArray();
            } else {
                $responseData['resep_obat'] = [];
            }

            // LAYANAN
            $layananRows = DB::table('kunjungan_layanan as kl')
                ->join('layanan as l', 'l.id', '=', 'kl.layanan_id')
                ->where('kl.kunjungan_id', $kunjungan->id)
                ->select(
                    'l.id',
                    'l.nama_layanan',
                    'kl.jumlah',
                    DB::raw('COALESCE(l.harga_setelah_diskon, l.harga_sebelum_diskon, 0) as harga_layanan')
                )
                ->get();

            $responseData['layanan'] = $layananRows->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_layanan' => $item->nama_layanan,
                    'jumlah' => $item->jumlah,
                    'harga_layanan' => $item->harga_layanan,
                ];
            })->values()->toArray();

            // LAB TESTS
            $labTests = DB::table('order_lab as ol')
                ->join('order_lab_detail as old', 'ol.id', '=', 'old.order_lab_id')
                ->join('jenis_pemeriksaan_lab as jpl', 'jpl.id', '=', 'old.jenis_pemeriksaan_lab_id')
                ->where('ol.kunjungan_id', $kunjungan->id)
                ->select(
                    'old.id',
                    'jpl.nama_pemeriksaan',
                    'ol.tanggal_pemeriksaan',
                    'ol.jam_pemeriksaan',
                    'old.status_pemeriksaan as status'
                )
                ->get();

            $responseData['lab_tests'] = $labTests->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_pemeriksaan' => $item->nama_pemeriksaan,
                    'tanggal_pemeriksaan' => $item->tanggal_pemeriksaan,
                    'jam_pemeriksaan' => $item->jam_pemeriksaan,
                    'status' => $item->status,
                ];
            })->values()->toArray();

            // RADIOLOGI TESTS
            $radiologiTests = DB::table('order_radiologi as orad')
                ->join('order_radiologi_detail as ord', 'orad.id', '=', 'ord.order_radiologi_id')
                ->join('jenis_pemeriksaan_radiologi as jpr', 'jpr.id', '=', 'ord.jenis_pemeriksaan_radiologi_id')
                ->where('orad.kunjungan_id', $kunjungan->id)
                ->select(
                    'ord.id',
                    'jpr.nama_pemeriksaan',
                    'orad.tanggal_pemeriksaan',
                    'orad.jam_pemeriksaan',
                    'ord.status_pemeriksaan as status'
                )
                ->get();

            $responseData['radiologi_tests'] = $radiologiTests->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_pemeriksaan' => $item->nama_pemeriksaan,
                    'tanggal_pemeriksaan' => $item->tanggal_pemeriksaan,
                    'jam_pemeriksaan' => $item->jam_pemeriksaan,
                    'status' => $item->status,
                ];
            })->values()->toArray();

            // PENGKAJIAN PENYAKIT DALAM
            $responseData['pengkajian_penyakit_dalam'] = null;

            if ($pengkajianPenyakitDalam) {
                $responseData['pengkajian_penyakit_dalam'] = [
                    'id' => $pengkajianPenyakitDalam->id,
                    'emr_id' => $pengkajianPenyakitDalam->emr_id,
                    'dokter_id' => $pengkajianPenyakitDalam->dokter_id,
                    'tanggal_pengkajian' => $pengkajianPenyakitDalam->tanggal_pengkajian,
                    'jam_pengkajian' => $pengkajianPenyakitDalam->jam_pengkajian,
                    'alergi' => $pengkajianPenyakitDalam->alergi,
                    'sumber_data' => $pengkajianPenyakitDalam->sumber_data,
                    'sumber_data_lainnya' => $pengkajianPenyakitDalam->sumber_data_lainnya,

                    'nyeri_ada' => $pengkajianPenyakitDalam->nyeri_ada,
                    'skala_nyeri' => $pengkajianPenyakitDalam->skala_nyeri,
                    'karakteristik_nyeri' => $pengkajianPenyakitDalam->karakteristik_nyeri,
                    'lokasi_nyeri' => $pengkajianPenyakitDalam->lokasi_nyeri,
                    'durasi_nyeri' => $pengkajianPenyakitDalam->durasi_nyeri,
                    'frekuensi_nyeri' => $pengkajianPenyakitDalam->frekuensi_nyeri,
                    'tren_nyeri' => $pengkajianPenyakitDalam->tren_nyeri,

                    'keluhan_utama' => $pengkajianPenyakitDalam->keluhan_utama,
                    'riwayat_penyakit_sekarang' => $pengkajianPenyakitDalam->riwayat_penyakit_sekarang,

                    'riwayat_keluarga_hipertensi' => $pengkajianPenyakitDalam->riwayat_keluarga_hipertensi,
                    'riwayat_keluarga_kencing_manis' => $pengkajianPenyakitDalam->riwayat_keluarga_kencing_manis,
                    'riwayat_keluarga_jantung' => $pengkajianPenyakitDalam->riwayat_keluarga_jantung,
                    'riwayat_keluarga_asthma' => $pengkajianPenyakitDalam->riwayat_keluarga_asthma,
                    'riwayat_penyakit_keluarga_lain' => $pengkajianPenyakitDalam->riwayat_penyakit_keluarga_lain,
                    'riwayat_pekerjaan_sosial_ekonomi_psikologi_kebiasaan' => $pengkajianPenyakitDalam->riwayat_pekerjaan_sosial_ekonomi_psikologi_kebiasaan,

                    'keadaan_umum' => $pengkajianPenyakitDalam->keadaan_umum,
                    'status_gizi' => $pengkajianPenyakitDalam->status_gizi,

                    'gcs_e' => $pengkajianPenyakitDalam->gcs_e,
                    'gcs_m' => $pengkajianPenyakitDalam->gcs_m,
                    'gcs_v' => $pengkajianPenyakitDalam->gcs_v,
                    'tindakan_resusitasi' => $pengkajianPenyakitDalam->tindakan_resusitasi,

                    'berat_badan' => $pengkajianPenyakitDalam->berat_badan,
                    'tinggi_badan' => $pengkajianPenyakitDalam->tinggi_badan,
                    'tensi_sistolik' => $pengkajianPenyakitDalam->tensi_sistolik,
                    'tensi_diastolik' => $pengkajianPenyakitDalam->tensi_diastolik,
                    'suhu_axila' => $pengkajianPenyakitDalam->suhu_axila,
                    'suhu_rectal' => $pengkajianPenyakitDalam->suhu_rectal,
                    'nadi' => $pengkajianPenyakitDalam->nadi,
                    'respirasi' => $pengkajianPenyakitDalam->respirasi,
                    'saturasi_o2' => $pengkajianPenyakitDalam->saturasi_o2,
                    'saturasi_o2_dengan' => $pengkajianPenyakitDalam->saturasi_o2_dengan,

                    'pemeriksaan_kulit' => $pengkajianPenyakitDalam->pemeriksaan_kulit,
                    'pemeriksaan_kepala_dan_leher' => $pengkajianPenyakitDalam->pemeriksaan_kepala_dan_leher,
                    'pemeriksaan_telinga_hidung_mulut' => $pengkajianPenyakitDalam->pemeriksaan_telinga_hidung_mulut,
                    'pemeriksaan_leher' => $pengkajianPenyakitDalam->pemeriksaan_leher,

                    'paru_inspeksi' => $pengkajianPenyakitDalam->paru_inspeksi,
                    'paru_palpasi' => $pengkajianPenyakitDalam->paru_palpasi,
                    'paru_perkusi' => $pengkajianPenyakitDalam->paru_perkusi,
                    'paru_auskultasi' => $pengkajianPenyakitDalam->paru_auskultasi,

                    'jantung_inspeksi' => $pengkajianPenyakitDalam->jantung_inspeksi,
                    'jantung_palpasi' => $pengkajianPenyakitDalam->jantung_palpasi,
                    'jantung_perkusi' => $pengkajianPenyakitDalam->jantung_perkusi,
                    'jantung_auskultasi' => $pengkajianPenyakitDalam->jantung_auskultasi,

                    'pemeriksaan_ekstremitas' => $pengkajianPenyakitDalam->pemeriksaan_ekstremitas,
                    'pemeriksaan_alat_kelamin_dan_rektum' => $pengkajianPenyakitDalam->pemeriksaan_alat_kelamin_dan_rektum,
                    'pemeriksaan_neurologis' => $pengkajianPenyakitDalam->pemeriksaan_neurologis,

                    'diagnosa_kerja' => $pengkajianPenyakitDalam->diagnosa_kerja,
                    'diagnosa_diferensial' => $pengkajianPenyakitDalam->diagnosa_diferensial,
                    'terapi_tindakan' => $pengkajianPenyakitDalam->terapi_tindakan,
                    'rencana_kerja' => $pengkajianPenyakitDalam->rencana_kerja,

                    'boleh_pulang' => $pengkajianPenyakitDalam->boleh_pulang,
                    'tanggal_pulang' => $pengkajianPenyakitDalam->tanggal_pulang,
                    'jam_keluar' => $pengkajianPenyakitDalam->jam_keluar,
                    'kontrol_poliklinik' => $pengkajianPenyakitDalam->kontrol_poliklinik,
                    'nama_poli_kontrol' => $pengkajianPenyakitDalam->nama_poli_kontrol,
                    'tanggal_kontrol' => $pengkajianPenyakitDalam->tanggal_kontrol,
                    'dirawat_di_ruang' => $pengkajianPenyakitDalam->dirawat_di_ruang,
                    'kelas_rawat' => $pengkajianPenyakitDalam->kelas_rawat,

                    'tanggal_ttd_dokter' => $pengkajianPenyakitDalam->tanggal_ttd_dokter,
                    'jam_ttd_dokter' => $pengkajianPenyakitDalam->jam_ttd_dokter,
                    'nama_dokter_ttd' => $pengkajianPenyakitDalam->nama_dokter_ttd,
                    'status_form' => $pengkajianPenyakitDalam->status_form,

                    'dokter' => $pengkajianPenyakitDalam->dokter ? [
                        'id' => $pengkajianPenyakitDalam->dokter->id,
                        'nama_dokter' => $pengkajianPenyakitDalam->dokter->nama_dokter,
                    ] : null,

                    'riwayat' => $pengkajianPenyakitDalam->riwayat
                        ? $pengkajianPenyakitDalam->riwayat->values()->toArray()
                        : [],

                    'penunjang' => $pengkajianPenyakitDalam->penunjang
                        ? $pengkajianPenyakitDalam->penunjang->values()->toArray()
                        : [],
                ];
            }

            // ✅ TAMBAHKAN INI - Dental Examination Response
            $responseData['dental_examination'] = null;

            if ($dentalExamination) {
                $responseData['dental_examination'] = [
                    'id' => $dentalExamination->id,
                    'pasien_id' => $dentalExamination->pasien_id,
                    'kunjungan_id' => $dentalExamination->kunjungan_id,
                    'order_layanan_id' => $dentalExamination->order_layanan_id,
                    'tanggal_kunjungan' => $dentalExamination->tanggal_kunjungan,
                    'dpjp_nama' => $dentalExamination->dpjp_nama,
                    'ppjp_nama' => $dentalExamination->ppjp_nama,
                    'status' => $dentalExamination->status,
                    'diperiksa_oleh' => $dentalExamination->diperiksa_oleh,
                    'tanggal_pemeriksaan' => $dentalExamination->tanggal_pemeriksaan,

                    // Odontogram - NORMALIZED
                    'gigi_dewasa_atas' => $this->normalizeOdontogram($dentalExamination->gigi_dewasa_atas),
                    'gigi_dewasa_bawah' => $this->normalizeOdontogram($dentalExamination->gigi_dewasa_bawah),
                    'gigi_anak_atas' => $this->normalizeOdontogram($dentalExamination->gigi_anak_atas),
                    'gigi_anak_bawah' => $this->normalizeOdontogram($dentalExamination->gigi_anak_bawah),

                    // Pemeriksaan Klinis
                    'occlusi' => $dentalExamination->occlusi,
                    'torus_palatinus' => $dentalExamination->torus_palatinus,
                    'torus_mandibularis' => $dentalExamination->torus_mandibularis,
                    'palatum' => $dentalExamination->palatum,
                    'diastema_ada' => $dentalExamination->diastema_ada,
                    'diastema_keterangan' => $dentalExamination->diastema_keterangan,
                    'gigi_anomali_ada' => $dentalExamination->gigi_anomali_ada,
                    'gigi_anomali_keterangan' => $dentalExamination->gigi_anomali_keterangan,
                    'lain_lain' => $dentalExamination->lain_lain,

                    // DMF Index
                    'd_index' => $dentalExamination->d_index,
                    'm_index' => $dentalExamination->m_index,
                    'f_index' => $dentalExamination->f_index,

                    // Dokumentasi
                    'jumlah_foto' => $dentalExamination->jumlah_foto,
                    'jenis_foto' => $dentalExamination->jenis_foto,
                    'jumlah_rontgen' => $dentalExamination->jumlah_rontgen,
                    'jenis_rontgen' => $dentalExamination->jenis_rontgen,

                    'created_at' => $dentalExamination->created_at,
                    'updated_at' => $dentalExamination->updated_at,
                ];
            }

            // DATA KKLP (existing code tetap sama)
            $responseData['kklp'] = null;

            if ($kklp) {
                $responseData['kklp'] = [
                    'id' => $kklp->id,
                    'form' => [
                        'id' => $kklp->id,
                        'diagnosis_holistik' => $kklp->diagnosis_holistik ?? null,
                        'uraian_diagnosis_holistik' => $kklp->uraian_diagnosis_holistik ?? null,
                        'biologis' => $kklp->biologis ?? null,
                        'psikologis' => $kklp->psikologis ?? null,
                        'sosial' => $kklp->sosial ?? null,
                        'fungsional' => $kklp->fungsional ?? null,
                        'derajat_fungsional' => $kklp->derajat_fungsional ?? null,
                        'prognosis' => $kklp->prognosis ?? null,
                        'penatalaksanaan' => $kklp->penatalaksanaan ?? null,
                    ],
                    'orangtua' => $kklp->orangtua ? $kklp->orangtua->values()->toArray() : [],
                    'heteroanamnesis' => $kklp->heteroanamnesis ? $kklp->heteroanamnesis->toArray() : null,
                    'family_apgars' => $kklp->familyApgar ? $kklp->familyApgar->toArray() : null,
                    'family_screems' => $kklp->familyScreem ? $kklp->familyScreem->toArray() : null,
                    'anggota_keluarga' => $kklp->anggotaKeluarga ? $kklp->anggotaKeluarga->values()->toArray() : [],
                    'homevisits' => $kklp->homevisit ? $kklp->homevisit->values()->toArray() : [],
                    'family_plans' => $kklp->familyPlan ? $kklp->familyPlan->values()->toArray() : [],
                    'copc_plans' => $kklp->copcPlan ? $kklp->copcPlan->values()->toArray() : [],
                    'ekstremitas' => $kklp->ekstremitas ? $kklp->ekstremitas->values()->toArray() : [],
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail riwayat pasien berhasil diambil',
                'data' => $responseData,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('ERROR getDetailRiwayatPasien: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail riwayat pasien: '.$e->getMessage(),
            ], 500);
        }
    }

    private function normalizeOdontogram($raw): array
    {
        if (empty($raw)) {
            return [];
        }

        // Kalau masih string JSON, decode dulu
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }

        // Kalau object/collection, ubah ke array
        if ($raw instanceof \Illuminate\Support\Collection) {
            $raw = $raw->toArray();
        }

        if (is_object($raw)) {
            $raw = (array) $raw;
        }

        if (! is_array($raw)) {
            return [];
        }

        $result = [];

        foreach ($raw as $kodeGigi => $item) {
            // Kalau item berupa object
            if (is_object($item)) {
                $item = (array) $item;
            }

            // Kalau item array/detail
            if (is_array($item)) {
                $status = $item['status']
                    ?? $item['label']
                    ?? $item['kondisi']
                    ?? $item['value']
                    ?? $item['kode']
                    ?? '-';

                $keterangan = $item['keterangan']
                    ?? $item['catatan']
                    ?? $item['note']
                    ?? $item['deskripsi']
                    ?? '-';

                $result[$kodeGigi] = [
                    'status' => is_scalar($status) ? (string) $status : '-',
                    'keterangan' => is_scalar($keterangan) ? (string) $keterangan : '-',
                ];
            } else {
                // Kalau item cuma string / angka
                $result[$kodeGigi] = [
                    'status' => is_scalar($item) ? (string) $item : '-',
                    'keterangan' => '-',
                ];
            }
        }

        return $result;
    }

    public function getMasterLabDokter(Request $request)
    {
        $items = DB::table('jenis_pemeriksaan_lab as j')
            ->leftJoin('satuan_lab as s', 's.id', '=', 'j.satuan_lab_id')->select([
                'j.id',
                'j.kode_pemeriksaan',
                'j.nama_pemeriksaan',
                'j.nilai_normal',
                'j.harga_pemeriksaan_lab',
                'j.status',
                'j.satuan_lab_id',
                DB::raw('COALESCE(s.nama_satuan, "") as nama_satuan'),
            ])
            ->orderBy('j.nama_pemeriksaan')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function dokterMasterLab()
    {
        $rows = DB::table('jenis_pemeriksaan_lab as j')
            ->leftJoin('satuan_lab as s', 's.id', '=', 'j.satuan_lab_id')
            ->where('j.status', 'Active') // ⬅️ penting
            ->select(
                'j.id',
                'j.nama_pemeriksaan',
                'j.nilai_normal',
                'j.harga_pemeriksaan_lab',
                DB::raw('COALESCE(s.nama_satuan, "") as satuan')
            )
            ->orderBy('j.nama_pemeriksaan')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'nama' => $r->nama_pemeriksaan, // ⬅️ WAJIB "nama"
                    'nilai_normal' => $r->nilai_normal,
                    'satuan' => $r->satuan,
                    'harga' => $r->harga_pemeriksaan_lab,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    public function dokterCreateOrderLab(Request $request)
    {
        $data = $request->all();

        $user = $request->user();
        $dokter = \App\Models\Dokter::where('user_id', $user->id)->first();
        if (! $dokter) {
            return response()->json([
                'success' => false,
                'message' => 'Dokter tidak ditemukan untuk user ini.',
            ], 404);
        }

        $data['dokter_id'] = $dokter->id;

        if (! empty($data['kunjungan_id'])) {
            $k = DB::table('kunjungan')->where('id', $data['kunjungan_id'])->first();
            if ($k && empty($data['pasien_id'])) {
                $data['pasien_id'] = (int) $k->pasien_id;
            }
        }

        $jenisIds = [];

        if (isset($data['jenis_pemeriksaan_lab_ids']) && is_array($data['jenis_pemeriksaan_lab_ids'])) {
            foreach ($data['jenis_pemeriksaan_lab_ids'] as $v) {
                if (is_numeric($v)) {
                    $jenisIds[] = (int) $v;
                } elseif (is_string($v) && preg_match('/(\d+)/', $v, $m)) {
                    $jenisIds[] = (int) $m[1];
                }
            }
        }

        if (empty($jenisIds) && isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $it) {
                if (is_numeric($it)) {
                    $jenisIds[] = (int) $it;

                    continue;
                }

                if (is_array($it)) {
                    $raw = $it['id']
                        ?? $it['lab_test_id']
                        ?? $it['jenis_pemeriksaan_lab_id']
                        ?? $it['value']
                        ?? null;

                    if ($raw === null) {
                        continue;
                    }

                    if (is_numeric($raw)) {
                        $jenisIds[] = (int) $raw;
                    } elseif (is_string($raw) && preg_match('/(\d+)/', $raw, $m)) {
                        $jenisIds[] = (int) $m[1];
                    }
                }
            }
        }

        $data['jenis_pemeriksaan_lab_ids'] = array_values(array_unique(array_filter($jenisIds, fn ($x) => (int) $x > 0)));

        // ❌ HAPUS AUTO-FILL tanggal & jam

        $v = Validator::make($data, [
            'dokter_id' => 'required|exists:dokter,id',
            'pasien_id' => 'required|exists:pasien,id',
            'jenis_pemeriksaan_lab_ids' => 'required|array|min:1',
            'jenis_pemeriksaan_lab_ids.*' => 'required|exists:jenis_pemeriksaan_lab,id',
            // ✅ TIDAK ADA validasi tanggal_pemeriksaan & jam_pemeriksaan
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $v->errors(),
            ], 422);
        }

        return DB::transaction(function () use ($data) {

            $no = 'LAB-'.date('Ymd').'-'.strtoupper(Str::random(6));

            // ✅ FIX: tanggal_pemeriksaan & jam_pemeriksaan = NULL
            $orderId = DB::table('order_lab')->insertGetId([
                'no_order_lab' => $no,
                'dokter_id' => (int) $data['dokter_id'],
                'pasien_id' => (int) $data['pasien_id'],
                'tanggal_order' => now()->toDateString(),
                'tanggal_pemeriksaan' => null, // ✅ NULL - ISI NANTI VIA FLUTTER
                'jam_pemeriksaan' => null,     // ✅ NULL - ISI NANTI VIA FLUTTER
                'status' => 'Pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $details = [];
            foreach ($data['jenis_pemeriksaan_lab_ids'] as $jpId) {
                $details[] = [
                    'order_lab_id' => $orderId,
                    'jenis_pemeriksaan_lab_id' => (int) $jpId,
                    'status_pemeriksaan' => 'Pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('order_lab_detail')->insert($details);

            $order = DB::table('order_lab')->where('id', $orderId)->first();

            $detailRows = DB::table('order_lab_detail as d')
                ->join('jenis_pemeriksaan_lab as j', 'j.id', '=', 'd.jenis_pemeriksaan_lab_id')
                ->leftJoin('satuan_lab as s', 's.id', '=', 'j.satuan_lab_id')
                ->where('d.order_lab_id', $orderId)
                ->select(
                    'd.id as order_lab_detail_id',
                    'd.status_pemeriksaan',
                    'j.id as jenis_id',
                    'j.kode_pemeriksaan',
                    'j.nama_pemeriksaan',
                    'j.nilai_normal',
                    'j.harga_pemeriksaan_lab',
                    's.nama_satuan'
                )
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Order lab berhasil dibuat. Jadwal pemeriksaan akan ditentukan nanti.',
                'data' => [
                    'order' => $order,
                    'details' => $detailRows,
                ],
            ]);
        });
    }
    // Di section: punya dokter
    // Setelah method dokterCreateOrderLab()
    // ============================================================
    // DENTAL EXAMINATION SECTION
    // ============================================================

    /**
     * Cek apakah layanan adalah "Gigi dan Mulut Dewasa"
     * Trigger untuk menampilkan form dental
     */
    public function checkDentalService($orderLayananId)
    {
        try {
            $layanan = DB::table('order_layanan')
                ->join('order_layanan_detail', 'order_layanan.id', '=', 'order_layanan_detail.order_layanan_id')
                ->join('layanan', 'order_layanan_detail.layanan_id', '=', 'layanan.id')
                ->where('order_layanan.id', $orderLayananId)
                ->select('layanan.nama_layanan')
                ->first();

            if (! $layanan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Layanan tidak ditemukan',
                ], 404);
            }

            // TRIGGER: Cek apakah layanan dental
            $isDental = stripos($layanan->nama_layanan, 'Gigi dan Mulut Dewasa') !== false;

            return response()->json([
                'success' => true,
                'is_dental' => $isDental,
                'layanan_nama' => $layanan->nama_layanan,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tampilkan form dental examination
     */
    public function dentalCreate($pasienId, $orderLayananId)
    {
        try {
            // Cek apakah sudah ada draft
            $existing = DB::table('dental_examinations')
                ->where('pasien_id', $pasienId)
                ->where('order_layanan_id', $orderLayananId)
                ->where('status', 'draft')
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'Draft ditemukan',
                    'data' => $existing,
                    'mode' => 'edit',
                ]);
            }

            // Data pasien
            $pasien = DB::table('pasien')->where('id', $pasienId)->first();

            return response()->json([
                'success' => true,
                'message' => 'Form baru',
                'data' => [
                    'pasien' => $pasien,
                    'tanggal_kunjungan' => date('Y-m-d'),
                ],
                'mode' => 'create',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Simpan SECTION 1 & 2 (Informasi Dasar + Odontogram)
     */
    public function dentalSaveBasicInfo(Request $request)
    {
        try {
            $validated = $request->validate([
                'pasien_id' => 'required|exists:pasien,id',
                'order_layanan_id' => 'nullable|exists:order_layanan,id',
                'tanggal_kunjungan' => 'required|date',
                'dpjp_nama' => 'nullable|string',
                'ppjp_nama' => 'nullable|string',
                'gigi_dewasa_atas' => 'nullable|json',
                'gigi_dewasa_bawah' => 'nullable|json',
                'gigi_anak_atas' => 'nullable|json',
                'gigi_anak_bawah' => 'nullable|json',
            ]);

            // Cek apakah sudah ada draft
            $existing = DB::table('dental_examinations')
                ->where('pasien_id', $request->pasien_id)
                ->where('order_layanan_id', $request->order_layanan_id)
                ->where('status', 'draft')
                ->first();

            if ($existing) {
                // Update existing draft
                DB::table('dental_examinations')
                    ->where('id', $existing->id)
                    ->update([
                        'tanggal_kunjungan' => $request->tanggal_kunjungan,
                        'dpjp_nama' => $request->dpjp_nama,
                        'ppjp_nama' => $request->ppjp_nama,
                        'gigi_dewasa_atas' => $request->gigi_dewasa_atas,
                        'gigi_dewasa_bawah' => $request->gigi_dewasa_bawah,
                        'gigi_anak_atas' => $request->gigi_anak_atas,
                        'gigi_anak_bawah' => $request->gigi_anak_bawah,
                        'updated_by' => Auth::id(),
                        'updated_at' => now(),
                    ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil diupdate',
                    'dental_examination_id' => $existing->id,
                ]);
            } else {
                // Insert new
                $id = DB::table('dental_examinations')->insertGetId([
                    'pasien_id' => $request->pasien_id,
                    'order_layanan_id' => $request->order_layanan_id,
                    'tanggal_kunjungan' => $request->tanggal_kunjungan,
                    'dpjp_nama' => $request->dpjp_nama,
                    'ppjp_nama' => $request->ppjp_nama,
                    'gigi_dewasa_atas' => $request->gigi_dewasa_atas,
                    'gigi_dewasa_bawah' => $request->gigi_dewasa_bawah,
                    'gigi_anak_atas' => $request->gigi_anak_atas,
                    'gigi_anak_bawah' => $request->gigi_anak_bawah,
                    'status' => 'draft',
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil disimpan',
                    'dental_examination_id' => $id,
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Simpan SECTION 3 (Pemeriksaan Klinis)
     */
    public function dentalSaveClinicalExam(Request $request)
    {
        try {
            $validated = $request->validate([
                'dental_examination_id' => 'required|exists:dental_examinations,id',
                'occlusi' => 'required|in:normal_bite,cross_bite,steep_bite',
                'torus_palatinus' => 'required|in:tidak_ada,kecil,sedang,besar,multiple',
                'torus_mandibularis' => 'required|in:tidak_ada,sisi_kiri,sisi_kanan,kedua_sisi',
                'palatum' => 'required|in:dalam,sedang,rendah',
                'diastema_ada' => 'required|boolean',
                'diastema_keterangan' => 'nullable|string',
                'gigi_anomali_ada' => 'required|boolean',
                'gigi_anomali_keterangan' => 'nullable|string',
                'lain_lain' => 'nullable|string',
                'd_index' => 'required|integer|min:0',
                'm_index' => 'required|integer|min:0',
                'f_index' => 'required|integer|min:0',
                'jumlah_foto' => 'nullable|integer|min:0',
                'jenis_foto' => 'nullable|string',
                'jumlah_rontgen' => 'nullable|integer|min:0',
                'jenis_rontgen' => 'nullable|string',
            ]);

            DB::table('dental_examinations')
                ->where('id', $request->dental_examination_id)
                ->update([
                    'occlusi' => $request->occlusi,
                    'torus_palatinus' => $request->torus_palatinus,
                    'torus_mandibularis' => $request->torus_mandibularis,
                    'palatum' => $request->palatum,
                    'diastema_ada' => $request->diastema_ada,
                    'diastema_keterangan' => $request->diastema_keterangan,
                    'gigi_anomali_ada' => $request->gigi_anomali_ada,
                    'gigi_anomali_keterangan' => $request->gigi_anomali_keterangan,
                    'lain_lain' => $request->lain_lain,
                    'd_index' => $request->d_index,
                    'm_index' => $request->m_index,
                    'f_index' => $request->f_index,
                    'jumlah_foto' => $request->jumlah_foto ?? 0,
                    'jenis_foto' => $request->jenis_foto,
                    'jumlah_rontgen' => $request->jumlah_rontgen ?? 0,
                    'jenis_rontgen' => $request->jenis_rontgen,
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Pemeriksaan klinis berhasil disimpan',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Simpan SECTION 4 (Verifikasi) & Complete
     */
    public function dentalComplete(Request $request)
    {
        try {
            $validated = $request->validate([
                'dental_examination_id' => 'required|exists:dental_examinations,id',
                'diperiksa_oleh' => 'required|string',
                'tanggal_pemeriksaan' => 'required|date',
            ]);

            DB::table('dental_examinations')
                ->where('id', $request->dental_examination_id)
                ->update([
                    'diperiksa_oleh' => $request->diperiksa_oleh,
                    'tanggal_pemeriksaan' => $request->tanggal_pemeriksaan,
                    'status' => 'completed',
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Pemeriksaan gigi selesai dan disimpan',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get detail pemeriksaan gigi
     */
    public function dentalShow($id)
    {
        try {
            $examination = DB::table('dental_examinations as de')
                ->leftJoin('pasien as p', 'de.pasien_id', '=', 'p.id')
                ->leftJoin('order_layanan as ol', 'de.order_layanan_id', '=', 'ol.id')
                ->where('de.id', $id)
                ->select(
                    'de.*',
                    'p.nama_pasien',
                    'p.no_emr',
                    'p.tanggal_lahir'
                )
                ->first();

            if (! $examination) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                ], 404);
            }

            // Get treatments
            $treatments = DB::table('dental_treatments')
                ->where('dental_examination_id', $id)
                ->whereNull('deleted_at')
                ->orderBy('tanggal', 'desc')
                ->get();

            // Get photos
            $photos = DB::table('dental_photos')
                ->where('dental_examination_id', $id)
                ->whereNull('deleted_at')
                ->get();

            // Add full URL for photos
            foreach ($photos as $photo) {
                $photo->url = Storage::url($photo->file_path);
            }

            // Get xrays
            $xrays = DB::table('dental_xrays')
                ->where('dental_examination_id', $id)
                ->whereNull('deleted_at')
                ->get();

            // Add full URL for xrays
            foreach ($xrays as $xray) {
                $xray->url = Storage::url($xray->file_path);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'examination' => $examination,
                    'treatments' => $treatments,
                    'photos' => $photos,
                    'xrays' => $xrays,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * List semua pemeriksaan gigi
     */
    public function dentalIndex(Request $request)
    {
        try {
            $query = DB::table('dental_examinations as de')
                ->leftJoin('pasien as p', 'de.pasien_id', '=', 'p.id')
                ->select(
                    'de.id',
                    'de.tanggal_kunjungan',
                    'de.dpjp_nama',
                    'de.status',
                    'de.d_index',
                    'de.m_index',
                    'de.f_index',
                    'p.nama_pasien',
                    'p.no_emr'
                )
                ->whereNull('de.deleted_at');

            // Filter by status
            if ($request->has('status')) {
                $query->where('de.status', $request->status);
            }

            // Filter by date range
            if ($request->has('tanggal_dari')) {
                $query->where('de.tanggal_kunjungan', '>=', $request->tanggal_dari);
            }
            if ($request->has('tanggal_sampai')) {
                $query->where('de.tanggal_kunjungan', '<=', $request->tanggal_sampai);
            }

            // Search by patient name or no_emr
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('p.nama_pasien', 'like', "%{$search}%")
                        ->orWhere('p.no_emr', 'like', "%{$search}%");
                });
            }

            $data = $query->orderBy('de.tanggal_kunjungan', 'desc')
                ->paginate($request->per_page ?? 15);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete pemeriksaan gigi (soft delete)
     */
    public function dentalDestroy($id)
    {
        try {
            $examination = DB::table('dental_examinations')->where('id', $id)->first();

            if (! $examination) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                ], 404);
            }

            // Hanya bisa hapus kalau masih draft
            if ($examination->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya data draft yang bisa dihapus',
                ], 400);
            }

            DB::table('dental_examinations')
                ->where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'updated_by' => Auth::id(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // DENTAL TREATMENTS
    // ============================================================

    /**
     * Get all treatments untuk dental examination tertentu
     */
    public function dentalTreatmentIndex($dentalExaminationId)
    {
        try {
            $treatments = DB::table('dental_treatments')
                ->where('dental_examination_id', $dentalExaminationId)
                ->whereNull('deleted_at')
                ->orderBy('tanggal', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $treatments,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tambah treatment baru
     */
    public function dentalTreatmentStore(Request $request)
    {
        try {
            $validated = $request->validate([
                'dental_examination_id' => 'required|exists:dental_examinations,id',
                'pasien_id' => 'required|exists:pasien,id',
                'tanggal' => 'required|date',
                'gigi' => 'required|string',
                'keluhan' => 'nullable|string',
                'kode_icd10' => 'nullable|string|max:20',
                'nama_diagnosa' => 'nullable|string',
                'perawatan' => 'required|string',
                'keterangan' => 'nullable|string',
                'biaya' => 'nullable|numeric|min:0',
                'sudah_dibayar' => 'nullable|boolean',
            ]);

            $id = DB::table('dental_treatments')->insertGetId([
                'dental_examination_id' => $request->dental_examination_id,
                'pasien_id' => $request->pasien_id,
                'tanggal' => $request->tanggal,
                'gigi' => $request->gigi,
                'keluhan' => $request->keluhan,
                'kode_icd10' => $request->kode_icd10,
                'nama_diagnosa' => $request->nama_diagnosa,
                'perawatan' => $request->perawatan,
                'keterangan' => $request->keterangan,
                'status' => 'planned',
                'biaya' => $request->biaya ?? 0,
                'sudah_dibayar' => $request->sudah_dibayar ?? false,
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Treatment berhasil ditambahkan',
                'treatment_id' => $id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update treatment
     */
    public function dentalTreatmentUpdate(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'tanggal' => 'required|date',
                'gigi' => 'required|string',
                'keluhan' => 'nullable|string',
                'kode_icd10' => 'nullable|string|max:20',
                'nama_diagnosa' => 'nullable|string',
                'perawatan' => 'required|string',
                'keterangan' => 'nullable|string',
                'status' => 'nullable|in:planned,in_progress,completed,cancelled',
                'biaya' => 'nullable|numeric|min:0',
                'sudah_dibayar' => 'nullable|boolean',
            ]);

            $treatment = DB::table('dental_treatments')->where('id', $id)->first();

            if (! $treatment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Treatment tidak ditemukan',
                ], 404);
            }

            DB::table('dental_treatments')
                ->where('id', $id)
                ->update([
                    'tanggal' => $request->tanggal,
                    'gigi' => $request->gigi,
                    'keluhan' => $request->keluhan,
                    'kode_icd10' => $request->kode_icd10,
                    'nama_diagnosa' => $request->nama_diagnosa,
                    'perawatan' => $request->perawatan,
                    'keterangan' => $request->keterangan,
                    'status' => $request->status ?? $treatment->status,
                    'biaya' => $request->biaya ?? $treatment->biaya,
                    'sudah_dibayar' => $request->sudah_dibayar ?? $treatment->sudah_dibayar,
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Treatment berhasil diupdate',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update status treatment
     */
    public function dentalTreatmentUpdateStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:planned,in_progress,completed,cancelled',
            ]);

            DB::table('dental_treatments')
                ->where('id', $id)
                ->update([
                    'status' => $request->status,
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Status treatment berhasil diupdate',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete treatment (soft delete)
     */
    public function dentalTreatmentDestroy($id)
    {
        try {
            DB::table('dental_treatments')
                ->where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'updated_by' => Auth::id(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Treatment berhasil dihapus',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // DENTAL PHOTOS
    // ============================================================

    /**
     * Get all photos untuk dental examination
     */
    public function dentalPhotoIndex($dentalExaminationId)
    {
        try {
            $photos = DB::table('dental_photos')
                ->where('dental_examination_id', $dentalExaminationId)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->get();

            // Add full URL for each photo
            foreach ($photos as $photo) {
                $photo->url = Storage::url($photo->file_path);
            }

            return response()->json([
                'success' => true,
                'data' => $photos,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload foto
     */
    public function dentalPhotoUpload(Request $request)
    {
        try {
            $validated = $request->validate([
                'dental_examination_id' => 'required|exists:dental_examinations,id',
                'pasien_id' => 'required|exists:pasien,id',
                'foto' => 'required|image|max:10240', // max 10MB
                'tipe_foto' => 'required|in:digital,intraoral,extraoral,oklusal,lateral',
                'keterangan' => 'nullable|string',
                'tanggal_foto' => 'nullable|date',
            ]);

            // Upload file
            $file = $request->file('foto');
            $fileName = time().'_'.$file->getClientOriginalName();
            $filePath = $file->storeAs('dental_photos', $fileName, 'public');

            // Save to database
            $id = DB::table('dental_photos')->insertGetId([
                'dental_examination_id' => $request->dental_examination_id,
                'pasien_id' => $request->pasien_id,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'tipe_foto' => $request->tipe_foto,
                'keterangan' => $request->keterangan,
                'tanggal_foto' => $request->tanggal_foto ?? date('Y-m-d'),
                'uploaded_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update jumlah foto di dental_examinations
            $currentCount = DB::table('dental_photos')
                ->where('dental_examination_id', $request->dental_examination_id)
                ->whereNull('deleted_at')
                ->count();

            DB::table('dental_examinations')
                ->where('id', $request->dental_examination_id)
                ->update(['jumlah_foto' => $currentCount]);

            return response()->json([
                'success' => true,
                'message' => 'Foto berhasil diupload',
                'photo_id' => $id,
                'url' => Storage::url($filePath),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete foto
     */
    public function dentalPhotoDestroy($id)
    {
        try {
            $photo = DB::table('dental_photos')->where('id', $id)->first();

            if (! $photo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Foto tidak ditemukan',
                ], 404);
            }

            // Delete file from storage
            if (Storage::disk('public')->exists($photo->file_path)) {
                Storage::disk('public')->delete($photo->file_path);
            }

            // Soft delete from database
            DB::table('dental_photos')
                ->where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);

            // Update jumlah foto
            $currentCount = DB::table('dental_photos')
                ->where('dental_examination_id', $photo->dental_examination_id)
                ->whereNull('deleted_at')
                ->count();

            DB::table('dental_examinations')
                ->where('id', $photo->dental_examination_id)
                ->update(['jumlah_foto' => $currentCount]);

            return response()->json([
                'success' => true,
                'message' => 'Foto berhasil dihapus',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // DENTAL X-RAYS
    // ============================================================

    /**
     * Get all rontgen untuk dental examination
     */
    public function dentalXrayIndex($dentalExaminationId)
    {
        try {
            $xrays = DB::table('dental_xrays')
                ->where('dental_examination_id', $dentalExaminationId)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->get();

            // Add full URL for each xray
            foreach ($xrays as $xray) {
                $xray->url = Storage::url($xray->file_path);
            }

            return response()->json([
                'success' => true,
                'data' => $xrays,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload rontgen
     */
    public function dentalXrayUpload(Request $request)
    {
        try {
            $validated = $request->validate([
                'dental_examination_id' => 'required|exists:dental_examinations,id',
                'pasien_id' => 'required|exists:pasien,id',
                'rontgen' => 'required|image|max:20480', // max 20MB
                'jenis_rontgen' => 'required|in:dental,pa,opg,ceph,bitewing,occlusal',
                'lokasi_gigi' => 'nullable|string',
                'keterangan' => 'nullable|string',
                'tanggal_rontgen' => 'nullable|date',
            ]);

            // Upload file
            $file = $request->file('rontgen');
            $fileName = time().'_'.$file->getClientOriginalName();
            $filePath = $file->storeAs('dental_xrays', $fileName, 'public');

            // Save to database
            $id = DB::table('dental_xrays')->insertGetId([
                'dental_examination_id' => $request->dental_examination_id,
                'pasien_id' => $request->pasien_id,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'jenis_rontgen' => $request->jenis_rontgen,
                'lokasi_gigi' => $request->lokasi_gigi,
                'keterangan' => $request->keterangan,
                'tanggal_rontgen' => $request->tanggal_rontgen ?? date('Y-m-d'),
                'uploaded_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update jumlah rontgen di dental_examinations
            $currentCount = DB::table('dental_xrays')
                ->where('dental_examination_id', $request->dental_examination_id)
                ->whereNull('deleted_at')
                ->count();

            DB::table('dental_examinations')
                ->where('id', $request->dental_examination_id)
                ->update(['jumlah_rontgen' => $currentCount]);

            return response()->json([
                'success' => true,
                'message' => 'Rontgen berhasil diupload',
                'xray_id' => $id,
                'url' => Storage::url($filePath),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete rontgen
     */
    public function dentalXrayDestroy($id)
    {
        try {
            $xray = DB::table('dental_xrays')->where('id', $id)->first();

            if (! $xray) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rontgen tidak ditemukan',
                ], 404);
            }

            // Delete file from storage
            if (Storage::disk('public')->exists($xray->file_path)) {
                Storage::disk('public')->delete($xray->file_path);
            }

            // Soft delete from database
            DB::table('dental_xrays')
                ->where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);

            // Update jumlah rontgen
            $currentCount = DB::table('dental_xrays')
                ->where('dental_examination_id', $xray->dental_examination_id)
                ->whereNull('deleted_at')
                ->count();

            DB::table('dental_examinations')
                ->where('id', $xray->dental_examination_id)
                ->update(['jumlah_rontgen' => $currentCount]);

            return response()->json([
                'success' => true,
                'message' => 'Rontgen berhasil dihapus',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * ✅ RIWAYAT LAB - List order lab yang sudah selesai untuk pasien tertentu
     * GET /api/dokter/riwayat-lab/pasien/{pasien_id}
     */
    public function dokterRiwayatLabPasien(Request $request, $pasienId)
    {
        $user = $request->user();
        $dokter = \App\Models\Dokter::where('user_id', $user->id)->first();

        if (! $dokter) {
            return response()->json([
                'success' => false,
                'message' => 'Dokter tidak ditemukan untuk user ini.',
            ], 404);
        }

        // Ambil data pasien
        $pasien = \App\Models\Pasien::find($pasienId);
        if (! $pasien) {
            return response()->json([
                'success' => false,
                'message' => 'Pasien tidak ditemukan.',
            ], 404);
        }

        // Ambil order lab yang SELESAI untuk pasien ini
        $riwayat = \App\Models\OrderLab::with([
            'dokter:id,nama_dokter',
            'orderLabDetail.jenisPemeriksaanLab:id,kode_pemeriksaan,nama_pemeriksaan',
        ])
            ->where('pasien_id', $pasienId)
            ->where('status', 'Selesai')
            ->orderByDesc('tanggal_pemeriksaan')
            ->get()
            ->map(function ($order) {
                $jumlahPemeriksaan = $order->orderLabDetail->count();
                $previewPemeriksaan = $order->orderLabDetail->first()?->jenisPemeriksaanLab->nama_pemeriksaan ?? '-';

                return [
                    'id' => $order->id,
                    'no_order_lab' => $order->no_order_lab,
                    'tanggal_pemeriksaan' => $order->tanggal_pemeriksaan,
                    'status' => $order->status,
                    'dokter' => $order->dokter ? [
                        'id' => $order->dokter->id,
                        'nama_dokter' => $order->dokter->nama_dokter,
                    ] : null,
                    'jumlah_pemeriksaan' => $jumlahPemeriksaan,
                    'preview_pemeriksaan' => $previewPemeriksaan,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'pasien' => [
                    'id' => $pasien->id,
                    'nama_pasien' => $pasien->nama_pasien,
                    'no_emr' => $pasien->no_emr,
                ],
                'riwayat' => $riwayat,
            ],
        ]);
    }

    public function dokterDetailRiwayatLab(Request $request, $orderLabId)
    {
        try {
            $user = $request->user();
            $dokter = \App\Models\Dokter::where('user_id', $user->id)->first();

            if (! $dokter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokter tidak ditemukan untuk user ini.',
                ], 404);
            }

            // ✅ LOAD SEMUA RELASI YANG DIPERLUKAN
            $order = \App\Models\OrderLab::with([
                // Data Pasien lengkap
                'pasien:id,nama_pasien,no_emr,nik,no_bpjs,jenis_kelamin,tanggal_lahir,alamat,no_hp_pasien,golongan_darah,pekerjaan',

                // Data Dokter yang order
                'dokter:id,nama_dokter,no_hp,foto_dokter',
                'dokter.jenisSpesialis:id,nama_spesialis',
                'dokter.poli:id,nama_poli',

                // Detail pemeriksaan lab
                'orderLabDetail.jenisPemeriksaanLab:id,kode_pemeriksaan,nama_pemeriksaan,nilai_normal,satuan_lab_id,harga_pemeriksaan_lab',
                'orderLabDetail.jenisPemeriksaanLab.satuanLab:id,nama_satuan',

                // Hasil lab + perawat yang input
                'orderLabDetail.hasilLab',
                'orderLabDetail.hasilLab.perawat:id,nama_perawat,no_hp_perawat,foto_perawat',
            ])
                ->where('id', $orderLabId)
                ->where('status', 'Selesai')
                ->first();

            if (! $order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order lab tidak ditemukan atau belum selesai.',
                ], 404);
            }

            // ✅ FORMAT RESPONSE LENGKAP
            $response = [
                'success' => true,
                'data' => [
                    // INFO ORDER LAB
                    'order_info' => [
                        'id' => $order->id,
                        'no_order_lab' => $order->no_order_lab,
                        'tanggal_order' => $order->tanggal_order,
                        'tanggal_pemeriksaan' => $order->tanggal_pemeriksaan,
                        'jam_pemeriksaan' => $order->jam_pemeriksaan,
                        'status' => $order->status,
                    ],

                    // DATA PASIEN LENGKAP
                    'pasien' => $order->pasien ? [
                        'id' => $order->pasien->id,
                        'nama_pasien' => $order->pasien->nama_pasien,
                        'no_emr' => $order->pasien->no_emr,
                        'nik' => $order->pasien->nik,
                        'no_bpjs' => $order->pasien->no_bpjs,
                        'jenis_kelamin' => $order->pasien->jenis_kelamin,
                        'tanggal_lahir' => $order->pasien->tanggal_lahir,
                        'umur' => $order->pasien->tanggal_lahir
    ? \Carbon\Carbon::parse((string) $order->pasien->tanggal_lahir)->age.' tahun'
    : null,

                        'alamat' => $order->pasien->alamat,
                        'no_hp' => $order->pasien->no_hp_pasien,
                        'golongan_darah' => $order->pasien->golongan_darah,
                        'pekerjaan' => $order->pasien->pekerjaan,
                    ] : null,

                    // DATA DOKTER YANG ORDER
                    'dokter_pemeriksa' => $order->dokter ? [
                        'id' => $order->dokter->id,
                        'nama_dokter' => $order->dokter->nama_dokter,
                        'no_hp' => $order->dokter->no_hp,
                        'foto_dokter' => $order->dokter->foto_dokter
                            ? url('storage/'.$order->dokter->foto_dokter)
                            : null,
                        'spesialis' => $order->dokter->jenisSpesialis?->nama_spesialis,
                        'poli' => $order->dokter->poli instanceof \Illuminate\Support\Collection
    ? ($order->dokter->poli->first()?->nama_poli)
    : ($order->dokter->poli?->nama_poli),

                    ] : null,

                    // DETAIL PEMERIKSAAN LAB
                    'pemeriksaan' => $order->orderLabDetail->map(function ($detail) {
                        $jp = $detail->jenisPemeriksaanLab;
                        $hasil = $detail->hasilLab;

                        return [
                            'detail_id' => $detail->id,
                            'status_pemeriksaan' => $detail->status_pemeriksaan,

                            // Info pemeriksaan
                            'pemeriksaan' => $jp ? [
                                'id' => $jp->id,
                                'kode_pemeriksaan' => $jp->kode_pemeriksaan,
                                'nama_pemeriksaan' => $jp->nama_pemeriksaan,
                                'nilai_normal' => $jp->nilai_normal,
                                'satuan' => $jp->satuanLab?->nama_satuan,
                                'harga' => $jp->harga_pemeriksaan_lab,
                            ] : null,

                            // Hasil lab + perawat
                            'hasil' => $hasil ? [
                                'id' => $hasil->id,
                                'nilai_hasil' => $hasil->nilai_hasil,
                                'nilai_rujukan' => $hasil->nilai_rujukan,
                                'keterangan' => $hasil->keterangan,
                                'tanggal_pemeriksaan' => $hasil->tanggal_pemeriksaan,
                                'jam_pemeriksaan' => $hasil->jam_pemeriksaan,

                                // ✅ PERAWAT YANG INPUT HASIL
                                'perawat_pemeriksa' => $hasil->perawat ? [
                                    'id' => $hasil->perawat->id,
                                    'nama_perawat' => $hasil->perawat->nama_perawat,
                                    'no_hp' => $hasil->perawat->no_hp_perawat,
                                    'foto_perawat' => $hasil->perawat->foto_perawat
                                        ? url('storage/'.$hasil->perawat->foto_perawat)
                                        : null,
                                ] : null,
                            ] : null,
                        ];
                    }),

                    // ✅ RINGKASAN
                    'ringkasan' => [
                        'total_pemeriksaan' => $order->orderLabDetail->count(),
                        'pemeriksaan_selesai' => $order->orderLabDetail->where('status_pemeriksaan', 'Selesai')->count(),
                        'pemeriksaan_pending' => $order->orderLabDetail->where('status_pemeriksaan', 'Pending')->count(),
                    ],
                ],
            ];

            return response()->json($response);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ERROR dokterDetailRiwayatLab: '.$e->getMessage(), [
                'order_lab_id' => $orderLabId ?? null,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail order lab',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Dokter submit / create hasil lab (upsert) berdasarkan order_lab_detail
     * POST /api/dokter/hasil-lab
     */
    public function dokterCreateHasilLab(Request $request)
    {
        $user = $request->user();
        $dokterId = $user->dokter->id ?? null;

        $data = $request->validate([
            'order_lab_detail_id' => 'required|exists:order_lab_detail,id',
            'nilai_hasil' => 'required|string',
            'nilai_rujukan' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'tanggal_pemeriksaan' => 'required|date',
            'jam_pemeriksaan' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // ✅ UPSERT hasil lab
            $hasil = HasilLab::updateOrCreate(
                ['order_lab_detail_id' => $data['order_lab_detail_id']],
                [
                    'dokter_id' => $dokterId,
                    'perawat_id' => null,
                    'nilai_hasil' => $data['nilai_hasil'],
                    'nilai_rujukan' => $data['nilai_rujukan'],
                    'catatan' => $data['keterangan'],
                    'tanggal_pemeriksaan' => $data['tanggal_pemeriksaan'],
                    'jam_pemeriksaan' => $data['jam_pemeriksaan'],
                ]
            );

            // ✅ Update status detail jadi "Selesai"
            DB::table('order_lab_detail')
                ->where('id', $data['order_lab_detail_id'])
                ->update([
                    'status_pemeriksaan' => 'Selesai',
                    'updated_at' => now(),
                ]);

            // ✅ Cek apakah semua detail sudah selesai
            $detail = DB::table('order_lab_detail')
                ->where('id', $data['order_lab_detail_id'])
                ->first();

            $orderLabId = $detail->order_lab_id;

            $allCompleted = DB::table('order_lab_detail')
                ->where('order_lab_id', $orderLabId)
                ->where('status_pemeriksaan', '!=', 'Selesai')
                ->doesntExist();

            // ✅ Jika semua selesai, update order lab status + KIRIM NOTIFIKASI
            if ($allCompleted) {
                DB::table('order_lab')
                    ->where('id', $orderLabId)
                    ->update([
                        'status' => 'Selesai',
                        'updated_at' => now(),
                    ]);

                // ✅✅✅ KIRIM NOTIFIKASI KE PASIEN ✅✅✅
                $orderLab = \App\Models\OrderLab::find($orderLabId);
                if ($orderLab) {
                    NotificationHelper::kirimNotifikasiHasilLab($orderLab, $hasil);

                    Log::info('🔔 Notifikasi hasil lab triggered', [
                        'order_lab_id' => $orderLabId,
                        'hasil_lab_id' => $hasil->id,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hasil lab berhasil disimpan'.($allCompleted ? ' dan notifikasi terkirim ke pasien' : ''),
                'data' => $hasil,
                'notification_sent' => $allCompleted,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error dokterCreateHasilLab: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan hasil lab',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateHasilLab(Request $request, $hasilLabId)
    {
        $data = $request->validate([
            'nilai_hasil' => 'required|string',
            'nilai_rujukan' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $hasil = HasilLab::findOrFail($hasilLabId);
            $hasil->update([
                'nilai_hasil' => $data['nilai_hasil'],
                'nilai_rujukan' => $data['nilai_rujukan'],
                'catatan' => $data['keterangan'],
            ]);

            // ✅ Ambil order lab untuk notifikasi
            $detail = DB::table('order_lab_detail')
                ->where('id', $hasil->order_lab_detail_id)
                ->first();

            if ($detail) {
                $orderLab = \App\Models\OrderLab::find($detail->order_lab_id);

                // ✅✅✅ KIRIM NOTIFIKASI UPDATE ✅✅✅
                if ($orderLab) {
                    NotificationHelper::kirimNotifikasiUpdateHasilLab($orderLab, $hasil);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Hasil lab berhasil diperbarui dan notifikasi terkirim',
                'data' => $hasil,
            ]);

        } catch (\Exception $e) {
            Log::error('Error updateHasilLab: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal update hasil lab',
            ], 500);
        }
    }

    public function getLayananOrderDokter(Request $request)
    {
        try {
            $user = $request->user();

            // Pastikan user adalah dokter
            $dokter = Dokter::where('user_id', $user->id)->first();
            if (! $dokter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dokter tidak ditemukan.',
                ], 404);
            }

            // SUBQUERY ringkasan layanan per kunjungan
            $subLayanan = DB::table('penjualan_layanan as pl2')
                ->join('layanan as l', 'pl2.layanan_id', '=', 'l.id')
                ->selectRaw('pl2.kunjungan_id, GROUP_CONCAT(l.nama_layanan SEPARATOR ", ") AS ringkasan_layanan')
                ->groupBy('pl2.kunjungan_id');

            $rows = DB::table('penjualan_layanan as pl')
                ->join('kunjungan as k', 'pl.kunjungan_id', '=', 'k.id')
                ->join('pasien as p', 'k.pasien_id', '=', 'p.id')
                ->leftJoin('poli as po', 'k.poli_id', '=', 'po.id')
                ->leftJoin('emr as e', 'e.kunjungan_id', '=', 'k.id')
                ->leftJoin('perawat as pr', 'e.perawat_id', '=', 'pr.id')
                ->leftJoinSub($subLayanan, 'jl', function ($join) {
                    $join->on('jl.kunjungan_id', '=', 'pl.kunjungan_id');
                })
                ->where('k.dokter_id', $dokter->id)
                ->whereIn('k.status', ['Engaged', 'Payment'])
                ->whereIn('pl.status', ['Belum Bayar', 'Pending']) // sesuaikan value statusmu
                ->selectRaw('
                    pl.id,
                    pl.kunjungan_id,
                    pl.pasien_id,
                    pl.kode_transaksi,
                    pl.jumlah,
                    pl.total_tagihan,
                    pl.sub_total,
                    pl.uang_yang_diterima,
                    pl.kembalian,
                    pl.tanggal_transaksi,
                    pl.status AS status_pembayaran,
                    pl.created_at AS created_at_transaksi,

                    k.poli_id,
                k.tanggal_kunjungan,
                    k.no_antrian,             
                    k.status AS status_kunjungan,
                    k.keluhan_awal,                -- ✅ TAMBAH

                    p.nama_pasien AS nama_pasien,
                    p.no_emr AS no_rekam_medis,
                    p.alamat AS alamat_pasien,     -- ✅ TAMBAH

                    po.nama_poli AS nama_poli,

                    COALESCE(jl.ringkasan_layanan, "") AS ringkasan_layanan,

                    e.tekanan_darah,
                    e.suhu_tubuh,
                    e.nadi,
                    e.pernapasan,
                    e.saturasi_oksigen,
                    e.riwayat_penyakit_dahulu,
                    e.riwayat_penyakit_keluarga,
                    e.created_at AS tanggal_pemeriksaan_perawat,

                    pr.nama_perawat AS nama_perawat
                ')
                ->orderByDesc('pl.tanggal_transaksi')
                ->get();

            // Map biar bentuknya sama kayak kunjungan (ada pasien/poli/emr/perawat)
            $formatted = $rows->map(function ($r) {
                return [
                    'id' => (int) $r->id,
                    'kunjungan_id' => (int) $r->kunjungan_id,
                    'pasien_id' => (int) $r->pasien_id,
                    'no_antrian' => $r->no_antrian, // ✅ kirim apa adanya (contoh: "001")

                    'kode_transaksi' => $r->kode_transaksi,
                    'ringkasan_layanan' => (string) ($r->ringkasan_layanan ?? ''),
                    'jumlah' => (int) ($r->jumlah ?? 0),

                    'sub_total' => (float) ($r->sub_total ?? 0),
                    'total_tagihan' => (float) ($r->total_tagihan ?? 0),
                    'status_pembayaran' => (string) ($r->status_pembayaran ?? 'Belum Bayar'),
                    'tanggal_transaksi' => $r->tanggal_transaksi,
                    'created_at' => $r->created_at_transaksi,

                    'tanggal_kunjungan' => $r->tanggal_kunjungan,
                    'status_kunjungan' => (string) ($r->status_kunjungan ?? ''),

                    'keluhan_awal' => $r->keluhan_awal, // ✅ buat Keluhan/Catatan di UI

                    'pasien' => [
                        'nama_pasien' => (string) ($r->nama_pasien ?? '-'),
                        'no_emr' => (string) ($r->no_rekam_medis ?? ''),
                        'alamat' => (string) ($r->alamat_pasien ?? '-'), // ✅ buat icon lokasi di UI
                    ],

                    'poli' => [
                        'nama_poli' => (string) ($r->nama_poli ?? '-'),
                    ],

                    'emr' => [
                        'tekanan_darah' => $r->tekanan_darah,
                        'suhu_tubuh' => $r->suhu_tubuh,
                        'nadi' => $r->nadi,
                        'pernapasan' => $r->pernapasan,
                        'saturasi_oksigen' => $r->saturasi_oksigen,
                        'riwayat_penyakit_dahulu' => $r->riwayat_penyakit_dahulu,
                        'riwayat_penyakit_keluarga' => $r->riwayat_penyakit_keluarga,
                        'tanggal_pemeriksaan_perawat' => $r->tanggal_pemeriksaan_perawat,
                    ],

                    'perawat' => $r->nama_perawat ? [
                        'nama_perawat' => (string) $r->nama_perawat,
                    ] : null,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'status' => 200,
                'data' => $formatted,
                'total' => $formatted->count(),
                'dokter_info' => [
                    'id' => (int) $dokter->id,
                    'nama_dokter' => (string) $dokter->nama_dokter,
                    'user_id' => (int) $user->id,
                ],
                'message' => 'Berhasil mengambil order layanan dokter (Engaged)',
            ], 200);

        } catch (\Throwable $e) {
            Log::error('getLayananOrderDokter error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data.',
            ], 500);
        }
    }

    public function getDetailOrderLayanan($kunjunganId)
    {
        try {
            $layanan = DB::table('penjualan_layanan as pl')
                ->join('layanan as l', 'pl.layanan_id', '=', 'l.id')
                ->select(
                    'pl.*',
                    'l.nama_layanan',
                    'l.harga_layanan'
                )
                ->where('pl.kunjungan_id', $kunjunganId)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $layanan,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getRiwayatEMRPasien(Request $request, $pasienId)
    {
        try {
            $userId = Auth::id();
            $dokter = Dokter::where('user_id', $userId)->first();

            if (! $dokter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dokter tidak ditemukan',
                ], 404);
            }

            if (! $pasienId) {
                return response()->json([
                    'success' => false,
                    'message' => 'pasien_id wajib diisi',
                ], 400);
            }

            $riwayatPasien = DB::table('emr as e')
                ->join('kunjungan as k', 'e.kunjungan_id', '=', 'k.id')
                ->join('pasien as p', 'k.pasien_id', '=', 'p.id')
                ->leftJoin('poli as po', 'k.poli_id', '=', 'po.id')
                ->leftJoin('perawat as pr', 'e.perawat_id', '=', 'pr.id')
                ->leftJoin('resume_dokter as rd', 'rd.emr_id', '=', 'e.id')
                ->leftJoin('dokter as d', 'e.dokter_id', '=', 'd.id')
                ->leftJoin('jenis_spesialis as js', 'd.jenis_spesialis_id', '=', 'js.id')
                ->where('k.pasien_id', $pasienId)
                ->whereIn('k.status', ['Payment', 'Succeed', 'Canceled'])
                ->select(
                    'k.id',
                    'k.pasien_id',
                    'k.poli_id',
                    'k.tanggal_kunjungan',
                    'k.no_antrian',
                    'k.status as status_kunjungan',
                    'k.keluhan_awal',
                    'k.created_at',
                    'k.updated_at',
                    'p.nama_pasien',
                    'p.no_emr',
                    'po.nama_poli',
                    'e.id as emr_id',
                    'e.diagnosis',
                    'e.keluhan_utama',
                    'pr.id as perawat_id',
                    'pr.nama_perawat',
                    'pr.foto_perawat',
                    'pr.no_hp_perawat',
                    'd.id as dokter_id',
                    'd.nama_dokter',
                    'd.foto_dokter',
                    'js.id as spesialis_id',
                    'js.nama_spesialis',
                    DB::raw("COALESCE(rd.status, 'draft') as status"),
                    'rd.finalized_at'
                )
                ->orderByDesc(DB::raw('COALESCE(rd.finalized_at, e.updated_at, e.created_at)'))
                ->get()
                ->map(function ($row) use ($dokter) {
                    // ✅ Return array kosong untuk lab & radiologi (karena tabel tidak ada)
                    $hasilLab = [];
                    $hasilRadiologi = [];

                    return [
                        'id' => $row->id,
                        'pasien_id' => $row->pasien_id,
                        'poli_id' => $row->poli_id,
                        'tanggal_kunjungan' => $row->tanggal_kunjungan,
                        'no_antrian' => $row->no_antrian ? (string) $row->no_antrian : null,
                        'status' => $row->status,
                        'status_kunjungan' => $row->status_kunjungan,
                        'keluhan_awal' => $row->keluhan_awal,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,

                        'pasien' => [
                            'id' => $row->pasien_id,
                            'nama_pasien' => $row->nama_pasien,
                            'no_emr' => $row->no_emr,
                        ],

                        'poli' => [
                            'id' => $row->poli_id,
                            'nama_poli' => $row->nama_poli,
                        ],

                        'emr' => [
                            'id' => $row->emr_id,
                            'diagnosis' => $row->diagnosis,
                            'keluhan_utama' => $row->keluhan_utama,
                        ],

                        'perawat' => [
                            'id' => $row->perawat_id,
                            'nama_perawat' => $row->nama_perawat,
                            'foto_perawat' => $row->foto_perawat,
                            'no_hp_perawat' => $row->no_hp_perawat,
                        ],

                        'dokter_pemeriksa' => [
                            'id' => $row->dokter_id,
                            'nama_dokter' => $row->nama_dokter ?? '-',
                            'foto_dokter' => $row->foto_dokter,
                            'spesialis' => [
                                'id' => $row->spesialis_id,
                                'nama_spesialis' => $row->nama_spesialis ?? 'Umum',
                            ],
                        ],

                        // ✅ Lab & Radiologi kosong (tidak ada tabel)
                        'hasil_lab' => $hasilLab,
                        'hasil_radiologi' => $hasilRadiologi,

                        'is_my_emr' => (int) $row->dokter_id === $dokter->id,
                        'finalized_at' => $row->finalized_at,
                        'can_edit' => true,
                        'emr_id' => $row->emr_id,
                    ];
                });

            return response()->json([
                'success' => true,
                'status' => 200,
                'data' => $riwayatPasien->toArray(),
                'total_pasien' => $riwayatPasien->count(),
                'dokter_info' => [
                    'id' => $dokter->id,
                    'nama_dokter' => $dokter->nama_dokter,
                    'message' => 'Menampilkan riwayat lengkap dari SEMUA dokter',
                ],
                'message' => 'Berhasil mengambil riwayat pasien dari semua dokter',
            ], 200);

        } catch (\Throwable $e) {
            Log::error('ERROR getRiwayatEMRPasien: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDataKunjunganById(Request $request, $id)
    {
        try {
            $user = $request->user();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi',
                ], 401);
            }

            $dokter = Dokter::with(['jenisSpesialis', 'poli'])
                ->where('user_id', $user->id)
                ->first();

            if (! $dokter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokter tidak ditemukan untuk user ini',
                ], 404);
            }

            $kunjungan = Kunjungan::with([
                'pasien:id,nama_pasien,alamat,tanggal_lahir,jenis_kelamin,no_emr',
                'poli:id,nama_poli',
                'emr.perawat:id,nama_perawat,foto_perawat,no_hp_perawat',
            ])->find($id);

            if (! $kunjungan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kunjungan tidak ditemukan',
                ], 404);
            }

            if ((int) $kunjungan->dokter_id !== (int) $dokter->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kunjungan ini bukan milik dokter yang sedang login',
                ], 403);
            }

            $emr = $kunjungan->emr;

            $isKklp = $this->isDokterKKLP($dokter);
            $isPenyakitDalam = $this->isKunjunganPenyakitDalam($kunjungan);
            $formType = $this->resolveFormType($dokter, $kunjungan, $emr);

            $resolvedOrderLayananId = $this->resolveOrderLayananIdFromKunjungan($kunjungan);

            $responseData = [
                'id' => (int) $kunjungan->id,
                'pasien_id' => (int) $kunjungan->pasien_id,
                'poli_id' => (int) $kunjungan->poli_id,
                'order_layanan_id' => $resolvedOrderLayananId,

                'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                'no_antrian' => $kunjungan->no_antrian ? (string) $kunjungan->no_antrian : null,
                'status' => (string) $kunjungan->status,
                'keluhan_awal' => $kunjungan->keluhan_awal ?? null,
                'created_at' => optional($kunjungan->created_at)->toISOString(),
                'updated_at' => optional($kunjungan->updated_at)->toISOString(),

                'form_type' => $formType,
                'is_kklp' => $isKklp,
                'is_penyakit_dalam' => $isPenyakitDalam,

                'pasien' => $kunjungan->pasien ? [
                    'id' => (int) $kunjungan->pasien->id,
                    'nama_pasien' => (string) ($kunjungan->pasien->nama_pasien ?? 'Tidak ada nama'),
                    'alamat' => $kunjungan->pasien->alamat ? (string) $kunjungan->pasien->alamat : null,
                    'tanggal_lahir' => $kunjungan->pasien->tanggal_lahir,
                    'jenis_kelamin' => $kunjungan->pasien->jenis_kelamin ? (string) $kunjungan->pasien->jenis_kelamin : null,
                    'no_emr' => $kunjungan->pasien->no_emr ? (string) $kunjungan->pasien->no_emr : null,
                ] : null,

                'poli' => $kunjungan->poli ? [
                    'id' => (int) $kunjungan->poli->id,
                    'nama_poli' => (string) $kunjungan->poli->nama_poli,
                ] : null,

                'dokter' => [
                    'id' => (int) $dokter->id,
                    'nama_dokter' => (string) ($dokter->nama_dokter ?? ''),
                    'jenis_spesialis_id' => $dokter->jenis_spesialis_id ? (int) $dokter->jenis_spesialis_id : null,
                    'jenis_spesialis' => $dokter->jenisSpesialis->nama_spesialis ?? null,
                ],

                'emr' => $emr ? [
                    'id' => (int) $emr->id,
                    'keluhan_utama' => $emr->keluhan_utama,
                    'riwayat_penyakit_dahulu' => $emr->riwayat_penyakit_dahulu,
                    'riwayat_penyakit_keluarga' => $emr->riwayat_penyakit_keluarga,
                    'tekanan_darah' => $emr->tekanan_darah,
                    'suhu_tubuh' => $emr->suhu_tubuh,
                    'nadi' => $emr->nadi,
                    'pernapasan' => $emr->pernapasan,
                    'saturasi_oksigen' => $emr->saturasi_oksigen,
                    'diagnosis' => $emr->diagnosis,
                    'tanggal_pemeriksaan_perawat' => $emr->created_at,
                    'perawat' => $emr->perawat ? [
                        'id' => (int) $emr->perawat->id,
                        'nama_perawat' => (string) $emr->perawat->nama_perawat,
                        'foto_perawat' => $emr->perawat->foto_perawat,
                        'no_hp_perawat' => $emr->perawat->no_hp_perawat,
                    ] : null,
                ] : null,

                'debug_kunjungan_raw' => $kunjungan->toArray(),
                'debug_order_layanan_resolved' => [
                    'order_layanan_id' => $resolvedOrderLayananId,
                ],
            ];

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil detail kunjungan',
                'data' => $responseData,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Error getDataKunjunganById: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail kunjungan: '.$e->getMessage(),
            ], 500);
        }
    }

    private function resolveOrderLayananIdFromKunjungan($kunjungan): ?int
    {
        try {
            $query = \App\Models\OrderLayanan::query()
                ->where('pasien_id', $kunjungan->pasien_id)
                ->where('dokter_id', $kunjungan->dokter_id)
                ->where('poli_id', $kunjungan->poli_id);

            if (! empty($kunjungan->tanggal_kunjungan)) {
                $tanggalKunjungan = \Carbon\Carbon::parse($kunjungan->tanggal_kunjungan)->toDateString();

                $orderByTanggal = (clone $query)
                    ->whereDate('tanggal_order', $tanggalKunjungan)
                    ->latest('id')
                    ->first();

                if ($orderByTanggal) {
                    return (int) $orderByTanggal->id;
                }
            }

            $order = $query->latest('id')->first();

            return $order ? (int) $order->id : null;
        } catch (\Throwable $e) {
            Log::warning('resolveOrderLayananIdFromKunjungan gagal', [
                'kunjungan_id' => $kunjungan->id ?? null,
                'pasien_id' => $kunjungan->pasien_id ?? null,
                'dokter_id' => $kunjungan->dokter_id ?? null,
                'poli_id' => $kunjungan->poli_id ?? null,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function getPerawatByDokter(Request $request)
    {
        try {
            $userId = Auth::id();
            if (! $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            // Ambil dokter berdasarkan user login
            $dokter = Dokter::with('poli')->where('user_id', $userId)->first();
            if (! $dokter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dokter tidak ditemukan',
                ], 404);
            }

            // 1️⃣ Ambil semua dokter_poli yang dimiliki dokter ini
            $dokterPoliIds = \App\Models\DokterPoli::where('dokter_id', $dokter->id)
                ->pluck('id');

            if ($dokterPoliIds->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Dokter tidak memiliki poli atau belum ada perawat',
                    'dokter' => [
                        'id' => $dokter->id,
                        'nama_dokter' => $dokter->nama_dokter,
                    ],
                    'data' => [],
                ]);
            }

            // 2️⃣ Ambil pivot + relasi, tapi ID tetap pakai kolom di pivot (AMAN)
            $pivotRows = \App\Models\PerawatDokterPoli::with(['perawat', 'dokterPoli.poli'])
                ->whereIn('dokter_poli_id', $dokterPoliIds)
                ->get();

            // 3️⃣ Map jadi bentuk rapi untuk API response
            $perawat = $pivotRows->map(function ($pivot) {
                // ⚠️ PAKAI ID DARI PIVOT, BUKAN DARI RELASI (BIAR GAK ERROR COLLECTION)
                $perawatId = $pivot->perawat_id ?? null;
                $p = $pivot->perawat;          // bisa Model, bisa null, bisa collection → aman kita cek
                $dokterPoli = $pivot->dokterPoli;
                $poli = $dokterPoli && $dokterPoli->poli ? $dokterPoli->poli : null;

                // Kalau perawatId kosong, skip (nanti difilter)
                return [
                    'id' => $perawatId,
                    'nama_perawat' => is_object($p) ? ($p->nama_perawat ?? null) : null,
                    'foto_perawat' => is_object($p) ? ($p->foto_perawat ?? null) : null,
                    'no_hp_perawat' => is_object($p) ? ($p->no_hp_perawat ?? null) : null,
                    'poli' => $poli ? [
                        'id' => $poli->id,
                        'nama_poli' => $poli->nama_poli,
                    ] : null,
                ];
            })
            // buang yang id-nya null
                ->filter(fn ($row) => ! is_null($row['id']))
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil data perawat',
                'dokter' => [
                    'id' => $dokter->id,
                    'nama_dokter' => $dokter->nama_dokter,
                ],
                'data' => $perawat,
            ]);

        } catch (\Throwable $e) {
            Log::error('ERROR getPerawatByDokter: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data perawat',
            ], 500);
        }
    }

    public function getEmrById($emr_id)
    {
        $emr = Emr::with([
            'pasien:id,no_emr,nama_pasien,nik,no_bpjs,no_hp_pasien',
            'dokter:id,nama_dokter',
            'perawat:id,nama_perawat,no_hp_perawat',
            'poli:id,nama_poli',
            'kunjungan:id,tanggal_kunjungan',
        ])
            ->find($emr_id);

        if (! $emr) {
            return response()->json([
                'success' => false,
                'message' => 'EMR tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $data = [
            'emr' => [
                'id' => $emr->id,
                'kunjungan_id' => $emr->kunjungan_id,
                'pasien_id' => $emr->pasien_id,
                'dokter_id' => $emr->dokter_id,
                'poli_id' => $emr->poli_id,
                'perawat_id' => $emr->perawat_id,
                'resep_id' => $emr->resep_id,
                'keluhan_utama' => $emr->keluhan_utama,
                'riwayat_penyakit_dahulu' => $emr->riwayat_penyakit_dahulu,
                'riwayat_penyakit_keluarga' => $emr->riwayat_penyakit_keluarga,
                'tekanan_darah' => $emr->tekanan_darah,
                'suhu_tubuh' => $emr->suhu_tubuh,
                'nadi' => $emr->nadi,
                'pernapasan' => $emr->pernapasan,
                'saturasi_oksigen' => $emr->saturasi_oksigen,
                'diagnosis' => $emr->diagnosis,
                'created_at' => $emr->created_at,
                'updated_at' => $emr->updated_at,
            ],

            'pasien' => $emr->pasien ? [
                'id' => $emr->pasien->id,
                'no_emr' => $emr->pasien->no_emr,
                'nama_pasien' => $emr->pasien->nama_pasien,
                'nik' => $emr->pasien->nik,
                'no_bpjs' => $emr->pasien->no_bpjs,
                'no_hp_pasien' => $emr->pasien->no_hp_pasien,
            ] : null,

            'dokter_pemeriksa' => $emr->dokter ? [
                'id' => $emr->dokter->id,
                'nama_dokter' => $emr->dokter->nama_dokter,
            ] : null,

            'perawat_pemeriksa' => $emr->perawat ? [
                'id' => $emr->perawat->id,
                'nama_perawat' => $emr->perawat->nama_perawat,
                'no_hp_perawat' => $emr->perawat->no_hp_perawat,
            ] : null,

            'poli' => $emr->poli ? [
                'id' => $emr->poli->id,
                'nama_poli' => $emr->poli->nama_poli,
            ] : null,

            'kunjungan' => $emr->kunjungan ? [
                'id' => $emr->kunjungan->id,
                'tanggal_kunjungan' => $emr->kunjungan->tanggal_kunjungan,
            ] : null,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Detail EMR berdasarkan ID',
            'data' => $data,
        ]);
    }

    public function saveEMRLayanan(Request $request)
    {
        try {

            // ✅ DEBUG WAJIB: biar pasti request masuk ke method ini
            Log::info('✅ HIT saveEMRLayanan', [
                'user_id' => Auth::id(),
                'kunjungan_id' => $request->kunjungan_id,
                'payload' => $request->all(),
            ]);

            // ✅ VALIDASI
            $request->validate([
                'kunjungan_id' => 'required|exists:kunjungan,id',
                'diagnosis' => 'required|string',

                // anamnesis & vital opsional
                'keluhan_utama' => 'nullable|string',
                'riwayat_penyakit_dahulu' => 'nullable|string',
                'riwayat_penyakit_keluarga' => 'nullable|string',
                'tekanan_darah' => 'nullable|string|max:20',
                'suhu_tubuh' => 'nullable|string|max:10',
                'nadi' => 'nullable|string|max:10',
                'pernapasan' => 'nullable|string|max:10',
                'saturasi_oksigen' => 'nullable|string|max:10',

                // ✅ layanan tambahan OPSIONAL (sesuai UI dokter)
                'layanan' => 'nullable|array',
                'layanan.*.layanan_id' => 'required_with:layanan|exists:layanan,id',
                'layanan.*.jumlah' => 'required_with:layanan|integer|min:1',
            ]);

            $user_id = \Illuminate\Support\Facades\Auth::id();
            $dokter = \App\Models\Dokter::where('user_id', $user_id)->firstOrFail();

            $kunjungan = \App\Models\Kunjungan::where('id', $request->kunjungan_id)
                ->with(['pasien', 'emr.perawat'])
                ->firstOrFail();

            // =========================================================
            // ✅ FIX INTI: DOKTER BOLEH LANJUT WALAU EMR BELUM ADA
            // =========================================================
            $emr = $kunjungan->emr;

            // kalau relasi belum ke-load / beda case, cari manual
            if (! $emr) {
                $emr = \App\Models\Emr::where('kunjungan_id', $kunjungan->id)->first();
            }

            // kalau benar-benar belum ada, bikin baru
            if (! $emr) {
                $emr = \App\Models\Emr::create([
                    'kunjungan_id' => $kunjungan->id,
                    'dokter_id' => $dokter->id,
                    'perawat_id' => null,

                    'keluhan_utama' => $request->input('keluhan_utama'),
                    'riwayat_penyakit_dahulu' => $request->input('riwayat_penyakit_dahulu'),
                    'riwayat_penyakit_keluarga' => $request->input('riwayat_penyakit_keluarga'),
                    'tekanan_darah' => $request->input('tekanan_darah'),
                    'suhu_tubuh' => $request->input('suhu_tubuh'),
                    'nadi' => $request->input('nadi'),
                    'pernapasan' => $request->input('pernapasan'),
                    'saturasi_oksigen' => $request->input('saturasi_oksigen'),

                    'diagnosis' => null,
                    'resep_id' => null,

                    // kalau kolommu beda / gak ada: hapus 2 baris ini
                    'tanggal' => now()->toDateString(),
                    'waktu' => now()->format('H:i'),
                ]);
            } else {
                // kalau EMR sudah ada tapi belum punya dokter_id, isi
                if (empty($emr->dokter_id)) {
                    $emr->update(['dokter_id' => $dokter->id]);
                    $emr->refresh();
                }

                // kalau EMR sudah "dikunci" dokter lain
                if (! empty($emr->dokter_id) && (int) $emr->dokter_id !== (int) $dokter->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'EMR ini bukan untuk dokter yang sedang login',
                    ], 403);
                }
            }

            // pastikan relation kebaca untuk proses lanjutan
            $kunjungan->setRelation('emr', $emr);

            // ✅ kalau status kamu memang wajib Engaged, biarkan.
            if ($kunjungan->status !== 'Engaged') {
                return response()->json([
                    'success' => false,
                    'message' => 'Kunjungan harus dalam status Engaged untuk dapat melengkapi EMR layanan',
                ], 400);
            }

            Log::info('saveEMRLayanan: incoming', [
                'kunjungan_id' => $kunjungan->id,
                'emr_id' => $emr->id,
                'dokter_id' => $dokter->id,
                'layanan_payload' => $request->layanan,
            ]);

            $result = DB::transaction(function () use ($request, $kunjungan, $emr) {

                // ================== UPDATE EMR ==================
                $updateData = [
                    'diagnosis' => $request->diagnosis,
                ];

                $editableFields = [
                    'keluhan_utama',
                    'riwayat_penyakit_dahulu',
                    'riwayat_penyakit_keluarga',
                    'tekanan_darah',
                    'suhu_tubuh',
                    'nadi',
                    'pernapasan',
                    'saturasi_oksigen',
                ];

                foreach ($editableFields as $field) {
                    if ($request->filled($field)) {
                        $updateData[$field] = $request->input($field);
                    }
                }

                $emr->update($updateData);

                // ================== LAYANAN TAMBAHAN (OPSIONAL) ==================
                $layananReq = $request->input('layanan', []);
                if (is_array($layananReq) && count($layananReq) > 0) {

                    $pasienId = $kunjungan->pasien_id;

                    $existingTransaction = \App\Models\PenjualanLayanan::where('kunjungan_id', $kunjungan->id)->first();
                    $kodeTransaksiBase = $existingTransaction
                        ? $existingTransaction->kode_transaksi
                        : strtoupper(uniqid('TRX-'));

                    foreach ($layananReq as $layananData) {
                        $layanan = \App\Models\Layanan::findOrFail($layananData['layanan_id']);
                        $jumlah = (int) $layananData['jumlah'];

                        $harga = (float) ($layanan->harga_sebelum_diskon ?? $layanan->harga_layanan ?? 0);
                        $subTotal = $harga * $jumlah;

                        \App\Models\KunjunganLayanan::create([
                            'kunjungan_id' => $kunjungan->id,
                            'layanan_id' => $layanan->id,
                            'jumlah' => $jumlah,
                        ]);

                        \App\Models\PenjualanLayanan::create([
                            'pasien_id' => $pasienId,
                            'layanan_id' => $layanan->id,
                            'kategori_layanan_id' => $layanan->kategori_layanan_id ?? null,
                            'kunjungan_id' => $kunjungan->id,
                            'metode_pembayaran_id' => null,
                            'kode_transaksi' => $kodeTransaksiBase,
                            'jumlah' => $jumlah,
                            'sub_total' => $subTotal,
                            'total_tagihan' => $subTotal,

                            'diskon_tipe' => null,
                            'diskon_nilai' => 0,
                            'total_setelah_diskon' => null,

                            'tanggal_transaksi' => now(),
                            'status' => 'Belum Bayar',
                        ]);
                    }
                }

                // ✅ set status kunjungan jadi Payment (kalau memang flow kamu begitu)
                $kunjungan->update(['status' => 'Payment']);

                // kalau ada kalkulasi total, jalankan kalau methodnya ada
                $totalTagihan = null;
                $pembayaran = null;

                if (method_exists($this, 'calculateTotalTagihan')) {
                    $totalTagihan = $this->calculateTotalTagihan($kunjungan, $emr->resep_id);

                    $pembayaran = \App\Models\Pembayaran::updateOrCreate(
                        ['emr_id' => $emr->id],
                        [
                            'total_tagihan' => $totalTagihan,
                            'uang_yang_diterima' => 0,
                            'kembalian' => 0,
                            'kode_transaksi' => strtoupper(uniqid('TRX_')),
                            'metode_pembayaran_id' => null,
                            'tanggal_pembayaran' => null,
                            'status' => 'Belum Bayar',
                            'catatan' => 'Menunggu pembayaran di kasir - EMR layanan telah dilengkapi dokter',
                        ]
                    );
                }

                return [
                    'emr' => $emr->fresh(['perawat']),
                    'kunjungan' => $kunjungan->fresh(),
                    'pembayaran' => $pembayaran,
                    'billing_info' => [
                        'total_tagihan' => $totalTagihan,
                        'layanan_count' => is_array($layananReq) ? count($layananReq) : 0,
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'EMR layanan berhasil disimpan.',
                'data' => $result,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('saveEMRLayanan validation error: ', $e->errors());

            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error saveEMRLayanan: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan EMR layanan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function editEMR(Request $request, $emrId)
    {
        try {
            Log::info('=== EDIT EMR START (EMR-based validation) ===', [
                'emr_id' => $emrId,
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);

            $user_id = Auth::id();
            $dokter = Dokter::where('user_id', $user_id)->firstOrFail();

            // ✅ PERBAIKAN: Validasi input yang lebih lengkap
            $request->validate([
                'keluhan_utama' => 'required|string',
                'diagnosis' => 'required|string',
                'riwayat_penyakit_dahulu' => 'nullable|string',
                'riwayat_penyakit_keluarga' => 'nullable|string',
                'tekanan_darah' => 'nullable|string|max:20',
                'suhu_tubuh' => 'nullable|string|max:10',
                'nadi' => 'nullable|string|max:10',
                'pernapasan' => 'nullable|string|max:10',
                'saturasi_oksigen' => 'nullable|string|max:10',
            ], [
                'keluhan_utama.required' => 'Keluhan utama harus diisi',
                'diagnosis.required' => 'Diagnosis harus diisi',
                'tekanan_darah.max' => 'Tekanan darah maksimal 20 karakter',
                'suhu_tubuh.max' => 'Suhu tubuh maksimal 10 karakter',
                'nadi.max' => 'Nadi maksimal 10 karakter',
                'pernapasan.max' => 'Pernapasan maksimal 10 karakter',
                'saturasi_oksigen.max' => 'Saturasi oksigen maksimal 10 karakter',
            ]);

            // ✅ PERBAIKAN: Validasi EMR berdasarkan dokter_id langsung
            $emr = EMR::with(['kunjungan.pasien', 'kunjungan.poli', 'perawat'])
                ->where('id', $emrId)
                ->where('dokter_id', $dokter->id) // ✅ Langsung filter berdasarkan dokter_id
                ->firstOrFail();

            Log::info('EMR found for editing (EMR-based):', [
                'emr_id' => $emr->id,
                'kunjungan_id' => $emr->kunjungan_id,
                'dokter_id' => $emr->dokter_id,
                'perawat_id' => $emr->perawat_id,
                'login_dokter_id' => $dokter->id,
            ]);

            // ✅ PERBAIKAN: Update EMR dengan semua field yang diedit
            $updateData = [
                'keluhan_utama' => $request->keluhan_utama,
                'diagnosis' => $request->diagnosis,
            ];

            // Field opsional - update hanya jika ada di request
            $optionalFields = [
                'riwayat_penyakit_dahulu',
                'riwayat_penyakit_keluarga',
                'tekanan_darah',
                'suhu_tubuh',
                'nadi',
                'pernapasan',
                'saturasi_oksigen',
            ];

            foreach ($optionalFields as $field) {
                if ($request->has($field)) {
                    $updateData[$field] = $request->input($field);

                    Log::info("Field {$field} will be updated", [
                        'old_value' => $emr->$field,
                        'new_value' => $request->input($field),
                    ]);
                }
            }

            $emr->update($updateData);

            Log::info('✅ EMR updated successfully (EMR-based):', [
                'emr_id' => $emr->id,
                'dokter_id' => $emr->dokter_id,
                'fields_updated' => array_keys($updateData),
            ]);

            // Load fresh data dengan relasi
            $emr->load(['kunjungan.pasien', 'kunjungan.poli', 'perawat', 'resep.obat']);

            return response()->json([
                'success' => true,
                'message' => 'EMR berhasil diperbarui',
                'data' => [
                    'emr' => [
                        'id' => $emr->id,
                        'keluhan_utama' => $emr->keluhan_utama,
                        'diagnosis' => $emr->diagnosis,
                        'riwayat_penyakit_dahulu' => $emr->riwayat_penyakit_dahulu,
                        'riwayat_penyakit_keluarga' => $emr->riwayat_penyakit_keluarga,
                        'tekanan_darah' => $emr->tekanan_darah,
                        'suhu_tubuh' => $emr->suhu_tubuh,
                        'nadi' => $emr->nadi,
                        'pernapasan' => $emr->pernapasan,
                        'saturasi_oksigen' => $emr->saturasi_oksigen,
                        'tanggal_pemeriksaan' => $emr->created_at,
                        'last_updated' => $emr->updated_at,
                    ],
                    'kunjungan' => [
                        'id' => $emr->kunjungan->id,
                        'tanggal_kunjungan' => $emr->kunjungan->tanggal_kunjungan,
                        'no_antrian' => $emr->kunjungan->no_antrian,
                        'status' => $emr->kunjungan->status,
                    ],
                    'pasien' => [
                        'id' => $emr->kunjungan->pasien->id,
                        'nama_pasien' => $emr->kunjungan->pasien->nama_pasien,
                        'no_emr' => $emr->kunjungan->pasien->no_emr,
                    ],
                    'perawat' => $emr->perawat ? [
                        'id' => $emr->perawat->id,
                        'nama_perawat' => $emr->perawat->nama_perawat,
                    ] : null,
                ],
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('EMR not found or no access (EMR-based):', [
                'emr_id' => $emrId,
                'user_id' => Auth::id(),
                'dokter_id' => $dokter->id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'EMR tidak ditemukan atau Anda tidak memiliki akses untuk mengeditnya',
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('EMR edit validation error:', $e->errors());

            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error editing EMR (EMR-based):', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'emr_id' => $emrId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui EMR: '.$e->getMessage(),
            ], 500);
        }
    }

    // punya pasien
    public function pasienListOrderRadiologi(Request $request)
    {
        try {
            $user = $request->user();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $pasien = \App\Models\Pasien::where('user_id', $user->id)->first();
            if (! $pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pasien tidak ditemukan',
                ], 404);
            }

            // ✅ Subquery progress: total & selesai per order
            $progressSub = DB::table('order_radiologi_detail as ord')
                ->selectRaw("
                ord.order_radiologi_id,
                COUNT(*) as total,
                SUM(CASE WHEN ord.status_pemeriksaan = 'Selesai' THEN 1 ELSE 0 END) as selesai
            ")
                ->groupBy('ord.order_radiologi_id');

            // ✅ Ambil semua order radiologi milik pasien ini (tanpa duplikat + tanpa N+1)
            $orders = DB::table('order_radiologi as o')
                ->where('o.pasien_id', $pasien->id)
                ->leftJoin('dokter as d', 'd.id', '=', 'o.dokter_id')

                // ✅ dokter → poli lewat pivot
                ->leftJoin('dokter_poli as dp', 'dp.dokter_id', '=', 'd.id')
                ->leftJoin('poli as p', 'p.id', '=', 'dp.poli_id')

                // ✅ join progress
                ->leftJoinSub($progressSub, 'pr', function ($join) {
                    $join->on('pr.order_radiologi_id', '=', 'o.id');
                })

                ->select(
                    'o.id',
                    'o.no_order_radiologi',
                    'o.tanggal_order',
                    'o.tanggal_pemeriksaan',
                    'o.jam_pemeriksaan',
                    'o.status',
                    'o.created_at',
                    'd.nama_dokter',
                    'd.foto_dokter',

                    // ✅ cegah duplikat: ambil 1 poli saja (atau ganti GROUP_CONCAT kalau mau semua)
                    DB::raw('MAX(p.nama_poli) as nama_poli'),

                    // ✅ progress (default 0 kalau null)
                    DB::raw('COALESCE(pr.total, 0) as total_detail'),
                    DB::raw('COALESCE(pr.selesai, 0) as selesai_detail')
                )
                ->groupBy(
                    'o.id',
                    'o.no_order_radiologi',
                    'o.tanggal_order',
                    'o.tanggal_pemeriksaan',
                    'o.jam_pemeriksaan',
                    'o.status',
                    'o.created_at',
                    'd.nama_dokter',
                    'd.foto_dokter',
                    'pr.total',
                    'pr.selesai'
                )
                ->orderByDesc('o.created_at')
                ->get()
                ->map(function ($o) {
                    return [
                        'id' => (int) $o->id,
                        'no_order_radiologi' => $o->no_order_radiologi,
                        'tanggal_order' => $o->tanggal_order,
                        'tanggal_pemeriksaan' => $o->tanggal_pemeriksaan,
                        'jam_pemeriksaan' => $o->jam_pemeriksaan,
                        'status' => $o->status,
                        'progress' => [
                            'total' => (int) $o->total_detail,
                            'selesai' => (int) $o->selesai_detail,
                        ],
                        'dokter' => [
                            'nama_dokter' => $o->nama_dokter ?? '-',
                            'foto_dokter' => $o->foto_dokter,
                        ],
                        'poli' => [
                            'nama_poli' => $o->nama_poli ?? 'Radiologi',
                        ],
                        'created_at' => $o->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'List order radiologi berhasil diambil',
                'data' => $orders,
                'total' => $orders->count(),
            ], 200);

        } catch (\Throwable $e) {
            Log::error('ERROR pasienListOrderRadiologi: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data order radiologi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ✅ PASIEN - Detail Order Radiologi
     * GET /api/pasien/order-radiologi/{orderId}
     * Auth: Pasien (via Sanctum)
     */
    public function pasienDetailOrderRadiologi(Request $request, $orderId)
    {
        try {
            $user = $request->user();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $pasien = \App\Models\Pasien::where('user_id', $user->id)->first();
            if (! $pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pasien tidak ditemukan',
                ], 404);
            }

            // =========================
            // Order + validasi kepemilikan
            // =========================
            $order = DB::table('order_radiologi as o')
                ->leftJoin('dokter as dk', 'dk.id', '=', 'o.dokter_id')
                ->leftJoin('pasien as ps', 'ps.id', '=', 'o.pasien_id')
                ->where('o.id', $orderId)
                ->where('o.pasien_id', $pasien->id)
                ->select(
                    'o.id',
                    'o.no_order_radiologi',
                    'o.tanggal_order',
                    'o.tanggal_pemeriksaan',
                    'o.jam_pemeriksaan',
                    'o.status',
                    'o.created_at',

                    'dk.nama_dokter',
                    'dk.foto_dokter',

                    'ps.nama_pasien',
                    'ps.jenis_kelamin',
                    'ps.tanggal_lahir'
                )
                ->first();

            if (! $order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order radiologi tidak ditemukan',
                ], 404);
            }

            // =========================
            // Detail + hasil (tanpa dokter_radiologi_id)
            // FIX 1: deskripsi tidak ada di jenis_pemeriksaan_radiologi -> NULL as deskripsi
            // FIX 2: dokter_radiologi_id tidak ada di hasil_radiologi -> hapus join dokter dr
            // =========================
            $details = DB::table('order_radiologi_detail as od')
                ->join('jenis_pemeriksaan_radiologi as j', 'j.id', '=', 'od.jenis_pemeriksaan_radiologi_id')
                ->leftJoin('hasil_radiologi as h', 'h.order_radiologi_detail_id', '=', 'od.id')
                ->leftJoin('perawat as pr', 'pr.id', '=', 'h.perawat_id')
                ->where('od.order_radiologi_id', $orderId)
                ->select(
                    'od.id as order_radiologi_detail_id',
                    'od.status_pemeriksaan',

                    'j.id as jenis_id',
                    'j.kode_pemeriksaan',
                    'j.nama_pemeriksaan',
                    DB::raw('NULL as deskripsi'),
                    'j.harga_pemeriksaan_radiologi',

                    'h.id as hasil_id',
                    'h.foto_hasil_radiologi',
                    'h.keterangan as interpretasi',
                    'h.tanggal_pemeriksaan as tanggal_hasil',
                    'h.jam_pemeriksaan as jam_hasil',

                    'pr.nama_perawat as radiografer_nama'
                )
                ->get()
                ->map(function ($row) {
                    return [
                        'order_radiologi_detail_id' => (int) $row->order_radiologi_detail_id,
                        'status_pemeriksaan' => $row->status_pemeriksaan,

                        'pemeriksaan' => [
                            'id' => (int) $row->jenis_id,
                            'kode_pemeriksaan' => $row->kode_pemeriksaan,
                            'nama_pemeriksaan' => $row->nama_pemeriksaan,
                            'deskripsi' => $row->deskripsi, // null (aman)
                            'harga' => (float) $row->harga_pemeriksaan_radiologi,
                        ],

                        'hasil' => $row->hasil_id ? [
                            'id' => (int) $row->hasil_id,
                            'foto_url' => $row->foto_hasil_radiologi
                                ? asset('storage/'.$row->foto_hasil_radiologi)
                                : null,
                            'interpretasi' => $row->interpretasi,
                            'tanggal_pemeriksaan' => $row->tanggal_hasil,
                            'jam_pemeriksaan' => $row->jam_hasil,
                            'radiografer' => $row->radiografer_nama,
                        ] : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Detail order radiologi berhasil diambil',
                'data' => [
                    'id' => (int) $order->id,
                    'no_order_radiologi' => $order->no_order_radiologi,
                    'tanggal_order' => $order->tanggal_order,
                    'tanggal_pemeriksaan' => $order->tanggal_pemeriksaan,
                    'jam_pemeriksaan' => $order->jam_pemeriksaan,
                    'status' => $order->status,

                    'dokter' => [
                        'nama_dokter' => $order->nama_dokter ?? '-',
                        'foto_dokter' => $order->foto_dokter,
                    ],

                    'pasien' => [
                        'nama_pasien' => $order->nama_pasien,
                        'jenis_kelamin' => $order->jenis_kelamin,
                        'tanggal_lahir' => $order->tanggal_lahir,
                    ],

                    'detail' => $details,
                    'created_at' => $order->created_at,
                ],
            ], 200);

        } catch (\Throwable $e) {
            Log::error('ERROR pasienDetailOrderRadiologi: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail order radiologi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ✅ PASIEN - List Hasil Radiologi (yang sudah selesai)
     * GET /api/pasien/hasil-radiologi
     * Auth: Pasien (via Sanctum)
     */
    public function pasienListHasilRadiologi(Request $request)
    {
        try {
            $user = $request->user();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $pasien = \App\Models\Pasien::where('user_id', $user->id)->first();
            if (! $pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pasien tidak ditemukan',
                ], 404);
            }

            $hasil = DB::table('hasil_radiologi as h')
                ->join('order_radiologi_detail as od', 'od.id', '=', 'h.order_radiologi_detail_id')
                ->join('order_radiologi as o', 'o.id', '=', 'od.order_radiologi_id')
                ->join('jenis_pemeriksaan_radiologi as j', 'j.id', '=', 'od.jenis_pemeriksaan_radiologi_id')
                ->leftJoin('dokter as d', 'd.id', '=', 'o.dokter_id')
                ->leftJoin('perawat as pr', 'pr.id', '=', 'h.perawat_id')
                ->where('o.pasien_id', $pasien->id)
                ->where('od.status_pemeriksaan', 'Selesai')
                ->when($request->query('kunjungan_id'), function ($query, $kunjunganId) {
                    $query->where('o.kunjungan_id', $kunjunganId);
                })
                ->select(
                    'h.id as hasil_id',
                    'h.foto_hasil_radiologi',
                    'h.keterangan',
                    'h.tanggal_pemeriksaan',
                    'h.jam_pemeriksaan',

                    'o.no_order_radiologi',
                    'o.tanggal_order',

                    'j.nama_pemeriksaan',
                    'j.kode_pemeriksaan',
                    'j.harga_pemeriksaan_radiologi', // ✅ TAMBAHKAN INI

                    'd.nama_dokter',
                    'pr.nama_perawat as radiografer_nama'
                )
                ->orderByDesc('h.tanggal_pemeriksaan')
                ->orderByDesc('h.jam_pemeriksaan')
                ->get()
                ->map(function ($h) {
                    return [
                        'hasil_id' => (int) $h->hasil_id,
                        'foto_url' => $h->foto_hasil_radiologi
                            ? asset('storage/'.$h->foto_hasil_radiologi)
                            : null,
                        'interpretasi' => $h->keterangan,
                        'tanggal_pemeriksaan' => $h->tanggal_pemeriksaan,
                        'jam_pemeriksaan' => $h->jam_pemeriksaan,

                        'pemeriksaan' => [
                            'nama_pemeriksaan' => $h->nama_pemeriksaan,
                            'kode_pemeriksaan' => $h->kode_pemeriksaan,
                            'harga_pemeriksaan_radiologi' => (float) $h->harga_pemeriksaan_radiologi, // ✅ KIRIM INI
                        ],

                        'order' => [
                            'no_order_radiologi' => $h->no_order_radiologi,
                            'tanggal_order' => $h->tanggal_order,
                        ],

                        'dokter' => [
                            'nama_dokter' => $h->nama_dokter ?? '-',
                        ],

                        'radiografer' => [
                            'nama' => $h->radiografer_nama ?? '-',
                        ],
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'List hasil radiologi berhasil diambil',
                'data' => $hasil,
                'total' => $hasil->count(),
            ], 200);

        } catch (\Throwable $e) {
            Log::error('ERROR pasienListHasilRadiologi: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil hasil radiologi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ✅ PASIEN - Detail Hasil Radiologi
     * GET /api/pasien/hasil-radiologi/{hasilId}
     * Auth: Pasien (via Sanctum)
     */
    public function pasienDetailHasilRadiologi(Request $request, $hasilId)
    {
        try {
            $user = $request->user();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $pasien = \App\Models\Pasien::where('user_id', $user->id)->first();
            if (! $pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pasien tidak ditemukan',
                ], 404);
            }

            // Ambil detail hasil + validasi kepemilikan
            $hasil = DB::table('hasil_radiologi as h')
                ->join('order_radiologi_detail as od', 'od.id', '=', 'h.order_radiologi_detail_id')
                ->join('order_radiologi as o', 'o.id', '=', 'od.order_radiologi_id')
                ->join('jenis_pemeriksaan_radiologi as j', 'j.id', '=', 'od.jenis_pemeriksaan_radiologi_id')
                ->leftJoin('dokter as d', 'd.id', '=', 'o.dokter_id')
                ->leftJoin('perawat as pr', 'pr.id', '=', 'h.perawat_id')
                ->leftJoin('dokter as dr', 'dr.id', '=', 'h.dokter_radiologi_id')
                ->where('h.id', $hasilId)
                ->where('o.pasien_id', $pasien->id) // ✅ Validasi kepemilikan
                ->select(
                    'h.*',
                    'o.no_order_radiologi',
                    'o.tanggal_order',
                    'j.nama_pemeriksaan',
                    'j.kode_pemeriksaan',
                    'j.deskripsi',
                    'd.nama_dokter as dokter_order',
                    'pr.nama_perawat as radiografer_nama',
                    'dr.nama_dokter as dokter_radiologi_nama'
                )
                ->first();

            if (! $hasil) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hasil radiologi tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail hasil radiologi berhasil diambil',
                'data' => [
                    'id' => (int) $hasil->id,
                    'foto_url' => $hasil->foto_hasil_radiologi
                        ? asset('storage/'.$hasil->foto_hasil_radiologi)
                        : null,
                    'interpretasi' => $hasil->keterangan,
                    'tanggal_pemeriksaan' => $hasil->tanggal_pemeriksaan,
                    'jam_pemeriksaan' => $hasil->jam_pemeriksaan,

                    'pemeriksaan' => [
                        'nama_pemeriksaan' => $hasil->nama_pemeriksaan,
                        'kode_pemeriksaan' => $hasil->kode_pemeriksaan,
                        'deskripsi' => $hasil->deskripsi,
                    ],

                    'order' => [
                        'no_order_radiologi' => $hasil->no_order_radiologi,
                        'tanggal_order' => $hasil->tanggal_order,
                    ],

                    'dokter_order' => [
                        'nama_dokter' => $hasil->dokter_order ?? '-',
                    ],

                    'radiografer' => [
                        'nama' => $hasil->radiografer_nama ?? '-',
                    ],

                    'dokter_radiologi' => [
                        'nama' => $hasil->dokter_radiologi_nama ?? '-',
                    ],

                    'created_at' => $hasil->created_at,
                ],
            ], 200);

        } catch (\Throwable $e) {
            Log::error('ERROR pasienDetailHasilRadiologi: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail hasil radiologi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function getAuthPasien(Request $request): ?\App\Models\Pasien
    {
        $user = $request->user();
        if (! $user) {
            return null;
        }

        // Coba ambil dari relasi dulu
        if ($user->relationLoaded('pasien') && $user->pasien) {
            return $user->pasien;
        }

        // Kalau tidak ada, query langsung
        return \App\Models\Pasien::where('user_id', $user->id)->first();
    }

    private function currentPasienId(Request $request): ?int
    {
        $user = $request->user();
        if (! $user) {
            return null;
        }

        // Cek relasi pasien() di User model (jika ada)
        // if ($user->pasien && $user->pasien->id) return (int)$user->pasien->id;

        // Fallback: query langsung
        $p = DB::table('pasien')->where('user_id', $user->id)->first();

        return $p?->id ? (int) $p->id : null;
    }

    public function getNotifikasiPasien(Request $request)
    {
        try {
            $user = $request->user();

            $perPage = (int) $request->get('per_page', 50);
            $perPage = max(1, min($perPage, 100)); // batasi biar aman
            $onlyUnread = (int) $request->get('only_unread', 0) === 1;

            // unread_count harus dari DB (jangan dari hasil limit)
            $unreadCount = DB::table('notifications')
                ->where('user_id', $user->id)
                ->whereNull('read_at')
                ->count();

            $q = DB::table('notifications')
                ->where('user_id', $user->id)
                ->orderByDesc('created_at');

            if ($onlyUnread) {
                $q->whereNull('read_at');
            }

            // kalau kamu mau pagination:
            $paginator = $q->paginate($perPage);

            $items = collect($paginator->items())->map(function ($n) {
                $decoded = [];
                if (! empty($n->data)) {
                    $tmp = json_decode($n->data, true);
                    $decoded = is_array($tmp) ? $tmp : [];
                }

                return [
                    'id' => $n->id,
                    'title' => $n->title ?? '',
                    'body' => $n->body ?? '',
                    'data' => $decoded,
                    'is_read' => ! is_null($n->read_at),
                    'sent_at' => $n->sent_at ?? null,
                    'read_at' => $n->read_at,
                    'created_at' => $n->created_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $items,
                    'unread_count' => $unreadCount,
                    'pagination' => [
                        'current_page' => $paginator->currentPage(),
                        'per_page' => $paginator->perPage(),
                        'total' => $paginator->total(),
                        'last_page' => $paginator->lastPage(),
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error getNotifikasiPasien: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil notifikasi',
            ], 500);
        }
    }

    /**
     * ✅ PASIEN - Get Hasil Lab by Kunjungan ID
     * GET /api/pasien/hasil-lab/kunjungan/{kunjungan_id}
     * Auth: Pasien (via Sanctum)
     *
     * Purpose: Mendapatkan hasil lab HANYA untuk kunjungan tertentu
     * Filter: order_lab yang terkait dengan kunjungan_id tersebut
     */
    public function getHasilLabByKunjungan(Request $request, $kunjunganId)
    {
        try {
            $user = $request->user();
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

            // ✅ Verify kunjungan belongs to this pasien
            $kunjungan = Kunjungan::where('id', $kunjunganId)
                ->where('pasien_id', $pasien->id)
                ->first();

            if (! $kunjungan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kunjungan tidak ditemukan',
                ], 404);
            }

            // ✅ FIX: Filter by kunjungan_id through order_lab table
            // This ensures ONLY lab results for THIS specific visit are shown
            $hasilLab = DB::table('order_lab as ol')
                ->join('order_lab_detail as old', 'ol.id', '=', 'old.order_lab_id')
                ->join('jenis_pemeriksaan_lab as jpl', 'old.jenis_pemeriksaan_lab_id', '=', 'jpl.id')
                ->leftJoin('satuan_lab as sl', 'jpl.satuan_lab_id', '=', 'sl.id')
                ->leftJoin('hasil_lab as hl', 'old.id', '=', 'hl.order_lab_detail_id')

                // ✅ KEY FIX: Filter by kunjungan_id from order_lab table
                ->where('ol.kunjungan_id', $kunjunganId)
                ->where('ol.pasien_id', $pasien->id) // Security check
                ->whereNotNull('hl.id') // Only get results that have hasil_lab

                ->select(
                    'old.id as order_lab_detail_id',
                    'old.status_pemeriksaan',
                    'jpl.id as pemeriksaan_id',
                    'jpl.kode_pemeriksaan',
                    'jpl.nama_pemeriksaan',
                    'jpl.nilai_normal',
                    'jpl.harga_pemeriksaan_lab',
                    'sl.nama_satuan as satuan',
                    'hl.id as hasil_id',
                    'hl.nilai_hasil',
                    'hl.nilai_rujukan',
                    'hl.catatan as keterangan',
                    'hl.tanggal_pemeriksaan',
                    'hl.jam_pemeriksaan',
                    'ol.tanggal_order',
                    'ol.no_order_lab',
                    'ol.kunjungan_id' // Include for verification
                )
                ->orderByDesc('hl.tanggal_pemeriksaan')
                ->orderByDesc('hl.jam_pemeriksaan')
                ->get();

            // Format data for response
            $formattedData = $hasilLab->map(function ($item) {
                // Determine status based on nilai_hasil vs nilai_rujukan
                $status = 'normal';
                if ($item->nilai_hasil && $item->nilai_rujukan) {
                    $nilaiHasil = (float) $item->nilai_hasil;
                    $nilaiRujukan = (string) $item->nilai_rujukan;

                    if (strpos($nilaiRujukan, '-') !== false) {
                        $range = explode('-', $nilaiRujukan);
                        $min = (float) trim($range[0]);
                        $max = (float) trim($range[1]);

                        if ($nilaiHasil < $min) {
                            $status = 'rendah';
                        } elseif ($nilaiHasil > $max) {
                            $status = 'tinggi';
                        }
                    } elseif (strpos($nilaiRujukan, '<') !== false) {
                        $max = (float) str_replace('<', '', $nilaiRujukan);
                        if ($nilaiHasil >= $max) {
                            $status = 'tinggi';
                        }
                    } elseif (strpos($nilaiRujukan, '>') !== false) {
                        $min = (float) str_replace('>', '', $nilaiRujukan);
                        if ($nilaiHasil <= $min) {
                            $status = 'rendah';
                        }
                    }
                }

                return [
                    'order_lab_detail_id' => $item->order_lab_detail_id,
                    'status_pemeriksaan' => $item->status_pemeriksaan,
                    'no_order_lab' => $item->no_order_lab,
                    'tanggal_order' => $item->tanggal_order,
                    'kunjungan_id' => $item->kunjungan_id, // ✅ For debugging

                    'pemeriksaan' => [
                        'id' => $item->pemeriksaan_id,
                        'kode_pemeriksaan' => $item->kode_pemeriksaan,
                        'nama_pemeriksaan' => $item->nama_pemeriksaan,
                        'nilai_normal' => $item->nilai_normal,
                        'satuan' => $item->satuan,
                        'harga_pemeriksaan_lab' => (float) $item->harga_pemeriksaan_lab,
                    ],

                    'hasil' => [
                        'id' => $item->hasil_id,
                        'nilai_hasil' => $item->nilai_hasil,
                        'nilai_rujukan' => $item->nilai_rujukan ?? $item->nilai_normal,
                        'keterangan' => $item->keterangan,
                        'tanggal_pemeriksaan' => $item->tanggal_pemeriksaan,
                        'jam_pemeriksaan' => $item->jam_pemeriksaan,
                        'status' => $status,
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Hasil lab berhasil diambil',
                'data' => $formattedData,
                'total' => $formattedData->count(),
                'kunjungan_id' => $kunjunganId, // ✅ For debugging
            ], 200);

        } catch (\Throwable $e) {
            Log::error('ERROR getHasilLabByKunjungan: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'kunjungan_id' => $kunjunganId ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil hasil lab',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function markNotifikasiAsRead(Request $request, $notifId)
    {
        try {
            $user = $request->user();

            $affected = DB::table('notifications')
                ->where('id', $notifId)
                ->where('user_id', $user->id)
                ->whereNull('read_at') // biar gak spam update
                ->update([
                    'read_at' => now(),
                    'updated_at' => now(),
                ]);

            if ($affected === 0) {
                // bisa karena tidak ada / bukan milik user / sudah dibaca
                $exists = DB::table('notifications')
                    ->where('id', $notifId)
                    ->where('user_id', $user->id)
                    ->exists();

                return response()->json([
                    'success' => false,
                    'message' => $exists ? 'Notifikasi sudah dibaca.' : 'Notifikasi tidak ditemukan.',
                ], $exists ? 200 : 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi ditandai sudah dibaca',
            ]);

        } catch (\Exception $e) {
            Log::error('Error markNotifikasiAsRead: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal update notifikasi',
            ], 500);
        }
    }

    /**
     * Mark all notifications as read for current user
     */
    public function markAllNotifikasiAsRead(Request $request)
    {
        try {
            $user = $request->user();

            $affected = DB::table('notifications')
                ->where('user_id', $user->id)
                ->whereNull('read_at') // hanya yang belum dibaca
                ->update([
                    'read_at' => now(),
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi ditandai sudah dibaca',
                'affected' => $affected,
            ]);

        } catch (\Exception $e) {
            Log::error('Error markAllNotifikasiAsRead: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal update semua notifikasi',
            ], 500);
        }
    }

    public function testKirimNotifikasiSimple(Request $request)
    {
        try {
            $user = $request->user();

            DB::table('notifications')->insert([
                'user_id' => $user->id,
                'title' => 'TEST: Notifikasi Berhasil',
                'body' => 'Ini notifikasi test tanpa cek order lab.',
                'data' => json_encode([
                    'type' => 'hasil_lab',
                    'order_lab_id' => 123,
                ]),
                'sent_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi test berhasil dibuat.',
            ]);
        } catch (\Exception $e) {
            Log::error('testKirimNotifikasiSimple error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat notifikasi test.',
            ], 500);
        }
    }

    public function pasienListTestimoni(Request $request)
    {
        try {
            $query = \App\Models\Testimoni::query()->orderByDesc('created_at');

            // ✅ Optional filter: only_mine=1 untuk tampilkan punya pasien login saja
            if ($request->boolean('only_mine')) {
                $user = $request->user();
                if (! $user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User tidak terautentikasi.',
                    ], 401);
                }

                $pasien = \App\Models\Pasien::where('user_id', $user->id)->first();
                if (! $pasien) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data pasien tidak ditemukan untuk user ini.',
                    ], 404);
                }

                $query->where('pasien_id', $pasien->id);
            }

            $perPage = (int) $request->get('per_page', 10);
            $items = $query->paginate($perPage);

            // ✅ Rapikan URL foto
            $items->getCollection()->transform(function ($t) {
                if ($t->foto) {
                    $t->foto_url = $this->toPublicUrl($t->foto);
                } else {
                    $t->foto_url = null;
                }

                return $t;
            });

            return response()->json([
                'success' => true,
                'message' => 'List testimoni berhasil diambil.',
                'data' => $items,
            ]);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error getting list testimoni: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil list testimoni.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/pasien/testimoni/{id}
     * Detail testimoni
     */
    public function pasienDetailTestimoni(Request $request, $id)
    {
        try {
            $testimoni = Testimoni::find($id);
            if (! $testimoni) {
                return response()->json([
                    'success' => false,
                    'message' => 'Testimoni tidak ditemukan.',
                ], 404);
            }

            $testimoni->foto_url = $testimoni->foto ? $this->toPublicUrl($testimoni->foto) : null;

            return response()->json([
                'success' => true,
                'message' => 'Detail testimoni berhasil diambil.',
                'data' => $testimoni,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail testimoni.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/pasien/testimoni
     * Pasien buat testimoni
     *
     * body:
     * - nama_testimoni (optional, kalau kosong bisa auto dari pasien.nama_pasien)
     * - umur (optional)
     * - pekerjaan (optional)
     * - isi_testimoni (required)
     * - link_video (optional)
     * - foto (optional file)
     */
    public function pasienCreateTestimoni(Request $request)
    {
        try {
            // ✅ PERBAIKAN 1: Ambil user yang login
            $user = $request->user();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi.',
                ], 401);
            }

            // ✅ PERBAIKAN 2: Ambil data pasien dengan lebih aman
            $pasien = \App\Models\Pasien::where('user_id', $user->id)->first();

            if (! $pasien) {
                \Illuminate\Support\Facades\Log::error('Pasien tidak ditemukan untuk user_id: '.$user->id);

                return response()->json([
                    'success' => false,
                    'message' => 'Data pasien tidak ditemukan untuk user ini.',
                    'debug' => [
                        'user_id' => $user->id,
                        'username' => $user->username,
                    ],
                ], 404);
            }

            // ✅ PERBAIKAN 3: Log untuk debugging
            \Illuminate\Support\Facades\Log::info('Creating testimoni:', [
                'user_id' => $user->id,
                'pasien_id' => $pasien->id,
                'nama_pasien' => $pasien->nama_pasien,
                'request_data' => $request->all(),
            ]);

            // ✅ Validasi input
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'nama_testimoni' => 'nullable|string|max:255',
                'umur' => 'nullable|string|max:50',
                'pekerjaan' => 'nullable|string|max:255',
                'isi_testimoni' => 'required|string',
                'link_video' => 'nullable|string|max:255',
                'foto' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            ], [
                'isi_testimoni.required' => 'Isi testimoni wajib diisi.',
                'foto.mimes' => 'Format foto harus jpg, jpeg, png, atau webp.',
                'foto.max' => 'Ukuran foto maksimal 2MB.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // ✅ Handle upload foto
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                try {
                    // Simpan ke storage/app/public/testimoni
                    $fotoPath = $request->file('foto')->store('testimoni', 'public');

                    \Illuminate\Support\Facades\Log::info('Foto uploaded successfully', [
                        'path' => $fotoPath,
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error uploading foto: '.$e->getMessage());

                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal mengupload foto: '.$e->getMessage(),
                    ], 500);
                }
            }

            // ✅ PERBAIKAN 4: Tentukan nama_testimoni (fallback ke nama_pasien)
            $namaTestimoni = $request->input('nama_testimoni');
            if (empty($namaTestimoni)) {
                $namaTestimoni = $pasien->nama_pasien ?: 'Pasien';
            }

            // ✅ Create testimoni
            $testimoni = \App\Models\Testimoni::create([
                'pasien_id' => $pasien->id,
                'nama_testimoni' => $namaTestimoni,
                'umur' => $request->input('umur'),
                'pekerjaan' => $request->input('pekerjaan'),
                'isi_testimoni' => $request->input('isi_testimoni'),
                'foto' => $fotoPath,
                'link_video' => $request->input('link_video'),
            ]);

            // ✅ Generate foto_url
            $testimoni->foto_url = $testimoni->foto ? $this->toPublicUrl($testimoni->foto) : null;

            \Illuminate\Support\Facades\Log::info('Testimoni created successfully', [
                'testimoni_id' => $testimoni->id,
                'pasien_id' => $pasien->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Testimoni berhasil dibuat.',
                'data' => $testimoni,
            ], 201);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error creating testimoni: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat testimoni.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/pasien/testimoni/{id}
     * Update testimoni milik pasien login
     */
    public function pasienUpdateTestimoni(Request $request, $id)
    {
        try {
            $pasien = $this->getAuthPasien($request);
            if (! $pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pasien tidak ditemukan untuk user ini.',
                ], 404);
            }

            $testimoni = Testimoni::where('id', $id)
                ->where('pasien_id', $pasien->id)
                ->first();

            if (! $testimoni) {
                return response()->json([
                    'success' => false,
                    'message' => 'Testimoni tidak ditemukan / bukan milik kamu.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nama_testimoni' => 'nullable|string|max:255',
                'umur' => 'nullable|string|max:50',
                'pekerjaan' => 'nullable|string|max:255',
                'isi_testimoni' => 'nullable|string',
                'link_video' => 'nullable|string|max:255',
                'foto' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
                'hapus_foto' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // hapus foto jika diminta
            if ($request->boolean('hapus_foto') && $testimoni->foto) {
                Storage::disk('public')->delete($testimoni->foto);
                $testimoni->foto = null;
            }

            // replace foto jika upload baru
            if ($request->hasFile('foto')) {
                if ($testimoni->foto) {
                    Storage::disk('public')->delete($testimoni->foto);
                }
                $testimoni->foto = $request->file('foto')->store('testimoni', 'public');
            }

            // update field lain
            foreach (['nama_testimoni', 'umur', 'pekerjaan', 'isi_testimoni', 'link_video'] as $f) {
                if ($request->has($f)) {
                    $testimoni->$f = $request->input($f);
                }
            }

            $testimoni->save();

            $testimoni->foto_url = $testimoni->foto ? $this->toPublicUrl($testimoni->foto) : null;

            return response()->json([
                'success' => true,
                'message' => 'Testimoni berhasil diupdate.',
                'data' => $testimoni,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update testimoni.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/pasien/testimoni/{id}
     * Hapus testimoni milik pasien login
     */
    public function pasienDeleteTestimoni(Request $request, $id)
    {
        try {
            $pasien = $this->getAuthPasien($request);
            if (! $pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pasien tidak ditemukan untuk user ini.',
                ], 404);
            }

            $testimoni = Testimoni::where('id', $id)
                ->where('pasien_id', $pasien->id)
                ->first();

            if (! $testimoni) {
                return response()->json([
                    'success' => false,
                    'message' => 'Testimoni tidak ditemukan / bukan milik kamu.',
                ], 404);
            }

            if ($testimoni->foto) {
                Storage::disk('public')->delete($testimoni->foto);
            }

            $testimoni->delete();

            return response()->json([
                'success' => true,
                'message' => 'Testimoni berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal hapus testimoni.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * helper buat bikin URL foto yang konsisten
     * kalau kamu pakai storage:link, maka /storage/...
     */
    private function toPublicUrl(string $path): string
    {
        // Contoh hasil: https://domain.com/storage/testimoni/xxx.jpg
        return asset('storage/'.ltrim($path, '/'));
    }

    /**
     * ✅ LIST ORDER LAB - FIX FINAL
     */
    public function pasienListOrderLab(Request $request)
    {
        $pasienId = $this->currentPasienId($request);
        if (! $pasienId) {
            return response()->json([
                'success' => false,
                'message' => 'Pasien tidak ditemukan untuk user ini.',
            ], 404);
        }

        try {
            $q = DB::table('order_lab as o')
                ->where('o.pasien_id', $pasienId)
                ->leftJoin('dokter as d', 'd.id', '=', 'o.dokter_id')
                ->select(
                    'o.id',
                    'o.no_order_lab',
                    'o.dokter_id',
                    'o.pasien_id',
                    'o.tanggal_order',
                    'o.tanggal_pemeriksaan',
                    'o.jam_pemeriksaan',
                    'o.status',
                    // ✅ FIX: hanya gunakan kolom yang ADA di tabel dokter
                    DB::raw("COALESCE(d.nama_dokter, '-') as dokter_nama"),
                    'o.created_at',
                    'o.updated_at'
                )
                ->orderByDesc('o.id');

            // ✅ Optional filters
            if ($request->filled('status')) {
                $q->where('o.status', $request->status);
            }
            if ($request->filled('from')) {
                $q->whereDate('o.tanggal_pemeriksaan', '>=', $request->from);
            }
            if ($request->filled('to')) {
                $q->whereDate('o.tanggal_pemeriksaan', '<=', $request->to);
            }

            $rows = $q->paginate((int) ($request->get('per_page', 15)));

            return response()->json([
                'success' => true,
                'message' => 'List order lab pasien',
                'data' => $rows,
            ]);

        } catch (\Exception $e) {
            Log::error('Error pasienListOrderLab: '.$e->getMessage(), [
                'pasien_id' => $pasienId,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data order lab',
                'error' => config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan server',
            ], 500);
        }
    }

    /**
     * ✅ DETAIL ORDER LAB - FIX FINAL
     */
    public function pasienDetailOrderLab(Request $request, $orderLabId)
    {
        $user = $request->user();

        $pasien = \App\Models\Pasien::where('user_id', $user->id)->first();
        if (! $pasien) {
            return response()->json([
                'success' => false,
                'message' => 'Pasien tidak ditemukan untuk user ini.',
            ], 404);
        }

        $order = OrderLab::with([
            'pasien:id,nama_pasien,no_emr,jenis_kelamin,tanggal_lahir',
            'dokter:id,nama_dokter',
            'orderLabDetail.jenisPemeriksaanLab:id,kode_pemeriksaan,nama_pemeriksaan,nilai_normal,satuan_lab_id',
            'orderLabDetail.jenisPemeriksaanLab.satuanLab:id,nama_satuan',
        ])
            ->where('id', $orderLabId)
            ->where('pasien_id', $pasien->id)
            ->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order lab tidak ditemukan / bukan milik pasien ini.',
            ], 404);
        }

        $detailIds = $order->orderLabDetail->pluck('id')->toArray();
        $hasilMap = HasilLab::whereIn('order_lab_detail_id', $detailIds)
            ->get()
            ->keyBy('order_lab_detail_id');

        $details = $order->orderLabDetail->map(function ($d) use ($hasilMap) {
            $jp = $d->jenisPemeriksaanLab;
            $hasil = $hasilMap->get($d->id);

            return [
                'order_lab_detail_id' => $d->id,
                'status_pemeriksaan' => $d->status_pemeriksaan,
                'pemeriksaan' => $jp ? [
                    'id' => $jp->id,
                    'kode_pemeriksaan' => $jp->kode_pemeriksaan,
                    'nama_pemeriksaan' => $jp->nama_pemeriksaan,
                    'nilai_normal' => $jp->nilai_normal,
                    'satuan' => $jp->satuanLab?->nama_satuan,
                ] : null,
                'hasil' => $hasil ? [
                    'id' => $hasil->id,
                    'nilai_hasil' => $hasil->nilai_hasil,
                    'nilai_rujukan' => $hasil->nilai_rujukan,
                    'keterangan' => $hasil->catatan,
                    'tanggal_pemeriksaan' => $hasil->tanggal_pemeriksaan,
                    'jam_pemeriksaan' => $hasil->jam_pemeriksaan,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'no_order_lab' => $order->no_order_lab,
                'tanggal_order' => $order->tanggal_order,
                'tanggal_pemeriksaan' => $order->tanggal_pemeriksaan,
                'jam_pemeriksaan' => $order->jam_pemeriksaan,
                'status' => $order->status,
                'dokter' => $order->dokter ? [
                    'id' => $order->dokter->id,
                    'nama_dokter' => $order->dokter->nama_dokter,
                ] : null,
                'pasien' => $order->pasien ? [
                    'id' => $order->pasien->id,
                    'nama_pasien' => $order->pasien->nama_pasien,
                    'no_emr' => $order->pasien->no_emr,
                    'jenis_kelamin' => $order->pasien->jenis_kelamin,
                    'tanggal_lahir' => $order->pasien->tanggal_lahir,
                ] : null,
                'detail' => $details,
            ],
        ]);
    }

    public function pasienMarkNotifAsRead(Request $request, $notificationId)
    {
        try {
            $user = $request->user();

            $updated = DB::table('notifications')
                ->where('id', $notificationId)
                ->where('user_id', $user->id)
                ->whereNull('read_at')
                ->update([
                    'read_at' => now(),
                    'updated_at' => now(),
                ]);

            if (! $updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notifikasi tidak ditemukan atau sudah dibaca',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi ditandai sudah dibaca',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai notifikasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Pasien mark all notif as read
     * PATCH /api/pasien/notifikasi/read-all
     */
    public function pasienMarkAllNotifAsRead(Request $request)
    {
        try {
            $user = $request->user();

            DB::table('notifications')
                ->where('user_id', $user->id)
                ->whereNull('read_at')
                ->update([
                    'read_at' => now(),
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi ditandai sudah dibaca',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai notifikasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET LAYANAN PUBLIK (untuk homepage/landing - tanpa auth)
     * Endpoint: GET /api/layanan-publik
     * Query params (optional):
     * - limit: jumlah data (default 10)
     * - kategori: filter by kategori
     * - search: cari by nama_layanan
     */
    public function getLayananPublik(Request $request)
    {
        try {
            $limit = (int) $request->input('limit', 10);
            $kategori = $request->input('kategori');
            $search = $request->input('search');

            $query = DB::table('layanan')
                ->whereNull('deleted_at');

            // Filter by kategori jika ada
            if ($kategori) {
                $query->where('kategori', $kategori);
            }

            // Search by nama layanan
            if ($search) {
                $query->where('nama_layanan', 'LIKE', "%{$search}%");
            }

            $layanan = $query
                ->select([
                    'id',
                    'kode_layanan',
                    'nama_layanan',
                    'deskripsi',
                    'kategori',
                    'tipe_layanan',
                    'jumlah_visit',
                    'durasi_menit',
                    'harga_fix',
                    'gambar',
                    'lokasi_tersedia',
                    'syarat_perawat',
                    'created_at',
                ])
                ->orderBy('created_at', 'DESC')
                ->limit($limit)
                ->get();

            // Format response
            $layanan = $layanan->map(function ($item) {
                return [
                    'id' => (int) $item->id,
                    'kode_layanan' => $item->kode_layanan,
                    'nama_layanan' => $item->nama_layanan,
                    'deskripsi' => $item->deskripsi,
                    'kategori' => $item->kategori,
                    'tipe_layanan' => $item->tipe_layanan,
                    'jumlah_visit' => $item->jumlah_visit,
                    'durasi_menit' => $item->durasi_menit,

                    // Format harga
                    'harga_fix' => (float) $item->harga_fix,
                    'harga_formatted' => 'Rp '.number_format($item->harga_fix, 0, ',', '.'),

                    // Gambar URL (sesuaikan dengan storage Anda)
                    'gambar' => $item->gambar,
                    'gambar_url' => $item->gambar
                        ? asset('storage/'.$item->gambar)
                        : null,

                    'lokasi_tersedia' => $item->lokasi_tersedia,
                    'syarat_perawat' => $item->syarat_perawat,
                    'created_at' => $item->created_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Data layanan berhasil diambil',
                'data' => $layanan,
                'total' => $layanan->count(),
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getLayananPublik: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data layanan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET DETAIL LAYANAN PUBLIK (tanpa auth)
     * Endpoint: GET /api/layanan-publik/{id}
     */
    public function getDetailLayananPublik($id)
    {
        try {
            $layanan = DB::table('layanan')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->first();

            if (! $layanan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Layanan tidak ditemukan',
                ], 404);
            }

            $data = [
                'id' => (int) $layanan->id,
                'kode_layanan' => $layanan->kode_layanan,
                'nama_layanan' => $layanan->nama_layanan,
                'deskripsi' => $layanan->deskripsi,
                'kategori' => $layanan->kategori,
                'tipe_layanan' => $layanan->tipe_layanan,
                'jumlah_visit' => $layanan->jumlah_visit,
                'durasi_menit' => $layanan->durasi_menit,
                'harga_fix' => (float) $layanan->harga_fix,
                'harga_formatted' => 'Rp '.number_format($layanan->harga_fix, 0, ',', '.'),
                'gambar' => $layanan->gambar,
                'gambar_url' => $layanan->gambar
                    ? asset('storage/'.$layanan->gambar)
                    : null,
                'lokasi_tersedia' => $layanan->lokasi_tersedia,
                'syarat_perawat' => $layanan->syarat_perawat,
                'aktif' => (bool) $layanan->aktif,
                'created_at' => $layanan->created_at,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Detail layanan berhasil diambil',
                'data' => $data,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getDetailLayananPublik: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail layanan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET KATEGORI LAYANAN (untuk filter)
     * Endpoint: GET /api/kategori-layanan-publik
     */
    public function getKategoriLayananPublik()
    {
        try {
            // Ambil kategori unik dari tabel layanan
            $kategori = DB::table('layanan')
                ->select('kategori')
                ->whereNotNull('kategori')
                ->whereNull('deleted_at')
                ->distinct()
                ->orderBy('kategori')
                ->get()
                ->pluck('kategori');

            return response()->json([
                'success' => true,
                'message' => 'Data kategori layanan berhasil diambil',
                'data' => $kategori,
                'total' => $kategori->count(),
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getKategoriLayananPublik: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil kategori layanan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getProfile(Request $request)
    {
        $user = $request->user();
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

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $pasien->id,
                'user_id' => $pasien->user_id,
                'nama_pasien' => $pasien->nama_pasien, // ✅ FIELD UTAMA
                'no_emr' => $pasien->no_emr,
                'nama_pasien' => $pasien->nama_pasien,
                'nik' => $pasien->nik,
                'no_bpjs' => $pasien->no_bpjs,
                'jenis_kelamin' => $pasien->jenis_kelamin,
                'tanggal_lahir' => $pasien->tanggal_lahir,

                // Kontak & alamat
                'alamat' => $pasien->alamat,
                'no_hp_pasien' => $pasien->no_hp_pasien,

                // Info tambahan
                'golongan_darah' => $pasien->golongan_darah,
                'status_perkawinan' => $pasien->status_perkawinan,
                'pekerjaan' => $pasien->pekerjaan,
                'alergi' => $pasien->alergi,

                // Penanggung jawab
                'nama_penanggung_jawab' => $pasien->nama_penanggung_jawab,
                'no_hp_penanggung_jawab' => $pasien->no_hp_penanggung_jawab,

                // Foto & kode
                'foto_pasien' => $pasien->foto_pasien,
                'qr_code_pasien' => $pasien->qr_code_pasien,
                'barcode_pasien' => $pasien->barcode_pasien,

                'created_at' => $pasien->created_at,
                'updated_at' => $pasien->updated_at,
            ],
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user(); // konsisten dengan getProfile()
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

            // ✅ AUTO-GENERATE NO_EMR JIKA BELUM ADA
            if (empty($pasien->no_emr)) {
                $lastPasien = Pasien::where('no_emr', 'LIKE', 'RM-%')
                    ->orderBy('id', 'desc')
                    ->first();

                $lastNumber = 0;
                if ($lastPasien && preg_match('/RM-(\d+)/', $lastPasien->no_emr, $matches)) {
                    $lastNumber = (int) $matches[1];
                }

                $nextNumber = $lastNumber + 1;
                $pasien->no_emr = 'RM-'.str_pad($nextNumber, 8, '0', STR_PAD_LEFT);

                Log::info('Auto-generated EMR for existing mobile user:', [
                    'pasien_id' => $pasien->id,
                    'no_emr' => $pasien->no_emr,
                ]);
            }

            // ✅ VALIDASI SESUAI MIGRATION
            $validated = $request->validate([
                'nama_pasien' => 'required|string|max:255',
                'alamat' => 'nullable|string|max:255',
                'tanggal_lahir' => 'nullable|date',
                'jenis_kelamin' => 'nullable|string|in:Laki-laki,Perempuan',

                'no_hp_pasien' => 'nullable|string|max:20',
                'nik' => 'nullable|string|max:20',
                'no_bpjs' => 'nullable|string|max:50',
                'golongan_darah' => 'nullable|string|max:3',
                'status_perkawinan' => 'nullable|string|max:20',
                'pekerjaan' => 'nullable|string|max:100',
                'nama_penanggung_jawab' => 'nullable|string|max:255',
                'no_hp_penanggung_jawab' => 'nullable|string|max:20',
                'alergi' => 'nullable|string',
                'barcode_pasien' => 'nullable|string|max:255',

                'foto_pasien' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            // ✅ handle foto
            $pathFotoPasien = $pasien->foto_pasien;

            if ($request->hasFile('foto_pasien')) {
                if ($pasien->foto_pasien && Storage::disk('public')->exists($pasien->foto_pasien)) {
                    Storage::disk('public')->delete($pasien->foto_pasien);
                }

                $fileFoto = $request->file('foto_pasien');
                $namaFoto = 'pasien_'.$user->id.'_'.time().'.'.$fileFoto->getClientOriginalExtension();
                $pathFotoPasien = $fileFoto->storeAs('Foto-Pasien', $namaFoto, 'public');
            }

            // ✅ data utama
            $updateData = [
                'nama_pasien' => $validated['nama_pasien'],
                'foto_pasien' => $pathFotoPasien,
                'no_emr' => $pasien->no_emr,
            ];

            // ✅ field opsional – hanya kalau dikirim dari Flutter
            $optionalFields = [
                'alamat',
                'tanggal_lahir',
                'jenis_kelamin',
                'no_hp_pasien',
                'nik',
                'no_bpjs',
                'golongan_darah',
                'status_perkawinan',
                'pekerjaan',
                'nama_penanggung_jawab',
                'no_hp_penanggung_jawab',
                'alergi',
                'barcode_pasien',
            ];

            foreach ($optionalFields as $field) {
                if ($request->has($field)) {
                    if ($field === 'tanggal_lahir' && $request->input('tanggal_lahir')) {
                        $updateData['tanggal_lahir'] = Carbon::parse($request->input('tanggal_lahir'))
                            ->timezone(config('app.timezone', 'Asia/Jakarta'))
                            ->format('Y-m-d');
                    } else {
                        $updateData[$field] = $request->input($field);
                    }
                }
            }

            $pasien->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => $pasien->fresh(),
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error updating profile: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getLatestVitalPasien(Request $request)
    {
        $user = $request->user();

        // Pastikan user punya data pasien
        if (! $user->pasien) {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini tidak memiliki data pasien.',
            ], 403);
        }

        $pasienId = $user->pasien->id;

        /**
         * ✅ Ambil EMR terbaru untuk pasien ini YANG SUDAH DIBAYAR
         */
        $latestEmr = Emr::query()
            ->where(function ($q) use ($pasienId) {
                $q->where('pasien_id', $pasienId)
                    ->orWhereHas('kunjungan', function ($k) use ($pasienId) {
                        $k->where('pasien_id', $pasienId);
                    });
            })
            // ✅ TAMBAHKAN: Hanya EMR yang pembayarannya sudah lunas
            ->whereHas('pembayaran', function ($query) {
                $query->where('status', 'Sudah Bayar');
            })
            ->orderByDesc('created_at')
            ->first();

        if (! $latestEmr) {
            return response()->json([
                'success' => true,
                'message' => 'Belum ada data vital sign yang sudah dibayar.',
                'data' => null,
            ]);
        }

        // ✅ Bungkus data agar rapi dan lengkap
        $data = [
            'emr_id' => $latestEmr->id,
            'kunjungan_id' => $latestEmr->kunjungan_id,
            'resep_id' => $latestEmr->resep_id,

            // ======== Identitas snapshot ========
            'pasien_id' => $latestEmr->pasien_id,
            'dokter_id' => $latestEmr->dokter_id,
            'poli_id' => $latestEmr->poli_id,
            'perawat_id' => $latestEmr->perawat_id,

            // ======== Catatan klinis ========
            'keluhan_utama' => $latestEmr->keluhan_utama,
            'riwayat_penyakit_dahulu' => $latestEmr->riwayat_penyakit_dahulu,
            'riwayat_penyakit_keluarga' => $latestEmr->riwayat_penyakit_keluarga,
            'diagnosis' => $latestEmr->diagnosis,

            // ======== Vital sign (lama) ========
            'tekanan_darah' => $latestEmr->tekanan_darah,
            'suhu_tubuh' => $latestEmr->suhu_tubuh,
            'nadi' => $latestEmr->nadi,
            'pernapasan' => $latestEmr->pernapasan,
            'saturasi_oksigen' => $latestEmr->saturasi_oksigen,

            // ======== Vital sign (baru) ========
            'tinggi_badan' => $latestEmr->tinggi_badan,
            'berat_badan' => $latestEmr->berat_badan,
            'imt' => $latestEmr->imt,

            // ======== Tanggal & waktu ========
            'tanggal' => optional($latestEmr->created_at)->toDateString(),
            'waktu' => optional($latestEmr->created_at)->format('H:i'),
            'created_at' => $latestEmr->created_at,
            'updated_at' => $latestEmr->updated_at,

            // ✅ TAMBAHAN: Info pembayaran
            'pembayaran_status' => 'Sudah Bayar',
            'tanggal_pembayaran' => optional($latestEmr->pembayaran)->tanggal_pembayaran,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data vital sign terbaru berhasil diambil.',
            'data' => $data,
        ]);
    }

    public function getVitalHistoryPasien(Request $request)
    {
        $user = $request->user();

        if (! $user->pasien) {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini tidak memiliki data pasien.',
            ], 403);
        }

        $pasienId = $user->pasien->id;

        // ✅ TAMBAHKAN: Filter hanya yang sudah dibayar
        $emrs = Emr::query()
            ->where(function ($q) use ($pasienId) {
                $q->where('pasien_id', $pasienId)
                    ->orWhereHas('kunjungan', function ($k) use ($pasienId) {
                        $k->where('pasien_id', $pasienId);
                    });
            })
            // ✅ Hanya yang pembayarannya sudah lunas
            ->whereHas('pembayaran', function ($query) {
                $query->where('status', 'Sudah Bayar');
            })
            ->orderByDesc('created_at')
            ->take(10)
            ->get()
            ->map(function (Emr $emr) {
                return [
                    'emr_id' => $emr->id,
                    'kunjungan_id' => $emr->kunjungan_id,
                    'resep_id' => $emr->resep_id,

                    // snapshot ids
                    'pasien_id' => $emr->pasien_id,
                    'dokter_id' => $emr->dokter_id,
                    'poli_id' => $emr->poli_id,
                    'perawat_id' => $emr->perawat_id,

                    'tanggal' => optional($emr->created_at)->toDateString(),
                    'waktu' => optional($emr->created_at)->format('H:i'),

                    // vital sign lama
                    'tekanan_darah' => $emr->tekanan_darah,
                    'suhu_tubuh' => $emr->suhu_tubuh,
                    'nadi' => $emr->nadi,
                    'pernapasan' => $emr->pernapasan,
                    'saturasi_oksigen' => $emr->saturasi_oksigen,

                    // vital sign baru
                    'tinggi_badan' => $emr->tinggi_badan,
                    'berat_badan' => $emr->berat_badan,
                    'imt' => $emr->imt,

                    // opsional: diagnosis
                    'diagnosis' => $emr->diagnosis,

                    // ✅ TAMBAHAN: Info pembayaran
                    'pembayaran_status' => 'Sudah Bayar',
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Riwayat vital sign berhasil diambil.',
            'data' => $emrs,
        ]);
    }

    /**
     * Check if patient can view vital signs (payment must be completed)
     * GET /api/pasien/can-view-vital-signs
     */
    public function canViewVitalSigns(Request $request)
    {
        try {
            $user = $request->user();

            if (! $user->pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun ini tidak memiliki data pasien.',
                ], 403);
            }

            $pasienId = $user->pasien->id;

            // Cek apakah ada EMR yang sudah dibayar
            $hasCompletedPayment = Emr::query()
                ->where(function ($q) use ($pasienId) {
                    $q->where('pasien_id', $pasienId)
                        ->orWhereHas('kunjungan', function ($k) use ($pasienId) {
                            $k->where('pasien_id', $pasienId);
                        });
                })
                ->whereHas('pembayaran', function ($query) {
                    $query->where('status', 'Sudah Bayar');
                })
                ->exists();

            // Cek apakah ada pembayaran yang pending
            $hasPendingPayment = Emr::query()
                ->where(function ($q) use ($pasienId) {
                    $q->where('pasien_id', $pasienId)
                        ->orWhereHas('kunjungan', function ($k) use ($pasienId) {
                            $k->where('pasien_id', $pasienId);
                        });
                })
                ->whereHas('pembayaran', function ($query) {
                    $query->where('status', 'Belum Bayar');
                })
                ->exists();

            return response()->json([
                'success' => true,
                'data' => [
                    'can_view' => $hasCompletedPayment,
                    'has_pending_payment' => $hasPendingPayment,
                    'message' => $hasCompletedPayment
                        ? 'Anda dapat melihat data vital sign'
                        : ($hasPendingPayment
                            ? 'Selesaikan pembayaran untuk melihat data vital sign'
                            : 'Belum ada data vital sign tersedia'),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('ERROR canViewVitalSigns: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengecek status',
            ], 500);
        }
    }

    public function getRiwayatDiagnosisPasien($pasienId)
    {
        try {
            Log::info('Getting riwayat diagnosis for pasien_id: '.$pasienId);

            $pasien = Pasien::find($pasienId);
            if (! $pasien) {
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
                $riwayatFormatted = 'Tidak ada riwayat penyakit sebelumnya';
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
            Log::error('Error getting riwayat diagnosis: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat diagnosis: '.$e->getMessage(),
            ], 500);
        }
    }

    public function orderLayananPasienMobile(Request $request)
    {
        set_time_limit(15);
        $startTime = microtime(true);

        try {
            Log::info('🔥 orderLayananPasienMobile START', [
                'payload' => $request->all(),
                'time' => now()->toDateTimeString(),
            ]);

            // =========================
            // 1) AUTH + PASIEN
            // =========================
            $user = $request->user();
            if (! $user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $pasien = Pasien::where('user_id', $user->id)->first();
            if (! $pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun ini tidak memiliki data pasien',
                ], 403);
            }

            // =========================
            // 2) VALIDASI INPUT
            // =========================
            $validator = Validator::make($request->all(), [
                'poli_id' => 'required|exists:poli,id',
                'dokter_id' => 'required|exists:dokter,id',
                'jadwal_dokter_id' => 'required|exists:jadwal_dokter,id',
                'tanggal_kunjungan' => 'required|date|after_or_equal:today',
                'keluhan_awal' => 'required|string|max:500',
                'items' => 'required|array|min:1',
                'items.*.layanan_id' => 'required|exists:layanan,id',
                'items.*.jumlah' => 'required|integer|min:1|max:10',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed', ['errors' => $validator->errors()]);

                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $poliId = (int) $request->poli_id;
            $dokterId = (int) $request->dokter_id;
            $jadwalId = (int) $request->jadwal_dokter_id;

            // =========================
            // 3) VALIDASI DOKTER
            // =========================
            $dokter = Dokter::query()->find($dokterId);
            if (! $dokter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokter tidak ditemukan',
                ], 422);
            }

            $dokterPunyaPoli = DB::table('dokter_poli')
                ->where('dokter_id', $dokterId)
                ->where('poli_id', $poliId)
                ->exists();

            if (! $dokterPunyaPoli) {
                $dokterPunyaPoli = isset($dokter->poli_id) && (int) $dokter->poli_id === $poliId;
            }

            if (! $dokterPunyaPoli) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokter tidak terdaftar pada poli yang dipilih',
                ], 422);
            }

            // =========================
            // 4) VALIDASI JADWAL
            // =========================
            $tanggal = Carbon::parse($request->tanggal_kunjungan);

            $hariMap = [
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu',
                'Sunday' => 'Minggu',
            ];
            $hari = $hariMap[$tanggal->format('l')] ?? $tanggal->format('l');

            $jadwal = JadwalDokter::query()
                ->where('id', $jadwalId)
                ->where('dokter_id', $dokterId)
                ->where('poli_id', $poliId)
                ->first();

            if (! $jadwal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal tidak valid',
                ], 422);
            }

            if (mb_strtolower(trim((string) $jadwal->hari)) !== mb_strtolower($hari)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tanggal tidak sesuai jadwal',
                ], 422);
            }

            // =========================
            // 5) MERGE ITEMS
            // =========================
            $merged = [];
            foreach ($request->items as $it) {
                $lid = (int) $it['layanan_id'];
                $qty = (int) $it['jumlah'];
                $merged[$lid] = ($merged[$lid] ?? 0) + $qty;
            }
            $layananIds = array_keys($merged);

            // =========================
            // 6) VALIDASI LAYANAN (POLI)
            // =========================
            $layananRows = Layanan::query()
                ->whereIn('id', $layananIds)
                ->get(['id', 'is_global'])
                ->keyBy('id');

            $restrictedIds = [];

            foreach ($layananIds as $lid) {
                $row = $layananRows->get($lid);
                if (! $row) {
                    continue;
                }
                if ((int) $row->is_global !== 1) {
                    $restrictedIds[] = (int) $lid;
                }
            }

            if (! empty($restrictedIds)) {
                $allowedRestrictedIds = DB::table('layanan_poli')
                    ->where('poli_id', $poliId)
                    ->whereIn('layanan_id', $restrictedIds)
                    ->pluck('layanan_id')
                    ->map(fn ($x) => (int) $x)
                    ->toArray();

                $invalidRestricted = array_values(array_diff($restrictedIds, $allowedRestrictedIds));

                if (! empty($invalidRestricted)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ada layanan yang tidak tersedia di poli ini',
                    ], 422);
                }
            }

            // =========================
            // 7) HITUNG TOTAL
            // =========================
            $layanans = Layanan::whereIn('id', $layananIds)->get()->keyBy('id');
            $subtotal = 0;
            $detailItems = [];

            foreach ($merged as $layananId => $qty) {
                $layanan = $layanans->get($layananId);
                if (! $layanan) {
                    throw new \Exception("Layanan {$layananId} tidak ditemukan");
                }

                $harga = (float) ($layanan->harga_setelah_diskon ?? $layanan->harga_sebelum_diskon ?? 0);
                $total = $harga * (int) $qty;
                $subtotal += $total;

                $detailItems[] = [
                    'layanan_id' => (int) $layanan->id,
                    'kategori_layanan_id' => (int) ($layanan->kategori_layanan_id ?? 0),
                    'qty' => (int) $qty,
                    'harga' => $harga,
                    'total' => $total,
                ];
            }

            Log::info('💰 Calculated totals', [
                'subtotal' => $subtotal,
                'items_count' => count($detailItems),
            ]);

            // ✅ TRANSACTION setelah semua validasi
            DB::beginTransaction();

            try {
                if ((microtime(true) - $startTime) > 10) {
                    throw new \Exception('Validation took too long');
                }

                // =========================
                // 8) CREATE ORDER_LAYANAN (SAFE: cek kolom dulu)
                // =========================
                $dataOrder = [
                    'pasien_id' => $pasien->id,
                    'subtotal' => $subtotal,
                    'total_bayar' => $subtotal,
                ];

                // isi hanya kalau kolomnya ada
                if (Schema::hasColumn('order_layanan', 'poli_id')) {
                    $dataOrder['poli_id'] = $poliId;
                }
                if (Schema::hasColumn('order_layanan', 'dokter_id')) {
                    $dataOrder['dokter_id'] = $dokterId;
                }
                if (Schema::hasColumn('order_layanan', 'jadwal_dokter_id')) {
                    $dataOrder['jadwal_dokter_id'] = $jadwal->id;
                }
                if (Schema::hasColumn('order_layanan', 'keluhan_utama')) {
                    $dataOrder['keluhan_utama'] = $request->keluhan_awal;
                }
                if (Schema::hasColumn('order_layanan', 'potongan_pesanan')) {
                    $dataOrder['potongan_pesanan'] = 0;
                }

                // status: pilih yang aman
                if (Schema::hasColumn('order_layanan', 'status_order_layanan')) {
                    $dataOrder['status_order_layanan'] = 'Belum Bayar'; // aman untuk enum umum
                }

                // kalau ada kode_transaksi / tanggal_order
                if (Schema::hasColumn('order_layanan', 'kode_transaksi')) {
                    $dataOrder['kode_transaksi'] = 'OL-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
                }
                if (Schema::hasColumn('order_layanan', 'tanggal_order')) {
                    $dataOrder['tanggal_order'] = now();
                }

                $order = OrderLayanan::create($dataOrder);

                Log::info('✅ Order created', ['order_id' => $order->id]);

                foreach ($detailItems as $d) {
                    OrderLayananDetail::create([
                        'order_layanan_id' => $order->id,
                        'layanan_id' => $d['layanan_id'],
                        'qty' => $d['qty'],
                        'harga_satuan' => $d['harga'],
                        'total_harga_item' => $d['total'],
                    ]);

                }

                // =========================
                // 9) KUNJUNGAN + NO ANTRIAN
                // =========================
                $maxRetries = 3;
                $retryCount = 0;
                $noAntrian = null;

                while ($retryCount < $maxRetries && $noAntrian === null) {
                    try {
                        $lastRow = Kunjungan::where('poli_id', $poliId)
                            ->whereDate('tanggal_kunjungan', $tanggal->toDateString())
                            ->orderByRaw('CAST(no_antrian AS UNSIGNED) DESC')
                            ->lockForUpdate()
                            ->first();

                        $lastNumber = $lastRow ? (int) $lastRow->no_antrian : 0;
                        $noAntrian = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

                    } catch (\Exception $e) {
                        $retryCount++;
                        if ($retryCount >= $maxRetries) {
                            throw new \Exception("Failed to acquire lock after {$maxRetries} attempts");
                        }
                        usleep(100000);
                    }
                }

                if ($noAntrian === null) {
                    throw new \Exception('Failed to generate queue number');
                }

                $kunjungan = Kunjungan::create([
                    'poli_id' => $poliId,
                    'pasien_id' => $pasien->id,
                    'tanggal_kunjungan' => $tanggal->toDateString(),
                    'no_antrian' => $noAntrian,
                    'keluhan_awal' => $request->keluhan_awal,
                    'status' => 'Pending',
                    'dokter_id' => $dokterId,
                    'jadwal_dokter_id' => $jadwalId,
                ]);

                foreach ($detailItems as $d) {
                    KunjunganLayanan::create([
                        'kunjungan_id' => $kunjungan->id,
                        'layanan_id' => $d['layanan_id'],
                        'jumlah' => $d['qty'],
                    ]);
                }

                // =========================
                // 10) PENJUALAN_LAYANAN
                // =========================
                $kodeTransaksi = 'TRX-'.strtoupper(uniqid());

                foreach ($detailItems as $d) {
                    PenjualanLayanan::create([
                        'pasien_id' => $pasien->id,
                        'layanan_id' => $d['layanan_id'],
                        'kategori_layanan_id' => $d['kategori_layanan_id'] ?: null,
                        'kunjungan_id' => $kunjungan->id,
                        'metode_pembayaran_id' => null,
                        'jumlah' => $d['qty'],
                        'total_tagihan' => $d['total'],
                        'sub_total' => $d['total'],
                        'kode_transaksi' => $kodeTransaksi,
                        'tanggal_transaksi' => now(),
                        'status' => 'Belum Bayar',
                        'diskon_tipe' => null,
                        'diskon_nilai' => 0,
                        'total_setelah_diskon' => $d['total'],
                    ]);
                }

                if ((microtime(true) - $startTime) > 12) {
                    throw new \Exception('Transaction timeout');
                }

                DB::commit();

                $duration = round((microtime(true) - $startTime) * 1000, 2);
                Log::info('✅ orderLayananPasienMobile SUCCESS', [
                    'order_id' => $order->id,
                    'duration_ms' => $duration,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Order berhasil dibuat',
                    'data' => [
                        'order_id' => $order->id,
                        'kunjungan_id' => $kunjungan->id,
                        'no_antrian' => $noAntrian,
                        'kode_transaksi' => $kodeTransaksi,
                        'total_bayar' => $subtotal,
                    ],
                ], 201);

            } catch (\Throwable $e) {
                DB::rollBack();

                Log::error('❌ Transaction error', [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan data: '.$e->getMessage(),
                ], 500);
            }

        } catch (\Throwable $e) {
            Log::error('❌ orderLayananPasienMobile ERROR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getRiwayatOrderLayanan(Request $request)
    {
        try {
            $user = $request->user();
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

            $orders = \App\Models\OrderLayanan::with([
                'poli:id,nama_poli',
                'dokter:id,nama_dokter,foto_dokter',
                'details.layanan:id,nama_layanan',
            ])
                ->where('pasien_id', $pasien->id)
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'tanggal_order' => $order->created_at->format('Y-m-d H:i:s'),
                        'keluhan_utama' => $order->keluhan_utama,
                        'status' => $order->status_order_layanan,
                        'subtotal' => (float) $order->subtotal,
                        'potongan_pesanan' => (float) $order->potongan_pesanan,
                        'total_bayar' => (float) $order->total_bayar,
                        'poli' => $order->poli ? [
                            'id' => $order->poli->id,
                            'nama_poli' => $order->poli->nama_poli,
                        ] : null,
                        'dokter' => $order->dokter ? [
                            'id' => $order->dokter->id,
                            'nama_dokter' => $order->dokter->nama_dokter,
                            'foto_dokter' => $order->dokter->foto_dokter,
                        ] : null,
                        'items_count' => $order->details->count(),
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Riwayat order layanan berhasil diambil',
                'data' => $orders,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('ERROR getRiwayatOrderLayanan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDetailOrderLayananPasien(Request $request, $orderId)
    {
        try {
            $user = $request->user();
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

            $order = \App\Models\OrderLayanan::with([
                'poli:id,nama_poli',
                'dokter:id,nama_dokter,foto_dokter',
                'jadwalDokter:id,hari,jam_awal,jam_selesai',
                'details.layanan:id,nama_layanan,harga_sebelum_diskon,harga_setelah_diskon,diskon',
            ])
                ->where('id', $orderId)
                ->where('pasien_id', $pasien->id)
                ->first();

            if (! $order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan',
                ], 404);
            }

            $data = [
                'id' => $order->id,
                'kode_transaksi' => $order->kode_transaksi,
                'tanggal_order' => $order->tanggal_order
                    ? \Carbon\Carbon::parse($order->tanggal_order)->format('Y-m-d H:i:s')
                    : $order->created_at->format('Y-m-d H:i:s'),
                'keluhan_utama' => $order->keluhan_utama,
                'status' => $order->status_order_layanan,

                // ✅ DISKON INFO
                'subtotal' => (float) $order->subtotal,
                'potongan_pesanan' => (float) $order->potongan_pesanan,
                'total_bayar' => (float) $order->total_bayar,

                // ✅ TAMBAHAN: Persentase diskon (jika ada)
                'persentase_diskon' => $order->subtotal > 0
                    ? round(($order->potongan_pesanan / $order->subtotal) * 100, 2)
                    : 0,

                'poli' => $order->poli ? [
                    'id' => $order->poli->id,
                    'nama_poli' => $order->poli->nama_poli,
                ] : null,
                'dokter' => $order->dokter ? [
                    'id' => $order->dokter->id,
                    'nama_dokter' => $order->dokter->nama_dokter,
                    'foto_dokter' => $order->dokter->foto_dokter,
                ] : null,
                'jadwal' => $order->jadwalDokter ? [
                    'id' => $order->jadwalDokter->id,
                    'hari' => $order->jadwalDokter->hari,
                    'jam_awal' => $order->jadwalDokter->jam_awal,
                    'jam_selesai' => $order->jadwalDokter->jam_selesai,
                ] : null,

                'items' => $order->details->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'qty' => $detail->qty,
                        'harga_satuan' => (float) $detail->harga_satuan,
                        'total_harga_item' => (float) $detail->total_harga_item,

                        'layanan' => $detail->layanan ? [
                            'id' => $detail->layanan->id,
                            'nama_layanan' => $detail->layanan->nama_layanan,
                            'harga_asli' => (float) ($detail->layanan->harga_sebelum_diskon ?? 0),
                            'harga_setelah_diskon' => (float) ($detail->layanan->harga_setelah_diskon ?? 0),
                            'diskon' => (float) ($detail->layanan->diskon ?? 0),
                        ] : null,
                    ];
                })->values(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Detail order berhasil diambil',
                'data' => $data,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('ERROR getDetailOrderLayananPasien', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $orderId ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function batalkanOrderLayanan(Request $request, $id)
    {
        try {
            // Debug: Cek user yang login
            $user = auth()->user();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi',
                ], 401);
            }

            // Debug: Cek pasien
            $pasien = $user->pasien;
            if (! $pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pasien tidak ditemukan',
                ], 404);
            }

            $pasienId = $pasien->id;

            // Debug: Cek order tanpa filter pasien_id dulu
            $orderExists = OrderLayanan::find($id);
            if (! $orderExists) {
                return response()->json([
                    'success' => false,
                    'message' => "Order dengan ID {$id} tidak ditemukan di database",
                ], 404);
            }

            // Debug: Cek apakah order milik pasien ini
            if ($orderExists->pasien_id != $pasienId) {
                return response()->json([
                    'success' => false,
                    'message' => "Order ini milik pasien ID {$orderExists->pasien_id}, bukan pasien ID {$pasienId}",
                ], 403);
            }

            // Update status
            $orderExists->update([
                'status_order_layanan' => 'Dibatalkan',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibatalkan',
                'data' => $orderExists,
            ]);

        } catch (\Exception $e) {
            Log::error('ERROR batalkanOrderLayanan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getLayananPasien(Request $request)
    {
        try {
            $q = trim((string) $request->query('q', ''));
            $kategoriId = $request->query('kategori_layanan_id');

            $query = Layanan::query()
                ->with([
                    'kategori:id,nama_kategori,deskripsi_kategori,status_kategori',
                    'polis:id,nama_poli',
                ])
                ->select(
                    'id',
                    'nama_layanan',
                    'harga_sebelum_diskon',
                    'harga_setelah_diskon',
                    'diskon',
                    'kategori_layanan_id',
                    'is_global' // ✅ TAMBAHAN
                );

            if (! empty($kategoriId)) {
                $query->where('kategori_layanan_id', $kategoriId);
            }

            if ($q !== '') {
                $query->where('nama_layanan', 'like', '%'.$q.'%');
            }

            $data = $query->orderBy('nama_layanan')->get()->map(function ($l) {
                // ✅ GLOBAL?
                $isGlobal = (int) ($l->is_global ?? 0) === 1;

                // ✅ kalau global -> poli_ids kosong (atau bisa semua poli kalau kamu mau)
                $poliIds = $isGlobal
                    ? []
                    : ($l->polis?->pluck('id')->map(fn ($x) => (int) $x)->values()->toArray() ?? []);

                // ✅ Hitung harga yang benar
                $hargaSetelahDiskon = (float) ($l->harga_setelah_diskon ?? 0);
                $hargaSebelumDiskon = (float) ($l->harga_sebelum_diskon ?? 0);
                $hargaFinal = $hargaSetelahDiskon > 0 ? $hargaSetelahDiskon : $hargaSebelumDiskon;

                return [
                    'id' => (int) $l->id,
                    'nama_layanan' => (string) $l->nama_layanan,

                    // ✅ FLAG GLOBAL
                    'is_global' => $isGlobal ? 1 : 0,
                    'is_all_poli' => $isGlobal, // opsional biar FE enak

                    // ✅ KIRIM SEMUA DATA HARGA
                    'harga_layanan' => $hargaFinal, // backward compatibility
                    'harga_sebelum_diskon' => $hargaSebelumDiskon,
                    'harga_setelah_diskon' => $hargaSetelahDiskon,
                    'diskon' => (float) ($l->diskon ?? 0),
                    'harga_asli' => $hargaSebelumDiskon,

                    'kategori_layanan_id' => $l->kategori_layanan_id ? (int) $l->kategori_layanan_id : null,

                    // ✅ POLI (kalau global -> kosong)
                    'poli_ids' => $poliIds,
                    'polis' => $isGlobal
                        ? []
                        : ($l->polis?->map(fn ($p) => [
                            'id' => (int) $p->id,
                            'nama_poli' => (string) $p->nama_poli,
                        ])->values()),

                    'kategori' => $l->kategori ? [
                        'id' => (int) $l->kategori->id,
                        'nama_kategori' => (string) $l->kategori->nama_kategori,
                        'deskripsi_kategori' => (string) $l->kategori->deskripsi_kategori,
                        'status_kategori' => (string) $l->kategori->status_kategori,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'List layanan',
                'data' => $data,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('getLayananPasien error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil layanan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDetailLayananPasien($id)
    {
        $l = Layanan::with([
            'kategori:id,nama_kategori,deskripsi_kategori,status_kategori',
            'polis:id,nama_poli',
        ])->find($id);

        if (! $l) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan tidak ditemukan',
            ], 404);
        }

        $isGlobal = (int) ($l->is_global ?? 0) === 1;

        return response()->json([
            'success' => true,
            'message' => 'Detail layanan',
            'data' => [
                'id' => (int) $l->id,
                'nama_layanan' => (string) $l->nama_layanan,

                // ✅ FLAG GLOBAL
                'is_global' => $isGlobal ? 1 : 0,
                'is_all_poli' => $isGlobal,

                // harga
                'harga_layanan' => (float) ($l->harga_setelah_diskon ?? $l->harga_layanan ?? 0),
                'harga_asli' => (float) ($l->harga_layanan ?? 0),

                'kategori_layanan_id' => $l->kategori_layanan_id ? (int) $l->kategori_layanan_id : null,

                // ✅ POLI (kalau global -> kosong)
                'poli_ids' => $isGlobal
                    ? []
                    : ($l->polis?->pluck('id')->map(fn ($x) => (int) $x)->values()->toArray() ?? []),

                'polis' => $isGlobal
                    ? []
                    : ($l->polis?->map(fn ($p) => [
                        'id' => (int) $p->id,
                        'nama_poli' => (string) $p->nama_poli,
                    ])->values()),

                'kategori' => $l->kategori ? [
                    'id' => (int) $l->kategori->id,
                    'nama_kategori' => (string) $l->kategori->nama_kategori,
                    'deskripsi_kategori' => (string) $l->kategori->deskripsi_kategori,
                    'status_kategori' => (string) $l->kategori->status_kategori,
                ] : null,
            ],
        ], 200);
    }

    public function getKategoriLayanan(Request $request)
    {
        try {
            $data = KategoriLayanan::query()
                ->orderBy('nama_kategori')
                ->get()
                ->map(function ($k) {
                    return [
                        'id' => $k->id,
                        'nama_kategori' => $k->nama_kategori,
                        'deskripsi_kategori' => $k->deskripsi_kategori,
                        'status_kategori' => $k->status_kategori,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'List kategori layanan',
                'data' => $data,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('getKategoriLayanan error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil kategori layanan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getRiwayatPembelianLayanan(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $pasien = Pasien::where('user_id', $user->id)->first();
        if (! $pasien) {
            return response()->json(['success' => false, 'message' => 'Data pasien tidak ditemukan'], 404);
        }

        // ✅ FIX: Tambahkan relasi dokter via kunjungan
        $rows = PenjualanLayanan::with([
            'layanan:id,nama_layanan',
            'kunjungan.dokter:id,nama_dokter,foto_dokter',  // ✅ TAMBAHKAN INI
            'kunjungan.poli:id,nama_poli',                    // ✅ BONUS: poli juga
        ])
            ->where('pasien_id', $pasien->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($p) {
                // ✅ Ambil dokter dari kunjungan
                $dokter = $p->kunjungan && $p->kunjungan->dokter
                    ? $p->kunjungan->dokter
                    : null;

                $poli = $p->kunjungan && $p->kunjungan->poli
                    ? $p->kunjungan->poli
                    : null;

                return [
                    'id' => $p->id,
                    'kode_transaksi' => $p->kode_transaksi,
                    'tanggal_transaksi' => $p->tanggal_transaksi,
                    'status' => $p->status,
                    'jumlah' => (int) $p->jumlah,
                    'total_tagihan' => (float) $p->total_tagihan,
                    'nama_layanan' => optional($p->layanan)->nama_layanan,

                    // ✅ TAMBAHKAN DATA DOKTER
                    'dokter' => $dokter ? [
                        'id' => $dokter->id,
                        'nama_dokter' => $dokter->nama_dokter,
                        'foto_dokter' => $dokter->foto_dokter,
                    ] : null,

                    // ✅ BONUS: Info poli
                    'poli' => $poli ? [
                        'id' => $poli->id,
                        'nama_poli' => $poli->nama_poli,
                    ] : null,
                ];
            });

        return response()->json(['success' => true, 'data' => $rows], 200);
    }

    public function getListPembayaran($pasienId)
    {
        try {
            Log::info('Getting payment list for pasien_id: '.$pasienId);

            // Validasi pasien exists
            $pasien = Pasien::find($pasienId);
            if (! $pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pasien tidak ditemukan',
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
                },
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
                                'harga_obat' => $obat->total_harga ?? 0,
                            ],
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
                        'nama_pasien' => $kunjungan->pasien->nama_pasien ?? 'Unknown',
                    ],
                    'poli' => [
                        'nama_poli' => $kunjungan->poli->nama_poli ?? 'Unknown',
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
                'data' => $formattedList,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting payment list: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getPembayaranPasien($pasienId)
    {
        try {
            Log::info('Getting pembayaran for pasien_id: '.$pasienId);

            $kunjungan = Kunjungan::with(['pasien', 'poli'])
                ->where('pasien_id', $pasienId)
                ->orderBy('created_at', 'desc')
                ->first();

            if (! $kunjungan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kunjungan tidak ditemukan untuk pasien ini',
                ], 404);
            }

            // ✅ SAFE: Cari EMR, jika tidak ada buat response kosong
            $emr = EMR::where('kunjungan_id', $kunjungan->id)->first();

            if (! $emr) {
                // ✅ Jika EMR tidak ada, return data minimal
                $paymentData = [
                    'id' => null,
                    'total_tagihan' => 0,
                    'status_pembayaran' => 'Belum Bayar',
                    'kode_transaksi' => null,
                    'tanggal_pembayaran' => null,
                    'metode_pembayaran_nama' => null,
                    'pasien' => [
                        'nama_pasien' => $kunjungan->pasien->nama_pasien ?? 'Unknown',
                    ],
                    'poli' => [
                        'nama_poli' => $kunjungan->poli->nama_poli ?? 'Unknown',
                    ],
                    'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                    'no_antrian' => $kunjungan->no_antrian,
                    'diagnosis' => 'Menunggu pemeriksaan dokter',
                    'resep' => [],
                    'layanan' => [],
                    'is_emr_missing' => true,
                ];
            } else {
                // ✅ EMR ada, cek pembayaran
                $pembayaran = Pembayaran::with('metodePembayaran')->where('emr_id', $emr->id)->first();

                if (! $pembayaran) {
                    $paymentData = [
                        'id' => null,
                        'total_tagihan' => 0,
                        'status_pembayaran' => 'Belum Bayar',
                        'kode_transaksi' => null,
                        'tanggal_pembayaran' => null,
                        'metode_pembayaran_nama' => null,
                        'pasien' => [
                            'nama_pasien' => $kunjungan->pasien->nama_pasien ?? 'Unknown',
                        ],
                        'poli' => [
                            'nama_poli' => $kunjungan->poli->nama_poli ?? 'Unknown',
                        ],
                        'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                        'no_antrian' => $kunjungan->no_antrian,
                        'diagnosis' => $emr->diagnosis ?? 'Sedang diproses',
                        'resep' => [],
                        'layanan' => [],
                        'is_payment_missing' => true,
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
                            'nama_pasien' => $kunjungan->pasien->nama_pasien ?? 'Unknown',
                        ],
                        'poli' => [
                            'nama_poli' => $kunjungan->poli->nama_poli ?? 'Unknown',
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
                                    'harga_obat' => $resep->obat->harga_obat ?? 0,
                                ],
                            ];
                        })->toArray(),
                        'layanan' => [],
                    ];
                }
            }

            $responseData = [
                'payments' => [$paymentData],
            ];

            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil diambil',
                'data' => $responseData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting pembayaran by pasien: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getPembayaranDetail($kunjunganId)
    {
        try {
            Log::info('Getting pembayaran detail for kunjungan_id: '.$kunjunganId);

            $kunjungan = Kunjungan::with([
                'pasien' => function ($query) {
                    $query->select('id', 'nama_pasien', 'alamat', 'tanggal_lahir', 'jenis_kelamin');
                },
                'poli' => function ($query) {
                    $query->select('id', 'nama_poli');
                },
            ])->find($kunjunganId);

            if (! $kunjungan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kunjungan tidak ditemukan',
                ], 404);
            }

            // Cari EMR
            $emr = EMR::where('kunjungan_id', $kunjunganId)->first();

            if (! $emr) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data pembayaran belum tersedia - EMR belum dibuat',
                    'data' => [
                        'id' => null,
                        'total_tagihan' => 0,
                        'diskon_tipe' => null, // ✅ TAMBAHAN
                        'diskon_nilai' => 0, // ✅ TAMBAHAN
                        'total_setelah_diskon' => 0, // ✅ TAMBAHAN
                        'status_pembayaran' => 'Belum Bayar',
                        'kode_transaksi' => null,
                        'tanggal_pembayaran' => null,
                        'metode_pembayaran_nama' => null,
                        'bukti_pembayaran' => null,
                        'pasien' => [
                            'nama_pasien' => $kunjungan->pasien->nama_pasien ?? 'Unknown',
                        ],
                        'poli' => [
                            'nama_poli' => $kunjungan->poli->nama_poli ?? 'Unknown',
                        ],
                        'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                        'no_antrian' => $kunjungan->no_antrian,
                        'keluhan_awal' => $kunjungan->keluhan_awal,
                        'diagnosis' => 'Menunggu pemeriksaan dokter',
                        'resep' => [],
                        'layanan' => [],
                        'is_emr_missing' => true,
                    ],
                ]);
            }

            // Jika EMR ada, cari pembayaran
            $pembayaran = Pembayaran::with('metodePembayaran')->where('emr_id', $emr->id)->first();

            if (! $pembayaran) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pembayaran belum dibuat untuk kunjungan ini',
                    'data' => [
                        'id' => null,
                        'total_tagihan' => 0,
                        'diskon_tipe' => null, // ✅ TAMBAHAN
                        'diskon_nilai' => 0, // ✅ TAMBAHAN
                        'total_setelah_diskon' => 0, // ✅ TAMBAHAN
                        'status_pembayaran' => 'Belum Bayar',
                        'kode_transaksi' => null,
                        'tanggal_pembayaran' => null,
                        'metode_pembayaran_nama' => null,
                        'bukti_pembayaran' => null,
                        'pasien' => [
                            'nama_pasien' => $kunjungan->pasien->nama_pasien ?? 'Unknown',
                        ],
                        'poli' => [
                            'nama_poli' => $kunjungan->poli->nama_poli ?? 'Unknown',
                        ],
                        'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                        'no_antrian' => $kunjungan->no_antrian,
                        'keluhan_awal' => $kunjungan->keluhan_awal,
                        'diagnosis' => $emr->diagnosis ?? 'Sedang diproses',
                        'resep' => [],
                        'layanan' => [],
                        'is_payment_missing' => true,
                    ],
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
                            'harga_obat' => $obat->harga_jual_obat ?? 0, // ✅ PERBAIKAN
                        ],
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
                        'harga_layanan' => $kl->layanan->harga_setelah_diskon ?? $kl->layanan->harga_sebelum_diskon, // ✅ PERBAIKAN
                        'jumlah' => $kl->jumlah ?? 1,
                    ];
                }
            }

            $responseData = [
                'id' => $pembayaran->id,
                'emr_id' => $pembayaran->emr_id,
                'total_tagihan' => (float) $pembayaran->total_tagihan,

                // ✅ DISKON DETAIL
                'diskon_tipe' => $pembayaran->diskon_tipe, // 'persen' atau 'nominal'
                'diskon_nilai' => (float) ($pembayaran->diskon_nilai ?? 0),
                'total_setelah_diskon' => (float) ($pembayaran->total_setelah_diskon ?? $pembayaran->total_tagihan),

                'status_pembayaran' => $pembayaran->status,
                'kode_transaksi' => $pembayaran->kode_transaksi,
                'tanggal_pembayaran' => $pembayaran->tanggal_pembayaran,
                'metode_pembayaran_id' => $pembayaran->metode_pembayaran_id,
                'metode_pembayaran_nama' => $pembayaran->metodePembayaran->nama_metode ?? null,
                'bukti_pembayaran' => $pembayaran->bukti_pembayaran,
                'uang_yang_diterima' => (float) ($pembayaran->uang_yang_diterima ?? 0),
                'kembalian' => (float) ($pembayaran->kembalian ?? 0),
                'catatan' => $pembayaran->catatan,

                'pasien' => [
                    'nama_pasien' => $kunjungan->pasien->nama_pasien ?? 'Unknown',
                ],
                'poli' => [
                    'nama_poli' => $kunjungan->poli->nama_poli ?? 'Unknown',
                ],
                'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                'no_antrian' => $kunjungan->no_antrian,
                'keluhan_awal' => $kunjungan->keluhan_awal,
                'diagnosis' => $emr->diagnosis ?? null,
                'resep' => $resepList,
                'layanan' => $layananList,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil diambil',
                'data' => $responseData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting pembayaran detail: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
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
        } elseif (! empty($resep->kunjungan_id)) {
            $kunjungan = Kunjungan::find($resep->kunjungan_id);
        }

        if ($kunjungan) {
            try {
                $title = 'Status Resep/Obat Diperbarui';
                $body = 'Status obat Anda kini: '.($resep->status ?? '-');

                $this->notifyPasienFromKunjungan($kunjungan, $title, $body, [
                    'type' => 'obat_status',
                    'resep_id' => $resep->id,
                    'new_status' => $resep->status,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim notif updateStatusObat: '.$e->getMessage());
            }
        } else {
            Log::warning('updateStatusObat: kunjungan tidak ditemukan untuk resep_id='.$resep->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status obat berhasil diperbarui',
            'data' => $resep,
        ]);
    }

    public function prosesPembayaran(Request $request)
    {
        try {
            Log::info('=== PROSES PEMBAYARAN START ===', [
                'request_data' => $request->all(),
                'timestamp' => now(),
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

            if (! $pembayaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pembayaran tidak ditemukan',
                ], 404);
            }

            // Cek apakah sudah dibayar
            if ($pembayaran->status == 'Sudah Bayar') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran sudah selesai sebelumnya',
                ], 400);
            }

            // Validasi relasi
            if (! $pembayaran->emr || ! $pembayaran->emr->kunjungan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data EMR atau kunjungan tidak ditemukan',
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
                    ],
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
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('=== PROSES PEMBAYARAN ERROR ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran: '.$e->getMessage(),
            ], 500);
        }
    }

    public function checkPaymentStatus($order_id)
    {
        try {
            Log::info('Checking payment status for order_id: '.$order_id);

            // Cari pembayaran berdasarkan kode_transaksi atau ID
            $pembayaran = Pembayaran::with(['metodePembayaran', 'emr.kunjungan.pasien', 'emr.kunjungan.poli'])
                ->where(function ($query) use ($order_id) {
                    $query->where('kode_transaksi', $order_id)
                        ->orWhere('id', $order_id);
                })
                ->first();

            if (! $pembayaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pembayaran tidak ditemukan',
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
                    'nama_pasien' => $kunjungan->pasien->nama_pasien ?? 'Unknown',
                ],
                'poli' => [
                    'nama_poli' => $kunjungan->poli->nama_poli ?? 'Unknown',
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
                            'harga_obat' => $resep->obat->harga_obat ?? 0,
                        ],
                    ];
                })->toArray(),
                'layanan' => [], // Bisa ditambahkan jika ada data layanan
            ];

            return response()->json([
                'success' => true,
                'message' => 'Status pembayaran berhasil diambil',
                'data' => $responseData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking payment status: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
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
            Log::error('Error getting metode pembayaran: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data metode pembayaran: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getResumeDokterPasien(Request $request)
    {
        try {
            $user = $request->user();
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
                    'message' => 'Data pasien tidak ditemukan untuk user ini',
                ], 404);
            }

            // ✅ ambil resume dokter milik pasien (via emr -> kunjungan)
            $rows = DB::table('resume_dokter as r')
                ->join('emr as e', 'r.emr_id', '=', 'e.id')
                ->join('kunjungan as k', 'e.kunjungan_id', '=', 'k.id')
                ->leftJoin('dokter as d', 'r.dokter_id', '=', 'd.id')
                ->leftJoin('poli as p', 'k.poli_id', '=', 'p.id')
                ->where('k.pasien_id', $pasien->id)
                ->where('r.status', 'final') // ✅ pasien hanya lihat yg sudah final
                ->select(
                    'r.id',
                    'r.emr_id',
                    'r.dokter_id',
                    'r.status',
                    'r.finalized_at',
                    'r.created_at',
                    'r.updated_at',

                    'r.ringkasan_kasus',
                    'r.diagnosis_utama',
                    'r.diagnosis_sekunder',
                    'r.tindakan',
                    'r.terapi_ringkas',
                    'r.hasil_penunjang_ringkas',
                    'r.kondisi_akhir',
                    'r.instruksi_pulang',
                    'r.rencana_tindak_lanjut',

                    'k.id as kunjungan_id',
                    'k.tanggal_kunjungan',
                    'k.no_antrian',
                    'p.nama_poli',

                    'd.nama_dokter',
                    'd.foto_dokter'
                )
                ->orderByDesc(DB::raw('COALESCE(r.finalized_at, r.updated_at, r.created_at)'))
                ->get()
                ->map(function ($r) {
                    return [
                        'id' => (int) $r->id,
                        'emr_id' => (int) $r->emr_id,
                        'status' => $r->status,
                        'finalized_at' => $r->finalized_at,
                        'created_at' => $r->created_at,
                        'updated_at' => $r->updated_at,

                        'resume' => [
                            'ringkasan_kasus' => $r->ringkasan_kasus,
                            'diagnosis_utama' => $r->diagnosis_utama,
                            'diagnosis_sekunder' => $r->diagnosis_sekunder,
                            'tindakan' => $r->tindakan,
                            'terapi_ringkas' => $r->terapi_ringkas,
                            'hasil_penunjang_ringkas' => $r->hasil_penunjang_ringkas,
                            'kondisi_akhir' => $r->kondisi_akhir,
                            'instruksi_pulang' => $r->instruksi_pulang,
                            'rencana_tindak_lanjut' => $r->rencana_tindak_lanjut,
                        ],

                        'kunjungan' => [
                            'id' => (int) $r->kunjungan_id,
                            'tanggal_kunjungan' => $r->tanggal_kunjungan,
                            'no_antrian' => $r->no_antrian,
                            'poli' => $r->nama_poli,
                        ],

                        'dokter' => [
                            'id' => $r->dokter_id ? (int) $r->dokter_id : null,
                            'nama_dokter' => $r->nama_dokter ?? '-',
                            'foto_dokter' => $r->foto_dokter,
                        ],
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Resume dokter (final) pasien berhasil diambil',
                'data' => $rows,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('ERROR getResumeDokterPasien: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil resume: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getDetailResumeDokterPasien(Request $request, $id)
    {
        try {
            $user = $request->user();
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

            // ✅ ambil resume yang memang milik pasien login (via emr.pasien_id)
            $row = DB::table('resume_dokter as r')
                ->join('emr as e', 'r.emr_id', '=', 'e.id')
                ->leftJoin('dokter as d', 'r.dokter_id', '=', 'd.id')
                ->leftJoin('poli as p', 'e.poli_id', '=', 'p.id')
                ->where('r.id', $id)
                ->where('e.pasien_id', $pasien->id)
                ->where('r.status', 'final') // ✅ hanya FINAL boleh tampil ke pasien
                ->select(
                    'r.id',
                    'r.emr_id',
                    'r.dokter_id',
                    'r.ringkasan_kasus',
                    'r.diagnosis_utama',
                    'r.diagnosis_sekunder',
                    'r.tindakan',
                    'r.terapi_ringkas',
                    'r.hasil_penunjang_ringkas',
                    'r.kondisi_akhir',
                    'r.instruksi_pulang',
                    'r.rencana_tindak_lanjut',
                    'r.status',
                    'r.finalized_at',
                    'r.created_at',
                    'r.updated_at',
                    'd.nama_dokter',
                    'd.foto_dokter',
                    'p.nama_poli'
                )
                ->first();

            if (! $row) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resume tidak ditemukan / bukan milik pasien ini',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => (int) $row->id,
                    'emr_id' => (int) $row->emr_id,
                    'dokter_id' => $row->dokter_id ? (int) $row->dokter_id : null,

                    'ringkasan_kasus' => $row->ringkasan_kasus,
                    'diagnosis_utama' => $row->diagnosis_utama,
                    'diagnosis_sekunder' => $row->diagnosis_sekunder,
                    'tindakan' => $row->tindakan,
                    'terapi_ringkas' => $row->terapi_ringkas,
                    'hasil_penunjang_ringkas' => $row->hasil_penunjang_ringkas,
                    'kondisi_akhir' => $row->kondisi_akhir,
                    'instruksi_pulang' => $row->instruksi_pulang,
                    'rencana_tindak_lanjut' => $row->rencana_tindak_lanjut,

                    'status' => $row->status,
                    'finalized_at' => $row->finalized_at,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,

                    'dokter' => [
                        'id' => $row->dokter_id ? (int) $row->dokter_id : null,
                        'nama_dokter' => $row->nama_dokter ?? '-',
                        'foto_dokter' => $row->foto_dokter ?? null,
                    ],
                    'poli' => [
                        'nama_poli' => $row->nama_poli ?? null,
                    ],
                ],
            ], 200);

        } catch (\Throwable $e) {
            Log::error('ERROR getDetailResumeDokterPasien: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getDokterByPoliJadwal(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'poli_id' => 'required|exists:poli,id',
            'tanggal' => 'required|date',
        ]);

        $poliId = (int) $request->poli_id;

        $tanggal = \Carbon\Carbon::parse($request->tanggal);
        $hariMap = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];
        $hari = $hariMap[$tanggal->format('l')] ?? $tanggal->format('l');

        $dokterList = \App\Models\Dokter::query()
            ->with(['jenisSpesialis'])
            ->whereIn('id', function ($q) use ($poliId) {
                $q->from('dokter_poli')
                    ->select('dokter_id')
                    ->where('poli_id', $poliId);
            })
            ->whereHas('jadwalDokter', function ($q) use ($hari, $poliId) {
                $q->where('hari', $hari);

                // kalau jadwal_dokter punya kolom poli_id, aktifkan:
                if (\Illuminate\Support\Facades\Schema::hasColumn('jadwal_dokter', 'poli_id')) {
                    $q->where('poli_id', $poliId);
                }
            })
            ->orderBy('nama_dokter')
            ->get()
            ->map(function ($d) {
                return [
                    'id' => (int) $d->id,
                    'nama_dokter' => (string) $d->nama_dokter,
                    'spesialis' => $d->jenisSpesialis ? (string) $d->jenisSpesialis->nama_spesialis : null,
                ];
            })->values();

        return response()->json([
            'success' => true,
            'message' => 'List dokter tersedia',
            'data' => $dokterList,
        ]);
    }

    public function getJadwalDokterByDokterPoliTanggal(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'dokter_id' => 'required|exists:dokter,id',
            'poli_id' => 'required|exists:poli,id',
            'tanggal' => 'required|date',
        ]);

        $dokterId = (int) $request->dokter_id;
        $poliId = (int) $request->poli_id;

        $tanggal = \Carbon\Carbon::parse($request->tanggal);
        $hariMap = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];
        $hari = $hariMap[$tanggal->format('l')] ?? $tanggal->format('l');

        $q = \App\Models\JadwalDokter::query()
            ->where('dokter_id', $dokterId)
            ->where('hari', $hari)
            ->orderBy('jam_awal');

        // ✅ hanya filter poli_id kalau kolomnya memang ada
        if (\Illuminate\Support\Facades\Schema::hasColumn('jadwal_dokter', 'poli_id')) {
            $q->where('poli_id', $poliId);
        }

        $jadwal = $q->get()->map(function ($j) {
            return [
                'id' => (int) $j->id, // ✅ ini yg dipakai FE jadi jadwal_dokter_id
                'hari' => (string) $j->hari,
                'jam_awal' => (string) $j->jam_awal,
                'jam_selesai' => (string) $j->jam_selesai,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal dokter',
            'data' => $jadwal,
        ]);
    }

    public function getDataPoli(Request $request)
    {
        try {
            $data = \App\Models\Poli::query()
                ->select('id', 'nama_poli')
                ->orderBy('nama_poli')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $data,
            ], 200);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('getDataPoli error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat daftar poli.',
            ], 500);
        }
    }

    // end pasien

    public function bookingDokter(Request $request)
    {
        try {
            Log::info('🔥 bookingDokter called with data: ', $request->all());

            $request->validate([
                'pasien_id' => ['required', 'exists:pasien,id'],
                'poli_id' => ['required', 'exists:poli,id'],
                'tanggal_kunjungan' => ['required', 'date'],
                'keluhan_awal' => ['required', 'string'],
                'dokter_id' => ['required', 'exists:dokter,id'],
                'jadwal_dokter_id' => ['nullable', 'exists:jadwal_dokter,id'],
            ]);

            $pasienId = $request->pasien_id;
            $tanggalKunjungan = $request->tanggal_kunjungan;
            $poliId = $request->poli_id;

            // VALIDASI PROFIL LENGKAP
            if (! $this->isProfileComplete($pasienId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mohon lengkapi data profil Anda terlebih dahulu sebelum membuat janji',
                    'error_code' => 'PROFILE_INCOMPLETE',
                ], 422);
            }

            Log::info("🎯 Processing booking for pasien_id: $pasienId, poli_id: $poliId, tanggal: $tanggalKunjungan");

            // CEK BOOKING AKTIF YANG TIDAK BOLEH DUPLIKAT
            $activeStatuses = ['Pending', 'Confirmed', 'Waiting', 'Engaged'];
            $existingActiveBooking = Kunjungan::where('pasien_id', $pasienId)
                ->where('poli_id', $poliId)
                ->where('tanggal_kunjungan', $tanggalKunjungan)
                ->whereIn('status', $activeStatuses)
                ->first();

            if ($existingActiveBooking) {
                Log::info("❌ Active booking found for pasien_id: $pasienId, poli_id: $poliId, tanggal: $tanggalKunjungan, status: {$existingActiveBooking->status}");

                $statusMessages = [
                    'Pending' => 'Anda sudah memiliki janji yang menunggu konfirmasi dengan poli ini pada tanggal yang sama.',
                    'Confirmed' => 'Anda sudah memiliki janji yang telah dikonfirmasi dengan poli ini pada tanggal yang sama.',
                    'Waiting' => 'Anda sudah terdaftar dalam antrian dengan poli ini pada tanggal yang sama.',
                    'Engaged' => 'Anda sedang dalam proses konsultasi dengan poli ini pada tanggal yang sama.',
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
                        'tanggal_kunjungan' => $existingActiveBooking->tanggal_kunjungan,
                    ],
                ], 422);
            }

            // INFO BOOKING SEBELUMNYA (CANCELLED / SUCCESS / COMPLETED)
            $previousBookings = Kunjungan::where('pasien_id', $pasienId)
                ->where('poli_id', $poliId)
                ->where('tanggal_kunjungan', $tanggalKunjungan)
                ->whereIn('status', ['Cancelled', 'Success', 'Completed'])
                ->get();

            if ($previousBookings->count() > 0) {
                Log::info("ℹ️ Found {$previousBookings->count()} previous booking(s) with Cancelled/Success status for same date");
            }

            $result = DB::transaction(function () use ($tanggalKunjungan, $poliId, $pasienId, $request) {

                // =========================
                // HITUNG NOMOR ANTRIAN
                // =========================
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

                // =========================
                // TENTUKAN JADWAL_DOKTER_ID
                // =========================
                $jadwalDokterId = $request->jadwal_dokter_id;

                if (! $jadwalDokterId) {
                    // Ambil hari dari tanggal_kunjungan (English → Indo)
                    $hariCarbon = Carbon::parse($tanggalKunjungan)->format('l');

                    $mapHari = [
                        'Monday' => 'Senin',
                        'Tuesday' => 'Selasa',
                        'Wednesday' => 'Rabu',
                        'Thursday' => 'Kamis',
                        'Friday' => 'Jumat',
                        'Saturday' => 'Sabtu',
                        'Sunday' => 'Minggu',
                    ];

                    $hari = $mapHari[$hariCarbon] ?? $hariCarbon;
                    Log::info("🕒 Hari kunjungan: $hari");
                    $jadwal = JadwalDokter::where('poli_id', $poliId)
                        ->where('dokter_id', $request->dokter_id)
                        ->where('hari', $hari)
                        ->orderBy('id')        // atau bisa dihapus juga, cuma .first()
                        ->first();

                    if ($jadwal) {
                        $jadwalDokterId = $jadwal->id;
                        Log::info("📌 Jadwal ditemukan: {$jadwal->id} untuk dokter {$request->dokter_id} ($hari)");
                    } else {
                        Log::warning("⚠️ Tidak ada jadwal ditemukan untuk dokter {$request->dokter_id} hari $hari!");
                    }
                } else {
                    Log::info("📌 jadwal_dokter_id diterima dari FE: $jadwalDokterId");
                }

                // =========================
                // CREATE BOOKING
                // =========================
                $kunjungan = new Kunjungan;
                $kunjungan->pasien_id = $pasienId;
                $kunjungan->poli_id = $poliId;
                $kunjungan->dokter_id = $request->dokter_id;
                $kunjungan->jadwal_dokter_id = $jadwalDokterId;
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
            Log::error('❌ Exception in bookingDokter: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat kunjungan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function ubahStatusKunjungan(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|max:50',
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
            $body = 'Status kunjungan Anda kini: '.($kunjungan->status ?? '-');
            if (! empty($kunjungan->no_antrian)) {
                $body .= ' | No. Antrian: '.$kunjungan->no_antrian;
            }

            $this->notifyPasienFromKunjungan($kunjungan, $title, $body, [
                'changed_by' => 'admin',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim notif ubahStatusKunjungan: '.$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Status kunjungan berhasil diperbarui',
            'data' => $kunjungan,
        ]);
    }

    public function batalkanStatusKunjungan(Request $request)
    {
        try {
            Log::info('=== BATALKAN KUNJUNGAN START ===');
            Log::info('Request method: '.$request->method());
            Log::info('Request data: ', $request->all());

            $request->validate([
                'id' => 'required|integer|exists:kunjungan,id',
            ]);

            $kunjunganId = $request->input('id');
            Log::info('Processing kunjungan ID: '.$kunjunganId);

            $dataKunjungan = Kunjungan::findOrFail($kunjunganId);
            Log::info('Found kunjungan before update: ', $dataKunjungan->toArray());

            if (! in_array($dataKunjungan->status, ['Pending', 'Confirmed', 'Waiting'])) {
                Log::warning('Cannot cancel kunjungan with status: '.$dataKunjungan->status);

                return response()->json([
                    'success' => false,
                    'status' => 400,
                    'message' => 'Kunjungan dengan status "'.$dataKunjungan->status.'" tidak dapat dibatalkan',
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

                Log::info('Rows affected by update: '.$affected);

                if ($affected === 0) {
                    throw new \Exception('Gagal memperbarui data kunjungan');
                }

                return Kunjungan::find($kunjunganId);
            });

            Log::info('Updated kunjungan after transaction: ', $updatedKunjungan->toArray());

            if ($updatedKunjungan->status !== 'Canceled') {
                Log::error('Status update failed - still: '.$updatedKunjungan->status);

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
            Log::error('Kunjungan not found: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Data kunjungan tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Exception in batalkanStatusKunjungan: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getRiwayatKunjungan($pasien_id)
    {
        try {
            Log::info('=== GET RIWAYAT KUNJUNGAN START ===', [
                'pasien_id' => $pasien_id,
                'timestamp' => now(),
            ]);

            if (! $pasien_id || ! is_numeric($pasien_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID pasien tidak valid',
                ], 400);
            }

            $pasien = Pasien::find($pasien_id);
            if (! $pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pasien tidak ditemukan',
                ], 404);
            }

            // ✅ PERBAIKAN QUERY - Tambahkan eager loading yang lengkap
            $riwayatKunjungan = Kunjungan::with([
                'pasien',
                'poli', // ✅ PENTING!
                'dokter' => function ($query) {
                    $query->with(['jenisSpesialis', 'poli']);
                },
                'emr' => function ($query) {
                    $query->with([
                        'resep.obat',
                        'perawat',
                    ]);
                },
            ])
                ->where('pasien_id', $pasien_id)
                ->orderBy('tanggal_kunjungan', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Kunjungan query result', [
                'total_found' => $riwayatKunjungan->count(),
                'pasien_id' => $pasien_id,
            ]);

            $formattedData = $riwayatKunjungan->map(function ($kunjungan) {
                $statusFinal = $this->calculateFinalStatus($kunjungan);

                $data = [
                    'id' => $kunjungan->id,
                    'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                    'status' => $kunjungan->status ?? 'Pending',
                    'status_final' => $statusFinal,
                    'no_antrian' => $kunjungan->no_antrian,
                    'keluhan_awal' => $kunjungan->keluhan_awal,
                    'created_at' => $kunjungan->created_at,
                    'updated_at' => $kunjungan->updated_at,
                ];

                // ===== POLI =====
                try {
                    if ($kunjungan->poli) {
                        $data['poli'] = [
                            'id' => $kunjungan->poli->id,
                            'nama_poli' => $kunjungan->poli->nama_poli,
                        ];
                    } else {
                        $data['poli'] = null;
                    }
                } catch (\Exception $e) {
                    $data['poli'] = null;
                    Log::warning('Poli relation error', ['error' => $e->getMessage()]);
                }

                // ===== DOKTER + POLI + SPESIALIS =====
                try {
                    $dokter = $kunjungan->dokter;

                    if ($dokter) {
                        $data['dokter'] = [
                            'id' => $dokter->id,
                            'nama_dokter' => $dokter->nama_dokter ?? 'Tidak diketahui',
                            'foto_dokter' => $dokter->foto_dokter ?? null,
                            'no_hp' => $dokter->no_hp ?? null,
                            'pengalaman' => $dokter->pengalaman ?? null,
                        ];

                        // ✅ POLI - Ambil dari relasi dokter dulu, kalau tidak ada ambil dari kunjungan
                        if ($dokter->poli && $dokter->poli->isNotEmpty()) {
                            $data['dokter']['poli'] = [
                                'id' => $dokter->poli->first()->id,
                                'nama_poli' => $dokter->poli->first()->nama_poli,
                            ];
                        } elseif ($kunjungan->poli) {
                            $data['dokter']['poli'] = [
                                'id' => $kunjungan->poli->id,
                                'nama_poli' => $kunjungan->poli->nama_poli,
                            ];
                        } else {
                            $data['dokter']['poli'] = null;
                        }

                        // ✅ SPESIALIS
                        if ($dokter->jenisSpesialis) {
                            $data['dokter']['spesialis'] = [
                                'id' => $dokter->jenisSpesialis->id,
                                'nama_spesialis' => $dokter->jenisSpesialis->nama_spesialis,
                            ];
                        } else {
                            $data['dokter']['spesialis'] = [
                                'id' => null,
                                'nama_spesialis' => 'Umum',
                            ];
                        }

                        Log::info('Dokter data mapped', [
                            'kunjungan_id' => $kunjungan->id,
                            'dokter_id' => $dokter->id,
                            'poli' => $data['dokter']['poli'],
                            'spesialis' => $data['dokter']['spesialis'],
                        ]);
                    } else {
                        $data['dokter'] = null;
                    }
                } catch (\Exception $e) {
                    $data['dokter'] = null;
                    Log::error('Dokter relation error', ['error' => $e->getMessage()]);
                }

                // ===== EMR (sisanya sama seperti kode Anda) =====
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
                            'tanggal_pemeriksaan' => $emr->created_at,
                            'created_at' => $emr->created_at,
                            'tanda_vital' => [
                                'tekanan_darah' => $emr->tekanan_darah ?? null,
                                'suhu_tubuh' => $emr->suhu_tubuh ?? null,
                                'nadi' => $emr->nadi ?? null,
                                'pernapasan' => $emr->pernapasan ?? null,
                                'saturasi_oksigen' => $emr->saturasi_oksigen ?? null,
                                'tinggi_badan' => $emr->tinggi_badan ?? null,
                                'berat_badan' => $emr->berat_badan ?? null,
                                'imt' => $emr->imt ?? null,
                            ],
                        ];

                        if ($emr->perawat) {
                            $data['emr']['perawat'] = [
                                'id' => $emr->perawat->id,
                                'nama_perawat' => $emr->perawat->nama_perawat,
                                'foto_perawat' => $emr->perawat->foto_perawat,
                                'no_hp_perawat' => $emr->perawat->no_hp_perawat,
                            ];
                        }
                    } else {
                        $data['emr'] = null;
                    }
                } catch (\Exception $e) {
                    $data['emr'] = null;
                    Log::warning('EMR relation error', ['error' => $e->getMessage()]);
                }

                // ===== LAYANAN =====
                try {
                    $layanan = DB::table('kunjungan_layanan')
                        ->join('layanan', 'kunjungan_layanan.layanan_id', '=', 'layanan.id')
                        ->leftJoin('kategori_layanan', 'layanan.kategori_layanan_id', '=', 'kategori_layanan.id')
                        ->where('kunjungan_layanan.kunjungan_id', $kunjungan->id)
                        ->select(
                            'kunjungan_layanan.id',
                            'layanan.nama_layanan',
                            'layanan.harga_sebelum_diskon',
                            'layanan.harga_setelah_diskon',
                            'layanan.diskon',
                            'kunjungan_layanan.jumlah',
                            'kategori_layanan.nama_kategori',
                            // ✅ Gunakan harga setelah diskon kalau ada, fallback ke sebelum diskon
                            DB::raw('COALESCE(layanan.harga_setelah_diskon, layanan.harga_sebelum_diskon, 0) as harga_final'),
                            DB::raw('(COALESCE(layanan.harga_setelah_diskon, layanan.harga_sebelum_diskon, 0) * kunjungan_layanan.jumlah) as subtotal')
                        )
                        ->get();

                    $data['layanan'] = $layanan->map(function ($l) {
                        return [
                            'id' => $l->id,
                            'nama_layanan' => $l->nama_layanan,
                            'harga_layanan' => (float) $l->harga_final, // ✅ untuk backward compatibility dengan Flutter
                            'harga_sebelum_diskon' => (float) ($l->harga_sebelum_diskon ?? 0),
                            'harga_setelah_diskon' => (float) ($l->harga_setelah_diskon ?? 0),
                            'diskon' => (float) ($l->diskon ?? 0),
                            'jumlah' => (int) $l->jumlah,
                            'subtotal' => (float) $l->subtotal,
                            'kategori' => $l->nama_kategori,
                        ];
                    })->toArray();
                } catch (\Exception $e) {
                    $data['layanan'] = [];
                    Log::warning('Layanan relation error', ['error' => $e->getMessage()]);
                }

                // ===== RESEP OBAT =====
                // ===== RESEP OBAT =====
                try {
                    $resep = DB::table('resep_obat')
                        ->join('obat', 'resep_obat.obat_id', '=', 'obat.id')
                        ->join('resep', 'resep_obat.resep_id', '=', 'resep.id')
                        ->leftJoin('brand_farmasi', 'obat.brand_farmasi_id', '=', 'brand_farmasi.id')
                        ->leftJoin('kategori_obat', 'obat.kategori_obat_id', '=', 'kategori_obat.id')
                        ->where('resep.kunjungan_id', $kunjungan->id)
                        ->select(
                            'resep_obat.id',
                            'obat.nama_obat',
                            'obat.kode_obat',
                            'obat.kandungan_obat',
                            'resep_obat.dosis',
                            'resep_obat.jumlah',
                            'obat.harga_jual_obat as harga_per_item',
                            DB::raw('(obat.harga_jual_obat * resep_obat.jumlah) as subtotal'),
                            'resep_obat.keterangan',
                            'resep.status as status',
                            'brand_farmasi.nama_brand',
                            'kategori_obat.nama_kategori_obat'
                        )
                        ->get();

                    $data['resep_obat'] = $resep->map(function ($r) {
                        return [
                            'id' => $r->id,
                            'nama_obat' => $r->nama_obat,
                            'kode_obat' => $r->kode_obat,
                            'kandungan_obat' => $r->kandungan_obat,
                            'dosis' => $r->dosis,
                            'jumlah' => (int) $r->jumlah,
                            'harga_per_item' => (float) $r->harga_per_item,
                            'subtotal' => (float) $r->subtotal,
                            'keterangan' => $r->keterangan ?? null,
                            'status' => $r->status ?? 'waiting',
                            'brand' => $r->nama_brand,
                            'kategori' => $r->nama_kategori_obat,
                        ];
                    })->toArray();
                } catch (\Exception $e) {
                    $data['resep_obat'] = [];
                    Log::warning('Resep obat relation error', ['error' => $e->getMessage()]);
                }

                // ===== PEMBAYARAN =====
                try {
                    $emr = EMR::where('kunjungan_id', $kunjungan->id)->first();
                    if ($emr) {
                        $pembayaran = Pembayaran::with('metodePembayaran')->where('emr_id', $emr->id)->first();
                        if ($pembayaran) {
                            $totalLayanan = collect($data['layanan'])->sum('subtotal');
                            $totalObat = collect($data['resep_obat'])->sum('subtotal');

                            $data['pembayaran'] = [
                                'id' => $pembayaran->id,
                                'biaya_konsultasi' => $totalLayanan > 0 ? $totalLayanan : null,
                                'total_obat' => $totalObat,
                                'total_tagihan' => $pembayaran->total_tagihan ?? ($totalLayanan + $totalObat),
                                'status' => $pembayaran->status ?? 'Belum Bayar',
                                'kode_transaksi' => $pembayaran->kode_transaksi ?? null,
                                'tanggal_pembayaran' => $pembayaran->tanggal_pembayaran ?? $pembayaran->created_at,
                                'uang_yang_diterima' => $pembayaran->uang_yang_diterima ?? null,
                                'kembalian' => $pembayaran->kembalian ?? null,
                                'diskon_tipe' => $pembayaran->diskon_tipe ?? null,
                                'diskon_nilai' => $pembayaran->diskon_nilai ?? 0,
                                'total_setelah_diskon' => $pembayaran->total_setelah_diskon ?? null,
                                'bukti_pembayaran' => $pembayaran->bukti_pembayaran ?? null,
                                'catatan' => $pembayaran->catatan ?? null,
                                'metode_pembayaran' => $pembayaran->metodePembayaran ?
                                    $pembayaran->metodePembayaran->nama_metode : null,
                            ];
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

                return $data;
            });

            // Info pasien
            $pasienInfo = [
                'id' => $pasien->id,
                'no_emr' => $pasien->no_emr,
                'nama_pasien' => $pasien->nama_pasien,
                'nik' => $pasien->nik,
                'no_bpjs' => $pasien->no_bpjs,
                'alamat' => $pasien->alamat ?? null,
                'tanggal_lahir' => $pasien->tanggal_lahir ?? null,
                'jenis_kelamin' => $pasien->jenis_kelamin ?? null,
                'golongan_darah' => $pasien->golongan_darah ?? null,
                'no_hp_pasien' => $pasien->no_hp_pasien ?? null,
                'foto_pasien' => $pasien->foto_pasien ?? null,
                'qr_code_pasien' => $pasien->qr_code_pasien ?? null,
            ];

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
                'pasien_id' => $pasien_id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
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

    private function isProfileComplete($pasienId)
    {
        $pasien = Pasien::find($pasienId);
        if (! $pasien) {
            return false;
        }

        // Field wajib untuk order
        $requiredFields = [
            'nama_pasien',
            'tanggal_lahir',
            'jenis_kelamin',
            'alamat',
            'no_hp_pasien',
        ];

        foreach ($requiredFields as $field) {
            if (empty($pasien->$field)) {
                Log::info("Profile incomplete: missing $field", ['pasien_id' => $pasienId]);

                return false;
            }
        }

        return true;
    }

    /**
     * ✅ PASIEN - LIST SEMUA LAYANAN (dinamis dari DB)
     * GET /api/pasien/layanan?q=...&kategori_layanan_id=...
     */
    public function getAllLayananPasien(Request $request)
    {
        $q = $request->query('q');
        $kategoriId = $request->query('kategori_layanan_id');

        $query = Layanan::query()
            ->with(['kategori:id,nama_kategori,deskripsi_kategori,status_kategori']);

        if (! empty($kategoriId)) {
            $query->where('kategori_layanan_id', $kategoriId);
        }

        if (! empty($q)) {
            $query->where('nama_layanan', 'like', "%{$q}%");
        }

        $layanan = $query
            ->orderBy('nama_layanan')
            ->get()
            ->map(function ($l) {
                return [
                    'id' => $l->id,
                    'nama_layanan' => $l->nama_layanan,
                    'harga_layanan' => (float) $l->harga_layanan,
                    'kategori_layanan_id' => $l->kategori_layanan_id,
                    'kategori' => $l->kategori ? [
                        'id' => $l->kategori->id,
                        'nama_kategori' => $l->kategori->nama_kategori,
                        'deskripsi_kategori' => $l->kategori->deskripsi_kategori,
                        'status_kategori' => $l->kategori->status_kategori,
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'List layanan',
            'data' => $layanan,
        ]);
    }

    public function getRiwayatLayananPasien()
    {
        $user = auth()->user();
        $pasien = Pasien::where('user_id', $user->id)->firstOrFail();

        $data = PenjualanLayanan::with('metodePembayaran')
            ->where('pasien_id', $pasien->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($p) {
                return [
                    'kode_transaksi' => $p->kode_transaksi,
                    'total' => $p->total_tagihan,
                    'status' => $p->status,
                    'metode_pembayaran' => $p->metodePembayaran?->nama_metode,
                    'tanggal' => $p->tanggal_transaksi,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function getMetodePembayaran()
    {
        $data = MetodePembayaran::query()
            ->orderBy('nama_metode')
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'nama_metode' => $m->nama_metode,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'List metode pembayaran',
            'data' => $data,
        ]);
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
            Log::warning('Error calculating final status: '.$e->getMessage());

            return $kunjungan->status ?? 'Pending';
        }
    }

    private function normalizeFarmasiStatus(?string $raw): ?string
    {
        if (! $raw) {
            return null;
        }

        $s = strtolower(trim($raw));

        // samakan dengan FE kamu: waiting / done
        if (in_array($s, ['menunggu', 'waiting', 'belum diambil', 'belum_diambil'])) {
            return 'waiting';
        }
        if (in_array($s, ['done', 'diambil', 'sudah diambil', 'sudah_diambil'])) {
            return 'done';
        }

        return $s;
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
                    'errors' => $validator->errors(),
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
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Login dokter error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
            ], 500);
        }
    }

    /**
     * ✅ Get jadwal dokter yang sedang login (untuk sidebar calendar)
     * Endpoint: GET /api/dokter/jadwal
     * Auth: Dokter only
     */
    public function getJadwalDokterSaya(Request $request)
    {
        try {
            // Ambil dokter yang sedang login
            $user = $request->user();

            if (! $user || ! $user->dokter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dokter tidak ditemukan',
                ], 404);
            }

            $dokterId = $user->dokter->id;

            // Ambil semua jadwal dokter ini
            $jadwal = \App\Models\JadwalDokter::where('dokter_id', $dokterId)
                ->orderBy('hari')
                ->get();

            if ($jadwal->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Tidak ada jadwal praktik',
                ]);
            }

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
            $now = Carbon::now($tz);

            $result = [];

            foreach ($jadwal as $item) {
                $hariStr = $item->hari;
                $hariNumber = $hariMapping[$hariStr] ?? null;

                if ($hariNumber !== null) {
                    // Konversi hari ke tanggal terdekat bulan ini
                    $tanggalTerdekat = $this->getNextDateByDay($hariNumber, $now->copy()->startOfMonth());

                    $result[] = [
                        'id' => $item->id,
                        'dokter_id' => $dokterId,
                        'hari' => $hariStr,
                        'jam_mulai' => $item->jam_mulai,
                        'jam_selesai' => $item->jam_selesai,
                        'tanggal_terdekat' => $tanggalTerdekat->format('Y-m-d'),
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $result,
                'dokter_id' => $dokterId,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting jadwal dokter saya: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    private function _calculateAssessmentScore($emr)
    {
        if (! $emr) {
            return 0;
        }

        $fields = [
            'tekanan_darah', 'suhu_tubuh', 'nadi', 'pernapasan', 'saturasi_oksigen',
            'tinggi_badan', 'berat_badan', 'keluhan_utama', 'catatan_perawat',
        ];

        $completed = 0;
        foreach ($fields as $field) {
            if (! empty($emr->{$field})) {
                $completed++;
            }
        }

        return round(($completed / count($fields)) * 100);
    }

    private function _calculatePriorityLevel($emr, $pasien)
    {
        $priority = 'normal';

        // Check vital signs for abnormal values
        if ($emr && ! empty($emr->saturasi_oksigen) && floatval($emr->saturasi_oksigen) < 95) {
            $priority = 'urgent';
        } elseif ($emr && ! empty($emr->suhu_tubuh) && floatval($emr->suhu_tubuh) > 37.5) {
            $priority = 'high';
        }

        // Check age-based priority
        if ($pasien && $pasien->tanggal_lahir) {
            $age = \Carbon\Carbon::parse($pasien->tanggal_lahir)->age;
            if ($age >= 65 || $age <= 5) {
                $priority = $priority === 'normal' ? 'high' : $priority;
            }
        }

        return $priority;
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
                if ($kl->layanan) {
                    $subtotal = (float) $kl->layanan->harga_layanan * (int) $kl->jumlah;
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
                Log::info('No layanan found - no default consultation fee added');
            }

            // ✅ NULLABLE: Add medication costs if resep exists
            if ($resepId) {
                $resep = Resep::with('obat')->find($resepId);
                if ($resep && $resep->obat) {
                    foreach ($resep->obat as $obat) {
                        $jumlah = (int) ($obat->pivot->jumlah ?? 1);
                        $hargaObat = (float) ($obat->total_harga ?? 0);
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
            } else {
                Log::info('ℹ️ No medications in billing - consultation only', [
                    'kunjungan_id' => $kunjungan->id,
                ]);
            }

            // Ensure minimum value
            if ($totalTagihan < 0) {
                $totalTagihan = 0;
            } // optional, untuk jaga-jaga

            Log::info('Total billing calculated:', [
                'kunjungan_id' => $kunjungan->id,
                'total_tagihan' => $totalTagihan,
                'has_resep' => $resepId !== null,
                'has_layanan' => ! $kunjunganLayanan->isEmpty(),
            ]);

            return round($totalTagihan, 2);
        } catch (\Exception $e) {
            Log::error('Error calculating total tagihan: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            // Return default consultation fee as fallback
            return 0.00;
        }
    }

    /**
     * Get EMR details for editing
     */
    public function getEMRForEdit($emrId)
    {
        try {
            $user_id = Auth::id();
            $dokter = Dokter::where('user_id', $user_id)->firstOrFail();

            // ✅ PERBAIKAN: Validasi EMR berdasarkan dokter_id langsung
            $emr = EMR::with([
                'kunjungan.pasien',
                'kunjungan.poli',
                'resep.obat' => function ($query) {
                    $query->withPivot('jumlah', 'dosis', 'keterangan', 'status');
                },
            ])
                ->where('id', $emrId)
                ->where('dokter_id', $dokter->id) // ✅ Langsung filter berdasarkan dokter_id
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Data EMR berhasil diambil',
                'data' => $emr,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'EMR tidak ditemukan atau Anda tidak memiliki akses',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error getting EMR for edit (EMR-based): '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data EMR: '.$e->getMessage(),
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
                'email.email' => 'Format email tidak valid.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $email = $request->email;
            $user = User::where('email', $email)->first();

            // Email tidak ditemukan
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email tidak ditemukan dalam sistem',
                ], 404);
            }

            $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

            $cacheKey = 'forgot_password_otp_'.$email;
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
                Log::error('Failed to send forgot password email: '.$e->getMessage());

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim email. Silakan coba lagi.',
                ], 500);
            }
        } catch (\Throwable $e) {
            Log::error('Error in sendForgotPasswordOTP: '.$e->getMessage());

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
                'email' => 'required|email',
                'otp' => 'required|string|size:6',
                'new_password' => 'required|string|min:6|confirmed',
            ], [
                'email.required' => 'Email tidak boleh kosong.',
                'email.email' => 'Format email tidak valid.',
                'otp.required' => 'Kode OTP wajib diisi.',
                'otp.size' => 'Kode OTP harus 6 digit.',
                'new_password.required' => 'Password baru wajib diisi.',
                'new_password.min' => 'Password baru minimal 6 karakter.',
                'new_password.confirmed' => 'Konfirmasi password tidak sama.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $email = $request->email;
            $otp = $request->otp;
            $newPassword = $request->new_password;

            $user = User::where('email', $email)->first();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email tidak ditemukan dalam sistem',
                ], 404);
            }

            $cacheKey = 'forgot_password_otp_'.$email;
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
            Log::error('Error in resetPasswordWithOTP: '.$e->getMessage());

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
                'email.email' => 'Format email tidak valid.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $email = $request->email;

            // CEK EMAIL DI DB
            $user = User::where('email', $email)->first();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email tidak ditemukan dalam sistem',
                ], 404);
            }

            // GENERATE & SIMPAN OTP (5 menit)
            $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $cacheKey = 'forgot_username_otp_'.$email;
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
                Log::error('Failed to send forgot-username OTP email: '.$e->getMessage());

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim email. Silakan coba lagi.',
                ], 500);
            }
        } catch (\Throwable $e) {
            Log::error('Error in sendForgotUsernameOTP: '.$e->getMessage());

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
                'email' => 'required|email',
                'otp' => 'required|string|size:6',
                'new_username' => 'sometimes|nullable|string|min:3|max:50|regex:/^[A-Za-z0-9_.-]+$/|unique:user,username',
            ], [
                'email.required' => 'Email tidak boleh kosong.',
                'email.email' => 'Format email tidak valid.',
                'otp.required' => 'Kode OTP wajib diisi.',
                'otp.size' => 'Kode OTP harus 6 digit.',
                'new_username.min' => 'Username minimal 3 karakter.',
                'new_username.regex' => 'Username hanya boleh huruf, angka, titik, garis bawah, dan minus.',
                'new_username.unique' => 'Username sudah dipakai.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $email = trim(strtolower($request->email));
            $otp = $request->otp;
            $new = $request->new_username;

            $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email tidak ditemukan dalam sistem',
                ], 404);
            }

            $cacheKey = 'forgot_username_otp_'.$email;
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
                        'email' => $email,
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
                    'email' => $email,
                    'new_username' => $new,
                    'updated_at' => now()->toISOString(),
                ],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Error in verifyOrChangeUsernameWithOTP: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
            ], 500);
        }
    }

    public function getDetailPembayaran($kunjunganId)
    {
        try {
            Log::info('🔍 getDetailPembayaran called for kunjungan_id: '.$kunjunganId);

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
                        'diskon_tipe',
                        'diskon_nilai',
                        'total_setelah_diskon',
                        'status',
                        'kode_transaksi',
                        'metode_pembayaran_id',
                        'tanggal_pembayaran',
                        'uang_yang_diterima',
                        'kembalian',
                        'bukti_pembayaran',
                        'catatan'
                    );
                },
                'emr.pembayaran.metodePembayaran',
            ])
                ->where('id', $kunjunganId)
                ->first();

            if (! $kunjungan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kunjungan tidak ditemukan',
                ], 404);
            }

            if (! $kunjungan->emr || ! $kunjungan->emr->pembayaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pembayaran tidak tersedia',
                ], 404);
            }

            $pembayaran = $kunjungan->emr->pembayaran;

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

                'kode_transaksi' => $pembayaran->kode_transaksi ?? null,
                'metode_pembayaran_id' => $pembayaran->metode_pembayaran_id ?? null,
                'metode_pembayaran_nama' => $pembayaran->metodePembayaran->nama_metode ?? 'Cash',

                'status_pembayaran' => $pembayaran->status ?? 'Belum Bayar',
                'pembayaran_id' => $pembayaran->id ?? null,

                // global/header
                'total_tagihan' => (float) ($pembayaran->total_tagihan ?? 0),
                'diskon_tipe' => $pembayaran->diskon_tipe,
                'diskon_nilai' => (float) ($pembayaran->diskon_nilai ?? 0),
                'total_setelah_diskon' => (float) ($pembayaran->total_setelah_diskon ?? 0),
                'uang_yang_diterima' => (float) ($pembayaran->uang_yang_diterima ?? 0),
                'kembalian' => (float) ($pembayaran->kembalian ?? 0),
                'bukti_pembayaran' => $pembayaran->bukti_pembayaran,
                'catatan' => $pembayaran->catatan,

                // detail section
                'layanan' => [],
                'resep_obat' => [],
                'lab' => [],
                'radiologi' => [],

                // summary per section
                'total_layanan_before' => 0,
                'total_layanan_discount' => 0,
                'total_layanan_after' => 0,

                'total_obat_before' => 0,
                'total_obat_discount' => 0,
                'total_obat_after' => 0,

                'total_lab_before' => 0,
                'total_lab_discount' => 0,
                'total_lab_after' => 0,

                'total_radiologi_before' => 0,
                'total_radiologi_discount' => 0,
                'total_radiologi_after' => 0,
            ];

            // =========================
            // LAYANAN
            // =========================
            $layananDetails = DB::table('pembayaran_detail as pd')
                ->join('layanan as l', 'pd.layanan_id', '=', 'l.id')
                ->where('pd.pembayaran_id', $pembayaran->id)
                ->whereNotNull('pd.layanan_id')
                ->select(
                    'pd.id as pembayaran_detail_id',
                    'l.id as layanan_id',
                    'l.nama_layanan',
                    'pd.harga',
                    'pd.qty as jumlah',
                    'pd.subtotal',
                    'pd.diskon_tipe',
                    'pd.diskon_nilai',
                    'pd.total_setelah_diskon'
                )
                ->get();

            foreach ($layananDetails as $item) {
                $before = (float) ($item->subtotal ?? 0);
                $after = (float) ($item->total_setelah_diskon ?? $before);
                $disc = max($before - $after, 0);

                $responseData['layanan'][] = [
                    'pembayaran_detail_id' => $item->pembayaran_detail_id,
                    'id' => $item->layanan_id,
                    'nama_layanan' => $item->nama_layanan,
                    'harga_layanan' => (float) ($item->harga ?? 0),
                    'jumlah' => (int) ($item->jumlah ?? 1),
                    'subtotal' => $before,
                    'diskon_tipe' => $item->diskon_tipe,
                    'diskon_nilai' => (float) ($item->diskon_nilai ?? 0), // persen
                    'diskon_nominal' => $disc,
                    'total_setelah_diskon' => $after,
                ];

                $responseData['total_layanan_before'] += $before;
                $responseData['total_layanan_discount'] += $disc;
                $responseData['total_layanan_after'] += $after;
            }

            // =========================
            // OBAT
            // =========================
            $obatDetails = DB::table('pembayaran_detail as pd')
                ->join('resep_obat as ro', 'pd.resep_obat_id', '=', 'ro.id')
                ->join('obat as o', 'ro.obat_id', '=', 'o.id')
                ->where('pd.pembayaran_id', $pembayaran->id)
                ->whereNotNull('pd.resep_obat_id')
                ->select(
                    'pd.id as pembayaran_detail_id',
                    'o.id as obat_id',
                    'o.nama_obat',
                    'ro.dosis',
                    'ro.keterangan',
                    'pd.harga',
                    'pd.qty as jumlah',
                    'pd.subtotal',
                    'pd.diskon_tipe',
                    'pd.diskon_nilai',
                    'pd.total_setelah_diskon'
                )
                ->get();

            foreach ($obatDetails as $item) {
                $before = (float) ($item->subtotal ?? 0);
                $after = (float) ($item->total_setelah_diskon ?? $before);
                $disc = max($before - $after, 0);

                $responseData['resep_obat'][] = [
                    'pembayaran_detail_id' => $item->pembayaran_detail_id,
                    'obat' => [
                        'id' => $item->obat_id,
                        'nama_obat' => $item->nama_obat,
                        'harga_obat' => (float) ($item->harga ?? 0),
                    ],
                    'jumlah' => (int) ($item->jumlah ?? 1),
                    'dosis' => $item->dosis,
                    'keterangan' => $item->keterangan ?? 'Sesuai anjuran dokter',
                    'subtotal' => $before,
                    'diskon_tipe' => $item->diskon_tipe,
                    'diskon_nilai' => (float) ($item->diskon_nilai ?? 0), // persen
                    'diskon_nominal' => $disc,
                    'total_setelah_diskon' => $after,
                ];

                $responseData['total_obat_before'] += $before;
                $responseData['total_obat_discount'] += $disc;
                $responseData['total_obat_after'] += $after;
            }

            // =========================
            // LAB
            // =========================
            $labDetails = DB::table('pembayaran_detail as pd')
                ->join('order_lab_detail as old', 'pd.order_lab_detail_id', '=', 'old.id')
                ->join('jenis_pemeriksaan_lab as jpl', 'old.jenis_pemeriksaan_lab_id', '=', 'jpl.id')
                ->where('pd.pembayaran_id', $pembayaran->id)
                ->whereNotNull('pd.order_lab_detail_id')
                ->select(
                    'pd.id as pembayaran_detail_id',
                    'jpl.id as jenis_pemeriksaan_lab_id',
                    'jpl.nama_pemeriksaan',
                    'jpl.kode_pemeriksaan',
                    'pd.harga',
                    'pd.qty as jumlah',
                    'pd.subtotal',
                    'pd.diskon_tipe',
                    'pd.diskon_nilai',
                    'pd.total_setelah_diskon'
                )
                ->get();

            foreach ($labDetails as $item) {
                $before = (float) ($item->subtotal ?? 0);
                $after = (float) ($item->total_setelah_diskon ?? $before);
                $disc = max($before - $after, 0);

                $responseData['lab'][] = [
                    'pembayaran_detail_id' => $item->pembayaran_detail_id,
                    'id' => $item->jenis_pemeriksaan_lab_id,
                    'nama_pemeriksaan' => $item->nama_pemeriksaan,
                    'kode_pemeriksaan' => $item->kode_pemeriksaan,
                    'harga' => (float) ($item->harga ?? 0),
                    'jumlah' => (int) ($item->jumlah ?? 1),
                    'subtotal' => $before,
                    'diskon_tipe' => $item->diskon_tipe,
                    'diskon_nilai' => (float) ($item->diskon_nilai ?? 0), // persen
                    'diskon_nominal' => $disc,
                    'total_setelah_diskon' => $after,
                ];

                $responseData['total_lab_before'] += $before;
                $responseData['total_lab_discount'] += $disc;
                $responseData['total_lab_after'] += $after;
            }

            // =========================
            // RADIOLOGI
            // =========================
            $radiologiDetails = DB::table('pembayaran_detail as pd')
                ->join('order_radiologi_detail as ord', 'pd.order_radiologi_detail_id', '=', 'ord.id')
                ->join('jenis_pemeriksaan_radiologi as jpr', 'ord.jenis_pemeriksaan_radiologi_id', '=', 'jpr.id')
                ->where('pd.pembayaran_id', $pembayaran->id)
                ->whereNotNull('pd.order_radiologi_detail_id')
                ->select(
                    'pd.id as pembayaran_detail_id',
                    'jpr.id as jenis_pemeriksaan_radiologi_id',
                    'jpr.nama_pemeriksaan',
                    'jpr.kode_pemeriksaan',
                    'pd.harga',
                    'pd.qty as jumlah',
                    'pd.subtotal',
                    'pd.diskon_tipe',
                    'pd.diskon_nilai',
                    'pd.total_setelah_diskon'
                )
                ->get();

            foreach ($radiologiDetails as $item) {
                $before = (float) ($item->subtotal ?? 0);
                $after = (float) ($item->total_setelah_diskon ?? $before);
                $disc = max($before - $after, 0);

                $responseData['radiologi'][] = [
                    'pembayaran_detail_id' => $item->pembayaran_detail_id,
                    'id' => $item->jenis_pemeriksaan_radiologi_id,
                    'nama_pemeriksaan' => $item->nama_pemeriksaan,
                    'kode_pemeriksaan' => $item->kode_pemeriksaan,
                    'harga' => (float) ($item->harga ?? 0),
                    'jumlah' => (int) ($item->jumlah ?? 1),
                    'subtotal' => $before,
                    'diskon_tipe' => $item->diskon_tipe,
                    'diskon_nilai' => (float) ($item->diskon_nilai ?? 0), // persen
                    'diskon_nominal' => $disc,
                    'total_setelah_diskon' => $after,
                ];

                $responseData['total_radiologi_before'] += $before;
                $responseData['total_radiologi_discount'] += $disc;
                $responseData['total_radiologi_after'] += $after;
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail pembayaran berhasil diambil',
                'data' => $responseData,
            ], 200);
        } catch (\Exception $e) {
            Log::error('❌ Error getting detail pembayaran: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail pembayaran: '.$e->getMessage(),
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

            if (! $userId) {
                Log::warning('notifyPasienFromKunjungan: user_id pasien tidak ditemukan. kunjungan_id='.$kunjungan->id);

                return;
            }

            $payload = array_merge([
                'type' => 'kunjungan_status',
                'kunjungan_id' => $kunjungan->id,
                'status' => $kunjungan->status ?? null,
                'nomor_antrian' => $kunjungan->no_antrian ?? null,
            ], $extra);

            $this->createNotification($userId, $title, $body, $payload);
        } catch (\Throwable $e) {
            Log::warning('notifyPasienFromKunjungan error: '.$e->getMessage());
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
        if (! $tanggalLahir) {
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

            if (! $pembayaran) {
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
            Log::error('Cash payment error: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran: '.$e->getMessage(),
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
                'emr.resep.obat',
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
            Log::error('Error getting pending cash payments: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pembayaran pending: '.$e->getMessage(),
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
                'message' => 'Gagal memperbarui status resep obat: '.$e->getMessage(),
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
            $notif = new \Midtrans\Notification;
            $transaction = $notif->transaction_status;
            $order_id = $notif->order_id;

            $dataPembayaran = Pembayaran::firstOrFail($request->id);

            if ($transaction == 'settlement') {
                $dataPembayaran->update([
                    'status' => 'Sudah Bayar',
                ]);
            }

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('Notification handler error: '.$e->getMessage());

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

        Log::info('✅ Payment status updated successfully');
    }

    public function getRiwayatPembelianObatPasien(Request $request)
    {
        try {
            $user = $request->user();
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

            /**
             * Ambil resep + obat berdasarkan pasien
             */
            $rows = DB::table('resep as r')
                ->join('emr as e', 'r.emr_id', '=', 'e.id')
                ->join('kunjungan as k', 'e.kunjungan_id', '=', 'k.id')
                ->join('resep_obat as ro', 'ro.resep_id', '=', 'r.id')
                ->join('obat as o', 'ro.obat_id', '=', 'o.id')
                ->leftJoin('dokter as d', 'e.dokter_id', '=', 'd.id')
                ->leftJoin('pembayaran as p', 'p.emr_id', '=', 'e.id')
                ->where('k.pasien_id', $pasien->id)
                ->orderByDesc('r.created_at')
                ->select(
                    'r.id as resep_id',
                    'r.status as status_resep',

                    'k.id as kunjungan_id',
                    'k.tanggal_kunjungan',

                    'd.nama_dokter',

                    'o.id as obat_id',
                    'o.nama_obat',
                    'o.kode_obat',
                    'o.kandungan_obat',

                    'ro.dosis',
                    'ro.jumlah',
                    'ro.keterangan',
                    'ro.status as status_obat',

                    'o.harga_jual_obat as harga_satuan',
                    DB::raw('(o.harga_jual_obat * ro.jumlah) as subtotal'),

                    'p.status as status_pembayaran',
                    'p.kode_transaksi',
                    'r.created_at'
                )
                ->get();

            /**
             * Group per resep
             */
            $grouped = collect($rows)->groupBy('resep_id')->map(function ($items) {
                $first = $items->first();

                return [
                    'resep_id' => (int) $first->resep_id,
                    'tanggal' => optional($first->created_at)->format('Y-m-d H:i'),
                    'status_resep' => $first->status_resep ?? 'waiting',

                    'kunjungan' => [
                        'id' => (int) $first->kunjungan_id,
                        'tanggal_kunjungan' => $first->tanggal_kunjungan,
                    ],

                    'dokter' => [
                        'nama_dokter' => $first->nama_dokter ?? '-',
                    ],

                    'pembayaran' => [
                        'status' => $first->status_pembayaran ?? 'Belum Bayar',
                        'kode_transaksi' => $first->kode_transaksi,
                    ],

                    'total_obat' => $items->sum('subtotal'),

                    'items' => $items->map(function ($i) {
                        return [
                            'obat_id' => (int) $i->obat_id,
                            'nama_obat' => $i->nama_obat,
                            'kode_obat' => $i->kode_obat,
                            'kandungan_obat' => $i->kandungan_obat,
                            'dosis' => $i->dosis,
                            'jumlah' => (int) $i->jumlah,
                            'harga_satuan' => (float) $i->harga_satuan,
                            'subtotal' => (float) $i->subtotal,
                            'keterangan' => $i->keterangan,
                            'status_obat' => $i->status_obat ?? 'waiting',
                        ];
                    })->values(),
                ];
            })->values();

            return response()->json([
                'success' => true,
                'message' => 'Riwayat pembelian obat berhasil diambil',
                'data' => $grouped,
                'total' => $grouped->count(),
            ], 200);

        } catch (\Throwable $e) {
            Log::error('ERROR getRiwayatPembelianObatPasien: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat pembelian obat',
                'error' => $e->getMessage(),
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

    /**
     * Get all dokter with poli and jadwal data
     */

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
            $payload = 'PAS-'.strtoupper(uniqid());
            $pasien->qr_code_pasien = $payload;
            $pasien->save();
            Log::info('Auto-set qr_code_pasien', ['pasien_id' => $pasien->id, 'qr' => $payload]);
        }
    }

    // obat
    public function getDaftarObat()
    {
        try {
            $obat = Obat::where('jumlah', '>', 0)
                ->orderBy('nama_obat', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data obat berhasil diambil',
                'data' => $obat->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'nama_obat' => $item->nama_obat,
                        'jumlah' => $item->jumlah,
                        'dosis' => $item->dosis,
                        'harga_jual_obat' => $item->harga_jual_obat,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ];
                }),
                'total' => $obat->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting daftar obat: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data obat: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getAllObat()
    {
        try {
            $obat = Obat::orderBy('nama_obat', 'asc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Semua data obat berhasil diambil',
                'data' => $obat->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'nama_obat' => $item->nama_obat,
                        'jumlah' => $item->jumlah,
                        'dosis' => $item->dosis,
                        'harga_jual_obat' => $item->harga_jual_obat,
                        'stock_status' => $item->jumlah > 0 ? 'Available' : 'Out of Stock',
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ];
                }),
                'total' => $obat->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting all obat: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil semua data obat: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store penjualan obat transactions
     */
    public function storePenjualanObat(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'transaksi_data' => 'required|array',
                'transaksi_data.*.pasien_id' => 'required|integer|exists:pasien,id',
                'transaksi_data.*.obat_id' => 'required|integer|exists:obat,id',
                'transaksi_data.*.kode_transaksi' => 'required|string|max:255',
                'transaksi_data.*.jumlah' => 'required|integer|min:1',
                'transaksi_data.*.sub_total' => 'required|numeric|min:0',
            ], [
                'transaksi_data.required' => 'Data transaksi tidak boleh kosong.',
                'transaksi_data.array' => 'Data transaksi harus berupa array.',
                'transaksi_data.*.pasien_id.required' => 'ID pasien tidak boleh kosong.',
                'transaksi_data.*.pasien_id.exists' => 'Pasien tidak ditemukan.',
                'transaksi_data.*.obat_id.required' => 'ID obat tidak boleh kosong.',
                'transaksi_data.*.obat_id.exists' => 'Obat tidak ditemukan.',
                'transaksi_data.*.kode_transaksi.required' => 'Kode transaksi tidak boleh kosong.',
                'transaksi_data.*.jumlah.required' => 'Jumlah obat tidak boleh kosong.',
                'transaksi_data.*.jumlah.min' => 'Jumlah obat minimal 1.',
                'transaksi_data.*.sub_total.required' => 'Sub total tidak boleh kosong.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return DB::transaction(function () use ($request) {
                $transaksiData = $request->input('transaksi_data');
                $createdTransactions = [];
                $kodeTransaksi = $transaksiData[0]['kode_transaksi'];

                // Hitung total tagihan
                $totalTagihan = 0;
                foreach ($transaksiData as $data) {
                    $totalTagihan += $data['sub_total'];
                }

                Log::info('Starting penjualan obat transaction', [
                    'kode_transaksi' => $kodeTransaksi,
                    'total_items' => count($transaksiData),
                    'total_tagihan' => $totalTagihan,
                ]);

                foreach ($transaksiData as $index => $data) {
                    // Check obat stock
                    $obat = Obat::findOrFail($data['obat_id']);

                    if ($obat->jumlah < $data['jumlah']) {
                        throw new \Exception("Stok obat {$obat->nama_obat} tidak mencukupi. Stok tersedia: {$obat->jumlah}, diminta: {$data['jumlah']}");
                    }

                    // Verify pasien exists
                    $pasien = Pasien::findOrFail($data['pasien_id']);

                    // Create penjualan obat record - MINIMAL DATA
                    $penjualanObat = PenjualanObat::create([
                        'pasien_id' => $data['pasien_id'],
                        'obat_id' => $data['obat_id'],
                        'kode_transaksi' => $data['kode_transaksi'],
                        'jumlah' => $data['jumlah'],
                        'sub_total' => $data['sub_total'],
                        'total_tagihan' => $totalTagihan, // Sama untuk semua item
                        'tanggal_transaksi' => now(),
                        'status' => 'Belum Bayar', // Default status
                        // TIDAK ADA: metode_pembayaran_id, uang_yang_diterima, kembalian, bukti_pembayaran
                    ]);

                    // JANGAN update stok dulu - tunggu admin konfirmasi pembayaran

                    Log::info('Penjualan obat created (waiting admin confirmation)', [
                        'penjualan_id' => $penjualanObat->id,
                        'obat_nama' => $obat->nama_obat,
                        'jumlah' => $data['jumlah'],
                    ]);

                    $createdTransactions[] = [
                        'id' => $penjualanObat->id,
                        'obat_nama' => $obat->nama_obat,
                        'jumlah' => $data['jumlah'],
                        'sub_total' => $data['sub_total'],
                    ];
                }

                Log::info('Penjualan obat transaction created successfully', [
                    'kode_transaksi' => $kodeTransaksi,
                    'total_items' => count($createdTransactions),
                    'total_tagihan' => $totalTagihan,
                    'status' => 'Menunggu konfirmasi admin',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Pesanan berhasil dikirim. Silakan menuju kasir untuk pembayaran.',
                    'data' => [
                        'kode_transaksi' => $kodeTransaksi,
                        'total_items' => count($createdTransactions),
                        'total_tagihan' => $totalTagihan,
                        'status' => 'Belum Bayar',
                        'tanggal_transaksi' => now()->toDateTimeString(),
                        'items' => $createdTransactions,
                        'message_for_user' => 'Pesanan Anda telah diterima. Silakan menuju kasir untuk melakukan pembayaran.',
                    ],
                ], 201);
            });
        } catch (\Exception $e) {
            Log::error('Error storing penjualan obat: '.$e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pesanan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get penjualan obat history for a specific pasien
     */
    public function getRiwayatPembelian(Request $request, $pasienId)
    {
        try {
            $penjualan = PenjualanObat::with(['obat', 'pasien', 'metodePembayaran'])
                ->where('pasien_id', $pasienId)
                ->orderBy('tanggal_transaksi', 'desc')
                ->get();

            $groupedByTransaction = $penjualan->groupBy('kode_transaksi');

            $riwayat = $groupedByTransaction->map(function ($items, $kodeTransaksi) {
                $firstItem = $items->first();

                return [
                    'kode_transaksi' => $kodeTransaksi,
                    'tanggal_transaksi' => $firstItem->tanggal_transaksi,
                    'total_tagihan' => $firstItem->total_tagihan,
                    'uang_yang_diterima' => $firstItem->uang_yang_diterima,
                    'kembalian' => $firstItem->kembalian,
                    'metode_pembayaran' => $firstItem->metodePembayaran?->nama_metode ?? 'Tidak diketahui',
                    'status' => $firstItem->status,
                    'items' => $items->map(function ($item) {
                        return [
                            'nama_obat' => $item->obat->nama_obat,
                            'jumlah' => $item->jumlah,
                            'sub_total' => $item->sub_total,
                            'dosis' => $item->obat->dosis,
                        ];
                    })->toArray(),
                    'total_items' => $items->count(),
                ];
            })->values();

            return response()->json([
                'success' => true,
                'message' => 'Riwayat pembelian obat berhasil diambil',
                'data' => $riwayat,
                'total_transactions' => $riwayat->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting riwayat pembelian: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat pembelian: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get specific transaction details
     */
    public function getDetailTransaksi($kodeTransaksi)
    {
        try {
            // ✅ PERBAIKAN: Gunakan penjualan_obat dengan detail
            $penjualan = \App\Models\PenjualanObat::with([
                'pasien:id,nama_pasien',
                'metodePembayaran:id,nama_metode',
                'details.obat:id,nama_obat,dosis,harga_jual_obat', // ✅ Relasi baru
            ])
                ->where('kode_transaksi', $kodeTransaksi)
                ->first();

            if (! $penjualan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan',
                ], 404);
            }

            $transaksiDetail = [
                'kode_transaksi' => $kodeTransaksi,
                'pasien' => [
                    'id' => $penjualan->pasien->id,
                    'nama_pasien' => $penjualan->pasien->nama_pasien,
                ],
                'tanggal_transaksi' => $penjualan->tanggal_transaksi,

                // ✅ DISKON INFO
                'total_tagihan' => (float) $penjualan->total_tagihan,
                'diskon_tipe' => $penjualan->diskon_tipe,
                'diskon_nilai' => (float) ($penjualan->diskon_nilai ?? 0),
                'total_setelah_diskon' => (float) ($penjualan->total_setelah_diskon ?? $penjualan->total_tagihan),

                'uang_yang_diterima' => (float) ($penjualan->uang_yang_diterima ?? 0),
                'kembalian' => (float) ($penjualan->kembalian ?? 0),
                'metode_pembayaran' => $penjualan->metodePembayaran?->nama_metode ?? 'Tidak diketahui',
                'status' => $penjualan->status,

                'items' => $penjualan->details->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'nama_obat' => $detail->obat->nama_obat,
                        'dosis' => $detail->obat->dosis,
                        'jumlah' => $detail->jumlah,
                        'harga_satuan' => (float) $detail->harga_satuan,
                        'sub_total' => (float) $detail->sub_total,
                    ];
                })->toArray(),

                'total_items' => $penjualan->details->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Detail transaksi berhasil diambil',
                'data' => $transaksiDetail,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting detail transaksi: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail transaksi: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get sales summary/statistics
     */
    public function getSalesSummary(Request $request)
    {
        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth());

            $sales = PenjualanObat::with(['obat'])
                ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
                ->where('status', 'Sudah Bayar')
                ->get();

            $totalRevenue = $sales->sum('sub_total');
            $totalTransactions = $sales->groupBy('kode_transaksi')->count();
            $totalItemsSold = $sales->sum('jumlah');

            // Top selling medicines
            $topMedicines = $sales->groupBy('obat_id')
                ->map(function ($items, $obatId) {
                    $firstItem = $items->first();

                    return [
                        'obat_id' => $obatId,
                        'nama_obat' => $firstItem->obat->nama_obat,
                        'total_sold' => $items->sum('jumlah'),
                        'total_revenue' => $items->sum('sub_total'),
                    ];
                })
                ->sortByDesc('total_sold')
                ->take(10)
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Ringkasan penjualan berhasil diambil',
                'data' => [
                    'period' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ],
                    'summary' => [
                        'total_revenue' => $totalRevenue,
                        'total_transactions' => $totalTransactions,
                        'total_items_sold' => $totalItemsSold,
                        'average_transaction_value' => $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0,
                    ],
                    'top_medicines' => $topMedicines,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting sales summary: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil ringkasan penjualan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update stock for a specific medicine (for admin/inventory management)
     */
    public function updateStokObat(Request $request, $obatId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'jumlah' => 'required|integer|min:0',
                'action' => 'required|string|in:add,subtract,set',
            ], [
                'jumlah.required' => 'Jumlah tidak boleh kosong.',
                'jumlah.integer' => 'Jumlah harus berupa angka.',
                'jumlah.min' => 'Jumlah tidak boleh negatif.',
                'action.required' => 'Aksi tidak boleh kosong.',
                'action.in' => 'Aksi harus berupa add, subtract, atau set.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $obat = Obat::findOrFail($obatId);
            $jumlah = $request->input('jumlah');
            $action = $request->input('action');
            $oldStock = $obat->jumlah;

            switch ($action) {
                case 'add':
                    $obat->jumlah += $jumlah;
                    break;
                case 'subtract':
                    if ($obat->jumlah < $jumlah) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Stok tidak mencukupi untuk dikurangi',
                        ], 400);
                    }
                    $obat->jumlah -= $jumlah;
                    break;
                case 'set':
                    $obat->jumlah = $jumlah;
                    break;
            }

            $obat->save();

            Log::info('Stock updated for medicine', [
                'obat_id' => $obatId,
                'nama_obat' => $obat->nama_obat,
                'old_stock' => $oldStock,
                'new_stock' => $obat->jumlah,
                'action' => $action,
                'amount' => $jumlah,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stok obat berhasil diperbarui',
                'data' => [
                    'obat' => [
                        'id' => $obat->id,
                        'nama_obat' => $obat->nama_obat,
                        'old_stock' => $oldStock,
                        'new_stock' => $obat->jumlah,
                        'difference' => $obat->jumlah - $oldStock,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating stock: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui stok obat: '.$e->getMessage(),
            ], 500);
        }
    }
}
