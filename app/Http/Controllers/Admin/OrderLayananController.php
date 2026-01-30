<?php

namespace App\Http\Controllers\Admin;

use App\Models\Poli;
use App\Models\Pasien;
use App\Models\Layanan;
use App\Models\Kunjungan;
use App\Models\JadwalDokter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\KategoriLayanan;
use App\Models\PenjualanLayanan;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\OrderLayanan;
use App\Models\OrderLayananDetail;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\ValidationException;

class OrderLayananController extends Controller
{
    public function index()
    {
        $dataLayanan = Layanan::with('kategoriLayanan')->orderBy('nama_layanan')->get();
        $dataPoli = Poli::all();

        return view('admin.order-layanan.order-layanan', compact('dataLayanan', 'dataPoli'));
    }

    public function getDataOrderLayanan()
    {
        // Sekarang kita query langsung ke tabel Header (order_layanan)
        // Jauh lebih ringan karena 1 baris = 1 transaksi
        $query = OrderLayanan::query()
            ->join('pasien', 'order_layanan.pasien_id', '=', 'pasien.id')
            ->select([
                'order_layanan.id',
                'order_layanan.kode_transaksi',
                'order_layanan.pasien_id',
                'order_layanan.tanggal_order', // Gunakan tanggal_order dari tabel baru
                'order_layanan.status_order_layanan', // Gunakan kolom status yang baru
                'order_layanan.total_bayar',
                'pasien.nama_pasien',
            ])
            ->orderByDesc('order_layanan.tanggal_order');

        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('kode_transaksi', function ($order) {
                return $order->kode_transaksi ?? '-';
            })

            // 1. Nama Pasien
            ->addColumn('nama_pasien', function ($order) {
                return $order->nama_pasien ?? '-';
            })

            // 2. Nama Layanan (Ambil dari Relasi Detail) dalam bentuk List
            ->addColumn('nama_layanan', function ($order) {
                // Ambil semua nama layanan unik
                $layananArray = $order->details->map(function ($detail) {
                    return $detail->layanan->nama_layanan ?? '-';
                })->unique();

                if ($layananArray->isEmpty()) {
                    return '-';
                }

                // Susun menjadi HTML List
                $html = '<ul class="list-disc list-inside text-[11px] space-y-0.5">';
                foreach ($layananArray as $nama) {
                    $html .= '<li>' . $nama . '</li>';
                }
                $html .= '</ul>';

                return $html;
            })

            // 3. Kategori (Ambil dari Relasi Detail -> Layanan -> Kategori)
            ->addColumn('kategori_layanan', function ($order) {
                $kategoriNames = $order->details->map(function ($detail) {
                    return $detail->layanan->kategori->nama_kategori ?? '-';
                })->unique()->implode(', ');

                return $kategoriNames ?: '-';
            })

            // 4. Jumlah (Total Qty dalam satu order)
            ->addColumn('jumlah', function ($order) {
                return $order->details->sum('qty') . ' Item';
            })

            // 5. Total Tagihan
            ->addColumn('total_tagihan', function ($order) {
                return 'Rp ' . number_format($order->total_bayar, 0, ',', '.');
            })

            // 6. Status (Warna Label)
            ->addColumn('status', function ($order) {
                $status = $order->status_order_layanan ?? 'Belum Bayar';

                $color = ($status === 'Sudah Bayar')
                    ? 'bg-green-100 text-green-700'
                    : 'bg-yellow-100 text-yellow-700';

                return '<span class="px-2 py-1 rounded-lg text-xs font-semibold ' . $color . '">' . $status . '</span>';
            })

            // 7. Tanggal Transaksi
            ->addColumn('tanggal_transaksi', function ($order) {
                if (!$order->tanggal_order) {
                    return '-';
                }

                // Menggunakan translatedFormat agar bulan menjadi Bahasa Indonesia (Januari, dsb)
                // 'd F Y' -> d (tgl), F (Bulan Panjang), Y (Tahun 4 digit)
                return Carbon::parse($order->tanggal_order)->translatedFormat('d F Y');
            })

            // 8. Action
            ->addColumn('action', function ($order) {
                return '
                <div class="flex items-center justify-center gap-1">
                    <button type="button" 
                data-id="' . $order->id . '" 
                data-kode-transaksi="' . $order->kode_transaksi . '" 
                class="btn-update-order-layanan px-3 py-1.5 rounded-lg text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-200 hover:bg-sky-100 transition-colors">
                <i class="fa-solid fa-pen-to-square"></i> Edit
            </button>
                    <button type="button" data-id="' . $order->id . '" data-kode="' . $order->kode_transaksi . '"
                        class="btn-delete-order-layanan px-3 py-1.5 rounded-lg text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition-colors">
                        <i class="fa-solid fa-trash-can"></i> Hapus
                    </button>
                </div>';
            })
            ->rawColumns(['status', 'action', 'nama_layanan'])
            ->make(true);
    }


