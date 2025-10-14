<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Dokter;
use App\Models\Pasien;
use App\Models\Apoteker;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\JenisSpesialis;
use App\Models\Poli;
use Yajra\DataTables\Facades\DataTables;

class ManajemenPenggunaController extends Controller
{
    public function index()
    {
        $spesialis = JenisSpesialis::all();
        $dataPoli = Poli::all();
        return view('admin.manajemen_pengguna', compact('spesialis', 'dataPoli'));
    }

    public function dataUser()
    {
        $query = User::select(['id', 'username', 'email', 'role']);

        return DataTables::of($query)
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
        $query = Dokter::with(['user', 'jenisSpesialis', 'poli'])->get();

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
            ->addColumn('nama_poli', fn($row) => $row->poli->nama_poli ?? '-')
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
            ->rawColumns(['foto', 'action'])
            ->make(true);
    }

    public function dataPasien()
    {
        $query = Pasien::with('user')->select('pasien.*');

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

    public function dataApoteker()
    {
        $query = Apoteker::with('user')->select('apoteker.*');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('foto', function ($row) {
                if ($row->foto_apoteker) {
                    $url = asset('storage/' . $row->foto_apoteker);
                    return '<img src="' . $url . '" alt="Foto Apoteker" class="w-12 h-12 rounded-lg object-cover mx-auto shadow">';
                } else {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }
            })
            ->addColumn('username', fn($row) => $row->user->username ?? '-')
            ->addColumn('email_user', fn($row) => $row->user->email ?? '-')
            ->addColumn('role', fn($row) => $row->user->role ?? '-')
            ->addColumn('action', function ($apoteker) {
                return '
            <button class="btn-edit-apoteker text-blue-600 hover:text-blue-800 mr-2" data-id="' . $apoteker->id . '" title="Edit">
                <i class="fa-regular fa-pen-to-square text-lg"></i>
            </button>
            <button class="btn-delete-apoteker text-red-600 hover:text-red-800" data-id="' . $apoteker->id . '" title="Hapus">
                <i class="fa-regular fa-trash-can text-lg"></i>
            </button>
            ';
            })
            ->rawColumns(['foto', 'action'])
            ->make(true);
    }
}
