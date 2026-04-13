<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Exports\SuperAdmin\KunjunganReportExport;
use App\Http\Controllers\Controller;
use App\Models\Kunjungan;
use App\Models\OrderLayanan;
use App\Models\Pasien;
use App\Models\Pembayaran;
use App\Models\PenjualanObat;
use App\Models\SuperAdmin;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class SuperAdminController extends Controller
{
    private const ACTIVE_KUNJUNGAN_STATUSES = ['Pending', 'Waiting', 'Engaged', 'Payment'];

    private function ensureManager(): void
    {
        $user = auth()->user();

        $role = strtolower((string) ($user->role ?? $user->level ?? $user->jenis_role ?? ''));

        $isManager = in_array($role, ['super admin', 'super_admin', 'superadmin'], true)
            || (bool) ($user->is_super_admin ?? false);

        abort_unless($user && $isManager, 403, 'Hanya Super Admin yang dapat mengakses dashboard ini.');
    }

    private function getNamaSuperAdmin(): string
    {
        $login = Auth::id();
        $superAdmin = SuperAdmin::where('user_id', $login)->first();

        return $superAdmin->nama_super_admin ?? 'Super Admin';
    }

    public function dashboard()
    {
        $this->ensureManager();

        $namaSuperAdmin = $this->getNamaSuperAdmin();

        $serverStatus = 'Online';
        $hariIni = Carbon::today();

        $totalPasien = Pasien::count();
        $totalUser = User::count();

        $totalAdminOnline = User::whereIn('role', ['Admin', 'admin'])
            ->whereNotNull('terakhir_login')
            ->where('terakhir_login', '>=', now()->subMinutes(5))
            ->count();

        $pasienHariIni = Kunjungan::whereDate('tanggal_kunjungan', $hariIni)
            ->distinct()
            ->count('pasien_id');

        $antrianAktif = Kunjungan::whereDate('tanggal_kunjungan', $hariIni)
            ->whereIn('status', self::ACTIVE_KUNJUNGAN_STATUSES)
            ->count();

        $totalPembayaran = Pembayaran::count();
        $totalTransaksiLayanan = OrderLayanan::count();
        $totalTransaksiObat = PenjualanObat::count();
        $totalTransaksi = $totalPembayaran + $totalTransaksiObat + $totalTransaksiLayanan;

        $pembayaran = Pembayaran::where('status', 'Sudah Bayar')->sum('uang_yang_diterima');
        $layanan = OrderLayanan::where('status_order_layanan', 'Selesai')->sum('total_bayar');
        $obat = PenjualanObat::where('status', 'Sudah Bayar')->sum('uang_yang_diterima');

        $pendapatan = $pembayaran + $layanan + $obat;
        $pendapatanRupiah = 'Rp ' . number_format($pendapatan, 0, ',', '.');

        $chartFilter = 'bulanan';
        $chartData = $this->buildKunjunganChart($chartFilter, [
            'bulan' => now()->format('Y-m'),
        ]);

        return view('super-admin.dashboard-super-admin', compact(
            'namaSuperAdmin',
            'serverStatus',
            'totalUser',
            'totalAdminOnline',
            'totalPasien',
            'pasienHariIni',
            'antrianAktif',
            'totalTransaksi',
            'pendapatanRupiah',
            'chartFilter',
            'chartData'
        ));
    }

    public function chartKunjungan(Request $request): JsonResponse
    {
        $this->ensureManager();

        $filter = $this->normalizeChartFilter($request->get('filter'));
        $selected = $this->getChartSelectionsFromRequest($request);

        return response()->json($this->buildKunjunganChart($filter, $selected));
    }

    public function reportKunjunganPdf(Request $request)
    {
        $this->ensureManager();

        Carbon::setLocale('id');

        $filter = $this->normalizeChartFilter($request->get('filter'));
        $selected = $this->getChartSelectionsFromRequest($request);
        $chartData = $this->buildKunjunganChart($filter, $selected);
        $namaSuperAdmin = $this->getNamaSuperAdmin();
        $generatedAt = now();

        $fileName = 'laporan-kunjungan-' . $filter . '-' . $generatedAt->format('Ymd_His') . '.pdf';

        $pdf = Pdf::loadView('super-admin.report-kunjungan-pdf', [
            'namaSuperAdmin' => $namaSuperAdmin,
            'chartData' => $chartData,
            'generatedAt' => $generatedAt,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }

    public function reportKunjunganExcel(Request $request)
    {
        $this->ensureManager();

        Carbon::setLocale('id');

        $filter = $this->normalizeChartFilter($request->get('filter'));
        $selected = $this->getChartSelectionsFromRequest($request);
        $generatedAt = now();

        $period = $this->resolveKunjunganPeriod($filter, $selected);
        $start = $period['start'];
        $end = $period['end'];

        $rows = Kunjungan::query()
            ->leftJoin('dokter', 'kunjungan.dokter_id', '=', 'dokter.id')
            ->leftJoin('pasien', 'kunjungan.pasien_id', '=', 'pasien.id')
            ->leftJoin('poli', 'kunjungan.poli_id', '=', 'poli.id')
            ->whereDate('kunjungan.tanggal_kunjungan', '>=', $start->toDateString())
            ->whereDate('kunjungan.tanggal_kunjungan', '<=', $end->toDateString())
            ->orderBy('kunjungan.tanggal_kunjungan', 'desc')
            ->orderBy('kunjungan.no_antrian', 'asc')
            ->select([
                'kunjungan.no_antrian',
                'kunjungan.keluhan_awal',
                'kunjungan.status',
                'kunjungan.tanggal_kunjungan',
                'dokter.nama_dokter',
                'pasien.nama_pasien',
                'poli.nama_poli',
            ])
            ->get();

        $fileName = 'laporan-kunjungan-' . $filter . '-' . $generatedAt->format('Ymd_His') . '.xlsx';

        return Excel::download(new KunjunganReportExport($rows), $fileName);
    }

    private function normalizeChartFilter(?string $filter): string
    {
        $allowed = ['harian', 'mingguan', 'bulanan', 'tahunan'];

        return in_array($filter, $allowed, true) ? $filter : 'bulanan';
    }

    private function getChartSelectionsFromRequest(Request $request): array
    {
        return [
            'tanggal' => $request->get('tanggal'),
            'minggu' => $request->get('minggu'),
            'bulan' => $request->get('bulan'),
            'tahun' => $request->get('tahun'),
        ];
    }

    private function getChartFilterLabel(string $filter): string
    {
        return match ($filter) {
            'harian' => 'Harian',
            'mingguan' => 'Mingguan',
            'tahunan' => 'Tahunan',
            default => 'Bulanan',
        };
    }

    private function resolveKunjunganPeriod(string $filter, array $selected = []): array
    {
        Carbon::setLocale('id');

        $now = now();

        $normalized = [
            'tanggal' => $now->format('Y-m-d'),
            'minggu' => sprintf('%s-W%s', $now->format('o'), $now->format('W')),
            'bulan' => $now->format('Y-m'),
            'tahun' => (int) $now->format('Y'),
        ];

        if (!empty($selected['tanggal'])) {
            $normalized['tanggal'] = (string) $selected['tanggal'];
        }

        if (!empty($selected['minggu'])) {
            $normalized['minggu'] = (string) $selected['minggu'];
        }

        if (!empty($selected['bulan'])) {
            $normalized['bulan'] = (string) $selected['bulan'];
        }

        if (!empty($selected['tahun'])) {
            $normalized['tahun'] = (int) $selected['tahun'];
        }

        switch ($filter) {
            case 'harian':
                try {
                    $selectedDate = Carbon::createFromFormat('Y-m-d', $normalized['tanggal']);
                } catch (\Throwable $th) {
                    $selectedDate = $now->copy();
                }

                $start = $selectedDate->copy()->startOfDay();
                $end = $selectedDate->copy()->endOfDay();
                $bucketType = 'day';

                $normalized['tanggal'] = $selectedDate->format('Y-m-d');
                break;

            case 'mingguan':
                if (preg_match('/^(\d{4})-W(\d{2})$/', $normalized['minggu'], $matches)) {
                    $isoYear = (int) $matches[1];
                    $isoWeek = (int) $matches[2];
                } else {
                    $isoYear = (int) $now->format('o');
                    $isoWeek = (int) $now->format('W');
                }

                $start = $now->copy()->setISODate($isoYear, $isoWeek)->startOfWeek(Carbon::MONDAY);
                $end = $start->copy()->endOfWeek(Carbon::SUNDAY);
                $bucketType = 'day';

                $normalized['minggu'] = sprintf('%04d-W%02d', $isoYear, $isoWeek);
                break;

            case 'tahunan':
                $year = (int) $normalized['tahun'];

                if ($year < 2000 || $year > 3000) {
                    $year = (int) $now->format('Y');
                }

                $start = Carbon::create($year, 1, 1)->startOfYear();
                $end = $start->copy()->endOfYear();
                $bucketType = 'month';

                $normalized['tahun'] = $year;
                break;

            case 'bulanan':
            default:
                if (preg_match('/^(\d{4})-(\d{2})$/', $normalized['bulan'], $matches)) {
                    $year = (int) $matches[1];
                    $month = (int) $matches[2];
                } else {
                    $year = (int) $now->format('Y');
                    $month = (int) $now->format('m');
                }

                if ($month < 1 || $month > 12) {
                    $month = (int) $now->format('m');
                }

                $start = Carbon::create($year, $month, 1)->startOfMonth();
                $end = $start->copy()->endOfMonth();
                $bucketType = 'day';

                $normalized['bulan'] = sprintf('%04d-%02d', $year, $month);
                break;
        }

        $rangeText = $start->isSameDay($end)
            ? $start->translatedFormat('d M Y')
            : $start->translatedFormat('d M Y') . ' - ' . $end->translatedFormat('d M Y');

        return [
            'start' => $start,
            'end' => $end,
            'bucket_type' => $bucketType,
            'selected' => $normalized,
            'range_text' => $rangeText,
        ];
    }

    private function buildKunjunganChart(string $filter, array $selected = []): array
    {
        Carbon::setLocale('id');

        $period = $this->resolveKunjunganPeriod($filter, $selected);

        $start = $period['start'];
        $end = $period['end'];
        $bucketType = $period['bucket_type'];
        $selected = $period['selected'];
        $rangeText = $period['range_text'];

        $labels = [];
        $keys = [];

        if ($bucketType === 'day') {
            $cursor = $start->copy()->startOfDay();
            $last = $end->copy()->startOfDay();

            while ($cursor->lte($last)) {
                $keys[] = $cursor->format('Y-m-d');
                $labels[] = $cursor->translatedFormat('d M');
                $cursor->addDay();
            }
        } else {
            $cursor = $start->copy()->startOfMonth();
            $last = $end->copy()->startOfMonth();

            while ($cursor->lte($last)) {
                $keys[] = $cursor->format('Y-m');
                $labels[] = $cursor->translatedFormat('M Y');
                $cursor->addMonth();
            }
        }

        $totalMap = array_fill_keys($keys, 0);
        $activeMap = array_fill_keys($keys, 0);
        $successMap = array_fill_keys($keys, 0);
        $canceledMap = array_fill_keys($keys, 0);

        $kunjungans = Kunjungan::query()
            ->select('tanggal_kunjungan', 'status')
            ->whereDate('tanggal_kunjungan', '>=', $start->toDateString())
            ->whereDate('tanggal_kunjungan', '<=', $end->toDateString())
            ->get();

        foreach ($kunjungans as $kunjungan) {
            $tanggal = Carbon::parse($kunjungan->tanggal_kunjungan);

            $key = $bucketType === 'day'
                ? $tanggal->format('Y-m-d')
                : $tanggal->format('Y-m');

            if (!array_key_exists($key, $totalMap)) {
                continue;
            }

            $totalMap[$key]++;

            if (in_array($kunjungan->status, self::ACTIVE_KUNJUNGAN_STATUSES, true)) {
                $activeMap[$key]++;
            } elseif ($kunjungan->status === 'Succeed') {
                $successMap[$key]++;
            } elseif ($kunjungan->status === 'Canceled') {
                $canceledMap[$key]++;
            }
        }

        $rows = [];
        foreach ($keys as $index => $key) {
            $rows[] = [
                'label' => $labels[$index],
                'total' => $totalMap[$key] ?? 0,
                'aktif' => $activeMap[$key] ?? 0,
                'selesai' => $successMap[$key] ?? 0,
                'dibatalkan' => $canceledMap[$key] ?? 0,
            ];
        }

        return [
            'filter' => $filter,
            'filter_label' => $this->getChartFilterLabel($filter),
            'labels' => $labels,
            'range_text' => $rangeText,
            'selected' => $selected,

            'total_kunjungan' => array_values($totalMap),
            'kunjungan_aktif' => array_values($activeMap),
            'kunjungan_selesai' => array_values($successMap),
            'kunjungan_dibatalkan' => array_values($canceledMap),

            'summary_total' => array_sum($totalMap),
            'summary_aktif' => array_sum($activeMap),
            'summary_selesai' => array_sum($successMap),
            'summary_dibatalkan' => array_sum($canceledMap),

            'rows' => $rows,
        ];
    }
}
