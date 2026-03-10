<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PasienHariIniController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $search = trim((string) $request->search);

        $baseQuery = $this->baseQuery($today, $search);

        // Statistik tetap total harian, bukan hasil filter search
        $totalPasienHariIni = $this->baseQuery($today)->count();

        $totalMenunggu = $this->baseQuery($today)
            ->whereIn('k.status', ['Pending', 'Waiting', 'Engaged', 'Payment'])
            ->count();

        $totalSelesai = $this->baseQuery($today)
            ->where('k.status', 'Succeed')
            ->count();

        $pasienHariIni = $baseQuery
            ->orderByDesc('k.id')
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'table_body' => view('super-admin.pasien-hari-ini.partials.table-body', compact('pasienHariIni'))->render(),
                'pagination' => view('super-admin.pasien-hari-ini.partials.pagination', compact('pasienHariIni'))->render(),
                'filtered_total' => $pasienHariIni->total(),
            ]);
        }

        return view('super-admin.pasien-hari-ini.index', compact(
            'pasienHariIni',
            'totalPasienHariIni',
            'totalMenunggu',
            'totalSelesai'
        ));
    }

    private function baseQuery($today, $search = '')
    {
        $query = DB::table('kunjungan as k')
            ->join('pasien as p', 'p.id', '=', 'k.pasien_id')
            ->select([
                'k.id as kunjungan_id',
                'k.no_antrian',
                'k.status',
                'k.tanggal_kunjungan as waktu_kunjungan',
                'p.id as pasien_id',
                'p.no_emr as no_rm',
                'p.nama_pasien as nama_pasien',
                'p.no_hp_pasien as no_hp',
            ])
            ->whereDate('k.tanggal_kunjungan', $today)
            ->whereIn('k.status', ['Pending', 'Waiting', 'Engaged', 'Payment', 'Succeed']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('p.nama_pasien', 'like', "%{$search}%")
                    ->orWhere('p.no_emr', 'like', "%{$search}%")
                    ->orWhere('p.no_hp_pasien', 'like', "%{$search}%")
                    ->orWhere('k.no_antrian', 'like', "%{$search}%");
            });
        }

        return $query;
    }
}
