<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Dokter;
use App\Models\Pasien;
use App\Models\Apoteker;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Farmasi;
use App\Models\JenisSpesialis;
use App\Models\Kasir;
use App\Models\Perawat;
use App\Models\Poli;
use Yajra\DataTables\Facades\DataTables;

class ManajemenPenggunaController extends Controller
{
    public function index()
    {
        $spesialis = JenisSpesialis::latest()->get();
        $dataPoli = Poli::latest()->get();
        return view('admin.manajemen_pengguna', compact('spesialis', 'dataPoli'));
    }

    public function dataUser()
    {
        $query = User::select(['id', 'username', 'email', 'role']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($user) {
                return '
                    <button class="btn-edit-user text-blue-600 hover:text-blue-800 mr-2" data-id="' . $user->id . '"  title="Edit"
                    >
                        <i class="fa-regular fa-pen-to-square text-lg"></i>
                    </button>
                    <button class="btn-delete text-red-600 hover:text-red-800" data-id="' . $user->id . '" title="Hapus">
                        <i class="fa-regular fa-trash-can text-lg"></i>
                    </button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function dataDokter()
    {
        $query = Dokter::with(['user', 'jenisSpesialis', 'poli'])->latest()->get();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('foto', function ($row) {
                if ($row->foto_dokter) {
                    $url = asset('storage/' . $row->foto_dokter);
                    return '<img src="' . $url . '" alt="Foto Dokter" class="w-12 h-12 rounded-lg object-cover mx-auto shadow">';
                } else {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }
            })
            ->addColumn('username', fn($row) => $row->user->username ?? '-')
            ->addColumn('email_user', fn($row) => $row->user->email ?? '-')
            ->addColumn('role', fn($row) => $row->user->role ?? '-')
            ->addColumn('nama_spesialis', fn($row) => $row->jenisSpesialis->nama_spesialis ?? '-')

            // 🔁 REVISI: tampilkan banyak poli sebagai badge
            ->addColumn('nama_poli', function ($row) {
                if (!$row->relationLoaded('poli')) {
                    $row->load('poli:id,nama_poli');
                }

                if ($row->poli->isEmpty()) {
                    return '<div class="flex flex-wrap gap-1">
                    <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-500 text-xs border border-gray-300">
                        Tidak ada
                    </span>
                </div>';
                }

                $badges = $row->poli->map(function ($poli) {
                    return '<span class="px-2 py-1 rounded-full bg-blue-50 text-blue-700 text-xs border border-blue-200 
                        hover:bg-blue-100 transition-colors">
                    ' . e($poli->nama_poli) . '
                </span>';
                })->implode('');

                return '<div class="flex flex-wrap gap-2">' . $badges . '</div>';
            })


            ->addColumn('action', function ($dokter) {
                return '
                <button class="btn-edit-dokter text-blue-600 hover:text-blue-800 mr-2" data-id="' . $dokter->id . '"  title="Edit">
                    <i class="fa-regular fa-pen-to-square text-lg"></i>
                </button>
                <button class="btn-delete-dokter text-red-600 hover:text-red-800" data-id="' . $dokter->id . '" title="Hapus">
                    <i class="fa-regular fa-trash-can text-lg"></i>
                </button>
            ';
            })
            ->rawColumns(['foto', 'nama_poli', 'action'])
            ->make(true);
    }

    public function dataPasien()
    {
        $query = Pasien::with('user')->select('pasien.*')->latest()->get();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('foto', function ($row) {
                if ($row->foto_pasien) {
                    $url = asset('storage/' . $row->foto_pasien);
                    return '<img src="' . $url . '" alt="Foto Pasien" class="w-12 h-12 rounded-lg object-cover mx-auto shadow">';
                } else {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }
            })
            ->addColumn('username', fn($row) => $row->user->username ?? '-')
            ->addColumn('email_user', fn($row) => $row->user->email ?? '-')
            ->addColumn('role', fn($row) => $row->user->role ?? '-')
            ->addColumn('action', function ($pasien) {
                return '
            <button class="btn-edit-pasien text-blue-600 hover:text-blue-800 mr-2" data-id="' . $pasien->id . '" title="Edit">
                <i class="fa-regular fa-pen-to-square text-lg"></i>
            </button>
            <button class="btn-delete-pasien text-red-600 hover:text-red-800" data-id="' . $pasien->id . '" title="Hapus">
                <i class="fa-regular fa-trash-can text-lg"></i>
            </button>
            ';
            })
            ->rawColumns(['foto', 'action'])
            ->make(true);
    }

    public function dataFarmasi()
    {
        $query = Farmasi::with('user')->select('farmasi.*')->latest()->get();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('foto', function ($row) {
                if ($row->foto_farmasi) {
                    $url = asset('storage/' . $row->foto_farmasi);
                    return '<img src="' . $url . '" alt="Foto Farmasi" class="w-12 h-12 rounded-lg object-cover mx-auto shadow">';
                } else {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }
            })
            ->addColumn('username', fn($row) => $row->user->username ?? '-')
            ->addColumn('email_user', fn($row) => $row->user->email ?? '-')
            ->addColumn('role', fn($row) => $row->user->role ?? '-')
            ->addColumn('action', function ($farmasi) {
                return '
            <button class="btn-edit-farmasi text-blue-600 hover:text-blue-800 mr-2" data-id="' . $farmasi->id . '" title="Edit">
                <i class="fa-regular fa-pen-to-square text-lg"></i>
            </button>
            <button class="btn-delete-farmasi text-red-600 hover:text-red-800" data-id="' . $farmasi->id . '" title="Hapus">
                <i class="fa-regular fa-trash-can text-lg"></i>
            </button>
            ';
            })
            ->rawColumns(['foto', 'action'])
            ->make(true);
    }

    public function dataPerawat()
    {
        $query = Perawat::with('user')->select('perawat.*')->latest()->get();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('foto', function ($row) {
                if ($row->foto_perawat) {
                    $url = asset('storage/' . $row->foto_perawat);
                    return '<img src="' . $url . '" alt="Foto Farmasi" class="w-12 h-12 rounded-lg object-cover mx-auto shadow">';
                } else {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }
            })
            ->addColumn('username', fn($row) => $row->user->username ?? '-')
            ->addColumn('email_user', fn($row) => $row->user->email ?? '-')
            ->addColumn('role', fn($row) => $row->user->role ?? '-')
            ->addColumn('action', function ($perawat) {
                return '
            <button class="btn-edit-perawat text-blue-600 hover:text-blue-800 mr-2" data-id="' . $perawat->id . '" title="Edit">
                <i class="fa-regular fa-pen-to-square text-lg"></i>
            </button>
            <button class="btn-delete-perawat text-red-600 hover:text-red-800" data-id="' . $perawat->id . '" title="Hapus">
                <i class="fa-regular fa-trash-can text-lg"></i>
            </button>
            ';
            })
            ->rawColumns(['foto', 'action'])
            ->make(true);
    }

    public function dataKasir()
    {
        $query = Kasir::with('user')->select('kasir.*')->latest()->get();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('foto', function ($row) {
                if ($row->foto_kasir) {
                    $url = asset('storage/' . $row->foto_kasir);
                    return '<img src="' . $url . '" alt="Foto Farmasi" class="w-12 h-12 rounded-lg object-cover mx-auto shadow">';
                } else {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }
            })
            ->addColumn('username', fn($row) => $row->user->username ?? '-')
            ->addColumn('email_user', fn($row) => $row->user->email ?? '-')
            ->addColumn('role', fn($row) => $row->user->role ?? '-')
            ->addColumn('action', function ($kasir) {
                return '
            <button class="btn-edit-kasir text-blue-600 hover:text-blue-800 mr-2" data-id="' . $kasir->id . '" title="Edit">
                <i class="fa-regular fa-pen-to-square text-lg"></i>
            </button>
            <button class="btn-delete-kasir text-red-600 hover:text-red-800" data-id="' . $kasir->id . '" title="Hapus">
                <i class="fa-regular fa-trash-can text-lg"></i>
            </button>
            ';
            })
            ->rawColumns(['foto', 'action'])
            ->make(true);
    }
}
