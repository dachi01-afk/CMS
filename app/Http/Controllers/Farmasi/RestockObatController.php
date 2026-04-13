<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\BatchObat;
use App\Models\Depot;
use App\Models\HutangObat;
use App\Models\Obat;
use App\Models\RestockObat;
use App\Models\RestockObatDetail;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class RestockObatController extends Controller
{
    public function index()
    {
        $dataSupplier = Supplier::get();
        $dataDepot = Depot::get();
        $dataObat = Obat::get();

        return view('farmasi.restock-obat.restock-obat', [
            'dataSupplier' => $dataSupplier,
            'dataDepot' => $dataDepot,
            'dataObat' => $dataObat,
        ]);
    }

    public function getDataRestockObat()
    {
        $dataRestockObat = RestockObat::with(['supplier', 'depot'])->where('status_restock', 'Pending')->latest();

        return DataTables::of($dataRestockObat)
            ->addIndexColumn()
            ->editColumn('supplier_id', function ($row) {
                return $row->supplier?->nama_supplier ?? '-';
            })
            ->editColumn('depot_id', function ($row) {
                return $row->depot?->nama_depot ?? '-';
            })
            ->editColumn('status_restock', function ($row) {
                return match ($row->status_restock) {
                    'Pending'  => '<span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Pending</span>',
                    default    => '<span class="px-2 py-1 text-xs font-semibold text-gray-700 bg-gray-100 rounded">-</span>',
                };
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
                            <button 
                                type="button"
                                class="button-detail-restock-obat px-4 py-2 bg-blue-500 text-white rounded-lg text-sm hover:bg-blue-600"
                                data-id="' . $row->id . '">
                                Detail
                            </button>

                            <button 
                                type="button"
                                class="button-cancel-restock-obat px-4 py-2 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600"
                                data-no-faktur="' . $row->no_faktur . '">
                                Cancel
                            </button>
                        </div>
                        ';
            })
            ->rawColumns(['action', 'status_restock'])
            ->make(true);
    }

    public function getDataBatchObatByObatId($obatId)
    {
        $dataBatchObat = BatchObat::where('obat_id', $obatId)
            ->orderBy('tanggal_kadaluarsa_obat', 'asc')
            ->get()
            ->map(function ($batch) {
                return [
                    'id' => (string) $batch->id,
                    'value' => (string) $batch->id,
                    'nama_batch' => $batch->nama_batch,
                    'format_tanggal_kadaluarsa_obat' => $batch->format_tanggal_kadaluarsa_obat,
                    'tanggal_kadaluarsa_obat' => \Carbon\Carbon::parse($batch->tanggal_kadaluarsa_obat)->format('Y-m-d'),
                    'text' => $batch->nama_batch . ' - EXP ' . $batch->format_tanggal_kadaluarsa_obat,
                ];
            })
            ->values();

        return response()->json($dataBatchObat);
    }

    public function createDataRestockObat(Request $request)
    {
        $auth = Auth::id();

        $validator = Validator::make($request->all(), [
            'supplier_id' => ['required', 'exists:supplier,id'],
            'depot_id' => ['required', 'exists:depot,id'],
            'no_faktur' => ['required', 'string', 'max:255', 'unique:restock_obat,no_faktur'],
            'tanggal_jatuh_tempo' => ['required', 'date', 'after_or_equal:tanggal_terima'],
            'total_tagihan' => ['required', 'numeric', 'min:0'],

            'details' => ['required', 'array', 'min:1'],
            'details.*.obat_id' => ['required', 'exists:obat,id'],
            'details.*.batch_obat_id' => ['nullable', 'exists:batch_obat,id'],
            'details.*.batch_nama' => ['nullable', 'string', 'max:255'],
            'details.*.tanggal_kadaluarsa_obat' => ['required', 'date'],
            'details.*.qty' => ['required', 'integer', 'min:1'],
            'details.*.harga_beli' => ['required', 'numeric', 'min:0'],
            'details.*.subtotal' => ['required', 'numeric', 'min:0'],
            'details.*.diskon_type' => ['nullable', 'in:nominal,persen'],
            'details.*.diskon_value' => ['nullable', 'numeric', 'min:0'],
            'details.*.diskon_amount' => ['nullable', 'numeric', 'min:0'],
            'details.*.total_setelah_diskon' => ['required', 'numeric', 'min:0'],
        ], [
            'supplier_id.required' => 'Supplier wajib dipilih.',
            'depot_id.required' => 'Depot wajib dipilih.',
            'no_faktur.required' => 'No faktur wajib diisi.',
            'no_faktur.unique' => 'No faktur sudah digunakan.',
            'tanggal_jatuh_tempo.required' => 'Tanggal jatuh tempo wajib diisi.',
            'tanggal_jatuh_tempo.after_or_equal' => 'Tanggal jatuh tempo harus sama atau setelah tanggal terima.',
            'details.required' => 'Minimal harus ada 1 detail obat.',
            'details.min' => 'Minimal harus ada 1 detail obat.',
            'details.*.obat_id.required' => 'Obat wajib dipilih.',
            'details.*.tanggal_kadaluarsa_obat.required' => 'Tanggal kadaluarsa wajib diisi.',
            'details.*.qty.required' => 'Qty wajib diisi.',
            'details.*.qty.min' => 'Qty minimal 1.',
        ]);

        $validator->after(function ($validator) use ($request) {
            foreach ($request->details ?? [] as $index => $detail) {
                $batchObatId = $detail['batch_obat_id'] ?? null;
                $batchNama = trim($detail['batch_nama'] ?? '');
                $obatId = $detail['obat_id'] ?? null;

                if (empty($batchObatId) && empty($batchNama)) {
                    $validator->errors()->add("details.$index.batch_nama", 'Batch obat wajib dipilih atau diketik.');
                }

                if (!empty($batchObatId) && !empty($obatId)) {
                    $batch = BatchObat::where('id', $batchObatId)
                        ->where('obat_id', $obatId)
                        ->first();

                    if (!$batch) {
                        $validator->errors()->add("details.$index.batch_obat_id", 'Batch tidak sesuai dengan obat yang dipilih.');
                    }
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $restockObat = RestockObat::create([
                'supplier_id' => $request->supplier_id,
                'depot_id' => $request->depot_id,
                'no_faktur' => $request->no_faktur,
                'tanggal_terima' => null,
                'total_tagihan' => $request->total_tagihan,
                'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
                'status_restock' => 'Pending',
                'dibuat_oleh' => $auth,
                'dikonfirmasi_oleh' => null,
                'dikonfirmasi_jam' => null,
            ]);

            foreach ($request->details as $detail) {
                $batchObatId = $detail['batch_obat_id'] ?? null;
                $batchNama = trim($detail['batch_nama'] ?? '');
                $tanggalKadaluarsa = $detail['tanggal_kadaluarsa_obat'];
                $isBatchBaru = false;

                if ($batchObatId) {
                    $batchObat = BatchObat::where('id', $batchObatId)
                        ->where('obat_id', $detail['obat_id'])
                        ->firstOrFail();
                } else {
                    $existingBatch = BatchObat::where('obat_id', $detail['obat_id'])
                        ->whereRaw('LOWER(nama_batch) = ?', [strtolower($batchNama)])
                        ->first();

                    if ($existingBatch) {
                        $batchObat = $existingBatch;
                    } else {
                        $batchObat = BatchObat::create([
                            'obat_id' => $detail['obat_id'],
                            'nama_batch' => $batchNama,
                            'tanggal_kadaluarsa_obat' => $tanggalKadaluarsa,
                        ]);

                        $isBatchBaru = true;
                    }
                }

                RestockObatDetail::create([
                    'restock_obat_id' => $restockObat->id,
                    'obat_id' => $detail['obat_id'],
                    'batch_obat_id' => $batchObat->id,
                    'qty' => $detail['qty'],
                    'harga_beli' => $detail['harga_beli'],
                    'subtotal' => $detail['subtotal'],
                    'diskon_type' => $detail['diskon_type'] ?? null,
                    'diskon_value' => $detail['diskon_value'] ?? 0,
                    'diskon_amount' => $detail['diskon_amount'] ?? 0,
                    'total_setelah_diskon' => $detail['total_setelah_diskon'],
                    'is_batch_baru' => $isBatchBaru,
                ]);
            }

            HutangObat::create([
                'restock_obat_id' => $restockObat->id,
                'supplier_id' => $request->supplier_id,
                'dibuat_oleh' => $auth,
                'diupdate_oleh' => null,
                'metode_pembayaran_id' => null,
                'tanggal_hutang' => now()->toDateString(),
                'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
                'total_hutang' => $request->total_tagihan,
                'tanggal_pelunasan' => null,
                'no_faktur' => $request->no_faktur,
                'status_hutang' => 'Belum Lunas',
                'bukti_pembayaran' => null,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Data restock obat berhasil disimpan.',
                'data' => $restockObat,
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data restock obat.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function getDetailRestockObat($id)
    {
        $restockObat = RestockObat::with([
            'supplier',
            'depot',
            'restockObatDetail.obat',
            'restockObatDetail.batchObat',
        ])->find($id);

        if (!$restockObat) {
            return response()->json([
                'message' => 'Data restock obat tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Detail data restock obat berhasil diambil.',
            'data' => $restockObat,
        ], 200);
    }

    public function getDataRiwayatRestockObat()
    {
        $dataRestockObat = RestockObat::with(['supplier', 'depot'])->whereIn('status_restock', ['Succeed', 'Canceled'])->latest();

        return DataTables::of($dataRestockObat)
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
                <button 
                    type="button"
                    class="button-detail-riwayat-stok-masuk-obat px-4 py-2 bg-blue-500 text-white rounded-lg text-sm hover:bg-blue-600"
                    data-id="' . $row->id . '">
                    Detail
                </button>
            ';
            })
            ->rawColumns(['action', 'status_restock'])
            ->make(true);
    }

    public function cancelRestockObat($noFaktur)
    {
        $restockObat = RestockObat::with('hutang', 'restockObatDetail.batchObat')
            ->where('no_faktur', $noFaktur)
            ->firstOrFail();

        if ($restockObat->status_restock !== 'Pending') {
            return response()->json([
                'message' => 'Hanya data dengan status Pending yang bisa dibatalkan.'
            ], 422);
        }

        if ($restockObat->hutang && $restockObat->hutang->status_hutang === 'Sudah Lunas') {
            return response()->json([
                'message' => 'Data restock obat tidak bisa dibatalkan karena hutang sudah lunas.'
            ], 422);
        }

        foreach ($restockObat->restockObatDetail as $dataDetail) {
            if ($dataDetail->is_batch_baru == true) {
                $dataDetail->batchObat->delete();
            }
        }

        $restockObat->update([
            'status_restock' => 'Canceled',
            'dikonfirmasi_oleh' => Auth::id(),
            'dikonfirmasi_jam' => now(),
        ]);

        if ($restockObat->hutang) {
            $restockObat->hutang->update([
                'status_hutang' => 'Dibatalkan',
                'diupdate_oleh' => Auth::id(),
            ]);
        }

        return response()->json([
            'message' => 'Data restock obat berhasil dibatalkan.'
        ]);
    }
}
