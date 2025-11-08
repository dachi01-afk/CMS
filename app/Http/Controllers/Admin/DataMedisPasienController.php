<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\EMR;
use App\Models\Konsul;
use App\Models\TesLab;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\DataTables;

class DataMedisPasienController extends Controller
{
    public function index()
    {
        $dataEMR = EMR::with('kunjungan.pasien', 'kunjungan.poli.dokter', 'kunjungan', 'resep.obat')->paginate(10);
        return view('admin.data_medis_pasien', compact('dataEMR'));
    }


    public function getDataEMR()
    {
        $dataEMR = EMR::with([
            'kunjungan.pasien',
            'kunjungan.poli.dokter', // fallback kalau tidak ada di cache
            'resep.obat',
        ])->latest()->get();

        // helper kecil: resolve nama dokter terpilih per EMR (pakai static cache in-process biar hemat query)
        $resolveNamaDokter = function ($emr) {
            $kunj = $emr->kunjungan;
            if (!$kunj) return '-';

            $cached = Cache::get("kunjungan_dokter:{$kunj->id}");
            if (!empty($cached['dokter_id'])) {
                static $dokterNameCache = [];
                $dokterId = (int) $cached['dokter_id'];

                if (!array_key_exists($dokterId, $dokterNameCache)) {
                    $dokterNameCache[$dokterId] = DB::table('dokter')
                        ->where('id', $dokterId)
                        ->value('nama_dokter');
                }
                return $dokterNameCache[$dokterId] ?: '-';
            }

            // fallback: ambil dokter pertama yang terhubung ke poli
            return $kunj->poli?->dokter?->first()?->nama_dokter ?? '-';
        };

        return DataTables::of($dataEMR)
            ->addIndexColumn()
            ->addColumn('nama_pasien', fn($emr) => $emr->kunjungan?->pasien?->nama_pasien ?? '-')
            ->addColumn('nama_dokter', fn($emr) => $resolveNamaDokter($emr))
            ->addColumn('tanggal_kunjungan', fn($emr) => $emr->kunjungan?->tanggal_kunjungan ?? '-')
            ->addColumn('keluhan_awal', fn($emr) => $emr->kunjungan?->keluhan_awal ?? '-')
            ->addColumn('keluhan_utama', fn($emr) => $emr->keluhan_utama ?? '-')
            ->addColumn('riwayat_penyakit_dahulu', fn($emr) => $emr->riwayat_penyakit_dahulu ?? '-')
            ->addColumn('riwayat_penyakit_keluarga', fn($emr) => $emr->riwayat_penyakit_keluarga ?? '-')
            ->addColumn('tekanan_darah', fn($emr) => $emr->tekanan_darah ?? '-')
            ->addColumn('suhu_tubuh', fn($emr) => $emr->suhu_tubuh ?? '-')
            ->addColumn('nadi', fn($emr) => $emr->nadi ?? '-')
            ->addColumn('pernapasan', fn($emr) => $emr->pernapasan ?? '-')
            ->addColumn('saturasi_oksigen', fn($emr) => $emr->saturasi_oksigen ?? '-')
            ->addColumn('diagnosis', fn($emr) => $emr->diagnosis ?? '-')
            ->addColumn('action', function ($emr) use ($resolveNamaDokter) {
                $namaDokter = $resolveNamaDokter($emr);
                return '
                <button class="btn-detail-emr text-blue-600 hover:text-blue-800 mr-2 text-center items-center"
                        data-id="' . $emr->id . '"
                        data-dokter="' . e($namaDokter) . '"
                        title="Detail">
                    <i class="fa-solid fa-circle-info text-lg"></i> Lihat Detail
                </button>
            ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function detailEMR($id)
    {
        // Ambil EMR + relasi yang dibutuhkan
        $emr = EMR::with([
            'kunjungan.pasien',
            'kunjungan.poli.dokter',   // fallback bila cache dokter kosong
            'resep.obat'               // asumsi: relasi many-to-many/hasMany ke obat
        ])->findOrFail($id);

        $kunjungan = $emr->kunjungan;
        $pasien    = $kunjungan?->pasien;
        $poli      = $kunjungan?->poli;

        // Ambil dokter terpilih dari cache KYAD; fallback ke dokter pertama poli
        $dokterTerpilih = null;
        if ($kunjungan) {
            $cached = Cache::get("kunjungan_dokter:{$kunjungan->id}");
            if (!empty($cached['dokter_id'])) {
                $dokterTerpilih = DB::table('dokter')
                    ->select('id', 'nama_dokter')
                    ->where('id', (int)$cached['dokter_id'])
                    ->first();
            }
        }
        if (!$dokterTerpilih) {
            $dokterTerpilih = optional($poli?->dokter?->first())->only(['id', 'nama_dokter']);
            if (is_array($dokterTerpilih)) {
                $dokterTerpilih = (object)$dokterTerpilih;
            }
        }

        // Siapkan resep + obat (nama, dosis, aturan pakai bila ada di pivot)
        $resep = $emr->resep;
        $obatItems = [];
        if ($resep && $resep->relationLoaded('obat')) {
            foreach ($resep->obat as $o) {
                $obatItems[] = [
                    'nama'         => $o->nama_obat ?? $o->nama ?? '(Tanpa nama)',
                    'dosis'        => $o->pivot->dosis        ?? $o->dosis        ?? '-',
                    'aturan_pakai' => $o->pivot->aturan_pakai ?? $o->aturan_pakai ?? '-',
                ];
            }
        }

        // Data ringkas untuk Blade
        return view('admin.dataMedisPasien.detail-emr', [
            'emr'            => $emr,
            'kunjungan'      => $kunjungan,
            'pasien'         => $pasien,
            'poli'           => $poli,
            'dokter'         => $dokterTerpilih, // object {id, nama_dokter} atau null
            'obatItems'      => $obatItems,
        ]);
    }


    public function getDataEMRById($id)
    {
        $dataEMR = EMR::with('kunjungan.pasien', 'kunjungan.poli.dokter', 'resep.obat')->findOrFail($id);

        return response()->json([
            'data' => $dataEMR,
        ]);
    }

    // public function dataRekamMedis()
    // {
    //     $query = EMR::with(['kunjungan.dokter:id,nama_dokter', 'kunjungan.pasien:id,nama_pasien'])
    //         ->select(['id', 'kunjungan_id', 'riwayat_penyakit', 'alergi', 'hasil_periksa']);

    //     return DataTables::of($query)
    //         ->addColumn('dokter', fn($rekam) => $rekam->kunjungan->dokter->nama_dokter ?? '-')
    //         ->addColumn('pasien', fn($rekam) => $rekam->kunjungan->pasien->nama_pasien ?? '-')
    //         ->addColumn('tanggal_kunjungan', fn($rekam) => $rekam->kunjungan->tanggal_kunjungan ?? '-')
    //         ->make(true);
    // }

    // public function dataKonsultasi()
    // {
    //     $query = Konsul::with([
    //         'kunjungan.dokter:id,nama_dokter',
    //         'kunjungan.pasien:id,nama_pasien'
    //     ])
    //         ->select(['id', 'kunjungan_id', 'diagnosa', 'catatan']);

    //     return DataTables::of($query)
    //         ->addColumn('dokter', fn($konsul) => $konsul->kunjungan->dokter->nama_dokter ?? '-')
    //         ->addColumn('pasien', fn($konsul) => $konsul->kunjungan->pasien->nama_pasien ?? '-')
    //         ->addColumn('tanggal_kunjungan', fn($konsul) => $konsul->kunjungan->tanggal_kunjungan ?? '-')
    //         ->make(true);
    // }

    // public function dataLab()
    // {
    //     $query = TesLab::with(['kunjungan.dokter:id,nama_dokter', 'kunjungan.pasien:id,nama_pasien'])
    //         ->select(['id', 'kunjungan_id', 'jenis_tes', 'hasil_tes', 'tanggal_tes']);

    //     return DataTables::of($query)
    //         ->addColumn('dokter', fn($lab) => $lab->kunjungan->dokter->nama_dokter ?? '-')
    //         ->addColumn('pasien', fn($lab) => $lab->kunjungan->pasien->nama_pasien ?? '-')
    //         ->editColumn('jenis_tes', function ($lab) {
    //             $jenis = json_decode($lab->jenis_tes, true);
    //             return is_array($jenis) ? implode(', ', $jenis) : $lab->jenis_tes;
    //         })
    //         ->make(true);
    // }


    // public function getDataEMR(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $data = EMR::with(['kunjungan.dokter', 'kunjungan.pasien'])->select('emr.*');

    //         return DataTables::of($data)
    //             ->addIndexColumn()
    //             ->addColumn('nama_dokter', function ($row) {
    //                 return optional($row->kunjungan->dokter)->nama_dokter ?? '-';
    //             })
    //             ->addColumn('nama_pasien', function ($row) {
    //                 return optional($row->kunjungan->pasien)->nama_pasien ?? '-';
    //             })
    //             ->addColumn('tanggal_kunjungan', function ($row) {
    //                 return $row->kunjungan && $row->kunjungan->tanggal_kunjungan
    //                     ? Carbon::parse($row->kunjungan->tanggal_kunjungan)->format('d-m-Y')
    //                     : '-';
    //             })
    //             ->addColumn('diagnosa', function ($row) {
    //                 return $row->diagnosis ?? '-';
    //             })
    //             ->addColumn('catatan', function ($row) {
    //                 return $row->riwayat_penyakit_sekarang ?? '-';
    //             })
    //             ->rawColumns(['nama_dokter', 'nama_pasien', 'diagnosa', 'catatan'])
    //             ->make(true);
    //     }
    // }
}
