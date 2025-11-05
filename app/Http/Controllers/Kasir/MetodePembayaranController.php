<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\MetodePembayaran;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MetodePembayaranController extends Controller
{
    public function index()
    {
        return view('kasir.metode-pembayaran.metode-pembayaran');
    }

    public function getDataMetodePembayaran()
    {
        $dataMetodePembayaran = MetodePembayaran::latest()->get();

        return DataTables::of($dataMetodePembayaran)
            ->addIndexColumn()
            ->addColumn('nama_metode', fn($mP) => $mP->nama_metode)
            ->addColumn('action', function ($mP) {
                return '
                <button class="btn-update-metode-pembayaran text-blue-600 hover:text-blue-800 mr-2" data-id="' . $mP->id . '" title="Edit">
                    <i class="fa-regular fa-pen-to-square text-lg"></i>
                </button>
                <button class="btn-delete-metode-pembayaran text-red-600 hover:text-red-800" data-id="' . $mP->id . '" title="Hapus">
                    <i class="fa-regular fa-trash-can text-lg"></i>
                </button>
            ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getDataMetodePembayaramById($id)
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
