<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Dokter;
use App\Models\Pasien;
use App\Models\Apoteker;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class ManajemenPenggunaController extends Controller
{
    public function index()
    {

        return view('admin.manajemen_pengguna');
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
        $query = Dokter::select(['id', 'nama_dokter', 'spesialisasi', 'email', 'no_hp']);

        return DataTables::of($query)
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
            ->rawColumns(['action'])
            ->make(true);
    }

    public function dataPasien()
    {
        $query = Pasien::select(['id', 'nama_pasien', 'alamat', 'tanggal_lahir', 'jenis_kelamin']);

        return DataTables::of($query)
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
            ->rawColumns(['action'])
            ->make(true);
    }

    public function dataApoteker()
    {
        $query = Apoteker::select(['id', 'nama_apoteker', 'email_apoteker', 'no_hp_apoteker']);

        return DataTables::of($query)
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
            ->rawColumns(['action'])
            ->make(true);
    }
}
