<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\Obat;
use App\Models\Pasien;
use App\Models\PenjualanObat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class OrderObatController extends Controller
{
    public function getDataPenjualanObat(Request $request)
    {
        $query = PenjualanObat::with(['pasien', 'penjualanObatDetail.obat'])
            ->select('penjualan_obat.*')
            ->latest();

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('nama_pasien', function ($row) {
                return $row->pasien->nama_pasien ?? '-';
            })
            ->addColumn('jumlah_item', function ($row) {
                return $row->penjualanObatDetail->count();
            })
            ->addColumn('action', function ($row) {
                $urlOrderObat = route('get.data.detail.order.obat', [
                    'kodeTransaksi' => $row->kode_transaksi
                ]);

                return '
        <div class="flex items-center justify-center gap-2">
            <button
                type="button"
                class="btn-detail-order-obat inline-flex items-center justify-center w-8 h-8 rounded-lg bg-sky-50 text-sky-600 hover:bg-sky-100 border border-sky-100"
                data-kode-transaksi="' . $row->kode_transaksi . '"
                data-url-detail-order-obat="' . $urlOrderObat . '">
                <i class="fa-solid fa-eye text-xs"></i>
            </button>

            <button
                type="button"
                title="Edit Order"
                class="btn-edit-order inline-flex h-9 w-9 items-center justify-center rounded-lg border border-amber-200 bg-amber-50 text-amber-600 shadow-sm transition hover:bg-amber-100 hover:text-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-300 active:scale-95"
                data-id="' . $row->id . '">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487a2.25 2.25 0 1 1 3.182 3.182L8.25 19.463 4.5 20.25l.787-3.75L16.862 4.487z" />
                </svg>
            </button>

            <button
                type="button"
                title="Hapus Order"
                class="btn-delete-order inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 border border-red-100"
                data-id="' . $row->id . '">
                <i class="fa-regular fa-trash-can text-xs"></i>
            </button>
        </div>
    ';
            })
            ->filterColumn('nama_pasien', function ($query, $keyword) {
                $query->whereHas('pasien', function ($q) use ($keyword) {
                    $q->where('nama_pasien', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function getDataDetailOrderObat($kodeTransaksi)
    {
        $dataOrderObat = PenjualanObat::with([
            'pasien:id,nama_pasien,no_emr',
            'metodePembayaran:id,nama_metode',
            'penjualanObatDetail.obat:id,nama_obat'
        ])
            ->where('kode_transaksi', $kodeTransaksi)
            ->firstOrFail();

        $details = $dataOrderObat->penjualanObatDetail;

        $totalItem = (int) $details->sum('jumlah');
        $subtotal = (float) $details->sum('sub_total');
        $diskon = (float) ($dataOrderObat->diskon_nilai ?? 0);
        $grandTotal = (float) ($dataOrderObat->total_setelah_diskon ?? $dataOrderObat->total_tagihan ?? 0);

        return response()->json([
            'dataOrderObat' => [
                'id' => $dataOrderObat->id,
                'kode_transaksi' => $dataOrderObat->kode_transaksi,
                'nama_pasien' => $dataOrderObat->pasien->nama_pasien ?? '-',
                'no_emr' => $dataOrderObat->pasien->no_emr ?? '-',
                'tanggal_transaksi' => $dataOrderObat->tanggal_transaksi,
                'tanggal_transaksi_format' => $dataOrderObat->tanggal_transaksi
                    ? Carbon::parse($dataOrderObat->tanggal_transaksi)->translatedFormat('d F Y H:i')
                    : '-',
                'total_tagihan' => (float) ($dataOrderObat->total_tagihan ?? 0),
                'total_setelah_diskon' => $grandTotal,
                'diskon_tipe' => $dataOrderObat->diskon_tipe,
                'diskon_nilai' => $diskon,
                'jumlah_item' => $totalItem,
                'subtotal' => $subtotal,
                'biaya_lain' => 0,
                'metode_pembayaran' => $dataOrderObat->metodePembayaran->nama_metode ?? '-',
                'status' => $dataOrderObat->status ?? '-',
                'uang_yang_diterima' => (float) ($dataOrderObat->uang_yang_diterima ?? 0),
                'kembalian' => (float) ($dataOrderObat->kembalian ?? 0),
                'created_at' => $dataOrderObat->created_at
                    ? $dataOrderObat->created_at->translatedFormat('d F Y H:i')
                    : '-',
                'updated_at' => $dataOrderObat->updated_at
                    ? $dataOrderObat->updated_at->translatedFormat('d F Y H:i')
                    : '-',
                'bukti_pembayaran' => $dataOrderObat->bukti_pembayaran,
                'bukti_pembayaran_url' => $dataOrderObat->bukti_pembayaran
                    ? asset('storage/' . $dataOrderObat->bukti_pembayaran)
                    : null,
            ],

            'dataDetailOrderObat' => $details->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_obat' => $item->obat->nama_obat ?? '-',
                    'jumlah' => (int) ($item->jumlah ?? 0),
                    'harga_satuan' => (float) ($item->harga_satuan ?? 0),
                    'sub_total' => (float) ($item->sub_total ?? 0),
                    'diskon_tipe' => $item->diskon_tipe,
                    'diskon_nilai' => (float) ($item->diskon_nilai ?? 0),
                    'total_setelah_diskon' => (float) ($item->total_setelah_diskon ?? $item->sub_total ?? 0),

                    // karena di tabel detail yang kamu tunjukkan tidak ada kolom batch/exp
                    'batch' => '-',
                    'expired_at' => '-',
                ];
            })->values(),
        ]);
    }

    public function show($id)
    {
        $data = PenjualanObat::with([
            'pasien',
            'penjualanObatDetail.obat'
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function search(Request $request)
    {
        $query = trim($request->get('query', ''));

        $pasien = Pasien::query()
            ->where(function ($q) use ($query) {
                $q->where('nama_pasien', 'like', "%{$query}%")
                    ->orWhere('no_emr', 'like', "%{$query}%");
            })
            ->get(['id', 'nama_pasien', 'alamat', 'jenis_kelamin', 'no_emr']);

        return response()->json($pasien);
    }

    public function searchObat(Request $request)
    {
        $query = trim($request->get('query', ''));

        $obat = Obat::where('nama_obat', 'like', "%{$query}%")
            ->where('jumlah', '>', 0)
            ->get([
                'id',
                'kode_obat',
                'nama_obat',
                'dosis',
                'jumlah',
                'harga_jual_obat',
                'harga_otc_obat',
                'total_harga'
            ])
            ->map(function ($item) {
                $item->harga_final = $item->harga_jual_obat
                    ?? $item->harga_otc_obat
                    ?? $item->total_harga
                    ?? 0;

                return $item;
            });

        return response()->json($obat);
    }

    public function pesanObat(Request $request)
    {
        $request->validate([
            'pasien_id' => ['required', 'exists:pasien,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.obat_id' => ['required', 'exists:obat,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
        ]);

        DB::beginTransaction();

        try {
            $now = now();
            $kodeTransaksi = 'TRX-' . strtoupper(uniqid());
            $grandTotal = 0;
            $detailRows = [];

            foreach ($request->items as $item) {
                $obat = Obat::lockForUpdate()->findOrFail($item['obat_id']);
                $qty = (int) $item['jumlah'];

                if ($qty > (int) $obat->jumlah) {
                    throw ValidationException::withMessages([
                        'items' => "Stok obat {$obat->nama_obat} tidak mencukupi. Sisa stok: {$obat->jumlah}"
                    ]);
                }

                $hargaSatuan = (float) (
                    $obat->harga_jual_obat
                    ?? $obat->harga_otc_obat
                    ?? $obat->total_harga
                    ?? 0
                );

                $subTotal = $hargaSatuan * $qty;
                $grandTotal += $subTotal;

                $detailRows[] = [
                    'obat_id'      => $obat->id,
                    'jumlah'       => $qty,
                    'harga_satuan' => $hargaSatuan,
                    'sub_total'    => $subTotal,
                ];
            }

            $header = PenjualanObat::create([
                'pasien_id'            => $request->pasien_id,
                'kode_transaksi'       => $kodeTransaksi,
                'metode_pembayaran_id' => null,
                'total_tagihan'        => $grandTotal,
                'diskon_tipe'          => null,
                'diskon_nilai'         => 0,
                'total_setelah_diskon' => $grandTotal,
                'uang_yang_diterima'   => null,
                'kembalian'            => null,
                'tanggal_transaksi'    => $now,
                'bukti_pembayaran'     => null,
                'status'               => 'Belum Bayar',
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);

            foreach ($detailRows as $row) {
                DB::table('penjualan_obat_detail')->insert([
                    'penjualan_obat_id' => $header->id,
                    'obat_id'           => $row['obat_id'],
                    'jumlah'            => $row['jumlah'],
                    'harga_satuan'      => $row['harga_satuan'],
                    'sub_total'         => $row['sub_total'],
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);

                Obat::where('id', $row['obat_id'])->decrement('jumlah', $row['jumlah']);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Order obat berhasil disimpan.',
                'data' => [
                    'id' => $header->id,
                    'kode_transaksi' => $header->kode_transaksi,
                    'total_tagihan' => $header->total_tagihan,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e instanceof ValidationException
                    ? collect($e->errors())->flatten()->first()
                    : 'Gagal menyimpan transaksi: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pasien_id' => ['required', 'exists:pasien,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.obat_id' => ['required', 'exists:obat,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
        ]);

        DB::beginTransaction();

        try {
            $header = PenjualanObat::with('penjualanObatDetail')->findOrFail($id);

            if ($header->status === 'Sudah Bayar') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi yang sudah dibayar tidak dapat diedit.'
                ], 422);
            }

            $now = now();

            foreach ($header->penjualanObatDetail as $detail) {
                Obat::where('id', $detail->obat_id)->increment('jumlah', $detail->jumlah);
            }

            DB::table('penjualan_obat_detail')
                ->where('penjualan_obat_id', $header->id)
                ->delete();

            $grandTotal = 0;

            foreach ($request->items as $item) {
                $obat = Obat::lockForUpdate()->findOrFail($item['obat_id']);
                $qty = (int) $item['jumlah'];

                if ($qty > (int) $obat->jumlah) {
                    throw ValidationException::withMessages([
                        'items' => "Stok obat {$obat->nama_obat} tidak mencukupi. Sisa stok: {$obat->jumlah}"
                    ]);
                }

                $hargaSatuan = (float) (
                    $obat->harga_jual_obat
                    ?? $obat->harga_otc_obat
                    ?? $obat->total_harga
                    ?? 0
                );

                $subTotal = $hargaSatuan * $qty;
                $grandTotal += $subTotal;

                DB::table('penjualan_obat_detail')->insert([
                    'penjualan_obat_id' => $header->id,
                    'obat_id'           => $obat->id,
                    'jumlah'            => $qty,
                    'harga_satuan'      => $hargaSatuan,
                    'sub_total'         => $subTotal,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);

                Obat::where('id', $obat->id)->decrement('jumlah', $qty);
            }

            $header->update([
                'pasien_id'            => $request->pasien_id,
                'total_tagihan'        => $grandTotal,
                'total_setelah_diskon' => $grandTotal,
                'updated_at'           => $now,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Order obat berhasil diperbarui.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e instanceof ValidationException
                    ? collect($e->errors())->flatten()->first()
                    : 'Gagal update transaksi: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $header = PenjualanObat::with('penjualanObatDetail')->findOrFail($id);

            if ($header->status === 'Sudah Bayar') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi yang sudah dibayar tidak dapat dihapus.'
                ], 422);
            }

            foreach ($header->penjualanObatDetail as $detail) {
                Obat::where('id', $detail->obat_id)->increment('jumlah', $detail->jumlah);
            }

            DB::table('penjualan_obat_detail')
                ->where('penjualan_obat_id', $header->id)
                ->delete();

            $header->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getDataRiwayatTransaksiObat()
    {
        $query = PenjualanObat::with(['pasien', 'penjualanObatDetail.obat', 'metodePembayaran'])
            ->where('status', 'Sudah Bayar')
            ->latest();

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('nama_pasien', fn($row) => $row->pasien->nama_pasien ?? '-')
            ->addColumn('nama_obat', fn($row) => $row->penjualanObatDetail->pluck('obat.nama_obat')->implode(', '))
            ->addColumn('jumlah', fn($row) => $row->penjualanObatDetail->pluck('jumlah')->implode(', '))
            ->addColumn('dosis', fn($row) => $row->penjualanObatDetail->pluck('obat.dosis')->filter()->implode(', '))
            ->addColumn('metode_pembayaran', fn($row) => $row->metodePembayaran->nama_metode ?? '-')
            ->editColumn('tanggal_transaksi', function ($row) {
                return $row->tanggal_transaksi
                    ? Carbon::parse($row->tanggal_transaksi)->toIso8601String()
                    : null;
            })
            ->addColumn('bukti_pembayaran', function ($row) {
                if (!$row->bukti_pembayaran) {
                    return '-';
                }

                $url = asset('storage/' . $row->bukti_pembayaran);

                return '
                    <div class="flex flex-col items-center text-center space-y-2">
                        <img src="' . $url . '" alt="Bukti Pembayaran"
                            class="w-24 h-24 object-cover rounded-lg border border-gray-300 shadow-sm hover:scale-105 transition cursor-pointer"
                            onclick="window.open(\'' . $url . '\', \'_blank\')" />
                        <a href="' . $url . '" target="_blank"
                            class="text-sky-600 underline text-sm font-medium">
                            Lihat Bukti Pembayaran
                        </a>
                    </div>
                ';
            })
            ->rawColumns(['bukti_pembayaran'])
            ->toJson();
    }

    public function ajaxResepAktif(Request $request)
    {
        $request->validate([
            'pasien_id' => 'required|exists:pasien,id'
        ]);

        return response()->json([
            'resep_id' => null,
            'kunjungan_id' => null,
            'tanggal_kunjungan' => null,
            'created' => false,
        ]);
    }
}
