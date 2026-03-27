<?php

namespace App\Models;

use App\Models\BatchObat;
use App\Models\Obat;
use App\Models\RestockObat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestockObatDetail extends Model
{
    /** @use HasFactory<\Database\Factories\RestockObatDetailFactory> */
    use HasFactory;

    protected $table = 'restock_obat_detail';

    protected $guarded = [];

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
