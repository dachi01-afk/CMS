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

class APIMobileController extends Controller
{
    /** LOGIN */
    public function login(Request $request)
    {
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

            // Validasi input
            $validated = $request->validate([
                'nama_pasien' => 'required|string|max:255',
                'alamat' => 'nullable|string|max:255',
                'tanggal_lahir' => 'nullable|date',
                'jenis_kelamin' => 'nullable|string|in:Laki-laki,Perempuan',
                'foto_pasien' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // 🔥 TAMBAH VALIDASI FOTO
            ]);

            // Handle foto upload
            $pathFotoPasien = $pasien->foto_pasien; // Keep existing photo if no new upload

            if ($request->hasFile('foto_pasien')) {
                // Delete old photo if exists
                if ($pasien->foto_pasien && Storage::disk('public')->exists($pasien->foto_pasien)) {
                    Storage::disk('public')->delete($pasien->foto_pasien);
                }

                // Upload new photo
                $fileFoto = $request->file('foto_pasien');
                $namaFoto = 'pasien_'.$user->id.'_'.time().'.'.$fileFoto->getClientOriginalExtension();
                $pathFotoPasien = $fileFoto->storeAs('Foto-Pasien', $namaFoto, 'public');
            }

            // Update data pasien
            $pasien->update([
                'nama_pasien' => $validated['nama_pasien'],
                'alamat' => $validated['alamat'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'foto_pasien' => $pathFotoPasien, // 🔥 UPDATE FOTO
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => $pasien->fresh(), // Return fresh data
            ]);
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
            $todayNumber = $today->dayOfWeek; // 0 = Minggu, 6 = Sabtu

            $jadwalWithDates = $jadwal->map(function ($item) use ($hariMapping, $today) {
                $hari = $item->hari;
                $hariNumber = $hariMapping[$hari] ?? null;

                if ($hariNumber !== null) {
                    // ambil next date sebagai Carbon (dari helper)
                    $tanggalTerdekat = $this->getNextDateByDay($hariNumber, $today);

                    // Pastikan jika target masih < hari ini (mis. karena perbedaan waktu) -> lompat satu minggu
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
            \Log::error('Error getting jadwal dokter: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil jadwal dokter: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Kembalikan Carbon instance tanggal terdekat untuk $dayOfWeek,
     * relatif terhadap $from (jika diberikan) atau sekarang (dengan timezone app).
     */
    private function getNextDateByDay(int $dayOfWeek, ?Carbon $from = null): Carbon
    {
        $tz = config('app.timezone') ?: 'Asia/Jakarta';

        $from = $from ? $from->copy()->startOfDay() : Carbon::now($tz)->startOfDay();
        $daysUntilTarget = ($dayOfWeek - $from->dayOfWeek + 7) % 7;

        // 🔥 kalau hasilnya 0 (berarti hari ini), loncat ke minggu depan
        if ($daysUntilTarget === 0) {
            $daysUntilTarget = 7;
        }

        $target = $from->copy()->addDays($daysUntilTarget);

        return $target;
    }

    /** Format tanggal (terima Carbon atau string) */
    private function formatTanggalIndonesia($date)
    {
        if (! $date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        $bulanIndonesia = [
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
            '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags',
            '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',
        ];

        $hari = $date->format('j');
        $bulan = $bulanIndonesia[$date->format('m')];
        $tahun = $date->format('Y');

        return "{$hari} {$bulan} {$tahun}";
    }

    /** Hitung selisih hari dari sekarang (pakai timezone app) */
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
    }

    public function getDokterBySpesialisasi($spesialisasiId)
    {
        try {
            // Validasi apakah spesialisasi ada
            $spesialisasi = DB::table('jenis_spesialis')
                ->where('id', $spesialisasiId)
                ->first();

            if (! $spesialisasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Spesialisasi tidak ditemukan',
                ], 404);
            }

            // Ambil dokter berdasarkan spesialisasi dengan jadwal mereka
            $dokterList = Dokter::with([
                'jenisSpesialis',
                'jadwalDokter' => function ($query) {
                    $query->orderBy('hari');
                },
            ])
                ->where('jenis_spesialis_id', $spesialisasiId)
                ->get();

            // Mapping hari ke angka untuk perhitungan tanggal
            $hariMapping = [
                'Senin' => 1,
                'Selasa' => 2,
                'Rabu' => 3,
                'Kamis' => 4,
                'Jumat' => 5,
                'Sabtu' => 6,
                'Minggu' => 0,
            ];

            // Tambahkan tanggal terdekat untuk setiap jadwal dokter
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

                // Urutkan jadwal berdasarkan tanggal terdekat
                $dokter->jadwalDokter = $jadwalWithDates->sortBy('hari_selisih')->values();

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

    public function batalkanStatusKunjungan(Request $request)
    {
        try {
            // Log untuk debugging
            Log::info('=== BATALKAN KUNJUNGAN START ===');
            Log::info('Request method: '.$request->method());
            Log::info('Request data: ', $request->all());

            // Validasi input - cek apakah 'id' ada dalam request
            $request->validate([
                'id' => 'required|integer|exists:kunjungan,id',
            ]);

            $kunjunganId = $request->input('id');
            Log::info('Processing kunjungan ID: '.$kunjunganId);

            // Cari data kunjungan
            $dataKunjungan = Kunjungan::findOrFail($kunjunganId);
            Log::info('Found kunjungan before update: ', $dataKunjungan->toArray());

            // Cek apakah status masih bisa dibatalkan
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

                // Ambil data yang sudah diupdate
                return Kunjungan::find($kunjunganId);
            });

            Log::info('Updated kunjungan after transaction: ', $updatedKunjungan->toArray());

            // Verifikasi bahwa update berhasil
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
                    $nextNumber = (int) $lastKunjungan->no_antrian + 1;
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
                $kunjungan = new Kunjungan;
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
                    'no_antrian' => $formattedNumber,
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
            Log::error('❌ Exception in bookingDokter: '.$e->getMessage());
            Log::error('❌ Stack trace: '.$e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat kunjungan: '.$e->getMessage(),
            ], 500);
        }
    }

    // Method untuk mengambil riwayat kunjungan pasien
    public function getRiwayatKunjungan($pasienId)
    {
        try {
            // Validasi apakah pasien ada
            $pasien = Pasien::find($pasienId);
            if (! $pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pasien tidak ditemukan',
                ], 404);
            }

            // Ambil semua kunjungan pasien dengan relasi dokter
            $riwayat = Kunjungan::where('pasien_id', $pasienId)
                ->with(['dokter' => function ($query) {
                    $query->select('id', 'nama_dokter', 'no_hp', 'pengalaman', 'foto_dokter');
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
            Log::error('Error getting riwayat kunjungan: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat kunjungan: '.$e->getMessage(),
            ], 500);
        }
    }

    // /////////// Function untuk Testimoni //////////////////

    public function getDataTestimoni()
    {
        try {
            $dataTestimoni = Testimoni::all();

            return response()->json([
                'success' => true,
                'status' => 200,
                'Data Testimoni' => $dataTestimoni,
                'message' => 'Berhasil Meminta Data Testimoni',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error getting testimoni: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data testimoni: '.$e->getMessage(),
            ], 500);
        }
    }

    // 🔥 PERBAIKAN: Typo function name dari createDataTestimomi → createDataTestimoni
    public function createDataTestimoni(Request $request)
    {
        try {
            $request->validate([
                'pasien_id' => ['required', 'exists:pasien,id'],
                'nama_testimoni' => ['required', 'string', 'max:255'],
                'umur' => ['required', 'numeric', 'min:1', 'max:150'],
                'pekerjaan' => ['required', 'string', 'max:255'],
                'isi_testimoni' => ['required', 'string'],
                'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
                'video' => ['nullable', 'mimetypes:video/mp4,video/avi,video/mpeg'], // max 50MB
            ]);

            $jalurFoto = null;
            $jalurVideo = null;

            // Upload foto jika ada
            if ($request->hasFile('foto')) {
                $foto = $request->file('foto');
                $namaFoto = time().'_'.$foto->getClientOriginalName();
                $jalurFoto = $foto->storeAs('Foto-Testimoni', $namaFoto, 'public');
            }

            // Upload video jika ada
            if ($request->hasFile('video')) {
                $video = $request->file('video');
                $namaVideo = time().'_'.$video->getClientOriginalName();
                $jalurVideo = $video->storeAs('Video-Testimoni', $namaVideo, 'public');
            }

            $dataTestimoni = Testimoni::create([
                'pasien_id' => $request->pasien_id,
                'nama_testimoni' => $request->nama_testimoni,
                'umur' => $request->umur,
                'pekerjaan' => $request->pekerjaan,
                'isi_testimoni' => $request->isi_testimoni,
                'foto' => $jalurFoto,
                'link_video' => $jalurVideo,
            ]);

            return response()->json([
                'success' => true,
                'status' => 200,
                'Data Testimoni' => $dataTestimoni,
                'message' => 'Berhasil Membuat Testimoni',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating testimoni: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat testimoni: '.$e->getMessage(),
            ], 500);
        }
    }

    // //////////// Get Data Dokter ////////////
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
            Log::error('Error getting data dokter: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dokter: '.$e->getMessage(),
            ], 500);
        }
    }

