<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmrPengkajianAwalPenyakitDalamPenunjang extends Model
{
    use HasFactory;

    protected $table = 'emr_pengkajian_awal_penyakit_dalam_penunjang';

    protected $fillable = [
        'pengkajian_id',
        'jenis_penunjang',
        'jenis_penunjang_lainnya',
        'hasil_penunjang',
        'tanggal_penunjang',
        'urutan',
    ];

    protected $casts = [
        'tanggal_penunjang' => 'datetime',
        'urutan' => 'integer',
    ];

    public function pengkajian()
    {
        return $this->belongsTo(EmrPengkajianAwalPenyakitDalam::class, 'pengkajian_id');
    }
}