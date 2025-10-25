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
            ->withPivot('jumlah', 'dosis', 'keterangan', 'status');
    }

    public function pasien()
    {
        return $this->belongsToMany(Pasien::class, 'penjualan_obat', 'obat_id', 'pasien_id')
            ->withPivot('kode_transaksi', 'jumlah', 'sub_total', 'tanggal_transaksi')
            ->withTimestamps();
    }
}
