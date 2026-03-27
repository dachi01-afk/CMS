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

    private function getPerawatLogin(): ?Perawat
    {
        return Perawat::where('user_id', Auth::id())->first();
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

        $perawat = Perawat::with(['dokterPoli.dokter', 'dokterPoli.poli'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // =========================================================
        // QUERY DASAR 1:
        // Semua kunjungan yang MUNCUL ke halaman perawat
        // berdasarkan penugasan dokter + poli
        // =========================================================
        $kunjunganAreaTugas = Kunjungan::query()
            ->with(['pasien', 'dokter', 'poli', 'emr'])
            ->untukPerawat($perawat);

        // =========================================================
        // QUERY DASAR 2:
        // Kunjungan area tugas perawat HARI INI
        // =========================================================
        $kunjunganHariIni = (clone $kunjunganAreaTugas)
            ->hariIni($today);

        // =========================================================
        // QUERY DASAR 3:
        // Semua EMR yang benar-benar ditangani perawat ini
        // =========================================================
        $emrSaya = EMR::query()
            ->with(['pasien', 'dokter', 'poli', 'kunjungan'])
            ->filterByPerawat($perawat->id);

        // =========================================================
        // QUERY DASAR 4:
        // Detail Kunjungan 
        // =========================================================
        $detailRingkasanAntrian = (clone $kunjunganAreaTugas)
            ->whereIn('status', self::STATUS_ANTRIAN)
            ->orderByDesc('tanggal_kunjungan')
            ->orderBy('no_antrian')
            ->get()
            ->map(function ($item) {
                $jadwal = '-';

                if ($item->jadwalDokter) {
                    $bagian = array_filter([
                        $item->jadwalDokter->hari ?? null,
                        $item->jadwalDokter->jam_mulai ?? null,
                        $item->jadwalDokter->jam_selesai ?? null
                            ? ' - ' . $item->jadwalDokter->jam_selesai
                            : null,
                    ]);

                    if (!empty($bagian)) {
                        $jadwal = implode(' ', $bagian);
                    }
                }

                if ($jadwal === '-' && $item->tanggal_kunjungan) {
                    $jadwal = \Carbon\Carbon::parse($item->tanggal_kunjungan)->format('d/m/Y H:i');
                }

                return (object) [
                    'no_antrian' => $item->no_antrian ?? '-',
                    'nama_pasien' => $item->pasien->nama_pasien ?? '-',
                    'nama_poli' => $item->poli->nama_poli ?? '-',
                    'nama_dokter' => $item->dokter->nama_dokter ?? '-',
                    'jadwal' => $jadwal,
                    'status' => $item->status ?? '-',
                    'keluhan' => $item->keluhan_awal ?? '-',
                ];
            });

        // =========================================================
        // KPI DASHBOARD
        // =========================================================

        // 1. Ringkasan Antrian -> semua data, bukan hari ini
        $statMenungguTindakan = (clone $kunjunganAreaTugas)
            ->whereIn('status', self::STATUS_ANTRIAN)
            ->count();

        // 2. Pasien Dalam Konsultasi -> semua data, bukan hari ini
        $statSedangKonsultasi = (clone $kunjunganAreaTugas)
            ->where('status', self::STATUS_DALAM_KONSULTASI)
            ->count();

        // 3. Pasien Selesai Dilayani -> semua data EMR milik perawat ini
        $statSudahDitangani = (clone $emrSaya)
            ->whereNotNull('pasien_id')
            ->distinct('pasien_id')
            ->count('pasien_id');

        // 4. Pasien Hari Ini -> baru ini pakai filter hari ini
        $statPasienHariIni = (clone $kunjunganHariIni)
            ->whereNotNull('pasien_id')
            ->distinct('pasien_id')
            ->count('pasien_id');

        // =========================================================
        // LIST UNTUK TABEL
        // =========================================================

        // List antrian yang tampil ke perawat
        // tidak tergantung emr.perawat_id
        $listSiapTriage = (clone $kunjunganAreaTugas)
            ->whereIn('status', self::STATUS_ANTRIAN)
            ->orderByDesc('tanggal_kunjungan')
            ->orderBy('no_antrian')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return (object) [
                    'no_antrian' => $item->no_antrian ?? '-',
                    'nama_pasien' => $item->pasien->nama_pasien ?? '-',
                    'nama_poli' => $item->poli->nama_poli ?? '-',
                    'nama_dokter' => $item->dokter->nama_dokter ?? '-',
                ];
            });

        // List pasien terbaru yang benar-benar ditangani perawat
        $listPasienTerbaruDitangani = (clone $emrSaya)
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return (object) [
                    'created_at' => $item->created_at,
                    'nama_pasien' => $item->pasien->nama_pasien ?? '-',
                    'nama_poli' => $item->poli->nama_poli ?? '-',
                    'nama_dokter' => $item->dokter->nama_dokter ?? '-',
                ];
            });

        $chartData = $this->buildHandledPerawatChart($perawat, $chartFilter);

        return view('perawat.dashboard', [
            'namaPerawat' => $perawat->nama_perawat ?? 'Perawat',
            'serverStatus' => 'Aktif',

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
        $perawat = $this->getPerawatLogin();

        if (!$perawat) {
            return response()->json($this->emptyChartPayload());
        }

        $filter = $this->normalizeChartFilter($request->get('filter'));

        return response()->json(
            $this->buildHandledPerawatChart($perawat, $filter)
        );
    }

    private function buildHandledPerawatChart(Perawat $perawat, string $filter): array
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
            ->filterByPerawat($perawat->id)
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

            if (!array_key_exists($key, $handledMap)) {
                continue;
            }

            if (!empty($row->pasien_id)) {
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
