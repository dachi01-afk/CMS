<?php

namespace App\Http\Controllers\Management;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{

    public function createAdmin(Request $request)
    {
        $request->validate([
            'username'     => 'required|string|max:255',
            'email_admin'  => 'required|email|unique:user,email',
            'password'     => 'required|string|min:6',
            'nama_admin'   => 'required|string|max:255',
            'no_hp'        => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email'    => $request->email_admin,
            'password' => Hash::make($request->password),
            'role'     => 'Admin',
        ]);

        Admin::create([
            'user_id'     => $user->id,
            'nama_admin'  => $request->nama_admin,
            'email_admin' => $request->email_admin,
            'no_hp'       => $request->no_hp,
        ]);

        return response()->json(['success' => 'Data admin berhasil ditambahkan.']);
    }

    public function getAdminById($id)
    {
        $data = Admin::with('user')->findOrFail($id);
        return response()->json($data);
    }

    public function updateAdmin(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $user = $admin->user;

        $request->validate([
            'username'     => 'required|string|max:255',
            'email_admin'  => 'required|email|unique:user,email,' . $user->id,
            'nama_admin'   => 'required|string|max:255',
            'no_hp'        => 'nullable|string|max:20',
        ]);

        $user->update([
            'username' => $request->username,
            'email'    => $request->email_admin,
        ]);

        $admin->update([
            'nama_admin'  => $request->nama_admin,
            'email_admin' => $request->email_admin,
            'no_hp'       => $request->no_hp,
        ]);

        return response()->json(['success' => 'Data admin berhasil diperbarui.']);
    }

    public function deleteAdmin($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->user->delete();
        $admin->delete();

        return response()->json(['success' => 'Data admin berhasil dihapus.']);
    }
}
