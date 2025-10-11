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
        // Ambil hari saat ini, misal: 'Senin', 'Selasa', dst.
        $hariIni = ucfirst(Carbon::now()->locale('id')->dayName);

        // Ambil jadwal berdasarkan hari ini
        $jadwalHariIni = JadwalDokter::with('dokter')
            ->where('hari', $hariIni)
            ->get();
        return view('admin.jadwal_kunjungan', compact('jadwalHariIni', 'hariIni'));
    }

    public function search(Request $request)
    {
        $query = $request->get('query');
        $pasien = Pasien::where('nama_pasien', 'LIKE', "%{$query}%")->get(['id', 'nama_pasien', 'alamat', 'jenis_kelamin']);
        return response()->json($pasien);
    }

    public function store(Request $request)
    {
        $request->validate([
            'dokter_id' => 'required|exists:dokter,id',
            'pasien_id' => 'required|exists:pasien,id',
            'tanggal_kunjungan' => 'required|date',
            'keluhan_awal' => 'required|string',
        ]);

        // Gunakan transaksi agar aman dari race condition
        $kunjungan = DB::transaction(function () use ($request) {

            $tanggal = $request->tanggal_kunjungan;

            // Ambil kunjungan terakhir di tanggal yang sama
            $lastKunjungan = Kunjungan::where('tanggal_kunjungan', $tanggal)
                ->orderByDesc('id')
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
                'dokter_id' => $request->dokter_id,
                'pasien_id' => $request->pasien_id,
                'tanggal_kunjungan' => $tanggal,
                'no_antrian' => $formattedNo,
                'keluhan_awal' => $request->keluhan_awal,
                'status' => 'Waiting',
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

        $kunjungan = Kunjungan::with(['dokter', 'pasien'])
            ->whereDate('tanggal_kunjungan', $today)
            ->where('status', 'Waiting')
            ->orderBy('no_antrian')
            ->get();

        return response()->json($kunjungan);
    }

    public function updateStatus($id)
    {

        $kunjungan = Kunjungan::findOrFail($id);

        if ($kunjungan->status !== 'Waiting') {
            return response()->json(['success' => false, 'message' => 'Status tidak valid untuk diproses.']);
        }

        $kunjungan->update(['status' => 'Engaged']);

        return response()->json(['success' => true, 'message' => 'Status kunjungan diperbarui menjadi Engaged.']);
    }
}
