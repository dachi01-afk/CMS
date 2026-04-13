<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\MetodePembayaran;
use App\Models\OrderLayanan;
use App\Models\PenjualanLayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class TransaksiLayananController extends Controller
{
    public function getDataTransaksiLayanan()
    {
        $dataOrderLayanan = OrderLayanan::with([
            'pasien',
            'metodePembayaran',
        ])->where('status_order_layanan', 'Belum Bayar')->latest();

        return DataTables::of($dataOrderLayanan)
            ->addIndexColumn()
            ->editColumn('nama_pasien', function ($dataOrderan) {
                return $dataOrderan->pasien->nama_pasien ?? '-';
            })
            ->editColumn('nama_metode', function ($dataOrderan) {
                return $dataOrderan->metodePembayaran->nama_metode ?? '-';
            })
            ->addColumn('action', function ($dataOrderan) {
                // halaman detail / bayar layanan → per kode_transaksi
                $bayarUrl = route('kasir.detail.orderan.transaksi.layanan', $dataOrderan->kode_transaksi);

                return '
                <button
                    type="button"
                    class="btn-bayar-layanan inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-md"
                    data-url="' . $bayarUrl . '">
                      <i class="fa-regular fa-pen-to-square mr-1"></i>
                    Bayar Sekarang
                </button>
            ';
            })
            ->make(true);
    }

    public function detailTransaksiLayanan($kodeTransaksi)
    {
        $dataOrderLayanan = OrderLayanan::with([
            'pasien',
            'metodePembayaran',
            'orderLayananDetail.layanan',
            'orderLayananDetail.layanan.kategoriLayanan',
        ])
            ->where('kode_transaksi', $kodeTransaksi)
            ->firstOrFail();

        $dataOrderan = $dataOrderLayanan->orderLayananDetail ?? collect();
        $totalOrderan = $dataOrderan->sum('qty');
        $dataMetodePembayaran = MetodePembayaran::all();
        $totalAwal = (float) $dataOrderan->sum('total_harga_item');

        return view('kasir.pembayaran.detail-transaksi-layanan', [
            'dataOrderLayanan' => $dataOrderLayanan,
            'dataOrderan' => $dataOrderan,
            'totalOrderan' => $totalOrderan,
            'dataMetodePembayaran' => $dataMetodePembayaran,
            'totalAwal' => $totalAwal,
            'approvalStatus' => null,
            'approvalItemsRaw' => [],
            'diskonLocked' => false,
        ]);
    }

    public function prosesPembayaranLayanan($kodeTransaksi)
    {
        // Ambil SEMUA baris dengan kode_transaksi tsb
        $items = PenjualanLayanan::with([
            'pasien',
            'layanan.kategoriLayanan',
            'kunjungan',
        ])
            ->where('kode_transaksi', $kodeTransaksi)
            ->get();

        if ($items->isEmpty()) {
            abort(404);
        }

        $first = $items->first();

        // Ringkasan transaksi (dipakai di header & bagian "Ringkasan Transaksi")
        $summary = (object) [
            'id_utama'          => $first->id,
            'kode_transaksi'    => $first->kode_transaksi,
            'status'            => $first->status ?? 'Belum Bayar',
            'tanggal_transaksi' => $first->tanggal_transaksi,

            'pasien'            => $first->pasien,
            'kunjungan'         => $first->kunjungan,

            'metode_pembayaran' => optional($first->metodePembayaran)->nama_metode ?? '-',
            'kasir_nama'        => $first->kasir_nama ?? null,

            // agregat
            'jumlah_total'      => $items->sum('jumlah'),
            'total_tagihan'     => $items->sum('total_tagihan'),
        ];

        $metodePembayaran = MetodePembayaran::all();

        return view('kasir.pembayaran.proses-pembayaran-layanan', [
            'summary'              => $summary,
            'items'                => $items,
            'dataMetodePembayaran' => $metodePembayaran,
        ]);
    }

    public function submitPembayaranLayanan(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:penjualan_layanan,id',
            'metode_pembayaran' => 'required|string',
            'bukti_pembayaran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $transaksi = PenjualanLayanan::findOrFail($validated['id']);

        // Upload bukti pembayaran jika ada
        if ($request->hasFile('bukti_pembayaran')) {
            $path = $request->file('bukti_pembayaran')->store('bukti_pembayaran', 'public');
            $transaksi->bukti_pembayaran = $path;
        }

        // Update transaksi
        $transaksi->metode_pembayaran = $validated['metode_pembayaran'];
        $transaksi->status = 'Sudah Bayar';
        $transaksi->tanggal_transaksi = now();
        $transaksi->save();

        return redirect()
            ->route('kasir.show.detail.transaksi.layanan', $transaksi->id)
            ->with('success', 'Pembayaran berhasil diproses.');
    }

    public function pembayaranLayananCash(Request $request)
    {
        $request->validate([
            'id'                   => ['required', 'exists:order_layanan,id'],
            'metode_pembayaran_id' => ['required', 'exists:metode_pembayaran,id'],
            'uang_yang_diterima'   => ['required', 'numeric', 'min:0'],
            'kembalian'            => ['nullable', 'numeric'],
            'diskon_items'         => ['nullable', 'string'],
        ]);

        try {
            $orderLayanan = OrderLayanan::with([
                'pasien',
                'metodePembayaran',
                'orderLayananDetail.layanan',
            ])->findOrFail($request->id);

            if (($orderLayanan->status_order_layanan ?? '') !== 'Belum Bayar') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi layanan ini sudah dibayar sebelumnya.',
                ], 422);
            }

            $details = $orderLayanan->orderLayananDetail ?? collect();

            if ($details->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detail layanan kosong.',
                ], 422);
            }

            $diskonItems = json_decode($request->diskon_items ?? '[]', true);
            if (!is_array($diskonItems)) {
                $diskonItems = [];
            }

            $mapPersen = [];
            foreach ($diskonItems as $item) {
                $detailId = (int) ($item['id'] ?? 0);
                $persen   = (float) ($item['persen'] ?? 0);

                if ($detailId > 0) {
                    if ($persen < 0) $persen = 0;
                    if ($persen > 100) $persen = 100;
                    $mapPersen[$detailId] = $persen;
                }
            }

            $subtotal = 0;
            $potonganPesanan = 0;
            $totalBayar = 0;

            foreach ($details as $detail) {
                $subtotalItem = (float) ($detail->total_harga_item ?? 0);
                $subtotal += $subtotalItem;

                $persen = (float) ($mapPersen[$detail->id] ?? 0);
                $potonganItem = $subtotalItem * ($persen / 100);

                if ($potonganItem > $subtotalItem) {
                    $potonganItem = $subtotalItem;
                }

                $potonganPesanan += $potonganItem;
                $totalBayar += ($subtotalItem - $potonganItem);
            }

            if ($subtotal <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total tagihan tidak valid.',
                ], 422);
            }

            $uangDiterima = (float) $request->uang_yang_diterima;

            if ($uangDiterima < $totalBayar) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nominal uang yang diterima belum cukup.',
                ], 422);
            }

            $kembalian = $uangDiterima - $totalBayar;

            $diskonPersenGlobal = 0;
            if ($subtotal > 0 && $potonganPesanan > 0) {
                $diskonPersenGlobal = ($potonganPesanan / $subtotal) * 100;
            }

            $orderLayanan->metode_pembayaran_id = $request->metode_pembayaran_id;
            $orderLayanan->subtotal = $subtotal;
            $orderLayanan->diskon_tipe = $potonganPesanan > 0 ? 'persen' : null;
            $orderLayanan->diskon_nilai = $potonganPesanan > 0 ? $diskonPersenGlobal : 0;
            $orderLayanan->potongan_pesanan = $potonganPesanan;
            $orderLayanan->total_bayar = $totalBayar;
            $orderLayanan->uang_yang_diterima = $uangDiterima;
            $orderLayanan->kembalian = $kembalian;
            $orderLayanan->tanggal_pembayaran = now();
            $orderLayanan->status_order_layanan = 'Sudah Bayar';
            $orderLayanan->save();

            $orderLayanan->refresh();

            return response()->json([
                'success' => true,
                'data' => $orderLayanan,
                'message' => 'Uang Kembalian Rp' . number_format((float) $orderLayanan->kembalian, 0, ',', '.') . '. Terimakasih 😊😊😊',
            ]);
        } catch (Throwable $e) {
            Log::error('Error pembayaran layanan cash', [
                'error'   => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function pembayaranLayananTransfer(Request $request)
    {
        $validated = $request->validate([
            'id'                   => 'required|exists:order_layanan,id',
            'metode_pembayaran_id' => 'required|exists:metode_pembayaran,id',
            'kode_transaksi'       => 'nullable|string',
            'total_tagihan'        => 'nullable|numeric|min:0',
            'total_setelah_diskon' => 'nullable|numeric|min:0',
            'diskon_tipe'          => 'nullable|in:persen,rupiah',
            'diskon_nilai'         => 'nullable|numeric|min:0',
            'bukti_pembayaran'     => 'required|file|mimes:jpg,jpeg,png,gif,pdf|max:5120',
        ]);

        try {
            $orderLayanan = OrderLayanan::with('orderLayananDetail')->findOrFail($validated['id']);

            // Optional, tapi bagus untuk jaga konsistensi
            if (!empty($validated['kode_transaksi']) && $orderLayanan->kode_transaksi !== $validated['kode_transaksi']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode transaksi tidak sesuai dengan data di sistem.',
                ], 422);
            }

            if ($orderLayanan->status_order_layanan === 'Sudah Bayar') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi layanan ini sudah dibayar sebelumnya.',
                ], 422);
            }

            // Hitung subtotal dari DB, jangan percaya penuh FE
            $subtotal = (float) $orderLayanan->orderLayananDetail()->sum('total_harga_item');

            // Kalau FE kirim total setelah diskon, pakai itu. Kalau tidak, fallback ke subtotal
            $totalBayar = $request->filled('total_setelah_diskon')
                ? (float) $request->total_setelah_diskon
                : $subtotal;

            if ($totalBayar < 0) {
                $totalBayar = 0;
            }

            if ($totalBayar > $subtotal) {
                $totalBayar = $subtotal;
            }

            $diskonTipe  = $request->input('diskon_tipe') ?: null;
            $diskonNilai = $request->filled('diskon_nilai')
                ? (float) $request->diskon_nilai
                : 0;

            $potonganPesanan = max($subtotal - $totalBayar, 0);

            $path = $orderLayanan->bukti_pembayaran;

            if ($request->hasFile('bukti_pembayaran')) {
                if ($path && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }

                $path = $request->file('bukti_pembayaran')
                    ->store('bukti_pembayaran_layanan', 'public');
            }

            $orderLayanan->update([
                'metode_pembayaran_id' => $validated['metode_pembayaran_id'],
                'subtotal'             => $subtotal,
                'diskon_tipe'          => $diskonTipe,
                'diskon_nilai'         => $diskonNilai,
                'potongan_pesanan'     => $potonganPesanan,
                'total_bayar'          => $totalBayar,
                'uang_yang_diterima'   => $totalBayar, // transfer dianggap pas
                'kembalian'            => 0,
                'tanggal_pembayaran'   => now(),
                'bukti_pembayaran'     => $path,
                'status_order_layanan' => 'Sudah Bayar',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran layanan (transfer) berhasil diproses.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error pembayaran layanan transfer', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses pembayaran transfer.',
            ], 500);
        }
    }

    public function getDataRiwayatTransaksiLayanan()
    {
        $dataOrderLayanan = OrderLayanan::with([
            'pasien',
            'metodePembayaran',
        ])->where('status_order_layanan', 'Sudah Bayar')->latest();

        return DataTables::of($dataOrderLayanan)
            ->addIndexColumn()
            ->filter(function ($dataOrderLayanan) {
                $search = trim(request('search.value'));

                if ($search !== '') {
                    $dataOrderLayanan->where(function ($query) use ($search) {
                        $query->whereRaw("DATE_FORMAT(order_layanan.tanggal_order, '%d %b %Y') LIKE ?", ["%{$search}%"])
                            ->orWhereRaw("DATE_FORMAT(order_layanan.tanggal_order, '%d') LIKE ?", ["%{$search}%"])
                            ->orWhereRaw("DATE_FORMAT(order_layanan.tanggal_order, '%m') LIKE ?", ["%{$search}%"])
                            ->orWhereRaw("DATE_FORMAT(order_layanan.tanggal_order, '%Y') LIKE ?", ["%{$search}%"])
                            ->orWhereHas('orderLayanan.pasien', function ($qq) use ($search) {
                                $qq->where('nama_pasien', 'like', '%' . $search . '%');
                            });
                    });
                };
            })
            ->editColumn('nama_pasien', function ($dataOrderLayanan) {
                return $dataOrderLayanan->pasien->nama_pasien ?? '-';
            })
            ->editColumn('nama_metode', function ($dataOrderLayanan) {
                return $dataOrderLayanan->metodePembayaran->nama_metode ?? '-';
            })
            ->editColumn('tanggal_order', function ($dataOrderLayanan) {
                return $dataOrderLayanan->getFormatTanggalOrder();
            })
            ->editColumn('bukti_pembayaran', function ($dataOrderLayanan) {
                if (!$dataOrderLayanan->bukti_pembayaran) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }

                $url = asset('storage/' . $dataOrderLayanan->bukti_pembayaran);

                return '
                <div class="flex flex-col items-center text-center space-y-2">
                    <img src="' . e($url) . '" alt="Bukti Pembayaran"
                         class="w-24 h-24 object-cover rounded-lg border border-gray-300 shadow-sm hover:scale-105 transition-transform duration-200 cursor-pointer"
                         onclick="window.open(\'' . e($url) . '\', \'_blank\')" />
                    <a href="' . e($url) . '" target="_blank"
                       class="text-sky-600 underline text-sm font-medium">
                        Lihat Bukti Pembayaran
                    </a>
                </div>
            ';
            })
            ->addColumn('action', function ($dataOrderLayanan) {
                $url = route('kasir.show.kwitansi.transaksi.layanan', [
                    'kodeTransaksi' => $dataOrderLayanan->kode_transaksi,
                ]);

                return '
                <button class="cetakKuitansi text-blue-600 hover:text-blue-800"
                        data-url="' . e($url) . '"
                        title="Cetak Kwitansi">
                    <i class="fa-solid fa-print"></i> Cetak Kwitansi
                </button>
            ';
            })
            ->rawColumns(['bukti_pembayaran', 'action'])
            ->make(true);
    }

    public function kwitansiTransaksiLayanan($kodeTransaksi)
    {
        // Ambil SEMUA baris penjualan_layanan untuk kode_transaksi ini
        $dataOrderLayanan = OrderLayanan::with([
            'pasien',
            'metodePembayaran',
            'orderLayananDetail',
            'orderLayananDetail.layanan',
            'orderLayananDetail.layanan.kategoriLayanan',
        ])
            ->where('kode_transaksi', $kodeTransaksi)
            ->first();

        if (!$dataOrderLayanan) {
            abort(404);
        }

        $subtotal = (float) $dataOrderLayanan->orderLayananDetail->sum('total_harga_item');

        $afterRow     = $dataOrderLayanan->firstWhere('total_bayar', '!=', null);
        $totalSesudah = $afterRow
            ? (float) $afterRow->total_bayar
            : $subtotal;

        $diskonNominal = max($subtotal - $totalSesudah, 0);

        $kategoriLayanan = $dataOrderLayanan->orderLayananDetail->first()?->layanan?->kategoriLayanan?->nama_kategori;

        $summary = (object) [
            'kode_transaksi'        => $dataOrderLayanan->kode_transaksi,
            'pasien'                => $dataOrderLayanan->pasien->nama_pasien ?? '-',
            'metode_pembayaran'     => $dataOrderLayanan->metodePembayaran->nama_metode ?? '-',
            'tanggal_pembayaran'     => $dataOrderLayanan->getFormatTanggalPembayaran(),
            'tanggal_order'     => $dataOrderLayanan->getFormatTanggalOrder(),

            'total_sebelum_diskon'  => $subtotal,
            'diskon_nominal'        => $diskonNominal,
            'total_setelah_diskon'  => $totalSesudah,

            'uang_yang_diterima'    => $dataOrderLayanan->uang_yang_diterima ? (float) $dataOrderLayanan->uang_yang_diterima : null,
            'kembalian'             => $dataOrderLayanan->kembalian ? (float) $dataOrderLayanan->kembalian : null,

            'kategori_layanan'        => $kategoriLayanan,
        ];

        $namaPT = 'Royal Klinik';

        return view('kasir.riwayat-transaksi.kwitansi-transaksi-layanan', [
            'dataOrderLayanan'   => $dataOrderLayanan,
            'summary' => $summary,
            'namaPT'  => $namaPT,
        ]);
    }
}
