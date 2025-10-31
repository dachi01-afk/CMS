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
}
