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
        $query = PenjualanLayanan::with([
            'pasien',
            'layanan',
            'kategoriLayanan',
            'kunjungan',
            'metodePembayaran'
        ])->latest();

        return DataTables::of($query)
            ->addIndexColumn()

            // Nama Pasien
            ->addColumn('nama_pasien', function ($order) {
                return $order->pasien->nama_pasien ?? '-';
            })

            // Nama Layanan
            ->addColumn('nama_layanan', function ($order) {
                return $order->layanan->nama_layanan ?? '-';
            })

            // Kategori (Pemeriksaan / Non Pemeriksaan)
            ->addColumn('kategori_layanan', function ($order) {
                return $order->kategoriLayanan->nama_kategori ?? '-';
            })

            // Jumlah
            ->addColumn('jumlah', function ($order) {
                return $order->jumlah ?? 1;
            })

            // Total Tagihan
            ->addColumn('total_tagihan', function ($order) {
                return 'Rp ' . number_format($order->total_tagihan, 0, ',', '.');
            })

            // Status (Sudah Bayar / Belum Bayar)
            ->addColumn('status', function ($order) {
                $color = $order->status == 'Sudah Bayar'
                    ? 'bg-green-100 text-green-700'
                    : 'bg-yellow-100 text-yellow-700';

                return '<span class="px-3 py-1 rounded-lg text-xs font-semibold ' . $color . '">'
                    . $order->status .
                    '</span>';
            })

            // Tanggal Transaksi
            ->addColumn('tanggal_transaksi', function ($order) {
                return $order->tanggal_transaksi
                    ? date('d M Y H:i', strtotime($order->tanggal_transaksi))
                    : '-';
            })

            // Kode Transaksi
            ->addColumn('kode_transaksi', function ($order) {
                return $order->kode_transaksi ?? '-';
            })

            // Tombol Action
            ->addColumn('action', function ($order) {
                return '
                <div class="flex gap-2">
                    <button data-id="' . $order->id . '" 
                        class="btn-update-order-layanan px-3 py-1 bg-blue-600 text-white rounded-lg text-xs hover:bg-blue-700">
                        Edit
                    </button>

                    <button data-id="' . $order->id . '" 
                        class="btn-delete-order-layanan px-3 py-1 bg-red-600 text-white rounded-lg text-xs hover:bg-red-700">
                        Hapus
                    </button>
                </div>
            ';
            })
            ->rawColumns(['status', 'action']) // Important!
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
        // 1) Validasi umum
        $validated = $request->validate([
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

        // 2) Validasi tambahan kalau Pemeriksaan
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

            if ($isPemeriksaan) {
                $poliId = (int) $request->poli_id;

                // ambil jadwal_dokter & pastikan cocok dengan poli
                $hariIni = Carbon::now()->locale('id')->dayName; // "Senin", dsb.

                $jadwal = JadwalDokter::where('id', $request->jadwal_dokter_id)
                    ->where('poli_id', $poliId)
                    // ->where('hari', $hariIni)  // aktifkan kalau format harinya sama
                    ->first();

                if (!$jadwal) {
                    throw ValidationException::withMessages([
                        'jadwal_dokter_id' => 'Jadwal dokter tidak valid untuk poli ini / hari ini.',
                    ]);
                }

                // Generate no antrian per poli & tanggal
                $tanggal = today();

                $lastRow = Kunjungan::where('poli_id', $poliId)
                    ->whereDate('tanggal_kunjungan', $tanggal)
                    ->orderByRaw('CAST(no_antrian AS UNSIGNED) DESC')
                    ->lockForUpdate()
                    ->first();

                $lastNumber  = $lastRow ? (int)$lastRow->no_antrian : 0;
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

            $kodeTransaksi = 'TRX-' . strtoupper(uniqid());

            $order = PenjualanLayanan::create([
                'pasien_id'           => $validated['pasien_id'],
                'layanan_id'          => $validated['layanan_id'],
                'kategori_layanan_id' => $validated['kategori_layanan_id'],
                'kunjungan_id'        => $kunjunganId,
                'metode_pembayaran_id' => null,
                'jumlah'              => $validated['jumlah'],
                'total_tagihan'       => $validated['total_tagihan'],
                'sub_total'           => $validated['total_tagihan'],
                'kode_transaksi'      => $kodeTransaksi,
                'tanggal_transaksi'   => now(),
                'status'              => 'Belum Bayar',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isPemeriksaan
                    ? 'Order Layanan + Kunjungan Berhasil Dibuat!'
                    : 'Order Layanan Berhasil Dibuat!',
                'data'    => $order,
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
