<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriLayanan extends Model
{
    protected $table = "kategori_layanan";

    protected $guarded = [];

    public function layanan()
    {
        return $this->hasMany(Layanan::class);
    }

    public function penjualanLayanan()
    {
        return $this->hasMany(PenjualanLayanan::class);
    }
}
