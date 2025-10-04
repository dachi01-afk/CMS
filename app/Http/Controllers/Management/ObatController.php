<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Obat;
use Illuminate\Http\Request;

class ObatController extends Controller
{
    public function createObat(Request $request)
    {
        $request->validate([
            'nama_obat' => ['required'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'dosis' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ]);

        $dataObat = Obat::create([
            'nama_obat' => $request->nama_obat,
            'jumlah' => $request->jumlah,
            'dosis' => $request->dosis,
        ]);

        return response()->json(['status' => 200, 'data' => $dataObat, 'message' => 'Data Berhasil Di Tambahkan']);
    }

    public function updateObat(Request $request)
    {
        $request->validate([
            'nama_obat' => ['required'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'dosis' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ]);

        $dataObat = Obat::where('id', $request->id)->firstOrFail();

        $dataObat->update(attributes: [
            'nama_obat' => $request->nama_obat,
            'jumlah' => $request->jumlah,
            'dosis' => $request->dosis,
        ]);

        return response()->json(['status' => 200, 'data' => $dataObat, 'massage' => 'Data Berhasil Di Update']);
    }

    public function deleteObat(Request $request)
    {
        $dataObat = Obat::findOrFail($request->id);

        $dataObat->delete();

        return response()->json(['status' => 200, 'data' => $dataObat, 'massage' => 'Data Berhasil Dihapus']);
    }
}
