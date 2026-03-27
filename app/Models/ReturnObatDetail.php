<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnObatDetail extends Model
{
    protected $table = 'return_obat_detail';

    protected $guarded = [];

    public function returnObat()
    {
        return $this->belongsTo(ReturnObat::class, 'return_obat_id');
    }

    public function obat()
    {
        return $this->belongsTo(Obat::class, 'obat_id');
    }

    public function batchObat()
    {
        return $this->belongsTo(BatchObat::class, 'batch_obat_id');
    }
}
