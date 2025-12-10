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
        // Query digroup per kode_transaksi
        $query = PenjualanLayanan::query()
            ->join('pasien', 'penjualan_layanan.pasien_id', '=', 'pasien.id')
            ->join('layanan', 'penjualan_layanan.layanan_id', '=', 'layanan.id')
            ->join(
                'kategori_layanan',
                'penjualan_layanan.kategori_layanan_id',
                '=',
                'kategori_layanan.id'
            )
            ->selectRaw('
            MIN(penjualan_layanan.id) as id,
            penjualan_layanan.kode_transaksi,
            penjualan_layanan.pasien_id,
            pasien.nama_pasien,

            MIN(penjualan_layanan.tanggal_transaksi) as tanggal_transaksi,
            MIN(penjualan_layanan.status) as status,

            SUM(penjualan_layanan.jumlah) as jumlah,
            SUM(penjualan_layanan.total_tagihan) as total_tagihan,

            GROUP_CONCAT(DISTINCT layanan.nama_layanan
                ORDER BY layanan.nama_layanan SEPARATOR ", ") as nama_layanan,

            GROUP_CONCAT(DISTINCT kategori_layanan.nama_kategori
                ORDER BY kategori_layanan.nama_kategori SEPARATOR ", ") as kategori_layanan
        ')
            ->groupBy(
                'penjualan_layanan.kode_transaksi',
                'penjualan_layanan.pasien_id',
                'pasien.nama_pasien'
            )
            ->orderByDesc(DB::raw('MIN(penjualan_layanan.tanggal_transaksi)'));

        return DataTables::of($query)
            ->addIndexColumn()

            // Nama Pasien
            ->addColumn('nama_pasien', function ($order) {
                return $order->nama_pasien ?? '-';
            })

            // Nama Layanan (sudah hasil GROUP_CONCAT)
            ->addColumn('nama_layanan', function ($order) {
                return $order->nama_layanan ?? '-';
            })

            // Kategori (bisa "Pemeriksaan", "Non Pemeriksaan", atau gabungan)
            ->addColumn('kategori_layanan', function ($order) {
                return $order->kategori_layanan ?? '-';
            })

            // Jumlah (total jumlah semua layanan)
            ->addColumn('jumlah', function ($order) {
                return $order->jumlah ?? 1;
            })

            // Total Tagihan (total semua layanan dalam transaksi)
            ->addColumn('total_tagihan', function ($order) {
                $total = $order->total_tagihan ?? 0;
                return 'Rp ' . number_format($total, 0, ',', '.');
            })

            // Status (pakai MIN(status) → asumsi semua item status-nya sama)
            ->addColumn('status', function ($order) {
                $status = $order->status ?? 'Belum Bayar';

                $color = $status === 'Sudah Bayar'
                    ? 'bg-green-100 text-green-700'
                    : 'bg-yellow-100 text-yellow-700';

                return '<span class="text-center py-1 rounded-lg text-xs font-semibold ' . $color . '">' . $status . '</span>';
            })

            // Tanggal Transaksi (MIN dari semua baris dalam transaksi)
            ->addColumn('tanggal_transaksi', function ($order) {
                return $order->tanggal_transaksi
                    ? date('d M Y H:i', strtotime($order->tanggal_transaksi))
                    : '-';
            })

            // Kode Transaksi
            ->addColumn('kode_transaksi', function ($order) {
                return $order->kode_transaksi ?? '-';
            })

            // Tombol Action (pakai MIN(id) sebagai "id transaksi" wakil)
            ->addColumn('action', function ($order) {
                return '
        <div class="flex items-center justify-center gap-1">
            <button 
                type="button"
                data-kode-transaksi="' . $order->kode_transaksi . '"
                class="btn-update-order-layanan inline-flex items-center gap-1
                       px-3 py-1.5 rounded-lg text-[11px] font-semibold
                       bg-sky-50 text-sky-700 border border-sky-200
                       hover:bg-sky-100 hover:text-sky-900
                       focus:outline-none focus:ring-1 focus:ring-sky-400
                       transition-colors duration-150">
                <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                <span>Edit</span>
            </button>

            <button 
                type="button"
                data-kode-transaksi="' . $order->kode_transaksi . '"
                class="btn-delete-order-layanan inline-flex items-center gap-1
                       px-3 py-1.5 rounded-lg text-[11px] font-semibold
                       bg-rose-50 text-rose-700 border border-rose-200
                       hover:bg-rose-100 hover:text-rose-900
                       focus:outline-none focus:ring-1 focus:ring-rose-400
                       transition-colors duration-150">
                <i class="fa-solid fa-trash-can text-[10px]"></i>
                <span>Hapus</span>
            </button>
        </div>
    ';
            })
            ->rawColumns(['status', 'action'])
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
        // 1) Validasi umum + array items
        $validated = $request->validate([
            'pasien_id'      => 'required|exists:pasien,id',
            'total_tagihan'  => 'required|numeric|min:0',

            'items'                         => 'required|array|min:1',
            'items.*.layanan_id'           => 'required|exists:layanan,id',
            'items.*.kategori_layanan_id'  => 'required|exists:kategori_layanan,id',
            'items.*.jumlah'               => 'required|integer|min:1',
            'items.*.total_tagihan'        => 'required|numeric|min:0',
        ], [
            'required' => 'Field ini wajib diisi.',
            'exists'   => 'Data tidak valid.',
            'integer'  => 'Harus berupa angka.',
            'numeric'  => 'Harus berupa angka.',
            'array'    => 'Format data tidak sesuai.',
            'min'      => 'Nilai minimal tidak terpenuhi.',
        ]);

        // Cek apakah ada item dengan kategori "Pemeriksaan"
        $kategoriIds   = collect($validated['items'])->pluck('kategori_layanan_id')->unique();
        $kategoriList  = KategoriLayanan::whereIn('id', $kategoriIds)->get();
        $isPemeriksaan = $kategoriList->contains(function ($k) {
            return $k->nama_kategori === 'Pemeriksaan';
        });

        // 2) Validasi tambahan kalau ada layanan Pemeriksaan
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

            // 3) Kalau ada Pemeriksaan → buat kunjungan + no antrian
            if ($isPemeriksaan) {
                $poliId = (int) $request->poli_id;

                $hariIni = Carbon::now()->locale('id')->dayName; // "Senin", dst.

                $jadwal = JadwalDokter::where('id', $request->jadwal_dokter_id)
                    ->where('poli_id', $poliId)
                    // ->where('hari', $hariIni) // aktifkan kalau kolom hari cocok
                    ->first();

                if (! $jadwal) {
                    throw ValidationException::withMessages([
                        'jadwal_dokter_id' => 'Jadwal dokter tidak valid untuk poli ini / hari ini.',
                    ]);
                }

                $tanggal = today();

                // kunci antrian per poli + tanggal
                $lastRow = Kunjungan::where('poli_id', $poliId)
                    ->whereDate('tanggal_kunjungan', $tanggal)
                    ->orderByRaw('CAST(no_antrian AS UNSIGNED) DESC')
                    ->lockForUpdate()
                    ->first();

                $lastNumber  = $lastRow ? (int) $lastRow->no_antrian : 0;
                $formattedNo = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

                $kunjungan = Kunjungan::create([
                    'jadwal_dokter_id'  => $jadwal->id,
                    'dokter_id'         => $jadwal->dokter_id,
                    'poli_id'           => $jadwal->poli_id,
                    'pasien_id'         => $validated['pasien_id'],
                    'tanggal_kunjungan' => $tanggal,
                    'no_antrian'        => $formattedNo,
                    'keluhan_awal'      => null,
                    'status'            => 'Pending',
                ]);

                $kunjunganId = $kunjungan->id;
            }

            // 4) Generate kode transaksi sekali untuk semua layanan
            $kodeTransaksi = 'TRX-' . strtoupper(uniqid());

            $orders      = [];
            $grandTotal  = 0;

            foreach ($validated['items'] as $item) {
                $subtotal = (float) $item['total_tagihan'];
                $grandTotal += $subtotal;

                $orders[] = PenjualanLayanan::create([
                    'pasien_id'            => $validated['pasien_id'],
                    'layanan_id'           => $item['layanan_id'],
                    'kategori_layanan_id'  => $item['kategori_layanan_id'],
                    'kunjungan_id'         => $kunjunganId,
                    'metode_pembayaran_id' => null,
                    'jumlah'               => $item['jumlah'],
                    'total_tagihan'        => $subtotal,
                    'sub_total'            => $subtotal,
                    'kode_transaksi'       => $kodeTransaksi,
                    'tanggal_transaksi'    => now(),
                    'status'               => 'Belum Bayar',
                ]);
            }

            // (opsional) override total_tagihan header dengan hasil hitung backend
            // kalau mau lebih aman:
            // $validated['total_tagihan'] = $grandTotal;

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isPemeriksaan
                    ? 'Order Layanan + Kunjungan Berhasil Dibuat!'
                    : 'Order Layanan Berhasil Dibuat!',
                'data'    => [
                    'kode_transaksi' => $kodeTransaksi,
                    'total_tagihan'  => $grandTotal,
                    'orders'         => $orders,
                    'kunjungan_id'   => $kunjunganId,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data.',
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
        $orders = PenjualanLayanan::with([
            'pasien',
            'layanan',
            'kategoriLayanan',
            'kunjungan.poli',
            'kunjungan.jadwalDokter',
        ])
            ->where('kode_transaksi', $kodeTransaksi)
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data order layanan tidak ditemukan.',
            ], 404);
        }

        $first = $orders->first();

        $tanggalTransaksi = $first->tanggal_transaksi
            ? Carbon::parse($first->tanggal_transaksi)->format('Y-m-d\TH:i')
            : null;

        // Ambil kunjungan utk header
        $kunjungan = $first->kunjungan;
        $poli      = optional($kunjungan)->poli;
        $jadwal    = optional($kunjungan)->jadwalDokter;

        // Mapping setiap baris menjadi item layanan
        $items = $orders->map(function ($row) {
            $kunjunganRow = $row->kunjungan;
            $poliRow      = optional($kunjunganRow)->poli;
            $jadwalRow    = optional($kunjunganRow)->jadwalDokter;

            return [
                'id'                     => $row->id, // id penjualan_layanan
                'layanan_id'             => $row->layanan_id,
                'nama_layanan'           => optional($row->layanan)->nama_layanan,
                'kategori_layanan_id'    => $row->kategori_layanan_id,
                'kategori_layanan_nama'  => optional($row->kategoriLayanan)->nama_kategori,
                'jumlah'                 => $row->jumlah,
                'total_tagihan'          => $row->total_tagihan,

                // info poli / jadwal per transaksi (biasanya sama utk semua items)
                'poli_id'                => optional($kunjunganRow)->poli_id,
                'nama_poli'              => optional($poliRow)->nama_poli,
                'jadwal_dokter_id'       => optional($kunjunganRow)->jadwal_dokter_id,
                'jadwal_dokter'          => $jadwalRow
                    ? $jadwalRow->jam_awal . ' - ' . $jadwalRow->jam_selesai
                    : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                // header transaksi
                'kode_transaksi'    => $first->kode_transaksi,
                'tanggal_transaksi' => $tanggalTransaksi,
                'status'            => $first->status,

                // ==> TAMBAH INI
                'order_layanan_id'  => $items->first()->id ?? null,

                // data pasien
                'pasien' => [
                    'id'            => $first->pasien_id,
                    'nama_pasien'   => optional($first->pasien)->nama_pasien,
                    'no_emr'        => optional($first->pasien)->no_emr,
                    'jenis_kelamin' => optional($first->pasien)->jenis_kelamin,
                ],

                // header poli / jadwal
                'poli_id'          => optional($kunjungan)->poli_id,
                'nama_poli'        => optional($poli)->nama_poli,
                'jadwal_dokter_id' => optional($kunjungan)->jadwal_dokter_id,
                'jadwal_dokter'    => $jadwal
                    ? $jadwal->jam_awal . ' - ' . $jadwal->jam_selesai
                    : null,

                // daftar item layanan
                'items' => $items,

                // total keseluruhan
                'total_tagihan' => $items->sum('total_tagihan'),
            ],
        ]);
    }

    /**
     * Update data order layanan (multi item per kode_transaksi)
     */
    public function updateDataOrderLayanan(Request $request)
    {
        // ==============
        // VALIDASI DASAR
        // ==============
        $validated = $request->validate([
            'order_layanan_id'        => 'required|exists:penjualan_layanan,id',
            'pasien_id'               => 'required|exists:pasien,id',

            'items'                   => 'required|array|min:1',
            'items.*.layanan_id'      => 'required|exists:layanan,id',
            'items.*.kategori_layanan_id' => 'required|exists:kategori_layanan,id',
            'items.*.jumlah'          => 'required|integer|min:1',
            'items.*.total_tagihan'   => 'required|numeric|min:0',

            'total_tagihan'           => 'required|numeric|min:0',
        ], [
            'required' => 'Field ini wajib diisi.',
            'exists'   => 'Data tidak valid.',
            'integer'  => 'Harus berupa angka.',
            'numeric'  => 'Harus berupa angka.',
            'array'    => 'Format data tidak valid.',
            'min'      => 'Nilai minimal tidak valid.',
        ]);

        $itemsInput = collect($validated['items']);

        // ==================================
        // CEK APAKAH ADA KATEGORI PEMERIKSAAN
        // ==================================
        $kategoriIds = $itemsInput->pluck('kategori_layanan_id')->unique()->all();

        $kategoriList = KategoriLayanan::whereIn('id', $kategoriIds)
            ->get()
            ->keyBy('id');

        $hasPemeriksaan = $kategoriList->contains(function ($kat) {
            return $kat->nama_kategori === 'Pemeriksaan';
        });

        // VALIDASI TAMBAHAN JIKA ADA PEMERIKSAAN
        if ($hasPemeriksaan) {
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
            /** @var PenjualanLayanan $firstOrder */
            $firstOrder = PenjualanLayanan::lockForUpdate()
                ->with('kunjungan')
                ->findOrFail($validated['order_layanan_id']);

            $kodeTransaksi    = $firstOrder->kode_transaksi;
            $statusTransaksi  = $firstOrder->status;
            $tanggalTransaksi = $firstOrder->tanggal_transaksi ?? Carbon::now();
            $kunjunganId      = $firstOrder->kunjungan_id;

            // =====================================================
            // JIKA ADA PEMERIKSAAN → UPDATE / BUAT KUNJUNGAN SEKALI
            // (dipakai bersama utk semua item Pemeriksaan)
            // =====================================================
            if ($hasPemeriksaan) {
                $poliId  = (int) $request->poli_id;
                $jadwalId = (int) $request->jadwal_dokter_id;

                // pastikan jadwal valid utk poli tsb
                $jadwal = JadwalDokter::where('id', $jadwalId)
                    ->where('poli_id', $poliId)
                    ->first();

                if (!$jadwal) {
                    throw ValidationException::withMessages([
                        'jadwal_dokter_id' => 'Jadwal dokter tidak valid untuk poli ini.',
                    ]);
                }

                if ($kunjunganId) {
                    // sudah punya kunjungan → update poli/jadwal/dokter
                    $kunjungan = Kunjungan::lockForUpdate()->find($kunjunganId);
                    if ($kunjungan) {
                        $kunjungan->poli_id          = $jadwal->poli_id;
                        $kunjungan->dokter_id        = $jadwal->dokter_id;
                        $kunjungan->jadwal_dokter_id = $jadwal->id;
                        // no_antrian & tanggal_kunjungan dibiarkan
                        $kunjungan->save();
                    }
                } else {
                    // belum ada kunjungan → buat antrian baru utk hari ini
                    $tanggal = today();

                    $lastRow = Kunjungan::where('poli_id', $poliId)
                        ->whereDate('tanggal_kunjungan', $tanggal)
                        ->orderByRaw('CAST(no_antrian AS UNSIGNED) DESC')
                        ->lockForUpdate()
                        ->first();

                    $lastNumber  = $lastRow ? (int) $lastRow->no_antrian : 0;
                    $formattedNo = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

                    $kunjungan = Kunjungan::create([
                        'jadwal_dokter_id'  => $jadwal->id,
                        'dokter_id'         => $jadwal->dokter_id,
                        'poli_id'           => $jadwal->poli_id,
                        'pasien_id'         => $validated['pasien_id'],
                        'tanggal_kunjungan' => $tanggal,
                        'no_antrian'        => $formattedNo,
                        'keluhan_awal'      => null,
                        'status'            => 'Pending',
                    ]);

                    $kunjunganId = $kunjungan->id;
                }
            } else {
                // Kalau sebelumnya punya kunjungan tapi sekarang SEMUA layanan bukan Pemeriksaan,
                // bisa pilih: mau dibiarkan / di-null-kan. Di sini kita biarkan saja.
            }

            // ==========================
            // HAPUS SEMUA ITEM LAMA
            // ==========================
            PenjualanLayanan::where('kode_transaksi', $kodeTransaksi)
                ->lockForUpdate()
                ->delete();

            // ==========================
            // INSERT ULANG ITEM BARU
            // ==========================
            foreach ($itemsInput as $item) {
                $kategoriId = (int) $item['kategori_layanan_id'];
                $kat        = $kategoriList->get($kategoriId);
                $isPemeriksaanItem = $kat && $kat->nama_kategori === 'Pemeriksaan';

                PenjualanLayanan::create([
                    'kode_transaksi'      => $kodeTransaksi,
                    'pasien_id'           => $validated['pasien_id'],
                    'layanan_id'          => (int) $item['layanan_id'],
                    'kategori_layanan_id' => $kategoriId,
                    'jumlah'              => (int) $item['jumlah'],
                    'total_tagihan'       => (float) $item['total_tagihan'],
                    'sub_total'           => (float) $item['total_tagihan'],
                    'tanggal_transaksi'   => $tanggalTransaksi,
                    'status'              => $statusTransaksi,
                    'kunjungan_id'        => $isPemeriksaanItem ? $kunjunganId : null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order Layanan berhasil diperbarui!',
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e; // biar tetap balik sebagai 422 ke front-end
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate data.',
                'error'   => $e->getMessage(),
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
