<?php

namespace App\Http\Controllers\Farmasi;

use Illuminate\Http\Request;
use App\Models\BahanHabisPakai;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Exports\PenggunaanBhpExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
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

    public function exportExcel(Request $request)
    {
        $filters = [
            'start_date' => $request->filter_start_date,
            'end_date' => $request->filter_end_date,
            'nama_barang' => $request->filter_nama_barang,
        ];

        $nama_file = 'Laporan_Penggunaan_BHP_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new PenggunaanBhpExport($filters), $nama_file);
    }

    /**
     * #3 – Print
     * route: farmasi.penggunaan-obat.print
     */
    public function printPdf(Request $request)
    {
        // 1. Ambil data dengan filter yang sama
        $data = BahanHabisPakai::getDataPenggunaanBhp([
            'start_date' => $request->filter_start_date,
            'end_date' => $request->filter_end_date,
            'nama_barang' => $request->filter_nama_barang,
        ])->get(); // Gunakan get() untuk mengambil koleksi data

        // 2. Siapkan data untuk dikirim ke view
        $payload = [
            'title' => 'Laporan Penggunaan Bahan Habis Pakai',
            'date' => date('d/m/Y H:i'),
            'startDate' => $request->filter_start_date,
            'endDate' => $request->filter_end_date,
            'data' => $data
        ];

        // 3. Load view dan set ukuran kertas
        $pdf = Pdf::loadView('farmasi.penggunaan-bhp.print-preview', $payload)
            ->setPaper('a4', 'portrait');

        // 4. Stream atau Download
        return $pdf->stream('Laporan-Penggunaan-BHP.pdf');
    }
}
