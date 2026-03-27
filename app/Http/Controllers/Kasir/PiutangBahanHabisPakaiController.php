<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\PiutangBahanHabisPakai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PiutangBahanHabisPakaiController extends Controller
{
    public function index()
    {
        return view('kasir.piutang-bahan-habis-pakai.piutang-bahan-habis-pakai');
    }

    public function getDataPiutangBahanHabisPakai(Request $request)
    {
        if ($request->ajax()) {
            $query = DB::table('piutang_bahan_habis_pakai as pbhp')
                ->leftJoin('supplier as s', 'pbhp.supplier_id', '=', 's.id')
                ->select([
                    'pbhp.id',
                    'pbhp.no_referensi',
                    'pbhp.tanggal_piutang',
                    'pbhp.tanggal_jatuh_tempo',
                    'pbhp.total_piutang',
                    'pbhp.status_piutang',
                    's.nama_supplier',
                ])
                ->orderByDesc('pbhp.id');

            return DataTables::of($query)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    $search = $request->input('search.value');

                    if ($search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('s.nama_supplier', 'like', "%{$search}%")
                                ->orWhere('pbhp.no_referensi', 'like', "%{$search}%")
                                ->orWhere('pbhp.tanggal_piutang', 'like', "%{$search}%")
                                ->orWhere('pbhp.tanggal_jatuh_tempo', 'like', "%{$search}%")
                                ->orWhere('pbhp.status_piutang', 'like', "%{$search}%");
                        });
                    }
                })
                ->editColumn('nama_supplier', function ($row) {
                    return $row->nama_supplier ?? '-';
                })
                ->addColumn('no_faktur', function ($row) {
                    return $row->no_referensi ?? '-';
                })
                ->editColumn('status_piutang', function ($row) {
                    if ($row->status_piutang === 'Sudah Lunas') {
                        return '<span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">Sudah Lunas</span>';
                    }

                    return '<span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-1 text-xs font-medium text-rose-700">Belum Lunas</span>';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <div class="flex items-center justify-center gap-2">
                            <button type="button"
                                class="btn-detail-piutang-bhp inline-flex items-center rounded-lg bg-sky-100 px-3 py-2 text-xs font-medium text-sky-700 hover:bg-sky-200"
                                data-id="' . $row->id . '">
                                <i class="fa-solid fa-eye mr-1"></i> Detail
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['status_piutang', 'action'])
                ->make(true);
        }

        abort(404);
    }

    public function getDetailPiutangBahanHabisPakai($id)
    {
        $detailPiutang = PiutangBahanHabisPakai::with([
            'supplier',
            'metodePembayaran',

            'dibuatOleh.admin',
            'dibuatOleh.dokter',
            'dibuatOleh.pasien',
            'dibuatOleh.farmasi',
            'dibuatOleh.perawat',
            'dibuatOleh.kasir',
            'dibuatOleh.superAdmin',

            'diupdateOleh.admin',
            'diupdateOleh.dokter',
            'diupdateOleh.pasien',
            'diupdateOleh.farmasi',
            'diupdateOleh.perawat',
            'diupdateOleh.kasir',
            'diupdateOleh.superAdmin',

            'returnBahanHabisPakai',
            'returnBahanHabisPakai.supplier',
            'returnBahanHabisPakai.returnBahanHabisPakaiDetail.batchBahanHabisPakai',
        ])->find($id);

        if (!$detailPiutang) {
            return response()->json([
                'message' => 'Data piutang obat tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Detail data piutang obat berhasil diambil.',
            'data' => $detailPiutang,
        ], 200);
    }
}
