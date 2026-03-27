<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\MetodePembayaran;
use App\Models\PenjualanObat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Yajra\DataTables\Facades\DataTables;

class TransaksiObatController extends Controller
{
    public function getDataTransaksiObat(Request $request)
    {
        $query = PenjualanObat::with([
            'pasien',
            'penjualanObatDetail.obat',
            'metodePembayaran'
        ])
            ->where('status', 'Belum Bayar')
            ->select('penjualan_obat.*')
            ->latest();

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('nama_pasien', function ($row) {
                return $row->pasien->nama_pasien ?? '-';
            })
            ->addColumn('nama_obat', function ($row) {
                return $row->penjualanObatDetail
                    ->map(function ($detail) {
                        $nama = $detail->obat->nama_obat ?? '-';
                        $qty = (int) ($detail->jumlah ?? 0);
                        return "{$nama} ({$qty})";
                    })
                    ->implode(', ');
            })
            ->addColumn('jumlah_item', function ($row) {
                return $row->penjualanObatDetail->count();
            })
            ->addColumn('metode_pembayaran', function ($row) {
                return $row->metodePembayaran->nama_metode ?? '-';
            })
            ->editColumn('tanggal_transaksi', function ($row) {
                return $row->tanggal_transaksi
                    ? Carbon::parse($row->tanggal_transaksi)->toIso8601String()
                    : null;
            })
            ->addColumn('bukti_pembayaran', function ($row) {
                if (!$row->bukti_pembayaran) {
                    return '<span class="text-slate-400 italic">Belum ada</span>';
                }

                $url = asset('storage/' . $row->bukti_pembayaran);

                return '
                    <div class="flex flex-col items-center gap-2">
                        <img src="' . $url . '" alt="Bukti Pembayaran"
                            class="w-14 h-14 rounded-lg object-cover border border-slate-200 shadow-sm cursor-pointer"
                            onclick="window.open(\'' . $url . '\', \'_blank\')" />
                        <a href="' . $url . '" target="_blank"
                            class="text-xs text-sky-600 hover:underline">
                            Lihat
                        </a>
                    </div>
                ';
            })
            ->addColumn('action', function ($row) {
                $url = route('kasir.transaksi.obat', ['kodeTransaksi' => $row->kode_transaksi]);

                return '
                    <button
                        class="bayarSekarang inline-flex items-center gap-2 rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-100"
                        data-url="' . $url . '">
                        <i class="fa-solid fa-money-bill-wave"></i>
                        Bayar Sekarang
                    </button>
                ';
            })
            ->filterColumn('nama_pasien', function ($query, $keyword) {
                $query->whereHas('pasien', function ($q) use ($keyword) {
                    $q->where('nama_pasien', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['bukti_pembayaran', 'action'])
            ->toJson();
    }

    public function transaksiObat($kodeTransaksi)
    {
        $transaksi = PenjualanObat::with([
            'pasien',
            'penjualanObatDetail.obat',
            'metodePembayaran'
        ])->where('kode_transaksi', $kodeTransaksi)->first();

        if (!$transaksi) {
            abort(404, 'Transaksi tidak ditemukan');
        }

        $dataPasien = $transaksi->pasien;
        $dataMetodePembayaran = MetodePembayaran::all();

        $subTotal = (float) $transaksi->penjualanObatDetail->sum(function ($detail) {
            return (float) ($detail->sub_total ?? 0);
        });

        $totalSetelahDiskon = (float) $transaksi->penjualanObatDetail->sum(function ($detail) {
            return $detail->total_setelah_diskon !== null
                ? (float) $detail->total_setelah_diskon
                : (float) ($detail->sub_total ?? 0);
        });

        $tanggalTransaksi = $transaksi->tanggal_transaksi;
        $id = $transaksi->id;

        // default awal, nanti bisa diisi dari tabel diskon_approval kalau sudah siap
        $approvalStatus = null;
        $approvalItemsRaw = [];

        return view('kasir.pembayaran.detail-transaksi-obat', compact(
            'transaksi',
            'dataMetodePembayaran',
            'dataPasien',
            'subTotal',
            'totalSetelahDiskon',
            'tanggalTransaksi',
            'id',
            'kodeTransaksi',
            'approvalStatus',
            'approvalItemsRaw',
        ));
    }

    public function transaksiCash(Request $request)
    {
        $request->validate([
            'uang_yang_diterima'    => ['required', 'numeric'],
            'kembalian'             => ['required', 'numeric'],
            'metode_pembayaran_id'  => ['required', 'exists:metode_pembayaran,id'],
            'kode_transaksi'        => ['required', 'string'],
            'total_tagihan'         => ['nullable', 'numeric'],
            'total_setelah_diskon'  => ['nullable', 'numeric'],
            'diskon_tipe'           => ['nullable', 'in:persen,nominal'],
            'diskon_nilai'          => ['nullable', 'numeric'],
        ]);

        $transaksi = PenjualanObat::where('kode_transaksi', $request->kode_transaksi)->first();

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi dengan kode tersebut tidak ditemukan.',
            ], 404);
        }

        $transaksi->update([
            'metode_pembayaran_id' => $request->metode_pembayaran_id,
            'total_tagihan'        => $request->total_tagihan ?? $transaksi->total_tagihan,
            'diskon_tipe'          => $request->diskon_tipe ?: null,
            'diskon_nilai'         => $request->diskon_nilai ?? 0,
            'total_setelah_diskon' => $request->total_setelah_diskon ?? $transaksi->total_tagihan,
            'uang_yang_diterima'   => $request->uang_yang_diterima,
            'kembalian'            => $request->kembalian,
            'tanggal_transaksi'    => now(),
            'status'               => 'Sudah Bayar',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil! Kembalian Rp ' . number_format($request->kembalian, 0, ',', '.') . '.',
        ]);
    }

    public function transaksiTransfer(Request $request)
    {
        $request->validate([
            'kode_transaksi'       => ['required', 'string'],
            'total_tagihan'        => ['required', 'numeric'],
            'total_setelah_diskon' => ['nullable', 'numeric'],
            'diskon_tipe'          => ['nullable', 'in:persen,nominal'],
            'diskon_nilai'         => ['nullable', 'numeric'],
            'bukti_pembayaran'     => ['required', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,jfif', 'max:5120'],
            'metode_pembayaran'    => ['required', 'exists:metode_pembayaran,id'],
        ]);

        $transaksi = PenjualanObat::where('kode_transaksi', $request->kode_transaksi)->first();

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi dengan kode tersebut tidak ditemukan.',
            ], 404);
        }

