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
            'username_dokter'    => 'required|string|max:255|unique:user,username',
            'nama_dokter'        => 'required|string|max:255',
            'email_akun_dokter'  => 'required|email|max:255|unique:user,email',
            'spesialis_dokter'   => 'required|integer|exists:jenis_spesialis,id',
            'email_dokter'       => 'required|email|max:255|unique:dokter,email',
            'password_dokter'    => 'required|string|min:8|confirmed',
            // 'foto'               => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'deskripsi_dokter'   => 'nullable|string',
            'pengalaman_dokter'  => 'nullable|string|max:255',
            'no_hp_dokter'       => 'nullable|string|max:20',
        ]);

        // 1️⃣ Simpan ke tabel user
        $user = User::create([
            'username' => $request->username_dokter,
            'email'    => $request->email_akun_dokter,
            'password' => Hash::make($request->password_dokter),
            'role'     => 'Dokter',
        ]);

        // // 2️⃣ Upload foto (jika ada)
        // $fotoPath = null;
        // if ($request->hasFile('foto')) {
        //     $fotoPath = $request->file('foto')->store('dokter', 'public');
        // }

        // 3️⃣ Simpan ke tabel dokter
        Dokter::create([
            'user_id'            => $user->id,
            'nama_dokter'        => $request->nama_dokter,
            'jenis_spesialis_id' => $request->spesialis_dokter,
            'email'              => $request->email_dokter,
            // 'foto'               => $fotoPath,
            'deskripsi_dokter'   => $request->deskripsi_dokter,
            'pengalaman'         => $request->pengalaman_dokter,
            'no_hp'              => $request->no_hp_dokter,
        ]);

        return response()->json(['success' => 'Data dokter berhasil ditambahkan.']);
    }


    public function getDokterById($id)
    {
        $data = Dokter::with('user')->findOrFail($id);
        return response()->json(['data' => $data]);
    }


    public function updateDokter(Request $request, $id)
    {
        $dokter = Dokter::findOrFail($id);
        $user   = $dokter->user;

        $request->validate([
            'edit_username_dokter'    => 'required|string|max:255|unique:user,username,' . $user->id,
            'edit_nama_dokter'        => 'required|string|max:255',
            'edit_email_akun_dokter'  => 'required|email|max:255|unique:user,email,' . $user->id,
            'edit_spesialis_dokter'   => 'required|integer|exists:jenis_spesialis,id',
            'edit_email_dokter'       => 'required|email|max:255|unique:dokter,email,' . $dokter->id,
            // 'edit_foto'               => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'edit_no_hp_dokter'       => 'nullable|string|max:20',
            'edit_deskripsi_dokter'   => 'nullable|string',
            'edit_pengalaman_dokter'  => 'nullable|string|max:255',
            'edit_password_dokter'    => 'nullable|string|min:8|confirmed',
        ]);;


        // Update user
        $user->username = $request->edit_username_dokter;
        $user->email    = $request->edit_email_akun_dokter;

        if ($request->filled('edit_password_dokter')) {
            $user->password = Hash::make($request->edit_password_dokter);
        }

        // // 2️⃣ Handle foto
        // $fotoPath = $dokter->foto;
        // if ($request->hasFile('foto')) {
        //     // hapus foto lama jika ada
        //     if ($dokter->foto && Storage::disk('public')->exists($dokter->foto)) {
        //         Storage::disk('public')->delete($dokter->foto);
        //     }
        //     $fotoPath = $request->file('foto')->store('dokter', 'public');
        // }

        // 3️⃣ Update dokter
        $dokter->update([
            'nama_dokter'        => $request->edit_nama_dokter,
            'jenis_spesialis_id' => $request->edit_spesialis_dokter,
            'email'              => $request->edit_email_dokter,
            // 'foto'               => $fotoPath,
            'deskripsi_dokter'   => $request->edit_deskripsi_dokter,
            'pengalaman'         => $request->edit_pengalaman_dokter,
            'no_hp'              => $request->edit_no_hp_dokter,
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
