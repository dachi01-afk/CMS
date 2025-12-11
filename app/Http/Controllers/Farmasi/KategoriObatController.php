<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\KategoriObat;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class KategoriObatController extends Controller
{
    public function index()
    {
        return view('farmasi.kategori-obat.kategori-obat');
    }
    public function getDataKategoriObat()
    {
        $dataKategoriObat = KategoriObat::latest()->get();

        return DataTables::of($dataKategoriObat)
            ->addIndexColumn()
            ->addColumn('nama_kategori_obat', fn($kategoriObat) => $kategoriObat->nama_kategori_obat ?? '-')
            ->addColumn('action', function ($kategoriObat) {
                return '
                <button class="btn-edit-kategori-obat text-blue-600 hover:text-blue-800 mr-2" 
                        data-id="' . $kategoriObat->id . '"  
                        title="Edit">
                    <i class="fa-regular fa-pen-to-square text-lg"></i>
                </button>
                <button class="btn-delete-kategori-obat text-red-600 hover:text-red-800" 
                        data-id="' . $kategoriObat->id . '" 
                        title="Hapus">
                    <i class="fa-regular fa-trash-can text-lg"></i>
                </button>
                ';
            })
            ->make(true);
    }

    public function createDataKategoriObat(Request $request)
    {
        $request->validate([
            'nama_kategori_obat' => 'required|string|max:255',
        ]);

        KategoriObat::create([
            'nama_kategori_obat' => $request->nama_kategori_obat,
        ]);

        return response()->json(['message' => 'Kategori obat berhasil ditambahkan.'], 201);
    }

    public function getDataKategoriObatById($id)
    {
        $kategoriObat = KategoriObat::findOrFail($id);
        return response()->json($kategoriObat);
    }

    public function updateDataKategoriObat(Request $request)
    {
        $request->validate([
            'nama_kategori_obat' => 'required|string|max:255',
        ]);

        $kategoriObat = KategoriObat::findOrFail($request->id);

        $kategoriObat->update([
            'nama_kategori_obat' => $request->nama_kategori_obat,
        ]);

        return response()->json(['message' => 'Kategori obat berhasil diperbarui.'], 200);
    }

    public function deleteDataKategoriObat(Request $request)
    {
        $dataKategoriObat = KategoriObat::findOrFail($request->id);
        $dataKategoriObat->delete();
        return response()->json(['message' => 'Kategori obat berhasil dihapus.'], 200);
    }
}
