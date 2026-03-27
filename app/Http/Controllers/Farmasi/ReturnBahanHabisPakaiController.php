<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\BahanHabisPakai;
use App\Models\BatchBahanHabisPakai;
use App\Models\BatchBahanHabisPakaiDepot;
use App\Models\Depot;
use App\Models\DepotBHP;
use App\Models\PiutangBahanHabisPakai;
use App\Models\ReturnBahanHabisPakai;
use App\Models\ReturnBahanHabisPakaiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class ReturnBahanHabisPakaiController extends Controller
{
    public function index()
    {
        $dataDepot = Depot::all();

        return view('farmasi.return-bahan-habis-pakai.return-bahan-habis-pakai', compact('dataDepot'));
    }

    public function getDataReturnBhp()
    {
        $dataReturn = ReturnBahanHabisPakai::with(['supplier', 'depot'])
            ->latest();

        return DataTables::of($dataReturn)
            ->addIndexColumn()
            ->editColumn('supplier_id', function ($row) {
                return $row->supplier?->nama_supplier ?? '-';
            })
            ->editColumn('depot_id', function ($row) {
                return $row->depot?->nama_depot ?? '-';
            })
            ->addColumn('action', function ($row) {
                return '
                    <div class="flex items-center justify-center gap-2">
                        <button
                            class="btn-detail-return-bhp group inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:from-blue-700 hover:to-indigo-700 hover:shadow-blue-300/50 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2"
                            data-kode-return="' . e($row->kode_return) . '">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-300 group-hover:rotate-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0A9 9 0 1112 3a9 9 0 019 9z" />
                            </svg>
                            Detail
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getDataReturnBhpByKodeReturn($kodeReturn)
    {
        $dataReturn = ReturnBahanHabisPakai::with([
            'supplier',
            'depot',
            'returnBahanHabisPakaiDetail.batchBahanHabisPakai',
            'returnBahanHabisPakaiDetail.bahanHabisPakai',
        ])->where('kode_return', $kodeReturn)->firstOrFail();

        return response()->json([
            'message' => 'Berhasil memunculkan data return bahan habis pakai',
            'data' => $dataReturn,
        ]);
    }

    public function getDataBhp(Request $request)
    {
        $search = $request->get('q');

        $dataBhp = BahanHabisPakai::query()
            ->when($search, function ($query) use ($search) {
                $query->where('nama_barang', 'like', '%' . $search . '%')
                    ->orWhere('kode', 'like', '%' . $search . '%');
            })
            ->get();

        return response()->json($dataBhp);
    }

    public function getDataBatchByBhpId($bhpId)
    {
        $dataBatchBhp = BatchBahanHabisPakai::with('bahanHabisPakai:id,harga_beli_satuan_bhp')
            ->where('bahan_habis_pakai_id', $bhpId)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_batch' => $item->nama_batch ?? '-',
                    'tanggal_kadaluarsa_bahan_habis_pakai' => $item->tanggal_kadaluarsa_bahan_habis_pakai ?? '',
                    'harga_beli_satuan_bhp' => $item->harga_beli_satuan_bhp ?? $item->bahanHabisPakai?->harga_beli_satuan_bhp ?? 0,
                ];
            });

        return response()->json([
            'message' => 'Berhasil memunculkan data batch BHP',
            'data' => $dataBatchBhp,
        ]);
    }

    public function getStokBatchBhpDepot($batchBhpId, $depotId)
    {
        $stok = BatchBahanHabisPakaiDepot::where('batch_bahan_habis_pakai_id', $batchBhpId)
            ->where('depot_id', $depotId)
            ->value('stok_bahan_habis_pakai');

        // dd($stok);

        return response()->json([
            'stok_bahan_habis_pakai' => $stok ?? 0,
        ]);
    }

    public function createDataReturnBhp(Request $request)
    {
        $validated = $request->validate([
            'kode_return' => ['nullable', 'string', 'max:255'],
            'tanggal_return' => ['required', 'date'],
            'supplier_id' => ['required', 'exists:supplier,id'],
            'depot_id' => ['required', 'exists:depot,id'],
            'keterangan' => ['nullable', 'string'],

            'details' => ['required', 'array', 'min:1'],
            'details.*.bahan_habis_pakai_id' => ['required', 'exists:bahan_habis_pakai,id'],
            'details.*.batch_bahan_habis_pakai_id' => ['required', 'exists:batch_bahan_habis_pakai,id'],
            'details.*.qty' => ['required', 'integer', 'min:1'],
            'details.*.harga_beli' => ['required', 'numeric', 'min:0'],
            'details.*.keterangan_item' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();

        try {
            $kodeReturn = !empty($validated['kode_return'])
                ? $validated['kode_return']
                : $this->generateKodeReturnBhp();

            if (ReturnBahanHabisPakai::where('kode_return', $kodeReturn)->exists()) {
                $kodeReturn = $this->generateKodeReturnBhp();
            }

            $returnBhp = ReturnBahanHabisPakai::create([
                'supplier_id'    => $validated['supplier_id'],
                'depot_id'       => $validated['depot_id'],
                'dibuat_oleh'    => Auth::id(),
                'diupdate_oleh'  => null,
                'kode_return'    => $kodeReturn,
                'tanggal_return' => $validated['tanggal_return'],
                'keterangan'     => $validated['keterangan'] ?? null,
                'status_return'  => 'Succeed',
            ]);

            $totalPiutang = 0;

            foreach ($validated['details'] as $index => $item) {
                $bhpId = (int) $item['bahan_habis_pakai_id'];
                $batchBhpId = (int) $item['batch_bahan_habis_pakai_id'];
                $qty = (int) $item['qty'];
                $hargaBeli = (float) $item['harga_beli'];
                $subtotal = $qty * $hargaBeli;

                $batchBhp = BatchBahanHabisPakai::where('id', $batchBhpId)
                    ->where('bahan_habis_pakai_id', $bhpId)
                    ->first();

                if (! $batchBhp) {
                    throw ValidationException::withMessages([
                        "details.$index.batch_bahan_habis_pakai_id" => ['Batch Bahan Habis Pakai tidak sesuai dengan item yang dipilih.']
                    ]);
                }

                $batchBhpDepot = BatchBahanHabisPakaiDepot::where('batch_bahan_habis_pakai_id', $batchBhpId)
                    ->where('depot_id', $validated['depot_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $batchBhpDepot) {
                    throw ValidationException::withMessages([
                        "details.$index.batch_bahan_habis_pakai_id" => ['Data batch BHP pada depot tidak ditemukan.']
                    ]);
                }

                $depotBhp = DepotBHP::where('bahan_habis_pakai_id', $bhpId)
                    ->where('depot_id', $validated['depot_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $depotBhp) {
                    throw ValidationException::withMessages([
                        "details.$index.bahan_habis_pakai_id" => ['Data stok BHP pada depot tidak ditemukan.']
                    ]);
                }

                $bhp = BahanHabisPakai::where('id', $bhpId)
                    ->lockForUpdate()
                    ->first();

                if (! $bhp) {
                    throw ValidationException::withMessages([
                        "details.$index.bahan_habis_pakai_id" => ['Data Bahan Habis Pakai tidak ditemukan.']
                    ]);
                }

                if ((int) $batchBhpDepot->stok_bahan_habis_pakai < $qty) {
                    throw ValidationException::withMessages([
                        "details.$index.qty" => ['Qty return melebihi stok batch BHP pada depot.']
                    ]);
                }

                if ((int) $depotBhp->stok_barang < $qty) {
                    throw ValidationException::withMessages([
                        "details.$index.qty" => ['Qty return melebihi stok BHP pada depot.']
                    ]);
                }

                if ((int) $bhp->stok_barang < $qty) {
                    throw ValidationException::withMessages([
                        "details.$index.qty" => ['Qty return melebihi stok global BHP.']
                    ]);
                }

                ReturnBahanHabisPakaiDetail::create([
                    'return_bahan_habis_pakai_id' => $returnBhp->id,
                    'bahan_habis_pakai_id'        => $bhpId,
                    'batch_bahan_habis_pakai_id'  => $batchBhpId,
                    'qty'                         => $qty,
                    'harga_beli'                  => $hargaBeli,
                    'subtotal'                    => $subtotal,
                ]);

                $batchBhpDepot->update([
                    'stok_bahan_habis_pakai' => ((int) $batchBhpDepot->stok_bahan_habis_pakai) - $qty,
                ]);

                $depotBhp->update([
                    'stok_barang' => ((int) $depotBhp->stok_barang) - $qty,
                ]);

                $bhp->update([
                    'stok_barang' => ((int) $bhp->stok_barang) - $qty,
                ]);

                $totalPiutang += $subtotal;
            }

            PiutangBahanHabisPakai::create([
                'return_bahan_habis_pakai_id' => $returnBhp->id,
                'supplier_id'                 => $validated['supplier_id'],
                'dibuat_oleh'                 => Auth::id(),
                'diupdate_oleh'               => null,
                'metode_pembayaran_id'        => null,
                'tanggal_piutang'             => $validated['tanggal_return'],
                'tanggal_jatuh_tempo'         => null,
                'total_piutang'               => $totalPiutang,
                'tanggal_pelunasan'           => null,
                'no_referensi'                => $kodeReturn,
                'bukti_penerimaan'            => null,
                'status_piutang'              => 'Belum Lunas',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Data return bahan habis pakai berhasil disimpan.',
                'data' => [
                    'id' => $returnBhp->id,
                    'kode_return' => $returnBhp->kode_return,
                    'total_piutang' => $totalPiutang,
                ]
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
                'message' => 'Gagal menyimpan data return bahan habis pakai.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function generateKodeReturnBhp(): string
    {
        $prefix = 'RTBHP';
        $date = now()->format('Ymd');

        $lastData = ReturnBahanHabisPakai::whereDate('created_at', now()->toDateString())
            ->latest('id')
            ->first();

        $lastNumber = 0;

        if ($lastData && preg_match('/(\d+)$/', $lastData->kode_return, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$newNumber}";
    }
}
