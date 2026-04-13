<?php

namespace App\Http\Controllers\Perawat;

use App\Http\Controllers\Controller;
use App\Models\EMR;
use App\Models\Kunjungan;
use App\Models\Perawat;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class PerawatController extends Controller
{
    private const STATUS_ANTRIAN = ['Pending'];
    private const STATUS_DALAM_KONSULTASI = 'Engaged';

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

    private function emptyChartPayload(string $filter = 'bulanan'): array
    {
        return [
            'filter' => $filter,
            'filter_label' => $this->getChartFilterLabel($filter),
            'labels' => [],
            'range_text' => '-',
            'handled_total' => [],
            'summary_handled_total' => 0,
            'rows' => [],
        ];
    }

    public function index(Request $request)
    {
        Carbon::setLocale('id');

        $today = now()->toDateString();
        $chartFilter = $this->normalizeChartFilter($request->get('filter'));
        $isSuperAdmin = Auth::user()?->role === 'Super Admin';

        $perawat = null;

        if (! $isSuperAdmin) {
            $perawat = Perawat::with(['dokterPoli.dokter', 'dokterPoli.poli'])
                ->where('user_id', Auth::id())
                ->firstOrFail();
        }

        $namaPerawatById = Perawat::pluck('nama_perawat', 'id')->toArray();

        // =========================================================
        // QUERY DASAR 1:
        // Semua kunjungan yang muncul di dashboard
        // - Super Admin => semua kunjungan
        // - Perawat biasa => sesuai area tugas
        // =========================================================
        $kunjunganAreaTugas = Kunjungan::query()
            ->with(['pasien', 'dokter', 'poli', 'emr'])
            ->when(
                ! $isSuperAdmin,
                fn($query) => $query->untukPerawat($perawat)
            );

        // =========================================================
        // QUERY DASAR 2:
        // Kunjungan area tugas perawat HARI INI
        // =========================================================
        $kunjunganHariIni = (clone $kunjunganAreaTugas)
            ->hariIni($today);

        // =========================================================
        // QUERY DASAR 3:
        // Semua EMR yang benar-benar ditangani
        // - Super Admin => semua EMR
        // - Perawat biasa => EMR milik perawat ini
        // =========================================================
        $emrSaya = EMR::query()
            ->with(['pasien', 'dokter', 'poli', 'kunjungan'])
            ->when(
                ! $isSuperAdmin,
                fn($query) => $query->filterByPerawat($perawat->id)
            );

        // =========================================================
        // DETAIL RINGKASAN ANTRIAN
        // Nama perawat diambil dari EMR
        // Kalau belum ada => Belum Dilayani
        // =========================================================
        $detailRingkasanAntrian = (clone $kunjunganAreaTugas)
            ->whereIn('status', self::STATUS_ANTRIAN)
            ->orderByDesc('tanggal_kunjungan')
            ->orderBy('no_antrian')
            ->get()
            ->map(function ($item) use ($namaPerawatById) {
                $jadwal = '-';
                $tanggalKunjunganText = '-';
                $namaPerawat = 'Belum Dilayani';

                if ($item->jadwalDokter) {
                    $bagian = array_filter([
                        $item->jadwalDokter->hari ?? null,
                        $item->jadwalDokter->jam_mulai ?? null,
                        $item->jadwalDokter->jam_selesai ?? null
                            ? ' - ' . $item->jadwalDokter->jam_selesai
                            : null,
                    ]);

                    if (! empty($bagian)) {
                        $jadwal = implode(' ', $bagian);
                    }
                }

                if ($item->tanggal_kunjungan) {
                    $tanggalKunjunganText = \Carbon\Carbon::parse($item->tanggal_kunjungan)
                        ->translatedFormat('l, d M Y');
                }

                if (! empty(optional($item->emr)->perawat_id)) {
                    $namaPerawat = $namaPerawatById[$item->emr->perawat_id] ?? 'Belum Dilayani';
                }

                return (object) [
                    'tanggal_kunjungan_text' => $tanggalKunjunganText,
                    'no_antrian' => $item->no_antrian ?? '-',
                    'nama_pasien' => $item->pasien->nama_pasien ?? '-',
                    'nama_poli' => $item->poli->nama_poli ?? '-',
                    'nama_dokter' => $item->dokter->nama_dokter ?? '-',
                    'nama_perawat' => $namaPerawat,
                    'jadwal' => $jadwal,
                    'status' => $item->status ?? '-',
                    'keluhan' => $item->keluhan_awal ?? '-',
                ];
            });

        // =========================================================
        // KPI DASHBOARD
        // =========================================================
        $statMenungguTindakan = (clone $kunjunganAreaTugas)
            ->whereIn('status', self::STATUS_ANTRIAN)
            ->count();

        $statSedangKonsultasi = (clone $kunjunganAreaTugas)
            ->where('status', self::STATUS_DALAM_KONSULTASI)
            ->count();

        $statSudahDitangani = (clone $emrSaya)
            ->whereNotNull('pasien_id')
            ->distinct('pasien_id')
            ->count('pasien_id');

        $statPasienHariIni = (clone $kunjunganHariIni)
            ->whereNotNull('pasien_id')
            ->distinct('pasien_id')
            ->count('pasien_id');

        // =========================================================
        // LIST UNTUK TABEL: RINGKASAN ANTRIAN
        // =========================================================
        $listSiapTriage = (clone $kunjunganAreaTugas)
            ->whereIn('status', self::STATUS_ANTRIAN)
            ->orderByDesc('tanggal_kunjungan')
            ->orderBy('no_antrian')
            ->limit(5)
            ->get()
            ->map(function ($item) use ($namaPerawatById) {
                $tanggalKunjunganText = '-';
                $namaPerawat = 'Belum Dilayani';

                if ($item->tanggal_kunjungan) {
                    $tanggalKunjunganText = \Carbon\Carbon::parse($item->tanggal_kunjungan)
                        ->translatedFormat('l, d M Y');
                }

                if (! empty(optional($item->emr)->perawat_id)) {
                    $namaPerawat = $namaPerawatById[$item->emr->perawat_id] ?? 'Belum Dilayani';
                }

                return (object) [
                    'tanggal_kunjungan_text' => $tanggalKunjunganText,
                    'no_antrian' => $item->no_antrian ?? '-',
                    'nama_pasien' => $item->pasien->nama_pasien ?? '-',
                    'nama_poli' => $item->poli->nama_poli ?? '-',
                    'nama_dokter' => $item->dokter->nama_dokter ?? '-',
                    'nama_perawat' => $namaPerawat,
                ];
            });

        // =========================================================
        // LIST UNTUK TABEL: PASIEN SELESAI DILAYANI
        // Nama perawat HARUS dari EMR
        // Tanggal ambil dari tanggal kunjungan, fallback ke created_at EMR
        // =========================================================
        $listPasienTerbaruDitangani = (clone $emrSaya)
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) use ($namaPerawatById) {
                $namaPerawat = 'Belum Dilayani';
                $tanggalKunjunganText = '-';

                if (! empty($item->perawat_id)) {
                    $namaPerawat = $namaPerawatById[$item->perawat_id] ?? 'Belum Dilayani';
                }

                if (! empty(optional($item->kunjungan)->tanggal_kunjungan)) {
                    $tanggalKunjunganText = \Carbon\Carbon::parse($item->kunjungan->tanggal_kunjungan)
                        ->translatedFormat('l, d M Y');
                } elseif (! empty($item->created_at)) {
                    $tanggalKunjunganText = \Carbon\Carbon::parse($item->created_at)
                        ->translatedFormat('l, d M Y');
                }

                return (object) [
                    'tanggal_kunjungan_text' => $tanggalKunjunganText,
                    'nama_pasien' => $item->pasien->nama_pasien ?? '-',
                    'nama_poli' => $item->poli->nama_poli ?? '-',
                    'nama_dokter' => $item->dokter->nama_dokter ?? '-',
                    'nama_perawat' => $namaPerawat,
                ];
            });

        $chartData = $this->buildHandledPerawatChart($perawat, $chartFilter, $isSuperAdmin);

        return view('perawat.dashboard', [
            'namaPerawat' => $isSuperAdmin
                ? (Auth::user()->username ?? 'Super Admin')
                : ($perawat->nama_perawat ?? 'Perawat'),
            'serverStatus' => 'Aktif',
            'isSuperAdmin' => $isSuperAdmin,

            'statMenungguTindakan' => $statMenungguTindakan,
            'statSedangKonsultasi' => $statSedangKonsultasi,
            'statSudahDitangani' => $statSudahDitangani,
            'statPasienHariIni' => $statPasienHariIni,

            'chartFilter' => $chartFilter,
            'chartData' => $chartData,

            'listSiapTriage' => $listSiapTriage,
            'listPasienTerbaruDitangani' => $listPasienTerbaruDitangani,

            'detailRingkasanAntrian' => $detailRingkasanAntrian,
        ]);
    }

    public function chartDashboard(Request $request)
    {
        $isSuperAdmin = Auth::user()?->role === 'Super Admin';
        $perawat = null;

        if (! $isSuperAdmin) {
            $perawat = Perawat::where('user_id', Auth::id())->first();

            if (! $perawat) {
                return response()->json($this->emptyChartPayload());
            }
        }

        $filter = $this->normalizeChartFilter($request->get('filter'));

        return response()->json(
            $this->buildHandledPerawatChart($perawat, $filter, $isSuperAdmin)
        );
    }

    private function buildHandledPerawatChart(?Perawat $perawat, string $filter, bool $isSuperAdmin = false): array
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

        $handledMap = array_fill_keys($keys, []);

        $emrRows = EMR::query()
            ->when(
                ! $isSuperAdmin,
                fn($query) => $query->filterByPerawat($perawat->id)
            )
            ->whereDate('created_at', '>=', $start->toDateString())
            ->whereDate('created_at', '<=', $end->toDateString())
            ->select('pasien_id', 'created_at')
            ->get();

        foreach ($emrRows as $row) {
            $tanggal = Carbon::parse($row->created_at);

            if ($bucketType === 'day') {
                $key = $tanggal->format('Y-m-d');
            } elseif ($bucketType === 'week') {
                $key = $tanggal->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
            } elseif ($bucketType === 'month') {
                $key = $tanggal->format('Y-m');
            } else {
                $key = $tanggal->format('Y');
            }

            if (! array_key_exists($key, $handledMap)) {
                continue;
            }

            if (! empty($row->pasien_id)) {
                $handledMap[$key][$row->pasien_id] = true;
            }
        }

        $handledCountMap = [];
        foreach ($handledMap as $key => $patientSet) {
            $handledCountMap[$key] = count($patientSet);
        }

        $rows = [];
        foreach ($keys as $index => $key) {
            $rows[] = [
                'label' => $labels[$index],
                'handled_total' => $handledCountMap[$key] ?? 0,
            ];
        }

        return [
            'filter' => $filter,
            'filter_label' => $this->getChartFilterLabel($filter),
            'labels' => $labels,
            'range_text' => $start->translatedFormat('d M Y') . ' - ' . $end->translatedFormat('d M Y'),
            'handled_total' => array_values($handledCountMap),
            'summary_handled_total' => array_sum($handledCountMap),
            'rows' => $rows,
        ];
    }
}
