<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiskonApproval extends Model
{
     protected $table = 'diskon_approval';

     protected $fillable = [
          'pembayaran_id',
          'requested_by',
          'approved_by',
          'status',
          'reason',
          'rejection_note',
          'diskon_items',
          'diskon_hash',
          'approved_at',
     ];

     protected $casts = [
          'diskon_items' => 'array',
          'approved_at'  => 'datetime',
     ];

     public function pembayaran()
     {
          return $this->belongsTo(Pembayaran::class, 'pembayaran_id');
     }

     public function requester()
     {
          return $this->belongsTo(User::class, 'requested_by');
     }

     public function approver()
     {
          return $this->belongsTo(User::class, 'approved_by');
     }
}
