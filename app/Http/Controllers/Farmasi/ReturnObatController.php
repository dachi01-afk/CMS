<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\BatchObat;
use App\Models\BatchObatDepot;
use App\Models\Depot;
use App\Models\DepotObat;
use App\Models\Obat;
use App\Models\PiutangObat;
use App\Models\RestockObatDetail;
use App\Models\ReturnObat;
use App\Models\ReturnObatDetail;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class ReturnObatController extends Controller
{
    public function index()
    {
        return view('farmasi.return-obat.return-obat');
    }

    public function getDataReturnObat()
    {
        $dataReturn = ReturnObat::with(['supplier', 'depot'])->latest('id');

        return DataTables::of($dataReturn)
            ->addIndexColumn()
            ->editColumn('kode_return', function ($row) {
                return $row->kode_return ?? '-';
            })
            ->editColumn('supplier_id', function ($row) {
                return $row->supplier?->nama_supplier ?? '-';
            })
            ->editColumn('depot_id', function ($row) {
                return $row->depot?->nama_depot ?? '-';
            })
            ->editColumn('tanggal_return', function ($row) {
                return $row->tanggal_return ?? null;
            })
            ->filterColumn('supplier_id', function ($query, $keyword) {
                $query->whereHas('supplier', function ($q) use ($keyword) {
                    $q->where('nama_supplier', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('depot_id', function ($query, $keyword) {
                $query->whereHas('depot', function ($q) use ($keyword) {
                    $q->where('nama_depot', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('action', function ($row) {
                return '
        <div class="flex items-center justify-center gap-2">
            <button type="button"
                class="button-open-modal-detail-return-obat inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-md hover:from-blue-700 hover:to-indigo-700"
                data-kode-return="' . e($row->kode_return) . '">
                <i class="fa-solid fa-eye text-xs"></i>
                Detail
            </button>
        </div>
    ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getDataReturnObatByNoReturn($noReturn)
    {
        $noReturn = urldecode($noReturn);

        $dataReturn = ReturnObat::with([
            'supplier:id,nama_supplier,kontak_person',
            'depot:id,nama_depot',
            'returnObatDetail:id,return_obat_id,obat_id,batch_obat_id,qty,harga_beli,subtotal',
            'returnObatDetail.obat:id,nama_obat,kode_obat',
            'returnObatDetail.batchObat:id,nama_batch,tanggal_kadaluarsa_obat',
            'piutang:id,return_obat_id,total_piutang,status_piutang,no_referensi,tanggal_piutang,tanggal_jatuh_tempo,tanggal_pelunasan',
        ])
            ->where('kode_return', $noReturn)
            ->firstOrFail();

        return response()->json([
            'message' => 'Berhasil memunculkan data return obat',
            'data' => [
                'id' => $dataReturn->id,
                'kode_return' => $dataReturn->kode_return,
                'tanggal_return' => $dataReturn->tanggal_return,
                'keterangan' => $dataReturn->keterangan,
                'status_return' => $dataReturn->status_return,
                'total_tagihan' => (float) $dataReturn->total_tagihan,

                'supplier' => [
                    'id' => $dataReturn->supplier?->id,
                    'nama_supplier' => $dataReturn->supplier?->nama_supplier,
                    'kontak_person' => $dataReturn->supplier?->kontak_person,
                ],

                'depot' => [
                    'id' => $dataReturn->depot?->id,
                    'nama_depot' => $dataReturn->depot?->nama_depot,
                ],

                'piutang_obat' => $dataReturn->piutang ? [
                    'id' => $dataReturn->piutang->id,
                    'total_piutang' => (float) $dataReturn->piutang->total_piutang,
                    'status_piutang' => $dataReturn->piutang->status_piutang,
                    'no_referensi' => $dataReturn->piutang->no_referensi,
                    'tanggal_piutang' => $dataReturn->piutang->tanggal_piutang,
                    'tanggal_jatuh_tempo' => $dataReturn->piutang->tanggal_jatuh_tempo,
                    'tanggal_pelunasan' => $dataReturn->piutang->tanggal_pelunasan,
                ] : null,

                'details' => $dataReturn->returnObatDetail->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'qty' => (int) $detail->qty,
                        'harga_beli' => (float) $detail->harga_beli,
                        'subtotal' => (float) $detail->subtotal,

                        'obat' => [
                            'id' => $detail->obat?->id,
                            'nama_obat' => $detail->obat?->nama_obat,
                            'kode_obat' => $detail->obat?->kode_obat,
                        ],

                        'batch_obat' => [
                            'id' => $detail->batchObat?->id,
                            'nama_batch' => $detail->batchObat?->nama_batch,
                            'tanggal_kadaluarsa_obat' => $detail->batchObat?->tanggal_kadaluarsa_obat,
                        ],
                    ];
                })->values(),
            ],
        ]);
    }

    public function getDataObat(Request $request)
    {
        $search = $request->get('q');

        $dataObat = Obat::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('nama_obat', 'like', '%' . $search . '%')
                        ->orWhere('kode_obat', 'like', '%' . $search . '%');
                });
            })
            ->select('id', 'nama_obat', 'kode_obat')
            ->orderBy('nama_obat')
            ->get();

        return response()->json($dataObat);
    }

    public function getDataBatchByObatId(Request $request, $obatId)
    {
        $depotId = (int) $request->depot_id;
        $supplierId = (int) $request->supplier_id;

        if (!$depotId) {
            return response()->json([
                'pesan' => 'Depot wajib dipilih.',
                'data' => [],
            ], 422);
        }

        if (!$supplierId) {
            return response()->json([
                'pesan' => 'Supplier wajib dipilih.',
                'data' => [],
            ], 422);
        }

        $batchIdsBySupplier = RestockObatDetail::query()
            ->where('obat_id', $obatId)
            ->whereNotNull('batch_obat_id')
            ->whereHas('restockObat', function ($query) use ($supplierId, $depotId) {
                $query->where('supplier_id', $supplierId)
                    ->where('depot_id', $depotId)
                    ->where('status_restock', 'Succeed');
            })
            ->pluck('batch_obat_id')
            ->unique()
            ->values();

        $depotObat = DepotObat::query()
            ->where('obat_id', $obatId)
            ->where('depot_id', $depotId)
            ->first();

        $stokDepotObat = (int) ($depotObat?->stok_obat ?? 0);

        $dataBatchObat = BatchObat::query()
            ->where('obat_id', $obatId)
            ->whereIn('id', $batchIdsBySupplier)
            ->whereHas('batchObatDepot', function ($query) use ($depotId) {
                $query->where('depot_id', $depotId)
                    ->where('stok_obat', '>', 0);
            })
            ->with(['batchObatDepot' => function ($query) use ($depotId) {
                $query->where('depot_id', $depotId);
            }])
            ->orderBy('tanggal_kadaluarsa_obat', 'asc')
            ->get()
            ->map(function ($batch) use ($supplierId, $depotId, $obatId, $stokDepotObat) {
                $batchDepot = $batch->batchObatDepot->first();
                $stokBatchDepot = (int) ($batchDepot?->stok_obat ?? 0);
                $stokTersedia = min($stokBatchDepot, $stokDepotObat);

                return [
                    'id' => (string) $batch->id,
                    'value' => (string) $batch->id,
                    'nama_batch' => $batch->nama_batch ?? '',
                    'text' => trim(
                        ($batch->nama_batch ?? '') .
                            ($batch->tanggal_kadaluarsa_obat ? ' - EXP ' . $batch->tanggal_kadaluarsa_obat : '')
                    ),
                    'tanggal_kadaluarsa_obat' => $batch->tanggal_kadaluarsa_obat ?? '',
                    'stok_tersedia' => $stokTersedia,
                    'harga_beli' => $this->getLatestHargaBeliByBatchSupplierDepot(
                        (int) $batch->id,
                        $supplierId,
                        $depotId,
                        (int) $obatId
                    ),
                ];
            })
            ->filter(function ($item) {
                return (int) $item['stok_tersedia'] > 0;
            })
            ->values();

        return response()->json([
            'pesan' => 'Berhasil memunculkan data batch obat',
            'data' => $dataBatchObat,
        ]);
    }

    public function getStokBatchObatDepot(Request $request, $batchObatId, $depotId)
    {
        $supplierId = (int) $request->supplier_id;
        $obatId = (int) $request->obat_id;

        if (!$supplierId || !$obatId || !$depotId || !$batchObatId) {
            return response()->json([
                'message' => 'Supplier, depot, obat, dan batch wajib dipilih.',
                'batch_obat_id' => (int) $batchObatId,
                'stok_obat' => 0,
                'stok_tersedia' => 0,
                'harga_beli' => 0,
            ], 422);
        }

        $batchObat = BatchObat::query()
            ->where('id', $batchObatId)
            ->where('obat_id', $obatId)
            ->first();

        if (!$batchObat) {
            return response()->json([
                'message' => 'Batch obat tidak sesuai dengan obat yang dipilih.',
                'batch_obat_id' => (int) $batchObatId,
                'stok_obat' => 0,
                'stok_tersedia' => 0,
                'harga_beli' => 0,
            ], 404);
        }

        $restockDetailQuery = RestockObatDetail::query()
            ->where('batch_obat_id', $batchObatId)
            ->where('obat_id', $obatId)
            ->whereHas('restockObat', function ($query) use ($supplierId, $depotId) {
                $query->where('supplier_id', $supplierId)
                    ->where('depot_id', $depotId)
                    ->where('status_restock', 'Succeed');
            });

        if (!(clone $restockDetailQuery)->exists()) {
            return response()->json([
                'message' => 'Batch obat tidak berasal dari supplier dan depot yang dipilih.',
                'batch_obat_id' => (int) $batchObatId,
                'stok_obat' => 0,
                'stok_tersedia' => 0,
                'harga_beli' => 0,
            ], 404);
        }

        $batchObatDepot = BatchObatDepot::query()
            ->where('batch_obat_id', $batchObatId)
            ->where('depot_id', $depotId)
            ->first();

        $depotObat = DepotObat::query()
            ->where('obat_id', $obatId)
            ->where('depot_id', $depotId)
            ->first();

        $stokBatchDepot = (int) ($batchObatDepot?->stok_obat ?? 0);
        $stokDepotObat = (int) ($depotObat?->stok_obat ?? 0);
        $stokTersedia = min($stokBatchDepot, $stokDepotObat);

        $hargaBeli = $this->getLatestHargaBeliByBatchSupplierDepot(
            (int) $batchObatId,
            $supplierId,
            (int) $depotId,
            (int) $obatId
        );

        return response()->json([
            'batch_obat_id' => (int) $batchObatId,
            'obat_id' => (int) $obatId,
            'supplier_id' => (int) $supplierId,
            'depot_id' => (int) $depotId,
            'nama_batch' => $batchObat->nama_batch ?? '',
            'tanggal_kadaluarsa_obat' => $batchObat->tanggal_kadaluarsa_obat ?? null,

            'stok_obat' => $stokTersedia,
            'stok_tersedia' => $stokTersedia,

            'stok_batch_depot' => $stokBatchDepot,
            'stok_depot_obat' => $stokDepotObat,

            'harga_beli' => $hargaBeli,
        ]);
    }

    public function createDataReturnObat(Request $request)
    {
        $validated = $request->validate([
            'tanggal_return' => ['required', 'date'],
            'supplier_id' => ['required', 'exists:supplier,id'],
            'depot_id' => ['required', 'exists:depot,id'],
            'keterangan' => ['nullable', 'string'],

            'details' => ['required', 'array', 'min:1'],
            'details.*.obat_id' => ['required', 'exists:obat,id'],
            'details.*.batch_obat_id' => ['required', 'exists:batch_obat,id'],
            'details.*.qty' => ['required', 'integer', 'min:1'],
            'details.*.harga_beli' => ['required', 'numeric', 'min:0'],
        ]);

        DB::beginTransaction();

        try {
            $kodeReturn = $this->generateKodeReturnObat();

            $details = collect($validated['details'])->map(function ($item) {
                return [
                    'obat_id'       => (int) $item['obat_id'],
                    'batch_obat_id' => (int) $item['batch_obat_id'],
                    'qty'           => (int) $item['qty'],
                    'harga_beli'    => (float) $item['harga_beli'],
                ];
            })->values();

            // Cegah batch yang sama dobel di request
            $duplicates = $details
                ->groupBy(fn($item) => $item['obat_id'] . '-' . $item['batch_obat_id'])
                ->filter(fn($rows) => $rows->count() > 1);

            if ($duplicates->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'details' => ['Batch obat yang sama tidak boleh diinput lebih dari 1 kali.'],
                ]);
            }

            // Validasi batch memang berasal dari restock supplier + depot terpilih
            foreach ($details as $index => $item) {
                $batchObat = BatchObat::query()
                    ->where('id', $item['batch_obat_id'])
                    ->where('obat_id', $item['obat_id'])
                    ->first();

                if (!$batchObat) {
                    throw ValidationException::withMessages([
                        "details.$index.batch_obat_id" => ['Batch obat tidak sesuai dengan obat yang dipilih.'],
                    ]);
                }

                $batchSupplierExists = RestockObatDetail::query()
                    ->where('batch_obat_id', $item['batch_obat_id'])
                    ->where('obat_id', $item['obat_id'])
                    ->whereHas('restockObat', function ($query) use ($validated) {
                        $query->where('supplier_id', $validated['supplier_id'])
                            ->where('depot_id', $validated['depot_id'])
                            ->where('status_restock', 'Succeed');
                    })
                    ->exists();

                if (!$batchSupplierExists) {
                    throw ValidationException::withMessages([
                        "details.$index.batch_obat_id" => ['Batch obat tidak berasal dari supplier dan depot yang dipilih.'],
                    ]);
                }
            }

            $totalTagihan = $details->sum(fn($item) => $item['qty'] * $item['harga_beli']);

            $returnObat = ReturnObat::create([
                'supplier_id'    => $validated['supplier_id'],
                'depot_id'       => $validated['depot_id'],
                'dibuat_oleh'    => Auth::id(),
                'diupdate_oleh'  => null,
                'kode_return'    => $kodeReturn,
                'tanggal_return' => $validated['tanggal_return'],
                'keterangan'     => $validated['keterangan'] ?? null,
                'total_tagihan'  => $totalTagihan,
                'status_return'  => 'Pending',
            ]);

            foreach ($details as $item) {
                ReturnObatDetail::create([
                    'return_obat_id' => $returnObat->id,
                    'obat_id'        => $item['obat_id'],
                    'batch_obat_id'  => $item['batch_obat_id'],
                    'qty'            => $item['qty'],
                    'harga_beli'     => $item['harga_beli'],
                    'subtotal'       => $item['qty'] * $item['harga_beli'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Draft return obat berhasil disimpan.',
                'data' => [
                    'id' => $returnObat->id,
                    'kode_return' => $returnObat->kode_return,
                    'status_return' => $returnObat->status_return,
                ],
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Gagal menyimpan draft return obat.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function konfirmasiReturnObat($kodeReturn)
    {
        DB::beginTransaction();

        try {
            $returnObat = ReturnObat::with('returnObatDetail')
                ->where('kode_return', $kodeReturn)
                ->lockForUpdate()
                ->firstOrFail();

            if ($returnObat->status_return !== 'Pending') {
                throw ValidationException::withMessages([
                    'return_obat' => ['Hanya return dengan status Pending yang bisa dikonfirmasi.'],
                ]);
            }

            $details = $returnObat->returnObatDetail->map(function ($item) {
                return [
                    'obat_id'       => (int) $item->obat_id,
                    'batch_obat_id' => (int) $item->batch_obat_id,
                    'qty'           => (int) $item->qty,
                    'harga_beli'    => (float) $item->harga_beli,
                    'subtotal'      => (float) $item->subtotal,
                ];
            });

            $groupedBatchQty = $details
                ->groupBy(fn($item) => $item['obat_id'] . '-' . $item['batch_obat_id'])
                ->map(fn($rows) => $rows->sum('qty'));

            $groupedObatQty = $details
                ->groupBy('obat_id')
                ->map(fn($rows) => $rows->sum('qty'));

            // VALIDASI STOK BATCH
            foreach ($groupedBatchQty as $key => $totalQtyBatch) {
                [$obatId, $batchObatId] = explode('-', $key);

                $batchObatDepot = BatchObatDepot::query()
                    ->where('batch_obat_id', $batchObatId)
                    ->where('depot_id', $returnObat->depot_id)
                    ->lockForUpdate()
                    ->first();

                if (!$batchObatDepot) {
                    throw ValidationException::withMessages([
                        'batch_obat' => ["Stok batch ID {$batchObatId} pada depot tidak ditemukan."],
                    ]);
                }

                if ((int) $batchObatDepot->stok_obat < (int) $totalQtyBatch) {
                    throw ValidationException::withMessages([
                        'batch_obat' => ["Qty return batch ID {$batchObatId} melebihi stok batch."],
                    ]);
                }
            }

            // VALIDASI STOK DEPOT + GLOBAL PER OBAT
            foreach ($groupedObatQty as $obatId => $totalQtyObat) {
                $depotObat = DepotObat::query()
                    ->where('obat_id', $obatId)
                    ->where('depot_id', $returnObat->depot_id)
                    ->lockForUpdate()
                    ->first();

                if (!$depotObat) {
                    throw ValidationException::withMessages([
                        'depot_obat' => ["Stok depot untuk obat ID {$obatId} tidak ditemukan."],
                    ]);
                }

                if ((int) $depotObat->stok_obat < (int) $totalQtyObat) {
                    throw ValidationException::withMessages([
                        'depot_obat' => ["Qty return obat ID {$obatId} melebihi stok depot."],
                    ]);
                }

                $obat = Obat::query()
                    ->where('id', $obatId)
                    ->lockForUpdate()
                    ->first();

                if (!$obat) {
                    throw ValidationException::withMessages([
                        'obat' => ["Data obat ID {$obatId} tidak ditemukan."],
                    ]);
                }

                if ((int) $obat->jumlah < (int) $totalQtyObat) {
                    throw ValidationException::withMessages([
                        'obat' => ["Qty return obat ID {$obatId} melebihi stok global."],
                    ]);
                }
            }

            // KURANGI STOK BATCH
            foreach ($groupedBatchQty as $key => $totalQtyBatch) {
                [$obatId, $batchObatId] = explode('-', $key);

                $batchObatDepot = BatchObatDepot::query()
                    ->where('batch_obat_id', $batchObatId)
                    ->where('depot_id', $returnObat->depot_id)
                    ->lockForUpdate()
                    ->first();

                $batchObatDepot->update([
                    'stok_obat' => ((int) $batchObatDepot->stok_obat) - (int) $totalQtyBatch,
                ]);
            }

            // KURANGI STOK DEPOT + GLOBAL
            foreach ($groupedObatQty as $obatId => $totalQtyObat) {
                $depotObat = DepotObat::query()
                    ->where('obat_id', $obatId)
                    ->where('depot_id', $returnObat->depot_id)
                    ->lockForUpdate()
                    ->first();

                $obat = Obat::query()
                    ->where('id', $obatId)
                    ->lockForUpdate()
                    ->first();

                $depotObat->update([
                    'stok_obat' => ((int) $depotObat->stok_obat) - (int) $totalQtyObat,
                ]);

                $obat->update([
                    'jumlah' => ((int) $obat->jumlah) - (int) $totalQtyObat,
                ]);
            }

            // BUAT PIUTANG
            PiutangObat::create([
                'return_obat_id'       => $returnObat->id,
                'supplier_id'          => $returnObat->supplier_id,
                'dibuat_oleh'          => Auth::id(),
                'diupdate_oleh'        => null,
                'metode_pembayaran_id' => null,
                'tanggal_piutang'      => $returnObat->tanggal_return,
                'tanggal_jatuh_tempo'  => null,
                'total_piutang'        => $returnObat->total_tagihan,
                'tanggal_pelunasan'    => null,
                'no_referensi'         => $returnObat->kode_return,
                'bukti_penerimaan'     => null,
                'status_piutang'       => 'Belum Lunas',
            ]);

            $returnObat->update([
                'status_return' => 'Succeed',
                'diupdate_oleh' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Return obat berhasil dikonfirmasi.',
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Gagal mengonfirmasi return obat.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function generateKodeReturnObat(): string
    {
        $prefix = 'RTR';
        $date = now()->format('Ymd');

        $lastData = ReturnObat::whereDate('created_at', now()->toDateString())
            ->latest('id')
            ->first();

        $lastNumber = 0;

        if ($lastData && preg_match('/(\d+)$/', $lastData->kode_return, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$newNumber}";
    }

    private function getLatestHargaBeliByBatchId(int $batchObatId): float
    {
        return (float) (
            RestockObatDetail::query()
            ->where('batch_obat_id', $batchObatId)
            ->where('harga_beli', '>', 0)
            ->latest('id')
            ->value('harga_beli') ?? 0
        );
    }

    public function getDataSupplier(Request $request)
    {
        $q = $request->q;

        $dataSupplier = Supplier::query()
            ->whereHas('restockObat', function ($query) {
                $query->where('status_restock', 'Succeed');
            })
            ->when($q, function ($query) use ($q) {
                $query->where('nama_supplier', 'like', '%' . $q . '%');
            })
            ->select('id', 'nama_supplier', 'kontak_person')
            ->orderBy('nama_supplier')
            ->limit(20)
            ->get();

        return response()->json($dataSupplier);
    }

    public function getDepotBySupplier(Request $request)
    {
        $supplierId = $request->supplier_id;
        $q = $request->q;

        if (!$supplierId) {
            return response()->json([]);
        }

        $dataDepot = Depot::query()
            ->whereHas('restockObat', function ($query) use ($supplierId) {
                $query->where('supplier_id', $supplierId)
                    ->where('status_restock', 'Succeed');
            })
            ->when($q, function ($query) use ($q) {
                $query->where('nama_depot', 'like', '%' . $q . '%');
            })
            ->select('id', 'nama_depot')
            ->orderBy('nama_depot')
            ->limit(20)
            ->get();

        return response()->json($dataDepot);
    }

    public function getObatBySupplierDepot(Request $request)
    {
        $supplierId = (int) $request->supplier_id;
        $depotId = (int) $request->depot_id;
        $q = $request->q;

        if (!$supplierId || !$depotId) {
            return response()->json([]);
        }

        $dataObat = Obat::query()
            ->whereHas('restockObatDetail.restockObat', function ($query) use ($supplierId, $depotId) {
                $query->where('supplier_id', $supplierId)
                    ->where('depot_id', $depotId)
                    ->where('status_restock', 'Succeed');
            })
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama_obat', 'like', '%' . $q . '%')
                        ->orWhere('kode_obat', 'like', '%' . $q . '%');
                });
            })
            ->select('id', 'nama_obat', 'kode_obat')
            ->orderBy('nama_obat')
            ->limit(20)
            ->get();

        return response()->json($dataObat);
    }

    private function getLatestHargaBeliByBatchSupplierDepot(
        int $batchObatId,
        int $supplierId,
        int $depotId,
        ?int $obatId = null
    ): float {
        return (float) (
            RestockObatDetail::query()
            ->where('batch_obat_id', $batchObatId)
            ->when($obatId, function ($query) use ($obatId) {
                $query->where('obat_id', $obatId);
            })
            ->where('harga_beli', '>', 0)
            ->whereHas('restockObat', function ($query) use ($supplierId, $depotId) {
                $query->where('supplier_id', $supplierId)
                    ->where('depot_id', $depotId)
                    ->where('status_restock', 'Succeed');
            })
            ->latest('id')
            ->value('harga_beli') ?? 0
        );
    }
}
