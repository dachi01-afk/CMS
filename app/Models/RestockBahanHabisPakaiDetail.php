<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestockBahanHabisPakaiDetail extends Model
{
    protected $table = 'restock_bahan_habis_pakai_detail';

    protected $guarded = [];

    public function restockBahanHabisPakai()
    {
        return $this->belongsTo(RestockBahanHabisPakai::class);
    }

    public function bahanHabisPakai()
    {
        return $this->belongsTo(BahanHabisPakai::class);
    }

    public function batchBahanHabisPakai()
    {
        return $this->belongsTo(BatchBahanHabisPakai::class);
    }
}
