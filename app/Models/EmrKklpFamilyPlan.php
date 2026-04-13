<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmrKklpFamilyPlan extends Model
{
    use HasFactory;

    protected $table = 'emr_kklp_family_plan';

    protected $guarded = [];

    public function emrKklp()
    {
        return $this->belongsTo(EmrKklp::class, 'emr_kklp_id');
    }
}