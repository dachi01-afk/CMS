<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TenagaMedis extends Model
{
    use HasFactory;

    protected $table = 'tenaga_medis';
    protected $primaryKey = 'id_tenaga_medis';
    protected $guarded = [];

    public function poli(): BelongsToMany
    {
        return $this->belongsToMany(Poli::class, 'tenaga_medis_poli', 'tenaga_medis_id', 'poli_id');
    }

    public function jadwalPraktik(): HasMany
    {
        return $this->hasMany(JadwalPraktik::class, 'tenaga_medis_id', 'id_tenaga_medis');
    }
}
