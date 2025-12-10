<?php

namespace App\Http\Controllers\Perawat;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\DokterPoli;
use App\Models\JadwalDokter;
use App\Models\Kunjungan;
use App\Models\Perawat;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class PerawatController extends Controller
{

    public function dashboard()
    {
        $tz    = config('app.timezone', 'Asia/Jakarta');
        $today = Carbon::today($tz)->toDateString();

        // Ambil perawat yang sedang login
        $perawat = Perawat::where('user_id', Auth::id())->first();

        // Default nilai
        $statMenungguTriage      = 0;
        $statSedangKonsultasi    = 0;
        $statTotalTriageHariIni  = 0;
        $statSelesaiHariIni      = 0;
        $persenTriageSelesai     = 0;
        $listSiapTriage          = collect();
        $listSedangKonsultasi    = collect();
        $grafikTanggal           = [];
        $grafikJumlah            = [];

        if ($perawat) {
            $perawatId = $perawat->id;

            /* ============================================================
         *  BASE QUERY: Semua kunjungan yang "milik" perawat ini
         *  (sesuai tabel pivot perawat_dokter_poli dan dokter_poli)
         * ============================================================ */
            $baseQuery = Kunjungan::query()
                ->whereDate('tanggal_kunjungan', $today)
                ->whereExists(function ($q) use ($perawatId) {
                    $q->select(DB::raw(1))
                        ->from('perawat_dokter_poli as pdp')
                        ->join('dokter_poli as dp', 'dp.id', '=', 'pdp.dokter_poli_id')
                        // dokter & poli di kunjungan harus sama dengan pasangan di dokter_poli
                        ->whereColumn('dp.dokter_id', 'kunjungan.dokter_id')
                        ->whereColumn('dp.poli_id', 'kunjungan.poli_id')
                        ->where('pdp.perawat_id', $perawatId);
                });

            /* ============================================================
         *  1. Statistik Utama (berdasarkan baseQuery)
         * ============================================================ */

            // Pasien menunggu triage → status = "Waiting"
            $statMenungguTriage = (clone $baseQuery)
                ->where('status', 'Waiting')
                ->count();

            // Pasien sedang konsultasi → status = "Engaged"
            $statSedangKonsultasi = (clone $baseQuery)
                ->where('status', 'Engaged')
                ->count();

            // Total triage hari ini → misal status "Selesai Triage" & "Menunggu Dokter"
            $statTotalTriageHariIni = (clone $baseQuery)
                ->whereIn('status', ['Engaged'])
                ->count();

            // Selesai triage hari ini saja (opsional)
            $statSelesaiHariIni = (clone $baseQuery)
                ->where('status', 'Selesai Triage')
                ->count();

            // Dokter aktif hari ini (tidak perlu filter perawat)
            $dayName        = strtolower(Carbon::today($tz)->locale('id')->dayName); // senin, selasa, ...
            $statDokterAktif = JadwalDokter::where('hari', $dayName)->count();

            /* ============================================================
         *  2. Persentase Triage Selesai
         * ============================================================ */
            $totalKunjunganHariIni = (clone $baseQuery)->count();
            $persenTriageSelesai   = $totalKunjunganHariIni > 0
                ? round(($statTotalTriageHariIni / $totalKunjunganHariIni) * 100)
                : 0;

            /* ============================================================
         *  3. Daftar Siap Triage (Limit 5)
         *     (list yang muncul di menu Kunjungan → status "Waiting")
         * ============================================================ */
            $listSiapTriage = (clone $baseQuery)
                ->with(['pasien', 'poli'])
                ->where('status', 'Waiting')
                ->orderByRaw('CAST(no_antrian AS UNSIGNED)')
                ->limit(5)
                ->get()
                ->map(function ($row) {
                    return (object) [
                        'id'          => $row->id,
                        'no_antrian'  => $row->no_antrian,
                        'nama_pasien' => $row->pasien->nama_pasien ?? '-',
                        'nama_poli'   => $row->poli->nama_poli ?? '-',
                    ];
                });

            /* ============================================================
         *  4. Daftar Pasien dalam Konsultasi
         * ============================================================ */
            $listSedangKonsultasi = (clone $baseQuery)
                ->with(['pasien', 'poli', 'dokter'])
                ->where('status', 'Sedang Konsultasi')
                ->get()
                ->map(function ($row) {
                    return (object) [
                        'nama_pasien' => $row->pasien->nama_pasien ?? '-',
                        'nama_dokter' => $row->dokter->nama_dokter ?? '-',
                        'nama_poli'   => $row->poli->nama_poli ?? '-',
                    ];
                });

            /* ============================================================
         *  5. Grafik Triage 7 Hari Terakhir (juga berdasarkan penugasan perawat)
         * ============================================================ */
            for ($i = 6; $i >= 0; $i--) {
                $tanggal = Carbon::today($tz)->subDays($i)->toDateString();
                $label   = Carbon::today($tz)->subDays($i)->translatedFormat('d M');

                $jumlah = Kunjungan::whereDate('tanggal_kunjungan', $tanggal)
                    ->whereExists(function ($q) use ($perawatId) {
                        $q->select(DB::raw(1))
                            ->from('perawat_dokter_poli as pdp')
                            ->join('dokter_poli as dp', 'dp.id', '=', 'pdp.dokter_poli_id')
                            ->whereColumn('dp.dokter_id', 'kunjungan.dokter_id')
                            ->whereColumn('dp.poli_id', 'kunjungan.poli_id')
                            ->where('pdp.perawat_id', $perawatId);
                    })
                    ->whereIn('status', ['Selesai Triage', 'Menunggu Dokter'])
                    ->count();

                $grafikTanggal[] = $label;
                $grafikJumlah[]  = $jumlah;
            }
        } else {
            // Kalau user belum terdaftar sebagai perawat → tetap isi label grafik dengan 0
            for ($i = 6; $i >= 0; $i--) {
                $grafikTanggal[] = Carbon::today($tz)->subDays($i)->translatedFormat('d M');
                $grafikJumlah[]  = 0;
            }

            $dayName        = strtolower(Carbon::today($tz)->locale('id')->dayName);
            $statDokterAktif = JadwalDokter::where('hari', $dayName)->count();
        }

        /* ============================================================
     *  RETURN TO VIEW
     * ============================================================ */
        return view('perawat.dashboard', [
            // statistik
            'statMenungguTriage'      => $statMenungguTriage,
            'statSedangKonsultasi'    => $statSedangKonsultasi,
            'statTotalTriageHariIni'  => $statTotalTriageHariIni,
            'statDokterAktif'         => $statDokterAktif,
            'persenTriageSelesai'     => $persenTriageSelesai,
            'statSelesaiHariIni'      => $statSelesaiHariIni,

            // list
            'listSiapTriage'          => $listSiapTriage,
            'listSedangKonsultasi'    => $listSedangKonsultasi,

            // grafik
            'grafikTanggal'           => $grafikTanggal,
            'grafikJumlah'            => $grafikJumlah,
        ]);
    }


    public function createPerawat(Request $request)
    {
        try {
            // 🧩 Validasi input
            $validated = $request->validate([
                'foto_perawat'         => 'required|file|mimes:jpeg,jpg,png,gif,webp,svg,jfif|max:5120',
                'username_perawat'     => 'required|string|max:255',
                'nama_perawat'         => 'required|string|max:255',
                'email_perawat'        => 'required|email',
                'no_hp_perawat'        => 'nullable|string|max:20',
                'password_perawat'     => 'required|string|min:8|confirmed',

                // relasi banyak dokter_poli
                'dokter_poli_id'      => 'nullable|array',
                'dokter_poli_id.*'    => 'exists:dokter_poli,id',
            ]);

            DB::beginTransaction();

            // 🧑‍💻 Buat user baru
            $user = User::create([
                'username' => $validated['username_perawat'],
                'email'    => $validated['email_perawat'],
                'password' => Hash::make($validated['password_perawat']),
                'role'     => 'Perawat',
            ]);

            // 📸 Upload + Kompres Foto
            $fotoPath = null;
            if ($request->hasFile('foto_perawat')) {
                $file = $request->file('foto_perawat');

                $extension = strtolower($file->getClientOriginalExtension());
                if ($extension === 'jfif') {
                    $extension = 'jpg';
                }

                $fileName = 'perawat_' . time() . '.' . $extension;
                $path     = 'perawat/' . $fileName;

                if ($extension === 'svg') {
                    Storage::disk('public')->put($path, file_get_contents($file));
                } else {
                    $image = Image::read($file);
                    $image->scale(width: 800);
                    Storage::disk('public')->put(
                        $path,
                        (string) $image->encodeByExtension($extension, quality: 80)
                    );
                }

                $fotoPath = $path;
            }

            // 🏥 Buat data perawat (tanpa dokter_id & poli_id)
            $perawat = Perawat::create([
                'user_id'       => $user->id,
                'nama_perawat'  => $validated['nama_perawat'],
                'foto_perawat'  => $fotoPath,
                'no_hp_perawat' => $validated['no_hp_perawat'] ?? null,
            ]);

            // 🔗 Simpan relasi ke pivot perawat_dokter_poli (boleh banyak)
            if (!empty($validated['dokter_poli_id'])) {
                $perawat->perawatDokterPoli()->attach($validated['dokter_poli_id']);
            }

            DB::commit();

            return response()->json(['message' => 'Data perawat berhasil ditambahkan.']);
        } catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Ukuran file terlalu besar! Maksimal 5 MB.'], 413);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message'       => 'Tidak ada respon dari server.',
                'error_detail'  => $e->getMessage(),
            ], 500);
        }
    }
    public function getPerawatById($id)
    {
        $data = Perawat::with('user', 'perawatDokterPoli.poli', 'perawatDokterPoli.dokter')->findOrFail($id);
        return response()->json(['data' => $data]);
    }

    public function listPoli(Request $request)
    {
        $q = $request->input('q', '');

        $data = Poli::select('id', 'nama_poli')
            ->when($q, fn($w) => $w->where('nama_poli', 'like', "%{$q}%"))
            ->orderBy('nama_poli')
            ->get();

        return response()->json(['data' => $data]);
    }

    // List dokter berdasarkan poli (ambil dari tabel dokter_poli)
    public function listDokterByPoli(Request $request, $poliId)
    {
        $q = $request->input('q', '');

        $dokterPolis = DokterPoli::with('dokter:id,nama_dokter')
            ->where('poli_id', $poliId)
            ->when($q, function ($w) use ($q) {
                $w->whereHas('dokter', function ($qq) use ($q) {
                    $qq->where('nama_dokter', 'like', "%{$q}%");
                });
            })
            ->get()
            ->sortBy('dokter.nama_dokter')
            ->values();

        $data = $dokterPolis->map(function ($dp) {
            return [
                'dokter_poli_id' => $dp->id,
                'dokter_id'      => $dp->dokter_id,
                'nama_dokter'    => $dp->dokter->nama_dokter ?? 'Tanpa Nama',
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function updatePerawat(Request $request, $id)
    {
        try {
            $perawat = Perawat::with('user')->findOrFail($id);
            $user    = $perawat->user;

            $validated = $request->validate([
                'edit_username_perawat'  => 'required|string|max:255',
                'edit_nama_perawat'      => 'required|string|max:255',
                'edit_email_perawat'     => 'required|email',
                'edit_foto_perawat'      => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg,jfif|max:5120',
                'edit_no_hp_perawat'     => 'nullable|string|max:20',
                'edit_password_perawat'  => 'nullable|string|min:8|confirmed',

                // multi penugasan
                'dokter_poli_id'        => 'nullable|array',
                'dokter_poli_id.*'      => 'integer|exists:dokter_poli,id',
            ]);

            $dokterPoliIds = $request->input('dokter_poli_id', []);

            DB::beginTransaction();

            // --- update user ---
            $user->username = $validated['edit_username_perawat'];
            $user->email    = $validated['edit_email_perawat'];

            if (!empty($validated['edit_password_perawat'])) {
                $user->password = Hash::make($validated['edit_password_perawat']);
            }
            $user->save();

            // --- handle foto ---
            $fotoPath = null;
            if ($request->hasFile('edit_foto_perawat')) {
                $file = $request->file('edit_foto_perawat');

                $extension = strtolower($file->getClientOriginalExtension());
                if ($extension === 'jfif') $extension = 'jpg';

                $fileName = 'perawat_' . time() . '.' . $extension;
                $path     = 'perawat/' . $fileName;

                if ($extension === 'svg') {
                    Storage::disk('public')->put($path, file_get_contents($file));
                } else {
                    $image = Image::read($file);
                    $image->scale(width: 800);
                    Storage::disk('public')->put($path, (string) $image->encodeByExtension($extension, quality: 80));
                }

                if ($perawat->foto_perawat && Storage::disk('public')->exists($perawat->foto_perawat)) {
                    Storage::disk('public')->delete($perawat->foto_perawat);
                }

                $fotoPath = $path;
            }

            // --- update perawat ---
            $updateData = [
                'nama_perawat'  => $validated['edit_nama_perawat'],
                'no_hp_perawat' => $validated['edit_no_hp_perawat'] ?? $perawat->no_hp_perawat,
            ];
            if ($fotoPath) {
                $updateData['foto_perawat'] = $fotoPath;
            }

            $perawat->update($updateData);

            // --- sync pivot penugasan ---
            $perawat->perawatDokterPoli()->sync($dokterPoliIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data perawat berhasil diperbarui.',
            ]);
        } catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
            return response()->json([
                'message' => 'Ukuran file terlalu besar! Maksimal 5 MB.'
            ], 413);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message'      => 'Tidak ada respon dari server.',
                'error_detail' => $e->getMessage(),
            ], 500);
        }
    }



    public function deletePerawat($id)
    {
        try {
            $perawat = Perawat::with('user')->findOrFail($id);

            DB::beginTransaction();

            // Hapus foto jika ada
            if ($perawat->foto_perawat && Storage::disk('public')->exists($perawat->foto_perawat)) {
                Storage::disk('public')->delete($perawat->foto_perawat);
            }

            // Jika FK user_id sudah cascadeOnDelete di migrasi perawat,
            // cukup hapus $perawat saja. Tapi untuk pasti, kita hapus berurutan:
            if ($perawat->user) {
                $perawat->user->delete(); // hapus akun user
            }
            $perawat->delete(); // hapus record perawat

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data perawat berhasil dihapus.'
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            // FK constraint (contoh: perawat dipakai di tabel lain)
            if ((int) ($e->errorInfo[1] ?? 0) === 1451) { // MySQL: Cannot delete or update a parent row: a foreign key constraint fails
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus perawat karena masih terkait dengan data lain (kunjungan/EMR/…).
Silakan hapus/lepaskan keterkaitannya terlebih dahulu.'
                ], 409);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus perawat.',
                'error_detail' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
}
