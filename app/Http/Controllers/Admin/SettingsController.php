<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Farmasi;
use App\Models\Kasir;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load([
            'superAdmin',
            'admin',
            'farmasi',
            'kasir',
            // 'dokter',
        ]);

        return view('admin.settings', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $roleConfig = [
            'Super Admin' => [
                'relation'        => 'superAdmin',
                'model'           => SuperAdmin::class,
                'nama_field'      => 'nama_super_admin',
                'foto_field'      => 'foto_super_admin',
                'no_hp_field'     => 'no_hp_super_admin',
                'foto_folder'     => 'super-admin/foto-profile',
                'label'           => 'Super Admin',
            ],
            'Admin' => [
                'relation'        => 'admin',
                'model'           => Admin::class,
                'nama_field'      => 'nama_admin',
                'foto_field'      => 'foto_admin',
                'no_hp_field'     => 'no_hp',
                'foto_folder'     => 'admin/foto-profile',
                'label'           => 'Admin',
            ],
            'Farmasi' => [
                'relation'        => 'farmasi',
                'model'           => Farmasi::class,
                'nama_field'      => 'nama_farmasi',
                'foto_field'      => 'foto_farmasi',
                'no_hp_field'     => 'no_hp_farmasi',
                'foto_folder'     => 'farmasi/foto-profile',
                'label'           => 'Farmasi',
            ],
            'Kasir' => [
                'relation'        => 'kasir',
                'model'           => Kasir::class,
                'nama_field'      => 'nama_kasir',
                'foto_field'      => 'foto_kasir',
                'no_hp_field'     => 'no_hp_kasir',
                'foto_folder'     => 'kasir/foto-profile',
                'label'           => 'Kasir',
            ],

            /*
            Kalau nanti role lain sudah siap:

            'Dokter' => [
                'relation'        => 'dokter',
                'model'           => \App\Models\Dokter::class,
                'nama_field'      => 'nama_dokter',
                'foto_field'      => 'foto_dokter',
                'no_hp_field'     => 'no_hp_dokter',
                'foto_folder'     => 'dokter/foto-profile',
                'label'           => 'Dokter',
            ],
            */
        ];

        if (!isset($roleConfig[$user->role])) {
            return redirect()
                ->route('settings.index')
                ->with('error', 'Role ini belum didukung untuk update profil.');
        }

        $config = $roleConfig[$user->role];

        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('user', 'username')->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('user', 'email')->ignore($user->id),
            ],
            'nama' => ['required', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+\-\s\(\)]+$/'],
            'foto_profil' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'nama.required' => 'Nama wajib diisi.',
            'no_hp.regex' => 'No. HP hanya boleh berisi angka, spasi, tanda kurung, tanda +, atau -.',
            'foto_profil.image' => 'File harus berupa gambar.',
            'foto_profil.mimes' => 'Foto harus berformat JPG, JPEG, atau PNG.',
            'foto_profil.max' => 'Ukuran foto maksimal 2MB.',
        ]);

        DB::beginTransaction();

        $newPhotoPath = null;

        try {
            $relationName = $config['relation'];
            $detail = $user->$relationName;

            if (!$detail) {
                $modelClass = $config['model'];
                $detail = new $modelClass();
                $detail->user_id = $user->id;
            }

            $oldPhotoPath = $detail->{$config['foto_field']} ?? null;

            // update tabel user
            $emailLama = $user->email;
            $emailBaru = strtolower($validated['email']);

            $user->username = $validated['username'];
            $user->email = $emailBaru;

            if ($emailLama !== $emailBaru) {
                $user->email_verified_at = null;
            }

            $user->save();

            // update tabel detail sesuai role
            $detail->{$config['nama_field']} = $validated['nama'];
            $detail->{$config['no_hp_field']} = $validated['no_hp'] ?? null;

            if ($request->hasFile('foto_profil')) {
                $newPhotoPath = $request->file('foto_profil')->store($config['foto_folder'], 'public');
                $detail->{$config['foto_field']} = $newPhotoPath;
            }

            $detail->save();

            DB::commit();

            if ($newPhotoPath && $oldPhotoPath && Storage::disk('public')->exists($oldPhotoPath)) {
                Storage::disk('public')->delete($oldPhotoPath);
            }

            return redirect()
                ->route('settings.index')
                ->with('status', 'profile-updated')
                ->with('success', 'Profil ' . $config['label'] . ' berhasil diperbarui.');
        } catch (\Throwable $th) {
            DB::rollBack();

            if ($newPhotoPath && Storage::disk('public')->exists($newPhotoPath)) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            return redirect()
                ->route('settings.index')
                ->withInput()
                ->with('error', 'Gagal memperbarui profil.');
        }
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                'different:current_password',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',

            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.different' => 'Password baru harus berbeda dari password saat ini.',

            'password.min' => 'Password baru minimal 8 karakter.',
            'password.letters' => 'Password baru harus mengandung minimal satu huruf.',
            'password.mixed' => 'Password baru harus mengandung minimal satu huruf besar dan satu huruf kecil.',
            'password.numbers' => 'Password baru harus mengandung minimal satu angka.',
            'password.symbols' => 'Password baru harus mengandung minimal satu simbol.',
        ]);

        $user = $request->user();
        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()
            ->route('settings.index')
            ->with('status', 'password-updated')
            ->with('success_password', 'Password berhasil diperbarui.');
    }
}
