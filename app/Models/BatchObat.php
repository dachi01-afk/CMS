<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchObat extends Model
{
    protected $table = 'batch_obat';

    protected $guarded = [];

    public function obat()
    {
        return $this->belongsTo(Obat::class);
    }

    public function batchObatDepot()
    {
        return $this->hasMany(BatchObatDepot::class);
    }
}
