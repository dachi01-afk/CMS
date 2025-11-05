<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\Obat;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ObatController extends Controller
{
    public function index()
    {
        return view('farmasi.obat.obat');
    }

    public function getDataObat()
    {
        $dataObat = Obat::latest()->get();

        return DataTables::of($dataObat)
            ->addIndexColumn()
            ->addColumn('nama_obat', fn($obat) => $obat->nama_obat ?? '-')
            ->addColumn('jumlah', fn($obat) => is_null($obat->jumlah) ? 0 : (int) $obat->jumlah)
            ->addColumn('dosis', fn($obat) => $obat->dosis ?? '-')
            ->addColumn('total_harga', fn($obat) => $obat->total_harga ?? '-')
            ->addColumn('action', function ($obat) {
                return '
        <button class="btn-edit-obat text-blue-600 hover:text-blue-800 mr-2" data-id="' . $obat->id . '" title="Edit">
            <i class="fa-regular fa-pen-to-square text-lg"></i>
        </button>
        <button class="btn-delete-obat text-red-600 hover:text-red-800" data-id="' . $obat->id . '" title="Hapus">
            <i class="fa-regular fa-trash-can text-lg"></i>
        </button>
        ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function createObat(Request $request)
    {
        // --- Ubah format total_harga dari teks jadi numeric ---
        $request->merge([
            'total_harga' => floatval(str_replace(['.', ','], ['', '.'], $request->total_harga))
        ]);

        $request->validate([
            'nama_obat' => ['required'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'dosis' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'total_harga' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ]);

        $dataObat = Obat::create([
            'nama_obat' => $request->nama_obat,
            'jumlah' => $request->jumlah,
            'dosis' => $request->dosis,
            'total_harga' => $request->total_harga,
        ]);

        return response()->json([
            'status' => 200,
            'data' => $dataObat,
            'message' => 'Berhasil Menambahkan Data Obat!'
        ]);
    }

    public function getObatById($id)
    {
        $Obat = Obat::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $Obat
        ]);
    }

    public function updateObat(Request $request, $id)
    {
        $dataObat = Obat::findOrFail($id);

        $request->merge([
            'total_harga' => floatval(str_replace(['.', ','], ['', '.'], $request->total_harga))
        ]);

        $request->validate([
            'nama_obat' => ['required'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'dosis' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'total_harga' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ]);


        $dataObat->update(attributes: [
            'nama_obat' => $request->nama_obat,
            'jumlah' => $request->jumlah,
            'dosis' => $request->dosis,
            'total_harga' => $request->total_harga,
        ]);

        return response()->json([
            'status' => 200,
            'data' => $dataObat,
            'message' => 'Berhasil Mengupdate Data Obat!'
        ]);
    }

    public function deleteObat($id)
    {
        $dataObat = Obat::findOrFail($id);
        $dataObat->delete();
        return response()->json([
            'status' => 200,
            'data' => $dataObat,
            'message' => 'Berhasil Menghapus Data Obat!'
        ]);
    }
}
