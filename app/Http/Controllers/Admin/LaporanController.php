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
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class LaporanController extends Controller
{
    public function index()
    {
        return view('admin.laporan');
    }

    public function dataKunjungan()
    {
        $dataKunjungan = Kunjungan::with('poli.dokter', 'pasien')->latest()->get();

        return DataTables::of($dataKunjungan)
            ->addIndexColumn()
            ->addColumn('no_antrian', fn($row) => $row->no_antrian ?? '-')
            ->addColumn('nama_dokter', fn($row) => $row->poli->dokter->nama_dokter ?? '-')
            ->addColumn('nama_pasien', fn($row) => $row->pasien->nama_pasien ?? '-')
            ->editColumn('tanggal_kunjungan', function ($row) {
                return $row->tanggal_kunjungan
                    ? \Carbon\Carbon::parse($row->tanggal_kunjungan)
                    ->locale('id')
                    ->translatedFormat('j F Y')
                    : '-';
            })
            ->editColumn('status', function ($row) {
                return match ($row->status) {
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
        $periode = $request->input('periode'); // ambil dari form
        return Excel::download(new KunjunganExport($periode), 'kunjungan.xlsx');
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
