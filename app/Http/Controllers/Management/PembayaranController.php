<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function readPembayaran()
    {
        $dataPembayaran = Pembayaran::with(['pasien'])->get();

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataPembayaran,
            'message' => 'Data Pembayaran Berhasil Ditampilkan'
        ]);
    }

    public function createPembayaran(Request $request)
    {
        $request->validate([
            'pasien_id' => ['required', 'exists:pasien,id'],
            'total_tagihan' => ['required'],
            'status' => ['required'],
            'tanggal_pembayaran' => ['required', 'date'],
        ]);

        $dataPembayaran = Pembayaran::create([
            'pasien_id' => $request->pasien_id,
            'total_tagihan' => $request->total_tagihan,
            'status' => $request->status,
            'tanggal_pembayaran' => $request->tanggal_pembayaran,
        ]);

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataPembayaran,
            'message' => 'Data Pembayaran Berhasil Ditambahkan'
        ]);
    }

    public function updatePembayaran(Request $request)
    {
        $request->validate([
            'pasien_id' => ['required', 'exists:pasien,id'],
            'total_tagihan' => ['required'],
            'status' => ['required'],
            'tanggal_pembayaran' => ['required', 'date'],
        ]);

        $dataPembayaran = Pembayaran::findOrFail($request->id);

        $dataPembayaran->update([
            'pasien_id' => $request->pasien_id,
            'total_tagihan' => $request->total_tagihan,
            'status' => $request->status,
            'tanggal_pembayaran' => $request->tanggal_pembayaran,
        ]);

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataPembayaran,
            'message' => 'Data Pembayaran Berhasil Diupdate'
        ]);
    }

    public function deletePembayaran(Request $request)
    {
        $dataPembayaran = Pembayaran::findOrFail($request->id);

        $dataPembayaran->delete();

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataPembayaran,
            'message' => 'Data Pembayaran Berhasil Diupdate'
        ]);
    }
}
