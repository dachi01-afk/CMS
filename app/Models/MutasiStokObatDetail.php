<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MutasiStokObatDetail extends Model
{
    protected $table = 'mutasi_stok_obat_detail';

    protected $guarded = [];

    public function mutasiStokObat()
    {
        return $this->belongsTo(MutasiStokObat::class);
    }
    public function obat()
    {
        return $this->belongsTo(Obat::class);
    }
    public function depot()
    {
        return $this->belongsTo(Depot::class);
    }
}
