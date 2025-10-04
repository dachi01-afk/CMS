<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'username' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $dataDokter = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'Dokter',
        ]);

        return response()->json(['status' => 200, 'data' => $dataDokter, 'message' => 'Anda Berhasil Mendaftar']);
    }
    
    public function prosesLogin(Request $request)
    {
        $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        $user = User::where('username', $request->username)->firstOrFail();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Username yang Anda masukkan salah.'],
                'password' => ['Password yang Anda masukkan salah.'],
            ]);
        }

        if(!$user) {
            return response()->json(['status' => 'Error', 'message' => 'Username Atau Password Anda Salah']);
        } elseif($user){
            return response()->json(['status', 200, 'message' => 'Login Berhasil']);
        }
    }
}
