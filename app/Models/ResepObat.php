<?php

namespace App\Models;

use App\Support\Audit\Auditable;
use Illuminate\Database\Eloquent\Model;

class ResepObat extends Model
{
    use Auditable;

    protected $table = 'resep_obat';

    protected $guarded = [];

    public function resep()
    {
        return $this->belongsTo(Resep::class);
    }

    public function obat()
    {
        return $this->belongsTo(Obat::class);
    }
}
