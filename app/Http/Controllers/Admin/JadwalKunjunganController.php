<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Pasien;
use App\Models\Kunjungan;
use App\Models\JadwalDokter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

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
        $request->validate([
            'poli_id' => 'required|exists:poli,id',
            'pasien_id' => 'required|exists:pasien,id',
            'keluhan_awal' => 'required|string',
        ]);

        // dd($request->all());

        // Gunakan transaksi agar aman dari race condition
        $kunjungan = DB::transaction(function () use ($request) {

            $tanggal = $request->tanggal_kunjungan;

            // Ambil kunjungan terakhir di tanggal yang sama
            $lastKunjungan = Kunjungan::where('tanggal_kunjungan', $tanggal)
                ->orderByDesc('no_antrian')
                ->lockForUpdate() // kunci baris agar tidak bentrok antar request
                ->first();

            // Tentukan nomor antrian berikutnya
            if ($lastKunjungan) {
                $nextNumber = (int) $lastKunjungan->no_antrian + 1;
            } else {
                $nextNumber = 1;
            }

            // Format 3 digit (001, 002, 010, dst)
            $formattedNo = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            // Simpan data kunjungan
            $kunjungan = Kunjungan::create([
                'poli_id' => $request->poli_id,
                'pasien_id' => $request->pasien_id,
                'tanggal_kunjungan' => $tanggal,
                'no_antrian' => $formattedNo,
                'keluhan_awal' => $request->keluhan_awal,
                'status' => 'Pending',
            ]);

            return $kunjungan;
        });

        return response()->json([
            'success' => true,
            'message' => 'Data kunjungan berhasil ditambahkan.',
            'data' => $kunjungan,
        ]);
    }

    public function waiting()
    {
        $today = now()->toDateString();

        // $kunjungan = Kunjungan::with(['poli.dokter', 'pasien'])->where('status', 'pending')->whereDate('tanggal_kunjunfan', $today)->oderBy('no_antrian')->get();   

        $kunjungan = Kunjungan::with(['poli', 'dokter', 'pasien'])
            ->whereDate('tanggal_kunjungan', $today)
            ->where('status', 'Pending')
            ->orderBy('no_antrian')
            ->get();

        return response()->json($kunjungan);
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
