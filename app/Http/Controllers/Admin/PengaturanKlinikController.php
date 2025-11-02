<?php

namespace App\Http\Controllers\Admin;

use App\Models\Obat;
use App\Models\JadwalDokter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\Poli;
use Yajra\DataTables\Facades\DataTables;

class PengaturanKlinikController extends Controller
{
    public function index()
    {
        $dokters = Dokter::select('id', 'nama_dokter')->get();
        $dataPoli = Poli::all();
        return view('admin.pengaturan_klinik', compact('dokters', 'dataPoli'));
    }

    public function dataObat()
    {
        $query = Obat::select(['id', 'nama_obat', 'jumlah', 'dosis', 'total_harga'])->latest()->get();

        return DataTables::of($query)
            ->addIndexColumn()
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

    public function dataJadwalDokter()
    {
        // $query = JadwalDokter::with('dokter:id,nama_dokter')
        //     ->select(['id', 'dokter_id', 'hari', 'jam_awal', 'jam_selesai']);

        $query = JadwalDokter::with('dokter', 'dokter.poli')->latest()->get();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('nama_dokter', function ($jadwal) {
                return $jadwal->dokter->nama_dokter ?? '-';
            })
            ->addColumn('nama_poli', function ($jadwal) {
                return $jadwal->dokter->poli->nama_poli ?? '-';
            })
            ->addColumn('hari_formatted', function ($jadwal) {
                return is_array($jadwal->hari) ? implode(', ', $jadwal->hari) : $jadwal->hari;
            })
            ->addColumn('action', function ($jadwal) {
                return '
                <button class="btn-edit-jadwal text-blue-600 hover:text-blue-800 mr-2" data-id="' . $jadwal->id . '" title="Edit">
                    <i class="fa-regular fa-pen-to-square text-lg"></i>
                </button>
                <button class="btn-delete-jadwal text-red-600 hover:text-red-800" data-id="' . $jadwal->id . '" title="Hapus">
                    <i class="fa-regular fa-trash-can text-lg"></i>
                </button>
            ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
