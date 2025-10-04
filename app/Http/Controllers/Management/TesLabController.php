<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\TesLab;
use Illuminate\Http\Request;

class TesLabController extends Controller
{
    public function createTesLab(Request $request)
    {
        $request->validate([
            'kunjungan_id' => ['required', 'exists:kunjungan,id'],
            'jenis_tes' => ['required', 'array'], // karena field kamu json
            'hasil_tes'  => ['required'],
            'tanggal_tes' => ['required', 'date'],
        ]);

        $dataTesLab = TesLab::create([
            'kunjungan_id' => $request->kunjungan_id,
            'jenis_tes' => $request->jenis_tes,
            'hasil_tes' => $request->hasil_tes,
            'tanggal_tes' => $request->tanggal_tes,
        ]);

        return response()->json([
            'success' => true,
            'data' => $dataTesLab,
            'message' => 'Data Tes Lab Berhasil Ditambahkan',
        ]);
    }

    public function updateTesLab(Request $request)
    {
        $request->validate([
            'kunjungan_id' => ['required', 'exists:kunjungan,id'],
            'jenis_tes' => ['required', 'array'], // karena field kamu json
            'hasil_tes'  => ['required'],
            'tanggal_tes' => ['required', 'date'],
        ]);

        $dataTesLab = TesLab::findOrFail($request->id);

        $dataTesLab->update([
            'kunjungan_id' => $request->kunjungan_id,
            'jenis_tes' => $request->jenis_tes,
            'hasil_tes' => $request->hasil_tes,
            'tanggal_tes' => $request->tanggal_tes,
        ]);

        return response()->json([
            'success' => true,
            'data' => $dataTesLab,
            'message' => 'Data Tes Lab Berhasil Diupdate',
        ]);
    }

    public function deleteTesLab(Request $request)
    {
        $dataTesLab = TesLab::findOrFail($request->id);

        $dataTesLab->delete();

        return response()->json([
            'success' => true,
            'data' => $dataTesLab,
            'message' => 'Data Tes Lab Berhasil Dihapus',
        ]);
    }
}
