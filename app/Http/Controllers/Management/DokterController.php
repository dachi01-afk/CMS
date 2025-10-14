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
use Intervention\Image\Laravel\Facades\Image;


class DokterController extends Controller
{

    public function createDokter(Request $request)
    {
        $request->validate([
            'username_dokter'    => 'required|string|max:255|unique:user,username',
            'poli_id'            => ['required', 'exists:poli,id'],
            'nama_dokter'        => 'required|string|max:255',
            'email_akun_dokter'  => 'required|email|max:255|unique:user,email',
            'spesialis_dokter'   => 'required|integer|exists:jenis_spesialis,id',
            'password_dokter'    => 'required|string|min:8|confirmed',
            'foto_dokter'        => 'nullable|file|mimetypes:image/jpeg,image/png,image/gif,image/webp,image/svg+xml|max:5120',
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

        // 2️⃣ Upload + Kompres Foto
        $fotoPath = null;
        if ($request->hasFile('foto_dokter')) {
            $file = $request->file('foto_dokter');

            // ubah jfif ke jpg agar bisa di-encode
            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension === 'jfif') {
                $extension = 'jpg';
            }

            $fileName = 'dokter_' . time() . '.' . $extension;
            $path = 'dokter/' . $fileName;

            if ($extension === 'svg') {
                Storage::disk('public')->put($path, file_get_contents($file));
            } else {
                // ✅ Gambar raster → resize & kompres
                $image = Image::read($file);
                $image->scale(width: 800);
                Storage::disk('public')->put($path, (string) $image->encodeByExtension($extension, quality: 80));
            }

            $fotoPath = $path;
        }

        // 3️⃣ Simpan ke tabel dokter
        Dokter::create([
            'user_id'            => $user->id,
            'poli_id'            => $request->poli_id,
            'nama_dokter'        => $request->nama_dokter,
            'jenis_spesialis_id' => $request->spesialis_dokter,
            'foto_dokter'        => $fotoPath,
            'deskripsi_dokter'   => $request->deskripsi_dokter,
            'pengalaman'         => $request->pengalaman_dokter,
            'no_hp'              => $request->no_hp_dokter,
        ]);

        return response()->json(['success' => 'Data dokter berhasil ditambahkan.']);
    }


    public function getDokterById($id)
    {
        $data = Dokter::with('user', 'poli')->findOrFail($id);
        return response()->json(['data' => $data]);
    }


    public function updateDokter(Request $request)
    {
        $dokter = Dokter::findOrFail($request->edit_dokter_id);
        $user   = $dokter->user;

        $request->validate([
            'edit_username_dokter'    => 'required|string|max:255|unique:user,username,' . $user->id,
            'poli_id'              => ['required'],
            'edit_nama_dokter'        => 'required|string|max:255',
            'edit_email_akun_dokter'  => 'required|email|max:255|unique:user,email,' . $user->id,
            'edit_spesialis_dokter'   => 'required|integer|exists:jenis_spesialis,id',
            'edit_foto_dokter'        => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg,jfif|max:5120',
            'edit_no_hp_dokter'       => 'nullable|string|max:20',
            'edit_deskripsi_dokter'   => 'nullable|string',
            'edit_pengalaman_dokter'  => 'nullable|string|max:255',
            'edit_password_dokter'    => 'nullable|string|min:8|confirmed',
        ]);;


        // Update user account
        $user->username = $request->input('edit_username_dokter');
        $user->email    = $request->input('edit_email_akun_dokter');

        if ($request->filled('edit_password_dokter')) {
            $user->password = Hash::make($request->input('edit_password_dokter'));
        }

        // Handle foto upload (jika ada)
        $fotoPath = null;
        if ($request->hasFile('edit_foto_dokter')) {
            $file = $request->file('edit_foto_dokter');

            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension === 'jfif') {
                $extension = 'jpg';
            }

            $fileName = 'dokter_' . time() . '.' . $extension;
            $path = 'dokter/' . $fileName;

            if ($extension === 'svg') {
                Storage::disk('public')->put($path, file_get_contents($file));
            } else {
                // ✅ Gambar raster → resize & kompres
                $image = Image::read($file);
                $image->scale(width: 800);
                Storage::disk('public')->put($path, (string) $image->encodeByExtension($extension, quality: 80));
            }

            $fotoPath = $path;

            // opsional: hapus foto lama jika ada
            if ($dokter->foto_dokter && Storage::disk('public')->exists($dokter->foto_dokter)) {
                Storage::disk('public')->delete($dokter->foto_dokter);
            }
        }


        // 3️⃣ Update dokter
        $updateData = [
            'nama_dokter'        => $request->edit_nama_dokter,
            'jenis_spesialis_id' => $request->edit_spesialis_dokter,
            'deskripsi_dokter'   => $request->edit_deskripsi_dokter,
            'pengalaman'         => $request->edit_pengalaman_dokter,
            'no_hp'              => $request->edit_no_hp_dokter,
            'poli_id'              => $request->poli_id,
        ];

        $updateDataUser = ([
            'username' => $request->edit_username_dokter,
        ]);

        if ($fotoPath) {
            $updateData['foto_dokter'] = $fotoPath;
        }
        $dokter->update($updateData);
        $user->update($updateDataUser);

        return response()->json(['success' => 'Data dokter berhasil diperbarui.']);
    }


    public function deleteDokter($id)
    {
        $dokter = Dokter::findOrFail($id);
        $user = $dokter->user;

        // Hapus foto jika ada
        if ($dokter->foto_dokter && Storage::disk('public')->exists($dokter->foto_dokter)) {
            Storage::disk('public')->delete($dokter->foto_dokter);
        }

        $dokter->delete();
        $user->delete();

        return response()->json(['success' => 'Data dokter berhasil dihapus.']);
    }
}
