<?php

namespace App\Http\Controllers\Farmasi;

use App\Models\JenisObat;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JenisObatController extends Controller
{
    /**
     * GET /brand-farmasi
     * Digunakan untuk search Brand di TomSelect.
     */
    public function getDataJenisObat(Request $request)
    {
        $search = $request->q; // TomSelect kirim "q" sebagai keyword search

        $query = JenisObat::query();

        if ($search) {
            $query->where('nama_jenis_obat', 'like', '%' . $search . '%');
        }

        // batasin biar ringan
        $data = $query->orderBy('nama_jenis_obat')->limit(20)->get();

        return response()->json($data);
    }

    /**
     * POST /brand-farmasi
     * Digunakan saat user klik "Add xxxx..." di TomSelect.
     */
    public function createDataJenisObat(Request $request)
    {
        $validated = $request->validate([
            'nama_jenis_obat' => ['required', 'string', 'max:100', 'unique:jenis_obat,nama_jenis_obat'],
        ]);

        $jenis = JenisObat::create([
            'nama_jenis_obat' => $validated['nama_jenis_obat'],
        ]);

        // TomSelect butuh object: { id: xxx, nama: "..." }
        return response()->json($jenis, 201);
    }

    public function deleteDataJenisObat(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer', 'exists:jenis_obat,id'],
        ]);

        $jenis = JenisObat::findOrFail($request->id);

        // Kalau sudah dipakai di obat, jangan dihapus
        if ($jenis->obat()->exists()) {
            return response()->json([
                'message' => 'Jenis Obat sudah dipakai di data obat, tidak dapat dihapus.',
            ], 422);
        }

        $jenis->delete();

        return response()->json([
            'message' => 'Jenis Obat berhasil dihapus.',
        ]);
    }
}
