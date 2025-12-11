<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\SatuanObat;
use Illuminate\Http\Request;

class SatuanObatController extends Controller
{
    /**
     * GET /brand-farmasi
     * Digunakan untuk search Brand di TomSelect.
     */
    public function getDataSatuanObat(Request $request)
    {
        $search = $request->q; // TomSelect kirim "q" sebagai keyword search

        $query = SatuanObat::query();

        if ($search) {
            $query->where('nama_satuan_obat', 'like', '%' . $search . '%');
        }

        // batasin biar ringan
        $data = $query->orderBy('nama_satuan_obat')->limit(20)->get();

        return response()->json($data);
    }

    /**
     * POST /brand-farmasi
     * Digunakan saat user klik "Add xxxx..." di TomSelect.
     */
    public function createDataSatuanObat(Request $request)
    {
        $validated = $request->validate([
            'nama_satuan_obat' => ['required', 'string', 'max:100', 'unique:satuan_obat,nama_satuan_obat'],
        ]);

        $satuan = SatuanObat::create([
            'nama_satuan_obat' => $validated['nama_satuan_obat'],
        ]);

        // TomSelect butuh object: { id: xxx, nama: "..." }
        return response()->json($satuan, 201);
    }

    public function deleteDataSatuanObat(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer', 'exists:jenis_obat,id'],
        ]);

        $satuan = SatuanObat::findOrFail($request->id);

        // Kalau sudah dipakai di obat, jangan dihapus
        if ($satuan->obat()->exists()) {
            return response()->json([
                'message' => 'Jenis Obat sudah dipakai di data obat, tidak dapat dihapus.',
            ], 422);
        }

        $satuan->delete();

        return response()->json([
            'message' => 'Jenis Obat berhasil dihapus.',
        ]);
    }
}
