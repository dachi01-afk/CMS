<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Poli extends Model
{
    use HasFactory;

    protected $table = 'poli';
    protected $primaryKey = 'id_poli';
    protected $guarded = [];

    public function tenagaMedis(): BelongsToMany
    {
        return $this->belongsToMany(TenagaMedis::class, 'tenaga_medis_poli', 'poli_id', 'tenaga_medis_id');
    }
}
