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
    public function login()
    {
        return view('dokter.login');
    }

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

        return redirect()->route('');

        // return response()->json(['status' => 200, 'data' => $dataDokter, 'message' => 'Anda Berhasil Mendaftar']);
    }

    public function prosesLogin(Request $request)
    {
        // return $request;
        $credentials = $request->validate([
            'email' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->role === 'Dokter') {
                return redirect()->route('dokter.dashboard');
            } else {
                Auth::logout();
                return redirect()->back();
            }
        }
        return redirect()->back();
    }
}
