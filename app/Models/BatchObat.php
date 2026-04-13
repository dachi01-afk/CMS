<?php

namespace App\Models;

use App\Models\BatchObatDepot;
use App\Models\Obat;
use App\Models\RestockObatDetail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class BatchObat extends Model
{
    protected $table = 'batch_obat';

    protected $guarded = [];

    protected $casts = [
        'tanggal_kadaluarsa_obat' => 'datetime',
    ];

    protected $appends = [
        'format_tanggal_kadaluarsa_obat',
    ];
    
    protected function formatTanggalKadaluarsaObat() : Attribute
    {
        return Attribute::make(get: fn() => $this->tanggal_kadaluarsa_obat->translatedFormat('d F Y') ?? '-');
    }

    public function obat()
    {
        return $this->belongsTo(Obat::class);
    }

    public function batchObatDepot()
    {
        return $this->hasMany(BatchObatDepot::class);
    }

    public function restockObatDetail()
    {
        return $this->hasMany(RestockObatDetail::class);
    }
}
