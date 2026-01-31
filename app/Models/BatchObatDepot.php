<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchObatDepot extends Model
{
    protected $table = 'batch_obat_depot';

    protected $guarded = [];

    public function batchObat()
    {
        return $this->belongsTo(BatchObat::class);
    }

    public function depot()
    {
        return $this->belongsTo(Depot::class);
    }
}
