<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\Depot;
use App\Models\KategoriObat;
use App\Models\Obat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ObatController extends Controller
{
    public function index()
    {
        return view('farmasi.obat.obat');
    }

    public function getDataKategoriObat(Request $request)
    {
        $search = $request->q; // TomSelect kirim "q" sebagai keyword search

        $query = KategoriObat::query();

        if ($search) {
            $query->where('nama_kategori_obat', 'like', '%' . $search . '%');
        }

        // batasin biar ringan
        $data = $query->orderBy('nama_kategori_obat')->limit(20)->get();

        return response()->json($data);
    }

    public function getDataObat()
    {
        $query = Obat::with([
            'kategoriObat:id,nama_kategori_obat',
            'satuanObat:id,nama_satuan_obat',
            'brandFarmasi:id,nama_brand',
            'jenisObat:id,nama_jenis_obat',
        ])->latest();

        return DataTables::eloquent($query)
            ->addIndexColumn()

            // KODE
            ->addColumn('kode', function ($obat) {
                return $obat->kode_obat ?? '-';
            })

            // NAMA OBAT
            ->editColumn('nama_obat', function ($obat) {
                return $obat->nama_obat ?? '-';
            })

            // FARMASI (brand)
            ->addColumn('farmasi', function ($obat) {
                return optional($obat->brandFarmasi)->nama_brand ?? '-';
            })

            // JENIS
            ->addColumn('jenis', function ($obat) {
                return optional($obat->jenisObat)->nama_jenis_obat ?? '-';
            })

            // KATEGORI
            ->addColumn('kategori', function ($obat) {
                return optional($obat->kategoriObat)->nama_kategori_obat ?? '-';
            })

            // STOK (global)
            ->addColumn('stok', function ($obat) {
                return is_null($obat->jumlah) ? 0 : (int) $obat->jumlah;
            })

            // DOSIS mentah (kalau perlu, kamu format `mg` di JS)
            ->editColumn('dosis', function ($obat) {
                return $obat->dosis;
            })

            // HARGA BELI (pakai total_harga)
            ->addColumn('harga_beli', function ($obat) {
                return is_null($obat->total_harga) ? 0 : (float) $obat->total_harga;
            })

            // HARGA UMUM (harga_jual_obat)
            ->addColumn('harga_umum', function ($obat) {
                return is_null($obat->harga_jual_obat) ? 0 : (float) $obat->harga_jual_obat;
            })

            // AVG HPP (sementara samakan dengan harga_beli)
            ->addColumn('avg_hpp', function ($obat) {
                return is_null($obat->total_harga) ? 0 : (float) $obat->total_harga;
            })

            // HARGA OTC
            ->addColumn('harga_otc', function ($obat) {
                return is_null($obat->harga_otc_obat) ? 0 : (float) $obat->harga_otc_obat;
            })

            // MARGIN PROFIT = harga_umum - harga_beli
            ->addColumn('margin_profit', function ($obat) {
                $beli = $obat->total_harga ?? 0;
                $jual = $obat->harga_jual_obat ?? 0;
                return max($jual - $beli, 0);
            })

            // kalau masih mau kirim total_harga mentah juga (dipakai JS lama)
            ->editColumn('total_harga', function ($obat) {
                return is_null($obat->total_harga) ? 0 : (float) $obat->total_harga;
            })

            // AKSI
            ->addColumn('action', function ($obat) {
                return '
                <div class="flex items-center justify-center gap-2">
                    <button 
                        class="btn-edit-obat inline-flex items-center justify-center w-8 h-8 rounded-lg 
                               bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-100"
                        data-id="' . $obat->id . '" 
                        title="Edit">
                        <i class="fa-regular fa-pen-to-square text-xs"></i>
                    </button>

                    <button 
                        class="btn-delete-obat inline-flex items-center justify-center w-8 h-8 rounded-lg 
                               bg-red-50 text-red-600 hover:bg-red-100 border border-red-100"
                        data-id="' . $obat->id . '" 
                        title="Hapus">
                        <i class="fa-regular fa-trash-can text-xs"></i>
                    </button>
                </div>
            ';
            })

            ->rawColumns(['action'])
            ->toJson();
    }

    public function createObat(Request $request)
    {
        // ==============================
        // Helper parse angka rupiah/number
        // ==============================
        $parseNumber = function ($value) {
            if ($value === null || $value === '') {
                return 0;
            }

            // "100.000" -> "100000"
            // "1.234,56" -> "1234.56"
            $value = str_replace(['.', ','], ['', '.'], $value);

            return (float) $value;
        };

        // ==============================
        // Hitung total stok dari semua depot
        // ==============================
        $stokDepotCollection = collect($request->input('stok_depot', []))
            ->map(fn($v) => (int) $v)
            ->filter(fn($v) => $v > 0);

        $totalStok = $stokDepotCollection->sum();

        // fallback ke stok_obat kalau stok_depot kosong / semua 0
        if ($totalStok <= 0) {
            $totalStok = (int) $request->input('stok_obat', 0);
        }

        // ==============================
        // Parse harga
        // ==============================
        $hargaBeli = $parseNumber($request->input('harga_beli_satuan'));
        $hargaJual = $parseNumber($request->input('harga_jual_umum'));
        $hargaOtc  = $parseNumber($request->input('harga_otc'));

        // ==============================
        // Kode obat: pakai barcode kalau diisi, kalau tidak auto generate
        // ==============================
        $kodeObat = $request->input('barcode') ?: 'OBT-' . Str::upper(Str::random(8));

        // ==============================
        // VALIDASI
        // ==============================
        $validated = $request->validate([
            'barcode'          => ['nullable', 'string', 'max:255'],
            'nama_obat'        => ['required', 'string', 'max:255'],

            'brand_farmasi_id' => ['nullable', 'exists:brand_farmasi,id'],
            'kategori_obat'    => ['required', 'exists:kategori_obat,id'],
            'jenis'            => ['nullable', 'exists:jenis_obat,id'],
            'satuan'           => ['required', 'exists:satuan_obat,id'],

            'dosis'            => ['required', 'numeric', 'min:0'],
            'expired_date'     => ['required', 'date'],
            'nomor_batch'      => ['required', 'string', 'max:255'],
            'kandungan'        => ['nullable', 'string', 'max:255'],

            'stok_obat'        => ['required', 'integer', 'min:0'],

            // harga – sudah dibersihkan di JS, tapi tetap kita validasi
            'harga_beli_satuan' => ['nullable', 'numeric', 'min:0'],
            'harga_jual_umum'   => ['nullable', 'numeric', 'min:0'],
            'harga_otc'         => ['nullable', 'numeric', 'min:0'],

            // 'kunci_harga_obat'  => ['required', 'boolean'],

            // array depot
            'depot_id'          => ['required', 'array', 'min:1'],
            'depot_id.*'        => ['nullable', 'exists:depot,id'],

            'stok_depot'        => ['required', 'array', 'min:1'],
            'stok_depot.*'      => ['nullable', 'integer', 'min:0'],

            'tipe_depot'        => ['nullable', 'array'],
            'tipe_depot.*'      => ['nullable', 'exists:tipe_depot,id'],
        ]);

        // ==============================
        // SIMPAN DATA OBAT
        // ==============================
        $obat = Obat::create([
            'kode_obat'               => $kodeObat,
            'brand_farmasi_id'        => $request->input('brand_farmasi_id'),
            'kategori_obat_id'        => $request->input('kategori_obat'),
            'jenis_obat_id'           => $request->input('jenis'),
            'satuan_obat_id'          => $request->input('satuan'),
            'nama_obat'               => $request->input('nama_obat'),
            'kandungan_obat'          => $request->input('kandungan'),
            'tanggal_kadaluarsa_obat' => $request->input('expired_date'),
            'nomor_batch_obat'        => $request->input('nomor_batch'),
            'jumlah'                  => $totalStok,
            'dosis'                   => $request->input('dosis'),
            'total_harga'             => $hargaBeli,
            'harga_jual_obat'         => $hargaJual,
            'harga_otc_obat'          => $hargaOtc,
            // 'kunci_harga_obat'        => $request->boolean('kunci_harga_obat'),
        ]);

        // ==============================
        // SIMPAN RELASI MANY-TO-MANY DEPOT
        // + UPDATE tipe_depot_id & jumlah_stok_depot DI TABEL depot
        // ==============================
        $depotIds     = $request->input('depot_id', []);
        $tipeDepotIds = $request->input('tipe_depot', []);
        $stokDepot    = $request->input('stok_depot', []);

        $attachDepotIds = [];

        foreach ($depotIds as $index => $depId) {
            if (!$depId) {
                continue;
            }

            $attachDepotIds[] = $depId;

            $depot = Depot::find($depId);
            if (!$depot) {
                continue;
            }

            $tipeId = $tipeDepotIds[$index] ?? null;
            $stok   = (int) ($stokDepot[$index] ?? 0);

            // set tipe_depot jika diisi
            if ($tipeId) {
                $depot->tipe_depot_id = $tipeId;
            }

            // simpan stok per depot
            $depot->jumlah_stok_depot = $stok;
            $depot->save();
        }

        // attach ke pivot depot_obat (many to many)
        if (!empty($attachDepotIds)) {
            $obat->depotObat()->sync($attachDepotIds);
        }

        // reload relasi buat respon ke FE
        $obat->load(
            'brandFarmasi',
            'kategoriObat',
            'jenisObat',
            'satuanObat',
            'depotObat.tipeDepot'
        );

        return response()->json([
            'status'  => 200,
            'data'    => $obat,
            'message' => 'Berhasil menambahkan data obat!',
        ]);
    }

    /**
     * Generate kode_obat unik (OBT-XXXXXXX)
     */
    protected function generateKodeObat(): string
    {
        do {
            $kode = 'OBT-' . strtoupper(Str::random(8));
        } while (Obat::where('kode_obat', $kode)->exists());

        return $kode;
    }

    public function getObatById($id)
    {
        $obat = Obat::with(
            'brandFarmasi',
            'kategoriObat',
            'jenisObat',
            'satuanObat',
            'depot',
            // MANY TO MANY: semua depot yang terkait obat ini
            'depotObat.tipeDepot'
        )->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $obat,
        ]);
    }

    public function updateObat(Request $request, $id)
    {
        $obat = Obat::findOrFail($id);

        // ==============================
        // Helper parse angka rupiah/number
        // ==============================
        $parseNumber = function ($value) {
            if ($value === null || $value === '') {
                return 0;
            }

            // "100.000" -> "100000"
            // "1.234,56" -> "1234.56"
            $value = str_replace(['.', ','], ['', '.'], $value);

            return (float) $value;
        };

        // ==============================
        // Hitung total stok dari semua depot
        // ==============================
        $stokDepotCollection = collect($request->input('stok_depot', []))
            ->map(fn($v) => (int) $v)
            ->filter(fn($v) => $v > 0);

        $totalStok = $stokDepotCollection->sum();

        // fallback ke stok_obat kalau stok_depot kosong / semua 0
        if ($totalStok <= 0) {
            $totalStok = (int) $request->input('stok_obat', 0);
        }

        // ==============================
        // Parse harga
        // (front-end sudah kirim angka bersih, tapi kita amanin lagi)
        // ==============================
        $hargaBeli = $parseNumber($request->input('harga_beli_satuan'));
        $hargaJual = $parseNumber($request->input('harga_jual_umum'));
        $hargaOtc  = $parseNumber($request->input('harga_otc'));

        // ==============================
        // Kode obat: pakai barcode kalau diisi, kalau tidak pakai yang lama
        // ==============================
        $kodeObat = $request->input('barcode') ?: $obat->kode_obat;

        // ==============================
        // VALIDASI
        // (disamakan dengan createObat)
        // ==============================
        $validated = $request->validate([
            'barcode'          => ['nullable', 'string', 'max:255'],
            'nama_obat'        => ['required', 'string', 'max:255'],

            'brand_farmasi_id' => ['nullable', 'exists:brand_farmasi,id'],
            'kategori_obat'    => ['required', 'exists:kategori_obat,id'],
            'jenis'            => ['nullable', 'exists:jenis_obat,id'],
            'satuan'           => ['required', 'exists:satuan_obat,id'],

            'dosis'            => ['required', 'numeric', 'min:0'],
            'expired_date'     => ['required', 'date'],
            'nomor_batch'      => ['required', 'string', 'max:255'],
            'kandungan'        => ['nullable', 'string', 'max:255'],

            'stok_obat'        => ['required', 'integer', 'min:0'],

            // harga – sama seperti create: cukup nullable, parsing di server
            'harga_beli_satuan' => ['nullable'],
            'harga_jual_umum'   => ['nullable'],
            'harga_otc'         => ['nullable'],

            // 'kunci_harga_obat'  => ['required', 'boolean'],

            // array depot
            'depot_id'          => ['required', 'array', 'min:1'],
            'depot_id.*'        => ['nullable', 'exists:depot,id'],

            'stok_depot'        => ['required', 'array', 'min:1'],
            'stok_depot.*'      => ['nullable', 'integer', 'min:0'],

            'tipe_depot'        => ['nullable', 'array'],
            'tipe_depot.*'      => ['nullable', 'exists:tipe_depot,id'],
        ]);

        $depotIds  = $request->input('depot_id', []);
        $tipeDepot = $request->input('tipe_depot', []);   // masih buat validasi saja
        $stokDepot = $request->input('stok_depot', []);

        // filter depot yg bener2 ada isi id
        $validDepotIds = array_filter($depotIds, function ($id) {
            return !empty($id);
        });

        DB::beginTransaction();

        try {
            // ==============================
            // UPDATE DATA OBAT
            // ==============================
            $obat->update([
                'kode_obat'               => $kodeObat,
                'brand_farmasi_id'        => $request->input('brand_farmasi_id'),
                'kategori_obat_id'        => $request->input('kategori_obat'),
                'jenis_obat_id'           => $request->input('jenis'),
                'satuan_obat_id'          => $request->input('satuan'),

                // kalau masih ada kolom depot_id di tabel obat dan mau isi depot utama:
                // 'depot_id'                => $depotIds[0] ?? $obat->depot_id,

                'nama_obat'               => $request->input('nama_obat'),
                'kandungan_obat'          => $request->input('kandungan'),

                'tanggal_kadaluarsa_obat' => $request->input('expired_date'),
                'nomor_batch_obat'        => $request->input('nomor_batch'),

                'jumlah'                  => $totalStok,
                'dosis'                   => $request->input('dosis'),

                'total_harga'             => $hargaBeli,
                'harga_jual_obat'         => $hargaJual,
                'harga_otc_obat'          => $hargaOtc,

                // 'kunci_harga_obat'        => $request->boolean('kunci_harga_obat'),
            ]);

            // ==============================
            // SYNC PIVOT depot_obat
            // ==============================
            // pastikan di model Obat sudah ada:
            // public function depots() {
            //     return $this->belongsToMany(Depot::class, 'depot_obat', 'obat_id', 'depot_id')
            //                 ->withTimestamps();
            // }

            if (!empty($validDepotIds)) {
                // kalau pivot cuma simpan obat_id & depot_id
                $obat->depotObat()->sync($validDepotIds);

                // === OPSIONAL: kalau di pivot ada kolom stok, bisa pakai:
                /*
            $pivotData = [];
            foreach ($validDepotIds as $index => $depotId) {
                $stok = (int) ($stokDepot[$index] ?? 0);
                $pivotData[$depotId] = ['stok' => $stok];
            }
            $obat->depots()->sync($pivotData);
            */
            } else {
                // kalau semua depot dikosongkan, detach semua
                $obat->depotObat()->detach();
            }

            DB::commit();

            return response()->json([
                'status'  => 200,
                'data'    => $obat->fresh([
                    'brandFarmasi',
                    'kategoriObat',
                    'jenisObat',
                    'satuanObat',
                    // kalau butuh list semua depot:
                    'depot',
                ]),
                'message' => 'Berhasil Mengupdate Data Obat!',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Gagal mengupdate data obat & depot',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function deleteObat($id)
    {
        $dataObat = Obat::findOrFail($id);
        $dataObat->delete();
        return response()->json([
            'status' => 200,
            'data' => $dataObat,
            'message' => 'Berhasil Menghapus Data Obat!'
        ]);
    }
}
