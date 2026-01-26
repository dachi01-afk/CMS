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
     * - Hanya status "Belum Bayar"
     * - Digroup per kode_transaksi (1 row per transaksi)
     */
    public function getDataTransaksiLayanan()
    {
        // Ambil semua item yang Belum Bayar + relasi
        $items = PenjualanLayanan::with(['pasien', 'layanan', 'kategoriLayanan', 'metodePembayaran'])
            ->where('status', 'Belum Bayar')
            ->orderByDesc('tanggal_transaksi')
            ->get();

        // Grouping per kode_transaksi, lalu diringkas jadi 1 row per transaksi
        $grouped = $items
            ->groupBy('kode_transaksi')
            ->map(function ($group) {
                $first = $group->first();

                return (object) [
                    // identitas transaksi
                    'kode_transaksi'    => $first->kode_transaksi,
                    'nama_pasien'       => $first->pasien->nama_pasien ?? '-',

                    // gabung semua layanan yang ada di transaksi ini
                    // contoh: "Konsultasi Dokter + USG + Lab"
                    'nama_layanan'      => $group->pluck('layanan.nama_layanan')
                        ->filter()
                        ->unique()
                        ->implode(' + ') ?: '-',

                    // gabung kategori (kalau campur-campur)
                    'kategori_layanan'  => $group->pluck('kategoriLayanan.nama_kategori')
                        ->filter()
                        ->unique()
                        ->implode(' / ') ?: '-',

                    // jumlah total item dalam transaksi (boleh pakai sum(jumlah) kalau kolomnya dipakai)
                    'jumlah'            => $group->sum('jumlah') ?: $group->count(),

                    // total tagihan = penjumlahan semua item per transaksi
                    'total_tagihan'     => $group->sum('total_tagihan') ?: 0,

                    // properti lain diasumsikan sama dalam 1 transaksi → ambil dari first()
                    'metode_pembayaran' => optional($first->metodePembayaran)->nama_metode ?? '-',
                    'tanggal_transaksi' => $first->tanggal_transaksi,
                    'status'            => $first->status ?? '-',
                    'bukti_pembayaran'  => $first->bukti_pembayaran,
                ];
            })
            ->values(); // reset index supaya rapi

        return DataTables::of($grouped)
            ->addIndexColumn()

            ->addColumn('nama_pasien', function ($row) {
                return $row->nama_pasien ?? '-';
            })
            ->addColumn('nama_layanan', function ($row) {
                return $row->nama_layanan ?? '-';
            })
            ->addColumn('kategori_layanan', function ($row) {
                return $row->kategori_layanan ?? '-';
            })

            ->editColumn('jumlah', function ($row) {
                return $row->jumlah ?? 0;
            })
            ->editColumn('total_tagihan', function ($row) {
                // kirim angka murni, nanti dirupiahkan di JS
                return $row->total_tagihan ?? 0;
            })

            ->editColumn('metode_pembayaran', function ($row) {
                return $row->metode_pembayaran ?? '-';
            })
            ->editColumn('kode_transaksi', function ($row) {
                return $row->kode_transaksi ?? '-';
            })
            ->editColumn('tanggal_transaksi', function ($row) {
                if (!$row->tanggal_transaksi) {
                    return null;
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
                // halaman detail / bayar layanan → per kode_transaksi
                $bayarUrl = route('kasir.show.detail.transaksi.layanan', $row->kode_transaksi);

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

            ->rawColumns(['bukti_pembayaran', 'action'])
            ->make(true);
    }

    public function showDetailTransaksiLayanan($kodeTransaksi)
    {
        // Ambil SEMUA baris dengan kode_transaksi tersebut
        $items = PenjualanLayanan::with([
            'pasien',
            'layanan.kategoriLayanan',
            'kunjungan.poli',
            'kunjungan.dokter',
            'metodePembayaran',
        ])
            ->where('kode_transaksi', $kodeTransaksi)
            ->get();

        if ($items->isEmpty()) {
            abort(404);
        }

        $first = $items->first();

        // Ringkasan untuk header & kartu-kartu
        $summary = (object) [
            'kode_transaksi'    => $first->kode_transaksi,
            'status'            => $first->status ?? 'Belum Bayar',
            'tanggal_transaksi' => $first->tanggal_transaksi,
            'kasir_nama'        => $first->kasir_nama ?? null,

            // relasi (biar di Blade tetap bisa pakai ->pasien, ->kunjungan dsb)
            'pasien'            => $first->pasien,
            'kunjungan'         => $first->kunjungan,
            'bukti_pembayaran'  => $first->bukti_pembayaran,
            // string nama metode pembayaran
            'metode_pembayaran' => optional($first->metodePembayaran)->nama_metode ?? '-',

            // agregat
            'jumlah_total'      => $items->sum('jumlah'),
            'total_tagihan'     => $items->sum('total_tagihan'),
        ];

        // dd($summary->total_tagihan);

        return view('kasir.pembayaran.detail-transaksi-layanan', [
            'summary' => $summary,
            'items'   => $items,
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

    /**
     * Generate kode transaksi layanan jika belum ada.
     */
    protected function generateKodeTransaksiLayanan(): string
    {
        return 'TRX-' . strtoupper(uniqid());
    }

    public function pembayaranLayananCash(Request $request)
    {
        $validated = $request->validate([
            'id'                   => 'required|exists:penjualan_layanan,id',
            'kode_transaksi'       => 'required|string',
            'metode_pembayaran_id' => 'required|exists:metode_pembayaran,id',

            'total_tagihan'        => 'required|numeric', // grand total sebelum diskon
            'total_setelah_diskon' => 'required|numeric', // grand total sesudah diskon

            'uang_yang_diterima'   => 'required|numeric',
            'kembalian'            => 'required|numeric',

            'diskon_tipe'          => 'nullable|string|in:persen,rupiah,',
            'diskon_nilai'         => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

        // Ambil salah satu baris sebagai "utama" + lock
            /** @var \App\Models\PenjualanLayanan $transaksiUtama */
            $transaksiUtama = PenjualanLayanan::lockForUpdate()->findOrFail($validated['id']);

            // Pastikan kode_transaksi konsisten (optional, biar aman)
            $kodeTransaksi = $validated['kode_transaksi'];
            if ($transaksiUtama->kode_transaksi !== $kodeTransaksi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode transaksi tidak sesuai dengan data di sistem.',
                ], 422);
            }

            // Ambil semua item dalam transaksi ini (1 kode_transaksi = banyak baris)
            $items = PenjualanLayanan::where('kode_transaksi', $kodeTransaksi)
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data transaksi layanan tidak ditemukan.',
                ], 404);
            }

            // Cek kalau sudah dibayar
            if ($items->first()->status === 'Sudah Bayar') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi layanan ini sudah dibayar sebelumnya.',
                ], 422);
            }

            $totalTagihan       = (float) $validated['total_tagihan'];        // grand total sebelum diskon
            $totalSetelahDiskon = (float) $validated['total_setelah_diskon']; // grand total sesudah diskon
            $uangYangDiterima   = (float) $validated['uang_yang_diterima'];
            $kembalian          = (float) $validated['kembalian'];

            // Validasi server-side: uang diterima harus cukup
            if ($uangYangDiterima < $totalSetelahDiskon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nominal uang yang diterima belum mencukupi total tagihan.',
                ], 422);
            }

            $diskonTipe  = $request->input('diskon_tipe') ?: null;
            $diskonNilai = (float) $request->input('diskon_nilai', 0);

            // Update SEMUA baris dalam transaksi ini
            foreach ($items as $item) {
                // Pastikan ada kode_transaksi
                if (empty($item->kode_transaksi)) {
                    $item->kode_transaksi = $kodeTransaksi;
                }

                $item->metode_pembayaran_id = $validated['metode_pembayaran_id'];

                // Jangan mengubah total_tagihan per item (biarkan nilai per baris),
                // grand total kita simpan di kolom total_setelah_diskon dll secara "duplikatif" per baris
                $item->diskon_tipe          = $diskonTipe;
                $item->diskon_nilai         = $diskonNilai;
                $item->total_setelah_diskon = $totalSetelahDiskon;

                $item->uang_yang_diterima   = $uangYangDiterima;
                $item->kembalian            = $kembalian;

                // Kalau sub_total masih null, isi dengan total_tagihan baris tsb
                if (is_null($item->sub_total)) {
                    $item->sub_total = $item->total_tagihan;
                }

                $item->tanggal_transaksi    = now();
                $item->status               = 'Sudah Bayar';

                $item->save();
            }

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
     * Route:
     * Route::post('/kasir/layanan/pembayaran/transfer', [TransaksiLayananController::class, 'pembayaranLayananTransfer'])
     *      ->name('kasir.layanan.pembayaran.transfer');
     */
    public function pembayaranLayananTransfer(Request $request)
    {
        $validated = $request->validate([
            'id'                   => 'required|exists:penjualan_layanan,id',
            'kode_transaksi'       => 'required|string',
            'metode_pembayaran_id' => 'required|exists:metode_pembayaran,id',

            // dikirim dari JS (sama seperti CASH)
            'total_tagihan'        => 'required|numeric',   // grand total SEBELUM diskon (boleh kita abaikan di DB)
            'total_setelah_diskon' => 'required|numeric',   // grand total SETELAH diskon
            'diskon_tipe'          => 'nullable|string',
            'diskon_nilai'         => 'nullable|numeric',

            // bukti transfer
            'bukti_pembayaran'     => 'required|file|mimes:jpg,jpeg,png,gif,pdf|max:5120', // 5MB
        ]);

        try {
            DB::beginTransaction();

            // Ambil SEMUA item dengan kode_transaksi tsb
            $items = PenjualanLayanan::where('kode_transaksi', $validated['kode_transaksi'])
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data transaksi layanan tidak ditemukan.',
                ], 404);
            }

            // Kalau sudah ada yang Sudah Bayar -> jangan diproses lagi
            if ($items->contains(fn($row) => $row->status === 'Sudah Bayar')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi layanan ini sudah dibayar sebelumnya.',
                ], 422);
            }

            // Upload bukti pembayaran (sekali, dipakai semua baris)
            $path = null;
            if ($request->hasFile('bukti_pembayaran')) {
                $file = $request->file('bukti_pembayaran');

                // hapus bukti lama kalau ada (cukup di baris pertama)
                $firstRow = $items->first();
                if (
                    $firstRow->bukti_pembayaran &&
                    Storage::disk('public')->exists($firstRow->bukti_pembayaran)
                ) {
                    Storage::disk('public')->delete($firstRow->bukti_pembayaran);
                }

                $path = $file->store('bukti_pembayaran_layanan', 'public');
            }

            $now = now();

            foreach ($items as $row) {
                // kolom umum -> semua baris
                $row->metode_pembayaran_id = $validated['metode_pembayaran_id'];
                $row->tanggal_transaksi    = $now;
                $row->status               = 'Sudah Bayar';

                if ($path) {
                    $row->bukti_pembayaran = $path;
                }

                // *** BIARKAN total_tagihan ASLI (tidak diubah) ***

                // kolom agregat -> DISET KE SEMUA BARIS
                $row->total_setelah_diskon = $validated['total_setelah_diskon']; // grand total setelah diskon
                $row->uang_yang_diterima   = $validated['total_setelah_diskon']; // transfer selalu pas
                $row->kembalian            = 0;

                $row->diskon_tipe          = $validated['diskon_tipe']  ?: null;
                $row->diskon_nilai         = $validated['diskon_nilai'] ?: null;

                $row->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran layanan (transfer) berhasil diproses.',
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e; // biarkan Laravel munculin error aslinya

            // Log::error('Error pembayaran layanan transfer: ' . $e->getMessage(), [
            //     'request' => $request->all(),
            // ]);

            // return response()->json([
            //     'success' => false,
            //     'message' => 'Terjadi kesalahan saat memproses pembayaran transfer.',
            // ], 500);
        }
    }

    /**
     * DataTables: Riwayat transaksi layanan (hanya yang Sudah Bayar),
     * digroup per kode_transaksi → 1 baris = 1 transaksi.
     */
    public function getDataRiwayatTransaksiLayanan()
    {
        // Ambil SEMUA item yang sudah bayar
        $items = PenjualanLayanan::with([
            'pasien',
            'kunjungan',
            'layanan',
            'metodePembayaran',
            'kategoriLayanan',
        ])
            ->where('status', 'Sudah Bayar')
            ->orderByDesc('tanggal_transaksi')
            ->orderByDesc('created_at')
            ->get();

        // Group per kode_transaksi → diringkas satu objek per transaksi
        $grouped = $items
            ->groupBy('kode_transaksi')
            ->map(function ($group) {
                $first = $group->first();

                $tanggalKunjungan = optional($first->kunjungan)->tanggal_kunjungan
                    ?? $first->tanggal_transaksi;

                // total sebelum diskon = sum total_tagihan semua baris (sama seperti di prosesPembayaranLayanan)
                $totalSebelum = (float) $group->sum('total_tagihan');

                // grand total setelah diskon: ambil dari salah satu baris yang punya total_setelah_diskon
                $grandAfter   = $group->firstWhere('total_setelah_diskon', '!=', null);
                $totalSesudah = $grandAfter
                    ? (float) $grandAfter->total_setelah_diskon
                    : $totalSebelum; // fallback kalau belum ada diskon / transaksi lama

                $diskonNominal = max($totalSebelum - $totalSesudah, 0);

                return (object) [
                    'kode_transaksi'        => $first->kode_transaksi,
                    'nama_pasien'           => $first->pasien->nama_pasien ?? '-',
                    'tanggal_kunjungan'     => $tanggalKunjungan
                        ? Carbon::parse($tanggalKunjungan)->toIso8601String()
                        : null,
                    'no_antrian'            => optional($first->kunjungan)->no_antrian
                        ?? optional($first->kunjungan)->nomor_antrian
                        ?? '-',

                    'nama_layanan'          => $group->pluck('layanan.nama_layanan')
                        ->filter()
                        ->unique()
                        ->implode(' + ') ?: '-',

                    'nama_kategori'         => $group->pluck('kategoriLayanan.nama_kategori')
                        ->filter()
                        ->unique()
                        ->implode(' / ') ?: '-',

                    'jumlah_layanan'        => $group->sum('jumlah') ?: $group->count(),

                    // simpan tiga angka ke summary (kalau mau dipakai di tempat lain / kwitansi)
                    'total_sebelum_diskon'  => $totalSebelum,
                    'diskon_nominal'        => $diskonNominal,
                    'total_setelah_diskon'  => $totalSesudah,

                    // angka yang dikirim ke DataTables kolom "Total" (final sesudah diskon)
                    'total_tagihan'         => $totalSesudah,

                    'metode_pembayaran'     => optional($first->metodePembayaran)->nama_metode ?? '-',
                    'status'                => $first->status ?? '-',
                    'bukti_pembayaran'      => $first->bukti_pembayaran,
                ];
            })
            ->values();

        return DataTables::of($grouped)
            ->addIndexColumn()

            ->addColumn('nama_pasien', fn($row) => $row->nama_pasien ?? '-')
            ->addColumn('tanggal_kunjungan', fn($row) => $row->tanggal_kunjungan)
            ->addColumn('no_antrian', fn($row) => $row->no_antrian ?? '-')
            ->addColumn('nama_layanan', fn($row) => $row->nama_layanan ?? '-')
            ->addColumn('nama_kategori', fn($row) => $row->nama_kategori ?? '-')
            ->addColumn('jumlah_layanan', fn($row) => $row->jumlah_layanan ?? 0)
            ->addColumn('total_tagihan', fn($row) => $row->total_tagihan ?? 0)
            ->addColumn('metode_pembayaran', fn($row) => $row->metode_pembayaran ?? '-')
            ->addColumn('status', fn($row) => $row->status ?? '-')

            ->addColumn('bukti_pembayaran', function ($row) {
                if (!$row->bukti_pembayaran) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }

                $url = asset('storage/' . $row->bukti_pembayaran);

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

            ->addColumn('action', function ($row) {
                $url = route('kasir.show.kwitansi.transaksi.layanan', [
                    'kodeTransaksi' => $row->kode_transaksi,
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
        $items = PenjualanLayanan::with([
            'pasien',
            'kunjungan',
            'layanan.kategoriLayanan',
            'kategoriLayanan',
            'metodePembayaran',
        ])
            ->where('kode_transaksi', $kodeTransaksi)
            ->get();

        if ($items->isEmpty()) {
            abort(404);
        }

        $first = $items->first();

        // ====== AGREGAT TOTAL & DISKON (SAMA LOGIC DENGAN RIWAYAT & PROSES PEMBAYARAN) ======
        // total sebelum diskon = sum(total_tagihan) per baris
        $totalSebelum = (float) $items->sum('total_tagihan');

        // grand total setelah diskon (disimpan di salah satu baris, sama untuk 1 kode_transaksi)
        $afterRow     = $items->firstWhere('total_setelah_diskon', '!=', null);
        $totalSesudah = $afterRow
            ? (float) $afterRow->total_setelah_diskon
            : $totalSebelum;

        $diskonNominal = max($totalSebelum - $totalSesudah, 0);

        // sumber info pembayaran (uang diterima, kembalian, metode)
        $paySource = $items->firstWhere('status', 'Sudah Bayar') ?? $first;

        // kategori utama (dipakai untuk cek Non Pemeriksaan / lainnya)
        $kategoriUtama = optional($first->kategoriLayanan)->nama_kategori
            ?? optional(optional($first->layanan)->kategoriLayanan)->nama_kategori
            ?? null;

        $summary = (object) [
            'kode_transaksi'        => $first->kode_transaksi,
            'pasien'                => $first->pasien,
            'kunjungan'             => $first->kunjungan,
            'metode_pembayaran'     => optional($paySource->metodePembayaran)->nama_metode ?? '-',
            'tanggal_transaksi'     => $paySource->tanggal_transaksi,

            'total_sebelum_diskon'  => $totalSebelum,
            'diskon_nominal'        => $diskonNominal,
            'total_setelah_diskon'  => $totalSesudah,

            'uang_yang_diterima'    => (float) ($paySource->uang_yang_diterima ?? 0),
            'kembalian'             => (float) ($paySource->kembalian ?? 0),

            'kategori_utama'        => $kategoriUtama,
        ];

        // dd($summary->total_sebelum_diskon);

        $namaPT = 'Royal Klinik';

        return view('kasir.riwayat-transaksi.kwitansi-transaksi-layanan', [
            'items'   => $items,
            'summary' => $summary,
            'namaPT'  => $namaPT,
        ]);
    }
}
