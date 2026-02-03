<?php

namespace App\Http\Controllers\Farmasi;

use App\Models\Obat;
use App\Models\Depot;
use App\Models\BatchObat;
use App\Models\JenisObat;
use App\Exports\ObatExport;
use App\Imports\ObatImport;
use Illuminate\Support\Str;
use App\Models\KategoriObat;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreObatRequest;
use App\Http\Requests\UpdateObatRequest;
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

    public function createObat(StoreObatRequest $request)
    {
        try {
            $inputs = $request->validated(); // Data bersih dari Form Request

            // 1. Generate Kode Otomatis
            $kodeGenerated = $this->generateKodeObat($inputs);

            // 2. Siapkan data lainnya
            $parsePrice = fn($v) => (float) str_replace(['.', ','], ['', '.'], $v ?? 0);

            $dataToStore = array_merge($inputs, [
                'kode_obat'  => $kodeGenerated,
                'total_stok' => array_sum($inputs['stok_depot'] ?? []),
                'harga_beli' => $parsePrice($request->harga_beli_satuan),
                'harga_jual' => $parsePrice($request->harga_jual_umum),
                'harga_otc'  => $parsePrice($request->harga_otc),
            ]);

            // 3. Simpan via Model (Logika transaksi yang kita buat tadi)
            $obat = Obat::simpanData($dataToStore);

            return response()->json([
                'status'  => 200,
                'data'    => $obat->load('batchObat', 'depotObat'),
                'message' => 'Berhasil menambahkan data!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    // Pisahkan generator kode ke fungsi tersendiri agar rapi
    private function generateKodeObat($inputs)
    {
        // Jika ada barcode manual, gunakan itu
        if (!empty($inputs['barcode'])) {
            return $inputs['barcode'];
        }

        $jenisId    = $inputs['jenis'];
        $kategoriId = $inputs['kategori_obat'];

        $jenis    = \App\Models\JenisObat::find($jenisId);
        $kategori = \App\Models\KategoriObat::findOrFail($kategoriId);

        $kodeJenis    = $jenis ? strtoupper(substr($jenis->nama_jenis_obat, 0, 3)) : 'UNK';
        $kodeKategori = strtoupper(substr($kategori->nama_kategori_obat, 0, 3));

        // Cari kode terakhir berdasarkan kategori & jenis yang sama
        $lastKode = \App\Models\Obat::where('jenis_obat_id', $jenisId)
            ->where('kategori_obat_id', $kategoriId)
            ->orderBy('kode_obat', 'desc')
            ->value('kode_obat');

        // Ambil 3 digit terakhir, lalu tambah 1
        $newNumber = $lastKode ? ((int) substr($lastKode, -3)) + 1 : 1;
        $urutan    = str_pad($newNumber, 3, '0', STR_PAD_LEFT);

        return "{$kodeJenis}-{$kodeKategori}-{$urutan}";
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


    public function updateObat(UpdateObatRequest $request, $id)
    {
        $obat = Obat::findOrFail($id);

        DB::beginTransaction();

        try {
            // Menggunakan method yang kita buat di model
            $obat->updateDataObat($request->validated());

            DB::commit();

            return response()->json([
                'status'  => 200,
                'data'    => $obat->fresh(['brandFarmasi', 'kategoriObat', 'jenisObat', 'satuanObat']),
                'message' => 'Berhasil Mengupdate Data Obat!',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 500,
                'message' => 'Gagal mengupdate data obat',
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
