<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    protected $table = 'dokter';

    protected $guarded = [];

    protected $casts = [
        'hari' => 'array'
    ];

    public function user()
    {
        $this->belongsTo(User::class);
    }

    public function jadwalDokter()
    {
        return $this->hasMany(JadwalDokter::class);
    }
}
