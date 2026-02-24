<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestockObat extends Model
{
    /** @use HasFactory<\Database\Factories\RestockObatFactory> */
    use HasFactory;

    protected $table = 'restock_obat';

    protected $guarded = [];
}
