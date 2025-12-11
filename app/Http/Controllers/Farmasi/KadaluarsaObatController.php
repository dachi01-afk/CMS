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
        $today     = Carbon::today();
        $threshold = (int) $request->input('threshold', 7);
        $limit     = (int) $request->input('limit', 5);

        $nearDate = $today->copy()->addDays($threshold);

        $data = Obat::select('id', 'kode_obat', 'nama_obat', 'jumlah', 'tanggal_kadaluarsa_obat')
            ->whereNotNull('tanggal_kadaluarsa_obat')
            ->whereDate('tanggal_kadaluarsa_obat', '>=', $today)
            ->whereDate('tanggal_kadaluarsa_obat', '<=', $nearDate)
            ->orderBy('tanggal_kadaluarsa_obat', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($obat) use ($today) {
                $exp = Carbon::parse($obat->tanggal_kadaluarsa_obat);

                // 🚀 hitung sisa hari: positif = masih sisa, negatif = sudah lewat
                $obat->sisa_hari = $today->diffInDays($exp, false);

                return $obat;
            });

        return response()->json($data);
    }

    // ==========================
    // DATA UNTUK DATATABLES
    // ==========================
    public function getDataKadaluarsaObat(Request $request)
    {
        $today     = Carbon::today();
        $threshold = (int) $request->input('threshold', 60);
        $nearDate  = $today->copy()->addDays($threshold);

        $query = Obat::query()
            ->whereNotNull('tanggal_kadaluarsa_obat')
            ->whereDate('tanggal_kadaluarsa_obat', '>=', $today)
            ->whereDate('tanggal_kadaluarsa_obat', '<=', $nearDate);

        return datatables()->eloquent($query)
            ->addIndexColumn()

            // 🔹 kirim sisa_hari ke frontend: positif = masih sisa, negatif = lewat
            ->addColumn('sisa_hari', function ($row) use ($today) {
                $exp = Carbon::parse($row->tanggal_kadaluarsa_obat);
                return $today->diffInDays($exp, false);
            })

            // 🔹 status berdasarkan sisa_hari
            ->addColumn('status_kadaluarsa', function ($row) use ($today) {
                $exp  = Carbon::parse($row->tanggal_kadaluarsa_obat);
                $diff = $today->diffInDays($exp, false); // sama: positif = sisa, negatif = lewat

                if ($diff < 0) {
                    // sudah lewat
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] bg-red-50 text-red-600 border border-red-200">Expired</span>';
                } elseif ($diff <= 7) {
                    // masih sisa <= 7 hari
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] bg-amber-50 text-amber-700 border border-amber-200">Warning</span>';
                }

                // masih aman
                return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200">Aman</span>';
            })
            ->rawColumns(['status_kadaluarsa'])
            ->toJson();
    }
}
