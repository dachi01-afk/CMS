<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
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
        ])->get();

        return DataTables::of($dataPembayaran)
            ->addIndexColumn()
            ->addColumn('nama_pasien', fn($payment) => $payment->kunjungan->pasien->nama_pasien ?? '-')
            ->addColumn('tanggal_kunjungan', fn($payment) => $payment->kunjungan->tanggal_kunjungan ?? '-')
            ->addColumn('no_antrian', fn($payment) => $payment->kunjungan->no_antrian ?? '-')
            ->addColumn('nama_obat', function ($payment) {

                $output = '<ul class="list-disc pl-4">';
                foreach ($payment->resep->obat as $obat) {
                    if ($payment->resep->obat->isEmpty()) {
                        return '<span class="text-gray-400 italic">Tidak ada</span>';
                    }
                    $output .= '<li>' . e($obat->nama_obat) . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })
            ->addColumn('dosis', function ($payment) {
                $output = '<ul class="list-disc pl-4">';
                foreach ($payment->resep->obat as $obat) {
                    $output .= '<li>' . e($obat->dosis) . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })
            ->addColumn('jumlah', function ($payment) {
                $output = '<ul class="list-disc pl-4">';
                foreach ($payment->resep->obat as $obat) {
                    $output .= '<li>' . e($obat->jumlah) . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })
            ->addColumn('total_tagihan', fn($payment) => $payment->total_tagihan ?? '-')
            ->addColumn('metode_pembayaran', fn($payment) => $payment->metode_pembayaran ?? '-')
            ->addColumn('status', fn($payment) => $payment->status ?? '-')
            // 🔹 Kolom action — per obat
            ->addColumn('action', function ($row) {
                if ($row->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada tindakan</span>';
                }

                $output = '<ul class="pl-0">';
                foreach ($row->obat as $obat) {
                    $output .= '
                    <li class="list-none mb-1">
                        <button class="text-blue-600 hover:text-blue-800" title="Update Status">
                            <i class="fa-regular fa-pen-to-square"></i> Bayar Sekarang
                        </button>
                    </li>
                ';
                }
                $output .= '</ul>';

                return $output;
            })
            ->rawColumns(['nama_obat', 'jumlah', 'keterangan', 'action'])
            ->make(true);
    }
}
