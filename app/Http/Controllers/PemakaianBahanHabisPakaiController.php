<?php

namespace App\Http\Controllers;

use App\Http\Requests\Farmasi\StorePemakaianBhpRequest;
use App\Models\BahanHabisPakai;
use App\Models\Depot;
use App\Models\RiwayatPenggunaanBahanHabisPakai;
use Exception;
use Illuminate\Http\Request;

class PemakaianBahanHabisPakaiController extends Controller
{
    public function getDataPemakaianBHP(Request $request)
    {
        $query = BahanHabisPakai::with(['satuanBHP']);

        if ($request->has('q') && !empty($request->q)) {
            $term = $request->q;
            $query->where(function ($q) use ($term) {
                $q->where('nama_barang', 'LIKE', "%{$term}%")
                    ->orWhere('kode', 'LIKE', "%{$term}%");
            });
        }

        $dataBHP = $query->orderBy('nama_barang', 'asc')->limit(50)->get();

        return response()->json([
            'status' => 'success',
            'data'   => $dataBHP
        ]);
    }

    public function getDataDepot(Request $request)
    {
        $bhpId = $request->bhp_id;

        $dataDepot = Depot::getData($bhpId)->get();

        return response()->json([
            'status' => 'success',
            'data' => $dataDepot,
        ]);
    }

    public function storeDataPemakaianBHP(StorePemakaianBhpRequest $request)
    {
        try {
            $dataBhp = $request->validated();
            RiwayatPenggunaanBahanHabisPakai::simpanData($dataBhp);

            return response()->json([
                'status' => 'success',
                'pesan' => 'Data pemakaian BHP berhasil disimpan.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'pesan' => $e->getMessage(),
            ], 422);
        }
    }
}
