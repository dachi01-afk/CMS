<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MetodePembayaran;
use App\Models\Pasien;
use App\Models\PenjualanObat;
use Illuminate\Http\Request;
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
            'uang_yang_diterima' => ['required'],
            'kembalian' => ['required'],
            'metode_pembayaran' => ['required'],
        ]);

        $dataPembayaran = PenjualanObat::where('kode_transaksi', $request->kode_transaksi);

        $dataPembayaran->update([
            'total_tagihan' => $request->uang_yang_diterima,
            'uang_yang_diterima' => $request->uang_yang_diterima,
            'kembalian' => $request->kembalian,
            'tanggal_transaksi' => now(),
            'status' => 'Sudah Bayar',
            'metode_pembayaran_id' => $request->metode_pembayaran,
        ]);

        return response()->json([
            'success' => true,
            'data' => $dataPembayaran,
            'message' => 'Uang Kembalian ' . $request->kembalian,
        ]);
    }

    public function transaksiTransfer(Request $request)
    {
        // validasi minimal: pastikan id dan file bukti ada
        $request->validate([
            'id' => ['required', 'exists:penjualan_obat,id'],
            'bukti_pembayaran' => ['required', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,jfif', 'max:5120'],
            'metode_pembayaran' => ['required', 'exists:metode_pembayaran,id'],
        ]);

        // cari record transaksi
        $dataPembayaran = PenjualanObat::findOrFail($request->id);

        // ambil nilai subtotal dari DB (fallback kalau nama kolom beda)
        $amount = null;
        if (isset($dataPembayaran->sub_total)) {
            $amount = $dataPembayaran->sub_total;
        } elseif (isset($dataPembayaran->total_tagihan)) {
            $amount = $dataPembayaran->total_tagihan;
        } elseif (isset($dataPembayaran->total)) {
            $amount = $dataPembayaran->total;
        } else {
            // jika tidak ada field subtotal di model, batalkan dengan error
            return response()->json([
                'success' => false,
                'message' => 'Kolom subtotal/total tidak ditemukan di record transaksi. Periksa nama kolom di database.'
            ], 422);
        }

        // pastikan amount bernilai numeric
        $amount = floatval($amount);
        if ($amount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Nilai tagihan tidak valid (<= 0).'
            ], 422);
        }

        // 2️⃣ Upload + Kompres Gambar
        $fotoPath = null;
        if ($request->hasFile('bukti_pembayaran')) {
            $file = $request->file('bukti_pembayaran');
            $extension = strtolower($file->getClientOriginalExtension());

            if ($extension === 'jfif') {
                $extension = 'jpg';
            }

            $fileName = time() . '_' . uniqid() . '.' . $extension;
            $path = 'bukti-transaksi/' . $fileName;

            if ($extension === 'svg') {
                Storage::disk('public')->put($path, file_get_contents($file));
            } else {
                // gunakan Intervention Image untuk resize/kompres
                $image = Image::read($file);
                $image->scale(width: 800);
                Storage::disk('public')->put($path, (string) $image->encodeByExtension($extension, quality: 80));
            }

            $fotoPath = $path;
        }

        // 3️⃣ Update Data Transaksi:
        // - isi uang_yang_diterima = subtotal dari DB
        // - isi kembalian = 0 (karena uang pas sama tagihan)
        $dataPembayaran->update([
            'bukti_pembayaran'     => $fotoPath,
            'uang_yang_diterima'   => $amount,
            'kembalian'            => 0,
            'tanggal_transaksi'   => now(),
            'status'               => 'Sudah Bayar', // atau "Sudah Bayar" jika otomatis terima
            'metode_pembayaran_id' => $request->metode_pembayaran,
        ]);

        return response()->json([
            'success' => true,
            'data' => $dataPembayaran,
            'message' => 'Bukti transfer diterima. Nominal terbayar: Rp' . number_format($amount, 0, ',', '.') . '. Terimakasih 😊😊😊'
        ]);
    }
}
