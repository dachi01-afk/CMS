<?php

namespace App\Http\Controllers;

use App\Http\Requests\Farmasi\StorePemakaianBhpRequest;
use App\Models\BahanHabisPakai;
use App\Models\RiwayatPenggunaanBahanHabisPakai;
use Exception;
use Illuminate\Http\Request;

class PemakaianBahanHabisPakaiController extends Controller
{
    public function getDataPemakaianBHP(Request $request)
    {
        $dataBHP = BahanHabisPakai::getData()->get();

        if ($request->has('q') && !empty($request->q)) {
            $term = $request->q;
            $dataBHP->where(function ($q) use ($term) {
                $q->where('nama_barang', 'LIKE', "%{$term}%")
                    ->orWhere('kode', 'LIKE', "%{$term}%");
            });
        };

        return response()->json(['data' => $dataBHP]);
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
