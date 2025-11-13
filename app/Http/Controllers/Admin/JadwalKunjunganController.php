<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Pasien;
use App\Models\Kunjungan;
use App\Models\JadwalDokter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Dokter;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;


class JadwalKunjunganController extends Controller
{
    public function index()
    {
        $tz  = config('app.timezone', 'Asia/Jakarta');
        $now = CarbonImmutable::now($tz); // pakai immutable agar tidak termutasi

        // Nama hari versi Indonesia & English (lowercase), untuk jaga-jaga DB pakai salah satunya
        $hariId = Str::of($now->locale('id')->isoFormat('dddd'))->lower()->toString(); // contoh: "senin"
        $hariEn = Str::of($now->locale('en')->isoFormat('dddd'))->lower()->toString(); // contoh: "monday"

        $tanggalHariIni = $now->toDateString();
        $jamSekarang    = $now->format('H:i:s');

        $mapHari = [
            'senin'  => 'monday',
            'selasa' => 'tuesday',
            'rabu'   => 'wednesday',
            'kamis'  => 'thursday',
            'jumat'  => 'friday',
            'sabtu'  => 'saturday',
            'minggu' => 'sunday',
        ];

        // --- JADWAL HARI INI (yang masih berlangsung / belum lewat) ---
        // Robust: TRIM + LOWER dan whereIn untuk cover ID/EN
        $jadwalHariIni = JadwalDokter::query()
            ->with([
                'dokter:id,nama_dokter,jenis_spesialis_id',
                'dokter.jenisSpesialis:id,nama_spesialis',
                'poli:id,nama_poli',
            ])
            // Cocokkan "hari" secara robust: trim + lower, cover ID & EN
            ->where(function ($q) use ($hariId, $hariEn) {
                $q->whereRaw('LOWER(TRIM(hari)) = ?', [$hariId])
                    ->orWhereRaw('LOWER(TRIM(hari)) = ?', [$hariEn]);
            })
            ->orderBy('jam_awal')
            ->get();

        // --- SEMUA JADWAL + TANGGAL BERIKUTNYA ---
        $jadwalSemua = JadwalDokter::query()
            ->with([
                'dokter:id,nama_dokter,jenis_spesialis_id',
                'dokter.jenisSpesialis:id,nama_spesialis',
                'poli:id,nama_poli',
            ])
            ->get();

        $jadwalYangAkanDatang = $jadwalSemua
            ->map(function ($jd) use ($now, $mapHari) {
                $hariIndo = Str::of($jd->hari ?? '')->trim()->lower()->toString();

                // Normalisasi: kalau DB pakai English, konversi dulu ke Indo
                // (kita tetap pakai $mapHari yang ID->EN; untuk EN->EN langsung pakai nilai EN)
                $hariEng = $mapHari[$hariIndo] ?? $hariIndo; // jika sudah english, biarkan

                if (!$hariEng) {
                    $jd->setAttribute('tanggal_berikutnya', null);
                    return $jd;
                }

                // target hari di minggu ini berdasarkan timezone
                $target = CarbonImmutable::parse("this {$hariEng}", $now->timezone);

                // Jika target < awal hari ini => geser ke minggu depan
                if ($target->lt($now->startOfDay())) {
                    $target = $target->addWeek();
                }

                // Jika target sama dengan hari ini, tapi jam selesai sudah lewat => minggu depan
                if ($target->isSameDay($now)) {
                    try {
                        $jamSelesai = CarbonImmutable::createFromFormat('H:i:s', (string) $jd->jam_selesai, $now->timezone);
                        $jamNow     = CarbonImmutable::createFromFormat('H:i:s', $now->format('H:i:s'), $now->timezone);
                        if ($jamSelesai->lte($jamNow)) {
                            $target = $target->addWeek();
                        }
                    } catch (\Exception $e) {
                        // jika format jam tidak valid, fallback: tetap pakai target minggu ini
                    }
                }

                $jd->setAttribute('tanggal_berikutnya', $target->toDateString());
                return $jd;
            })
            ->filter(fn($jd) => !empty($jd->tanggal_berikutnya))
            ->sortBy([
                fn($a, $b) => strcmp($a->tanggal_berikutnya, $b->tanggal_berikutnya),
                'jam_awal',
            ])
            ->values();

        // Simpan nama hari (rapi) untuk tampilan
        $hariIni = ucfirst($hariId); // "Senin", "Selasa", dst (dari versi Indonesia)

        return view('admin.jadwal_kunjungan', compact(
            'jadwalHariIni',
            'hariIni',
            'jadwalYangAkanDatang',
            'tanggalHariIni'
        ));
    }

