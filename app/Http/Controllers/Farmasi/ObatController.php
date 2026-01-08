<?php

namespace App\Http\Controllers\Farmasi;

use App\Models\Obat;
use App\Models\Depot;
use App\Models\JenisObat;
use App\Exports\ObatExport;
use App\Imports\ObatImport;
use Illuminate\Support\Str;
use App\Models\KategoriObat;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
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
        $parseNumber = function ($value) {
            if ($value === null || $value === '') {
                return 0;
            }

            $value = str_replace(['.', ','], ['', '.'], $value);
            return (float) $value;
        };

        $stokDepotCollection = collect($request->input('stok_depot', []))
            ->map(fn($v) => (int) $v)
            ->filter(fn($v) => $v > 0);

        $totalStok = $stokDepotCollection->sum();

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

            'harga_beli_satuan' => ['nullable', 'numeric', 'min:0'],
            'harga_jual_umum'   => ['nullable', 'numeric', 'min:0'],
            'harga_otc'         => ['nullable', 'numeric', 'min:0'],

            'depot_id'          => ['required', 'array', 'min:1'],
            'depot_id.*'        => ['nullable', 'exists:depot,id'],

            'stok_depot'        => ['required', 'array', 'min:1'],
            'stok_depot.*'      => ['nullable', 'integer', 'min:0'],

            'tipe_depot'        => ['nullable', 'array'],
            'tipe_depot.*'      => ['nullable', 'exists:tipe_depot,id'],
        ]);

        // ==============================
        // KODE OBAT
        // ==============================
        $kodeObat = $request->input('barcode');

        if (!$kodeObat) {
            DB::transaction(function () use ($request, &$kodeObat) {

                $jenisId    = $request->input('jenis');
                $kategoriId = $request->input('kategori_obat');

                $jenis    = JenisObat::find($jenisId);
                $kategori = KategoriObat::findOrFail($kategoriId);

                $kodeJenis = $jenis
                    ? strtoupper(substr($jenis->nama_jenis_obat, 0, 3))
                    : 'UNK';

                $kodeKategori = strtoupper(substr($kategori->nama_kategori_obat, 0, 3));

                $lastKode = Obat::where('jenis_obat_id', $jenisId)
                    ->where('kategori_obat_id', $kategoriId)
                    ->orderBy('kode_obat', 'desc')
                    ->lockForUpdate()
                    ->value('kode_obat');

                if ($lastKode) {
                    $lastNumber = (int) substr($lastKode, -3);
                    $newNumber  = $lastNumber + 1;
                } else {
                    $newNumber = 1;
                }

                $urutan  = str_pad($newNumber, 3, '0', STR_PAD_LEFT);
                $kodeObat = "{$kodeJenis}-{$kodeKategori}-{$urutan}";
            });
        }

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
        ]);

        // ==============================
        // SIMPAN RELASI DEPOT (TIDAK DIUBAH)
        // ==============================
        $depotIds     = $request->input('depot_id', []);
        $tipeDepotIds = $request->input('tipe_depot', []);
        $stokDepot    = $request->input('stok_depot', []);

        $attachDepotIds = [];

        foreach ($depotIds as $index => $depId) {
            if (!$depId) continue;

            $attachDepotIds[] = $depId;
            $depot = Depot::find($depId);
            if (!$depot) continue;

            $tipeId = $tipeDepotIds[$index] ?? null;
            $stok   = (int) ($stokDepot[$index] ?? 0);

            if ($tipeId) {
                $depot->tipe_depot_id = $tipeId;
            }

            $depot->jumlah_stok_depot = $stok;
            $depot->save();
        }

        if (!empty($attachDepotIds)) {
            $obat->depotObat()->sync($attachDepotIds);
        }

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

            'harga_beli_satuan' => ['nullable'],
            'harga_jual_umum'   => ['nullable'],
            'harga_otc'         => ['nullable'],

            // array depot
            'depot_id'          => ['required', 'array', 'min:1'],
            'depot_id.*'        => ['nullable', 'exists:depot,id'],

            'stok_depot'        => ['required', 'array', 'min:1'],
            'stok_depot.*'      => ['nullable', 'integer', 'min:0'],

            'tipe_depot'        => ['nullable', 'array'],
            'tipe_depot.*'      => ['nullable', 'exists:tipe_depot,id'],
        ]);

        $depotIds  = (array) $request->input('depot_id', []);
        $tipeDepot = (array) $request->input('tipe_depot', []);
        $stokDepot = (array) $request->input('stok_depot', []);

        // filter depot yg bener2 ada isi id (buat sync pivot)
        $validDepotIds = array_values(array_filter($depotIds, fn($id) => !empty($id)));

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

            // ==============================
            // UPDATE TIPE DEPOT DI TABEL DEPOT
            // (berdasarkan pasangan depot_id[] & tipe_depot[] yang sejajar index)
            // ==============================
            foreach ($depotIds as $index => $depotId) {
                if (empty($depotId)) continue;

                $tipeId = $tipeDepot[$index] ?? null;
                $stok   = $stokDepot[$index] ?? 0;

                $updateData = [];

                if (!empty($tipeId)) {
                    $updateData['tipe_depot_id'] = (int) $tipeId;
                }

                $updateData['jumlah_stok_depot'] = (int) $stok;

                DB::table('depot')
                    ->where('id', (int) $depotId)
                    ->update($updateData);
            }

            // ==============================
            // SYNC PIVOT depot_obat (tetap seperti semula)
            // ==============================
            if (!empty($validDepotIds)) {
                $obat->depotObat()->sync($validDepotIds);
            } else {
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

    public function export()
    {
        return Excel::download(new ObatExport, 'obat.xlsx');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new ObatImport, $request->file('file'));

        return redirect()->back()->with('success', 'Data obat berhasil diimport!');
    }

    public function printPDF()
    {
        $obats = Obat::with([
            'brandFarmasi',
            'kategoriObat',
            'jenisObat',
            'satuanObat',
            'depotObat.tipeDepot'
        ])->get();

        return Pdf::loadView('farmasi.obat.print-pdf-obat', compact('obats'))
            ->setPaper('a4', 'landscape') // orientasi landscape supaya muat tabel
            ->stream('data-obat.pdf');
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
