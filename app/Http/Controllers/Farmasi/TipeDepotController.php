<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\TipeDepot;
use Illuminate\Http\Request;

class TipeDepotController extends Controller
{
    /**
     * Digunakan untuk search Brand di TomSelect.
     */
    public function getDataTipeDepot(Request $request)
    {
        $search = $request->q; // TomSelect kirim "q" sebagai keyword search

        $query = TipeDepot::query();

        if ($search) {
            $query->where('nama_tipe_depot', 'like', '%' . $search . '%');
        }

        // batasin biar ringan
        $data = $query->orderBy('nama_tipe_depot')->limit(20)->get();

        return response()->json($data);
    }

    /**
     * Digunakan saat user klik "Add xxxx..." di TomSelect.
     */
    public function createDataTipeDepot(Request $request)
    {
        $validated = $request->validate([
            'nama_tipe_depot' => ['required', 'string', 'max:100', 'unique:depot,nama_tipe_depot'],
        ]);

        $jenis = TipeDepot::create([
            'nama_tipe_depot' => $validated['nama_tipe_depot'],
        ]);

        // TomSelect butuh object: { id: xxx, nama: "..." }
        return response()->json($jenis, 201);
    }

    public function deleteDataTipeDepot(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer', 'exists:jenis_obat,id'],
        ]);

        $jenis = TipeDepot::findOrFail($request->id);

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
