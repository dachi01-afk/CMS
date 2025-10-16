<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use App\Models\Poli;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class LayananController extends Controller
{
    public function index()
    {
        $dataPoli = Poli::all();
        return view('admin.layanan.layanan', compact('dataPoli'));
    }

    public function getDataLayanan()
    {
        $dataLayanan = Layanan::with('poli')->get();

        return DataTables::of($dataLayanan)
            ->addIndexColumn()
            ->addColumn('nama_poli', fn($row) => $row->poli->nama_poli ?? '-')
            ->addColumn('nama_layanan', fn($row) => $row->nama_layanan ?? '-')
            ->addColumn('harga_layanan', fn($row) => $row->harga_layanan ?? '-')
            ->addColumn('action', function ($l) {
                return '
                <button class="btn-edit-layanan text-blue-600 hover:text-blue-800 mr-2" 
                        data-id="' . $l->id . '" 
                        data-poli-id="' . $l->poli->id . '"     
                        title="Edit">
                    <i class="fa-regular fa-pen-to-square text-lg"></i>
                </button>
                <button class="btn-delete-layanan text-red-600 hover:text-red-800" 
                        data-id="' . $l->id . '" 
                        data-poli-id="' . $l->poli->id . '"  
                        title="Hapus">
                    <i class="fa-regular fa-trash-can text-lg"></i>
                </button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function createDataLayanan(Request $request)
    {
        $request->validate([
            'poli_id' => ['required', 'exists:poli,id'],
            'nama_layanan' => ['required'],
    'harga_layanan' => ['required', 'numeric', 'min:0'],
        ]);

        $dataPoli = Poli::findOrFail($request->poli_id);

        $dataLayanan = Layanan::create([
            'poli_id' => $request->poli_id,
            'nama_layanan' => $request->nama_layanan,
            'harga_layanan' => $request->harga_layanan,
        ]);

        return response()->json([
            'message' => 'Berhasil Menambahkan Data Layanan Pada Poli ' . $dataPoli->nama_poli . ' ',
        ]);
    }

    public function getDataLayananById($id)
    {
        $dataLayanan = Layanan::with('poli')->where('id', $id)->firstOrFail();

        return response()->json([
            'data' => $dataLayanan,
        ]);
    }

    public function updateDataLayanan(Request $request)
    {
        $request->validate([
            'poli_id' => ['required', 'exists:poli,id'],
            'nama_layanan' => ['required'],
            'harga_layanan' => ['required'],
        ]);

        $dataLayanan = Layanan::with('poli')->findOrFail($request->id);

        $dataLayanan->update([
            'poli_id' => $request->poli_id,
            'nama_layanan' => $request->nama_layanan,
            'harga_layanan' => $request->harga_layanan,
        ]);

        return response()->json([
            'message' => 'Berhasil Merubah Data Layanan Pada Poli ' . $dataLayanan->poli->nama_poli . '',
        ]);
    }

    public function deleteDataLayanan(Request $request)
    {
        $dataLayanan = Layanan::with('poli')->findOrFail($request->id);

        $dataLayanan->delete();

        return response()->json([
            'message' => "Berhasil Menghapus 1 Data Layanan Dari Poli " . $dataLayanan->poli->nama_poli . '',
        ]);
    }
}
