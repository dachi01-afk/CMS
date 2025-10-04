<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function createUser(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:user,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $dataUser = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'data' => $dataUser,
            'message' => 'Data Berhasil Di Tambahkan',
        ], 200);
    }

    public function updateUser(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'exists:user,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $dataUser = User::where('id', $request->id)->firstOrFail();

        if (!$dataUser) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        $dataUser->update([
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        return response()->json([
            'success' => true,
            'data' => $dataUser,
            'message' => 'Data Berhasil Di Update',
        ], 200);
    }

    public function deleteUser(Request $request)
    {
        $dataUser = User::find($request->id);

        if (!$dataUser) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        $dataUser->delete();

        return response()->json([
            'success' => true,
            'data' => $dataUser,
            'message' => 'Data Berhasil Di Dihapus',
        ], 200);
    }
}
