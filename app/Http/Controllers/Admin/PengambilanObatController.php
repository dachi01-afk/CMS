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

    // public function getDataResepObat()
    // {
    //     $query = Resep::with([
    //         'obat' => fn($q) => $q->withPivot('jumlah', 'keterangan', 'status'),
    //         'kunjungan.pasien',
    //         'kunjungan.poli.dokter'
    //     ])
    //         ->whereHas('obat', function ($q) {
    //             // tampilkan yang belum diambil (boleh sertakan yang status-nya null)
    //             $q->wherePivot('status', 'Belum Diambil')
    //                 ->orWherePivot('status', null);
    //         })
    //         ->latest('created_at') // pastikan kolom ini ada
    //         ->get();

    //     return DataTables::of($query)
    //         ->addIndexColumn()
    //         ->addColumn('nama_dokter', fn($row) => optional(optional($row->kunjungan)->poli)->dokter->first()->nama_dokter ?? '-')
    //         ->addColumn('nama_pasien', fn($row) => optional(optional($row->kunjungan)->pasien)->nama_pasien ?? '-')
    //         ->addColumn('no_antrian', fn($row) => optional($row->kunjungan)->no_antrian ?? '-')
    //         ->addColumn('tanggal_kunjungan', fn($row) => optional($row->kunjungan)->tanggal_kunjungan ?? '-')

    //         // Nama obat
    //         ->addColumn('nama_obat', function ($row) {
    //             if ($row->obat->isEmpty()) return '<span class="text-gray-400 italic">Tidak ada</span>';
    //             $out = '<ul class="list-disc pl-4">';
    //             foreach ($row->obat as $ob) {
    //                 $out .= '<li>' . e($ob->nama_obat) . '</li>';
    //             }
    //             return $out . '</ul>';
    //         })

    //         // Jumlah
    //         ->addColumn('jumlah', function ($row) {
    //             if ($row->obat->isEmpty()) return '-';
    //             $out = '<ul class="list-disc pl-4">';
    //             foreach ($row->obat as $ob) {
    //                 $out .= '<li>' . e($ob->pivot->jumlah) . '</li>';
    //             }
    //             return $out . '</ul>';
    //         })

    //         // Keterangan
    //         ->addColumn('keterangan', function ($row) {
    //             if ($row->obat->isEmpty()) return '-';
    //             $out = '<ul class="list-disc pl-4">';
    //             foreach ($row->obat as $ob) {
    //                 $out .= '<li>' . e($ob->pivot->keterangan) . '</li>';
    //             }
    //             return $out . '</ul>';
    //         })

    //         // Status (FIX: jangan timpa $output)
    //         ->addColumn('status', function ($row) {
    //             if ($row->obat->isEmpty()) return '-';
    //             $out = '<ul class="list-disc pl-4">';
    //             foreach ($row->obat as $ob) {
    //                 $out .= '<li>' . e($ob->pivot->status ?? 'Belum Diambil') . '</li>';
    //             }
    //             return $out . '</ul>';
    //         })

    //         ->addColumn('action', function ($row) {
    //             if ($row->obat->isEmpty()) return '<span class="text-gray-400 italic">Tidak ada tindakan</span>';
    //             $dataObat = $row->obat->map(fn($ob) => ['id' => $ob->id, 'jumlah' => $ob->pivot->jumlah]);
    //             $jsonObat = e(json_encode($dataObat));
    //             return '<button class="btnUpdateStatus text-blue-600 hover:text-blue-800"
    //                     data-resep-id="' . $row->id . '"
    //                     data-obat=\'' . $jsonObat . '\'
    //                     title="Update Status">
    //                     <i class="fa-regular fa-pen-to-square"></i> Update Status
    //                 </button>';
    //         })
    //         ->rawColumns(['nama_obat', 'jumlah', 'keterangan', 'status', 'action'])
    //         ->make(true);
    // }

    public function getDataResepObat()
    {
        $query = Resep::with([
            'obat' => fn($q) => $q->withPivot('jumlah', 'keterangan', 'status'),
            'kunjungan.pasien',
            'kunjungan.poli.dokter'
        ])
            ->whereHas('obat', function ($q) {
                // tampilkan yang belum diambil atau status-nya null (tanpa pakai orWherePivot)
                $q->where(function ($qq) {
                    $qq->where('resep_obat.status', 'Belum Diambil')
                        ->orWhereNull('resep_obat.status');
                });
            })
            ->latest()
            ->get();

        return DataTables::of($query)
            ->addIndexColumn()

            // 🔹 Nama Dokter (biarkan '-' jika tidak ada)
            ->addColumn('nama_dokter', function ($row) {
                if (
                    $row->kunjungan &&
                    $row->kunjungan->poli &&
                    $row->kunjungan->poli->dokter &&
                    $row->kunjungan->poli->dokter->count() > 0
                ) {
                    return e($row->kunjungan->poli->dokter->first()->nama_dokter);
                }
                return '<span class="italic text-gray-400">-</span>';
            })

            // 🔹 Nama Pasien (dua logika: kunjungan → penjualan_obat)
            ->addColumn('nama_pasien', function ($row) {
                // 1) dari kunjungan (alur pemeriksaan)
                if ($row->kunjungan && $row->kunjungan->pasien) {
                    return e($row->kunjungan->pasien->nama_pasien);
                }

                // 2) fallback: cari dari penjualan_obat (alur beli obat langsung)
                //    ambil pasien terakhir di hari yang sama, dengan obat yang ada pada resep ini
                $obatIds = $row->obat->pluck('id')->all();
                if (!empty($obatIds)) {
                    $pasienId = DB::table('penjualan_obat')
                        ->whereIn('obat_id', $obatIds)
                        ->whereDate('tanggal_transaksi', $row->created_at->toDateString())
                        ->orderByDesc('tanggal_transaksi')
                        ->value('pasien_id');

                    if ($pasienId) {
                        $nama = DB::table('pasien')->where('id', $pasienId)->value('nama_pasien');
                        if ($nama) return e($nama);
                    }
                }

                return '<span class="italic text-gray-400">-</span>';
            })

            // 🔹 Nomor Antrian (tetap '-' jika tidak ada)
            ->addColumn('no_antrian', function ($row) {
                return e($row->kunjungan->no_antrian ?? '-');
            })

            // 🔹 Tanggal Kunjungan / Transaksi
            ->addColumn('tanggal_kunjungan', function ($row) {
                if ($row->kunjungan && $row->kunjungan->tanggal_kunjungan) {
                    return e($row->kunjungan->tanggal_kunjungan);
                }
                return e($row->created_at->format('Y-m-d'));
            })

            // 🔹 Nama Obat
            ->addColumn('nama_obat', function ($row) {
                if ($row->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }
                $html = '<ul class="list-disc pl-4">';
                foreach ($row->obat as $obat) {
                    $html .= '<li>' . e($obat->nama_obat) . '</li>';
                }
                $html .= '</ul>';
                return $html;
            })

            // 🔹 Jumlah
            ->addColumn('jumlah', function ($row) {
                if ($row->obat->isEmpty()) return '-';
                $html = '<ul class="list-disc pl-4">';
                foreach ($row->obat as $obat) {
                    $html .= '<li>' . e($obat->pivot->jumlah ?? '-') . '</li>';
                }
                $html .= '</ul>';
                return $html;
            })

            // 🔹 Keterangan
            ->addColumn('keterangan', function ($row) {
                if ($row->obat->isEmpty()) return '-';
                $html = '<ul class="list-disc pl-4">';
                foreach ($row->obat as $obat) {
                    $html .= '<li>' . e($obat->pivot->keterangan ?? '-') . '</li>';
                }
                $html .= '</ul>';
                return $html;
            })

            // 🔹 Status
            ->addColumn('status', function ($row) {
                if ($row->obat->isEmpty()) return '-';
                $html = '<ul class="list-disc pl-4">';
                foreach ($row->obat as $obat) {
                    $status = $obat->pivot->status ?? 'Belum Diambil';
                    $color = $status === 'Sudah Diambil' ? 'text-green-600' : 'text-red-600';
                    $html .= "<li class='{$color} font-semibold'>" . e($status) . '</li>';
                }
                $html .= '</ul>';
                return $html;
            })

            // 🔹 Action Button
            ->addColumn('action', function ($row) {
                if ($row->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada tindakan</span>';
                }

                $dataObat = $row->obat->map(fn($ob) => [
                    'id'     => $ob->id,
                    'jumlah' => $ob->pivot->jumlah
                ]);
                $jsonObat = e(json_encode($dataObat));

                return '
                <button class="btnUpdateStatus text-blue-600 hover:text-blue-800"
                        data-resep-id="' . $row->id . '"
                        data-obat=\'' . $jsonObat . '\'
                        title="Update Status">
                    <i class="fa-regular fa-pen-to-square"></i> Update Status
                </button>';
            })

            ->rawColumns([
                'nama_dokter',
                'nama_pasien',
                'no_antrian',
                'tanggal_kunjungan',
                'nama_obat',
                'jumlah',
                'keterangan',
                'status',
                'action'
            ])
            ->make(true);
    }
}
