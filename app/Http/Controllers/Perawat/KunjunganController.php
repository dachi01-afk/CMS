<?php

namespace App\Http\Controllers\Perawat;

use App\Http\Controllers\Controller;
use App\Models\Kunjungan;
use App\Models\Perawat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KunjunganController extends Controller
{
    public function index()
    {
        return view('perawat.kunjungan.kunjungan');
    }


    public function getDataKunjunganHariIni(Request $request)
    {
        $userId = Auth::id();

        $perawat = Perawat::query()
            ->select(['id', 'user_id', 'dokter_id', 'poli_id'])
            ->where('user_id', $userId)
            ->firstOrFail();

        $today = Carbon::today()->toDateString();

        $q = Kunjungan::query()
            ->with([
                'pasien:id,nama_pasien',
                'dokter:id,nama_dokter',
                'poli:id,nama_poli',
            ])
            ->whereDate('tanggal_kunjungan', $today)
            ->where('status', 'Engaged');   // <- HANYA Engaged

        // filter sesuai relasi perawat yang login
        if (!empty($perawat->dokter_id)) {
            $q->where('dokter_id', $perawat->dokter_id);
        }
        if (!empty($perawat->poli_id)) {
            $q->where('poli_id', $perawat->poli_id);
        }

        $rows = $q->orderBy('no_antrian')
            ->get()
            ->map(function ($k) {
                return [
                    'kunjungan_id' => $k->id,
                    'no_antrian'   => $k->no_antrian ?? '-',
                    'nama_pasien'  => $k->pasien->nama_pasien ?? '-',
                    'dokter'       => $k->dokter->nama_dokter ?? '-',
                    'poli'         => $k->poli->nama_poli ?? '-',
                    'keluhan'      => $k->keluhan_awal ?? '-',
                ];
            });

        return response()->json(['data' => $rows]);
    }
}
