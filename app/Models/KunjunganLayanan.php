<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KunjunganLayanan extends Model
{
    protected $table = 'kunjungan_layanan';

    protected $guarded = [];

    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class);
    }

    // Di app/Models/KunjunganLayanan.php
public function layanan()
{
    return $this->belongsTo(Layanan::class, 'layanan_id');
}
}
