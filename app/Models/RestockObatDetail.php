<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestockObatDetail extends Model
{
    /** @use HasFactory<\Database\Factories\RestockObatDetailFactory> */
    use HasFactory;

    protected $table = 'restock_obat_detail';

    protected $guarded = [];
}
