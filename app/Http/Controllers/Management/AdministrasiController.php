<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Administrasi;
use Illuminate\Http\Request;

class AdministrasiController extends Controller
{
    public function createAdministrasi(Request $request)
    {
        $request->validate([
            'pembayaran_id' => ['required', 'exists:pembayaran,id'],
            'laporan' => ['required'],
            'tarif' => ['required'],
            'periode' => ['required'],
        ]);

        $dataAdministrasi = Administrasi::create([
            'pembayaran_id' => $request->pembayaran_id,
            'laporan' => $request->laporan,
            'tarif' => $request->tarif,
            'periode' => $request->periode,
        ]);

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataAdministrasi,
            'message' => 'Data Berhasil Ditambahkan',
        ]);
    }

    public function updateAdministrasi(Request $request)
    {
        $request->validate([
            'pembayaran_id' => ['required', 'exists:pembayaran,id'],
            'laporan' => ['required'],
            'tarif' => ['required'],
            'periode' => ['required'],
        ]);

        $dataAdministrasi = Administrasi::findOrFail($request->id);

        $dataAdministrasi->update([
            'pembayaran_id' => $request->pembayaran_id,
            'laporan' => $request->laporan,
            'tarif' => $request->tarif,
            'periode' => $request->periode,
        ]);

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataAdministrasi,
            'message' => 'Data Berhasil Diupdate',
        ]);
    }

    public function deleteAdministrasi(Request $request)
    {
        $dataAdministrasi = Administrasi::findOrFail($request->id);

        $dataAdministrasi->delete();

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataAdministrasi,
            'message' => 'Data Berhasil Dihapus',
        ]);
    }
}
