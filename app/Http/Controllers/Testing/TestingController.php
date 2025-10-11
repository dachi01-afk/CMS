<?php

namespace App\Http\Controllers\Testing;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\EMR;
use App\Models\JadwalDokter;
use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\Resep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TestingController extends Controller
{
    public function testing()
    {
        $dataResepObat = Resep::with('obat', 'kunjungan.pasien', 'kunjungan.dokter')->paginate(10);

        dd($dataResepObat);

        return view('testing.index', compact('dataResepObat'));
    }

    public function ubahStatusKunjungan(Request $request)
    {
        $dataKunjungan = Kunjungan::findOrFail($request->id);

        if ($dataKunjungan->status === 'Pending') {
            $dataKunjungan->update([
                'status' => 'Waiting'
            ]);

            return response()->json([
                'success' => true,
                'status' => 200,
                'Data Kunjungan' => $dataKunjungan,
                'message' => 'Berhasil Merubah Status Kunjungan Dari Pending Menjadi Waiting'
            ]);
        } elseif ($dataKunjungan->status === 'Waiting') {
            $dataKunjungan->update([
                'status' => 'Engaged'
            ]);

            return response()->json([
                'success' => true,
                'status' => 200,
                'Data Kunjungan' => $dataKunjungan,
                'message' => 'Berhasil Merubah Status Kunjungan Dari Waiting Menjadi Engaged'
            ]);
        }

        return response()->json([
            'success' => false,
            'status' => 404,
            'message' => 'Error',
            'Data Kunjungan' => $dataKunjungan,
        ]);
    }

    public function batalkanStatusKunjungan(Request $request)
    {
        $dataKunjungan = Kunjungan::findOrFail($request->id);

        $dataKunjungan->update([
            'status' => 'Canceled',
            'no_antrian' => null,
        ]);

        return response()->json([
            'success' => true,
            'status' => 200,
            'Data Kunjungan' => $dataKunjungan,
            'message' => 'Berhasil Merubah Status Kunjungan Dari Pending Menjadi Canceled',
        ]);
    }

    public function testingCreateKunjungan(Request $request)
    {
        $request->validate([
            'pasien_id' => ['required', 'exists:pasien,id'],
            'dokter_id' => ['required', 'exists:dokter,id'],
            'tanggal_kunjungan' => ['required', 'date'],
            'keluhan_awal' => ['required'],
        ]);

        $tanggalKunjungan = $request->tanggal_kunjungan;

        // Gunakan transaksi supaya aman dari race condition (2 pasien booking bersamaan)
        $nomorAntrian = DB::transaction(function () use ($tanggalKunjungan, $request) {

            // Cari kunjungan terakhir di tanggal yang sama
            $lastKunjungan = Kunjungan::where('tanggal_kunjungan', $tanggalKunjungan)
                ->orderByDesc('id')
                ->lockForUpdate() // kunci baris agar tidak duplikat
                ->first();

            // Tentukan nomor antrian berikutnya
            if ($lastKunjungan) {
                $nextNumber = (int)$lastKunjungan->no_antrian + 1;
            } else {
                $nextNumber = 1;
            }

            // Format jadi 3 digit, misal: 001, 002, 010, 123
            $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            // Simpan data kunjungan baru
            $kunjungan = Kunjungan::create([
                'pasien_id' => $request->pasien_id,
                'dokter_id' => $request->dokter_id,
                'tanggal_kunjungan' => $tanggalKunjungan,
                'no_antrian' => $formattedNumber,
                'keluhan_awal' => $request->keluhan_awal,
                'status' => 'Pending', // contoh default status
                // tambahkan kolom lain sesuai struktur tabel kamu
            ]);

            return response()->json([
                'success' => true,
                'status' => 200,
                'Data Kunjungan' => $kunjungan,
                'Data No Antrian' => $formattedNumber,
            ]);
        });

        return response()->json([
            'message' => 'Kunjungan berhasil dibuat',
            'no_antrian' => $nomorAntrian,
        ]);
    }


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

    public function updateStatusResepObat(Request $request)
    {
        $request->validate([
            'resep_id' => ['required', 'exists:resep,id'],
            'obat_id' => ['required', 'exists:obat,id'],
            'status' => ['required', 'string'], // contoh: 'belum bayar' / 'sudah bayar'
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Ambil resep
                $resep = Resep::findOrFail($request->resep_id);

                // Cek apakah obat ada di dalam resep ini
                $obat = $resep->obat()->where('obat_id', $request->obat_id)->firstOrFail();

                // Update status di tabel pivot resep_obat
                $resep->obat()->updateExistingPivot($request->obat_id, [
                    'status' => $request->status,
                ]);

                // Jika status berubah jadi "sudah bayar", kurangi stok obat
                if ($request->status === 'sudah bayar') {
                    $jumlahObat = $obat->pivot->jumlah;

                    if ($obat->jumlah < $jumlahObat) {
                        throw new \Exception('Stok obat tidak mencukupi.');
                    }

                    $obat->decrement('jumlah', $jumlahObat);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Status resep obat berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status resep obat: ' . $e->getMessage(),
            ], 500);
        }
    }
}
