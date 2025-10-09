<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Kunjungan;
use Illuminate\Support\Facades\Auth;
use App\Models\Pasien;
use App\Models\Testimoni;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class APIController extends Controller
{
    /** LOGIN */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
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
    }

    /** REGISTER */
    public function register(Request $request)
    {
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

        // otomatis buat data pasien baru dengan hanya user_id
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
    }

    public function getProfile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        $pasien = $user->pasien;

        if (!$pasien) {
            return response()->json([
                'success' => false,
                'message' => 'Data pasien tidak ditemukan'
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
                'created_at' => $user->created_at,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role,
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $pasien = Pasien::where('user_id', $user->id)->first();
            if (!$pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pasien tidak ditemukan',
                ], 404);
            }

            // Validasi input
            $validated = $request->validate([
                'nama_pasien' => 'required|string|max:255',
                'alamat' => 'nullable|string|max:255',
                'tanggal_lahir' => 'nullable|date',
                'jenis_kelamin' => 'nullable|string|in:Laki-laki,Perempuan',
            ]);

            // Update data pasien
            $pasien->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => $pasien,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getJadwalDokter(Request $request)
    {
        try {
            $jadwal = \App\Models\JadwalDokter::with('dokter')->get();

            // Mapping hari ke angka (untuk PHP date function)
            $hariMapping = [
                'Senin' => 1,
                'Selasa' => 2, 
                'Rabu' => 3,
                'Kamis' => 4,
                'Jumat' => 5,
                'Sabtu' => 6,
                'Minggu' => 0
            ];

            // Tambahkan tanggal terdekat untuk setiap jadwal
            $jadwalWithDates = $jadwal->map(function ($item) use ($hariMapping) {
                $hari = $item->hari;
                $hariNumber = $hariMapping[$hari] ?? null;
                
                if ($hariNumber !== null) {
                    // Hitung tanggal terdekat untuk hari tersebut
                    $tanggalTerdekat = $this->getNextDateByDay($hariNumber);
                    $item->tanggal_terdekat = $tanggalTerdekat;
                    $item->tanggal_terdekat_formatted = $this->formatTanggalIndonesia($tanggalTerdekat);
                    $item->hari_selisih = $this->getDayDifference($tanggalTerdekat);
                } else {
                    $item->tanggal_terdekat = null;
                    $item->tanggal_terdekat_formatted = null;
                    $item->hari_selisih = 999; // Untuk sorting terakhir
                }
                
                return $item;
            });

            // Urutkan berdasarkan tanggal terdekat
            $jadwalSorted = $jadwalWithDates->sortBy('hari_selisih')->values();

            return response()->json([
                'success' => true,
                'data' => $jadwalSorted,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting jadwal dokter: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil jadwal dokter: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hitung tanggal terdekat berdasarkan hari dalam minggu
     */
    private function getNextDateByDay($dayOfWeek)
    {
        $today = new \DateTime();
        $currentDayOfWeek = (int)$today->format('w'); // 0=Minggu, 1=Senin, dst
        
        // Hitung selisih hari
        $daysUntilTarget = ($dayOfWeek - $currentDayOfWeek + 7) % 7;
        
        // Jika hari ini sama dengan target, ambil hari ini
        if ($daysUntilTarget === 0) {
            return $today->format('Y-m-d');
        }
        
        // Tambahkan hari ke tanggal sekarang
        $targetDate = clone $today;
        $targetDate->add(new \DateInterval("P{$daysUntilTarget}D"));
        
        return $targetDate->format('Y-m-d');
    }

    /**
     * Format tanggal ke bahasa Indonesia
     */
    private function formatTanggalIndonesia($date)
    {
        $bulanIndonesia = [
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
            '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags',
            '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'
        ];
        
        $dateObj = new \DateTime($date);
        $hari = $dateObj->format('j');
        $bulan = $bulanIndonesia[$dateObj->format('m')];
        $tahun = $dateObj->format('Y');
        
        return "{$hari} {$bulan} {$tahun}";
    }

    /**
     * Hitung selisih hari dari sekarang
     */
    private function getDayDifference($targetDate)
    {
        $today = new \DateTime();
        $target = new \DateTime($targetDate);
        $diff = $today->diff($target);
        
        return $diff->days;
    }

    public function ubahStatusKunjungan(Request $request)
    {
        $dataKunjungan = Kunjungan::findOrFail($request->id);

        if ($dataKunjungan->status === 'Pending') {
            $dataKunjungan->update([
                'status' => 'Waiting'
            ]);

            return response()->json([
                'success' => true,
                'status' => 200,
                'Data Kunjungan' => $dataKunjungan,
                'message' => 'Berhasil Merubah Status Kunjungan Dari Pending Menjadi Waiting'
            ]);
        } elseif ($dataKunjungan->status === 'Waiting') {
            $dataKunjungan->update([
                'status' => 'Engaged'
            ]);

            return response()->json([
                'success' => true,
                'status' => 200,
                'Data Kunjungan' => $dataKunjungan,
                'message' => 'Berhasil Merubah Status Kunjungan Dari Waiting Menjadi Engaged'
            ]);
        }

        return response()->json([
            'success' => false,
            'status' => 404,
            'message' => 'Error',
            'Data Kunjungan' => $dataKunjungan,
        ]);
    }

    public function batalkanStatusKunjungan(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:kunjungan,id',
                'action' => 'nullable|string|in:cancel,delete' // cancel = soft delete, delete = hard delete
            ]);

            $dataKunjungan = Kunjungan::findOrFail($request->id);
            $action = $request->action ?? 'cancel'; // default ke cancel

            // Cek apakah kunjungan bisa dibatalkan/dihapus
            if (!in_array($dataKunjungan->status, ['Pending', 'Confirmed', 'Waiting'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kunjungan dengan status "' . $dataKunjungan->status . '" tidak dapat dibatalkan',
                ], 422);
            }

            if ($action === 'delete') {
                // HARD DELETE - Hapus dari database
                Log::info("🗑️ Hard deleting kunjungan ID: {$request->id}");
                
                // Hapus data terkait dulu (jika ada)
                // Misalnya: resep, tes lab, konsul, EMR
                $dataKunjungan->resep()->delete();
                $dataKunjungan->tesLab()->delete();
                $dataKunjungan->konsul()->delete();
                $dataKunjungan->emr()->delete();
                
                // Hapus kunjungan
                $dataKunjungan->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Kunjungan berhasil dihapus permanen',
                ], 200);

            } else {
                // SOFT DELETE - Ubah status jadi Canceled
                Log::info("❌ Canceling kunjungan ID: {$request->id}");
                
                $dataKunjungan->update([
                    'status' => 'Canceled',
                ]);

                return response()->json([
                    'success' => true,
                    'status' => 200,
                    'Data Kunjungan' => $dataKunjungan,
                    'message' => 'Berhasil membatalkan kunjungan',
                ], 200);
            }

        } catch (\Exception $e) {
            Log::error('Error in batalkanStatusKunjungan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan kunjungan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // 🔥 METHOD YANG DIPANGGIL DARI /api/kunjungan/create
    public function bookingDokter(Request $request)
    {
        try {
            // 🟢 Log untuk debugging
            Log::info('🔥 bookingDokter called with data: ', $request->all());

            $request->validate([
                'pasien_id' => ['required', 'exists:pasien,id'],
                'dokter_id' => ['required', 'exists:dokter,id'],
                'tanggal_kunjungan' => ['required', 'date'],
                'keluhan_awal' => ['required', 'string'],
            ]);

            $tanggalKunjungan = $request->tanggal_kunjungan;
            $dokterId = $request->dokter_id;
            $pasienId = $request->pasien_id;

            Log::info("🎯 Processing booking for pasien_id: $pasienId, dokter_id: $dokterId, tanggal: $tanggalKunjungan");

            // 🚫 VALIDASI: Cek apakah pasien sudah pernah booking dokter yang sama di hari yang sama
            $existingBooking = Kunjungan::where('pasien_id', $pasienId)
                ->where('dokter_id', $dokterId)
                ->where('tanggal_kunjungan', $tanggalKunjungan)
                ->whereIn('status', ['Pending', 'Confirmed', 'Waiting', 'Engaged']) // tidak termasuk yang sudah selesai/dibatalkan
                ->first();

            if ($existingBooking) {
                Log::info("❌ Duplicate booking found for pasien_id: $pasienId, dokter_id: $dokterId, tanggal: $tanggalKunjungan");
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah memiliki jadwal dengan dokter ini pada tanggal yang sama. Silakan pilih dokter lain atau tanggal lain.',
                ], 422);
            }

            // Gunakan transaksi supaya aman dari race condition
            $result = DB::transaction(function () use ($tanggalKunjungan, $dokterId, $pasienId, $request) {
                
                // 🎯 LOGIC BENAR: Cari antrian terakhir berdasarkan DOKTER + TANGGAL
                $lastKunjungan = Kunjungan::where('tanggal_kunjungan', $tanggalKunjungan)
                    ->where('dokter_id', $dokterId)
                    ->orderByDesc('no_antrian')
                    ->lockForUpdate()
                    ->first();

                Log::info('🔍 Last kunjungan found: ', $lastKunjungan ? $lastKunjungan->toArray() : ['none']);

                // Tentukan nomor antrian berikutnya
                if ($lastKunjungan && $lastKunjungan->no_antrian) {
                    $nextNumber = (int)$lastKunjungan->no_antrian + 1;
                    Log::info("📈 Next number calculated from existing: $nextNumber");
                } else {
                    $nextNumber = 1;
                    Log::info("🆕 Starting fresh with number: $nextNumber");
                }

                // Format jadi 3 digit: 001, 002, 010, 123
                $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
                Log::info("🎫 Formatted number: $formattedNumber");

                // Data yang akan disimpan
                $dataToCreate = [
                    'pasien_id' => $pasienId,
                    'dokter_id' => $dokterId,
                    'tanggal_kunjungan' => $tanggalKunjungan,
                    'no_antrian' => $formattedNumber,
                    'keluhan_awal' => $request->keluhan_awal,
                    'status' => 'Pending',
                ];

                Log::info('💾 Data to create: ', $dataToCreate);

                // Simpan data kunjungan baru
                $kunjungan = new Kunjungan();
                $kunjungan->pasien_id = $pasienId;
                $kunjungan->dokter_id = $dokterId;
                $kunjungan->tanggal_kunjungan = $tanggalKunjungan;
                $kunjungan->no_antrian = $formattedNumber;  // 🔥 Manual assign
                $kunjungan->keluhan_awal = $request->keluhan_awal;
                $kunjungan->status = 'Pending';
                $kunjungan->save();

                Log::info('✅ Kunjungan created: ', $kunjungan->toArray());

                return [
                    'kunjungan' => $kunjungan,
                    'no_antrian' => $formattedNumber
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Kunjungan berhasil dibuat',
                'Data Kunjungan' => $result['kunjungan'],
                'Data No Antrian' => $result['no_antrian'],
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Validation error: ', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('❌ Exception in bookingDokter: ' . $e->getMessage());
            Log::error('❌ Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat kunjungan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Method untuk mengambil riwayat kunjungan pasien
    public function getRiwayatKunjungan($pasienId)
    {
        try {
            // Validasi apakah pasien ada
            $pasien = Pasien::find($pasienId);
            if (!$pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pasien tidak ditemukan',
                ], 404);
            }

            // Ambil semua kunjungan pasien dengan relasi dokter
            $riwayat = Kunjungan::where('pasien_id', $pasienId)
                ->with(['dokter' => function($query) {
                    $query->select('id', 'nama_dokter', 'email', 'no_hp', 'pengalaman', 'foto');
                }])
                ->orderByDesc('tanggal_kunjungan')
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Riwayat kunjungan berhasil diambil',
                'data' => $riwayat,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting riwayat kunjungan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat kunjungan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getDataTestimoni() {
        $dataTestimoni = Testimoni::all();

        return response()->json([
            'success' => true,
            'status' => 200,
            'Data Testimoni' => $dataTestimoni,
            'message' => 'Berhasil Meminta Data Testimoni',
        ]);
    }
 
    
}