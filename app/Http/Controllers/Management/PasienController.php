<?php

namespace App\Http\Controllers\Management;

use App\Models\Pasien;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PasienController extends Controller
{
    public function createPasien(Request $request)
    {
        $request->validate([
            'nama_pasien'   => ['required', 'string', 'max:255'],
            'alamat'        => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'jenis_kelamin' => ['required', 'in:Laki-laki,Perempuan'],
        ]);

        $dataPasien = Pasien::create([
            'nama_pasien'   => $request->nama_pasien,
            'alamat'        => $request->alamat,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditambahkan',
            'data'    => $dataPasien
        ], 201);
    }

    public function getPasienById($id)
    {
        $pasien = Pasien::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $pasien
        ]);
    }

    public function updatePasien(Request $request, $id)
    {
        $dataPasien = Pasien::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_pasien'   => 'required|string|max:255',
            'alamat'        => 'nullable|string|max:255',
            'tanggal_lahir' => 'required|date|before:today',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Jika spesialisasi kosong, gunakan yang lama
        $jenis_kelamin = $request->filled('jenis_kelamin')
            ? $request->jenis_kelamin
            : $dataPasien->jenis_kelamin;



        $dataPasien->update([
            'nama_pasien' => $request->nama_pasien,
            'alamat' => $request->alamat,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $jenis_kelamin,
        ]);

        return response()->json(['status' => 200, 'data' => $dataPasien, 'message' => 'Data Berhasil Di Update']);
    }

    public function deletePasien(Request $request, $id)
    {
        $dataPasien = Pasien::findOrFail($request->id);

        $dataPasien->delete();

        return response()->json(['status' => 200, 'data' => $dataPasien, 'message' => 'Data Berhasil Dihapus']);
    }
}
