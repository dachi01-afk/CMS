<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
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
        $dataLayanan = Layanan::with('poli')->get();

        return DataTables::of($dataLayanan)
            ->addIndexColumn()
            ->addColumn('nama_poli', fn($l) => $l->poli->nama_poli ?? '-')
            ->addColumn('nama_layanan', fn($l) => $l->nama_layanan ?? '-')
            ->addColumn('harga_layanan', fn($l) => $l->harga_layanan ?? '-')
            ->addColumn('action', function ($l) {
                return '
                <button class="btn-edit-poli text-blue-600 hover:text-blue-800 mr-2" 
                        data-id="' . $l->id . '" 
                        data-poli-id="' . $l->poli->id . '"  
                        title="Edit">
                    <i class="fa-regular fa-pen-to-square text-lg"></i>
                </button>
                <button class="btn-delete-poli text-red-600 hover:text-red-800" 
                        data-id="' . $l->id . '" 
                        data-poli-id="' . $l->poli->id . '"  
                        title="Hapus">
                    <i class="fa-regular fa-trash-can text-lg"></i>
                </button>
                ';
            });
    }
}
