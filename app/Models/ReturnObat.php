<?php

namespace App\Models;

use App\Models\Depot;
use App\Models\PiutangObat;
use App\Models\ReturnObatDetail;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class ReturnObat extends Model
{
    protected $table = 'return_obat';

    protected $guarded = [];

    protected $casts = [
        'tanggal_return' => 'datetime',
    ];

    protected $appends = [
        'format_tanggal_return',
        'format_total_tagihan',
    ];

    protected function formatTanggalReturn(): Attribute
    {
        return Attribute::make(get: fn() => $this->tanggal_return->translatedFormat('d F Y') ?? '-');
    }

    protected function formatTotalTagihan(): Attribute
    {
        return Attribute::make(get: fn() => 'Rp. ' . number_format($this->total_tagihan, 0, ',', '.'));
    }

    public function returnObatDetail()
    {
        return $this->hasMany(ReturnObatDetail::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function depot()
    {
        return $this->belongsTo(Depot::class, 'depot_id');
    }

    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function diupdateOleh()
    {
        return $this->belongsTo(User::class, 'diupdate_oleh');
    }

    public function piutang()
    {
        return $this->hasOne(PiutangObat::class);
    }
}
