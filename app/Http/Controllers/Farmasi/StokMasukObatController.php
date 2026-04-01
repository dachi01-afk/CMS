<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\BahanHabisPakai;
use App\Models\BatchBahanHabisPakaiDepot;
use App\Models\DepotBHP;
use App\Models\RestockBahanHabisPakai;
use App\Models\RestockObat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class StokMasukObatController extends Controller
{
    public function index()
    {
        return view('farmasi.stok-masuk-obat.stok-masuk-obat');
    }

    public function getDataStokMasukobat()
    {
        $dataStokMasuk = RestockObat::with([
            'supplier',
            'depot',
            'hutang'
        ])->where('status_restock', 'Pending')->latest();

        return DataTables::of($dataStokMasuk)
            ->addIndexColumn()
            ->editColumn('supplier_id', function ($row) {
                return $row->supplier?->nama_supplier ?? '-';
            })
            ->editColumn('depot_id', function ($row) {
                return $row->depot?->nama_depot ?? '-';
            })
            ->editColumn('status_restock', function ($row) {
                if ($row->status_restock === 'Pending') {
                    return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">Pending</span>';
                }

                if ($row->status_restock === 'Succeed') {
                    return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">Succeed</span>';
                }

                return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300">Canceled</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <div class="flex items-center justify-center gap-2">
                        <button 
                            type="button"
                            class="btn-detail-stok-masuk inline-flex items-center gap-1 px-3 py-2 text-xs font-medium rounded-lg bg-sky-500 hover:bg-sky-600 text-white"
                            data-no-faktur="' . $row->no_faktur . '">
                            <i class="fa-solid fa-eye text-[11px]"></i>
                            Detail
                        </button>

                        <button 
                            type="button"
                            class="btn-konfirmasi-stok-masuk inline-flex items-center gap-1 px-3 py-2 text-xs font-medium rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white"
                            data-id="' . $row->id . '">
                            <i class="fa-solid fa-check text-[11px]"></i>
                            Konfirmasi
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['status_restock', 'action'])
            ->make(true);
    }

    public function getDataDetailStokMasukObat($noFaktur)
    {
        $dataStokMasuk = RestockObat::with([
            'supplier',
            'depot',
            'hutang',
            'restockObatDetail.obat',
            'restockObatDetail.batchObat',
        ])->where('no_faktur', $noFaktur)->firstOrFail();

        $detailItems = $dataStokMasuk->restockObatDetail->map(function ($detail) {
            return [
                'id' => $detail->id,
                'kode_obat' => $detail->obat?->kode_obat ?? '-',
                'nama_obat' => $detail->obat?->nama_obat ?? '-',
                'nama_batch' => $detail->batchObat?->nama_batch ?? '-',
                'tanggal_kadaluarsa_obat' => $detail->batchObat?->tanggal_kadaluarsa_obat ?? null,
                'qty' => $detail->qty ?? 0,
                'harga_beli' => $detail->harga_beli ?? 0,
                'diskon_type' => $detail->diskon_type ?? '-',
                'diskon_value' => $detail->diskon_value ?? 0,
                'diskon_amount' => $detail->diskon_amount ?? 0,
                'total_setelah_diskon' => $detail->total_setelah_diskon ?? 0,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $dataStokMasuk->id,
                'nama_supplier' => $dataStokMasuk->supplier?->nama_supplier ?? '-',
                'nama_depot' => $dataStokMasuk->depot?->nama_depot ?? '-',
                'no_faktur' => $dataStokMasuk->no_faktur ?? '-',
                'tanggal_terima' => $dataStokMasuk->tanggal_terima,
                'tanggal_jatuh_tempo' => $dataStokMasuk->tanggal_jatuh_tempo,
                'total_tagihan' => $dataStokMasuk->total_tagihan ?? 0,
                'status_restock' => $dataStokMasuk->status_restock ?? '-',
                'status_hutang' => $dataStokMasuk->hutang?->status_hutang ?? '-',
                'details' => $detailItems,
            ]
        ]);
    }

    public function konfirmasiStokMasukBahanHabisPakai($id)
    {
        DB::beginTransaction();

        try {
            $dataStokMasuk = RestockBahanHabisPakai::with([
                'restockBahanHabisPakaiDetail',
            ])
                ->lockForUpdate()
                ->findOrFail($id);

            if ($dataStokMasuk->status_restock !== 'Pending') {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Data restock ini sudah dikonfirmasi atau tidak valid.',
                ], 422);
            }

            foreach ($dataStokMasuk->restockBahanHabisPakaiDetail as $detail) {
                $qty = (int) $detail->qty;

                if ($qty <= 0) {
                    continue;
                }

                // 1. Update stok global BHP
                $bhp = BahanHabisPakai::lockForUpdate()->find($detail->bahan_habis_pakai_id);

                if (!$bhp) {
                    throw new \Exception('Data bahan habis pakai tidak ditemukan.');
                }

                $bhp->stok_barang += $qty;
                $bhp->save();

                // 2. Update stok per depot
                $depotBhp = DepotBHP::where('depot_id', $dataStokMasuk->depot_id)
                    ->where('bahan_habis_pakai_id', $detail->bahan_habis_pakai_id)
                    ->lockForUpdate()
                    ->first();

                if (!$depotBhp) {
                    $depotBhp = new DepotBHP();
                    $depotBhp->depot_id = $dataStokMasuk->depot_id;
                    $depotBhp->bahan_habis_pakai_id = $detail->bahan_habis_pakai_id;
                    $depotBhp->stok_barang = 0;
                }

                $depotBhp->stok_barang += $qty;
                $depotBhp->save();

                // 3. Update stok batch per depot
                $batchDepot = BatchBahanHabisPakaiDepot::where('batch_bahan_habis_pakai_id', $detail->batch_bahan_habis_pakai_id)
                    ->where('depot_id', $dataStokMasuk->depot_id)
                    ->lockForUpdate()
                    ->first();

                if (!$batchDepot) {
                    $batchDepot = new BatchBahanHabisPakaiDepot();
                    $batchDepot->batch_bahan_habis_pakai_id = $detail->batch_bahan_habis_pakai_id;
                    $batchDepot->depot_id = $dataStokMasuk->depot_id;
                    $batchDepot->stok_bahan_habis_pakai = 0;
                }

                $batchDepot->stok_bahan_habis_pakai += $qty;
                $batchDepot->save();
            }

            $dataStokMasuk->status_restock = 'Succeed';
            $dataStokMasuk->dikonfirmasi_oleh = Auth::id();
            $dataStokMasuk->dikonfirmasi_jam = now();
            $dataStokMasuk->tanggal_terima = $dataStokMasuk->tanggal_terima ?? now();
            $dataStokMasuk->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stok masuk bahan habis pakai berhasil dikonfirmasi.',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat konfirmasi stok masuk.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function getDataRiwayatStokMasukobat()
    {
        $dataStokMasuk = RestockObat::with([
            'supplier',
            'depot',
            'hutang'
        ])->whereIn('status_restock', ['Succeed', 'Canceled'])->latest();

        return DataTables::of($dataStokMasuk)
            ->addIndexColumn()
            ->editColumn('supplier_id', function ($row) {
                return $row->supplier?->nama_supplier ?? '-';
            })
            ->editColumn('depot_id', function ($row) {
                return $row->depot?->nama_depot ?? '-';
            })
            ->editColumn('status_restock', function ($row) {
                return match ($row->status_restock) {
                    'Succeed'  => '<span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Succeed</span>',
                    'Canceled' => '<span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Canceled</span>',
                    default    => '<span class="px-2 py-1 text-xs font-semibold text-gray-700 bg-gray-100 rounded">-</span>',
                };
            })
            ->addColumn('action', function ($row) {
                return '
                    <div class="flex items-center justify-center gap-2">
                        <button 
                            type="button"
                            class="btn-detail-riwayat-stok-masuk-obat inline-flex items-center gap-1 px-3 py-2 text-xs font-medium rounded-lg bg-sky-500 hover:bg-sky-600 text-white"
                            data-id="' . $row->id . '">
                            <i class="fa-solid fa-eye text-[11px]"></i>
                            Detail
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['status_restock', 'action'])
            ->make(true);
    }
}
