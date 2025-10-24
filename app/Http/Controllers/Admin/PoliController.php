<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Poli;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PoliController extends Controller
{
    public function index()
    {
        return view('admin.poli.poli');
    }

    public function getDataPoli()
    {
        $dataPoli = Poli::latest()->get();

        return DataTables::of($dataPoli)
            ->addIndexColumn()
            ->addColumn('nama_poli', fn($poli) => $poli->nama_poli ?? '-')
            ->addColumn('action', function ($poli) {
                return '
                <button class="btn-edit-poli text-blue-600 hover:text-blue-800 mr-2" 
                        data-id="' . $poli->id . '"  title="Edit">
                    <i class="fa-regular fa-pen-to-square text-lg"></i>
                </button>
                <button class="btn-delete-poli text-red-600 hover:text-red-800" 
                        data-id="' . $poli->id . '" title="Hapus">
                    <i class="fa-regular fa-trash-can text-lg"></i>
                </button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function createDataPoli(Request $request)
    {
        $request->validate([
            'nama_poli' => ['required'],
        ]);

        $dataPoli = Poli::create([
            'nama_poli' => $request->nama_poli
        ]);


        return response()->json([
            'message' => 'Berhasil Menambahkan Data Poli'
        ]);
    }

    public function getDataPoliById($id)
    {
        $dataPoli = Poli::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $dataPoli
        ]);
    }

    public function updateDataPoli(Request $request)
    {
        $request->validate([
            'nama_poli' => ['required'],
        ]);

        $dataPoli = Poli::findOrFail($request->id);

        $dataPoli->update([
            'nama_poli' => $request->nama_poli,
        ]);

        return response()->json([
            'message' => 'Berhasil Update Data Poli'
        ]);
    }

    public function deleteDataPoli(Request $request)
    {
        $dataPoli = Poli::findOrFail($request->id);

        $dataPoli->delete();

        return response()->json(['message' => 'Berhasil Menghapus Data Poli']);
    }
}
