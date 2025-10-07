<?php

namespace App\Http\Controllers\Testing;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\JadwalDokter;
use App\Models\Kunjungan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestingController extends Controller
{
    public function index()
    {
        $dataJadwalDokter = JadwalDokter::with('dokter')->get();

        return response()->json([
            'data Jadwal Dokter' => $dataJadwalDokter
        ]);
    }

    public function loginDokter()
    {
        return view('testing.dokter.login');
    }

    public function prosesLoginDokter(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();
            // dd($user);

            if ($user->role === 'Dokter') {
                return redirect()->route('index.dokter');
            } else {
                Auth::logout();
                return redirect()->back();
            }
        }

        return redirect()->back();

        // return $request;
    }

    public function indexDokter()
    {
        $idUser = Auth::user()->id;
        $dataDokter = Dokter::with('user')->where('user_id', $idUser)->firstOrFail();
        $dataKunjungan = Kunjungan::with('dokter', 'pasien')->where('dokter_id', $dataDokter->id)->paginate(10);
        // dd($dataKunjungan);

        return view('testing.dokter.dashboard', compact('dataKunjungan'));
    }

    public function logoutDokter()
    {
        Auth::logout();

        return redirect()->route('dokter.login');
    }

    public function kunjungan()
    {
        $dataKunjungan = Kunjungan::with('dokter', 'pasien')->paginate(10);
        return view('admin.kunjungan', compact('dataKunjungan'));
    }
}
