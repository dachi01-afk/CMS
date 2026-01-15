<?php

namespace App\Http\Controllers\Farmasi;

use App\Models\Depot;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class DepotController extends Controller
{
    /**
     * Digunakan untuk search Brand di TomSelect.
     */
    public function getDataDepot(Request $request)
    {
        $search = $request->q; // TomSelect kirim "q" sebagai keyword search

        $query = Depot::query();

        if ($search) {
            $query->where('nama_depot', 'like', '%' . $search . '%');
        }

        // batasin biar ringan
        $data = $query->orderBy('nama_depot')->limit(20)->get();

        return response()->json($data);
    }

    /**
     * Digunakan saat user klik "Add xxxx..." di TomSelect.
     */
    public function createDataDepot(Request $request)
    {
        $validated = $request->validate([
            'nama_depot' => ['required', 'string', 'max:100', 'unique:depot,nama_depot'],
        ]);

        $jenis = Depot::create([
            'nama_depot' => $validated['nama_depot'],
        ]);

        // TomSelect butuh object: { id: xxx, nama: "..." }
        return response()->json($jenis, 201);
    }

    public function deleteDataDepot(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer', 'exists:depot,id'],
        ]);

        $jenis = Depot::findOrFail($request->id);

        // Kalau sudah dipakai di obat, jangan dihapus
        if ($jenis->obat()->exists()) {
            return response()->json([
                'message' => 'Jenis Obat sudah dipakai di data obat, tidak dapat dihapus.',
            ], 422);
        }

        $jenis->delete();

        return response()->json([
            'message' => 'Jenis Obat berhasil dihapus.',
        ]);
    }

    public function index()
    {
        return view('farmasi.depot.depot');
    }

    public function dataTables(Request $request)
    {
        $q = Depot::query()
            ->from('depot')
            ->leftJoin('tipe_depot', 'tipe_depot.id', '=', 'depot.tipe_depot_id')
            ->select([
                'depot.id',
                'depot.nama_depot',
                'depot.jumlah_stok_depot',
                'depot.updated_at',
                'tipe_depot.nama_tipe_depot',
            ])
            // jumlah jenis obat di depot (baris di depot_obat)
            ->selectSub(function ($sq) {
                $sq->from('depot_obat')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('depot_obat.depot_id', 'depot.id');
            }, 'total_obat')
            // jumlah jenis BHP di depot (baris di depot_bhp)
            ->selectSub(function ($sq) {
                $sq->from('depot_bhp')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('depot_bhp.depot_id', 'depot.id');
            }, 'total_bhp')
            // total stok BHP (SUM stok)
            ->selectSub(function ($sq) {
                $sq->from('depot_bhp')
                    ->selectRaw('COALESCE(SUM(stok),0)')
                    ->whereColumn('depot_bhp.depot_id', 'depot.id');
            }, 'total_stok_bhp');

        return DataTables::of($q)
            ->addIndexColumn()

            // kolom depot (dibuat cantik + info tipe + badge ringkas)
            ->editColumn('nama_depot', function ($row) {
                $tipe = $row->nama_tipe_depot ?: 'Tanpa Tipe';

                $obat = (int) ($row->total_obat ?? 0);
                $bhp  = (int) ($row->total_bhp ?? 0);
                $stokDepot = (int) ($row->jumlah_stok_depot ?? 0);

                return '
                    <div class="flex items-start gap-3">
                        <div class="h-10 w-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center">
                            <span class="text-emerald-700 font-bold text-xs">DP</span>
                        </div>

                        <div class="min-w-0">
                            <div class="font-semibold text-slate-800 leading-tight">' . e($row->nama_depot) . '</div>

                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold
                                             bg-slate-100 text-slate-700 border border-slate-200">
                                    ' . e($tipe) . '
                                </span>

                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold
                                             bg-sky-50 text-sky-700 border border-sky-100">
                                    Obat: ' . $obat . '
                                </span>

                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold
                                             bg-teal-50 text-teal-700 border border-teal-100">
                                    BHP: ' . $bhp . '
                                </span>

                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold
                                             bg-emerald-50 text-emerald-700 border border-emerald-100">
                                    Stok Depot: ' . number_format($stokDepot, 0, ',', '.') . '
                                </span>
                            </div>
                        </div>
                    </div>
                ';
            })

            // kolom tipe depot (kalau kamu mau tampil kolom terpisah)
            ->editColumn('nama_tipe_depot', function ($row) {
                return $row->nama_tipe_depot ?: '-';
            })

            // kolom jumlah stok depot (angka rapih)
            ->editColumn('jumlah_stok_depot', function ($row) {
                return number_format((int) $row->jumlah_stok_depot, 0, ',', '.');
            })

            ->addColumn('aksi', function ($row) {
                $id = $row->id;

                $showObat   = url("/farmasi/depot/$id/obat");
                $opnameObat = url("/farmasi/depot/$id/stok-opname-obat");
                $opnameBhp  = url("/farmasi/depot/$id/stok-opname-bhp");

                return '
                    <div class="flex items-center justify-end gap-2 flex-wrap">
                        <a href="' . $showObat . '"
                           class="inline-flex items-center justify-center px-3 py-2 rounded-xl text-[11px] font-semibold
                                  bg-sky-600 text-white hover:bg-sky-700">
                            Show Obat
                        </a>

                        <a href="' . $opnameObat . '"
                           class="inline-flex items-center justify-center px-3 py-2 rounded-xl text-[11px] font-semibold
                                  bg-emerald-600 text-white hover:bg-emerald-700">
                            Stok Opname Obat
                        </a>

                        <a href="' . $opnameBhp . '"
                           class="inline-flex items-center justify-center px-3 py-2 rounded-xl text-[11px] font-semibold
                                  bg-teal-600 text-white hover:bg-teal-700">
                            Stok Opname BHP
                        </a>
                    </div>
                ';
            })

            ->rawColumns(['nama_depot', 'aksi'])
            ->make(true);
    }
}
