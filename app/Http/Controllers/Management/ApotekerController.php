<?php

namespace App\Http\Controllers\Management;

use App\Models\User;
use App\Models\Apoteker;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApotekerController extends Controller
{
    public function index()
    {
        return view('apoteker.dashboard');
    }

    public function createApoteker(Request $request)
    {
        try {
            // 🧩 Validasi input
            $request->validate([
                'foto_apoteker'     => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg,jfif|max:5120',
                'username_apoteker' => 'required|string|max:255|unique:user,username',
                'nama_apoteker'     => 'required|string|max:255',
                'email_apoteker'    => 'required|email|unique:user,email',
                'no_hp_apoteker'    => 'nullable|string|max:20',
                'password_apoteker' => 'required|string|min:8|confirmed',
            ]);

            // 🧑‍💻 Buat user baru
            $user = User::create([
                'username' => $request->username_apoteker,
                'email'    => $request->email_apoteker,
                'password' => Hash::make($request->password_apoteker),
                'role'     => 'Apoteker',
            ]);

            // 📸 Upload + Kompres Foto
            $fotoPath = null;
            if ($request->hasFile('foto_apoteker')) {
                $file = $request->file('foto_apoteker');

                $extension = strtolower($file->getClientOriginalExtension());
                if ($extension === 'jfif') {
                    $extension = 'jpg';
                }

                $fileName = 'apoteker_' . time() . '.' . $extension;
                $path = 'apoteker/' . $fileName;

                if ($extension === 'svg') {
                    Storage::disk('public')->put($path, file_get_contents($file));
                } else {
                    $image = Image::read($file);
                    $image->scale(width: 800);
                    Storage::disk('public')->put($path, (string) $image->encodeByExtension($extension, quality: 80));
                }

                $fotoPath = $path;
            }

            // 🏥 Buat data apoteker
            Apoteker::create([
                'user_id'        => $user->id,
                'foto_apoteker'  => $fotoPath,
                'nama_apoteker'  => $request->nama_apoteker,
                'no_hp_apoteker' => $request->no_hp_apoteker,
            ]);

            return response()->json(['message' => 'Data apoteker berhasil ditambahkan.']);
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


    public function getApotekerById($id)
    {
        $data = Apoteker::with('user')->findOrFail($id);
        return response()->json(['data' => $data]);
    }

    public function updateApoteker(Request $request, $id)
    {
        try {
            $apoteker = Apoteker::findOrFail($id);
            $user = $apoteker->user;

            $request->validate([
                'edit_username_apoteker'    => 'required|string|max:255|unique:user,username,' . $user->id,
                'edit_nama_apoteker'        => 'required|string|max:255',
                'edit_email_apoteker'       => 'required|email|unique:user,email,' . $user->id,
                'edit_foto_apoteker'        => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg,jfif|max:5120',
                'edit_no_hp_apoteker'       => 'nullable|string|max:20',
                'edit_password_apoteker'    => 'nullable|string|min:8|confirmed',
            ]);

            // Update user
            $user->username = $request->input('edit_username_apoteker');
            $user->email    = $request->input('edit_email_apoteker');

            if ($request->filled('edit_password_apoteker')) {
                $user->password = Hash::make($request->input('edit_password_apoteker'));
            }

            // Handle foto upload
            $fotoPath = null;
            if ($request->hasFile('edit_foto_apoteker')) {
                $file = $request->file('edit_foto_apoteker');

                $extension = strtolower($file->getClientOriginalExtension());
                if ($extension === 'jfif') {
                    $extension = 'jpg';
                }

                $fileName = 'apoteker_' . time() . '.' . $extension;
                $path = 'apoteker/' . $fileName;

                if ($extension === 'svg') {
                    Storage::disk('public')->put($path, file_get_contents($file));
                } else {
                    $image = Image::read($file);
                    $image->scale(width: 800);
                    Storage::disk('public')->put($path, (string) $image->encodeByExtension($extension, quality: 80));
                }

                $fotoPath = $path;

                if ($apoteker->foto_apoteker && Storage::disk('public')->exists($apoteker->foto_apoteker)) {
                    Storage::disk('public')->delete($apoteker->foto_apoteker);
                }
            }

            // Update apoteker
            $updateData = [
                'nama_apoteker'  => $request->edit_nama_apoteker,
                'no_hp_apoteker' => $request->edit_no_hp_apoteker,
            ];

            if ($fotoPath) {
                $updateData['foto_apoteker'] = $fotoPath;
            }

            $apoteker->update($updateData);

            return response()->json(['message' => 'Data apoteker berhasil diperbarui.']);
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
            // 📛 Error umum
            return response()->json([
                'message' => 'Terjadi kesalahan di server: ' . $e->getMessage()
            ], 500);
        }
    }


    public function deleteApoteker($id)
    {
        $apoteker = Apoteker::findOrFail($id);

        $apoteker->user->delete();
        $apoteker->delete();
        // Hapus foto jika ada
        if ($apoteker->foto_apoteker && Storage::disk('public')->exists($apoteker->foto_apoteker)) {
            Storage::disk('public')->delete($apoteker->foto_apoteker);
        }

        return response()->json(['success' => 'Data apoteker berhasil dihapus.']);
    }
}
