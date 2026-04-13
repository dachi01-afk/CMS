<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\BahanHabisPakai;
use App\Models\BatchBahanHabisPakai;
use App\Models\Depot;
use App\Models\HutangBahanHabisPakai;
use App\Models\RestockBahanHabisPakai;
use App\Models\RestockBahanHabisPakaiDetail;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class RestockBahanHabisPakaiController extends Controller
{
    public function index()
    {
        $dataSupplier = Supplier::get();
        $dataDepot = Depot::get();
        $dataBahanHabisPakai = BahanHabisPakai::get();

        return view('farmasi.restock-bahan-habis-pakai.restock-bahan-habis-pakai', [
            'dataSupplier' => $dataSupplier,
            'dataDepot' => $dataDepot,
            'dataBahanHabisPakai' => $dataBahanHabisPakai,
        ]);
    }

    public function getDataRestockBahanHabisPakai()
    {
        $dataRestockBahanHabisPakai = RestockBahanHabisPakai::with(['supplier', 'depot'])->where('status_restock', 'Pending')->latest();

        return DataTables::of($dataRestockBahanHabisPakai)
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
                                class="button-detail-restock-bhp px-4 py-2 bg-blue-500 text-white rounded-lg text-sm hover:bg-blue-600"
                                data-id="' . $row->id . '">
                                Detail
                            </button>

                            <button 
                                type="button"
                                class="button-cancel-restock-bhp px-4 py-2 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600"
                                data-no-faktur="' . $row->no_faktur . '">
                                Cancel
                            </button>
                        </div>
                        ';
            })
            ->rawColumns(['action', 'status_restock'])
            ->make(true);
    }

    public function getDataBatchBahanHabisPakaiById($bhpId)
    {
        $dataBatchBahanHabisPakai = BatchBahanHabisPakai::where('bahan_habis_pakai_id', $bhpId)
            ->orderBy('tanggal_kadaluarsa_bahan_habis_pakai', 'asc')
            ->get()
            ->map(function ($batch) {
                $expDate = $batch->tanggal_kadaluarsa_bahan_habis_pakai
                    ? \Carbon\Carbon::parse($batch->tanggal_kadaluarsa_bahan_habis_pakai)->format('Y-m-d')
                    : null;

                return [
                    'id' => (string) $batch->id,
                    'value' => (string) $batch->id,
                    'nama_batch' => $batch->nama_batch,
                    'tanggal_kadaluarsa_bahan_habis_pakai' => $expDate,
                    'text' => $batch->nama_batch . ' - EXP ' . $expDate,
                ];
            })
            ->values();

        return response()->json($dataBatchBahanHabisPakai);
    }

    public function createDataRestockRestockBahanHabisPakai(Request $request)
    {
        $auth = Auth::id();

        $validator = Validator::make($request->all(), [
            'supplier_id' => ['required', 'exists:supplier,id'],
            'depot_id' => ['required', 'exists:depot,id'],
            'no_faktur' => ['required', 'string', 'max:255', 'unique:restock_bahan_habis_pakai,no_faktur'],
            'tanggal_jatuh_tempo' => ['required', 'date'],
            'total_tagihan' => ['required', 'numeric', 'min:0'],

            'details' => ['required', 'array', 'min:1'],
            'details.*.bhp_id' => ['required', 'exists:bahan_habis_pakai,id'],
            'details.*.batch_bhp_id' => ['nullable', 'exists:batch_bahan_habis_pakai,id'],
            'details.*.batch_nama' => ['nullable', 'string', 'max:255'],
            'details.*.tanggal_kadaluarsa_bahan_habis_pakai' => ['required', 'date'],
            'details.*.qty' => ['required', 'integer', 'min:1'],
            'details.*.harga_beli' => ['required', 'numeric', 'min:0'],
            'details.*.subtotal' => ['required', 'numeric', 'min:0'],
            'details.*.diskon_type' => ['nullable', 'in:nominal,persen'],
            'details.*.diskon_value' => ['nullable', 'numeric', 'min:0'],
            'details.*.diskon_amount' => ['nullable', 'numeric', 'min:0'],
            'details.*.total_setelah_diskon' => ['required', 'numeric', 'min:0'],
        ], [
            'supplier_id.required' => 'Supplier wajib dipilih.',
            'supplier_id.exists' => 'Supplier tidak ditemukan.',
            'depot_id.required' => 'Depot wajib dipilih.',
            'depot_id.exists' => 'Depot tidak ditemukan.',
            'no_faktur.required' => 'No faktur wajib diisi.',
            'no_faktur.unique' => 'No faktur sudah digunakan.',
            'tanggal_jatuh_tempo.required' => 'Tanggal jatuh tempo wajib diisi.',
            'details.required' => 'Minimal harus ada 1 detail bahan habis pakai.',
            'details.min' => 'Minimal harus ada 1 detail bahan habis pakai.',
            'details.*.bhp_id.required' => 'Bahan Habis Pakai wajib dipilih.',
            'details.*.tanggal_kadaluarsa_bahan_habis_pakai.required' => 'Tanggal kadaluarsa wajib diisi.',
            'details.*.qty.required' => 'Qty wajib diisi.',
            'details.*.qty.min' => 'Qty minimal 1.',
        ]);

        $validator->after(function ($validator) use ($request) {
            foreach ($request->details ?? [] as $index => $detail) {
                $batchBhpId = $detail['batch_bhp_id'] ?? null;
                $batchNama = trim($detail['batch_nama'] ?? '');
                $bhpId = $detail['bhp_id'] ?? null;

                if (empty($batchBhpId) && empty($batchNama)) {
                    $validator->errors()->add("details.$index.batch_nama", 'Batch BHP wajib dipilih atau diketik.');
                }

                if (!empty($batchBhpId) && !empty($bhpId)) {
                    $batch = BatchBahanHabisPakai::where('id', $batchBhpId)
                        ->where('bahan_habis_pakai_id', $bhpId)
                        ->first();

                    if (!$batch) {
                        $validator->errors()->add("details.$index.batch_bhp_id", 'Batch tidak sesuai dengan bahan habis pakai yang dipilih.');
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
            $restockBahanHabisPakai = RestockBahanHabisPakai::create([
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
                $batchBhpId = $detail['batch_bhp_id'] ?? null;
                $batchNama = trim($detail['batch_nama'] ?? '');
                $tanggalKadaluarsa = $detail['tanggal_kadaluarsa_bahan_habis_pakai'];

                if ($batchBhpId) {
                    $batchBhp = BatchBahanHabisPakai::where('id', $batchBhpId)
                        ->where('bahan_habis_pakai_id', $detail['bhp_id'])
                        ->firstOrFail();
                } else {
                    $existingBatch = BatchBahanHabisPakai::where('bahan_habis_pakai_id', $detail['bhp_id'])
                        ->whereRaw('LOWER(nama_batch) = ?', [strtolower($batchNama)])
                        ->first();

                    if ($existingBatch) {
                        $batchBhp = $existingBatch;
                    } else {
                        $batchBhp = BatchBahanHabisPakai::create([
                            'bahan_habis_pakai_id' => $detail['bhp_id'],
                            'nama_batch' => $batchNama,
                            'tanggal_kadaluarsa_bahan_habis_pakai' => $tanggalKadaluarsa,
                        ]);
                    }
                }

                RestockBahanHabisPakaiDetail::create([
                    'restock_bahan_habis_pakai_id' => $restockBahanHabisPakai->id,
                    'bahan_habis_pakai_id' => $detail['bhp_id'],
                    'batch_bahan_habis_pakai_id' => $batchBhp->id,
                    'qty' => (int) $detail['qty'],
                    'harga_beli' => $detail['harga_beli'],
                    'subtotal' => $detail['subtotal'],
                    'diskon_type' => $detail['diskon_type'] ?? null,
                    'diskon_value' => $detail['diskon_value'] ?? 0,
                    'diskon_amount' => $detail['diskon_amount'] ?? 0,
                    'total_setelah_diskon' => $detail['total_setelah_diskon'],
                ]);

                // PENTING:
                // JANGAN update stok ke batch_bahan_habis_pakai_depot di sini.
                // Stok baru boleh masuk saat konfirmasi.
            }

            HutangBahanHabisPakai::create([
                'restock_bahan_habis_pakai_id' => $restockBahanHabisPakai->id,
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
                'message' => 'Data restock bahan habis pakai berhasil disimpan.',
                'data' => $restockBahanHabisPakai,
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data restock bahan habis pakai.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    protected function dibuatOleh($data)
    {
        return $data->dibuatOleh?->nama_role ?? '-';
    }

    protected function dikonfirmasiOleh($data)
    {
        return $data->dikonfirmasiOleh?->nama_role ?? '-';
    }

    public function getDetailRestockBahanHabisPakai($id)
    {
        $restockBhp = RestockBahanHabisPakai::with([
            'supplier',
            'depot',
            'restockBahanHabisPakaiDetail.bahanHabisPakai',
            'restockBahanHabisPakaiDetail.batchBahanHabisPakai',
            'dibuatOleh.kasir',
            'dibuatOleh.superAdmin',
            'dikonfirmasiOleh.kasir',
            'dikonfirmasiOleh.superAdmin',
        ])->find($id);

        $dibuatOleh = $this->dibuatOleh($restockBhp);

        $dikonfirmasiOleh = $this->dikonfirmasiOleh($restockBhp);

        if (!$restockBhp) {
            return response()->json([
                'message' => 'Data restock bahan habis pakai tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Detail data restock bahan habis pakai berhasil diambil.',
            'data' => $restockBhp,
            'dibuatOleh' => $dibuatOleh,
            'dikonfirmasiOleh' => $dikonfirmasiOleh,
        ], 200);
    }

    public function getDataRiwayatRestockBahanHabisPakai()
    {
        $dataRestockBahanHabisPakai = RestockBahanHabisPakai::with(['supplier', 'depot'])->whereIn('status_restock', ['Succeed', 'Canceled'])->latest();

        return DataTables::of($dataRestockBahanHabisPakai)
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
                    class="button-detail-riwayat-stok-masuk-bhp px-4 py-2 bg-blue-500 text-white rounded-lg text-sm hover:bg-blue-600"
                    data-id="' . $row->id . '">
                    Detail
                </button>
            ';
            })
            ->rawColumns(['action', 'status_restock'])
            ->make(true);
    }

    public function cancelRestockBahanHabisPakai($noFaktur)
    {
        $restockBahanHabisPakai = RestockBahanHabisPakai::with('hutang')
            ->where('no_faktur', $noFaktur)
            ->firstOrFail();

        if ($restockBahanHabisPakai->status_restock !== 'Pending') {
            return response()->json([
                'message' => 'Hanya data dengan status Pending yang bisa dibatalkan.'
            ], 422);
        }

        $restockBahanHabisPakai->update([
            'status_restock' => 'Canceled',
            'dikonfirmasi_oleh' => Auth::id(),
            'dikonfirmasi_jam' => now(),
        ]);

        if ($restockBahanHabisPakai->hutang) {
            $restockBahanHabisPakai->hutang->update([
                'status_hutang' => 'Dibatalkan',
                'diupdate_oleh' => Auth::id(),
            ]);
        }

        return response()->json([
            'message' => 'Data restock Bahan Habis Pakai berhasil dibatalkan.'
        ]);
    }
}
