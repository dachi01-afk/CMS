<?php

namespace App\Models;

use App\Models\HutangObat;
use App\Models\MutasiStokObat;
use App\Models\StokTransaksi;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'supplier';

    protected $guarded = [];

    public function stokTransaksi()
    {
        return $this->hasMany(StokTransaksi::class);
    }

    public function mutasiStokObat()
    {
        return $this->hasMany(MutasiStokObat::class);
    }

    public function hutangObat()
    {
        return $this->hasMany(HutangObat::class);
    }
    
    public function restockObat()
    {
        return $this->hasMany(RestockObat::class);
    }
}
