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
use Illuminate\Validation\ValidationException;

class JadwalKunjunganController extends Controller
{
    public function index()
    {
        // Ambil hari saat ini dalam bahasa Indonesia
        $hariIni = ucfirst(Carbon::now()->locale('id')->dayName);

        $tanggalHariIni = Carbon::now()->toDateString();

        // Mapping hari Indonesia ke bahasa Inggris (untuk keperluan parsing Carbon)
        $mapHari = [
            'senin'  => 'monday',
            'selasa' => 'tuesday',
            'rabu'   => 'wednesday',
            'kamis'  => 'thursday',
            'jumat'  => 'friday',
            'sabtu'  => 'saturday',
            'minggu' => 'sunday',
        ];

        // Ambil semua jadwal dokter
        $jadwalSemua = JadwalDokter::with(['dokter', 'poli'])->latest()->get();

        // Proses jadwal yang akan datang berdasarkan hari
        $jadwalYangAkanDatang = $jadwalSemua->map(function ($jadwal) use ($mapHari) {
            $hariIndo = strtolower($jadwal->hari);
            $hariEng = $mapHari[$hariIndo] ?? null;

            if (!$hariEng) {
                $jadwal->tanggal_berikutnya = null;
                return $jadwal;
            }

            // Tanggal hari ini dan nama hari yang sedang diproses
            $today = Carbon::today();
            $tanggalHariIni = Carbon::parse('this ' . $hariEng);

            // Jika hari itu sudah lewat, ambil minggu depan
            if ($tanggalHariIni->lessThan($today)) {
                $tanggalHariIni = Carbon::parse('next ' . $hariEng);
            }

            // Simpan hasil ke model (atribut baru)
            $jadwal->tanggal_berikutnya = $tanggalHariIni;

            return $jadwal;
        })->filter(function ($jadwal) {
            // Hanya ambil jadwal yang masih relevan (hari ini atau setelahnya)

            return $jadwal->tanggal_berikutnya &&
                $jadwal->tanggal_berikutnya->greaterThanOrEqualTo(Carbon::today());
        });

        $jamSekarang = Carbon::now()->format('H:i:s');

        // Ambil jadwal berdasarkan hari ini
        $jadwalHariIni = JadwalDokter::with(['dokter', 'poli'])
            ->where('hari', $hariIni)->where('jam_selesai', '>', $jamSekarang)
            ->latest()->get();

        // Kirim ke view
        return view('admin.jadwal_kunjungan', compact('jadwalHariIni', 'hariIni', 'jadwalYangAkanDatang', 'tanggalHariIni'));
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
        // Logging cepat untuk diagnosis
        Log::info('REQ store kunjungan', $request->only([
            'dokter_id','jadwal_id','poli_id','pasien_id','tanggal_kunjungan'
        ]));

        $validated = $request->validate([
            'poli_id'           => 'required|exists:poli,id',
            'pasien_id'         => 'required|exists:pasien,id',
            'tanggal_kunjungan' => 'required|date',
            'keluhan_awal'      => 'required|string',
            'jadwal_id'         => 'nullable|exists:jadwal_dokter,id',
            // dokter_id: divalidasi manual di bawah supaya bisa cek konsistensi poli
        ]);

        $dokterId = $request->filled('dokter_id') ? (int) $request->input('dokter_id') : null;

        // Jika FE mengirim dokter_id → pastikan dokter berada di poli yang sama
        if ($dokterId !== null) {
            $valid = DB::table('dokter')
                ->where('id', $dokterId)
                ->where('poli_id', $validated['poli_id'])
                ->exists();

            if (!$valid) {
                throw ValidationException::withMessages([
                    'dokter_id' => 'Dokter tidak termasuk ke poli yang dipilih.',
                ]);
            }
        }

        $kunjungan = DB::transaction(function () use ($validated, $dokterId, $request) {
            // Cegah double booking
            $sudahAda = Kunjungan::whereDate('tanggal_kunjungan', $validated['tanggal_kunjungan'])
                ->where('poli_id',   $validated['poli_id'])
                ->where('pasien_id', $validated['pasien_id'])
                ->lockForUpdate()
                ->exists();

            if ($sudahAda) {
                throw ValidationException::withMessages([
                    'pasien_id' => 'Pasien sudah memiliki kunjungan pada tanggal & poli ini.',
                ]);
            }

            // Nomor antrian per tgl+poli
            $maxNumber = DB::table('kunjungan')
                ->whereDate('tanggal_kunjungan', $validated['tanggal_kunjungan'])
                ->where('poli_id', $validated['poli_id'])
                ->lockForUpdate()
                ->max(DB::raw('CAST(no_antrian AS UNSIGNED)'));

            $nextNumber  = (int)($maxNumber ?? 0) + 1;
            $formattedNo = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            // Simpan kunjungan
            $kunjungan = Kunjungan::create([
                'poli_id'           => $validated['poli_id'],
                'pasien_id'         => $validated['pasien_id'],
                'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
                'no_antrian'        => $formattedNo,
                'keluhan_awal'      => $validated['keluhan_awal'],
                'status'            => 'Pending',
            ]);

            // Tentukan dokter terpilih: FE -> jadwal -> error (tanpa fallback diam-diam)
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
            }

            // Simpan ke cache agar halaman lain mudah membaca
            Cache::forever("kunjungan_dokter:{$kunjungan->id}", [
                'dokter_id'         => $chosenDokterId,
                'poli_id'           => (int)$validated['poli_id'],
                'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
                'by'                => $dokterId !== null ? 'fe' : 'jadwal',
            ]);

            // Lampirkan id dokter ke model via accessor sederhana (opsional)
            $kunjungan->setAttribute('dokter_id_terpilih', $chosenDokterId);

            return $kunjungan;
        });

        // Ambil data dokter terpilih untuk dikirim ke FE
        $dokter = null;
        if ($kunjungan->getAttribute('dokter_id_terpilih')) {
            $dokter = DB::table('dokter')
                ->select('id', 'nama_dokter', 'poli_id')
                ->where('id', $kunjungan->getAttribute('dokter_id_terpilih'))
                ->first();
        }

        return response()->json([
            'success' => true,
            'message' => 'Data kunjungan berhasil ditambahkan.',
            'data'    => [
                'kunjungan'       => $kunjungan->load('poli','pasien'),
                'dokter_terpilih' => $dokter,
            ],
        ], 201);
    }

    public function waiting()
{
    $today = now()->toDateString();

    // Ambil kunjungan hari ini status Pending
    $kunjungan = Kunjungan::with(['poli','pasien'])
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
            ->select('id','nama_dokter','poli_id')
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
        
        $kunjunganMasaDepan = Kunjungan::with('poli.dokter', 'pasien')
            ->whereRaw("LOWER(TRIM(status)) = ?", ['pending']) // biar aman dari kapitalisasi/spasi
            ->whereDate('tanggal_kunjungan', '>=', $besok) // mulai dari besok
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
