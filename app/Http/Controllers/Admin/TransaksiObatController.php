<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MetodePembayaran;
use App\Models\Pasien;
use App\Models\PenjualanObat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Yajra\DataTables\Facades\DataTables;

class TransaksiObatController extends Controller
{
    public function getDataTransaksiObat()
    {
        // Ambil data dari tabel penjualan_obat + relasi
        $dataTransaksi = PenjualanObat::with(['pasien', 'obat', 'metodePembayaran'])
            ->where('status', 'Belum Bayar')
            ->latest()
            ->get()
            ->groupBy('kode_transaksi'); // 🔥 Gabungkan berdasarkan kode_transaksi

        // Map hasil group ke bentuk tabel
        $transaksiData = $dataTransaksi->map(function ($group) {
            // Ambil data pertama dari grup (untuk data pasien, tanggal, dll)
            $first = $group->first();

            // Gabungkan semua nama obat dan jumlah dalam satu baris
            $namaObat = $group->pluck('obat.nama_obat')->implode(', ');
            $dosis    = $group->pluck('obat.dosis')->implode(', ');
            $jumlah   = $group->pluck('jumlah')->implode(', ');

            // Hitung total keseluruhan dari semua item
            $totalSub = $group->sum('sub_total');

            // Ambil metode pembayaran & status
            $namaMetode = $first->metodePembayaran->nama_metode ?? '-';
            $status     = $first->status ?? '-';

            // Ambil tanggal transaksi
            $tanggalTransaksi = $first->tanggal_transaksi
                ? (is_string($first->tanggal_transaksi)
                    ? date('d-m-Y H:i', strtotime($first->tanggal_transaksi))
                    : $first->tanggal_transaksi->format('d-m-Y H:i'))
                : '-';

            // Bukti pembayaran (ambil salah satu)
            $buktiPembayaranUrl = $first->bukti_pembayaran
                ? asset('storage/' . $first->bukti_pembayaran)
                : null;

            $buktiPembayaranHTML = $buktiPembayaranUrl
                ? '<img src="' . $buktiPembayaranUrl . '" alt="Bukti Pembayaran" class="w-12 h-12 rounded-lg object-cover mx-auto shadow">'
                : '<span class="text-gray-400 italic">Tidak ada</span>';

            return [
                'nama_pasien'       => $first->pasien->nama_pasien ?? '-',
                'nama_obat'         => $namaObat,
                'dosis'             => $dosis,
                'jumlah'            => $jumlah,
                'sub_total'         => $totalSub,
                'metode_pembayaran' => $namaMetode,
                'kode_transaksi'    => $first->kode_transaksi,
                'tanggal_transaksi' => $tanggalTransaksi,
                'status'            => $status,
                'bukti_pembayaran'  => $buktiPembayaranHTML,
            ];
        })->values();

        // Kirim ke DataTables
        return DataTables::of($transaksiData)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $kode = $row['kode_transaksi'] ?? '';
                $url = route('kasir.transaksi.obat', ['kodeTransaksi' => $kode]);

                $namaPasien = e($row['nama_pasien'] ?? '-');
                $namaObat   = e($row['nama_obat'] ?? '-');
                $jumlah     = e($row['jumlah'] ?? '-');
                $subTotal   = $row['sub_total'] ?? 0;
                $tanggal    = e($row['tanggal_transaksi'] ?? '-');
                $subTotalFormatted = number_format($subTotal, 0, ',', '.');

                return '
                <button 
                    class="text-green-600 hover:text-green-800 mr-2 bayarSekarang"
                    title="Bayar Sekarang"
                    data-url="' . $url . '"
                    data-kode="' . $kode . '"
                    data-nama-pasien="' . $namaPasien . '"
                    data-nama-obat="' . $namaObat . '"
                    data-jumlah="' . $jumlah . '"
                    data-subtotal="' . $subTotal . '"
                    data-subtotal-formatted="Rp ' . $subTotalFormatted . '"
                    data-tanggal="' . $tanggal . '"
                >
                    <i class="fa-solid fa-money-bill text-lg"></i> Bayar Sekarang
                </button>
            ';
            })
            ->rawColumns(['bukti_pembayaran', 'action'])
            ->make(true);
    }



    public function transaksiObat($kodeTransaksi)
    {
        $dataTransaksiObat = PenjualanObat::with([
            'pasien',
            'obat',
            'metodePembayaran'
        ])->where('kode_transaksi', $kodeTransaksi)->get();

        if ($dataTransaksiObat->isEmpty()) {
            abort(404, 'Transaksi tidak ditemukan');
        }

        $first = $dataTransaksiObat->first();

        $tanggalTransaksi = $first->first()->tanggalTransaksi;

        // Ambil data pasien dari salah satu record (semuanya sama)
        $dataPasien = $dataTransaksiObat->first()->pasien;

        $subTotal = $dataTransaksiObat->sum('sub_total');

        $id = $dataTransaksiObat->first()->id;

        $kodeTransaksi = $first->kode_transaksi;

        $dataMetodePembayaran = MetodePembayaran::all();
        // Debug (kalau masih mau cek hasil, bisa pakai info log biar nggak ganggu tampilan)
        Log::info($dataTransaksiObat);

        // dd($dataTransaksiObat);

        return view('admin.pembayaran.detail-transaksi-obat', compact(
            'dataTransaksiObat',
            'dataMetodePembayaran',
            'dataPasien',
            'subTotal',
            'tanggalTransaksi',
            'id',
            'kodeTransaksi',
        ));
    }

    public function transaksiCash(Request $request)
    {
        $request->validate([
            'uang_yang_diterima' => ['required', 'numeric'],
            'kembalian' => ['required', 'numeric'],
            'metode_pembayaran' => ['required', 'exists:metode_pembayaran,id'],
            'kode_transaksi' => ['required', 'string'],
        ]);

        // 🔍 Ambil semua data transaksi berdasarkan kode_transaksi
        $transaksiList = PenjualanObat::where('kode_transaksi', $request->kode_transaksi)->get();

        if ($transaksiList->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi dengan kode tersebut tidak ditemukan.',
            ], 404);
        }

        // 🔄 Update semua data dengan kode transaksi yang sama
        PenjualanObat::where('kode_transaksi', $request->kode_transaksi)->update([
            'total_tagihan' => $request->uang_yang_diterima,
            'uang_yang_diterima' => $request->uang_yang_diterima,
            'kembalian' => $request->kembalian,
            'tanggal_transaksi' => now(),
            'status' => 'Sudah Bayar',
            'metode_pembayaran_id' => $request->metode_pembayaran,
        ]);

        // 🔽 Kurangi stok obat untuk setiap item di transaksi
        foreach ($transaksiList as $item) {
            DB::table('obat')->where('id', $item->obat_id)->decrement('jumlah', $item->jumlah);
        }

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil! Kembalian Rp ' . number_format($request->kembalian, 0, ',', '.') . '. Terimakasih 😊😊😊',
        ]);
    }

    public function transaksiTransfer(Request $request)
    {
        $request->validate([
            'kode_transaksi' => ['required', 'string'],
            'bukti_pembayaran' => ['required', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,jfif', 'max:5120'],
            'metode_pembayaran' => ['required', 'exists:metode_pembayaran,id'],
        ]);

        // Ambil salah satu record berdasarkan kode_transaksi
        $record = PenjualanObat::where('kode_transaksi', $request->kode_transaksi)->get();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi dengan kode tersebut tidak ditemukan.',
            ], 404);
        }

        // Ambil nominal dari salah satu kolom total/subtotal
        $amount = $request->total_tagihan;

        if (!$amount) {
            return response()->json([
                'success' => false,
                'message' => 'Kolom sub_total / total_tagihan tidak ditemukan di record transaksi.',
            ], 422);
        }

        // Upload + kompres gambar
        $fotoPath = null;
        if ($request->hasFile('bukti_pembayaran')) {
            $file = $request->file('bukti_pembayaran');
            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension === 'jfif') $extension = 'jpg';

            $fileName = time() . '_' . uniqid() . '.' . $extension;
            $path = 'bukti-transaksi/' . $fileName;

            if ($extension === 'svg') {
                Storage::disk('public')->put($path, file_get_contents($file));
            } else {
                $image = Image::read($file);
                $image->scale(width: 800);
                Storage::disk('public')->put($path, (string) $image->encodeByExtension($extension, quality: 80));
            }

            $fotoPath = $path;
        }

        // Update SEMUA data yang punya kode_transaksi sama
        PenjualanObat::where('kode_transaksi', $request->kode_transaksi)->update([
            'bukti_pembayaran'     => $fotoPath,
            'uang_yang_diterima'   => $amount,
            'kembalian'            => 0,
            'sub_total'            => $amount,
            'tanggal_transaksi'    => now(),
            'status'               => 'Sudah Bayar',
            'metode_pembayaran_id' => $request->metode_pembayaran,
        ]);

        // 🔽 Kurangi stok obat untuk setiap item di transaksi
        foreach ($record as $item) {
            DB::table('obat')->where('id', $item->obat_id)->decrement('jumlah', $item->jumlah);
        }

        return response()->json([
            'success' => true,
            'message' => 'Transaksi dengan kode ' . $request->kode_transaksi . ' berhasil diperbarui. Nominal: Rp' . number_format($amount, 0, ',', '.') . ' ✅',
        ]);
    }
}
