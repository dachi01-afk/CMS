<?php

namespace App\Http\Controllers\Admin;

use App\Exports\KunjunganExport;
use App\Exports\LaporanKeuanganExport;
use App\Models\Kunjungan;
use App\Models\Pembayaran;
use App\Models\Administrasi;
use Illuminate\Http\Request;
use App\Models\TransaksiApoteker;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class LaporanController extends Controller
{
    public function index()
    {
        return view('admin.laporan');
    }

    public function dataKunjungan(Request $request)
{
    $periode = $request->get('periode'); // "" | minggu | bulan | tahun
    $bulan   = $request->get('bulan');
    $tahun   = $request->get('tahun');

    $today = Carbon::today();

    // fallback tahun kalau kosong
    if (!$tahun) {
        $tahun = $today->year;
    }

    $query = Kunjungan::with(['dokter', 'poli', 'pasien'])
        ->latest('tanggal_kunjungan');

    if ($periode === 'minggu') {
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek   = $today->copy()->endOfWeek(Carbon::SUNDAY);

        $query->whereBetween('tanggal_kunjungan', [$startOfWeek, $endOfWeek]);
    } elseif ($periode === 'bulan') {
        // kalau user pilih bulan → pakai itu, kalau tidak → pakai bulan sekarang
        $bulanDipakai = $bulan ?: $today->month;

        $query->whereYear('tanggal_kunjungan', $tahun)
              ->whereMonth('tanggal_kunjungan', $bulanDipakai);
    } elseif ($periode === 'tahun') {
        $query->whereYear('tanggal_kunjungan', $tahun);
    }

    return DataTables::of($query)
        ->filter(function ($query) use ($request) {
            $search = $request->input('search.value');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('no_antrian', 'like', "%{$search}%")
                      ->orWhere('tanggal_kunjungan', 'like', "%{$search}%")
                      ->orWhere('keluhan_awal', 'like', "%{$search}%")
                      ->orWhere('status', 'like', "%{$search}%")
                      ->orWhereHas('dokter', function ($q2) use ($search) {
                          $q2->where('nama_dokter', 'like', "%{$search}%");
                      })
                      ->orWhereHas('pasien', function ($q3) use ($search) {
                          $q3->where('nama_pasien', 'like', "%{$search}%");
                      });
                });
            }
        })
        ->addIndexColumn()
        ->addColumn('no_antrian', fn($kunjungan) => $kunjungan->no_antrian ?? '-')
        ->addColumn('nama_dokter', fn($kunjungan) => $kunjungan->dokter->nama_dokter ?? '-')
        ->addColumn('nama_pasien', fn($kunjungan) => $kunjungan->pasien->nama_pasien ?? '-')
        ->editColumn('tanggal_kunjungan', function ($kunjungan) {
            return $kunjungan->tanggal_kunjungan
                ? \Carbon\Carbon::parse($kunjungan->tanggal_kunjungan)
                    ->locale('id')
                    ->translatedFormat('j F Y')
                : '-';
        })
        ->editColumn('status', function ($kunjungan) {
            return match ($kunjungan->status) {
                'Pending'  => '<span class="px-2 py-1 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded">Pending</span>',
                'Waiting'  => '<span class="px-2 py-1 text-xs font-semibold text-blue-700 bg-blue-100 rounded">Waiting</span>',
                'Engaged'  => '<span class="px-2 py-1 text-xs font-semibold text-sky-700 bg-sky-100 rounded">Engaged</span>',
                'Succeed'  => '<span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded">Succeed</span>',
                'Canceled' => '<span class="px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded">Canceled</span>',
                default    => '<span class="px-2 py-1 text-xs font-semibold text-gray-700 bg-gray-100 rounded">-</span>',
            };
        })
        ->rawColumns(['status'])
        ->make(true);
}


    public function dataPembayaran()
    {
        $query = Pembayaran::with(['pasien:id,nama_pasien'])
            ->select(['id', 'pasien_id', 'total_tagihan', 'status', 'tanggal_pembayaran']);

        return DataTables::of($query)
            ->addColumn('pasien', fn($pembayaran) => $pembayaran->pasien->nama_pasien ?? '-')
            ->editColumn('total_tagihan', fn($pembayaran) => 'Rp ' . number_format($pembayaran->total_tagihan, 0, ',', '.'))
            ->editColumn('status', function ($pembayaran) {
                return $pembayaran->status === 'Sudah Bayar'
                    ? '<span class="text-green-600 font-semibold">Sudah Bayar</span>'
                    : '<span class="text-red-600 font-semibold">Belum Bayar</span>';
            })
            ->editColumn('tanggal_pembayaran', function ($pembayaran) {
                return \Carbon\Carbon::parse($pembayaran->tanggal_pembayaran)->format('d/m/Y H:i');
            })
            ->rawColumns(['status'])
            ->make(true);
    }

    public function dataTransaksiApoteker()
    {
        $query = TransaksiApoteker::with([
            'apoteker:id,nama_apoteker'
        ])->select(['id', 'resep_id', 'apoteker_id', 'tanggal_transaksi_apoteker', 'total_harga']);

        return DataTables::of($query)
            ->addColumn('kode_resep', fn($row) => $row->resep->id ?? '-')
            ->addColumn('nama_apoteker', fn($row) => $row->apoteker->nama_apoteker ?? '-')
            ->make(true);
    }

    public function dataAdministrasi()
    {
        $query = Administrasi::select(['id', 'laporan', 'tarif', 'periode']);

        return DataTables::of($query)
            ->make(true);
    }

