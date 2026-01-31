<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Obat extends Model
{
    protected $table = 'obat';

    protected $guarded = [];

    public function resep()
    {
        return $this->belongsToMany(Resep::class, 'resep_obat', 'obat_id', 'resep_id')
            ->withPivot('jumlah', 'dosis', 'keterangan');
    }

    public function pasien()
    {
        return $this->belongsToMany(Pasien::class, 'penjualan_obat', 'obat_id', 'pasien_id')
            ->withPivot('kode_transaksi', 'jumlah', 'sub_total', 'tanggal_transaksi')
            ->withTimestamps();
    }

    public function brandFarmasi()
    {
        return $this->belongsTo(BrandFarmasi::class);
    }

    public function kategoriObat()
    {
        return $this->belongsTo(KategoriObat::class);
    }

    public function jenisObat()
    {
        return $this->belongsTo(JenisObat::class);
    }

    public function satuanObat()
    {
        return $this->belongsTo(SatuanObat::class);
    }

    public function depot()
    {
        return $this->belongsTo(Depot::class);
    }

    public function depotObat()
    {
        return $this->belongsToMany(Depot::class, 'depot_obat', 'obat_id', 'depot_id')->withPivot('stok_obat')->withTimestamps();
    }

    public function stokTransaksiDetail()
    {
        return $this->hasMany(StokTransaksiDetail::class);
    }

    public function mutasiStokObatDetail()
    {
        return $this->hasMany(MutasiStokObatDetail::class);
    }

    public function batchObat()
    {
        return $this->hasMany(BatchObat::class);
    }
}
