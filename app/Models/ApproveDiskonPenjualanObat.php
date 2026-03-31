<?php

namespace App\Models;

use App\Models\PenjualanObat;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ApproveDiskonPenjualanObat extends Model
{
    protected $table = 'approve_diskon_penjualan_obat';

    protected $guarded = [];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function getFormatTanggalApprove()
    {
        return $this->approved_at ? $this->approved_at->translatedFormat('d M Y') : '-';
    }

    public function penjualanObat()
    {
        return $this->belongsTo(PenjualanObat::class, 'penjualan_obat_id');
    }

    public function request()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approve()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getDiskonItemsNormalizedAttribute(): array
    {
        $raw = $this->diskon_items;

        if (is_array($raw)) {
            return $raw;
        }

        $decoded = json_decode($raw, true);

        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : [];
    }
}
