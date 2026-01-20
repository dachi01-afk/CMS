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
        return $this->belongsToMany(Depot::class, 'depot_bhp', 'bahan_habis_pakai_id', 'depot_id')
            ->withPivot('stok_barang')->withTimestamps();
    }

    public function stokTransaksiDetail()
    {
        return $this->hasMany(StokTransaksiDetail::class, 'bahan_habis_pakai_id');
    }

    public function scopeGetData($query)
    {
        return $query->select(
            'id',
            'nama_barang',
            'kode',
            'harga_jual_umum_bhp',
            'stok_barang',
        )->where('stok_barang', '>', 0)->orderBy('nama_barang', 'asc');
    }

    
}
