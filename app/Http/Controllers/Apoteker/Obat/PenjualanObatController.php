<?php

namespace App\Http\Controllers\Apoteker\Obat;

use App\Http\Controllers\Controller;
use App\Models\Kunjungan;
use App\Models\Obat;
use App\Models\Pasien;
use App\Models\PenjualanObat;
use App\Models\Resep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class PenjualanObatController extends Controller
{
    // public function getDataPenjualanObat()
    // {
    //     // Ambil semua pasien + data obat yang pernah dibeli
    //     $dataPenjualanObat = Pasien::with('obat')->latest()->get();

    //     // Flatten (karena pasien bisa punya banyak obat)
    //     $penjualanData = collect();

    //     foreach ($dataPenjualanObat as $pasien) {
    //         foreach ($pasien->obat as $obat) {
    //             $penjualanData->push([
    //                 'nama_pasien'       => $pasien->nama_pasien,
    //                 'nama_obat'         => $obat->nama_obat,
    //                 'kode_transaksi'    => $obat->pivot->kode_transaksi,
    //                 'jumlah'            => $obat->pivot->jumlah,
    //                 'sub_total'         => $obat->pivot->sub_total,
    //                 'tanggal_transaksi' => $obat->pivot->tanggal_transaksi,
    //             ]);
    //         }
    //     }

    //     return DataTables::of($penjualanData)
    //         ->addIndexColumn()
    //         ->addColumn('action', function ($row) {
    //             return '
    //             <button class="text-blue-600 hover:text-blue-800 mr-2">
    //                 <i class="fa-regular fa-pen-to-square text-lg"></i>
    //             </button>
    //             <button class="text-red-600 hover:text-red-800">
    //                 <i class="fa-regular fa-trash-can text-lg"></i>
    //             </button>
    //         ';
    //         })
    //         ->make(true);
    // }


    public function getDataPenjualanObat()
    {
        // Ambil data dari tabel penjualan_obat beserta relasinya
        $dataPenjualan = PenjualanObat::with(['pasien', 'obat', 'metodePembayaran'])
            ->latest('tanggal_transaksi')
            ->get()
            ->groupBy('kode_transaksi'); // 🔥 Kelompokkan per transaksi

        // Ubah hasil menjadi bentuk siap tampil
        $penjualanData = $dataPenjualan->map(function ($group) {
            $first = $group->first();

            // Gabungkan nama obat dan jumlah
            $namaObat = $group->pluck('obat.nama_obat')->implode(', ');
            $jumlah   = $group->pluck('jumlah')->implode(', ');

            // Hitung total transaksi (subtotal semua obat)
            $totalTagihan = $group->sum('sub_total');

            // Format uang diterima dan kembalian (jika ada)
            $uangDiterima = $first->uang_yang_diterima ?? 0;
            $kembalian    = $first->kembalian ?? 0;

            // Format tanggal
            $tanggalTransaksi = $first->tanggal_transaksi
                ? (is_string($first->tanggal_transaksi)
                    ? date('d-m-Y H:i', strtotime($first->tanggal_transaksi))
                    : $first->tanggal_transaksi->format('d-m-Y H:i'))
                : '-';

            return [
                'kode_transaksi'    => $first->kode_transaksi,
                'nama_pasien'       => $first->pasien->nama_pasien ?? '-',
                'nama_obat'         => $namaObat,
                'jumlah'            => $jumlah,
                'sub_total'         => $totalTagihan,
                'total_tagihan'     => 'Rp ' . number_format($totalTagihan, 0, ',', '.'),
                'uang_diterima'     => 'Rp ' . number_format($uangDiterima, 0, ',', '.'),
                'kembalian'         => 'Rp ' . number_format($kembalian, 0, ',', '.'),
                'metode_pembayaran' => $first->metodePembayaran->nama_metode ?? '-',
                'status'            => $first->status ?? '-',
                'tanggal_transaksi' => $tanggalTransaksi,
            ];
        })->values();

        // Return ke DataTables
        return DataTables::of($penjualanData)
            ->addIndexColumn()
            // ->addColumn('action', function ($row) {
            //     return '
            //     <button class="text-blue-600 hover:text-blue-800 mr-2" title="Edit">
            //         <i class="fa-regular fa-pen-to-square text-lg"></i>
            //     </button>
            //     <button class="text-red-600 hover:text-red-800" title="Hapus">
            //         <i class="fa-regular fa-trash-can text-lg"></i>
            //     </button>
            // ';
            // })
            ->make(true);
    }




    public function search(Request $request)
    {
        $query = $request->get('query');
        $pasien = Pasien::where('nama_pasien', 'LIKE', "%{$query}%")->get(['id', 'nama_pasien', 'alamat', 'jenis_kelamin']);
        return response()->json($pasien);
    }

    public function searchObat(Request $request)
    {
        $query = $request->get('query');
        $obat = Obat::where('nama_obat', 'like', "%{$query}%")
            ->get(['id', 'nama_obat', 'dosis', 'total_harga', 'jumlah']);

        return response()->json($obat);
    }

    // public function pesanObat(Request $request)
    // {
    //     $request->validate([
    //         'pasien_id'   => 'required|exists:pasien,id',
    //         'obat_id'     => 'required|array',
    //         'obat_id.*'   => 'exists:obat,id',
    //         'jumlah'      => 'required|array',
    //         'jumlah.*'    => 'integer|min:1',
    //     ]);

    //     // Generate kode transaksi unik
    //     $kodeTransaksi = 'TRX-' . strtoupper(uniqid());

    //     // Loop untuk setiap obat yang dibeli
    //     foreach ($request->obat_id as $index => $obatId) {
    //         $jumlah = $request->jumlah[$index];

    //         $obat = DB::table('obat')->where('id', $obatId)->first();

    //         if (!$obat) continue;

    //         // Hitung subtotal (jika harga ada di tabel obat)
    //         $subTotal = property_exists($obat, 'total_harga') ? $jumlah * $obat->total_harga : 0;

    //         // Simpan ke tabel penjualan_obat
    //         DB::table('penjualan_obat')->insert([
    //             'pasien_id'          => $request->pasien_id,
    //             'obat_id'            => $obatId,
    //             'kode_transaksi'     => $kodeTransaksi,
    //             'jumlah'             => $jumlah,
    //             'sub_total'          => $subTotal,
    //             'tanggal_transaksi'  => now(),
    //             'created_at'         => now(),
    //             'updated_at'         => now(),
    //         ]);

    //         // Kurangi stok obat
    //         DB::table('obat')->where('id', $obatId)->decrement('jumlah', $jumlah);
    //     }

    //     return response()->json([
    //         'status'  => 'success',
    //         'message' => 'Transaksi penjualan obat berhasil disimpan.',
    //         'kode_transaksi' => $kodeTransaksi
    //     ]);
    // }

    public function pesanObat(Request $request)
    {
        $request->validate([
            'pasien_id' => ['required', 'exists:pasien,id'],
            'obat_id'   => ['required', 'array', 'min:1'],
            'obat_id.*' => ['required', 'exists:obat,id'],
            'jumlah'    => ['required', 'array', 'min:1'],
            'jumlah.*'  => ['required', 'integer', 'min:1'],
        ]);

        if (count($request->obat_id) !== count($request->jumlah)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jumlah item tidak sama dengan jumlah obat.'
            ], 422);
        }

        $kodeTransaksi = 'TRX-' . strtoupper(uniqid());
        $now = now();

        DB::beginTransaction();
        try {
            // 🔹 1. Buatkan resep dummy TANPA pasien_id (karena kolom itu tidak ada)
            $resepId = DB::table('resep')->insertGetId([
                'kunjungan_id' => null, // ini yang membedakan dari resep dokter
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);

            $grandTotal = 0;
            $items = [];

            // 🔹 2. Loop setiap obat yang dibeli
            foreach ($request->obat_id as $i => $obatId) {
                $qty = (int) $request->jumlah[$i];
                $obat = DB::table('obat')->where('id', $obatId)->first();

                if (!$obat) continue;

                $harga = $obat->harga ?? ($obat->total_harga ?? 0);
                $subTotal = $qty * $harga;
                $grandTotal += $subTotal;

                // Simpan ke penjualan_obat
                DB::table('penjualan_obat')->insert([
                    'pasien_id'         => $request->pasien_id,
                    'obat_id'           => $obatId,
                    'kode_transaksi'    => $kodeTransaksi,
                    'jumlah'            => $qty,
                    'sub_total'         => $subTotal,
                    'total_tagihan'     => $subTotal,
                    'status'            => 'Belum Bayar',
                    'tanggal_transaksi' => $now,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);

                // Simpan ke resep_obat agar tampil di menu Pengambilan Obat
                DB::table('resep_obat')->insert([
                    'resep_id'   => $resepId,
                    'obat_id'    => $obatId,
                    'jumlah'     => $qty,
                    'status'     => 'Belum Diambil',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $items[] = [
                    'nama_obat' => $obat->nama_obat,
                    'jumlah'    => $qty,
                    'harga'     => $harga,
                    'subtotal'  => $subTotal,
                ];
            }

            DB::commit();

            return response()->json([
                'status'         => 'success',
                'message'        => 'Transaksi obat berhasil disimpan dan siap diambil.',
                'kode_transaksi' => $kodeTransaksi,
                'total'          => $grandTotal,
                'items'          => $items,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }




    private function getOrCreateResepIdForPasien(int $pasienId): int
    {
        return DB::transaction(function () use ($pasienId) {
            // 1) Cari kunjungan "terbaru" hari ini; sesuaikan kriteria jika perlu
            $today = now()->toDateString();

            $kunjungan = Kunjungan::where('pasien_id', $pasienId)
                ->whereDate('tanggal_kunjungan', $today)
                ->latest('id')
                ->first();

            if (!$kunjungan) {
                // (opsional) cari poli khusus apotek/farmasi bila ada
                $poliId = DB::table('poli')
                    ->whereIn(DB::raw('LOWER(nama_poli)'), ['apotek', 'farmasi'])
                    ->value('id'); // bisa null kalau tidak ada & kolom kunjungan.poli_id nullable

                // nomor antrian per hari (3 digit)
                $lastNo = DB::table('kunjungan')
                    ->whereDate('tanggal_kunjungan', $today)
                    ->max('no_antrian');
                $nextNo = str_pad(((int)$lastNo) + 1, 3, '0', STR_PAD_LEFT);

                // **PASTIKAN** kolom berikut nullable jika tidak kamu isi (dokter_id, poli_id, dll.)
                $kunjunganId = DB::table('kunjungan')->insertGetId([
                    'pasien_id'         => $pasienId,
                    'tanggal_kunjungan' => now(),
                    'no_antrian'        => $nextNo,
                    'poli_id'           => $poliId,     // null jika tidak ada
                    // 'dokter_id'         => null,        // pastikan nullable
                    'status'            => 'Order Obat', // sesuaikan enum/status mu
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                $kunjungan = Kunjungan::find($kunjunganId);
            }

            // 2) Ambil/buat resep untuk kunjungan ini
            $resep = Resep::firstOrCreate(
                ['kunjungan_id' => $kunjungan->id],
                ['created_at' => now(), 'updated_at' => now()]
            );

            return (int) $resep->id;
        });
    }

    public function ajaxResepAktif(Request $request)
    {
        $request->validate(['pasien_id' => 'required|exists:pasien,id']);
        $resepId = $this->getOrCreateResepIdForPasien((int)$request->pasien_id);

        $resep = Resep::with('kunjungan')->find($resepId);
        return response()->json([
            'resep_id'         => $resepId,
            'kunjungan_id'     => $resep->kunjungan_id,
            'tanggal_kunjungan' => optional($resep->kunjungan)->tanggal_kunjungan,
            'created'          => true,
        ]);
    }
}
