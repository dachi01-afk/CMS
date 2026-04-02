<?php

namespace App\Models;

use App\Models\BatchObat;
use App\Models\Obat;
use App\Models\ReturnObat;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class ReturnObatDetail extends Model
{
    protected $table = 'return_obat_detail';

    protected $guarded = [];

    protected $appends = [
        'format_harga_beli',
        'format_subtotal',
    ];

    protected function formatHargaBeli(): Attribute
    {
        return Attribute::make(get: fn() => 'Rp. ' . number_format($this->harga_beli, 0, ',', '.'));
    }
    
    protected function formatSubtotal(): Attribute
    {
        return Attribute::make(get: fn() => 'Rp. ' . number_format($this->subtotal, 0, ',', '.'));
    }

    public function returnObat()
    {
        return $this->belongsTo(ReturnObat::class, 'return_obat_id');
    }

    public function obat()
    {
        return $this->belongsTo(Obat::class, 'obat_id');
    }

    public function batchObat()
    {
        return $this->belongsTo(BatchObat::class, 'batch_obat_id');
    }
}
