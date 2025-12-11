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
        // Helper parse angka (kalau masih ada . atau , yang nyangkut)
        $parseNumber = function ($value) {
            if ($value === null || $value === '') {
                return 0;
            }

            // "100.000" -> "100000" ;  "1.234,56" -> "1234.56"
            $value = str_replace(['.', ','], ['', '.'], $value);

            return (float) $value;
        };

        // Hitung total stok dari semua depot
        $stokDepotCollection = collect($request->input('stok_depot', []))
            ->map(fn($v) => (int) $v)
            ->filter(fn($v) => $v > 0);

        $totalStok = $stokDepotCollection->sum();

        // Parse harga
        $hargaBeli = $parseNumber($request->input('harga_beli_satuan'));
        $hargaJual = $parseNumber($request->input('harga_jual_umum'));
        $hargaOtc  = $parseNumber($request->input('harga_otc'));

        // Generate kode_obat (pakai barcode kalau diisi, kalau tidak generate sendiri)
        $kodeObat = $request->input('barcode') ?: $this->generateKodeObat();

        // --- VALIDASI ---
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

            // stok global (readonly di form, tapi tetap kita validasi)
            'stok_obat'        => ['required', 'integer', 'min:0'],

            // harga – sudah dibersihkan di JS, jadi numeric string
            'harga_beli_satuan' => ['nullable'],
            'harga_jual_umum'   => ['nullable'],
            'harga_otc'         => ['nullable'],

            'kunci_harga_obat'  => ['required', 'boolean'],

            // array depot
            'depot_id'          => ['required', 'array', 'min:1'],
            'depot_id.*'        => ['nullable', 'exists:depot,id'],

            'stok_depot'        => ['required', 'array', 'min:1'],
            'stok_depot.*'      => ['nullable', 'integer', 'min:0'],

            'tipe_depot'        => ['nullable', 'array'],
            'tipe_depot.*'      => ['nullable', 'exists:tipe_depot,id'],
        ]);

        // Kalau stok_depot kosong / semua 0, fallback ke stok_obat dari form
        if ($totalStok <= 0) {
            $totalStok = (int) $request->input('stok_obat', 0);
        }

        DB::beginTransaction();

        try {
            // ==========================
            // 1. SIMPAN KE TABEL OBAT
            // ==========================
            $depotIds   = $request->input('depot_id', []);
            $stokDepot  = $request->input('stok_depot', []);
            $tipeDepot  = $request->input('tipe_depot', []); // masih dipakai utk validasi saja

            // ambil depot pertama sebagai depot utama (kalau kolom depot_id masih ada di tabel obat)
            $firstDepotId = $depotIds[0] ?? null;

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
            ]);

            // ==========================
            // 2. SIMPAN KE PIVOT depot_obat (sesuai skema: depot_id, obat_id)
            // ==========================
            // pastikan di model Obat ada:
            // public function depot() {
            //     return $this->belongsToMany(Depot::class, 'depot_obat', 'oxampbat_id', 'depot_id')
            //                 ->withTimestamps();
            // }

            $validDepotIds = array_filter($depotIds, function ($id) {
                return !empty($id);
            });

            if (!empty($validDepotIds)) {
                // kirim array id saja, karena pivot cuma punya depot_id & obat_id
                $obat->depot()->attach($validDepotIds);
            }

            DB::commit();

            return response()->json([
                'status'  => 200,
                'data'    => $obat,
                'message' => 'Berhasil Menambahkan Data Obat!',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Gagal menyimpan data obat & depot',
                'error'   => $e->getMessage(),
            ], 500);
        }
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
        $Obat = Obat::with('brandFarmasi', 'kategoriObat', 'jenisObat', 'satuanObat', 'depot.tipeDepot')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $Obat
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

            'kunci_harga_obat'  => ['required', 'boolean'],

            // array depot
            'depot_id'          => ['required', 'array', 'min:1'],
            'depot_id.*'        => ['nullable', 'exists:depot,id'],

            'stok_depot'        => ['required', 'array', 'min:1'],
            'stok_depot.*'      => ['nullable', 'integer', 'min:0'],

            'tipe_depot'        => ['nullable', 'array'],
            'tipe_depot.*'      => ['nullable', 'exists:tipe_depot,id'],
        ]);

        // ==============================
        // UPDATE DATA OBAT
        // ==============================
        $obat->update([
            'kode_obat'              => $kodeObat,
            'brand_farmasi_id'       => $request->input('brand_farmasi_id'),
            'kategori_obat_id'       => $request->input('kategori_obat'),
            'jenis_obat_id'          => $request->input('jenis'),
            'satuan_obat_id'         => $request->input('satuan'),

            // untuk sementara ambil depot pertama sebagai depot utama
            'depot_id'               => $request->input('depot_id.0'),

            'nama_obat'              => $request->input('nama_obat'),
            'kandungan_obat'         => $request->input('kandungan'),

            'tanggal_kadaluarsa_obat' => $request->input('expired_date'),
            'nomor_batch_obat'       => $request->input('nomor_batch'),

            'jumlah'                 => $totalStok,
            'dosis'                  => $request->input('dosis'),

            'total_harga'            => $hargaBeli,       // harga beli
            'harga_jual_obat'        => $hargaJual,
            'harga_otc_obat'         => $hargaOtc,

            // 'kunci_harga_obat'       => $request->boolean('kunci_harga_obat'),
        ]);

        // ==============================
        // UPDATE DATA DEPOT UTAMA
        // (sementara hanya 1 depot, pakai index 0)
        // ==============================
        $depotIdUtama = $request->input('depot_id.0');

        if ($depotIdUtama) {
            $stokUtama    = (int) $request->input('stok_depot.0', 0);
            $tipeDepotId  = $request->input('tipe_depot.0');

            $depot = Depot::find($depotIdUtama);
            if ($depot) {
                $depot->jumlah_stok_depot = $stokUtama;

                if ($tipeDepotId) {
                    $depot->tipe_depot_id = $tipeDepotId;
                }

                $depot->save();
            }
        }

        return response()->json([
            'status'  => 200,
            'data'    => $obat->fresh('brandFarmasi', 'kategoriObat', 'jenisObat', 'satuanObat', 'depot.tipeDepot'),
            'message' => 'Berhasil Mengupdate Data Obat!',
        ]);
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
