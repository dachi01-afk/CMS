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
        $query = Dokter::with([
            'user:id,username,email,role',
            'jenisSpesialis:id,nama_spesialis',
            'poli:id,nama_poli',
        ])
            ->latest()
            ->get();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('foto', function ($row) {
                if ($row->foto_dokter) {
                    $url = asset('storage/' . $row->foto_dokter);
                    return '<img src="' . $url . '" alt="Foto Dokter" class="w-12 h-12 rounded-lg object-cover mx-auto shadow">';
                }

                return '<span class="text-gray-400 italic">Tidak ada</span>';
            })
            ->addColumn('username', fn($row) => $row->user->username ?? '-')
            ->addColumn('email_user', fn($row) => $row->user->email ?? '-')
            ->addColumn('role', fn($row) => $row->user->role ?? '-')
            ->addColumn('nama_spesialis', fn($row) => $row->jenisSpesialis->nama_spesialis ?? '-')

            // 🔁 Tampilkan banyak poli sebagai badge (pakai relasi many-to-many)
            ->addColumn('nama_poli', function ($row) {
                if ($row->poli->isEmpty()) {
                    return '<div class="flex flex-wrap gap-1">
                    <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-500 text-xs border border-gray-300">
                        Tidak ada
                    </span>
                </div>';
                }

                $badges = $row->poli->map(function ($poli) {
                    return '<span class="px-2 py-1 rounded-full bg-blue-50 text-blue-700 text-xs border border-blue-200 
                        hover:bg-blue-100 transition-colors">'
                        . e($poli->nama_poli) .
                        '</span>';
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

                $showUrl   = route('manajemen_pengguna.show.detail.pasien', $pasien->no_emr);
                $stikerUrl = route('manajemen_pengguna.cetak.stiker.pasien', $pasien->no_emr);

                return '
    <div class="relative inline-block text-left">

        <!-- Trigger -->
        <button type="button"
    class="btn-action-menu w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-200">
    <i class="fa-solid fa-ellipsis-vertical text-gray-700"></i>
</button>

        <!-- Dropdown -->
        <div class="hidden absolute right-0 mt-2 w-40 origin-top-right bg-white border border-gray-200 
                    rounded-xl shadow-lg z-50 action-dropdown">

            <a href="' . $showUrl . '" 
               class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                <i class="fa-regular fa-eye text-blue-600"></i>
                Detail Pasien
            </a>

            <a href="' . $stikerUrl . '" target="_blank"
               class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                <i class="fa-solid fa-print text-amber-600"></i>
                Cetak Stiker
            </a>

            <button type="button"
                class="btn-edit-pasien flex items-center gap-2 w-full px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                data-id="' . $pasien->id . '">
                <i class="fa-regular fa-pen-to-square text-green-600"></i>
                Edit Pasien
            </button>

            <button type="button"
                class="btn-delete-pasien flex items-center gap-2 w-full px-3 py-2 text-sm text-red-700 hover:bg-gray-50"
                data-id="' . $pasien->id . '">
                <i class="fa-regular fa-trash-can text-red-600"></i>
                Hapus Pasien
            </button>

        </div>
    </div>
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
        $query = Perawat::with([
            'user',
            'perawatDokterPoli.dokter',
            'perawatDokterPoli.poli',
        ])
            ->select('perawat.*')
            ->latest();

        // helper untuk teks fallback (single value)
        $fallback = function (string $rel, $value) {
            return ($value !== null && $value !== '')
                ? e($value)
                : '<span class="text-gray-400 italic">Tidak ada Data ' . ucfirst($rel) . '</span>';
        };

        return DataTables::of($query)
            ->addIndexColumn()

            // ================= FOTO =================
            ->addColumn('foto', function (Perawat $row) {
                if (!empty($row->foto_perawat)) {
                    $url = asset('storage/' . $row->foto_perawat);
                    return '<img src="' . $url . '" alt="Foto Perawat" class="w-12 h-12 rounded-lg object-cover mx-auto shadow">';
                }
                return '<span class="text-gray-400 italic">Tidak ada</span>';
            })

            // ================= USER =================
            ->addColumn('username', function (Perawat $row) use ($fallback) {
                return $fallback('User', optional($row->user)->username);
            })

            ->addColumn('email_user', function (Perawat $row) use ($fallback) {
                return $fallback('User', optional($row->user)->email);
            })

            ->addColumn('role', function (Perawat $row) use ($fallback) {
                return $fallback('User', optional($row->user)->role);
            })

            // ================= POLI (dari relasi dokterPolis) =================
            ->addColumn('nama_poli', function (Perawat $row) {
                // Ambil semua nama poli dari relasi dokterPolis
                $polis = $row->perawatDokterPoli
                    ->pluck('poli.nama_poli')
                    ->filter()
                    ->unique()
                    ->values();

                if ($polis->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada Data Poli</span>';
                }

                $htmlList = $polis->map(function ($nama) {
                    return '<span class="inline-block">' . e($nama) . '</span>';
                })->implode('<br>');

                return $htmlList;
            })

            // ================= DOKTER (dari relasi dokterPolis) =================
            ->addColumn('nama_dokter', function (Perawat $row) {
                $dokters = $row->perawatDokterPoli
                    ->pluck('dokter.nama_dokter')
                    ->filter()
                    ->unique()
                    ->values();

                if ($dokters->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada Data Dokter</span>';
                }

                $htmlList = $dokters->map(function ($nama) {
                    return '<span class="inline-block">' . e($nama) . '</span>';
                })->implode('<br>');

                return $htmlList;
            })

            // ================= ACTION =================
            ->addColumn('action', function (Perawat $perawat) {
                return '
                <button class="btn-edit-perawat text-blue-600 hover:text-blue-800 mr-2" data-id="' . $perawat->id . '" title="Edit">
                    <i class="fa-regular fa-pen-to-square text-lg"></i>
                </button>
                <button class="btn-delete-perawat text-red-600 hover:text-red-800" data-id="' . $perawat->id . '" title="Hapus">
                    <i class="fa-regular fa-trash-can text-lg"></i>
                </button>
            ';
            })

            // kolom yang berisi HTML
            ->rawColumns([
                'foto',
                'username',
                'email_user',
                'role',
                'nama_poli',
                'nama_dokter',
                'action',
            ])

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
