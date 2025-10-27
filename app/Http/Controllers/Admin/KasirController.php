<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\MetodePembayaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Yajra\DataTables\DataTables;

class KasirController extends Controller
{
    public function index()
    {
        return view('admin.pembayaran.kasir');
    }

    public function getDataPembayaran()
    {
        $dataPembayaran = Pembayaran::with([
            'emr.kunjungan.pasien',
            'emr.resep.obat',
            'emr.kunjungan.layanan',
            'metodePembayaran',
        ])->where('status', 'Belum Bayar')->latest()->get();

        return DataTables::of($dataPembayaran)
            ->addIndexColumn()
            ->addColumn('nama_pasien', fn($p) => $p->emr->kunjungan->pasien->nama_pasien ?? '-')
            ->addColumn('tanggal_kunjungan', fn($p) => $p->emr->kunjungan->tanggal_kunjungan ?? '-')
            ->addColumn('no_antrian', fn($p) => $p->emr->kunjungan->no_antrian ?? '-')

            // daftar nama obat
            ->addColumn('nama_obat', function ($p) {
                $resep = $p->emr->resep ?? null;
                if (!$resep || $resep->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }

                $output = '<ul class="list-disc pl-4">';
                foreach ($resep->obat as $obat) {
                    $output .= '<li>' . e($obat->nama_obat) . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })

            // dosis
            ->addColumn('dosis', function ($p) {
                $resep = $p->emr->resep ?? null;
                if (!$resep || $resep->obat->isEmpty()) return '-';
                $output = '<ul class="list-disc pl-4">';
                foreach ($resep->obat as $obat) {
                    $output .= '<li>' . e($obat->pivot->dosis ?? '-') . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })

            // jumlah
            ->addColumn('jumlah', function ($p) {
                $resep = $p->emr->resep ?? null;
                if (!$resep || $resep->obat->isEmpty()) return '-';
                $output = '<ul class="list-disc pl-4">';
                foreach ($resep->obat as $obat) {
                    $output .= '<li>' . e($obat->pivot->jumlah ?? '-') . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })

            ->addColumn('nama_layanan', function ($p) {
                $layanan = $p->emr->kunjungan->layanan ?? collect();
                if ($layanan->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }

                $output = '<ul class="list-disc pl-4">';
                foreach ($layanan as $l) {
                    $output .= '<li>' . e($l->nama_layanan ?? '-') . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })

            ->addColumn('jumlah_layanan', function ($p) {
                $layanan = $p->emr->kunjungan->layanan ?? collect();
                if ($layanan->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }

                $output = '<ul class="list-disc pl-4">';
                foreach ($layanan as $l) {
                    $output .= '<li>' . e($l->pivot->jumlah ?? '-') . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })

            ->addColumn('total_tagihan', fn($p) => 'Rp ' .  number_format($p->total_tagihan, 0, ',', '.')  ?? '-')
            ->addColumn('metode_pembayaran', fn($p) => $p->metodePembayaran->nama_metode ?? '-')
            ->addColumn('status', fn($p) => $p->status ?? '-')

            // kolom action
            ->addColumn('action', function ($p) {
                $resep = $p->emr->resep ?? null;
                if (!$resep || $resep->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada tindakan</span>';
                }

                $output = '<ul class="pl-0">';
                $url = route('kasir.transaksi', ['kode_transaksi' => $p->kode_transaksi]);
                foreach ($resep->obat as $obat) {
                    $output .= '
                    <li class="list-none mb-1">
                        <button class="bayarSekarang text-blue-600 hover:text-blue-800" 
                                data-url="' . $url . '"
                                data-id="' . $p->id . '"
                                data-emr-id="' . $p->emr->id . '"
                                title="Update Status">
                            <i class="fa-regular fa-pen-to-square"></i> Bayar Sekarang
                        </button>
                    </li>';
                }
                $output .= '</ul>';

                return $output;
            })
            ->rawColumns(['nama_obat', 'dosis', 'jumlah', 'nama_layanan', 'jumlah_layanan', 'action'])
            ->make(true);
    }

    public function transaksi($kodeTransaksi)
    {
        $dataPembayaran = Pembayaran::with([
            'emr.kunjungan.pasien',
            'emr.kunjungan.poli',
            'emr.kunjungan.layanan', // ambil layanan langsung dari kunjungan
            'emr.resep.obat', // kalau kamu punya relasi ini
        ])->where('kode_transaksi', $kodeTransaksi)
            ->firstOrFail();

        $dataMetodePembayaran = MetodePembayaran::all();
        // Debug (kalau masih mau cek hasil, bisa pakai info log biar nggak ganggu tampilan)
        Log::info($dataPembayaran);

        // dd($dataPembayaran);

        return view('admin.pembayaran.transaksi', compact('dataPembayaran', 'dataMetodePembayaran'));
    }

    public function transaksiCash(Request $request)
    {
        $request->validate([
            'uang_yang_diterima' => ['required'],
            'kembalian' => ['required'],
            'metode_pembayaran_id' => ['required'],
        ]);

        $dataPembayaran = Pembayaran::findOrFail($request->id);

        $dataPembayaran->update([
            'uang_yang_diterima' => $request->uang_yang_diterima,
            'kembalian' => $request->kembalian,
            'tanggal_pembayaran' => now(),
            'status' => 'Sudah Bayar',
            'metode_pembayaran_id' => $request->metode_pembayaran_id,
        ]);

        return response()->json([
            'success' => true,
            'data' => $dataPembayaran,
            'message' => 'Uang Kembalian Rp.' . $request->kembalian,
        ]);
    }

    public function transaksiTransfer(Request $request)
    {
        // validasi minimal: pastikan id dan file bukti ada
        $request->validate([
            'id' => ['required', 'exists:pembayaran,id'],
            'bukti_pembayaran' => ['required', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,jfif', 'max:5120'],
            'metode_pembayaran_id' => ['required', 'exists:metode_pembayaran,id'],
        ]);

        // cari record transaksi
        $dataPembayaran = Pembayaran::findOrFail($request->id);

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
            'tanggal_pembayaran'   => now(),
            'status'               => 'Sudah Bayar', // atau "Sudah Bayar" jika otomatis terima
            'metode_pembayaran_id' => $request->metode_pembayaran_id,
        ]);

        return response()->json([
            'success' => true,
            'data' => $dataPembayaran,
            'message' => 'Bukti transfer diterima. Nominal terbayar: Rp' . number_format($amount, 0, ',', '.') . '. Terimakasih 😊😊😊'
        ]);
    }

    public function getDataRiwayatPembayaran()
    {
        $dataPembayaran = Pembayaran::with([
            'emr.kunjungan.pasien',
            'emr.resep.obat',
            'emr.kunjungan.layanan',
            'metodePembayaran',
        ])->where('status', 'Sudah Bayar')->latest()->get();

        return DataTables::of($dataPembayaran)
            ->addIndexColumn()
            ->addColumn('nama_pasien', fn($p) => $p->emr->kunjungan->pasien->nama_pasien ?? '-')
            ->addColumn('tanggal_kunjungan', fn($p) => $p->emr->kunjungan->tanggal_kunjungan ?? '-')
            ->addColumn('no_antrian', fn($p) => $p->emr->kunjungan->no_antrian ?? '-')

            // daftar nama obat
            ->addColumn('nama_obat', function ($p) {
                $resep = $p->emr->resep ?? null;
                if (!$resep || $resep->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }

                $output = '<ul class="list-disc pl-4">';
                foreach ($resep->obat as $obat) {
                    $output .= '<li>' . e($obat->nama_obat) . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })

            // dosis
            ->addColumn('dosis', function ($p) {
                $resep = $p->emr->resep ?? null;
                if (!$resep || $resep->obat->isEmpty()) return '-';
                $output = '<ul class="list-disc pl-4">';
                foreach ($resep->obat as $obat) {
                    $output .= '<li>' . e($obat->pivot->dosis ?? '-') . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })

            // jumlah
            ->addColumn('jumlah', function ($p) {
                $resep = $p->emr->resep ?? null;
                if (!$resep || $resep->obat->isEmpty()) return '-';
                $output = '<ul class="list-disc pl-4">';
                foreach ($resep->obat as $obat) {
                    $output .= '<li>' . e($obat->pivot->jumlah ?? '-') . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })

            ->addColumn('nama_layanan', function ($p) {
                $layanan = $p->emr->kunjungan->layanan ?? collect();
                if ($layanan->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }

                $output = '<ul class="list-disc pl-4">';
                foreach ($layanan as $l) {
                    $output .= '<li>' . e($l->nama_layanan ?? '-') . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })

            ->addColumn('jumlah_layanan', function ($p) {
                $layanan = $p->emr->kunjungan->layanan ?? collect();
                if ($layanan->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }

                $output = '<ul class="list-disc pl-4">';
                foreach ($layanan as $l) {
                    $output .= '<li>' . e($l->pivot->jumlah ?? '-') . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })

            ->addColumn('total_tagihan', fn($p) => 'Rp ' .  number_format($p->total_tagihan, 0, ',', '.')  ?? '-')
            ->addColumn('metode_pembayaran', fn($p) => $p->metodePembayaran->nama_metode ?? '-')
            ->addColumn('status', fn($p) => $p->status ?? '-')
            ->addColumn('bukti_pembayaran', function ($p) {
                if ($p->bukti_pembayaran) {
                    $url = asset('storage/' . $p->bukti_pembayaran);
                    return '<img src="' . $url . '" alt="Foto Bukti Pembayaran" class="w-12 h-12 rounded-lg object-cover mx-auto shadow">';
                } else {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }
            })

            // kolom action
            ->addColumn('action', function ($p) {
                $resep = $p->emr->resep ?? null;

                // kalau tidak ada resep / obat
                if (!$resep || $resep->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada tindakan</span>';
                }

                // tampilkan hanya 1 tombol saja
                $url = route('kasir.show.kwitansi', ['kodeTransaksi' => $p->kode_transaksi]);
                return '
            <button class="cetakKuitansi text-blue-600 hover:text-blue-800"
                data-url="' . $url . '"
                title="Cetak Kwitansi">
                <i class="fa-solid fa-print"></i> Cetak Kwitansi
                </button>
    ';
            })
            ->rawColumns(['nama_obat', 'dosis', 'jumlah', 'nama_layanan', 'jumlah_layanan', 'bukti_pembayaran', 'action'])
            ->make(true);
    }

    public function showKwitansi($kodeTransaksi)
    {
        $dataPembayaran = Pembayaran::with([
            'emr.kunjungan.pasien',
            'emr.resep.obat',
            'emr.kunjungan.layanan',
            'metodePembayaran',
        ])->where('kode_transaksi', $kodeTransaksi)->firstOrFail();

        // Hitung total harga obat & layanan
        $totalObat = $dataPembayaran->emr->resep->obat->sum(function ($obat) {
            return $obat->pivot->jumlah * $obat->total_harga;
        });

        $totalLayanan = $dataPembayaran->emr->kunjungan->layanan->sum(function ($layanan) {
            return $layanan->pivot->jumlah * $layanan->harga_layanan;
        });

        $grandTotal = $totalObat + $totalLayanan;

        $namaPT = 'Royal Klinik';

        return view('admin.pembayaran.kwitansi', compact('dataPembayaran', 'totalObat', 'totalLayanan', 'grandTotal', 'namaPT'));
    }

    public function getDataMetodePembayaran()
    {
        $dataMetodePembayaran = MetodePembayaran::latest()->get();

        return DataTables::of($dataMetodePembayaran)
            ->addIndexColumn()
            ->addColumn('nama_metode', fn($mP) => $mP->nama_metode)
            ->addColumn('action', function ($mP) {
                return '
                <button class="btn-update-metode-pembayaran text-blue-600 hover:text-blue-800 mr-2" data-id="' . $mP->id . '" title="Edit">
                    <i class="fa-regular fa-pen-to-square text-lg"></i>
                </button>
                <button class="btn-delete-metode-pembayaran text-red-600 hover:text-red-800" data-id="' . $mP->id . '" title="Hapus">
                    <i class="fa-regular fa-trash-can text-lg"></i>
                </button>
            ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
