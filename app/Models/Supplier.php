<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'supplier';

    protected $guarded = [];

    public function stokTransaksi()
    {
        return $this->hasMany(StokTransaksi::class);
    }
}
