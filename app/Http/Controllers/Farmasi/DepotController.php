<?php

namespace App\Http\Controllers\Farmasi;

use App\Models\Depot;
use App\Models\DepotObat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\DepotBHP;
use Yajra\DataTables\Facades\DataTables;

class DepotController extends Controller
{
    /**
     * Digunakan untuk search Brand di TomSelect.
     */
    public function getDataDepot(Request $request)
    {
        $search = $request->q; // TomSelect kirim "q" sebagai keyword search

        $query = Depot::query();

        if ($search) {
            $query->where('nama_depot', 'like', '%' . $search . '%');
        }

        // batasin biar ringan
        $data = $query->orderBy('nama_depot')->limit(20)->get();

        return response()->json($data);
    }

    /**
     * Digunakan saat user klik "Add xxxx..." di TomSelect.
     */
    public function createDataDepot(Request $request)
    {
        $validated = $request->validate([
            'nama_depot' => ['required', 'string', 'max:100', 'unique:depot,nama_depot'],
        ]);

        $jenis = Depot::create([
            'nama_depot' => $validated['nama_depot'],
        ]);

        // TomSelect butuh object: { id: xxx, nama: "..." }
        return response()->json($jenis, 201);
    }

    public function deleteDataDepot(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer', 'exists:depot,id'],
        ]);

        $jenis = Depot::findOrFail($request->id);

        // Kalau sudah dipakai di obat, jangan dihapus
        if ($jenis->obat()->exists()) {
            return response()->json([
                'message' => 'Jenis Obat sudah dipakai di data obat, tidak dapat dihapus.',
            ], 422);
        }

        $jenis->delete();

        return response()->json([
            'message' => 'Jenis Obat berhasil dihapus.',
        ]);
    }

    public function index()
    {
        return view('farmasi.depot.depot');
    }

    public function dataTables(Request $request)
    {
        $q = Depot::query()
            ->from('depot')
            ->leftJoin('tipe_depot', 'tipe_depot.id', '=', 'depot.tipe_depot_id')
            ->select([
                'depot.id',
                'depot.nama_depot',
                'depot.jumlah_stok_depot',
                'depot.updated_at',
                'tipe_depot.nama_tipe_depot',
            ])
            // jumlah jenis obat di depot (baris di depot_obat)
            ->selectSub(function ($sq) {
                $sq->from('depot_obat')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('depot_obat.depot_id', 'depot.id');
            }, 'total_obat')
            // jumlah jenis BHP di depot (baris di depot_bhp)
            ->selectSub(function ($sq) {
                $sq->from('depot_bhp')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('depot_bhp.depot_id', 'depot.id');
            }, 'total_bhp')
            // total stok BHP (SUM stok)
            ->selectSub(function ($sq) {
                $sq->from('depot_bhp')
                    ->selectRaw('COALESCE(SUM(stok_barang),0)')
                    ->whereColumn('depot_bhp.depot_id', 'depot.id');
            }, 'total_stok_bhp');

        return DataTables::of($q)
            ->addIndexColumn()

            ->addColumn('nama_depot', function ($row) {
                return '
                    <div class="flex items-center gap-3">
                        <div class="min-w-0">
                            <div class="font-semibold text-slate-800 leading-tight">' . e($row->nama_depot) . '</div>
                        </div>
                    </div>
                ';
            })

            // kolom tipe depot (kalau kamu mau tampil kolom terpisah)
            ->editColumn('nama_tipe_depot', function ($row) {
                return $row->nama_tipe_depot ?: '-';
            })

            // kolom jumlah stok depot (angka rapih)
            ->editColumn('jumlah_stok_depot', function ($row) {
                return number_format((int) $row->jumlah_stok_depot, 0, ',', '.');
            })

            ->addColumn('aksi', function ($row) {
                $id = $row->id;

                $showObat   = url("/farmasi/depot/get-data-obat-by-depot/$id");
                $repairObat = url("/farmasi/depot/get-data-repair-obat-by-depot/$id");
                $repairBhp  = url("/farmasi/depot/get-data-repair-bhp-by-depot/$id");

                return '
                    <div class="flex items-center justify-end gap-2 flex-wrap">
                        <button id="btn-show-obat" 
                                data-id="' . $id . '"
                                data-url="' . $showObat . '"
                           class="inline-flex items-center justify-center px-3 py-2 rounded-xl text-[11px] font-semibold
                                  bg-sky-600 text-white hover:bg-sky-700">
                            Show Obat
                        </button>

                        <button id="btn-repair-obat" 
                                data-id="' . $id . '"
                                data-url="' . $repairObat . '"
                           class="inline-flex items-center justify-center px-3 py-2 rounded-xl text-[11px] font-semibold
                                  bg-emerald-600 text-white hover:bg-emerald-700">
                            Stok Opname Obat
                        </button>

                        <button id="btn-repair-bhp" 
                                data-id="' . $id . '"
                                data-url="' . $repairBhp . '"
                           class="inline-flex items-center justify-center px-3 py-2 rounded-xl text-[11px] font-semibold
                                  bg-teal-600 text-white hover:bg-teal-700">
                            Stok Opname BHP
                        </button>
                    </div>
                ';
            })

            ->rawColumns(['nama_depot', 'aksi'])
            ->make(true);
    }

    public function getDataObatByDepotId($id)
    {
        $depot = Depot::with('depotObat')->findOrFail($id);

        return response()->json([
            // Kita ambil array 'obat' yang ada di dalam objek depot tersebut
            'data'    => $depot->depotObat,
            'message' => 'Data Obat Berhasil Diambil',
        ], 200);
    }

    public function getDataRepairStokObatByDepotId(Request $request, $id)
    {
        // 1. Langsung Query ke model DepotObat (tabel depot_obat) 
        // Cari data yang depot_id nya sesuai dengan $id yang dikirim
        $query = DepotObat::where('depot_id', $id)->with('obat');

        // 2. Masukkan ke Engine DataTable
        return DataTables::of($query)
            ->addIndexColumn()
            ->filter(function ($instance) use ($request) {
                if ($request->has('search') && !empty($request->get('search')['value'])) {
                    $search = $request->get('search')['value'];

                    // Logic pencarian harus masuk ke relasi 'obat' karena 
                    // nama_obat dan kode_obat ada di tabel obats
                    $instance->where(function ($q) use ($search) {
                        $q->whereHas('obat', function ($queryObat) use ($search) {
                            $queryObat->where('nama_obat', 'LIKE', "%$search%")
                                ->orWhere('kode_obat', 'LIKE', "%$search%");
                        });
                    });
                }
            })
            // Ambil data stok dari tabel pivot/depot_obat
            ->editColumn('pivot.stok_obat', function ($row) {
                return $row->stok_obat; // Sesuaikan dengan nama kolom stok di tabel depot_obat
            })
            // Keluarkan kode_obat dari relasi obat
            ->addColumn('kode_obat', function ($row) {
                return $row->obat->kode_obat ?? '-';
            })
            // Keluarkan nama_obat dari relasi obat
            ->addColumn('nama_obat', function ($row) {
                return $row->obat->nama_obat ?? '-';
            })
            ->make(true);
    }

    public function repairStokObat(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'depot_id'          => 'required|exists:depot,id',
            'items'             => 'required|array|min:1',
            'items.*.obat_id'   => 'required|exists:obat,id',
            'items.*.qty_fisik' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $depotId = $request->depot_id;

                foreach ($request->items as $item) {
                    $obatId   = $item['obat_id'];
                    $qtyFisik = $item['qty_fisik']; // Angka nyata di rak (Contoh: 100)

                    // --- LANGKAH 1: Ambil Stok Sistem Saat Ini ---
                    $stokSistem = DB::table('depot_obat')
                        ->where('depot_id', $depotId)
                        ->where('obat_id', $obatId)
                        ->value('stok_obat') ?? 0; // Contoh: 300

                    // --- LANGKAH 2: Hitung Selisih ---
                    // 300 (sistem) - 100 (fisik) = 200 (selisih yang harus dibuang)
                    $selisih = $stokSistem - $qtyFisik;

                    // --- LANGKAH 3: Update Tabel Pivot (depot_obat) ---
                    // Timpa stok sistem dengan angka fisik agar saat modal dibuka lagi muncul 100
                    DB::table('depot_obat')
                        ->where('depot_id', $depotId)
                        ->where('obat_id', $obatId)
                        ->update([
                            'stok_obat'  => $qtyFisik,
                            'updated_at' => now()
                        ]);

                    // --- LANGKAH 4: Update Master (obat & depot) ---
                    // Kurangi total stok global dan depot sebesar SELISIH-nya saja
                    if ($selisih != 0) {
                        // Update Master Obat
                        DB::table('obat')
                            ->where('id', $obatId)
                            ->decrement('jumlah', $selisih);

                        // Update Master Depot
                        DB::table('depot')
                            ->where('id', $depotId)
                            ->decrement('jumlah_stok_depot', $selisih);
                    }
                }
            });

            return response()->json([
                'status'  => 200,
                'message' => 'Berhasil! Stok depot diperbarui sesuai fisik dan master disesuaikan.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDataRepairStokBHPByDepotId($id)
    {
        $depot = DepotBHP::getDataBHP($id)->get();

        return DataTables::of($depot)
            ->addIndexColumn()
            ->filter(function ($query) { // Hapus parameter kedua ($request)
    // Gunakan helper request() langsung
    $search = request('search'); 
    
    if (!empty($search['value'])) {
        $keyword = $search['value'];

        $query->whereHas('bahanHabisPakai', function ($q) use ($keyword) {
            $q->where('nama_bhp', 'LIKE', "%$keyword%")
              ->orWhere('kode_bhp', 'LIKE', "%$keyword%");
        });
    }
})
            ->addColumn('kode_bhp', function ($row) {
                return $row->bahanHabisPakai->kode ?? '-';
            })
            ->addColumn('nama_bhp', function ($row) {
                return $row->bahanHabisPakai->nama_barang ?? '-';
            })
            // Ambil data stok dari tabel pivot/depot_obat
            ->editColumn('pivot.stok_barang', function ($row) {
                return $row->stok_barang; // Sesuaikan dengan nama kolom stok di tabel depot_obat
            })
            ->make(true);
    }

        public function repairStokBHP(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'depot_id'          => 'required|exists:depot,id',
            'items'             => 'required|array|min:1',
            'items.*.bahan_habis_pakai_id'   => 'required|exists:bahan_habis_pakai,id',
            'items.*.qty_fisik' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $depotId = $request->depot_id;

                foreach ($request->items as $item) {
                    $bhpId   = $item['bahan_habis_pakai_id'];
                    $qtyFisik = $item['qty_fisik']; // Angka nyata di rak (Contoh: 100)

                    // --- LANGKAH 1: Ambil Stok Sistem Saat Ini ---
                    $stokSistem = DB::table('depot_bhp')
                        ->where('depot_id', $depotId)
                        ->where('bahan_habis_pakai_id', $bhpId)
                        ->value('stok_barang') ?? 0; // Contoh: 300

                    // --- LANGKAH 2: Hitung Selisih ---
                    // 300 (sistem) - 100 (fisik) = 200 (selisih yang harus dibuang)
                    $selisih = $stokSistem - $qtyFisik;

                    // --- LANGKAH 3: Update Tabel Pivot (depot_obat) ---
                    // Timpa stok sistem dengan angka fisik agar saat modal dibuka lagi muncul 100
                    DB::table('depot_bhp')
                        ->where('depot_id', $depotId)
                        ->where('bahan_habis_pakai_id', $bhpId)
                        ->update([
                            'stok_barang'  => $qtyFisik,
                            'updated_at' => now()
                        ]);

                    // --- LANGKAH 4: Update Master (obat & depot) ---
                    // Kurangi total stok global dan depot sebesar SELISIH-nya saja
                    if ($selisih != 0) {
                        // Update Master Obat
                        DB::table('bahan_habis_pakai')
                            ->where('id', $bhpId)
                            ->decrement('stok_barang', $selisih);

                        // Update Master Depot
                        DB::table('depot')
                            ->where('id', $depotId)
                            ->decrement('jumlah_stok_depot', $selisih);
                    }
                }
            });

            return response()->json([
                'status'  => 200,
                'message' => 'Berhasil! Stok depot diperbarui sesuai fisik dan master disesuaikan.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
    }
}
