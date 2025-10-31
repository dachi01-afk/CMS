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
        return view('admin.layanan.layanan');
    }

    public function getDataLayanan()
    {
        $dataLayanan = Layanan::latest()->get();

        return DataTables::of($dataLayanan)
            ->addIndexColumn()
            ->addColumn('nama_layanan', fn($row) => $row->nama_layanan ?? '-')
            ->addColumn('harga_layanan', fn($row) => $row->harga_layanan ?? '-')
            ->addColumn('action', function ($l) {
                return '
                <button class="btn-edit-layanan text-blue-600 hover:text-blue-800 mr-2" 
                        data-id="' . $l->id . '"  
                        title="Edit">
                    <i class="fa-regular fa-pen-to-square text-lg"></i>
                </button>
                <button class="btn-delete-layanan text-red-600 hover:text-red-800" 
                        data-id="' . $l->id . '" 
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
        // --- Ubah format total_harga dari teks jadi numeric ---
        $request->merge([
            'harga_layanan' => floatval(str_replace(['.', ','], ['', '.'], $request->harga_layanan))
        ]);

        $request->validate([
            'nama_layanan' => ['required'],
            'harga_layanan' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ]);


        $dataLayanan = Layanan::create([
            'nama_layanan' => $request->nama_layanan,
            'harga_layanan' => $request->harga_layanan,
        ]);

        return response()->json([
            'message' => 'Berhasil Menambahkan Data Layanan Pada Poli ',
        ]);
    }

    public function getDataLayananById($id)
    {
        $dataLayanan = Layanan::where('id', $id)->firstOrFail();

        return response()->json([
            'data' => $dataLayanan,
        ]);
    }

    public function updateDataLayanan(Request $request)
    {

        // --- Ubah format total_harga dari teks jadi numeric ---
        $request->merge([
            'harga_layanan' => floatval(str_replace(['.', ','], ['', '.'], $request->harga_layanan))
        ]);

        $request->validate([
            'nama_layanan' => ['required'],
            'harga_layanan' => ['required'],
        ]);

        $dataLayanan = Layanan::findOrFail($request->id);

        $dataLayanan->update([
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
