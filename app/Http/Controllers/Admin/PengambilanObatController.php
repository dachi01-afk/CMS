<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resep;
use App\Models\ResepObat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PengambilanObatController extends Controller
{
    public function index()
    {
        $dataResepObat = Resep::with('obat', 'kunjungan.pasien', 'kunjungan.dokter')->paginate(10);
        return view('admin.pengambilan_obat', compact('dataResepObat'));
    }

    public function getDataResepObat()
    {
        $query = Resep::with('obat', 'kunjungan.pasien', 'kunjungan.dokter')->get();

        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('nama_dokter', fn($row) => $row->kunjungan->dokter->nama_dokter ?? '-')
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

            // 🔹 Kolom jumlah obat (dari pivot)
            ->addColumn('jumlah', function ($row) {
                if ($row->obat->isEmpty()) {
                    return '-';
                }

                $output = '<ul class="list-disc pl-4">';
                foreach ($row->obat as $obat) {
                    $output .= '<li>' . e($obat->pivot->jumlah) . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })

            // 🔹 Kolom keterangan (dari pivot)
            ->addColumn('keterangan', function ($row) {
                if ($row->obat->isEmpty()) {
                    return '-';
                }

                $output = '<ul class="list-disc pl-4">';
                foreach ($row->obat as $obat) {
                    $output .= '<li>' . e($obat->pivot->keterangan) . '</li>';
                }
                $output .= '</ul>';
                return $output;
            })

            ->addColumn('action', function ($row) {
                return '
                <button class="btnUpdateStatus text-blue-600 hover:text-blue-800 mr-2" 
                        data-id="' . $row->id . '" title="Edit">
                    Update Status
                    <i class="fa-regular fa-pen-to-square text-lg"></i>
                </button>
            ';
            })
            ->rawColumns(['nama_obat', 'jumlah', 'keterangan', 'action'])
            ->make(true);
    }

    // public function updateStatusResepObat(Request $request)
    // {
    //     $request->validate([
    //         'resep_id' => ['required', 'exists:resep,id'],
    //         'obat_id' => ['required', 'exists:obat,id'],
    //         'status' => ['required', 'string'], // contoh: 'belum bayar' / 'sudah bayar'
    //     ]);

    //     try {
    //         DB::transaction(function () use ($request) {
    //             // Ambil resep
    //             $resep = Resep::findOrFail($request->resep_id);

    //             // Cek apakah obat ada di dalam resep ini
    //             $obat = $resep->obat()->where('obat_id', $request->obat_id)->firstOrFail();

    //             // Update status di tabel pivot resep_obat
    //             $resep->obat()->updateExistingPivot($request->obat_id, [
    //                 'status' => $request->status,
    //             ]);

    //             // Jika status berubah jadi "sudah bayar", kurangi stok obat
    //             if ($request->status === 'sudah bayar') {
    //                 $jumlahObat = $obat->pivot->jumlah;

    //                 if ($obat->jumlah < $jumlahObat) {
    //                     throw new \Exception('Stok obat tidak mencukupi.');
    //                 }

    //                 $obat->decrement('jumlah', $jumlahObat);
    //             }
    //         });

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Status resep obat berhasil diperbarui.',
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal memperbarui status resep obat: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function updateStatusResepObat(Request $request)
    {
        $request->validate([
            'resep_id' => ['required', 'exists:resep,id'],
            'obat_id'  => ['required', 'exists:obat,id'],
            'status'   => ['required', 'string'], // contoh: 'belum bayar' / 'sudah bayar'
        ]);

        try {
            DB::transaction(function () use ($request) {
                $resep = Resep::findOrFail($request->resep_id);
                $obat  = $resep->obat()->where('obat_id', $request->obat_id)->firstOrFail();

                // update pivot
                $resep->obat()->updateExistingPivot($request->obat_id, [
                    'status' => $request->status,
                ]);

                // jika status sudah bayar, kurangi stok obat
                if ($request->status === 'sudah bayar') {
                    $jumlahObat = $obat->pivot->jumlah;

                    if ($obat->jumlah < $jumlahObat) {
                        throw new \Exception('Stok obat tidak mencukupi.');
                    }

                    $obat->decrement('jumlah', $jumlahObat);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Status resep obat berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status resep obat: ' . $e->getMessage(),
            ], 500);
        }
    }
}
