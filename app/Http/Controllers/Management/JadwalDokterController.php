<?php

namespace App\Http\Controllers\Management;

use App\Models\Dokter;
use App\Models\JadwalDokter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Models\DokterPoli;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class JadwalDokterController extends Controller
{

    public function createJadwalDokter(Request $request)
    {
        // 1) Normalisasi jam: terima "HH:MM" atau "HH:MM:SS"
        $jamAwalRaw    = trim((string)$request->jam_awal);
        $jamSelesaiRaw = trim((string)$request->jam_selesai);

        $jamAwal    = strlen($jamAwalRaw) === 5 ? $jamAwalRaw . ':00' : $jamAwalRaw;
        $jamSelesai = strlen($jamSelesaiRaw) === 5 ? $jamSelesaiRaw . ':00' : $jamSelesaiRaw;

        // 2) Validasi input dasar
        $validated = $request->validate([
            'dokter_id'   => ['required', 'integer', 'exists:dokter,id'],
            'poli_id'     => ['required', 'integer', 'exists:poli,id'],
            'hari'        => ['required', 'string', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])],
            'jam_awal'    => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'jam_selesai' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
        ], [
            'jam_awal.regex'    => 'Format jam_awal harus HH:MM atau HH:MM:SS',
            'jam_selesai.regex' => 'Format jam_selesai harus HH:MM atau HH:MM:SS',
        ]);

        $dokterId = (int) $validated['dokter_id'];
        $poliId   = (int) $validated['poli_id'];
        $hari     = $validated['hari'];
        $start    = $jamAwal;
        $end      = $jamSelesai;

        // 3) Validasi urutan jam
        if ($end <= $start) {
            return response()->json([
                'message' => 'Gagal menambahkan jadwal.',
                'errors'  => ['jam_selesai' => ['Jam selesai harus lebih besar dari jam mulai.']]
            ], 422);
        }

        return DB::transaction(function () use ($dokterId, $poliId, $hari, $start, $end) {
            // 4) Pastikan pasangan ada di pivot
            $dp = DokterPoli::firstOrCreate(
                ['dokter_id' => $dokterId, 'poli_id' => $poliId],
                [] // timestamps auto
            );

            // 5) Cek bentrok pada hari yang sama - pasangan yang sama
            $isOverlapping = JadwalDokter::where('dokter_poli_id', $dp->id)
                ->where('hari', $hari)
                ->where(function ($q) use ($start, $end) {
                    // overlap if existing.start < newEnd AND existing.end > newStart
                    $q->where('jam_awal', '<', $end)
                        ->where('jam_selesai', '>', $start);
                })
                ->exists();

            if ($isOverlapping) {
                return response()->json([
                    'message' => 'Gagal menambahkan jadwal.',
                    'errors'  => [
                        'jam_awal' => ["Jadwal bertabrakan pada hari {$hari}. Ubah jam atau pilih slot lain."]
                    ]
                ], 422);
            }

            try {
                // 6) Simpan — isi **tiga kolom** agar kompatibel dengan schema lama
                $jadwal = JadwalDokter::create([
                    'dokter_poli_id' => $dp->id,
                    'dokter_id'      => $dokterId,   // ⬅️ penting
                    'poli_id'        => $poliId,     // ⬅️ penting
                    'hari'           => $hari,
                    'jam_awal'       => $start,
                    'jam_selesai'    => $end,
                ]);

                return response()->json([
                    'message' => 'Sesi jadwal berhasil ditambahkan.',
                    'data'    => $jadwal
                ], 201);
            } catch (QueryException $e) {
                // Tangkap pelanggaran UNIQUE (kalau ada uniq_jd_slot_dokterpoli)
                if ((int)$e->getCode() === 23000) {
                    return response()->json([
                        'message' => 'Gagal menambahkan jadwal.',
                        'errors'  => [
                            'jam_awal' => ['Slot jadwal ini sudah ada untuk dokter & poli tersebut.']
                        ]
                    ], 422);
                }
                throw $e;
            }
        });
    }

    public function getJadwalDokterById($id)
    {
        $jadwal = JadwalDokter::with('dokter', 'poli')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $jadwal
        ]);
    }

    public function updateJadwalDokter(Request $request, string $id)
    {
        $jadwal = JadwalDokter::findOrFail($id);

        // Normalisasi jam: terima HH:MM atau HH:MM:SS
        $jamAwalRaw    = trim((string)$request->input('jam_awal', $jadwal->jam_awal));
        $jamSelesaiRaw = trim((string)$request->input('jam_selesai', $jadwal->jam_selesai));
        $jamAwalNorm    = strlen($jamAwalRaw)    === 5 ? $jamAwalRaw    . ':00' : $jamAwalRaw;
        $jamSelesaiNorm = strlen($jamSelesaiRaw) === 5 ? $jamSelesaiRaw . ':00' : $jamSelesaiRaw;

        // Validasi
        $validated = $request->validate([
            'dokter_id'   => ['required', 'integer', 'exists:dokter,id'],
            'poli_id'     => ['required', 'integer', 'exists:poli,id'],
            'hari'        => ['required', 'string', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])],
            'jam_awal'    => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'jam_selesai' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
        ], [
            'jam_awal.regex'    => 'Format jam_awal harus HH:MM atau HH:MM:SS',
            'jam_selesai.regex' => 'Format jam_selesai harus HH:MM atau HH:MM:SS',
        ]);

        $dokterId = (int) $validated['dokter_id'];
        $poliId   = (int) $validated['poli_id'];
        $hari     = $validated['hari'];
        $start    = $jamAwalNorm;
        $end      = $jamSelesaiNorm;

        if ($end <= $start) {
            return response()->json([
                'message' => 'Gagal memperbarui jadwal.',
                'errors'  => ['jam_selesai' => ['Jam selesai harus lebih besar dari jam mulai.']]
            ], 422);
        }

        return DB::transaction(function () use ($jadwal, $dokterId, $poliId, $hari, $start, $end) {
            // pastikan pasangan ada di pivot
            $dp = DokterPoli::firstOrCreate(['dokter_id' => $dokterId, 'poli_id' => $poliId], []);

            // cek tabrakan berdasarkan dokter_poli_id + hari (exclude current id)
            $isOverlapping = JadwalDokter::where('dokter_poli_id', $dp->id)
                ->where('hari', $hari)
                ->where('id', '!=', $jadwal->id)
                ->where(function ($q) use ($start, $end) {
                    $q->where('jam_awal', '<', $end)
                        ->where('jam_selesai', '>', $start);
                })
                ->exists();

            if ($isOverlapping) {
                return response()->json([
                    'message' => 'Gagal memperbarui jadwal.',
                    'errors'  => ['jam_awal' => ["Jadwal bertabrakan pada hari {$hari}. Ubah jam atau pilih slot lain."]]
                ], 422);
            }

            // update — isi tiga kolom agar kompatibel
            $jadwal->update([
                'dokter_poli_id' => $dp->id,
                'dokter_id'      => $dokterId,
                'poli_id'        => $poliId,
                'hari'           => $hari,
                'jam_awal'       => $start,
                'jam_selesai'    => $end,
            ]);

            return response()->json([
                'message' => 'Sesi jadwal berhasil diperbarui.',
                'data'    => $jadwal->fresh(['dokter:id,nama_dokter', 'poli:id,nama_poli'])
            ]);
        });
    }

    public function deleteJadwalDokter($id)
    {
        $dataJadwalDokter = JadwalDokter::findOrFail($id);
        $dataJadwalDokter->delete();
        return response()->json([
            'success' => true,
            'data' => $dataJadwalDokter,
            'message' => 'Jadwal dokter berhasil dihapus.',
        ]);
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->get('query', ''));

        // Optional: minimal 2 karakter biar hemat query
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        // Ambil dokter + semua poli-nya (hanya kolom perlu)
        $dokters = Dokter::query()
            ->with(['poli:id,nama_poli'])     // relasi many-to-many ->poli()
            ->where('nama_dokter', 'like', "%{$q}%")
            ->orderBy('nama_dokter')
            ->limit(25)
            ->get(['id', 'nama_dokter']);

        // Flatten: setiap (dokter, poli) jadi satu item.
        // Dokter tanpa poli TIDAK dikembalikan (karena create jadwal butuh poli).
        $data = $dokters->flatMap(function ($d) {
            if ($d->poli->isEmpty()) {
                return []; // skip dokter tanpa poli
            }
            return $d->poli->map(function ($p) use ($d) {
                return [
                    'id'           => $d->id,              // (dipakai JS sebagai dokter.id)
                    'nama_dokter'  => $d->nama_dokter,
                    'poli_id'      => $p->id,              // (dipakai JS sebagai poli_id)
                    'nama_poli'    => $p->nama_poli,
                ];
            });
        })->values();

        return response()->json($data);
    }
}
