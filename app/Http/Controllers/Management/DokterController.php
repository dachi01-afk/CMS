<?php

namespace App\Http\Controllers\Management;

use App\Models\User;
use App\Models\Dokter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class DokterController extends Controller
{

    public function createDokter(Request $request)
    {
        $request->validate([
            'username'           => 'required|string|max:255|unique:users,username',
            'password'           => 'required|string|min:8|confirmed',
            'email'              => 'required|email|max:255|unique:users,email',
            'nama_dokter'        => 'required|string|max:255',
            'jenis_spesialis_id' => 'required|integer|exists:jenis_spesialis,id',
            'foto'               => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'deskripsi_dokter'   => 'nullable|string',
            'pengalaman'         => 'nullable|string|max:255',
            'no_hp'              => 'nullable|string|max:20',
        ]);

        // Simpan ke tabel user
        $user = User::create([
            'username' => $request->username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'Dokter',
        ]);

        // Simpan ke tabel dokter
        Dokter::create([
            'user_id'       => $user->id,
            'nama_dokter'   => $request->nama_dokter,
            'jenis_spesialis_id'  => $request->jenis_spesialis_id,
            'email'         => $request->email,
            'foto'         => $request->foto,
            'deskripsi_dokter'   => $request->deskripsi_dokter,
            'pengalaman'         => $request->pengalaman,
            'no_hp'         => $request->no_hp,
        ]);

        return response()->json(['success' => 'Data dokter berhasil ditambahkan.']);
    }

    public function getDokterById($id)
    {
        $dokter = Dokter::with('user')->findOrFail($id);
        return response()->json($dokter);
    }

    public function updateDokter(Request $request, $id)
    {
        $dokter = Dokter::findOrFail($id);
        $user   = $dokter->user;

        $request->validate([
            'username'      => 'required|string|max:255',
            'email'         => 'required|email|unique:user,email,' . $user->id,
            'nama_dokter'   => 'required|string|max:255',
            'spesialisasi'  => 'required|string',
            'no_hp'         => 'nullable|string|max:20',
        ]);

        $user->update([
            'username' => $request->username,
            'email'    => $request->email,
        ]);

        $dokter->update([
            'nama_dokter'  => $request->nama_dokter,
            'spesialisasi' => $request->spesialisasi,
            'email'        => $request->email,
            'no_hp'        => $request->no_hp,
        ]);

        return response()->json(['success' => 'Data dokter berhasil diperbarui.']);
    }

    public function deleteDokter($id)
    {
        $dokter = Dokter::findOrFail($id);
        $user = $dokter->user;
        $dokter->delete();
        $user->delete();

        return response()->json(['success' => 'Data dokter berhasil dihapus.']);
    }
}
