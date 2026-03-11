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
        $chartData = $this->buildKunjunganChart($chartFilter);

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

        return response()->json($this->buildKunjunganChart($filter));
    }

    public function reportKunjunganPdf(Request $request)
    {
        $this->ensureManager();

        Carbon::setLocale('id');

        $filter = $this->normalizeChartFilter($request->get('filter'));
        $chartData = $this->buildKunjunganChart($filter);
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
        $generatedAt = now();
        $now = now();

        switch ($filter) {
            case 'harian':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;

            case 'mingguan':
                $start = $now->copy()->subWeeks(11)->startOfWeek(Carbon::MONDAY);
                $end = $now->copy()->endOfWeek(Carbon::SUNDAY);
                break;

            case 'tahunan':
                $start = $now->copy()->subYears(4)->startOfYear();
                $end = $now->copy()->endOfYear();
                break;

            case 'bulanan':
            default:
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                break;
        }

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

    private function getChartFilterLabel(string $filter): string
    {
        return match ($filter) {
            'harian' => 'Harian',
            'mingguan' => 'Mingguan',
            'tahunan' => 'Tahunan',
            default => 'Bulanan',
        };
    }

    private function buildKunjunganChart(string $filter): array
    {
        Carbon::setLocale('id');
        $now = now();

        switch ($filter) {
            case 'harian':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                $bucketType = 'day';
                break;

            case 'mingguan':
                $start = $now->copy()->subWeeks(11)->startOfWeek(Carbon::MONDAY);
                $end = $now->copy()->endOfWeek(Carbon::SUNDAY);
                $bucketType = 'week';
                break;

            case 'tahunan':
                $start = $now->copy()->subYears(4)->startOfYear();
                $end = $now->copy()->endOfYear();
                $bucketType = 'year';
                break;

            case 'bulanan':
            default:
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                $bucketType = 'month';
                break;
        }

        $labels = [];
        $keys = [];
        $cursor = $start->copy();

        if ($bucketType === 'day') {
            while ($cursor->lte($end)) {
                $keys[] = $cursor->format('Y-m-d');
                $labels[] = $cursor->translatedFormat('d M');
                $cursor->addDay();
            }
        } elseif ($bucketType === 'week') {
            while ($cursor->lte($end)) {
                $weekStart = $cursor->copy()->startOfWeek(Carbon::MONDAY);
                $weekEnd = $cursor->copy()->endOfWeek(Carbon::SUNDAY);

                $keys[] = $weekStart->format('Y-m-d');
                $labels[] = $weekStart->translatedFormat('d M') . ' - ' . $weekEnd->translatedFormat('d M');

                $cursor->addWeek();
            }
        } elseif ($bucketType === 'month') {
            while ($cursor->lte($end)) {
                $keys[] = $cursor->format('Y-m');
                $labels[] = $cursor->translatedFormat('M Y');
                $cursor->addMonth();
            }
        } else {
            while ($cursor->lte($end)) {
                $keys[] = $cursor->format('Y');
                $labels[] = $cursor->format('Y');
                $cursor->addYear();
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

            if ($bucketType === 'day') {
                $key = $tanggal->format('Y-m-d');
            } elseif ($bucketType === 'week') {
                $key = $tanggal->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
            } elseif ($bucketType === 'month') {
                $key = $tanggal->format('Y-m');
            } else {
                $key = $tanggal->format('Y');
            }

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
            'range_text' => $start->translatedFormat('d M Y') . ' - ' . $end->translatedFormat('d M Y'),

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
