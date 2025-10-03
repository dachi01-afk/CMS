<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pasien extends Model
{
    use HasFactory;

    protected $table = 'pasien';
    protected $primaryKey = 'id_pasien';
    protected $guarded = [];

    public function penanggungJawab(): HasOne
    {
        return $this->hasOne(PenanggungJawab::class, 'pasien_id', 'id_pasien');
    }

    public function riwayatPasien(): HasOne
    {
        return $this->hasOne(RiwayatPasien::class, 'pasien_id', 'id_pasien');
    }

    public function psikososialSpiritual(): HasOne
    {
        return $this->hasOne(PsikososialSpiritual::class, 'pasien_id', 'id_pasien');
    }

    public function kunjungan(): HasMany
    {
        return $this->hasMany(Kunjungan::class, 'pasien_id', 'id_pasien');
    }
}
