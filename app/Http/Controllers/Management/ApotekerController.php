<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Apoteker;
use Illuminate\Http\Request;

class ApotekerController extends Controller
{
    public function createApoteker(Request $request)
    {
        $request->validate([
            'nama_apoteker' => ['required'],
            'email_apoteker' => ['required', 'email'],
            'no_hp_apoteker' => ['required'],
        ]);

        $dataApoteker = Apoteker::create([
            'nama_apoteker' => $request->nama_apoteker,
            'email_apoteker' => $request->email_apoteker,
            'no_hp_apoteker' => $request->no_hp_apoteker,
        ]);

        return response()->json(['status' => 200, 'data' => $dataApoteker, 'message' => 'Data Berhasil Di Tambahkan']);
    }

    public function updateApoteker(Request $request)
    {
        $request->validate([
            'nama_apoteker' => ['required'],
            'email_apoteker' => ['required', 'email'],
            'no_hp_apoteker' => ['required'],
        ]);

        $dataApoteker = Apoteker::where('id', $request->id)->firstOrFail();

        $dataApoteker->update([
            'nama_apoteker' => $request->nama_apoteker,
            'email_apoteker' => $request->email_apoteker,
            'no_hp_apoteker' => $request->no_hp_apoteker,
        ]);

        return response()->json(['status' => 200, 'data' => $dataApoteker, 'massage' => 'Data Berhasil Di Update']);
    }

    public function deleteApoteker(Request $request)
    {
        $dataApoteker = Apoteker::where('id', $request->id);

        $dataApoteker->delete();

        return response()->json(['status' => 200, 'data' => $dataApoteker, 'massage' => 'Data Berhasil Dihapus']);
    }
}
