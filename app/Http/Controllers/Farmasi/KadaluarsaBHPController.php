<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\BahanHabisPakai;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class KadaluarsaBHPController extends Controller
{
    public function index()
    {
        return view('farmasi.kadaluarsa-bhp.kadaluarsa-bhp');
    }

    // ==========================
    // DATA UNTUK CARD WARNING ATAS
    // ==========================
    public function getWarningKadaluarsa(Request $request)
    {
        $threshold = (int) $request->input('threshold', 90);
        $limit     = (int) $request->input('limit', 5);

        // Langsung panggil scope dari Model
        $data = BahanHabisPakai::getWarningKadaluarsa($threshold, $limit);

        return response()->json($data);
    }

    // ==========================
    // DATA UNTUK DATATABLES
    // ==========================
    public function getDataKadaluarsaBHP(Request $request)
    {
        $threshold = (int) $request->input('threshold', 90);

        // Panggil scope dari model
        $query = BahanHabisPakai::getDataKadaluarsa($threshold);

        // dd($query);

        return datatables()->eloquent($query)
            ->addIndexColumn()

            // Ambil dari relasi yang sudah di-eager load di model
            ->addColumn('satuan', function ($row) {
                return optional($row->satuanObat)->nama_satuan_obat ?? '';
            })

            // Menggunakan Accessor 'sisa_hari' dari Model
            ->addColumn('tanggal_kadaluarsa_bhp', function ($row) {
                return $row->tgl_exp_terdekat;
            })

            // Status badge berdasarkan nilai sisa_hari di Model
            ->addColumn('status_kadaluarsa', function ($row) {
                $diff = $row->sisa_hari;

                if ($diff < 0) {
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] bg-red-50 text-red-600 border border-red-200">Expired</span>';
                } elseif ($diff === 0) {
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] bg-rose-50 text-rose-700 border border-rose-200">Hari ini</span>';
                } elseif ($diff <= 7) {
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] bg-amber-50 text-amber-700 border border-amber-200">Warning</span>';
                }

                return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200">Aman</span>';
            })
            ->rawColumns(['status_kadaluarsa'])
            ->toJson();
    }
}
