<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PasienController extends Controller
{
    public function createPasien(Request $request)
    {
        $request->validate([
            'nama_pasien'   => ['required', 'string', 'max:255'],
            'alamat'        => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
        ]);

        $dataPasien = Pasien::create([
            'nama_pasien'   => $request->nama_pasien,
            'alamat'        => $request->alamat,
            'tanggal_lahir' => $request->tanggal_lahir,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditambahkan',
            'data'    => $dataPasien
        ], 201);
    }

    public function updatePasien(Request $request)
    {
        $request->validate([
            'nama_pasien'   => ['required', 'string', 'max:255'],
            'alamat'        => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
        ]);

        $userId = Auth::id();

        $dataPasien = Pasien::where('user_id', $userId)->firstOrFail();

        $dataPasien->update([
            'nama_pasien' => $request->nama_pasien,
            'alamat' => $request->alamat,
            'tanggal_lahir' => $request->tanggal_lahir,
        ]);

        return response()->json(['status' => 200, 'data' => $dataPasien, 'message' => 'Data Berhasil Di Update']);
    }

    public function deletePasien(Request $request)
    {
        $dataPasien = Pasien::findOrFail($request->id);

        $dataPasien->delete();

        return response()->json(['status' => 200, 'data' => $dataPasien, 'message' => 'Data Berhasil Dihapus']);
    }
}
