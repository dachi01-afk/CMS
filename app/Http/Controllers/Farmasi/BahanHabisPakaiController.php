<?php

namespace App\Http\Controllers\Farmasi;

use App\Exports\BahanHabisPakaiExport;
use App\Http\Controllers\Controller;
use App\Imports\BahanHabisPakaiImport;
use App\Models\BahanHabisPakai;
use App\Models\Depot;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;
use Yajra\DataTables\DataTables;

class BahanHabisPakaiController extends Controller
{
    public function index()
    {
        return view('farmasi.bahan-habis-pakai.bahan-habis-pakai');
    }

    public function getDataBahanHabisPakai()
    {
        $dataBhp = BahanHabisPakai::with('brandFarmasi', 'jenisBHP', 'satuanBHP', 'depotBHP')
            ->latest()->get();

        return DataTables::of($dataBhp)
            ->addIndexColumn()
            ->addColumn('kode', fn($bhp) => $bhp->kode ?? '-')
            ->addColumn('nama_barang', fn($bhp) => $bhp->nama_barang ?? '-')
            ->addColumn('brand_farmasi', fn($bhp) => $bhp->brandFarmasi->nama_brand ?? '-')
            ->addColumn('stok', fn($bhp) => is_null($bhp->stok_barang) ? 0 : (int) $bhp->stok_barang)
            ->addColumn('harga_jual_umum_bhp', function ($bhp) {
                return 'Rp' . number_format($bhp->harga_jual_umum_bhp ?? 0, 2, ',', '.');
            })
            ->addColumn('harga_beli_satuan_bhp', function ($bhp) {
                return 'Rp' . number_format($bhp->harga_beli_satuan_bhp ?? 0, 2, ',', '.');
            })
            ->addColumn('avg_hpp_bhp', function ($bhp) {
                return 'Rp' . number_format($bhp->avg_hpp_bhp ?? 0, 2, ',', '.');
            })
            ->addColumn('harga_otc_bhp', function ($bhp) {
                return 'Rp' . number_format($bhp->harga_otc_bhp ?? 0, 2, ',', '.');
            })
            ->addColumn('margin_profit_bhp', function ($bhp) {
                $hpp   = $bhp->avg_hpp_bhp ?? 0;
                $jual  = $bhp->harga_jual_umum_bhp ?? 0;

                $margin = $jual - $hpp;

                return 'Rp ' . number_format($margin, 0, ',', '.');
            })
            // AKSI
            ->addColumn('action', function ($bhp) {
                return '
                <div class="flex items-center justify-center gap-2">
                    <button 
                        class="btn-edit-bhp inline-flex items-center justify-center w-8 h-8 rounded-lg 
                               bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-100"
                        data-id="' . $bhp->id . '" 
                        title="Edit">
                        <i class="fa-regular fa-pen-to-square text-xs"></i>
                    </button>

                    <button 
                        class="btn-delete-bhp inline-flex items-center justify-center w-8 h-8 rounded-lg 
                               bg-red-50 text-red-600 hover:bg-red-100 border border-red-100"
                        data-id="' . $bhp->id . '" 
                        title="Hapus">
                        <i class="fa-regular fa-trash-can text-xs"></i>
                    </button>
                </div>
            ';
            })
            ->make(true);
    }

public function createDataBahanHabisPakai(Request $request)
{
    try {
        $parseNumber = function ($value) {
            if ($value === null || $value === '') return 0;
            $value = str_replace(['.', ','], ['', '.'], $value);
            return (float) $value;
        };

        // ==============================
        // VALIDASI
        // ==============================
        $validator = Validator::make($request->all(), [
            'kode'             => ['nullable', 'string', 'max:255', 'unique:bahan_habis_pakai,kode'],
            'nama_barang'      => ['required', 'string', 'max:255'],
            'brand_farmasi_id' => ['nullable', 'exists:brand_farmasi,id'],
            'jenis_id'         => ['nullable', 'exists:jenis_obat,id'],
            'satuan_id'        => ['required', 'exists:satuan_obat,id'],
            'dosis'            => ['required', 'numeric', 'min:0'],
            'tanggal_kadaluarsa_bhp' => ['required', 'date'],
            'no_batch'         => ['required', 'string', 'max:255'],
            'stok_barang'      => ['nullable', 'integer', 'min:0'],
            'harga_beli_satuan_bhp'  => ['nullable', 'numeric', 'min:0'],
            'harga_jual_umum_bhp'    => ['nullable', 'numeric', 'min:0'],
            'harga_otc_bhp'          => ['nullable', 'numeric', 'min:0'],
            'depot_id'         => ['required', 'array', 'min:1'],
            'depot_id.*'       => ['required', 'distinct', 'exists:depot,id'],
            'stok_depot'       => ['required', 'array', 'min:1'],
            'stok_depot.*'     => ['required', 'integer', 'min:0'],
            'tipe_depot'       => ['nullable', 'array'],
            'tipe_depot.*'     => ['nullable', 'exists:tipe_depot,id'],
        ], [
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'satuan_id.required'   => 'Satuan wajib dipilih.',
            'depot_id.required'    => 'Minimal 1 depot harus dipilih.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => 'Validasi gagal. Periksa input yang ditandai.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Hitung total stok untuk main table
        $stokDepotArr = collect($request->input('stok_depot', []))
            ->map(fn($v) => max((int) $v, 0))
            ->all();
        $totalStok = array_sum($stokDepotArr);

        $hargaBeli = $parseNumber($request->input('harga_beli_satuan_bhp'));
        $hargaJual = $parseNumber($request->input('harga_jual_umum_bhp'));
        $hargaOtc  = $parseNumber($request->input('harga_otc_bhp'));

        // ==============================
        // SIMPAN TRANSAKSI
        // ==============================
        $dataBHP = DB::transaction(function () use (
            $request,
            $hargaBeli,
            $hargaJual,
            $hargaOtc,
            $totalStok
        ) {
            // Generate Kode Otomatis jika kosong
            $kodeBHP = $request->input('kode');
            if (!$kodeBHP) {
                $ymd    = now()->format('Ymd');
                $prefix = "BHP-{$ymd}-";
                $lastKode = BahanHabisPakai::where('kode', 'like', $prefix . '%')
                    ->lockForUpdate()
                    ->orderBy('kode', 'desc')
                    ->value('kode');

                $nextNumber = $lastKode ? ((int) substr($lastKode, -4)) + 1 : 1;
                $kodeBHP = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }

            // 1. Simpan ke tabel bahan_habis_pakai
            $bhp = BahanHabisPakai::create([
                'kode'                   => $kodeBHP,
                'brand_farmasi_id'       => $request->input('brand_farmasi_id'),
                'jenis_id'               => $request->input('jenis_id'),
                'satuan_id'              => $request->input('satuan_id'),
                'nama_barang'            => $request->input('nama_barang'),
                'tanggal_kadaluarsa_bhp' => $request->input('tanggal_kadaluarsa_bhp'),
                'no_batch'               => $request->input('no_batch'),
                'stok_barang'            => $totalStok,
                'dosis'                  => $request->input('dosis'),
                'harga_beli_satuan_bhp'  => $hargaBeli,
                'harga_jual_umum_bhp'    => $hargaJual,
                'harga_otc_bhp'          => $hargaOtc,
            ]);

            $depotIds = $request->input('depot_id', []);
            $stokDepotInput = $request->input('stok_depot', []);
            $syncData = [];

            foreach ($depotIds as $i => $depId) {
                $depId = (int) $depId;
                $stokBaru = max((int) ($stokDepotInput[$i] ?? 0), 0);

                // --- LOGIC UPDATE JUMLAH_STOK_DEPOT DI TABEL DEPOT ---
                // Ambil data depot, lock untuk keamanan transaksi
                $depot = Depot::where('id', $depId)->lockForUpdate()->first();
                
                if ($depot) {
                    // Update field jumlah_stok_depot: lama + baru
                    $depot->increment('jumlah_stok_depot', $stokBaru);
                }
                // ------------------------------------------------------

                $syncData[$depId] = ['stok_barang' => $stokBaru];
            }

            // 2. Simpan ke tabel pivot depot_bhp
            $bhp->depotBHP()->sync($syncData);

            return $bhp;
        });

        $dataBHP->load('brandFarmasi', 'jenisBHP', 'satuanBHP', 'depotBHP');

        return response()->json([
            'status'  => 200,
            'data'    => $dataBHP,
            'message' => 'Berhasil menambahkan data Bahan Habis Pakai dan memperbarui stok depot!',
        ], 200);

    } catch (\Throwable $e) {
        Log::error('createDataBahanHabisPakai error', [
            'message' => $e->getMessage(),
            'trace'   => $e->getTraceAsString()
        ]);

        return response()->json([
            'status'  => 500,
            'message' => 'Terjadi kesalahan pada server.',
            'debug'   => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}

    public function getDataBahanHabisPakaiById($id)
    {
        $dataBHP = BahanHabisPakai::with(
            'brandFarmasi',
            'jenisBHP',
            'satuanBHP',
            // 'depot',
            'depotBHP.tipeDepot'
        )->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $dataBHP,
        ]);
    }

    public function updateDataBahanHabisPakai(Request $request, $id)
    {
        $parseNumber = function ($value) {
            if ($value === null || $value === '') return 0;
            $value = str_replace(['.', ','], ['', '.'], $value);
            return (float) $value;
        };

        // ==============================
        // VALIDASI
        // ==============================
        $validated = $request->validate([
            // kode boleh divalidasi agar input aman, tapi TIDAK dipakai untuk update
            'kode'                   => ['nullable', 'string', 'max:255'],

            'nama_barang'            => ['required', 'string', 'max:255'],
            'brand_farmasi_id'       => ['nullable', 'exists:brand_farmasi,id'],
            'jenis_id'               => ['nullable', 'exists:jenis_obat,id'],
            'satuan_id'              => ['required', 'exists:satuan_obat,id'],

            'dosis'                  => ['required', 'numeric', 'min:0'],
            'tanggal_kadaluarsa_bhp' => ['required', 'date'],
            'no_batch'               => ['required', 'string', 'max:255'],

            // stok_barang tidak lagi jadi sumber utama, tapi biarkan validasi kalau form kamu masih kirim
            'stok_barang'            => ['nullable', 'integer', 'min:0'],

            'harga_beli_satuan_bhp'  => ['nullable'],
            'harga_jual_umum_bhp'    => ['nullable'],
            'harga_otc_bhp'          => ['nullable'],

            // array depot
            'depot_id'               => ['nullable', 'array'],
            'depot_id.*'             => ['nullable', 'exists:depot,id'],

            'stok_depot'             => ['nullable', 'array'],
            'stok_depot.*'           => ['nullable', 'integer', 'min:0'],

            'tipe_depot'             => ['nullable', 'array'],
            'tipe_depot.*'           => ['nullable', 'exists:tipe_depot,id'],
        ]);

        $depotIds  = (array) $request->input('depot_id', []);
        $tipeDepot = (array) $request->input('tipe_depot', []);
        $stokDepot = (array) $request->input('stok_depot', []);

        // ==============================
        // Hitung total stok dari semua depot (TERMASUK 0)
        // ==============================
        $stokDepotCollection = collect($stokDepot)
            ->map(fn($v) => max((int) $v, 0));

        $totalStok = (int) $stokDepotCollection->sum();

        // fallback kalau stok_depot tidak dikirim sama sekali (mis. form lama)
        if (empty($stokDepot) && $request->filled('stok_barang')) {
            $totalStok = (int) $request->input('stok_barang', 0);
        }

        // ==============================
        // Parse harga
        // ==============================
        $hargaBeli = $parseNumber($request->input('harga_beli_satuan_bhp'));
        $hargaJual = $parseNumber($request->input('harga_jual_umum_bhp'));
        $hargaOtc  = $parseNumber($request->input('harga_otc_bhp'));

        DB::beginTransaction();

        try {
            // ==============================
            // Ambil data + lock (aman untuk generate kode)
            // ==============================
            $dataBhp = BahanHabisPakai::where('id', $id)
                ->lockForUpdate()
                ->firstOrFail();

            // ==============================
            // KODE: TIDAK DIUBAH.
            // Jika kode di DB kosong => generate BHP-YYYYMMDD-XXXX
            // ==============================
            if ($dataBhp->kode === null || trim($dataBhp->kode) === '') {
                $ymd    = now()->format('Ymd');     // YYYYMMDD
                $prefix = "BHP-{$ymd}-";            // BHP-YYYYMMDD-

                $lastKode = BahanHabisPakai::where('kode', 'like', $prefix . '%')
                    ->lockForUpdate()
                    ->orderBy('kode', 'desc')
                    ->value('kode');

                $nextNumber = 1;
                if ($lastKode) {
                    $lastSeq = (int) substr($lastKode, -4); // ambil XXXX
                    $nextNumber = $lastSeq + 1;
                }

                $dataBhp->kode = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }
            // selain kondisi di atas: kode tetap (abaikan $request->kode)

            // ==============================
            // UPDATE DATA BHP
            // ==============================
            $dataBhp->update([
                // 'kode' tidak ditaruh di sini agar tidak pernah tertimpa input
                'brand_farmasi_id'       => $request->input('brand_farmasi_id'),
                'jenis_id'               => $request->input('jenis_id'),
                'satuan_id'              => $request->input('satuan_id'),

                'nama_barang'            => $request->input('nama_barang'),
                'tanggal_kadaluarsa_bhp' => $request->input('tanggal_kadaluarsa_bhp'),
                'no_batch'               => $request->input('no_batch'),

                'stok_barang'            => $totalStok, // ✅ selalu dari SUM depot (termasuk 0)
                'dosis'                  => $request->input('dosis'),

                'harga_beli_satuan_bhp'  => $hargaBeli,
                'harga_jual_umum_bhp'    => $hargaJual,
                'harga_otc_bhp'          => $hargaOtc,
            ]);

            // kalau kode baru saja digenerate (di-set langsung ke model), pastikan tersimpan
            // (update() di atas tidak menyentuh 'kode')
            if ($dataBhp->isDirty('kode')) {
                $dataBhp->save();
            }

            // ==============================
            // UPDATE TIPE DEPOT DI TABEL DEPOT (tetap seperti punyamu)
            // ==============================
            foreach ($depotIds as $index => $depotId) {
                if (empty($depotId)) continue;

                $tipeId = $tipeDepot[$index] ?? null;
                if (empty($tipeId)) continue;

                DB::table('depot')
                    ->where('id', (int) $depotId)
                    ->update(['tipe_depot_id' => (int) $tipeId]);
            }

            // ==============================
            // UPDATE PIVOT depot_bhp.stok (TERMASUK 0)
            // ==============================
            $syncData = [];

            foreach ($depotIds as $i => $depotId) {
                $depotId = (int) ($depotId ?? 0);
                if ($depotId <= 0) continue;

                $syncData[$depotId] = [
                    'stok' => max((int) ($stokDepot[$i] ?? 0), 0),
                ];
            }

            // kalau depot_id tidak dikirim, biarkan pivot seperti sebelumnya (tidak detach otomatis)
            // tapi kalau kamu memang mau detach saat kosong, ubah sesuai kebutuhan.
            if (!empty($depotIds)) {
                if (empty($syncData)) {
                    $dataBhp->depotBHP()->detach();
                } else {
                    $dataBhp->depotBHP()->sync($syncData);
                }
            }

            DB::commit();

            return response()->json([
                'status'  => 200,
                'data'    => $dataBhp->fresh([
                    'brandFarmasi',
                    'jenisBHP',
                    'satuanBHP',
                    'depotBHP',
                ]),
                'message' => 'Berhasil Mengupdate Data Bahan Habis Pakai!',
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Gagal mengupdate data BHP & depot',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function deleteDataBahanHabisPakai($id)
    {
        $dataBhp = BahanHabisPakai::findOrFail($id);
        $dataBhp->delete();
        return response()->json([
            'status' => 200,
            'data' => $dataBhp,
            'message' => 'Berhasil Menghapus Data BHP!'
        ]);
    }

    public function exportExcelBhp()
    {
        $fileName = 'BHP_' . Carbon::now('Asia/Jakarta')->format('Y-m-d') . '.xlsx';
        return Excel::download(new BahanHabisPakaiExport, $fileName);
    }

    public function printPdfBhp(Request $request)
    {
        $q = trim((string) $request->query('q', '')); // keyword dari search input (opsional)

        $query = BahanHabisPakai::query()
            ->with(['brandFarmasi', 'satuanBHP'])
            ->latest();

        // Jika kamu mau print "semua data" tanpa filter, hapus blok ini.
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('kode', 'like', "%{$q}%")
                    ->orWhere('nama_barang', 'like', "%{$q}%")
                    ->orWhereHas('brandFarmasi', function ($b) use ($q) {
                        $b->where('nama_brand', 'like', "%{$q}%");
                    });
            });
        }

        $rows = $query->get();

        $meta = [
            'title' => 'Laporan Data Stok Bahan Habis Pakai',
            'printed_at' => Carbon::now('Asia/Jakarta')->format('d/m/Y H:i'),
            'keyword' => $q,
            'total' => $rows->count(),
        ];

        $pdf = Pdf::loadView('farmasi.bahan-habis-pakai.print-preview-bahan-habis-pakai', compact('rows', 'meta'))
            ->setPaper('a4', 'landscape');

        $filename = 'PRINT_BHP_' . Carbon::now('Asia/Jakarta')->format('Y-m-d') . '.pdf';

        // stream = buka di tab baru (enak untuk print)
        return $pdf->stream($filename);
    }

    public function importExcelBhp(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:20480'],
        ], [
            'file.required' => 'File excel wajib dipilih.',
            'file.mimes' => 'File harus berformat .xlsx atau .xls',
        ]);

        try {
            $import = new BahanHabisPakaiImport();

            Excel::import($import, $request->file('file'));

            // Kalau ada baris gagal validasi
            if ($import->failures()->isNotEmpty()) {
                $first = $import->failures()->first();
                return back()->with('error', 'Ada data yang gagal diimport. Baris: ' . $first->row());
            }

            return back()->with('success', 'Import BHP berhasil.');
        } catch (Throwable $e) {
            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }
}
