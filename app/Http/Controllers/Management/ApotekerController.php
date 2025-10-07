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
        return response()->json($data);
    }

    public function updateApoteker(Request $request, $id)
    {
        $apoteker = Apoteker::findOrFail($id);
        $user = $apoteker->user;

        $request->validate([
            'username'         => 'required|string|max:255',
            'email_apoteker'   => 'required|email|unique:user,email,' . $user->id,
            'nama_apoteker'    => 'required|string|max:255',
            'no_hp_apoteker'   => 'nullable|string|max:20',
        ]);

        $user->update([
            'username' => $request->username,
            'email'    => $request->email_apoteker,
        ]);

        $apoteker->update([
            'nama_apoteker'  => $request->nama_apoteker,
            'email_apoteker' => $request->email_apoteker,
            'no_hp_apoteker' => $request->no_hp_apoteker,
        ]);

        return response()->json(['success' => 'Data apoteker berhasil diperbarui.']);
    }

    public function deleteApoteker($id)
    {
        $apoteker = Apoteker::findOrFail($id);
        $apoteker->user->delete();
        $apoteker->delete();

        return response()->json(['success' => 'Data apoteker berhasil dihapus.']);
    }
}
