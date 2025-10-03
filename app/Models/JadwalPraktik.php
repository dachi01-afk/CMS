<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JadwalPraktik extends Model
{
    use HasFactory;

    protected $table = 'jadwal_praktik';
    protected $primaryKey = 'id_jadwal';
    protected $guarded = [];

    public function tenagaMedis(): BelongsTo
    {
        return $this->belongsTo(TenagaMedis::class, 'tenaga_medis_id', 'id_tenaga_medis');
    }
}
