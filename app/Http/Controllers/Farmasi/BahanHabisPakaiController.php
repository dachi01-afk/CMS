<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\BahanHabisPakai;
use App\Models\Depot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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
            // VALIDASI (custom response JSON)
            // ==============================
            $validator = Validator::make($request->all(), [
                'kode'          => ['nullable', 'string', 'max:255'],
                'nama_barang'   => ['required', 'string', 'max:255'],

                'brand_farmasi_id' => ['nullable', 'exists:brand_farmasi,id'],
                'jenis_id'         => ['nullable', 'exists:jenis_obat,id'],
                'satuan_id'        => ['required', 'exists:satuan_obat,id'],

                'dosis'                    => ['required', 'numeric', 'min:0'],
                'tanggal_kadaluarsa_bhp'   => ['required', 'date'],
                'no_batch'                 => ['required', 'string', 'max:255'],

                'stok_barang'              => ['required', 'integer', 'min:0'],

                'harga_beli_satuan_bhp'    => ['nullable', 'numeric', 'min:0'],
                'harga_jual_umum_bhp'      => ['nullable', 'numeric', 'min:0'],
                'harga_otc_bhp'            => ['nullable', 'numeric', 'min:0'],

                'depot_id'                 => ['required', 'array', 'min:1'],
                'depot_id.*'               => ['nullable', 'exists:depot,id'],

                'stok_depot'               => ['required', 'array', 'min:1'],
                'stok_depot.*'             => ['nullable', 'integer', 'min:0'],

                'tipe_depot'               => ['nullable', 'array'],
                'tipe_depot.*'             => ['nullable', 'exists:tipe_depot,id'],
            ], [
                // opsional: custom message lebih enak dibaca
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

            // ==============================
            // Hitung total stok dari semua depot
            // ==============================
            $stokDepotCollection = collect($request->input('stok_depot', []))
                ->map(fn($v) => (int) $v)
                ->filter(fn($v) => $v > 0);

            $totalStok = $stokDepotCollection->sum();

            if ($totalStok <= 0) {
                $totalStok = (int) $request->input('stok_barang', 0);
            }

            // contoh error bisnis (optional):
            // jika semua stok 0 dan stok_barang juga 0, boleh dianggap invalid
            if ($totalStok <= 0) {
                return response()->json([
                    'status'  => 400,
                    'message' => 'Stok tidak boleh 0. Isi stok barang atau stok depot.',
                ], 400);
            }

            // ==============================
            // Parse harga
            // ==============================
            $hargaBeli = $parseNumber($request->input('harga_beli_satuan_bhp'));
            $hargaJual = $parseNumber($request->input('harga_jual_umum_bhp'));
            $hargaOtc  = $parseNumber($request->input('harga_otc_bhp'));

            // ==============================
            // Kode: pakai barcode kalau diisi, kalau tidak auto
            // ==============================
            $kodeBHP = $request->input('kode') ?: 'BHP-' . Str::upper(Str::random(8));

            // ==============================
            // SIMPAN (pakai transaksi biar aman)
            // ==============================
            $dataBHP = DB::transaction(function () use ($request, $kodeBHP, $hargaBeli, $hargaJual, $hargaOtc, $totalStok) {

                $dataBHP = BahanHabisPakai::create([
                    'kode'               => $kodeBHP,
                    'brand_farmasi_id'        => $request->input('brand_farmasi_id'),
                    'jenis_id'           => $request->input('jenis_id'),
                    'satuan_id'               => $request->input('satuan_id'),
                    'nama_barang'             => $request->input('nama_barang'),
                    'tanggal_kadaluarsa_bhp'  => $request->input('tanggal_kadaluarsa_bhp'),
                    'no_batch'                => $request->input('no_batch'),
                    'stok_barang'             => $totalStok,
                    'dosis'                   => $request->input('dosis'),
                    'harga_beli_satuan_bhp'   => $hargaBeli,
                    'harga_jual_umum_bhp'     => $hargaJual,
                    'harga_otc_bhp'           => $hargaOtc,
                ]);

                $depotIds     = $request->input('depot_id', []);
                $stokDepot    = $request->input('stok_depot', []);
                $tipeDepotIds = $request->input('tipe_depot', []); // kalau pivot kamu juga simpan tipe (opsional)

                $syncData = [];

                // bikin payload sync untuk pivot: depot_bhp.stok
                foreach ($depotIds as $index => $depId) {
                    if (empty($depId)) continue;

                    $stok = (int) ($stokDepot[$index] ?? 0);

                    // kalau mau skip depot yg stoknya 0, aktifkan ini:
                    // if ($stok <= 0) continue;

                    $syncData[$depId] = [
                        'stok' => $stok,
                    ];
                }

                // simpan ke pivot depot_bhp
                if (!empty($syncData)) {
                    $dataBHP->depotBHP()->sync($syncData); // insert/update stok di depot_bhp
                }

                return $dataBHP;
            });

            $dataBHP->load(
                'brandFarmasi',
                'jenisBHP',
                'satuanBHP',
                'depotBHP.tipeDepot'
            );

            return response()->json([
                'status'  => 200,
                'data'    => $dataBHP,
                'message' => 'Berhasil menambahkan data Bahan Habis Pakai!',
            ], 200);
        } catch (Throwable $e) {
            Log::error('createDataBahanHabisPakai error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $payload = [
                'status'  => 500,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi.',
            ];

            if (config('app.debug')) {
                $payload['debug'] = $e->getMessage(); // hanya local
            }

            return response()->json($payload, 500);
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
        $dataBhp = BahanHabisPakai::findOrFail($id);

        $parseNumber = function ($value) {
            if ($value === null || $value === '') return 0;
            $value = str_replace(['.', ','], ['', '.'], $value);
            return (float) $value;
        };

        // ==============================
        // VALIDASI
        // ==============================
        $validated = $request->validate([
            'kode'                => ['nullable', 'string', 'max:255'],
            'nama_barang'         => ['required', 'string', 'max:255'],

            'brand_farmasi_id'    => ['nullable', 'exists:brand_farmasi,id'],
            'jenis_id'            => ['nullable', 'exists:jenis_obat,id'],
            'satuan_id'           => ['required', 'exists:satuan_obat,id'],

            'dosis'               => ['required', 'numeric', 'min:0'],
            'tanggal_kadaluarsa_bhp' => ['required', 'date'],
            'no_batch'            => ['required', 'string', 'max:255'],

            'stok_barang'         => ['required', 'integer', 'min:0'],

            'harga_beli_satuan_bhp' => ['nullable'],
            'harga_jual_umum_bhp'   => ['nullable'],
            'harga_otc_bhp'         => ['nullable'],

            // array depot
            'depot_id'            => ['nullable', 'array'],
            'depot_id.*'          => ['nullable', 'exists:depot,id'],

            'stok_depot'          => ['nullable', 'array'],
            'stok_depot.*'        => ['nullable', 'integer', 'min:0'],

            'tipe_depot'          => ['nullable', 'array'],
            'tipe_depot.*'        => ['nullable', 'exists:tipe_depot,id'],
        ]);

        $depotIds  = (array) $request->input('depot_id', []);
        $tipeDepot = (array) $request->input('tipe_depot', []);
        $stokDepot = (array) $request->input('stok_depot', []);

        // ==============================
        // Hitung total stok dari semua depot (pakai stok_depot)
        // ==============================
        $stokDepotCollection = collect($stokDepot)
            ->map(fn($v) => (int) $v)
            ->filter(fn($v) => $v > 0);

        $totalStok = $stokDepotCollection->sum();

        // ✅ fallback HARUS ke stok_barang (bukan stok_obat)
        if ($totalStok <= 0) {
            $totalStok = (int) $request->input('stok_barang', 0);
        }

        // ==============================
        // Parse harga
        // ==============================
        $hargaBeli = $parseNumber($request->input('harga_beli_satuan_bhp'));
        $hargaJual = $parseNumber($request->input('harga_jual_umum_bhp'));
        $hargaOtc  = $parseNumber($request->input('harga_otc_bhp'));

        $kodeBhp = $request->input('kode') ?: $dataBhp->kode;

        DB::beginTransaction();

        try {
            // ==============================
            // UPDATE DATA BHP
            // ==============================
            $dataBhp->update([
                'kode'                   => $kodeBhp,
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
            // ✅ UPDATE PIVOT depot_bhp.stok
            // ==============================
            $syncData = [];

            foreach ($depotIds as $i => $depotId) {
                $depotId = (int) ($depotId ?? 0);
                if ($depotId <= 0) continue;

                $syncData[$depotId] = [
                    'stok' => (int) ($stokDepot[$i] ?? 0),
                ];
            }

            // kalau kosong, detach semua
            if (empty($syncData)) {
                $dataBhp->depotBHP()->detach();
            } else {
                // ini akan INSERT/UPDATE stok pada pivot depot_bhp
                $dataBhp->depotBHP()->sync($syncData);
            }



            DB::commit();

            return response()->json([
                'status'  => 200,
                'data'    => $dataBhp->fresh([
                    'brandFarmasi',
                    'jenisBHP',
                    'satuanBHP',
                    'depotBHP', // kalau kamu mau lihat pivot stok, load ini
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
}
