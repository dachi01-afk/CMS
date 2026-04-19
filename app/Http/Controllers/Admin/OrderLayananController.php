<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalDokter;
use App\Models\KategoriLayanan;
use App\Models\Kunjungan;
use App\Models\Layanan;
use App\Models\OrderLayanan;
use App\Models\OrderLayananDetail;
use App\Models\Pasien;
use App\Models\PenjualanLayanan;
use App\Models\Poli;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class OrderLayananController extends Controller
{
    public function index()
    {
        $dataPoli = Poli::all();

        return view('admin.order-layanan.order-layanan', compact('dataPoli'));
    }

    public function getDataOrderLayanan()
    {
        $dataOrderLayanan = OrderLayanan::with([
            'pasien'
        ])->latest();

        return DataTables::of($dataOrderLayanan)
            ->addIndexColumn()
            ->editColumn('nama_pasien', function ($dataOrderan) {
                return $dataOrderan->pasien->nama_pasien ?? '-';
            })
            ->editColumn('tanggal_order', function ($dataOrderan) {
                return $dataOrderan->getformatTanggalOrder();
            })
            ->editColumn('subtotal', function ($dataOrderan) {
                return $dataOrderan->total_bayar_rupiah;
            })
            ->addColumn('action', function ($dataOrderan) {
                $urlOrderLayananDetail = route('get.data.detail.order.layanan', [
                    'kodeTransaksi' => $dataOrderan->kode_transaksi
                ]);

                $urlOrderLayananUpdate = route('order.layanan.get.data.order.layanan.by.id', [
                    'kodeTransaksi' => $dataOrderan->kode_transaksi
                ]);

                $html = '
        <div class="flex items-center gap-2">
            <button
                type="button"
                class="btn-detail-order-layanan inline-flex items-center gap-2 rounded-xl bg-sky-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md transition duration-200 hover:bg-sky-600 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-sky-300 active:scale-95"
                data-kode-transaksi="' . $dataOrderan->kode_transaksi . '"
                data-url-detail-order-layanan="' . $urlOrderLayananDetail . '"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5z" />
                </svg>
                Detail
            </button>

            <button
                type="button"
                class="btn-open-modal-update-order-layanan inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md transition duration-200 hover:scale-105 hover:from-amber-500 hover:to-orange-600 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-orange-300 active:scale-95"
                data-kode-transaksi="' . $dataOrderan->kode_transaksi . '"
                data-url-update-order-layanan="' . $urlOrderLayananUpdate . '"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487a2.25 2.25 0 1 1 3.182 3.182L8.25 19.463 4.5 20.25l.787-3.75L16.862 4.487z" />
                </svg>
                Edit
            </button>
    ';

                if (Auth::check() && Auth::user()->role === 'Super Admin') {
                    $html .= '
            <button
                type="button"
                class="btn-delete-order-layanan inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md transition duration-200 hover:scale-105 hover:from-rose-600 hover:to-red-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-red-300 active:scale-95"
                data-kode-transaksi="' . $dataOrderan->kode_transaksi . '"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 7.5h12m-1.5 0-.422 10.133A2.25 2.25 0 0 1 13.83 19.5h-3.66a2.25 2.25 0 0 1-2.248-1.867L7.5 7.5m3-3h3a1.5 1.5 0 0 1 1.5 1.5V7.5h-6V6a1.5 1.5 0 0 1 1.5-1.5Z" />
                </svg>
                Hapus
            </button>
        ';
                }

                $html .= '</div>';

                return $html;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getDetailOrderLayanan($kodeTransaksi)
    {
        $dataOrderLayanan = OrderLayanan::with([
            'pasien',
            'orderLayananDetail',
            'orderLayananDetail.layanan',
            'orderLayananDetail.layanan.kategoriLayanan'
        ])->where('kode_transaksi', $kodeTransaksi)->firstOrFail();

        return response()->json([
            'dataOrderLayanan' => [
                'id' => $dataOrderLayanan->id,
                'kode_transaksi' => $dataOrderLayanan->kode_transaksi,
                'tanggal_order' => $dataOrderLayanan->getFormatTanggalOrder(),
                'tanggal_pembayaran' => $dataOrderLayanan->tanggal_pembayaran,
                'nama_pasien' => $dataOrderLayanan->pasien->nama_pasien ?? '-',
                'nama_metode_pembayaran' => $dataOrderLayanan->metodePembayaran->nama_metode_pembayaran ?? '-',
                'subtotal' => $dataOrderLayanan->subtotal_rupiah,
                'diskon_tipe' => $dataOrderLayanan->diskon_tipe,
                'diskon_nilai' => $dataOrderLayanan->diskon_nilai,
                'potongan_pesanan' => $dataOrderLayanan->potongan_pesanan,
                'total_bayar' => $dataOrderLayanan->total_bayar_rupiah,
                'uang_yang_diterima' => $dataOrderLayanan->uang_yang_diterima,
                'kembalian' => $dataOrderLayanan->kembalian,
                'status_order_layanan' => $dataOrderLayanan->status_order_layanan,
                'bukti_pembayaran' => $dataOrderLayanan->bukti_pembayaran,
                'bukti_pembayaran_url' => $dataOrderLayanan->bukti_pembayaran
                    ? asset('storage/' . $dataOrderLayanan->bukti_pembayaran)
                    : null,
            ],
            'dataDetailOrderLayanan' => $dataOrderLayanan->orderLayananDetail->map(fn($item) => [
                'id' => $item->id,
                'nama_layanan' => $item->layanan->nama_layanan ?? '-',
                'qty' => $item->qty,
                'harga_satuan' => $item->harga_satuan,
                'total_harga_item' => $item->total_harga_item,
            ]),
        ]);
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
                    throw ValidationException::withMessages([
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
                    'kode_transaksi'            => $kodeTransaksi,
                    'pasien_id'                 => $validated['pasien_id'],
                    'total_bayar'               => $validated['total_tagihan'],
                    'subtotal'                  => $validated['total_tagihan'],
                    'potongan_pesanan'          => $request->diskon ?? 0, // Ambil dari input diskon global
                    'tanggal_order'             => now(),
                    'status_order_layanan'      => 'Belum Bayar',
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

    public function searchLayanan(Request $request)
    {
        $search = $request->q;

        $dataLayanan = Layanan::query()->when($search, function ($namaLayanan) use ($search) {
            $namaLayanan->where('nama_layanan', 'like', "%{$search}%");
        })->get();

        return response()->json($dataLayanan->map(function ($item) {
            return [
                'id' => $item->id,
                'nama_layanan' => $item->nama_layanan
            ];
        }));
    }

    public function detailDataLayanan($id)
    {
        $dataLayanan = Layanan::with(['kategoriLayanan'])->findOrFail($id);

        return response()->json([
            'id' => $dataLayanan->id,
            'nama_layanan' => $dataLayanan->nama_layanan,
            'kategori_id' => $dataLayanan->kategoriLayanan?->id,
            'nama_kategori' => $dataLayanan->kategoriLayanan?->nama_kategori,
            'harga_layanan' => $dataLayanan->harga_layanan,
        ]);
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
        $validated = $request->validate([
            'order_layanan_id'            => 'required|exists:order_layanan,id',
            'pasien_id'                   => 'required|exists:pasien,id',
            'items'                       => 'required|array|min:1',
            'items.*.layanan_id'          => 'required|exists:layanan,id',
            'items.*.kategori_layanan_id' => 'required|exists:kategori_layanan,id',
            'items.*.jumlah'              => 'required|integer|min:1',
            'items.*.total_tagihan'       => 'required|numeric|min:0',
            'total_tagihan'               => 'required|numeric|min:0',
            'poli_id'                     => 'nullable|exists:poli,id',
            'jadwal_dokter_id'            => 'nullable|exists:jadwal_dokter,id',
        ]);

        $itemsInput = collect($validated['items']);

        $kategoriIds = $itemsInput->pluck('kategori_layanan_id')->unique()->values()->all();

        $kategoriList = KategoriLayanan::whereIn('id', $kategoriIds)->get()->keyBy('id');

        $containsPemeriksaan = $kategoriList->contains(function ($kat) {
            return strtolower(trim($kat->nama_kategori)) === 'pemeriksaan';
        });

        $allPemeriksaan = $kategoriList->every(function ($kat) {
            return strtolower(trim($kat->nama_kategori)) === 'pemeriksaan';
        });

        if ($containsPemeriksaan && !$allPemeriksaan) {
            return response()->json([
                'success' => false,
                'message' => 'Item pemeriksaan tidak boleh digabung dengan item non-pemeriksaan dalam satu transaksi.',
            ], 422);
        }

        try {
            $oldOrder = OrderLayanan::findOrFail($validated['order_layanan_id']);
            $kodeTransaksi = $oldOrder->kode_transaksi;

            if ($containsPemeriksaan) {
                $request->validate([
                    'poli_id'          => 'required|exists:poli,id',
                    'jadwal_dokter_id' => 'required|exists:jadwal_dokter,id',
                ]);

                $jadwal = JadwalDokter::findOrFail($request->jadwal_dokter_id);

                $existingKunjungan = Kunjungan::where('kode_transaksi', $kodeTransaksi)->first();
                if ($existingKunjungan) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kunjungan untuk transaksi ini sudah ada.',
                    ], 422);
                }

                $tanggalKunjungan = now()->toDateString();

                $lastRow = Kunjungan::where('poli_id', $request->poli_id)
                    ->whereDate('tanggal_kunjungan', $tanggalKunjungan)
                    ->orderByRaw('CAST(no_antrian AS UNSIGNED) DESC')
                    ->first();

                $noAntrian = str_pad(
                    ($lastRow ? ((int) $lastRow->no_antrian + 1) : 1),
                    3,
                    '0',
                    STR_PAD_LEFT
                );

                Kunjungan::create([
                    'jadwal_dokter_id'  => $jadwal->id,
                    'dokter_id'         => $jadwal->dokter_id,
                    'poli_id'           => $request->poli_id,
                    'pasien_id'         => $validated['pasien_id'],
                    'tanggal_kunjungan' => $tanggalKunjungan,
                    'no_antrian'        => $noAntrian,
                    'status'            => 'Pending',
                    'kode_transaksi'    => $kodeTransaksi,
                ]);

                $oldOrder->details()->delete();
                $oldOrder->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Order berhasil diubah menjadi kunjungan pemeriksaan.',
                ]);
            }

            $oldOrder->update([
                'pasien_id'   => $validated['pasien_id'],
                'subtotal'    => (float) $validated['total_tagihan'],
                'total_bayar' => (float) $validated['total_tagihan'],
            ]);

            $oldOrder->details()->delete();

            foreach ($itemsInput as $item) {
                $jumlah = (int) $item['jumlah'];
                $totalTagihanItem = (float) $item['total_tagihan'];
                $hargaSatuan = $jumlah > 0 ? $totalTagihanItem / $jumlah : 0;

                OrderLayananDetail::create([
                    'order_layanan_id' => $oldOrder->id,
                    'layanan_id'       => $item['layanan_id'],
                    'qty'              => $jumlah,
                    'harga_satuan'     => $hargaSatuan,
                    'total_harga_item' => $totalTagihanItem,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order layanan berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
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
            $orders = OrderLayanan::where('kode_transaksi', $kodeTransaksi)->get();

            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data order tidak ditemukan.',
                ], 404);
            }

            $orderIds = $orders->pluck('id');

            OrderLayananDetail::whereIn('order_layanan_id', $orderIds)->delete();
            OrderLayanan::whereIn('id', $orderIds)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order layanan berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data.',
                // 'error' => $e->getMessage(),
            ], 500);
        }
    }
}
