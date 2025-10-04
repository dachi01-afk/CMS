<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\EMR;
use Illuminate\Http\Request;

class EMRController extends Controller
{
    public function createEMR(Request $request)
    {
        $request->validate([
            'kunjungan_id' => ['required', 'exists:kunjungan,id'],
            'riwayat_penyakit' => ['required'],
            'alergi' => ['required'],
            'hasil_periksa' => ['required'],
        ]);

        $dataEMR = EMR::create([
            'kunjungan_id' => $request->kunjungan_id,
            'riwayat_penyakit' => $request->riwayat_penyakit,
            'alergi' => $request->alergi,
            'hasil_periksa' => $request->hasil_periksa,
        ]);

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataEMR,
            'message' => 'Data EMR Berhasil Ditambahkan',
        ]);
    }

    public function updateEMR(Request $request)
    {
        $request->validate([
            'kunjungan_id' => ['required', 'exists:kunjungan,id'],
            'riwayat_penyakit' => ['required'],
            'alergi' => ['required'],
            'hasil_periksa' => ['required'],
        ]);

        $dataEMR = EMR::findOrFail($request->id);

        $dataEMR->update([
            'kunjungan_id' => $request->kunjungan_id,
            'riwayat_penyakit' => $request->riwayat_penyakit,
            'alergi' => $request->alergi,
            'hasil_periksa' => $request->hasil_periksa,
        ]);

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataEMR,
            'message' => 'Data EMR Berhasil Diupdate',
        ]);
    }

    public function deleteEMR(Request $request)
    {
        $dataEMR = EMR::findOrFail($request->id);

        $dataEMR->delete();

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataEMR,
            'message' => 'Data EMR Berhasil Dihapus',
        ]);
    }
}
