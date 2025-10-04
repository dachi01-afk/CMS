<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Apoteker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApotekerController extends Controller
{
    public function createApoteker(Request $request)
    {
        $request->validate([
            'nama_apoteker' => ['required', 'string', 'max:100'],
            'email_apoteker' => ['required', 'email', 'unique:dokters,email'],
            'no_hp_apoteker' => ['required', 'regex:/^[0-9]+$/', 'digits_between:10,15'],
        ]);

        $dataApoteker = Apoteker::create([
            'nama_apoteker' => $request->nama_apoteker,
            'email_apoteker' => $request->email_apoteker,
            'no_hp_apoteker' => $request->no_hp_apoteker,
        ]);

        return response()->json(['status' => 201, 'data' => $dataApoteker, 'message' => 'Data Berhasil Di Tambahkan']);
    }

    public function updateApoteker(Request $request)
    {
        $request->validate([
            'nama_apoteker' => ['required'],
            'email_apoteker' => ['required', 'email'],
            'no_hp_apoteker' => ['required'],
        ]);

        $userId = Auth::id();

        $dataApoteker = Apoteker::where('user_id', $userId)->firstOrFail();

        $dataApoteker->update([
            'nama_apoteker' => $request->nama_apoteker,
            'email_apoteker' => $request->email_apoteker,
            'no_hp_apoteker' => $request->no_hp_apoteker,
        ]);

        return response()->json(['status' => 200, 'data' => $dataApoteker, 'message' => 'Data Berhasil Di Update']);
    }

    public function deleteApoteker(Request $request)
    {
        $dataApoteker = Apoteker::findOrFail($request->id);

        $dataApoteker->delete();

        return response()->json(['status' => 200, 'data' => $dataApoteker, 'message' => 'Data Berhasil Dihapus']);
    }
}
