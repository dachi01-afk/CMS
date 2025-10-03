<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SatuanObat extends Model
{
    use HasFactory;

    protected $table = 'satuan_obat';
    protected $primaryKey = 'id_satuan_obat';
    protected $guarded = [];

    public function dataObat(): HasMany
    {
        return $this->hasMany(DataObat::class, 'satuan_obat_id', 'id_satuan_obat');
    }
}
