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
                'foto_dokter'        => 'required|file|mimes:jpeg,jpg,png,gif,webp,svg,jfif|max:5120',
                'deskripsi_dokter'   => 'nullable|string',
                'pengalaman_dokter'  => 'nullable|string|max:255',
                'no_hp_dokter'       => 'nullable|string|max:20',
            ]);

            DB::beginTransaction();

            $user = User::create([
                'username' => $validated['username_dokter'],
                'email'    => $validated['email_akun_dokter'],
                'password' => Hash::make($validated['password_dokter']),
                'role'     => 'Dokter',
            ]);

            $fotoPath = null;
            if ($request->hasFile('foto_dokter')) {
                $file = $request->file('foto_dokter');
                $ext = strtolower($file->getClientOriginalExtension());

                if ($ext === 'jfif') {
                    $ext = 'jpg';
                }

                $fileName = 'dokter_' . time() . '.' . $ext;
                $path = 'dokter/' . $fileName;

                if ($ext === 'svg') {
                    Storage::disk('public')->put($path, file_get_contents($file));
                } else {
                    $image = Image::read($file);
                    $image->scale(width: 800);
                    Storage::disk('public')->put($path, (string) $image->encodeByExtension($ext, quality: 80));
                }

                $fotoPath = $path;
            }

            $poliId = $validated['poli_id'];
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
                'poli_id'                 => ['required', 'array', 'min:1'],
                'poli_id.*'               => ['integer', 'distinct', 'exists:poli,id'],
            ]);

            DB::beginTransaction();

            $user->username = $validated['edit_username_dokter'];
            $user->email    = $validated['edit_email_akun_dokter'];

            if (!empty($validated['edit_password_dokter'])) {
                $user->password = Hash::make($validated['edit_password_dokter']);
            }

            $user->save();

            $fotoPath = $dokter->foto_dokter;
            if ($request->hasFile('edit_foto_dokter')) {
                if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
                    Storage::disk('public')->delete($fotoPath);
                }

                $file = $request->file('edit_foto_dokter');
                $ext  = strtolower($file->getClientOriginalExtension());

                if ($ext === 'jfif') {
                    $ext = 'jpg';
                }

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

            $dokter->update([
                'nama_dokter'        => $validated['edit_nama_dokter'],
                'jenis_spesialis_id' => $validated['edit_spesialis_dokter'],
                'no_hp'              => $validated['edit_no_hp_dokter'] ?? null,
                'pengalaman'         => $validated['edit_pengalaman_dokter'] ?? null,
                'deskripsi_dokter'   => $validated['edit_deskripsi_dokter'] ?? null,
                'foto_dokter'        => $fotoPath,
            ]);

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
        $userLogin = Auth::user();

        if (! $userLogin || strtolower(trim($userLogin->role)) !== 'super admin') {
            return response()->json([
                'message' => 'Akses ditolak. Hanya Super Admin yang dapat menghapus data dokter.',
            ], 403);
        }

        try {
            return DB::transaction(function () use ($id) {
                $dokter = Dokter::with('user')->findOrFail($id);
                $user   = $dokter->user;

                if ($dokter->foto_dokter && Storage::disk('public')->exists($dokter->foto_dokter)) {
                    Storage::disk('public')->delete($dokter->foto_dokter);
                }

                $dokter->poli()->detach();

                $dokter->delete();

                if ($user) {
                    $user->delete();
                }

                return response()->json([
                    'success' => 'Data dokter berhasil dihapus.',
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan di server ' . $e->getMessage(),
            ], 500);
        }
    }
}
