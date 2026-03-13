<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\Depot;
use App\Models\Farmasi;
use App\Models\KategoriObat;
use App\Models\Obat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StokObatController extends Controller
{
    protected $batasStokMenipis = 10;

    public function stokObatPage()
    {
        $userId = Auth::id();
        $namaFarmasi = Farmasi::where('user_id', $userId)->value('nama_farmasi') ?? 'Farmasi';

        $kategoriObat = KategoriObat::orderBy('nama_kategori_obat', 'asc')->get();
        $depotList = Depot::orderBy('nama_depot', 'asc')->get();
        $batasStokMenipis = $this->batasStokMenipis;

        return view('farmasi.stok-obat.index', compact(
            'namaFarmasi',
            'kategoriObat',
            'depotList',
            'batasStokMenipis'
        ));
    }

    public function stokObatData(Request $request)
    {
        $status = $request->get('status', 'semua');
        $kategoriObatId = $request->get('kategori_obat_id');
        $depotId = $request->get('depot_id');
        $keyword = trim($request->get('keyword', ''));

        $query = Obat::query()
            ->with([
                'kategoriObat:id,nama_kategori_obat',
                'satuanObat:id,nama_satuan_obat',
                'batchObat' => function ($query) use ($depotId) {
                    $query->select('id', 'obat_id', 'nama_batch', 'tanggal_kadaluarsa_obat')
                        ->with([
                            'batchObatDepot' => function ($subQuery) use ($depotId) {
                                $subQuery->select('id', 'batch_obat_id', 'depot_id', 'stok_obat');

                                if (!empty($depotId)) {
                                    $subQuery->where('depot_id', $depotId);
                                }
                            }
                        ]);
                },
            ])
            ->select([
                'id',
                'kode_obat',
                'nama_obat',
                'jumlah',
                'kategori_obat_id',
                'satuan_obat_id',
            ]);

        if (!empty($kategoriObatId)) {
            $query->where('kategori_obat_id', $kategoriObatId);
        }

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('kode_obat', 'like', "%{$keyword}%")
                    ->orWhere('nama_obat', 'like', "%{$keyword}%")
                    ->orWhereHas('kategoriObat', function ($subQuery) use ($keyword) {
                        $subQuery->where('nama_kategori_obat', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('satuanObat', function ($subQuery) use ($keyword) {
                        $subQuery->where('nama_satuan_obat', 'like', "%{$keyword}%");
                    });
            });
        }

        $data = $query->orderBy('nama_obat', 'asc')
            ->get()
            ->map(function ($item) {
                $stokMaster = (int) ($item->jumlah ?? 0);

                $batchAktif = $item->batchObat
                    ->filter(function ($batch) {
                        $totalStokBatch = (int) $batch->batchObatDepot->sum(function ($batchDepot) {
                            return (int) ($batchDepot->stok_obat ?? 0);
                        });

                        return $totalStokBatch > 0;
                    })
                    ->sortBy('tanggal_kadaluarsa_obat')
                    ->values();

                $kadaluarsaTerdekat = optional($batchAktif->first())->tanggal_kadaluarsa_obat;

                $statusStok = $stokMaster <= 0
                    ? 'Habis'
                    : ($stokMaster <= $this->batasStokMenipis ? 'Menipis' : 'Aman');

                return [
                    'id' => $item->id,
                    'kode_obat' => $item->kode_obat ?? '-',
                    'nama_obat' => $item->nama_obat ?? '-',
                    'kategori_obat' => optional($item->kategoriObat)->nama_kategori_obat ?? '-',
                    'satuan_obat' => optional($item->satuanObat)->nama_satuan_obat ?? '-',
                    'stok_master' => $stokMaster,
                    'kadaluarsa_terdekat' => $kadaluarsaTerdekat,
                    'status_stok' => $statusStok,
                ];
            })
            ->values();

        if ($status !== 'semua') {
            $mapStatus = [
                'aman' => 'Aman',
                'menipis' => 'Menipis',
                'habis' => 'Habis',
            ];

            if (isset($mapStatus[$status])) {
                $data = $data->filter(function ($row) use ($mapStatus, $status) {
                    return $row['status_stok'] === $mapStatus[$status];
                })->values();
            }
        }

        return response()->json([
            'data' => $data,
            'meta' => [
                'total_item' => $data->count(),
                'total_stok_tampil' => $data->sum(function ($row) {
                    return (int) ($row['stok_master'] ?? 0);
                }),
                'stok_menipis' => $data->where('status_stok', 'Menipis')->count(),
                'stok_habis' => $data->where('status_stok', 'Habis')->count(),
            ],
        ]);
    }

    public function stokObatDetail(Request $request, $id)
    {
        $depotId = $request->get('depot_id');
        $today = Carbon::today();
        $batasSegeraKadaluarsa = Carbon::today()->addDays(30);

        $obat = Obat::query()
            ->with([
                'kategoriObat:id,nama_kategori_obat',
                'satuanObat:id,nama_satuan_obat',
                'batchObat' => function ($query) use ($depotId) {
                    $query->select('id', 'obat_id', 'nama_batch', 'tanggal_kadaluarsa_obat')
                        ->orderBy('tanggal_kadaluarsa_obat', 'asc')
                        ->with([
                            'batchObatDepot' => function ($subQuery) use ($depotId) {
                                $subQuery->select('id', 'batch_obat_id', 'depot_id', 'stok_obat')
                                    ->with('depot:id,nama_depot');

                                if (!empty($depotId)) {
                                    $subQuery->where('depot_id', $depotId);
                                }
                            }
                        ]);
                },
            ])
            ->find($id);

        if (!$obat) {
            return response()->json([
                'data' => null,
                'message' => 'Data obat tidak ditemukan.',
            ], 404);
        }

        $batchList = collect();

        foreach ($obat->batchObat as $batch) {
            foreach ($batch->batchObatDepot as $batchDepot) {
                $stokBatch = (int) ($batchDepot->stok_obat ?? 0);

                if ($stokBatch <= 0) {
                    continue;
                }

                $statusBatch = 'Aman';
                $tanggalKadaluarsa = Carbon::parse($batch->tanggal_kadaluarsa_obat)->startOfDay();

                if ($tanggalKadaluarsa->lt($today)) {
                    $statusBatch = 'Kadaluarsa';
                } elseif ($tanggalKadaluarsa->lte($batasSegeraKadaluarsa)) {
                    $statusBatch = 'Segera Kadaluarsa';
                }

                $batchList->push([
                    'nama_batch' => $batch->nama_batch,
                    'tanggal_kadaluarsa_obat' => $batch->tanggal_kadaluarsa_obat,
                    'stok_batch' => $stokBatch,
                    'nama_depot' => optional($batchDepot->depot)->nama_depot ?? '-',
                    'status_batch' => $statusBatch,
                ]);
            }
        }

        $namaDepotAktif = 'Semua Depot';
        if (!empty($depotId)) {
            $namaDepotAktif = Depot::where('id', $depotId)->value('nama_depot') ?? 'Semua Depot';
        }

        return response()->json([
            'data' => [
                'id' => $obat->id,
                'kode_obat' => $obat->kode_obat ?? '-',
                'nama_obat' => $obat->nama_obat ?? '-',
                'kategori_obat' => optional($obat->kategoriObat)->nama_kategori_obat ?? '-',
                'satuan_obat' => optional($obat->satuanObat)->nama_satuan_obat ?? '-',
                'stok_master' => (int) ($obat->jumlah ?? 0),
                'total_batch' => $batchList->count(),
                'nama_depot_aktif' => $namaDepotAktif,
                'batch_list' => $batchList->values(),
            ]
        ]);
    }
}
