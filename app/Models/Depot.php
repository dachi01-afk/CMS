<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Depot extends Model
{
    protected $table = 'depot';

    protected $guarded = [];

    public function obat()
    {
        return $this->hasMany(Obat::class);
    }

    public function bph()
    {
        return $this->hasMany(BahanHabisPakai::class);
    }

    public function stokTransaksiDetal()
    {
        return $this->hasMany(StokTransaksiDetail::class);
    }

    public function tipeDepot()
    {
        return $this->belongsTo(TipeDepot::class);
    }

    public function depotObat()
    {
        return $this->belongsToMany(Obat::class, 'depot_obat', 'depot_id', 'obat_id')->withPivot('stok_obat')->withTimestamps();
    }

    public function depotBHP()
    {
        return $this->belongsToMany(BahanHabisPakai::class, 'depot_bhp', 'depot_id', 'bahan_habis_pakai_id')->withPivot('stok_barang')->withTimestamps();
    }
}
