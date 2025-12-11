<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\BrandFarmasi;
use Illuminate\Http\Request;

class BrandFarmasiController extends Controller
{
    /**
     * GET /brand-farmasi
     * Digunakan untuk search Brand di TomSelect.
     */
    public function getDataBrandFarmasi(Request $request)
    {
        $search = $request->q; // TomSelect kirim "q" sebagai keyword search

        $query = BrandFarmasi::query();

        if ($search) {
            $query->where('nama_brand', 'like', '%' . $search . '%');
        }

        // batasin biar ringan
        $data = $query->orderBy('nama_brand')->limit(20)->get();

        return response()->json($data);
    }

    /**
     * POST /brand-farmasi
     * Digunakan saat user klik "Add xxxx..." di TomSelect.
     */
    public function createDataBrandFarmasi(Request $request)
    {
        $validated = $request->validate([
            'nama_brand' => ['required', 'string', 'max:100', 'unique:brand_farmasi,nama_brand'],
        ]);

        $brand = BrandFarmasi::create([
            'nama_brand' => $validated['nama_brand'],
        ]);

        // TomSelect butuh object: { id: xxx, nama: "..." }
        return response()->json($brand, 201);
    }

    public function deleteDataBrandFarmasi(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer', 'exists:brand_farmasi,id'],
        ]);

        $brand = BrandFarmasi::findOrFail($request->id);

        // Kalau sudah dipakai di obat, jangan dihapus
        if ($brand->obat()->exists()) {
            return response()->json([
                'message' => 'Brand sudah dipakai di data obat, tidak dapat dihapus.',
            ], 422);
        }

        $brand->delete();

        return response()->json([
            'message' => 'Brand berhasil dihapus.',
        ]);
    }
}
