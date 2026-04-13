<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmrPengkajianAwalPenyakitDalamRiwayat extends Model
{
    use HasFactory;

    protected $table = 'emr_pengkajian_awal_penyakit_dalam_riwayat';

    protected $fillable = [
        'pengkajian_id',
        'riwayat_penyakit',
        'tahun',
        'riwayat_pengobatan',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    public function pengkajian()
    {
        return $this->belongsTo(EmrPengkajianAwalPenyakitDalam::class, 'pengkajian_id');
    }
}