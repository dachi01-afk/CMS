<?php

namespace App\Http\Controllers\Management;

use App\Models\User;
use App\Models\Pasien;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class PasienController extends Controller
{

    public function createPasien(Request $request)
    {
        $request->validate([
            'username'       => 'required|string|max:255',
            'email'          => 'required|email|unique:user,email',
            'password'       => 'required|string|min:6',
            'nama_pasien'    => 'required|string|max:255',
            'alamat'         => 'nullable|string|max:255',
            'tanggal_lahir'  => 'nullable|date',
            'jenis_kelamin'  => 'nullable|in:Laki-laki,Perempuan',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'Pasien',
        ]);

        Pasien::create([
            'user_id'       => $user->id,
            'nama_pasien'   => $request->nama_pasien,
            'alamat'        => $request->alamat,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
        ]);

        return response()->json(['success' => 'Data pasien berhasil ditambahkan.']);
    }

    public function getPasienById($id)
    {
        $data = Pasien::with('user')->findOrFail($id);
        return response()->json($data);
    }

    public function updatePasien(Request $request, $id)
    {
        $pasien = Pasien::findOrFail($id);
        $user = $pasien->user;

        $request->validate([
            'username'       => 'required|string|max:255',
            'email'          => 'required|email|unique:user,email,' . $user->id,
            'nama_pasien'    => 'required|string|max:255',
            'alamat'         => 'nullable|string|max:255',
            'tanggal_lahir'  => 'nullable|date',
            'jenis_kelamin'  => 'nullable|in:Laki-laki,Perempuan',
        ]);

        $user->update([
            'username' => $request->username,
            'email'    => $request->email,
        ]);

        $pasien->update([
            'nama_pasien'   => $request->nama_pasien,
            'alamat'        => $request->alamat,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
        ]);

        return response()->json(['success' => 'Data pasien berhasil diperbarui.']);
    }

    public function deletePasien($id)
    {
        $pasien = Pasien::findOrFail($id);
        $pasien->user->delete();
        $pasien->delete();

        return response()->json(['success' => 'Data pasien berhasil dihapus.']);
    }
}
