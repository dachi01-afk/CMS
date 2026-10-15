<?php

namespace App\Models;

use App\Support\Audit\Auditable;
use Illuminate\Database\Eloquent\Model;

class Auditor extends Model
{
    use Auditable;

    protected $table = 'auditor';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
