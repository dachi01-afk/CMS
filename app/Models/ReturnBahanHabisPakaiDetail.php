<?php

namespace App\Models;

use App\Models\Depot;
use App\Models\ReturnBahanHabisPakai;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Model;

class ReturnBahanHabisPakaiDetail extends Model
{
    protected $table = 'return_bahan_habis_pakai_detail';

    protected $guarded = [];

    public function returnBahanHabisPakai()
    {
        return $this->belongsTo(ReturnBahanHabisPakai::class, 'return_bahan_habis_pakai_id');
    }

    public function batchBahanHabisPakai()
    {
        return $this->belongsTo(BatchBahanHabisPakai::class);
    }

    public function bahanHabisPakai()
    {
        return $this->belongsTo(BahanHabisPakai::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function depot()
    {
        return $this->belongsTo(Depot::class, 'depot_id');
    }
}
