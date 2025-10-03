<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResepObat extends Model
{
    use HasFactory;

    protected $table = 'resep_obat';
    protected $primaryKey = 'id_resep';
    protected $guarded = [];

    public function rekamMedis(): BelongsTo
    {
        return $this->belongsTo(RekamMedis::class, 'rekam_medis_id', 'id_rekam_medis');
    }

    public function obat(): BelongsTo
    {
        return $this->belongsTo(DataObat::class, 'obat_id', 'id_obat');
    }

    public function detailPembayaran(): HasOne
    {
        return $this->hasOne(DetailPembayaran::class, 'resep_id', 'id_resep');
    }
}
