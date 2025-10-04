<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\JadwalDokter;
use Illuminate\Http\Request;

class JadwalDokterController extends Controller
{
    public function createJadwalDokter(Request $request)
    {
        $request->validate([
            'dokter_id' => ['required', 'exists:dokter,id'],
            'hari' => ['required', 'array'], // karena field kamu json
            'hari.*' => ['in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu'],
            'jam_awal'  => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_awal'],
        ]);

        $dataDokter = Dokter::findOrFail($request->dokter_id);

        $namaDokter = $dataDokter->dokter->nama_dokter;

        if (!$dataDokter) {
            return response()->json(['success' => false, 'message' => 'Data Dokter Tidak Ada'], 400);
        }

        $dataJadwalDokter = JadwalDokter::create([
            'dokter_id' => $request->dokter_id,
            'hari' => $request->hari,
            'jam_awal' => $request->jam_awal,
            'jam_selesai' => $request->jam_selesai,
        ]);

        return response()->json([
            'success' => true,
            'data' => $dataJadwalDokter,
            'message' => 'Data Jadwal Dokter Dengan Nama ' . $namaDokter . ' Berhasil Ditambahkan',
        ]);
    }

    public function updateJadwalDokter(Request $request)
    {
        $request->validate([
            'dokter_id' => ['required', 'exists:dokter,id'],
            'hari' => ['required', 'array'], // karena field kamu json
            'hari.*' => ['in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu'],
            'jam_awal'  => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_awal'],
        ]);

        $dataJadwalDokter = JadwalDokter::with(['dokter'])->findOrFail($request->id);

        $namaDokter = $dataJadwalDokter->dokter->nama_dokter;

        if (isset($dataJadwalDokter->user_id)) {
            return response()->json(['success' => false, 'message' => 'Data Dokter Tidak Ada'], 400);
        }

        $dataJadwalDokter->update([
            'dokter_id' => $request->dokter_id,
            'hari' => $request->hari,
            'jam_awal' => $request->jam_awal,
            'jam_selesai' => $request->jam_selesai,
        ]);

        return response()->json([
            'success' => true,
            'data' => $dataJadwalDokter,
            'message' => 'Data Jadwal Dokter Dengan Nama ' . $namaDokter . ' Berhasil Diupdate',
        ]);
    }

    public function deleteJadwalDokter(Request $request)
    {
        $dataJadwalDokter = JadwalDokter::with('dokter')->findOrFail($request->id);

        $namaDokter = $dataJadwalDokter->dokter->nama_dokter;

        $dataJadwalDokter->delete();

        return response()->json([
            'success' => true,
            'data' => $dataJadwalDokter,
            'message' => 'Data Jadwal Dokter Dengan Nama ' . $namaDokter . ' Berhasil Dihapus',
        ]);
    }
}
