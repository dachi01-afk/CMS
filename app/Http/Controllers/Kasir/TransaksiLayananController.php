<?php

namespace App\Http\Controllers\Kasir;

use Illuminate\Http\Request;
use App\Models\PenjualanLayanan;
use App\Http\Controllers\Controller;
use App\Models\MetodePembayaran;
use Yajra\DataTables\Facades\DataTables;

class TransaksiLayananController extends Controller
{
    /**
     * DataTables: get data transaksi layanan untuk kasir.
     */
    public function getDataTransaksiLayanan()
    {
        $query = PenjualanLayanan::query()
            ->with(['pasien', 'layanan', 'kategoriLayanan'])
            ->orderByDesc('tanggal_transaksi');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('nama_pasien', function ($row) {
                return $row->pasien->nama_pasien ?? '-';
            })
            ->addColumn('nama_layanan', function ($row) {
                return $row->layanan->nama_layanan ?? '-';
            })
            ->addColumn('kategori_layanan', function ($row) {
                return $row->kategoriLayanan->nama_kategori ?? '-';
            })
            ->editColumn('jumlah', function ($row) {
                return $row->jumlah ?? 0;
            })
            ->editColumn('total_tagihan', function ($row) {
                // kirim angka murni, nanti dirupiahkan di JS
                return $row->total_tagihan ?? 0;
            })
            ->editColumn('metode_pembayaran', function ($row) {
                return $row->metode_pembayaran ?? '-';
            })
            ->editColumn('kode_transaksi', function ($row) {
                return $row->kode_transaksi ?? '-';
            })
            ->editColumn('tanggal_transaksi', function ($row) {
                return optional($row->tanggal_transaksi)->format('Y-m-d H:i:s');
            })
            ->editColumn('status', function ($row) {
                return $row->status ?? '-';
            })
            ->addColumn('bukti_pembayaran', function ($row) {
                if (!$row->bukti_pembayaran) {
                    return '-';
                }

                $url = asset('storage/' . $row->bukti_pembayaran);

                return '<a href="' . $url . '" target="_blank" class="text-sky-600 underline text-xs">
                            Lihat
                        </a>';
            })
            ->addColumn('action', function ($row) {
                // halaman detail / bayar layanan
                $bayarUrl = route('kasir.show.detail.transaksi.layanan', $row->id);

                return '
                    <button
                        type="button"
                        class="btn-bayar-layanan inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-md"
                        data-url="' . $bayarUrl . '">
                        <i class="fa-regular fa-pen-to-square"></i>
                        Bayar Sekarang
                    </button>
                ';
            })
            ->rawColumns(['bukti_pembayaran', 'action'])
            ->make(true);
    }

    public function showDetailTransaksiLayanan($id)
    {
        $transaksi = PenjualanLayanan::with([
            'pasien',
            'layanan.kategoriLayanan',
            'kunjungan.poli',
            'kunjungan.dokter',
        ])->findOrFail($id);

        return view('kasir.pembayaran.detail-transaksi-layanan', [
            'transaksi' => $transaksi,
        ]);
    }

    public function prosesPembayaranLayanan($id)
    {
        $transaksi = PenjualanLayanan::with([
            'pasien',
            'layanan.kategoriLayanan'
        ])->findOrFail($id);

        $metodePembayaran = MetodePembayaran::get();

        return view('kasir.pembayaran.proses-pembayaran-layanan', [
            'transaksi' => $transaksi,
            'metodePembayaran' => $metodePembayaran
        ]);
    }

    public function submitPembayaranLayanan(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:penjualan_layanan,id',
            'metode_pembayaran' => 'required|string',
            'bukti_pembayaran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $transaksi = PenjualanLayanan::findOrFail($validated['id']);

        // Upload bukti pembayaran jika ada
        if ($request->hasFile('bukti_pembayaran')) {
            $path = $request->file('bukti_pembayaran')->store('bukti_pembayaran', 'public');
            $transaksi->bukti_pembayaran = $path;
        }

        // Update transaksi
        $transaksi->metode_pembayaran = $validated['metode_pembayaran'];
        $transaksi->status = 'Sudah Bayar';
        $transaksi->tanggal_transaksi = now();
        $transaksi->save();

        return redirect()
            ->route('kasir.show.detail.transaksi.layanan', $transaksi->id)
            ->with('success', 'Pembayaran berhasil diproses.');
    }
}
