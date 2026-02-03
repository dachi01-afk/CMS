<?php

namespace App\Http\Controllers\Farmasi;

use App\Models\Obat;
use App\Models\User;
use App\Models\Depot;
use App\Models\Farmasi;
use App\Models\Perawat;
use App\Models\BatchObat;
use Illuminate\Http\Request;
use App\Models\StokTransaksi;
use App\Models\BatchObatDepot;
use App\Models\MutasiStokObat;
use App\Models\BahanHabisPakai;
use Illuminate\Support\Facades\DB;
use App\Models\StokTransaksiDetail;
use App\Http\Controllers\Controller;
use App\Models\MutasiStokObatDetail;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

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
        $kategoriObat = DB::table('kategori_obat')
            ->select('id', 'nama_kategori_obat as nama')
            ->orderBy('nama_kategori_obat')
            ->get();

        $satuan = DB::table('satuan_obat')
            ->select('id', 'nama_satuan_obat as nama')
            ->orderBy('nama_satuan_obat')
            ->get();

        $depot = DB::table('depot')
            ->select('id', 'nama_depot as nama')
            ->orderBy('nama_depot')
            ->get();

        $defaultDepot = DB::table('depot')->where('nama_depot', 'Apotek')->first();
        $defaultDepotId = $defaultDepot ? $defaultDepot->id : null;

        return response()->json([
            'kategori_obat'    => $kategoriObat,
            'satuan'           => $satuan,
            'depot'            => $depot,
            'default_depot_id' => $defaultDepotId,
            // PANGGIL DARI MODEL DI SINI
            'jenis_transaksi'  => MutasiStokObat::getEnumValues('jenis_transaksi'),
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

    public function getDataBatchObat($id)
    {
        $batchObat = BatchObat::where('obat_id', $id)->select('tanggal_kadaluarsa_obat');

        return response()->json(['data' => $batchObat]);
    }

    /**
     * SEARCH BHP untuk select.
     */
    public function getDataBHP(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        // Gunakan Eloquent Model
        $rows = BahanHabisPakai::with(['satuanBHP', 'jenisBHP', 'batchBahanHabisPakai'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where('nama_barang', 'like', "%{$q}%")
                    ->orWhere('kode', 'like', "%{$q}%")
                    // Mencari ke relasi batches
                    ->orWhereHas('batchBahanHabisPakai', function ($queryBatch) use ($q) {
                        $queryBatch->where('nama_batch', 'like', "%{$q}%");
                    });
            })
            ->orderBy('nama_barang')
            ->limit(50)
            ->get();

        // Transformasi data agar JavaScript mudah membacanya
        $data = $rows->map(function ($item) {
            // Ambil batch pertama/terbaru (jika ada)
            $latestBatch = $item->batchBahanHabisPakai->first();

            return [
                'id'           => $item->id,
                'nama_barang'  => $item->nama_barang,
                'kode'         => $item->kode,
                'stok_barang'  => $item->stok_barang,
                'harga_beli'   => $item->harga_beli_satuan_bhp,
                'harga_jual'   => $item->harga_jual_umum_bhp,
                // Ambil Nama dari relasi, bukan cuma ID
                'nama_satuan'  => $item->satuan->nama_satuan ?? '-',
                'nama_kategori' => $item->jenis->nama_jenis ?? '-',
                // Data dari batch
                'no_batch'     => $latestBatch->no_batch ?? '-',
                'tgl_kadaluarsa' => $latestBatch->tanggal_kadaluarsa_bhp ?? '-',
            ];
        });

        return response()->json($data);
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
        $hargaJualOtcLama = $obat->harga_otc_obat;

        return response()->json([
            'id'                => $obat->id,
            'nama_obat'         => $obat->nama_obat,
            'kategori_id'       => $obat->kategori_obat_id,
            'nama_kategori'     => $obat->nama_kategori_obat ?? '-',
            'satuan_id'         => $obat->satuan_obat_id,
            'nama_satuan'       => $obat->nama_satuan_obat ?? '-',
            'harga_beli_satuan_obat_lama'   => (float) $obat->total_harga,
            'harga_jual_otc_obat_lama'   => (float) $obat->harga_otc_obat,
            'harga_jual_lama'   => (float) $obat->harga_jual_obat,
            'harga_total_awal'   => (float) $obat->total_harga,
            'batch_lama'        => $obat->nomor_batch_obat ?? '',
            'expired_lama'      => $obat->tanggal_kadaluarsa_obat ?? '',
            'stok_sekarang'     => $stok
        ]);
    }

    /**
     * META BHP: harga lama + kategori + satuan + batch/expired histori.
     */
    public function getMetaBHP($id, Request $request)
    {
        $depotId = $request->get('depot_id');

        // Gunakan Eloquent dengan pengaman relasi
        $bhp = BahanHabisPakai::with(['satuanBHP', 'jenis', 'batchBahanHabisPakai'])
            ->findOrFail($id);

        $latestBatch = $bhp->batchBahanHabisPakai ? $bhp->batchBahanHabisPakai->first() : null;

        return response()->json([
            'nama_kategori' => $bhp->jenisBHP->nama_jenis_obat ?? '-',
            'nama_satuan'   => $bhp->satuanBHP->nama_satuan_obat ?? '-',
            'harga_beli_satuan_bhp_lama' => $bhp->harga_beli_satuan_bhp,
            'harga_jual_lama' => $bhp->harga_jual_umum_bhp,
            'harga_jual_otc_bhp_lama' => $bhp->harga_otc_bhp,
            'batch_lama'    => $latestBatch->no_batch ?? null,
            'expired_lama'  => $latestBatch->tanggal_kadaluarsa_bhp ?? null,
            'stok_sekarang' => $bhp->stok_barang, // Atau hitung berdasarkan depot_id jika ada tabel stok per depot
        ]);
    }

    /**
     * STORE: Versi yang rapi & aman.
     * - transaksi di header saja
     * - detail bisa 1 item atau banyak item (items[])
     */
    public function store(Request $request)
    {
        // 1. Pre-processing: Bersihkan format Rupiah dari input sebelum divalidasi
        $input = $request->all();
        if (isset($input['items']) && is_array($input['items'])) {
            foreach ($input['items'] as $key => $item) {
                if (isset($item['harga_beli'])) {
                    $input['items'][$key]['harga_beli'] = (int) preg_replace('/[^0-9]/', '', $item['harga_beli']);
                }
                if (isset($item['harga_jual'])) {
                    $input['items'][$key]['harga_jual'] = (int) preg_replace('/[^0-9]/', '', $item['harga_jual']);
                }
            }
        }

        // 2. Validasi
        $validator = Validator::make($input, [
            'tanggal_transaksi' => 'required|date',
            'jenis_transaksi'   => 'required',
            'supplier_id'       => 'nullable|exists:supplier,id',
            // 'nomor_transaksi'      => 'nullable|string|max:255',
            'keterangan'        => 'nullable|string',

            'items'                 => 'required|array|min:1',
            'items.*.type'          => 'required|in:obat,bhp',
            'items.*.obat_id'       => 'nullable|required_if:items.*.type,obat',
            'items.*.batch'         => 'required|string|max:255',
            'items.*.expired_date'  => 'required|date',
            'items.*.jumlah'        => 'required|integer|min:1',
            'items.*.harga_beli'    => 'required|numeric|min:0',
            'items.*.depot_id'      => 'required|exists:depot,id', // Sesuaikan nama tabel depot kamu
        ], [
            'items.required' => 'Minimal 1 rincian item harus ditambahkan.',
            'items.*.obat_id.required_if' => 'Obat harus dipilih.',
            'items.*.batch.required' => 'Nomor Batch wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        return DB::transaction(function () use ($input) {
            $kode = $this->generateKodeTransaksi();

            $id = Auth::id();

            $userId = User::where('id', $id)->firstOrFail();

            $farmasiId = Farmasi::where('user_id', $userId)->value('id');

            // dd($farmasiId);

            // 3. Simpan Header
            $st = MutasiStokObat::create([
                // 'kode_transaksi'    => $kode,
                'tanggal_transaksi' => $input['tanggal_transaksi'],
                'jenis_transaksi'   => $input['jenis_transaksi'],
                'supplier_id'       => $input['supplier_id'] ?? null,
                'nomor_transaksi'      => $kode ?? null,
                'keterangan'        => $input['keterangan'] ?? null,
                'farmasi_id'        => $id,
            ]);

            // 4. Simpan Detail
            foreach ($input['items'] as $it) {
                MutasiStokObatDetail::create([
                    'mutasi_stok_obat_id'     => $st->id,
                    'obat_id'               => $it['type'] === 'obat' ? $it['obat_id'] : null,
                    'depot_id'              => $it['depot_id'],
                    'nomor_batch'                 => $it['batch'],
                    'tanggal_kadaluarsa'          => $it['expired_date'],
                    'jumlah'                => $it['jumlah'],
                    'harga_beli_satuan'            => $it['harga_beli'],
                    'harga_jual_umum'            => $it['harga_beli'],
                    'harga_jual_otc'            => $it['harga_beli'],
                    // 'keterangan'            => $it['keterangan'] ?? null,
                ]);

                // Opsional: Update Harga Jual di tabel Obat jika ada harga baru
                if ($it['type'] === 'obat' && !empty($it['harga_jual'])) {
                    \App\Models\Obat::where('id', $it['obat_id'])->update([
                        'harga_beli_terakhir' => $it['harga_beli'],
                        'harga_jual' => $it['harga_jual']
                    ]);
                }
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Transaksi ' . $kode . ' berhasil disimpan',
                'id'      => $st->id
            ]);
        });
    }

    private function generateKodeTransaksi(): string
    {
        $ymd = now()->format('Ymd');
        $prefix = "STK-{$ymd}-";

        $last = MutasiStokObat::where('nomor_transaksi', 'like', $prefix . '%')
            ->max('nomor_transaksi');

        $next = 1;
        if ($last) {
            $parts = explode('-', $last);
            $seq = (int) end($parts);
            $next = $seq + 1;
        }

        return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }

    public function getDataDepot(Request $request)
    {
        $search = $request->get('q');
        $obatId = $request->get('obat_id'); // Ambil ID obat dari request

        $dataDepot = Depot::where('nama_depot', 'like', "%{$search}%")
            ->get()
            ->map(function ($depot) use ($obatId) {
                // Cari stok di tabel pivot/relasi depot_obat
                $stok = DB::table('depot_obat')
                    ->where('depot_id', $depot->id)
                    ->where('obat_id', $obatId)
                    ->value('stok_obat') ?? 0;

                return [
                    'id' => $depot->id,
                    'nama_depot' => $depot->nama_depot,
                    'stok_obat' => $stok // Tambahkan info stok ke response
                ];
            });

        return response()->json($dataDepot);
    }

    public function getDataDepotBhp(Request $request)
    {
        // $batchId = 
        // $stokItem = BatchDepotObat::where('batch_obat_id', $batchId)->firstOrFail();

        $search = $request->get('q');
        $bhpId = $request->get('bhp_id'); // Ambil ID obat dari request

        $dataDepotBhp = Depot::where('nama_depot', 'like', "%{$search}%")
            ->get()
            ->map(function ($depot) use ($bhpId) {
                // Cari stok di tabel pivot/relasi depot_obat
                $stok = DB::table('depot_bhp')
                    ->where('depot_id', $depot->id)
                    ->where('bahan_habis_pakai_id', $bhpId)
                    ->value('stok_barang') ?? 0;

                return [
                    'id' => $depot->id,
                    'nama_barang' => $depot->nama_barang,
                    'stok_barang' => $stok // Tambahkan info stok ke response
                ];
            });

        return response()->json($dataDepotBhp);
    }

    // Di Controller Anda (misal: ObatController atau RestockController)

    // Route: /farmasi/restock-return/batch-expired
    public function getBatchExpired(Request $request)
    {
        // Mengambil data kadaluarsa berdasarkan obat yang dipilih
        $batches = DB::table('batch_obat')
            ->where('obat_id', $request->obat_id)
            ->select('id', 'tanggal_kadaluarsa', 'nama_batch')
            ->get();

        return response()->json($batches);
    }

    // Route: /farmasi/restock-return/batch-detail
    public function getBatchDetail(Request $request)
    {
        $batchId = $request->batch_id;
        $depotId = $request->depot_id;

        // 1. Ambil Nama Batch untuk diisi ke input text
        $batch = DB::table('batch_obat')->where('id', $batchId)->first();

        // 2. Ambil Stok spesifik di depot tersebut dari tabel batch_obat_depot
        $stokAtDepot = DB::table('batch_obat_depot')
            ->where('batch_obat_id', $batchId)
            ->where('depot_id', $depotId)
            ->value('stok_obat'); // Mengambil kolom 'stok_obat'

        return response()->json([
            'nama_batch' => $batch ? $batch->nama_batch : '-',
            'stok' => $stokAtDepot ?? 0
        ]);
    }

    // public function store(Request $request)
    // {
    //     // 1. Validasi Input
    //     $request->validate([
    //         'tanggal_transaksi' => 'required|date',
    //         'jenis_transaksi'   => 'required|string',
    //         'items'             => 'required|array|min:1',
    //         'items.*.obat_id'   => 'required',
    //         'items.*.jumlah'    => 'required|integer|min:1',
    //     ]);

    //     DB::beginTransaction();
    //     try {
    //         // 2. Simpan ke Tabel Header (RestockReturn)
    //         $transaksi = RestockReturn::create([
    //             'tanggal_transaksi' => $request->tanggal_transaksi,
    //             'jenis_transaksi'   => $request->jenis_transaksi,
    //             'supplier_id'       => $request->supplier_id,
    //             'nomor_faktur'      => $request->nomor_faktur,
    //             'keterangan'        => $request->keterangan,
    //             'user_id'           => auth()->id(), // Mencatat siapa yang input
    //         ]);

    //         // 3. Simpan ke Tabel Detail & Update Stok di Depot
    //         foreach ($request->items as $item) {
    //             // Simpan Detail
    //             RestockReturnDetail::create([
    //                 'restock_return_id' => $transaksi->id,
    //                 'obat_id'           => $item['obat_id'],
    //                 'batch'             => $item['batch'],
    //                 'expired_date'      => $item['expired_date'],
    //                 'jumlah'            => $item['jumlah'],
    //                 'satuan_id'         => $item['satuan_id'],
    //                 'harga_beli'        => $item['harga_beli'],
    //                 'depot_id'          => $item['depot_id'],
    //                 'keterangan'        => $item['keterangan'] ?? null,
    //             ]);

    //             // 4. Update Stok di Tabel depot_obat
    //             // Jika RESTOCK (Barang Masuk) maka stok bertambah (+), jika RETURN (Barang Keluar) maka berkurang (-)
    //             $operator = ($request->jenis_transaksi == 'restock') ? '+' : '-';

    //             DB::table('depot_obat')
    //                 ->updateOrInsert(
    //                     ['depot_id' => $item['depot_id'], 'obat_id' => $item['obat_id']],
    //                     ['stok' => DB::raw("stok $operator " . $item['jumlah']), 'updated_at' => now()]
    //                 );
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Transaksi berhasil disimpan!',
    //             'redirect_url' => route('farmasi.restock-return.index') // Sesuaikan route indexmu
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal menyimpan data: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function getBatchesByObat(Request $request, $obat_id)
    {
        $batches = DB::table('batch_obat')
            ->where('obat_id', $obat_id)
            ->select('id', 'nama_batch', 'tanggal_kadaluarsa_obat')
            ->get();

        return response()->json($batches);
    }

    public function getStokBatch($batch_id)
    {
        // Mengambil stok dari tabel batch_obat_depot berdasarkan id yang dipilih
        $data = DB::table('batch_obat as bo')
            ->join('batch_obat_depot as bod', 'bod.batch_obat_id', '=', 'bo.id')
            ->where('bo.id', $batch_id)
            ->select('bod.stok_obat', 'bo.nama_batch', 'bo.tanggal_kadaluarsa_obat')
            ->first();

        return response()->json($data);
    }
}
