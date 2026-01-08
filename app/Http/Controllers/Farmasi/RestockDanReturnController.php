<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\StokTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RestockDanReturnController extends Controller
{
    public function index()
    {
        return view('farmasi.restock-dan-return-obat-dan-bhp.restock-dan-return-obat-dan-bhp');
    }

    public function getDataRestockDanReturnBarangDanObat()
    {
        $q = StokTransaksi::query()
            ->from('stok_transaksi as st')
            ->leftJoin('supplier as s', 's.id', '=', 'st.supplier_id')
            ->leftJoin('stok_transaksi_detail as d', 'd.stok_transaksi_id', '=', 'st.id')
            ->leftJoin('obat as o', 'o.id', '=', 'd.obat_id')
            ->leftJoin('bahan_habis_pakai as b', 'b.id', '=', 'd.bahan_habis_pakai_id')
            ->select([
                'st.id',
                'st.kode_transaksi',
                'st.nomor_faktur',
                'st.jenis_transaksi',
                'st.tanggal_transaksi',
                'st.created_at',

                DB::raw('COALESCE(s.nama_supplier, "-") as supplier_nama'),

                // kolom yang belum ada di tabel stok_transaksi (tampilkan "-" dulu)
                DB::raw('"-" as tanggal_pengiriman'),
                DB::raw('"-" as tempo'),
                DB::raw('"-" as status'),
                DB::raw('"-" as approved_by_nama'),

                // total qty semua item dalam transaksi
                DB::raw('COALESCE(SUM(d.jumlah), 0) as total_jumlah'),

                // total harga (sum qty * harga_beli)
                DB::raw('COALESCE(SUM(d.jumlah * COALESCE(d.harga_beli, 0)), 0) as total_harga'),

                // gabungkan nama item (obat/bhp)
                DB::raw("GROUP_CONCAT(DISTINCT COALESCE(o.nama_obat, b.nama_barang) SEPARATOR ', ') as nama_item"),
            ])
            ->groupBy([
                'st.id',
                'st.kode_transaksi',
                'st.nomor_faktur',
                'st.jenis_transaksi',
                'st.tanggal_transaksi',
                'st.created_at',
                's.nama_supplier',
            ])
            ->orderByDesc('st.tanggal_transaksi');

        return DataTables::of($q)
            ->editColumn('jenis_transaksi', function ($row) {
                return $row->jenis_transaksi === 'restock'
                    ? '<span class="px-2 py-1 rounded text-xs bg-green-100 text-green-700">Restock</span>'
                    : '<span class="px-2 py-1 rounded text-xs bg-red-100 text-red-700">Return</span>';
            })
            ->addColumn('tanggal_pembuatan', function ($row) {
                // tampilkan created_at sebagai "Tanggal Pembuatan"
                return optional($row->created_at)->format('Y-m-d H:i');
            })
            ->editColumn('nama_item', fn($row) => $row->nama_item ?: '-')
            ->editColumn('total_harga', function ($row) {
                return 'Rp' . number_format((float)$row->total_harga, 0, ',', '.');
            })
            ->addColumn('aksi', function ($row) {
                // karena status belum ada, tombol approve belum ditampilkan dulu
                return '<a href="#" class="text-blue-600 hover:underline">Detail</a>';
            })
            ->rawColumns(['jenis_transaksi', 'aksi'])
            ->make(true);
    }
}