    public function search(Request $request)
    {
        $query = $request->get('query');
        $pasien = Pasien::where('nama_pasien', 'LIKE', "%{$query}%")->get(['id', 'nama_pasien', 'alamat', 'jenis_kelamin']);
        return response()->json($pasien);
    }

    public function store(Request $request)
    {
        Log::info('REQ store kunjungan', $request->only([
            'dokter_id',
            'jadwal_id',
            'poli_id',
            'pasien_id',
            'tanggal_kunjungan'
        ]));

        $validated = $request->validate([
            'poli_id'           => 'required|exists:poli,id',
            'pasien_id'         => 'required|exists:pasien,id',
            'tanggal_kunjungan' => 'required|date',
            'keluhan_awal'      => 'required|string',
            'jadwal_id'         => 'nullable|exists:jadwal_dokter,id',
            'dokter_id'         => 'nullable|exists:dokter,id', // ⬅️ pastikan exist
        ]);

        $dokterId = $request->filled('dokter_id') ? (int) $request->input('dokter_id') : null;

        // Jika FE mengirim dokter_id → validasi dokter memang terdaftar di poli tsb (pivot dokter_poli)
        if ($dokterId !== null) {
            $valid = DB::table('dokter_poli')
                ->where('dokter_id', $dokterId)
                ->where('poli_id', $validated['poli_id'])
                ->exists();

            if (!$valid) {
                throw ValidationException::withMessages([
                    'dokter_id' => 'Dokter tidak termasuk ke poli yang dipilih.',
                ]);
            }
        }

        $result = DB::transaction(function () use ($validated, $dokterId) {

            // Anti duplikat 60 detik
            $dupe = Kunjungan::whereDate('tanggal_kunjungan', $validated['tanggal_kunjungan'])
                ->where('poli_id',   $validated['poli_id'])
                ->where('pasien_id', $validated['pasien_id'])
                ->where('keluhan_awal', $validated['keluhan_awal'])
                ->where('created_at', '>=', now()->subSeconds(60))
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($dupe) {
                $dokter = null;
                if ($dupe->dokter_id) {
                    $dokter = DB::table('dokter')
                        ->select('id', 'nama_dokter')
                        ->where('id', $dupe->dokter_id)
                        ->first();
                }

                return [
                    'reuse'     => true,
                    'kunjungan' => $dupe,
                    'dokter'    => $dokter,
                ];
            }

            // Hitung nomor antrian per (tanggal, poli)
            $lastRow = DB::table('kunjungan')
                ->whereDate('tanggal_kunjungan', $validated['tanggal_kunjungan'])
                ->where('poli_id', $validated['poli_id'])
                ->orderByRaw('CAST(no_antrian AS UNSIGNED) DESC')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $lastNumber  = $lastRow ? (int)$lastRow->no_antrian : 0;
            $formattedNo = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

            // Simpan kunjungan baru (tanpa dokter dulu)
            $baru = Kunjungan::create([
                'poli_id'           => $validated['poli_id'],
                'pasien_id'         => $validated['pasien_id'],
                'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
                'no_antrian'        => $formattedNo,
                'keluhan_awal'      => $validated['keluhan_awal'],
                'status'            => 'Pending',
            ]);

            // Tentukan dokter terpilih
            $chosenDokterId = $dokterId;

            if ($chosenDokterId === null) {
                if (!empty($validated['jadwal_id'])) {
                    $row = DB::table('jadwal_dokter')
                        ->select('dokter_id', 'poli_id')
                        ->where('id', $validated['jadwal_id'])
                        ->first();

                    if (!$row || (int)$row->poli_id !== (int)$validated['poli_id']) {
                        throw ValidationException::withMessages([
                            'jadwal_id' => 'Jadwal tidak sesuai dengan poli yang dipilih.',
                        ]);
                    }
                    $chosenDokterId = (int) $row->dokter_id;
                } else {
                    throw ValidationException::withMessages([
                        'dokter_id' => 'Harus memilih dokter atau jadwal dokter.',
                    ]);
                }
            } else {
                // double-check via pivot (race safety)
                $exists = DB::table('dokter_poli')
                    ->where('dokter_id', $chosenDokterId)
                    ->where('poli_id', $validated['poli_id'])
                    ->lockForUpdate()
                    ->exists();

                if (!$exists) {
                    throw ValidationException::withMessages([
                        'dokter_id' => 'Dokter tidak terdaftar pada poli yang dipilih.',
                    ]);
                }
            }

            // ⬇️⬇️ INI YANG KURANG: SIMPAN dokter_id (+ jadwal_dokter_id jika ada)
            $baru->dokter_id = $chosenDokterId;
            if (!empty($validated['jadwal_id'])) {
                $baru->jadwal_dokter_id = (int) $validated['jadwal_id'];
            }
            $baru->save();

            $dokter = DB::table('dokter')
                ->select('id', 'nama_dokter')
                ->where('id', $chosenDokterId)
                ->first();

            return [
                'reuse'     => false,
                'kunjungan' => $baru,
                'dokter'    => $dokter,
            ];
        });

        $msg = $result['reuse']
            ? 'Entry identik baru saja dibuat; mengembalikan kunjungan terakhir (anti duplikat).'
            : 'Data kunjungan berhasil ditambahkan.';

        $kunjunganLoaded = $result['kunjungan']->load('poli', 'pasien');

        return response()->json([
            'success' => true,
            'message' => $msg,
            'data'    => [
                'no_antrian'      => $kunjunganLoaded->no_antrian,
                'kunjungan'       => $kunjunganLoaded,
                'dokter_terpilih' => $result['dokter'],
            ],
        ], $result['reuse'] ? 200 : 201);
    }

