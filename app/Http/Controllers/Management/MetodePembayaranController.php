<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\MetodePembayaran;
use Illuminate\Http\Request;

class MetodePembayaranController extends Controller
{
    public function getDataMetodePembayaran($id)
    {
        $dataMetodePembayaran = MetodePembayaran::findOrFail($id);

        return response()->json([
            'data' => $dataMetodePembayaran
        ]);
    }

    public function createData(Request $request)
    {
        $request->validate([
            'nama_metode' => ['required'],
        ]);

        MetodePembayaran::create([
            'nama_metode' => $request->nama_metode,
        ]);

        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => "Berhasil Menambahkan Data Metode Pembayaran"
        ]);
    }

    public function updateData(Request $request)
    {
        $request->validate([
            'nama_metode' => ['required'],
        ]);

        $dataMetodePembayaran = MetodePembayaran::findOrFail($request->id);

        $dataMetodePembayaran->update([
            'nama_metode' => $request->nama_metode
        ]);

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataMetodePembayaran,
            'message' => "Berhasil Update Data Metode Pembayaran"
        ]);
    }

    public function deleteData($id)
    {
        $dataMetodePembayaran = MetodePembayaran::findOrFail($id);

        $dataMetodePembayaran->delete();

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $dataMetodePembayaran,
            'message' => "Berhasil Menghapus Data Metode Pembayaran"
        ]);
    }
}
