<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use Illuminate\Http\Request;

class DokterController extends Controller
{
    public function index()
    {
        $dataDokter = Dokter::all();
        return view('admin.dokter', compact('dataDokter'));
    }

    public function createDokter(Request $request)
    {
        $request->validate([
            'nama_dokter' => ['required'],
            'spesialisasi' => ['required'],
            'email' => ['required', 'email'],
            'no_hp' => ['required', 'email'],
        ]);

        $dataDokter = Dokter::create([
            'nama_dokter' => $request->nama_dokter,
            'spesialisasi' => $request->spesialisasi,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
        ]);

        return response()->json(['status' => 200, 'data' => $dataDokter, 'message' => 'Data Berhasil Di Tambahkan']);
    }

    public function updateDokter(Request $request)
    {
        $request->validate([
            'nama_dokter' => ['required'],
            'spesialisasi' => ['required'],
            'email' => ['required', 'email'],
            'no_hp' => ['required', 'email'],
        ]);

        $dataDokter = Dokter::where('id', $request->id)->firstOrFail();

        $dataDokter->update([
            'nama_dokter' => $request->nama_dokter,
            'spesialisasi' => $request->spesialisasi,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
        ]);

        return response()->json(['status' => 200, 'data' => $dataDokter, 'massage' => 'Data Berhasil Di Update']);
    }

    public function deleteDokter(Request $request)
    {
        $dataDokter = Dokter::where('id', $request->id);

        $dataDokter->delete();

        return response()->json(['status' => 200, 'data' => $dataDokter, 'massage' => 'Data Berhasil Dihapus']);
    }
}
