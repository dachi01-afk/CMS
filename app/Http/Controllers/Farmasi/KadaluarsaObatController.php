<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Obat;
use Carbon\Carbon;

class KadaluarsaObatController extends Controller
{
    public function index()
    {
        return view('farmasi.kadaluarsa-obat.kadaluarsa-obat');
    }

    // ==========================
    // DATA UNTUK CARD WARNING ATAS
    // ==========================
    public function getWarningKadaluarsa(Request $request)
    {
        $today     = Carbon::today()->startOfDay();
        $threshold = (int) $request->input('threshold', 90);
        $limit     = (int) $request->input('limit', 5);

        $nearDate = $today->copy()->addDays($threshold)->endOfDay();

        $data = Obat::select('id', 'kode_obat', 'nama_obat', 'jumlah', 'tanggal_kadaluarsa_obat')
            ->whereNotNull('tanggal_kadaluarsa_obat')
            ->whereDate('tanggal_kadaluarsa_obat', '<=', $nearDate)
            ->orderBy('tanggal_kadaluarsa_obat', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($obat) use ($today, $threshold) {
                $exp = Carbon::parse($obat->tanggal_kadaluarsa_obat)->startOfDay();
                $diff = $today->diffInDays($exp, false); // negatif = lewat

                $obat->sisa_hari = $diff;

                // ✅ status khusus untuk FE
                if ($diff < 0) {
                    $obat->status_key = 'expired';
                } elseif ($diff === 0) {
                    $obat->status_key = 'today';
                } elseif ($diff <= $threshold) {
                    $obat->status_key = 'warning';
                } else {
                    $obat->status_key = 'aman';
                }

                return $obat;
            });

        return response()->json($data);
    }

    // ==========================
    // DATA UNTUK DATATABLES
    // ==========================
    public function getDataKadaluarsaObat(Request $request)
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
        $query = Obat::query()
            ->select('id', 'kode_obat', 'nama_obat', 'jumlah', 'tanggal_kadaluarsa_obat', 'satuan_obat_id')
            ->with(['satuanObat:id,nama_satuan_obat'])
            ->whereNotNull('tanggal_kadaluarsa_obat')
            ->whereDate('tanggal_kadaluarsa_obat', '<=', $nearDate);

        return datatables()->eloquent($query)
            ->addIndexColumn()

            // satuan untuk FE (karena JS kamu pakai row.satuan)
            ->addColumn('satuan', function ($row) {
                return optional($row->satuanObat)->nama_satuan_obat ?? '';
            })

            // sisa hari
            ->addColumn('sisa_hari', function ($row) use ($today) {
                $exp = Carbon::parse($row->tanggal_kadaluarsa_obat)->startOfDay();
                return $today->diffInDays($exp, false);
            })

            // status badge (kalau FE mau pakai dari BE langsung)
            ->addColumn('status_kadaluarsa', function ($row) use ($today) {
                $exp  = Carbon::parse($row->tanggal_kadaluarsa_obat)->startOfDay();
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
