<?php

namespace App\Http\Controllers\Management;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dataAdmin()
    {
        $user = Auth::user();
        $isSuperAdmin = $user && strtolower(str_replace(' ', '', $user->role)) === 'superadmin';

        $query = Admin::with('user')
            ->select('admin.*')
            ->latest('admin.id');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('foto', function ($row) {
                if ($row->foto_admin) {
                    $url = asset('storage/' . $row->foto_admin);
                    return '<img src="' . $url . '" alt="Foto Admin" class="w-12 h-12 rounded-lg object-cover mx-auto shadow">';
                }

                return '
                    <div class="w-12 h-12 rounded-lg bg-slate-100 text-slate-400 flex items-center justify-center mx-auto shadow-sm">
                        <i class="fa-solid fa-user"></i>
                    </div>
                ';
            })
            ->addColumn('nama_admin', fn($row) => $row->nama_admin ?? '-')
            ->addColumn('username', fn($row) => optional($row->user)->username ?? '-')
            ->addColumn('email_user', fn($row) => optional($row->user)->email ?? '-')
            ->addColumn('role', function ($row) {
                $role = optional($row->user)->role ?? '-';

                return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200">'
                    . e($role) .
                    '</span>';
            })
            ->addColumn('no_hp', fn($row) => $row->no_hp ?? '-')
            ->addColumn('action', function ($row) use ($isSuperAdmin) {
                $buttons = '
                    <div class="flex items-center justify-center gap-2">
                        <button type="button"
                            class="btn-edit-admin inline-flex items-center justify-center w-9 h-9 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 hover:text-amber-700"
                            data-id="' . $row->id . '" title="Edit">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </button>
                ';

                if ($isSuperAdmin) {
                    $buttons .= '
                        <button type="button"
                            class="btn-delete-admin inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700"
                            data-id="' . $row->id . '" title="Hapus">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    ';
                }

                $buttons .= '</div>';

                return $buttons;
            })
            ->rawColumns(['foto', 'role', 'action'])
            ->make(true);
    }

    public function createAdmin(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:user,username',
            'email' => 'required|email|max:255|unique:user,email',
            'password' => 'required|string|min:6|confirmed',
            'nama_admin' => 'required|string|max:255',
            'no_hp' => 'nullable|string|max:20',
            'foto_admin' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'Admin',
            ]);

            $fotoPath = null;
            if ($request->hasFile('foto_admin')) {
                $fotoPath = $request->file('foto_admin')->store('admin', 'public');
            }

            Admin::create([
                'user_id' => $user->id,
                'nama_admin' => $validated['nama_admin'],
                'foto_admin' => $fotoPath,
                'no_hp' => $validated['no_hp'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Data admin berhasil ditambahkan.'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Gagal menambahkan data admin.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function getAdminById($id)
    {
        $admin = Admin::with('user')->findOrFail($id);

        return response()->json([
            'data' => $admin
        ]);
    }

    public function updateAdmin(Request $request, $id)
    {
        $admin = Admin::with('user')->findOrFail($id);
        $user = $admin->user;

        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255|',
            'password' => 'nullable|string|min:6|confirmed',
            'nama_admin' => 'required|string|max:255',
            'no_hp' => 'nullable|string|max:20',
            'foto_admin' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $userData = [
                'username' => $validated['username'],
                'email' => $validated['email'],
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $user->update($userData);

            $adminData = [
                'nama_admin' => $validated['nama_admin'],
                'no_hp' => $validated['no_hp'] ?? null,
            ];

            if ($request->hasFile('foto_admin')) {
                if ($admin->foto_admin && Storage::disk('public')->exists($admin->foto_admin)) {
                    Storage::disk('public')->delete($admin->foto_admin);
                }

                $adminData['foto_admin'] = $request->file('foto_admin')->store('admin', 'public');
            }

            $admin->update($adminData);

            DB::commit();

            return response()->json([
                'message' => 'Data admin berhasil diperbarui.'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Gagal memperbarui data admin.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function deleteAdmin($id)
    {
        $admin = Admin::with('user')->findOrFail($id);

        DB::beginTransaction();

        try {
            if ($admin->foto_admin && Storage::disk('public')->exists($admin->foto_admin)) {
                Storage::disk('public')->delete($admin->foto_admin);
            }

            $user = $admin->user;

            $admin->delete();

            if ($user) {
                $user->delete();
            }

            DB::commit();

            return response()->json([
                'message' => 'Data admin berhasil dihapus.'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Gagal menghapus data admin.',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}