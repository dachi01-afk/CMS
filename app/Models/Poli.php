<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poli extends Model
{
    protected $table = 'poli';
    protected $guarded = [];

    // 🔁 Banyak dokter di satu poli (via tabel pivot dokter_poli)
    public function dokter()
    {
        return $this->belongsToMany(Dokter::class, 'dokter_poli', 'poli_id', 'dokter_id')
            ->withTimestamps();
    }

    // 🔁 Satu poli bisa punya banyak kunjungan
    public function kunjungan()
    {
        return $this->hasMany(Kunjungan::class);
    }
}
