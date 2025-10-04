<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Apoteker;
use App\Models\Kunjungan;
use App\Models\Resep;
use Illuminate\Http\Request;

class ResepController extends Controller
{
    public function readDataResep()
    {
        $dataResep = Resep::with('kunjungan', 'apoteker')->get();

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataResep,
            'message' => 'Data Resep Berhasil Ditampilkan',
        ]);
    }

    public function createDataResep(Request $request)
    {
        $request->validate([
            'kunjungan_id' => ['required', 'exists:kunjungan,id'],
            'apoteker_id' => ['required', 'exists:apoteker,id'],
        ]);

        $dataResep = Resep::create([
            'kunjungan_id' => $request->kunjungan_id,
            'apoteker_id' => $request->apoteker_id,
        ]);

        return response()->json(([
            'success' => true,
            'status' => 200,
            'data' => $dataResep,
            'message' => 'Data Resep Berhasil Ditambahkan',
        ]));
    }

    public function updateDataResep(Request $request)
    {
        $request->validate([
            'kunjungan_id' => ['required', 'exists:kunjungan,id'],
            'apoteker_id' => ['required', 'exists:apoteker,id'],
        ]);

        $dataResep = Resep::find($request->id);

        $dataResep->update([
            'kunjungan_id' => $request->kunjungan_id,
            'apoteker_id' => $request->apoteker_id,
        ]);

        return response()->json(([
            'success' => true,
            'status' => 200,
            'data' => $dataResep,
            'message' => 'Data Resep Berhasil Diupdate',
        ]));
    }

    public function deleteDataResep(Request $request)
    {
        $dataResep = Resep::findOrFail($request->id);

        $dataResep->delete();

        return response()->json(([
            'success' => true,
            'status' => 200,
            'data' => $dataResep,
            'message' => 'Data Resep Berhasil Dihapus',
        ]));
    }
}
