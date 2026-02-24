<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\DokterPoli;
use App\Models\EMR;
use App\Models\JadwalDokter;
use App\Models\Kunjungan;
use App\Models\Pasien;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class JadwalKunjunganController extends Controller
{
    public function index()
    {
        $tz = config('app.timezone', 'Asia/Jakarta');
        $now = CarbonImmutable::now($tz);

        $hariId = Str::of($now->locale('id')->isoFormat('dddd'))->lower()->toString();
        $hariEn = Str::of($now->locale('en')->isoFormat('dddd'))->lower()->toString();

        $tanggalHariIni = $now->toDateString();
        $jamSekarang = $now->format('H:i:s');

        $mapHari = [
            'senin' => 'monday',
            'selasa' => 'tuesday',
            'rabu' => 'wednesday',
            'kamis' => 'thursday',
            'jumat' => 'friday',
            'sabtu' => 'saturday',
            'minggu' => 'sunday',
        ];

        // ✅ Scope aktifSekarang() sudah benar (menggunakan jam_selesai)
        $jadwalHariIni = JadwalDokter::with(['dokter', 'dokter.jenisSpesialis', 'poli'])
            ->aktifSekarang()
            ->get();

        // --- SEMUA JADWAL + TANGGAL BERIKUTNYA ---
        $jadwalSemua = JadwalDokter::query()
            ->with([
                'dokter:id,nama_dokter,jenis_spesialis_id',
                'dokter.jenisSpesialis:id,nama_spesialis',
                'poli:id,nama_poli',
            ])
            ->get();

        $jadwalYangAkanDatangCollection = $jadwalSemua
            ->map(function ($jd) use ($now, $mapHari) {
                $hariRaw = Str::of($jd->hari ?? '')->trim()->lower()->toString();

                if (array_key_exists($hariRaw, $mapHari)) {
                    $hariEng = $mapHari[$hariRaw];
                } else {
                    $hariEng = $hariRaw;
                }

                if (empty($hariEng)) {
                    $jd->setAttribute('tanggal_berikutnya', null);

                    return $jd;
                }

                try {
                    $target = CarbonImmutable::parse("next {$hariEng}", $now->timezone);
                } catch (\Exception $e) {
                    $jd->setAttribute('tanggal_berikutnya', null);

                    return $jd;
                }

                $jd->setAttribute('tanggal_berikutnya', $target->toDateString());

                return $jd;
            })
            ->filter(fn ($jd) => ! empty($jd->tanggal_berikutnya))
            ->sortBy([
                fn ($a, $b) => strcmp($a->tanggal_berikutnya, $b->tanggal_berikutnya),
                'jam_awal', // ✅ Ini tetap jam_awal, bukan jam_selesai (untuk sorting)
            ])
            ->values();

        // --- PAGINATION ---
        $perPage = (int) request('per_page', 10);
        $currentPage = Paginator::resolveCurrentPage('page');
        $total = $jadwalYangAkanDatangCollection->count();

        $itemsForCurrentPage = $jadwalYangAkanDatangCollection
            ->slice(($currentPage - 1) * $perPage, $perPage)
            ->values();

        $jadwalYangAkanDatang = new LengthAwarePaginator(
            $itemsForCurrentPage,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => Paginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );

        $hariIni = ucfirst($hariId);

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
        $pasien = Pasien::where('nama_pasien', 'LIKE', "%{$query}%")->orWhere('no_emr', 'LIKE', "%{$query}%")->get(['id', 'nama_pasien', 'alamat', 'jenis_kelamin', 'no_emr']);

        return response()->json($pasien);
    }

    public function store(Request $request)
    {
        Log::info('REQ store kunjungan', $request->only([
            'dokter_id',
            'jadwal_id',
            'poli_id',
            'pasien_id',
            'tanggal_kunjungan',
        ]));

        $validated = $request->validate([
            'poli_id' => 'required|exists:poli,id',
            'pasien_id' => 'required|exists:pasien,id',
            'tanggal_kunjungan' => 'required|date',
            'keluhan_awal' => 'required|string',
            'jadwal_id' => 'nullable|exists:jadwal_dokter,id',
            'dokter_id' => 'nullable|exists:dokter,id', // ⬅️ pastikan exist
        ]);

        $dokterId = $request->filled('dokter_id') ? (int) $request->input('dokter_id') : null;

        // Jika FE mengirim dokter_id → validasi dokter memang terdaftar di poli tsb (pivot dokter_poli)
        if ($dokterId !== null) {
            $valid = DB::table('dokter_poli')
                ->where('dokter_id', $dokterId)
                ->where('poli_id', $validated['poli_id'])
                ->exists();

            if (! $valid) {
                throw ValidationException::withMessages([
                    'dokter_id' => 'Dokter tidak termasuk ke poli yang dipilih.',
                ]);
            }
        }

        $result = DB::transaction(function () use ($validated, $dokterId) {

            // Anti duplikat 60 detik
            $dupe = Kunjungan::whereDate('tanggal_kunjungan', $validated['tanggal_kunjungan'])
                ->where('poli_id', $validated['poli_id'])
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
                    'reuse' => true,
                    'kunjungan' => $dupe,
                    'dokter' => $dokter,
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

            $lastNumber = $lastRow ? (int) $lastRow->no_antrian : 0;
            $formattedNo = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

            // Simpan kunjungan baru (tanpa dokter dulu)
            $baru = Kunjungan::create([
                'poli_id' => $validated['poli_id'],
                'pasien_id' => $validated['pasien_id'],
                'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
                'no_antrian' => $formattedNo,
                'keluhan_awal' => $validated['keluhan_awal'],
                'status' => 'Pending',
            ]);

            // Tentukan dokter terpilih
            $chosenDokterId = $dokterId;

            if ($chosenDokterId === null) {
                if (! empty($validated['jadwal_id'])) {
                    $row = DB::table('jadwal_dokter')
                        ->select('dokter_id', 'poli_id')
                        ->where('id', $validated['jadwal_id'])
                        ->first();

                    if (! $row || (int) $row->poli_id !== (int) $validated['poli_id']) {
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

                if (! $exists) {
                    throw ValidationException::withMessages([
                        'dokter_id' => 'Dokter tidak terdaftar pada poli yang dipilih.',
                    ]);
                }
            }

            // ⬇️⬇️ INI YANG KURANG: SIMPAN dokter_id (+ jadwal_dokter_id jika ada)
            $baru->dokter_id = $chosenDokterId;
            if (! empty($validated['jadwal_id'])) {
                $baru->jadwal_dokter_id = (int) $validated['jadwal_id'];
            }
            $baru->save();

            $dokter = DB::table('dokter')
                ->select('id', 'nama_dokter')
                ->where('id', $chosenDokterId)
                ->first();

            return [
                'reuse' => false,
                'kunjungan' => $baru,
                'dokter' => $dokter,
            ];
        });

        $msg = $result['reuse']
            ? 'Entry identik baru saja dibuat; mengembalikan kunjungan terakhir (anti duplikat).'
            : 'Data kunjungan berhasil ditambahkan.';

        $kunjunganLoaded = $result['kunjungan']->load('poli', 'pasien');

        return response()->json([
            'success' => true,
            'message' => $msg,
            'data' => [
                'no_antrian' => $kunjunganLoaded->no_antrian,
                'kunjungan' => $kunjunganLoaded,
                'dokter_terpilih' => $result['dokter'],
            ],
        ], $result['reuse'] ? 200 : 201);
    }

    public function waiting()
    {
        $tz = config('app.timezone', 'Asia/Jakarta');
        $todayLocal = Carbon::today($tz);
        $today = $todayLocal->toDateString();

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

    public function updateDataKunjungan(Request $request, $id)
    {
        // === VALIDASI ===
        $validated = $request->validate(
            [
                'dokter_id' => ['required', 'integer', 'exists:dokter,id'],
                'poli_id' => [
                    'required',
                    'integer',
                    // pastikan poli itu memang ter-relasi dengan dokter (tabel pivot dokter_poli)
                    Rule::exists('dokter_poli', 'poli_id')->where(
                        fn ($q) => $q->where('dokter_id', $request->dokter_id)
                    ),
                ],
                'keluhan_awal' => ['required', 'string', 'max:2000'],
            ],
            [
                'dokter_id.required' => 'Dokter wajib dipilih.',
                'dokter_id.exists' => 'Dokter tidak ditemukan.',
                'poli_id.required' => 'Poli wajib dipilih.',
                'poli_id.exists' => 'Poli tidak sesuai dengan dokter yang dipilih.',
                'keluhan_awal.required' => 'Keluhan awal wajib diisi.',
                'keluhan_awal.max' => 'Keluhan awal maksimal 2000 karakter.',
            ]
        );

        // === UPDATE DATA KUNJUNGAN (tanpa mengubah no_antrian, tanggal, pasien) ===
        $kunjungan = Kunjungan::findOrFail($id);

        $kunjungan->dokter_id = $validated['dokter_id'];
        $kunjungan->poli_id = $validated['poli_id'];
        $kunjungan->keluhan_awal = $validated['keluhan_awal'];

        $kunjungan->save();

        return response()->json([
            'success' => true,
            'message' => 'Data kunjungan berhasil diperbarui.',
            'data' => $kunjungan->fresh(['pasien', 'dokter', 'poli']),
        ]);
    }

    public function updateStatusKunjunganToEngaged($id)
    {
        // Cari data kunjungan
        $kunjungan = Kunjungan::findOrFail($id);

        if ($kunjungan->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => "Status saat ini adalah '{$kunjungan->status}', tidak dapat diproses.",
            ], 422);
        }

        DB::transaction(function () use ($kunjungan) {

            // 1️⃣ Update status kunjungan → Engaged
            $kunjungan->update([
                'status' => 'Engaged',
                'updated_at' => now(),
            ]);

            // 🔔 KIRIM NOTIFIKASI FCM KE PASIEN
            NotificationHelper::kirimNotifikasiStatusEngaged($kunjungan);

            // 2️⃣ Buat EMR (perawat_id dikosongkan dulu)
            Emr::firstOrCreate(
                [
                    // kunci unik EMR per kunjungan
                    'kunjungan_id' => $kunjungan->id,
                ],
                [
                    'pasien_id' => $kunjungan->pasien_id,
                    'dokter_id' => $kunjungan->dokter_id,
                    'poli_id' => $kunjungan->poli_id,
                    'perawat_id' => null, // karena yang klik admin, belum perawat

                    'resep_id' => null,

                    // samakan dengan keluhan_awal
                    'keluhan_utama' => $kunjungan->keluhan_awal,

                    // sisanya dibiarkan null dulu, akan diisi di form EMR
                    'riwayat_penyakit_dahulu' => null,
                    'riwayat_penyakit_keluarga' => null,
                    'tekanan_darah' => null,
                    'suhu_tubuh' => null,
                    'nadi' => null,
                    'pernapasan' => null,
                    'saturasi_oksigen' => null,
                    'diagnosis' => null,
                ]
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Status kunjungan berhasil diperbarui menjadi Engaged dan EMR dibuat.',
            'data' => [
                'id' => $kunjungan->id,
                'status' => 'Engaged',
            ],
        ]);
    }

    public function getDataKunjunganYangAkanDatang()
    {
        $besok = Carbon::tomorrow()->toDateString();

        // Ambil kunjungan pending mulai besok
        $kunjunganMasaDepan = Kunjungan::with([
            'poli',
            'pasien',
            'dokter',
        ])
            ->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
            ->whereDate('tanggal_kunjungan', '>=', $besok)
            ->orderBy('tanggal_kunjungan', 'asc')
            ->orderBy('no_antrian', 'asc')
            ->get();

        return response()->json($kunjunganMasaDepan);
    }

    public function getDataKYAD($id)
    {
        $dataKYAD = Kunjungan::with('pasien', 'poli', 'dokter')->where('id', $id)->firstOrFail();

        return response()->json([
            'data' => $dataKYAD,
        ]);
    }

    public function batalkanKunjungan($id)
    {
        $dataKYAD = Kunjungan::find($id);

        if (! $dataKYAD) {
            return response()->json([
                'success' => false,
                'message' => 'Data kunjungan tidak ditemukan',
            ]);
        }

        $dataKYAD->update([
            'status' => 'Canceled',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil Membatalkan Kunjungan',
        ]);
    }

    public function listDokter(Request $request)
    {
        $q = $request->input('q', '');

        $data = Dokter::select('id', 'nama_dokter')
            ->when($q, fn ($w) => $w->where('nama_dokter', 'like', "%{$q}%"))
            ->orderBy('nama_dokter')
            ->get();

        return response()->json(['data' => $data]);
    }

    /**
     * List POLI berdasarkan DOKTER (ambil dari tabel dokter_poli)
     * URL (sesuai JS mu):
     * GET /jadwal_kunjungan/listPoliByDokter/{dokterId}/poli?q=...
     */
    public function listPoliByDokter(Request $request, $dokterId)
    {
        $q = $request->input('q', '');

        $dokterPolis = DokterPoli::with(['poli:id,nama_poli'])
            ->where('dokter_id', $dokterId)
            ->when($q, function ($w) use ($q) {
                $w->whereHas('poli', function ($qq) use ($q) {
                    $qq->where('nama_poli', 'like', "%{$q}%");
                });
            })
            ->get()
            ->sortBy('poli.nama_poli')
            ->values();

        $data = $dokterPolis->map(function ($dp) {
            return [
                // INI yang dibaca TomSelect POLI (valueField = "id")
                'id' => $dp->poli_id,
                'nama_poli' => $dp->poli->nama_poli ?? 'Tanpa Nama',

                // tambahan info kalau nanti mau dipakai
                'dokter_poli_id' => $dp->id,
                'dokter_id' => $dp->dokter_id,
            ];
        });

        return response()->json(['data' => $data]);
    }
}
