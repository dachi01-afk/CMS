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

        if (empty($perawat->dokter_id) || empty($perawat->poli_id)) {
            return response()->json(['data' => []]);
        }

        $tz          = config('app.timezone', 'Asia/Jakarta');
        $todayLocal  = Carbon::today($tz);
        $endLocal    = $todayLocal->copy()->endOfDay();
        $startUtc    = $todayLocal->copy()->timezone('UTC');
        $endUtc      = $endLocal->copy()->timezone('UTC');
        $todayString = $todayLocal->toDateString();

        // Ambil nama dokter & poli via JOIN supaya pasti muncul
        $rows = Kunjungan::query()
            ->leftJoin('dokter', 'dokter.id', '=', 'kunjungan.dokter_id')
            ->leftJoin('poli',   'poli.id',   '=', 'kunjungan.poli_id')
            ->with([
                'pasien:id,nama_pasien', // tetap eager load pasien
            ])
            ->where(function ($q) use ($todayString, $startUtc, $endUtc) {
                $q->whereDate('kunjungan.tanggal_kunjungan', $todayString)
                    ->orWhereBetween('kunjungan.tanggal_kunjungan', [$startUtc, $endUtc]);
            })
            ->where('kunjungan.status', 'Waiting')
            ->where('kunjungan.dokter_id', $perawat->dokter_id)
            ->where('kunjungan.poli_id',   $perawat->poli_id)
            ->orderBy('kunjungan.no_antrian')
            ->get([
                'kunjungan.*',
                'dokter.nama_dokter as _nama_dokter',
                'poli.nama_poli as _nama_poli',
            ])
            ->map(function ($k) {
                return [
                    'kunjungan_id' => $k->id,
                    'no_antrian'   => $k->no_antrian ?? '-',
                    'nama_pasien'  => $k->pasien->nama_pasien ?? '-',
                    'dokter'       => $k->_nama_dokter ?? '-', // ← pasti ada dari JOIN
                    'poli'         => $k->_nama_poli ?? '-',
                    'keluhan'      => $k->keluhan_awal ?? '-',
                ];
            });

        return response()->json(['data' => $rows]);
    }
}
