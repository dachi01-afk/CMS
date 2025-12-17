<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BahanHabisPakai extends Model
{
    protected $table = 'bahan_habis_pakai';

    protected $guarded = [];

    public function brandFarmasi()
    {
        return $this->belongsTo(BrandFarmasi::class);
    }
    public function jenisBHP()
    {
        return $this->belongsTo(JenisObat::class, 'jenis_id');
    }
    public function satuanBHP()
    {
        return $this->belongsTo(SatuanObat::class, 'satuan_id');
    }
    public function depot()
    {
        return $this->belongsTo(Depot::class);
    }
    public function depotBHP()
    {
        return $this->belongsToMany(Depot::class, 'depot_bhp', 'bahan_habis_pakai_id', 'depot_id')->withPivot('stok');
    }
}
