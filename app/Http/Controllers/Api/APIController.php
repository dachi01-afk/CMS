<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\JadwalDokter;
use App\Models\Pasien;
use App\Models\Testimoni;
use App\Models\Kunjungan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpParser\Node\Stmt\Foreach_;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class APIController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:user'],
            'nama_pasien' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:user'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);


        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'Pasien',
        ]);


        $pasien = Pasien::create([
            'email' => $request->email,
            'nama_lengkap' => $request->nama_pasien,
            'jenis_kelamin' => $request->jenis_kelamin,
        ]);

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Akun dan data pasien berhasil didaftarkan.',
            'data' => [
                'account' => $pasien,
                'token' => $token,
                'pasien_id' => $pasien->id_pasien,
            ]
        ], 201);
    }

    public function getDataSpesialisasiDokter()
    {
        $dataSpesialis = Dokter::select('spesialisasi')->get();

        return response()->json([
            'Data Spesialis Dokter' => $dataSpesialis,
        ]);
    }

    public function getDataDokterSpesialisasi()
    {
        $dataDokterSpesialisasi = Dokter::where('spesialisasi', 'Psikiatri')->get();
        return response()->json([
            'Data Dokter Spesialisasi' => $dataDokterSpesialisasi,
        ]);
    }

    public function getDataJadwalDokter()
    {
        $dataJadwalDokter = JadwalDokter::with('dokter')->get();

        $dataDokter = [];

        foreach ($dataJadwalDokter as $jadwal) {
            // Pastikan relasi dokter tidak null
            if ($jadwal->dokter) {
                $dataDokter[] = [
                    'dokter_id' => $jadwal->dokter->id,
                    'nama_dokter' => $jadwal->dokter->nama_dokter,
                    'hari' => $jadwal->hari,
                    'spesialisasi' => $jadwal->dokter->spesialisasi,
                    'jam_awal' => $jadwal->jam_awal,
                    'jam_selesai' => $jadwal->jam_selesai,
                    'foto' => $jadwal->dokter->foto,
                    'pengalaman' => $jadwal->dokter->pengalaman,
                    'deskripsi_dokter' => $jadwal->dokter->deskripsi_dokter,
                ];
            }
        }

        return response()->json([
            'Data Jadwal Dokter' => $dataDokter,
        ]);
    }

    public function getDataKunjungan() {}



    public function getDataTestimoni()
    {
        $dataTestimoni = Testimoni::get();

        return response()->json([
            'Data Testimoni' => $dataTestimoni,
        ]);
    }
    public function getDataPasien()
    {
        $dataPasien = Pasien::get();

        return response()->json([
            'Data Pasien' => $dataPasien,
        ]);
    }

    public function getDataDokter()
    {
        $dataDokter = Dokter::all();

        return response()->json([
            'Data Dokter' => $dataDokter,
        ]);
    }

    public function storeDataTestimoni(Request $request)
    {
        $request->validate([
            'pasien_id' => ['required', 'exists:pasien,id'],
            'nama_testimoni' => ['required', 'exists:pasien,nama_pasien'],
            'umur' => ['required', 'integer'],
            'pekerjaan' => ['required', 'string'],
            'isi_testimoni' => ['required', 'string'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'], // 2MB
            'video' => ['nullable', 'mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime', 'max:20480'], // 20MB
        ]);

        // Inisialisasi path null
        $pathFoto = null;
        $pathVideo = null;

        // Upload foto jika ada
        if ($request->hasFile('foto')) {
            $pathFoto = $request->file('foto')->store('testimoni/foto', 'public');
        }

        // Upload video jika ada
        if ($request->hasFile('video')) {
            $pathVideo = $request->file('video')->store('testimoni/video', 'public');
        }

        // Simpan ke database
        $dataTestimoni = Testimoni::create([
            'pasien_id' => $request->pasien_id,
            'nama_testimoni' => $request->nama_testimoni,
            'umur' => $request->umur,
            'pekerjaan' => $request->pekerjaan,
            'isi_testimoni' => $request->isi_testimoni,
            'foto' => $pathFoto,
            'video' => $pathVideo,
        ]);

        return response()->json(['Data Testimoni' => $dataTestimoni]);
    }

    public function getDataKunjunganDokter()
    {
        $idUser = Auth::user()->id;
        $dataDokter = Dokter::where('user_id', $idUser)->get();

        $dataKunjungan = Kunjungan::with('dokter', 'pasien')->where('dokter_id', $dataDokter)->get();

        return response()->json([
            'Data Orderan Dokter' => $dataKunjungan,
        ]);
    }

    public function createKunjungan(Request $request)
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
}
