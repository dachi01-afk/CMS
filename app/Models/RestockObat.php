<?php

namespace App\Models;

use App\Models\Depot;
use App\Models\Hutang;
use App\Models\RestockObatDetail;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestockObat extends Model
{
    /** @use HasFactory<\Database\Factories\RestockObatFactory> */
    use HasFactory;

    protected $table = 'restock_obat';

    protected $guarded = [];

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
