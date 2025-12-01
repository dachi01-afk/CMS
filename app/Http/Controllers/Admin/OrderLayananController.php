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
                data-id="' . $order->id . '"
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
                data-id="' . $order->id . '"
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

    public function getDataOrderLayananById($id)
    {
        $order = PenjualanLayanan::with(['pasien', 'layanan', 'kategoriLayanan'])
            ->findOrFail($id);

        $tanggalTransaksi = $order->tanggal_transaksi
            ? Carbon::parse($order->tanggal_transaksi)->format('Y-m-d\TH:i')
            : null;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'pasien_id' => $order->pasien_id,
                'nama_pasien' => $order->pasien->nama_pasien ?? null,
                'pasien_no_emr' => $order->pasien->no_emr ?? null,
                'pasien_jenis_kelamin' => $order->pasien->jenis_kelamin ?? null,

                'layanan_id' => $order->layanan_id,
                'nama_layanan' => $order->layanan->nama_layanan ?? null,

                'kategori_layanan_id' => $order->kategori_layanan_id,
                'kategori_layanan' => $order->kategoriLayanan->nama_kategori ?? null,

                'jumlah' => $order->jumlah,
                'total_tagihan' => $order->total_tagihan,
                'tanggal_transaksi' => $tanggalTransaksi,
                'status' => $order->status,
            ],
        ]);
    }

    /**
     * Update data order layanan
     */
    public function updateDataOrderLayanan(Request $request)
    {
        // VALIDASI UTAMA
        $validated = $request->validate([
            'id'                  => 'required|exists:penjualan_layanan,id',
            'pasien_id'           => 'required|exists:pasien,id',
            'layanan_id'          => 'required|exists:layanan,id',
            'kategori_layanan_id' => 'required|exists:kategori_layanan,id',
            'jumlah'              => 'required|integer|min:1',
            'total_tagihan'       => 'required|numeric|min:0',
        ], [
            'required' => 'Field ini wajib diisi.',
            'exists'   => 'Data tidak valid.',
            'integer'  => 'Harus berupa angka.',
            'numeric'  => 'Harus berupa angka.',
        ]);

        $kategori = KategoriLayanan::find($validated['kategori_layanan_id']);
        $isPemeriksaan = $kategori && $kategori->nama_kategori === 'Pemeriksaan';

        // VALIDASI TAMBAHAN JIKA PEMERIKSAAN
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
            /** @var PenjualanLayanan $order */
            $order = PenjualanLayanan::lockForUpdate()->findOrFail($validated['id']);

            $kunjunganId = $order->kunjungan_id;

            if ($isPemeriksaan) {
                $poliId = (int) $request->poli_id;

                // ambil jadwal & pastikan poli cocok
                $jadwal = JadwalDokter::where('id', $request->jadwal_dokter_id)
                    ->where('poli_id', $poliId)
                    ->first();

                if (!$jadwal) {
                    throw ValidationException::withMessages([
                        'jadwal_dokter_id' => 'Jadwal dokter tidak valid untuk poli ini.',
                    ]);
                }

                // kalau sudah punya kunjungan -> update poli/jadwal/dokter
                if ($kunjunganId) {
                    $kunjungan = Kunjungan::find($kunjunganId);
                    if ($kunjungan) {
                        $kunjungan->poli_id          = $jadwal->poli_id;
                        $kunjungan->dokter_id        = $jadwal->dokter_id;
                        $kunjungan->jadwal_dokter_id = $jadwal->id;
                        // no_antrian tetap, tanggal_kunjungan bisa dibiarkan apa adanya
                        $kunjungan->save();
                    }
                } else {
                    // sebelumnya tidak punya kunjungan → buat baru (tanpa ubah antrian lain)
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
            }

            // UPDATE ORDER (tanpa utak-atik tanggal_transaksi & status)
            $order->pasien_id           = $validated['pasien_id'];
            $order->layanan_id          = $validated['layanan_id'];
            $order->kategori_layanan_id = $validated['kategori_layanan_id'];
            $order->jumlah              = $validated['jumlah'];
            $order->total_tagihan       = $validated['total_tagihan'];
            $order->sub_total           = $validated['total_tagihan'];

            if ($isPemeriksaan) {
                $order->kunjungan_id = $kunjunganId;
            }

            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order Layanan Berhasil Diperbarui!',
                'data'    => $order,
            ]);
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
     * Hapus data order layanan.
     * - Jika order punya kunjungan_id dan hanya order ini yang pakai,
     *   maka kunjungan ikut dihapus (order ikut hilang via cascade).
     * - Jika kunjungan dipakai order lain, hanya order ini yang dihapus.
     */
    public function deleteDataOrderLayanan($id, Request $request)
    {
        try {
            DB::beginTransaction();

            /** @var \App\Models\PenjualanLayanan|null $order */
            $order = PenjualanLayanan::lockForUpdate()->find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data order tidak ditemukan.',
                ], 404);
            }

            // (opsional) kalau mau larang hapus yang sudah bayar, buka komentar ini:
            /*
            if ($order->status === 'Sudah Bayar') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order yang sudah dibayar tidak boleh dihapus.',
                ], 422);
            }
            */

            $kunjunganId = $order->kunjungan_id;

            if ($kunjunganId) {
                // hitung berapa order yang pakai kunjungan ini
                $totalOrderKunjungan = PenjualanLayanan::where('kunjungan_id', $kunjunganId)->count();

                if ($totalOrderKunjungan === 1) {
                    // cuma order ini yang pakai kunjungan tsb
                    // hapus kunjungan → lewat FK cascadeOnDelete, order ikut terhapus
                    Kunjungan::where('id', $kunjunganId)->delete();

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Order layanan & kunjungan terkait berhasil dihapus.',
                    ]);
                }
            }

            // kalau tidak punya kunjungan, atau kunjungan dipakai banyak order
            $order->delete();

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
                // 'error'   => $e->getMessage(), // boleh di-uncomment kalau mau debugging
            ], 500);
        }
    }
}
