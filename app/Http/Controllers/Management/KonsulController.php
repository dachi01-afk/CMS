<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Konsul;
use Illuminate\Http\Request;

class KonsulController extends Controller
{
    public function createKonsul(Request $request)
    {
        $request->validate([
            'kunjungan_id' => ['required', 'exists:kunjungan,id'],
            'diagnosa' => ['required'],
            'catatan' => ['required'],
        ]);

        $dataKonsul = Konsul::create([
            'kunjungan_id' => $request->kunjungan_id,
            'diagnosa' => $request->diagnosa,
            'catatan' => $request->catatan,
        ]);

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataKonsul,
            'message' => 'Data Konsultasi Berhasil Ditambahkan',
        ]);
    }

    public function updateKonsul(Request $request)
    {
        $request->validate([
            'kunjungan_id' => ['required', 'exists:kunjungan,id'],
            'diagnosa' => ['required'],
            'catatan' => ['required'],
        ]);

        $dataKonsul = Konsul::findOrFail($request->id);

        $dataKonsul->update([
            'kunjungan_id' => $request->kunjungan_id,
            'diagnosa' => $request->diagnosa,
            'catatan' => $request->catatan,
        ]);

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataKonsul,
            'message' => 'Data Konsultasi Berhasil Diupdate',
        ]);
    }

    public function deleteKonsul(Request $request)
    {
        $dataKonsul = Konsul::findOrFail($request->id);
        $dataKonsul->delete();

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataKonsul,
            'message' => 'Data Konsultasi Berhasil Diupdate',
        ]);
    }
}
