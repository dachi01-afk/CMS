<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\StokTransaksi;
use App\Models\StokTransaksiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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

                // placeholder
                DB::raw('"-" as tanggal_pengiriman'),
                DB::raw('"-" as tempo'),
                DB::raw('"-" as status'),
                DB::raw('"-" as approved_by_nama'),

                DB::raw('COALESCE(SUM(d.jumlah), 0) as total_jumlah'),
                DB::raw('COALESCE(SUM(d.jumlah * COALESCE(d.harga_beli, 0)), 0) as total_harga'),
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
                return optional($row->created_at)->format('Y-m-d H:i');
            })
            ->editColumn('nama_item', fn($row) => $row->nama_item ?: '-')
            ->editColumn('total_harga', function ($row) {
                return 'Rp' . number_format((float)$row->total_harga, 0, ',', '.');
            })
            ->addColumn('aksi', function ($row) {
                return '<a href="#" class="text-blue-600 hover:underline">Detail</a>';
            })
            ->rawColumns(['jenis_transaksi', 'aksi'])
            ->make(true);
    }

    /**
     * META untuk dropdown global form (yang kecil & stabil).
     * Kategori & satuan bisa kamu ambil di sini.
     * Kalau tabel kamu beda nama kolomnya, sesuaikan select-nya.
     */
    public function getFormMeta()
    {
        $kategoriObat = DB::table('kategori_obat')->select('id', 'nama_kategori_obat as nama')->orderBy('nama_kategori_obat')->get();

        $satuan = DB::table('satuan_obat')->select('id', 'nama_satuan_obat as nama')->orderBy('nama_satuan_obat')->get();

        $depot = DB::table('depot')
            ->select('id', 'nama_depot as nama') // <- sesuaikan kolom ini jika beda
            ->orderBy('nama_depot')
            ->get();


        // default depot "Apotek" (kalau ada)
        $defaultDepotId = optional(
            DB::table('depot')->where('nama_depot', 'Apotek')->first()
        )->id;

        return response()->json([
            'kategori_obat' => $kategoriObat,
            'satuan'        => $satuan,
            'depot'         => $depot,
            'default_depot_id' => $defaultDepotId,
        ]);
    }

    /**
     * SEARCH OBAT: Digunakan oleh TomSelect untuk mencari list nama obat.
     */
    public function getDataObat(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $rows = DB::table('obat as o')
            // Join ke kategori dan satuan agar info ini bisa langsung muncul/terkeep
            ->leftJoin('kategori_obat as ko', 'ko.id', '=', 'o.kategori_obat_id')
            ->leftJoin('satuan_obat as so', 'so.id', '=', 'o.satuan_obat_id')
            ->select([
                'o.id',
                'o.kode_obat',
                'o.nama_obat',
                'o.kategori_obat_id',
                'ko.nama_kategori_obat', // Nama kategori
                'o.satuan_obat_id',
                'so.nama_satuan_obat',   // Nama satuan
                'o.kandungan_obat',
                'o.harga_jual_obat',
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('o.nama_obat', 'like', "%{$q}%")
                        ->orWhere('o.kode_obat', 'like', "%{$q}%");
                });
            })
            ->orderBy('o.nama_obat')
            ->limit(20)
            ->get();

        return response()->json($rows);
    }

    /**
     * SEARCH BHP untuk select.
     */
    public function getDataBHP(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $rows = DB::table('bahan_habis_pakai as b')
            ->select([
                'b.id',
                'b.kode',
                'b.nama_barang',
                'b.jenis_id',
                'b.satuan_id',
                'b.stok_barang',
                'b.tanggal_kadaluarsa_bhp',
                'b.no_batch',
                'b.harga_beli_satuan_bhp',
                'b.harga_jual_umum_bhp',
                'b.harga_otc_bhp',
                'b.keterangan',
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('b.nama_barang', 'like', "%{$q}%")
                        ->orWhere('b.kode', 'like', "%{$q}%")
                        ->orWhere('b.no_batch', 'like', "%{$q}%");
                });
            })
            ->orderBy('b.nama_barang')
            ->limit(50)
            ->get();

        return response()->json($rows);
    }


    /**
     * META OBAT: Diambil saat satu obat dipilih. 
     * Mengambil detail harga beli lama, batch terakhir, dan expired.
     */
    public function getMetaObat(Request $request, $id)
    {
        $obat = DB::table('obat as o')
            ->leftJoin('kategori_obat as ko', 'ko.id', '=', 'o.kategori_obat_id')
            ->leftJoin('satuan_obat as so', 'so.id', '=', 'o.satuan_obat_id')
            ->select([
                'o.*',
                'ko.nama_kategori_obat',
                'so.nama_satuan_obat'
            ])
            ->where('o.id', $id)
            ->first();

        if (!$obat) {
            return response()->json(['message' => 'Obat tidak ditemukan'], 404);
        }

        // Kalkulasi Harga Beli Satuan Lama (dari stok yang ada)
        $stok = (float) ($obat->jumlah ?? 0);
        $totalHargaPersediaan = (float) ($obat->total_harga ?? 0);
        $hargaBeliLama = $stok > 0 ? ($totalHargaPersediaan / $stok) : 0;

        return response()->json([
            'id'                => $obat->id,
            'nama_obat'         => $obat->nama_obat,
            'kategori_id'       => $obat->kategori_obat_id,
            'nama_kategori'     => $obat->nama_kategori_obat ?? '-',
            'satuan_id'         => $obat->satuan_obat_id,
            'nama_satuan'       => $obat->nama_satuan_obat ?? '-',
            'harga_beli_lama'   => $hargaBeliLama,
            'harga_jual_lama'   => (float) $obat->harga_jual_obat,
            'batch_lama'        => $obat->nomor_batch_obat ?? '',
            'expired_lama'      => $obat->tanggal_kadaluarsa_obat ?? '',
            'stok_sekarang'     => $stok
        ]);
    }


    /**
     * META BHP: harga lama + kategori + satuan + batch/expired histori.
     */
    public function getMetaBhp($id, Request $request)
    {
        $depotId = $request->get('depot_id');

        $bhp = DB::table('bahan_habis_pakai')
            ->select('id', 'nama_barang', 'kategori_bhp_id', 'harga_beli', 'satuan_id')
            ->where('id', $id)
            ->first();

        if (!$bhp) return response()->json(['message' => 'BHP tidak ditemukan'], 404);

        $histori = DB::table('stok_transaksi_detail')
            ->select('batch', 'expired_date')
            ->where('bahan_habis_pakai_id', $id)
            ->when($depotId, fn($q) => $q->where('depot_id', $depotId))
            ->whereNotNull('batch')
            ->groupBy('batch', 'expired_date')
            ->orderByDesc('expired_date')
            ->limit(50)
            ->get();

        return response()->json([
            'bhp' => $bhp,
            'batch_expired' => $histori,
        ]);
    }

    /**
     * STORE: Versi yang rapi & aman.
     * - transaksi di header saja
     * - detail bisa 1 item atau banyak item (items[])
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_transaksi' => 'required|date',
            'jenis_transaksi'   => 'required|in:restock,return',
            'supplier_id'       => 'nullable|exists:supplier,id',
            'nomor_faktur'      => 'nullable|string|max:255',
            'keterangan'        => 'nullable|string',

            // items[] (disarankan)
            'items'                 => 'required|array|min:1',
            'items.*.type'          => 'required|in:obat,bhp',
            'items.*.obat_id'       => 'nullable|required_if:items.*.type,obat|exists:obat,id',
            'items.*.bhp_id'        => 'nullable|required_if:items.*.type,bhp|exists:bahan_habis_pakai,id',
            'items.*.batch'         => 'nullable|string|max:255',
            'items.*.expired_date'  => 'nullable|date',
            'items.*.jumlah'        => 'required|integer|min:1',
            'items.*.satuan_id'     => 'nullable|exists:satuan_obat,id',
            'items.*.harga_beli'    => 'nullable|numeric|min:0',
            'items.*.depot_id'      => 'required|exists:depot_obat,id',
            'items.*.keterangan'    => 'nullable|string',
        ], [
            'items.required' => 'Minimal 1 rincian item harus ditambahkan.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        return DB::transaction(function () use ($request) {
            $kode = $this->generateKodeTransaksi();

            $st = StokTransaksi::create([
                'kode_transaksi'    => $kode,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'jenis_transaksi'   => $request->jenis_transaksi,
                'supplier_id'       => $request->supplier_id,
                'nomor_faktur'      => $request->nomor_faktur,
                'keterangan'        => $request->keterangan,
                'created_by'        => Auth::id(),
            ]);

            foreach ($request->items as $it) {
                StokTransaksiDetail::create([
                    'stok_transaksi_id'     => $st->id,
                    'obat_id'               => $it['type'] === 'obat' ? ($it['obat_id'] ?? null) : null,
                    'bahan_habis_pakai_id'  => $it['type'] === 'bhp'  ? ($it['bhp_id'] ?? null) : null,
                    'batch'                 => $it['batch'] ?? null,
                    'expired_date'          => $it['expired_date'] ?? null,
                    'jumlah'                => $it['jumlah'],
                    'satuan_id'             => $it['satuan_id'] ?? null,
                    'harga_beli'            => $it['harga_beli'] ?? null,
                    'depot_id'              => $it['depot_id'],
                    'keterangan'            => $it['keterangan'] ?? null,
                ]);

                // Kalau nanti kamu mau update stok fisik:
                // $this->applyStockMutation($request->jenis_transaksi, $it);
            }

            return response()->json([
                'message' => 'Transaksi berhasil disimpan',
                'id' => $st->id,
                'kode' => $st->kode_transaksi,
                // optional redirect
                // 'redirect_url' => route('farmasi.restock_return.detail', $st->id),
            ]);
        });
    }

    private function generateKodeTransaksi(): string
    {
        $ymd = now()->format('Ymd');
        $prefix = "STK-{$ymd}-";

        $last = StokTransaksi::where('kode_transaksi', 'like', $prefix . '%')
            ->max('kode_transaksi');

        $next = 1;
        if ($last) {
            $parts = explode('-', $last);
            $seq = (int) end($parts);
            $next = $seq + 1;
        }

        return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }
}
