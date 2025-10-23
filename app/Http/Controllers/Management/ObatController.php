<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Obat;
use Illuminate\Http\Request;

class ObatController extends Controller
{
    public function createObat(Request $request)
    {
        // --- Ubah format total_harga dari teks jadi numeric ---
        $request->merge([
            'total_harga' => floatval(str_replace(['.', ','], ['', '.'], $request->total_harga))
        ]);

        $request->validate([
            'nama_obat' => ['required'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'dosis' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'total_harga' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ]);

        $dataObat = Obat::create([
            'nama_obat' => $request->nama_obat,
            'jumlah' => $request->jumlah,
            'dosis' => $request->dosis,
            'total_harga' => $request->total_harga,
        ]);

        return response()->json(['status' => 200, 'data' => $dataObat, 'message' => 'Data Berhasil Di Tambahkan']);
    }

    public function getObatById($id)
    {
        $Obat = Obat::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $Obat
        ]);
    }

    public function updateObat(Request $request, $id)
    {
        $dataObat = Obat::findOrFail($id);

        $request->merge([
            'total_harga' => floatval(str_replace(['.', ','], ['', '.'], $request->total_harga))
        ]);

        $request->validate([
            'nama_obat' => ['required'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'dosis' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'total_harga' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ]);


        $dataObat->update(attributes: [
            'nama_obat' => $request->nama_obat,
            'jumlah' => $request->jumlah,
            'dosis' => $request->dosis,
            'total_harga' => $request->total_harga,
        ]);

        return response()->json(['status' => 200, 'data' => $dataObat, 'massage' => 'Data Berhasil Di Update']);
    }

    public function deleteObat($id)
    {
        $dataObat = Obat::findOrFail($id);
        $dataObat->delete();
        return response()->json(['status' => 200, 'data' => $dataObat, 'massage' => 'Data Berhasil Dihapus']);
    }
}
