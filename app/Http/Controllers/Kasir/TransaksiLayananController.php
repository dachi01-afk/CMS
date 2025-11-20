<?php

namespace App\Http\Controllers\Kasir;

use Throwable;
use Illuminate\Http\Request;
use App\Models\MetodePembayaran;
use App\Models\PenjualanLayanan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class TransaksiLayananController extends Controller
{
    /**
     * DataTables: get data transaksi layanan untuk kasir.
     */
    public function getDataTransaksiLayanan()
    {
        $query = PenjualanLayanan::query()
            ->with(['pasien', 'layanan', 'kategoriLayanan', 'metodePembayaran'])
            ->orderByDesc('tanggal_transaksi')->where('status', 'Belum Bayar')->get();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('nama_pasien', function ($row) {
                return $row->pasien->nama_pasien ?? '-';
            })
            ->addColumn('nama_layanan', function ($row) {
                return $row->layanan->nama_layanan ?? '-';
            })
            ->addColumn('kategori_layanan', function ($row) {
                return $row->kategoriLayanan->nama_kategori ?? '-';
            })
            ->editColumn('jumlah', function ($row) {
                return $row->jumlah ?? 0;
            })
            ->editColumn('total_tagihan', function ($row) {
                // kirim angka murni, nanti dirupiahkan di JS
                return $row->total_tagihan ?? 0;
            })
            ->editColumn('metode_pembayaran', function ($row) {
                return $row->metodePembayaran->nama_metode ?? '-';
            })
            ->editColumn('kode_transaksi', function ($row) {
                return $row->kode_transaksi ?? '-';
            })
            ->editColumn('tanggal_transaksi', function ($row) {
                if (!$row->tanggal_transaksi) {
                    return null; // atau '-' kalau mau string
                }

                return Carbon::parse($row->tanggal_transaksi)->format('Y-m-d H:i:s');
            })
            ->editColumn('status', function ($row) {
                return $row->status ?? '-';
            })
            ->addColumn('bukti_pembayaran', function ($row) {
                if (!$row->bukti_pembayaran) {
                    return '-';
                }

                $url = asset('storage/' . $row->bukti_pembayaran);

                return '<a href="' . $url . '" target="_blank" class="text-sky-600 underline text-xs">
                            Lihat
                        </a>';
            })
            ->addColumn('action', function ($row) {
                // halaman detail / bayar layanan
                $bayarUrl = route('kasir.show.detail.transaksi.layanan', $row->kode_transaksi);

                return '
                    <button
                        type="button"
                        class="btn-bayar-layanan inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-md"
                        data-url="' . $bayarUrl . '">
                        <i class="fa-regular fa-pen-to-square"></i>
                        Bayar Sekarang
                    </button>
                ';
            })
            ->rawColumns(['bukti_pembayaran', 'action'])
            ->make(true);
    }

    public function showDetailTransaksiLayanan($kodeTransaksi)
    {
        $transaksi = PenjualanLayanan::with([
            'pasien',
            'layanan.kategoriLayanan',
            'kunjungan.poli',
            'kunjungan.dokter',
        ])->where('kode_transaksi', $kodeTransaksi)->firstOrFail();

        return view('kasir.pembayaran.detail-transaksi-layanan', [
            'transaksi' => $transaksi,
        ]);
    }

    public function prosesPembayaranLayanan($kodeTransaksi)
    {
        $transaksi = PenjualanLayanan::with([
            'pasien',
            'layanan.kategoriLayanan'
        ])->where('kode_transaksi', $kodeTransaksi)->firstOrFail();

        $metodePembayaran = MetodePembayaran::get();

        return view('kasir.pembayaran.proses-pembayaran-layanan', [
            'transaksiLayanan' => $transaksi,
            'dataMetodePembayaran' => $metodePembayaran
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

    /**
     * Generate kode transaksi layanan jika belum ada.
     */
    protected function generateKodeTransaksiLayanan(): string
    {
        return 'TRX-' . strtoupper(uniqid());
    }

    /**
     * Proses pembayaran layanan dengan metode CASH.
     * Route contoh:
     * Route::post('/kasir/layanan/pembayaran/cash', [TransaksiLayananController::class, 'pembayaranLayananCash'])
     *      ->name('kasir.layanan.pembayaran.cash');
     */
    public function pembayaranLayananCash(Request $request)
    {
        $validated = $request->validate([
            'id'                   => 'required|exists:penjualan_layanan,id',
            'metode_pembayaran_id' => 'required|exists:metode_pembayaran,id',
            'total_tagihan'        => 'required|numeric',
            'uang_yang_diterima'   => 'required|numeric',
            'kembalian'            => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            /** @var \App\Models\PenjualanLayanan $transaksi */
            $transaksi = PenjualanLayanan::lockForUpdate()->findOrFail($validated['id']);

            if ($transaksi->status === 'Sudah Bayar') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi layanan ini sudah dibayar sebelumnya.',
                ], 422);
            }

            // Pastikan kode_transaksi terisi
            if (empty($transaksi->kode_transaksi)) {
                $transaksi->kode_transaksi = $this->generateKodeTransaksiLayanan();
            }

            $totalTagihan      = (float) $validated['total_tagihan'];
            $uangYangDiterima  = (float) $validated['uang_yang_diterima'];
            $kembalian         = (float) $validated['kembalian'];

            $transaksi->metode_pembayaran_id = $validated['metode_pembayaran_id'];
            $transaksi->total_tagihan        = $totalTagihan;
            $transaksi->uang_yang_diterima   = $uangYangDiterima;
            $transaksi->kembalian            = $kembalian;
            // sub_total bisa diisi sama dengan total_tagihan, atau tetap biarkan sesuai proses awal
            if (is_null($transaksi->sub_total)) {
                $transaksi->sub_total = $totalTagihan;
            }
            $transaksi->tanggal_transaksi    = now();
            $transaksi->status               = 'Sudah Bayar';

            $transaksi->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran layanan (cash) berhasil diproses.',
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Error pembayaran layanan cash: ' . $e->getMessage(), [
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses pembayaran.',
            ], 500);
        }
    }

    /**
     * Proses pembayaran layanan dengan metode TRANSFER.
     * Route contoh:
     * Route::post('/kasir/layanan/pembayaran/transfer', [TransaksiLayananController::class, 'pembayaranLayananTransfer'])
     *      ->name('kasir.layanan.pembayaran.transfer');
     */
    public function pembayaranLayananTransfer(Request $request)
    {
        $validated = $request->validate([
            'id'                   => 'required|exists:penjualan_layanan,id',
            'metode_pembayaran_id' => 'required|exists:metode_pembayaran,id',
            'bukti_pembayaran'     => 'required|file|mimes:jpg,jpeg,png,gif,pdf|max:5120', // 5MB
        ]);

        try {
            DB::beginTransaction();

            /** @var \App\Models\PenjualanLayanan $transaksi */
            $transaksi = PenjualanLayanan::lockForUpdate()->findOrFail($validated['id']);

            if ($transaksi->status === 'Sudah Bayar') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi layanan ini sudah dibayar sebelumnya.',
                ], 422);
            }

            // Pastikan kode_transaksi terisi
            if (empty($transaksi->kode_transaksi)) {
                $transaksi->kode_transaksi = $this->generateKodeTransaksiLayanan();
            }

            // Upload bukti pembayaran
            if ($request->hasFile('bukti_pembayaran')) {
                // hapus file lama kalau ada
                if ($transaksi->bukti_pembayaran && Storage::disk('public')->exists($transaksi->bukti_pembayaran)) {
                    Storage::disk('public')->delete($transaksi->bukti_pembayaran);
                }

                $path = $request->file('bukti_pembayaran')
                    ->store('bukti_pembayaran_layanan', 'public');

                $transaksi->bukti_pembayaran = $path;
            }

            $transaksi->metode_pembayaran_id = $validated['metode_pembayaran_id'];
            // kalau total_tagihan belum diisi, biarkan atau hitung di tempat lain.
            if (is_null($transaksi->total_tagihan)) {
                // kalau mau, bisa diisi dari sub_total:
                $transaksi->total_tagihan = $transaksi->sub_total ?? 0;
            }
            $transaksi->tanggal_transaksi = now();
            $transaksi->status            = 'Sudah Bayar';

            $transaksi->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran layanan (transfer) berhasil diproses.',
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Error pembayaran layanan transfer: ' . $e->getMessage(), [
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses pembayaran transfer.',
            ], 500);
        }
    }

    /**
     * DataTables: Riwayat transaksi layanan (hanya yang Sudah Bayar).
     * URL dipakai di FE: /kasir/get-data-riwayat-pembayaran
     */
    public function getDataRiwayatTransaksiLayanan()
    {
        $dataTransaksi = PenjualanLayanan::with([
            'pasien',
            'kunjungan',
            'layanan',
            'metodePembayaran',
            'kategoriLayanan',
        ])
            ->where('status', 'Sudah Bayar')
            ->latest('tanggal_transaksi')
            ->latest('created_at')
            ->get();

        return DataTables::of($dataTransaksi)
            ->addIndexColumn()

            // =================== IDENTITAS DASAR ===================
            ->addColumn('nama_pasien', function (PenjualanLayanan $p) {
                return $p->pasien->nama_pasien ?? '-';
            })

            ->addColumn('tanggal_kunjungan', function (PenjualanLayanan $p) {
                // pakai tanggal kunjungan kalau ada, kalau tidak pakai tanggal_transaksi
                $tgl = optional($p->kunjungan)->tanggal_kunjungan ?? $p->tanggal_transaksi;

                return $tgl
                    ? Carbon::parse($tgl)->toIso8601String()  // biar JS mudah parse
                    : '-';
            })

            ->addColumn('no_antrian', function (PenjualanLayanan $p) {
                // sesuaikan dengan nama kolom yang kamu pakai di tabel kunjungan
                return optional($p->kunjungan)->no_antrian
                    ?? optional($p->kunjungan)->nomor_antrian
                    ?? '-';
            })

            // =================== LAYANAN, KATEGORI LAYANAN & JUMLAH ===================
            ->addColumn('nama_layanan', function (PenjualanLayanan $p) {
                if (!$p->layanan) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }

                $nama = e($p->layanan->nama_layanan ?? '-');
                return '<ul class="list-disc pl-4"><li>' . $nama . '</li></ul>';
            })

            ->addColumn('nama_kategori', function (PenjualanLayanan $p) {
                if (!$p->kategoriLayanan) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }

                $nama = e($p->kategoriLayanan->nama_kategori ?? '-');
                return '<ul class="list-disc pl-4"><li>' . $nama . '</li></ul>';
            })

            ->addColumn('jumlah_layanan', function (PenjualanLayanan $p) {
                if (!$p->layanan) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }

                $qty = $p->jumlah ?? 0;
                return '<ul class="list-disc pl-4"><li>' . e($qty) . '</li></ul>';
            })

            // =================== TOTAL & METODE ===================
            ->addColumn('total_tagihan', function (PenjualanLayanan $p) {
                // total_tagihan langsung dari kolom decimal penjualan_layanan
                $total = (float) ($p->total_tagihan ?? 0);
                return 'Rp ' . number_format($total, 0, ',', '.');
            })

            ->addColumn('metode_pembayaran', function (PenjualanLayanan $p) {
                // relasi ke tabel metode_pembayaran via metode_pembayaran_id
                return optional($p->metodePembayaran)->nama_metode ?? '-';
            })

            // =================== BUKTI PEMBAYARAN ===================
            ->addColumn('bukti_pembayaran', function (PenjualanLayanan $p) {
                if (!$p->bukti_pembayaran) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }

                $url    = asset('storage/' . $p->bukti_pembayaran);
                $urlEsc = e($url);

                $html = <<<HTML
<div class="flex flex-col items-center text-center space-y-2">
    <img src="{$urlEsc}" alt="Bukti Pembayaran"
         class="w-24 h-24 object-cover rounded-lg border border-gray-300 shadow-sm hover:scale-105 transition-transform duration-200 cursor-pointer"
         onclick="window.open('{$urlEsc}', '_blank')" />
    <a href="{$urlEsc}" target="_blank" class="text-sky-600 underline text-sm font-medium">
        Lihat Bukti Pembayaran
    </a>
</div>
HTML;
                return $html;
            })

            // =================== STATUS ===================
            ->addColumn('status', function (PenjualanLayanan $p) {
                return $p->status ?? '-';
            })

            // =================== ACTION (CETAK KWITANSI) ===================
            ->addColumn('action', function (PenjualanLayanan $p) {
                $url    = route('kasir.show.kwitansi.transaksi.layanan', [
                    'kodeTransaksi' => $p->kode_transaksi,
                ]);

                return '<button class="cetakKuitansi text-blue-600 hover:text-blue-800"
                       data-url="' . $url . '"
                       title="Cetak Kwitansi">
                        <i class="fa-solid fa-print"></i> Cetak Kwitansi
                    </button>';
            })

            ->rawColumns([
                'nama_layanan',
                'nama_kategori',
                'jumlah_layanan',
                'bukti_pembayaran',
                'action',
            ])
            ->make(true);
    }


    public function kwitansiTransaksiLayanan($kodeTransaksi)
    {
        // Ambil transaksi layanan berdasarkan kode_transaksi
        $transaksi = PenjualanLayanan::with([
            'pasien',
            'kunjungan',
            'layanan',
            'kategoriLayanan',
            'metodePembayaran',
        ])
            ->where('kode_transaksi', $kodeTransaksi)
            ->firstOrFail();

        // Total layanan: ambil dari total_tagihan kalau ada,
        // kalau kosong fallback ke sub_total, kalau masih kosong kasih 0
        $totalLayanan = (float) ($transaksi->total_tagihan
            ?? $transaksi->sub_total
            ?? 0);

        // Grand total = hanya dari layanan
        $grandTotal = $totalLayanan;

        $namaPT = 'Royal Klinik';

        $kodeTransaksi = $transaksi->kode_transaksi;

        // Sesuaikan nama view dengan yang kamu pakai untuk kwitansi layanan
        return view('kasir.riwayat-transaksi.kwitansi-transaksi-layanan', [
            'dataTransaksiLayanan'     => $transaksi,
            'totalLayanan'  => $totalLayanan,
            'grandTotal'    => $grandTotal,
            'namaPT'        => $namaPT,
            'kodeTransaksi'        => $kodeTransaksi,
        ]);
    }
}
