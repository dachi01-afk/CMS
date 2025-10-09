<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\Kunjungan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DokterController extends Controller
{
    public function index()
    {
        return view('dokter.index', ['header' => 'Dashboard Dokter']);
    }

    public function logoutDokter()
    {
        Auth::logout();

        return redirect()->route('dokter.login');
    }

    public function kunjungan()
    {
        $user_id = Auth::user()->id;

        $dokter_id = Dokter::with('user')->where('user_id', $user_id)->firstOrFail();

        $dataKunjungan = Kunjungan::with('dokter', 'pasien')->where('dokter_id', $dokter_id->id)->where('status', 'Engaged')->paginate(10);

        // dd($dataKunjungan);

        // return response()->json(['data' => $dataKunjungan]);

        return view('dokter.kunjungan', compact('dataKunjungan'), ['header' => 'Kunjungan']);
    }

    public function riwayatKunjungan()
    {
        $user_id = Auth::user()->id;

        $dokter_id = Dokter::with('user')->where('user_id', $user_id)->firstOrFail();

        $dataRiwayatKunjungan = Kunjungan::with('dokter', 'pasien')->where('dokter_id', $dokter_id->id)->where('status', 'Succeed')->orderByDesc('tanggal_kunjungan')->paginate(10);

        // dd($dataKunjungan);

        // return response()->json(['data' => $dataKunjungan]);

        return view('dokter.riwayat-kunjungan', compact('dataRiwayatKunjungan'), ['header' => 'Riwayat Kunjungan']);
    }
}
