<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\BahanHabisPakai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PenggunaanBHPController extends Controller
{
    public function index()
    {
        return view('farmasi.penggunaan-bhp.penggunaan-bhp');
    }

    /**
     * #1 – Datatable Ajax
     * route: farmasi.penggunaan-bhp.datatable
     */
    public function getDataPenggunaanBHP(Request $request)
    {
        $dataBhp = BahanHabisPakai::getDataPenggunaanBhp([
            'start_date' => $request->filter_start_date,
            'end_date' => $request->filter_end_date,
            // 'nama_barang' => $request->filter_nama_barang,
        ]);

        return DataTables::of($dataBhp)
            ->addIndexColumn()

            // --- TAMBAHKAN INI AGAR PENCARIAN GLOBAL TIDAK ERROR ---
            // Pindahkan filter Nama Barang ke sini agar DataTables tahu 
            // bahwa ini adalah proses "Filtering" bukan "Data Kosong"
            ->filter(function ($query) use ($request) {
                if ($request->has('filter_nama_barang') && !empty($request->filter_nama_barang)) {
                    $query->where('bahan_habis_pakai.nama_barang', 'like', "%" . $request->filter_nama_barang . "%");
                }
            })

            // Kolom Penggunaan Umum (Qty)
            ->editColumn('penggunaan_umum', function ($row) {
                return number_format($row->total_pakai_umum ?? 0, 0);
            })

            // Kolom Nominal Umum (Qty x Harga Umum)
            ->addColumn('nominal_umum', function ($row) {
                $nominal = ($row->total_pakai_umum ?? 0) * ($row->harga_jual_umum_bhp ?? 0);
                return 'Rp ' . number_format($nominal, 0, ',', '.');
            })

            // Kolom Sisa Stok
            ->editColumn('sisa_stok', function ($row) {
                return number_format($row->stok_barang, 0);
            })

            ->rawColumns(['nominal_umum'])
            ->make(true);
    }

    public function export(Request $request)
    {
        $query = $this->buildBaseQuery($request);

        $fileName = 'penggunaan-obat-' . now()->format('Ymd_His') . '.xlsx';

        $start = $request->start_date;
        $end   = $request->end_date;

        $title = 'Laporan Penggunaan Obat';
        if ($start && $end) {
            $title .= " ({$start} s/d {$end})";
        }

        return Excel::download(
            new PenggunaanObatExport($query, $title),
            $fileName,
            ExcelWriter::XLSX
        );
    }

    /**
     * #3 – Print
     * route: farmasi.penggunaan-obat.print
     */
    public function print(Request $request)
    {
        // pakai query yang sama biar hasilnya konsisten dengan tabel & export
        $query = $this->buildBaseQuery($request);

        // untuk PDF, sebaiknya ambil semua rows (kalau rekap per obat biasanya kecil)
        $rows = $query->orderBy('nama_obat')->get();

        $start = $request->start_date;
        $end   = $request->end_date;
        $nama  = $request->nama_obat;

        $periode = '-';
        if ($start && $end) {
            $periode = Carbon::parse($start)->translatedFormat('d F Y')
                . ' s/d ' .
                Carbon::parse($end)->translatedFormat('d F Y');
        }

        $meta = [
            'judul'      => 'Laporan Penggunaan Obat',
            'periode'    => $periode,
            'filterNama' => $nama ? $nama : '-',
            'printedAt'  => now()->translatedFormat('d F Y H:i'),
        ];

        $pdf = Pdf::loadView('farmasi.penggunaan-obat.print-preview', compact('rows', 'meta'))
            ->setPaper('a4', 'landscape');

        $fileName = 'cetak-penggunaan-obat-' . now()->format('Ymd_His') . '.pdf';

        // stream = buka di tab baru (lebih cocok untuk print)
        return $pdf->stream($fileName);
    }
}
