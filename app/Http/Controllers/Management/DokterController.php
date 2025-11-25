<?php

namespace App\Http\Controllers\Management;

use App\Models\User;
use App\Models\Dokter;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Validation\Rule;


class DokterController extends Controller
{
    public function createDokter(Request $request)
    {
        try {
            $validated = $request->validate([
                'username_dokter'    => 'required|string|max:255',
                'poli_id'           => ['required', 'array', 'min:1'],
                'poli_id.*'         => ['integer', 'distinct', 'exists:poli,id'],
                'nama_dokter'        => 'required|string|max:255',
                'email_akun_dokter'  => 'required|email|max:255',
                'spesialis_dokter'   => 'required|integer|exists:jenis_spesialis,id',
                'password_dokter'    => 'required|string|min:8|confirmed',
                'foto_dokter'        => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg,jfif|max:5120',
                'deskripsi_dokter'   => 'nullable|string',
                'pengalaman_dokter'  => 'nullable|string|max:255',
                'no_hp_dokter'       => 'nullable|string|max:20',
            ]);

            DB::beginTransaction();

            // 1) user
            $user = User::create([
                'username' => $validated['username_dokter'],
                'email'    => $validated['email_akun_dokter'],
                'password' => Hash::make($validated['password_dokter']),
                'role'     => 'Dokter',
            ]);

            // 2) upload & kompres foto
            $fotoPath = null;
            if ($request->hasFile('foto_dokter')) {
                $file = $request->file('foto_dokter');
                $ext = strtolower($file->getClientOriginalExtension());

                // normalisasi jfif -> jpg
                if ($ext === 'jfif') $ext = 'jpg';

                $fileName = 'dokter_' . time() . '.' . $ext;
                $path = 'dokter/' . $fileName;

                if ($ext === 'svg') {
                    Storage::disk('public')->put($path, file_get_contents($file));
                } else {
                    $image = Image::read($file);
                    $image->scale(width: 800); // jaga ukuran
                    Storage::disk('public')->put($path, (string) $image->encodeByExtension($ext, quality: 80));
                }
                $fotoPath = $path;
            }

            // 3) dokter
            $poliId = $validated['poli_id'];

            // ⚠️ Masa transisi (opsional, tapi direkomendasikan):
            // simpan poli pertama ke kolom lama `dokter.poli_id` agar fitur lama tidak rusak
            $legacyPoliId = $poliId[0] ?? null;

            $dokter = Dokter::create([
                'user_id'            => $user->id,
                'nama_dokter'        => $validated['nama_dokter'],
                'jenis_spesialis_id' => $validated['spesialis_dokter'],
                'foto_dokter'        => $fotoPath,
                'deskripsi_dokter'   => $validated['deskripsi_dokter'] ?? null,
                'pengalaman'         => $validated['pengalaman_dokter'] ?? null,
                'no_hp'              => $validated['no_hp_dokter'] ?? null,
            ]);

            // 4) attach ke pivot (many-to-many)
            $dokter->poli()->sync($validated['poli_id']);

            DB::commit();

            return response()->json(['message' => 'Data dokter berhasil ditambahkan.']);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validasi Gagal!',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi Kesalahan Di Server ' . $e->getMessage()
            ], 500);
        }
    }


    public function getDokterById($id)
    {
        $data = Dokter::with('user', 'poli')->findOrFail($id);
        return response()->json(['data' => $data]);
    }


    public function updateDokter(Request $request)
    {
        try {
            $dokter = Dokter::with('user')->findOrFail($request->edit_dokter_id);
            $user   = $dokter->user;

            // ✅ VALIDASI
            $validated = $request->validate([
                'edit_username_dokter'    => [
                    'required',
                    'string',
                    'max:255',
                ],
                'edit_email_akun_dokter'  => [
                    'required',
                    'email',
                    'max:255',
                ],
                'edit_nama_dokter'        => 'required|string|max:255',
                'edit_spesialis_dokter'   => 'required|integer|exists:jenis_spesialis,id',
                'edit_no_hp_dokter'       => 'nullable|string|max:20',
                'edit_pengalaman_dokter'  => 'nullable|string|max:255',
                'edit_deskripsi_dokter'   => 'nullable|string',
                'edit_password_dokter'    => 'nullable|string|min:8|confirmed',
                'edit_foto_dokter'        => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg,jfif|max:5120',

                // 🔁 many-to-many Poli
                'poli_id'                => ['required', 'array', 'min:1'],
                'poli_id.*'              => ['integer', 'distinct', 'exists:poli,id'],
            ]);

            DB::beginTransaction();

            // 1) UPDATE USER
            $user->username = $validated['edit_username_dokter'];
            $user->email    = $validated['edit_email_akun_dokter'];
            if (!empty($validated['edit_password_dokter'])) {
                $user->password = Hash::make($validated['edit_password_dokter']);
            }
            $user->save();

            // 2) FOTO (opsional, aman hapus lama)
            $fotoPath = $dokter->foto_dokter;
            if ($request->hasFile('edit_foto_dokter')) {
                // hapus lama jika ada
                if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
                    Storage::disk('public')->delete($fotoPath);
                }

                $file = $request->file('edit_foto_dokter');
                $ext  = strtolower($file->getClientOriginalExtension());
                if ($ext === 'jfif') $ext = 'jpg';

                $fileName = 'dokter_' . time() . '.' . $ext;
                $path     = 'dokter/' . $fileName;

                if ($ext === 'svg') {
                    Storage::disk('public')->put($path, file_get_contents($file));
                } else {
                    $image = Image::read($file);
                    $image->scale(width: 800);
                    Storage::disk('public')->put($path, (string) $image->encodeByExtension($ext, quality: 80));
                }
                $fotoPath = $path;
            }

            // 3) UPDATE DOKTER
            $dokter->update([
                'nama_dokter'        => $validated['edit_nama_dokter'],
                'jenis_spesialis_id' => $validated['edit_spesialis_dokter'],
                'no_hp'              => $validated['edit_no_hp_dokter'] ?? null,
                'pengalaman'         => $validated['edit_pengalaman_dokter'] ?? null,
                'deskripsi_dokter'   => $validated['edit_deskripsi_dokter'] ?? null,
                'foto_dokter'        => $fotoPath,
                // ⚠️ OPSIONAL (masa transisi): isi kolom lama poli_id dengan poli pertama
                // hapus baris ini setelah kolom legacy di-drop
                // 'poli_id'         => $validated['poli_id'][0] ?? null,
            ]);

            // 4) SYNC PIVOT POLI
            $dokter->poli()->sync($validated['poli_id']);

            DB::commit();

            return response()->json(['success' => 'Data dokter berhasil diperbarui.']);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Validasi Gagal!",
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan di server ' . $e->getMessage()
            ], 500);
        }
    }


    public function deleteDokter($id)
    {
        return DB::transaction(function () use ($id) {
            // Ambil dokter + user
            $dokter = Dokter::with('user')->findOrFail($id);
            $user   = $dokter->user;

            // 1) Hapus foto jika ada
            if ($dokter->foto_dokter && Storage::disk('public')->exists($dokter->foto_dokter)) {
                Storage::disk('public')->delete($dokter->foto_dokter);
            }

            // 2) Bersihkan relasi pivot dokter_poli (tanpa menyentuh jadwal_dokter)
            //    - Jika FK dokter_poli.dokter_id sudah cascadeOnDelete, bagian ini boleh di-skip.
            $dokter->poli()->detach();

            // 3) Hapus Dokter, lalu User
            $dokter->delete();
            if ($user) {
                $user->delete();
            }

            return response()->json(['success' => 'Data dokter berhasil dihapus.']);
        });
    }
}
