<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kunjungan extends Model
{
    use HasFactory;

    protected $table = 'kunjungan';
    protected $primaryKey = 'id_kunjungan';
    protected $guarded = [];

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class, 'pasien_id', 'id_pasien');
    }

    public function tenagaMedis(): BelongsTo
    {
        return $this->belongsTo(TenagaMedis::class, 'tenaga_medis_id', 'id_tenaga_medis');
    }

    public function poli(): BelongsTo
    {
        return $this->belongsTo(Poli::class, 'poli_id', 'id_poli');
    }

    public function rekamMedis(): HasOne
    {
        return $this->hasOne(RekamMedis::class, 'kunjungan_id', 'id_kunjungan');
    }

    public function vitalSign(): HasOne
    {
        return $this->hasOne(VitalSign::class, 'kunjungan_id', 'id_kunjungan');
    }

    public function pengantar(): HasOne
    {
        return $this->hasOne(Pengantar::class, 'kunjungan_id', 'id_kunjungan');
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'kunjungan_id', 'id_kunjungan');
    }
}
