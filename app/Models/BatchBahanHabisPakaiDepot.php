<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchBahanHabisPakaiDepot extends Model
{
    protected $table = 'batch_bahan_habis_pakai_depot';

    protected $guarded = [];

    public function batchBahanHabisPakai()
    {
        return $this->belongsTo(BatchBahanHabisPakai::class);
    }

    public function depot()
    {
        return $this->belongsTo(Depot::class);
    }

    public static function createData($dataBatchBhpId, $data)
    {
        $depotId    = $data['depot_id'] ?? [];
        $stokDepot  = $data['stok_depot']  ?? [];
        $results     = [];

        foreach ($depotId as $index => $depId) {
            if (empty($depId)) continue;

            $results[] = self::create([
                'batch_bahan_habis_pakai_id'    => $dataBatchBhpId,
                'depot_id'                      => $depId,
                'stok_bahan_habis_pakai'        => $stokDepot[$index] ?? 0,
            ]);
        };

        return $results;
    }
}
