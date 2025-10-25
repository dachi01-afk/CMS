<?php

namespace App\Http\Controllers\Apoteker\Obat;

use App\Http\Controllers\Controller;
use App\Models\Obat;
use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        // Ambil semua pasien beserta relasi ke tabel obat dan data pivot
        $dataPenjualan = Pasien::with(['obat' => function ($query) {
            $query->select('obat.id', 'obat.nama_obat', 'obat.total_harga');
        }])->latest()->get();

        // Map jadi struktur baru sesuai kebutuhan tabel
        $penjualanData = $dataPenjualan->map(function ($pasien) {
            // Kalau pasien belum pernah beli obat
            if ($pasien->obat->isEmpty()) {
                return null;
            }

            // Gabungkan semua nama obat
            $namaObat = $pasien->obat->pluck('nama_obat')->implode(', ');

            // Gabungkan jumlah obat dari tabel pivot
            $jumlahObat = $pasien->obat->pluck('pivot.jumlah')->implode(', ');

            // Hitung subtotal (jumlah × harga)
            $subTotal = $pasien->obat->sum(function ($obat) {
                return $obat->pivot->jumlah * $obat->total_harga;
            });

            // Ambil tanggal transaksi terakhir (optional)
            $tanggalTransaksi = $pasien->obat->max('pivot.tanggal_transaksi');

            return [
                'nama_pasien'       => $pasien->nama_pasien,
                'nama_obat'         => $namaObat,
                'kode_transaksi'    => $pasien->obat->first()->pivot->kode_transaksi ?? '-',
                'jumlah'            => $jumlahObat,
                'sub_total'         => $subTotal,
                'tanggal_transaksi' => $tanggalTransaksi,
            ];
        })->filter()->values(); // Hapus null data & reset index

        return DataTables::of($penjualanData)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                return '
                <button class="text-blue-600 hover:text-blue-800 mr-2" title="Edit">
                    <i class="fa-regular fa-pen-to-square text-lg"></i>
                </button>
                <button class="text-red-600 hover:text-red-800" title="Hapus">
                    <i class="fa-regular fa-trash-can text-lg"></i>
                </button>
            ';
            })
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

    public function pesanObat(Request $request)
    {
        $request->validate([
            'pasien_id'   => 'required|exists:pasien,id',
            'obat_id'     => 'required|array',
            'obat_id.*'   => 'exists:obat,id',
            'jumlah'      => 'required|array',
            'jumlah.*'    => 'integer|min:1',
        ]);

        // Generate kode transaksi unik
        $kodeTransaksi = 'TRX-' . strtoupper(uniqid());

        // Loop untuk setiap obat yang dibeli
        foreach ($request->obat_id as $index => $obatId) {
            $jumlah = $request->jumlah[$index];

            $obat = DB::table('obat')->where('id', $obatId)->first();

            if (!$obat) continue;

            // Hitung subtotal (jika harga ada di tabel obat)
            $subTotal = property_exists($obat, 'total_harga') ? $jumlah * $obat->total_harga : 0;

            // Simpan ke tabel penjualan_obat
            DB::table('penjualan_obat')->insert([
                'pasien_id'          => $request->pasien_id,
                'obat_id'            => $obatId,
                'kode_transaksi'     => $kodeTransaksi,
                'jumlah'             => $jumlah,
                'sub_total'          => $subTotal,
                'tanggal_transaksi'  => now(),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            // Kurangi stok obat
            DB::table('obat')->where('id', $obatId)->decrement('jumlah', $jumlah);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Transaksi penjualan obat berhasil disimpan.',
            'kode_transaksi' => $kodeTransaksi
        ]);
    }
}
