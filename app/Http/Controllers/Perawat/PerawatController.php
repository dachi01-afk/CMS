<?php

namespace App\Http\Controllers\Perawat;

use App\Http\Controllers\Controller;
use App\Models\Perawat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class PerawatController extends Controller
{
    public function dashboard()
    {
        return view('perawat.dashboard');
    }

    public function createPerawat(Request $request)
    {
        try {
            // 🧩 Validasi input
            $request->validate([
                'foto_perawat'     => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg,jfif|max:5120',
                'username_perawat' => 'required|string|max:255|unique:user,username',
                'nama_perawat'     => 'required|string|max:255',
                'email_perawat'    => 'required|email|unique:user,email',
                'no_hp_perawat'    => 'nullable|string|max:20',
                'password_perawat' => 'required|string|min:8|confirmed',
            ]);

            // 🧑‍💻 Buat user baru
            $user = User::create([
                'username' => $request->username_perawat,
                'email'    => $request->email_perawat,
                'password' => Hash::make($request->password_perawat),
                'role'     => 'Perawat',
            ]);

            // 📸 Upload + Kompres Foto
            $fotoPath = null;
            if ($request->hasFile('foto_perawat')) {
                $file = $request->file('foto_perawat');

                $extension = strtolower($file->getClientOriginalExtension());
                if ($extension === 'jfif') {
                    $extension = 'jpg';
                }

                $fileName = 'perawat_' . time() . '.' . $extension;
                $path = 'perawat/' . $fileName;

                if ($extension === 'svg') {
                    Storage::disk('public')->put($path, file_get_contents($file));
                } else {
                    $image = Image::read($file);
                    $image->scale(width: 800);
                    Storage::disk('public')->put($path, (string) $image->encodeByExtension($extension, quality: 80));
                }

                $fotoPath = $path;
            }

            // 🏥 Buat data kasir
            Perawat::create([
                'user_id'        => $user->id,
                'nama_perawat'  => $request->nama_perawat,
                'foto_perawat'  => $fotoPath,
                'no_hp_perawat' => $request->no_hp_perawat,
            ]);

            return response()->json(['message' => 'Data perawat berhasil ditambahkan.']);
        } catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
            // 🚫 File terlalu besar
            return response()->json([
                'message' => 'Ukuran file terlalu besar! Maksimal 5 MB.'
            ], 413);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // ⚠️ Validasi gagal
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // 💥 Error umum
            return response()->json([
                'message' => 'Tidak ada respon dari server.', // 🔥 ini pesan yang kamu mau
                'error_detail' => $e->getMessage(), // opsional, untuk debugging (bisa kamu hapus kalau gak mau tampil)
            ], 500);
        }
    }

    public function getPerawatById($id)
    {
        $data = Perawat::with('user')->findOrFail($id);
        return response()->json(['data' => $data]);
    }

    public function updatePerawat(Request $request, $id)
    {
        try {
            $perawat = Perawat::findOrFail($id);
            $user = $perawat->user;

            $request->validate([
                'edit_username_perawat'    => 'required|string|max:255|unique:user,username,' . $user->id,
                'edit_nama_perawat'        => 'required|string|max:255',
                'edit_email_perawat'       => 'required|email|unique:user,email,' . $user->id,
                'edit_foto_perawat'        => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg,jfif|max:5120',
                'edit_no_hp_perawat'       => 'nullable|string|max:20',
                'edit_password_perawat'    => 'nullable|string|min:8|confirmed',
            ]);

            // Update user
            $user->username = $request->input('edit_username_perawat');
            $user->email    = $request->input('edit_email_perawat');

            if ($request->filled('edit_password_perawat')) {
                $user->password = Hash::make($request->input('edit_password_perawat'));
            }

            // Handle foto upload
            $fotoPath = null;
            if ($request->hasFile('edit_foto_perawat')) {
                $file = $request->file('edit_foto_perawat');

                $extension = strtolower($file->getClientOriginalExtension());
                if ($extension === 'jfif') {
                    $extension = 'jpg';
                }

                $fileName = 'perawat_' . time() . '.' . $extension;
                $path = 'perawat/' . $fileName;

                if ($extension === 'svg') {
                    Storage::disk('public')->put($path, file_get_contents($file));
                } else {
                    $image = Image::read($file);
                    $image->scale(width: 800);
                    Storage::disk('public')->put($path, (string) $image->encodeByExtension($extension, quality: 80));
                }

                $fotoPath = $path;

                if ($perawat->foto_perawat && Storage::disk('public')->exists($perawat->foto_perawat)) {
                    Storage::disk('public')->delete($perawat->foto_perawat);
                }
            }

            // Update perawat
            $updateData = [
                'nama_perawat'  => $request->edit_nama_perawat,
                'no_hp_perawat' => $request->edit_no_hp_perawat,
            ];

            $updateDataUser = ([
                'username' => $request->edit_username_perawat,
            ]);

            if ($fotoPath) {
                $updateData['foto_perawat'] = $fotoPath;
            }

            $perawat->update($updateData);
            $user->update($updateDataUser);

            return response()->json(['message' => 'Data perawat berhasil diperbarui.']);
        } catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
            // 📛 Jika file melebihi batas upload
            return response()->json([
                'message' => 'Ukuran file terlalu besar! Maksimal 5 MB.'
            ], 413);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // 📛 Jika validasi gagal
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // 💥 Error umum
            return response()->json([
                'message' => 'Tidak ada respon dari server.', // 🔥 ini pesan yang kamu mau
                'error_detail' => $e->getMessage(), // opsional, untuk debugging (bisa kamu hapus kalau gak mau tampil)
            ], 500);
        }
    }

    public function deletePerawat($id)
    {
        $perawat = Perawat::findOrFail($id);

        $perawat->user->delete();
        $perawat->delete();
        // Hapus foto jika ada
        if ($perawat->foto_perawat && Storage::disk('public')->exists($perawat->foto_perawat)) {
            Storage::disk('public')->delete($perawat->foto_perawat);
        }

        return response()->json(['success' => 'Data perawat berhasil dihapus.']);
    }
}
