<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\TransaksiApoteker;
use Illuminate\Http\Request;

class TransaksiApotekerController extends Controller
{
    public function createTransaksiApoteker(Request $request)
    {
        $request->validate([
            'resep_id' => ['required', 'exists:resep,id'],
            'apoteker_id' => ['required', 'exists:apoteker,id'],
            'tanggal_transaksi' => ['required', 'date'],
            'total_harga' => ['required'],
        ]);

        $dataTransaksiApoteker = TransaksiApoteker::create([
            'resep_id' => $request->resep_id,
            'apoteker_id' => $request->apoteker_id,
            'tanggal_transaksi' => $request->tanggal_transaksi,
            'total_harga' => $request->total_harga,
        ]);

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataTransaksiApoteker,
            'message' => 'Data Transaksi Apoteker Berhasil Ditambahkan',
        ]);
    }

    public function updateTransaksiApoteker(Request $request)
    {
        $request->validate([
            'resep_id' => ['required', 'exists:resep,id'],
            'apoteker_id' => ['required', 'exists:apoteker,id'],
            'tanggal_transaksi' => ['required', 'date'],
            'total_harga' => ['required'],
        ]);

        $dataTransaksiApoteker = TransaksiApoteker::findOrFail($request->id);

        $dataTransaksiApoteker->update([
            'resep_id' => $request->resep_id,
            'apoteker_id' => $request->apoteker_id,
            'tanggal_transaksi' => $request->tanggal_transaksi,
            'total_harga' => $request->total_harga,
        ]);

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataTransaksiApoteker,
            'message' => 'Data Transaksi Apoteker Berhasil Diupdate',
        ]);
    }

    public function deleteTransaksiApoteker(Request $request)
    {
        $dataTransaksiApoteker = TransaksiApoteker::findOrFail($request->id);
        $dataTransaksiApoteker->delete();

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataTransaksiApoteker,
            'message' => 'Data Transaksi Apoteker Berhasil Dihapus',
        ]);
    }
}
