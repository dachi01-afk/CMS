<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TesLab extends Model
{
    protected $table = 'tes_lab';

    protected $guarded = [];

    protected $casts = [
        'jenis_tes' => 'array',
    ];

    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class, 'kunjungan_id');
    }
}
