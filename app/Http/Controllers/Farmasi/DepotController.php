<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\Depot;
use Illuminate\Http\Request;

class DepotController extends Controller
{
    /**
     * Digunakan untuk search Brand di TomSelect.
     */
    public function getDataDepot(Request $request)
    {
        $search = $request->q; // TomSelect kirim "q" sebagai keyword search

        $query = Depot::query();

        if ($search) {
            $query->where('nama_depot', 'like', '%' . $search . '%');
        }

        // batasin biar ringan
        $data = $query->orderBy('nama_depot')->limit(20)->get();

        return response()->json($data);
    }

    /**
     * Digunakan saat user klik "Add xxxx..." di TomSelect.
     */
    public function createDataDepot(Request $request)
    {
        $validated = $request->validate([
            'nama_depot' => ['required', 'string', 'max:100', 'unique:depot,nama_depot'],
        ]);

        $jenis = Depot::create([
            'nama_depot' => $validated['nama_depot'],
        ]);

        // TomSelect butuh object: { id: xxx, nama: "..." }
        return response()->json($jenis, 201);
    }

    public function deleteDataDepot(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer', 'exists:depot,id'],
        ]);

        $jenis = Depot::findOrFail($request->id);

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