    public function loginDokter(Request $request)
    {
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

        // 🧩 Tambahkan pengecekan role dokter
        if ($user->role !== 'Dokter') { // atau cek role_id jika pakai ID
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
    }

    public function updateDataDokter(Request $request)
    {
        // Ambil user_id dari token yang sudah diautentikasi
        $login = Auth::user()->id;

        $dataDokter = Dokter::with('user')->where('user_id', $login)->first();

        if (! $dataDokter) {
            return response()->json([
                'success' => false,
                'message' => 'Data dokter tidak ditemukan',
            ], 404);
        }

        $request->validate([
            // Hapus user_id dari validation karena kita ambil dari Auth
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
            'user_id' => $login, // ini pid Gunakan user_id dari Auth
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
    }

    public function getDataKunjunganBerdasarkanIdDokter()
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

            // Hanya ambil kunjungan dengan status Engaged
            $dataKunjungan = Kunjungan::with(['dokter', 'pasien'])
                ->where('dokter_id', $dokter->id)
                ->where('status', 'Engaged') // Filter hanya Engaged
                ->orderBy('tanggal_kunjungan', 'desc')
                ->orderBy('no_antrian', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'status' => 200,
                'data' => $dataKunjungan,
                'kunjungan_hari_ini' => $dataKunjungan,
                'dokter_info' => [
                    'id' => $dokter->id,
                    'nama_dokter' => $dokter->nama_dokter,
                    'user_id' => $user_id,
                ],
                'message' => 'Berhasil mengambil data kunjungan dokter',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error getting kunjungan by dokter ID: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kunjungan: '.$e->getMessage(),
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

    public function getDataObat()
    {
        $dataObat = Obat::all();

        return response()->json([
            'success' => true,
            'status' => 200,
            'Data Obat' => $dataObat,
            'message' => 'Berhasil Memunculkan Data Obat',
        ]);
    }

    public function saveEMR(Request $request)
    {
        try {
            $request->validate([
                'kunjungan_id' => 'required|exists:kunjungan,id',
                'keluhan_utama' => 'required|string',
                'riwayat_penyakit_sekarang' => 'nullable|string',
                'riwayat_penyakit_dahulu' => 'nullable|string',
                'riwayat_penyakit_keluarga' => 'nullable|string',
                'tanda_vital' => 'nullable|array',
                'diagnosis' => 'required|string',
                'resep' => 'nullable|array', // [{obat_id, jumlah, dosis, keterangan}]
            ]);

            $user_id = Auth::id();
            $dokter = Dokter::where('user_id', $user_id)->firstOrFail();

            $kunjungan = Kunjungan::where('id', $request->kunjungan_id)
                ->where('dokter_id', $dokter->id)
                ->firstOrFail();

            // VALIDASI: Pastikan status kunjungan adalah 'Engaged'
            if ($kunjungan->status !== 'Engaged') {
                return response()->json([
                    'success' => false,
                    'message' => 'Kunjungan harus dalam status Engaged untuk dapat membuat EMR',
                ], 400);
            }

            $result = DB::transaction(function () use ($request, $kunjungan) {
                $resepId = null;

                // Jika dokter menambahkan obat
                if (! empty($request->resep)) {
                    // Buat data resep terlebih dahulu (tanpa obat)
                    $resep = Resep::create([
                        'kunjungan_id' => $kunjungan->id,
                    ]);
                    $resepId = $resep->id;

                    // Tambahkan obat ke resep_obat (pivot)
                    foreach ($request->resep as $obatResep) {
                        $obat = Obat::findOrFail($obatResep['obat_id']);

                        $resep->obat()->attach($obat->id, [
                            'jumlah' => $obatResep['jumlah'] ?? 1,
                            'dosis' => $obatResep['dosis'] ?? null,
                            'keterangan' => $obatResep['keterangan'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // Buat EMR
                $emr = EMR::create([
                    'kunjungan_id' => $kunjungan->id,
                    'resep_id' => $resepId,
                    'keluhan_utama' => $request->keluhan_utama,
                    'riwayat_penyakit_sekarang' => $request->riwayat_penyakit_sekarang,
                    'riwayat_penyakit_dahulu' => $request->riwayat_penyakit_dahulu,
                    'riwayat_penyakit_keluarga' => $request->riwayat_penyakit_keluarga,
                    'tekanan_darah' => $request->tanda_vital['tekanan_darah'] ?? null,
                    'suhu_tubuh' => ! empty($request->tanda_vital['suhu_tubuh'])
                        ? (float) $request->tanda_vital['suhu_tubuh']
                        : null,
                    'nadi' => ! empty($request->tanda_vital['nadi'])
                        ? (int) $request->tanda_vital['nadi']
                        : null,
                    'pernapasan' => ! empty($request->tanda_vital['pernapasan'])
                        ? (int) $request->tanda_vital['pernapasan']
                        : null,
                    'saturasi_oksigen' => ! empty($request->tanda_vital['saturasi_oksigen'])
                        ? (int) $request->tanda_vital['saturasi_oksigen']
                        : null,
                    'diagnosis' => $request->diagnosis,
                ]);

                // UPDATE STATUS KUNJUNGAN: Dari 'Engaged' ke 'Payment'
                $kunjungan->update([
                    'status' => 'Payment',
                ]);

                Log::info("EMR created successfully. Kunjungan status updated from 'Engaged' to 'Payment'", [
                    'kunjungan_id' => $kunjungan->id,
                    'emr_id' => $emr->id,
                    'dokter_id' => $kunjungan->dokter_id,
                    'pasien_id' => $kunjungan->pasien_id,
                ]);

                // Hitung total tagihan berdasarkan resep
                $totalTagihan = 0;

                // Biaya konsultasi dasar (bisa diambil dari setting atau tabel tarif)
                $biayaKonsultasi = 100000; // atau ambil dari database/config
                $totalTagihan += $biayaKonsultasi;

                if (! empty($resepId)) {
                    $resep = Resep::with('obat')->find($resepId);
                    foreach ($resep->obat as $obat) {
                        $totalTagihan += $obat->total_harga * $obat->pivot->jumlah;
                    }
                }

                // Buat data pembayaran - SESUAIKAN DENGAN STRUKTUR TABEL
                $pembayaran = Pembayaran::create([
                    'emr_id' => $emr->id,
                    'total_tagihan' => $totalTagihan,
                    'uang_yang_diterima' => 0, // ← GANTI dari jumlah_bayar ke uang_yang_diterima
                    'kembalian' => 0,
                    'metode_pembayaran' => 'Cash', // ← WAJIB diisi, tidak boleh null
                    'tanggal_pembayaran' => now(), // ← WAJIB diisi, tidak boleh null
                    'status' => 'Belum Bayar', // ← GANTI dari 'Pending' ke 'Belum Bayar'
                ]);

                return [
                    'emr' => $emr,
                    'resep' => $resep ?? null,
                    'kunjungan' => $kunjungan->fresh(),
                    'pembayaran' => $pembayaran,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'EMR berhasil disimpan dan status kunjungan diubah ke Payment.',
                'data' => [
                    'emr' => $result['emr'],
                    'resep' => $result['resep'],
                    'kunjungan' => $result['kunjungan'],
                    'pembayaran' => $result['pembayaran'],
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error saving EMR: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan EMR: '.$e->getMessage(),
            ], 500);
        }
    }

    // tampilkan dia riwayat pasien yg diperiksa dokter
    // Ganti method getRiwayatPasienDiperiksa() di APIMobileController.php

    // Perbaiki method getRiwayatPasienDiperiksa() di APIMobileController.php

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

            // 🔥 PERBAIKAN: Specify kolom dengan nama tabel untuk menghindari ambiguous column
            $riwayatPasien = Kunjungan::with([
                'pasien' => function ($query) {
                    $query->select('id', 'nama_pasien', 'alamat', 'tanggal_lahir', 'jenis_kelamin', 'foto_pasien');
                },
                'emr' => function ($query) {
                    $query->select('id', 'kunjungan_id', 'keluhan_utama', 'diagnosis', 'created_at');
                },
                'resep.obat' => function ($query) {
                    // 🔥 PERBAIKAN: Specify tabel untuk kolom id
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
            Log::error('Error getting riwayat pasien diperiksa: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat pasien: '.$e->getMessage(),
            ], 500);
        }
    }

    // Juga perbaiki method getDetailRiwayatPasien()
    public function getDetailRiwayatPasien($kunjunganId)
    {
        try {
            $user_id = Auth::user()->id;
            $dokter = Dokter::where('user_id', $user_id)->firstOrFail();

            $detailRiwayat = Kunjungan::with([
                'pasien',
                'emr',
                'resep.obat' => function ($query) {
                    // 🔥 TAMBAHKAN withPivot untuk keterangan
                    $query->select('obat.id', 'obat.nama_obat', 'obat.dosis')
                        ->withPivot('jumlah', 'dosis', 'keterangan'); // ← TAMBAH keterangan
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
            Log::error('Error getting detail riwayat pasien: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail riwayat: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send OTP for forgot password
     */
    public function sendForgotPasswordOTP(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:user,email',
            ]);

            $email = $request->email;

            // Generate 6 digit OTP
            $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

            // Store OTP in cache for 5 minutes
            $cacheKey = 'forgot_password_otp_'.$email;
            Cache::put($cacheKey, $otp, now()->addMinutes(5));

            // Send email
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
                        'expires_in' => 5, // minutes
                    ],
                ], 200);

            } catch (\Exception $e) {
                Log::error('Failed to send forgot password email: '.$e->getMessage());

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
            Log::error('Error in sendForgotPasswordOTP: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify OTP and reset password
     */
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

            // Check OTP from cache
            $cacheKey = 'forgot_password_otp_'.$email;
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

            // Update password
            $user = User::where('email', $email)->first();
            $user->update([
                'password' => Hash::make($newPassword),
            ]);

            // Delete OTP from cache
            Cache::forget($cacheKey);

            // Revoke all tokens for security
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
            Log::error('Error in resetPasswordWithOTP: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: '.$e->getMessage(),
            ], 500);
        }
    }

    // Tambahkan method ini ke APIMobileController.php

    /**
     * Send username to email
     */
    public function sendForgotUsername(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:user,email',
            ]);

            $email = $request->email;

            // Find user by email
            $user = User::where('email', $email)->first();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email tidak ditemukan dalam sistem',
                ], 404);
            }

            // Send email with username
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
                Log::error('Failed to send username email: '.$e->getMessage());

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
            Log::error('Error in sendForgotUsername: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: '.$e->getMessage(),
            ], 500);
        }
    }

    // pembayaran

    public function getPembayaranPasien($pasienId)
    {
        try {
            // Validasi apakah pasien ada
            $pasien = Pasien::find($pasienId);
            if (! $pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pasien tidak ditemukan',
                ], 404);
            }

            // Ambil kunjungan dengan status Payment
            $kunjunganPayment = Kunjungan::with([
                'dokter' => function ($query) {
                    $query->select('id', 'nama_dokter', 'no_hp', 'pengalaman', 'foto_dokter');
                },
                'pasien' => function ($query) {
                    $query->select('id', 'nama_pasien', 'alamat', 'tanggal_lahir', 'jenis_kelamin', 'foto_pasien');
                },
                'emr' => function ($query) {
                    $query->select('id', 'kunjungan_id', 'resep_id', 'keluhan_utama', 'diagnosis',
                        'tekanan_darah', 'suhu_tubuh', 'nadi', 'pernapasan', 'saturasi_oksigen');
                },
                'emr.resep.obat' => function ($query) {
                    $query->select('obat.id', 'obat.nama_obat', 'obat.dosis', 'obat.total_harga')
                        ->withPivot('jumlah', 'dosis', 'keterangan', 'status');
                },
                'emr.pembayaran',
            ])
                ->where('pasien_id', $pasienId)
                ->where('status', 'Payment')
                ->orderBy('tanggal_kunjungan', 'desc')
                ->first();

            if (! $kunjunganPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada pembayaran yang menunggu',
                ], 404);
            }

            // Format response data
            $responseData = [
                'kunjungan_id' => $kunjunganPayment->id,
                'pasien' => [
                    'nama_pasien' => $kunjunganPayment->pasien->nama_pasien ?? 'Tidak ada',
                    'umur' => $this->calculateAge($kunjunganPayment->pasien->tanggal_lahir ?? null),
                    'jenis_kelamin' => $kunjunganPayment->pasien->jenis_kelamin ?? 'Tidak ada',
                    'foto_pasien' => $kunjunganPayment->pasien->foto_pasien,
                ],
                'dokter' => [
                    'nama_dokter' => $kunjunganPayment->dokter->nama_dokter ?? 'Tidak ada',
                    'no_hp' => $kunjunganPayment->dokter->no_hp ?? 'Tidak ada',
                    'spesialisasi' => $kunjunganPayment->dokter->jenisSpesialis->nama_spesialis ?? 'Umum',
                ],
                'tanggal_kunjungan' => $kunjunganPayment->tanggal_kunjungan,
                'no_antrian' => $kunjunganPayment->no_antrian,
                'diagnosis' => $kunjunganPayment->emr->diagnosis ?? 'Tidak ada diagnosis',
                'resep_obat' => [],
                'biaya_konsultasi' => 150000.0, // Bisa diambil dari config atau tabel tarif
                'total_obat' => 0.0,
                'total_tagihan' => 0.0,
                'status_pembayaran' => $kunjunganPayment->emr->pembayaran->status ?? 'Belum Bayar',
                'pembayaran_id' => $kunjunganPayment->emr->pembayaran->id ?? null,
            ];

            // Hitung total obat dan format resep
            if ($kunjunganPayment->emr && $kunjunganPayment->emr->resep) {
                $resepObat = [];
                $totalObat = 0;

                foreach ($kunjunganPayment->emr->resep->obat as $obat) {
                    $jumlah = $obat->pivot->jumlah ?? 1;
                    $hargaObat = $obat->total_harga ?? 0;
                    $subtotal = $hargaObat * $jumlah;
                    $totalObat += $subtotal;

                    $resepObat[] = [
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

                $responseData['resep_obat'] = $resepObat;
                $responseData['total_obat'] = $totalObat;
            }

            // Hitung total tagihan
            $responseData['total_tagihan'] = $responseData['biaya_konsultasi'] + $responseData['total_obat'];

            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil diambil',
                'data' => $responseData,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting pembayaran pasien: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pembayaran: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper method to calculate age from birth date
     */
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

    /**
     * Update medication status (for apoteker simulation)
     */
    public function updateStatusObat(Request $request)
    {
        try {
            $request->validate([
                'resep_id' => 'required|exists:resep,id',
                'obat_id' => 'required|exists:obat,id',
                'status' => 'required|in:Belum Diambil,Sudah Diambil',
            ]);

            // Update status di pivot table resep_obat
            DB::table('resep_obat')
                ->where('resep_id', $request->resep_id)
                ->where('obat_id', $request->obat_id)
                ->update([
                    'status' => $request->status,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Status obat berhasil diupdate',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error updating status obat: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status obat: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process payment from patient
     */
    public function prosesPembayaran(Request $request)
    {
        try {
            $request->validate([
                'pembayaran_id' => 'required|exists:pembayaran,id',
                'metode_pembayaran' => 'required|in:Cash,Transfer',
            ]);

            $pembayaran = Pembayaran::with(['emr.kunjungan'])->findOrFail($request->pembayaran_id);

            // Validasi: Cek apakah sudah bayar
            if ($pembayaran->status === 'Sudah Bayar') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran sudah dilakukan sebelumnya',
                ], 400);
            }

            // Validasi: Cek apakah semua obat sudah diambil
            if ($pembayaran->emr->resep_id) {
                $belumDiambil = DB::table('resep_obat')
                    ->where('resep_id', $pembayaran->emr->resep_id)
                    ->where('status', 'Belum Diambil')
                    ->count();

                if ($belumDiambil > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Semua obat harus diambil terlebih dahulu sebelum melakukan pembayaran',
                    ], 400);
                }
            }

            // Update pembayaran
            DB::transaction(function () use ($pembayaran, $request) {
                $pembayaran->update([
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'uang_yang_diterima' => $pembayaran->total_tagihan, // Simulasi: exact amount
                    'kembalian' => 0,
                    'tanggal_pembayaran' => now(),
                    'status' => 'Sudah Bayar',
                ]);

                // Update status kunjungan menjadi Succeed
                $pembayaran->emr->kunjungan->update([
                    'status' => 'Succeed',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diproses',
                'data' => $pembayaran->fresh(),
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error processing pembayaran: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran: '.$e->getMessage(),
            ], 500);
        }
    }
}
