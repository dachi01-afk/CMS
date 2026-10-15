<?php

namespace App\Models;

use App\Support\Audit\Auditable;
use Illuminate\Database\Eloquent\Model;

class BatchObatDepot extends Model
{
    use Auditable;

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
