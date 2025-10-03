<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriObat extends Model
{
    use HasFactory;

    protected $table = 'kategori_obat';
    protected $primaryKey = 'id_kategori_obat';
    protected $guarded = [];

    public function dataObat(): HasMany
    {
        return $this->hasMany(DataObat::class, 'kategori_obat_id', 'id_kategori_obat');
    }
}