    /* =========================================================
     *  AJAX: JADWAL DOKTER HARI INI BERDASAR POLI
     * ========================================================= */
    public function getJadwalDokterHariIni(Request $request)
    {
        $request->validate([
            'poli_id' => 'required|exists:poli,id',
        ]);

        $poliId = $request->poli_id;

        // Asumsi kolom 'hari' berisi: Senin, Selasa, ... (Indonesia)
        $hariIni = Carbon::now()->locale('id')->dayName; // "Senin", dll.

        $jadwal = JadwalDokter::with('dokter')
            ->where('poli_id', $poliId)
            ->where('hari', $hariIni)   // sesuaikan kalau format beda
            ->orderBy('jam_awal')
            ->get()
            ->map(function ($row) {
                return [
                    'id'          => $row->id,
                    'dokter_id'   => $row->dokter_id,
                    'nama_dokter' => $row->dokter->nama_dokter ?? '-',
                    'jam_awal'    => $row->jam_awal,
                    'jam_selesai' => $row->jam_selesai,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $jadwal,
        ]);
    }

    public function createDataOrderLayanan(Request $request)
    {
        // 1) Validasi input dasar
        $validated = $request->validate([
            'pasien_id'      => 'required|exists:pasien,id',
            'total_tagihan'  => 'required|numeric|min:0',

            'items'                         => 'required|array|min:1',
            'items.*.layanan_id'            => 'required|exists:layanan,id',
            'items.*.kategori_layanan_id'   => 'required|exists:kategori_layanan,id',
            'items.*.jumlah'                => 'required|integer|min:1',
            'items.*.total_tagihan'         => 'required|numeric|min:0',
        ], [
            'required' => 'Field ini wajib diisi.',
            'exists'   => 'Data tidak valid.',
            'min'      => 'Nilai minimal tidak terpenuhi.',
        ]);

        // dd($validated['items']);

        // Identifikasi Kategori
        $kategoriIds   = collect($validated['items'])->pluck('kategori_layanan_id')->unique()->values();
        $kategoriList  = KategoriLayanan::whereIn('id', $kategoriIds)->get(['id', 'nama_kategori']);
        $kategoriMap   = $kategoriList->pluck('nama_kategori', 'id');

        // Cek apakah ada item dengan kategori "Pemeriksaan"
        $isPemeriksaan = $kategoriList->contains(fn($k) => $k->nama_kategori === 'Pemeriksaan');

        // 2) Validasi tambahan khusus jika ada layanan Pemeriksaan
        if ($isPemeriksaan) {
            $request->validate([
                'poli_id'          => 'required|exists:poli,id',
                'jadwal_dokter_id' => 'required|exists:jadwal_dokter,id',
            ], [
                'poli_id.required'          => 'Poli harus dipilih untuk layanan pemeriksaan.',
                'jadwal_dokter_id.required' => 'Jadwal dokter hari ini harus dipilih.',
            ]);
        }

        DB::beginTransaction();

        try {
            $kunjunganId = null;
            $orderId = null;
            $message = "";

            if ($isPemeriksaan) {
                /**
                 * ALUR 1: HANYA BUAT KUNJUNGAN
                 * (Pembayaran & Detail akan diurus Dokter/EMR nanti)
                 */
                $poliId = (int) $request->poli_id;

                // Validasi relasi Layanan - Poli (untuk layanan non-global)
                $pemeriksaanItems = collect($validated['items'])->filter(function ($it) use ($kategoriMap) {
                    return ($kategoriMap[(int)$it['kategori_layanan_id']] ?? null) === 'Pemeriksaan';
                });
                $layananPemeriksaanIds = $pemeriksaanItems->pluck('layanan_id')->unique();
                $layanans = Layanan::with('layananPoli:id')->whereIn('id', $layananPemeriksaanIds)->get();

                foreach ($layanans as $l) {
                    if ((int)$l->is_global === 0) {
                        if (!$l->layananPoli->pluck('id')->contains($poliId)) {
                            throw ValidationException::withMessages([
                                'poli_id' => ['Poli tidak sesuai dengan layanan pemeriksaan yang dipilih.'],
                            ]);
                        }
                    }
                }

                // Validasi Jadwal Dokter
                $hariIni = \Carbon\Carbon::now()->locale('id')->dayName;
                $jadwal = JadwalDokter::where('id', $request->jadwal_dokter_id)
                    ->where('poli_id', $poliId)
                    ->where('hari', $hariIni)
                    ->first();

                if (!$jadwal) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'jadwal_dokter_id' => ['Jadwal dokter tidak ditemukan atau tidak aktif hari ini.'],
                    ]);
                }

                // Generate No Antrian
                $tanggal = today();
                $lastRow = Kunjungan::where('poli_id', $poliId)
                    ->whereDate('tanggal_kunjungan', $tanggal)
                    ->orderByRaw('CAST(no_antrian AS UNSIGNED) DESC')
                    ->lockForUpdate()
                    ->first();

                $formattedNo = str_pad(($lastRow ? (int)$lastRow->no_antrian : 0) + 1, 3, '0', STR_PAD_LEFT);

                // Buat Data Kunjungan
                $kunjungan = Kunjungan::create([
                    'jadwal_dokter_id'  => $jadwal->id,
                    'dokter_id'         => $jadwal->dokter_id,
                    'poli_id'           => $jadwal->poli_id,
                    'pasien_id'         => $validated['pasien_id'],
                    'tanggal_kunjungan' => $tanggal,
                    'no_antrian'        => $formattedNo,
                    'status'            => 'Pending',
                    'keluhan_awal'      => null,
                ]);

                $kunjunganId = $kunjungan->id;
                $message = "Kunjungan berhasil dibuat. Silakan pasien menuju poli.";
            } else {
                /**
                 * ALUR 2: BUAT ORDER LAYANAN (Murni Non-Pemeriksaan / Ritel)
                 */
                $kodeTransaksi = 'TRX-' . strtoupper(uniqid());

                $order = OrderLayanan::create([
                    'kode_transaksi' => $kodeTransaksi,
                    'pasien_id'      => $validated['pasien_id'],
                    'total_bayar'    => $validated['total_tagihan'],
                    'potongan_pesanan'         => $request->diskon ?? 0, // Ambil dari input diskon global
                    'tanggal_order'  => now(),
                    'status_order_layanan'         => 'Belum Bayar',
                ]);

                foreach ($validated['items'] as $item) {
                    // Hitung harga satuan agar benar jika qty > 1
                    $qty = (int)$item['jumlah'];
                    $totalTagihanItem = (float)$item['total_tagihan'];
                    $hargaSatuan = $totalTagihanItem / $qty;

                    OrderLayananDetail::create([
                        'order_layanan_id' => $order->id,
                        'layanan_id'       => $item['layanan_id'],
                        'qty'              => $qty,
                        'harga_satuan'     => $hargaSatuan,
                        'total_harga_item' => $totalTagihanItem,
                    ]);
                }

                $orderId = $order->id;
                $message = "Order layanan berhasil dibuat.";
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => [
                    'kunjungan_id'   => $kunjunganId,
                    'order_id'       => $orderId,
                    'is_pemeriksaan' => $isPemeriksaan
                ],
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($e instanceof ValidationException) throw $e;

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses permintaan.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function searchPasien(Request $request)
    {
        $keyword = $request->get('q');

        $pasien = Pasien::query()
            ->where('nama_pasien', 'like', "%{$keyword}%")
            ->orWhere('no_emr', 'like', "%{$keyword}%")
            ->limit(10)
            ->get(['id', 'nama_pasien', 'no_emr', 'jenis_kelamin']);

        return response()->json([
            'data' => $pasien
        ]);
    }

    public function getDataOrderLayananById($kodeTransaksi)
    {
        // Query ke tabel Header, lalu Eager Load relasi detailnya
        $order = OrderLayanan::with([
            'pasien',
            'orderLayananDetail.layanan.kategoriLayanan', // Ambil data layanan & kategori lewat detail
        ])
            ->where('kode_transaksi', $kodeTransaksi)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Data order layanan tidak ditemukan.',
            ], 404);
        }

        // Format tanggal untuk input type="datetime-local" (Y-m-d\TH:i)
        $tanggalOrder = $order->tanggal_order
            ? \Carbon\Carbon::parse($order->tanggal_order)->format('Y-m-d\TH:i')
            : null;

        // Mapping item dari tabel order_layanan_detail
        $items = $order->details->map(function ($detail) {
            return [
                'id'                    => $detail->id, // id detail
                'layanan_id'            => $detail->layanan_id,
                'nama_layanan'          => optional($detail->layanan)->nama_layanan,
                'kategori_layanan_id'   => optional($detail->layanan)->kategori_layanan_id,
                'kategori_layanan_nama' => optional($detail->layanan->kategori)->nama_kategori,
                'jumlah'                => $detail->qty,
                'harga_satuan'          => $detail->harga_satuan,
                'total_tagihan'         => $detail->total_harga_item,
            ];
        });

        // Cek apakah ada layanan "Pemeriksaan" di dalam item
        // Ini penting untuk JS kamu menentukan apakah section Poli harus muncul
        $isPemeriksaan = $items->contains(function ($item) {
            return strtolower($item['kategori_layanan_nama'] ?? '') === 'pemeriksaan';
        });

        return response()->json([
            'success' => true,
            'data' => [
                // Header Transaksi
                'id'                   => $order->id,
                'kode_transaksi'       => $order->kode_transaksi,
                'tanggal_transaksi'    => $tanggalOrder,
                'status'               => $order->status_order_layanan,
                'potongan_pesanan'     => $order->potongan_pesanan,
                'total_tagihan'        => $order->total_bayar,

                // Data Pasien
                'pasien' => [
                    'id'            => $order->pasien_id,
                    'nama_pasien'   => optional($order->pasien)->nama_pasien,
                    'no_emr'        => optional($order->pasien)->no_emr,
                    'jenis_kelamin' => optional($order->pasien)->jenis_kelamin,
                ],

                // Daftar Item Layanan (Detail)
                'items' => $items,

                // Flag untuk JS (opsional tapi membantu)
                'has_pemeriksaan' => $isPemeriksaan,
            ],
        ]);
    }

    public function getDataPoli(Request $request)
    {
        // 1. Buat layanan_id jadi optional (nullable)
        $request->validate([
            'layanan_id'   => 'nullable|array',
            'layanan_id.*' => 'integer|exists:layanan,id',
        ]);

        // 2. Jika tidak ada layanan yang dipilih, kembalikan semua poli
        if (!$request->has('layanan_id') || empty($request->layanan_id)) {
            $allPoli = Poli::select('id', 'nama_poli')->orderBy('nama_poli')->get();
            return response()->json(['success' => true, 'data' => $allPoli, 'mode' => 'all']);
        }

        // 3. Logika filter tetap sama seperti yang kamu buat
        $layanans = Layanan::with('layananPoli:id,nama_poli')
            ->whereIn('id', $request->layanan_id)
            ->get(['id', 'is_global']);

        $restricted = $layanans->filter(fn($l) => (int)$l->is_global === 0);

        if ($restricted->isEmpty()) {
            $allPoli = Poli::select('id', 'nama_poli')->orderBy('nama_poli')->get();
            return response()->json(['success' => true, 'data' => $allPoli, 'mode' => 'all']);
        }

        $allowedIds = null;
        foreach ($restricted as $l) {
            // Asumsi relasi di model Layanan bernama 'layananPoli' (BelongsToMany ke Poli)
            $ids = $l->layananPoli->pluck('id')->toArray();
            $allowedIds = is_null($allowedIds) ? $ids : array_values(array_intersect($allowedIds, $ids));
        }

        $allowedPoli = Poli::select('id', 'nama_poli')
            ->whereIn('id', $allowedIds ?: [])
            ->orderBy('nama_poli')
            ->get();

        return response()->json(['success' => true, 'data' => $allowedPoli, 'mode' => 'filtered']);
    }

    /**
     * Update data order layanan (multi item per kode_transaksi)
     */
    public function updateDataOrderLayanan(Request $request)
    {
        // 1. VALIDASI DASAR
        $validated = $request->validate([
            'order_layanan_id'            => 'required|exists:order_layanan,id',
            'pasien_id'                   => 'required|exists:pasien,id',
            'items'                       => 'required|array|min:1',
            'items.*.layanan_id'          => 'required|exists:layanan,id',
            'items.*.kategori_layanan_id' => 'required|exists:kategori_layanan,id',
            'items.*.jumlah'              => 'required|integer|min:1',
            'items.*.total_tagihan'       => 'required|numeric|min:0',
            'total_tagihan'               => 'required|numeric|min:0',
        ]);

        // dd($validated);

        $itemsInput = collect($validated['items']);

        // 2. CEK KATEGORI PEMERIKSAAN
        $kategoriIds = $itemsInput->pluck('kategori_layanan_id')->unique()->all();
        $kategoriList = KategoriLayanan::whereIn('id', $kategoriIds)->get()->keyBy('id');

        $hasPemeriksaan = $kategoriList->contains(function ($kat) {
            return strtolower($kat->nama_kategori) === 'pemeriksaan';
        });

        DB::beginTransaction();
        try {
            // Ambil data order lama
            $oldOrder = OrderLayanan::lockForUpdate()->findOrFail($validated['order_layanan_id']);
            $kodeTransaksi = $oldOrder->kode_transaksi;

            if ($hasPemeriksaan) {
                /**
                 * SKENARIO A: BERUBAH MENJADI PEMERIKSAAN
                 * (Hapus data di order_layanan, pindah ke kunjungan)
                 */
                $request->validate([
                    'poli_id'          => 'required|exists:poli,id',
                    'jadwal_dokter_id' => 'required|exists:jadwal_dokter,id',
                ]);

                $jadwal = JadwalDokter::findOrFail($request->jadwal_dokter_id);

                // 1. Buat Kunjungan (Alur Medis)
                $tanggal = today();
                $lastRow = Kunjungan::where('poli_id', $request->poli_id)
                    ->whereDate('tanggal_kunjungan', $tanggal)
                    ->orderByRaw('CAST(no_antrian AS UNSIGNED) DESC')
                    ->lockForUpdate()->first();

                $noAntrian = str_pad(($lastRow ? (int)$lastRow->no_antrian : 0) + 1, 3, '0', STR_PAD_LEFT);

                $kunjungan = Kunjungan::create([
                    'jadwal_dokter_id'  => $jadwal->id,
                    'dokter_id'         => $jadwal->dokter_id,
                    'poli_id'           => $request->poli_id,
                    'pasien_id'         => $validated['pasien_id'],
                    'tanggal_kunjungan' => $tanggal,
                    'no_antrian'        => $noAntrian,
                    'status'            => 'Pending',
                    'kode_transaksi'    => $kodeTransaksi, // Keep track kodenya
                ]);

                // 2. HAPUS data lama di order_layanan karena sudah pindah jalur ke Medis
                $oldOrder->details()->delete();
                $oldOrder->delete();

                $msg = "Order diubah menjadi Kunjungan Pemeriksaan.";
            } else {
                /**
                 * SKENARIO B: TETAP / BERUBAH MENJADI NON-PEMERIKSAAN
                 * (Update table order_layanan & order_layanan_detail)
                 */
                $oldOrder->update([
                    'pasien_id'     => $validated['pasien_id'],
                    'total_tagihan' => $validated['total_tagihan'],
                    'tanggal_order' => now(),
                ]);

                // Sync Detail: Hapus yang lama, insert yang baru
                $oldOrder->details()->delete();

                foreach ($itemsInput as $item) {
                    OrderLayananDetail::create([
                        'order_layanan_id'    => $oldOrder->id,
                        'layanan_id'          => $item['layanan_id'],
                        'kategori_layanan_id' => $item['kategori_layanan_id'],
                        'jumlah'              => $item['jumlah'],
                        'harga_satuan'        => (float)$item['total_tagihan'] / $item['jumlah'],
                        'subtotal'            => $item['total_tagihan'],
                    ]);
                }
                $msg = "Order Layanan berhasil diperbarui.";
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * Hapus data order layanan berdasarkan kode_transaksi.
     *
     * - Ambil SEMUA baris PenjualanLayanan dengan kode_transaksi tsb.
     * - Untuk setiap kunjungan_id yang terlibat:
     *      - Jika kunjungan tersebut HANYA dipakai oleh baris-baris pada kode_transaksi ini,
     *        maka kunjungan dihapus (dan baris PenjualanLayanan yang terkait ikut terhapus via FK cascade).
     * - Setelah itu, hapus semua sisa baris PenjualanLayanan dengan kode_transaksi tsb
     *   (jika masih ada yang belum terhapus oleh cascade).
     */
    public function deleteDataOrderLayanan($kodeTransaksi)
    {
        try {
            DB::beginTransaction();

            // Ambil semua order untuk kode_transaksi ini (lock for update supaya aman secara konkurensi)
            $orders = PenjualanLayanan::lockForUpdate()
                ->where('kode_transaksi', $kodeTransaksi)
                ->get();

            if ($orders->isEmpty()) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Data order tidak ditemukan.',
                ], 404);
            }

            // Ambil semua kunjungan_id unik yang tidak null dari kumpulan order ini
            $kunjunganIds = $orders->pluck('kunjungan_id')
                ->filter()      // buang null
                ->unique()
                ->values();

            foreach ($kunjunganIds as $kunjunganId) {
                // Total semua order (semua kode_transaksi) yang memakai kunjungan ini
                $totalOrderKunjungan = PenjualanLayanan::where('kunjungan_id', $kunjunganId)->count();

                // Berapa banyak baris pada kode_transaksi ini yang pakai kunjungan tsb
                $totalOrderKunjunganDiTransaksiIni = $orders
                    ->where('kunjungan_id', $kunjunganId)
                    ->count();

                // Jika kunjungan hanya dipakai oleh baris-baris di transaksi ini
                if ($totalOrderKunjungan === $totalOrderKunjunganDiTransaksiIni) {
                    // Hapus kunjungan
                    // Dengan FK cascadeOnDelete dari penjualan_layanan.kunjungan_id → kunjungan.id,
                    // semua PenjualanLayanan yang terkait kunjungan ini ikut terhapus otomatis.
                    Kunjungan::where('id', $kunjunganId)->delete();
                }
            }

            // Setelah penghapusan kunjungan (yang bisa menghapus beberapa PenjualanLayanan via cascade),
            // pastikan TIDAK ada lagi baris dengan kode_transaksi ini yang tersisa.
            PenjualanLayanan::where('kode_transaksi', $kodeTransaksi)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order layanan berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data.',
                // 'error'   => $e->getMessage(), // boleh di-uncomment untuk debugging
            ], 500);
        }
    }
}
