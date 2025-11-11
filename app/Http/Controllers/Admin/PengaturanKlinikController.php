<?php

namespace App\Http\Controllers\Admin;

use App\Models\Obat;
use App\Models\JadwalDokter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\Poli;
use Illuminate\Support\Facades\DB;
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

    public function dataJadwalDokter(Request $request)
    {
        $query = JadwalDokter::with([
            'dokter:id,nama_dokter',
            'poli:id,nama_poli',
            'dokterPoli.poli:id,nama_poli',
        ])->latest(); 

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('nama_dokter', fn($jd) => $jd->dokter->nama_dokter ?? '-')
            ->addColumn('nama_poli', function ($jd) {
                $namaPoli = ($jd->poli->nama_poli ?? null)
                    ?? optional(optional($jd->dokterPoli)->poli)->nama_poli
                    ?? '-';
                return $namaPoli === '-' ? $namaPoli
                    : '<span class="inline-block px-2 py-1 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-md">'
                    . e($namaPoli) . '</span>';
            })
            ->addColumn('hari_formatted', fn($jd) => is_array($jd->hari) ? implode(', ', $jd->hari) : ($jd->hari ?? '-'))
            ->editColumn('jam_awal',    fn($jd) => $jd->jam_awal    ? substr($jd->jam_awal, 0, 5)    : '-')
            ->editColumn('jam_selesai', fn($jd) => $jd->jam_selesai ? substr($jd->jam_selesai, 0, 5) : '-')

            // ====== custom global search utk kolom relasi/format ======
            ->filter(function ($q) use ($request) {
                $search = strtolower($request->input('search.value', ''));
                if ($search === '') return;

                $q->where(function ($qq) use ($search) {
                    $qq->orWhereRaw('LOWER(jadwal_dokter.jam_awal) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(jadwal_dokter.jam_selesai) LIKE ?', ["%{$search}%"])
                        // jika kolom hari disimpan sebagai string:
                        ->orWhereRaw('LOWER(jadwal_dokter.hari) LIKE ?', ["%{$search}%"])
                        // relasi dokter
                        ->orWhereHas('dokter', function ($d) use ($search) {
                            $d->whereRaw('LOWER(nama_dokter) LIKE ?', ["%{$search}%"]);
                        })
                        // relasi poli langsung
                        ->orWhereHas('poli', function ($p) use ($search) {
                            $p->whereRaw('LOWER(nama_poli) LIKE ?', ["%{$search}%"]);
                        })
                        // fallback via pivot
                        ->orWhereHas('dokterPoli.poli', function ($p) use ($search) {
                            $p->whereRaw('LOWER(nama_poli) LIKE ?', ["%{$search}%"]);
                        });
                });
            })
            ->addColumn('action', function ($jd) {
                return '
                <button class="btn-edit-jadwal text-blue-600 hover:text-blue-800 mr-2" data-id="' . $jd->id . '" title="Edit">
                    <i class="fa-regular fa-pen-to-square text-lg"></i>
                </button>
                <button class="btn-delete-jadwal text-red-600 hover:text-red-800" data-id="' . $jd->id . '" title="Hapus">
                    <i class="fa-regular fa-trash-can text-lg"></i>
                </button>';
            })
            ->rawColumns(['nama_poli', 'action'])
            ->make(true);
    }

    public function searchDataPoliByIdDokter(Request $request, $dokterId)
    {
        $q = trim((string) $request->query('q', ''));

        // Ambil poli yang dimiliki dokter dari tabel pivot dokter_poli
        $poli = DB::table('dokter_poli')
            ->join('poli', 'poli.id', '=', 'dokter_poli.poli_id')
            ->where('dokter_poli.dokter_id', $dokterId)
            ->when($q !== '', function ($query) use ($q) {
                $query->where('poli.nama_poli', 'like', "%{$q}%");
            })
            ->select('poli.id', 'poli.nama_poli')
            ->distinct()
            ->orderBy('poli.nama_poli')
            ->get();

        // Selalu 200 + array (biar frontendmu gampang handle)
        return response()->json($poli, 200);
    }
}
