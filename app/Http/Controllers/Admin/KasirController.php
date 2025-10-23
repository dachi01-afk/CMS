<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\MetodePembayaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        ])->where('status', 'Belum Bayar')->get();

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
            ->addColumn('metode_pembayaran', fn($p) => $p->metode_pembayaran ?? '-')
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

    public function melakukanPembayaranCash(Request $request)
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
            'message' => 'Uang Kembalian ' . $request->kembalian,
        ]);
    }

    public function getDataRiwayatPembayaran()
    {
        $dataPembayaran = Pembayaran::with([
            'emr.kunjungan.pasien',
            'emr.resep.obat',
            'emr.kunjungan.layanan',
            'metodePembayaran',
        ])->where('status', 'Sudah Bayar')->get();

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
            ->addColumn('metode_pembayaran', fn($p) => $p->metode_pembayaran ?? '-')
            ->addColumn('status', fn($p) => $p->status ?? '-')

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
            ->rawColumns(['nama_obat', 'dosis', 'jumlah', 'nama_layanan', 'jumlah_layanan', 'action'])
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
        $dataMetodePembayaran = MetodePembayaran::all();

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
