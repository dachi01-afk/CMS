<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\PiutangObat;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PiutangObatController extends Controller
{
    public function index()
    {
        return view('kasir.piutang-obat.piutang-obat');
    }

    public function getDataPiutangObat(Request $request)
    {
        $dataPiutang = PiutangObat::query()
            ->with(['supplier', 'returnObat'])
            ->where('status_piutang', 'Belum Lunas')
            ->latest('tanggal_piutang');

        return DataTables::eloquent($dataPiutang)
            ->addIndexColumn()
            ->addColumn('nama_supplier', function ($row) {
                return $row->supplier?->nama_supplier ?? '-';
            })
            ->filterColumn('nama_supplier', function ($query, $keyword) {
                $query->whereHas('supplier', function ($q) use ($keyword) {
                    $q->where('nama_supplier', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('action', function ($row) {
                return '
                    <div class="flex items-center justify-center gap-2">
                        <button 
                            type="button"
                            class="button-detail-piutang-obat px-4 py-2 bg-blue-500 text-white rounded-lg text-sm hover:bg-blue-600"
                            data-no-referensi="' . e($row->no_referensi) . '">
                            Detail
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getDetailPiutangObat($noReferensi)
    {
        $detailPiutang = PiutangObat::query()
            ->with([
                'supplier',
                'metodePembayaran',

                'dibuatOleh.admin',
                'dibuatOleh.dokter',
                'dibuatOleh.pasien',
                'dibuatOleh.farmasi',
                'dibuatOleh.perawat',
                'dibuatOleh.kasir',

                'diupdateOleh.admin',
                'diupdateOleh.dokter',
                'diupdateOleh.pasien',
                'diupdateOleh.farmasi',
                'diupdateOleh.perawat',
                'diupdateOleh.kasir',

                'returnObat',
                'returnObat.supplier',
                'returnObat.depot',
                'returnObat.returnObatDetail.obat',
                'returnObat.returnObatDetail.batchObat',
            ])
            ->where('no_referensi', $noReferensi)
            ->first();

        if (!$detailPiutang) {
            return response()->json([
                'message' => 'Data piutang tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Detail data piutang berhasil diambil.',
            'data' => $detailPiutang,
        ], 200);
    }
}
