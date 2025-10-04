<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pasien;
use Illuminate\Http\Request;

class PasienController extends Controller
{
    public function createPasien(Request $request)
    {
        $request->validate([
            'nama_pasien' => ['required'],
            'alamat' => ['required'],
            'tanggal_lahir' => ['required', 'date'],
        ]);

        $dataPasien = Pasien::create([
            'nama_pasien' => $request->nama_pasien,
            'alamat' => $request->alamat,
            'tanggal_lahir' => $request->tanggal_lahir,
        ]);

        return response()->json(['status' => 200, 'data' => $dataPasien, 'message' => 'Data Berhasil Di Tambahkan']);
    }

    public function updatePasien(Request $request)
    {
        $request->validate([
            'nama_pasien' => ['required'],
            'alamat' => ['required'],
            'tanggal_lahir' => ['required', 'date'],
        ]);

        $dataPasien = Pasien::where('id', $request->id)->firstOrFail();

        $dataPasien->update([
            'nama_pasien' => $request->nama_pasien,
            'alamat' => $request->alamat,
            'tanggal_lahir' => $request->tanggal_lahir,
        ]);

        return response()->json(['status' => 200, 'data' => $dataPasien, 'massage' => 'Data Berhasil Di Update']);
    }

    public function deletePasien(Request $request)
    {
        $dataPasien = Pasien::where('id', $request->id);

        $dataPasien->delete();

        return response()->json(['status' => 200, 'data' => $dataPasien, 'massage' => 'Data Berhasil Dihapus']);
    }
}
