<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;

use App\Models\Poli;
use App\Models\Pasien;

use App\Models\DataObat;
use App\Models\Kunjungan;
use App\Models\Pembayaran;
use App\Models\TenagaMedis;
use Illuminate\Http\Request;
use App\Models\PembelianObat;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function getChartKunjungan(Request $request)
    {
        $periode = $request->input('periode', 'bulan');
        $tahun = $request->input('tahun', now()->year);
        $jenis = $request->input('jenis_kunjungan', null);
        $poli = $request->input('poli', null);


        $query = Kunjungan::query();

        if ($jenis) {
            $query->where('jenis_kunjungan', $jenis);
        }
        if ($poli) {
            $query->whereHas('poli', function ($q) use ($poli) {
                $q->where('nama_poli', $poli);
            });
        }
        $labels = [];
        $values = [];
        $chartQuery = clone $query;


        if ($periode == 'minggu') {
            $data = $chartQuery->whereYear('tanggal_kunjungan', $tahun)
                ->selectRaw('DAYOFWEEK(tanggal_kunjungan) as hari, COUNT(id_kunjungan) as total')
                ->groupBy('hari')
                ->pluck('total', 'hari');


            $mapHari = [2 => 'Senin', 3 => 'Selasa', 4 => 'Rabu', 5 => 'Kamis', 6 => 'Jumat', 7 => 'Sabtu', 1 => 'Minggu'];
            foreach ($mapHari as $num => $label) {
                $labels[] = $label;
                $values[] = $data[$num] ?? 0;
            }

            // Summary minggu ini vs minggu lalu, menggunakan $summaryQuery yang sudah difilter
            $total = (clone $query)->whereBetween('tanggal_kunjungan', [now()->startOfWeek(), now()->endOfWeek()])->count();
            $last = (clone $query)->whereBetween('tanggal_kunjungan', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])->count();
            $compareText = "dari Minggu lalu";
        } else {
            $data = $chartQuery->whereyear('tanggal_kunjungan', $tahun)
                ->selectRaw('MONTH(tanggal_kunjungan) as bulan, COUNT(id_kunjungan) as total')
                ->groupBy('bulan')
                ->pluck('total', 'bulan');

            $mapBulan = [
                1 => 'Jan',
                2 => 'Feb',
                3 => 'Mar',
                4 => 'Apr',
                5 => 'Mei',
                6 => 'Jun',
                7 => 'Jul',
                8 => 'Agu',
                9 => 'Sep',
                10 => 'Okt',
                11 => 'Nov',
                12 => 'Des'
            ];

            foreach ($mapBulan as $num => $label) {
                $labels[] = $label;
                $values[] = $data[$num] ?? 0;
            }


            // Summary bulan ini vs bulan lalu, menggunakan $summaryQuery yang sudah difilter
            $total = (clone $query)->whereYear('tanggal_kunjungan', $tahun)
                ->whereMonth('tanggal_kunjungan', now()->month)
                ->count();
            $last = (clone $query)->whereYear('tanggal_kunjungan', $tahun)
                ->whereMonth('tanggal_kunjungan', now()->subMonth()->month)
                ->count();
            $compareText = "dari Bulan lalu";
        }

        // Hitung persentase di akhir setelah nilai $total dan $last ditentukan
        if ($last > 0) {
            $percentage = round((($total - $last) / $last) * 100, 1);
        } else {
            $percentage = ($total > 0) ? 100 : 0;
        }

        return response()->json([
            'labels' => $labels,
            'data' => $values,
            'summary' => [
                'total' => $total,
                'percentage' => $percentage,
                'compare_text' => $compareText
            ]
        ]);
    }

    public function getDashboardMetrics()
    {
        // Tanggal untuk perbandingan
        $thisMonth = now();
        $lastMonth = now()->subMonth();
        $compareText = "dari bulan " . $thisMonth->translatedFormat('F');

        // =======================================================
        // 1. Rata-Rata Waktu Konsultasi (getAverageWaktuKonsultasi)
        // =======================================================
        $consultationBaseQuery = Kunjungan::query();

        $averageTotal = (clone $consultationBaseQuery)
            ->whereYear('tanggal_kunjungan', $thisMonth->year)
            ->whereMonth('tanggal_kunjungan', $thisMonth->month)
            ->avg('lama_durasi_menit');

        $averageLast = (clone $consultationBaseQuery)
            ->whereYear('tanggal_kunjungan', $lastMonth->year)
            ->whereMonth('tanggal_kunjungan', $lastMonth->month)
            ->avg('lama_durasi_menit');

        if ($averageLast > 0) {
            // Waktu tunggu/konsultasi: penurunan persentase adalah POSITIF
            $consultationPercentage = round((($averageTotal - $averageLast) / $averageLast) * 100, 1);
        } else {
            $consultationPercentage = ($averageTotal > 0) ? 100 : 0;
        }

        $minutes = floor($averageTotal);
        $seconds = round(($averageTotal - $minutes) * 60);
        $formattedConsultationTime = "{$minutes}m {$seconds}s";

        // =======================================================
        // 2. Pasien Baru (getNewPatients)
        // =======================================================
        $totalNewPatients = Pasien::whereYear('tanggal_pendaftaran', $thisMonth->year)
            ->whereMonth('tanggal_pendaftaran', $thisMonth->month)
            ->count();

        $lastMonthNewPatients = Pasien::whereYear('tanggal_pendaftaran', $lastMonth->year)
            ->whereMonth('tanggal_pendaftaran', $lastMonth->month)
            ->count();

        if ($lastMonthNewPatients > 0) {
            $newPatientPercentage = round((($totalNewPatients - $lastMonthNewPatients) / $lastMonthNewPatients) * 100, 1);
        } else {
            $newPatientPercentage = ($totalNewPatients > 0) ? 100 : 0;
        }

        // =======================================================
        // 3. Pasien Terdaftar (getRegisteredPatientsSummary) - Pasien Unik Kunjungan
        // =======================================================
        $registeredThisMonth = Kunjungan::whereYear('tanggal_kunjungan', $thisMonth->year)
            ->whereMonth('tanggal_kunjungan', $thisMonth->month)
            ->distinct('pasien_id')
            ->count('pasien_id');

        $registeredLastMonth = Kunjungan::whereYear('tanggal_kunjungan', $lastMonth->year)
            ->whereMonth('tanggal_kunjungan', $lastMonth->month)
            ->distinct('pasien_id')
            ->count('pasien_id');

        if ($registeredLastMonth > 0) {
            $registeredPercentage = round((($registeredThisMonth - $registeredLastMonth) / $registeredLastMonth) * 100, 1);
        } else {
            $registeredPercentage = ($registeredThisMonth > 0) ? 100 : 0;
        }

        // =======================================================
        // 4. Rata-Rata Waktu Tunggu Dokter (getAverageWaitTime)
        // =======================================================
        // Logika Anda untuk menghitung totalWaitTimeThisMonth dan totalWaitTimeLastMonth sudah benar, 
        // tetapi memanggil 'get' lalu 'sum' di dalam closure bisa tidak efisien. 
        // Saya akan asumsikan $totalWaitTimeThisMonth dan $totalWaitTimeLastMonth sudah dalam total menit.

        // *Karena kode ini cukup panjang dan diulang, saya sarankan membuat Trait/Helper untuk ini*
        $averageWaitTime = $this->calculateAverageWaitTime($thisMonth);
        $averageWaitTimeLast = $this->calculateAverageWaitTime($lastMonth);

        if ($averageWaitTimeLast > 0) {
            // Waktu tunggu/konsultasi: penurunan persentase adalah POSITIF
            $doctorWaitPercentage = round((($averageWaitTime - $averageWaitTimeLast) / $averageWaitTimeLast) * 100, 1);
        } else {
            $doctorWaitPercentage = ($averageWaitTime > 0) ? 100 : 0;
        }

        $minutes = floor($averageWaitTime);
        $seconds = round(($averageWaitTime - $minutes) * 60);
        $formattedDoctorWaitTime = "{$minutes}m {$seconds}s";


        // =======================================================
        // 5. Obat Habis (getObatHabisCount) - TIDAK ADA PERBANDINGAN BULANAN
        // =======================================================
        $totalObatHabis = DataObat::where('stok', 0)->count();
        $obatHabisPercentage = 0; // Tidak ada perbandingan, diset 0 atau '-'
        $obatHabisIsPositive = false; // Obat habis selalu negatif

        // =======================================================
        // 6. Rata-Rata Waktu Tunggu Apotek (getAverageApotekWaitTime)
        // =======================================================
        $apotekBaseQuery = Kunjungan::query()
            ->join('rekam_medis', 'kunjungan.id_kunjungan', '=', 'rekam_medis.kunjungan_id')
            ->join('pembayaran', 'kunjungan.id_kunjungan', '=', 'pembayaran.kunjungan_id');

        // Rata-rata detik bulan ini
        $averageApotekTotal = (clone $apotekBaseQuery)
            ->whereYear('kunjungan.tanggal_kunjungan', $thisMonth->year)
            ->whereMonth('kunjungan.tanggal_kunjungan', $thisMonth->month)
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, rekam_medis.waktu_resep_selesai, pembayaran.waktu_obat_diserahkan)) as average_seconds')
            ->value('average_seconds') ?? 0;

        // Rata-rata detik bulan lalu
        $averageApotekLast = (clone $apotekBaseQuery)
            ->whereYear('kunjungan.tanggal_kunjungan', $lastMonth->year)
            ->whereMonth('kunjungan.tanggal_kunjungan', $lastMonth->month)
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, rekam_medis.waktu_resep_selesai, pembayaran.waktu_obat_diserahkan)) as average_seconds')
            ->value('average_seconds') ?? 0;

        if ($averageApotekLast > 0) {
            // Waktu tunggu: penurunan persentase adalah POSITIF
            $apotekPercentage = round((($averageApotekTotal - $averageApotekLast) / $averageApotekLast) * 100, 1);
        } else {
            $apotekPercentage = ($averageApotekTotal > 0) ? 100 : 0;
        }

        $minutes = floor($averageApotekTotal / 60);
        $seconds = floor($averageApotekTotal % 60);
        $formattedApotekTime = "{$minutes}m {$seconds}s";


        // =======================================================
        // SUSUN DATA UNTUK DIKIRIM KE VIEW
        // =======================================================
        $metrics = [
            // 1. Konsultasi
            'consultation_time' => $formattedConsultationTime,
            'consultation_percentage' => $consultationPercentage . '%',
            // Waktu: Lebih sedikit adalah POSITIF (persentase negatif berarti perbaikan)
            'consultation_is_positive' => $consultationPercentage < 0,

            // 2. Pasien Baru
            'new_patient_count' => $totalNewPatients,
            'new_patient_percentage' => $newPatientPercentage . '%',
            // Pasien: Lebih banyak adalah POSITIF
            'new_patient_is_positive' => $newPatientPercentage >= 0,

            // 3. Pasien Terdaftar
            'registered_patient_count' => $registeredThisMonth,
            'registered_patient_percentage' => $registeredPercentage . '%',
            'registered_is_positive' => $registeredPercentage >= 0,

            // 4. Tunggu Dokter
            'doctor_wait_time' => $formattedDoctorWaitTime,
            'doctor_wait_percentage' => $doctorWaitPercentage . '%',
            'doctor_wait_is_positive' => $doctorWaitPercentage < 0,

            // 5. Obat Habis
            'medicine_out_count' => $totalObatHabis,
            'medicine_out_percentage' => $obatHabisPercentage . '%',
            'medicine_out_is_positive' => $totalObatHabis == 0, // Hanya positif jika 0

            // 6. Tunggu Apotek
            'pharmacy_wait_time' => $formattedApotekTime,
            'pharmacy_wait_percentage' => $apotekPercentage . '%',
            'pharmacy_wait_is_positive' => $apotekPercentage < 0,

            // Context
            'compare_text' => $compareText,
        ];

        // Mengirim ke View (asumsi nama view: dashboard.index)
        return response()->json($metrics);
    }

    /**
     * Helper function untuk menghitung rata-rata waktu tunggu dokter 
     * (memecah logika kompleks agar kode utama tetap bersih)
     */
    private function calculateAverageWaitTime(Carbon $date)
    {
        $kunjungan = Kunjungan::whereNotNull('waktu_mulai_pemeriksaan')
            ->whereYear('tanggal_kunjungan', $date->year)
            ->whereMonth('tanggal_kunjungan', $date->month)
            ->get();

        $totalWaitTimeMinutes = $kunjungan->sum(function ($k) {
            // Gabungkan tanggal_kunjungan dan jam_kunjungan untuk waktu appointment
            $appointmentTime = Carbon::parse($k->tanggal_kunjungan . ' ' . $k->jam_kunjungan);
            $startTime = Carbon::parse($k->waktu_mulai_pemeriksaan);

            // Pastikan waktu pemeriksaan tidak lebih awal dari waktu janji (abs)
            return abs($startTime->diffInMinutes($appointmentTime));
        });

        $count = $kunjungan->count();

        return ($count > 0) ? ($totalWaitTimeMinutes / $count) : 0;
    }


    public function getPendapatanBulanan()
    {
        $totalThisMonth = Pembayaran::whereYear('tanggal_pembayaran', now()->year)
            ->whereMonth('tanggal_pembayaran', now()->month)
            ->sum('total_biaya');

        // Hitung total pendapatan bulan lalu
        $totalLastMonth = Pembayaran::whereYear('tanggal_pembayaran', now()->subMonth()->year)
            ->whereMonth('tanggal_pembayaran', now()->subMonth()->month)
            ->sum('total_biaya');


        // Hitung persentase perubahan
        if ($totalLastMonth > 0) {
            $percentage = round((($totalThisMonth - $totalLastMonth) / $totalLastMonth) * 100, 1);
        } else {
            $percentage = ($totalThisMonth > 0) ? 100 : 0;
        }
        // dd($totalLastMonth, $totalThisMonth, $percentage);

        $currentMonthName = now()->translatedFormat('F');
        $compareText = "dari bulan " . $currentMonthName;

        return response()->json([
            'total' => $totalThisMonth,
            'percentage' => $percentage,
            'compare_text' => $compareText
        ]);
    }

    public function getPengeluaranBulanan()
    {
        $totalThisMonth = PembelianObat::whereYear('tanggal_pembelian', now()->year)
            ->whereMonth('tanggal_pembelian', now()->month)
            ->sum('total_harga');

        // Hitung total pengeluaran bulan lalu
        $totalLastMonth = PembelianObat::whereYear('tanggal_pembelian', now()->subMonth()->year)
            ->whereMonth('tanggal_pembelian', now()->subMonth()->month)
            ->sum('total_harga');

        // Hitung persentase perubahan
        if ($totalLastMonth > 0) {
            $percentage = round((($totalThisMonth - $totalLastMonth) / $totalLastMonth) * 100, 1);
        } else {
            $percentage = ($totalThisMonth > 0) ? 100 : 0;
        }
        // dd($totalLastMonth, $totalThisMonth, $percentage);
        $currentMonthName = now()->translatedFormat('F');
        $compareText = "dari bulan " . $currentMonthName;

        return response()->json([
            'total' => $totalThisMonth,
            'percentage' => $percentage,
            'compare_text' => $compareText
        ]);
    }

    public function getDataAntriCepat()
    {
        $data = Kunjungan::with('tenagaMedis', 'pasien')
            ->select([
                'waktu_mulai_pemeriksaan',
                'status',
                'pasien_id',
                'tenaga_medis_id'
            ])
            ->get();


        return DataTables::of($data)
            ->addIndexColumn()

            ->addColumn('nama', function ($kunjungan) {
                return $kunjungan->pasien->nama_lengkap ?? 'N/A';
            })

            ->addColumn('tenaga_medis', function ($kunjungan) {
                return $kunjungan->tenagaMedis->nama_lengkap ?? 'N/A';
            })

            ->addColumn('jadwal', function ($kunjungan) {
                return $kunjungan->waktu_mulai_pemeriksaan;
            })

            ->addColumn('status', function ($kunjungan) {
                return $kunjungan->status;
            })
            ->make(true);
    }
}
