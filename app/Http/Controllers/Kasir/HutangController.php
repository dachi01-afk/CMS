<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\HutangObat;
use App\Models\MetodePembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class HutangController extends Controller
{
    public function index()
    {
        return view('kasir.hutang.hutang');
    }

    public function getDataHutangObat()
    {
        $dataHutang = HutangObat::with([
            'supplier',
        ])->where('status_hutang', 'Belum Lunas')->latest();

        return DataTables::of($dataHutang)
            ->addIndexColumn()
            ->editColumn('supplier_id', function ($row) {
                return $row->supplier?->nama_supplier ?? '-';
            })
            ->filterColumn('supplier_id', function ($query, $keyword) {
                $query->whereHas('supplier', function ($q) use ($keyword) {
                    $q->where('nama_supplier', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('action', function ($row) {
                $buttonDetail = '
                    <button 
                        type="button"
                        class="button-detail-hutang px-4 py-2 bg-blue-500 text-white rounded-lg text-sm hover:bg-blue-600"
                        data-no-faktur="' . e($row->no_faktur) . '">
                        Detail
                    </button>
                ';

                $buttonBayar = '';

                if ($row->status_hutang === "Belum Lunas") {
                    $buttonBayar = '
                        <button 
                            type="button"
                            class="button-bayar-hutang px-4 py-2 bg-emerald-500 text-white rounded-lg text-sm hover:bg-emerald-600"
                            data-no-faktur="' . e($row->no_faktur) . '">
                            Bayar
                        </button>
                    ';
                }

                return '
                    <div class="flex items-center justify-center gap-2">
                        ' . $buttonDetail . '
                        ' . $buttonBayar . '
                    </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getDataDetailHutangObat($noFaktur)
    {
        $detailHutang = HutangObat::with([
            'supplier',
            'metodePembayaran',

            'dibuatOleh.admin',
            'dibuatOleh.dokter',
            'dibuatOleh.pasien',
            'dibuatOleh.farmasi',
            'dibuatOleh.perawat',
            'dibuatOleh.kasir',

            'diupdateOleh.admin',
            'diupdateOleh.dokter',
            'diupdateOleh.pasien',
            'diupdateOleh.farmasi',
            'diupdateOleh.perawat',
            'diupdateOleh.kasir',

            'restockObat',
            'restockObat.supplier',
            'restockObat.depot',
            'restockObat.restockObatDetail.obat',
        ])->where('no_faktur', $noFaktur)->first();

        if (!$detailHutang) {
            return response()->json([
                'message' => 'Data hutang tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Detail data hutang berhasil diambil.',
            'data' => $detailHutang,
        ], 200);
    }

    public function halamanPembayaranHutangObat($noFaktur)
    {
        $dataHutang = HutangObat::with([
            'supplier',
            'metodePembayaran',
            'restockObat',
            'restockObat.depot',
            'restockObat.restockObatDetail.obat',
        ])->where('no_faktur', $noFaktur)->first();

        $dataMetodePembayaran = MetodePembayaran::get();

        if (!$dataHutang) {
            return redirect()->route('kasir.hutang.index')
                ->with('error', 'Data hutang tidak ditemukan.');
        }

        $restockObat = $dataHutang->restockObat;

        $subTotalHutang = $restockObat->restockObatDetail->sum('subtotal') ?? 0;

        $totalDiskon = $restockObat?->restockObatDetail?->sum('diskon_amount') ?? 0;

        $totalHutang = max($subTotalHutang - $totalDiskon, 0);

        return view('kasir.hutang.pembayaran-hutang-obat', compact(
            'dataHutang',
            'dataMetodePembayaran',
            'totalDiskon',
            'totalHutang',
            'subTotalHutang'
        ));
    }

    public function transaksiCash(Request $request, $noFaktur)
    {
        $request->validate([
            'metode_pembayaran_id' => 'required|exists:metode_pembayaran,id',
            'uang_yang_diterima'   => 'required|numeric|min:1',
        ]);

        $hutang = HutangObat::where('no_faktur', $noFaktur)->first();

        if (!$hutang) {
            return response()->json([
                'success' => false,
                'message' => 'Data hutang tidak ditemukan.',
            ], 404);
        }

        if ($hutang->status_hutang === 'Sudah Lunas') {
            return response()->json([
                'success' => false,
                'message' => 'Hutang ini sudah dilunasi.',
            ], 422);
        }

        if (!$hutang->restockObat) {
            return response()->json([
                'success' => false,
                'message' => 'Data restock obat tidak ditemukan.',
            ], 422);
        }

        if ($hutang->restockObat->status_restock !== 'Succeed') {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran tidak dapat diproses karena barang restock belum masuk.',
            ], 422);
        }

        $metodePembayaran = MetodePembayaran::find($request->metode_pembayaran_id);

        if (!$metodePembayaran || stripos($metodePembayaran->nama_metode, 'cash') === false) {
            return response()->json([
                'success' => false,
                'message' => 'Metode pembayaran yang dipilih bukan cash.',
            ], 422);
        }

        $totalHutang = (float) $hutang->total_hutang;
        $uangDiterima = (float) $request->uang_yang_diterima;

        if ($uangDiterima < $totalHutang) {
            return response()->json([
                'success' => false,
                'message' => 'Nominal uang yang diterima belum cukup.',
            ], 422);
        }

        $hutang->update([
            'metode_pembayaran_id' => $request->metode_pembayaran_id,
            'tanggal_pelunasan'    => now()->format('Y-m-d'),
            'bukti_pembayaran'     => null,
            'status_hutang'        => 'Sudah Lunas',
            'diupdate_oleh'        => Auth::id(),
        ]);

        return response()->json([
            'success'   => true,
            'message'   => 'Pembayaran hutang cash berhasil diproses.',
            'kembalian' => $uangDiterima - $totalHutang,
        ]);
    }

    public function transaksiTransfer(Request $request, $noFaktur)
    {
        $request->validate([
            'metode_pembayaran_id' => 'required|exists:metode_pembayaran,id',
            'bukti_pembayaran'     => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'metode_pembayaran_id.required' => 'Metode pembayaran wajib dipilih.',
            'metode_pembayaran_id.exists'   => 'Metode pembayaran tidak valid.',
            'bukti_pembayaran.required'     => 'Bukti pembayaran wajib diupload.',
            'bukti_pembayaran.image'        => 'File bukti pembayaran harus berupa gambar.',
            'bukti_pembayaran.mimes'        => 'Format bukti pembayaran harus JPG, JPEG, atau PNG.',
            'bukti_pembayaran.max'          => 'Ukuran bukti pembayaran maksimal 2 MB.',
        ]);

        $hutang = HutangObat::where('no_faktur', $noFaktur)->first();

        if (!$hutang) {
            return response()->json([
                'success' => false,
                'message' => 'Data hutang tidak ditemukan.',
            ], 404);
        }

        if ($hutang->status_hutang === 'Sudah Lunas') {
            return response()->json([
                'success' => false,
                'message' => 'Hutang ini sudah dilunasi.',
            ], 422);
        }

        if (!$hutang->restockObat) {
            return response()->json([
                'success' => false,
                'message' => 'Data restock obat tidak ditemukan.',
            ], 422);
        }

        if ($hutang->restockObat->status_restock !== 'Succeed') {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran tidak dapat diproses karena barang restock belum masuk.',
            ], 422);
        }

        $metodePembayaran = MetodePembayaran::find($request->metode_pembayaran_id);

        if (!$metodePembayaran || stripos($metodePembayaran->nama_metode, 'transfer') === false) {
            return response()->json([
                'success' => false,
                'message' => 'Metode pembayaran yang dipilih bukan transfer.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $pathBukti = $request->file('bukti_pembayaran')->store('bukti-pembayaran-hutang', 'public');

            if ($hutang->bukti_pembayaran && Storage::disk('public')->exists($hutang->bukti_pembayaran)) {
                Storage::disk('public')->delete($hutang->bukti_pembayaran);
            }

            $hutang->update([
                'metode_pembayaran_id' => $request->metode_pembayaran_id,
                'tanggal_pelunasan'    => now()->format('Y-m-d'),
                'bukti_pembayaran'     => $pathBukti,
                'status_hutang'        => 'Sudah Lunas',
                'diupdate_oleh'        => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran hutang transfer berhasil diproses.',
                'data'    => $hutang->fresh(['supplier', 'metodePembayaran', 'restockObat']),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses pembayaran transfer.',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }

    public function getDataRiwayatHutang()
    {
        $dataRiwayatHutang = HutangObat::with([
            'supplier',
            'restockObat.depot',
        ])
            ->whereIn('status_hutang', ['Sudah Lunas', 'Dibatalkan'])
            ->latest();

        return DataTables::of($dataRiwayatHutang)
            ->addIndexColumn()

            ->editColumn('supplier_id', function ($row) {
                return $row->supplier?->nama_supplier ?? '-';
            })

            ->filterColumn('supplier_id', function ($query, $keyword) {
                $query->whereHas('supplier', function ($q) use ($keyword) {
                    $q->where('nama_supplier', 'like', "%{$keyword}%");
                });
            })

            ->filter(function ($query) {
                $search = request('search')['value'] ?? null;

                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('no_faktur', 'like', "%{$search}%")
                            ->orWhere('status_hutang', 'like', "%{$search}%")
                            ->orWhereHas('supplier', function ($supplier) use ($search) {
                                $supplier->where('nama_supplier', 'like', "%{$search}%");
                            })
                            ->orWhereHas('restockObat.depot', function ($depot) use ($search) {
                                $depot->where('nama_depot', 'like', "%{$search}%");
                            });
                    });
                }
            })

            ->addColumn('action', function ($row) {
                return '
                <button 
                    type="button"
                    class="button-detail-riwayat-hutang px-4 py-2 bg-blue-500 text-white rounded-lg text-sm hover:bg-blue-600"
                    data-no-faktur="' . e($row->no_faktur) . '">
                    Detail
                </button>
            ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getDataDetailRiwayatHutang($noFaktur)
    {
        $hutang = HutangObat::with([
            'supplier',
            'metodePembayaran',

            'dibuatOleh.admin',
            'dibuatOleh.dokter',
            'dibuatOleh.pasien',
            'dibuatOleh.farmasi',
            'dibuatOleh.perawat',
            'dibuatOleh.kasir',

            'diupdateOleh.admin',
            'diupdateOleh.dokter',
            'diupdateOleh.pasien',
            'diupdateOleh.farmasi',
            'diupdateOleh.perawat',
            'diupdateOleh.kasir',

            'restockObat',
            'restockObat.supplier',
            'restockObat.depot',
            'restockObat.restockObatDetail.obat',
        ])->where('no_faktur', $noFaktur)->first();

        if (!$hutang) {
            return response()->json([
                'success' => false,
                'message' => 'Data hutang tidak ditemukan.',
            ], 404);
        }

        $dibuatOleh = $this->resolveUserName($hutang->dibuatOleh);
        $diupdateOleh = $this->resolveUserName($hutang->diupdateOleh);

        $restock = $hutang->restockObat;

        return response()->json([
            'success' => true,
            'message' => 'Detail data hutang berhasil diambil.',
            'data' => [
                'id' => $hutang->id,
                'no_faktur' => $hutang->no_faktur,
                'tanggal_hutang' => $hutang->tanggal_hutang,
                'tanggal_jatuh_tempo' => $hutang->tanggal_jatuh_tempo,
                'tanggal_pelunasan' => $hutang->tanggal_pelunasan,
                'total_hutang' => $hutang->total_hutang,
                'status_hutang' => $hutang->status_hutang,
                'bukti_pembayaran' => $hutang->bukti_pembayaran,
                'metode_pembayaran' => $hutang->metodePembayaran?->nama_metode ?? '-',
                'created_at' => $hutang->created_at,
                'updated_at' => $hutang->updated_at,

                'supplier' => [
                    'nama_supplier' => $hutang->supplier?->nama_supplier ?? '-',
                    'kontak_person' => $hutang->supplier?->kontak_person ?? '-',
                    'no_hp' => $hutang->supplier?->no_hp ?? '-',
                    'email' => $hutang->supplier?->email ?? '-',
                    'alamat' => $hutang->supplier?->alamat ?? '-',
                ],

                'audit' => [
                    'dibuat_oleh' => $dibuatOleh,
                    'diupdate_oleh' => $diupdateOleh,
                    'created_at' => $hutang->created_at,
                    'updated_at' => $hutang->updated_at,
                ],

                'restock' => [
                    'id' => $restock?->id,
                    'no_faktur_restock' => $restock?->no_faktur ?? '-',
                    'tanggal_terima' => $restock?->tanggal_terima,
                    'tanggal_jatuh_tempo' => $restock?->tanggal_jatuh_tempo,
                    'status_restock' => $restock?->status_pembayaran ?? $restock?->status_restock ?? '-',
                    'depot' => $restock?->depot?->nama_depot ?? '-',
                    'supplier' => $restock?->supplier?->nama_supplier ?? '-',
                    'total_tagihan' => $restock?->total_tagihan ?? $restock?->total_harga ?? $hutang->total_hutang ?? 0,
                    'detail_item' => collect($restock?->restockObatDetail ?? [])->map(function ($detail, $index) {
                        $qty = (float) ($detail->qty ?? 0);
                        $hargaBeli = (float) ($detail->harga_beli ?? 0);
                        $subtotal = (float) ($detail->subtotal ?? ($qty * $hargaBeli));
                        $diskonValue = (float) ($detail->diskon_value ?? 0);
                        $diskonAmount = (float) ($detail->diskon_amount ?? 0);
                        $totalSetelahDiskon = (float) ($detail->total_setelah_diskon ?? ($subtotal - $diskonAmount));

                        return [
                            'no' => $index + 1,
                            'kode_obat' => $detail->obat?->kode_obat ?? '-',
                            'nama_obat' => $detail->obat?->nama_obat ?? '-',
                            'qty' => $detail->qty ?? 0,
                            'harga_beli' => $detail->harga_beli ?? 0,
                            'subtotal' => $subtotal,
                            'diskon_type' => $detail->diskon_type ?? '-',
                            'diskon_value' => $diskonValue,
                            'diskon_amount' => $diskonAmount,
                            'total_setelah_diskon' => $totalSetelahDiskon,
                        ];
                    })->values(),
                ],
            ],
        ], 200);
    }

    /**
     * Helper untuk ambil nama user real, bukan nama role.
     */
    private function resolveUserName($user)
    {
        if (!$user) {
            return '-';
        }

        return $user->admin?->nama_admin
            ?? $user->dokter?->nama_dokter
            ?? $user->pasien?->nama_pasien
            ?? $user->farmasi?->nama_farmasi
            ?? $user->perawat?->nama_perawat
            ?? $user->kasir?->nama_kasir
            ?? $user->nama
            ?? $user->nama_role
            ?? '-';
    }
}
