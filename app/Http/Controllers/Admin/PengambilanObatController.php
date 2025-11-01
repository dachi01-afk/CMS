<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resep;
use App\Models\ResepObat;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class PengambilanObatController extends Controller
{
    public function index()
    {

        return view('admin.pengambilan_obat');
    }

    public function getDataResepObat()
    {
        // $query = Resep::with('obat', 'kunjungan.pasien', 'kunjungan.dokter')->get();
        $query = Resep::with('obat', 'kunjungan.pasien', 'kunjungan.poli.dokter')->whereHas('obat', function ($q) {
            $q->where('resep_obat.status', 'Belum Diambil');
        })->latest()->get();

        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('nama_dokter', function ($row) {
                $dokter = $row->kunjungan->poli->dokter->first();
                return $dokter->nama_dokter ?? '-';
            })

            ->addColumn('nama_pasien', fn($row) => $row->kunjungan->pasien->nama_pasien ?? '-')
            ->addColumn('no_antrian', fn($row) => $row->kunjungan->no_antrian ?? '-')
            ->addColumn('tanggal_kunjungan', fn($row) => $row->kunjungan->tanggal_kunjungan ?? '-')

            // 🔹 Kolom nama_obat
            ->addColumn('nama_obat', function ($row) {
                if ($row->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }

                $output = '<ul class="list-disc pl-4">';
                foreach ($row->obat as $obat) {
                    $output .= '<li>' . e($obat->nama_obat) . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })

            // 🔹 Kolom jumlah
            ->addColumn('jumlah', function ($row) {
                if ($row->obat->isEmpty()) return '-';

                $output = '<ul class="list-disc pl-4">';
                foreach ($row->obat as $obat) {
                    $output .= '<li>' . e($obat->pivot->jumlah) . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })

            // 🔹 Kolom keterangan
            ->addColumn('keterangan', function ($row) {
                if ($row->obat->isEmpty()) return '-';

                $output = '<ul class="list-disc pl-4">';
                foreach ($row->obat as $obat) {
                    $output .= '<li>' . e($obat->pivot->keterangan) . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })

            // Kolom status 
            ->addColumn('status', function ($row) {
                if ($row->obat->isEmpty()) return '-';

                $output = '<ul class="list-disc pl-4">';
                foreach ($row->obat as $obat) {
                    $output = e($obat->pivot->status);
                }
                return $output;
            })

            // 🔹 Kolom action — per obat
            ->addColumn('action', function ($row) {
                if ($row->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada tindakan</span>';
                }

                return '
        <button class="btnUpdateStatus text-blue-600 hover:text-blue-800" 
                data-resep-id="' . $row->id . '" 
                title="Update Status">
            <i class="fa-regular fa-pen-to-square"></i> Update Status
        </button>
    ';
            })

            ->rawColumns(['nama_obat', 'jumlah', 'keterangan', 'status', 'action'])
            ->make(true);
    }


    public function updateStatusResepObat(Request $request)
    {
        $request->validate([
            'resep_id' => ['required', 'exists:resep,id'],
            'obat_id'  => ['required', 'exists:obat,id'],
            'jumlah_obat' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                $resep = \App\Models\Resep::findOrFail($request->resep_id);

                // 🔹 Ambil pembayaran berdasarkan emr yang punya resep_id ini
                $pembayaran = \App\Models\Pembayaran::whereHas('emr', function ($q) use ($resep) {
                    $q->where('resep_id', $resep->id);
                })->first();

                // 🔹 Validasi pembayaran
                if (!$pembayaran) {
                    throw new \Exception('Obat belum dibayar. Silahkan bayar terlebih dahulu');
                }

                if ($pembayaran->status !== 'Sudah Bayar') {
                    throw new \Exception('Status pembayaran masih "Belum Bayar". Silakan lakukan pembayaran terlebih dahulu.');
                }

                // 🔹 Pastikan obat benar-benar ada dalam resep
                $obatPivot = $resep->obat()->where('obat_id', $request->obat_id)->firstOrFail();

                // 🔹 Ambil jumlah obat dari pivot
                $jumlahObat = $obatPivot->pivot->jumlah ?? 0;

                // 🔹 Ambil data stok obat
                $obat = \App\Models\Obat::findOrFail($request->obat_id);

                // 🔹 Validasi stok cukup
                if ($obat->jumlah < $jumlahObat) {
                    throw new Exception("Stok obat '{$obat->nama_obat}' tidak mencukupi. Stok saat ini: {$obat->stok}");
                }

                // 🔹 Kurangi stok obat
                $obat->jumlah = $obat->jumlah - $jumlahObat;
                $obat->save();

                // 🔹 Update status pivot jadi "Sudah Diambil"
                $resep->obat()->updateExistingPivot($request->obat_id, [
                    'status' => 'Sudah Diambil',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Status resep obat berhasil diperbarui menjadi "Sudah Diambil".',
            ]);
        } catch (Exception $e) {
            Log::error('updateStatusResepObat error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
