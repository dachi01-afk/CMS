<?php

namespace App\Models;

use App\Models\MetodePembayaran;
use App\Models\ReturnObat;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class PiutangObat extends Model
{
    protected $table = 'piutang_obat';
    protected $guarded = [];

    protected $casts = [
        'tanggal_piutang' => 'datetime',
    ];

    protected $appends = [
        'format_total_piutang',
        'format_tanggal_piutang',
    ];

    protected function formatTotalPiutang(): Attribute
    {
        return Attribute::make(get: fn() => 'Rp. ' . number_format($this->total_piutang, 0, ',', '.'));
    }

    protected function formatTanggalPiutang(): Attribute
    {
        return Attribute::make(get: fn() => $this->tanggal_piutang->translatedFormat('d F Y') ?? '-');
    }

    public function returnObat()
    {
        return $this->belongsTo(ReturnObat::class, 'return_obat_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function diupdateOleh()
    {
        return $this->belongsTo(User::class, 'diupdate_oleh');
    }

    public function metodePembayaran()
    {
        return $this->belongsTo(MetodePembayaran::class, 'metode_pembayaran_id');
    }
}