    public function waiting()
    {
        $tz         = config('app.timezone', 'Asia/Jakarta');
        $todayLocal = Carbon::today($tz);
        $today      = $todayLocal->toDateString();

        // Ambil KUNJUNGAN "Pending" untuk HARI INI
        $kunjungan = Kunjungan::query()
            ->with([
                'pasien',
                'dokter',
                'poli',
            ])
            ->whereDate('tanggal_kunjungan', $today)
            ->where('status', 'Pending')
            ->orderByRaw('CAST(no_antrian AS UNSIGNED)')
            ->get();

        // Langsung kirim dalam key "data" supaya cocok sama JS-mu
        return response()->json([
            'data' => $kunjungan,
        ]);
    }

    public function updateStatus($id)
    {
        // Cari data kunjungan
        $kunjungan = Kunjungan::findOrFail($id);

        // Validasi hanya bisa ubah dari Pending ke Waiting
        if ($kunjungan->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => "Status saat ini adalah '{$kunjungan->status}', tidak dapat diproses."
            ], 422);
        }

        // Lakukan update atomik dalam transaksi
        DB::transaction(function () use ($kunjungan) {
            $kunjungan->update([
                'status' => 'Waiting',
                'updated_at' => now(), // pastikan update timestamp juga
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Status kunjungan berhasil diperbarui menjadi Waiting.',
            'data'    => [
                'id' => $kunjungan->id,
                'status' => 'Waiting'
            ],
        ]);
    }


    // public function masaDepan()
    // {
    //     $kunjunganMasaDepan = Kunjungan::with(['poli', 'dokter', 'pasien'])
    //         ->where('status', 'Pending')
    //         ->whereDate('tanggal_kunjungan', '>', Carbon::today())
    //         ->orderBy('tanggal_kunjungan', 'asc')
    //         ->orderBy('no_antrian', 'asc')
    //         ->get();

    //     return response()->json($kunjunganMasaDepan);
    // }

    public function masaDepan()
    {
        $besok = Carbon::tomorrow()->toDateString();

        // Ambil kunjungan pending mulai besok
        $kunjunganMasaDepan = Kunjungan::with([
            'poli',
            'pasien',
            'dokter'
        ])
            ->whereRaw("LOWER(TRIM(status)) = ?", ['pending'])
            ->whereDate('tanggal_kunjungan', '>=', $besok)
            ->orderBy('tanggal_kunjungan', 'asc')
            ->orderBy('no_antrian', 'asc')
            ->get();

        return response()->json($kunjunganMasaDepan);
    }


    public function getDataKYAD($id)
    {
        $dataKYAD = Kunjungan::with('pasien', 'poli.dokter')->where('id', $id)->firstOrFail();

        return response()->json([
            'data' => $dataKYAD
        ]);
    }

    public function batalkanKunjungan($id)
    {
        $dataKYAD = Kunjungan::find($id);

        if (!$dataKYAD) {
            return response()->json([
                'success' => false,
                'message' => 'Data kunjungan tidak ditemukan'
            ]);
        }

        $dataKYAD->update([
            'status' => 'Canceled',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil Membatalkan Kunjungan'
        ]);
    }
}
