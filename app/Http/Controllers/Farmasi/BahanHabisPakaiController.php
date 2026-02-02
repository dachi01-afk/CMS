<?php

namespace App\Http\Controllers\Farmasi;

use Throwable;
use App\Models\Depot;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\BahanHabisPakai;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BahanHabisPakaiExport;
use App\Imports\BahanHabisPakaiImport;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Farmasi\StoreBahanHabisPakaiRequest;
use App\Http\Requests\updateBahanHabisPakaiRequest;

class BahanHabisPakaiController extends Controller
{
    public function index()
    {
        return view('farmasi.bahan-habis-pakai.bahan-habis-pakai');
    }

    public function getDataBahanHabisPakai()
    {
        $dataBhp = BahanHabisPakai::with('brandFarmasi', 'jenisBHP', 'satuanBHP', 'depotBHP')
            ->latest()->get();

        return DataTables::of($dataBhp)
            ->addIndexColumn()
            ->addColumn('kode', fn($bhp) => $bhp->kode ?? '-')
            ->addColumn('nama_barang', fn($bhp) => $bhp->nama_barang ?? '-')
            ->addColumn('brand_farmasi', fn($bhp) => $bhp->brandFarmasi->nama_brand ?? '-')
            ->addColumn('stok', fn($bhp) => is_null($bhp->stok_barang) ? 0 : (int) $bhp->stok_barang)
            ->addColumn('harga_jual_umum_bhp', function ($bhp) {
                return 'Rp' . number_format($bhp->harga_jual_umum_bhp ?? 0, 2, ',', '.');
            })
            ->addColumn('harga_beli_satuan_bhp', function ($bhp) {
                return 'Rp' . number_format($bhp->harga_beli_satuan_bhp ?? 0, 2, ',', '.');
            })
            ->addColumn('avg_hpp_bhp', function ($bhp) {
                return 'Rp' . number_format($bhp->avg_hpp_bhp ?? 0, 2, ',', '.');
            })
            ->addColumn('harga_otc_bhp', function ($bhp) {
                return 'Rp' . number_format($bhp->harga_otc_bhp ?? 0, 2, ',', '.');
            })
            ->addColumn('margin_profit_bhp', function ($bhp) {
                $hpp   = $bhp->avg_hpp_bhp ?? 0;
                $jual  = $bhp->harga_jual_umum_bhp ?? 0;

                $margin = $jual - $hpp;

                return 'Rp ' . number_format($margin, 0, ',', '.');
            })
            // AKSI
            ->addColumn('action', function ($bhp) {
                return '
                <div class="flex items-center justify-center gap-2">
                    <button 
                        class="btn-edit-bhp inline-flex items-center justify-center w-8 h-8 rounded-lg 
                               bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-100"
                        data-id="' . $bhp->id . '" 
                        title="Edit">
                        <i class="fa-regular fa-pen-to-square text-xs"></i>
                    </button>

                    <button 
                        class="btn-delete-bhp inline-flex items-center justify-center w-8 h-8 rounded-lg 
                               bg-red-50 text-red-600 hover:bg-red-100 border border-red-100"
                        data-id="' . $bhp->id . '" 
                        title="Hapus">
                        <i class="fa-regular fa-trash-can text-xs"></i>
                    </button>
                </div>
            ';
            })
            ->make(true);
    }

    public function createDataBahanHabisPakai(StoreBahanHabisPakaiRequest $request)
    {
        try {
            // Data sudah otomatis tervalidasi dan ter-parse harganya di Request Class
            $bhp = BahanHabisPakai::simpanData($request->validated());

            return response()->json([
                'status'  => 200,
                'message' => 'Berhasil menambahkan data BHP!',
                'data'    => $bhp->load('brandFarmasi', 'jenisBHP', 'satuanBHP', 'depotBHP', 'batchBahanHabisPakai.batchBahanHabisPakaiDepot'),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDataBahanHabisPakaiById($id)
    {
        $dataBHP = BahanHabisPakai::getDataById($id);

        return response()->json([
            'success' => true,
            'data'    => $dataBHP,
        ]);
    }

    public function updateDataBahanHabisPakai(updateBahanHabisPakaiRequest $request, $id)
    {
        try {
            // Data sudah otomatis tervalidasi dan ter-parse harganya di Request Class
            $result = BahanHabisPakai::updateData($request->validated(), $id);

            $bhp = $result[0];

            return response()->json([
                'status'  => 200,
                'message' => 'Berhasil update data BHP!',
                'data'    => $bhp->load('brandFarmasi', 'jenisBHP', 'satuanBHP', 'depotBHP', 'batchBahanHabisPakai.batchBahanHabisPakaiDepot'),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }


    public function deleteDataBahanHabisPakai($id)
    {
        $dataBhp = BahanHabisPakai::findOrFail($id);
        $dataBhp->delete();
        return response()->json([
            'status' => 200,
            'data' => $dataBhp,
            'message' => 'Berhasil Menghapus Data BHP!'
        ]);
    }

    public function exportExcelBhp()
    {
        $fileName = 'BHP_' . Carbon::now('Asia/Jakarta')->format('Y-m-d') . '.xlsx';
        return Excel::download(new BahanHabisPakaiExport, $fileName);
    }

    public function printPdfBhp(Request $request)
    {
        $q = trim((string) $request->query('q', '')); // keyword dari search input (opsional)

        $query = BahanHabisPakai::query()
            ->with(['brandFarmasi', 'satuanBHP'])
            ->latest();

        // Jika kamu mau print "semua data" tanpa filter, hapus blok ini.
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('kode', 'like', "%{$q}%")
                    ->orWhere('nama_barang', 'like', "%{$q}%")
                    ->orWhereHas('brandFarmasi', function ($b) use ($q) {
                        $b->where('nama_brand', 'like', "%{$q}%");
                    });
            });
        }

        $rows = $query->get();

        $meta = [
            'title' => 'Laporan Data Stok Bahan Habis Pakai',
            'printed_at' => Carbon::now('Asia/Jakarta')->format('d/m/Y H:i'),
            'keyword' => $q,
            'total' => $rows->count(),
        ];

        $pdf = Pdf::loadView('farmasi.bahan-habis-pakai.print-preview-bahan-habis-pakai', compact('rows', 'meta'))
            ->setPaper('a4', 'landscape');

        $filename = 'PRINT_BHP_' . Carbon::now('Asia/Jakarta')->format('Y-m-d') . '.pdf';

        // stream = buka di tab baru (enak untuk print)
        return $pdf->stream($filename);
    }

    public function importExcelBhp(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:20480'],
        ], [
            'file.required' => 'File excel wajib dipilih.',
            'file.mimes' => 'File harus berformat .xlsx atau .xls',
        ]);

        try {
            $import = new BahanHabisPakaiImport();

            Excel::import($import, $request->file('file'));

            // Kalau ada baris gagal validasi
            if ($import->failures()->isNotEmpty()) {
                $first = $import->failures()->first();
                return back()->with('error', 'Ada data yang gagal diimport. Baris: ' . $first->row());
            }

            return back()->with('success', 'Import BHP berhasil.');
        } catch (Throwable $e) {
            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }
}