public function exportKunjungan(Request $request)
{
    $periode = $request->input('periode'); // "", minggu, bulan, tahun
    $bulan   = $request->input('bulan');   // 01–12 (optional)
    $tahun   = $request->input('tahun');   // optional

    $today = Carbon::today();

    if (!$tahun) {
        $tahun = $today->year;
    }

    $query = Kunjungan::with(['dokter', 'poli', 'pasien'])
        ->latest('tanggal_kunjungan');

    // 🔹 Filter tanggal – samakan dengan dataKunjungan()
    if ($periode === 'minggu') {
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek   = $today->copy()->endOfWeek(Carbon::SUNDAY);

        $query->whereBetween('tanggal_kunjungan', [$startOfWeek, $endOfWeek]);
    } elseif ($periode === 'bulan') {
        $bulanDipakai = $bulan ?: $today->month;

        $query->whereYear('tanggal_kunjungan', $tahun)
              ->whereMonth('tanggal_kunjungan', $bulanDipakai);
    } elseif ($periode === 'tahun') {
        $query->whereYear('tanggal_kunjungan', $tahun);
    }
    // kalau "" (semua) → tanpa filter tanggal

    $dataKunjungan = $query->get();

    // 🔹 Kalau tidak ada data → balik dengan pesan error
    if ($dataKunjungan->isEmpty()) {
        $namaBulan = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];

        if ($periode === 'bulan') {
            // kalau bulan gak diisi, pakai bulan sekarang untuk teks
            $bulanTeks = $bulan ? ($namaBulan[$bulan] ?? $bulan) : $namaBulan[$today->format('m')];
            $teksPeriode = "bulan {$bulanTeks} pada tahun {$tahun}";
        } elseif ($periode === 'tahun') {
            $teksPeriode = "tahun {$tahun}";
        } elseif ($periode === 'minggu') {
            $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY)->format('d M Y');
            $endOfWeek   = $today->copy()->endOfWeek(Carbon::SUNDAY)->format('d M Y');
            $teksPeriode = "minggu ini ({$startOfWeek} - {$endOfWeek})";
        } else {
            $teksPeriode = "periode yang dipilih";
        }

        return back()->with('error', "Data untuk {$teksPeriode} tidak ada.");
    }

    // 🔹 Kalau ada data → lanjutkan export
    return Excel::download(new KunjunganExport($dataKunjungan), 'kunjungan.xlsx');
}

    // public function exportKeuangan(Request $request)
    // {
    //     $filter = $request->get('filter', 'mingguan');
    //     $bulan = $request->get('bulan');
    //     $tahun = $request->get('tahun', now()->year);

    //     return Excel::download(new LaporanKeuanganExport($filter, $bulan, $tahun), "laporan_keuangan_{$filter}_{$tahun}.xlsx");
    // }

    public function exportKeuangan(Request $request)
    {
        $filter = $request->get('filter', 'mingguan');
        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun', now()->year);

        // 🔹 Ambil data pembayaran beserta relasi pasien & metode pembayaran
        $data = Pembayaran::with([
            'emr.kunjungan.pasien',
            'emr.kunjungan.layanan', // ini penting
            'emr.resep.obat',
            'metodePembayaran',
        ])
            ->when(
                $filter === 'tahunan',
                fn($q) =>
                $q->whereYear('tanggal_pembayaran', $tahun)
            )
            ->when(
                $filter === 'bulanan',
                fn($q) =>
                $q->whereMonth('tanggal_pembayaran', $bulan)
                    ->whereYear('tanggal_pembayaran', $tahun)
            )
            ->when(
                $filter === 'mingguan',
                fn($q) =>
                $q->whereBetween('tanggal_pembayaran', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ])
            )
            ->orderBy('tanggal_pembayaran', 'asc')
            ->get();

        return Excel::download(
            new LaporanKeuanganExport($data, $filter, $bulan, $tahun),
            "laporan_keuangan_{$filter}_{$tahun}.xlsx"
        );
    }
}
