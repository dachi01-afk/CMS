<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\Testimoni;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
                'Minggu' => 0,
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
        $today = new \DateTime;
        $currentDayOfWeek = (int) $today->format('w'); // 0=Minggu, 1=Senin, dst

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
        $today = new \DateTime;
        $target = new \DateTime($targetDate);
        $diff = $today->diff($target);

        return $diff->days;
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

    public function batalkanStatusKunjungan(Request $request)
    {
        try {
            // Log untuk debugging
            Log::info('=== BATALKAN KUNJUNGAN START ===');
            Log::info('Request method: ' . $request->method());
            Log::info('Request data: ', $request->all());

            // Validasi input - cek apakah 'id' ada dalam request
            $request->validate([
                'id' => 'required|integer|exists:kunjungan,id',
            ]);

            $kunjunganId = $request->input('id');
            Log::info('Processing kunjungan ID: ' . $kunjunganId);

            // Cari data kunjungan
            $dataKunjungan = Kunjungan::findOrFail($kunjunganId);
            Log::info('Found kunjungan before update: ', $dataKunjungan->toArray());

            // Cek apakah status masih bisa dibatalkan
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

                // Ambil data yang sudah diupdate
                return Kunjungan::find($kunjunganId);
            });

            Log::info('Updated kunjungan after transaction: ', $updatedKunjungan->toArray());

            // Verifikasi bahwa update berhasil
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
            Log::error('Error getting riwayat kunjungan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat kunjungan: ' . $e->getMessage(),
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
            Log::error('Error getting testimoni: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data testimoni: ' . $e->getMessage(),
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
                $namaFoto = time() . '_' . $foto->getClientOriginalName();
                $jalurFoto = $foto->storeAs('Foto-Testimoni', $namaFoto, 'public');
            }

            // Upload video jika ada
            if ($request->hasFile('video')) {
                $video = $request->file('video');
                $namaVideo = time() . '_' . $video->getClientOriginalName();
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
            Log::error('Error creating testimoni: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat testimoni: ' . $e->getMessage(),
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
            Log::error('Error getting data dokter: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dokter: ' . $e->getMessage(),
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
        $login = Auth::user()->id;

        $dataDokter = Dokter::with('user')->where('user_id', $login)->get();

        $request->validate([
            'user_id' => ['required', 'exists:user,id'],
            'nama_dokter' => ['required'],
            'foto_dokter' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'deskripsi_dokter' => ['required'],
            'pengalaman' => ['required'],
            'jenis_spesialis_id' => ['required', 'exists:userjenis_spesialis,id'],
            'no_hp' => ['required'],
        ]);

        $pathFotoDokter = null;

        if ($request->hasFile('foto_dokter')) {
            $fileFoto = $request->file('foto_dokter');
            $namaFoto = $request->nama_dokter . '_' . $fileFoto->getClientOriginalName();
            $pathFotoDokter = $fileFoto->storeAs('Foto-Testimoni', $namaFoto, 'public');

            $dataDokter->update([
                'user_id' => $request->user_id,
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
                'Data Dokter' => $dataDokter,
                'message' => 'Berhasil Mengupdate Data Dokter Beserta Foto Dokter'
            ]);
        }

        $dataDokter->update([
            'user_id' => $request->user_id,
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
            'Data Dokter' => $dataDokter,
            'message' => 'Berhasil Mengupdate Data Dokter Tanpa Ikut Mengupdate Data Foto Dokter'
        ]);
    }
}
