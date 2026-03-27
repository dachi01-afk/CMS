<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\BatchObat;
use App\Models\BatchObatDepot;
use App\Models\Depot;
use App\Models\DepotObat;
use App\Models\Obat;
use App\Models\piutang;
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
            'kode_return' => ['nullable', 'string', 'max:255'],
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
            $kodeReturn = !empty($validated['kode_return'])
                ? $validated['kode_return']
                : $this->generateKodeReturnObat();

            if (ReturnObat::where('kode_return', $kodeReturn)->exists()) {
                $kodeReturn = $this->generateKodeReturnObat();
            }

            $details = collect($validated['details'])->map(function ($item) {
                return [
                    'obat_id'       => (int) $item['obat_id'],
                    'batch_obat_id' => (int) $item['batch_obat_id'],
                    'qty'           => (int) $item['qty'],
                    'harga_beli'    => (float) $item['harga_beli'],
                ];
            })->values();

            $totalTagihan = $details->sum(function ($item) {
                return $item['qty'] * $item['harga_beli'];
            });

            $groupedQty = $details->groupBy(function ($item) {
                return $item['obat_id'] . '-' . $item['batch_obat_id'];
            })->map(function ($rows) {
                return $rows->sum('qty');
            });

            $returnObat = ReturnObat::create([
                'supplier_id'    => $validated['supplier_id'],
                'depot_id'       => $validated['depot_id'],
                'dibuat_oleh'    => Auth::id(),
                'diupdate_oleh'  => null,
                'kode_return'    => $kodeReturn,
                'tanggal_return' => $validated['tanggal_return'],
                'keterangan'     => $validated['keterangan'] ?? null,
                'total_tagihan'  => $totalTagihan,
                'status_return'  => 'Succeed',
            ]);

            $totalPiutang = 0;

            foreach ($details as $index => $item) {
                $obatId = $item['obat_id'];
                $batchObatId = $item['batch_obat_id'];
                $qty = $item['qty'];
                $hargaBeli = $item['harga_beli'];
                $subtotal = $qty * $hargaBeli;

                $groupKey = $obatId . '-' . $batchObatId;
                $totalQtyRequestForSameBatch = (int) $groupedQty[$groupKey];

                $batchObat = BatchObat::query()
                    ->where('id', $batchObatId)
                    ->where('obat_id', $obatId)
                    ->first();

                if (!$batchObat) {
                    throw ValidationException::withMessages([
                        "details.$index.batch_obat_id" => ['Batch obat tidak sesuai dengan obat yang dipilih.'],
                    ]);
                }

                $batchSupplierExists = RestockObatDetail::query()
                    ->where('batch_obat_id', $batchObatId)
                    ->where('obat_id', $obatId)
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

                $batchObatDepot = BatchObatDepot::query()
                    ->where('batch_obat_id', $batchObatId)
                    ->where('depot_id', $validated['depot_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$batchObatDepot) {
                    throw ValidationException::withMessages([
                        "details.$index.batch_obat_id" => ['Data batch obat pada depot tidak ditemukan.'],
                    ]);
                }

                $depotObat = DepotObat::query()
                    ->where('obat_id', $obatId)
                    ->where('depot_id', $validated['depot_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$depotObat) {
                    throw ValidationException::withMessages([
                        "details.$index.obat_id" => ['Data stok obat pada depot tidak ditemukan.'],
                    ]);
                }

                $obat = Obat::query()
                    ->where('id', $obatId)
                    ->lockForUpdate()
                    ->first();

                if (!$obat) {
                    throw ValidationException::withMessages([
                        "details.$index.obat_id" => ['Data obat tidak ditemukan.'],
                    ]);
                }

                $stokBatchDepot = (int) $batchObatDepot->stok_obat;
                $stokDepotObat  = (int) $depotObat->stok_obat;
                $stokGlobalObat = (int) $obat->jumlah;

                if ($stokBatchDepot < $totalQtyRequestForSameBatch) {
                    throw ValidationException::withMessages([
                        "details.$index.qty" => [
                            "Qty total untuk batch ini = {$totalQtyRequestForSameBatch}, sedangkan stok batch di depot = {$stokBatchDepot}."
                        ],
                    ]);
                }

                if ($stokDepotObat < $qty) {
                    throw ValidationException::withMessages([
                        "details.$index.qty" => [
                            "Qty return = {$qty}, sedangkan stok obat pada depot = {$stokDepotObat}."
                        ],
                    ]);
                }

                if ($stokGlobalObat < $qty) {
                    throw ValidationException::withMessages([
                        "details.$index.qty" => [
                            "Qty return = {$qty}, sedangkan stok global obat = {$stokGlobalObat}."
                        ],
                    ]);
                }

                ReturnObatDetail::create([
                    'return_obat_id' => $returnObat->id,
                    'obat_id'        => $obatId,
                    'batch_obat_id'  => $batchObatId,
                    'qty'            => $qty,
                    'harga_beli'     => $hargaBeli,
                    'subtotal'       => $subtotal,
                ]);

                $batchObatDepot->update([
                    'stok_obat' => $stokBatchDepot - $qty,
                ]);

                $depotObat->update([
                    'stok_obat' => $stokDepotObat - $qty,
                ]);

                $obat->update([
                    'jumlah' => $stokGlobalObat - $qty,
                ]);

                $totalPiutang += $subtotal;
            }

            PiutangObat::create([
                'return_obat_id'       => $returnObat->id,
                'supplier_id'          => $validated['supplier_id'],
                'dibuat_oleh'          => Auth::id(),
                'diupdate_oleh'        => null,
                'metode_pembayaran_id' => null,
                'tanggal_piutang'      => $validated['tanggal_return'],
                'tanggal_jatuh_tempo'  => null,
                'total_piutang'        => $totalPiutang,
                'tanggal_pelunasan'    => null,
                'no_referensi'         => $kodeReturn,
                'bukti_penerimaan'     => null,
                'status_piutang'       => 'Belum Lunas',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Data return obat berhasil disimpan.',
                'data' => [
                    'id' => $returnObat->id,
                    'kode_return' => $returnObat->kode_return,
                    'total_tagihan' => $totalTagihan,
                    'total_piutang' => $totalPiutang,
                ],
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
                'request_details' => $validated['details'] ?? [],
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Gagal menyimpan data return obat.',
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
