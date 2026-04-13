<?php

namespace App\Models;

use App\Models\Depot;
use App\Models\Hutang;
use App\Models\HutangObat;
use App\Models\RestockObatDetail;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestockObat extends Model
{
    /** @use HasFactory<\Database\Factories\RestockObatFactory> */
    use HasFactory;

    protected $table = 'restock_obat';

    protected $guarded = [];

    protected $appends = [
        'format_total_tagihan'
    ];

    protected function formatTotalTagihan(): Attribute
    {
        return Attribute::make(get: fn() => "Rp. " . number_format($this->total_tagihan, 0, ',', '.'));
    }

    public function restockObatDetail()
    {
        return $this->hasMany(RestockObatDetail::class);
    }

    public function hutang()
    {
        return $this->hasOne(HutangObat::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function depot()
    {
        return $this->belongsTo(Depot::class);
    }

    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function dikonfirmasiOleh()
    {
        return $this->belongsTo(User::class, 'dikonfirmasi_oleh');
    }
}
