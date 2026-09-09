<?php

namespace App\Http\Controllers\Farmasi;

use App\Exports\PenggunaanObatExport;
use App\Http\Controllers\Controller;
use App\Models\Obat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelWriter;

class PenggunaanObatController extends Controller
{
    public function index()
    {
        return view('farmasi.penggunaan-obat.penggunaan-obat');
    }

    /**
     * Base query rekap penggunaan obat per obat.
     *
     * Catatan:
     * - Status "Sudah Diambil" sekarang ada di tabel resep (resep.status).
     *   SESUAIKAN nilai enum/teksnya di $statusDiambil jika perlu.
     * - Filter tanggal menggunakan resep_obat.updated_at (tanggal item resep berubah/diambil).
     */
    protected function buildBaseQuery(Request $request)
    {
        $startDate = $request->input('start_date'); // Y-m-d
        $endDate   = $request->input('end_date');   // Y-m-d
        $namaObat  = $request->input('nama_obat');

        $statusDiambil = 'done';

        $query = Obat::query()
            ->from('obat')
            ->leftJoin('resep_obat', 'obat.id', '=', 'resep_obat.obat_id')
            // ✅ JOIN resep tanpa filter status di sini
            ->leftJoin('resep', 'resep_obat.resep_id', '=', 'resep.id')
            ->leftJoin('kunjungan', 'resep.kunjungan_id', '=', 'kunjungan.id')
            ->leftJoin('satuan_obat', 'obat.satuan_obat_id', '=', 'satuan_obat.id')
            ->when($namaObat, function ($q) use ($namaObat) {
                $q->where('obat.nama_obat', 'like', '%' . $namaObat . '%');
            })
            ->select([
                'obat.id',
                'obat.nama_obat',
                'obat.kandungan_obat',
                'obat.jumlah as sisa_obat',
                'obat.harga_jual_obat',
                'satuan_obat.nama_satuan_obat as satuan',
            ]);

        // ✅ Bangun ekspresi CASE WHEN secara dinamis, tergantung ada filter tanggal atau tidak
        if ($startDate && $endDate) {
            $query->addSelect([
                DB::raw("
                COALESCE(SUM(
                    CASE
                        WHEN resep.status = " . DB::getPdo()->quote($statusDiambil) . "
                         AND DATE(resep_obat.updated_at) BETWEEN " . DB::getPdo()->quote($startDate) . " AND " . DB::getPdo()->quote($endDate) . "
                        THEN resep_obat.jumlah
                        ELSE 0
                    END
                ), 0) as penggunaan_umum
            "),
                DB::raw("
                COALESCE(SUM(
                    CASE
                        WHEN resep.status = " . DB::getPdo()->quote($statusDiambil) . "
                         AND DATE(resep_obat.updated_at) BETWEEN " . DB::getPdo()->quote($startDate) . " AND " . DB::getPdo()->quote($endDate) . "
                        THEN resep_obat.jumlah * obat.harga_jual_obat
                        ELSE 0
                    END
                ), 0) as nominal_umum
            "),
            ]);
        } else {
            $query->addSelect([
                DB::raw("
                COALESCE(SUM(
                    CASE WHEN resep.status = " . DB::getPdo()->quote($statusDiambil) . "
                    THEN resep_obat.jumlah ELSE 0 END
                ), 0) as penggunaan_umum
            "),
                DB::raw("
                COALESCE(SUM(
                    CASE WHEN resep.status = " . DB::getPdo()->quote($statusDiambil) . "
                    THEN resep_obat.jumlah * obat.harga_jual_obat ELSE 0 END
                ), 0) as nominal_umum
            "),
            ]);
        }

        $query->addSelect([
            DB::raw('0 as penggunaan_bpjs'),
            DB::raw('0 as nominal_bpjs'),
        ])
            ->groupBy(
                'obat.id',
                'obat.nama_obat',
                'obat.kandungan_obat',
                'obat.jumlah',
                'obat.harga_jual_obat',
                'satuan_obat.nama_satuan_obat'
            );

        return $query;
    }

    /**
     * #1 – Datatable Ajax
     * route: farmasi.penggunaan-obat.datatable
     */
    public function getDataPenggunaanObat(Request $request)
    {
        $query = $this->buildBaseQuery($request);

        return DataTables::of($query)
            ->editColumn('nominal_umum', fn($row) => (int) $row->nominal_umum)
            ->editColumn('nominal_bpjs', fn($row) => (int) $row->nominal_bpjs)
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
