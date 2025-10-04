<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\ResepObat;
use Illuminate\Http\Request;

class ResepObatController extends Controller
{
    public function readDataResepObat()
    {
        $dataResepObat = ResepObat::with('resep', 'obat')->get();

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataResepObat,
            'message' => 'Data Resep Obat Berhasil Ditampilkan',
        ]);
    }

    public function createResepObat(Request $request)
    {
        $request->validate([
            'resep_id' => ['required', 'exists:resep,id'],
            'obat_id' => ['required', 'exists:obat,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'dosis' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ]);

        $dataResepObat = ResepObat::create([
            'resep_id' => $request->resep_id,
            'obat_id' => $request->obat_id,
            'jumlah' => $request->jumlah,
            'dosis' => $request->dosis,
        ]);

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataResepObat,
            'message' => 'Data Resep Obat Berhasil Ditambahkan',
        ]);
    }

    public function updateResepObat(Request $request)
    {
        $request->validate([
            'resep_id' => ['required', 'exists:resep,id'],
            'obat_id' => ['required', 'exists:obat,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'dosis' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ]);

        $dataResepObat = ResepObat::findOrFail($request->id);

        $dataResepObat->update([
            'resep_id' => $request->resep_id,
            'obat_id' => $request->obat_id,
            'jumlah' => $request->jumlah,
            'dosis' => $request->dosis,
        ]);

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataResepObat,
            'message' => 'Data Resep Obat Berhasil Diupdate',
        ]);
    }

    public function deleteResepObat(Request $request)
    {
        $dataResepObat = ResepObat::findOrFail($request->id);
        $dataResepObat->delete();

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataResepObat,
            'message' => 'Data Resep Obat Berhasil Dihapus',
        ]);
    }
}
