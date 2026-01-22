<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    protected $table = 'dokter';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔁 Relasi many-to-many ke Poli lewat tabel dokter_poli
    // Nama method tetap "poli" biar kompatibel dengan kode lama (DataTables, dll).
    public function poli()
    {
        return $this->belongsToMany(Poli::class, 'dokter_poli', 'dokter_id', 'poli_id')
            ->withTimestamps();
    }

    // Opsional: akses langsung ke row pivot dokter_poli
    public function dokterPoli()
    {
        return $this->hasMany(DokterPoli::class);
    }

    public function jenisSpesialis()
    {
        return $this->belongsTo(JenisSpesialis::class);
    }

    public function jadwalDokter()
    {
        return $this->hasMany(JadwalDokter::class);
    }

    public function orderLayanan()
    {
        return $this->hasMany(OrderLayanan::class);
    }

    public function orderLab() {
        return $this->hasMany(OrderLab::class);
    }
}
