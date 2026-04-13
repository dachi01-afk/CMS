<?php

namespace App\Models;

use App\Models\BatchObat;
use App\Models\Obat;
use App\Models\RestockObat;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestockObatDetail extends Model
{
    /** @use HasFactory<\Database\Factories\RestockObatDetailFactory> */
    use HasFactory;

    protected $table = 'restock_obat_detail';

    protected $guarded = [];

    protected $appends = [
        'format_harga_beli',
        'format_subtotal',
        'format_diskon_amount',
        'format_total_setelah_diskon',
    ];

    protected function formatHargaBeli(): Attribute
    {
        return Attribute::make(get: fn() => "Rp. " . number_format($this->harga_beli, 0, ',', '.'));
    }
    protected function formatSubtotal(): Attribute
    {
        return Attribute::make(get: fn() => "Rp. " . number_format($this->subtotal, 0, ',', '.'));
    }
    protected function formatDiskonAmount(): Attribute
    {
        return Attribute::make(get: fn() => "Rp. " . number_format($this->diskon_amount, 0, ',', '.'));
    }
    protected function formatTotalSetelahDiskon(): Attribute
    {
        return Attribute::make(get: fn() => "Rp. " . number_format($this->total_setelah_diskon, 0, ',', '.'));
    }

    public function restockObat()
    {
        return $this->belongsTo(RestockObat::class);
    }

    public function obat()
    {
        return $this->belongsTo(Obat::class);
    }

    public function batchObat()
    {
        return $this->belongsTo(BatchObat::class);
    }
}
