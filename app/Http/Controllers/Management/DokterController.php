<?php

namespace App\Http\Controllers\Management;

use App\Models\Dokter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DokterController extends Controller
{

    public function getDokterById($id)
    {
        $dokter = Dokter::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $dokter
        ]);
    }

    public function createDokter(Request $request)
    {
        $request->validate([
            'nama_dokter'  => 'required|string|max:100',
            'user_id'       => 'required|exists:user,id',
            'spesialisasi' => 'required|string|in:Determatologi,Psikiatri,Onkologi,Kardiologi',
            'email'        => 'required|email|unique:dokter,email',
            'no_hp'        => 'required|string|max:15',
        ]);

        $dokter = Dokter::create([
            'nama_dokter'  => $request->nama_dokter,
            'user_id'      => $request->user_id,
            'spesialisasi' => $request->spesialisasi,
            'email'        => $request->email,
            'no_hp'        => $request->no_hp,
        ]);

        return response()->json([
            'success' => true,
            'data' => $dokter,
            'message' => 'Data dokter berhasil ditambahkan'
        ], 201);
    }

    public function updateDokter(Request $request, $id)
    {
        $dokter = Dokter::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_dokter'   => 'required|string|max:255',
            'spesialisasi'  => 'nullable|string|max:100',
            'email'         => 'required|email|unique:dokter,email,' . $dokter->id,
            'no_hp'         => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Jika spesialisasi kosong, gunakan yang lama
        $spesialisasi = $request->filled('spesialisasi')
            ? $request->spesialisasi
            : $dokter->spesialisasi;

        $dokter->update([
            'nama_dokter'  => $request->nama_dokter,
            'spesialisasi' => $spesialisasi,
            'email'        => $request->email,
            'no_hp'        => $request->no_hp,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data dokter berhasil diperbarui.',
            'data'    => $dokter,
        ]);
    }


    public function deleteDokter($id)
    {
        $dataDokter = Dokter::findOrFail($id);

        $dataDokter->delete();

        return response()->json([
            'success' => true,
            'data' => $dataDokter,
            'message' => 'Data Berhasil Di Dihapus',
        ], 200);
    }
}
