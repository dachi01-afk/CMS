<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\Obat;
use App\Models\PenjualanObat;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FarmasiController extends Controller
{
    public function index()
    {
        return view('farmasi.dashboard');
    }

    public function chartPenjualanObat(Request $request)
    {
        $tz   = 'Asia/Jakarta';
        $mode = $request->query('range', 'harian'); // harian | mingguan | bulanan | tahunan
        $now  = Carbon::now($tz);

        $label    = [];
        $seriesA  = []; // jumlah transaksi
        $seriesB  = []; // total pemasukan (Rp)
        $mapTrans = [];
        $mapTotal = [];

        if ($mode === 'harian') {
            $hari = $now->toDateString();

            $rows = PenjualanObat::query()
                ->selectRaw('HOUR(tanggal_transaksi) as idx')
                ->selectRaw('COUNT(*) as jumlah_transaksi')
                ->selectRaw('COALESCE(SUM(sub_total),0) as total')
                ->whereDate('tanggal_transaksi', $hari)
                ->groupBy('idx')
                ->orderBy('idx')
                ->get();

            foreach ($rows as $r) {
                $mapTrans[(int)$r->idx] = (int)$r->jumlah_transaksi;
                $mapTotal[(int)$r->idx] = (float)$r->total;
            }
            for ($h = 0; $h <= 23; $h++) {
                $label[]   = sprintf('%02d:00', $h);
                $seriesA[] = $mapTrans[$h] ?? 0;
                $seriesB[] = $mapTotal[$h] ?? 0.0;
            }

            $meta = [
                'range'    => 'harian',
                'tanggal'  => $hari,
                'timezone' => $tz,
                'x_title'  => 'Jam',
            ];
        } elseif ($mode === 'mingguan') {
            $start = $now->copy()->startOfWeek(Carbon::MONDAY);
            $end   = $now->copy()->endOfWeek(Carbon::SUNDAY);

            $rows = PenjualanObat::query()
                ->selectRaw('WEEKDAY(tanggal_transaksi) as idx') // 0=Mon..6=Sun
                ->selectRaw('COUNT(*) as jumlah_transaksi')
                ->selectRaw('COALESCE(SUM(sub_total),0) as total')
                ->whereBetween(DB::raw('DATE(tanggal_transaksi)'), [$start->toDateString(), $end->toDateString()])
                ->groupBy('idx')
                ->orderBy('idx')
                ->get();

            foreach ($rows as $r) {
                $mapTrans[(int)$r->idx] = (int)$r->jumlah_transaksi;
                $mapTotal[(int)$r->idx] = (float)$r->total;
            }

            $hariIndo = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
            for ($d = 0; $d <= 6; $d++) {
                $label[]   = $hariIndo[$d];
                $seriesA[] = $mapTrans[$d] ?? 0;
                $seriesB[] = $mapTotal[$d] ?? 0.0;
            }

            $meta = [
                'range'    => 'mingguan',
                'start'    => $start->toDateString(),
                'end'      => $end->toDateString(),
                'timezone' => $tz,
                'x_title'  => 'Hari (Minggu ini)',
            ];
        } elseif ($mode === 'bulanan') {
            $start = $now->copy()->startOfMonth();
            $end   = $now->copy()->endOfMonth();
            $days  = $now->daysInMonth;

            $rows = PenjualanObat::query()
                ->selectRaw('DAY(tanggal_transaksi) as idx') // 1..31
                ->selectRaw('COUNT(*) as jumlah_transaksi')
                ->selectRaw('COALESCE(SUM(sub_total),0) as total')
                ->whereBetween(DB::raw('DATE(tanggal_transaksi)'), [$start->toDateString(), $end->toDateString()])
                ->groupBy('idx')
                ->orderBy('idx')
                ->get();

            foreach ($rows as $r) {
                $mapTrans[(int)$r->idx] = (int)$r->jumlah_transaksi;
                $mapTotal[(int)$r->idx] = (float)$r->total;
            }

            for ($d = 1; $d <= $days; $d++) {
                $label[]   = sprintf('%02d', $d);
                $seriesA[] = $mapTrans[$d] ?? 0;
                $seriesB[] = $mapTotal[$d] ?? 0.0;
            }

            $meta = [
                'range'    => 'bulanan',
                'bulan'    => $now->format('Y-m'),
                'timezone' => $tz,
                'x_title'  => 'Tanggal (Bulan ini)',
            ];
        } else { // tahunan
            $start = $now->copy()->startOfYear();
            $end   = $now->copy()->endOfYear();

            $rows = PenjualanObat::query()
                ->selectRaw('MONTH(tanggal_transaksi) as idx') // 1..12
                ->selectRaw('COUNT(*) as jumlah_transaksi')
                ->selectRaw('COALESCE(SUM(sub_total),0) as total')
                ->whereBetween(DB::raw('DATE(tanggal_transaksi)'), [$start->toDateString(), $end->toDateString()])
                ->groupBy('idx')
                ->orderBy('idx')
                ->get();

            foreach ($rows as $r) {
                $mapTrans[(int)$r->idx] = (int)$r->jumlah_transaksi;
                $mapTotal[(int)$r->idx] = (float)$r->total;
            }

            $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            for ($m = 1; $m <= 12; $m++) {
                $label[]   = $bulan[$m - 1];
                $seriesA[] = $mapTrans[$m] ?? 0;
                $seriesB[] = $mapTotal[$m] ?? 0.0;
            }

            $meta = [
                'range'    => 'tahunan',
                'tahun'    => $now->year,
                'timezone' => $tz,
                'x_title'  => 'Bulan (Tahun ini)',
            ];
        }

        return response()->json([
            'label'   => $label,
            'dataset' => [
                [
                    'label'           => 'Jumlah Transaksi',
                    'type'            => 'bar',
                    'data'            => $seriesA,
                    'borderWidth'     => 1,
                    'borderRadius'    => 6,
                    'backgroundColor' => 'rgba(37,99,235,0.7)',
                    'borderColor'     => 'rgba(37,99,235,1)',
                ],
                [
                    'label'       => 'Total Pemasukan (Rp)',
                    'type'        => 'line',
                    'yAxisID'     => 'y1',
                    'data'        => $seriesB,
                    'borderWidth' => 2,
                    'tension'     => 0.3,
                    'pointRadius' => 3,
                    'borderColor' => 'rgba(16,185,129,1)',
                ],
            ],
            'meta' => $meta,
        ]);
    }

    public function getJumlahPenjualanObatHariIni(Request $request)
    {
        $tz     = 'Asia/Jakarta';
        $today  = Carbon::now($tz)->toDateString();

        // Secara default hanya hitung transaksi yang Sudah Bayar.
        // Bisa override dengan query ?paid=0 kalau ingin termasuk "Belum Bayar".
        $onlyPaid = filter_var($request->query('paid', '1'), FILTER_VALIDATE_BOOLEAN);

        $q = PenjualanObat::query()
            ->whereDate('tanggal_transaksi', $today);

        if ($onlyPaid) {
            $q->where('status', 'Sudah Bayar');
        }

        // Hitung jumlah transaksi unik berdasarkan kode_transaksi
        $totalTransaksi = $q->distinct('kode_transaksi')->count('kode_transaksi');

        return response()->json([
            'total' => (int) $totalTransaksi,
            'meta'  => [
                'tanggal'  => $today,
                'timezone' => $tz,
                'onlyPaid' => $onlyPaid,
            ],
        ]);
    }

    public function getJumlahKeseluruhanTransaksiObat()
    {
        // Hitung semua transaksi unik berdasarkan kode_transaksi
        $totalTransaksi = PenjualanObat::distinct('kode_transaksi')->count('kode_transaksi');

        return response()->json([
            'total' => (int) $totalTransaksi,
        ]);
    }

    public function getTotalObat()
    {
        // Hitung total stok obat dari kolom 'jumlah'
        $jumlahObat = Obat::sum('jumlah');

        // Kembalikan hasil dalam format JSON
        return response()->json([
            'total' => $jumlahObat,
        ]);
    }
}
