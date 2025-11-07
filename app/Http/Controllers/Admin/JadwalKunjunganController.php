<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Pasien;
use App\Models\Kunjungan;
use App\Models\JadwalDokter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;


class JadwalKunjunganController extends Controller
{

    public function index()
    {
        $tz  = config('app.timezone', 'Asia/Jakarta');
        $now = Carbon::now($tz);

        $hariIni = ucfirst(Str::of($now->locale('id')->dayName)->lower());
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
        $jadwalHariIni = JadwalDokter::query()
            ->with([
                'dokter:id,nama_dokter,jenis_spesialis_id',
                'dokter.jenisSpesialis:id,nama_spesialis',
                'poli:id,nama_poli',
            ])
            ->whereRaw('LOWER(hari) = ?', [Str::of($hariIni)->lower()])
            ->where('jam_selesai', '>', $jamSekarang)
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
                $hariIndo = Str::of($jd->hari)->lower()->toString();
                $hariEng  = $mapHari[$hariIndo] ?? null;

                if (!$hariEng) {
                    $jd->setAttribute('tanggal_berikutnya', null);
                    return $jd;
                }

                // tentukan tanggal target minggu ini
                $target = Carbon::parse("this {$hariEng}", $now->timezone);

                // kalau hari target sudah lewat → next week
                if ($target->lt($now->startOfDay())) {
                    $target->addWeek();
                }

                // kalau hari ini tapi jam_selesai sudah lewat → next week juga
                if ($target->isSameDay($now)) {
                    $jamSelesai = Carbon::createFromFormat('H:i:s', $jd->jam_selesai, $now->timezone);
                    $jamNow     = Carbon::createFromFormat('H:i:s', $now->format('H:i:s'), $now->timezone);

                    if ($jamSelesai->lte($jamNow)) {
                        $target->addWeek();
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

        return view('admin.jadwal_kunjungan', compact(
            'jadwalHariIni',
            'hariIni',
            'jadwalYangAkanDatang',
            'tanggalHariIni'
        ));
    }





    // public function index()
    // {
    //     $hariIni = ucfirst(Carbon::now()->locale('id')->dayName);
    //     $tanggalHariIni = Carbon::now()->toDateString();

    //     // Ambil semua kunjungan dengan status "Pending" atau "Waiting"
    //     $kunjunganYangAkanDatang = Kunjungan::with(['poli.dokter', 'pasien'])
    //         ->whereIn('status', ['Pending', 'Waiting'])
    //         ->whereDate('tanggal_kunjungan', '>=', Carbon::today())
    //         ->orderBy('tanggal_kunjungan', 'asc')
    //         ->get();

    //     // Ambil kunjungan yang berlangsung hari ini
    //     $kunjunganHariIni = Kunjungan::with(['poli.dokter', 'pasien'])
    //         ->whereDate('tanggal_kunjungan', '=', Carbon::today())
    //         ->orderBy('tanggal_kunjungan', 'asc')
    //         ->get();

    //     return view('admin.jadwal_kunjungan', compact('hariIni', 'tanggalHariIni', 'kunjunganHariIni', 'kunjunganYangAkanDatang'));
    // }

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
            'dokter_id'         => 'nullable|integer',
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

            // Anti-duplikat 60 detik (identik)
            $dupe = Kunjungan::whereDate('tanggal_kunjungan', $validated['tanggal_kunjungan'])
                ->where('poli_id',   $validated['poli_id'])
                ->where('pasien_id', $validated['pasien_id'])
                ->where('keluhan_awal', $validated['keluhan_awal'])
                ->where('created_at', '>=', now()->subSeconds(60))
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($dupe) {
                $cached = Cache::get("kunjungan_dokter:{$dupe->id}");
                $dokter = null;
                if (!empty($cached['dokter_id'])) {
                    $dokter = DB::table('dokter')
                        ->select('id', 'nama_dokter')
                        ->where('id', (int)$cached['dokter_id'])
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
            $nextNumber  = $lastNumber + 1;
            $formattedNo = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            // Simpan kunjungan baru
            $baru = Kunjungan::create([
                'poli_id'           => $validated['poli_id'],
                'pasien_id'         => $validated['pasien_id'],
                'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
                'no_antrian'        => $formattedNo,
                'keluhan_awal'      => $validated['keluhan_awal'],
                'status'            => 'Pending',
            ]);

            // Tentukan dokter terpilih: FE → jadwal → jika kosong → error
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
                    $chosenDokterId = (int)$row->dokter_id;
                } else {
                    throw ValidationException::withMessages([
                        'dokter_id' => 'Harus memilih dokter atau jadwal dokter.',
                    ]);
                }
            } else {
                // kalau FE kirim dokter_id, amankan juga via pivot (sekali lagi untuk race)
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

            // Cache mapping kunjungan -> dokter (opsional)
            Cache::forever("kunjungan_dokter:{$baru->id}", [
                'dokter_id'         => $chosenDokterId,
                'poli_id'           => (int)$validated['poli_id'],
                'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
                'by'                => $dokterId !== null ? 'fe' : 'jadwal',
            ]);

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

        // Siapkan payload yang ramah FE (data.no_antrian ada di tingkat atas)
        $kunjunganLoaded = $result['kunjungan']->load('poli', 'pasien');

        return response()->json([
            'success' => true,
            'message' => $msg,
            'data'    => [
                'no_antrian'       => $kunjunganLoaded->no_antrian,   // <— dibaca JS
                'kunjungan'        => $kunjunganLoaded,
                'dokter_terpilih'  => $result['dokter'],
            ],
        ], $result['reuse'] ? 200 : 201);
    }




    public function waiting()
    {
        $today = now()->toDateString();

        // Ambil kunjungan hari ini status Pending
        $kunjungan = Kunjungan::with(['poli', 'pasien'])
            ->whereDate('tanggal_kunjungan', $today)
            ->where('status', 'Pending')
            // no_antrian disimpan string "001","002",..., kita cast supaya urutan numerik
            ->orderByRaw('CAST(no_antrian AS UNSIGNED)')
            ->get();

        // Ambil semua dokter_id dari cache dalam sekali jalan
        $dokterIdMap = [];   // [kunjungan_id => dokter_id]
        $dokterIdList = [];

        foreach ($kunjungan as $k) {
            $c = Cache::get("kunjungan_dokter:{$k->id}");
            if ($c && !empty($c['dokter_id'])) {
                $dokterIdMap[$k->id] = (int) $c['dokter_id'];
                $dokterIdList[] = (int) $c['dokter_id'];
            }
        }

        // Query detail dokter sekali (hemat N+1)
        $dokters = collect();
        if (!empty($dokterIdList)) {
            $dokters = DB::table('dokter')
                ->select('id', 'nama_dokter', 'poli_id')
                ->whereIn('id', array_values(array_unique($dokterIdList)))
                ->get()
                ->keyBy('id');
        }

        // Satukan ke payload response
        $payload = $kunjungan->map(function ($k) use ($dokterIdMap, $dokters) {
            $dokter = null;
            if (isset($dokterIdMap[$k->id])) {
                $dokter = $dokters->get($dokterIdMap[$k->id]);
            }

            // set attribute agar tetap bentuknya mirip model + ekstra field
            $k->setAttribute('dokter_terpilih', $dokter);
            return $k;
        });

        return response()->json([
            'success' => true,
            'date'    => $today,
            'data'    => $payload,
        ]);
    }

    public function updateStatus($id)
    {
        $kunjungan = Kunjungan::findOrFail($id);

        if ($kunjungan->status !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Status tidak valid untuk diproses.']);
        }

        if ($kunjungan->status === 'Pending') {
            $kunjungan->update(['status' => 'Engaged']);
            return response()->json(['success' => true, 'message' => 'Status kunjungan diperbarui menjadi Engaged.']);
        }

        return response()->json(['success' => true, 'message' => 'Status kunjungan diperbarui menjadi Waiting.']);
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
            'poli',   // cukup poli; dokter akan diisi dari cache
            'pasien',
        ])
            ->whereRaw("LOWER(TRIM(status)) = ?", ['pending'])
            ->whereDate('tanggal_kunjungan', '>=', $besok)
            ->orderBy('tanggal_kunjungan', 'asc')
            ->orderBy('no_antrian', 'asc')
            ->get();

        // Sisipkan dokter_terpilih dari cache (fallback: null)
        $enriched = $kunjunganMasaDepan->map(function ($k) {
            $cached = Cache::get("kunjungan_dokter:{$k->id}");
            $dokter = null;

            if (!empty($cached['dokter_id'])) {
                $dokter = DB::table('dokter')
                    ->select('id', 'nama_dokter')
                    ->where('id', (int) $cached['dokter_id'])
                    ->first();
            }

            // tambahkan field virtual ke payload
            $k->setAttribute('dokter_terpilih', $dokter);

            return $k;
        });

        return response()->json($enriched);
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
