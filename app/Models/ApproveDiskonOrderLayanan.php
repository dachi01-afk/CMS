<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApproveDiskonOrderLayanan extends Model
{
    protected $table = 'approve_diskon_order_layanan';

    protected $guarded = [];

    protected $casts = [
        'diskon_items' => 'array',
        'approved_at' => 'datetime',
    ];

    public function getFormatTanggalApprove()
    {
        return $this->approved_at ? $this->approved_at->format('d M Y') : '-';
    }

    public function orderLayanan()
    {
        return $this->belongsTo(OrderLayanan::class);
    }

    public function request()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approve()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
