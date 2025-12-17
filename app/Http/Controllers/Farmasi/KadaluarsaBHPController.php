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
        $today     = Carbon::today()->startOfDay();
        $threshold = (int) $request->input('threshold', 7);  // warning window ke depan
        $limit     = (int) $request->input('limit', 5);

        $nearDate = $today->copy()->addDays($threshold)->endOfDay();

        /**
         * ✅ Ambil:
         * - sudah lewat (expired)  : tanggal < today
         * - hari ini              : tanggal = today
         * - kurang dari threshold : today < tanggal <= nearDate
         *
         * Jadi range query: tanggal <= nearDate (include expired juga)
         */
        $dataBhp = BahanHabisPakai::select('id', 'kode', 'nama_barang', 'stok_barang', 'tanggal_kadaluarsa_bhp')
            ->whereNotNull('tanggal_kadaluarsa_bhp')
            ->whereDate('tanggal_kadaluarsa_bhp', '<=', $nearDate)
            ->orderBy('tanggal_kadaluarsa_bhp', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($bhp) use ($today, $threshold) {
                $exp = Carbon::parse($bhp->tanggal_kadaluarsa_bhp)->startOfDay();
                $diff = $today->diffInDays($exp, false); // negatif = lewat

                $bhp->sisa_hari = $diff;

                // ✅ status khusus untuk FE
                if ($diff < 0) {
                    $bhp->status_key = 'expired'; // lewat
                } elseif ($diff === 0) {
                    $bhp->status_key = 'today';   // kadaluarsa hari ini
                } elseif ($diff <= $threshold) {
                    $bhp->status_key = 'warning'; // < 7 hari
                } else {
                    $bhp->status_key = 'aman';
                }

                return $bhp;
            });

        return response()->json($dataBhp);
    }

    public function getDataKadaluarsaBHP(Request $request)
    {
        $today     = Carbon::today()->startOfDay();
        $threshold = (int) $request->input('threshold', 60);
        $nearDate  = $today->copy()->addDays($threshold)->endOfDay();

        /**
         * ✅ Fokus tanggal kadaluarsa:
         * - hanya yang punya tanggal
         * - tampilkan yang exp <= nearDate
         *   (include expired + hari ini + yang akan datang sampai threshold)
         */
        $dataBhp = BahanHabisPakai::query()
            ->select('id', 'kode', 'nama_barang', 'stok_barang', 'tanggal_kadaluarsa_bhp', 'satuan_id')
            ->with(['satuanBHP:id,nama_satuan_obat'])
            ->whereNotNull('tanggal_kadaluarsa_bhp')
            ->whereDate('tanggal_kadaluarsa_bhp', '<=', $nearDate);

        return datatables()->eloquent($dataBhp)
            ->addIndexColumn()

            // satuan untuk FE (karena JS kamu pakai row.satuan)
            ->addColumn('satuan', function ($row) {
                return optional($row->satuanBHP)->nama_satuan_obat ?? '';
            })

            // sisa hari
            ->addColumn('sisa_hari', function ($row) use ($today) {
                $exp = Carbon::parse($row->tanggal_kadaluarsa_bhp)->startOfDay();
                return $today->diffInDays($exp, false);
            })

            // status badge (kalau FE mau pakai dari BE langsung)
            ->addColumn('status_kadaluarsa', function ($row) use ($today) {
                $exp  = Carbon::parse($row->tanggal_kadaluarsa_bhp)->startOfDay();
                $diff = $today->diffInDays($exp, false);

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
