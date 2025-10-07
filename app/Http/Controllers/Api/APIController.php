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
}
