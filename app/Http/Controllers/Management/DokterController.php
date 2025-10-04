<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DokterController extends Controller
{
    public function createDokter(Request $request)
    {
        $request->validate([
            'nama_dokter'   => ['required', 'string', 'max:100'],
            'spesialisasi'  => ['required', 'string', 'max:100'],
            'email'         => ['required', 'email', 'unique:dokters,email'],
            'no_hp'         => ['required', 'regex:/^[0-9]+$/', 'digits_between:10,15'],
        ]);

        $dataDokter = Dokter::create([
            'nama_dokter'  => $request->nama_dokter,
            'spesialisasi' => $request->spesialisasi,
            'email'        => $request->email,
            'no_hp'        => $request->no_hp,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $dataDokter,
            'message' => 'Data Berhasil Ditambahkan',
        ], 201); // 201 Created
    }

    public function updateDokter(Request $request)
    {
        $userId = Auth::id();

        $dataDokter = Dokter::where('user_id', $userId)->firstOrFail();

        $request->validate([
            'nama_dokter'   => ['required', 'string', 'max:100'],
            'spesialisasi'  => ['required', 'string', 'max:100'],
            'email'         => ['required', 'email', 'unique:dokters,email,' . $dataDokter->id],
            'no_hp'         => ['required', 'regex:/^[0-9]+$/', 'digits_between:10,15'],
        ]);

        $dataDokter->update([
            'nama_dokter'  => $request->nama_dokter,
            'spesialisasi' => $request->spesialisasi,
            'email'        => $request->email,
            'no_hp'        => $request->no_hp,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $dataDokter,
            'message' => 'Data berhasil diupdate',
        ], 200);
    }


    public function deleteDokter(Request $request)
    {
        $dataDokter = Dokter::findOrFail($request->id);

        $dataDokter->delete();

        return response()->json([
            'status' => 200,
            'data' => $dataDokter,
            'message' => 'Data Berhasil Dihapus'
        ]);
    }
}
