<?php

namespace App\Http\Controllers\Management;

use App\Models\User;
use App\Models\Pasien;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PasienController extends Controller
{
    public function createPasien(Request $request)
    {
        $request->validate([
            'foto_pasien'           => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg,jfif|max:5120',
            'username_pasien'       => 'required|string|max:255',
            'nama_pasien'           => 'required|string|max:255',
            'email_pasien'          => 'required|email|unique:user,email',
            'alamat_pasien'         => 'nullable|string|max:255',
            'password_pasien'       => 'required|string|min:8|confirmed',
            'tanggal_lahir_pasien'  => 'nullable|date',
            'jenis_kelamin_pasien'  => 'nullable|in:Laki-laki,Perempuan',
        ]);

        // ✅ Ambil nomor EMR terakhir dari database
        $lastPasien = Pasien::orderBy('id', 'desc')->first();
        $lastNumber = 0;

        if ($lastPasien && preg_match('/RM-(\d+)/', $lastPasien->no_emr, $matches)) {
            $lastNumber = (int)$matches[1];
        }

        // ✅ Nomor EMR berikutnya
        $nextNumber = $lastNumber + 1;
        $no_emr = 'RM-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);

        // ✅ Buat user baru
        $user = User::create([
            'username' => $request->username_pasien,
            'email'    => $request->email_pasien,
            'password' => Hash::make($request->password_pasien),
            'role'     => 'Pasien',
        ]);

        // ✅ Upload & kompres foto (jika ada)
        $fotoPath = null;
        if ($request->hasFile('foto_pasien')) {
            $file = $request->file('foto_pasien');
            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension === 'jfif') {
                $extension = 'jpg';
            }

            $fileName = 'pasien_' . time() . '.' . $extension;
            $path = 'pasien/' . $fileName;

            if ($extension === 'svg') {
                Storage::disk('public')->put($path, file_get_contents($file));
            } else {
                $image = Image::read($file);
                $image->scale(width: 800);
                Storage::disk('public')->put($path, (string) $image->encodeByExtension($extension, quality: 80));
            }

            $fotoPath = $path;
        }

        // ✅ Simpan data pasien ke database
        Pasien::create([
            'user_id'       => $user->id,
            'no_emr'        => $no_emr,
            'foto_pasien'   => $fotoPath,
            'nama_pasien'   => $request->nama_pasien,
            'alamat'        => $request->alamat_pasien,
            'tanggal_lahir' => $request->tanggal_lahir_pasien,
            'jenis_kelamin' => $request->jenis_kelamin_pasien,
        ]);

        return response()->json([
            'message' => 'Data pasien berhasil ditambahkan.',
            'no_emr'  => $no_emr,
        ]);
    }

    public function getPasienById($id)
    {
        // dd($id);
        $data = Pasien::with('user')->findOrFail($id);
        return response()->json(['data' => $data]);
    }

    public function updatePasien(Request $request, $id)
    {
        $pasien = Pasien::findOrFail($id);
        $user = $pasien->user;

        $request->validate([
            'edit_foto_pasien'          => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg,jfif|max:5120',
            'edit_username_pasien'      => 'required|string|max:255|unique:user,username,' . $user->id,
            'edit_nama_pasien'          => 'required|string|max:255',
            'edit_email_pasien'         => 'required|email|unique:user,email,' . $user->id,
            'edit_alamat_pasien'        => 'nullable|string|max:255',
            'edit_tanggal_lahir_pasien' => 'nullable|date',
            'edit_jenis_kelamin_pasien' => 'nullable|in:Laki-laki,Perempuan',
            'edit_password_pasien'      => 'nullable|string|min:8|confirmed',
        ]);

        // ✅ Jika pasien belum punya no_emr, buatkan otomatis
        if (empty($pasien->no_emr)) {
            $lastPasien = Pasien::orderBy('id', 'desc')->first();
            $lastNumber = 0;

            if ($lastPasien && preg_match('/RM-(\d+)/', $lastPasien->no_emr, $matches)) {
                $lastNumber = (int)$matches[1];
            }

            $nextNumber = $lastNumber + 1;
            $no_emr = 'RM-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);

            $pasien->no_emr = $no_emr;
        }

        // ✅ Update akun user
        $user->username = $request->input('edit_username_pasien');
        $user->email    = $request->input('edit_email_pasien');

        if ($request->filled('edit_password_pasien')) {
            $user->password = Hash::make($request->input('edit_password_pasien'));
        }
        $user->save();

        // ✅ Upload & kompres foto jika ada
        $fotoPath = null;
        if ($request->hasFile('edit_foto_pasien')) {
            $file = $request->file('edit_foto_pasien');
            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension === 'jfif') {
                $extension = 'jpg';
            }

            $fileName = 'pasien_' . time() . '.' . $extension;
            $path = 'pasien/' . $fileName;

            if ($extension === 'svg') {
                Storage::disk('public')->put($path, file_get_contents($file));
            } else {
                $image = Image::read($file);
                $image->scale(width: 800);
                Storage::disk('public')->put($path, (string) $image->encodeByExtension($extension, quality: 80));
            }

            $fotoPath = $path;

            // Opsional: hapus foto lama jika ada
            if ($pasien->foto_pasien && Storage::disk('public')->exists($pasien->foto_pasien)) {
                Storage::disk('public')->delete($pasien->foto_pasien);
            }
        }

        // ✅ Update data pasien
        $updateData = [
            'nama_pasien'   => $request->edit_nama_pasien,
            'alamat'        => $request->edit_alamat_pasien,
            'tanggal_lahir' => $request->edit_tanggal_lahir_pasien,
            'jenis_kelamin' => $request->edit_jenis_kelamin_pasien,
        ];

        if ($fotoPath) {
            $updateData['foto_pasien'] = $fotoPath;
        }

        $pasien->update($updateData);

        return response()->json([
            'message' => 'Data pasien berhasil diperbarui.',
            'no_emr'  => $pasien->no_emr,
        ]);
    }


    public function deletePasien($id)
    {
        $pasien = Pasien::findOrFail($id);
        // Hapus foto jika ada
        if ($pasien->foto_pasien && Storage::disk('public')->exists($pasien->foto_pasien)) {
            Storage::disk('public')->delete($pasien->foto_pasien);
        }

        $pasien->user->delete();
        $pasien->delete();

        return response()->json(['success' => 'Data pasien berhasil dihapus.']);
    }
}