        $fotoPath = null;

        if ($request->hasFile('bukti_pembayaran')) {
            $file = $request->file('bukti_pembayaran');
            $extension = strtolower($file->getClientOriginalExtension());

            if ($extension === 'jfif') {
                $extension = 'jpg';
            }

            $fileName = time() . '_' . uniqid() . '.' . $extension;
            $path = 'bukti-transaksi/' . $fileName;

            if ($extension === 'svg') {
                Storage::disk('public')->put($path, file_get_contents($file));
            } else {
                $image = Image::read($file);
                $image->scale(width: 800);
                Storage::disk('public')->put(
                    $path,
                    (string) $image->encodeByExtension($extension, quality: 80)
                );
            }

            $fotoPath = $path;
        }

        $transaksi->update([
            'metode_pembayaran_id' => $request->metode_pembayaran,
            'total_tagihan'        => $request->total_tagihan,
            'diskon_tipe'          => $request->diskon_tipe ?: null,
            'diskon_nilai'         => $request->diskon_nilai ?? 0,
            'total_setelah_diskon' => $request->total_setelah_diskon ?? $request->total_tagihan,
            'uang_yang_diterima'   => $request->total_setelah_diskon ?? $request->total_tagihan,
            'kembalian'            => 0,
            'bukti_pembayaran'     => $fotoPath,
            'tanggal_transaksi'    => now(),
            'status'               => 'Sudah Bayar',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaksi dengan kode ' . $request->kode_transaksi . ' berhasil diperbarui.',
        ]);
    }
}
