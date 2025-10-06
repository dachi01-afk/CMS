<?php

namespace App\Http\Controllers\Management;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function createUser(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:user,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role'     => ['required'],
        ]);

        $dataUser = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'data' => $dataUser,
            'message' => 'Data Berhasil Di Tambahkan',
        ], 201);
    }

    public function getUserById($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Data pengguna tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $user
        ]);
    }

    public function updateUser(Request $request, $id)
    {
        $dataUser = User::findOrFail($id);

        $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'email'    => [
                'required',
                'email',
                Rule::unique('user', 'email')->ignore($dataUser->id),
            ],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $updateData = [
            'username' => $request->username,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }
        $dataUser->update($updateData);

        return response()->json([
            'success' => true,
            'data' => $dataUser,
            'message' => 'Data Berhasil Di Update',
        ], 200);
    }

    public function deleteUser($id)
    {
        $dataUser = User::findOrFail($id);

        $dataUser->delete();

        return response()->json([
            'success' => true,
            'data' => $dataUser,
            'message' => 'Data Berhasil Di Dihapus',
        ], 200);
    }
}
