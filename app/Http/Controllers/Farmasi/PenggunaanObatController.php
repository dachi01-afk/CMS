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
     * - Status "Sudah Diambil" sekarang ada di tabel resep (resep.status).
     *   SESUAIKAN nilai enum/teksnya di $statusDiambil jika perlu.
     * - Filter tanggal menggunakan resep_obat.updated_at (tanggal item resep berubah/diambil).
     */
    protected function buildBaseQuery(Request $request)
    {
        $startDate = $request->input('start_date'); // Y-m-d
        $endDate   = $request->input('end_date');   // Y-m-d
        $namaObat  = $request->input('nama_obat');

        // ✅ status ada di tabel resep
        $statusDiambil = 'Sudah Diambil'; // SESUAIKAN bila enum kamu beda: 'diambil', 'done', dll.

        $query = Obat::query()
            ->from('obat')
            ->leftJoin('resep_obat', function ($join) {
                $join->on('obat.id', '=', 'resep_obat.obat_id');
                // ⛔ jangan filter status di sini lagi, karena status bukan di resep_obat
            })
            ->leftJoin('resep', function ($join) use ($statusDiambil) {
                $join->on('resep_obat.resep_id', '=', 'resep.id')
                    ->where('resep.status', '=', $statusDiambil); // ✅ pindah ke sini
            })
            ->leftJoin('kunjungan', 'resep.kunjungan_id', '=', 'kunjungan.id')
            ->leftJoin('satuan_obat', 'obat.satuan_obat_id', '=', 'satuan_obat.id')
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                // 🔁 filter range tanggal berdasarkan tanggal item resep (pivot) terakhir berubah
                // ✅ TAMBAHAN LOGIC: tetap tampilkan obat yang belum pernah terjual (resep_obat NULL)
                $q->where(function ($w) use ($startDate, $endDate) {
                    $w->whereBetween(DB::raw('DATE(resep_obat.updated_at)'), [$startDate, $endDate])
                        ->orWhereNull('resep_obat.id'); // atau orWhereNull('resep_obat.updated_at')
                });
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

                // ✅ karena join resep sudah di-filter statusnya, SUM hanya menghitung yang statusnya "Sudah Diambil"
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

    /**
     * #2 – Export CSV
     * route: farmasi.penggunaan-obat.export
     */
    public function export(Request $request)
    {
        $rows = $this->buildBaseQuery($request)->get();

        $fileName = 'penggunaan-obat-' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');

            // (opsional) BOM biar Excel enak baca UTF-8
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'Nama Obat',
                'Kandungan',
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
     * #3 – Print
     * route: farmasi.penggunaan-obat.print
     */
    public function print(Request $request)
    {
        $rows      = $this->buildBaseQuery($request)->get();
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $namaObat  = $request->input('nama_obat');

        return view('farmasi.penggunaan-obat.print', compact('rows', 'startDate', 'endDate', 'namaObat'));
    }
}
