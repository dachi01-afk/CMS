<?php

namespace App\Http\Controllers\Management;

use App\Models\User;
use App\Models\Dokter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class DokterController extends Controller
{

    public function createDokter(Request $request)
    {
        $request->validate([
            'username'           => 'required|string|max:255|unique:user,username',
            'password'           => 'required|string|min:8|confirmed',
            'email'              => 'required|email|max:255|unique:user,email|unique:dokter,email',
            'nama_dokter'        => 'required|string|max:255',
            'jenis_spesialis_id' => 'required|integer|exists:jenis_spesialis,id',
            'foto'               => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'deskripsi_dokter'   => 'nullable|string',
            'pengalaman'         => 'nullable|string|max:255',
            'no_hp'              => 'nullable|string|max:20',
        ]);

        // 1️⃣ Simpan ke tabel user
        $user = User::create([
            'username' => $request->username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'Dokter',
        ]);

        // 2️⃣ Upload foto (jika ada)
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('dokter', 'public');
        }

        // 3️⃣ Simpan ke tabel dokter
        Dokter::create([
            'user_id'            => $user->id,
            'nama_dokter'        => $request->nama_dokter,
            'jenis_spesialis_id' => $request->jenis_spesialis_id,
            'email'              => $request->email,
            'foto'               => $fotoPath,
            'deskripsi_dokter'   => $request->deskripsi_dokter,
            'pengalaman'         => $request->pengalaman,
            'no_hp'              => $request->no_hp,
        ]);

        return response()->json(['success' => 'Data dokter berhasil ditambahkan.']);
    }


    public function getDokterById($id)
    {
        $dokter = Dokter::with(['user', 'spesialis'])->findOrFail($id);
        return response()->json($dokter);
    }


    public function updateDokter(Request $request, $id)
    {
        $dokter = Dokter::findOrFail($id);
        $user   = $dokter->user;

        $request->validate([
            'username'           => 'required|string|max:255|unique:user,username,' . $user->id,
            'email'              => 'required|email|max:255|unique:user,email,' . $user->id . '|unique:dokter,email,' . $dokter->id,
            'nama_dokter'        => 'required|string|max:255',
            'jenis_spesialis_id' => 'required|integer|exists:jenis_spesialis,id',
            'foto'               => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'deskripsi_dokter'   => 'nullable|string',
            'pengalaman'         => 'nullable|string|max:255',
            'no_hp'              => 'nullable|string|max:20',
            'password'           => 'nullable|string|min:8|confirmed',
        ]);

        // 1️⃣ Update user
        $userData = [
            'username' => $request->username,
            'email'    => $request->email,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        // 2️⃣ Handle foto
        $fotoPath = $dokter->foto;
        if ($request->hasFile('foto')) {
            // hapus foto lama jika ada
            if ($dokter->foto && Storage::disk('public')->exists($dokter->foto)) {
                Storage::disk('public')->delete($dokter->foto);
            }
            $fotoPath = $request->file('foto')->store('dokter', 'public');
        }

        // 3️⃣ Update dokter
        $dokter->update([
            'nama_dokter'        => $request->nama_dokter,
            'jenis_spesialis_id' => $request->jenis_spesialis_id,
            'email'              => $request->email,
            'foto'               => $fotoPath,
            'deskripsi_dokter'   => $request->deskripsi_dokter,
            'pengalaman'         => $request->pengalaman,
            'no_hp'              => $request->no_hp,
        ]);

        return response()->json(['success' => 'Data dokter berhasil diperbarui.']);
    }


    public function deleteDokter($id)
    {
        $dokter = Dokter::findOrFail($id);
        $user = $dokter->user;

        // Hapus foto jika ada
        if ($dokter->foto && Storage::disk('public')->exists($dokter->foto)) {
            Storage::disk('public')->delete($dokter->foto);
        }

        $dokter->delete();
        $user->delete();

        return response()->json(['success' => 'Data dokter berhasil dihapus.']);
    }
}
