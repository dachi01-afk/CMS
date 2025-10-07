<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\JadwalDokter;
use App\Models\Testimoni;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Foreach_;

class APIController extends Controller
{
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

    public function getDataSpesialisasiDokter()
    {
        $dataSpesialis = Dokter::select('spesialisasi')->get();

        $dataDokterSpesialis = Dokter::where('spesialisasi', 'Psikiatri')->get();

        return response()->json([
            'Data Spesialis Dokter' => $dataSpesialis,
            'Data Dokter Spesialisasi' => $dataDokterSpesialis,
        ]);
    }

    public function getDataTestimoni()
    {
        $dataTestimoni = Testimoni::get();

        return response()->json([
            'Data Testimoni' => $dataTestimoni,
        ]);
    }

    public function getDataDokter()
    {
        $dataDokter = Dokter::all();

        return response()->json([
            'Data Dokter' => $dataDokter,
        ]);
    }
}
