<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HasilLabTersedia
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $hasilLab;
    public $pasien;
    public $orderLab;

    // ✅ FIXED: Typo di constructor
    public function __construct($hasilLab, $pasien, $orderLab)
    {
        $this->hasilLab = $hasilLab;
        $this->pasien = $pasien;
        $this->orderLab = $orderLab;
    }
}