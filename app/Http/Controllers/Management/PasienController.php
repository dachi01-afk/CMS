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
            'email_pasien'   => 'required|email|unique:user,email',
            'password'       => 'required|string|min:6',
            'nama_pasien'    => 'required|string|max:255',
            'alamat_pasien'  => 'nullable|string|max:255',
            'tanggal_lahir'  => 'nullable|date',
            'jenis_kelamin'  => 'nullable|in:Laki-laki,Perempuan',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email'    => $request->email_pasien,
            'password' => Hash::make($request->password),
            'role'     => 'Pasien',
        ]);

        Pasien::create([
            'user_id'       => $user->id,
            'nama_pasien'   => $request->nama_pasien,
            'alamat'        => $request->alamat_pasien,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
        ]);

        return response()->json(['message' => 'Data pasien berhasil ditambahkan.']);
    }

    public function getPasienById($id)
    {
        // dd($id);
        $data = Pasien::with('user')->findOrFail($id);
        return response()->json(['data' => $data]);
    }

    public function updatePasien(Request $request, $id)
    {
        $pasien = Pasien::findOrFail($id);
        $user = $pasien->user;

        $request->validate([
            'edit_username'       => 'required|string|max:255|unique:user,username,' . $user->id,
            'edit_nama_pasien'    => 'required|string|max:255',
            'edit_email_pasien'   => 'required|email|unique:user,email,' . $user->id,
            'edit_alamat'         => 'nullable|string|max:255',
            'edit_tanggal_lahir'  => 'nullable|date',
            'edit_jenis_kelamin'  => 'nullable|in:Laki-laki,Perempuan',
            'edit_password_pasien'  => 'nullable|string|min:6|confirmed',
        ]);

        // Update user
        $user->username = $request->edit_username;
        $user->email    = $request->edit_email_pasien;

        if ($request->filled('edit_password_pasien')) {
            $user->password = Hash::make($request->edit_password_pasien);
        }
        // update pasien
        $pasien->update([
            'nama_pasien'   => $request->edit_nama_pasien,
            'alamat'        => $request->edit_alamat,
            'tanggal_lahir' => $request->edit_tanggal_lahir,
            'jenis_kelamin' => $request->edit_jenis_kelamin,
        ]);

        return response()->json(['message' => 'Data pasien berhasil diperbarui.']);
    }

    public function deletePasien($id)
    {
        $pasien = Pasien::findOrFail($id);
        $pasien->user->delete();
        $pasien->delete();

        return response()->json(['success' => 'Data pasien berhasil dihapus.']);
    }
}
