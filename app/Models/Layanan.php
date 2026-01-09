<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    protected $table = 'layanan';
    protected $guarded = [];

    public function kunjungan()
    {
        return $this->belongsToMany(
            Kunjungan::class,
            'kunjungan_layanan',
            'layanan_id',
            'kunjungan_id'
        )->withPivot('jumlah')
            ->withTimestamps();
    }

    public function kategoriLayanan()
    {
        return $this->belongsTo(KategoriLayanan::class);
    }
    public function penjualanLayanan()
    {
        return $this->hasMany(PenjualanLayanan::class);
    }

    public function orderLayananDetail()
    {
        return $this->hasMany(OrderLayananDetail::class);
    }

    public function layananPoli()
    {
        return $this->belongsToMany(Poli::class, 'layanan_poli', 'layanan_id', 'poli_id');
    }
}
