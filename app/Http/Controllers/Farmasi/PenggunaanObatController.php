<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\Obat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

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
     * - Semua resep_obat yang status-nya "Sudah Diambil" dianggap sebagai obat yang sudah dipakai.
     *   Kalau enum-mu beda (misal: 'Diambil', 'Selesai'), SESUAIKAN di where().
     * - Umum / BPJS di sini sementara disatukan ke "Umum" saja.
     *   Kalau nanti kamu punya field penjamin, bisa dipisah di sini.
     */
    protected function buildBaseQuery(Request $request)
    {
        $startDate = $request->input('start_date');  // format: Y-m-d
        $endDate   = $request->input('end_date');
        $namaObat  = $request->input('nama_obat');

        $query = Obat::query()
            ->from('obat')
            ->leftJoin('resep_obat', function ($join) {
                $join->on('obat.id', '=', 'resep_obat.obat_id')
                    ->where('resep_obat.status', '=', 'Sudah Diambil'); // SESUAIKAN ENUM
            })
            ->leftJoin('resep', 'resep_obat.resep_id', '=', 'resep.id')
            ->leftJoin('kunjungan', 'resep.kunjungan_id', '=', 'kunjungan.id') // boleh tetap, sekadar info
            ->leftJoin('satuan_obat', 'obat.satuan_obat_id', '=', 'satuan_obat.id')
            ->leftJoin('depot', 'obat.depot_id', '=', 'depot.id')
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                // 🔁 SEKARANG: filter pakai tanggal resep diambil
                $q->whereBetween(DB::raw('DATE(resep_obat.updated_at)'), [$startDate, $endDate]);
            })
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
                'depot.nama_depot as depot_nama',

                DB::raw('COALESCE(SUM(resep_obat.jumlah), 0) as penggunaan_umum'),
                DB::raw('COALESCE(SUM(resep_obat.jumlah * obat.harga_jual_obat), 0) as nominal_umum'),

                DB::raw('0 as penggunaan_bpjs'),
                DB::raw('0 as nominal_bpjs'),
            ])
            ->groupBy(
                'obat.id',
                'obat.nama_obat',
                'obat.kandungan_obat',
                'obat.jumlah',
                'obat.harga_jual_obat',
                'satuan_obat.nama_satuan_obat',
                'depot.nama_depot'
            );

        return $query;
    }


    /**
     * #1 – Datatable Ajax
     *
     * route: farmasi.penggunaan-obat.datatable
     */
    public function getDataPenggunanObat(Request $request)
    {
        $query = $this->buildBaseQuery($request);

        return DataTables::of($query)
            ->editColumn('nominal_umum', function ($row) {
                return (int) $row->nominal_umum;
            })
            ->editColumn('nominal_bpjs', function ($row) {
                return (int) $row->nominal_bpjs;
            })
            ->make(true);
    }

    /**
     * #2 – Export (CSV sederhana, bisa dibuka di Excel)
     *
     * route: farmasi.penggunaan-obat.export
     */
    public function export(Request $request)
    {
        $rows = $this->buildBaseQuery($request)->get();

        $fileName = 'penggunaan-obat-' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');

            // Header CSV
            fputcsv($handle, [
                'Nama Obat',
                'Kandungan',
                'Depot',
                'Satuan',
                'Penggunaan Umum',
                'Nominal Umum',
                'Penggunaan BPJS',
                'Nominal BPJS',
                'Sisa Obat',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->nama_obat,
                    $row->kandungan_obat,
                    $row->depot_nama,
                    $row->satuan,
                    $row->penggunaan_umum,
                    $row->nominal_umum,
                    $row->penggunaan_bpjs,
                    $row->nominal_bpjs,
                    $row->sisa_obat,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * #3 – Print (tampilan HTML siap di-print)
     *
     * route: farmasi.penggunaan-obat.print
     *
     * Kamu bisa buat view: resources/views/farmasi/penggunaan-obat/print.blade.php
     */
    public function print(Request $request)
    {
        $rows      = $this->buildBaseQuery($request)->get();
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $namaObat  = $request->input('nama_obat');

        return view('farmasi.penggunaan-obat.print', [
            'rows'      => $rows,
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'namaObat'  => $namaObat,
        ]);
    }
}
