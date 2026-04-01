<?php

namespace App\Models;

use App\Models\BahanHabisPakai;
use App\Models\BatchBahanHabisPakaiDepot;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class BatchBahanHabisPakai extends Model
{
    protected $table = 'batch_bahan_habis_pakai';

    protected $guarded = [];

    protected $casts = [
        'tanggal_kadaluarsa_bahan_habis_pakai' => 'datetime',
    ];

    protected $appends = [
        'format_tanggal_kadaluarsa_bahan_habis_pakai'
    ];

    protected function formatTanggalKadaluarsaBahanHabisPakai(): Attribute
    {
        return Attribute::make(get: fn() => $this->tanggal_kadaluarsa_bahan_habis_pakai->translatedFormat('d F Y') ?? '-');
    }

    public function batchBahanHabisPakaiDepot()
    {
        return $this->hasMany(BatchBahanHabisPakaiDepot::class);
    }

    public function bahanHabisPakai()
    {
        return $this->belongsTo(BahanHabisPakai::class);
    }

    public static function createData($dataBhpId, $data)
    {
        return self::create([
            'bahan_habis_pakai_id'                      => $dataBhpId,
            'nama_batch'                                => $data['no_batch'],
            'tanggal_kadaluarsa_bahan_habis_pakai'      => $data['tanggal_kadaluarsa_bhp'],
        ]);
    }
}
