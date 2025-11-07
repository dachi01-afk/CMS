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

    public function dataJadwalDokter()
    {
        // Ambil hanya yang diperlukan: dokter (nama), poli langsung dari kolom poli_id,
        // serta fallback via dokter_poli.poli kalau suatu saat poli_id null.
        $query = JadwalDokter::with([
            'dokter:id,nama_dokter',
            'poli:id,nama_poli',                 // from jadwal_dokter.poli_id
            'dokterPoli.poli:id,nama_poli',      // fallback via pivot
        ])->latest();

        return DataTables::eloquent($query)
            ->addIndexColumn()

            // Nama Dokter
            ->addColumn('nama_dokter', fn($jd) => $jd->dokter->nama_dokter ?? '-')

            // Nama Poli — hanya SATU (spesifik baris jadwal)
            ->addColumn('nama_poli', function ($jd) {
                $namaPoli =
                    ($jd->poli->nama_poli ?? null)                                       // direct FK
                    ?? optional(optional($jd->dokterPoli)->poli)->nama_poli               // via pivot
                    ?? '-';

                if ($namaPoli === '-') return $namaPoli;

                return '<span class="inline-block px-2 py-1 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-md">'
                    . e($namaPoli) .
                    '</span>';
            })

            // Hari
            ->addColumn('hari_formatted', fn($jd) => is_array($jd->hari) ? implode(', ', $jd->hari) : ($jd->hari ?? '-'))

            // Jam
            ->editColumn('jam_awal',    fn($jd) => $jd->jam_awal    ? substr($jd->jam_awal, 0, 5)    : '-')
            ->editColumn('jam_selesai', fn($jd) => $jd->jam_selesai ? substr($jd->jam_selesai, 0, 5) : '-')

            // Aksi
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
        $polis = DB::table('dokter_poli')
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
        return response()->json($polis, 200);
    }
}
