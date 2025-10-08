<?php

namespace App\Http\Controllers\Management;

use App\Models\User;
use App\Models\Apoteker;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApotekerController extends Controller
{

    public function createApoteker(Request $request)
    {
        $request->validate([
            'username'         => 'required|string|max:255',
            'email_apoteker'   => 'required|email|unique:user,email',
            'password'         => 'nullable|string|min:6|confirmed',
            'nama_apoteker'    => 'required|string|max:255',
            'no_hp_apoteker'   => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email'    => $request->email_apoteker,
            'password' => Hash::make($request->password),
            'role'     => 'Apoteker',
        ]);

        Apoteker::create([
            'user_id'        => $user->id,
            'nama_apoteker'  => $request->nama_apoteker,
            'email_apoteker' => $request->email_apoteker,
            'no_hp_apoteker' => $request->no_hp_apoteker,
        ]);

        return response()->json(['success' => 'Data apoteker berhasil ditambahkan.']);
    }

    public function getApotekerById($id)
    {
        $data = Apoteker::with('user')->findOrFail($id);
        return response()->json(['data' => $data]);
    }

    public function updateApoteker(Request $request, $id)
    {
        $apoteker = Apoteker::findOrFail($id);
        $user = $apoteker->user;

        $request->validate([
            'edit_username_apoteker' => 'required|string|max:255|unique:user,username,' . $user->id,
            'edit_nama_apoteker'    => 'required|string|max:255',
            'edit_email_apoteker'   => 'required|email|unique:user,email,' . $user->id,
            'edit_no_hp_apoteker'   => 'nullable|string|max:20',
            'edit_password_apoteker'   => 'nullable|string|min:6|confirmed',
        ]);

        // Update user
        $user->username = $request->edit_username_apoteker;
        $user->email    = $request->edit_email_apoteker;

        if ($request->filled('edit_password_apoteker')) {
            $user->password = Hash::make($request->edit_password);
        }

        $user->save();

        // Update apoteker
        $apoteker->update([
            'nama_apoteker'  => $request->edit_nama_apoteker,
            'email_apoteker' => $request->edit_email_apoteker,
            'no_hp_apoteker' => $request->edit_no_hp_apoteker,
        ]);

        return response()->json(['message' => 'Data apoteker berhasil diperbarui.']);
    }

    public function deleteApoteker($id)
    {
        $apoteker = Apoteker::findOrFail($id);
        $apoteker->user->delete();
        $apoteker->delete();

        return response()->json(['success' => 'Data apoteker berhasil dihapus.']);
    }
}
